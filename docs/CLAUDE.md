# CLAUDE.md — SIGTUR-IMATUR
**Última actualización:** 2026-09-17 (c) — **BIENES: Acta de Desincorporación por lote (C-5, mig. 077).** `inventario_actas_desincorporacion` + `inventario.id_acta_desincorporacion`, modelo `ActaDesincorporacion`, pantalla `/inventario/actas`. **Dos estados, como en la realidad:** *emitida* (se arma con los bienes desincorporados, se imprime y se lleva) → *firmada* (vuelve sellada, se registra con su escaneado y **TODOS sus bienes pasan a «Retirado» de una vez**). Reemplaza al `marcarRetirado()` bien por bien, que **se conserva** para confirmaciones sueltas. Anular un acta firmada **revierte el retiro**: el aval que lo respaldaba dejó de existir. Correlativo propio (`correlativo_oficio_acta`), que no se recicla. Endpoint propio `DescargaController::acta()` — el id es de la tabla de actas, `bien()` habría servido el archivo equivocado. ⚠️ **La vista imprimible es PROVISIONAL**: el formato oficial no ha llegado (R-2). Cuando llegue se sustituye solo `acta_imprimible.php`; la tabla, el flujo y lo registrado no se tocan.

**Anterior:** 2026-09-17 (b) — **BIENES: H-16 cerrado y el estatus pasa a «Desincorporado» (mig. 076).** El reporte *Bienes Dados de Baja* medía la **papelera** (`is_active = FALSE`) en vez de `estatus = 'Dado de baja'`: una desincorporación real nunca aparecía y un registro borrado por error sí. Sobrevivió a la reconstrucción del módulo porque la consulta estaba **copiada tres veces** (listado + 2 exportaciones); ahora hay un solo origen, `bajasQuery()`. El **mismo error estaba en el KPI del Dashboard** (`kpiBajasAnio`) y también se corrigió. La fecha del reporte sale del **movimiento de Baja**, no de `deleted_at`, y se añadió la columna *Por retirar / Retirado* (B-67). **C-6:** `EST_BAJA` pasa de `'Dado de baja'` a **`'Desincorporado'`** (mig. 076, CHECK nuevo) — se renombró el **valor**, no solo el rótulo, aprovechando que `inventario` está en 0 filas. ⚠️ Eso obligó a tocar **catorce consultas** que tenían el texto CABLEADO (Dashboard, indicadores, reportes, Centro de Alertas, dotación): ahora usan `:baja` o `Inventario::sqlEstBaja()`. **No vuelvas a escribir el literal en SQL.** También se quitó de `/config` la sección *Nómina*, que solo contenía un cartel apuntando a `/nomina/parametros`.

> 📓 **Historial anterior:** `docs/CHANGELOG.md`. Esta cabecera acumulaba 7 bloques
> «Anterior:» (8 KB) antes de la primera línea de referencia técnica. Aquí solo va el
> último cambio; cuando deje de ser el último, se muda al changelog.

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
| `inventario` | Bienes. **`estatus`** (administrativo: En espera de codificación · Activo · En mantenimiento · Extraviado · Robado · Dado de baja) **separado de `condicion`** (físico: Nuevo/Bueno/Regular/Dañado) desde la mig. 062 — no confundirlos ni filtrar por la condición para saber si un bien está activo. Código oficial **por partes** (`codigo_grupo`/`codigo_subgrupo`/`codigo_seccion`/`nro_orden`) + `codigo_bn` compuesto · adquisición (`origen`, `donante`, `costo_adquisicion`, `proveedor`, garantía) · `verificado_alcaldia`/`fecha_verificacion` · `retirado_alcaldia`/`fecha_retiro` · `id_consolidado_bm1` · `foto_url`. El **responsable NO se almacena**: se deriva del departamento (mig. 066) |
| `inventario_consolidados_bm1` · `inventario_documentos` · `inventario_mantenimientos` · `inventario_mantenimiento_plan` · `inventario_conteos`(+`_detalle`) · `inventario_dotacion` | Expediente y ciclo de vida del bien (mig. 062-067). Ver `docs/PLAN_MODULO_BIENES.md` |
| `actividad_inventario` | Movimientos con **origen/destino** y `autorizado_por` (mig. 063): Traslado · Asignación de responsable · Salida/Retorno de mantenimiento · Baja |

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
| `ruta_ejecuciones` · `ruta_ejecucion_empleados` | **Cada salida** de un recorrido (mig. 078): fecha, estado (Programado/Ejecutado/No ejecutado), origen, institución solicitante, aprobación y el personal de IMATUR que va |
| `ruta_informes` · `ruta_ficha_grupos` | **Ficha Institucional** de la salida (mig. 080): cabecera + renglones por institución y de apoyo. Ver la trampa de la mig. 080 |
| `oficios_emitidos` | Oficios salientes generados desde rutas *(005)* |

#### Geografía
| Tabla | Descripción |
|-------|-------------|
| `municipio` | Municipios con código postal; `created_at NOT NULL` sin DEFAULT |
| `parroquia` | Por municipio; nomenclatura inconsistente: `create_at`/`create_by` sin "d" |

---

### Migraciones — Estado de ejecución

El **índice completo de migraciones está en `database/migrations/`** (un archivo por migración, con su
propio encabezado explicando el porqué). Reproducirlo aquí como tabla costaba 39 KB —el 30 % de este
documento— y quedaba desactualizado en cuanto se agregaba una.

**Lo que hay que saber sin abrir la carpeta:** para instalar se usa `schema_consolidado.sql` y nada
más; `database/migrations/` es historial y sirve para **actualizar** instalaciones antiguas.

Estas son las únicas migraciones que dejaron una **trampa o una convención viva**. Si vas a tocar esa
área, lee su archivo antes:

