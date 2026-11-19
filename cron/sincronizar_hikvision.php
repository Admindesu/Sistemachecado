<?php
/**
 * Consulta periódica al terminal Hikvision. Recupera los marcajes que no
 * llegaron por el envío en tiempo real (servidor apagado, red caída, etc.).
 *
 * Uso:   php cron/sincronizar_hikvision.php [--desde=AAAA-MM-DD]
 * Docker: lo ejecuta el servicio "worker" cada HIK_INTERVALO_SEG segundos.
 * XAMPP:  programarlo con el Programador de tareas de Windows.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

include __DIR__ . '/../modelo/conexion.php';
require_once __DIR__ . '/../modelo/hikvision.php';

$cfg = hik_config();
$opciones = getopt('', ['desde:']);
$ahora = new DateTime();

if (!empty($opciones['desde'])) {
    $desde = new DateTime($opciones['desde'] . ' 00:00:00');
} else {
    // Desde el último marcaje guardado (con 30 min de traslape), o los últimos N días
    $res = consulta($conexion, "SELECT MAX(fecha) AS ultima FROM marcaje_dispositivo WHERE dispositivo = ?",
        "s", $cfg['dispositivo']);
    $ultima = $res ? $res->fetch_object()->ultima : null;
    $desde = $ultima
        ? (new DateTime($ultima))->modify('-30 minutes')
        : (clone $ahora)->modify('-' . $cfg['dias_iniciales'] . ' days')->setTime(0, 0);
}

$log = fn(string $msg) => fwrite(STDOUT, '[' . date('Y-m-d H:i:s') . "] $msg\n");

try {
    $eventos = hik_buscar_eventos($cfg, $desde, $ahora);
    $stats = procesar_eventos($conexion, $eventos, $cfg);
    $log(sprintf('Sincronizado %s -> %s: %d eventos, %d marcajes nuevos, %d ignorados',
        $desde->format('Y-m-d H:i'), $ahora->format('Y-m-d H:i'),
        $stats['recibidos'], $stats['nuevos'], $stats['ignorados']));
    exit(0);
} catch (Throwable $e) {
    $log('ERROR: ' . $e->getMessage());
    exit(1);
}
