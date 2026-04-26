<?php

/**
 * Controlador MunicipioController
 */
class MunicipioController extends Controller
{

    public function index()
    {
        $municipio = Municipio::all();
        $data = [
            'titulo' => 'Gestión de Municipio',
            'municipio' => $municipio
        ];
        $this->view('municipio/index', $data);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $data = [
                'id' => $id,
                'nombre' => trim($_POST['nombre']),
                'codigo_postal' => trim($_POST['codigo_postal'])
            ];

            $municipio = new Municipio($data);
            if ($municipio->save($this->getUserId())) { // ID temporal
                header('Location: ' . URL_ROOT . '/municipio/index');
            } else {
                die('Error al guardar el municipio');
            }
        }
    }

    public function delete($id)
    {
        if (Municipio::delete($id, $this->getUserId())) {
            header('Location: ' . URL_ROOT . '/municipio/index');
        } else {
            die('Error al eliminar el municipio');
        }
    }
}
