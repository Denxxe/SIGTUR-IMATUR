# CLAUDE.md — SIGTUR-IMATUR
**Última actualización:** 2026-08-27 (c) — **Nómina: motor de cálculo construido (mig. 072, fases N-A y N-B del plan).** Las primas ya NO se capturan: se derivan de sueldo base, grado de instrucción, años en la administración pública y nº de hijos. Modelo `Nomina` con `calcular()` **pura** (sin BD), 45 casos de prueba contra los valores de la plantilla real — incluido el que destapa el defecto #1 del cliente (prima de antigüedad del tramo ≥23 años: 56,40 correctos vs. 112,80 que paga su hoja). Porcentajes en tablas (`nomina_grados`, `nomina_antiguedad`), cesta ticket y tasa del dólar **por mes con vigencia**, quinto tipo de personal (Comisión de Servicio), nómina quincenal con períodos/snapshot/recálculo/cierre y **export de 6 hojas**. Un valor de grado no reconocido **se reporta**, no se paga como 0 % en silencio (defecto #7 del cliente). **Leer `docs/PLAN_MODULO_NOMINA.md` antes de tocar el módulo.**

**Anterior:** 2026-08-27 (b) — **Cierre de los 4 defectos abiertos + feriados movibles (mig. 070-071).** (1) Las **evidencias de talleres** eran el último archivo de usuario en `public/uploads/`: legibles por URL sin control de rol y con el enlace roto bajo el vhost donde `public/` es la raíz. Ahora van a `storage/uploads/talleres/` servidas por `DescargaController::taller()`, con el bloque de subida unificado en `TalleresController::procesarEvidencias()` (antes duplicado y sin validar MIME real ni tamaño). **`public/uploads/` ya no existe — no reintroducirlo.** (2) **H-14**: se retiró la columna Tarifa del reporte de rutas (informaba «Gratuita» siempre). (3) **H-13**: `DROP TABLE actividades_ruta` (mig. 070), 56 → 55 tablas. (4) **Feriados movibles** de Carnaval y Semana Santa 2026-2028 (mig. 071): faltaban por completo y el conteo de vacaciones descontaba 4 días hábiles de más al año.

**Anterior:** 2026-08-27 — **El menú lateral pasa a leer el RBAC real (cierra H-12) + semilla de ubicaciones (mig. 069).** El sidebar tenía los permisos cableados por número de rol en 8 bloques de `header.php` mientras el Router los resolvía desde `permisos_rol`; ahora se genera con `RolesController::getNavegacion()`/`getNavegacionVisible()` filtrando por `roleHasModulo()` — **ver la sección «Sidebar» del RBAC antes de tocar el menú**. La mig. 069 siembra `ubicaciones` (una por departamento + Depósito General, con su sede): sin esas filas era **imposible registrar un bien**, así que las fases 1-4 del módulo (mig. 062-067) estaban inalcanzables. De paso se completó un hueco de la Fase 1: `ubicaciones.sede`/`es_deposito` se leían en todo el módulo pero no se escribían en ninguna parte.

**Anterior:** 2026-08-04 — **Módulo de Bienes: Fases 1, 2 y 4 completas, Fase 3 parcial** (mig. 062-065): `estatus` separado de `condicion` (cierra H-04), código oficial por partes con flujo de codificación contra el BM-1, datos de adquisición, responsable único, sedes y 11 categorías internas. **Fase 2**: movimientos con origen/destino y autorización por cargo+departamento, mantenimiento con salida/retorno registrado (`inventario_mantenimientos`), todo transaccional. **Fase 3**: expediente documental por bien, recepción del BM-1 y hoja de vida; falta la generación de documentos (bloqueada por los formatos del cliente). **Fase 4**: etiquetas con QR, reportes filtrables para la Presidencia, alertas de garantía/preventivo/sin codificar, mantenimiento preventivo programado, conteo por cambio de gestión con acta, y **lectura/escritura por rol** (`InventarioController::puedeEscribir()`, B-58). **Lo único que falta son 3 documentos bloqueados por los formatos del cliente: `docs/PLAN_MODULO_BIENES.md` §12.** Antes: **Módulo de Bienes en replanteamiento**: el levantamiento con el cliente (59 preguntas respondidas) mostró que lo construido es un CRUD genérico y lo que hace falta es un expediente administrativo por bien; plan por fases en `docs/PLAN_MODULO_BIENES.md`. Antes en la misma fecha: carnet rediseñado según el modelo físico (mig. 061), limpieza de columnas inertes (mig. 060) y **`database/schema_consolidado.sql` regenerado y ahora es autosuficiente (001–068)**: instalar desde cero = importar ese único archivo, sin migraciones encima. Incluye catálogos institucionales sembrados y un administrador de arranque (`admin`/`Sigtur2026`, cambiar al primer ingreso). Verificado cargándolo en una BD vacía: 49 tablas, 0 errores. Antes, el consolidado cubría solo hasta la 023 y el README mandaba aplicar 024–052, así que **toda instalación nueva quedaba sin las migraciones 053–059** (carnet, auditoría de login, alertas vistas, recuperación de contraseña, nómina).

**Anterior:** 2026-07-16 (migración 059; **Nómina — Bono Vacacional v1** "registro + reporte": historial salarial por empleado (`empleado_salarios`), módulo `/nomina` (períodos + captura/edición + cierre), escritor OOXML multi-hoja reusable `XlsxMultiSheet` para exportar en el formato exacto que exige la Alcaldía; días base por tipo de personal configurables. Liquidación de Prestaciones Sociales queda para una 2da entrega — ver `docs/BACKLOG.md` §3.1 — suite `php tests/run.php` 18/18 ✓)  
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
| **RRHH** | Empleados, Cargos, Departamentos, Asistencias, Vacaciones, **Nomina** | personas, empleados, cargos, departamentos, asistencias, horarios, permisos_laborales, vacaciones, empleado_salarios, bono_vacacional_periodos/detalle, **nomina_grados, nomina_antiguedad, nomina_parametros_mes, nomina_periodos, nomina_detalle** |
| **Inventario** | Inventario, Categorias, Ubicaciones, ActividadesInventario | inventario, categorias, ubicaciones, actividad_inventario |
| **Formación** | Talleres, UbicacionesFormacion, Pasantes | talleres, ubicaciones_formacion, pasantes, pasante_documentos, taller_informes, taller_inventario, participantes_taller |
| **Turismo** | Rutas, Visitantes, Visitas | rutas, puntos_ruta, participantes_ruta, ruta_informes, oficios_emitidos, visitantes, visitas |
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
- **Excepción:** la Bitácora general (`AuditoriaController::index`) NO está en `RolesController::getModulos()` — es exclusiva del Administrador por `guardAdmin()` (rol=1 hardcodeado), no delegable desde la UI (mig. 055). La Papelera de Reciclaje (`AuditoriaPapelera`) sí sigue siendo delegable por módulo operativo.
- **Cuidado con `getModulos()`:** `RolesController::storePermisos()` borra y reinserta TODOS los permisos del rol filtrados contra `array_keys(getModulos())`. Cualquier módulo con fila en `permisos_rol` (por seed de migración) pero ausente de `getModulos()` se pierde silenciosamente en el próximo guardado — pasó con Horarios/Permisos/Vacaciones/Amonestaciones/Visitas hasta la mig. 055. Todo módulo operativo nuevo con RBAC por rol **debe** agregarse a `getModulos()`.

| Rol ID | Nombre | Controladores permitidos (seed 008) |
|--------|--------|--------------------------------------|
| 1 | Administrador | `'*'` — acceso total sin restricción |
| 2 | RRHH | Dashboard, Empleados, Cargos, Departamentos, Horarios, Amonestaciones, Permisos, Asistencias, Visitantes, Visitas, Reportes, Config |
| 3 | Turismo | Dashboard, Rutas, Talleres, UbicacionesFormacion, Pasantes, Visitantes, Visitas, Reportes |
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

### Sidebar (header.php) — generado desde el RBAC, no cableado (H-12, 2026-08-27)

El menú **no** tiene condiciones por número de rol. `header.php` recorre
`RolesController::getNavegacionVisible()`, que filtra `getNavegacion()` con `roleHasModulo()` — el
**mismo mapa** (`permisos_rol`) que aplica el Router. Consecuencias prácticas:

- **Para agregar o mover un módulo del menú, editar `RolesController::getNavegacion()`** (token de
  permiso → `url`, `label`, `icon`, `grupo`). No tocar la vista. El orden del array es el orden de
  aparición y los grupos se dibujan según su primera aparición; los grupos sin ítems no se dibujan.
- **Nunca escribir `in_array($rol, [...])` en el sidebar.** Antes había 8 bloques así y contradecían
  al RBAC en ambos sentidos: roles con permiso que no veían el enlace (rol 2 → Pasantes/Usuarios;
  rol 6 → Visitas) y un enlace visible para todos que llevaba a *Acceso Denegado* (rol 5 → Reportes).
- Lo **no delegable** se marca con `'soloAdmin' => true` en la misma definición, porque no vive en
  `permisos_rol`: Bitácora (`AuditoriaController::guardAdmin`, mig. 055), Municipios y Parroquias
  (fuera de `getModulos()`). `AuditoriaPapelera` **sí** es delegable y se resuelve normal.
- `DashboardController` se pinta siempre, arriba y sin etiqueta de grupo (todo rol lo tiene:
  `storePermisos()` lo agrega de oficio). `VisitasController` se excluye del menú a propósito — es
  acceso directo desde Visitantes.
