<?php
/**
 * Helper de Sesión: Manejo de mensajes flash
 * Permite mostrar mensajes temporales al usuario tras una redirección.
 */

function flash($nombre = '', $mensaje = '', $clase = 'alert alert-success alert-dismissible fade show') {
    if (!empty($nombre)) {
        if (!empty($mensaje) && empty($_SESSION[$nombre])) {
            // Guardar el mensaje en sesión
            if (!empty($_SESSION[$nombre])) {
                unset($_SESSION[$nombre]);
            }
            if (!empty($_SESSION[$nombre . '_clase'])) {
                unset($_SESSION[$nombre . '_clase']);
            }

            $_SESSION[$nombre] = $mensaje;
            $_SESSION[$nombre . '_clase'] = $clase;
        } elseif (empty($mensaje) && !empty($_SESSION[$nombre])) {
            // Mostrar el mensaje
            $clase = !empty($_SESSION[$nombre . '_clase']) ? $_SESSION[$nombre . '_clase'] : '';
            echo '<div class="' . $clase . '" role="alert" id="msg-flash">
                    ' . $_SESSION[$nombre] . '
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>';
            unset($_SESSION[$nombre]);
            unset($_SESSION[$nombre . '_clase']);
        }
    }
}
