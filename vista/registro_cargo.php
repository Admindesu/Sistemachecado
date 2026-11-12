<?php
require_once __DIR__ . '/../controlador/auth.php';
requerir_admin();

include "../modelo/conexion.php";
?>
<!-- primero se carga el topbar -->
<?php require('./layout/topbar.php'); ?>
<!-- luego se carga el sidebar -->
<?php require('./layout/sidebar.php'); ?>
<!-- Incluir jQuery y PNotify -->   

<?php
// Incluir el controlador después de cargar PNotify
include "../controlador/controlador_registrar_cargo.php";
?>




<!-- inicio del contenido principal -->
<div class="page-content">
    <div class="container-fluid">
        <h4 class="text-center text-secondary">REGISTRO DE CARGO</h4>
        
        <div class="row">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Nuevo Cargo</h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
<?= campo_csrf() ?>
                            <div class="form-group">
                                <label for="txtnombre">Nombre del Cargo</label>
                                <input type="text" class="form-control" name="txtnombre" required>
                            </div>
                            
                            <div class="mt-4 text-center">
                                <button type="submit" class="btn btn-primary" name="btnregistrar" value="ok">Registrar</button>
                                <a href="organigrama.php" class="btn btn-secondary">Volver</a>
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