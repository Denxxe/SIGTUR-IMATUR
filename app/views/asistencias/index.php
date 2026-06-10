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
                <div class="sig-card__body" style="padding:var(--sp-4);text-align:center;">
                    <i class="bi <?php echo $t[3]; ?>" style="font-size:1.3rem;color:<?php echo $t[2]; ?>"></i>
                    <div style="font-size:1.8rem;font-weight:800;color:var(--text-primary);line-height:1.1;margin-top:4px;"><?php echo (int)$t[1]; ?></div>
                    <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.04em;"><?php echo $t[0]; ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Tarjeta de marcaje -->
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-5)">
    <div class="sig-card__body" style="padding:var(--sp-5) var(--sp-6)">
        <form action="<?php echo URL_ROOT; ?>/asistencias/marcar" method="POST" style="display:flex;gap:var(--sp-3);align-items:center;flex-wrap:wrap">
            <select name="id_empleado" class="sig-select js-search" required>
                <option value="">Buscar empleado por nombre o cédula...</option>
                <?php foreach ($data['empleados'] ?? [] as $e): ?>
                    <option value="<?php echo $e->id; ?>"><?php echo ($e->nombre ?? '') . ' ' . ($e->apellido ?? ''); ?> (<?php echo $e->cedula ?? ''; ?>)</option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-sig btn-sig--primary" style="height:40px"><i class="bi bi-check2-circle"></i> MARCAR</button>
            <span class="sig-badge sig-badge--neutral" id="clock" style="font-size:14px;padding:6px 14px">--:--:--</span>
            <span style="font-size:11px;color:var(--text-tertiary)">Tolerancia de puntualidad: <?php echo $tol; ?> min</span>
        </form>
    </div>
</div>

<!-- Ausentes / En actividad de hoy -->
<div class="row g-3 anim-slide-up" style="margin-bottom:var(--sp-5)">
    <div class="col-md-6">
        <div class="sig-table-wrap">
            <div style="padding:var(--sp-3) var(--sp-5);border-bottom:1px solid var(--border-subtle);background:var(--bg-muted)">
                <strong><i class="bi bi-person-dash" style="color:#D97706"></i> Ausentes hoy (<?php echo count($data['ausentes'] ?? []); ?>)</strong>
            </div>
            <div style="max-height:220px;overflow:auto;padding:var(--sp-2) var(--sp-4)">
                <?php if (empty($data['ausentes'])): ?>
                    <p style="color:var(--text-secondary);font-size:13px;margin:var(--sp-3) 0">Sin ausentes registrados.</p>
                <?php else: foreach ($data['ausentes'] as $e): ?>
                    <div style="padding:6px 0;border-bottom:1px solid var(--border-subtle);font-size:13px">
                        <?php echo htmlspecialchars(($e->nombre ?? '') . ' ' . ($e->apellido ?? '')); ?>
                        <span style="color:var(--text-tertiary)">· <?php echo htmlspecialchars($e->departamento ?? ''); ?></span>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="sig-table-wrap">
            <div style="padding:var(--sp-3) var(--sp-5);border-bottom:1px solid var(--border-subtle);background:var(--bg-muted)">
                <strong><i class="bi bi-geo-alt" style="color:#7C3AED"></i> En actividad hoy (<?php echo count($data['actividadDetalle'] ?? []); ?>)</strong>
            </div>
            <div style="max-height:220px;overflow:auto;padding:var(--sp-2) var(--sp-4)">
                <?php if (empty($data['actividadDetalle'])): ?>
                    <p style="color:var(--text-secondary);font-size:13px;margin:var(--sp-3) 0">Nadie en ruta o formación externa hoy.</p>
                <?php else: foreach ($data['actividadDetalle'] as $e): ?>
                    <div style="padding:6px 0;border-bottom:1px solid var(--border-subtle);font-size:13px">
                        <i class="bi bi-airplane" style="color:#7C3AED"></i>
                        <?php echo htmlspecialchars(($e->nombre ?? '') . ' ' . ($e->apellido ?? '')); ?>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Historial de marcajes (paginado en servidor) -->
<div class="sig-table-wrap anim-slide-up">
    <div style="padding:var(--sp-3) var(--sp-4);border-bottom:1px solid var(--border-subtle);background:var(--bg-muted);display:flex;align-items:center;justify-content:space-between;gap:var(--sp-3);flex-wrap:wrap;">
        <strong style="font-size:var(--fs-md);color:var(--text-primary)">Historial de marcajes</strong>
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
                <th class="col-actions">Eliminar</th>
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
                    <td style="font-size:12.5px;color:var(--text-secondary)"><?php echo htmlspecialchars($as->observacion ?? ''); ?></td>
                    <td class="col-actions">
                        <a href="<?php echo URL_ROOT; ?>/asistencias/delete/<?php echo $as->id; ?>" class="row-action row-action--del delete-btn">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
    <?php if ($totalReg > 0): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;gap:var(--sp-3);padding:12px 16px;border-top:1px solid var(--border-subtle);flex-wrap:wrap;">
        <span class="tabla-count">Mostrando <?php echo (($pagina-1)*$porPagina)+1; ?>–<?php echo min($totalReg, $pagina*$porPagina); ?> de <?php echo number_format($totalReg); ?></span>
        <?php if ($totalPaginas > 1): ?>
        <div style="display:flex;align-items:center;gap:8px;">
            <a class="tabla-pager__btn" href="<?php echo asisUrl($flt, max(1,$pagina-1)); ?>" <?php echo $pagina<=1?'style="pointer-events:none;opacity:.45;"':''; ?>><i class="bi bi-chevron-left"></i></a>
            <span class="tabla-pager__info">Página <?php echo $pagina; ?> de <?php echo $totalPaginas; ?></span>
            <a class="tabla-pager__btn" href="<?php echo asisUrl($flt, min($totalPaginas,$pagina+1)); ?>" <?php echo $pagina>=$totalPaginas?'style="pointer-events:none;opacity:.45;"':''; ?>><i class="bi bi-chevron-right"></i></a>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
    function updateClock() {
        const now = new Date();
        document.getElementById('clock').innerText = now.toLocaleTimeString('es-ES', { hour12: false });
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
