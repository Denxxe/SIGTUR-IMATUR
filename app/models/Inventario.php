<?php
/**
 * Clase Inventario: bienes institucionales.
 *
 * Fase 1 del replanteamiento (mig. 062) — ver docs/PLAN_MODULO_BIENES.md.
 * Dos conceptos que ANTES estaban mezclados y ahora son ejes separados:
 *
 *   · ESTATUS   → situación administrativa (en espera de codificación,
 *                 activo, en mantenimiento, dado de baja…). Decide si el
 *                 bien sigue formando parte del inventario activo.
 *   · CONDICION → estado físico (nuevo, bueno, regular, dañado).
 *
 * Y dos ejes de clasificación, también independientes:
 *
 *   · CÓDIGO OFICIAL (grupo-subgrupo-sección + N° de orden) lo asigna la
 *     Alcaldía; IMATUR solo lo transcribe del Formulario BM-1. No sirve
 *     para clasificar: sillas, mesas y aires comparten `2-01-108`.
 *   · CATEGORÍA interna, para los reportes de la Presidencia.
 */
class Inventario extends Model {

    // ── Estatus administrativo (mig. 062) ────────────────────────────────────
    const EST_SIN_CODIFICAR = 'En espera de codificación';
    const EST_ACTIVO        = 'Activo';
    const EST_MANTENIMIENTO = 'En mantenimiento';
    const EST_EXTRAVIADO    = 'Extraviado';
    const EST_ROBADO        = 'Robado';
    const EST_BAJA          = 'Dado de baja';

    const ESTATUS = [
        self::EST_SIN_CODIFICAR,
        self::EST_ACTIVO,
        self::EST_MANTENIMIENTO,
        self::EST_EXTRAVIADO,
        self::EST_ROBADO,
        self::EST_BAJA,
    ];
    const ESTATUS_DEFAULT = self::EST_SIN_CODIFICAR;

    const ESTATUS_BADGES = [
        self::EST_SIN_CODIFICAR => 'sig-badge--warning',
        self::EST_ACTIVO        => 'sig-badge--success',
        self::EST_MANTENIMIENTO => 'sig-badge--info',
        self::EST_EXTRAVIADO    => 'sig-badge--warning',
        self::EST_ROBADO        => 'sig-badge--danger',
        self::EST_BAJA          => 'sig-badge--neutral',
    ];

    /**
     * Estatus que SACAN al bien del inventario activo.
     * B-38: el bien dado de baja no debe salir en el inventario activo de
     * IMATUR, aunque su registro y el oficio se conservan como aval.
     * B-34: el bien en mantenimiento NO desaparece, solo deja de estar
     * disponible — por eso no está en esta lista.
     */
    const ESTATUS_FUERA_DE_INVENTARIO = [self::EST_BAJA];

    /** Estatus en los que el bien no puede usarse (pero sigue inventariado). */
    const ESTATUS_NO_DISPONIBLE = [
        self::EST_MANTENIMIENTO, self::EST_EXTRAVIADO, self::EST_ROBADO, self::EST_BAJA,
    ];

    // ── Condición física ─────────────────────────────────────────────────────
    const CONDICIONES       = ['Nuevo', 'Bueno', 'Regular', 'Dañado'];
    const CONDICION_DEFAULT = 'Bueno';
    const CONDICION_BADGES  = [
        'Nuevo'   => 'sig-badge--success',
        'Bueno'   => 'sig-badge--info',
        'Regular' => 'sig-badge--warning',
        'Dañado'  => 'sig-badge--danger',
    ];

    // ── Procedencia (B-18) ───────────────────────────────────────────────────
    const ORIGENES       = ['Compra', 'Donación'];
    const ORIGEN_DEFAULT = 'Compra';

    /**
     * Tipo de bien (mig. 044). Quedó sin uso: B-07 dice que no llevan
     * consumibles y B-09 que el registro es individual. Se mantiene por
     * compatibilidad hasta que el cliente confirme su eliminación (B-66).
     */
    const TIPOS_BIEN        = ['Durable', 'Fungible'];
    const TIPO_BIEN_DEFAULT = 'Durable';
    const TIPO_BIEN_BADGES  = ['Durable' => 'sig-badge--info', 'Fungible' => 'sig-badge--neutral'];

