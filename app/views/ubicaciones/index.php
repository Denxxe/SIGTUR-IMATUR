<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Inventario · Logística</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? ''; ?></h1>
        <p class="page__subtitle">Sedes y almacenes donde se ubican los bienes de la institución.</p>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalUbi" onclick="nuevaUbi()">
            <i class="bi bi-plus-lg"></i> Nueva Ubicación
        </button>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="10">
    <table class="sig-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre de Sede/Almacén</th>
                <th>Sede</th>
                <th>Departamento</th>
                <th>Referencia</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['ubicaciones'])): ?>
                <tr><td colspan="6" class="sig-table-empty">No hay ubicaciones registradas.</td></tr>
            <?php else: foreach ($data['ubicaciones'] as $ubi): ?>
                <tr>
                    <td><span class="cell-id"><?php echo $ubi->id; ?></span></td>
                    <td class="cell-strong">
                        <?php echo $ubi->nombre; ?>
                        <?php if (!empty($ubi->es_deposito)): ?>
                            <br><span class="sig-badge sig-badge--warning"><i class="bi bi-archive"></i> Depósito</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:13px"><?php echo htmlspecialchars($ubi->sede ?? '—'); ?></td>
                    <td>
                        <?php if (!empty($ubi->departamento_nombre)): ?>
                            <span class="sig-badge sig-badge--info"><i class="bi bi-building"></i> <?php echo htmlspecialchars($ubi->departamento_nombre); ?></span>
                        <?php else: ?>
                            <span style="color:var(--text-tertiary);font-style:italic;">Sin departamento</span>
                        <?php endif; ?>
                    </td>
                    <td style="color:var(--text-secondary);font-size:13px"><?php echo $ubi->descripcion; ?></td>
                    <td class="col-actions">
                        <button class="row-action row-action--edit" onclick='editarUbi(<?php echo json_encode($ubi); ?>)'><i class="bi bi-pencil"></i> Editar</button>
                        <a href="<?php echo URL_ROOT; ?>/ubicaciones/delete/<?php echo $ubi->id; ?>" class="row-action row-action--del delete-btn"><i class="bi bi-trash"></i> Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="modalUbi" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/ubicaciones/store" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUbiLabel">Ubicación Física</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="ubi_id">
                <div class="sig-field mb-3"><label class="sig-field__label">Nombre <span class="req">*</span></label><input type="text" name="nombre" id="ubi_nombre" class="sig-input" required placeholder="Ej: Mezzanina - Oficina RRHH"></div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Departamento <span class="req">*</span></label>
                    <select name="id_departamento" id="ubi_departamento" class="sig-input" required>
                        <option value="">— Seleccione —</option>
                        <?php foreach ($data['departamentos'] ?? [] as $dep): ?>
                            <option value="<?php echo $dep->id; ?>"><?php echo htmlspecialchars($dep->nombre); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label" for="ubi_sede">Sede <span class="req">*</span></label>
                    <select name="sede" id="ubi_sede" class="sig-input" required>
                        <?php foreach ($data['sedes'] ?? [] as $sede): ?>
                            <option value="<?php echo htmlspecialchars($sede); ?>"><?php echo htmlspecialchars($sede); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="sig-field mb-3"><label class="sig-field__label" for="ubi_descripcion">Referencia</label><textarea name="descripcion" id="ubi_descripcion" class="sig-textarea" rows="3"></textarea></div>
                <div class="sig-field">
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer">
                        <input type="checkbox" name="es_deposito" id="ubi_es_deposito" value="1">
                        Es el <strong>depósito</strong> de bienes sin asignar
                    </label>
                    <small style="display:block;color:var(--text-tertiary);font-size:12px;margin-top:4px;">
                        Todo bien que no esté asignado a un departamento debe estar en un depósito.
                        Su responsable no sale de este departamento, sino del que autoriza los bienes
                        (configurable en <em>Configuración</em>).
                    </small>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button><button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button></div>
        </form>
    </div>
</div>

<script>
    function nuevaUbi() {
        document.getElementById('modalUbiLabel').innerText = 'Nueva Ubicación';
        document.getElementById('ubi_id').value = '';
        document.querySelector('#modalUbi form').reset();
    }

    function editarUbi(ubi) {
        document.getElementById('modalUbiLabel').innerText = 'Editar: ' + ubi.nombre;
        document.getElementById('ubi_id').value = ubi.id;
        document.getElementById('ubi_nombre').value = ubi.nombre;
        document.getElementById('ubi_descripcion').value = ubi.descripcion || '';
        document.getElementById('ubi_departamento').value = ubi.id_departamento || '';
        document.getElementById('ubi_sede').value = ubi.sede || '<?php echo Ubicacion::SEDE_DEFAULT; ?>';
        // Mismo criterio defensivo que inventario/index.php para booleanos de PDO.
        document.getElementById('ubi_es_deposito').checked = (ubi.es_deposito === true || ubi.es_deposito === 't' || ubi.es_deposito === '1');
        new bootstrap.Modal(document.getElementById('modalUbi')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>