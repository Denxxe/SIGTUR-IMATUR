<?php require_once '../app/views/inc/header.php';
$flt          = $data['filtros'] ?? ['buscar'=>'','estado'=>'','tipo'=>'','fecha_desde'=>'','fecha_hasta'=>''];
$pagina       = (int)($data['pagina'] ?? 1);
$totalPaginas = (int)($data['total_paginas'] ?? 1);
$totalReg     = (int)($data['total'] ?? 0);
$porPagina    = (int)($data['por_pagina'] ?? 12);
$hayFiltro    = array_filter($flt, fn($v) => $v !== '');
function rutaUrl(array $f, int $p): string {
    $q = array_filter($f, fn($v) => $v !== '' && $v !== null);
    $q['p'] = $p;
    return URL_ROOT . '/rutas/index?' . http_build_query($q);
}
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Turismo · Gestión de Destinos</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Gestión de Rutas'; ?></h1>
        <p class="page__subtitle">Planificación y control de rutas turísticas y puntos de interés del municipio.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/rutas/salidas" class="btn-sig btn-sig--primary">
            <i class="bi bi-calendar-event"></i> Salidas programadas
        </a>
        <a href="<?php echo URL_ROOT; ?>/reportes/rutas" class="btn-sig btn-sig--success" title="Exportar listado completo (Excel/PDF)">
            <i class="bi bi-file-earmark-spreadsheet"></i> Exportar
        </a>
        <button type="button" class="btn-sig btn-sig--primary"
                style="background:linear-gradient(180deg, var(--teal-500), var(--teal-700)); box-shadow: var(--sh-glow-teal);"
                data-bs-toggle="modal" data-bs-target="#modalRuta" onclick="nuevaRuta()">
            <i class="bi bi-map"></i> Crear Nueva Ruta
        </button>
    </div>
</div>

<!-- Filtros (servidor) -->
<form class="sig-card anim-slide-up" method="GET" action="<?php echo URL_ROOT; ?>/rutas/index" style="margin-bottom:var(--sp-4);">
    <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-5); display:flex; align-items:flex-end; gap:var(--sp-3); flex-wrap:wrap;">
        <div style="flex:1; min-width:200px;">
            <label class="sig-field__label" style="font-size:11px;" for="buscar">Buscar</label>
            <div class="tabla-search">
                <i class="bi bi-search"></i>
                <input id="buscar" type="text" name="buscar" class="sig-input" style="padding-left:32px;width:100%;" placeholder="Nombre, descripción o guía…" value="<?php echo htmlspecialchars($flt['buscar'] ?? ''); ?>">
            </div>
        </div>
        <div>
            <label class="sig-field__label" style="font-size:11px;" for="estado">Estado</label>
            <select id="estado" name="estado" class="sig-input" style="min-width:150px;">
                <option value="">Todos</option>
                <?php foreach (Ruta::ESTADOS as $est): ?>
                    <option value="<?php echo $est; ?>" <?php echo ($flt['estado'] ?? '') === $est ? 'selected' : ''; ?>><?php echo $est; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="sig-field__label" style="font-size:11px;" for="tipo">Tipo</label>
            <select id="tipo" name="tipo" class="sig-input" style="min-width:150px;">
                <option value="">Todos</option>
                <?php foreach (Ruta::$TIPOS_RUTA as $tp): ?>
                    <option value="<?php echo htmlspecialchars($tp); ?>" <?php echo ($flt['tipo'] ?? '') === $tp ? 'selected' : ''; ?>><?php echo htmlspecialchars($tp); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php /* Los filtros de período y fecha se fueron a /rutas/salidas (mig. 078):
                 el catálogo no tiene fecha — preguntarle a un recorrido «¿fue esta
                 semana?» no significa nada. Eso se le pregunta a una salida. */ ?>
        <div style="display:flex; gap:var(--sp-2);">
            <button type="submit" class="btn-sig btn-sig--primary" style="height:42px;"><i class="bi bi-funnel"></i> Filtrar</button>
            <?php if ($hayFiltro): ?>
                <a href="<?php echo URL_ROOT; ?>/rutas/index" class="btn-sig btn-sig--ghost" style="height:42px; padding:0 var(--sp-3);" title="Limpiar"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </div>
    </div>
