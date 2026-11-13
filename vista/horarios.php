<?php
require_once __DIR__ . '/../controlador/auth.php';
requerir_admin();

/** Hora resultante de sumar minutos a la entrada, p. ej. ('07:40:00', 90) -> '09:10' */
function hora_mas_minutos(string $hora, int $minutos): string
{
    return date('H:i', strtotime($hora) + $minutos * 60);
}

/**
 * Campos de tolerancia y retardo en minutos después de la entrada; el JS de abajo
 * muestra debajo a qué horas equivalen.
 */
function campos_tolerancia(int $tolerancia, int $retardo): void
{ ?>
    <!-- Cuadrícula propia: la plantilla mezcla versiones de Bootstrap y .form-row/.col-sm-6 se encimaban con el resumen -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0 16px;">
        <div class="form-group">
            <label>Tolerancia (minutos)</label>
            <input type="number" class="form-control" name="tolerancia_entrada" value="<?= $tolerancia ?>" required min="0" max="720">
        </div>
        <div class="form-group">
            <label>Límite de retardo (minutos)</label>
            <input type="number" class="form-control" name="limite_retardo" value="<?= $retardo ?>" required min="1" max="720">
        </div>
    </div>
    <div class="alert alert-secondary small py-2 js-resumen"></div>
<?php }
?>


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
                                    <td><?= e($datos->id_horario) ?></td>
                                    <td><?= e($datos->nombre) ?></td>
                                    <td><?= date('h:i A', strtotime($datos->hora_entrada)) ?></td>
                                    <td><?= date('h:i A', strtotime($datos->hora_salida)) ?></td>
                                    <td><?= e($datos->tolerancia_entrada) ?> <small class="text-muted">(hasta <?= hora_mas_minutos($datos->hora_entrada, (int) $datos->tolerancia_entrada) ?>)</small></td>
                                    <td><?= e($datos->limite_retardo) ?> <small class="text-muted">(hasta <?= hora_mas_minutos($datos->hora_entrada, (int) $datos->limite_retardo) ?>)</small></td>
                                    <td><?= e($datos->descripcion) ?></td>
                                    <td>
                                        <a href="#" data-toggle="modal" data-target="#editarHorario<?= e($datos->id_horario) ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="javascript:void(0)" onclick="confirmarEliminacion(<?= e($datos->id_horario) ?>)" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>

                                <!-- Modal Editar -->
                                <div class="modal fade" id="editarHorario<?= e($datos->id_horario) ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Editar Horario</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <form id="formEditarHorario<?= e($datos->id_horario) ?>" onsubmit="editarHorario(event, <?= e($datos->id_horario) ?>)" method="POST">
                                                <?= campo_csrf() ?>
                                                    <input type="hidden" name="id_horario" value="<?= e($datos->id_horario) ?>">
                                                    
                                                    <div class="form-group">
                                                        <label>Nombre del Horario</label>
                                                        <input type="text" class="form-control" name="nombre" value="<?= e($datos->nombre) ?>" required>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label>Hora de Entrada</label>
                                                        <input type="time" class="form-control" name="hora_entrada" value="<?= e($datos->hora_entrada) ?>" required>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label>Hora de Salida</label>
                                                        <input type="time" class="form-control" name="hora_salida" value="<?= e($datos->hora_salida) ?>" required>
                                                    </div>
                                                    
                                                    <?php campos_tolerancia((int) $datos->tolerancia_entrada, (int) $datos->limite_retardo); ?>
                                                    
                                                    <div class="form-group">
                                                        <label>Descripción</label>
                                                        <textarea class="form-control" name="descripcion" rows="3"><?= e($datos->descripcion) ?></textarea>
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
                <form method="POST" action="../controlador/controlador_registrar_horario.php" onsubmit="return validarHorario(this)">
<?= campo_csrf() ?>
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
                    
                    <?php campos_tolerancia(10, 20); ?>
                    
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
/* ---- Tolerancia / retardo: resumen de las franjas ---- */
function aMinutos(hhmm) {
    if (!hhmm) return null;
    const [h, m] = hhmm.split(':').map(Number);
    return h * 60 + m;
}
function hora12(minutos) {
    const h = Math.floor(minutos / 60), m = minutos % 60;
    return ((h + 11) % 12 + 1) + ':' + String(m).padStart(2, '0') + (h < 12 ? ' a. m.' : ' p. m.');
}

