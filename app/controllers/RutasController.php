<?php
class RutasController extends Controller {

    public function index() {
        $porPagina = 12;
        $pagina    = max(1, (int)($_GET['p'] ?? 1));
        $filtros   = [
            'buscar'      => trim($_GET['buscar']      ?? ''),
            'estado'      => trim($_GET['estado']      ?? ''),
            'tipo'        => trim($_GET['tipo']        ?? ''),
            'fecha_desde' => trim($_GET['fecha_desde'] ?? ''),
            'fecha_hasta' => trim($_GET['fecha_hasta'] ?? ''),
        ];
        $res          = Ruta::paginate($pagina, $porPagina, $filtros);
        $totalReg     = $res['total'];
        $totalPaginas = max(1, (int)ceil($totalReg / $porPagina));
        if ($pagina > $totalPaginas) $pagina = $totalPaginas;

        $data = [
            'titulo'        => 'Gestión de Rutas Turísticas',
            'rutas'         => $res['items'],
            'empleados'     => Empleado::all(),
            'departamentos' => Departamento::all(),
            'pagina'        => $pagina,
            'total_paginas' => $totalPaginas,
            'total'         => $totalReg,
            'por_pagina'    => $porPagina,
            'filtros'       => $filtros,
        ];
        $this->view('rutas/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $_POST = $this->sanitizePost();
        $userId    = $this->getUserId();
        $esEdicion = !empty($_POST['id']);

        $estado  = in_array($_POST['estado'] ?? '', Ruta::ESTADOS) ? $_POST['estado'] : Ruta::ESTADOS[0];
        $tipoRuta = in_array($_POST['tipo_ruta'] ?? '', Ruta::$TIPOS_RUTA) ? $_POST['tipo_ruta'] : 'General';

        // Máquina de estados: una ruta Finalizada es TERMINAL, no admite edición ni cambio
        if ($esEdicion) {
            $rutaActual = Ruta::find((int)$_POST['id']);
            if ($rutaActual && $rutaActual->estado === Ruta::ESTADO_TERMINAL) {
                flash('global_msg', 'La ruta está Finalizada (estado definitivo) y no puede modificarse. Cada ejecución es un registro independiente.', 'danger');
                header('Location: ' . URL_ROOT . '/rutas/index');
                exit;
            }
            // Validación al FINALIZAR: requiere al menos 1 participante inscrito
            if ($estado === Ruta::ESTADO_TERMINAL && $rutaActual && $rutaActual->estado !== Ruta::ESTADO_TERMINAL) {
                if (Ruta::countParticipantes((int)$_POST['id']) === 0) {
                    flash('global_msg', 'No se puede finalizar una ruta sin participantes inscritos. Registre los participantes de la visita antes de finalizarla.', 'danger');
                    header('Location: ' . URL_ROOT . '/rutas/index');
                    exit;
                }
            }
        } elseif ($estado === Ruta::ESTADO_TERMINAL) {
            // No se puede crear una ruta directamente como Finalizada (no tendría participantes)
            $estado = 'Activa';
        }

        // Validaciones generales de la ruta
        $nombre = trim($_POST['nombre'] ?? '');
        if (mb_strlen($nombre) < 3) {
            flash('global_msg', 'El nombre de la ruta debe tener al menos 3 caracteres.', 'danger');
            header('Location: ' . URL_ROOT . '/rutas/index');
            exit;
        }

        $fechaVisita = $_POST['fecha_visita'] ?: null;
        // Solo se valida fecha futura al crear o si la ruta aún no está Finalizada
        if (!empty($fechaVisita) && $fechaVisita < date('Y-m-d') && $estado !== Ruta::ESTADO_TERMINAL) {
            flash('global_msg', 'La fecha de visita no puede ser anterior a hoy.', 'danger');
            header('Location: ' . URL_ROOT . '/rutas/index');
            exit;
        }

        $duracion = trim($_POST['duracion_estimada'] ?? '');
        if (!empty($duracion) && !preg_match('/^\d{1,2}:\d{2}$/', $duracion)) {
            flash('global_msg', 'La duración debe estar en formato H:MM (ej: 2:30 para 2 horas y media).', 'danger');
            header('Location: ' . URL_ROOT . '/rutas/index');
            exit;
        }

        // Validar formato de hora_visita en servidor (HH:MM o HH:MM:SS)
        $horaVisita = trim($_POST['hora_visita'] ?? '');
        if (!empty($horaVisita) && !preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $horaVisita)) {
            flash('global_msg', 'La hora de visita no tiene un formato válido (HH:MM).', 'danger');
            header('Location: ' . URL_ROOT . '/rutas/index');
            exit;
        }

        // RT-02: motivo obligatorio al pasar a En Mantenimiento
        $motivoMant = trim($_POST['motivo_mantenimiento'] ?? '');
        if ($estado === 'En Mantenimiento' && empty($motivoMant)) {
            flash('global_msg', 'Debe indicar el motivo por el que la ruta pasa a mantenimiento.', 'danger');
            header('Location: ' . URL_ROOT . '/rutas/index');
            exit;
        }

        $data = [
            'id'                    => $esEdicion ? (int)$_POST['id'] : null,
            'nombre'                => trim($_POST['nombre']),
            'descripcion'           => trim($_POST['descripcion'] ?? ''),
            'duracion_estimada'     => trim($_POST['duracion_estimada'] ?? ''),
            'estado'                => $estado,
            'fecha_visita'          => $_POST['fecha_visita'] ?: null,
            'hora_visita'           => $_POST['hora_visita'] ?: null,
            'id_departamento'       => (int)$_POST['id_departamento'] ?: null,
            'id_facilitador'        => (int)$_POST['id_facilitador'] ?: null,
            'cupo_maximo'           => min(200, max(1, (int)($_POST['cupo_maximo'] ?? 20))),
            'requiere_formacion'    => !empty($_POST['requiere_formacion']),
            'tipo_ruta'             => $tipoRuta,
            'motivo_mantenimiento'  => $estado === 'En Mantenimiento' ? $motivoMant : null,
        ];

        $ruta = new Ruta($data);
        try {
            if ($ruta->save($userId)) {
                $msg = $esEdicion ? 'Ruta actualizada correctamente.' : 'Nueva ruta creada exitosamente.';
                flash('global_msg', $msg);
            } else {
                throw new Exception('Error al guardar la ruta.');
            }
        } catch (Exception $e) {
            flash('global_msg', 'Error: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/rutas/index');
    }

    public function detalle($id) {
        $ruta = Ruta::find($id);
        if (!$ruta) {
            header('Location: ' . URL_ROOT . '/rutas/index');
            exit;
        }
        $puntos               = Ruta::getPuntos($id);
        $participantes        = Ruta::getParticipantes($id);

        require_once '../app/models/Parroquia.php';
        $parroquias = Parroquia::all();

        // Historial de oficios emitidos para esta ruta
        $db = new Database();
        $db->query("SELECT numero, fecha, destinatario_nombre, destinatario_cargo, asunto, created_at
                    FROM oficios_emitidos
                    WHERE id_ruta = :id AND is_active = TRUE
                    ORDER BY created_at DESC");
        $db->bind(':id', $id);
        $oficiosEmitidos = $db->resultSet();

        $data = [
            'titulo'               => 'Ruta: ' . $ruta->nombre,
            'ruta'                 => $ruta,
            'puntos'               => $puntos,
            'participantes'        => $participantes,
            'parroquias'           => $parroquias,
            'oficiosEmitidos'      => $oficiosEmitidos,
        ];
        $this->view('rutas/detalle', $data);
    }

    public function buscarPersona() {
        header('Content-Type: application/json');
        $cedula = trim($_GET['cedula'] ?? '');
        if (empty($cedula)) {
            echo json_encode(['found' => false]);
            exit;
        }
        $persona = Ruta::buscarPersonaPorCedula($cedula);
        if ($persona) {
            require_once '../app/models/Taller.php';
            echo json_encode([
                'found'          => true,
                'tiene_formacion'=> Taller::personaRecibioFormacion((int)$persona->id),
                'persona'        => [
                    'id'               => $persona->id,
                    'cedula'           => $persona->cedula,
                    'nombre'           => $persona->nombre,
                    'apellido'         => $persona->apellido    ?? '',
                    'telefono'         => $persona->telefono    ?? '',
                    'correo'           => $persona->correo      ?? '',
                    'genero'           => $persona->genero      ?? '',
                    'fecha_nacimiento' => $persona->fecha_nacimiento ?? '',
                    'parroquia_id'     => $persona->parroquia_id ?? '',
                    'direccion'        => $persona->direccion   ?? '',
                ],
            ]);
        } else {
            echo json_encode(['found' => false]);
        }
        exit;
    }

    // ── Participantes ────────────────────────────────────────────────────────

    public function inscribir() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $_POST  = $this->sanitizePost();
        $id_ruta = (int)$_POST['id_ruta'];
        $userId  = $this->getUserId();
        $esLibre = !empty($_POST['tipo_participante_libre']);

        $observaciones  = trim($_POST['observaciones'] ?? '') ?: null;

        require_once '../app/models/Taller.php';

        try {
            // ── Flujo libre: participante sin cédula (menor 5-11 años) ──────────
            if ($esLibre) {
                $nombre = trim($_POST['nombre_libre'] ?? '');
                if (empty($nombre)) throw new Exception('El nombre del participante es requerido.');

                $fechaNacLibreRaw = trim($_POST['fecha_nac_libre'] ?? '');
                if (empty($fechaNacLibreRaw)) {
                    throw new Exception('La fecha de nacimiento es obligatoria para participantes sin cédula.');
                }
                if (\DateTime::createFromFormat('Y-m-d', $fechaNacLibreRaw) === false) {
                    throw new Exception('El formato de fecha de nacimiento no es válido.');
                }
                $fnacDt    = new \DateTime($fechaNacLibreRaw);
                $hoyDt     = new \DateTime();
                if ($fnacDt >= $hoyDt) throw new Exception('La fecha de nacimiento no puede ser una fecha futura.');
                $edadAnios = (int)Util::edad($fechaNacLibreRaw);
                if ($edadAnios < 5)  throw new Exception('El participante debe tener al menos 5 años.');
                if ($edadAnios >= 12) throw new Exception('Los participantes de 12 años o más deben registrarse con su cédula.');

                // cedula_libre (ID escolar) es opcional, pero si se proporciona valida formato alfanumérico
                $cedulaLibre = trim($_POST['cedula_libre'] ?? '') ?: null;
                if ($cedulaLibre !== null && !preg_match('/^[A-Za-z0-9\-]{3,20}$/', $cedulaLibre)) {
                    throw new Exception('El N° ID escolar solo admite letras, números y guiones (3 a 20 caracteres).');
                }

                $apellidoLibre = trim($_POST['apellido_libre'] ?? '') ?: null;

                // Representante obligatorio: ancla la identidad del menor sin cédula.
                $nombreRep = trim($_POST['nombre_representante'] ?? '');
                $cedulaRep = preg_replace('/\D/', '', trim($_POST['cedula_representante'] ?? ''));
                if ($nombreRep === '' || $cedulaRep === '') {
                    throw new Exception('El representante (nombre y cédula) es obligatorio para participantes sin cédula.');
                }
                if (strlen($cedulaRep) < 6 || strlen($cedulaRep) > 8) {
                    throw new Exception('La cédula del representante debe tener entre 6 y 8 dígitos.');
                }

                // Anti-duplicado en la MISMA ruta (mismo niño/a sin cédula)
                if (Ruta::estaInscritoLibre($id_ruta, $nombre, $apellidoLibre, $fechaNacLibreRaw, $cedulaLibre)) {
                    throw new Exception('Ya hay un participante con ese nombre y fecha de nacimiento inscrito en esta ruta.');
                }

                Ruta::inscribirLibre($id_ruta, [
                    'nombre_libre'   => $nombre,
                    'apellido_libre' => $apellidoLibre,
                    'cedula_libre'   => $cedulaLibre,
                    'genero_libre'   => trim($_POST['genero_libre']   ?? '') ?: null,
                    'fecha_nac_libre'=> $fechaNacLibreRaw,
                    'id_institucion' => null,
                    'observaciones'  => $observaciones,
                    'nombre_representante' => $nombreRep,
                    'cedula_representante' => $cedulaRep,
                ], $userId);

            // ── Flujo con cédula: buscar o crear en personas ─────────────────
            } else {
                $cedula = trim($_POST['cedula_busqueda'] ?? '');
                if (empty($cedula)) throw new Exception('Ingrese la cédula del participante.');

                $cedulaN = strtoupper(preg_replace('/[\s.\-]/', '', $cedula));
                if (!preg_match('/^[VEJGCP]?[1-9]\d{5,8}$/', $cedulaN)) {
                    throw new Exception('La cédula no es válida. Use solo números (6 a 8 dígitos).');
                }
                // Guardar/buscar siempre con solo dígitos (formato normalizado)
                $cedula = preg_replace('/\D/', '', $cedula);

                $nombre   = trim($_POST['nombre']   ?? '');
                $apellido = trim($_POST['apellido']  ?? '');
                if (empty($nombre) || empty($apellido)) {
                    throw new Exception('El nombre y apellido del participante son requeridos.');
                }

                // Correo: validar formato si está presente
                $correoRaw = trim($_POST['correo'] ?? '') ?: null;
                if ($correoRaw !== null && !$this->emailValido($correoRaw)) {
                    throw new Exception('El correo electrónico no es válido (sin espacios ni símbolos especiales; ejemplo: nombre@dominio.com).');
                }
                $telRaw = trim($_POST['telefono'] ?? '') ?: null;
                if ($telRaw !== null && !$this->telefonoValido($telRaw)) {
                    throw new Exception('El teléfono no es válido. Debe ser un número venezolano (prefijo + 7 dígitos).');
                }

                $fechaNac   = trim($_POST['fecha_nacimiento'] ?? '') ?: null;
                if ($fechaNac && (\DateTime::createFromFormat('Y-m-d', $fechaNac) === false || $fechaNac > date('Y-m-d'))) $fechaNac = null;
                $parroquiaId = (int)($_POST['parroquia_id'] ?? 0) ?: null;

                $persona = Ruta::buscarPersonaPorCedula($cedula);

                if ($persona) {
                    // Persona encontrada: completar campos vacíos
                    $actualizacion = [];
                    if (empty($persona->telefono)         && !empty($_POST['telefono']))  $actualizacion['telefono']         = trim($_POST['telefono']);
                    if (empty($persona->correo)           && $correoRaw)                  $actualizacion['correo']            = $correoRaw;
                    if (empty($persona->genero)           && !empty($_POST['genero']))     $actualizacion['genero']            = trim($_POST['genero']);
                    if (empty($persona->fecha_nacimiento) && $fechaNac)                   $actualizacion['fecha_nacimiento']  = $fechaNac;
                    if (empty($persona->parroquia_id)     && $parroquiaId)                $actualizacion['parroquia_id']      = $parroquiaId;
                    if (empty($persona->direccion)        && !empty($_POST['direccion']))  $actualizacion['direccion']         = trim($_POST['direccion']);
                    if (!empty($actualizacion)) Taller::actualizarPersona((int)$persona->id, $actualizacion, $userId);
                    $idPersona = (int)$persona->id;
                } else {
                    // Persona no encontrada: crear nueva en personas
                    $idPersona = Taller::crearPersona([
                        'cedula'           => $cedula,
                        'nombre'           => $nombre,
                        'apellido'         => $apellido,
                        'telefono'         => trim($_POST['telefono'] ?? '') ?: null,
                        'correo'           => $correoRaw,
                        'genero'           => trim($_POST['genero']   ?? '') ?: null,
                        'fecha_nacimiento' => $fechaNac,
                        'parroquia_id'     => $parroquiaId,
                        'direccion'        => trim($_POST['direccion'] ?? '') ?: null,
                    ], $userId);
                }

                // RN-F12: verificar prerequisito de formación si la ruta lo requiere
                $ruta   = Ruta::find($id_ruta);
                $forzar = !empty($_POST['forzar_inscripcion']);
                if ($ruta && !empty($ruta->requiere_formacion)) {
                    if (!Taller::personaRecibioFormacion($idPersona) && !$forzar) {
                        throw new Exception(
                            "{$nombre} {$apellido} no tiene actividades de formación completadas. " .
                            'Marque "Inscribir sin formación" si es un caso excepcional.'
                        );
                    }
                }

                Ruta::inscribir($id_ruta, $idPersona, $userId, null, $observaciones);
            }

            // Advertencia no bloqueante de cupo (mismo criterio que talleres):
            // cupo_maximo es estimación de planificación, no límite rígido.
            $rutaCupo  = Ruta::find($id_ruta);
            $cupoMax   = (int)($rutaCupo->cupo_maximo ?? 0);
            $inscritos = Ruta::countParticipantes($id_ruta);
            if ($cupoMax > 0 && $inscritos >= $cupoMax) {
                flash('global_msg', 'Participante registrado. Aviso: el cupo estimado de ' . $cupoMax . ' personas ha sido alcanzado o superado.', 'warning');
            } else {
                flash('global_msg', 'Participante registrado correctamente.');
            }
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/rutas/detalle/' . $id_ruta);
    }

    public function desinscribir($id_participante) {
        $id_ruta = 0;
        try {
            $db = new Database();
            $db->query("SELECT id_ruta FROM participantes_ruta WHERE id = :id");
            $db->bind(':id', $id_participante);
            $row     = $db->single();
            $id_ruta = $row ? $row->id_ruta : 0;

            Ruta::desinscribir((int)$id_participante, $this->getUserId());
            flash('global_msg', 'Participante removido.', 'warning');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/rutas/detalle/' . $id_ruta);
    }

    // ── Asistencia ───────────────────────────────────────────────────────────

    public function marcarAsistencia() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['ok'=>false]); exit; }
        $id      = (int)($_POST['id']     ?? 0);
        $asistio = !empty($_POST['asistio']) && $_POST['asistio'] !== '0';
        $userId  = $this->getUserId();
        try {
            Ruta::marcarAsistencia($id, $asistio, $userId);
            echo json_encode(['ok' => true, 'asistio' => $asistio]);
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
        }
        exit;
    }

