# CLAUDE.md — SIGTUR-IMATUR
**Última actualización:** 2026-06-21 (migraciones hasta 050; ver `docs/BACKLOG.md` para pendientes)  
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
| 2 | RRHH | Dashboard, Empleados, Cargos, Departamentos, Horarios, Amonestaciones, Permisos, Asistencias, Visitantes, Visitas, Reportes, Config |
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
| `permisos`, `exportarPermisosCsv` | [1, 2] |
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
| `departamentos` | Unidades organizativas **jerárquicas** (`id_padre` auto-FK + `tipo_unidad`); seed del organigrama oficial *(027)* |
| `cargos` | Puestos por **nivel jerárquico** (Presidencia/Dirección/Coordinación/Adscrito); sin sueldo_base *(035)* |
| `empleados` | 1:1 con personas; FK a cargo/departamento/horario; `tipo_contrato`, `fecha_egreso` |
| `asistencias` | Marcaje diario entrada/salida (patrón toggle) |
| `horarios` *(002; UI + seed en 028)* | Catálogo de turnos asignables (Estándar, OAC Matutino/Vespertino, Servicios Generales, personalizados) |
| `permisos_laborales` *(002; UI + categoría/duración en 032)* | Permisos y reposos: `categoria` (Reposo/Permiso), `tipo_permiso` (taxonomía), fechas, `estado` aprobación |
| `vacaciones` *(002, sin UI)* | Control anual de días |
| `carga_familiar` *(026)* | Familiares del empleado (FK `id_persona`); bloque de la Ficha Técnica |
| `cursos_realizados` *(026)* | Cursos por persona (FK `id_persona`); bloque de la Ficha Técnica |
| `experiencia_laboral` *(026)* | Trabajos anteriores (FK `id_persona`); bloque de la Ficha Técnica |
| `expediente_documentos` *(033)* | Recaudos subidos del expediente (FK `id_empleado`); checklist + faltantes |
| `faltas` *(031)* | Faltas injustificadas por empleado (RRHH); el sistema las cuenta |
| `amonestaciones` *(031)* | Amonestaciones por empleado (RRHH); 3 activas = causa de despido |
| `constancias` *(034)* | Historial de constancias de trabajo emitidas (FK `id_empleado`); correlativo CONST-NNN/AAAA |

