<?php
/**
 * Controlador HorariosController — catálogo de horarios/turnos (RRHH).
 */
class HorariosController extends Controller {

    public function index() {
        $data = [
            'titulo'   => 'Horarios y Turnos',
            'horarios' => Horario::all(),
        ];
        $this->view('horarios/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $_POST = $this->sanitizePost();

        $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
        $data = [
            'id'             => $id,
            'nombre'         => trim($_POST['nombre'] ?? ''),
            'hora_entrada'   => $_POST['hora_entrada'] ?? '',
            'hora_salida'    => $_POST['hora_salida'] ?? '',
            'dias_laborales' => trim($_POST['dias_laborales'] ?? 'L-V'),
            'descripcion'    => trim($_POST['descripcion'] ?? ''),
        ];

        // Validaciones mínimas
        if ($data['nombre'] === '' || empty($data['hora_entrada']) || empty($data['hora_salida'])) {
            flash('global_msg', 'Nombre, hora de entrada y hora de salida son obligatorios.', 'danger');
            header('Location: ' . URL_ROOT . '/horarios/index');
            return;
        }
        if ($data['hora_salida'] <= $data['hora_entrada']) {
            flash('global_msg', 'La hora de salida debe ser posterior a la de entrada.', 'danger');
            header('Location: ' . URL_ROOT . '/horarios/index');
            return;
        }

        $esEdicion = !empty($id);
        $horario = new Horario($data);
        try {
            if ($horario->save($this->getUserId())) {
                flash('global_msg', $esEdicion ? 'Horario actualizado.' : 'Nuevo horario registrado.');
            } else {
                throw new Exception('No se pudo guardar el horario.');
            }
        } catch (Exception $e) {
            flash('global_msg', 'Error: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/horarios/index');
    }

    public function delete($id) {
        try {
            if (Horario::delete($id, $this->getUserId())) {
                flash('global_msg', 'Horario movido a la papelera.', 'warning');
            } else {
                throw new Exception('El horario no pudo eliminarse.');
            }
        } catch (Exception $e) {
            flash('global_msg', 'Fallo: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/horarios/index');
    }
}
