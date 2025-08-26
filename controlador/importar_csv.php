<?php
session_start();
include "../modelo/conexion.php";
?>
<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .btn {
            margin: 10px;
        }
        .alert {
            max-width: 800px;
            margin: 20px auto;
        }
        .result-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<?php
// Check if the download template button was clicked
if(isset($_GET['download_template'])) {
    $filename = '../vista/plantillacsv/bulk_usuarios.csv';
    
    // Verificar que el archivo exista
    if (!file_exists($filename)) {
        // Si no existe, crear el archivo con contenido básico
        $content = "nombre,apellido,dni,usuario,password,cargo,direccion,subsecretaria,is_admin,id_horario\n";
        $content .= "Juan,Pérez,12345678,jperez,password123,1,1,1,0,1\n";
        $content .= "María,González,87654321,mgonzalez,password456,2,2,2,0,2";
        file_put_contents($filename, $content);
    }
    
    // Configurar headers para descarga
    header('Content-Description: File Transfer');
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="plantilla_empleados.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Content-Length: ' . filesize($filename));
    
    // Limpiar cualquier salida anterior
    ob_clean();
    flush();
    
    // Enviar el archivo
    readfile($filename);
    exit;
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
    
    if ($table === 'horarios') {
        $query = "SELECT $id_field, nombre, hora_entrada, hora_salida FROM $table";
    } else {
        $query = "SELECT $id_field, nombre FROM $table";
    }
    
    $result = $conexion->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $id = (int)$row[$id_field];
            $ids[] = $id;
            if ($table === 'horarios') {
                $names[$id] = $row['nombre'] . ' (' . 
                             date('h:i A', strtotime($row['hora_entrada'])) . ' - ' . 
                             date('h:i A', strtotime($row['hora_salida'])) . ')';
            } else {
                $names[$id] = $row['nombre'];
            }
        }
    }
    
    return ['ids' => $ids, 'names' => $names];
}

// Get available IDs before processing
$cargos = getAvailableIDs($conexion, 'cargo', 'id_cargo');
$direcciones = getAvailableIDs($conexion, 'direccion', 'id_direccion');
$subsecretarias = getAvailableIDs($conexion, 'subsecretaria', 'id_subsecretaria');
$horarios = getAvailableIDs($conexion, 'horarios', 'id_horario');

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

// Horarios
echo "<div style='flex:1; min-width:300px;'>";
echo "<h4>Horarios:</h4><ul>";
foreach ($horarios['names'] as $id => $name) {
    echo "<li>ID $id: $name</li>";
}
echo "</ul></div>";
echo "</div>";

echo "<p>Por favor verifica que los IDs en tu CSV coincidan con los valores disponibles en la base de datos.</p>";
echo "<p>El formato del CSV debe ser: nombre,apellido,dni,usuario,password,cargo,direccion,subsecretaria,is_admin,id_horario</p>";
echo "</div>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csvFile'])) {
    try {
        $file = $_FILES['csvFile'];
        
        // Check for upload errors
        if ($file['error'] !== 0) {
            throw new Exception('Error en la carga: ' . $file['error']);
        }
        
        // Validate file type
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($fileExtension !== 'csv') {
            throw new Exception('El archivo debe tener extensión .csv');
        }

        // Open and read the file
        $handle = fopen($file['tmp_name'], "r");
        if ($handle === FALSE) {
            throw new Exception('No se pudo abrir el archivo');
        }

        // Begin transaction
        $conexion->begin_transaction();

        try {
            // Skip header row
            $header = fgetcsv($handle);
            if (!$header) {
                throw new Exception('El archivo CSV está vacío');
            }

            // Validate header columns
            $expectedColumns = ['nombre', 'apellido', 'dni', 'usuario', 'password', 'cargo', 'direccion', 'subsecretaria', 'is_admin', 'id_horario'];
            if (count($header) !== count($expectedColumns)) {
                throw new Exception('El formato del CSV no es correcto. Se esperaban ' . count($expectedColumns) . ' columnas');
            }

            // Prepare the statement
            $stmt = $conexion->prepare("INSERT INTO empleado (nombre, apellido, dni, usuario, password, cargo, direccion, subsecretaria, is_admin, id_horario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if (!$stmt) {
                throw new Exception("Error preparando la consulta: " . $conexion->error);
            }

            $rowNumber = 2; // Start at 2 because row 1 is headers
            $successful = 0;
            $errors = [];

            while (($data = fgetcsv($handle)) !== FALSE) {
                try {
                    if (count($data) !== count($expectedColumns)) {
                        throw new Exception("Número incorrecto de columnas");
                    }

                    // Validate foreign keys
                    if (!in_array($data[5], $cargos['ids'])) {
                        throw new Exception("ID de cargo inválido");
                    }
                    if (!in_array($data[6], $direcciones['ids'])) {
                        throw new Exception("ID de dirección inválido");
                    }
                    if (!in_array($data[7], $subsecretarias['ids'])) {
                        throw new Exception("ID de subsecretaría inválido");
                    }
                    if (!in_array($data[9], $horarios['ids'])) {
                        throw new Exception("ID de horario inválido");
                    }

                    // Hash password
                    $hashedPassword = md5($data[4]);
                    
                    // Bind parameters
                    $stmt->bind_param("sssssiiiis", 
                        $data[0], // nombre
                        $data[1], // apellido
                        $data[2], // dni
                        $data[3], // usuario
                        $hashedPassword, // password
                        $data[5], // cargo
                        $data[6], // direccion
                        $data[7], // subsecretaria
                        $data[8], // is_admin
                        $data[9]  // id_horario
                    );

                    if ($stmt->execute()) {
                        $successful++;
                    } else {
                        throw new Exception($stmt->error);
                    }
                } catch (Exception $e) {
                    $errors[] = "Error en la fila $rowNumber: " . $e->getMessage();
                }
                $rowNumber++;
            }

            if (count($errors) > 0) {
                throw new Exception(implode("\n", $errors));
            }

            // If we got here, commit the transaction
            $conexion->commit();
            
            ?>
            <div style="text-align: center; margin-top: 20px;">
                <div class="alert alert-success" role="alert">
                    <h4 class="alert-heading">¡Importación Exitosa!</h4>
                    <p>Se importaron <?= $successful ?> empleados correctamente.</p>
                </div>
                <a href="../vista/empleado.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Regresar a Empleados
                </a>
            </div>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'Se importaron <?= $successful ?> empleados correctamente',
                    showConfirmButton: true,
                    confirmButtonText: 'OK'
                });
            </script>
            <?php
            
        } catch (Exception $e) {
            $conexion->rollback();
            throw $e;
        }

        fclose($handle);
        
    } catch (Exception $e) {
        ?>
        <div style="text-align: center; margin-top: 20px;">
            <div class="alert alert-danger" role="alert">
                <h4 class="alert-heading">Error en la Importación</h4>
                <p><?= nl2br(htmlspecialchars($e->getMessage())) ?></p>
            </div>
            <a href="../vista/empleado.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Regresar a Empleados
            </a>
        </div>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error en la Importación',
                text: '<?= str_replace("'", "\'", $e->getMessage()) ?>',
                showConfirmButton: true,
                confirmButtonText: 'OK'
            });
        </script>
        <?php
    }
}