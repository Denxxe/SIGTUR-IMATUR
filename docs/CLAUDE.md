# CLAUDE.md — SIGTUR-IMATUR
**Última actualización:** 2026-05-25  
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
    Controller.php        ← Base: $this->view(), $this->model(), sanitizePost()
    Model.php             ← Base: $this->db, toArray() para AuditLog
  controllers/            ← 24 controllers (uno por módulo)
  models/                 ← 24 models
  views/
    inc/header.php        ← Layout maestro + sidebar con RBAC
    inc/footer.php        ← Scripts + toast container + modal eliminación global
    auth/login.php        ← Vista independiente (sin header.php)
```

**Patrón de URL:** `/controlador/metodo/parametro`  
**Autenticación:** Session-based — `$_SESSION['user_id']`, `$_SESSION['user_rol']`

---

## Módulos y Controladores

| Módulo | Controladores | Tablas principales |
|--------|-------------|-------------------|
| **RRHH** | Empleados, Cargos, Departamentos, Asistencias | personas, empleados, cargos, departamentos, asistencias, horarios*, permisos_laborales*, vacaciones* |
| **Inventario** | Inventario, Categorias, Ubicaciones, ActividadesInventario | inventario, categorias, ubicaciones, actividad_inventario |
| **Formación** | Talleres, UbicacionesFormacion, Pasantes | talleres, ubicaciones_formacion, pasantes, pasante_documentos, taller_informes, taller_inventario, participantes_taller |
| **Turismo** | Rutas, ActividadesRuta, Visitantes, Visitas | rutas, puntos_ruta, actividades_ruta, ruta_inventario, visitantes, visitas |
| **Ubicación** | Municipio, Parroquia | municipio, parroquia |
| **Sistema** | Usuarios, Roles, Auditoria, **Config** | usuarios, roles, audit_logs, configuracion_sistema |
| **Reportes** | Reportes, Dashboard | — (queries JOIN sobre todas las tablas) |

*Tablas creadas en migración 002, sin controlador/vista dedicada aún.

---

## RBAC — Control de Acceso

Implementado en `app/core/Router.php` (nivel de ruta) **y** en `ReportesController.php` (nivel de método).

**A partir de migración 008:** Los permisos son **dinámicos** — almacenados en la tabla `permisos_rol` y gestionables desde `Sistema → Roles y Permisos` en la UI.  
- `RolesController::getMapaRbac()` es la fuente única: la llama el Router en cada request y también la vista de roles.  
- El Administrador (rol 1) usa el marcador `'*'` en `permisos_rol` → acceso total, no modificable desde la UI.  
- Los demás roles tienen lista explícita de controladores permitidos. Cambios aplican en la próxima sesión del usuario.

| Rol ID | Nombre | Controladores permitidos (seed 008) |
|--------|--------|--------------------------------------|
| 1 | Administrador | `'*'` — acceso total sin restricción |
| 2 | RRHH | Dashboard, Empleados, Cargos, Departamentos, Asistencias, Visitantes, Visitas, Reportes, Config |
| 3 | Turismo | Dashboard, Rutas, ActividadesRuta, Talleres, UbicacionesFormacion, Pasantes, Visitantes, Visitas, Reportes |
| 4 | Inventario | Dashboard, Inventario, Categorias, Ubicaciones, ActividadesInventario, Reportes |
| 5 | Recepción | Dashboard, Visitantes, Visitas, Asistencias |

### Protección por reporte (ReportesController::requireRoles)

| Método(s) | Roles permitidos |
|-----------|-----------------|
| `asistencia`, `exportarAsistenciaCsv/Pdf` | [1, 2] |
| `visitantes`, `exportarVisitantesCsv/Pdf` | [1, 2] |
| `talleres`, `exportarTalleresCsv/Pdf`, `rutas`, `exportarRutasCsv/Pdf`, `exportarParticipantesCsv`, `dossier`, `exportarDossierCsv`, `pasantes`, `exportarPasantesCsv/Pdf` | [1, 3] |
| `inventario`, `exportarInventarioCsv/Pdf`, `bajasInventario`, `exportarBajasInventarioCsv` | [1, 4] |
| `indicadores`, `index` | todos |

### Sidebar por rol (header.php)

| Sección | Condición PHP |
|---------|--------------|
| RRHH | `in_array($rol, [1, 2])` |
| Inventario | `in_array($rol, [1, 4])` |
| Formación | `in_array($rol, [1, 3])` |
| Turismo (Rutas + Actividades) | `in_array($rol, [1, 3])` |
| Visitantes / Visitas / Asistencias | `in_array($rol, [1, 2, 3, 5])` |
| Análisis / Reportes | todos los roles |
| Sistema + Configuración | `in_array($rol, [1, 2])` |

---

## Base de Datos (PostgreSQL 17)

**DB:** `SIGTUR-IMATUR` | **User:** `postgres` | **Password:** `1234` (entorno local Laragon)  
**psql path (Windows):** `C:\Program Files\PostgreSQL\17\bin\psql.exe`

### Inventario de tablas — Estado actual

#### Sistema
| Tabla | Descripción |
|-------|-------------|
| `roles` | 5 roles (Admin, RRHH, Turismo, Inventario, Recepción) |
| `permisos_rol` | Permisos dinámicos: `(id_rol, modulo)`. Admin usa marcador `'*'` *(migración 008)* |
| `usuarios` | Credenciales, FK opcional a empleados y roles |
| `audit_logs` | Log inmutable de operaciones JSONB |
| `configuracion_sistema` | Clave/valor: director, resolución, correlativo de oficios |

#### RRHH
| Tabla | Descripción |
|-------|-------------|
| `personas` | Entidad base; FK a `parroquia` |
| `departamentos` | Unidades organizativas (plana, sin jerarquía) |
| `cargos` | Puestos con sueldo_base |
| `empleados` | 1:1 con personas; FK a cargo/departamento/horario; `tipo_contrato`, `fecha_egreso` |
| `asistencias` | Marcaje diario entrada/salida (patrón toggle) |
| `horarios` *(002, sin UI)* | Turnos de trabajo |
| `permisos_laborales` *(002, sin UI)* | Permisos y ausencias |
| `vacaciones` *(002, sin UI)* | Control anual de días |

Nota: `horarios`, `permisos_laborales`, `vacaciones` existen desde migración 002. Sin UI. Pendiente respuestas D-RH01–D-RH11.

#### Inventario
| Tabla | Descripción |
|-------|-------------|
| `categorias` | Clasificación de bienes |
| `ubicaciones` | Oficinas/almacenes; FK `"departamento _d"` (columna con espacio) |
| `inventario` | Bienes: codigo_bn, marca, modelo, serial, condicion |
| `actividad_inventario` | Movimientos: Asignacion/Devolucion/Traslado/Baja/Mantenimiento |

#### Formación
| Tabla | Descripción |
|-------|-------------|
| `ubicaciones_formacion` | Sedes e instituciones; `es_sede_propia BOOL` |
| `talleres` | Actividades formativas; `tipo_actividad` ('Taller','Charla','Inducción'); `es_interna BOOL`; `tipo_ente VARCHAR(50)` *(006)* |
| `taller_informes` | Informe demográfico por taller (mujeres/hombres/niñas/niños) |
| `taller_inventario` | Préstamo de bienes a un taller (sin UI dedicada) |
| `participantes_taller` | Inscripción; `id_persona` nullable; `nombre_libre/apellido_libre/cedula_libre`; `es_brigadista BOOL`; `nombre_docente`; `cedula_docente` *(006)* |
| `pasantes` | Historial de pasantes; FK `id_persona` (migración 003) |
| `pasante_documentos` | Flags de documentos entregados |
| `oficios` | Oficios recibidos (externos → IMATUR); FK `id_oficio` en talleres externos |

#### Turismo
| Tabla | Descripción |
|-------|-------------|
| `rutas` | Itinerarios; `nivel_dificultad` CHECK; `requiere_formacion BOOL` *(006)*; `tiene_tarifa BOOL`, `tarifa_monto DECIMAL`, `nombre_facilitador_externo VARCHAR` *(007)* |
| `puntos_ruta` | Paradas con lat/lon y orden |
| `actividades_ruta` | Eventos programados por ruta |
| `ruta_inventario` | Bienes asignados a una ruta |
| `participantes_ruta` | Inscripción a rutas; modo libre para niños/as *(005)*; `id_institucion FK instituciones_externas` *(007)* |
| `instituciones_externas` | Instituciones educativas/empresas externas con flag `es_educativa` *(007)* |
| `visitantes` | Personas externas que visitan IMATUR físicamente |
| `visitas` | Marcaje entrada/salida; `id_empleado` (empleado visitado) |
| `oficios_emitidos` | Oficios salientes generados desde rutas *(005)* |

#### Geografía
| Tabla | Descripción |
|-------|-------------|
| `municipio` | Municipios con código postal; `created_at NOT NULL` sin DEFAULT |
| `parroquia` | Por municipio; nomenclatura inconsistente: `create_at`/`create_by` sin "d" |

---

### Migraciones — Estado de ejecución

| # | Archivo | Estado | Contenido |
|---|---------|--------|-----------|
| schema | `database/schema.sql` | ✅ Ejecutado | Schema base completo con datos de prueba |
| 001 | `001_visitantes_visitas.sql` | ✅ Ejecutado | visitantes, visitas |
| 002 | `002_rrhh_extensions.sql` | ✅ Ejecutado | horarios, permisos_laborales, vacaciones + auditoría |
| 003 | `003_normalize_pasantes.sql` | ✅ Ejecutado | pasantes → id_persona FK |
| 004 | `004_formacion_reglas_negocio.sql` | ✅ Ejecutado | tipo_actividad, es_sede_propia, oficios, participantes libre |
| 005 | `005_rutas_config_sistema.sql` | ✅ Ejecutado | rutas extendidas, participantes_ruta, configuracion_sistema, oficios_emitidos |
| 006 | `006_formacion_mejoras.sql` | ✅ Ejecutado | talleres: es_interna/tipo_ente; participantes_taller: es_brigadista/nombre_docente/cedula_docente; rutas: requiere_formacion; tipo_actividad: +Inducción |
| 007 | `007_mejoras_negocio.sql` | ✅ Ejecutado | condicion+En Reparación; rol 5 Recepción; correlativos por módulo; instituciones_externas; rutas+tarifa+facilitador_externo |
| 008 | `008_permisos_rol.sql` | ✅ Ejecutado | Tabla `permisos_rol`; convierte RBAC hardcoded a dinámico; seed con permisos de los 5 roles |
| 009 | `009_fix_sequences.sql` | ✅ Ejecutado | Resincroniza las 36 secuencias SERIAL desincronizadas por inserts con ID explícito en seeds |
| 010 | `010_taller_evidencias.sql` | ✅ Ejecutado | Tabla `taller_evidencias`; campo `talleres.motivo_cancelacion` |
| 011 | `011_visitantes_persona.sql` | ✅ Ejecutado | `visitantes.id_persona FK personas`; `nombre`/`apellido` nullable; migración de datos existentes |
| 012 | `012_participantes_libre_campos.sql` | ✅ Ejecutado | Campos demográficos para participantes libres (talleres) |
| 013 | `013_tipo_ruta_meta.sql` | ✅ Ejecutado | `rutas.tipo_ruta` + metas anuales |
| 014 | `014_config_metas_alertas.sql` | ✅ Ejecutado | Metas anuales + umbrales de alerta en `configuracion_sistema` |
| 015 | `015_rutas_motivo_mantenimiento.sql` | ✅ Ejecutado | `rutas.motivo_mantenimiento` |
| 016 | `016_puntos_ruta_orden_unico.sql` | ✅ Ejecutado | Índice único `(id_ruta, orden)` en `puntos_ruta` |
| 017 | `017_participantes_ruta_demograficos.sql` | ✅ Ejecutado | `genero_libre`/`fecha_nac_libre` en `participantes_ruta` |
| 018 | `018_ruta_informes.sql` | ✅ Ejecutado | Tabla `ruta_informes` (demografía post-visita) |
| 019 | `019_drop_ruta_inventario.sql` | ✅ Ejecutado | DROP TABLE `ruta_inventario` |
| 020 | `020_rutas_estado_finalizada.sql` | ✅ Ejecutado | Estado `Finalizada` (terminal) en `rutas` |
| 021 | `021_drop_nivel_dificultad.sql` | ✅ Ejecutado | DROP COLUMN `rutas.nivel_dificultad` |
| 022 | `022_validate_fk_constraints.sql` | ✅ Ejecutado | `VALIDATE CONSTRAINT` en 7 FKs que quedaron NOT VALID (sin huérfanos) |

> **Fuente única de verdad (2026-05-31):** `database/schema_consolidado.sql` consolida el esquema base + migraciones 001-021 (37 tablas) + seeds de sistema. Generado desde la BD viva y verificado (recrea todo sin errores). Para instalar desde cero usar **ese** archivo.

Para ejecutar una migración suelta: `PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f <ruta_archivo>`  
psql en Windows: `"C:\Program Files\PostgreSQL\17\bin\psql.exe"`

---

### Soft Delete
Todas las tablas tienen: `is_active BOOL`, `deleted_at TIMESTAMP`, `deleted_by INT`.  
Nunca se borran filas — se marcan inactivas. La papelera está en Auditoría → Papelera.

### Convención de auditoría
```
created_at, updated_at, deleted_at  ← TIMESTAMPS
created_by, updated_by, deleted_by  ← INT (id del usuario)
```
**Excepción:** `parroquia` usa `create_at`/`create_by` (sin "d").

---

## Reportes implementados

| Reporte | Roles | Export |
|---------|-------|--------|
| Asistencia con filtro de fechas | 1, 2 | CSV + PDF |
| Visitantes con filtro fecha/motivo | 1, 2 | CSV + PDF |
| Talleres con filtros estado/tipo | 1, 3 | CSV + PDF |
| Dossier integral de taller | 1, 3 | CSV |
| Participantes de un taller | 1, 3 | CSV |
| Rutas con filtros estado/dificultad | 1, 3 | CSV + PDF |
| Pasantes con estado y tutor | 1, 3 | CSV + PDF |
| Inventario con filtros condición/categoría | 1, 4 | CSV + PDF |
| Bienes dados de baja | 1, 4 | CSV |
| Indicadores KPIs (4 gráficas ApexCharts) | todos | — |

### Reportes pendientes de implementar
- Permisos laborales por tipo/empleado/período
- Saldo de vacaciones por empleado
- Informe trimestral de Formación (metas, logros, actividades)
- Indicadores ampliados: visitas activas, empleados en permiso hoy

---

## Frontend — Recursos locales

**Todos los recursos en `/public/assets/libs/` — sin CDN, sin internet.**

| Archivo | Versión |
|---------|---------|
| `bootstrap.min.css` / `bootstrap.bundle.min.js` | 5.3 |
| `bootstrap-icons.min.css` + `.woff2` + `.woff` + `.svg` | 1.11.3 |
| `apexcharts.min.js` | Latest |

Tipografía: `'Inter', system-ui, sans-serif` — Google Fonts eliminado. Sin internet funciona.

---

## Design System

| Archivo | Propósito |
|---------|-----------|
| `public/assets/css/sigtur-tokens.css` | Variables CSS: colores, tipografía, espaciado, dark mode |
| `public/assets/css/sigtur-components.css` | Componentes: `.app-shell`, `.sidebar`, `.sig-header`, `.btn-sig`, `.sig-card` |
| `public/assets/css/login.css` | Estilos exclusivos del login |
| `public/assets/js/sigtur-validations.js` | Validación y formateo client-side (cédulas, nombres, teléfonos) |

**Dark mode:** `data-theme="dark"` en `<html>`. Persiste en `localStorage['sigtur-theme']`.  
**Cache-busting JS:** Script src usa `?v=<?php echo filemtime(...); ?>` — se actualiza automáticamente.

---

## Convenciones de Código

### Sanitización de POST (crítico)

```php
// TODOS los controllers usan:
$_POST = $this->sanitizePost();  // definido en app/core/Controller.php
// Usa strip_tags() + trim() — NO FILTER_SANITIZE_FULL_SPECIAL_CHARS (corrompe tildes)
```

Para campos con CHECK constraint de enum, **siempre validar contra whitelist** después del sanitize:
```php
$nivelesValidos = ['Fácil','Moderado','Difícil','Extremo'];
$nivel = in_array($_POST['nivel_dificultad'] ?? '', $nivelesValidos)
    ? $_POST['nivel_dificultad'] : 'Fácil';
