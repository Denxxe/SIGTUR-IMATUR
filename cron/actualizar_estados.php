<?php
/**
 * Tarea programada — Transiciones automáticas por fecha/hora.
 *
 * Hoy: Talleres "Programado" → "En Curso" cuando llega su fecha/hora de inicio
 * (y tienen participantes). Pensado para ejecutarse periódicamente desde el
 * Programador de tareas de Windows o el cron de Laragon, de modo que el estado
 * se actualice aunque nadie tenga abierto el sistema.
 *
 * Uso (CLI):
 *   php cron/actualizar_estados.php
 *
 * Programar en Windows (cada 10 min), por ejemplo:
 *   schtasks /Create /SC MINUTE /MO 10 /TN "SIGTUR-Estados" ^
 *     /TR "C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe C:\laragon\www\SIGTUR-IMATUR\cron\actualizar_estados.php"
 *   (ajusta la ruta de php.exe a tu versión instalada)
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Solo CLI.');
}

$base = dirname(__DIR__);
require_once $base . '/config/config.php';

// Hora de Venezuela para date() y para la sesión de PostgreSQL (ver Database).
// Sin esto PHP y PostgreSQL trabajaban en UTC: 4 horas adelantados (las horas
// de asistencia y la fecha de lo emitido después de las 8 p. m. salían mal).
if (!defined('APP_TIMEZONE')) define('APP_TIMEZONE', 'America/Caracas');
date_default_timezone_set(APP_TIMEZONE);

spl_autoload_register(function ($class) use ($base) {
    foreach (['/app/core/', '/app/controllers/', '/app/models/'] as $p) {
        $file = $base . $p . $class . '.php';
        if (file_exists($file)) { require_once $file; return; }
    }
});

$ts = date('Y-m-d H:i:s');
try {
    Taller::autoTransicionarProgramados();
    echo "[$ts] OK — talleres Programado→En Curso actualizados.\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "[$ts] ERROR — " . $e->getMessage() . "\n");
    exit(1);
}
