<?php
/**
 * Controlador ActividadesrutaController
 */
class ActividadesrutaController extends Controller {

    public function index() {
        $actividades = ActividadRuta::all();
        $rutas = Ruta::all();
        $empleados = Empleado::all();

        $data = [
            'titulo' => 'Actividades y Excursiones Turísticas',
            'actividades' => $actividades,
            'rutas' => $rutas,
            'empleados' => $empleados
        ];

        $this->view('actividades_ruta/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = $this->sanitizePost();

            $data = [
                'id' => isset($_POST['id']) ? (int)$_POST['id'] : null,
                'id_ruta' => (int)$_POST['id_ruta'],
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion']),
                'fecha' => $_POST['fecha'] ?: null,
                'id_empleado_responsable' => !empty($_POST['id_empleado_responsable']) ? (int)$_POST['id_empleado_responsable'] : null
            ];

            $actividad = new ActividadRuta($data);
            if ($actividad->save($this->getUserId())) {
                header('Location: ' . URL_ROOT . '/actividadesruta/index');
            } else {
                die('Error al guardar la actividad de ruta');
            }
        }
    }

    public function delete($id) {
        if (ActividadRuta::delete($id, $this->getUserId())) {
            header('Location: ' . URL_ROOT . '/actividadesruta/index');
        }
    }
}
