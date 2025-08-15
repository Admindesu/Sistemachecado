<?php

session_start();

if(!empty($_POST['btningresar'])) {
    if(!empty($_POST['usuario']) and !empty($_POST['password'])) {
        $usuario= $_POST["usuario"];
        $password= md5($_POST["password"]);
        // Verifica que $conexion esté definido
        if (!isset($conexion)) {
            include "../modelo/conexion.php";
        }
        $sql= $conexion->query("SELECT * FROM empleado WHERE usuario='$usuario' AND password='$password'");
        if ($sql && ($datos=$sql->fetch_object())) {
            if ($datos->is_admin == 1) {
                $_SESSION['nombre'] = $datos->nombre;
                $_SESSION['apellido'] = $datos->apellido;
                $_SESSION['id_usuario'] = $datos->id_empleado;
                header("location: ../inicio.php");
            } else {
                echo "<div class='alert alert-danger'>Acceso solo permitido para administradores.</div>";
            }
        } else {
            echo "<div class = 'alert alert-danger'> Usuario no existe o error en la consulta</div>";
        }
    } else {
        echo "<div class = 'alert alert-danger'> Los campos estan vacios</div>";
    }
}

?>