| # | Qué dejó vivo |
|---|---|
| 009 | **Secuencias SERIAL.** Insertar con id explícito no las avanza. Si aparece `llave duplicada viola restricción «X_pkey»`, reejecutarla (usa `GREATEST(MAX(id), last_value)`). |
| 023 | `genero` CHECK = `IN ('M','F')` en **4 tablas** (personas, visitantes, participantes_taller, participantes_ruta). No reintroducir `'O'`. |
| 025 | **Modelo de contrato.** `tipo_contrato` (Fijo/Contratado) e `institucion_origen` son campos distintos; `es_comision_servicio` **se deriva** (origen ≠ IMATUR), no es checkbox. |
| 037 | **Cédula = solo dígitos, máx. 8.** Toda búsqueda o guardado por cédula debe normalizar antes (excepción: campos `*_libre`). |
| 050 · 060 · 070 | **Limpiezas.** Se eliminaron `taller_inventario`, `participantes_taller.es_brigadista`, `oficios`, `talleres.id_oficio`, `instituciones_externas`, `participantes_ruta.id_institucion`, `rutas.nombre_facilitador_externo` y `actividades_ruta`. **No revivirlas**: si hace falta registrar oficios recibidos, es un módulo nuevo. |
| 051 | **Login endurecido.** `failed_attempts`/`locked_until`; 5 intentos → 15 min de bloqueo; mensaje genérico a propósito. |
| 053 | `personas.foto_url` — una foto por **persona**, compartida entre empleado y pasante. |
| 062 · 076 | **`estatus` (administrativo) ≠ `condicion` (físico)** — origen del bug H-04. Y desde la 076 el estatus de baja se llama **`'Desincorporado'`**: no queda ningún `'Dado de baja'` cableado. Para listar desincorporados usar `Inventario::desincorporados()`, **nunca `is_active = FALSE`** (eso es la papelera — fue el bug H-16). |
| 069 | **Semilla de `ubicaciones`.** Sin esas filas es imposible registrar un bien. |
| 071 | **Feriados movibles.** Carnaval y Semana Santa **no se repiten en fecha**: hay que cargar cada año desde `/vacaciones/feriados`. Si nadie los carga, el conteo de vacaciones falla **en silencio**. |
| 072 | **Motor de nómina.** Los porcentajes viven en tablas (`nomina_grados`, `nomina_antiguedad`), no en código — son parámetros de contratación colectiva, el patrón H-07 no aplica. `Nomina::calcular()` es **pura** (sin BD) y está cubierta por 45 pruebas. |
| 074 | **La tasa del dólar nunca se consulta al calcular.** `generarPeriodo()`/`recalcular()` no salen a internet: usan la tasa congelada en `nomina_periodos`. |
| 078 | **Rutas: catálogo ≠ salida.** `rutas` es el recorrido reutilizable; `ruta_ejecuciones` es cada salida. Participantes, asistencia, informe y oficios cuelgan de **`id_ejecucion`**, no de `id_ruta` — y `participantes_ruta.id_ruta` / `ruta_informes.id_ruta` quedaron **NULLABLE** a propósito (son la columna vieja, solo para las filas migradas). Escribir siempre `id_ejecucion`. |
| 080 | **La Ficha Institucional VIVE EN `ruta_informes`**, no en una tabla nueva: R-43, R-47 y R-48 eran el mismo documento. Sus renglones están en `ruta_ficha_grupos` (`tipo` = Institucion/Apoyo). **`mujeres`/`hombres`/`ninas`/`ninos`/`total_atendidos` son DERIVADAS** — las recalcula `RutaFicha::recalcular()` desde los renglones en cada guardado, como `taller_informes.total_atendidas`. **No escribirlas a mano.** Hay un índice único por `id_ejecucion`: una ficha por salida. La ficha **nace sola** al pasar la salida a «Ejecutado» (R-50), en `RutaEjecucion::cambiarEstado()`. |
| 079 | **La edad NO se valida en el código.** El rango vive en el recorrido (`rutas.edad_min`/`edad_max`, NULL = sin tope) y la única regla es `Ruta::motivoEdadNoValida()`, que se aplica en los dos flujos de inscripción. **No reintroducir un 5–11 ni ningún rango literal** — fue el bug H-17. El cupo de 60 es por **día** (`ConfigSistema::get('rutas_cupo_diario')`, 0 = sin tope) y **advierte, no bloquea**. |

> **Fuente única de verdad (regenerado 2026-08-04, ampliado hasta la mig. 076):** `database/schema_consolidado.sql` es **autosuficiente**: contiene el esquema base + **todas** las migraciones **001–076** (**60 tablas**) + los catálogos institucionales sembrados + un usuario administrador de arranque. Generado desde la BD viva con `pg_dump --no-owner --no-privileges` excluyendo los datos operativos, y **verificado cargándolo en una base vacía** (2026-08-27: 60 tablas, 25 ubicaciones, 24 feriados, catálogos de nómina, 0 errores).
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

**Utilidades CSS (2026-08-28):** para presentación suelta, usar primero las de **Bootstrap 5**, que ya está cargado en local (`assets/libs/bootstrap.min.css`) y las genera con `!important`: `text-center`, `text-end`, `d-none`, `mb-3`… Lo propio va en el bloque *UTILIDADES* al final de `sigtur-components.css` (hoy solo `.u-num`, cifras tabulares). **Llevan `!important` a propósito:** sustituyen `style=""` inline, y un inline gana a toda regla sin la marca — sin ella, `.sig-table th { text-align: left }` se impondría donde antes mandaba el inline.

> **Dos trampas al migrar `style=""` a clases** (documentadas tras el barrido del 2026-08-28):
> 1. **Nunca cambiar `style="display:none"` por `.d-none`.** Hay **143** sitios que hacen
>    `el.style.display = '…'` por JS; el `!important` de la clase le gana al inline del toggle y el
>    elemento no vuelve a aparecer.
> 2. **19 vistas son standalone** (constancias, fichas, carnets, oficios, listas de asistencia, login):
>    no incluyen `inc/header.php` y por tanto **no cargan Bootstrap**. Una clase ahí no tiene estilo.
>
> Solo es fiel migrar una familia si no existe ninguna regla `!important` que compita por esa
> propiedad. `color`, `padding` y `margin` **sí** chocan (overrides del modal, tema oscuro, `@media print`).

**Accesibilidad de formularios:** todo control con etiqueta debe llevar `<label for="…">` + `id` en el control (393 de 469 al 2026-08-28). Los `id` han de ser únicos en la **página**, no en el archivo: toda vista incluye el layout, así que no pueden chocar con los de `inc/header.php`/`inc/footer.php`. Dentro de un `foreach`, el `id` **tiene que generarlo PHP** (p. ej. `id="motivo_<?php echo $x->id; ?>"`) — uno estático se repetiría en cada fila.

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

**Parámetros de nómina: UNA sola pantalla (2026-09-14).** Todo lo que interviene en el cálculo se
administra en `/nomina/parametros`, **nada está fijo en el código**: cesta ticket y tasa del dólar por
mes (`nomina_parametros_mes`), porcentajes de profesionalización (`nomina_grados`) y de antigüedad
(`nomina_antiguedad`), y los escalares `nomina_*` + `bono_vac_dias_*` de `configuracion_sistema`
(`NominaController::guardarEscalares()`, que **solo acepta las claves declaradas por
`Nomina::params()` y `BonoVacacional::CONFIG_DIAS`** — nunca lo que venga en el POST, para que ese
endpoint no escriba cualquier otra configuración). La sección de nómina se **quitó de `/config`**, que
queda con un enlace: tener los mismos valores en dos formularios era pedir que se desincronizaran.

> **Lo que se encontró al moverlo** (y por qué convenía moverlo): (1) los **13 escalares `nomina_*`**
> —SSO, FAOV, LRPPF, aportes patronales, transporte, prima por hijo, becas, semanas, días base— **no
> eran editables en ninguna pantalla**: `/config` nunca los renderizó, y como `ConfigController::store()`
> solo actualiza las claves presentes en el POST, no había forma de tocarlos desde la UI pese a que la
> vista de nómina decía «Se editan en Configuración». (2) `bono_vac_dias_comision` (5.º tipo de
> personal, mig. 072) **faltaba** en el formulario de `/config`, así que los días de Comisión de
> Servicio tampoco se podían cambiar. (3) El escalar `monto_cesta_ticket` de `configuracion_sistema`
> está **muerto**: desde la mig. 072 la cesta ticket vive por mes en `nomina_parametros_mes` y es de
> ahí de donde leen `Nomina` y `BonoVacacional`. Se retiró el campo (la fila queda, inofensiva);
> mostrarlo hacía creer que se estaba fijando la cesta ticket del cálculo.

**Consulta al BCV (`app/core/TasaBcv.php`, mig. 074):** el BCV **no tiene API** — se lee su portada
(bloque `id="dolar"`; la fecha valor sale del atributo `content` en ISO, no del texto en español). Dos
cosas que no son obvias y ya costaron tiempo: (1) **el BCV publica con fecha valor adelantada** —un
domingo la portada ya muestra la tasa del martes—, así que **nunca usar `CURRENT_DATE`** como fecha de
la tasa; (2) **su servidor entrega la cadena de certificados equivocada** (manda un intermedio Sectigo
distinto del que firmó su certificado), y OpenSSL —lo que usa PHP— no persigue el intermedio que falta
como sí hacen los navegadores (AIA). Por eso todos los ejemplos que circulan traen
`CURLOPT_SSL_VERIFYPEER => false`: **no hacer eso**, dejaría que un intermediario dicte la tasa con la
que se paga la nómina. La solución es el intermedio correcto versionado en
`storage/certs/bcv-sectigo-ca.pem` (vence 2036, ver su `LEEME.md`), con la verificación **activa**. Se
descartaron los espejos JSON de terceros: al comprobarlos, `ve.dolarapi.com` iba 4 días atrasado (~1,2 %
de diferencia) y `pydolarve.org` no respondía. Todo fallo lanza `Exception` con causa legible y la tasa
se carga a mano: **nunca bloquea la nómina**.

