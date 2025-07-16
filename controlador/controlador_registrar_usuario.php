<?php
if(!empty($_POST["btnregistrar"])){
if(!empty($_POST["txtnombre"]) && !empty($_POST["txtapellido"]) && !empty($_POST["txtusuario"]) && !empty($_POST["txtpassword"])){
    $nombre = $_POST["txtnombre"];
    $apellido = $_POST["txtapellido"];
    $usuario = $_POST["txtusuario"];
    $password = md5($_POST["txtpassword"]);

    $sql=$conexion->query("select count(*) as 'total' from usuario where usuario='$usuario'");
if($sql->fetch_object()->total>0) {?>
     <script>
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
$registro=$conexion->query("insert into usuario(nombre,apellido,usuario,password)values('$nombre','$apellido','$usuario','$password')");
if ($registro==true) {?>
 <script>
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

setTimeout(() => {
   window.history.replaceState(null, null, window.location.pathname); 
}, 0);

</script>

<?php }

