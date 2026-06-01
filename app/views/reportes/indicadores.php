<?php require_once '../app/views/inc/header.php'; ?>

<?php
function fmtMes(string $ym): string {
    static $m = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    $p = explode('-', $ym);
    if (count($p) < 2) return $ym ?: 'N/A';
    return ($m[(int)$p[1]] ?? '?') . ' ' . substr($p[0], 2);
}
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/reportes/index" style="color:inherit;text-decoration:none;">Reportes</a> · Dashboard Global
        </div>
        <h1 class="page__title"><?php echo htmlspecialchars($data['titulo'] ?? ''); ?></h1>
        <p class="page__subtitle">Métricas operativas en tiempo real — <?php echo date('d/m/Y H:i'); ?></p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/reportes/index" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <button class="btn-sig btn-sig--primary" onclick="window.print()">
            <i class="bi bi-printer"></i> Imprimir
        </button>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════
     RESUMEN EJECUTIVO — KPI por Área
════════════════════════════════════════════════════════════════════════ -->
<?php
// Helper para renderizar una tarjeta KPI de resumen
function kpiCard(array $k): void {
    $isAlert  = !empty($k['alert']) && $k['value'] > 0;
    $valColor = $isAlert ? '#DC2626' : 'var(--text-primary)';
    $border   = $isAlert ? 'border-left:3px solid #DC2626;' : '';
    echo '<div class="sig-card" style="' . $border . '">';
    echo '<div class="sig-card__body" style="padding:var(--sp-4);">';
    echo '<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:var(--sp-3);">';
    echo '<div style="min-width:0;flex:1;">';
    echo '<div style="font-size:0.67rem;font-weight:600;text-transform:uppercase;letter-spacing:0.07em;color:var(--text-secondary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:4px;">' . htmlspecialchars($k['label']) . '</div>';
    echo '<div style="font-size:1.875rem;font-weight:800;color:' . $valColor . ';line-height:1;margin-bottom:4px;">' . number_format($k['value']) . '</div>';
    echo '<div style="font-size:0.69rem;color:var(--text-tertiary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' . htmlspecialchars($k['sub']) . '</div>';
    echo '</div>';
    echo '<div style="flex-shrink:0;width:42px;height:42px;border-radius:10px;background:' . $k['bg'] . ';display:flex;align-items:center;justify-content:center;">';
    echo '<i class="bi ' . $k['icon'] . '" style="font-size:1.15rem;color:white;"></i></div>';
    echo '</div></div></div>';
}

// Helper separador de área (mini-versión)
function areaSep(string $label, string $color, string $icon): void {
    echo '<div style="display:flex;align-items:center;gap:var(--sp-3);margin-bottom:var(--sp-3);" class="anim-slide-up">';
    echo '<div style="width:3px;height:16px;border-radius:2px;background:' . $color . ';flex-shrink:0;"></div>';
    echo '<span style="font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-secondary);">';
    echo '<i class="bi ' . $icon . '" style="color:' . $color . ';margin-right:3px;"></i>' . htmlspecialchars($label);
    echo '</span><div style="flex:1;height:1px;background:var(--border-subtle);"></div></div>';
}
?>

<!-- Fila 1: Recursos Humanos + Recepción ──────────────────────────────── -->
<?php areaSep('Recursos Humanos y Recepción', '#3B82F6', 'bi-people'); ?>
<div class="row g-3 mb-5 anim-slide-up">
    <div class="col-6 col-md-3"><?php kpiCard(['label'=>'Empleados Activos',  'value'=>$data['kpiEmpleados'],       'sub'=>'en nómina institucional',   'icon'=>'bi-people-fill',     'bg'=>'#3B82F6']); ?></div>
    <div class="col-6 col-md-3"><?php kpiCard(['label'=>'Visitas Hoy',         'value'=>$data['kpiVisitasHoy'],      'sub'=>'registradas esta jornada',  'icon'=>'bi-door-open-fill',  'bg'=>'#0891B2']); ?></div>
    <div class="col-6 col-md-3"><?php kpiCard(['label'=>'Pasantes en Curso',   'value'=>$data['kpiPasantesEnCurso'], 'sub'=>'realizando pasantías',      'icon'=>'bi-journal-text',    'bg'=>'#0EA5E9']); ?></div>
    <div class="col-6 col-md-3"><?php kpiCard(['label'=>'Bienes Activos',      'value'=>$data['kpiBienesActivos'],   'sub'=>'activos registrados',       'icon'=>'bi-box-seam-fill',   'bg'=>'#64748B']); ?></div>
</div>

<!-- Fila 2: Formación y Turismo ───────────────────────────────────────── -->
<?php areaSep('Formación y Turismo', '#7C3AED', 'bi-mortarboard'); ?>
<div class="row g-3 mb-5 anim-slide-up">
    <div class="col-6 col-md-3"><?php kpiCard(['label'=>'Actividades Activas', 'value'=>$data['kpiActividadesActivas'], 'sub'=>'en curso o programadas',         'icon'=>'bi-mortarboard-fill',  'bg'=>'#7C3AED']); ?></div>
    <div class="col-6 col-md-3"><?php kpiCard(['label'=>'Formados '.date('Y'), 'value'=>$data['kpiFormadosAnio'],       'sub'=>'inscripciones activas en el año','icon'=>'bi-person-check-fill', 'bg'=>'#059669']); ?></div>
    <div class="col-6 col-md-3"><?php kpiCard(['label'=>'Rutas Operativas',    'value'=>$data['kpiRutasActivas'],       'sub'=>'en estado Activa',               'icon'=>'bi-geo-alt-fill',      'bg'=>'#D97706']); ?></div>
    <div class="col-6 col-md-3"><?php kpiCard(['label'=>'Bienes en Alerta',    'value'=>$data['kpiBienesAlerta'],       'sub'=>'dañados o en reparación',        'icon'=>'bi-exclamation-triangle-fill','bg'=>'#DC2626','alert'=>true]); ?></div>
</div>

<?php
// ── Cálculo de ratios — Eficiencia Operativa ──────────────────────────────
$totalInscritos = (int)($data['kpiOcupacion']->total_inscritos    ?? 0);
$totalCupos     = (int)($data['kpiOcupacion']->total_cupos        ?? 0);
$tasaOcupacion  = $totalCupos > 0 ? round(($totalInscritos / $totalCupos) * 100, 1) : 0;
$colOcup        = $tasaOcupacion >= 75 ? '#059669' : ($tasaOcupacion >= 50 ? '#D97706' : '#DC2626');

$totalActs   = (int)($data['kpiEficienciaActs']->total       ?? 0);
$finalizadas = (int)($data['kpiEficienciaActs']->finalizadas  ?? 0);
$canceladas  = (int)($data['kpiEficienciaActs']->canceladas   ?? 0);
$tasaFinaliz = $totalActs > 0 ? round(($finalizadas / $totalActs) * 100, 1) : 0;
$colFinaliz  = $tasaFinaliz >= 85 ? '#059669' : ($tasaFinaliz >= 70 ? '#D97706' : '#DC2626');
$tasaCancel  = $totalActs > 0 ? round(($canceladas  / $totalActs) * 100, 1) : 0;
$colCancel   = $tasaCancel <= 5  ? '#059669' : ($tasaCancel <= 10 ? '#D97706' : '#DC2626');

$totalBienes  = (int)($data['kpiDepreciacion']->total        ?? 0);
$deteriorados = (int)($data['kpiDepreciacion']->deteriorados  ?? 0);
$tasaDeprec   = $totalBienes > 0 ? round(($deteriorados / $totalBienes) * 100, 1) : 0;
$colDeprec    = $tasaDeprec <= 10 ? '#059669' : ($tasaDeprec <= 15 ? '#D97706' : '#DC2626');

// ── Metas (para el semáforo institucional) ────────────────────────────────
$semMetaTall  = (int)($data['metaTalleres'] ?? 0);
$semTallAnio  = (int)($data['talleresAnio'] ?? 0);
$semPctTall   = $semMetaTall > 0 ? min(100, round(($semTallAnio / $semMetaTall) * 100)) : null;
$semMetaRut   = (int)($data['metaRutas'] ?? 0);
$semRutAnio   = (int)($data['rutasAnio'] ?? 0);
$semPctRut    = $semMetaRut > 0 ? min(100, round(($semRutAnio / $semMetaRut) * 100)) : null;

