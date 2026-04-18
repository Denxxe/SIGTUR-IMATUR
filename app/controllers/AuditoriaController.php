<?php
/**
 * Controlador AuditoriaController
 * Maneja la gestión de bitácoras y la papelera de reciclaje global.
 * Acceso restringido solo a Administradores.
 */
class AuditoriaController extends Controller {

    /**
     * Vista de Bitácora (Historial de acciones)
     */
    public function index() {
        $logs = AuditLog::all();

        $data = [
            'titulo' => 'Bitácora y Trazabilidad de Acciones',
            'logs' => $logs
        ];

        $this->view('auditoria/index', $data);
    }

    /**
     * Vista de Papelera de Reciclaje (Registros is_active = false)
     * Organizado por pestañas modulares.
     */
    public function papelera() {
        $data = [
            'titulo' => 'Papelera de Reciclaje Global',
            'secciones' => [
                'Recursos Humanos' => [
                    'Personal/Personas' => ['tabla' => 'personas', 'items' => AuditLog::getDeleted('personas')],
                    'Cargos' => ['tabla' => 'cargos', 'items' => AuditLog::getDeleted('cargos')],
                    'Departamentos' => ['tabla' => 'departamentos', 'items' => AuditLog::getDeleted('departamentos')]
                ],
                'Inventario' => [
                    'Bienes Institucionales' => ['tabla' => 'inventario', 'items' => AuditLog::getDeleted('inventario')],
                    'Categorías' => ['tabla' => 'categorias', 'items' => AuditLog::getDeleted('categorias')],
                    'Ubicaciones/Almacenes' => ['tabla' => 'ubicaciones', 'items' => AuditLog::getDeleted('ubicaciones')]
                ],
                'Formación' => [
                    'Talleres' => ['tabla' => 'talleres', 'items' => AuditLog::getDeleted('talleres')],
                    'Pasantes' => ['tabla' => 'pasantes', 'items' => AuditLog::getDeleted('pasantes')],
                    'Sedes de Formación' => ['tabla' => 'ubicaciones_formacion', 'items' => AuditLog::getDeleted('ubicaciones_formacion')]
                ],
                'Rutas Turísticas' => [
                    'Rutas' => ['tabla' => 'rutas', 'items' => AuditLog::getDeleted('rutas')]
                ]
            ]
        ];

        $this->view('auditoria/papelera', $data);
    }

    /**
     * Restaurar un registro borrado lógicamente con lógica de cascada.
     */
    public function restaurar($tabla, $id) {
        $db = new Database();
        $db->beginTransaction();

        try {
            // 1. Restaurar registro principal
            $db->query("UPDATE $tabla SET is_active = TRUE, deleted_at = NULL, deleted_by = NULL WHERE id = :id");
            $db->bind(':id', $id);
            if (!$db->execute()) {
                throw new Exception("No se pudo actualizar el registro principal en $tabla.");
            }

            // 2. Restauración en Cascada
            if ($tabla == 'rutas') {
                $db->query("UPDATE puntos_ruta SET is_active = TRUE, deleted_at = NULL WHERE id_ruta = :id");
                $db->bind(':id', $id);
                $db->execute();
            }

            if ($tabla == 'personas') {
                $db->query("UPDATE empleados SET is_active = TRUE, deleted_at = NULL WHERE id_persona = :id");
                $db->bind(':id', $id);
                $db->execute();
            }

            // 3. Registrar acción en Auditoría
            // Usamos 'UPDATE' en lugar de 'RESTORE' para cumplir con la restricción CHECK de la base de datos actual.
            AuditLog::log($tabla, 'UPDATE', $id, ['is_active' => false], ['is_active' => true], $_SESSION['user_id']);

            $db->endTransaction();
            flash('global_msg', '¡Registro restaurado exitosamente! Los datos y sus asociaciones vuelven a estar vigentes.');
        } catch (Exception $e) {
            $db->cancelTransaction();
            flash('global_msg', 'No se pudo restaurar el registro: ' . $e->getMessage(), 'danger');
        }

        header('Location: ' . URL_ROOT . '/auditoria/papelera');
    }
}
