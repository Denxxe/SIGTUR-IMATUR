<?php require_once '../app/views/inc/header.php'; ?>

<?php
$filtros      = $data['filtros'] ?? [];
$pagina       = (int)($data['pagina'] ?? 1);
$totalPaginas = (int)($data['total_paginas'] ?? 1);
$total        = (int)($data['total'] ?? 0);
$porPagina    = (int)($data['por_pagina'] ?? 20);
$hayFiltro    = array_filter($filtros, fn($v) => $v !== '');

function tallerUrl(array $filtros, int $p): string {
    $q = array_filter($filtros, fn($v) => $v !== '' && $v !== null);
    $q['p'] = $p;
    return URL_ROOT . '/talleres/index?' . http_build_query($q);
}
// Helper: detectar si una actividad Programada está atrasada (fecha/hora de inicio ya pasó)
function esAtrasada($t): bool {
    if (($t->estado ?? '') !== 'Programado') return false;
    $hoy  = date('Y-m-d');
    $hora = date('H:i:s');
    $fi   = $t->fecha_inicio ?? '';
    if ($fi < $hoy) return true;
    if ($fi === $hoy && !empty($t->hora_inicio) && $t->hora_inicio < $hora) return true;
    return false;
}
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Formación · Capacitación</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Gestión de Talleres'; ?></h1>
        <p class="page__subtitle">Administración de talleres y charlas de formación comunitaria.</p>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalTaller" onclick="nuevoTaller()">
            <i class="bi bi-calendar-plus"></i> Programar Actividad
        </button>
    </div>
</div>

<!-- Filtros -->
<form class="sig-card anim-slide-up" method="GET" action="<?php echo URL_ROOT; ?>/talleres/index" style="margin-bottom:var(--sp-4);">
    <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-5); display:flex; align-items:flex-end; gap:var(--sp-3); flex-wrap:wrap;">
        <div style="flex:1; min-width:200px;">
            <label class="sig-field__label" style="font-size:11px;">Buscar</label>
            <div style="position:relative;">
                <i class="bi bi-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-tertiary);font-size:13px;pointer-events:none;"></i>
                <input type="text" name="buscar" class="sig-input" style="padding-left:32px;" placeholder="Nombre o facilitador…" value="<?php echo htmlspecialchars($filtros['buscar'] ?? ''); ?>">
            </div>
        </div>
        <div>
            <label class="sig-field__label" style="font-size:11px;">Estado</label>
            <select name="estado" class="sig-input" style="min-width:140px;">
                <option value="">Todos</option>
                <?php foreach (Taller::ESTADOS as $est): ?>
                <option value="<?php echo $est; ?>" <?php echo ($filtros['estado'] ?? '') === $est ? 'selected' : ''; ?>><?php echo $est; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="sig-field__label" style="font-size:11px;">Tipo</label>
            <select name="tipo" class="sig-input" style="min-width:130px;">
                <option value="">Todos</option>
                <?php foreach (Taller::TIPOS_ACTIVIDAD as $tp): ?>
                <option value="<?php echo $tp; ?>" <?php echo ($filtros['tipo'] ?? '') === $tp ? 'selected' : ''; ?>><?php echo $tp; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="sig-field__label" style="font-size:11px;">Ámbito</label>
            <select name="es_interna" class="sig-input" style="min-width:120px;">
                <option value="">Todos</option>
                <option value="1" <?php echo ($filtros['es_interna'] ?? '') === '1' ? 'selected' : ''; ?>>Interna</option>
                <option value="0" <?php echo ($filtros['es_interna'] ?? '') === '0' ? 'selected' : ''; ?>>Externa</option>
            </select>
        </div>
        <div>
            <label class="sig-field__label" style="font-size:11px;">Desde</label>
            <input type="date" name="fecha_inicio" class="sig-input" style="max-width:148px;" value="<?php echo htmlspecialchars($filtros['fecha_inicio'] ?? ''); ?>">
        </div>
        <div>
            <label class="sig-field__label" style="font-size:11px;">Hasta</label>
            <input type="date" name="fecha_fin" class="sig-input" style="max-width:148px;" value="<?php echo htmlspecialchars($filtros['fecha_fin'] ?? ''); ?>">
        </div>
        <div style="display:flex; gap:var(--sp-2);">
            <button type="submit" class="btn-sig btn-sig--primary" style="height:42px;"><i class="bi bi-funnel"></i> Filtrar</button>
            <?php if ($hayFiltro): ?>
            <a href="<?php echo URL_ROOT; ?>/talleres/index" class="btn-sig btn-sig--ghost" style="height:42px; padding:0 var(--sp-3);" title="Limpiar"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </div>
    </div>
</form>

<!-- Resumen + leyenda de colores -->
<div style="display:flex; align-items:center; justify-content:space-between; gap:var(--sp-3); margin-bottom:var(--sp-4); flex-wrap:wrap;" class="anim-slide-up">
    <span style="font-size:13px; color:var(--text-secondary);">
        <?php if ($total > 0): ?>
            Mostrando <strong><?php echo (($pagina-1)*$porPagina)+1; ?>–<?php echo min($total, $pagina*$porPagina); ?></strong> de <strong><?php echo number_format($total); ?></strong> actividades<?php echo $hayFiltro ? ' (filtradas)' : ''; ?>
        <?php else: ?> Sin actividades<?php echo $hayFiltro ? ' para el filtro aplicado' : ''; ?><?php endif; ?>
    </span>
    <div style="display:flex; gap:var(--sp-2); flex-wrap:wrap; font-size:11px; align-items:center;">
        <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;border-radius:2px;background:#DC2626;display:inline-block;"></span> Atrasada</span>
        <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;border-radius:2px;background:#F59E0B;display:inline-block;"></span> Interna</span>
        <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;border-radius:2px;background:#3B82F6;display:inline-block;"></span> Programada</span>
        <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;border-radius:2px;background:#7C3AED;display:inline-block;"></span> En Curso</span>
        <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;border-radius:2px;background:#059669;display:inline-block;"></span> Finalizada</span>
        <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;border-radius:2px;background:#64748B;display:inline-block;"></span> Cancelada</span>
    </div>
