<?php
/**
 * ActividadInventario — bitácora de movimientos de bienes.
 *
 * Fase 2 del replanteamiento (mig. 063), ver docs/PLAN_MODULO_BIENES.md §4.2/§4.3.
 *
 * Un movimiento NO es solo un apunte: cambia el estado del bien. Por eso
 * `registrarMovimiento()` es transaccional — o se registra el movimiento y
 * se actualiza el bien, o no ocurre ninguna de las dos cosas.
 *
 * Los tres traslados que describe el cliente (B-31: depósito→departamento,
 * departamento→depósito, departamento→departamento) se modelan con un solo
 * tipo 'Traslado' + origen/destino, en lugar de tres tipos distintos: el
 * caso concreto se deduce de las ubicaciones, y los reportes no dependen
 * de cómo se haya nombrado el traslado.
 *
 * Todo movimiento requiere autorización de la Coordinadora de Bienes
 * (B-32), que se identifica por CARGO + DEPARTAMENTO (B-64).
 */
class ActividadInventario extends Model {

    const MOV_TRASLADO     = 'Traslado';
    const MOV_RESPONSABLE  = 'Asignación de responsable';
    const MOV_SALIDA_MANT  = 'Salida a mantenimiento';
    const MOV_RETORNO_MANT = 'Retorno de mantenimiento';
    const MOV_BAJA         = 'Baja';

    const TIPOS = [
        self::MOV_TRASLADO,
        self::MOV_RESPONSABLE,
        self::MOV_SALIDA_MANT,
        self::MOV_RETORNO_MANT,
        self::MOV_BAJA,
    ];

    /** Tipos que el usuario puede elegir al registrar (la baja tiene su propio flujo). */
    const TIPOS_MANUALES = [
        self::MOV_TRASLADO,
        self::MOV_RESPONSABLE,
        self::MOV_SALIDA_MANT,
        self::MOV_RETORNO_MANT,
    ];

    const TIPO_BADGES = [
        self::MOV_TRASLADO     => 'sig-badge--info',
        self::MOV_RESPONSABLE  => 'sig-badge--brand',
        self::MOV_SALIDA_MANT  => 'sig-badge--warning',
        self::MOV_RETORNO_MANT => 'sig-badge--success',
        self::MOV_BAJA         => 'sig-badge--danger',
    ];

    private ?int $id;
    private ?int $id_inventario;
    private string $tipo_movimiento;
    private string $descripcion;
    private ?string $fecha;
    private ?int $id_empleado_responsable;

