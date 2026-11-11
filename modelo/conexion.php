<?php
// Configuración por variables de entorno (Docker). Los valores por defecto
// corresponden a XAMPP, así que sin variables definidas funciona igual que antes.
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_port = (int) (getenv('DB_PORT') ?: 3306);
$db_name = getenv('DB_NAME') ?: 'sis-asistencia';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';

// Desde PHP 8.1 mysqli lanza excepciones por defecto. El código actual revisa
// los valores de retorno (if ($sql == true)), así que se mantiene el modo anterior.
mysqli_report(MYSQLI_REPORT_OFF);

$conexion = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
if ($conexion->connect_errno) {
    error_log('Error de conexión a la base de datos: ' . $conexion->connect_error);
    http_response_code(500);
    exit('No se pudo conectar a la base de datos.');
}
$conexion->set_charset('utf8mb4');

require_once __DIR__ . '/db.php';

// Una sola zona horaria para toda la aplicación.
date_default_timezone_set(getenv('APP_TZ') ?: 'America/Cancun');
