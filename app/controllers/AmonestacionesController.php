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

    /** Genera una amonestación a partir de una falta existente (escalado, 3E). */
    public function amonestarDesdeFalta() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $_POST = $this->sanitizePost();
        $idEmpleado = (int)($_POST['id_empleado'] ?? 0);
        $idFalta    = (int)($_POST['id_falta'] ?? 0);
        try {
            $falta = Falta::find($idFalta);
            if (!$falta || !$falta->is_active) throw new Exception('La falta no existe o ya fue anulada.');
            $motivo = 'Originada por falta del ' . date('d/m/Y', strtotime($falta->fecha))
                    . ' (' . ($falta->tipo ?? 'falta') . ')'
                    . (!empty($falta->motivo) ? ': ' . $falta->motivo : '');
            Amonestacion::save([
                'id_empleado'     => $idEmpleado,
                'fecha'           => date('Y-m-d'),
                'motivo'          => $motivo,
                'id_falta_origen' => $idFalta,
            ], $this->getUserId());
            flash('global_msg', 'Amonestación generada a partir de la falta.', 'warning');
        } catch (Exception $e) {
            flash('global_msg', 'No se pudo generar la amonestación: ' . $e->getMessage(), 'danger');
        }
        $this->volver($idEmpleado);
    }

    public function eliminarFalta($id, $idEmpleado = 0) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->volver($idEmpleado); return; }
        $_POST = $this->sanitizePost();
        try {
            $motivo = trim($_POST['motivo_anulacion'] ?? '');
            if ($motivo === '') throw new Exception("Debe indicar el motivo de la anulación.");
            Falta::delete($id, $this->getUserId(), $motivo);
            flash('global_msg', 'Falta anulada.', 'warning');
        } catch (Exception $e) { flash('global_msg', 'No se pudo anular: ' . $e->getMessage(), 'danger'); }
        $this->volver($idEmpleado);
    }

    public function eliminarAmonestacion($id, $idEmpleado = 0) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->volver($idEmpleado); return; }
        $_POST = $this->sanitizePost();
        try {
            $motivo = trim($_POST['motivo_anulacion'] ?? '');
            if ($motivo === '') throw new Exception("Debe indicar el motivo de la anulación.");
            Amonestacion::delete($id, $this->getUserId(), $motivo);
            flash('global_msg', 'Amonestación anulada.', 'warning');
        } catch (Exception $e) { flash('global_msg', 'No se pudo anular: ' . $e->getMessage(), 'danger'); }
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
