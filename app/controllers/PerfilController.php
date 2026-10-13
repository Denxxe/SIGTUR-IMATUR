<?php
/**
 * PerfilController — gestión de las credenciales y datos del usuario en sesión.
 * Accesible por cualquier usuario autenticado (siempre permitido en Router.php).
 */
class PerfilController extends Controller {

    public function index() {
        $userId = (int)$this->getUserId();
        $db = new Database();
        $db->query("SELECT u.id, u.username, u.id_rol, u.last_login,
                           r.nombre AS rol_nombre,
                           p.id AS id_persona,
                           COALESCE(p.nombre, '')   AS nombre,
                           COALESCE(p.apellido, '') AS apellido,
                           COALESCE(p.cedula, '')   AS cedula,
                           COALESCE(p.correo, '')   AS correo,
                           COALESCE(p.telefono, '') AS telefono,
                           COALESCE(p.direccion, '') AS direccion
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

    /**
     * Datos de contacto del propio usuario: correo, teléfono y dirección (los
     * de su ficha en `personas`). Nombre, apellido y cédula NO: son identidad
     * y los mantiene Talento Humano desde el expediente.
     *
     * Cambiar el CORREO pide la contraseña actual: es el canal de recuperación
     * de la cuenta, y una sesión olvidada abierta bastaría para secuestrarla.
     */
    public function actualizarContacto() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_ROOT . '/perfil/index'); return;
        }
        $volver = function (string $msg, string $tipo = 'danger') {
            flash('global_msg', $msg, $tipo);
            header('Location: ' . URL_ROOT . '/perfil/index');
        };
        $password = $_POST['password_actual_contacto'] ?? '';   // sin sanear: se compara tal cual
        $_POST    = $this->sanitizePost();
        $userId   = (int)$this->getUserId();

        $db = new Database();
        $db->query("SELECT u.password, p.id AS id_persona, p.correo, p.telefono, p.direccion
                    FROM usuarios u
                    LEFT JOIN empleados e ON u.id_empleado = e.id
                    LEFT JOIN personas  p ON e.id_persona  = p.id
                    WHERE u.id = :id");
        $db->bind(':id', $userId);
        $act = $db->single();
        if (!$act || empty($act->id_persona)) {
            $volver('Tu usuario no está vinculado a una ficha de personal, así que no tiene datos de contacto que editar. Pídele a Talento Humano que lo vincule.');
            return;
        }

        $correo    = mb_strtolower(trim($_POST['correo'] ?? ''));
        $telefono  = preg_replace('/\D/', '', $_POST['telefono'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');

        if ($correo !== '' && (!$this->emailValido($correo) || mb_strlen($correo) > 100)) {
            $volver('El correo no es válido. Usa el formato nombre@dominio.com.'); return;
        }
        if ($telefono !== '' && !$this->telefonoValido($telefono)) {
            $volver('El teléfono no es válido: prefijo (0412, 0414, 0416, 0424, 0426) y 7 dígitos.'); return;
        }
        if (mb_strlen($direccion) > 300) {
            $volver('La dirección es demasiado larga (máximo 300 caracteres).'); return;
        }

        $correoAntes = mb_strtolower(trim((string)$act->correo));
        if ($correo !== $correoAntes) {
            if (!password_verify($password, $act->password)) {
                $volver('Para cambiar el correo escribe tu contraseña actual (el correo sirve para recuperar la cuenta).'); return;
            }
            // Otra cuenta con el mismo correo rompería el inicio de sesión por
            // correo y la recuperación: ambas exigen que el correo sea de UNA cuenta.
            if ($correo !== '') {
                $db->query("SELECT 1 FROM usuarios u
                            JOIN empleados e ON u.id_empleado = e.id
                            JOIN personas  p ON e.id_persona  = p.id
                            WHERE LOWER(p.correo) = :c AND p.id <> :p AND u.is_active = TRUE LIMIT 1");
                $db->bind(':c', $correo); $db->bind(':p', (int)$act->id_persona);
                if ($db->single()) {
                    $volver('Ese correo ya lo usa otra cuenta del sistema. Usa uno distinto.'); return;
                }
            }
        }

        $antes   = ['correo' => $act->correo, 'telefono' => $act->telefono, 'direccion' => $act->direccion];
        $despues = ['correo' => $correo ?: null, 'telefono' => $telefono ?: null, 'direccion' => $direccion ?: null];
        if ($antes == $despues) { $volver('No había cambios que guardar.', 'info'); return; }

        $db->query("UPDATE personas SET correo = :c, telefono = :t, direccion = :d,
                           updated_at = NOW(), updated_by = :uid
                    WHERE id = :p");
        $db->bind(':c', $despues['correo']); $db->bind(':t', $despues['telefono']);
        $db->bind(':d', $despues['direccion']); $db->bind(':uid', $userId); $db->bind(':p', (int)$act->id_persona);
        $db->execute();
        try {
            AuditLog::log('personas', 'UPDATE', (int)$act->id_persona, $antes, $despues, $userId);
        } catch (Exception $e) {
            error_log('AuditLog actualizarContacto falló: ' . $e->getMessage());
        }
        $volver('Datos de contacto actualizados.', 'success');
    }

    /** Cambiar username */
    public function cambiarUsername() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_ROOT . '/perfil/index'); return;
        }
        $_POST    = $this->sanitizePost();
        $userId   = (int)$this->getUserId();
        $nuevo    = trim($_POST['username'] ?? '');

        // Sin espacios ni «@»: un usuario con forma de correo podría coincidir
        // con el correo de otra cuenta y el login por correo dejaría de saber
        // a quién corresponde.
        if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $nuevo)) {
            flash('global_msg', 'El nombre de usuario debe tener de 3 a 50 caracteres: letras, números, punto, guion o guion bajo (sin espacios ni @).', 'danger');
            header('Location: ' . URL_ROOT . '/perfil/index'); return;
        }

