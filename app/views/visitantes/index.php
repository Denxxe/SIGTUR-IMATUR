<?php require_once '../app/views/inc/header.php';
$flt          = $data['filtros'] ?? ['buscar'=>'','fecha_desde'=>'','fecha_hasta'=>''];
$pagina       = (int)($data['pagina'] ?? 1);
$totalPaginas = (int)($data['total_paginas'] ?? 1);
$totalReg     = (int)($data['total'] ?? 0);
$porPagina    = (int)($data['por_pagina'] ?? 12);
$hayFiltro    = array_filter($flt, fn($v) => $v !== '');
function visUrl(array $f, int $p): string {
    $q = array_filter($f, fn($v) => $v !== '' && $v !== null);
    $q['p'] = $p;
    return URL_ROOT . '/visitantes/index?' . http_build_query($q);
}
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Recepción'; ?></h1>
        <p class="page__subtitle">Control de entrada de visitantes — historial completo con búsqueda.</p>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary"
                data-bs-toggle="modal" data-bs-target="#modalMarcaje"
                onclick="abrirModalMarcaje()">
            <i class="bi bi-person-plus"></i> Registrar Visitante
        </button>
    </div>
</div>

<!-- Filtros (servidor) -->
<form method="GET" action="<?php echo URL_ROOT; ?>/visitantes/index" class="anim-slide-up" style="display:flex;gap:var(--sp-2);align-items:flex-end;margin-bottom:var(--sp-4);flex-wrap:wrap;">
    <div class="tabla-search" style="flex:0 0 auto;">
        <i class="bi bi-search"></i>
        <input type="text" name="buscar" class="sig-input" style="padding-left:32px;min-width:220px;" placeholder="Cédula, nombre o procedencia…" value="<?php echo htmlspecialchars($flt['buscar'] ?? ''); ?>">
    </div>
    <input type="date" name="fecha_desde" class="sig-input" style="max-width:150px;" title="Desde" value="<?php echo htmlspecialchars($flt['fecha_desde'] ?? ''); ?>">
    <input type="date" name="fecha_hasta" class="sig-input" style="max-width:150px;" title="Hasta" value="<?php echo htmlspecialchars($flt['fecha_hasta'] ?? ''); ?>">
    <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-funnel"></i> Filtrar</button>
    <?php if ($hayFiltro): ?>
        <a href="<?php echo URL_ROOT; ?>/visitantes/index" class="btn-sig btn-sig--ghost" title="Limpiar"><i class="bi bi-x-lg"></i></a>
    <?php endif; ?>
</form>

<div class="sig-table-wrap anim-slide-up">
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
                <th class="col-actions">Detalles</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['movimientos'])): ?>
                <tr>
                    <td colspan="8" class="sig-table-empty"><?php echo $hayFiltro ? 'Sin visitas para el filtro aplicado.' : 'Sin visitas registradas.'; ?></td>
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
                        <td class="col-actions">
                            <button type="button" class="row-action row-action--view" data-bs-toggle="modal" data-bs-target="#detVis<?php echo $v->id; ?>" title="Ver detalles">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if ($totalReg > 0): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;gap:var(--sp-3);padding:12px 16px;border-top:1px solid var(--border-subtle);flex-wrap:wrap;">
        <span class="tabla-count">Mostrando <?php echo (($pagina-1)*$porPagina)+1; ?>–<?php echo min($totalReg, $pagina*$porPagina); ?> de <?php echo number_format($totalReg); ?></span>
        <?php if ($totalPaginas > 1): ?>
        <div style="display:flex;align-items:center;gap:8px;">
            <a class="tabla-pager__btn" href="<?php echo visUrl($flt, max(1,$pagina-1)); ?>" <?php echo $pagina<=1?'style="pointer-events:none;opacity:.45;"':''; ?>><i class="bi bi-chevron-left"></i></a>
            <span class="tabla-pager__info">Página <?php echo $pagina; ?> de <?php echo $totalPaginas; ?></span>
            <a class="tabla-pager__btn" href="<?php echo visUrl($flt, min($totalPaginas,$pagina+1)); ?>" <?php echo $pagina>=$totalPaginas?'style="pointer-events:none;opacity:.45;"':''; ?>><i class="bi bi-chevron-right"></i></a>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Modales de detalle de visita -->
<?php foreach ($data['movimientos'] ?? [] as $v):
    $generoLabel = ['M' => 'Masculino', 'F' => 'Femenino'][$v->vis_genero ?? ''] ?? '—';
?>
<div class="modal fade" id="detVis<?php echo $v->id; ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-lines-fill"></i> Detalle de visita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="audit-kv" style="width:100%">
                    <tr><th>Visitante</th><td><?php echo htmlspecialchars($v->vis_nombre . ' ' . $v->vis_apellido); ?></td></tr>
                    <tr><th>Cédula</th><td><?php echo htmlspecialchars($v->vis_cedula ?? '—'); ?></td></tr>
                    <tr><th>Género</th><td><?php echo $generoLabel; ?></td></tr>
                    <tr><th>Teléfono</th><td><?php echo htmlspecialchars($v->vis_telefono ?? '—'); ?></td></tr>
                    <tr><th>Correo</th><td><?php echo htmlspecialchars($v->vis_correo ?? '—'); ?></td></tr>
                    <tr><th>Procedencia</th><td><?php echo htmlspecialchars($v->procedencia ?? '—'); ?></td></tr>
                    <tr><th>Motivo</th><td><?php echo htmlspecialchars($v->motivo ?? '—'); ?></td></tr>
                    <tr><th>Atendido por</th><td><?php echo !empty($v->emp_nombre) ? htmlspecialchars($v->emp_nombre . ' ' . $v->emp_apellido) : '—'; ?></td></tr>
                    <tr><th>Entrada</th><td><?php echo date('d/m/Y H:i', strtotime($v->hora_entrada)); ?></td></tr>
                    <tr><th>Salida</th><td><?php echo $v->hora_salida ? date('d/m/Y H:i', strtotime($v->hora_salida)) : 'Pendiente'; ?></td></tr>
                    <tr><th>Observaciones</th><td><?php echo htmlspecialchars($v->observaciones ?? '—'); ?></td></tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<style>
.audit-kv { width:100%; border-collapse:collapse; font-size:13px; }
.audit-kv th { text-align:left; font-weight:700; color:var(--text-secondary); padding:4px 12px 4px 0; white-space:nowrap; vertical-align:top; width:1%; }
.audit-kv td { color:var(--text-primary); padding:4px 0; word-break:break-word; }
.audit-kv tr + tr th, .audit-kv tr + tr td { border-top:1px dashed var(--border-subtle); }
</style>

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
