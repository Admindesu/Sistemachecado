
<?php
require_once __DIR__ . '/auth.php';
requerir_admin();

/**
 * Controlador para eliminar un empleado de la base de datos.
 *
 * Este script recibe el parámetro 'id' por GET, ejecuta la consulta SQL para eliminar el registro
 * correspondiente en la tabla 'empleado' y muestra una notificación al usuario indicando el resultado.
 * Utiliza PNotify para mostrar mensajes de éxito o error en la interfaz.
 * 
 * Notas de implementación:
 * - El script utiliza jQuery y PNotify para las notificaciones.
 * - Se realiza un replaceState en el historial del navegador para limpiar la URL después de la operación.
 */
if (!empty($_POST['id'])) {   
    $id = $_POST['id'];
    // Solo se borra definitivamente a quien ya está dado de baja (borra también su historial)
    $sql = consulta($conexion, "DELETE FROM empleado WHERE id_empleado = ? AND activo = 0", "i", $id);
    $sql = $sql && $conexion->affected_rows > 0;
    if ($sql == true) { ?>
        <script>
            $(function notificacion() {
                new PNotify({
                    title: "Correcto",
                    text: "Este empleado se ha eliminado correctamente",
                    type: "success",
                    styling: "bootstrap3"
                })
            })
        </script>
<?php } else { ?> 
<script>
            $(function notificacion() {
                new PNotify({
                    title: "Incorrecto",
                    text: "Error al eliminar empleado",
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