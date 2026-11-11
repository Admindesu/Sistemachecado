<?php
require_once __DIR__ . '/auth.php';
requerir_admin();

if (!empty($_POST["btnmodificarsub"])) {
    if (!empty($_POST["txtnombre"])) {
        $id = $_POST["txtid"];
        $nombre = $_POST["txtnombre"];
        
        $sql = consulta($conexion, "UPDATE subsecretaria SET nombre = ? WHERE id_subsecretaria = ?", "si", $nombre, $id);
        
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