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
    // Niveles académicos sugeridos para la ficha técnica (de menor a mayor).
    // `personas.nivel_academico` es varchar libre (sin CHECK): el select sugiere
    // estos valores, pero se conserva cualquier otro ya guardado.
    const NIVELES_ACADEMICOS = [
        'Primaria',
        '1er año', '2do año', '3er año', '4to año',
        'Bachiller',
        'Técnico Medio',
        'TSU',
        'Licenciado',
        'Ingeniero',
        'Especialización',
        'Magíster',
        'Doctorado',
    ];
    // Grupo de rotación (solo Servicios Generales) — mig.028
    const GRUPOS_ROTACION = ['A', 'B'];
    // Motivos de egreso / desincorporación (mig. 036 — R-12)
    const MOTIVOS_EGRESO = ['Renuncia', 'Despido', 'Jubilación', 'Fin de contrato', 'Fallecimiento', 'Otro'];

    // Umbral (años de servicio) para SEÑALAR elegibilidad a Fijo, por origen (3C).
    // Rangos del negocio: Alcaldía 5-6, Gobernación 3-6, IMATUR 5-6 → se marca al
    // alcanzar el mínimo. NO promueve automáticamente: solo es un indicador visual
    // (la decisión final es de Presidencia y requiere carta de asignación).
    const UMBRAL_FIJO = ['IMATUR' => 5, 'Alcaldía' => 5, 'Gobernación' => 3];

    /** Años de servicio (antigüedad total: usa fecha_ingreso_administracion si existe). */
    public static function aniosServicio($empleado): int {
        $base = !empty($empleado->fecha_ingreso_administracion)
            ? $empleado->fecha_ingreso_administracion
            : ($empleado->fecha_ingreso ?? null);
        if (!$base) return 0;
        try { $ini = new \DateTime($base); $hoy = new \DateTime('today'); }
        catch (\Exception $e) { return 0; }
        if ($hoy < $ini) return 0;
        return (int)$ini->diff($hoy)->y;
    }

    /** ¿Contratado activo con tiempo suficiente para considerarse Fijo? (3C, solo señal). */
    public static function elegibleParaFijo($empleado): bool {
        if (($empleado->tipo_contrato ?? '') !== 'Contratado') return false;
        if (!empty($empleado->fecha_egreso)) return false;
        $umbral = self::UMBRAL_FIJO[$empleado->institucion_origen ?? 'IMATUR'] ?? 5;
        return self::aniosServicio($empleado) >= $umbral;
    }

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
    private ?string $fecha_vencimiento_contrato;
    private ?string $fecha_ingreso_administracion;
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
            $this->fecha_vencimiento_contrato = !empty($data['fecha_vencimiento_contrato']) ? $data['fecha_vencimiento_contrato'] : null;
            $this->fecha_ingreso_administracion = !empty($data['fecha_ingreso_administracion']) ? $data['fecha_ingreso_administracion'] : null;
            $this->id_horario    = !empty($data['id_horario'])    ? (int)$data['id_horario'] : null;
        }
    }

    /**
     * Obtener listado completo de empleados con joins
     */
    public function getId() { return $this->id; }
    public function getIdPersona() { return $this->id_persona; }
    public function getNroExpediente() { return $this->nro_expediente; }

    /** Formato del folio de expediente, derivado del id del empleado (permanente). */
    public static function formatoFolio($id): string
    {
        return 'EXP-' . str_pad((string)(int)$id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Próximo folio de expediente estimado (solo para preview en la UI de alta).
     * El valor autoritativo se asigna en save() a partir del id real.
     */
    public static function proximoNumeroExpediente(): string
    {
        $db = new Database();
        $db->query("SELECT COALESCE(MAX(id), 0) + 1 AS prox FROM empleados");
        $row = $db->single();
        return self::formatoFolio($row->prox ?? 1);
    }

    /**
     * Construye el filtro por origen/comisión de servicio.
     * $origen: 'comision' (Alcaldía/Gobernación), 'IMATUR', 'Alcaldía', 'Gobernación' o '' (todos).
     * Devuelve el fragmento SQL; si usa bind, lo agrega a $binds (':origen').
     */
    private static function filtroOrigen(string $origen, array &$binds): string
    {
        if ($origen === 'comision') return " AND e.institucion_origen <> 'IMATUR'";
        if (in_array($origen, self::INSTITUCIONES_ORIGEN, true)) { $binds[':origen'] = $origen; return " AND e.institucion_origen = :origen"; }
        return '';
    }

    public static function all(string $origen = '')
    {
        $db = new Database();
        $binds = [];
        $cond = self::filtroOrigen($origen, $binds);
        $db->query("SELECT e.*,
                           p.cedula, p.nombre, p.apellido, p.telefono, p.correo, p.genero,
                           p.fecha_nacimiento, p.direccion, p.parroquia_id, p.rif, p.estado_civil,
                           p.discapacidad, p.discapacidad_detalle, p.nivel_academico, p.profesion,
                           p.fecha_graduacion, p.institucion_academica,
                           p.centro_votacion, p.consejo_comunal, p.comuna,
                           c.nombre as cargo, d.nombre as departamento,
                           COALESCE(am.total, 0) AS amonestaciones, COALESCE(fa.total, 0) AS faltas
                    FROM empleados e
                    INNER JOIN personas p ON e.id_persona = p.id
                    INNER JOIN cargos c ON e.id_cargo = c.id
                    INNER JOIN departamentos d ON e.id_departamento = d.id
                    LEFT JOIN (SELECT id_empleado, COUNT(*) total FROM amonestaciones WHERE is_active = TRUE GROUP BY id_empleado) am ON am.id_empleado = e.id
                    LEFT JOIN (SELECT id_empleado, COUNT(*) total FROM faltas WHERE is_active = TRUE GROUP BY id_empleado) fa ON fa.id_empleado = e.id
                    WHERE e.is_active = TRUE AND p.is_active = TRUE
                      AND e.fecha_egreso IS NULL {$cond}
                    ORDER BY p.nombre ASC");
        foreach ($binds as $k => $v) $db->bind($k, $v);
        return $db->resultSet();
    }

    /**
     * Empleados egresados (histórico): trabajaron en IMATUR y ya no.
     * Incluye fecha y motivo de egreso para mostrar el tiempo de servicio.
     */
    public static function egresados(string $origen = '')
    {
        $db = new Database();
        $binds = [];
        $cond = self::filtroOrigen($origen, $binds);
        $db->query("SELECT e.*,
                           p.cedula, p.nombre, p.apellido, p.telefono, p.correo,
                           c.nombre as cargo, d.nombre as departamento
                    FROM empleados e
                    INNER JOIN personas p ON e.id_persona = p.id
                    INNER JOIN cargos c ON e.id_cargo = c.id
                    INNER JOIN departamentos d ON e.id_departamento = d.id
                    WHERE e.is_active = TRUE AND p.is_active = TRUE
                      AND e.fecha_egreso IS NOT NULL {$cond}
                    ORDER BY e.fecha_egreso DESC, p.nombre ASC");
        foreach ($binds as $k => $v) $db->bind($k, $v);
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
     * ¿Ya existe un empleado (activo o egresado, no en papelera) con esta cédula?
     * Compara solo los dígitos (la cédula se normaliza a números — mig.037).
     * $excluirId permite ignorar al propio registro en edición.
     */
    public static function existeCedula($cedula, $excluirId = null): bool
    {
        $ced = preg_replace('/\D/', '', (string)$cedula);
        if ($ced === '') return false;
        $db = new Database();
        $sql = "SELECT e.id FROM empleados e
                INNER JOIN personas p ON e.id_persona = p.id
                WHERE e.is_active = TRUE
                  AND regexp_replace(COALESCE(p.cedula, ''), '[^0-9]', '', 'g') = :ced";
        if ($excluirId) $sql .= " AND e.id <> :id";
        $sql .= " LIMIT 1";
        $db->query($sql);
        $db->bind(':ced', $ced);
        if ($excluirId) $db->bind(':id', (int)$excluirId);
        return (bool) $db->single();
    }

    /**
     * Anti-duplicado de correo entre empleados activos (comparación insensible
     * a mayúsculas). Un correo repetido entre dos personas rompe silenciosamente
     * la recuperación de contraseña por correo (Usuario::findByUsernameOrEmail()
     * trata el correo ambiguo como "no encontrado", por diseño anti-enumeración).
     */
    public static function existeCorreo($correo, $excluirId = null): bool
    {
        $correo = trim((string)$correo);
        if ($correo === '') return false;
        $db = new Database();
        $sql = "SELECT e.id FROM empleados e
                INNER JOIN personas p ON e.id_persona = p.id
                WHERE e.is_active = TRUE
                  AND LOWER(COALESCE(p.correo, '')) = LOWER(:correo)";
        if ($excluirId) $sql .= " AND e.id <> :id";
        $sql .= " LIMIT 1";
        $db->query($sql);
        $db->bind(':correo', $correo);
        if ($excluirId) $db->bind(':id', (int)$excluirId);
        return (bool) $db->single();
    }

    /**
     * Buscar un empleado por ID
     */
    public static function find($id)
    {
        $db = new Database();
        $db->query("SELECT e.*, p.*, e.id as id,
                           c.nombre AS cargo, c.nivel_jerarquico AS nivel_cargo, d.nombre AS departamento,
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
                                 nivel_academico=:nivel_academico, profesion=:profesion,
                                 fecha_graduacion=:fecha_graduacion, institucion_academica=:institucion_academica,
                                 centro_votacion=:centro_votacion, consejo_comunal=:consejo_comunal, comuna=:comuna,
                                 updated_at=CURRENT_TIMESTAMP, updated_by=:user_id WHERE id=:id_persona");
                $this->db->bind(':id_persona', $this->id_persona);
            } else {
                // INSERT Persona
                $this->db->query("INSERT INTO personas (cedula, nombre, apellido, telefono, correo, genero, fecha_nacimiento, direccion,
                                 parroquia_id, rif, estado_civil, discapacidad, discapacidad_detalle,
                                 nivel_academico, profesion, fecha_graduacion, institucion_academica,
                                 centro_votacion, consejo_comunal, comuna, created_by)
                                 VALUES (:cedula, :nombre, :apellido, :telefono, :correo, :genero, :fecha_nacimiento, :direccion,
                                 :parroquia_id, :rif, :estado_civil, :discapacidad, :discapacidad_detalle,
                                 :nivel_academico, :profesion, :fecha_graduacion, :institucion_academica,
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
                // nro_expediente NO se modifica en edición: es un folio permanente.
                $this->db->query("UPDATE empleados
                                  SET id_cargo=:id_cargo, id_departamento=:id_departamento,
                                      fecha_ingreso=:fecha_ingreso,
                                      tipo_contrato=:tipo_contrato, institucion_origen=:institucion_origen,
                                      es_comision_servicio=:es_comision_servicio, clasificacion=:clasificacion,
                                      grupo_rotacion=:grupo_rotacion, uniforme=:uniforme,
                                      talla_camisa=:talla_camisa, talla_pantalon=:talla_pantalon, talla_zapato=:talla_zapato,
                                      fecha_vencimiento_contrato=:fecha_vencimiento_contrato,
                                      fecha_ingreso_administracion=:fecha_ingreso_administracion, id_horario=:id_horario,
                                      updated_at=CURRENT_TIMESTAMP, updated_by=:user_id
                                  WHERE id=:id");
                // Nota: fecha_egreso NO se toca aquí; la gestiona el módulo de egreso (R-12).
                $this->db->bind(':id', $this->id);
            } else {
                // El folio (nro_expediente) se asigna después del INSERT, derivado del id real.
                $this->db->query("INSERT INTO empleados
                                  (id_persona, id_cargo, id_departamento,
                                   fecha_ingreso, tipo_contrato, institucion_origen, es_comision_servicio,
                                   clasificacion, grupo_rotacion, uniforme, talla_camisa, talla_pantalon, talla_zapato,
                                   fecha_vencimiento_contrato, fecha_ingreso_administracion, id_horario, created_by)
                                  VALUES (:id_persona, :id_cargo, :id_departamento,
                                          :fecha_ingreso, :tipo_contrato, :institucion_origen, :es_comision_servicio,
                                          :clasificacion, :grupo_rotacion, :uniforme, :talla_camisa, :talla_pantalon, :talla_zapato,
                                          :fecha_vencimiento_contrato, :fecha_ingreso_administracion, :id_horario, :user_id)
                                  RETURNING id");
                $this->db->bind(':id_persona', $this->id_persona);
            }

            $this->db->bind(':id_cargo',        $this->id_cargo);
            $this->db->bind(':id_departamento',  $this->id_departamento);
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
            $this->db->bind(':fecha_vencimiento_contrato', $this->fecha_vencimiento_contrato);
            $this->db->bind(':fecha_ingreso_administracion', $this->fecha_ingreso_administracion);
            $this->db->bind(':id_horario',       $this->id_horario);
            $this->db->bind(':user_id', $user_id);

            if (!$this->id) {
                $resEmp = $this->db->single();
                if (!$resEmp) throw new Exception("Error al instanciar el perfil del empleado.");
                $prevId = $resEmp->id;
                $this->id = $resEmp->id;

                // Folio de expediente automático: 'EXP-####' derivado del id (permanente, único).
                $folio = self::formatoFolio($this->id);
                $this->db->query("UPDATE empleados SET nro_expediente = :folio WHERE id = :id");
                $this->db->bind(':folio', $folio);
                $this->db->bind(':id', $this->id);
                $this->db->execute();
                $this->nro_expediente = $folio;
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

            // Desactiva la cuenta de acceso del empleado egresado (si tiene una
            // activa) — evita cuentas huérfanas con acceso tras el egreso.
            $db->query("SELECT id FROM usuarios WHERE id_empleado = :id AND is_active = TRUE");
            $db->bind(':id', $id);
            $usuario = $db->single();
            if ($usuario) {
                $db->query("UPDATE usuarios SET is_active = FALSE, updated_at = CURRENT_TIMESTAMP, updated_by = :uid WHERE id = :uidu");
                $db->bind(':uid', $user_id);
                $db->bind(':uidu', $usuario->id);
                $db->execute();
                self::auditStatic('usuarios', 'UPDATE', (int)$usuario->id, ['is_active' => true],
                    ['is_active' => false, 'motivo' => 'Egreso del empleado'], $user_id);
            }

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

            // Reactiva la cuenta de acceso que había quedado desactivada por el
            // egreso (si tiene una), con los intentos fallidos limpios.
            $db->query("SELECT id FROM usuarios WHERE id_empleado = :id AND is_active = FALSE");
            $db->bind(':id', $id);
            $usuario = $db->single();
            if ($usuario) {
                $db->query("UPDATE usuarios SET is_active = TRUE, failed_attempts = 0, locked_until = NULL,
                                updated_at = CURRENT_TIMESTAMP, updated_by = :uid WHERE id = :uidu");
                $db->bind(':uid', $user_id);
                $db->bind(':uidu', $usuario->id);
                $db->execute();
                self::auditStatic('usuarios', 'UPDATE', (int)$usuario->id, ['is_active' => false],
                    ['is_active' => true, 'motivo' => 'Reingreso del empleado'], $user_id);
            }

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

    /** Historial de traslados de departamento del empleado (3D). */
    public static function historialTraslados($id): array
    {
        $db = new Database();
        $db->query("SELECT t.*, dor.nombre AS depto_origen, dde.nombre AS depto_destino,
                           cor.nombre AS cargo_origen, cde.nombre AS cargo_destino
                    FROM empleado_traslados t
                    LEFT JOIN departamentos dor ON t.id_departamento_origen  = dor.id
                    LEFT JOIN departamentos dde ON t.id_departamento_destino = dde.id
                    LEFT JOIN cargos cor ON t.id_cargo_origen  = cor.id
                    LEFT JOIN cargos cde ON t.id_cargo_destino = cde.id
                    WHERE t.id_empleado = :id AND t.is_active = TRUE
                    ORDER BY t.fecha DESC, t.id DESC");
        $db->bind(':id', $id);
        return $db->resultSet();
    }

    /**
     * Traslada al empleado a otro departamento (y opcionalmente otro cargo),
     * registrando el movimiento en el historial. Transaccional (3D / O3).
     */
    public static function trasladar(int $id, int $deptoDestino, ?int $cargoDestino, string $fecha, ?string $motivo, ?string $obs, $user_id = null): bool
    {
        $emp = self::find($id);
        if (!$emp) throw new Exception('Empleado no encontrado.');
        if ($deptoDestino < 1) throw new Exception('Departamento destino no válido.');
        $deptoOrigen = (int)$emp->id_departamento;
        $cargoOrigen = (int)$emp->id_cargo;
        $cargoDest   = $cargoDestino ?: $cargoOrigen;
        if ($deptoDestino === $deptoOrigen && $cargoDest === $cargoOrigen) {
            throw new Exception('El destino coincide con el departamento y cargo actuales.');
        }
        $db = new Database();
        $db->beginTransaction();
        try {
            $db->query("INSERT INTO empleado_traslados
                        (id_empleado, id_departamento_origen, id_departamento_destino, id_cargo_origen, id_cargo_destino, fecha, motivo, observacion, created_by)
                        VALUES (:e, :do_, :dd, :co, :cd, :f, :m, :o, :u)");
            $db->bind(':e', $id);    $db->bind(':do_', $deptoOrigen); $db->bind(':dd', $deptoDestino);
            $db->bind(':co', $cargoOrigen); $db->bind(':cd', $cargoDest);
            $db->bind(':f', $fecha); $db->bind(':m', $motivo); $db->bind(':o', $obs); $db->bind(':u', $user_id);
            $db->execute();

            $db->query("UPDATE empleados SET id_departamento = :dd, id_cargo = :cd, updated_at = CURRENT_TIMESTAMP, updated_by = :u WHERE id = :id");
            $db->bind(':dd', $deptoDestino); $db->bind(':cd', $cargoDest); $db->bind(':u', $user_id); $db->bind(':id', $id);
            $db->execute();

            $db->endTransaction();
        } catch (Exception $e) {
            $db->cancelTransaction();
            throw $e;
        }
        self::auditStatic('empleados', 'UPDATE', $id,
            ['id_departamento' => $deptoOrigen, 'id_cargo' => $cargoOrigen],
            ['id_departamento' => $deptoDestino, 'id_cargo' => $cargoDest], $user_id);
        return true;
    }

    /**
     * Tiempo de servicio en MESES completos entre ingreso y egreso (o hasta hoy).
     * Devuelve null si no hay fecha de ingreso o el rango es inválido.
     */
    public static function mesesServicio($fechaIngreso, $fechaEgreso = null): ?int
    {
        if (empty($fechaIngreso)) return null;
        try {
            $ini = new DateTime($fechaIngreso);
            $fin = !empty($fechaEgreso) ? new DateTime($fechaEgreso) : new DateTime();
        } catch (Exception $e) {
            return null;
        }
        if ($fin < $ini) return null;
        $d = $ini->diff($fin);
        return $d->y * 12 + $d->m;
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

    /**
     * Datos que el cálculo de nómina necesita y no estaban en la ficha (mig. 072):
     * cuenta bancaria de nómina, divisas del bono de responsabilidad, sueldo que
     * paga la dependencia de origen (comisión de servicio) y la corrección
     * manual del código de grado de instrucción.
     *
     * A diferencia de `Sueldo::guardar()`, esto NO es append-only: son datos
     * corrientes de la ficha, no un histórico salarial. Toca `empleados` y
     * `personas`, así que va transaccional.
     */
    public static function guardarDatosNomina(int $id, array $datos, $user_id = null): bool
    {
        $emp = self::find($id);
        if (!$emp) throw new Exception('Empleado no encontrado.');

        $cuenta  = trim((string)($datos['cuenta_nomina'] ?? ''));
        $banco   = trim((string)($datos['banco_nomina'] ?? ''));
        $divisas = round((float)($datos['divisas_bono_responsabilidad'] ?? 0), 2);
        $origen  = round((float)($datos['sueldo_dependencia_origen'] ?? 0), 2);
        $grado   = strtoupper(trim((string)($datos['codigo_grado'] ?? '')));

        if ($divisas < 0 || $origen < 0) throw new Exception('Los montos no pueden ser negativos.');
        // La cuenta bancaria venezolana son 20 dígitos; se acepta con o sin guiones.
        if ($cuenta !== '' && !preg_match('/^[0-9\- ]{10,30}$/', $cuenta)) {
            throw new Exception('La cuenta de nómina solo admite números (y guiones o espacios como separadores).');
        }
        if ($grado !== '' && !array_key_exists($grado, Nomina::grados())) {
            throw new Exception('El código de grado de instrucción no es válido.');
        }

        $previos = ['cuenta_nomina' => $emp->cuenta_nomina ?? null, 'banco_nomina' => $emp->banco_nomina ?? null,
                    'divisas_bono_responsabilidad' => $emp->divisas_bono_responsabilidad ?? null,
                    'sueldo_dependencia_origen' => $emp->sueldo_dependencia_origen ?? null,
                    'codigo_grado' => $emp->codigo_grado ?? null];

        $db = new Database();
        $db->beginTransaction();
        try {
            $db->query("UPDATE empleados
                           SET cuenta_nomina = :cta, banco_nomina = :banco,
                               divisas_bono_responsabilidad = :div, sueldo_dependencia_origen = :orig,
                               updated_at = CURRENT_TIMESTAMP, updated_by = :uid
                         WHERE id = :id");
            $db->bind(':cta', $cuenta !== '' ? $cuenta : null);
            $db->bind(':banco', $banco !== '' ? $banco : null);
            $db->bind(':div', $divisas);
            $db->bind(':orig', $origen);
            $db->bind(':uid', $user_id);
            $db->bind(':id', $id);
            $db->execute();

            $db->query("UPDATE personas SET codigo_grado = :g, updated_at = CURRENT_TIMESTAMP, updated_by = :uid WHERE id = :idp");
            $db->bind(':g', $grado !== '' ? $grado : null);
            $db->bind(':uid', $user_id);
            $db->bind(':idp', (int)$emp->id_persona);
            $db->execute();

            $db->endTransaction();
        } catch (Exception $e) {
            $db->cancelTransaction();
            throw $e;
        }

        self::auditStatic('empleados', 'UPDATE', $id, $previos,
            ['cuenta_nomina' => $cuenta, 'banco_nomina' => $banco,
             'divisas_bono_responsabilidad' => $divisas, 'sueldo_dependencia_origen' => $origen,
             'codigo_grado' => $grado], $user_id);
        return true;
    }
}
