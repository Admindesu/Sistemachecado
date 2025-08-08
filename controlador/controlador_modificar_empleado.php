<?php 
// Asegúrate de incluir la conexión a la base de datos antes de usar $conexion
include_once("../modelo/conexion.php");

if (!empty($_POST["btnmodificar"])) {
if (!empty($_POST["txtid"]) && !empty($_POST["txtnombre"]) && !empty($_POST["txtapellido"]) && !empty($_POST["txtcargo"])){
$id=$_POST["txtid"];
    $nombre = $_POST["txtnombre"];
    $apellido = $_POST["txtapellido"];
    $cargo = $_POST["txtcargo"];
    $sql=$conexion-> query("update empleado set nombre='$nombre',apellido='$apellido',cargo='$cargo' where id_empleado=$id");
 if($sql==true){ ?>
  <script>
            $(function notificacion(){
                new PNotify({
                    title: "Correcto",
                    text: "Este usuario se modifico correctamente",
                    type: "success",
                    styling: "bootstrap3"
            })
            })
        </script>

<?php } else{ ?>
  <script>
            $(function notificacion(){
                new PNotify({
                    title: "Error",
                    text: "Error al modificar empleado",
                    type: "error",
                    styling: "bootstrap3"
            })
            })
        </script>

<?php } 
} else{ ?>
  <script>
            $(function notificacion(){
                new PNotify({
                    title: "Error",
                    text: "Campos vacios",
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