Nota: `horarios`, `permisos_laborales`, `vacaciones` existen desde migración 002. Sin UI. Pendiente respuestas D-RH01–D-RH11. Las tablas hijas de la Ficha Técnica (`carga_familiar`/`cursos_realizados`/`experiencia_laboral`) ya tienen UI en el expediente del empleado (`/empleados/detalle/{id}`).

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
| ~~`taller_inventario`~~ | **ELIMINADA** (mig.050, D-FO07 — no se usaba) |
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
| 023 | `023_genero_solo_mf.sql` | ✅ Ejecutado | CHECK `genero IN ('M','F')` en personas, visitantes, participantes_taller, participantes_ruta — elimina 'O' |
| 024 | `024_pasantes_carta_aceptacion.sql` | ✅ Ejecutado | `pasantes.oficio_aceptacion`/`tutor_externo` (carta de aceptación PAST-NNN/AAAA) |
| 025 | `025_empleados_contrato_origen.sql` | ✅ Ejecutado | `empleados`: DEFAULT `tipo_contrato`→'Contratado'; CHECK solo (Fijo,Contratado); deprecadas 'Suplente'/'Comisión de Servicio'; +`institucion_origen` (Alcaldía/Gobernación/IMATUR) +`es_comision_servicio` bool (R-3 RRHH) |
| 026 | `026_empleado_ficha_tecnica.sql` | ✅ Ejecutado | `personas` +rif/estado_civil/discapacidad(+detalle)/nivel_academico/profesion/titulo/fecha_graduacion/institucion_academica; `empleados` +clasificacion (Empleado/Obrero); nuevas tablas `carga_familiar`, `cursos_realizados`, `experiencia_laboral` (R-2 RRHH — Ficha Técnica) |
| 027 | `027_departamentos_jerarquia.sql` | ✅ Ejecutado | `departamentos` +id_padre (auto-FK) +tipo_unidad (Presidencia/Dirección/Coordinación/Oficina/Unidad); seed del organigrama oficial (Presidencia→3 Direcciones→Coordinaciones+staff); cargos +Presidenta/Coordinador (R-1 RRHH) |
| 028 | `028_horarios_grupos.sql` | ✅ Ejecutado | seed `horarios` (Estándar, OAC Matutino/Vespertino, Servicios Generales); `empleados` +grupo_rotacion (A/B); `configuracion_sistema` +minutos_tolerancia_puntualidad=15; RBAC +HorariosController (rol 2) (R-6 RRHH) |
| 029 | `029_asistencia_puntualidad.sql` | ✅ Ejecutado | `asistencias` +minutos_tarde (retraso vs horario asignado, calculado al marcar entrada) (R-7 RRHH) |
| 030 | `030_uniforme_comunitarios.sql` | ✅ Ejecutado | `personas` +centro_votacion/consejo_comunal/comuna; `empleados` +uniforme/talla_camisa/talla_pantalon/talla_zapato (R-2b RRHH — campos del wizard) |
| 031 | `031_faltas_amonestaciones.sql` | ✅ Ejecutado | tablas `faltas` y `amonestaciones` (FK id_empleado); RBAC +AmonestacionesController (rol 2) (R-9 RRHH) |
| 032 | `032_permisos_reposos.sql` | ✅ Ejecutado | `permisos_laborales` +categoria (Reposo/Permiso/Vacaciones) +duracion; nueva taxonomía `tipo_permiso`; RBAC +PermisosController (rol 2) (R-8 RRHH — sin vacaciones) |
| 033 | `033_expediente_documentos.sql` | ✅ Ejecutado | tabla `expediente_documentos` (recaudos subidos por empleado, FK id_empleado) (R-5 RRHH) |
| 034 | `034_constancias.sql` | ✅ Ejecutado | tabla `constancias` (FK id_empleado) + claves correlativo `*_constancia`; genera CONST-NNN/AAAA (R-10 RRHH) |
| 035 | `035_cargos_jerarquia.sql` | ✅ Ejecutado | `cargos`: **DROP `sueldo_base`** (IMATUR no distingue sueldo por cargo, D-RH11) + `nivel_jerarquico` (Presidencia/Dirección/Coordinación/Adscrito); seed de niveles |
| 036 | `036_egreso_empleados.sql` | ✅ Ejecutado | `empleados`: `motivo_egreso`/`observacion_egreso` (`fecha_egreso` ya existía) + tabla `empleados_egresos` (historial egreso/reingreso, índice único parcial de egreso abierto). Egreso ≠ papelera (R-12) |
| 037 | `037_normalizar_cedulas.sql` | ✅ Ejecutado | Data-only: normaliza `personas.cedula`/`visitantes.cedula`/`participantes_taller.cedula_docente` a **solo dígitos** (quita V-/E-/puntos), con guarda anti-colisión (omite filas que violarían UNIQUE → posibles duplicados a depurar). NO toca `cedula_libre` (ID escolar, alfanumérico) |
| 038 | `038_representante_participante_ruta.sql` | ✅ Ejecutado | `participantes_ruta`: `nombre_representante`/`cedula_representante` (ancla de identidad del menor sin cédula; talleres ya lo tiene como `nombre_docente`/`cedula_docente`) |
| 039 | `039_carga_familiar_genero_estado.sql` | ✅ Ejecutado | `carga_familiar`: `genero` (M/F) + `vive` (bool, vivo/fallecido). Habilita el reporte detallado de carga familiar con filtros (sexo, estado, edad, N° familiares) |
| 040 | `040_nro_expediente_auto.sql` | ✅ Ejecutado | Data-only: el folio `empleados.nro_expediente` es **automático y permanente** = `EXP-####` derivado del `id`. Rellena/normaliza folios NULL o no conformes. El campo ya **no se edita en la UI** (lo asigna `Empleado::save()`); preview `Empleado::proximoNumeroExpediente()` (B2) |
| 041 | `041_fecha_vencimiento_contrato.sql` | ✅ Ejecutado | `empleados.fecha_vencimiento_contrato` (DATE): **vencimiento del contrato** (futuro, solo Contratados) separado de `fecha_egreso` (salida real, R-12). Resuelve el choque semántico (`all()` filtra `fecha_egreso IS NULL`). El form lo valida (≥ ingreso + 3 meses, oculto para Fijos); `save()` ya NO toca `fecha_egreso`; la alerta "contratos por vencer" del Dashboard usa esta columna (B4) |
| 043 | `043_rif_institucional.sql` | ✅ Ejecutado | Siembra `configuracion_sistema.rif_institucional` = `G-20008498-7` (idempotente). Fuente única del RIF; `ConfigSistema::rif()` lo lee (fallback `RIF_DEFAULT`). Editable en `/config`; expuesto al JS como `window.SIGTUR_RIF`. Reemplaza 13 hardcodes en documentos/reportes y corrige el RIF erróneo de la carta de aceptación de pasantes (U7) |
| 045 | `045_vacaciones_base.sql` | ✅ Ejecutado | Base de Vacaciones (R-8/3A): `empleados.fecha_ingreso_administracion` (antigüedad total para comisionados); tabla `feriados` (+seed nacionales/Cumaná); `vacaciones` pasa a registro de períodos (se elimina UNIQUE(empleado,anio)); RBAC `VacacionesController` (rol 2). Lógica en `Vacacion` (15 hábiles + 1/año, tope 30; días excluyen finde+feriados; saldo acumulado) y `Feriado` |
| 050 | `050_limpieza_formacion.sql` | ✅ Ejecutado | Limpieza (3F): **DROP** `participantes_taller.es_brigadista` (D-FO08) y **DROP TABLE** `taller_inventario` (D-FO07) — no se usaban. `Taller::inscribir()` ya no recibe `$esBrigadista` |
| 049 | `049_horario_estandar_8_2.sql` | ✅ Ejecutado | Horario "Estándar" 8am-4pm → **8am-2pm** (cambio institucional, O5). Data-only idempotente |
| 048 | `048_falta_tipo_escalado.sql` | ✅ Ejecutado | `faltas.tipo` (Inasistencia injustificada / Incumplimiento de reglas) + `amonestaciones.id_falta_origen` (vínculo). Escalado falta→amonestación (`amonestarDesdeFalta`); UI con tipo, columna y botón "Generar amonestación" (3E) |
| 047 | `047_empleado_traslados.sql` | ✅ Ejecutado | Traslado de personal entre departamentos (3D/O3): tabla `empleado_traslados` (origen/destino depto y cargo, fecha, motivo). **Reasignación con historial** (sin flujo de aprobación): `Empleado::trasladar()` (transaccional, actualiza depto/cargo + registra histórico) y `historialTraslados()`; sección + modal en el expediente |
| 046 | `046_vacaciones_ajuste_inicial.sql` | ✅ Ejecutado | `empleados.vacaciones_ajuste_dias` (días ya disfrutados antes del sistema). `saldo = acumulado − ajuste − períodos`. UI en `/vacaciones` (roster, detalle, feriados). **Cobro/liquidación pendiente (nómina, 3B)** |
| 044 | `044_inventario_tipo_bien.sql` | ✅ Ejecutado | `inventario` +`tipo_bien` (Durable/Fungible, CHECK) +`cantidad` (≥1). Normaliza `codigo_bn`/`serial` vacíos a NULL. Durable = inventariable (Código BN obligatorio); Fungible = consumible (sin código/serial, con cantidad). `Inventario::TIPOS_BIEN`/`TIPO_BIEN_BADGES`; validación por tipo en `InventarioController::store()` + toggle en el modal (U5) |
| 042 | `042_motivo_anulacion_disciplina.sql` | ✅ Ejecutado | `amonestaciones.motivo_anulacion` + `faltas.motivo_anulacion` (TEXT): al anular una falta/amonestación RRHH registra el porqué (modal POST `eliminarFalta`/`eliminarAmonestacion`, motivo obligatorio). Alerta "Causa de despido" para **todos** (3+ amonestaciones) con botón **Procesar despido** → `empleados/detalle?egreso=despido` (preselecciona motivo Despido). Lista de empleados muestra columna **Disciplina** (conteo amonestaciones/faltas vía `Empleado::all()`) (B14) |

