<?php
/**
 * Cálculos del dashboard de asistencia.
 *
 * Cada empleado, en cada día del rango, queda en un estado:
 *   PUNTUAL   entrada A_TIEMPO
 *   RETARDO   entrada RETARDO
 *   FALTA     entrada FALTA (llegó fuera del límite) o no se presentó en un día laborable
 *   PENDIENTE hoy, aún sin entrada pero dentro de su límite de retardo
 *   JUSTIFICADO un retardo, falta o pendiente cubierto por una justificación
 * Además se marca "sin salida" si checó entrada pero no salida (salvo que esté justificada).
 * Los fines de semana y los días del catálogo de días inhábiles no generan faltas.
 */
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/calendario.php';

const DIAS_LABORABLES = [1, 2, 3, 4, 5]; // lunes a viernes (ISO-8601: 1 = lunes)
const MAX_DIAS_RANGO = 366;
const MINUTOS_LIMITE_SALIDA = 120;       // misma regla que el checado: salida hasta 2 h después

function es_laborable(DateTimeInterface $dia): bool
{
    return in_array((int) $dia->format('N'), DIAS_LABORABLES, true);
}

/**
 * Último día laborable antes de $hoy (si hoy es lunes, el viernes).
 * $es_inhabil(DateTimeInterface): bool permite saltar también los días del catálogo.
 */
function dia_laborable_anterior(DateTimeImmutable $hoy, ?callable $es_inhabil = null): DateTimeImmutable
{
    $dia = $hoy->modify('-1 day');
    for ($i = 0; $i < 31 && (!es_laborable($dia) || ($es_inhabil && $es_inhabil($dia))); $i++) {
        $dia = $dia->modify('-1 day');
    }
    return $dia;
}

/**
 * Interpreta el rango pedido (?rango=ayer|hoy|semana|mes|personalizado&desde=&hasta=).
 * Devuelve ['clave', 'desde' => DateTimeImmutable, 'hasta' => DateTimeImmutable].
 * Nunca incluye días futuros.
 */
function rango_dashboard(array $q, DateTimeImmutable $hoy, ?callable $es_inhabil = null): array
{
    $hoy = $hoy->setTime(0, 0);
    $clave = $q['rango'] ?? 'ayer';
    switch ($clave) {
        case 'hoy':
            return ['clave' => 'hoy', 'desde' => $hoy, 'hasta' => $hoy];
        case 'semana':
            return ['clave' => 'semana', 'desde' => $hoy->modify('monday this week'), 'hasta' => $hoy];
        case 'mes':
            return ['clave' => 'mes', 'desde' => $hoy->modify('first day of this month'), 'hasta' => $hoy];
        case 'personalizado':
            $leer = function ($v) {
                $d = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $v);
                return ($d && $d->format('Y-m-d') === $v) ? $d : null;
            };
            $desde = $leer($q['desde'] ?? '');
            $hasta = $leer($q['hasta'] ?? '');
            if ($desde && $hasta) {
                if ($desde > $hasta) [$desde, $hasta] = [$hasta, $desde];
                if ($hasta > $hoy) $hasta = $hoy;
                if ($desde > $hoy) $desde = $hoy;
                if ($desde->diff($hasta)->days >= MAX_DIAS_RANGO) {
                    $desde = $hasta->modify('-' . (MAX_DIAS_RANGO - 1) . ' days');
                }
                return ['clave' => 'personalizado', 'desde' => $desde, 'hasta' => $hasta];
            }
            // fechas inválidas: se usa el rango por defecto
        default:
            $dia = dia_laborable_anterior($hoy, $es_inhabil);
            return ['clave' => 'ayer', 'desde' => $dia, 'hasta' => $dia];
    }
}

function minutos_hora_dashboard(?string $hora): ?int
{
    if (!$hora) return null;
    [$h, $m] = array_map('intval', explode(':', $hora));
    return $h * 60 + $m;
}

/**
 * Calcula el resumen de asistencia del rango.
 * $ahora se usa para "hoy" (pendientes y salidas aún no vencidas).
 */
