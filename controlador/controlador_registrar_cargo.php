<?php
// Para depuración - ver lo que llega en POST
// echo "<pre>"; print_r($_POST); echo "</pre>";

// Verifica si el formulario fue enviado (si el botón 'btnregistrar' existe)
if (isset($_POST['btnregistrar'])) {
    // Verifica si el campo 'txtnombre' no está vacío
    if (!empty($_POST['txtnombre'])) {
        $nombre = $_POST['txtnombre'];
        
        // Consulta para verificar si ya existe un cargo con el mismo nombre
        $verificarNombre = $conexion->query("SELECT count(*) as 'total' FROM cargo WHERE nombre = '$nombre'");
        
        // Si el nombre ya existe, muestra una notificación de error usando PNotify
        if ($verificarNombre->fetch_object()->total > 0) { ?>
            <script>
               $(function notificacion(){
                    new PNotify({
                        title: "Error",
                        text: "Este empleado o usuario ya existe",
                        type: "error",
                        styling: "bootstrap3"
                    })
                })
            </script>
        <?php } else {
            // Si el nombre no existe, inserta el nuevo cargo en la base de datos
            $sql = $conexion->query("INSERT INTO cargo (nombre) VALUES ('$nombre')");
            
            // Si la inserción fue exitosa, muestra una notificación de éxito y redirige a 'organigrama.php'
            if ($sql == true) { ?>
                <script>
                    $(function notificacion(){
                    new PNotify({
                        title: 'Correcto',
                        text: 'Cargo registrado correctamente',
                        type: 'success',
                        styling: 'bootstrap3'
                    });
                    setTimeout(function() {
                        window.location.href = "organigrama.php";
                    }, 1500);
                });
            </script>
        <?php } else { ?>
            <script>
                $(function notificacion(){
                    new PNotify({
                        title: 'Error',
                        text: 'Error al registrar el cargo: <?= $conexion->error ?>',
                        type: 'error',
                        styling: 'bootstrap3'
                    });
                </script>
            <?php }
        }
    } else { ?>
        <script>
            new PNotify({
                title: 'Advertencia',
                text: 'El campo nombre no puede estar vacío',
                type: 'notice',
                styling: 'bootstrap3'
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