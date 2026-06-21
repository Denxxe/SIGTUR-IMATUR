<?php require_once '../app/views/inc/header.php';
$e = $data['empleado'] ?? null;
$isEdit = $e !== null;
$val = function ($f, $d = '') use ($e) { return ($e && isset($e->$f) && $e->$f !== null) ? htmlspecialchars($e->$f) : $d; };
$sel = function ($f, $o) use ($e) { return ($e && ($e->$f ?? null) == $o) ? 'selected' : ''; };
$chk = function ($f) use ($e) { $v = $e->$f ?? null; return ($v === true || $v === 't' || $v === '1' || $v === 1) ? 'checked' : ''; };
$pasos = ['Datos personales', 'Formación', 'Datos institucionales', 'Carga familiar', 'Resumen'];
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · <?php echo $isEdit ? 'Edición' : 'Registro'; ?> de Personal</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Empleado'; ?></h1>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/empleados/index" class="btn-sig btn-sig--ghost"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
</div>

<!-- Stepper -->
<div class="wz-stepper anim-slide-up" id="wzStepper" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:var(--sp-5)">
    <?php foreach ($pasos as $i => $p): ?>
        <div class="wz-pill" data-pill="<?php echo $i; ?>"
             style="display:flex;align-items:center;gap:6px;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;background:var(--bg-muted);color:var(--text-secondary);border:1px solid var(--border-subtle)">
            <span class="wz-num" style="width:18px;height:18px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:11px;background:var(--text-tertiary);color:#fff"><?php echo $i + 1; ?></span>
            <?php echo $p; ?>
        </div>
    <?php endforeach; ?>
</div>

