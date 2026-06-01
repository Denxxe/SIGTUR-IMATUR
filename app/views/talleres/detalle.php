<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/talleres/index" style="color:inherit; text-decoration:none;">Formación</a> · Detalle de Actividad
        </div>
        <h1 class="page__title"><?php echo htmlspecialchars($data['taller']->nombre ?? ''); ?></h1>
        <div style="display:flex; gap:var(--sp-4); margin-top:var(--sp-2); font-size:13px; color:var(--text-secondary); flex-wrap:wrap; align-items:center;">
            <span><strong>Tipo:</strong> <?php echo $data['taller']->tipo_actividad ?? 'Taller'; ?></span>
            <?php if (!empty($data['taller']->es_interna)): ?>
                <span class="sig-badge sig-badge--brand">Interna</span>
            <?php else: ?>
                <span class="sig-badge sig-badge--neutral">
                    <?php echo !empty($data['taller']->tipo_ente) ? 'Externa · ' . htmlspecialchars($data['taller']->tipo_ente) : 'Externa'; ?>
                </span>
            <?php endif; ?>
            <span><strong>Facilitador:</strong> <?php echo $data['taller']->facilitador_nombre ?? 'N/A'; ?></span>
            <span><strong>Sede:</strong> <?php echo $data['taller']->ubicacion ?? 'N/A'; ?></span>
            <span><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($data['taller']->fecha_inicio ?? 'now')); ?></span>
            <span><strong>Estado:</strong>
                <?php
                $e = $data['taller']->estado ?? '';
                $cls = Taller::ESTADO_BADGES[$e] ?? 'sig-badge--neutral';
                echo "<span class='sig-badge {$cls}'>{$e}</span>";
                ?>
            </span>
        </div>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/talleres/index" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <a href="<?php echo URL_ROOT; ?>/talleres/informe/<?php echo $data['taller']->id; ?>" class="btn-sig btn-sig--ghost">
            <i class="bi bi-file-earmark-text"></i> Informe Oficial
        </a>
        <a href="<?php echo URL_ROOT; ?>/talleres/listaAsistencia/<?php echo $data['taller']->id; ?>"
           class="btn-sig btn-sig--ghost" target="_blank">
            <i class="bi bi-list-check"></i> Lista de Asistencia
        </a>
        <a href="<?php echo URL_ROOT; ?>/reportes/dossier/<?php echo $data['taller']->id; ?>"
           class="btn-sig btn-sig--ghost" target="_blank">
            <i class="bi bi-file-earmark-pdf"></i> Dossier / PDF
        </a>
        <?php if (!in_array($data['taller']->estado ?? '', ['Finalizado', 'Cancelado'])): ?>
            <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalInscripcion">
                <i class="bi bi-person-plus"></i> Agregar Participante
            </button>
        <?php endif; ?>
    </div>
</div>

