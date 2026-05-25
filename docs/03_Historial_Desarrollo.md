# Historial de Desarrollo — SIGTUR-IMATUR

**Última actualización:** 2026-05-22  
Sistema para IMATUR (Instituto Municipal de Turismo de Cumaná, Sucre, Venezuela).

---

## Resumen de Fases

| Fase | Estado | Descripción |
|------|--------|-------------|
| Fase 1 | ✅ Completada | Core MVC + módulos base + RBAC + reportes |
| Fase 2 | ✅ Completada | Reglas de negocio + oficios + documentos imprimibles |
| Fase 2.5 | ✅ Completada | Corrección de bugs + mejoras de calidad |
| Fase 3 | 🔄 Pendiente | RRHH extensiones + libro correspondencia + mapa offline |
| Fase 4 | 📋 Backlog | Importación datos históricos + multi-facilitadores |

---

## Fase 1 — Core y Módulos Base

### Arquitectura MVC
- Front controller en `public/index.php`
- Router dinámico + middleware RBAC en `app/core/Router.php`
- PDO wrapper con prepared statements en `app/core/Database.php`
- Base controllers/models con `sanitizePost()`, `toArray()`, `logAudit()`
- Session helper: `flash()` + toasts asíncronos

### Schema base (`database/schema.sql`)
Tablas iniciales: `roles`, `usuarios`, `personas`, `departamentos`, `cargos`, `empleados`, `asistencias`, `categorias`, `ubicaciones`, `inventario`, `actividad_inventario`, `ubicaciones_formacion`, `talleres`, `taller_informes`, `pasantes`, `pasante_documentos`, `rutas`, `puntos_ruta`, `actividades_ruta`, `ruta_inventario`, `municipio`, `parroquia`, `audit_logs`.

### Módulos implementados
- **RRHH:** Empleados (INSERT atómico personas+empleados), Cargos, Departamentos, Asistencias (toggle)
- **Inventario:** Bienes, Categorías, Ubicaciones, Movimientos
- **Formación:** Talleres, Ubicaciones de formación, Pasantes, Informes demográficos
- **Turismo:** Rutas, Puntos de ruta, Actividades de ruta
- **Sistema:** Usuarios, Roles, Auditoría + Papelera lógica global
- **Reportes:** Asistencia, Talleres, Rutas, Pasantes (CSV + PDF), Dashboard con 4 KPIs (ApexCharts)
- **RBAC:** 4 roles (Admin, RRHH, Turismo, Inventario)

### Design System
- `sigtur-tokens.css` (CSS vars), `sigtur-components.css`, `login.css`
- `sigtur-validations.js` — validación de cédulas, nombres, fechas
- Bootstrap 5.3 + Icons 1.11.3 — 100% local, sin CDN
- Dark mode via `data-theme="dark"` + `localStorage`

### Bugs corregidos en Fase 1
- **Falsos positivos de BD:** `PDO::ATTR_PERSISTENT = false` — evita deadlocks al mezclar UPDATE + audit_log en misma transacción
- **Fechas vacías en PostgreSQL:** Conversión de `""` a `null` en modelos antes de enviar a PDO
- **CHECK constraint `audit_logs.operacion`:** Ampliado para incluir `RESTORE` y `UPDATE`
- **`sanitizePost()`:** Cambiado de `FILTER_SANITIZE_FULL_SPECIAL_CHARS` a `strip_tags() + trim()` para no corromper tildes

---

## Migración 001 — Visitantes y Visitas

**Archivo:** `database/migrations/001_visitantes_visitas.sql`

- Tabla `visitantes`: personas externas que ingresan a IMATUR (trámites, reuniones)
- Tabla `visitas`: registro entrada/salida con patrón toggle (abre si no hay visita activa; cierra si hay)
- Campo `id_empleado` (FK opcional): empleado que atendió al visitante

---

## Migración 002 — Extensiones RRHH y Auditoría

**Archivo:** `database/migrations/002_rrhh_extensions.sql`

