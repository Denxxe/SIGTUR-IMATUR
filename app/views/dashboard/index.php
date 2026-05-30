<?php require_once '../app/views/inc/header.php'; ?>

<?php
$rol  = (int)($data['rol']  ?? 0);
$anio = (int)($data['anio'] ?? date('Y'));

$rolLabel = [1=>'Administrador',2=>'RRHH',3=>'Turismo',4=>'Inventario',5=>'Recepción'][$rol] ?? 'Usuario';

function fmtMesDash(string $ym): string {
    static $m = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    $p = explode('-', $ym);
    return count($p) === 2 ? (($m[(int)$p[1]] ?? '?') . ' ' . substr($p[0], 2)) : $ym;
}

// ── KPI cards según rol ────────────────────────────────────────────────────
$kpiCards = [];

<?php
// Helper: calcula estado semáforo
// $thresholds = [good_threshold, warn_threshold], $lowerIsBetter = true invierte la lógica
function kpiSt(mixed $v, float $tGood, float $tWarn, bool $inv = false): string {
    if (!$inv) return $v >= $tGood ? 'good' : ($v >= $tWarn ? 'warning' : 'bad');
    return $v <= $tGood ? 'good' : ($v <= $tWarn ? 'warning' : 'bad');
}
?>

<?php if (in_array($rol, [1, 2])): ?>
<?php
    $alContr = ($data['kpiContratosVencen'] ?? 0) > 0;
    $kpiCards[] = ['label'=>'Empleados Activos',    'value'=>number_format($data['kpiEmpleados'] ?? 0),       'sub'=>'en nómina institucional',           'icon'=>'bi-people-fill',               'bg'=>'#3B82F6','href'=>URL_ROOT.'/empleados/index',
        'status' => kpiSt($data['kpiEmpleados']??0, 1, 1)];
    $kpiCards[] = ['label'=>'Asistencias '.date('M'),'value'=>number_format($data['kpiAsistenciaMes'] ?? 0),  'sub'=>'registros este mes',                'icon'=>'bi-calendar-check-fill',       'bg'=>'#059669','href'=>URL_ROOT.'/asistencias/index',
        'delta'  => $data['deltaAsistenciaMes']??null];
    $kpiCards[] = ['label'=>'Visitas Hoy',           'value'=>number_format($data['kpiVisitasHoy'] ?? 0),     'sub'=>'registradas en la jornada',         'icon'=>'bi-door-open-fill',             'bg'=>'#0891B2','href'=>URL_ROOT.'/visitantes/index',
        'delta'  => $data['deltaVisitasHoy']??null];
    $kpiCards[] = ['label'=>'Contratos Vencen',      'value'=>number_format($data['kpiContratosVencen'] ?? 0),'sub'=>'en los próximos 30 días',           'icon'=>'bi-person-badge-fill',         'bg'=>$alContr?'#DC2626':'#64748B','alert'=>$alContr,
        'status' => kpiSt($data['kpiContratosVencen']??0, 0, 2, true)];
?>
<?php endif; ?>

