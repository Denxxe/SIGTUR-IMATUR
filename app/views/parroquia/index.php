<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Sistema · Localidades</div>
        <h1 class="page__title"><?php echo $data['titulo']; ?></h1>
        <p class="page__subtitle">Administración de parroquias del estado.</p>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalParroquia" onclick="nuevaParroquia()">
            <i class="bi bi-plus-lg"></i> Nueva Parroquia
        </button>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Municipio</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['parroquia'])): ?>
                <tr><td colspan="4" class="sig-table-empty">No hay parroquias registradas.</td></tr>
            <?php else: ?>
                <?php foreach ($data['parroquia'] as $par): ?>
                    <tr>
                        <td><span class="cell-id"><?php echo $par->id; ?></span></td>
                        <td class="cell-strong"><?php echo $par->nombre; ?></td>
                        <td><span class="sig-badge sig-badge--brand"><?php echo $par->municipio; ?></span></td>
                        <td class="col-actions">
                            <button class="row-action row-action--edit" onclick='editarParroquia(<?php echo json_encode($par); ?>)'>
                                <i class="bi bi-pencil"></i> Editar
                            </button>
                            <a href="<?php echo URL_ROOT; ?>/parroquia/delete/<?php echo $par->id; ?>" class="row-action row-action--del delete-btn">
                                <i class="bi bi-trash"></i> Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Parroquia -->
<div class="modal fade" id="modalParroquia" tabindex="-1" aria-labelledby="modalParroquiaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo URL_ROOT; ?>/parroquia/store" method="POST" class="needs-validation" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalParroquiaLabel">Nueva Parroquia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="parroquia_id">
                    <div class="sig-field mb-3">
                        <label class="sig-field__label">Nombre <span class="req">*</span></label>
                        <input type="text" class="sig-input" name="nombre" id="parroquia_nombre" required placeholder="Ej: Altagracia">
                    </div>
                    <div class="sig-field mb-3">
                        <label class="sig-field__label">Municipio <span class="req">*</span></label>
                        <select class="sig-select" name="id_municipio" id="parroquia_municipio" required>
                            <option value="">Seleccione un municipio...</option>
                            <?php foreach ($data['municipios'] as $mun): ?>
                                <option value="<?php echo $mun->id; ?>"><?php echo $mun->nombre; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function nuevaParroquia() {
        document.getElementById('modalParroquiaLabel').innerText = 'Nueva Parroquia';
        document.getElementById('parroquia_id').value = '';
        document.getElementById('parroquia_nombre').value = '';
        document.getElementById('parroquia_municipio').value = '';
    }
    function editarParroquia(par) {
        document.getElementById('modalParroquiaLabel').innerText = 'Editar: ' + par.nombre;
        document.getElementById('parroquia_id').value = par.id;
        document.getElementById('parroquia_nombre').value = par.nombre;
        document.getElementById('parroquia_municipio').value = par.id_municipio;
        new bootstrap.Modal(document.getElementById('modalParroquia')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
