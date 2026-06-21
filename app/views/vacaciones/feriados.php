<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Vacaciones</div>
        <h1 class="page__title">Calendario de Feriados</h1>
        <p class="page__subtitle">Los feriados se descuentan al contar los días hábiles de vacaciones. Los recurrentes se repiten cada año; los movibles (Carnaval, Semana Santa) se cargan por fecha puntual.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/vacaciones/index" class="btn-sig btn-sig--ghost"><i class="bi bi-arrow-left"></i> Volver</a>
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalFeriado"><i class="bi bi-plus-lg"></i> Agregar Feriado</button>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="15" data-buscar-placeholder="Buscar feriado…">
    <table class="sig-table">
        <thead><tr><th>Fecha</th><th>Nombre</th><th>Tipo</th><th class="col-actions">Acciones</th></tr></thead>
        <tbody>
            <?php if (empty($data['feriados'])): ?>
                <tr><td colspan="4" class="sig-table-empty">No hay feriados registrados.</td></tr>
            <?php else: foreach ($data['feriados'] as $f): ?>
                <tr>
                    <td class="cell-strong"><?php echo $f->recurrente ? date('d/m', strtotime($f->fecha)) : date('d/m/Y', strtotime($f->fecha)); ?></td>
                    <td><?php echo htmlspecialchars($f->nombre); ?></td>
                    <td>
                        <?php if ($f->recurrente): ?>
                            <span class="sig-badge sig-badge--info">Recurrente (cada año)</span>
                        <?php else: ?>
                            <span class="sig-badge sig-badge--warning">Puntual (movible)</span>
                        <?php endif; ?>
                    </td>
                    <td class="col-actions">
                        <a href="<?php echo URL_ROOT; ?>/vacaciones/eliminarFeriado/<?php echo (int)$f->id; ?>" class="row-action row-action--del delete-btn" title="Eliminar"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="modalFeriado" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/vacaciones/agregarFeriado" method="POST" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Nuevo Feriado</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="sig-field mb-3"><label class="sig-field__label">Fecha <span class="req">*</span></label>
                    <input type="date" name="fecha" class="sig-input" required></div>
                <div class="sig-field mb-3"><label class="sig-field__label">Nombre <span class="req">*</span></label>
                    <input type="text" name="nombre" class="sig-input" required placeholder="Ej: Lunes de Carnaval"></div>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer">
                    <input type="checkbox" name="recurrente" value="1">
                    Se repite cada año (mismo mes/día) — desmarca para feriados movibles
                </label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
