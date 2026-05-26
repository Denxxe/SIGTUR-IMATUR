<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Seguridad · Control de Accesos</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? ''; ?></h1>
        <p class="page__subtitle">Control de flujo y marcaje de entrada/salida de personas ajenas a la institución.</p>
    </div>
</div>

<div class="row g-4 mb-8 anim-slide-up">

    <!-- ── TABLA DE MOVIMIENTOS ──────────────────────────────────────────── -->
    <div class="col-lg-7 order-lg-1">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title">Movimientos Recientes</div>
            </div>
            <div class="sig-table-wrap">
                <table class="sig-table">
                    <thead>
                        <tr>
                            <th>Visitante</th>
                            <th>Correo / Institución</th>
                            <th>Motivo</th>
                            <th style="text-align:center;">Entrada</th>
                            <th style="text-align:center;">Salida</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['movimientos'])): ?>
                            <tr>
                                <td colspan="5" class="sig-table-empty">Sin movimientos registrados.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data['movimientos'] as $v): ?>
                                <tr>
                                    <td>
                                        <div class="cell-strong"><?php echo htmlspecialchars($v->vis_nombre . ' ' . $v->vis_apellido); ?></div>
                                        <?php if ($v->vis_cedula): ?>
                                            <div class="cell-id">CI: <?php echo htmlspecialchars($v->vis_cedula); ?></div>
                                        <?php endif; ?>
                                        <?php if ($v->procedencia): ?>
                                            <div style="font-size:11px; color:var(--text-tertiary);"><?php echo htmlspecialchars($v->procedencia); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($v->vis_correo): ?>
                                            <div style="font-size:12px; color:var(--text-secondary);"><?php echo htmlspecialchars($v->vis_correo); ?></div>
                                        <?php endif; ?>
                                        <?php if ($v->emp_nombre): ?>
                                            <div style="font-size:11px; color:var(--text-tertiary);">Visita a: <?php echo htmlspecialchars($v->emp_nombre . ' ' . $v->emp_apellido); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="font-size:12px; color:var(--text-secondary);"><?php echo htmlspecialchars($v->motivo ?? '—'); ?></div>
                                    </td>
                                    <td style="text-align:center; white-space:nowrap;">
                                        <span class="sig-badge sig-badge--success" style="font-weight:700; font-family:var(--font-mono); font-size:11px;">
                                            <?php echo date('d/m H:i', strtotime($v->hora_entrada)); ?>
                                        </span>
                                    </td>
                                    <td style="text-align:center; white-space:nowrap;">
                                        <?php if ($v->hora_salida): ?>
                                            <span class="sig-badge sig-badge--danger" style="font-weight:700; font-family:var(--font-mono); font-size:11px;">
                                                <?php echo date('d/m H:i', strtotime($v->hora_salida)); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="sig-badge sig-badge--neutral" style="opacity:0.5; font-size:11px;">adentro</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ── FORMULARIO DE REGISTRO UNIFICADO ─────────────────────────────── -->
    <div class="col-lg-5 order-lg-2">
        <div class="sig-card" style="border-top: 4px solid var(--brand-500);">
            <div class="sig-card__head">
                <div class="sig-card__title">
                    <i class="bi bi-clock-history" style="color:var(--brand-500);"></i> Registro de Marcaje
                </div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-5);">
                <form action="<?php echo URL_ROOT; ?>/visitantes/registrar" method="POST" id="formMarcaje">

                    <!-- Búsqueda por cédula -->
                    <div style="margin-bottom:var(--sp-4);">
                        <label class="sig-field__label" style="margin-bottom:6px;">Cédula del visitante</label>
                        <div style="display:flex; gap:8px;">
                            <input type="text" id="vis_cedula_busq" name="cedula"
                                   class="sig-input" placeholder="Ej: 12345678"
                                   style="flex:1;" autocomplete="off" inputmode="numeric">
                            <button type="button" class="btn-sig btn-sig--ghost btn-sig--sm"
                                    style="white-space:nowrap;" onclick="buscarVisitante()">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                        <div id="vis_status" style="margin-top:6px; min-height:20px;"></div>
                    </div>

                    <!-- Datos del visitante -->
                    <div id="bloque_visitante" style="display:none; border-top:1px solid var(--border-subtle); padding-top:var(--sp-4); margin-bottom:var(--sp-4);">

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-3); margin-bottom:var(--sp-3);">
                            <div class="sig-field">
                                <label class="sig-field__label">Nombre <span class="req">*</span></label>
                                <input type="text" name="nombre" id="vis_nombre" class="sig-input" placeholder="Nombre" required>
                            </div>
                            <div class="sig-field">
                                <label class="sig-field__label">Apellido <span class="req">*</span></label>
                                <input type="text" name="apellido" id="vis_apellido" class="sig-input" placeholder="Apellido" required>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-3); margin-bottom:var(--sp-3);">
                            <div class="sig-field">
                                <label class="sig-field__label">Institución / Procedencia</label>
                                <input type="text" name="procedencia" id="vis_procedencia" class="sig-input" placeholder="Ej: Alcaldía">
                            </div>
                            <div class="sig-field">
                                <label class="sig-field__label">Teléfono</label>
                                <input type="text" name="telefono" id="vis_telefono" class="sig-input" placeholder="04XX-XXXXXXX">
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-3); margin-bottom:var(--sp-4);">
                            <div class="sig-field">
                                <label class="sig-field__label">Correo</label>
                                <input type="email" name="correo" id="vis_correo" class="sig-input" placeholder="correo@ejemplo.com">
                            </div>
                            <div class="sig-field">
                                <label class="sig-field__label">Género</label>
                                <select name="genero" id="vis_genero" class="sig-select">
                                    <option value="">Sin especificar</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                    <option value="O">Otro</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Datos de la visita -->
                    <div id="bloque_visita" style="display:none; border-top:1px solid var(--border-subtle); padding-top:var(--sp-4);">

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-3); margin-bottom:var(--sp-4);">
                            <div class="sig-field">
                                <label class="sig-field__label">Motivo</label>
                                <select name="motivo" class="sig-select">
                                    <option value="">Sin especificar</option>
                                    <option value="Reunión de trabajo">Reunión de trabajo</option>
                                    <option value="Trámite administrativo">Trámite administrativo</option>
                                    <option value="Entrega de documentos">Entrega de documentos</option>
                                    <option value="Visita institucional">Visita institucional</option>
                                    <option value="Pasantías">Pasantías</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div class="sig-field">
                                <label class="sig-field__label">Empleado a visitar</label>
                                <select name="id_empleado" class="sig-select">
                                    <option value="">Sin asignar</option>
                                    <?php foreach ($data['empleados'] ?? [] as $e): ?>
                                        <option value="<?php echo $e->id; ?>">
                                            <?php echo htmlspecialchars(($e->nombre ?? '') . ' ' . ($e->apellido ?? '')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Reloj automático -->
                        <div style="background:var(--bg-muted-subtle); border-radius:8px; padding:var(--sp-3); display:flex; align-items:center; gap:var(--sp-3); margin-bottom:var(--sp-4);">
                            <i class="bi bi-clock" style="font-size:20px; color:var(--brand-500);"></i>
                            <div>
                                <div style="font-size:10px; color:var(--text-tertiary); text-transform:uppercase; letter-spacing:.05em;">Fecha y hora del marcaje</div>
                                <div id="reloj_marcaje" style="font-size:15px; font-weight:700; font-family:var(--font-mono); color:var(--text-primary);"></div>
                            </div>
                        </div>

                        <button type="submit" id="btn_procesar"
                                class="btn-sig btn-sig--primary"
                                style="width:100%; height:48px; font-size:16px;" disabled>
                            <i class="bi bi-check-circle"></i> PROCESAR MARCAJE
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
const URL_ROOT = '<?php echo URL_ROOT; ?>';

// Reloj en tiempo real
(function tick() {
    const el = document.getElementById('reloj_marcaje');
    if (!el) return;
    const n = new Date(), p = x => String(x).padStart(2,'0');
    const dias = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
    el.textContent = dias[n.getDay()] + ' ' + p(n.getDate()) + '/' + p(n.getMonth()+1) + '/' + n.getFullYear()
                   + '  ' + p(n.getHours()) + ':' + p(n.getMinutes()) + ':' + p(n.getSeconds());
    setTimeout(tick, 1000);
})();

// ── AJAX búsqueda ────────────────────────────────────────────────────────────

function mostrarStatus(tipo, msg) {
    const colores = { success:'var(--success-500)', danger:'var(--danger-500)', warning:'var(--warning-600)', info:'var(--brand-500)' };
    document.getElementById('vis_status').innerHTML = msg
        ? `<span style="font-size:12px;color:${colores[tipo]||'inherit'}">${msg}</span>` : '';
}

function setReadonly(readonly) {
    ['vis_nombre','vis_apellido','vis_procedencia','vis_telefono','vis_correo'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.readOnly = readonly;
    });
    const gen = document.getElementById('vis_genero');
    if (gen) gen.disabled = readonly;
}

