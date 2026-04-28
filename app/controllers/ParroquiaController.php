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
                $mensaje = $id ? 'Parroquia actualizada exitosamente.' : 'Parroquia registrada exitosamente.';
                flash('global_msg', $mensaje, 'success');
                header('Location: ' . URL_ROOT . '/parroquia/index');
                exit();
            } else {
                flash('global_msg', 'Error al guardar la parroquia.', 'danger');
                header('Location: ' . URL_ROOT . '/parroquia/index');
                exit();
            }
        }
    }

    public function delete($id)
    {
        if (Parroquia::delete($id, $this->getUserId())) {
            flash('global_msg', 'Parroquia eliminada correctamente.', 'success');
        } else {
            flash('global_msg', 'Error al eliminar la parroquia.', 'danger');
        }
        header('Location: ' . URL_ROOT . '/parroquia/index');
        exit();
    }
}