function resumen_asistencia(mysqli $conexion, DateTimeImmutable $desde, DateTimeImmutable $hasta,
                            DateTimeImmutable $ahora): array
{
    // Los dados de baja solo cuentan hasta su fecha de baja (último día laborado)
    $empleados = consulta($conexion,
        "SELECT e.dni, e.nombre, e.apellido, e.fecha_baja, d.nombre AS direccion,
                h.hora_entrada, h.hora_salida, h.limite_retardo
         FROM empleado e
         LEFT JOIN direccion d ON e.direccion = d.id_direccion
         LEFT JOIN horarios h ON e.id_horario = h.id_horario
         WHERE e.activo = 1 OR e.fecha_baja >= ?
         ORDER BY e.nombre, e.apellido", "s", $desde->format('Y-m-d'))->fetch_all(MYSQLI_ASSOC);

    // Primer entrada y última salida de cada empleado por día
    $registros = [];
    $res = consulta($conexion,
        "SELECT dni, tipo, fecha, estado FROM asistencia
         WHERE fecha >= ? AND fecha < DATE_ADD(?, INTERVAL 1 DAY) ORDER BY fecha",
        "ss", $desde->format('Y-m-d'), $hasta->format('Y-m-d'));
    while ($r = $res->fetch_assoc()) {
        $dia = substr($r['fecha'], 0, 10);
        $hora = substr($r['fecha'], 11, 5);
        if ($r['tipo'] === 'entrada' && !isset($registros[$r['dni']][$dia]['entrada'])) {
            $registros[$r['dni']][$dia]['entrada'] = ['hora' => $hora, 'estado' => $r['estado']];
        } elseif ($r['tipo'] === 'salida') {
            $registros[$r['dni']][$dia]['salida'] = $hora;
        }
    }

    $inhabiles = cargar_dias_inhabiles($conexion, $desde, $hasta);
    $justificaciones = cargar_justificaciones($conexion, $desde, $hasta);

    $hoy = $ahora->format('Y-m-d');
    $minutos_ahora = (int) $ahora->format('H') * 60 + (int) $ahora->format('i');
    $vacio = ['PUNTUAL' => 0, 'RETARDO' => 0, 'FALTA' => 0, 'PENDIENTE' => 0, 'JUSTIFICADO' => 0, 'SIN_SALIDA' => 0];
    $dias_inhabiles = [];

    $totales = $vacio;
    $por_dia = [];
    $por_empleado = [];
    $detalle = [];   // una fila por empleado y día

    for ($dia = $desde; $dia <= $hasta; $dia = $dia->modify('+1 day')) {
        $clave_dia = $dia->format('Y-m-d');
        $inhabil = dia_inhabil($inhabiles, $dia);
        if ($inhabil !== null) {
            $dias_inhabiles[$clave_dia] = $inhabil;
        }
        $laborable = es_laborable($dia) && $inhabil === null;
        $por_dia[$clave_dia] = $vacio + ['laborable' => $laborable, 'inhabil' => $inhabil];

        foreach ($empleados as $emp) {
            if (!empty($emp['fecha_baja']) && $clave_dia > $emp['fecha_baja']) {
                continue;   // ya no laboraba ese día
            }
            $dni = $emp['dni'];
            $reg = $registros[$dni][$clave_dia] ?? [];
            $entrada = $reg['entrada'] ?? null;
            $salida = $reg['salida'] ?? null;

            if ($entrada) {
                $estado = in_array($entrada['estado'], ['RETARDO', 'FALTA'], true) ? $entrada['estado'] : 'PUNTUAL';
                $motivo = $estado === 'FALTA' ? 'Llegó fuera del límite' : null;
            } elseif (!$laborable) {
                continue; // fin de semana o día inhábil sin registro: no cuenta
            } elseif ($clave_dia === $hoy) {
                $limite = minutos_hora_dashboard($emp['hora_entrada']);
                $limite = $limite === null ? null : $limite + (int) ($emp['limite_retardo'] ?? 20);
                if ($limite === null || $minutos_ahora <= $limite) {
                    $estado = 'PENDIENTE';
                    $motivo = 'Aún dentro de su horario de entrada';
                } else {
                    $estado = 'FALTA';
                    $motivo = 'No se ha presentado';
                }
            } else {
                $estado = 'FALTA';
                $motivo = 'No se presentó';
            }

            // Sin salida: tiene entrada, no tiene salida y ya pasó el límite para checarla
            $sin_salida = false;
            if ($entrada && !$salida) {
                if ($clave_dia < $hoy) {
                    $sin_salida = true;
                } elseif ($clave_dia === $hoy && $emp['hora_salida']) {
                    $sin_salida = $minutos_ahora > minutos_hora_dashboard($emp['hora_salida']) + MINUTOS_LIMITE_SALIDA;
                }
            }

            // Qué tipo de justificación correspondería (para el enlace "Justificar")
            $tipo_sugerido = null;
            if ($estado === 'FALTA' && !$entrada) $tipo_sugerido = 'dia_completo';
            elseif ($estado === 'FALTA' || $estado === 'RETARDO') $tipo_sugerido = 'entrada';
            elseif ($sin_salida) $tipo_sugerido = 'salida';

            // Justificaciones: solo mejoran el estado, nunca lo empeoran
            $just = justificacion_del_dia($justificaciones[$dni] ?? [], $clave_dia);
            $justificacion = null;
            if (in_array($estado, ['RETARDO', 'FALTA', 'PENDIENTE'], true)) {
                if ($just['dia_completo']) {
                    $justificacion = $just['dia_completo'] . ' (día completo)';
                } elseif ($just['entrada']) {
                    $justificacion = $just['entrada'] . ' (entrada)';
                }
                if ($justificacion) {
                    $motivo = trim(($motivo ? $motivo . ' · ' : '') . 'Justificado: ' . $justificacion);
                    $estado = 'JUSTIFICADO';
                    $tipo_sugerido = null;
                }
            }
            $salida_justificada = null;
            if ($sin_salida && ($just['salida'] || $just['dia_completo'])) {
                $sin_salida = false;
                $salida_justificada = $just['salida'] ?? $just['dia_completo'];
                if ($tipo_sugerido === 'salida') $tipo_sugerido = null;
            }

            $totales[$estado]++;
            $por_dia[$clave_dia][$estado]++;
            if ($sin_salida) {
                $totales['SIN_SALIDA']++;
                $por_dia[$clave_dia]['SIN_SALIDA']++;
            }

            if (!isset($por_empleado[$dni])) {
                $por_empleado[$dni] = $vacio + [
                    'dni' => $dni,
                    'nombre' => trim($emp['nombre'] . ' ' . $emp['apellido']),
                    'direccion' => $emp['direccion'] ?? '',
                ];
            }
            $por_empleado[$dni][$estado]++;
            if ($sin_salida) $por_empleado[$dni]['SIN_SALIDA']++;

            $detalle[] = [
                'dia' => $clave_dia,
                'dni' => $dni,
                'nombre' => trim($emp['nombre'] . ' ' . $emp['apellido']),
                'direccion' => $emp['direccion'] ?? '',
                'estado' => $estado,
                'motivo' => $motivo,
                'entrada' => $entrada['hora'] ?? null,
                'salida' => $salida,
                'sin_salida' => $sin_salida,
                'salida_justificada' => $salida_justificada,
                'tipo_sugerido' => $tipo_sugerido,
            ];
        }
    }

    // Empleados con más faltas y retardos primero
    usort($por_empleado, fn($a, $b) => [$b['FALTA'], $b['RETARDO'], $a['nombre']] <=> [$a['FALTA'], $a['RETARDO'], $b['nombre']]);

    $evaluados = $totales['PUNTUAL'] + $totales['RETARDO'] + $totales['FALTA'];
    return [
        'totales' => $totales,
        'puntualidad' => $evaluados ? (int) round(100 * $totales['PUNTUAL'] / $evaluados) : null,
        'evaluados' => $evaluados,
        'por_dia' => $por_dia,
        'por_empleado' => array_values($por_empleado),
        'detalle' => $detalle,
        'dias_laborables' => count(array_filter($por_dia, fn($d) => $d['laborable'])),
        'dias_inhabiles' => $dias_inhabiles,
    ];
}
