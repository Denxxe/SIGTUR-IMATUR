<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Sistema · Localidades</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Gestión de Municipios'; ?></h1>
        <p class="page__subtitle">Administración de municipios del estado.</p>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalMunicipio" onclick="nuevoMunicipio()">
            <i class="bi bi-plus-lg"></i> Nuevo Municipio
        </button>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Código Postal</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['municipio'])): ?>
                <tr>
                    <td colspan="4" class="sig-table-empty">No hay municipios registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($data['municipio'] as $mun): ?>
                    <tr>
                        <td><span class="cell-id"><?php echo $mun->id; ?></span></td>
                        <td class="cell-strong"><?php echo $mun->nombre; ?></td>
                        <td><?php echo $mun->codigo_postal; ?></td>
                        <td class="col-actions">
                            <button class="row-action row-action--edit" onclick='editarMunicipio(<?php echo json_encode($mun); ?>)'>
                                <i class="bi bi-pencil"></i> Editar
                            </button>
                            <a href="<?php echo URL_ROOT; ?>/municipio/delete/<?php echo $mun->id; ?>" class="row-action row-action--del delete-btn">
                                <i class="bi bi-trash"></i> Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Municipio -->
<div class="modal fade" id="modalMunicipio" tabindex="-1" aria-labelledby="modalMunicipioLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo URL_ROOT; ?>/municipio/store" method="POST" class="needs-validation" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMunicipioLabel">Nuevo Municipio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="municipio_id">
                    <div class="sig-field mb-3">
                        <label class="sig-field__label">Nombre del Municipio <span class="req">*</span></label>
                        <input type="text" class="sig-input" name="nombre" id="municipio_nombre" required placeholder="Ej: Sucre">
                    </div>
                    <div class="sig-field mb-3">
                        <label class="sig-field__label">Código Postal <span class="req">*</span></label>
                        <input type="text" class="sig-input" name="codigo_postal" id="municipio_cp" required placeholder="Ej: 6101">
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
    function nuevoMunicipio() {
        document.getElementById('modalMunicipioLabel').innerText = 'Nuevo Municipio';
        document.getElementById('municipio_id').value = '';
        document.getElementById('municipio_nombre').value = '';
        document.getElementById('municipio_cp').value = '';
    }

    function editarMunicipio(mun) {
        document.getElementById('modalMunicipioLabel').innerText = 'Editar: ' + mun.nombre;
        document.getElementById('municipio_id').value = mun.id;
        document.getElementById('municipio_nombre').value = mun.nombre;
        document.getElementById('municipio_cp').value = mun.codigo_postal;
        new bootstrap.Modal(document.getElementById('modalMunicipio')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>