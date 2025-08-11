<?php

session_start();


if(!empty($_POST['btningresar'])) {
    if(!empty($_POST['usuario']) and !empty($_POST['password'])) {
        $usuario= $_POST["usuario"];
        $password= $_POST["password"];
        
        $sql= $conexion->query("SELECT * FROM usuario WHERE usuario='$usuario'");
        
        if ($datos=$sql ->fetch_object()){
            if (password_verify($password, $datos->password)) {
                $_SESSION["nombre"] = $datos->nombre;
                $_SESSION["apellido"] = $datos->apellido;
                header("location:../inicio.php");
            } else {
                echo '<div class="alert alert-danger">Contraseña incorrecta</div>';
            }
        } else {
            echo '<div class="alert alert-danger">Usuario no existe</div>';
        }
    } else {
        echo '<div class="alert alert-danger">Campos vacíos</div>';
    }
}

?>