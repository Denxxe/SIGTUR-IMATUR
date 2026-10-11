<?php require_once '../app/views/inc/header.php';
$tol = (int)($data['tolerancia'] ?? 15);
$r   = $data['resumen'] ?? ['activos'=>0,'presentes'=>0,'impuntuales'=>0,'en_actividad'=>0,'ausentes'=>0];
$hm  = fn($t) => !empty($t) ? substr($t, 0, 5) : '—';
// Badge de puntualidad a partir de minutos_tarde
$puntualidad = function ($min) use ($tol) {
    if ($min === null) return '<span class="sig-badge sig-badge--neutral">— sin horario</span>';
    $min = (int)$min;
    if ($min > $tol) return '<span class="sig-badge sig-badge--danger">Impuntual (' . $min . ' min)</span>';
    return '<span class="sig-badge sig-badge--success">Puntual</span>';
};
// Paginación servidor del historial
$flt          = $data['filtros'] ?? ['buscar'=>'','fecha_desde'=>'','fecha_hasta'=>''];
$pagina       = (int)($data['pagina'] ?? 1);
$totalPaginas = (int)($data['total_paginas'] ?? 1);
$totalReg     = (int)($data['total'] ?? 0);
$porPagina    = (int)($data['por_pagina'] ?? 12);
$hayFiltro    = array_filter($flt, fn($v) => $v !== '');
function asisUrl(array $f, int $p): string {
    $q = array_filter($f, fn($v) => $v !== '' && $v !== null);
    $q['p'] = $p;
    return URL_ROOT . '/asistencias/index?' . http_build_query($q);
}
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Asistencia</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Asistencias'; ?></h1>
        <p class="page__subtitle">Registro de entrada/salida, puntualidad y ausentismo del personal IMATUR.</p>
    </div>
</div>

<!-- Resumen del día -->
<div class="row g-3 anim-slide-up" style="margin-bottom:var(--sp-5)">
    <?php
    $tiles = [
        ['Activos', $r['activos'], '#3B82F6', 'bi-people-fill'],
        ['Presentes hoy', $r['presentes'], '#059669', 'bi-check2-circle'],
        ['Impuntuales', $r['impuntuales'], '#EF4444', 'bi-alarm'],
        ['En actividad', $r['en_actividad'], '#7C3AED', 'bi-geo-alt'],
        ['Ausentes', $r['ausentes'], '#D97706', 'bi-person-dash'],
    ];
    foreach ($tiles as $t): ?>
        <div class="col">
            <div class="sig-card" style="border-top:3px solid <?php echo $t[2]; ?>;">
                <div class="sig-card__body u-p-sp-4 u-ta-center">
                    <i class="bi <?php echo $t[3]; ?>" style="font-size:1.3rem;color:<?php echo $t[2]; ?>"></i>
                    <div class="u-fs-1_8rem u-fw-800 u-c-text-primary u-lh-1_1 u-mt-4px"><?php echo (int)$t[1]; ?></div>
                    <div class="u-fs-11px u-c-text-secondary u-tt-uppercase u-ls-_04em"><?php echo $t[0]; ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Tarjeta de marcaje -->
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-5)">
    <div class="sig-card__body" style="padding:var(--sp-5) var(--sp-6)">
        <form action="<?php echo URL_ROOT; ?>/asistencias/marcar" method="POST" style="display:flex;gap:var(--sp-3);align-items:center;flex-wrap:wrap" id="formMarcaje">
            <select name="id_empleado" id="id_empleado" class="sig-select js-search" required>
                <option value="">Buscar empleado por nombre o cédula...</option>
                <?php foreach ($data['empleados'] ?? [] as $e): ?>
                    <option value="<?php echo $e->id; ?>"><?php echo ($e->nombre ?? '') . ' ' . ($e->apellido ?? ''); ?> (<?php echo $e->cedula ?? ''; ?>)</option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-sig btn-sig--primary" style="height:40px"><i class="bi bi-check2-circle"></i> MARCAR</button>
            <span class="sig-badge sig-badge--neutral" id="clock" style="font-size:14px;padding:6px 14px">--:--:--</span>
            <span class="u-fs-11px u-c-text-tertiary">Tolerancia de puntualidad: <?php echo $tol; ?> min</span>
            <div id="motivoTempranoWrap" style="display:none;flex:1 1 100%;">
                <label class="sig-field__label u-c-danger-600" for="motivo_temprano">
                    <i class="bi bi-exclamation-triangle-fill"></i> Sale antes de su horario (<span id="motivoHoraProgramada"></span>) — indique el motivo:
                </label>
                <textarea name="motivo_temprano" id="motivo_temprano" class="sig-input" rows="2" placeholder="Motivo de la salida anticipada..."></textarea>
            </div>
        </form>
    </div>
</div>