    public function marcarAsistenciaMasiva() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['ok'=>false]); exit; }
        $idRuta = (int)($_POST['id_ruta'] ?? 0);
        $userId = $this->getUserId();
        try {
            Ruta::marcarAsistenciaMasiva($idRuta, $userId);
            echo json_encode(['ok' => true]);
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
        }
        exit;
    }

    // ── Informe post-visita ───────────────────────────────────────────────────

    public function informe($id) {
        $ruta = Ruta::find($id);
        if (!$ruta) { header('Location: ' . URL_ROOT . '/rutas/index'); exit; }

        // Sugerencia demográfica desde participantes activos
        $sugeridos = ['mujeres'=>0,'hombres'=>0,'ninas'=>0,'ninos'=>0];
        $totalSug  = 0;
        $db = new Database();
        $db->query("SELECT
                        CASE WHEN pr.id_persona IS NOT NULL AND p.genero = 'F' THEN 'mujeres'
                             WHEN pr.id_persona IS NOT NULL AND p.genero = 'M' THEN 'hombres'
                             WHEN pr.id_persona IS NULL AND pr.genero_libre = 'F' THEN 'ninas'
                             WHEN pr.id_persona IS NULL AND pr.genero_libre = 'M' THEN 'ninos'
                             ELSE 'hombres' END AS categoria,
                        COUNT(*) AS total
                    FROM participantes_ruta pr
                    LEFT JOIN personas p ON pr.id_persona = p.id
                    WHERE pr.id_ruta = :id AND pr.is_active = TRUE
                    GROUP BY categoria");
        $db->bind(':id', $id);
        foreach ($db->resultSet() as $row) {
            if (isset($sugeridos[$row->categoria])) {
                $sugeridos[$row->categoria] = (int)$row->total;
                $totalSug += (int)$row->total;
            }
        }

        $informe = Ruta::getInforme((int)$id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mujeres = max(0, (int)$_POST['mujeres']);
            $hombres = max(0, (int)$_POST['hombres']);
            $ninas   = max(0, (int)$_POST['ninas']);
            $ninos   = max(0, (int)$_POST['ninos']);
            try {
                if (($mujeres + $hombres + $ninas + $ninos) === 0) {
                    throw new Exception('Debe registrar al menos un participante en el informe.');
                }
                if (empty(trim($_POST['resumen_visita'] ?? ''))) {
                    throw new Exception('El resumen de la visita es obligatorio.');
                }
                Ruta::saveInforme([
                    'id_ruta'       => $id,
                    'lugar_exacto'  => trim($_POST['lugar_exacto']  ?? ''),
                    'mujeres'       => $mujeres,
                    'hombres'       => $hombres,
                    'ninas'         => $ninas,
                    'ninos'         => $ninos,
                    'observaciones' => trim($_POST['observaciones']  ?? '') ?: null,
                    'resumen_visita'=> trim($_POST['resumen_visita'] ?? ''),
                ]);
                flash('global_msg', 'Informe guardado correctamente.');
            } catch (Exception $e) {
                flash('global_msg', $e->getMessage(), 'danger');
            }
            header('Location: ' . URL_ROOT . '/rutas/informe/' . $id);
            exit;
        }

        $this->view('rutas/informe', [
            'titulo'        => 'Informe de Visita',
            'ruta'          => $ruta,
            'informe'       => $informe,
            'sugeridos'     => $sugeridos,
            'totalSugeridos'=> $totalSug,
        ]);
    }

    public function exportarInformeCsv($id) {
        $ruta = Ruta::find($id);
        if (!$ruta) { header('Location: ' . URL_ROOT . '/rutas/index'); exit; }
        $informe       = Ruta::getInforme((int)$id);
        $participantes = Ruta::getParticipantes((int)$id);

        $nombre = 'Informe_Ruta_' . preg_replace('/[^A-Za-z0-9_]/', '_', $ruta->nombre ?? 'ruta');
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombre . '_' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($out, ['REPÚBLICA BOLIVARIANA DE VENEZUELA'], ';');
        fputcsv($out, ['ALCALDÍA BOLIVARIANA DEL MUNICIPIO SUCRE'], ';');
        fputcsv($out, ['Instituto Municipal Autónomo de Turismo (IMATUR-SUCRE) — RIF. G-20008498-7'], ';');
        fputcsv($out, ['Generado por: ' . ($_SESSION['user_username'] ?? 'Sistema') . '  Fecha: ' . date('d/m/Y H:i')], ';');
        fputcsv($out, [''], ';');
        fputcsv($out, ['INFORME DE VISITA TURÍSTICA'], ';');
        fputcsv($out, ['Ruta',     $ruta->nombre], ';');
        fputcsv($out, ['Tipo',     $ruta->tipo_ruta ?? ''], ';');
        fputcsv($out, ['Fecha',    $ruta->fecha_visita ?? ''], ';');
        fputcsv($out, ['Estado',   $ruta->estado], ';');
        fputcsv($out, [''], ';');

        if ($informe) {
            fputcsv($out, ['RESUMEN DEMOGRÁFICO'], ';');
            fputcsv($out, ['Lugar',    $informe->lugar_exacto ?? ''], ';');
            fputcsv($out, ['Mujeres',  $informe->mujeres  ?? 0], ';');
            fputcsv($out, ['Hombres',  $informe->hombres  ?? 0], ';');
            fputcsv($out, ['Niñas (5-11)', $informe->ninas ?? 0], ';');
            fputcsv($out, ['Niños (5-11)', $informe->ninos ?? 0], ';');
            fputcsv($out, ['Total',    $informe->total_atendidos ?? 0], ';');
            fputcsv($out, ['Resumen',  $informe->resumen_visita ?? ''], ';');
            fputcsv($out, [''], ';');
        }

        fputcsv($out, ['LISTADO DE PARTICIPANTES (' . count($participantes) . ')'], ';');
        fputcsv($out, ['Tipo','Cédula/ID','Nombre','Apellido','Género','Asistió','Observaciones'], ';');
        foreach ($participantes as $p) {
            $esLibre = empty($p->id_persona);
            $genero  = $esLibre ? ($p->genero_libre ?? '') : ($p->genero ?? '');
            $genMap  = ['M'=>'Masculino','F'=>'Femenino','O'=>'Otro'];
            fputcsv($out, [
                $esLibre ? 'Niño/a' : 'Adulto',
                $esLibre ? ($p->cedula_libre ?? '—') : ($p->cedula ?? '—'),
                $esLibre ? ($p->nombre_libre ?? '') : ($p->nombre ?? ''),
                $esLibre ? ($p->apellido_libre ?? '') : ($p->apellido ?? ''),
                $genMap[$genero] ?? $genero,
                $p->asistio ? 'Sí' : 'No',
                $p->observaciones ?? '',
            ], ';');
        }
        fclose($out); exit;
    }

    // ── Oficio ───────────────────────────────────────────────────────────────

    public function oficio($id) {
        $ruta = Ruta::find($id);
        if (!$ruta) {
            header('Location: ' . URL_ROOT . '/rutas/index');
            exit;
        }
        $puntos = Ruta::getPuntos($id);
        $config = ConfigSistema::getAll();
        $total  = Ruta::countParticipantes($id);

        $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = $this->sanitizePost();

            $destNombre = trim($_POST['destinatario_nombre'] ?? '');
            $destCargo  = trim($_POST['destinatario_cargo']  ?? '');
            $espacio    = trim($_POST['espacio'] ?? $ruta->nombre);
            $numEst     = (int)($_POST['num_estudiantes'] ?? $total);
            $numAdu     = (int)($_POST['num_adultos'] ?? 0);

            if (empty($destNombre)) {
                flash('global_msg', 'El nombre del destinatario es requerido.', 'danger');
                header('Location: ' . URL_ROOT . '/rutas/oficio/' . $id);
                exit;
            }

            $numero = Ruta::crearOficioEmitido($id, [
                'destinatario_nombre' => $destNombre,
                'destinatario_cargo'  => $destCargo,
                'asunto'              => 'Visita: ' . $ruta->nombre,
            ], $this->getUserId());

            $fechaRuta = null;
            if ($ruta->fecha_visita) {
                $ts  = strtotime($ruta->fecha_visita);
                $dia = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'][date('w', $ts)];
                $fechaRuta = $dia . ' ' . date('j', $ts) . ' de ' . $meses[(int)date('n', $ts) - 1];
            }

            $data = [
                'ruta'               => $ruta,
                'config'             => $config,
                'numero'             => $numero,
                'destinatario_nombre'=> $destNombre,
                'destinatario_cargo' => $destCargo,
                'espacio'            => $espacio,
                'num_estudiantes'    => $numEst,
                'num_adultos'        => $numAdu,
                'fecha_hoy'          => date('j') . ' de ' . $meses[(int)date('n') - 1] . ' de ' . date('Y'),
                'fecha_ruta_esp'     => $fechaRuta,
            ];
            $this->view('rutas/oficio_imprimible', $data);
            return;
        }

        // GET — formulario
        $fechaRuta = null;
        if ($ruta->fecha_visita) {
            $ts  = strtotime($ruta->fecha_visita);
            $dia = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'][date('w', $ts)];
            $fechaRuta = $dia . ' ' . date('j', $ts) . ' de ' . $meses[(int)date('n', $ts) - 1];
        }

        // Oficios ya emitidos para esta ruta (para mostrar aviso)
        $dbO = new Database();
        $dbO->query("SELECT numero, fecha, destinatario_nombre FROM oficios_emitidos
                     WHERE id_ruta = :id AND is_active = TRUE ORDER BY created_at DESC LIMIT 5");
        $dbO->bind(':id', $id);
        $oficiosPrevios = $dbO->resultSet();

        $data = [
            'titulo'              => 'Generar Oficio: ' . $ruta->nombre,
            'ruta'                => $ruta,
            'puntos'              => $puntos,
            'config'              => $config,
            'fecha_hoy'           => date('j') . ' de ' . $meses[(int)date('n') - 1] . ' de ' . date('Y'),
            'fecha_ruta_esp'      => $fechaRuta,
            'total_participantes' => $total,
            'oficiosPrevios'      => $oficiosPrevios,
        ];
        $this->view('rutas/oficio', $data);
    }

    // ── CRUD básico ──────────────────────────────────────────────────────────

    public function storePunto() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $_POST = $this->sanitizePost();

        $pNombre  = trim($_POST['punto_nombre'] ?? '');
        $pOrden   = (int)$_POST['orden'];
        $pIdRuta  = (int)$_POST['id_ruta'];
        $pId      = isset($_POST['punto_id']) ? (int)$_POST['punto_id'] : null;
        $pLat     = trim($_POST['latitud']  ?? '') ?: null;
        $pLng     = trim($_POST['longitud'] ?? '') ?: null;

        if (empty($pNombre)) {
            flash('global_msg', 'El nombre de la parada es requerido.', 'danger');
            header('Location: ' . URL_ROOT . '/rutas/detalle/' . $pIdRuta);
            exit;
        }
        if ($pOrden < 1) {
            flash('global_msg', 'El orden de la parada debe ser un número positivo.', 'danger');
            header('Location: ' . URL_ROOT . '/rutas/detalle/' . $pIdRuta);
            exit;
        }
        // Validar rango de coordenadas
        if ($pLat !== null && ((float)$pLat < -90 || (float)$pLat > 90)) {
            flash('global_msg', 'La latitud debe estar entre -90 y 90.', 'danger');
            header('Location: ' . URL_ROOT . '/rutas/detalle/' . $pIdRuta);
            exit;
        }
        if ($pLng !== null && ((float)$pLng < -180 || (float)$pLng > 180)) {
            flash('global_msg', 'La longitud debe estar entre -180 y 180.', 'danger');
            header('Location: ' . URL_ROOT . '/rutas/detalle/' . $pIdRuta);
            exit;
        }
        // RT-07: verificar unicidad de orden dentro de la ruta (excluyendo el registro actual)
        $dbCheck = new Database();
        $dbCheck->query("SELECT 1 FROM puntos_ruta
                         WHERE id_ruta = :r AND orden = :o AND is_active = TRUE
                           AND (:eid = 0 OR id <> :eid)");
        $dbCheck->bind(':r',   $pIdRuta);
        $dbCheck->bind(':o',   $pOrden);
        $dbCheck->bind(':eid', $pId ?? 0);
        if ($dbCheck->single()) {
            flash('global_msg', "Ya existe una parada con el orden {$pOrden} en esta ruta. Elija un número diferente.", 'danger');
            header('Location: ' . URL_ROOT . '/rutas/detalle/' . $pIdRuta);
            exit;
        }

        $data = [
            'id'          => $pId,
            'id_ruta'     => $pIdRuta,
            'nombre'      => $pNombre,
            'descripcion' => trim($_POST['punto_descripcion'] ?? ''),
            'orden'       => $pOrden,
            'latitud'     => $pLat,
            'longitud'    => $pLng,
        ];
        $punto = new PuntoRuta($data);
        try {
            if ($punto->save($this->getUserId())) flash('global_msg', 'Punto guardado.');
            else throw new Exception('No se pudo registrar el punto.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/rutas/detalle/' . $data['id_ruta']);
    }

    public function deletePunto($id, $id_ruta) {
        try {
            if (PuntoRuta::delete($id, $this->getUserId())) flash('global_msg', 'Punto desactivado.', 'warning');
            else throw new Exception('Error al eliminar el punto.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/rutas/detalle/' . $id_ruta);
    }

    public function delete($id) {
        try {
            if (Ruta::delete($id, $this->getUserId())) flash('global_msg', 'Ruta movida a papelera.', 'warning');
            else throw new Exception('No se puede eliminar la ruta.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/rutas/index');
    }
}
