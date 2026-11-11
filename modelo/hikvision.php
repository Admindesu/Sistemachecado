<?php
/**
 * Integración con el terminal Hikvision (ISAPI).
 *
 * Flujo:
 *   evento del dispositivo (push o consulta) -> normalizar_evento()
 *   -> registrar_marcaje() en marcaje_dispositivo (crudo, sin duplicados)
 *   -> recalcular_dia() genera las filas de asistencia con origen 'hikvision'
 *
 * El dispositivo solo dice "esta persona pasó a esta hora". Este sistema decide
 * qué es entrada y qué es salida, con las mismas reglas que el checado web.
 */
require_once __DIR__ . '/asistencia_reglas.php';

const ANTIRREBOTE_MINUTOS = 5;   // marcajes a menos de 5 min de la entrada se ignoran (doble lectura)
const MAJOR_CONTROL_ACCESO = 5;  // major "Event" de control de acceso en ISAPI

function hik_config(): array
{
    $minors = getenv('HIK_MINORS_VALIDOS') ?: '1,38,75,153'; // tarjeta, huella, rostro, rostro + huella autenticados
    return [
        'host' => getenv('HIK_HOST') ?: '',
        'usuario' => getenv('HIK_USUARIO') ?: 'admin',
        'password' => getenv('HIK_PASSWORD') ?: '',
        'token' => getenv('HIK_PUSH_TOKEN') ?: '',
        'ips_permitidas' => array_filter(array_map('trim', explode(',', getenv('HIK_IPS_PERMITIDAS') ?: ''))),
        'minors_validos' => array_map('intval', explode(',', $minors)),
        'dias_iniciales' => (int) (getenv('HIK_DIAS_INICIALES') ?: 7),
        // Identificador del dispositivo en marcaje_dispositivo (con un solo equipo basta la IP)
        'dispositivo' => getenv('HIK_HOST') ?: 'hikvision',
    ];
}

/**
 * Convierte un evento del dispositivo a un formato común. Acepta:
 *  - push (HTTP Listening): {"dateTime": "...", "eventType": "AccessControllerEvent",
 *                            "AccessControllerEvent": {"majorEventType":5,"subEventType":75,"employeeNoString":"..."}}
 *  - consulta (AcsEvent InfoList): {"major":5,"minor":75,"time":"...","employeeNoString":"..."}
 * Devuelve null si no es un marcaje válido de un empleado (puerta abierta, rostro no reconocido, heartbeat...).
 */
function normalizar_evento(array $evento, array $cfg): ?array
{
    if (isset($evento['AccessControllerEvent']) && is_array($evento['AccessControllerEvent'])) {
        $ace = $evento['AccessControllerEvent'];
        $major = (int) ($ace['majorEventType'] ?? 0);
        $minor = (int) ($ace['subEventType'] ?? 0);
        $hora = $evento['dateTime'] ?? null;
    } else {
        $ace = $evento;
        $major = (int) ($evento['major'] ?? 0);
        $minor = (int) ($evento['minor'] ?? 0);
        $hora = $evento['time'] ?? null;
    }

    $dni = trim((string) ($ace['employeeNoString'] ?? $ace['employeeNo'] ?? ''));
    if ($major !== MAJOR_CONTROL_ACCESO || !in_array($minor, $cfg['minors_validos'], true) || $dni === '' || !$hora) {
        return null;
    }

    try {
        // El dispositivo manda la hora con su desfase (p. ej. 2026-11-11T08:01:23-05:00)
        $fecha = new DateTime($hora);
        $fecha->setTimezone(new DateTimeZone(date_default_timezone_get()));
    } catch (Exception $e) {
        error_log("Hikvision: fecha inválida '$hora'");
        return null;
    }

    return [
        'dni' => $dni,
        'fecha' => $fecha,
        'serial_no' => isset($ace['serialNo']) ? (int) $ace['serialNo'] : null,
        'minor' => $minor,
        'metodo' => $ace['currentVerifyMode'] ?? null,
        'datos' => json_encode($evento, JSON_UNESCAPED_UNICODE),
    ];
}

