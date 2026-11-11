<?php
/**
 * Control de acceso compartido.
 *
 * Uso:
 *   require_once __DIR__ . '/auth.php';        // desde controlador/
 *   requerir_sesion();      // cualquier usuario con sesión iniciada
 *   requerir_admin();       // solo administradores
 *   requerir_admin(true);   // igual, pero responde JSON (para endpoints fetch/AJAX)
 *
 * Toda petición POST que pase por requerir_sesion()/requerir_admin() debe traer
 * el token CSRF: en formularios con <?= campo_csrf() ?>, en fetch con el campo
 * "csrf" o la cabecera X-CSRF-Token (el token está en <meta name="csrf-token">).
 *
 * Para imprimir cualquier dato en HTML usar e():  <?= e($datos->nombre) ?>
 */

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',   // el navegador no envía la cookie en POST desde otros sitios
    ]);
    session_start();
}

/** Escapa un valor para imprimirlo en HTML (contenido o atributos). */
function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/* ---------- CSRF ---------- */
function token_csrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function campo_csrf(): string
{
    return '<input type="hidden" name="csrf" value="' . e(token_csrf()) . '">';
}

function verificar_csrf(bool $json = false): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }
    $enviado = (string) ($_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if ($enviado !== '' && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $enviado)) {
        return;
    }
    error_log('CSRF: token inválido en ' . ($_SERVER['REQUEST_URI'] ?? '?'));
    http_response_code(403);
    if ($json) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Solicitud inválida o sesión expirada. Recarga la página.']);
    } else {
        echo '<!doctype html><meta charset="utf-8"><title>Solicitud inválida</title>'
           . '<p style="font-family:sans-serif;padding:2em">La solicitud no es válida o la sesión expiró. '
           . '<a href="javascript:history.back()">Regresa</a>, recarga la página e inténtalo de nuevo.</p>';
    }
    exit;
}

function hay_sesion(): bool
{
    return !empty($_SESSION['dni']);
}

function es_admin(): bool
{
    return hay_sesion() && isset($_SESSION['is_admin']) && (int) $_SESSION['is_admin'] === 1;
}

/**
 * Ruta relativa a un archivo de la aplicación desde el script que se está
 * ejecutando, para que funcione igual desde la raíz, controlador/ o vista/reportes/.
 */
function ruta_app(string $ruta): string
{
    $raiz = str_replace('\\', '/', realpath(__DIR__ . '/..'));
    $dir_script = str_replace('\\', '/', dirname(realpath($_SERVER['SCRIPT_FILENAME'])));
    $relativa = trim(substr($dir_script, strlen($raiz)), '/');
    $prefijo = $relativa === '' ? '' : str_repeat('../', substr_count($relativa, '/') + 1);
    return $prefijo . $ruta;
}

function denegar_acceso(int $codigo, bool $json): void
{
    if ($json) {
        http_response_code($codigo);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $codigo === 401 ? 'No autenticado' : 'Acceso denegado']);
    } elseif ($codigo === 401) {
        header('Location: ' . ruta_app('vista/login/login.php'));
    } else {
        // Usuario sin permisos de administrador: regresa a la pantalla de checado
        header('Location: ' . ruta_app('index.php'));
    }
    exit;
}

function requerir_sesion(bool $json = false): void
{
    if (!hay_sesion()) {
        denegar_acceso(401, $json);
    }
    verificar_csrf($json);
}

function requerir_admin(bool $json = false): void
{
    requerir_sesion($json);
    if (!es_admin()) {
        denegar_acceso(403, $json);
    }
}

/* ---------- Contraseñas ----------
 * Las contraseñas antiguas están en md5 (32 caracteres hex). Se siguen aceptando,
 * y al iniciar sesión se reemplazan por password_hash() (ver login.php).
 */
function hash_password(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function es_hash_md5(string $hash): bool
{
    return (bool) preg_match('/^[a-f0-9]{32}$/i', $hash);
}

function verificar_password(string $password, string $hash): bool
{
    if (es_hash_md5($hash)) {
        return hash_equals(strtolower($hash), md5($password));
    }
    return password_verify($password, $hash);
}

function necesita_rehash(string $hash): bool
{
    return es_hash_md5($hash) || password_needs_rehash($hash, PASSWORD_DEFAULT);
}
