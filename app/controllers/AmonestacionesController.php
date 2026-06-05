<?php
/**
 * Controlador AmonestacionesController — faltas injustificadas y amonestaciones (R-9).
 */
class AmonestacionesController extends Controller {

    public function index() {
        $data = [
            'titulo'    => 'Faltas y Amonestaciones',
            'roster'    => Amonestacion::roster(),
            'empleados' => Empleado::all(),
            'limite'    => Amonestacion::LIMITE_DESPIDO,
        ];
        $this->view('amonestaciones/index', $data);
    }

    /** Detalle por empleado: faltas y amonestaciones registradas. */
    public function empleado($id) {
        $empleado = Empleado::find($id);
        if (!$empleado) {
            flash('global_msg', 'El empleado solicitado no existe.', 'danger');
            header('Location: ' . URL_ROOT . '/amonestaciones/index');
            return;
        }
        $data = [
            'titulo'         => 'Faltas y Amonestaciones: ' . $empleado->nombre . ' ' . $empleado->apellido,
            'empleado'       => $empleado,
            'faltas'         => Falta::porEmpleado($id),
            'amonestaciones' => Amonestacion::porEmpleado($id),
            'limite'         => Amonestacion::LIMITE_DESPIDO,
        ];
        $this->view('amonestaciones/detalle', $data);
    }

    public function registrarFalta() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $_POST = $this->sanitizePost();
        $idEmpleado = (int)($_POST['id_empleado'] ?? 0);
        try {
            Falta::save($_POST, $this->getUserId());
            flash('global_msg', 'Falta registrada.');
        } catch (Exception $e) {
            flash('global_msg', 'No se pudo registrar la falta: ' . $e->getMessage(), 'danger');
        }
        $this->volver($idEmpleado);
    }

    public function registrarAmonestacion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $_POST = $this->sanitizePost();
        $idEmpleado = (int)($_POST['id_empleado'] ?? 0);
        try {
            Amonestacion::save($_POST, $this->getUserId());
            flash('global_msg', 'Amonestación registrada.', 'warning');
        } catch (Exception $e) {
            flash('global_msg', 'No se pudo registrar la amonestación: ' . $e->getMessage(), 'danger');
        }
        $this->volver($idEmpleado);
    }

    public function eliminarFalta($id, $idEmpleado = 0) {
        try { Falta::delete($id, $this->getUserId()); flash('global_msg', 'Falta eliminada.', 'warning'); }
        catch (Exception $e) { flash('global_msg', 'No se pudo eliminar: ' . $e->getMessage(), 'danger'); }
        $this->volver($idEmpleado);
    }

    public function eliminarAmonestacion($id, $idEmpleado = 0) {
        try { Amonestacion::delete($id, $this->getUserId()); flash('global_msg', 'Amonestación eliminada.', 'warning'); }
        catch (Exception $e) { flash('global_msg', 'No se pudo eliminar: ' . $e->getMessage(), 'danger'); }
        $this->volver($idEmpleado);
    }

    /** Vuelve al detalle del empleado si se conoce, si no al roster. */
    private function volver($idEmpleado) {
        if ($idEmpleado > 0) {
            header('Location: ' . URL_ROOT . '/amonestaciones/empleado/' . $idEmpleado);
        } else {
            header('Location: ' . URL_ROOT . '/amonestaciones/index');
        }
    }
}
