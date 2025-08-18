<?php 
// Incluye el archivo de conexión a la base de datos para poder ejecutar consultas SQL
include_once("../modelo/conexion.php");

// Verifica si el formulario fue enviado comprobando si el botón 'btnregistrar' no está vacío
if (!empty($_POST['btnregistrar'])) {

    // Comprueba que los campos requeridos no estén vacíos antes de continuar
    if (!empty($_POST["txtnombre"]) && !empty($_POST["txtapellido"]) && !empty($_POST["txtusuario"])) {

        // Asigna los valores recibidos por POST a variables locales
        $nombre = $_POST["txtnombre"];
        $apellido = $_POST["txtapellido"];
        $usuario = $_POST["txtusuario"];
        $id = $_POST["txtid"];

        // Consulta si ya existe un usuario con el mismo nombre de usuario, excluyendo el usuario actual por su id
        $sql = $conexion->query("select count(*) as 'total' from usuario where usuario='$usuario' and id_usuario!=$id");

        // Si el usuario ya existe, muestra una notificación de error usando PNotify
        if ($sql->fetch_object()->total > 0) { ?>
            <script>
                // Notificación de error: el usuario ya existe
                $(function notificacion(){
                    new PNotify({
                        title: "Error",
                        text: "Este usuario ya existe",
                        type: "error",
                        styling: "bootstrap3"
                    })
                })
            </script>
        <?php 
        } else {
            // Si el usuario no existe, actualiza los datos del usuario en la base de datos
            $modificar = $conexion->query("update usuario set nombre='$nombre',apellido='$apellido',usuario='$usuario' where id_usuario=$id");

            // Si la actualización fue exitosa, muestra una notificación de éxito
            if ($modificar == true) { ?>
                <script>
                    // Notificación de éxito: usuario modificado correctamente
                    $(function notificacion(){
                        new PNotify({
                            title: "Correcto",
                            text: "Este usuario se ha modificado correctamente",
                            type: "success",
                            styling: "bootstrap3"
                        })
                    })
                </script>
            <?php 
            } else { ?>
                <script>
                    // Notificación de error: ocurrió un error al modificar el usuario
                    $(function notificacion(){
                        new PNotify({
                            title: "Incorrecto",
                            text: "Error",
                            type: "error",
                            styling: "bootstrap3"
                        })
                    })
                </script>
            <?php 
            }
        }
    } else {
        // No se realiza ninguna acción si los campos requeridos están vacíos
    } ?>

    <script>
        // Elimina los parámetros de la URL para evitar reenvío de formulario al refrescar la página
        setTimeout(() => {
            window.history.replaceState(null, null, window.location.pathname); 
        }, 0);
    </script>
<?php 
}
?>