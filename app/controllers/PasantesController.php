<?php
class PasantesController extends Controller {

    private Pasante $pasanteModel;
    private Empleado $empleadoModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URL_ROOT . '/auth/login');
            exit;
        }
        $this->pasanteModel  = $this->model('Pasante');
        $this->empleadoModel = $this->model('Empleado');
    }

    public function index() {
        $data = [
            'titulo'   => 'Módulo de Pasantes',
            'pasantes' => $this->pasanteModel->getPasantesConTutor()
        ];
        $this->view('pasantes/index', $data);
    }

    public function detalle($id) {
        $id = (int)$id;
        $pasante = $this->pasanteModel->getPasanteUnico($id);
        if (!$pasante) {
            flash('global_msg', 'El pasante solicitado no existe.', 'danger');
            header('Location: ' . URL_ROOT . '/pasantes/index');
            exit;
        }

        // Pasantes disponibles para agrupar en la aprobación (solo Postulados, excluyendo el actual)
        $db = new Database();
        $db->query("SELECT p.id, per.nombre, per.apellido, p.institucion, p.carrera
                    FROM pasantes p JOIN personas per ON p.id_persona=per.id
                    WHERE p.estado='Postulado' AND p.is_active=TRUE AND p.id <> :id
                    ORDER BY per.apellido, per.nombre");
        $db->bind(':id', $id);
        $postulados = $db->resultSet();

        $data = [
            'titulo'     => 'Detalle del Pasante',
            'pasante'    => $pasante,
            'documentos' => $this->pasanteModel->getDocumentos($id),
            'postulados' => $postulados,
        ];
        $this->view('pasantes/detalle', $data);
    }

    /**
     * Aprobar un pasante (Postulado → Aceptado), individual o en grupo (máx. 4 total).
     * Genera un único oficio de aceptación para todo el grupo.
     */
    public function aprobar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_ROOT . '/pasantes/detalle/' . (int)$id); return;
        }
        $id     = (int)$id;
        $userId = $this->getUserId();
        $tipo   = $_POST['tipo'] ?? 'individual';

        // Construir lista de IDs: el pasante actual + seleccionados (si grupo)
        $ids = [$id];
        if ($tipo === 'grupo' && !empty($_POST['grupo_ids'])) {
            foreach ((array)$_POST['grupo_ids'] as $gid) {
                $gid = (int)$gid;
                if ($gid > 0 && $gid !== $id) $ids[] = $gid;
            }
        }
        if (count($ids) > 4) {
            flash('global_msg', 'El grupo no puede tener más de 4 pasantes.', 'danger');
            header('Location: ' . URL_ROOT . '/pasantes/detalle/' . $id); return;
        }

        // Verificar que todos estén en estado Postulado
        $db = new Database();
        foreach ($ids as $pid) {
            $db->query("SELECT estado FROM pasantes WHERE id=:id AND is_active=TRUE");
            $db->bind(':id', $pid);
            $row = $db->single();
            if (!$row || $row->estado !== 'Postulado') {
                flash('global_msg', "El pasante #$pid no está en estado Postulado.", 'danger');
                header('Location: ' . URL_ROOT . '/pasantes/detalle/' . $id); return;
            }
        }

        // Generar UN solo correlativo para el grupo
        $oficio = ConfigSistema::generarNumeroOficio('pasante');

        // Aprobar a todos con el mismo oficio
        $db->beginTransaction();
        try {
            foreach ($ids as $pid) {
                $db->query("UPDATE pasantes SET estado='Aceptado', oficio_aceptacion=:o,
                                updated_at=CURRENT_TIMESTAMP, updated_by=:uid WHERE id=:id");
                $db->bind(':o',   $oficio);
                $db->bind(':uid', $userId);
                $db->bind(':id',  $pid);
                $db->execute();
            }
            $db->endTransaction();
        } catch (Exception $e) {
            $db->cancelTransaction();
            flash('global_msg', 'Error al aprobar: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/pasantes/detalle/' . $id); return;
        }

        $n = count($ids);
        $msg = $n > 1
            ? "$n pasantes aprobados. Oficio $oficio generado para el grupo."
            : "Pasante aprobado. Oficio $oficio generado.";
        flash('global_msg', $msg . ' La carta de aceptación ya está disponible.');
        header('Location: ' . URL_ROOT . '/pasantes/detalle/' . $id);
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = $this->sanitizePost();

            $cedula    = trim($_POST['cedula']    ?? '');
            $nombre    = trim($_POST['nombre']    ?? '');
            $apellido  = trim($_POST['apellido']  ?? '');
            $userId    = $this->getUserId();

            $fechaInicio = !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null;
            $fechaFin    = !empty($_POST['fecha_fin'])    ? $_POST['fecha_fin']    : null;

            if ($fechaInicio && $fechaFin && $fechaFin < $fechaInicio) {
                flash('global_msg', 'La fecha de fin (' . date('d/m/Y', strtotime($fechaFin)) . ') no puede ser anterior a la fecha de inicio (' . date('d/m/Y', strtotime($fechaInicio)) . ').', 'danger');
                header('Location: ' . URL_ROOT . '/pasantes/crear');
                return;
            }

            $pasanteData = [
                'institucion'            => trim($_POST['institucion']   ?? ''),
                'carrera'                => trim($_POST['carrera']       ?? ''),
                'tutor_externo'          => trim($_POST['tutor_externo'] ?? '') ?: null,
                'id_tutor_institucional' => !empty($_POST['id_tutor_institucional']) ? (int)$_POST['id_tutor_institucional'] : null,
                'fecha_inicio'           => $fechaInicio,
                'fecha_fin'              => $fechaFin,
                'estado'                 => 'Postulado'
            ];

            try {
                // Reusar persona existente o crear una nueva
                $persona = $this->pasanteModel->findPersonaByCedula($cedula);

                if ($persona) {
                    $idPersona = $persona->id;
                } else {
                    $idPersona = $this->pasanteModel->createPersona(
                        ['cedula' => $cedula, 'nombre' => $nombre, 'apellido' => $apellido],
                        $userId
                    );
                    if (!$idPersona) {
                        throw new Exception("No se pudo registrar los datos personales.");
                    }
                }

                if ($this->pasanteModel->create($idPersona, $pasanteData, $userId)) {
                    flash('global_msg', 'Expediente de pasante creado exitosamente.');
                    header('Location: ' . URL_ROOT . '/pasantes/index');
                } else {
                    throw new Exception("No se pudo registrar el pasante.");
                }
            } catch (Exception $e) {
                flash('global_msg', 'Error al registrar: ' . $e->getMessage(), 'danger');
                header('Location: ' . URL_ROOT . '/pasantes/crear');
            }
            exit;
        }

        $data = [
            'titulo'    => 'Registrar Nuevo Pasante',
            'empleados' => Empleado::all()
        ];
        $this->view('pasantes/crear', $data);
    }

    public function editar($id) {
        $pasante = $this->pasanteModel->getById($id);
        if (!$pasante) {
            flash('global_msg', 'Pasante no encontrado.', 'danger');
            header('Location: ' . URL_ROOT . '/pasantes/index');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = $this->sanitizePost();

            $idPersona = (int)($_POST['id_persona'] ?? $pasante->id_persona);
            $userId    = $this->getUserId();

            $personaData = [
                'cedula'   => trim($_POST['cedula']   ?? ''),
                'nombre'   => trim($_POST['nombre']   ?? ''),
                'apellido' => trim($_POST['apellido'] ?? '')
            ];

            $fechaInicioEd = !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null;
            $fechaFinEd    = !empty($_POST['fecha_fin'])    ? $_POST['fecha_fin']    : null;

            if ($fechaInicioEd && $fechaFinEd && $fechaFinEd < $fechaInicioEd) {
                flash('global_msg', 'La fecha de fin (' . date('d/m/Y', strtotime($fechaFinEd)) . ') no puede ser anterior a la fecha de inicio (' . date('d/m/Y', strtotime($fechaInicioEd)) . ').', 'danger');
                header('Location: ' . URL_ROOT . '/pasantes/editar/' . $id);
                return;
            }

            $pasanteData = [
                'id'                     => $id,
                'institucion'            => trim($_POST['institucion'] ?? ''),
                'carrera'                => trim($_POST['carrera']     ?? ''),
                'tutor_externo'          => trim($_POST['tutor_externo'] ?? '') ?: null,
                'id_tutor_institucional' => !empty($_POST['id_tutor_institucional']) ? (int)$_POST['id_tutor_institucional'] : null,
                'fecha_inicio'           => $fechaInicioEd,
                'fecha_fin'              => $fechaFinEd,
                'estado'                 => $_POST['estado']    ?? 'Postulado',
                'evaluacion'             => trim($_POST['evaluacion'] ?? ''),
                'nota'                   => $_POST['nota'] ?? ''
            ];

            // Agrupamiento manual: si el admin editó el oficio_aceptacion, actualizarlo.
            $oficioEditado = trim($_POST['oficio_aceptacion'] ?? '');
            if ($oficioEditado !== '') {
                $db2 = new Database();
                $db2->query("UPDATE pasantes SET oficio_aceptacion = :o WHERE id = :id");
                $db2->bind(':o', $oficioEditado);
                $db2->bind(':id', $id);
                $db2->execute();
            }

            try {
                // RN-PS01: Solo el Administrador puede aprobar (Postulado → Aceptado)
                $estadoActual = $pasante->estado ?? '';
                $estadoNuevo  = $pasanteData['estado'] ?? '';
                if ($estadoActual === 'Postulado' && $estadoNuevo === 'Aceptado') {
                    $rolActual = (int)($_SESSION['user_rol'] ?? 0);
                    if ($rolActual !== 1) {
                        throw new Exception('Solo el Administrador puede aprobar el paso de Postulado a Aceptado (RN-PS01).');
                    }
                    // Cada aceptación genera su propio correlativo. Si el admin quiere agrupar
                    // varios pasantes en una misma carta, puede editar oficio_aceptacion manualmente
                    // para asignarles el mismo número (campo editable en el formulario de edición).
                    if (empty($pasante->oficio_aceptacion)) {
                        $oficio = ConfigSistema::generarNumeroOficio('pasante');
                        $db2 = new Database();
                        $db2->query("UPDATE pasantes SET oficio_aceptacion = :o WHERE id = :id");
                        $db2->bind(':o', $oficio);
                        $db2->bind(':id', $id);
                        $db2->execute();
                    }
                }

                $this->pasanteModel->updatePersona($idPersona, $personaData, $userId);

                if ($this->pasanteModel->update($pasanteData, $userId)) {
                    $msgAdicional = ($estadoNuevo === 'Aceptado' && $estadoActual === 'Postulado')
                        ? ' La carta de aceptación ya está disponible.' : '';
                    flash('global_msg', 'Expediente actualizado correctamente.' . $msgAdicional);
                    header('Location: ' . URL_ROOT . '/pasantes/detalle/' . $id);
                } else {
                    throw new Exception("Error al actualizar el expediente.");
                }
            } catch (Exception $e) {
                flash('global_msg', 'Error: ' . $e->getMessage(), 'danger');
                header('Location: ' . URL_ROOT . '/pasantes/editar/' . $id);
            }
            exit;
        }

        $data = [
            'titulo'    => 'Editar Expediente',
            'pasante'   => $pasante,
            'empleados' => Empleado::all()
        ];
        $this->view('pasantes/editar', $data);
    }

    public function eliminar($id) {
        try {
            if ($this->pasanteModel->softDelete($id, $this->getUserId())) {
                flash('global_msg', 'Pasante desactivado y movido a la papelera.', 'warning');
            } else {
                throw new Exception("No se pudo desactivar el pasante.");
            }
        } catch (Exception $e) {
            flash('global_msg', 'Error: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/pasantes/index');
        exit;
    }

    public function carta($id) {
        $id = (int)$id;
        $pasante = $this->pasanteModel->getPasanteUnico($id);
        if (!$pasante || $pasante->estado !== 'Culminado') {
            flash('global_msg', 'La carta de culminación solo está disponible para pasantes en estado Culminado.', 'danger');
            header('Location: ' . URL_ROOT . '/pasantes/detalle/' . $id);
            exit;
        }

        // Cargar el grupo si tiene oficio_aceptacion (compañeros de la misma aceptación)
        $grupo = [$pasante];
        if (!empty($pasante->oficio_aceptacion)) {
            $db = new Database();
            $db->query("SELECT p.*, per.nombre, per.apellido, per.cedula
                        FROM pasantes p JOIN personas per ON p.id_persona=per.id
                        WHERE p.oficio_aceptacion=:o AND p.is_active=TRUE
                        ORDER BY per.apellido, per.nombre");
            $db->bind(':o', $pasante->oficio_aceptacion);
            $grp = $db->resultSet();
            if (count($grp) > 1) $grupo = $grp;
        }

        $config = ConfigSistema::getAll();
        $data = [
            'pasante'   => $pasante,
            'grupo'     => $grupo,
            'config'    => $config,
            'fecha_hoy' => $this->fechaEspanol((int)date('d'), (int)date('n'), (int)date('Y')),
        ];
        $this->view('pasantes/carta_culminacion', $data);
    }

    /**
     * Carta de Aceptación de Pasantes (generada al pasar Postulado → Aceptado).
     * Agrupa en una sola carta todos los pasantes de la misma institución
     * que comparten el mismo oficio_aceptacion.
     */
    public function cartaAceptacion($id) {
        $id = (int)$id;

        $db = new Database();
        // Datos del pasante de referencia
        $db->query("SELECT p.*, per.nombre, per.apellido, per.cedula,
                           COALESCE(tp.nombre||' '||tp.apellido,'') AS tutor_nombre,
                           d.nombre AS tutor_departamento
                    FROM pasantes p
                    JOIN personas per ON p.id_persona = per.id
                    LEFT JOIN empleados e  ON p.id_tutor_institucional = e.id
                    LEFT JOIN personas tp  ON e.id_persona = tp.id
                    LEFT JOIN departamentos d ON e.id_departamento = d.id
                    WHERE p.id = :id AND p.is_active = TRUE");
        $db->bind(':id', $id);
        $ref = $db->single();

        if (!$ref || !$ref->oficio_aceptacion) {
            flash('global_msg', 'Este pasante aún no tiene carta de aceptación generada. Apruébelo primero (Postulado → Aceptado).', 'danger');
            header('Location: ' . URL_ROOT . '/pasantes/detalle/' . $id);
            exit;
        }

        // Todos los pasantes del mismo grupo (solo por oficio_aceptacion — el grupo es explícito)
        $db->query("SELECT p.*, per.nombre, per.apellido, per.cedula,
                           COALESCE(tp.nombre||' '||tp.apellido,'') AS tutor_nombre,
                           d.nombre AS tutor_departamento
                    FROM pasantes p
                    JOIN personas per ON p.id_persona = per.id
                    LEFT JOIN empleados e  ON p.id_tutor_institucional = e.id
                    LEFT JOIN personas tp  ON e.id_persona = tp.id
                    LEFT JOIN departamentos d ON e.id_departamento = d.id
                    WHERE p.oficio_aceptacion = :oficio AND p.is_active = TRUE
                    ORDER BY per.apellido, per.nombre");
        $db->bind(':oficio', $ref->oficio_aceptacion);
        $grupo = $db->resultSet();

        // Calcular duración en semanas
        $semanas = null;
        if (!empty($ref->fecha_inicio) && !empty($ref->fecha_fin)) {
            $ini = new DateTime($ref->fecha_inicio);
            $fin = new DateTime($ref->fecha_fin);
            $semanas = (int)round($ini->diff($fin)->days / 7);
        }

        $config = ConfigSistema::getAll();

        $data = [
            'titulo'   => 'Carta de Aceptación',
            'ref'      => $ref,
            'grupo'    => $grupo,
            'semanas'  => $semanas,
            'config'   => $config,
            'fecha_hoy' => $this->fechaEspanol((int)date('d'), (int)date('n'), (int)date('Y')),
        ];
        $this->view('pasantes/carta_aceptacion', $data);
    }

    private function fechaEspanol(int $dia, int $mes, int $year): string {
        $meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
                  'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        return $dia . ' de ' . $meses[$mes] . ' de ' . $year;
    }

    public function subirDocumento($id_pasante) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $tipo          = $_POST['tipo_documento'];
            $observaciones = $_POST['observaciones'] ?? '';
            $archivoUrl    = null;

            try {
                if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
                    $fileName  = time() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($_FILES['archivo']['name']));
                    $uploadDir = dirname(dirname(__DIR__)) . '/public/uploads/pasantes/';

                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    if (move_uploaded_file($_FILES['archivo']['tmp_name'], $uploadDir . $fileName)) {
                        $archivoUrl = '/uploads/pasantes/' . $fileName;
                    } else {
                        throw new Exception("Error al mover el archivo al servidor.");
                    }
                }

                $docData = [
                    'id_pasante'     => $id_pasante,
                    'tipo_documento' => $tipo,
                    'entregado'      => true,
                    'archivo_url'    => $archivoUrl,
                    'observaciones'  => $observaciones
                ];

                if ($this->pasanteModel->saveDocumento($docData)) {
                    flash('global_msg', 'Documentación del pasante actualizada correctamente.');
                } else {
                    throw new Exception("Fallo al registrar el documento en la base de datos.");
                }
            } catch (Exception $e) {
                flash('global_msg', 'Error en gestión documental: ' . $e->getMessage(), 'danger');
            }

            header('Location: ' . URL_ROOT . '/pasantes/detalle/' . $id_pasante);
        }
    }
}
