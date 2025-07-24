<?php
if(!empty($_POST["btnregistrar"])){
if(!empty($_POST["txtnombre"]) && !empty($_POST["txtapellido"]) && !empty($_POST["txtcargo"])&& !empty($_POST["txtdni"])){
    $nombre = $_POST["txtnombre"];
    $apellido = $_POST["txtapellido"];
    $dni = $_POST["txtdni"];
    $cargo = $_POST["txtcargo"];

    $sql=$conexion->query("select count(*) as 'total' from empleado where dni ='$dni'");
if($sql->fetch_object()->total>0) {?>
     <script>
            $(function notificacion(){
                new PNotify({
                    title: "Error",
                    text: "Este empleado ya existe",
                    type: "error",
                    styling: "bootstrap3"
            })
            })
        </script>
<?php } else{
$registro=$conexion->query("insert into empleado(nombre,apellido,dni,cargo)values('$nombre','$apellido','$dni','$cargo')");
if ($registro==true) {?>
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
<?php }else { ?>
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

