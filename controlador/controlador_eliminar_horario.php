<?php
require_once __DIR__ . '/auth.php';
requerir_admin();

if (!isset($conexion)) {
    include_once("../modelo/conexion.php");
}

if (isset($_GET['id'])) {
    $id_horario = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    
    try {
        // Verificar si el horario está siendo usado por empleados
        $query = "SELECT COUNT(*) as total FROM empleado WHERE id_horario = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $id_horario);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['total'] > 0) {
            ?>
            <script>
                $(function notificacion(){
                    new PNotify({
                        title: "Error",
                        text: "No se puede eliminar el horario porque está asignado a empleados",
                        type: "error",
                        styling: "bootstrap3"
                    });
                });
            </script>
            <?php
            return;
        }
        
        // Si no está siendo usado, proceder con la eliminación
        $query = "DELETE FROM horarios WHERE id_horario = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $id_horario);
        
        if ($stmt->execute()) {
            ?>
            <script>
                $(function notificacion(){
                    new PNotify({
                        title: "Correcto",
                        text: "Horario eliminado exitosamente",
                        type: "success",
                        styling: "bootstrap3"
                    });
                });
            </script>
            <?php
        } else {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        ?>
        <script>
            $(function notificacion(){
                new PNotify({
                    title: "Error",
                    text: "Error al eliminar el horario",
                    type: "error",
                    styling: "bootstrap3"
                });
            });
        </script>
        <?php
    }
}