// ── Helper de semáforo: traduce un estado a color, ícono y veredicto ──────
function semaforo(string $estado): array {
    $map = [
        'ok'      => ['#059669', 'bi-check-circle-fill',        'Óptimo',    '🟢'],
        'warn'    => ['#D97706', 'bi-exclamation-triangle-fill', 'Atención',  '🟡'],
        'crit'    => ['#DC2626', 'bi-x-octagon-fill',            'Crítico',   '🔴'],
        'nodata'  => ['#64748B', 'bi-dash-circle',               'Sin datos', '⚪'],
        'nometa'  => ['#64748B', 'bi-flag',                      'Sin meta',  '⚪'],
    ];
    return $map[$estado] ?? $map['nodata'];
}

// ── Helper: pill de veredicto según el color del umbral ya calculado ──────
function verdictPill(string $color): string {
    if ($color === '#059669') { $txt = 'Óptimo';   $ico = 'bi-check-circle-fill'; }
    elseif ($color === '#D97706') { $txt = 'Aceptable'; $ico = 'bi-exclamation-triangle-fill'; }
    else { $txt = 'Crítico'; $ico = 'bi-x-octagon-fill'; }
    return '<span style="display:inline-flex;align-items:center;gap:3px;font-size:9px;font-weight:800;'
         . 'text-transform:uppercase;letter-spacing:.03em;color:' . $color . ';background:' . $color . '18;'
         . 'padding:2px 7px;border-radius:20px;"><i class="bi ' . $ico . '"></i>' . $txt . '</span>';
}

// ── Evaluación de cada área estratégica ───────────────────────────────────
$semTiles = [];

// 1. Eficacia formativa (tasa de finalización)
$semTiles[] = [
    'area'   => 'Eficacia Formativa',
    'icon'   => 'bi-mortarboard-fill',
    'estado' => $totalActs === 0 ? 'nodata' : ($tasaFinaliz >= 85 ? 'ok' : ($tasaFinaliz >= 70 ? 'warn' : 'crit')),
    'metric' => $totalActs === 0 ? '— sin actividades' : $tasaFinaliz . '% finalizadas',
    'hint'   => 'Meta ≥ 85%',
];
// 2. Ocupación de cupos
$semTiles[] = [
    'area'   => 'Ocupación de Cupos',
    'icon'   => 'bi-people-fill',
    'estado' => $totalCupos === 0 ? 'nodata' : ($tasaOcupacion >= 75 ? 'ok' : ($tasaOcupacion >= 50 ? 'warn' : 'crit')),
    'metric' => $totalCupos === 0 ? '— sin cupos definidos' : $tasaOcupacion . '% ocupación',
    'hint'   => 'Meta ≥ 75%',
];
// 3. Control de cancelaciones
$semTiles[] = [
    'area'   => 'Control de Cancelaciones',
    'icon'   => 'bi-x-circle-fill',
    'estado' => $totalActs === 0 ? 'nodata' : ($tasaCancel <= 5 ? 'ok' : ($tasaCancel <= 10 ? 'warn' : 'crit')),
    'metric' => $totalActs === 0 ? '— sin actividades' : $tasaCancel . '% canceladas',
    'hint'   => 'Umbral < 10%',
];
// 4. Meta anual de formación
$semTiles[] = [
    'area'   => 'Meta de Formación',
    'icon'   => 'bi-flag-fill',
    'estado' => $semPctTall === null ? 'nometa' : ($semPctTall >= 100 ? 'ok' : ($semPctTall >= 70 ? 'warn' : 'crit')),
    'metric' => $semPctTall === null ? '— meta no definida' : $semPctTall . '% (' . $semTallAnio . '/' . $semMetaTall . ')',
    'hint'   => 'Actividades ejecutadas',
];
// 5. Meta anual de rutas
$semTiles[] = [
    'area'   => 'Meta de Rutas',
    'icon'   => 'bi-geo-alt-fill',
    'estado' => $semPctRut === null ? 'nometa' : ($semPctRut >= 100 ? 'ok' : ($semPctRut >= 70 ? 'warn' : 'crit')),
    'metric' => $semPctRut === null ? '— meta no definida' : $semPctRut . '% (' . $semRutAnio . '/' . $semMetaRut . ')',
    'hint'   => 'Rutas ejecutadas',
];
// 6. Salud del patrimonio (inverso de la depreciación)
$semTiles[] = [
    'area'   => 'Salud del Patrimonio',
    'icon'   => 'bi-box-seam-fill',
    'estado' => $totalBienes === 0 ? 'nodata' : ($tasaDeprec <= 10 ? 'ok' : ($tasaDeprec <= 15 ? 'warn' : 'crit')),
    'metric' => $totalBienes === 0 ? '— sin bienes' : $tasaDeprec . '% deteriorado',
    'hint'   => 'Umbral < 15%',
];

// Conteo global para el resumen del encabezado
$semOk = $semWarn = $semCrit = 0;
foreach ($semTiles as $st) {
    if ($st['estado'] === 'ok') $semOk++;
    elseif ($st['estado'] === 'warn') $semWarn++;
    elseif ($st['estado'] === 'crit') $semCrit++;
}
?>

<!-- ══════════════════════════════════════════════════════════════════════
     SEMÁFORO DE GESTIÓN INSTITUCIONAL — lectura ejecutiva de un vistazo
════════════════════════════════════════════════════════════════════════ -->
<div style="display:flex;align-items:center;gap:var(--sp-3);margin:var(--sp-6) 0 var(--sp-4) 0;flex-wrap:wrap;">
    <div style="width:4px;height:20px;border-radius:2px;background:#0F172A;flex-shrink:0;"></div>
    <span style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-secondary);">Semáforo de Gestión Institucional — <?php echo $data['anioActual']; ?></span>
    <div style="flex:1;height:1px;background:var(--border-subtle);min-width:20px;"></div>
    <div style="display:flex;gap:var(--sp-2);flex-shrink:0;">
        <span class="sig-badge" style="background:#05966922;color:#059669;font-weight:700;"><i class="bi bi-check-circle-fill"></i> <?php echo $semOk; ?> óptimo</span>
        <span class="sig-badge" style="background:#D9770622;color:#D97706;font-weight:700;"><i class="bi bi-exclamation-triangle-fill"></i> <?php echo $semWarn; ?> atención</span>
        <span class="sig-badge" style="background:#DC262622;color:#DC2626;font-weight:700;"><i class="bi bi-x-octagon-fill"></i> <?php echo $semCrit; ?> crítico</span>
    </div>
</div>

<div class="row g-3 mb-4 anim-slide-up">
    <?php foreach ($semTiles as $st):
        [$sColor, $sIcon, $sLabel, $sDot] = semaforo($st['estado']);
    ?>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="sig-card h-100" style="border-top:3px solid <?php echo $sColor; ?>;position:relative;overflow:hidden;">
            <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-3);">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-2);">
                    <i class="bi <?php echo $st['icon']; ?>" style="font-size:1.05rem;color:<?php echo $sColor; ?>;"></i>
                    <span style="position:relative;display:inline-flex;align-items:center;justify-content:center;width:14px;height:14px;">
                        <span style="width:10px;height:10px;border-radius:50%;background:<?php echo $sColor; ?>;box-shadow:0 0 0 3px <?php echo $sColor; ?>33;"></span>
                    </span>
                </div>
                <div style="font-size:0.66rem;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:var(--text-secondary);line-height:1.25;min-height:2.3em;">
                    <?php echo htmlspecialchars($st['area']); ?>
                </div>
                <div style="display:flex;align-items:center;gap:5px;margin:6px 0 2px 0;">
                    <i class="bi <?php echo $sIcon; ?>" style="color:<?php echo $sColor; ?>;font-size:0.9rem;"></i>
                    <span style="font-size:0.95rem;font-weight:800;color:<?php echo $sColor; ?>;"><?php echo $sLabel; ?></span>
                </div>
                <div style="font-size:0.7rem;color:var(--text-primary);font-weight:600;"><?php echo htmlspecialchars($st['metric']); ?></div>
                <div style="font-size:0.62rem;color:var(--text-tertiary);margin-top:2px;"><?php echo htmlspecialchars($st['hint']); ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ══════════════════════════════════════════════════════════════════════
     SECCIÓN: EFICIENCIA OPERATIVA (por área)
════════════════════════════════════════════════════════════════════════ -->

