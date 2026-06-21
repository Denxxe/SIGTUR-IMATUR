<?php
/**
 * Controlador EmpleadosController
 */
class EmpleadosController extends Controller {

    public function index() {
        $ver = ($_GET['ver'] ?? 'activos') === 'egresados' ? 'egresados' : 'activos';
        // Filtro por origen / comisión de servicio
        $origenOpts = array_merge(['comision'], Empleado::INSTITUCIONES_ORIGEN);
        $origen = in_array($_GET['origen'] ?? '', $origenOpts, true) ? $_GET['origen'] : '';
        $empleados = ($ver === 'egresados') ? Empleado::egresados($origen) : Empleado::all($origen);

        // Filtro por departamento (O4: organizar el personal por departamento).
        $depto = (int)($_GET['departamento'] ?? 0);
        if ($depto > 0) {
            $empleados = array_values(array_filter($empleados, fn($e) => (int)($e->id_departamento ?? 0) === $depto));
        }

        $data = [
            'titulo'        => 'Gestión de Personal (Empleados)',
            'empleados'     => $empleados,
            'ver'           => $ver,
            'origen'        => $origen,
            'departamento'  => $depto,
            'departamentos' => Departamento::all(),
            'motivos'       => Empleado::MOTIVOS_EGRESO,
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
            'proximo_expediente' => Empleado::proximoNumeroExpediente(),
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
            'motivos'      => Empleado::MOTIVOS_EGRESO,
            'historial_egresos' => Empleado::historialEgresos($id),
            'historial_traslados' => Empleado::historialTraslados($id),
            'tiempo_servicio'   => Empleado::tiempoServicio($empleado->fecha_ingreso, $empleado->fecha_egreso),
            'permiso_vigente'   => empty($empleado->fecha_egreso) ? PermisoLaboral::vigenteHoy($id) : null,
        ];

        $this->view('empleados/detalle', $data);
    }

