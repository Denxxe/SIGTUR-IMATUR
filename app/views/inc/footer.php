    </main>
    
    <footer class="py-3 mt-5 text-center text-muted small border-top">
        &copy; <?php echo date('Y'); ?> IMATUR — SIGTUR v2.0 | Sistema Integral de Gestión Turística y Administrativa
    </footer>
</div>

<!-- Bootstrap 5 Bundle JS Local -->
<script src="<?php echo URL_ROOT; ?>/assets/libs/bootstrap.bundle.min.js"></script>

<script>
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
        const currentPath = window.location.pathname;
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            if (currentPath.includes(link.getAttribute('href').replace('<?php echo URL_ROOT; ?>', ''))) {
                link.classList.add('active');
            }
        });
    });
</script>
</body>
</html>