<!-- Sub-área: Formación ─────────────────────────────────────────────── -->
<?php areaSep('Eficiencia · Formación — ' . $data['anioActual'], '#7C3AED', 'bi-mortarboard'); ?>
<div class="row g-3 mb-4 anim-slide-up">
    <div class="col-6 col-md-4">
        <div class="sig-card" style="border-bottom:3px solid <?php echo $colOcup; ?>;">
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:var(--sp-2);">
                    <div style="min-width:0;flex:1;">
                        <div style="font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.07em;color:var(--text-secondary);margin-bottom:4px;">Ocupación de Actividades</div>
                        <div style="font-size:1.875rem;font-weight:800;color:<?php echo $colOcup; ?>;line-height:1;margin-bottom:4px;"><?php echo $tasaOcupacion; ?>%</div>
                        <div style="font-size:0.65rem;color:var(--text-tertiary);"><?php echo $totalInscritos; ?> inscritos / <?php echo $totalCupos; ?> cupos</div>
                        <div style="margin-top:5px;"><?php echo verdictPill($colOcup); ?></div>
                    </div>
                    <div style="flex-shrink:0;width:38px;height:38px;border-radius:8px;background:<?php echo $colOcup; ?>22;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-people-fill" style="font-size:1rem;color:<?php echo $colOcup; ?>;"></i>
                    </div>
                </div>
                <div style="margin-top:var(--sp-3);">
                    <div style="height:4px;background:var(--bg-muted);border-radius:2px;overflow:hidden;">
                        <div style="height:100%;width:<?php echo min(100,$tasaOcupacion); ?>%;background:<?php echo $colOcup; ?>;border-radius:2px;"></div>
                    </div>
                    <div style="text-align:right;margin-top:2px;"><span style="font-size:9px;color:var(--text-tertiary);">Meta: ≥ 75%</span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="sig-card" style="border-bottom:3px solid <?php echo $colFinaliz; ?>;">
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:var(--sp-2);">
                    <div style="min-width:0;flex:1;">
                        <div style="font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.07em;color:var(--text-secondary);margin-bottom:4px;">Finalización de Actividades</div>
                        <div style="font-size:1.875rem;font-weight:800;color:<?php echo $colFinaliz; ?>;line-height:1;margin-bottom:4px;"><?php echo $tasaFinaliz; ?>%</div>
                        <div style="font-size:0.65rem;color:var(--text-tertiary);"><?php echo $finalizadas; ?> finalizadas de <?php echo $totalActs; ?></div>
                        <div style="margin-top:5px;"><?php echo verdictPill($colFinaliz); ?></div>
                    </div>
                    <div style="flex-shrink:0;width:38px;height:38px;border-radius:8px;background:<?php echo $colFinaliz; ?>22;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-check-circle-fill" style="font-size:1rem;color:<?php echo $colFinaliz; ?>;"></i>
                    </div>
                </div>
                <div style="margin-top:var(--sp-3);">
                    <div style="height:4px;background:var(--bg-muted);border-radius:2px;overflow:hidden;">
                        <div style="height:100%;width:<?php echo min(100,$tasaFinaliz); ?>%;background:<?php echo $colFinaliz; ?>;border-radius:2px;"></div>
                    </div>
                    <div style="text-align:right;margin-top:2px;"><span style="font-size:9px;color:var(--text-tertiary);">Meta: ≥ 85%</span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="sig-card" style="border-bottom:3px solid <?php echo $colCancel; ?>;">
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:var(--sp-2);">
                    <div style="min-width:0;flex:1;">
                        <div style="font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.07em;color:var(--text-secondary);margin-bottom:4px;">Tasa de Cancelación</div>
                        <div style="font-size:1.875rem;font-weight:800;color:<?php echo $colCancel; ?>;line-height:1;margin-bottom:4px;"><?php echo $tasaCancel; ?>%</div>
                        <div style="font-size:0.65rem;color:var(--text-tertiary);"><?php echo $canceladas; ?> canceladas de <?php echo $totalActs; ?></div>
                        <div style="margin-top:5px;"><?php echo verdictPill($colCancel); ?></div>
                    </div>
                    <div style="flex-shrink:0;width:38px;height:38px;border-radius:8px;background:<?php echo $colCancel; ?>22;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-x-circle-fill" style="font-size:1rem;color:<?php echo $colCancel; ?>;"></i>
                    </div>
                </div>
                <div style="margin-top:var(--sp-3);">
                    <div style="height:4px;background:var(--bg-muted);border-radius:2px;overflow:hidden;">
                        <div style="height:100%;width:<?php echo min(100,$tasaCancel); ?>%;background:<?php echo $colCancel; ?>;border-radius:2px;"></div>
                    </div>
                    <div style="text-align:right;margin-top:2px;"><span style="font-size:9px;color:var(--text-tertiary);">Umbral: &lt; 10%</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sub-área: Inventario y Patrimonio ───────────────────────────────── -->
<?php areaSep('Eficiencia · Inventario y Patrimonio', '#64748B', 'bi-box-seam'); ?>
<div class="row g-3 mb-4 anim-slide-up">
    <div class="col-6 col-md-4">
        <div class="sig-card" style="border-bottom:3px solid <?php echo $colDeprec; ?>;">
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:var(--sp-2);">
                    <div style="min-width:0;flex:1;">
                        <div style="font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.07em;color:var(--text-secondary);margin-bottom:4px;">Depreciación del Patrimonio</div>
                        <div style="font-size:1.875rem;font-weight:800;color:<?php echo $colDeprec; ?>;line-height:1;margin-bottom:4px;"><?php echo $tasaDeprec; ?>%</div>
                        <div style="font-size:0.65rem;color:var(--text-tertiary);"><?php echo $deteriorados; ?> deteriorados de <?php echo $totalBienes; ?></div>
                        <div style="margin-top:5px;"><?php echo verdictPill($colDeprec); ?></div>
                    </div>
                    <div style="flex-shrink:0;width:38px;height:38px;border-radius:8px;background:<?php echo $colDeprec; ?>22;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-exclamation-circle-fill" style="font-size:1rem;color:<?php echo $colDeprec; ?>;"></i>
                    </div>
                </div>
                <div style="margin-top:var(--sp-3);">
                    <div style="height:4px;background:var(--bg-muted);border-radius:2px;overflow:hidden;">
                        <div style="height:100%;width:<?php echo min(100,$tasaDeprec); ?>%;background:<?php echo $colDeprec; ?>;border-radius:2px;"></div>
                    </div>
                    <div style="text-align:right;margin-top:2px;"><span style="font-size:9px;color:var(--text-tertiary);">Umbral: &lt; 15%</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════
     INDICADORES DERIVADOS DE PRODUCTIVIDAD
════════════════════════════════════════════════════════════════════════ -->
<?php
$totalActsYear = (int)($data['kpiEficienciaActs']->total ?? 0);
$avgPartAct    = $totalActsYear > 0 ? round((int)$data['kpiFormadosAnio'] / $totalActsYear, 1) : 0;
$numCap        = count($data['capacitadores'] ?? []);
$avgPorCap     = $numCap > 0 ? round((int)$data['kpiFormadosAnio'] / $numCap) : 0;
$sumVis = 0; foreach ($data['visitasPorDia'] ?? [] as $v) $sumVis += (int)($v->total ?? 0);
$avgVisDia = round($sumVis / 14, 1);
$ctD       = $data['coberturaTerrForma'] ?? null;
$cobPctD   = (int)($ctD->total_municipios ?? 0) > 0
           ? round(((int)($ctD->municipios_cubiertos ?? 0) / (int)$ctD->total_municipios) * 100) : 0;

$derivados = [
    ['Participantes / Actividad', $avgPartAct,         'promedio de inscritos por actividad', 'bi-people',          '#7C3AED'],
    ['Formados / Capacitador',    number_format($avgPorCap), 'carga formativa por facilitador',     'bi-person-badge',    '#059669'],
    ['Visitas / Día',             $avgVisDia,          'promedio diario (últimos 14 días)',   'bi-door-open',       '#0891B2'],
    ['Cobertura Territorial',     $cobPctD . '%',      'municipios del estado con actividad', 'bi-geo-alt',         '#D97706'],
];
?>
<div style="display:flex;align-items:center;gap:var(--sp-3);margin:var(--sp-6) 0 var(--sp-4) 0;">
    <div style="width:4px;height:20px;border-radius:2px;background:#0F172A;flex-shrink:0;"></div>
    <span style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-secondary);">Indicadores Derivados de Productividad — <?php echo $data['anioActual']; ?></span>
    <div style="flex:1;height:1px;background:var(--border-subtle);"></div>
