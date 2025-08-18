<?php
// Incluye el archivo de conexión a la base de datos
include "../../modelo/conexion.php";

// Obtiene los parámetros de filtro desde el formulario (POST)
// Si no se envía fecha_inicio, se usa el primer día del año actual
// Si no se envía fecha_fin, se usa el último día del año actual
// Si no se envía empleado, se deja vacío
$fecha_inicio = !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : date('Y-01-01');
$fecha_fin = !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : date('Y-12-31');
$empleado_id = $_POST['empleado'] ?? '';

// Construye el WHERE para el filtro de fechas y empleado
$where = [];
$where[] = "DATE(asistencia.fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin'";
if ($empleado_id) {
    $where[] = "asistencia.dni = '$empleado_id'";
}

// Une los filtros en una sola cláusula WHERE
$where_clause = "WHERE " . implode(" AND ", $where);

// Construye la consulta SQL para obtener los datos de asistencia
$sql = "SELECT 
    asistencia.dni,
    asistencia.tipo,
    asistencia.fecha,
    empleado.nombre as nom_empleado,
    empleado.apellido,
    empleado.dni,
    empleado.cargo,
    cargo.nombre as nom_cargo
    FROM asistencia
    INNER JOIN empleado ON asistencia.dni = empleado.dni
    INNER JOIN cargo ON empleado.cargo = cargo.id_cargo
    $where_clause
    ORDER BY asistencia.fecha DESC";

// Establece las cabeceras para exportar el contenido como archivo Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Reporte_Asistencias.xls"');
header('Cache-Control: max-age=0');

// Prepara la información de los filtros aplicados para mostrarla en el reporte
$filtros = [];
if ($empleado_id) {
    // Consulta el nombre completo del empleado filtrado
    $emp = $conexion->query("SELECT CONCAT(nombre, ' ', apellido) as nombre FROM empleado WHERE dni = '$empleado_id'")->fetch_object();
    $filtros[] = "Empleado: " . ($emp ? $emp->nombre : 'N/A');
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
echo "<tr>
        <th>Empleado</th>
        <th>NoEmpleado</th>
        <th>Cargo</th>
        <th>Tipo</th>
        <th>Fecha/Hora</th>
    </tr>
</thead>
<tbody>";

// Ejecuta la consulta y recorre los resultados para imprimir cada fila
$result = $conexion->query($sql);
while($row = $result->fetch_object()) {
    echo "<tr>
        <td>{$row->nom_empleado} {$row->apellido}</td>
        <td>{$row->dni}</td>
        <td>{$row->nom_cargo}</td>
        <td>" . ucfirst($row->tipo) . "</td>
        <td>{$row->fecha}</td>
    </tr>";
}

// Cierra la tabla HTML
echo "</tbody></table>";
?>