<?php
$puedeModificar = !in_array($data['taller']->estado ?? '', ['Finalizado', 'Cancelado']);
$esInterna      = !empty($data['taller']->es_interna);
$inscritos      = count($data['participantes'] ?? []);
$cupo           = $data['taller']->cupo_maximo ?? 0;
$porcentaje     = ($cupo > 0) ? round(($inscritos / $cupo) * 100) : 0;
?>
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__head" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:var(--sp-3);">
        <div style="display:flex; align-items:center; gap:var(--sp-3);">
            <div class="sig-card__title">Participantes</div>
            <?php if ($puedeModificar): ?>
            <div style="position:relative;">
                <input type="text" id="filtro_participantes" placeholder="Buscar por nombre o cédula…"
                       class="sig-input" style="font-size:12px; padding:4px 10px; min-width:220px;">
            </div>
            <?php endif; ?>
        </div>
        <div style="display:flex; align-items:center; gap:var(--sp-3); flex-wrap:wrap;">
            <?php if ($puedeModificar && $inscritos > 0): ?>
            <button type="button" id="btn_asistencia_masiva" class="btn-sig btn-sig--ghost btn-sig--sm"
                    data-taller="<?php echo $data['taller']->id; ?>">
                <i class="bi bi-check2-all"></i> Marcar todos asistieron
            </button>
            <?php endif; ?>
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarParticipantesCsv/<?php echo $data['taller']->id; ?>" class="btn-sig btn-sig--ghost btn-sig--sm">
                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar CSV
            </a>
            <div style="text-align:right;">
                <div style="font-size:12px; font-weight:700; color:var(--text-primary);">
                    <?php echo $inscritos; ?> / <?php echo $cupo; ?> <span style="color:var(--text-tertiary); font-weight:500;">(<?php echo $porcentaje; ?>%)</span>
                </div>
                <div style="height:4px; width:100px; background:var(--bg-muted); border-radius:2px; margin-top:4px; overflow:hidden;">
                    <div style="height:100%; width:<?php echo $porcentaje; ?>%; background:var(--brand-500);"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="sig-table-wrap">
        <table class="sig-table">
            <thead>
                <tr>
                    <th>Cédula / ID</th>
                    <th>Nombre Completo</th>
                    <th>Género / Edad</th>
                    <th>Parroquia</th>
                    <th class="text-center">Asistencia</th>
                    <th>Rol</th>
                    <?php if ($puedeModificar): ?>
                    <th class="text-center" style="width:50px;"></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['participantes'])): ?>
                    <tr>
                        <td colspan="<?php echo $puedeModificar ? 7 : 6; ?>" class="sig-table-empty">No hay participantes registrados aún.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['participantes'] as $p): ?>
                        <?php
                        $esLibre = empty($p->id_persona);
                        $generoSrc = $esLibre ? ($p->genero_libre ?? '') : ($p->genero ?? '');
                        $generoLabel = ['M' => 'Masc.', 'F' => 'Fem.', 'O' => 'Otro'][$generoSrc] ?? '—';
                        $fechaNacSrc = $esLibre ? ($p->fecha_nac_libre ?? null) : ($p->fecha_nacimiento ?? null);
                        $edad = null;
                        if (!empty($fechaNacSrc)) {
                            $nac  = new DateTime($fechaNacSrc);
                            $hoy  = new DateTime();
                            $edad = (int)$nac->diff($hoy)->y;
                        }
                        $parroquiaNombre  = $esLibre ? ($p->parroquia_libre_nombre ?? null) : ($p->parroquia_nombre ?? null);
                        $municipioNombre  = $esLibre ? ($p->municipio_libre_nombre ?? null) : ($p->municipio_nombre ?? null);
                        $nombreCompleto   = $esLibre
                            ? htmlspecialchars(trim(($p->nombre_libre ?? '') . ' ' . ($p->apellido_libre ?? '')))
                            : htmlspecialchars(($p->nombre ?? '') . ' ' . ($p->apellido ?? ''));
                        ?>
                        <tr id="fila-pt-<?php echo $p->id; ?>" class="fila-participante">
                            <td class="cell-id" data-buscar="<?php echo htmlspecialchars($esLibre ? ($p->cedula_libre ?? '') : ($p->cedula ?? '')); ?>">
                                <?php if ($esLibre): ?>
                                    <?php echo $p->cedula_libre ? htmlspecialchars($p->cedula_libre) : '<em style="color:var(--text-tertiary);">Sin cédula</em>'; ?>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($p->cedula ?? '—'); ?>
                                <?php endif; ?>
                            </td>
                            <td class="cell-strong" data-buscar="<?php echo $nombreCompleto; ?>">
                                <?php if ($esLibre): ?>
                                    <?php echo $nombreCompleto; ?>
                                    <span class="sig-badge sig-badge--neutral" style="font-size:10px; margin-left:4px;"><?php echo ($edad !== null && $edad >= 12) ? 'Adolesc.' : 'Niño/a'; ?></span>
                                <?php else: ?>
                                    <span style="cursor:pointer; text-decoration:underline dotted; color:inherit;"
                                          class="link-historial"
                                          data-id="<?php echo $p->id_persona; ?>"
                                          data-nombre="<?php echo $nombreCompleto; ?>"
                                          title="Ver historial de actividades">
                                        <?php echo $nombreCompleto; ?>
                                    </span>
                                    <?php if (!empty($p->telefono)): ?>
                                        <span style="display:block; font-size:11px; color:var(--text-tertiary); margin-top:1px;">
                                            <i class="bi bi-telephone" style="font-size:10px;"></i> <?php echo htmlspecialchars($p->telefono); ?>
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px; white-space:nowrap;">
                                <span><?php echo $generoLabel; ?></span>
                                <?php if ($edad !== null): ?>
                                    <span style="color:var(--text-tertiary); margin-left:4px;"><?php echo $edad; ?> años</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px; color:var(--text-secondary);">
                                <?php if (!empty($parroquiaNombre)): ?>
                                    <?php echo htmlspecialchars($parroquiaNombre); ?>
                                    <?php if (!empty($municipioNombre)): ?>
                                        <span style="color:var(--text-tertiary); display:block; font-size:11px;"><?php echo htmlspecialchars($municipioNombre); ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                                <?php if ($esLibre && !empty($p->direccion_libre)): ?>
                                    <span style="color:var(--text-tertiary); display:block; font-size:11px; font-style:italic; white-space:normal;"><?php echo htmlspecialchars($p->direccion_libre); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($puedeModificar && !$esLibre): ?>
                                    <button type="button"
                                        class="btn-asistencia sig-badge <?php echo $p->asistio ? 'sig-badge--success' : 'sig-badge--neutral'; ?>"
                                        data-id="<?php echo $p->id; ?>"
                                        data-asistio="<?php echo $p->asistio ? '1' : '0'; ?>"
                                        title="Click para cambiar asistencia"
                                        style="cursor:pointer; border:none; background:none; padding:0;">
                                        <?php echo $p->asistio ? 'Asistió' : 'Pendiente'; ?>
                                    </button>
                                <?php elseif ($p->asistio): ?>
                                    <span class="sig-badge sig-badge--success">Asistió</span>
                                <?php else: ?>
                                    <span class="sig-badge sig-badge--neutral">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px; color:var(--text-secondary);">
                                <?php if ($esLibre && !empty($p->nombre_docente)): ?>
                                    <span style="display:flex; align-items:center; gap:4px;">
                                        <i class="bi bi-person-badge" style="color:var(--brand-400);"></i>
                                        <?php echo htmlspecialchars($p->nombre_docente); ?>
                                        <?php if (!empty($p->cedula_docente)): ?>
                                            <span style="color:var(--text-tertiary);">(<?php echo htmlspecialchars($p->cedula_docente); ?>)</span>
                                        <?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <?php if ($puedeModificar): ?>
                            <td class="text-center" style="white-space:nowrap;">
                                <button type="button" class="btn-sig btn-sig--ghost btn-sig--sm" title="Editar datos"
                                        style="padding:2px 6px;"
                                        onclick='editarParticipante(<?php echo htmlspecialchars(json_encode($p), ENT_QUOTES); ?>)'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="<?php echo URL_ROOT; ?>/talleres/desinscribir/<?php echo $p->id; ?>"
                                      onsubmit="return confirm('¿Desinscribir a este participante?');" style="margin:0; display:inline;">
                                    <input type="hidden" name="id_taller" value="<?php echo $data['taller']->id; ?>">
                                    <button type="submit" class="btn-sig btn-sig--danger btn-sig--sm" title="Desinscribir" style="padding:2px 6px;">
                                        <i class="bi bi-person-dash"></i>
                                    </button>
                                </form>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- Controles de paginación (50 por página) -->
    <div id="part_paginacion" style="display:none; align-items:center; justify-content:space-between; gap:var(--sp-3); padding:var(--sp-3) var(--sp-4); border-top:1px solid var(--border-subtle); flex-wrap:wrap;">
        <span id="part_pag_info" style="font-size:12px; color:var(--text-secondary);"></span>
        <div style="display:flex; gap:var(--sp-2); align-items:center;">
            <button type="button" id="part_pag_prev" class="btn-sig btn-sig--ghost btn-sig--sm"><i class="bi bi-chevron-left"></i> Anterior</button>
            <span id="part_pag_actual" style="font-size:12px; font-weight:700; color:var(--text-primary); min-width:80px; text-align:center;"></span>
            <button type="button" id="part_pag_next" class="btn-sig btn-sig--ghost btn-sig--sm">Siguiente <i class="bi bi-chevron-right"></i></button>
        </div>
    </div>
</div>