</div>
<div class="row g-3 mb-6 anim-slide-up">
    <?php foreach ($derivados as [$lbl, $val, $sub, $ico, $col]): ?>
    <div class="col-6 col-md-3">
        <div class="sig-card h-100">
            <div class="sig-card__body" style="padding:var(--sp-4);display:flex;align-items:center;gap:var(--sp-3);">
                <div style="flex-shrink:0;width:44px;height:44px;border-radius:10px;background:<?php echo $col; ?>18;display:flex;align-items:center;justify-content:center;">
                    <i class="bi <?php echo $ico; ?>" style="font-size:1.25rem;color:<?php echo $col; ?>;"></i>
                </div>
                <div style="min-width:0;">
                    <div style="font-size:1.5rem;font-weight:900;color:<?php echo $col; ?>;line-height:1;"><?php echo $val; ?></div>
                    <div style="font-size:0.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--text-secondary);margin:3px 0 1px;"><?php echo htmlspecialchars($lbl); ?></div>
                    <div style="font-size:0.62rem;color:var(--text-tertiary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($sub); ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- PROP-P01: Tipo de Contrato — RRHH -->
<?php areaSep('Recursos Humanos', '#3B82F6', 'bi-people'); ?>
<div class="row g-4 mb-6 anim-slide-up">
    <div class="col-md-5">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title">
                    <i class="bi bi-file-earmark-person-fill" style="color:#3B82F6;"></i> Distribución por Tipo de Contrato
                </div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div id="chartContratoTipo"></div>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title">
                    <i class="bi bi-table" style="color:#3B82F6;"></i> Composición Contractual del Personal
                </div>
            </div>
            <div class="sig-table-wrap">
                <table class="sig-table">
                    <thead>
                        <tr>
                            <th>Tipo de Contrato</th>
                            <th style="text-align:center;">Empleados</th>
                            <th style="text-align:center;">% del Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totalEmpsContrato = 0;
                        foreach ($data['empPorContrato'] ?? [] as $ec) $totalEmpsContrato += (int)($ec->total ?? 0);
                        if (empty($data['empPorContrato'])): ?>
                            <tr><td colspan="3" style="text-align:center;color:var(--text-tertiary);padding:var(--sp-4);">Sin datos registrados</td></tr>
                        <?php else:
                            foreach ($data['empPorContrato'] as $ec):
                                $pctEc = $totalEmpsContrato > 0 ? round(((int)($ec->total ?? 0) / $totalEmpsContrato) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td class="cell-strong"><?php echo htmlspecialchars($ec->tipo_contrato ?? '—'); ?></td>
                            <td style="text-align:center;font-weight:700;color:#3B82F6;"><?php echo (int)($ec->total ?? 0); ?></td>
                            <td style="text-align:center;">
                                <div style="display:flex;align-items:center;gap:var(--sp-2);">
                                    <div style="flex:1;height:4px;background:var(--bg-muted);border-radius:2px;overflow:hidden;">
                                        <div style="height:100%;width:<?php echo $pctEc; ?>%;background:#3B82F6;"></div>
                                    </div>
                                    <span style="font-size:11px;font-weight:700;color:#3B82F6;min-width:36px;"><?php echo $pctEc; ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════
     SECCIÓN: PERSONAL Y ASISTENCIA
════════════════════════════════════════════════════════════════════════ -->
<div style="display:flex;align-items:center;gap:var(--sp-3);margin:var(--sp-6) 0 var(--sp-4) 0;">
    <div style="width:4px;height:20px;border-radius:2px;background:#3B82F6;flex-shrink:0;"></div>
    <span style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-secondary);">Personal y Asistencia</span>
    <div style="flex:1;height:1px;background:var(--border-subtle);"></div>
</div>

<div class="row g-4 mb-6 anim-slide-up">
    <div class="col-md-8">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title">
                    <i class="bi bi-bar-chart-horizontal-fill" style="color:#3B82F6;"></i> Empleados por Departamento
                </div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div id="chartEmpDepto"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title">
                    <i class="bi bi-calendar-check-fill" style="color:#F59E0B;"></i> Asistencia — Últimos 4 meses
                </div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div id="chartAsistMes"></div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════
     SECCIÓN: FORMACIÓN
════════════════════════════════════════════════════════════════════════ -->
<div style="display:flex;align-items:center;gap:var(--sp-3);margin:var(--sp-6) 0 var(--sp-4) 0;">
    <div style="width:4px;height:20px;border-radius:2px;background:#7C3AED;flex-shrink:0;"></div>
    <span style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-secondary);">Formación</span>
    <div style="flex:1;height:1px;background:var(--border-subtle);"></div>
</div>

<?php
$metaTall  = (int)($data['metaTalleres'] ?? 0);
$tallAnio  = (int)($data['talleresAnio'] ?? 0);
$pctTall   = ($metaTall > 0) ? min(100, round(($tallAnio / $metaTall) * 100)) : null;
$colTall   = $pctTall === null ? '#7C3AED' : ($pctTall >= 100 ? '#059669' : ($pctTall >= 70 ? '#D97706' : '#7C3AED'));
?>
<?php if ($metaTall > 0): ?>
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-4); border-left:3px solid <?php echo $colTall; ?>;">
    <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-5);">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:var(--sp-4);flex-wrap:wrap;">
            <div>
                <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-secondary);margin-bottom:4px;">
                    <i class="bi bi-flag-fill" style="color:<?php echo $colTall; ?>;"></i> Meta Anual de Actividades Formativas — <?php echo $data['anioActual']; ?>
                </div>
                <div style="font-size:1.5rem;font-weight:800;color:<?php echo $colTall; ?>;line-height:1;">
                    <?php echo $tallAnio; ?> <span style="font-size:1rem;color:var(--text-tertiary);font-weight:500;">de <?php echo $metaTall; ?></span>
                </div>
                <div style="font-size:12px;color:var(--text-tertiary);margin-top:2px;">actividades finalizadas en el año</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:2.5rem;font-weight:900;color:<?php echo $colTall; ?>;line-height:1;"><?php echo $pctTall; ?>%</div>
                <div style="font-size:11px;color:var(--text-tertiary);">cumplimiento</div>
            </div>
        </div>
        <div style="margin-top:var(--sp-3);height:8px;background:var(--bg-muted);border-radius:4px;overflow:hidden;">
            <div style="height:100%;width:<?php echo $pctTall; ?>%;background:<?php echo $colTall; ?>;border-radius:4px;transition:width 1s;"></div>
        </div>
        <?php if ($pctTall >= 100): ?>
            <div style="margin-top:var(--sp-2);font-size:12px;color:#059669;font-weight:600;"><i class="bi bi-check-circle-fill"></i> ¡Meta alcanzada!</div>
        <?php else: $faltan = $metaTall - $tallAnio; ?>
            <div style="margin-top:var(--sp-2);font-size:12px;color:var(--text-tertiary);">Faltan <?php echo $faltan; ?> actividad(es) para cumplir la meta</div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="row g-4 mb-6 anim-slide-up">
    <div class="col-md-6">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title">
                    <i class="bi bi-graph-up-arrow" style="color:#7C3AED;"></i> Actividades — Tendencia 6 meses
                </div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div id="chartTallMes"></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title">
                    <i class="bi bi-pie-chart-fill" style="color:#8B5CF6;"></i> Tipo de Actividad
                </div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div id="chartTallTipo"></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title">
                    <i class="bi bi-people-fill" style="color:#059669;"></i> Participantes
                </div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div id="chartPartTipo"></div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════
     SECCIÓN: RECEPCIÓN DE VISITANTES
════════════════════════════════════════════════════════════════════════ -->
<div style="display:flex;align-items:center;gap:var(--sp-3);margin:var(--sp-6) 0 var(--sp-4) 0;">
    <div style="width:4px;height:20px;border-radius:2px;background:#0891B2;flex-shrink:0;"></div>
    <span style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-secondary);">Recepción de Visitantes</span>
    <div style="flex:1;height:1px;background:var(--border-subtle);"></div>
</div>

<div class="row g-4 mb-6 anim-slide-up">
    <div class="col-md-7">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title">
                    <i class="bi bi-door-open-fill" style="color:#0891B2;"></i> Visitas — Últimos 14 días
                </div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div id="chartVisitasDia"></div>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title">
                    <i class="bi bi-tags-fill" style="color:#0891B2;"></i> Visitas por Motivo
                </div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div id="chartVisitasMotivo"></div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════
     SECCIÓN: FORMACIÓN — DEMOGRAFÍA Y GÉNERO (F-3)
