<?php 
// Asegúrate de incluir la conexión a la base de datos antes de usar $conexion
include_once("../modelo/conexion.php");

if (!empty($_POST["btnmodificar"])) {
    if (
        !empty($_POST["txtid"]) &&
        !empty($_POST["txtnombre"]) &&
        !empty($_POST["txtapellido"]) &&
        !empty($_POST["txtcargo"]) &&
        !empty($_POST["txtusuario"]) &&
        !empty($_POST["txtpassword"])
    ) {
        $id = $_POST["txtid"];
        $nombre = $_POST["txtnombre"];
        $apellido = $_POST["txtapellido"];
        $cargo = $_POST["txtcargo"];
        $usuario = $_POST["txtusuario"];
        $password = md5($_POST["txtpassword"]);
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;

        $sql = $conexion->query(
            "UPDATE empleado SET 
                nombre='$nombre',
                apellido='$apellido',
                cargo='$cargo',
                usuario='$usuario',
                password='$password',
                is_admin='$is_admin'
            WHERE id_empleado=$id"
        );
        if ($sql == true) { ?>
            <script>
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
        setTimeout(() => {
            window.history.replaceState(null, null, window.location.pathname); 
        }, 0);
    </script>
<?php }
?>