<?php
require_once __DIR__ . '/../controlador/auth.php';
requerir_admin();

include "../modelo/conexion.php";
?>
<!-- primero se carga el topbar -->
<?php require('./layout/topbar.php'); ?>
<!-- luego se carga el sidebar -->
<?php require('./layout/sidebar.php'); ?>

<!-- Carga jQuery y SweetAlert ANTES de usarlos -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
// Eliminaciones (llegan por POST con token CSRF; cada controlador revisa su propio campo)
include "../controlador/controlador_eliminar_cargo.php";
include "../controlador/controlador_eliminar_direccion.php";
include "../controlador/controlador_eliminar_subsecretaria.php";

?>



<!-- inicio del contenido principal -->
<div class="page-content">
    <div class="container-fluid">
        <h4 class="text-center text-secondary">Catálogos del Sistema</h4>

        <?php
        // Include controllers for update operations
        include "../controlador/controlador_modificar_cargo.php";
        include "../controlador/controlador_modificar_direccion.php";
        include "../controlador/controlador_modificar_subsecretaria.php";

        // Obtener datos de cargo
        $sqlCargo = $conexion->query("SELECT * FROM cargo ORDER BY id_cargo");

        // Obtener datos de dirección
        $sqlDireccion = $conexion->query("SELECT * FROM direccion ORDER BY id_direccion");

        // Obtener datos de subsecretaria
        $sqlSubsecretaria = $conexion->query("SELECT * FROM subsecretaria ORDER BY id_subsecretaria");
        ?>

        <!-- Tabs para los catálogos -->
        <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="cargo-tab" data-toggle="tab" href="#cargo" role="tab" aria-controls="cargo" aria-selected="true">Cargos</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="direccion-tab" data-toggle="tab" href="#direccion" role="tab" aria-controls="direccion" aria-selected="false">Direcciones</a>
            </li>
            <?php if (usar_subsecretarias()): ?>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="subsecretaria-tab" data-toggle="tab" href="#subsecretaria" role="tab" aria-controls="subsecretaria" aria-selected="false">Subsecretarías</a>
            </li>
            <?php endif; ?>
        </ul>

        <!-- Contenido de los tabs -->
        <div class="tab-content" id="myTabContent">
            <!-- Tab Cargos -->
            <div class="tab-pane show active" id="cargo" role="tabpanel" aria-labelledby="cargo-tab">
                <div class="row mb-3">
                    <div class="col-12">
                        <a href="registro_cargo.php" class="btn btn-primary btn-rounded">
                            <i class="fas fa-plus"></i> Agregar Cargo
                        </a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover w-100" id="tablaCargo">
                                <thead>
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Nombre</th>
                                        <th scope="col">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($datos = $sqlCargo->fetch_object()) { ?>
                                    <tr>
                                        <td><?= e($datos->id_cargo) ?></td>
                                        <td><?= e($datos->nombre) ?></td>
                                        <td>
                                            <a href="" data-toggle="modal" data-target="#modalCargo<?= e($datos->id_cargo) ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Editar</a>
                                            <a href="organigrama.php?tab=cargo&id=<?= e($datos->id_cargo) ?>" onclick="advertencia(event)" class="btn btn-danger"><i class="fas fa-exclamation-triangle"></i> Eliminar</a>
                                        </td>
                                    </tr>

                                    <!-- Modal Cargo -->
                                    <div class="modal fade" id="modalCargo<?= e($datos->id_cargo) ?>" tabindex="-1" aria-labelledby="modalCargoLabel<?= e($datos->id_cargo) ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalCargoLabel">Modificar Cargo</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="" method="POST">
<?= campo_csrf() ?>
                                                        <div hidden class="fl-flex-label mb-4 px-2 col-12">
                                                            <label for="ID">ID</label>
                                                            <input type="text" class="input input__text" name="txtid" value="<?= e($datos->id_cargo) ?>" >
                                                        </div>
                                                        <div class="fl-flex-label mb-4 px-2 col-12">
                                                            <label for="nombre">Nombre</label>
                                                            <input type="text" class="input input__text" name="txtnombre" value="<?= e($datos->nombre) ?>" >
                                                        </div>
                                                        <div class="text-right p-2">
                                                            <button type="button" class="btn btn-secondary btn-rounded" data-dismiss="modal">Cerrar</button>
                                                            <button type="submit" value="ok" name="btnmodificar" class="btn btn-primary btn-rounded">Guardar Cambios</button>
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

            <!-- Tab Direcciones -->
            <div class="tab-pane fade" id="direccion" role="tabpanel" aria-labelledby="direccion-tab">
                <div class="row mb-3">
                    <div class="col-12">
                        <a href="registro_direccion.php" class="btn btn-primary btn-rounded">
                            <i class="fas fa-plus"></i> Agregar Dirección
                        </a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover w-100" id="tablaDireccion">
                                <thead>
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Nombre</th>
                                        <th scope="col">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($datosDireccion = $sqlDireccion->fetch_object()) { ?>
                                    <tr>
                                        <td><?= e($datosDireccion->id_direccion) ?></td>
                                        <td><?= e($datosDireccion->nombre) ?></td>
                                        <td>
                                            <a href="" data-toggle="modal" data-target="#modalDireccion<?= e($datosDireccion->id_direccion) ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Editar</a>
                                            <a href="organigrama.php?nav=direccion&id_direccion=<?= e($datosDireccion->id_direccion) ?>" onclick="advertencia(event)" class="btn btn-danger"><i class="fas fa-exclamation-triangle"></i> Eliminar</a>
                                        </td>
                                    </tr>

                                    <!-- Modal Dirección -->
                                    <div class="modal fade" id="modalDireccion<?= e($datosDireccion->id_direccion) ?>" tabindex="-1" aria-labelledby="modalDireccionLabel<?= e($datosDireccion->id_direccion) ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalDireccionLabel">Modificar Dirección</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="" method="POST">
<?= campo_csrf() ?>
                                                        <div hidden class="fl-flex-label mb-4 px-2 col-12">
                                                            <label for="ID">ID</label>
                                                            <input type="text" class="input input__text" name="txtid" value="<?= e($datosDireccion->id_direccion) ?>" >
                                                        </div>
                                                        <div class="fl-flex-label mb-4 px-2 col-12">
                                                            <label for="nombre">Nombre</label>
                                                            <input type="text" class="input input__text" name="txtnombre" value="<?= e($datosDireccion->nombre) ?>" >
                                                        </div>
                                                        <div class="text-right p-2">
                                                            <button type="button" class="btn btn-secondary btn-rounded" data-dismiss="modal">Cerrar</button>
                                                            <button type="submit" value="ok" name="btnmodificardir" class="btn btn-primary btn-rounded">Guardar Cambios</button>
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

            <?php if (usar_subsecretarias()): ?>
            <!-- Tab Subsecretarías -->
            <div class="tab-pane fade" id="subsecretaria" role="tabpanel" aria-labelledby="subsecretaria-tab">
                <div class="row mb-3">
                    <div class="col-12">
                        <a href="registro_subsecretaria.php" class="btn btn-primary btn-rounded">
                            <i class="fas fa-plus"></i> Agregar Subsecretaría
                        </a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover w-100" id="tablaSubsecretaria">
                                <thead>
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Nombre</th>
                                        <th scope="col">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($datosSubsec = $sqlSubsecretaria->fetch_object()) { ?>
                                    <tr>
                                        <td><?= e($datosSubsec->id_subsecretaria) ?></td>
                                        <td><?= e($datosSubsec->nombre) ?></td>
                                        <td>
                                            <a href="" data-toggle="modal" data-target="#modalSubsec<?= e($datosSubsec->id_subsecretaria) ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Editar</a>
                                            <a href="organigrama.php?tab=subsecretaria&id_subsecretaria=<?= e($datosSubsec->id_subsecretaria) ?>" onclick="advertencia(event)" class="btn btn-danger"><i class="fas fa-exclamation-triangle"></i> Eliminar</a>
                                        </td>
                                    </tr>

                                    <!-- Modal Subsecretaría -->
                                    <div class="modal fade" id="modalSubsec<?= e($datosSubsec->id_subsecretaria) ?>" tabindex="-1" aria-labelledby="modalSubsecLabel<?= e($datosSubsec->id_subsecretaria) ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalSubsecLabel">Modificar Subsecretaría</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="" method="POST">
<?= campo_csrf() ?>
                                                        <div hidden class="fl-flex-label mb-4 px-2 col-12">
                                                            <label for="ID">ID</label>
                                                            <input type="text" class="input input__text" name="txtid" value="<?= e($datosSubsec->id_subsecretaria) ?>" >
                                                        </div>
                                                        <div class="fl-flex-label mb-4 px-2 col-12">
                                                            <label for="nombre">Nombre</label>
                                                            <input type="text" class="input input__text" name="txtnombre" value="<?= e($datosSubsec->nombre) ?>" >
                                                        </div>
                                                        <div class="text-right p-2">
                                                            <button type="button" class="btn btn-secondary btn-rounded" data-dismiss="modal">Cerrar</button>
                                                            <button type="submit" value="ok" name="btnmodificarsub" class="btn btn-primary btn-rounded">Guardar Cambios</button>
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
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- fin del contenido principal -->
<!-- Añadir esto cerca del inicio del archivo, después de incluir jQuery -->
<link href="..\public\pnotify\css\pnotify.css" rel="stylesheet">
<link href="..\public\pnotify\css\pnotify.buttons.css" rel="stylesheet">
<script src="..\public\pnotify\js\pnotify.js"></script>
<script src="..\public\pnotify\js\pnotify.buttons.js"></script>


<script>
    $(document).ready(function() {
        // Definir objeto de lenguaje español directamente
        var spanishLanguage = {
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "No se encontraron resultados",
            "sEmptyTable":     "Ningún dato disponible en esta tabla",
            "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":    "",
            "sSearch":         "Buscar:",
            "sUrl":            "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            },
            "buttons": {
                "copy": "Copiar",
                "colvis": "Visibilidad"
            }
        };

        // Initialize DataTables with Spanish language
        $('#tablaCargo').DataTable({
            "language": spanishLanguage,
            "initComplete": function() {
                $(window).trigger('resize');
            }
        });

        $('#tablaDireccion').DataTable({
            "language": spanishLanguage
        });

        $('#tablaSubsecretaria').DataTable({
            "language": spanishLanguage
        });

        // Fix for tabs and DataTables
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        });

        // Force cargo tab to be active and visible on page load
        setTimeout(function() {
            $('#cargo-tab').tab('show');
            $('#tablaCargo').DataTable().columns.adjust();
        }, 100);
    });

    // En organigrama.php
    function advertencia(e) {
        e.preventDefault();
        var url = e.currentTarget.getAttribute('href');
        
        Swal.fire({
            title: '¿Está seguro?',
            text: "Esta acción no se puede revertir",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                enviarPost(url);
            }
        });
    }
</script>
<!-- por ultimo se carga el footer -->
<?php require('./layout/footer.php'); ?>