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
            $url = []; // Limpiar parámetros para no pasarlos a login
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
