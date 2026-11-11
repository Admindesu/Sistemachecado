<?php
require_once __DIR__ . '/../../controlador/auth.php';
requerir_admin();

// Incluye el archivo de conexión a la base de datos
include "../../modelo/conexion.php";


// Obtiene los parámetros de filtro desde el formulario (POST)
$fecha_inicio = !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : date('Y-01-01');
$fecha_fin = !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : date('Y-12-31');
$empleado_id = $_POST['empleado'] ?? '';

// Construye el WHERE para el filtro de fechas y empleado
// Solo se aceptan fechas válidas (AAAA-MM-DD); si no, se usa el año en curso
$fecha_valida = function ($f) { $d = DateTime::createFromFormat('Y-m-d', $f); return $d && $d->format('Y-m-d') === $f; };
if (!$fecha_valida($fecha_inicio)) $fecha_inicio = date('Y-01-01');
if (!$fecha_valida($fecha_fin)) $fecha_fin = date('Y-12-31');

// Filtro por rango de fechas (sin DATE() para que MySQL pueda usar índices) y por empleado
$where = ["asistencia.fecha >= ?", "asistencia.fecha < DATE_ADD(?, INTERVAL 1 DAY)"];
$tipos = "ss";
$params = [$fecha_inicio, $fecha_fin];
if ($empleado_id) {
    $where[] = "asistencia.dni = ?";
    $tipos .= "s";
    $params[] = $empleado_id;
}
$where_clause = "WHERE " . implode(" AND ", $where);

// Construye la consulta SQL para obtener los datos de asistencia
$sql = "SELECT 
    asistencia.dni,
    asistencia.tipo,
    asistencia.fecha,
    asistencia.estado,
    CONCAT(empleado.nombre, ' ', empleado.apellido) as nombre_completo,
    cargo.nombre as nom_cargo
    FROM asistencia
    INNER JOIN empleado ON asistencia.dni = empleado.dni
    INNER JOIN cargo ON empleado.cargo = cargo.id_cargo
    LEFT JOIN horarios ON empleado.id_horario = horarios.id_horario
    $where_clause
    ORDER BY asistencia.fecha DESC";

// Establece las cabeceras para exportar el contenido como archivo Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Reporte_Asistencias.xls"');
header('Cache-Control: max-age=0');

// Función para obtener el color según el estado
function getEstadoColor($estado) {
    switch ($estado) {
        case 'A_TIEMPO':
            return '#90EE90'; // Verde claro
        case 'RETARDO':
            return '#FFD700'; // Amarillo
        case 'FALTA':
            return '#FFB6C1'; // Rojo claro
        default:
            return '#FFFFFF'; // Blanco
    }
}

// Función para formatear la hora en formato 12 horas
function formatearHora($hora) {
    return date('h:i A', strtotime($hora));
}

// Prepara la información de los filtros aplicados para mostrarla en el reporte
$filtros = [];
if ($empleado_id) {
    // Consulta el nombre completo del empleado filtrado
    $emp = consulta($conexion, "SELECT CONCAT(nombre, ' ', apellido) as nombre FROM empleado WHERE dni = ?", "s", $empleado_id)->fetch_object();
    $filtros[] = "Empleado: " . ($emp ? htmlspecialchars($emp->nombre) : 'N/A');
}
if ($fecha_inicio) $filtros[] = "Desde: $fecha_inicio";
if ($fecha_fin) $filtros[] = "Hasta: $fecha_fin";

// Imprime la cabecera de la tabla y los filtros aplicados
echo "<table border='1'>
    <thead>
        <tr>
            <th colspan='6' style='font-size:16px; text-align:center; background-color:#1e88e5; color:#fff;'>
                Reporte de Asistencias
            </th>
        </tr>";

// Si hay filtros, los muestra en una fila aparte
if (!empty($filtros)) {
    echo "<tr>
        <th colspan='6' style='text-align:left; background-color:#f5f5f5;'>
            Filtros: " . implode(" | ", $filtros) . "
        </th>
    </tr>";
}

// Imprime los encabezados de las columnas
echo "<tr style='background-color:#f5f5f5; font-weight:bold;'>
    <th style='width:100px;'>DNI</th>
    <th style='width:200px;'>Empleado</th>
    <th style='width:150px;'>Cargo</th>
    <th style='width:120px;'>Fecha y Hora</th>
    <th style='width:80px;'>Tipo</th>
    <th style='width:100px;'>Estado</th>
</tr>
</thead>
<tbody>";

// Ejecuta la consulta y muestra los resultados
$result = consulta($conexion, $sql, $tipos, ...$params);

while ($row = $result->fetch_assoc()) {
    $estado_color = getEstadoColor($row['estado']);
    
    echo "<tr>
        <td style='text-align:center;'>" . htmlspecialchars($row['dni']) . "</td>
        <td>" . htmlspecialchars($row['nombre_completo']) . "</td>
        <td>" . htmlspecialchars($row['nom_cargo']) . "</td>
        <td style='text-align:center;'>" . date('d/m/Y h:i A', strtotime($row['fecha'])) . "</td>
        <td style='text-align:center;'>" . ucfirst($row['tipo']) . "</td>
        <td style='background-color:{$estado_color}; text-align:center;'>" . 
            ($row['estado'] ? str_replace('_', ' ', strtoupper($row['estado'])) : 'N/A') . 
        "</td>
    </tr>";
}

echo "</tbody></table>";
?>