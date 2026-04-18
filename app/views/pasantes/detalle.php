<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1><i class="bi bi-person-badge"></i> Expediente Técnico del Pasante</h1>
        <p class="text-muted">Control de documentos y evaluación formal.</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?php echo URL_ROOT; ?>/pasantes/index" class="btn btn-outline-secondary">← Volver</a>
    </div>
</div>

<div class="row g-4">
    <!-- INFO BÁSICA Y ESTADO -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white fw-bold">
                <i class="bi bi-info-circle"></i> Datos Generales
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Pasante:</small>
                    <span class="fs-5 fw-bold"><?php echo $data['pasante']->nombre . ' ' . $data['pasante']->apellido; ?></span><br>
                    <span class="badge bg-secondary"><?php echo $data['pasante']->cedula; ?></span>
                </div>
                <hr>
                <div class="mb-3">
                    <small class="text-muted d-block">Institución Origen:</small>
                    <span class="fw-bold"><?php echo $data['pasante']->institucion; ?></span><br>
                    <span><?php echo $data['pasante']->carrera; ?></span>
                </div>
                <hr>
                <div class="mb-3">
                    <small class="text-muted d-block">Estado Institucional:</small>
                    <?php 
                        $color = 'bg-secondary';
                        $e = $data['pasante']->estado;
                        if ($e == 'Aceptado') $color = 'bg-primary';
                        if ($e == 'En Curso') $color = 'bg-success';
                        if ($e == 'Culminado') $color = 'bg-dark';
                        if ($e == 'Rechazado') $color = 'bg-danger';
                        if ($e == 'Postulado') $color = 'bg-warning text-dark';
                    ?>
                    <span class="badge <?php echo $color; ?> fs-6"><?php echo $e; ?></span>
                </div>
                <hr>
                <div class="mb-0">
                    <small class="text-muted d-block">Tutor Asignado:</small>
                    <?php if($data['pasante']->id_tutor_institucional): ?>
                        <span class="fw-bold text-primary"><i class="bi bi-person-check-fill"></i> <?php echo $data['pasante']->tutor_nombre . ' ' . $data['pasante']->tutor_apellido; ?></span>
                    <?php else: ?>
                        <span class="text-muted">No asignado</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if($data['pasante']->estado == 'Culminado'): ?>
        <div class="card shadow-sm border-0 border-top border-success border-4">
            <div class="card-body">
                <h5 class="text-success fw-bold"><i class="bi bi-award-fill"></i> Evaluación Final</h5>
                <p class="fst-italic small text-muted mb-2">"<?php echo $data['pasante']->evaluacion; ?>"</p>
                <div class="display-6 fw-bold text-success text-center border rounded p-2">
                    <?php echo $data['pasante']->nota; ?> / 20
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- DOCUMENTOS Y CHECKLIST -->
    <div class="col-md-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0 fw-bold pt-3 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-folder-check text-primary"></i> Checklist de Documentación</span>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalSubirDoc">
                    <i class="bi bi-upload"></i> Subir Documento
                </button>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Tipo Documento</th>
                            <th>Estado Físico</th>
                            <th>Archivo Digital</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($data['documentos'])): ?>
                            <tr><td colspan="4" class="text-center py-5 text-muted">Sin documentos registrados.</td></tr>
                        <?php else: ?>
                            <?php foreach($data['documentos'] as $doc): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-secondary">
                                    <i class="bi bi-file-earmark-text"></i> <?php echo $doc->tipo_documento; ?>
                                </td>
                                <td>
                                    <?php if($doc->entregado): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Entregado</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="bi bi-x-circle-fill"></i> Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($doc->archivo_url): ?>
                                        <a href="<?php echo URL_ROOT . $doc->archivo_url; ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-file-pdf-fill"></i> Ver PDF
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">No subido</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted"><?php echo $doc->observaciones ?: '-'; ?></small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-light p-3 text-muted small">
                * Para legalizar el paso a <strong>En Curso</strong>, debe constar de Carta de Postulación y Aceptación entregadas.
            </div>
        </div>
    </div>
</div>

<!-- Modal Subir Documento -->
<div class="modal fade" id="modalSubirDoc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <form action="<?php echo URL_ROOT; ?>/pasantes/subirDocumento/<?php echo $data['pasante']->id; ?>" method="POST" enctype="multipart/form-data">
          <div class="modal-header bg-primary text-white border-0">
            <h5 class="modal-title"><i class="bi bi-cloud-upload"></i> Cargar Requisito Físico</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body bg-light">
              <div class="alert alert-info py-2 small">
                  El sistema marcará este documento como <strong>Entregado ✅</strong> automáticamente al guardarlo. Puedes anexar el escaneo en PDF o simplemente registrar que lo recibiste en físico.
              </div>
              <div class="mb-3">
                  <label class="form-label fw-bold">Tipo de Documento</label>
                  <select name="tipo_documento" class="form-select" required>
                      <option value="">Seleccione...</option>
                      <option value="Carta de Postulación">Carta de Postulación (Origen)</option>
                      <option value="Carta de Aceptación">Carta de Aceptación (IMATUR)</option>
                      <option value="Evaluación">Formato de Evaluación Final</option>
                      <option value="Otro">Otro (Proyectos, Planillas anexas...)</option>
                  </select>
              </div>
              <div class="mb-3">
                  <label class="form-label fw-bold">Archivo PDF (Opcional si solo es físico)</label>
                  <input type="file" name="archivo" class="form-control" accept=".pdf,.jpeg,.jpg,.png">
              </div>
              <div class="mb-3">
                  <label class="form-label fw-bold">Observaciones</label>
                  <textarea name="observaciones" class="form-control" rows="2" placeholder="Ej: Firmada por el Director académico..."></textarea>
              </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Registrar Entrega</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
