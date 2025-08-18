<?php
session_start(); // Inicia la sesión para acceder a variables de sesión
include "../modelo/conexion.php"; // Incluye el archivo de conexión a la base de datos

$dni = isset($_SESSION['dni']) ? $_SESSION['dni'] : ''; // Obtiene el DNI del empleado desde la sesión
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : ''; // Obtiene el tipo de registro (entrada/salida) desde el formulario

if ($dni && ($tipo === 'entrada' || $tipo === 'salida')) {
    // Verifica que el empleado exista en la base de datos
    $sql = "SELECT * FROM empleado WHERE dni = ?";
    $stmt = $conexion->prepare($sql); // Prepara la consulta para evitar inyección SQL
    
    if (!$stmt) {
        die('Error en prepare: ' . $conexion->error); // Maneja errores en la preparación de la consulta
    }
    
    $stmt->bind_param("s", $dni); // Asocia el parámetro DNI a la consulta preparada
    $stmt->execute(); // Ejecuta la consulta
    $result = $stmt->get_result(); // Obtiene el resultado de la consulta

    if ($result->num_rows > 0) {
        // Si el empleado existe, registra la asistencia
        date_default_timezone_set('America/Cancun'); // Establece la zona horaria
        $fecha = date('Y-m-d H:i:s'); // Obtiene la fecha y hora actual
        $sql2 = "INSERT INTO asistencia (dni, tipo, fecha) VALUES (?, ?, ?)";
        $stmt2 = $conexion->prepare($sql2); // Prepara la consulta de inserción
        
        if (!$stmt2) {
            die('Error en segundo prepare: ' . $conexion->error); // Maneja errores en la preparación de la consulta de inserción
        }
        
        $stmt2->bind_param("sss", $dni, $tipo, $fecha); // Asocia los parámetros a la consulta de inserción
        
        if ($stmt2->execute()) {
            header("Location: ../index.php?msg={$tipo}_ok"); // Redirige con mensaje de éxito
        } else {
            header("Location: ../index.php?msg=error_registro"); // Redirige con mensaje de error en el registro
        }
        $stmt2->close(); // Cierra el statement de inserción
    } else {
        header("Location: ../index.php?msg=empleado_no_encontrado"); // Redirige si el empleado no existe
    }
    $stmt->close(); // Cierra el statement de verificación
    $conexion->close(); // Cierra la conexión a la base de datos
} else {
    header("Location: ../index.php?msg=empleado_no_encontrado"); // Redirige si faltan datos o el tipo es incorrecto
}
?>