<?php if (in_array($rol, [1, 3])): ?>
<?php
    $colOcup = ($data['tasaOcupacion']??0)>=75?'#059669':(($data['tasaOcupacion']??0)>=50?'#D97706':'#DC2626');
    $colFin  = ($data['tasaFinaliz']  ??0)>=85?'#059669':(($data['tasaFinaliz']  ??0)>=70?'#D97706':'#DC2626');
    $kpiCards[] = ['label'=>'Actividades Activas',   'value'=>number_format($data['kpiActividadesActivas']??0),'sub'=>'en curso o programadas',           'icon'=>'bi-mortarboard-fill',           'bg'=>'#7C3AED','href'=>URL_ROOT.'/talleres/index',
        'status' => ($data['kpiActividadesActivas']??0) > 0 ? 'good' : 'warning'];
    $kpiCards[] = ['label'=>'Formados '.$anio,       'value'=>number_format($data['kpiFormadosAnio']??0),     'sub'=>'participantes inscritos en el año', 'icon'=>'bi-person-check-fill',         'bg'=>'#059669',
        'delta'  => $data['deltaFormados']??null];
    $kpiCards[] = ['label'=>'Rutas Operativas',      'value'=>number_format($data['kpiRutas']??0),            'sub'=>'en estado Activa',                  'icon'=>'bi-geo-alt-fill',               'bg'=>'#D97706','href'=>URL_ROOT.'/rutas/index',
        'status' => ($data['kpiRutas']??0) > 0 ? 'good' : 'warning'];
    $kpiCards[] = ['label'=>'Pasantes en Curso',     'value'=>number_format($data['kpiPasantes']??0),         'sub'=>'realizando pasantías',              'icon'=>'bi-journal-text',               'bg'=>'#0EA5E9','href'=>URL_ROOT.'/pasantes/index',
        'status' => ($data['kpiPasantes']??0) > 0 ? 'good' : 'neutral'];
    $kpiCards[] = ['label'=>'Ocupación Actividades', 'value'=>($data['tasaOcupacion']??0).'%',               'sub'=>($data['ocupInscritos']??0).' inscritos / '.($data['ocupCupos']??0).' cupos','icon'=>'bi-bar-chart-fill','bg'=>$colOcup,
        'status' => kpiSt($data['tasaOcupacion']??0, 75, 50)];
    $kpiCards[] = ['label'=>'Tasa Finalización',     'value'=>($data['tasaFinaliz']??0).'%',                 'sub'=>'actividades completadas '.$anio,    'icon'=>'bi-check-circle-fill',         'bg'=>$colFin,
        'status' => kpiSt($data['tasaFinaliz']??0, 85, 70)];
?>
<?php endif; ?>

<?php if (in_array($rol, [1, 4])): ?>
<?php
    $colDep = ($data['tasaDeprec']??0)<=10?'#059669':(($data['tasaDeprec']??0)<=15?'#D97706':'#DC2626');
    $alInv  = ($data['kpiBienesAlerta']??0) > 0;
    $kpiCards[] = ['label'=>'Bienes Activos',        'value'=>number_format($data['kpiBienes']??0),           'sub'=>'activos registrados',               'icon'=>'bi-box-seam-fill',              'bg'=>'#64748B','href'=>URL_ROOT.'/inventario/index',
        'status' => ($data['kpiBienes']??0) > 0 ? 'good' : 'warning'];
    $kpiCards[] = ['label'=>'Bienes en Alerta',      'value'=>number_format($data['kpiBienesAlerta']??0),     'sub'=>'dañados o en reparación',           'icon'=>'bi-exclamation-triangle-fill',  'bg'=>'#DC2626','alert'=>$alInv,
        'status' => kpiSt($data['kpiBienesAlerta']??0, 0, 3, true)];
    $kpiCards[] = ['label'=>'Bajas '.$anio,          'value'=>number_format($data['kpiBajasAnio']??0),        'sub'=>'bienes dados de baja este año',     'icon'=>'bi-trash3-fill',                'bg'=>'#94A3B8',
        'status' => kpiSt($data['kpiBajasAnio']??0, 0, 5, true)];
    $kpiCards[] = ['label'=>'Depreciación',          'value'=>($data['tasaDeprec']??0).'%',                  'sub'=>'del patrimonio deteriorado',        'icon'=>'bi-graph-down',                 'bg'=>$colDep,
        'status' => kpiSt($data['tasaDeprec']??0, 10, 15, true)];
?>
<?php endif; ?>

<?php if ($rol === 5): ?>
<?php
    $kpiCards[] = ['label'=>'Visitas Hoy',           'value'=>number_format($data['kpiVisitasHoy']??0),       'sub'=>'registradas en la jornada',         'icon'=>'bi-door-open-fill',             'bg'=>'#0891B2','href'=>URL_ROOT.'/visitantes/index',
        'delta'  => $data['deltaVisitasHoy']??null];
    $kpiCards[] = ['label'=>'Visitantes Semana',     'value'=>number_format($data['kpiVisitasSemana']??0),    'sub'=>'únicos en la semana actual',        'icon'=>'bi-people-fill',                'bg'=>'#7C3AED',
        'delta'  => $data['deltaVisitasSemana']??null];
    $kpiCards[] = ['label'=>'Visitantes Mes',        'value'=>number_format($data['kpiVisitantesMes']??0),    'sub'=>'únicos en el mes actual',           'icon'=>'bi-calendar-month',             'bg'=>'#059669',
        'status' => ($data['kpiVisitantesMes']??0) > 0 ? 'good' : 'neutral'];
