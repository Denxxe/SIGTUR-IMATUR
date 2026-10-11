<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Sistema · Seguridad</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Roles y Permisos'; ?></h1>
        <p class="page__subtitle">
            Asigna los módulos que puede acceder cada rol. Los cambios se aplican en la próxima sesión del usuario.
        </p>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary"
                data-bs-toggle="modal" data-bs-target="#modalRol" onclick="nuevoRol()">
            <i class="bi bi-plus-lg"></i> Nuevo Rol
        </button>
    </div>
</div>

<?php
$rbac    = $data['rbac']    ?? [];
$modulos = $data['modulos'] ?? [];
$roles   = $data['roles']   ?? [];

$coloresGrupo = [
    'General'    => ['bg' => '#f0f9ff', 'color' => '#0369a1', 'border' => '#bae6fd', 'check' => '#0284c7'],
    'RRHH'       => ['bg' => '#fdf4ff', 'color' => '#7e22ce', 'border' => '#e9d5ff', 'check' => '#9333ea'],
    'Recepción'  => ['bg' => '#f0fdf4', 'color' => '#15803d', 'border' => '#bbf7d0', 'check' => '#16a34a'],
    'Formación'  => ['bg' => '#f5f3ff', 'color' => '#6d28d9', 'border' => '#ddd6fe', 'check' => '#7c3aed'],
    'Turismo'    => ['bg' => '#fff7ed', 'color' => '#c2410c', 'border' => '#fed7aa', 'check' => '#ea580c'],
    'Inventario' => ['bg' => '#fefce8', 'color' => '#92400e', 'border' => '#fde68a', 'check' => '#d97706'],
    'Sistema'    => ['bg' => '#fef2f2', 'color' => '#991b1b', 'border' => '#fecaca', 'check' => '#dc2626'],
];

// Agrupar todos los módulos por grupo (para las cards)
$modulosPorGrupo = [];
foreach ($modulos as $ctrl => $meta) {
    if ($ctrl === 'DashboardController') continue;
    $modulosPorGrupo[$meta['grupo']][] = array_merge(['ctrl' => $ctrl], $meta);
}
?>

<div class="anim-slide-up u-fd-column" style="display:flex; gap:var(--sp-5);">

<?php foreach ($roles as $rol): ?>
<?php
    $rolId      = (int)$rol->id;
    $permitidos = $rbac[$rolId] ?? [];
    $esTotal    = $permitidos === '*';
    $esAdmin    = $rolId === 1;

    $ctrlActivos = $esTotal ? [] : (is_array($permitidos) ? $permitidos : []);
