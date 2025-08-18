<?php
use setasign\Fpdi\Fpdi;

// Requiere las librerías necesarias para generar PDFs y manejar plantillas PDF.
require('../../libs/fpdf/fpdf.php');
require('../../libs/fpdi/src/autoload.php'); // Ajusta la ruta si es necesario
include "../../modelo/conexion.php";

// Obtiene los parámetros de filtro enviados por POST, con valores por defecto si no existen.
$fecha_inicio = !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : date('Y-01-01');
$fecha_fin = !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : date('Y-12-31');
$empleado_id = $_POST['empleado'] ?? '';

// Construye la cláusula WHERE para el filtro de fechas y empleado.
$where = [];
$where[] = "DATE(asistencia.fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin'";
if ($empleado_id) {
    $where[] = "asistencia.dni = '$empleado_id'";
}
$where_clause = "WHERE " . implode(" AND ", $where);

// Consulta SQL para obtener los datos de asistencia junto con información del empleado y su cargo.
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

// Clase personalizada que extiende FPDI para el reporte PDF.
// Se sobreescribe el método Header para no mostrar encabezado, ya que se usa una plantilla.
class ReportePDF extends Fpdi {
    function Header() {
        // No header, as template is used
    }
}

// Instancia la clase ReportePDF y agrega una nueva página.
$pdf = new ReportePDF();
$pdf->AddPage();

// Importa la plantilla PDF y la usa como fondo de la página.
$pdf->setSourceFile('plantilla.pdf');
$tplIdx = $pdf->importPage(1);
$pdf->useTemplate($tplIdx);

// Establece la posición inicial para la tabla de datos.
$pdf->SetXY(10, 50);

// Agrega el título del reporte.
$pdf->SetFont('Arial', 'B', 15);
$pdf->Cell(0, 10, 'Reporte de Asistencias', 0, 1, 'C');

// Agrega el periodo del reporte.
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, 'Periodo: ' . $fecha_inicio . ' - ' . $fecha_fin, 0, 1, 'C');
$pdf->Ln(5);

// Encabezados de la tabla.
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(60, 10, 'Empleado', 1, 0, 'C');
$pdf->Cell(30, 10, 'NoEmpleado', 1, 0, 'C');
$pdf->Cell(30, 10, 'Cargo', 1, 0, 'C');
$pdf->Cell(25, 10, 'Tipo', 1, 0, 'C');
$pdf->Cell(35, 10, 'Fecha/Hora', 1, 1, 'C');

// Fuente para los datos de la tabla.
$pdf->SetFont('Arial', '', 10);

// Ejecuta la consulta y recorre los resultados para agregarlos al PDF.
$result = $conexion->query($sql);
while($row = $result->fetch_object()) {
    $pdf->Cell(60, 10, utf8_decode($row->nom_empleado . " " . $row->apellido), 1, 0, 'L');
    $pdf->Cell(30, 10, $row->dni, 1, 0, 'C');
    $pdf->Cell(30, 10, utf8_decode($row->nom_cargo), 1, 0, 'L');
    $pdf->Cell(25, 10, ucfirst($row->tipo), 1, 0, 'C');
    $pdf->Cell(35, 10, $row->fecha, 1, 1, 'C');
}

// Genera y muestra el PDF al usuario.
$pdf->Output();
?>