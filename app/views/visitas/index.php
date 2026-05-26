<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <h1 class="page__title"><?php echo $data['titulo'] ?? ''; ?></h1>
        <p class="page__subtitle">Control de flujo y marcaje de entrada/salida de personas ajenas a la institución.</p>
    </div>
</div>

<div class="row g-4 mb-8 anim-slide-up">

    <!-- ── TABLA DE MOVIMIENTOS ──────────────────────────────────────────── -->
    <div class="col-md-7 order-md-1">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title">Movimientos Recientes</div>
            </div>
            <div class="sig-table-wrap">
                <table class="sig-table">
                    <thead>
                        <tr>
                            <th>Visitante</th>
                            <th>Visita a / Motivo</th>
                            <th style="text-align:center;">Entrada</th>
                            <th style="text-align:center;">Salida</th>
                            <th class="col-actions">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['visitas'])): ?>
                            <tr>
                                <td colspan="5" class="sig-table-empty">Sin movimientos registrados.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data['visitas'] as $v): ?>
                                <tr>
                                    <td>
                                        <div style="display:flex; flex-direction:column;">
                                            <span class="cell-strong"><?php echo htmlspecialchars($v->vis_nombre . ' ' . $v->vis_apellido); ?></span>
                                            <span class="cell-id">CI: <?php echo htmlspecialchars($v->vis_cedula ?? '—'); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($v->emp_nombre): ?>
                                            <div style="font-size:12px; font-weight:600; color:var(--text-secondary);"><?php echo htmlspecialchars($v->emp_nombre . ' ' . $v->emp_apellido); ?></div>
                                        <?php endif; ?>
                                        <div style="font-size:11px; color:var(--text-tertiary);"><?php echo htmlspecialchars($v->motivo ?? '—'); ?></div>
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="sig-badge sig-badge--success" style="font-weight:700; font-family:var(--font-mono);">
                                            <?php echo date('d/m H:i', strtotime($v->hora_entrada)); ?>
                                        </span>
                                    </td>
                                    <td style="text-align:center;">
                                        <?php if ($v->hora_salida): ?>
                                            <span class="sig-badge sig-badge--danger" style="font-weight:700; font-family:var(--font-mono);">
                                                <?php echo date('d/m H:i', strtotime($v->hora_salida)); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="sig-badge sig-badge--neutral" style="opacity:0.5;">--:--</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="col-actions">
                                        <a href="<?php echo URL_ROOT; ?>/visitas/delete/<?php echo $v->id; ?>"
                                           class="row-action row-action--del delete-btn">
                                            <i class="bi bi-trash"></i>
                                        </a>
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
    <div class="col-md-5 order-md-2">
        <div class="sig-card" style="border-top: 4px solid var(--brand-500);">
            <div class="sig-card__head">
                <div class="sig-card__title">
                    <i class="bi bi-clock-history" style="color:var(--brand-500);"></i> Registro de Marcaje
                </div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-5);">
                <form action="<?php echo URL_ROOT; ?>/visitas/registrar" method="POST" id="formMarcaje">

                    <!-- Búsqueda por cédula -->
                    <div style="margin-bottom:var(--sp-4);">
                        <label class="sig-field__label" style="margin-bottom:6px;">Cédula del visitante</label>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <input type="text" id="vis_cedula_busq" name="cedula"
                                   class="sig-input" placeholder="Ej: 12345678"
                                   style="flex:1;" autocomplete="off" inputmode="numeric">
                            <button type="button" id="btn_buscar_vis"
                                    class="btn-sig btn-sig--ghost btn-sig--sm" style="white-space:nowrap;"
                                    onclick="buscarVisitante()">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                        <div id="vis_status" style="margin-top:6px; min-height:22px;"></div>
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
                                <input type="text" name="procedencia" id="vis_procedencia" class="sig-input" placeholder="Ej: Alcaldía de Sucre">
                            </div>
                            <div class="sig-field">
                                <label class="sig-field__label">Teléfono</label>
                                <input type="text" name="telefono" id="vis_telefono" class="sig-input" placeholder="04XX-XXXXXXX">
                            </div>
                        </div>

                        <div style="margin-bottom:var(--sp-3);">
                            <div class="sig-field">
                                <label class="sig-field__label">Género</label>
                                <select name="genero" id="vis_genero" class="sig-select">
                                    <option value="">Sin especificar</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Datos de la visita -->
                    <div id="bloque_visita" style="display:none; border-top:1px solid var(--border-subtle); padding-top:var(--sp-4);">
                        <div class="sig-field" style="margin-bottom:var(--sp-3);">
                            <label class="sig-field__label">Motivo de la visita</label>
                            <select name="motivo" id="vis_motivo" class="sig-select">
                                <option value="">Seleccione un motivo...</option>
                                <option value="Reunión de trabajo">Reunión de trabajo</option>
                                <option value="Trámite administrativo">Trámite administrativo</option>
                                <option value="Entrega de documentos">Entrega de documentos</option>
                                <option value="Visita institucional">Visita institucional</option>
                                <option value="Pasantías">Pasantías</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>

                        <div class="sig-field" style="margin-bottom:var(--sp-4);">
                            <label class="sig-field__label">Empleado a visitar</label>
                            <select name="id_empleado" id="vis_empleado" class="sig-select">
                                <option value="">Sin asignar / Trámite general</option>
                                <?php foreach ($data['empleados'] ?? [] as $e): ?>
                                    <option value="<?php echo $e->id; ?>">
                                        <?php echo htmlspecialchars(($e->nombre ?? '') . ' ' . ($e->apellido ?? '')); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Hora automática (display only) -->
                        <div style="background:var(--bg-muted-subtle); border-radius:8px; padding:var(--sp-3); display:flex; align-items:center; gap:var(--sp-3); margin-bottom:var(--sp-4);">
                            <i class="bi bi-clock" style="font-size:20px; color:var(--brand-500);"></i>
                            <div>
                                <div style="font-size:11px; color:var(--text-tertiary); text-transform:uppercase; letter-spacing:.05em;">Fecha y hora del marcaje</div>
                                <div id="reloj_marcaje" style="font-size:16px; font-weight:700; font-family:var(--font-mono); color:var(--text-primary);"></div>
                            </div>
                        </div>

                        <button type="submit" id="btn_procesar" class="btn-sig btn-sig--primary"
                                style="width:100%; height:48px; font-size:16px;" disabled>
                            <i class="bi bi-check-circle"></i> Registrar visita
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
const URL_ROOT = '<?php echo URL_ROOT; ?>';

