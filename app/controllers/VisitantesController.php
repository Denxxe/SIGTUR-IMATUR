<?php
/**
 * Controlador VisitantesController
 */
class VisitantesController extends Controller {

    public function index() {
        $visitantes = Visitante::all();
        $data = [
            'titulo' => 'Control de Visitantes',
            'visitantes' => $visitantes
        ];
        $this->view('visitantes/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            $data = [
                'id' => isset($_POST['id']) ? (int)$_POST['id'] : null,
                'id_persona' => isset($_POST['id_persona']) ? (int)$_POST['id_persona'] : null,
                'cedula' => trim($_POST['cedula']),
                'nombre' => trim($_POST['nombre']),
                'apellido' => trim($_POST['apellido']),
                'telefono' => trim($_POST['telefono']),
                'correo' => trim($_POST['correo']),
                'genero' => $_POST['genero'],
                'fecha_nacimiento' => $_POST['fecha_nacimiento'],
                'direccion' => trim($_POST['direccion']),
                'procedencia' => trim($_POST['procedencia']),
                'motivo_frecuente' => trim($_POST['motivo_frecuente'])
            ];

            $visitante = new Visitante($data);
            if ($visitante->save(1)) {
                header('Location: ' . URL_ROOT . '/visitantes/index');
            } else {
                die('Error al guardar el visitante');
            }
        }
    }

    public function delete($id) {
        if (Visitante::delete($id, 1)) {
            header('Location: ' . URL_ROOT . '/visitantes/index');
        } else {
            die('Error al eliminar');
        }
    }
}