    /**
     * Traslada al empleado a otro departamento (reasignación con historial, 3D).
     */
    public function trasladar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/empleados/index'); return; }
        $_POST = $this->sanitizePost();
        $id = (int)($_POST['id_empleado'] ?? 0);
        try {
            $depto = (int)($_POST['id_departamento_destino'] ?? 0);
            if ($depto < 1) throw new Exception('Selecciona el departamento destino.');
            $cargo = !empty($_POST['id_cargo_destino']) ? (int)$_POST['id_cargo_destino'] : null;
            $fecha = trim($_POST['fecha'] ?? '') ?: date('Y-m-d');
            $motivo = trim($_POST['motivo'] ?? '') ?: null;
            $obs    = trim($_POST['observacion'] ?? '') ?: null;
            Empleado::trasladar($id, $depto, $cargo, $fecha, $motivo, $obs, $this->getUserId());
            flash('global_msg', 'Traslado registrado y aplicado al expediente.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/empleados/detalle/' . $id);
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

    /**
     * AJAX (GET): ¿la cédula ya está registrada como empleado?
     * Usado por el formulario para avisar en vivo antes de enviar.
     */
    public function verificarCedula() {
        header('Content-Type: application/json; charset=utf-8');
        $ced = preg_replace('/\D/', '', $_GET['cedula'] ?? '');
        $excluir = !empty($_GET['id']) ? (int)$_GET['id'] : null;
        $existe = ($ced !== '') && Empleado::existeCedula($ced, $excluir);
        echo json_encode(['existe' => $existe, 'cedula' => $ced]);
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

            // Comisión de servicio = el empleado viene de Alcaldía o Gobernación (IMATUR = no comisión).
            // Se DERIVA del origen; no es una decisión manual.
            $esComisionServicio = ($institucionOrigen !== 'IMATUR');

            // Clasificación, estado civil y nivel académico (whitelists; vacío permitido)
            $clasificacion = in_array($_POST['clasificacion'] ?? '', Empleado::CLASIFICACIONES, true)
                             ? $_POST['clasificacion'] : null;
            $estadoCivil   = in_array($_POST['estado_civil'] ?? '', Empleado::ESTADOS_CIVILES, true)
                             ? $_POST['estado_civil'] : null;
            // nivel_academico: varchar libre (sin CHECK en BD). El select sugiere los
            // valores estándar; se acepta y conserva el texto (máx. 50) tal cual.
            $nivelAcademico = !empty($_POST['nivel_academico']) ? mb_substr(trim($_POST['nivel_academico']), 0, 50) : null;
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
                // nro_expediente lo asigna el sistema automáticamente (folio EXP-####); no se lee del POST.
                'fecha_ingreso'  => $_POST['fecha_ingreso'],
                'tipo_contrato'  => $tipoContrato,
                'institucion_origen'   => $institucionOrigen,
                'es_comision_servicio' => $esComisionServicio,
                // Antigüedad para vacaciones (3A): solo comisionados; si no, usa fecha_ingreso.
                'fecha_ingreso_administracion' => ($institucionOrigen !== 'IMATUR' && !empty($_POST['fecha_ingreso_administracion']))
                                                  ? $_POST['fecha_ingreso_administracion'] : null,
                'clasificacion'  => $clasificacion,
                'grupo_rotacion' => $grupoRotacion,
                'uniforme'       => $uniforme,
                'talla_camisa'   => ($uniforme && !empty($_POST['talla_camisa'])) ? trim($_POST['talla_camisa']) : null,
                'talla_pantalon' => ($uniforme && !empty($_POST['talla_pantalon'])) ? trim($_POST['talla_pantalon']) : null,
                'talla_zapato'   => ($uniforme && !empty($_POST['talla_zapato'])) ? trim($_POST['talla_zapato']) : null,
                // Vencimiento del contrato: solo aplica a Contratados (los Fijos no expiran por tiempo).
                // La fecha de egreso real la gestiona el módulo de egreso (R-12), no este formulario.
                'fecha_vencimiento_contrato' => ($tipoContrato !== 'Fijo' && !empty($_POST['fecha_vencimiento_contrato']))
                                                ? $_POST['fecha_vencimiento_contrato'] : null,
                'id_horario'     => !empty($_POST['id_horario']) ? (int)$_POST['id_horario'] : null,
                // Datos personales extra + formación académica (van a personas)
                'parroquia_id'   => !empty($_POST['parroquia_id']) ? (int)$_POST['parroquia_id'] : null,
                'rif'            => !empty($_POST['rif']) ? trim($_POST['rif']) : null,
                'estado_civil'   => $estadoCivil,
                'discapacidad'   => $discapacidad,
                'discapacidad_detalle' => ($discapacidad && !empty($_POST['discapacidad_detalle'])) ? trim($_POST['discapacidad_detalle']) : null,
                'nivel_academico'=> $nivelAcademico,
                'profesion'      => !empty($_POST['profesion']) ? trim($_POST['profesion']) : null,
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

            // Validación de campos obligatorios (server-side; los `required` de HTML pueden saltarse).
            $requeridos = [
                'cedula' => 'Cédula', 'nombre' => 'Nombres', 'apellido' => 'Apellidos',
                'genero' => 'Género', 'fecha_nacimiento' => 'Fecha de nacimiento',
                'telefono' => 'Teléfono', 'rif' => 'RIF', 'parroquia_id' => 'Parroquia',
                'direccion' => 'Dirección', 'id_cargo' => 'Cargo', 'id_departamento' => 'Departamento',
                'clasificacion' => 'Clasificación', 'tipo_contrato' => 'Tipo de contrato',
                'institucion_origen' => 'Institución / Nómina', 'fecha_ingreso' => 'Fecha de ingreso',
            ];
            $faltantes = [];
            foreach ($requeridos as $campo => $etiqueta) {
                if (empty($data[$campo])) $faltantes[] = $etiqueta;
            }
            if (!empty($faltantes)) {
                flash('global_msg', 'Complete los campos obligatorios: ' . implode(', ', $faltantes) . '.', 'danger');
                header('Location: ' . $volverForm);
                return;
            }
            if (!in_array($data['genero'], ['M', 'F'], true)) {
                flash('global_msg', 'Seleccione un género válido.', 'danger');
                header('Location: ' . $volverForm);
                return;
            }

            // B6 — Validación de RIF venezolano (formato letra + 8 dígitos + verificador)
            if (!$this->rifValido($data['rif'])) {
                flash('global_msg', 'El RIF no es válido. Use el formato venezolano: letra (V/E/J/P/G/C) + 8 dígitos + verificador. Ej: J-12345678-9.', 'danger');
                header('Location: ' . $volverForm);
                return;
            }
            $data['rif'] = $this->normalizarRif($data['rif']);

            // B5 — Normaliza nombres/apellidos a Mayúscula Inicial (robustez server-side)
            $data['nombre']   = mb_convert_case($data['nombre'], MB_CASE_TITLE, 'UTF-8');
            $data['apellido'] = mb_convert_case($data['apellido'], MB_CASE_TITLE, 'UTF-8');

            // Cédula: solo dígitos (regla global, mig.037)
            $data['cedula'] = preg_replace('/\D/', '', $data['cedula']);

            // Anti-duplicado: una misma cédula no puede registrarse dos veces como empleado.
            if (Empleado::existeCedula($data['cedula'], $esEdicion ? (int)$data['id'] : null)) {
                $msg = $esEdicion
                    ? "La cédula {$data['cedula']} ya pertenece a otro empleado registrado."
                    : "Ya existe un empleado registrado con la cédula {$data['cedula']}. Si egresó de la institución, use la opción de «Reingreso» en el histórico de egresados; no es necesario registrarlo de nuevo.";
                flash('global_msg', $msg, 'danger');
                header('Location: ' . $volverForm);
                return;
            }

            // B4 — Vencimiento del contrato: OBLIGATORIO para Contratados, mínimo 3 meses.
            // Los Fijos no expiran por tiempo (campo nulo).
            if ($tipoContrato !== 'Fijo') {
                if (empty($data['fecha_vencimiento_contrato'])) {
                    flash('global_msg', 'El vencimiento del contrato es obligatorio para empleados Contratados.', 'danger');
                    header('Location: ' . $volverForm);
                    return;
                }
                $ing = $data['fecha_ingreso'];
                $ven = $data['fecha_vencimiento_contrato'];
                $minVenc = date('Y-m-d', strtotime($ing . ' +3 months'));
                if ($ven <= $ing) {
                    flash('global_msg', 'La fecha de vencimiento del contrato debe ser posterior a la fecha de ingreso.', 'danger');
                    header('Location: ' . $volverForm);
                    return;
                }
                if ($ven < $minVenc) {
                    flash('global_msg', "El contrato debe tener una vigencia mínima de 3 meses (vencimiento a partir del {$minVenc}).", 'danger');
                    header('Location: ' . $volverForm);
                    return;
                }
            }

            // Validación de correo electrónico
            if (!empty($data['correo']) && !$this->emailValido($data['correo'])) {
                flash('global_msg', 'El correo electrónico "' . htmlspecialchars($data['correo']) . '" no es válido (sin espacios ni símbolos especiales; ejemplo: nombre@dominio.com).', 'danger');
                header('Location: ' . $volverForm);
                return;
            }

            // Validación de teléfono (si se proporcionó)
            if (!empty($data['telefono']) && !$this->telefonoValido($data['telefono'])) {
                flash('global_msg', 'El teléfono no es válido. Debe ser un número venezolano (prefijo + 7 dígitos).', 'danger');
                header('Location: ' . $volverForm);
                return;
            }

            // Validación de edad (RN: 18–65; hasta 70 solo por comisión de servicio)
            $edad = Util::edad($data['fecha_nacimiento'] ?? null);
            if (!empty($data['fecha_nacimiento']) && $edad === null) {
                flash('global_msg', 'La fecha de nacimiento no es válida (no puede ser una fecha futura).', 'danger');
                header('Location: ' . $volverForm);
                return;
            }
            if ($edad !== null) {
                $errEdad = null;
                if ($edad < 18)          $errEdad = "El empleado debe ser mayor de 18 años (edad: {$edad}).";
                elseif ($edad > 70)      $errEdad = "La edad no puede superar los 70 años (edad: {$edad}).";
                elseif ($edad > 65 && !$esComisionServicio) $errEdad = "Personal IMATUR: máximo 65 años; los 66–70 solo aplican a comisión de servicio (Alcaldía/Gobernación). Edad: {$edad}.";
                if ($errEdad) {
                    flash('global_msg', $errEdad, 'danger');
                    header('Location: ' . $volverForm);
                    return;
                }
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

    /**
     * Egreso / desincorporación del empleado (renuncia, despido, jubilación…).
     * No borra el registro: lo marca como egresado (histórico consultable).
     */
    public function egresar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_ROOT . '/empleados/index');
            return;
        }
        $_POST = $this->sanitizePost();
        $id     = (int)($_POST['id_empleado'] ?? 0);
        $fecha  = $_POST['fecha_egreso'] ?? '';
        $motivo = $_POST['motivo_egreso'] ?? '';
        $obs    = $_POST['observacion_egreso'] ?? null;

        try {
            if ($fecha > date('Y-m-d')) throw new Exception("La fecha de egreso no puede ser futura.");
            Empleado::procesarEgreso($id, $fecha, $motivo, $obs, $this->getUserId());
            flash('global_msg', 'Egreso procesado. El empleado pasó al histórico de egresados.', 'warning');
        } catch (Exception $e) {
            flash('global_msg', 'No se pudo procesar el egreso: ' . $e->getMessage(), 'danger');
        }
        $this->backToDetalle($id);
    }

