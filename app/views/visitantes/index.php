<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Recepción'; ?></h1>
        <p class="page__subtitle">Control de entrada de visitantes.</p>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary"
                data-bs-toggle="modal" data-bs-target="#modalMarcaje"
                onclick="abrirModalMarcaje()">
            <i class="bi bi-person-plus"></i> Registrar Visitante
        </button>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="10">
    <table class="sig-table">
        <thead>
            <tr>
                <th style="text-align:center;">Entrada</th>
                <th>Cédula</th>
                <th>Nombre y Apellido</th>
                <th>Teléfono</th>
                <th>Institución / Procedencia</th>
                <th>Correo</th>
                <th>Motivo</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['movimientos'])): ?>
                <tr>
                    <td colspan="7" class="sig-table-empty">Sin movimientos registrados hoy.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($data['movimientos'] as $v): ?>
                    <tr>
                        <td style="text-align:center;">
                            <span class="sig-badge sig-badge--success" style="font-family:var(--font-mono); font-size:11px; font-weight:700;">
                                <?php echo date('d/m H:i', strtotime($v->hora_entrada)); ?>
                            </span>
                        </td>
                        <td class="cell-id"><?php echo htmlspecialchars($v->vis_cedula ?? '—'); ?></td>
                        <td>
                            <span class="cell-strong"><?php echo htmlspecialchars($v->vis_nombre . ' ' . $v->vis_apellido); ?></span>
                        </td>
                        <td style="font-size:13px; color:var(--text-secondary);">
                            <?php echo htmlspecialchars($v->vis_telefono ?? '—'); ?>
                        </td>
                        <td style="font-size:13px; color:var(--text-secondary);">
                            <?php echo htmlspecialchars($v->procedencia ?? '—'); ?>
                        </td>
                        <td style="font-size:13px; color:var(--text-secondary);">
                            <?php echo htmlspecialchars($v->vis_correo ?? '—'); ?>
                        </td>
                        <td style="font-size:13px; color:var(--text-secondary);">
                            <?php echo htmlspecialchars($v->motivo ?? '—'); ?>
                            <?php if (!empty($v->emp_nombre)): ?>
                                <div style="font-size:11px; color:var(--text-tertiary);">
                                    → <?php echo htmlspecialchars($v->emp_nombre . ' ' . $v->emp_apellido); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ── Modal Registro de Visita ──────────────────────────────────────────── -->
<div class="modal fade" id="modalMarcaje" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo URL_ROOT; ?>/visitantes/registrar" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Marcaje</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <!-- Búsqueda por cédula -->
                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <div class="sig-field">
                            <label class="sig-field__label">Cédula del visitante</label>
                            <div style="display:flex; gap:8px;">
                                <input type="text" id="m_cedula" name="cedula"
                                       class="sig-input" placeholder="Ingrese la cédula y presione Buscar"
                                       autocomplete="off" inputmode="numeric" style="flex:1;">
                                <button type="button" class="btn-sig btn-sig--ghost btn-sig--sm"
                                        style="white-space:nowrap;" onclick="buscarVisitanteModal()">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4" style="display:flex; align-items:flex-end;">
                        <div id="m_status" style="font-size:12px; padding-bottom:6px;"></div>
                    </div>
                </div>

                <!-- Datos personales del visitante -->
                <div style="border-top:1px solid var(--border-subtle); padding-top:var(--sp-4); margin-bottom:var(--sp-4);">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="sig-field">
                                <label class="sig-field__label">Nombre <span class="req">*</span></label>
                                <input type="text" name="nombre" id="m_nombre" class="sig-input" required placeholder="Nombre">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="sig-field">
                                <label class="sig-field__label">Apellido <span class="req">*</span></label>
                                <input type="text" name="apellido" id="m_apellido" class="sig-input" required placeholder="Apellido">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="sig-field">
                                <label class="sig-field__label">Institución / Procedencia</label>
                                <input type="text" name="procedencia" id="m_procedencia" class="sig-input" placeholder="Institución que representa">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="sig-field">
                                <label class="sig-field__label">Teléfono</label>
                                <input type="text" name="telefono" id="m_telefono" class="sig-input" placeholder="04XX-XXXXXXX">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="sig-field">
                                <label class="sig-field__label">Correo electrónico</label>
                                <input type="email" name="correo" id="m_correo" class="sig-input" placeholder="correo@ejemplo.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="sig-field">
                                <label class="sig-field__label">Género</label>
                                <select name="genero" id="m_genero" class="sig-select">
                                    <option value="">Sin especificar</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Motivo de la visita -->
                <div style="border-top:1px solid var(--border-subtle); padding-top:var(--sp-4);">
                    <div class="sig-field">
                        <label class="sig-field__label">Motivo de la visita</label>
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
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" id="btn_guardar_marcaje" class="btn-sig btn-sig--primary" disabled>
                    <i class="bi bi-check-circle"></i> Procesar Marcaje
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const URL_ROOT = '<?php echo URL_ROOT; ?>';

