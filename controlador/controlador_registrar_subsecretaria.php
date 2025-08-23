<?php

// Verifica si el formulario fue enviado (si el botón 'btnregistrar' no está vacío)
if (isset($_POST['btnregistrar'])) {
    // Verifica si el campo 'txtnombre' no está vacío
    if (!empty($_POST['txtnombre'])) {
        $nombre = $_POST['txtnombre'];
        
        // Consulta para verificar si ya existe una subsecretaría con el mismo nombre
        $verificar = $conexion->query("SELECT count(*) as 'total' FROM subsecretaria WHERE nombre = '$nombre'");
        
        // Si el nombre ya existe, muestra una notificación de error
        if ($verificar->fetch_object()->total > 0) { ?>
            <script>
                $(document).ready(function() {
                    new PNotify({
                        title: "Error",
                        text: "La subsecretaría ya existe",
                        type: "error",
                        styling: "bootstrap3"
                    });
                });
            </script>
        <?php } else {
            // Si el nombre no existe, inserta la nueva subsecretaría en la base de datos
            $sql = $conexion->query("INSERT INTO subsecretaria (nombre) VALUES ('$nombre')");
            
            // Si la inserción fue exitosa, muestra una notificación de éxito
            if ($sql == true) { ?>
                <script>
                    $(document).ready(function() {
                        new PNotify({
                            title: "Éxito",
                            text: "Subsecretaría registrada correctamente",
                            type: "success",
                            styling: "bootstrap3",
                            after_close: function() {
                                window.location = "organigrama.php?nav=subsecretaria";
                            }
                        });
                    });
                </script>
            <?php } else { ?>
                <script>
                    $(document).ready(function() {
                        new PNotify({
                            title: "Error",
                            text: "Error al registrar la subsecretaría: <?= $conexion->error ?>",
                            type: "error",
                            styling: "bootstrap3"
                        });
                    });
                </script>
            <?php }
        }
    } else { ?>
        <script>
            $(document).ready(function() {
                new PNotify({
                    title: "Advertencia",
                    text: "El campo nombre no puede estar vacío",
                    type: "warning",
                    styling: "bootstrap3"
                });
            });
        </script>
    <?php }
    
    // Prevenir reenvío del formulario al recargar la página
    echo '<script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>';
}
?>