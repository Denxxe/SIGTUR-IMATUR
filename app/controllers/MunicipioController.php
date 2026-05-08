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
            $_POST = $this->sanitizePost();

            $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $data = [
                'id' => $id,
                'nombre' => trim($_POST['nombre']),
                'codigo_postal' => trim($_POST['codigo_postal'])
            ];

            $municipio = new Municipio($data);
            if ($municipio->save($this->getUserId())) { // ID temporal
                $mensaje = $id ? 'Municipio actualizado exitosamente.' : 'Municipio registrado exitosamente.';
                flash('global_msg', $mensaje, 'success');
                header('Location: ' . URL_ROOT . '/municipio/index');
                exit();
            } else {
                flash('global_msg', 'Error al guardar el municipio.', 'danger');
                header('Location: ' . URL_ROOT . '/municipio/index');
                exit();
            }
        }
    }

    public function delete($id)
    {
        if (Municipio::delete($id, $this->getUserId())) {
            flash('global_msg', 'Municipio eliminado correctamente.', 'success');
        } else {
            flash('global_msg', 'Error al eliminar el municipio.', 'danger');
        }
        header('Location: ' . URL_ROOT . '/municipio/index');
        exit();
    }
}