</div>

<div class="anim-slide-up" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:var(--sp-5); margin-bottom:var(--sp-6);">
    <?php if (empty($data['talleres'])): ?>
        <div style="grid-column:1/-1; text-align:center; padding:var(--sp-12); color:var(--text-tertiary);">
            <i class="bi bi-mortarboard" style="font-size:48px; display:block; margin-bottom:var(--sp-4);"></i>
            <p><?php echo $hayFiltro ? 'Sin actividades que coincidan con el filtro.' : 'No hay actividades registradas actualmente.'; ?></p>
        </div>
    <?php else: ?>
        <?php foreach ($data['talleres'] as $t):
            $atrasada = esAtrasada($t);
            $esInterna = !empty($t->es_interna) && $t->es_interna !== 'f';

            // Color del borde izquierdo de la tarjeta según estado/condición
            if ($atrasada)                               $cardBorder = '#DC2626'; // rojo — atrasada
            elseif ($esInterna)                          $cardBorder = '#F59E0B'; // ámbar — interna
            elseif ($t->estado === 'En Curso')           $cardBorder = '#7C3AED'; // morado — en curso
            elseif ($t->estado === 'Programado')         $cardBorder = '#3B82F6'; // azul — programada a tiempo
            elseif ($t->estado === 'Finalizado')         $cardBorder = '#059669'; // verde — finalizada
            else                                         $cardBorder = '#94A3B8'; // gris — cancelada/otro
        ?>
            <div class="sig-card h-100" style="display:flex; flex-direction:column; border-left:4px solid <?php echo $cardBorder; ?>;">
                <div class="sig-card__head" style="padding:var(--sp-3) var(--sp-4); border-bottom:1px solid var(--border-subtle); display:flex; justify-content:space-between; align-items:center;">
                    <?php $badgeClass = Taller::ESTADO_BADGES[$t->estado ?? ''] ?? 'sig-badge--neutral'; ?>
                    <div style="display:flex; gap:var(--sp-2); align-items:center; flex-wrap:wrap;">
                        <span class="sig-badge <?php echo $badgeClass; ?>"><?php echo $t->estado; ?></span>
                        <?php if ($atrasada): ?>
                            <span class="sig-badge sig-badge--danger" style="font-size:10px;"><i class="bi bi-clock-fill"></i> Atrasada</span>
                        <?php endif; ?>
                        <span class="sig-badge sig-badge--neutral" style="font-size:10px;"><?php echo $t->tipo_actividad ?? 'Taller'; ?></span>
                        <?php if ($esInterna): ?>
                            <span class="sig-badge" style="font-size:10px;background:#FEF9C3;color:#92400E;border:1px solid #FDE68A;">Interna</span>
                        <?php else: ?>
                            <span class="sig-badge sig-badge--neutral" style="font-size:10px; color:var(--text-secondary);">
                                <?php echo $t->tipo_ente ? htmlspecialchars($t->tipo_ente) : 'Externa'; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <span style="font-size:11px; color:var(--text-tertiary); font-weight:600;">ID #<?php echo $t->id; ?></span>
                </div>
                <div class="sig-card__body" style="flex:1;">
                    <h3 style="font-size:18px; font-weight:700; color:var(--text-primary); margin-bottom:var(--sp-2); line-height:1.3;"><?php echo $t->nombre ?? 'Actividad sin nombre'; ?></h3>
                    <p class="text-clamp-2" style="font-size:13px; color:var(--text-secondary); margin-bottom:var(--sp-4);">
                        <?php echo strip_tags($t->descripcion ?? 'Sin descripción'); ?>
                    </p>
                    <div style="display:grid; gap:var(--sp-2);">
                        <div style="display:flex; align-items:center; gap:var(--sp-2); font-size:13px; color:var(--text-secondary);">
                            <i class="bi bi-calendar-event" style="color:var(--brand-500);"></i>
                            <span><?php echo date('d/m/Y', strtotime($t->fecha_inicio ?? 'today')); ?></span>
                        </div>
                        <div style="display:flex; align-items:center; gap:var(--sp-2); font-size:13px; color:var(--text-secondary);">
                            <i class="bi bi-geo-alt" style="color:var(--brand-500);"></i>
                            <span><?php echo $t->ubicacion ?? 'Sin asignar'; ?></span>
                        </div>
                        <div style="display:flex; align-items:center; gap:var(--sp-2); font-size:13px; color:var(--text-secondary);">
                            <i class="bi bi-person-badge" style="color:var(--brand-500);"></i>
                            <span><?php echo ($t->facilitador_nombre ?? 'Facilitador') . ' ' . ($t->facilitador_apellido ?? 'Pendiente'); ?></span>
                        </div>
                        <div style="margin-top:var(--sp-2);">
                            <div style="display:flex; justify-content:space-between; font-size:12px; font-weight:600; margin-bottom:4px;">
                                <span>Ocupación</span>
                                <span><?php echo $t->total_inscritos; ?> / <?php echo $t->cupo_maximo; ?></span>
                            </div>
                            <div style="height:6px; background:var(--bg-muted); border-radius:3px; overflow:hidden;">
                                <?php $porcentaje = ($t->cupo_maximo > 0) ? ($t->total_inscritos / $t->cupo_maximo) * 100 : 0; ?>
                                <div style="height:100%; width:<?php echo $porcentaje; ?>%; background:var(--brand-500); border-radius:3px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sig-card__footer" style="padding:var(--sp-4); border-top:1px solid var(--border-subtle); display:flex; gap:var(--sp-2); justify-content:space-between; background:var(--bg-muted-subtle);">
                    <a href="<?php echo URL_ROOT; ?>/talleres/detalle/<?php echo $t->id; ?>" class="btn-sig btn-sig--ghost btn-sig--sm" style="flex:1; justify-content:center;">
                        <i class="bi bi-eye"></i> Detalle
                    </a>
                    <div style="display:flex; gap:var(--sp-1);">
                        <?php if (!in_array($t->estado, ['Finalizado', 'Cancelado'])): ?>
                            <button class="row-action" style="color:var(--brand-600);" title="Cambiar estado"
                                    onclick='abrirCambioEstado(<?php echo $t->id; ?>, "<?php echo $t->estado; ?>", <?php echo (int)$t->total_inscritos; ?>)'>
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                            <button class="row-action row-action--edit" onclick='editarTaller(<?php echo json_encode($t); ?>)'>
                                <i class="bi bi-pencil"></i>
                            </button>
                        <?php endif; ?>
                        <a href="<?php echo URL_ROOT; ?>/talleres/delete/<?php echo $t->id; ?>" class="row-action row-action--del delete-btn">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Paginación -->
