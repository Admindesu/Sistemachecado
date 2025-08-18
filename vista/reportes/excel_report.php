<?php
include "../../modelo/conexion.php";

// Get filter parameters
$fecha_inicio = !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : date('Y-01-01');
$fecha_fin = !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : date('Y-12-31');
$empleado_id = $_POST['empleado'] ?? '';

// Build WHERE clause
$where = [];
$where[] = "DATE(asistencia.fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin'";
if ($empleado_id) {
    $where[] = "asistencia.dni = '$empleado_id'";
}

$where_clause = "WHERE " . implode(" AND ", $where);

// SQL query
$sql = "SELECT 
    asistencia.id_asistencia,
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

// Excel headers
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Reporte_Asistencias.xls"');
header('Cache-Control: max-age=0');

// Agregar información de filtros aplicados
$filtros = [];
if ($empleado_id) {
    $emp = $conexion->query("SELECT CONCAT(nombre, ' ', apellido) as nombre FROM empleado WHERE dni = '$empleado_id'")->fetch_object();
    $filtros[] = "Empleado: " . ($emp ? $emp->nombre : 'N/A');
}
if ($fecha_inicio) $filtros[] = "Desde: $fecha_inicio";
if ($fecha_fin) $filtros[] = "Hasta: $fecha_fin";

echo "<table border='1'>
    <thead>
        <tr>
            <th colspan='6' style='font-size:16px; text-align:center; background-color:#1e88e5; color:#fff;'>
                Reporte de Asistencias
            </th>
        </tr>";

if (!empty($filtros)) {
    echo "<tr>
        <th colspan='6' style='text-align:left; background-color:#f5f5f5;'>
            Filtros: " . implode(" | ", $filtros) . "
        </th>
    </tr>";
}

echo "<tr>
        <th>ID</th>
        <th>Empleado</th>
        <th>NoEmpleado</th>
        <th>Cargo</th>
        <th>Tipo</th>
        <th>Fecha/Hora</th>
    </tr>
</thead>
<tbody>";

$result = $conexion->query($sql);
while($row = $result->fetch_object()) {
    echo "<tr>
        <td>{$row->id_asistencia}</td>
        <td>{$row->nom_empleado} {$row->apellido}</td>
        <td>{$row->dni}</td>
        <td>{$row->nom_cargo}</td>
        <td>" . ucfirst($row->tipo) . "</td>
        <td>{$row->fecha}</td>
    </tr>";
}

echo "</tbody></table>";
?>