> **Fuente única de verdad (2026-05-31):** `database/schema_consolidado.sql` consolida el esquema base + migraciones 001-023 (37 tablas) + seeds de sistema. Generado desde la BD viva y verificado (recrea todo sin errores). El DDL de `personas`/`empleados`/`departamentos`/`asistencias` ya refleja además las migraciones **025–039** (columnas/constraints; `empleados` incluye `motivo_egreso`/`observacion_egreso`; `participantes_ruta` incluye `nombre_representante`/`cedula_representante`). Para una instalación completa: importar el consolidado y luego aplicar las migraciones **024–039** desde `database/migrations/` (idempotentes; la 026 crea `carga_familiar`/`cursos_realizados`/`experiencia_laboral`, la 027 siembra el organigrama, la 028 siembra `horarios` + config de puntualidad, la 029 agrega `asistencias.minutos_tarde`, la 030 agrega uniforme/datos comunitarios, la 031 crea faltas/amonestaciones, la 032 amplía permisos_laborales, la 033 crea expediente_documentos, la 034 crea constancias, la 035 reemplaza sueldo_base por nivel_jerarquico en cargos, la 036 agrega egreso/reingreso de empleados + tabla `empleados_egresos`, la 037 normaliza cédulas a solo dígitos, la 038 agrega representante del participante sin cédula en rutas, la 039 agrega genero/vive a carga_familiar).

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
| Asistencia con filtro de fechas (+ puntualidad y horas) | 1, 2 | CSV + PDF |
| Permisos y reposos por tipo/estado/período | 1, 2 | CSV |
| Visitantes con filtro fecha/motivo | 1, 2 | CSV + PDF |
| Talleres con filtros estado/tipo | 1, 3 | CSV + PDF |
| Dossier integral de taller | 1, 3 | CSV |
| Participantes de un taller | 1, 3 | CSV |
| Rutas con filtros estado/dificultad | 1, 3 | CSV + PDF |
| Pasantes con estado y tutor | 1, 3 | CSV + PDF |
| Inventario con filtros condición/categoría | 1, 4 | CSV + PDF |
| Bienes dados de baja | 1, 4 | CSV |
| Indicadores KPIs (4 gráficas ApexCharts) | todos | — |

### Reportes / indicadores RRHH (módulos 025-034)
- **Reporte de Asistencia** ahora incluye **puntualidad** (impuntual vs tolerancia) y **horas** trabajadas + KPIs (impuntuales, horas totales).
- **Reporte de Permisos y Reposos** (`reportes/permisos`, CSV) por categoría/estado/período.
- **Indicadores** (`reportes/indicadores`) sección Personal: clasificación (Empleado/Obrero), permisos/reposos vigentes hoy + pendientes, amonestaciones (empleados + en causa de despido), impuntualidad del mes.

### Reportes pendientes de implementar
- Saldo de vacaciones por empleado (bloqueado: fórmula D-RH04/05)
- Informe trimestral de Formación (metas, logros, actividades)

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
| `public/assets/js/sigtur-validations.js` | Validación y formateo client-side (cédulas, nombres, **teléfonos VE prefijo+7**, **edad/fecha de nacimiento**, **correos**) |

**Dark mode:** `data-theme="dark"` en `<html>`. Persiste en `localStorage['sigtur-theme']`.  
**Cache-busting JS/CSS:** `sigtur-validations.js` y los CSS del sistema (`sigtur-tokens.css`, `sigtur-components.css`) usan `?v=<?php echo filemtime(...); ?>` — se actualizan automáticamente al editarlos.

**Correo electrónico (validación global, automática):** todo `<input>` cuyo `name`/`id` contenga `correo`/`email` (o `type="email"`) se valida en cliente vía `initEmailInput` (`sigtur-validations.js`): bloquea espacios y **símbolos especiales** (saneo en vivo al set `[A-Za-z0-9._%+-@]`), exige formato `nombre@dominio.com` (`pattern` + `setCustomValidity` → integra con "botón deshabilitado hasta válido"). En el **servidor** usar `Controller::emailValido($email)` (mismo criterio: `filter_var` + regex sin símbolos especiales) — ya aplicado en Empleados/Rutas/Talleres/Visitantes. Front y back comparten el regex `^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$`.

**Cálculo de edad (centralizado):** la edad NUNCA se almacena; siempre se deriva de la fecha de nacimiento. En PHP usar **`Util::edad($fecha): ?int`** (años cumplidos, null si vacía/inválida/futura) y `Util::edadTexto($fecha)` ("N años"/"—") — `app/core/Util.php`, autoload. En cliente, `sigturEdad()` (`sigtur-validations.js`). En SQL, `EXTRACT(YEAR FROM age(fecha))`. Las tres dan años cumplidos y se actualizan solas en cada lectura (no hay edad cacheada). No reintroducir cálculos inline `->diff()->y`.

