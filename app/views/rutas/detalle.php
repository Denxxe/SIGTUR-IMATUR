<?php require_once '../app/views/inc/header.php'; ?>

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

<!-- ── Paradas ── -->
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__head">
        <div class="sig-card__title">Paradas de la Ruta (Orden de recorrido)</div>
    </div>
    <div class="sig-table-wrap">
        <table class="sig-table">
            <thead>
                <tr>
                    <th style="width:80px; text-align:center;">#</th>
                    <th>Nombre del Punto</th>
                    <th>Descripción</th>
                    <th>Coordenadas</th>
                    <th class="col-actions">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['puntos'])): ?>
                    <tr><td colspan="5" class="sig-table-empty">Esta ruta aún no tiene paradas definidas.</td></tr>
                <?php else: ?>
                    <?php foreach ($data['puntos'] as $p): ?>
                        <tr>
                            <td style="text-align:center;">
                                <div style="width:32px; height:32px; background:var(--teal-100); color:var(--teal-700); border-radius:50%; display:grid; place-items:center; font-weight:700; font-size:14px; margin:0 auto; border:2px solid var(--teal-200);">
                                    <?php echo $p->orden ?? ''; ?>
                                </div>
                            </td>
                            <td class="cell-strong"><?php echo htmlspecialchars($p->nombre ?? ''); ?></td>
                            <td style="font-size:13px; color:var(--text-secondary);"><?php echo htmlspecialchars($p->descripcion ?? '—'); ?></td>
                            <td style="font-family:var(--font-mono); font-size:12px; color:var(--text-tertiary);">
                                <?php if ($p->latitud && $p->longitud): ?>
                                    <i class="bi bi-geo-alt"></i> <?php echo $p->latitud . ', ' . $p->longitud; ?>
                                <?php else: ?> — <?php endif; ?>
                            </td>
                            <td class="col-actions">
                                <button class="row-action row-action--edit" onclick='editarPunto(<?php echo json_encode($p); ?>)'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="<?php echo URL_ROOT; ?>/rutas/deletePunto/<?php echo $p->id; ?>/<?php echo $data['ruta']->id; ?>"
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
                            <label class="sig-field__label">Latitud</label>
                            <input type="text" name="latitud" id="pt_lat" class="sig-input">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Longitud</label>
                            <input type="text" name="longitud" id="pt_lng" class="sig-input">
                        </div>
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

        // Solo buscar si tiene formato mínimo: X-NNNNNNN (al menos 5 dígitos)
        if (!/^[VEve]-\d{5,10}$/.test(cedula)) return;

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
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
