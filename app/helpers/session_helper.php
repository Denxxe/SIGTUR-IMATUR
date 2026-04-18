<?php
/**
 * Helper de Sesión: Manejo de notificaciones (Toasts)
 * Permite mostrar mensajes dinámicos al usuario tras una redirección.
 */

/**
 * Función Flash mejorada para usar Toasts dinámicos
 * @param string $nombre  Nombre de la sesión (ej: 'global_msg')
 * @param string $mensaje Contenido del mensaje. Si está vacío, busca mostrar el mensaje guardado.
 * @param string $clase   Tipo de notificación (success, danger, warning, info)
 */
function flash($nombre = 'global_msg', $mensaje = '', $clase = 'success') {
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
            // Normalizar clase (aceptar alert-success o solo success)
            $tipo = str_replace(['alert', 'alert-', ' '], '', $clase);
            $_SESSION[$nombre . '_clase'] = $tipo;
        } 
        // Mostrar mensaje si existe en sesión
        elseif (empty($mensaje) && !empty($_SESSION[$nombre])) {
            $tipo = !empty($_SESSION[$nombre . '_clase']) ? $_SESSION[$nombre . '_clase'] : 'success';
            
            // Determinar título automático
            $titulos = [
                'success' => 'Operación Exitosa',
                'danger' => 'Error en el Sistema',
                'warning' => 'Advertencia',
                'info' => 'Información'
            ];
            $titulo = $titulos[$tipo] ?? 'Notificación';
            
            // Escapar datos para JS usando json_encode para máxima seguridad
            $jsTitulo = json_encode($titulo);
            $jsMensaje = json_encode($_SESSION[$nombre]);
            $jsTipo = json_encode($tipo);
            
            // Inyectar el script (esperar a que el DOM esté listo para asegurar que showToast exista)
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof showToast === 'function') {
                        showToast($jsTitulo, $jsMensaje, $jsTipo);
                    } else {
                        console.error('Sistema de Toasts no inicializado (showToast no encontrada)');
                    }
                });
            </script>";

            unset($_SESSION[$nombre]);
            unset($_SESSION[$nombre . '_clase']);
        }
    }
}
