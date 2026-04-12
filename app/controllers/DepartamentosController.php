<?php
/**
 * Controlador DepartamentosController
 */
class DepartamentosController extends Controller {

    public function index() {
        $departamentos = Departamento::all();
        $data = [
            'titulo' => 'Estructura Organizativa (Departamentos)',
            'departamentos' => $departamentos
        ];
        $this->view('departamentos/index', $data);
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

            $dpto = new Departamento($data);
            if ($dpto->save($this->getUserId())) {
                header('Location: ' . URL_ROOT . '/departamentos/index');
            } else {
                die('Error al guardar el departamento');
            }
        }
    }

    public function delete($id) {
        if (Departamento::delete($id, $this->getUserId())) {
            header('Location: ' . URL_ROOT . '/departamentos/index');
        } else {
            die('Error al eliminar');
        }
    }
}
