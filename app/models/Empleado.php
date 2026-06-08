<?php

/**
 * Clase Empleado: Modelo para la tabla empleados (Hereda atributos de Persona en el flujo)
 */
class Empleado extends Model
{
    // ── Enums centralizados (fuente única — patrón H-07) ───────────────────────
    // Estabilidad del contrato. 'Suplente'/'Comisión de Servicio' deprecados (mig. 025).
    const TIPOS_CONTRATO       = ['Fijo', 'Contratado'];
    const TIPO_CONTRATO_DEFAULT = 'Contratado';
    // Origen / nómina del empleado. 'Comisión de Servicio' se modela como flag aparte.
    const INSTITUCIONES_ORIGEN  = ['Alcaldía', 'Gobernación', 'IMATUR'];
    const INSTITUCION_ORIGEN_DEFAULT = 'IMATUR';
    // Clasificación laboral (mig. 026)
    const CLASIFICACIONES = ['Empleado', 'Obrero'];
    // Estado civil (mig. 026) — valores canónicos sin género
    const ESTADOS_CIVILES = ['Soltero', 'Casado', 'Concubinato', 'Divorciado', 'Viudo'];
    // Niveles académicos sugeridos para la ficha técnica
    const NIVELES_ACADEMICOS = ['Primaria', 'Media', 'Diversificada', 'Técnico Medio', 'Universitario', 'Postgrado'];
    // Grupo de rotación (solo Servicios Generales) — mig.028
    const GRUPOS_ROTACION = ['A', 'B'];
    // Motivos de egreso / desincorporación (mig. 036 — R-12)
    const MOTIVOS_EGRESO = ['Renuncia', 'Despido', 'Jubilación', 'Fin de contrato', 'Fallecimiento', 'Otro'];

    // Datos de personas
    private ?int $id_persona;
    private string $cedula;
    private string $nombre;
    private string $apellido;
    private string $telefono;
    private string $correo;
    private ?string $genero;
    private ?string $fecha_nacimiento;
    private string $direccion;
    private ?int    $parroquia_id;
    private ?string $rif;
    private ?string $estado_civil;
    private bool    $discapacidad;
    private ?string $discapacidad_detalle;
    private ?string $nivel_academico;
    private ?string $profesion;
    private ?string $titulo;
    private ?string $fecha_graduacion;
    private ?string $institucion_academica;
    private ?string $centro_votacion;
    private ?string $consejo_comunal;
    private ?string $comuna;

    // Datos de empleados
    private ?int $id;
    private ?int $id_cargo;
    private ?int $id_departamento;
    private ?string $nro_expediente;
    private ?string $fecha_ingreso;
    private ?string $tipo_contrato;
    private ?string $institucion_origen;
    private bool    $es_comision_servicio;
    private ?string $clasificacion;
    private ?string $grupo_rotacion;
    private bool    $uniforme;
    private ?string $talla_camisa;
    private ?string $talla_pantalon;
    private ?string $talla_zapato;
    private ?string $fecha_egreso;
    private ?int    $id_horario;

