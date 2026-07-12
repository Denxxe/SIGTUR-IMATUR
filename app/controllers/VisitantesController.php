<?php
class VisitantesController extends Controller {

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
        $porPagina = 12;
        $pagina    = max(1, (int)($_GET['p'] ?? 1));
        $filtros   = [
            'buscar'      => trim($_GET['buscar']      ?? ''),
            'fecha_desde' => trim($_GET['fecha_desde'] ?? ''),
            'fecha_hasta' => trim($_GET['fecha_hasta'] ?? ''),
        ];
        $res          = Visita::paginate($pagina, $porPagina, $filtros);
        $totalReg     = $res['total'];
        $totalPaginas = max(1, (int)ceil($totalReg / $porPagina));
        if ($pagina > $totalPaginas) $pagina = $totalPaginas;

        $data = [
            'titulo'        => 'Recepción',
            'movimientos'   => $res['items'],
            'empleados'     => Empleado::all(),
            'pagina'        => $pagina,
            'total_paginas' => $totalPaginas,
            'total'         => $totalReg,
            'por_pagina'    => $porPagina,
            'filtros'       => $filtros,
        ];
        $this->view('visitantes/index', $data);
    }

    public function buscarVisitante() {
        header('Content-Type: application/json');
        $cedula = strip_tags(trim($_GET['cedula'] ?? ''));
        if (empty($cedula)) {
            echo json_encode(['found' => false]);
            exit;
        }
        $v = Visitante::buscarPorCedula($cedula);
        if ($v) {
            echo json_encode([
                'found'     => true,
                'visitante' => [
                    'id_visitante'    => $v->id_visitante,
                    'cedula'          => $v->cedula,
                    'nombre'          => $v->nombre,
                    'apellido'        => $v->apellido,
                    'procedencia'     => $v->procedencia     ?? '',
                    'telefono'        => $v->telefono        ?? '',
                    'correo'          => $v->correo          ?? '',
                    'genero'          => $v->genero          ?? '',
                    'motivo_frecuente'=> $v->motivo_frecuente ?? '',
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
                throw new Exception('El nombre y apellido son requeridos.');
            }

            $correo = trim($_POST['correo'] ?? '');
            if ($correo !== '' && !$this->emailValido($correo)) {
                throw new Exception('El correo "' . htmlspecialchars($correo) . '" no es válido (sin espacios ni símbolos especiales; ejemplo: nombre@dominio.com).');
            }
            $tel = trim($_POST['telefono'] ?? '');
            if ($tel !== '' && !$this->telefonoValido($tel)) {
                throw new Exception('El teléfono no es válido. Debe ser un número venezolano (prefijo + 7 dígitos).');
            }

            // Find existing visitante by cédula, or create one
            $visitante = $cedula ? Visitante::buscarPorCedula($cedula) : null;

            if ($visitante) {
                $idVisitante = $visitante->id_visitante;
            } else {
                $idVisitante = Visitante::crear([
                    'cedula'           => $cedula,
                    'nombre'           => $nombre,
                    'apellido'         => $apellido,
                    'procedencia'      => trim($_POST['procedencia'] ?? '') ?: null,
                    'telefono'         => trim($_POST['telefono']    ?? '') ?: null,
                    'genero'           => trim($_POST['genero']      ?? '') ?: null,
                    'correo'           => trim($_POST['correo']      ?? '') ?: null,
                    'motivo_frecuente' => trim($_POST['motivo']      ?? '') ?: null,
                ], $userId);
            }

            $visitaData = [
                'id_visitante'  => $idVisitante,
                'id_empleado'   => !empty($_POST['id_empleado']) ? (int)$_POST['id_empleado'] : null,
                'motivo'        => trim($_POST['motivo'] ?? ''),
                'observaciones' => 'Registro en recepción',
            ];

            Visita::registrar($visitaData, $userId);
            flash('global_msg', 'Marcaje procesado correctamente.');

        } catch (Exception $e) {
            flash('global_msg', 'Error: ' . $e->getMessage(), 'danger');
        }

        header('Location: ' . URL_ROOT . '/visitantes/index');
    }
}
