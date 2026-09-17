<?php require_once '../app/views/inc/header.php';
/**
 * Salidas programadas (fase T-A, mig. 078).
 *
 * La otra mitad del módulo: cada vez que se ejecuta una ruta del catálogo.
 * Aquí viven la fecha, el grupo, los guías y el estado — R-07: *"así sean la
 * misma ruta, es considerada 2 salidas y en el registro son 2 rutas aplicadas"*.
 */
$flt          = $data['filtros'] ?? [];
$pagina       = (int)($data['pagina'] ?? 1);
$totalPaginas = (int)($data['total_paginas'] ?? 1);
$totalReg     = (int)($data['total'] ?? 0);
$porPagina    = (int)($data['por_pagina'] ?? 15);
$resumen      = $data['resumen'] ?? [];
$hayFiltro    = array_filter($flt, fn($v) => $v !== '');

function salidaUrl(array $f, int $p): string {
    $q = array_filter($f, fn($v) => $v !== '' && $v !== null);
    $q['p'] = $p;
    return URL_ROOT . '/rutas/salidas?' . http_build_query($q);
}
$fmt = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Turismo · Ejecución</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Salidas programadas'; ?></h1>
        <p class="page__subtitle">Cada vez que se ejecuta una ruta: su fecha, su grupo, sus guías y su cierre.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/rutas/index" class="btn-sig btn-sig--ghost">
            <i class="bi bi-map"></i> Catálogo de rutas
        </a>
        <?php if (!empty($data['catalogo'])): ?>
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalSalida" onclick="nuevaSalida()">
            <i class="bi bi-calendar-plus"></i> Programar salida
        </button>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($data['catalogo'])): ?>
<div class="sig-alert sig-alert--warning anim-slide-up" style="margin-bottom:var(--sp-4);">
    <i class="bi bi-exclamation-triangle"></i>
    <div>
        No hay ninguna ruta <strong>Activa</strong> en el catálogo, así que no se puede programar una
        salida. Crea o reactiva una ruta en <a href="<?php echo URL_ROOT; ?>/rutas/index">el catálogo</a>.
    </div>
</div>
<?php endif; ?>

<!-- Resumen por estado -->
<div class="row g-3 anim-slide-up" style="margin-bottom:var(--sp-4);">
    <?php
    $tiles = [
        RutaEjecucion::EST_PROGRAMADO   => ['Programadas',   'var(--info-600, #2563EB)',    'bi-calendar-event'],
        RutaEjecucion::EST_EJECUTADO    => ['Ejecutadas',    'var(--success-600, #059669)', 'bi-check-circle'],
        RutaEjecucion::EST_NO_EJECUTADO => ['No ejecutadas', 'var(--danger-600, #DC2626)',  'bi-x-circle'],
    ];
    foreach ($tiles as $est => [$lbl, $col, $ico]): ?>
    <div class="col-md-4 col-12">
        <div class="sig-card" style="border-bottom:3px solid <?php echo $col; ?>;">
            <div class="sig-card__body" style="text-align:center;padding:var(--sp-4);">
                <div style="font-size:10px;font-weight:700;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:.05em;">
                    <i class="bi <?php echo $ico; ?>"></i> <?php echo $lbl; ?>
                </div>
                <div style="font-size:26px;font-weight:900;color:<?php echo $col; ?>;">
                    <?php echo number_format($resumen[$est] ?? 0); ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filtros -->
