<?php
   session_start();
   if (empty($_SESSION['nombre']) and empty($_SESSION['apellido'])) {
       header('location:login/login.php');
   }

?>

<style>
ul li:nth-child(3) .activo {
    background: rgb(171, 11, 61) !important;
}

</style>

<!-- primero se carga el topbar -->
<?php require('./layout/topbar.php'); ?>
<!-- luego se carga el sidebar -->
<?php require('./layout/sidebar.php'); ?>

<!-- inicio del contenido principal -->
<div class="page-content">

    <h4 class= "text-center text-secondary">Lista de empleados</h4>

<?php
include "../modelo/conexion.php";
include "../controlador/controlador_modificar_empleado.php";
include "../controlador/controlador_eliminar_empleado.php";
// Consulta para obtener los datos de asistencia, empleado y cargo
// Se utiliza INNER JOIN para combinar las tablas asistencia, empleado y cargo
$sql= $conexion->query("SELECT 
empleado.id_empleado,
empleado.nombre,
empleado.apellido,
empleado.cargo,
cargo.nombre AS 'nom_cargo'
 FROM empleado
 INNER JOIN cargo ON empleado.cargo = cargo.id_cargo
 ");

 
?>
<a href="registro_empleado.php" class="btn btn-primary btn-rounded mb-3"><i class="fas fa-plus"></i> Agregar Empleado</a>
    <table class="table table-bordered table-hover col-12" id="example">
  <thead>
    <tr>
      <th scope="col">ID</th>
      <th scope="col">Nombre</th>
      <th scope="col">Apellido</th>
      <th scope="col">nom_cargo</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php
     while ($datos = $sql->fetch_object()) { ?>
      <tr>
    <td><?= $datos-> id_empleado ?></td>
      <td><?= $datos-> nombre ?></td>
      <td><?= $datos-> apellido ?></td>
      <td><?= $datos-> nom_cargo ?></td>
      <td>
        <a href="" data-toggle="modal" data-target="#exampleModal<?= $datos->id_empleado ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Editar</a>
<a href="empleado.php?id=<?= $datos-> id_empleado ?>" onclick="advertencia(event)" class="btn btn-danger"><i class="fas fa-exclamation-triangle"></i> Eliminar</a>

      </td>
    </tr>


<!-- Modal -->
<div class="modal fade" id="exampleModal<?= $datos->id_empleado ?>" tabindex="-1" aria-labelledby="exampleModalLabel<?= $datos->id_empleado ?>" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modificar Empleado</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="" method="POST">
          <div hidden class="fl-flex-label mb-4 px-2 col-12">
        
            <label for="ID">ID</label>
            <input type="text" class="input input__text" name="txtid" value="<?= $datos-> id_empleado ?>" >
        
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
        
            <label for="cargo">Cargo</label>
           <select name="txtcargo" class= "input input__select">
           <?php 
           $sql2= $conexion->query("SELECT * FROM cargo");
           while ($datos2 = $sql2->fetch_object()) { ?>
           <option <?= $datos->cargo==$datos2->id_cargo ?'selected' : '' ?> value ="<?= $datos2->id_cargo ?>"><?= $datos2->nombre ?></option>
           <?php }

?>
           </select>
        
    </div>
    <div class="text-right p-2">
        <a href="empleado.php" class="btn btn-secondary btn-rounded">Atras</a>
        <button type="submit" value="ok" name="btnmodificar" class="btn btn-primary btn-rounded">Registrar</button>
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