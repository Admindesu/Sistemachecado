<?php
if (!empty($_POST['btnregistrar'])) {
   if (!empty($_POST['txtnombre'])) {
    $nombre = $_POST['txtnombre'];
    $verificarNombre = $conexion->query("select count(*) as 'total' from cargo where nombre='$nombre'");
    if ($verificarNombre->fetch_object()->total > 0) { ?>
          <script>
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
        $sql= $conexion->query("INSERT INTO cargo(nombre) VALUES ('$nombre')");
        if ($sql==true) { ?>
                <script>
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

setTimeout(() => {
   window.history.replaceState(null, null, window.location.pathname); 
}, 0);

</script>
<?php }

?>