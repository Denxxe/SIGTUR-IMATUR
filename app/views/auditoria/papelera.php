<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Sistema · Recuperación</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Papelera de Reciclaje'; ?></h1>
        <p class="page__subtitle">Recupera registros eliminados de los módulos a los que tienes acceso.</p>
    </div>
    <div class="page__actions">
        <?php if (RolesController::roleHasModulo('AuditoriaController')): ?>
        <a href="<?php echo URL_ROOT; ?>/auditoria/index" class="btn-sig btn-sig--ghost"><i class="bi bi-chevron-left"></i> Volver a Bitácora</a>
        <?php else: ?>
        <a href="<?php echo URL_ROOT; ?>" class="btn-sig btn-sig--ghost"><i class="bi bi-chevron-left"></i> Volver al Panel</a>
        <?php endif; ?>
    </div>
</div>

<?php $secciones = $data['secciones'] ?? []; $totalGlobal = (int)($data['total_global'] ?? 0); ?>

<!-- Resumen superior -->
<div class="row g-3 mb-4 anim-slide-up">
    <div class="col-12 col-md-4">
        <div class="sig-card h-100" style="border-left:3px solid var(--brand-600);">
            <div class="sig-card__body" style="padding:var(--sp-4);display:flex;align-items:center;gap:var(--sp-3);">
                <div style="flex-shrink:0;width:46px;height:46px;border-radius:10px;background:var(--brand-50);display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-recycle" style="font-size:1.4rem;color:var(--brand-600);"></i>
                </div>
                <div>
                    <div style="font-size:1.9rem;font-weight:900;line-height:1;color:var(--text-primary);"><?php echo number_format($totalGlobal); ?></div>
                    <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-secondary);">registros recuperables</div>
                </div>
            </div>
        </div>
    </div>
    <?php foreach ($secciones as $nombreSec => $sec): ?>
    <div class="col-6 col-md-2">
        <div class="sig-card h-100">
            <div class="sig-card__body" style="padding:var(--sp-3);text-align:center;">
                <i class="bi <?php echo $sec['icon']; ?>" style="font-size:1.1rem;color:var(--brand-600);"></i>
                <div style="font-size:1.3rem;font-weight:800;color:var(--text-primary);margin-top:2px;"><?php echo (int)$sec['total']; ?></div>
                <div style="font-size:0.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.03em;color:var(--text-tertiary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($nombreSec); ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="sig-card anim-slide-up">
    <div class="sig-card__body" style="padding:var(--sp-5) var(--sp-6)">

        <?php if (empty($secciones)): ?>
            <div style="text-align:center;padding:var(--sp-8) var(--sp-4);color:var(--text-tertiary);">
                <i class="bi bi-shield-lock" style="font-size:2.5rem;display:block;margin-bottom:var(--sp-3);"></i>
                <div style="font-size:14px;font-weight:600;color:var(--text-secondary);">No tienes módulos con papelera disponible.</div>
                <div style="font-size:12px;margin-top:4px;">El acceso a cada pestaña depende de los módulos asignados a tu rol.</div>
            </div>
        <?php else: ?>

        <!-- Pestañas por Módulo -->
        <ul class="nav nav-pills mb-4 gap-2" id="papeleraTabs" role="tablist">
            <?php $i = 0;
            foreach ($secciones as $nombreSec => $sec): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $i == 0 ? 'active' : ''; ?> px-4 border shadow-sm d-flex align-items-center gap-2" id="tab-<?php echo $i; ?>" data-bs-toggle="pill" data-bs-target="#sec-<?php echo $i; ?>" type="button">
                        <i class="bi <?php echo $sec['icon']; ?>"></i>
                        <strong><?php echo htmlspecialchars($nombreSec); ?></strong>
                        <?php if ((int)$sec['total'] > 0): ?>
                            <span class="badge rounded-pill" style="background:var(--brand-600);font-size:10px;"><?php echo (int)$sec['total']; ?></span>
                        <?php endif; ?>
                    </button>
                </li>
            <?php $i++;
            endforeach; ?>
        </ul>

        <div class="tab-content" id="papeleraTabsContent">
            <?php $i = 0;
            foreach ($secciones as $nombreSec => $sec): ?>
                <div class="tab-pane fade <?php echo $i == 0 ? 'show active' : ''; ?>" id="sec-<?php echo $i; ?>" role="tabpanel">
                    <div class="accordion border-0" id="accordionSec-<?php echo $i; ?>">
                        <?php $j = 0;
                        foreach ($sec['modulos'] as $nombreMod => $info):
                            $cnt = count($info['items']); ?>
                            <div class="accordion-item border rounded mb-3 overflow-hidden" style="border-color:var(--border-default) !important">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#mod-<?php echo $i; ?>-<?php echo $j; ?>">
                                        <span class="sig-badge <?php echo $cnt > 0 ? 'sig-badge--brand' : 'sig-badge--neutral'; ?>" style="margin-right:10px;min-width:26px;text-align:center;"><?php echo $cnt; ?></span>
                                        <i class="bi <?php echo $info['icon']; ?>" style="margin-right:8px;color:var(--text-secondary);"></i>
                                        <strong><?php echo htmlspecialchars($nombreMod); ?></strong>
                                    </button>
                                </h2>
                                <div id="mod-<?php echo $i; ?>-<?php echo $j; ?>" class="accordion-collapse collapse" data-bs-parent="#accordionSec-<?php echo $i; ?>">
                                    <div class="accordion-body p-0">
                                        <table class="sig-table">
                                            <thead>
                                                <tr>
                                                    <th>Registro</th>
                                                    <th>Fecha Eliminación</th>
                                                    <th>Eliminado Por</th>
                                                    <th class="col-actions">Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($info['items'])): ?>
                                                    <tr>
                                                        <td colspan="4" class="sig-table-empty">
                                                            <i class="bi bi-inbox" style="opacity:.5;margin-right:6px;"></i>No hay nada eliminado en este módulo.
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($info['items'] as $item): ?>
                                                        <tr>
                                                            <td class="cell-strong" style="color:var(--brand-600)"><?php echo htmlspecialchars($item->display_name ?? ('#' . $item->id)); ?></td>
                                                            <td><i class="bi bi-clock-history" style="opacity:.5;margin-right:5px;"></i><?php echo $item->deleted_at ? date('d/m/Y H:i', strtotime($item->deleted_at)) : '—'; ?></td>
                                                            <td>
                                                                <?php if (!empty($item->deleted_by_name)): ?>
                                                                    <span class="sig-badge sig-badge--neutral"><i class="bi bi-person"></i> <?php echo htmlspecialchars($item->deleted_by_name); ?></span>
                                                                <?php else: ?>
                                                                    <span style="color:var(--text-tertiary);font-style:italic;">Sistema</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="col-actions">
                                                                <form action="<?php echo URL_ROOT; ?>/auditoria/restaurar/<?php echo $info['tabla']; ?>/<?php echo $item->id; ?>" method="POST" onsubmit="return confirm('¿Confirma restaurar este registro y sus vínculos?')" style="display:inline">
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
                        <?php $j++;
                        endforeach; ?>
                    </div>
                </div>
            <?php $i++;
            endforeach; ?>
        </div>

        <?php endif; ?>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
