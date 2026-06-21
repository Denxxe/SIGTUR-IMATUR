<?php
/**
 * VacacionesController — gestión de vacaciones (R-8 / 3A).
 * RBAC: rol 2 (RRHH) + admin '*' vía permisos_rol (mig.045).
 * El cálculo de días/saldo vive en el modelo Vacacion; el cobro/liquidación
 * NO entra aquí (pendiente de formatos de nómina, 3B).
 */
class VacacionesController extends Controller {

    /** Resumen de saldos de todo el personal activo. */
    public function index() {
        $filas = [];
        foreach (Empleado::all() as $e) {
            $filas[] = [
                'emp'         => $e,
                'anios'       => Vacacion::aniosServicio($e),
                'derechoAnio' => Vacacion::derechoAnioActual($e),
                'acumulado'   => Vacacion::derechoAcumulado($e),
                'disfrutado'  => Vacacion::totalDisfrutado((int)$e->id),
                'saldo'       => Vacacion::saldo($e),
            ];
        }
        $this->view('vacaciones/index', [
            'titulo'        => 'Vacaciones',
            'filas'         => $filas,
            'totalFeriados' => count(Feriado::all()),
        ]);
    }

    /** Detalle por empleado: saldo, ajuste inicial y períodos. */
    public function empleado($id) {
        $e = Empleado::find((int)$id);
        if (!$e) {
            flash('global_msg', 'Empleado no encontrado.', 'danger');
            header('Location: ' . URL_ROOT . '/vacaciones/index');
            return;
        }
        $this->view('vacaciones/empleado', [
            'titulo'      => 'Vacaciones — ' . trim(($e->nombre ?? '') . ' ' . ($e->apellido ?? '')),
            'emp'         => $e,
            'anios'       => Vacacion::aniosServicio($e),
            'derechoAnio' => Vacacion::derechoAnioActual($e),
            'acumulado'   => Vacacion::derechoAcumulado($e),
            'ajuste'      => (int)($e->vacaciones_ajuste_dias ?? 0),
            'disfrutado'  => Vacacion::totalDisfrutado((int)$e->id),
            'saldo'       => Vacacion::saldo($e),
            'periodos'    => Vacacion::porEmpleado((int)$e->id),
        ]);
    }

    /** Registra un período (Pendiente). Calcula días hábiles del rango. */
    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/vacaciones/index'); return; }
        $_POST = $this->sanitizePost();
        $idEmp = (int)($_POST['id_empleado'] ?? 0);
        try {
            $emp = Empleado::find($idEmp);
            if (!$emp) throw new Exception('Empleado no válido.');
            $ini = trim($_POST['fecha_inicio'] ?? '');
            $fin = trim($_POST['fecha_fin'] ?? '');
            if ($ini === '' || $fin === '') throw new Exception('Indica las fechas de inicio y fin.');
            if ($fin < $ini) throw new Exception('La fecha de fin no puede ser anterior a la de inicio.');
            $dias = Vacacion::diasHabiles($ini, $fin);
            if ($dias < 1) throw new Exception('El período no contiene días hábiles (solo fines de semana o feriados).');
            $saldo = Vacacion::saldo($emp);
            $obs   = trim($_POST['observaciones'] ?? '') ?: null;
            $anio  = (int)date('Y', strtotime($ini));
            if (!Vacacion::crear($idEmp, $anio, $ini, $fin, $dias, $obs, $this->getUserId())) {
                throw new Exception('No se pudo registrar el período.');
            }
            $msg = "Período registrado: {$dias} día(s) hábil(es).";
            if ($dias > $saldo) {
                flash('global_msg', $msg . " Aviso: excede el saldo disponible ({$saldo} día(s)).", 'warning');
            } else {
                flash('global_msg', $msg);
            }
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/vacaciones/empleado/' . $idEmp);
    }

    /** Cambia el estado de un período (aprobar/rechazar/curso/completar). */
    public function cambiarEstado() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/vacaciones/index'); return; }
        $_POST = $this->sanitizePost();
        $id    = (int)($_POST['id'] ?? 0);
        $idEmp = (int)($_POST['id_empleado'] ?? 0);
        $estado = $_POST['estado'] ?? '';
        try {
            if (!Vacacion::cambiarEstado($id, $estado, $this->getUserId())) throw new Exception('Estado no válido.');
            flash('global_msg', 'Estado actualizado a ' . $estado . '.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/vacaciones/empleado/' . $idEmp);
    }

    /** Guarda el ajuste de saldo inicial (días disfrutados antes del sistema). */
    public function guardarAjuste() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/vacaciones/index'); return; }
        $_POST = $this->sanitizePost();
        $idEmp = (int)($_POST['id_empleado'] ?? 0);
        $dias  = max(0, (int)($_POST['ajuste'] ?? 0));
        try {
            $db = new Database();
            $db->query("UPDATE empleados SET vacaciones_ajuste_dias = :d, updated_at = CURRENT_TIMESTAMP, updated_by = :u WHERE id = :id");
            $db->bind(':d', $dias);
            $db->bind(':u', $this->getUserId());
            $db->bind(':id', $idEmp);
            $db->execute();
            flash('global_msg', 'Ajuste de saldo inicial guardado.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/vacaciones/empleado/' . $idEmp);
    }

    public function eliminar($id) {
        $periodo = Vacacion::find((int)$id);
        $idEmp = (int)($periodo->id_empleado ?? 0);
        try {
            if (!Vacacion::eliminar((int)$id, $this->getUserId())) throw new Exception('No se pudo eliminar.');
            flash('global_msg', 'Período eliminado.', 'warning');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/vacaciones/empleado/' . $idEmp);
    }

    // ── Calendario de feriados (afecta el conteo de días hábiles) ─────────────
    public function feriados() {
        $this->view('vacaciones/feriados', ['titulo' => 'Feriados', 'feriados' => Feriado::all()]);
    }

    public function agregarFeriado() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/vacaciones/feriados'); return; }
        $_POST = $this->sanitizePost();
        try {
            $fecha  = trim($_POST['fecha'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $rec    = !empty($_POST['recurrente']);
            if ($fecha === '' || $nombre === '') throw new Exception('Indica la fecha y el nombre del feriado.');
            Feriado::crear($fecha, $nombre, $rec, $this->getUserId());
            flash('global_msg', 'Feriado agregado.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/vacaciones/feriados');
    }

    public function eliminarFeriado($id) {
        try {
            Feriado::eliminar((int)$id, $this->getUserId());
            flash('global_msg', 'Feriado eliminado.', 'warning');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/vacaciones/feriados');
    }
}