<?php if (($data['taller']->estado ?? '') === 'Cancelado' && !empty($data['taller']->motivo_cancelacion)): ?>
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6); border-left:4px solid var(--danger-500);">
    <div class="sig-card__head">
        <div class="sig-card__title" style="color:var(--danger-600);">
            <i class="bi bi-x-circle"></i> Motivo de Cancelación
        </div>
    </div>
    <div class="sig-card__body">
        <p style="font-size:14px; color:var(--text-primary); white-space:pre-wrap; margin:0;">
            <?php echo htmlspecialchars($data['taller']->motivo_cancelacion); ?>
        </p>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($data['evidencias'])): ?>
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__head">
        <div class="sig-card__title"><i class="bi bi-images"></i> Evidencias</div>
        <div style="font-size:12px; color:var(--text-tertiary);"><?php echo count($data['evidencias']); ?> archivo(s)</div>
    </div>
    <div class="sig-card__body">
        <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(140px,1fr)); gap:var(--sp-3);">
            <?php foreach ($data['evidencias'] as $ev): ?>
                <?php
                $url      = URL_ROOT . '/public/uploads/talleres/' . $ev->archivo;
                $esPdf    = strtolower(pathinfo($ev->archivo, PATHINFO_EXTENSION)) === 'pdf';
                $nombre   = htmlspecialchars($ev->nombre_original);
                $fecha    = date('d/m/Y H:i', strtotime($ev->uploaded_at));
                ?>
                <a href="<?php echo $url; ?>" target="_blank" title="<?php echo $nombre; ?> — <?php echo $fecha; ?>"
                   style="display:flex; flex-direction:column; align-items:center; gap:var(--sp-1); padding:var(--sp-3);
                          background:var(--bg-muted-subtle); border-radius:8px; border:1px solid var(--border-subtle);
                          text-decoration:none; transition:box-shadow .15s;" onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,.1)'" onmouseout="this.style.boxShadow=''">
                    <?php if ($esPdf): ?>
                        <i class="bi bi-file-earmark-pdf" style="font-size:40px; color:var(--danger-500);"></i>
                    <?php else: ?>
                        <img src="<?php echo $url; ?>" alt="<?php echo $nombre; ?>"
                             style="width:100%; height:80px; object-fit:cover; border-radius:4px;">
                    <?php endif; ?>
                    <span style="font-size:10px; color:var(--text-secondary); text-align:center; word-break:break-all; max-width:100%;">
                        <?php echo mb_strimwidth($ev->nombre_original, 0, 28, '…'); ?>
                    </span>
                    <span style="font-size:9px; color:var(--text-tertiary);"><?php echo $fecha; ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal agregar participante -->