?>
<div class="sig-card" style="padding:0; overflow:hidden;" id="card-rol-<?php echo $rolId; ?>">

    <!-- Cabecera -->
    <div class="u-ai-center u-jc-space-between u-p-sp-4-sp-5 u-bg-bg-muted-subtle u-bdb-1px-solid-border-subtle" style="display:flex;">
        <div class="u-ai-center u-gap-sp-3" style="display:flex;">
            <div class="u-br-8px u-ai-center u-jc-center u-fw-700 u-fs-15px u-fsh-0" style="width:36px; height:36px; background:var(--brand-600); display:flex; color:#fff;">
                <?php echo $rolId; ?>
            </div>
            <div>
                <div class="u-fw-600 u-fs-15px u-c-text-primary">
                    <?php echo htmlspecialchars($rol->nombre); ?>
                </div>
                <?php if ($rol->descripcion): ?>
                <div class="u-fs-12px u-c-text-secondary u-mt-2px">
                    <?php echo htmlspecialchars($rol->descripcion); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="u-ai-center u-gap-sp-3 u-fwr-wrap u-jc-flex-end" style="display:flex;">
            <?php if ($esAdmin): ?>
            <span class="sig-badge sig-badge--danger u-fs-12px">
                <i class="bi bi-infinity"></i> Acceso total — no modificable
            </span>
            <?php else: ?>
            <span class="sig-badge sig-badge--neutral" style="font-size:12px;" id="badge-count-<?php echo $rolId; ?>">
                <?php echo count($ctrlActivos); ?> módulos
            </span>
            <?php endif; ?>
            <button class="row-action row-action--edit"
                    onclick='editarRol(<?php echo json_encode($rol, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                <i class="bi bi-pencil"></i> Editar
            </button>
            <?php if (!$esAdmin): ?>
            <a href="<?php echo URL_ROOT; ?>/roles/delete/<?php echo $rolId; ?>"
               class="row-action row-action--del delete-btn">
                <i class="bi bi-trash"></i>
            </a>
            <?php else: ?>
            <span class="row-action" style="opacity:.5;cursor:not-allowed;" title="El rol Administrador es inmutable">
                <i class="bi bi-lock"></i>
            </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Permisos -->
    <div class="u-p-sp-4-sp-5">

        <?php if ($esAdmin): ?>
        <div class="u-fs-13px u-c-text-secondary u-fst-italic">
            <i class="bi bi-shield-fill-check u-c-danger-500"></i>
            Acceso sin restricciones a todos los módulos del sistema. No se puede reducir.
        </div>

        <?php else: ?>

        <form action="<?php echo URL_ROOT; ?>/roles/storePermisos" method="POST" id="form-permisos-<?php echo $rolId; ?>">
            <input type="hidden" name="id_rol" value="<?php echo $rolId; ?>">

            <div class="u-fwr-wrap u-gap-sp-3 u-mb-sp-4" style="display:flex;">

                <?php foreach ($modulosPorGrupo as $grupo => $items): ?>
                <?php $c = $coloresGrupo[$grupo] ?? ['bg'=>'#f8fafc','color'=>'#475569','border'=>'#e2e8f0','check'=>'#64748b']; ?>
                <div style="background:<?php echo $c['bg']; ?>; border:1px solid <?php echo $c['border']; ?>;
                            border-radius:8px; padding:var(--sp-3); min-width:160px; flex:1;">
                    <div style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.5px;
                                color:<?php echo $c['color']; ?>; margin-bottom:var(--sp-2); display:flex;
                                align-items:center; gap:6px;">
                        <span><?php echo $grupo; ?></span>
                        <button type="button" onclick="toggleGrupo(<?php echo $rolId; ?>, '<?php echo $grupo; ?>')"
                                style="font-size:10px; font-weight:600; cursor:pointer; border:none; background:none;
                                       color:<?php echo $c['color']; ?>; padding:0; opacity:.7;"
                                title="Seleccionar/deseleccionar todos">
                            todo
                        </button>
                    </div>
                    <div class="u-fd-column u-gap-6px" style="display:flex;">
                        <?php foreach ($items as $item): ?>
                        <?php $checked = in_array($item['ctrl'], $ctrlActivos) ? 'checked' : ''; ?>
                        <label style="display:flex; align-items:center; gap:8px; font-size:12px;
                                      color:<?php echo $c['color']; ?>; cursor:pointer;"
                               data-grupo="<?php echo $grupo; ?>">
                            <input type="checkbox" name="modulos[]"
                                   value="<?php echo $item['ctrl']; ?>"
                                   <?php echo $checked; ?>
                                   style="accent-color:<?php echo $c['check']; ?>; width:14px; height:14px; cursor:pointer;"
                                   onchange="actualizarContador(<?php echo $rolId; ?>)">
                            <i class="bi <?php echo $item['icon']; ?>" style="flex-shrink:0;"></i>
                            <?php echo htmlspecialchars($item['label']); ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>

            <div class="u-ai-center u-gap-sp-3" style="display:flex;">
                <button type="submit" class="btn-sig btn-sig--primary u-fs-13px" style="padding:6px 16px;">
                    <i class="bi bi-floppy"></i> Guardar permisos
                </button>
                <span class="u-fs-12px u-c-text-secondary">
                    <i class="bi bi-info-circle"></i> Dashboard siempre incluido.
                    Los cambios aplican en la próxima sesión.
                </span>
            </div>
        </form>

        <?php endif; ?>
    </div>

</div>
<?php endforeach; ?>

</div>

<!-- Modal: crear/editar rol -->
<div class="modal fade" id="modalRol" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/roles/store" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRolLabel">Nuevo Rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="rol_id">
                <div class="sig-field mb-3">
                    <label for="rol_nombre" class="sig-field__label">Nombre <span class="req">*</span></label>
                    <input type="text" name="nombre" id="rol_nombre" class="sig-input" required
                           placeholder="Ej: Supervisor">
                </div>
                <div class="sig-field mb-3">
                    <label for="rol_descripcion" class="sig-field__label">Descripción</label>
                    <textarea name="descripcion" id="rol_descripcion" class="sig-textarea" rows="3"
                              placeholder="Responsabilidades del rol"></textarea>
                </div>
                <div class="u-p-sp-3 u-br-6px u-fs-12px" style="background:var(--brand-50); border:1px solid var(--brand-200); color:var(--brand-700);">
                    <i class="bi bi-lightbulb-fill"></i>
                    Al crear el rol podrás asignarle los módulos directamente desde esta misma pantalla.
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
function nuevoRol() {
    document.getElementById('modalRolLabel').innerText = 'Nuevo Rol';
    document.getElementById('rol_id').value = '';
    document.getElementById('rol_nombre').value = '';
    document.getElementById('rol_descripcion').value = '';
}
function editarRol(rol) {
    document.getElementById('modalRolLabel').innerText = 'Editar: ' + rol.nombre;
    document.getElementById('rol_id').value          = rol.id;
    document.getElementById('rol_nombre').value      = rol.nombre;
    document.getElementById('rol_descripcion').value = rol.descripcion || '';
    new bootstrap.Modal(document.getElementById('modalRol')).show();
}

function actualizarContador(rolId) {
    const form    = document.getElementById('form-permisos-' + rolId);
    const total   = form.querySelectorAll('input[type=checkbox]:checked').length;
    const badge   = document.getElementById('badge-count-' + rolId);
    if (badge) badge.textContent = total + ' módulos';
}

function toggleGrupo(rolId, grupo) {
    const form  = document.getElementById('form-permisos-' + rolId);
    const boxes = form.querySelectorAll('label[data-grupo="' + grupo + '"] input[type=checkbox]');
    const allChecked = Array.from(boxes).every(b => b.checked);
    boxes.forEach(b => b.checked = !allChecked);
    actualizarContador(rolId);
}
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
