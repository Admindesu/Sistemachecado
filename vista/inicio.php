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
    <h4 class="text-center text-secondary">Asistencia de empleados</h4>

    <?php
    include "../modelo/conexion.php";
    include "../controlador/controlador_eliminar_asistencia.php";
    // Consulta para obtener los datos de asistencia, empleado y cargo
    // Se utiliza INNER JOIN para combinar las tablas asistencia, empleado y cargo
    $sql = $conexion->query("SELECT 
    asistencia.id_asistencia,
    asistencia.dni,
    asistencia.tipo,
    asistencia.fecha,
    asistencia.estado,
    asistencia.origen,
    empleado.nombre as 'nom_empleado',
    empleado.apellido,
    empleado.dni,
    empleado.cargo,
    cargo.id_cargo,
    cargo.nombre as 'nom_cargo'
    FROM 
    asistencia
    INNER JOIN empleado ON asistencia.dni = empleado.dni
    INNER JOIN cargo ON empleado.cargo = cargo.id_cargo
    ORDER BY asistencia.fecha DESC, asistencia.id_asistencia DESC");

    if (!$sql) {
        die("Error en la consulta: " . $conexion->error);
    }
    ?>

    <div class="container-fluid">
        <div class="mb-3">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalFiltros" data-formato="pdf">
                <i class="fas fa-file-pdf"></i> Generar Reporte PDF
            </button>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalFiltros" data-formato="excel">
                <i class="fas fa-file-excel"></i> Exportar a Excel
            </button>
        </div>
        
        <!-- Modal Filtros -->
        <div class="modal fade" id="modalFiltros" tabindex="-1" aria-labelledby="modalFiltrosLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalFiltrosLabel">Filtros para Reporte</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="reportForm" method="POST" target="_blank">
<?= campo_csrf() ?>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Rango de Fechas (Opcional)</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Fecha Inicio</label>
                                        <input type="date" class="form-control" name="fecha_inicio">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Fecha Fin</label>
                                        <input type="date" class="form-control" name="fecha_fin">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Empleado (Opcional)</label>
                                <select class="form-control" name="empleado">
                                    <option value="">Todos los empleados</option>
                                    <?php
                                    $empleados = $conexion->query("SELECT dni, nombre, apellido FROM empleado ORDER BY nombre, apellido");
                                    while($emp = $empleados->fetch_object()): ?>
                                        <option value="<?= e($emp->dni) ?>"><?= e($emp->nombre . ' ' . $emp->apellido) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary" id="btnGenerarReporte">Generar Reporte</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover w-100 table-striped" id="example" data-order='[[5, "desc"]]'>
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Empleado</th>
                        <th scope="col">NoEmpleado</th>
                        <th scope="col">Cargo</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Fecha/Hora</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Origen</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($datos = $sql->fetch_object()) { ?>
                    <tr>
                        <td><?= e($datos->id_asistencia) ?></td>
                        <td><?= e($datos->nom_empleado . " " . $datos->apellido) ?></td>
                        <td><?= e($datos->dni) ?></td>
                        <td><?= e($datos->nom_cargo) ?></td>
                        <td><?= e(ucfirst($datos->tipo)) ?></td>
                        <td><?= e($datos->fecha) ?></td>
                        <td><?= e(str_replace('_', ' ', (string) $datos->estado)) ?></td>
                        <td><?= $datos->origen === 'hikvision' ? '<span class="badge badge-info">Hikvision</span>' : '<span class="badge badge-secondary">Web</span>' ?></td>
                        <td>
                            <a href="inicio.php?id=<?= e($datos->id_asistencia) ?>" onclick="advertencia(event)" class="btn btn-danger">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
<!-- fin del contenido principal -->


<!-- por ultimo se carga el footer -->
<?php require('./layout/footer.php'); ?>

<script>
// El mismo modal de filtros sirve para el PDF y para Excel; el botón que lo abre elige el formato.
const formatosReporte = {
    pdf:   { action: 'reportes/pdf_report.php',   titulo: 'Filtros para Reporte PDF', boton: '<i class="fas fa-file-pdf"></i> Generar PDF',      clase: 'btn btn-primary', target: '_blank' },
    excel: { action: 'reportes/excel_report.php', titulo: 'Exportar a Excel',         boton: '<i class="fas fa-file-excel"></i> Descargar Excel', clase: 'btn btn-success', target: '_self' }
};
function elegirFormatoReporte(formato) {
    const f = formatosReporte[formato] || formatosReporte.pdf;
    const form = document.getElementById('reportForm');
    form.action = f.action;
    form.target = f.target;   // el Excel se descarga, no hace falta abrir otra pestaña
    document.getElementById('modalFiltrosLabel').textContent = f.titulo;
    const boton = document.getElementById('btnGenerarReporte');
    boton.innerHTML = f.boton;
    boton.className = f.clase;
}
document.querySelectorAll('[data-target="#modalFiltros"]').forEach(function (b) {
    b.addEventListener('click', function () { elegirFormatoReporte(this.dataset.formato); });
});
elegirFormatoReporte('pdf');
</script>