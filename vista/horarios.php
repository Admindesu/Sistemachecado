<?php
session_start();
if (empty($_SESSION['nombre']) || empty($_SESSION['apellido'])) {
    header('location: login/login.php');
    exit;
}

// Verifica si el usuario no es admin
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] != 1) {
    session_destroy();
    header('location: login/login.php');
    exit;
}
?>

<style>
ul li:nth-child(6) .activo {
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
        <h4 class="text-center text-secondary">GESTIÓN DE HORARIOS</h4>

        <?php
        include "../modelo/conexion.php";
        include "../controlador/controlador_modificar_horario.php";
        include "../controlador/controlador_eliminar_horario.php";
        
        $sql = $conexion->query("SELECT * FROM horarios ORDER BY nombre ASC");
        ?>

        <div class="row mb-3">
            <div class="col-12">
                <a href="#" data-toggle="modal" data-target="#nuevoHorario" class="btn btn-primary btn-rounded">
                    <i class="fas fa-plus"></i> Agregar Horario
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="example">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Tolerancia (min)</th>
                                <th>Límite Retardo (min)</th>
                                <th>Descripción</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($datos = $sql->fetch_object()) { ?>
                                <tr>
                                    <td><?= $datos->id_horario ?></td>
                                    <td><?= $datos->nombre ?></td>
                                    <td><?= date('h:i A', strtotime($datos->hora_entrada)) ?></td>
                                    <td><?= date('h:i A', strtotime($datos->hora_salida)) ?></td>
                                    <td><?= $datos->tolerancia_entrada ?></td>
                                    <td><?= $datos->limite_retardo ?></td>
                                    <td><?= $datos->descripcion ?></td>
                                    <td>
                                        <a href="#" data-toggle="modal" data-target="#editarHorario<?= $datos->id_horario ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="horarios.php?id=<?= $datos->id_horario ?>" onclick="return confirm('¿Está seguro de eliminar este horario?')" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>

                                <!-- Modal Editar -->
                                <div class="modal fade" id="editarHorario<?= $datos->id_horario ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Editar Horario</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <form id="formEditarHorario<?= $datos->id_horario ?>" onsubmit="editarHorario(event, <?= $datos->id_horario ?>)" method="POST">
                                                    <input type="hidden" name="id_horario" value="<?= $datos->id_horario ?>">
                                                    
                                                    <div class="form-group">
                                                        <label>Nombre del Horario</label>
                                                        <input type="text" class="form-control" name="nombre" value="<?= $datos->nombre ?>" required>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label>Hora de Entrada</label>
                                                        <input type="time" class="form-control" name="hora_entrada" value="<?= $datos->hora_entrada ?>" required>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label>Hora de Salida</label>
                                                        <input type="time" class="form-control" name="hora_salida" value="<?= $datos->hora_salida ?>" required>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label>Tolerancia (minutos)</label>
                                                        <input type="number" class="form-control" name="tolerancia_entrada" value="<?= $datos->tolerancia_entrada ?>" required min="0" max="60">
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label>Límite de Retardo (minutos)</label>
                                                        <input type="number" class="form-control" name="limite_retardo" value="<?= $datos->limite_retardo ?>" required min="0" max="60">
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label>Descripción</label>
                                                        <textarea class="form-control" name="descripcion" rows="3"><?= $datos->descripcion ?></textarea>
                                                    </div>
                                                    
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                        <button type="submit" class="btn btn-primary" name="btnmodificar">Guardar Cambios</button>
                                                    </div>
                                                </form>
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

<!-- Modal Nuevo Horario -->
<div class="modal fade" id="nuevoHorario" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Horario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="../controlador/controlador_registrar_horario.php">
                    <div class="form-group">
                        <label>Nombre del Horario</label>
                        <input type="text" class="form-control" name="nombre" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Hora de Entrada</label>
                        <input type="time" class="form-control" name="hora_entrada" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Hora de Salida</label>
                        <input type="time" class="form-control" name="hora_salida" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Tolerancia (minutos)</label>
                        <input type="number" class="form-control" name="tolerancia_entrada" required min="0" max="60" value="10">
                    </div>
                    
                    <div class="form-group">
                        <label>Límite de Retardo (minutos)</label>
                        <input type="number" class="form-control" name="limite_retardo" required min="0" max="60" value="20">
                    </div>
                    
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="3"></textarea>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary" name="btnregistrar">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- fin del contenido principal -->

<script>
function editarHorario(event, id) {
    event.preventDefault();
    const form = document.getElementById('formEditarHorario' + id);
    const formData = new FormData(form);
    formData.append('btnmodificar', '1');

    // Validaciones
    const horaEntrada = formData.get('hora_entrada');
    const horaSalida = formData.get('hora_salida');
    const tolerancia = parseInt(formData.get('tolerancia_entrada'));
    const limiteRetardo = parseInt(formData.get('limite_retardo'));

    if (!horaEntrada || !horaSalida) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Las horas de entrada y salida son obligatorias'
        });
        return;
    }

    if (horaEntrada >= horaSalida) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'La hora de salida debe ser posterior a la hora de entrada'
        });
        return;
    }

    if (tolerancia < 0 || tolerancia > 60 || limiteRetardo < 0 || limiteRetardo > 60 || limiteRetardo <= tolerancia) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Los valores de tolerancia y límite de retardo son inválidos'
        });
        return;
    }

    fetch('../controlador/controlador_modificar_horario.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.includes('Error')) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al modificar el horario'
            });
        } else {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: 'Horario modificado correctamente',
                timer: 1500
            }).then(() => {
                window.location.reload();
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error en la comunicación con el servidor'
        });
    });
}
</script>

<!-- por ultimo se carga el footer -->
<?php require('./layout/footer.php'); ?>
