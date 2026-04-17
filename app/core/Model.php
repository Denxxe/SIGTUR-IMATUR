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
            AuditLog::log($tabla, $operacion, $record_id, $previos, $nuevos, $user_id);
        } catch (\Exception $e) {
            // La auditoría nunca debe romper la operación principal
            error_log("AuditLog Error: " . $e->getMessage());
        }
    }

    /**
     * Helper estático para auditoría en métodos delete() estáticos
     */
    protected static function auditStatic($tabla, $operacion, $record_id, $previos = null, $nuevos = null, $user_id = null) {
        try {
            AuditLog::log($tabla, $operacion, $record_id, $previos, $nuevos, $user_id);
        } catch (\Exception $e) {
            error_log("AuditLog Error: " . $e->getMessage());
        }
    }
}
