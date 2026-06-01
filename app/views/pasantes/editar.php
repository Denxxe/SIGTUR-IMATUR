<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/pasantes/index" style="color:inherit; text-decoration:none;">Pasantes</a> · Editar Expediente
        </div>
        <h1 class="page__title"><?php echo $data['pasante']->nombre . ' ' . $data['pasante']->apellido; ?></h1>
        <p class="page__subtitle">Modificar datos académicos, estado institucional y evaluación final.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/pasantes/detalle/<?php echo $data['pasante']->id; ?>" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Cancelar
        </a>
    </div>
</div>

<div class="sig-card anim-slide-up" style="max-width:900px; margin:0 auto var(--sp-8);">
    <div class="sig-card__body" style="padding:var(--sp-8);">
        <form action="<?php echo URL_ROOT; ?>/pasantes/editar/<?php echo $data['pasante']->id; ?>" method="POST">
            <input type="hidden" name="id_persona" value="<?php echo $data['pasante']->id_persona; ?>">

            <!-- Datos Personales -->
            <div style="display:flex; align-items:center; gap:var(--sp-3); margin-bottom:var(--sp-6); border-bottom:1px solid var(--border-subtle); padding-bottom:var(--sp-3);">
                <i class="bi bi-person-bounding-box" style="font-size:20px; color:var(--brand-500);"></i>
                <h3 style="font-size:18px; font-weight:700; color:var(--text-primary); margin:0;">Datos Personales</h3>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="sig-field">
                        <label class="sig-field__label">Cédula de Identidad <span class="req">*</span></label>
                        <input type="text" name="cedula" class="sig-input" required
                               value="<?php echo $data['pasante']->cedula; ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="sig-field">
                        <label class="sig-field__label">Nombres <span class="req">*</span></label>
                        <input type="text" name="nombre" class="sig-input" required
                               value="<?php echo $data['pasante']->nombre; ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="sig-field">
                        <label class="sig-field__label">Apellidos <span class="req">*</span></label>
                        <input type="text" name="apellido" class="sig-input" required
                               value="<?php echo $data['pasante']->apellido; ?>">
                    </div>
                </div>
            </div>

            <!-- Datos Académicos -->
            <div style="display:flex; align-items:center; gap:var(--sp-3); margin-bottom:var(--sp-6); border-bottom:1px solid var(--border-subtle); padding-bottom:var(--sp-3); margin-top:var(--sp-6);">
                <i class="bi bi-building" style="font-size:20px; color:var(--brand-500);"></i>
                <h3 style="font-size:18px; font-weight:700; color:var(--text-primary); margin:0;">Datos Académicos e Institucionales</h3>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="sig-field">
                        <label class="sig-field__label">Institución de Origen <span class="req">*</span></label>
                        <input type="text" name="institucion" class="sig-input" required
                               value="<?php echo $data['pasante']->institucion; ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="sig-field">
                        <label class="sig-field__label">Carrera / Especialidad <span class="req">*</span></label>
                        <input type="text" name="carrera" class="sig-input" required
                               value="<?php echo $data['pasante']->carrera; ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="sig-field">
                        <label class="sig-field__label">Tutor Institucional (IMATUR)</label>
                        <select name="id_tutor_institucional" class="sig-select">
                            <option value="">-- Sin asignar --</option>
                            <?php foreach ($data['empleados'] ?? [] as $e): ?>
                                <option value="<?php echo $e->id; ?>"
                                    <?php echo ($data['pasante']->id_tutor_institucional == $e->id) ? 'selected' : ''; ?>>
                                    <?php echo $e->nombre . ' ' . $e->apellido; ?>
                                </option>
                            <?php endforeach; ?>
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
                               value="<?php echo htmlspecialchars($data['pasante']->tutor_externo ?? ''); ?>"
                               placeholder="Ej: Prof. Rosa Rincón, Responsable de Gestión de Proyecto">
                    </div>
                </div>
                <?php if (!empty($data['pasante']->oficio_aceptacion)): ?>
                <div class="col-md-6">
                    <div class="sig-field">
                        <label class="sig-field__label">
                            N° Carta de aceptación
                            <span style="font-weight:400;color:var(--text-tertiary);font-size:11px;"> — Editar para agrupar con otro pasante en la misma carta</span>
                        </label>
                        <input type="text" name="oficio_aceptacion" class="sig-input"
                               value="<?php echo htmlspecialchars($data['pasante']->oficio_aceptacion ?? ''); ?>"
                               placeholder="Ej: PAST-001/2026"
                               style="font-family:var(--font-mono);">
                        <small style="color:var(--text-tertiary);font-size:11px;">
                            <i class="bi bi-info-circle"></i>
                            Para incluir otro pasante en esta carta, edita su expediente y coloca el mismo número aquí.
                        </small>
                    </div>
                </div>
                <?php endif; ?>
                <div class="col-md-3">
                    <div class="sig-field">
                        <label class="sig-field__label">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="sig-input"
                               value="<?php echo $data['pasante']->fecha_inicio; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="sig-field">
                        <label class="sig-field__label">Fecha Culminación</label>
                        <input type="date" name="fecha_fin" class="sig-input"
                               value="<?php echo $data['pasante']->fecha_fin; ?>">
                    </div>
                </div>
            </div>

            <!-- Estado y Evaluación -->
            <div style="display:flex; align-items:center; gap:var(--sp-3); margin-bottom:var(--sp-6); border-bottom:1px solid var(--border-subtle); padding-bottom:var(--sp-3); margin-top:var(--sp-6);">
                <i class="bi bi-clipboard2-check" style="font-size:20px; color:var(--brand-500);"></i>
                <h3 style="font-size:18px; font-weight:700; color:var(--text-primary); margin:0;">Estado y Evaluación</h3>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="sig-field">
                        <label class="sig-field__label">Estado Institucional <span class="req">*</span></label>
                        <select name="estado" id="selectEstado" class="sig-select" required onchange="toggleEvaluacion()">
                            <?php
                            $estados = ['Postulado', 'Aceptado', 'En Curso', 'Culminado', 'Rechazado'];
                            foreach ($estados as $est):
                            ?>
                                <option value="<?php echo $est; ?>"
                                    <?php echo ($data['pasante']->estado == $est) ? 'selected' : ''; ?>>
                                    <?php echo $est; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-6 seccion-evaluacion" id="campoEvaluacion"
                     style="<?php echo ($data['pasante']->estado == 'Culminado') ? '' : 'display:none;'; ?>">
                    <div class="sig-field">
                        <label class="sig-field__label">Evaluación / Comentario Final</label>
                        <textarea name="evaluacion" class="sig-textarea" rows="2"
                                  placeholder="Observaciones del tutor sobre el desempeño..."><?php echo $data['pasante']->evaluacion ?? ''; ?></textarea>
                    </div>
                </div>

                <div class="col-md-2 seccion-evaluacion" id="campoNota"
                     style="<?php echo ($data['pasante']->estado == 'Culminado') ? '' : 'display:none;'; ?>">
                    <div class="sig-field">
                        <label class="sig-field__label">Nota (0–20)</label>
                        <input type="number" name="nota" class="sig-input" min="0" max="20" step="0.01"
                               placeholder="Ej: 18.5"
                               value="<?php echo $data['pasante']->nota ?? ''; ?>">
                    </div>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:var(--sp-3); padding-top:var(--sp-6); border-top:1px solid var(--border-subtle);">
                <a href="<?php echo URL_ROOT; ?>/pasantes/eliminar/<?php echo $data['pasante']->id; ?>"
                   class="btn-sig btn-sig--danger"
                   onclick="return confirm('¿Desactivar este pasante? Podrá recuperarlo desde la Papelera.')">
                    <i class="bi bi-trash"></i> Desactivar
                </a>
                <button type="submit" class="btn-sig btn-sig--primary" style="padding:0 var(--sp-10); height:48px; font-size:16px;">
                    <i class="bi bi-check-lg"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleEvaluacion() {
    const estado = document.getElementById('selectEstado').value;
    const mostrar = estado === 'Culminado';
    document.getElementById('campoEvaluacion').style.display = mostrar ? '' : 'none';
    document.getElementById('campoNota').style.display = mostrar ? '' : 'none';
}
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