?>
<?php endif; ?>
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow"><?php echo htmlspecialchars($rolLabel); ?> · <?php echo date('d \d\e F \d\e Y'); ?></div>
        <h1 class="page__title">Panel Principal</h1>
        <p class="page__subtitle">Métricas operativas en tiempo real — <?php echo date('H:i'); ?></p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/reportes/indicadores" class="btn-sig btn-sig--ghost">
            <i class="bi bi-graph-up-arrow"></i> Indicadores completos
        </a>
    </div>
</div>

<?php if (!empty($data['dash_error'])): ?>
<div style="background:rgba(239,68,68,.08); border:1px solid var(--danger-300); border-radius:8px; padding:var(--sp-4); margin-bottom:var(--sp-4); font-size:13px; color:var(--danger-700);">
    <i class="bi bi-exclamation-circle"></i> Algunos datos no pudieron cargarse: <?php echo htmlspecialchars($data['dash_error']); ?>
</div>
<?php endif; ?>

<?php if (!empty($data['alertas'])): ?>
<!-- Alertas ─────────────────────────────────────────────────────────────── -->
<div style="display:flex; flex-wrap:wrap; gap:var(--sp-2); margin-bottom:var(--sp-5);" class="anim-slide-up">
    <?php
    $aC = ['warning'=>['rgba(245,158,11,.1)','#D97706','#92400E'],'danger'=>['rgba(239,68,68,.1)','#DC2626','#7F1D1D'],'brand'=>['rgba(124,58,237,.08)','#7C3AED','#4C1D95'],'info'=>['rgba(8,145,178,.08)','#0891B2','#164E63']];
    foreach ($data['alertas'] as $a):
        [$bg,$bc,$tc] = $aC[$a['tipo']] ?? $aC['info'];
    ?>
    <div style="display:flex;align-items:center;gap:var(--sp-2);padding:var(--sp-2) var(--sp-4);background:<?php echo $bg;?>;border:1px solid <?php echo $bc;?>;border-radius:20px;font-size:12px;font-weight:600;color:<?php echo $tc;?>;">
        <i class="bi <?php echo $a['ico']; ?>"></i> <?php echo htmlspecialchars($a['msg']); ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- KPI Cards ────────────────────────────────────────────────────────────── -->
