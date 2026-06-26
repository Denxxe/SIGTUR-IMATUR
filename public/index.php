<?php
/**
 * SIGTUR-IMATUR - Punto de entrada principal (Front Controller)
 */

// Cargar configuración (define APP_DEBUG, credenciales, URL_ROOT, etc.)
require_once '../config/config.php';

// ── Manejo de errores según entorno ───────────────────────────────────────
// En PRODUCCIÓN (APP_DEBUG=false) no se muestran errores al usuario (evita
// filtrar rutas/SQL); siempre se registran en el log del servidor.
$__debug = defined('APP_DEBUG') && APP_DEBUG;
ini_set('display_errors', $__debug ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// ── Cookie de sesión endurecida ───────────────────────────────────────────
// httponly: inaccesible a JS (mitiga robo por XSS); samesite Lax: mitiga CSRF;
// secure: solo bajo HTTPS (se activa solo cuando la conexión es segura).
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'samesite' => 'Lax',
]);
session_start();

// Cargar Helpers
require_once '../app/helpers/session_helper.php';

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

// ── Captura global de excepciones no controladas ──────────────────────────
// Evita la "pantalla en blanco" o el volcado de traza al usuario en producción.
set_exception_handler(function (\Throwable $e) use ($__debug) {
    error_log('[SIGTUR] Excepción no controlada: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) http_response_code(500);
    if ($__debug) {
        echo '<pre style="padding:1rem;font-family:monospace;white-space:pre-wrap;">'
           . htmlspecialchars((string)$e) . '</pre>';
        return;
    }
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>Error - SIGTUR-IMATUR</title></head><body>'
       . '<div style="font-family:system-ui,Segoe UI,sans-serif;max-width:480px;margin:12vh auto;text-align:center;color:#334155;padding:0 1rem;">'
       . '<h1 style="font-size:1.4rem;margin-bottom:.5rem;">Ocurrió un error</h1>'
       . '<p>No se pudo completar la operación. Intenta nuevamente; si el problema persiste, contacta al administrador del sistema.</p>'
       . '</div></body></html>';
});

// Arrancar el Router
$init = new Router();
