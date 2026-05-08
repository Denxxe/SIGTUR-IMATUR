# CLAUDE.md — SIGTUR-IMATUR
**Última actualización:** 2026-05-08  
**Stack:** PHP 8+ · PostgreSQL 17 · Bootstrap 5.3 · Custom MVC (sin Composer)

---

## ¿Qué es este proyecto?

Sistema Integral de Gestión Turística y Administrativa (SIGTUR) para **IMATUR** (Instituto Municipal de Turismo de Cumaná, Sucre, Venezuela). Aplicación web MVC en PHP puro, despliegue **on-premise** sin acceso a internet.

**Usuario de prueba:** `admin` / contraseña en la BD (hash bcrypt en tabla `usuarios`, rol 1)

---

## Arquitectura MVC

```
public/index.php          ← Front controller (único punto de entrada)
config/config.php         ← DB host/port/name/user + URL_ROOT
app/
  core/
    Router.php            ← URL parser + middleware autenticación + RBAC
    Database.php          ← PDO/PostgreSQL wrapper (prepared statements)
    Controller.php        ← Base: $this->view(), $this->model()
    Model.php             ← Base: $this->db
  controllers/            ← 23 controllers (uno por módulo)
  models/                 ← 23 models
  views/
    inc/header.php        ← Layout maestro + sidebar con RBAC
    inc/footer.php        ← Scripts + toast container
    auth/login.php        ← Vista independiente (sin header.php)
```

**Patrón de URL:** `/controlador/metodo/parametro`  
**Autenticación:** Session-based — `$_SESSION['user_id']`, `$_SESSION['user_rol']`

---

## Módulos y Controladores

| Módulo | Controladores | Tablas principales |
|--------|-------------|-------------------|
| **RRHH** | Empleados, Cargos, Departamentos, Asistencias | personas, empleados, cargos, departamentos, asistencias, horarios, permisos_laborales, vacaciones |
| **Inventario** | Inventario, Categorias, Ubicaciones, ActividadesInventario | inventario, categorias, ubicaciones, actividad_inventario |
| **Formación** | Talleres, UbicacionesFormacion, Pasantes | talleres, ubicaciones_formacion, pasantes, pasante_documentos, taller_informes, taller_inventario, participantes_taller |
| **Turismo** | Rutas, ActividadesRuta, Visitantes, Visitas | rutas, puntos_ruta, actividades_ruta, ruta_inventario, visitantes, visitas |
| **Ubicación** | Municipio, Parroquia | municipio, parroquia |
| **Sistema** | Usuarios, Roles, Auditoria | usuarios, roles, audit_logs |
| **Reportes** | Reportes, Dashboard | — (queries JOIN sobre todas las tablas) |

---

## RBAC — Control de Acceso

Implementado en `app/core/Router.php` (nivel de ruta) **y** en `ReportesController.php` (nivel de método).

| Rol ID | Nombre | Controladores permitidos |
|--------|--------|--------------------------|
| 1 | Administrador | Todo sin restricción |
| 2 | RRHH | Dashboard, Empleados, Cargos, Departamentos, Asistencias, Reportes |
| 3 | Turismo | Dashboard, Rutas, ActividadesRuta, Talleres, UbicacionesFormacion, Pasantes, Reportes |
| 4 | Inventario | Dashboard, Inventario, Categorias, Ubicaciones, ActividadesInventario, Reportes |

### Protección por reporte (ReportesController::requireRoles)

| Método(s) | Roles permitidos |
|-----------|-----------------|
| `asistencia`, `exportarAsistenciaCsv`, `exportarAsistenciaPdf` | [1, 2] |
| `talleres`, `exportarTalleresCsv`, `exportarTalleresPdf`, `rutas`, `exportarRutasCsv`, `exportarRutasPdf`, `exportarParticipantesCsv`, `dossier`, `exportarDossierCsv`, `pasantes`, `exportarPasantesCsv`, `exportarPasantesPdf` | [1, 3] |
| `indicadores`, `index` | todos |