<!-- Ausentes / En actividad de hoy -->
<div class="row g-3 anim-slide-up" style="margin-bottom:var(--sp-5)">
    <div class="col-md-6">
        <div class="sig-table-wrap">
            <div class="u-p-sp-3-sp-5 u-bdb-1px-solid-border-subtle u-bg-bg-muted">
                <strong><i class="bi bi-person-dash u-c-xD97706"></i> Ausentes hoy (<?php echo count($data['ausentes'] ?? []); ?>)</strong>
            </div>
            <div class="u-maxhei-220px u-ov-auto" style="padding:var(--sp-2) var(--sp-4);">
                <?php if (empty($data['ausentes'])): ?>
                    <p class="u-c-text-secondary u-fs-13px u-m-sp-3-0">Sin ausentes registrados.</p>
                <?php else: foreach ($data['ausentes'] as $e): ?>
                    <div class="u-bdb-1px-solid-border-subtle u-fs-13px" style="padding:6px 0;">
                        <?php echo htmlspecialchars(($e->nombre ?? '') . ' ' . ($e->apellido ?? '')); ?>
                        <span class="u-c-text-tertiary">· <?php echo htmlspecialchars($e->departamento ?? ''); ?></span>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="sig-table-wrap">
            <div class="u-p-sp-3-sp-5 u-bdb-1px-solid-border-subtle u-bg-bg-muted">
                <strong><i class="bi bi-geo-alt u-c-x7C3AED"></i> En actividad hoy (<?php echo count($data['actividadDetalle'] ?? []); ?>)</strong>
            </div>
            <div class="u-maxhei-220px u-ov-auto" style="padding:var(--sp-2) var(--sp-4);">
                <?php if (empty($data['actividadDetalle'])): ?>
                    <p class="u-c-text-secondary u-fs-13px u-m-sp-3-0">Nadie en ruta o formación externa hoy.</p>
                <?php else: foreach ($data['actividadDetalle'] as $e): ?>
                    <div class="u-bdb-1px-solid-border-subtle u-fs-13px" style="padding:6px 0;">
                        <i class="bi bi-airplane u-c-x7C3AED"></i>
                        <?php echo htmlspecialchars(($e->nombre ?? '') . ' ' . ($e->apellido ?? '')); ?>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Historial de marcajes (paginado en servidor) -->
<div class="sig-table-wrap anim-slide-up">
    <div class="u-p-sp-3-sp-4 u-bdb-1px-solid-border-subtle u-bg-bg-muted u-ai-center u-jc-space-between u-gap-sp-3 u-fwr-wrap" style="display:flex;">
        <strong class="u-c-text-primary" style="font-size:var(--fs-md);">Historial de marcajes</strong>
        <form method="GET" action="<?php echo URL_ROOT; ?>/asistencias/index" style="display:flex;gap:var(--sp-2);align-items:flex-end;flex-wrap:wrap;">
            <div class="tabla-search" style="flex:0 0 auto;">
                <i class="bi bi-search"></i>
                <input type="text" name="buscar" class="sig-input" style="padding-left:32px;min-width:200px;" placeholder="Empleado o cédula…" value="<?php echo htmlspecialchars($flt['buscar'] ?? ''); ?>">
            </div>
            <input type="date" name="fecha_desde" class="sig-input" style="max-width:150px;" title="Desde" value="<?php echo htmlspecialchars($flt['fecha_desde'] ?? ''); ?>">
            <input type="date" name="fecha_hasta" class="sig-input" style="max-width:150px;" title="Hasta" value="<?php echo htmlspecialchars($flt['fecha_hasta'] ?? ''); ?>">
            <button type="submit" class="btn-sig btn-sig--primary btn-sig--sm"><i class="bi bi-funnel"></i></button>
            <?php if ($hayFiltro): ?>
                <a href="<?php echo URL_ROOT; ?>/asistencias/index" class="btn-sig btn-sig--ghost btn-sig--sm" title="Limpiar"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </form>
    </div>
    <table class="sig-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Empleado</th>
                <th>Entrada</th>
                <th>Salida</th>
                <th>Puntualidad</th>
                <th>Horas</th>
                <th>Observación</th>
                <th class="col-actions">Detalles</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['asistencias'])): ?>
                <tr><td colspan="8" class="sig-table-empty"><?php echo $hayFiltro ? 'Sin marcajes para el filtro aplicado.' : 'Aún no hay marcajes registrados.'; ?></td></tr>
            <?php else: foreach ($data['asistencias'] as $as): ?>
                <tr>
                    <td class="cell-strong"><?php echo date('d/m/Y', strtotime($as->fecha)); ?></td>
                    <td><?php echo $as->nombre . ' ' . $as->apellido; ?></td>
                    <td><span class="sig-badge sig-badge--success"><?php echo $hm($as->hora_entrada); ?></span></td>
                    <td>
                        <?php if ($as->hora_salida): ?>
                            <span class="sig-badge sig-badge--danger"><?php echo $hm($as->hora_salida); ?></span>
                        <?php else: ?>
                            <span class="sig-badge sig-badge--warning">PENDIENTE</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $puntualidad($as->minutos_tarde ?? null); ?></td>
                    <td><?php echo isset($as->horas) && $as->horas !== null ? number_format((float)$as->horas, 2) . ' h' : '—'; ?></td>
                    <td class="u-fs-12_5px u-c-text-secondary"><?php echo htmlspecialchars($as->observacion ?? ''); ?></td>
                    <td class="col-actions">
                        <button type="button" class="row-action row-action--view" data-bs-toggle="modal" data-bs-target="#detAsis<?php echo $as->id; ?>" title="Ver detalles">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
    <?php if ($totalReg > 0): ?>
    <div class="u-ai-center u-jc-space-between u-gap-sp-3 u-p-12px-16px u-bdt-1px-solid-border-subtle u-fwr-wrap" style="display:flex;">
        <span class="tabla-count">Mostrando <?php echo (($pagina-1)*$porPagina)+1; ?>–<?php echo min($totalReg, $pagina*$porPagina); ?> de <?php echo number_format($totalReg); ?></span>
        <?php if ($totalPaginas > 1): ?>
        <div class="u-ai-center u-gap-8px" style="display:flex;">
            <a class="tabla-pager__btn" href="<?php echo asisUrl($flt, max(1,$pagina-1)); ?>" <?php echo $pagina<=1?'style="pointer-events:none;opacity:.45;"':''; ?>><i class="bi bi-chevron-left"></i></a>
            <span class="tabla-pager__info">Página <?php echo $pagina; ?> de <?php echo $totalPaginas; ?></span>
            <a class="tabla-pager__btn" href="<?php echo asisUrl($flt, min($totalPaginas,$pagina+1)); ?>" <?php echo $pagina>=$totalPaginas?'style="pointer-events:none;opacity:.45;"':''; ?>><i class="bi bi-chevron-right"></i></a>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Modales de detalle de marcaje -->
