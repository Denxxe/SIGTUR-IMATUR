<?php
/**
 * RutasController — Turismo.
 *
 * Desde la mig. 078 (fase T-A) el módulo tiene DOS pantallas, porque tiene dos
 * entidades:
 *
 *   /rutas/index    → EL CATÁLOGO. Los recorridos que se ofrecen, con sus
 *                     puntos. No tienen fecha.
 *   /rutas/salidas  → LAS SALIDAS. Cada vez que se ejecuta una ruta, con su
 *                     fecha, su grupo, sus guías y su estado.
 *
 * Todo lo que antes colgaba de una ruta —participantes, asistencia, informe,
 * oficio— cuelga ahora de la SALIDA: son distintos cada vez que se sale.
 */
class RutasController extends Controller {

    /** Catálogo de rutas. */
    public function index() {
        $porPagina = 12;
        $pagina    = max(1, (int)($_GET['p'] ?? 1));
        $filtros   = [
            'buscar' => trim($_GET['buscar'] ?? ''),
            'estado' => trim($_GET['estado'] ?? ''),
            'tipo'   => trim($_GET['tipo']   ?? ''),
        ];
        $res          = Ruta::paginate($pagina, $porPagina, $filtros);
        $totalPaginas = max(1, (int)ceil($res['total'] / $porPagina));
        if ($pagina > $totalPaginas) $pagina = $totalPaginas;

        $this->view('rutas/index', [
            'titulo'        => 'Catálogo de Rutas Turísticas',
            'rutas'         => $res['items'],
            'departamentos' => Departamento::all(),
            'pagina'        => $pagina,
            'total_paginas' => $totalPaginas,
            'total'         => $res['total'],
            'por_pagina'    => $porPagina,
            'filtros'       => $filtros,
        ]);
    }

    // =====================================================================
    //  SALIDAS (ruta_ejecuciones) — fase T-A
    // =====================================================================

    /** Listado de salidas, con sus filtros de fecha, estado y período. */
    public function salidas() {
        $porPagina = 15;
        $pagina    = max(1, (int)($_GET['p'] ?? 1));
        $filtros   = [
            'buscar'      => trim($_GET['buscar']      ?? ''),
            'estado'      => trim($_GET['estado']      ?? ''),
            'origen'      => trim($_GET['origen']      ?? ''),
            'ruta'        => trim($_GET['ruta']        ?? ''),
            'periodo'     => trim($_GET['periodo']     ?? ''),
            'fecha_desde' => trim($_GET['fecha_desde'] ?? ''),
            'fecha_hasta' => trim($_GET['fecha_hasta'] ?? ''),
        ];
        $res          = RutaEjecucion::paginate($pagina, $porPagina, $filtros);
        $totalPaginas = max(1, (int)ceil($res['total'] / $porPagina));
        if ($pagina > $totalPaginas) $pagina = $totalPaginas;

        $this->view('rutas/salidas', [
            'titulo'        => 'Salidas programadas',
            'salidas'       => $res['items'],
            'catalogo'      => Ruta::activas(),
            'resumen'       => RutaEjecucion::resumenPorEstado(),
            'pagina'        => $pagina,
            'total_paginas' => $totalPaginas,
            'total'         => $res['total'],
            'por_pagina'    => $porPagina,
            'filtros'       => $filtros,
        ]);
    }

    /** Programa una salida nueva o edita una que sigue programada. */
    public function storeSalida() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/rutas/salidas'); return; }
        $_POST = $this->sanitizePost();
        $id    = (int)($_POST['id'] ?? 0);

        try {
            $hora = trim($_POST['hora'] ?? '');
            if ($hora !== '' && !preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $hora)) {
                throw new Exception('La hora no tiene un formato válido (HH:MM).');
            }
            $d = [
                'id_ruta'            => (int)($_POST['id_ruta'] ?? 0),
                'fecha'              => trim($_POST['fecha'] ?? ''),
                'hora'               => $hora,
                'cupo_maximo'        => trim($_POST['cupo_maximo'] ?? ''),
                'origen'             => $_POST['origen'] ?? 'Particular',
                'institucion_nombre' => trim($_POST['institucion_nombre'] ?? ''),
                'observaciones'      => trim($_POST['observaciones'] ?? ''),
            ];