</form>

<!-- Resumen -->
<div class="anim-slide-up" style="font-size:13px; color:var(--text-secondary); margin-bottom:var(--sp-3);">
    <?php if ($totalReg > 0): ?>
        Mostrando <strong><?php echo (($pagina-1)*$porPagina)+1; ?>–<?php echo min($totalReg, $pagina*$porPagina); ?></strong> de <strong><?php echo number_format($totalReg); ?></strong> rutas<?php echo $hayFiltro ? ' (filtradas)' : ''; ?>
    <?php else: ?>Sin rutas<?php echo $hayFiltro ? ' para el filtro aplicado' : ''; ?><?php endif; ?>
</div>

<?php
// Color de la tarjeta (acento + pill) según estado de la ruta
$estadoColores = [
    'Activa'           => '#059669', // verde
    'En Mantenimiento' => '#F59E0B', // ámbar
    'Inactiva'         => '#DC2626', // rojo
];
?>
<!-- Leyenda de colores -->
<div class="anim-slide-up" style="display:flex; gap:var(--sp-3); flex-wrap:wrap; justify-content:flex-end; margin-bottom:var(--sp-3); font-size:11px; color:var(--text-secondary);">
    <span style="display:flex;align-items:center;gap:5px;"><span style="width:9px;height:9px;border-radius:50%;background:#059669;"></span> Activa</span>
    <span style="display:flex;align-items:center;gap:5px;"><span style="width:9px;height:9px;border-radius:50%;background:#F59E0B;"></span> En Mantenimiento</span>
    <span style="display:flex;align-items:center;gap:5px;"><span style="width:9px;height:9px;border-radius:50%;background:#DC2626;"></span> Inactiva</span>
</div>