    public function __construct(array $data = [])
    {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->id_persona = $data['id_persona'] ?? null;
            $this->cedula = $data['cedula'] ?? '';
            $this->nombre = $data['nombre'] ?? '';
            $this->apellido = $data['apellido'] ?? '';
            $this->telefono = $data['telefono'] ?? '';
            $this->correo = $data['correo'] ?? '';
            $this->genero = !empty($data['genero']) ? $data['genero'] : null;
            $this->fecha_nacimiento = !empty($data['fecha_nacimiento']) ? $data['fecha_nacimiento'] : null;
            $this->direccion = $data['direccion'] ?? '';
            $this->parroquia_id = !empty($data['parroquia_id']) ? (int)$data['parroquia_id'] : null;
            $this->rif = !empty($data['rif']) ? $data['rif'] : null;
            $this->estado_civil = !empty($data['estado_civil']) ? $data['estado_civil'] : null;
            $this->discapacidad = !empty($data['discapacidad']);
            $this->discapacidad_detalle = !empty($data['discapacidad_detalle']) ? $data['discapacidad_detalle'] : null;
            $this->nivel_academico = !empty($data['nivel_academico']) ? $data['nivel_academico'] : null;
            $this->profesion = !empty($data['profesion']) ? $data['profesion'] : null;
            $this->titulo = !empty($data['titulo']) ? $data['titulo'] : null;
            $this->fecha_graduacion = !empty($data['fecha_graduacion']) ? $data['fecha_graduacion'] : null;
            $this->institucion_academica = !empty($data['institucion_academica']) ? $data['institucion_academica'] : null;
            $this->centro_votacion = !empty($data['centro_votacion']) ? $data['centro_votacion'] : null;
            $this->consejo_comunal = !empty($data['consejo_comunal']) ? $data['consejo_comunal'] : null;
            $this->comuna = !empty($data['comuna']) ? $data['comuna'] : null;
            $this->id_cargo = !empty($data['id_cargo']) ? (int)$data['id_cargo'] : null;
            $this->id_departamento = !empty($data['id_departamento']) ? (int)$data['id_departamento'] : null;
            $this->nro_expediente = $data['nro_expediente'] ?? null;
            $this->fecha_ingreso = !empty($data['fecha_ingreso']) ? $data['fecha_ingreso'] : null;
            $this->tipo_contrato = !empty($data['tipo_contrato']) ? $data['tipo_contrato'] : self::TIPO_CONTRATO_DEFAULT;
            $this->institucion_origen = !empty($data['institucion_origen']) ? $data['institucion_origen'] : self::INSTITUCION_ORIGEN_DEFAULT;
            $this->es_comision_servicio = !empty($data['es_comision_servicio']);
            $this->clasificacion = !empty($data['clasificacion']) ? $data['clasificacion'] : null;
            $this->grupo_rotacion = !empty($data['grupo_rotacion']) ? $data['grupo_rotacion'] : null;
            $this->uniforme = !empty($data['uniforme']);
            $this->talla_camisa = !empty($data['talla_camisa']) ? $data['talla_camisa'] : null;
            $this->talla_pantalon = !empty($data['talla_pantalon']) ? $data['talla_pantalon'] : null;
            $this->talla_zapato = !empty($data['talla_zapato']) ? $data['talla_zapato'] : null;
            $this->fecha_egreso  = !empty($data['fecha_egreso'])  ? $data['fecha_egreso']  : null;
            $this->id_horario    = !empty($data['id_horario'])    ? (int)$data['id_horario'] : null;
        }
    }

    /**
     * Obtener listado completo de empleados con joins
     */
    public function getId() { return $this->id; }
    public function getIdPersona() { return $this->id_persona; }

    public static function all()
    {
        $db = new Database();
        $db->query("SELECT e.*,
                           p.cedula, p.nombre, p.apellido, p.telefono, p.correo, p.genero,
                           p.fecha_nacimiento, p.direccion, p.parroquia_id, p.rif, p.estado_civil,
                           p.discapacidad, p.discapacidad_detalle, p.nivel_academico, p.profesion,
                           p.titulo, p.fecha_graduacion, p.institucion_academica,
                           p.centro_votacion, p.consejo_comunal, p.comuna,
                           c.nombre as cargo, d.nombre as departamento
                    FROM empleados e
                    INNER JOIN personas p ON e.id_persona = p.id
                    INNER JOIN cargos c ON e.id_cargo = c.id
                    INNER JOIN departamentos d ON e.id_departamento = d.id
                    WHERE e.is_active = TRUE AND p.is_active = TRUE
                      AND e.fecha_egreso IS NULL
                    ORDER BY p.nombre ASC");
        return $db->resultSet();
    }

    /**
     * Empleados egresados (histórico): trabajaron en IMATUR y ya no.
     * Incluye fecha y motivo de egreso para mostrar el tiempo de servicio.
     */
    public static function egresados()
    {
        $db = new Database();
        $db->query("SELECT e.*,
                           p.cedula, p.nombre, p.apellido, p.telefono, p.correo,
                           c.nombre as cargo, d.nombre as departamento
                    FROM empleados e
                    INNER JOIN personas p ON e.id_persona = p.id
                    INNER JOIN cargos c ON e.id_cargo = c.id
                    INNER JOIN departamentos d ON e.id_departamento = d.id
                    WHERE e.is_active = TRUE AND p.is_active = TRUE
                      AND e.fecha_egreso IS NOT NULL
                    ORDER BY e.fecha_egreso DESC, p.nombre ASC");
        return $db->resultSet();
    }

    /**
     * Empleados que pueden ser facilitadores de talleres:
     * pertenecen a departamentos de Turismo o Formación, o tienen usuario con rol Admin.
     */
    public static function facilitadoresTalleres()
    {
        $db = new Database();
        $db->query("SELECT e.*, p.cedula, p.nombre, p.apellido, c.nombre as cargo, d.nombre as departamento
                    FROM empleados e
                    INNER JOIN personas p ON e.id_persona = p.id
                    INNER JOIN cargos c ON e.id_cargo = c.id
                    INNER JOIN departamentos d ON e.id_departamento = d.id
                    WHERE e.is_active = TRUE AND p.is_active = TRUE
                    AND e.fecha_egreso IS NULL
                    AND (
                        d.nombre ILIKE '%turismo%'
                        OR d.nombre ILIKE '%formaci%'
                        OR EXISTS (
                            SELECT 1 FROM usuarios u
                            WHERE u.id_empleado = e.id AND u.id_rol = 1 AND u.is_active = TRUE
                        )
                    )
                    ORDER BY p.nombre ASC");
        return $db->resultSet();
    }

    /**
     * Buscar un empleado por ID
     */
    public static function find($id)
    {
        $db = new Database();
        $db->query("SELECT e.*, p.*, e.id as id,
                           c.nombre AS cargo, d.nombre AS departamento,
                           par.nombre AS parroquia, h.nombre AS horario,
                           h.hora_entrada, h.hora_salida
                    FROM empleados e
                    INNER JOIN personas p   ON e.id_persona = p.id
                    LEFT JOIN cargos c       ON e.id_cargo = c.id
                    LEFT JOIN departamentos d ON e.id_departamento = d.id
                    LEFT JOIN parroquia par  ON p.parroquia_id = par.id
                    LEFT JOIN horarios h     ON e.id_horario = h.id
                    WHERE e.id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    /**
     * Guardar registro (Atómico: Persona + Empleado)
     */
    public function save($user_id = null)
    {
        $previos  = null;
        $prevId   = $this->id;
        $esNuevo  = empty($this->id);
        try {
            $this->db->beginTransaction();

            if ($this->id) {
                $previos = self::find($this->id);
                // UPDATE Persona
                $this->db->query("UPDATE personas SET cedula=:cedula, nombre=:nombre, apellido=:apellido, telefono=:telefono,
                                 correo=:correo, genero=:genero, fecha_nacimiento=:fecha_nacimiento, direccion=:direccion,
                                 parroquia_id=:parroquia_id, rif=:rif, estado_civil=:estado_civil,
                                 discapacidad=:discapacidad, discapacidad_detalle=:discapacidad_detalle,
                                 nivel_academico=:nivel_academico, profesion=:profesion, titulo=:titulo,
                                 fecha_graduacion=:fecha_graduacion, institucion_academica=:institucion_academica,
                                 centro_votacion=:centro_votacion, consejo_comunal=:consejo_comunal, comuna=:comuna,
                                 updated_at=CURRENT_TIMESTAMP, updated_by=:user_id WHERE id=:id_persona");
                $this->db->bind(':id_persona', $this->id_persona);
            } else {
                // INSERT Persona
                $this->db->query("INSERT INTO personas (cedula, nombre, apellido, telefono, correo, genero, fecha_nacimiento, direccion,
                                 parroquia_id, rif, estado_civil, discapacidad, discapacidad_detalle,
                                 nivel_academico, profesion, titulo, fecha_graduacion, institucion_academica,
                                 centro_votacion, consejo_comunal, comuna, created_by)
                                 VALUES (:cedula, :nombre, :apellido, :telefono, :correo, :genero, :fecha_nacimiento, :direccion,
                                 :parroquia_id, :rif, :estado_civil, :discapacidad, :discapacidad_detalle,
                                 :nivel_academico, :profesion, :titulo, :fecha_graduacion, :institucion_academica,
                                 :centro_votacion, :consejo_comunal, :comuna, :user_id) RETURNING id");
            }

            $this->db->bind(':cedula', $this->cedula);
            $this->db->bind(':nombre', $this->nombre);
            $this->db->bind(':apellido', $this->apellido);
            $this->db->bind(':telefono', $this->telefono);
            $this->db->bind(':correo', $this->correo);
            $this->db->bind(':genero', $this->genero);
            $this->db->bind(':fecha_nacimiento', $this->fecha_nacimiento);
            $this->db->bind(':direccion', $this->direccion);
            $this->db->bind(':parroquia_id', $this->parroquia_id);
            $this->db->bind(':rif', $this->rif);
            $this->db->bind(':estado_civil', $this->estado_civil);
            $this->db->bind(':discapacidad', $this->discapacidad, PDO::PARAM_BOOL);
            $this->db->bind(':discapacidad_detalle', $this->discapacidad_detalle);
            $this->db->bind(':nivel_academico', $this->nivel_academico);
            $this->db->bind(':profesion', $this->profesion);
            $this->db->bind(':titulo', $this->titulo);
            $this->db->bind(':fecha_graduacion', $this->fecha_graduacion);
            $this->db->bind(':institucion_academica', $this->institucion_academica);
            $this->db->bind(':centro_votacion', $this->centro_votacion);
            $this->db->bind(':consejo_comunal', $this->consejo_comunal);
            $this->db->bind(':comuna', $this->comuna);
            $this->db->bind(':user_id', $user_id);
            
            if (!$this->id) {
                $resPer = $this->db->single();
                if (!$resPer) throw new Exception("Error al insertar los datos personales.");
                $this->id_persona = $resPer->id;
            } else {
                $this->db->execute();
            }

            // --- EMPLEADO ---
            if ($this->id) {
                $this->db->query("UPDATE empleados
                                  SET id_cargo=:id_cargo, id_departamento=:id_departamento,
                                      nro_expediente=:nro_expediente, fecha_ingreso=:fecha_ingreso,
                                      tipo_contrato=:tipo_contrato, institucion_origen=:institucion_origen,
                                      es_comision_servicio=:es_comision_servicio, clasificacion=:clasificacion,
                                      grupo_rotacion=:grupo_rotacion, uniforme=:uniforme,
                                      talla_camisa=:talla_camisa, talla_pantalon=:talla_pantalon, talla_zapato=:talla_zapato,
                                      fecha_egreso=:fecha_egreso, id_horario=:id_horario,
                                      updated_at=CURRENT_TIMESTAMP, updated_by=:user_id
                                  WHERE id=:id");
                $this->db->bind(':id', $this->id);
            } else {
                $this->db->query("INSERT INTO empleados
                                  (id_persona, id_cargo, id_departamento, nro_expediente,
                                   fecha_ingreso, tipo_contrato, institucion_origen, es_comision_servicio,
                                   clasificacion, grupo_rotacion, uniforme, talla_camisa, talla_pantalon, talla_zapato,
                                   fecha_egreso, id_horario, created_by)
                                  VALUES (:id_persona, :id_cargo, :id_departamento, :nro_expediente,
                                          :fecha_ingreso, :tipo_contrato, :institucion_origen, :es_comision_servicio,
                                          :clasificacion, :grupo_rotacion, :uniforme, :talla_camisa, :talla_pantalon, :talla_zapato,
                                          :fecha_egreso, :id_horario, :user_id)
                                  RETURNING id");
                $this->db->bind(':id_persona', $this->id_persona);
            }

            $this->db->bind(':id_cargo',        $this->id_cargo);
            $this->db->bind(':id_departamento',  $this->id_departamento);
            $this->db->bind(':nro_expediente',   $this->nro_expediente);
            $this->db->bind(':fecha_ingreso',    $this->fecha_ingreso);
            $this->db->bind(':tipo_contrato',    $this->tipo_contrato);
            $this->db->bind(':institucion_origen',   $this->institucion_origen);
            $this->db->bind(':es_comision_servicio', $this->es_comision_servicio, PDO::PARAM_BOOL);
            $this->db->bind(':clasificacion',    $this->clasificacion);
            $this->db->bind(':grupo_rotacion',   $this->grupo_rotacion);
            $this->db->bind(':uniforme',         $this->uniforme, PDO::PARAM_BOOL);
            $this->db->bind(':talla_camisa',     $this->talla_camisa);
            $this->db->bind(':talla_pantalon',   $this->talla_pantalon);
            $this->db->bind(':talla_zapato',     $this->talla_zapato);
            $this->db->bind(':fecha_egreso',     $this->fecha_egreso);
            $this->db->bind(':id_horario',       $this->id_horario);
            $this->db->bind(':user_id', $user_id);

            if (!$this->id) {
                $resEmp = $this->db->single();
                if (!$resEmp) throw new Exception("Error al instanciar el perfil del empleado.");
                $prevId = $resEmp->id;
                $this->id = $resEmp->id;
            } else {
                $this->db->execute();
            }

            $this->db->endTransaction();

            // Auditoría automática
            $operacion = $esNuevo ? 'INSERT' : 'UPDATE';
            $this->audit('empleados', $operacion, $this->id ?? $prevId, $previos, [
                'cedula' => $this->cedula,
                'nombre' => $this->nombre,
                'apellido' => $this->apellido,
                'id_cargo' => $this->id_cargo,
                'id_departamento' => $this->id_departamento
            ], $user_id);

            return true;
        } catch (Exception $e) {
            $this->db->cancelTransaction();
            return false;
        }
    }

    /**
     * Borrado lógico (Solo marca el empleado como inactivo)
     */
    public static function delete($id, $user_id = null)
    {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE empleados SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:user_id WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        $result = $db->execute();
        self::auditStatic('empleados', 'DELETE', $id, $previos, null, $user_id);
        return $result;
    }

    // ── Egreso / desincorporación (R-12, mig. 036) ─────────────────────────────

    /**
     * Procesa el egreso (baja) de un empleado: marca fecha y motivo en el
     * registro y deja constancia en el historial (empleados_egresos).
     * El registro permanece is_active = TRUE (histórico válido, no papelera).
     */
    public static function procesarEgreso($id, $fecha, $motivo, $observacion = null, $user_id = null)
    {
        if (empty($id) || empty($fecha)) throw new Exception("Empleado y fecha de egreso son obligatorios.");
        if (!in_array($motivo, self::MOTIVOS_EGRESO, true)) throw new Exception("Motivo de egreso inválido.");

        $previos = self::find($id);
        if (!$previos) throw new Exception("El empleado no existe.");
        if (!empty($previos->fecha_egreso)) throw new Exception("El empleado ya se encuentra egresado.");
        if (!empty($previos->fecha_ingreso) && $fecha < $previos->fecha_ingreso) {
            throw new Exception("La fecha de egreso no puede ser anterior a la fecha de ingreso.");
        }

        $db = new Database();
        $db->beginTransaction();
        try {
            $db->query("UPDATE empleados
                        SET fecha_egreso = :f, motivo_egreso = :m, observacion_egreso = :o,
                            updated_at = CURRENT_TIMESTAMP, updated_by = :uid
                        WHERE id = :id");
            $db->bind(':f', $fecha);
            $db->bind(':m', $motivo);
            $db->bind(':o', $observacion ?: null);
            $db->bind(':uid', $user_id);
            $db->bind(':id', $id);
            $db->execute();

            $db->query("INSERT INTO empleados_egresos (id_empleado, fecha_egreso, motivo_egreso, observacion, created_by)
                        VALUES (:id, :f, :m, :o, :uid)");
            $db->bind(':id', $id);
            $db->bind(':f', $fecha);
            $db->bind(':m', $motivo);
            $db->bind(':o', $observacion ?: null);
            $db->bind(':uid', $user_id);
            $db->execute();

            $db->endTransaction();
        } catch (Exception $e) {
            $db->cancelTransaction();
            throw $e;
        }

        self::auditStatic('empleados', 'UPDATE', $id, $previos,
            ['accion' => 'EGRESO', 'fecha_egreso' => $fecha, 'motivo_egreso' => $motivo], $user_id);
        return true;
    }

    /**
     * Reingreso de un ex-empleado: limpia el egreso vigente y cierra la fila
     * abierta del historial (conserva el egreso anterior — reingreso con historial).
     */
    public static function reingresar($id, $observacion = null, $user_id = null)
    {
        $previos = self::find($id);
        if (!$previos) throw new Exception("El empleado no existe.");
        if (empty($previos->fecha_egreso)) throw new Exception("El empleado no está egresado.");

        $db = new Database();
        $db->beginTransaction();
        try {
            $db->query("UPDATE empleados_egresos
                        SET fecha_reingreso = CURRENT_DATE, reingreso_observacion = :o,
                            reingreso_at = CURRENT_TIMESTAMP, reingreso_by = :uid
                        WHERE id_empleado = :id AND fecha_reingreso IS NULL");
            $db->bind(':o', $observacion ?: null);
            $db->bind(':uid', $user_id);
            $db->bind(':id', $id);
            $db->execute();

            $db->query("UPDATE empleados
                        SET fecha_egreso = NULL, motivo_egreso = NULL, observacion_egreso = NULL,
                            updated_at = CURRENT_TIMESTAMP, updated_by = :uid
                        WHERE id = :id");
            $db->bind(':uid', $user_id);
            $db->bind(':id', $id);
            $db->execute();

            $db->endTransaction();
        } catch (Exception $e) {
            $db->cancelTransaction();
            throw $e;
        }

        self::auditStatic('empleados', 'UPDATE', $id, $previos, ['accion' => 'REINGRESO'], $user_id);
        return true;
    }

    /** Historial de egresos/reingresos de un empleado (más reciente primero). */
    public static function historialEgresos($id)
    {
        $db = new Database();
        $db->query("SELECT * FROM empleados_egresos WHERE id_empleado = :id ORDER BY fecha_egreso DESC, id DESC");
        $db->bind(':id', $id);
        return $db->resultSet();
    }

    /**
     * Tiempo de servicio formateado (años, meses) entre ingreso y egreso.
     * Si no hay egreso, calcula hasta la fecha actual.
     */
    public static function tiempoServicio($fechaIngreso, $fechaEgreso = null): string
    {
        if (empty($fechaIngreso)) return '—';
        try {
            $ini = new DateTime($fechaIngreso);
            $fin = !empty($fechaEgreso) ? new DateTime($fechaEgreso) : new DateTime();
        } catch (Exception $e) {
            return '—';
        }
        if ($fin < $ini) return '—';
        $d = $ini->diff($fin);
        $partes = [];
        if ($d->y) $partes[] = $d->y . ' año' . ($d->y > 1 ? 's' : '');
        if ($d->m) $partes[] = $d->m . ' mes' . ($d->m > 1 ? 'es' : '');
        if (empty($partes)) $partes[] = $d->d . ' día' . ($d->d != 1 ? 's' : '');
        return implode(', ', $partes);
    }
}
