<?php
require_once __DIR__ . '/../controlador/auth.php';
requerir_admin();
include "../modelo/conexion.php";
require_once __DIR__ . '/../modelo/dashboard.php';

$ahora = new DateTimeImmutable();
// Días inhábiles recientes, para que "Ayer" se salte también los festivos
$inhabiles_recientes = cargar_dias_inhabiles($conexion, $ahora->modify('-40 days'), $ahora);
$rango = rango_dashboard($_GET, $ahora, fn($d) => dia_inhabil($inhabiles_recientes, $d) !== null);
$r = resumen_asistencia($conexion, $rango['desde'], $rango['hasta'], $ahora);
$un_dia = $rango['desde'] == $rango['hasta'];
$t = $r['totales'];

function fecha_es(DateTimeImmutable $d, bool $con_dia = true): string
{
    static $dias = [1 => 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
    static $meses = [1 => 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    $texto = (int) $d->format('j') . ' ' . $meses[(int) $d->format('n')] . ' ' . $d->format('Y');
    return $con_dia ? $dias[(int) $d->format('N')] . ' ' . $texto : $texto;
}

$hoy = $ahora->setTime(0, 0);
if ($un_dia) {
    $titulo_rango = ucfirst(fecha_es($rango['desde']));
    if ($rango['desde'] == $hoy) $titulo_rango = 'Hoy, ' . fecha_es($rango['desde']);
    elseif ($rango['desde'] == $hoy->modify('-1 day')) $titulo_rango = 'Ayer, ' . fecha_es($rango['desde']);
} else {
    $titulo_rango = fecha_es($rango['desde'], false) . ' – ' . fecha_es($rango['hasta'], false);
}

// Días que van en la gráfica: laborables, o fines de semana con algún registro
$dias_grafica = array_filter($r['por_dia'], fn($d) => $d['laborable'] || $d['PUNTUAL'] + $d['RETARDO'] + $d['FALTA'] + $d['JUSTIFICADO'] > 0);
$etiquetas_dia = [];
foreach (array_keys($dias_grafica) as $dia) {
    $d = new DateTimeImmutable($dia);
    $etiquetas_dia[] = ['lun', 'mar', 'mié', 'jue', 'vie', 'sáb', 'dom'][(int) $d->format('N') - 1] . ' ' . $d->format('j');
}

// Para un solo día: filas agrupadas por estado
$grupos = ['FALTA' => [], 'RETARDO' => [], 'PENDIENTE' => [], 'JUSTIFICADO' => [], 'PUNTUAL' => []];
if ($un_dia) {
    foreach ($r['detalle'] as $fila) {
        $grupos[$fila['estado']][] = $fila;
    }
    // Retardos y puntuales por hora de llegada; faltas por nombre
    usort($grupos['RETARDO'], fn($a, $b) => strcmp($b['entrada'], $a['entrada']));
    usort($grupos['PUNTUAL'], fn($a, $b) => strcmp($a['entrada'], $b['entrada']));
}

$estados = [
    'PUNTUAL'   => ['Puntuales', 'fa-check-circle', 'var(--estado-bien)'],
    'RETARDO'   => ['Retardos', 'fa-clock', 'var(--estado-aviso)'],
    'FALTA'     => ['Faltas', 'fa-times-circle', 'var(--estado-critico)'],
    'PENDIENTE' => ['Pendientes', 'fa-hourglass-half', 'var(--estado-neutro)'],
    'JUSTIFICADO' => ['Justificados', 'fa-file-signature', 'var(--justificado)'],
];
$base = $r['evaluados'] + $t['JUSTIFICADO'];   // personas (o empleado × día) que debían asistir
$pct = fn($n) => $base ? round(100 * $n / $base) . '%' : '–';
$enlace_justificar = fn($f) => 'justificaciones.php?' . http_build_query(['dni' => $f['dni'], 'fecha' => $f['dia'], 'tipo' => $f['tipo_sugerido']]);
$hora = fn($h) => $h ? date('h:i A', strtotime($h)) : '—';
$rangos = ['ayer' => 'Ayer', 'hoy' => 'Hoy', 'semana' => 'Semana', 'mes' => 'Mes'];
?>
<style>
ul li:nth-child(6) .activo {
    background: rgb(171, 11, 61) !important;
}
:root {
    --estado-bien: #0ca30c;
    --estado-aviso: #fab219;
    --estado-critico: #d03b3b;
    --estado-serio: #ec835a;
    --estado-neutro: #8d8c85;
    --justificado: #3a6fc4;
    --tinta: #0b0b0b;
    --tinta-2: #52514e;
    --tinta-3: #77766f;
    --superficie: #ffffff;
    --linea: #e1e0d9;
}
.dash .filtros { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; margin-bottom: 8px; }
.dash .rangos { display: inline-flex; border: 1px solid #c3c2b7; border-radius: 6px; overflow: hidden; background: var(--superficie); }
.dash .rangos a { padding: 7px 16px; color: var(--tinta); text-decoration: none; border-left: 1px solid #c3c2b7; font-size: 14px; }
.dash .rangos a:first-child { border-left: 0; }
.dash .rangos a:hover { background: #f2f1ed; }
.dash .rangos a.activo-rango { background: rgb(171, 11, 61); color: #fff; font-weight: 600; }
.dash .btn-aplicar { border: 1px solid #c3c2b7; background: var(--superficie); color: var(--tinta); padding: 4px 14px; border-radius: 6px; font-size: 14px; }
.dash .btn-aplicar.activo-rango { background: rgb(171, 11, 61); border-color: rgb(171, 11, 61); color: #fff; font-weight: 600; }
.dash .filtros form { display: flex; flex-wrap: wrap; gap: 8px; align-items: flex-end; }
.dash .filtros label { font-size: 12px; color: var(--tinta-2); margin: 0 0 2px; display: block; }
.dash .subtitulo { color: var(--tinta-2); margin-bottom: 20px; }
.dash .subtitulo strong { color: var(--tinta); font-size: 18px; }
.dash .kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; margin-bottom: 20px; }
.dash .kpi { background: var(--superficie); border: 1px solid var(--linea); border-left: 4px solid var(--color); border-radius: 8px; padding: 14px 16px; }
.dash .kpi .etiqueta { color: var(--tinta-2); font-size: 13px; display: flex; align-items: center; gap: 6px; }
.dash .kpi .etiqueta i { color: var(--color); font-size: 15px; }
.dash .kpi .valor { color: var(--tinta); font-size: 32px; font-weight: 600; line-height: 1.2; font-variant-numeric: tabular-nums; }
.dash .kpi .nota { color: var(--tinta-3); font-size: 12px; }
.dash .tarjeta { background: var(--superficie); border: 1px solid var(--linea); border-radius: 8px; padding: 16px; margin-bottom: 20px; }
.dash .tarjeta h5 { font-size: 15px; color: var(--tinta); margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
.dash .barra { display: flex; height: 28px; gap: 2px; border-radius: 4px; overflow: hidden; margin-bottom: 8px; }
.dash .barra span { background: var(--color); color: #fff; font-size: 12px; font-weight: 600; display: flex; align-items: center; justify-content: center; min-width: 2px; }
.dash .barra span.oscuro { color: #0b0b0b; }
.dash .leyenda { display: flex; flex-wrap: wrap; gap: 16px; font-size: 13px; color: var(--tinta-2); }
.dash .leyenda i { display: inline-block; width: 10px; height: 10px; border-radius: 2px; background: var(--color); margin-right: 6px; }
.dash .grupos { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; }
.dash .grupo h5 i { color: var(--color); }
.dash .grupo h5 .cuenta { margin-left: auto; color: var(--tinta-2); font-weight: 400; }
.dash .grupo ul { list-style: none; padding: 0; margin: 0; max-height: 420px; overflow-y: auto; }
.dash .grupo li { padding: 8px 0; border-top: 1px solid var(--linea); display: flex; justify-content: space-between; gap: 8px; }
.dash .grupo li .quien { color: var(--tinta); }
.dash .grupo li .donde { color: var(--tinta-3); font-size: 12px; }
.dash .grupo li .horas { color: var(--tinta-2); font-size: 13px; text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
.dash .grupo .vacio { color: var(--tinta-3); font-size: 13px; padding: 8px 0; }
.dash .aviso-salida { color: var(--tinta-2); font-size: 12px; }
.dash .aviso-salida i { color: var(--estado-serio); }
.dash .grafica { position: relative; height: 320px; }
.dash td.num { text-align: right; font-variant-numeric: tabular-nums; }
.dash .justificar { font-size: 12px; white-space: nowrap; }
.dash .inhabiles { background: var(--superficie); border: 1px solid var(--linea); border-left: 4px solid var(--estado-neutro); border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; color: var(--tinta-2); font-size: 14px; }
</style>
<?php require('./layout/topbar.php'); ?>
<?php require('./layout/sidebar.php'); ?>

<div class="page-content dash">
    <div class="container-fluid">
        <h4 class="text-center text-secondary">Dashboard de asistencia</h4>

        <div class="filtros">
            <nav class="rangos" aria-label="Rango de fechas">
                <?php foreach ($rangos as $clave => $texto): ?>
                    <a href="dashboard.php?rango=<?= $clave ?>"
                       class="<?= $rango['clave'] === $clave ? 'activo-rango' : '' ?>"
                       <?= $rango['clave'] === $clave ? 'aria-current="true"' : '' ?>><?= $texto ?></a>
                <?php endforeach; ?>
            </nav>
            <form method="GET" action="dashboard.php">
                <input type="hidden" name="rango" value="personalizado">
                <div>
                    <label for="desde">Desde</label>
                    <input type="date" id="desde" name="desde" class="form-control form-control-sm" required
                           max="<?= e($hoy->format('Y-m-d')) ?>" value="<?= e($rango['desde']->format('Y-m-d')) ?>">
                </div>
                <div>
                    <label for="hasta">Hasta</label>
                    <input type="date" id="hasta" name="hasta" class="form-control form-control-sm" required
                           max="<?= e($hoy->format('Y-m-d')) ?>" value="<?= e($rango['hasta']->format('Y-m-d')) ?>">
                </div>
                <button type="submit" class="btn-aplicar <?= $rango['clave'] === 'personalizado' ? 'activo-rango' : '' ?>">Aplicar</button>
            </form>
        </div>

        <p class="subtitulo">
            <strong><?= e($titulo_rango) ?></strong><br>
            <?php if ($rango['clave'] === 'ayer' && $rango['desde'] != $hoy->modify('-1 day')): ?>
                Último día hábil ·
            <?php endif; ?>
            <?= $r['dias_laborables'] ?> <?= $r['dias_laborables'] === 1 ? 'día hábil' : 'días hábiles' ?>
            · <?= count($r['por_empleado']) ?> empleados
        </p>

        <?php if ($r['dias_inhabiles']): ?>
            <div class="inhabiles">
                <i class="fas fa-calendar-times" aria-hidden="true"></i>
                <?= count($r['dias_inhabiles']) === 1 && $un_dia ? 'Día inhábil' : 'Días inhábiles en el rango' ?>:
                <?= e(implode(', ', array_map(fn($d, $desc) => fecha_es(new DateTimeImmutable($d), false) . " ($desc)", array_keys($r['dias_inhabiles']), $r['dias_inhabiles']))) ?>
                · no generan faltas. <a href="dias_inhabiles.php">Administrar</a>
            </div>
        <?php endif; ?>

        <?php if (!$r['evaluados'] && !$t['PENDIENTE'] && !$t['JUSTIFICADO']): ?>
            <div class="alert alert-info">No hay días hábiles ni registros en este rango.</div>
        <?php else: ?>

        <!-- Indicadores -->
        <div class="kpis">
            <?php foreach (['PUNTUAL', 'RETARDO', 'FALTA'] as $k): [$txt, $icono, $color] = $estados[$k]; ?>
                <div class="kpi" style="--color: <?= $color ?>">
                    <div class="etiqueta"><i class="fas <?= $icono ?>" aria-hidden="true"></i><?= $txt ?></div>
                    <div class="valor"><?= $t[$k] ?></div>
                    <div class="nota"><?= $pct($t[$k]) ?> <?= $un_dia ? 'de ' . $base . ' empleados' : 'del total' ?></div>
                </div>
            <?php endforeach; ?>
            <div class="kpi" style="--color: var(--justificado)">
                <div class="etiqueta"><i class="fas fa-file-signature" aria-hidden="true"></i>Justificados</div>
                <div class="valor"><?= $t['JUSTIFICADO'] ?></div>
                <div class="nota">con permiso registrado · <a href="justificaciones.php">ver</a></div>
            </div>
            <?php if ($t['PENDIENTE']): ?>
                <div class="kpi" style="--color: var(--estado-neutro)">
                    <div class="etiqueta"><i class="fas fa-hourglass-half" aria-hidden="true"></i>Pendientes</div>
                    <div class="valor"><?= $t['PENDIENTE'] ?></div>
                    <div class="nota">aún en su horario de entrada</div>
                </div>
            <?php endif; ?>
            <div class="kpi" style="--color: var(--estado-serio)">
                <div class="etiqueta"><i class="fas fa-sign-out-alt" aria-hidden="true"></i>Sin salida</div>
                <div class="valor"><?= $t['SIN_SALIDA'] ?></div>
                <div class="nota">entraron y no checaron salida</div>
            </div>
            <div class="kpi" style="--color: var(--tinta-2)">
                <div class="etiqueta"><i class="fas fa-percentage" aria-hidden="true"></i>Puntualidad</div>
                <div class="valor"><?= $r['puntualidad'] === null ? '–' : $r['puntualidad'] . '%' ?></div>
                <div class="nota">puntuales, sin contar justificados</div>
            </div>
        </div>

        <?php if ($un_dia): ?>
            <!-- Distribución del día -->
            <div class="tarjeta">
                <?php $total_barra = $t['PUNTUAL'] + $t['RETARDO'] + $t['FALTA'] + $t['PENDIENTE'] + $t['JUSTIFICADO']; ?>
                <div class="barra" role="img" aria-label="<?= e("Puntuales {$t['PUNTUAL']}, retardos {$t['RETARDO']}, faltas {$t['FALTA']}, pendientes {$t['PENDIENTE']}, justificados {$t['JUSTIFICADO']}") ?>">
                    <?php foreach ($estados as $k => [$txt, $icono, $color]): if (!$t[$k]) continue; ?>
                        <span style="--color: <?= $color ?>; flex: <?= $t[$k] ?>" class="<?= $k === 'RETARDO' ? 'oscuro' : '' ?>"
                              title="<?= e("$txt: {$t[$k]}") ?>"><?= 100 * $t[$k] / $total_barra >= 8 ? $t[$k] : '' ?></span>
                    <?php endforeach; ?>
                </div>
                <div class="leyenda">
                    <?php foreach ($estados as $k => [$txt, $icono, $color]): if (!$t[$k]) continue; ?>
                        <span><i style="--color: <?= $color ?>"></i><?= $txt ?> · <?= $t[$k] ?></span>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Quién faltó, llegó tarde o a tiempo -->
            <div class="grupos">
                <?php foreach ($grupos as $k => $filas): if (in_array($k, ['PENDIENTE', 'JUSTIFICADO'], true) && !$filas) continue; [$txt, $icono, $color] = $estados[$k]; ?>
                    <div class="tarjeta grupo" style="--color: <?= $color ?>">
                        <h5><i class="fas <?= $icono ?>" aria-hidden="true"></i><?= $txt ?><span class="cuenta"><?= count($filas) ?></span></h5>
                        <?php if (!$filas): ?>
                            <div class="vacio">Nadie</div>
                        <?php else: ?>
                            <ul>
                                <?php foreach ($filas as $f): ?>
                                    <li>
                                        <div>
                                            <div class="quien"><?= e($f['nombre']) ?></div>
                                            <div class="donde"><?= e($f['direccion']) ?><?= $f['motivo'] ? ' · ' . e($f['motivo']) : '' ?></div>
                                            <?php if ($f['tipo_sugerido']): ?>
                                                <a class="justificar" href="<?= e($enlace_justificar($f)) ?>"><i class="fas fa-file-signature" aria-hidden="true"></i> Justificar <?= $f['tipo_sugerido'] === 'dia_completo' ? 'falta' : $f['tipo_sugerido'] ?></a>
                                            <?php endif; ?>
                                        </div>
                                        <div class="horas">
                                            <?php if ($f['entrada']): ?>
                                                Entrada <?= e($hora($f['entrada'])) ?><br>
                                                <?php if ($f['salida']): ?>
                                                    Salida <?= e($hora($f['salida'])) ?>
                                                <?php elseif ($f['sin_salida']): ?>
                                                    <span class="aviso-salida"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> Sin salida</span>
                                                <?php elseif ($f['salida_justificada']): ?>
                                                    <span class="aviso-salida">Salida justificada</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <!-- Evolución por día -->
            <div class="tarjeta">
                <h5>Asistencia por día</h5>
                <div class="grafica"><canvas id="graficaDias" aria-label="Asistencia por día: puntuales, retardos y faltas" role="img"></canvas></div>
            </div>

            <!-- Resumen por empleado -->
            <div class="tarjeta">
                <h5>Por empleado <small class="text-muted">(primero los que más faltan)</small></h5>
                <div class="table-responsive">
                    <table class="table table-sm table-hover w-100" id="example" data-order='[[4, "desc"], [3, "desc"], [0, "asc"]]'>
                        <thead><tr>
                            <th>Empleado</th><th>Dirección</th>
                            <th class="text-right"><i class="fas fa-check-circle" style="color: var(--estado-bien)" aria-hidden="true"></i> Puntual</th>
                            <th class="text-right"><i class="fas fa-clock" style="color: var(--estado-aviso)" aria-hidden="true"></i> Retardo</th>
                            <th class="text-right"><i class="fas fa-times-circle" style="color: var(--estado-critico)" aria-hidden="true"></i> Falta</th>
                            <th class="text-right"><i class="fas fa-file-signature" style="color: var(--justificado)" aria-hidden="true"></i> Justificado</th>
                            <th class="text-right"><i class="fas fa-sign-out-alt" style="color: var(--estado-serio)" aria-hidden="true"></i> Sin salida</th>
                            <th class="text-right">Puntualidad</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($r['por_empleado'] as $p): $ev = $p['PUNTUAL'] + $p['RETARDO'] + $p['FALTA']; ?>
                            <tr>
                                <td><?= e($p['nombre']) ?></td>
                                <td><?= e($p['direccion']) ?></td>
                                <td class="num"><?= $p['PUNTUAL'] ?></td>
                                <td class="num"><?= $p['RETARDO'] ?></td>
                                <td class="num"><?= $p['FALTA'] ?></td>
                                <td class="num"><?= $p['JUSTIFICADO'] ?></td>
                                <td class="num"><?= $p['SIN_SALIDA'] ?></td>
                                <td class="num" data-order="<?= $ev ? round(100 * $p['PUNTUAL'] / $ev) : -1 ?>"><?= $ev ? round(100 * $p['PUNTUAL'] / $ev) . '%' : '–' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require('./layout/footer.php'); ?>

<?php if (!$un_dia && ($r['evaluados'] || $t['PENDIENTE'] || $t['JUSTIFICADO'])): ?>
<script>
(function () {
    var estilo = getComputedStyle(document.documentElement);
    var color = function (v) { return estilo.getPropertyValue(v).trim(); };
    var datos = <?= json_encode([
        'etiquetas' => $etiquetas_dia,
        'fechas' => array_keys($dias_grafica),
        'PUNTUAL' => array_values(array_column($dias_grafica, 'PUNTUAL')),
        'RETARDO' => array_values(array_column($dias_grafica, 'RETARDO')),
        'FALTA' => array_values(array_column($dias_grafica, 'FALTA')),
        'PENDIENTE' => array_values(array_column($dias_grafica, 'PENDIENTE')),
        'JUSTIFICADO' => array_values(array_column($dias_grafica, 'JUSTIFICADO')),
    ], JSON_UNESCAPED_UNICODE) ?>;
    var serie = function (nombre, clave, variable) {
        return {
            label: nombre, data: datos[clave], backgroundColor: color(variable),
            borderColor: color('--superficie'), borderWidth: 2, borderRadius: 4, borderSkipped: false,
            maxBarThickness: 48
        };
    };
    var series = [serie('Puntuales', 'PUNTUAL', '--estado-bien'), serie('Retardos', 'RETARDO', '--estado-aviso'),
                  serie('Faltas', 'FALTA', '--estado-critico')];
    if (datos.JUSTIFICADO.some(function (n) { return n > 0; })) series.push(serie('Justificados', 'JUSTIFICADO', '--justificado'));
    if (datos.PENDIENTE.some(function (n) { return n > 0; })) series.push(serie('Pendientes', 'PENDIENTE', '--estado-neutro'));

    new Chart(document.getElementById('graficaDias'), {
        type: 'bar',
        data: { labels: datos.etiquetas, datasets: series },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', align: 'start', labels: { boxWidth: 10, boxHeight: 10, color: color('--tinta-2') } },
                tooltip: {
                    callbacks: {
                        title: function (items) { return items[0].label + ' · ' + datos.fechas[items[0].dataIndex]; },
                        footer: function (items) {
                            var total = items.reduce(function (s, i) { return s + i.parsed.y; }, 0);
                            return 'Total: ' + total;
                        }
                    }
                }
            },
            scales: {
                x: { stacked: true, grid: { display: false }, ticks: { color: color('--tinta-2') } },
                y: { stacked: true, beginAtZero: true, ticks: { precision: 0, color: color('--tinta-2') },
                     grid: { color: color('--linea') }, border: { display: false } }
            }
        }
    });
})();
</script>
<?php endif; ?>