            if ($id > 0) {
                RutaEjecucion::actualizar($id, $d, $this->getUserId());
                flash('global_msg', 'Salida actualizada.');
            } else {
                // Una salida nueva no puede programarse en el pasado.
                if ($d['fecha'] !== '' && $d['fecha'] < date('Y-m-d')) {
                    throw new Exception('La fecha de la salida no puede ser anterior a hoy.');
                }
                $id = RutaEjecucion::crear($d, $this->getUserId());
                flash('global_msg', 'Salida programada. Ahora puedes asignarle los guías y registrar el grupo.');
                header('Location: ' . URL_ROOT . '/rutas/detalle/' . $id);
                return;
            }
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/rutas/salidas');
    }

    /** R-14: marcar la salida como Ejecutada o No ejecutada (con motivo). */
    public function cambiarEstadoSalida() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/rutas/salidas'); return; }
        $_POST = $this->sanitizePost();
        $id    = (int)($_POST['id'] ?? 0);
        try {
            RutaEjecucion::cambiarEstado($id, $_POST['estado'] ?? '', $_POST['motivo'] ?? null, $this->getUserId());
            flash('global_msg', 'Salida marcada como «' . $_POST['estado'] . '».');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/rutas/detalle/' . $id);
    }

    /** R-16: reprogramar una salida que no se pudo ejecutar. */
    public function reprogramar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/rutas/salidas'); return; }
        $_POST = $this->sanitizePost();
        $id    = (int)($_POST['id'] ?? 0);
        try {
            $nueva = RutaEjecucion::reprogramar($id, trim($_POST['fecha'] ?? ''), trim($_POST['hora'] ?? '') ?: null, $this->getUserId());
            flash('global_msg', 'Salida reprogramada. La original queda en el histórico como no ejecutada.');
            header('Location: ' . URL_ROOT . '/rutas/detalle/' . $nueva);
            return;
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/rutas/detalle/' . $id);
    }

    /** R-13: la Presidencia aprueba la salida. */
    public function aprobarSalida() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/rutas/salidas'); return; }
        $_POST = $this->sanitizePost();
        $id    = (int)($_POST['id'] ?? 0);
        try {
            RutaEjecucion::aprobar($id, $this->getUserId());
            flash('global_msg', 'Salida aprobada.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/rutas/detalle/' . $id);
    }

    /** R-31/R-33: guías y acompañantes de la salida. */
    public function agregarEmpleadoSalida() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/rutas/salidas'); return; }
        $_POST = $this->sanitizePost();
        $id    = (int)($_POST['id_ejecucion'] ?? 0);
        try {
            RutaEjecucion::agregarEmpleado($id, (int)($_POST['id_empleado'] ?? 0),
                !empty($_POST['es_encargado']), $this->getUserId());
            flash('global_msg', 'Empleado asignado a la salida.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/rutas/detalle/' . $id);
    }

    public function quitarEmpleadoSalida() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/rutas/salidas'); return; }
        $_POST = $this->sanitizePost();
        $id    = (int)($_POST['id_ejecucion'] ?? 0);
        try {
            RutaEjecucion::quitarEmpleado((int)($_POST['id'] ?? 0), $this->getUserId());
            flash('global_msg', 'Empleado retirado de la salida.', 'warning');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/rutas/detalle/' . $id);
    }

    /** R-45: incidencias de la salida. */
    public function guardarIncidencias() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/rutas/salidas'); return; }
        $_POST = $this->sanitizePost();
        $id    = (int)($_POST['id'] ?? 0);
        try {
            RutaEjecucion::guardarIncidencias($id, trim($_POST['incidencias'] ?? ''), $this->getUserId());
            flash('global_msg', 'Incidencias guardadas.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/rutas/detalle/' . $id);
    }

    public function eliminarSalida() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/rutas/salidas'); return; }
        $_POST = $this->sanitizePost();
        try {
            RutaEjecucion::delete((int)($_POST['id'] ?? 0), $this->getUserId());
            flash('global_msg', 'Salida eliminada.', 'warning');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/rutas/salidas');
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $_POST = $this->sanitizePost();
        $userId    = $this->getUserId();
        $esEdicion = !empty($_POST['id']);

        $estado   = in_array($_POST['estado'] ?? '', Ruta::ESTADOS) ? $_POST['estado'] : Ruta::ESTADOS[0];
        $tipoRuta = in_array($_POST['tipo_ruta'] ?? '', Ruta::$TIPOS_RUTA) ? $_POST['tipo_ruta'] : 'General';

        // La máquina de estados «Finalizada» desapareció en la mig. 078: describía
        // una salida, no un recorrido del catálogo. Una ruta se ofrece (Activa),
        // se deja de ofrecer (Inactiva) o está temporalmente cerrada
        // (En Mantenimiento) — y eso se puede cambiar siempre.

        $nombre = trim($_POST['nombre'] ?? '');
        if (mb_strlen($nombre) < 3) {
            flash('global_msg', 'El nombre de la ruta debe tener al menos 3 caracteres.', 'danger');
            header('Location: ' . URL_ROOT . '/rutas/index');
            exit;
        }

        $duracion = trim($_POST['duracion_estimada'] ?? '');
        if (!empty($duracion) && !preg_match('/^\d{1,2}:\d{2}$/', $duracion)) {
            flash('global_msg', 'La duración debe estar en formato H:MM (ej: 1:30 — el cliente indica que lo normal es hora y media y el máximo 3 horas).', 'danger');
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

        // T-B: restricciones de edad del recorrido (mig. 079). Vacío = sin tope.
        // Es la regla que el cliente dio en R-14/R-30: Exploradores 4–16,
        // Río Brito desde 12. Antes el rango 5–11 estaba cableado en el código.
        $edadMin = ($_POST['edad_min'] ?? '') === '' ? null : (int)$_POST['edad_min'];
        $edadMax = ($_POST['edad_max'] ?? '') === '' ? null : (int)$_POST['edad_max'];
        foreach ([$edadMin, $edadMax] as $e) {
            if ($e !== null && ($e < 0 || $e > 120)) {
                flash('global_msg', 'Las edades deben estar entre 0 y 120 años.', 'danger');
                header('Location: ' . URL_ROOT . '/rutas/index');
                exit;
            }
        }
        if ($edadMin !== null && $edadMax !== null && $edadMin > $edadMax) {
            flash('global_msg', 'La edad mínima no puede ser mayor que la máxima.', 'danger');
            header('Location: ' . URL_ROOT . '/rutas/index');
            exit;
        }

        $data = [
            'id'                    => $esEdicion ? (int)$_POST['id'] : null,
            'nombre'                => $nombre,
            'descripcion'           => trim($_POST['descripcion'] ?? ''),
            'duracion_estimada'     => $duracion,
            'estado'                => $estado,
            'id_departamento'       => (int)($_POST['id_departamento'] ?? 0) ?: null,
            'requiere_formacion'    => !empty($_POST['requiere_formacion']),
            'edad_min'              => $edadMin,
            'edad_max'              => $edadMax,
            'restricciones'         => trim($_POST['restricciones'] ?? '') ?: null,
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

    /**
     * Detalle de una SALIDA. `$id` es de `ruta_ejecuciones`, no de `rutas`
     * (cambió en la mig. 078): el grupo, la asistencia y el informe son de la
     * salida, y los puntos se muestran desde el catálogo de su ruta.
     */
    public function detalle($id) {
        $id  = (int)$id;
        $ej  = RutaEjecucion::find($id);
        if (!$ej) {
            flash('global_msg', 'La salida solicitada no existe.', 'danger');
            header('Location: ' . URL_ROOT . '/rutas/salidas');
            exit;
        }
        $ruta = Ruta::find((int)$ej->id_ruta);

        require_once '../app/models/Parroquia.php';

        $db = new Database();
        $db->query("SELECT numero, fecha, destinatario_nombre, destinatario_cargo, asunto, created_at
                      FROM oficios_emitidos
                     WHERE id_ejecucion = :id AND is_active = TRUE
                     ORDER BY created_at DESC");
        $db->bind(':id', $id);
        $oficiosEmitidos = $db->resultSet();

        $participantes = RutaEjecucion::participantes($id);

        $this->view('rutas/detalle', [
            'titulo'          => 'Salida: ' . $ej->ruta_nombre . ' — ' . date('d/m/Y', strtotime($ej->fecha)),
            'ejecucion'       => $ej,
            'ruta'            => $ruta,
            'puntos'          => Ruta::getPuntos((int)$ej->id_ruta),
            'participantes'   => $participantes,
            'empleadosSalida' => RutaEjecucion::empleados($id),
            'empleados'       => Empleado::all(),
            'parroquias'      => Parroquia::all(),
            'oficiosEmitidos' => $oficiosEmitidos,
            // R-33: 7-8 personas por guía. Es una sugerencia, no un límite.
            'guias_sugeridos' => RutaEjecucion::guiasSugeridos(count($participantes)),
            // R-28: el cupo del cliente es por DÍA, no por salida.
            'personas_del_dia'=> RutaEjecucion::personasEnFecha($ej->fecha),
        ]);
    }

    /** Ficha del recorrido en el catálogo: sus puntos y sus salidas. */
    public function ruta($id) {
        $id   = (int)$id;
        $ruta = Ruta::find($id);
        if (!$ruta) {
            flash('global_msg', 'La ruta solicitada no existe.', 'danger');
            header('Location: ' . URL_ROOT . '/rutas/index');
            exit;
        }
        $this->view('rutas/ruta_detalle', [
            'titulo'  => 'Ruta: ' . $ruta->nombre,
            'ruta'    => $ruta,
            'puntos'  => Ruta::getPuntos($id),
            'salidas' => RutaEjecucion::porRuta($id),
        ]);
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
        $id_ruta = (int)($_POST['id_ejecucion'] ?? $_POST['id_ruta'] ?? 0); // id de la SALIDA (mig. 078)
        $userId  = $this->getUserId();
        $esLibre = !empty($_POST['tipo_participante_libre']);

        $observaciones  = trim($_POST['observaciones'] ?? '') ?: null;

        require_once '../app/models/Taller.php';

        try {
            // ── Flujo libre: participante sin cédula (menor) ────────────────────
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
                // H-17 / T-B: el rango 5–11 estaba CABLEADO aquí y se fijó en la
                // mig. 017 sin levantamiento. Ahora la restricción es del
                // RECORRIDO (mig. 079): Exploradores va de 4 a 16 (R-66) y Río
                // Brito de 12 en adelante (R-57). Sin rango definido, no restringe.
                $edadAnios = (int)Util::edad($fechaNacLibreRaw);
                $ejecLibre = RutaEjecucion::find($id_ruta);
                $rutaLibre = $ejecLibre ? Ruta::find((int)$ejecLibre->id_ruta) : null;
                if ($rutaLibre && ($errEdad = Ruta::motivoEdadNoValida($rutaLibre, $edadAnios)) !== null) {
                    throw new Exception($errEdad);
                }

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
                if (RutaEjecucion::estaInscritoLibre($id_ruta, $nombre, $apellidoLibre, $fechaNacLibreRaw, $cedulaLibre)) {
                    throw new Exception('Ya hay un participante con ese nombre y fecha de nacimiento inscrito en esta ruta.');
                }

                RutaEjecucion::inscribirLibre($id_ruta, [
                    'nombre_libre'   => $nombre,
                    'apellido_libre' => $apellidoLibre,
                    'cedula_libre'   => $cedulaLibre,
                    'genero_libre'   => trim($_POST['genero_libre']   ?? '') ?: null,
                    'fecha_nac_libre'=> $fechaNacLibreRaw,
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

                $ejecFor = RutaEjecucion::find($id_ruta);
                $ruta    = $ejecFor ? Ruta::find((int)$ejecFor->id_ruta) : null;

                // T-B: misma restricción de edad que en el flujo sin cédula, si
                // la persona tiene fecha de nacimiento registrada.
                if ($ruta && $fechaNac
                    && ($errEdad = Ruta::motivoEdadNoValida($ruta, (int)Util::edad($fechaNac))) !== null) {
                    throw new Exception($errEdad);
                }

                // RN-F12: verificar prerequisito de formación si la ruta lo requiere
                $forzar = !empty($_POST['forzar_inscripcion']);
                if ($ruta && !empty($ruta->requiere_formacion)) {
                    if (!Taller::personaRecibioFormacion($idPersona) && !$forzar) {
                        throw new Exception(
                            "{$nombre} {$apellido} no tiene actividades de formación completadas. " .
                            'Marque "Inscribir sin formación" si es un caso excepcional.'
                        );
                    }
                }

                RutaEjecucion::inscribir($id_ruta, $idPersona, $userId, $observaciones);
            }

            // Avisos de cupo — NO bloqueantes, mismo criterio que talleres: es
            // una estimación de planificación, no un límite rígido. Y el cupo
            // diario «es nuevo» según el propio cliente (R-28).
            $ejec      = RutaEjecucion::find($id_ruta);
            $cupoMax   = (int)($ejec->cupo_maximo ?? 0);
            $inscritos = RutaEjecucion::countParticipantes($id_ruta);
            $avisos    = [];

            if ($cupoMax > 0 && $inscritos >= $cupoMax) {
                $avisos[] = 'el cupo estimado de ' . $cupoMax . ' personas de esta salida ya se alcanzó';
            }
            // T-I (R-28): el tope de 60 es por DÍA, sumando todas las salidas.
            $restante = RutaEjecucion::cupoRestanteDelDia($ejec->fecha);
            if ($restante !== null && $restante <= 0) {
                $avisos[] = 'el cupo diario de ' . RutaEjecucion::cupoDiario()
                          . ' personas para el ' . date('d/m/Y', strtotime($ejec->fecha))
                          . ' ya se alcanzó (cuenta todas las salidas del día)';
            }

            if ($avisos) {
                flash('global_msg', 'Participante registrado. Aviso: ' . implode(' y ', $avisos) . '.', 'warning');
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
            $db->query("SELECT id_ejecucion FROM participantes_ruta WHERE id = :id");
            $db->bind(':id', $id_participante);
            $row     = $db->single();
            $id_ruta = $row ? (int)$row->id_ejecucion : 0;

            RutaEjecucion::desinscribir((int)$id_participante, $this->getUserId());
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
            RutaEjecucion::marcarAsistencia($id, $asistio, $userId);
            echo json_encode(['ok' => true, 'asistio' => $asistio]);
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
        }
        exit;
    }

    public function marcarAsistenciaMasiva() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['ok'=>false]); exit; }
        $idRuta = (int)($_POST['id_ejecucion'] ?? $_POST['id_ruta'] ?? 0);
        $userId = $this->getUserId();
        try {
            RutaEjecucion::marcarAsistenciaMasiva($idRuta, $userId);
            echo json_encode(['ok' => true]);
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
        }
        exit;
    }

    // ── Informe post-visita ───────────────────────────────────────────────────

    public function informe($id) {
        $ejec = RutaEjecucion::find((int)$id);   // $id es de la SALIDA (mig. 078)
        $ruta = $ejec ? Ruta::find((int)$ejec->id_ruta) : null;
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

        $informe = RutaEjecucion::getInforme((int)$id);

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
                RutaEjecucion::saveInforme([
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
            'ejecucion'     => $ejec,
            'informe'       => $informe,
            'sugeridos'     => $sugeridos,
            'totalSugeridos'=> $totalSug,
        ]);
    }

    public function exportarInformeCsv($id) {
        $ejec = RutaEjecucion::find((int)$id);   // $id es de la SALIDA (mig. 078)
        $ruta = $ejec ? Ruta::find((int)$ejec->id_ruta) : null;
        if (!$ruta) { header('Location: ' . URL_ROOT . '/rutas/index'); exit; }
        $informe       = RutaEjecucion::getInforme((int)$id);
        $participantes = RutaEjecucion::participantes((int)$id);

        $nombre = 'Informe_Ruta_' . preg_replace('/[^A-Za-z0-9_]/', '_', $ruta->nombre ?? 'ruta');
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombre . '_' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($out, ['REPÚBLICA BOLIVARIANA DE VENEZUELA'], ';');
        fputcsv($out, ['ALCALDÍA BOLIVARIANA DEL MUNICIPIO SUCRE'], ';');
        fputcsv($out, ['Instituto Municipal Autónomo de Turismo (IMATUR-SUCRE) — RIF. ' . ConfigSistema::rif()], ';');
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
            fputcsv($out, ['Niñas', $informe->ninas ?? 0], ';');
            fputcsv($out, ['Niños', $informe->ninos ?? 0], ';');
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
        $ejec = RutaEjecucion::find((int)$id);   // $id es de la SALIDA (mig. 078)
        $ruta = $ejec ? Ruta::find((int)$ejec->id_ruta) : null;
        if (!$ruta) {
            header('Location: ' . URL_ROOT . '/rutas/index');
            exit;
        }
        $puntos = Ruta::getPuntos($id);
        $config = ConfigSistema::getAll();
        $total  = RutaEjecucion::countParticipantes($id);

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

            $numero = RutaEjecucion::crearOficioEmitido($id, [
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
                'ejecucion'          => $ejec,
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
            'ejecucion'           => $ejec,
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
