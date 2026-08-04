<?php
class Ruta extends Model {
    private ?int   $id;
    private string $nombre;
    private string $descripcion;
    private string $duracion_estimada;
    private string $estado;
    private ?string $fecha_visita;
    private ?string $hora_visita;
    private ?int    $id_departamento;
    private ?int    $id_facilitador;
    private int     $cupo_maximo;
    private bool    $requiere_formacion;
    private string  $tipo_ruta;
    private ?string $motivo_mantenimiento;

    // ── Fuente única de verdad para enums de este módulo ─────────────────────
    const ESTADOS        = ['Activa', 'Inactiva', 'En Mantenimiento', 'Finalizada'];
    const ESTADO_TERMINAL= 'Finalizada';
    /** CSS class por estado (para vistas) */
    const ESTADO_BADGES  = [
        'Activa'           => 'sig-badge--success',
        'Inactiva'         => 'sig-badge--danger',
        'En Mantenimiento' => 'sig-badge--warning',
        'Finalizada'       => 'sig-badge--brand',
    ];

    public static array $TIPOS_RUTA = ['Cumaná Histórica', 'Exploradores de Cumaná', 'Comunitaria', 'General'];

    public function __construct(array $data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id                  = $data['id'] ?? null;
            $this->nombre              = $data['nombre'] ?? '';
            $this->descripcion         = $data['descripcion'] ?? '';
            $this->duracion_estimada   = $data['duracion_estimada'] ?? '';
            $this->estado              = $data['estado'] ?? 'Activa';
            $this->fecha_visita        = $data['fecha_visita'] ?: null;
            $this->hora_visita         = $data['hora_visita'] ?: null;
            $this->id_departamento     = $data['id_departamento'] ? (int)$data['id_departamento'] : null;
            $this->id_facilitador      = $data['id_facilitador'] ? (int)$data['id_facilitador'] : null;
            $this->cupo_maximo         = isset($data['cupo_maximo']) ? (int)$data['cupo_maximo'] : 20;
            $this->requiere_formacion  = !empty($data['requiere_formacion']);
            $this->tipo_ruta           = in_array($data['tipo_ruta'] ?? '', self::$TIPOS_RUTA)
                                         ? $data['tipo_ruta'] : 'General';
            $this->motivo_mantenimiento = $data['motivo_mantenimiento'] ?? null;
        }
    }

    public static function all() {
        $db = new Database();
        $db->query("SELECT r.*,
                           d.nombre AS departamento_nombre,
                           p.nombre AS facilitador_nombre,
                           p.apellido AS facilitador_apellido,
                           (SELECT COUNT(*) FROM puntos_ruta pr
                            WHERE pr.id_ruta = r.id AND pr.is_active = TRUE) AS total_puntos,
                           (SELECT COUNT(*) FROM participantes_ruta prt
                            WHERE prt.id_ruta = r.id AND prt.is_active = TRUE) AS total_participantes
                    FROM rutas r
                    LEFT JOIN departamentos d  ON r.id_departamento = d.id
                    LEFT JOIN empleados     e  ON r.id_facilitador  = e.id
                    LEFT JOIN personas      p  ON e.id_persona      = p.id
                    WHERE r.is_active = TRUE
                    ORDER BY r.nombre ASC");
        return $db->resultSet();
    }

    /**
     * Rutas paginadas en servidor, con búsqueda (nombre/descripción/facilitador),
     * filtro por estado, tipo y rango de fecha de visita. ['items'=>[], 'total'=>n].
     */
    public static function paginate(int $pagina, int $porPagina, array $f = []): array {
        $db    = new Database();
        $binds = [];
        $where = "r.is_active = TRUE";

        if (!empty($f['buscar'])) {
            $where .= " AND (r.nombre ILIKE :q OR r.descripcion ILIKE :q OR (p.nombre||' '||p.apellido) ILIKE :q)";
            $binds[':q'] = '%' . $f['buscar'] . '%';
        }
        if (!empty($f['estado']))     { $where .= " AND r.estado = :estado";    $binds[':estado'] = $f['estado']; }
        if (!empty($f['tipo']))       { $where .= " AND r.tipo_ruta = :tipo";    $binds[':tipo']   = $f['tipo']; }
        if (!empty($f['fecha_desde'])){ $where .= " AND r.fecha_visita >= :fd";  $binds[':fd']     = $f['fecha_desde']; }
        if (!empty($f['fecha_hasta'])){ $where .= " AND r.fecha_visita <= :fh";  $binds[':fh']     = $f['fecha_hasta']; }
        // Filtro rápido por período relativo a hoy (U3)
        switch ($f['periodo'] ?? '') {
            case 'proximos': $where .= " AND r.fecha_visita >= CURRENT_DATE"; break;
            case 'hoy':      $where .= " AND r.fecha_visita = CURRENT_DATE"; break;
            case 'semana':   $where .= " AND r.fecha_visita BETWEEN CURRENT_DATE AND (CURRENT_DATE + INTERVAL '7 days')"; break;
            case 'mes':      $where .= " AND date_trunc('month', r.fecha_visita) = date_trunc('month', CURRENT_DATE)"; break;
            case 'pasados':  $where .= " AND r.fecha_visita < CURRENT_DATE"; break;
        }

        $base = "FROM rutas r
                 LEFT JOIN departamentos d ON r.id_departamento = d.id
                 LEFT JOIN empleados     e ON r.id_facilitador  = e.id
                 LEFT JOIN personas      p ON e.id_persona       = p.id
                 WHERE {$where}";

        $db->query("SELECT COUNT(*) AS total {$base}");
        foreach ($binds as $k => $v) $db->bind($k, $v);
        $total = (int)($db->single()->total ?? 0);

        $offset = ($pagina - 1) * $porPagina;
        $db->query("SELECT r.*,
                           d.nombre AS departamento_nombre,
                           p.nombre AS facilitador_nombre, p.apellido AS facilitador_apellido,
                           (SELECT COUNT(*) FROM puntos_ruta pr WHERE pr.id_ruta = r.id AND pr.is_active = TRUE) AS total_puntos,
                           (SELECT COUNT(*) FROM participantes_ruta prt WHERE prt.id_ruta = r.id AND prt.is_active = TRUE) AS total_participantes
                    {$base}
                    ORDER BY
                        CASE r.estado WHEN 'Activa' THEN 0 WHEN 'En Mantenimiento' THEN 1 WHEN 'Inactiva' THEN 2 ELSE 3 END,
                        r.fecha_visita DESC NULLS LAST, r.nombre ASC
                    LIMIT :lim OFFSET :off");
        foreach ($binds as $k => $v) $db->bind($k, $v);
        $db->bind(':lim', $porPagina);
        $db->bind(':off', $offset);

        return ['items' => $db->resultSet(), 'total' => $total];
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT r.*,
                           d.nombre AS departamento_nombre,
                           p.nombre AS facilitador_nombre,
                           p.apellido AS facilitador_apellido
                    FROM rutas r
                    LEFT JOIN departamentos d ON r.id_departamento = d.id
                    LEFT JOIN empleados     e ON r.id_facilitador  = e.id
                    LEFT JOIN personas      p ON e.id_persona      = p.id
                    WHERE r.id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    public function save($user_id = null) {
        $previos = null;
        if ($this->id) {
            $previos = self::find($this->id);
            $this->db->query("UPDATE rutas
                              SET nombre=:nombre, descripcion=:descripcion,
                                  duracion_estimada=:duracion_estimada, estado=:estado,
                                  fecha_visita=:fecha_visita, hora_visita=:hora_visita,
                                  id_departamento=:id_departamento,
                                  id_facilitador=:id_facilitador,
                                  cupo_maximo=:cupo_maximo,
                                  requiere_formacion=:requiere_formacion,
                                  tipo_ruta=:tipo_ruta,
                                  motivo_mantenimiento=:motivo_mant,
                                  updated_at=CURRENT_TIMESTAMP, updated_by=:user_id
                              WHERE id=:id");
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO rutas
                              (nombre, descripcion, duracion_estimada, estado,
                               fecha_visita, hora_visita, id_departamento, id_facilitador,
                               cupo_maximo, requiere_formacion, tipo_ruta, created_by)
                              VALUES (:nombre, :descripcion, :duracion_estimada,
                                      :estado, :fecha_visita, :hora_visita, :id_departamento,
                                      :id_facilitador, :cupo_maximo, :requiere_formacion,
                                      :tipo_ruta, :user_id)");
        }
        $this->db->bind(':nombre',             $this->nombre);
        $this->db->bind(':descripcion',        $this->descripcion);
        $this->db->bind(':duracion_estimada',  $this->duracion_estimada);
        $this->db->bind(':estado',             $this->estado);
        $this->db->bind(':fecha_visita',       $this->fecha_visita);
        $this->db->bind(':hora_visita',        $this->hora_visita);
        $this->db->bind(':id_departamento',    $this->id_departamento);
        $this->db->bind(':id_facilitador',     $this->id_facilitador);
        $this->db->bind(':cupo_maximo',        $this->cupo_maximo);
        $this->db->bind(':requiere_formacion', $this->requiere_formacion);
        $this->db->bind(':tipo_ruta',          $this->tipo_ruta);
        $this->db->bind(':motivo_mant',        $this->motivo_mantenimiento);
        $this->db->bind(':user_id',            $user_id);
        $result = $this->db->execute();
        $this->audit('rutas', $this->id ? 'UPDATE' : 'INSERT', $this->id ?? null, $previos, ['nombre' => $this->nombre, 'estado' => $this->estado, 'tipo_ruta' => $this->tipo_ruta, 'fecha_visita' => $this->fecha_visita, 'cupo_maximo' => $this->cupo_maximo, 'requiere_formacion' => $this->requiere_formacion], $user_id);
        return $result;
    }

    public static function delete($id, $user_id = null) {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE rutas SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:user_id WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        $result = $db->execute();
        self::auditStatic('rutas', 'DELETE', $id, $previos, null, $user_id);
        return $result;
    }

    public static function getPuntos($id_ruta) {
        $db = new Database();
        $db->query("SELECT * FROM puntos_ruta WHERE id_ruta = :id_ruta AND is_active = TRUE ORDER BY orden ASC");
        $db->bind(':id_ruta', $id_ruta);
        return $db->resultSet();
    }

    // ── Participantes ────────────────────────────────────────────────────────

    public static function getParticipantes($id_ruta) {
        $db = new Database();
        $db->query("SELECT prt.*,
                           p.cedula, p.nombre, p.apellido, p.telefono
                    FROM participantes_ruta prt
                    LEFT JOIN personas p ON prt.id_persona = p.id
                    WHERE prt.id_ruta = :id AND prt.is_active = TRUE
                    ORDER BY COALESCE(p.apellido, prt.apellido_libre) ASC");
        $db->bind(':id', $id_ruta);
        return $db->resultSet();
    }

    public static function countParticipantes($id_ruta): int {
        $db = new Database();
        $db->query("SELECT COUNT(*) AS total FROM participantes_ruta WHERE id_ruta = :id AND is_active = TRUE");
        $db->bind(':id', $id_ruta);
        $row = $db->single();
        return (int)($row->total ?? 0);
    }

    public static function buscarPersonaPorCedula(string $cedula) {
        // Las cédulas se almacenan solo con dígitos (migración 037): normalizar la
        // entrada (quita V-/E-/puntos/espacios) para que la búsqueda no falle por formato.
        $cedula = preg_replace('/\D/', '', $cedula);
        if ($cedula === '') return null;
        // Devuelve SIEMPRE los datos de personas (id de personas, no de empleados)
        $db = new Database();
        $db->query("SELECT id, cedula, nombre, apellido, telefono, correo, genero,
                           fecha_nacimiento, parroquia_id, direccion
                    FROM personas WHERE cedula = :cedula AND is_active = TRUE LIMIT 1");
        $db->bind(':cedula', $cedula);
        return $db->single() ?: null;
    }

    public static function inscribir(int $id_ruta, int $id_persona, int $user_id, ?string $observaciones = null) {
        $db = new Database();
        $db->query("SELECT id FROM participantes_ruta WHERE id_ruta=:r AND id_persona=:p AND is_active=TRUE LIMIT 1");
        $db->bind(':r', $id_ruta);
        $db->bind(':p', $id_persona);
        if ($db->single()) {
            throw new Exception('Esta persona ya está inscrita en la ruta.');
        }
        $db->query("INSERT INTO participantes_ruta (id_ruta, id_persona, observaciones, created_by)
                    VALUES (:r, :p, :obs, :u)");
        $db->bind(':r',    $id_ruta);
        $db->bind(':p',    $id_persona);
        $db->bind(':obs',  $observaciones);
        $db->bind(':u',    $user_id);
        $result = $db->execute();
        self::auditStatic('participantes_ruta', 'INSERT', null, null, ['id_ruta' => $id_ruta, 'id_persona' => $id_persona], $user_id);
        return $result;
    }

    /**
     * ¿Ya hay un participante SIN cédula (libre) equivalente en esta ruta?
     * Mismo nombre + apellido + fecha de nacimiento, o misma cédula_libre.
     */
    public static function estaInscritoLibre(int $id_ruta, string $nombre, ?string $apellido, ?string $fnac, ?string $cedulaLibre = null): bool {
        $db = new Database();
        $tieneCed = $cedulaLibre !== null && trim($cedulaLibre) !== '';
        $sql = "SELECT 1 FROM participantes_ruta
                WHERE id_ruta = :r AND is_active = TRUE AND id_persona IS NULL
                  AND (
                        ( lower(trim(nombre_libre)) = lower(trim(:nom))
                          AND lower(trim(COALESCE(apellido_libre,''))) = lower(trim(:ape))
                          AND COALESCE(fecha_nac_libre::text,'') = COALESCE(:fnac,'') )";
        if ($tieneCed) {
            $sql .= " OR ( cedula_libre IS NOT NULL AND lower(trim(cedula_libre)) = lower(trim(:ced)) )";
        }
        $sql .= " ) LIMIT 1";
        $db->query($sql);
        $db->bind(':r', $id_ruta);
        $db->bind(':nom', $nombre);
        $db->bind(':ape', $apellido ?? '');
        $db->bind(':fnac', $fnac ?: null);
        if ($tieneCed) $db->bind(':ced', $cedulaLibre);
        return (bool)$db->single();
    }

    public static function inscribirLibre(int $id_ruta, array $datos, int $user_id) {
        $db = new Database();
        $db->query("INSERT INTO participantes_ruta
                        (id_ruta, nombre_libre, apellido_libre, cedula_libre,
                         genero_libre, fecha_nac_libre, observaciones,
                         nombre_representante, cedula_representante, created_by)
                    VALUES (:r, :nom, :ape, :ced, :gen, :fnac, :obs, :nrep, :crep, :u)");
        $db->bind(':r',    $id_ruta);
        $db->bind(':nom',  $datos['nombre_libre']);
        $db->bind(':ape',  $datos['apellido_libre']  ?? null);
        $db->bind(':ced',  $datos['cedula_libre']    ?? null);
        $db->bind(':gen',  $datos['genero_libre']    ?? null);
        $db->bind(':fnac', $datos['fecha_nac_libre'] ?? null);
        $db->bind(':obs',  $datos['observaciones']   ?? null);
        $db->bind(':nrep', $datos['nombre_representante'] ?? null);
        $db->bind(':crep', $datos['cedula_representante'] ?? null);
        $db->bind(':u',    $user_id);
        $result = $db->execute();
        self::auditStatic('participantes_ruta', 'INSERT', null, null, [
            'id_ruta'      => $id_ruta,
            'nombre_libre' => $datos['nombre_libre'],
            'cedula_libre' => $datos['cedula_libre'] ?? null,
            'genero_libre' => $datos['genero_libre'] ?? null,
            'cedula_representante' => $datos['cedula_representante'] ?? null,
        ], $user_id);
        return $result;
    }

    public static function desinscribir(int $id_participante, int $user_id) {
        $db = new Database();
        $db->query("UPDATE participantes_ruta
                    SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:u
                    WHERE id=:id");
        $db->bind(':id', $id_participante);
        $db->bind(':u',  $user_id);
        $result = $db->execute();
        self::auditStatic('participantes_ruta', 'DELETE', $id_participante, null, null, $user_id);
        return $result;
    }

    // ── Oficio emitido ───────────────────────────────────────────────────────

    /**
     * Genera un número correlativo, guarda el registro y devuelve el número asignado.
     */
    public static function crearOficioEmitido(int $id_ruta, array $data, int $user_id): string {
        $numero = ConfigSistema::generarNumeroOficio('ruta');
        $db     = new Database();
        $db->query("INSERT INTO oficios_emitidos
                    (numero, fecha, destinatario_nombre, destinatario_cargo, asunto, id_ruta, created_by)
                    VALUES (:num, CURRENT_DATE, :dest_nom, :dest_car, :asunto, :id_ruta, :uid)");
        $db->bind(':num',      $numero);
        $db->bind(':dest_nom', $data['destinatario_nombre'] ?? '');
        $db->bind(':dest_car', $data['destinatario_cargo']  ?? '');
        $db->bind(':asunto',   $data['asunto']              ?? '');
        $db->bind(':id_ruta',  $id_ruta);
        $db->bind(':uid',      $user_id);
        $db->execute();
        return $numero;
    }

    public static function getUltimoOficio(int $id_ruta): ?object {
        $db = new Database();
        $db->query("SELECT * FROM oficios_emitidos WHERE id_ruta = :id ORDER BY created_at DESC LIMIT 1");
        $db->bind(':id', $id_ruta);
        return $db->single() ?: null;
    }

    // ── Asistencia ───────────────────────────────────────────────────────────

    public static function marcarAsistencia(int $id_participante, bool $asistio, $userId): void {
        $db = new Database();
        $db->query("UPDATE participantes_ruta SET asistio = :a, updated_at = NOW(), updated_by = :u WHERE id = :id AND is_active = TRUE");
        $db->bind(':a', $asistio);
        $db->bind(':u', $userId);
        $db->bind(':id', $id_participante);
        $db->execute();
    }

    public static function marcarAsistenciaMasiva(int $id_ruta, $userId): void {
        $db = new Database();
        $db->query("UPDATE participantes_ruta SET asistio = TRUE, updated_at = NOW(), updated_by = :u WHERE id_ruta = :r AND is_active = TRUE AND asistio = FALSE");
        $db->bind(':u', $userId);
        $db->bind(':r', $id_ruta);
        $db->execute();
    }

    // ── Informe post-visita ──────────────────────────────────────────────────

    public static function getInforme(int $id_ruta): ?object {
        $db = new Database();
        $db->query("SELECT * FROM ruta_informes WHERE id_ruta = :id");
        $db->bind(':id', $id_ruta);
        return $db->single() ?: null;
    }

    public static function saveInforme(array $data): bool {
        $db     = new Database();
        $userId = $_SESSION['user_id'] ?? null;
        $inf    = self::getInforme($data['id_ruta']);
        $total  = (int)$data['mujeres'] + (int)$data['hombres'] + (int)$data['ninas'] + (int)$data['ninos'];
        if ($inf) {
            $db->query("UPDATE ruta_informes
                        SET lugar_exacto=:lugar, mujeres=:m, hombres=:h, ninas=:ni, ninos=:no,
                            total_atendidos=:tot, observaciones=:obs, resumen_visita=:res,
                            updated_at=CURRENT_TIMESTAMP
                        WHERE id_ruta=:id_ruta");
        } else {
            $db->query("INSERT INTO ruta_informes
                        (id_ruta, lugar_exacto, mujeres, hombres, ninas, ninos, total_atendidos, observaciones, resumen_visita, created_by)
                        VALUES (:id_ruta, :lugar, :m, :h, :ni, :no, :tot, :obs, :res, :uid)");
            $db->bind(':uid', $userId);
        }
        $db->bind(':id_ruta', $data['id_ruta']);
        $db->bind(':lugar',   $data['lugar_exacto']  ?? '');
        $db->bind(':m',       (int)$data['mujeres']);
        $db->bind(':h',       (int)$data['hombres']);
        $db->bind(':ni',      (int)$data['ninas']);
        $db->bind(':no',      (int)$data['ninos']);
        $db->bind(':tot',     $total);
        $db->bind(':obs',     $data['observaciones']  ?? null);
        $db->bind(':res',     $data['resumen_visita'] ?? '');
        return $db->execute();
    }
}