<?php if (!empty($kpiCards)): ?>
<div class="row g-3 mb-6 anim-slide-up">
    <?php foreach ($kpiCards as $k):
        $isAlert = !empty($k['alert']);
        $vColor  = $isAlert ? '#DC2626' : 'var(--text-primary)';
        $border  = $isAlert ? 'border-left:3px solid #DC2626;' : '';
        $hasHref = !empty($k['href']);
    ?>
    <div class="col-6 col-md-3">
        <div class="sig-card<?php echo $hasHref?' sig-card--hover':''; ?>" style="<?php echo $border; ?>">
            <?php if ($hasHref): ?><a href="<?php echo $k['href']; ?>" style="display:block;text-decoration:none;color:inherit;padding:var(--sp-4);"><?php else: ?><div style="padding:var(--sp-4);"><?php endif; ?>
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:var(--sp-3);">
                    <div style="min-width:0;flex:1;">
                        <div style="font-size:0.67rem;font-weight:600;text-transform:uppercase;letter-spacing:.07em;color:var(--text-secondary);margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($k['label']); ?></div>
                        <div style="font-size:1.75rem;font-weight:800;color:<?php echo $vColor;?>;line-height:1;margin-bottom:4px;"><?php echo $k['value']; ?></div>
                        <div style="font-size:0.69rem;color:var(--text-tertiary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($k['sub']); ?></div>
                        <?php if (!empty($k['delta'])): $d=$k['delta']; ?>
                        <div style="font-size:10px;font-weight:700;color:<?php echo $d['color'];?>;margin-top:3px;white-space:nowrap;">
                            <?php echo $d['arrow']; ?> <?php echo $d['pct']; ?>% <span style="font-weight:400;color:var(--text-tertiary);"><?php echo htmlspecialchars($d['label']); ?></span>
                        </div>
                        <?php elseif (!empty($k['status']) && $k['status'] !== 'neutral'):
                            $sMap = ['good'=>['#059669','bi-check-circle-fill','Óptimo'],'warning'=>['#D97706','bi-dash-circle-fill','Atención'],'bad'=>['#DC2626','bi-x-circle-fill','Alerta']];
                            [$sCol,$sIco,$sTxt] = $sMap[$k['status']] ?? ['#64748B','bi-dash','—'];
                        ?>
                        <div style="font-size:10px;font-weight:700;color:<?php echo $sCol;?>;margin-top:3px;white-space:nowrap;">
                            <i class="bi <?php echo $sIco;?>" style="font-size:9px;"></i> <?php echo $sTxt; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div style="flex-shrink:0;width:42px;height:42px;border-radius:10px;background:<?php echo $k['bg'];?>;display:flex;align-items:center;justify-content:center;">
                        <i class="bi <?php echo $k['icon']; ?>" style="font-size:1.15rem;color:white;"></i>
                    </div>
                </div>
            <?php if ($hasHref): ?></a><?php else: ?></div><?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php
// Flags de disponibilidad por rol
$hasActividades = in_array($rol,[1,3]) && !empty($data['talleresPorMes']);
$hasInv         = in_array($rol,[1,4]) && !empty($data['invPorCondicion']);
$hasAsist       = in_array($rol,[1,2]) && !empty($data['asistenciaPorMes']);
$hasEmp         = in_array($rol,[1,2]) && !empty($data['empPorDepto']);
$hasVisitas     = in_array($rol,[1,5]) && !empty($data['visitasPorDia']);
?>

<!-- Gráficas Fila 1: métricas de actividad principal ──────────────────────── -->
<?php if ($hasActividades || $hasInv): ?>
<div class="row g-4 mb-4 anim-slide-up">
    <?php if ($hasActividades): ?>
    <div class="<?php echo $hasInv ? 'col-md-8' : 'col-12'; ?>">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-graph-up-arrow" style="color:#7C3AED;"></i> Actividades de Formación — últimos 6 meses</div>
                <span style="font-size:11px;color:var(--text-tertiary);"><?php echo ($data['kpiActividadesActivas']??0); ?> activas ahora</span>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);"><div id="chartTalleresMes"></div></div>
        </div>
    </div>
    <?php endif; ?>
    <?php if ($hasInv): ?>
    <div class="<?php echo $hasActividades ? 'col-md-4' : 'col-md-6 offset-md-3'; ?>">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-clipboard-check-fill" style="color:#D97706;"></i> Estado Físico del Patrimonio</div>
                <span style="font-size:11px;color:var(--text-tertiary);"><?php echo ($data['kpiBienes']??0); ?> bienes</span>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);"><div id="chartInvCondicion"></div></div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Gráficas Fila 2: personal ─────────────────────────────────────────────── -->
<?php if ($hasAsist || $hasEmp): ?>
<div class="row g-4 mb-4 anim-slide-up">
    <?php if ($hasAsist): ?>
    <div class="col-md-6">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-calendar-check-fill" style="color:#F59E0B;"></i> Asistencia del Personal — últimos 4 meses</div>
                <span style="font-size:11px;color:var(--text-tertiary);"><?php echo ($data['kpiAsistenciaMes']??0); ?> registros este mes</span>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);"><div id="chartAsistencia"></div></div>
        </div>
    </div>
    <?php endif; ?>
    <?php if ($hasEmp): ?>
    <div class="col-md-6">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-bar-chart-horizontal-fill" style="color:#3B82F6;"></i> Empleados por Departamento</div>
                <span style="font-size:11px;color:var(--text-tertiary);"><?php echo ($data['kpiEmpleados']??0); ?> empleados activos</span>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);"><div id="chartEmpDepto"></div></div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Gráficas Fila 3: flujo de visitas ─────────────────────────────────────── -->
