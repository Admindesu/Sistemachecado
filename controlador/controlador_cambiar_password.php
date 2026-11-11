<?php
/**
 * Cambia la contraseña del empleado que tiene la sesión iniciada.
 * Recibe: pass_actual, pass_nueva, pass_confirmar y volver (página a la que regresar).
 * Regresa a la página de origen con ?pass=<resultado> para mostrar la notificación.
 */
require_once __DIR__ . '/auth.php';
requerir_sesion();
include "../modelo/conexion.php";

// Solo se permite regresar a una vista existente o a la pantalla de checado
$volver = $_POST['volver'] ?? '';
if (preg_match('/^[A-Za-z_]+\.php$/', $volver) && is_file(__DIR__ . '/../vista/' . $volver)) {
    $destino = '../vista/' . $volver;
} else {
    $destino = '../index.php';
}

function regresar(string $destino, string $resultado): void
{
    header('Location: ' . $destino . '?pass=' . $resultado);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    regresar($destino, 'error');
}

$pass_actual = $_POST['pass_actual'] ?? '';
$pass_nueva = $_POST['pass_nueva'] ?? '';
$pass_confirmar = $_POST['pass_confirmar'] ?? '';

if ($pass_actual === '' || $pass_nueva === '') {
    regresar($destino, 'vacio');
}
if ($pass_nueva !== $pass_confirmar) {
    regresar($destino, 'no_coincide');
}
if (strlen($pass_nueva) < 6) {
    regresar($destino, 'corta');
}

$id_empleado = (int) $_SESSION['id_usuario']; // id_usuario guarda el id_empleado (ver login.php)

$stmt = $conexion->prepare("SELECT password FROM empleado WHERE id_empleado = ?");
$stmt->bind_param("i", $id_empleado);
$stmt->execute();
$fila = $stmt->get_result()->fetch_assoc();

if (!$fila || !verificar_password($pass_actual, $fila['password'])) {
    regresar($destino, 'incorrecta');
}

$nuevo_hash = hash_password($pass_nueva);
$stmt = $conexion->prepare("UPDATE empleado SET password = ? WHERE id_empleado = ?");
$stmt->bind_param("si", $nuevo_hash, $id_empleado);
regresar($destino, $stmt->execute() ? 'ok' : 'error');
