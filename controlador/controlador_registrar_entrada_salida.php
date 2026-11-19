<?php
/**
 * Checado web (botones Entrada / Salida de index.php).
 * Las reglas de estado están en modelo/asistencia_reglas.php y son las mismas
 * que se aplican a los marcajes del terminal Hikvision.
 */
require_once __DIR__ . '/auth.php';
requerir_sesion(); // también valida el token CSRF
include "../modelo/conexion.php";
require_once __DIR__ . '/../modelo/asistencia_reglas.php';

function volver(string $mensaje): void
{
    header("Location: ../index.php?msg=" . $mensaje);
    exit;
}

$dni = (string) $_SESSION['dni'];
$tipo = $_POST['tipo'] ?? '';

if ($tipo !== 'entrada' && $tipo !== 'salida') {
    volver('datos_invalidos');
}

$horario = horario_empleado($conexion, $dni);
if (!$horario) {
    volver('empleado_no_encontrado');
}
if (!(int) $horario['activo']) {
    volver('empleado_baja');   // sesión abierta antes de la baja
}
if (empty($horario['hora_entrada']) || empty($horario['hora_salida'])) {
    error_log("Checado web: el empleado $dni no tiene horario asignado");
    volver('error_registro');
}

$ahora = new DateTime();
$hoy = registros_del_dia($conexion, $dni, $ahora->format('Y-m-d'));
$entrada_hoy = null;
$salida_hoy = null;
foreach ($hoy as $r) {
    if ($r['tipo'] === 'entrada' && !$entrada_hoy) $entrada_hoy = $r;
    if ($r['tipo'] === 'salida') $salida_hoy = $r;
}

if ($tipo === 'entrada') {
    if ($entrada_hoy) {
        volver('entrada_duplicada');
    }
    $estado = estado_entrada($horario, $ahora);
    if ($estado === 'ANTICIPADA') {
        volver('entrada_anticipada');
    }
    if (!insertar_asistencia($conexion, $dni, 'entrada', $ahora, $estado, 'web')) {
        volver('error_registro');
    }
    // A_TIEMPO -> entrada_atiempo, RETARDO -> entrada_retardo, FALTA -> entrada_falta
    volver('entrada_' . strtolower(str_replace('_', '', $estado)));
}

// Salida
if ($salida_hoy) {
    volver('salida_duplicada');
}
$estado = estado_salida($horario, $ahora);
if ($estado === 'TEMPRANA') {
    volver('salida_temprana');
}
if (!insertar_asistencia($conexion, $dni, 'salida', $ahora, $estado, 'web')) {
    volver('error_registro');
}
if ($estado === 'FALTA') {
    // Salida más de 2 h tarde: la entrada del día también se marca como FALTA
    if ($entrada_hoy) {
        consulta($conexion, "UPDATE asistencia SET estado = 'FALTA' WHERE id_asistencia = ?", "i",
            $entrada_hoy['id_asistencia']);
    }
    volver('salida_falta');
}
volver('salida_ok');
