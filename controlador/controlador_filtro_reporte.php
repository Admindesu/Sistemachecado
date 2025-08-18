<?php
docs\Sistemachecado\vista\reportes\reporte_asistencia.php
<?php
require('../../libs/fpdf/fpdf.php');
include "../../modelo/conexion.php";

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'Reporte de Asistencias', 0, 1, 'C');
        
        // Subtítulo con el tipo de reporte
        $this->SetFont('Arial', 'I', 10);
        $filtro = isset($_POST['tipo_reporte']) ? ucfirst($_POST['tipo_reporte']) : 'General';
        $this->Cell(0, 10, 'Filtro: ' . $filtro, 0, 1, 'C');
        $this->Ln(10);
        
        // Encabezados de la tabla
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(15, 10, 'ID', 1, 0, 'C');
        $this->Cell(60, 10, 'Empleado', 1, 0, 'C');
        $this->Cell(30, 10, 'NoEmpleado', 1, 0, 'C');
        $this->Cell(30, 10, 'Cargo', 1, 0, 'C');
        $this->Cell(25, 10, 'Tipo', 1, 0, 'C');
        $this->Cell(35, 10, 'Fecha/Hora', 1, 1, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// Construir la consulta según el filtro
$where = "";
switch($_POST['tipo_reporte']) {
    case 'cargo':
        $cargo = $_POST['cargo'];
        $where = "WHERE empleado.cargo = '$cargo'";
        break;
    case 'dni':
        $dni = $_POST['dni'];
        $where = "WHERE asistencia.dni = '$dni'";
        break;
    case 'usuario':
        $usuario = $_POST['usuario'];
        $where = "WHERE asistencia.dni = '$usuario'";
        break;
    case 'fecha':
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_fin = $_POST['fecha_fin'];
        $where = "WHERE DATE(asistencia.fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        break;
}

$sql = $conexion->query("SELECT 
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
    $where
    ORDER BY asistencia.fecha DESC");

while($row = $sql->fetch_object()) {
    $pdf->Cell(15, 10, $row->id_asistencia, 1, 0, 'C');
    $pdf->Cell(60, 10, utf8_decode($row->nom_empleado . " " . $row->apellido), 1, 0, 'L');
    $pdf->Cell(30, 10, $row->dni, 1, 0, 'C');
    $pdf->Cell(30, 10, utf8_decode($row->nom_cargo), 1, 0, 'L');
    $pdf->Cell(25, 10, ucfirst($row->tipo), 1, 0, 'C');
    $pdf->Cell(35, 10, $row->fecha, 1, 1, 'C');
}

$pdf->Output();
?>