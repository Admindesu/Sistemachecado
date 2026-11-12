<?php
require_once __DIR__ . '/../controlador/auth.php';
requerir_admin();
?>


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
                empleado.id_horario,
                cargo.nombre AS nom_cargo,
                direccion.nombre AS nom_direccion,
                subsecretaria.nombre AS nom_subsecretaria,
                horarios.nombre AS nom_horario,
                horarios.hora_entrada,
                horarios.hora_salida
                FROM empleado
                INNER JOIN cargo ON empleado.cargo = cargo.id_cargo
                INNER JOIN direccion ON empleado.direccion = direccion.id_direccion
                LEFT JOIN subsecretaria ON empleado.subsecretaria = subsecretaria.id_subsecretaria
                LEFT JOIN horarios ON empleado.id_horario = horarios.id_horario
            ");
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Error al cargar los datos.</div>";
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

                <a href="importar_hikvision.php" class="btn btn-info btn-rounded ml-2">
                    <i class="fas fa-user-check"></i> Importar desde Hikvision
                </a>
            </div>
        </div>

        <!-- Edición masiva: se marcan empleados en la tabla y se editan juntos -->
        <div class="row mb-3">
            <div class="col-12 d-flex flex-wrap align-items-center" style="gap: 8px;">
                <button type="button" class="btn btn-warning btn-rounded" id="btnEditarSeleccionados" disabled
                        data-toggle="modal" data-target="#modalEditarMasivo">
                    <i class="fas fa-users-cog"></i> Editar seleccionados (<span class="js-num-seleccionados">0</span>)
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnSeleccionarFiltrados">
                    <i class="fas fa-check-double"></i> Seleccionar todos los de la búsqueda
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnQuitarSeleccion">
                    <i class="fas fa-times"></i> Quitar selección
                </button>
                <small class="text-muted">Marca empleados en la tabla (puedes buscar, p. ej., el cargo provisional) y cámbiales cargo, dirección u horario de una vez.</small>
            </div>
        </div>

        <?php
        // Resultado de la edición masiva (controlador_editar_masivo.php)
        $mensajes_masivo = [
            'ok'            => ['success', 'Listo', 'Se actualizaron ' . (int) ($_GET['n'] ?? 0) . ' empleados'],
            'sin_empleados' => ['warning', 'Sin empleados', 'No seleccionaste ningún empleado'],
            'sin_cambios'   => ['info', 'Sin cambios', 'No elegiste ningún dato para cambiar'],
            'invalido'      => ['error', 'Dato inválido', 'El cargo, la dirección o el horario elegido no existe'],
            'error'         => ['error', 'Error', 'No se pudieron actualizar los empleados'],
        ];
        $mensaje_masivo = $mensajes_masivo[$_GET['masivo'] ?? ''] ?? null;
        ?>

        <!-- Modal edición masiva -->
        <div class="modal fade" id="modalEditarMasivo" tabindex="-1" aria-labelledby="modalEditarMasivoLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="../controlador/controlador_editar_masivo.php" method="POST" id="formEditarMasivo">
                        <?= campo_csrf() ?>
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalEditarMasivoLabel">
                                Editar <span class="js-num-seleccionados">0</span> empleados
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p class="small text-muted mb-2">Solo se cambia lo que elijas; lo que quede en «No cambiar» se conserva.</p>
                            <p class="small mb-3"><strong>Seleccionados:</strong> <span id="listaSeleccionados"></span></p>
                            <?php
                            $catalogos = [
                                'cargo' => ['Cargo', "SELECT id_cargo AS id, nombre FROM cargo ORDER BY nombre"],
                                'direccion' => ['Dirección', "SELECT id_direccion AS id, nombre FROM direccion ORDER BY nombre"],
                                'horario' => ['Horario', "SELECT id_horario AS id, CONCAT(nombre, ' (', TIME_FORMAT(hora_entrada, '%H:%i'), ' - ', TIME_FORMAT(hora_salida, '%H:%i'), ')') AS nombre FROM horarios ORDER BY nombre"],
                            ];
                            if (usar_subsecretarias()) {
                                $catalogos['subsecretaria'] = ['Subsecretaría', "SELECT id_subsecretaria AS id, nombre FROM subsecretaria ORDER BY nombre"];
                            }
                            foreach ($catalogos as $campo => [$etiqueta, $consulta_catalogo]): ?>
                                <div class="form-group">
                                    <label for="masivo_<?= $campo ?>"><?= $etiqueta ?></label>
                                    <select class="form-control" name="<?= $campo ?>" id="masivo_<?= $campo ?>">
                                        <option value="">— No cambiar —</option>
                                        <?php $opciones = $conexion->query($consulta_catalogo);
                                        while ($opciones && ($op = $opciones->fetch_object())): ?>
                                            <option value="<?= e($op->id) ?>"><?= e($op->nombre) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            <?php endforeach; ?>
                            <div class="form-check" id="opcionRecalcular" style="display: none;">
                                <input class="form-check-input" type="checkbox" name="recalcular" value="1" id="masivo_recalcular" checked>
                                <label class="form-check-label small" for="masivo_recalcular">
                                    Recalcular sus asistencias del terminal con el horario nuevo (retardos y faltas)
                                </label>
                            </div>
                            <div id="idsSeleccionados"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Aplicar cambios</button>
                        </div>
                    </form>
                </div>
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
<?= campo_csrf() ?>
                            <div class="form-group">
                                <label for="csvFile">Seleccionar archivo CSV</label>
                                <input type="file" class="form-control-file" id="csvFile" name="csvFile" accept=".csv" required>
                            </div>
                            <div class="alert alert-info">
                                <small>
                                    <strong>Formato del CSV:</strong><br>
                                    nombre,apellido,dni,usuario,password,cargo,direccion,subsecretaria,is_admin,id_horario<br><br>
                                    <strong>Ejemplo:</strong><br>
                                    <?php if (usar_subsecretarias()): ?>
                                    Juan,Perez,12345678,jperez,password123,1,1,1,0,1
                                    <?php else: ?>
                                    Juan,Perez,12345678,jperez,password123,1,1,,0,1<br>
                                    <em>La columna subsecretaria se deja vacía.</em>
                                    <?php endif; ?>
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
                    <table class="table table-bordered table-hover w-100" id="example" data-order='[[1, "asc"]]'>
                        <thead>
                            <tr>
                                <th scope="col" data-orderable="false" data-searchable="false" class="text-center" style="width: 30px;">
                                    <input type="checkbox" id="checkTodos" title="Seleccionar / quitar los de esta página" aria-label="Seleccionar los empleados de esta página">
                                </th>
                                <th scope="col">ID</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Apellido</th>
                                <th scope="col">NoEmpleado</th>
                                <th scope="col">Usuario</th>
                                <th scope="col">Admin</th>
                                <th scope="col">Cargo</th> 
                                <th scope="col">Direccion</th>
                                <?php if (usar_subsecretarias()): ?><th scope="col">Subsecretaria</th><?php endif; ?>
                                <th scope="col">Horario</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($sql) {
                                while ($datos = $sql->fetch_object()) { 
                            ?>
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="js-seleccion" value="<?= e($datos->id_empleado) ?>"
                                               data-nombre="<?= e($datos->nombre . ' ' . $datos->apellido) ?>"
                                               aria-label="Seleccionar a <?= e($datos->nombre . ' ' . $datos->apellido) ?>">
                                    </td>
                                    <td><?= e($datos->id_empleado) ?></td>
                                    <td><?= e($datos->nombre) ?></td>
                                    <td><?= e($datos->apellido) ?></td>
                                    <td><?= e($datos->dni) ?></td>
                                    <td><?= e($datos->usuario) ?></td>
                                    <td>
                                        <input type="checkbox" disabled <?= ($datos->is_admin == 1) ? 'checked' : '' ?>>
                                    </td>
                                    <td><?= e($datos->nom_cargo) ?></td>
                                    <td><?= e($datos->nom_direccion) ?></td>
                                    <?php if (usar_subsecretarias()): ?><td><?= $datos->nom_subsecretaria !== null ? e($datos->nom_subsecretaria) : '<span class="text-muted">—</span>' ?></td><?php endif; ?>
                                    <td><?= $datos->nom_horario ? e($datos->nom_horario) . ' (' . date('h:i A', strtotime($datos->hora_entrada)) . ' - ' . date('h:i A', strtotime($datos->hora_salida)) . ')' : 'No asignado' ?></td>
                                    <td>
                                        <a href="" data-toggle="modal" data-target="#exampleModal<?= e($datos->id_empleado) ?>" class="btn btn-warning">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <a href="empleado.php?id=<?= e($datos->id_empleado) ?>" onclick="advertencia(event)" class="btn btn-danger">
                                            <i class="fas fa-exclamation-triangle"></i> Eliminar
                                        </a>
                                    </td>
                                </tr>



<!-- Modal -->
<div class="modal fade" id="exampleModal<?= e($datos->id_empleado) ?>" tabindex="-1" aria-labelledby="exampleModalLabel<?= e($datos->id_empleado) ?>" aria-hidden="true">
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
<?= campo_csrf() ?>
                    <div class="row">
                        <div class="col-12" hidden>
                            <div class="form-group">
                                <label for="ID">ID</label>
                                <input type="text" class="form-control" name="txtid" value="<?= e($datos->id_empleado) ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre">Nombre</label>
                                <input type="text" class="form-control" name="txtnombre" value="<?= e($datos->nombre) ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="apellido">Apellido</label>
                                <input type="text" class="form-control" name="txtapellido" value="<?= e($datos->apellido) ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="horario">Horario Laboral</label>
                                <select name="txthorario" class="form-control">
                                    <?php 
                                    $sql_horarios = $conexion->query("SELECT * FROM horarios ORDER BY nombre ASC");
                                    while ($horario = $sql_horarios->fetch_object()) { ?>
                                        <option value="<?= e($horario->id_horario) ?>" <?= $datos->id_horario == $horario->id_horario ? 'selected' : '' ?>>
                                            <?= e($horario->nombre) ?> (<?= date('h:i A', strtotime($horario->hora_entrada)) ?> - <?= date('h:i A', strtotime($horario->hora_salida)) ?>)
                                        </option>
                                    <?php } ?>
                                </select>
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
                                                value="<?= e($datos2->id_cargo) ?>">
                                            <?= e($datos2->nombre) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        
                        <?php if (!usar_subsecretarias()): ?>
                        <input type="hidden" name="txtsubsecretaria" value="<?= e($datos->subsecretaria) ?>">
                        <?php else: ?>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="subsecretaria">Subsecretaría</label>
                                <select name="txtsubsecretaria" class="form-control">
                                    <option value="">Sin subsecretaría</option>
                                    <?php 
                                    $sql2 = $conexion->query("SELECT * FROM subsecretaria");
                                    while ($datos2 = $sql2->fetch_object()) { ?>
                                        <option <?= $datos->subsecretaria == $datos2->id_subsecretaria ? 'selected' : '' ?> 
                                                value="<?= e($datos2->id_subsecretaria) ?>">
                                            <?= e($datos2->nombre) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="direccion">Dirección</label>
                                <select name="txtdireccion" class="form-control">
                                    <?php 
                                    $sql2 = $conexion->query("SELECT * FROM direccion");
                                    while ($datos2 = $sql2->fetch_object()) { ?>
                                        <option <?= $datos->direccion == $datos2->id_direccion ? 'selected' : '' ?> 
                                                value="<?= e($datos2->id_direccion) ?>">
                                            <?= e($datos2->nombre) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="usuario">Usuario</label>
                                <input type="text" class="form-control" name="txtusuario" value="<?= e($datos->usuario) ?>" required>
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
                                           id="is_admin<?= e($datos->id_empleado) ?>" 
                                           value="1" 
                                           <?= ($datos->is_admin == 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" 
                                           for="is_admin<?= e($datos->id_empleado) ?>">
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

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- por ultimo se carga el footer -->
<?php require('./layout/footer.php'); ?>

<!-- Va después del footer: footer.php vuelve a cargar jQuery y Bootstrap -->
<script>
/*
 * Selección para la edición masiva. DataTables conserva las filas de las otras páginas
 * aunque no estén en pantalla, así que las casillas marcadas se leen con su API.
 */
(function () {
    function tabla() { return $('#example').DataTable(); }
    function casillas(filtro) {
        return $(tabla().rows(filtro || {}).nodes()).find('input.js-seleccion');
    }
    function marcadas() { return casillas().filter(':checked'); }

    function actualizar() {
        // La tabla la inicializa footer.php; antes de eso no se toca (si no, se crearía dos veces)
        if (!$.fn.dataTable.isDataTable('#example')) return;
        const n = marcadas().length;
        $('.js-num-seleccionados').text(n);
        $('#btnEditarSeleccionados').prop('disabled', n === 0);
        const visibles = casillas({ page: 'current', search: 'applied' });
        const marcadasVisibles = visibles.filter(':checked').length;
        $('#checkTodos').prop('checked', visibles.length > 0 && marcadasVisibles === visibles.length)
                        .prop('indeterminate', marcadasVisibles > 0 && marcadasVisibles < visibles.length);
    }

    $(document).on('change', 'input.js-seleccion', actualizar);
    $(document).on('draw.dt', '#example', actualizar);
    $(document).on('click', 'input.js-seleccion', function (ev) { ev.stopPropagation(); });

    // Casilla del encabezado: los de la página actual
    $(document).on('change', '#checkTodos', function () {
        casillas({ page: 'current', search: 'applied' }).prop('checked', this.checked);
        actualizar();
    });
    // Todos los que coinciden con la búsqueda, en todas las páginas
    $('#btnSeleccionarFiltrados').on('click', function () {
        casillas({ search: 'applied' }).prop('checked', true);
        actualizar();
    });
    $('#btnQuitarSeleccion').on('click', function () {
        casillas().prop('checked', false);
        actualizar();
    });

    // Al abrir el modal se pasan los empleados marcados al formulario
    $('#modalEditarMasivo').on('show.bs.modal', function () {
        const seleccion = marcadas();
        const ids = $('#idsSeleccionados').empty();
        const nombres = [];
        seleccion.each(function () {
            ids.append($('<input type="hidden" name="ids[]">').val(this.value));
            nombres.push(this.dataset.nombre);
        });
        $('#listaSeleccionados').text(nombres.length > 8
            ? nombres.slice(0, 8).join(', ') + ' y ' + (nombres.length - 8) + ' más'
            : nombres.join(', '));
        $('#formEditarMasivo select').val('');
        $('#opcionRecalcular').hide();
    });
    $('#masivo_horario').on('change', function () {
        $('#opcionRecalcular').toggle(this.value !== '');
    });
    $('#formEditarMasivo').on('submit', function (ev) {
        const cambios = $(this).find('select').filter(function () { return this.value !== ''; }).length;
        if (!cambios) {
            ev.preventDefault();
            Swal.fire({ icon: 'info', title: 'Sin cambios', text: 'Elige al menos un dato para cambiar' });
        }
    });

    $(document).on('init.dt', '#example', actualizar);
})();
</script>

<?php if ($mensaje_masivo): [$icono, $titulo, $texto] = $mensaje_masivo; ?>
<script>
    Swal.fire({ icon: <?= json_encode($icono) ?>, title: <?= json_encode($titulo) ?>, text: <?= json_encode($texto) ?> });
    // Quita ?masivo= de la URL para que al recargar no se repita el mensaje
    window.history.replaceState(null, null, window.location.pathname);
</script>
<?php endif; ?>
