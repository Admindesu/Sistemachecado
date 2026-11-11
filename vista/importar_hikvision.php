<?php
/**
 * Importar empleados desde el terminal Hikvision.
 * GET:  lee los usuarios del dispositivo y muestra la vista previa.
 * POST: da de alta los seleccionados con los datos comunes elegidos y procesa
 *       los marcajes que ya tuvieran guardados.
 */
require_once __DIR__ . '/../controlador/auth.php';
requerir_admin();
include "../modelo/conexion.php";
require_once __DIR__ . '/../modelo/hikvision.php';

$cfg = hik_config();
$error = null;
$importados = [];   // [employeeNo, nombre, apellido, usuario, password temporal, días procesados]
$omitidos = [];     // [employeeNo, motivo]

function password_temporal(): string
{
    $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
    $p = '';
    for ($i = 0; $i < 8; $i++) {
        $p .= $caracteres[random_int(0, strlen($caracteres) - 1)];
    }
    return $p;
}

function usuario_disponible(mysqli $conexion, string $base): string
{
    $usuario = $base;
    for ($i = 2; ; $i++) {
        $r = consulta($conexion, "SELECT COUNT(*) AS c FROM empleado WHERE usuario = ?", "s", $usuario);
        if ($r && (int) $r->fetch_object()->c === 0) return $usuario;
        $usuario = $base . '_' . $i;
    }
}

// ---------- Importar (POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnimportar'])) {
    $cargo = (int) ($_POST['cargo'] ?? 0);
    $direccion = (int) ($_POST['direccion'] ?? 0);
    $subsecretaria = usar_subsecretarias() ? ((int) ($_POST['subsecretaria'] ?? 0) ?: null) : null; // opcional
    $horario = (int) ($_POST['horario'] ?? 0);
    $seleccion = $_POST['sel'] ?? [];

    if (!$cargo || !$direccion || !$horario) {
        $error = 'Selecciona cargo, dirección y horario.';
    } elseif (!$seleccion) {
        $error = 'No seleccionaste ningún empleado.';
    } else {
        foreach ($seleccion as $no) {
            $no = trim((string) $no);
            $nombre = trim((string) ($_POST['nombre'][$no] ?? ''));
            $apellido = trim((string) ($_POST['apellido'][$no] ?? ''));
            if ($no === '' || $nombre === '' || $apellido === '') {
                $omitidos[] = [$no, 'Nombre o apellido vacío'];
                continue;
            }
            $existe = consulta($conexion, "SELECT COUNT(*) AS c FROM empleado WHERE dni = ?", "s", $no);
            if ($existe && (int) $existe->fetch_object()->c > 0) {
                $omitidos[] = [$no, 'Ya existe en el sistema'];
                continue;
            }
            $usuario = usuario_disponible($conexion, $no);
            $temporal = password_temporal();
            $ok = consulta($conexion,
                "INSERT INTO empleado (nombre, apellido, dni, cargo, direccion, subsecretaria, usuario, password, is_admin, id_horario)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?)",
                "sssiiissi", $nombre, $apellido, $no, $cargo, $direccion, $subsecretaria, $usuario,
                hash_password($temporal), $horario);
            if (!$ok) {
                $omitidos[] = [$no, 'Error al guardar'];
                continue;
            }
            // Los marcajes que llegaron antes de existir en el sistema se procesan ahora
            $dias = recalcular_empleado($conexion, $no);
            $importados[] = [$no, $nombre, $apellido, $usuario, $temporal, $dias];
        }
    }
}

// ---------- Vista previa (GET, o POST con error) ----------
$usuarios_dispositivo = [];
if (!$importados && !$omitidos) {
    try {
        $usuarios_dispositivo = hik_buscar_usuarios($cfg);
        foreach ($usuarios_dispositivo as &$u) {
            $r = consulta($conexion, "SELECT COUNT(*) AS c FROM empleado WHERE dni = ?", "s", $u['employeeNo']);
            $u['existe'] = $r && (int) $r->fetch_object()->c > 0;
            [$u['nombre'], $u['apellido']] = dividir_nombre($u['name']);
        }
        unset($u);
        // Primero los nuevos
        usort($usuarios_dispositivo, fn($a, $b) => [$a['existe'], $a['employeeNo']] <=> [$b['existe'], $b['employeeNo']]);
    } catch (Throwable $e) {
        $error = 'No se pudo leer el dispositivo: ' . $e->getMessage();
    }
}

$catalogo = fn(string $sql) => consulta($conexion, $sql)->fetch_all(MYSQLI_ASSOC);
$cargos = $catalogo("SELECT id_cargo AS id, nombre FROM cargo ORDER BY nombre");
$direcciones = $catalogo("SELECT id_direccion AS id, nombre FROM direccion ORDER BY nombre");
$subsecretarias = $catalogo("SELECT id_subsecretaria AS id, nombre FROM subsecretaria ORDER BY nombre");
$horarios = $catalogo("SELECT id_horario AS id, CONCAT(nombre, ' (', TIME_FORMAT(hora_entrada, '%H:%i'), ' - ', TIME_FORMAT(hora_salida, '%H:%i'), ')') AS nombre FROM horarios ORDER BY nombre");
$nuevos = array_filter($usuarios_dispositivo, fn($u) => !$u['existe']);
$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES);
?>
<style>
ul li:nth-child(2) .activo {
    background: rgb(171, 11, 61) !important;
}
</style>
<?php require('./layout/topbar.php'); ?>
<?php require('./layout/sidebar.php'); ?>

