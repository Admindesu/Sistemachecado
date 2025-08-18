
<?php
/**
 * Este script elimina un registro de asistencia de la base de datos según el ID recibido por GET.
 * 
 * Proceso:
 * - Verifica que el parámetro 'id' esté presente en la URL.
 * - Incluye el archivo de conexión a la base de datos.
 * - Ejecuta la consulta DELETE sobre la tabla 'asistencia' usando el ID proporcionado.
 * - Muestra una notificación (PNotify) en el frontend indicando si la eliminación fue exitosa o fallida.
 * - Utiliza JavaScript para limpiar la URL después de la operación.
 * 
 */
if (!empty($_GET['id'])) {
    include "../modelo/conexion.php";
    $id = $_GET['id'];
    $sql = $conexion->query("DELETE FROM asistencia WHERE id_asistencia = '$id'");

    if ($sql==true) {?>
        <script>
            $(function notificacion(){
                new PNotify({
                    title: "Eliminación Exitosa",
                    text: "Registro eliminado correctamente.",
                    type: "success",
                    styling: "bootstrap3"
            })
            })
        </script>
    <?php } else {?>
         <script>
            $(function notificacion(){
                new PNotify({
                    title: "Eliminación Fallida",
                    text: "No se pudo eliminar el registro.",
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