**Membrete institucional — UN solo partial (2026-09-14):** todo documento imprimible incluye `app/views/inc/membrete.php` (logo de la **Alcaldía** a la izquierda, bloque central de 5 líneas en mayúsculas —República / Alcaldía / Instituto / Cumaná / RIF— y logo de **IMATUR** a la derecha, con línea de separación). Opciones vía `$mb` antes del `require`: `alto_logo`, `tamano`, `fuente`, `margen_inf`, `sin_linea`, `dependencia`. Migradas **12 vistas**: oficio de ruta (la referencia que el cliente dio por buena), oficio imprimible, constancia, ficha técnica, cartas de pasantes (aceptación y culminación), listas de asistencia (interna y externa), informe de taller (pantalla e imprimible), acta de conteo, `reportes/pdf_template` y `reportes/taller_detalle`. Las **tres vías de exportación** usan las mismas 5 líneas: `ReportesExportTrait::construirHojaMembrete()`, `XlsxMultiSheet::membrete()` y `sigturExportarTabla()` del lado cliente (antes eran 3 líneas, con Instituto y RIF fusionados).

> 🔴 **`Logo.png` NO es el logo de la Alcaldía, es el de IMATUR.** Todo el sistema lo venía usando como si fuera el de la Alcaldía (`alt="Alcaldía de Cumaná"` incluido), así que **los documentos salían con el mismo logo repetido a izquierda y derecha** — justo lo que el cliente señaló. El de la Alcaldía es **`NR2.png`** («ALCALDÍA DE CUMANÁ»), que **no se usaba en ningún membrete**. Las rutas viven ahora en `ConfigSistema::LOGO_ALCALDIA`/`LOGO_IMATUR` con cuatro accesores (`urlLogoAlcaldia`/`urlLogoImatur` para `<img>` y JS; `rutaLogoAlcaldia`/`rutaLogoImatur` para los exportadores): **no volver a escribir estas rutas a mano.** Además, varias vistas repetían el logo de IMATUR a los dos lados (ficha técnica, carta de aceptación) y otras mostraban uno solo (informe de taller, lista externa). `Logo_imatur.png` (1,2 MB) queda sin uso: el membrete usa la versión `-removebg-preview`.

**Logos reales en el membrete de exportaciones (`app/core/XlsxLogos.php`, 2026-07-16):** los `.xlsx` generados a mano (`ReportesController::descargarXlsx`, `XlsxMultiSheet`) y el `.xls` del lado cliente (`sigturExportarTabla`) originalmente solo tenían el membrete institucional como **texto plano** — sin los logos reales que sí usan constancias/oficios/carnets. `XlsxLogos::piezasParaHoja($ncol)` arma el XML de `drawing`/`.rels` + los bytes de `Logo.png` (Alcaldía) y `Logo_imatur-removebg-preview.png` (IMATUR) para anclarlos como imagen real en la primera y última columna de cada hoja (mismo patrón `oneCellAnchor` en las 2 clases). El export del lado cliente (`sigtur-validations.js`) los incrusta como `data:image/png;base64,...` (`sigturLogosBase64()`, cacheado en memoria, descarga on-demand vía `fetch` desde `window.SIGTUR_LOGO_ALCALDIA`/`SIGTUR_LOGO_IMATUR` — inyectados por `footer.php`, mismo patrón que `SIGTUR_RIF`). De paso se agrandó la tipografía del membrete (título 14→16pt) y se le dio más aire (alto de fila explícito en las líneas institucionales/título/meta) en los 3 mecanismos.

**Exportación multi-hoja (`app/core/XlsxMultiSheet.php`, migración 059):** para documentos que deben reproducir un formato oficial de **varias hojas** (ej. Bono Vacacional: 4 tipos de personal + resumen) se extrajo el mismo mecanismo hecho a mano de `ReportesController` (ZipArchive + XML, sin librerías externas, celdas `inlineStr` para preservar cédulas/códigos) a una clase reusable: `nuevaHoja()`/`membrete()`/`filaFusionada()`/`filaCeldas()`/`cerrarHoja()`/`descargar()`. Misma paleta de 8 estilos (`XlsxMultiSheet::S_*`) que `ReportesController::descargarXlsx()`, para verse consistente. `ReportesController` sigue con su propio escritor de una sola hoja (no se tocó); usar `XlsxMultiSheet` para cualquier exportación nueva que necesite más de una hoja.

**Organización de `ReportesController` (2026-08-28):** el controlador llegó a **3.405 líneas y 101 métodos** y se repartió en **8 traits** bajo `app/controllers/reportes/`, quedando en `ReportesController.php` solo el índice y los tres helpers transversales (`requireRoles`, `qsFiltros`, `renderReporte`). Los traits se componen en la misma clase, así que `$this`, los métodos privados y las firmas son **idénticos**: no cambió ninguna URL ni llamada. Se incluyen con `require_once __DIR__ . '/reportes/…'` porque el autocargador de `public/index.php` solo mira rutas planas y no entra en subdirectorios. **Un reporte nuevo va en el trait de su área**, no en el controlador: `ReportesRrhhTrait` (personal, asistencia, permisos, disciplina, vacaciones) · `ReportesFormacionTrait` (talleres, cobertura, dossier, pasantes, trimestral) · `ReportesTurismoTrait` (rutas, participación, ejecuciones) · `ReportesInventarioTrait` (bienes, kardex, asignaciones, bajas) · `ReportesRecepcionTrait` (visitantes, visitas) · `ReportesSistemaTrait` (alertas, auditoría, accesos, duplicados) · `ReportesIndicadoresTrait` (CMI) · `ReportesExportTrait` (helpers CSV/XLSX/PDF).

**Reportes (centro de reportes):** `reportes/index` es data-driven (arreglo `$secciones` con RBAC por rol). Para un reporte tabular nuevo: agregar método en el **trait de su área** (ver arriba) que arme `columnas`+`filas` (celda string = escapada; `['raw'=>'<html>']` = sin escapar, para badges), `resumen` (tiles), `filtros` (GET) y `export_url`, y renderice la **vista genérica `reportes/tabla.php`** + un `exportarXCsv()` con `exportCsv()`; luego añadir la tarjeta en `reportes/index`. **`exportCsv($filename,$headers,$rows)` exporta un `.xlsx` REAL** (OOXML vía `ZipArchive`, sin librerías externas — pese al nombre, no es CSV): membrete institucional (REPÚBLICA/ALCALDÍA/IMATUR+RIF), encabezados en color, bordes, zebra; celdas como texto para preservar cédulas/códigos con ceros. **`exportCsvSecciones($filename,$tituloReporte,$secciones)`** es la variante para reportes que NO son una tabla plana (varias secciones con su propio título/encabezado en una sola hoja, ej. Dossier de Taller) — comparte el mismo membrete y empaquetador (`construirHojaMembrete`/`descargarXlsx`) que `exportCsv()`. **PDF:** `exportPdf($titulo,$subtitulo,$headers,$rows,$kpis)` renderiza `reportes/pdf_template.php` (logos reales `public/assets/images/Logo.png` + `Logo_imatur-removebg-preview.png`, RIF, KPIs, tabla, pie institucional) — es el estándar "documento oficial" (Rutas, Comisión de Servicio, Permisos y Reposos). Reportes actuales incluyen RRHH (directorio, asistencia, permisos, amonestaciones, egresos, comisión, constancias, expedientes incompletos), Formación/Turismo (talleres, cobertura por parroquia, rutas, participación), Inventario (inventario, kardex, bienes asignados, bajas), Seguridad (auditoría) y Centro de Alertas. **Impresión/PDF vía `window.print()`:** botón + reglas `@media print` (ocultan sidebar/header/controles) → convención deliberada para vistas simples (listados, Indicadores); marcar con `.no-print` lo que no deba imprimirse. Para vistas que son "documento oficial" propio (ej. Dossier de Taller, `reportes/taller_detalle.php`) el `window.print()` incluye además un membrete institucional impreso (`d-none d-print-block`, mismos logos/RIF) para no depender solo del layout de pantalla.

