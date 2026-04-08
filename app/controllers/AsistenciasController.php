<?php
/**
 * Controlador AsistenciasController
 */
class AsistenciasController extends Controller {

    public function index() {
        $asistencias = Asistencia::all();
        $empleados = Empleado::all();

        $data = [
            'titulo' => 'Control de Asistencia Biométrico (Manual)',
            'asistencias' => $asistencias,
            'empleados' => $empleados
        ];

        $this->view('asistencias/index', $data);
    }

    /**
     * Procesar Marcaje (Entrada o Salida automática)
     */
    public function marcar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_empleado = (int)$_POST['id_empleado'];
            $user_id = 1; // ID temporal

            // 1. Verificar si tiene una asistencia abierta hoy
            $asistenciaAbierta = Asistencia::findOpen($id_empleado);

            if ($asistenciaAbierta) {
                // Registrar Salida
                $data = [
                    'id' => $asistenciaAbierta->id,
                    'hora_salida' => date('H:i:s'),
                    'observacion' => 'Marcaje de salida automático'
                ];
                $asistencia = new Asistencia($data);
                $asistencia->save($user_id);
            } else {
                // Registrar Entrada
                $data = [
                    'id_empleado' => $id_empleado,
                    'fecha' => date('Y-m-d'),
                    'hora_entrada' => date('H:i:s'),
                    'observacion' => 'Marcaje de entrada automático'
                ];
                $asistencia = new Asistencia($data);
                $asistencia->save($user_id);
            }

            header('Location: ' . URL_ROOT . '/asistencias/index');
        }
    }

    public function delete($id) {
        if (Asistencia::delete($id, 1)) {
            header('Location: ' . URL_ROOT . '/asistencias/index');
        }
    }
}