<div class="modal fade" id="modalInscripcion" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo URL_ROOT; ?>/talleres/inscribir" method="POST" class="modal-content" id="formInscripcion">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus"></i> Agregar Participante
                    <span class="sig-badge <?php echo $esInterna ? 'sig-badge--brand' : 'sig-badge--neutral'; ?>" style="font-size:11px; margin-left:8px; vertical-align:middle;">
                        <?php echo $esInterna ? 'Actividad Interna' : 'Actividad Externa'; ?>
                    </span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="display:flex; flex-direction:column; gap:var(--sp-4);">
                <input type="hidden" name="id_taller" value="<?php echo $data['taller']->id; ?>">
                <input type="hidden" name="es_interna_taller" value="<?php echo $esInterna ? '1' : '0'; ?>">

                <?php if ($esInterna): ?>
                <!-- ── BLOQUE INTERNO: selección de empleado ─────────────── -->
                <div style="padding:var(--sp-3); background:rgba(var(--brand-rgb,.22,.48,.86),.06); border-left:3px solid var(--brand-500); border-radius:6px; font-size:13px; color:var(--text-secondary);">
                    <i class="bi bi-info-circle"></i> Las actividades internas se inscriben directamente desde el registro de empleados.
                </div>
                <div class="sig-field">
                    <label class="sig-field__label">Empleado <span class="req">*</span></label>
                    <select name="id_empleado_persona" id="sel_empleado" class="sig-select">
                        <option value="">— Seleccione empleado —</option>
                        <?php foreach ($data['empleados'] as $emp): ?>
                            <option value="<?php echo $emp->id_persona; ?>">
                                <?php echo htmlspecialchars($emp->nombre . ' ' . $emp->apellido); ?>
                                <?php if (!empty($emp->cedula)): ?> — <?php echo htmlspecialchars($emp->cedula); ?><?php endif; ?>
                                <?php if (!empty($emp->cargo)): ?> (<?php echo htmlspecialchars($emp->cargo); ?>)<?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php else: ?>
                <!-- ── BLOQUE EXTERNO ────────────────────────────────────── -->

                <!-- Toggle niño/a sin cédula (RN-F16) -->
                <div style="padding:var(--sp-3); background:var(--bg-muted-subtle); border-radius:8px;">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="insc_es_libre" name="tipo_participante_libre" value="1">
                        <label class="form-check-label" for="insc_es_libre" style="font-size:13px; cursor:pointer; user-select:none;">
                            <i class="bi bi-person-x"></i> Menor de edad sin cédula <span style="color:var(--text-tertiary); font-weight:400;">(5 a 11 años)</span>
                        </label>
                    </div>
                </div>

                <!-- ── BLOQUE PERSONA CON CÉDULA ─────────────────────────── -->
                <div id="bloque_persona">
                    <div class="row g-3" style="margin-bottom:var(--sp-1);">
                        <div class="col-md-8">
                            <div class="sig-field" style="margin:0;">
                                <label class="sig-field__label">
                                    Cédula
                                    <span style="font-size:11px; color:var(--text-tertiary); font-weight:400; margin-left:4px;">— busca si ya está registrado, o completa para registrar</span>
                                </label>
                                <input type="text" id="insc_cedula_busqueda" name="cedula_participante"
                                       class="sig-input" placeholder="Ej: V-12345678" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-4" style="display:flex; align-items:flex-end;">
                            <button type="button" id="btn_buscar_cedula" class="btn-sig btn-sig--ghost" style="width:100%;">
                                <i class="bi bi-search" id="ico_buscar"></i> Buscar
                            </button>
                        </div>
                    </div>
                    <div id="insc_status" style="display:none;"></div>
                    <div id="bloque_datos_persona" style="display:none;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="sig-field">
                                    <label class="sig-field__label">Nombre <span class="req">*</span></label>
                                    <input type="text" name="nombre" id="insc_nombre" class="sig-input" placeholder="Ej: Carlos">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="sig-field">
                                    <label class="sig-field__label">Apellido <span class="req">*</span></label>
                                    <input type="text" name="apellido" id="insc_apellido" class="sig-input" placeholder="Ej: González">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="sig-field">
                                    <label class="sig-field__label">Teléfono</label>
                                    <input type="text" name="telefono" id="insc_telefono" class="sig-input" placeholder="0412-1234567">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="sig-field">
                                    <label class="sig-field__label">Correo electrónico</label>
                                    <input type="email" name="correo" id="insc_correo" class="sig-input" placeholder="ejemplo@correo.com">
                                    <div class="invalid-feedback" id="msg_correo"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="sig-field">
                                    <label class="sig-field__label">Género</label>
                                    <select name="genero" id="insc_genero" class="sig-select">
                                        <option value="">—</option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                        <option value="O">Otro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="sig-field">
                                    <label class="sig-field__label">Fecha de nacimiento <span id="insc_edad_label" style="color:var(--text-tertiary); font-weight:400;"></span></label>
                                    <input type="date" name="fecha_nacimiento" id="insc_fecha_nac" class="sig-input">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="sig-field">
                                    <label class="sig-field__label">Parroquia</label>
                                    <select name="parroquia_id" id="insc_parroquia" class="sig-select">
                                        <option value="">— Seleccione parroquia —</option>
                                        <?php foreach ($data['parroquias'] as $par): ?>
                                            <option value="<?php echo $par->id; ?>">
                                                <?php echo htmlspecialchars($par->nombre); ?>
                                                <?php if (!empty($par->municipio)): ?> (<?php echo htmlspecialchars($par->municipio); ?>)<?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="sig-field">
                                    <label class="sig-field__label">Dirección</label>
                                    <input type="text" name="direccion" id="insc_direccion" class="sig-input" placeholder="Ej: Urb. Las Palmas, Calle 5, Casa 12">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── BLOQUE LIBRE — niños/as (RN-F16) ─────────────────── -->
                <div id="bloque_libre" style="display:none;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="sig-field">
                                <label class="sig-field__label">Nombre <span class="req">*</span></label>
                                <input type="text" name="nombre_libre" id="insc_nombre_libre" class="sig-input" placeholder="Ej: Carlos">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="sig-field">
                                <label class="sig-field__label">Apellido</label>
                                <input type="text" name="apellido_libre" class="sig-input" placeholder="Ej: González">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="sig-field">
                                <label class="sig-field__label">Fecha de nacimiento <span class="req">*</span> <span id="libre_edad_label" style="color:var(--text-tertiary); font-weight:400;"></span></label>
                                <input type="date" name="fecha_nac_libre" id="libre_fecha_nac" class="sig-input" required
                                       max="<?php echo date('Y-m-d', strtotime('-5 years')); ?>"
                                       min="<?php echo date('Y-m-d', strtotime('-12 years +1 day')); ?>">
                                <span id="libre_edad_error" style="display:none; font-size:11px; color:var(--danger-600); margin-top:2px;"></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="sig-field">
                                <label class="sig-field__label">Género</label>
                                <select name="genero_libre" class="sig-select">
                                    <option value="">—</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                    <option value="O">Otro</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="sig-field">
                                <label class="sig-field__label">N° ID Escolar</label>
                                <input type="text" name="cedula_libre" class="sig-input" placeholder="Opcional">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="sig-field">
                                <label class="sig-field__label">Parroquia</label>
                                <select name="parroquia_id_libre" class="sig-select">
                                    <option value="">— Seleccione parroquia —</option>
                                    <?php foreach ($data['parroquias'] as $par): ?>
                                        <option value="<?php echo $par->id; ?>">
                                            <?php echo htmlspecialchars($par->nombre); ?>
                                            <?php if (!empty($par->municipio)): ?> (<?php echo htmlspecialchars($par->municipio); ?>)<?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="sig-field">
                                <label class="sig-field__label">Dirección</label>
                                <input type="text" name="direccion_libre" class="sig-input" placeholder="Ej: Urb. Las Palmas, Calle 5, Casa 12">
                            </div>
                        </div>
                        <div class="col-12">
                            <hr style="margin:var(--sp-1) 0;">
                            <p style="font-size:12px; font-weight:600; color:var(--brand-500); margin-bottom:var(--sp-2);">
                                <i class="bi bi-person-badge"></i> Docente acompañante (opcional)
                            </p>
                        </div>
                        <div class="col-md-7">
                            <div class="sig-field">
                                <label class="sig-field__label">Nombre del docente</label>
                                <input type="text" name="nombre_docente" class="sig-input" placeholder="Ej: María Rodríguez">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="sig-field">
                                <label class="sig-field__label">Cédula del docente</label>
                                <input type="text" name="cedula_docente" class="sig-input" placeholder="V-12345678">
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; // fin bloque externo ?>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" id="btn_insc_submit" class="btn-sig btn-sig--primary" <?php echo $esInterna ? '' : 'disabled'; ?>>
                    <i class="bi bi-person-plus"></i> Agregar
                </button>
            </div>
        </form>
    </div>
</div>

<style>
#btn_insc_submit:disabled { opacity:.5; cursor:not-allowed; pointer-events:none; }
input[readonly].sig-input, select:disabled.sig-select {
    background: var(--bg-muted-subtle);
    color: var(--text-secondary);
    cursor: default;
}
</style>
<script>
function calcularEdad(fechaNac) {
    if (!fechaNac) return null;
    var hoy = new Date(), nac = new Date(fechaNac);
    var a = hoy.getFullYear() - nac.getFullYear();
    if (hoy.getMonth() < nac.getMonth() || (hoy.getMonth() === nac.getMonth() && hoy.getDate() < nac.getDate())) a--;
    return a >= 0 ? a : null;
}

