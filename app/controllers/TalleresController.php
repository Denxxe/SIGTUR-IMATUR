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

            $taller = new Taller($data);
            if ($taller->save($this->getUserId())) {
                header('Location: ' . URL_ROOT . '/talleres/index');
            } else {
                die('Error al guardar el taller');
            }
        }
    }

    /**
     * Ver detalle de un taller y sus participantes
     */
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

    /**
     * Inscribir participante
     */
    public function inscribir() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_taller = (int)$_POST['id_taller'];
            $id_persona = (int)$_POST['id_persona'];

            if (Taller::inscribir($id_taller, $id_persona, 1)) {
                header('Location: ' . URL_ROOT . '/talleres/detalle/' . $id_taller);
            } else {
                die('Error al inscribir participante');
            }
        }
    }

    public function delete($id) {
        if (Taller::delete($id, $this->getUserId())) {
            header('Location: ' . URL_ROOT . '/talleres/index');
        }
    }
}
