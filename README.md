# SIGTUR-IMATUR

Sistema Integral de Gestión Turística y Administrativa del **Instituto Municipal de Turismo de Cumaná (IMATUR)**, estado Sucre, Venezuela.

Aplicación web **on-premise** (sin acceso a internet). MVC en PHP puro, sin framework ni Composer.

- **Stack:** PHP 8+ · PostgreSQL 17 · Bootstrap 5.3 (local) · ApexCharts (local)
- **Módulos:** RRHH, Formación, Turismo (Rutas), Inventario, Recepción (Visitas), Reportes/Indicadores, Sistema (usuarios, roles, auditoría).

> Documentación detallada en `docs/`: `CLAUDE.md` (técnica), `BACKLOG.md` (pendientes), `MANUAL_USUARIO.md` (uso por rol), `INDICADORES_GESTION.md`, `REGLAS_NEGOCIO_*.md`.

---

## Requisitos

- PHP 8.0+ con extensiones `pdo_pgsql`, `zip`, `fileinfo`.
- PostgreSQL 17 (o 16).
- Servidor web (Apache/Laragon) apuntando a la carpeta **`public/`** como raíz.

---

## Instalación

```bash
# 1. Crear la base de datos
createdb -U postgres "SIGTUR-IMATUR"

# 2. Importar el esquema consolidado — un solo archivo, esto es todo
psql -U postgres -d "SIGTUR-IMATUR" -f database/schema_consolidado.sql
```

**No hay paso 3.** `database/schema_consolidado.sql` incluye el esquema base y
**todas** las migraciones 001–073. No hay que aplicar nada de
`database/migrations/`: esa carpeta se conserva como historial y para
actualizar instalaciones antiguas, no para instalar desde cero.

El consolidado deja sembrados los catálogos institucionales (roles y permisos,
configuración del instituto, organigrama de 23 departamentos, cargos, horarios,
feriados, municipios y parroquias) y **un usuario administrador de arranque**.
Las tablas operativas (personal, inventario, talleres, rutas, visitantes…)
quedan vacías, y los correlativos de oficios en 0.

En Windows (Laragon), `psql` suele estar en
`C:\Program Files\PostgreSQL\17\bin\psql.exe` (anteponer `PGPASSWORD=...`).

> **Al regenerar el consolidado** (tras nuevas migraciones), volcar la BD con
> `pg_dump --no-owner --no-privileges` excluyendo los datos de las tablas
> operativas con `--exclude-table-data`, y mantener el bloque del administrador
> de arranque **entre** los datos de `departamentos` y los de `municipio`:
> necesita el catálogo ya cargado, y `municipio`/`parroquia` tienen columnas de
> auditoría `NOT NULL` que apuntan a ese usuario.

### Configuración (credenciales)

`config/config.php` **no se versiona** (contiene credenciales). Crear el propio a partir de la plantilla:

```bash
cp config/config.example.php config/config.php
```

Editar `config/config.php`:
- `URL_ROOT` — URL base del sitio (sin barra final).
- `DB_HOST/DB_PORT/DB_USER/DB_PASS/DB_NAME` — conexión a PostgreSQL.
- `APP_DEBUG` — **`false` en producción** (oculta errores al usuario).
- `PG_DUMP_PATH` / `BACKUP_RETENTION` — para los respaldos.

### Acceso

URL: `http://SIGTUR-IMATUR.test` o `http://localhost/SIGTUR-IMATUR/public`.

En una instalación nueva, el consolidado crea el usuario de arranque:

| Usuario | Contraseña   |
|---------|--------------|
| `admin` | `Sigtur2026` |

> ⚠️ **Cambiar esta contraseña en el primer ingreso** (Perfil → Cambiar
> contraseña). Está publicada en este repositorio, así que no sirve como
> credencial real.

Ese `admin` va montado sobre un empleado técnico ("Administrador del Sistema"),
porque `usuarios.id_empleado` es `NOT NULL`. Una vez cargado el personal real y
creado un administrador nominal, conviene desactivarlo.

---

## Tareas programadas (cron / Programador de tareas de Windows)

Ambas son scripts CLI fuera de `public/`:

```bat
:: Transiciones automáticas de estado de talleres (cada ~10 min)
schtasks /Create /SC MINUTE /MO 10 /TN "SIGTUR-Estados" ^
  /TR "C:\ruta\php.exe C:\laragon\www\SIGTUR-IMATUR\cron\actualizar_estados.php"

:: Respaldo diario de la base de datos (rota según BACKUP_RETENTION)
schtasks /Create /SC DAILY /ST 23:00 /TN "SIGTUR-Respaldo" ^
  /TR "C:\ruta\php.exe C:\laragon\www\SIGTUR-IMATUR\cron\respaldo_bd.php"
```

Los respaldos se guardan en `storage/backups/` (no versionados).

### Restaurar un respaldo

```bash
createdb -U postgres "SIGTUR-IMATUR"
psql -U postgres -d "SIGTUR-IMATUR" -f storage/backups/sigtur_AAAA-MM-DD_HHMMSS.sql
```

---

## Pruebas

Suite mínima sin dependencias (lógica pura, no toca la BD):

```bash
php tests/run.php
```

---

## Notas de seguridad / operación

- **Cambiar** la contraseña de PostgreSQL y reflejarla en `config/config.php`.
- En producción mantener `APP_DEBUG=false`.
- Servir el sitio desde `public/`; el resto del proyecto (`app/`, `config/`, `storage/`, `cron/`) **no** debe ser accesible por web.
- Los documentos subidos (recaudos, pasantes) se guardan en `storage/uploads/` y se sirven con control de acceso vía `DescargaController` (no por URL directa).
- Carpetas no versionadas: `config/config.php`, `storage/`, `public/uploads/`, `*.log`.

---

## Estructura

```
public/            Front controller (index.php) — raíz web
app/
  core/            Router, Database, Controller, Model, Util
  controllers/     Un controlador por módulo
  models/          Un modelo por entidad
  views/           Vistas (inc/header.php, inc/footer.php, por módulo)
  helpers/         session_helper (flash, toasts, token anti doble-envío)
config/            config.example.php (plantilla) · config.php (local, ignorado)
database/          schema_consolidado.sql + migrations/
cron/              Scripts CLI (estados, respaldo)
storage/           backups/ y uploads/ (ignorados)
tests/             run.php (suite mínima)
docs/              Documentación técnica y de negocio
```