        $db = new Database();
        $db->query("SELECT username FROM usuarios WHERE id = :id");
        $db->bind(':id', $userId);
        $anterior = (string)($db->single()->username ?? '');
        if ($anterior === $nuevo) {
            flash('global_msg', 'Ese ya es tu nombre de usuario.', 'info');
            header('Location: ' . URL_ROOT . '/perfil/index'); return;
        }

        // Sin distinguir mayúsculas: «Admin» y «admin» confundirían a cualquiera.
        $db->query("SELECT id FROM usuarios WHERE LOWER(username) = LOWER(:u) AND id <> :id AND is_active = TRUE");
        $db->bind(':u', $nuevo); $db->bind(':id', $userId);
        if ($db->single()) {
            flash('global_msg', 'El usuario "' . htmlspecialchars($nuevo) . '" ya está en uso. Elige otro.', 'danger');
            header('Location: ' . URL_ROOT . '/perfil/index'); return;
        }

        $db->query("UPDATE usuarios SET username = :u, updated_at = NOW(), updated_by = :uid WHERE id = :id");
        $db->bind(':u', $nuevo); $db->bind(':uid', $userId); $db->bind(':id', $userId);
        $db->execute();
        try {
            AuditLog::log('usuarios', 'UPDATE', $userId, ['username' => $anterior], ['username' => $nuevo], $userId);
        } catch (Exception $e) {
            error_log('AuditLog cambiarUsername falló: ' . $e->getMessage());
        }
        $_SESSION['user_username'] = $nuevo;
        flash('global_msg', 'Nombre de usuario actualizado. Desde ahora inicia sesión con «' . htmlspecialchars($nuevo) . '».');
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
            // Antes no quedaba rastro: el cambio desde Mi perfil no se auditaba.
            try {
                AuditLog::log('usuarios', 'UPDATE', (int)$userId, null,
                    ['password_changed' => true, 'password_origen' => 'Desde «Mi perfil»'], (int)$userId);
            } catch (Exception $e) {
                error_log('AuditLog cambio de contraseña falló: ' . $e->getMessage());
            }
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
