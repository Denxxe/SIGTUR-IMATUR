        </div><!-- /.page -->

        <footer class="sig-footer">
            &copy; <?php echo date('Y'); ?> IMATUR — SIGTUR v2.0 | Sistema Integral de Gestión Turística y Administrativa
        </footer>
        </div><!-- /.main-area -->
        </div><!-- /.app-shell -->

        <!-- Bootstrap 5 Bundle JS Local -->
        <script src="<?php echo URL_ROOT; ?>/assets/libs/bootstrap.bundle.min.js"></script>
        <!-- ApexCharts Local -->
        <script src="<?php echo URL_ROOT; ?>/assets/libs/apexcharts.min.js"></script>
        <!-- Validador Global de Formularios -->
        <script src="<?php echo URL_ROOT; ?>/assets/js/sigtur-validations.js?v=<?php echo filemtime('../public/assets/js/sigtur-validations.js'); ?>"></script>

        <!-- Contenedor de Toasts del nuevo diseño -->
        <div class="sig-toast-region" id="sigToastRegion"></div>

        <!-- ==================== MODAL CONFIRMACIÓN ELIMINACIÓN ==================== -->
        <div class="modal fade" id="modalConfirmDelete" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
                <div class="modal-content" style="border:none; overflow:hidden;">
                    <div class="modal-header" style="background:var(--danger-50,#fef2f2); border-bottom:1px solid var(--danger-100,#fecaca); padding:16px 20px;">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div style="width:38px; height:38px; border-radius:50%; background:var(--danger-100,#fecaca);
                                        display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <i class="bi bi-trash3" style="color:var(--danger-600,#dc2626); font-size:17px;"></i>
                            </div>
                            <h5 class="modal-title" id="modalDeleteTitle"
                                style="font-size:15px; font-weight:700; color:var(--danger-700,#b91c1c); margin:0;">
                                Confirmar eliminación
                            </h5>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body" style="padding:20px;">
                        <p id="modalDeleteMsg"
                           style="font-size:14px; font-weight:500; color:var(--text-primary); margin-bottom:10px;"></p>
                        <p id="modalDeleteSub"
                           style="font-size:12px; color:var(--text-tertiary); margin:0; display:flex; align-items:center; gap:6px;">
                        </p>
                    </div>
                    <div class="modal-footer" style="padding:12px 20px; border-top:1px solid var(--border-subtle); gap:8px;">
                        <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg"></i> Cancelar
                        </button>
                        <a id="modalDeleteConfirm" href="#" class="btn-sig btn-sig--danger">
                            <i class="bi bi-trash3"></i> <span id="modalDeleteAction">Eliminar</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // ==================== IDEMPOTENCIA / ANTI DOBLE-ENVÍO (B10) ====================
            // Token de un solo uso emitido por el servidor; se inyecta en cada formulario
            // POST y el backend lo consume (Router). Un reenvío con el mismo token se ignora.
            window.SIGTUR_TOKEN = <?php echo json_encode(sigtur_token_emitir()); ?>;
            // RIF institucional (fuente única) para los exportadores del lado cliente.
            window.SIGTUR_RIF = <?php echo json_encode(ConfigSistema::rif()); ?>;

            // Inyecta el token en todos los formularios POST (salvo data-no-token).
            function sigturInjectTokens() {
                document.querySelectorAll('form[method="post"], form[method="POST"]').forEach(function(form) {
                    if (form.hasAttribute('data-no-token')) return;
                    if (form.querySelector('input[name="_token"]')) return;
                    var inp = document.createElement('input');
                    inp.type = 'hidden'; inp.name = '_token'; inp.value = window.SIGTUR_TOKEN;
                    form.appendChild(inp);
                });
            }

            // Guard de doble-envío: bloquea reenviar el mismo formulario (doble clic).
            document.addEventListener('submit', function(e) {
                var form = e.target;
                if (!form || form.tagName !== 'FORM') return;
                if ((form.method || '').toLowerCase() !== 'post') return;        // sólo POST
                if (form.hasAttribute('data-allow-multi-submit')) return;
                if (form.dataset.sigSubmitting === '1') { e.preventDefault(); e.stopPropagation(); return; }
                if (typeof form.checkValidity === 'function' && !form.checkValidity()) return; // deja ver errores
                form.dataset.sigSubmitting = '1';
                setTimeout(function() {
                    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function(b) { b.disabled = true; });
                }, 50);
            }, false);

            /**
             * Sistema de Notificaciones (Toasts) — Diseño SIGTUR v2.0
             */
            function showToast(title, message, type = 'success') {
                const container = document.getElementById('sigToastRegion');
                const id = 'toast-' + Date.now();
                const icons = {
                    'success': 'bi-check-circle-fill',
                    'danger': 'bi-x-circle-fill',
                    'warning': 'bi-exclamation-triangle-fill',
                    'info': 'bi-info-circle-fill'
                };
                const icon = icons[type] || icons['info'];
                const defaultTitles = {
                    'success': 'Éxito',
                    'danger': 'Error',
                    'warning': 'Advertencia',
                    'info': 'Información'
                };
                const activeTitle = title || defaultTitles[type] || 'Notificación';

                const toastHTML = `
            <div id="${id}" class="sig-toast sig-toast--${type}">
                <div class="sig-toast__icon"><i class="bi ${icon}"></i></div>
                <div style="flex:1;min-width:0">
                    <div class="sig-toast__title">${activeTitle}</div>
                    <div class="sig-toast__msg">${message}</div>
                </div>
                <button class="sig-toast__close" onclick="this.closest('.sig-toast').remove()">
                    <i class="bi bi-x"></i>
                </button>
                <div class="sig-toast__progress"></div>
            </div>
        `;
                container.insertAdjacentHTML('beforeend', toastHTML);
                setTimeout(() => {
                    const el = document.getElementById(id);
                    if (el) el.remove();
                }, 5000);
            }

            // ==================== SIDEBAR FUNCTIONS ====================
            function toggleSidebar() {
                document.getElementById('sidebar').classList.toggle('open');
                document.getElementById('sbOverlay').classList.toggle('show');
            }

            function closeSidebar() {
                document.getElementById('sidebar').classList.remove('open');
                document.getElementById('sbOverlay').classList.remove('show');
            }

            // ==================== DARK MODE TOGGLE ====================
            function toggleTheme() {
                const html = document.documentElement;
                const current = html.getAttribute('data-theme');
                const next = current === 'dark' ? 'light' : 'dark';
                html.setAttribute('data-theme', next);
                localStorage.setItem('sigtur-theme', next);
                updateThemeIcon(next);
            }

            function updateThemeIcon(theme) {
                const icon = document.getElementById('themeIcon');
                if (icon) {
                    icon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon';
                }
            }
            // Restaurar tema guardado
            (function() {
                const saved = localStorage.getItem('sigtur-theme');
                if (saved) {
                    document.documentElement.setAttribute('data-theme', saved);
                    updateThemeIcon(saved);
                }
            })();

            // ==================== DOMContentLoaded ====================
            document.addEventListener('DOMContentLoaded', function() {

                // ── Idempotencia: inyectar token en formularios POST ──────
                sigturInjectTokens();

                // ── Modal de confirmación de eliminación ──────────────────
                const _delModal = new bootstrap.Modal(document.getElementById('modalConfirmDelete'));

                // Textos y subtítulos según el controlador detectado en la URL
                const _delContexts = {
                    'usuarios':              { title: 'Suspender usuario',       action: 'Suspender',    sub: 'El usuario perderá acceso al sistema. Puede reactivarse desde la Papelera.' },
                    'inventario':            { title: 'Dar de baja el bien',      action: 'Dar de baja',  sub: 'El bien será registrado como inactivo. Puede restaurarse desde la Papelera.' },
                    'empleados':             { title: 'Desactivar empleado',      action: 'Desactivar',   sub: 'El empleado pasará a inactivo. Puede restaurarse desde la Papelera.' },
                    'desinscribir':          { title: 'Quitar participante',      action: 'Quitar',       sub: 'El participante será removido de esta actividad.' },
                    'deletepunto':           { title: 'Eliminar parada',          action: 'Eliminar',     sub: 'La parada será removida de la ruta.' },
                    'deleteinventario':      { title: 'Desvincular equipamiento', action: 'Desvincular',  sub: 'El recurso será desvinculado de esta ruta.' },
                };
                const _delDefault = { title: 'Confirmar eliminación', action: 'Eliminar',
                    sub: 'El registro pasará a la Papelera y podrá restaurarse desde Auditoría → Papelera.' };

                document.querySelectorAll('.delete-btn').forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();

                        // Detectar contexto desde la URL del botón
                        var href   = this.getAttribute('href') || '#';
                        var parts  = href.replace(/^.*\/\/[^/]+/, '').split('/').filter(Boolean);
                        // Busca la acción: "delete", "desinscribir", "deletePunto", etc.
                        var action = '';
                        for (var i = 0; i < parts.length; i++) {
                            if (/delete|desinscribir/i.test(parts[i])) {
                                action = parts[i].toLowerCase() + (parts[i-1] ? '' : '');
                                // controller/action → combinación
                                action = (parts[i-1] || '') + '/' + parts[i].toLowerCase();
                                break;
                            }
                        }
                        // Buscar contexto: primero la acción completa, luego solo el controller
                        var ctx = null;
                        for (var key in _delContexts) {
                            if (action.includes(key)) { ctx = _delContexts[key]; break; }
                        }
                        ctx = ctx || _delDefault;

                        // Detectar nombre del registro: data-nombre → .cell-strong en <tr> → h3 en .sig-card
                        var nombre = this.dataset.nombre || '';
                        if (!nombre) {
                            var tr = this.closest('tr');
                            if (tr) nombre = (tr.querySelector('.cell-strong, td:first-child') || {}).textContent || '';
                        }
                        if (!nombre) {
                            var card = this.closest('.sig-card');
                            if (card) nombre = (card.querySelector('h3, .sig-card__title') || {}).textContent || '';
                        }
                        nombre = nombre.trim().replace(/\s+/g, ' ').substring(0, 60);

                        // Rellenar modal
                        document.getElementById('modalDeleteTitle').textContent  = ctx.title;
                        document.getElementById('modalDeleteAction').textContent = ctx.action;
                        document.getElementById('modalDeleteMsg').textContent    = nombre
                            ? '¿Estás seguro de que deseas ' + ctx.action.toLowerCase() + ' "' + nombre + '"?'
                            : '¿Estás seguro de que deseas ' + ctx.action.toLowerCase() + ' este registro?';
                        document.getElementById('modalDeleteSub').innerHTML =
                            '<i class="bi bi-recycle" style="color:var(--warning-500);margin-right:4px;"></i>' + ctx.sub;
                        document.getElementById('modalDeleteConfirm').setAttribute('href', href);

                        _delModal.show();
                    });
                });

                // Marcar link activo en sidebar
                var currentPath = window.location.pathname.toLowerCase().replace(/\/$/, "");
                document.querySelectorAll('.sidebar__item').forEach(function(link) {
                    var href = link.getAttribute('href');
                    if (!href) return;

                    // Limpiamos la URL del enlace para comparar
                    var linkPath = href.replace(/^https?:\/\/[^\/]+/, "").toLowerCase().replace(/\/$/, "");

                    // Lógica de coincidencia:
                    // 1. Si el linkPath es el mismo que el currentPath (coincidencia exacta)
                    // 2. Si es el Panel Principal (ruta base), solo marcar si es exactamente la base
                    // 3. Para otros módulos, marcar si el currentPath comienza con el linkPath
                    var isDashboard = (linkPath.split('/').length <= 2); // Detecta si es la raíz p.ej. /sigtur-imatur

                    if (isDashboard) {
                        if (currentPath === linkPath) link.classList.add('is-active');
                    } else {
                        if (currentPath.startsWith(linkPath)) link.classList.add('is-active');
                    }
                });


                // Cerrar sidebar en móvil al navegar
                if (window.innerWidth <= 991) {
                    document.querySelectorAll('.sidebar__item').forEach(function(link) {
                        link.addEventListener('click', closeSidebar);
                    });
                }
            });
        </script>
        </body>

        </html>