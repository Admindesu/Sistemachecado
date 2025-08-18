
<?php
// Controlador de asistencia de empleados.
// - Verifica sesión activa.
// - Registra entrada/salida en la BD según botón presionado.
// - Redirecciona con mensaje de éxito.
//


session_start();
include "../modelo/conexion.php";

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../vista/login/login.php");
    exit();
}

$numeroEmpleado = $_POST['txtEmpleado'] ?? null;
$id_usuario = $_SESSION['id_usuario'];
$fecha = date('Y-m-d H:i:s');

if (isset($_POST['btn_entrada'])) {
    // Registrar entrada
    $conexion->query("INSERT INTO asistencia (id_usuario, numero_empleado, tipo, fecha) VALUES ('$id_usuario', '$numeroEmpleado', 'entrada', '$fecha')");
    header("Location: ../index.php?msg=entrada_ok");
    exit();
}

if (isset($_POST['btn_salida'])) {
    // Registrar salida
    $conexion->query("INSERT INTO asistencia (id_usuario, numero_empleado, tipo, fecha) VALUES ('$id_usuario', '$numeroEmpleado', 'salida', '$fecha')");
    header("Location: ../index.php?msg=salida_ok");
    exit();
}
?>