<?php
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