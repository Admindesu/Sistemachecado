<?php
/**
 * Baja (soft delete) y reactivación de empleados, desde vista/empleado.php.
 *
 * Dar de baja NO borra nada: el empleado queda inactivo con su fecha de baja (último día
 * laborado) y un motivo. Sus asistencias, marcajes y justificaciones se conservan y siguen
 * en reportes; ya no puede iniciar sesión ni checar, el terminal deja de generarle
 * asistencias después de esa fecha y el dashboard deja de contarle faltas.
 *
 * POST accion=baja|reactivar, id, fecha_baja (AAAA-MM-DD), motivo_baja.
 * Responde con una redirección a empleado.php?baja=<resultado>.
 */
require_once __DIR__ . '/auth.php';
requerir_admin();   // en POST también valida el token CSRF

include_once __DIR__ . '/../modelo/conexion.php';

function volver_a_empleados_baja(string $resultado, bool $ver_bajas = false): void
{
    header('Location: ../vista/empleado.php?baja=' . $resultado . ($ver_bajas ? '&bajas=1' : ''));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    volver_a_empleados_baja('');
}

$id = (int) ($_POST['id'] ?? 0);
$accion = $_POST['accion'] ?? '';
$res = consulta($conexion, "SELECT id_empleado, dni, activo FROM empleado WHERE id_empleado = ?", "i", $id);
$empleado = $res ? $res->fetch_assoc() : null;
if (!$empleado) {
    volver_a_empleados_baja('no_existe');
}

if ($accion === 'baja') {
    // Un administrador no puede darse de baja a sí mismo (se quedaría fuera del sistema).
    // Se compara por id y por número de empleado, por si la sesión no trae alguno de los dos.
    $es_propia = $id === (int) ($_SESSION['id_usuario'] ?? 0)
        || (string) $empleado['dni'] === (string) ($_SESSION['dni'] ?? '');
    if ($es_propia) {
        volver_a_empleados_baja('propia');
    }
    $fecha = (string) ($_POST['fecha_baja'] ?? '');
    $d = DateTime::createFromFormat('!Y-m-d', $fecha);
    if (!$d || $d->format('Y-m-d') !== $fecha) {
        volver_a_empleados_baja('fecha');
    }
    $motivo = mb_substr(trim((string) ($_POST['motivo_baja'] ?? '')), 0, 150);
    $ok = consulta($conexion,
        "UPDATE empleado SET activo = 0, fecha_baja = ?, motivo_baja = ? WHERE id_empleado = ?",
        "ssi", $fecha, $motivo !== '' ? $motivo : null, $id);
    volver_a_empleados_baja($ok ? 'ok' : 'error');
}

if ($accion === 'reactivar') {
    $ok = consulta($conexion,
        "UPDATE empleado SET activo = 1, fecha_baja = NULL, motivo_baja = NULL WHERE id_empleado = ?",
        "i", $id);
    volver_a_empleados_baja($ok ? 'reactivado' : 'error', !$ok);
}

volver_a_empleados_baja('error');