// ── Filtro + paginación de participantes (50 por página) ──────────────────
(function() {
    var PAGE_SIZE   = 50;
    var paginaActual = 1;
    var filtroInput = document.getElementById('filtro_participantes');
    var filas       = Array.prototype.slice.call(document.querySelectorAll('tr.fila-participante'));
    var pagWrap     = document.getElementById('part_paginacion');
    var pagInfo     = document.getElementById('part_pag_info');
    var pagActual   = document.getElementById('part_pag_actual');
    var btnPrev     = document.getElementById('part_pag_prev');
    var btnNext     = document.getElementById('part_pag_next');

    if (!filas.length) return;

    function coincide(tr, q) {
        if (!q) return true;
        return Array.prototype.slice.call(tr.querySelectorAll('[data-buscar]'))
            .map(function(el) { return (el.dataset.buscar || '').toLowerCase(); })
            .join(' ').includes(q);
    }

    function render() {
        var q = filtroInput ? filtroInput.value.toLowerCase().trim() : '';
        var visibles = filas.filter(function(tr) { return coincide(tr, q); });
        var total    = visibles.length;
        var totalPag = Math.max(1, Math.ceil(total / PAGE_SIZE));
        if (paginaActual > totalPag) paginaActual = totalPag;

        // Ocultar todas, luego mostrar solo la ventana de la página
        filas.forEach(function(tr) { tr.style.display = 'none'; });
        var ini = (paginaActual - 1) * PAGE_SIZE;
        visibles.slice(ini, ini + PAGE_SIZE).forEach(function(tr) { tr.style.display = ''; });

        // Controles solo si hay más de una página
        if (total > PAGE_SIZE) {
            pagWrap.style.display = 'flex';
            var hasta = Math.min(ini + PAGE_SIZE, total);
            pagInfo.textContent   = 'Mostrando ' + (total ? ini + 1 : 0) + '–' + hasta + ' de ' + total;
            pagActual.textContent = 'Página ' + paginaActual + ' / ' + totalPag;
            btnPrev.disabled = paginaActual <= 1;
            btnNext.disabled = paginaActual >= totalPag;
            btnPrev.style.opacity = btnPrev.disabled ? '0.4' : '1';
            btnNext.style.opacity = btnNext.disabled ? '0.4' : '1';
        } else {
            pagWrap.style.display = 'none';
        }
    }

    if (filtroInput) filtroInput.addEventListener('input', function() { paginaActual = 1; render(); });
    if (btnPrev) btnPrev.addEventListener('click', function() { if (paginaActual > 1) { paginaActual--; render(); } });
    if (btnNext) btnNext.addEventListener('click', function() { paginaActual++; render(); });

    render();
}());

// ── Marcado masivo de asistencia ─────────────────────────────────────────
var btnMasiva = document.getElementById('btn_asistencia_masiva');
if (btnMasiva) {
    btnMasiva.addEventListener('click', function() {
        if (!confirm('¿Marcar como "Asistió" a todos los participantes pendientes?')) return;
        var idTaller = this.dataset.taller;
        var self = this;
        var fd = new FormData();
        fd.append('id_taller', idTaller);
        self.disabled = true;
        fetch('<?php echo URL_ROOT; ?>/talleres/marcarAsistenciaMasiva', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.ok) {
                    document.querySelectorAll('.btn-asistencia[data-asistio="0"]').forEach(function(btn) {
                        btn.dataset.asistio = '1';
                        btn.className = 'btn-asistencia sig-badge sig-badge--success';
                        btn.textContent = 'Asistió';
                    });
                    self.style.opacity = '0.4';
                    self.disabled = true;
                }
            });
    });
}

// ── Toggle de asistencia inline ───────────────────────────────────────────
document.querySelectorAll('.btn-asistencia').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id     = this.dataset.id;
        var actual = this.dataset.asistio === '1';
        var nuevo  = actual ? '0' : '1';
        var self   = this;
        var fd = new FormData(); fd.append('id', id); fd.append('asistio', nuevo);
        fetch('<?php echo URL_ROOT; ?>/talleres/marcarAsistencia', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.ok) {
                    self.dataset.asistio = res.asistio ? '1' : '0';
                    self.className = 'btn-asistencia sig-badge ' + (res.asistio ? 'sig-badge--success' : 'sig-badge--neutral');
                    self.textContent = res.asistio ? 'Asistió' : 'Pendiente';
                }
            });
    });
});

// ── Modal historial por persona ───────────────────────────────────────────
var modalHistorial = document.getElementById('modalHistorial');
document.querySelectorAll('.link-historial').forEach(function(el) {
    el.addEventListener('click', function() {
        var idPersona = this.dataset.id;
        var nombre    = this.dataset.nombre;
        document.getElementById('hist_nombre').textContent = nombre;
        var cuerpo = document.getElementById('hist_cuerpo');
        cuerpo.innerHTML = '<em style="color:var(--text-tertiary);">Cargando…</em>';
        new bootstrap.Modal(modalHistorial).show();
        fetch('<?php echo URL_ROOT; ?>/talleres/historialPersona?id_persona=' + idPersona)
            .then(function(r) { return r.json(); })
            .then(function(rows) {
                if (!rows.length) { cuerpo.innerHTML = '<em>Sin actividades registradas.</em>'; return; }
                var html = '<table class="sig-table" style="font-size:12px;"><thead><tr><th>Actividad</th><th>Tipo</th><th>Fecha</th><th>Estado</th><th class="text-center">Asistió</th></tr></thead><tbody>';
                rows.forEach(function(r) {
                    var asistio = r.asistio ? '<span class="sig-badge sig-badge--success" style="font-size:10px;">Sí</span>' : '<span class="sig-badge sig-badge--neutral" style="font-size:10px;">No</span>';
                    html += '<tr><td>' + r.nombre + '</td><td>' + r.tipo_actividad + '</td><td>' + (r.fecha_inicio || '').substring(0,10) + '</td><td>' + r.estado + '</td><td class="text-center">' + asistio + '</td></tr>';
                });
                cuerpo.innerHTML = html + '</tbody></table>';
            });
    });
});

// ── Editar participante ───────────────────────────────────────────────────
function editarParticipante(p) {
    var esLibre = !p.id_persona;
    document.getElementById('ep_id_pt').value = p.id;
    document.getElementById('ep_bloque_persona').style.display = esLibre ? 'none' : 'block';
    document.getElementById('ep_bloque_libre').style.display   = esLibre ? 'block' : 'none';

    if (esLibre) {
        document.getElementById('ep_nombre_libre').value     = p.nombre_libre   || '';
        document.getElementById('ep_apellido_libre').value   = p.apellido_libre || '';
        document.getElementById('ep_fecha_nac_libre').value  = p.fecha_nac_libre|| '';
        document.getElementById('ep_genero_libre').value     = p.genero_libre   || '';
        document.getElementById('ep_cedula_libre').value     = p.cedula_libre   || '';
        document.getElementById('ep_parroquia_libre').value  = p.parroquia_id_libre || '';
        document.getElementById('ep_direccion_libre').value  = p.direccion_libre|| '';
        document.getElementById('ep_nombre_docente').value   = p.nombre_docente || '';
        document.getElementById('ep_cedula_docente').value   = p.cedula_docente || '';
        epActualizarEdad();
    } else {
        document.getElementById('ep_cedula').value    = p.cedula    || '';
        document.getElementById('ep_nombre').value    = p.nombre    || '';
        document.getElementById('ep_apellido').value  = p.apellido  || '';
        document.getElementById('ep_telefono').value  = p.telefono  || '';
        document.getElementById('ep_correo').value    = p.correo    || '';
        document.getElementById('ep_genero').value    = p.genero    || '';
        document.getElementById('ep_fecha_nac').value = p.fecha_nacimiento || '';
        document.getElementById('ep_parroquia').value = p.parroquia_id || '';
        document.getElementById('ep_direccion').value = p.direccion  || '';
    }
    new bootstrap.Modal(document.getElementById('modalEditarParticipante')).show();
}

