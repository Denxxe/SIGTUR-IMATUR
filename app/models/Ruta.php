<?php
/**
 * Ruta — EL CATÁLOGO: qué se ofrece.
 *
 * Desde la mig. 078 (fase T-A) esta tabla ya **no** representa una salida. El
 * recorrido, sus puntos, la duración y la tarifa se guardan **una vez**; cada
 * vez que se sale, se crea una fila en `ruta_ejecuciones` (ver `RutaEjecucion`).
 *
 * El cliente lo dijo de tres formas distintas: *"existe un catálogo… todo lleva
 * un catálogo"* (R-08), *"dos salidas de la misma ruta en una misma mañana"*
 * (R-09) y, cerrando el tema, *"así sean la misma ruta, es considerada 2 salidas
 * y en el registro son 2 rutas aplicadas"* (R-07).
 *
 * ⚠️ `fecha_visita`, `hora_visita`, `id_facilitador` y `cupo_maximo` **siguen en
 * la tabla pero están OBSOLETAS**: se conservan hasta la limpieza posterior y
 * este modelo ya no las escribe. Si las lees, estás leyendo un dato muerto.
 */
class Ruta extends Model {
    private ?int   $id;
    private string $nombre;
    private string $descripcion;
    private string $duracion_estimada;
    private string $estado;
    private ?int    $id_departamento;
    private bool    $requiere_formacion;
    private string  $tipo_ruta;
    private ?string $motivo_mantenimiento;

    // ── Fuente única de verdad para enums de este módulo ─────────────────────
    // «Finalizada» se retiró en la mig. 078: describía una SALIDA, no una ruta
    // del catálogo. El estado de una salida vive en RutaEjecucion::ESTADOS.
    const ESTADOS        = ['Activa', 'Inactiva', 'En Mantenimiento'];
    /** CSS class por estado (para vistas) */
    const ESTADO_BADGES  = [
        'Activa'           => 'sig-badge--success',
        'Inactiva'         => 'sig-badge--danger',
        'En Mantenimiento' => 'sig-badge--warning',
    ];

    public static array $TIPOS_RUTA = ['Cumaná Histórica', 'Exploradores de Cumaná', 'Comunitaria', 'General'];

