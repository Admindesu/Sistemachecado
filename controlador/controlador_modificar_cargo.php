<?php
// Verifica si el formulario fue enviado (si el botón 'btnmodificar' no está vacío)
if (!empty($_POST['btnmodificar'])) {

    // Verifica que los campos 'txtid' y 'txtnombre' no estén vacíos
    if (!empty($_POST['txtid']) && !empty($_POST['txtnombre'])) {
        $nombre = $_POST['txtnombre'];
        $id = $_POST['txtid'];

        // Consulta para verificar si ya existe un cargo con el mismo nombre, excluyendo el actual por id
        $verificarNombre = $conexion->query("select count(*) as 'total' from cargo where nombre = '$nombre' and id_cargo != '$id'");

        // Si el nombre ya existe, muestra una notificación de error usando PNotify
        if ($verificarNombre->fetch_object()->total > 0) { ?>
            <script>
                $(function notificacion() {
                    new PNotify({
                        title: "Incorrecto",
                        text: "El nombre ya existe",
                        type: "error",
                        styling: "bootstrap3"
                    })
                })
            </script>
        <?php } else { 
            // Si el nombre no existe, realiza la actualización del cargo en la base de datos
            $sql=$conexion->query("UPDATE cargo SET nombre='$nombre' WHERE id_cargo='$id'");

            // Si la actualización fue exitosa, muestra una notificación de éxito
            if ($sql==true) { ?>
                <script>
                    $(function notificacion() {
                        new PNotify({
                            title: "Correcto",
                            text: "Cargo modificado correctamente",
                            type: "success",
                            styling: "bootstrap3"
                        })
                    })
                </script>
            <?php } else { ?>
              
                <script>
                    $(function notificacion() {
                        new PNotify({
                            title: "Incorrecto",
                            text: "Error al modificar el cargo",
                            type: "error",
                            styling: "bootstrap3"
                        })
                    })
                </script>
            <?php }
        }
    } else { ?>
    
        <script>
            $(function notificacion() {
                new PNotify({
                    title: "Incorrecto",
                    text: "Los campos no pueden estar vacios",
                    type: "error",
                    styling: "bootstrap3"
                })
            })
        </script>
    <?php } ?>


    <script>
        setTimeout(() => {
            window.history.replaceState(null, null, window.location.pathname); 
        }, 0);
    </script>
<?php }

?>