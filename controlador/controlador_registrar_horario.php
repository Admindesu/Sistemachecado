<?php
/**
 * Alta de horario (formulario "Nuevo Horario" de vista/horarios.php).
 * Responde con una redirección a horarios.php?msg=<resultado>; la vista muestra
 * el mensaje con SweetAlert y recargar la página no vuelve a enviar el formulario.
 */
require_once __DIR__ . '/auth.php';
requerir_admin();   // en POST también valida el token CSRF

include_once("../modelo/conexion.php");

function volver_a_horarios(string $resultado): void
{
    header('Location: ../vista/horarios.php?msg=' . $resultado);
    exit;
}

if (!isset($_POST['btnregistrar'])) {
    header('Location: ../vista/horarios.php');
    exit;
}

// Todos los campos requeridos
if (empty($_POST['nombre']) || empty($_POST['hora_entrada']) || empty($_POST['hora_salida'])
    || !isset($_POST['tolerancia_entrada']) || !isset($_POST['limite_retardo'])) {
    volver_a_horarios('campos');
}

$nombre = trim((string) $_POST['nombre']);
$hora_entrada = (string) $_POST['hora_entrada'];
$hora_salida = (string) $_POST['hora_salida'];
$tolerancia = (int) $_POST['tolerancia_entrada'];
$limite_retardo = (int) $_POST['limite_retardo'];
$descripcion = trim((string) ($_POST['descripcion'] ?? ''));

// Formato HH:MM
$formato = "/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/";
if (!preg_match($formato, $hora_entrada) || !preg_match($formato, $hora_salida)) {
    volver_a_horarios('formato');
}

// La salida debe ser posterior a la entrada
if (strtotime($hora_entrada) >= strtotime($hora_salida)) {
    volver_a_horarios('orden');
}

// Tolerancia y límite de retardo entre 0 y 60, y el límite mayor que la tolerancia
if ($tolerancia < 0 || $tolerancia > 60 || $limite_retardo < 0 || $limite_retardo > 60 || $limite_retardo <= $tolerancia) {
    volver_a_horarios('tolerancia');
}

$ok = consulta($conexion,
    "INSERT INTO horarios (nombre, hora_entrada, hora_salida, tolerancia_entrada, limite_retardo, descripcion)
     VALUES (?, ?, ?, ?, ?, ?)",
    "sssiis", $nombre, $hora_entrada, $hora_salida, $tolerancia, $limite_retardo, $descripcion);

volver_a_horarios($ok ? 'registrado' : 'error');
