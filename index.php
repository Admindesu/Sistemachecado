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
    <form action="">    
        <input type="text" placeholder="NumeroDeEmpleado" name="txtEmpleado">
        <div class="botones">
        <a class="entrada" href="">Entrada</a>
        <a class="salida" href="">Salida</a>
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
</body>
</html>