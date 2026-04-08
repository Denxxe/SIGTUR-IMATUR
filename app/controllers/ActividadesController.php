<?php
/**
 * Controlador ActividadesController
 */
class ActividadesController extends Controller {

    public function index() {
        $actividades = Actividad::all();
        $data = [
            'titulo' => 'Agenda de Actividades y Eventos',
            'actividades' => $actividades
        ];
        $this->view('actividades/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            $data = [
                'id' => isset($_POST['id']) ? (int)$_POST['id'] : null,
                'nombre' => trim($_POST['nombre']),
                'tipo' => $_POST['tipo'],
                'descripcion' => trim($_POST['descripcion']),
                'fecha_inicio' => $_POST['fecha_inicio'],
                'fecha_fin' => $_POST['fecha_fin'],
                'lugar' => trim($_POST['lugar']),
                'presupuesto' => (float)$_POST['presupuesto'],
                'estado' => $_POST['estado']
            ];

            $actividad = new Actividad($data);
            if ($actividad->save(1)) {
                header('Location: ' . URL_ROOT . '/actividades/index');
            } else {
                die('Error al guardar la actividad');
            }
        }
    }

    public function delete($id) {
        if (Actividad::delete($id, 1)) {
            header('Location: ' . URL_ROOT . '/actividades/index');
        }
    }
}
