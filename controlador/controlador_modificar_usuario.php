<?php 
// Asegúrate de incluir la conexión a la base de datos antes de usar $conexion
include_once("../modelo/conexion.php");

if (!empty($_POST['btnregistrar'])) {
if (!empty($_POST["txtnombre"]) && !empty($_POST["txtapellido"]) && !empty($_POST["txtusuario"])){
$nombre = $_POST["txtnombre"];
    $apellido = $_POST["txtapellido"];
    $usuario = $_POST["txtusuario"];
    $id=$_POST["txtid"];
     $sql=$conexion->query("select count(*) as 'total' from usuario where usuario='$usuario' and id_usuario!=$id");
    if ($sql->fetch_object()->total>0) {?>
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

   <?php } else {
    // Si se proporciona una nueva contraseña, actualizarla
        if (!empty($_POST["txtpassword"])) {
            $password = password_hash($_POST["txtpassword"], PASSWORD_DEFAULT);
            $sql = $conexion->query("UPDATE usuario SET nombre='$nombre', apellido='$apellido', usuario='$usuario', password='$password' WHERE id_usuario = $id");
        } else {
            $sql = $conexion->query("UPDATE usuario SET nombre='$nombre', apellido='$apellido', usuario='$usuario' WHERE id_usuario = $id");
        }
        
        if ($sql == 1) { ?>
 <script>
            $(function notificacion(){
                new PNotify({
                    title: "Correcto",
                    text: "Este usuario se ha modificado correctamente",
                    type: "success",
                    styling: "bootstrap3"
            })
            })
        </script>
<?php } else { ?>
<script>
            $(function notificacion(){
                new PNotify({
                    title: "Incorrecto",
                    text: "Error",
                    type: "error",
                    styling: "bootstrap3"
            })
            })
        </script>
<?php }
}
}else{

}?>
<script>

setTimeout(() => {
   window.history.replaceState(null, null, window.location.pathname); 
}, 0);

</script>
<?php }
?>