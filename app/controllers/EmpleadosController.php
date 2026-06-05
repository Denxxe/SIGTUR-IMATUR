<?php
/**
 * Controlador EmpleadosController
 */
class EmpleadosController extends Controller {

    public function index() {
        $empleados     = Empleado::all();
        $cargos        = Cargo::all();
        $departamentos = Departamento::all();
        $parroquias    = Parroquia::all();
        $horarios      = Horario::all();

        $data = [
            'titulo'       => 'Gestión de Personal (Empleados)',
            'empleados'    => $empleados,
            'cargos'       => $cargos,
            'departamentos'=> $departamentos,
            'horarios'     => $horarios,
            'parroquias'   => $parroquias,
        ];

        $this->view('empleados/index', $data);
    }

    /** Catálogos para el asistente de registro/edición. */
    private function catalogosForm(): array {
        return [
            'cargos'        => Cargo::all(),
            'departamentos' => Departamento::all(),
            'parroquias'    => Parroquia::all(),
            'horarios'      => Horario::all(),
        ];
    }

    /** Asistente multi-paso — alta de empleado. */
    public function nuevo() {
        $data = array_merge($this->catalogosForm(), [
            'titulo'   => 'Registrar Empleado',
            'empleado' => null,
        ]);
        $this->view('empleados/form', $data);
    }

    /** Asistente multi-paso — edición de empleado. */
    public function editar($id) {
        $empleado = Empleado::find($id);
        if (!$empleado) {
            flash('global_msg', 'El empleado solicitado no existe.', 'danger');
            header('Location: ' . URL_ROOT . '/empleados/index');
            return;
        }
        $data = array_merge($this->catalogosForm(), [
            'titulo'      => 'Editar: ' . $empleado->nombre . ' ' . $empleado->apellido,
            'empleado'    => $empleado,
            'familiares'  => CargaFamiliar::porPersona($empleado->id_persona),
        ]);
        $this->view('empleados/form', $data);
    }

    /**
     * Expediente / Ficha del empleado: datos completos + tablas hijas.
     */
    public function detalle($id) {
        $empleado = Empleado::find($id);
        if (!$empleado) {
            flash('global_msg', 'El empleado solicitado no existe.', 'danger');
            header('Location: ' . URL_ROOT . '/empleados/index');
            return;
        }

        $data = [
            'titulo'       => 'Expediente: ' . $empleado->nombre . ' ' . $empleado->apellido,
            'empleado'     => $empleado,
            'cargos'       => Cargo::all(),
            'departamentos'=> Departamento::all(),
            'parroquias'   => Parroquia::all(),
            'familiares'   => CargaFamiliar::porPersona($empleado->id_persona),
            'cursos'       => CursoRealizado::porPersona($empleado->id_persona),
            'experiencia'  => ExperienciaLaboral::porPersona($empleado->id_persona),
            'horarios'     => Horario::all(),
            'recaudos'     => ExpedienteDocumento::recaudosEstado($id),
            'constancias'  => Constancia::porEmpleado($id),
        ];

        $this->view('empleados/detalle', $data);
    }