**Listados con búsqueda + paginación (convención global, opt-in):** agregar `data-tabla-buscable` al contenedor `.sig-table-wrap` (que envuelve un `table.sig-table`) inyecta una **barra de búsqueda** arriba y un **paginador** abajo, del lado cliente (`initTablasBuscables` en `sigtur-validations.js`). Opcionales: `data-por-pagina` (default 10) y `data-buscar-placeholder`. Filtra filas por texto y pagina; ignora la fila de estado vacío. Aplicado en los índices de varios módulos (empleados, inventario, pasantes, usuarios, amonestaciones, permisos, y catálogos). **Excepciones (paginación del lado SERVIDOR, no usar el helper):** `talleres`, `rutas`, `auditoría`, **`asistencias` y `visitantes`** paginan en el backend (modelos con `paginate($pagina,$porPagina,$filtros)` → `['items','total']`; controlador lee `$_GET['p']`+filtros; vista con form GET de filtro y nav de páginas que preserva filtros). Patrón de referencia: `Taller::paginate` / `Asistencia::paginate` / `Visita::paginate`. El helper cliente filtra sobre las filas ya cargadas; para volúmenes grandes usar siempre paginación servidor.

**Impresión: `@page { margin: 0 }` + padding en la hoja + sello propio (2026-09-14).** Chrome imprime **su propio encabezado y pie** (fecha · título de la pestaña · URL · nº de página) **en el margen de página**. Es **todo o nada**: no hay CSS que quite solo la URL y el título dejando la fecha (se comprobó que el iframe del exportador **hereda la URL de la página**, así que vaciar el `<title>` no bastaba). Por eso el PDF de listados **dibuja su propio sello** `.sello` con fecha y hora cortas en la esquina superior izquierda —donde Chrome ponía la suya—, en `position:fixed` **para que Chrome lo repita en todas las hojas**. Así el documento conserva la marca de emisión que le da validez, sin la URL ni el nombre del módulo. En un documento oficial eso no puede salir, y no se puede desactivar por JavaScript: la única vía desde el documento es **dejar `@page` sin margen** —sin franja, no hay dónde dibujarlo— y dar el aire con **`padding` en el contenedor de la hoja**. Aplicado en el PDF de listados (`sigturExportarTabla`, que además emite `<title>` **vacío** como segundo cinturón), `reportes/pdf_template`, constancia, ficha técnica, oficio imprimible de rutas, carta de culminación, las dos listas de asistencia y el informe imprimible de talleres. **Al crear una vista imprimible nueva, no poner margen en `@page`:** ponerlo en el padding del contenedor.

**Márgenes en las hojas 2+ — filas espaciadoras en `thead`/`tfoot` (2026-09-14).** Con `@page{margin:0}`, el `padding` del `body` solo vale para la **primera** hoja: en las siguientes la tabla arrancaba pegada al borde del papel y chocaba con el sello de la esquina. La tabla del PDF de listados lleva ahora una fila vacía `tr.sp` (11 mm) **dentro de `thead`** y otra dentro de **`tfoot`**: como ambos grupos se repiten en cada hoja (es el mismo mecanismo por el que ya se repetían los encabezados de columna), hacen de margen superior e inferior página a página.

**Adornos que no deben viajar al export — `data-no-export` en línea (2026-09-14).** El atributo ya servía para saltarse una **columna** entera (en el `th`); ahora `sigturTablaMatriz()` también lo respeta **dentro de una celda**: clona la celda, elimina los nodos marcados y lee el texto. Se usa en los listados jerárquicos (departamentos y cargos), que anteponen `└ ` al nombre para dibujar el árbol en pantalla — ese carácter salía en el Excel y en el PDF. Preferido a recortar caracteres a ciegas: el adorno se marca donde se crea.

**Nombre del documento exportado — `data-titulo-export` (2026-09-14):** el exportador tomaba el `.page__title`, que nombra la **pantalla** («Módulo de Pasantes», «Gestión de Personal») y se lee mal como encabezado de un documento oficial. Ahora el `.sig-table-wrap` puede declarar su propio nombre: `data-titulo-export="Listado de Pasantes Registrados"`. Sin el atributo, el comportamiento es el de antes. Puesto en pasantes, empleados (cambia según la pestaña Activos/Egresados), bienes, movimientos, permisos, amonestaciones, vacaciones, cargos y departamentos. También cambió el pie: de `Generado: 14/9/2026, 9:31:52 a. m. · 2 registro(s)` a dos líneas — **«Emitido en Cumaná el 14 de septiembre de 2026 a las 9:31 a. m.»** y el conteo en negrita, con singular/plural correcto (`sigturFechaLarga`/`sigturHoraCorta`).

**El botón Excel de los listados genera un `.xlsx` REAL desde el servidor (2026-09-14).** Ya no se arma un HTML con extensión `.xls` en el navegador: `sigturExportarTabla(…, 'excel')` monta un formulario oculto y hace **POST** a **`ExportarController::tabla()`** con `titulo`, `headers` y `rows` en JSON; el servidor lo escribe con el mismo generador de los reportes de Análisis (`ReportesExportTrait::exportCsv()`, que ahora acepta un **4.º parámetro con el título explícito** para no derivarlo del slug y perder tildes). Se mandan las filas **que el usuario tiene en pantalla** —ya filtradas por el buscador y de todas las páginas—, así el servidor no rehace la consulta de cada módulo. Va por POST porque una tabla completa no cabe en una URL y el listado no debe quedar en el historial; el JSON evita el corte silencioso de `max_input_vars` (1000 entradas). Cotas en el controlador: 20.000 filas, 60 columnas, 2.000 caracteres por celda, y las celdas que empiezan por `= + - @` se prefijan con `'` para que ninguna hoja de cálculo las ejecute como fórmula. **El PDF sigue siendo del lado cliente** (el navegador sí pinta los logos).

> **Dos ajustes que el endpoint necesitó en el Router:** `ExportarController` entra en `$accesoSiempre` (no expone datos nuevos —da formato a lo que el usuario ya ve, filtrado por el RBAC de su módulo— pero exige sesión), y en la **exención del token anti doble-envío**: no escribe nada, y exigírselo impedía exportar dos veces seguidas sin recargar. Fue el motivo por el que el endpoint devolvía 302 en la primera prueba.

> 🔴 **El `.xls` del lado cliente NO podía llevar logos** (por eso se movió al servidor). Es un HTML con extensión `.xls`, y **Excel no renderiza imágenes en `data:` URI** al importarlo: salían **dos recuadros con una X roja** a los lados del membrete (reportado por el cliente el 2026-09-14). Las celdas de logo además estrechaban la primera y la última columna, partiendo la cédula en dos líneas. Se retiraron: queda el membrete de texto, centrado y a todo el ancho, con `white-space:nowrap` para que Excel dé el ancho natural a cada columna. **Los logos sí salen** en el `.xlsx` real del servidor (`XlsxLogos`, imagen incrustada como `drawing`) y en el **PDF del lado cliente**, que ahora sí los incrusta (lo pinta el navegador, no Excel) y espera a que decodifiquen antes de abrir el diálogo de impresión.

**Exportación de listados a Excel/PDF (convención global, automática):** todo `.sig-table-wrap` con `data-tabla-buscable` obtiene **automáticamente** botones **Excel** y **PDF** en su barra (`initTablasBuscables` → `sigturExportarTabla` en `sigtur-validations.js`). Sin tocar controladores ni vistas. Exporta el conjunto **filtrado completo** (todas las páginas, respeta el buscador), **omite** la columna de acciones (`th/td.col-actions` o `data-no-export` por columna) y aplica el **membrete institucional** (RIF vía `window.SIGTUR_RIF`). Excel = `.xls` HTML/Office (celdas como texto, preserva cédulas/códigos); PDF = documento limpio en un iframe oculto → diálogo de impresión (Guardar como PDF). **Opt-out del listado completo:** `data-no-export` en el contenedor (aplicado a `reportes/tabla` y `reportes/comision`, que ya traen exportación server-side con membrete vía `ReportesController::exportCsv`).