════════════════════════════════════════════════════════════════════════ -->
<div style="display:flex;align-items:center;gap:var(--sp-3);margin:var(--sp-6) 0 var(--sp-4) 0;">
    <div style="width:4px;height:20px;border-radius:2px;background:#7C3AED;flex-shrink:0;"></div>
    <span style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-secondary);">Formación — Demografía <?php echo $data['anioActual']; ?></span>
    <div style="flex:1;height:1px;background:var(--border-subtle);"></div>
</div>

<?php
$dem = $data['demografiaFormacion'] ?? null;
$demTotal = (int)($dem->total ?? 0);
$demMujeres = (int)($dem->mujeres ?? 0);
$demHombres = (int)($dem->hombres ?? 0);
$demNinas   = (int)($dem->ninas   ?? 0);
$demNinos   = (int)($dem->ninos   ?? 0);
function pctDem($v, $t) { return $t > 0 ? round(($v/$t)*100, 1) : 0; }
?>
<div class="row g-3 mb-4 anim-slide-up">
    <?php foreach ([
        ['Mujeres',   $demMujeres, '#EC4899', 'bi-gender-female'],
        ['Hombres',   $demHombres, '#3B82F6', 'bi-gender-male'],
        ['Niñas',     $demNinas,   '#F59E0B', 'bi-person-heart'],
        ['Niños',     $demNinos,   '#10B981', 'bi-person'],
        ['Total formados', $demTotal, '#7C3AED', 'bi-people-fill'],
    ] as [$lbl, $val, $color, $icon]): ?>
    <div class="col-md col-4">
        <div class="sig-card" style="border-bottom:3px solid <?php echo $color; ?>;">
            <div class="sig-card__body" style="text-align:center;padding:var(--sp-4) var(--sp-3);">
                <i class="bi <?php echo $icon; ?>" style="color:<?php echo $color; ?>;font-size:1.2rem;"></i>
                <div style="font-size:1.5rem;font-weight:900;color:var(--text-primary);margin:4px 0;"><?php echo number_format($val); ?></div>
                <div style="font-size:9px;font-weight:700;color:var(--text-tertiary);text-transform:uppercase;"><?php echo $lbl; ?></div>
                <?php if ($lbl !== 'Total formados' && $demTotal > 0): ?>
                    <div style="font-size:10px;color:<?php echo $color; ?>;font-weight:700;"><?php echo pctDem($val, $demTotal); ?>%</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<div class="row g-4 mb-6 anim-slide-up">
    <div class="col-md-5">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-pie-chart-fill" style="color:#EC4899;"></i> Distribución por Género y Edad</div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div id="chartDemografia"></div>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-bar-chart-horizontal-fill" style="color:#7C3AED;"></i> Tipo de Entidad Atendida</div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div id="chartTipoEntidad"></div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════
     SECCIÓN: FORMACIÓN — COBERTURA TERRITORIAL Y CAPACITADORES (F-4, F-5)
════════════════════════════════════════════════════════════════════════ -->
<div style="display:flex;align-items:center;gap:var(--sp-3);margin:var(--sp-6) 0 var(--sp-4) 0;">
    <div style="width:4px;height:20px;border-radius:2px;background:#0891B2;flex-shrink:0;"></div>
    <span style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-secondary);">Formación — Cobertura Territorial y Capacitadores</span>
    <div style="flex:1;height:1px;background:var(--border-subtle);"></div>
</div>

<?php
$ct = $data['coberturaTerrForma'] ?? null;
$mCubiertos = (int)($ct->municipios_cubiertos ?? 0);
$mTotal     = (int)($ct->total_municipios     ?? 0);
$pctCob     = $mTotal > 0 ? round(($mCubiertos / $mTotal) * 100) : 0;
?>
<div class="row g-4 mb-6 anim-slide-up">
    <div class="col-md-4">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-geo-alt-fill" style="color:#0891B2;"></i> Cobertura Municipal</div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-5);text-align:center;">
                <div style="font-size:3rem;font-weight:900;color:#0891B2;line-height:1;"><?php echo $mCubiertos; ?></div>
                <div style="font-size:0.85rem;color:var(--text-secondary);margin:4px 0;">de <?php echo $mTotal; ?> municipios del estado</div>
                <div style="height:8px;background:var(--bg-muted);border-radius:4px;margin:var(--sp-3) 0;overflow:hidden;">
                    <div style="height:100%;width:<?php echo $pctCob; ?>%;background:#0891B2;border-radius:4px;transition:width 1s;"></div>
                </div>
                <div style="font-size:1.25rem;font-weight:800;color:#0891B2;"><?php echo $pctCob; ?>%</div>
                <div style="font-size:10px;color:var(--text-tertiary);margin-top:var(--sp-2);">Municipios cubiertos por sedes de actividades</div>
                <?php if (!empty($data['municipiosCubiertos'])): ?>
                <div style="margin-top:var(--sp-3);display:flex;flex-wrap:wrap;gap:var(--sp-1);justify-content:center;">
                    <?php foreach ($data['municipiosCubiertos'] as $mc): ?>
                        <span class="sig-badge sig-badge--sm sig-badge--info"><?php echo htmlspecialchars($mc->municipio); ?></span>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div style="font-size:11px;color:var(--text-tertiary);margin-top:var(--sp-2);font-style:italic;">Sin actividades con sede registrada este año</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-person-badge-fill" style="color:#059669;"></i> Capacitadores — Actividades <?php echo $data['anioActual']; ?></div>
            </div>
            <div class="sig-table-wrap">
                <table class="sig-table">
                    <thead>
                        <tr>
                            <th>Facilitador</th>
                            <th style="text-align:center;">Actividades</th>
                            <th style="text-align:center;">Personas Formadas</th>
                            <th style="text-align:center;">Promedio/Actividad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['capacitadores'])): ?>
                            <tr><td colspan="4" style="text-align:center;color:var(--text-tertiary);padding:var(--sp-4);">Sin actividades este año</td></tr>
                        <?php else: ?>
                            <?php foreach ($data['capacitadores'] as $cap): ?>
                            <tr>
                                <td class="cell-strong"><?php echo htmlspecialchars($cap->facilitador ?? '—'); ?></td>
                                <td style="text-align:center;font-weight:700;color:#7C3AED;"><?php echo (int)($cap->actividades ?? 0); ?></td>
                                <td style="text-align:center;font-weight:700;color:#059669;"><?php echo number_format((int)($cap->formados ?? 0)); ?></td>
                                <td style="text-align:center;color:var(--text-secondary);">
                                    <?php echo (int)($cap->actividades ?? 0) > 0 ? round((int)($cap->formados ?? 0) / (int)$cap->actividades, 1) : '—'; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════
     SECCIÓN: TURISMO — RUTAS POR TIPO Y META (T-1, T-2)
════════════════════════════════════════════════════════════════════════ -->
<div style="display:flex;align-items:center;gap:var(--sp-3);margin:var(--sp-6) 0 var(--sp-4) 0;">
    <div style="width:4px;height:20px;border-radius:2px;background:#D97706;flex-shrink:0;"></div>
    <span style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-secondary);">Turismo — Tipo de Ruta y Cobertura</span>
    <div style="flex:1;height:1px;background:var(--border-subtle);"></div>
</div>