    /**
     * Ficha Técnica del Trabajador (vista imprimible).
     */
    public function fichaTecnica($id) {
        $empleado = Empleado::find($id);
        if (!$empleado) {
            flash('global_msg', 'El empleado solicitado no existe.', 'danger');
            header('Location: ' . URL_ROOT . '/empleados/index');
            return;
        }
        $data = [
            'titulo'      => 'Ficha Técnica',
            'empleado'    => $empleado,
            'familiares'  => CargaFamiliar::porPersona($empleado->id_persona),
            'cursos'      => CursoRealizado::porPersona($empleado->id_persona),
            'experiencia' => ExperienciaLaboral::porPersona($empleado->id_persona),
        ];
        $this->view('empleados/ficha_tecnica', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = $this->sanitizePost();
            
            // Estabilidad del contrato (whitelist desde el modelo — patrón H-07)
            $tipoContrato  = in_array($_POST['tipo_contrato'] ?? '', Empleado::TIPOS_CONTRATO, true)
                             ? $_POST['tipo_contrato'] : Empleado::TIPO_CONTRATO_DEFAULT;

            // Origen / nómina del empleado
            $institucionOrigen = in_array($_POST['institucion_origen'] ?? '', Empleado::INSTITUCIONES_ORIGEN, true)
                                 ? $_POST['institucion_origen'] : Empleado::INSTITUCION_ORIGEN_DEFAULT;

            // Comisión de servicio: solo aplica a Alcaldía/Gobernación
            $esComisionServicio = !empty($_POST['es_comision_servicio']) && $institucionOrigen !== 'IMATUR';

            // Clasificación, estado civil y nivel académico (whitelists; vacío permitido)
            $clasificacion = in_array($_POST['clasificacion'] ?? '', Empleado::CLASIFICACIONES, true)
                             ? $_POST['clasificacion'] : null;
            $estadoCivil   = in_array($_POST['estado_civil'] ?? '', Empleado::ESTADOS_CIVILES, true)
                             ? $_POST['estado_civil'] : null;
            $nivelAcademico = in_array($_POST['nivel_academico'] ?? '', Empleado::NIVELES_ACADEMICOS, true)
                             ? $_POST['nivel_academico'] : null;
            $discapacidad  = !empty($_POST['discapacidad']);
            $grupoRotacion = in_array($_POST['grupo_rotacion'] ?? '', Empleado::GRUPOS_ROTACION, true)
                             ? $_POST['grupo_rotacion'] : null;
            $uniforme = !empty($_POST['uniforme']);

            $data = [
                'id'             => isset($_POST['id']) ? (int)$_POST['id'] : null,
                'id_persona'     => isset($_POST['id_persona']) ? (int)$_POST['id_persona'] : null,
                'cedula'         => trim($_POST['cedula']),
                'nombre'         => trim($_POST['nombre']),
                'apellido'       => trim($_POST['apellido']),
                'telefono'       => trim($_POST['telefono']),
                'correo'         => trim($_POST['correo']),
                'genero'         => $_POST['genero'],
                'fecha_nacimiento'=> $_POST['fecha_nacimiento'],
                'direccion'      => trim($_POST['direccion']),
                'id_cargo'       => (int)$_POST['id_cargo'],
                'id_departamento'=> (int)$_POST['id_departamento'],
                'nro_expediente' => trim($_POST['nro_expediente']),
                'fecha_ingreso'  => $_POST['fecha_ingreso'],
                'tipo_contrato'  => $tipoContrato,
                'institucion_origen'   => $institucionOrigen,
                'es_comision_servicio' => $esComisionServicio,
                'clasificacion'  => $clasificacion,
                'grupo_rotacion' => $grupoRotacion,
                'uniforme'       => $uniforme,
                'talla_camisa'   => ($uniforme && !empty($_POST['talla_camisa'])) ? trim($_POST['talla_camisa']) : null,
                'talla_pantalon' => ($uniforme && !empty($_POST['talla_pantalon'])) ? trim($_POST['talla_pantalon']) : null,
                'talla_zapato'   => ($uniforme && !empty($_POST['talla_zapato'])) ? trim($_POST['talla_zapato']) : null,
                'fecha_egreso'   => !empty($_POST['fecha_egreso']) ? $_POST['fecha_egreso'] : null,
                'id_horario'     => !empty($_POST['id_horario']) ? (int)$_POST['id_horario'] : null,
                // Datos personales extra + formación académica (van a personas)
                'parroquia_id'   => !empty($_POST['parroquia_id']) ? (int)$_POST['parroquia_id'] : null,
                'rif'            => !empty($_POST['rif']) ? trim($_POST['rif']) : null,
                'estado_civil'   => $estadoCivil,
                'discapacidad'   => $discapacidad,
                'discapacidad_detalle' => ($discapacidad && !empty($_POST['discapacidad_detalle'])) ? trim($_POST['discapacidad_detalle']) : null,
                'nivel_academico'=> $nivelAcademico,
                'profesion'      => !empty($_POST['profesion']) ? trim($_POST['profesion']) : null,
                'titulo'         => !empty($_POST['titulo']) ? trim($_POST['titulo']) : null,
                'fecha_graduacion' => !empty($_POST['fecha_graduacion']) ? $_POST['fecha_graduacion'] : null,
                'institucion_academica' => !empty($_POST['institucion_academica']) ? trim($_POST['institucion_academica']) : null,
                'centro_votacion' => !empty($_POST['centro_votacion']) ? trim($_POST['centro_votacion']) : null,
                'consejo_comunal' => !empty($_POST['consejo_comunal']) ? trim($_POST['consejo_comunal']) : null,
                'comuna'          => !empty($_POST['comuna']) ? trim($_POST['comuna']) : null,
            ];

            $esEdicion = !empty($data['id']);
            $volverForm = $esEdicion
                ? URL_ROOT . '/empleados/editar/' . (int)$data['id']
                : URL_ROOT . '/empleados/nuevo';

            // Validación de correo electrónico
            if (!empty($data['correo']) && !filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
                flash('global_msg', 'El correo electrónico "' . htmlspecialchars($data['correo']) . '" no es válido (ejemplo: nombre@dominio.com).', 'danger');
                header('Location: ' . $volverForm);
                return;
            }

            $empleado = new Empleado($data);

            try {
                if (!$empleado->save($this->getUserId())) {
                    throw new Exception("Error interno al procesar el registro del empleado.");
                }

                // Al crear: insertar la carga familiar recolectada en el asistente
                if (!$esEdicion) {
                    $idPersona = $empleado->getIdPersona();
                    $this->guardarCargaFamiliarInicial($idPersona);
                    flash('global_msg', "Empleado registrado. Complete cursos y experiencia en el expediente si aplica.");
                    header('Location: ' . URL_ROOT . '/empleados/detalle/' . (int)$empleado->getId());
                } else {
                    flash('global_msg', "Datos de empleado actualizados correctamente.");
                    header('Location: ' . URL_ROOT . '/empleados/index');
                }
            } catch (Exception $e) {
                flash('global_msg', 'No se pudo guardar la información: ' . $e->getMessage(), 'danger');
                header('Location: ' . $volverForm);
            }
        }
    }

