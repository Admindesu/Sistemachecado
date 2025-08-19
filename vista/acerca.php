<?php
session_start();
if (empty($_SESSION['nombre']) and empty($_SESSION['apellido'])) {
    header('location:login/login.php');
    exit; // Importante añadir exit después de un header redirect
}

// Ahora que verificaste la sesión, puedes incluir los archivos de diseño
require('./layout/topbar.php');
require('./layout/sidebar.php');
?>

<style>
ul li:nth-child(4) .activo {
    background: rgb(171, 11, 61) !important;
}
</style>
<div class="page-content">

    <h4 class="text-center text-secondary">Acerca de</h4>
    <div class="container">
        <p class="text-justify">
            Este sistema de gestión de asistencia y control de empleados está diseñado para la Secretaría de Ecología y Medio Ambiente del Estado de Quintana Roo. Facilita el registro y seguimiento de la asistencia del personal, permitiendo a los administradores gestionar usuarios, registrar asistencias y generar reportes.
        </p>
        <p class="text-justify">
            Desarrollado por: Dirección de Tecnología e Información de la Secretaría de Ecología y Medio Ambiente de Quintana Roo.
        </p>
        <p class="text-justify">
            Ing. Sistemas Fermín Pérez Soza
        </p>
        <p class="text-justify">
            2025
        </p>
        <div class="text-center mt-4">
            <img src="../public/img-inicio/SEMA.png" alt="Logo SEMA" style="max-width:300px;">
        </div>
    </div>

</div>
</div>

<?php require('./layout/footer.php'); ?>