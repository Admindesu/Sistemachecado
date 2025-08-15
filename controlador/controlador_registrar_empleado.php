<?php
if(!empty($_POST["btnregistrar"])){
    if(
        !empty($_POST["txtnombre"]) &&
        !empty($_POST["txtapellido"]) &&
        !empty($_POST["txtcargo"]) &&
        !empty($_POST["txtdni"]) &&
        !empty($_POST["txtusuario"]) &&
        !empty($_POST["txtpassword"])
    ){
        $nombre = $_POST["txtnombre"];
        $apellido = $_POST["txtapellido"];
        $dni = $_POST["txtdni"];
        $cargo = $_POST["txtcargo"];
        $usuario = $_POST["txtusuario"];
        $password = md5($_POST["txtpassword"]);
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;

        $sql=$conexion->query("SELECT count(*) as 'total' FROM empleado WHERE dni ='$dni' OR usuario='$usuario'");
        if($sql->fetch_object()->total > 0) {?>
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
            $registro = $conexion->query("INSERT INTO empleado(nombre,apellido,dni,cargo,usuario,password,is_admin) VALUES('$nombre','$apellido','$dni','$cargo','$usuario','$password','$is_admin')");
            if ($registro == true) {?>
                <script>
                    $(function notificacion(){
                        new PNotify({
                            title: "Correcto",
                            text: "Este empleado se ha registrado correctamente",
                            type: "success",
                            styling: "bootstrap3"
                        })
                    })
                </script>
            <?php } else { ?>
                <script>
                    $(function notificacion(){
                        new PNotify({
                            title: "Error",
                            text: "Error al registrar empleado",
                            type: "error",
                            styling: "bootstrap3"
                        })
                    })
                </script>
            <?php }
        }
    } else { ?>
        <script>
            $(function notificacion(){
                new PNotify({
                    title: "Error",
                    text: "Los campos están vacíos",
                    type: "error",
                    styling: "bootstrap3"
                })
            })
        </script>
    <?php  }?>

<script>
setTimeout(() => {
   window.history.replaceState(null, null, window.location.pathname); 
}, 0);
</script>

<?php }