// ── Reloj en tiempo real ─────────────────────────────────────────────────────
(function tickReloj() {
    const el = document.getElementById('reloj_marcaje');
    if (!el) return;
    const now = new Date();
    const pad = n => String(n).padStart(2, '0');
    const dias = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
    el.textContent = dias[now.getDay()] + ' ' +
        pad(now.getDate()) + '/' + pad(now.getMonth() + 1) + '/' + now.getFullYear() +
        '  ' + pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
    setTimeout(tickReloj, 1000);
})();

// ── AJAX búsqueda de visitante ───────────────────────────────────────────────
let visitanteEncontrado = false;

function mostrarStatus(tipo, msg) {
    const el = document.getElementById('vis_status');
    const colores = { success: 'var(--success-500)', danger: 'var(--danger-500)', warning: 'var(--warning-500)', info: 'var(--brand-500)' };
    el.innerHTML = msg
        ? `<span style="font-size:12px; color:${colores[tipo] || 'inherit'};">${msg}</span>`
        : '';
}

function setVisitanteReadonly(readonly) {
    ['vis_nombre','vis_apellido','vis_procedencia','vis_telefono'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.readOnly = readonly;
    });
    const gen = document.getElementById('vis_genero');
    if (gen) gen.disabled = readonly;
}

function resetBloqueVisitante() {
    ['vis_nombre','vis_apellido','vis_procedencia','vis_telefono'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    const gen = document.getElementById('vis_genero');
    if (gen) gen.value = '';
    setVisitanteReadonly(false);
    visitanteEncontrado = false;
}

function mostrarBloques() {
    document.getElementById('bloque_visitante').style.display = 'block';
    document.getElementById('bloque_visita').style.display    = 'block';
    checkMarcajeValid();
}

function buscarVisitante() {
    const cedula = document.getElementById('vis_cedula_busq').value.trim();
    if (!cedula) {
        resetBloqueVisitante();
        mostrarStatus('info', '<i class="bi bi-info-circle"></i> Sin cédula — complete los datos manualmente.');
        mostrarBloques();
        return;
    }

    mostrarStatus('info', '<i class="bi bi-hourglass-split"></i> Buscando...');

    fetch(URL_ROOT + '/visitas/buscarVisitante?cedula=' + encodeURIComponent(cedula))
        .then(r => r.json())
        .then(json => {
            resetBloqueVisitante();
            if (json.found) {
                const v = json.visitante;
                document.getElementById('vis_nombre').value      = v.nombre;
                document.getElementById('vis_apellido').value    = v.apellido;
                document.getElementById('vis_procedencia').value = v.procedencia;
                document.getElementById('vis_telefono').value    = v.telefono;
                document.getElementById('vis_genero').value      = v.genero;
                setVisitanteReadonly(true);
                visitanteEncontrado = true;
                mostrarStatus('success', '<i class="bi bi-check-circle-fill"></i> Visitante encontrado: <strong>' + v.nombre + ' ' + v.apellido + '</strong>');
            } else {
                mostrarStatus('warning', '<i class="bi bi-exclamation-triangle"></i> No encontrado — complete los datos para registrar.');
            }
            mostrarBloques();
        })
        .catch(() => {
            mostrarStatus('danger', '<i class="bi bi-x-circle"></i> Error al consultar. Intente de nuevo.');
        });
}

// Buscar al presionar Enter en el campo cédula
document.getElementById('vis_cedula_busq').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); buscarVisitante(); }
});

// ── Validación del botón procesar ────────────────────────────────────────────
function checkMarcajeValid() {
    const nombre   = (document.getElementById('vis_nombre')?.value   || '').trim();
    const apellido = (document.getElementById('vis_apellido')?.value || '').trim();
    const btn = document.getElementById('btn_procesar');
    if (btn) btn.disabled = !(nombre && apellido);
}

document.getElementById('vis_nombre')?.addEventListener('input', checkMarcajeValid);
document.getElementById('vis_apellido')?.addEventListener('input', checkMarcajeValid);
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
