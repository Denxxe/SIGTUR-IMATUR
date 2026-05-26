<?php
class RolesController extends Controller {

    /**
     * Mapa RBAC: rol_id => controladores permitidos ('*' = acceso total).
     * Fuente única de verdad para permisos — Router.php lee esta misma estructura.
     * Cuando los permisos pasen a BD, solo se reemplaza este método.
     */
    public static function getMapaRbac(): array {
        return [
            1 => '*',
            2 => ['DashboardController','EmpleadosController','CargosController',
                  'DepartamentosController','AsistenciasController','VisitantesController',
                  'VisitasController','ReportesController','ConfigController'],
            3 => ['DashboardController','RutasController','ActividadesrutaController',
                  'TalleresController','UbicacionesformacionController','PasantesController',
                  'VisitantesController','VisitasController','ReportesController'],
            4 => ['DashboardController','InventarioController','CategoriasController',
                  'UbicacionesController','ActividadesinventarioController','ReportesController'],
            5 => ['DashboardController','VisitantesController','VisitasController',
                  'AsistenciasController'],
        ];
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
            'VisitantesController'            => ['label' => 'Visitantes',            'icon' => 'bi-people',            'grupo' => 'Atención'],
            'VisitasController'               => ['label' => 'Visitas',               'icon' => 'bi-journal-text',      'grupo' => 'Atención'],
            'RutasController'                 => ['label' => 'Rutas Turísticas',      'icon' => 'bi-map',               'grupo' => 'Turismo'],
            'TalleresController'              => ['label' => 'Talleres/Formación',    'icon' => 'bi-mortarboard',       'grupo' => 'Turismo'],
            'ActividadesrutaController'       => ['label' => 'Actividades de Ruta',   'icon' => 'bi-pin-map',           'grupo' => 'Turismo'],
            'UbicacionesformacionController'  => ['label' => 'Lugares de Formación',  'icon' => 'bi-geo-alt',           'grupo' => 'Turismo'],
            'PasantesController'              => ['label' => 'Pasantes',              'icon' => 'bi-person-workspace',  'grupo' => 'Turismo'],
            'InventarioController'            => ['label' => 'Inventario',            'icon' => 'bi-box-seam',          'grupo' => 'Inventario'],
            'CategoriasController'            => ['label' => 'Categorías',            'icon' => 'bi-tags',              'grupo' => 'Inventario'],
            'UbicacionesController'           => ['label' => 'Ubicaciones',           'icon' => 'bi-building',          'grupo' => 'Inventario'],
            'ActividadesinventarioController' => ['label' => 'Movimientos de Bienes', 'icon' => 'bi-arrow-left-right',  'grupo' => 'Inventario'],
            'UsuariosController'              => ['label' => 'Usuarios del Sistema',  'icon' => 'bi-shield-lock',       'grupo' => 'Sistema'],
            'RolesController'                 => ['label' => 'Roles y Permisos',      'icon' => 'bi-key',               'grupo' => 'Sistema'],
        ];
    }

    public function index() {
        $roles   = Rol::all();
        $rbac    = self::getMapaRbac();
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
