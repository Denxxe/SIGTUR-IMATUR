<?php

/**
 * Controlador ParroquiaController
 */
class ParroquiaController extends Controller
{

    public function index()
    {
        $parroquia = Parroquia::all();
        $municipios = Municipio::all(); // Cargar todos los municipios
        $data = [
            'titulo' => 'Gestión de Parroquias',
            'parroquia' => $parroquia,
            'municipios' => $municipios // Pasarlos a la vista
        ];
        $this->view('parroquia/index', $data);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $data = [
                'id' => $id,
                'nombre' => trim($_POST['nombre']),
                'id_municipio' => trim($_POST['id_municipio'])
            ];

            $parroquia = new Parroquia($data);
            if ($parroquia->save($this->getUserId())) { // ID temporal
                header('Location: ' . URL_ROOT . '/parroquia/index');
            } else {
                die('Error al guardar la parroquia');
            }
        }
    }

    public function delete($id)
    {
        if (Parroquia::delete($id, $this->getUserId())) {
            header('Location: ' . URL_ROOT . '/parroquia/index');
        } else {
            die('Error al eliminar la parroquia');
        }
    }
}
