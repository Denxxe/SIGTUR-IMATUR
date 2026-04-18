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
            $db->execute();

            // 2. Restauración en Cascada (Según requerimiento del usuario)
            if ($tabla == 'talleres') {
                // El soft-delete en este sistema mantiene los inscritos pero el taller está oculto.
                // No se requiere acción adicional de reversión mas que habilitar el padre.
            }

            if ($tabla == 'rutas') {
                // Restaurar puntos de ruta asociados
                $db->query("UPDATE puntos_ruta SET is_active = TRUE, deleted_at = NULL WHERE id_ruta = :id");
                $db->bind(':id', $id);
                $db->execute();
            }

            if ($tabla == 'personas') {
                // Si restauramos una persona, restauramos su vínculo como empleado si existía
                $db->query("UPDATE empleados SET is_active = TRUE, deleted_at = NULL WHERE id_persona = :id");
                $db->bind(':id', $id);
                $db->execute();
            }

            // 3. Registrar acción en Auditoría
            AuditLog::log($tabla, 'RESTORE', $id, ['is_active' => false], ['is_active' => true], $_SESSION['user_id']);

            $db->endTransaction();
            flash('auditoria_msg', 'Registro y sus dependencias han sido restaurados exitosamente.');
        } catch (Exception $e) {
            $db->cancelTransaction();
            flash('auditoria_msg', 'Error crítico en la restauración: ' . $e->getMessage(), 'alert alert-danger');
        }

        header('Location: ' . URL_ROOT . '/auditoria/papelera');
    }
}