**Transiciones automáticas por fecha/hora (tarea programada):** los talleres pasan de **Programado → En Curso** al llegar su fecha/hora de inicio (con participantes) vía `Taller::autoTransicionarProgramados()`. Se ejecuta de dos formas: (1) **perezosa**, al abrir Talleres o el Dashboard (respaldo, idempotente); (2) **servidor**, con el script CLI `cron/actualizar_estados.php` programado en el Programador de tareas de Windows / cron de Laragon (~cada 10 min) para que corra aunque nadie use el sistema. Un trigger de BD NO sirve (los triggers no reaccionan al paso del tiempo); por eso es tarea programada. El script está fuera de `public/` (no accesible por web) y solo corre en CLI.

**Reportes (centro de reportes):** `reportes/index` es data-driven (arreglo `$secciones` con RBAC por rol). Para un reporte tabular nuevo: agregar método en `ReportesController` que arme `columnas`+`filas` (celda string = escapada; `['raw'=>'<html>']` = sin escapar, para badges), `resumen` (tiles), `filtros` (GET) y `export_url`, y renderice la **vista genérica `reportes/tabla.php`** + un `exportarXCsv()` con `exportCsv()`; luego añadir la tarjeta en `reportes/index`. **`exportCsv($filename,$headers,$rows)` exporta Excel con formato** (HTML-como-`.xls`: membrete institucional, encabezados en color, bordes, zebra; celdas como texto para preservar cédulas/códigos con ceros). Sin librerías ni internet. Reportes actuales incluyen RRHH (directorio, asistencia, permisos, amonestaciones, egresos, comisión, constancias, expedientes incompletos), Formación/Turismo (talleres, cobertura por parroquia, rutas, participación), Inventario (inventario, kardex, bienes asignados, bajas), Seguridad (auditoría) y Centro de Alertas. **Impresión/PDF:** botón `window.print()` + reglas `@media print` (ocultan sidebar/header/controles) → cualquier vista imprime/exporta a PDF limpia; marcar con `.no-print` lo que no deba imprimirse.

**Listados con búsqueda + paginación (convención global, opt-in):** agregar `data-tabla-buscable` al contenedor `.sig-table-wrap` (que envuelve un `table.sig-table`) inyecta una **barra de búsqueda** arriba y un **paginador** abajo, del lado cliente (`initTablasBuscables` en `sigtur-validations.js`). Opcionales: `data-por-pagina` (default 10) y `data-buscar-placeholder`. Filtra filas por texto y pagina; ignora la fila de estado vacío. Aplicado en los índices de varios módulos (empleados, inventario, pasantes, usuarios, amonestaciones, permisos, y catálogos). **Excepciones (paginación del lado SERVIDOR, no usar el helper):** `talleres`, `rutas`, `auditoría`, **`asistencias` y `visitantes`** paginan en el backend (modelos con `paginate($pagina,$porPagina,$filtros)` → `['items','total']`; controlador lee `$_GET['p']`+filtros; vista con form GET de filtro y nav de páginas que preserva filtros). Patrón de referencia: `Taller::paginate` / `Asistencia::paginate` / `Visita::paginate`. El helper cliente filtra sobre las filas ya cargadas; para volúmenes grandes usar siempre paginación servidor.

**Exportación de listados a Excel/PDF (convención global, automática):** todo `.sig-table-wrap` con `data-tabla-buscable` obtiene **automáticamente** botones **Excel** y **PDF** en su barra (`initTablasBuscables` → `sigturExportarTabla` en `sigtur-validations.js`). Sin tocar controladores ni vistas. Exporta el conjunto **filtrado completo** (todas las páginas, respeta el buscador), **omite** la columna de acciones (`th/td.col-actions` o `data-no-export` por columna) y aplica el **membrete institucional** (RIF vía `window.SIGTUR_RIF`). Excel = `.xls` HTML/Office (celdas como texto, preserva cédulas/códigos); PDF = documento limpio en un iframe oculto → diálogo de impresión (Guardar como PDF). **Opt-out del listado completo:** `data-no-export` en el contenedor (aplicado a `reportes/tabla` y `reportes/comision`, que ya traen exportación server-side con membrete vía `ReportesController::exportCsv`).

**Select con búsqueda (convención global, opt-in):** agregar la clase `js-search` a un `<select>` lo convierte en un **combobox con buscador** (`initSearchSelect` en `sigtur-validations.js`): un campo de texto filtra las opciones; al elegir una se fija el valor del select original (queda oculto pero se envía en el POST). Sin librerías externas (entorno sin internet). Conserva `required` vía el campo visible (`setCustomValidity`), integrándose con "botón deshabilitado hasta válido". Se auto-aplica en carga y `shown.bs.modal`; para selects inyectados por AJAX llamar `window.initSearchSelect(sel)`. Usado en Asistencia (elegir empleado); reutilizable en cualquier select largo.

**Botones de acción en tablas (convención global, automática):** usar la clase `.row-action` (+ variante `--edit` / `--del` / `--view`, o ninguna para neutro) con **un ícono Bootstrap** y el texto de la acción. `initRowActions()` (`sigtur-validations.js`) deja **solo el ícono** y mueve el texto a `title`/`aria-label` (tooltip), dando un patrón visual uniforme y moderno en todo el sistema (cuadrado 32px vía `.is-icon`). No hace falta escribir el markup icon-only a mano: poner ícono + texto y el helper lo colapsa. Se auto-aplica en carga y en `shown.bs.modal`; para filas inyectadas por AJAX, llamar `window.initRowActions()` tras insertarlas. Si un `.row-action` no tiene ícono, conserva su texto.

**Teléfonos (convención global, automática):** todo `<input name="telefono">` (o `type="tel"`/id con "telefono") se transforma en **[select de prefijo VE] + [campo de 7 dígitos]** vía `initTelefonoInput` (`sigtur-validations.js`). Prefijos: **solo móviles** `0412/0414/0416/0424/0426` (los fijos no se muestran; si un registro legado trae otro prefijo, se agrega como opción al editar). El input original se oculta y conserva el valor combinado (`0XXX`+7 = 11 dígitos) para el POST — **no requiere cambios en controladores/modelos**. Valida exactamente 7 dígitos (`setCustomValidity` + `required` movido al campo visible). Sincroniza con autocompletado por cédula (intercepta asignaciones a `.value` con un descriptor). Se auto-aplica en carga y en `shown.bs.modal`.