<?php
$metaR  = (int)($data['metaRutas']  ?? 0);
$rutasA = (int)($data['rutasAnio']  ?? 0);
$pctR   = ($metaR > 0) ? min(100, round(($rutasA / $metaR) * 100)) : null;
$colR   = $pctR === null ? '#D97706' : ($pctR >= 100 ? '#059669' : ($pctR >= 70 ? '#D97706' : '#DC2626'));
?>
<?php if ($metaR > 0): ?>
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-4); border-left:3px solid <?php echo $colR; ?>;">
    <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-5);">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:var(--sp-4);flex-wrap:wrap;">
            <div>
                <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-secondary);margin-bottom:4px;">
                    <i class="bi bi-flag-fill" style="color:<?php echo $colR; ?>;"></i> Meta Anual de Rutas Turísticas — <?php echo $data['anioActual']; ?>
                </div>
                <div style="font-size:1.5rem;font-weight:800;color:<?php echo $colR; ?>;line-height:1;">
                    <?php echo $rutasA; ?> <span style="font-size:1rem;color:var(--text-tertiary);font-weight:500;">de <?php echo $metaR; ?></span>
                </div>
                <div style="font-size:12px;color:var(--text-tertiary);margin-top:2px;">rutas finalizadas (ejecutadas) este año</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:2.5rem;font-weight:900;color:<?php echo $colR; ?>;line-height:1;"><?php echo $pctR; ?>%</div>
                <div style="font-size:11px;color:var(--text-tertiary);">cumplimiento</div>
            </div>
        </div>
        <div style="margin-top:var(--sp-3);height:8px;background:var(--bg-muted);border-radius:4px;overflow:hidden;">
            <div style="height:100%;width:<?php echo $pctR; ?>%;background:<?php echo $colR; ?>;border-radius:4px;transition:width 1s;"></div>
        </div>
        <?php if ($pctR >= 100): ?>
            <div style="margin-top:var(--sp-2);font-size:12px;color:#059669;font-weight:600;"><i class="bi bi-check-circle-fill"></i> ¡Meta alcanzada!</div>
        <?php else: $faltanR = $metaR - $rutasA; ?>
            <div style="margin-top:var(--sp-2);font-size:12px;color:var(--text-tertiary);">Faltan <?php echo $faltanR; ?> ruta(s) para cumplir la meta</div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="row g-4 mb-6 anim-slide-up">
    <div class="col-md-5">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-pie-chart-fill" style="color:#D97706;"></i> Participantes por Tipo de Ruta</div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <?php if (empty($data['rutasPorTipo'])): ?>
                    <div style="text-align:center;padding:var(--sp-6);color:var(--text-tertiary);font-size:12px;">
                        <i class="bi bi-info-circle" style="font-size:1.5rem;display:block;margin-bottom:var(--sp-2);"></i>
                        Aún no hay rutas con participantes registrados.
                    </div>
                <?php else: ?>
                    <div id="chartRutasTipo"></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-trophy-fill" style="color:#D97706;"></i> Resumen por Tipo de Ruta</div>
            </div>
            <div class="sig-table-wrap">
                <table class="sig-table">
                    <thead>
                        <tr>
                            <th>Tipo de Ruta</th>
                            <th style="text-align:center;">Rutas</th>
                            <th style="text-align:center;">Participantes</th>
                            <th style="text-align:center;">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totalPartRutas = array_sum(array_column((array)$data['rutasPorTipo'], 'participantes'));
                        if (empty($data['rutasPorTipo'])): ?>
                            <tr><td colspan="4" style="text-align:center;color:var(--text-tertiary);padding:var(--sp-4);">Aún no hay rutas con participantes registrados</td></tr>
                        <?php else:
                            foreach ($data['rutasPorTipo'] as $rt):
                                $pctRt = $totalPartRutas > 0 ? round(($rt->participantes / $totalPartRutas) * 100) : 0;
                        ?>
                            <tr>
                                <td class="cell-strong"><?php echo htmlspecialchars($rt->tipo_ruta ?? '—'); ?></td>
                                <td style="text-align:center;font-weight:700;color:#D97706;"><?php echo (int)($rt->rutas ?? 0); ?></td>
                                <td style="text-align:center;font-weight:700;color:var(--text-primary);"><?php echo number_format((int)($rt->participantes ?? 0)); ?></td>
                                <td style="text-align:center;">
                                    <div style="display:flex;align-items:center;gap:var(--sp-2);">
                                        <div style="flex:1;height:4px;background:var(--bg-muted);border-radius:2px;overflow:hidden;">
                                            <div style="height:100%;width:<?php echo $pctRt; ?>%;background:#D97706;"></div>
                                        </div>
                                        <span style="font-size:11px;font-weight:700;color:#D97706;min-width:28px;"><?php echo $pctRt; ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════
     SECCIÓN: TURISMO — DEMOGRAFÍA DE PARTICIPANTES (T-DEMO)
════════════════════════════════════════════════════════════════════════ -->
<?php
$dr     = $data['demografiaRutas'] ?? null;
$drTot  = (int)($dr->total ?? 0);
$drMuj  = (int)($dr->mujeres ?? 0);
$drHom  = (int)($dr->hombres ?? 0);
$drNia  = (int)($dr->ninas   ?? 0);
$drNio  = (int)($dr->ninos   ?? 0);
function pctDR($v,$t){return $t>0?round(($v/$t)*100,1):0;}
?>
<?php if ($drTot > 0): ?>
<div style="display:flex;align-items:center;gap:var(--sp-3);margin:var(--sp-6) 0 var(--sp-4) 0;">
    <div style="width:4px;height:20px;border-radius:2px;background:#D97706;flex-shrink:0;"></div>
    <span style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-secondary);">Turismo — Demografía de Participantes <?php echo $data['anioActual']; ?></span>
    <div style="flex:1;height:1px;background:var(--border-subtle);"></div>
</div>
<div class="row g-3 mb-4 anim-slide-up">
    <?php foreach ([
        ['Mujeres', $drMuj, '#EC4899', 'bi-gender-female'],
        ['Hombres', $drHom, '#3B82F6', 'bi-gender-male'],
        ['Niñas (5-11)', $drNia, '#F59E0B', 'bi-person-heart'],
        ['Niños (5-11)', $drNio, '#10B981', 'bi-person'],
        ['Total', $drTot, '#D97706', 'bi-people-fill'],
    ] as [$lbl,$val,$color,$ico]): ?>
    <div class="col-md col-4">
        <div class="sig-card" style="border-bottom:3px solid <?php echo $color; ?>;">
            <div class="sig-card__body" style="text-align:center;padding:var(--sp-4) var(--sp-3);">
                <i class="bi <?php echo $ico; ?>" style="color:<?php echo $color; ?>;font-size:1.2rem;"></i>
                <div style="font-size:1.5rem;font-weight:900;color:var(--text-primary);margin:4px 0;"><?php echo number_format($val); ?></div>
                <div style="font-size:9px;font-weight:700;color:var(--text-tertiary);text-transform:uppercase;"><?php echo $lbl; ?></div>
                <?php if ($lbl !== 'Total' && $drTot > 0): ?>
                    <div style="font-size:10px;color:<?php echo $color; ?>;font-weight:700;"><?php echo pctDR($val,$drTot); ?>%</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<div class="row g-4 mb-6 anim-slide-up">
    <div class="col-md-5">
        <div class="sig-card h-100">
            <div class="sig-card__head"><div class="sig-card__title"><i class="bi bi-pie-chart-fill" style="color:#D97706;"></i> Distribución por Género y Grupo</div></div>
            <div class="sig-card__body" style="padding:var(--sp-4);"><div id="chartDemografiaRutas"></div></div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="sig-card h-100">
            <div class="sig-card__head"><div class="sig-card__title"><i class="bi bi-info-circle" style="color:#D97706;"></i> Notas sobre los datos</div></div>
            <div class="sig-card__body" style="padding:var(--sp-5);font-size:13px;color:var(--text-secondary);line-height:1.8;">
                <p><i class="bi bi-check-circle" style="color:#059669;"></i> <strong>Mujeres y Hombres</strong>: adultos con cédula registrados en el sistema (género del perfil de personas).</p>
                <p><i class="bi bi-check-circle" style="color:#059669;"></i> <strong>Niñas y Niños (5-11)</strong>: participantes libres con género y fecha de nacimiento capturados al inscribirse.</p>
                <p><i class="bi bi-exclamation-circle" style="color:#D97706;"></i> Participantes sin género registrado o sin fecha de nacimiento no se contabilizan en esta gráfica.</p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════════════════
     SECCIÓN: INVENTARIO DE BIENES
════════════════════════════════════════════════════════════════════════ -->
<div style="display:flex;align-items:center;gap:var(--sp-3);margin:var(--sp-6) 0 var(--sp-4) 0;">
    <div style="width:4px;height:20px;border-radius:2px;background:#059669;flex-shrink:0;"></div>
    <span style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-secondary);">Inventario de Bienes</span>
    <div style="flex:1;height:1px;background:var(--border-subtle);"></div>
</div>

<div class="row g-4 mb-8 anim-slide-up">
    <div class="col-md-4">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title">
                    <i class="bi bi-clipboard-check-fill" style="color:#D97706;"></i> Estado Físico
                </div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div id="chartInvCond"></div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title">
                    <i class="bi bi-box-seam-fill" style="color:#059669;"></i> Distribución por Categoría
                </div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div id="chartInvCat"></div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════
     TABLAS DE RESPALDO
