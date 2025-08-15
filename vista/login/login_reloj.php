<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link href="https://fonts.googleapis.com/css?family=Poppins:600&display=swap" rel="stylesheet">
    <link href="https://tresplazas.com/web/img/big_punto_de_venta.png" rel="shortcut icon">
    <title>Inicio de sesión</title>
</head>

<body>
    <img class="wave" src="img/grecas.png">
    <div class="container d-flex justify-content-end">
        <div class="login-content" style="margin-top: -80px;">
            <form method="POST" action="">
                <img src="img/caracol rojo.png">
                <h2 class="title">BIENVENIDO</h2>
                <?php
                include "../../modelo/conexion.php";
                // Copia de login.php pero redirige a index.php
                if(!empty($_POST['btningresar'])) {
                    if(!empty($_POST['usuario']) && !empty($_POST['password'])) {
                        $usuario= $_POST["usuario"];
                        $password= md5($_POST["password"]);
                        $sql= $conexion->query("SELECT * FROM empleado WHERE usuario='$usuario' AND password='$password'");
                        if ($sql && ($datos=$sql->fetch_object())) {
                            $_SESSION['nombre'] = $datos->nombre;
                            $_SESSION['apellido'] = $datos->apellido;
                            $_SESSION['id_usuario'] = $datos->id_empleado;
                            $_SESSION['dni'] = $datos->dni;
                            header("location: ../../index.php");
                        } else {
                            echo "<div class='alert alert-danger'>Usuario o contraseña incorrectos</div>";
                        }
                    } else {
                        echo "<div class='alert alert-danger'>Los campos están vacíos</div>";
                    }
                }
                ?>
                <div class="input-div one">
                    <div class="i">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="div">
                        <h5>Usuario</h5>
                        <input id="usuario" type="text"
                            class="input" name="usuario"
                            title="ingrese su nombre de usuario" autocomplete="usuario" value="">
                    </div>
                </div>
                <div class="input-div pass">
                    <div class="i">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="div">
                        <h5>Contraseña</h5>
                        <input type="password" id="input" class="input"
                            name="password" title="ingrese su clave para ingresar" autocomplete="current-password">
                    </div>
                </div>
                <div class="view">
                    <div class="fas fa-eye verPassword" onclick="vista()" id="verPassword"></div>
                </div>
                <div class="text-center">
                    <a class="font-italic isai5" href="">Olvidé mi contraseña</a>
                </div>
                <input name="btningresar" class="btn" title="click para ingresar" type="submit"
                    value="INICIAR SESION">
            </form>
        </div>
    </div>
    <script src="js/fontawesome.js"></script>
    <script src="js/main.js"></script>
    <script src="js/main2.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.js"></script>
    <script src="js/bootstrap.bundle.js"></script>
</body>

</html>