    /** Reingreso de un ex-empleado (lo reincorpora a la nómina activa). */
    public function reingresar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_ROOT . '/empleados/index');
            return;
        }
        $_POST = $this->sanitizePost();
        $id  = (int)($_POST['id_empleado'] ?? 0);
        $obs = $_POST['reingreso_observacion'] ?? null;
        try {
            Empleado::reingresar($id, $obs, $this->getUserId());
            flash('global_msg', 'Reingreso registrado. El empleado vuelve a la nómina activa.');
        } catch (Exception $e) {
            flash('global_msg', 'No se pudo registrar el reingreso: ' . $e->getMessage(), 'danger');
        }
        $this->backToDetalle($id);
    }

    /** Papelera: elimina un registro creado por error (soft delete). */
    public function delete($id) {
        try {
            if (Empleado::delete($id, $this->getUserId())) {
                flash('global_msg', 'El registro del empleado ha sido movido a la papelera.', 'warning');
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

    public function generarConstancia($idEmpleado, $tipo = 'trabajo') {
        try {
            if (!array_key_exists($tipo, Constancia::TIPOS)) $tipo = 'trabajo';
            $idConst = Constancia::crear($idEmpleado, $tipo, $this->getUserId());
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
            'tiempo_servicio' => Empleado::tiempoServicio($empleado->fecha_ingreso ?? null, $empleado->fecha_egreso ?? null),
            'egresado'        => !empty($empleado->fecha_egreso),
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
