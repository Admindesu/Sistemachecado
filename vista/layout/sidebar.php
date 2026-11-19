<?php
/*
 * Menú lateral. El orden de $menu es el orden en pantalla; la opción de la página
 * actual se resalta sola (las subpáginas cuentan como su sección).
 */
$menu = [
    ['dashboard.php', 'dashboard.png', 'DASHBOARD', []],
    ['inicio.php', 'programar.png', 'ASISTENCIAS', []],
    ['empleado.php', 'team.png', 'EMPLEADOS', ['registro_empleado.php', 'importar_hikvision.php']],
    ['organigrama.php', 'team.png', 'ORGANIGRAMA', ['registro_cargo.php', 'registro_direccion.php', 'registro_subsecretaria.php']],
    ['horarios.php', 'programar.png', 'HORARIOS', []],
    ['justificaciones.php', 'programar.png', 'JUSTIFICACIONES', []],
    ['dias_inhabiles.php', 'programar.png', 'DÍAS INHÁBILES', []],
    ['acerca.php', 'info.png', 'ACERCA DE', ['Graficos.php']],
];
$pagina_actual = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
<style>
.side-menu-list a.activo.actual {
    background: rgb(171, 11, 61) !important;
}
</style>
<nav class="side-menu">
    <ul class="side-menu-list p-0">
        <?php foreach ($menu as [$pagina, $icono, $texto, $subpaginas]):
            $actual = $pagina_actual === $pagina || in_array($pagina_actual, $subpaginas, true); ?>
        <li class="red">
            <a href="<?= $pagina ?>" class="activo<?= $actual ? ' actual' : '' ?>"<?= $actual ? ' aria-current="page"' : '' ?>>
                <img src="../public/img-inicio/<?= $icono ?>" class="img-inicio" alt="">
                <span class="lbl"><?= $texto ?></span>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</nav>
