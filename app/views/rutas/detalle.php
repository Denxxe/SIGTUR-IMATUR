<?php require_once '../app/views/inc/header.php'; ?>
<link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/leaflet.min.css">
<style>
/* ── Botón de asistencia táctil (escritorio + móvil) ── */
.btn-asistencia-ruta {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 20px; border: 1px solid transparent;
    font-size: 12px; font-weight: 700; cursor: pointer; white-space: nowrap;
    transition: background .15s, border-color .15s, transform .1s;
    min-height: 34px;
}
.btn-asistencia-ruta:active { transform: scale(.96); }
.btn-asistencia-ruta i { font-size: 14px; }
.btn-asistencia-ruta.is-asistio  { background: rgba(16,185,129,.12); color: #059669; border-color: rgba(16,185,129,.35); }
.btn-asistencia-ruta.is-pendiente{ background: var(--bg-muted-subtle); color: var(--text-secondary); border-color: var(--border-subtle); }
.btn-asistencia-ruta.is-asistio:hover  { background: rgba(16,185,129,.20); }
.btn-asistencia-ruta.is-pendiente:hover{ background: var(--bg-muted); }

/* Acciones de fila más táctiles */
.ruta-participantes .row-action { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; font-size: 15px; }

/* ── Responsividad de la tabla de participantes en móvil ── */
@media (max-width: 640px) {
    .ruta-participantes table, .ruta-participantes thead, .ruta-participantes tbody,
    .ruta-participantes th, .ruta-participantes td, .ruta-participantes tr { display: block; }
    .ruta-participantes thead { display: none; }
    .ruta-participantes tr {
        border: 1px solid var(--border-subtle); border-radius: 10px;
        margin-bottom: var(--sp-3); padding: var(--sp-3); background: var(--bg-surface);
    }
    .ruta-participantes td { border: none !important; padding: 4px 0 !important; display: flex; justify-content: space-between; align-items: center; gap: var(--sp-3); }
    .ruta-participantes td::before { content: attr(data-label); font-size: 10px; font-weight: 700; text-transform: uppercase; color: var(--text-tertiary); letter-spacing: .04em; }
    .ruta-participantes td.text-center, .ruta-participantes td.col-actions { justify-content: flex-start; }
}
</style>

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
            <?php if (!empty($data['ruta']->tipo_ruta) && $data['ruta']->tipo_ruta !== 'General'): ?>
            <span><strong>Tipo:</strong>
                <span class="sig-badge sig-badge--sm sig-badge--info"><?php echo htmlspecialchars($data['ruta']->tipo_ruta); ?></span>
            </span>
            <?php endif; ?>
            <span><strong>Estado:</strong>
                <?php
                $sc = Ruta::ESTADO_BADGES[$data['ruta']->estado ?? ''] ?? 'sig-badge--neutral';
                ?>
                <span class="sig-badge sig-badge--sm <?php echo $sc; ?>"><?php echo $data['ruta']->estado ?? ''; ?></span>
            </span>
        </div>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/rutas/index" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <a href="<?php echo URL_ROOT; ?>/rutas/informe/<?php echo $data['ruta']->id; ?>" class="btn-sig btn-sig--ghost">
            <i class="bi bi-file-earmark-text"></i> Informe de Visita
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
    <div class="sig-card__head" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:var(--sp-3);">
        <div style="display:flex; align-items:center; gap:var(--sp-3);">
            <div class="sig-card__title">
                <i class="bi bi-people" style="color:var(--teal-500);"></i> Participantes
            </div>
            <?php if (!empty($data['participantes'])): ?>
            <button type="button" id="btn_asistencia_masiva_ruta" class="btn-sig btn-sig--ghost btn-sig--sm"
                    data-ruta="<?php echo $data['ruta']->id; ?>">
                <i class="bi bi-check2-all"></i> Marcar todos asistieron
            </button>
            <?php endif; ?>
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
    <div class="sig-table-wrap ruta-participantes">
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
                            <td class="cell-id" data-label="Cédula / ID">
                                <?php if ($esLibre): ?>
                                    <?php echo $p->cedula_libre ? htmlspecialchars($p->cedula_libre) : '<em style="color:var(--text-tertiary);">Sin cédula</em>'; ?>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($p->cedula ?? '—'); ?>
                                <?php endif; ?>
                            </td>
                            <td class="cell-strong" data-label="Nombre">
                                <?php if ($esLibre): ?>
                                    <?php echo htmlspecialchars(trim(($p->nombre_libre ?? '') . ' ' . ($p->apellido_libre ?? ''))); ?>
                                    <span class="sig-badge sig-badge--neutral" style="font-size:10px; margin-left:4px;">Niño/a</span>
                                <?php else: ?>
                                    <?php echo htmlspecialchars(($p->nombre ?? '') . ' ' . ($p->apellido ?? '')); ?>
                                <?php endif; ?>
                            </td>
                            <td data-label="Teléfono"><?php echo $esLibre ? '—' : htmlspecialchars($p->telefono ?? '—'); ?></td>
                            <td class="text-center" data-label="Asistencia">
                                <button type="button"
                                    class="btn-asistencia-ruta <?php echo $p->asistio ? 'is-asistio' : 'is-pendiente'; ?>"
                                    data-id="<?php echo $p->id; ?>"
                                    data-asistio="<?php echo $p->asistio ? '1' : '0'; ?>"
                                    title="Clic para cambiar asistencia">
                                    <i class="bi <?php echo $p->asistio ? 'bi-check-circle-fill' : 'bi-circle'; ?>"></i>
                                    <span><?php echo $p->asistio ? 'Asistió' : 'Pendiente'; ?></span>
                                </button>
                            </td>
                            <td class="col-actions" data-label="Acciones">
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
// Detectar órdenes duplicados: contar ocurrencias y quedarse con los que aparecen >1 vez
$ordenesPuntos = array_column((array)($data['puntos'] ?? []), 'orden');
$conteoOrden   = array_count_values(array_map('strval', $ordenesPuntos));
$duplicados    = array_keys(array_filter($conteoOrden, fn($c) => $c > 1));
sort($duplicados, SORT_NUMERIC);
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
        Existen paradas con órdenes duplicados (<?php echo implode(', ', $duplicados); ?>). Edite las paradas para corregirlo.
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
                        <div style="font-size:11px; color:var(--text-secondary); display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                            <span style="font-family:var(--font-mono);">
                                <i class="bi bi-geo-alt-fill" style="color:var(--teal-500);"></i>
                                <?php echo htmlspecialchars($p->latitud); ?>, <?php echo htmlspecialchars($p->longitud); ?>
                            </span>
                            <button type="button" class="btn-sig btn-sig--ghost btn-sig--sm" style="padding:1px 8px; font-size:10px; color:var(--teal-600);"
                                    onclick="verEnMapa(<?php echo (float)$p->latitud; ?>, <?php echo (float)$p->longitud; ?>, '<?php echo htmlspecialchars(addslashes($p->nombre ?? ''), ENT_QUOTES); ?>')">
                                <i class="bi bi-map"></i> Ver en mapa
                            </button>
                        </div>
                        <?php else: ?>
                        <div style="font-size:11px; color:var(--text-tertiary); font-style:italic;">
                            <i class="bi bi-geo"></i> Sin coordenadas registradas
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

<!-- ── Modal: Añadir Participante ── -->
<div class="modal fade" id="modalParticipante" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form action="<?php echo URL_ROOT; ?>/rutas/inscribir" method="POST" class="modal-content" id="formInscribir">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus"></i> Añadir Participante a la Ruta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="display:flex; flex-direction:column; gap:var(--sp-4);">
                <input type="hidden" name="id_ruta" value="<?php echo $data['ruta']->id; ?>">

                <!-- Toggle sin cédula -->
                <div style="padding:var(--sp-3); background:var(--bg-muted-subtle); border-radius:8px;">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="part_es_libre" name="tipo_participante_libre" value="1">
                        <label class="form-check-label" for="part_es_libre" style="font-size:13px; cursor:pointer; user-select:none;">
                            <i class="bi bi-person-x"></i> Menor de edad sin cédula <span style="color:var(--text-tertiary); font-weight:400;">(5 a 11 años)</span>
                        </label>
                    </div>
                </div>

                <!-- ═══ BLOQUE CON CÉDULA ═══════════════════════════════════ -->
                <div id="bloque_cedula_ruta">
                    <div class="row g-3 mb-2">
                        <div class="col-md-8">
                            <div class="sig-field" style="margin:0;">
                                <label class="sig-field__label">
                                    Cédula <span class="req">*</span>
                                    <span style="font-size:11px; color:var(--text-tertiary); font-weight:400; margin-left:4px;">— busca si ya está registrado</span>
                                </label>
                                <input type="text" id="part_cedula" name="cedula_busqueda" class="sig-input"
                                       placeholder="Ej: V-12345678" autocomplete="off" style="text-transform:uppercase;">
                            </div>
                        </div>
                        <div class="col-md-4" style="display:flex; align-items:flex-end;">
                            <button type="button" id="btn_buscar_part" class="btn-sig btn-sig--ghost" style="width:100%;">
                                <i class="bi bi-search" id="ico_buscar_part"></i> Buscar
                            </button>
                        </div>
                    </div>
                    <div id="part_cedula_feedback" style="display:none; margin-bottom:var(--sp-3);"></div>

                    <!-- Datos del participante (visible tras búsqueda) -->
                    <div id="bloque_datos_part" style="display:none;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="sig-field" style="margin:0;">
                                    <label class="sig-field__label">Nombre <span class="req">*</span></label>
                                    <input type="text" name="nombre" id="part_nombre" class="sig-input" placeholder="Ej: Carlos">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="sig-field" style="margin:0;">
                                    <label class="sig-field__label">Apellido <span class="req">*</span></label>
                                    <input type="text" name="apellido" id="part_apellido" class="sig-input" placeholder="Ej: González">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="sig-field" style="margin:0;">
                                    <label class="sig-field__label">Teléfono</label>
                                    <input type="text" name="telefono" id="part_telefono" class="sig-input" placeholder="0412-1234567">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="sig-field" style="margin:0;">
                                    <label class="sig-field__label">Correo electrónico</label>
                                    <input type="email" name="correo" id="part_correo" class="sig-input" placeholder="ejemplo@correo.com">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="sig-field" style="margin:0;">
                                    <label class="sig-field__label">Género</label>
                                    <select name="genero" id="part_genero" class="sig-select">
                                        <option value="">—</option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="sig-field" style="margin:0;">
                                    <label class="sig-field__label">Fecha de nacimiento <span id="part_edad_label" style="color:var(--text-tertiary); font-weight:400;"></span></label>
                                    <input type="date" name="fecha_nacimiento" id="part_fecha_nac" class="sig-input js-edad" data-edad-target="part_edad_label">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="sig-field" style="margin:0;">
                                    <label class="sig-field__label">Parroquia</label>
                                    <select name="parroquia_id" id="part_parroquia" class="sig-select">
                                        <option value="">— Seleccione parroquia —</option>
                                        <?php foreach ($data['parroquias'] ?? [] as $par): ?>
                                            <option value="<?php echo $par->id; ?>">
                                                <?php echo htmlspecialchars($par->nombre); ?>
                                                <?php if (!empty($par->municipio)): ?> (<?php echo htmlspecialchars($par->municipio); ?>)<?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="sig-field" style="margin:0;">
                                    <label class="sig-field__label">Dirección</label>
                                    <input type="text" name="direccion" id="part_direccion" class="sig-input" placeholder="Urb. Las Palmas, Calle 5, Casa 12">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ═══ BLOQUE SIN CÉDULA (menor) ════════════════════════════ -->
                <div id="bloque_libre_ruta" style="display:none;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="sig-field" style="margin:0;">
                                <label class="sig-field__label">Nombre <span class="req">*</span></label>
                                <input type="text" name="nombre_libre" id="part_nombre_libre" class="sig-input" placeholder="Nombre(s)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="sig-field" style="margin:0;">
                                <label class="sig-field__label">Apellido</label>
                                <input type="text" name="apellido_libre" class="sig-input" placeholder="Apellido(s)">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="sig-field" style="margin:0;">
                                <label class="sig-field__label">Fecha de nacimiento <span class="req">*</span> <span id="libre_edad_label" style="color:var(--text-tertiary); font-weight:400;"></span></label>
                                <input type="date" name="fecha_nac_libre" id="libre_fecha_nac" class="sig-input" required
                                       max="<?php echo date('Y-m-d', strtotime('-5 years')); ?>"
                                       min="<?php echo date('Y-m-d', strtotime('-12 years +1 day')); ?>">
                                <span id="libre_edad_error" style="display:none; font-size:11px; color:var(--danger-600); margin-top:2px;"></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="sig-field" style="margin:0;">
                                <label class="sig-field__label">Género</label>
                                <select name="genero_libre" class="sig-select">
                                    <option value="">—</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="sig-field" style="margin:0;">
                                <label class="sig-field__label">N° ID Escolar <small style="color:var(--text-tertiary);">(opcional)</small></label>
                                <input type="text" name="cedula_libre" class="sig-input" placeholder="Si tiene ID escolar"
                                       pattern="^[A-Za-z0-9\-]{3,20}$" title="Letras, números y guiones (3 a 20 caracteres)">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RN-F12 override -->
                <?php if (!empty($data['ruta']->requiere_formacion)): ?>
                <div id="bloque_sin_formacion" style="display:none; background:rgba(239,68,68,.06); border:1px solid var(--danger-300); border-radius:8px; padding:var(--sp-3) var(--sp-4);">
                    <p style="font-size:13px; color:var(--danger-700); margin:0 0 var(--sp-2); font-weight:600;">
                        <i class="bi bi-exclamation-triangle-fill"></i> Este participante no tiene formación previa registrada.
                    </p>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="part_forzar" name="forzar_inscripcion" value="1">
                        <label class="form-check-label" for="part_forzar" style="font-size:12px; color:var(--danger-700);">
                            Inscribir de todas formas (caso excepcional — quedará registrado)
                        </label>
                    </div>
                </div>
                <?php endif; ?>

                <hr style="margin:0; border-color:var(--border-subtle);">

                <!-- Observaciones -->
                <div class="sig-field" style="margin:0;">
                    <label class="sig-field__label">Observaciones <small style="color:var(--text-tertiary);">(opcional)</small></label>
                    <input type="text" name="observaciones" class="sig-input" placeholder="Notas adicionales sobre este participante">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" id="part_submit_btn" class="btn-sig btn-sig--primary" disabled>
                    <i class="bi bi-person-plus"></i> Agregar
                </button>
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
                            Tiles cacheados para uso offline.
                        </span>
                        <!-- Feedback de coordenadas seleccionadas -->
                        <div id="coord_feedback" style="display:none; margin-top:var(--sp-2); font-size:12px; font-weight:600; color:#0d9488; padding:var(--sp-2) var(--sp-3); background:rgba(13,148,136,.08); border:1px solid rgba(13,148,136,.2); border-radius:6px;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" id="btn_guardar_punto" class="btn-sig btn-sig--primary" style="background:var(--teal-600);" disabled>
                    <i class="bi bi-check-lg"></i> Guardar Punto
                </button>
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
                            style="background:var(--teal-600);" onclick="aplicarCoordenadas()" disabled
                            title="Selecciona un punto en el mapa primero">
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
    const URL_ROOT  = '<?php echo URL_ROOT; ?>';
    const rutaRF    = <?php echo !empty($data['ruta']->requiere_formacion) ? 'true' : 'false'; ?>;
    const elModal   = document.getElementById('modalParticipante');
    const elToggle  = document.getElementById('part_es_libre');
    const elCedula  = document.getElementById('part_cedula');
    const elFeedback= document.getElementById('part_cedula_feedback');
    const elSubmit  = document.getElementById('part_submit_btn');
    const elBF      = document.getElementById('bloque_sin_formacion');

    // ── Helpers ───────────────────────────────────────────────────────────────
    function calcEdad(f) {
        if (!f) return null;
        var h=new Date(), n=new Date(f), a=h.getFullYear()-n.getFullYear();
        if (h.getMonth()<n.getMonth()||(h.getMonth()===n.getMonth()&&h.getDate()<n.getDate())) a--;
        return a>=0?a:null;
    }

    function feedback(tipo, html) {
        var c={'ok':'#d1fae5;color:#065f46;border-left:3px solid #10b981',
               'warn':'#fef3c7;color:#92400e;border-left:3px solid #d97706',
               'err':'#fee2e2;color:#991b1b;border-left:3px solid #ef4444',
               'info':'#dbeafe;color:#1e3a5f;border-left:3px solid #3b82f6'}[tipo]||'';
        elFeedback.style.cssText='padding:8px 12px;border-radius:6px;font-size:13px;margin-bottom:12px;background:'+c;
        elFeedback.innerHTML=html; elFeedback.style.display='block';
    }
    function clearFeedback(){elFeedback.style.display='none'; elCedula.style.borderColor='';}
    function hideFormOverride(){if(elBF)elBF.style.display='none'; var f=document.getElementById('part_forzar');if(f)f.checked=false;}

    function setPersona(p, ro) {
        var m={'part_nombre':p.nombre,'part_apellido':p.apellido,'part_telefono':p.telefono,
               'part_correo':p.correo,'part_fecha_nac':p.fecha_nacimiento,'part_direccion':p.direccion};
        Object.entries(m).forEach(function([id,v]){
            var el=document.getElementById(id); if(!el)return;
            el.value=v||''; el.readOnly=ro&&!!v;
        });
        var g=document.getElementById('part_genero'), pr=document.getElementById('part_parroquia');
        if(g){g.value=p.genero||''; g.disabled=ro&&!!p.genero;}
        if(pr){pr.value=p.parroquia_id||''; pr.disabled=ro&&!!p.parroquia_id;}
        var edad=calcEdad(p.fecha_nacimiento);
        var lbl=document.getElementById('part_edad_label');
        if(lbl&&edad!==null) lbl.textContent='· '+edad+' años';
    }
    function resetPersona(){
        ['part_nombre','part_apellido','part_telefono','part_correo','part_fecha_nac','part_direccion'].forEach(function(id){
            var el=document.getElementById(id); if(el){el.value=''; el.readOnly=false;}
        });
        var g=document.getElementById('part_genero'),pr=document.getElementById('part_parroquia');
        if(g){g.value='';g.disabled=false;} if(pr){pr.value='';pr.disabled=false;}
        var lbl=document.getElementById('part_edad_label'); if(lbl) lbl.textContent='';
        document.getElementById('bloque_datos_part').style.display='none';
    }

    // ── Validación submit ─────────────────────────────────────────────────────
    function checkValid() {
        if (elToggle.checked) {
            var nom  = (document.getElementById('part_nombre_libre').value||'').trim();
            var fnac = (document.getElementById('libre_fecha_nac').value||'').trim();
            var edad = fnac ? calcEdad(fnac) : null;
            elSubmit.disabled = !(nom && fnac && edad!==null && edad>=5 && edad<12);
        } else {
            var vis  = document.getElementById('bloque_datos_part').style.display!=='none';
            var nom  = (document.getElementById('part_nombre').value||'').trim();
            var ape  = (document.getElementById('part_apellido').value||'').trim();
            elSubmit.disabled = !(vis && nom && ape);
        }
    }

    // ── Toggle ────────────────────────────────────────────────────────────────
    elToggle.addEventListener('change', function() {
        var libre = this.checked;
        document.getElementById('bloque_cedula_ruta').style.display = libre?'none':'block';
        document.getElementById('bloque_libre_ruta').style.display  = libre?'block':'none';
        if (!libre){clearFeedback(); resetPersona();} hideFormOverride(); checkValid();
    });

    // ── Búsqueda ──────────────────────────────────────────────────────────────
    document.getElementById('btn_buscar_part').addEventListener('click', function() {
        var cedula=(elCedula.value||'').trim();
        var btn=this, ico=document.getElementById('ico_buscar_part');
        clearFeedback(); resetPersona(); hideFormOverride();

        if (!cedula) {
            document.getElementById('bloque_datos_part').style.display='block';
            feedback('info','<i class="bi bi-pencil"></i> Complete los datos para registrar un nuevo participante.');
            checkValid(); return;
        }
        var cn=cedula.toUpperCase().replace(/[\s.\-]/g,'');
        if (!/^[VEJGCP]?\d{6,9}$/.test(cn)) {
            feedback('err','<i class="bi bi-exclamation-circle"></i> Formato no válido. Use V-12345678 o solo números.');
            return;
        }
        btn.disabled=true; ico.className='bi bi-hourglass-split';

        fetch(URL_ROOT+'/rutas/buscarPersona?cedula='+encodeURIComponent(cedula))
            .then(function(r){return r.json();})
            .then(function(res){
                document.getElementById('bloque_datos_part').style.display='block';
                if (res.found) {
                    var p=res.persona;
                    setPersona(p,true);
                    var edaT=p.fecha_nacimiento?'· '+(calcEdad(p.fecha_nacimiento)||'')+' años':'';
                    if (rutaRF&&res.tiene_formacion===false) {
                        feedback('err','<i class="bi bi-person-x-fill"></i> <strong>'+(p.nombre+' '+p.apellido).trim()+'</strong> &mdash; <span style="color:#dc2626;font-weight:600;">Sin formación previa</span>');
                        if(elBF) elBF.style.display='block';
                    } else {
                        var mBadge=rutaRF?' <i class="bi bi-mortarboard-fill" style="color:#059669;"></i>':'';
                        var falt=!p.telefono||!p.correo||!p.genero||!p.fecha_nacimiento;
                        feedback('ok','<i class="bi bi-person-check-fill"></i> <strong>'+(p.nombre+' '+p.apellido).trim()+'</strong> '+edaT+mBadge+(falt?' &mdash; <em>complete los campos vacíos</em>':''));
                    }
                } else {
                    feedback('warn','<i class="bi bi-person-plus"></i> Cédula no registrada &mdash; complete los datos para crear el registro.');
                }
                checkValid();
            })
            .catch(function(){feedback('err','Error al consultar. Intente nuevamente.');})
            .finally(function(){btn.disabled=false; ico.className='bi bi-search';});
    });

    elCedula.addEventListener('keydown', function(e){if(e.key==='Enter'){e.preventDefault();document.getElementById('btn_buscar_part').click();}});

    // ── Listeners para validación en tiempo real ──────────────────────────────
    document.getElementById('part_nombre_libre').addEventListener('input', checkValid);
    document.getElementById('libre_fecha_nac').addEventListener('change', function() {
        var edad=calcEdad(this.value);
        var lbl=document.getElementById('libre_edad_label');
        var errEl=document.getElementById('libre_edad_error');
        errEl.style.display='none';
        if(edad===null){lbl.textContent='';}
        else if(edad<5){lbl.textContent='· '+edad+' años'; errEl.textContent='El participante debe tener al menos 5 años.'; errEl.style.display='block';}
        else if(edad>=12){lbl.textContent='· '+edad+' años'; errEl.textContent='De 12 años en adelante debe registrarse con cédula.'; errEl.style.display='block';}
        else{lbl.textContent='· '+edad+' años (Niño/a)';}
        checkValid();
    });
    ['part_nombre','part_apellido'].forEach(function(id){document.getElementById(id).addEventListener('input',checkValid);});
    document.getElementById('part_fecha_nac').addEventListener('change',function(){
        var edad=calcEdad(this.value);
        var lbl=document.getElementById('part_edad_label');
        if(lbl&&edad!==null) lbl.textContent='· '+edad+' años';
    });

    // ── Reset al cerrar ───────────────────────────────────────────────────────
    elModal.addEventListener('hidden.bs.modal', function() {
        document.getElementById('formInscribir').reset();
        elToggle.checked=false;
        document.getElementById('bloque_cedula_ruta').style.display='block';
        document.getElementById('bloque_libre_ruta').style.display='none';
        clearFeedback(); resetPersona(); hideFormOverride();
        elSubmit.disabled=true;
    });
}());

