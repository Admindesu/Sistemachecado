<?php
if (!isset($conexion)) {
    include_once("../modelo/conexion.php");
}

if (isset($_POST['btnmodificar'])) {
    header('Content-Type: application/json');
    // Validar que todos los campos requeridos estén presentes
    if (empty($_POST['id_horario']) || empty($_POST['nombre']) || 
        empty($_POST['hora_entrada']) || empty($_POST['hora_salida']) || 
        !isset($_POST['tolerancia_entrada']) || !isset($_POST['limite_retardo'])) {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios']);
        exit;
    }

    // Sanitizar y validar inputs
    $id_horario = filter_var($_POST['id_horario'], FILTER_SANITIZE_NUMBER_INT);
    $nombre = filter_var($_POST['nombre'], FILTER_SANITIZE_STRING);
    $hora_entrada = $_POST['hora_entrada'];
    $hora_salida = $_POST['hora_salida'];
    $tolerancia = filter_var($_POST['tolerancia_entrada'], FILTER_SANITIZE_NUMBER_INT);
    $limite_retardo = filter_var($_POST['limite_retardo'], FILTER_SANITIZE_NUMBER_INT);
    $descripcion = filter_var($_POST['descripcion'], FILTER_SANITIZE_STRING);

    // Validar formato de hora
    if (!preg_match("/^([0-1][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/", $hora_entrada) ||
        !preg_match("/^([0-1][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/", $hora_salida)) {
        ?>
        <script>
            $(function notificacion(){
                new PNotify({
                    title: "Error",
                    text: "Formato de hora inválido",
                    type: "error",
                    styling: "bootstrap3"
                });
            });
        </script>
        <?php
        return;
    }

    // Validar que la hora de salida sea posterior a la de entrada
    if (strtotime($hora_entrada) >= strtotime($hora_salida)) {
        ?>
        <script>
            $(function notificacion(){
                new PNotify({
                    title: "Error",
                    text: "La hora de salida debe ser posterior a la hora de entrada",
                    type: "error",
                    styling: "bootstrap3"
                });
            });
        </script>
        <?php
        return;
    }

    // Validar rangos de tolerancia y retardo
    if ($tolerancia < 0 || $tolerancia > 60 || $limite_retardo < 0 || $limite_retardo > 60 || $limite_retardo <= $tolerancia) {
        ?>
        <script>
            $(function notificacion(){
                new PNotify({
                    title: "Error",
                    text: "Los valores de tolerancia y límite de retardo son inválidos",
                    type: "error",
                    styling: "bootstrap3"
                });
            });
        </script>
        <?php
        return;
    }

    try {
        // Preparar la consulta
        $query = "UPDATE horarios SET 
                    nombre = ?, 
                    hora_entrada = ?, 
                    hora_salida = ?, 
                    tolerancia_entrada = ?, 
                    limite_retardo = ?, 
                    descripcion = ?
                 WHERE id_horario = ?";
        
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("sssiisi", $nombre, $hora_entrada, $hora_salida, $tolerancia, $limite_retardo, $descripcion, $id_horario);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Horario modificado correctamente']);
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
                    text: "Error al modificar el horario: <?= $e->getMessage() ?>",
                    type: "error",
                    styling: "bootstrap3"
                });
            });
        </script>
        <?php
    }
}