<div class="anim-slide-up" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:var(--sp-6); margin-bottom:var(--sp-8);">
    <?php if (empty($data['rutas'])): ?>
        <div style="grid-column:1/-1; text-align:center; padding:var(--sp-12); color:var(--text-tertiary);">
            <i class="bi bi-compass" style="font-size:48px; display:block; margin-bottom:var(--sp-4);"></i>
            <p><?php echo $hayFiltro ? 'No hay rutas que coincidan con el filtro.' : 'No hay rutas turísticas registradas.'; ?></p>
        </div>
    <?php else: ?>
        <?php foreach ($data['rutas'] ?? [] as $r): ?>
            <?php
                $color   = $estadoColores[$r->estado ?? ''] ?? '#64748B';
                $enMant  = ($r->estado === 'En Mantenimiento');
                $salidas = (int)($r->total_salidas ?? 0);
            ?>
            <div class="sig-card act-card h-100" style="border-left-color:<?php echo $color; ?>;">
                <div class="act-card__head">
                    <span class="act-status" style="color:<?php echo $color; ?>; background:<?php echo $color; ?>1f;">
                        <span class="act-status__dot"></span><?php echo htmlspecialchars($r->estado ?? '—'); ?>
                    </span>
                    <span class="act-id">#<?php echo $r->id; ?></span>
                </div>
                <div class="sig-card__body" style="flex:1;">
                    <h3 style="font-size:18px; font-weight:700; color:var(--text-primary); margin-bottom:var(--sp-2); line-height:1.3;">
                        <?php echo htmlspecialchars($r->nombre ?? ''); ?>
                    </h3>
                    <p class="text-clamp-2" style="font-size:13px; color:var(--text-secondary); margin-bottom:var(--sp-3);">
                        <?php echo htmlspecialchars(strip_tags($r->descripcion ?? 'Sin descripción')); ?>
                    </p>
                    <?php if ($enMant && !empty($r->motivo_mantenimiento)): ?>
                    <div class="act-late" style="margin-bottom:12px; white-space:normal; align-items:flex-start;">
                        <i class="bi bi-tools" style="margin-top:1px;"></i> <?php echo htmlspecialchars($r->motivo_mantenimiento); ?>
                    </div>
                    <?php endif; ?>
                    <div class="act-chips">
                        <span class="act-chip"><span class="act-chip__dot"></span><?php echo htmlspecialchars($r->tipo_ruta ?: 'General'); ?></span>
                        <span class="act-chip"><i class="bi bi-pin-map"></i> <?php echo (int)$r->total_puntos; ?> paradas</span>
                        <span class="act-chip" title="Tarifa por persona, en dólares (R-36)">
                            <i class="bi bi-cash-coin"></i> <?php echo htmlspecialchars(Ruta::textoTarifa($r)); ?>
                        </span>
                        <?php if (($r->edad_min ?? null) !== null || ($r->edad_max ?? null) !== null): ?>
                        <span class="act-chip" title="Restricción de edad del recorrido">
                            <i class="bi bi-person-check"></i> <?php echo htmlspecialchars(Ruta::textoEdades($r)); ?>
                        </span>
                        <?php endif; ?>
                        <?php if (!empty($r->restricciones)): ?>
                        <span class="act-chip" title="<?php echo htmlspecialchars($r->restricciones); ?>">
                            <i class="bi bi-shield-exclamation"></i> Con condiciones
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="act-meta-list">
                        <?php if ($r->departamento_nombre): ?>
                        <div class="act-meta"><i class="bi bi-geo-alt"></i><span><?php echo htmlspecialchars($r->departamento_nombre); ?></span></div>
                        <?php endif; ?>
                        <div class="act-meta"><i class="bi bi-clock"></i><span><?php echo htmlspecialchars($r->duracion_estimada ?: 'Duración no definida'); ?></span></div>
                        <div class="act-meta">
                            <i class="bi bi-calendar-event"></i>
                            <span>
                                <?php if ($salidas > 0): ?>
                                    <strong><?php echo $salidas; ?></strong> salida<?php echo $salidas === 1 ? '' : 's'; ?>
                                    <?php if (!empty($r->ultima_salida)): ?>
                                        · última <?php echo date('d/m/Y', strtotime($r->ultima_salida)); ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    Sin salidas todavía
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="act-card__foot">
                    <a href="<?php echo URL_ROOT; ?>/rutas/ruta/<?php echo $r->id; ?>"
                       class="btn-sig btn-sig--ghost btn-sig--sm" style="flex:1; justify-content:center; color:var(--teal-600); border-color:var(--teal-200);">
                        <i class="bi bi-geo"></i> Ver recorrido
                    </a>
                    <div style="display:flex; gap:var(--sp-1);">
                        <button class="row-action row-action--edit" title="Editar"
                                onclick='editarRuta(<?php echo htmlspecialchars(json_encode($r), ENT_QUOTES, "UTF-8"); ?>)'>
                            <i class="bi bi-pencil"></i>
                        </button>
                        <?php if ($salidas === 0): ?>
                        <a href="<?php echo URL_ROOT; ?>/rutas/delete/<?php echo $r->id; ?>"
                           class="row-action row-action--del delete-btn" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </a>
                        <?php else: ?>
                        <span class="row-action" style="opacity:.45;cursor:not-allowed;"
                              title="Esta ruta ya tiene salidas: no se elimina. Márcala como «Inactiva» si dejó de ofrecerse.">
                            <i class="bi bi-trash"></i>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if ($totalPaginas > 1): ?>
