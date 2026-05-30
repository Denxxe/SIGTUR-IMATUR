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

    <!-- ══ Metas Anuales ══ -->
    <div class="col-12">
        <div class="sig-card">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-trophy-fill" style="color:var(--warning-500);"></i> Metas Anuales</div>
                <span style="font-size:12px; color:var(--text-tertiary);">Usadas en los indicadores de gestión para calcular el % de cumplimiento</span>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-5);">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="sig-field">
                            <label class="sig-field__label">
                                <i class="bi bi-mortarboard" style="color:#7C3AED;"></i>
                                Meta anual de actividades formativas
                            </label>
                            <input type="number" name="meta_talleres_anio" class="sig-input" min="0"
                                   value="<?php echo (int)($cfg['meta_talleres_anio']['valor'] ?? 0); ?>">
                            <span class="sig-field__hint">Número de talleres, charlas e inducciones planificados para el año. 0 = sin meta definida.</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="sig-field">
                            <label class="sig-field__label">
                                <i class="bi bi-geo-alt-fill" style="color:#D97706;"></i>
                                Meta anual de rutas turísticas
                            </label>
                            <input type="number" name="meta_rutas_anio" class="sig-input" min="0"
                                   value="<?php echo (int)($cfg['meta_rutas_anio']['valor'] ?? 0); ?>">
                            <span class="sig-field__hint">Número de rutas planificadas para operar en el año. 0 = sin meta definida.</span>
                        </div>
                    </div>
                </div>
                <?php
                $metaTalleres = (int)($cfg['meta_talleres_anio']['valor'] ?? 0);
                $metaRutas    = (int)($cfg['meta_rutas_anio']['valor']    ?? 0);
                if ($metaTalleres > 0 || $metaRutas > 0):
                ?>
                <div style="margin-top:var(--sp-4); padding:var(--sp-3) var(--sp-4); background:rgba(var(--brand-rgb,.22,.48,.86),.05); border-radius:8px; font-size:12px; color:var(--text-secondary);">
                    <i class="bi bi-info-circle" style="color:var(--brand-500);"></i>
                    Los indicadores de gestión mostrarán el progreso del año actual respecto a estas metas.
                    Actualiza los valores cada año antes de comenzar la temporada.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ══ Alertas del Sistema ══ -->
    <div class="col-lg-6">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-bell-fill" style="color:var(--danger-500);"></i> Umbrales de Alertas</div>
                <span style="font-size:12px; color:var(--text-tertiary);">Días de anticipación para las alertas del Panel Principal</span>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-5);">
                <div class="sig-field">
                    <label class="sig-field__label"><i class="bi bi-person-badge" style="color:#3B82F6;"></i> Contratos vencientes (días)</label>
                    <input type="number" name="dias_preaviso_contrato" class="sig-input" min="1" max="365"
                           value="<?php echo (int)($cfg['dias_preaviso_contrato']['valor'] ?? 30); ?>">
                    <span class="sig-field__hint">El dashboard alertará con X días de anticipación sobre contratos que vencen.</span>
                </div>
                <div class="sig-field">
                    <label class="sig-field__label"><i class="bi bi-journal-text" style="color:#0EA5E9;"></i> Pasantes próximos a culminar (días)</label>
                    <input type="number" name="dias_preaviso_pasante" class="sig-input" min="1" max="365"
                           value="<?php echo (int)($cfg['dias_preaviso_pasante']['valor'] ?? 15); ?>">
                    <span class="sig-field__hint">El dashboard alertará con X días de anticipación sobre pasantes que culminan.</span>
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
