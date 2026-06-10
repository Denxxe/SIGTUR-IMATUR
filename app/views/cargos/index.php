<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Organización</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Gestión de Cargos'; ?></h1>
        <p class="page__subtitle">Puestos institucionales ordenados por jerarquía: Presidencia → Dirección → Coordinación → Adscrito.</p>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalCargo" onclick="nuevoCargo()">
            <i class="bi bi-plus-lg"></i> Nuevo Cargo
        </button>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="10">
    <table class="sig-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nivel jerárquico</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['cargos'])): ?>
                <tr>
                    <td colspan="5" class="sig-table-empty">No hay cargos registrados.</td>
                </tr>
            <?php else: ?>
                <?php
                $nivelBadge = ['Presidencia'=>'sig-badge--danger','Dirección'=>'sig-badge--info','Coordinación'=>'sig-badge--success','Adscrito'=>'sig-badge--neutral'];
                foreach ($data['cargos'] as $cargo): ?>
                    <tr>
                        <td><span class="cell-id"><?php echo $cargo->id; ?></span></td>
                        <td><span class="sig-badge <?php echo $nivelBadge[$cargo->nivel_jerarquico] ?? 'sig-badge--neutral'; ?>"><?php echo htmlspecialchars($cargo->nivel_jerarquico ?? '—'); ?></span></td>
                        <td class="cell-strong"><?php echo $cargo->nombre; ?></td>
                        <td style="color:var(--text-secondary);font-size:13px"><?php echo $cargo->descripcion; ?></td>
                        <td class="col-actions">
                            <button class="row-action row-action--edit" onclick='editarCargo(<?php echo json_encode($cargo); ?>)'>
                                <i class="bi bi-pencil"></i> Editar
                            </button>
                            <a href="<?php echo URL_ROOT; ?>/cargos/delete/<?php echo $cargo->id; ?>" class="row-action row-action--del delete-btn">
                                <i class="bi bi-trash"></i> Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="modalCargo" tabindex="-1" aria-labelledby="modalCargoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo URL_ROOT; ?>/cargos/store" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCargoLabel">Nuevo Cargo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="cargo_id">
                    <div class="sig-field mb-3">
                        <label class="sig-field__label">Nombre del Cargo <span class="req">*</span></label>
                        <input type="text" class="sig-input" name="nombre" id="cargo_nombre" required placeholder="Ej: Especialista III">
                    </div>
                    <div class="sig-field mb-3">
                        <label class="sig-field__label">Descripción</label>
                        <textarea class="sig-textarea" name="descripcion" id="cargo_descripcion" rows="3" placeholder="Funciones del cargo..."></textarea>
                    </div>
                    <div class="sig-field mb-3">
                        <label class="sig-field__label">Nivel jerárquico <span class="req">*</span></label>
                        <select class="sig-select" name="nivel_jerarquico" id="cargo_nivel" required>
                            <?php foreach (Cargo::NIVELES as $nv): ?>
                                <option value="<?php echo $nv; ?>"<?php echo $nv === Cargo::NIVEL_DEFAULT ? ' selected' : ''; ?>><?php echo $nv; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color:var(--text-tertiary)">Sucesión de responsabilidad según el organigrama.</small>
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
    function nuevoCargo() {
        document.getElementById('modalCargoLabel').innerText = 'Nuevo Cargo';
        document.getElementById('cargo_id').value = '';
        document.getElementById('cargo_nombre').value = '';
        document.getElementById('cargo_descripcion').value = '';
        document.getElementById('cargo_nivel').value = '<?php echo Cargo::NIVEL_DEFAULT; ?>';
    }

    function editarCargo(cargo) {
        document.getElementById('modalCargoLabel').innerText = 'Editar: ' + cargo.nombre;
        document.getElementById('cargo_id').value = cargo.id;
        document.getElementById('cargo_nombre').value = cargo.nombre;
        document.getElementById('cargo_descripcion').value = cargo.descripcion;
        document.getElementById('cargo_nivel').value = cargo.nivel_jerarquico || '<?php echo Cargo::NIVEL_DEFAULT; ?>';
        new bootstrap.Modal(document.getElementById('modalCargo')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>