<?php foreach ($data['asistencias'] ?? [] as $as): ?>
<div class="modal fade" id="detAsis<?php echo $as->id; ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-calendar-check"></i> Detalle de asistencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="audit-kv u-w-100pct">
                    <tr><th>Empleado</th><td><?php echo htmlspecialchars($as->nombre . ' ' . $as->apellido); ?></td></tr>
                    <tr><th>Cédula</th><td><?php echo htmlspecialchars($as->cedula ?? '—'); ?></td></tr>
                    <tr><th>Expediente</th><td><?php echo htmlspecialchars($as->nro_expediente ?? '—'); ?></td></tr>
                    <tr><th>Departamento</th><td><?php echo htmlspecialchars($as->departamento ?? '—'); ?></td></tr>
                    <tr><th>Cargo</th><td><?php echo htmlspecialchars($as->cargo ?? '—'); ?></td></tr>
                    <tr><th>Fecha</th><td><?php echo date('d/m/Y', strtotime($as->fecha)); ?></td></tr>
                    <tr><th>Hora entrada</th><td><?php echo $hm($as->hora_entrada); ?></td></tr>
                    <tr><th>Hora salida</th><td><?php echo $as->hora_salida ? $hm($as->hora_salida) : 'Pendiente'; ?></td></tr>
                    <tr><th>Horas trabajadas</th><td><?php echo isset($as->horas) && $as->horas !== null ? number_format((float)$as->horas, 2) . ' h' : '—'; ?></td></tr>
                    <tr><th>Puntualidad</th><td><?php echo $puntualidad($as->minutos_tarde ?? null); ?></td></tr>
                    <tr><th>Observación</th><td><?php echo htmlspecialchars($as->observacion ?? '—'); ?></td></tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<style>
.audit-kv { width:100%; border-collapse:collapse; font-size:13px; }
.audit-kv th { text-align:left; font-weight:700; color:var(--text-secondary); padding:4px 12px 4px 0; white-space:nowrap; vertical-align:top; width:1%; }
.audit-kv td { color:var(--text-primary); padding:4px 0; word-break:break-word; }
.audit-kv tr + tr th, .audit-kv tr + tr td { border-top:1px dashed var(--border-subtle); }
</style>

<script>
    function updateClock() {
        const now = new Date();
        document.getElementById('clock').innerText = now.toLocaleTimeString('es-ES', { hour12: false });
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Si el próximo marcaje del empleado seleccionado es una salida antes de
    // su horario, exige un motivo antes de permitir el envío del formulario.
    const selEmpleado    = document.getElementById('id_empleado');
    const wrapMotivo      = document.getElementById('motivoTempranoWrap');
    const inputMotivo     = document.getElementById('motivo_temprano');
    const spanHoraProg    = document.getElementById('motivoHoraProgramada');

    function ocultarMotivo() {
        wrapMotivo.style.display = 'none';
        inputMotivo.required = false;
        inputMotivo.value = '';
    }

    selEmpleado?.addEventListener('change', () => {
        const id = selEmpleado.value;
        if (!id) { ocultarMotivo(); return; }
        fetch('<?php echo URL_ROOT; ?>/asistencias/estadoMarcaje?id=' + encodeURIComponent(id))
            .then(r => r.json())
            .then(json => {
                if (json.requiere_motivo) {
                    spanHoraProg.textContent = json.hora_programada || '';
                    wrapMotivo.style.display = 'block';
                    inputMotivo.required = true;
                } else {
                    ocultarMotivo();
                }
            })
            .catch(() => ocultarMotivo());
    });
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
