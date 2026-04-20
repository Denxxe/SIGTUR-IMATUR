<?php
/**
 * Controlador AuditoriaController
 * Maneja la gestión de bitácoras y la papelera de reciclaje global.
 */
class AuditoriaController extends Controller {

    public function index() {
        $logs = AuditLog::all();
        $data = [
            'titulo' => 'Bitácora y Trazabilidad de Acciones',
            'logs' => $logs
        ];
        $this->view('auditoria/index', $data);
    }

    public function papelera() {
        $data = [
            'titulo' => 'Papelera de Reciclaje Global',
            'secciones' => [
                'Recursos Humanos' => [
                    'Personal' => ['tabla' => 'personas', 'items' => AuditLog::getDeleted('personas')],
                    'Cargos' => ['tabla' => 'cargos', 'items' => AuditLog::getDeleted('cargos')],
                    'Departamentos' => ['tabla' => 'departamentos', 'items' => AuditLog::getDeleted('departamentos')]
                ],
                'Inventario' => [
                    'Bienes' => ['tabla' => 'inventario', 'items' => AuditLog::getDeleted('inventario')],
                    'Categorías' => ['tabla' => 'categorias', 'items' => AuditLog::getDeleted('categorias')],
                    'Ubicaciones' => ['tabla' => 'ubicaciones', 'items' => AuditLog::getDeleted('ubicaciones')]
                ],
                'Formación' => [
                    'Talleres' => ['tabla' => 'talleres', 'items' => AuditLog::getDeleted('talleres')],
                    'Pasantes' => ['tabla' => 'pasantes', 'items' => AuditLog::getDeleted('pasantes')],
                    'Sedes' => ['tabla' => 'ubicaciones_formacion', 'items' => AuditLog::getDeleted('ubicaciones_formacion')]
                ],
                'Rutas Turísticas' => [
                    'Rutas' => ['tabla' => 'rutas', 'items' => AuditLog::getDeleted('rutas')]
                ]
            ]
        ];
        $this->view('auditoria/papelera', $data);
    }

    /**
     * Restaurar un registro borrado lógicamente.
     */
    public function restaurar($tabla, $id) {
        $id = (int)$id;
        $tabla = strtolower(trim($tabla));
        
        // Creamos instancia única de conexión para asegurar integridad transaccional
        $db = new Database();
        $db->beginTransaction();

        try {
            // 1. Verificar existencia y estado actual
            $db->query("SELECT id FROM $tabla WHERE id = :id AND is_active = FALSE");
            $db->bind(':id', $id);
            $check = $db->single();
            
            if (!$check) {
                throw new Exception("El registro con ID $id no pudo ser localizado en la papelera para la tabla '$tabla'.");
            }

            // 2. Restaurar registro principal
            $db->query("UPDATE $tabla SET is_active = TRUE, deleted_at = NULL, deleted_by = NULL WHERE id = :id");
            $db->bind(':id', $id);
            $db->execute();
            
            if ($db->rowCount() === 0) {
                throw new Exception("No se detectaron cambios al intentar restaurar el registro principal.");
            }

            // 3. Restauración en Cascada (Dependencias críticas)
            if ($tabla == 'personas') {
                $db->query("UPDATE empleados SET is_active = TRUE, deleted_at = NULL WHERE id_persona = :id");
                $db->bind(':id', $id);
                $db->execute();
            }

            if ($tabla == 'rutas') {
                $db->query("UPDATE puntos_ruta SET is_active = TRUE, deleted_at = NULL WHERE id_ruta = :id");
                $db->bind(':id', $id);
                $db->execute();
            }

            // 4. Registrar acción en Auditoría (Pasamos la instancia $db para que se guarde en la misma transacción)
            AuditLog::log($tabla, 'UPDATE', $id, ['is_active' => false], ['is_active' => true], $_SESSION['user_id'], $db);

            // 5. Commit y validación de persistencia física
            if (!$db->endTransaction()) {
                throw new Exception("La base de datos rechazó la confirmación de la restauración (Commit Fallido).");
            }

            flash('global_msg', '¡Restauración completada con éxito! El registro y sus vínculos han sido reactivados.');
            
        } catch (Exception $e) {
            $db->cancelTransaction();
            flash('global_msg', 'Error crítico de restauración: ' . $e->getMessage(), 'danger');
        }

        header('Location: ' . URL_ROOT . '/auditoria/papelera');
        exit;
    }
}
