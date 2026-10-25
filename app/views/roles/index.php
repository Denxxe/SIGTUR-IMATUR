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
        <button type="button" class="btn-sig btn-sig--ghost" id="rl-toggle-todos" onclick="rlAlternarTodos()">
            <i class="bi bi-arrows-expand"></i> <span>Expandir todos</span>
        </button>
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

// Un color y un ícono por grupo. El tinte de fondo se deriva con color-mix(),
// así el mismo valor sirve en tema claro y oscuro.
$estiloGrupo = [
    'General'    => ['c' => '#0284c7', 'icon' => 'bi-grid'],
    'RRHH'       => ['c' => '#9333ea', 'icon' => 'bi-people'],
    'Recepción'  => ['c' => '#16a34a', 'icon' => 'bi-door-open'],
    'Formación'  => ['c' => '#7c3aed', 'icon' => 'bi-mortarboard'],
    'Turismo'    => ['c' => '#ea580c', 'icon' => 'bi-compass'],
    'Inventario' => ['c' => '#d97706', 'icon' => 'bi-box-seam'],
    'Sistema'    => ['c' => '#dc2626', 'icon' => 'bi-shield-lock'],
];

// Módulos asignables agrupados. El Panel Principal no se muestra: todo rol lo tiene.
$modulosPorGrupo = [];
foreach ($modulos as $ctrl => $meta) {
    if ($ctrl === 'DashboardController') continue;
    $modulosPorGrupo[$meta['grupo']][] = array_merge(['ctrl' => $ctrl], $meta);
}
$totalAsignables = array_sum(array_map('count', $modulosPorGrupo));
?>

<div class="rl-list anim-slide-up">

<?php foreach ($roles as $rol): ?>
<?php
    $rolId      = (int)$rol->id;
    $permitidos = $rbac[$rolId] ?? [];
    $esTotal    = $permitidos === '*';
    $esAdmin    = $rolId === 1;

    $ctrlActivos = $esTotal ? [] : (is_array($permitidos) ? $permitidos : []);
    // Solo cuentan los que se pueden marcar (el Panel Principal va de oficio).
    $nActivos = 0;
    foreach ($modulosPorGrupo as $items) {
        foreach ($items as $it) { if (in_array($it['ctrl'], $ctrlActivos, true)) $nActivos++; }
    }
    $pct = $esAdmin ? 100 : ($totalAsignables ? round($nActivos * 100 / $totalAsignables) : 0);
