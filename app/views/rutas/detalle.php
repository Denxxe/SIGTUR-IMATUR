<?php require_once '../app/views/inc/header.php'; ?>
<link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/leaflet.min.css">

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/rutas/index" style="color:inherit; text-decoration:none;">Turismo</a> · Detalle de Ruta
        </div>
        <h1 class="page__title"><?php echo htmlspecialchars($data['ruta']->nombre ?? ''); ?></h1>
        <div style="display:flex; gap:var(--sp-4); margin-top:var(--sp-2); font-size:13px; color:var(--text-secondary); flex-wrap:wrap;">
            <?php if ($data['ruta']->fecha_visita): ?>
            <span><strong>Visita:</strong> <?php echo date('d/m/Y', strtotime($data['ruta']->fecha_visita)); ?>
                <?php if ($data['ruta']->hora_visita): ?> a las <?php echo substr($data['ruta']->hora_visita, 0, 5); ?><?php endif; ?>
            </span>
            <?php endif; ?>
            <?php if ($data['ruta']->departamento_nombre): ?>
            <span><strong>Depto:</strong> <?php echo htmlspecialchars($data['ruta']->departamento_nombre); ?></span>
            <?php endif; ?>
            <?php if ($data['ruta']->facilitador_nombre): ?>
            <span><strong>Guía:</strong> <?php echo htmlspecialchars($data['ruta']->facilitador_nombre . ' ' . ($data['ruta']->facilitador_apellido ?? '')); ?></span>
            <?php endif; ?>
            <span><strong>Dificultad:</strong>
                <span class="sig-badge sig-badge--sm sig-badge--neutral"><?php echo $data['ruta']->nivel_dificultad ?? ''; ?></span>
            </span>
            <span><strong>Estado:</strong>
                <?php $sc = $data['ruta']->estado == 'Activa' ? 'sig-badge--success' : 'sig-badge--neutral'; ?>
                <span class="sig-badge sig-badge--sm <?php echo $sc; ?>"><?php echo $data['ruta']->estado ?? ''; ?></span>
            </span>
        </div>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/rutas/index" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <a href="<?php echo URL_ROOT; ?>/rutas/oficio/<?php echo $data['ruta']->id; ?>" class="btn-sig btn-sig--ghost">
            <i class="bi bi-envelope-paper"></i> Generar Oficio
        </a>
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalParticipante">
            <i class="bi bi-person-plus"></i> Añadir Participante
        </button>
        <button type="button" class="btn-sig btn-sig--primary" style="background:var(--teal-600);"
                data-bs-toggle="modal" data-bs-target="#modalPunto" onclick="nuevoPunto()">
            <i class="bi bi-pin-map"></i> Agregar Parada
        </button>
        <button type="button" class="btn-sig btn-sig--primary"
                data-bs-toggle="modal" data-bs-target="#modalInventario">
            <i class="bi bi-box-seam"></i> Asignar Equipo
        </button>
    </div>
</div>

<?php if ($data['ruta']->descripcion ?? ''): ?>
    <div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-4);">
        <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-6);">
            <p style="margin:0; font-size:15px; color:var(--text-secondary); line-height:1.6;">
                <?php echo htmlspecialchars($data['ruta']->descripcion ?? ''); ?>
            </p>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($data['ruta']->requiere_formacion)): ?>
<div style="display:flex; align-items:flex-start; gap:var(--sp-3); background:rgba(245,158,11,.08); border-left:4px solid #D97706; border-radius:6px; padding:var(--sp-3) var(--sp-5); margin-bottom:var(--sp-6);" class="anim-slide-up">
    <i class="bi bi-mortarboard-fill" style="color:#D97706; font-size:1.2rem; flex-shrink:0; margin-top:2px;"></i>
    <div>
        <strong style="color:#92400E; font-size:13px;">Esta ruta requiere formación previa</strong>
        <p style="margin:4px 0 0; font-size:12px; color:#78350F; line-height:1.5;">
            Los participantes deben haber completado al menos una actividad formativa (taller, charla o inducción) para inscribirse.
            Si se trata de un caso excepcional, puede forzar la inscripción desde el formulario de inscripción.
        </p>
    </div>
</div>
<?php endif; ?>

