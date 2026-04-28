<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Seguridad · Control de Accesos</div>
        <h1 class="page__title"><?php echo $data['titulo']; ?></h1>
        <p class="page__subtitle">Control de flujo y marcaje de entrada/salida de personas ajenas a la institución.</p>
    </div>
</div>

<div class="row g-4 mb-8 anim-slide-up">
    <!-- FORMULARIO DE REGISTRO RÁPIDO -->
    <div class="col-md-5 order-md-2">
        <div class="sig-card" style="border-top: 4px solid var(--brand-500); height: 100%;">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-clock-history" style="color:var(--brand-500);"></i> Registro de Marcaje</div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-6);">
                <form action="<?php echo URL_ROOT; ?>/visitas/registrar" method="POST">
                    <div class="sig-field mb-4">
                        <label class="sig-field__label">Visitante (Quién llega/sale) <span class="req">*</span></label>
                        <select name="id_visitante" class="sig-select" required>
                            <option value="">Seleccione un visitante...</option>
                            <?php foreach ($data['visitantes'] as $v): ?>
                                <option value="<?php echo $v->id; ?>"><?php echo $v->nombre . ' ' . $v->apellido; ?> (<?php echo $v->cedula; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="sig-field mb-4">
                        <label class="sig-field__label">Empleado a visitar</label>
                        <select name="id_empleado" class="sig-select">
                            <option value="">Sin asignar / Trámite general</option>
                            <?php foreach ($data['empleados'] as $e): ?>
                                <option value="<?php echo $e->id; ?>"><?php echo $e->nombre . ' ' . $e->apellido; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p style="font-size:11px; color:var(--text-tertiary); margin-top:4px;">Opcional si es una nueva entrada.</p>
                    </div>

                    <div class="sig-field mb-6">
                        <label class="sig-field__label">Motivo de la visita</label>
                        <input type="text" name="motivo" class="sig-input" placeholder="Ej: Entrega de documentos, Reunión...">
                    </div>

                    <button type="submit" class="btn-sig btn-sig--primary" style="width:100%; height:48px; font-size:16px;">
                        <i class="bi bi-check-circle"></i> PROCESAR MARCAJE
                    </button>
                    <input type="hidden" name="observaciones" value="Registro manual en recepción">
                </form>
            </div>
        </div>
    </div>

    <!-- TABLA DE MOVIMIENTOS -->
    <div class="col-md-7 order-md-1">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title">Movimientos Recientes</div>
            </div>
            <div class="sig-table-wrap">
                <table class="sig-table">
                    <thead>
                        <tr>
                            <th>Visitante</th>
                            <th>Visita a:</th>
                            <th style="text-align:center;">Entrada</th>
                            <th style="text-align:center;">Salida</th>
                            <th class="col-actions">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['visitas'])): ?>
                            <tr><td colspan="5" class="sig-table-empty">Sin movimientos registrados hoy.</td></tr>
                        <?php else: ?>
                            <?php foreach ($data['visitas'] as $v): ?>
                                <tr>
                                    <td>
                                        <div style="display:flex; flex-direction:column;">
                                            <span class="cell-strong"><?php echo $v->vis_nombre . ' ' . $v->vis_apellido; ?></span>
                                            <span class="cell-id">CI: <?php echo $v->vis_cedula; ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-size:12px; font-weight:600; color:var(--text-secondary);"><?php echo $v->emp_nombre . ' ' . $v->emp_apellido; ?></div>
                                        <div style="font-size:11px; color:var(--text-tertiary);"><?php echo $v->motivo; ?></div>
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="sig-badge sig-badge--success" style="font-weight:700; font-family:var(--font-mono);"><?php echo $v->hora_entrada; ?></span>
                                    </td>
                                    <td style="text-align:center;">
                                        <?php if ($v->hora_salida): ?>
                                            <span class="sig-badge sig-badge--danger" style="font-weight:700; font-family:var(--font-mono);"><?php echo $v->hora_salida; ?></span>
                                        <?php else: ?>
                                            <span class="sig-badge sig-badge--neutral" style="opacity:0.5;">--:--</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="col-actions">
                                        <a href="<?php echo URL_ROOT; ?>/visitas/delete/<?php echo $v->id; ?>" class="row-action row-action--del delete-btn">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