- Los guards **por método** son otra capa y siguen siendo válidos: el enlace «Ver centro de alertas»
  de la campana usa `in_array($rol,[1,2])` porque replica el `requireRoles([1,2])` de
  `ReportesController::alertas()`, no un permiso de módulo.

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
| `permisos_laborales` *(002; UI + categoría/duración en 032)* | Permisos y reposos: `categoria` (Reposo/Permiso), `tipo_permiso` (taxonomía), fechas, `estado` aprobación. **Duración** se autocalcula en días al elegir Desde/Hasta (JS en `permisos/index.php`); sigue siendo editable a mano (ej. "72 horas") y no se recalcula si el usuario la corrigió |
| `vacaciones` *(002, sin UI)* | Control anual de días |
| `carga_familiar` *(026)* | Familiares del empleado (FK `id_persona`); bloque de la Ficha Técnica |
| `cursos_realizados` *(026)* | Cursos por persona (FK `id_persona`); bloque de la Ficha Técnica |
| `experiencia_laboral` *(026)* | Trabajos anteriores (FK `id_persona`); bloque de la Ficha Técnica |
| `expediente_documentos` *(033)* | Recaudos subidos del expediente (FK `id_empleado`); checklist + faltantes |
| `faltas` *(031)* | Faltas injustificadas por empleado (RRHH); el sistema las cuenta |
| `amonestaciones` *(031)* | Amonestaciones por empleado (RRHH); 3 activas = causa de despido |
| `constancias` *(034)* | Historial de constancias de trabajo emitidas (FK `id_empleado`); correlativo CONST-NNN/AAAA |
| `empleado_salarios` *(059)* | Historial salarial append-only por empleado (sueldo básico + primas); el vigente es la fila con `fecha_efectiva` más reciente |
| `bono_vacacional_periodos` / `bono_vacacional_detalle` *(059)* | Corridas mensuales de Bono Vacacional (snapshot por empleado, agrupado por tipo de personal) — módulo `/nomina`, v1 "registro + reporte" |

Nota: `horarios`, `permisos_laborales`, `vacaciones` existen desde migración 002. Sin UI. Pendiente respuestas D-RH01–D-RH11. Las tablas hijas de la Ficha Técnica (`carga_familiar`/`cursos_realizados`/`experiencia_laboral`) ya tienen UI en el expediente del empleado (`/empleados/detalle/{id}`).

#### Inventario
| Tabla | Descripción |
|-------|-------------|
| `categorias` | Clasificación de bienes |
| `ubicaciones` | Oficinas/almacenes; FK `"departamento _d"` (columna con espacio); `sede` (enum `Ubicacion::SEDES`) y `es_deposito`. **Sembrada en la mig. 069**: una por departamento + Depósito General — sin filas aquí es imposible registrar un bien |
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

