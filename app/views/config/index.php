<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Sistema · Configuración</div>
        <h1 class="page__title">Configuración Institucional</h1>
        <p class="page__subtitle">Datos del firmante, resolución, gaceta y contacto institucional usados en oficios y documentos.</p>
    </div>
</div>

<?php $cfg = $data['config'] ?? []; ?>

<form action="<?php echo URL_ROOT; ?>/config/store" method="POST">
<div class="row g-6 anim-slide-up">

    <!-- Datos del Firmante / Director -->
    <div class="col-lg-6">
        <div class="sig-card">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-person-badge" style="color:var(--brand-500);"></i> Firmante / Director</div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-5);">
                <div class="sig-field">
                    <label class="sig-field__label">Nombre</label>
                    <input type="text" name="director_nombre" class="sig-input"
                           value="<?php echo htmlspecialchars($cfg['director_nombre']['valor'] ?? ''); ?>"
                           placeholder="Ej: Carlos">
                    <span class="sig-field__hint"><?php echo htmlspecialchars($cfg['director_nombre']['descripcion'] ?? ''); ?></span>
                </div>
                <div class="sig-field">
                    <label class="sig-field__label">Apellido</label>
                    <input type="text" name="director_apellido" class="sig-input"
                           value="<?php echo htmlspecialchars($cfg['director_apellido']['valor'] ?? ''); ?>"
                           placeholder="Ej: González">
                </div>
                <div class="sig-field">
                    <label class="sig-field__label">Cargo</label>
                    <input type="text" name="director_cargo" class="sig-input"
                           value="<?php echo htmlspecialchars($cfg['director_cargo']['valor'] ?? ''); ?>"
                           placeholder="Ej: Director General">
                </div>
            </div>
        </div>
    </div>

    <!-- Resolución y Gaceta -->
    <div class="col-lg-6">
        <div class="sig-card">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-file-text" style="color:var(--teal-500);"></i> Resolución y Gaceta Municipal</div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-5);">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="sig-field">
                            <label class="sig-field__label">N° Resolución</label>
                            <input type="text" name="resolucion_numero" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['resolucion_numero']['valor'] ?? ''); ?>"
                                   placeholder="Ej: 025">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="sig-field">
                            <label class="sig-field__label">Fecha Resolución</label>
                            <input type="text" name="resolucion_fecha" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['resolucion_fecha']['valor'] ?? ''); ?>"
                                   placeholder="Ej: 15 de enero de 2024">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="sig-field">
                            <label class="sig-field__label">N° Gaceta Municipal</label>
                            <input type="text" name="gaceta_numero" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['gaceta_numero']['valor'] ?? ''); ?>"
                                   placeholder="Ej: 042">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="sig-field">
                            <label class="sig-field__label">Fecha Gaceta</label>
                            <input type="text" name="gaceta_fecha" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['gaceta_fecha']['valor'] ?? ''); ?>"
                                   placeholder="Ej: 20 de enero de 2024">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contacto institucional -->
    <div class="col-lg-6">
        <div class="sig-card">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-telephone" style="color:var(--success-500);"></i> Contacto Institucional</div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-5);">
                <div class="sig-field">
                    <label class="sig-field__label">Teléfono</label>
                    <input type="text" name="telf_institucion" class="sig-input"
                           value="<?php echo htmlspecialchars($cfg['telf_institucion']['valor'] ?? ''); ?>"
                           placeholder="(0293) 431-4073">
                </div>
                <div class="sig-field">
                    <label class="sig-field__label">Correo electrónico</label>
                    <input type="email" name="correo_institucion" class="sig-input"
                           value="<?php echo htmlspecialchars($cfg['correo_institucion']['valor'] ?? ''); ?>"
                           placeholder="imatur.cumana@gmail.com">
                </div>
            </div>
        </div>
    </div>

    <!-- Correlativo de oficios -->
    <div class="col-lg-6">
        <div class="sig-card">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-hash" style="color:var(--warning-500);"></i> Correlativo de Oficios</div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-5);">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="sig-field">
                            <label class="sig-field__label">Último N° emitido</label>
                            <input type="number" name="correlativo_oficio" class="sig-input" min="0"
                                   value="<?php echo (int)($cfg['correlativo_oficio']['valor'] ?? 0); ?>">
                            <span class="sig-field__hint">El próximo oficio usará este número + 1.</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="sig-field">
                            <label class="sig-field__label">Año del correlativo</label>
                            <input type="number" name="ano_correlativo" class="sig-input" min="2020"
                                   value="<?php echo (int)($cfg['ano_correlativo']['valor'] ?? date('Y')); ?>">
                            <span class="sig-field__hint">Al cambiar de año se reinicia automáticamente.</span>
                        </div>
                    </div>
                </div>
                <div style="margin-top:var(--sp-3); padding:var(--sp-3) var(--sp-4); background:var(--bg-muted-subtle); border-radius:6px; font-size:13px; color:var(--text-secondary);">
                    <i class="bi bi-info-circle" style="color:var(--brand-500);"></i>
                    El próximo oficio generado llevará el número
                    <strong>
                        <?php
                        $corr = (int)($cfg['correlativo_oficio']['valor'] ?? 0) + 1;
                        echo str_pad($corr, 3, '0', STR_PAD_LEFT) . '/' . date('Y');
                        ?>
                    </strong>
                </div>
            </div>
        </div>
    </div>

</div>

<div style="margin-top:var(--sp-6); display:flex; justify-content:flex-end; gap:var(--sp-3);">
    <a href="<?php echo URL_ROOT; ?>/dashboard/index" class="btn-sig btn-sig--ghost">Cancelar</a>
    <button type="submit" class="btn-sig btn-sig--primary">
        <i class="bi bi-check-lg"></i> Guardar Configuración
    </button>
</div>

</form>

<?php require_once '../app/views/inc/footer.php'; ?>
