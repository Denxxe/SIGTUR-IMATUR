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
        <a href="<?php echo URL_ROOT; ?>/pasantes/carnet/<?php echo $data['pasante']->id; ?>" target="_blank" class="btn-sig btn-sig--primary">
            <i class="bi bi-person-vcard"></i> Carnet
        </a>
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
                    <i class="bi bi-info-circle u-c-brand-500"></i> Datos Generales
                </div>
            </div>
            <div class="sig-card__body u-p-sp-6">
                <div class="u-mb-sp-4 u-gap-12px u-ai-center" style="display:flex;">
                    <div class="u-w-64px u-bd-1px-solid-border-subtle u-br-6px u-ov-hidden u-bg-bg-muted-subtle u-fsh-0 u-ai-center u-jc-center" style="height:80px; display:flex;">
                        <img src="<?php echo URL_ROOT; ?>/descarga/foto/<?php echo (int)$data['pasante']->id_persona; ?>?v=<?php echo time(); ?>"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                             style="width:100%; height:100%; object-fit:cover;" alt="Foto">
                        <span class="u-ai-center u-jc-center u-w-100pct u-h-100pct u-fs-32px u-c-text-tertiary" style="display:none;"><i class="bi bi-person"></i></span>
                    </div>
                    <div class="u-minw-0">
                        <small class="u-fs-11px u-fw-700 u-c-text-tertiary u-tt-uppercase u-mb-4px" style="display:block;">Pasante</small>
                        <div class="u-fs-18px u-fw-800 u-c-text-primary"><?php echo (isset($data['pasante']->nombre) ? $data['pasante']->nombre : 'N/A') . ' ' . (isset($data['pasante']->apellido) ? $data['pasante']->apellido : ''); ?></div>
                        <div class="cell-id u-mt-2px"><?php echo isset($data['pasante']->cedula) ? $data['pasante']->cedula : 'N/A'; ?></div>
                        <button type="button" class="btn-sig btn-sig--ghost btn-sig--sm u-mt-6px" data-bs-toggle="modal" data-bs-target="#modalFoto">
                            <i class="bi bi-camera"></i> Foto
                        </button>
                    </div>
                </div>

                <div class="u-mb-sp-4 u-pt-sp-4 u-bdt-1px-solid-border-subtle">
                    <small class="u-fs-11px u-fw-700 u-c-text-tertiary u-tt-uppercase u-mb-4px" style="display:block;">Institución / Carrera</small>
                    <div class="u-fw-700 u-c-text-secondary"><?php echo isset($data['pasante']->institucion) ? $data['pasante']->institucion : 'No especificada'; ?></div>
                    <div class="u-fs-13px u-c-text-tertiary"><?php echo isset($data['pasante']->carrera) ? $data['pasante']->carrera : 'No especificada'; ?></div>
                </div>

                <div class="u-mb-sp-4 u-pt-sp-4 u-bdt-1px-solid-border-subtle">
                    <small class="u-fs-11px u-fw-700 u-c-text-tertiary u-tt-uppercase u-mb-4px" style="display:block;">Estado Institucional</small>
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

                <div class="u-pt-sp-4 u-bdt-1px-solid-border-subtle">
                    <small class="u-fs-11px u-fw-700 u-c-text-tertiary u-tt-uppercase u-mb-4px" style="display:block;">Tutor Institucional</small>
                    <?php if(isset($data['pasante']->id_tutor_institucional) && $data['pasante']->id_tutor_institucional): ?>
                        <div class="u-ai-center u-gap-sp-2 u-fw-700 u-c-brand-600" style="display:flex;">
                            <i class="bi bi-person-check-fill"></i>
                            <span><?php echo (isset($data['pasante']->tutor_nombre) ? $data['pasante']->tutor_nombre : 'Tutor') . ' ' . (isset($data['pasante']->tutor_apellido) ? $data['pasante']->tutor_apellido : ''); ?></span>
                        </div>
                    <?php else: ?>
                        <span class="u-fs-13px u-c-text-tertiary u-fst-italic">No asignado</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if(isset($data['pasante']->estado) && $data['pasante']->estado == 'Culminado'): ?>
            <div class="sig-card" style="border-top: 4px solid var(--success-500);">
                <div class="sig-card__body u-p-sp-6">
                    <h5 class="u-c-success-600 u-fw-800 u-ai-center u-gap-8px u-mb-sp-4" style="display:flex;">
                        <i class="bi bi-award-fill"></i> Evaluación Final
                    </h5>
                    <p class="u-fs-13px u-fst-italic u-c-text-secondary u-mb-sp-4">
                        "<?php echo $data['pasante']->evaluacion ?? ''; ?>"
                    </p>
                    <div class="u-p-sp-4 u-ta-center" style="background:var(--success-50); border:1px solid var(--success-200); border-radius:var(--r-lg);">
                        <span class="u-fs-11px u-fw-700 u-c-success-600 u-tt-uppercase" style="display:block; letter-spacing:0.05em;">Nota Final</span>
                        <span class="u-fs-32px u-fw-900" style="color:var(--success-700);"><?php echo $data['pasante']->nota ?? 0; ?> <small class="u-fs-14px u-fw-500">/ 20</small></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- DOCUMENTOS Y CHECKLIST -->
    <div class="col-md-8">
        <div class="sig-card h-100">
            <div class="sig-card__head u-jc-space-between u-ai-center" style="display:flex;">
                <div class="sig-card__title">
                    <i class="bi bi-folder-check u-c-brand-500"></i> Checklist de Documentación
                </div>
            </div>
            <div class="sig-table-wrap">
                <table class="sig-table">
                    <thead>
                        <tr>
                            <th>Tipo Documento</th>
                            <th class="text-center">Estado Físico</th>
                            <th class="text-center">Digital</th>
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
                                    <div class="u-ai-center u-gap-sp-3" style="display:flex;">
                                        <div class="u-bg-bg-muted u-c-text-secondary" style="width:32px; height:32px; border-radius:var(--r-md); display:grid; place-items:center;">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </div>
                                        <span class="cell-strong"><?php echo isset($doc->tipo_documento) ? $doc->tipo_documento : 'Documento'; ?></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if(isset($doc->entregado) && $doc->entregado): ?>
                                        <span class="sig-badge sig-badge--success"><i class="bi bi-check-circle"></i> Recibido</span>
                                    <?php else: ?>
                                        <span class="sig-badge sig-badge--danger"><i class="bi bi-x-circle"></i> Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if(isset($doc->archivo_url) && $doc->archivo_url): ?>
                                        <a href="<?php echo URL_ROOT; ?>/descarga/pasante/<?php echo (int)$doc->id; ?>" target="_blank" class="row-action row-action--view" style="width:auto; padding:0 var(--sp-3);">
                                            <i class="bi bi-file-pdf"></i> Ver PDF
                                        </a>
                                    <?php else: ?>
                                        <span class="u-fs-11px u-c-text-tertiary">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="u-fs-12px u-c-text-secondary"><?php echo (isset($doc->observaciones) && $doc->observaciones) ? $doc->observaciones : '—'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="sig-card__footer u-bg-bg-muted-subtle u-bdt-1px-solid-border-subtle" style="padding:var(--sp-4) var(--sp-6);">
                <p class="u-m-0 u-fs-12px u-c-text-tertiary u-lh-1_5">
                    <i class="bi bi-info-circle"></i> Nota: Para validar el estatus <strong>"En Curso"</strong>, es requisito obligatorio la entrega de la Carta de Postulación y la respectiva Carta de Aceptación.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Cargar / cambiar foto (carnetización) -->