**Select con búsqueda (convención global, opt-in):** agregar la clase `js-search` a un `<select>` lo convierte en un **combobox con buscador** (`initSearchSelect` en `sigtur-validations.js`): un campo de texto filtra las opciones; al elegir una se fija el valor del select original (queda oculto pero se envía en el POST). Sin librerías externas (entorno sin internet). Conserva `required` vía el campo visible (`setCustomValidity`), integrándose con "botón deshabilitado hasta válido". Se auto-aplica en carga y `shown.bs.modal`; para selects inyectados por AJAX llamar `window.initSearchSelect(sel)`. Usado en Asistencia (elegir empleado); reutilizable en cualquier select largo.

**Botones de acción en tablas (convención global, automática):** usar la clase `.row-action` (+ variante `--edit` / `--del` / `--view`, o ninguna para neutro) con **un ícono Bootstrap** y el texto de la acción. `initRowActions()` (`sigtur-validations.js`) deja **solo el ícono** y mueve el texto a `title`/`aria-label` (tooltip), dando un patrón visual uniforme y moderno en todo el sistema (cuadrado 32px vía `.is-icon`). No hace falta escribir el markup icon-only a mano: poner ícono + texto y el helper lo colapsa. Se auto-aplica en carga y en `shown.bs.modal`; para filas inyectadas por AJAX, llamar `window.initRowActions()` tras insertarlas. Si un `.row-action` no tiene ícono, conserva su texto.

**Nombres que NO son de persona — `data-nombre-libre` (2026-09-14):** el validador global aplica la
regla de nombre de persona (**solo letras y espacios**) a todo input cuyo `name`/`id` contenga
`nombre` o `apellido`. Eso rompía en silencio los catálogos cuyo "nombre" es una **denominación**:
`horarios` («OAC Matutino (7:00am–12:00pm)»), `departamentos` («Relaciones Inter-Institucionales»),
`ubicaciones`, `feriados` («Santa Inés (Cumaná)») y `nomina_grados` («Profesional / Licenciado»).
**11 registros reales no se podían editar y volver a guardar**: el formulario se negaba a enviarse y
el usuario solo veía «Hazlo coincidir con el formato solicitado» — y los propios *placeholders* de
esos formularios sugerían valores que la regla rechazaba. Marcar el input con **`data-nombre-libre`**
lo exime del patrón y le aplica la capitalización de texto libre (`formatCapitalizar`, B5), que
respeta números y signos. Aplicado en esas 5 vistas. **Al crear un campo `nombre` que no sea el de
una persona, marcarlo.** El saneo del servidor (`sanitizePost`) no cambia.

**Teléfonos (convención global, automática):** todo `<input name="telefono">` (o `type="tel"`/id con "telefono") se transforma en **[select de prefijo VE] + [campo de 7 dígitos]** vía `initTelefonoInput` (`sigtur-validations.js`). Prefijos: **solo móviles** `0412/0414/0416/0424/0426` (los fijos no se muestran; si un registro legado trae otro prefijo, se agrega como opción al editar). El input original se oculta y conserva el valor combinado (`0XXX`+7 = 11 dígitos) para el POST — **no requiere cambios en controladores/modelos**. Valida exactamente 7 dígitos (`setCustomValidity` + `required` movido al campo visible). Sincroniza con autocompletado por cédula (intercepta asignaciones a `.value` con un descriptor). Se auto-aplica en carga y en `shown.bs.modal`.

**Edad / fecha de nacimiento (convención global):** cualquier `<input type="date" class="js-edad">` muestra la edad calculada en vivo y valida el rango con `data-edad-min` / `data-edad-max` (años). Opcional `data-edad-target="idElemento"` para escribir la edad en un elemento existente (si no, crea un `<small>` debajo). Aplica restricciones nativas `min`/`max` al datepicker y `setCustomValidity`. El helper (`initEdadInput` + `sigturEdad`) vive en `sigtur-validations.js` y se auto-conecta en carga y en `shown.bs.modal`; para filas dinámicas, llamar `initSigturValidations()` tras insertarlas. El rango puede ajustarse en vivo: cambiar `data-edad-min/max` y disparar `input.dispatchEvent(new Event('edad:refresh'))`. Ejemplos: empleado `data-edad-min="18"` con `data-edad-max` **dinámico 65↔70** según comisión de servicio (`wzAjustarEdadMax()` en `form.php`; el servidor valida lo mismo en `EmpleadosController`: comisión 18–70, no comisión 18–65); participantes libres (niños) 5–11 **en talleres** (RN-F16). **En rutas ya no**: desde la mig. 079 el rango sale de la ruta (`Ruta::motivoEdadNoValida()`), y la vista lo inyecta al JS por salida. Carga familiar usa `js-edad` sin min/max (solo muestra edad).

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

18a. **Cédula solo dígitos + anti-duplicado de participantes (migración 037)** — La cédula se guarda y valida **solo con números, máx. 8** (regla global en `sigtur-validations.js`; excepción: campos `*_libre` = ID escolar/extranjeros, alfanuméricos). `TalleresController`/`RutasController` normalizan la cédula a dígitos antes de buscar/crear en `personas` (evita personas duplicadas por formato). **Anti-duplicado en la misma actividad:** personas (con cédula) ya estaban cubiertas (`Taller::estaInscrito`, check en `Ruta::inscribir`); para participantes **sin cédula (libre)** se agregó `Taller::estaInscritoLibre()`/`Ruta::estaInscritoLibre()` (mismo nombre+apellido+fecha nac, o misma `cedula_libre`) → bloquea registrar dos veces al mismo niño/a. **Control de registros basura:** reporte `reportes/duplicados` (`ReportesController::duplicados()`, roles 1/3) que agrupa posibles duplicados: personas con cédula repetida, personas con mismo nombre+apellido+fnac, y participantes libre repetidos entre talleres y rutas. Los participantes sin cédula NO tienen clave única → el sistema solo señala coincidencias para revisión humana (desambiguar con representante/docente, parroquia, género).

18b. **Ancla por representante para menores sin cédula (migración 038)** — Decisión de negocio: un niño/a sin cédula se identifica por su **representante** (adulto con cédula = identificador estable). En el flujo libre, el **representante (nombre + cédula) es OBLIGATORIO**: talleres lo guarda en `nombre_docente`/`cedula_docente` (relabeled "Representante / Docente"), rutas en `nombre_representante`/`cedula_representante` (mig.038). La cédula del representante se normaliza a dígitos (6–8) en `TalleresController::store()/actualizarParticipante()` y `RutasController::inscribir()`. El reporte `reportes/duplicados` agrupa los libre por nombre+apellido+fnac **+ cédula del representante**: así dos homónimos con representantes distintos no se marcan como duplicados, y la misma persona (mismo representante) en varias actividades sí se detecta. El bloqueo dentro de la misma actividad sigue por nombre+apellido+fnac / `cedula_libre` (`estaInscritoLibre`).

