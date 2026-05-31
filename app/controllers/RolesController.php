<?php
class RolesController extends Controller {

    /**
     * Mapa RBAC leído desde la tabla permisos_rol.
     * Devuelve [id_rol => '*'] para acceso total o [id_rol => [ctrl, ...]] para limitado.
     * Router.php llama este método en cada request.
     */
    /** Caché por request para evitar consultas repetidas (header, router, controladores). */
    private static $cacheRbac = null;

    public static function getMapaRbac(): array {
        if (self::$cacheRbac !== null) return self::$cacheRbac;
        try {
            $db = new Database();
            $db->query("SELECT id_rol, modulo FROM permisos_rol ORDER BY id_rol, modulo");
            $rows = $db->resultSet();
            $mapa = [];
            foreach ($rows as $row) {
                $rolId = (int)$row->id_rol;
                if ($row->modulo === '*') {
                    $mapa[$rolId] = '*';
                } elseif (!isset($mapa[$rolId]) || $mapa[$rolId] !== '*') {
                    $mapa[$rolId][] = $row->modulo;
                }
            }
            self::$cacheRbac = $mapa;
            return $mapa;
        } catch (Exception $e) {
            // Fallback mínimo si la BD no responde
            return [1 => '*'];
        }
    }

    /**
     * ¿El rol de la sesión actual tiene acceso a un módulo/token dado?
     * El Administrador (marcador '*') tiene acceso a todo.
     */
    public static function roleHasModulo(string $modulo): bool {
        $rolId      = (int)($_SESSION['user_rol'] ?? 0);
        $mapa       = self::getMapaRbac();
        $permitidos = $mapa[$rolId] ?? [];
        if ($permitidos === '*') return true;
        return is_array($permitidos) && in_array($modulo, $permitidos);
    }

    /**
     * Etiquetas legibles por módulo (controlador → nombre + ícono + grupo).
     */
    public static function getModulos(): array {
        return [
            'DashboardController'             => ['label' => 'Panel Principal',      'icon' => 'bi-speedometer2',      'grupo' => 'General'],
            'ReportesController'              => ['label' => 'Reportes',              'icon' => 'bi-bar-chart-line',    'grupo' => 'General'],
            'ConfigController'                => ['label' => 'Configuración',         'icon' => 'bi-gear',              'grupo' => 'General'],
            'EmpleadosController'             => ['label' => 'Empleados',             'icon' => 'bi-person-badge',      'grupo' => 'RRHH'],
            'CargosController'                => ['label' => 'Cargos',                'icon' => 'bi-briefcase',         'grupo' => 'RRHH'],
            'DepartamentosController'         => ['label' => 'Departamentos',         'icon' => 'bi-diagram-3',         'grupo' => 'RRHH'],
            'AsistenciasController'           => ['label' => 'Asistencias',           'icon' => 'bi-calendar-check',   'grupo' => 'RRHH'],
            'VisitantesController'            => ['label' => 'Recepción (Visitas)',  'icon' => 'bi-door-open',         'grupo' => 'Recepción'],
            'TalleresController'              => ['label' => 'Talleres/Formación',    'icon' => 'bi-mortarboard',       'grupo' => 'Formación'],
            'UbicacionesformacionController'  => ['label' => 'Sedes de Formación',    'icon' => 'bi-geo-alt',           'grupo' => 'Formación'],
            'PasantesController'              => ['label' => 'Pasantes',              'icon' => 'bi-person-workspace',  'grupo' => 'Formación'],
            'RutasController'                 => ['label' => 'Rutas Turísticas',      'icon' => 'bi-map',               'grupo' => 'Turismo'],
            'InventarioController'            => ['label' => 'Inventario',            'icon' => 'bi-box-seam',          'grupo' => 'Inventario'],
            'CategoriasController'            => ['label' => 'Categorías',            'icon' => 'bi-tags',              'grupo' => 'Inventario'],
            'UbicacionesController'           => ['label' => 'Ubicaciones',           'icon' => 'bi-building',          'grupo' => 'Inventario'],
            'ActividadesinventarioController' => ['label' => 'Movimientos de Bienes', 'icon' => 'bi-arrow-left-right',  'grupo' => 'Inventario'],
            'UsuariosController'              => ['label' => 'Usuarios del Sistema',  'icon' => 'bi-shield-lock',       'grupo' => 'Sistema'],
            'RolesController'                 => ['label' => 'Roles y Permisos',      'icon' => 'bi-key',               'grupo' => 'Sistema'],
            'AuditoriaController'             => ['label' => 'Bitácora / Auditoría',  'icon' => 'bi-clipboard-data',    'grupo' => 'Sistema'],
            'AuditoriaPapelera'               => ['label' => 'Papelera de Reciclaje', 'icon' => 'bi-recycle',           'grupo' => 'Sistema'],
        ];
    }

