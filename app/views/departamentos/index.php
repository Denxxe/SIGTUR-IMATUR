<?php require_once '../app/views/inc/header.php';
$tipoBadge = function ($t) {
    $map = [
        'Presidencia' => 'sig-badge--danger', 'Junta Directiva' => 'sig-badge--danger',
        'Dirección' => 'sig-badge--info', 'Oficina' => 'sig-badge--warning',
        'Coordinación' => 'sig-badge--success', 'Unidad' => 'sig-badge--secondary',
    ];
    $cls = $map[$t] ?? 'sig-badge--secondary';
    return '<span class="sig-badge ' . $cls . '">' . htmlspecialchars($t ?: '—') . '</span>';
};
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Organización</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Estructura Organizativa'; ?></h1>
        <p class="page__subtitle">Jerarquía de unidades: Presidencia → Direcciones → Coordinaciones.</p>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalDpto" onclick="nuevoDpto()">
            <i class="bi bi-plus-lg"></i> Agregar Unidad
        </button>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="100" data-buscar-placeholder="Buscar unidad…">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Unidad</th>
                <th>Unidad superior</th>
                <th>Descripción</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['departamentos'])): ?>
                <tr><td colspan="5" class="sig-table-empty">No hay unidades registradas.</td></tr>
            <?php else: foreach ($data['departamentos'] as $dpto): ?>
                <?php $pad = (int)($dpto->nivel ?? 0) * 22; ?>
                <tr>
                    <td><?php echo $tipoBadge($dpto->tipo_unidad); ?></td>
                    <td class="cell-strong" style="padding-left:<?php echo (8 + $pad); ?>px">
                        <?php echo ($pad > 0 ? '<span style="color:var(--text-secondary)">└ </span>' : ''); ?><?php echo htmlspecialchars($dpto->nombre); ?>
                    </td>
                    <td style="color:var(--text-secondary);font-size:13px"><?php echo htmlspecialchars($dpto->padre ?? '—'); ?></td>
                    <td style="color:var(--text-secondary);font-size:13px"><?php echo htmlspecialchars($dpto->descripcion ?? ''); ?></td>
                    <td class="col-actions">
                        <button class="row-action row-action--edit" onclick='editarDpto(<?php echo htmlspecialchars(json_encode($dpto), ENT_QUOTES, "UTF-8"); ?>)'><i class="bi bi-pencil"></i> Editar</button>
                        <a href="<?php echo URL_ROOT; ?>/departamentos/delete/<?php echo $dpto->id; ?>" class="row-action row-action--del delete-btn"><i class="bi bi-trash"></i> Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="modalDpto" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/departamentos/store" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDptoLabel">Nueva Unidad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="dpto_id">
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Nombre <span class="req">*</span></label>
                    <input type="text" name="nombre" id="dpto_nombre" class="sig-input" required placeholder="Ej: Dirección de Administración">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="sig-field">
                            <label class="sig-field__label">Tipo de unidad</label>
                            <select name="tipo_unidad" id="dpto_tipo_unidad" class="sig-select">
                                <option value="">— Seleccione —</option>
                                <?php foreach (Departamento::TIPOS_UNIDAD as $t): ?>
                                    <option value="<?php echo $t; ?>"><?php echo $t; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="sig-field">
                            <label class="sig-field__label">Unidad superior</label>
                            <select name="id_padre" id="dpto_id_padre" class="sig-select">
                                <option value="">— Ninguna (nivel raíz) —</option>
                                <?php foreach ($data['departamentos'] ?? [] as $opt): ?>
                                    <option value="<?php echo $opt->id; ?>"><?php echo str_repeat('— ', (int)($opt->nivel ?? 0)) . htmlspecialchars($opt->nombre); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Descripción / Funciones</label>
                    <textarea name="descripcion" id="dpto_descripcion" class="sig-textarea" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function nuevoDpto() {
        document.getElementById('modalDptoLabel').innerText = 'Nueva Unidad';
        document.getElementById('dpto_id').value = '';
        document.getElementById('dpto_nombre').value = '';
        document.getElementById('dpto_descripcion').value = '';
        document.getElementById('dpto_tipo_unidad').value = '';
        document.getElementById('dpto_id_padre').value = '';
    }

    function editarDpto(dpto) {
        document.getElementById('modalDptoLabel').innerText = 'Editar: ' + dpto.nombre;
        document.getElementById('dpto_id').value = dpto.id;
        document.getElementById('dpto_nombre').value = dpto.nombre;
        document.getElementById('dpto_descripcion').value = dpto.descripcion || '';
        document.getElementById('dpto_tipo_unidad').value = dpto.tipo_unidad || '';
        document.getElementById('dpto_id_padre').value = dpto.id_padre || '';
        // Una unidad no puede ser su propio padre: ocultar esa opción
        const sel = document.getElementById('dpto_id_padre');
        for (const o of sel.options) { o.disabled = (o.value !== '' && o.value == String(dpto.id)); }
        new bootstrap.Modal(document.getElementById('modalDpto')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
