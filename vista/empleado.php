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
                empleado.direccion,
                empleado.subsecretaria,
                empleado.is_admin,
                cargo.nombre AS nom_cargo,
                direccion.nombre AS nom_direccion,
                subsecretaria.nombre AS nom_subsecretaria
                FROM empleado
                INNER JOIN cargo ON empleado.cargo = cargo.id_cargo
                INNER JOIN direccion ON empleado.direccion = direccion.id_direccion
                INNER JOIN subsecretaria ON empleado.subsecretaria = subsecretaria.id_subsecretaria
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
                
                <!-- CSV Upload Button and Form -->
                <button type="button" class="btn btn-success btn-rounded ml-2" data-toggle="modal" data-target="#csvUploadModal">
                    <i class="fas fa-file-csv"></i> Importar CSV
                </button>
            </div>
        </div>

        <!-- CSV Upload Modal -->
        <div class="modal fade" id="csvUploadModal" tabindex="-1" aria-labelledby="csvUploadModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="csvUploadModalLabel">Importar Empleados desde CSV</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <a href="../controlador/importar_csv.php?download_template=1" class="btn btn-info btn-sm">
                                <i class="fas fa-download"></i> Descargar plantilla CSV
                            </a>
                        </div>
                        
                        <form action="../controlador/importar_csv.php" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="csvFile">Seleccionar archivo CSV</label>
                                <input type="file" class="form-control-file" id="csvFile" name="csvFile" accept=".csv" required>
                            </div>
                            <div class="alert alert-info">
                                <small>
                                    <strong>Formato del CSV:</strong><br>
                                    nombre,apellido,dni,usuario,password,cargo,direccion,subsecretaria,is_admin<br><br>
                                    <strong>Ejemplo:</strong><br>
                                    Juan,Perez,12345678,jperez,password123,1,1,1,0
                                </small>
                            </div>
                            <button type="submit" class="btn btn-primary">Importar</button>
                        </form>
                    </div>
                </div>
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
                                <th scope="col">Direccion</th>
                                <th scope="col">Subsecretaria</th>
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
                                    <td><?= $datos->nom_direccion ?></td>
                                    <td><?= $datos->nom_subsecretaria ?></td>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Modificar Empleado</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <div class="row">
                        <div class="col-12" hidden>
                            <div class="form-group">
                                <label for="ID">ID</label>
                                <input type="text" class="form-control" name="txtid" value="<?= $datos->id_empleado ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre">Nombre</label>
                                <input type="text" class="form-control" name="txtnombre" value="<?= $datos->nombre ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="apellido">Apellido</label>
                                <input type="text" class="form-control" name="txtapellido" value="<?= $datos->apellido ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cargo">Cargo</label>
                                <select name="txtcargo" class="form-control">
                                    <?php 
                                    $sql2 = $conexion->query("SELECT * FROM cargo");
                                    while ($datos2 = $sql2->fetch_object()) { ?>
                                        <option <?= $datos->cargo == $datos2->id_cargo ? 'selected' : '' ?> 
                                                value="<?= $datos2->id_cargo ?>">
                                            <?= $datos2->nombre ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="subsecretaria">Subsecretaría</label>
                                <select name="txtsubsecretaria" class="form-control">
                                    <?php 
                                    $sql2 = $conexion->query("SELECT * FROM subsecretaria");
                                    while ($datos2 = $sql2->fetch_object()) { ?>
                                        <option <?= $datos->subsecretaria == $datos2->id_subsecretaria ? 'selected' : '' ?> 
                                                value="<?= $datos2->id_subsecretaria ?>">
                                            <?= $datos2->nombre ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="direccion">Dirección</label>
                                <select name="txtdireccion" class="form-control">
                                    <?php 
                                    $sql2 = $conexion->query("SELECT * FROM direccion");
                                    while ($datos2 = $sql2->fetch_object()) { ?>
                                        <option <?= $datos->direccion == $datos2->id_direccion ? 'selected' : '' ?> 
                                                value="<?= $datos2->id_direccion ?>">
                                            <?= $datos2->nombre ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="usuario">Usuario</label>
                                <input type="text" class="form-control" name="txtusuario" value="<?= $datos->usuario ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password">Contraseña</label>
                                <input type="password" class="form-control" name="txtpassword" placeholder="Ingrese nueva contraseña si desea cambiarla">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           name="is_admin" 
                                           id="is_admin<?= $datos->id_empleado ?>" 
                                           value="1" 
                                           <?= ($datos->is_admin == 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" 
                                           for="is_admin<?= $datos->id_empleado ?>">
                                        ¿Añadir como administrador?
                                    </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" name="btnmodificar" value="1" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
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