// ── Asistencia de participantes ───────────────────────────────────────────
function pintarAsistencia(btn, asistio) {
    btn.dataset.asistio = asistio ? '1' : '0';
    btn.className = 'btn-asistencia-ruta ' + (asistio ? 'is-asistio' : 'is-pendiente');
    btn.innerHTML = '<i class="bi ' + (asistio ? 'bi-check-circle-fill' : 'bi-circle') + '"></i><span>' +
                    (asistio ? 'Asistió' : 'Pendiente') + '</span>';
}

document.querySelectorAll('.btn-asistencia-ruta').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id     = this.dataset.id;
        var nuevo  = this.dataset.asistio === '1' ? '0' : '1';
        var self   = this;
        var fd     = new FormData(); fd.append('id', id); fd.append('asistio', nuevo);
        fetch('<?php echo URL_ROOT; ?>/rutas/marcarAsistencia', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(res) { if (res.ok) pintarAsistencia(self, res.asistio); });
    });
});

var btnMasivaRuta = document.getElementById('btn_asistencia_masiva_ruta');
if (btnMasivaRuta) {
    btnMasivaRuta.addEventListener('click', function() {
        if (!confirm('¿Marcar como "Asistió" a todos los participantes pendientes?')) return;
        var self = this;
        var fd   = new FormData(); fd.append('id_ruta', this.dataset.ruta);
        self.disabled = true;
        fetch('<?php echo URL_ROOT; ?>/rutas/marcarAsistenciaMasiva', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.ok) {
                    document.querySelectorAll('.btn-asistencia-ruta[data-asistio="0"]').forEach(function(btn) {
                        pintarAsistencia(btn, true);
                    });
                    self.style.opacity = '0.4';
                }
                self.disabled = false;
            });
    });
}

