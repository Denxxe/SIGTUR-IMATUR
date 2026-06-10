<?php
class TalleresController extends Controller {

    public function index() {
        // Auto-transición: Programado → En Curso cuando la fecha/hora de inicio ya llegó
        try { Taller::autoTransicionarProgramados(); } catch (\Exception $ignored) {}

        $porPagina = 20;
        $pagina    = max(1, (int)($_GET['p'] ?? 1));
        $filtros   = [
            'buscar'      => trim($_GET['buscar']      ?? ''),
            'estado'      => trim($_GET['estado']      ?? ''),
            'tipo'        => trim($_GET['tipo']        ?? ''),
            'es_interna'  => $_GET['es_interna']       ?? '',
            'fecha_inicio'=> trim($_GET['fecha_inicio'] ?? ''),
            'fecha_fin'   => trim($_GET['fecha_fin']   ?? ''),
        ];

        $res          = Taller::paginate($pagina, $porPagina, $filtros);
        $total        = $res['total'];
        $totalPaginas = max(1, (int)ceil($total / $porPagina));
        if ($pagina > $totalPaginas) $pagina = $totalPaginas;

        $empleados   = Empleado::facilitadoresTalleres();
        $ubicaciones = UbicacionFormacion::all();

        $data = [
            'titulo'        => 'Formación: Talleres y Charlas',
            'talleres'      => $res['items'],
            'total'         => $total,
            'pagina'        => $pagina,
            'total_paginas' => $totalPaginas,
            'por_pagina'    => $porPagina,
            'filtros'       => $filtros,
            'empleados'     => $empleados,
            'ubicaciones'   => $ubicaciones,
        ];
        $this->view('talleres/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $_POST = $this->sanitizePost();
        $userId    = $this->getUserId();
        $esEdicion = !empty($_POST['id']);
        $esInterna = !empty($_POST['es_interna']);

        $tipoActividad = in_array($_POST['tipo_actividad'] ?? '', Taller::TIPOS_ACTIVIDAD)
            ? $_POST['tipo_actividad'] : Taller::TIPOS_ACTIVIDAD[0];

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
            'cupo_maximo'            => max(1, (int)$_POST['cupo_maximo']),
            'tipo_actividad'         => $tipoActividad,
            'es_interna'             => $esInterna,
            'tipo_ente'              => $tipoEnte,
            'estado'                 => $esEdicion
                                        ? $_POST['estado']
                                        : (in_array($_POST['estado'] ?? '', Taller::ESTADOS)
                                            ? $_POST['estado'] : Taller::ESTADOS[0]),
            'motivo_cancelacion'     => null,
        ];

        try {
            // Validar fecha_fin >= fecha_inicio cuando está presente
            if (!empty($data['fecha_fin']) && $data['fecha_fin'] < $data['fecha_inicio']) {
                throw new Exception('La fecha de finalización no puede ser anterior a la fecha de inicio.');
            }

            // Validar duración mínima 10 min y máxima 5 horas cuando ambas horas están presentes
            if (!empty($data['hora_inicio']) && !empty($data['hora_fin'])) {
                $hi = strtotime('2000-01-01 ' . $data['hora_inicio']);
                $hf = strtotime('2000-01-01 ' . $data['hora_fin']);
                if ($hi !== false && $hf !== false) {
                    if ($hf <= $hi) {
                        throw new Exception('La hora de finalización debe ser posterior a la hora de inicio.');
                    }
                    $durMin = ($hf - $hi) / 60;
                    if ($durMin < 10) {
                        throw new Exception('La duración mínima de una actividad formativa es de 10 minutos.');
                    }
                    if ($durMin > 300) {
                        throw new Exception('La duración máxima es de 5 horas. Para sesiones más extensas regístrelas como actividades separadas.');
                    }
                }
            }

            if ($esEdicion) {
                // RN-F13: validar transición de estado
                $actual = Taller::find($data['id']);
                if ($actual) {
                    $this->validarTransicion($actual->estado, $data['estado']);
                }

                // RN-F12: En Curso y Finalizado requieren al menos un participante
                if (in_array($data['estado'], ['En Curso', 'Finalizado'])) {
                    if (Taller::countParticipantes($data['id']) === 0) {
                        throw new Exception('No se puede cambiar a "' . $data['estado'] . '" sin participantes inscritos (RN-F12).');
                    }
                }

                // Finalizado: procesar evidencias adjuntadas en el modal de edición
                if ($data['estado'] === 'Finalizado' && !empty($_FILES['evidencias']['name'][0])) {
                    $dir = dirname(dirname(__DIR__)) . '/public/uploads/talleres/';
                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                    $allowedTypes = ['image/jpeg','image/png','image/gif','image/webp','application/pdf'];
                    $archivos = [];
                    $count = count($_FILES['evidencias']['name']);
                    for ($i = 0; $i < $count; $i++) {
                        if ($_FILES['evidencias']['error'][$i] !== UPLOAD_ERR_OK) continue;
                        $tipo = $_FILES['evidencias']['type'][$i];
                        if (!in_array($tipo, $allowedTypes)) {
                            throw new Exception('Tipo de archivo no permitido. Solo imágenes y PDF.');
                        }
                        $ext    = strtolower(pathinfo($_FILES['evidencias']['name'][$i], PATHINFO_EXTENSION));
                        $nombre = 'ev_' . $data['id'] . '_' . time() . '_' . $i . '.' . $ext;
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
                        Taller::saveEvidencias((int)$data['id'], $archivos, $userId);
                    }
                }

                // Finalizado requiere al menos una evidencia (cargada ahora o anteriormente)
                if ($data['estado'] === 'Finalizado' && Taller::countEvidencias($data['id']) === 0) {
                    throw new Exception('Debe adjuntar al menos una evidencia para finalizar la actividad.');
                }

                // Auto-generar informe demográfico desde participantes activos (store → edición modal)
                if ($data['estado'] === 'Finalizado') {
                    Taller::autoGenerarInforme((int)$data['id']);
                }
            }

            // Anti-duplicado: solo al crear (en edición el propio registro daría falso positivo)
            if (!$esEdicion && !empty($data['id_facilitador'])) {
                $ubiId = !empty($data['id_ubicacion_formacion']) ? (int)$data['id_ubicacion_formacion'] : null;
                $dup   = Taller::findDuplicate(
                    $data['nombre'],
                    $data['fecha_inicio'],
                    (int)$data['id_facilitador'],
                    $ubiId
                );
                if ($dup) {
                    throw new Exception(
                        'Ya existe una actividad con el mismo nombre, fecha, facilitador y sede '
                        . '(ID #' . $dup->id . ' — "' . $dup->nombre . '", '
                        . date('d/m/Y', strtotime($dup->fecha_inicio)) . ', estado: ' . $dup->estado . '). '
                        . 'Si es una actividad distinta, cambia el nombre, la fecha o la sede.'
                    );
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
            // RN-F12: no se pueden inscribir participantes en una actividad ya finalizada
            $tallerActual = Taller::find($id_taller);
            if (!$tallerActual) throw new Exception('Actividad no encontrada.');
            if ($tallerActual->estado === 'Finalizado') {
                throw new Exception('No se pueden inscribir participantes en una actividad ya finalizada.');
            }

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
                // RN-F16: fecha de nacimiento obligatoria y en rango válido (5-11 años)
                $fechaNacLibreRaw = trim($_POST['fecha_nac_libre'] ?? '');
                if (empty($fechaNacLibreRaw)) {
                    throw new Exception('La fecha de nacimiento es obligatoria para participantes sin cédula de identidad.');
                }
                if (\DateTime::createFromFormat('Y-m-d', $fechaNacLibreRaw) === false) {
                    throw new Exception('El formato de fecha de nacimiento no es válido.');
                }
                $fnacDt    = new \DateTime($fechaNacLibreRaw);
                $hoyDt     = new \DateTime();
                if ($fnacDt >= $hoyDt) {
                    throw new Exception('La fecha de nacimiento no puede ser una fecha futura.');
                }
                $edadAnios = (int)$hoyDt->diff($fnacDt)->y;
                if ($edadAnios < 5) {
                    throw new Exception('El participante debe tener al menos 5 años para inscribirse en una actividad formativa.');
                }
                if ($edadAnios >= 12) {
                    throw new Exception('Los participantes de 12 años o más deben registrarse con su cédula de identidad en el formulario estándar.');
                }
                $fechaNacLibre = $fechaNacLibreRaw;
                $cedulaLibre   = trim($_POST['cedula_libre'] ?? '') ?: null;
                $apellidoLibre = trim($_POST['apellido_libre'] ?? '');

                // Representante / docente obligatorio: ancla la identidad del menor
                // sin cédula (adulto con cédula = identificador estable).
                $nombreRep = trim($_POST['nombre_docente'] ?? '');
                $cedulaRep = preg_replace('/\D/', '', trim($_POST['cedula_docente'] ?? ''));
                if ($nombreRep === '' || $cedulaRep === '') {
                    throw new Exception('El representante/docente (nombre y cédula) es obligatorio para participantes sin cédula.');
                }
                if (strlen($cedulaRep) < 6 || strlen($cedulaRep) > 8) {
                    throw new Exception('La cédula del representante debe tener entre 6 y 8 dígitos.');
                }

                // Anti-duplicado en la MISMA actividad (mismo niño/a sin cédula)
                if (Taller::estaInscritoLibre($id_taller, $nombre, $apellidoLibre, $fechaNacLibre, $cedulaLibre)) {
                    throw new Exception('Ya hay un participante con ese nombre y fecha de nacimiento inscrito en esta actividad.');
                }
                Taller::inscribirLibre($id_taller, [
                    'nombre_libre'      => $nombre,
                    'apellido_libre'    => $apellidoLibre,
                    'cedula_libre'      => $cedulaLibre,
                    'nombre_docente'    => $nombreRep,
                    'cedula_docente'    => $cedulaRep,
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

                // Validar formato de cédula venezolana (V/E/J/G/C/P + 6-9 dígitos)
                if ($cedula !== null) {
                    $cedulaN = strtoupper(preg_replace('/[\s\.\-]/', '', $cedula));
                    if (!preg_match('/^[VEJGCP]?\d{6,9}$/', $cedulaN)) {
                        throw new Exception('La cédula no es válida. Use solo números (6 a 8 dígitos).');
                    }
                    // Guardar/buscar siempre con solo dígitos (formato normalizado)
                    $cedula = preg_replace('/\D/', '', $cedula);
                }

                // Validar formato de correo electrónico
                $correoRaw = trim($_POST['correo'] ?? '') ?: null;
                if ($correoRaw !== null && !$this->emailValido($correoRaw)) {
                    throw new Exception('El correo electrónico no es válido (sin espacios ni símbolos especiales; ejemplo: nombre@dominio.com).');
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

                Taller::inscribir($id_taller, $idPersona, $userId, false);
            }
            // REGLA DE NEGOCIO (decisión confirmada): el cupo_maximo es una ESTIMACIÓN
            // de planificación, NO un límite rígido. El sistema permite el overbooking
            // y solo emite una ADVERTENCIA no bloqueante al alcanzar o superar el cupo.
            // Esto es intencional: en actividades comunitarias la asistencia real puede
            // exceder lo planificado y no debe impedirse el registro. Mismo criterio en rutas.
            $inscritosPost = Taller::countParticipantes($id_taller);
            $cupoMax       = (int)($tallerActual->cupo_maximo ?? 0);
            if ($cupoMax > 0 && $inscritosPost >= $cupoMax) {
                flash('global_msg', 'Participante registrado. Aviso: el cupo estimado de ' . $cupoMax . ' personas ha sido alcanzado o superado.', 'warning');
            } else {
                flash('global_msg', 'Participante registrado correctamente.');
            }

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

        // Pre-calcular conteo demográfico sugerido desde participantes registrados
        $sugeridos      = ['mujeres' => 0, 'hombres' => 0, 'ninas' => 0, 'ninos' => 0];
        $totalSugeridos = 0;
        foreach (Taller::getParticipantes($id_taller) as $p) {
            $esLibre  = empty($p->id_persona);
            $genero   = $esLibre ? ($p->genero_libre  ?? '') : ($p->genero          ?? '');
            $fechaNac = $esLibre ? ($p->fecha_nac_libre ?? null) : ($p->fecha_nacimiento ?? null);
            $edadV    = !empty($fechaNac)
                ? (int)(new \DateTime())->diff(new \DateTime($fechaNac))->y
                : 99; // Sin fecha → adulto
            $totalSugeridos++;
            if ($edadV < 12) {
                if ($genero === 'F') $sugeridos['ninas']++;
                else                 $sugeridos['ninos']++;
            } else {
                if ($genero === 'F') $sugeridos['mujeres']++;
                else                 $sugeridos['hombres']++;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mujeres = (int)$_POST['mujeres'];
            $hombres = (int)$_POST['hombres'];
            $ninas   = (int)$_POST['ninas'];
            $ninos   = (int)$_POST['ninos'];

            $data = [
                'id_taller'               => $id_taller,
                'unidad_estadal'          => trim($_POST['unidad_estadal'] ?? 'Sucre'),
                'lugar_exacto'            => trim($_POST['lugar_exacto'] ?? ''),
                'instituciones_presentes' => trim($_POST['instituciones_presentes'] ?? ''),
                'mujeres'                 => $mujeres,
                'hombres'                 => $hombres,
                'ninas'                   => $ninas,
                'ninos'                   => $ninos,
                'resumen_actividad'       => trim($_POST['resumen_actividad'] ?? '')
            ];
            try {
                // Validar valores demográficos
                if ($mujeres < 0 || $hombres < 0 || $ninas < 0 || $ninos < 0) {
                    throw new Exception('Los valores demográficos no pueden ser negativos.');
                }
                if (($mujeres + $hombres + $ninas + $ninos) === 0) {
                    throw new Exception('Debe registrar al menos un asistente en el informe demográfico.');
                }
                if (empty(trim($_POST['resumen_actividad'] ?? ''))) {
                    throw new Exception('El resumen de la actividad es obligatorio en el informe.');
                }
                Taller::saveInforme($data);
                flash('global_msg', 'Informe guardado correctamente.');
            } catch (Exception $e) {
                flash('global_msg', 'Error al guardar informe: ' . $e->getMessage(), 'danger');
            }
            header('Location: ' . URL_ROOT . '/talleres/informe/' . $id_taller);
            exit;
        }

        $data = [
            'titulo'         => 'Reporte Oficial de Actividad',
            'taller'         => $taller,
            'informe'        => $informe,
            'sugeridos'      => $sugeridos,
            'totalSugeridos' => $totalSugeridos,
        ];
        $this->view('talleres/informe', $data);
    }

    public function exportarInformeCsv($id) {
        $this->requireRoles([1, 3]);
        try {
            $db = new Database();

            $db->query("SELECT t.*, uf.nombre AS sede,
                               p.nombre AS fac_nombre, p.apellido AS fac_apellido
                        FROM talleres t
                        LEFT JOIN ubicaciones_formacion uf ON t.id_ubicacion_formacion = uf.id
                        INNER JOIN empleados e ON t.id_facilitador = e.id
                        INNER JOIN personas  p ON e.id_persona = p.id
                        WHERE t.id = :id");
            $db->bind(':id', $id);
            $taller = $db->single();
            if (!$taller) throw new Exception('Actividad no encontrada.');

            $db->query("SELECT * FROM taller_informes WHERE id_taller = :id");
            $db->bind(':id', $id);
            $informe = $db->single();

            $db->query("SELECT
                               CASE WHEN pt.id_persona IS NULL THEN 'Niño/a' ELSE 'Participante' END AS tipo,
                               COALESCE(p.cedula, pt.cedula_libre, '')    AS cedula,
                               COALESCE(p.nombre, pt.nombre_libre, '')    AS nombre,
                               COALESCE(p.apellido, pt.apellido_libre, '') AS apellido,
                               COALESCE(p.telefono, '')                   AS telefono,
                               COALESCE(p.correo, '')                     AS correo,
                               CASE WHEN pt.id_persona IS NULL
                                    THEN CASE pt.genero_libre WHEN 'M' THEN 'Masculino' WHEN 'F' THEN 'Femenino' WHEN 'O' THEN 'Otro' ELSE '' END
                                    ELSE CASE p.genero      WHEN 'M' THEN 'Masculino' WHEN 'F' THEN 'Femenino' WHEN 'O' THEN 'Otro' ELSE '' END
                               END AS genero,
                               COALESCE(p.fecha_nacimiento::text, pt.fecha_nac_libre::text, '') AS fecha_nac,
                               pt.asistio,
                               COALESCE(pt.nombre_docente, '') AS nombre_docente,
                               COALESCE(pt.cedula_docente, '')  AS cedula_docente,
                               CASE WHEN pt.id_persona IS NULL THEN COALESCE(par_libre.nombre, '') ELSE COALESCE(par_pers.nombre, '') END AS parroquia,
                               CASE WHEN pt.id_persona IS NULL THEN COALESCE(mun_libre.nombre, '') ELSE COALESCE(mun_pers.nombre, '') END AS municipio,
                               COALESCE(pt.direccion_libre, COALESCE(p.direccion, '')) AS direccion
                        FROM participantes_taller pt
                        LEFT JOIN personas  p         ON pt.id_persona        = p.id
                        LEFT JOIN parroquia par_libre ON pt.parroquia_id_libre = par_libre.id
                        LEFT JOIN parroquia par_pers  ON p.parroquia_id        = par_pers.id
                        LEFT JOIN municipio mun_libre ON par_libre.id_municipio = mun_libre.id
                        LEFT JOIN municipio mun_pers  ON par_pers.id_municipio  = mun_pers.id
                        WHERE pt.id_taller = :id AND pt.is_active = TRUE
                        ORDER BY COALESCE(p.apellido, pt.apellido_libre) ASC");
            $db->bind(':id', $id);
            $participantes = $db->resultSet();
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar informe: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/talleres/informe/' . $id);
            exit;
        }

        $nombreArchivo = 'Informe_' . preg_replace('/[^A-Za-z0-9_]/', '_', $taller->nombre ?? 'actividad');
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '_' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        // Membrete institucional
        fputcsv($out, ['REPÚBLICA BOLIVARIANA DE VENEZUELA'], ';');
        fputcsv($out, ['ALCALDÍA BOLIVARIANA DEL MUNICIPIO SUCRE'], ';');
        fputcsv($out, ['Instituto Municipal Autónomo de Turismo (IMATUR-SUCRE)  —  RIF. G-20008498-7'], ';');
        fputcsv($out, ['Cumaná, Estado Sucre'], ';');
        fputcsv($out, ['Generado por: ' . ($_SESSION['user_username'] ?? 'Sistema') . '    Fecha: ' . date('d/m/Y H:i')], ';');
        fputcsv($out, [''], ';');

        // Ficha de la actividad
        fputcsv($out, ['DATOS DE LA ACTIVIDAD'], ';');
        fputcsv($out, ['Actividad',    $taller->nombre], ';');
        fputcsv($out, ['Tipo',         $taller->tipo_actividad ?? ''], ';');
        fputcsv($out, ['Ámbito',       !empty($taller->es_interna) ? 'Interna (Personal IMATUR)' : 'Externa · ' . ($taller->tipo_ente ?? '')], ';');
        fputcsv($out, ['Facilitador',  trim(($taller->fac_nombre ?? '') . ' ' . ($taller->fac_apellido ?? ''))], ';');
        fputcsv($out, ['Sede',         $taller->sede ?? '—'], ';');
        fputcsv($out, ['Fecha',        $taller->fecha_inicio ?? ''], ';');
        fputcsv($out, ['Hora',         $taller->hora_inicio  ?? ''], ';');
        fputcsv($out, ['Estado',       $taller->estado], ';');
        fputcsv($out, ['Cupo máximo',  $taller->cupo_maximo ?? 0], ';');
        fputcsv($out, [''], ';');

        // Informe demográfico
        if ($informe) {
            fputcsv($out, ['RESUMEN DEMOGRÁFICO'], ';');
            fputcsv($out, ['Lugar exacto',            $informe->lugar_exacto            ?? ''], ';');
            fputcsv($out, ['Instituciones presentes', $informe->instituciones_presentes ?? ''], ';');
            fputcsv($out, ['Mujeres',                 (int)($informe->mujeres ?? 0)], ';');
            fputcsv($out, ['Hombres',                 (int)($informe->hombres ?? 0)], ';');
            fputcsv($out, ['Niñas (5-11 años)',       (int)($informe->ninas   ?? 0)], ';');
            fputcsv($out, ['Niños (5-11 años)',       (int)($informe->ninos   ?? 0)], ';');
            fputcsv($out, ['Total atendidos',         (int)($informe->total_atendidas ?? 0)], ';');
            fputcsv($out, ['Resumen de la actividad', $informe->resumen_actividad ?? ''], ';');
        } else {
            fputcsv($out, ['INFORME DEMOGRÁFICO', 'Pendiente de completar'], ';');
        }
        fputcsv($out, [''], ';');

        // Listado de participantes
        fputcsv($out, ['LISTADO DE PARTICIPANTES (' . count($participantes) . ')'], ';');
        fputcsv($out, ['N°', 'Tipo', 'Cédula/ID', 'Nombre', 'Apellido', 'Teléfono', 'Correo', 'Género', 'Fecha Nac.', 'Edad', 'Parroquia', 'Municipio', 'Dirección', 'Asistió', 'Docente/Tutor', 'C.I. Docente'], ';');
        $n = 0;
        foreach ($participantes as $p) {
            $n++;
            $edad = '';
            if (!empty($p->fecha_nac)) {
                try { $edad = (new DateTime())->diff(new DateTime($p->fecha_nac))->y; } catch (Exception $e) { $edad = ''; }
            }
            fputcsv($out, [
                $n,
                $p->tipo,
                $p->cedula,
                $p->nombre,
                $p->apellido,
                $p->telefono,
                $p->correo,
                $p->genero,
                $p->fecha_nac,
                $edad,
                $p->parroquia,
                $p->municipio,
                $p->direccion,
                $p->asistio ? 'Sí' : 'No',
                $p->nombre_docente,
                $p->cedula_docente,
            ], ';');
        }
        fputcsv($out, [''], ';');
        $totalAsist = 0; foreach ($participantes as $p) { if ($p->asistio) $totalAsist++; }
        fputcsv($out, ['Total inscritos', count($participantes)], ';');
        fputcsv($out, ['Total asistieron', $totalAsist], ';');

        fclose($out);
        exit;
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
        // Actividades externas usan el formato CONTROL DE ASISTENCIA (institución/empresa)
        $vista = (!empty($taller->es_interna) && $taller->es_interna !== 'f')
                 ? 'talleres/lista_asistencia'
                 : 'talleres/lista_asistencia_externa';
        $this->view($vista, $data);
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

                // Auto-generar informe demográfico desde participantes activos (cambiarEstado)
                Taller::autoGenerarInforme((int)$id);
            }

            Taller::cambiarEstado((int)$id, $nuevoEstado, $motivo, $userId);

            // RN-F12: al finalizar, todos los participantes activos quedan marcados como asistentes
            if ($nuevoEstado === 'Finalizado') {
                Taller::marcarAsistenciaMasiva((int)$id, $userId);
            }

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

    // Editar datos de un participante ya inscrito (RN-F16 + datos de persona)
    public function actualizarParticipante() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $_POST     = $this->sanitizePost();
        $id_pt     = (int)($_POST['id_pt']     ?? 0);
        $id_taller = (int)($_POST['id_taller'] ?? 0);
        $userId    = $this->getUserId();

        try {
            $pt = Taller::getParticipante($id_pt);
            if (!$pt) throw new Exception('Participante no encontrado.');
            $esLibre = empty($pt->id_persona);

            if ($esLibre) {
                $nombre = trim($_POST['nombre_libre'] ?? '');
                if (empty($nombre)) throw new Exception('El nombre del participante es requerido.');

                $fechaRaw = trim($_POST['fecha_nac_libre'] ?? '');
                if (empty($fechaRaw)) throw new Exception('La fecha de nacimiento es obligatoria.');
                if (\DateTime::createFromFormat('Y-m-d', $fechaRaw) === false) {
                    throw new Exception('El formato de fecha de nacimiento no es válido.');
                }
                $edad = (int)(new \DateTime())->diff(new \DateTime($fechaRaw))->y;
                if ($edad < 5)  throw new Exception('El participante debe tener al menos 5 años.');
                if ($edad >= 12) throw new Exception('Los participantes de 12 años o más deben registrarse con cédula.');

                // Representante / docente obligatorio (ancla la identidad del menor)
                $nombreRep = trim($_POST['nombre_docente'] ?? '');
                $cedulaRep = preg_replace('/\D/', '', trim($_POST['cedula_docente'] ?? ''));
                if ($nombreRep === '' || $cedulaRep === '') {
                    throw new Exception('El representante/docente (nombre y cédula) es obligatorio para participantes sin cédula.');
                }
                if (strlen($cedulaRep) < 6 || strlen($cedulaRep) > 8) {
                    throw new Exception('La cédula del representante debe tener entre 6 y 8 dígitos.');
                }

                Taller::actualizarParticipanteLibre($id_pt, [
                    'nombre_libre'       => $nombre,
                    'apellido_libre'     => trim($_POST['apellido_libre'] ?? '') ?: null,
                    'cedula_libre'       => trim($_POST['cedula_libre']   ?? '') ?: null,
                    'nombre_docente'     => $nombreRep,
                    'cedula_docente'     => $cedulaRep,
                    'fecha_nac_libre'    => $fechaRaw,
                    'genero_libre'       => trim($_POST['genero_libre']   ?? '') ?: null,
                    'parroquia_id_libre' => (int)($_POST['parroquia_id_libre'] ?? 0) ?: null,
                    'direccion_libre'    => trim($_POST['direccion_libre'] ?? '') ?: null,
                ], $userId);
            } else {
                $nombre   = trim($_POST['nombre']   ?? '');
                $apellido = trim($_POST['apellido'] ?? '');
                if (empty($nombre) || empty($apellido)) {
                    throw new Exception('El nombre y apellido son requeridos.');
                }
                $correo = trim($_POST['correo'] ?? '') ?: null;
                if ($correo !== null && !$this->emailValido($correo)) {
                    throw new Exception('El correo electrónico no es válido (sin espacios ni símbolos especiales; ejemplo: nombre@dominio.com).');
                }
                $fechaNac = trim($_POST['fecha_nacimiento'] ?? '') ?: null;
                if ($fechaNac && \DateTime::createFromFormat('Y-m-d', $fechaNac) === false) $fechaNac = null;

                Taller::actualizarPersona((int)$pt->id_persona, [
                    'nombre'           => $nombre,
                    'apellido'         => $apellido,
                    'telefono'         => trim($_POST['telefono'] ?? '') ?: null,
                    'correo'           => $correo,
                    'genero'           => trim($_POST['genero'] ?? '') ?: null,
                    'fecha_nacimiento' => $fechaNac,
                    'parroquia_id'     => (int)($_POST['parroquia_id'] ?? 0) ?: null,
                    'direccion'        => trim($_POST['direccion'] ?? '') ?: null,
                ], $userId);
            }
            flash('global_msg', 'Datos del participante actualizados correctamente.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/talleres/detalle/' . $id_taller);
    }

    // ── RN-F13: Máquina de estados ───────────────────────────────────────────

    /**
     * AJAX: verifica si ya existe un taller con el mismo nombre+fecha+facilitador.
     * Devuelve JSON: { duplicate: bool, id, nombre, fecha, estado }
     */
    public function verificarDuplicado() {
        header('Content-Type: application/json');
        $nombre    = trim($_GET['nombre']   ?? '');
        $fecha     = trim($_GET['fecha']    ?? '');
        $facId     = (int)($_GET['id_fac']  ?? 0);
        $ubiId     = !empty($_GET['id_ubi']) ? (int)$_GET['id_ubi'] : null;
        $excludeId = (int)($_GET['excl_id'] ?? 0) ?: null;

        if ($nombre === '' || $fecha === '' || $facId === 0) {
            echo json_encode(['duplicate' => false]); exit;
        }
        $dup = Taller::findDuplicate($nombre, $fecha, $facId, $ubiId, $excludeId);
        if ($dup) {
            echo json_encode([
                'duplicate' => true,
                'id'        => $dup->id,
                'nombre'    => $dup->nombre,
                'fecha'     => date('d/m/Y', strtotime($dup->fecha_inicio)),
                'estado'    => $dup->estado,
            ]);
        } else {
            echo json_encode(['duplicate' => false]);
        }
        exit;
    }

    private function validarTransicion(string $desde, string $hacia): void {
        if (in_array($desde, Taller::ESTADOS_TERMINALES, true)) {
            throw new Exception("La actividad está en estado '{$desde}', que es definitivo y no admite cambios de estado.");
        }
        $permitidas = Taller::TRANSICIONES;
        if (!isset($permitidas[$desde]) || !in_array($hacia, $permitidas[$desde])) {
            throw new Exception("Cambio de estado no permitido: '{$desde}' → '{$hacia}'.");
        }
    }
}
