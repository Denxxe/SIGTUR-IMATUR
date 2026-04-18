<?php
/**
 * Controlador TalleresController — Gestión de Formación Comunitaria
 */
class TalleresController extends Controller {

    public function index() {
        $talleres = Taller::all();
        $empleados = Empleado::all();
        $ubicaciones = UbicacionFormacion::all();

        $data = [
            'titulo' => 'Formación: Talleres Comunitarios',
            'talleres' => $talleres,
            'empleados' => $empleados,
            'ubicaciones' => $ubicaciones
        ];
        $this->view('talleres/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'id' => isset($_POST['id']) ? (int)$_POST['id'] : null,
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion']),
                'fecha_inicio' => $_POST['fecha_inicio'],
                'fecha_fin' => $_POST['fecha_fin'] ?: null,
                'hora_inicio' => $_POST['hora_inicio'] ?: null,
                'hora_fin' => $_POST['hora_fin'] ?: null,
                'id_ubicacion_formacion' => (int)$_POST['id_ubicacion_formacion'] ?: null,
                'id_facilitador' => (int)$_POST['id_facilitador'],
                'cupo_maximo' => (int)$_POST['cupo_maximo'],
                'estado' => $_POST['estado']
            ];

            $esEdicion = !empty($data['id']);
            $taller = new Taller($data);

            try {
                if ($taller->save($this->getUserId())) {
                    $msg = $esEdicion ? "Información del taller actualizada." : "Nuevo taller programado exitosamente.";
                    flash('global_msg', $msg);
                    header('Location: ' . URL_ROOT . '/talleres/index');
                } else {
                    throw new Exception("Error al procesar la solicitud del taller.");
                }
            } catch (Exception $e) {
                flash('global_msg', 'Fallo en formación: ' . $e->getMessage(), 'danger');
                header('Location: ' . URL_ROOT . '/talleres/index');
            }
        }
    }

    public function detalle($id) {
        $taller = Taller::find($id);
        $participantes = Taller::getParticipantes($id);

        $data = [
            'titulo' => 'Detalle del Taller: ' . $taller->nombre,
            'taller' => $taller,
            'participantes' => $participantes
        ];
        $this->view('talleres/detalle', $data);
    }

    public function inscribir() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_taller = (int)$_POST['id_taller'];
            $id_persona = (int)$_POST['id_persona'];

            try {
                if (Taller::inscribir($id_taller, $id_persona, 1)) {
                    flash('global_msg', 'Participante inscrito correctamente en el taller.');
                } else {
                    throw new Exception("No se pudo realizar la inscripción. Verifique si el participante ya existe.");
                }
            } catch (Exception $e) {
                flash('global_msg', 'Error de inscripción: ' . $e->getMessage(), 'danger');
            }
            header('Location: ' . URL_ROOT . '/talleres/detalle/' . $id_taller);
        }
    }

    public function delete($id) {
        try {
            if (Taller::delete($id, $this->getUserId())) {
                flash('global_msg', 'Taller cancelado y movido al historial de papelera.', 'warning');
            } else {
                throw new Exception("No es posible eliminar el taller en este momento.");
            }
        } catch (Exception $e) {
            flash('global_msg', 'Fallo en eliminación: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/talleres/index');
    }

    public function informe($id_taller) {
        $taller = Taller::find($id_taller);
        if (!$taller) {
            header('Location: ' . URL_ROOT . '/talleres/index');
            exit;
        }

        $informe = Taller::getInforme($id_taller);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'id_taller' => $id_taller,
                'unidad_estadal' => trim($_POST['unidad_estadal'] ?? 'Sucre'),
                'lugar_exacto' => trim($_POST['lugar_exacto'] ?? ''),
                'instituciones_presentes' => trim($_POST['instituciones_presentes'] ?? ''),
                'mujeres' => (int)$_POST['mujeres'],
                'hombres' => (int)$_POST['hombres'],
                'ninas' => (int)$_POST['ninas'],
                'ninos' => (int)$_POST['ninos'],
                'resumen_actividad' => trim($_POST['resumen_actividad'] ?? '')
            ];

            try {
                Taller::saveInforme($data);
                flash('global_msg', 'Informe de actividad guardado y procesado correctamente.');
            } catch (Exception $e) {
                flash('global_msg', 'Error al guardar informe: ' . $e->getMessage(), 'danger');
            }
            header('Location: ' . URL_ROOT . '/talleres/informe/' . $id_taller);
            exit;
        }

        $data = [
            'titulo' => 'Reporte Oficial de Actividad',
            'taller' => $taller,
            'informe' => $informe
        ];

        $this->view('talleres/informe', $data);
    }
}