<nav class="anim-slide-up" style="display:flex;align-items:center;justify-content:center;gap:6px;margin-bottom:var(--sp-8);flex-wrap:wrap;">
    <?php
    $win = 2;
    $ini = max(1, $pagina - $win);
    $fin = min($totalPaginas, $pagina + $win);
    ?>
    <?php if ($pagina > 1): ?>
        <a class="btn-sig btn-sig--ghost btn-sig--sm" href="<?php echo rutaUrl($flt, 1); ?>"><i class="bi bi-chevron-double-left"></i></a>
        <a class="btn-sig btn-sig--ghost btn-sig--sm" href="<?php echo rutaUrl($flt, $pagina - 1); ?>"><i class="bi bi-chevron-left"></i> Anterior</a>
    <?php endif; ?>
    <?php if ($ini > 1): ?><span style="color:var(--text-tertiary);padding:0 4px;">…</span><?php endif; ?>
    <?php for ($n = $ini; $n <= $fin; $n++): ?>
        <?php if ($n === $pagina): ?>
            <span class="btn-sig btn-sig--primary btn-sig--sm" style="pointer-events:none;min-width:38px;justify-content:center;"><?php echo $n; ?></span>
        <?php else: ?>
            <a class="btn-sig btn-sig--ghost btn-sig--sm" href="<?php echo rutaUrl($flt, $n); ?>" style="min-width:38px;justify-content:center;"><?php echo $n; ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    <?php if ($fin < $totalPaginas): ?><span style="color:var(--text-tertiary);padding:0 4px;">…</span><?php endif; ?>
    <?php if ($pagina < $totalPaginas): ?>
        <a class="btn-sig btn-sig--ghost btn-sig--sm" href="<?php echo rutaUrl($flt, $pagina + 1); ?>">Siguiente <i class="bi bi-chevron-right"></i></a>
        <a class="btn-sig btn-sig--ghost btn-sig--sm" href="<?php echo rutaUrl($flt, $totalPaginas); ?>"><i class="bi bi-chevron-double-right"></i></a>
    <?php endif; ?>
</nav>
<?php endif; ?>