    public function __construct(array $data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id                      = $data['id'] ?? null;
            $this->id_inventario           = $data['id_inventario'] ?? null;
            $this->tipo_movimiento         = $data['tipo_movimiento'] ?? self::MOV_TRASLADO;
            $this->descripcion             = $data['descripcion'] ?? '';
            $this->fecha                   = $data['fecha'] ?? date('Y-m-d');
            $this->id_empleado_responsable = $data['id_empleado_responsable'] ?? null;
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Quién autoriza (B-32 + B-64)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Resuelve el empleado que autoriza los movimientos de bienes: el que
     * ocupa el CARGO configurado dentro del DEPARTAMENTO configurado
     * (`bienes_cargo_autoriza` / `bienes_depto_autoriza`, mig. 063).
     * Devuelve null si el puesto está vacante — el módulo debe avisarlo
     * en vez de dejar pasar movimientos sin autorización.
     */
    public static function autorizador() {
        $idDepto = (int)ConfigSistema::get('bienes_depto_autoriza');
        $idCargo = (int)ConfigSistema::get('bienes_cargo_autoriza');
        if ($idDepto <= 0 || $idCargo <= 0) return null;

        $db = new Database();
        $db->query("SELECT e.id,
                           TRIM(COALESCE(p.nombre,'') || ' ' || COALESCE(p.apellido,'')) AS nombre,
                           c.nombre AS cargo, d.nombre AS departamento
                      FROM empleados e
                      INNER JOIN personas      p ON e.id_persona      = p.id
                      LEFT  JOIN cargos        c ON e.id_cargo        = c.id
                      LEFT  JOIN departamentos d ON e.id_departamento = d.id
                     WHERE e.is_active = TRUE AND e.fecha_egreso IS NULL
                       AND e.id_departamento = :depto AND e.id_cargo = :cargo
                     ORDER BY e.id LIMIT 1");
        $db->bind(':depto', $idDepto);
        $db->bind(':cargo', $idCargo);
        return $db->single() ?: null;
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Consultas
    // ─────────────────────────────────────────────────────────────────────

    private const SELECT_BASE = "
        SELECT ai.*,
               i.nombre AS item_nombre, i.codigo_bn, i.estatus AS item_estatus,
               p.nombre  AS emp_nombre, p.apellido AS emp_apellido,
               uo.nombre AS ubicacion_origen, ud.nombre AS ubicacion_destino,
               TRIM(COALESCE(pa.nombre,'') || ' ' || COALESCE(pa.apellido,'')) AS autorizador
          FROM actividad_inventario ai
          INNER JOIN inventario  i  ON ai.id_inventario = i.id
          LEFT  JOIN empleados   e  ON ai.id_empleado_responsable = e.id
          LEFT  JOIN personas    p  ON e.id_persona = p.id
          LEFT  JOIN ubicaciones uo ON ai.id_ubicacion_origen  = uo.id
          LEFT  JOIN ubicaciones ud ON ai.id_ubicacion_destino = ud.id
          LEFT  JOIN empleados   ea ON ai.autorizado_por = ea.id
          LEFT  JOIN personas    pa ON ea.id_persona = pa.id
    ";

    public static function all() {
        $db = new Database();
        $db->query(self::SELECT_BASE . "
            WHERE ai.is_active = TRUE
            ORDER BY ai.fecha DESC, ai.id DESC");
        return $db->resultSet();
    }

    /** Hoja de vida del bien: todos sus movimientos, del más reciente al más antiguo (B-36). */
    public static function byItem($id_inventario) {
        $db = new Database();
        $db->query(self::SELECT_BASE . "
            WHERE ai.id_inventario = :id AND ai.is_active = TRUE
            ORDER BY ai.fecha DESC, ai.id DESC");
        $db->bind(':id', $id_inventario);
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT * FROM actividad_inventario WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Registro de movimientos (transaccional)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Registra un movimiento y aplica su efecto sobre el bien, todo dentro
     * de una transacción.
     *
     * $datos: id_inventario, tipo_movimiento, fecha, descripcion,
     *         id_ubicacion_destino, id_empleado_responsable, autorizado_por,
     *         y para mantenimiento: proveedor_externo, descripcion_falla,
     *         trabajo_realizado, costo, resultado.
     *
     * @throws Exception con un mensaje apto para mostrarle al usuario.
     */
    public static function registrarMovimiento(array $datos, $user_id = null): bool {
        $idBien = (int)($datos['id_inventario'] ?? 0);
        $tipo   = $datos['tipo_movimiento'] ?? '';
        $fecha  = $datos['fecha'] ?: date('Y-m-d');

        if (!in_array($tipo, self::TIPOS, true)) {
            throw new Exception('Tipo de movimiento no válido.');
        }

        $bien = Inventario::find($idBien);
        if (!$bien) {
            throw new Exception('El bien indicado no existe.');
        }
        // Un bien desincorporado ya no se mueve (B-38).
        if ($bien->estatus === Inventario::EST_BAJA) {
            throw new Exception('Este bien está dado de baja: ya no admite movimientos.');
        }
        // Sin código de la Alcaldía el bien aún no está formalmente inventariado.
        if ($bien->estatus === Inventario::EST_SIN_CODIFICAR && $tipo !== self::MOV_RESPONSABLE) {
            throw new Exception('El bien aún espera la codificación de la Alcaldía. '
                . 'Solo puede asignársele un responsable hasta que tenga su N° de orden.');
        }

        // B-32: la autorización de la Coordinadora de Bienes es obligatoria.
        $autorizadoPor = (int)($datos['autorizado_por'] ?? 0);
        if ($autorizadoPor <= 0) {
            throw new Exception('El movimiento debe ser autorizado por la Coordinación de Bienes.');
        }

        $origen  = (int)($bien->id_ubicacion ?? 0) ?: null;
        $destino = (int)($datos['id_ubicacion_destino'] ?? 0) ?: null;
        $resp    = (int)($datos['id_empleado_responsable'] ?? 0) ?: null;

        // ── Validaciones propias de cada tipo ────────────────────────────
        if ($tipo === self::MOV_TRASLADO) {
            if (!$destino) {
                throw new Exception('Indica la ubicación de destino del traslado.');
            }
            if ($destino === $origen) {
                throw new Exception('El bien ya se encuentra en esa ubicación.');
            }
        }
        if ($tipo === self::MOV_RESPONSABLE && !$resp) {
            throw new Exception('Indica el empleado que queda como responsable del bien.');
        }
        if ($tipo === self::MOV_SALIDA_MANT && $bien->estatus === Inventario::EST_MANTENIMIENTO) {
            throw new Exception('El bien ya está en mantenimiento.');
        }
        if ($tipo === self::MOV_RETORNO_MANT) {
            if ($bien->estatus !== Inventario::EST_MANTENIMIENTO) {
                throw new Exception('El bien no está en mantenimiento, no hay retorno que registrar.');
            }
            if (!Mantenimiento::abiertoDe($idBien)) {
                throw new Exception('No hay un mantenimiento abierto para este bien.');
            }
        }

        $db = new Database();
        try {
            $db->beginTransaction();

            // 1. La bitácora del movimiento
            $db->query("INSERT INTO actividad_inventario
                    (id_inventario, tipo_movimiento, descripcion, fecha,
                     id_empleado_responsable, id_ubicacion_origen, id_ubicacion_destino,
                     autorizado_por, created_by)
                 VALUES (:bien, :tipo, :desc, :fecha, :resp, :origen, :destino, :autoriza, :u)
                 RETURNING id");
            $db->bind(':bien',     $idBien);
            $db->bind(':tipo',     $tipo);
            $db->bind(':desc',     trim((string)($datos['descripcion'] ?? '')));
            $db->bind(':fecha',    $fecha);
            $db->bind(':resp',     $resp);
            $db->bind(':origen',   $origen);
            $db->bind(':destino',  $destino);
            $db->bind(':autoriza', $autorizadoPor);
            $db->bind(':u',        $user_id);
            $idMov = (int)($db->single()->id ?? 0);

            // 2. El efecto sobre el bien
            switch ($tipo) {
                case self::MOV_TRASLADO:
                    $db->query("UPDATE inventario SET id_ubicacion=:ubi,
                                    updated_at=CURRENT_TIMESTAMP, updated_by=:u WHERE id=:id");
                    $db->bind(':ubi', $destino);
                    $db->bind(':u',   $user_id);
                    $db->bind(':id',  $idBien);
                    $db->execute();
                    break;

                case self::MOV_RESPONSABLE:
                    $db->query("UPDATE inventario SET id_responsable=:r,
                                    updated_at=CURRENT_TIMESTAMP, updated_by=:u WHERE id=:id");
                    $db->bind(':r',  $resp);
                    $db->bind(':u',  $user_id);
                    $db->bind(':id', $idBien);
                    $db->execute();
                    break;

                case self::MOV_SALIDA_MANT:
                    // B-34: deja de estar disponible, pero sigue en el inventario.
                    $db->query("UPDATE inventario SET estatus=:est,
                                    updated_at=CURRENT_TIMESTAMP, updated_by=:u WHERE id=:id");
                    $db->bind(':est', Inventario::EST_MANTENIMIENTO);
                    $db->bind(':u',   $user_id);
                    $db->bind(':id',  $idBien);
                    $db->execute();
                    Mantenimiento::abrir($db, $idBien, $idMov, $fecha, $datos, $user_id);
                    break;

                case self::MOV_RETORNO_MANT:
                    Mantenimiento::cerrar($db, $idBien, $fecha, $datos, $user_id);
                    // Si el taller lo declaró irrecuperable, el bien no vuelve a estar
                    // operativo: queda dañado a la espera del acto de baja (Fase 3).
                    $irrecuperable = (($datos['resultado'] ?? '') === 'Irrecuperable');
                    $db->query("UPDATE inventario
                                   SET estatus = :est"
                                . ($irrecuperable ? ", condicion = 'Dañado'" : "") . ",
                                       updated_at=CURRENT_TIMESTAMP, updated_by=:u
                                 WHERE id=:id");
                    $db->bind(':est', Inventario::EST_ACTIVO);
                    $db->bind(':u',   $user_id);
                    $db->bind(':id',  $idBien);
                    $db->execute();
                    break;

                case self::MOV_BAJA:
                    $db->query("UPDATE inventario SET estatus=:est,
                                    updated_at=CURRENT_TIMESTAMP, updated_by=:u WHERE id=:id");
                    $db->bind(':est', Inventario::EST_BAJA);
                    $db->bind(':u',   $user_id);
                    $db->bind(':id',  $idBien);
                    $db->execute();
                    break;
            }

            $db->endTransaction();
        } catch (Exception $e) {
            $db->cancelTransaction();
            throw $e;
        }

        // La auditoría va fuera de la transacción: si el log falla, no debe
        // revertir un movimiento que ya es válido (convención del proyecto).
        self::auditStatic('actividad_inventario', 'INSERT', $idMov ?? null, null, [
            'id_inventario'   => $idBien,
            'tipo_movimiento' => $tipo,
            'origen'          => $origen,
            'destino'         => $destino,
            'autorizado_por'  => $autorizadoPor,
        ], $user_id);

        return true;
    }

    public static function delete($id, $user_id = null) {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE actividad_inventario SET is_active=FALSE,
                        deleted_at=CURRENT_TIMESTAMP, deleted_by=:user_id WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        $result = $db->execute();
        self::auditStatic('actividad_inventario', 'DELETE', $id, $previos, null, $user_id);
        return $result;
    }
}