/** Revisa los valores del formulario; devuelve el error o null. */
function errorHorario(form) {
    const entrada = aMinutos(form.hora_entrada.value);
    const salida = aMinutos(form.hora_salida.value);
    const tol = parseInt(form.tolerancia_entrada.value, 10);
    const ret = parseInt(form.limite_retardo.value, 10);
    if (entrada === null || salida === null) return 'Las horas de entrada y salida son obligatorias';
    if (entrada >= salida) return 'La hora de salida debe ser posterior a la hora de entrada';
    if (isNaN(tol) || tol < 0) return 'La tolerancia no puede ser negativa';
    if (isNaN(ret) || ret <= tol) return 'El retardo debe terminar después de la tolerancia';
    if (entrada + ret >= salida) return 'El retardo debe terminar antes de la hora de salida';
    return null;
}

/** Muestra a qué horas equivalen la tolerancia y el retardo. */
function sincronizarHorario(form) {
    const entrada = aMinutos(form.hora_entrada.value);
    const resumen = form.querySelector('.js-resumen');
    const error = errorHorario(form);
    resumen.classList.toggle('alert-danger', !!error);
    resumen.classList.toggle('alert-secondary', !error);
    if (error) {
        resumen.textContent = error;
        return;
    }
    const tol = parseInt(form.tolerancia_entrada.value, 10);
    const ret = parseInt(form.limite_retardo.value, 10);
    resumen.innerHTML =
        '<b>A tiempo:</b> hasta las ' + hora12(entrada + tol) +
        ' &nbsp;·&nbsp; <b>Retardo:</b> de ' + hora12(entrada + tol + 1) + ' a ' + hora12(entrada + ret) +
        ' &nbsp;·&nbsp; <b>Falta:</b> después de las ' + hora12(entrada + ret);
}

function validarHorario(form) {
    const error = errorHorario(form);
    if (error) {
        Swal.fire({ icon: 'error', title: 'Error', text: error });
        return false;
    }
    return true;
}

['input', 'change'].forEach(function (tipo) {
    document.addEventListener(tipo, function (ev) {
        const form = ev.target.form;
        if (form && form.querySelector('.js-resumen')) sincronizarHorario(form);
    });
});
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-resumen').forEach(function (r) { sincronizarHorario(r.closest('form')); });
});

function editarHorario(event, id) {
    event.preventDefault();
    const form = document.getElementById('formEditarHorario' + id);
    const formData = new FormData(form);
    formData.append('btnmodificar', '1');

    if (!validarHorario(form)) return;

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

function confirmarEliminacion(id) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción eliminará permanentemente este horario",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            enviarPost('horarios.php?id=' + id);
        }
    });
}
</script>

<!-- por ultimo se carga el footer -->
<?php require('./layout/footer.php'); ?>

<?php
// Resultado del alta de horario (ver controlador_registrar_horario.php)
$mensajes_horario = [
    'registrado' => ['success', '¡Listo!', 'Horario registrado correctamente'],
    'campos'     => ['error', 'Faltan datos', 'Todos los campos son obligatorios'],
    'formato'    => ['error', 'Hora inválida', 'Revisa el formato de las horas'],
    'orden'      => ['error', 'Horario inválido', 'La hora de salida debe ser posterior a la hora de entrada'],
    'tolerancia' => ['error', 'Valores inválidos', 'El límite de retardo debe ser mayor que la tolerancia y terminar antes de la hora de salida'],
    'error'      => ['error', 'Error', 'No se pudo registrar el horario'],
];
if (isset($_GET['msg'], $mensajes_horario[$_GET['msg']])) {
    [$icono, $titulo, $texto] = $mensajes_horario[$_GET['msg']]; ?>
    <script>
        Swal.fire({ icon: <?= json_encode($icono) ?>, title: <?= json_encode($titulo) ?>, text: <?= json_encode($texto) ?> });
        // Quita ?msg= de la URL para que al recargar no se repita el mensaje
        window.history.replaceState(null, null, window.location.pathname);
    </script>
<?php } ?>
