<?php
require('../../libs/fpdf/fpdf.php');
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

class ReportePDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'Reporte de Asistencias', 0, 1, 'C');
        
        if (isset($GLOBALS['fecha_inicio']) && isset($GLOBALS['fecha_fin'])) {
            $this->SetFont('Arial', 'I', 10);
            $this->Cell(0, 10, 'Periodo: ' . $GLOBALS['fecha_inicio'] . ' - ' . $GLOBALS['fecha_fin'], 0, 1, 'C');
        }
        
        $this->Ln(10);
        
        // Encabezados
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(15, 10, 'ID', 1, 0, 'C');
        $this->Cell(60, 10, 'Empleado', 1, 0, 'C');
        $this->Cell(30, 10, 'NoEmpleado', 1, 0, 'C');
        $this->Cell(30, 10, 'Cargo', 1, 0, 'C');
        $this->Cell(25, 10, 'Tipo', 1, 0, 'C');
        $this->Cell(35, 10, 'Fecha/Hora', 1, 1, 'C');
    }
}

$pdf = new ReportePDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

$result = $conexion->query($sql);
while($row = $result->fetch_object()) {
    $pdf->Cell(15, 10, $row->id_asistencia, 1, 0, 'C');
    $pdf->Cell(60, 10, utf8_decode($row->nom_empleado . " " . $row->apellido), 1, 0, 'L');
    $pdf->Cell(30, 10, $row->dni, 1, 0, 'C');
    $pdf->Cell(30, 10, utf8_decode($row->nom_cargo), 1, 0, 'L');
    $pdf->Cell(25, 10, ucfirst($row->tipo), 1, 0, 'C');
    $pdf->Cell(35, 10, $row->fecha, 1, 1, 'C');
}

$pdf->Output();
?>