<div class="modal fade" id="modalFoto" tabindex="-1">
  <div class="modal-dialog">
    <form action="<?php echo URL_ROOT; ?>/pasantes/subirFoto" method="POST" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-camera"></i> Foto del pasante</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input type="hidden" name="id_pasante" value="<?php echo (int)$data['pasante']->id; ?>">
        <p class="u-fs-13px u-c-text-secondary" style="margin-bottom:10px;">Se usará en el carnet. Recomendado: foto tipo carnet, fondo claro.</p>
        <div class="sig-field"><label class="sig-field__label" for="foto">Imagen (JPG/PNG, máx. 5 MB) <span class="req">*</span></label>
          <input id="foto" type="file" name="foto" class="sig-input" accept=".jpg,.jpeg,.png" required></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-upload"></i> Guardar foto</button>
      </div>
    </form>
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
              <div class="u-p-sp-3-sp-4 u-fs-13px u-mb-sp-6" style="background:var(--brand-50); border:1px solid var(--brand-200); border-radius:var(--r-md); color:var(--brand-700);">
                  <i class="bi bi-info-circle-fill"></i> Al registrar el documento, se marcará automáticamente como <strong>Entregado</strong> en el expediente físico.
              </div>
              <div class="sig-field mb-4">
                  <label class="sig-field__label" for="tipo_documento">Tipo de Documento <span class="req">*</span></label>
                  <select id="tipo_documento" name="tipo_documento" class="sig-select" required>
                      <option value="">Seleccione un tipo...</option>
                      <option value="Carta de Postulación">Carta de Postulación (Origen)</option>
                      <option value="Carta de Aceptación">Carta de Aceptación (IMATUR)</option>
                      <option value="Evaluación">Formato de Evaluación Final</option>
                      <option value="Otro">Otro (Proyectos, Planillas anexas...)</option>
                  </select>
              </div>
              <div class="sig-field mb-4">
                  <label class="sig-field__label" for="archivo">Archivo Digital (PDF/Imagen)</label>
                  <input id="archivo" type="file" name="archivo" class="sig-input" accept=".pdf,.jpeg,.jpg,.png" style="padding-top:10px;">
              </div>
              <div class="sig-field">
                  <label class="sig-field__label" for="observaciones">Observaciones</label>
                  <textarea id="observaciones" name="observaciones" class="sig-textarea" rows="2" placeholder="Ej: Firmada por el Director académico..."></textarea>
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
                    <h5 class="modal-title"><i class="bi bi-person-check-fill u-c-brand-600"></i> Aprobar Pasante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <div class="u-bg-bg-muted-subtle u-br-8px u-p-sp-3-sp-4 u-mb-sp-4 u-fs-13px">
                        <strong><?php echo htmlspecialchars(($data['pasante']->nombre ?? '') . ' ' . ($data['pasante']->apellido ?? '')); ?></strong><br>
                        <span class="u-c-text-secondary"><?php echo htmlspecialchars($data['pasante']->institucion ?? ''); ?> · <?php echo htmlspecialchars($data['pasante']->carrera ?? ''); ?></span>
                    </div>

                    <div class="sig-field mb-4" role="radiogroup" aria-labelledby="pas_tipo_lbl">
                        <label class="sig-field__label" id="pas_tipo_lbl">Tipo de aceptación</label>
                        <div class="u-gap-sp-4 u-mt-sp-2" style="display:flex;">
                            <label class="u-ai-center u-gap-8px u-cur-pointer u-fs-14px" style="display:flex;" for="tipo">
                                <input id="tipo" type="radio" name="tipo" value="individual" checked onchange="toggleGrupo(this.value)" style="accent-color:var(--brand-600);">
                                <span><strong>Individual</strong> — carta solo para este pasante</span>
                            </label>
                        </div>
                        <div class="u-gap-sp-4 u-mt-sp-2" style="display:flex;">
                            <label class="u-ai-center u-gap-8px u-cur-pointer u-fs-14px" style="display:flex;" for="tipo_2">
                                <input id="tipo_2" type="radio" name="tipo" value="grupo" onchange="toggleGrupo(this.value)" style="accent-color:var(--brand-600);">
                                <span><strong>En grupo</strong> — compartirá la carta con otros pasantes (máx. 4 total)</span>
                            </label>
                        </div>
                    </div>

                    <div id="seccionGrupo" style="display:none;">
                        <div class="sig-field" role="group" aria-labelledby="pas_grupo_lbl">
                            <label class="sig-field__label" id="pas_grupo_lbl">
                                Selecciona los demás pasantes del grupo
                                <span class="u-fw-400 u-c-text-tertiary u-fs-11px"> — máximo 3 adicionales (4 en total)</span>
                            </label>
                            <?php if (empty($data['postulados'])): ?>
                                <div class="u-p-sp-3 u-bg-bg-muted u-br-6px u-fs-13px u-c-text-secondary">
                                    <i class="bi bi-info-circle"></i> No hay otros pasantes en estado Postulado disponibles.
                                </div>
                            <?php else: ?>
                            <div class="u-br-8px" style="border:1px solid var(--border-default); max-height:200px; overflow-y:auto; padding:var(--sp-2);">
                                <?php foreach ($data['postulados'] as $p): ?>
                                <label class="u-ai-flex-start u-gap-10px u-br-6px u-cur-pointer u-fs-13px" style="display:flex; padding:var(--sp-2) var(--sp-3);"
                                       onmouseover="this.style.background='var(--bg-muted-subtle)'" onmouseout="this.style.background=''">
                                    <input type="checkbox" name="grupo_ids[]"
                                           value="<?php echo $p->id; ?>"
                                           onchange="contarSeleccionados()"
                                           style="accent-color:var(--brand-600);margin-top:2px;flex-shrink:0;">
                                    <span>
                                        <strong><?php echo htmlspecialchars($p->apellido . ', ' . $p->nombre); ?></strong><br>
                                        <span class="u-c-text-tertiary u-fs-11px"><?php echo htmlspecialchars($p->institucion . ' · ' . $p->carrera); ?></span>
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

                    <div class="u-mt-sp-4 u-p-sp-3 u-br-6px u-fs-12px" style="background:#fefce8; border:1px solid #fde68a; color:#92400e;">
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
