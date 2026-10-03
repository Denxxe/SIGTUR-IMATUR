#!/bin/sh
# SIGTUR-IMATUR — tareas programadas dentro de Docker (servicio `tareas`).
# Reemplaza al Programador de tareas de Windows (cron/instalar_tareas.ps1):
#   · cada 10 min: transiciones automáticas de estado (cron/actualizar_estados.php)
#   · una vez al día, a la hora HORA_RESPALDO (por defecto 23): respaldo de la BD
set -u
HORA="${HORA_RESPALDO:-23}"
ULTIMO=""

echo "[tareas] iniciado · estados cada 10 min · respaldo diario a las ${HORA}:00"
while true; do
    php /var/www/html/cron/actualizar_estados.php

    HOY="$(date +%F)"
    if [ "$(date +%H)" = "$(printf '%02d' "$HORA")" ] && [ "$ULTIMO" != "$HOY" ]; then
        php /var/www/html/cron/respaldo_bd.php && ULTIMO="$HOY"
    fi

    sleep 600
done
