<?php
/**
 * SIGTUR-IMATUR - Punto de entrada principal (Front Controller)
 */
session_start();

// Cargar configuración
require_once '../config/config.php';

// Cargar Clases base manualmente (Autoload simple)
spl_autoload_register(function($className) {
    $paths = [
        '../app/core/',
        '../app/controllers/',
        '../app/models/'
    ];

    foreach ($paths as $path) {
        if (file_exists($path . $className . '.php')) {
            require_once $path . $className . '.php';
            return;
        }
    }
});

// Arrancar el Router
$init = new Router();