<!-- ── Participantes ── -->
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6); border-top:4px solid var(--teal-500);">
    <div class="sig-card__head" style="display:flex; justify-content:space-between; align-items:center;">
        <div class="sig-card__title">
            <i class="bi bi-people" style="color:var(--teal-500);"></i> Participantes
        </div>
        <?php
        $inscritos  = count($data['participantes'] ?? []);
        $cupo       = $data['ruta']->cupo_maximo ?? 0;
        $porcentaje = ($cupo > 0) ? round(($inscritos / $cupo) * 100) : 0;
        ?>
        <div style="text-align:right;">
            <div style="font-size:12px; font-weight:700; color:var(--text-primary);">
                <?php echo $inscritos; ?> / <?php echo $cupo; ?>
                <span style="color:var(--text-tertiary); font-weight:500;">(<?php echo $porcentaje; ?>%)</span>
            </div>
            <div style="height:4px; width:100px; background:var(--bg-muted); border-radius:2px; margin-top:4px; overflow:hidden;">
                <div style="height:100%; width:<?php echo min($porcentaje,100); ?>%; background:var(--teal-500);"></div>
            </div>
        </div>
    </div>
    <div class="sig-table-wrap">
        <table class="sig-table">
            <thead>
                <tr>
                    <th>Cédula / ID</th>
                    <th>Nombre Completo</th>
                    <th>Teléfono</th>
                    <th class="text-center">Asistencia</th>
                    <th class="col-actions">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['participantes'])): ?>
                    <tr><td colspan="5" class="sig-table-empty">No hay participantes registrados aún.</td></tr>
                <?php else: ?>
                    <?php foreach ($data['participantes'] as $p): ?>
                        <?php $esLibre = empty($p->id_persona); ?>
                        <tr>
                            <td class="cell-id">
                                <?php if ($esLibre): ?>
                                    <?php echo $p->cedula_libre ? htmlspecialchars($p->cedula_libre) : '<em style="color:var(--text-tertiary);">Sin cédula</em>'; ?>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($p->cedula ?? '—'); ?>
                                <?php endif; ?>
                            </td>
                            <td class="cell-strong">
                                <?php if ($esLibre): ?>
                                    <?php echo htmlspecialchars(trim(($p->nombre_libre ?? '') . ' ' . ($p->apellido_libre ?? ''))); ?>
                                    <span class="sig-badge sig-badge--neutral" style="font-size:10px; margin-left:4px;">Niño/a</span>
                                <?php else: ?>
                                    <?php echo htmlspecialchars(($p->nombre ?? '') . ' ' . ($p->apellido ?? '')); ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $esLibre ? '—' : htmlspecialchars($p->telefono ?? '—'); ?></td>
                            <td class="text-center">
                                <?php if ($p->asistio): ?>
                                    <span class="sig-badge sig-badge--success">Asistió</span>
                                <?php else: ?>
                                    <span class="sig-badge sig-badge--neutral">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td class="col-actions">
                                <a href="<?php echo URL_ROOT; ?>/rutas/desinscribir/<?php echo $p->id; ?>"
                                   class="row-action row-action--del delete-btn" title="Quitar participante">
                                    <i class="bi bi-person-dash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Oficios Emitidos ── -->