// ── Validación del formulario de punto ─────────────────────────────────────

function _parseCoord(val) { return val.trim() === '' ? null : parseFloat(val); }

function checkPuntoValid() {
    var nombre  = (document.getElementById('pt_nombre').value || '').trim();
    var orden   = parseInt(document.getElementById('pt_orden').value || '0', 10);
    var latRaw  = document.getElementById('pt_lat').value;
    var lngRaw  = document.getElementById('pt_lng').value;
    var lat     = _parseCoord(latRaw);
    var lng     = _parseCoord(lngRaw);

    var nombreOk = nombre.length >= 1;
    var ordenOk  = orden >= 1;
    // Lat y Lng son opcionales, pero si se ingresan deben estar en rango
    var latOk    = lat === null || (lat >= -90  && lat <= 90);
    var lngOk    = lng === null || (lng >= -180 && lng <= 180);

    // Marcar is-invalid en coords si están fuera de rango
    document.getElementById('pt_lat').classList.toggle('is-invalid', lat !== null && !latOk);
    document.getElementById('pt_lng').classList.toggle('is-invalid', lng !== null && !lngOk);

    var todo = nombreOk && ordenOk && latOk && lngOk;
    var btn  = document.getElementById('btn_guardar_punto');
    if (btn) btn.disabled = !todo;

    // Actualizar feedback de coordenadas
    var fb = document.getElementById('coord_feedback');
    if (!fb) return;
    if (!latOk || !lngOk) {
        fb.innerHTML = '<i class="bi bi-exclamation-triangle-fill" style="color:#dc2626;"></i> '
            + (!latOk ? 'Latitud fuera de rango (-90 a 90). ' : '')
            + (!lngOk ? 'Longitud fuera de rango (-180 a 180).' : '');
        fb.style.cssText = 'display:block;color:#dc2626;background:rgba(220,38,38,.08);border-color:rgba(220,38,38,.25);';
    } else if (lat !== null && lng !== null) {
        fb.innerHTML = '<i class="bi bi-geo-alt-fill"></i> Coordenadas: <strong>' + lat.toFixed(6) + '</strong>, <strong>' + lng.toFixed(6) + '</strong>';
        fb.style.cssText = 'display:block;color:#0d9488;background:rgba(13,148,136,.08);border-color:rgba(13,148,136,.2);';
    } else {
        fb.style.display = 'none';
    }
}