<?php if ($totalPaginas > 1):
    $win = 2;
    $ini = max(1, $pagina - $win);
    $fin = min($totalPaginas, $pagina + $win);
?>
<nav class="anim-slide-up" style="display:flex;align-items:center;justify-content:center;gap:6px;margin-bottom:var(--sp-8);flex-wrap:wrap;">
    <?php if ($pagina > 1): ?>
        <a class="btn-sig btn-sig--ghost btn-sig--sm" href="<?php echo tallerUrl($filtros, 1); ?>"><i class="bi bi-chevron-double-left"></i></a>
        <a class="btn-sig btn-sig--ghost btn-sig--sm" href="<?php echo tallerUrl($filtros, $pagina - 1); ?>"><i class="bi bi-chevron-left"></i> Anterior</a>
    <?php endif; ?>
    <?php if ($ini > 1): ?><span style="color:var(--text-tertiary);padding:0 4px;">…</span><?php endif; ?>
    <?php for ($n = $ini; $n <= $fin; $n++): ?>
        <?php if ($n === $pagina): ?>
            <span class="btn-sig btn-sig--primary btn-sig--sm" style="pointer-events:none;min-width:38px;justify-content:center;"><?php echo $n; ?></span>
        <?php else: ?>
            <a class="btn-sig btn-sig--ghost btn-sig--sm" href="<?php echo tallerUrl($filtros, $n); ?>" style="min-width:38px;justify-content:center;"><?php echo $n; ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    <?php if ($fin < $totalPaginas): ?><span style="color:var(--text-tertiary);padding:0 4px;">…</span><?php endif; ?>
    <?php if ($pagina < $totalPaginas): ?>
        <a class="btn-sig btn-sig--ghost btn-sig--sm" href="<?php echo tallerUrl($filtros, $pagina + 1); ?>">Siguiente <i class="bi bi-chevron-right"></i></a>
        <a class="btn-sig btn-sig--ghost btn-sig--sm" href="<?php echo tallerUrl($filtros, $totalPaginas); ?>"><i class="bi bi-chevron-double-right"></i></a>
    <?php endif; ?>
</nav>
<?php endif; ?>

