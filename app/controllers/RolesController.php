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
            'HorariosController'              => ['label' => 'Horarios',              'icon' => 'bi-clock',             'grupo' => 'RRHH'],
            'PermisosController'              => ['label' => 'Permisos y Reposos',    'icon' => 'bi-file-earmark-medical', 'grupo' => 'RRHH'],
            'VacacionesController'            => ['label' => 'Vacaciones',            'icon' => 'bi-airplane',           'grupo' => 'RRHH'],
            'NominaController'                => ['label' => 'Nómina',                'icon' => 'bi-cash-coin',          'grupo' => 'RRHH'],
            'AmonestacionesController'        => ['label' => 'Amonestaciones y Faltas', 'icon' => 'bi-exclamation-triangle', 'grupo' => 'RRHH'],
            'VisitantesController'            => ['label' => 'Recepción (Visitas)',  'icon' => 'bi-door-open',         'grupo' => 'Recepción'],
            'VisitasController'               => ['label' => 'Visitas (acceso directo)', 'icon' => 'bi-door-open',   'grupo' => 'Recepción'],
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
            // 'AuditoriaController' (Bitácora general) NO es asignable: es exclusiva del Administrador
            // (ver AuditoriaController::guardAdmin()). No agregar aquí.
            'AuditoriaPapelera'               => ['label' => 'Papelera de Reciclaje', 'icon' => 'bi-recycle',           'grupo' => 'Sistema'],
        ];
    }

    /**
     * Definición del MENÚ LATERAL (cierra H-12).
     *
     * Antes el sidebar tenía los permisos cableados por número de rol
     * (`in_array($rol, [1,2,3,5])`, 8 bloques), lo que contradecía al RBAC
     * dinámico: un rol creado desde *Roles y Permisos* recibía el permiso pero
     * no veía el enlace, y al revés, "Reportes" se mostraba a todos aunque el
     * Router lo denegara.
     *
     * Ahora el sidebar itera esta definición y resuelve la visibilidad con
     * `roleHasModulo()` — el MISMO mapa (`permisos_rol`) que aplica el Router.
     * Agregar un módulo al menú = agregar una fila aquí.
     *
     * La clave es el token de permiso (nombre del controlador, o pseudo-módulo
     * como 'AuditoriaPapelera'). `soloAdmin` marca lo NO delegable, que no vive
     * en `permisos_rol` y por tanto no se resuelve con `roleHasModulo()`:
     *   · Bitácora — exclusiva del Administrador (ver AuditoriaController::guardAdmin)
     *   · Municipios / Parroquias — catálogos geográficos, fuera de getModulos()
     *
     * El orden del array es el orden de aparición, y los grupos se dibujan según
     * su primera aparición. `DashboardController` no está aquí: el sidebar lo
     * pinta siempre, arriba y sin etiqueta de grupo.
     */
    public static function getNavegacion(): array {
        return [
            // ── RRHH ──────────────────────────────────────────────────────────
            'EmpleadosController'             => ['url' => '/empleados/index',            'label' => 'Empleados',          'icon' => 'bi-person-badge',     'grupo' => 'RRHH'],
            'CargosController'                => ['url' => '/cargos/index',               'label' => 'Cargos',             'icon' => 'bi-briefcase',        'grupo' => 'RRHH'],
            'DepartamentosController'         => ['url' => '/departamentos/index',        'label' => 'Departamentos',      'icon' => 'bi-building',         'grupo' => 'RRHH'],
            'HorariosController'              => ['url' => '/horarios/index',             'label' => 'Horarios',           'icon' => 'bi-clock',            'grupo' => 'RRHH'],
            'AmonestacionesController'        => ['url' => '/amonestaciones/index',       'label' => 'Amonestaciones',     'icon' => 'bi-flag',             'grupo' => 'RRHH'],
            'PermisosController'              => ['url' => '/permisos/index',             'label' => 'Permisos y Reposos', 'icon' => 'bi-calendar2-week',   'grupo' => 'RRHH'],
            'VacacionesController'            => ['url' => '/vacaciones/index',           'label' => 'Vacaciones',         'icon' => 'bi-umbrella',         'grupo' => 'RRHH'],
            'NominaController'                => ['url' => '/nomina/index',               'label' => 'Nómina',             'icon' => 'bi-cash-coin',        'grupo' => 'RRHH'],
            'AsistenciasController'           => ['url' => '/asistencias/index',          'label' => 'Asistencia',         'icon' => 'bi-clock-history',    'grupo' => 'RRHH'],

            // ── Recepción ─────────────────────────────────────────────────────
            // VisitasController queda fuera del menú: es acceso directo desde Visitantes.
            'VisitantesController'            => ['url' => '/visitantes/index',           'label' => 'Visitas',            'icon' => 'bi-door-open',        'grupo' => 'Recepción'],

            // ── Formación ─────────────────────────────────────────────────────
            'TalleresController'              => ['url' => '/talleres/index',             'label' => 'Talleres',           'icon' => 'bi-mortarboard',      'grupo' => 'Formación'],
            'UbicacionesformacionController'  => ['url' => '/ubicacionesformacion/index', 'label' => 'Sedes Formación',    'icon' => 'bi-pin-map',          'grupo' => 'Formación'],
            'PasantesController'              => ['url' => '/pasantes/index',             'label' => 'Pasantes',           'icon' => 'bi-person-video3',    'grupo' => 'Formación'],

            // ── Turismo ───────────────────────────────────────────────────────
            'RutasController'                 => ['url' => '/rutas/index',                'label' => 'Rutas Turísticas',   'icon' => 'bi-compass',          'grupo' => 'Turismo'],

            // ── Inventario ────────────────────────────────────────────────────
            'InventarioController'            => ['url' => '/inventario/index',           'label' => 'Bienes',             'icon' => 'bi-box-seam',         'grupo' => 'Inventario'],
            'CategoriasController'            => ['url' => '/categorias/index',           'label' => 'Categorías',         'icon' => 'bi-tags',             'grupo' => 'Inventario'],
            'UbicacionesController'           => ['url' => '/ubicaciones/index',          'label' => 'Ubicaciones',        'icon' => 'bi-geo-alt',          'grupo' => 'Inventario'],
            'ActividadesinventarioController' => ['url' => '/actividadesinventario/index','label' => 'Movimientos',        'icon' => 'bi-arrow-left-right', 'grupo' => 'Inventario'],

            // ── Análisis ──────────────────────────────────────────────────────
            'ReportesController'              => ['url' => '/reportes/index',             'label' => 'Reportes',           'icon' => 'bi-bar-chart-line',   'grupo' => 'Análisis'],

            // ── Sistema ───────────────────────────────────────────────────────
            'ConfigController'                => ['url' => '/config/index',               'label' => 'Configuración',      'icon' => 'bi-gear',             'grupo' => 'Sistema'],
            'UsuariosController'              => ['url' => '/usuarios/index',             'label' => 'Usuarios',           'icon' => 'bi-people',           'grupo' => 'Sistema'],
            'RolesController'                 => ['url' => '/roles/index',                'label' => 'Roles y Permisos',   'icon' => 'bi-shield-lock',      'grupo' => 'Sistema'],
            'MunicipioController'             => ['url' => '/municipio/index',            'label' => 'Municipios',         'icon' => 'bi-map',              'grupo' => 'Sistema', 'soloAdmin' => true],
            'ParroquiaController'             => ['url' => '/parroquia/index',            'label' => 'Parroquias',         'icon' => 'bi-signpost',         'grupo' => 'Sistema', 'soloAdmin' => true],
            'AuditoriaBitacora'               => ['url' => '/auditoria/index',            'label' => 'Auditoría',          'icon' => 'bi-shield-check',     'grupo' => 'Sistema', 'soloAdmin' => true],
            'AuditoriaPapelera'               => ['url' => '/auditoria/papelera',         'label' => 'Papelera',           'icon' => 'bi-recycle',          'grupo' => 'Sistema'],
        ];
    }

    /**
     * Menú lateral ya resuelto para el rol de la sesión: ['Grupo' => [item, ...]].
     * Los grupos sin ítems no se devuelven, así el sidebar no dibuja etiquetas huérfanas.
     */
    public static function getNavegacionVisible(): array {
        $rolId  = (int)($_SESSION['user_rol'] ?? 0);
        $grupos = [];
        foreach (self::getNavegacion() as $token => $item) {
            $permitido = !empty($item['soloAdmin'])
                ? ($rolId === 1)
                : self::roleHasModulo($token);
            if ($permitido) $grupos[$item['grupo']][] = $item;
        }
        return $grupos;
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
