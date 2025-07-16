<?php
   session_start();
   if (empty($_SESSION['nombre']) and empty($_SESSION['apellido'])) {
       header('location:login/login.php');
   }

?>

<style>
ul li:nth-child(2) .activo {
    background: rgb(11, 150, 214) !important;
}

</style>

<!-- primero se carga el topbar -->
<?php require('./layout/topbar.php'); ?>
<!-- luego se carga el sidebar -->
<?php require('./layout/sidebar.php'); ?>

<!-- inicio del contenido principal -->
<div class="page-content">

    <h4 class= "text-center text-secondary">Registro de usuarios</h4>
<div class="row">
<form action="">
    <div class="fl-flex-label mb-4 px-2 col-12 col-md-6">
        
            <label for="nombre">Nombre</label>
            <input type="text" class="input input__text" name="txtnombre" >
        
    </div>
    <div class="fl-flex-label mb-4 px-2 col-12 col-md-6">
       
            <label for="apellido">Apellido</label>
            <input type="text" class="input input__text" name="txtapellido" >
        
    </div>
    <div class="fl-flex-label mb-4 px-2 col-12 col-md-6">
        
            <label for="usuario">Usuario</label>
            <input type="text" class="input input__text" name="txtusuario">
        
    </div>
    <div class="fl-flex-label mb-4 px-2 col-12 col-md-6">
        
            <label for="password">Contraseña</label>
            <input type="password" class="input input__text" name="txtpassword">
        
    </div>
    <div>
        <a href="" class="btn btn-secondary btn-rounded">Atras</a>
    </div>
</form>

</div>


</div>
</div>
<!-- fin del contenido principal -->


<!-- por ultimo se carga el footer -->
<?php require('./layout/footer.php'); ?>