<?php require_once '../app/views/inc/header.php'; ?>

<?php $cfg = $data['config'] ?? []; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Sistema · Configuración</div>
        <h1 class="page__title">Configuración Institucional</h1>
        <p class="page__subtitle">Parámetros del sistema, datos del firmante y metas operativas.</p>
    </div>
</div>

<form action="<?php echo URL_ROOT; ?>/config/store" method="POST">

<!-- ════════════════════════════════════════════════════════════
     SECCIÓN 1 — DATOS INSTITUCIONALES
═══════════════════════════════════════════════════════════════ -->
<div style="display:flex;" class="anim-slide-up u-ai-center u-gap-sp-3 u-m-0-0-sp-4">
    <div class="u-w-4px u-h-20px u-br-2px u-fsh-0" style="background:#3B82F6;"></div>
    <span class="u-fs-0_72rem u-fw-700 u-tt-uppercase u-ls-0_08em u-c-text-secondary">Datos Institucionales</span>
    <div class="u-flex-1 u-h-1px u-bg-border-subtle"></div>
</div>

<div class="row g-4 mb-5 anim-slide-up">

    <!-- Firmante / Director -->
    <div class="col-md-4">
        <div class="sig-card h-100" style="border-top:3px solid #3B82F6;">
            <div class="sig-card__head u-bdb-1px-solid-border-subtle" style="background:rgba(59,130,246,.04);">
                <div class="sig-card__title">
                    <i class="bi bi-person-badge-fill u-c-x3B82F6"></i> Firmante / Director
                </div>
            </div>
            <div class="sig-card__body u-p-sp-4">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="sig-field u-m-0">
                            <label class="sig-field__label" for="director_nombre">Nombre</label>
                            <input id="director_nombre" type="text" name="director_nombre" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['director_nombre']['valor'] ?? ''); ?>"
                                   placeholder="Ej: María">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="sig-field u-m-0">
                            <label class="sig-field__label" for="director_apellido">Apellido</label>
                            <input id="director_apellido" type="text" name="director_apellido" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['director_apellido']['valor'] ?? ''); ?>"
                                   placeholder="Ej: González">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="sig-field u-m-0">
                            <label class="sig-field__label" for="director_cargo">Cargo</label>
                            <input id="director_cargo" type="text" name="director_cargo" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['director_cargo']['valor'] ?? ''); ?>"
                                   placeholder="Ej: Director General">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="sig-field u-m-0">
                            <label class="sig-field__label" for="rif_institucional">RIF institucional</label>
                            <input id="rif_institucional" type="text" name="rif_institucional" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['rif_institucional']['valor'] ?? ConfigSistema::RIF_DEFAULT); ?>"
                                   placeholder="G-20008498-7">
                            <small class="u-c-text-tertiary">Aparece en constancias, oficios, fichas y reportes.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resolución y Gaceta -->
    <div class="col-md-4">
        <div class="sig-card h-100" style="border-top:3px solid #0891B2;">
            <div class="sig-card__head u-bdb-1px-solid-border-subtle" style="background:rgba(8,145,178,.04);">
                <div class="sig-card__title">
                    <i class="bi bi-file-text-fill u-c-x0891B2"></i> Resolución y Gaceta
                </div>
                <span class="u-fs-11px u-c-text-tertiary">Referencia legal en documentos</span>
            </div>
            <div class="sig-card__body u-p-sp-4">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="sig-field u-m-0">
                            <label class="sig-field__label" for="resolucion_numero">N° Resolución</label>
                            <input id="resolucion_numero" type="text" name="resolucion_numero" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['resolucion_numero']['valor'] ?? ''); ?>"
                                   placeholder="025">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="sig-field u-m-0">
                            <label class="sig-field__label" for="resolucion_fecha">Fecha</label>
                            <input id="resolucion_fecha" type="text" name="resolucion_fecha" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['resolucion_fecha']['valor'] ?? ''); ?>"
                                   placeholder="15 ene 2024">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="sig-field u-m-0">
                            <label class="sig-field__label" for="gaceta_numero">N° Gaceta</label>
                            <input id="gaceta_numero" type="text" name="gaceta_numero" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['gaceta_numero']['valor'] ?? ''); ?>"
                                   placeholder="042">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="sig-field u-m-0">
                            <label class="sig-field__label" for="gaceta_fecha">Fecha</label>
                            <input id="gaceta_fecha" type="text" name="gaceta_fecha" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['gaceta_fecha']['valor'] ?? ''); ?>"
                                   placeholder="20 ene 2024">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="u-br-6px u-fs-11px u-c-text-secondary u-lh-1_5" style="padding:var(--sp-2) var(--sp-3); background:rgba(8,145,178,.06);">
                            <i class="bi bi-info-circle u-c-x0891B2"></i>
                            Aparecen en el cuerpo de oficios emitidos para acreditar la base legal de IMATUR.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contacto -->
    <div class="col-md-4">
        <div class="sig-card h-100" style="border-top:3px solid #059669;">
            <div class="sig-card__head u-bdb-1px-solid-border-subtle" style="background:rgba(5,150,105,.04);">
                <div class="sig-card__title">
                    <i class="bi bi-telephone-fill u-c-x059669"></i> Contacto Institucional
                </div>
            </div>
            <div class="sig-card__body u-p-sp-4">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="sig-field u-m-0">
                            <label class="sig-field__label" for="telf_institucion">Teléfono</label>
                            <input id="telf_institucion" type="text" name="telf_institucion" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['telf_institucion']['valor'] ?? ''); ?>"
                                   placeholder="(0293) 431-4073">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="sig-field u-m-0">
                            <label class="sig-field__label" for="correo_institucion">Correo electrónico</label>
                            <input id="correo_institucion" type="email" name="correo_institucion" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['correo_institucion']['valor'] ?? ''); ?>"
                                   placeholder="Sucreimatur@gmail.com">
                            <small class="u-c-text-tertiary">Aparece en el carnet, constancias y oficios. Es además el <strong>remitente</strong> de los correos de recuperación de contraseña.</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="sig-field u-m-0">
                            <label class="sig-field__label" for="direccion_institucion">Dirección</label>
                            <input id="direccion_institucion" type="text" name="direccion_institucion" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['direccion_institucion']['valor'] ?? ''); ?>"
                                   placeholder="Estado Sucre, municipio Sucre, Cumaná, Calle Sucre, Casa Nº11">
                            <small class="u-c-text-tertiary">Se imprime al pie del carnet institucional.</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="sig-field u-m-0">
                            <label class="sig-field__label" for="lema_institucion">Lema institucional</label>
                            <input id="lema_institucion" type="text" name="lema_institucion" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['lema_institucion']['valor'] ?? ''); ?>"
                                   placeholder="Historia y Porvenir">
                            <small class="u-c-text-tertiary">Frase que cierra el carnet institucional.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ════════════════════════════════════════════════════════════
     SECCIÓN 2 — METAS ANUALES