### Sidebar por rol (header.php)

| Sección | Condición PHP |
|---------|--------------|
| RRHH | `in_array($rol, [1, 2])` |
| Inventario | `in_array($rol, [1, 4])` |
| Formación | `in_array($rol, [1, 3])` |
| Turismo (Rutas + Actividades) | `in_array($rol, [1, 3])` |
| Análisis / Reportes | todos los roles (sin condición) |
| Sistema | `$rol == 1` |

---

## Base de Datos (PostgreSQL 17)

**Archivo:** `database/schema.sql` (pg_dump con datos de prueba)  
**DB:** `SIGTUR-IMATUR` | **User:** `postgres`

### Inventario completo de tablas

#### Sistema
| Tabla | Descripción | Auditoría completa |
|-------|-------------|-------------------|
| `roles` | 4 roles fijos | ✅ |
| `usuarios` | Credenciales, FK a empleados y roles | ✅ |
| `audit_logs` | Log inmutable de operaciones JSONB | — (no se borra) |

#### RRHH
| Tabla | Descripción | Auditoría completa |
|-------|-------------|-------------------|
| `personas` | Entidad base de personas físicas, FK a `parroquia` | ✅ |
| `departamentos` | Unidades organizativas | ✅ |
| `cargos` | Puestos con sueldo_base | ✅ |
| `empleados` | 1:1 con personas; FK a cargo/departamento/horario | ✅ + `tipo_contrato`, `fecha_egreso` (migración 002) |
| `asistencias` | Marcaje diario entrada/salida | ✅ |
| `horarios` *(002)* | Turnos de trabajo | ✅ |
| `permisos_laborales` *(002)* | Permisos y ausencias justificadas | ✅ |
| `vacaciones` *(002)* | Control anual de días vacaciones | ✅ |

#### Inventario
| Tabla | Descripción | Auditoría completa |
|-------|-------------|-------------------|
| `categorias` | Clasificación de bienes | ✅ |
| `ubicaciones` | Oficinas/almacenes internos; tiene FK `"departamento _d"` (columna con espacio) | ✅ |
| `inventario` | Bienes con código BN, marca, modelo, serial, condición | ✅ |
| `actividad_inventario` | Movimientos: Asignacion/Devolucion/Traslado/Baja/Mantenimiento | ✅ |

#### Formación
| Tabla | Descripción | Auditoría completa |
|-------|-------------|-------------------|
| `ubicaciones_formacion` | Sedes e instituciones; FK a `parroquia`; columna `es_sede_propia BOOL` (migración 004) para marcar la sede de IMATUR | ✅ |
| `talleres` | Actividades formativas; `tipo_actividad` ('Taller','Charla') por migración 004; `id_oficio FK` para actividades externas | ✅ |
| `taller_informes` | Informe demográfico por taller (mujeres/hombres/niñas/niños) | ✅ (002) |
| `taller_inventario` | Préstamo de bienes a un taller | ✅ (002) |
| `participantes_taller` | Inscripción y asistencia; `id_persona` nullable desde migración 004; columnas `nombre_libre`, `apellido_libre`, `cedula_libre` para participantes sin cédula (niños/as) | ✅ (002) |
| `pasantes` | Históricamente independiente; migración 003 agrega `id_persona FK` y elimina cedula/nombre/apellido propios | ✅ (002) |
| `pasante_documentos` | Cartas y evaluaciones con flags de entrega | ✅ (002) |

#### Turismo
| Tabla | Descripción | Auditoría completa |
|-------|-------------|-------------------|
| `rutas` | Rutas turísticas; nivel y estado con CHECK | ✅ |
| `puntos_ruta` | Waypoints con lat/lon y orden | ✅ |
| `actividades_ruta` | Eventos por ruta con responsable | ✅ |
| `ruta_inventario` | Bienes asignados a una ruta | ✅ (002) |
| `visitantes` *(001)* | Personas externas; cedula, procedencia, genero | ✅ |
| `visitas` *(001)* | Marcaje entrada/salida de visitantes | ✅ básico |

