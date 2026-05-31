<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Inventario · Logística</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? ''; ?></h1>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalUbi" onclick="nuevaUbi()">
            <i class="bi bi-plus-lg"></i> Nueva Ubicación
        </button>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre de Sede/Almacén</th>
                <th>Departamento</th>
                <th>Referencia</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['ubicaciones'] ?? [] as $ubi): ?>
                <tr>
                    <td><span class="cell-id"><?php echo $ubi->id; ?></span></td>
                    <td class="cell-strong"><?php echo $ubi->nombre; ?></td>
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
            <?php endforeach; ?>
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
                <div class="sig-field mb-3"><label class="sig-field__label">Referencia</label><textarea name="descripcion" id="ubi_descripcion" class="sig-textarea" rows="3"></textarea></div>
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
        new bootstrap.Modal(document.getElementById('modalUbi')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>