- Tablas nuevas: `horarios` (turnos), `permisos_laborales` (ausencias), `vacaciones` (control anual)
- Columnas en `empleados`: `tipo_contrato` ('Fijo','Contratado','Suplente','Comisión de Servicio'), `fecha_egreso`, `id_horario`
- Auditoría extendida a: `taller_informes`, `taller_inventario`, `participantes_taller`, `pasantes`, `pasante_documentos`, `ruta_inventario`
- Tabla `participantes_taller` — inscripción individual con `asistio BOOL`

**Nota:** `horarios`, `permisos_laborales`, `vacaciones` — tablas creadas, sin UI ni controlador dedicado. Pendiente Fase 3.

---

## Migración 003 — Normalización Pasantes → Personas

**Archivo:** `database/migrations/003_normalize_pasantes.sql`

- Añade `id_persona INT FK` a `pasantes`
- Migra datos: para cada pasante activo, busca o crea la `persona` equivalente por cédula
- Elimina columnas redundantes (`cedula`, `nombre`, `apellido`) de `pasantes`
- **Requiere ejecutarse después de 001 y 002**

---

## Migración 004 — Formación: Reglas de Negocio

**Archivo:** `database/migrations/004_formacion_reglas_negocio.sql`

- `talleres.tipo_actividad` CHECK: `'Taller'`, `'Charla'` (ampliado en 006 para incluir `'Inducción'`)
- `ubicaciones_formacion.es_sede_propia BOOL` — distingue sedes propias de externas
- Tabla `oficios` — oficios recibidos (externos → IMATUR), con número, fecha, asunto
- `talleres.id_oficio FK` nullable — solo para actividades externas con oficio soporte
- `participantes_taller` modo libre: campos `nombre_libre`, `apellido_libre`, `cedula_libre` para niños/as sin cédula

---

## Migración 005 — Rutas Extendidas + Config Sistema

**Archivo:** `database/migrations/005_rutas_config_sistema.sql`

- Tabla `configuracion_sistema` (clave/valor): `director_nombre`, `director_apellido`, `rif_institucion`, `correlativo_oficio`, etc.
- Tabla `oficios_emitidos` — oficios salientes generados desde rutas
- `participantes_ruta` extendido con modo libre para niños/as
- Generación de oficio de visita con correlativo `001/2026` (reinicia cada año)
- Vista imprimible standalone `rutas/oficio_imprimible.php` (sin layout del sistema)
- Controlador `ConfigController` + vistas de configuración institucional

---

## Migración 006 — Formación: Mejoras de Negocio

**Archivo:** `database/migrations/006_formacion_mejoras.sql`

- `talleres.es_interna BOOL` — actividad para personal IMATUR propio (sin oficio externo)
- `talleres.tipo_ente VARCHAR(50)` — para externas: `'Escuela'`,`'Liceo'`,`'Comunidad'`,`'Prestador de Servicio'`
- `talleres.tipo_actividad` ampliado: añade `'Inducción'`
- `participantes_taller.es_brigadista BOOL` — integrantes frecuentes de instituciones externas
- `participantes_taller.nombre_docente`, `cedula_docente` — acompañante para niños/as en externas
- `rutas.requiere_formacion BOOL` — prerequisito de asistencia a taller para inscribirse en ruta
- Lista de asistencia imprimible institucional (`talleres/lista_asistencia.php`)
- Informe de actividad imprimible (`talleres/informe_imprimible.php`)

---

## Migración 007 — Mejoras de Negocio (Transversal)

**Archivo:** `database/migrations/007_mejoras_negocio.sql`