    /** Inserta los familiares recolectados en el asistente (arrays paralelos cf_*). */
    private function guardarCargaFamiliarInicial($idPersona) {
        if (empty($idPersona) || empty($_POST['cf_nombre']) || !is_array($_POST['cf_nombre'])) return;
        $nombres     = $_POST['cf_nombre'];
        $cedulas     = $_POST['cf_cedula']     ?? [];
        $fnacs       = $_POST['cf_fnac']       ?? [];
        $parentescos = $_POST['cf_parentesco'] ?? [];
        foreach ($nombres as $i => $nombre) {
            $nombre = trim($nombre);
            if ($nombre === '') continue;
            try {
                CargaFamiliar::save([
                    'id_persona'      => $idPersona,
                    'nombre_apellido' => $nombre,
                    'cedula'          => $cedulas[$i] ?? null,
                    'fecha_nacimiento'=> $fnacs[$i] ?? null,
                    'parentesco'      => $parentescos[$i] ?? '',
                ], $this->getUserId());
            } catch (Exception $e) {
                // Un familiar inválido no debe abortar el alta del empleado
            }
        }
    }

    public function delete($id) {
        try {
            if (Empleado::delete($id, $this->getUserId())) {
                flash('global_msg', 'El expediente del empleado ha sido movido a la papelera.', 'warning');
            } else {
                throw new Exception("No pudimos eliminar el registro en este momento.");
            }
        } catch (Exception $e) {
            flash('global_msg', 'Fallo en la eliminación: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/empleados/index');
    }

    // ── Tablas hijas de la Ficha Técnica ───────────────────────────────────────

    /** Redirige al expediente del empleado (helper interno). */
    private function backToDetalle($idEmpleado) {
        header('Location: ' . URL_ROOT . '/empleados/detalle/' . (int)$idEmpleado);
    }

    public function guardarFamiliar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $_POST = $this->sanitizePost();
        $idEmpleado = (int)($_POST['id_empleado'] ?? 0);
        try {
            CargaFamiliar::save($_POST, $this->getUserId());
            flash('global_msg', 'Familiar guardado en la carga familiar.');
        } catch (Exception $e) {
            flash('global_msg', 'No se pudo guardar el familiar: ' . $e->getMessage(), 'danger');
        }
        $this->backToDetalle($idEmpleado);
    }

    public function eliminarFamiliar($id, $idEmpleado = 0) {
        try {
            CargaFamiliar::delete($id, $this->getUserId());
            flash('global_msg', 'Familiar eliminado.', 'warning');
        } catch (Exception $e) {
            flash('global_msg', 'No se pudo eliminar: ' . $e->getMessage(), 'danger');
        }
        $this->backToDetalle($idEmpleado);
    }

