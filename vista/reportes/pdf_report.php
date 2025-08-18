<?php
use setasign\Fpdi\Fpdi;

require('../../libs/fpdf/fpdf.php');
require('../../libs/fpdi/src/autoload.php'); // Ajusta la ruta si es necesario
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

class ReportePDF extends Fpdi {
    function Header() {
        // No header, as template is used
    }
}

$pdf = new ReportePDF();
$pdf->AddPage();

// Import template
$pdf->setSourceFile('plantilla.pdf');
$tplIdx = $pdf->importPage(1);
$pdf->useTemplate($tplIdx);

// Set position for table (adjust X/Y as needed)
$pdf->SetXY(10, 50);

$pdf->SetFont('Arial', 'B', 15);
$pdf->Cell(0, 10, 'Reporte de Asistencias', 0, 1, 'C');

$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, 'Periodo: ' . $fecha_inicio . ' - ' . $fecha_fin, 0, 1, 'C');
$pdf->Ln(5);

// Encabezados
$pdf->SetFont('Arial', 'B', 11);

$pdf->Cell(60, 10, 'Empleado', 1, 0, 'C');
$pdf->Cell(30, 10, 'NoEmpleado', 1, 0, 'C');
$pdf->Cell(30, 10, 'Cargo', 1, 0, 'C');
$pdf->Cell(25, 10, 'Tipo', 1, 0, 'C');
$pdf->Cell(35, 10, 'Fecha/Hora', 1, 1, 'C');

$pdf->SetFont('Arial', '', 10);

$result = $conexion->query($sql);
while($row = $result->fetch_object()) {
   
    $pdf->Cell(60, 10, utf8_decode($row->nom_empleado . " " . $row->apellido), 1, 0, 'L');
    $pdf->Cell(30, 10, $row->dni, 1, 0, 'C');
    $pdf->Cell(30, 10, utf8_decode($row->nom_cargo), 1, 0, 'L');
    $pdf->Cell(25, 10, ucfirst($row->tipo), 1, 0, 'C');
    $pdf->Cell(35, 10, $row->fecha, 1, 1, 'C');
}

$pdf->Output();
?>