<?php if (!empty($data['oficiosEmitidos'])): ?>
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6); border-top:3px solid #6366F1;">
    <div class="sig-card__head" style="background:rgba(99,102,241,.04); border-bottom:1px solid var(--border-subtle);">
        <div class="sig-card__title">
            <i class="bi bi-envelope-paper-fill" style="color:#6366F1;"></i> Oficios Emitidos
        </div>
        <a href="<?php echo URL_ROOT; ?>/rutas/oficio/<?php echo $data['ruta']->id; ?>" class="btn-sig btn-sig--ghost btn-sig--sm">
            <i class="bi bi-plus-lg"></i> Nuevo oficio
        </a>
    </div>
    <div class="sig-table-wrap">
        <table class="sig-table">
            <thead>
                <tr>
                    <th style="width:110px;">N° Oficio</th>
                    <th style="width:100px;">Fecha</th>
                    <th>Dirigido a</th>
                    <th>Cargo / Institución</th>
                    <th>Asunto</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['oficiosEmitidos'] as $of): ?>
                <tr>
                    <td class="cell-id" style="font-family:var(--font-mono); font-weight:700; color:#6366F1;">
                        <?php echo htmlspecialchars($of->numero ?? '—'); ?>
                    </td>
                    <td style="font-size:12px; color:var(--text-secondary);">
                        <?php echo $of->fecha ? date('d/m/Y', strtotime($of->fecha)) : '—'; ?>
                    </td>
                    <td class="cell-strong"><?php echo htmlspecialchars($of->destinatario_nombre ?? '—'); ?></td>
                    <td style="font-size:12px; color:var(--text-secondary);"><?php echo htmlspecialchars($of->destinatario_cargo ?? '—'); ?></td>
                    <td style="font-size:12px; color:var(--text-secondary);"><?php echo htmlspecialchars($of->asunto ?? '—'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:var(--sp-4);" class="anim-slide-up">
    <div style="font-size:12px; color:var(--text-tertiary);">
        <i class="bi bi-envelope-paper" style="color:#6366F1;"></i>
        No se ha generado ningún oficio para esta ruta.
    </div>
    <a href="<?php echo URL_ROOT; ?>/rutas/oficio/<?php echo $data['ruta']->id; ?>" class="btn-sig btn-sig--ghost btn-sig--sm">
        <i class="bi bi-envelope-paper"></i> Generar oficio
    </a>
</div>
<?php endif; ?>

<!-- ── Paradas ── -->
<?php
// Detectar órdenes duplicados
$ordenesPuntos = array_column((array)($data['puntos'] ?? []), 'orden');
$duplicados    = array_diff_key($ordenesPuntos, array_unique($ordenesPuntos));
?>
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__head">
        <div class="sig-card__title">
            <i class="bi bi-signpost-split" style="color:var(--teal-500);"></i>
            Itinerario de Paradas
        </div>
        <span style="font-size:11px;color:var(--text-tertiary);"><?php echo count($data['puntos'] ?? []); ?> parada(s) · orden de recorrido</span>
    </div>
    <?php if (!empty($duplicados)): ?>
    <div style="padding:var(--sp-2) var(--sp-4); background:rgba(239,68,68,.07); border-bottom:1px solid rgba(239,68,68,.15); font-size:12px; color:var(--danger-700);">
        <i class="bi bi-exclamation-triangle-fill"></i>
        Existen paradas con órdenes duplicados (<?php echo implode(', ', array_unique($duplicados)); ?>). Edite las paradas para corregirlo.
    </div>
    <?php endif; ?>
    <?php if (empty($data['puntos'])): ?>
    <div style="padding:var(--sp-8); text-align:center; color:var(--text-tertiary);">
        <i class="bi bi-signpost" style="font-size:2rem; display:block; margin-bottom:var(--sp-3);"></i>
        <p style="font-size:13px; margin:0;">Esta ruta aún no tiene paradas definidas. Use "Agregar Parada" para crear el itinerario.</p>
    </div>
    <?php else: ?>
    <div style="padding:var(--sp-4) var(--sp-5);">
        <?php foreach ($data['puntos'] as $i => $p):
            $esUltimo = ($i === count($data['puntos']) - 1);
            $tieneCoordenadas = $p->latitud && $p->longitud;
        ?>
        <div style="display:flex; gap:var(--sp-4); <?php echo !$esUltimo ? 'padding-bottom:var(--sp-4);' : ''; ?>">
            <!-- Indicador de orden (timeline) -->
            <div style="display:flex; flex-direction:column; align-items:center; flex-shrink:0;">
                <div style="width:36px; height:36px; border-radius:50%; background:var(--teal-500); color:white; display:grid; place-items:center; font-weight:800; font-size:14px; flex-shrink:0; box-shadow:0 2px 6px rgba(0,150,136,.25);">
                    <?php echo $p->orden ?? $i+1; ?>
                </div>
                <?php if (!$esUltimo): ?>
                <div style="width:2px; flex:1; background:linear-gradient(to bottom, var(--teal-300), var(--border-subtle)); margin:4px 0;"></div>
                <?php endif; ?>
            </div>
            <!-- Contenido -->
            <div style="flex:1; min-width:0; padding-bottom:<?php echo !$esUltimo?'var(--sp-2)':'0'; ?>;">
                <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:var(--sp-3);">
                    <div style="min-width:0;">
                        <div style="font-weight:700; font-size:14px; color:var(--text-primary); margin-bottom:2px;">
                            <?php echo htmlspecialchars($p->nombre ?? ''); ?>
                        </div>
                        <?php if (!empty($p->descripcion)): ?>
                        <div style="font-size:13px; color:var(--text-secondary); margin-bottom:4px;">
                            <?php echo htmlspecialchars($p->descripcion); ?>
                        </div>
                        <?php endif; ?>
                        <?php if ($tieneCoordenadas): ?>
                        <div style="font-size:11px; color:var(--text-tertiary); font-family:var(--font-mono);">
                            <i class="bi bi-geo-alt" style="color:var(--teal-500);"></i>
                            <?php echo $p->latitud; ?>, <?php echo $p->longitud; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div style="display:flex; gap:4px; flex-shrink:0;">
                        <button class="row-action row-action--edit" onclick='editarPunto(<?php echo htmlspecialchars(json_encode($p), ENT_QUOTES); ?>)' title="Editar parada">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <a href="<?php echo URL_ROOT; ?>/rutas/deletePunto/<?php echo $p->id; ?>/<?php echo $data['ruta']->id; ?>"
                           class="row-action row-action--del delete-btn" title="Eliminar parada">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- ── Equipos ── -->
<div class="sig-card anim-slide-up" style="border-top: 4px solid var(--brand-500);">
    <div class="sig-card__head">
        <div class="sig-card__title"><i class="bi bi-box-seam" style="color:var(--brand-500);"></i> Bienes y Equipos Asignados</div>
    </div>
    <div class="sig-table-wrap">
        <table class="sig-table">
            <thead>
                <tr>
                    <th>Código / Bien</th><th>Condición</th>
                    <th class="text-center">Cantidad</th><th>Observaciones</th>
                    <th class="col-actions">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['inventario_asignado'])): ?>
                    <tr><td colspan="5" class="sig-table-empty">No se han asignado bienes a esta ruta.</td></tr>
                <?php else: ?>
                    <?php foreach ($data['inventario_asignado'] as $inv): ?>
                        <tr>
                            <td>
                                <div class="cell-strong"><?php echo htmlspecialchars($inv->item_nombre); ?></div>
                                <div class="cell-id"><?php echo $inv->codigo_bn ?: 'Sin Código'; ?></div>
                            </td>
                            <td><span class="sig-badge sig-badge--info"><?php echo $inv->condicion; ?></span></td>
                            <td class="text-center" style="font-weight:700;"><?php echo $inv->cantidad; ?></td>
                            <td style="font-size:12px; color:var(--text-secondary);"><?php echo htmlspecialchars($inv->observaciones ?? '—'); ?></td>
                            <td class="col-actions">
                                <a href="<?php echo URL_ROOT; ?>/rutas/deleteInventario/<?php echo $inv->id; ?>/<?php echo $data['ruta']->id; ?>"
                                   class="row-action row-action--del delete-btn">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Modal: Añadir Participante ── -->