    private ?int $id;
    private ?int $id_categoria;
    private ?int $id_ubicacion;
    private string $nombre;
    private string $descripcion;
    private string $marca;
    private string $modelo;
    private ?string $serial;
    private string $condicion;
    private string $estatus;
    private string $observaciones;
    // Código oficial de la Alcaldía, por partes
    private ?string $codigo_grupo;
    private ?string $codigo_subgrupo;
    private ?string $codigo_seccion;
    private ?string $nro_orden;
    private bool $verificado_alcaldia;
    private ?string $fecha_verificacion;
    // Adquisición
    private ?string $origen;
    private ?string $donante;
    private ?string $costo_adquisicion;
    private ?string $fecha_adquisicion;
    private ?string $proveedor;
    private bool $tiene_garantia;
    private ?string $garantia_vence;
    // Responsable
    private ?int $id_responsable;

    public function __construct(array $data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id            = $data['id'] ?? null;
            $this->id_categoria  = $data['id_categoria'] ?? null;
            $this->id_ubicacion  = $data['id_ubicacion'] ?? null;
            $this->nombre        = $data['nombre'] ?? '';
            $this->descripcion   = $data['descripcion'] ?? '';
            $this->marca         = $data['marca'] ?? '';
            $this->modelo        = $data['modelo'] ?? '';
            $this->serial        = $data['serial'] ?? null;
            $this->condicion     = in_array($data['condicion'] ?? '', self::CONDICIONES, true)
                                    ? $data['condicion'] : self::CONDICION_DEFAULT;
            $this->estatus       = in_array($data['estatus'] ?? '', self::ESTATUS, true)
                                    ? $data['estatus'] : self::ESTATUS_DEFAULT;
            $this->observaciones = $data['observaciones'] ?? '';

            $this->codigo_grupo        = self::limpiarParte($data['codigo_grupo']    ?? null);
            $this->codigo_subgrupo     = self::limpiarParte($data['codigo_subgrupo'] ?? null);
            $this->codigo_seccion      = self::limpiarParte($data['codigo_seccion']  ?? null);
            $this->nro_orden           = self::limpiarParte($data['nro_orden']       ?? null);
            $this->verificado_alcaldia = !empty($data['verificado_alcaldia']);
            $this->fecha_verificacion  = ($data['fecha_verificacion'] ?? '') ?: null;

            $this->origen            = in_array($data['origen'] ?? '', self::ORIGENES, true)
                                        ? $data['origen'] : self::ORIGEN_DEFAULT;
            $this->donante           = ($data['donante'] ?? '') ?: null;
            $this->costo_adquisicion = ($data['costo_adquisicion'] ?? '') !== '' ? $data['costo_adquisicion'] : null;
            $this->fecha_adquisicion = ($data['fecha_adquisicion'] ?? '') ?: null;
            $this->proveedor         = ($data['proveedor'] ?? '') ?: null;
            $this->tiene_garantia    = !empty($data['tiene_garantia']);
            $this->garantia_vence    = ($data['garantia_vence'] ?? '') ?: null;

            $this->id_responsable = !empty($data['id_responsable']) ? (int)$data['id_responsable'] : null;
        }
    }

    /** Normaliza una parte del código: sin espacios, vacío → NULL. */
    private static function limpiarParte($v): ?string {
        $v = trim((string)($v ?? ''));
        return $v !== '' ? $v : null;
    }

    /**
     * Arma el código oficial compuesto tal como lo emite la Alcaldía en el
     * Formulario BM-1: `grupo-subgrupo-sección-N° de orden` (ej. 2-01-108-084).
     * Devuelve NULL mientras falte alguna parte — un bien sin codificar no
     * tiene código, y así se distingue de uno ya verificado.
     */
    public static function componerCodigo(?string $g, ?string $sg, ?string $sec, ?string $orden): ?string {
        foreach ([$g, $sg, $sec, $orden] as $p) {
            if (trim((string)$p) === '') return null;
        }
        return trim($g) . '-' . trim($sg) . '-' . trim($sec) . '-' . trim($orden);
    }

    /** ¿El bien está fuera del inventario activo? (dado de baja) */
    public static function fueraDeInventario(?string $estatus): bool {
        return in_array((string)$estatus, self::ESTATUS_FUERA_DE_INVENTARIO, true);
    }

    /** ¿El bien puede usarse hoy? */
    public static function disponible(?string $estatus): bool {
        return !in_array((string)$estatus, self::ESTATUS_NO_DISPONIBLE, true);
    }

    private const SELECT_BASE = "
        SELECT i.*,
               c.nombre  AS categoria,
               u.nombre  AS ubicacion,
               u.sede    AS sede,
               u.es_deposito,
               d.nombre  AS departamento,
               TRIM(COALESCE(p.nombre,'') || ' ' || COALESCE(p.apellido,'')) AS responsable
          FROM inventario i
          INNER JOIN categorias  c ON i.id_categoria = c.id
          INNER JOIN ubicaciones u ON i.id_ubicacion = u.id
          LEFT  JOIN departamentos d ON u.\"departamento _d\" = d.id
          LEFT  JOIN empleados    e ON i.id_responsable = e.id
          LEFT  JOIN personas     p ON e.id_persona = p.id
    ";

    /**
     * Inventario ACTIVO: excluye los dados de baja (B-38).
     * Los que están en mantenimiento SÍ aparecen (B-34), marcados por su estatus.
     */
    public static function all() {
        $db = new Database();
        $db->query(self::SELECT_BASE . "
            WHERE i.is_active = TRUE AND i.estatus <> :baja
            ORDER BY i.nombre ASC");
        $db->bind(':baja', self::EST_BAJA);
        return $db->resultSet();
    }

    /** Bienes desincorporados — el histórico que queda tras la baja (B-38). */
    public static function desincorporados() {
        $db = new Database();
        $db->query(self::SELECT_BASE . "
            WHERE i.is_active = TRUE AND i.estatus = :baja
            ORDER BY i.nombre ASC");
        $db->bind(':baja', self::EST_BAJA);
        return $db->resultSet();
    }

    /** Bienes registrados que aún esperan el código de la Alcaldía (B-12). */
    public static function pendientesCodificacion() {
        $db = new Database();
        $db->query(self::SELECT_BASE . "
            WHERE i.is_active = TRUE AND i.estatus = :est
            ORDER BY i.created_at ASC");
        $db->bind(':est', self::EST_SIN_CODIFICAR);
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query(self::SELECT_BASE . " WHERE i.id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    /**
     * ¿Ya existe otro bien con ese N° de orden de la Alcaldía?
     * Es el identificador real del bien; el código completo se deriva de él.
     */
    public static function findByNroOrden(string $orden, ?int $excludeId = null): ?int {
        if (trim($orden) === '') return null;
        $db = new Database();
        $db->query("SELECT id FROM inventario
                     WHERE nro_orden = :orden AND is_active = TRUE"
                   . ($excludeId ? " AND id <> :excl" : ""));
        $db->bind(':orden', trim($orden));
        if ($excludeId) $db->bind(':excl', $excludeId);
        $row = $db->single();
        return $row ? (int)$row->id : null;
    }

    public static function findByCodigoBn(string $codigo, ?int $excludeId = null): ?int {
        if ($codigo === '') return null;
        $db = new Database();
        $db->query("SELECT id FROM inventario WHERE codigo_bn = :codigo AND is_active = TRUE"
                   . ($excludeId ? " AND id <> :excl" : ""));
        $db->bind(':codigo', $codigo);
        if ($excludeId) $db->bind(':excl', $excludeId);
        $row = $db->single();
        return $row ? (int)$row->id : null;
    }

    public static function findBySerial(string $serial, ?int $excludeId = null): ?int {
        if ($serial === '') return null;
        $db = new Database();
        $db->query("SELECT id FROM inventario WHERE serial = :serial AND is_active = TRUE"
                   . ($excludeId ? " AND id <> :excl" : ""));
        $db->bind(':serial', $serial);
        if ($excludeId) $db->bind(':excl', $excludeId);
        $row = $db->single();
        return $row ? (int)$row->id : null;
    }

    public function save($user_id = null) {
        $previos = null;

        // El código compuesto se deriva SIEMPRE de sus partes: así no puede
        // quedar desincronizado con lo que la Alcaldía asignó.
        $codigoBn = self::componerCodigo(
            $this->codigo_grupo, $this->codigo_subgrupo, $this->codigo_seccion, $this->nro_orden
        );

        $campos = "id_categoria=:id_categoria, id_ubicacion=:id_ubicacion, nombre=:nombre,
                   descripcion=:descripcion, marca=:marca, modelo=:modelo, serial=:serial,
                   condicion=:condicion, estatus=:estatus, observaciones=:observaciones,
                   codigo_bn=:codigo_bn, codigo_grupo=:codigo_grupo, codigo_subgrupo=:codigo_subgrupo,
                   codigo_seccion=:codigo_seccion, nro_orden=:nro_orden,
                   verificado_alcaldia=:verificado_alcaldia, fecha_verificacion=:fecha_verificacion,
                   origen=:origen, donante=:donante, costo_adquisicion=:costo_adquisicion,
                   fecha_adquisicion=:fecha_adquisicion, proveedor=:proveedor,
                   tiene_garantia=:tiene_garantia, garantia_vence=:garantia_vence,
                   id_responsable=:id_responsable";

        if ($this->id) {
            $previos = self::find($this->id);
            $this->db->query("UPDATE inventario SET $campos,
                              updated_at=CURRENT_TIMESTAMP, updated_by=:user_id WHERE id=:id");
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO inventario
                (id_categoria, id_ubicacion, nombre, descripcion, marca, modelo, serial,
                 condicion, estatus, observaciones, codigo_bn, codigo_grupo, codigo_subgrupo,
                 codigo_seccion, nro_orden, verificado_alcaldia, fecha_verificacion,
                 origen, donante, costo_adquisicion, fecha_adquisicion, proveedor,
                 tiene_garantia, garantia_vence, id_responsable, created_by)
                VALUES
                (:id_categoria, :id_ubicacion, :nombre, :descripcion, :marca, :modelo, :serial,
                 :condicion, :estatus, :observaciones, :codigo_bn, :codigo_grupo, :codigo_subgrupo,
                 :codigo_seccion, :nro_orden, :verificado_alcaldia, :fecha_verificacion,
                 :origen, :donante, :costo_adquisicion, :fecha_adquisicion, :proveedor,
                 :tiene_garantia, :garantia_vence, :id_responsable, :user_id)");
        }

        $this->db->bind(':id_categoria',        $this->id_categoria);
        $this->db->bind(':id_ubicacion',        $this->id_ubicacion);
        $this->db->bind(':nombre',              $this->nombre);
        $this->db->bind(':descripcion',         $this->descripcion);
        $this->db->bind(':marca',               $this->marca);
        $this->db->bind(':modelo',              $this->modelo);
        $this->db->bind(':serial',              $this->serial);
        $this->db->bind(':condicion',           $this->condicion);
        $this->db->bind(':estatus',             $this->estatus);
        $this->db->bind(':observaciones',       $this->observaciones);
        $this->db->bind(':codigo_bn',           $codigoBn);
        $this->db->bind(':codigo_grupo',        $this->codigo_grupo);
        $this->db->bind(':codigo_subgrupo',     $this->codigo_subgrupo);
        $this->db->bind(':codigo_seccion',      $this->codigo_seccion);
        $this->db->bind(':nro_orden',           $this->nro_orden);
        $this->db->bind(':verificado_alcaldia', $this->verificado_alcaldia ? 't' : 'f');
        $this->db->bind(':fecha_verificacion',  $this->fecha_verificacion);
        $this->db->bind(':origen',              $this->origen);
        $this->db->bind(':donante',             $this->donante);
        $this->db->bind(':costo_adquisicion',   $this->costo_adquisicion);
        $this->db->bind(':fecha_adquisicion',   $this->fecha_adquisicion);
        $this->db->bind(':proveedor',           $this->proveedor);
        $this->db->bind(':tiene_garantia',      $this->tiene_garantia ? 't' : 'f');
        $this->db->bind(':garantia_vence',      $this->garantia_vence);
        $this->db->bind(':id_responsable',      $this->id_responsable);
        $this->db->bind(':user_id',             $user_id);

        $result = $this->db->execute();
        $this->audit('inventario', $this->id ? 'UPDATE' : 'INSERT', $this->id ?? 0, $previos, [
            'nombre'    => $this->nombre,
            'codigo_bn' => $codigoBn,
            'estatus'   => $this->estatus,
            'condicion' => $this->condicion,
        ], $user_id);
        return $result;
    }

    /**
     * Registra el código que la Alcaldía asignó al recibir el BM-1 y pasa el
     * bien a Activo (§2-bis y §4.1 del plan). Es el paso de conciliación.
     */
    public static function codificar(int $id, array $partes, ?string $fecha, $user_id = null): bool {
        $codigo = self::componerCodigo(
            $partes['codigo_grupo'] ?? null, $partes['codigo_subgrupo'] ?? null,
            $partes['codigo_seccion'] ?? null, $partes['nro_orden'] ?? null
        );
        if ($codigo === null) {
            throw new Exception('Faltan partes del código: se requieren grupo, sub-grupo, sección y N° de orden.');
        }
        $dup = self::findByNroOrden((string)$partes['nro_orden'], $id);
        if ($dup) {
            throw new Exception('El N° de orden ' . htmlspecialchars((string)$partes['nro_orden'])
                . ' ya está asignado a otro bien (ID #' . $dup . ').');
        }

        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE inventario
                       SET codigo_grupo=:g, codigo_subgrupo=:sg, codigo_seccion=:sec,
                           nro_orden=:orden, codigo_bn=:codigo,
                           verificado_alcaldia=TRUE, fecha_verificacion=:fecha,
                           estatus=:activo, updated_at=CURRENT_TIMESTAMP, updated_by=:u
                     WHERE id=:id AND is_active = TRUE");
        $db->bind(':g',      trim((string)$partes['codigo_grupo']));
        $db->bind(':sg',     trim((string)$partes['codigo_subgrupo']));
        $db->bind(':sec',    trim((string)$partes['codigo_seccion']));
        $db->bind(':orden',  trim((string)$partes['nro_orden']));
        $db->bind(':codigo', $codigo);
        $db->bind(':fecha',  $fecha ?: date('Y-m-d'));
        $db->bind(':activo', self::EST_ACTIVO);
        $db->bind(':u',      $user_id);
        $db->bind(':id',     $id);
        $ok = $db->execute();
        self::auditStatic('inventario', 'UPDATE', $id, $previos,
            ['codigo_bn' => $codigo, 'estatus' => self::EST_ACTIVO, 'verificado_alcaldia' => true], $user_id);
        return $ok;
    }

    /** Conteo por estatus, para los tiles del listado y los reportes. */
    public static function resumenPorEstatus(): array {
        $db = new Database();
        $db->query("SELECT estatus, COUNT(*) AS n FROM inventario
                     WHERE is_active = TRUE GROUP BY estatus");
        $out = array_fill_keys(self::ESTATUS, 0);
        foreach ($db->resultSet() as $r) $out[$r->estatus] = (int)$r->n;
        return $out;
    }

    public static function delete($id, $user_id = null) {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE inventario SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:user_id WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        $result = $db->execute();
        self::auditStatic('inventario', 'DELETE', $id, $previos, null, $user_id);
        return $result;
    }
}
