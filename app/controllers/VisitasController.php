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
        $visitas   = Visita::getRecientes();
        $empleados = Empleado::all();

        $data = [
            'titulo'    => 'Control de Visitas',
            'visitas'   => $visitas,
            'empleados' => $empleados,
        ];
        $this->view('visitas/index', $data);
    }

    public function buscarVisitante() {
        header('Content-Type: application/json');
        $cedula = strip_tags(trim($_GET['cedula'] ?? ''));
        if (empty($cedula)) {
            echo json_encode(['found' => false]);
            exit;
        }
        $visitante = Visitante::buscarPorCedula($cedula);
        if ($visitante) {
            echo json_encode([
                'found'     => true,
                'visitante' => [
                    'id'          => $visitante->id,
                    'cedula'      => $visitante->cedula,
                    'nombre'      => $visitante->nombre,
                    'apellido'    => $visitante->apellido,
                    'procedencia' => $visitante->procedencia ?? '',
                    'telefono'    => $visitante->telefono    ?? '',
                    'genero'      => $visitante->genero      ?? '',
                    'correo'      => $visitante->correo      ?? '',
                ],
            ]);
        } else {
            echo json_encode(['found' => false]);
        }
        exit;
    }

    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $_POST  = $this->sanitizePost();
        $userId = $this->getUserId();

        try {
            $cedula   = trim($_POST['cedula']   ?? '') ?: null;
            $nombre   = trim($_POST['nombre']   ?? '');
            $apellido = trim($_POST['apellido'] ?? '');

            if (empty($nombre) || empty($apellido)) {
                throw new Exception('El nombre y apellido del visitante son requeridos.');
            }

            // Lookup or create visitante
            $visitante = $cedula ? Visitante::buscarPorCedula($cedula) : null;

            if ($visitante) {
                $idVisitante = $visitante->id;
            } else {
                $idVisitante = Visitante::crear([
                    'cedula'          => $cedula,
                    'nombre'          => $nombre,
                    'apellido'        => $apellido,
                    'procedencia'     => trim($_POST['procedencia'] ?? '') ?: null,
                    'telefono'        => trim($_POST['telefono']    ?? '') ?: null,
                    'genero'          => trim($_POST['genero']      ?? '') ?: null,
                    'correo'          => trim($_POST['correo']      ?? '') ?: null,
                    'motivo_frecuente'=> trim($_POST['motivo']      ?? '') ?: null,
                ], $userId);
            }

            $visitaData = [
                'id_visitante'  => $idVisitante,
                'id_empleado'   => !empty($_POST['id_empleado']) ? (int)$_POST['id_empleado'] : null,
                'motivo'        => trim($_POST['motivo'] ?? ''),
                'observaciones' => 'Registro en recepción',
            ];

            if (Visita::registrar($visitaData, $userId)) {
                flash('global_msg', 'Marcaje procesado correctamente.');
            } else {
                throw new Exception('Error al procesar el marcaje.');
            }

        } catch (Exception $e) {
            flash('global_msg', 'Error: ' . $e->getMessage(), 'danger');
        }

        header('Location: ' . URL_ROOT . '/visitas/index');
    }

    public function delete($id) {
        try {
            if (Visita::delete($id, $this->getUserId())) {
                flash('global_msg', 'Registro de visita eliminado.', 'warning');
            } else {
                throw new Exception('No se pudo eliminar el registro.');
            }
        } catch (Exception $e) {
            flash('global_msg', 'Error: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/visitas/index');
    }
}