**Edad / fecha de nacimiento (convención global):** cualquier `<input type="date" class="js-edad">` muestra la edad calculada en vivo y valida el rango con `data-edad-min` / `data-edad-max` (años). Opcional `data-edad-target="idElemento"` para escribir la edad en un elemento existente (si no, crea un `<small>` debajo). Aplica restricciones nativas `min`/`max` al datepicker y `setCustomValidity`. El helper (`initEdadInput` + `sigturEdad`) vive en `sigtur-validations.js` y se auto-conecta en carga y en `shown.bs.modal`; para filas dinámicas, llamar `initSigturValidations()` tras insertarlas. El rango puede ajustarse en vivo: cambiar `data-edad-min/max` y disparar `input.dispatchEvent(new Event('edad:refresh'))`. Ejemplos: empleado `data-edad-min="18"` con `data-edad-max` **dinámico 65↔70** según comisión de servicio (`wzAjustarEdadMax()` en `form.php`; el servidor valida lo mismo en `EmpleadosController`: comisión 18–70, no comisión 18–65); participantes libres (niños) 5–11 en talleres/rutas. Carga familiar usa `js-edad` sin min/max (solo muestra edad).

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

### Idempotencia / anti doble-envío (B10 — global, automático)

Mecanismo de **token de un solo uso** para que un POST repetido del mismo cliente (doble clic, refrescar el POST, reintento de red) NO duplique registros:
- `sigtur_token_emitir()` / `sigtur_token_consumir()` en `app/helpers/session_helper.php` mantienen un pool de tokens por sesión (últimos 30, soporta varias pestañas).
- `footer.php` emite un token (`window.SIGTUR_TOKEN`), lo **inyecta automáticamente** como `<input name="_token">` en **todos los `form[method=post]`** (salvo `data-no-token`) y aplica un **guard de doble-envío** (deshabilita el submit tras enviar; opt-out `data-allow-multi-submit`).
- `Router.php` **consume y valida** el token en cada POST de usuario autenticado; si falta o se reutiliza → flash de "solicitud duplicada" + redirect, sin ejecutar el controlador.
- **Exentos:** `AuthController` (login, vista sin footer) y los endpoints AJAX de asistencia (`marcarAsistencia`/`marcarAsistenciaMasiva`, idempotentes por diseño). Los **deletes** van por enlace GET (soft-delete idempotente), no requieren token.
- No hay que tocar cada formulario/controlador: la protección es transversal. Para un POST que deba permitir reenvíos legítimos, marcar el form con `data-no-token` (servidor) y/o `data-allow-multi-submit` (cliente).
- **Anti-duplicado de contenido (empleados):** además del token, `Empleado::existeCedula($ced, $excluirId)` impide registrar dos veces la misma cédula como empleado (activo o egresado). `EmpleadosController::store()` normaliza la cédula a dígitos y bloquea con aviso (sugiere usar «Reingreso» si ya egresó). La cédula se compara solo por dígitos (`regexp_replace … '[^0-9]'`).

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

10. **`participantes_taller`** — `es_brigadista` **ELIMINADO** (mig.050, no se usaba). `nombre_docente`/`cedula_docente` para niños/as (libre, representante).

11. **`rutas.nivel_dificultad` ELIMINADO (migración 021)** — columna eliminada; ya no existe en BD ni en código.
11b. **Enums centralizados en constantes de modelo (H-07, 2026-05-31)** — los valores válidos de estado/tipo/condición viven en constantes PHP como fuente única: `Taller::ESTADOS/TIPOS_ACTIVIDAD/ESTADO_BADGES/TRANSICIONES`, `Ruta::ESTADOS/ESTADO_TERMINAL/ESTADO_BADGES`, `Inventario::CONDICIONES/CONDICION_DEFAULT/CONDICION_BADGES`. Los controllers usan estas constantes para whitelists; las vistas PHP para badges; el JS las recibe vía `json_encode()`. **No hardcodear** estos valores en controllers ni vistas.
11c. **`personas/visitantes.genero` CHECK = `IN ('M','F')` (migración 023)** — eliminada la opción 'O'. Aplica a 4 tablas: personas, visitantes, participantes_taller, participantes_ruta.

12. **`rutas.requiere_formacion`** — `TRUE` → el sistema verifica en `participantes_taller` que la persona asistió a al menos un taller antes de inscribir (RN-F12). Libres (niños) exentos.

13. **`talleres.id_oficio`** — FK nullable a `oficios`. Solo se asigna al crear actividad con sede externa y `es_interna = FALSE`.

14. **`configuracion_sistema`** — clave/valor para datos institucionales. `correlativo_oficio` se incrementa al generar oficio; `ano_correlativo` se reinicia automáticamente al cambiar de año.

15. **`AuditLog::log()`** — requiere `?array`. PDO retorna `stdClass`. El `Model::toArray()` hace el cast. **No pasar objetos directamente**.

16. **`AsistenciasController::marcar()`** — usa `$this->getUserId()` para registrar el usuario que marcó la asistencia. Bug corregido en fase 1.

17. **Máquina de estados de talleres (RN-F13)** — `TalleresController::validarTransicion()`. Terminales: Finalizado, Cancelado. No se puede Finalizar sin participantes.