<!-- Modal crear/editar actividad -->
<div class="modal fade" id="modalTaller" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo URL_ROOT; ?>/talleres/store" method="POST" enctype="multipart/form-data" class="modal-content needs-validation" novalidate>
            <div class="modal-header">
                <h5 class="modal-title" id="modalTallerLabel">Programar Actividad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="tal_id">
                <div class="row g-4">

                    <!-- Toggle Interna / Externa -->
                    <div class="col-12">
                        <div id="toggle_inner" style="padding:var(--sp-3) var(--sp-4); background:var(--bg-muted-subtle); border-radius:8px; border:1px solid transparent; transition:background .25s,border-color .25s; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:var(--sp-3);">
                            <div class="form-check form-switch" style="margin:0;">
                                <input class="form-check-input" type="checkbox" id="tal_es_interna" name="es_interna" value="1">
                                <label class="form-check-label" for="tal_es_interna" style="font-size:13px; cursor:pointer; user-select:none;">
                                    <i class="bi bi-building"></i> Actividad interna (para personal IMATUR)
                                </label>
                            </div>
                            <div id="bloque_tipo_ente" style="display:flex; align-items:center; gap:var(--sp-2);">
                                <label class="sig-field__label" style="margin:0; white-space:nowrap;">Dirigida a:</label>
                                <select name="tipo_ente" id="tal_tipo_ente" class="sig-select" style="min-width:180px;">
                                    <option value="">Sin especificar</option>
                                    <option value="Escuela">Escuela</option>
                                    <option value="Liceo">Liceo</option>
                                    <option value="Comunidad">Comunidad / Comuna</option>
                                    <option value="Prestador de Servicio">Prestador de Servicio</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="sig-field">
                            <label class="sig-field__label">Nombre <span class="req">*</span></label>
                            <input type="text" name="nombre" id="tal_nombre" class="sig-input" required placeholder="Ej: Taller de Turismo Sostenible" oninput="checkFormValid();checkDuplicado();">
                        </div>
                    </div>
                    <!-- Aviso de duplicado (visible si AJAX detecta coincidencia) -->
                    <div class="col-12">
                        <div id="aviso_duplicado" style="display:none; padding:var(--sp-3); background:rgba(234,179,8,.12); border-left:3px solid #D97706; border-radius:6px; font-size:13px; color:#92400E;"></div>
                    </div>
                    <div class="col-md-3">
                        <div class="sig-field">
                            <label class="sig-field__label">Tipo <span class="req">*</span></label>
                            <select name="tipo_actividad" id="tal_tipo" class="sig-select" required>
                                <?php foreach (Taller::TIPOS_ACTIVIDAD as $t): ?>
                                <option value="<?php echo $t; ?>"><?php echo $t; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="sig-field">
                            <label class="sig-field__label">Estado <span class="req">*</span></label>
                            <select name="estado" id="tal_estado" class="sig-select" required>
                                <option value="Programado">Programado</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="sig-field">
                            <label class="sig-field__label">Descripción</label>
                            <textarea name="descripcion" id="tal_descripcion" class="sig-textarea" rows="2" placeholder="Objetivos y contenido..."></textarea>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="sig-field">
                            <label class="sig-field__label">Fecha Inicio <span class="req">*</span></label>
                            <input type="date" name="fecha_inicio" id="tal_fecha_inicio" class="sig-input" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="sig-field">
                            <label class="sig-field__label">Fecha Fin</label>
                            <input type="date" name="fecha_fin" id="tal_fecha_fin" class="sig-input">
                            <div class="invalid-feedback" id="msg_fecha_fin"></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="sig-field">
                            <label class="sig-field__label">Hora Inicio</label>
                            <input type="time" name="hora_inicio" id="tal_hora_inicio" class="sig-input">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="sig-field">
                            <label class="sig-field__label">Hora Fin</label>
                            <input type="time" name="hora_fin" id="tal_hora_fin" class="sig-input">
                            <div class="invalid-feedback" id="msg_hora_fin"></div>
                        </div>
                    </div>

                    <div class="col-md-5" id="bloque_sede">
                        <div class="sig-field">
                            <label class="sig-field__label">Sede / Institución</label>
                            <select name="id_ubicacion_formacion" id="tal_ubicacion" class="sig-select">
                                <option value="">Seleccione una sede...</option>
                                <?php foreach ($data['ubicaciones'] ?? [] as $u): ?>
                                    <option value="<?php echo $u->id; ?>"
                                            data-sede-propia="<?php echo !empty($u->es_sede_propia) ? '1' : '0'; ?>">
                                        <?php echo $u->nombre; ?>
                                        <?php if (!empty($u->es_sede_propia)): ?> (IMATUR)<?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="sig-field">
                            <label class="sig-field__label">Facilitador <span class="req">*</span></label>
                            <select name="id_facilitador" id="tal_facilitador" class="sig-select" required>
                                <option value="">Seleccione un facilitador...</option>
                                <?php foreach ($data['empleados'] ?? [] as $e): ?>
                                    <option value="<?php echo $e->id; ?>"><?php echo ($e->nombre ?? '') . ' ' . ($e->apellido ?? ''); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="sig-field">
                            <label class="sig-field__label">Cupo Máx.</label>
                            <input type="number" name="cupo_maximo" id="tal_cupo" class="sig-input" value="30" min="1" max="200">
                        </div>
                    </div>

                    <!-- Motivo cancelación — solo visible al seleccionar Cancelado en edición -->
                    <div id="sec_edit_cancelado" class="col-12" style="display:none;">
                        <div class="sig-field">
                            <label class="sig-field__label">Motivo de cancelación <span class="req">*</span></label>
                            <textarea name="motivo_cancelacion" id="tal_motivo_cancelacion" class="sig-textarea" rows="3"
                                      placeholder="Indique el motivo por el que se cancela esta actividad..."></textarea>
                        </div>
                    </div>

                    <!-- Aviso participantes — visible al seleccionar En Curso en edición -->
                    <div id="sec_edit_en_curso" class="col-12" style="display:none;">
                        <div id="edit_en_curso_msg" style="padding:var(--sp-3); border-radius:8px; border-left:3px solid var(--brand-300); font-size:13px;"></div>
                    </div>

                    <!-- Evidencias — visible al seleccionar Finalizado en edición -->
                    <div id="sec_edit_finalizado" class="col-12" style="display:none;">
                        <div style="background:rgba(239,68,68,.06); border-left:3px solid var(--danger-500); border-radius:6px; padding:var(--sp-3) var(--sp-4); font-size:13px; margin-bottom:var(--sp-3);">
                            <i class="bi bi-exclamation-circle" style="color:var(--danger-600);"></i>
                            <strong style="color:var(--danger-700);">Finalizar actividad</strong> —
                            Se requiere al menos una evidencia fotográfica o en PDF.
                            Si ya las cargó anteriormente, puede continuar sin adjuntar nuevas.
                        </div>
                        <div class="sig-field">
                            <label class="sig-field__label">Adjuntar evidencias <span id="ev_edit_req" style="font-size:11px; color:var(--text-tertiary); font-weight:400;">(obligatorio si no hay evidencias previas)</span></label>
                            <input type="file" name="evidencias[]" id="ev_edit_files" class="sig-input"
                                   multiple accept="image/*,application/pdf">
                            <p style="font-size:11px; color:var(--text-tertiary); margin-top:4px;">
                                <i class="bi bi-info-circle"></i> Imágenes (JPG, PNG, WebP) o PDF. Verifique la previsualización antes de finalizar.
                            </p>
                            <div id="ev_edit_preview" class="ev-preview-grid"></div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" id="btn_guardar" class="btn-sig btn-sig--primary" disabled><i class="bi bi-check-lg"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal cambio rápido de estado -->
