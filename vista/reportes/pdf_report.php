<?php
// Evitar que se muestren warnings que puedan afectar la generación del PDF
error_reporting(E_ERROR | E_PARSE);

// Requiere las librerías necesarias para generar PDFs y manejar plantillas PDF.
require('../../libs/fpdf/fpdf.php');
require('../../libs/fpdi/src/autoload.php');
use setasign\Fpdi\Fpdi;
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
    asistencia.estado,
    CONCAT(empleado.nombre, ' ', empleado.apellido) as nombre_completo,
    cargo.nombre as nom_cargo
    FROM asistencia
    INNER JOIN empleado ON asistencia.dni = empleado.dni
    INNER JOIN cargo ON empleado.cargo = cargo.id_cargo
    $where_clause
    ORDER BY asistencia.fecha DESC";

// Clase personalizada que extiende FPDI para el reporte PDF.
// Se sobreescribe el método Header para no mostrar encabezado, ya que se usa una plantilla.
class PDF extends Fpdi {
    function Header() {
        // No header, as template is used
    }

    // Formatea una hora en formato 12 horas
    function formatearHora($hora) {
        if (!$hora) return 'N/A';
        return date('h:i A', strtotime($hora));
    }
    
    // Obtiene el color RGB según el estado
    function getEstadoColor($estado) {
        switch (strtoupper($estado)) {
            case 'A_TIEMPO':
                return array(0, 200, 0); // Verde
            case 'RETARDO':
                return array(255, 191, 0); // Amarillo
            case 'FALTA':
                return array(255, 0, 0); // Rojo
            default:
                return array(0, 0, 0); // Negro
        }
    }
    
    // Formatea el estado para mostrar
    function formatearEstado($estado) {
        switch (strtoupper($estado)) {
            case 'A_TIEMPO':
                return 'A TIEMPO';
            case 'RETARDO':
                return 'RETARDO';
            case 'FALTA':
                return 'FALTA';
            default:
                return $estado ? str_replace('_', ' ', $estado) : 'N/A';
        }
    }
}

// Crea el PDF
$pdf = new PDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10);

// Título del reporte
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Reporte de Asistencias', 0, 1, 'C');
$pdf->Ln(5);

// Periodo del reporte
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 10, 'Periodo: ' . date('d/m/Y', strtotime($fecha_inicio)) . ' - ' . date('d/m/Y', strtotime($fecha_fin)), 0, 1, 'C');

// Si hay filtro por empleado
if ($empleado_id) {
    $emp = $conexion->query("SELECT CONCAT(nombre, ' ', apellido) as nombre FROM empleado WHERE dni = '$empleado_id'")->fetch_object();
    if ($emp) {
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 10, 'Empleado: ' . utf8_decode($emp->nombre), 0, 1, 'L');
    }
}
$pdf->Ln(5);

// Encabezados de la tabla
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(230, 230, 230);

// Configuración de la tabla
$pdf->SetFillColor(230, 230, 230);
$pdf->SetFont('Arial', 'B', 10);

// Anchura de las columnas (total 190mm para A4)
$w = array(25, 55, 35, 35, 20, 20);
$headers = array('DNI', 'Empleado', 'Cargo', 'Fecha/Hora', 'Tipo', 'Estado');

// Cabecera de la tabla
foreach($headers as $i => $header) {
    $pdf->Cell($w[$i], 8, utf8_decode($header), 1, 0, 'C', true);
}
$pdf->Ln();

// Contenido de la tabla
$pdf->SetFont('Arial', '', 9);
$result = $conexion->query($sql);

while ($row = $result->fetch_assoc()) {
    $pdf->Cell($w[0], 7, $row['dni'], 1, 0, 'C');
    $pdf->Cell($w[1], 7, utf8_decode($row['nombre_completo']), 1, 0, 'L');
    $pdf->Cell($w[2], 7, utf8_decode($row['nom_cargo']), 1, 0, 'L');
    $pdf->Cell($w[3], 7, date('d/m/Y H:i', strtotime($row['fecha'])), 1, 0, 'C');
    $pdf->Cell($w[4], 7, ucfirst($row['tipo']), 1, 0, 'C');
    
    // Color según el estado
    $estado_color = $pdf->getEstadoColor($row['estado']);
    $pdf->SetTextColor($estado_color[0], $estado_color[1], $estado_color[2]);
    $pdf->Cell($w[5], 7, $pdf->formatearEstado($row['estado']), 1, 0, 'C');
    $pdf->SetTextColor(0, 0, 0);
    
    $pdf->Ln();
}



// Agregar leyenda de estados al final
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(0, 6, 'Leyenda de Estados:', 0, 1, 'L');
$pdf->SetFont('Arial', '', 9);

// A Tiempo
$pdf->SetTextColor(0, 200, 0);
$pdf->Cell(30, 6, 'A TIEMPO', 0, 0, 'L');
// Retardo
$pdf->SetTextColor(255, 191, 0);
$pdf->Cell(30, 6, 'RETARDO', 0, 0, 'L');
// Falta
$pdf->SetTextColor(255, 0, 0);
$pdf->Cell(30, 6, 'FALTA', 0, 1, 'L');

// Restaurar color del texto
$pdf->SetTextColor(0, 0, 0);

// Genera y muestra el PDF al usuario.
$pdf->Output();
?>