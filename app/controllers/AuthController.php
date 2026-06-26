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
                    $loggedInUser = Usuario::findByUsername($data['username']);
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
                    Usuario::registrarLoginExitoso((int)$loggedInUser->id);
                    try {
                        AuditLog::log('usuarios', 'LOGIN', (int)$loggedInUser->id, null,
                            ['username' => $loggedInUser->username], (int)$loggedInUser->id);
                    } catch (Exception $ignored) {}
                    $this->createUserSession($loggedInUser);
                    return;
                }

                // Credenciales inválidas — mensaje genérico (no revela si el usuario existe)
                if ($loggedInUser) {
                    $r = Usuario::registrarLoginFallido((int)$loggedInUser->id);
                    try {
                        AuditLog::log('usuarios', 'LOGIN_FALLIDO', (int)$loggedInUser->id, null,
                            ['username' => $loggedInUser->username, 'bloqueada' => $r['bloqueada']], (int)$loggedInUser->id);
                    } catch (Exception $ignored) {}
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
        $_SESSION['last_activity'] = time();
        header('Location: ' . URL_ROOT . '/dashboard/index');
    }

    public function logout() {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_username']);
        unset($_SESSION['user_rol']);
        session_destroy();
        header('Location: ' . URL_ROOT . '/auth/login');
    }
}
