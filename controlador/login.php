<?php
session_start(); // Inicia la sesión para manejar variables de sesión del usuario

// Verifica si el botón de ingresar fue presionado
if(!empty($_POST['btningresar'])) {
    // Verifica que los campos usuario y password no estén vacíos
    if(!empty($_POST['usuario']) && !empty($_POST['password'])) {
        $usuario = $_POST["usuario"];
        $password = md5($_POST["password"]); // Encripta la contraseña usando md5 (no recomendado para producción)

        // Incluye el archivo de conexión si la variable $conexion no está definida
        if (!isset($conexion)) {
            include "../modelo/conexion.php";
        }

        // Prepara la consulta SQL para buscar el usuario en la base de datos
        $stmt = $conexion->prepare("SELECT id_empleado, nombre, apellido, dni, is_admin FROM empleado WHERE usuario = ? AND password = ?");
        if (!$stmt) {
            die('Error en la preparación de la consulta: ' . $conexion->error); // Maneja errores de preparación
        }

        // Asocia los parámetros usuario y password a la consulta preparada
        $stmt->bind_param("ss", $usuario, $password);
        $stmt->execute(); // Ejecuta la consulta
        $result = $stmt->get_result(); // Obtiene el resultado de la consulta

        // Si se encontró un usuario, guarda los datos en la sesión
        if ($result && $datos = $result->fetch_object()) {
            $_SESSION['id_usuario'] = $datos->id_empleado;
            $_SESSION['nombre'] = $datos->nombre;
            $_SESSION['apellido'] = $datos->apellido;
            $_SESSION['dni'] = $datos->dni;
            $_SESSION['is_admin'] = $datos->is_admin;

            // Verifica que la sesión se guardó correctamente y redirige al index
            if(isset($_SESSION['dni'])) {
                header("Location: ../index.php");
                exit();
            }
        } else {
            // Muestra mensaje de error si el usuario o contraseña son incorrectos
            echo "<div class='alert alert-danger'>Usuario o contraseña incorrectos</div>";
        }
        $stmt->close(); // Cierra el statement preparado
    } else {
        // Muestra mensaje de error si los campos están vacíos
        echo "<div class='alert alert-danger'>Los campos están vacíos</div>";
    }
}
?>