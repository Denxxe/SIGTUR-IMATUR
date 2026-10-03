<?php

/**
 * SIGTUR-IMATUR — configuración para Docker.
 *
 * El Dockerfile copia este archivo a config/config.php. No lleva credenciales:
 * todo sale de las variables de entorno que docker-compose toma del archivo .env
 * (plantilla: .env.example). Para un servidor sin Docker se sigue usando
 * config/config.example.php.
 */

$env = function (string $clave, $porDefecto = '') {
    $v = getenv($clave);
    return ($v === false || $v === '') ? $porDefecto : $v;
};

// ── URL base ─────────────────────────────────────────────────────────────────
// Los enlaces se arman con URL_ROOT, así que tiene que ser la dirección con la
// que el usuario entró: la IP local desde el teléfono, el túnel desde internet.
// APP_URLS es la lista de direcciones PERMITIDAS (separadas por coma); se usa la
// que coincide con la petición y, si ninguna coincide, la primera. Una dirección
// que no esté en la lista nunca se acepta: si se tomara el Host tal cual, alguien
// podría falsificarlo y recibir enlaces de recuperación de contraseña hacia su
// propio dominio.
$permitidas = array_values(array_filter(array_map(
    fn($u) => rtrim(trim($u), '/'),
    explode(',', $env('APP_URLS', 'http://localhost:8080'))
)));
$urlRoot = $permitidas[0] ?? 'http://localhost:8080';
if (!empty($_SERVER['HTTP_HOST'])) {
    $esquema = !empty($_SERVER['HTTP_X_FORWARDED_PROTO'])
        ? strtolower(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'])[0])
        : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
    $actual = $esquema . '://' . strtolower($_SERVER['HTTP_HOST']);
    foreach ($permitidas as $u) {
        if (strtolower($u) === $actual) { $urlRoot = $u; break; }
    }
}
define('URL_ROOT', $urlRoot);

define('SITE_NAME', 'SIGTUR-IMATUR');
define('APP_DEBUG', $env('APP_DEBUG', 'false') === 'true');
define('APP_TIMEZONE', $env('APP_TIMEZONE', 'America/Caracas'));

// ── Base de datos (el servicio `db` de docker-compose) ───────────────────────
define('DB_HOST', $env('DB_HOST', 'db'));
define('DB_PORT', $env('DB_PORT', '5432'));
define('DB_USER', $env('DB_USER', 'postgres'));
define('DB_PASS', $env('DB_PASS'));
define('DB_NAME', $env('DB_NAME', 'SIGTUR-IMATUR'));

define('APP_ROOT', dirname(dirname(__FILE__)));
define('SESSION_TIMEOUT', (int)$env('SESSION_TIMEOUT', 1800));

// ── Respaldos (los corre el servicio `tareas`) ───────────────────────────────
define('PG_DUMP_PATH', '/usr/bin/pg_dump');
define('BACKUP_RETENTION', (int)$env('BACKUP_RETENTION', 14));

// ── Correo saliente (recuperación de contraseña) ────────────────────────────
define('SMTP_HOST', $env('SMTP_HOST', 'CAMBIAR_HOST_SMTP'));
define('SMTP_PORT', (int)$env('SMTP_PORT', 587));
define('SMTP_USER', $env('SMTP_USER', 'CAMBIAR_USUARIO_SMTP'));
define('SMTP_PASS', $env('SMTP_PASS', 'CAMBIAR_CLAVE_SMTP'));
define('SMTP_ENCRYPTION', $env('SMTP_ENCRYPTION', 'tls'));
define('SMTP_FROM_EMAIL', $env('SMTP_FROM_EMAIL', 'no-responder@imatur.gob.ve'));
define('SMTP_FROM_NAME', 'SIGTUR-IMATUR');
