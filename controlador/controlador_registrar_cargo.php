<?php
// Verifica si el formulario fue enviado (si el botón 'btnregistrar' no está vacío)
if (!empty($_POST['btnregistrar'])) {
    // Verifica si el campo 'txtnombre' no está vacío
    if (!empty($_POST['txtnombre'])) {
        $nombre = $_POST['txtnombre'];
        // Consulta para verificar si el nombre del cargo ya existe en la base de datos
        $verificarNombre = $conexion->query("select count(*) as 'total' from cargo where nombre='$nombre'");
        // Si el cargo ya existe, muestra una notificación de error
        if ($verificarNombre->fetch_object()->total > 0) { ?>
            <script>
                // Notificación de error usando PNotify si el cargo ya existe
                $(function notificacion(){
                    new PNotify({
                        title: "Error",
                        text: "Este cargo ya existe",
                        type: "error",
                        styling: "bootstrap3"
                    })
                })
            </script>
        <?php } else {
            // Si el cargo no existe, realiza el registro en la base de datos
            $sql= $conexion->query("INSERT INTO cargo(nombre) VALUES ('$nombre')");
            // Si la inserción fue exitosa, muestra una notificación de éxito
            if ($sql==true) { ?>
                <script>
                    // Notificación de éxito usando PNotify si el cargo se registró correctamente
                    $(function notificacion(){
                        new PNotify({
                            title: "Correcto",
                            text: "Cargo registrado correctamente",
                            type: "success",
                            styling: "bootstrap3"
                        })
                    })
                </script>
            <?php } else { ?>
                <script>
                    // Notificación de error usando PNotify si hubo un error al registrar el cargo
                    $(function notificacion(){
                        new PNotify({
                            title: "Inorrecto",
                            text: "Error al registrar el cargo",
                            type: "error",
                            styling: "bootstrap3"
                        })
                    })
                </script>
            <?php }
        }
    } else { ?>
        <script>
            // Notificación de error usando PNotify si los campos están vacíos
            $(function notificacion(){
                new PNotify({
                    title: "Inorrecto",
                    text: "Los campos no pueden estar vacios",
                    type: "error",
                    styling: "bootstrap3"
                })
            })
        </script>
    <?php } ?>
    <script>
        // Limpia el historial para evitar el reenvío del formulario al recargar la página
        setTimeout(() => {
            window.history.replaceState(null, null, window.location.pathname); 
        }, 0);
    </script>
<?php }
?>