<form action="<?php echo URL_ROOT; ?>/empleados/store" method="POST" id="wzForm" class="sig-card" data-edit="<?php echo $isEdit ? '1' : '0'; ?>">
  <div class="sig-card__body" style="padding:var(--sp-5) var(--sp-6)">
    <input type="hidden" name="id" value="<?php echo $isEdit ? (int)$e->id : ''; ?>">
    <input type="hidden" name="id_persona" value="<?php echo $isEdit ? (int)$e->id_persona : ''; ?>">

    <!-- ───────── PASO 1: Datos personales ───────── -->
    <div class="wz-step" data-step="0">
        <h5 class="mb-4"><i class="bi bi-person-vcard"></i> Datos personales</h5>
        <div class="row g-3">
            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Cédula <span class="req">*</span></label>
                <input type="text" name="cedula" id="emp_cedula" class="sig-input" required value="<?php echo $val('cedula'); ?>">
                <small id="emp_cedula_msg" style="display:block;margin-top:4px;font-weight:600;"></small></div></div>
            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Nombres <span class="req">*</span></label>
                <input type="text" name="nombre" class="sig-input" required value="<?php echo $val('nombre'); ?>"></div></div>
            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Apellidos <span class="req">*</span></label>
                <input type="text" name="apellido" class="sig-input" required value="<?php echo $val('apellido'); ?>"></div></div>
            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Género <span class="req">*</span></label>
                <select name="genero" class="sig-select" required>
                    <option value="M" <?php echo $sel('genero','M'); ?>>Masculino</option>
                    <option value="F" <?php echo $sel('genero','F'); ?>>Femenino</option>
                </select></div></div>
            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Fecha de nacimiento <span class="req">*</span></label>
                <input type="date" name="fecha_nacimiento" id="emp_fecha_nac" class="sig-input js-edad" required
                       data-edad-min="18" data-edad-max="65" data-edad-target="emp_edad_badge"
                       value="<?php echo $val('fecha_nacimiento'); ?>">
                <small id="emp_edad_badge" style="display:block;margin-top:4px;font-weight:600;"></small>
                <small style="color:var(--text-tertiary)">18–65 años · comisión de servicio: 18–70.</small></div></div>
            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Teléfono <span class="req">*</span></label>
                <input type="text" name="telefono" class="sig-input" required value="<?php echo $val('telefono'); ?>"></div></div>
            <div class="col-md-6"><div class="sig-field"><label class="sig-field__label">Correo electrónico</label>
                <input type="email" name="correo" class="sig-input" value="<?php echo $val('correo'); ?>"></div></div>
            <div class="col-md-6"><div class="sig-field"><label class="sig-field__label">Parroquia <span class="req">*</span></label>
                <select name="parroquia_id" class="sig-select" required>
                    <option value="">— Seleccione —</option>
                    <?php foreach ($data['parroquias'] ?? [] as $pq): ?>
                        <option value="<?php echo $pq->id; ?>" <?php echo $sel('parroquia_id',$pq->id); ?>><?php echo htmlspecialchars($pq->nombre); ?></option>
                    <?php endforeach; ?>
                </select></div></div>
            <div class="col-md-12"><div class="sig-field"><label class="sig-field__label">Dirección de habitación <span class="req">*</span></label>
                <textarea name="direccion" class="sig-textarea" rows="2" required><?php echo $val('direccion'); ?></textarea></div></div>
            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">RIF <span class="req">*</span></label>
                <input type="text" name="rif" class="sig-input" placeholder="V-XXXXXXXXX" required value="<?php echo $val('rif'); ?>"></div></div>
            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Estado civil</label>
                <select name="estado_civil" class="sig-select">
                    <option value="">— Seleccione —</option>
                    <?php foreach (Empleado::ESTADOS_CIVILES as $ec): ?>
                        <option value="<?php echo $ec; ?>" <?php echo $sel('estado_civil',$ec); ?>><?php echo $ec; ?></option>
                    <?php endforeach; ?>
                </select></div></div>
            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">¿Discapacidad?</label>
                <div class="form-check" style="padding-top:8px"><input class="form-check-input" type="checkbox" name="discapacidad" id="wz_disc" value="1" <?php echo $chk('discapacidad'); ?> onchange="wzToggleDisc()">
                    <label class="form-check-label" for="wz_disc">Sí, posee discapacidad</label></div></div></div>
            <div class="col-md-12" id="wz_disc_wrap" style="display:none"><div class="sig-field"><label class="sig-field__label">Detalle de la discapacidad / ajuste de horario</label>
                <input type="text" name="discapacidad_detalle" class="sig-input" value="<?php echo $val('discapacidad_detalle'); ?>"></div></div>
            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Centro de votación</label>
                <input type="text" name="centro_votacion" class="sig-input" value="<?php echo $val('centro_votacion'); ?>"></div></div>
            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Consejo comunal</label>
                <input type="text" name="consejo_comunal" class="sig-input" value="<?php echo $val('consejo_comunal'); ?>"></div></div>
            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Comuna</label>
                <input type="text" name="comuna" class="sig-input" value="<?php echo $val('comuna'); ?>"></div></div>
        </div>
    </div>

    <!-- ───────── PASO 2: Formación ───────── -->
    <div class="wz-step" data-step="1" style="display:none">
        <h5 class="mb-4"><i class="bi bi-mortarboard"></i> Formación académica</h5>
        <div class="row g-3">
            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Nivel académico</label>
                <select name="nivel_academico" class="sig-select js-search">
                    <option value="">— Seleccione —</option>
                    <?php $nivActual = $e->nivel_academico ?? null;
                          if (!empty($nivActual) && !in_array($nivActual, Empleado::NIVELES_ACADEMICOS, true)): ?>
                        <option value="<?php echo htmlspecialchars($nivActual); ?>" selected><?php echo htmlspecialchars($nivActual); ?> (actual)</option>
                    <?php endif; ?>
                    <?php foreach (Empleado::NIVELES_ACADEMICOS as $na): ?>
                        <option value="<?php echo $na; ?>" <?php echo $sel('nivel_academico',$na); ?>><?php echo $na; ?></option>
                    <?php endforeach; ?>
                </select></div></div>
            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Profesión</label>
                <input type="text" name="profesion" class="sig-input" value="<?php echo $val('profesion'); ?>"></div></div>
            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Fecha de graduación</label>
                <input type="date" name="fecha_graduacion" class="sig-input" value="<?php echo $val('fecha_graduacion'); ?>"></div></div>
            <div class="col-md-6"><div class="sig-field"><label class="sig-field__label">Institución académica</label>
                <input type="text" name="institucion_academica" class="sig-input" value="<?php echo $val('institucion_academica'); ?>"></div></div>
        </div>
        <p style="color:var(--text-tertiary);font-size:12px;margin-top:var(--sp-3)"><i class="bi bi-info-circle"></i> Los cursos realizados se gestionan en el expediente tras crear el empleado.</p>
    </div>

    <!-- ───────── PASO 3: Datos institucionales ───────── -->
    <div class="wz-step" data-step="2" style="display:none">
        <h5 class="mb-4"><i class="bi bi-building"></i> Datos institucionales</h5>
        <div class="row g-3">
            <div class="col-md-6"><div class="sig-field"><label class="sig-field__label">Cargo <span class="req">*</span></label>
                <select name="id_cargo" class="sig-select" required>
                    <option value="">— Seleccione —</option>
                    <?php foreach ($data['cargos'] ?? [] as $c): ?>
                        <option value="<?php echo $c->id; ?>" <?php echo $sel('id_cargo',$c->id); ?>><?php echo htmlspecialchars($c->nombre); ?></option>
                    <?php endforeach; ?>
                </select></div></div>
            <div class="col-md-6"><div class="sig-field"><label class="sig-field__label">Departamento <span class="req">*</span></label>
                <select name="id_departamento" class="sig-select" required>
                    <option value="">— Seleccione —</option>
                    <?php foreach ($data['departamentos'] ?? [] as $d): ?>
                        <option value="<?php echo $d->id; ?>" <?php echo $sel('id_departamento',$d->id); ?>><?php echo htmlspecialchars($d->nombre); ?></option>
                    <?php endforeach; ?>
                </select></div></div>
            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Nro. Expediente</label>
                <?php if ($isEdit): ?>
                    <input type="text" name="nro_expediente" class="sig-input" readonly value="<?php echo $val('nro_expediente'); ?>">
                    <small style="color:var(--text-tertiary)">Folio permanente del expediente.</small>
                <?php else: ?>
                    <input type="text" name="nro_expediente" class="sig-input" readonly value="<?php echo htmlspecialchars($data['proximo_expediente'] ?? ''); ?>"
                           style="background:var(--bg-muted);color:var(--text-secondary)">
                    <small style="color:var(--text-tertiary)"><i class="bi bi-magic"></i> Asignado automáticamente por el sistema al registrar.</small>
                <?php endif; ?></div></div>
            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Clasificación <span class="req">*</span></label>
                <select name="clasificacion" class="sig-select" required>
                    <option value="">— Seleccione —</option>
                    <?php foreach (Empleado::CLASIFICACIONES as $cl): ?>
                        <option value="<?php echo $cl; ?>" <?php echo $sel('clasificacion',$cl); ?>><?php echo $cl; ?></option>
                    <?php endforeach; ?>
                </select></div></div>
            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Tipo de contrato <span class="req">*</span></label>
                <select name="tipo_contrato" id="wz_tipo_contrato" class="sig-select" required onchange="wzVencToggle()">
                    <?php foreach (Empleado::TIPOS_CONTRATO as $tc): ?>
                        <option value="<?php echo $tc; ?>" <?php echo $isEdit ? $sel('tipo_contrato',$tc) : ($tc === Empleado::TIPO_CONTRATO_DEFAULT ? 'selected' : ''); ?>><?php echo $tc; ?></option>
                    <?php endforeach; ?>
                </select></div></div>
            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Institución / Nómina <span class="req">*</span></label>
                <select name="institucion_origen" id="wz_origen" class="sig-select" required onchange="wzOrigenCambio()">
                    <?php foreach (Empleado::INSTITUCIONES_ORIGEN as $io): ?>
                        <option value="<?php echo $io; ?>" <?php echo $isEdit ? $sel('institucion_origen',$io) : ($io === Empleado::INSTITUCION_ORIGEN_DEFAULT ? 'selected' : ''); ?>><?php echo $io; ?></option>
                    <?php endforeach; ?>
                </select></div></div>
            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Comisión de servicio</label>
                <div style="padding-top:8px"><span id="wz_comision_info" class="sig-badge sig-badge--neutral">—</span>
                    <small style="display:block;color:var(--text-tertiary);margin-top:2px">Se determina por la institución de origen.</small></div></div></div>
            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Horario asignado</label>
                <select name="id_horario" class="sig-select">
                    <option value="">— Sin horario —</option>
                    <?php foreach ($data['horarios'] ?? [] as $h): ?>
                        <option value="<?php echo $h->id; ?>" <?php echo $sel('id_horario',$h->id); ?>><?php echo htmlspecialchars($h->nombre); ?> (<?php echo substr($h->hora_entrada,0,5); ?>–<?php echo substr($h->hora_salida,0,5); ?>)</option>
                    <?php endforeach; ?>
                </select></div></div>
            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Grupo (Servicios Generales)</label>
                <select name="grupo_rotacion" class="sig-select">
                    <option value="">— No aplica —</option>
                    <?php foreach (Empleado::GRUPOS_ROTACION as $g): ?>
                        <option value="<?php echo $g; ?>" <?php echo $sel('grupo_rotacion',$g); ?>>Grupo <?php echo $g; ?></option>
                    <?php endforeach; ?>
                </select></div></div>
            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Fecha de ingreso <span class="req">*</span></label>
                <input type="date" name="fecha_ingreso" id="wz_fecha_ingreso" class="sig-input" required value="<?php echo $val('fecha_ingreso', date('Y-m-d')); ?>" oninput="wzVencCalc()"></div></div>
            <div class="col-md-4" id="wz_ingadm_wrap" style="display:none"><div class="sig-field"><label class="sig-field__label">Ingreso a la administración pública <small style="color:var(--text-secondary)">(comisión)</small></label>
                <input type="date" name="fecha_ingreso_administracion" id="wz_fecha_ingadm" class="sig-input" value="<?php echo $val('fecha_ingreso_administracion'); ?>">
                <small style="color:var(--text-tertiary)">Antigüedad para vacaciones: fecha original en Alcaldía/Gobernación.</small></div></div>
            <div class="col-md-4" id="wz_venc_wrap"><div class="sig-field"><label class="sig-field__label">Vencimiento del contrato <span class="req" id="wz_venc_req">*</span> <small style="color:var(--text-secondary)">(contratados)</small></label>
                <input type="date" name="fecha_vencimiento_contrato" id="wz_fecha_venc" class="sig-input" value="<?php echo $val('fecha_vencimiento_contrato'); ?>" oninput="wzVencCalc()">
                <small id="wz_venc_info" style="display:block;margin-top:4px;color:var(--text-tertiary)">Mínimo 3 meses desde el ingreso.</small></div></div>
            <div class="col-md-12"><div class="sig-field"><label class="sig-field__label">¿Usa uniforme?</label>
                <div class="form-check" style="padding-top:8px"><input class="form-check-input" type="checkbox" name="uniforme" id="wz_uniforme" value="1" <?php echo $chk('uniforme'); ?> onchange="wzToggleUniforme()">
                    <label class="form-check-label" for="wz_uniforme">Sí, registrar tallas</label></div></div></div>
            <div class="col-12" id="wz_tallas_wrap" style="display:none"><div class="row g-3">
                <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Talla camisa</label>
                    <input type="text" name="talla_camisa" class="sig-input" value="<?php echo $val('talla_camisa'); ?>"></div></div>
                <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Talla pantalón</label>
                    <input type="text" name="talla_pantalon" class="sig-input" value="<?php echo $val('talla_pantalon'); ?>"></div></div>
                <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Talla zapato</label>
                    <input type="text" name="talla_zapato" class="sig-input" value="<?php echo $val('talla_zapato'); ?>"></div></div>
            </div></div>
        </div>
    </div>

    <!-- ───────── PASO 4: Carga familiar ───────── -->
    <div class="wz-step" data-step="3" style="display:none">
        <h5 class="mb-4"><i class="bi bi-people"></i> Carga familiar</h5>
        <?php if ($isEdit): ?>
            <div class="sig-table-wrap" style="margin-bottom:var(--sp-3)">
                <table class="sig-table">
                    <thead><tr><th>Nombre</th><th>Cédula</th><th>Parentesco</th></tr></thead>
                    <tbody>
                        <?php if (empty($data['familiares'])): ?>
                            <tr><td colspan="3" class="sig-table-empty">Sin familiares registrados.</td></tr>
                        <?php else: foreach ($data['familiares'] as $f): ?>
                            <tr><td><?php echo htmlspecialchars($f->nombre_apellido); ?></td><td><?php echo htmlspecialchars($f->cedula ?? '—'); ?></td><td><?php echo htmlspecialchars($f->parentesco); ?></td></tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <a href="<?php echo URL_ROOT; ?>/empleados/detalle/<?php echo (int)$e->id; ?>" class="btn-sig btn-sig--ghost"><i class="bi bi-folder2-open"></i> Gestionar en el expediente</a>
        <?php else: ?>
            <p style="color:var(--text-secondary);font-size:13px">Agregue los familiares del empleado (opcional). Podrá añadir más en el expediente luego.</p>
            <div id="cfRows"></div>
            <button type="button" class="btn-sig btn-sig--ghost btn-sig--sm" onclick="cfAddRow()"><i class="bi bi-plus-lg"></i> Agregar familiar</button>
        <?php endif; ?>
    </div>

    <!-- ───────── PASO 5: Resumen ───────── -->
    <div class="wz-step" data-step="4" style="display:none">
        <h5 class="mb-4"><i class="bi bi-clipboard-check"></i> Resumen — verifique antes de guardar</h5>
        <div id="wzResumen" class="row g-3"></div>
    </div>

    <!-- Navegación -->
    <div style="display:flex;justify-content:space-between;margin-top:var(--sp-5);padding-top:var(--sp-4);border-top:1px solid var(--border-subtle)">
        <button type="button" class="btn-sig btn-sig--ghost" id="wzPrev" onclick="wzGo(-1)" style="visibility:hidden"><i class="bi bi-chevron-left"></i> Anterior</button>
        <div style="display:flex;gap:var(--sp-2)">
            <button type="button" class="btn-sig btn-sig--primary" id="wzNext" onclick="wzGo(1)">Siguiente <i class="bi bi-chevron-right"></i></button>
            <button type="submit" class="btn-sig btn-sig--primary" id="wzSubmit" style="display:none"><i class="bi bi-check-lg"></i> <?php echo $isEdit ? 'Guardar cambios' : 'Crear empleado'; ?></button>
        </div>
    </div>
  </div>