function resetFeedbackCoord() {
    var fb = document.getElementById('coord_feedback');
    if (fb) fb.style.display = 'none';
    document.getElementById('pt_lat').classList.remove('is-invalid');
    document.getElementById('pt_lng').classList.remove('is-invalid');
}

['pt_nombre','pt_orden','pt_lat','pt_lng'].forEach(function(id) {
    document.getElementById(id).addEventListener('input', checkPuntoValid);
});

function nuevoPunto() {
    document.getElementById('modalPuntoLabel').innerText = 'Agregar Parada';
    document.getElementById('pt_id').value          = '';
    document.getElementById('pt_nombre').value      = '';
    document.getElementById('pt_descripcion').value = '';
    document.getElementById('pt_orden').value       = <?php echo count($data['puntos'] ?? []) + 1; ?>;
    document.getElementById('pt_lat').value         = '';
    document.getElementById('pt_lng').value         = '';
    resetFeedbackCoord();
    checkPuntoValid();
}

function editarPunto(p) {
    document.getElementById('modalPuntoLabel').innerText    = 'Editar: ' + p.nombre;
    document.getElementById('pt_id').value                  = p.id;
    document.getElementById('pt_nombre').value              = p.nombre;
    document.getElementById('pt_descripcion').value         = p.descripcion;
    document.getElementById('pt_orden').value               = p.orden;
    document.getElementById('pt_lat').value                 = p.latitud || '';
    document.getElementById('pt_lng').value                 = p.longitud || '';
    // Mostrar feedback de coords si el punto ya las tiene
    var fb = document.getElementById('coord_feedback');
    if (fb && p.latitud && p.longitud) {
        fb.innerHTML = '<i class="bi bi-geo-alt-fill"></i> Coordenadas actuales: ' + p.latitud + ', ' + p.longitud;
        fb.style.display = 'block';
    } else if (fb) {
        fb.style.display = 'none';
    }
    checkPuntoValid();
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

    // Cerrar el modal del mapa programáticamente (data-bs-dismiss no funciona
    // de forma fiable con backdrop=static en modales apilados)
    var mapaModal = bootstrap.Modal.getInstance(document.getElementById('modalMapa'));
    if (mapaModal) mapaModal.hide();

    if (lat && lng) {
        document.getElementById('pt_lat').value = lat;
        document.getElementById('pt_lng').value = lng;
        // Mostrar feedback de coordenadas aplicadas en el formulario del punto
        var fb = document.getElementById('coord_feedback');
        if (fb) {
            fb.innerHTML = '<i class="bi bi-geo-alt-fill"></i> Coordenadas seleccionadas: <strong>' + lat + '</strong>, <strong>' + lng + '</strong>';
            fb.style.display = 'block';
        }
        checkPuntoValid();
    }
}