<div class="modal fade" id="modalCambioEstado" tabindex="-1">
    <div class="modal-dialog">
        <form id="formCambioEstado" action="" method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-repeat"></i> Cambiar Estado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="display:flex; flex-direction:column; gap:var(--sp-4);">
                <input type="hidden" id="ce_id_taller" name="ce_id_taller">
                <input type="hidden" id="ce_estado_actual" name="ce_estado_actual">

                <div class="sig-field">
                    <label class="sig-field__label">Estado actual</label>
                    <div id="ce_badge_actual" style="margin-top:4px;"></div>
                </div>

                <div class="sig-field">
                    <label class="sig-field__label">Nuevo estado <span class="req">*</span></label>
                    <select id="ce_nuevo_estado" name="nuevo_estado" class="sig-select">
                        <option value="">Seleccione...</option>
                    </select>
                </div>

                <!-- Sección Cancelado -->
                <div id="sec_ce_cancelado" style="display:none;">
                    <div class="sig-field">
                        <label class="sig-field__label">Motivo de cancelación <span class="req">*</span></label>
                        <textarea id="ce_motivo" name="motivo_cancelacion" class="sig-textarea" rows="3"
                                  placeholder="Indique el motivo por el que se cancela..."></textarea>
                    </div>
                </div>

                <!-- Sección En Curso -->
                <div id="sec_ce_en_curso" style="display:none;">
                    <div id="ce_msg_participantes" style="padding:var(--sp-3); background:var(--bg-muted-subtle); border-radius:8px; border-left:3px solid var(--brand-300); font-size:13px;"></div>
                </div>

                <!-- Sección Finalizado — subir evidencias -->
                <div id="sec_ce_finalizado" style="display:none;">
                    <div class="sig-field">
                        <label class="sig-field__label">Evidencias <span class="req">*</span></label>
                        <input type="file" id="ce_evidencias" name="evidencias[]" class="sig-input"
                               multiple accept="image/*,application/pdf">
                        <p style="font-size:11px; color:var(--text-tertiary); margin-top:4px;">
                            <i class="bi bi-info-circle"></i> Imágenes (JPG, PNG, WebP) o PDF. Verifique la previsualización antes de finalizar.
                        </p>
                        <div id="ce_evidencias_preview" class="ev-preview-grid"></div>
                        <div id="ce_evidencias_existentes" data-count="0" style="font-size:12px; color:var(--text-secondary); margin-top:var(--sp-1);"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" id="btn_ce_guardar" class="btn-sig btn-sig--primary" disabled>
                    <i class="bi bi-arrow-repeat"></i> Cambiar Estado
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.sig-input.is-invalid,
.sig-select.is-invalid {
    border-color: var(--danger-500) !important;
    box-shadow: 0 0 0 2px rgba(239,68,68,.12) !important;
}
.sig-input.is-invalid + .invalid-feedback,
.sig-select.is-invalid + .invalid-feedback {
    display: block;
    color: var(--danger-600);
    font-size: 11px;
    margin-top: 4px;
}
#btn_guardar:disabled,
#btn_ce_guardar:disabled {
    opacity: .5;
    cursor: not-allowed;
    pointer-events: none;
}
.ev-preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
    gap: var(--sp-2);
    margin-top: var(--sp-3);
}
.ev-preview-item {
    position: relative;
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    overflow: hidden;
    background: var(--bg-muted-subtle);
    aspect-ratio: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: var(--sp-2);
}
.ev-preview-item img { width: 100%; height: 100%; object-fit: cover; position: absolute; inset: 0; }
.ev-preview-item .ev-pdf { font-size: 1.8rem; color: var(--danger-500); }
.ev-preview-item .ev-name {
    font-size: 9px; color: var(--text-secondary); text-align: center;
    word-break: break-all; line-height: 1.2; margin-top: 4px; z-index: 1;
}
</style>
<script>
// ── Previsualización de evidencias antes de subir ─────────────────────────
function renderEvPreview(input, contId) {
    var cont = document.getElementById(contId);
    if (!cont) return;
    cont.innerHTML = '';
    Array.from(input.files).forEach(function(file) {
        var item = document.createElement('div');
        item.className = 'ev-preview-item';
        if (file.type.startsWith('image/')) {
            var img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.onload = function() { URL.revokeObjectURL(this.src); };
            item.appendChild(img);
        } else {
            item.innerHTML = '<i class="bi bi-file-earmark-pdf ev-pdf"></i>' +
                             '<span class="ev-name">' + file.name.replace(/[<>&]/g,'') + '</span>';
        }
        cont.appendChild(item);
    });
}
// ── Captura inicial de todas las opciones de sede ─────────────────────────
var sedesData = [];
(function() {
    var sel = document.getElementById('tal_ubicacion');
    sedesData = Array.from(sel.options).map(function(opt) {
        return { value: opt.value, text: opt.text, esPropia: opt.dataset.sedePropia === '1' };
    });
})();

// ── Gestión de sede según modo interno/externo ────────────────────────────
// Interna ON  → auto-selecciona la sede IMATUR y oculta el campo visualmente
// Interna OFF → muestra todas las sedes (incluida IMATUR) para elegir libremente
function filtrarSedes(esInterna) {
    var sel    = document.getElementById('tal_ubicacion');
    var bloque = document.getElementById('bloque_sede');
    var valorPrevio = sel.value;
    sel.innerHTML = '';

    if (esInterna) {
        // Poblar solo con sedes propias de IMATUR y auto-seleccionar la primera
        var primeraImatur = null;
        sedesData.forEach(function(d) {
            if (!d.value || !d.esPropia) return;
            var opt = document.createElement('option');
            opt.value = d.value; opt.textContent = d.text;
            opt.dataset.sedePropia = '1';
            sel.appendChild(opt);
            if (!primeraImatur) primeraImatur = d.value;
        });
        if (primeraImatur) sel.value = primeraImatur;
        bloque.style.display = 'none';
    } else {
        // Mostrar todas las sedes (externas e IMATUR — pueden celebrarse en cualquier lugar)
        sedesData.forEach(function(d) {
            var opt = document.createElement('option');
            opt.value = d.value; opt.textContent = d.text;
            if (d.value) opt.dataset.sedePropia = d.esPropia ? '1' : '0';
            sel.appendChild(opt);
        });
        var disponible = Array.from(sel.options).some(function(o) { return o.value === valorPrevio; });
        sel.value = disponible ? valorPrevio : '';
        bloque.style.display = '';
    }
}

// ── Estilo visual del bloque toggle ──────────────────────────────────────
function actualizarEstiloToggle(esInterna) {
    var inner = document.getElementById('toggle_inner');
    if (esInterna) {
        inner.style.background      = 'var(--brand-50)';
        inner.style.borderColor     = 'var(--brand-200)';
        inner.style.borderLeftWidth = '3px';
        inner.style.borderLeftColor = 'var(--brand-500)';
    } else {
        inner.style.background      = 'var(--bg-muted-subtle)';
        inner.style.borderColor     = 'transparent';
        inner.style.borderLeftWidth = '1px';
    }
}

