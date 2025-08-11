<?php
session_start();
include "../../modelo/conexion.php";

// Función para generar token seguro
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Función para enviar email (reemplazar con tu sistema de correo)
function sendResetEmail($email, $token) {
    $resetLink = "http://localhost/sis-asistencia/vista/login/newpassword.php?token=" . $token;
    $to = $email;
    $subject = "Recuperación de contraseña - Sistema de Asistencia";
    $message = "Para restablecer tu contraseña, haz clic en el siguiente enlace:\n\n" . $resetLink;
    $headers = "From: noreply@sistema-asistencia.com";

    return mail($to, $subject, $message, $headers);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Recuperar Contraseña</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        if ($_SERVER["REQUEST_METHOD"] == "POST") {
                            $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING);
                            
                            if (!empty($usuario)) {
                                // Verificar si el usuario existe
                                $stmt = $conexion->prepare("SELECT id_usuario, nombre, email FROM usuario WHERE usuario = ? AND estado = 'activo'");
                                $stmt->bind_param("s", $usuario);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result->num_rows > 0) {
                                    $user = $result->fetch_assoc();
                                    $token = generateToken();
                                    $expiracion = date('Y-m-d H:i:s', strtotime('+24 hours')); // Changed from +1 hour to +24 hours

                                    // Guardar token en la base de datos
                                    $stmt = $conexion->prepare("INSERT INTO password_resets (usuario_id, token, fecha_expiracion) VALUES (?, ?, ?)");
                                    $stmt->bind_param("iss", $user['id_usuario'], $token, $expiracion);
                                    
                                    if ($stmt->execute()) {
                                        // Enviar el correo usando el email del usuario
                                        if (sendResetEmail($user['email'], $token)) {
                                            echo '<div class="alert alert-success">
                                                    Se ha enviado un enlace de recuperación a ' . htmlspecialchars($user['email']) . '
                                                  </div>';
                                        } else {
                                            echo '<div class="alert alert-danger">
                                                    Error al enviar el correo. Por favor, contacte al administrador.
                                                  </div>';
                                        }
                                    } else {
                                        echo '<div class="alert alert-danger">
                                                Error al procesar la solicitud. Por favor, intenta nuevamente.
                                              </div>';
                                    }
                                } else {
                                    echo '<div class="alert alert-danger">
                                            Usuario no encontrado o cuenta inactiva.
                                          </div>';
                                }
                                $stmt->close();
                            }
                        }
                        ?>

                        <form method="POST" action="">
                            <div class="form-group mb-3">
                                <label for="usuario">Nombre de Usuario</label>
                                <input type="text" class="form-control" id="usuario" name="usuario" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Enviar enlace de recuperación</button>
                                <a href="login.php" class="btn btn-secondary">Volver al login</a>
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