<div class="page-content">
    <div class="container-fluid">
        <h4 class="text-center text-secondary">Importar empleados desde Hikvision</h4>
        <p class="text-center text-muted">Dispositivo: <?= $e($cfg['host'] ?: 'sin configurar (HIK_HOST)') ?></p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $e($error) ?></div>
        <?php endif; ?>

        <?php if ($importados || $omitidos): ?>
            <?php if ($importados): ?>
                <div class="alert alert-success">
                    Se importaron <?= count($importados) ?> empleados.
                    <strong>Anota las contraseñas temporales: no se vuelven a mostrar.</strong>
                    Las necesitan solo para el checado web o el panel.
                </div>
                <table class="table table-bordered table-sm">
                    <thead><tr><th>NoEmpleado</th><th>Nombre</th><th>Apellido</th><th>Usuario</th><th>Contraseña temporal</th><th>Días con marcajes procesados</th></tr></thead>
                    <tbody>
                    <?php foreach ($importados as [$no, $nom, $ape, $usr, $tmp, $dias]): ?>
                        <tr><td><?= $e($no) ?></td><td><?= $e($nom) ?></td><td><?= $e($ape) ?></td>
                            <td><?= $e($usr) ?></td><td><code><?= $e($tmp) ?></code></td><td><?= (int) $dias ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            <?php if ($omitidos): ?>
                <div class="alert alert-warning">No se importaron <?= count($omitidos) ?> registros:</div>
                <ul>
                    <?php foreach ($omitidos as [$no, $motivo]): ?>
                        <li><?= $e($no) ?>: <?= $e($motivo) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <a href="empleado.php" class="btn btn-primary">Ir a empleados</a>
            <a href="importar_hikvision.php" class="btn btn-secondary">Importar más</a>

        <?php elseif ($usuarios_dispositivo): ?>
            <p><?= count($usuarios_dispositivo) ?> personas en el dispositivo, <?= count($nuevos) ?> nuevas.
               Revisa cómo se dividió el nombre antes de importar.</p>
            <form method="POST">
                <?= campo_csrf() ?>
                <div class="row mb-3">
                    <?php
                    $comunes = ['cargo' => $cargos, 'direccion' => $direcciones, 'subsecretaria' => $subsecretarias, 'horario' => $horarios];
                    if (!usar_subsecretarias()) unset($comunes['subsecretaria']);
                    foreach ($comunes as $campo => $opciones): ?>
                        <div class="col-md-3">
                            <label><?= ucfirst($campo === 'direccion' ? 'dirección' : ($campo === 'subsecretaria' ? 'subsecretaría' : $campo)) ?> (<?= $campo === 'subsecretaria' ? 'opcional' : 'para todos' ?>)</label>
                            <select name="<?= $campo ?>" class="form-control" <?= $campo === 'subsecretaria' ? '' : 'required' ?>>
                                <option value=""><?= $campo === 'subsecretaria' ? 'Sin subsecretaría' : 'Seleccionar...' ?></option>
                                <?php foreach ($opciones as $o): ?>
                                    <option value="<?= (int) $o['id'] ?>" <?= (int) ($_POST[$campo] ?? 0) === (int) $o['id'] ? 'selected' : '' ?>><?= $e($o['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>
                </div>

                <table class="table table-bordered table-sm">
                    <thead><tr>
                        <th><input type="checkbox" id="todos" checked></th>
                        <th>NoEmpleado</th><th>Nombre en el dispositivo</th><th>Nombre(s)</th><th>Apellidos</th><th>Estado</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($usuarios_dispositivo as $u): $no = $e($u['employeeNo']); ?>
                        <tr class="<?= $u['existe'] ? 'text-muted' : '' ?>">
                            <td><?php if (!$u['existe']): ?><input type="checkbox" class="sel" name="sel[]" value="<?= $no ?>" checked><?php endif; ?></td>
                            <td><?= $no ?></td>
                            <td><?= $e($u['name']) ?></td>
                            <?php if ($u['existe']): ?>
                                <td colspan="2"></td><td><span class="badge badge-secondary">Ya existe</span></td>
                            <?php else: ?>
                                <td><input type="text" class="form-control form-control-sm" name="nombre[<?= $no ?>]" value="<?= $e($u['nombre']) ?>"></td>
                                <td><input type="text" class="form-control form-control-sm" name="apellido[<?= $no ?>]" value="<?= $e($u['apellido']) ?>"></td>
                                <td><span class="badge badge-success">Nuevo</span></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if ($nuevos): ?>
                    <p class="text-muted">Usuario de acceso = NoEmpleado. Se genera una contraseña temporal para cada uno.</p>
                    <button type="submit" name="btnimportar" value="1" class="btn btn-primary">Importar seleccionados</button>
                <?php endif; ?>
                <a href="empleado.php" class="btn btn-secondary">Cancelar</a>
            </form>
            <script>
                document.getElementById('todos').addEventListener('change', function () {
                    document.querySelectorAll('.sel').forEach(c => c.checked = this.checked);
                });
            </script>

        <?php elseif (!$error): ?>
            <div class="alert alert-info">El dispositivo no tiene usuarios registrados.</div>
        <?php endif; ?>
    </div>
</div>

<?php require('./layout/footer.php'); ?>
