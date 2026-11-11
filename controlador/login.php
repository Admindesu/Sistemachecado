<?php
require_once __DIR__ . '/auth.php';
include "../modelo/conexion.php";

function volver_al_login(string $error): void
{
    header('Location: ../vista/login/login.php?error=' . $error);
    exit;
}

if (empty($_POST['btningresar'])) {
    volver_al_login('');
}

$usuario = trim($_POST['usuario'] ?? '');
$password = $_POST['password'] ?? '';

if ($usuario === '' || $password === '') {
    volver_al_login('vacio');
}

$resultado = consulta(
    $conexion,
    "SELECT id_empleado, nombre, apellido, dni, is_admin, password FROM empleado WHERE usuario = ?",
    "s",
    $usuario
);
$datos = $resultado ? $resultado->fetch_object() : null;

if (!$datos || !verificar_password($password, $datos->password)) {
    volver_al_login('credenciales');
}

// Migración automática: si la contraseña sigue en md5, se guarda con password_hash()
if (necesita_rehash($datos->password)) {
    consulta($conexion, "UPDATE empleado SET password = ? WHERE id_empleado = ?", "si",
        hash_password($password), $datos->id_empleado);
}

// Evita fijación de sesión
session_regenerate_id(true);

$_SESSION['id_usuario'] = $datos->id_empleado;
$_SESSION['nombre'] = $datos->nombre;
$_SESSION['apellido'] = $datos->apellido;
$_SESSION['dni'] = $datos->dni;
$_SESSION['is_admin'] = $datos->is_admin;

header("Location: ../index.php");
exit;
