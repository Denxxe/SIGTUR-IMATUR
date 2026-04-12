<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-shield-lock"></i> <?php echo $data['titulo']; ?></h1>
        <p class="text-muted">Últimos 500 eventos operativos registrados en el sistema</p>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 text-sm">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Fecha / Hora</th>
                        <th>Tabla</th>
                        <th>Operación</th>
                        <th>Realizado por</th>
                        <th>IP</th>
                        <th class="text-center">Trazas JSON</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['logs'])): ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">No hay registros de auditoría almacenados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['logs'] as $log): ?>
                            <tr>
                                <td class="ps-4 small"><?php echo $log->fecha; ?></td>
                                <td class="fw-bold text-muted"><?php echo $log->tabla_afectada; ?> (#<?php echo $log->record_id; ?>)</td>
                                <td>
                                    <?php 
                                        $color = 'bg-secondary';
                                        if ($log->operacion == 'INSERT') $color = 'bg-success';
                                        if ($log->operacion == 'UPDATE') $color = 'bg-primary';
                                        if ($log->operacion == 'DELETE') $color = 'bg-danger';
                                    ?>
                                    <span class="badge <?php echo $color; ?>"><?php echo $log->operacion; ?></span>
                                </td>
                                <td><?php echo $log->username ?: '<span class="text-secondary fst-italic">Sistema</span>'; ?></td>
                                <td class="small text-muted"><?php echo $log->ip_direccion; ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-secondary" onclick='verJSON(<?php echo htmlspecialchars($log->datos_previos, ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars($log->datos_nuevos, ENT_QUOTES, 'UTF-8'); ?>)'>
                                        <i class="bi bi-code-slash"></i> Ver Payload
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal JSON -->
<div class="modal fade" id="modalJSON" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Inspección de Payload de Auditoría</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 border-end">
                        <h6 class="fw-bold text-danger">Estado Previo</h6>
                        <pre id="json_previo" class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"></pre>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-success">Estado Nuevo</h6>
                        <pre id="json_nuevo" class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function verJSON(previo, nuevo) {
        document.getElementById('json_previo').innerText = previo ? JSON.stringify(previo, null, 4) : 'N/A';
        document.getElementById('json_nuevo').innerText = nuevo ? JSON.stringify(nuevo, null, 4) : 'N/A';
        new bootstrap.Modal(document.getElementById('modalJSON')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
