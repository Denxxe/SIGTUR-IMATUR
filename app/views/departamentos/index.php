<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Organización</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Gestión de Departamentos'; ?></h1>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalDpto" onclick="nuevoDpto()">
            <i class="bi bi-plus-lg"></i> Agregar Departamento
        </button>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['departamentos'] ?? [] as $dpto): ?>
                <tr>
                    <td><span class="cell-id"><?php echo $dpto->id; ?></span></td>
                    <td class="cell-strong"><?php echo $dpto->nombre; ?></td>
                    <td style="color:var(--text-secondary);font-size:13px"><?php echo $dpto->descripcion; ?></td>
                    <td class="col-actions">
                        <button class="row-action row-action--edit" onclick='editarDpto(<?php echo json_encode($dpto); ?>)'><i class="bi bi-pencil"></i> Editar</button>
                        <a href="<?php echo URL_ROOT; ?>/departamentos/delete/<?php echo $dpto->id; ?>" class="row-action row-action--del delete-btn"><i class="bi bi-trash"></i> Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="modalDpto" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/departamentos/store" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDptoLabel">Nuevo Departamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="dpto_id">
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Nombre <span class="req">*</span></label>
                    <input type="text" name="nombre" id="dpto_nombre" class="sig-input" required placeholder="Ej: Dirección de Turismo">
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
        document.getElementById('modalDptoLabel').innerText = 'Nuevo Departamento';
        document.getElementById('dpto_id').value = '';
        document.getElementById('dpto_nombre').value = '';
        document.getElementById('dpto_descripcion').value = '';
    }

    function editarDpto(dpto) {
        document.getElementById('modalDptoLabel').innerText = 'Editar: ' + dpto.nombre;
        document.getElementById('dpto_id').value = dpto.id;
        document.getElementById('dpto_nombre').value = dpto.nombre;
        document.getElementById('dpto_descripcion').value = dpto.descripcion;
        new bootstrap.Modal(document.getElementById('modalDpto')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>