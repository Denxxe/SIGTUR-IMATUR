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
    <div class="sig-card__body u-p-sp-8">
        <form action="<?php echo URL_ROOT; ?>/pasantes/crear" method="POST">
            
            <div class="u-ai-center u-gap-sp-3 u-mb-sp-6 u-bdb-1px-solid-border-subtle u-pb-sp-3" style="display:flex;">
                <i class="bi bi-person-bounding-box u-fs-20px u-c-brand-500"></i>
                <h3 class="u-fs-18px u-fw-700 u-c-text-primary u-m-0">Datos Personales</h3>
            </div>

            <div class="row g-4 mb-8">
                <div class="col-md-4">
                    <div class="sig-field">
                        <label class="sig-field__label" for="cedula">Cédula de Identidad <span class="req">*</span></label>
                        <input id="cedula" type="text" name="cedula" class="sig-input" placeholder="12345678 (solo números)" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="sig-field">
                        <label class="sig-field__label" for="nombre">Nombres <span class="req">*</span></label>
                        <input id="nombre" type="text" name="nombre" class="sig-input" required placeholder="Ej: María José">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="sig-field">
                        <label class="sig-field__label" for="apellido">Apellidos <span class="req">*</span></label>
                        <input id="apellido" type="text" name="apellido" class="sig-input" required placeholder="Ej: Perez Silva">
                    </div>
                </div>
            </div>

            <div class="u-ai-center u-gap-sp-3 u-mb-sp-6 u-bdb-1px-solid-border-subtle u-pb-sp-3 u-mt-sp-4" style="display:flex;">
                <i class="bi bi-building u-fs-20px u-c-brand-500"></i>
                <h3 class="u-fs-18px u-fw-700 u-c-text-primary u-m-0">Datos Académicos e Institucionales</h3>
            </div>

            <div class="row g-4 mb-8">
                <div class="col-md-6">
                    <div class="sig-field">
                        <label class="sig-field__label" for="institucion">Institución de Origen <span class="req">*</span></label>
                        <input id="institucion" type="text" name="institucion" class="sig-input" placeholder="Ej: UDO, UPTOS Clodosbaldo Russian..." required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="sig-field">
                        <label class="sig-field__label" for="carrera">Carrera / Especialidad <span class="req">*</span></label>
                        <input id="carrera" type="text" name="carrera" class="sig-input" required placeholder="Ej: Turismo, Administración, Informática...">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="sig-field">
                        <label class="sig-field__label" for="id_tutor_institucional">Tutor Institucional (IMATUR)</label>
                        <select id="id_tutor_institucional" name="id_tutor_institucional" class="sig-select">
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
                        <label class="sig-field__label" for="tutor_externo">
                            Responsable externo
                            <span class="u-fw-400 u-c-text-tertiary u-fs-11px"> — Persona en la institución a quien se dirige la carta de aceptación</span>
                        </label>
                        <input id="tutor_externo" type="text" name="tutor_externo" class="sig-input"
                               placeholder="Ej: Prof. Rosa Rincón, Responsable de Gestión de Proyecto">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="sig-field">
                        <label class="sig-field__label" for="fecha_inicio">Fecha Inicio</label>
                        <input id="fecha_inicio" type="date" name="fecha_inicio" class="sig-input">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="sig-field">
                        <label class="sig-field__label" for="fecha_fin">Fecha Culminación</label>
                        <input id="fecha_fin" type="date" name="fecha_fin" class="sig-input">
                    </div>
                </div>
            </div>

            <div class="u-jc-flex-end u-bdt-1px-solid-border-subtle" style="display:flex; padding-top:var(--sp-6);">
                <button type="submit" class="btn-sig btn-sig--primary" style="padding:0 var(--sp-10); height:48px; font-size:16px;">
                    <i class="bi bi-save"></i> Registrar y Abrir Expediente
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
