<?php
/**
 * Catálogo de días inhábiles (festivos, asuetos, cierres).
 * Los días de este catálogo no generan faltas en el dashboard.
 */
require_once __DIR__ . '/../controlador/auth.php';
requerir_admin();   // en POST también valida el token CSRF
include "../modelo/conexion.php";
require_once __DIR__ . '/../modelo/calendario.php';

// ---------- Acciones (POST -> redirección) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $msg = 'error';
    if ($accion === 'agregar') {
        $fecha = (string) ($_POST['fecha'] ?? '');
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
        $anual = isset($_POST['anual']) ? 1 : 0;
        $f = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        if (!$f || $f->format('Y-m-d') !== $fecha || $descripcion === '' || mb_strlen($descripcion) > 150) {
            $msg = 'invalido';
        } else {
            $existe = consulta($conexion, "SELECT COUNT(*) AS c FROM dia_inhabil WHERE fecha = ?", "s", $fecha);
            if ($existe && (int) $existe->fetch_object()->c > 0) {
                $msg = 'duplicado';
            } else {
                $msg = consulta($conexion, "INSERT INTO dia_inhabil (fecha, descripcion, anual) VALUES (?, ?, ?)",
                    "ssi", $fecha, $descripcion, $anual) ? 'agregado' : 'error';
            }
        }
    } elseif ($accion === 'eliminar') {
        $msg = consulta($conexion, "DELETE FROM dia_inhabil WHERE id_dia_inhabil = ?", "i", (int) ($_POST['id'] ?? 0))
            ? 'eliminado' : 'error';
    }
    header('Location: dias_inhabiles.php?msg=' . $msg);
    exit;
}

$mensajes = [
    'agregado' => ['success', 'Día inhábil agregado.'],
    'eliminado' => ['success', 'Día inhábil eliminado.'],
    'duplicado' => ['warning', 'Esa fecha ya está en el catálogo.'],
    'invalido' => ['danger', 'Revisa la fecha y la descripción (máximo 150 caracteres).'],
    'error' => ['danger', 'No se pudo guardar el cambio.'],
];
$mensaje = $mensajes[$_GET['msg'] ?? ''] ?? null;

$dias = consulta($conexion, "SELECT id_dia_inhabil, fecha, descripcion, anual FROM dia_inhabil ORDER BY anual DESC, fecha DESC");
$dias = $dias ? $dias->fetch_all(MYSQLI_ASSOC) : null;   // null: falta aplicar la migración
$meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$dias_semana = [1 => 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
?>
<?php require('./layout/topbar.php'); ?>
<?php require('./layout/sidebar.php'); ?>

<div class="page-content">
    <div class="container-fluid">
        <h4 class="text-center text-secondary">Días inhábiles</h4>
        <p class="text-center text-muted">Festivos, asuetos y cierres. Estos días no generan faltas en el dashboard.</p>

        <?php if ($mensaje): ?>
            <div class="alert alert-<?= $mensaje[0] ?>"><?= e($mensaje[1]) ?></div>
        <?php endif; ?>

        <?php if ($dias === null): ?>
            <div class="alert alert-danger">Falta aplicar la migración <code>sql/migraciones/002_dias_inhabiles_justificaciones.sql</code>.</div>
        <?php else: ?>

        <div class="card mb-4">
            <div class="card-body">
                <form method="POST" class="form-row align-items-end">
                    <?= campo_csrf() ?>
                    <input type="hidden" name="accion" value="agregar">
                    <div class="col-md-3 mb-2">
                        <label for="fecha">Fecha</label>
                        <input type="date" id="fecha" name="fecha" class="form-control" required>
                    </div>
                    <div class="col-md-5 mb-2">
                        <label for="descripcion">Descripción</label>
                        <input type="text" id="descripcion" name="descripcion" class="form-control" maxlength="150" required
                               placeholder="Ej. Día de la Revolución">
                    </div>
                    <div class="col-md-2 mb-2">
                        <div class="form-check">
                            <input type="checkbox" id="anual" name="anual" value="1" class="form-check-input">
                            <label for="anual" class="form-check-label">Se repite cada año</label>
                        </div>
                    </div>
                    <div class="col-md-2 mb-2">
                        <button type="submit" class="btn btn-primary btn-block">Agregar</button>
                    </div>
                </form>
                <small class="text-muted">Marca "se repite cada año" solo para fechas fijas (1 de enero, 16 de septiembre, 25 de diciembre).
                    Los festivos que cambian de día (p. ej. el tercer lunes de noviembre) se agregan cada año.</small>
            </div>
        </div>

        <table class="table table-bordered table-hover w-100" id="example" data-order='[]'>
            <thead><tr><th>Fecha</th><th>Descripción</th><th>Repetición</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($dias as $d): $f = new DateTimeImmutable($d['fecha']); ?>
                <tr>
                    <td data-order="<?= e($d['anual'] ? '0000-' . $f->format('m-d') : $d['fecha']) ?>">
                        <?php if ($d['anual']): ?>
                            <?= (int) $f->format('j') ?> de <?= $meses[(int) $f->format('n')] ?>
                        <?php else: ?>
                            <?= $dias_semana[(int) $f->format('N')] ?> <?= (int) $f->format('j') ?> de <?= $meses[(int) $f->format('n')] ?> de <?= $f->format('Y') ?>
                        <?php endif; ?>
                    </td>
                    <td><?= e($d['descripcion']) ?></td>
                    <td><?= $d['anual'] ? 'Cada año' : 'Solo esa fecha' ?></td>
                    <td>
                        <a href="dias_inhabiles.php?accion=eliminar&id=<?= (int) $d['id_dia_inhabil'] ?>" onclick="advertencia(event)" class="btn btn-danger btn-sm">
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

<?php require('./layout/footer.php'); ?>
