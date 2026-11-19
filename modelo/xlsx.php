<?php
/**
 * Generador mínimo de archivos Excel (.xlsx) sin dependencias: arma el ZIP a mano con
 * zlib (no necesita la extensión zip de PHP, que XAMPP trae desactivada).
 *
 * Uso:
 *   $x = new Xlsx('Asistencias');
 *   $x->anchos([12, 40, 20]);
 *   $x->fila(['Título'], Xlsx::TITULO);
 *   $x->fila(['DNI', 'Nombre', 'Fecha'], Xlsx::ENCABEZADO);
 *   $x->fila(['123', 'Ana', new DateTime()], [Xlsx::NORMAL, Xlsx::NORMAL, Xlsx::FECHA_HORA]);
 *   $x->filtrar_desde(2);  // fila de encabezados: filtros y paneles fijos
 *   $x->descargar('reporte.xlsx');
 *
 * Valores: string (texto), int/float (número), DateTimeInterface (fecha de Excel), null (vacía).
 */
class Xlsx
{
    // Estilos (índices de cellXfs en estilos())
    const NORMAL = 0;
    const TITULO = 1;
    const ENCABEZADO = 2;
    const FECHA = 3;
    const HORA = 4;
    const FECHA_HORA = 5;
    const VERDE = 6;
    const AMARILLO = 7;
    const ROJO = 8;
    const SUBTITULO = 9;

    private $hoja;
    private $filas = [];
    private $anchos = [];
    private $combinadas = [];
    private $fila_filtro = null;
    private $max_columnas = 0;

    public function __construct(string $hoja = 'Hoja1')
    {
        // Excel limita el nombre de la hoja a 31 caracteres y prohíbe : \ / ? * [ ]
        $this->hoja = mb_substr(preg_replace('/[:\\\\\/?*\[\]]/', '', $hoja), 0, 31) ?: 'Hoja1';
    }

    /** Anchos de columna en caracteres. */
    public function anchos(array $anchos): void
    {
        $this->anchos = $anchos;
    }

    /** Agrega una fila; $estilo es uno para toda la fila o uno por celda. */
    public function fila(array $valores, $estilo = self::NORMAL): void
    {
        $estilos = is_array($estilo) ? $estilo : array_fill(0, count($valores), $estilo);
        $this->filas[] = [$valores, $estilos];
        $this->max_columnas = max($this->max_columnas, count($valores));
    }

    /** Combina las celdas de la última fila agregada, de la columna 1 a la $hasta. */
    public function combinar_ultima(int $hasta): void
    {
        $n = count($this->filas);
        $this->combinadas[] = 'A' . $n . ':' . self::columna($hasta) . $n;
    }

    /** Fila (1 = primera) de encabezados: se le ponen filtros y queda fija al desplazarse. */
    public function filtrar_desde(int $fila): void
    {
        $this->fila_filtro = $fila;
    }