════════════════════════════════════════════════════════════════════════ -->
<div class="row g-4 anim-slide-up" style="margin-bottom:var(--sp-8);">
    <div class="col-md-6">
        <div class="sig-card">
            <div class="sig-card__head">
                <div class="sig-card__title">Detalle Personal por Departamento</div>
            </div>
            <div class="sig-table-wrap">
                <table class="sig-table">
                    <thead>
                        <tr>
                            <th>Departamento</th>
                            <th style="text-align:center;">Empleados</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['empPorDepto'] ?? [] as $e): ?>
                            <tr>
                                <td class="cell-strong"><?php echo htmlspecialchars($e->departamento ?? 'Sin Dpto'); ?></td>
                                <td style="text-align:center;font-weight:700;color:#3B82F6;"><?php echo (int)($e->total ?? 0); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($data['empPorDepto'])): ?>
                            <tr><td colspan="2" style="text-align:center;color:var(--text-tertiary);padding:var(--sp-4);">Sin datos registrados</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="sig-card">
            <div class="sig-card__head">
                <div class="sig-card__title">Detalle Inventario por Categoría</div>
            </div>
            <div class="sig-table-wrap">
                <table class="sig-table">
                    <thead>
                        <tr>
                            <th>Categoría</th>
                            <th style="text-align:center;">Bienes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['invPorCat'] ?? [] as $i): ?>
                            <tr>
                                <td class="cell-strong"><?php echo htmlspecialchars($i->categoria ?? 'Sin Cat'); ?></td>
                                <td style="text-align:center;font-weight:700;color:#059669;"><?php echo (int)($i->total ?? 0); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($data['invPorCat'])): ?>
                            <tr><td colspan="2" style="text-align:center;color:var(--text-tertiary);padding:var(--sp-4);">Sin datos registrados</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════
     APEXCHARTS — inicialización
