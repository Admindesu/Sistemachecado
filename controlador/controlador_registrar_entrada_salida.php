<?php
session_start();
include "../modelo/conexion.php";

$dni = isset($_SESSION['dni']) ? $_SESSION['dni'] : '';
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';

if ($dni && ($tipo === 'entrada' || $tipo === 'salida')) {
    // Verifica que el empleado exista
    $sql = "SELECT * FROM empleado WHERE dni = ?";
    $stmt = $conexion->prepare($sql);
    
    if (!$stmt) {
        die('Error en prepare: ' . $conexion->error);
    }
    
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Registrar asistencia
        date_default_timezone_set('America/Cancun'); // Cambia a tu zona horaria
        $fecha = date('Y-m-d H:i:s');
        $sql2 = "INSERT INTO asistencia (dni, tipo, fecha) VALUES (?, ?, ?)";
        $stmt2 = $conexion->prepare($sql2);
        
        if (!$stmt2) {
            die('Error en segundo prepare: ' . $conexion->error);
        }
        
        $stmt2->bind_param("sss", $dni, $tipo, $fecha);
        
        if ($stmt2->execute()) {
            header("Location: ../index.php?msg={$tipo}_ok");
        } else {
            header("Location: ../index.php?msg=error_registro");
        }
        $stmt2->close();
    } else {
        header("Location: ../index.php?msg=empleado_no_encontrado");
    }
    $stmt->close();
    $conexion->close();
} else {
    header("Location: ../index.php?msg=empleado_no_encontrado");
}
?>

