<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Talento Humano</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Vacaciones'; ?></h1>
        <p class="page__subtitle">Saldo de vacaciones del personal: 15 días hábiles base + 1 por año de servicio (tope 30). Las no disfrutadas se acumulan.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/vacaciones/feriados" class="btn-sig btn-sig--ghost">
            <i class="bi bi-calendar-event"></i> Feriados (<?php echo (int)($data['totalFeriados'] ?? 0); ?>)
        </a>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="15" data-buscar-placeholder="Buscar por nombre o cédula…">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Empleado</th>
                <th>Cédula</th>
                <th>Años servicio</th>
                <th>Derecho del año</th>
                <th>Acumulado</th>
                <th>Disfrutado</th>
                <th>Saldo</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['filas'])): ?>
                <tr><td colspan="8" class="sig-table-empty">No hay personal activo.</td></tr>
            <?php else: foreach ($data['filas'] as $f): $e = $f['emp']; ?>
                <tr>
                    <td class="cell-strong"><?php echo htmlspecialchars(trim(($e->nombre ?? '') . ' ' . ($e->apellido ?? ''))); ?></td>
                    <td><?php echo htmlspecialchars($e->cedula ?? '—'); ?></td>
                    <td><?php echo (int)$f['anios']; ?></td>
                    <td><?php echo (int)$f['derechoAnio']; ?></td>
                    <td><?php echo (int)$f['acumulado']; ?></td>
                    <td><?php echo (int)$f['disfrutado']; ?></td>
                    <td>
                        <?php $s = (int)$f['saldo']; $cls = $s <= 0 ? 'sig-badge--neutral' : ($s > 30 ? 'sig-badge--warning' : 'sig-badge--success'); ?>
                        <span class="sig-badge <?php echo $cls; ?>"><?php echo $s; ?> día(s)</span>
                    </td>
                    <td class="col-actions">
                        <a href="<?php echo URL_ROOT; ?>/vacaciones/empleado/<?php echo $e->id; ?>" class="row-action row-action--view"><i class="bi bi-eye"></i> Ver / Registrar</a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
