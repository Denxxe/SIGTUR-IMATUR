<?php
/**
 * Tarea programada — Respaldo automático de la base de datos.
 *
 * Genera un volcado completo (pg_dump, formato SQL plano) en
 * storage/backups/ con nombre fechado, y rota los más antiguos conservando
 * los últimos BACKUP_RETENTION. Pensado para correr periódicamente desde el
 * Programador de tareas de Windows, de modo que siempre exista una copia
 * reciente aunque nadie use el sistema. La carpeta está FUERA de public/
 * (no accesible por web).
 *
 * Uso (CLI):
 *   php cron/respaldo_bd.php
 *
 * Programar en Windows (diario a las 11:00 pm, por ejemplo):
 *   schtasks /Create /SC DAILY /ST 23:00 /TN "SIGTUR-Respaldo" ^
 *     /TR "C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe C:\laragon\www\SIGTUR-IMATUR\cron\respaldo_bd.php"
 *   (ajusta las rutas de php.exe y del proyecto a tu entorno)
 *
 * Restaurar un respaldo (en una BD vacía):
 *   1) createdb -U postgres "SIGTUR-IMATUR"
 *   2) psql -U postgres -d "SIGTUR-IMATUR" -f storage/backups/sigtur_YYYY-MM-DD_HHMMSS.sql
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Solo CLI.');
}

$base = dirname(__DIR__);
require_once $base . '/config/config.php';

$ts     = date('Y-m-d H:i:s');
$dir    = $base . '/storage/backups';
$logF   = $dir . '/_backup.log';

/** Escribe en el log y en stdout/stderr. */
function bk_log(string $dir, string $msg, bool $err = false): void {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
    @file_put_contents($dir . '/_backup.log', $line, FILE_APPEND);
    if ($err) fwrite(STDERR, $line); else echo $line;
}

// 1. Asegurar carpeta de respaldos
if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
    fwrite(STDERR, "[$ts] ERROR — no se pudo crear la carpeta de respaldos: $dir\n");
    exit(1);
}

// 2. Localizar pg_dump
$pgDump = defined('PG_DUMP_PATH') ? PG_DUMP_PATH : 'pg_dump';
if (defined('PG_DUMP_PATH') && !is_file($pgDump)) {
    // Fallback razonable si la ruta configurada no existe
    foreach ([
        'C:\\Program Files\\PostgreSQL\\17\\bin\\pg_dump.exe',
        'C:\\Program Files\\PostgreSQL\\16\\bin\\pg_dump.exe',
        'pg_dump',
    ] as $cand) {
        if ($cand === 'pg_dump' || is_file($cand)) { $pgDump = $cand; break; }
    }
}

// 3. Ejecutar pg_dump (formato SQL plano)
$outFile = $dir . '/sigtur_' . date('Y-m-d_His') . '.sql';
putenv('PGPASSWORD=' . DB_PASS);  // pg_dump lee la contraseña del entorno (no en la línea de comandos)

$cmd = escapeshellarg($pgDump)
     . ' -h ' . escapeshellarg(DB_HOST)
     . ' -p ' . escapeshellarg((string)DB_PORT)
     . ' -U ' . escapeshellarg(DB_USER)
     . ' -d ' . escapeshellarg(DB_NAME)
     . ' -F p --no-owner --no-privileges'
     . ' -f ' . escapeshellarg($outFile)
     . ' 2>&1';

$salida = []; $code = 0;
exec($cmd, $salida, $code);

if ($code !== 0 || !is_file($outFile) || filesize($outFile) === 0) {
    @unlink($outFile); // no dejar un respaldo vacío/corrupto
    bk_log($dir, 'ERROR — pg_dump falló (código ' . $code . '): ' . trim(implode(' | ', $salida)), true);
    exit(1);
}

$tam = round(filesize($outFile) / 1024, 1);
bk_log($dir, 'OK — respaldo creado: ' . basename($outFile) . ' (' . $tam . ' KB)');

// 4. Rotación: conservar solo los últimos BACKUP_RETENTION
$retencion = defined('BACKUP_RETENTION') ? max(1, (int)BACKUP_RETENTION) : 14;
$archivos = glob($dir . '/sigtur_*.sql') ?: [];
sort($archivos); // nombre fechado → orden cronológico ascendente
$sobrantes = count($archivos) - $retencion;
for ($i = 0; $i < $sobrantes; $i++) {
    if (@unlink($archivos[$i])) {
        bk_log($dir, 'Rotación — eliminado respaldo antiguo: ' . basename($archivos[$i]));
    }
}

exit(0);