```

### Controllers

```php
public function index() {
    $data = ['titulo' => 'Titulo', 'items' => Model::all()];
    $this->view('modulo/index', $data);
}
```

### Protección de roles en reportes

```php
$this->requireRoles([1, 2]);  // al inicio del método
```

### Auditoría

```php
$this->logAudit('nombre_tabla', 'INSERT', $newId, null, $newData);
```

### Modal de eliminación global (footer.php)

Todos los botones de eliminación usan la clase `.delete-btn`. El modal global en `footer.php` detecta el contexto por URL y nombre del registro automáticamente. No se necesita JS adicional en cada vista.

### Toasts (notificaciones)

```php
// En controllers (PHP):
flash('global_msg', 'Mensaje de éxito.');
flash('global_msg', 'Error.', 'danger');

// En JS (para acciones ajax/inline):
showToast('Título', 'Mensaje', 'success'); // success | danger | warning | info
```

---

## Peculiaridades críticas

1. **`ubicaciones."departamento _d"`** — FK con espacio en el nombre. Siempre comillas dobles en SQL.

2. **`parroquia` nomenclatura inconsistente** — `create_at`/`create_by` sin "d". Los models Municipio y Parroquia manejan esto.

3. **`pasantes` normalizada (post-003)** — usa `id_persona FK`. Sin campos propios de cédula/nombre. JOINs siempre necesarios.

4. **Transacciones en Empleados** — INSERT en `personas` + `empleados` atómico con `beginTransaction` + `RETURNING id`.

5. **`municipio.created_at NOT NULL` sin DEFAULT** — pasar `created_at = NOW()` en INSERT.

6. **Visitas — patrón toggle** — `Visita::registrar()` detecta visita abierta; INSERT si no hay, UPDATE si hay. No crear dos registros.

7. **`taller_informes.total_atendidas`** — dato derivado (`mujeres + hombres + ninas + ninos`). Recalcular antes de guardar.

8. **`talleres.tipo_actividad` CHECK** — valores exactos: `'Taller'`, `'Charla'`, `'Inducción'`. (migración 006 añadió Inducción; 004 limitó a Taller/Charla).

9. **`talleres.es_interna`** — `TRUE` = actividad para personal IMATUR; no requiere oficio aunque la sede no sea propia. `tipo_ente` = NULL cuando interna.

10. **`participantes_taller.es_brigadista`** — para participantes con cédula. `nombre_docente`/`cedula_docente` para niños/as (libre).

11. **`rutas.nivel_dificultad` CHECK** — valores con tilde exactos: `'Fácil'`,`'Moderado'`,`'Difícil'`,`'Extremo'`. Validar siempre contra whitelist.

12. **`rutas.requiere_formacion`** — `TRUE` → el sistema verifica en `participantes_taller` que la persona asistió a al menos un taller antes de inscribir (RN-F12). Libres (niños) exentos.

13. **`talleres.id_oficio`** — FK nullable a `oficios`. Solo se asigna al crear actividad con sede externa y `es_interna = FALSE`.

14. **`configuracion_sistema`** — clave/valor para datos institucionales. `correlativo_oficio` se incrementa al generar oficio; `ano_correlativo` se reinicia automáticamente al cambiar de año.

15. **`AuditLog::log()`** — requiere `?array`. PDO retorna `stdClass`. El `Model::toArray()` hace el cast. **No pasar objetos directamente**.

16. **`AsistenciasController::marcar()`** — usa `$this->getUserId()` para registrar el usuario que marcó la asistencia. Bug corregido en fase 1.

17. **Máquina de estados de talleres (RN-F13)** — `TalleresController::validarTransicion()`. Terminales: Finalizado, Cancelado. No se puede Finalizar sin participantes.

18. **`empleados.tipo_contrato`** — valores: `'Fijo'`,`'Contratado'`,`'Suplente'`,`'Comisión de Servicio'`. DEFAULT `'Fijo'`.

19. **`configuracion_sistema` correlativos por módulo** — claves `correlativo_oficio_ruta`/`ano_correlativo_ruta` (renombradas desde 007). `ConfigSistema::generarNumeroOficio($modulo)` acepta parámetro de módulo. Formato resultado: `RUTA-007/2026` o `FORM-001/2026`.

20. **`inventario.condicion` CHECK** — ahora incluye `'En Reparación'`. Actualizar whitelist en todos los controladores que validen este campo: `['Nuevo','Bueno','Regular','Dañado','En Reparación']`.

21. **`inventario.codigo_bn` nullable** — puede ser NULL para bienes pendientes de código BN oficial. Mostrar "—" en vistas cuando sea NULL.

22. **`permisos_rol` — RBAC dinámico (migración 008)** — no modificar el RBAC tocando `Router.php`. La fuente de verdad es la tabla. `RolesController::getMapaRbac()` devuelve `[id_rol => '*']` (acceso total) o `[id_rol => ['Ctrl1', 'Ctrl2',...]]`. `DashboardController` se agrega automáticamente a todo rol en `storePermisos()`.

23. **`AuditLog::log()` en controllers** — `$this->audit()` y `$this->auditStatic()` son métodos `protected` de `Model`. Los **controllers** extienden `Controller`, no `Model` → usar `AuditLog::log()` directamente. Envolver en try-catch separado para no revertir la transacción principal si el log falla.

24. **Convención de manejo de errores** — Todo método público de controller que acceda a BD debe envolver el cuerpo en `try-catch (Exception $e)`. En caso de error: `flash('global_msg', $e->getMessage(), 'danger')` + `header('Location: ...')`. Los métodos de exportación (CSV/PDF) deben capturar excepciones **antes** de enviar cualquier header de descarga.

25. **Secuencias SERIAL (migración 009)** — Al insertar filas con IDs explícitos en seeds, las secuencias PostgreSQL no avanzan. Si aparece `llave duplicada viola restricción «X_pkey»`, ejecutar migración 009 (`009_fix_sequences.sql`) que usa `GREATEST(MAX(id), last_value)` para resincronizar las 36 secuencias sin riesgo de retroceso.

---

## Pasos para levantar el entorno

```bash
# 1. Laragon activo con PHP 8+ y PostgreSQL 17
# 2. Crear la base de datos:
createdb -U postgres "SIGTUR-IMATUR"

