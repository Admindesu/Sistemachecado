<?php
session_start();
include "../../modelo/conexion.php";

if (!isset($_GET['token'])) {
    header('Location: login.php');
    exit();
}

$token = $_GET['token'];
$current_time = date('Y-m-d H:i:s');

// Verificar token válido y no expirado
$stmt = $conexion->prepare("SELECT pr.usuario_id, u.usuario 
                           FROM password_resets pr 
                           JOIN usuario u ON pr.usuario_id = u.id_usuario 
                           WHERE pr.token = ? AND pr.fecha_expiracion > ? 
                           AND NOT EXISTS (
                               SELECT 1 FROM password_resets 
                               WHERE usuario_id = pr.usuario_id 
                               AND fecha_creacion > pr.fecha_creacion
                           )");
$stmt->bind_param("ss", $token, $current_time);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: login.php?error=token_invalido');
    exit();
}

$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Establecer Nueva Contraseña</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        if ($_SERVER["REQUEST_METHOD"] == "POST") {
                            $password = $_POST['password'];
                            $confirm_password = $_POST['confirm_password'];

                            if ($password === $confirm_password) {
                                if (strlen($password) >= 8) {
                                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                    
                                    // Actualizar contraseña
                                    $stmt = $conexion->prepare("UPDATE usuario SET password = ? WHERE id_usuario = ?");
                                    $stmt->bind_param("si", $hashed_password, $user['usuario_id']);
                                    
                                    if ($stmt->execute()) {
                                        // Eliminar todos los tokens de reset para este usuario
                                        $stmt = $conexion->prepare("DELETE FROM password_resets WHERE usuario_id = ?");
                                        $stmt->bind_param("i", $user['usuario_id']);
                                        $stmt->execute();
                                        
                                        echo '<div class="alert alert-success">
                                                Contraseña actualizada exitosamente. 
                                                <a href="login.php">Iniciar sesión</a>
                                              </div>';
                                    } else {
                                        echo '<div class="alert alert-danger">
                                                Error al actualizar la contraseña.
                                              </div>';
                                    }
                                } else {
                                    echo '<div class="alert alert-danger">
                                            La contraseña debe tener al menos 8 caracteres.
                                          </div>';
                                }
                            } else {
                                echo '<div class="alert alert-danger">
                                        Las contraseñas no coinciden.
                                      </div>';
                            }
                        }
                        ?>

                        <form method="POST" action="">
                            <div class="form-group mb-3">
                                <label for="password">Nueva Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       required minlength="8">
                            </div>
                            <div class="form-group mb-3">
                                <label for="confirm_password">Confirmar Contraseña</label>
                                <input type="password" class="form-control" id="confirm_password" 
                                       name="confirm_password" required minlength="8">
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>