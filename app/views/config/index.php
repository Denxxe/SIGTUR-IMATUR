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
<div style="display:flex;align-items:center;gap:var(--sp-3);margin:0 0 var(--sp-4);" class="anim-slide-up">
    <div style="width:4px;height:20px;border-radius:2px;background:#3B82F6;flex-shrink:0;"></div>
    <span style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-secondary);">Datos Institucionales</span>
    <div style="flex:1;height:1px;background:var(--border-subtle);"></div>
</div>

<div class="row g-4 mb-5 anim-slide-up">

    <!-- Firmante / Director -->
    <div class="col-md-4">
        <div class="sig-card h-100" style="border-top:3px solid #3B82F6;">
            <div class="sig-card__head" style="background:rgba(59,130,246,.04); border-bottom:1px solid var(--border-subtle);">
                <div class="sig-card__title">
                    <i class="bi bi-person-badge-fill" style="color:#3B82F6;"></i> Firmante / Director
                </div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="sig-field" style="margin:0;">
                            <label class="sig-field__label">Nombre</label>
                            <input type="text" name="director_nombre" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['director_nombre']['valor'] ?? ''); ?>"
                                   placeholder="Ej: María">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="sig-field" style="margin:0;">
                            <label class="sig-field__label">Apellido</label>
                            <input type="text" name="director_apellido" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['director_apellido']['valor'] ?? ''); ?>"
                                   placeholder="Ej: González">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="sig-field" style="margin:0;">
                            <label class="sig-field__label">Cargo</label>
                            <input type="text" name="director_cargo" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['director_cargo']['valor'] ?? ''); ?>"
                                   placeholder="Ej: Director General">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resolución y Gaceta -->
    <div class="col-md-4">
        <div class="sig-card h-100" style="border-top:3px solid #0891B2;">
            <div class="sig-card__head" style="background:rgba(8,145,178,.04); border-bottom:1px solid var(--border-subtle);">
                <div class="sig-card__title">
                    <i class="bi bi-file-text-fill" style="color:#0891B2;"></i> Resolución y Gaceta
                </div>
                <span style="font-size:11px;color:var(--text-tertiary);">Referencia legal en documentos</span>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="sig-field" style="margin:0;">
                            <label class="sig-field__label">N° Resolución</label>
                            <input type="text" name="resolucion_numero" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['resolucion_numero']['valor'] ?? ''); ?>"
                                   placeholder="025">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="sig-field" style="margin:0;">
                            <label class="sig-field__label">Fecha</label>
                            <input type="text" name="resolucion_fecha" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['resolucion_fecha']['valor'] ?? ''); ?>"
                                   placeholder="15 ene 2024">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="sig-field" style="margin:0;">
                            <label class="sig-field__label">N° Gaceta</label>
                            <input type="text" name="gaceta_numero" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['gaceta_numero']['valor'] ?? ''); ?>"
                                   placeholder="042">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="sig-field" style="margin:0;">
                            <label class="sig-field__label">Fecha</label>
                            <input type="text" name="gaceta_fecha" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['gaceta_fecha']['valor'] ?? ''); ?>"
                                   placeholder="20 ene 2024">
                        </div>
                    </div>
                    <div class="col-12">
                        <div style="padding:var(--sp-2) var(--sp-3); background:rgba(8,145,178,.06); border-radius:6px; font-size:11px; color:var(--text-secondary); line-height:1.5;">
                            <i class="bi bi-info-circle" style="color:#0891B2;"></i>
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
            <div class="sig-card__head" style="background:rgba(5,150,105,.04); border-bottom:1px solid var(--border-subtle);">
                <div class="sig-card__title">
                    <i class="bi bi-telephone-fill" style="color:#059669;"></i> Contacto Institucional
                </div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="sig-field" style="margin:0;">
                            <label class="sig-field__label">Teléfono</label>
                            <input type="text" name="telf_institucion" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['telf_institucion']['valor'] ?? ''); ?>"
                                   placeholder="(0293) 431-4073">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="sig-field" style="margin:0;">
                            <label class="sig-field__label">Correo electrónico</label>
                            <input type="email" name="correo_institucion" class="sig-input"
                                   value="<?php echo htmlspecialchars($cfg['correo_institucion']['valor'] ?? ''); ?>"
                                   placeholder="imatur.cumana@gmail.com">
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
<div style="display:flex;align-items:center;gap:var(--sp-3);margin:0 0 var(--sp-4);" class="anim-slide-up">
    <div style="width:4px;height:20px;border-radius:2px;background:#F59E0B;flex-shrink:0;"></div>
    <span style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-secondary);">Planificación Anual — <?php echo date('Y'); ?></span>
    <div style="flex:1;height:1px;background:var(--border-subtle);"></div>
</div>