function resetBloque() {
    ['vis_nombre','vis_apellido','vis_procedencia','vis_telefono','vis_correo'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    const gen = document.getElementById('vis_genero');
    if (gen) gen.value = '';
    setReadonly(false);
}

function mostrarBloques() {
    document.getElementById('bloque_visitante').style.display = 'block';
    document.getElementById('bloque_visita').style.display    = 'block';
    checkValid();
}

function buscarVisitante() {
    const cedula = document.getElementById('vis_cedula_busq').value.trim();
    if (!cedula) {
        resetBloque();
        mostrarStatus('info', '<i class="bi bi-info-circle"></i> Sin cédula — complete los datos manualmente.');
        mostrarBloques();
        return;
    }
    mostrarStatus('info', '<i class="bi bi-hourglass-split"></i> Buscando...');
    fetch(URL_ROOT + '/visitantes/buscarVisitante?cedula=' + encodeURIComponent(cedula))
        .then(r => r.json())
        .then(json => {
            resetBloque();
            if (json.found) {
                const v = json.visitante;
                document.getElementById('vis_nombre').value      = v.nombre;
                document.getElementById('vis_apellido').value    = v.apellido;
                document.getElementById('vis_procedencia').value = v.procedencia;
                document.getElementById('vis_telefono').value    = v.telefono;
                document.getElementById('vis_correo').value      = v.correo;
                document.getElementById('vis_genero').value      = v.genero;
                setReadonly(true);
                mostrarStatus('success', '<i class="bi bi-check-circle-fill"></i> <strong>' + v.nombre + ' ' + v.apellido + '</strong> — encontrado');
            } else {
                mostrarStatus('warning', '<i class="bi bi-exclamation-triangle"></i> No registrado — complete los datos.');
            }
            mostrarBloques();
        })
        .catch(() => mostrarStatus('danger', '<i class="bi bi-x-circle"></i> Error de conexión. Intente de nuevo.'));
}

document.getElementById('vis_cedula_busq').addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); buscarVisitante(); }
});

function checkValid() {
    const n = (document.getElementById('vis_nombre')?.value   || '').trim();
    const a = (document.getElementById('vis_apellido')?.value || '').trim();
    const btn = document.getElementById('btn_procesar');
    if (btn) btn.disabled = !(n && a);
}

document.getElementById('vis_nombre')?.addEventListener('input', checkValid);
document.getElementById('vis_apellido')?.addEventListener('input', checkValid);
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