#### Geografía
| Tabla | Descripción | Auditoría completa |
|-------|-------------|-------------------|
| `municipio` | Municipios con código postal | ✅ (NOT NULL sin DEFAULT — ver quirks) |
| `parroquia` | Parroquias por municipio | ⚠️ Nombrado inconsistente: `create_at`/`create_by` sin "d" |

### Migraciones incrementales

| Archivo | Estado | Contenido |
|---------|--------|-----------|
| `database/migrations/001_visitantes_visitas.sql` | ⚠️ Pendiente de ejecutar | Tablas `visitantes` y `visitas` |
| `database/migrations/002_rrhh_extensions.sql` | ⚠️ Pendiente de ejecutar | Horarios, Permisos, Vacaciones + corrección auditoría |
| `database/migrations/003_normalize_pasantes.sql` | ⚠️ Pendiente de ejecutar (requiere 001 y 002 previos) | Normaliza `pasantes`: agrega `id_persona FK`, migra datos por cédula, elimina campos redundantes |
| `database/migrations/004_formacion_reglas_negocio.sql` | ⚠️ Pendiente de ejecutar (requiere 001, 002 y 003 previos) | RN-F01 tipo_actividad → solo Taller/Charla; RN-F02 `es_sede_propia` en ubicaciones; RN-F05/06 tabla `oficios` + `id_oficio` en talleres; RN-F16 participantes sin cédula |

### Soft Delete
Todas las tablas tienen: `is_active BOOL`, `deleted_at TIMESTAMP`, `deleted_by INT`.  
Nunca se borran filas — se deshabilitan.

### Convención de auditoría
```
created_at, updated_at, deleted_at  ← TIMESTAMPS
created_by, updated_by, deleted_by  ← INT (id del usuario que operó)
```
**Excepción:** `parroquia` usa `create_at`/`update_at`/`delete_at` y `create_by`/`update_by`/`delete_by` (sin la "d").

---

## Análisis de Normalización (3FN)

### Bien normalizado ✅
- Jerarquía `personas → empleados → usuarios` (1:1, sin duplicación)
- `categorias → inventario`, `departamentos/cargos → empleados`
- Tablas pivote correctas: `participantes_taller`, `ruta_inventario`, `taller_inventario`

### Problemas conocidos ⚠️
1. **`pasantes` vs `personas`**: Antes de ejecutar la migración 003, `pasantes` tiene cedula/nombre/apellido propios. Después de 003 queda normalizado con `id_persona FK`. Hasta que se ejecute 003, los reportes cruzados con `participantes_taller` requieren JOIN manual por cédula.
2. **`taller_informes.total_atendidas`**: Dato derivado (mujeres+hombres+niñas+niños). Se puede generar inconsistencia si se actualiza una columna sin la otra. Validar en el controller.
3. **`municipio.created_at NOT NULL` sin DEFAULT**: Si se hace INSERT sin ese valor explota. El model debe siempre pasar `created_at = NOW()`.
4. **`ubicaciones."departamento _d"`**: Nombre con espacio — siempre usar comillas dobles en SQL.

---

## Reportes e Indicadores

### Implementados (ReportesController)

| Reporte | Roles | Export |
|---------|-------|--------|
| Asistencia con filtro de fechas | 1, 2 | CSV + PDF |
| Talleres con filtro de estado | 1, 3 | CSV + PDF |
| Dossier integral de taller | 1, 3 | CSV |
| Participantes de un taller | 1, 3 | CSV |
| Rutas turísticas | 1, 3 | CSV + PDF |
| Pasantes con estado y tutor | 1, 3 | CSV + PDF |
| Indicadores de gestión (KPIs) | todos | — |