?>
<section class="sig-card rl-card<?php echo $esAdmin ? ' rl-card--admin' : ''; ?>" id="rol-<?php echo $rolId; ?>" data-rol="<?php echo $rolId; ?>">

    <header class="rl-head">
        <?php if ($esAdmin): ?>
        <div class="rl-head__main">
        <?php else: ?>
        <button type="button" class="rl-head__main" aria-expanded="false"
                aria-controls="rl-body-<?php echo $rolId; ?>" onclick="rlAlternar(<?php echo $rolId; ?>)">
        <?php endif; ?>
            <span class="rl-avatar"><i class="bi <?php echo $esAdmin ? 'bi-shield-fill-check' : 'bi-person-badge'; ?>"></i></span>
            <span class="rl-head__text">
                <span class="rl-head__name">
                    <?php echo htmlspecialchars($rol->nombre); ?>
                    <span class="rl-head__id">#<?php echo $rolId; ?></span>
                </span>
                <?php if ($rol->descripcion): ?>
                <span class="rl-head__desc"><?php echo htmlspecialchars($rol->descripcion); ?></span>
                <?php endif; ?>
            </span>
            <span class="rl-meter" aria-hidden="true">
                <span class="rl-meter__label">
                    <?php if ($esAdmin): ?>
                        <strong>Acceso total</strong>
                    <?php else: ?>
                        <strong id="rl-count-<?php echo $rolId; ?>"><?php echo $nActivos; ?></strong> de <?php echo $totalAsignables; ?> módulos
                    <?php endif; ?>
                </span>
                <span class="rl-meter__bar"><span id="rl-bar-<?php echo $rolId; ?>" style="width:<?php echo $pct; ?>%"></span></span>
            </span>
            <?php if (!$esAdmin): ?>
            <i class="bi bi-chevron-down rl-head__chev"></i>
            <?php endif; ?>
        <?php echo $esAdmin ? '</div>' : '</button>'; ?>

        <div class="rl-head__actions">
            <button type="button" class="row-action row-action--edit"
                    onclick='editarRol(<?php echo json_encode($rol, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                <i class="bi bi-pencil"></i> Editar
            </button>
            <?php if (!$esAdmin): ?>
            <a href="<?php echo URL_ROOT; ?>/roles/delete/<?php echo $rolId; ?>"
               class="row-action row-action--del delete-btn">
                <i class="bi bi-trash"></i> Eliminar
            </a>
            <?php else: ?>
            <span class="row-action rl-locked" title="El rol Administrador es inmutable">
                <i class="bi bi-lock"></i>
            </span>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($esAdmin): ?>
    <div class="rl-admin-note">
        <i class="bi bi-infinity"></i>
        Acceso sin restricciones a todos los módulos del sistema, incluida la Bitácora. No se puede reducir.
    </div>

    <?php else: ?>
    <div class="rl-body" id="rl-body-<?php echo $rolId; ?>" hidden>
        <form action="<?php echo URL_ROOT; ?>/roles/storePermisos" method="POST"
              id="form-permisos-<?php echo $rolId; ?>" class="rl-form" data-rol="<?php echo $rolId; ?>">
            <input type="hidden" name="id_rol" value="<?php echo $rolId; ?>">

            <div class="rl-grid">
                <?php foreach ($modulosPorGrupo as $grupo => $items): ?>
                <?php
                    $g = $estiloGrupo[$grupo] ?? ['c' => '#64748b', 'icon' => 'bi-folder'];
                    $nGrupo = 0;
                    foreach ($items as $it) { if (in_array($it['ctrl'], $ctrlActivos, true)) $nGrupo++; }
                ?>
                <div class="rl-group" style="--rl-c: <?php echo $g['c']; ?>;" data-grupo="<?php echo htmlspecialchars($grupo); ?>">
                    <div class="rl-group__head">
                        <span class="rl-group__icon"><i class="bi <?php echo $g['icon']; ?>"></i></span>
                        <span class="rl-group__name"><?php echo htmlspecialchars($grupo); ?></span>
                        <span class="rl-group__count"><span class="js-n"><?php echo $nGrupo; ?></span>/<?php echo count($items); ?></span>
                        <button type="button" class="rl-group__all" onclick="rlGrupo(this)">
                            <?php echo $nGrupo === count($items) ? 'Ninguno' : 'Todos'; ?>
                        </button>
                    </div>
                    <div class="rl-group__items">
                        <?php foreach ($items as $item): ?>
                        <label class="rl-item">
                            <i class="bi <?php echo $item['icon']; ?> rl-item__icon"></i>
                            <span class="rl-item__label"><?php echo htmlspecialchars($item['label']); ?></span>
                            <input type="checkbox" class="rl-switch" name="modulos[]"
                                   value="<?php echo $item['ctrl']; ?>"
                                   <?php echo in_array($item['ctrl'], $ctrlActivos, true) ? 'checked' : ''; ?>>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="rl-savebar">
                <span class="rl-savebar__state">
                    <span class="rl-dot"></span>
                    <span class="js-estado">Sin cambios</span>
                    <span class="rl-savebar__hint">· El Panel Principal siempre está incluido.</span>
                </span>
                <span class="rl-savebar__btns">
                    <button type="button" class="btn-sig btn-sig--ghost btn-sig--sm js-descartar" onclick="rlDescartar(<?php echo $rolId; ?>)" disabled>
                        <i class="bi bi-arrow-counterclockwise"></i> Descartar
                    </button>
                    <button type="submit" class="btn-sig btn-sig--primary btn-sig--sm js-guardar" disabled>
                        <i class="bi bi-floppy"></i> Guardar permisos
                    </button>
                </span>
            </div>
        </form>
    </div>
    <?php endif; ?>

</section>
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

