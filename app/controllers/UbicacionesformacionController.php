<?php
/**
 * Controlador UbicacionesFormacionController
 */
class UbicacionesformacionController extends Controller {

    public function index() {
        $ubicaciones = UbicacionFormacion::all();
        $data = [
            'titulo' => 'Sedes de Formación',
            'ubicaciones' => $ubicaciones
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
                'municipio' => trim($_POST['municipio'])
            ];

            $ubi = new UbicacionFormacion($data);
            if ($ubi->save(1)) {
                header('Location: ' . URL_ROOT . '/ubicacionesformacion/index');
            } else {
                die('Error al guardar la sede');
            }
        }
    }

    public function delete($id) {
        if (UbicacionFormacion::delete($id, 1)) {
            header('Location: ' . URL_ROOT . '/ubicacionesformacion/index');
        }
    }
}