═══════════════════════════════════════════════════════════════ -->
<div style="display:flex;" class="anim-slide-up u-ai-center u-gap-sp-3 u-m-0-0-sp-4">
    <div class="u-w-4px u-h-20px u-br-2px u-fsh-0" style="background:#F59E0B;"></div>
    <span class="u-fs-0_72rem u-fw-700 u-tt-uppercase u-ls-0_08em u-c-text-secondary">Planificación Anual — <?php echo date('Y'); ?></span>
    <div class="u-flex-1 u-h-1px u-bg-border-subtle"></div>
</div>

<div class="row g-4 mb-5 anim-slide-up">

    <div class="col-md-6">
        <div class="sig-card" style="border-top:3px solid #7C3AED;">
            <div class="sig-card__body u-p-sp-5">
                <div class="u-ai-flex-start u-gap-sp-4" style="display:flex;">
                    <div class="u-ai-center u-jc-center u-fsh-0" style="width:44px; height:44px; border-radius:12px; background:rgba(124,58,237,.1); display:flex;">
                        <i class="bi bi-mortarboard-fill u-c-x7C3AED" style="font-size:1.2rem;"></i>
                    </div>
                    <div class="u-flex-1 u-minw-0">
                        <div class="u-fs-13px u-fw-700 u-c-text-primary u-mb-2px">Meta — Actividades Formativas</div>
                        <div class="u-fs-12px u-c-text-secondary u-mb-sp-4">Talleres, charlas e inducciones planificados para el año</div>
                        <div class="u-ai-center u-gap-sp-3" style="display:flex;">
                            <input type="number" name="meta_talleres_anio" class="sig-input"
                                   style="font-size:1.5rem;font-weight:800;text-align:center;color:#7C3AED;width:120px;padding:var(--sp-2) var(--sp-3);"
                                   min="0" value="<?php echo (int)($cfg['meta_talleres_anio']['valor'] ?? 0); ?>">
                            <span class="u-fs-13px u-c-text-tertiary">actividades para <?php echo date('Y'); ?></span>
                        </div>
                        <div class="u-mt-sp-2 u-fs-11px u-c-text-tertiary">0 = sin meta definida — el indicador se oculta</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="sig-card" style="border-top:3px solid #D97706;">
            <div class="sig-card__body u-p-sp-5">
                <div class="u-ai-flex-start u-gap-sp-4" style="display:flex;">
                    <div class="u-ai-center u-jc-center u-fsh-0" style="width:44px; height:44px; border-radius:12px; background:rgba(217,119,6,.1); display:flex;">
                        <i class="bi bi-geo-alt-fill u-c-xD97706" style="font-size:1.2rem;"></i>
                    </div>
                    <div class="u-flex-1 u-minw-0">
                        <div class="u-fs-13px u-fw-700 u-c-text-primary u-mb-2px">Meta — Rutas Turísticas</div>
                        <div class="u-fs-12px u-c-text-secondary u-mb-sp-4">Rutas planificadas para operar durante el año</div>
                        <div class="u-ai-center u-gap-sp-3" style="display:flex;">
                            <input type="number" name="meta_rutas_anio" class="sig-input"
                                   style="font-size:1.5rem;font-weight:800;text-align:center;color:#D97706;width:120px;padding:var(--sp-2) var(--sp-3);"
                                   min="0" value="<?php echo (int)($cfg['meta_rutas_anio']['valor'] ?? 0); ?>">
                            <span class="u-fs-13px u-c-text-tertiary">rutas para <?php echo date('Y'); ?></span>
                        </div>
                        <div class="u-mt-sp-2 u-fs-11px u-c-text-tertiary">0 = sin meta definida — el indicador se oculta</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ════════════════════════════════════════════════════════════
     SECCIÓN 3 — PARÁMETROS DEL SISTEMA
