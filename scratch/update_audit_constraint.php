<?php
require_once 'config/config.php';
require_once 'app/core/Database.php';

$db = new Database();

try {
    echo "Actualizando restricción de audit_logs...\n";
    
    // 1. Eliminar la restricción antigua (PostgreSQL suele ponerles nombres automáticos o fijos)
    // Buscamos el nombre de la restricción de check para 'operacion'
    $db->query("SELECT conname FROM pg_constraint WHERE conrelid = 'audit_logs'::regclass AND contype = 'c'");
    $constraints = $db->resultSet();
    
    foreach ($constraints as $c) {
        $db->query("ALTER TABLE audit_logs DROP CONSTRAINT " . $c->conname);
        $db->execute();
    }

    // 2. Añadir la nueva restricción con 'RESTORE' incluido
    $db->query("ALTER TABLE audit_logs ADD CONSTRAINT audit_logs_operacion_check CHECK (operacion IN ('INSERT', 'UPDATE', 'DELETE', 'RESTORE'))");
    $db->execute();

    echo "✅ Restricción actualizada con éxito. Ahora se permite la operación 'RESTORE'.\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
