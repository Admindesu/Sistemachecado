<?php 
// Incluye el archivo de conexión a la base de datos para poder ejecutar consultas SQL
include_once("../modelo/conexion.php");

// Verifica si el formulario de modificación fue enviado
if (!empty($_POST["btnmodificar"])) {
    // Comprueba que todos los campos requeridos del formulario estén completos
    if (
        !empty($_POST["txtid"]) &&
        !empty($_POST["txtnombre"]) &&
        !empty($_POST["txtapellido"]) &&
        !empty($_POST["txtcargo"]) &&
        !empty($_POST["txtdireccion"]) &&
        !empty($_POST["txtsubsecretaria"]) &&
        !empty($_POST["txtusuario"]) &&
        !empty($_POST["txtpassword"])
    ) {
        // Asigna los valores recibidos por POST a variables locales
        $id = $_POST["txtid"];
        $nombre = $_POST["txtnombre"];
        $apellido = $_POST["txtapellido"];
        $cargo = $_POST["txtcargo"];
        $direccion = $_POST["txtdireccion"];
        $subsecretaria = $_POST["txtsubsecretaria"];
        $usuario = $_POST["txtusuario"];
        // Encripta la contraseña usando md5 antes de almacenarla
        $password = md5($_POST["txtpassword"]);
        // Determina si el usuario es administrador según el checkbox recibido
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;

        // Ejecuta la consulta SQL para actualizar los datos del empleado en la base de datos
        $sql = $conexion->query(
            "UPDATE empleado SET 
                nombre='$nombre',
                apellido='$apellido',
                cargo='$cargo',
                direccion='$direccion',
                subsecretaria='$subsecretaria',
                usuario='$usuario',
                password='$password',
                is_admin='$is_admin'
            WHERE id_empleado=$id"
        );
        // Si la consulta fue exitosa, muestra una notificación de éxito con PNotify
        if ($sql == true) { ?>
            <script>
                // Notifica al usuario que la modificación fue exitosa
                $(function notificacion(){
                    new PNotify({
                        title: "Correcto",
                        text: "Empleado modificado correctamente",
                        type: "success",
                        styling: "bootstrap3"
                    })
                })
            </script>
        <?php } else { ?>
            <script>
                // Notifica al usuario que ocurrió un error al modificar el empleado
                $(function notificacion(){
                    new PNotify({
                        title: "Error",
                        text: "Error al modificar empleado",
                        type: "error",
                        styling: "bootstrap3"
                    })
                })
            </script>
        <?php } 
    } else { ?>
        <script>
            // Notifica al usuario que hay campos vacíos en el formulario
            $(function notificacion(){
                new PNotify({
                    title: "Error",
                    text: "Campos vacíos",
                    type: "error",
                    styling: "bootstrap3"
                })
            })
        </script>
    <?php } ?>
    <script>
        // Limpia el historial para evitar el reenvío del formulario al recargar la página
        setTimeout(() => {
            window.history.replaceState(null, null, window.location.pathname); 
        }, 0);
    </script>
<?php }
?>