<?php if ($hasVisitas): ?>
<div class="row g-4 mb-4 anim-slide-up">
    <div class="col-12">
        <div class="sig-card">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-door-open-fill" style="color:#0891B2;"></i> Flujo de Visitas — últimos 14 días</div>
                <span style="font-size:11px;color:var(--text-tertiary);"><?php echo ($data['kpiVisitasSemana']??0); ?> únicos esta semana</span>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);"><div id="chartVisitas"></div></div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Acciones rápidas + CTA ──────────────────────────────────────────────── -->
<div class="row g-4 mb-6 anim-slide-up">
    <div class="col-md-8">
        <div class="sig-card h-100">
            <div class="sig-card__head"><div class="sig-card__title"><i class="bi bi-lightning-charge-fill" style="color:var(--brand-500);"></i> Acciones Rápidas</div></div>
            <div class="sig-card__body" style="padding:var(--sp-5);">
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(148px,1fr));gap:var(--sp-3);">
                    <?php
                    $acciones = [];
                    if (in_array($rol,[1,2]))    $acciones[] = ['Registrar Asistencia','bi-calendar-plus',          URL_ROOT.'/asistencias/index'];
                    if (in_array($rol,[1,2,5]))  $acciones[] = ['Registrar Visitante', 'bi-person-plus-fill',       URL_ROOT.'/visitantes/index'];
                    if (in_array($rol,[1,3]))    $acciones[] = ['Nueva Actividad',     'bi-calendar-event',         URL_ROOT.'/talleres/index'];
                    if (in_array($rol,[1,3]))    $acciones[] = ['Rutas Turísticas',    'bi-geo-alt-fill',           URL_ROOT.'/rutas/index'];
                    if (in_array($rol,[1,3]))    $acciones[] = ['Pasantes',            'bi-journal-text',           URL_ROOT.'/pasantes/index'];
                    if (in_array($rol,[1,3]))    $acciones[] = ['Instituciones',       'bi-building',               URL_ROOT.'/institucionesexternas/index'];
                    if (in_array($rol,[1,4]))    $acciones[] = ['Inventario',          'bi-box-seam-fill',          URL_ROOT.'/inventario/index'];
                    if (in_array($rol,[1,2]))    $acciones[] = ['Personal',            'bi-people-fill',            URL_ROOT.'/empleados/index'];
                    if (in_array($rol,[1,2,3,4]))$acciones[] = ['Reportes',            'bi-file-earmark-bar-graph', URL_ROOT.'/reportes/index'];
                    if ($rol === 1)              $acciones[] = ['Configuración',       'bi-gear-fill',              URL_ROOT.'/config/index'];
                    foreach ($acciones as [$lbl,$ico,$href]):
                    ?>
                    <a href="<?php echo $href; ?>" style="display:flex;flex-direction:column;align-items:center;gap:var(--sp-2);padding:var(--sp-4);background:var(--bg-muted-subtle);border-radius:10px;border:1px solid var(--border-subtle);text-decoration:none;color:var(--text-primary);font-size:12px;font-weight:600;text-align:center;transition:box-shadow .15s,border-color .15s;"
                       onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,.08)';this.style.borderColor='var(--brand-300)';"
                       onmouseout="this.style.boxShadow='';this.style.borderColor='var(--border-subtle)';">
                        <i class="bi <?php echo $ico; ?>" style="font-size:1.5rem;color:var(--brand-500);"></i>
                        <?php echo htmlspecialchars($lbl); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="sig-card h-100" style="background:linear-gradient(135deg,var(--brand-600) 0%,var(--brand-800) 100%);border:none;">
            <div class="sig-card__body" style="padding:var(--sp-8);display:flex;flex-direction:column;justify-content:space-between;height:100%;">
                <div>
                    <i class="bi bi-graph-up-arrow" style="font-size:2.5rem;color:rgba(255,255,255,.7);display:block;margin-bottom:var(--sp-4);"></i>
                    <h3 style="font-size:1.1rem;font-weight:700;color:white;margin:0 0 var(--sp-2);">Indicadores Completos</h3>
                    <p style="font-size:13px;color:rgba(255,255,255,.75);margin:0 0 var(--sp-6);line-height:1.6;">
                        Análisis profundo con demografía, cobertura territorial, tendencias históricas y KPIs de eficiencia operativa.
                    </p>
                </div>
                <a href="<?php echo URL_ROOT; ?>/reportes/indicadores" class="btn-sig"
                   style="background:rgba(255,255,255,.15);color:white;border:1px solid rgba(255,255,255,.3);backdrop-filter:blur(4px);justify-content:center;">
                    <i class="bi bi-arrow-right-circle"></i> Ver análisis completo
                </a>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($data['feedActividad'])): ?>