<div class="modal fade" id="modalParticipante" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo URL_ROOT; ?>/rutas/inscribir" method="POST" class="modal-content" id="formInscribir">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus"></i> Añadir Participante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_ruta" value="<?php echo $data['ruta']->id; ?>">

                <!-- Toggle tipo participante -->
                <div class="mb-4" style="padding:var(--sp-3); background:var(--bg-muted-subtle); border-radius:8px; display:flex; align-items:center; gap:var(--sp-4);">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="part_es_libre" name="tipo_participante_libre" value="1">
                        <label class="form-check-label" for="part_es_libre" style="font-size:13px; cursor:pointer; user-select:none;">
                            <i class="bi bi-person-badge"></i> Sin cédula (niño/a sin documento de identidad)
                        </label>
                    </div>
                </div>

                <!-- Bloque con cédula -->
                <div id="bloque_cedula_ruta">
                    <div class="sig-field mb-2">
                        <label class="sig-field__label">Cédula <span class="req">*</span></label>
                        <div style="position:relative;">
                            <input type="text" name="cedula_busqueda" id="part_cedula" class="sig-input"
                                   placeholder="V-12345678 o E-12345678" autocomplete="off"
                                   style="padding-right:40px; text-transform:uppercase;">
                            <span id="part_cedula_spinner" style="display:none; position:absolute; right:10px; top:50%; transform:translateY(-50%); color:var(--text-secondary);">
                                <i class="bi bi-hourglass-split"></i>
                            </span>
                        </div>
                    </div>
                    <!-- Feedback de búsqueda -->
                    <div id="part_cedula_feedback" style="display:none; padding:var(--sp-2) var(--sp-3); border-radius:6px; font-size:13px; margin-bottom:var(--sp-3);"></div>
                </div>

                <!-- Bloque sin cédula -->
                <div id="bloque_libre_ruta" style="display:none;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="sig-field">
                                <label class="sig-field__label">Nombre <span class="req">*</span></label>
                                <input type="text" name="nombre_libre" id="part_nombre_libre" class="sig-input" placeholder="Nombre(s)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="sig-field">
                                <label class="sig-field__label">Apellido</label>
                                <input type="text" name="apellido_libre" class="sig-input" placeholder="Apellido(s)">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="sig-field">
                                <label class="sig-field__label">N° ID Escolar <small style="color:var(--text-secondary);">(opcional)</small></label>
                                <input type="text" name="cedula_libre" class="sig-input" placeholder="Si tiene identificación escolar">
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($data['ruta']->requiere_formacion)): ?>
                <!-- Alerta y override RN-F12 — solo visible cuando la búsqueda detecta sin formación -->
                <div id="bloque_sin_formacion" style="display:none; background:rgba(239,68,68,.06); border:1px solid var(--danger-300); border-radius:8px; padding:var(--sp-3) var(--sp-4); margin-bottom:var(--sp-3);">
                    <p style="font-size:13px; color:var(--danger-700); margin:0 0 var(--sp-2); font-weight:600;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        Este participante no tiene formación previa registrada en el sistema.
                    </p>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="part_forzar" name="forzar_inscripcion" value="1">
                        <label class="form-check-label" for="part_forzar" style="font-size:12px; color:var(--danger-700);">
                            Inscribir de todas formas (caso excepcional — quedará registrado)
                        </label>
                    </div>
                </div>
                <?php endif; ?>

                <hr style="margin:var(--sp-4) 0; border-color:var(--border-subtle);">

                <!-- Campos comunes -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="sig-field">
                            <label class="sig-field__label">Institución <small style="color:var(--text-secondary);">(opcional)</small></label>
                            <select name="id_institucion" class="sig-input">
                                <option value="">— Sin institución —</option>
                                <?php foreach ($data['instituciones'] ?? [] as $inst): ?>
                                <option value="<?php echo $inst->id; ?>">
                                    <?php echo htmlspecialchars($inst->nombre); ?>
                                    <?php if ($inst->tipo): ?><small>(<?php echo htmlspecialchars($inst->tipo); ?>)</small><?php endif; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="sig-field">
                            <label class="sig-field__label">Observaciones <small style="color:var(--text-secondary);">(opcional)</small></label>
                            <input type="text" name="observaciones" class="sig-input" placeholder="Notas adicionales">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" id="part_submit_btn" class="btn-sig btn-sig--primary"><i class="bi bi-person-plus"></i> Agregar</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Modal: Punto ── -->