<form class="sig-card anim-slide-up" method="GET" action="<?php echo URL_ROOT; ?>/rutas/salidas" style="margin-bottom:var(--sp-4);">
    <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-5);display:flex;align-items:flex-end;gap:var(--sp-3);flex-wrap:wrap;">
        <div style="flex:1;min-width:190px;">
            <label class="sig-field__label" style="font-size:11px;" for="buscar">Buscar</label>
            <div class="tabla-search">
                <i class="bi bi-search"></i>
                <input id="buscar" type="text" name="buscar" class="sig-input" style="padding-left:32px;width:100%;"
                       placeholder="Ruta, institución u observación…" value="<?php echo htmlspecialchars($flt['buscar'] ?? ''); ?>">
            </div>
        </div>
        <div>
            <label class="sig-field__label" style="font-size:11px;" for="f_ruta">Ruta</label>
            <select id="f_ruta" name="ruta" class="sig-input" style="min-width:170px;">
                <option value="">Todas</option>
                <?php foreach ($data['catalogo'] ?? [] as $r): ?>
                    <option value="<?php echo $r->id; ?>" <?php echo (string)($flt['ruta'] ?? '') === (string)$r->id ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($r->nombre); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="sig-field__label" style="font-size:11px;" for="f_estado">Estado</label>
            <select id="f_estado" name="estado" class="sig-input" style="min-width:145px;">
                <option value="">Todos</option>
                <?php foreach (RutaEjecucion::ESTADOS as $est): ?>
                    <option value="<?php echo $est; ?>" <?php echo ($flt['estado'] ?? '') === $est ? 'selected' : ''; ?>><?php echo $est; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="sig-field__label" style="font-size:11px;" for="f_origen">Origen</label>
            <select id="f_origen" name="origen" class="sig-input" style="min-width:135px;">
                <option value="">Todos</option>
                <?php foreach (RutaEjecucion::ORIGENES as $o): ?>
                    <option value="<?php echo $o; ?>" <?php echo ($flt['origen'] ?? '') === $o ? 'selected' : ''; ?>><?php echo $o; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="sig-field__label" style="font-size:11px;" for="f_periodo">Período</label>
            <?php $periodos = ['' => 'Todos', 'proximos' => 'Próximas', 'hoy' => 'Hoy', 'semana' => 'Esta semana', 'mes' => 'Este mes', 'pasados' => 'Pasadas']; ?>
            <select id="f_periodo" name="periodo" class="sig-input" style="min-width:130px;">
                <?php foreach ($periodos as $v => $l): ?>
                    <option value="<?php echo $v; ?>" <?php echo ($flt['periodo'] ?? '') === $v ? 'selected' : ''; ?>><?php echo $l; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:flex;gap:var(--sp-2);">
            <button type="submit" class="btn-sig btn-sig--primary" style="height:42px;"><i class="bi bi-funnel"></i> Filtrar</button>
            <?php if ($hayFiltro): ?>
            <a href="<?php echo URL_ROOT; ?>/rutas/salidas" class="btn-sig btn-sig--ghost" style="height:42px;padding:0 var(--sp-3);" title="Limpiar"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </div>
    </div>
</form>

<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead><tr>
            <th>Fecha</th><th>Ruta</th><th>Solicitante</th>
            <th class="text-center">Grupo</th><th class="text-center">Guías</th>
            <th>Estado</th><th class="col-actions">Acciones</th>
        </tr></thead>
        <tbody>
            <?php if (empty($data['salidas'])): ?>
                <tr><td colspan="7" class="sig-table-empty">
                    <?php echo $hayFiltro ? 'No hay salidas que coincidan con el filtro.' : 'Todavía no se ha programado ninguna salida.'; ?>
                </td></tr>
            <?php else: foreach ($data['salidas'] as $s): ?>
                <tr>
                    <td style="white-space:nowrap;">
                        <span class="cell-strong"><?php echo $fmt($s->fecha); ?></span>
                        <?php if ($s->hora): ?>
                            <div style="font-size:12px;color:var(--text-tertiary);"><?php echo substr($s->hora, 0, 5); ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="cell-strong"><?php echo htmlspecialchars($s->ruta_nombre); ?></span>
                        <?php if (!empty($s->fecha_original)): ?>
                            <div style="font-size:11px;color:var(--warning-600,#d97706);">
                                <i class="bi bi-arrow-repeat"></i> reprogramada del <?php echo $fmt($s->fecha_original); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;">
                        <span class="sig-badge <?php echo $s->origen === 'Institucional' ? 'sig-badge--info' : 'sig-badge--neutral'; ?>">
                            <?php echo htmlspecialchars($s->origen); ?>
                        </span>
                        <?php if (!empty($s->institucion_nombre)): ?>
                            <div style="color:var(--text-secondary);max-width:200px;white-space:normal;">
                                <?php echo htmlspecialchars($s->institucion_nombre); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?php echo (int)$s->total_participantes; ?></td>
                    <td class="text-center"><?php echo (int)$s->total_empleados; ?></td>
                    <td>
                        <span class="sig-badge <?php echo RutaEjecucion::ESTADO_BADGES[$s->estado] ?? 'sig-badge--neutral'; ?>">
                            <?php echo htmlspecialchars($s->estado); ?>
                        </span>
                        <?php if ($s->estado === RutaEjecucion::EST_NO_EJECUTADO && !empty($s->motivo_no_ejecucion)): ?>
                            <div style="font-size:11px;color:var(--text-tertiary);max-width:210px;white-space:normal;">
                                <?php echo htmlspecialchars($s->motivo_no_ejecucion); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (empty($s->fecha_aprobacion) && $s->estado === RutaEjecucion::EST_PROGRAMADO): ?>
                            <div style="font-size:11px;color:var(--warning-600,#d97706);">
                                <i class="bi bi-hourglass"></i> sin aprobar
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="col-actions">
                        <a href="<?php echo URL_ROOT; ?>/rutas/detalle/<?php echo $s->id; ?>" class="row-action">
                            <i class="bi bi-folder2-open"></i> Abrir
                        </a>
                        <?php if ($s->estado === RutaEjecucion::EST_PROGRAMADO): ?>
                        <button type="button" class="row-action row-action--edit js-editar-salida"
                                data-salida='<?php echo htmlspecialchars(json_encode($s), ENT_QUOTES, "UTF-8"); ?>'>
                            <i class="bi bi-pencil"></i> Editar
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPaginas > 1): ?>
<nav class="anim-slide-up" style="display:flex;align-items:center;justify-content:center;gap:6px;margin:var(--sp-4) 0 var(--sp-8);flex-wrap:wrap;">
    <?php for ($i = max(1, $pagina - 2); $i <= min($totalPaginas, $pagina + 2); $i++): ?>
        <a href="<?php echo salidaUrl($flt, $i); ?>"
           class="btn-sig <?php echo $i === $pagina ? 'btn-sig--primary' : 'btn-sig--ghost'; ?> btn-sig--sm"><?php echo $i; ?></a>
    <?php endfor; ?>