<!-- Modal Ruta -->
<div class="modal fade" id="modalRuta" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form action="<?php echo URL_ROOT; ?>/rutas/store" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRutaLabel">Nueva Ruta Turística</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="rut_id">
                <div class="row g-4">

                    <!-- Nombre + duración -->
                    <div class="col-md-7">
                        <div class="sig-field">
                            <label class="sig-field__label" for="rut_nombre">Nombre de la Ruta <span class="req">*</span></label>
                            <input type="text" name="nombre" id="rut_nombre" class="sig-input" required
                                   minlength="3" placeholder="Ej: Ruta Histórica de Cumaná">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="sig-field">
                            <label class="sig-field__label" for="rut_duracion">Duración <span style="font-size:11px;font-weight:400;color:var(--text-tertiary);">H:MM</span></label>
                            <input type="text" name="duracion_estimada" id="rut_duracion" class="sig-input"
                                   pattern="^\d{1,2}:\d{2}$"
                                   placeholder="Ej: 2:30"
                                   title="Formato H:MM — Ej: 2:30 para 2h y media, 0:45 para 45 min">
                            <div class="invalid-feedback" id="msg_duracion">Formato requerido: H:MM (ej: 2:30)</div>
                        </div>
                    </div>


                    <!-- Descripción -->
                    <div class="col-12">
                        <div class="sig-field">
                            <label class="sig-field__label" for="rut_descripcion">Descripción</label>
                            <textarea name="descripcion" id="rut_descripcion" class="sig-textarea" rows="2"
                                      placeholder="Objetivos del recorrido y atractivos..."></textarea>
                        </div>
                    </div>

                    <!-- Tipo de ruta + Dificultad + Estado -->
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label" for="rut_tipo">Tipo de Ruta <span class="req">*</span></label>
                            <select name="tipo_ruta" id="rut_tipo" class="sig-select">
                                <option value="General">General</option>
                                <option value="Cumaná Histórica">Cumaná Histórica</option>
                                <option value="Exploradores de Cumaná">Exploradores de Cumaná</option>
                                <option value="Comunitaria">Comunitaria</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label" for="rut_estado">Estado</label>
                            <select name="estado" id="rut_estado" class="sig-select">
                                <?php foreach (Ruta::ESTADOS as $est): ?>
                                <option value="<?php echo $est; ?>"><?php echo $est; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="sig-field__hint" style="font-size:11px;">
                                Si la ruta deja de ofrecerse, «Inactiva»: el histórico de sus salidas se conserva.
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label" for="rut_depto">Departamento responsable</label>
                            <select name="id_departamento" id="rut_depto" class="sig-select">
                                <option value="">Sin asignar</option>
                                <?php foreach ($data['departamentos'] ?? [] as $d): ?>
                                    <option value="<?php echo $d->id; ?>"><?php echo htmlspecialchars($d->nombre); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <?php /* Guía, fecha, hora y cupo NO están aquí: son de la SALIDA, no del
                             recorrido (mig. 078). Una ruta se ejecuta muchas veces, cada una con
                             su fecha y sus guías — R-09: hasta dos salidas de la misma ruta en
                             una misma mañana. Se programan desde «Salidas programadas». */ ?>
                    <div class="col-12">
                        <div class="sig-alert sig-alert--info" style="margin:0;">
                            <i class="bi bi-info-circle"></i>
                            <div>
                                Aquí se define <strong>el recorrido</strong>. La fecha, los guías y el
                                grupo de cada visita se registran en
                                <a href="<?php echo URL_ROOT; ?>/rutas/salidas">Salidas programadas</a>.
                            </div>
                        </div>
                    </div>

                    <!-- Tarifa (mig. 083 — T-C, cierra H-14) -->
                    <div class="col-12">
                        <div style="padding:var(--sp-3);background:var(--bg-muted-subtle);border-radius:8px;">
                            <div style="font-size:12px;font-weight:700;color:var(--text-secondary);margin-bottom:var(--sp-2);">
                                <i class="bi bi-cash-coin"></i> Cobro de este recorrido
                            </div>
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <div class="sig-field" style="margin:0;">
                                        <label class="sig-field__label" for="rut_tarifa_modo">¿Se cobra?</label>
                                        <select name="tarifa_modo" id="rut_tarifa_modo" class="sig-select">
                                            <?php foreach (PagoRuta::TARIFA_MODOS as $tm): ?>
                                                <option value="<?php echo $tm; ?>"><?php echo $tm; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4" id="rut_box_monto">
                                    <div class="sig-field" style="margin:0;">
                                        <label class="sig-field__label" for="rut_tarifa_monto">Tarifa por persona (USD)</label>
                                        <input type="number" name="tarifa_monto" id="rut_tarifa_monto" class="sig-input"
                                               min="0" step="0.01" placeholder="Ej: 5.00">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="sig-field" style="margin:0;">
                                        <label class="sig-field__label" for="rut_exon_menores">No se cobra a menores de</label>
                                        <input type="number" name="exonera_menores_de" id="rut_exon_menores" class="sig-input"
                                               min="0" max="120" placeholder="sin exoneración">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="rut_exon_inst" name="exonera_instituciones" value="1">
                                        <label class="form-check-label" for="rut_exon_inst" style="font-size:13px;cursor:pointer;user-select:none;">
                                            <i class="bi bi-building"></i> Las <strong>instituciones públicas</strong> no pagan este recorrido
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <small style="color:var(--text-tertiary);font-size:11px;display:block;margin-top:var(--sp-2);">
                                La tarifa se pacta <strong>en dólares</strong> y se cobra en bolívares a la tasa del día,
                                que se congela en cada salida (R-36).
                                Referencias del cliente: <strong>Cumaná Histórica 5 $</strong> (menores de 8 gratis;
                                instituciones públicas exoneradas) · <strong>Río Brito 15 $</strong> ·
                                <strong>Playa Colorada y Las Maritas 25 $</strong> ·
                                <strong>Exploradores</strong> gratuita · <strong>Altos de Cumaná</strong> a convenir.
                            </small>
                        </div>
                    </div>

                    <!-- Restricciones del recorrido (mig. 079 — T-B/T-I, cierra H-17) -->
                    <div class="col-12">
                        <div style="padding:var(--sp-3);background:var(--bg-muted-subtle);border-radius:8px;">
                            <div style="font-size:12px;font-weight:700;color:var(--text-secondary);margin-bottom:var(--sp-2);">
                                <i class="bi bi-shield-exclamation"></i> Restricciones de este recorrido
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="sig-field" style="margin:0;">
                                        <label class="sig-field__label" for="rut_edad_min">Edad mínima</label>
                                        <input type="number" name="edad_min" id="rut_edad_min" class="sig-input"
                                               min="0" max="120" placeholder="sin mínimo">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="sig-field" style="margin:0;">
                                        <label class="sig-field__label" for="rut_edad_max">Edad máxima</label>
                                        <input type="number" name="edad_max" id="rut_edad_max" class="sig-input"
                                               min="0" max="120" placeholder="sin tope">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="sig-field" style="margin:0;">
                                        <label class="sig-field__label" for="rut_restricciones">Condiciones a advertir</label>
                                        <input type="text" name="restricciones" id="rut_restricciones" class="sig-input"
                                               placeholder="Ej: excluye dificultad visual; advertir condición articular">
                                    </div>
                                </div>
                            </div>
                            <small style="color:var(--text-tertiary);font-size:11px;display:block;margin-top:var(--sp-2);">
                                Déjalo vacío si la ruta no tiene tope.
                                Referencias del cliente: <strong>Exploradores de Cumaná 4–16</strong> ·
                                <strong>Río Brito desde 12</strong>, sin dificultad visual.
                            </small>
                        </div>
                    </div>

                    <!-- Prerequisito de formación (RN-F12) -->
                    <div class="col-12">
                        <div style="padding:var(--sp-3); background:var(--bg-muted-subtle); border-radius:8px;">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="rut_req_form" name="requiere_formacion" value="1">
                                <label class="form-check-label" for="rut_req_form" style="font-size:13px; cursor:pointer; user-select:none;">
                                    <i class="bi bi-mortarboard"></i> Requiere formación previa para inscribirse
                                    <span style="color:var(--text-tertiary); font-size:11px;">(ej: Exploradores de Cumaná)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Motivo de mantenimiento — solo visible cuando estado = En Mantenimiento -->
                    <div id="sec_motivo_mant" class="col-12" style="display:none;">
                        <div class="sig-field" style="margin:0;">
                            <label class="sig-field__label" for="rut_motivo_mant">
                                <i class="bi bi-tools" style="color:#F59E0B;"></i>
                                Motivo de Mantenimiento <span class="req">*</span>
                            </label>
                            <textarea name="motivo_mantenimiento" id="rut_motivo_mant" class="sig-textarea" rows="2"
                                      placeholder="Describa el motivo por el que la ruta pasa a mantenimiento (ej: reparación de sendero, revisión de seguridad)..."></textarea>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn-sig btn-sig--primary" style="background:var(--teal-600);">
                    <i class="bi bi-check-lg"></i> Guardar Ruta
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleMotivoMant(estado) {
    var sec  = document.getElementById('sec_motivo_mant');
    var txt  = document.getElementById('rut_motivo_mant');
    var esMant = (estado === 'En Mantenimiento');
    sec.style.display = esMant ? 'block' : 'none';
    txt.required      = esMant;
    if (!esMant) txt.value = '';
}