18. **`empleados` modelo de contrato (migración 025)** — `tipo_contrato` = estabilidad: solo `'Fijo'`/`'Contratado'`, DEFAULT `'Contratado'` (todo nuevo es Contratado). `'Suplente'` y `'Comisión de Servicio'` **deprecados** (ya no son valores válidos). El origen se modela aparte: `institucion_origen` ∈ `'Alcaldía'`/`'Gobernación'`/`'IMATUR'` (DEFAULT 'IMATUR'). **`es_comision_servicio` se DERIVA del origen** (= origen ≠ IMATUR): comisión de servicio ⟺ viene de Alcaldía/Gobernación; no es checkbox manual (el asistente lo muestra como indicador). Tope de edad: IMATUR 18–65, comisión 18–70. Enums centralizados en `Empleado::TIPOS_CONTRATO` / `Empleado::INSTITUCIONES_ORIGEN` (patrón H-07). Ver `docs/MODELO_NEGOCIO_RRHH.md` 2.2 (D-RH27). **Consulta por comisión:** el listado `/empleados?origen=comision|IMATUR|Alcaldía|Gobernación` filtra por origen (`Empleado::all($origen)`/`egresados($origen)`, helper `filtroOrigen`; `comision` = origen ≠ IMATUR) y muestra columna "Origen"; además reporte `reportes/comisionServicio` (+`exportarComisionCsv`, roles 1/2) lista el personal en comisión agrupado por institución con tiempo de servicio.

18l. **Cédula solo dígitos + anti-duplicado de participantes (migración 037)** — La cédula se guarda y valida **solo con números, máx. 8** (regla global en `sigtur-validations.js`; excepción: campos `*_libre` = ID escolar/extranjeros, alfanuméricos). `TalleresController`/`RutasController` normalizan la cédula a dígitos antes de buscar/crear en `personas` (evita personas duplicadas por formato). **Anti-duplicado en la misma actividad:** personas (con cédula) ya estaban cubiertas (`Taller::estaInscrito`, check en `Ruta::inscribir`); para participantes **sin cédula (libre)** se agregó `Taller::estaInscritoLibre()`/`Ruta::estaInscritoLibre()` (mismo nombre+apellido+fecha nac, o misma `cedula_libre`) → bloquea registrar dos veces al mismo niño/a. **Control de registros basura:** reporte `reportes/duplicados` (`ReportesController::duplicados()`, roles 1/3) que agrupa posibles duplicados: personas con cédula repetida, personas con mismo nombre+apellido+fnac, y participantes libre repetidos entre talleres y rutas. Los participantes sin cédula NO tienen clave única → el sistema solo señala coincidencias para revisión humana (desambiguar con representante/docente, parroquia, género).

18m. **Ancla por representante para menores sin cédula (migración 038)** — Decisión de negocio: un niño/a sin cédula se identifica por su **representante** (adulto con cédula = identificador estable). En el flujo libre, el **representante (nombre + cédula) es OBLIGATORIO**: talleres lo guarda en `nombre_docente`/`cedula_docente` (relabeled "Representante / Docente"), rutas en `nombre_representante`/`cedula_representante` (mig.038). La cédula del representante se normaliza a dígitos (6–8) en `TalleresController::store()/actualizarParticipante()` y `RutasController::inscribir()`. El reporte `reportes/duplicados` agrupa los libre por nombre+apellido+fnac **+ cédula del representante**: así dos homónimos con representantes distintos no se marcan como duplicados, y la misma persona (mismo representante) en varias actividades sí se detecta. El bloqueo dentro de la misma actividad sigue por nombre+apellido+fnac / `cedula_libre` (`estaInscritoLibre`).

18k. **Egreso / desincorporación de empleados (migración 036, R-12)** — dar de baja a un trabajador (renuncia, despido, jubilación, fin de contrato, fallecimiento, otro) **NO borra** el registro: lo marca como egresado (`empleados.fecha_egreso` + `motivo_egreso` + `observacion_egreso`), manteniéndolo `is_active=TRUE` como **histórico consultable** (sale de la nómina activa pero sigue disponible para constancias y tiempo de servicio). `is_active=FALSE` (`delete()`) queda reservado para registros creados por error (papelera). `Empleado::all()`/`facilitadoresTalleres()` filtran `fecha_egreso IS NULL`; `Empleado::egresados()` lista el histórico; `procesarEgreso()`/`reingresar()` (transaccionales + auditados) usan la tabla `empleados_egresos` (historial; índice único parcial `uq_emp_egreso_abierto` impide dos egresos abiertos). **Reingreso con historial**: al reingresar se cierra la fila (`fecha_reingreso`) y se limpia el egreso vigente. `Empleado::tiempoServicio($ingreso,$egreso)` → "X años, Y meses" (hasta egreso o hasta hoy), embebido en la **constancia** (redacción en pasado si egresado). Enum `Empleado::MOTIVOS_EGRESO`. UI: pestañas Activos/Egresados en `empleados/index` (`?ver=egresados`), modal "Procesar egreso"/"Reingreso" en index y expediente, banner + tiempo de servicio + historial en `empleados/detalle`. Controlador: `egresar()`/`reingresar()` (POST, validan fecha ≥ ingreso y no futura).

18j. **Constancias de trabajo (migración 034, R-10)** — dentro del módulo Empleados. **Multi-tipo (B13):** `Constancia::TIPOS` (clave→etiqueta) = `trabajo`/`bancaria`(sin monto, espacio en blanco)/`horario`/`funciones`/`antiguedad`/`egreso`; la **clave** se guarda en `constancias.tipo` y `Constancia::labelTipo()` la traduce. `EmpleadosController::generarConstancia($id, $tipo='trabajo')` valida el tipo; la vista imprimible `constancia.php` adapta título y cuerpo por tipo (horario usa `horarios`+grupo; funciones usa cargo+nivel_jerárquico; egreso usa motivo/fechas). El expediente ofrece un **dropdown** de tipos (egreso solo si egresado; bancaria/horario solo si activo) y muestra un **badge de estatus** (Activo/Egresado·motivo/En permiso·tipo/En reposo·tipo, vía `PermisoLaboral::vigenteHoy()`) + tiempo de servicio. **No exige antigüedad mínima.** `Constancia::crear($idEmpleado, $tipo)` genera correlativo `CONST-` + `ConfigSistema::generarNumeroOficio('constancia')` → `CONST-NNN/AAAA` (claves `correlativo_oficio_constancia`/`ano_correlativo_constancia` sembradas en 034). `EmpleadosController::generarConstancia($id)` (crea + redirige a imprimible), `constancia($idConst)` (vista imprimible `empleados/constancia.php`, carta institucional con firmante de ConfigSistema), `eliminarConstancia()`. Historial en la sección "Constancias / Documentos generados" del expediente. RIF en la constancia = G-20008498-7 (igual que la ficha; difiere de carta_aceptacion — unificar vía ConfigSistema).

