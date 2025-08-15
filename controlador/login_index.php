<?php
<?php

session_start();

if(!empty($_POST['btningresar'])) {
    if(!empty($_POST['usuario']) and !empty($_POST['password'])) {
        $usuario= $_POST["usuario"];
        $password= md5($_POST["password"]);
        $sql= $conexion->query("SELECT * FROM empleado WHERE usuario='$usuario' AND password='$password'");
        if ($datos=$sql ->fetch_object()){
            $_SESSION['nombre'] = $datos->nombre;
            $_SESSION['apellido'] = $datos->apellido;
            $_SESSION['id_usuario'] = $datos->id_usuario;
            header("location: ../index.php");
        }else{
            echo "<div class = 'alert alert-danger'> Usuario no existe</div>";
        }
    } else {
        echo "<div class = 'alert alert-danger'> Los campos estan vacios</div>";
    }
}

?>