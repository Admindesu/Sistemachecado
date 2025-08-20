<?php
if (!empty($_POST["btnmodificardir"])) {
    if (!empty($_POST["txtnombre"])) {
        $id = $_POST["txtid"];
        $nombre = $_POST["txtnombre"];
        
        $sql = $conexion->query("UPDATE direccion SET nombre='$nombre' WHERE id_direccion=$id");
        
        if ($sql == true) { ?>
            <script>
                $(function notificacion() {
                    new PNotify({
                        title: "Correcto",
                        text: "Dirección modificada correctamente",
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
                        text: "Error al modificar dirección",
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