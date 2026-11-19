<?php
require_once __DIR__ . '/auth.php';
requerir_admin();
 
// Código para depuración - comenta esta sección una vez que resuelvas el problema
// if (isset($_POST["btnmodificar"])) {
//     echo "<div class='alert alert-info'>Formulario enviado. Datos recibidos:<pre>";
//     print_r($_POST);
//     echo "</pre></div>";
// }

// Incluye el archivo de conexión a la base de datos para poder ejecutar consultas SQL
// include_once("../modelo/conexion.php"); // Esto probablemente ya se incluye en el archivo principal

// Verifica si el formulario de modificación fue enviado
if (isset($_POST["btnmodificar"])) {
    // Debug para verificar los datos recibidos (opcional)
    // echo "<div class='alert alert-info'>Formulario enviado. Datos recibidos:<pre>";
    // print_r($_POST);
    // echo "</pre></div>";

    // Comprueba que los campos requeridos tengan valores
    if (
        !empty($_POST["txtid"]) &&
        !empty($_POST["txtnombre"]) &&
        !empty($_POST["txtapellido"]) &&
        !empty($_POST["txtcargo"]) &&
        !empty($_POST["txtdireccion"]) &&
        !empty($_POST["txtusuario"])
    ) {
        $id = (int) $_POST["txtid"];
        $nombre = trim($_POST["txtnombre"]);
        $apellido = trim($_POST["txtapellido"]);
        $cargo = (int) $_POST["txtcargo"];
        $direccion = (int) $_POST["txtdireccion"];
        $subsecretaria = !empty($_POST["txtsubsecretaria"]) ? (int) $_POST["txtsubsecretaria"] : null; // opcional
        $usuario = trim($_POST["txtusuario"]);
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;
        $horario = !empty($_POST["txthorario"]) ? (int) $_POST["txthorario"] : null;

        // Solo se cambia la contraseña si se escribió una nueva.
        $password_nueva = $_POST["txtpassword"] ?? '';

        if ($password_nueva !== '') {
            $password = hash_password($password_nueva);
            $stmt = $conexion->prepare("UPDATE empleado SET
                nombre = ?, apellido = ?, usuario = ?, password = ?,
                cargo = ?, direccion = ?, subsecretaria = ?, is_admin = ?, id_horario = ?
                WHERE id_empleado = ?");
            $stmt->bind_param("ssssiiiiii", $nombre, $apellido, $usuario, $password,
                $cargo, $direccion, $subsecretaria, $is_admin, $horario, $id);
        } else {
            $stmt = $conexion->prepare("UPDATE empleado SET
                nombre = ?, apellido = ?, usuario = ?,
                cargo = ?, direccion = ?, subsecretaria = ?, is_admin = ?, id_horario = ?
                WHERE id_empleado = ?");
            $stmt->bind_param("sssiiiiii", $nombre, $apellido, $usuario,
                $cargo, $direccion, $subsecretaria, $is_admin, $horario, $id);
        }

        // Ejecuta la consulta y verifica si tuvo éxito
        $consulta = $stmt->execute();
        
        if($consulta){
            echo '<div class="alert alert-success">Empleado modificado correctamente</div>';
            
            // Redirige a la misma página para actualizar la vista
            echo "<script>
                setTimeout(function(){
                    window.location.href = 'empleado.php';
                }, 1500);
            </script>";
        } else {
            echo '<div class="alert alert-danger">Error al modificar empleado: ' . htmlspecialchars($stmt->error) . '</div>';
        }
    } else { 
        echo '<div class="alert alert-warning">Hay campos requeridos vacíos</div>';
    }
}
?>
    