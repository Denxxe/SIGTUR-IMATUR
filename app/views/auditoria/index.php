<?php require_once '../app/views/inc/header.php'; ?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h1 class="fw-bold"><i class="bi bi-shield-check text-primary"></i> <?php echo $data['titulo']; ?></h1>
        <p class="text-muted small">Historial completo de inserciones, ediciones y eliminaciones en todos los módulos.</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?php echo URL_ROOT; ?>/auditoria/papelera" class="btn btn-warning shadow-sm">
            <i class="bi bi-recycle"></i> Ver Papelera de Reciclaje
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0" id="tablaBitacora">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">Fecha y Hora</th>
                        <th>Usuario</th>
                        <th>Módulo / Tabla</th>
                        <th>Operación</th>
                        <th>ID Reg.</th>
                        <th>Detalles (Previo vs Nuevo)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['logs'])): ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">No se han registrado acciones de auditoría aún.</td></tr>
                    <?php else: ?>
                        <?php foreach($data['logs'] as $log): ?>
                        <tr>
                            <td class="ps-3 small fw-bold text-nowrap"><?php echo date('d/m/Y H:i:s', strtotime($log->fecha)); ?></td>
                            <td>
                                <span class="badge bg-light text-dark border"><i class="bi bi-person-circle"></i> <?php echo $log->username ?: 'Sistema'; ?></span>
                            </td>
                            <td><span class="text-uppercase small fw-bold text-primary"><?php echo $log->tabla_afectada; ?></span></td>
                            <td>
                                <?php 
                                    $class = 'bg-secondary';
                                    if($log->operacion == 'INSERT') $class = 'bg-success';
                                    if($log->operacion == 'UPDATE') $class = 'bg-primary';
                                    if($log->operacion == 'DELETE') $class = 'bg-danger';
                                    if($log->operacion == 'RESTORE') $class = 'bg-info';
                                ?>
                                <span class="badge <?php echo $class; ?>"><?php echo $log->operacion; ?></span>
                            </td>
                            <td class="fw-bold">#<?php echo $log->record_id; ?></td>
                            <td class="small">
                                <button class="btn btn-sm btn-outline-dark py-0" type="button" data-bs-toggle="collapse" data-bs-target="#log_<?php echo $log->id; ?>">
                                    <i class="bi bi-eye"></i> Ver JSON
                                </button>
                                <div class="collapse mt-2" id="log_<?php echo $log->id; ?>">
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <div class="bg-light p-2 border rounded" style="max-height: 150px; overflow-y: auto;">
                                                <small class="d-block fw-bold text-danger mb-1 border-bottom">Previo:</small>
                                                <pre class="m-0" style="font-size: 10px;"><?php echo $log->datos_previos ? json_encode(json_decode($log->datos_previos), JSON_PRETTY_PRINT) : 'N/A'; ?></pre>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-light p-2 border rounded" style="max-height: 150px; overflow-y: auto;">
                                                <small class="d-block fw-bold text-success mb-1 border-bottom">Nuevo:</small>
                                                <pre class="m-0" style="font-size: 10px;"><?php echo $log->datos_nuevos ? json_encode(json_decode($log->datos_nuevos), JSON_PRETTY_PRINT) : 'N/A'; ?></pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
