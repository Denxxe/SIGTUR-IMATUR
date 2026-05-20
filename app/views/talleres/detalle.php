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
                $cls = ['Programado'=>'sig-badge--warning','En Curso'=>'sig-badge--brand','Finalizado'=>'sig-badge--success','Cancelado'=>'sig-badge--danger'][$e] ?? 'sig-badge--neutral';
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
        <?php if (!in_array($data['taller']->estado ?? '', ['Finalizado', 'Cancelado'])): ?>
            <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalInscripcion">
                <i class="bi bi-person-plus"></i> Agregar Participante
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__head" style="display:flex; justify-content:space-between; align-items:center;">
        <div class="sig-card__title">Participantes</div>
        <div style="display:flex; align-items:center; gap:var(--sp-4);">
            <?php
            $inscritos  = count($data['participantes'] ?? []);
            $cupo       = $data['taller']->cupo_maximo ?? 0;
            $porcentaje = ($cupo > 0) ? round(($inscritos / $cupo) * 100) : 0;
            ?>
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
                    <th>Teléfono</th>
                    <th class="text-center">Asistencia</th>
                    <th>Brigadista / Docente</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['participantes'])): ?>
                    <tr>
                        <td colspan="6" class="sig-table-empty">No hay participantes registrados aún.</td>
                    </tr>
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
                            <td style="font-size:12px; color:var(--text-secondary);">
                                <?php if ($esLibre && !empty($p->nombre_docente)): ?>
                                    <span style="display:flex; align-items:center; gap:4px;">
                                        <i class="bi bi-person-badge" style="color:var(--brand-400);"></i>
                                        <?php echo htmlspecialchars($p->nombre_docente); ?>
                                        <?php if (!empty($p->cedula_docente)): ?>
                                            <span style="color:var(--text-tertiary);">(<?php echo htmlspecialchars($p->cedula_docente); ?>)</span>
                                        <?php endif; ?>
                                    </span>
                                <?php elseif (!$esLibre && !empty($p->es_brigadista)): ?>
                                    <span class="sig-badge sig-badge--brand" style="font-size:10px;">Brigadista</span>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px; color:var(--text-secondary);"><?php echo htmlspecialchars($p->observaciones ?? '—'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal agregar participante -->
<div class="modal fade" id="modalInscripcion" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/talleres/inscribir" method="POST" class="modal-content needs-validation" novalidate>
            <div class="modal-header">
                <h5 class="modal-title">Agregar Participante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_taller" value="<?php echo $data['taller']->id; ?>">

                <!-- Toggle tipo participante (RN-F16) -->
                <div class="mb-4" style="padding:var(--sp-3); background:var(--bg-muted-subtle); border-radius:8px;">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="insc_es_libre" name="tipo_participante_libre" value="1">
                        <label class="form-check-label" for="insc_es_libre" style="font-size:13px; cursor:pointer; user-select:none;">
                            <i class="bi bi-person-x"></i> Participante sin cédula (niño/a sin documento de identidad)
                        </label>
                    </div>
                </div>

                <!-- Bloque con cédula -->
                <div id="bloque_cedula">
                    <div class="sig-field">
                        <label class="sig-field__label">Cédula <span class="req">*</span></label>
                        <input type="text" name="cedula_busqueda" id="insc_cedula" class="sig-input" placeholder="V-12345678">
                        <p style="font-size:12px; color:var(--text-tertiary); margin-top:6px;">
                            <i class="bi bi-info-circle"></i> La persona debe estar registrada en el sistema.
                        </p>
                    </div>
                    <div class="form-check" style="margin-top:var(--sp-2);">
                        <input class="form-check-input" type="checkbox" id="insc_brigadista" name="es_brigadista" value="1">
                        <label class="form-check-label" for="insc_brigadista" style="font-size:13px;">
                            <i class="bi bi-shield-check"></i> Es brigadista de la institución
                        </label>
                    </div>
                </div>

                <!-- Bloque libre — niños/as (RN-F16) -->
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
                        <div class="col-12">
                            <div class="sig-field">
                                <label class="sig-field__label">N° ID Escolar (opcional)</label>
                                <input type="text" name="cedula_libre" class="sig-input" placeholder="Si tiene identificación escolar...">
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
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-person-plus"></i> Agregar</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('insc_es_libre').addEventListener('change', function () {
    const esLibre = this.checked;
    document.getElementById('bloque_cedula').style.display = esLibre ? 'none' : 'block';
    document.getElementById('bloque_libre').style.display  = esLibre ? 'block' : 'none';
    document.getElementById('insc_cedula').required        = !esLibre;
    document.getElementById('insc_nombre_libre').required  = esLibre;
});
// Cédula requerida por defecto
document.getElementById('insc_cedula').required = true;
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
