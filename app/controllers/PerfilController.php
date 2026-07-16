<?php
/**
 * PerfilController — gestión de las credenciales y datos del usuario en sesión.
 * Accesible por cualquier usuario autenticado (siempre permitido en Router.php).
 */
class PerfilController extends Controller {

    public function index() {
        $userId = (int)$this->getUserId();
        $db = new Database();
        $db->query("SELECT u.id, u.username, u.id_rol,
                           r.nombre AS rol_nombre,
                           COALESCE(p.nombre, '')   AS nombre,
                           COALESCE(p.apellido, '') AS apellido,
                           COALESCE(p.cedula, '')   AS cedula,
                           COALESCE(p.correo, '')   AS correo,
                           COALESCE(p.telefono, '') AS telefono
                    FROM usuarios u
                    INNER JOIN roles r ON u.id_rol = r.id
                    LEFT  JOIN empleados e  ON u.id_empleado = e.id
                    LEFT  JOIN personas  p  ON e.id_persona  = p.id
                    WHERE u.id = :id");
        $db->bind(':id', $userId);
        $usuario = $db->single();

        $data = [
            'titulo'  => 'Mi Perfil',
            'usuario' => $usuario,
        ];
        $this->view('perfil/index', $data);
    }

    /** Cambiar username */
    public function cambiarUsername() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_ROOT . '/perfil/index'); return;
        }
        $_POST    = $this->sanitizePost();
        $userId   = (int)$this->getUserId();
        $nuevo    = trim($_POST['username'] ?? '');

        if (mb_strlen($nuevo) < 3) {
            flash('global_msg', 'El nombre de usuario debe tener al menos 3 caracteres.', 'danger');
            header('Location: ' . URL_ROOT . '/perfil/index'); return;
        }

        $db = new Database();
        $db->query("SELECT id FROM usuarios WHERE username = :u AND id <> :id AND is_active = TRUE");
        $db->bind(':u', $nuevo); $db->bind(':id', $userId);
        if ($db->single()) {
            flash('global_msg', 'El usuario "' . htmlspecialchars($nuevo) . '" ya está en uso. Elige otro.', 'danger');
            header('Location: ' . URL_ROOT . '/perfil/index'); return;
        }

        $db->query("UPDATE usuarios SET username = :u, updated_at = NOW(), updated_by = :uid WHERE id = :id");
        $db->bind(':u', $nuevo); $db->bind(':uid', $userId); $db->bind(':id', $userId);
        $db->execute();
        $_SESSION['user_username'] = $nuevo;
        flash('global_msg', 'Nombre de usuario actualizado correctamente.');
        header('Location: ' . URL_ROOT . '/perfil/index');
    }

    /** Cambiar contraseña */
    public function cambiarPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_ROOT . '/perfil/index'); return;
        }
        $_POST   = $this->sanitizePost();
        $userId  = (int)$this->getUserId();
        $actual  = $_POST['password_actual']    ?? '';
        $nueva   = $_POST['password_nuevo']     ?? '';
        $repite  = $_POST['password_confirmar'] ?? '';

        $db = new Database();
        $db->query("SELECT password FROM usuarios WHERE id = :id");
        $db->bind(':id', $userId);
        $row = $db->single();

        if (!$row || !password_verify($actual, $row->password)) {
            flash('global_msg', 'La contraseña actual no es correcta.', 'danger');
        } elseif (($err = Usuario::passwordPolicyError($nueva)) !== null) {
            flash('global_msg', $err, 'danger');
        } elseif ($nueva !== $repite) {
            flash('global_msg', 'Las contraseñas nuevas no coinciden.', 'danger');
        } else {
            $hash = password_hash($nueva, PASSWORD_BCRYPT);
            $db->query("UPDATE usuarios SET password = :p, updated_at = NOW(), updated_by = :uid WHERE id = :id");
            $db->bind(':p', $hash); $db->bind(':uid', $userId); $db->bind(':id', $userId);
            $db->execute();
            flash('global_msg', 'Contraseña actualizada correctamente.');
        }
        header('Location: ' . URL_ROOT . '/perfil/index');
    }

    /**
     * Keep-alive de sesión: sin efecto propio, solo llegar aquí ya hace que el
     * Router refresque $_SESSION['last_activity']. Usado por formularios largos
     * (ej. wizard de empleados) para evitar expirar la sesión por inactividad
     * mientras el usuario sigue escribiendo, sin bajar SESSION_TIMEOUT global.
     */
    public function ping() {
        header('Content-Type: application/json');
        header('Cache-Control: no-store');
        echo json_encode(['ok' => true]);
    }
}