    /** Envía el archivo al navegador como descarga. */
    public function descargar(string $nombre): void
    {
        $contenido = $this->generar();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . str_replace('"', '', $nombre) . '"');
        header('Content-Length: ' . strlen($contenido));
        header('Cache-Control: max-age=0');
        echo $contenido;
    }

    /** Contenido binario del .xlsx. */
    public function generar(): string
    {
        $esc = fn($s) => htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $archivos = [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                . '<Default Extension="xml" ContentType="application/xml"/>'
                . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
                . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
                . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
                . '</Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
                . '</Relationships>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
                . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
                . '</Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                . '<sheets><sheet name="' . $esc($this->hoja) . '" sheetId="1" r:id="rId1"/></sheets>'
                . ($this->fila_filtro ? '<definedNames><definedName name="_xlnm._FilterDatabase" localSheetId="0" hidden="1">'
                    . "'" . $esc(str_replace("'", "''", $this->hoja)) . "'!" . $this->rango_filtro(true) . '</definedName></definedNames>' : '')
                . '</workbook>',
            'xl/styles.xml' => self::estilos(),
            'xl/worksheets/sheet1.xml' => $this->hoja_xml($esc),
        ];
        return self::zip($archivos);
    }

    private function hoja_xml(callable $esc): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        if ($this->fila_filtro) {
            $xml .= '<sheetViews><sheetView workbookViewId="0"><pane ySplit="' . $this->fila_filtro
                . '" topLeftCell="A' . ($this->fila_filtro + 1) . '" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>';
        }
        if ($this->anchos) {
            $xml .= '<cols>';
            foreach ($this->anchos as $i => $ancho) {
                $xml .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . (float) $ancho . '" customWidth="1"/>';
            }
            $xml .= '</cols>';
        }
        $xml .= '<sheetData>';
        foreach ($this->filas as $n => [$valores, $estilos]) {
            $r = $n + 1;
            $alto = ($estilos[0] ?? 0) === self::TITULO ? ' ht="24" customHeight="1"' : '';
            $xml .= '<row r="' . $r . '"' . $alto . '>';
            foreach ($valores as $c => $valor) {
                $ref = self::columna($c + 1) . $r;
                $s = (int) ($estilos[$c] ?? 0);
                if ($valor === null || $valor === '') {
                    $xml .= '<c r="' . $ref . '" s="' . $s . '"/>';
                } elseif ($valor instanceof DateTimeInterface) {
                    $xml .= '<c r="' . $ref . '" s="' . $s . '"><v>' . self::fecha_excel($valor) . '</v></c>';
                } elseif (is_int($valor) || is_float($valor)) {
                    $xml .= '<c r="' . $ref . '" s="' . $s . '"><v>' . $valor . '</v></c>';
                } else {
                    $xml .= '<c r="' . $ref . '" s="' . $s . '" t="inlineStr"><is><t xml:space="preserve">'
                        . $esc((string) $valor) . '</t></is></c>';
                }
            }
            $xml .= '</row>';
        }
        $xml .= '</sheetData>';
        if ($this->fila_filtro) {
            $xml .= '<autoFilter ref="' . $this->rango_filtro(false) . '"/>';
        }
        if ($this->combinadas) {
            $xml .= '<mergeCells count="' . count($this->combinadas) . '">';
            foreach ($this->combinadas as $rango) {
                $xml .= '<mergeCell ref="' . $rango . '"/>';
            }
            $xml .= '</mergeCells>';
        }
        return $xml . '</worksheet>';
    }

    private function rango_filtro(bool $absoluto): string
    {
        $ultima_fila = max(count($this->filas), $this->fila_filtro);
        $col = self::columna(max(1, $this->max_columnas));
        return $absoluto
            ? '$A$' . $this->fila_filtro . ':$' . $col . '$' . $ultima_fila
            : 'A' . $this->fila_filtro . ':' . $col . $ultima_fila;
    }

    /** 1 -> A, 27 -> AA */
    private static function columna(int $n): string
    {
        $s = '';
        for (; $n > 0; $n = intdiv($n - 1, 26)) {
            $s = chr(65 + ($n - 1) % 26) . $s;
        }
        return $s;
    }

    /** Número de serie de Excel (días desde 1899-12-30, con la hora como fracción). */
    private static function fecha_excel(DateTimeInterface $f): string
    {
        $base = new DateTimeImmutable('1899-12-30 00:00:00', $f->getTimezone());
        $dias = (int) $base->diff(new DateTimeImmutable($f->format('Y-m-d 00:00:00'), $f->getTimezone()))->format('%a');
        $segundos = (int) $f->format('H') * 3600 + (int) $f->format('i') * 60 + (int) $f->format('s');
        return rtrim(rtrim(number_format($dias + $segundos / 86400, 10, '.', ''), '0'), '.');
    }

    private static function estilos(): string
    {
        // fills 0 y 1 son obligatorios (none, gray125); borde 0 = sin borde, 1 = gris fino
        $relleno = fn($rgb) => '<fill><patternFill patternType="solid"><fgColor rgb="FF' . $rgb . '"/><bgColor indexed="64"/></patternFill></fill>';
        $xf = fn($fmt, $font, $fill, $alin = '', $borde = 1) =>
            '<xf numFmtId="' . $fmt . '" fontId="' . $font . '" fillId="' . $fill . '" borderId="' . $borde . '"'
            . ($fmt ? ' applyNumberFormat="1"' : '') . ' applyFont="1" applyFill="1" applyBorder="1"'
            . ($alin ? ' applyAlignment="1"><alignment ' . $alin . '/></xf>' : '/>');
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="3">'
            . '<numFmt numFmtId="164" formatCode="dd/mm/yyyy"/>'
            . '<numFmt numFmtId="165" formatCode="hh:mm AM/PM"/>'
            . '<numFmt numFmtId="166" formatCode="dd/mm/yyyy hh:mm"/>'
            . '</numFmts>'
            . '<fonts count="4">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'                              // 0 normal
            . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'  // 1 encabezado
            . '<font><b/><sz val="14"/><color rgb="FF810010"/><name val="Calibri"/></font>'  // 2 título
            . '<font><i/><sz val="10"/><color rgb="FF555555"/><name val="Calibri"/></font>'  // 3 subtítulo
            . '</fonts>'
            . '<fills count="6"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
            . $relleno('810010') . $relleno('C6EFCE') . $relleno('FFEB9C') . $relleno('FFC7CE') . '</fills>'
            . '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border><left style="thin"><color rgb="FFBFBFBF"/></left><right style="thin"><color rgb="FFBFBFBF"/></right>'
            . '<top style="thin"><color rgb="FFBFBFBF"/></top><bottom style="thin"><color rgb="FFBFBFBF"/></bottom><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="10">'
            . $xf(0, 0, 0)                                                   // 0 NORMAL
            . $xf(0, 2, 0, 'vertical="center"', 0)                           // 1 TITULO
            . $xf(0, 1, 2, 'horizontal="center" vertical="center"')          // 2 ENCABEZADO
            . $xf(164, 0, 0, 'horizontal="center"')                          // 3 FECHA
            . $xf(165, 0, 0, 'horizontal="center"')                          // 4 HORA
            . $xf(166, 0, 0, 'horizontal="center"')                          // 5 FECHA_HORA
            . $xf(0, 0, 3, 'horizontal="center"')                            // 6 VERDE
            . $xf(0, 0, 4, 'horizontal="center"')                            // 7 AMARILLO
            . $xf(0, 0, 5, 'horizontal="center"')                            // 8 ROJO
            . $xf(0, 3, 0, '', 0)                                            // 9 SUBTITULO
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    /** ZIP con compresión deflate, armado a mano. */
    private static function zip(array $archivos): string
    {
        $datos = '';
        $central = '';
        $hora = (date('H') << 11) | (date('i') << 5) | intdiv((int) date('s'), 2);
        $fecha = ((date('Y') - 1980) << 9) | (date('n') << 5) | date('j');
        foreach ($archivos as $nombre => $contenido) {
            $comprimido = gzdeflate($contenido, 6);
            $crc = crc32($contenido);
            // versión, banderas (0x0800 = nombres UTF-8), método 8 = deflate, hora, fecha,
            // CRC, tamaño comprimido, tamaño original, largo del nombre, largo del extra
            $cabecera = pack('vvvvvVVVvv', 20, 0x0800, 8, $hora, $fecha, $crc,
                strlen($comprimido), strlen($contenido), strlen($nombre), 0);
            $central .= pack('V', 0x02014b50) . pack('v', 20) . $cabecera
                . pack('vvvVV', 0, 0, 0, 0, strlen($datos)) . $nombre;
            $datos .= pack('V', 0x04034b50) . $cabecera . $nombre . $comprimido;
        }
        return $datos . $central . pack('VvvvvVVv', 0x06054b50, 0, 0, count($archivos), count($archivos),
            strlen($central), strlen($datos), 0);
    }
}
