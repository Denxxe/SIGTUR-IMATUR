<?php
/**
 * Tarea programada — Respaldo automático de la base de datos.
 *
 * Genera un volcado completo (pg_dump, formato SQL plano) en
 * storage/backups/ con nombre fechado y, junto a él, un .zip con los
 * ARCHIVOS SUBIDOS (storage/uploads/: fotos de carnet, recaudos, documentos
 * de bienes y rutas), que no viven en la base de datos. Rota los más antiguos
 * conservando los últimos BACKUP_RETENTION de cada tipo. Pensado para correr periódicamente desde el
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
 *   3) descomprimir storage/backups/sigtur_archivos_YYYY-MM-DD_HHMMSS.zip
 *      dentro de storage/uploads/
 *
 * Config (config/config.php): PG_DUMP_PATH, BACKUP_RETENTION y
 * BACKUP_UPLOADS (false = no respaldar los archivos subidos; por defecto sí).
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
        'C:\\Program Files\\PostgreSQL\\18\\bin\\pg_dump.exe',
        'C:\\Program Files\\PostgreSQL\\17\\bin\\pg_dump.exe',
        'C:\\Program Files\\PostgreSQL\\16\\bin\\pg_dump.exe',
        'pg_dump',
    ] as $cand) {
        if ($cand === 'pg_dump' || is_file($cand)) { $pgDump = $cand; break; }
    }
}

// 3. Ejecutar pg_dump (formato SQL plano)
$sello   = date('Y-m-d_His');   // mismo sello para el .sql y el .zip del mismo respaldo
$outFile = $dir . '/sigtur_' . $sello . '.sql';
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

// 4. Archivos subidos (storage/uploads/) → .zip con el mismo sello de fecha.
//    Un fallo aquí no invalida el volcado de la BD, pero sí marca la tarea
//    como fallida (código 1) para que se note en el Programador de tareas.
$fallaArchivos = false;
if (!defined('BACKUP_UPLOADS') || BACKUP_UPLOADS) {
    $uploads = $base . '/storage/uploads';
    $zipFile = $dir . '/sigtur_archivos_' . $sello . '.zip';
    if (!is_dir($uploads)) {
        bk_log($dir, 'Archivos — no hay carpeta storage/uploads todavía; nada que respaldar.');
    } elseif (!class_exists('ZipArchive')) {
        $fallaArchivos = true;
        bk_log($dir, 'ERROR — no se respaldaron los archivos subidos: falta la extensión zip de PHP (activarla en Laragon → PHP → Extensions).', true);
    } else {
        $n = respaldarArchivos($uploads, $zipFile);
        if ($n === false) {
            $fallaArchivos = true;
            @unlink($zipFile);
            bk_log($dir, 'ERROR — no se pudo crear el .zip de archivos subidos: ' . basename($zipFile), true);
        } else {
            $tamZ = is_file($zipFile) ? round(filesize($zipFile) / 1024, 1) : 0;
            bk_log($dir, 'OK — archivos subidos: ' . basename($zipFile) . " ($n archivos, $tamZ KB)");
        }
    }
}

// 5. Rotación: conservar solo los últimos BACKUP_RETENTION de cada tipo.
//    'sigtur_2*' distingue los .sql (sigtur_2026-…) de los .zip (sigtur_archivos_…).
$retencion = defined('BACKUP_RETENTION') ? max(1, (int)BACKUP_RETENTION) : 14;
foreach (['/sigtur_2*.sql', '/sigtur_archivos_*.zip'] as $patron) {
    $archivos = glob($dir . $patron) ?: [];
    sort($archivos); // nombre fechado → orden cronológico ascendente
    $sobrantes = count($archivos) - $retencion;
    for ($i = 0; $i < $sobrantes; $i++) {
        if (@unlink($archivos[$i])) {
            bk_log($dir, 'Rotación — eliminado respaldo antiguo: ' . basename($archivos[$i]));
        }
    }
}

exit($fallaArchivos ? 1 : 0);

/**
 * Comprime recursivamente $origen en $zipFile, con rutas relativas a $origen
 * (al restaurar se descomprime tal cual dentro de storage/uploads/).
 * Devuelve cuántos archivos agregó, o false si el .zip no se pudo escribir.
 */
function respaldarArchivos(string $origen, string $zipFile) {
    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) return false;

    $origen = rtrim(str_replace('\\', '/', realpath($origen)), '/');
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($origen, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    $n = 0;
    foreach ($it as $f) {
        if (!$f->isFile()) continue;
        $ruta = str_replace('\\', '/', $f->getPathname());
        $rel  = ltrim(substr($ruta, strlen($origen)), '/');
        if ($zip->addFile($ruta, $rel)) {
            // Fotos y PDF ya vienen comprimidos: guardarlos sin recomprimir es
            // igual de pequeño y mucho más rápido.
            $ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'zip', 'docx', 'xlsx'], true)) {
                $zip->setCompressionName($rel, ZipArchive::CM_STORE);
            }
            $n++;
        }
    }
    // Un .zip sin entradas no se escribe: se deja constancia con un archivo vacío.
    if ($n === 0) $zip->addFromString('LEEME.txt', "storage/uploads estaba vacio al momento del respaldo.\n");
    return $zip->close() ? $n : false;
}
