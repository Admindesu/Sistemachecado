<?php
require_once __DIR__ . '/auth.php';
requerir_admin();

if (!empty($_POST["btnmodificardir"])) {
    if (!empty($_POST["txtnombre"])) {
        $id = $_POST["txtid"];
        $nombre = $_POST["txtnombre"];
        
        $sql = consulta($conexion, "UPDATE direccion SET nombre = ? WHERE id_direccion = ?", "si", $nombre, $id);
        
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