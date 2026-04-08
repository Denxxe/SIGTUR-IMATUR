<?php
/**
 * Controlador UbicacionesController
 */
class UbicacionesController extends Controller {

    public function index() {
        $ubicaciones = Ubicacion::all();
        $data = [
            'titulo' => 'Configuración: Sedes y Almacenes',
            'ubicaciones' => $ubicaciones
        ];
        $this->view('ubicaciones/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            $data = [
                'id' => isset($_POST['id']) ? (int)$_POST['id'] : null,
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion'])
            ];

            $ubi = new Ubicacion($data);
            if ($ubi->save(1)) {
                header('Location: ' . URL_ROOT . '/ubicaciones/index');
            } else {
                die('Error al guardar la ubicación');
            }
        }
    }

    public function delete($id) {
        if (Ubicacion::delete($id, 1)) {
            header('Location: ' . URL_ROOT . '/ubicaciones/index');
        }
    }
}
