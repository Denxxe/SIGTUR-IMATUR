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
            'password_err' => ''
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
                    $data['username_err'] = 'Error de conexión con el sistema. Intente más tarde.';
                    $this->view('auth/login', $data);
                    return;
                }

                if ($loggedInUser) {
                    if (password_verify($data['password'], $loggedInUser->password)) {
                        $this->createUserSession($loggedInUser);
                    } else {
                        $data['password_err'] = 'Contraseña incorrecta';
                        $this->view('auth/login', $data);
                    }
                } else {
                    $data['username_err'] = 'No se encontró el usuario';
                    $this->view('auth/login', $data);
                }
            } else {
                $this->view('auth/login', $data);
            }
        } else {
            $this->view('auth/login', $data);
        }
    }

    public function createUserSession($user) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_username'] = $user->username;
        $_SESSION['user_rol'] = $user->id_rol;
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