    /**
     * Guarda los módulos asignados a un rol (reemplaza los permisos actuales).
     * El rol Administrador (id=1) no puede modificarse desde aquí.
     */
    public function storePermisos() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $_POST = $this->sanitizePost();
        $id_rol = (int)($_POST['id_rol'] ?? 0);
        $userId = $this->getUserId();

        if ($id_rol <= 0) {
            flash('global_msg', 'Rol inválido.', 'danger');
            header('Location: ' . URL_ROOT . '/roles/index');
            return;
        }
        if ($id_rol === 1) {
            flash('global_msg', 'El rol Administrador no puede modificarse desde aquí.', 'danger');
            header('Location: ' . URL_ROOT . '/roles/index');
            return;
        }

        $modulosValidos  = array_keys(self::getModulos());
        $modulosEnviados = (array)($_POST['modulos'] ?? []);

        // Filtrar solo módulos conocidos (whitelist)
        $modulosNuevos = array_filter($modulosEnviados, fn($m) => in_array($m, $modulosValidos));

        // DashboardController siempre incluido
        $modulosNuevos[] = 'DashboardController';
        $modulosNuevos   = array_unique($modulosNuevos);

        try {
            $db = new Database();
            $db->beginTransaction();

            $previos = [];
            $db->query("SELECT modulo FROM permisos_rol WHERE id_rol = :id");
            $db->bind(':id', $id_rol);
            foreach ($db->resultSet() as $r) $previos[] = $r->modulo;

            $db->query("DELETE FROM permisos_rol WHERE id_rol = :id");
            $db->bind(':id', $id_rol);
            $db->execute();

            foreach ($modulosNuevos as $modulo) {
                $db->query("INSERT INTO permisos_rol (id_rol, modulo, created_by) VALUES (:r, :m, :u)");
                $db->bind(':r', $id_rol);
                $db->bind(':m', $modulo);
                $db->bind(':u', $userId);
                $db->execute();
            }

            $db->endTransaction();

            try {
                AuditLog::log('permisos_rol', 'UPDATE', $id_rol,
                    ['modulos' => implode(',', $previos)],
                    ['modulos' => implode(',', $modulosNuevos)],
                    $userId);
            } catch (Exception $ae) {
                error_log('AuditLog storePermisos: ' . $ae->getMessage());
            }

            flash('global_msg', 'Permisos del rol actualizados correctamente.');
        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) $db->cancelTransaction();
            flash('global_msg', 'Error al guardar permisos: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/roles/index');
    }

    public function index() {
        $roles   = Rol::all();
        $rbac    = self::getMapaRbac();   // ahora viene de BD
        $modulos = self::getModulos();

        $data = [
            'titulo'  => 'Roles y Permisos',
            'roles'   => $roles,
            'rbac'    => $rbac,
            'modulos' => $modulos,
        ];
        $this->view('roles/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $_POST = $this->sanitizePost();
        $data  = [
            'id'          => !empty($_POST['id']) ? (int)$_POST['id'] : null,
            'nombre'      => trim($_POST['nombre']),
            'descripcion' => trim($_POST['descripcion']),
        ];

        $rol = new Rol($data);
        try {
            if ($rol->save($this->getUserId())) {
                $msg = $data['id'] ? 'Rol actualizado.' : 'Rol creado.';
                flash('global_msg', $msg);
            } else {
                throw new Exception('Error interno al guardar el rol.');
            }
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/roles/index');
    }

    public function delete($id) {
        if ((int)$id === 1) {
            flash('global_msg', 'El rol Administrador es inmutable y no puede eliminarse.', 'danger');
            header('Location: ' . URL_ROOT . '/roles/index');
            return;
        }
        try {
            if (Rol::delete($id, $this->getUserId())) {
                flash('global_msg', 'Rol eliminado.', 'warning');
            } else {
                throw new Exception('No se pudo eliminar el rol.');
            }
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/roles/index');
    }
}
