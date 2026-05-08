<?php
class VisitasController extends Controller {

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URL_ROOT . '/auth/login');
            exit;
        }
        $this->model('Visitante');
        $this->model('Visita');
        $this->model('Empleado');
    }

    public function index() {
        $visitas    = Visita::getRecientes();
        $visitantes = Visitante::all();
        $empleados  = Empleado::all();

        $data = [
            'titulo'     => 'Control de Visitas',
            'visitas'    => $visitas,
            'visitantes' => $visitantes,
            'empleados'  => $empleados
        ];
        $this->view('visitas/index', $data);
    }

    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = $this->sanitizePost();
            $data = [
                'id_visitante'  => (int)$_POST['id_visitante'],
                'id_empleado'   => !empty($_POST['id_empleado']) ? (int)$_POST['id_empleado'] : null,
                'motivo'        => trim($_POST['motivo'] ?? ''),
                'observaciones' => trim($_POST['observaciones'] ?? '')
            ];

            try {
                if (Visita::registrar($data, $this->getUserId())) {
                    flash('global_msg', 'Marcaje procesado correctamente.');
                } else {
                    throw new Exception("Error al procesar el marcaje.");
                }
            } catch (Exception $e) {
                flash('global_msg', 'Error: ' . $e->getMessage(), 'danger');
            }
            header('Location: ' . URL_ROOT . '/visitas/index');
        }
    }

    public function delete($id) {
        try {
            if (Visita::delete($id)) {
                flash('global_msg', 'Registro de visita eliminado.', 'warning');
            } else {
                throw new Exception("No se pudo eliminar el registro.");
            }
        } catch (Exception $e) {
            flash('global_msg', 'Error: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/visitas/index');
    }
}
