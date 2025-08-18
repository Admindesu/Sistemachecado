<?php
session_start();
if (empty($_SESSION['nombre']) || empty($_SESSION['apellido'])) {
    header('location: vista/login.php');
    exit;
}

// Verifica si el usuario no es admin y cierra la sesión
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] != 1) {
    session_destroy();
    header('location: vista/login.php');
    exit;
}
?>
<style>
ul li:nth-child(4) .activo {
    background: rgb(11, 150, 214) !important;
}

</style>

<!-- primero se carga el topbar -->
<?php require('./layout/topbar.php'); ?>
<!-- luego se carga el sidebar -->
<?php require('./layout/sidebar.php'); ?>

<!-- inicio del contenido principal -->
<div class="page-content">

    <h4 class= "text-center text-secondary">Registro de cargos</h4>
<?php
include '../modelo/conexion.php';
include "../controlador/controlador_registrar_cargo.php";
?>


<div class="row">
<form action="" method="POST">
    <div class="fl-flex-label mb-4 px-2 col-12 ">
        
            <label for="nombre">Nombre</label>
            <input type="text" class="input input__text" name="txtnombre" >
        
    </div>
    <div class="text-right p-2">
        <a href="cargo.php" class="btn btn-secondary btn-rounded">Atras</a>
        <button type="submit" value="ok" name="btnregistrar" class="btn btn-primary btn-rounded">Registrar</button>
    </div>
</form>

</div>


</div>
</div>
<!-- fin del contenido principal -->


<!-- por ultimo se carga el footer -->
<?php require('./layout/footer.php'); ?>