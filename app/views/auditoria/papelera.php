<?php require_once '../app/views/inc/header.php'; ?>

<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h1 class="fw-bold"><i class="bi bi-recycle text-warning"></i> <?php echo $data['titulo']; ?></h1>
        <p class="text-muted small">Recupera registros eliminados de cualquier módulo. La restauración incluye dependencias críticas.</p>
    </div>
    <div class="col-md-5 text-end">
        <a href="<?php echo URL_ROOT; ?>/auditoria/index" class="btn btn-primary shadow-sm">
            <i class="bi bi-chevron-left"></i> Volver a Bitácora
        </a>
    </div>
</div>

<?php flash('auditoria_msg'); ?>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <!-- Pestañas por Módulo -->
        <ul class="nav nav-pills mb-4 gap-2" id="papeleraTabs" role="tablist">
            <?php $i = 0; foreach($data['secciones'] as $nombreSec => $modulos): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $i == 0 ? 'active' : ''; ?> px-4 border shadow-sm" id="tab-<?php echo $i; ?>" data-bs-toggle="pill" data-bs-target="#sec-<?php echo $i; ?>" type="button">
                        <strong><?php echo $nombreSec; ?></strong>
                    </button>
                </li>
            <?php $i++; endforeach; ?>
        </ul>

        <div class="tab-content" id="papeleraTabsContent">
            <?php $i = 0; foreach($data['secciones'] as $nombreSec => $modulos): ?>
                <div class="tab-pane fade <?php echo $i == 0 ? 'show active' : ''; ?>" id="sec-<?php echo $i; ?>" role="tabpanel">
                    
                    <div class="accordion border-0" id="accordionSec-<?php echo $i; ?>">
                        <?php $j = 0; foreach($modulos as $nombreMod => $info): ?>
                            <div class="accordion-item border rounded mb-3 overflow-hidden shadow-sm">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#mod-<?php echo $i; ?>-<?php echo $j; ?>">
                                        <span class="badge bg-primary me-2"><?php echo count($info['items']); ?></span> 
                                        <strong><?php echo $nombreMod; ?></strong>
                                    </button>
                                </h2>
                                <div id="mod-<?php echo $i; ?>-<?php echo $j; ?>" class="accordion-collapse collapse" data-bs-parent="#accordionSec-<?php echo $i; ?>">
                                    <div class="accordion-body p-0">
                                        <table class="table table-hover table-striped mb-0 align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="ps-4">Registro (Identificador)</th>
                                                    <th>Fecha Eliminación</th>
                                                    <th class="text-end pe-4">Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if(empty($info['items'])): ?>
                                                    <tr><td colspan="3" class="text-center py-4 text-muted small">No hay nada eliminado en esta categoría.</td></tr>
                                                <?php else: ?>
                                                    <?php foreach($info['items'] as $item): ?>
                                                    <tr>
                                                        <td class="ps-4 fw-bold text-primary"><?php echo $item->display_name; ?></td>
                                                        <td><?php echo date('d/m/Y H:i', strtotime($item->deleted_at)); ?></td>
                                                        <td class="text-end pe-4">
                                                            <form action="<?php echo URL_ROOT; ?>/auditoria/restaurar/<?php echo $info['tabla']; ?>/<?php echo $item->id; ?>" method="POST" onsubmit="return confirm('¿Confirma que desea restaurar este registro y sus asociaciones?')">
                                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                                    <i class="bi bi-arrow-counterclockwise"></i> Restaurar
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php $j++; endforeach; ?>
                    </div>

                </div>
            <?php $i++; endforeach; ?>
        </div>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
