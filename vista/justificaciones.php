<?php
/**
 * Justificaciones (permisos) de entrada, salida o día completo.
 * Desde el dashboard se llega con ?dni=&fecha=&tipo= para llenar el formulario.
 */
require_once __DIR__ . '/../controlador/auth.php';
requerir_admin();   // en POST también valida el token CSRF
include "../modelo/conexion.php";
require_once __DIR__ . '/../modelo/calendario.php';

function leer_fecha(string $v): ?DateTimeImmutable
{
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $v);
    return ($d && $d->format('Y-m-d') === $v) ? $d : null;
}

// ---------- Acciones (POST -> redirección) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $msg = 'error';
    if ($accion === 'agregar') {
        $dni = (string) ($_POST['dni'] ?? '');
        $tipo = (string) ($_POST['tipo'] ?? '');
        $motivo = (string) ($_POST['motivo'] ?? '');
        $inicio = leer_fecha((string) ($_POST['fecha_inicio'] ?? ''));
        $fin = leer_fecha((string) ($_POST['fecha_fin'] ?? '')) ?? $inicio;
        $observaciones = trim((string) ($_POST['observaciones'] ?? ''));
        $existe = consulta($conexion, "SELECT COUNT(*) AS c FROM empleado WHERE dni = ?", "s", $dni);

        if (!$existe || (int) $existe->fetch_object()->c === 0) {
            $msg = 'empleado';
        } elseif (!isset(TIPOS_JUSTIFICACION[$tipo]) || !in_array($motivo, MOTIVOS_JUSTIFICACION, true)) {
            $msg = 'invalido';
        } elseif (!$inicio || !$fin || $fin < $inicio) {
            $msg = 'fechas';
        } elseif ($inicio->diff($fin)->days >= MAX_DIAS_JUSTIFICACION) {
            $msg = 'largo';
        } elseif (mb_strlen($observaciones) > 500) {
            $msg = 'invalido';
        } else {
            $ok = consulta($conexion,
                "INSERT INTO justificacion (dni, fecha_inicio, fecha_fin, tipo, motivo, observaciones, registrado_por)
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                "ssssssi", $dni, $inicio->format('Y-m-d'), $fin->format('Y-m-d'), $tipo, $motivo,
                $observaciones !== '' ? $observaciones : null, (int) $_SESSION['id_usuario']);
            $msg = $ok ? 'agregado' : 'error';
        }
    } elseif ($accion === 'eliminar') {
        $msg = consulta($conexion, "DELETE FROM justificacion WHERE id_justificacion = ?", "i", (int) ($_POST['id'] ?? 0))
            ? 'eliminado' : 'error';
    }
    header('Location: justificaciones.php?msg=' . $msg);
    exit;
}

$mensajes = [
    'agregado' => ['success', 'Justificación registrada. Ya se refleja en el dashboard.'],
    'eliminado' => ['success', 'Justificación eliminada.'],
    'empleado' => ['danger', 'Selecciona un empleado válido.'],
    'fechas' => ['danger', 'Revisa las fechas: la fecha final no puede ser anterior a la inicial.'],
    'largo' => ['danger', 'Una justificación no puede abarcar más de un año.'],
    'invalido' => ['danger', 'Revisa el tipo, el motivo y las observaciones (máximo 500 caracteres).'],
    'error' => ['danger', 'No se pudo guardar el cambio.'],
];
$mensaje = $mensajes[$_GET['msg'] ?? ''] ?? null;

// Valores iniciales del formulario (desde el enlace "Justificar" del dashboard)
$pre_dni = (string) ($_GET['dni'] ?? '');
$pre_fecha = leer_fecha((string) ($_GET['fecha'] ?? ''));
$pre_fecha = $pre_fecha ? $pre_fecha->format('Y-m-d') : date('Y-m-d');
$pre_tipo = isset(TIPOS_JUSTIFICACION[$_GET['tipo'] ?? '']) ? $_GET['tipo'] : 'entrada';

