<?php

/**
 * SIGTUR-IMATUR — Plantilla de configuración.
 *
 * COPIAR este archivo a `config/config.php` y completar con los valores reales
 * del entorno. `config/config.php` NO se versiona (está en .gitignore) para no
 * exponer credenciales. Cada servidor (local/producción) tiene el suyo.
 */

// URL Base de la aplicación (ajustar por entorno; sin barra final)
define('URL_ROOT', 'http://localhost/SIGTUR-IMATUR');

// Nombre del Sitio
define('SITE_NAME', 'SIGTUR-IMATUR');

// Modo depuración: en PRODUCCIÓN debe ser false (no muestra errores al usuario).
define('APP_DEBUG', false);

// Configuración de la Base de Datos (PostgreSQL) — usar credenciales propias.
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_USER', 'postgres');
define('DB_PASS', 'CAMBIAR_ESTA_CONTRASEÑA');
define('DB_NAME', 'SIGTUR-IMATUR');

// Carpeta de la aplicación (Raíz del proyecto)
define('APP_ROOT', dirname(dirname(__FILE__)));

// Seguridad: expiración de sesión por inactividad (segundos). 1800 = 30 minutos.
define('SESSION_TIMEOUT', 1800);

// Respaldos de BD (tarea programada cron/respaldo_bd.php)
//   PG_DUMP_PATH: ruta a pg_dump.exe (ajustar a la versión instalada).
//   BACKUP_RETENTION: cuántos respaldos conservar (rota los más antiguos).
define('PG_DUMP_PATH', 'C:\\Program Files\\PostgreSQL\\17\\bin\\pg_dump.exe');
define('BACKUP_RETENTION', 14);
