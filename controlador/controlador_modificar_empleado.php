<?php 
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
        !empty($_POST["txtsubsecretaria"]) &&
        !empty($_POST["txtusuario"])
    ) {
        // Asigna los valores recibidos a variables y aplica escape de caracteres para evitar inyección SQL
        $id = $conexion->real_escape_string($_POST["txtid"]);
        $nombre = $conexion->real_escape_string($_POST["txtnombre"]);
        $apellido = $conexion->real_escape_string($_POST["txtapellido"]);
        $cargo = $conexion->real_escape_string($_POST["txtcargo"]);
        $direccion = $conexion->real_escape_string($_POST["txtdireccion"]);
        $subsecretaria = $conexion->real_escape_string($_POST["txtsubsecretaria"]);
        $usuario = $conexion->real_escape_string($_POST["txtusuario"]);
        $password = $conexion->real_escape_string($_POST["txtpassword"]);
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;

        // Construye la consulta SQL
        if(!empty($password)){
            $sql = "UPDATE empleado SET 
                nombre='$nombre', 
                apellido='$apellido', 
                usuario='$usuario', 
                password='$password', 
                cargo='$cargo', 
                direccion='$direccion', 
                subsecretaria='$subsecretaria',
                is_admin=$is_admin 
                WHERE id_empleado=$id";
        } else {
            $sql = "UPDATE empleado SET 
                nombre='$nombre', 
                apellido='$apellido', 
                usuario='$usuario', 
                cargo='$cargo', 
                direccion='$direccion', 
                subsecretaria='$subsecretaria',
                is_admin=$is_admin 
                WHERE id_empleado=$id";
        }

        // Debug de la consulta SQL (opcional)
        // echo "<div class='alert alert-info'>Consulta SQL: $sql</div>";
        
        // Ejecuta la consulta y verifica si tuvo éxito
        $consulta = $conexion->query($sql);
        
        if($consulta){
            echo '<div class="alert alert-success">Empleado modificado correctamente</div>';
            
            // Redirige a la misma página para actualizar la vista
            echo "<script>
                setTimeout(function(){
                    window.location.href = 'empleado.php';
                }, 1500);
            </script>";
        } else {
            echo '<div class="alert alert-danger">Error al modificar empleado: ' . $conexion->error . '</div>';
        }
    } else { 
        echo '<div class="alert alert-warning">Hay campos requeridos vacíos</div>';
    }
}
?>
    