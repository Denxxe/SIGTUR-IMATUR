<?php
/**
 * Controlador UbicacionesFormacionController
 */
class UbicacionesformacionController extends Controller {

    public function index() {
        $ubicaciones = UbicacionFormacion::all();
        require_once '../app/models/Parroquia.php';
        $parroquias = Parroquia::all();
        
        $data = [
            'titulo' => 'Sedes de Formación',
            'ubicaciones' => $ubicaciones,
            'parroquias' => $parroquias
        ];
        $this->view('ubicaciones_formacion/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'id' => isset($_POST['id']) ? (int)$_POST['id'] : null,
                'nombre' => trim($_POST['nombre']),
                'tipo' => trim($_POST['tipo']),
                'direccion' => trim($_POST['direccion']),
                'id_parroquia' => trim($_POST['parroquia'])
            ];

            $ubi = new UbicacionFormacion($data);
            if ($ubi->save($this->getUserId())) {
                $mensaje = $data['id'] ? 'Sede actualizada exitosamente.' : 'Sede registrada exitosamente.';
                flash('global_msg', $mensaje, 'success');
                header('Location: ' . URL_ROOT . '/ubicacionesformacion/index');
                exit();
            } else {
                flash('global_msg', 'Error al guardar la sede.', 'danger');
                header('Location: ' . URL_ROOT . '/ubicacionesformacion/index');
                exit();
            }
        }
    }

    public function delete($id) {
        if (UbicacionFormacion::delete($id, $this->getUserId())) {
            flash('global_msg', 'Sede de formación eliminada correctamente.', 'success');
        } else {
            flash('global_msg', 'Error al eliminar la sede.', 'danger');
        }
        header('Location: ' . URL_ROOT . '/ubicacionesformacion/index');
        exit();
    }
}
