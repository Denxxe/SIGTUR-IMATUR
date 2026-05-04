<?php
class VisitantesController extends Controller {

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URL_ROOT . '/auth/login');
            exit;
        }
        $this->model('Visitante');
    }

    public function index() {
        $visitantes = Visitante::all();
        $data = [
            'titulo'     => 'Registro de Visitantes',
            'visitantes' => $visitantes
        ];
        $this->view('visitantes/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data = [
                'id'               => !empty($_POST['id']) ? (int)$_POST['id'] : null,
                'cedula'           => trim($_POST['cedula'] ?? ''),
                'nombre'           => trim($_POST['nombre'] ?? ''),
                'apellido'         => trim($_POST['apellido'] ?? ''),
                'procedencia'      => trim($_POST['procedencia'] ?? ''),
                'telefono'         => trim($_POST['telefono'] ?? ''),
                'genero'           => $_POST['genero'] ?? null,
                'correo'           => trim($_POST['correo'] ?? ''),
                'motivo_frecuente' => trim($_POST['motivo_frecuente'] ?? '')
            ];

            try {
                if (Visitante::store($data, $this->getUserId())) {
                    $msg = empty($_POST['id']) ? 'Visitante registrado correctamente.' : 'Datos del visitante actualizados.';
                    flash('global_msg', $msg);
                } else {
                    throw new Exception("No se pudo guardar el registro.");
                }
            } catch (Exception $e) {
                flash('global_msg', 'Error: ' . $e->getMessage(), 'danger');
            }
            header('Location: ' . URL_ROOT . '/visitantes/index');
        }
    }

    public function delete($id) {
        try {
            if (Visitante::delete($id, $this->getUserId())) {
                flash('global_msg', 'Visitante eliminado del registro.', 'warning');
            } else {
                throw new Exception("No se pudo eliminar.");
            }
        } catch (Exception $e) {
            flash('global_msg', 'Error: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/visitantes/index');
    }
}