function epActualizarEdad() {
    var f = document.getElementById('ep_fecha_nac_libre').value;
    var edad = calcularEdad(f);
    var lbl = document.getElementById('ep_edad_label');
    var err = document.getElementById('ep_edad_error');
    err.style.display = 'none';
    if (edad === null) { lbl.textContent = ''; return; }
    if (edad < 5)      { lbl.textContent = '· ' + edad + ' años'; err.textContent = 'Debe tener al menos 5 años.'; err.style.display = 'block'; }
    else if (edad >= 12){ lbl.textContent = '· ' + edad + ' años'; err.textContent = 'De 12 años o más debe usar cédula.'; err.style.display = 'block'; }
    else                { lbl.textContent = '· ' + edad + ' años (Niño/a)'; }
}
document.getElementById('ep_fecha_nac_libre').addEventListener('change', epActualizarEdad);
document.getElementById('ep_correo').addEventListener('input', function() {
    var v = this.value.trim();
    var ok = !v || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
    this.classList.toggle('is-invalid', !ok);
    document.getElementById('ep_msg_correo').textContent = ok ? '' : 'Formato de correo no válido.';
});
document.getElementById('formEditarPart').addEventListener('submit', function(e) {
    var esLibre = document.getElementById('ep_bloque_libre').style.display !== 'none';
    if (esLibre) {
        var edad = calcularEdad(document.getElementById('ep_fecha_nac_libre').value);
        if (edad === null || edad < 5 || edad >= 12) {
            e.preventDefault(); epActualizarEdad();
        }
    } else {
        var c = document.getElementById('ep_correo').value.trim();
        if (c && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(c)) {
            e.preventDefault();
            document.getElementById('ep_correo').classList.add('is-invalid');
            document.getElementById('ep_msg_correo').textContent = 'Formato de correo no válido.';
        }
    }
});

<?php if (!$esInterna): ?>
// ── Modal de inscripción (actividad externa) ──────────────────────────────
function checkInscripcionValid() {
    var esLibre = document.getElementById('insc_es_libre').checked;
    var btn = document.getElementById('btn_insc_submit');
    if (esLibre) {
        var nombre = (document.getElementById('insc_nombre_libre').value || '').trim();
        var fecha  = (document.getElementById('libre_fecha_nac').value  || '').trim();
        var edadV  = fecha ? calcularEdad(fecha) : null;
        var edadOk = edadV !== null && edadV >= 5 && edadV < 12;
        btn.disabled = !nombre || !fecha || !edadOk;
    } else {
        var visible  = document.getElementById('bloque_datos_persona').style.display !== 'none';
        var nombre   = (document.getElementById('insc_nombre').value   || '').trim();
        var apellido = (document.getElementById('insc_apellido').value || '').trim();
        var correo   = (document.getElementById('insc_correo').value   || '').trim();
        var correoOk = !correo || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo);
        btn.disabled = !visible || !nombre || !apellido || !correoOk;
    }
}

function resetBloquePersona() {
    ['insc_nombre','insc_apellido','insc_telefono','insc_correo','insc_fecha_nac','insc_direccion'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) { el.value = ''; el.readOnly = false; }
    });
    document.getElementById('insc_genero').value = '';    document.getElementById('insc_genero').disabled = false;
    document.getElementById('insc_parroquia').value = ''; document.getElementById('insc_parroquia').disabled = false;
    document.getElementById('insc_status').style.display = 'none';
    document.getElementById('bloque_datos_persona').style.display = 'none';
    document.getElementById('insc_edad_label').textContent = '';
    checkInscripcionValid();
}

function setPersonaReadonly(readonly) {
    ['insc_nombre','insc_apellido','insc_telefono','insc_correo','insc_fecha_nac','insc_direccion'].forEach(function(id) {
        document.getElementById(id).readOnly = readonly;
    });
    document.getElementById('insc_genero').disabled    = readonly;
    document.getElementById('insc_parroquia').disabled = readonly;
}

function mostrarStatus(tipo, html) {
    var s = document.getElementById('insc_status');
    var estilos = {
        ok:   'background:rgba(34,197,94,.1);  border-left:3px solid var(--success-600); color:var(--success-700);',
        warn: 'background:rgba(234,179,8,.1);  border-left:3px solid #ca8a04; color:#92400e;',
        err:  'background:rgba(239,68,68,.1);  border-left:3px solid var(--danger-600);  color:var(--danger-700);'
    };
    s.style.cssText = 'padding:var(--sp-2) var(--sp-3); border-radius:6px; font-size:13px; ' + (estilos[tipo] || '');
    s.innerHTML = html; s.style.display = 'block';
}

