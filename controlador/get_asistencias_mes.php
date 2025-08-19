<?php
include "../modelo/conexion.php";

$sql = "SELECT 
    DATE_FORMAT(fecha, '%Y-%m') as mes,
    COUNT(*) as cantidad
FROM asistencia
GROUP BY DATE_FORMAT(fecha, '%Y-%m')
ORDER BY mes DESC
LIMIT 12";

$result = $conexion->query($sql);
$data = [
    'meses' => [],
    'cantidades' => []
];

while ($row = $result->fetch_object()) {
    $data['meses'][] = date('M Y', strtotime($row->mes));
    $data['cantidades'][] = (int)$row->cantidad;
}

header('Content-Type: application/json');
echo json_encode($data);