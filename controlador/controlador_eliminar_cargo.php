<?php
// filepath: c:\xampp\htdocs\Sistemachecado\controlador\controlador_eliminar_cargo.php
// Delete cargo
if (!empty($_GET["id"])) {
    $id = $_GET["id"];
    $sql = $conexion->query("DELETE FROM cargo WHERE id_cargo = $id");
    
    if ($sql == true) { ?>
        <script>
            $(function notificacion() {
                new PNotify({
                    title: "Correcto",
                    text: "Cargo eliminado correctamente",
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
                    text: "Error al eliminar cargo. Puede estar en uso.",
                    type: "error",
                    styling: "bootstrap3"
                })
            })
        </script>
    <?php }
}

// Delete direccion
if (!empty($_GET["id_direccion"])) {
    $id = $_GET["id_direccion"];
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

// Delete subsecretaria
if (!empty($_GET["id_subsecretaria"])) {
    $id = $_GET["id_subsecretaria"];
    $sql = $conexion->query("DELETE FROM subsecretaria WHERE id_subsecretaria = $id");
    
    if ($sql == true) { ?>
        <script>
            $(function notificacion() {
                new PNotify({
                    title: "Correcto",
                    text: "Subsecretaría eliminada correctamente",
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
                    text: "Error al eliminar subsecretaría. Puede estar en uso.",
                    type: "error",
                    styling: "bootstrap3"
                })
            })
        </script>
    <?php }
}
?>