</form>

<script>
const wzForm = document.getElementById('wzForm');
const isEdit = wzForm.dataset.edit === '1';
const LS_KEY = 'sigtur_emp_wizard';
let wzCur = 0;
const wzSteps = Array.from(document.querySelectorAll('.wz-step'));
const totalSteps = wzSteps.length;

function wzShow(n) {
    wzSteps.forEach(s => s.style.display = (parseInt(s.dataset.step) === n ? '' : 'none'));
    document.querySelectorAll('.wz-pill').forEach(p => {
        const i = parseInt(p.dataset.pill);
        const num = p.querySelector('.wz-num');
        p.style.background = i === n ? 'var(--brand-50, #eff6ff)' : 'var(--bg-muted)';
        p.style.color = i === n ? 'var(--brand-600, #2563eb)' : 'var(--text-secondary)';
        p.style.borderColor = i === n ? 'var(--brand-600, #2563eb)' : 'var(--border-subtle)';
        num.style.background = i <= n ? 'var(--brand-600, #2563eb)' : 'var(--text-tertiary)';
    });
    document.getElementById('wzPrev').style.visibility = n === 0 ? 'hidden' : 'visible';
    document.getElementById('wzNext').style.display = n === totalSteps - 1 ? 'none' : '';
    document.getElementById('wzSubmit').style.display = n === totalSteps - 1 ? '' : 'none';
    if (n === totalSteps - 1) wzBuildResumen();
    wzCur = n;
    wzUpdateNav();
}