<style>
.rl-list { display: flex; flex-direction: column; gap: var(--sp-4); }
/* overflow visible: con el hidden de .sig-card la barra de guardado no puede quedar fija (sticky). */
.rl-card { scroll-margin-top: calc(var(--header-h) + var(--sp-5)); overflow: visible; }
.rl-body, .rl-savebar { border-radius: 0 0 var(--r-lg) var(--r-lg); }

/* Cabecera del rol: la parte izquierda despliega, las acciones quedan aparte. */
.rl-head { display: flex; align-items: center; gap: var(--sp-3); padding: var(--sp-4) var(--sp-5); }
.rl-head__main {
  flex: 1; min-width: 0; display: flex; align-items: center; gap: var(--sp-4);
  background: none; border: 0; padding: 0; text-align: left; color: inherit; font: inherit; cursor: pointer;
}
div.rl-head__main { cursor: default; }
.rl-head__main:focus-visible { outline: 2px solid var(--brand-400); outline-offset: 4px; border-radius: var(--r-sm); }
.rl-avatar {
  width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
  display: grid; place-items: center; font-size: 19px; color: #fff;
  background: linear-gradient(135deg, var(--brand-500), var(--brand-700));
  box-shadow: var(--sh-glow-brand);
}
.rl-card--admin .rl-avatar { background: linear-gradient(135deg, var(--danger-500), var(--danger-700)); box-shadow: 0 8px 24px -6px rgba(220, 38, 38, .45); }
.rl-head__text { min-width: 0; flex: 1; display: flex; flex-direction: column; gap: 2px; }
.rl-head__name { font-size: var(--fs-md); font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 8px; }
.rl-head__id { font-size: 11px; font-weight: 600; color: var(--text-tertiary); font-variant-numeric: tabular-nums; }
.rl-head__desc { font-size: 12.5px; color: var(--text-secondary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.rl-meter { width: 190px; flex-shrink: 0; display: flex; flex-direction: column; gap: 6px; }
.rl-meter__label { font-size: 12px; color: var(--text-secondary); text-align: right; }
.rl-meter__label strong { color: var(--text-primary); font-variant-numeric: tabular-nums; }
.rl-meter__bar { height: 6px; border-radius: var(--r-full); background: var(--border-subtle); overflow: hidden; }
.rl-meter__bar > span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, var(--brand-400), var(--brand-600)); transition: width var(--t-slow) var(--ease-out); }
.rl-card--admin .rl-meter__bar > span { background: linear-gradient(90deg, var(--danger-500), var(--danger-600)); }

.rl-head__chev { font-size: 15px; color: var(--text-tertiary); transition: transform var(--t-base) var(--ease-out); }
.rl-card.is-open .rl-head__chev { transform: rotate(180deg); }
.rl-head__actions { display: flex; align-items: center; gap: var(--sp-2); flex-shrink: 0; }
.rl-locked { opacity: .5; cursor: not-allowed; }

.rl-admin-note {
  margin: 0 var(--sp-5) var(--sp-4); padding: var(--sp-3) var(--sp-4); border-radius: var(--r-md);
  font-size: 13px; display: flex; align-items: center; gap: 8px;
  color: var(--danger-700); background: color-mix(in srgb, var(--danger-500) 8%, transparent);
  border: 1px dashed color-mix(in srgb, var(--danger-500) 35%, transparent);
}
[data-theme="dark"] .rl-admin-note { color: #fca5a5; }

/* Cuerpo: grupos en rejilla uniforme. */
.rl-body { border-top: 1px solid var(--border-subtle); background: var(--bg-muted); }
.rl-card.is-open .rl-body { animation: rlIn var(--t-base) var(--ease-out); }
@keyframes rlIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: none; } }
.rl-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: var(--sp-4); padding: var(--sp-5); }

.rl-group {
  background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--r-md);
  box-shadow: var(--sh-xs); overflow: hidden; border-top: 3px solid var(--rl-c);
}
.rl-group__head { display: flex; align-items: center; gap: 8px; padding: 10px 12px; border-bottom: 1px solid var(--border-subtle); }
.rl-group__icon {
  width: 26px; height: 26px; border-radius: 8px; display: grid; place-items: center; font-size: 13px;
  color: var(--rl-c); background: color-mix(in srgb, var(--rl-c) 12%, transparent);
}
.rl-group__name { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--text-primary); }
.rl-group__count { font-size: 11px; font-weight: 600; color: var(--text-tertiary); font-variant-numeric: tabular-nums; }
.rl-group__all {
  margin-left: auto; border: 0; background: none; padding: 2px 8px; border-radius: var(--r-full);
  font-size: 11px; font-weight: 600; color: var(--rl-c); cursor: pointer;
}
.rl-group__all:hover { background: color-mix(in srgb, var(--rl-c) 10%, transparent); }