### KPIs en Indicadores (todos los roles)
- Empleados por departamento (barras)
- Inventario por categoría (barras)
- Inventario por condición (dona)
- Talleres por mes últimos 6 meses (línea)

### Reportes pendientes de implementar (siguientes fases)
- Permisos laborales por tipo/empleado/período
- Saldo de vacaciones por empleado
- Visitantes por mes / procedencia / motivo
- Asistencia % promedio mensual (días asistidos / días laborales)
- Indicadores ampliados: empleados en permiso hoy, visitas activas del día

---

## Frontend — Recursos Locales

**Todos los recursos en `/public/assets/libs/` — sin CDN.**

| Archivo | Estado | Versión |
|---------|--------|---------|
| `bootstrap.min.css` | ✅ Local | 5.3 |
| `bootstrap.bundle.min.js` | ✅ Local | 5.3 + Popper |
| `apexcharts.min.js` | ✅ Local | Latest |
| `bootstrap-icons.min.css` | ✅ Local | 1.11.3 |
| `bootstrap-icons.woff2` | ✅ Local | 1.11.3 |
| `bootstrap-icons.woff` | ✅ Local | 1.11.3 (fallback) |
| `bootstrap-icons.svg` | ✅ Local | 1.11.3 (fallback SVG) |

### Tipografía
Google Fonts fue eliminado de `header.php`. Fallback en CSS: `'Inter', system-ui, -apple-system, sans-serif`.  
En Windows 11 resuelve a Segoe UI Variable. Sin internet funciona correctamente.

---

## Design System

| Archivo | Propósito |
|---------|-----------|
| `public/assets/css/sigtur-tokens.css` | Variables CSS: colores, tipografía, espaciado, dark mode |
| `public/assets/css/sigtur-components.css` | Componentes: `.app-shell`, `.sidebar`, `.sig-header`, `.btn-sig`, `.sig-card` |
| `public/assets/css/login.css` | Estilos exclusivos del login |
| `public/assets/js/sigtur-validations.js` | Validación client-side |

**Dark mode:** Toggle con `data-theme="dark"` en `<html>`. Persiste en `localStorage['sigtur-theme']`.

---

## Convenciones de Código

### Controllers
```php
public function index() {
    $model = $this->model('NombreModel');
    $data = ['titulo' => 'Titulo', 'items' => $model->getAll()];
    $this->view('modulo/index', $data);
}
```

### Protección de roles en controllers de reporte
```php
// Llamar al inicio de cada método restringido:
$this->requireRoles([1, 2]);  // solo RRHH y Admin
$this->requireRoles([1, 3]);  // solo Turismo y Admin
```

### Models
```php
public function getAll() {
    $this->db->query('SELECT * FROM tabla WHERE is_active = TRUE ORDER BY id DESC');
    return $this->db->resultSet();
}
```

### Auditoría
```php
// Desde controllers al hacer INSERT/UPDATE/DELETE:
$this->logAudit('nombre_tabla', 'INSERT', $newId, null, $newData);
```

---

## Peculiaridades — Cosas a tener en cuenta

1. **`ubicaciones."departamento _d"`**: FK a departamentos con espacio en el nombre. Siempre comillas dobles en SQL.

2. **`parroquia` con nomenclatura inconsistente**: `create_at`/`update_at` en lugar de `created_at`/`updated_at`. Los models Municipio y Parroquia manejan esto.

3. **`pasantes` antes de migración 003**: Tiene sus propios campos cedula/nombre/apellido. Reportes cruzados con `participantes_taller` requieren JOIN manual por cedula hasta que se ejecute la migración 003, que resuelve esto con `id_persona FK`.

4. **Transacciones en Empleados**: Guardar un empleado requiere INSERT en `personas` y luego en `empleados` de forma atómica. Ver `EmpleadosController::store()`.

5. **`ubicaciones_formacion.parroquia NOT NULL`**: Al crear sede de formación siempre se necesita seleccionar parroquia.

