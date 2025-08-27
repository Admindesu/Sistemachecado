<?php
session_start();
include "../modelo/conexion.php";

date_default_timezone_set('America/Cancun');

$dni = isset($_SESSION['dni']) ? $_SESSION['dni'] : '';
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';

if ($dni && ($tipo === 'entrada' || $tipo === 'salida')) {
    try {
        // Obtener información del empleado y su horario
        $sql = "SELECT e.*, h.* FROM empleado e 
                LEFT JOIN horarios h ON e.id_horario = h.id_horario 
                WHERE e.dni = ?";
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            throw new Exception('Error preparando consulta: ' . $conexion->error);
        }
        
        $stmt->bind_param("s", $dni);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $empleado = $result->fetch_assoc();
            
            if ($tipo === 'entrada') {
                // Verificar si el empleado tiene horario asignado
                if (empty($empleado['hora_entrada'])) {
                    throw new Exception('Empleado no tiene horario asignado');
                }

                $hora_actual = new DateTime();
                $hora_actual_str = $hora_actual->format('H:i:s');
                
                // Convertir la hora de entrada del horario a objeto DateTime
                $hora_entrada = DateTime::createFromFormat('H:i:s', $empleado['hora_entrada']);
                if (!$hora_entrada) {
                    throw new Exception('Formato de hora inválido en el horario');
                }
                
                // Obtener solo la hora actual sin la fecha para comparación
                $hora_actual_comp = DateTime::createFromFormat('H:i:s', $hora_actual_str);
                
                // Convertir a minutos desde medianoche para comparación
                $minutos_actual = $hora_actual_comp->format('H') * 60 + $hora_actual_comp->format('i');
                $minutos_entrada = $hora_entrada->format('H') * 60 + $hora_entrada->format('i');
                
                // Límites según la tolerancia definida en la tabla horarios
                $tolerancia = isset($empleado['tolerancia_entrada']) ? intval($empleado['tolerancia_entrada']) : 15;
                
                // Límites fijos según los requerimientos
                $limite_anticipacion = $minutos_entrada - 15; // 15 minutos antes
                $limite_puntual = $minutos_entrada + $tolerancia; // A tiempo hasta la tolerancia definida
                $limite_retardo = $minutos_entrada + 30; // Retardo hasta 30 minutos después
                
                // Verificar si no está intentando checar muy temprano
                if ($minutos_actual < $limite_anticipacion) {
                    error_log("Intento de entrada muy temprano: " . $hora_actual_str);
                    header("Location: ../index.php?msg=entrada_anticipada");
                    exit;
                }

                // Determinar estado
                if ($minutos_actual <= $limite_puntual) {
                    $estado = 'A_TIEMPO';
                } elseif ($minutos_actual <= $limite_retardo) {
                    $estado = 'RETARDO';
                } else {
                    $estado = 'FALTA';
                }

                // Log para depuración
                error_log("Registro de entrada:");
                error_log("Hora actual: " . $hora_actual_str);
                error_log("Hora entrada configurada: " . $empleado['hora_entrada']);
                error_log("Minutos actual: " . $minutos_actual);
                error_log("Minutos entrada: " . $minutos_entrada);
                error_log("Tolerancia: " . $tolerancia . " minutos");
                error_log("Límite anticipación: " . $limite_anticipacion);
                error_log("Límite puntual: " . $limite_puntual);
                error_log("Límite retardo: " . $limite_retardo);
                error_log("Estado asignado: " . $estado);
                
                // Registrar la entrada con el estado correspondiente
                $sql_insert = "INSERT INTO asistencia (dni, tipo, fecha, estado) VALUES (?, ?, ?, ?)";
                $stmt_insert = $conexion->prepare($sql_insert);
                $fecha_str = $hora_actual->format('Y-m-d H:i:s');
                $stmt_insert->bind_param("ssss", $dni, $tipo, $fecha_str, $estado);
                
                if ($stmt_insert->execute()) {
                    // Convertir el estado para el mensaje (A_TIEMPO -> a_tiempo)
                    $estado_mensaje = strtolower(str_replace('_', '', $estado));
                    $mensaje = "entrada_" . $estado_mensaje;
                    header("Location: ../index.php?msg=$mensaje");
                    exit;
                } else {
                    throw new Exception('Error registrando entrada: ' . $stmt_insert->error);
                }
            } else {
                // Para registro de salida
                if (empty($empleado['hora_salida'])) {
                    throw new Exception('Empleado no tiene horario de salida asignado');
                }

                $hora_actual = new DateTime();
                $fecha_actual = $hora_actual->format('Y-m-d');
                $hora_actual_str = $hora_actual->format('H:i:s');
                
                // Convertir la hora de salida del horario a objeto DateTime
                $hora_salida = DateTime::createFromFormat('H:i:s', $empleado['hora_salida']);
                if (!$hora_salida) {
                    throw new Exception('Formato de hora de salida inválido en el horario');
                }
                
                // Obtener solo la hora actual sin la fecha para comparación
                $hora_actual_comp = DateTime::createFromFormat('H:i:s', $hora_actual_str);
                
                // Convertir a minutos desde medianoche para comparación
                $minutos_actual = $hora_actual_comp->format('H') * 60 + $hora_actual_comp->format('i');
                $minutos_salida = $hora_salida->format('H') * 60 + $hora_salida->format('i');
                
                // Verificar si ya es hora de salida (usar tolerancia_salida)
                $tolerancia_salida = isset($empleado['tolerancia_salida']) ? intval($empleado['tolerancia_salida']) : 15;
                $minutos_salida_min = $minutos_salida - $tolerancia_salida;
                
                if ($minutos_actual < $minutos_salida_min) {
                    error_log("Intento de salida temprana");
                    header("Location: ../index.php?msg=salida_temprana");
                    exit;
                }

                // Verificar si han pasado más de 2 horas después de la hora de salida para marcar FALTA
                $limite_salida = $minutos_salida + 120; // 120 minutos = 2 horas
                $estado_salida = ($minutos_actual > $limite_salida) ? 'FALTA' : 'A_TIEMPO';
                
                // Log para depuración
                error_log("Registro de salida:");
                error_log("Hora actual: " . $hora_actual_str);
                error_log("Hora salida configurada: " . $empleado['hora_salida']);
                error_log("Minutos actual: " . $minutos_actual);
                error_log("Minutos salida: " . $minutos_salida);
                error_log("Tolerancia salida: " . $tolerancia_salida . " minutos");
                error_log("Minutos salida mínimo: " . $minutos_salida_min);
                error_log("Límite para falta (2 horas después): " . $limite_salida);
                error_log("Estado asignado: " . $estado_salida);

                // Verificar entrada del día
                $sql_check = "SELECT * FROM asistencia WHERE dni = ? AND tipo = 'entrada' AND DATE(fecha) = ? ORDER BY fecha DESC LIMIT 1";
                $stmt_check = $conexion->prepare($sql_check);
                $stmt_check->bind_param("ss", $dni, $fecha_actual);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();
                
                // Registrar la salida con el estado
                $sql_insert = "INSERT INTO asistencia (dni, tipo, fecha, estado) VALUES (?, ?, ?, ?)";
                $stmt_insert = $conexion->prepare($sql_insert);
                $fecha_str = $hora_actual->format('Y-m-d H:i:s');
                $stmt_insert->bind_param("ssss", $dni, $tipo, $fecha_str, $estado_salida);
                
                if ($stmt_insert->execute()) {
                    // Si es una salida muy tarde (FALTA) y había una entrada ese día
                    if ($estado_salida == 'FALTA' && $result_check->num_rows > 0) {
                        $entrada = $result_check->fetch_assoc();
                        
                        // Actualizar la entrada a FALTA también
                        $sql_update = "UPDATE asistencia SET estado = 'FALTA' WHERE id_asistencia = ?";
                        $stmt_update = $conexion->prepare($sql_update);
                        $stmt_update->bind_param("i", $entrada['id_asistencia']);
                        $stmt_update->execute();
                        
                        error_log("Entrada actualizada a FALTA, ID: " . $entrada['id_asistencia']);
                        header("Location: ../index.php?msg=salida_falta");
                        exit;
                    } else {
                        header("Location: ../index.php?msg=salida_ok");
                        exit;
                    }
                } else {
                    throw new Exception('Error registrando salida: ' . $stmt_insert->error);
                }
            }
        } else {
            header("Location: ../index.php?msg=empleado_no_encontrado");
            exit;
        }
    } catch (Exception $e) {
        error_log("Error en registro de asistencia: " . $e->getMessage());
        header("Location: ../index.php?msg=error_registro&error=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: ../index.php?msg=datos_invalidos");
    exit;
}