.rl-group__items { display: flex; flex-direction: column; padding: 6px; }
.rl-item {
  display: flex; align-items: center; gap: 10px; padding: 8px; border-radius: 8px;
  font-size: 13px; color: var(--text-secondary); cursor: pointer; transition: background var(--t-fast);
}
.rl-item:hover { background: var(--bg-muted); }
.rl-item__icon { font-size: 14px; width: 16px; text-align: center; color: var(--text-tertiary); flex-shrink: 0; transition: color var(--t-fast); }
.rl-item__label { flex: 1; min-width: 0; line-height: 1.3; }
.rl-item:has(.rl-switch:checked) { color: var(--text-primary); font-weight: 500; background: color-mix(in srgb, var(--rl-c) 7%, transparent); }
.rl-item:has(.rl-switch:checked) .rl-item__icon { color: var(--rl-c); }

/* Interruptor: es el checkbox nativo (se envía en el POST), solo cambia la apariencia. */
.rl-switch {
  appearance: none; -webkit-appearance: none; flex-shrink: 0; margin: 0; cursor: pointer;
  width: 34px; height: 20px; border-radius: var(--r-full); position: relative;
  background: var(--border-default); transition: background var(--t-base) var(--ease-out);
}
.rl-switch::after {
  content: ""; position: absolute; top: 2px; left: 2px; width: 16px; height: 16px; border-radius: 50%;
  background: #fff; box-shadow: 0 1px 3px rgba(14, 20, 38, .25); transition: transform var(--t-base) var(--ease-spring);
}
.rl-switch:checked { background: var(--rl-c); }
.rl-switch:checked::after { transform: translateX(14px); }
.rl-switch:focus-visible { outline: 2px solid var(--rl-c); outline-offset: 2px; }

/* Barra de guardado: fija al fondo mientras el rol está abierto. */
.rl-savebar {
  position: sticky; bottom: 0; z-index: 2; display: flex; align-items: center; justify-content: space-between;
  gap: var(--sp-3); flex-wrap: wrap; padding: var(--sp-3) var(--sp-5);
  background: var(--bg-surface); border-top: 1px solid var(--border-subtle);
  box-shadow: 0 -6px 16px -10px rgba(14, 20, 38, .18);
}
.rl-savebar__state { display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: var(--text-secondary); }
.rl-savebar__hint { color: var(--text-tertiary); }
.rl-savebar__btns { display: flex; gap: var(--sp-2); }
.rl-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--success-500); }
.rl-form.is-dirty .rl-dot { background: var(--warning-500); box-shadow: 0 0 0 4px color-mix(in srgb, var(--warning-500) 20%, transparent); }
.rl-form.is-dirty .js-estado { color: var(--warning-600); font-weight: 600; }
.rl-card.is-dirty { border-color: color-mix(in srgb, var(--warning-500) 55%, transparent); }

@media (max-width: 768px) {
  .rl-head { flex-wrap: wrap; padding: var(--sp-4); }
  .rl-head__main { flex-wrap: wrap; gap: var(--sp-3); }
  .rl-meter { order: 3; width: 100%; }
  .rl-meter__label { text-align: left; }
  .rl-head__desc { white-space: normal; }
  .rl-head__actions { margin-left: auto; }
  .rl-grid { padding: var(--sp-4); grid-template-columns: 1fr; }
  .rl-savebar { padding: var(--sp-3) var(--sp-4); }
  .rl-savebar__hint { display: none; }
  .rl-savebar__btns { width: 100%; }
  .rl-savebar__btns .btn-sig { flex: 1; }
}
</style>

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

const RL_TOTAL = <?php echo (int)$totalAsignables; ?>;
let rlEnviando = false;

