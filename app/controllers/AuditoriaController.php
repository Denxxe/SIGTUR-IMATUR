<?php
/**
 * Controlador AuditoriaController
 * Maneja la gestión de bitácoras y la papelera de reciclaje global.
 *
 * Permisos (RBAC a nivel de método, ver Router.php):
 *  - 'AuditoriaController' → Bitácora / log de auditoría (index)
 *  - 'AuditoriaPapelera'   → Papelera de Reciclaje (papelera, restaurar)
 * El Administrador (marcador '*') accede a todo.
 *
 * Las pestañas de la papelera se derivan de los módulos operativos del rol:
 * un rol ve una pestaña solo si ya gestiona ese módulo.
 */
class AuditoriaController extends Controller {

    /**
     * Catálogo de secciones de la papelera. Cada sección (pestaña) declara:
     *  - icon: ícono de la pestaña
     *  - modulos: controladores cuya posesión habilita la pestaña para el rol
     *  - items: submódulos con su tabla e ícono
     */
    private function catalogoPapelera(): array {
        return [
            'Recursos Humanos' => [
                'icon'    => 'bi-people-fill',
                'modulos' => ['EmpleadosController', 'CargosController', 'DepartamentosController'],
                'items'   => [
                    'Personal'      => ['tabla' => 'personas',      'icon' => 'bi-person-vcard'],
                    'Cargos'        => ['tabla' => 'cargos',        'icon' => 'bi-briefcase'],
                    'Departamentos' => ['tabla' => 'departamentos', 'icon' => 'bi-diagram-3'],
                ],
            ],
            'Inventario' => [
                'icon'    => 'bi-box-seam-fill',
                'modulos' => ['InventarioController', 'CategoriasController', 'UbicacionesController'],
                'items'   => [
                    'Bienes'      => ['tabla' => 'inventario',  'icon' => 'bi-box-seam'],
                    'Categorías'  => ['tabla' => 'categorias',  'icon' => 'bi-tags'],
                    'Ubicaciones' => ['tabla' => 'ubicaciones', 'icon' => 'bi-building'],
                ],
            ],
            'Formación' => [
                'icon'    => 'bi-mortarboard-fill',
                'modulos' => ['TalleresController', 'PasantesController', 'UbicacionesformacionController'],
                'items'   => [
                    'Talleres' => ['tabla' => 'talleres',              'icon' => 'bi-mortarboard'],
                    'Pasantes' => ['tabla' => 'pasantes',              'icon' => 'bi-person-workspace'],
                    'Sedes'    => ['tabla' => 'ubicaciones_formacion', 'icon' => 'bi-geo-alt'],
                ],
            ],
            'Rutas Turísticas' => [
                'icon'    => 'bi-map-fill',
                'modulos' => ['RutasController'],
                'items'   => [
                    'Rutas' => ['tabla' => 'rutas', 'icon' => 'bi-signpost-split'],
                ],
            ],
        ];
    }

    /** Bloquea el acceso si el rol no posee el token requerido. */
    private function guard(string $token): void {
        if (!RolesController::roleHasModulo($token)) {
            header('Location: ' . URL_ROOT . '/dashboard/accesoDenegado');
            exit;
        }
    }

    public function index() {
        $this->guard('AuditoriaController');
        $logs = AuditLog::all();
        $data = [
            'titulo' => 'Bitácora y Trazabilidad de Acciones',
            'logs' => $logs
        ];
        $this->view('auditoria/index', $data);
    }

    public function papelera() {
        $this->guard('AuditoriaPapelera');

        $catalogo  = $this->catalogoPapelera();
        $secciones = [];
        $totalGlobal = 0;

        foreach ($catalogo as $nombreSec => $sec) {
            // El rol ve la pestaña solo si tiene acceso a alguno de sus módulos.
            $visible = false;
            foreach ($sec['modulos'] as $m) {
                if (RolesController::roleHasModulo($m)) { $visible = true; break; }
            }
            if (!$visible) continue;

            $modulos    = [];
            $totalSec   = 0;
            foreach ($sec['items'] as $nombreMod => $info) {
                $items = AuditLog::getDeleted($info['tabla']);
                $totalSec += count($items);
                $modulos[$nombreMod] = [
                    'tabla' => $info['tabla'],
                    'icon'  => $info['icon'],
                    'items' => $items,
                ];
            }
            $totalGlobal += $totalSec;
            $secciones[$nombreSec] = [
                'icon'    => $sec['icon'],
                'total'   => $totalSec,
                'modulos' => $modulos,
            ];
        }

        $data = [
            'titulo'       => 'Papelera de Reciclaje Global',
            'secciones'    => $secciones,
            'total_global' => $totalGlobal,
        ];
        $this->view('auditoria/papelera', $data);
    }

    /**
     * Restaurar un registro borrado lógicamente.
     */
    public function restaurar($tabla, $id) {
        $this->guard('AuditoriaPapelera');

        $id = (int)$id;
        $tabla = strtolower(trim($tabla));

        // Defensa en profundidad: el rol solo puede restaurar tablas de las
        // pestañas a las que tiene acceso (un Admin con '*' pasa todas).
        $tablaPermitida = false;
        foreach ($this->catalogoPapelera() as $sec) {
            $tieneModulo = false;
            foreach ($sec['modulos'] as $m) {
                if (RolesController::roleHasModulo($m)) { $tieneModulo = true; break; }
            }
            if (!$tieneModulo) continue;
            foreach ($sec['items'] as $info) {
                if ($info['tabla'] === $tabla) { $tablaPermitida = true; break 2; }
            }
        }
        if (!$tablaPermitida) {
            flash('global_msg', 'No tiene permiso para restaurar registros de este módulo.', 'danger');
            header('Location: ' . URL_ROOT . '/auditoria/papelera');
            exit;
        }

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
