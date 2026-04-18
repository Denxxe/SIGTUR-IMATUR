<?php require_once '../app/views/inc/header.php'; ?>

<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h1><i class="bi bi-file-earmark-text"></i> <?php echo $data['titulo']; ?></h1>
        <p class="text-muted">REPORTE DE ACTIVIDAD IMATUR-SUCRE — Taller: <strong><?php echo $data['taller']->nombre; ?></strong></p>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?php echo URL_ROOT; ?>/talleres/detalle/<?php echo $data['taller']->id; ?>" class="btn btn-outline-secondary mb-2">← Volver al Taller</a>
        <?php if($data['informe']): ?>
            <button onclick="window.print()" class="btn btn-danger mb-2"><i class="bi bi-printer"></i> Imprimir Oficial</button>
        <?php endif; ?>
    </div>
</div>

<div class="row d-print-none">
    <div class="col-12">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form action="<?php echo URL_ROOT; ?>/talleres/informe/<?php echo $data['taller']->id; ?>" method="POST">
                    
                    <h5 class="text-primary border-bottom pb-2 mb-4"><i class="bi bi-geo-alt"></i> Datos Básicos del Evento</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Unidad Estadal</label>
                            <input type="text" name="unidad_estadal" class="form-control" value="<?php echo $data['informe']->unidad_estadal ?? 'Sucre'; ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Fecha y Hora</label>
                            <input type="text" class="form-control bg-light" value="<?php echo $data['taller']->fecha_inicio . ' | ' . ($data['taller']->hora_inicio ?? 'N/A'); ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Lugar exacto y municipio</label>
                            <input type="text" name="lugar_exacto" class="form-control" value="<?php echo $data['informe']->lugar_exacto ?? $data['taller']->ubicacion; ?>" placeholder="Ej: Plaza Bolívar, Municipio Sucre">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Instituciones o empresas presentes</label>
                            <input type="text" name="instituciones_presentes" class="form-control" value="<?php echo $data['informe']->instituciones_presentes ?? ''; ?>" placeholder="Ej: Alcaldía, Policía, Voceros Comunales...">
                        </div>
                    </div>

                    <h5 class="text-primary border-bottom pb-2 mb-4 mt-5"><i class="bi bi-people"></i> Demografía de Asistentes</h5>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label">Mujeres</label>
                            <input type="number" name="mujeres" class="form-control text-center" min="0" value="<?php echo $data['informe']->mujeres ?? 0; ?>" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Hombres</label>
                            <input type="number" name="hombres" class="form-control text-center" min="0" value="<?php echo $data['informe']->hombres ?? 0; ?>" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Niñas</label>
                            <input type="number" name="ninas" class="form-control text-center" min="0" value="<?php echo $data['informe']->ninas ?? 0; ?>" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Niños</label>
                            <input type="number" name="ninos" class="form-control text-center" min="0" value="<?php echo $data['informe']->ninos ?? 0; ?>" required>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="bg-light p-2 rounded border">
                                <small class="d-block text-muted">Total Personas Atendidas</small>
                                <span class="fs-4 fw-bold text-success"><?php echo $data['informe']->total_atendidas ?? 0; ?></span>
                            </div>
                        </div>
                    </div>

                    <h5 class="text-primary border-bottom pb-2 mb-4 mt-5"><i class="bi bi-justify-left"></i> Resumen</h5>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Resumen de la actividad</label>
                        <textarea name="resumen_actividad" class="form-control" rows="5" required placeholder="Redacte los pormenores, logros observados y conclusiones de la actividad formacional..."><?php echo $data['informe']->resumen_actividad ?? ''; ?></textarea>
                    </div>

                    <div class="text-end pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-5"><i class="bi bi-save"></i> Guardar Informe Oficial</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Estilos para Impresión -->
<style>
@media print {
    body * { visibility: hidden; }
    .print-container, .print-container * { visibility: visible; }
    .print-container { position: absolute; left: 0; top: 0; width: 100%; padding: 2cm; background: #fff;}
    .d-print-none { display: none !important; }
}
.print-container { display: none; }
@media print { .print-container { display: block; } }
</style>

<!-- Plantilla de Impresión Oficial -->
<div class="print-container">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px;">
        <div>
            <h3 style="margin:0;">IMATUR</h3>
            <p style="margin:0;">Instituto Municipal de Turismo</p>
        </div>
        <div>
            <h4 style="margin:0; text-align: right;">REPORTE DE ACTIVIDAD</h4>
            <p style="margin:0; text-align: right; font-size: 12px;">Fecha Impresión: <?php echo date('d/m/Y'); ?></p>
        </div>
    </div>

    <p><strong>📍 Nombre de la Unidad Estadal:</strong> <?php echo $data['informe']->unidad_estadal ?? 'Sucre'; ?></p>
    <p><strong>📌 Nombre de la Actividad:</strong> <?php echo $data['taller']->nombre; ?></p>
    
    <p><strong>📆 Fecha:</strong> <?php echo $data['taller']->fecha_inicio; ?></p>
    <p><strong>⏰ Hora:</strong> <?php echo $data['taller']->hora_inicio ?? 'N/A'; ?></p>
    <p><strong>🌐 Lugar exacto y municipio:</strong> <?php echo $data['informe']->lugar_exacto ?? $data['taller']->ubicacion; ?></p>
    <br>
    <p><strong>📚 Instituciones o empresas presentes:</strong> <?php echo $data['informe']->instituciones_presentes ?? '-'; ?></p>

    <div style="margin: 20px 0; padding: 15px; border: 1px dashed #ccc; width: 300px;">
        <p style="margin: 3px 0;"><strong>Mujeres:</strong> <?php echo $data['informe']->mujeres ?? 0; ?></p>
        <p style="margin: 3px 0;"><strong>Hombres:</strong> <?php echo $data['informe']->hombres ?? 0; ?></p>
        <p style="margin: 3px 0;"><strong>Niñas:</strong> <?php echo $data['informe']->ninas ?? 0; ?></p>
        <p style="margin: 3px 0;"><strong>Niños:</strong> <?php echo $data['informe']->ninos ?? 0; ?></p>
        <hr>
        <p style="margin: 3px 0;"><strong>Total personas Atendidas:</strong> <?php echo $data['informe']->total_atendidas ?? 0; ?></p>
    </div>

    <p><strong>🧾 Resumen de la actividad:</strong></p>
    <p style="text-align: justify; line-height: 1.6;">
        <?php echo nl2br(htmlspecialchars($data['informe']->resumen_actividad ?? '')); ?>
    </p>

    <div style="margin-top: 60px; text-align: center;">
        <p>_____________________________________</p>
        <p><strong>Firma Facilitador</strong><br><?php echo $data['taller']->facilitador_nombre . ' ' . $data['taller']->facilitador_apellido; ?></p>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