function nuevaRuta() {
    document.getElementById('modalRutaLabel').innerText = 'Nueva Ruta Turística';
    document.getElementById('rut_id').value = '';
    document.querySelector('#modalRuta form').reset();
    toggleMotivoMant('Activa');
    toggleTarifaMonto();
}

function editarRuta(r) {
    document.getElementById('modalRutaLabel').innerText   = 'Editar: ' + r.nombre;
    document.getElementById('rut_id').value               = r.id;
    document.getElementById('rut_nombre').value           = r.nombre;
    document.getElementById('rut_descripcion').value      = r.descripcion;
    document.getElementById('rut_duracion').value         = r.duracion_estimada;
    document.getElementById('rut_estado').value           = r.estado;
    document.getElementById('rut_depto').value            = r.id_departamento || '';
    document.getElementById('rut_req_form').checked       = r.requiere_formacion == true || r.requiere_formacion === 't' || r.requiere_formacion === '1';
    document.getElementById('rut_tipo').value             = r.tipo_ruta || 'General';
    document.getElementById('rut_edad_min').value         = (r.edad_min === null || r.edad_min === undefined) ? '' : r.edad_min;
    document.getElementById('rut_edad_max').value         = (r.edad_max === null || r.edad_max === undefined) ? '' : r.edad_max;
    document.getElementById('rut_restricciones').value    = r.restricciones || '';
    document.getElementById('rut_tarifa_modo').value      = r.tarifa_modo || 'Gratuita';
    document.getElementById('rut_tarifa_monto').value     = r.tarifa_monto || '';
    document.getElementById('rut_exon_menores').value     = (r.exonera_menores_de === null || r.exonera_menores_de === undefined) ? '' : r.exonera_menores_de;
    document.getElementById('rut_exon_inst').checked      = r.exonera_instituciones == true || r.exonera_instituciones === 't' || r.exonera_instituciones === '1';
    toggleTarifaMonto();
    // Pre-rellenar motivo de mantenimiento
    document.getElementById('rut_motivo_mant').value      = r.motivo_mantenimiento || '';
    toggleMotivoMant(r.estado);
    new bootstrap.Modal(document.getElementById('modalRuta')).show();
}