// ── Transiciones válidas de estado (RN-F13) — generadas desde Taller::TRANSICIONES ──
const TRANSICIONES = <?php echo json_encode(Taller::TRANSICIONES); ?>;

function setOpcionesEstado(estadoActual) {
    var sel = document.getElementById('tal_estado');
    sel.innerHTML = '';
    (TRANSICIONES[estadoActual] || [estadoActual]).forEach(function(op) {
        var opt = document.createElement('option');
        opt.value = op; opt.textContent = op;
        if (op === estadoActual) opt.selected = true;
        sel.appendChild(opt);
    });
}

// ── Validación de fechas y horas ──────────────────────────────────────────
function timeToMin(t) {
    var p = t.split(':'); return parseInt(p[0], 10) * 60 + parseInt(p[1], 10);
}

function validarFechas() {
    var fi    = document.getElementById('tal_fecha_inicio').value;
    var ff    = document.getElementById('tal_fecha_fin').value;
    var hi    = document.getElementById('tal_hora_inicio').value;
    var hf    = document.getElementById('tal_hora_fin').value;
    var inFf  = document.getElementById('tal_fecha_fin');
    var inHf  = document.getElementById('tal_hora_fin');
    var msgFf = document.getElementById('msg_fecha_fin');
    var msgHf = document.getElementById('msg_hora_fin');

    inFf.classList.remove('is-invalid'); inHf.classList.remove('is-invalid');
    if (msgFf) msgFf.textContent = ''; if (msgHf) msgHf.textContent = '';

    if (fi && ff && ff < fi) {
        inFf.classList.add('is-invalid');
        if (msgFf) msgFf.textContent = 'La fecha de fin no puede ser anterior a la de inicio.';
        return false;
    }
    // Mismo día (o sin fecha fin): duración mínima 10 min, máxima 5 horas
    if (hi && hf && (!ff || ff === fi)) {
        var dur = timeToMin(hf) - timeToMin(hi);
        if (dur <= 0) {
            inHf.classList.add('is-invalid');
            if (msgHf) msgHf.textContent = 'La hora de fin debe ser posterior a la de inicio.';
            return false;
        }
        if (dur < 10) {
            inHf.classList.add('is-invalid');
            if (msgHf) msgHf.textContent = 'La duración mínima es de 10 minutos.';
            return false;
        }
        if (dur > 300) {
            inHf.classList.add('is-invalid');
            if (msgHf) msgHf.textContent = 'La duración máxima es de 5 horas (300 min). Registre sesiones adicionales si es necesario.';
            return false;
        }
    }
    return true;
}

// ── Mostrar/ocultar secciones condicionales en modal edición ──────────────
function actualizarSeccionesEstadoEdit(estado) {
    var secCancelado  = document.getElementById('sec_edit_cancelado');
    var secEnCurso    = document.getElementById('sec_edit_en_curso');
    var secFinalizado = document.getElementById('sec_edit_finalizado');
    var motivo        = document.getElementById('tal_motivo_cancelacion');

    if (estado === 'Cancelado') {
        secCancelado.style.display = 'block';
        motivo.required = true;
    } else {
        secCancelado.style.display = 'none';
        motivo.required = false;
    }

    if (estado === 'En Curso') {
        var totalInscritos = parseInt(document.getElementById('tal_estado').dataset.totalInscritos || '0', 10);
        var sinInscritos   = totalInscritos === 0;
        var msg = document.getElementById('edit_en_curso_msg');
        msg.style.borderLeftColor = sinInscritos ? 'var(--danger-500)' : 'var(--success-500)';
        msg.style.background      = sinInscritos ? 'rgba(239,68,68,.06)' : 'rgba(34,197,94,.06)';
        var icon  = sinInscritos ? 'bi-exclamation-circle' : 'bi-people';
        var color = sinInscritos ? 'var(--danger-600)' : 'var(--success-600)';
        msg.innerHTML = '<i class="bi ' + icon + '" style="color:' + color + ';"></i> '
            + '<strong>' + totalInscritos + '</strong> participante(s) inscrito(s).'
            + (sinInscritos ? ' <span style="color:var(--danger-600);">Se requiere al menos 1 para iniciar.</span>' : '');
        secEnCurso.style.display = 'block';
    } else {
        secEnCurso.style.display = 'none';
    }

    secFinalizado.style.display = (estado === 'Finalizado') ? 'block' : 'none';
    if (estado !== 'Finalizado') {
        document.getElementById('ev_edit_files').value = '';
        var prev = document.getElementById('ev_edit_preview');
        if (prev) prev.innerHTML = '';
    }
}

// ── Habilitar/deshabilitar botón Guardar ──────────────────────────────────
function checkFormValid() {
    var nombre       = (document.getElementById('tal_nombre').value || '').trim();
    var fechaInicio  = document.getElementById('tal_fecha_inicio').value;
    var facil        = document.getElementById('tal_facilitador').value;
    var fechasOk     = validarFechas();
    var canceladoVis = document.getElementById('sec_edit_cancelado').style.display !== 'none';
    var motivoOk     = !canceladoVis || (document.getElementById('tal_motivo_cancelacion').value || '').trim() !== '';
    var cupo         = parseInt(document.getElementById('tal_cupo').value || '0', 10);
    var cupoOk       = cupo >= 1 && cupo <= 200;
    var estado       = document.getElementById('tal_estado').value;
    var totalInsc    = parseInt(document.getElementById('tal_estado').dataset.totalInscritos || '0', 10);
    var enCursoOk    = estado !== 'En Curso' || totalInsc > 0;

    document.getElementById('btn_guardar').disabled =
        !(nombre !== '' && fechaInicio !== '' && facil !== '' && fechasOk && motivoOk && cupoOk && enCursoOk);
}

