<?php
// Verifica si el botón de registro fue presionado
if(!empty($_POST["btnregistrar"])){
    // Verifica que todos los campos requeridos estén llenos
    if(!empty($_POST["txtnombre"]) && !empty($_POST["txtapellido"]) && !empty($_POST["txtusuario"]) && !empty($_POST["txtpassword"])){
        // Asigna los valores recibidos por POST a variables locales
        $nombre = $_POST["txtnombre"];
        $apellido = $_POST["txtapellido"];
        $usuario = $_POST["txtusuario"];
        // Encripta la contraseña usando md5 (no recomendado en producción)
        $password = md5($_POST["txtpassword"]);

        // Consulta si el usuario ya existe en la base de datos
        $sql=$conexion->query("select count(*) as 'total' from usuario where usuario='$usuario'");
        // Si el usuario ya existe, muestra una notificación de error
        if($sql->fetch_object()->total>0) {?>
            <script>
                // Notificación de error usando PNotify
                $(function notificacion(){
                    new PNotify({
                        title: "Error",
                        text: "Este usuario ya existe",
                        type: "error",
                        styling: "bootstrap3"
                    })
                })
            </script>
        <?php } else{
            // Si el usuario no existe, intenta registrar el nuevo usuario
            $registro=$conexion->query("insert into usuario(nombre,apellido,usuario,password)values('$nombre','$apellido','$usuario','$password')");
            // Si el registro fue exitoso, muestra una notificación de éxito
            if ($registro==true) {?>
                <script>
                    // Notificación de éxito usando PNotify
                    $(function notificacion(){
                        new PNotify({
                            title: "Correcto",
                            text: "Este usuario se ha registrado correctamente",
                            type: "success",
                            styling: "bootstrap3"
                        })
                    })
                </script>
            <?php }else { ?>
                <script>
                    // Notificación de error si hubo un problema al registrar
                    $(function notificacion(){
                        new PNotify({
                            title: "Error",
                            text: "Error al registrar usuario",
                            type: "error",
                            styling: "bootstrap3"
                        })
                    })
                </script>
            <?php }
        }
    } else { ?>
        <script>
            // Notificación de error si algún campo está vacío
            $(function notificacion(){
                new PNotify({
                    title: "Error",
                    text: "los campos estan vacios ",
                    type: "error",
                    styling: "bootstrap3"
                })
            })
        </script>
    <?php  }?>

    <script>
        // Limpia el historial para evitar reenvío del formulario al recargar la página
        setTimeout(() => {
            window.history.replaceState(null, null, window.location.pathname); 
        }, 0);
    </script>

<?php }
