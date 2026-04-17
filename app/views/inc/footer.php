    </main>
    
    <footer class="py-3 mt-5 text-center text-muted small border-top">
        &copy; <?php echo date('Y'); ?> IMATUR — SIGTUR v2.0 | Sistema Integral de Gestión Turística y Administrativa
    </footer>
</div>

<!-- Bootstrap 5 Bundle JS Local -->
<script src="<?php echo URL_ROOT; ?>/assets/libs/bootstrap.bundle.min.js"></script>
<!-- ApexCharts Local -->
<script src="<?php echo URL_ROOT; ?>/assets/libs/apexcharts.min.js"></script>

<script>
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
            // Extraer la parte después del dominio
            var linkPath = href.replace(/^https?:\/\/[^\/]+/, '').toLowerCase();
            if (currentPath.indexOf(linkPath) !== -1 && linkPath.length > 1) {
                link.classList.add('active');
                // Asegurar que la sección padre esté expandida
                var parentGroup = link.closest('.sb-group');
                if (parentGroup) {
                    parentGroup.classList.remove('collapsed');
                    var prevSection = parentGroup.previousElementSibling;
                    if (prevSection) prevSection.classList.remove('collapsed');
                }
            }
        });

        // En móvil: cerrar sidebar al hacer clic en un link
        if (window.innerWidth <= 991) {
            document.querySelectorAll('.sb-link').forEach(function(link) {
                link.addEventListener('click', closeSidebar);
            });
        }
    });
</script>
</body>
</html>
