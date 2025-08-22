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

    <h4 class= "text-center text-secondary">Registro de empleados</h4>
<?php
include '../modelo/conexion.php';
include "../controlador/controlador_registrar_empleado.php";

// Obtener lista de horarios disponibles
$sql_horarios = $conexion->query("SELECT * FROM horarios ORDER BY nombre ASC");
?>

<div class="row">
<form action="" method="POST">
    <div class="fl-flex-label mb-4 px-2 col-12 col-md-6">
            <label for="nombre">Nombre</label>
            <input type="text" class="input input__text" name="txtnombre" required>
        
    </div>
    <div class="fl-flex-label mb-4 px-2 col-12 col-md-6">
       
            <label for="apellido">Apellido</label>
            <input type="text" class="input input__text" name="txtapellido" >
        
    </div>

    <div class="fl-flex-label mb-4 px-2 col-12 col-md-6">
       
            <label for="dni">NoEmpleado</label>
            <input type="text" class="input input__text" name="txtdni" >
        
    </div>

    <div class="fl-flex-label mb-4 px-2 col-12 col-md-6">
        <label for="cargo">Cargo</label>
        <select name="txtcargo" class="input input__select">
            <option value="">Seleccionar...</option>
            <?php
            $sqlCargo = $conexion->query("SELECT * FROM cargo");
            while ($datosCargo = $sqlCargo->fetch_object()) { ?>
                <option value="<?= $datosCargo->id_cargo ?>"><?= $datosCargo->nombre ?></option>
            <?php } ?>
        </select>
    </div>
 
    <div style="display: flex; flex-direction: column; align-items: flex-start;" class="fl-flex-label mb-4 px-2 col-12 col-md-6">
        <label for="direccion" style="align-self: flex-start;">Dirección</label>
        <select name="txtdireccion" class="input input__select w-100">
            <option value="">Seleccionar...</option>
            <?php
            $sqlDireccion = $conexion->query("SELECT * FROM direccion");
            while ($datosDireccion = $sqlDireccion->fetch_object()) { ?>
                <option value="<?= $datosDireccion->id_direccion ?>"><?= $datosDireccion->nombre ?></option>
            <?php } ?>
        </select>
    </div>

    <div style="display: flex; flex-direction: column; align-items: flex-start;" class="fl-flex-label mb-4 px-2 col-12 col-md-6">
        <label for="subsecretaria" style="align-self: flex-start;">Subsecretaría</label>
        <select name="txtsubsecretaria" class="input input__select w-100">
            <option value="">Seleccionar...</option>
            <?php
            $sqlSubsecretaria = $conexion->query("SELECT * FROM subsecretaria");
            while ($datosSubsecretaria = $sqlSubsecretaria->fetch_object()) { ?>
                <option value="<?= $datosSubsecretaria->id_subsecretaria ?>"><?= $datosSubsecretaria->nombre ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="fl-flex-label mb-4 px-2 col-12 col-md-6">
        <label for="usuario">Usuario</label>
        <input type="text" class="input input__text" name="txtusuario" required>
    </div>
    <div class="fl-flex-label mb-4 px-2 col-12 col-md-6">
        <label for="password">Contraseña</label>
        <input type="password" class="input input__text" name="txtpassword" required placeholder="Ingrese contraseña">
    </div>
    </div>
    <div style="display: flex; flex-direction: column; align-items: flex-start;" class="fl-flex-label mb-4 px-2 col-12 col-md-6">
        <label for="horario" style="align-self: flex-start;">Horario Laboral</label>
        <select name="txthorario" class="input input__select w-100" required>
            <option value="">Seleccionar...</option>
            <?php
            while ($horario = $sql_horarios->fetch_object()) { ?>
                <option value="<?= $horario->id_horario ?>"><?= $horario->nombre ?> (<?= date('h:i A', strtotime($horario->hora_entrada)) ?> - <?= date('h:i A', strtotime($horario->hora_salida)) ?>)</option>
            <?php } ?>
        </select>
    </div>
    
    <div class="fl-flex-label mb-4 px-2 col-12 md-6">
        <label for="is_admin">Administrador</label>
        <input type="checkbox" name="is_admin" id="is_admin" value="1">
        <span style="margin-left:8px;">¿Habilitar como administrador?</span>
    </div>
    
    
    <div class="text-right p-2 mt-4 col-12">
        <a href="empleado.php" class="btn btn-secondary btn-rounded">Atras</a>
        <button type="submit" value="ok" name="btnregistrar" class="btn btn-primary btn-rounded">Registrar</button>
    </div>
</form>

</div>


</div>
</div>
<!-- fin del contenido principal -->


<!-- por ultimo se carga el footer -->
<?php require('./layout/footer.php'); ?>