    public function guardarCurso() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $_POST = $this->sanitizePost();
        $idEmpleado = (int)($_POST['id_empleado'] ?? 0);
        try {
            CursoRealizado::save($_POST, $this->getUserId());
            flash('global_msg', 'Curso guardado.');
        } catch (Exception $e) {
            flash('global_msg', 'No se pudo guardar el curso: ' . $e->getMessage(), 'danger');
        }
        $this->backToDetalle($idEmpleado);
    }

    public function eliminarCurso($id, $idEmpleado = 0) {
        try {
            CursoRealizado::delete($id, $this->getUserId());
            flash('global_msg', 'Curso eliminado.', 'warning');
        } catch (Exception $e) {
            flash('global_msg', 'No se pudo eliminar: ' . $e->getMessage(), 'danger');
        }
        $this->backToDetalle($idEmpleado);
    }

    public function guardarExperiencia() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $_POST = $this->sanitizePost();
        $idEmpleado = (int)($_POST['id_empleado'] ?? 0);
        try {
            ExperienciaLaboral::save($_POST, $this->getUserId());
            flash('global_msg', 'Experiencia laboral guardada.');
        } catch (Exception $e) {
            flash('global_msg', 'No se pudo guardar la experiencia: ' . $e->getMessage(), 'danger');
        }
        $this->backToDetalle($idEmpleado);
    }

    public function eliminarExperiencia($id, $idEmpleado = 0) {
        try {
            ExperienciaLaboral::delete($id, $this->getUserId());
            flash('global_msg', 'Experiencia eliminada.', 'warning');
        } catch (Exception $e) {
            flash('global_msg', 'No se pudo eliminar: ' . $e->getMessage(), 'danger');
        }
        $this->backToDetalle($idEmpleado);
    }

    // ── Recaudos del expediente (documentos) ───────────────────────────────────

    public function subirDocumento() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $idEmpleado = (int)($_POST['id_empleado'] ?? 0);
        $tipo = $_POST['tipo_documento'] ?? '';

        try {
            if (!array_key_exists($tipo, ExpedienteDocumento::RECAUDOS)) {
                throw new Exception("Tipo de recaudo inválido.");
            }
            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Debe adjuntar un archivo válido.");
            }
            // Validación de tipo y tamaño (PDF/imagen, máx. 5MB)
            $permitidas = ['pdf', 'jpg', 'jpeg', 'png'];
            $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $permitidas, true)) {
                throw new Exception("Formato no permitido. Use PDF, JPG o PNG.");
            }
            if ($_FILES['archivo']['size'] > 5 * 1024 * 1024) {
                throw new Exception("El archivo supera el límite de 5 MB.");
            }

            // Convención de nombre: Tipo_Empleado_ID_timestamp.ext
            $fileName  = $tipo . '_Empleado_' . $idEmpleado . '_' . time() . '.' . $ext;
            $uploadDir = dirname(dirname(__DIR__)) . '/public/uploads/expedientes/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $uploadDir . $fileName)) {
                throw new Exception("No se pudo guardar el archivo en el servidor.");
            }

            ExpedienteDocumento::save([
                'id_empleado'     => $idEmpleado,
                'tipo_documento'  => $tipo,
                'archivo_url'     => '/uploads/expedientes/' . $fileName,
                'nombre_original' => basename($_FILES['archivo']['name']),
                'observaciones'   => $_POST['observaciones'] ?? null,
            ], $this->getUserId());

            flash('global_msg', 'Recaudo cargado correctamente.');
        } catch (Exception $e) {
            flash('global_msg', 'No se pudo cargar el recaudo: ' . $e->getMessage(), 'danger');
        }
        $this->backToDetalle($idEmpleado);
    }

    public function eliminarDocumento($id, $idEmpleado = 0) {
        try {
            ExpedienteDocumento::delete($id, $this->getUserId());
            flash('global_msg', 'Recaudo eliminado.', 'warning');
        } catch (Exception $e) {
            flash('global_msg', 'No se pudo eliminar: ' . $e->getMessage(), 'danger');
        }
        $this->backToDetalle($idEmpleado);
    }

    // ── Constancias de trabajo (R-10) ──────────────────────────────────────────

    public function generarConstancia($idEmpleado) {
        try {
            $idConst = Constancia::crear($idEmpleado, 'Constancia de trabajo', $this->getUserId());
            if (!$idConst) throw new Exception("No se pudo generar la constancia.");
            flash('global_msg', 'Constancia generada. Ábrala para imprimir/PDF.');
            header('Location: ' . URL_ROOT . '/empleados/constancia/' . (int)$idConst);
        } catch (Exception $e) {
            flash('global_msg', 'No se pudo generar la constancia: ' . $e->getMessage(), 'danger');
            $this->backToDetalle($idEmpleado);
        }
    }

    /** Vista imprimible de una constancia. */
    public function constancia($idConstancia) {
        $constancia = Constancia::find($idConstancia);
        if (!$constancia) {
            flash('global_msg', 'La constancia solicitada no existe.', 'danger');
            header('Location: ' . URL_ROOT . '/empleados/index');
            return;
        }
        $empleado = Empleado::find($constancia->id_empleado);
        $data = [
            'titulo'     => 'Constancia de Trabajo',
            'constancia' => $constancia,
            'empleado'   => $empleado,
            'config'     => ConfigSistema::getAll(),
            'fecha_hoy'  => $this->fechaLarga(date('Y-m-d')),
        ];
        $this->view('empleados/constancia', $data);
    }

    public function eliminarConstancia($id, $idEmpleado = 0) {
        try {
            Constancia::delete($id, $this->getUserId());
            flash('global_msg', 'Constancia eliminada del historial.', 'warning');
        } catch (Exception $e) {
            flash('global_msg', 'No se pudo eliminar: ' . $e->getMessage(), 'danger');
        }
        $this->backToDetalle($idEmpleado);
    }

    /** Fecha en formato largo en español (para documentos). */
    private function fechaLarga($fecha) {
        $meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
                  'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $t = strtotime($fecha);
        return 'Cumaná, ' . (int)date('d', $t) . ' de ' . $meses[(int)date('n', $t)] . ' de ' . date('Y', $t);
    }
}
