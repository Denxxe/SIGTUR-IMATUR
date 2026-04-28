<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/talleres/detalle/<?php echo $data['taller']->id; ?>" style="color:inherit; text-decoration:none;">Detalle de Taller</a> · Informe Oficial
        </div>
        <h1 class="page__title">Generar Informe de Actividad</h1>
        <p class="page__subtitle">Documentación oficial de la jornada: <strong><?php echo $data['taller']->nombre ?? 'Taller'; ?></strong></p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/talleres/detalle/<?php echo $data['taller']->id; ?>" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <?php if($data['informe']): ?>
            <button onclick="window.print()" class="btn-sig btn-sig--primary" style="background:linear-gradient(180deg, var(--danger-500), var(--danger-700)); box-shadow: var(--sh-glow-danger);">
                <i class="bi bi-printer"></i> Imprimir Informe
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="sig-card anim-slide-up d-print-none" style="margin-bottom:var(--sp-8);">
    <div class="sig-card__body" style="padding:var(--sp-8);">
        <form action="<?php echo URL_ROOT; ?>/talleres/informe/<?php echo $data['taller']->id; ?>" method="POST">
            
            <div style="display:flex; align-items:center; gap:var(--sp-3); margin-bottom:var(--sp-6); border-bottom:1px solid var(--border-subtle); padding-bottom:var(--sp-3);">
                <i class="bi bi-geo-alt" style="font-size:20px; color:var(--brand-500);"></i>
                <h3 style="font-size:18px; font-weight:700; color:var(--text-primary); margin:0;">Datos del Evento</h3>
            </div>

            <div class="row g-4 mb-8">
                <div class="col-md-4">
                    <div class="sig-field">
                        <label class="sig-field__label">Unidad Estadal</label>
                        <input type="text" name="unidad_estadal" class="sig-input" value="<?php echo $data['informe']->unidad_estadal ?? 'Sucre'; ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="sig-field">
                        <label class="sig-field__label">Fecha y Hora</label>
                        <input type="text" class="sig-input" value="<?php echo date('d/m/Y', strtotime($data['taller']->fecha_inicio ?? 'now')) . ' | ' . ($data['taller']->hora_inicio ?? 'N/A'); ?>" readonly style="background:var(--bg-muted-subtle); cursor:not-allowed;">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="sig-field">
                        <label class="sig-field__label">Lugar exacto y municipio</label>
                        <input type="text" name="lugar_exacto" class="sig-input" value="<?php echo $data['informe']->lugar_exacto ?? ($data['taller']->ubicacion ?? ''); ?>" placeholder="Ej: Plaza Bolívar, Cumaná">
                    </div>
                </div>
                <div class="col-12">
                    <div class="sig-field">
                        <label class="sig-field__label">Instituciones o empresas presentes</label>
                        <input type="text" name="instituciones_presentes" class="sig-input" value="<?php echo $data['informe']->instituciones_presentes ?? ''; ?>" placeholder="Ej: Alcaldía, Policía Municipal, Voceros Comunales...">
                    </div>
                </div>
            </div>

            <div style="display:flex; align-items:center; gap:var(--sp-3); margin-bottom:var(--sp-6); border-bottom:1px solid var(--border-subtle); padding-bottom:var(--sp-3);">
                <i class="bi bi-people" style="font-size:20px; color:var(--brand-500);"></i>
                <h3 style="font-size:18px; font-weight:700; color:var(--text-primary); margin:0;">Demografía de Asistentes</h3>
            </div>

            <div class="row g-4 mb-8" style="align-items:flex-end;">
                <div class="col-md-2">
                    <div class="sig-field">
                        <label class="sig-field__label">Mujeres</label>
                        <input type="number" name="mujeres" class="sig-input" style="text-align:center;" min="0" value="<?php echo $data['informe']->mujeres ?? 0; ?>" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="sig-field">
                        <label class="sig-field__label">Hombres</label>
                        <input type="number" name="hombres" class="sig-input" style="text-align:center;" min="0" value="<?php echo $data['informe']->hombres ?? 0; ?>" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="sig-field">
                        <label class="sig-field__label">Niñas</label>
                        <input type="number" name="ninas" class="sig-input" style="text-align:center;" min="0" value="<?php echo $data['informe']->ninas ?? 0; ?>" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="sig-field">
                        <label class="sig-field__label">Niños</label>
                        <input type="number" name="ninos" class="sig-input" style="text-align:center;" min="0" value="<?php echo $data['informe']->ninos ?? 0; ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div style="background:var(--bg-muted); padding:var(--sp-3) var(--sp-4); border-radius:var(--r-lg); border:1px solid var(--border-subtle); text-align:center;">
                        <span style="display:block; font-size:11px; font-weight:600; color:var(--text-tertiary); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:2px;">Total Atendidos</span>
                        <span style="font-size:28px; font-weight:800; color:var(--success-600);"><?php echo $data['informe']->total_atendidas ?? 0; ?></span>
                    </div>
                </div>
            </div>

            <div style="display:flex; align-items:center; gap:var(--sp-3); margin-bottom:var(--sp-6); border-bottom:1px solid var(--border-subtle); padding-bottom:var(--sp-3);">
                <i class="bi bi-justify-left" style="font-size:20px; color:var(--brand-500);"></i>
                <h3 style="font-size:18px; font-weight:700; color:var(--text-primary); margin:0;">Resumen de la Actividad</h3>
            </div>

            <div class="sig-field mb-8">
                <textarea name="resumen_actividad" class="sig-textarea" rows="6" required placeholder="Redacte los pormenores, logros alcanzados y conclusiones de la actividad..."><?php echo $data['informe']->resumen_actividad ?? ''; ?></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; padding-top:var(--sp-4); border-top:1px solid var(--border-subtle);">
                <button type="submit" class="btn-sig btn-sig--primary" style="padding:0 var(--sp-10); height:48px; font-size:16px;">
                    <i class="bi bi-save"></i> Guardar Informe Oficial
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Estilos para Impresión -->
<style>
@media print {
    @page { margin: 1.5cm; }
    body { background: white !important; }
    .app-shell__sidebar, .app-shell__header, .page__head, .d-print-none, .btn-sig--ghost { display: none !important; }
    .app-shell__main { padding: 0 !important; margin: 0 !important; }
    .print-container { display: block !important; color: black; font-family: "Times New Roman", Times, serif; }
}
.print-container { display: none; background: white; padding: 20px; max-width: 800px; margin: 0 auto; border: 1px solid #eee; }
</style>

<!-- Plantilla de Impresión Oficial -->
<div class="print-container">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 30px;">
        <div style="display:flex; align-items:center; gap:15px;">
            <img src="<?php echo URL_ROOT; ?>/assets/images/Logo_imatur-removebg-preview.png" alt="Logo" style="width:60px;">
            <div>
                <h2 style="margin:0; font-size:22px;">IMATUR</h2>
                <p style="margin:0; font-size:12px; font-weight:600; text-transform:uppercase;">Instituto Municipal de Turismo</p>
            </div>
        </div>
        <div style="text-align: right;">
            <h3 style="margin:0; font-size:18px; color:#555;">REPORTE DE ACTIVIDAD</h3>
            <p style="margin:5px 0 0; font-size:11px; color:#888;">Emitido el: <?php echo date('d/m/Y'); ?></p>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
        <div>
            <p style="margin:8px 0;"><strong style="color:#555;">📍 Unidad Estadal:</strong> <?php echo $data['informe']->unidad_estadal ?? 'Sucre'; ?></p>
            <p style="margin:8px 0;"><strong style="color:#555;">📌 Actividad:</strong> <?php echo $data['taller']->nombre ?? 'N/A'; ?></p>
            <p style="margin:8px 0;"><strong style="color:#555;">📆 Fecha:</strong> <?php echo date('d/m/Y', strtotime($data['taller']->fecha_inicio ?? 'now')); ?></p>
            <p style="margin:8px 0;"><strong style="color:#555;">⏰ Hora:</strong> <?php echo $data['taller']->hora_inicio ?? 'N/A'; ?></p>
        </div>
        <div>
            <p style="margin:8px 0;"><strong style="color:#555;">🌐 Lugar / Municipio:</strong> <?php echo $data['informe']->lugar_exacto ?? ($data['taller']->ubicacion ?? 'N/A'); ?></p>
            <p style="margin:8px 0;"><strong style="color:#555;">🏢 Entidades presentes:</strong> <?php echo $data['informe']->instituciones_presentes ?? '-'; ?></p>
        </div>
    </div>

    <div style="margin: 30px 0; display:flex; gap: 40px; border-top: 1px solid #eee; border-bottom: 1px solid #eee; padding: 20px 0;">
        <div style="flex:1;">
            <h4 style="margin:0 0 15px; font-size:14px; text-transform:uppercase; color:#777; border-bottom: 2px solid #ddd; padding-bottom:5px;">Demografía de Asistencia</h4>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                <p style="margin:3px 0;"><strong>Mujeres:</strong> <?php echo $data['informe']->mujeres ?? 0; ?></p>
                <p style="margin:3px 0;"><strong>Hombres:</strong> <?php echo $data['informe']->hombres ?? 0; ?></p>
                <p style="margin:3px 0;"><strong>Niñas:</strong> <?php echo $data['informe']->ninas ?? 0; ?></p>
                <p style="margin:3px 0;"><strong>Niños:</strong> <?php echo $data['informe']->ninos ?? 0; ?></p>
            </div>
            <div style="margin-top:15px; padding-top:10px; border-top:1px dashed #ccc;">
                <p style="margin:0; font-size:16px;"><strong>Total Atendidos:</strong> <span style="font-size:20px;"><?php echo $data['informe']->total_atendidas ?? 0; ?></span></p>
            </div>
        </div>
        <div style="flex:1.5;">
            <h4 style="margin:0 0 15px; font-size:14px; text-transform:uppercase; color:#777; border-bottom: 2px solid #ddd; padding-bottom:5px;">Resumen Ejecutivo</h4>
            <p style="text-align: justify; line-height: 1.5; font-size: 13px; margin:0;">
                <?php echo nl2br(htmlspecialchars($data['informe']->resumen_actividad ?? '')); ?>
            </p>
        </div>
    </div>

    <div style="margin-top: 80px; display:flex; justify-content:space-around;">
        <div style="text-align: center; width: 250px;">
            <div style="border-top: 1px solid #000; padding-top: 10px;">
                <p style="margin:0; font-weight:bold;"><?php echo ($data['taller']->facilitador_nombre ?? 'N/A') . ' ' . ($data['taller']->facilitador_apellido ?? ''); ?></p>
                <p style="margin:0; font-size:12px; color:#666;">Firma del Facilitador / Responsable</p>
            </div>
        </div>
        <div style="text-align: center; width: 250px;">
            <div style="border-top: 1px solid #000; padding-top: 10px;">
                <p style="margin:0; font-weight:bold;">Sello de la Institución</p>
                <p style="margin:0; font-size:12px; color:#666;">Coordinación de Formación IMATUR</p>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
