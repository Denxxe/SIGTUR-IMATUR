<?php require_once '../app/views/inc/header.php';
$limite = (int)($data['limite'] ?? 3);
// Estado/semáforo por empleado
$estado = function ($r) use ($limite) {
    $am = (int)$r->amonestaciones; $fa = (int)$r->faltas;
    $contratado = ($r->tipo_contrato === 'Contratado');
    if ($am >= $limite) return ['Causa de despido', 'sig-badge--danger'];
    if ($am === $limite - 1) return ['En riesgo', 'sig-badge--warning'];
    if ($fa >= 3) return ['Faltas acumuladas', 'sig-badge--warning'];
    if ($am > 0 || $fa > 0) return ['Con observaciones', 'sig-badge--info'];
    return ['Sin novedades', 'sig-badge--success'];
};
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Disciplina</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Faltas y Amonestaciones'; ?></h1>
        <p class="page__subtitle">El sistema cuenta faltas y amonestaciones; RRHH las registra. <?php echo $limite; ?> amonestaciones = causa de despido (Contratado).</p>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--ghost" data-bs-toggle="modal" data-bs-target="#modalFalta"><i class="bi bi-exclamation-circle"></i> Registrar falta</button>
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalAmonestacion"><i class="bi bi-flag"></i> Registrar amonestación</button>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Empleado</th>
                <th>Departamento</th>
                <th>Contrato</th>
                <th>Faltas</th>
                <th>Amonestaciones</th>
                <th>Estado</th>
                <th class="col-actions">Detalle</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['roster'])): ?>
                <tr><td colspan="7" class="sig-table-empty">No hay empleados registrados.</td></tr>
            <?php else: foreach ($data['roster'] as $r): [$txt, $cls] = $estado($r); ?>
                <tr>
                    <td class="cell-strong"><?php echo htmlspecialchars($r->nombre . ' ' . $r->apellido); ?></td>
                    <td style="color:var(--text-secondary);font-size:13px"><?php echo htmlspecialchars($r->departamento ?? '—'); ?></td>
                    <td><span class="sig-badge sig-badge--neutral"><?php echo htmlspecialchars($r->tipo_contrato ?? '—'); ?></span></td>
                    <td><strong><?php echo (int)$r->faltas; ?></strong></td>
                    <td><strong style="color:<?php echo ((int)$r->amonestaciones >= $limite) ? 'var(--danger, #EF4444)' : 'inherit'; ?>"><?php echo (int)$r->amonestaciones; ?>/<?php echo $limite; ?></strong></td>
                    <td><span class="sig-badge <?php echo $cls; ?>"><?php echo $txt; ?></span></td>
                    <td class="col-actions">
                        <a href="<?php echo URL_ROOT; ?>/amonestaciones/empleado/<?php echo $r->id; ?>" class="row-action"><i class="bi bi-eye"></i> Ver</a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php
// Modales de registro (selección de empleado)
$selectEmpleados = function ($id = 'id_empleado') use ($data) {
    $html = '<select name="id_empleado" class="sig-select js-search" required><option value="">Seleccione empleado...</option>';
    foreach ($data['empleados'] ?? [] as $e) {
        $html .= '<option value="' . $e->id . '">' . htmlspecialchars(($e->nombre ?? '') . ' ' . ($e->apellido ?? '')) . ' (' . htmlspecialchars($e->cedula ?? '') . ')</option>';
    }
    return $html . '</select>';
};
?>

<!-- Modal: Falta -->
<div class="modal fade" id="modalFalta" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/amonestaciones/registrarFalta" method="POST" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Registrar falta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="sig-field mb-3"><label class="sig-field__label">Empleado <span class="req">*</span></label><?php echo $selectEmpleados(); ?></div>
                <div class="sig-field mb-3"><label class="sig-field__label">Fecha <span class="req">*</span></label>
                    <input type="date" name="fecha" class="sig-input" required value="<?php echo date('Y-m-d'); ?>"></div>
                <div class="sig-field"><label class="sig-field__label">Motivo / observación</label>
                    <textarea name="motivo" class="sig-textarea" rows="2"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Registrar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Amonestación -->
<div class="modal fade" id="modalAmonestacion" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/amonestaciones/registrarAmonestacion" method="POST" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Registrar amonestación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="sig-field mb-3"><label class="sig-field__label">Empleado <span class="req">*</span></label><?php echo $selectEmpleados(); ?></div>
                <div class="sig-field mb-3"><label class="sig-field__label">Fecha <span class="req">*</span></label>
                    <input type="date" name="fecha" class="sig-input" required value="<?php echo date('Y-m-d'); ?>"></div>
                <div class="sig-field"><label class="sig-field__label">Motivo <span class="req">*</span></label>
                    <textarea name="motivo" class="sig-textarea" rows="3" required></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Registrar</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
