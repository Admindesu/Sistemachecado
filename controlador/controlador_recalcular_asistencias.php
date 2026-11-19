<?php
/**
 * Recálculo explícito de asistencias de un empleado (botón "Recalcular" de vista/empleado.php).
 *
 * Un cambio de horario aplica desde la firma del documento y lo ya registrado se conserva
 * (auditoría). Solo en casos extraordinarios, como un horario asignado por error, el
 * administrador recalcula un rango de fechas con el horario actual del empleado. Cada
 * recálculo queda en la tabla recalculo_asistencia (sql/migraciones/005_bitacora_recalculo.sql).
 *
 * POST id, desde, hasta (AAAA-MM-DD), motivo, confirmar=1.
 * Responde con una redirección a empleado.php?recalculo=<resultado>.
 */
require_once __DIR__ . '/auth.php';
requerir_admin();   // en POST también valida el token CSRF

include_once __DIR__ . '/../modelo/conexion.php';
require_once __DIR__ . '/../modelo/hikvision.php';

const MAX_DIAS_RECALCULO = 366;

function volver_a_empleados_recalculo(string $resultado, array $extra = []): void
{
    $extra = array_merge(['recalculo' => $resultado], $extra);
    header('Location: ../vista/empleado.php?' . http_build_query($extra));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    volver_a_empleados_recalculo('');
}
if (empty($_POST['confirmar'])) {
    volver_a_empleados_recalculo('sin_confirmar');
}

$id = (int) ($_POST['id'] ?? 0);
$res = consulta($conexion,
    "SELECT e.dni, e.id_horario, h.hora_entrada FROM empleado e
     LEFT JOIN horarios h ON e.id_horario = h.id_horario WHERE e.id_empleado = ?", "i", $id);
$empleado = $res ? $res->fetch_assoc() : null;
if (!$empleado) {
    volver_a_empleados_recalculo('no_existe');
}
if (empty($empleado['hora_entrada'])) {
    volver_a_empleados_recalculo('sin_horario');
}

// Rango de fechas: válido, en orden, no en el futuro y de a lo más un año
$fechas = [];
foreach (['desde', 'hasta'] as $campo) {
    $valor = (string) ($_POST[$campo] ?? '');
    $d = DateTime::createFromFormat('!Y-m-d', $valor);
    if (!$d || $d->format('Y-m-d') !== $valor) {
        volver_a_empleados_recalculo('fecha');
    }
    $fechas[$campo] = $d;
}
$hoy = new DateTime('today');
if ($fechas['desde'] > $fechas['hasta'] || $fechas['hasta'] > $hoy
    || $fechas['desde']->diff($fechas['hasta'])->days + 1 > MAX_DIAS_RECALCULO) {
    volver_a_empleados_recalculo('rango');
}
$desde = $fechas['desde']->format('Y-m-d');
$hasta = $fechas['hasta']->format('Y-m-d');

$motivo = mb_substr(trim((string) ($_POST['motivo'] ?? '')), 0, 255);
if (mb_strlen($motivo) < 5) {
    volver_a_empleados_recalculo('motivo');
}

// La bitácora se escribe primero: si no se puede registrar, no se recalcula nada
$ok = consulta($conexion,
    "INSERT INTO recalculo_asistencia (dni, desde, hasta, motivo, id_horario, realizado_por)
     VALUES (?, ?, ?, ?, ?, ?)",
    "ssssis", $empleado['dni'], $desde, $hasta, $motivo, (int) $empleado['id_horario'],
    (string) ($_SESSION['dni'] ?? ''));
if (!$ok) {
    volver_a_empleados_recalculo('error');
}
$id_recalculo = $conexion->insert_id;

try {
    $r = recalcular_rango($conexion, $empleado['dni'], $desde, $hasta);
} catch (Throwable $e) {
    error_log('Recálculo de asistencias: ' . $e->getMessage());
    consulta($conexion, "UPDATE recalculo_asistencia SET motivo = LEFT(CONCAT('[FALLÓ] ', motivo), 255) WHERE id_recalculo = ?",
        "i", $id_recalculo);
    volver_a_empleados_recalculo('error');
}
consulta($conexion, "UPDATE recalculo_asistencia SET dias = ?, registros = ? WHERE id_recalculo = ?",
    "iii", $r['dias'], $r['registros'], $id_recalculo);

volver_a_empleados_recalculo('ok', ['dias' => $r['dias']]);
