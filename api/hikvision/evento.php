<?php
/**
 * Receptor del "HTTP Listening" del terminal Hikvision.
 * Configurar en el dispositivo: IP del servidor, puerto APP_PORT y la ruta
 *   /api/hikvision/evento.php?token=<HIK_PUSH_TOKEN>
 *
 * El dispositivo envía cada evento como JSON (cuerpo directo o como parte
 * multipart "event_log"/"AccessControllerEvent", a veces junto con una foto).
 * Siempre se responde 200 rápido para que el dispositivo no reintente sin fin.
 */
include __DIR__ . '/../../modelo/conexion.php';
require_once __DIR__ . '/../../modelo/hikvision.php';

header('Content-Type: application/json');
$cfg = hik_config();

function responder(int $codigo, string $mensaje, array $extra = []): void
{
    http_response_code($codigo);
    echo json_encode(['status' => $codigo === 200 ? 'ok' : 'error', 'message' => $mensaje] + $extra);
    exit;
}

// --- Autorización: token obligatorio y, opcionalmente, IP de origen ---
if ($cfg['token'] === '' || !hash_equals($cfg['token'], (string) ($_GET['token'] ?? ''))) {
    error_log('Hikvision push: token inválido desde ' . ($_SERVER['REMOTE_ADDR'] ?? '?'));
    responder(403, 'Token inválido');
}
if ($cfg['ips_permitidas'] && !in_array($_SERVER['REMOTE_ADDR'] ?? '', $cfg['ips_permitidas'], true)) {
    error_log('Hikvision push: IP no permitida ' . ($_SERVER['REMOTE_ADDR'] ?? '?'));
    responder(403, 'IP no permitida');
}

// --- Extraer los eventos JSON del cuerpo ---
$eventos = [];
$cuerpo = file_get_contents('php://input');
$json = $cuerpo !== '' ? json_decode($cuerpo, true) : null;
if (is_array($json)) {
    $eventos[] = $json;
}
foreach ($_POST as $valor) {          // multipart/form-data
    $parte = is_string($valor) ? json_decode($valor, true) : null;
    if (is_array($parte)) {
        $eventos[] = $parte;
    }
}

if (!$eventos) {
    responder(200, 'Sin eventos');
}

try {
    $stats = procesar_eventos($conexion, $eventos, $cfg);
    responder(200, 'Procesado', $stats);
} catch (Throwable $e) {
    error_log('Hikvision push: ' . $e->getMessage());
    // 200 igualmente: el marcaje se recupera en la siguiente consulta periódica
    responder(200, 'Error interno; se reintentará en la sincronización');
}
