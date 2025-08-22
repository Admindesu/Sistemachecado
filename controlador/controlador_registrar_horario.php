<?php
session_start();
include_once("../modelo/conexion.php");

if (isset($_POST['btnregistrar'])) {
    // Validar que todos los campos requeridos estén presentes
    if (empty($_POST['nombre']) || empty($_POST['hora_entrada']) || 
        empty($_POST['hora_salida']) || !isset($_POST['tolerancia_entrada']) || 
        !isset($_POST['limite_retardo'])) {
        
        echo "<script>
            alert('Todos los campos son obligatorios');
            window.location.href = '../vista/horarios.php';
        </script>";
        exit;
    }

    // Sanitizar y validar inputs
    $nombre = filter_var($_POST['nombre'], FILTER_SANITIZE_STRING);
    $hora_entrada = $_POST['hora_entrada'];
    $hora_salida = $_POST['hora_salida'];
    $tolerancia = filter_var($_POST['tolerancia_entrada'], FILTER_SANITIZE_NUMBER_INT);
    $limite_retardo = filter_var($_POST['limite_retardo'], FILTER_SANITIZE_NUMBER_INT);
    $descripcion = filter_var($_POST['descripcion'], FILTER_SANITIZE_STRING);

    // Validar formato de hora
    if (!preg_match("/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/", $hora_entrada) ||
        !preg_match("/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/", $hora_salida)) {
        echo "<script>
            alert('Formato de hora inválido');
            window.location.href = '../vista/horarios.php';
        </script>";
        exit;
    }

    // Validar que la hora de salida sea posterior a la de entrada
    if (strtotime($hora_entrada) >= strtotime($hora_salida)) {
        echo "<script>
            alert('La hora de salida debe ser posterior a la hora de entrada');
            window.location.href = '../vista/horarios.php';
        </script>";
        exit;
    }

    // Validar rangos de tolerancia y retardo
    if ($tolerancia < 0 || $tolerancia > 60 || $limite_retardo < 0 || $limite_retardo > 60 || $limite_retardo <= $tolerancia) {
        echo "<script>
            alert('Los valores de tolerancia y límite de retardo son inválidos');
            window.location.href = '../vista/horarios.php';
        </script>";
        exit;
    }

    try {
        // Preparar la consulta
        $query = "INSERT INTO horarios (nombre, hora_entrada, hora_salida, tolerancia_entrada, limite_retardo, descripcion) 
                 VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("ssssis", $nombre, $hora_entrada, $hora_salida, $tolerancia, $limite_retardo, $descripcion);
        
        if ($stmt->execute()) {
            echo "<script>
                alert('Horario registrado correctamente');
                window.location.href = '../vista/horarios.php';
            </script>";
        } else {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo "<script>
            alert('Error al registrar el horario: " . $e->getMessage() . "');
            window.location.href = '../vista/horarios.php';
        </script>";
    }
    
} else {
    // Si no se envió el formulario, redirigir
    header('Location: ../vista/horarios.php');
    exit;
}
