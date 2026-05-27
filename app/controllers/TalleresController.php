<?php
class TalleresController extends Controller {

    public function index() {
        $talleres    = Taller::all();
        $empleados   = Empleado::facilitadoresTalleres();
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

        $_POST = $this->sanitizePost();
        $userId    = $this->getUserId();
        $esEdicion = !empty($_POST['id']);
        $esInterna = !empty($_POST['es_interna']);

        $tiposValidos  = ['Taller', 'Charla', 'Inducción'];
        $tipoActividad = in_array($_POST['tipo_actividad'] ?? '', $tiposValidos)
            ? $_POST['tipo_actividad'] : 'Taller';

        $tiposEnteValidos = ['Escuela','Liceo','Comunidad','Prestador de Servicio','IMATUR'];
        $tipoEnte = (!$esInterna && in_array($_POST['tipo_ente'] ?? '', $tiposEnteValidos))
            ? $_POST['tipo_ente'] : null;

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
            'cupo_maximo'            => min(200, max(1, (int)$_POST['cupo_maximo'])),
            'tipo_actividad'         => $tipoActividad,
            'es_interna'             => $esInterna,
            'tipo_ente'              => $tipoEnte,
            'id_oficio'              => null,
            'estado'                 => $esEdicion
                                        ? $_POST['estado']
                                        : (in_array($_POST['estado'] ?? '', ['Programado','Cancelado'])
                                            ? $_POST['estado'] : 'Programado'),
            'motivo_cancelacion'     => null,
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

                // RN-F13: Finalizado requiere informe demográfico
                if ($data['estado'] === 'Finalizado') {
                    if (Taller::getInforme($data['id']) === false || Taller::getInforme($data['id']) === null) {
                        throw new Exception('No se puede finalizar una actividad sin completar el Reporte Oficial de Actividad (informe demográfico). Ir a Detalle → Reporte Oficial.');
                    }
                }

                // Finalizado vía modal edición requiere evidencias ya guardadas
                if ($data['estado'] === 'Finalizado' && Taller::countEvidencias($data['id']) === 0) {
                    throw new Exception('Debe subir evidencias antes de finalizar. Use "Cambiar Estado" en la tarjeta.');
                }
            }