# 3. Importar el esquema consolidado (schema base + migraciones 001-021 + seeds):
PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/schema_consolidado.sql

# 5. Verificar config/config.php:
#    DB_HOST=localhost | DB_PORT=5432 | DB_NAME=SIGTUR-IMATUR
#    DB_USER=postgres  | DB_PASS=1234 (entorno Laragon)

# 6. URL: http://SIGTUR-IMATUR.test  o  http://localhost/SIGTUR-IMATUR/public
```

> **Nota:** `database/schema_consolidado.sql` cubre schema base + migraciones 001-021 + seeds de sistema. No se necesitan migraciones adicionales en una instalación limpia. (`schema_completo.sql` queda obsoleto — solo cubría hasta la 011.)

---

## Documentación de reglas de negocio

| Archivo | Módulo |
|---------|--------|
| `docs/REGLAS_NEGOCIO_Formacion.md` | Talleres, Charlas, Inducciones |
| `docs/REGLAS_NEGOCIO_Rutas.md` | Rutas Turísticas |
| `docs/REGLAS_NEGOCIO_Pasantes.md` | Pasantes |
| `docs/REGLAS_NEGOCIO_RRHH.md` | Empleados, Asistencias, Permisos, Vacaciones |
| `docs/REGLAS_NEGOCIO_Inventario.md` | Bienes e Inventario |
| `docs/REGLAS_NEGOCIO_Visitantes.md` | Visitantes y Control de Visitas |
| `docs/ESTRUCTURA_ORGANIZATIVA.md` | Organigrama y análisis de jerarquía |
| `docs/preguntas_modelo_negocio.md` | 160 preguntas con estado ✅ ⚠️ ❓ |
| `docs/INDICADORES_GESTION.md` | **Todos los indicadores de gestión**: propósito, fórmula y fuente de datos (Dashboard + página RF30 + stats por reporte) |
| `docs/ANALISIS_MODULOS_FORMACION_TURISMO.md` | Análisis funcional de Formación y Turismo (estado, reglas, validaciones, backlog) |

> **Nota (2026-05-31):** migraciones aplicadas hasta la **021**. Cambios recientes en Turismo: `nivel_dificultad` eliminado (021), estado `Finalizada` (020), `ruta_inventario` eliminado (019), `ruta_informes` (018); módulos **Instituciones externas** y **Actividades de ruta** retirados del sistema. Ver `INDICADORES_GESTION.md` y la memoria interna para el detalle.

---

## Archivos clave de referencia

| Propósito | Archivo |
|-----------|---------|
| Configuración global + constantes | `config/config.php` |
| Conexión DB (PDO wrapper) | `app/core/Database.php` |
| Router + RBAC middleware | `app/core/Router.php` |
| Sanitización POST (sanitizePost) | `app/core/Controller.php` |
| AuditLog + toArray fix | `app/core/Model.php` |
| Flash messages / Toast | `app/helpers/session_helper.php` |
| Layout principal + sidebar RBAC | `app/views/inc/header.php` |
| Scripts + toasts + modal eliminación | `app/views/inc/footer.php` |
| Validaciones JS (nombres, cédulas) | `public/assets/js/sigtur-validations.js` |
| Config institucional (correlativo) | `app/models/ConfigSistema.php` |
| Schema consolidado (instalar desde cero) | `database/schema_consolidado.sql` (001-021 + seeds) |
| Auditoría senior + deuda técnica | `docs/AUDITORIA_SENIOR_2026-05-31.md` |
| Preguntas de negocio abiertas | `docs/preguntas_modelo_negocio.md` |
| Schema base original (historial) | `database/schema.sql` |
