<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/rutas/detalle/<?php echo $data['ruta']->id; ?>" style="color:inherit;text-decoration:none;">
                <?php echo htmlspecialchars($data['ruta']->nombre ?? ''); ?>
            </a> · Informe de Visita
        </div>
        <h1 class="page__title">Informe Post-Visita</h1>
        <p class="page__subtitle">Documentación oficial de la visita turística ejecutada.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/rutas/detalle/<?php echo $data['ruta']->id; ?>" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <?php if ($data['informe']): ?>
            <a href="<?php echo URL_ROOT; ?>/rutas/exportarInformeCsv/<?php echo $data['ruta']->id; ?>" class="btn-sig btn-sig--ghost">
                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar Excel
            </a>
        <?php endif; ?>
    </div>
</div>

<?php $inf = $data['informe']; ?>

<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-8);">
    <div class="sig-card__body" style="padding:var(--sp-8);">
        <form action="<?php echo URL_ROOT; ?>/rutas/informe/<?php echo $data['ruta']->id; ?>" method="POST">

            <!-- Lugar exacto -->
            <div style="display:flex;align-items:center;gap:var(--sp-3);margin-bottom:var(--sp-6);border-bottom:1px solid var(--border-subtle);padding-bottom:var(--sp-3);">
                <i class="bi bi-geo-alt" style="font-size:20px;color:var(--teal-500);"></i>
                <h3 style="font-size:18px;font-weight:700;margin:0;">Datos de la Visita</h3>
            </div>
            <div class="row g-4 mb-8">
                <div class="col-md-4">
                    <div class="sig-field">
                        <label class="sig-field__label">Ruta</label>
                        <input type="text" class="sig-input" value="<?php echo htmlspecialchars($data['ruta']->nombre ?? ''); ?>" readonly style="background:var(--bg-muted-subtle);cursor:not-allowed;">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="sig-field">
                        <label class="sig-field__label">Fecha de Visita</label>
                        <input type="text" class="sig-input" value="<?php echo $data['ruta']->fecha_visita ? date('d/m/Y', strtotime($data['ruta']->fecha_visita)) : 'N/A'; ?>" readonly style="background:var(--bg-muted-subtle);cursor:not-allowed;">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="sig-field">
                        <label class="sig-field__label">Lugar exacto y municipio</label>
                        <input type="text" name="lugar_exacto" class="sig-input" value="<?php echo htmlspecialchars($inf->lugar_exacto ?? ($data['ruta']->nombre ?? '')); ?>" placeholder="Ej: Castillo de San Antonio, Cumaná">
                    </div>
                </div>
            </div>

            <!-- Demografía -->
            <div style="display:flex;align-items:center;gap:var(--sp-3);margin-bottom:var(--sp-6);border-bottom:1px solid var(--border-subtle);padding-bottom:var(--sp-3);">
                <i class="bi bi-people" style="font-size:20px;color:var(--teal-500);"></i>
                <h3 style="font-size:18px;font-weight:700;margin:0;">Demografía de Participantes</h3>
            </div>

            <?php if (($data['totalSugeridos'] ?? 0) > 0): $s = $data['sugeridos']; ?>
            <div style="background:rgba(var(--brand-rgb,.22,.48,.86),.05);border-left:3px solid var(--brand-500);border-radius:6px;padding:var(--sp-3) var(--sp-4);margin-bottom:var(--sp-5);font-size:13px;color:var(--text-secondary);">
                <i class="bi bi-calculator" style="color:var(--brand-500);"></i>
                <strong style="color:var(--text-primary);">Sugerencia desde participantes registrados</strong>
                (<?php echo $data['totalSugeridos']; ?> inscritos con datos):
                <span style="margin-left:var(--sp-2);">
                    Mujeres <strong><?php echo $s['mujeres']; ?></strong> ·
                    Hombres <strong><?php echo $s['hombres']; ?></strong> ·
                    Niñas <strong><?php echo $s['ninas']; ?></strong> ·
                    Niños <strong><?php echo $s['ninos']; ?></strong>
                </span>
                <button type="button" onclick="aplicarSugeridos()" class="btn-sig btn-sig--ghost btn-sig--sm" style="margin-left:var(--sp-3);">
                    <i class="bi bi-arrow-down-circle"></i> Aplicar
                </button>
            </div>
            <?php endif; ?>

            <div class="row g-4 mb-8" style="align-items:flex-end;">
                <div class="col-md-2">
                    <div class="sig-field">
                        <label class="sig-field__label">Mujeres</label>
                        <input type="number" id="inf_mujeres" name="mujeres" class="sig-input" style="text-align:center;" min="0" value="<?php echo $inf->mujeres ?? 0; ?>" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="sig-field">
                        <label class="sig-field__label">Hombres</label>
                        <input type="number" id="inf_hombres" name="hombres" class="sig-input" style="text-align:center;" min="0" value="<?php echo $inf->hombres ?? 0; ?>" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="sig-field">
                        <label class="sig-field__label">Niñas <span style="font-size:10px;font-weight:400;color:var(--text-tertiary);">(5–11)</span></label>
                        <input type="number" id="inf_ninas" name="ninas" class="sig-input" style="text-align:center;" min="0" value="<?php echo $inf->ninas ?? 0; ?>" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="sig-field">
                        <label class="sig-field__label">Niños <span style="font-size:10px;font-weight:400;color:var(--text-tertiary);">(5–11)</span></label>
                        <input type="number" id="inf_ninos" name="ninos" class="sig-input" style="text-align:center;" min="0" value="<?php echo $inf->ninos ?? 0; ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div style="background:var(--bg-muted);padding:var(--sp-3) var(--sp-4);border-radius:var(--r-lg);border:1px solid var(--border-subtle);text-align:center;">
                        <span style="display:block;font-size:11px;font-weight:600;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">Total Atendidos</span>
                        <span style="font-size:28px;font-weight:800;color:var(--success-600);"><?php echo $inf->total_atendidos ?? 0; ?></span>
                    </div>
                </div>
            </div>

            <!-- Observaciones y Resumen -->
            <div style="display:flex;align-items:center;gap:var(--sp-3);margin-bottom:var(--sp-6);border-bottom:1px solid var(--border-subtle);padding-bottom:var(--sp-3);">
                <i class="bi bi-justify-left" style="font-size:20px;color:var(--teal-500);"></i>
                <h3 style="font-size:18px;font-weight:700;margin:0;">Resumen de la Visita</h3>
            </div>
            <div class="sig-field mb-4">
                <label class="sig-field__label">Observaciones adicionales</label>
                <input type="text" name="observaciones" class="sig-input" value="<?php echo htmlspecialchars($inf->observaciones ?? ''); ?>" placeholder="Incidentes, notas especiales...">
            </div>
            <div class="sig-field mb-8">
                <label class="sig-field__label">Resumen ejecutivo <span class="req">*</span></label>
                <textarea name="resumen_visita" class="sig-textarea" rows="6" required placeholder="Describa los objetivos alcanzados, lugares visitados, experiencia de los participantes..."><?php echo htmlspecialchars($inf->resumen_visita ?? ''); ?></textarea>
            </div>

            <div style="display:flex;justify-content:flex-end;padding-top:var(--sp-4);border-top:1px solid var(--border-subtle);">
                <button type="submit" class="btn-sig btn-sig--primary" style="background:var(--teal-600);padding:0 var(--sp-10);height:48px;font-size:16px;">
                    <i class="bi bi-save"></i> Guardar Informe
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (($data['totalSugeridos'] ?? 0) > 0): $s = $data['sugeridos']; ?>
<script>
function aplicarSugeridos() {
    document.getElementById('inf_mujeres').value = <?php echo (int)$s['mujeres']; ?>;
    document.getElementById('inf_hombres').value = <?php echo (int)$s['hombres']; ?>;
    document.getElementById('inf_ninas').value   = <?php echo (int)$s['ninas']; ?>;
    document.getElementById('inf_ninos').value   = <?php echo (int)$s['ninos']; ?>;
}
</script>
<?php endif; ?>

<?php require_once '../app/views/inc/footer.php'; ?>