18i. **Recaudos del expediente (migración 033, R-5)** — dentro del módulo Empleados (sin RBAC nuevo). `ExpedienteDocumento::RECAUDOS` = catálogo (clave→[etiqueta, obligatorio]); `recaudosEstado($id)` arma el checklist y cuenta faltantes obligatorios. Subida en `EmpleadosController::subirDocumento()` (valida PDF/JPG/PNG ≤5MB; nombre `Tipo_Empleado_{id}_{ts}.ext`; guarda en `public/uploads/expedientes/`; URL relativa `/uploads/expedientes/...`). Sección "Recaudos del Expediente" en `empleados/detalle.php` (estado entregado/falta, descarga, eliminar, aviso de faltantes). La Ficha Técnica generada (R-2) es un recaudo más del catálogo.

18h. **Permisos y reposos (migración 032, R-8)** — `PermisosController` (rol 2 + sidebar RRHH) sobre `permisos_laborales`. `PermisoLaboral::CATEGORIAS` (Reposo/Permiso) + `TIPOS` (cascada categoría→tipo en la UI) + `ESTADOS` (Pendiente/Aprobado/Rechazado/Anulado). Reposo y Permiso se distinguen por `categoria` (select, D-RH32). El estatus **En curso/Concluido** se DERIVA de `fecha_fin` vs hoy (no se almacena); `dias_solicitados` se calcula del rango; `duracion` es texto libre ("72 horas"/"6 meses"). Flujo: registrar (Pendiente) → aprobar/rechazar/anular. **Vacaciones NO incluido** (fórmula pendiente — D-RH04/05/NEW05). `tipo_permiso` CHECK = Reposo médico/Médico familiar/Diligencia/Duelo/Maternidad-Paternidad/Personal/Estudios/Otro.

18g. **Faltas y amonestaciones (migración 031, R-9)** — `AmonestacionesController` (rol 2 + sidebar RRHH): roster de empleados con conteo de `faltas` y `amonestaciones` activas + semáforo (`Amonestacion::roster()`), y detalle por empleado (`empleado($id)`). RRHH registra ambas manualmente (el sistema solo cuenta/notifica, D-RH28). `Amonestacion::LIMITE_DESPIDO = 3` → a las 3 amonestaciones activas se muestra "Causa de despido" (aplica a Contratado). Las `faltas` injustificadas son distintas de los permisos/ausencias justificadas (R-8, pendiente). Modelos `Falta`/`Amonestacion` (porEmpleado/save/delete, auditados).

18f. **Registro de empleado = asistente multi-paso (migración 030, R-2b)** — el alta/edición de empleado NO usa modal: es un wizard de página completa `empleados/form.php` (5 pasos: personales → formación → institucionales → carga familiar → resumen), servido por `EmpleadosController::nuevo()` y `editar($id)`, posteado a `store()`. Persiste el borrador en `localStorage` (solo alta), valida por paso, y muestra resumen antes de guardar. La carga familiar se recolecta en arrays `cf_nombre[]/cf_cedula[]/cf_fnac[]/cf_parentesco[]` e inserta tras crear la persona (`guardarCargaFamiliarInicial()`); en edición enlaza al expediente. Campos nuevos: `personas.centro_votacion/consejo_comunal/comuna`, `empleados.uniforme/talla_camisa/talla_pantalon/talla_zapato` (uniforme solo se registra, D-RH35). `Empleado::getId()/getIdPersona()` exponen los IDs tras `save()`.

18e. **Asistencia: puntualidad y ausentismo (migración 029)** — al marcar entrada, `AsistenciasController::marcar()` calcula `asistencias.minutos_tarde` vía `Asistencia::calcularMinutosTarde()` (hora real − hora del horario asignado); impuntual si `minutos_tarde > minutos_tolerancia_puntualidad` (config, default 15, editable en `/config`). `Asistencia::empleadosEnActividad($fecha)` detecta empleados en ruta (`rutas.fecha_visita` + `participantes_ruta`) o formación externa (`talleres.es_interna=FALSE` + rango fechas + `participantes_taller`) por `id_persona` → no cuentan como ausentes (RN-RH15). El index muestra resumen del día (activos/presentes/impuntuales/en actividad/ausentes) + horas trabajadas (derivadas, solo reporte; NO afectan pago). Sin horario asignado → `minutos_tarde` NULL ("sin horario").

18d. **Horarios y grupos (migración 028)** — `horarios` tiene CRUD (`HorariosController` + modelo `Horario` + `horarios/index.php`), accesible RRHH/Admin (sidebar bajo RRHH). Seed de modalidades: Estándar 08–14, OAC Matutino 07–12, OAC Vespertino 10–14, Servicios Generales 08–14 (rotación A/B). `empleados.grupo_rotacion` (A/B, `Empleado::GRUPOS_ROTACION`) solo para Servicios Generales. Config `minutos_tolerancia_puntualidad` (default 15) preparada para R-7 (puntualidad). `EmpleadosController` usa `Horario::all()` (ya no query inline).

