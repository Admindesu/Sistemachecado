<?php
session_start();
$numeroEmpleado = isset($_SESSION['dni']) ? $_SESSION['dni'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagina de bienvenida</title>
    <link rel="stylesheet" href="public/estilos/estilos.css">
</head>
<body>
    <h1>BIENVENIDOS, REGISTRA TU ASISTENCIA</h1>
    <h2 id="fecha"> </h2>
<div class="container">
    <a class="acceso" href="vista/login/login.php"> Ingresar al sistema </a>
    <p class="numeroempleado">Ingrese su Numero de empleado</p>
    <form action="controlador/controlador_registrar_entrada_salida.php" method="POST">
        <input type="text" placeholder="NumeroDeEmpleado" name="txtEmpleado" value="<?= htmlspecialchars($numeroEmpleado) ?>" readonly>
        <div class="botones">
            <button class="entrada" type="submit" name="tipo" value="entrada">Entrada</button>
            <button class="salida" type="submit" name="tipo" value="salida">Salida</button>
        </div>
    </form>
 </div>
    <script>
        setInterval(() => {
            let fecha = new Date();
            let fechaHora= fecha.toLocaleString();
            document.getElementById("fecha").textContent = fechaHora;
        }, 1000);
    </script>
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'entrada_ok'): ?>
    <div class="alert alert-success">Entrada registrada correctamente.</div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'salida_ok'): ?>
    <div class="alert alert-success">Salida registrada correctamente.</div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'no_entrada'): ?>
    <div class="alert alert-warning">No hay entrada registrada para marcar salida.</div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'empleado_no_encontrado'): ?>
    <div class="alert alert-danger">Empleado no encontrado.</div>
    <?php endif; ?>
</body>
</html>