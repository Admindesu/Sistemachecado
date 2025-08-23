<?php
session_start();
if (empty($_SESSION['nombre']) || empty($_SESSION['apellido'])) {
    header('location: login/login.php');
    exit;
}

// Verifica si el usuario no es admin y cierra la sesión
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] != 1) {
    session_destroy();
    header('location: login/login.php');
    exit;
}
require('./layout/topbar.php');
require('./layout/sidebar.php');
// Incluye la conexión a la base de datos
include "../modelo/conexion.php";

// Incluye el controlador DESPUÉS de la conexión pero ANTES de cualquier HTML
include "../controlador/controlador_registrar_direccion.php";

// Ahora carga los componentes de layout

?>

<style>
ul li:nth-child(3) .activo {
    background: rgb(171, 11, 61) !important;
}
</style>

<!-- inicio del contenido principal -->
<div class="page-content">
    <div class="container-fluid">
        <h4 class="text-center text-secondary">REGISTRO DE DIRECCIÓN</h4>
        
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Nueva Dirección</h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="txtnombre">Nombre de la Dirección</label>
                                <input type="text" class="form-control" name="txtnombre" required>
                            </div>
                            
                            <div class="mt-4 text-center">
                                <!-- Añadido value="ok" al botón -->
                                <button type="submit" class="btn btn-primary" name="btnregistrar" value="ok">Registrar</button>
                                <a href="organigrama.php?nav=direccion" class="btn btn-secondary">Volver</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- fin del contenido principal -->

<!-- por ultimo se carga el footer -->
<?php require('./layout/footer.php'); ?>