18c. **`departamentos` jerárquico (migración 027)** — `id_padre` (auto-FK, ON DELETE SET NULL) + `tipo_unidad` ∈ Presidencia/Junta Directiva/Dirección/Coordinación/Oficina/Unidad. Estructura oficial sembrada (Presidencia → 3 Direcciones [Planificación y Gestión Turística, Administración, Talento Humano] → Coordinaciones + unidades staff). Enum en `Departamento::TIPOS_UNIDAD`; `Departamento::all()/find()` traen `padre` (nombre) y ordenan por jerarquía. El liderazgo Director/Coordinador se **deriva del cargo** del empleado (cargos `Director`/`Coordinador`/`Presidenta`), no hay campo responsable. Ver `MODELO_NEGOCIO_RRHH.md` 7.1 (D-RH30). **Listado jerárquico (U1):** `Departamento::arbol()` devuelve el recorrido en profundidad (cada unidad seguida de sus subunidades, mayor→menor nivel) con `->nivel` para indentar; lo usa `departamentos/index` (DFS, ordena por `ORDEN_TIPO`+nombre, huérfanos como raíces). Cargos se indenta visualmente por `Cargo::ORDEN_NIVEL` (escalera Presidencia→Adscrito). `Departamento::all()` (plano, ordenado) sigue para los selects de otros módulos.

18b. **Ficha Técnica del Trabajador (migración 026)** — `Empleado::find()` trae nombres por LEFT JOIN (cargo, departamento, parroquia, horario) y `Empleado::all()` incluye los campos extra de `personas` para el modal de edición. Enums `Empleado::CLASIFICACIONES` (Empleado/Obrero), `ESTADOS_CIVILES`, `NIVELES_ACADEMICOS`. Tablas hijas claveadas por `id_persona` con modelos `CargaFamiliar`/`CursoRealizado`/`ExperienciaLaboral` (métodos `porPersona/save/delete`, auditados). Expediente en `EmpleadosController::detalle($id)`; documento imprimible en `fichaTecnica($id)` → `empleados/ficha_tecnica.php`. **RIF institucional en la ficha = G-20008498-7** (según el formato físico; difiere del usado en `pasantes/carta_aceptacion.php` G-20009499-7 — discrepancia a unificar, idealmente vía `ConfigSistema`).

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

# 3. Importar el esquema consolidado (schema base + migraciones 001-023 + seeds):
PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/schema_consolidado.sql

# 4. Aplicar las migraciones posteriores al consolidado (024 a 039):
PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/024_pasantes_carta_aceptacion.sql
PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/025_empleados_contrato_origen.sql
PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/026_empleado_ficha_tecnica.sql
PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/027_departamentos_jerarquia.sql
PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/028_horarios_grupos.sql
PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/029_asistencia_puntualidad.sql
PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/030_uniforme_comunitarios.sql
PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/031_faltas_amonestaciones.sql
PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/032_permisos_reposos.sql
PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/033_expediente_documentos.sql
PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/034_constancias.sql
PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/035_cargos_jerarquia.sql
PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/036_egreso_empleados.sql
PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/037_normalizar_cedulas.sql
PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/038_representante_participante_ruta.sql
PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/039_carga_familiar_genero_estado.sql
PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/040_nro_expediente_auto.sql
PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/041_fecha_vencimiento_contrato.sql
PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/042_motivo_anulacion_disciplina.sql

# 5. Verificar config/config.php:
#    DB_HOST=localhost | DB_PORT=5432 | DB_NAME=SIGTUR-IMATUR
#    DB_USER=postgres  | DB_PASS=1234 (entorno Laragon)

# 6. URL: http://SIGTUR-IMATUR.test  o  http://localhost/SIGTUR-IMATUR/public
```

> **Nota:** `database/schema_consolidado.sql` cubre schema base + migraciones 001-023 + seeds de sistema. Para una instalación completa, aplicar después las migraciones **024 a 039** desde `database/migrations/` (idempotentes). (`schema_completo.sql` queda obsoleto — solo cubría hasta la 011.)

---

## Documentación de reglas de negocio

| Archivo | Módulo |
|---------|--------|
| `docs/REGLAS_NEGOCIO_Formacion.md` | Talleres, Charlas, Inducciones |
| `docs/REGLAS_NEGOCIO_Rutas.md` | Rutas Turísticas |
| `docs/REGLAS_NEGOCIO_Pasantes.md` | Pasantes |
| `docs/REGLAS_NEGOCIO_RRHH.md` | Empleados, Asistencias, Permisos, Vacaciones (estado técnico + brechas) |
| `docs/MODELO_NEGOCIO_RRHH.md` | **Modelo de negocio RRHH** consolidado: horarios/grupos, tipos de empleado, expediente, carga familiar, permisos/reposos/vacaciones, organigrama, hoja de ruta R-1…R-11 |
| `docs/REGLAS_NEGOCIO_Inventario.md` | Bienes e Inventario |
| `docs/REGLAS_NEGOCIO_Visitantes.md` | Visitantes y Control de Visitas |
| `docs/ESTRUCTURA_ORGANIZATIVA.md` | Organigrama y análisis de jerarquía |
| **`docs/BACKLOG.md`** | **BACKLOG ÚNICO** — qué falta por hacer y decidir: estado por módulo, decisiones/insumos del cliente, preguntas abiertas, auditoría H-xx abierta, programación faltante. Consolida (y reemplaza) los antiguos REGISTRO_NEGOCIO/DECISIONES_PENDIENTES/preguntas/AUDITORIA_SENIOR/Notas/PLAN_ENTREGA |
| `docs/INDICADORES_GESTION.md` | **Todos los indicadores de gestión**: propósito, fórmula y fuente de datos (Dashboard + página RF30 + stats por reporte) |

> **Nota (2026-06-21):** migraciones aplicadas hasta la **050**. Para qué falta y decisiones pendientes, ver siempre `docs/BACKLOG.md` (fuente única de seguimiento).

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
| Schema consolidado (instalar desde cero) | `database/schema_consolidado.sql` (001-023 + seeds; aplicar 024-050 encima) |
| Backlog único / pendientes / decisiones | `docs/BACKLOG.md` |
| Schema base original (historial) | `database/schema.sql` |