// ── Abrir modal — nueva actividad ─────────────────────────────────────────
function nuevoTaller() {
    document.getElementById('modalTallerLabel').innerText = 'Programar Actividad';
    document.getElementById('tal_id').value = '';
    document.querySelector('#modalTaller form').reset();
    document.getElementById('tal_cupo').value = '30';
    document.getElementById('tal_tipo').value = 'Taller';
    setOpcionesEstado('Programado');
    document.getElementById('tal_es_interna').checked = false;
    actualizarModoInterno(false);
    document.getElementById('sec_edit_cancelado').style.display = 'none';
    document.getElementById('sec_edit_en_curso').style.display  = 'none';
    checkFormValid();
}

// ── Abrir modal — editar actividad ────────────────────────────────────────
function editarTaller(t) {
    document.getElementById('modalTallerLabel').innerText = 'Editar: ' + t.nombre;
    document.getElementById('tal_id').value           = t.id;
    document.getElementById('tal_nombre').value       = t.nombre;
    document.getElementById('tal_descripcion').value  = t.descripcion;
    document.getElementById('tal_fecha_inicio').value = t.fecha_inicio;
    document.getElementById('tal_fecha_fin').value    = t.fecha_fin   || '';
    document.getElementById('tal_hora_inicio').value  = t.hora_inicio || '';
    document.getElementById('tal_hora_fin').value     = t.hora_fin    || '';
    document.getElementById('tal_facilitador').value  = t.id_facilitador;
    document.getElementById('tal_cupo').value         = t.cupo_maximo;
    document.getElementById('tal_tipo').value         = t.tipo_actividad || 'Taller';
    document.getElementById('tal_motivo_cancelacion').value = t.motivo_cancelacion || '';

    var esInterna = t.es_interna == true || t.es_interna === 't' || t.es_interna === '1';
    document.getElementById('tal_es_interna').checked = esInterna;
    document.getElementById('tal_tipo_ente').value    = t.tipo_ente || '';

    actualizarEstiloToggle(esInterna);
    document.getElementById('bloque_tipo_ente').style.display = esInterna ? 'none' : 'flex';
    filtrarSedes(esInterna);
    document.getElementById('tal_ubicacion').value = t.id_ubicacion_formacion || '';

    document.getElementById('tal_estado').dataset.totalInscritos = t.total_inscritos || 0;
    setOpcionesEstado(t.estado);
    actualizarSeccionesEstadoEdit(t.estado);
    checkFormValid();
    new bootstrap.Modal(document.getElementById('modalTaller')).show();
}

function actualizarModoInterno(esInterna) {
    document.getElementById('bloque_tipo_ente').style.display = esInterna ? 'none' : 'flex';
    // Al pasar a interna, limpiar tipo_ente para que no persista el valor anterior
    if (esInterna) document.getElementById('tal_tipo_ente').value = '';
    actualizarEstiloToggle(esInterna);
    filtrarSedes(esInterna);
    checkFormValid();
}

// ── Cambio rápido de estado ───────────────────────────────────────────────
// Badges de estado generados desde Taller::ESTADO_BADGES
const ESTADO_BADGES_TALLER = <?php echo json_encode(Taller::ESTADO_BADGES); ?>;
function estadoBadgeClass(estado) {
    return ESTADO_BADGES_TALLER[estado] || 'sig-badge--neutral';
}

function abrirCambioEstado(id, estadoActual, totalInscritos) {
    document.getElementById('ce_id_taller').value      = id;
    document.getElementById('ce_estado_actual').value  = estadoActual;
    document.getElementById('formCambioEstado').action = '<?php echo URL_ROOT; ?>/talleres/cambiarEstado/' + id;

    document.getElementById('ce_badge_actual').innerHTML =
        '<span class="sig-badge ' + estadoBadgeClass(estadoActual) + '">' + estadoActual + '</span>';

    var transiciones = TRANSICIONES[estadoActual] || [];
    var sel = document.getElementById('ce_nuevo_estado');
    sel.innerHTML = '<option value="">Seleccione...</option>';
    transiciones.filter(function(op) { return op !== estadoActual; }).forEach(function(op) {
        var opt = document.createElement('option');
        opt.value = op; opt.textContent = op;
        sel.appendChild(opt);
    });
    sel.dataset.totalInscritos = totalInscritos;

    document.getElementById('sec_ce_cancelado').style.display  = 'none';
    document.getElementById('sec_ce_en_curso').style.display   = 'none';
    document.getElementById('sec_ce_finalizado').style.display = 'none';
    document.getElementById('ce_motivo').value  = '';
    document.getElementById('ce_evidencias').value = '';
    document.getElementById('ce_evidencias_preview').innerHTML = '';
    document.getElementById('ce_evidencias_existentes').innerHTML  = '';
    document.getElementById('ce_evidencias_existentes').dataset.count = '0';
    document.getElementById('btn_ce_guardar').disabled = true;

    new bootstrap.Modal(document.getElementById('modalCambioEstado')).show();
}

function checkCambioEstadoValid() {
    var estado         = document.getElementById('ce_nuevo_estado').value;
    var btn            = document.getElementById('btn_ce_guardar');
    if (!estado) { btn.disabled = true; return; }

    var totalInscritos = parseInt(document.getElementById('ce_nuevo_estado').dataset.totalInscritos || '0', 10);
    var motivo         = (document.getElementById('ce_motivo').value || '').trim();
    var motivoOk       = estado !== 'Cancelado'  || motivo !== '';
    var tieneExist     = parseInt(document.getElementById('ce_evidencias_existentes').dataset.count || '0', 10) > 0;
    var evidenciaOk    = estado !== 'Finalizado' || tieneExist || document.getElementById('ce_evidencias').files.length > 0;
    var inscritosOk    = estado !== 'En Curso'   || totalInscritos > 0;

    // Retroalimentación visual en el campo de motivo
    var campoMotivo = document.getElementById('ce_motivo');
    if (estado === 'Cancelado') {
        campoMotivo.style.borderColor = motivoOk ? '' : 'var(--danger-500)';
        campoMotivo.style.boxShadow   = motivoOk ? '' : '0 0 0 2px rgba(239,68,68,.12)';
    } else {
        campoMotivo.style.borderColor = '';
        campoMotivo.style.boxShadow   = '';
    }

    btn.disabled = !(motivoOk && evidenciaOk && inscritosOk);
}