18c. **Egreso / desincorporación de empleados (migración 036, R-12)** — dar de baja a un trabajador (renuncia, despido, jubilación, fin de contrato, fallecimiento, otro) **NO borra** el registro: lo marca como egresado (`empleados.fecha_egreso` + `motivo_egreso` + `observacion_egreso`), manteniéndolo `is_active=TRUE` como **histórico consultable** (sale de la nómina activa pero sigue disponible para constancias y tiempo de servicio). `is_active=FALSE` (`delete()`) queda reservado para registros creados por error (papelera). `Empleado::all()`/`facilitadoresTalleres()` filtran `fecha_egreso IS NULL`; `Empleado::egresados()` lista el histórico; `procesarEgreso()`/`reingresar()` (transaccionales + auditados) usan la tabla `empleados_egresos` (historial; índice único parcial `uq_emp_egreso_abierto` impide dos egresos abiertos). **Reingreso con historial**: al reingresar se cierra la fila (`fecha_reingreso`) y se limpia el egreso vigente. `Empleado::tiempoServicio($ingreso,$egreso)` → "X años, Y meses" (hasta egreso o hasta hoy), embebido en la **constancia** (redacción en pasado si egresado). Enum `Empleado::MOTIVOS_EGRESO`. UI: pestañas Activos/Egresados en `empleados/index` (`?ver=egresados`), modal "Procesar egreso"/"Reingreso" en index y expediente, banner + tiempo de servicio + historial en `empleados/detalle`. Controlador: `egresar()`/`reingresar()` (POST, validan fecha ≥ ingreso y no futura).

18d. **Constancias de trabajo (migración 034, R-10)** — dentro del módulo Empleados. **Multi-tipo (B13):** `Constancia::TIPOS` (clave→etiqueta) = `trabajo`/`bancaria`(sin monto, espacio en blanco)/`horario`/`funciones`/`antiguedad`/`egreso`; la **clave** se guarda en `constancias.tipo` y `Constancia::labelTipo()` la traduce. `EmpleadosController::generarConstancia($id, $tipo='trabajo')` valida el tipo; la vista imprimible `constancia.php` adapta título y cuerpo por tipo (horario usa `horarios`+grupo; funciones usa cargo+nivel_jerárquico; egreso usa motivo/fechas). El expediente ofrece un **dropdown** de tipos (egreso solo si egresado; bancaria/horario solo si activo) y muestra un **badge de estatus** (Activo/Egresado·motivo/En permiso·tipo/En reposo·tipo, vía `PermisoLaboral::vigenteHoy()`) + tiempo de servicio. **No exige antigüedad mínima.** `Constancia::crear($idEmpleado, $tipo)` genera correlativo `CONST-` + `ConfigSistema::generarNumeroOficio('constancia')` → `CONST-NNN/AAAA` (claves `correlativo_oficio_constancia`/`ano_correlativo_constancia` sembradas en 034). `EmpleadosController::generarConstancia($id)` (crea + redirige a imprimible), `constancia($idConst)` (vista imprimible `empleados/constancia.php`, carta institucional con firmante de ConfigSistema), `eliminarConstancia()`. Historial en la sección "Constancias / Documentos generados" del expediente. RIF en la constancia = G-20008498-7 (igual que la ficha; difiere de carta_aceptacion — unificar vía ConfigSistema).

18e. **Recaudos del expediente (migración 033, R-5)** — dentro del módulo Empleados (sin RBAC nuevo). `ExpedienteDocumento::RECAUDOS` = catálogo (clave→[etiqueta, obligatorio]); `recaudosEstado($id)` arma el checklist y cuenta faltantes obligatorios. Subida en `EmpleadosController::subirDocumento()` (valida PDF/JPG/PNG ≤5MB + MIME real; nombre `Tipo_Empleado_{id}_{ts}.ext`; guarda en **`storage/uploads/expedientes/`** fuera del web root; se sirve vía `DescargaController::expediente` — ver peculiaridad "Documentos privados"). Sección "Recaudos del Expediente" en `empleados/detalle.php` (estado entregado/falta, descarga, eliminar, aviso de faltantes). La Ficha Técnica generada (R-2) es un recaudo más del catálogo.

18f. **Carnetización (migración 053)** — carnets credencial **CR80 vertical (54×85.6mm), una sola cara**, imprimibles (HTML + `@media print` + `window.print()`, sin librería PDF; igual patrón que constancias). Diseño institucional con **colores del logo IMATUR** (navy `#16407A` / océano `#1C6FB0` / dorado `#F4B41A`). Muestra: tipo **TRABAJADOR/PASANTE**, subtipo **FIJO/CONTRATADO** (solo trabajadores), nombre, cédula, cargo, departamento (pasante: carrera, institución). **Sin** RIF, expediente, vigencia ni QR (decisión del cliente). **Foto**: `personas.foto_url`, una por persona (empleados y pasantes comparten `id_persona`); subida con el helper compartido `Controller::guardarFotoPersona($idPersona)` (jpg/png ≤5MB + MIME real) → `Persona::actualizarFoto()` → `storage/uploads/fotos/`; servida por `DescargaController::foto` (ver "Documentos privados"). Endpoints: `EmpleadosController::carnet($id)` / `subirFoto`, `PasantesController::carnet($id)` / `subirFoto`. Vistas: partial compartido `app/views/inc/carnet_card.php` (recibe `$carnet` normalizado) incluido por `empleados/carnet.php` y `pasantes/carnet.php` (standalone, sin header). UI: botón "Carnet" + miniatura/modal de foto en los detalles de empleado y pasante. Hay un `public/assets/libs/qrcode.min.js` vendorizado (offline) que quedó **sin usar** (por si se reactiva el QR).

18g. **Permisos y reposos (migración 032, R-8)** — `PermisosController` (rol 2 + sidebar RRHH) sobre `permisos_laborales`. `PermisoLaboral::CATEGORIAS` (Reposo/Permiso) + `TIPOS` (cascada categoría→tipo en la UI) + `ESTADOS` (Pendiente/Aprobado/Rechazado/Anulado). Reposo y Permiso se distinguen por `categoria` (select, D-RH32). El estatus **En curso/Concluido** se DERIVA de `fecha_fin` vs hoy (no se almacena); `dias_solicitados` se calcula del rango; `duracion` es texto libre ("72 horas"/"6 meses"). Flujo: registrar (Pendiente) → aprobar/rechazar/anular. **Vacaciones NO incluido** (fórmula pendiente — D-RH04/05/NEW05). `tipo_permiso` CHECK = Reposo médico/Médico familiar/Diligencia/Duelo/Maternidad-Paternidad/Personal/Estudios/Otro.

18h. **Faltas y amonestaciones (migración 031, R-9)** — `AmonestacionesController` (rol 2 + sidebar RRHH): roster de empleados con conteo de `faltas` y `amonestaciones` activas + semáforo (`Amonestacion::roster()`), y detalle por empleado (`empleado($id)`). RRHH registra ambas manualmente (el sistema solo cuenta/notifica, D-RH28). `Amonestacion::LIMITE_DESPIDO = 3` → a las 3 amonestaciones activas se muestra "Causa de despido" (aplica a Contratado). Las `faltas` injustificadas son distintas de los permisos/ausencias justificadas (R-8, pendiente). Modelos `Falta`/`Amonestacion` (porEmpleado/save/delete, auditados).

18i. **Registro de empleado = asistente multi-paso (migración 030, R-2b)** — el alta/edición de empleado NO usa modal: es un wizard de página completa `empleados/form.php` (5 pasos: personales → formación → institucionales → carga familiar → resumen), servido por `EmpleadosController::nuevo()` y `editar($id)`, posteado a `store()`. Persiste el borrador en `localStorage` (solo alta), valida por paso, y muestra resumen antes de guardar. La carga familiar se recolecta en arrays `cf_nombre[]/cf_cedula[]/cf_fnac[]/cf_parentesco[]` e inserta tras crear la persona (`guardarCargaFamiliarInicial()`); en edición enlaza al expediente. Campos nuevos: `personas.centro_votacion/consejo_comunal/comuna`, `empleados.uniforme/talla_camisa/talla_pantalon/talla_zapato` (uniforme solo se registra, D-RH35). `Empleado::getId()/getIdPersona()` exponen los IDs tras `save()`.

