    </main>
    
    <footer class="py-3 mt-5 text-center text-muted small border-top">
        &copy; <?php echo date('Y'); ?> IMATUR — SIGTUR v2.0 | Sistema Integral de Gestión Turística y Administrativa
    </footer>
</div>

<!-- Bootstrap 5 Bundle JS Local -->
<script src="<?php echo URL_ROOT; ?>/assets/libs/bootstrap.bundle.min.js"></script>
<!-- ApexCharts Local -->
<script src="<?php echo URL_ROOT; ?>/assets/libs/apexcharts.min.js"></script>

<!-- Contenedor Global de Toasts (Esquina Superior Derecha) -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

<script>
    /**
     * Sistema de Notificaciones Modernas (Toasts)
     * @param {string} title Título de la notificación
     * @param {string} message Cuerpo del mensaje
     * @param {string} type Tipo (success, danger, warning, info)
     */
    function showToast(title, message, type = 'success') {
        const container = document.querySelector('.toast-container');
        const id = 'toast-' + Date.now();
        
        // Mapeo e iconos automáticos
        const icons = {
            'success': 'bi-check-circle-fill',
            'danger': 'bi-x-circle-fill',
            'warning': 'bi-exclamation-triangle-fill',
            'info': 'bi-info-circle-fill'
        };
        const icon = icons[type] || icons['info'];
        
        // Títulos predeterminados si no se pasan
        const defaultTitles = {
            'success': 'Éxito',
            'danger': 'Error',
            'warning': 'Advertencia',
            'info': 'Información'
        };
        const activeTitle = title || defaultTitles[type] || 'Notificación';

        const toastHTML = `
            <div id="${id}" class="toast border-0 shadow-lg mb-2" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                <div class="toast-header text-white bg-${type} border-0">
                    <i class="bi ${icon} me-2"></i>
                    <strong class="me-auto">${activeTitle}</strong>
                    <small>Ahora</small>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body bg-white text-dark py-3">
                    ${message}
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', toastHTML);
        const toastEl = document.getElementById(id);
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
        
        // Eliminar del DOM al ocultarse
        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });
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
    function toggleSection(el) {
        el.classList.toggle('collapsed');
        var group = el.nextElementSibling;
        if (group) group.classList.toggle('collapsed');
    }

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
        document.querySelectorAll('.sb-link').forEach(function(link) {
            var href = link.getAttribute('href');
            if (!href) return;
            var linkPath = href.replace(/^https?:\/\/[^\/]+/, '').toLowerCase();
            if (currentPath.indexOf(linkPath) !== -1 && linkPath.length > 1) {
                link.classList.add('active');
                var parentGroup = link.closest('.sb-group');
                if (parentGroup) {
                    parentGroup.classList.remove('collapsed');
                    var prevSection = parentGroup.previousElementSibling;
                    if (prevSection) prevSection.classList.remove('collapsed');
                }
            }
        });

        if (window.innerWidth <= 991) {
            document.querySelectorAll('.sb-link').forEach(function(link) {
                link.addEventListener('click', closeSidebar);
            });
        }
    });
</script>
</body>
</html>
