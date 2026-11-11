<?php
require_once __DIR__ . '/auth.php';
requerir_admin();

// filepath: c:\xampp\htdocs\Sistemachecado\controlador\controlador_eliminar_cargo.php
if (!empty($_GET["id"])) {
    $id = $_GET["id"];
    
    // Primero verificamos si el cargo está en uso
    $verificar = consulta($conexion, "SELECT COUNT(*) as total FROM empleado WHERE cargo = ?", "i", $id);
    $resultado = $verificar->fetch_object();
    
    if ($resultado->total > 0) {
        // El cargo está en uso
        ?>
        <script>
            $(function notificacion(){
                new PNotify({
                    title: "Error al eliminar",
                    text: "No se puede eliminar el cargo porque está siendo utilizado por empleados",
                    type: "error",
                    styling: "bootstrap3"
                })
            })
        </script>
        <?php
    } else {
        // El cargo no está en uso, procedemos a eliminar
        $sql = consulta($conexion, "DELETE FROM cargo WHERE id_cargo = ?", "i", $id);
        
        if ($sql == true) { ?>
            <script>
                $(function notificacion(){
                    new PNotify({
                        title: "Eliminación Exitosa",
                        text: "Cargo eliminado correctamente.",
                        type: "success",
                        styling: "bootstrap3"
                    })
                })
            </script>
        <?php } else { ?>
            <script>
                $(function notificacion(){
                    new PNotify({
                        title: "Eliminación Fallida",
                        text: "Error al eliminar cargo",
                        type: "error",
                        styling: "bootstrap3"
                    })
                })
            </script>
        <?php }
    }
    
    // Limpiar la URL después de procesar
    ?>
    <script>
    setTimeout(() => {
       window.history.replaceState(null, null, window.location.pathname); 
    }, 0);
    </script>
    <?php
}
?>