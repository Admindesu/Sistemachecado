<?php
require_once __DIR__ . '/auth.php';
requerir_admin();

if (!empty($_GET["id_subsecretaria"])) {
    $id_subsecretaria = $_GET["id_subsecretaria"];
    
    // Primero verificamos si la subsecretaría está en uso
    $verificar = consulta($conexion, "SELECT COUNT(*) as total FROM empleado WHERE subsecretaria = ?", "i", $id_subsecretaria);
    $resultado = $verificar->fetch_object();
    
    if ($resultado->total > 0) {
        // La subsecretaría está en uso
        ?>
        <script>
            $(document).ready(function() {
                new PNotify({
                    title: "Error al eliminar",
                    text: "No se puede eliminar la subsecretaría porque está siendo utilizada por empleados",
                    type: "error",
                    styling: "bootstrap3"
                });
            });
        </script>
        <?php
    } else {
        // La subsecretaría no está en uso, procedemos a eliminar
        $sql = consulta($conexion, "DELETE FROM subsecretaria WHERE id_subsecretaria = ?", "i", $id_subsecretaria);
        
        if ($sql == true) { ?>
            <script>
                $(document).ready(function() {
                    new PNotify({
                        title: "Eliminación Exitosa",
                        text: "Subsecretaría eliminada correctamente.",
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
                        text: "Error al eliminar subsecretaría",
                        type: "error",
                        styling: "bootstrap3"
                    });
                });
            </script>
        <?php }
    }
    
    // Limpiar la URL después de procesar pero mantener el parámetro 'nav'
    ?>
    <script>
    $(document).ready(function() {
        setTimeout(function() {
            window.history.replaceState(null, null, window.location.pathname + "?nav=subsecretaria"); 
        }, 0);
    });
    </script>
    <?php
}
?>