════════════════════════════════════════════════════════════════════════ -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const tp = getComputedStyle(document.body).getPropertyValue('--text-primary').trim();
    const ts = getComputedStyle(document.body).getPropertyValue('--text-secondary').trim();
    const bs = getComputedStyle(document.body).getPropertyValue('--border-subtle').trim();
    const theme  = { mode: isDark ? 'dark' : 'light' };
    const grid   = { borderColor: bs, strokeDashArray: 4 };
    const noData = { text: 'Sin datos disponibles', style: { color: ts, fontSize: '12px', fontWeight: '400' } };
    const axLbl  = { style: { colors: tp, fontSize: '11px' } };
    const palette = ['#3B82F6','#10B981','#F59E0B','#8B5CF6','#EC4899','#06B6D4','#F97316','#64748B'];
    const donutLabelOpts = {
        show: true,
        total: { show: true, label: 'TOTAL', color: ts, fontSize: '10px', fontWeight: '700',
                 formatter: function(w) { return w.globals.seriesTotals.reduce(function(a,b){return a+b;},0); } }
    };

    // ── 1. Empleados por Departamento (barras horizontales) ────────────
    <?php
    $lblD = []; $valD = [];
    foreach ($data['empPorDepto'] ?? [] as $e) {
        $lblD[] = $e->departamento ?? 'N/A';
        $valD[] = (int)($e->total ?? 0);
    }
    ?>
    new ApexCharts(document.querySelector('#chartEmpDepto'), {
        chart: { type: 'bar', height: 280, background: 'transparent', toolbar: { show: false } },
        series: [{ name: 'Empleados', data: <?php echo json_encode($valD); ?> }],
        xaxis: { categories: <?php echo json_encode($lblD); ?>, labels: axLbl },
        yaxis: { labels: { style: { colors: tp } } },
        plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '55%', distributed: true } },
        colors: palette,
        dataLabels: { enabled: true, style: { fontWeight: '700', fontSize: '11px' } },
        legend: { show: false },
        grid, theme, noData
    }).render();

    // ── 2. Asistencia mensual (barras verticales) ─────────────────────
    <?php
    $lblA = []; $valA = [];
    foreach ($data['asistenciaPorMes'] ?? [] as $a) {
        $lblA[] = fmtMes($a->mes ?? '');
        $valA[] = (int)($a->total ?? 0);
    }
    ?>
    new ApexCharts(document.querySelector('#chartAsistMes'), {
        chart: { type: 'bar', height: 280, background: 'transparent', toolbar: { show: false } },
        series: [{ name: 'Registros', data: <?php echo json_encode($valA); ?> }],
        xaxis: { categories: <?php echo json_encode($lblA); ?>, labels: axLbl },
        yaxis: { labels: { style: { colors: tp } } },
        plotOptions: { bar: { borderRadius: 5, columnWidth: '55%' } },
        colors: ['#F59E0B'],
        dataLabels: { enabled: true, style: { fontWeight: '700', fontSize: '11px' } },
        grid, theme, noData
    }).render();

    // ── 3. Actividades por mes — tendencia (área) ─────────────────────
    <?php
    $lblM = []; $valM = [];
    foreach ($data['talleresPorMes'] ?? [] as $t) {
        $lblM[] = fmtMes($t->mes ?? '');
        $valM[] = (int)($t->total ?? 0);
    }
    ?>
    new ApexCharts(document.querySelector('#chartTallMes'), {
        chart: { type: 'area', height: 280, background: 'transparent', toolbar: { show: false } },
        series: [{ name: 'Actividades', data: <?php echo json_encode($valM); ?> }],
        xaxis: { categories: <?php echo json_encode($lblM); ?>, labels: axLbl, axisBorder: { show: false } },
        yaxis: { labels: { style: { colors: tp } }, min: 0 },
        colors: ['#7C3AED'],
        stroke: { curve: 'smooth', width: 3 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.03, stops: [20, 100] } },
        markers: { size: 5, colors: ['#7C3AED'], strokeWidth: 2, strokeColors: isDark ? '#1e1e2d' : '#fff' },
        grid, theme, noData
    }).render();

    // ── 4. Tipo de actividad (donut) ──────────────────────────────────
    <?php
    $lblTT = []; $valTT = [];
    foreach ($data['talleresPorTipo'] ?? [] as $t) {
        $lblTT[] = $t->tipo_actividad ?? 'N/A';
        $valTT[] = (int)($t->total ?? 0);
    }
    ?>
    new ApexCharts(document.querySelector('#chartTallTipo'), {
        chart: { type: 'donut', height: 280, background: 'transparent' },
        series: <?php echo json_encode($valTT); ?>,
        labels: <?php echo json_encode($lblTT); ?>,
        colors: ['#7C3AED','#10B981','#F59E0B'],
        legend: { position: 'bottom', labels: { colors: tp }, fontSize: '11px' },
        stroke: { show: false },
        plotOptions: { pie: { donut: { size: '55%', labels: donutLabelOpts } } },
        dataLabels: { enabled: false },
        theme, noData
    }).render();

    // ── 5. Participantes internos vs externos (donut) ─────────────────
    <?php
    $internos = (int)($data['participantesTipo']->internos ?? 0);
    $externos = (int)($data['participantesTipo']->externos ?? 0);
    ?>
    new ApexCharts(document.querySelector('#chartPartTipo'), {
        chart: { type: 'donut', height: 280, background: 'transparent' },
        series: [<?php echo $internos; ?>, <?php echo $externos; ?>],
        labels: ['Internos IMATUR', 'Externos'],
        colors: ['#3B82F6','#10B981'],
        legend: { position: 'bottom', labels: { colors: tp }, fontSize: '11px' },
        stroke: { show: false },
        plotOptions: { pie: { donut: { size: '55%', labels: donutLabelOpts } } },
        dataLabels: { enabled: false },
        theme, noData
    }).render();

    // ── 6. Visitas por día — últimos 14 días (barras) ─────────────────
    <?php
    $lblVD = []; $valVD = [];
    foreach ($data['visitasPorDia'] ?? [] as $v) {
        $partes = explode('-', $v->dia ?? '');
        $lblVD[] = isset($partes[2], $partes[1]) ? $partes[2] . '/' . $partes[1] : 'N/A';
        $valVD[] = (int)($v->total ?? 0);
    }
    ?>
    new ApexCharts(document.querySelector('#chartVisitasDia'), {
        chart: { type: 'bar', height: 280, background: 'transparent', toolbar: { show: false } },
        series: [{ name: 'Visitas', data: <?php echo json_encode($valVD); ?> }],
        xaxis: { categories: <?php echo json_encode($lblVD); ?>, labels: axLbl, axisBorder: { show: false } },
        yaxis: { labels: { style: { colors: tp } }, min: 0 },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
        colors: ['#0891B2'],
        dataLabels: { enabled: false },
        grid, theme, noData
    }).render();

    // ── 7. Visitas por motivo (barras horizontales) ───────────────────
    <?php
    $lblVM = []; $valVM = [];
    foreach ($data['visitasPorMotivo'] ?? [] as $v) {
        $lblVM[] = $v->motivo ?? 'N/A';
        $valVM[] = (int)($v->total ?? 0);
    }
    ?>
    new ApexCharts(document.querySelector('#chartVisitasMotivo'), {
        chart: { type: 'bar', height: 280, background: 'transparent', toolbar: { show: false } },
        series: [{ name: 'Visitas', data: <?php echo json_encode($valVM); ?> }],
        xaxis: { categories: <?php echo json_encode($lblVM); ?>, labels: { style: { colors: tp, fontSize: '10px' } } },
        yaxis: { labels: { style: { colors: tp } } },
        plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '60%', distributed: true } },
        colors: palette,
        dataLabels: { enabled: true, style: { fontWeight: '700', fontSize: '10px' } },
        legend: { show: false },
        grid, theme, noData
    }).render();

    // ── 8. Estado físico del inventario (radialBar) ───────────────────
    <?php
    $lblIC = []; $valIC = [];
    $totalInv = 0;
    foreach ($data['invPorCondicion'] ?? [] as $c) $totalInv += (int)($c->total ?? 0);
    foreach ($data['invPorCondicion'] ?? [] as $c) {
        $lblIC[] = $c->condicion ?? 'N/A';
        $valIC[] = $totalInv > 0 ? round(((int)($c->total ?? 0) / $totalInv) * 100) : 0;
    }
    ?>
    new ApexCharts(document.querySelector('#chartInvCond'), {
        chart: { type: 'radialBar', height: 320, background: 'transparent' },
        series: <?php echo json_encode($valIC); ?>,
        labels: <?php echo json_encode($lblIC); ?>,
        colors: ['#10B981','#3B82F6','#F59E0B','#EF4444','#64748B'],
        plotOptions: {
            radialBar: {
                dataLabels: {
                    name: { fontSize: '12px', color: tp },
                    value: { fontSize: '18px', fontWeight: '800', color: tp, formatter: function(v){ return v + '%'; } },
                    total: { show: true, label: 'BIENES', color: ts, fontSize: '10px', fontWeight: '700' }
                },
                hollow: { size: '35%' },
                track: { background: bs }
            }
        },
        legend: { show: true, position: 'bottom', labels: { colors: tp }, fontSize: '11px' },
        theme, noData
    }).render();

    // ── NEW: Demografía formación (donut) ────────────────────────────
    new ApexCharts(document.querySelector('#chartDemografia'), {
        chart: { type: 'donut', height: 280, background: 'transparent' },
        series: [<?php echo $demMujeres; ?>, <?php echo $demHombres; ?>, <?php echo $demNinas; ?>, <?php echo $demNinos; ?>],
        labels: ['Mujeres', 'Hombres', 'Niñas', 'Niños'],
        colors: ['#EC4899', '#3B82F6', '#F59E0B', '#10B981'],
        legend: { position: 'bottom', labels: { colors: tp }, fontSize: '11px' },
        stroke: { show: false },
        plotOptions: { pie: { donut: { size: '55%', labels: donutLabelOpts } } },
        dataLabels: { enabled: false },
        theme, noData
    }).render();

    // ── NEW: Tipo de entidad atendida (barras horizontales) ──────────
    <?php
    $lblTE = []; $valTE = [];
    foreach ($data['tipoEntidad'] ?? [] as $te) {
        $lblTE[] = $te->tipo_ente ?? 'N/A';
        $valTE[] = (int)($te->participantes ?? 0);
    }
    ?>
    new ApexCharts(document.querySelector('#chartTipoEntidad'), {
        chart: { type: 'bar', height: 280, background: 'transparent', toolbar: { show: false } },
        series: [{ name: 'Participantes', data: <?php echo json_encode($valTE); ?> }],
        xaxis: { categories: <?php echo json_encode($lblTE); ?>, labels: { style: { colors: tp, fontSize: '11px' } } },
        yaxis: { labels: { style: { colors: tp } } },
        plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '55%', distributed: true } },
        colors: palette,
        dataLabels: { enabled: true, style: { fontWeight: '700', fontSize: '11px' } },
        legend: { show: false },
        grid, theme, noData
    }).render();

    // ── NEW: Participantes por tipo de ruta (donut) ─────────────────
    <?php if (!empty($data['rutasPorTipo'])): ?>
    <?php
    $lblRT = []; $valRT = [];
    foreach ($data['rutasPorTipo'] as $rt) {
        $lblRT[] = $rt->tipo_ruta ?? 'General';
        $valRT[] = (int)($rt->participantes ?? 0);
    }
    ?>
    new ApexCharts(document.querySelector('#chartRutasTipo'), {
        chart: { type: 'donut', height: 280, background: 'transparent' },
        series: <?php echo json_encode($valRT); ?>,
        labels: <?php echo json_encode($lblRT); ?>,
        colors: ['#D97706', '#0EA5E9', '#10B981', '#8B5CF6'],
        legend: { position: 'bottom', labels: { colors: tp }, fontSize: '11px' },
        stroke: { show: false },
        plotOptions: { pie: { donut: { size: '55%', labels: donutLabelOpts } } },
        dataLabels: { enabled: false },
        theme, noData
    }).render();
    <?php endif; ?>

    // ── 9. Inventario por categoría (donut) ───────────────────────────
    <?php
    $lblCat = []; $valCat = [];
    foreach ($data['invPorCat'] ?? [] as $i) {
        $lblCat[] = $i->categoria ?? 'N/A';
        $valCat[] = (int)($i->total ?? 0);
    }
    ?>
    new ApexCharts(document.querySelector('#chartInvCat'), {
        chart: { type: 'donut', height: 320, background: 'transparent' },
        series: <?php echo json_encode($valCat); ?>,
        labels: <?php echo json_encode($lblCat); ?>,
        colors: palette,
        legend: { position: 'bottom', labels: { colors: tp }, fontSize: '11px' },
        stroke: { show: false },
        plotOptions: { pie: { donut: { size: '55%', labels: donutLabelOpts } } },
        dataLabels: { enabled: false },
        theme, noData
    }).render();

    // ── T-DEMO: Demografía de participantes en rutas (donut) ─────────────────
    <?php if (!empty($drTot)): ?>
    new ApexCharts(document.querySelector('#chartDemografiaRutas'), {
        chart: { type: 'donut', height: 280, background: 'transparent' },
        series: [<?php echo $drMuj; ?>, <?php echo $drHom; ?>, <?php echo $drNia; ?>, <?php echo $drNio; ?>],
        labels: ['Mujeres', 'Hombres', 'Niñas (5-11)', 'Niños (5-11)'],
        colors: ['#EC4899', '#3B82F6', '#F59E0B', '#10B981'],
        legend: { position: 'bottom', labels: { colors: tp }, fontSize: '11px' },
        stroke: { show: false },
        plotOptions: { pie: { donut: { size: '55%', labels: donutLabelOpts } } },
        dataLabels: { enabled: false },
        theme, noData
    }).render();
    <?php endif; ?>

    // ── PROP-P01: Tipo de contrato (donut) ────────────────────────────
    <?php
    $lblCT = []; $valCT = [];
    foreach ($data['empPorContrato'] ?? [] as $ec) {
        $lblCT[] = $ec->tipo_contrato ?? 'Sin especificar';
        $valCT[] = (int)($ec->total   ?? 0);
    }
    ?>
    new ApexCharts(document.querySelector('#chartContratoTipo'), {
        chart: { type: 'donut', height: 280, background: 'transparent' },
        series: <?php echo json_encode($valCT); ?>,
        labels: <?php echo json_encode($lblCT); ?>,
        colors: ['#3B82F6','#10B981','#F59E0B','#8B5CF6','#EC4899','#64748B'],
        legend: { position: 'bottom', labels: { colors: tp }, fontSize: '11px' },
        stroke: { show: false },
        plotOptions: { pie: { donut: { size: '55%', labels: donutLabelOpts } } },
        dataLabels: { enabled: false },
        theme, noData
    }).render();
});
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
