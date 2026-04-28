<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Asistencia</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Asistencias'; ?></h1>
        <p class="page__subtitle">Registro de entrada y salida del personal IMATUR.</p>
    </div>
</div>

<!-- Tarjeta de marcaje -->
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6)">
    <div class="sig-card__body" style="padding:var(--sp-5) var(--sp-6)">
        <form action="<?php echo URL_ROOT; ?>/asistencias/marcar" method="POST" style="display:flex;gap:var(--sp-3);align-items:center;flex-wrap:wrap">
            <select name="id_empleado" class="sig-select" required style="flex:1;min-width:200px">
                <option value="">Seleccione su nombre...</option>
                <?php foreach ($data['empleados'] ?? [] as $e): ?>
                    <option value="<?php echo $e->id; ?>"><?php echo ($e->nombre ?? '') . ' ' . ($e->apellido ?? ''); ?> (<?php echo $e->cedula ?? ''; ?>)</option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-sig btn-sig--primary" style="height:40px"><i class="bi bi-check2-circle"></i> MARCAR</button>
            <span class="sig-badge sig-badge--neutral" id="clock" style="font-size:14px;padding:6px 14px">--:--:--</span>
        </form>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up">
    <div style="padding:var(--sp-4) var(--sp-5);border-bottom:1px solid var(--border-subtle);background:var(--bg-muted)">
        <strong style="font-size:var(--fs-md);color:var(--text-primary)">Registros Recientes</strong>
    </div>
    <table class="sig-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Empleado</th>
                <th>Expediente</th>
                <th>Entrada</th>
                <th>Salida</th>
                <th>Observación</th>
                <th class="col-actions">Eliminar</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['asistencias'])): ?>
                <tr>
                    <td colspan="7" class="sig-table-empty">Aún no hay marcajes registrados hoy.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($data['asistencias'] as $as): ?>
                    <tr>
                        <td class="cell-strong"><?php echo date('d/m/Y', strtotime($as->fecha)); ?></td>
                        <td><?php echo $as->nombre . ' ' . $as->apellido; ?></td>
                        <td><?php echo $as->nro_expediente; ?></td>
                        <td><span class="sig-badge sig-badge--success"><?php echo $as->hora_entrada; ?></span></td>
                        <td>
                            <?php if ($as->hora_salida): ?>
                                <span class="sig-badge sig-badge--danger"><?php echo $as->hora_salida; ?></span>
                            <?php else: ?>
                                <span class="sig-badge sig-badge--warning">PENDIENTE</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12.5px;color:var(--text-secondary)"><?php echo $as->observacion; ?></td>
                        <td class="col-actions">
                            <a href="<?php echo URL_ROOT; ?>/asistencias/delete/<?php echo $as->id; ?>" class="row-action row-action--del delete-btn">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    function updateClock() {
        const now = new Date();
        document.getElementById('clock').innerText = now.toLocaleTimeString('es-ES', {
            hour12: false
        });
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>

<?php require_once '../app/views/inc/footer.php'; ?>