// El monto solo tiene sentido con tarifa fija: «a convenir» se pacta en cada
// salida (R-41) y «gratuita» no cobra nada.
function toggleTarifaMonto() {
    var modo = document.getElementById('rut_tarifa_modo').value;
    var box  = document.getElementById('rut_box_monto');
    var esFija = (modo === 'Fija');
    box.style.display = esFija ? 'block' : 'none';
    if (!esFija) document.getElementById('rut_tarifa_monto').value = '';
}
document.getElementById('rut_tarifa_modo').addEventListener('change', toggleTarifaMonto);
toggleTarifaMonto();

// Mostrar/ocultar motivo al cambiar estado en el selector
document.getElementById('rut_estado').addEventListener('change', function() {
    toggleMotivoMant(this.value);
});

// Validación de duración en formato H:MM
document.getElementById('rut_duracion').addEventListener('input', function() {
    var val = this.value.trim();
    var msgEl = document.getElementById('msg_duracion');
    var ok = !val || /^\d{1,2}:\d{2}$/.test(val);
    this.classList.toggle('is-invalid', !ok);
    if (msgEl) msgEl.style.display = ok ? 'none' : 'block';
});

// La validación de fecha se fue con el campo: la fecha es de la salida (mig. 078).

// Submit: bloquear si la duración tiene mal formato
document.querySelector('#modalRuta form').addEventListener('submit', function(e) {
    var durVal = document.getElementById('rut_duracion').value.trim();
    if (durVal && !/^\d{1,2}:\d{2}$/.test(durVal)) {
        e.preventDefault();
        document.getElementById('rut_duracion').classList.add('is-invalid');
        document.getElementById('msg_duracion').style.display = 'block';
    }
});
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