<div class="row g-4 mb-5 anim-slide-up">

    <div class="col-md-6">
        <div class="sig-card" style="border-top:3px solid #7C3AED;">
            <div class="sig-card__body" style="padding:var(--sp-5);">
                <div style="display:flex;align-items:flex-start;gap:var(--sp-4);">
                    <div style="width:44px;height:44px;border-radius:12px;background:rgba(124,58,237,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-mortarboard-fill" style="font-size:1.2rem;color:#7C3AED;"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:700;color:var(--text-primary);margin-bottom:2px;">Meta — Actividades Formativas</div>
                        <div style="font-size:12px;color:var(--text-secondary);margin-bottom:var(--sp-4);">Talleres, charlas e inducciones planificados para el año</div>
                        <div style="display:flex;align-items:center;gap:var(--sp-3);">
                            <input type="number" name="meta_talleres_anio" class="sig-input"
                                   style="font-size:1.5rem;font-weight:800;text-align:center;color:#7C3AED;width:120px;padding:var(--sp-2) var(--sp-3);"
                                   min="0" value="<?php echo (int)($cfg['meta_talleres_anio']['valor'] ?? 0); ?>">
                            <span style="font-size:13px;color:var(--text-tertiary);">actividades para <?php echo date('Y'); ?></span>
                        </div>
                        <div style="margin-top:var(--sp-2);font-size:11px;color:var(--text-tertiary);">0 = sin meta definida — el indicador se oculta</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="sig-card" style="border-top:3px solid #D97706;">
            <div class="sig-card__body" style="padding:var(--sp-5);">
                <div style="display:flex;align-items:flex-start;gap:var(--sp-4);">
                    <div style="width:44px;height:44px;border-radius:12px;background:rgba(217,119,6,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-geo-alt-fill" style="font-size:1.2rem;color:#D97706;"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:700;color:var(--text-primary);margin-bottom:2px;">Meta — Rutas Turísticas</div>
                        <div style="font-size:12px;color:var(--text-secondary);margin-bottom:var(--sp-4);">Rutas planificadas para operar durante el año</div>
                        <div style="display:flex;align-items:center;gap:var(--sp-3);">
                            <input type="number" name="meta_rutas_anio" class="sig-input"
                                   style="font-size:1.5rem;font-weight:800;text-align:center;color:#D97706;width:120px;padding:var(--sp-2) var(--sp-3);"
                                   min="0" value="<?php echo (int)($cfg['meta_rutas_anio']['valor'] ?? 0); ?>">
                            <span style="font-size:13px;color:var(--text-tertiary);">rutas para <?php echo date('Y'); ?></span>
                        </div>
                        <div style="margin-top:var(--sp-2);font-size:11px;color:var(--text-tertiary);">0 = sin meta definida — el indicador se oculta</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ════════════════════════════════════════════════════════════
     SECCIÓN 3 — PARÁMETROS DEL SISTEMA
═══════════════════════════════════════════════════════════════ -->
<div style="display:flex;align-items:center;gap:var(--sp-3);margin:0 0 var(--sp-4);" class="anim-slide-up">
    <div style="width:4px;height:20px;border-radius:2px;background:#64748B;flex-shrink:0;"></div>
    <span style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-secondary);">Parámetros del Sistema</span>
    <div style="flex:1;height:1px;background:var(--border-subtle);"></div>
</div>