    public function __construct(array $data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id                  = $data['id'] ?? null;
            $this->nombre              = $data['nombre'] ?? '';
            $this->descripcion         = $data['descripcion'] ?? '';
            $this->duracion_estimada   = $data['duracion_estimada'] ?? '';
            $this->estado              = in_array($data['estado'] ?? '', self::ESTADOS, true)
                                         ? $data['estado'] : 'Activa';
            $this->id_departamento     = !empty($data['id_departamento']) ? (int)$data['id_departamento'] : null;
            $this->requiere_formacion  = !empty($data['requiere_formacion']);
            $this->tipo_ruta           = in_array($data['tipo_ruta'] ?? '', self::$TIPOS_RUTA)
                                         ? $data['tipo_ruta'] : 'General';
            $this->motivo_mantenimiento = $data['motivo_mantenimiento'] ?? null;
        }
    }

    /**
     * El catálogo completo, con el número de puntos y de salidas de cada ruta.
     * El conteo de participantes ya no cuelga de la ruta sino de cada salida.
     */
    public static function all() {
        $db = new Database();
        $db->query("SELECT r.*,
                           d.nombre AS departamento_nombre,
                           (SELECT COUNT(*) FROM puntos_ruta pr
                             WHERE pr.id_ruta = r.id AND pr.is_active = TRUE) AS total_puntos,
                           (SELECT COUNT(*) FROM ruta_ejecuciones ej
                             WHERE ej.id_ruta = r.id AND ej.is_active = TRUE) AS total_salidas,
                           (SELECT MAX(ej.fecha) FROM ruta_ejecuciones ej
                             WHERE ej.id_ruta = r.id AND ej.is_active = TRUE) AS ultima_salida
                    FROM rutas r
                    LEFT JOIN departamentos d ON r.id_departamento = d.id
                    WHERE r.is_active = TRUE
                    ORDER BY r.nombre ASC");
        return $db->resultSet();
    }

    /** Solo las rutas que se pueden programar hoy (para el selector de salidas). */
    public static function activas() {
        $db = new Database();
        $db->query("SELECT id, nombre, tipo_ruta, duracion_estimada
                      FROM rutas
                     WHERE is_active = TRUE AND estado = 'Activa'
                     ORDER BY nombre ASC");
        return $db->resultSet();
    }

    /**
     * Catálogo paginado, con búsqueda por nombre/descripción y filtro por
     * estado y tipo.
     *
     * Los filtros por **fecha y período se fueron** a `RutaEjecucion::paginate()`:
     * el catálogo no tiene fecha. Preguntarle a una ruta «¿fue esta semana?» no
     * significa nada — eso se le pregunta a una salida.
     */
    public static function paginate(int $pagina, int $porPagina, array $f = []): array {
        $db    = new Database();
        $binds = [];
        $where = "r.is_active = TRUE";

        if (!empty($f['buscar'])) {
            $where .= " AND (r.nombre ILIKE :q OR r.descripcion ILIKE :q)";
            $binds[':q'] = '%' . $f['buscar'] . '%';
        }
        if (!empty($f['estado'])) { $where .= " AND r.estado = :estado"; $binds[':estado'] = $f['estado']; }
        if (!empty($f['tipo']))   { $where .= " AND r.tipo_ruta = :tipo"; $binds[':tipo']  = $f['tipo']; }

        $base = "FROM rutas r
                 LEFT JOIN departamentos d ON r.id_departamento = d.id
                 WHERE {$where}";

        $db->query("SELECT COUNT(*) AS total {$base}");
        foreach ($binds as $k => $v) $db->bind($k, $v);
        $total = (int)($db->single()->total ?? 0);

        $db->query("SELECT r.*,
                           d.nombre AS departamento_nombre,
                           (SELECT COUNT(*) FROM puntos_ruta pr
                             WHERE pr.id_ruta = r.id AND pr.is_active = TRUE) AS total_puntos,
                           (SELECT COUNT(*) FROM ruta_ejecuciones ej
                             WHERE ej.id_ruta = r.id AND ej.is_active = TRUE) AS total_salidas,
                           (SELECT MAX(ej.fecha) FROM ruta_ejecuciones ej
                             WHERE ej.id_ruta = r.id AND ej.is_active = TRUE) AS ultima_salida
                    {$base}
                    ORDER BY
                        CASE r.estado WHEN 'Activa' THEN 0 WHEN 'En Mantenimiento' THEN 1 ELSE 2 END,
                        r.nombre ASC
                    LIMIT :lim OFFSET :off");
        foreach ($binds as $k => $v) $db->bind($k, $v);
        $db->bind(':lim', $porPagina);
        $db->bind(':off', ($pagina - 1) * $porPagina);

        return ['items' => $db->resultSet(), 'total' => $total];
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT r.*,
                           d.nombre AS departamento_nombre,
                           (SELECT COUNT(*) FROM ruta_ejecuciones ej
                             WHERE ej.id_ruta = r.id AND ej.is_active = TRUE) AS total_salidas
                    FROM rutas r
                    LEFT JOIN departamentos d ON r.id_departamento = d.id
                    WHERE r.id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    /**
     * Guarda la ruta del catálogo. **No** escribe fecha, hora, facilitador ni
     * cupo: eso es de la salida (mig. 078) y vive en `ruta_ejecuciones`.
     */
    public function save($user_id = null) {
        $previos = null;
        if ($this->id) {
            $previos = self::find($this->id);
            $this->db->query("UPDATE rutas
                              SET nombre=:nombre, descripcion=:descripcion,
                                  duracion_estimada=:duracion_estimada, estado=:estado,
                                  id_departamento=:id_departamento,
                                  requiere_formacion=:requiere_formacion,
                                  tipo_ruta=:tipo_ruta,
                                  motivo_mantenimiento=:motivo_mant,
                                  updated_at=CURRENT_TIMESTAMP, updated_by=:user_id
                              WHERE id=:id");
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO rutas
                              (nombre, descripcion, duracion_estimada, estado,
                               id_departamento, requiere_formacion, tipo_ruta,
                               motivo_mantenimiento, created_by)
                              VALUES (:nombre, :descripcion, :duracion_estimada, :estado,
                                      :id_departamento, :requiere_formacion, :tipo_ruta,
                                      :motivo_mant, :user_id)");
        }
        $this->db->bind(':nombre',             $this->nombre);
        $this->db->bind(':descripcion',        $this->descripcion);
        $this->db->bind(':duracion_estimada',  $this->duracion_estimada);
        $this->db->bind(':estado',             $this->estado);
        $this->db->bind(':id_departamento',    $this->id_departamento);
        $this->db->bind(':requiere_formacion', $this->requiere_formacion);
        $this->db->bind(':tipo_ruta',          $this->tipo_ruta);
        $this->db->bind(':motivo_mant',        $this->motivo_mantenimiento);
        $this->db->bind(':user_id',            $user_id);
        $result = $this->db->execute();
        $this->audit('rutas', $this->id ? 'UPDATE' : 'INSERT', $this->id ?? null, $previos,
            ['nombre' => $this->nombre, 'estado' => $this->estado, 'tipo_ruta' => $this->tipo_ruta,
             'requiere_formacion' => $this->requiere_formacion], $user_id);
        return $result;
    }

    /**
     * Baja lógica de la ruta del catálogo. Se bloquea si tiene salidas: el
     * recorrido puede dejar de ofrecerse (estado «Inactiva»), pero borrarlo se
     * llevaría por delante el histórico de lo que ya se ejecutó.
     */
    public static function delete($id, $user_id = null) {
        $db = new Database();
        $db->query("SELECT COUNT(*) AS n FROM ruta_ejecuciones
                     WHERE id_ruta = :id AND is_active = TRUE");
        $db->bind(':id', $id);
        if ((int)($db->single()->n ?? 0) > 0) {
            throw new Exception('Esta ruta ya tiene salidas registradas: no se elimina. Si dejó de ofrecerse, márcala como «Inactiva» — así el histórico se conserva.');
        }

        $previos = self::find($id);
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

    // ── Puntos del recorrido ─────────────────────────────────────────────────
    //
    // Los participantes, el informe, el oficio y la asistencia SE MUDARON a
    // `RutaEjecucion` en la mig. 078: cuelgan de la SALIDA, no del recorrido.
    // Preguntarle a una ruta del catálogo «¿quién fue?» no significa nada —
    // fue gente distinta cada vez que se ejecutó.

    /** Busca una persona por cédula, para inscribirla en una salida. */
    public static function buscarPersonaPorCedula(string $cedula) {
        // Las cédulas se almacenan solo con dígitos (mig. 037): se normaliza la
        // entrada (quita V-/E-/puntos/espacios) para que no falle por formato.
        $cedula = preg_replace('/\D/', '', $cedula);
        if ($cedula === '') return null;
        $db = new Database();
        $db->query("SELECT id, cedula, nombre, apellido, telefono, correo, genero,
                           fecha_nacimiento, parroquia_id, direccion
                    FROM personas WHERE cedula = :cedula AND is_active = TRUE LIMIT 1");
        $db->bind(':cedula', $cedula);
        return $db->single() ?: null;
    }
}