// ── Ver una parada en el mapa (solo lectura) ──────────────────────────────
function verEnMapa(lat, lng, nombre) {
    document.getElementById('mapa_lat_preview').textContent = lat.toFixed(6);
    document.getElementById('mapa_lng_preview').textContent = lng.toFixed(6);
    document.getElementById('mapa_instruccion').style.display = 'none';
    document.getElementById('btn_aplicar_coords').style.display = 'none'; // modo solo lectura

    var modalMapaEl = document.getElementById('modalMapa');
    var modalMapa   = new bootstrap.Modal(modalMapaEl, { keyboard: true });
    modalMapa.show();

    modalMapaEl.addEventListener('shown.bs.modal', function _initView() {
        modalMapaEl.removeEventListener('shown.bs.modal', _initView);
        if (_mapaLeaflet) { _mapaLeaflet.remove(); _mapaLeaflet = null; _mapaMarker = null; }
        _mapaLeaflet = L.map('mapa_leaflet').setView([lat, lng], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap', maxZoom: 19
        }).addTo(_mapaLeaflet);
        _mapaMarker = L.marker([lat, lng], { icon: _pinIcon }).addTo(_mapaLeaflet);
        if (nombre) _mapaMarker.bindPopup(nombre).openPopup();
    });
    // Restaurar el botón Aplicar al cerrar (para el modo edición posterior)
    modalMapaEl.addEventListener('hidden.bs.modal', function _restore() {
        modalMapaEl.removeEventListener('hidden.bs.modal', _restore);
        document.getElementById('btn_aplicar_coords').style.display = '';
    });
}
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