</nav>
<?php endif; ?>

<!-- Modal: programar / editar salida -->
<div class="modal fade" id="modalSalida" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo URL_ROOT; ?>/rutas/storeSalida" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSalidaLabel"><i class="bi bi-calendar-plus"></i> Programar salida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="sal_id">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="sig-field">
                            <label class="sig-field__label" for="sal_ruta">Ruta <span class="req">*</span></label>
                            <select name="id_ruta" id="sal_ruta" class="sig-select js-search" required>
                                <option value="">— Seleccione el recorrido —</option>
                                <?php foreach ($data['catalogo'] ?? [] as $r): ?>
                                    <option value="<?php echo $r->id; ?>"><?php echo htmlspecialchars($r->nombre); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="sig-field">
                            <label class="sig-field__label" for="sal_fecha">Fecha <span class="req">*</span></label>
                            <input type="date" name="fecha" id="sal_fecha" class="sig-input" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="sig-field">
                            <label class="sig-field__label" for="sal_hora">Hora</label>
                            <input type="time" name="hora" id="sal_hora" class="sig-input">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label" for="sal_origen">Origen <span class="req">*</span></label>
                            <select name="origen" id="sal_origen" class="sig-select" required>
                                <?php foreach (RutaEjecucion::ORIGENES as $o): ?>
                                    <option value="<?php echo $o; ?>"><?php echo $o; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5" id="sal_inst_wrap" style="display:none;">
                        <div class="sig-field">
                            <label class="sig-field__label" for="sal_inst">Institución solicitante <span class="req">*</span></label>
                            <input type="text" name="institucion_nombre" id="sal_inst" class="sig-input"
                                   placeholder="Ej: U.E. Nacional Cumaná">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="sig-field">
                            <label class="sig-field__label" for="sal_cupo">Cupo estimado</label>
                            <input type="number" name="cupo_maximo" id="sal_cupo" class="sig-input" min="1" max="200" placeholder="20">
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="sig-field" style="margin:0;">
                            <label class="sig-field__label" for="sal_obs">Observaciones</label>
                            <textarea name="observaciones" id="sal_obs" class="sig-textarea" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar salida</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleInstitucion() {
    var esInst = document.getElementById('sal_origen').value === 'Institucional';
    document.getElementById('sal_inst_wrap').style.display = esInst ? 'block' : 'none';
    document.getElementById('sal_inst').required = esInst;
    if (!esInst) document.getElementById('sal_inst').value = '';
}

function nuevaSalida() {
    document.getElementById('modalSalidaLabel').innerHTML = '<i class="bi bi-calendar-plus"></i> Programar salida';
    document.querySelector('#modalSalida form').reset();
    document.getElementById('sal_id').value = '';
    // Una salida nueva no se programa en el pasado; al editar sí puede quedar atrás.
    document.getElementById('sal_fecha').min = '<?php echo date('Y-m-d'); ?>';
    toggleInstitucion();
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('sal_origen').addEventListener('change', toggleInstitucion);

    const modal = new bootstrap.Modal(document.getElementById('modalSalida'));
    document.querySelectorAll('.js-editar-salida').forEach(b => b.addEventListener('click', () => {
        const s = JSON.parse(b.dataset.salida);
        document.getElementById('modalSalidaLabel').innerHTML = '<i class="bi bi-pencil"></i> Editar salida';
        document.getElementById('sal_id').value     = s.id;
        document.getElementById('sal_ruta').value   = s.id_ruta;
        document.getElementById('sal_fecha').min    = '';
        document.getElementById('sal_fecha').value  = s.fecha || '';
        document.getElementById('sal_hora').value   = s.hora ? s.hora.substring(0,5) : '';
        document.getElementById('sal_origen').value = s.origen || 'Particular';
        document.getElementById('sal_inst').value   = s.institucion_nombre || '';
        document.getElementById('sal_cupo').value   = s.cupo_maximo || '';
        document.getElementById('sal_obs').value    = s.observaciones || '';
        toggleInstitucion();
        modal.show();
    }));
});
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
