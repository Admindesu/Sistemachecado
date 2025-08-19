<?php
include "../modelo/conexion.php";

$sql = "SELECT 
    d.nombre as direccion,
    COUNT(a.id_asistencia) as cantidad
FROM direccion d
LEFT JOIN empleado e ON d.id_direccion = e.direccion
LEFT JOIN asistencia a ON e.dni = a.dni
GROUP BY d.id_direccion, d.nombre
ORDER BY cantidad DESC";

$result = $conexion->query($sql);
$data = [
    'direcciones' => [],
    'cantidades' => []
];

while ($row = $result->fetch_object()) {
    $data['direcciones'][] = $row->direccion;
    $data['cantidades'][] = (int)$row->cantidad;
}

header('Content-Type: application/json');
echo json_encode($data);