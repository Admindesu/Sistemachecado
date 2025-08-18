<?php
   session_start();
   if (empty($_SESSION['nombre']) and empty($_SESSION['apellido'])) {
       header('location: vista/login.php'); // Added space after location:
   }
?>

<style>
ul li:nth-child(1) .activo {
    background: rgb(171, 11, 61) !important;
}

</style>

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
    empleado.nombre as 'nom_empleado',
    empleado.apellido,
    empleado.dni,
    empleado.cargo,
    cargo.id_cargo,
    cargo.nombre as 'nom_cargo'
    FROM 
    asistencia
    INNER JOIN empleado ON asistencia.dni = empleado.dni
    INNER JOIN cargo ON empleado.cargo = cargo.id_cargo");

    if (!$sql) {
        die("Error en la consulta: " . $conexion->error);
    }
    ?>

    <div class="container-fluid">
        <div class="mb-3">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalFiltros">
                <i class="fas fa-file-pdf"></i> Generar Reporte PDF
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
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Formato de Reporte</label>
                                <select class="form-control" name="tipo_reporte" id="tipo_reporte">
                                    <option value="pdf">Reporte PDF</option>
                                    <option value="excel">Reporte Excel</option>
                                </select>
                            </div>
                            
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
                                        <option value="<?= $emp->dni ?>"><?= $emp->nombre . ' ' . $emp->apellido ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Generar Reporte</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover w-100 table-striped" id="example">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Empleado</th>
                        <th scope="col">NoEmpleado</th>
                        <th scope="col">Cargo</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Fecha/Hora</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($datos = $sql->fetch_object()) { ?>
                    <tr>
                        <td><?= $datos->id_asistencia ?></td>
                        <td><?= $datos->nom_empleado. " ". $datos->apellido ?></td>
                        <td><?= $datos->dni ?></td>
                        <td><?= $datos->nom_cargo ?></td>
                        <td><?= ucfirst($datos->tipo) ?></td>
                        <td><?= $datos->fecha ?></td>
                        <td>
                            <a href="inicio.php?id=<?= $datos->id_asistencia ?>" onclick="advertencia(event)" class="btn btn-danger">
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
document.getElementById('tipo_reporte').addEventListener('change', function() {
    const form = document.getElementById('reportForm');
    if (this.value === 'pdf') {
        form.action = 'reportes/pdf_report.php';
    } else {
        form.action = 'reportes/excel_report.php';
    }
});

// Set initial form action
document.getElementById('reportForm').action = 'reportes/pdf_report.php';
</script>