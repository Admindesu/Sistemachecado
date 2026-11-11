<?php
require_once __DIR__ . '/auth.php';
requerir_admin();

if (isset($_POST['btnregistrar'])) {
    if (!empty($_POST['txtnombre'])) {
        $nombre = $_POST['txtnombre'];
    }
}

if (!empty($_POST["id_direccion"])) {
    $id_direccion = $_POST["id_direccion"];
    
    // Primero verificamos si la dirección está en uso
    $verificar = consulta($conexion, "SELECT COUNT(*) as total FROM empleado WHERE direccion = ?", "i", $id_direccion);
    $resultado = $verificar->fetch_object();
    
    if ($resultado->total > 0) {
        // La dirección está en uso
        ?>
        <script>
            $(document).ready(function() {
                new PNotify({
                    title: "Error al eliminar",
                    text: "No se puede eliminar la dirección porque está siendo utilizada por empleados",
                    type: "error",
                    styling: "bootstrap3"
                });
            });
        </script>
        <?php
    } else {
        // La dirección no está en uso, procedemos a eliminar
        $sql = consulta($conexion, "DELETE FROM direccion WHERE id_direccion = ?", "i", $id_direccion);
        
        if ($sql == true) { ?>
            <script>
                $(document).ready(function() {
                    new PNotify({
                        title: "Eliminación Exitosa",
                        text: "Dirección eliminada correctamente.",
                        type: "success",
                        styling: "bootstrap3"
                    });
                });
            </script>
        <?php } else { ?>
            <script>
                $(document).ready(function() {
                    new PNotify({
                        title: "Eliminación Fallida",
                        text: "Error al eliminar dirección",
                        type: "error",
                        styling: "bootstrap3"
                    });
                });
            </script>
        <?php }
    }
    
    // Limpiar la URL después de procesar
    ?>
    <script>
    $(document).ready(function() {
        setTimeout(function() {
            window.history.replaceState(null, null, window.location.pathname + "?nav=direccion"); 
        }, 0);
    });
    </script>
    <?php
}
?>