document.getElementById('btn_buscar_cedula').addEventListener('click', function() {
    var cedula = (document.getElementById('insc_cedula_busqueda').value || '').trim();
    var btn = this; var ico = document.getElementById('ico_buscar');
    if (!cedula) {
        resetBloquePersona();
        document.getElementById('bloque_datos_persona').style.display = 'block';
        mostrarStatus('warn', '<i class="bi bi-pencil"></i> Complete los datos para registrar un nuevo participante.');
        checkInscripcionValid(); return;
    }
    // Validar formato de cédula venezolana (V/E/J/G/C/P + 6-9 dígitos)
    var cedulaN = cedula.toUpperCase().replace(/[\s.\-]/g, '');
    if (!/^[VEJGCP]?\d{6,9}$/.test(cedulaN)) {
        mostrarStatus('err', '<i class="bi bi-exclamation-circle"></i> Formato no válido. Use V-12345678, E-1234567 o solo los números.');
        return;
    }
    btn.disabled = true; ico.className = 'bi bi-hourglass-split';
    fetch('<?php echo URL_ROOT; ?>/talleres/buscarPersona?cedula=' + encodeURIComponent(cedula))
        .then(function(r) { return r.json(); })
        .then(function(res) {
            document.getElementById('bloque_datos_persona').style.display = 'block';
            if (res.found) {
                var p = res.persona;
                document.getElementById('insc_nombre').value    = p.nombre;
                document.getElementById('insc_apellido').value  = p.apellido;
                document.getElementById('insc_telefono').value  = p.telefono || '';
                document.getElementById('insc_correo').value    = p.correo   || '';
                document.getElementById('insc_genero').value    = p.genero   || '';
                document.getElementById('insc_fecha_nac').value = p.fecha_nacimiento || '';
                document.getElementById('insc_parroquia').value = p.parroquia_id     || '';
                document.getElementById('insc_direccion').value = p.direccion        || '';
                setPersonaReadonly(true);
                ['insc_nombre','insc_apellido','insc_telefono','insc_correo','insc_fecha_nac','insc_direccion'].forEach(function(id) {
                    var el = document.getElementById(id); if (!el.value) el.readOnly = false;
                });
                if (!document.getElementById('insc_genero').value)    document.getElementById('insc_genero').disabled    = false;
                if (!document.getElementById('insc_parroquia').value) document.getElementById('insc_parroquia').disabled = false;
                var edad = calcularEdad(p.fecha_nacimiento);
                var edadTxt = edad !== null ? '· ' + edad + ' años' : '';
                document.getElementById('insc_edad_label').textContent = edadTxt;
                var faltantes = !p.fecha_nacimiento || !p.telefono || !p.correo || !p.genero || !p.parroquia_id || !p.direccion;
                mostrarStatus('ok', '<i class="bi bi-check-circle"></i> <strong>Persona encontrada</strong> ' + edadTxt + (faltantes ? ' — <em>complete los campos vacíos si lo desea</em>' : ' — datos cargados automáticamente.'));
            } else {
                setPersonaReadonly(false);
                mostrarStatus('warn', '<i class="bi bi-person-plus"></i> Persona no registrada — complete los datos para crear el registro.');
            }
            checkInscripcionValid();
        })
        .catch(function() { mostrarStatus('err', '<i class="bi bi-exclamation-circle"></i> Error al consultar. Intente nuevamente.'); })
        .finally(function() { btn.disabled = false; ico.className = 'bi bi-search'; });
});

document.getElementById('insc_cedula_busqueda').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); document.getElementById('btn_buscar_cedula').click(); }
});

document.getElementById('insc_es_libre').addEventListener('change', function() {
    var esLibre = this.checked;
    document.getElementById('bloque_persona').style.display = esLibre ? 'none' : 'block';
    document.getElementById('bloque_libre').style.display   = esLibre ? 'block' : 'none';
    if (!esLibre) { document.getElementById('insc_cedula_busqueda').value = ''; resetBloquePersona(); }
    checkInscripcionValid();
});

document.getElementById('insc_fecha_nac').addEventListener('change', function() {
    var edad = calcularEdad(this.value);
    document.getElementById('insc_edad_label').textContent = edad !== null ? '· ' + edad + ' años' : '';
});

document.getElementById('libre_fecha_nac').addEventListener('change', function() {
    var edad    = calcularEdad(this.value);
    var labelEl = document.getElementById('libre_edad_label');
    var errorEl = document.getElementById('libre_edad_error');
    errorEl.style.display = 'none';
    if (edad === null) { labelEl.textContent = ''; checkInscripcionValid(); return; }
    if (edad < 5) {
        labelEl.textContent = '· ' + edad + ' años';
        errorEl.textContent = 'El participante debe tener al menos 5 años para inscribirse.';
        errorEl.style.display = 'block';
    } else if (edad >= 12) {
        labelEl.textContent = '· ' + edad + ' años';
        errorEl.textContent = 'Participantes de 12 años o más deben registrarse con cédula en el formulario estándar.';
        errorEl.style.display = 'block';
    } else {
        labelEl.textContent = '· ' + edad + ' años (Niño/a)';
    }
    checkInscripcionValid();
});

document.getElementById('insc_nombre').addEventListener('input', checkInscripcionValid);
document.getElementById('insc_apellido').addEventListener('input', checkInscripcionValid);
document.getElementById('insc_nombre_libre').addEventListener('input', checkInscripcionValid);

// Validación de correo en tiempo real
document.getElementById('insc_correo').addEventListener('input', function() {
    var correo = this.value.trim();
    var msgEl  = document.getElementById('msg_correo');
    var valid  = !correo || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo);
    this.classList.toggle('is-invalid', !valid);
    if (msgEl) msgEl.textContent = valid ? '' : 'Formato de correo no válido (ej: usuario@dominio.com).';
    checkInscripcionValid();
});

// Última línea de defensa: validar cédula y correo al enviar el formulario
document.getElementById('formInscripcion').addEventListener('submit', function(e) {
    var esLibre = document.getElementById('insc_es_libre').checked;
    if (esLibre) return;

    var cedula = (document.getElementById('insc_cedula_busqueda').value || '').trim();
    if (cedula) {
        var cedulaN = cedula.toUpperCase().replace(/[\s.\-]/g, '');
        if (!/^[VEJGCP]?\d{6,9}$/.test(cedulaN)) {
            e.preventDefault();
            mostrarStatus('err', '<i class="bi bi-exclamation-circle"></i> Formato de cédula no válido. Use V-12345678 o solo los números.');
            return;
        }
    }

    var correo = (document.getElementById('insc_correo').value || '').trim();
    if (correo && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
        e.preventDefault();
        document.getElementById('insc_correo').classList.add('is-invalid');
        var msgEl = document.getElementById('msg_correo');
        if (msgEl) msgEl.textContent = 'Formato de correo no válido (ej: usuario@dominio.com).';
    }
});

document.getElementById('modalInscripcion').addEventListener('show.bs.modal', function() {
    document.getElementById('insc_es_libre').checked        = false;
    document.getElementById('bloque_persona').style.display = 'block';
    document.getElementById('bloque_libre').style.display   = 'none';
    document.getElementById('insc_cedula_busqueda').value   = '';
    resetBloquePersona();
});

