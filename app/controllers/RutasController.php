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
        $nivel  = in_array($_POST['nivel_dificultad'] ?? '', $nivelesValidos) ? $_POST['nivel_dificultad'] : 'Fácil';
        $estado = in_array($_POST['estado'] ?? '', $estadosValidos) ? $_POST['estado'] : 'Activa';

        $data = [
            'id'               => $esEdicion ? (int)$_POST['id'] : null,
            'nombre'           => trim($_POST['nombre']),
            'descripcion'      => trim($_POST['descripcion'] ?? ''),
            'duracion_estimada'=> trim($_POST['duracion_estimada'] ?? ''),
            'nivel_dificultad' => $nivel,
            'estado'           => $estado,
            'fecha_visita'     => $_POST['fecha_visita'] ?: null,
            'hora_visita'      => $_POST['hora_visita'] ?: null,
            'id_departamento'  => (int)$_POST['id_departamento'] ?: null,
            'id_facilitador'   => (int)$_POST['id_facilitador'] ?: null,
            'cupo_maximo'      => (int)($_POST['cupo_maximo'] ?? 20),
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

        $data = [
            'titulo'               => 'Ruta: ' . $ruta->nombre,
            'ruta'                 => $ruta,
            'puntos'               => $puntos,
            'participantes'        => $participantes,
            'inventario_asignado'  => $inventario_asignado,
            'inventario_disponible'=> $inventario_disponible,
        ];
        $this->view('rutas/detalle', $data);
    }

    // ── Participantes ────────────────────────────────────────────────────────

    public function inscribir() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $_POST = $this->sanitizePost();
        $id_ruta   = (int)$_POST['id_ruta'];
        $userId    = $this->getUserId();
        $esLibre   = !empty($_POST['tipo_participante_libre']);

        try {
            if ($esLibre) {
                $nombre = trim($_POST['nombre_libre'] ?? '');
                if (empty($nombre)) throw new Exception('El nombre del participante es requerido.');
                Ruta::inscribirLibre($id_ruta, [
                    'nombre_libre'   => $nombre,
                    'apellido_libre' => trim($_POST['apellido_libre'] ?? ''),
                    'cedula_libre'   => trim($_POST['cedula_libre'] ?? '') ?: null,
                ], $userId);
            } else {
                $cedula = trim($_POST['cedula_busqueda'] ?? '');
                if (empty($cedula)) throw new Exception('Ingrese la cédula del participante.');
                $persona = Ruta::buscarPersonaPorCedula($cedula);
                if (!$persona) throw new Exception("No se encontró ninguna persona con cédula '{$cedula}'.");
                Ruta::inscribir($id_ruta, $persona->id, $userId);
            }
            flash('global_msg', 'Participante registrado correctamente.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/rutas/detalle/' . $id_ruta);
    }

    public function desinscribir($id_participante) {
        $db = new Database();
        $db->query("SELECT id_ruta FROM participantes_ruta WHERE id = :id");
        $db->bind(':id', $id_participante);
        $row = $db->single();
        $id_ruta = $row ? $row->id_ruta : 0;

        try {
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

        $data = [
            'titulo'         => 'Generar Oficio: ' . $ruta->nombre,
            'ruta'           => $ruta,
            'puntos'         => $puntos,
            'config'         => $config,
            'fecha_hoy'      => date('j') . ' de ' . $meses[(int)date('n') - 1] . ' de ' . date('Y'),
            'fecha_ruta_esp' => $fechaRuta,
            'total_participantes' => $total,
        ];
        $this->view('rutas/oficio', $data);
    }

    // ── CRUD básico ──────────────────────────────────────────────────────────

    public function storePunto() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $_POST = $this->sanitizePost();

        $data = [
            'id'          => isset($_POST['punto_id']) ? (int)$_POST['punto_id'] : null,
            'id_ruta'     => (int)$_POST['id_ruta'],
            'nombre'      => trim($_POST['punto_nombre']),
            'descripcion' => trim($_POST['punto_descripcion']),
            'orden'       => (int)$_POST['orden'],
            'latitud'     => $_POST['latitud'] ?: null,
            'longitud'    => $_POST['longitud'] ?: null,
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
