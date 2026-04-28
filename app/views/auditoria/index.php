<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Sistema · Auditoría</div>
        <h1 class="page__title"><?php echo $data['titulo']; ?></h1>
        <p class="page__subtitle">Historial completo de inserciones, ediciones y eliminaciones.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/auditoria/papelera" class="btn-sig btn-sig--ghost">
            <i class="bi bi-recycle"></i> Ver Papelera
        </a>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr><th>Fecha y Hora</th><th>Usuario</th><th>Módulo</th><th>Operación</th><th>ID Reg.</th><th>Detalles</th></tr>
        </thead>
        <tbody>
            <?php if(empty($data['logs'])): ?>
                <tr><td colspan="6" class="sig-table-empty">No se han registrado acciones de auditoría.</td></tr>
            <?php else: ?>
                <?php foreach($data['logs'] as $log): ?>
                <tr>
                    <td class="cell-strong" style="white-space:nowrap"><?php echo date('d/m/Y H:i:s', strtotime($log->fecha)); ?></td>
                    <td><span class="sig-badge sig-badge--neutral"><i class="bi bi-person-circle"></i> <?php echo $log->username ?: 'Sistema'; ?></span></td>
                    <td style="text-transform:uppercase;font-size:11px;font-weight:700;color:var(--brand-600)"><?php echo $log->tabla_afectada; ?></td>
                    <td>
                        <?php
                            $cls = 'sig-badge--neutral';
                            if($log->operacion == 'INSERT') $cls = 'sig-badge--success';
                            if($log->operacion == 'UPDATE') $cls = 'sig-badge--info';
                            if($log->operacion == 'DELETE') $cls = 'sig-badge--danger';
                            if($log->operacion == 'RESTORE') $cls = 'sig-badge--brand';
                        ?>
                        <span class="sig-badge <?php echo $cls; ?>"><?php echo $log->operacion; ?></span>
                    </td>
                    <td><span class="cell-id">#<?php echo $log->record_id; ?></span></td>
                    <td>
                        <button class="row-action row-action--view" type="button" data-bs-toggle="collapse" data-bs-target="#log_<?php echo $log->id; ?>"><i class="bi bi-eye"></i> JSON</button>
                        <div class="collapse mt-2" id="log_<?php echo $log->id; ?>">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div style="background:var(--bg-muted);padding:8px;border-radius:8px;border:1px solid var(--border-subtle);max-height:150px;overflow-y:auto">
                                        <small style="display:block;font-weight:700;color:var(--danger-600);margin-bottom:4px;border-bottom:1px solid var(--border-subtle);padding-bottom:4px">Previo:</small>
                                        <pre style="margin:0;font-size:10px;font-family:var(--font-mono);color:var(--text-secondary)"><?php echo $log->datos_previos ? json_encode(json_decode($log->datos_previos), JSON_PRETTY_PRINT) : 'N/A'; ?></pre>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div style="background:var(--bg-muted);padding:8px;border-radius:8px;border:1px solid var(--border-subtle);max-height:150px;overflow-y:auto">
                                        <small style="display:block;font-weight:700;color:var(--success-600);margin-bottom:4px;border-bottom:1px solid var(--border-subtle);padding-bottom:4px">Nuevo:</small>
                                        <pre style="margin:0;font-size:10px;font-family:var(--font-mono);color:var(--text-secondary)"><?php echo $log->datos_nuevos ? json_encode(json_decode($log->datos_nuevos), JSON_PRETTY_PRINT) : 'N/A'; ?></pre>
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

<?php require_once '../app/views/inc/footer.php'; ?>
