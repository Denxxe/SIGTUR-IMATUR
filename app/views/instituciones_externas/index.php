<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Turismo · Instituciones</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? ''; ?></h1>
        <p class="page__subtitle">Registro de escuelas, liceos, comunidades y entes que participan en rutas y actividades formativas.</p>
    </div>
    <div class="page__actions">
        <button class="btn-sig btn-sig--primary" onclick="nuevaInstitucion()" style="background:var(--teal-600);">
            <i class="bi bi-building-add"></i> Nueva Institución
        </button>
    </div>
</div>

<!-- Tabla -->
<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Institución</th>
                <th>Tipo</th>
                <th>Municipio</th>
                <th>Contacto</th>
                <th>Teléfono</th>
                <th style="text-align:center;">En Rutas</th>
                <th style="text-align:center;">Participantes</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['instituciones'])): ?>
                <tr><td colspan="8" class="sig-table-empty">No hay instituciones registradas. Use "Nueva Institución" para agregar.</td></tr>
            <?php else: ?>
                <?php foreach ($data['instituciones'] as $ie): ?>
                <tr>
                    <td>
                        <div style="display:flex;flex-direction:column;gap:2px;">
                            <span class="cell-strong"><?php echo htmlspecialchars($ie->nombre); ?></span>
                            <?php if ($ie->es_educativa): ?>
                                <span style="font-size:10px;color:var(--brand-500);"><i class="bi bi-mortarboard-fill"></i> Educativa</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <?php
                        $tipoBadge = ['Educativa' => 'sig-badge--brand', 'Comunitaria' => 'sig-badge--success', 'Pública' => 'sig-badge--info', 'ONG' => 'sig-badge--warning'];
                        ?>
                        <span class="sig-badge sig-badge--sm <?php echo $tipoBadge[$ie->tipo ?? ''] ?? 'sig-badge--neutral'; ?>">
                            <?php echo htmlspecialchars($ie->tipo ?? '—'); ?>
                        </span>
                    </td>
                    <td style="font-size:13px;"><?php echo htmlspecialchars($ie->municipio ?? '—'); ?></td>
                    <td style="font-size:12px;color:var(--text-secondary);"><?php echo htmlspecialchars($ie->contacto ?? '—'); ?></td>
                    <td style="font-size:12px;color:var(--text-secondary);"><?php echo htmlspecialchars($ie->telefono ?? '—'); ?></td>
                    <td style="text-align:center;font-weight:700;color:var(--teal-600);"><?php echo (int)($ie->total_rutas ?? 0); ?></td>
                    <td style="text-align:center;font-weight:700;color:var(--brand-600);"><?php echo (int)($ie->total_participantes_rutas ?? 0); ?></td>
                    <td class="col-actions">
                        <button class="row-action row-action--edit" onclick='editarInstitucion(<?php echo json_encode($ie); ?>)' title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <a href="<?php echo URL_ROOT; ?>/institucionesexternas/delete/<?php echo $ie->id; ?>"
                           class="row-action row-action--delete"
                           onclick="return confirmDelete(event, this)"
                           data-nombre="<?php echo htmlspecialchars($ie->nombre); ?>"
                           title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="modalInstitucion" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo URL_ROOT; ?>/institucionesexternas/store" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalInstLabel">Nueva Institución</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="inst_id">
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="sig-field">
                            <label class="sig-field__label">Nombre de la Institución <span class="req">*</span></label>
                            <input type="text" name="nombre" id="inst_nombre" class="sig-input" required
                                   placeholder="Ej: U.E. Simón Bolívar, Consejo Comunal La Llanada...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Tipo</label>
                            <select name="tipo" id="inst_tipo" class="sig-select">
                                <?php foreach ($data['tipos'] as $t): ?>
                                    <option value="<?php echo $t; ?>"><?php echo $t; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Municipio</label>
                            <input type="text" name="municipio" id="inst_municipio" class="sig-input"
                                   placeholder="Ej: Sucre, Bolívar...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Persona de Contacto</label>
                            <input type="text" name="contacto" id="inst_contacto" class="sig-input"
                                   placeholder="Nombre del representante">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Teléfono</label>
                            <input type="text" name="telefono" id="inst_telefono" class="sig-input"
                                   placeholder="0424-0000000">
                        </div>
                    </div>
                    <div class="col-12">
                        <div style="padding:var(--sp-3);background:var(--bg-muted-subtle);border-radius:8px;">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="inst_es_edu" name="es_educativa" value="1">
                                <label class="form-check-label" for="inst_es_edu" style="font-size:13px;cursor:pointer;user-select:none;">
                                    <i class="bi bi-mortarboard"></i> Es institución educativa (escuela, liceo, universidad)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn-sig btn-sig--primary" style="background:var(--teal-600);">
                    <i class="bi bi-check-lg"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function nuevaInstitucion() {
    document.getElementById('modalInstLabel').innerText = 'Nueva Institución';
    document.getElementById('inst_id').value = '';
    document.querySelector('#modalInstitucion form').reset();
    new bootstrap.Modal(document.getElementById('modalInstitucion')).show();
}

function editarInstitucion(ie) {
    document.getElementById('modalInstLabel').innerText = 'Editar: ' + ie.nombre;
    document.getElementById('inst_id').value       = ie.id;
    document.getElementById('inst_nombre').value   = ie.nombre;
    document.getElementById('inst_tipo').value     = ie.tipo || 'Educativa';
    document.getElementById('inst_municipio').value= ie.municipio || '';
    document.getElementById('inst_contacto').value = ie.contacto || '';
    document.getElementById('inst_telefono').value = ie.telefono || '';
    document.getElementById('inst_es_edu').checked = ie.es_educativa == true || ie.es_educativa === 't' || ie.es_educativa === '1';
    new bootstrap.Modal(document.getElementById('modalInstitucion')).show();
}
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