#### Turismo
| Tabla | Descripción |
|-------|-------------|
| `rutas` | Itinerarios; `requiere_formacion BOOL` *(006)*; `tiene_tarifa BOOL`, `tarifa_monto DECIMAL` *(007)* — ⚠️ **nunca se escriben desde la UI** y desde el 2026-08-27 **tampoco se leen**: se retiraron del reporte de rutas porque informaban «Gratuita» para toda ruta, siempre (H-14). Las columnas se conservan a la espera de D-RT02; si el cliente descarta el cobro, se eliminan. **No volver a mostrarlas sin implementar la captura.** (`nivel_dificultad` eliminado en 021; `nombre_facilitador_externo` en **060**) |
| `puntos_ruta` | Paradas con lat/lon y orden |
| `participantes_ruta` | Inscripción a rutas; modo libre para niños/as *(005)*; representante del menor *(038)*. (`id_institucion` eliminado en **060**) |
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
| 051 | `051_usuarios_seguridad_login.sql` | ✅ Ejecutado | Endurecimiento del login: `usuarios` +`failed_attempts`/`locked_until`/`last_login`. Bloqueo temporal tras `Usuario::MAX_INTENTOS`(5) intentos por `BLOQUEO_MINUTOS`(15); `registrarLoginFallido/Exitoso`, `bloqueoRestante`, `passwordPolicyError`(min 8 + letra y número). AuthController usa mensaje genérico (anti-enumeración) + `session_regenerate_id`. Router expira sesión por inactividad (`SESSION_TIMEOUT`=1800s en config) |
| 052 | `052_indices_rendimiento.sql` | ✅ Ejecutado | Índices en tablas que crecen: `participantes_ruta(id_ruta)`, `actividad_inventario(id_inventario,fecha desc)`, `personas(parroquia_id)`, `parroquia(id_municipio)`, `audit_logs(tabla_afectada,operacion)`. Idempotente (IF NOT EXISTS); no duplica los ya existentes |
| 053 | `053_foto_persona.sql` | ✅ Ejecutado | **Carnetización**: `personas.foto_url` (solo nombre de archivo; binario en `storage/uploads/fotos/`). Una foto por persona, reutilizada por carnet de empleado y de pasante. Idempotente (ADD COLUMN IF NOT EXISTS) |
| 058 | `058_password_resets.sql` | ✅ Ejecutado | **Recuperación de contraseña por correo (autoservicio)**: tabla `password_resets` (token de un solo uso, solo se guarda `sha256(token)`, expira en 30 min, cooldown 60s anti-abuso). `AuthController::olvidoPassword/enviarRecuperacion/resetPassword/procesarReset` + modelo `PasswordReset`. `Usuario::findByUsernameOrEmail()` (anti-enumeración: null si no existe O si el correo es ambiguo entre 2+ cuentas) y `Usuario::actualizarPassword()` (solo password, no pisa username/id_rol como haría `save()`). Envío vía `sigtur_enviar_correo()` (`app/helpers/mail_helper.php`) usando PHPMailer vendoreado a mano (sin Composer) en `app/libs/PHPMailer/` — falla de forma controlada (log, sin romper la request) si `SMTP_HOST/USER/PASS` en `config/config.php` siguen con el placeholder `CAMBIAR_...`. El reset manual por Administrador (Sistema → Usuarios) se mantiene para cuentas sin correo registrado. Probado end-to-end contra la BD real (token→reset→login funcionando; SMTP falla controladamente sin credenciales reales). **Auditoría 2026-07-12**: se confirmó que el bug de cédula sin normalizar (que rompió Talleres/Rutas, fix en commit 996167b) tenía el MISMO defecto sin corregir en `Visitante::buscarPorCedula/crear/store` y `Pasante::findPersonaByCedula/createPersona/updatePersona` — ya corregido (mismo patrón `preg_replace('/\D/','',...)`). Probado en vivo: buscar con formato "V-XX.XXX.XXX" ahora encuentra a la persona en ambos módulos. Convención confirmada: TODA búsqueda/guardado por cédula debe normalizar a solo-dígitos (las cédulas se guardan así desde mig.037). Se descartaron como riesgo real: el patrón de JS `getElementById().addEventListener` sin guarda en 6 vistas más (todas seguras por orden del DOM) y la consistencia de `permisos_rol`/`getModulos()` (sin discrepancias, mig.055 cerrada). También corregidos: `CargaFamiliar::save()` normaliza cédula + `existeCedulaEnPersona()` (anti-duplicado **por empleado**, NO global — la misma cédula de familiar SÍ puede repetirse legítimamente entre empleados distintos, ej. hermanos que declaran al mismo padre); `BuscarController` (búsqueda global) ahora compara también la versión solo-dígitos del término contra cédula, sin afectar la búsqueda por nombre. **Ajuste 2026-07-12 (b)**: `AuthController::login()` ahora acepta usuario **o correo** (`Usuario::findByUsernameOrEmail()`, ya existente) — resuelve el caso de "olvidé mi usuario": si recuerda su correo, no necesita el username para nada. **Ajuste 2026-07-12 (c)**: el egreso de un empleado (`Empleado::procesarEgreso()`) ahora desactiva automáticamente su cuenta de acceso (`usuarios.is_active=FALSE`) si tiene una — antes quedaba huérfana y activa indefinidamente (brecha de seguridad real, confirmada en el código). El reingreso (`Empleado::reingresar()`) la reactiva automáticamente (decisión del cliente) y limpia intentos fallidos/bloqueo. Ambos casos auditados en la tabla `usuarios` además del log de `empleados`. Probado con un usuario de prueba desechable (no se tocó el único usuario real de este entorno). **Ajuste 2026-07-12**: `Empleado::existeCorreo()` (mismo patrón que `existeCedula()`) bloquea que dos empleados activos compartan correo — un correo repetido rompía silenciosamente `findByUsernameOrEmail()` (lo trata como "no encontrado", anti-enumeración). `sigtur_enviar_correo()` ahora usa `ConfigSistema::get('correo_institucion')` (el mismo correo institucional ya usado en oficios/constancias, hoy `imatur.cumana@gmail.com`) como remitente real; `SMTP_FROM_EMAIL` en config.php queda solo de respaldo si esa clave está vacía |
| 057 | `057_alertas_vistas.sql` | ✅ Ejecutado | Tabla `alertas_vistas` (id_usuario, clave_alerta, fingerprint, visto_at — UNIQUE por usuario+clave). La campana (`CentroAlertas::resumenPersonal()`) oculta, por usuario, las alertas ya vistas mientras su conjunto de IDs no cambie (fingerprint MD5); si cambia (ej. nuevo contrato entra en ventana de aviso), reaparece. `DashboardController::marcarAlertasVistas()` (AJAX, exento de token CSRF en Router igual que marcarAsistencia) marca al abrir el dropdown (`shown.bs.dropdown` en footer.php). `reportes/alertas` sigue usando `resumenCacheado()` sin filtrar (vista completa, no personalizada) |
| 056 | `056_tolerancia_salida_temprana.sql` | ✅ Ejecutado | Config `minutos_tolerancia_salida_temprana` (default 10). Al marcar **salida** antes de la `hora_salida` del horario del empleado más allá de esta tolerancia, `AsistenciasController::marcar()` exige un motivo (`motivo_temprano`), validado también server-side; `estadoMarcaje()` (AJAX) avisa al front para mostrar el campo. Editable en `/config` junto a la tolerancia de puntualidad (son independientes) |
| 054 | `054_audit_logs_login.sql` | ✅ Ejecutado (2026-07-12) | Fix: `audit_logs_operacion_check` no incluía `LOGIN`/`LOGIN_FALLIDO` → todo intento de acceso (exitoso o fallido) fallaba el INSERT en `audit_logs` y el error se descartaba en silencio (`catch (Exception $ignored) {}` en `AuthController`). Por eso `/reportes/accesos` (bitácora de IP) siempre aparecía vacío. Se amplía el CHECK; `AuthController` ahora deja rastro en `error_log` si un futuro INSERT de auditoría falla |
| 055 | `055_bitacora_solo_admin.sql` | ✅ Ejecutado (2026-07-12) | Bitácora general (`/auditoria/index`) pasa a ser **exclusiva del Administrador** (`AuditoriaController::guardAdmin()`, ya no delegable vía Roles y Permisos); se revoca cualquier concesión previa a otros roles. La Papelera de Reciclaje sigue delegable por módulo (sin cambios). Además corrige un bug de fondo: `RolesController::getModulos()` no incluía `HorariosController`/`PermisosController`/`VacacionesController`/`AmonestacionesController`/`VisitasController` pese a tener fila en `permisos_rol` desde migraciones anteriores — el próximo guardado en Roles y Permisos les habría revocado el acceso en silencio |
| 049 | `049_horario_estandar_8_2.sql` | ✅ Ejecutado | Horario "Estándar" 8am-4pm → **8am-2pm** (cambio institucional, O5). Data-only idempotente |
| 048 | `048_falta_tipo_escalado.sql` | ✅ Ejecutado | `faltas.tipo` (Inasistencia injustificada / Incumplimiento de reglas) + `amonestaciones.id_falta_origen` (vínculo). Escalado falta→amonestación (`amonestarDesdeFalta`); UI con tipo, columna y botón "Generar amonestación" (3E) |
| 047 | `047_empleado_traslados.sql` | ✅ Ejecutado | Traslado de personal entre departamentos (3D/O3): tabla `empleado_traslados` (origen/destino depto y cargo, fecha, motivo). **Reasignación con historial** (sin flujo de aprobación): `Empleado::trasladar()` (transaccional, actualiza depto/cargo + registra histórico) y `historialTraslados()`; sección + modal en el expediente |
| 046 | `046_vacaciones_ajuste_inicial.sql` | ✅ Ejecutado | `empleados.vacaciones_ajuste_dias` (días ya disfrutados antes del sistema). `saldo = acumulado − ajuste − períodos`. UI en `/vacaciones` (roster, detalle, feriados). **Cobro/liquidación pendiente (nómina, 3B)** |
| 044 | `044_inventario_tipo_bien.sql` | ✅ Ejecutado | `inventario` +`tipo_bien` (Durable/Fungible, CHECK) +`cantidad` (≥1). Normaliza `codigo_bn`/`serial` vacíos a NULL. Durable = inventariable (Código BN obligatorio); Fungible = consumible (sin código/serial, con cantidad). `Inventario::TIPOS_BIEN`/`TIPO_BIEN_BADGES`; validación por tipo en `InventarioController::store()` + toggle en el modal (U5) |
| 042 | `042_motivo_anulacion_disciplina.sql` | ✅ Ejecutado | `amonestaciones.motivo_anulacion` + `faltas.motivo_anulacion` (TEXT): al anular una falta/amonestación RRHH registra el porqué (modal POST `eliminarFalta`/`eliminarAmonestacion`, motivo obligatorio). Alerta "Causa de despido" para **todos** (3+ amonestaciones) con botón **Procesar despido** → `empleados/detalle?egreso=despido` (preselecciona motivo Despido). Lista de empleados muestra columna **Disciplina** (conteo amonestaciones/faltas vía `Empleado::all()`) (B14) |
| 059 | `059_nomina_bono_vacacional.sql` | ✅ Ejecutado (2026-07-16) | **Nómina — Bono Vacacional v1** (R-11, "registro + reporte"): tabla `empleado_salarios` (historial salarial append-only por empleado, mismo patrón que `empleado_traslados`); `bono_vacacional_periodos`/`bono_vacacional_detalle` (corridas mensuales, snapshot por empleado); config `bono_vac_dias_*` (días base por tipo de personal — **contrato colectivo, no LOTTT** — configurables) + `monto_cesta_ticket`; RBAC `NominaController` (rol 2). Modelos `Sueldo`/`BonoVacacional`; controlador `NominaController`; sección "Datos salariales" en el expediente del empleado. El total de bono vacacional por empleado es de **captura manual** (pendiente pedirle al cliente un mes ya calculado para calibrar la fórmula exacta) |
| 060 | `060_limpieza_columnas_inertes.sql` | ✅ Ejecutado (2026-08-04) | **Limpieza de estructuras inertes** (cierra H-09 y H-10). DROP de `rutas.nombre_facilitador_externo` (solo se leía en un reporte, nunca se capturaba), `participantes_ruta.id_institucion` (siempre `null`) y `talleres.id_oficio` (cero referencias); DROP TABLE `oficios` (oficios recibidos, sin CRUD, 2 filas de prueba) e `instituciones_externas` (0 filas, módulo retirado en 2026-05-31). 51 → 49 tablas. `Ruta::inscribir()` pierde el parámetro `$id_institucion`. **No se tocó** `rutas.tiene_tarifa`/`tarifa_monto` (pendiente D-RT02) ni `oficios_emitidos` (en uso). Las etiquetas `'id_oficio'`/`'instituciones_externas'` de `auditoria/index.php` y `dashboard/index.php` se **conservan a propósito**: humanizan registros históricos de `audit_logs` (18 filas los mencionan), no son referencias vivas. Idempotente (`DROP ... IF EXISTS`). |
| 061 | `061_datos_institucionales_carnet.sql` | ✅ Ejecutado (2026-08-04) | **Carnet institucional — datos reales.** El cliente entregó el carnet físico vigente y sus datos de contacto **no coincidían** con los del sistema: `telf_institucion` `(0293) 431-4073` → **`0293-4310178`** y `correo_institucion` `imatur.cumana@gmail.com` → **`Sucreimatur@gmail.com`**. ⚠️ El correo institucional es también el **remitente** de la recuperación de contraseña (mig. 058) y aparece en constancias/oficios: las credenciales SMTP deben corresponder a esa cuenta. Claves nuevas `direccion_institucion` y `lema_institucion` ("Historia y Porvenir"), ambas editables en `/config` → Contacto Institucional. Idempotente. |
| 062 | `062_bienes_fase1_expediente.sql` | ✅ Ejecutado (2026-08-04) | **Bienes, Fase 1** (ver `docs/PLAN_MODULO_BIENES.md`). Separa **`estatus`** (administrativo: En espera de codificación · Activo · En mantenimiento · Extraviado · Robado · Dado de baja) de **`condicion`** (físico: Nuevo/Bueno/Regular/Dañado) — era el origen de H-04. El código oficial de la Alcaldía pasa a sus partes reales (`codigo_grupo`/`codigo_subgrupo`/`codigo_seccion`/`nro_orden` + `verificado_alcaldia`/`fecha_verificacion`); `codigo_bn` queda como el compuesto que arma `Inventario::componerCodigo()` (`2-01-108-084`). Datos de adquisición (`origen` Compra/Donación, `donante`, `costo_adquisicion`, `fecha_adquisicion`, `proveedor`, `tiene_garantia`, `garantia_vence`), `id_responsable` (FK empleados, único — B-26/27) y `foto_url`. `ubicaciones` +`sede` (hay 2: principal y aeropuerto) +`es_deposito`. Siembra 11 **categorías internas** (el código de la Alcaldía NO clasifica: sillas, mesas y aires comparten `2-01-108`). `tipo_bien`/`cantidad` quedan sin uso pero **no se eliminan** hasta confirmar B-66. Idempotente. |
| 063 | `063_bienes_fase2_movimientos.sql` | ✅ Ejecutado (2026-08-04) | **Bienes, Fase 2 — movimientos con origen/destino.** `actividad_inventario` +`id_ubicacion_origen`/`id_ubicacion_destino` (antes NO registraba de dónde a dónde, que es justo lo que describe B-31), +`autorizado_por` (B-32) y +`fecha_retorno`. Nuevo enum de `tipo_movimiento`: **Traslado · Asignación de responsable · Salida a mantenimiento · Retorno de mantenimiento · Baja** — los tres traslados de B-31 (depósito↔departamento, departamento↔departamento) se modelan con **un solo** tipo + origen/destino. Nueva tabla `inventario_mantenimientos` (proceso completo: encargado o taller externo, falla, trabajo, costo, resultado; índice único parcial que impide dos mantenimientos abiertos del mismo bien). Config `bienes_depto_autoriza`/`bienes_cargo_autoriza` — la Coordinadora de Bienes se identifica por **cargo + departamento** (B-64), no por un nombre fijo. Idempotente. |
| 064 | `064_bienes_fase3_documentos.sql` | ✅ Ejecutado (2026-08-04) | **Bienes, Fase 3 (parte 1): expediente documental.** `inventario_documentos` (factura, informe de la Alcaldía, oficio de donación, actas, denuncia, garantía — catálogo en `InventarioDocumento::TIPOS`); binario **fuera del web root** en `storage/uploads/bienes/`, servido por `DescargaController::bien/bm1/fotoBien` (rol 1,4), mismo patrón que RRHH. `inventario_consolidados_bm1` = **recepciones del BM-1**, que es un documento **ENTRANTE** (lo elabora la Alcaldía y lo devuelve ya codificado; el sistema NO lo genera) + `inventario.id_consolidado_bm1` para saber en qué formulario se codificó cada bien. Nueva pantalla `/inventario/consolidados` y **hoja de vida del bien** en `/inventario/detalle/{id}` (B-36: ficha, foto, documentos, mantenimientos y movimientos en una sola vista). **Falta** la GENERACIÓN de documentos (informe de bienes nuevos, acta de asignación, acta de baja): depende de recibir los formatos reales — ver `docs/PLAN_MODULO_BIENES.md` §12. |
| 065 | `065_bienes_fase4_conteo_preventivo.sql` | OK Ejecutado (2026-08-04) | **Bienes, Fase 4.** `inventario_conteos` + `inventario_conteo_detalle` = **conteo por cambio de gestion** (el dolor #2, B-05/B-48): al abrirlo se **congela** lo que el sistema cree tener de cada bien (ubicacion, estatus, condicion) y luego se registra lo hallado; el acta compara ambos. Indice unico parcial: **un solo conteo abierto** a la vez. El cierre exige que no queden bienes sin verificar, y **no modifica los bienes** - las diferencias se corrigen con movimientos normales, para que quede rastro. `inventario_mantenimiento_plan` = **preventivo programado** (B-56); al retornar de un mantenimiento el calendario avanza solo (`PlanMantenimiento::marcarRealizado`). Config `dias_aviso_garantia` / `dias_aviso_mantenimiento` / `dias_alerta_sin_codificar` para los umbrales del Centro de Alertas. Idempotente. |
| 066 | `066_bienes_responsable_automatico.sql` | OK Ejecutado (2026-08-05) | **El responsable del bien pasa a ser AUTOMATICO** (B-68). Se **elimina** `inventario.id_responsable`: el responsable ya no se almacena, se **deriva** en la consulta (`Inventario::LATERAL_RESPONSABLE`) siguiendo bien -> ubicacion -> departamento -> jefatura, con prioridad **Director** y en su defecto **Coordinador** (`cargos.nivel_jerarquico`). Los bienes en **deposito** no pertenecen a un departamento: su custodio es la jefatura de la Coordinacion de Bienes (`bienes_depto_autoriza`). **Por que derivar y no recalcular:** una columna almacenada habria que reescribirla al cambiar un cargo, al egresar un empleado o al trasladar un bien, y basta olvidar un caso para mostrar como responsable a quien ya no lo es. El historico no se pierde: `actividad_inventario.id_empleado_responsable` guarda el responsable de cada movimiento. El tipo de movimiento `Asignacion de responsable` sale de `TIPOS_MANUALES` (se conserva la constante para registros historicos). CMI-I03 y el reporte de inventario recalculados sobre la derivacion. |
| 067 | `067_bienes_respuestas_cliente.sql` | OK Ejecutado (2026-08-05) | **Respuestas del cliente B-63, B-65, B-66, B-67.** **B-66:** se ELIMINAN `inventario.tipo_bien` y `cantidad` (R-10) — IMATUR no lleva consumibles y el registro es individual; se limpiaron tambien las constantes del modelo y las consultas CMI-I01/I03 que las usaban. **B-67:** `retirado_alcaldia` + `fecha_retiro` — el bien dado de baja sigue en IMATUR hasta que la Alcaldia lo retire, y se distingue "Dado de baja · Por retirar" de "· Retirado" (`Inventario::marcarRetirado()` / `porRetirar()`). **B-65:** la **Oficina de Informacion Turistica (Aeropuerto)** pasa a ser un DEPARTAMENTO (Oficina bajo Presidencia) con su propio coordinador — y por la mig. 066 ese coordinador es automaticamente el responsable de sus bienes. Verificado antes de crearla: no existia en `departamentos` ni en el organigrama oficial. Su ubicacion jerarquica se confirmo en la mig. 068 (pasa a Planificacion y Gestion Turistica). **B-63:** `inventario_dotacion` (unidades por empleado y categoria) + reporte `/inventario/suficiencia`: compara los bienes de cada departamento contra su numero de empleados. Excluye deposito y bienes de baja/extraviados/robados. Idempotente. |
| 072 | `072_nomina_motor_calculo.sql` | ✅ Ejecutado (2026-08-27) | **Nómina: motor de cálculo (fases N-A y N-B).** El Bono Vacacional v1 (mig. 059) era "registro + reporte" porque no teníamos las fórmulas; la plantilla real las trajo y muestran que **las primas se derivan** de 4 entradas (sueldo base, grado de instrucción, años en la administración pública, nº de hijos). **Porcentajes como DATOS, no como código:** `nomina_grados` (6 filas: BACH 0 % … DR 40 %) y `nomina_antiguedad` (23 filas, incrementos por tramo, tope 30 % desde el año 23) — son parámetros de contratación colectiva, no valores de dominio, así que el patrón H-07 no aplica. **`nomina_parametros_mes`**: cesta ticket y tasa del dólar **por mes con vigencia** (hoy eran escalares sin histórico y un mes pasado no se podía reconstruir). **Entradas nuevas en la ficha**: `empleados.cuenta_nomina`/`banco_nomina`/`divisas_bono_responsabilidad`/`sueldo_dependencia_origen` y `personas.codigo_grado` (corrección manual del mapeo). **Quinto tipo de personal**: se amplía el CHECK de `bono_vacacional_detalle.tipo_personal` con *Comisión de Servicio* (+ clave `bono_vac_dias_comision`). **Nómina quincenal**: `nomina_periodos` (congela cesta ticket, tasa y semanas) + `nomina_detalle` (snapshot que guarda las **entradas** además de los resultados, para poder auditar de dónde sale cada número, y una columna `advertencias`). 13 claves escalares nuevas en `configuracion_sistema`. Modelo `Nomina` con `calcular()` **pura** (sin BD) probada por 45 casos en `tests/run.php`. |
| 071 | `071_feriados_movibles.sql` | ✅ Ejecutado (2026-08-27) | **Feriados movibles: Carnaval y Semana Santa (2026-2028).** `Vacacion::diasHabiles()` excluye fines de semana **y feriados**, y `Feriado` ya distinguía fijos (`recurrente = TRUE`, año centinela 2000, comparados por mes-día) de movibles (`recurrente = FALSE`, fecha puntual) — pero la tabla solo tenía los **12 fijos**: ningún Carnaval ni Semana Santa. El sistema contaba esos 4 días como hábiles y **descontaba vacaciones que no correspondían**. Las fechas se calcularon con el algoritmo Gregoriano anónimo y se verificaron por dos vías (contra `easter_date()` de PHP y comprobando el día de semana de cada uno). Verificado el efecto: una semana de Carnaval pasa de 5 a **3** días hábiles; semanas de control siguen en 5 y 10. **⚠️ Mantenimiento anual:** no se repiten en la misma fecha, hay que cargar los del año siguiente desde `/vacaciones/feriados` (sin marcar «se repite cada año») o extendiendo esta migración; si nadie los carga, el conteo vuelve a fallar **en silencio**. |
| 070 | `070_drop_actividades_ruta.sql` | ✅ Ejecutado (2026-08-27) | **DROP TABLE `actividades_ruta`** (cierra **H-13**). El módulo "Actividades de ruta" se retiró el 2026-05-31; la tabla quedó atrás con **cero referencias** en `app/`, **0 filas** y **0 registros** en `audit_logs` — verificado antes de soltarla, por eso (a diferencia de `id_oficio`/`instituciones_externas`) no hizo falta conservar etiqueta en `auditoria/index.php`. `CASCADE` cubre sus 2 FKs, el índice `idx_act_ruta_fecha`, la PK y la secuencia. Se **retiró también su `setval` de `009_fix_sequences.sql`**: dejarlo haría fallar esa migración en cualquier instalación ya actualizada. 56 → **55 tablas**. |
| 069 | `069_ubicaciones_semilla.sql` | ✅ Ejecutado (2026-08-27) | **Semilla de ubicaciones — desbloquea el registro de bienes.** `InventarioController::store()` exige `id_ubicacion > 0` y la tabla estaba **vacía**: era imposible registrar un bien, así que las fases 1-4 (mig. 062-067) no se podían usar. Siembra **una ubicación por departamento activo** (el departamento es la unidad de responsabilidad: el responsable se deriva de él, mig. 066) + el **Depósito General** (`es_deposito`, área común de los bienes sin asignar, B-23/B-25) y asigna la `sede` de cada una (B-24): Oficina del Aeropuerto → *Aeropuerto de Cumaná*, resto → *Sede Principal*. Total 24 oficinas + 1 depósito. Los nombres arrancan iguales a los del departamento (dato cierto) y se renombran desde la UI; la relación es 1:N, se pueden crear varias por departamento. **De paso se completó un hueco de la Fase 1:** `sede` y `es_deposito` se **leían** en todo el módulo (`Inventario::LATERAL_RESPONSABLE`, `DotacionInventario`, reporte de suficiencia, filtro de depósito) pero **no se escribían en ninguna parte** — faltaban en `Ubicacion::save()`, en el controlador y en el modal; ahora se capturan (enum `Ubicacion::SEDES`, patrón H-07). Idempotente (verificada aplicándola 3 veces). Pendiente de **datos**: cargar los ~142 bienes y asignar el Coordinador de *Compra de Bienes y Servicios* (vacante = movimientos bloqueados por diseño, B-32). |
| 068 | `068_aeropuerto_bajo_planificacion.sql` | OK Ejecutado (2026-08-05) | La **Oficina de Informacion Turistica (Aeropuerto)** pasa de Presidencia a la **Direccion de Planificacion y Gestion Turistica**, que es donde encaja por funcion (atencion al turista), junto a Promocion Turistica, Calidad y Servicios Turisticos, Proyectos e Inversion, Formacion y Comunicacion. La mig. 067 la habia creado bajo Presidencia por analogia con las oficinas de staff, marcandolo como *por confirmar*; el cliente confirmo esta ubicacion. Solo cambia la jerarquia: **no afecta a los bienes ni a su responsable**, que se deriva del departamento del bien (mig. 066), no de su posicion en el organigrama. Idempotente. |

> **Fuente única de verdad (regenerado 2026-08-04, ampliado con las mig. 069-072 el 2026-08-27):** `database/schema_consolidado.sql` es **autosuficiente**: contiene el esquema base + **todas** las migraciones **001–072** (**60 tablas**) + los catálogos institucionales sembrados + un usuario administrador de arranque. Generado desde la BD viva con `pg_dump --no-owner --no-privileges` excluyendo los datos operativos, y **verificado cargándolo en una base vacía** (2026-08-27: 60 tablas, 25 ubicaciones, 24 feriados, catálogos de nómina, 0 errores).
>
> ⚠️ **Al editar el consolidado a mano**, recordar que el dump abre con `SELECT pg_catalog.set_config('search_path', '', false)`: **toda** sentencia añadida debe calificar el esquema (`public.tabla`), o falla con «no existe la relación». Y si inserta filas con ids explícitos, el `setval` correspondiente del final debe quedar por delante del último id (pasó con `feriados_id_seq`, 12 → 24 en la mig. 071).
>
> **Instalar desde cero = importar ese archivo y nada más.** No hay que aplicar ninguna migración encima; `database/migrations/` queda como historial y para actualizar instalaciones antiguas.
>
> Trae datos: `roles`, `permisos_rol`, `configuracion_sistema`, `departamentos` (organigrama oficial, 23), `cargos`, `horarios`, `feriados`, `municipio`, `parroquia`. Quedan **vacías** las tablas operativas (personal, usuarios reales, inventario, talleres, rutas, visitantes, pasantes, asistencias, constancias, nómina, bitácora) y los correlativos de oficios en 0.
>
> **Al regenerarlo** tras nuevas migraciones, cuidar dos cosas que rompen la carga si se pasan por alto:
> 1. Las columnas de auditoría `*_by` de los seeds referencian `usuarios.id`. Las **nullables** se ponen en `\N`; las **NOT NULL** (`municipio.created_by/updated_by`, `parroquia.create_by/update_by`) deben apuntar al admin de arranque (id 1).
> 2. El bloque `DO $bootstrap$` del administrador va **entre** los datos de `departamentos` y los de `municipio`: necesita el catálogo ya sembrado y debe existir antes de que se carguen las tablas que lo referencian. (Las FK se validan al final del dump, pero `NOT NULL` se comprueba al instante.)

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
| Permisos y reposos por tipo/estado/período | 1, 2 | Excel + PDF |
| Visitantes con filtro fecha/motivo | 1, 2 | CSV + PDF |
| Talleres con filtros estado/tipo | 1, 3 | CSV + PDF |
| Dossier integral de taller | 1, 3 | Excel (multi-sección) |
| Participantes de un taller | 1, 3 | CSV |
| Rutas con filtros estado/tipo | 1, 3 | CSV + PDF |
| Pasantes con estado y tutor | 1, 3 | CSV + PDF |
| Inventario con filtros condición/categoría | 1, 4 | CSV + PDF |
| Bienes dados de baja | 1, 4 | CSV |
| Indicadores KPIs (ApexCharts) + **bloque CMI** (jornada, precisión, documentación, cobertura parroquia, frecuencia rutas, movimientos/asignación inventario) | todos | — |
| **Saldo de vacaciones** por empleado | 1, 2 | Excel |
| **Estadísticas de visitas** (afluencia por mes, únicos, situación del día) | 1, 2 | Excel |
| **Informe trimestral de Formación** (por trimestre, filtro por año) | 1, 3 | Excel |
| **Ejecuciones de ruta** (rutas Finalizadas por fecha) | 1, 3 | Excel |

### Reportes / indicadores RRHH (módulos 025-034)
- **Reporte de Asistencia** ahora incluye **puntualidad** (impuntual vs tolerancia) y **horas** trabajadas + KPIs (impuntuales, horas totales).
- **Reporte de Permisos y Reposos** (`reportes/permisos`, Excel + PDF) por categoría/estado/período.
- **Indicadores** (`reportes/indicadores`) sección Personal: clasificación (Empleado/Obrero), permisos/reposos vigentes hoy + pendientes, amonestaciones (empleados + en causa de despido), impuntualidad del mes.

### Reportes pendientes de implementar
- Réplica imprimible del **formato físico de asistencia** (requiere la planilla oficial del cliente).
- Mejoras propuestas (respaldos automáticos, endurecer login, centro de notificaciones…) — ver `docs/BACKLOG.md` §5.2.

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

**Configuración y secretos:** `config/config.php` **NO se versiona** (está en `.gitignore`); la plantilla versionada es `config/config.example.php` (copiar a `config.php` y completar por entorno). Define `APP_DEBUG` (false en producción), credenciales BD, `URL_ROOT`, `SESSION_TIMEOUT`, `PG_DUMP_PATH`, `BACKUP_RETENTION`. **No volver a commitear `config.php`.**

**Manejo de errores en producción:** `public/index.php` fija `display_errors` según `APP_DEBUG` (off en producción) + `log_errors`, y registra un `set_exception_handler` global que loguea y muestra una página 500 limpia (sin trazas). `Database` ante fallo de conexión **loguea** el detalle y muestra mensaje genérico (no expone host/credenciales). La cookie de sesión usa `httponly` + `samesite=Lax` (+ `secure` si hay HTTPS), configurada antes de `session_start()`.

**Breadcrumb dinámico (header):** `header.php` arma el rastro **Inicio / Grupo / Sección (/ Página)** desde el 1.er segmento de `$_GET['url']` mapeado a `$___bcMap` (controlador→[grupo, etiqueta]). La "Página" usa `$data['titulo']` solo si aporta detalle frente a la sección (compara normalizando acentos; omite si es redundante). El grupo (RRHH, Inventario, Sistema…) es texto plano; la sección enlaza a su índice.

**Dashboard (role-aware):** KPIs por área en `$kpiSections` (dashboard/index.php) alimentados por `DashboardController`. Añadidos recientes en Recepción: tarjeta **"Pasantes (Visitas)"** = visitas con `motivo='Pasantías'` del mes (`kpiVisitantesPasantes`); en RRHH: **"Ausencias <mes>"** = faltas activas del mes (`kpiAusenciasMes`, tabla `faltas`) — distinta de **"Impuntualidad"** (tardanzas de marcaje vía `asistencias.minutos_tarde`; 0% si nadie con horario marcó tarde).

**Centro de alertas / campana (header):** la fuente única es `CentroAlertas::resumen($rol)` (model), reutilizada por el reporte `reportes/alertas` **y** por la campana de notificaciones en `header.php` (dropdown role-aware con badge de conteo accionable). Para agregar/editar una alerta, tocar **solo** `CentroAlertas::resumen()`. La campana usa `CentroAlertas::resumenCacheado($rol)` que **memoiza en sesión** por `CACHE_TTL` (120s) — evita repetir roster/faltantes/config en cada navegación; `invalidarCache()` se llama al abrir `reportes/alertas` para refrescar tras actuar. `ReportesController::alertas()` ya no contiene la lógica (solo renderiza).

**Anti-IDOR en borrados de registros hijos:** `EmpleadosController::eliminarFamiliar/Curso/Experiencia` validan que el registro pertenezca a la **persona** del empleado (`personaDeEmpleado()` + `find()` del registro) antes de borrar; `eliminarDocumento` valida `id_empleado`. Al agregar nuevos borrados de sub-registros por id, replicar esta verificación de pertenencia. (Las transacciones ya están aplicadas donde se requieren: `Empleado::save/procesarEgreso/reingresar/trasladar`, Pasantes, Roles, ConfigSistema; los demás guardados son de una sola sentencia = atómicos.)

**Documentos privados — almacenamiento y descarga:** **TODO** archivo subido por usuarios vive **fuera del web root**, en `storage/uploads/{expedientes,pasantes,fotos,bienes,talleres}/` (no accesible por URL). Se sirve **solo** vía `DescargaController`: `::expediente($idDoc)` (rol 1,2) · `::pasante($idDoc)` (rol 1,3) · `::foto($idPersona)` (rol 1,2,3) · `::bien($idDoc)`/`::bm1($id)`/`::fotoBien($id)` (rol 1,4) · `::taller($idEv)` (rol 1,3). Cada método resuelve el archivo por **id de registro** (nunca por ruta: `basename()` del valor guardado → sin path traversal), valida rol e `is_active`, y hace stream con `Content-Disposition: inline`. Las vistas enlazan a `/descarga/<tipo>/{id}`. La subida valida extensión **y MIME real** (`mime_content_type`, NO `$_FILES['type']` que lo manda el cliente) + tamaño ≤5 MB. `DescargaController` está en `$accesoSiempre` del Router (hace su propio chequeo de rol). Nuevos uploads guardan solo el nombre de archivo; los valores antiguos (`/uploads/...`) siguen funcionando por el `basename()`.

> **`public/uploads/` ya no existe** (eliminado el 2026-08-27). Las evidencias de talleres eran la última excepción: se escribían ahí, quedaban legibles por URL sin control de rol y el enlace `URL_ROOT.'/public/uploads/...'` **se rompía bajo el vhost donde `public/` es la raíz** (`SIGTUR-IMATUR.test`). Ahora usan `TalleresController::procesarEvidencias()` (helper único; antes el bloque de subida estaba duplicado en `store()` y `cambiarEstado()`, y ninguna de las dos copias validaba MIME real ni tamaño). **No reintroducir `public/uploads`**: cualquier archivo nuevo va a `storage/uploads/<sub>/` + su método en `DescargaController`.

**Búsqueda global (header):** `BuscarController::index()` busca en empleados/inventario/talleres/rutas/visitantes con resultados **gated por rol** (cada módulo solo si el rol tiene acceso). Es accesible para cualquier usuario autenticado (incluido en `$accesoSiempre` del Router junto a `PerfilController`). El input vive en `header.php` (GET a `/buscar/index?q=`).

**Accesos al sistema (auditoría de login):** `AuthController::login()` registra en `audit_logs` los eventos `LOGIN` (éxito) y `LOGIN_FALLIDO` (sobre `tabla_afectada='usuarios'`). El reporte `reportes/accesos` (rol 1) los lista (quién, cuándo, IP). No confundir con la bitácora general de cambios (`reportes/auditoria`).

**Filtro de año en Indicadores:** `reportes/indicadores?anio=YYYY` gobierna todos los indicadores **anuales** (`$anioActual` se lee de `$_GET['anio']`, default año del servidor). Las métricas "del mes" (jornada/precisión/puntualidad) y las tendencias "últimos N meses" siguen relativas a hoy por diseño. Selector en el encabezado del panel.

**Seguridad del login (mig. 051):** `usuarios` tiene `failed_attempts`/`locked_until`/`last_login`. `AuthController::login()` bloquea la cuenta tras `Usuario::MAX_INTENTOS` (5) intentos fallidos por `Usuario::BLOQUEO_MINUTOS` (15), usa **mensaje genérico** ("Usuario o contraseña incorrectos" — no revela si el usuario existe) y `session_regenerate_id` al autenticar. Toda contraseña pasa por `Usuario::passwordPolicyError()` (mín. 8 + al menos una letra y un número) en `UsuariosController::store()` y `PerfilController::cambiarPassword()`. **Expiración por inactividad:** el Router cierra la sesión si pasan más de `SESSION_TIMEOUT` (config, 1800s) desde `last_activity`; cada request renueva el reloj y redirige a `auth/login?expired=1`.

**Recaudos del expediente — sin N+1:** para conteos masivos NO llamar `ExpedienteDocumento::recaudosEstado()` en bucle por empleado. Usar las consultas agregadas (una sola): `ExpedienteDocumento::faltantesObligatorios()` → `[id_empleado => nº faltantes]` y `entregadosPorEmpleado()` → `[id_empleado => [tipo => true]]`. Aplicado en `indicadores()`, `alertas()` y `expedientesIncompletos()`. `recaudosEstado()` queda solo para el detalle de UN expediente.

**Transiciones automáticas por fecha/hora (tarea programada):** los talleres pasan de **Programado → En Curso** al llegar su fecha/hora de inicio (con participantes) vía `Taller::autoTransicionarProgramados()`. Se ejecuta de dos formas: (1) **perezosa**, al abrir Talleres o el Dashboard (respaldo, idempotente); (2) **servidor**, con el script CLI `cron/actualizar_estados.php` programado en el Programador de tareas de Windows / cron de Laragon (~cada 10 min) para que corra aunque nadie use el sistema. Un trigger de BD NO sirve (los triggers no reaccionan al paso del tiempo); por eso es tarea programada. El script está fuera de `public/` (no accesible por web) y solo corre en CLI.

**Respaldos automáticos de BD (tarea programada):** `cron/respaldo_bd.php` (solo CLI) genera un volcado `pg_dump` en formato SQL plano en `storage/backups/` con nombre fechado (`sigtur_YYYY-MM-DD_His.sql`) y **rota** conservando los últimos `BACKUP_RETENTION` (config, default 14). La contraseña se pasa por `PGPASSWORD` (entorno), no en la línea de comandos; `PG_DUMP_PATH` (config) apunta a `pg_dump.exe`. Carpeta **fuera de `public/`** (no accesible por web) y con `.gitignore` (los dumps no se versionan). Programar en el Programador de tareas de Windows (p. ej. diario). **Restaurar:** crear BD vacía + `psql -d "SIGTUR-IMATUR" -f <archivo>.sql`. Log en `storage/backups/_backup.log`.

**Logos reales en el membrete de exportaciones (`app/core/XlsxLogos.php`, 2026-07-16):** los `.xlsx` generados a mano (`ReportesController::descargarXlsx`, `XlsxMultiSheet`) y el `.xls` del lado cliente (`sigturExportarTabla`) originalmente solo tenían el membrete institucional como **texto plano** — sin los logos reales que sí usan constancias/oficios/carnets. `XlsxLogos::piezasParaHoja($ncol)` arma el XML de `drawing`/`.rels` + los bytes de `Logo.png` (Alcaldía) y `Logo_imatur-removebg-preview.png` (IMATUR) para anclarlos como imagen real en la primera y última columna de cada hoja (mismo patrón `oneCellAnchor` en las 2 clases). El export del lado cliente (`sigtur-validations.js`) los incrusta como `data:image/png;base64,...` (`sigturLogosBase64()`, cacheado en memoria, descarga on-demand vía `fetch` desde `window.SIGTUR_LOGO_ALCALDIA`/`SIGTUR_LOGO_IMATUR` — inyectados por `footer.php`, mismo patrón que `SIGTUR_RIF`). De paso se agrandó la tipografía del membrete (título 14→16pt) y se le dio más aire (alto de fila explícito en las líneas institucionales/título/meta) en los 3 mecanismos.

**Exportación multi-hoja (`app/core/XlsxMultiSheet.php`, migración 059):** para documentos que deben reproducir un formato oficial de **varias hojas** (ej. Bono Vacacional: 4 tipos de personal + resumen) se extrajo el mismo mecanismo hecho a mano de `ReportesController` (ZipArchive + XML, sin librerías externas, celdas `inlineStr` para preservar cédulas/códigos) a una clase reusable: `nuevaHoja()`/`membrete()`/`filaFusionada()`/`filaCeldas()`/`cerrarHoja()`/`descargar()`. Misma paleta de 8 estilos (`XlsxMultiSheet::S_*`) que `ReportesController::descargarXlsx()`, para verse consistente. `ReportesController` sigue con su propio escritor de una sola hoja (no se tocó); usar `XlsxMultiSheet` para cualquier exportación nueva que necesite más de una hoja.

**Reportes (centro de reportes):** `reportes/index` es data-driven (arreglo `$secciones` con RBAC por rol). Para un reporte tabular nuevo: agregar método en `ReportesController` que arme `columnas`+`filas` (celda string = escapada; `['raw'=>'<html>']` = sin escapar, para badges), `resumen` (tiles), `filtros` (GET) y `export_url`, y renderice la **vista genérica `reportes/tabla.php`** + un `exportarXCsv()` con `exportCsv()`; luego añadir la tarjeta en `reportes/index`. **`exportCsv($filename,$headers,$rows)` exporta un `.xlsx` REAL** (OOXML vía `ZipArchive`, sin librerías externas — pese al nombre, no es CSV): membrete institucional (REPÚBLICA/ALCALDÍA/IMATUR+RIF), encabezados en color, bordes, zebra; celdas como texto para preservar cédulas/códigos con ceros. **`exportCsvSecciones($filename,$tituloReporte,$secciones)`** es la variante para reportes que NO son una tabla plana (varias secciones con su propio título/encabezado en una sola hoja, ej. Dossier de Taller) — comparte el mismo membrete y empaquetador (`construirHojaMembrete`/`descargarXlsx`) que `exportCsv()`. **PDF:** `exportPdf($titulo,$subtitulo,$headers,$rows,$kpis)` renderiza `reportes/pdf_template.php` (logos reales `public/assets/images/Logo.png` + `Logo_imatur-removebg-preview.png`, RIF, KPIs, tabla, pie institucional) — es el estándar "documento oficial" (Rutas, Comisión de Servicio, Permisos y Reposos). Reportes actuales incluyen RRHH (directorio, asistencia, permisos, amonestaciones, egresos, comisión, constancias, expedientes incompletos), Formación/Turismo (talleres, cobertura por parroquia, rutas, participación), Inventario (inventario, kardex, bienes asignados, bajas), Seguridad (auditoría) y Centro de Alertas. **Impresión/PDF vía `window.print()`:** botón + reglas `@media print` (ocultan sidebar/header/controles) → convención deliberada para vistas simples (listados, Indicadores); marcar con `.no-print` lo que no deba imprimirse. Para vistas que son "documento oficial" propio (ej. Dossier de Taller, `reportes/taller_detalle.php`) el `window.print()` incluye además un membrete institucional impreso (`d-none d-print-block`, mismos logos/RIF) para no depender solo del layout de pantalla.

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

13. **`talleres.id_oficio` y la tabla `oficios` fueron ELIMINADOS (migración 060)** — nunca se usaron: cero referencias en `TalleresController`, el modelo `Taller` y sus vistas. Lo mismo con `participantes_ruta.id_institucion` + `instituciones_externas` y con `rutas.nombre_facilitador_externo`. **No confundir con `oficios_emitidos`** (oficios salientes de rutas), que sí está en uso. Si hace falta registrar oficios **recibidos**, se construye como módulo nuevo, no reviviendo esas tablas.

14. **`configuracion_sistema`** — clave/valor para datos institucionales. `correlativo_oficio` se incrementa al generar oficio; `ano_correlativo` se reinicia automáticamente al cambiar de año.

15. **`AuditLog::log()`** — requiere `?array`. PDO retorna `stdClass`. El `Model::toArray()` hace el cast. **No pasar objetos directamente**.

16. **`AsistenciasController::marcar()`** — usa `$this->getUserId()` para registrar el usuario que marcó la asistencia. Bug corregido en fase 1.

17. **Máquina de estados de talleres (RN-F13)** — `TalleresController::validarTransicion()`. Terminales: Finalizado, Cancelado. No se puede Finalizar sin participantes.

18. **`empleados` modelo de contrato (migración 025)** — `tipo_contrato` = estabilidad: solo `'Fijo'`/`'Contratado'`, DEFAULT `'Contratado'` (todo nuevo es Contratado). `'Suplente'` y `'Comisión de Servicio'` **deprecados** (ya no son valores válidos). El origen se modela aparte: `institucion_origen` ∈ `'Alcaldía'`/`'Gobernación'`/`'IMATUR'` (DEFAULT 'IMATUR'). **`es_comision_servicio` se DERIVA del origen** (= origen ≠ IMATUR): comisión de servicio ⟺ viene de Alcaldía/Gobernación; no es checkbox manual (el asistente lo muestra como indicador). Tope de edad: IMATUR 18–65, comisión 18–70. Enums centralizados en `Empleado::TIPOS_CONTRATO` / `Empleado::INSTITUCIONES_ORIGEN` (patrón H-07). Ver `docs/MODELO_NEGOCIO_RRHH.md` 2.2 (D-RH27). **Consulta por comisión:** el listado `/empleados?origen=comision|IMATUR|Alcaldía|Gobernación` filtra por origen (`Empleado::all($origen)`/`egresados($origen)`, helper `filtroOrigen`; `comision` = origen ≠ IMATUR) y muestra columna "Origen"; además reporte `reportes/comisionServicio` (+`exportarComisionCsv`, roles 1/2) lista el personal en comisión agrupado por institución con tiempo de servicio.

18l. **Cédula solo dígitos + anti-duplicado de participantes (migración 037)** — La cédula se guarda y valida **solo con números, máx. 8** (regla global en `sigtur-validations.js`; excepción: campos `*_libre` = ID escolar/extranjeros, alfanuméricos). `TalleresController`/`RutasController` normalizan la cédula a dígitos antes de buscar/crear en `personas` (evita personas duplicadas por formato). **Anti-duplicado en la misma actividad:** personas (con cédula) ya estaban cubiertas (`Taller::estaInscrito`, check en `Ruta::inscribir`); para participantes **sin cédula (libre)** se agregó `Taller::estaInscritoLibre()`/`Ruta::estaInscritoLibre()` (mismo nombre+apellido+fecha nac, o misma `cedula_libre`) → bloquea registrar dos veces al mismo niño/a. **Control de registros basura:** reporte `reportes/duplicados` (`ReportesController::duplicados()`, roles 1/3) que agrupa posibles duplicados: personas con cédula repetida, personas con mismo nombre+apellido+fnac, y participantes libre repetidos entre talleres y rutas. Los participantes sin cédula NO tienen clave única → el sistema solo señala coincidencias para revisión humana (desambiguar con representante/docente, parroquia, género).

18m. **Ancla por representante para menores sin cédula (migración 038)** — Decisión de negocio: un niño/a sin cédula se identifica por su **representante** (adulto con cédula = identificador estable). En el flujo libre, el **representante (nombre + cédula) es OBLIGATORIO**: talleres lo guarda en `nombre_docente`/`cedula_docente` (relabeled "Representante / Docente"), rutas en `nombre_representante`/`cedula_representante` (mig.038). La cédula del representante se normaliza a dígitos (6–8) en `TalleresController::store()/actualizarParticipante()` y `RutasController::inscribir()`. El reporte `reportes/duplicados` agrupa los libre por nombre+apellido+fnac **+ cédula del representante**: así dos homónimos con representantes distintos no se marcan como duplicados, y la misma persona (mismo representante) en varias actividades sí se detecta. El bloqueo dentro de la misma actividad sigue por nombre+apellido+fnac / `cedula_libre` (`estaInscritoLibre`).

18k. **Egreso / desincorporación de empleados (migración 036, R-12)** — dar de baja a un trabajador (renuncia, despido, jubilación, fin de contrato, fallecimiento, otro) **NO borra** el registro: lo marca como egresado (`empleados.fecha_egreso` + `motivo_egreso` + `observacion_egreso`), manteniéndolo `is_active=TRUE` como **histórico consultable** (sale de la nómina activa pero sigue disponible para constancias y tiempo de servicio). `is_active=FALSE` (`delete()`) queda reservado para registros creados por error (papelera). `Empleado::all()`/`facilitadoresTalleres()` filtran `fecha_egreso IS NULL`; `Empleado::egresados()` lista el histórico; `procesarEgreso()`/`reingresar()` (transaccionales + auditados) usan la tabla `empleados_egresos` (historial; índice único parcial `uq_emp_egreso_abierto` impide dos egresos abiertos). **Reingreso con historial**: al reingresar se cierra la fila (`fecha_reingreso`) y se limpia el egreso vigente. `Empleado::tiempoServicio($ingreso,$egreso)` → "X años, Y meses" (hasta egreso o hasta hoy), embebido en la **constancia** (redacción en pasado si egresado). Enum `Empleado::MOTIVOS_EGRESO`. UI: pestañas Activos/Egresados en `empleados/index` (`?ver=egresados`), modal "Procesar egreso"/"Reingreso" en index y expediente, banner + tiempo de servicio + historial en `empleados/detalle`. Controlador: `egresar()`/`reingresar()` (POST, validan fecha ≥ ingreso y no futura).

18j. **Constancias de trabajo (migración 034, R-10)** — dentro del módulo Empleados. **Multi-tipo (B13):** `Constancia::TIPOS` (clave→etiqueta) = `trabajo`/`bancaria`(sin monto, espacio en blanco)/`horario`/`funciones`/`antiguedad`/`egreso`; la **clave** se guarda en `constancias.tipo` y `Constancia::labelTipo()` la traduce. `EmpleadosController::generarConstancia($id, $tipo='trabajo')` valida el tipo; la vista imprimible `constancia.php` adapta título y cuerpo por tipo (horario usa `horarios`+grupo; funciones usa cargo+nivel_jerárquico; egreso usa motivo/fechas). El expediente ofrece un **dropdown** de tipos (egreso solo si egresado; bancaria/horario solo si activo) y muestra un **badge de estatus** (Activo/Egresado·motivo/En permiso·tipo/En reposo·tipo, vía `PermisoLaboral::vigenteHoy()`) + tiempo de servicio. **No exige antigüedad mínima.** `Constancia::crear($idEmpleado, $tipo)` genera correlativo `CONST-` + `ConfigSistema::generarNumeroOficio('constancia')` → `CONST-NNN/AAAA` (claves `correlativo_oficio_constancia`/`ano_correlativo_constancia` sembradas en 034). `EmpleadosController::generarConstancia($id)` (crea + redirige a imprimible), `constancia($idConst)` (vista imprimible `empleados/constancia.php`, carta institucional con firmante de ConfigSistema), `eliminarConstancia()`. Historial en la sección "Constancias / Documentos generados" del expediente. RIF en la constancia = G-20008498-7 (igual que la ficha; difiere de carta_aceptacion — unificar vía ConfigSistema).

18i. **Recaudos del expediente (migración 033, R-5)** — dentro del módulo Empleados (sin RBAC nuevo). `ExpedienteDocumento::RECAUDOS` = catálogo (clave→[etiqueta, obligatorio]); `recaudosEstado($id)` arma el checklist y cuenta faltantes obligatorios. Subida en `EmpleadosController::subirDocumento()` (valida PDF/JPG/PNG ≤5MB + MIME real; nombre `Tipo_Empleado_{id}_{ts}.ext`; guarda en **`storage/uploads/expedientes/`** fuera del web root; se sirve vía `DescargaController::expediente` — ver peculiaridad "Documentos privados"). Sección "Recaudos del Expediente" en `empleados/detalle.php` (estado entregado/falta, descarga, eliminar, aviso de faltantes). La Ficha Técnica generada (R-2) es un recaudo más del catálogo.

18j. **Carnetización (migración 053)** — carnets credencial **CR80 vertical (54×85.6mm), una sola cara**, imprimibles (HTML + `@media print` + `window.print()`, sin librería PDF; igual patrón que constancias). Diseño institucional con **colores del logo IMATUR** (navy `#16407A` / océano `#1C6FB0` / dorado `#F4B41A`). Muestra: tipo **TRABAJADOR/PASANTE**, subtipo **FIJO/CONTRATADO** (solo trabajadores), nombre, cédula, cargo, departamento (pasante: carrera, institución). **Sin** RIF, expediente, vigencia ni QR (decisión del cliente). **Foto**: `personas.foto_url`, una por persona (empleados y pasantes comparten `id_persona`); subida con el helper compartido `Controller::guardarFotoPersona($idPersona)` (jpg/png ≤5MB + MIME real) → `Persona::actualizarFoto()` → `storage/uploads/fotos/`; servida por `DescargaController::foto` (ver "Documentos privados"). Endpoints: `EmpleadosController::carnet($id)` / `subirFoto`, `PasantesController::carnet($id)` / `subirFoto`. Vistas: partial compartido `app/views/inc/carnet_card.php` (recibe `$carnet` normalizado) incluido por `empleados/carnet.php` y `pasantes/carnet.php` (standalone, sin header). UI: botón "Carnet" + miniatura/modal de foto en los detalles de empleado y pasante. Hay un `public/assets/libs/qrcode.min.js` vendorizado (offline) que quedó **sin usar** (por si se reactiva el QR).

18h. **Permisos y reposos (migración 032, R-8)** — `PermisosController` (rol 2 + sidebar RRHH) sobre `permisos_laborales`. `PermisoLaboral::CATEGORIAS` (Reposo/Permiso) + `TIPOS` (cascada categoría→tipo en la UI) + `ESTADOS` (Pendiente/Aprobado/Rechazado/Anulado). Reposo y Permiso se distinguen por `categoria` (select, D-RH32). El estatus **En curso/Concluido** se DERIVA de `fecha_fin` vs hoy (no se almacena); `dias_solicitados` se calcula del rango; `duracion` es texto libre ("72 horas"/"6 meses"). Flujo: registrar (Pendiente) → aprobar/rechazar/anular. **Vacaciones NO incluido** (fórmula pendiente — D-RH04/05/NEW05). `tipo_permiso` CHECK = Reposo médico/Médico familiar/Diligencia/Duelo/Maternidad-Paternidad/Personal/Estudios/Otro.

18g. **Faltas y amonestaciones (migración 031, R-9)** — `AmonestacionesController` (rol 2 + sidebar RRHH): roster de empleados con conteo de `faltas` y `amonestaciones` activas + semáforo (`Amonestacion::roster()`), y detalle por empleado (`empleado($id)`). RRHH registra ambas manualmente (el sistema solo cuenta/notifica, D-RH28). `Amonestacion::LIMITE_DESPIDO = 3` → a las 3 amonestaciones activas se muestra "Causa de despido" (aplica a Contratado). Las `faltas` injustificadas son distintas de los permisos/ausencias justificadas (R-8, pendiente). Modelos `Falta`/`Amonestacion` (porEmpleado/save/delete, auditados).

18f. **Registro de empleado = asistente multi-paso (migración 030, R-2b)** — el alta/edición de empleado NO usa modal: es un wizard de página completa `empleados/form.php` (5 pasos: personales → formación → institucionales → carga familiar → resumen), servido por `EmpleadosController::nuevo()` y `editar($id)`, posteado a `store()`. Persiste el borrador en `localStorage` (solo alta), valida por paso, y muestra resumen antes de guardar. La carga familiar se recolecta en arrays `cf_nombre[]/cf_cedula[]/cf_fnac[]/cf_parentesco[]` e inserta tras crear la persona (`guardarCargaFamiliarInicial()`); en edición enlaza al expediente. Campos nuevos: `personas.centro_votacion/consejo_comunal/comuna`, `empleados.uniforme/talla_camisa/talla_pantalon/talla_zapato` (uniforme solo se registra, D-RH35). `Empleado::getId()/getIdPersona()` exponen los IDs tras `save()`.

18e. **Asistencia: puntualidad y ausentismo (migración 029)** — al marcar entrada, `AsistenciasController::marcar()` calcula `asistencias.minutos_tarde` vía `Asistencia::calcularMinutosTarde()` (hora real − hora del horario asignado); impuntual si `minutos_tarde > minutos_tolerancia_puntualidad` (config, default 15, editable en `/config`). `Asistencia::empleadosEnActividad($fecha)` detecta empleados en ruta (`rutas.fecha_visita` + `participantes_ruta`) o formación externa (`talleres.es_interna=FALSE` + rango fechas + `participantes_taller`) por `id_persona` → no cuentan como ausentes (RN-RH15). El index muestra resumen del día (activos/presentes/impuntuales/en actividad/ausentes) + horas trabajadas (derivadas, solo reporte; NO afectan pago). Sin horario asignado → `minutos_tarde` NULL ("sin horario").

18d. **Horarios y grupos (migración 028)** — `horarios` tiene CRUD (`HorariosController` + modelo `Horario` + `horarios/index.php`), accesible RRHH/Admin (sidebar bajo RRHH). Seed de modalidades: Estándar 08–14, OAC Matutino 07–12, OAC Vespertino 10–14, Servicios Generales 08–14 (rotación A/B). `empleados.grupo_rotacion` (A/B, `Empleado::GRUPOS_ROTACION`) solo para Servicios Generales. Config `minutos_tolerancia_puntualidad` (default 15) preparada para R-7 (puntualidad). `EmpleadosController` usa `Horario::all()` (ya no query inline).

18c. **`departamentos` jerárquico (migración 027)** — `id_padre` (auto-FK, ON DELETE SET NULL) + `tipo_unidad` ∈ Presidencia/Junta Directiva/Dirección/Coordinación/Oficina/Unidad. Estructura oficial sembrada (Presidencia → 3 Direcciones [Planificación y Gestión Turística, Administración, Talento Humano] → Coordinaciones + unidades staff). Enum en `Departamento::TIPOS_UNIDAD`; `Departamento::all()/find()` traen `padre` (nombre) y ordenan por jerarquía. El liderazgo Director/Coordinador se **deriva del cargo** del empleado (cargos `Director`/`Coordinador`/`Presidenta`), no hay campo responsable. Ver `MODELO_NEGOCIO_RRHH.md` 7.1 (D-RH30). **Listado jerárquico (U1):** `Departamento::arbol()` devuelve el recorrido en profundidad (cada unidad seguida de sus subunidades, mayor→menor nivel) con `->nivel` para indentar; lo usa `departamentos/index` (DFS, ordena por `ORDEN_TIPO`+nombre, huérfanos como raíces). Cargos se indenta visualmente por `Cargo::ORDEN_NIVEL` (escalera Presidencia→Adscrito). `Departamento::all()` (plano, ordenado) sigue para los selects de otros módulos.

18b. **Ficha Técnica del Trabajador (migración 026)** — `Empleado::find()` trae nombres por LEFT JOIN (cargo, departamento, parroquia, horario) y `Empleado::all()` incluye los campos extra de `personas` para el modal de edición. Enums `Empleado::CLASIFICACIONES` (Empleado/Obrero), `ESTADOS_CIVILES`, `NIVELES_ACADEMICOS`. Tablas hijas claveadas por `id_persona` con modelos `CargaFamiliar`/`CursoRealizado`/`ExperienciaLaboral` (métodos `porPersona/save/delete`, auditados). Expediente en `EmpleadosController::detalle($id)`; documento imprimible en `fichaTecnica($id)` → `empleados/ficha_tecnica.php`. **RIF institucional en la ficha = G-20008498-7** (según el formato físico; difiere del usado en `pasantes/carta_aceptacion.php` G-20009499-7 — discrepancia a unificar, idealmente vía `ConfigSistema`).

18n. **Bono Vacacional v1 — "registro + reporte", no cálculo legal (migración 059, R-11)** — el sistema NO calcula el monto legal del bono vacacional ni de una futura liquidación: **organiza** datos que Talento Humano ya captura (igual que hoy en Excel) y los exporta en el formato exacto. `Sueldo::actual($idEmpleado, $fecha)` resuelve el sueldo vigente en una fecha (última fila de `empleado_salarios` con `fecha_efectiva <= $fecha`) — **nunca UPDATE, siempre INSERT** (mismo patrón que `Empleado::trasladar()`), necesario para poder reconstruir el sueldo vigente en una fecha pasada. `BonoVacacional::tipoPersonal()` deriva la categoría (Alto Nivel/Empleados Fijos/Obreros Fijos/Contratados) de datos que YA existen (`cargos.nivel_jerarquico`, `tipo_contrato`, `clasificacion`) — sin captura adicional. Los días base por tipo (`bono_vac_dias_*` en `configuracion_sistema`, default 75/75/85/45) son un **beneficio de contrato colectivo superior a la LOTTT** (Art. 192: 15+1/año, tope 30) — no hardcodear ese número en código, es editable. `BonoVacacional::generarPeriodo()` congela un snapshot (`bono_vacacional_detalle`) por empleado activo; `total_bono_vacacional` queda `NULL` hasta que RRHH lo captura/verifica en `/nomina/verPeriodo/{id}` — un período `Cerrado` bloquea más edición (`actualizarDetalle()` lanza excepción). No confundir con el módulo `Vacaciones` (mig.045, cuenta **días**): éste calcula el **monto** en Bs a pagar.

19. **`configuracion_sistema` correlativos por módulo** — claves `correlativo_oficio_ruta`/`ano_correlativo_ruta` (renombradas desde 007). `ConfigSistema::generarNumeroOficio($modulo)` acepta parámetro de módulo. Formato resultado: `RUTA-007/2026` o `FORM-001/2026`.

20. **`inventario.condicion` CHECK** — ahora incluye `'En Reparación'`. Actualizar whitelist en todos los controladores que validen este campo: `['Nuevo','Bueno','Regular','Dañado','En Reparación']`.

21. **`inventario.codigo_bn` nullable** — puede ser NULL para bienes pendientes de código BN oficial. Mostrar "—" en vistas cuando sea NULL.

22. **`permisos_rol` — RBAC dinámico (migración 008)** — no modificar el RBAC tocando `Router.php`. La fuente de verdad es la tabla. `RolesController::getMapaRbac()` devuelve `[id_rol => '*']` (acceso total) o `[id_rol => ['Ctrl1', 'Ctrl2',...]]`. `DashboardController` se agrega automáticamente a todo rol en `storePermisos()`.

23. **`AuditLog::log()` en controllers** — `$this->audit()` y `$this->auditStatic()` son métodos `protected` de `Model`. Los **controllers** extienden `Controller`, no `Model` → usar `AuditLog::log()` directamente. Envolver en try-catch separado para no revertir la transacción principal si el log falla.

24. **Convención de manejo de errores** — Todo método público de controller que acceda a BD debe envolver el cuerpo en `try-catch (Exception $e)`. En caso de error: `flash('global_msg', $e->getMessage(), 'danger')` + `header('Location: ...')`. Los métodos de exportación (CSV/PDF) deben capturar excepciones **antes** de enviar cualquier header de descarga.

25. **Secuencias SERIAL (migración 009)** — Al insertar filas con IDs explícitos en seeds, las secuencias PostgreSQL no avanzan. Si aparece `llave duplicada viola restricción «X_pkey»`, ejecutar migración 009 (`009_fix_sequences.sql`) que usa `GREATEST(MAX(id), last_value)` para resincronizar las 36 secuencias sin riesgo de retroceso.

26. **Botones "Siguiente"/submit: NO deshabilitar por validez sin dar feedback (2026-07-13)** — Deshabilitar un botón (`disabled`) cuando el paso/form es inválido bloquea también su `onclick`, así que `reportValidity()` nunca se ejecuta y el usuario se queda sin ninguna pista de qué falla (ver bug real en el wizard de empleados, `wzUpdateNav()` en `empleados/form.php`). Patrón correcto: dejar el botón siempre clickeable y validar **en el handler del click** (`checkValidity()`/`reportValidity()` por campo), como hace `wzValidateStep()`. Para campos con regex propia (ej. RIF en `sigtur-validations.js::initRifInput`), agregar además un mensaje visible en vivo (`<small>` bajo el input, mismo patrón que "Cédula disponible" en el wizard) para no depender solo del globo nativo del navegador.

---

## Pasos para levantar el entorno

```bash
# 1. Laragon activo con PHP 8+ y PostgreSQL 17
# 2. Crear la base de datos:
createdb -U postgres "SIGTUR-IMATUR"

# 3. Importar el esquema consolidado — UN SOLO ARCHIVO, esto es toda la BD
#    (esquema base + migraciones 001-072 + catálogos + admin de arranque):
PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/schema_consolidado.sql

# 4. NO HAY PASO 4. No se aplica ninguna migración encima del consolidado.

# 5. Verificar config/config.php:
#    DB_HOST=localhost | DB_PORT=5432 | DB_NAME=SIGTUR-IMATUR
#    DB_USER=postgres  | DB_PASS=1234 (entorno Laragon)

# 6. URL: http://SIGTUR-IMATUR.test  o  http://localhost/SIGTUR-IMATUR/public
#    Login de arranque: admin / Sigtur2026  <-- CAMBIAR EN EL PRIMER INGRESO
```

> **Nota:** `database/schema_consolidado.sql` es autosuficiente (001–072). `database/migrations/` solo sirve como historial y para **actualizar** instalaciones antiguas, no para instalar desde cero. (`schema_completo.sql` y `schema.sql` fueron **eliminados** en 2026-08-04: cubrían hasta la 011 y el base original, y solo generaban confusión sobre cuál importar. Recuperables desde el historial de git si hicieran falta.)

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
| **`docs/PLAN_MODULO_NOMINA.md`** | **Modelo de cálculo de nómina y plan del módulo** (2026-08-07). Extraído de las fórmulas de la plantilla real (`INSTITUTO IMATUR JULIO 2026.xlsx`, datos de prueba / fórmulas reales) + audios de Talento Humano: porcentajes de prima de profesionalización por grado, escala de antigüedad con tope 30 %, transporte, hijos, deducciones, aportes patronales, alícuotas y bono de responsabilidad en divisas. Documenta que son **3 documentos** (bono vacacional · quincenal · liquidación), **5 tipos de personal** (falta Comisión de Servicio), que las primas **se derivan y no se capturan**, los 7 defectos de la plantilla del cliente y las fases N-A…N-E. **Leer antes de tocar `NominaController`, `BonoVacacional`, `Sueldo` o `empleado_salarios`.** |
| **`docs/PLAN_MODULO_BIENES.md`** | **Plan de reconstrucción del módulo de Bienes** (2026-08-04). Derivado del levantamiento con el cliente: análisis de brechas, cambios al modelo de datos, flujos (codificación · asignación · mantenimiento · baja · conteo por cambio de gestión), documentos a generar, catálogo de categorías propuesto, preguntas abiertas B-60…B-68 y fases 1-5. **Leer antes de tocar Inventario.** |
| `docs/PREGUNTAS_DESCUBRIMIENTO_Bienes_Rutas.md` | Cuestionario de descubrimiento de Bienes y Rutas (123 preguntas, redactadas desde cero). **Parte 1 (Bienes) respondida** por el cliente; **Parte 2 (Rutas) pendiente**. |
| **`docs/BACKLOG.md`** | **BACKLOG ÚNICO** — qué falta por hacer y decidir: estado por módulo, decisiones/insumos del cliente, preguntas abiertas, auditoría H-xx abierta, programación faltante. Consolida (y reemplaza) los antiguos REGISTRO_NEGOCIO/DECISIONES_PENDIENTES/preguntas/AUDITORIA_SENIOR/Notas/PLAN_ENTREGA |
| `docs/INDICADORES_GESTION.md` | **Todos los indicadores de gestión**: propósito, fórmula y fuente de datos (Dashboard + página RF30 + stats por reporte) |
| `docs/MANUAL_USUARIO.md` | **Manual de usuario por rol** (acceso/seguridad, interfaz, módulos, reportes, campana, búsqueda, perfil, FAQ) |
| `tests/run.php` | **Suite mínima de pruebas** sin dependencias (`php tests/run.php`): lógica pura sin BD (contraseñas, vacaciones, edad, tiempo de servicio) |

> **Nota (2026-07-12):** migraciones aplicadas hasta la **058**. Para qué falta y decisiones pendientes, ver siempre `docs/BACKLOG.md` (fuente única de seguimiento).

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
| Schema consolidado (instalar desde cero) | `database/schema_consolidado.sql` — **autosuficiente, 001-072 + catálogos + admin de arranque; no aplicar migraciones encima** |
| Backlog único / pendientes / decisiones | `docs/BACKLOG.md` |
| Schema base original (historial) | `database/schema.sql` |
