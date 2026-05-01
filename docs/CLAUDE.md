# CLAUDE.md — SIGTUR-IMATUR
**Última actualización:** 2026-05-01  
**Stack:** PHP 8+ · PostgreSQL 17 · Bootstrap 5.3 · Custom MVC (sin Composer)

---

## ¿Qué es este proyecto?

Sistema Integral de Gestión Turística y Administrativa (SIGTUR) para **IMATUR** (Instituto Municipal de Turismo de Cumaná, Sucre, Venezuela). Es una aplicación web MVC en PHP puro, diseñada para despliegue **on-premise** sin acceso a internet.

**Usuario de prueba:** `admin` / contraseña en la BD (hash bcrypt en tabla `usuarios`)

---

## Arquitectura MVC

```
public/index.php          ← Front controller (único punto de entrada)
config/config.php         ← DB host/port/name/user + URL_ROOT
app/
  core/
    Router.php            ← URL parser + middleware de autenticación y RBAC
    Database.php          ← PDO/PostgreSQL wrapper (prepared statements)
    Controller.php        ← Base: $this->view(), $this->model()
    Model.php             ← Base: $this->db
  controllers/            ← 21 controllers (uno por módulo)
  models/                 ← 21 models
  views/
    inc/header.php        ← Layout maestro + sidebar (carga CSS/JS)
    inc/footer.php        ← Scripts + toast container
    auth/login.php        ← Vista independiente (sin header.php)
```

**Patrón de URL:** `/controlador/metodo/parametro`  
**Redirect default:** `DashboardController::index()`  
**Autenticación:** Session-based — `$_SESSION['user_id']`, `$_SESSION['user_rol']`

---

## Módulos y Controladores

| Módulo | Controladores | Tablas principales |
|--------|-------------|-------------------|
| **RRHH** | Empleados, Cargos, Departamentos, Asistencias | personas, empleados, cargos, departamentos, asistencias |
| **Inventario** | Inventario, Categorias, Ubicaciones, ActividadesInventario | inventario, categorias, ubicaciones, actividad_inventario |
| **Formación** | Talleres, UbicacionesFormacion, Pasantes | talleres, ubicaciones_formacion, pasantes, pasante_documentos, taller_informes, taller_inventario, participantes_taller |
| **Turismo** | Rutas, ActividadesRuta | rutas, puntos_ruta, actividades_ruta, ruta_inventario |
| **Ubicación** | Municipio, Parroquia | municipio, parroquia |
| **Sistema** | Usuarios, Roles, Auditoria | usuarios, roles, audit_logs |
| **Reportes** | Reportes, Dashboard | — (queries JOIN sobre todas las tablas) |

---

## Base de Datos (PostgreSQL 17)

**Archivo de referencia:** `database/schema.sql` (pg_dump completo con datos de prueba)  
**DB name:** `SIGTUR-IMATUR`  
**Superuser:** `postgres`

### Tablas por dominio

#### Dominio 1: Sistema
- `roles` — 4 roles fijos: Administrador(1), RRHH(2), Turismo(3), Inventario(4)

#### Dominio 2: RRHH
- `personas` — tabla base con `parroquia_id` FK → parroquia
- `departamentos`, `cargos`
- `empleados` — 1:1 con personas, FK a cargo y departamento
- `usuarios` — credenciales, FK a empleados y roles
- `asistencias` — registro diario entrada/salida

#### Dominio 3: Inventario
- `categorias`, `ubicaciones` — tiene columna `"departamento _d"` (FK con espacio en nombre — usar comillas en queries)
- `inventario` — condiciones: Nuevo/Bueno/Regular/Dañado/Inservible
- `actividad_inventario` — movimientos: Asignacion/Devolucion/Traslado/Baja/Mantenimiento

#### Dominio 4: Formación
- `ubicaciones_formacion` — FK a `parroquia` (columna `parroquia INT NOT NULL`)
- `talleres` — estados: Programado/En Curso/Finalizado/Cancelado
- `taller_informes` — informe demográfico por taller (mujeres/hombres/niñas/niños)
- `taller_inventario` — préstamo de bienes para un taller
- `participantes_taller` — asistencia de personas a un taller
- `pasantes` — tabla independiente (no hereda de personas): cedula/nombre/apellido/institucion propios
- `pasante_documentos` — tipos: Carta de Postulación/Carta de Aceptación/Evaluación/Otro

#### Dominio 5: Turismo
- `rutas` — nivel: Fácil/Moderado/Difícil/Extremo; estado: Activa/Inactiva/En Mantenimiento
- `puntos_ruta` — FK CASCADE a rutas, tiene lat/lon
- `actividades_ruta` — eventos por ruta
- `ruta_inventario` — bienes asignados a una ruta

#### Dominio 6: Geografía (nuevas)
- `municipio` — municipios con código postal
- `parroquia` — parroquias por municipio (convención de columnas: `create_by`/`update_by`/`delete_by`/`create_at`/`update_at`/`delete_at` — inconsistente con el resto del esquema que usa `created_*`)

### Soft Delete
Todas las tablas tienen: `is_active BOOL`, `deleted_at TIMESTAMP`, `deleted_by INT`. Nunca se borran filas, se deshabilitan.

### Convención de auditoría
Todas las tablas (excepto parroquia) usan: `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`.  
**Excepción:** `parroquia` usa `create_at`/`update_at`/`delete_at` y `create_by`/`update_by`/`delete_by` (sin la "d" al final).

---

## RBAC — Control de Acceso

Implementado en `app/core/Router.php`. El rol se lee de `$_SESSION['user_rol']` (integer).

