<?php
// Verifica si el parámetro 'id' está presente en la URL
if(!empty($_GET['id'])){
    $id = $_GET['id'];

    // Ejecuta la consulta SQL para eliminar el usuario con el id proporcionado
    $sql = $conexion->query("delete from usuario where id_usuario=$id");

    // Si la consulta fue exitosa, muestra una notificación de éxito
    if ($sql == true) { ?>
        <script>
            // Función para mostrar notificación de éxito usando PNotify
            $(function notificacion(){
                new PNotify({
                    title: "Correcto",
                    text: "Este usuario se ha eliminado correctamente",
                    type: "success",
                    styling: "bootstrap3"
                })
            })
        </script>
    <?php } else { ?>
        <script>
            // Función para mostrar notificación de error usando PNotify
            $(function notificacion(){
                new PNotify({
                    title: "Incorrecto",
                    text: "Error al eliminar usuario",
                    type: "error",
                    styling: "bootstrap3"
                })
            })
        </script>
    <?php } ?>
    <script>
        // Función para limpiar la URL eliminando los parámetros GET después de ejecutar la acción
        setTimeout(() => {
            window.history.replaceState(null, null, window.location.pathname); 
        }, 0);
    </script>
<?php }
?>