<div class="modal fade" id="modalPunto" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/rutas/storePunto" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPuntoLabel">Agregar Parada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="punto_id" id="pt_id">
                <input type="hidden" name="id_ruta" value="<?php echo $data['ruta']->id ?? ''; ?>">
                <div class="sig-field mb-4">
                    <label class="sig-field__label">Nombre del Punto <span class="req">*</span></label>
                    <input type="text" name="punto_nombre" id="pt_nombre" class="sig-input" required placeholder="Ej: Mirador de la Cruz">
                </div>
                <div class="sig-field mb-4">
                    <label class="sig-field__label">Descripción</label>
                    <textarea name="punto_descripcion" id="pt_descripcion" class="sig-textarea" rows="2"></textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Orden <span class="req">*</span></label>
                            <input type="number" name="orden" id="pt_orden" class="sig-input" value="1" min="1" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Latitud <span style="font-size:10px;font-weight:400;color:var(--text-tertiary);">(-90 a 90)</span></label>
                            <input type="text" name="latitud" id="pt_lat" class="sig-input" placeholder="Ej: 10.4594" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Longitud <span style="font-size:10px;font-weight:400;color:var(--text-tertiary);">(-180 a 180)</span></label>
                            <input type="text" name="longitud" id="pt_lng" class="sig-input" placeholder="Ej: -64.1741" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="button" class="btn-sig btn-sig--ghost btn-sig--sm" onclick="abrirMapa()" style="color:var(--teal-600); border-color:var(--teal-200);">
                            <i class="bi bi-map"></i> Seleccionar en mapa
                        </button>
                        <span style="font-size:11px; color:var(--text-tertiary); margin-left:var(--sp-2);">
                            Clic en el mapa para fijar la coordenada. Tiles cacheados para uso offline.
                        </span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn-sig btn-sig--primary" style="background:var(--teal-600);"><i class="bi bi-check-lg"></i> Guardar Punto</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Modal: Inventario ── -->
<div class="modal fade" id="modalInventario" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/rutas/storeInventario" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asignar Equipamiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_ruta" value="<?php echo $data['ruta']->id ?? ''; ?>">
                <div class="sig-field mb-4">
                    <label class="sig-field__label">Bien a Asignar <span class="req">*</span></label>
                    <select name="id_inventario" class="sig-select" required>
                        <option value="">Seleccione un bien...</option>
                        <?php foreach ($data['inventario_disponible'] ?? [] as $item): ?>
                            <option value="<?php echo $item->id; ?>">
                                <?php echo ($item->codigo_bn ? $item->codigo_bn . ' — ' : '') . htmlspecialchars($item->nombre); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="sig-field mb-4">
                    <label class="sig-field__label">Cantidad <span class="req">*</span></label>
                    <input type="number" name="cantidad" class="sig-input" value="1" min="1" required>
                </div>
                <div class="sig-field">
                    <label class="sig-field__label">Observaciones</label>
                    <textarea name="observaciones" class="sig-textarea" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Asignar Bien</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Modal: Mapa de coordenadas ── -->
