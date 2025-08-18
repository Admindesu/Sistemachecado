<?php
session_start();

// Redirigir si no hay sesión
if (!isset($_SESSION['dni'])) {
    header("Location: vista/login/login.php");
    exit();
}

$numeroEmpleado = $_SESSION['dni'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagina de bienvenida</title>
    <link rel="shortcut icon" href="public/app/publico/img/favicon.ico">
    <link rel="stylesheet" href="public/estilos/estilos.css">
    <style>
        /* Estilos responsive */
        @media screen and (max-width: 768px) {
            .container {
                width: 90%;
                margin: 20px auto;
                padding: 15px;
            }
            
            input[type="text"] {
                width: 100%;
                margin: 10px 0;
            }
            
            .botones {
                display: flex;
                flex-direction: column;
                gap: 10px;
                width: 100%;
            }
            
            .entrada, .salida {
                width: 100%;
                padding: 12px;
                font-size: 16px;
            }
            
            h1 {
                font-size: 24px;
                padding: 0 15px;
                text-align: center;
            }
            
            h2#fecha {
                font-size: 18px;
                text-align: center;
            }
            
            .numeroempleado {
                font-size: 16px;
                text-align: center;
            }
            
            .alert {
                margin: 10px;
                padding: 10px;
                border-radius: 5px;
                text-align: center;
            }
            
            .acceso {
                display: block;
                width: 100%;
                text-align: center;
                margin: 10px 0;
                padding: 10px;
            }
        }
        
        /* Estilos generales mejorados */
        .alert {
            margin: 20px auto;
            max-width: 90%;
            padding: 15px;
            border-radius: 5px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div style="position: absolute; top: 15px; right: 15px;">
        <form action="controlador/controlador_cerrar_sesion.php" method="POST" style="display:inline;">
            <button type="submit" style="padding:4px 10px; font-size:12px; border-radius:5px; background:#e74c3c; color:#fff; border:none; cursor:pointer;">
                Cerrar sesión
            </button>
        </form>
    </div>
    <h1>BIENVENIDOS, REGISTRA TU ASISTENCIA</h1>
    <h2 id="fecha"> </h2>
    <div class="container">
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
            <a class="acceso" href="vista/inicio.php">Ingresar al sistema</a>
        <?php endif; ?>
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
    <div style="width:100%; text-align:center; margin-top:30px;">
        <form action="vista/reportes/pdf_report.php" method="POST" target="_blank" style="display:inline-block;">
            <input type="hidden" name="empleado" value="<?= htmlspecialchars($numeroEmpleado) ?>">
            <button type="submit" style="padding:10px 25px; font-size:16px; border-radius:5px; background:#3498db; color:#fff; border:none; cursor:pointer;">
                Descargar mi reporte de asistencias
            </button>
        </form>
    </div>
</body>
</html>