<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/pasantes/index" style="color:inherit; text-decoration:none;">Pasantes</a> · Expediente
        </div>
        <h1 class="page__title">Expediente Técnico del Pasante</h1>
        <p class="page__subtitle">Control de documentación académica, evaluación y estatus institucional.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/pasantes/index" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalSubirDoc">
            <i class="bi bi-cloud-upload"></i> Subir Documento
        </button>
        <?php if ($data['pasante']->estado === 'Postulado'): ?>
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalAprobar">
            <i class="bi bi-person-check-fill"></i> Aprobar Pasante
        </button>
        <?php elseif (!empty($data['pasante']->oficio_aceptacion)): ?>
        <a href="<?php echo URL_ROOT; ?>/pasantes/cartaAceptacion/<?php echo $data['pasante']->id; ?>"
           class="btn-sig btn-sig--primary" target="_blank">
            <i class="bi bi-file-earmark-check"></i> Carta de Aceptación
        </a>
        <?php endif; ?>
        <?php if ($data['pasante']->estado === 'Culminado'): ?>
        <a href="<?php echo URL_ROOT; ?>/pasantes/carta/<?php echo $data['pasante']->id; ?>"
           class="btn-sig btn-sig--success" target="_blank">
            <i class="bi bi-file-earmark-text"></i> Carta de Culminación
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4 anim-slide-up">
    <!-- INFO BÁSICA Y ESTADO -->
    <div class="col-md-4">
        <div class="sig-card mb-4">
            <div class="sig-card__head">
                <div class="sig-card__title">
                    <i class="bi bi-info-circle" style="color:var(--brand-500);"></i> Datos Generales
                </div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-6);">
                <div style="margin-bottom:var(--sp-4);">
                    <small style="display:block; font-size:11px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; margin-bottom:4px;">Pasante</small>
                    <div style="font-size:18px; font-weight:800; color:var(--text-primary);"><?php echo (isset($data['pasante']->nombre) ? $data['pasante']->nombre : 'N/A') . ' ' . (isset($data['pasante']->apellido) ? $data['pasante']->apellido : ''); ?></div>
                    <div class="cell-id" style="margin-top:2px;"><?php echo isset($data['pasante']->cedula) ? $data['pasante']->cedula : 'N/A'; ?></div>
                </div>

                <div style="margin-bottom:var(--sp-4); padding-top:var(--sp-4); border-top:1px solid var(--border-subtle);">
                    <small style="display:block; font-size:11px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; margin-bottom:4px;">Institución / Carrera</small>
                    <div style="font-weight:700; color:var(--text-secondary);"><?php echo isset($data['pasante']->institucion) ? $data['pasante']->institucion : 'No especificada'; ?></div>
                    <div style="font-size:13px; color:var(--text-tertiary);"><?php echo isset($data['pasante']->carrera) ? $data['pasante']->carrera : 'No especificada'; ?></div>
                </div>

                <div style="margin-bottom:var(--sp-4); padding-top:var(--sp-4); border-top:1px solid var(--border-subtle);">
                    <small style="display:block; font-size:11px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; margin-bottom:4px;">Estado Institucional</small>
                    <?php 
                        $badgeClass = 'sig-badge--neutral';
                        $e = $data['pasante']->estado ?? 'Desconocido';
                        if ($e == 'Postulado') $badgeClass = 'sig-badge--warning';
                        elseif ($e == 'Aceptado') $badgeClass = 'sig-badge--info';
                        elseif ($e == 'En Curso') $badgeClass = 'sig-badge--brand';
                        elseif ($e == 'Culminado') $badgeClass = 'sig-badge--success';
                        elseif ($e == 'Rechazado') $badgeClass = 'sig-badge--danger';
                    ?>
                    <span class="sig-badge <?php echo $badgeClass; ?>" style="font-size:14px; padding:6px 12px;"><?php echo $e; ?></span>
                </div>

                <div style="padding-top:var(--sp-4); border-top:1px solid var(--border-subtle);">
                    <small style="display:block; font-size:11px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; margin-bottom:4px;">Tutor Institucional</small>
                    <?php if(isset($data['pasante']->id_tutor_institucional) && $data['pasante']->id_tutor_institucional): ?>
                        <div style="display:flex; align-items:center; gap:var(--sp-2); font-weight:700; color:var(--brand-600);">
                            <i class="bi bi-person-check-fill"></i>
                            <span><?php echo (isset($data['pasante']->tutor_nombre) ? $data['pasante']->tutor_nombre : 'Tutor') . ' ' . (isset($data['pasante']->tutor_apellido) ? $data['pasante']->tutor_apellido : ''); ?></span>
                        </div>
                    <?php else: ?>
                        <span style="font-size:13px; color:var(--text-tertiary); font-style:italic;">No asignado</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if(isset($data['pasante']->estado) && $data['pasante']->estado == 'Culminado'): ?>
            <div class="sig-card" style="border-top: 4px solid var(--success-500);">
                <div class="sig-card__body" style="padding:var(--sp-6);">
                    <h5 style="color:var(--success-600); font-weight:800; display:flex; align-items:center; gap:8px; margin-bottom:var(--sp-4);">
                        <i class="bi bi-award-fill"></i> Evaluación Final
                    </h5>
                    <p style="font-size:13px; font-style:italic; color:var(--text-secondary); margin-bottom:var(--sp-4);">
                        "<?php echo $data['pasante']->evaluacion ?? ''; ?>"
                    </p>
                    <div style="background:var(--success-50); border:1px solid var(--success-200); border-radius:var(--r-lg); padding:var(--sp-4); text-align:center;">
                        <span style="display:block; font-size:11px; font-weight:700; color:var(--success-600); text-transform:uppercase; letter-spacing:0.05em;">Nota Final</span>
                        <span style="font-size:32px; font-weight:900; color:var(--success-700);"><?php echo $data['pasante']->nota ?? 0; ?> <small style="font-size:14px; font-weight:500;">/ 20</small></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- DOCUMENTOS Y CHECKLIST -->
    <div class="col-md-8">
        <div class="sig-card h-100">
            <div class="sig-card__head" style="display:flex; justify-content:space-between; align-items:center;">
                <div class="sig-card__title">
                    <i class="bi bi-folder-check" style="color:var(--brand-500);"></i> Checklist de Documentación
                </div>
            </div>
            <div class="sig-table-wrap">
                <table class="sig-table">
                    <thead>
                        <tr>
                            <th>Tipo Documento</th>
                            <th style="text-align:center;">Estado Físico</th>
                            <th style="text-align:center;">Digital</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($data['documentos'])): ?>
                            <tr><td colspan="4" class="sig-table-empty">Sin documentos registrados en el expediente.</td></tr>
                        <?php else: ?>
                            <?php foreach($data['documentos'] ?? [] as $doc): ?>
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:var(--sp-3);">
                                        <div style="width:32px; height:32px; background:var(--bg-muted); border-radius:var(--r-md); display:grid; place-items:center; color:var(--text-secondary);">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </div>
                                        <span class="cell-strong"><?php echo isset($doc->tipo_documento) ? $doc->tipo_documento : 'Documento'; ?></span>
                                    </div>
                                </td>
                                <td style="text-align:center;">
                                    <?php if(isset($doc->entregado) && $doc->entregado): ?>
                                        <span class="sig-badge sig-badge--success"><i class="bi bi-check-circle"></i> Recibido</span>
                                    <?php else: ?>
                                        <span class="sig-badge sig-badge--danger"><i class="bi bi-x-circle"></i> Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:center;">
                                    <?php if(isset($doc->archivo_url) && $doc->archivo_url): ?>
                                        <a href="<?php echo URL_ROOT; ?>/descarga/pasante/<?php echo (int)$doc->id; ?>" target="_blank" class="row-action row-action--view" style="width:auto; padding:0 var(--sp-3);">
                                            <i class="bi bi-file-pdf"></i> Ver PDF
                                        </a>
                                    <?php else: ?>
                                        <span style="font-size:11px; color:var(--text-tertiary);">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:12px; color:var(--text-secondary);"><?php echo (isset($doc->observaciones) && $doc->observaciones) ? $doc->observaciones : '—'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="sig-card__footer" style="background:var(--bg-muted-subtle); padding:var(--sp-4) var(--sp-6); border-top:1px solid var(--border-subtle);">
                <p style="margin:0; font-size:12px; color:var(--text-tertiary); line-height:1.5;">
                    <i class="bi bi-info-circle"></i> Nota: Para validar el estatus <strong>"En Curso"</strong>, es requisito obligatorio la entrega de la Carta de Postulación y la respectiva Carta de Aceptación.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Subir Documento -->
