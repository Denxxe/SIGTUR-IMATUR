<?php
/**
 * Controlador AuthController - Manejo de Sesiones
 */
class AuthController extends Controller {

    public function index() {
        $this->login();
    }

    public function login() {
        // Redirigir si ya está logueado
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . URL_ROOT . '/dashboard/index');
            exit;
        }

        $data = [
            'username' => '',
            'password' => '',
            'username_err' => '',
            'password_err' => '',
            'login_err'  => '',
            'login_warn' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = $this->sanitizePost();

            $data['username'] = trim($_POST['username']);
            $data['password'] = trim($_POST['password']);

            if (empty($data['username'])) {
                $data['username_err'] = 'Por favor ingrese su usuario';
            }
            if (empty($data['password'])) {
                $data['password_err'] = 'Por favor ingrese su contraseña';
            }

            if (empty($data['username_err']) && empty($data['password_err'])) {
                try {
                    // Incluye cuentas desactivadas: si las credenciales son
                    // correctas hay que avisar del estado, no decir «usuario o
                    // contraseña incorrectos» (eso dejaba al usuario a ciegas).
                    $loggedInUser = Usuario::findParaLogin($data['username']);
                } catch (Exception $e) {
                    $data['login_err'] = 'Error de conexión con el sistema. Intente más tarde.';
                    $this->view('auth/login', $data);
                    return;
                }

                // Cuenta bloqueada temporalmente por intentos fallidos
                if ($loggedInUser && ($min = Usuario::bloqueoRestante($loggedInUser)) > 0) {
                    $data['login_err'] = 'Cuenta bloqueada temporalmente por seguridad. Intenta de nuevo en '
                        . $min . ' minuto(s).';
                    $this->view('auth/login', $data);
                    return;
                }

                if ($loggedInUser && password_verify($data['password'], $loggedInUser->password)) {
                    // Credenciales correctas pero cuenta desactivada (suspendida
                    // por el administrador o por egreso de su titular): no entra,
                    // pero sí se le dice por qué.
                    if (!(int)($loggedInUser->activo ?? 1)) {
                        try {
                            AuditLog::log('usuarios', 'LOGIN_INACTIVO', (int)$loggedInUser->id, null,
                                ['username' => $loggedInUser->username], (int)$loggedInUser->id);
                        } catch (Exception $e) {
                            error_log('AuditLog LOGIN_INACTIVO falló para usuario ' . $loggedInUser->username . ': ' . $e->getMessage());
                        }
                        $data['login_warn'] = 'Tu usuario está INACTIVO. La cuenta fue suspendida o su titular '
                            . 'figura como egresado, por lo que no puede iniciar sesión. '
                            . 'Comunícate con el Administrador del sistema para reactivarla.';
                        $this->view('auth/login', $data);
                        return;
                    }
                    Usuario::registrarLoginExitoso((int)$loggedInUser->id);
                    try {
                        AuditLog::log('usuarios', 'LOGIN', (int)$loggedInUser->id, null,
                            ['username' => $loggedInUser->username], (int)$loggedInUser->id);
                    } catch (Exception $e) {
                        error_log('AuditLog LOGIN falló para usuario ' . $loggedInUser->username . ': ' . $e->getMessage());
                    }
                    $this->createUserSession($loggedInUser);
                    return;
                }

                // Credenciales inválidas — mensaje genérico (no revela si el usuario existe)
                if ($loggedInUser) {
                    $r = Usuario::registrarLoginFallido((int)$loggedInUser->id);
                    try {
                        AuditLog::log('usuarios', 'LOGIN_FALLIDO', (int)$loggedInUser->id, null,
                            ['username' => $loggedInUser->username, 'bloqueada' => $r['bloqueada']], (int)$loggedInUser->id);
                    } catch (Exception $e) {
                        error_log('AuditLog LOGIN_FALLIDO falló para usuario ' . $loggedInUser->username . ': ' . $e->getMessage());
                    }
                    if ($r['bloqueada']) {
                        $data['login_err'] = 'Demasiados intentos fallidos. La cuenta quedó bloqueada por '
                            . Usuario::BLOQUEO_MINUTOS . ' minutos.';
                    } elseif ($r['restantes'] <= 2) {
                        $data['login_err'] = 'Usuario o contraseña incorrectos. Te queda(n) '
                            . $r['restantes'] . ' intento(s) antes del bloqueo.';
                    } else {
                        $data['login_err'] = 'Usuario o contraseña incorrectos.';
                    }
                } else {
                    $data['login_err'] = 'Usuario o contraseña incorrectos.';
                }
                $this->view('auth/login', $data);
            } else {
                $this->view('auth/login', $data);
            }
        } else {
            $this->view('auth/login', $data);
        }
    }

    public function createUserSession($user) {
        // Renueva el id de sesión al autenticar (mitiga fijación de sesión)
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_username'] = $user->username;
        $_SESSION['user_rol'] = $user->id_rol;
        $_SESSION['user_empleado_id'] = isset($user->id_empleado) ? (int)$user->id_empleado : null;
        $_SESSION['last_activity'] = time();
        header('Location: ' . URL_ROOT . '/dashboard/index');
    }

    public function logout() {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_username']);
        unset($_SESSION['user_rol']);
        unset($_SESSION['user_empleado_id']);
        session_destroy();
        header('Location: ' . URL_ROOT . '/auth/login');
    }

    // ── Recuperación de contraseña por correo (autoservicio) ─────────────────
    // El reset manual por Administrador (Sistema → Usuarios) sigue existiendo
    // para cuentas sin correo registrado.

    public function olvidoPassword() {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . URL_ROOT . '/dashboard/index');
            exit;
        }
        $this->view('auth/olvido', ['identificador' => '', 'identificador_err' => '', 'enviado' => false]);
    }

    public function enviarRecuperacion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_ROOT . '/auth/olvidoPassword');
            exit;
        }

        $_POST = $this->sanitizePost();
        $identificador = trim($_POST['identificador'] ?? '');

        if (empty($identificador)) {
            $this->view('auth/olvido', ['identificador' => '', 'identificador_err' => 'Ingresa tu usuario o correo.', 'enviado' => false]);
            return;
        }

        try {
            $usuario = Usuario::findByUsernameOrEmail($identificador);
            if ($usuario && !empty($usuario->correo)) {
                $token = PasswordReset::generar((int)$usuario->id, $_SERVER['REMOTE_ADDR'] ?? null);
                if ($token) {
                    $enlace  = URL_ROOT . '/auth/resetPassword?token=' . $token;
                    $nombre  = trim((string)($usuario->nombre_persona ?? ''));
                    $saludo  = $nombre !== '' ? 'Hola, ' . htmlspecialchars(ucwords(mb_strtolower($nombre))) . ':' : 'Hola:';
                    $minutos = PasswordReset::TTL_MINUTOS;
                    $cuerpo  = sigtur_plantilla_correo(
                        'Restablece tu contraseña',
                        '<p style="margin:0 0 14px;">' . $saludo . '</p>'
                        . '<p style="margin:0 0 14px;">Recibimos una solicitud para restablecer la contraseña de tu cuenta <strong>'
                        . htmlspecialchars($usuario->username) . '</strong> en SIGTUR-IMATUR. Pulsa el botón para elegir una nueva.</p>'
                        . '<p style="margin:0;padding:10px 14px;background:#eef4ff;border-radius:8px;font-size:14px;color:#1c39b0;">'
                        . 'El enlace es válido por <strong>' . $minutos . ' minutos</strong> y solo puede usarse una vez.</p>',
                        ['texto' => 'Restablecer contraseña', 'url' => $enlace],
                        'Enlace válido por ' . $minutos . ' minutos para restablecer tu contraseña.',
                        '<p style="margin:0 0 12px;">¿El botón no funciona? Copia y pega este enlace en tu navegador:<br>'
                        . '<a href="' . htmlspecialchars($enlace) . '" style="color:#2247db;word-break:break-all;">' . htmlspecialchars($enlace) . '</a></p>'
                        . '<p style="margin:0;">Si no solicitaste este cambio, ignora este correo: tu contraseña actual sigue funcionando.</p>'
                    );
                    $texto = "Restablece tu contraseña de SIGTUR-IMATUR (cuenta {$usuario->username}).\n\n"
                           . "Abre este enlace (válido por {$minutos} minutos, un solo uso):\n{$enlace}\n\n"
                           . "Si no solicitaste este cambio, ignora este correo.";
                    sigtur_enviar_correo($usuario->correo, 'Restablece tu contraseña · SIGTUR-IMATUR', $cuerpo, $texto);
                }
            }
        } catch (Exception $e) {
            error_log('[SIGTUR] Error en recuperación de contraseña: ' . $e->getMessage());
        }

        // Mismo mensaje exista o no la cuenta (anti-enumeración, igual que login()).
        $this->view('auth/olvido', ['identificador' => '', 'identificador_err' => '', 'enviado' => true]);
    }

    public function resetPassword() {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . URL_ROOT . '/dashboard/index');
            exit;
        }

        $token = trim($_GET['token'] ?? '');
        $reset = $token !== '' ? PasswordReset::validar($token) : null;

        if (!$reset) {
            header('Location: ' . URL_ROOT . '/auth/login?reset_invalido=1');
            exit;
        }

        $this->view('auth/reset', ['token' => $token, 'password_err' => '', 'confirm_err' => '']);
    }

    public function procesarReset() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_ROOT . '/auth/login');
            exit;
        }

        $_POST = $this->sanitizePost();
        $token = trim($_POST['token'] ?? '');
        $reset = $token !== '' ? PasswordReset::validar($token) : null;

        if (!$reset) {
            header('Location: ' . URL_ROOT . '/auth/login?reset_invalido=1');
            exit;
        }

        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';
        $data = ['token' => $token, 'password_err' => Usuario::passwordPolicyError($password) ?? '', 'confirm_err' => ''];

        if (empty($data['password_err']) && $password !== $confirm) {
            $data['confirm_err'] = 'Las contraseñas no coinciden.';
        }

        if (!empty($data['password_err']) || !empty($data['confirm_err'])) {
            $this->view('auth/reset', $data);
            return;
        }

        Usuario::actualizarPassword((int)$reset->id_usuario, $password);
        PasswordReset::marcarUsado((int)$reset->id_usuario);
        header('Location: ' . URL_ROOT . '/auth/login?reset_ok=1');
        exit;
    }
}
