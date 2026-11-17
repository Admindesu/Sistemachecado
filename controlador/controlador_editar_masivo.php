<?php
/**
 * Edición masiva de empleados (vista/empleado.php, botón "Editar seleccionados").
 * Cambia cargo, dirección, horario y (si están activas) subsecretaría de varios empleados
 * a la vez. Un campo vacío ("No cambiar") se deja como está.
 * Un cambio de horario aplica desde ese momento (lo respalda un documento) y las asistencias
 * ya registradas se conservan: aquí nunca se recalcula. Para corregir un horario asignado por
 * error está el botón "Recalcular" de cada empleado (controlador_recalcular_asistencias.php),
 * que deja constancia en la bitácora.
 * Responde con una redirección a empleado.php?masivo=<resultado>&n=<empleados>.
 */
require_once __DIR__ . '/auth.php';
requerir_admin();   // en POST también valida el token CSRF

include_once __DIR__ . '/../modelo/conexion.php';

function volver_a_empleados(string $resultado, int $n = 0): void
{
    header('Location: ../vista/empleado.php?masivo=' . $resultado . ($n ? '&n=' . $n : ''));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    volver_a_empleados('');
}

// Empleados seleccionados
$ids = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['ids'] ?? [])))));
if (!$ids) {
    volver_a_empleados('sin_empleados');
}

// Campos a cambiar: [columna, tabla del catálogo, llave del catálogo]
$campos = [
    'cargo' => ['cargo', 'cargo', 'id_cargo'],
    'direccion' => ['direccion', 'direccion', 'id_direccion'],
    'horario' => ['id_horario', 'horarios', 'id_horario'],
];
if (usar_subsecretarias()) {
    $campos['subsecretaria'] = ['subsecretaria', 'subsecretaria', 'id_subsecretaria'];
}

$set = [];
$tipos = '';
$valores = [];
foreach ($campos as $nombre => [$columna, $tabla, $llave]) {
    $valor = (int) ($_POST[$nombre] ?? 0);
    if ($valor <= 0) continue;   // "No cambiar"
    // El valor debe existir en su catálogo
    $existe = consulta($conexion, "SELECT 1 FROM `$tabla` WHERE `$llave` = ?", "i", $valor);
    if (!$existe || !$existe->fetch_row()) {
        volver_a_empleados('invalido');
    }
    $set[] = "`$columna` = ?";
    $tipos .= 'i';
    $valores[] = $valor;
}
if (!$set) {
    volver_a_empleados('sin_cambios');
}

$marcas = implode(',', array_fill(0, count($ids), '?'));
$ok = consulta($conexion,
    "UPDATE empleado SET " . implode(', ', $set) . " WHERE id_empleado IN ($marcas)",
    $tipos . str_repeat('i', count($ids)), ...$valores, ...$ids);
if (!$ok) {
    volver_a_empleados('error');
}

volver_a_empleados('ok', count($ids));
