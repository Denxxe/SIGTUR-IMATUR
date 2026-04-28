<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/talleres/index" style="color:inherit; text-decoration:none;">Formación</a> · Detalle de Taller
        </div>
        <h1 class="page__title"><?php echo $data['taller']->nombre ?? ''; ?></h1>
        <div style="display:flex; gap:var(--sp-4); margin-top:var(--sp-2); font-size:13px; color:var(--text-secondary);">
            <span><strong>Facilitador:</strong> <?php echo $data['taller']->facilitador_nombre ?? 'N/A'; ?></span>
            <span><strong>Sede:</strong> <?php echo $data['taller']->ubicacion ?? 'N/A'; ?></span>
            <span><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($data['taller']->fecha_inicio ?? 'N/A')); ?></span>
        </div>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/talleres/index" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <a href="<?php echo URL_ROOT; ?>/talleres/informe/<?php echo $data['taller']->id ?? 'Sin asignar'; ?>" class="btn-sig btn-sig--ghost">
            <i class="bi bi-file-earmark-text"></i> Informe Oficial
        </a>
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalInscripcion">
            <i class="bi bi-person-plus"></i> Inscribir
        </button>
    </div>
</div>

<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__head" style="display:flex; justify-content:space-between; align-items:center;">
        <div class="sig-card__title">Participantes Inscritos</div>
        <div style="display:flex; align-items:center; gap:var(--sp-4);">
            <?php
            $inscritos = count($data['participantes'] ?? []);
            $cupo = $data['taller']->cupo_maximo ?? 0;
            $porcentaje = ($cupo > 0) ? round(($inscritos / $cupo) * 100) : 0;
            ?>
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarParticipantesCsv/<?php echo $data['taller']->id ?? 'Sin asignar'; ?>" class="btn-sig btn-sig--ghost btn-sig--sm">
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
                    <th>Cédula</th>
                    <th>Nombre Completo</th>
                    <th>Teléfono</th>
                    <th class="text-center">Estado</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['participantes'])): ?>
                    <tr>
                        <td colspan="5" class="sig-table-empty">No hay participantes inscritos aún.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['participantes'] as $p): ?>
                        <tr>
                            <td class="cell-id"><?php echo $p->cedula; ?></td>
                            <td class="cell-strong"><?php echo $p->nombre . ' ' . $p->apellido; ?></td>
                            <td><?php echo $p->telefono; ?></td>
                            <td class="text-center">
                                <?php if ($p->asistio): ?>
                                    <span class="sig-badge sig-badge--success">Asistió</span>
                                <?php else: ?>
                                    <span class="sig-badge sig-badge--neutral">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px; color:var(--text-secondary);"><?php echo $p->observaciones ?? '—'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Inscripción -->
<div class="modal fade" id="modalInscripcion" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/talleres/inscribir" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Inscribir Persona</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_taller" value="<?php echo $data['taller']->id ?? 'Sin asignar'; ?>">
                <div class="sig-field">
                    <label class="sig-field__label">Cédula del Participante <span class="req">*</span></label>
                    <input type="text" name="id_persona" id="insc_persona" class="sig-input" required placeholder="Ingrese el ID de la persona">
                    <p style="font-size:12px; color:var(--text-tertiary); margin-top:8px;">
                        <i class="bi bi-info-circle"></i> La persona debe estar previamente registrada en el sistema de personal o comunidad.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-person-plus"></i> Inscribir</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>