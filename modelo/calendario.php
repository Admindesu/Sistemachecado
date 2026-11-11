<?php
/**
 * Días inhábiles y justificaciones (permisos) de asistencia.
 */
require_once __DIR__ . '/db.php';

const TIPOS_JUSTIFICACION = [
    'entrada' => 'Entrada (retardo o entrada no checada)',
    'salida' => 'Salida (salida no checada o anticipada)',
    'dia_completo' => 'Día completo',
];

const MOTIVOS_JUSTIFICACION = ['Permiso personal', 'Incapacidad médica', 'Comisión oficial', 'Vacaciones', 'Otro'];

const MAX_DIAS_JUSTIFICACION = 366;

/**
 * Catálogo de días inhábiles que afectan al rango:
 * ['fijos' => ['2026-11-16' => 'Revolución'], 'anuales' => ['01-01' => 'Año nuevo']]
 */
function cargar_dias_inhabiles(mysqli $conexion, DateTimeInterface $desde, DateTimeInterface $hasta): array
{
    $catalogo = ['fijos' => [], 'anuales' => []];
    $res = consulta($conexion,
        "SELECT fecha, descripcion, anual FROM dia_inhabil
         WHERE anual = 1 OR (fecha BETWEEN ? AND ?)",
        "ss", $desde->format('Y-m-d'), $hasta->format('Y-m-d'));
    if (!$res) {
        return $catalogo; // tabla aún no creada: se comporta como antes
    }
    while ($d = $res->fetch_assoc()) {
        if ((int) $d['anual'] === 1) {
            $catalogo['anuales'][substr($d['fecha'], 5)] = $d['descripcion'];
        } else {
            $catalogo['fijos'][$d['fecha']] = $d['descripcion'];
        }
    }
    return $catalogo;
}

/** Descripción del día inhábil, o null si el día es hábil según el catálogo. */
function dia_inhabil(array $catalogo, DateTimeInterface $dia): ?string
{
    return $catalogo['fijos'][$dia->format('Y-m-d')] ?? $catalogo['anuales'][$dia->format('m-d')] ?? null;
}

/** Justificaciones que se cruzan con el rango, agrupadas por dni. */
function cargar_justificaciones(mysqli $conexion, DateTimeInterface $desde, DateTimeInterface $hasta): array
{
    $por_dni = [];
    $res = consulta($conexion,
        "SELECT dni, fecha_inicio, fecha_fin, tipo, motivo, observaciones FROM justificacion
         WHERE fecha_inicio <= ? AND fecha_fin >= ?",
        "ss", $hasta->format('Y-m-d'), $desde->format('Y-m-d'));
    if (!$res) {
        return $por_dni;
    }
    while ($j = $res->fetch_assoc()) {
        $por_dni[$j['dni']][] = $j;
    }
    return $por_dni;
}

/**
 * Qué está justificado para un empleado en un día:
 * ['entrada' => 'Permiso personal' | null, 'salida' => ..., 'dia_completo' => ...]
 */
function justificacion_del_dia(array $justificaciones, string $dia): array
{
    $resultado = ['entrada' => null, 'salida' => null, 'dia_completo' => null];
    foreach ($justificaciones as $j) {
        if ($j['fecha_inicio'] <= $dia && $j['fecha_fin'] >= $dia && array_key_exists($j['tipo'], $resultado)) {
            $resultado[$j['tipo']] = $resultado[$j['tipo']] ?? $j['motivo'];
        }
    }
    return $resultado;
}
