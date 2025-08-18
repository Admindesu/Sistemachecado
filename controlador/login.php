<?php
session_start();

if(!empty($_POST['btningresar'])) {
    if(!empty($_POST['usuario']) && !empty($_POST['password'])) {
        $usuario = $_POST["usuario"];
        $password = md5($_POST["password"]);
        
        if (!isset($conexion)) {
            include "../modelo/conexion.php";
        }
        
        $stmt = $conexion->prepare("SELECT id_empleado, nombre, apellido, dni, is_admin FROM empleado WHERE usuario = ? AND password = ?");
        if (!$stmt) {
            die('Error en la preparación de la consulta: ' . $conexion->error);
        }
        
        $stmt->bind_param("ss", $usuario, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $datos = $result->fetch_object()) {
            $_SESSION['id_usuario'] = $datos->id_empleado;
            $_SESSION['nombre'] = $datos->nombre;
            $_SESSION['apellido'] = $datos->apellido;
            $_SESSION['dni'] = $datos->dni;
            $_SESSION['is_admin'] = $datos->is_admin;
            
            // Verifica que la sesión se guardó
            if(isset($_SESSION['dni'])) {
                header("Location: ../index.php");
                exit();
            }
        } else {
            echo "<div class='alert alert-danger'>Usuario o contraseña incorrectos</div>";
        }
        $stmt->close();
    } else {
        echo "<div class='alert alert-danger'>Los campos están vacíos</div>";
    }
}
?>