$empleados = consulta($conexion, "SELECT dni, nombre, apellido FROM empleado ORDER BY nombre, apellido")->fetch_all(MYSQLI_ASSOC);
$lista = consulta($conexion,
    "SELECT j.id_justificacion, j.dni, j.fecha_inicio, j.fecha_fin, j.tipo, j.motivo, j.observaciones, j.creado_en,
            CONCAT(e.nombre, ' ', e.apellido) AS empleado,
            CONCAT(a.nombre, ' ', a.apellido) AS registrado_por
     FROM justificacion j
     JOIN empleado e ON e.dni = j.dni
     LEFT JOIN empleado a ON a.id_empleado = j.registrado_por
     ORDER BY j.fecha_inicio DESC, j.id_justificacion DESC");
$lista = $lista ? $lista->fetch_all(MYSQLI_ASSOC) : null;   // null: falta aplicar la migración
$tipos_cortos = ['entrada' => 'Entrada', 'salida' => 'Salida', 'dia_completo' => 'Día completo'];
?>
<?php require('./layout/topbar.php'); ?>
<?php require('./layout/sidebar.php'); ?>

<div class="page-content">
    <div class="container-fluid">
        <h4 class="text-center text-secondary">Justificaciones de asistencia</h4>
        <p class="text-center text-muted">Permisos que justifican una entrada, una salida o días completos. No modifican los registros de checado: el dashboard los toma en cuenta.</p>

        <?php if ($mensaje): ?>
            <div class="alert alert-<?= $mensaje[0] ?>"><?= e($mensaje[1]) ?></div>
        <?php endif; ?>

        <?php if ($lista === null): ?>
            <div class="alert alert-danger">Falta aplicar la migración <code>sql/migraciones/002_dias_inhabiles_justificaciones.sql</code>.</div>
        <?php else: ?>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Nueva justificación</h5>
                <form method="POST">
                    <?= campo_csrf() ?>
                    <input type="hidden" name="accion" value="agregar">
                    <div class="form-row">
                        <div class="col-md-4 mb-2">
                            <label for="dni">Empleado</label>
                            <select id="dni" name="dni" class="form-control" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($empleados as $emp): ?>
                                    <option value="<?= e($emp['dni']) ?>" <?= $emp['dni'] === $pre_dni ? 'selected' : '' ?>>
                                        <?= e($emp['nombre'] . ' ' . $emp['apellido']) ?> (<?= e($emp['dni']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="tipo">Qué se justifica</label>
                            <select id="tipo" name="tipo" class="form-control" required>
                                <?php foreach (TIPOS_JUSTIFICACION as $clave => $texto): ?>
                                    <option value="<?= $clave ?>" <?= $clave === $pre_tipo ? 'selected' : '' ?>><?= e($texto) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="motivo">Motivo</label>
                            <select id="motivo" name="motivo" class="form-control" required>
                                <?php foreach (MOTIVOS_JUSTIFICACION as $m): ?>
                                    <option value="<?= e($m) ?>"><?= e($m) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-3 mb-2">
                            <label for="fecha_inicio">Desde</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" required value="<?= e($pre_fecha) ?>">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="fecha_fin">Hasta</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" required value="<?= e($pre_fecha) ?>">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="observaciones">Observaciones (opcional)</label>
                            <input type="text" id="observaciones" name="observaciones" class="form-control" maxlength="500"
                                   placeholder="Ej. Oficio 123/2026, cita en IMSS">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Registrar justificación</button>
                </form>
            </div>
        </div>

        <table class="table table-bordered table-hover w-100" id="example" data-order='[]'>
            <thead><tr>
                <th>Empleado</th><th>Justifica</th><th>Desde</th><th>Hasta</th><th>Motivo</th><th>Observaciones</th><th>Registró</th><th>Acciones</th>
            </tr></thead>
            <tbody>
            <?php foreach ($lista as $j): ?>
                <tr>
                    <td><?= e($j['empleado']) ?><br><small class="text-muted"><?= e($j['dni']) ?></small></td>
                    <td><?= e($tipos_cortos[$j['tipo']] ?? $j['tipo']) ?></td>
                    <td data-order="<?= e($j['fecha_inicio']) ?>"><?= e(date('d/m/Y', strtotime($j['fecha_inicio']))) ?></td>
                    <td data-order="<?= e($j['fecha_fin']) ?>"><?= e(date('d/m/Y', strtotime($j['fecha_fin']))) ?></td>
                    <td><?= e($j['motivo']) ?></td>
                    <td><?= e($j['observaciones']) ?></td>
                    <td><?= e($j['registrado_por']) ?><br><small class="text-muted"><?= e(date('d/m/Y H:i', strtotime($j['creado_en']))) ?></small></td>
                    <td>
                        <a href="justificaciones.php?accion=eliminar&id=<?= (int) $j['id_justificacion'] ?>" onclick="advertencia(event)" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash" aria-hidden="true"></i> Eliminar
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<script>
// "Hasta" no puede ser anterior a "Desde"
document.getElementById('fecha_inicio') && document.getElementById('fecha_inicio').addEventListener('change', function () {
    var fin = document.getElementById('fecha_fin');
    fin.min = this.value;
    if (fin.value < this.value) fin.value = this.value;
});
</script>

<?php require('./layout/footer.php'); ?>
