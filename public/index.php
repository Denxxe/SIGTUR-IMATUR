<?php
/**
 * SIGTUR-IMATUR - Punto de entrada principal (Front Controller)
 */

// Cargar configuración
require_once '../config/config.php';

// Cargar Clases base manualmente (Autoload simple)
spl_autoload_register(function($className) {
    if (file_exists('../app/core/' . $className . '.php')) {
        require_once '../app/core/' . $className . '.php';
    }
});

// Arrancar el Router
$init = new Router();