// ── Event listeners ───────────────────────────────────────────────────────
document.getElementById('tal_es_interna').addEventListener('change', function() {
    actualizarModoInterno(this.checked);
});
document.getElementById('tal_estado').addEventListener('change', function() {
    actualizarSeccionesEstadoEdit(this.value);
    checkFormValid();
});
document.getElementById('tal_nombre').addEventListener('input', checkFormValid);
document.getElementById('tal_nombre').addEventListener('change', checkDuplicado);
document.getElementById('tal_facilitador').addEventListener('change', function() { checkFormValid(); checkDuplicado(); });
document.getElementById('tal_fecha_inicio').addEventListener('change', function() { checkFormValid(); checkDuplicado(); });
document.getElementById('tal_fecha_fin').addEventListener('change', checkFormValid);
document.getElementById('tal_hora_inicio').addEventListener('change', checkFormValid);
document.getElementById('tal_hora_fin').addEventListener('change', checkFormValid);
document.getElementById('tal_motivo_cancelacion').addEventListener('input', checkFormValid);
document.getElementById('tal_cupo').addEventListener('input', checkFormValid);

document.getElementById('ce_nuevo_estado').addEventListener('change', function() {
    var estado         = this.value;
    var totalInscritos = parseInt(this.dataset.totalInscritos || '0', 10);

    document.getElementById('sec_ce_cancelado').style.display  = estado === 'Cancelado'  ? 'block' : 'none';
    document.getElementById('sec_ce_en_curso').style.display   = estado === 'En Curso'   ? 'block' : 'none';
    document.getElementById('sec_ce_finalizado').style.display = estado === 'Finalizado' ? 'block' : 'none';

    if (estado === 'En Curso') {
        var sinInscritos = totalInscritos === 0;
        var color = sinInscritos ? 'var(--danger-600)' : 'var(--success-600)';
        var icon  = sinInscritos ? 'bi-exclamation-circle' : 'bi-check-circle';
        var secEnCurso = document.getElementById('sec_ce_en_curso');
        secEnCurso.querySelector('#ce_msg_participantes').style.borderLeftColor =
            sinInscritos ? 'var(--danger-500)' : 'var(--success-500)';
        secEnCurso.querySelector('#ce_msg_participantes').style.background =
            sinInscritos ? 'rgba(239,68,68,.06)' : 'rgba(34,197,94,.06)';
        document.getElementById('ce_msg_participantes').innerHTML =
            '<i class="bi ' + icon + '" style="color:' + color + ';"></i> ' +
            '<strong>' + totalInscritos + '</strong> participante(s) inscrito(s).' +
            (sinInscritos ? ' <span style="color:var(--danger-600);">Se requiere al menos 1 para iniciar.</span>' : '');
    }
    checkCambioEstadoValid();
});
document.getElementById('ce_motivo').addEventListener('input', checkCambioEstadoValid);
document.getElementById('ce_evidencias').addEventListener('change', function() {
    renderEvPreview(this, 'ce_evidencias_preview');
    checkCambioEstadoValid();
});
var evEditInput = document.getElementById('ev_edit_files');
if (evEditInput) {
    evEditInput.addEventListener('change', function() {
        renderEvPreview(this, 'ev_edit_preview');
    });
}

// ── Verificación de duplicado en tiempo real ────────────────────────────
var dupTimer = null;
function checkDuplicado() {
    clearTimeout(dupTimer);
    dupTimer = setTimeout(function() {
        var nombre = (document.getElementById('tal_nombre').value || '').trim();
        var fecha  = document.getElementById('tal_fecha_inicio').value;
        var facId  = document.getElementById('tal_facilitador').value;
        var talId  = document.getElementById('tal_id').value;
        var aviso  = document.getElementById('aviso_duplicado');
        if (!nombre || !fecha || !facId) { aviso.style.display = 'none'; return; }
        var url = '<?php echo URL_ROOT; ?>/talleres/verificarDuplicado?nombre='
                  + encodeURIComponent(nombre)
                  + '&fecha=' + encodeURIComponent(fecha)
                  + '&id_fac=' + encodeURIComponent(facId)
                  + (talId ? '&excl_id=' + encodeURIComponent(talId) : '');
        fetch(url).then(function(r) { return r.json(); }).then(function(res) {
            if (res.duplicate) {
                aviso.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> '
                    + '<strong>Posible duplicado:</strong> ya existe "'
                    + res.nombre.replace(/[<>&]/g,'') + '" el ' + res.fecha
                    + ' (ID #' + res.id + ', estado: ' + res.estado + '). '
                    + 'Si es una actividad distinta, cambia el nombre o la fecha.';
                aviso.style.display = 'block';
            } else {
                aviso.style.display = 'none';
            }
        }).catch(function() { aviso.style.display = 'none'; });
    }, 500);
}

// Guard contra doble envío del formulario de taller
(function() {
    var form = document.querySelector('#modalTaller form');
    var btn  = document.getElementById('btn_guardar');
    var enviado = false;
    form.addEventListener('submit', function(e) {
        if (!validarFechas()) { e.preventDefault(); return; }
        if (enviado) { e.preventDefault(); return; }
        enviado = true;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando…';
    });
    // Reset al cerrar el modal (por si cancela y vuelve a abrir)
    document.getElementById('modalTaller').addEventListener('hidden.bs.modal', function() {
        enviado = false;
    });
}());
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
