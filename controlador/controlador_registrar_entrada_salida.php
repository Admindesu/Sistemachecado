<?php
session_start();
include "../modelo/conexion.php";

// Verifica que el usuario esté logueado y tenga DNI en sesión
if (!isset($_SESSION['dni'])) {
    header("Location: ../vista/login/login_reloj.php");
    exit();
}

$dni = $_SESSION['dni'];
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : null;
$fecha = date('Y-m-d H:i:s');

// Busca el id_empleado por DNI
$sql = $conexion->query("SELECT id_empleado FROM empleado WHERE dni='$dni'");
if ($sql && ($empleado = $sql->fetch_object())) {
    $id_empleado = $empleado->id_empleado;

    if ($tipo == 'entrada') {
        // Registrar entrada
        $conexion->query("INSERT INTO asistencia (id_empleado, entrada) VALUES ('$id_empleado', '$fecha')");
        header("Location: ../index.php?msg=entrada_ok");
        exit();
    } elseif ($tipo == 'salida') {
        // Buscar el último registro de entrada sin salida
        $sql_asistencia = $conexion->query("SELECT id_asistencia FROM asistencia WHERE id_empleado='$id_empleado' AND salida IS NULL ORDER BY entrada DESC LIMIT 1");
        if ($sql_asistencia && ($asistencia = $sql_asistencia->fetch_object())) {
            $id_asistencia = $asistencia->id_asistencia;
            $conexion->query("UPDATE asistencia SET salida='$fecha' WHERE id_asistencia='$id_asistencia'");
            header("Location: ../index.php?msg=salida_ok");
            exit();
        } else {
            header("Location: ../index.php?msg=no_entrada");
            exit();
        }
    }
} else {
    header("Location: ../index.php?msg=empleado_no_encontrado");
    exit();
}
?>