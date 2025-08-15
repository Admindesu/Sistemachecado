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
    <div class="container-fluid">
        <h4 class="text-center text-secondary">Lista de Cargos</h4>

        <?php
        include "../modelo/conexion.php";
        include "../controlador/controlador_modificar_cargo.php";
        $sql = $conexion->query("SELECT * FROM cargo ORDER BY id_cargo");
        ?>

        <div class="row mb-3">
            <div class="col-12">
                <a href="registro_cargo.php" class="btn btn-primary btn-rounded">
                    <i class="fas fa-plus"></i> Agregar
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
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($datos = $sql->fetch_object()) { ?>
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
                                            <h5 class="modal-title" id="exampleModalLabel">Modificar Cargo</h5>
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
                                        
                                                <div class="text-right p-2">
                                                    <a href="cargo.php" class="btn btn-secondary btn-rounded">Atras</a>
                                                    <button type="submit" value="ok" name="btnregistrar" class="btn btn-primary btn-rounded">Registrar</button>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
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