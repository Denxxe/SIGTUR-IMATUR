<?php
class TalleresController extends Controller {

    public function index() {
        $talleres   = Taller::all();
        $empleados  = Empleado::all();
        $ubicaciones = UbicacionFormacion::all();

        $data = [
            'titulo'      => 'Formación: Talleres y Charlas',
            'talleres'    => $talleres,
            'empleados'   => $empleados,
            'ubicaciones' => $ubicaciones
        ];
        $this->view('talleres/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $_POST   = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $userId  = $this->getUserId();
        $esEdicion = !empty($_POST['id']);

        $data = [
            'id'                     => $esEdicion ? (int)$_POST['id'] : null,
            'nombre'                 => trim($_POST['nombre']),
            'descripcion'            => trim($_POST['descripcion'] ?? ''),
            'fecha_inicio'           => $_POST['fecha_inicio'],
            'fecha_fin'              => $_POST['fecha_fin'] ?: null,
            'hora_inicio'            => $_POST['hora_inicio'] ?: null,
            'hora_fin'               => $_POST['hora_fin'] ?: null,
            'id_ubicacion_formacion' => (int)$_POST['id_ubicacion_formacion'] ?: null,
            'id_facilitador'         => (int)$_POST['id_facilitador'],
            'cupo_maximo'            => (int)$_POST['cupo_maximo'],
            'tipo_actividad'         => $_POST['tipo_actividad'] ?? 'Taller',
            'id_oficio'              => null,
            // RN-F13: nuevas actividades siempre inician como Programado
            'estado'                 => $esEdicion ? $_POST['estado'] : 'Programado',
        ];

        try {
            if ($esEdicion) {
                // RN-F13: validar transición de estado
                $actual = Taller::find($data['id']);
                if ($actual) {
                    $this->validarTransicion($actual->estado, $data['estado']);
                }

                // RN-F12: Finalizado requiere al menos un participante
                if ($data['estado'] === 'Finalizado') {
                    if (Taller::countParticipantes($data['id']) === 0) {
                        throw new Exception('No se puede finalizar una actividad sin participantes inscritos (RN-F12).');
                    }
                }
            } else {
                // RN-F05/F06: actividad externa requiere oficio
                if (!empty($data['id_ubicacion_formacion'])) {
                    $ubi = UbicacionFormacion::find($data['id_ubicacion_formacion']);
                    $esExterna = isset($ubi->es_sede_propia)
                        && !filter_var($ubi->es_sede_propia, FILTER_VALIDATE_BOOLEAN);

                    if ($esExterna) {
                        $fechaOficio = trim($_POST['oficio_fecha'] ?? '');
                        if (empty($fechaOficio)) {
                            throw new Exception('Las actividades externas requieren la fecha del oficio recibido (RN-F06).');
                        }
                        $data['id_oficio'] = Taller::crearOficio([
                            'numero'          => trim($_POST['oficio_numero'] ?? ''),
                            'fecha'           => $fechaOficio,
                            'id_institucion'  => $data['id_ubicacion_formacion'],
                            'asunto'          => trim($_POST['oficio_asunto'] ?? ''),
                        ], $userId);
                    }
                }
            }

            $taller = new Taller($data);
            if ($taller->save($userId)) {
                $msg = $esEdicion ? 'Actividad actualizada correctamente.' : 'Actividad programada exitosamente.';
                flash('global_msg', $msg);
            } else {
                throw new Exception('Error al guardar la actividad.');
            }

        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }

        header('Location: ' . URL_ROOT . '/talleres/index');
    }

    public function detalle($id) {
        $taller = Taller::find($id);
        if (!$taller) {
            header('Location: ' . URL_ROOT . '/talleres/index');
            exit;
        }
        $participantes = Taller::getParticipantes($id);

        $data = [
            'titulo'        => 'Detalle: ' . $taller->nombre,
            'taller'        => $taller,
            'participantes' => $participantes
        ];
        $this->view('talleres/detalle', $data);
    }

    public function inscribir() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $_POST     = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $id_taller = (int)$_POST['id_taller'];
        $userId    = $this->getUserId();
        $esLibre   = !empty($_POST['tipo_participante_libre']);

        try {
            if ($esLibre) {
                // RN-F16: participante sin cédula (niño/a)
                $nombre = trim($_POST['nombre_libre'] ?? '');
                if (empty($nombre)) {
                    throw new Exception('El nombre del participante es requerido.');
                }
                Taller::inscribirLibre($id_taller, [
                    'nombre_libre'   => $nombre,
                    'apellido_libre' => trim($_POST['apellido_libre'] ?? ''),
                    'cedula_libre'   => trim($_POST['cedula_libre'] ?? '') ?: null,
                ], $userId);
            } else {
                $cedula = trim($_POST['cedula_busqueda'] ?? '');
                if (empty($cedula)) {
                    throw new Exception('Ingrese la cédula del participante.');
                }
                $persona = Taller::buscarPersonaPorCedula($cedula);
                if (!$persona) {
                    throw new Exception("No se encontró ninguna persona con cédula '{$cedula}' en el sistema.");
                }
                Taller::inscribir($id_taller, $persona->id, $userId);
            }
            flash('global_msg', 'Participante registrado correctamente.');

        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }

        header('Location: ' . URL_ROOT . '/talleres/detalle/' . $id_taller);
    }

    public function delete($id) {
        try {
            if (Taller::delete($id, $this->getUserId())) {
                flash('global_msg', 'Actividad movida a papelera.', 'warning');
            } else {
                throw new Exception('No se puede eliminar en este momento.');
            }
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_taller'               => $id_taller,
                'unidad_estadal'          => trim($_POST['unidad_estadal'] ?? 'Sucre'),
                'lugar_exacto'            => trim($_POST['lugar_exacto'] ?? ''),
                'instituciones_presentes' => trim($_POST['instituciones_presentes'] ?? ''),
                'mujeres'                 => (int)$_POST['mujeres'],
                'hombres'                 => (int)$_POST['hombres'],
                'ninas'                   => (int)$_POST['ninas'],
                'ninos'                   => (int)$_POST['ninos'],
                'resumen_actividad'       => trim($_POST['resumen_actividad'] ?? '')
            ];
            try {
                Taller::saveInforme($data);
                flash('global_msg', 'Informe guardado correctamente.');
            } catch (Exception $e) {
                flash('global_msg', 'Error al guardar informe: ' . $e->getMessage(), 'danger');
            }
            header('Location: ' . URL_ROOT . '/talleres/informe/' . $id_taller);
            exit;
        }

        $data = [
            'titulo'  => 'Reporte Oficial de Actividad',
            'taller'  => $taller,
            'informe' => $informe
        ];
        $this->view('talleres/informe', $data);
    }

    // ── RN-F13: Máquina de estados ───────────────────────────────────────────

    private function validarTransicion(string $desde, string $hacia): void {
        $permitidas = [
            'Programado' => ['Programado', 'En Curso', 'Cancelado'],
            'En Curso'   => ['En Curso', 'Finalizado', 'Cancelado'],
            'Finalizado' => ['Finalizado'],
            'Cancelado'  => ['Cancelado'],
        ];
        if (!isset($permitidas[$desde]) || !in_array($hacia, $permitidas[$desde])) {
            throw new Exception("Cambio de estado no permitido: '{$desde}' → '{$hacia}'.");
        }
    }
}
