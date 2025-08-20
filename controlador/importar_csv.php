<?php
session_start();
include "../modelo/conexion.php";

// Check if the download template button was clicked
if(isset($_GET['download_template'])) {
    $file = "../vista/plantillacsv/bulk_usuarios.csv";
    
    if(file_exists($file)) {
        // Set headers for download
        header('Content-Description: File Transfer');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="plantilla_empleados.csv"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    } else {
        $_SESSION['error'] = "La plantilla no se encuentra disponible";
        header('Location: importar_csv.php');
        exit;
    }
}

// Enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to log and display errors
function logError($message) {
    error_log($message);
    return $message;
}

// Gather available IDs from tables
function getAvailableIDs($conexion, $table, $id_field) {
    $ids = [];
    $names = [];
    $result = $conexion->query("SELECT $id_field, nombre FROM $table");
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $id = (int)$row[$id_field];
            $ids[] = $id;
            $names[$id] = $row['nombre'];
        }
    }
    
    return ['ids' => $ids, 'names' => $names];
}

// Get available IDs before processing
$cargos = getAvailableIDs($conexion, 'cargo', 'id_cargo');
$direcciones = getAvailableIDs($conexion, 'direccion', 'id_direccion');
$subsecretarias = getAvailableIDs($conexion, 'subsecretaria', 'id_subsecretaria');

// Display available IDs
echo "<div style='background:#f8f9fa; color:#212529; padding:20px; margin:20px; border-radius:5px; border:1px solid #dee2e6;'>";
echo "<h3>IDs disponibles en la base de datos</h3>";

echo "<div style='display:flex; flex-wrap:wrap; gap:20px;'>";
// Cargo
echo "<div style='flex:1; min-width:300px;'>";
echo "<h4>Cargos:</h4><ul>";
foreach ($cargos['names'] as $id => $name) {
    echo "<li>ID $id: $name</li>";
}
echo "</ul></div>";

// Direccion
echo "<div style='flex:1; min-width:300px;'>";
echo "<h4>Direcciones:</h4><ul>";
foreach ($direcciones['names'] as $id => $name) {
    echo "<li>ID $id: $name</li>";
}
echo "</ul></div>";

// Subsecretaria
echo "<div style='flex:1; min-width:300px;'>";
echo "<h4>Subsecretarías:</h4><ul>";
foreach ($subsecretarias['names'] as $id => $name) {
    echo "<li>ID $id: $name</li>";
}
echo "</ul></div>";
echo "</div>";

