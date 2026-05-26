<?php
/**
 * Controlador AsistenciasController
 */
class AsistenciasController extends Controller {

    public function index() {
        try {
            $asistencias = Asistencia::all();
            $empleados   = Empleado::all();
        } catch (Exception $e) {
            $asistencias = [];
            $empleados   = [];
            flash('global_msg', 'Error al cargar datos: ' . $e->getMessage(), 'danger');
        }

        $data = [
            'titulo'      => 'Control de Asistencia Biométrico (Manual)',
            'asistencias' => $asistencias,
            'empleados'   => $empleados,
        ];

        $this->view('asistencias/index', $data);
    }

    /**
     * Procesar Marcaje (Entrada o Salida automática)
     */
    public function marcar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_empleado = (int)$_POST['id_empleado'];
            $user_id     = $this->getUserId();

            try {
                $asistenciaAbierta = Asistencia::findOpen($id_empleado);

                if ($asistenciaAbierta) {
                    $data = [
                        'id'          => $asistenciaAbierta->id,
                        'hora_salida' => date('H:i:s'),
                        'observacion' => 'Marcaje de salida automático',
                    ];
                    $asistencia = new Asistencia($data);
                    $asistencia->save($user_id);
                    flash('global_msg', 'Salida registrada correctamente.');
                } else {
                    $data = [
                        'id_empleado'  => $id_empleado,
                        'fecha'        => date('Y-m-d'),
                        'hora_entrada' => date('H:i:s'),
                        'observacion'  => 'Marcaje de entrada automático',
                    ];
                    $asistencia = new Asistencia($data);
                    $asistencia->save($user_id);
                    flash('global_msg', 'Entrada registrada correctamente.');
                }
            } catch (Exception $e) {
                flash('global_msg', 'Error al registrar marcaje: ' . $e->getMessage(), 'danger');
            }

            header('Location: ' . URL_ROOT . '/asistencias/index');
        }
    }

    public function delete($id) {
        try {
            if (Asistencia::delete($id, $this->getUserId())) {
                flash('global_msg', 'Registro de asistencia eliminado.', 'warning');
            } else {
                throw new Exception('No se pudo eliminar el registro.');
            }
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/asistencias/index');
    }
}
