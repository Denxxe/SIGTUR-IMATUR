<?php require_once '../app/views/inc/header.php';
$e = $data['emp'];
$badges = Vacacion::ESTADO_BADGES;
$baseServ = Vacacion::fechaBaseServicio($e);
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Vacaciones</div>
        <h1 class="page__title"><?php echo htmlspecialchars(trim(($e->nombre ?? '') . ' ' . ($e->apellido ?? ''))); ?></h1>
        <p class="page__subtitle">
            C.I. <?php echo htmlspecialchars($e->cedula ?? '—'); ?> ·
            Ingreso a la administración: <strong><?php echo $baseServ ? date('d/m/Y', strtotime($baseServ)) : '—'; ?></strong>
            <?php if (!empty($e->fecha_ingreso_administracion)): ?><span class="sig-badge sig-badge--info" style="margin-left:6px">comisión</span><?php endif; ?>
        </p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/vacaciones/index" class="btn-sig btn-sig--ghost"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
</div>

<!-- Resumen de saldo -->
<div class="anim-slide-up" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:var(--sp-3); margin-bottom:var(--sp-4);">
    <?php
    $tiles = [
        ['Años de servicio', (int)$data['anios'], 'bi-hourglass-split', '#0891B2'],
        ['Derecho del año', (int)$data['derechoAnio'] . ' días', 'bi-calendar-check', '#2563EB'],
        ['Acumulado histórico', (int)$data['acumulado'] . ' días', 'bi-stack', '#7c3aed'],
        ['Ajuste inicial', (int)$data['ajuste'] . ' días', 'bi-sliders', '#d97706'],
        ['Disfrutado (sistema)', (int)$data['disfrutado'] . ' días', 'bi-airplane', '#16a34a'],
    ];
    foreach ($tiles as [$lbl, $val, $ico, $col]): ?>
        <div class="sig-card" style="border-top:3px solid <?php echo $col; ?>;">
            <div class="sig-card__body" style="padding:var(--sp-3);">
                <div style="font-size:11px;text-transform:uppercase;color:var(--text-tertiary);letter-spacing:.5px"><i class="bi <?php echo $ico; ?>"></i> <?php echo $lbl; ?></div>
                <div style="font-size:20px;font-weight:700;color:var(--text-primary);margin-top:4px"><?php echo $val; ?></div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php $s = (int)$data['saldo']; $sc = $s <= 0 ? '#64748b' : ($s > 30 ? '#d97706' : '#059669'); ?>
    <div class="sig-card" style="border-top:3px solid <?php echo $sc; ?>; background:<?php echo $sc; ?>0f;">
        <div class="sig-card__body" style="padding:var(--sp-3);">
            <div style="font-size:11px;text-transform:uppercase;color:var(--text-tertiary);letter-spacing:.5px"><i class="bi bi-wallet2"></i> Saldo disponible</div>
            <div style="font-size:24px;font-weight:800;color:<?php echo $sc; ?>;margin-top:4px"><?php echo $s; ?> días</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Registrar período -->
    <div class="col-lg-7">
        <div class="sig-card anim-slide-up">
            <div class="sig-card__head"><div class="sig-card__title"><i class="bi bi-plus-circle"></i> Registrar período de vacaciones</div></div>
            <div class="sig-card__body">
                <form action="<?php echo URL_ROOT; ?>/vacaciones/registrar" method="POST">
                    <input type="hidden" name="id_empleado" value="<?php echo (int)$e->id; ?>">
                    <div class="row g-3">
                        <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Desde <span class="req">*</span></label>
                            <input type="date" name="fecha_inicio" class="sig-input" required></div></div>
                        <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Hasta <span class="req">*</span></label>
                            <input type="date" name="fecha_fin" class="sig-input" required></div></div>
                        <div class="col-md-4 d-flex align-items-end"><button type="submit" class="btn-sig btn-sig--primary w-100"><i class="bi bi-check-lg"></i> Registrar</button></div>
                        <div class="col-12"><div class="sig-field"><label class="sig-field__label">Observaciones</label>
                            <input type="text" name="observaciones" class="sig-input" placeholder="Opcional"></div></div>
                    </div>
                    <small style="color:var(--text-tertiary)"><i class="bi bi-info-circle"></i> Se cuentan solo días hábiles (sin fines de semana ni feriados).</small>
                </form>
            </div>
        </div>
    </div>
    <!-- Ajuste inicial -->
    <div class="col-lg-5">
        <div class="sig-card anim-slide-up">
            <div class="sig-card__head"><div class="sig-card__title"><i class="bi bi-sliders"></i> Ajuste de saldo inicial</div></div>
            <div class="sig-card__body">
                <form action="<?php echo URL_ROOT; ?>/vacaciones/guardarAjuste" method="POST">
                    <input type="hidden" name="id_empleado" value="<?php echo (int)$e->id; ?>">
                    <div class="sig-field"><label class="sig-field__label">Días ya disfrutados antes del sistema</label>
                        <input type="number" name="ajuste" class="sig-input" min="0" value="<?php echo (int)$data['ajuste']; ?>"></div>
                    <small style="color:var(--text-tertiary);display:block;margin:6px 0">Se resta del saldo. Cárgalo una sola vez al poner el módulo en marcha.</small>
                    <button type="submit" class="btn-sig btn-sig--ghost"><i class="bi bi-floppy"></i> Guardar ajuste</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Períodos -->
<div class="sig-table-wrap anim-slide-up" style="margin-top:var(--sp-4);" data-tabla-buscable data-por-pagina="10">
    <table class="sig-table">
        <thead><tr><th>Desde</th><th>Hasta</th><th>Días hábiles</th><th>Año</th><th>Estado</th><th>Observaciones</th><th class="col-actions">Acciones</th></tr></thead>
        <tbody>
            <?php if (empty($data['periodos'])): ?>
                <tr><td colspan="7" class="sig-table-empty">Sin períodos registrados.</td></tr>
            <?php else: foreach ($data['periodos'] as $p): ?>
                <tr>
                    <td><?php echo $p->fecha_inicio ? date('d/m/Y', strtotime($p->fecha_inicio)) : '—'; ?></td>
                    <td><?php echo $p->fecha_fin ? date('d/m/Y', strtotime($p->fecha_fin)) : '—'; ?></td>
                    <td class="cell-strong"><?php echo (int)$p->dias_tomados; ?></td>
                    <td><?php echo (int)$p->anio; ?></td>
                    <td><span class="sig-badge <?php echo $badges[$p->estado] ?? 'sig-badge--neutral'; ?>"><?php echo htmlspecialchars($p->estado); ?></span></td>
                    <td style="font-size:12.5px;color:var(--text-secondary)"><?php echo htmlspecialchars($p->observaciones ?? '—'); ?></td>
                    <td class="col-actions">
                        <?php foreach (Vacacion::ESTADOS as $est): if ($est === $p->estado) continue; ?>
                            <form action="<?php echo URL_ROOT; ?>/vacaciones/cambiarEstado" method="POST" style="display:inline">
                                <input type="hidden" name="id" value="<?php echo (int)$p->id; ?>">
                                <input type="hidden" name="id_empleado" value="<?php echo (int)$e->id; ?>">
                                <input type="hidden" name="estado" value="<?php echo $est; ?>">
                                <button type="submit" class="row-action" title="Marcar como <?php echo $est; ?>"><?php echo $est; ?></button>
                            </form>
                        <?php endforeach; ?>
                        <a href="<?php echo URL_ROOT; ?>/vacaciones/eliminar/<?php echo (int)$p->id; ?>" class="row-action row-action--del delete-btn" title="Eliminar"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
