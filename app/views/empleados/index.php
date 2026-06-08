<?php require_once '../app/views/inc/header.php';
$ver     = $data['ver'] ?? 'activos';
$egView  = ($ver === 'egresados');
$motivos = $data['motivos'] ?? [];
$ts = fn($ing, $eg) => Empleado::tiempoServicio($ing, $eg);
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Gestión de Personal</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Gestión de Personal'; ?></h1>
        <p class="page__subtitle">
            <?php echo $egView
                ? 'Histórico de personal egresado (renuncias, despidos, jubilaciones). Su expediente se conserva para constancias y tiempo de servicio.'
                : 'Registro y administración del personal activo de la institución.'; ?>
        </p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/empleados/nuevo" class="btn-sig btn-sig--primary">
            <i class="bi bi-person-plus"></i> Registrar Empleado
        </a>
    </div>
</div>

<!-- Pestañas Activos / Egresados -->
<div class="anim-slide-up" style="display:flex;gap:8px;margin-bottom:16px;">
    <a href="<?php echo URL_ROOT; ?>/empleados/index" class="btn-sig btn-sig--sm <?php echo $egView ? 'btn-sig--ghost' : 'btn-sig--primary'; ?>">
        <i class="bi bi-people"></i> Activos
    </a>
    <a href="<?php echo URL_ROOT; ?>/empleados/index?ver=egresados" class="btn-sig btn-sig--sm <?php echo $egView ? 'btn-sig--primary' : 'btn-sig--ghost'; ?>">
        <i class="bi bi-archive"></i> Egresados (histórico)
    </a>
</div>

<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Expediente</th>
                <th>Cédula</th>
                <th>Nombre</th>
                <th>Cargo</th>
                <?php if ($egView): ?>
                    <th>F. Egreso</th>
                    <th>Motivo</th>
                    <th>Tiempo de servicio</th>
                <?php else: ?>
                    <th>Departamento</th>
                <?php endif; ?>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['empleados'])): ?>
                <tr><td colspan="<?php echo $egView ? 8 : 6; ?>" class="sig-table-empty">
                    <?php echo $egView ? 'No hay empleados egresados.' : 'No hay empleados activos registrados.'; ?>
                </td></tr>
            <?php else: ?>
                <?php foreach ($data['empleados'] ?? [] as $emp): ?>
                    <tr>
                        <td class="cell-strong"><?php echo $emp->nro_expediente ?? 'N/A'; ?></td>
                        <td><?php echo $emp->cedula ?? 'N/A'; ?></td>
                        <td><?php echo ($emp->nombre ?? 'N/A') . ' ' . ($emp->apellido ?? ''); ?></td>
                        <td><span class="sig-badge sig-badge--info"><?php echo $emp->cargo ?? 'Sin cargo'; ?></span></td>
                        <?php if ($egView): ?>
                            <td><?php echo !empty($emp->fecha_egreso) ? date('d/m/Y', strtotime($emp->fecha_egreso)) : '—'; ?></td>
                            <td><span class="sig-badge sig-badge--warning"><?php echo htmlspecialchars($emp->motivo_egreso ?? '—'); ?></span></td>
                            <td><?php echo htmlspecialchars($ts($emp->fecha_ingreso ?? null, $emp->fecha_egreso ?? null)); ?></td>
                        <?php else: ?>
                            <td><?php echo $emp->departamento ?? 'Sin dpto.'; ?></td>
                        <?php endif; ?>
                        <td class="col-actions">
                            <a href="<?php echo URL_ROOT; ?>/empleados/detalle/<?php echo $emp->id; ?>" class="row-action">
                                <i class="bi bi-folder2-open"></i> Expediente
                            </a>
                            <?php if ($egView): ?>
                                <button type="button" class="row-action js-reingreso"
                                        data-id="<?php echo $emp->id; ?>"
                                        data-nombre="<?php echo htmlspecialchars($emp->nombre . ' ' . $emp->apellido); ?>">
                                    <i class="bi bi-arrow-counterclockwise"></i> Reingreso
                                </button>
                            <?php else: ?>
                                <a href="<?php echo URL_ROOT; ?>/empleados/editar/<?php echo $emp->id; ?>" class="row-action row-action--edit">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                                <button type="button" class="row-action row-action--del js-egreso"
                                        data-id="<?php echo $emp->id; ?>"
                                        data-nombre="<?php echo htmlspecialchars($emp->nombre . ' ' . $emp->apellido); ?>"
                                        data-ingreso="<?php echo htmlspecialchars($emp->fecha_ingreso ?? ''); ?>">
                                    <i class="bi bi-box-arrow-right"></i> Egresar
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal: Procesar egreso -->
<div class="modal fade" id="modalEgreso" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/empleados/egresar" method="POST" class="modal-content needs-validation" novalidate>
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-box-arrow-right"></i> Procesar egreso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_empleado" id="eg_id">
                <p class="text-muted" style="font-size:13px;">
                    Vas a dar de baja a <strong id="eg_nombre"></strong>. El expediente se conserva en el histórico
                    de egresados (no se elimina) y seguirá disponible para constancias.
                </p>
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Fecha de egreso <span class="req">*</span></label>
                    <input type="date" name="fecha_egreso" id="eg_fecha" class="sig-input" required max="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Motivo <span class="req">*</span></label>
                    <select name="motivo_egreso" class="sig-input" required>
                        <option value="">— Seleccione —</option>
                        <?php foreach ($motivos as $m): ?>
                            <option value="<?php echo htmlspecialchars($m); ?>"><?php echo htmlspecialchars($m); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="sig-field mb-2">
                    <label class="sig-field__label">Observación</label>
                    <textarea name="observacion_egreso" class="sig-input" rows="2" placeholder="N° de oficio, detalle del motivo, etc. (opcional)"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--danger"><i class="bi bi-box-arrow-right"></i> Confirmar egreso</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Reingreso -->
<div class="modal fade" id="modalReingreso" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/empleados/reingresar" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-counterclockwise"></i> Reingreso de empleado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_empleado" id="re_id">
                <p class="text-muted" style="font-size:13px;">
                    Vas a reincorporar a <strong id="re_nombre"></strong> a la nómina activa.
                    El egreso anterior queda guardado en su historial.
                </p>
                <div class="sig-field mb-2">
                    <label class="sig-field__label">Observación</label>
                    <textarea name="reingreso_observacion" class="sig-input" rows="2" placeholder="Motivo del reingreso (opcional)"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-arrow-counterclockwise"></i> Confirmar reingreso</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const mEg = new bootstrap.Modal(document.getElementById('modalEgreso'));
    const mRe = new bootstrap.Modal(document.getElementById('modalReingreso'));

    document.querySelectorAll('.js-egreso').forEach(btn => btn.addEventListener('click', () => {
        document.getElementById('eg_id').value = btn.dataset.id;
        document.getElementById('eg_nombre').textContent = btn.dataset.nombre;
        const f = document.getElementById('eg_fecha');
        if (btn.dataset.ingreso) f.min = btn.dataset.ingreso;
        f.value = '';
        mEg.show();
    }));

    document.querySelectorAll('.js-reingreso').forEach(btn => btn.addEventListener('click', () => {
        document.getElementById('re_id').value = btn.dataset.id;
        document.getElementById('re_nombre').textContent = btn.dataset.nombre;
        mRe.show();
    }));
});
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
