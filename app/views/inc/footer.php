    </main>
    <footer class="footer mt-auto py-3 bg-light border-top fixed-bottom">
        <div class="container text-center text-muted small">
            &copy; <?php echo date('Y'); ?> IMATUR - SIGTUR. Todos los derechos reservados.
        </div>
    </footer>
    
    <!-- Bootstrap 5 Bundle JS Local -->
    <script src="<?php echo URL_ROOT; ?>/assets/libs/bootstrap.bundle.min.js"></script>
    
    <!-- Scripts globales o personalizados -->
    <script>
        // Funciones auxiliares genéricas para CRUD
        document.addEventListener('DOMContentLoaded', function() {
            // Confirmación genérica antes de eliminar
            const deleteButtons = document.querySelectorAll('.delete-btn');
            deleteButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    if (!confirm('¿Está seguro de que desea eliminar este registro?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>