// Validez del paso (sin mostrar burbujas) — para habilitar/deshabilitar "Siguiente".
function wzStepValido(n) {
    const step = wzSteps[n];
    for (const f of step.querySelectorAll('input, select, textarea')) {
        if (f.offsetParent !== null && !f.checkValidity()) return false;
    }
    return true;
}

// Habilita "Siguiente" sólo si el paso actual es válido; refresca el submit (último paso).
function wzUpdateNav() {
    const next = document.getElementById('wzNext');
    if (next) next.disabled = !wzStepValido(wzCur);
    if (typeof window.sigturRefreshButtons === 'function') window.sigturRefreshButtons();
}

function wzValidateStep(n) {
    const step = wzSteps[n];
    const fields = step.querySelectorAll('input, select, textarea');
    for (const f of fields) {
        if (f.offsetParent !== null && !f.checkValidity()) { f.reportValidity(); return false; }
    }
    return true;
}

function wzGo(dir) {
    if (dir === 1 && !wzValidateStep(wzCur)) return;
    const next = Math.min(Math.max(wzCur + dir, 0), totalSteps - 1);
    wzShow(next);
}

// Campos condicionales
function wzToggleDisc() {
    const on = document.getElementById('wz_disc').checked;
    document.getElementById('wz_disc_wrap').style.display = on ? '' : 'none';
}
// Comisión de servicio = el empleado viene de Alcaldía o Gobernación (IMATUR = no comisión).
// La edad máxima depende del origen: IMATUR 65, comisión (Alcaldía/Gobernación) 70.
function wzOrigenCambio() {
    const origen = document.getElementById('wz_origen').value;
    const esComision = origen && origen !== 'IMATUR';
    const info = document.getElementById('wz_comision_info');
    if (info) {
        info.textContent = origen ? (esComision ? 'Sí (comisión de servicio)' : 'No (personal IMATUR)') : '—';
        info.className = 'sig-badge ' + (esComision ? 'sig-badge--info' : 'sig-badge--neutral');
    }
    const f = document.getElementById('emp_fecha_nac');
    if (f) { f.dataset.edadMax = esComision ? '70' : '65'; f.dispatchEvent(new Event('edad:refresh')); }
    const adm = document.getElementById('wz_ingadm_wrap');
    if (adm) adm.style.display = esComision ? '' : 'none';
}
function wzToggleUniforme() {
    const on = document.getElementById('wz_uniforme').checked;
    document.getElementById('wz_tallas_wrap').style.display = on ? '' : 'none';
}

