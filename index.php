<?php
session_start();

// Redirigir si no hay sesión
if (!isset($_SESSION['dni'])) {
    header("Location: vista/login/login.php");
    exit();
Un par de dobles comillas (") impedirá que el shell interprete cualquier metacarácter. ¿Verdadero o falso?}

$numeroEmpleado = $_SESSION['dni'];
$nombreCompleto = isset($_SESSION['nombre']) && isset($_SESSION['apellido']) ? 
    $_SESSION['nombre'] . ' ' . $_SESSION['apellido'] : '';

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

        body {
            background-image: url('vista/login/img/SEMA.PNG');
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            position: relative;
            
        }

        /* Actualiza el fondo del contenedor para mejor contraste */
        .container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

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
            
            .clock-container {
                text-align: center;
                padding: 10px;
                margin-bottom: 20px;
                height: 10vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .clock {
                background: #2c3e50;
                padding: 15px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                font-family: 'Arial', sans-serif;
                font-size: 24px;
                font-weight: bold;
                text-shadow: 0 2px 4px rgba(0,0,0,0.2);
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                white-space: nowrap;
            }

            .clock span {
                display: inline-block;
                min-width: 35px;
            }

            .clock .colon {
                min-width: 15px;
                text-align: center;
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
        
        .clock-container {
            text-align: center;
            padding: 20px;
            margin-bottom: 30px;
        }

        .clock {
            background: #2c3e50;
            padding: 20px 30px;
            border-radius: 8px;
            display: inline-block;
            color: #fff;
            font-family: 'Arial', sans-serif;
            font-size: 48px;
            font-weight: bold;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .clock span {
            display: inline-block;
            min-width: 60px;
        }

        .clock .colon {
            opacity: 1;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            50% { opacity: 0; }
        }
    </style>
</head>
<body>
    <h1>BIENVENIDO <?= strtoupper($nombreCompleto) ?>, REGISTRA TU ASISTENCIA</h1>
    <div class="container">
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
            <button type="button" onclick="window.location.href='vista/inicio.php'" 
                    style="padding:10px 25px; font-size:16px; border-radius:5px; background:#3498db; color:#fff; border:none; cursor:pointer; width:100%; margin-bottom:20px;">
                Ingresar al sistema
            </button>
            
        <?php endif; ?>
                    <form action="controlador/controlador_cerrar_sesion.php" method="POST">
                <button type="submit" 
                        style="padding:10px 25px; font-size:16px; border-radius:5px; background:#721c24; color:#fff; border:none; cursor:pointer; width:100%; margin-bottom:20px;">
                    Cerrar sesión
                </button>
            </form>
        
        <!-- Reloj Digital -->
        <div class="clock-container">
            <div class="clock">
                <span id="hours">00</span>
                <span class="colon">:</span>
                <span id="minutes">00</span>
                <span class="colon">:</span>
                <span id="seconds">00</span>
            </div>
        </div>

        <form action="controlador/controlador_registrar_entrada_salida.php" method="POST">
            <input type="hidden" name="txtEmpleado" value="<?= htmlspecialchars($numeroEmpleado) ?>">
            <div class="botones">
                <button type="submit" name="tipo" value="entrada" 
                        style="padding:10px 25px; font-size:16px; border-radius:5px; background:rgb(171, 11, 61); color:#fff; border:none; cursor:pointer; width:100%; margin-bottom:10px;">
                    Entrada
                </button>
                <button type="submit" name="tipo" value="salida" 
                        style="padding:10px 25px; font-size:16px; border-radius:5px; background:#3498db; color:#fff; border:none; cursor:pointer; width:100%; margin-bottom:10px;">
                    Salida
                </button>
            </div>
        </form>
    </div>
    <script>
        // Reloj
        function actualizarReloj() {
            const ahora = new Date();
            const horas = String(ahora.getHours()).padStart(2, '0');
            const minutos = String(ahora.getMinutes()).padStart(2, '0');
            const segundos = String(ahora.getSeconds()).padStart(2, '0');
            document.getElementById('hours').textContent = horas;
            document.getElementById('minutes').textContent = minutos;
            document.getElementById('seconds').textContent = segundos;
        }
        setInterval(actualizarReloj, 1000);
        actualizarReloj();
    </script>
    <?php if (isset($_GET['msg'])): ?>
        <?php 
        $msg = $_GET['msg'];
        switch ($msg) {
            case 'entrada_atiempo':
                echo '<div class="alert alert-success">
                    <strong>¡Bienvenido!</strong> Has registrado tu entrada A TIEMPO.
                </div>';
                break;
            case 'entrada_retardo':
                echo '<div class="alert alert-warning">
                    <strong>¡Atención!</strong> Has registrado tu entrada con RETARDO. 
                    Procura llegar más temprano mañana.
                </div>';
                break;
            case 'entrada_falta':
                echo '<div class="alert alert-danger">
                    <strong>¡Importante!</strong> Tu entrada ha sido registrada como FALTA 
                    debido al tiempo de llegada. Por favor, habla con tu supervisor.
                </div>';
                break;
            case 'salida_ok':
                echo '<div class="alert alert-success">
                    <strong>¡Hasta pronto!</strong> Salida registrada correctamente.
                </div>';
                break;
            case 'salida_falta':
                echo '<div class="alert alert-danger">
                    <strong>¡Atención!</strong> Tu salida ha sido registrada como FALTA 
                    debido a que excediste el límite de hora de Salida permitido. 
                    Por favor, habla con tu supervisor.
                </div>';
                break;
            case 'no_entrada':
                echo '<div class="alert alert-warning">
                    <strong>¡Atención!</strong> No hay entrada registrada para marcar salida.
                </div>';
                break;
            case 'empleado_no_encontrado':
                echo '<div class="alert alert-danger">
                    <strong>Error:</strong> Empleado no encontrado.
                </div>';
                break;
            case 'salida_temprana':
                echo '<div class="alert alert-warning">
                    <strong>¡Atención!</strong> No puedes registrar salida antes de la hora establecida en tu horario.
                </div>';
                break;
            case 'entrada_anticipada':
                echo '<div class="alert alert-warning">
                    <strong>¡Atención!</strong> Solo puedes registrar entrada 15 minutos antes de tu horario.
                </div>';
                break;
        }
        ?>
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