<?php
/**
 * Exporta las asistencias a Excel (.xlsx) con los mismos filtros que el reporte PDF:
 * rango de fechas y empleado. Un empleado normal solo puede exportar sus propios registros.
 */
require_once __DIR__ . '/../../controlador/auth.php';
requerir_sesion();   // en POST también valida el token CSRF

include "../../modelo/conexion.php";
require_once __DIR__ . '/../../modelo/xlsx.php';

// Filtros (fechas inválidas -> año en curso)
$fecha_valida = function ($f) { $d = DateTime::createFromFormat('Y-m-d', (string) $f); return $d && $d->format('Y-m-d') === $f; };
$fecha_inicio = $fecha_valida($_POST['fecha_inicio'] ?? '') ? $_POST['fecha_inicio'] : date('Y-01-01');
$fecha_fin = $fecha_valida($_POST['fecha_fin'] ?? '') ? $_POST['fecha_fin'] : date('Y-12-31');
if ($fecha_inicio > $fecha_fin) [$fecha_inicio, $fecha_fin] = [$fecha_fin, $fecha_inicio];
$empleado_id = es_admin() ? ($_POST['empleado'] ?? '') : $_SESSION['dni'];

// Filtro por rango de fechas (sin DATE() para que MySQL pueda usar índices) y por empleado
$where = ["asistencia.fecha >= ?", "asistencia.fecha < DATE_ADD(?, INTERVAL 1 DAY)"];
$tipos = "ss";
$params = [$fecha_inicio, $fecha_fin];
if ($empleado_id) {
    $where[] = "asistencia.dni = ?";
    $tipos .= "s";
    $params[] = $empleado_id;
}

$result = consulta($conexion,
    "SELECT asistencia.dni, asistencia.tipo, asistencia.fecha, asistencia.estado, asistencia.origen,
            CONCAT(empleado.nombre, ' ', empleado.apellido) AS nombre_completo,
            cargo.nombre AS nom_cargo, direccion.nombre AS nom_direccion
     FROM asistencia
     INNER JOIN empleado ON asistencia.dni = empleado.dni
     LEFT JOIN cargo ON empleado.cargo = cargo.id_cargo
     LEFT JOIN direccion ON empleado.direccion = direccion.id_direccion
     WHERE " . implode(" AND ", $where) . "
     ORDER BY asistencia.fecha DESC, asistencia.id_asistencia DESC",
    $tipos, ...$params);

// Color de la celda según el estado
$estilo_estado = [
    'A_TIEMPO' => Xlsx::VERDE,
    'NORMAL' => Xlsx::VERDE,
    'RETARDO' => Xlsx::AMARILLO,
    'SALIDA_TEMPRANA' => Xlsx::AMARILLO,
    'TEMPRANA' => Xlsx::AMARILLO,
    'FALTA' => Xlsx::ROJO,
];

$columnas = ['No. empleado', 'Empleado', 'Cargo', 'Dirección', 'Fecha', 'Hora', 'Tipo', 'Estado', 'Origen'];
$x = new Xlsx('Asistencias');
$x->anchos([14, 36, 28, 28, 12, 11, 10, 18, 11]);

$x->fila(['Reporte de asistencias'], Xlsx::TITULO);
$x->combinar_ultima(count($columnas));

$filtros = 'Periodo: ' . date('d/m/Y', strtotime($fecha_inicio)) . ' al ' . date('d/m/Y', strtotime($fecha_fin));
if ($empleado_id) {
    $emp = consulta($conexion, "SELECT CONCAT(nombre, ' ', apellido) AS nombre FROM empleado WHERE dni = ?", "s", $empleado_id)->fetch_object();
    $filtros .= ' · Empleado: ' . ($emp ? $emp->nombre : $empleado_id);
}
$x->fila([$filtros . ' · Generado: ' . date('d/m/Y H:i')], Xlsx::SUBTITULO);
$x->combinar_ultima(count($columnas));

$x->fila($columnas, Xlsx::ENCABEZADO);
$x->filtrar_desde(3);

$estilos_fila = [Xlsx::NORMAL, Xlsx::NORMAL, Xlsx::NORMAL, Xlsx::NORMAL, Xlsx::FECHA, Xlsx::HORA, Xlsx::NORMAL, Xlsx::NORMAL, Xlsx::NORMAL];
while ($row = $result->fetch_assoc()) {
    $momento = new DateTime($row['fecha']);
    $estado = strtoupper((string) $row['estado']);
    $estilos = $estilos_fila;
    $estilos[7] = $estilo_estado[$estado] ?? Xlsx::NORMAL;
    $x->fila([
        // número de empleado como número si lo es, para que Excel no marque "número almacenado como texto"
        ctype_digit((string) $row['dni']) && strlen($row['dni']) < 15 && $row['dni'][0] !== '0' ? (int) $row['dni'] : $row['dni'],
        $row['nombre_completo'],
        $row['nom_cargo'],
        $row['nom_direccion'],
        $momento,
        $momento,
        ucfirst((string) $row['tipo']),
        $estado !== '' ? str_replace('_', ' ', $estado) : 'N/A',
        $row['origen'] === 'hikvision' ? 'Terminal' : 'Web',
    ], $estilos);
}

$x->descargar('Asistencias_' . $fecha_inicio . '_a_' . $fecha_fin . '.xlsx');
