<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Sistema · Recuperación</div>
        <h1 class="page__title"><?php echo $data['titulo']; ?></h1>
        <p class="page__subtitle">Recupera registros eliminados de cualquier módulo.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/auditoria/index" class="btn-sig btn-sig--ghost"><i class="bi bi-chevron-left"></i> Volver a Bitácora</a>
    </div>
</div>

<div class="sig-card anim-slide-up">
    <div class="sig-card__body" style="padding:var(--sp-5) var(--sp-6)">
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
                            <div class="accordion-item border rounded mb-3 overflow-hidden" style="border-color:var(--border-default) !important">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#mod-<?php echo $i; ?>-<?php echo $j; ?>">
                                        <span class="sig-badge sig-badge--brand" style="margin-right:8px"><?php echo count($info['items']); ?></span>
                                        <strong><?php echo $nombreMod; ?></strong>
                                    </button>
                                </h2>
                                <div id="mod-<?php echo $i; ?>-<?php echo $j; ?>" class="accordion-collapse collapse" data-bs-parent="#accordionSec-<?php echo $i; ?>">
                                    <div class="accordion-body p-0">
                                        <table class="sig-table">
                                            <thead><tr><th>Registro</th><th>Fecha Eliminación</th><th class="col-actions">Acción</th></tr></thead>
                                            <tbody>
                                                <?php if(empty($info['items'])): ?>
                                                    <tr><td colspan="3" class="sig-table-empty">No hay nada eliminado.</td></tr>
                                                <?php else: ?>
                                                    <?php foreach($info['items'] as $item): ?>
                                                    <tr>
                                                        <td class="cell-strong" style="color:var(--brand-600)"><?php echo $item->display_name; ?></td>
                                                        <td><?php echo date('d/m/Y H:i', strtotime($item->deleted_at)); ?></td>
                                                        <td class="col-actions">
                                                            <form action="<?php echo URL_ROOT; ?>/auditoria/restaurar/<?php echo $info['tabla']; ?>/<?php echo $item->id; ?>" method="POST" onsubmit="return confirm('¿Confirma restaurar?')" style="display:inline">
                                                                <button type="submit" class="row-action row-action--edit"><i class="bi bi-arrow-counterclockwise"></i> Restaurar</button>
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
