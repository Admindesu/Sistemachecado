<?php
/**
 * Reglas de asistencia compartidas por el checado web y el terminal Hikvision.
 * Todas las horas se comparan en minutos desde la medianoche (no hay turnos nocturnos).
 */
require_once __DIR__ . '/db.php';

const MINUTOS_ANTICIPACION_ENTRADA = 15;   // se puede checar entrada hasta 15 min antes
const TOLERANCIA_SALIDA_DEFECTO = 15;      // se puede checar salida hasta 15 min antes
const MINUTOS_FALTA_SALIDA = 120;          // salida 2 h después de la hora = FALTA

function minutos_del_dia(DateTimeInterface $momento): int
{
    return (int) $momento->format('H') * 60 + (int) $momento->format('i');
}

function minutos_de_hora(string $hora): int
{
    [$h, $m] = array_map('intval', explode(':', $hora));
    return $h * 60 + $m;
}

/**
 * Empleado + su horario. null si el empleado no existe.
 * Las columnas del horario vienen en null si no tiene horario asignado.
 */
function horario_empleado(mysqli $conexion, string $dni): ?array
{
    $res = consulta($conexion,
        "SELECT e.dni, h.hora_entrada, h.hora_salida, h.tolerancia_entrada, h.limite_retardo
         FROM empleado e LEFT JOIN horarios h ON e.id_horario = h.id_horario
         WHERE e.dni = ?", "s", $dni);
    $fila = $res ? $res->fetch_assoc() : null;
    return $fila ?: null;
}

/** ANTICIPADA | A_TIEMPO | RETARDO | FALTA */
function estado_entrada(array $horario, DateTimeInterface $momento): string
{
    $actual = minutos_del_dia($momento);
    $entrada = minutos_de_hora($horario['hora_entrada']);
    $tolerancia = (int) ($horario['tolerancia_entrada'] ?? 10);
    $retardo = (int) ($horario['limite_retardo'] ?? 20);

    if ($actual < $entrada - MINUTOS_ANTICIPACION_ENTRADA) return 'ANTICIPADA';
    if ($actual <= $entrada + $tolerancia) return 'A_TIEMPO';
    if ($actual <= $entrada + $retardo) return 'RETARDO';
    return 'FALTA';
}

/** TEMPRANA | NORMAL | FALTA */
function estado_salida(array $horario, DateTimeInterface $momento): string
{
    $actual = minutos_del_dia($momento);
    $salida = minutos_de_hora($horario['hora_salida']);
    $tolerancia = (int) ($horario['tolerancia_salida'] ?? TOLERANCIA_SALIDA_DEFECTO);

    if ($actual < $salida - $tolerancia) return 'TEMPRANA';
    if ($actual > $salida + MINUTOS_FALTA_SALIDA) return 'FALTA';
    return 'NORMAL';
}

/** Registros de asistencia de un empleado en un día (Y-m-d), en orden. */
function registros_del_dia(mysqli $conexion, string $dni, string $dia, ?string $origen = null): array
{
    $sql = "SELECT id_asistencia, tipo, fecha, estado, origen FROM asistencia
            WHERE dni = ? AND fecha >= ? AND fecha < DATE_ADD(?, INTERVAL 1 DAY)";
    $tipos = "sss";
    $params = [$dni, $dia, $dia];
    if ($origen !== null) {
        $sql .= " AND origen = ?";
        $tipos .= "s";
        $params[] = $origen;
    }
    $res = consulta($conexion, $sql . " ORDER BY fecha", $tipos, ...$params);
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function insertar_asistencia(mysqli $conexion, string $dni, string $tipo, DateTimeInterface $fecha,
                             string $estado, string $origen = 'web', ?int $id_marcaje = null)
{
    return consulta($conexion,
        "INSERT INTO asistencia (dni, tipo, fecha, estado, origen, id_marcaje) VALUES (?, ?, ?, ?, ?, ?)",
        "sssssi", $dni, $tipo, $fecha->format('Y-m-d H:i:s'), $estado, $origen, $id_marcaje);
}