<div class="modal fade" id="modalMapa" tabindex="-1" data-bs-backdrop="static" style="z-index:1060;">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:1px solid var(--border-subtle);">
                <h5 class="modal-title">
                    <i class="bi bi-map" style="color:var(--teal-500);"></i>
                    Seleccionar coordenadas — haz clic en el mapa
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:0; position:relative;">
                <div id="mapa_leaflet" style="height:460px; width:100%; z-index:1;"></div>
                <div id="mapa_instruccion" style="position:absolute; top:12px; left:50%; transform:translateX(-50%); z-index:2; background:rgba(0,0,0,.68); color:white; padding:6px 18px; border-radius:20px; font-size:13px; font-weight:600; pointer-events:none; white-space:nowrap;">
                    <i class="bi bi-cursor-fill"></i> Haz clic para fijar la coordenada
                </div>
            </div>
            <div class="modal-footer" style="justify-content:space-between; align-items:center; border-top:1px solid var(--border-subtle); padding:var(--sp-3) var(--sp-5);">
                <div style="font-size:13px; color:var(--text-secondary); display:flex; align-items:center; gap:var(--sp-3);">
                    <i class="bi bi-geo-alt-fill" style="color:var(--teal-500);"></i>
                    Lat: <strong id="mapa_lat_preview" style="color:var(--teal-700); font-family:var(--font-mono);">—</strong>
                    &ensp; Lng: <strong id="mapa_lng_preview" style="color:var(--teal-700); font-family:var(--font-mono);">—</strong>
                    <input type="hidden" id="mapa_lat_val">
                    <input type="hidden" id="mapa_lng_val">
                </div>
                <div style="display:flex; gap:var(--sp-2);">
                    <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn_aplicar_coords" class="btn-sig btn-sig--primary"
                            style="background:var(--teal-600);" onclick="aplicarCoordenadas()" disabled>
                        <i class="bi bi-check-lg"></i> Aplicar coordenadas
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo URL_ROOT; ?>/assets/js/leaflet.min.js"></script>
<script>
(function () {
    const URL_ROOT = '<?php echo URL_ROOT; ?>';
    const elToggle   = document.getElementById('part_es_libre');
    const elCedula   = document.getElementById('part_cedula');
    const elNombre   = document.getElementById('part_nombre_libre');
    const elFeedback = document.getElementById('part_cedula_feedback');
    const elSpinner  = document.getElementById('part_cedula_spinner');
    const elModal    = document.getElementById('modalParticipante');
    const elForm     = document.getElementById('formInscribir');

    elCedula.required = true;

    // ── Toggle sin cédula ────────────────────────────────────────────────────
    elToggle.addEventListener('change', function () {
        const esLibre = this.checked;
        document.getElementById('bloque_cedula_ruta').style.display = esLibre ? 'none' : 'block';
        document.getElementById('bloque_libre_ruta').style.display  = esLibre ? 'block' : 'none';
        elCedula.required = !esLibre;
        elNombre.required = esLibre;
        clearFeedback();
    });

    // ── Formato y búsqueda AJAX de cédula ────────────────────────────────────
    let ajaxTimer = null;

    function normalizarCedula(val) {
        val = val.toUpperCase().replace(/\s/g, '');
        // Si escribe sólo números, agrega prefijo V-
        if (/^\d+$/.test(val)) val = 'V-' + val;
        // Si escribe V123... agrega el guión
        val = val.replace(/^([VEve])(\d)/, '$1-$2');
        return val;
    }

    function clearFeedback() {
        elFeedback.style.display = 'none';
        elFeedback.innerHTML = '';
        elCedula.style.borderColor = '';
        elSpinner.style.display = 'none';
    }

    function showFeedback(found, texto) {
        elSpinner.style.display = 'none';
        if (found) {
            elFeedback.style.background = '#d1fae5';
            elFeedback.style.color = '#065f46';
            elFeedback.innerHTML = '<i class="bi bi-person-check-fill"></i> ' + texto;
            elCedula.style.borderColor = '#10b981';
        } else {
            elFeedback.style.background = '#fee2e2';
            elFeedback.style.color = '#991b1b';
            elFeedback.innerHTML = '<i class="bi bi-person-x-fill"></i> ' + texto;
            elCedula.style.borderColor = '#ef4444';
        }
        elFeedback.style.display = 'block';
    }

    const rutaRequiereFormacion = <?php echo !empty($data['ruta']->requiere_formacion) ? 'true' : 'false'; ?>;
    const elBloqueFormacion = document.getElementById('bloque_sin_formacion');
    const elForzar          = document.getElementById('part_forzar');

    function ocultarFormacionOverride() {
        if (elBloqueFormacion) elBloqueFormacion.style.display = 'none';
        if (elForzar) elForzar.checked = false;
    }

    elCedula.addEventListener('input', function () {
        const norm = normalizarCedula(this.value);
        if (this.value !== norm) {
            const pos = this.selectionStart + (norm.length - this.value.length);
            this.value = norm;
            try { this.setSelectionRange(pos, pos); } catch(e) {}
        }

        clearFeedback();
        ocultarFormacionOverride();
        const cedula = this.value.trim();

        // Validar formato venezolano: prefijo opcional + 6-9 dígitos
        var cedulaN = cedula.toUpperCase().replace(/[\s.\-]/g, '');
        if (!/^[VEJGCP]?\d{6,9}$/.test(cedulaN)) {
            showFeedback(false, '<i class="bi bi-exclamation-circle"></i> Formato no válido. Use V-12345678, E-1234567 o solo números.');
            return;
        }

        clearTimeout(ajaxTimer);
        elSpinner.style.display = 'inline';
        ajaxTimer = setTimeout(function () {
            fetch(URL_ROOT + '/rutas/buscarPersona?cedula=' + encodeURIComponent(cedula))
                .then(r => r.json())
                .then(result => {
                    if (result.found) {
                        if (rutaRequiereFormacion && result.tiene_formacion === false) {
                            // Persona sin formación — mostrar advertencia + override
                            showFeedback(false,
                                'Encontrado: <strong>' + result.nombre + '</strong>' +
                                ' &mdash; <span style="color:#dc2626; font-weight:600;">Sin formación registrada</span>');
                            if (elBloqueFormacion) elBloqueFormacion.style.display = 'block';
                        } else {
                            showFeedback(true, 'Encontrado: <strong>' + result.nombre + '</strong>' +
                                (rutaRequiereFormacion ? ' <i class="bi bi-mortarboard-fill" style="color:#059669;" title="Tiene formación previa"></i>' : ''));
                            ocultarFormacionOverride();
                        }
                    } else {
                        showFeedback(false, 'No se encontró ninguna persona con esa cédula.');
                        ocultarFormacionOverride();
                    }
                })
                .catch(() => { clearFeedback(); ocultarFormacionOverride(); });
        }, 500);
    });

    // ── Reset del modal al cerrar ────────────────────────────────────────────
    elModal.addEventListener('hidden.bs.modal', function () {
        elForm.reset();
        elToggle.checked = false;
        document.getElementById('bloque_cedula_ruta').style.display = 'block';
        document.getElementById('bloque_libre_ruta').style.display  = 'none';
        elCedula.required = true;
        elNombre.required = false;
        clearFeedback();
        ocultarFormacionOverride();
    });
}());