/** Guarda el marcaje crudo. Devuelve true si es nuevo, false si ya existía. */
function registrar_marcaje(mysqli $conexion, array $m, array $cfg): bool
{
    // Sentencia directa (no consulta()) porque se necesita su affected_rows:
    // el de la conexión vale -1 una vez cerrada la sentencia.
    $stmt = $conexion->prepare(
        "INSERT IGNORE INTO marcaje_dispositivo (dispositivo, dni, fecha, serial_no, minor, metodo, datos)
         VALUES (?, ?, ?, ?, ?, ?, ?)");
    $fecha = $m['fecha']->format('Y-m-d H:i:s');
    $stmt->bind_param("sssiiss", $cfg['dispositivo'], $m['dni'], $fecha,
        $m['serial_no'], $m['minor'], $m['metodo'], $m['datos']);
    if (!$stmt->execute()) {
        error_log('Hikvision: error guardando marcaje: ' . $stmt->error);
        return false;
    }
    return $stmt->affected_rows === 1;
}

/**
 * Regenera las filas de asistencia 'hikvision' de un empleado en un día a partir
 * de todos sus marcajes. Es idempotente: el orden de llegada no importa.
 *  - Primer marcaje del día = entrada (si no checó entrada en la web).
 *  - Último marcaje = salida (si no checó salida en la web), ignorando dobles lecturas.
 * A diferencia del checado web, aquí no se rechaza nada: una entrada anticipada
 * cuenta como A_TIEMPO y una salida antes de hora queda como SALIDA_TEMPRANA.
 */
function recalcular_dia(mysqli $conexion, string $dni, string $dia): void
{
    $horario = horario_empleado($conexion, $dni);
    if (!$horario) {
        error_log("Hikvision: employeeNo $dni no existe en empleado; marcaje guardado sin procesar");
        return;
    }
    if (empty($horario['hora_entrada']) || empty($horario['hora_salida'])) {
        error_log("Hikvision: el empleado $dni no tiene horario; marcaje guardado sin procesar");
        return;
    }

    $res = consulta($conexion,
        "SELECT id_marcaje, fecha FROM marcaje_dispositivo
         WHERE dni = ? AND fecha >= ? AND fecha < DATE_ADD(?, INTERVAL 1 DAY) ORDER BY fecha",
        "sss", $dni, $dia, $dia);
    $marcajes = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

    $web = registros_del_dia($conexion, $dni, $dia, 'web');
    $web_entrada = false;
    $web_salida = false;
    foreach ($web as $r) {
        if ($r['tipo'] === 'entrada') $web_entrada = true;
        if ($r['tipo'] === 'salida') $web_salida = true;
    }

    $conexion->begin_transaction();
    try {
        consulta($conexion,
            "DELETE FROM asistencia WHERE dni = ? AND origen = 'hikvision'
             AND fecha >= ? AND fecha < DATE_ADD(?, INTERVAL 1 DAY)", "sss", $dni, $dia, $dia);

        $restantes = $marcajes;
        $entrada = null;
        if (!$web_entrada && $restantes) {
            $entrada = array_shift($restantes);
            $limite = (new DateTime($entrada['fecha']))->modify('+' . ANTIRREBOTE_MINUTOS . ' minutes');
            $restantes = array_values(array_filter($restantes, fn($m) => new DateTime($m['fecha']) > $limite));
        }
        $salida = (!$web_salida && $restantes) ? end($restantes) : null;

        $estado_salida = null;
        if ($salida) {
            $estado_salida = estado_salida($horario, new DateTime($salida['fecha']));
        }

        if ($entrada) {
            $fecha = new DateTime($entrada['fecha']);
            $estado = estado_entrada($horario, $fecha);
            if ($estado === 'ANTICIPADA') $estado = 'A_TIEMPO';
            if ($estado_salida === 'FALTA') $estado = 'FALTA'; // misma regla que el checado web
            insertar_asistencia($conexion, $dni, 'entrada', $fecha, $estado, 'hikvision', (int) $entrada['id_marcaje']);
        }
        if ($salida) {
            $estado = $estado_salida === 'TEMPRANA' ? 'SALIDA_TEMPRANA' : $estado_salida;
            insertar_asistencia($conexion, $dni, 'salida', new DateTime($salida['fecha']), $estado,
                'hikvision', (int) $salida['id_marcaje']);
        }
        $conexion->commit();
    } catch (Throwable $e) {
        $conexion->rollback();
        throw $e;
    }
}

/**
 * Procesa una lista de eventos: guarda los nuevos y recalcula los días afectados.
 * Devuelve ['recibidos' => n, 'nuevos' => n, 'ignorados' => n].
 */
function procesar_eventos(mysqli $conexion, array $eventos, array $cfg): array
{
    $stats = ['recibidos' => count($eventos), 'nuevos' => 0, 'ignorados' => 0];
    $dias = [];
    foreach ($eventos as $evento) {
        $m = is_array($evento) ? normalizar_evento($evento, $cfg) : null;
        if (!$m) {
            $stats['ignorados']++;
            continue;
        }
        if (registrar_marcaje($conexion, $m, $cfg)) {
            $stats['nuevos']++;
            $dias[$m['dni'] . '|' . $m['fecha']->format('Y-m-d')] = true;
        }
    }
    foreach (array_keys($dias) as $clave) {
        [$dni, $dia] = explode('|', $clave, 2);
        recalcular_dia($conexion, $dni, $dia);
    }
    return $stats;
}

/** Recalcula todos los días con marcajes de un empleado (p. ej. justo después de darlo de alta). */
function recalcular_empleado(mysqli $conexion, string $dni): int
{
    $res = consulta($conexion,
        "SELECT DISTINCT DATE(fecha) AS dia FROM marcaje_dispositivo WHERE dni = ?", "s", $dni);
    $dias = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    foreach ($dias as $d) {
        recalcular_dia($conexion, $dni, $d['dia']);
    }
    return count($dias);
}

/** POST JSON a una ruta ISAPI del dispositivo con autenticación digest. */
function hik_post(array $cfg, string $ruta, array $cuerpo): array
{
    if ($cfg['host'] === '') {
        throw new RuntimeException('HIK_HOST no está configurado');
    }
    $ch = curl_init('http://' . $cfg['host'] . $ruta);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($cuerpo),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
        CURLOPT_USERPWD => $cfg['usuario'] . ':' . $cfg['password'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 20,
    ]);
    $respuesta = curl_exec($ch);
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($respuesta === false) {
        throw new RuntimeException("No se pudo conectar con el dispositivo ({$cfg['host']}): $error");
    }
    if ($codigo === 401) {
        throw new RuntimeException('El dispositivo rechazó el usuario o la contraseña (HIK_USUARIO / HIK_PASSWORD)');
    }
    if ($codigo !== 200) {
        throw new RuntimeException("El dispositivo respondió HTTP $codigo: " . substr($respuesta, 0, 300));
    }
    $json = json_decode($respuesta, true);
    if (!is_array($json)) {
        throw new RuntimeException('Respuesta inesperada del dispositivo: ' . substr($respuesta, 0, 300));
    }
    return $json;
}

/**
 * Recorre una búsqueda paginada de ISAPI (30 resultados por página).
 * $raiz_cond / $raiz_resp: nombres del objeto de condición y de respuesta;
 * $lista: nombre del arreglo con los resultados dentro de la respuesta.
 */
function hik_buscar_paginado(array $cfg, string $ruta, string $raiz_cond, array $condicion,
                             string $raiz_resp, string $lista): array
{
    $busqueda = bin2hex(random_bytes(8));
    $posicion = 0;
    $resultados = [];
    do {
        $json = hik_post($cfg, $ruta, [$raiz_cond => [
            'searchID' => $busqueda,
            'searchResultPosition' => $posicion,
            'maxResults' => 30,
        ] + $condicion]);
        $resp = $json[$raiz_resp] ?? null;
        if (!is_array($resp)) {
            throw new RuntimeException('Respuesta inesperada del dispositivo: ' . substr(json_encode($json), 0, 300));
        }
        $pagina = $resp[$lista] ?? [];
        $resultados = array_merge($resultados, $pagina);
        $posicion += count($pagina);
        $estado = $resp['responseStatusStrg'] ?? 'OK';   // MORE | OK | NO MATCH
    } while ($estado === 'MORE' && count($pagina) > 0 && $posicion < 100000);
    return $resultados;
}

/** Eventos de control de acceso entre dos fechas (POST /ISAPI/AccessControl/AcsEvent). */
function hik_buscar_eventos(array $cfg, DateTimeInterface $desde, DateTimeInterface $hasta): array
{
    return hik_buscar_paginado($cfg, '/ISAPI/AccessControl/AcsEvent?format=json', 'AcsEventCond', [
        'major' => MAJOR_CONTROL_ACCESO,
        'minor' => 0, // todos; el filtro por subtipo se hace en normalizar_evento()
        'startTime' => $desde->format('c'),
        'endTime' => $hasta->format('c'),
    ], 'AcsEvent', 'InfoList');
}

/**
 * Usuarios registrados en el dispositivo (POST /ISAPI/AccessControl/UserInfo/Search).
 * Devuelve [['employeeNo' => '...', 'name' => '...'], ...]
 */
function hik_buscar_usuarios(array $cfg): array
{
    $usuarios = hik_buscar_paginado($cfg, '/ISAPI/AccessControl/UserInfo/Search?format=json',
        'UserInfoSearchCond', [], 'UserInfoSearch', 'UserInfo');
    $limpios = [];
    foreach ($usuarios as $u) {
        $no = trim((string) ($u['employeeNo'] ?? ''));
        if ($no !== '') {
            $limpios[] = ['employeeNo' => $no, 'name' => trim((string) ($u['name'] ?? ''))];
        }
    }
    return $limpios;
}

/**
 * Divide el nombre del dispositivo (un solo campo) en nombre y apellidos:
 * 4+ palabras o 3 palabras -> las 2 últimas son apellidos; 2 palabras -> 1 y 1.
 * Es solo una propuesta: la vista previa permite corregirlo antes de importar.
 */
function dividir_nombre(string $completo): array
{
    $partes = preg_split('/\s+/', trim($completo), -1, PREG_SPLIT_NO_EMPTY);
    $n = count($partes);
    if ($n >= 3) return [implode(' ', array_slice($partes, 0, $n - 2)), implode(' ', array_slice($partes, -2))];
    if ($n === 2) return [$partes[0], $partes[1]];
    return [$partes[0] ?? '', ''];
}
