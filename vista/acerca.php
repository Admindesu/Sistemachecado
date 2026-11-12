<?php
require_once __DIR__ . '/../controlador/auth.php';
requerir_admin();

// Ahora que verificaste la sesión, puedes incluir los archivos de diseño
require('./layout/topbar.php');
require('./layout/sidebar.php');
?>

<div class="page-content">

    <h4 class="text-center text-secondary">Acerca de</h4>
    <div class="container">
        <p class="text-justify">
            Este sistema de gestión de asistencia y control de empleados está diseñado para la Secretaría de Ecología y Medio Ambiente del Estado de Quintana Roo. Facilita el registro y seguimiento de la asistencia del personal, permitiendo a los administradores gestionar usuarios, registrar asistencias y generar reportes.
        </p>
        <p class="text-justify">
            Desarrollado por: Ing. Francisco Daniel Paredes Moreno. <br> Ing. en Sistemas Fermín Pérez Soza
        </p>
        <p class="text-justify">
            2025
        </p>
        <div class="text-center mt-4">
            <img src="../public/img-inicio/logo.png" alt="Logo IQTP" style="max-width:300px;">
        </div>
    </div>

</div>
</div>

<?php require('./layout/footer.php'); ?>