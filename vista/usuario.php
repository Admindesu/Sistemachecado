<?php
   session_start();
   if (empty($_SESSION['nombre']) and empty($_SESSION['apellido'])) {
       header('location:login/login.php');
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
        <h4 class="text-center text-secondary">Lista de usuarios</h4>

        <?php
        include "../modelo/conexion.php";
        include "../controlador/controlador_modificar_usuario.php";
        include "../controlador/controlador_eliminar_usuario.php";
        $sql= $conexion->query("SELECT * FROM usuario");
        ?>

        <div class="row mb-3">
            <div class="col-12">
                <a href="registro_usuario.php" class="btn btn-primary btn-rounded">
                    <i class="fas fa-plus"></i> Agregar Usuario
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
                                <th scope="col">Usuario</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($datos = $sql->fetch_object()) { ?>
                                <tr>
                                    <td><?= $datos->id_usuario ?></td>
                                    <td><?= $datos->nombre ?></td>
                                    <td><?= $datos->apellido ?></td>
                                    <td><?= $datos->usuario ?></td>
                                    <td>
                                        <a href="" data-toggle="modal" data-target="#exampleModal<?= $datos->id_usuario ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Editar</a>
                                        <a href="usuario.php?id=<?= $datos->id_usuario ?>" onclick="advertencia(event)" class="btn btn-danger"><i class="fas fa-exclamation-triangle"></i> Eliminar</a>
                                    </td>
                                </tr>
                                <!-- Modal -->
                                <div class="modal fade" id="exampleModal<?= $datos->id_usuario ?>" tabindex="-1" aria-labelledby="exampleModalLabel<?= $datos->id_usuario ?>" aria-hidden="true">
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
                                                        <input type="text" class="input input__text" name="txtid" value="<?= $datos->id_usuario ?>" >
                                                    </div>
                                                    <div class="fl-flex-label mb-4 px-2 col-12">
                                                        <label for="nombre">Nombre</label>
                                                        <input type="text" class="input input__text" name="txtnombre" value="<?= $datos->nombre ?>" >
                                                    </div>
                                                    <div class="fl-flex-label mb-4 px-2 col-12">
                                                        <label for="apellido">Apellido</label>
                                                        <input type="text" class="input input__text" name="txtapellido" value="<?= $datos->apellido ?>" >
                                                    </div>
                                                    <div class="fl-flex-label mb-4 px-2 col-12">
                                                        <label for="usuario">Usuario</label>
                                                        <input type="text" class="input input__text" name="txtusuario" value="<?= $datos->usuario ?>">
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

<?php
if (isset($_POST['btn_cambiar_pass'])) {
    include "../modelo/conexion.php";
    $id_usuario = $_SESSION['id_usuario'] ?? null;
    $pass_actual = $_POST['pass_actual'];
    $pass_nueva = $_POST['pass_nueva'];
    $pass_confirmar = $_POST['pass_confirmar'];

    if (!$id_usuario) {
        echo "<div class='alert alert-danger'>No se encontró el usuario logueado.</div>";
    } elseif ($pass_nueva !== $pass_confirmar) {
        echo "<div class='alert alert-danger'>Las contraseñas nuevas no coinciden.</div>";
    } else {
        $consulta = $conexion->query("SELECT password FROM usuario WHERE id_usuario=$id_usuario");
        if ($consulta && $consulta->num_rows > 0) {
            $row = $consulta->fetch_assoc();
            if (md5($pass_actual) === $row['password']) {
                $pass_hash = md5($pass_nueva);
                $conexion->query("UPDATE usuario SET password='$pass_hash' WHERE id_usuario=$id_usuario");
                echo "<div class='alert alert-success'>Contraseña actualizada correctamente.</div>";
            } else {
                echo "<div class='alert alert-danger'>La contraseña actual es incorrecta.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>No se pudo obtener la contraseña actual.</div>";
        }
    }
}
?>