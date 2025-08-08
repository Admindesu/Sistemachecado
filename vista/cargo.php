<?php
   session_start();
   if (empty($_SESSION['nombre']) and empty($_SESSION['apellido'])) {
       header('location:login/login.php');
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

    <h4 class= "text-center text-secondary">Lista de Cargos</h4>

<?php
include "../modelo/conexion.php";
// Consulta para obtener los datos de asistencia, empleado y cargo
// Se utiliza INNER JOIN para combinar las tablas asistencia, empleado y cargo
$sql= $conexion->query("SELECT * FROM CARGO");

 
?>
<a href="registro_cargo.php" class="btn btn-primary btn-rounded mb-3"><i class="fas fa-plus"></i> Agregar</a>
    <table class="table table-bordered table-hover col-12" id="example">
  <thead>
    <tr>
      <th scope="col">ID</th>
      <th scope="col">Nombre</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php
     while ($datos = $sql->fetch_object()) { ?>
      <tr>
        <td><?= $datos-> id_cargo ?></td>
        <td><?= $datos-> nombre ?></td>
      <td>
        <a href="" data-toggle="modal" data-target="#exampleModal<?= $datos->id_cargo ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Editar</a>
<a href="usuario.php?id=<?= $datos-> id_cargo ?>" onclick="advertencia(event)" class="btn btn-danger"><i class="fas fa-exclamation-triangle"></i> Eliminar</a>

      </td>
    </tr>


<!-- Modal -->
<div class="modal fade" id="exampleModal<?= $datos->id_cargo ?>" tabindex="-1" aria-labelledby="exampleModalLabel<?= $datos->id_cargo ?>" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modificar Usuario</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="" method="POST">
          <div hidden class="fl-flex-label mb-4 px-2 col-12">
        
            <label for="ID">ID</label>
            <input type="text" class="input input__text" name="txtid" value="<?= $datos-> id_cargo ?>" >
        
    </div>
    <div class="fl-flex-label mb-4 px-2 col-12">
        
            <label for="nombre">Nombre</label>
            <input type="text" class="input input__text" name="txtnombre" value="<?= $datos-> nombre ?>" >
        
    </div>
    <div class="fl-flex-label mb-4 px-2 col-12">
       
            <label for="apellido">Apellido</label>
            <input type="text" class="input input__text" name="txtapellido" value="<?= $datos-> apellido ?>" >
        
    </div>
    <div class="fl-flex-label mb-4 px-2 col-12">
        
            <label for="usuario">Usuario</label>
            <input type="text" class="input input__text" name="txtusuario" value="<?= $datos-> usuario ?>">
        
    </div>
    <div class="text-right p-2">
        <a href="usuario.php" class="btn btn-secondary btn-rounded">Atras</a>
        <button type="submit" value="ok" name="btnregistrar" class="btn btn-primary btn-rounded">Registrar</button>
    </div>
</form>
      </div>
      <div class="modal-footer">
  
      </div>
    </div>
  </div>
</div>
     <?php } 
     
     ?>
   
  </tbody>
</table>

</div>
</div>
<!-- fin del contenido principal -->


<!-- por ultimo se carga el footer -->
<?php require('./layout/footer.php'); ?>