<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Sistema · Seguridad</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Roles y Permisos'; ?></h1>
        <p class="page__subtitle">
            Define qué módulos puede usar cada rol.
            <span style="display:inline-flex; align-items:center; gap:4px; font-size:12px;
                         color:var(--warning-600); background:var(--warning-50);
                         padding:2px 8px; border-radius:4px; margin-left:6px;">
                <i class="bi bi-lock-fill"></i> Permisos definidos en código
            </span>
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

// Colores por grupo de módulo
$coloresGrupo = [
    'General'    => ['bg' => '#f0f9ff', 'color' => '#0369a1', 'border' => '#bae6fd'],
    'RRHH'       => ['bg' => '#fdf4ff', 'color' => '#7e22ce', 'border' => '#e9d5ff'],
    'Atención'   => ['bg' => '#f0fdf4', 'color' => '#15803d', 'border' => '#bbf7d0'],
    'Turismo'    => ['bg' => '#fff7ed', 'color' => '#c2410c', 'border' => '#fed7aa'],
    'Inventario' => ['bg' => '#fefce8', 'color' => '#92400e', 'border' => '#fde68a'],
    'Sistema'    => ['bg' => '#fef2f2', 'color' => '#991b1b', 'border' => '#fecaca'],
];
?>

<div class="anim-slide-up" style="display:flex; flex-direction:column; gap:var(--sp-5);">

<?php foreach ($roles as $rol): ?>
<?php
    $rolId      = (int)$rol->id;
    $permitidos = $rbac[$rolId] ?? [];
    $esTotal    = $permitidos === '*';
?>
<div class="sig-card" style="padding:0; overflow:hidden;">

    <!-- Cabecera del rol -->
    <div style="display:flex; align-items:center; justify-content:space-between;
                padding:var(--sp-4) var(--sp-5);
                background:var(--bg-muted-subtle); border-bottom:1px solid var(--border-subtle);">
        <div style="display:flex; align-items:center; gap:var(--sp-3);">
            <div style="width:36px; height:36px; border-radius:8px; background:var(--brand-600);
                        display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:15px;">
                <?php echo $rolId; ?>
            </div>
            <div>
                <div style="font-weight:600; font-size:15px; color:var(--text-primary);">
                    <?php echo htmlspecialchars($rol->nombre); ?>
                </div>
                <?php if ($rol->descripcion): ?>
                <div style="font-size:12px; color:var(--text-secondary); margin-top:2px;">
                    <?php echo htmlspecialchars($rol->descripcion); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div style="display:flex; align-items:center; gap:var(--sp-3);">
            <?php if ($esTotal): ?>
            <span class="sig-badge sig-badge--danger" style="font-size:12px;">
                <i class="bi bi-infinity"></i> Acceso total
            </span>
            <?php else: ?>
            <span class="sig-badge sig-badge--neutral" style="font-size:12px;">
                <?php echo count($permitidos); ?> módulos
            </span>
            <?php endif; ?>
            <button class="row-action row-action--edit"
                    onclick='editarRol(<?php echo json_encode($rol); ?>)'>
                <i class="bi bi-pencil"></i> Editar
            </button>
            <a href="<?php echo URL_ROOT; ?>/roles/delete/<?php echo $rolId; ?>"
               class="row-action row-action--del delete-btn">
                <i class="bi bi-trash"></i>
            </a>
        </div>
    </div>

    <!-- Módulos accesibles -->
    <div style="padding:var(--sp-4) var(--sp-5);">
        <?php if ($esTotal): ?>
        <div style="font-size:13px; color:var(--text-secondary); font-style:italic;">
            <i class="bi bi-shield-fill-check" style="color:var(--danger-500);"></i>
            Este rol tiene acceso sin restricciones a todos los módulos del sistema.
        </div>
        <?php else: ?>

        <?php
        // Agrupar módulos permitidos por grupo
        $grupos = [];
        foreach ($permitidos as $ctrl) {
            if ($ctrl === 'DashboardController') continue; // omitir dashboard, es implícito
            if (!isset($modulos[$ctrl])) continue;
            $m = $modulos[$ctrl];
            $grupos[$m['grupo']][] = ['ctrl' => $ctrl, 'label' => $m['label'], 'icon' => $m['icon']];
        }
        ?>

        <?php if (empty($grupos)): ?>
        <div style="font-size:13px; color:var(--text-secondary); font-style:italic;">
            Sin módulos asignados (solo dashboard).
        </div>
        <?php else: ?>
        <div style="display:flex; flex-wrap:wrap; gap:var(--sp-4);">
            <?php foreach ($grupos as $grupo => $items): ?>
            <?php $c = $coloresGrupo[$grupo] ?? ['bg'=>'#f8fafc','color'=>'#475569','border'=>'#e2e8f0']; ?>
            <div style="background:<?php echo $c['bg']; ?>; border:1px solid <?php echo $c['border']; ?>;
                        border-radius:8px; padding:var(--sp-2) var(--sp-3); min-width:140px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.5px;
                             color:<?php echo $c['color']; ?>; margin-bottom:var(--sp-2);">
                    <?php echo $grupo; ?>
                </div>
                <div style="display:flex; flex-direction:column; gap:4px;">
                    <?php foreach ($items as $item): ?>
                    <div style="display:flex; align-items:center; gap:6px; font-size:12px; color:<?php echo $c['color']; ?>;">
                        <i class="bi <?php echo $item['icon']; ?>" style="flex-shrink:0;"></i>
                        <?php echo htmlspecialchars($item['label']); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>

</div>
<?php endforeach; ?>

</div>

<!-- Nota sobre permisos futuros -->
<div style="margin-top:var(--sp-6); padding:var(--sp-4); background:var(--bg-muted-subtle);
            border:1px dashed var(--border-default); border-radius:8px;
            font-size:12px; color:var(--text-secondary); display:flex; gap:var(--sp-3); align-items:flex-start;">
    <i class="bi bi-info-circle-fill" style="color:var(--brand-500); margin-top:1px; flex-shrink:0;"></i>
    <div>
        <strong style="color:var(--text-primary);">Permisos estáticos (v1)</strong> —
        Los módulos visibles por rol están definidos en <code>RolesController::getMapaRbac()</code>.
        Para habilitar asignación dinámica desde esta pantalla, se requiere crear la tabla
        <code>permisos_rol</code> en BD y reemplazar ese método por una consulta.
        La estructura de esta vista ya está preparada para ese cambio.
    </div>
</div>

<!-- Modal Rol -->
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
                    <label class="sig-field__label">Nombre <span class="req">*</span></label>
                    <input type="text" name="nombre" id="rol_nombre" class="sig-input" required
                           placeholder="Ej: Supervisor">
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Descripción</label>
                    <textarea name="descripcion" id="rol_descripcion" class="sig-textarea" rows="3"
                              placeholder="Breve descripción de las responsabilidades de este rol"></textarea>
                </div>
                <div style="padding:var(--sp-3); background:var(--warning-50);
                            border:1px solid var(--warning-200); border-radius:6px; font-size:12px; color:var(--warning-700);">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    Los módulos accesibles para este rol deben configurarse en
                    <strong>RolesController::getMapaRbac()</strong> usando el ID del nuevo rol.
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
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