<!-- Feed de actividad reciente — solo Admin ─────────────────────────────── -->
<?php
$tablaNames = [
    'talleres'             => 'actividad formativa',
    'participantes_taller' => 'inscripción en actividad',
    'rutas'                => 'ruta turística',
    'participantes_ruta'   => 'participante en ruta',
    'empleados'            => 'empleado',
    'personas'             => 'persona registrada',
    'inventario'           => 'bien de inventario',
    'pasantes'             => 'pasante',
    'visitas'              => 'visita institucional',
    'visitantes'           => 'visitante',
    'asistencias'          => 'registro de asistencia',
    'taller_evidencias'    => 'evidencia de actividad',
    'taller_informes'      => 'informe de actividad',
    'rutas'                => 'ruta turística',
    'instituciones_externas'=> 'institución externa',
    'usuarios'             => 'usuario del sistema',
    'departamentos'        => 'departamento',
    'ubicaciones_formacion'=> 'sede de formación',
];
$opConfig = [
    'INSERT' => ['ico'=>'bi-plus-circle-fill',  'color'=>'#059669', 'verb'=>'registró'],
    'UPDATE' => ['ico'=>'bi-pencil-fill',        'color'=>'#D97706', 'verb'=>'actualizó'],
    'DELETE' => ['ico'=>'bi-trash3-fill',        'color'=>'#DC2626', 'verb'=>'eliminó'],
];
?>
<div class="sig-card mb-6 anim-slide-up">
    <div class="sig-card__head">
        <div class="sig-card__title">
            <i class="bi bi-activity" style="color:var(--brand-500);"></i> Actividad Reciente del Sistema
        </div>
        <span style="font-size:12px;color:var(--text-tertiary);">Últimos <?php echo count($data['feedActividad']); ?> registros</span>
    </div>
    <div class="sig-card__body" style="padding:0;">
        <?php foreach ($data['feedActividad'] as $i => $f):
            $tablaNombre = $tablaNames[$f->tabla_afectada] ?? str_replace('_', ' ', $f->tabla_afectada);
            $op = $opConfig[$f->operacion] ?? ['ico'=>'bi-dot','color'=>'var(--text-tertiary)','verb'=>'modificó'];
            $diff = time() - strtotime($f->fecha ?? 'now');
            if      ($diff < 60)    $timeAgo = 'hace ' . $diff . 's';
            elseif  ($diff < 3600)  $timeAgo = 'hace ' . floor($diff/60) . ' min';
            elseif  ($diff < 86400) $timeAgo = 'hace ' . floor($diff/3600) . ' h';
            else                    $timeAgo = 'hace ' . floor($diff/86400) . ' d';
        ?>
        <div style="display:flex;align-items:center;gap:var(--sp-3);padding:var(--sp-3) var(--sp-5);<?php echo $i>0?'border-top:1px solid var(--border-subtle);':''; ?>">
            <div style="width:30px;height:30px;border-radius:50%;background:<?php echo $op['color'];?>1a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi <?php echo $op['ico']; ?>" style="font-size:0.75rem;color:<?php echo $op['color']; ?>;"></i>
            </div>
            <div style="flex:1;min-width:0;font-size:13px;color:var(--text-primary);">
                <strong><?php echo htmlspecialchars($f->username ?? 'Sistema'); ?></strong>
                <span style="color:var(--text-secondary);"> <?php echo $op['verb']; ?> </span>
                <em style="color:var(--text-secondary);font-style:normal;"><?php echo htmlspecialchars($tablaNombre); ?></em>
                <?php if ($f->record_id): ?>
                    <span style="color:var(--text-tertiary);font-size:11px;"> #<?php echo $f->record_id; ?></span>
                <?php endif; ?>
            </div>
            <span style="font-size:11px;color:var(--text-tertiary);white-space:nowrap;flex-shrink:0;"><?php echo $timeAgo; ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php