<?php else: ?>
// ── Modal de inscripción (actividad interna — empleados) ──────────────────
document.getElementById('sel_empleado').addEventListener('change', function() {
    document.getElementById('btn_insc_submit').disabled = !this.value;
});
<?php endif; ?>
</script>

<!-- Modal: Editar Participante -->
<div class="modal fade" id="modalEditarParticipante" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo URL_ROOT; ?>/talleres/actualizarParticipante" method="POST" class="modal-content" id="formEditarPart">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar Participante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_pt" id="ep_id_pt">
                <input type="hidden" name="id_taller" value="<?php echo $data['taller']->id; ?>">

                <!-- ── Bloque con cédula ── -->
                <div id="ep_bloque_persona" style="display:none;">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="sig-field"><label class="sig-field__label">Cédula</label>
                                <input type="text" id="ep_cedula" class="sig-input" readonly style="background:var(--bg-muted-subtle);">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="sig-field"><label class="sig-field__label">Nombre <span class="req">*</span></label>
                                <input type="text" name="nombre" id="ep_nombre" class="sig-input"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="sig-field"><label class="sig-field__label">Apellido <span class="req">*</span></label>
                                <input type="text" name="apellido" id="ep_apellido" class="sig-input"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="sig-field"><label class="sig-field__label">Teléfono</label>
                                <input type="text" name="telefono" id="ep_telefono" class="sig-input"></div>
                        </div>
                        <div class="col-md-5">
                            <div class="sig-field"><label class="sig-field__label">Correo</label>
                                <input type="email" name="correo" id="ep_correo" class="sig-input">
                                <div class="invalid-feedback" id="ep_msg_correo"></div></div>
                        </div>
                        <div class="col-md-3">
                            <div class="sig-field"><label class="sig-field__label">Género</label>
                                <select name="genero" id="ep_genero" class="sig-select">
                                    <option value="">—</option><option value="M">Masculino</option>
                                    <option value="F">Femenino</option><option value="O">Otro</option>
                                </select></div>
                        </div>
                        <div class="col-md-4">
                            <div class="sig-field"><label class="sig-field__label">Fecha de nacimiento</label>
                                <input type="date" name="fecha_nacimiento" id="ep_fecha_nac" class="sig-input"></div>
                        </div>
                        <div class="col-md-8">
                            <div class="sig-field"><label class="sig-field__label">Parroquia</label>
                                <select name="parroquia_id" id="ep_parroquia" class="sig-select">
                                    <option value="">— Seleccione —</option>
                                    <?php foreach ($data['parroquias'] ?? [] as $par): ?>
                                        <option value="<?php echo $par->id; ?>"><?php echo htmlspecialchars($par->nombre); ?><?php if (!empty($par->municipio)): ?> (<?php echo htmlspecialchars($par->municipio); ?>)<?php endif; ?></option>
                                    <?php endforeach; ?>
                                </select></div>
                        </div>
                        <div class="col-12">
                            <div class="sig-field"><label class="sig-field__label">Dirección</label>
                                <input type="text" name="direccion" id="ep_direccion" class="sig-input"></div>
                        </div>
                    </div>
                    <p style="font-size:11px; color:var(--text-tertiary); margin-top:var(--sp-2);">
                        <i class="bi bi-info-circle"></i> Estos datos pertenecen al registro global de la persona; los cambios se reflejan en todo el sistema.
                    </p>
                </div>

                <!-- ── Bloque libre (menor) ── -->
                <div id="ep_bloque_libre" style="display:none;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="sig-field"><label class="sig-field__label">Nombre <span class="req">*</span></label>
                                <input type="text" name="nombre_libre" id="ep_nombre_libre" class="sig-input"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="sig-field"><label class="sig-field__label">Apellido</label>
                                <input type="text" name="apellido_libre" id="ep_apellido_libre" class="sig-input"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="sig-field"><label class="sig-field__label">Fecha de nacimiento <span class="req">*</span> <span id="ep_edad_label" style="color:var(--text-tertiary);font-weight:400;"></span></label>
                                <input type="date" name="fecha_nac_libre" id="ep_fecha_nac_libre" class="sig-input"
                                       max="<?php echo date('Y-m-d', strtotime('-5 years')); ?>"
                                       min="<?php echo date('Y-m-d', strtotime('-12 years +1 day')); ?>">
                                <span id="ep_edad_error" style="display:none;font-size:11px;color:var(--danger-600);"></span></div>
                        </div>
                        <div class="col-md-4">
                            <div class="sig-field"><label class="sig-field__label">Género</label>
                                <select name="genero_libre" id="ep_genero_libre" class="sig-select">
                                    <option value="">—</option><option value="M">Masculino</option>
                                    <option value="F">Femenino</option><option value="O">Otro</option>
                                </select></div>
                        </div>
                        <div class="col-md-4">
                            <div class="sig-field"><label class="sig-field__label">N° ID Escolar</label>
                                <input type="text" name="cedula_libre" id="ep_cedula_libre" class="sig-input"></div>
                        </div>
                        <div class="col-md-8">
                            <div class="sig-field"><label class="sig-field__label">Parroquia</label>
                                <select name="parroquia_id_libre" id="ep_parroquia_libre" class="sig-select">
                                    <option value="">— Seleccione —</option>
                                    <?php foreach ($data['parroquias'] ?? [] as $par): ?>
                                        <option value="<?php echo $par->id; ?>"><?php echo htmlspecialchars($par->nombre); ?><?php if (!empty($par->municipio)): ?> (<?php echo htmlspecialchars($par->municipio); ?>)<?php endif; ?></option>
                                    <?php endforeach; ?>
                                </select></div>
                        </div>
                        <div class="col-12">
                            <div class="sig-field"><label class="sig-field__label">Dirección</label>
                                <input type="text" name="direccion_libre" id="ep_direccion_libre" class="sig-input"></div>
                        </div>
                        <div class="col-md-7">
                            <div class="sig-field"><label class="sig-field__label">Docente / tutor</label>
                                <input type="text" name="nombre_docente" id="ep_nombre_docente" class="sig-input"></div>
                        </div>
                        <div class="col-md-5">
                            <div class="sig-field"><label class="sig-field__label">Cédula del docente</label>
                                <input type="text" name="cedula_docente" id="ep_cedula_docente" class="sig-input"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" id="ep_submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal historial de participante -->
<div class="modal fade" id="modalHistorial" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-clock-history"></i> Historial — <span id="hist_nombre"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="hist_cuerpo" style="min-height:80px;"></div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