function abrirModalMarcaje() {
    resetModal();
}

function resetModal() {
    ['m_cedula','m_nombre','m_apellido','m_procedencia','m_telefono','m_correo'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    const gen = document.getElementById('m_genero');
    if (gen) gen.value = '';
    document.getElementById('m_status').innerHTML = '';
    setModalReadonly(false);
    checkModalValid();
}

// Reset form when modal is closed
document.getElementById('modalMarcaje').addEventListener('hidden.bs.modal', resetModal);

function setModalReadonly(readonly) {
    ['m_nombre','m_apellido','m_procedencia','m_telefono','m_correo'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.readOnly = readonly;
    });
    const gen = document.getElementById('m_genero');
    if (gen) gen.disabled = readonly;
}

function mostrarStatus(tipo, msg) {
    const colores = { success:'var(--success-500)', warning:'var(--warning-600)', danger:'var(--danger-500)', info:'var(--brand-500)' };
    document.getElementById('m_status').innerHTML = msg
        ? `<span style="color:${colores[tipo]||'inherit'}">${msg}</span>` : '';
}

function buscarVisitanteModal() {
    const cedula = document.getElementById('m_cedula').value.trim();
    if (!cedula) {
        mostrarStatus('info', '<i class="bi bi-info-circle"></i> Sin cédula');
        setModalReadonly(false);
        return;
    }
    mostrarStatus('info', '<i class="bi bi-hourglass-split"></i> Buscando...');
    fetch(URL_ROOT + '/visitantes/buscarVisitante?cedula=' + encodeURIComponent(cedula))
        .then(r => r.json())
        .then(json => {
            // Always clear fields before filling
            ['m_nombre','m_apellido','m_procedencia','m_telefono','m_correo'].forEach(id => {
                document.getElementById(id).value = '';
            });
            document.getElementById('m_genero').value = '';

            if (json.found) {
                const v = json.visitante;
                document.getElementById('m_nombre').value      = v.nombre;
                document.getElementById('m_apellido').value    = v.apellido;
                document.getElementById('m_procedencia').value = v.procedencia;
                document.getElementById('m_telefono').value    = v.telefono;
                document.getElementById('m_correo').value      = v.correo;
                document.getElementById('m_genero').value      = v.genero;
                setModalReadonly(true);
                mostrarStatus('success', '<i class="bi bi-check-circle-fill"></i> ' + v.nombre + ' ' + v.apellido);
            } else {
                setModalReadonly(false);
                mostrarStatus('warning', '<i class="bi bi-exclamation-triangle"></i> No registrado');
            }
            checkModalValid();
        })
        .catch(() => mostrarStatus('danger', '<i class="bi bi-x-circle"></i> Error'));
}

// Search on Enter key in cédula field
document.getElementById('m_cedula').addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); buscarVisitanteModal(); }
});

function checkModalValid() {
    const n = (document.getElementById('m_nombre')?.value   || '').trim();
    const a = (document.getElementById('m_apellido')?.value || '').trim();
    const btn = document.getElementById('btn_guardar_marcaje');
    if (btn) btn.disabled = !(n && a);
}

document.getElementById('m_nombre')?.addEventListener('input', checkModalValid);
document.getElementById('m_apellido')?.addEventListener('input', checkModalValid);
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