| Rol ID | Nombre | Acceso |
|--------|--------|--------|
| 1 | Administrador | Todo — incluyendo Usuarios, Roles, Municipios, Parroquias, Auditoría |
| 2 | RRHH | RRHH + Reportes |
| 3 | Turismo | Rutas + Formación + Reportes |
| 4 | Inventario | Inventario + Ubicaciones + Reportes |

---

## Frontend — Recursos Locales

**Todos los recursos deben estar en `/public/assets/libs/` — sin CDN.**

| Archivo | Estado | Notas |
|---------|--------|-------|
| `bootstrap.min.css` | ✅ Local | v5.3 |
| `bootstrap.bundle.min.js` | ✅ Local | v5.3 + Popper |
| `apexcharts.min.js` | ✅ Local | Gráficos dashboard |
| `bootstrap-icons.min.css` | ✅ Local | v1.11.3 |
| `bootstrap-icons.woff2` | ✅ Local | Fuente de iconos |
| `bootstrap-icons.woff` | ✅ Local | Fallback fuente |

### Tipografía

Google Fonts fue eliminado del `header.php`. El CSS ya tiene fallback:
```css
--font-sans: 'Inter', system-ui, -apple-system, sans-serif;
```
En Windows 11, `system-ui` resuelve a Segoe UI Variable. Funcionamiento correcto sin internet.

---

## Design System

| Archivo | Propósito |
|---------|-----------|
| `public/assets/css/sigtur-tokens.css` | Variables CSS: colores, tipografía, espaciado, dark mode |
| `public/assets/css/sigtur-components.css` | Componentes custom: `.app-shell`, `.sidebar`, `.sig-header`, `.btn-sig`, `.card-sig` |
| `public/assets/css/login.css` | Estilos exclusivos del login |
| `public/assets/js/sigtur-validations.js` | Validación client-side de formularios |

**Dark mode:** Toggle por `data-theme="dark"` en `<html>`. Persiste en `localStorage['sigtur-theme']`.

---

## Convenciones de Código

### Controllers
```php
// Patrón estándar de un método index
public function index() {
    $model = $this->model('NombreModel');
    $data = ['titulo' => 'Titulo', 'items' => $model->getAll()];
    $this->view('modulo/index', $data);
}
```

### Models
```php
// Siempre usar prepared statements vía $this->db
public function getAll() {
    $this->db->query('SELECT * FROM tabla WHERE is_active = TRUE ORDER BY id DESC');
    return $this->db->resultSet();
}
```

### Vistas
- No hay template engine — PHP puro
- Las vistas cargan `inc/header.php` al inicio y `inc/footer.php` al final
- Flash messages con `flash('clave')` desde `session_helper.php`

### Auditoría
```php
// Llamar desde controllers al hacer INSERT/UPDATE/DELETE
$this->logAudit('nombre_tabla', 'INSERT', $newId, null, $newData);
```

---

## Peculiaridades / Cosas a tener en cuenta

1. **Columna con espacio en `ubicaciones`**: La columna FK a departamentos se llama `"departamento _d"` (con espacio). Siempre usar comillas dobles en SQL.

2. **`parroquia` con nomenclatura inconsistente**: Usa `create_at`/`update_at` en lugar de `created_at`/`updated_at`. Los modelos Municipio y Parroquia manejan esto.

3. **`pasantes` es independiente**: A diferencia de `empleados`, la tabla `pasantes` tiene sus propios campos cedula/nombre/apellido — no hereda de `personas`.

4. **Transacciones en Empleados**: El guardado de un empleado requiere insertar en `personas` Y en `empleados` de forma atómica. Ver `EmpleadosController::store()`.

5. **`ubicaciones_formacion` requiere parroquia**: La columna `parroquia INT NOT NULL` — al crear una sede de formación siempre se necesita seleccionar parroquia.

6. **Módulo de Ubicaciones tiene FK a departamento**: `"departamento _d"` es NOT NULL — al crear una ubicación de inventario se debe seleccionar departamento.

7. **Auth users**: Solo el usuario `admin` (id=2) existe en la BD de prueba. Rol 1 (Administrador).

---

## Pasos para levantar el entorno

```bash
# 1. Laragon activo con PHP 8+ y PostgreSQL 17
# 2. Crear la base de datos (si no existe):
createdb -U postgres "SIGTUR-IMATUR"

# 3. Importar el schema completo (incluye datos de prueba):
psql -U postgres -d "SIGTUR-IMATUR" -f database/schema.sql

# 4. Verificar config/config.php:
#    define('DB_HOST', 'localhost');
#    define('DB_PORT', '5432');
#    define('DB_NAME', 'SIGTUR-IMATUR');
#    define('DB_USER', 'postgres');
#    define('DB_PASS', '');  // ajustar según entorno

# 5. URL de acceso en Laragon: http://SIGTUR-IMATUR.test
#    o http://localhost/SIGTUR-IMATUR/public
```

---

## Archivos clave de referencia

| Propósito | Archivo |
|-----------|---------|
| Configuración global + constantes | `config/config.php` |
| Conexión DB (PDO wrapper) | `app/core/Database.php` |
| Router + RBAC middleware | `app/core/Router.php` |
| Flash messages / Toast | `app/helpers/session_helper.php` |
| Layout principal | `app/views/inc/header.php` |
| Scripts + pie de página | `app/views/inc/footer.php` |
| Schema completo con datos | `database/schema.sql` |
| Documentación de módulos | `docs/01_Documentacion_Modulos.md` |
| Arquitectura y auditoría | `docs/02_Documentacion_Arquitectura.md` |
| Librerías y dependencias | `docs/04_Librerias_y_Dependencias.md` |
