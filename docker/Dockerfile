# SIGTUR-IMATUR — imagen de la aplicación (PHP 8.1 + Apache)
# Se construye desde la raíz del proyecto: ver docker-compose.yml.
FROM php:8.1-apache-bookworm

# pg_dump tiene que ser de la MISMA versión mayor que el servidor (17) o se niega
# a respaldar: Debian trae el 15, así que se instala desde el repositorio de PostgreSQL.
RUN apt-get update \
 && apt-get install -y --no-install-recommends ca-certificates curl gnupg libpq-dev libzip-dev \
 && install -d /usr/share/postgresql-common/pgdg \
 && curl -fsSL -o /usr/share/postgresql-common/pgdg/apt.postgresql.org.asc https://www.postgresql.org/media/keys/ACCC4CF8.asc \
 && echo "deb [signed-by=/usr/share/postgresql-common/pgdg/apt.postgresql.org.asc] https://apt.postgresql.org/pub/repos/apt bookworm-pgdg main" > /etc/apt/sources.list.d/pgdg.list \
 && apt-get update \
 && apt-get install -y --no-install-recommends postgresql-client-17 \
 && docker-php-ext-install pdo_pgsql zip \
 && a2enmod rewrite headers \
 && rm -rf /var/lib/apt/lists/*

COPY docker/php.ini /usr/local/etc/php/conf.d/sigtur.ini
COPY docker/apache-sigtur.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
COPY . /var/www/html
# config.php no se versiona: en Docker se usa uno que lee las variables de entorno (.env).
COPY docker/config.php /var/www/html/config/config.php

RUN mkdir -p storage/backups storage/certs \
             storage/uploads/bienes storage/uploads/expedientes storage/uploads/pasantes \
             storage/uploads/rutas storage/uploads/talleres storage/uploads/fotos \
 && chown -R www-data:www-data storage \
 && chmod +x docker/tareas.sh

EXPOSE 80
