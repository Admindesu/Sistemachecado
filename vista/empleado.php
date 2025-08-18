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
    background: rgb(171, 11, 61) !important;
}

</style>

<!-- primero se carga el topbar -->
<?php require('./layout/topbar.php'); ?>
<!-- luego se carga el sidebar -->
<?php require('./layout/sidebar.php'); ?>

<!-- inicio del contenido principal -->
<div class="page-content">
    <div class="container-fluid">
        <h4 class="text-center text-secondary">Lista de empleados</h4>

        <?php
        include "../modelo/conexion.php";
        include "../controlador/controlador_modificar_empleado.php";
        include "../controlador/controlador_eliminar_empleado.php";

        try {
            $sql = $conexion->query("SELECT 
                empleado.id_empleado,
                empleado.nombre,
                empleado.apellido,
                empleado.dni,
                empleado.usuario,
                empleado.cargo,
                empleado.is_admin,
                cargo.nombre AS nom_cargo
                FROM empleado
                INNER JOIN cargo ON empleado.cargo = cargo.id_cargo
            ");
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Error al cargar los datos: " . $e->getMessage() . "</div>";
        }
        ?>

        <div class="row mb-3">
            <div class="col-12">
                <a href="registro_empleado.php" class="btn btn-primary btn-rounded">
                    <i class="fas fa-plus"></i> Agregar Empleado
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover w-100" id="example">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Apellido</th>
                                <th scope="col">DNI</th>
                                <th scope="col">Usuario</th>
                                <th scope="col">Admin</th>
                                <th scope="col">Cargo</th>                               
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($sql) {
                                while ($datos = $sql->fetch_object()) { 
                            ?>
                                <tr>
                                    <td><?= $datos->id_empleado ?></td>
                                    <td><?= $datos->nombre ?></td>
                                    <td><?= $datos->apellido ?></td>
                                    <td><?= $datos->dni ?></td>
                                    <td><?= $datos->usuario ?></td>
                                    <td>
                                        <input type="checkbox" disabled <?= ($datos->is_admin == 1) ? 'checked' : '' ?>>
                                    </td>
                                    <td><?= $datos->nom_cargo ?></td>
                                    <td>
                                        <a href="" data-toggle="modal" data-target="#exampleModal<?= $datos->id_empleado ?>" class="btn btn-warning">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <a href="empleado.php?id=<?= $datos->id_empleado ?>" onclick="advertencia(event)" class="btn btn-danger">
                                            <i class="fas fa-exclamation-triangle"></i> Eliminar
                                        </a>
                                    </td>
                                </tr>


<!-- Modal -->
<div class="modal fade" id="exampleModal<?= $datos->id_empleado ?>" tabindex="-1" aria-labelledby="exampleModalLabel<?= $datos->id_empleado ?>" aria-hidden="true">
  <div class="modal-dialog modal-lg"><!-- Cambiado a modal-lg para mayor tamaño -->
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
    <div class="fl-flex-label mb-5 px- col-12">
    <label for="usuario">Usuario</label>
    <input type="text" class="input input__text" name="txtusuario" value="<?= $datos->usuario ?>" required>
</div>
<div class="fl-flex-label mb-4 px-2 col-12">
    <label for="password">Contraseña</label>
    <input type="password" class="input input__text" name="txtpassword" placeholder="Ingrese nueva contraseña si desea cambiarla">
</div>
<div class="fl-flex-label mb-4 px-2 col-12">
    <label for="is_admin">Administrador</label>
    <input type="checkbox" name="is_admin" id="is_admin" value="1" <?= ($datos->is_admin == 1) ? 'checked' : '' ?>>
    <span style="margin-left:8px;">Añadir como administrador?</span>
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
     <?php 
                                }
                            } 
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- fin del contenido principal -->


<!-- por ultimo se carga el footer -->
<?php require('./layout/footer.php'); ?>