═══════════════════════════════════════════════════════════════ -->
<div style="display:flex;" class="anim-slide-up u-ai-center u-gap-sp-3 u-m-0-0-sp-4">
    <div class="u-w-4px u-h-20px u-br-2px u-fsh-0" style="background:#64748B;"></div>
    <span class="u-fs-0_72rem u-fw-700 u-tt-uppercase u-ls-0_08em u-c-text-secondary">Parámetros del Sistema</span>
    <div class="u-flex-1 u-h-1px u-bg-border-subtle"></div>
</div>

<div class="row g-4 mb-6 anim-slide-up">

    <!-- Umbrales de Alertas -->
    <div class="col-md-6">
        <div class="sig-card h-100" style="border-top:3px solid #EF4444;">
            <div class="sig-card__head u-bdb-1px-solid-border-subtle" style="background:rgba(239,68,68,.04);">
                <div class="sig-card__title">
                    <i class="bi bi-bell-fill u-c-xEF4444"></i> Umbrales de Alertas
                </div>
                <span class="u-fs-11px u-c-text-tertiary">Días de anticipación · Panel Principal</span>
            </div>
            <div class="sig-card__body u-p-sp-4">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="u-ai-center u-jc-space-between u-gap-sp-4 u-p-sp-3-sp-4 u-bg-bg-muted-subtle u-br-8px u-bd-1px-solid-border-subtle" style="display:flex;">
                            <div>
                                <div class="u-fs-12px u-fw-600 u-c-text-primary u-mb-2px">
                                    <i class="bi bi-person-badge u-c-x3B82F6"></i> Contratos vencientes
                                </div>
                                <div class="u-fs-11px u-c-text-tertiary">Alertar con X días de anticipación</div>
                            </div>
                            <div class="u-ai-center u-gap-sp-2" style="display:flex;">
                                <input type="number" name="dias_preaviso_contrato" class="sig-input"
                                       style="width:72px;text-align:center;font-weight:700;font-size:15px;padding:var(--sp-1) var(--sp-2);"
                                       min="1" max="365"
                                       value="<?php echo (int)($cfg['dias_preaviso_contrato']['valor'] ?? 30); ?>">
                                <span class="u-fs-12px u-c-text-secondary">días</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="u-ai-center u-jc-space-between u-gap-sp-4 u-p-sp-3-sp-4 u-bg-bg-muted-subtle u-br-8px u-bd-1px-solid-border-subtle" style="display:flex;">
                            <div>
                                <div class="u-fs-12px u-fw-600 u-c-text-primary u-mb-2px">
                                    <i class="bi bi-journal-text" style="color:#0EA5E9;"></i> Pasantes culminando
                                </div>
                                <div class="u-fs-11px u-c-text-tertiary">Alertar con X días de anticipación</div>
                            </div>
                            <div class="u-ai-center u-gap-sp-2" style="display:flex;">
                                <input type="number" name="dias_preaviso_pasante" class="sig-input"
                                       style="width:72px;text-align:center;font-weight:700;font-size:15px;padding:var(--sp-1) var(--sp-2);"
                                       min="1" max="365"
                                       value="<?php echo (int)($cfg['dias_preaviso_pasante']['valor'] ?? 15); ?>">
                                <span class="u-fs-12px u-c-text-secondary">días</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="u-ai-center u-jc-space-between u-gap-sp-4 u-p-sp-3-sp-4 u-bg-bg-muted-subtle u-br-8px u-bd-1px-solid-border-subtle" style="display:flex;">
                            <div>
                                <div class="u-fs-12px u-fw-600 u-c-text-primary u-mb-2px">
                                    <i class="bi bi-alarm u-c-xEF4444"></i> Tolerancia de puntualidad
                                </div>
                                <div class="u-fs-11px u-c-text-tertiary">Minutos tras la hora de entrada antes de marcar impuntualidad</div>
                            </div>
                            <div class="u-ai-center u-gap-sp-2" style="display:flex;">
                                <input type="number" name="minutos_tolerancia_puntualidad" class="sig-input"
                                       style="width:72px;text-align:center;font-weight:700;font-size:15px;padding:var(--sp-1) var(--sp-2);"
                                       min="0" max="120"
                                       value="<?php echo (int)($cfg['minutos_tolerancia_puntualidad']['valor'] ?? 15); ?>">
                                <span class="u-fs-12px u-c-text-secondary">min</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="u-ai-center u-jc-space-between u-gap-sp-4 u-p-sp-3-sp-4 u-bg-bg-muted-subtle u-br-8px u-bd-1px-solid-border-subtle" style="display:flex;">
                            <div>
                                <div class="u-fs-12px u-fw-600 u-c-text-primary u-mb-2px">
                                    <i class="bi bi-box-arrow-right u-c-xD97706"></i> Tolerancia de salida anticipada
                                </div>
                                <div class="u-fs-11px u-c-text-tertiary">Minutos antes de la hora de salida del horario antes de exigir motivo</div>
                            </div>
                            <div class="u-ai-center u-gap-sp-2" style="display:flex;">
                                <input type="number" name="minutos_tolerancia_salida_temprana" class="sig-input"
                                       style="width:72px;text-align:center;font-weight:700;font-size:15px;padding:var(--sp-1) var(--sp-2);"
                                       min="0" max="120"
                                       value="<?php echo (int)($cfg['minutos_tolerancia_salida_temprana']['valor'] ?? 10); ?>">
                                <span class="u-fs-12px u-c-text-secondary">min</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Correlativo de Oficios -->
    <div class="col-md-6">
        <div class="sig-card h-100" style="border-top:3px solid #6366F1;">
            <div class="sig-card__head u-bdb-1px-solid-border-subtle" style="background:rgba(99,102,241,.04);">
                <div class="sig-card__title">
                    <i class="bi bi-hash u-c-x6366F1"></i> Correlativo de Oficios
                </div>
                <span class="u-fs-11px u-c-text-tertiary">Se reinicia automáticamente cada año</span>
            </div>
            <div class="sig-card__body u-p-sp-4">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="sig-field u-m-0">
                            <label class="sig-field__label" for="correlativo_oficio">Último N° emitido</label>
                            <input id="correlativo_oficio" type="number" name="correlativo_oficio" class="sig-input" min="0"
                                   style="text-align:center; font-size:1.1rem; font-weight:700;"
                                   value="<?php echo (int)($cfg['correlativo_oficio']['valor'] ?? 0); ?>">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="sig-field u-m-0">
                            <label class="sig-field__label" for="ano_correlativo">Año del correlativo</label>
                            <input id="ano_correlativo" type="number" name="ano_correlativo" class="sig-input" min="2020"
                                   style="text-align:center; font-size:1.1rem; font-weight:700;"
                                   value="<?php echo (int)($cfg['ano_correlativo']['valor'] ?? date('Y')); ?>">
                        </div>
                    </div>
                    <div class="col-12">
                        <?php
                        $corrSig = str_pad((int)($cfg['correlativo_oficio']['valor'] ?? 0) + 1, 3, '0', STR_PAD_LEFT);
                        $anioSig = (int)($cfg['ano_correlativo']['valor'] ?? date('Y'));
                        ?>
                        <div class="u-ai-center u-jc-space-between u-p-sp-3-sp-4 u-br-8px" style="display:flex; background:rgba(99,102,241,.06); border:1px solid rgba(99,102,241,.2);">
                            <span class="u-fs-12px u-c-text-secondary">Próximo oficio:</span>
                            <span class="u-fs-1rem u-fw-800 u-c-x6366F1 u-ff-font-mono">
                                <?php echo $corrSig . '/' . $anioSig; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rutas Turísticas (mig. 079 — T-I) -->
    <div class="col-md-6">
        <div class="sig-card h-100" style="border-top:3px solid #0D9488;">
            <div class="sig-card__head u-bdb-1px-solid-border-subtle" style="background:rgba(13,148,136,.04);">
                <div class="sig-card__title">
                    <i class="bi bi-signpost-split-fill" style="color:#0D9488;"></i> Rutas Turísticas
                </div>
                <span class="u-fs-11px u-c-text-tertiary">Cupo de atención</span>
            </div>
            <div class="sig-card__body u-p-sp-4">
                <div class="u-ai-center u-jc-space-between u-gap-sp-4 u-p-sp-3-sp-4 u-bg-bg-muted-subtle u-br-8px u-bd-1px-solid-border-subtle" style="display:flex;">
                    <div>
                        <div class="u-fs-12px u-fw-600 u-c-text-primary u-mb-2px">
                            <i class="bi bi-people-fill" style="color:#0D9488;"></i> Personas por día
                        </div>
                        <div class="u-fs-11px u-c-text-tertiary">
                            Máximo de participantes atendidos en una misma fecha, sumando todas las salidas.
                            Es una <strong>advertencia</strong>, no un bloqueo. 0 = sin tope.
                        </div>
                    </div>
                    <div class="u-ai-center u-gap-sp-2" style="display:flex;">
                        <input type="number" name="rutas_cupo_diario" class="sig-input"
                               style="width:72px;text-align:center;font-weight:700;font-size:15px;padding:var(--sp-1) var(--sp-2);"
                               min="0" max="1000"
                               value="<?php echo (int)($cfg['rutas_cupo_diario']['valor'] ?? 60); ?>">
                        <span class="u-fs-12px u-c-text-secondary">pers.</span>
                    </div>
                </div>
                <div class="u-fs-11px u-c-text-tertiary u-mt-sp-3">
                    Las restricciones de edad y las condiciones de cada recorrido
                    (Exploradores 4–16, Río Brito desde 12…) se configuran
                    <a href="<?php echo URL_ROOT; ?>/rutas/index">en cada ruta del catálogo</a>, no aquí.
                </div>
            </div>
        </div>
    </div>

</div>

<?php /* La sección NÓMINA vivía aquí. Sus parámetros se mudaron a
         /nomina/parametros el 2026-09-14 y desde entonces este bloque solo
         contenía un cartel que apuntaba allá: ruido en una pantalla de
         configuración. El enlace vive en el menú lateral (Nómina → Parámetros),
         que es donde el usuario lo busca. */ ?>

<!-- Guardar -->
<div style="display:flex; padding-top:var(--sp-2);" class="anim-slide-up u-jc-flex-end u-gap-sp-3 u-bdt-1px-solid-border-subtle">
    <a href="<?php echo URL_ROOT; ?>/dashboard/index" class="btn-sig btn-sig--ghost">
        <i class="bi bi-x-lg"></i> Cancelar
    </a>
    <button type="submit" class="btn-sig btn-sig--primary" style="padding:0 var(--sp-8);">
        <i class="bi bi-check-lg"></i> Guardar Configuración
    </button>
</div>

</form>

<?php require_once '../app/views/inc/footer.php'; ?>