<div class="modal fade" id="modalSubirDoc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?php echo URL_ROOT; ?>/pasantes/subirDocumento/<?php echo $data['pasante']->id ?? ''; ?>" method="POST" enctype="multipart/form-data">
          <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-cloud-upload"></i> Cargar Requisito Físico</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
              <div style="background:var(--brand-50); border:1px solid var(--brand-200); border-radius:var(--r-md); padding:var(--sp-3) var(--sp-4); color:var(--brand-700); font-size:13px; margin-bottom:var(--sp-6);">
                  <i class="bi bi-info-circle-fill"></i> Al registrar el documento, se marcará automáticamente como <strong>Entregado</strong> en el expediente físico.
              </div>
              <div class="sig-field mb-4">
                  <label class="sig-field__label">Tipo de Documento <span class="req">*</span></label>
                  <select name="tipo_documento" class="sig-select" required>
                      <option value="">Seleccione un tipo...</option>
                      <option value="Carta de Postulación">Carta de Postulación (Origen)</option>
                      <option value="Carta de Aceptación">Carta de Aceptación (IMATUR)</option>
                      <option value="Evaluación">Formato de Evaluación Final</option>
                      <option value="Otro">Otro (Proyectos, Planillas anexas...)</option>
                  </select>
              </div>
              <div class="sig-field mb-4">
                  <label class="sig-field__label">Archivo Digital (PDF/Imagen)</label>
                  <input type="file" name="archivo" class="sig-input" accept=".pdf,.jpeg,.jpg,.png" style="padding-top:10px;">
              </div>
              <div class="sig-field">
                  <label class="sig-field__label">Observaciones</label>
                  <textarea name="observaciones" class="sig-textarea" rows="2" placeholder="Ej: Firmada por el Director académico..."></textarea>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Registrar Entrega</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- ══ Modal: Aprobar Pasante (individual o en grupo) ══════════════════════ -->
