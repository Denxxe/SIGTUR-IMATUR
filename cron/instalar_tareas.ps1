<#
    Instalador de las tareas programadas de SIGTUR-IMATUR (Windows).

    Crea las dos tareas que el sistema necesita para funcionar desatendido:

      · SIGTUR-Estados   — cada 10 min. Pasa los talleres de "Programado" a
                           "En Curso" cuando llega su fecha/hora de inicio.
                           Sin esto los estados NO avanzan solos: quedan en
                           "Programado" para siempre aunque el taller ya empezó.

      · SIGTUR-Respaldo  — diario 23:00. Volcado completo de la BD en
                           storage/backups/, rotando y conservando los
                           ultimos BACKUP_RETENTION (config/config.php).

    Uso (PowerShell, en la raiz del proyecto):
        powershell -ExecutionPolicy Bypass -File cron\instalar_tareas.ps1

    Opciones:
        -HoraRespaldo "23:00"   Cambia la hora del respaldo diario.
        -MinutosEstados 10      Cambia la frecuencia de la transicion de estados.
        -Desinstalar            Elimina las dos tareas en vez de crearlas.

    No requiere privilegios de administrador: las tareas se crean para el
    usuario actual. Eso implica que corren cuando ese usuario tiene sesion
    iniciada. En un servidor donde deban correr siempre, volver a crearlas
    con /RU SYSTEM desde una consola elevada.
#>

param(
    [string]$HoraRespaldo   = "23:00",
    [int]   $MinutosEstados = 10,
    [switch]$Desinstalar
)

$ErrorActionPreference = "Stop"

# --- Rutas: se deducen solas, para que el script sirva en cualquier maquina ---
$proyecto = Split-Path -Parent $PSScriptRoot
$schtasks = Join-Path $env:WINDIR "System32\schtasks.exe"

Write-Host "Proyecto: $proyecto"

if ($Desinstalar) {
    foreach ($t in @("SIGTUR-Estados", "SIGTUR-Respaldo")) {
        & $schtasks /Delete /TN $t /F 2>$null
        if ($LASTEXITCODE -eq 0) { Write-Host "Eliminada: $t" }
        else                     { Write-Host "No existia: $t" }
    }
    exit 0
}

# --- Localizar php.exe -------------------------------------------------------
# Se prefiere el php del PATH; si no esta, se busca el mas reciente de Laragon.
$php = (Get-Command php.exe -ErrorAction SilentlyContinue).Source
if (-not $php) {
    $php = Get-ChildItem "C:\laragon\bin\php" -Filter php.exe -Recurse -ErrorAction SilentlyContinue |
           Sort-Object FullName -Descending | Select-Object -First 1 -ExpandProperty FullName
}
if (-not $php -or -not (Test-Path $php)) {
    Write-Error "No se encontro php.exe. Agregalo al PATH o edita la variable `$php de este script."
    exit 1
}
Write-Host "PHP:      $php"

# --- Comprobar que los scripts existen antes de programarlos -----------------
$sEstados  = Join-Path $proyecto "cron\actualizar_estados.php"
$sRespaldo = Join-Path $proyecto "cron\respaldo_bd.php"
foreach ($s in @($sEstados, $sRespaldo)) {
    if (-not (Test-Path $s)) { Write-Error "No existe: $s"; exit 1 }
}

# --- Crear las tareas --------------------------------------------------------
# /F reemplaza la tarea si ya existe, para que reinstalar sea idempotente.
# Las comillas internas van escapadas con \" porque schtasks recibe /TR como
# una sola cadena que el propio schtasks vuelve a partir.

& $schtasks /Create /F /SC MINUTE /MO $MinutosEstados /TN "SIGTUR-Estados" `
    /TR "\`"$php\`" \`"$sEstados\`""
if ($LASTEXITCODE -ne 0) { Write-Error "Fallo al crear SIGTUR-Estados"; exit 1 }
Write-Host "Creada: SIGTUR-Estados (cada $MinutosEstados min)"

& $schtasks /Create /F /SC DAILY /ST $HoraRespaldo /TN "SIGTUR-Respaldo" `
    /TR "\`"$php\`" \`"$sRespaldo\`""
if ($LASTEXITCODE -ne 0) { Write-Error "Fallo al crear SIGTUR-Respaldo"; exit 1 }
Write-Host "Creada: SIGTUR-Respaldo (diario $HoraRespaldo)"

Write-Host ""
Write-Host "Listo. Verificar con:  schtasks /Query /TN SIGTUR-Estados /V /FO LIST"
Write-Host "Ejecutar ya una vez:   schtasks /Run /TN SIGTUR-Respaldo"
