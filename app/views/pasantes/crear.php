<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/pasantes/index" style="color:inherit; text-decoration:none;">Pasantes</a> · Registro
        </div>
        <h1 class="page__title">Ingresar Nuevo Pasante</h1>
        <p class="page__subtitle">Apertura de expediente académico y asignación de tutoría institucional.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/pasantes/index" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Cancelar
        </a>
    </div>
</div>

<div class="sig-card anim-slide-up" style="max-width:900px; margin:0 auto var(--sp-8);">
    <div class="sig-card__body" style="padding:var(--sp-8);">
        <form action="<?php echo URL_ROOT; ?>/pasantes/crear" method="POST">
            
            <div style="display:flex; align-items:center; gap:var(--sp-3); margin-bottom:var(--sp-6); border-bottom:1px solid var(--border-subtle); padding-bottom:var(--sp-3);">
                <i class="bi bi-person-bounding-box" style="font-size:20px; color:var(--brand-500);"></i>
                <h3 style="font-size:18px; font-weight:700; color:var(--text-primary); margin:0;">Datos Personales</h3>
            </div>

            <div class="row g-4 mb-8">
                <div class="col-md-4">
                    <div class="sig-field">
                        <label class="sig-field__label">Cédula de Identidad <span class="req">*</span></label>
                        <input type="text" name="cedula" class="sig-input" placeholder="V-00.000.000" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="sig-field">
                        <label class="sig-field__label">Nombres <span class="req">*</span></label>
                        <input type="text" name="nombre" class="sig-input" required placeholder="Ej: María José">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="sig-field">
                        <label class="sig-field__label">Apellidos <span class="req">*</span></label>
                        <input type="text" name="apellido" class="sig-input" required placeholder="Ej: Perez Silva">
                    </div>
                </div>
            </div>

            <div style="display:flex; align-items:center; gap:var(--sp-3); margin-bottom:var(--sp-6); border-bottom:1px solid var(--border-subtle); padding-bottom:var(--sp-3); margin-top:var(--sp-4);">
                <i class="bi bi-building" style="font-size:20px; color:var(--brand-500);"></i>
                <h3 style="font-size:18px; font-weight:700; color:var(--text-primary); margin:0;">Datos Académicos e Institucionales</h3>
            </div>

            <div class="row g-4 mb-8">
                <div class="col-md-6">
                    <div class="sig-field">
                        <label class="sig-field__label">Institución de Origen <span class="req">*</span></label>
                        <input type="text" name="institucion" class="sig-input" placeholder="Ej: UDO, UPTOS Clodosbaldo Russian..." required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="sig-field">
                        <label class="sig-field__label">Carrera / Especialidad <span class="req">*</span></label>
                        <input type="text" name="carrera" class="sig-input" required placeholder="Ej: Turismo, Administración, Informática...">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="sig-field">
                        <label class="sig-field__label">Tutor Institucional (IMATUR)</label>
                        <select name="id_tutor_institucional" class="sig-select">
                            <option value="">-- Seleccionar Tutor (Opcional) --</option>
                            <?php if(isset($data['empleados'])): ?>
                            <?php foreach($data['empleados'] ?? [] as $e): ?>
                                <option value="<?php echo $e->id; ?>"><?php echo ($e->nombre ?? '') . ' ' . ($e->apellido ?? ''); ?></option>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="sig-field">
                        <label class="sig-field__label">
                            Responsable externo
                            <span style="font-weight:400;color:var(--text-tertiary);font-size:11px;"> — Persona en la institución a quien se dirige la carta de aceptación</span>
                        </label>
                        <input type="text" name="tutor_externo" class="sig-input"
                               placeholder="Ej: Prof. Rosa Rincón, Responsable de Gestión de Proyecto">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="sig-field">
                        <label class="sig-field__label">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="sig-input">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="sig-field">
                        <label class="sig-field__label">Fecha Culminación</label>
                        <input type="date" name="fecha_fin" class="sig-input">
                    </div>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; padding-top:var(--sp-6); border-top:1px solid var(--border-subtle);">
                <button type="submit" class="btn-sig btn-sig--primary" style="padding:0 var(--sp-10); height:48px; font-size:16px;">
                    <i class="bi bi-save"></i> Registrar y Abrir Expediente
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
