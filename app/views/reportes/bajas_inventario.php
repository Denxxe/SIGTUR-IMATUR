<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Reportes · Inventario</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Bienes Dados de Baja'; ?></h1>
        <p class="page__subtitle">Historial de bienes desincorporados del inventario activo.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/reportes/index" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <a href="<?php echo URL_ROOT; ?>/reportes/exportarBajasInventarioCsv" class="btn-sig btn-sig--primary">
            <i class="bi bi-file-earmark-spreadsheet"></i> Exportar CSV
        </a>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Código BN</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Condición</th>
                <th>Fecha de Baja</th>
                <th>Dado de baja por</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['bajas'])): ?>
                <tr>
                    <td colspan="6" class="sig-table-empty">No hay bienes dados de baja registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($data['bajas'] as $b): ?>
                    <tr>
                        <td class="cell-strong" style="color:var(--brand-600);"><?php echo htmlspecialchars($b->codigo_bn ?? 'S/N'); ?></td>
                        <td><?php echo htmlspecialchars($b->nombre ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($b->categoria ?? '—'); ?></td>
                        <td>
                            <?php
                            $condCls = 'sig-badge--neutral';
                            if ($b->condicion == 'Nuevo') $condCls = 'sig-badge--success';
                            elseif ($b->condicion == 'Bueno') $condCls = 'sig-badge--info';
                            elseif ($b->condicion == 'Regular') $condCls = 'sig-badge--warning';
                            elseif (in_array($b->condicion, ['Dañado', 'En Reparación'])) $condCls = 'sig-badge--danger';
                            ?>
                            <span class="sig-badge <?php echo $condCls; ?>"><?php echo htmlspecialchars($b->condicion ?? '—'); ?></span>
                        </td>
                        <td><?php echo $b->deleted_at ? date('d/m/Y H:i', strtotime($b->deleted_at)) : '—'; ?></td>
                        <td style="font-size:12px; color:var(--text-secondary);"><?php echo htmlspecialchars($b->eliminado_por ?? '—'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
