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
<script src="<?php echo URL_ROOT; ?>/assets/js/sigtur-validations.js"></script>

<!-- Contenedor de Toasts del nuevo diseño -->
<div class="sig-toast-region" id="sigToastRegion"></div>

<script>
    /**
     * Sistema de Notificaciones (Toasts) — Diseño SIGTUR v2.0
     */
    function showToast(title, message, type = 'success') {
        const container = document.getElementById('sigToastRegion');
        const id = 'toast-' + Date.now();
        const icons = {
            'success': 'bi-check-circle-fill',
            'danger':  'bi-x-circle-fill',
            'warning': 'bi-exclamation-triangle-fill',
            'info':    'bi-info-circle-fill'
        };
        const icon = icons[type] || icons['info'];
        const defaultTitles = { 'success': 'Éxito', 'danger': 'Error', 'warning': 'Advertencia', 'info': 'Información' };
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
        // Confirmación genérica de eliminación
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('¿Está seguro de que desea eliminar este registro?')) {
                    e.preventDefault();
                }
            });
        });

        // Marcar link activo en sidebar
        var currentPath = window.location.pathname.toLowerCase();
        document.querySelectorAll('.sidebar__item').forEach(function(link) {
            var href = link.getAttribute('href');
            if (!href) return;
            var linkPath = href.replace(/^https?:\/\/[^\/]+/, '').toLowerCase();
            if (currentPath.indexOf(linkPath) !== -1 && linkPath.length > 1) {
                link.classList.add('is-active');
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