            // Aplica para creación y edición: Cancelado requiere motivo
            if ($data['estado'] === 'Cancelado') {
                $motivo = trim($_POST['motivo_cancelacion'] ?? '');
                if (empty($motivo)) {
                    $existing = $esEdicion ? (Taller::find($data['id'])->motivo_cancelacion ?? null) : null;
                    if (empty($existing)) {
                        throw new Exception('Debe indicar el motivo de cancelación.');
                    }
                    $data['motivo_cancelacion'] = $existing;
                } else {
                    $data['motivo_cancelacion'] = $motivo;
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
        $evidencias    = Taller::getEvidencias((int)$id);

        require_once '../app/models/Parroquia.php';
        require_once '../app/models/Empleado.php';
        $parroquias = Parroquia::all();
        $empleados  = !empty($taller->es_interna) ? Empleado::all() : [];

        $data = [
            'titulo'        => 'Detalle: ' . $taller->nombre,
            'taller'        => $taller,
            'participantes' => $participantes,
            'evidencias'    => $evidencias,
            'parroquias'    => $parroquias,
            'empleados'     => $empleados,
        ];
        $this->view('talleres/detalle', $data);
    }

    public function buscarPersona() {
        header('Content-Type: application/json');
        $cedula = strip_tags(trim($_GET['cedula'] ?? ''));
        if (empty($cedula)) {
            echo json_encode(['found' => false]);
            exit;
        }
        $persona = Taller::buscarPersonaPorCedula($cedula);
        if ($persona) {
            echo json_encode([
                'found'   => true,
                'persona' => [
                    'id'               => $persona->id,
                    'cedula'           => $persona->cedula,
                    'nombre'           => $persona->nombre,
                    'apellido'         => $persona->apellido,
                    'telefono'         => $persona->telefono         ?? '',
                    'correo'           => $persona->correo           ?? '',
                    'genero'           => $persona->genero           ?? '',
                    'fecha_nacimiento' => $persona->fecha_nacimiento ?? '',
                    'parroquia_id'     => $persona->parroquia_id     ?? '',
                    'direccion'        => $persona->direccion        ?? '',
                ]
            ]);
        } else {
            echo json_encode(['found' => false]);
        }
        exit;
    }

    public function inscribir() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $_POST     = $this->sanitizePost();
        $id_taller = (int)$_POST['id_taller'];
        $userId    = $this->getUserId();
        $esLibre   = !empty($_POST['tipo_participante_libre']);
        $esInterna = !empty($_POST['es_interna_taller']);

        try {
            if ($esInterna) {
                // Actividad interna: inscribir empleado directamente por id_persona
                $idPersona = (int)($_POST['id_empleado_persona'] ?? 0);
                if (!$idPersona) {
                    throw new Exception('Seleccione un empleado para inscribir.');
                }
                if (Taller::estaInscrito($id_taller, $idPersona)) {
                    throw new Exception('Este empleado ya está inscrito en esta actividad.');
                }
                Taller::inscribir($id_taller, $idPersona, $userId, false);
            } elseif ($esLibre) {
                // RN-F16: participante sin cédula (niño/a)
                $nombre = trim($_POST['nombre_libre'] ?? '');
                if (empty($nombre)) {
                    throw new Exception('El nombre del participante es requerido.');
                }
                $fechaNacLibre = trim($_POST['fecha_nac_libre'] ?? '') ?: null;
                if ($fechaNacLibre && \DateTime::createFromFormat('Y-m-d', $fechaNacLibre) === false) {
                    $fechaNacLibre = null;
                }
                Taller::inscribirLibre($id_taller, [
                    'nombre_libre'      => $nombre,
                    'apellido_libre'    => trim($_POST['apellido_libre'] ?? ''),
                    'cedula_libre'      => trim($_POST['cedula_libre'] ?? '') ?: null,
                    'nombre_docente'    => trim($_POST['nombre_docente'] ?? '') ?: null,
                    'cedula_docente'    => trim($_POST['cedula_docente'] ?? '') ?: null,
                    'fecha_nac_libre'   => $fechaNacLibre,
                    'genero_libre'      => trim($_POST['genero_libre'] ?? '') ?: null,
                    'parroquia_id_libre'=> (int)($_POST['parroquia_id_libre'] ?? 0) ?: null,
                    'direccion_libre'   => trim($_POST['direccion_libre'] ?? '') ?: null,
                ], $userId);
            } else {
                $cedula   = trim($_POST['cedula_participante'] ?? '') ?: null;
                $nombre   = trim($_POST['nombre']   ?? '');
                $apellido = trim($_POST['apellido'] ?? '');

                if (empty($nombre) || empty($apellido)) {
                    throw new Exception('El nombre y apellido del participante son requeridos.');
                }

                // Buscar persona existente por cédula; si no existe, crear nueva
                $persona = $cedula ? Taller::buscarPersonaPorCedula($cedula) : null;

                $fechaNac = trim($_POST['fecha_nacimiento'] ?? '') ?: null;
                if ($fechaNac && \DateTime::createFromFormat('Y-m-d', $fechaNac) === false) {
                    $fechaNac = null;
                }
                $parroquiaId = (int)($_POST['parroquia_id'] ?? 0) ?: null;
                $direccion   = trim($_POST['direccion'] ?? '') ?: null;

                if ($persona) {
                    $idPersona = $persona->id;

                    if (Taller::estaInscrito($id_taller, $idPersona)) {
                        throw new Exception('Este participante ya está inscrito en esta actividad.');
                    }

                    // Actualizar campos vacíos en personas con los datos recién aportados
                    $actualizacion = [];
                    if (empty($persona->telefono)         && !empty($_POST['telefono']))  $actualizacion['telefono']         = trim($_POST['telefono']);
                    if (empty($persona->correo)           && !empty($_POST['correo']))    $actualizacion['correo']           = trim($_POST['correo']);
                    if (empty($persona->genero)           && !empty($_POST['genero']))    $actualizacion['genero']           = trim($_POST['genero']);
                    if (empty($persona->fecha_nacimiento) && $fechaNac)                   $actualizacion['fecha_nacimiento'] = $fechaNac;
                    if (empty($persona->parroquia_id)     && $parroquiaId)               $actualizacion['parroquia_id']     = $parroquiaId;
                    if (empty($persona->direccion)        && $direccion)                 $actualizacion['direccion']        = $direccion;
                    if (!empty($actualizacion)) {
                        Taller::actualizarPersona($idPersona, $actualizacion, $userId);
                    }
                } else {
                    $idPersona = Taller::crearPersona([
                        'cedula'           => $cedula,
                        'nombre'           => $nombre,
                        'apellido'         => $apellido,
                        'telefono'         => trim($_POST['telefono'] ?? '') ?: null,
                        'correo'           => trim($_POST['correo']   ?? '') ?: null,
                        'genero'           => trim($_POST['genero']   ?? '') ?: null,
                        'fecha_nacimiento' => $fechaNac,
                        'parroquia_id'     => $parroquiaId,
                        'direccion'        => $direccion,
                    ], $userId);
                }

                Taller::inscribir($id_taller, $idPersona, $userId, !empty($_POST['es_brigadista']));
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

    public function listaAsistencia($id) {
        $taller = Taller::find($id);
        if (!$taller) {
            flash('global_msg', 'Actividad no encontrada.', 'danger');
            header('Location: ' . URL_ROOT . '/talleres/index');
            exit;
        }
        $participantes = Taller::getParticipantes($id);
        $data = [
            'taller'        => $taller,
            'participantes' => $participantes,
        ];
        $this->view('talleres/lista_asistencia', $data);
    }

    public function informeImprimible($id) {
        $taller = Taller::find($id);
        if (!$taller) {
            flash('global_msg', 'Actividad no encontrada.', 'danger');
            header('Location: ' . URL_ROOT . '/talleres/index');
            exit;
        }
        $informe      = Taller::getInforme($id);
        $participantes = Taller::getParticipantes($id);

        $configModel = $this->model('ConfigSistema');
        $config      = $configModel->getAll();

        $data = [
            'taller'        => $taller,
            'informe'       => $informe,
            'participantes' => $participantes,
            'config'        => $config,
        ];
        $this->view('talleres/informe_imprimible', $data);
    }

    public function cambiarEstado($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $_POST  = $this->sanitizePost();
        $userId = $this->getUserId();

        $taller = Taller::find($id);
        if (!$taller) {
            flash('global_msg', 'Actividad no encontrada.', 'danger');
            header('Location: ' . URL_ROOT . '/talleres/index');
            exit;
        }

        $nuevoEstado = $_POST['nuevo_estado'] ?? '';

        try {
            $this->validarTransicion($taller->estado, $nuevoEstado);

            $motivo = null;

            if ($nuevoEstado === 'Cancelado') {
                $motivo = trim($_POST['motivo_cancelacion'] ?? '');
                if (empty($motivo)) {
                    throw new Exception('Debe indicar el motivo de cancelación.');
                }
            }

            if ($nuevoEstado === 'En Curso') {
                if (Taller::countParticipantes((int)$id) === 0) {
                    throw new Exception('No se puede iniciar sin participantes inscritos (RN-F12).');
                }
            }

            if ($nuevoEstado === 'Finalizado') {
                if (Taller::countParticipantes((int)$id) === 0) {
                    throw new Exception('No se puede finalizar sin participantes inscritos (RN-F12).');
                }
                if (Taller::getInforme($id) === false || Taller::getInforme($id) === null) {
                    throw new Exception('Complete el Reporte Oficial de Actividad antes de finalizar.');
                }

                // Subir archivos de evidencia si se enviaron
                if (!empty($_FILES['evidencias']['name'][0])) {
                    $dir = dirname(dirname(__DIR__)) . '/public/uploads/talleres/';
                    if (!is_dir($dir)) mkdir($dir, 0755, true);

                    $allowedTypes = ['image/jpeg','image/png','image/gif','image/webp','application/pdf'];
                    $archivos = [];
                    $count    = count($_FILES['evidencias']['name']);

                    for ($i = 0; $i < $count; $i++) {
                        if ($_FILES['evidencias']['error'][$i] !== UPLOAD_ERR_OK) continue;
                        $tipo = $_FILES['evidencias']['type'][$i];
                        if (!in_array($tipo, $allowedTypes)) {
                            throw new Exception('Tipo de archivo no permitido. Solo imágenes y PDF.');
                        }
                        $ext    = strtolower(pathinfo($_FILES['evidencias']['name'][$i], PATHINFO_EXTENSION));
                        $nombre = 'ev_' . $id . '_' . time() . '_' . $i . '.' . $ext;
                        if (!move_uploaded_file($_FILES['evidencias']['tmp_name'][$i], $dir . $nombre)) {
                            throw new Exception('Error al mover el archivo de evidencia.');
                        }
                        $archivos[] = [
                            'archivo'         => $nombre,
                            'nombre_original' => $_FILES['evidencias']['name'][$i],
                            'tipo_archivo'    => $tipo,
                        ];
                    }
                    if (!empty($archivos)) {
                        Taller::saveEvidencias((int)$id, $archivos, $userId);
                    }
                }

                if (Taller::countEvidencias((int)$id) === 0) {
                    throw new Exception('Debe subir al menos una evidencia para finalizar la actividad.');
                }
            }

            Taller::cambiarEstado((int)$id, $nuevoEstado, $motivo, $userId);
            flash('global_msg', 'Estado actualizado a "' . $nuevoEstado . '" correctamente.');

        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }

        header('Location: ' . URL_ROOT . '/talleres/index');
    }

    // ── Asistencia y desinscripción de participantes ─────────────────────────

    public function marcarAsistencia() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok' => false]); exit;
        }
        $id      = (int)($_POST['id']      ?? 0);
        $asistio = !empty($_POST['asistio']) && $_POST['asistio'] !== '0';
        $userId  = $this->getUserId();
        try {
            Taller::marcarAsistencia($id, $asistio, $userId);
            echo json_encode(['ok' => true, 'asistio' => $asistio]);
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
        }
        exit;
    }

    public function marcarAsistenciaMasiva() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok' => false]); exit;
        }
        $idTaller = (int)($_POST['id_taller'] ?? 0);
        $userId   = $this->getUserId();
        try {
            Taller::marcarAsistenciaMasiva($idTaller, $userId);
            echo json_encode(['ok' => true]);
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
        }
        exit;
    }

    public function historialPersona() {
        header('Content-Type: application/json');
        $idPersona = (int)($_GET['id_persona'] ?? 0);
        if (!$idPersona) { echo json_encode([]); exit; }
        echo json_encode(Taller::getHistorialPersona($idPersona));
        exit;
    }

    public function desinscribir($id) {
        $id     = (int)$id;
        $userId = $this->getUserId();
        $ref    = $_POST['id_taller'] ?? '';
        try {
            Taller::desinscribir($id, $userId);
            flash('global_msg', 'Participante desinscrito correctamente.', 'warning');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/talleres/detalle/' . (int)$ref);
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