18j. **Asistencia: puntualidad y ausentismo (migración 029)** — al marcar entrada, `AsistenciasController::marcar()` calcula `asistencias.minutos_tarde` vía `Asistencia::calcularMinutosTarde()` (hora real − hora del horario asignado); impuntual si `minutos_tarde > minutos_tolerancia_puntualidad` (config, default 15, editable en `/config`). `Asistencia::empleadosEnActividad($fecha)` detecta empleados en ruta (`rutas.fecha_visita` + `participantes_ruta`) o formación externa (`talleres.es_interna=FALSE` + rango fechas + `participantes_taller`) por `id_persona` → no cuentan como ausentes (RN-RH15). El index muestra resumen del día (activos/presentes/impuntuales/en actividad/ausentes) + horas trabajadas (derivadas, solo reporte; NO afectan pago). Sin horario asignado → `minutos_tarde` NULL ("sin horario").

18k. **Horarios y grupos (migración 028)** — `horarios` tiene CRUD (`HorariosController` + modelo `Horario` + `horarios/index.php`), accesible RRHH/Admin (sidebar bajo RRHH). Seed de modalidades: Estándar 08–14, OAC Matutino 07–12, OAC Vespertino 10–14, Servicios Generales 08–14 (rotación A/B). `empleados.grupo_rotacion` (A/B, `Empleado::GRUPOS_ROTACION`) solo para Servicios Generales. Config `minutos_tolerancia_puntualidad` (default 15) preparada para R-7 (puntualidad). `EmpleadosController` usa `Horario::all()` (ya no query inline).

18l. **`departamentos` jerárquico (migración 027)** — `id_padre` (auto-FK, ON DELETE SET NULL) + `tipo_unidad` ∈ Presidencia/Junta Directiva/Dirección/Coordinación/Oficina/Unidad. Estructura oficial sembrada (Presidencia → 3 Direcciones [Planificación y Gestión Turística, Administración, Talento Humano] → Coordinaciones + unidades staff). Enum en `Departamento::TIPOS_UNIDAD`; `Departamento::all()/find()` traen `padre` (nombre) y ordenan por jerarquía. El liderazgo Director/Coordinador se **deriva del cargo** del empleado (cargos `Director`/`Coordinador`/`Presidenta`), no hay campo responsable. Ver `MODELO_NEGOCIO_RRHH.md` 7.1 (D-RH30). **Listado jerárquico (U1):** `Departamento::arbol()` devuelve el recorrido en profundidad (cada unidad seguida de sus subunidades, mayor→menor nivel) con `->nivel` para indentar; lo usa `departamentos/index` (DFS, ordena por `ORDEN_TIPO`+nombre, huérfanos como raíces). Cargos se indenta visualmente por `Cargo::ORDEN_NIVEL` (escalera Presidencia→Adscrito). `Departamento::all()` (plano, ordenado) sigue para los selects de otros módulos.

18m. **Ficha Técnica del Trabajador (migración 026)** — `Empleado::find()` trae nombres por LEFT JOIN (cargo, departamento, parroquia, horario) y `Empleado::all()` incluye los campos extra de `personas` para el modal de edición. Enums `Empleado::CLASIFICACIONES` (Empleado/Obrero), `ESTADOS_CIVILES`, `NIVELES_ACADEMICOS`. Tablas hijas claveadas por `id_persona` con modelos `CargaFamiliar`/`CursoRealizado`/`ExperienciaLaboral` (métodos `porPersona/save/delete`, auditados). Expediente en `EmpleadosController::detalle($id)`; documento imprimible en `fichaTecnica($id)` → `empleados/ficha_tecnica.php`. **RIF institucional en la ficha = G-20008498-7** (según el formato físico; difiere del usado en `pasantes/carta_aceptacion.php` G-20009499-7 — discrepancia a unificar, idealmente vía `ConfigSistema`).

18n. **Bono Vacacional v1 — "registro + reporte", no cálculo legal (migración 059, R-11)** — el sistema NO calcula el monto legal del bono vacacional ni de una futura liquidación: **organiza** datos que Talento Humano ya captura (igual que hoy en Excel) y los exporta en el formato exacto. `Sueldo::actual($idEmpleado, $fecha)` resuelve el sueldo vigente en una fecha (última fila de `empleado_salarios` con `fecha_efectiva <= $fecha`) — **nunca UPDATE, siempre INSERT** (mismo patrón que `Empleado::trasladar()`), necesario para poder reconstruir el sueldo vigente en una fecha pasada. `BonoVacacional::tipoPersonal()` deriva la categoría (Alto Nivel/Empleados Fijos/Obreros Fijos/Contratados) de datos que YA existen (`cargos.nivel_jerarquico`, `tipo_contrato`, `clasificacion`) — sin captura adicional. Los días base por tipo (`bono_vac_dias_*` en `configuracion_sistema`, default 75/75/85/45) son un **beneficio de contrato colectivo superior a la LOTTT** (Art. 192: 15+1/año, tope 30) — no hardcodear ese número en código, es editable. `BonoVacacional::generarPeriodo()` congela un snapshot (`bono_vacacional_detalle`) por empleado activo; `total_bono_vacacional` queda `NULL` hasta que RRHH lo captura/verifica en `/nomina/verPeriodo/{id}` — un período `Cerrado` bloquea más edición (`actualizarDetalle()` lanza excepción). No confundir con el módulo `Vacaciones` (mig.045, cuenta **días**): éste calcula el **monto** en Bs a pagar.

19. **`configuracion_sistema` correlativos por módulo** — claves `correlativo_oficio_ruta`/`ano_correlativo_ruta` (renombradas desde 007). `ConfigSistema::generarNumeroOficio($modulo)` acepta parámetro de módulo. Formato resultado: `RUTA-007/2026` o `FORM-001/2026`.

20. **`inventario.condicion` CHECK** — ahora incluye `'En Reparación'`. Actualizar whitelist en todos los controladores que validen este campo: `['Nuevo','Bueno','Regular','Dañado','En Reparación']`.

21. **`inventario.codigo_bn` nullable** — puede ser NULL para bienes pendientes de código BN oficial. Mostrar "—" en vistas cuando sea NULL. El código vive **por partes** y `codigo_bn` es el compuesto que arma `Inventario::componerCodigo()`; escribirlo siempre por ahí, nunca a mano. ⚠️ **2026-09-02:** con el procedimiento nuevo el código lo asigna **IMATUR** (secuencia propia) y `verificado_alcaldia` deja de ser sinónimo de «codificado» — leer `docs/PLAN_MODULO_BIENES.md` §2-ter antes de tocar `codificar()`.

21b. **Desincorporar un bien ≠ borrarlo** — desincorporar pone `estatus = 'Desincorporado'` (`Inventario::EST_BAJA`, mig. 076) **conservando `is_active = TRUE`**: el bien sale del inventario activo (`Inventario::fueraDeInventario()`, `ESTATUS_FUERA_DE_INVENTARIO`) pero su registro se preserva como aval (B-38). **`is_active = FALSE` es la papelera**, para registros creados por error. Para listar desincorporados usar **`Inventario::desincorporados()`**, nunca `is_active = FALSE` — confundirlos fue el bug **H-16** (el reporte de bajas medía la papelera: las desincorporaciones reales no salían y los borrados por error sí). Todas las consultas de inventario activo deben excluir ese estatus; **nunca escribirlo literal en SQL**, usar `EST_BAJA` como parámetro o `Inventario::sqlEstatusBaja()`.

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
#    (esquema base + migraciones 001-073 + catálogos + admin de arranque):
PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/schema_consolidado.sql

# 4. NO HAY PASO 4. No se aplica ninguna migración encima del consolidado.

# 5. Verificar config/config.php:
#    DB_HOST=localhost | DB_PORT=5432 | DB_NAME=SIGTUR-IMATUR
#    DB_USER=postgres  | DB_PASS=1234 (entorno Laragon)