<?php if ($data['pasante']->estado === 'Postulado'): ?>
<div class="modal fade" id="modalAprobar" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/pasantes/aprobar/<?php echo $data['pasante']->id; ?>" method="POST" id="formAprobar" onsubmit="return validarGrupo()">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-check-fill" style="color:var(--brand-600);"></i> Aprobar Pasante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <div style="background:var(--bg-muted-subtle);border-radius:8px;padding:var(--sp-3) var(--sp-4);margin-bottom:var(--sp-4);font-size:13px;">
                        <strong><?php echo htmlspecialchars(($data['pasante']->nombre ?? '') . ' ' . ($data['pasante']->apellido ?? '')); ?></strong><br>
                        <span style="color:var(--text-secondary);"><?php echo htmlspecialchars($data['pasante']->institucion ?? ''); ?> · <?php echo htmlspecialchars($data['pasante']->carrera ?? ''); ?></span>
                    </div>

                    <div class="sig-field mb-4">
                        <label class="sig-field__label">Tipo de aceptación</label>
                        <div style="display:flex;gap:var(--sp-4);margin-top:var(--sp-2);">
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;">
                                <input type="radio" name="tipo" value="individual" checked onchange="toggleGrupo(this.value)" style="accent-color:var(--brand-600);">
                                <span><strong>Individual</strong> — carta solo para este pasante</span>
                            </label>
                        </div>
                        <div style="display:flex;gap:var(--sp-4);margin-top:var(--sp-2);">
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;">
                                <input type="radio" name="tipo" value="grupo" onchange="toggleGrupo(this.value)" style="accent-color:var(--brand-600);">
                                <span><strong>En grupo</strong> — compartirá la carta con otros pasantes (máx. 4 total)</span>
                            </label>
                        </div>
                    </div>

                    <div id="seccionGrupo" style="display:none;">
                        <div class="sig-field">
                            <label class="sig-field__label">
                                Selecciona los demás pasantes del grupo
                                <span style="font-weight:400;color:var(--text-tertiary);font-size:11px;"> — máximo 3 adicionales (4 en total)</span>
                            </label>
                            <?php if (empty($data['postulados'])): ?>
                                <div style="padding:var(--sp-3);background:var(--bg-muted);border-radius:6px;font-size:13px;color:var(--text-secondary);">
                                    <i class="bi bi-info-circle"></i> No hay otros pasantes en estado Postulado disponibles.
                                </div>
                            <?php else: ?>
                            <div style="border:1px solid var(--border-default);border-radius:8px;max-height:200px;overflow-y:auto;padding:var(--sp-2);">
                                <?php foreach ($data['postulados'] as $p): ?>
                                <label style="display:flex;align-items:flex-start;gap:10px;padding:var(--sp-2) var(--sp-3);border-radius:6px;cursor:pointer;font-size:13px;"
                                       onmouseover="this.style.background='var(--bg-muted-subtle)'" onmouseout="this.style.background=''">
                                    <input type="checkbox" name="grupo_ids[]"
                                           value="<?php echo $p->id; ?>"
                                           onchange="contarSeleccionados()"
                                           style="accent-color:var(--brand-600);margin-top:2px;flex-shrink:0;">
                                    <span>
                                        <strong><?php echo htmlspecialchars($p->apellido . ', ' . $p->nombre); ?></strong><br>
                                        <span style="color:var(--text-tertiary);font-size:11px;"><?php echo htmlspecialchars($p->institucion . ' · ' . $p->carrera); ?></span>
                                    </span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                            <small id="limiteGrupo" style="color:var(--text-tertiary);font-size:11px;margin-top:4px;display:block;">
                                <i class="bi bi-people"></i> <span id="cuentaGrupo">0</span> seleccionados (máx. 3 adicionales)
                            </small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="margin-top:var(--sp-4);padding:var(--sp-3);background:#fefce8;border:1px solid #fde68a;border-radius:6px;font-size:12px;color:#92400e;">
                        <i class="bi bi-exclamation-triangle"></i>
                        Al aprobar se generará automáticamente la <strong>Carta de Aceptación</strong> con correlativo único. Esta acción cambia el estado a <strong>Aceptado</strong>.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-sig btn-sig--primary">
                        <i class="bi bi-check-circle-fill"></i> Confirmar Aprobación
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function toggleGrupo(val) {
    document.getElementById('seccionGrupo').style.display = val === 'grupo' ? 'block' : 'none';
    if (val === 'individual') {
        document.querySelectorAll('#seccionGrupo input[type=checkbox]').forEach(cb => cb.checked = false);
        document.getElementById('cuentaGrupo').textContent = '0';
    }
}

function contarSeleccionados() {
    const checks = document.querySelectorAll('#seccionGrupo input[type=checkbox]:checked');
    const n = checks.length;
    document.getElementById('cuentaGrupo').textContent = n;
    // Deshabilitar los no marcados si ya hay 3 seleccionados
    document.querySelectorAll('#seccionGrupo input[type=checkbox]').forEach(cb => {
        if (!cb.checked) cb.disabled = (n >= 3);
    });
}

function validarGrupo() {
    const esGrupo = document.querySelector('input[name=tipo]:checked')?.value === 'grupo';
    if (esGrupo) {
        const n = document.querySelectorAll('#seccionGrupo input[type=checkbox]:checked').length;
        if (n === 0) { alert('Selecciona al menos un pasante adicional para el grupo, o elige "Individual".'); return false; }
        if (n > 3)   { alert('El grupo no puede tener más de 3 pasantes adicionales (4 en total).'); return false; }
    }
    return true;
}
</script>
<?php endif; ?>

<?php require_once '../app/views/inc/footer.php'; ?>
