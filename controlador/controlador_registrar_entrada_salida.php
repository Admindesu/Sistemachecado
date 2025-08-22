<?php
session_start();
include "../modelo/conexion.php";

date_default_timezone_set('America/Noronha');

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
                
                // Límites fijos según los requerimientos
                $limite_anticipacion = $minutos_entrada - 15; // 15 minutos antes
                $limite_puntual = $minutos_entrada + 10; // A tiempo hasta 10 minutos después
                $limite_retardo = $minutos_entrada + 20; // Retardo hasta 20 minutos después
                
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
                error_log("Límite anticipación: " . $limite_anticipacion);
                error_log("Límite puntual: " . $limite_puntual);
                error_log("Límite retardo: " . $limite_retardo_final);
                
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
                
                // Verificar si ya es hora de salida
                if ($minutos_actual < $minutos_salida) {
                    error_log("Intento de salida temprana");
                    header("Location: ../index.php?msg=salida_temprana");
                    exit;
                }

                // Verificar si han pasado más de 1 hora después de la hora de salida
                $limite_salida = $minutos_salida + 60; // 60 minutos = 1 hora
                
                // Log para depuración
                error_log("Registro de salida:");
                error_log("Hora actual: " . $hora_actual_str);
                error_log("Hora salida configurada: " . $empleado['hora_salida']);
                error_log("Minutos actual: " . $minutos_actual);
                error_log("Minutos salida: " . $minutos_salida);
                error_log("Límite salida (1 hora después): " . $limite_salida);

                // Verificar entrada del día
                $fecha_actual = $hora_actual->format('Y-m-d');
                $sql_check = "SELECT * FROM asistencia WHERE dni = ? AND tipo = 'entrada' AND DATE(fecha) = ? ORDER BY fecha DESC LIMIT 1";
                $stmt_check = $conexion->prepare($sql_check);
                $stmt_check->bind_param("ss", $dni, $fecha_actual);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();
                
                if ($result_check->num_rows > 0) {
                    $entrada = $result_check->fetch_assoc();
                    
                    // Si han pasado más de 1 hora de la hora de salida, marcar como FALTA
                    if ($minutos_actual > $limite_salida) {
                        $sql_update = "UPDATE asistencia SET estado = 'FALTA' WHERE id_asistencia = ?";
                        $stmt_update = $conexion->prepare($sql_update);
                        $stmt_update->bind_param("i", $entrada['id_asistencia']);
                        $stmt_update->execute();
                    }
                }

                // Registrar la salida
                $sql_insert = "INSERT INTO asistencia (dni, tipo, fecha) VALUES (?, ?, ?)";
                $stmt_insert = $conexion->prepare($sql_insert);
                $fecha_str = $hora_actual->format('Y-m-d H:i:s');
                $stmt_insert->bind_param("sss", $dni, $tipo, $fecha_str);
                
                if ($stmt_insert->execute()) {
                    header("Location: ../index.php?msg=salida_ok");
                    exit;
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
        header("Location: ../index.php?msg=error_registro");
        exit;
    }
} else {
    header("Location: ../index.php?msg=datos_invalidos");
    exit;
}timezone_set('America/Cancun');

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
            $fecha_actual = new DateTime();
            
            // Si es registro de entrada, verificar horario
            if ($tipo === 'entrada') {
                // Verificar si el empleado tiene horario asignado
                if (empty($empleado['hora_entrada'])) {
                    throw new Exception('Empleado no tiene horario asignado');
                }

                $hora_actual = new DateTime();
                $hora_actual_str = $hora_actual->format('H:i:s');
                
                // Convertir la hora de entrada del horario a timestamp
                $hora_entrada = DateTime::createFromFormat('H:i:s', $empleado['hora_entrada']);
                if (!$hora_entrada) {
                    throw new Exception('Formato de hora inválido en el horario');
                }
                
                // Obtener solo la hora actual sin la fecha para comparación
                $hora_actual_comp = DateTime::createFromFormat('H:i:s', $hora_actual_str);
                
                // Convertir a minutos desde medianoche para comparación
                $minutos_actual = $hora_actual_comp->format('H') * 60 + $hora_actual_comp->format('i');
                $minutos_entrada = $hora_entrada->format('H') * 60 + $hora_entrada->format('i');
                
                $tolerancia = intval($empleado['tolerancia_entrada']);
                $limite_retardo = intval($empleado['limite_retardo']);
                
                // Calcular límites en minutos
                $limite_puntual = $minutos_entrada + $tolerancia;
                $limite_retardo = $minutos_entrada + $limite_retardo;
                
                // Determinar estado
                if ($minutos_actual <= $limite_puntual) {
                    $estado = 'A_TIEMPO';
                } elseif ($minutos_actual <= $limite_retardo) {
                    $estado = 'RETARDO';
                } else {
                    $estado = 'FALTA';
                }
                
                // Log para depuración
                error_log("Comparación de tiempos:");
                error_log("Hora actual (minutos desde medianoche): " . $minutos_actual);
                error_log("Hora entrada (minutos desde medianoche): " . $minutos_entrada);
                error_log("Límite puntual (minutos): " . $limite_puntual);
                error_log("Límite retardo (minutos): " . $limite_retardo);
                error_log("Estado asignado: " . $estado);
                
                // Log para depuración
                error_log("Registro de entrada:");
                error_log("Hora actual: " . $hora_actual->format('Y-m-d H:i:s'));
                error_log("Hora entrada establecida: " . $hora_entrada->format('Y-m-d H:i:s'));
                error_log("Límite puntual (con tolerancia): " . $hora_max_puntual->format('Y-m-d H:i:s'));
                error_log("Límite retardo: " . $hora_max_retardo->format('Y-m-d H:i:s'));
                error_log("Estado asignado: " . $estado);
                
                error_log("Estado asignado: " . $estado);
                
                // Registrar la entrada con el estado correspondiente
                $sql_insert = "INSERT INTO asistencia (dni, tipo, fecha, estado) VALUES (?, ?, ?, ?)";
                $stmt_insert = $conexion->prepare($sql_insert);
                $fecha_str = $fecha_actual->format('Y-m-d H:i:s');
                $stmt_insert->bind_param("ssss", $dni, $tipo, $fecha_str, $estado);
                
                if ($stmt_insert->execute()) {
                    $mensaje = "entrada_" . strtolower($estado);
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
                $hora_actual_str = $hora_actual->format('H:i:s');
                
                // Convertir la hora de salida del horario a objeto DateTime
                $hora_salida = DateTime::createFromFormat('H:i:s', $empleado['hora_salida']);
                if (!$hora_salida) {
                    throw new Exception('Formato de hora de salida inválido en el horario');
                }
                
                // Convertir a minutos desde medianoche para comparación
                $hora_actual_comp = DateTime::createFromFormat('H:i:s', $hora_actual_str);
                $minutos_actual = $hora_actual_comp->format('H') * 60 + $hora_actual_comp->format('i');
                $minutos_salida = $hora_salida->format('H') * 60 + $hora_salida->format('i');
                
                // Verificar si ya es hora de salida
                if ($minutos_actual < $minutos_salida) {
                    error_log("Intento de salida temprana: Actual(" . $hora_actual_str . ") < Salida(" . $empleado['hora_salida'] . ")");
                    header("Location: ../index.php?msg=salida_temprana");
                    exit;
                }

                // Si pasa la validación, registrar la salida
                $sql_insert = "INSERT INTO asistencia (dni, tipo, fecha) VALUES (?, ?, ?)";
                $stmt_insert = $conexion->prepare($sql_insert);
                $fecha_str = $fecha_actual->format('Y-m-d H:i:s');
                $stmt_insert->bind_param("sss", $dni, $tipo, $fecha_str);
                
                if ($stmt_insert->execute()) {
                    header("Location: ../index.php?msg=salida_ok");
                    exit;
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
        header("Location: ../index.php?msg=error_registro");
        exit;
    }
} else {
    header("Location: ../index.php?msg=datos_invalidos");
    exit;
}
