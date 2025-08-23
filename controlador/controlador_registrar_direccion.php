<?php

if (isset($_POST['btnregistrar'])) {
    if (!empty($_POST['txtnombre'])) {
        $nombre = $_POST['txtnombre'];
        
        // Validamos si la dirección ya existe
        $verificar = $conexion->query("SELECT count(*) as 'total' FROM direccion WHERE nombre = '$nombre'");
        
        if ($verificar->fetch_object()->total > 0) { ?>
            <script>
                $(document).ready(function() {
                    new PNotify({
                        title: "Error",
                        text: "La dirección ya existe",
                        type: "error",
                        styling: "bootstrap3"
                    });
                });
            </script>
        <?php } else {
            // Si no existe, la registramos
            $sql = $conexion->query("INSERT INTO direccion (nombre) VALUES ('$nombre')");
            
            if ($sql == true) { ?>
                <script>
                    $(document).ready(function() {
                        new PNotify({
                            title: "Éxito",
                            text: "Dirección registrada correctamente",
                            type: "success",
                            styling: "bootstrap3",
                            after_close: function() {
                                window.location = "organigrama.php?nav=direccion";
                            }
                        });
                    });
                </script>
            <?php } else { ?>
                <script>
                    $(document).ready(function() {
                        new PNotify({
                            title: "Error",
                            text: "Error al registrar la dirección: <?= $conexion->error ?>",
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