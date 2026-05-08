<?php
/**
 * Controlador UsuariosController
 */
class UsuariosController extends Controller {

    public function index() {
        $usuarios = Usuario::all();
        $roles = Rol::all();
        $empleados = Empleado::all(); // Solo empleados sin usuario en una app real

        $data = [
            'titulo' => 'Seguridad: Gestión de Usuarios',
            'usuarios' => $usuarios,
            'roles' => $roles,
            'empleados' => $empleados
        ];

        $this->view('usuarios/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = $this->sanitizePost();
            
            $data = [
                'id' => isset($_POST['id']) ? (int)$_POST['id'] : null,
                'id_empleado' => isset($_POST['id_empleado']) ? (int)$_POST['id_empleado'] : null,
                'id_rol' => (int)$_POST['id_rol'],
                'username' => trim($_POST['username']),
                'password' => $_POST['password'] // El modelo lo hasheará
            ];

            $usuario = new Usuario($data);

            if ($usuario->save($this->getUserId())) {
                header('Location: ' . URL_ROOT . '/usuarios/index');
            } else {
                die('Error al guardar el usuario');
            }
        }
    }

    public function delete($id) {
        if (Usuario::delete($id, $this->getUserId())) {
            header('Location: ' . URL_ROOT . '/usuarios/index');
        } else {
            die('Error al eliminar');
        }
    }
}