# 6. URL: http://SIGTUR-IMATUR.test  o  http://localhost/SIGTUR-IMATUR/public
#    Login de arranque: admin / Sigtur2026  <-- CAMBIAR EN EL PRIMER INGRESO
```

> **Nota:** `database/schema_consolidado.sql` es autosuficiente (001–076). `database/migrations/` solo sirve como historial y para **actualizar** instalaciones antiguas, no para instalar desde cero. (`schema_completo.sql` y `schema.sql` fueron **eliminados** en 2026-08-04: cubrían hasta la 011 y el base original, y solo generaban confusión sobre cuál importar. Recuperables desde el historial de git si hicieran falta.)

---

## Documentación

**Cada hecho vive en una capa y una sola.** Si dos documentos se contradicen, manda el de la capa
correspondiente:

| Capa | Documento | Responde |
|---|---|---|
| **Estado** | `BACKLOG.md` | Qué falta y qué se espera del cliente. **Fuente única de los IDs** B-xx / R-xx / N-x / D-xx / H-xx |
| **Técnica** | `CLAUDE.md` (este) | Cómo funciona el sistema hoy |
| **Reglas** | `REGLAS_NEGOCIO_*.md`, `MODELO_NEGOCIO_RRHH.md` | Por qué el negocio funciona así |
| **Historia** | `CHANGELOG.md` + `git log` | Qué se hizo y por qué se decidió así |
| **Cliente** | `PREGUNTAS_CLIENTE.md` | Lo mismo que BACKLOG §3, en lenguaje llano y **por módulo** |
| **Material de origen** | `PREGUNTAS_DESCUBRIMIENTO_*.md`, `docs/formatos/` | Lo que el cliente dijo, sin interpretar |

> **Regla al escribir documentación:** una pregunta abierta se enuncia **solo** en `BACKLOG.md` §3.
> Los planes y las reglas la **referencian por ID**. Así una respuesta del cliente se anota en un
> único sitio.

| Archivo | Contenido |
|---------|--------|
| `docs/REGLAS_NEGOCIO_Formacion.md` | Talleres, Charlas, Inducciones |
| `docs/REGLAS_NEGOCIO_Rutas.md` | Rutas Turísticas |
| `docs/REGLAS_NEGOCIO_Pasantes.md` | Pasantes |
| `docs/REGLAS_NEGOCIO_RRHH.md` | Empleados, Asistencias, Permisos, Vacaciones (estado técnico + brechas) |
| `docs/MODELO_NEGOCIO_RRHH.md` | **Modelo de negocio RRHH** consolidado: horarios/grupos, tipos de empleado, expediente, carga familiar, permisos/reposos/vacaciones, organigrama. Su hoja de ruta R-1…R-12 está **cerrada** (§10) |
| `docs/REGLAS_NEGOCIO_Inventario.md` | Bienes e Inventario |
| `docs/REGLAS_NEGOCIO_Visitantes.md` | Visitantes y Control de Visitas |
| **`docs/PLAN_MODULO_NOMINA.md`** | **Modelo de cálculo de nómina y plan del módulo** (2026-08-07). Extraído de las fórmulas de la plantilla real (`INSTITUTO IMATUR JULIO 2026.xlsx`, datos de prueba / fórmulas reales) + audios de Talento Humano: porcentajes de prima de profesionalización por grado, escala de antigüedad con tope 30 %, transporte, hijos, deducciones, aportes patronales, alícuotas y bono de responsabilidad en divisas. Documenta que son **3 documentos** (bono vacacional · quincenal · liquidación), **5 tipos de personal** (falta Comisión de Servicio), que las primas **se derivan y no se capturan**, los 7 defectos de la plantilla del cliente y las fases N-A…N-E. **Leer antes de tocar `NominaController`, `BonoVacacional`, `Sueldo` o `empleado_salarios`.** |
| **`docs/PLAN_MODULO_BIENES.md`** | **Plan de reconstrucción del módulo de Bienes** (2026-08-04). Derivado del levantamiento con el cliente: análisis de brechas, cambios al modelo de datos, flujos (codificación · asignación · mantenimiento · baja · conteo por cambio de gestión), documentos a generar, catálogo de categorías propuesto, preguntas abiertas B-60…B-68 y fases 1-5. **§2-ter (2026-09-02) documenta el cambio de procedimiento** —IMATUR codifica, Acta de Desincorporación por lote— con las consecuencias C-1…C-7 y las preguntas B-73…B-80. **Leer antes de tocar Inventario.** |
| **`docs/PLAN_MODULO_RUTAS.md`** | **Plan de reconstrucción del módulo de Rutas** (2026-09-03). Derivado de las respuestas R-01…R-16: el módulo está construido sobre una premisa falsa — cada fila de `rutas` es *una salida*, pero **existe un catálogo reutilizable** (R-07/R-08). Documenta la separación `rutas` (catálogo) + `ruta_ejecuciones` (salida), el cobro real (R-02: sí se cobra), la aprobación de la Presidencia, la cancelación/reprogramación, el **choque del rango de edad 5–11 vs. 4–8** (H-17), las fases T-A…T-I y las preguntas abiertas. **Al 2026-09-17 están hechas T-A (mig. 078), T-B y T-I (mig. 079) y T-G (mig. 080)** — el catálogo está separado de las salidas, H-17 cerrado y la Ficha Institucional se genera sola al ejecutar una salida; siguen T-D (reprogramación en pantalla), T-E/T-H (oficios), T-C (cobro) y T-F. **Leer antes de tocar Rutas.** |
| `docs/PREGUNTAS_DESCUBRIMIENTO_Bienes_Rutas.md` | Cuestionario de descubrimiento de Bienes y Rutas (123 preguntas, redactadas desde cero). **Parte 1 (Bienes) respondida**; **Parte 2 (Rutas) respondida a medias (R-01…R-16 el 2026-09-03)** — el bloque que define la estructura de datos. R-14 quedó en blanco y R-17…R-64 siguen pendientes. |
| **`docs/CHANGELOG.md`** | **Historial** de lo construido, con el porqué de cada decisión (30 entradas desde 2026-06-21). Extraído de BACKLOG §2 el 2026-09-17. **No describe el estado actual.** |
| **`docs/PREGUNTAS_CLIENTE.md`** | **Lo que se le pide a IMATUR, organizado por módulo** e imprimible: qué funciona ya, qué falta para completar cada módulo y las preguntas numeradas. Espejo en lenguaje llano de `BACKLOG.md` §3. |
| **`docs/BACKLOG.md`** | **BACKLOG ÚNICO** — qué falta por hacer y decidir: estado por módulo, decisiones/insumos del cliente, preguntas abiertas, auditoría H-xx abierta, programación faltante. Consolida (y reemplaza) los antiguos REGISTRO_NEGOCIO/DECISIONES_PENDIENTES/preguntas/AUDITORIA_SENIOR/Notas/PLAN_ENTREGA |
| `docs/INDICADORES_GESTION.md` | **Todos los indicadores de gestión**: propósito, fórmula y fuente de datos (Dashboard + página RF30 + stats por reporte) |
| `docs/MANUAL_USUARIO.md` | **Manual de usuario por rol** (acceso/seguridad, interfaz, módulos, reportes, campana, búsqueda, perfil, FAQ) |
| `tests/run.php` | **Suite mínima de pruebas** sin dependencias (`php tests/run.php`): lógica pura sin BD (contraseñas, vacaciones, edad, tiempo de servicio) |

> **El número de migración aplicado no se anota aquí** — quedaba desactualizado en cada ciclo. Está en
> la cabecera de `BACKLOG.md` y, sin margen de error, en `database/migrations/`.

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
| Schema consolidado (instalar desde cero) | `database/schema_consolidado.sql` — **autosuficiente, 001-076 + catálogos + admin de arranque; no aplicar migraciones encima** |
| Backlog único / pendientes / decisiones | `docs/BACKLOG.md` |
| Historial de migraciones | `database/migrations/` — una por archivo; **no** se aplican sobre el consolidado |
