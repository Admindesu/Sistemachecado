<?php
if(!empty($_GET['id'])){
    $id=$_GET['id'];
    $sql=$conexion->query("delete from usuario where id_usuario=$id");
    if ($sql==true) {?>
        <script>
            $(function notificacion(){
                new PNotify({
                    title: "Correcto",
                    text: "Este usuario se ha eliminado correctamente",
                    type: "success",
                    styling: "bootstrap3"
            })
            })
              </script>
        <?php }else { ?>
<script>
            $(function notificacion(){
                new PNotify({
                    title: "Incorrecto",
                    text: "Error al eliminar usuario",
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