<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Inicio de sesión</title>
    
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link href="https://fonts.googleapis.com/css?family=Poppins:600&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="../../public/app/publico/img/favicon.ico">
</head>

<body>
    <!-- Fondo armado con CSS (antes era la imagen img/grecas.png) -->
    <div class="fondo" aria-hidden="true">
        <div class="fondo-lienzo">
            <div class="fondo-curva"></div>
            <img class="fondo-q" src="img/Qlogo.svg" alt="">
        </div>
        <div class="fondo-barra">
            <img class="fondo-logo" src="img/logo_blanco.png" alt="IQTP - Instituto Quintanarroense de Transparencia para el Pueblo">
        </div>
    </div>
    <div class="container d-flex justify-content-end">
        <div class="login-content" style="margin-top: -80px;">
            <form method="POST" action="../../controlador/login.php">
                <img src="img/caracol rojo.png">
                <h2 class="title">BIENVENIDO</h2>
                <?php
                $errores_login = [
                    'credenciales' => 'Usuario o contraseña incorrectos',
                    'vacio' => 'Escribe tu usuario y contraseña',
                ];
                if (isset($_GET['error'], $errores_login[$_GET['error']])): ?>
                    <div class="alert alert-danger"><?= $errores_login[$_GET['error']] ?></div>
                <?php endif; ?>

                <div class="input-div one">
                    <div class="i">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="div">
                        <h5>Usuario</h5>
                        <input type="text" name="usuario" class="input" required>
                    </div>
                </div>
                <div class="input-div pass">
                    <div class="i">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="div">
                        <h5>Contraseña</h5>
                        <input type="password" name="password" class="input" required>
                    </div>
                </div>
                <input name="btningresar" class="btn" type="submit" value="INICIAR SESIÓN">
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