// Verificación en vivo de cédula duplicada (empleado ya registrado).
let wzCedTimer = null;
function wzCheckCedula() {
    const inp = document.getElementById('emp_cedula');
    const msg = document.getElementById('emp_cedula_msg');
    if (!inp) return;
    const ced = (inp.value || '').replace(/\D/g, '');
    inp.setCustomValidity('');                 // se revalida tras la consulta
    if (msg) { msg.textContent = ''; msg.style.color = ''; }
    if (ced.length < 6) { if (typeof window.sigturRefreshButtons === 'function') window.sigturRefreshButtons(); return; }
    const idEmp = (wzForm.querySelector('[name="id"]') || {}).value || '';
    clearTimeout(wzCedTimer);
    wzCedTimer = setTimeout(() => {
        const url = '<?php echo URL_ROOT; ?>/empleados/verificarCedula?cedula=' + encodeURIComponent(ced) + (idEmp ? '&id=' + encodeURIComponent(idEmp) : '');
        fetch(url).then(r => r.json()).then(res => {
            if (res && res.existe) {
                inp.setCustomValidity('Cédula ya registrada');
                if (msg) { msg.style.color = 'var(--danger, #dc2626)'; msg.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Ya existe un empleado con esta cédula. Si egresó, use «Reingreso».'; }
            } else {
                inp.setCustomValidity('');
                if (msg) { msg.style.color = 'var(--success, #16a34a)'; msg.innerHTML = '<i class="bi bi-check-circle"></i> Cédula disponible.'; }
            }
            if (typeof window.sigturRefreshButtons === 'function') window.sigturRefreshButtons();
            wzUpdateNav();
        }).catch(() => {});
    }, 350);
}

// B4 — Vencimiento del contrato: oculto para Fijos (no expiran por tiempo);
// mínimo 3 meses desde el ingreso y muestra los días de vigencia en vivo.
function wzFmtISO(d) {
    const y = d.getFullYear(), m = String(d.getMonth() + 1).padStart(2, '0'), dd = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${dd}`;
}
function wzAddMonths(iso, n) {
    const d = new Date(iso + 'T00:00:00');
    if (isNaN(d)) return null;
    const day = d.getDate();
    d.setMonth(d.getMonth() + n);
    if (d.getDate() < day) d.setDate(0); // ajuste de meses con menos días
    return d;
}
function wzVencToggle() {
    const tipo = document.getElementById('wz_tipo_contrato');
    const wrap = document.getElementById('wz_venc_wrap');
    const venc = document.getElementById('wz_fecha_venc');
    if (!wrap || !venc) return;
    const esFijo = tipo && tipo.value === 'Fijo';
    const req = document.getElementById('wz_venc_req');
    wrap.style.display = esFijo ? 'none' : '';
    if (esFijo) {
        venc.value = ''; venc.removeAttribute('required'); venc.setCustomValidity('');
        if (req) req.style.display = 'none';
    } else {
        venc.setAttribute('required', '');   // obligatorio para Contratados
        if (req) req.style.display = '';
    }
    wzVencCalc();
}
function wzVencCalc() {
    const ing  = document.getElementById('wz_fecha_ingreso');
    const venc = document.getElementById('wz_fecha_venc');
    const info = document.getElementById('wz_venc_info');
    const tipo = document.getElementById('wz_tipo_contrato');
    if (!venc || !info) return;
    if (tipo && tipo.value === 'Fijo') { venc.setCustomValidity(''); return; }

    let minDate = null;
    if (ing && ing.value) { minDate = wzAddMonths(ing.value, 3); if (minDate) venc.setAttribute('min', wzFmtISO(minDate)); }

    if (!venc.value) {
        info.style.color = 'var(--text-tertiary)';
        info.textContent = 'Mínimo 3 meses desde el ingreso (opcional).';
        venc.setCustomValidity('');
    } else if (!ing || !ing.value) {
        info.style.color = 'var(--danger, #dc2626)';
        info.textContent = 'Indique primero la fecha de ingreso.';
        venc.setCustomValidity('Indique la fecha de ingreso.');
    } else {
        const d1 = new Date(ing.value + 'T00:00:00'), d2 = new Date(venc.value + 'T00:00:00');
        const dias = Math.round((d2 - d1) / 86400000);
        if (d2 <= d1) {
            info.style.color = 'var(--danger, #dc2626)';
            info.textContent = 'El vencimiento debe ser posterior a la fecha de ingreso.';
            venc.setCustomValidity('Vencimiento posterior al ingreso.');
        } else if (minDate && d2 < minDate) {
            info.style.color = 'var(--danger, #dc2626)';
            info.textContent = `El contrato debe durar al menos 3 meses (vence desde el ${wzFmtISO(minDate)}).`;
            venc.setCustomValidity('Mínimo 3 meses de contrato.');
        } else {
            info.style.color = 'var(--success, #16a34a)';
            info.innerHTML = `<i class="bi bi-check-circle"></i> Contrato vigente por <strong>${dias} días</strong> (~${Math.floor(dias / 30)} meses).`;
            venc.setCustomValidity('');
        }
    }
    if (typeof window.sigturRefreshButtons === 'function') window.sigturRefreshButtons();
}

// Carga familiar dinámica (solo alta)
function cfAddRow(d) {
    d = d || {};
    const wrap = document.getElementById('cfRows');
    if (!wrap) return;
    const row = document.createElement('div');
    row.className = 'row g-2 align-items-end';
    row.style.marginBottom = '8px';
    row.innerHTML = `
        <div class="col-md-4"><input type="text" name="cf_nombre[]" class="sig-input" placeholder="Nombre y apellido"></div>
        <div class="col-md-3"><input type="text" name="cf_cedula[]" class="sig-input" placeholder="Cédula"></div>
        <div class="col-md-2"><input type="date" name="cf_fnac[]" class="sig-input js-edad"></div>
        <div class="col-md-2"><select name="cf_parentesco[]" class="sig-select">
            <option value="">Parentesco</option>
            <?php foreach (CargaFamiliar::PARENTESCOS as $p): ?><option value="<?php echo $p; ?>"><?php echo $p; ?></option><?php endforeach; ?>
        </select></div>
        <div class="col-md-1"><button type="button" class="btn-sig btn-sig--ghost btn-sig--sm" onclick="this.closest('.row').remove()"><i class="bi bi-x"></i></button></div>`;
    wrap.appendChild(row);
    if (typeof initSigturValidations === 'function') initSigturValidations(); // conecta edad/cédula de la fila nueva
}

// Resumen
function wzBuildResumen() {
    const cont = document.getElementById('wzResumen');
    const get = n => { const el = wzForm.querySelector(`[name="${n}"]`); if (!el) return ''; if (el.type === 'checkbox') return el.checked ? 'Sí' : 'No'; if (el.tagName === 'SELECT') return el.options[el.selectedIndex] ? el.options[el.selectedIndex].text : ''; return el.value; };
    const items = [
        ['Nombre completo', get('nombre') + ' ' + get('apellido')],
        ['Cédula', get('cedula')], ['RIF', get('rif')],
        ['Cargo', get('id_cargo')], ['Departamento', get('id_departamento')],
        ['Tipo de contrato', get('tipo_contrato')], ['Clasificación', get('clasificacion')],
        ['Institución/Nómina', get('institucion_origen')], ['Comisión de servicio', (get('institucion_origen') && get('institucion_origen') !== 'IMATUR') ? 'Sí' : 'No'],
        ['Horario', get('id_horario')], ['Grupo', get('grupo_rotacion')],
        ['Expediente', get('nro_expediente')], ['Fecha de ingreso', get('fecha_ingreso')],
    ];
    cont.innerHTML = items.map(([k, v]) => `<div class="col-md-4"><div style="font-size:11px;color:var(--text-tertiary);text-transform:uppercase">${k}</div><div style="font-weight:600">${(v && v.trim()) ? v : '—'}</div></div>`).join('');
}

// localStorage (solo alta)
function wzSave() {
    if (isEdit) return;
    const data = {};
    wzForm.querySelectorAll('input, select, textarea').forEach(el => {
        if (!el.name || el.name.endsWith('[]') || el.name === '_token') return;
        data[el.name] = el.type === 'checkbox' ? el.checked : el.value;
    });
    try { localStorage.setItem(LS_KEY, JSON.stringify(data)); } catch (e) {}
}
function wzRestore() {
    if (isEdit) return;
    let data; try { data = JSON.parse(localStorage.getItem(LS_KEY) || '{}'); } catch (e) { return; }
    Object.keys(data).forEach(n => {
        const el = wzForm.querySelector(`[name="${n}"]`);
        if (!el) return;
        if (el.type === 'checkbox') el.checked = !!data[n]; else el.value = data[n];
    });
}

document.addEventListener('DOMContentLoaded', () => {
    wzRestore();
    wzToggleDisc(); wzOrigenCambio(); wzToggleUniforme(); wzVencToggle();
    const cedInp = document.getElementById('emp_cedula');
    if (cedInp) { cedInp.addEventListener('input', wzCheckCedula); cedInp.addEventListener('blur', wzCheckCedula); }
    wzShow(0);
    wzForm.addEventListener('input', wzSave);
    wzForm.addEventListener('input', wzUpdateNav);
    wzForm.addEventListener('change', wzUpdateNav);
    wzForm.addEventListener('submit', () => { try { localStorage.removeItem(LS_KEY); } catch (e) {} });
});
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
