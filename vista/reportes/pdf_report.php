<?php
require_once __DIR__ . '/../../controlador/auth.php';
requerir_sesion();

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
// Un empleado normal solo puede ver su propio reporte; el admin puede elegir cualquiera.
$empleado_id = es_admin() ? ($_POST['empleado'] ?? '') : $_SESSION['dni'];

// Construye la cláusula WHERE para el filtro de fechas y empleado.
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
class PDF extends Fpdi {
    const IMAGEN_ENCABEZADO = __DIR__ . '/../../libs/header_pdf.png';
    const ALTO_MAX_ENCABEZADO = 35;      // mm; una imagen más "alta" se reduce y se centra
    public $columnas = [];               // [ancho, título] de la tabla, se repite en cada página

    /**
     * Tamaño en mm de la imagen del encabezado, respetando su proporción real:
     * a todo lo ancho de la página, o centrada si así pasaría de ALTO_MAX_ENCABEZADO.
     * Devuelve [x, ancho, alto].
     */
    function medidasEncabezado(): array {
        [$px_ancho, $px_alto] = getimagesize(self::IMAGEN_ENCABEZADO);
        $ancho = $this->GetPageWidth();
        $alto = $ancho * $px_alto / $px_ancho;
        if ($alto > self::ALTO_MAX_ENCABEZADO) {
            $alto = self::ALTO_MAX_ENCABEZADO;
            $ancho = $alto * $px_ancho / $px_alto;
        }
        return [($this->GetPageWidth() - $ancho) / 2, $ancho, $alto];
    }

    // Encabezado en todas las páginas: franja del color de la imagen a todo lo ancho
    // (rellena los lados si la imagen no alcanza) y la imagen header_pdf.png encima.
    function Header() {
        [$x, $ancho, $alto] = $this->medidasEncabezado();
        $this->SetFillColor(129, 0, 16);
        $this->Rect(0, 0, $this->GetPageWidth(), $alto, 'F');
        $this->Image(self::IMAGEN_ENCABEZADO, $x, 0, $ancho, $alto);
        $this->SetY($alto + 6);
        // A partir de la segunda página se repite la cabecera de la tabla
        if ($this->PageNo() > 1 && $this->columnas) {
            $this->cabeceraTabla();
        }
    }

    function cabeceraTabla() {
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(230, 230, 230);
        $this->SetTextColor(0, 0, 0);
        foreach ($this->columnas as [$ancho, $titulo]) {
            $this->Cell($ancho, 8, utf8_decode($titulo), 1, 0, 'C', true);
        }
        $this->Ln();
        $this->SetFont('Arial', '', 9);
    }

    function Footer() {
        $this->SetY(-12);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(0, 6, utf8_decode('Página ') . $this->PageNo() . ' de {nb}', 0, 0, 'R');
        $this->SetTextColor(0, 0, 0);
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
// Horizontal (A4: 297 x 210 mm); el título va en la imagen del encabezado
$pdf = new PDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->SetMargins(10, $pdf->medidasEncabezado()[2] + 6, 10);
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();

// Periodo del reporte
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 8, 'Periodo: ' . date('d/m/Y', strtotime($fecha_inicio)) . ' - ' . date('d/m/Y', strtotime($fecha_fin)), 0, 1, 'C');

// Si hay filtro por empleado
if ($empleado_id) {
    $emp = consulta($conexion, "SELECT CONCAT(nombre, ' ', apellido) as nombre FROM empleado WHERE dni = ?", "s", $empleado_id)->fetch_object();
    if ($emp) {
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 8, 'Empleado: ' . utf8_decode($emp->nombre), 0, 1, 'L');
    }
}
$pdf->Ln(3);

// Columnas de la tabla (total 277 mm: ancho horizontal menos márgenes)
$pdf->columnas = [[25, 'DNI'], [85, 'Empleado'], [70, 'Cargo'], [40, 'Fecha/Hora'], [25, 'Tipo'], [32, 'Estado']];
$w = array_column($pdf->columnas, 0);
$pdf->cabeceraTabla();

// Contenido de la tabla
$result = consulta($conexion, $sql, $tipos, ...$params);

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