function rlCard(rolId) { return document.getElementById('rol-' + rolId); }

function rlAbrir(rolId, abrir) {
    const card = rlCard(rolId);
    const body = document.getElementById('rl-body-' + rolId);
    if (!card || !body) return;
    card.classList.toggle('is-open', abrir);
    body.hidden = !abrir;
    card.querySelector('.rl-head__main').setAttribute('aria-expanded', abrir ? 'true' : 'false');
    rlSincronizarBotonTodos();
}
function rlAlternar(rolId) { rlAbrir(rolId, !rlCard(rolId).classList.contains('is-open')); }

function rlPlegables() { return Array.from(document.querySelectorAll('.rl-card:not(.rl-card--admin)')); }
function rlAlternarTodos() {
    const abrir = !rlPlegables().every(c => c.classList.contains('is-open'));
    rlPlegables().forEach(c => rlAbrir(c.dataset.rol, abrir));
}
function rlSincronizarBotonTodos() {
    const btn = document.getElementById('rl-toggle-todos');
    const todos = rlPlegables().length && rlPlegables().every(c => c.classList.contains('is-open'));
    btn.querySelector('span').textContent = todos ? 'Contraer todos' : 'Expandir todos';
    btn.querySelector('i').className = 'bi ' + (todos ? 'bi-arrows-collapse' : 'bi-arrows-expand');
}

// Recalcula contadores, barra y estado "sin guardar" de un rol.
function rlActualizar(form) {
    const rolId = form.dataset.rol;
    const boxes = Array.from(form.querySelectorAll('.rl-switch'));
    const n = boxes.filter(b => b.checked).length;
    document.getElementById('rl-count-' + rolId).textContent = n;
    document.getElementById('rl-bar-' + rolId).style.width = (RL_TOTAL ? Math.round(n * 100 / RL_TOTAL) : 0) + '%';

    form.querySelectorAll('.rl-group').forEach(g => {
        const gb = Array.from(g.querySelectorAll('.rl-switch'));
        const gn = gb.filter(b => b.checked).length;
        g.querySelector('.js-n').textContent = gn;
        g.querySelector('.rl-group__all').textContent = gn === gb.length ? 'Ninguno' : 'Todos';
    });

    const sucio = boxes.some(b => b.checked !== b.defaultChecked);
    form.classList.toggle('is-dirty', sucio);
    rlCard(rolId).classList.toggle('is-dirty', sucio);
    form.querySelector('.js-estado').textContent = sucio ? 'Cambios sin guardar' : 'Sin cambios';
    form.querySelector('.js-guardar').disabled = !sucio;
    form.querySelector('.js-descartar').disabled = !sucio;
}

function rlGrupo(btn) {
    const g = btn.closest('.rl-group');
    const boxes = Array.from(g.querySelectorAll('.rl-switch'));
    const marcar = !boxes.every(b => b.checked);
    boxes.forEach(b => b.checked = marcar);
    rlActualizar(btn.closest('form'));
}

function rlDescartar(rolId) {
    const form = document.getElementById('form-permisos-' + rolId);
    form.querySelectorAll('.rl-switch').forEach(b => b.checked = b.defaultChecked);
    rlActualizar(form);
}

document.querySelectorAll('.rl-form').forEach(form => {
    form.addEventListener('change', e => { if (e.target.classList.contains('rl-switch')) rlActualizar(form); });
    form.addEventListener('submit', () => { rlEnviando = true; });
});

// Avisar antes de salir con permisos marcados y sin guardar.
window.addEventListener('beforeunload', e => {
    if (!rlEnviando && document.querySelector('.rl-form.is-dirty')) { e.preventDefault(); e.returnValue = ''; }
});

// Al volver de guardar (#rol-N) se reabre ese rol; si no, el primero editable.
(function () {
    const m = location.hash.match(/^#rol-(\d+)$/);
    const destino = m ? m[1] : (rlPlegables()[0] ? rlPlegables()[0].dataset.rol : null);
    if (destino) rlAbrir(destino, true);
    if (m && rlCard(m[1])) rlCard(m[1]).scrollIntoView({ block: 'start' });
    rlSincronizarBotonTodos();
})();
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
