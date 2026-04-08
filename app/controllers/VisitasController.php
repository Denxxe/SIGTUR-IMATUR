<?php
/**
 * Controlador VisitasController
 */
class VisitasController extends Controller {

    public function index() {
        $visitas = Visita::all();
        $visitantes = Visitante::all();
        $empleados = Empleado::all();

        $data = [
            'titulo' => 'Registro de Visitas Institucionales',
            'visitas' => $visitas,
            'visitantes' => $visitantes,
            'empleados' => $empleados
        ];
        $this->view('visitas/index', $data);
    }

    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            $id_visitante = (int)$_POST['id_visitante'];
            
            // Verificar si el visitante ya tiene una visita abierta
            $visitaAbierta = Visita::findOpen($id_visitante);

            if ($visitaAbierta) {
                // Registrar Salida
                $data = [
                    'id' => $visitaAbierta->id,
                    'hora_salida' => date('H:i:s'),
                    'observaciones' => trim($_POST['observaciones']) . ' (Salida registrada)'
                ];
                $visita = new Visita($data);
                $visita->save(1);
            } else {
                // Registrar Nueva Entrada
                $data = [
                    'id_visitante' => $id_visitante,
                    'id_empleado_visitado' => (int)$_POST['id_empleado'],
                    'motivo' => trim($_POST['motivo']),
                    'fecha' => date('Y-m-d'),
                    'hora_entrada' => date('H:i:s'),
                    'observaciones' => trim($_POST['observaciones'])
                ];
                $visita = new Visita($data);
                $visita->save(1);
            }

            header('Location: ' . URL_ROOT . '/visitas/index');
        }
    }

    public function delete($id) {
        if (Visita::delete($id, 1)) {
            header('Location: ' . URL_ROOT . '/visitas/index');
        }
    }
}
