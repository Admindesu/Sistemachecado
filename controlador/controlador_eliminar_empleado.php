<?php
if (!empty($_GET['id'])) {   
    $id = $_GET['id'];
    $sql = $conexion->query("delete from empleado where id_empleado=$id");
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