<div class="row g-4 mb-6 anim-slide-up">

    <!-- Umbrales de Alertas -->
    <div class="col-md-6">
        <div class="sig-card h-100" style="border-top:3px solid #EF4444;">
            <div class="sig-card__head" style="background:rgba(239,68,68,.04); border-bottom:1px solid var(--border-subtle);">
                <div class="sig-card__title">
                    <i class="bi bi-bell-fill" style="color:#EF4444;"></i> Umbrales de Alertas
                </div>
                <span style="font-size:11px;color:var(--text-tertiary);">Días de anticipación · Panel Principal</span>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div class="row g-3">
                    <div class="col-12">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:var(--sp-4); padding:var(--sp-3) var(--sp-4); background:var(--bg-muted-subtle); border-radius:8px; border:1px solid var(--border-subtle);">
                            <div>
                                <div style="font-size:12px;font-weight:600;color:var(--text-primary);margin-bottom:2px;">
                                    <i class="bi bi-person-badge" style="color:#3B82F6;"></i> Contratos vencientes
                                </div>
                                <div style="font-size:11px;color:var(--text-tertiary);">Alertar con X días de anticipación</div>
                            </div>
                            <div style="display:flex;align-items:center;gap:var(--sp-2);">
                                <input type="number" name="dias_preaviso_contrato" class="sig-input"
                                       style="width:72px;text-align:center;font-weight:700;font-size:15px;padding:var(--sp-1) var(--sp-2);"
                                       min="1" max="365"
                                       value="<?php echo (int)($cfg['dias_preaviso_contrato']['valor'] ?? 30); ?>">
                                <span style="font-size:12px;color:var(--text-secondary);">días</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:var(--sp-4); padding:var(--sp-3) var(--sp-4); background:var(--bg-muted-subtle); border-radius:8px; border:1px solid var(--border-subtle);">
                            <div>
                                <div style="font-size:12px;font-weight:600;color:var(--text-primary);margin-bottom:2px;">
                                    <i class="bi bi-journal-text" style="color:#0EA5E9;"></i> Pasantes culminando
                                </div>
                                <div style="font-size:11px;color:var(--text-tertiary);">Alertar con X días de anticipación</div>
                            </div>
                            <div style="display:flex;align-items:center;gap:var(--sp-2);">
                                <input type="number" name="dias_preaviso_pasante" class="sig-input"
                                       style="width:72px;text-align:center;font-weight:700;font-size:15px;padding:var(--sp-1) var(--sp-2);"
                                       min="1" max="365"
                                       value="<?php echo (int)($cfg['dias_preaviso_pasante']['valor'] ?? 15); ?>">
                                <span style="font-size:12px;color:var(--text-secondary);">días</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:var(--sp-4); padding:var(--sp-3) var(--sp-4); background:var(--bg-muted-subtle); border-radius:8px; border:1px solid var(--border-subtle);">
                            <div>
                                <div style="font-size:12px;font-weight:600;color:var(--text-primary);margin-bottom:2px;">
                                    <i class="bi bi-alarm" style="color:#EF4444;"></i> Tolerancia de puntualidad
                                </div>
                                <div style="font-size:11px;color:var(--text-tertiary);">Minutos tras la hora de entrada antes de marcar impuntualidad</div>
                            </div>
                            <div style="display:flex;align-items:center;gap:var(--sp-2);">
                                <input type="number" name="minutos_tolerancia_puntualidad" class="sig-input"
                                       style="width:72px;text-align:center;font-weight:700;font-size:15px;padding:var(--sp-1) var(--sp-2);"
                                       min="0" max="120"
                                       value="<?php echo (int)($cfg['minutos_tolerancia_puntualidad']['valor'] ?? 15); ?>">
                                <span style="font-size:12px;color:var(--text-secondary);">min</span>
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
            <div class="sig-card__head" style="background:rgba(99,102,241,.04); border-bottom:1px solid var(--border-subtle);">
                <div class="sig-card__title">
                    <i class="bi bi-hash" style="color:#6366F1;"></i> Correlativo de Oficios
                </div>
                <span style="font-size:11px;color:var(--text-tertiary);">Se reinicia automáticamente cada año</span>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="sig-field" style="margin:0;">
                            <label class="sig-field__label">Último N° emitido</label>
                            <input type="number" name="correlativo_oficio" class="sig-input" min="0"
                                   style="text-align:center; font-size:1.1rem; font-weight:700;"
                                   value="<?php echo (int)($cfg['correlativo_oficio']['valor'] ?? 0); ?>">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="sig-field" style="margin:0;">
                            <label class="sig-field__label">Año del correlativo</label>
                            <input type="number" name="ano_correlativo" class="sig-input" min="2020"
                                   style="text-align:center; font-size:1.1rem; font-weight:700;"
                                   value="<?php echo (int)($cfg['ano_correlativo']['valor'] ?? date('Y')); ?>">
                        </div>
                    </div>
                    <div class="col-12">
                        <?php
                        $corrSig = str_pad((int)($cfg['correlativo_oficio']['valor'] ?? 0) + 1, 3, '0', STR_PAD_LEFT);
                        $anioSig = (int)($cfg['ano_correlativo']['valor'] ?? date('Y'));
                        ?>
                        <div style="display:flex;align-items:center;justify-content:space-between; padding:var(--sp-3) var(--sp-4); background:rgba(99,102,241,.06); border-radius:8px; border:1px solid rgba(99,102,241,.2);">
                            <span style="font-size:12px;color:var(--text-secondary);">Próximo oficio:</span>
                            <span style="font-size:1rem;font-weight:800;color:#6366F1; font-family:var(--font-mono);">
                                <?php echo $corrSig . '/' . $anioSig; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Guardar -->
<div style="display:flex;justify-content:flex-end;gap:var(--sp-3);padding-top:var(--sp-2);border-top:1px solid var(--border-subtle);" class="anim-slide-up">
    <a href="<?php echo URL_ROOT; ?>/dashboard/index" class="btn-sig btn-sig--ghost">
        <i class="bi bi-x-lg"></i> Cancelar
    </a>
    <button type="submit" class="btn-sig btn-sig--primary" style="padding:0 var(--sp-8);">
        <i class="bi bi-check-lg"></i> Guardar Configuración
    </button>
</div>

</form>

<?php require_once '../app/views/inc/footer.php'; ?>
