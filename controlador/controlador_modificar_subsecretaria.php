<?php
if (!empty($_POST["btnmodificarsub"])) {
    if (!empty($_POST["txtnombre"])) {
        $id = $_POST["txtid"];
        $nombre = $_POST["txtnombre"];
        
        $sql = $conexion->query("UPDATE subsecretaria SET nombre='$nombre' WHERE id_subsecretaria=$id");
        
        if ($sql == true) { ?>
            <script>
                $(function notificacion() {
                    new PNotify({
                        title: "Correcto",
                        text: "Subsecretaría modificada correctamente",
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
                        text: "Error al modificar subsecretaría",
                        type: "error",
                        styling: "bootstrap3"
                    })
                })
            </script>
        <?php }
    } else { ?>
        <script>
            $(function notificacion() {
                new PNotify({
                    title: "Error",
                    text: "Los campos no pueden estar vacíos",
                    type: "error",
                    styling: "bootstrap3"
                })
            })
        </script>
    <?php }
}
?>