6. **`municipio.created_at NOT NULL` sin DEFAULT**: El model debe pasar `created_at = NOW()` explícitamente en INSERT.

7. **Visitas — patrón toggle**: `Visita::registrar()` detecta si hay visita abierta (sin `hora_salida`). Si no hay → INSERT entrada. Si hay → UPDATE `hora_salida = NOW()`. No crear dos registros.

8. **`taller_informes.total_atendidas` es derivado**: Siempre recalcular como `mujeres + hombres + ninas + ninos` antes de guardar para evitar inconsistencia.

9. **`talleres.tipo_actividad`**: Restringido a ('Taller','Charla') desde migración 004. DEFAULT 'Taller'.

10. **`talleres.id_oficio`**: FK nullable a tabla `oficios`. Solo se asigna al crear una actividad en sede externa (`es_sede_propia = FALSE`). En ediciones posteriores no cambia.

11. **`ubicaciones_formacion.es_sede_propia`**: Flag booleano (migración 004). Al crear una actividad, si la sede seleccionada tiene `es_sede_propia = TRUE` → actividad interna, no requiere oficio. Si es `FALSE` → externa, requiere fecha del oficio. Marcar IMATUR con: `UPDATE ubicaciones_formacion SET es_sede_propia = TRUE WHERE nombre ILIKE '%IMATUR%';`

12. **`participantes_taller` sin cédula**: Desde migración 004, `id_persona` es nullable. Usar `inscribirLibre()` en el modelo para participantes sin documento (niños/as). La constraint `pt_participante_requerido` exige que al menos uno de `id_persona` o `nombre_libre` no sea NULL.

13. **Máquina de estados en talleres (RN-F13)**: Transiciones válidas: Programado→(EnCurso|Cancelado), EnCurso→(Finalizado|Cancelado). Finalizado y Cancelado son terminales. Validado en `TalleresController::validarTransicion()`.

14. **RN-F12 en TalleresController**: No se puede cambiar a 'Finalizado' si `Taller::countParticipantes()` retorna 0.

10. **`empleados.tipo_contrato`**: Valores: 'Fijo','Contratado','Suplente','Comisión de Servicio'. DEFAULT 'Fijo'.

---

## Pasos para levantar el entorno

```bash
# 1. Laragon activo con PHP 8+ y PostgreSQL 17
# 2. Crear la base de datos:
createdb -U postgres "SIGTUR-IMATUR"

# 3. Importar schema completo (con datos de prueba):
psql -U postgres -d "SIGTUR-IMATUR" -f database/schema.sql

# 4. Ejecutar migraciones en orden:
psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/001_visitantes_visitas.sql
psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/002_rrhh_extensions.sql
psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/003_normalize_pasantes.sql

# 5. Verificar config/config.php:
#    DB_HOST=localhost | DB_PORT=5432 | DB_NAME=SIGTUR-IMATUR
#    DB_USER=postgres  | DB_PASS=''  (ajustar según entorno)

# 6. URL: http://SIGTUR-IMATUR.test  o  http://localhost/SIGTUR-IMATUR/public
```

---

## Archivos clave de referencia

| Propósito | Archivo |
|-----------|---------|
| Configuración global + constantes | `config/config.php` |
| Conexión DB (PDO wrapper) | `app/core/Database.php` |
| Router + RBAC middleware | `app/core/Router.php` |
| Flash messages / Toast | `app/helpers/session_helper.php` |
| Layout principal + sidebar RBAC | `app/views/inc/header.php` |
| Scripts + pie de página | `app/views/inc/footer.php` |
| Schema completo con datos | `database/schema.sql` |
| Migración visitantes/visitas | `database/migrations/001_visitantes_visitas.sql` |
| Migración RRHH extensions | `database/migrations/002_rrhh_extensions.sql` |
| Migración normalización pasantes | `database/migrations/003_normalize_pasantes.sql` |
| Reportes e Indicadores | `app/controllers/ReportesController.php` |