// ── Preparar datos para JS ────────────────────────────────────────────────
$lblTall=[]; $valTall=[];
foreach ($data['talleresPorMes']??[] as $t){ $lblTall[]=fmtMesDash($t->mes??''); $valTall[]=(int)($t->total??0); }

$lblAsist=[]; $valAsist=[];
foreach ($data['asistenciaPorMes']??[] as $a){ $lblAsist[]=fmtMesDash($a->mes??''); $valAsist[]=(int)($a->total??0); }

$lblEmp=[]; $valEmp=[];
foreach ($data['empPorDepto']??[] as $e){ $lblEmp[]=$e->departamento??'N/A'; $valEmp[]=(int)($e->total??0); }

$lblVis=[]; $valVis=[];
foreach ($data['visitasPorDia']??[] as $v){
    $p=explode('-',$v->dia??''); $lblVis[]=isset($p[2],$p[1])?$p[2].'/'.$p[1]:'N/A'; $valVis[]=(int)($v->total??0);
}

$lblInv=[]; $valInvPct=[]; $totInv=0;
foreach ($data['invPorCondicion']??[] as $c) $totInv+=(int)($c->total??0);
foreach ($data['invPorCondicion']??[] as $c){
    $lblInv[]=$c->condicion??'N/A';
    $valInvPct[]=$totInv>0?round(((int)($c->total??0)/$totInv)*100):0;
}
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    var tp  = getComputedStyle(document.body).getPropertyValue('--text-primary').trim();
    var ts  = getComputedStyle(document.body).getPropertyValue('--text-secondary').trim();
    var bs  = getComputedStyle(document.body).getPropertyValue('--border-subtle').trim();
    var theme  = { mode: isDark ? 'dark' : 'light' };
    var grid   = { borderColor: bs, strokeDashArray: 4 };
    var noData = { text: 'Sin datos', style: { color: ts, fontSize: '12px' } };
    var axLbl  = { style: { colors: tp, fontSize: '11px' } };
    var palette= ['#3B82F6','#10B981','#F59E0B','#8B5CF6','#EC4899','#06B6D4','#F97316','#64748B'];

<?php if (in_array($rol,[1,3]) && !empty($lblTall)): ?>
    // Área — tipo 'monotoneCubic' cuando hay pocos puntos para evitar spikes
    new ApexCharts(document.querySelector('#chartTalleresMes'), {
        chart: { type:'area', height:240, background:'transparent', toolbar:{show:false} },
        series:[{ name:'Actividades', data:<?php echo json_encode($valTall); ?> }],
        xaxis:{ categories:<?php echo json_encode($lblTall); ?>, labels:axLbl, axisBorder:{show:false}, tickPlacement:'on' },
        yaxis:{ labels:{style:{colors:tp}, formatter:function(v){return Math.round(v);}}, min:0, forceNiceScale:true },
        colors:['#7C3AED'],
        stroke:{ curve:<?php echo count($valTall) <= 2 ? "'straight'" : "'smooth'"; ?>, width:3 },
        fill:{type:'gradient',gradient:{shadeIntensity:1,opacityFrom:.30,opacityTo:.02,stops:[0,100]}},
        markers:{size:<?php echo count($valTall) <= 3 ? 6 : 4; ?>,colors:['#7C3AED'],strokeWidth:2,strokeColors:isDark?'#1e1e2d':'#fff'},
        grid, theme, noData
    }).render();
<?php endif; ?>

