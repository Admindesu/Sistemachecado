<?php
// Iniciar sesión
session_start();
if (empty($_SESSION['nombre']) || empty($_SESSION['apellido'])) {
    header('location: login/login.php');
}

// Verifica si el usuario no es admin y cierra la sesión
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] != 1) {
    header('location: login/login.php');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráficos Estadísticos</title>
    <style>
        ul li:nth-child(5) .activo {
            background: rgb(171, 11, 61) !important;
        }
    </style>
</head>
<body>
    <!-- primero se carga el topbar -->
    <?php require('./layout/topbar.php'); ?>
    <!-- luego se carga el sidebar -->
    <?php require('./layout/sidebar.php'); ?>

    <!-- inicio del contenido principal -->
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <!-- Gráfica de Asistencias por Mes -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Asistencias por Mes</h4>
                            <canvas id="asistenciasMes"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Gráfica de Asistencias por Dirección -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Asistencias por Dirección</h4>
                            <canvas id="asistenciasDireccion"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Incluir Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Función para obtener datos de asistencias por mes
        async function getAsistenciasPorMes() {
            const response = await fetch('../controlador/get_asistencias_mes.php');
            const data = await response.json();
            return data;
        }

        // Función para obtener datos de asistencias por dirección
        async function getAsistenciasPorDireccion() {
            const response = await fetch('../controlador/get_asistencias_direccion.php');
            const data = await response.json();
            return data;
        }

        // Inicializar gráficas
        async function initCharts() {
            // Gráfica de Asistencias por Mes
            const datosMes = await getAsistenciasPorMes();
            new Chart(document.getElementById('asistenciasMes'), {
                type: 'line',
                data: {
                    labels: datosMes.meses,
                    datasets: [{
                        label: 'Asistencias',
                        data: datosMes.cantidades,
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Asistencias Mensuales'
                        }
                    }
                }
            });

            // Gráfica de Asistencias por Dirección
            const datosDireccion = await getAsistenciasPorDireccion();
            new Chart(document.getElementById('asistenciasDireccion'), {
                type: 'bar',
                data: {
                    labels: datosDireccion.direcciones,
                    datasets: [{
                        label: 'Asistencias',
                        data: datosDireccion.cantidades,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Asistencias por Dirección'
                        }
                    }
                }
            });
        }

        // Iniciar las gráficas cuando el documento esté listo
        document.addEventListener('DOMContentLoaded', initCharts);
    </script>

    <?php require('./layout/footer.php'); ?>
</body>
</html>