echo "<p>Por favor verifica que los IDs en tu CSV coincidan con los valores disponibles en la base de datos.</p>";
echo "</div>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csvFile'])) {
    try {
        $file = $_FILES['csvFile'];
        
        // Check for upload errors
        if ($file['error'] !== 0) {
            throw new Exception('Error en la carga: ' . $file['error']);
        }
        
        // Check file size
        if ($file['size'] == 0) {
            throw new Exception('El archivo está vacío');
        }
        
        // Validate file type (more permissive)
        $allowedTypes = ['text/csv', 'application/vnd.ms-excel', 'text/plain', 'application/octet-stream'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('El archivo debe ser CSV. Tipo detectado: ' . $file['type']);
        }

        // Open file
        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            $errorMsg = error_get_last() ? error_get_last()['message'] : 'Unknown error';
            throw new Exception('Error al abrir el archivo: ' . $errorMsg);
        }

        // Skip header row
        $headers = fgetcsv($handle);
        if (!$headers) {
            throw new Exception('El archivo CSV está vacío o tiene formato inválido');
        }

        // Begin transaction
        $conexion->begin_transaction();

        // Prepare insert statement
        $stmt = $conexion->prepare("INSERT INTO empleado (nombre, apellido, dni, usuario, password, cargo, direccion, subsecretaria, is_admin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception('Error en prepare statement: ' . $conexion->error);
        }

        $count = 0;
        $errors = [];
        $csvData = [];
        
        // First pass: validate all rows
        while (($data = fgetcsv($handle)) !== FALSE) {
            $rowNum = $count + 2; // +2 because of header row and 0-indexing
            $csvData[] = $data;
            
            // Validate row data
            if (count($data) !== 9) {
                $errors[] = "Fila $rowNum: Formato inválido. Columnas esperadas: 9, Encontradas: " . count($data);
                $count++;
                continue;
            }
            
            $cargo = (int)$data[5];
            $direccion = (int)$data[6];
            $subsecretaria = (int)$data[7];
            
            // Validate foreign keys
            if (!in_array($cargo, $cargos['ids'])) {
                $errors[] = "Fila $rowNum: El cargo ID $cargo no existe en la base de datos. Valores válidos: " . implode(', ', $cargos['ids']);
            }
            
            if (!in_array($direccion, $direcciones['ids'])) {
                $errors[] = "Fila $rowNum: La dirección ID $direccion no existe en la base de datos. Valores válidos: " . implode(', ', $direcciones['ids']);
            }
            
            if (!in_array($subsecretaria, $subsecretarias['ids'])) {
                $errors[] = "Fila $rowNum: La subsecretaría ID $subsecretaria no existe en la base de datos. Valores válidos: " . implode(', ', $subsecretarias['ids']);
            }
            
            $count++;
        }
        
        // If there are validation errors, stop and report them
        if (!empty($errors)) {
            throw new Exception("Se encontraron errores en el archivo CSV:<br>" . implode("<br>", $errors));
        }
        
        // Reset file pointer
        rewind($handle);
        fgetcsv($handle); // Skip header again
        
        // Second pass: insert rows
        $count = 0;
        foreach ($csvData as $data) {
            try {
                // Create variables that can be passed by reference
                $nombre = $data[0];
                $apellido = $data[1];
                $dni = $data[2];
                $usuario = $data[3];
                $password = md5($data[4]);
                $cargo = (int)$data[5];
                $direccion = (int)$data[6];
                $subsecretaria = (int)$data[7];
                $is_admin = filter_var($data[8], FILTER_VALIDATE_BOOLEAN) ? '1' : '0';

                // Bind parameters and execute
                $stmt->bind_param("sssssiiis", 
                    $nombre,
                    $apellido,
                    $dni,
                    $usuario,
                    $password,
                    $cargo,
                    $direccion,
                    $subsecretaria,
                    $is_admin
                );

                if (!$stmt->execute()) {
                    throw new Exception('Error al insertar: ' . $stmt->error);
                }

                $count++;
            } catch (Exception $rowEx) {
                throw new Exception('Error en la fila ' . ($count + 2) . ': ' . $rowEx->getMessage());
            }
        }

        // Commit transaction
        $conexion->commit();
        fclose($handle);

        $_SESSION['mensaje'] = "Se importaron $count registros exitosamente";
        
        echo "<div style='background:#d4edda; color:#155724; padding:15px; margin:20px; border-radius:5px;'>
            <h3>Importación Exitosa</h3>
            <p>Se importaron $count registros exitosamente.</p>
            <br>
            <a href='../vista/empleado.php' style='background:#155724; color:white; padding:10px; text-decoration:none; border-radius:5px;'>
                Volver a empleados
            </a>
        </div>";
        
    } catch (Exception $e) {
        $errorMsg = logError("CSV Import Error: " . $e->getMessage());
        
        // Rollback on error
        if (isset($conexion) && $conexion->ping()) {
            $conexion->rollback();
        }
        
        if (isset($handle) && is_resource($handle)) {
            fclose($handle);
        }
        
        echo "<div style='background:#f8d7da; color:#721c24; padding:15px; margin:20px; border-radius:5px;'>
            <h3>Error al importar CSV:</h3>
            <p>{$errorMsg}</p>
            <br>
            <a href='../vista/empleado.php' style='background:#721c24; color:white; padding:10px; text-decoration:none; border-radius:5px;'>
                Volver a empleados
            </a>
        </div>";
    }
} else {
    echo "<div style='margin:20px; padding:15px; background:#e9ecef; border-radius:5px;'>
        <h3>Importar empleados desde CSV</h3>
        <p>Por favor carga un archivo CSV con el formato correcto:</p>
        
        <div style='display:flex; justify-content:space-between; margin-bottom:20px;'>";
            // Download template button
            echo "<a href='importar_csv.php?download_template=1' class='btn btn-info' style='background:#17a2b8; color:white; border:none; padding:10px 20px; border-radius:4px; text-decoration:none;'>
                <i class='fas fa-download'></i> Descargar plantilla CSV
            </a>
        <div>
        </div>
        
        <form action='importar_csv.php' method='POST' enctype='multipart/form-data'>
            <div style='margin-bottom:15px;'>
                <input type='file' name='csvFile' accept='.csv' required style='padding:10px; border:1px solid #ced4da; border-radius:4px; width:100%;'>
            </div>
            <button type='submit' style='background:#007bff; color:white; border:none; padding:10px 20px; border-radius:4px; cursor:pointer;'>
                Importar CSV
            </button>
        </form>
    </div>";
}
?>