<?php if (in_array($rol,[1,4]) && !empty($lblInv)): ?>
    new ApexCharts(document.querySelector('#chartInvCondicion'), {
        chart:{ type:'radialBar', height:240, background:'transparent' },
        series:<?php echo json_encode($valInvPct); ?>,
        labels:<?php echo json_encode($lblInv); ?>,
        colors:['#10B981','#3B82F6','#F59E0B','#EF4444','#64748B'],
        plotOptions:{ radialBar:{
            offsetY: -10,
            dataLabels:{
                name:{fontSize:'10px',color:tp},
                value:{fontSize:'15px',fontWeight:'800',color:tp,formatter:function(v){return v+'%';}},
                total:{show:true,label:'BIENES',color:ts,fontSize:'10px',fontWeight:'700'}
            },
            hollow:{size:'30%'}, track:{background:bs}
        }},
        legend:{show:true,position:'bottom',labels:{colors:tp},fontSize:'11px',itemMargin:{horizontal:6}},
        theme, noData
    }).render();
<?php endif; ?>

<?php if (in_array($rol,[1,2]) && !empty($lblAsist)): ?>
    new ApexCharts(document.querySelector('#chartAsistencia'), {
        chart:{ type:'bar', height:220, background:'transparent', toolbar:{show:false} },
        series:[{ name:'Registros', data:<?php echo json_encode($valAsist); ?> }],
        xaxis:{ categories:<?php echo json_encode($lblAsist); ?>, labels:axLbl, axisBorder:{show:false} },
        yaxis:{ labels:{style:{colors:tp}, formatter:function(v){return Math.round(v);}}, min:0, forceNiceScale:true },
        plotOptions:{ bar:{borderRadius:6, columnWidth:<?php echo count($valAsist) <= 2 ? "'35%'" : "'50%'"; ?>} },
        colors:['#F59E0B'],
        dataLabels:{enabled:true,style:{fontWeight:'700',fontSize:'11px'}},
        grid, theme, noData
    }).render();
<?php endif; ?>

<?php if (in_array($rol,[1,2]) && !empty($lblEmp)): ?>
    <?php $maxEmp = max(array_merge($valEmp,[1])); ?>
    new ApexCharts(document.querySelector('#chartEmpDepto'), {
        chart:{ type:'bar', height:220, background:'transparent', toolbar:{show:false} },
        series:[{ name:'Empleados', data:<?php echo json_encode($valEmp); ?> }],
        xaxis:{
            categories:<?php echo json_encode($lblEmp); ?>,
            labels:{style:{colors:tp,fontSize:'11px'}},
            min: 0,
            tickAmount: <?php echo $maxEmp; ?>,
            labels:{ style:{colors:tp,fontSize:'11px'}, formatter:function(v){return Number.isInteger(+v)?+v:'';} }
        },
        yaxis:{ labels:{style:{colors:tp}} },
        plotOptions:{ bar:{horizontal:true,borderRadius:5,barHeight:'50%',distributed:true} },
        colors:palette,
        dataLabels:{enabled:true,style:{fontWeight:'700',fontSize:'11px'}},
        legend:{show:false},
        grid, theme, noData
    }).render();
<?php endif; ?>

<?php if (in_array($rol,[1,5]) && !empty($lblVis)): ?>
    new ApexCharts(document.querySelector('#chartVisitas'), {
        chart:{ type:'bar', height:160, background:'transparent', toolbar:{show:false} },
        series:[{ name:'Visitas', data:<?php echo json_encode($valVis); ?> }],
        xaxis:{ categories:<?php echo json_encode($lblVis); ?>, labels:{style:{colors:tp,fontSize:'10px'}}, axisBorder:{show:false} },
        yaxis:{ labels:{style:{colors:tp}, formatter:function(v){return Math.round(v);}}, min:0, forceNiceScale:true },
        plotOptions:{ bar:{borderRadius:4,columnWidth:'55%'} },
        colors:['#0891B2'],
        dataLabels:{enabled:false},
        grid, theme, noData
    }).render();
<?php endif; ?>

});
</script>

<style>
.sig-card--hover { transition: box-shadow .15s; }
.sig-card--hover:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); }
</style>

<?php require_once '../app/views/inc/footer.php'; ?>