- `inventario.condicion` CHECK: añade `'En Reparación'` a la lista de condiciones válidas
- `inventario.codigo_bn` ahora nullable — para bienes sin código BN asignado aún
- Rol 5 "Recepción" — acceso solo a Dashboard, Visitantes, Visitas, Asistencias
- Tabla `instituciones_externas` con `es_educativa BOOL` — para grupos escolares en rutas
- `participantes_ruta.id_institucion FK instituciones_externas` (nullable) — agrupación por institución
- `rutas.tiene_tarifa BOOL` + `tarifa_monto DECIMAL(10,2)` — para Cumaná Histórica
- `rutas.nombre_facilitador_externo VARCHAR(200)` — guías externos no empleados de IMATUR
- Correlativos por módulo en `configuracion_sistema`: `correlativo_oficio_ruta`/`ano_correlativo_ruta` y `correlativo_oficio_formacion`/`ano_correlativo_formacion`
- Formato de correlativo: `RUTA-007/2026` o `FORM-001/2026`
- `configuracion_sistema.firmante_cargo` — cargo del firmante de documentos institucionales

---

## Fase 2 — Documentos Imprimibles y Reglas de Negocio

### Documentos generados desde el sistema
| Documento | Ruta vista | Módulo |
|-----------|-----------|--------|
| Oficio de visita para ruta | `rutas/oficio_imprimible.php` | Turismo |
| Lista de asistencia institucional | `talleres/lista_asistencia.php` | Formación |
| Informe de actividad imprimible | `talleres/informe_imprimible.php` | Formación |
| Carta de culminación de pasante | `pasantes/carta_culminacion.php` | Pasantes |

### Mejoras de Dashboard
- 5 KPIs: Trabajadores Activos, Talleres Finalizados (mes), Rutas Ejecutadas (mes), Bienes Activos, Logs Recientes
- Alertas: contratos próximos a vencer (30 días), pasantes próximos a culminar (15 días), talleres en curso hoy

### Roles y control de acceso
- Rol 5 "Recepción" creado y configurado en Router.php + header.php
- `ReportesController::requireRoles()` en todos los métodos de exportación

### Máquina de estados
- `TalleresController::validarTransicion()` — bloquea transiciones inválidas
- `PasantesController::editar()` — restricción rol 1 para Postulado→Aceptado

---

## Fase 2.5 — Corrección de Bugs

| Bug | Archivo corregido | Descripción |
|-----|------------------|-------------|
| `AsistenciasController::marcar()` | `app/controllers/AsistenciasController.php` | `$user_id = 1` hardcodeado → usar `$this->getUserId()` |
| `taller_informes.total_atendidas` | `app/models/TallerInforme.php` | Recalcular antes de guardar (mujeres+hombres+niñas+niños) |
| `audit_logs.fecha` | `app/controllers/DashboardController.php` | Columna correcta es `fecha`, no `created_at` |
| Correlativo por módulo | `app/models/ConfigSistema.php` | `generarNumeroOficio(string $modulo)` acepta parámetro |

---

## Schema consolidado

**Archivo:** `database/schema_completo.sql`  
Reemplaza `schema.sql` + migraciones 001-007 individuales. Para instalación limpia, usar solo el schema consolidado.

```bash
PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/schema_completo.sql
```

---

## Backlog — Fase 3 (próxima)

### Requiere respuestas en `preguntas_modelo_negocio.md`
- HorariosController — turnos + asignación a empleados (requiere D-RH01, D-RH08)
- PermisosLaboralesController — CRUD + reporte (requiere D-RH02, D-RH03, D-NEW04)
- VacacionesController — saldo + días tomados (requiere D-RH04, D-RH05, D-RH06, D-NEW05)

### Puede implementarse sin respuestas pendientes
- Libro de correspondencia unificado (oficios enviados + recibidos)
- Informe de gestión trimestral consolidado (todas las secciones)
- InstitucionesExternasController + CRUD (requiere confirmar D-NEW02)
- Contraseña por defecto = cédula al crear usuario (flag `password_debe_cambiar`)

### Backlog Fase 4
- Mapa visual de puntos de ruta (Leaflet.js + OpenStreetMap offline)
- Módulo de cobro tarifa Cumaná Histórica (requiere confirmar D-RT02)
- Importación de datos históricos desde Excel/CSV (requiere D-TX03)
- Múltiples facilitadores por taller (tabla `taller_facilitadores`)
- Rol 6 "Solo Lectura" (requiere confirmar alcance D-US03)
- Reporte de puntualidad/ausentismo para nómina (requiere D-RH09)
