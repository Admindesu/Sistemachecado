<?php
if (!empty($_POST['btnregistrar'])) {
    if (!empty($_POST['txtid']) && !empty($_POST['txtnombre'])) {
        $nombre = $_POST['txtnombre'];
        $id = $_POST['txtid'];
        $verificarNombre = $conexion->query("select count(*) as 'total' from cargo where nombre = '$nombre' and id_cargo != '$id'");
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
        $sql=$conexion->query("UPDATE cargo SET nombre='$nombre' WHERE id_cargo='$id'");
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