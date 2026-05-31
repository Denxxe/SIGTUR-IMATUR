<?php
class RutasController extends Controller {

    public function index() {
        $rutas       = Ruta::all();
        $empleados   = Empleado::all();
        $departamentos = Departamento::all();
        $data = [
            'titulo'       => 'Gestión de Rutas Turísticas',
            'rutas'        => $rutas,
            'empleados'    => $empleados,
            'departamentos'=> $departamentos,
        ];
        $this->view('rutas/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $_POST = $this->sanitizePost();
        $userId    = $this->getUserId();
        $esEdicion = !empty($_POST['id']);

        $nivelesValidos = ['Fácil','Moderado','Difícil','Extremo'];
        $estadosValidos = ['Activa','Inactiva','En Mantenimiento'];
        $nivel   = in_array($_POST['nivel_dificultad'] ?? '', $nivelesValidos) ? $_POST['nivel_dificultad'] : 'Fácil';
        $estado  = in_array($_POST['estado'] ?? '', $estadosValidos)           ? $_POST['estado']           : 'Activa';
        $tipoRuta = in_array($_POST['tipo_ruta'] ?? '', Ruta::$TIPOS_RUTA)     ? $_POST['tipo_ruta']        : 'General';

        // Validaciones generales de la ruta
        $nombre = trim($_POST['nombre'] ?? '');
        if (mb_strlen($nombre) < 3) {
            flash('global_msg', 'El nombre de la ruta debe tener al menos 3 caracteres.', 'danger');
            header('Location: ' . URL_ROOT . '/rutas/index');
            exit;
        }

        $fechaVisita = $_POST['fecha_visita'] ?: null;
        if (!empty($fechaVisita) && $fechaVisita < date('Y-m-d')) {
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
            'nivel_dificultad'      => $nivel,
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
        $inventario_asignado  = RutaInventario::getByRuta($id);
        $inventario_disponible= Inventario::all();

        $db = new Database();
        $db->query("SELECT id, nombre, tipo FROM instituciones_externas WHERE is_active = TRUE ORDER BY nombre ASC");
        $instituciones = $db->resultSet();

        require_once '../app/models/Parroquia.php';
        $parroquias = Parroquia::all();

        // Historial de oficios emitidos para esta ruta
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
            'inventario_asignado'  => $inventario_asignado,
            'inventario_disponible'=> $inventario_disponible,
            'instituciones'        => $instituciones,
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

        $id_institucion = (int)($_POST['id_institucion'] ?? 0) ?: null;
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
                $edadAnios = (int)$hoyDt->diff($fnacDt)->y;
                if ($edadAnios < 5)  throw new Exception('El participante debe tener al menos 5 años.');
                if ($edadAnios >= 12) throw new Exception('Los participantes de 12 años o más deben registrarse con su cédula.');

                Ruta::inscribirLibre($id_ruta, [
                    'nombre_libre'   => $nombre,
                    'apellido_libre' => trim($_POST['apellido_libre'] ?? '') ?: null,
                    'cedula_libre'   => trim($_POST['cedula_libre']   ?? '') ?: null,
                    'genero_libre'   => trim($_POST['genero_libre']   ?? '') ?: null,
                    'fecha_nac_libre'=> $fechaNacLibreRaw,
                    'id_institucion' => $id_institucion,
                    'observaciones'  => $observaciones,
                ], $userId);

            // ── Flujo con cédula: buscar o crear en personas ─────────────────
            } else {
                $cedula = trim($_POST['cedula_busqueda'] ?? '');
                if (empty($cedula)) throw new Exception('Ingrese la cédula del participante.');

                $cedulaN = strtoupper(preg_replace('/[\s.\-]/', '', $cedula));
                if (!preg_match('/^[VEJGCP]?\d{6,9}$/', $cedulaN)) {
                    throw new Exception('Formato de cédula no válido. Use V-12345678, E-1234567 o solo los números.');
                }

                $nombre   = trim($_POST['nombre']   ?? '');
                $apellido = trim($_POST['apellido']  ?? '');
                if (empty($nombre) || empty($apellido)) {
                    throw new Exception('El nombre y apellido del participante son requeridos.');
                }

                // Correo: validar formato si está presente
                $correoRaw = trim($_POST['correo'] ?? '') ?: null;
                if ($correoRaw !== null && !filter_var($correoRaw, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('El correo electrónico no tiene un formato válido.');
                }

                $fechaNac   = trim($_POST['fecha_nacimiento'] ?? '') ?: null;
                if ($fechaNac && \DateTime::createFromFormat('Y-m-d', $fechaNac) === false) $fechaNac = null;
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

                Ruta::inscribir($id_ruta, $idPersona, $userId, $id_institucion, $observaciones);
            }

            flash('global_msg', 'Participante registrado correctamente.');
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

    public function storeInventario() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $_POST = $this->sanitizePost();

        $id_ruta       = (int)$_POST['id_ruta'];
        $id_inventario = (int)$_POST['id_inventario'];
        $cantidad      = (int)$_POST['cantidad'];
        $observaciones = trim($_POST['observaciones']);
        try {
            if (RutaInventario::asignar($id_ruta, $id_inventario, $cantidad, $observaciones, $this->getUserId())) {
                flash('global_msg', 'Equipamiento asignado.');
            } else {
                throw new Exception('Error al asignar recurso.');
            }
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/rutas/detalle/' . $id_ruta);
    }

    public function deleteInventario($id_asignacion, $id_ruta) {
        try {
            if (RutaInventario::remover($id_asignacion)) flash('global_msg', 'Asignación removida.', 'info');
            else throw new Exception('No se pudo desvincular el recurso.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/rutas/detalle/' . $id_ruta);
    }
}
