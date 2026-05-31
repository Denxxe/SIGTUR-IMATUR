<?php
/**
 * Clase Model: Clase base para todos los modelos
 * Incluye helpers de auditoría automática
 */
class Model {
    protected $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Registrar auditoría automática desde cualquier modelo hijo
     * @param string $tabla    Nombre de la tabla afectada
     * @param string $operacion INSERT|UPDATE|DELETE
     * @param int    $record_id ID del registro afectado
     * @param mixed  $previos   Datos previos (objeto o array) — null en INSERT
     * @param mixed  $nuevos    Datos nuevos (array) — null en DELETE
     * @param int    $user_id   ID del usuario responsable
     */
    protected function audit($tabla, $operacion, $record_id, $previos = null, $nuevos = null, $user_id = null) {
        try {
            // En UPDATE, capturar el estado COMPLETO del registro tras la edición
            // re-leyéndolo en la MISMA conexión (ve los cambios aún sin confirmar).
            // Así el diff de la bitácora refleja todos los campos modificados, no solo
            // el subconjunto que cada modelo arma a mano.
            if ($operacion === 'UPDATE' && $record_id && isset($this->db)) {
                $full = self::fetchFullRow($this->db->getHandler(), $tabla, (int)$record_id);
                if ($full !== null) $nuevos = $full;
            }
            AuditLog::log($tabla, $operacion, $record_id, self::toArray($previos), self::toArray($nuevos), $user_id);
        } catch (\Exception $e) {
            error_log("AuditLog Error: " . $e->getMessage());
        }
    }

    /**
     * Helper estático para auditoría en métodos delete()/update() estáticos
     */
    protected static function auditStatic($tabla, $operacion, $record_id, $previos = null, $nuevos = null, $user_id = null) {
        try {
            if ($operacion === 'UPDATE' && $record_id) {
                $tmp  = new Database();
                $full = self::fetchFullRow($tmp->getHandler(), $tabla, (int)$record_id);
                if ($full !== null) $nuevos = $full;
            }
            AuditLog::log($tabla, $operacion, $record_id, self::toArray($previos), self::toArray($nuevos), $user_id);
        } catch (\Exception $e) {
            error_log("AuditLog Error: " . $e->getMessage());
        }
    }

    /**
     * Lee el registro completo (PK = id) usando un handler PDO dado, sin tocar el
     * statement del wrapper Database. Degrada a null ante cualquier error
     * (p. ej. tabla sin columna 'id'), de modo que el llamador conserve $nuevos.
     */
    private static function fetchFullRow($pdo, string $tabla, int $id): ?array {
        if (!preg_match('/^[a-z_]+$/', $tabla)) return null; // whitelist del nombre de tabla
        try {
            $stmt = $pdo->prepare("SELECT * FROM {$tabla} WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row === false) return null;
            unset($row['password']); // nunca registrar credenciales en la bitácora
            return $row;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private static function toArray($val): ?array {
        if ($val === null) return null;
        if (is_array($val)) return $val;
        return (array) $val;
    }
}