function nuevoPunto() {
    document.getElementById('modalPuntoLabel').innerText = 'Agregar Parada';
    document.getElementById('pt_id').value = '';
    document.getElementById('pt_nombre').value = '';
    document.getElementById('pt_descripcion').value = '';
    document.getElementById('pt_orden').value = <?php echo count($data['puntos'] ?? []) + 1; ?>;
    document.getElementById('pt_lat').value = '';
    document.getElementById('pt_lng').value = '';
}

function editarPunto(p) {
    document.getElementById('modalPuntoLabel').innerText = 'Editar: ' + p.nombre;
    document.getElementById('pt_id').value = p.id;
    document.getElementById('pt_nombre').value = p.nombre;
    document.getElementById('pt_descripcion').value = p.descripcion;
    document.getElementById('pt_orden').value = p.orden;
    document.getElementById('pt_lat').value = p.latitud || '';
    document.getElementById('pt_lng').value = p.longitud || '';
    new bootstrap.Modal(document.getElementById('modalPunto')).show();
}

// ── Leaflet — Selección de coordenadas ───────────────────────────────────────
var _mapaLeaflet = null;
var _mapaMarker  = null;

// Ícono SVG personalizado — pin teal con centro blanco, sin texto ni sombra
var _pinIcon = null;
if (typeof L !== 'undefined') {
    _pinIcon = L.divIcon({
        className : '',          // sin clase extra (evita estilos por defecto)
        html      : '<svg xmlns="http://www.w3.org/2000/svg" width="30" height="40" viewBox="0 0 30 40">' +
                        '<filter id="dropshadow" x="-30%" y="-10%" width="160%" height="160%">' +
                            '<feDropShadow dx="0" dy="2" stdDeviation="2.5" flood-color="rgba(0,0,0,.35)"/>' +
                        '</filter>' +
                        '<path d="M15 0C6.72 0 0 6.72 0 15c0 10.5 15 25 15 25s15-14.5 15-25C30 6.72 23.28 0 15 0z"' +
                              ' fill="#0d9488" filter="url(#dropshadow)"/>' +
                        '<path d="M15 2C7.82 2 2 7.82 2 15c0 9.5 13 22.5 13 22.5S28 24.5 28 15C28 7.82 22.18 2 15 2z"' +
                              ' fill="none" stroke="white" stroke-width="1.5"/>' +
                        '<circle cx="15" cy="14.5" r="5.5" fill="white"/>' +
                        '<circle cx="15" cy="14.5" r="3" fill="#0d9488"/>' +
                    '</svg>',
        iconSize    : [30, 40],
        iconAnchor  : [15, 40],   // punta del pin en el centro-inferior
        popupAnchor : [0, -40],
    });
}

