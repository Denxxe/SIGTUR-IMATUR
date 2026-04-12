<?php
/**
 * Controlador RolesController
 */
class RolesController extends Controller {

    public function index() {
        $roles = Rol::all();
        $data = [
            'titulo' => 'Gestión de Roles',
            'roles' => $roles
        ];
        $this->view('roles/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $data = [
                'id' => $id,
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion'])
            ];

            $rol = new Rol($data);
            if ($rol->save($this->getUserId())) { // ID temporal
                header('Location: ' . URL_ROOT . '/roles/index');
            } else {
                die('Error al guardar el rol');
            }
        }
    }

    public function delete($id) {
        if (Rol::delete($id, $this->getUserId())) {
            header('Location: ' . URL_ROOT . '/roles/index');
        } else {
            die('Error al eliminar');
        }
    }
}
