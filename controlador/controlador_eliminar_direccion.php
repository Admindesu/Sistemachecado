<?php
if (!empty($_GET["id"])) {
    $id = $_GET["id"];
    $sql = $conexion->query("DELETE FROM direccion WHERE id_direccion = $id");
    
    if ($sql == true) { ?>
        <script>
            $(function notificacion() {
                new PNotify({
                    title: "Correcto",
                    text: "Dirección eliminada correctamente",
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
                    text: "Error al eliminar dirección. Puede estar en uso.",
                    type: "error",
                    styling: "bootstrap3"
                })
            })
        </script>
    <?php }
}
?>