function abrirMapa() {
    var latExist = parseFloat(document.getElementById('pt_lat').value) || null;
    var lngExist = parseFloat(document.getElementById('pt_lng').value) || null;
    var tieneCoords = latExist !== null && lngExist !== null;

    // Inicializar estado del modal
    document.getElementById('mapa_lat_preview').textContent = tieneCoords ? latExist.toFixed(6) : '—';
    document.getElementById('mapa_lng_preview').textContent = tieneCoords ? lngExist.toFixed(6) : '—';
    document.getElementById('mapa_lat_val').value           = tieneCoords ? latExist.toFixed(6) : '';
    document.getElementById('mapa_lng_val').value           = tieneCoords ? lngExist.toFixed(6) : '';
    document.getElementById('btn_aplicar_coords').disabled  = !tieneCoords;
    document.getElementById('mapa_instruccion').style.display = 'block';

    var modalMapaEl = document.getElementById('modalMapa');
    var modalMapa   = new bootstrap.Modal(modalMapaEl, { keyboard: false });
    modalMapa.show();

    modalMapaEl.addEventListener('shown.bs.modal', function _initMap() {
        modalMapaEl.removeEventListener('shown.bs.modal', _initMap);

        // Destruir instancia anterior si existe
        if (_mapaLeaflet) { _mapaLeaflet.remove(); _mapaLeaflet = null; _mapaMarker = null; }

        var cLat  = tieneCoords ? latExist  : 10.4594;   // Cumaná por defecto
        var cLng  = tieneCoords ? lngExist  : -64.1741;
        var zoom  = tieneCoords ? 16 : 13;

        _mapaLeaflet = L.map('mapa_leaflet').setView([cLat, cLng], zoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution : '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom     : 19
        }).addTo(_mapaLeaflet);

        if (tieneCoords) {
            _mapaMarker = L.marker([cLat, cLng], { icon: _pinIcon }).addTo(_mapaLeaflet);
        }

        _mapaLeaflet.on('click', function(e) {
            var lat = e.latlng.lat.toFixed(6);
            var lng = e.latlng.lng.toFixed(6);

            if (_mapaMarker) {
                _mapaMarker.setLatLng(e.latlng);
            } else {
                _mapaMarker = L.marker(e.latlng, { icon: _pinIcon }).addTo(_mapaLeaflet);
            }
            document.getElementById('mapa_lat_preview').textContent = lat;
            document.getElementById('mapa_lng_preview').textContent = lng;
            document.getElementById('mapa_lat_val').value           = lat;
            document.getElementById('mapa_lng_val').value           = lng;
            document.getElementById('btn_aplicar_coords').disabled  = false;
            document.getElementById('mapa_instruccion').style.display = 'none';
        });
    });
}

function aplicarCoordenadas() {
    var lat = document.getElementById('mapa_lat_val').value;
    var lng = document.getElementById('mapa_lng_val').value;
    if (lat && lng) {
        document.getElementById('pt_lat').value = lat;
        document.getElementById('pt_lng').value = lng;
    }
}
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
