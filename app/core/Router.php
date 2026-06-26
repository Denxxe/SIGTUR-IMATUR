<?php
/**
 * Clase Router: Maneja las rutas amigables de la aplicación
 * URL: /controlador/metodo/parametro
 */
class Router {
    protected $currentController = 'DashboardController'; // Default controller
    protected $currentMethod = 'index'; // Default method
    protected $params = [];

    public function __construct() {
        $url = $this->getUrl();

        // 1. Verificar si existe el controlador
        if (isset($url[0])) {
            $controllerName = ucwords($url[0]) . 'Controller';
            if (file_exists('../app/controllers/' . $controllerName . '.php')) {
                $this->currentController = $controllerName;
                unset($url[0]);
            }
        }

        // --- Auth Middleware ---
        if (!isset($_SESSION['user_id']) && $this->currentController != 'AuthController') {
            $this->currentController = 'AuthController';
            $this->currentMethod = 'login';
            $url = [];
        }

        // --- Expiración de sesión por inactividad ---
        // Si pasó más de SESSION_TIMEOUT desde la última actividad, se cierra la
        // sesión y se redirige al login con aviso. Cada request renueva el reloj.
        if (isset($_SESSION['user_id']) && defined('SESSION_TIMEOUT')) {
            $ahora = time();
            if (isset($_SESSION['last_activity']) && ($ahora - (int)$_SESSION['last_activity']) > SESSION_TIMEOUT) {
                $_SESSION = [];
                session_destroy();
                header('Location: ' . URL_ROOT . '/auth/login?expired=1');
                exit;
            }
            $_SESSION['last_activity'] = $ahora;
        }

        // --- RBAC Middleware (Control de Acceso por Rol) ---
        if (isset($_SESSION['user_id']) && $this->currentController != 'AuthController') {
            $rolId = $_SESSION['user_rol'] ?? 0;
            require_once '../app/controllers/RolesController.php';
            $permisos   = RolesController::getMapaRbac();
            $permitidos = $permisos[$rolId] ?? [];
            if ($permitidos !== '*' && !in_array($this->currentController, $permitidos)) {
                // PerfilController: accesible por cualquier usuario autenticado (gestión propia de cuenta).
                $accesoSiempre = $this->currentController === 'PerfilController';

                // AuditoriaController tiene permisos a nivel de método.
                $accesoAuditoria = $this->currentController === 'AuditoriaController'
                    && is_array($permitidos)
                    && in_array('AuditoriaPapelera', $permitidos);

                if (!$accesoSiempre && !$accesoAuditoria) {
                    $this->currentController = 'DashboardController';
                    $this->currentMethod = 'accesoDenegado';
                    $url = [];
                }
            }
        }
        // -----------------------

        // 2. Requerir el controlador
        require_once '../app/controllers/' . $this->currentController . '.php';

        // 3. Instanciar el controlador
        $this->currentController = new $this->currentController;

        // 4. Verificar si existe el método en el controlador
        if (isset($url[1])) {
            if (method_exists($this->currentController, $url[1])) {
                $this->currentMethod = $url[1];
                unset($url[1]);
            }
        }

        // 5. Obtener los parámetros restantes
        $this->params = $url ? array_values($url) : [];

        // --- Idempotencia: token anti doble-envío en peticiones POST (B10) ---
        // Cada formulario lleva un token de un solo uso; un POST repetido con el
        // mismo token (doble clic, refrescar el POST, reintento) se ignora para no
        // duplicar registros. Exentos: login (vista sin footer/token) y los
        // endpoints AJAX de asistencia (idempotentes por diseño: marcan un booleano).
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_SESSION['user_id'])) {
            $ctrl   = get_class($this->currentController);
            $metodo = $this->currentMethod;
            $metodosExentos = ['marcarAsistencia', 'marcarAsistenciaMasiva'];
            $exento = ($ctrl === 'AuthController') || in_array($metodo, $metodosExentos, true);
            if (!$exento && !sigtur_token_consumir($_POST['_token'] ?? null)) {
                flash('global_msg', 'Solicitud duplicada o expirada: la operación no se repitió para evitar registros duplicados. Verifica si los datos ya se guardaron.', 'warning');
                $destino = $_SERVER['HTTP_REFERER'] ?? (URL_ROOT . '/dashboard');
                header('Location: ' . $destino);
                exit;
            }
        }

        // 6. Ejecutar el callback con parámetros
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    public function getUrl() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
    }
}
