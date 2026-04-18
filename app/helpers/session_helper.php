<?php
/**
 * Helper de Sesión: Manejo de notificaciones (Toasts)
 * Permite mostrar mensajes dinámicos al usuario tras una redirección.
 */

/**
 * Función Flash mejorada para usar Toasts dinámicos
 * @param string $nombre  Nombre de la sesión (ej: 'msg_taller')
 * @param string $mensaje Contenido del mensaje. Si está vacío, busca mostrar el mensaje guardado.
 * @param string $clase   Tipo de notificación (success, danger, warning, info)
 */
function flash($nombre = '', $mensaje = '', $clase = 'success') {
    if (!empty($nombre)) {
        // Guardar mensaje en sesión
        if (!empty($mensaje)) {
            if (!empty($_SESSION[$nombre])) {
                unset($_SESSION[$nombre]);
            }
            if (!empty($_SESSION[$nombre . '_clase'])) {
                unset($_SESSION[$nombre . '_clase']);
            }

            $_SESSION[$nombre] = $mensaje;
            $_SESSION[$nombre . '_clase'] = str_replace('alert alert-', '', $clase); // Limpieza por si envían clases antiguas
        } 
        // Mostrar mensaje si existe en sesión
        elseif (empty($mensaje) && !empty($_SESSION[$nombre])) {
            $tipo = !empty($_SESSION[$nombre . '_clase']) ? $_SESSION[$nombre . '_clase'] : 'success';
            
            // Determinar título automático
            $titulo = ($tipo == 'success') ? "Operación Exitosa" : (($tipo == 'danger') ? "Error en el Sistema" : "Aviso");
            
            // Inyectar el script que llama a la función global showToast del footer.php
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    showToast('$titulo', '{$_SESSION[$nombre]}', '$tipo');
                });
            </script>";

            unset($_SESSION[$nombre]);
            unset($_SESSION[$nombre . '_clase']);
        }
    }
}
