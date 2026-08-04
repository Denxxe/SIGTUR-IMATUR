# BACKLOG ÚNICO — SIGTUR-IMATUR

**Última actualización:** 2026-08-04 · **Migraciones aplicadas:** hasta **062** · **Rama:** `development_stage`

Documento **único** de seguimiento: qué falta por hacer y decidir. Consolida y reemplaza a
`REGISTRO_NEGOCIO.md`, `DECISIONES_PENDIENTES.md`, `preguntas_modelo_negocio.md`,
`AUDITORIA_SENIOR_2026-05-31.md`, `Notas.md` y `PLAN_ENTREGA.md`.

- **Referencia técnica:** `CLAUDE.md` (arquitectura, BD, convenciones, migraciones).
- **Reglas de negocio por módulo (detalle):** `REGLAS_NEGOCIO_*.md`, `MODELO_NEGOCIO_RRHH.md`, `ESTRUCTURA_ORGANIZATIVA.md`.
- **Indicadores:** `INDICADORES_GESTION.md`.
- **Preguntas para el cliente (imprimible):** `PREGUNTAS_CLIENTE.md` (espejo de la sección 3).

**Leyenda:** 🔴 bloquea BD/lógica · 🟡 alto impacto · 🟢 menor · ✅ hecho · 🔒 espera decisión/insumo del cliente · 🛠️ implementable ya

---

## 1. ESTADO GLOBAL

- **RRHH:** completo salvo **Liquidación de Prestaciones Sociales** (2da entrega). **Bono Vacacional v1 ✅** (registro + reporte, mig.059); Vacaciones (días) ✅; egreso/reingreso ✅; traslados ✅; disciplina ✅; constancias ✅.
- **Formación / Recepción:** CRUD y reglas operativas completos. Quedan preguntas de impacto medio/bajo.
- **Inventario (Bienes):** 🔄 **En replanteamiento.** El levantamiento del 2026-08-04 (59 preguntas respondidas) reveló que lo construido es un CRUD genérico, mientras que el instituto necesita un **expediente administrativo por bien** con ciclo de vida gobernado por la Alcaldía (codificación, actas, oficios). Plan por fases en `docs/PLAN_MODULO_BIENES.md`.
- **Turismo (Rutas):** cuestionario de descubrimiento **pendiente de responder** (Parte 2 de `PREGUNTAS_DESCUBRIMIENTO_Bienes_Rutas.md`). Prioridad: R-07/R-08 (catálogo vs ejecución) — de esa respuesta depende si hay rediseño.
- **Cuello de botella de la entrega:** ya **no es código**, son **decisiones/insumos del cliente** (sección 3).

---

## 2. LO RESUELTO EN ESTE CICLO

### 2026-08-04 — Instalación desde cero reparada: `schema_consolidado.sql` autosuficiente (sin migración)

| # | Mejora | Detalle |
|---|--------|---------|
| ✅ 🔴 Despliegue | **El consolidado quedaba 36 migraciones atrás** | `database/schema_consolidado.sql` cubría hasta la **023**, el README mandaba aplicar "024 a 052" y `CLAUDE.md` decía "024 a 039" — pero existen hasta la **059**. Cualquier instalación nueva hecha siguiendo la documentación quedaba **sin las migraciones 053–059**: foto de carnet, auditoría de login, alertas vistas, tolerancia de salida temprana, recuperación de contraseña y **todo el módulo de Nómina**. Fallo silencioso: la BD se creaba sin error y el sistema reventaba al usar esos módulos. |
| ✅ | **Regenerado desde la BD viva, autosuficiente (001–060)** | `pg_dump --no-owner --no-privileges` + `--exclude-table-data` sobre las 42 tablas operativas. Instalar = importar **un solo archivo**, sin migraciones encima. `database/migrations/` queda como historial y para actualizar instalaciones antiguas. |
| ✅ | **Catálogos institucionales sembrados** | `roles`, `permisos_rol`, `configuracion_sistema`, `departamentos` (organigrama oficial, 23), `cargos`, `horarios`, `feriados`, `municipio`, `parroquia`. Vacías las operativas (personal, inventario, talleres, rutas, visitantes, pasantes, asistencias, constancias, nómina, bitácora) y **correlativos de oficios reiniciados a 0** (antes el dump los habría dejado en constancia=17, ruta=3). |
| ✅ | **Usuario administrador de arranque** | Hueco anterior no detectado: el consolidado **no incluía ningún usuario**, y como `usuarios.id_empleado` es `NOT NULL`, una instalación nueva **no tenía forma de iniciar sesión**. Ahora un bloque `DO $bootstrap$` crea persona + empleado técnico + `admin`/`Sigtur2026` (idempotente). ⚠️ Contraseña pública en el repo — cambiar al primer ingreso. |
| ✅ | **Verificado, no asumido** | Cargado en una base vacía (`ON_ERROR_STOP=1`): **49 tablas, 0 errores**, hash bcrypt validado con `password_verify`, secuencias sin colisión. Dos fallos reales encontrados y corregidos en el proceso: (1) las columnas de auditoría `*_by` de los seeds referenciaban `usuarios.id` inexistentes; las **NOT NULL** (`municipio.created_by/updated_by`, `parroquia.create_by/update_by`) obligan a que el admin exista **antes**, así que el bloque de arranque va **entre** los datos de `departamentos` y los de `municipio`; (2) el FK circular de `departamentos.id_padre` impedía usar `--data-only` (hay que usar dump completo, que pone las constraints después de los datos). |
| ✅ Docs | **README.md + `docs/CLAUDE.md` corregidos** | Se eliminó el paso "aplicar migraciones 024–0xx" de ambos, se documentó el login de arranque y se dejó una nota de **cómo regenerar el consolidado** sin repetir los dos fallos de arriba. |

### 2026-08-04 — Bienes, Fase 1 construida (mig. 062) — **cierra H-04**

Primera fase del plan (`docs/PLAN_MODULO_BIENES.md` §10). El módulo deja de ser un CRUD de bienes.

| # | Entregable | Detalle |
|---|-----------|---------|
| ✅ 🔴 **H-04 CERRADO** | **`estatus` separado de `condicion`** | Era el origen del bug: ambos ejes vivían en la misma columna. Ahora `estatus` = situación administrativa (En espera de codificación · Activo · En mantenimiento · Extraviado · Robado · Dado de baja) y `condicion` = estado físico (Nuevo/Bueno/Regular/Dañado). Con el criterio del cliente: **en mantenimiento el bien NO desaparece** (B-34) y **dado de baja SÍ sale** del inventario activo conservando su registro (B-38). |
| ✅ | **Flujo de codificación contra el BM-1** | El bien nace **sin código**, en estatus "En espera de codificación". `Inventario::codificar()` transcribe grupo/subgrupo/sección + N° de orden cuando la Alcaldía devuelve el BM-1, y lo pasa a Activo. `componerCodigo()` arma `2-01-108-084`; valida partes completas y N° de orden único. Pestaña "Sin codificar" con contador en el listado. |
| ✅ | **Dos ejes de clasificación** | Código oficial (Alcaldía) **y** categoría interna (reportes de Presidencia). Se sembraron **11 categorías** y se retiraron las 2 de prueba ("Inmobiliario", "Inmuebles"). El BM-1 demostró que el código no clasifica: sillas, mesas, aire acondicionado y router comparten `2-01-108`. |
| ✅ | **Adquisición y responsable** | `origen` (Compra/Donación, con donante obligatorio si es donación), `costo_adquisicion`, `fecha_adquisicion`, `proveedor`, `tiene_garantia`+`garantia_vence`, `id_responsable` (FK empleados, **único** — B-26/27) y `foto_url`. Cierra D-IN06 y D-IN09. |
| ✅ | **Sedes y depósito** | `ubicaciones` +`sede` (Sede Principal y Oficina del Aeropuerto — B-24) +`es_deposito` (área común de los bienes sin asignar — B-23/25). |
| ✅ | **Reportes y alertas alineados** | Se corrigieron **8 consultas** en `DashboardController`, `ReportesController` y `CentroAlertas` que seguían filtrando por la condición `'En Reparación'` (ya inexistente) y que **contaban los dados de baja como activos**. El reporte de inventario suma columnas Estatus y Responsable. |
| ✅ | **Verificado con pruebas reales** | 19 comprobaciones sobre la BD ejercitando el ciclo completo: alta sin código → pendiente → codificación → duplicado rechazado → código incompleto rechazado → mantenimiento (sigue visible) → baja (desaparece del activo, se conserva). Se detectaron y corrigieron 5 warnings de PHP (`?:` sobre claves inexistentes) que habrían llenado el log en producción. Consolidado regenerado y reinstalado en BD vacía. |

> **Pendiente de la Fase 1:** `tipo_bien`/`cantidad` (mig. 044) quedaron sin uso pero **no se eliminaron** — esperan la confirmación del cliente (**B-66**). Siguen con DEFAULT, así que nada se rompe.

### 2026-08-04 — Levantamiento del módulo de Bienes + cuestionario de descubrimiento (sin migración)

| # | Entregable | Detalle |
|---|-----------|---------|
| ✅ Docs | **`docs/PREGUNTAS_DESCUBRIMIENTO_Bienes_Rutas.md`** — 123 preguntas (59 Bienes + 64 Rutas) | Redactadas **desde cero, como si el sistema no existiera**, para que el cliente describa su realidad sin quedar anclado a lo ya construido. Cuatro niveles de prioridad (⭐ define BD · ▲ afecta pantallas · ○ complementaria · 💡 propuesta nuestra), lista de 15 formatos físicos a pedir, y un anexo interno de contraste contra lo implementado. |
| ✅ Cliente | **Parte 1 (Bienes) respondida completa** | Las 59 respuestas quedaron en el propio documento. |
| ✅ Análisis | **`docs/PLAN_MODULO_BIENES.md`** — plan de reconstrucción por fases | Lo construido es un **CRUD genérico**; lo que el instituto necesita es un **expediente administrativo por bien**. Cinco diferencias de fondo: el bien nace **sin** código (lo asigna la Alcaldía tras una inspección solicitada por oficio), el código es **estructurado** (`grupo-subgrupo-sección-cantidad-N° de orden`), la baja es un **acto administrativo** firmado por Coordinadora de Bienes + Presidencia, cada bien acumula **documentos** (factura, informe, oficios), y **todo movimiento lo autoriza** la Coordinadora de Bienes. |
| ✅ Diseño | **`estatus` (administrativo) separado de `condicion` (físico)** | Origen del bug H-04: hoy se mezclan. Nuevos estatus: En espera de codificación · Activo · En mantenimiento · Extraviado · Robado · Dado de baja. **Criterio del cliente ya definido:** en mantenimiento **no desaparece** (B-34); dado de baja **sí sale** del inventario activo (B-38). H-04 se corrige en la Fase 2. |
| ⚠️ Hallazgo | **La migración 044 quedó contradicha** | `tipo_bien` (Durable/Fungible) y `cantidad` se implementaron respondiendo a D-IN05. Ahora B-07 dice que **no llevan consumibles** y B-09 que el registro es **individual** aunque se compre en lote. Ambas columnas sobran → confirmar con **B-66** antes de eliminarlas. |
| ⚠️ Hallazgo | **D-IN11 (stock mínimo) estaba mal planteada** | No es stock de papelería: no llevan consumibles. Lo que piden es un umbral de **suficiencia de mobiliario** (sillas por empleado, mesas por departamento). Replanteada como **B-63**. |
| ⚠️ Hallazgo | **Dos sedes, no una** | Además de la Sede Principal, la **Oficina de Información Turística del Aeropuerto de Cumaná** tiene bienes que también se controlan (B-24). `ubicaciones` no contempla sedes. |
| ✅ Docs | **9 preguntas nuevas (B-60…B-68)** | Las dos bloqueantes: el **catálogo oficial de grupos/subgrupos/secciones** de la Alcaldía y **3 ejemplos reales de código BN**. Más el **oficio de codificación**, que es el formato más urgente (automatizarlo ataca el dolor #1 declarado por el cliente). |
| ⏳ Pendiente | **Parte 2 (Rutas) sin responder** | Prioridad: **R-07/R-08** — si el cliente espera un catálogo de rutas reutilizable en vez de una fila por ejecución, el módulo necesita **rediseño**, no ajustes. |

### 2026-08-04 — Carnet institucional rediseñado según el modelo físico (mig. 061)

El cliente entregó el **carnet físico vigente**. Se rehízo `app/views/inc/carnet_card.php` para reproducirlo.

| # | Cambio | Detalle |
|---|--------|---------|
| ✅ 🔴 Datos | **Teléfono y correo del sistema estaban equivocados** | El carnet real trae `0293-4310178` y `Sucreimatur@gmail.com`; el sistema tenía `(0293) 431-4073` e `imatur.cumana@gmail.com`. **No eran variantes de formato, eran datos distintos.** Corregidos en `configuracion_sistema` (mig. 061). ⚠️ **El correo institucional es el remitente de la recuperación de contraseña** y aparece en constancias/oficios — las credenciales SMTP que falten (BACKLOG §3.0) deben ser de **esa** cuenta. |
| ✅ | **Dirección y lema ahora configurables** | Claves nuevas `direccion_institucion` y `lema_institucion` ("Historia y Porvenir"), editables en `/config` → Contacto Institucional. No quedaron fijas en el código. |
| ✅ | **Diseño alineado al carnet real** | Logo de la Alcaldía arriba-izquierda; "IMATUR" grande con perfilado blanco y RIF debajo; **unidad de adscripción en vertical** sobre el margen izquierdo (tamaño de fuente automático según largo); foto **circular con aro dorado**; apellidos y nombres en líneas separadas alineados a la derecha; cédula con separadores de miles; contacto con iconos circulares al pie; lema sobre la franja inferior. |
| ✅ | **Tipo de credencial conservado** (decisión del cliente) | El modelo físico no los trae, pero se mantienen: insignia **TRABAJADOR/PASANTE** + **FIJO/CONTRATADO**, integradas al bloque de identidad en vez de centradas como antes. |
| ✅ | **Pasantes: institución en vertical** | Donde el trabajador lleva su departamento, el pasante lleva su **institución educativa** (decisión del cliente). Antes mostraba Carrera + Institución como líneas de datos. |
| ⏳ | **Falta el arte del fondo** | El degradado, la marca de agua y la foto de Cumaná al pie **todavía no los tenemos**. Se aproximan con CSS. Está preparado para incorporarlo sin tocar código: basta dejar el archivo en `public/assets/images/carnet_fondo.png` y la vista lo detecta (`is_file`) y sustituye el degradado. |
| ✅ | **Verificado** | Renderizado real contra la BD (empleado y pasante), no solo `php -l`. Se corrigió un fallo detectado al probar: la cédula se formateaba con `number_format((int)…)`, que **descartaba los ceros a la izquierda** (`00123456` → `123.456`); ahora se agrupa sobre la cadena. Probado con 7 casos incluidos cédula vacía y ya formateada. |

### 2026-08-04 — Limpieza de columnas y tablas inertes (mig. 060) — cierra H-09 y H-10

Auditoría: estas estructuras existían en la BD pero **ninguna parte del sistema las escribía**. Eran peso muerto y, en un caso, hacían que un reporte mostrara datos falsos. Decisión del cliente: eliminarlas.

| # | Eliminado | Por qué |
|---|-----------|---------|
| ✅ | `rutas.nombre_facilitador_externo` | Solo se **leía** en el reporte de Rutas (`ReportesController::rutas`), nunca se capturaba en ninguna pantalla → siempre NULL. Cierra **D-RT04**. |
| ✅ | `participantes_ruta.id_institucion` + tabla `instituciones_externas` | `RutasController` insertaba **siempre `null`**; la tabla quedó en 0 filas y sin UI desde que se retiró el módulo de instituciones externas (2026-05-31). Cierra **D-RT05** (el indicador CMI de "instituciones participantes" queda descartado). |
| ✅ | `talleres.id_oficio` + tabla `oficios` | Cero referencias en `TalleresController`, modelo `Taller` y vistas. `oficios` (oficios **recibidos**, externos → IMATUR) nunca tuvo CRUD; sus 2 únicas filas eran basura de prueba (asuntos `"klkkl"`, `"kjhgfd"`). Cierra **D-FO06**. |
| ⏸️ | `rutas.tiene_tarifa` / `tarifa_monto` | **NO se eliminó**: sigue pendiente de decisión del cliente (D-RT02). Ojo: hoy solo se lee, nunca se escribe → la columna "Tarifa" del reporte **siempre dice "Gratuita"**. O se implementa la captura o se quita del reporte. |

- **No confundir:** `oficios_emitidos` (oficios **salientes** generados desde rutas) sí está en uso y no se tocó.
- **Código ajustado:** `Ruta::inscribir()` pierde el parámetro `$id_institucion` (firma nueva: `(id_ruta, id_persona, user_id, observaciones)`), `Ruta::inscribirLibre()` y `RutasController` dejan de enviarlo, y el `COALESCE` del facilitador en `ReportesController` se simplifica.
- **Se conservaron a propósito** las etiquetas `'id_oficio'` (`auditoria/index.php`) e `'instituciones_externas'` (`dashboard/index.php`): son diccionarios de visualización de la **bitácora histórica**, no referencias vivas. Hay 18 registros de `audit_logs` cuyo JSON las menciona; sin la etiqueta se mostrarían con el nombre crudo de la columna. Ambas quedaron comentadas explicando esto.
- **Verificado:** migración aplicada (51 → 49 tablas), `php -l` en los 5 archivos tocados, los dos flujos de inscripción a ruta (con cédula y libre) probados con `INSERT` real + `ROLLBACK`, la consulta del reporte de Rutas ejecutada contra la BD migrada, y suite `php tests/run.php` 18/18 ✓. Consolidado **regenerado** y reinstalado desde cero en una BD vacía (49 tablas, 0 errores).
- **Limpieza extra:** se eliminaron `database/schema.sql` y `database/schema_completo.sql` (obsoletos: cubrían hasta la 011 y el base original; generaban dudas sobre cuál importar). Recuperables desde el historial de git.

### 2026-07-13 — UX: botón "Siguiente" del asistente de empleados sin feedback de error (sin migración)

| # | Mejora | Detalle |
|---|--------|---------|
| ✅ Fix UX | **Wizard de empleados**: "Siguiente" quedaba `disabled` sin explicar qué campo fallaba | `wzUpdateNav()` (`empleados/form.php`) ya no deshabilita `#wzNext`; se deja siempre clickeable para que `wzValidateStep()` pueda ejecutar `reportValidity()` sobre el primer campo inválido al hacer clic (globo nativo del navegador señalando el campo exacto). Antes, al estar `disabled`, el `onclick` nunca se disparaba y el usuario no tenía ninguna pista. |
| ✅ Fix UX | **RIF**: sin feedback visible mientras se escribía un valor mal formado | `initRifInput()` (`sigtur-validations.js`, se auto-adjunta a cualquier input con token `rif` en name/id) ahora inserta un `<small class="sig-rif-msg">` bajo el campo que muestra en rojo "RIF no válido. Formato: J-12345678-9." en vivo mientras se escribe, igual patrón que "Cédula disponible". Aplica automáticamente a los dos campos RIF del sistema (empleados y RIF institucional en `/config`). |

### 2026-07-11/12 — Bitácora inmutable, notificaciones, auditoría de reportes, recuperación de contraseña (mig. 054–058)

| # | Mejora | Detalle |
|---|--------|---------|
| ✅ Bitácora inmutable | **Asistencias y visitas ya NO son eliminables** | Se quitó el botón "Eliminar" (y el endpoint completo, no solo la UI) de `asistencias/index.php` y `visitantes/index.php`; reemplazado por "Ver detalles" (modal). Son bitácora/auditoría, no un CRUD editable. |
| ✅ Asistencia | **Motivo obligatorio si el empleado marca salida antes de su horario** (mig. 056) | Tolerancia configurable (`minutos_tolerancia_salida_temprana`, default 10 min), independiente de la tolerancia de puntualidad de entrada. Editable en `/config`. |
| ✅ Notificaciones | **Campana "tipo Facebook"**: alertas ya vistas no reaparecen (mig. 057) | `alertas_vistas` (fingerprint por usuario+clave de alerta). Reaparecen SOLO si cambia el conjunto de registros que las componen (ej. sube el número de contratos por vencer), nunca por simple paso del tiempo. |
| ✅ Empleados | **Listado principal**: badge de tipo de contrato (Fijo/Contratado/Suplente/Comisión), columna Contacto, filtro por Cargo, badge Grupo A/B (rotación) | `empleados/index.php` |
| ✅ Reportes/listados | **Auditoría completa (~18 hallazgos) cerrada**: Directorio de Personal (tel/correo/vencimiento), Amonestaciones (cédula/cargo/última fecha), Egresos (departamento/tiempo servicio), Constancias (cargo/depto/filtro tipo), Rutas (departamento/tarifa/guía externo/filtros), Visitantes (hora salida/atendido por), Pasantes (contacto/nota, + fechas en el listado), Bajas de Inventario (motivo), Inventario (filtros server-side) + bloque transversal (buscador/paginación en 6 reportes que no lo tenían + botón exportar en listados de tarjetas de Talleres/Rutas) | Ver detalle en `ReportesController.php` |
| ✅ Seguridad | **Recuperación de contraseña por correo** (autoservicio, mig. 058) | Token de un solo uso (30 min, hash sha256), PHPMailer vendoreado sin Composer (`app/libs/PHPMailer`). Remitente = correo institucional (`configuracion_sistema.correo_institucion`). **Pendiente:** credenciales SMTP reales (proveedor sin definir aún) — hoy el envío falla de forma controlada. |
| ✅ Seguridad | **Login acepta usuario o correo** | Resuelve "olvidé mi usuario" sin flujo aparte — si recuerda su correo, no necesita el username. |
| ✅ Seguridad | **Egreso desactiva automáticamente el acceso del empleado; reingreso lo reactiva** | Antes el usuario de acceso quedaba huérfano y activo indefinidamente tras un despido/renuncia — brecha confirmada y cerrada. `Empleado::procesarEgreso()`/`reingresar()`. |
| ✅ Fix | **Cédula sin normalizar en Visitantes/Pasantes/Búsqueda global** (mismo bug que rompió Talleres/Rutas días atrás) | `Visitante::buscarPorCedula/crear/store`, `Pasante::findPersonaByCedula/createPersona/updatePersona`, `BuscarController` ahora normalizan a solo-dígitos antes de buscar/guardar (mig. 037). Verificado con auditoría completa del sistema: patrón de JS que causó el bug original (script abortado por `getElementById` sin guarda) confirmado como caso aislado, no sistémico; RBAC/`permisos_rol` sin discrepancias. |
| ✅ Fix | **`CargaFamiliar`**: cédula normalizada + anti-duplicado **por empleado** (no global) | La misma cédula de familiar SÍ puede repetirse legítimamente entre empleados distintos (hermanos que declaran al mismo padre, cónyuges que ambos trabajan en la institución). Solo se bloquea el doble registro accidental del mismo familiar para el mismo empleado. |
| ✅ Migraciones | **054/055 aplicadas** (estaban pendientes desde hacía semanas) | 054: `audit_logs.operacion` acepta `LOGIN`/`LOGIN_FALLIDO` (antes fallaba en silencio, `/reportes/accesos` siempre vacío). 055: bitácora general exclusiva de Admin (0 filas afectadas, ya sin concesiones previas). |

### 2026-06-28/29 — Carnetización + UX (mig. 053)

| # | Mejora | Detalle |
|---|--------|---------|
| ✅ Carnetización | **Carnets CR80 imprimibles** (empleados y pasantes) | Formato credencial 54×85.6mm una cara, colores institucionales, `window.print()`. Foto por persona (`personas.foto_url`, mig.053) en `storage/uploads/fotos/`, servida por `DescargaController::foto`; subida con `Controller::guardarFotoPersona()` (MIME real). Partial compartido `inc/carnet_card.php`. Sin RIF/vigencia/QR por decisión del cliente (QR vendorizado queda disponible). |
| ✅ Dashboard | **Tarjeta "Pasantes (Visitas)"** (Recepción) + **KPI "Ausencias del mes"** (RRHH, tabla `faltas`, distinto de Impuntualidad) | `DashboardController` |
| ✅ UX | **Breadcrumb dinámico** en el header (Inicio / Grupo / Sección / Página) | `$___bcMap` en `header.php` |
| ✅ Docs | **`docs/PREGUNTAS_CLIENTE.md`** — lista consolidada de preguntas para el cliente (espejo de §3) | — |

### 2026-06-25 — Análisis profundo: Lote 5 (integridad) + Lote 6 (UX/a11y/README)

| # | Mejora | Detalle |
|---|--------|---------|
| ✅ Seguridad | **Anti-IDOR en borrados** | `eliminarFamiliar/Curso/Experiencia` validan pertenencia a la persona del empleado; `eliminarDocumento` valida `id_empleado`. |
| ✅ Verificación | **Transacciones** | Revisado: ya están aplicadas donde se requieren (`Empleado::save/egreso/reingreso/traslado`, Pasantes, Roles, ConfigSistema). Los demás guardados son de una sola sentencia (atómicos); `guardarCargaFamiliarInicial` es best-effort por diseño. **Sin cambios necesarios.** |
| ✅ UX | **Header móvil** | El buscador inline se oculta en <576px (queda campana/tema/perfil). |
| ✅ a11y | **Labels/aria** | `login` con `label[for]`+`autocomplete`; `aria-label` en campana y botón de tema. |
| ✅ Docs | **README.md** | Instalación, config (`config.example.php`), migraciones, crons (`schtasks`), restauración de respaldos, pruebas, estructura. |

### 2026-06-25 — Análisis profundo: Lote 2 (proteger uploads) + Lote 4 (cache de alertas)

| # | Mejora | Detalle |
|---|--------|---------|
| ✅ Confidencialidad | **Documentos privados fuera del web root** | Recaudos y docs de pasantes movidos a `storage/uploads/` (no accesibles por URL). Nuevo `DescargaController` sirve por **id de registro** con verificación de rol + `is_active` + `basename()` (sin path traversal). Vistas enlazan a `/descarga/...`. Archivos existentes migrados; valores antiguos siguen resolviéndose. |
| ✅ Seguridad | **Validación MIME en subida** | `EmpleadosController`/`PasantesController` validan extensión **y** `mime_content_type`. |
| ✅ Rendimiento | **Cache de alertas en sesión** | `CentroAlertas::resumenCacheado` (TTL 120s) usado por la campana del header; se invalida al abrir `reportes/alertas`. Evita recomputar roster/faltantes/config en cada página. |

> Residual: dos documentos de pasante quedaron en el **historial de git** (commiteados antes del `.gitignore`); se quitaron del tracking ahora. Si se requiere borrarlos del historial, hace falta reescritura (BFG/`git filter-repo`) — repo interno, prioridad baja.

### 2026-06-25 — Análisis profundo: Lote 1 (seguridad rápida) + Lote 3 (índices, mig. 052)

| # | Mejora | Detalle |
|---|--------|---------|
| ✅ Seguridad | **Errores de producción** | `public/index.php` con `display_errors` según `APP_DEBUG`, `log_errors` y `set_exception_handler` (página 500 limpia). `Database` ya no filtra el detalle del error de conexión. |
| ✅ Seguridad | **Cookie de sesión endurecida** | `httponly` + `samesite=Lax` (+ `secure` con HTTPS) antes de `session_start()`. |
| ✅ Seguridad | **Secretos fuera del repo** | `config/config.php` deja de versionarse (`.gitignore`); plantilla `config/config.example.php`. **Acción operativa:** cambiar la contraseña real de PostgreSQL. |
| ✅ Rendimiento | **Índices (mig. 052)** | 5 índices nuevos en tablas que crecen (participantes_ruta, actividad_inventario, personas/parroquia, audit_logs); verificado que no duplican los existentes. |

> Correcciones del análisis: el "SQL injection crítico en `Taller::actualizarPersona`" era **falso positivo** (claves de columna fijas en el controlador, no input). El "upload de PHP" está mitigado por whitelist de extensión (el riesgo real es de *fuga*, ver §5.2).

### 2026-06-25 — Respaldos automáticos de BD (sin migración)

| # | Mejora | Detalle |
|---|--------|---------|
| ✅ Continuidad | **Respaldo automático de la base de datos** (`cron/respaldo_bd.php`) | `pg_dump` (SQL plano) a `storage/backups/` con nombre fechado + **rotación** (conserva `BACKUP_RETENTION`=14). Carpeta fuera de `public/` y con `.gitignore`. `PG_DUMP_PATH`/`BACKUP_RETENTION` en config. Programable en el Programador de tareas de Windows; restaurar con `psql -f`. Probado: genera dump válido (92 CREATE/COPY). |

### 2026-06-25 — Calidad: pruebas, normalización de fin de línea y manual (sin migración)

| # | Mejora | Detalle |
|---|--------|---------|
| ✅ Pruebas | **Suite mínima sin dependencias** (`tests/run.php`, `php tests/run.php`) | 18 checks de lógica pura sin BD: política de contraseñas, vacaciones (derecho/antigüedad/acumulado), `Util::edad`, `Empleado::tiempoServicio`. |
| ✅ Repo | **`.gitattributes`** | Normaliza fin de línea a LF y marca binarios — elimina el ruido "LF will be replaced by CRLF". |
| ✅ Docs | **Manual de usuario por rol** (`docs/MANUAL_USUARIO.md`) | Guía práctica: acceso/seguridad, interfaz, roles, módulos, reportes, campana, búsqueda, perfil y FAQ. |

### 2026-06-25 — UX/seguridad: campana, búsqueda global, accesos, filtro de año (sin migración)

| # | Mejora | Detalle |
|---|--------|---------|
| ✅ Vista de accesos | **Reporte de accesos al sistema** (`reportes/accesos`, rol 1) | Inicios de sesión e intentos fallidos desde `audit_logs` (AuthController ahora registra `LOGIN`/`LOGIN_FALLIDO`); filtros usuario/tipo/fecha + export. |
| ✅ Centro de notificaciones | **Campana en el header** | `CentroAlertas::resumen($rol)` (fuente única, reusada por `reportes/alertas`); dropdown role-aware con badge de conteo accionable. |
| ✅ Filtro de período | **Selector de año en Indicadores** | `?anio=` gobierna los indicadores anuales del panel; métricas "del mes" y tendencias siguen relativas a hoy. |
| ✅ Búsqueda global | **Buscador en el header** (`BuscarController`) | Empleados / inventario / talleres / rutas / visitantes, **gated por rol**; acceso permitido a todo usuario autenticado en el Router. |

### 2026-06-25 — Bloque CMI de indicadores (sin migración)

Alineación del panel `reportes/indicadores` con el *Cuadro de Mando Integral* del documento del proyecto. 8 indicadores nuevos (prefijo `CMI-*` en `INDICADORES_GESTION.md`), solo lectura sobre datos existentes:

| # | Indicador | Fórmula |
|---|-----------|---------|
| ✅ CMI-RH01 | Cumplimiento de jornada | horas reales / programadas (mes, días con marcaje completo + horario) |
| ✅ CMI-RH02 | Precisión de asistencia | registros con salida / total (mes) |
| ✅ CMI-RH03 | Documentación del personal | empleados con recaudos obligatorios completos / total |
| ✅ CMI-I01 | Precisión del registro (inventario) | durables con código BN (+fungibles) / total |
| ✅ CMI-I02 | Movimientos de bienes | conteo por `tipo_movimiento` (año) |
| ✅ CMI-I03 | Asignación de responsables | durables con último movimiento = Asignación / total durables |
| ✅ CMI-F01 | Cobertura por parroquia | parroquias con actividad / total (año) |
| ✅ CMI-T01 | Frecuencia de rutas | rutas finalizadas por mes (6 meses) |

> Archivos: `ReportesController::indicadores()` + `views/reportes/indicadores.php`. Pendientes del documento que **no** se implementaron (ver 3.4 y 3.5): stock mínimo, instituciones participantes en rutas, tiempo de generación de reportes.

### 2026-06-25 — Endurecimiento de login + optimización N+1 (mig. 051)

| # | Mejora | Detalle |
|---|--------|---------|
| ✅ Seguridad | **Endurecer el login** | Bloqueo tras 5 intentos fallidos por 15 min (`usuarios.failed_attempts`/`locked_until`), política de contraseñas (mín. 8 + letra y número), mensaje genérico anti-enumeración, `session_regenerate_id`, expiración de sesión por inactividad (`SESSION_TIMEOUT`=30 min en el Router). |
| ✅ Rendimiento | **Optimizar N+1 de documentación** | `ExpedienteDocumento::faltantesObligatorios()` + `entregadosPorEmpleado()` (consultas agregadas) reemplazan el bucle `recaudosEstado()` por empleado en `indicadores()`, `alertas()` y `expedientesIncompletos()`. |

> Migración **051** (`usuarios_seguridad_login`): `+failed_attempts/locked_until/last_login`. Idempotente.

### 2026-06-25 — Bloque B (reportes implementables, sin migración)

| # | Reporte | Ruta · Roles |
|---|---------|--------------|
| ✅ BRH-07 | **Saldo de vacaciones** por empleado (años servicio, derecho, acumulado, ajuste, disfrutado, saldo) | `reportes/vacacionesSaldo` · 1,2 |
| ✅ D-RE01/02 | **Informe trimestral de Formación** (actividades/finalizadas/canceladas/inscritos/atendidos + género por trimestre, filtro por año) | `reportes/formacionTrimestral` · 1,3 |
| ✅ BRT-05 | **Ejecuciones de ruta** (rutas Finalizadas por fecha, participantes y atendidos; filtros año/tipo) | `reportes/ejecucionesRuta` · 1,3 |
| ✅ BVIS-05 | **Estadísticas de visitas** (afluencia por mes, visitantes únicos, situación del día) | `reportes/estadisticasVisitas` · 1,2 |
| ✅ BVIS-04 | **Visitas activas del día** en el Dashboard (`kpiVisitasActivas` = entradas de hoy sin salida) | Dashboard · 1,2,5 |

> Quedan del Bloque B: **formato físico imprimible de asistencia** (necesita el formato real del cliente, ver 5) y **`taller_facilitadores`** / **importación de históricos** (condicionados a decisión).

### 2026-06-21 (mig. 043–050)

| # | Entregable | Migración |
|---|-----------|-----------|
| ✅ | **Export Excel/PDF transversal** en todo listado `data-tabla-buscable` (`sigturExportarTabla`, opt-out `data-no-export`) | — |
| ✅ | **RIF institucional centralizado** en `ConfigSistema::rif()` + `window.SIGTUR_RIF` (oficial G-20008498-7) | 043 |
| ✅ | **Inventario Durable/Fungible** (`tipo_bien`+`cantidad`, validación por tipo) — cierra D-IN05 | 044 |
| ✅ | **Vacaciones (días)**: 15 hábiles +1/año tope 30, antigüedad total, feriados, saldo acumulado + ajuste inicial (`/vacaciones`) — cierra D-RH04/05 (parte de días) | 045/046 |
| ✅ | **3C** badge "Elegible a fijo" (señal visual, no promueve) | — |
| ✅ | **3D** Traslado de departamento = reasignación con historial (`empleado_traslados`) | 047 |
| ✅ | **3E** Faltas con `tipo` (injustificada/incumplimiento) + escalado falta→amonestación (`id_falta_origen`) | 048 |
| ✅ | **U4** Alertas de vencimiento: talleres vencidos (Dashboard + Centro de Alertas role-aware con contratos/pasantes) | — |
| ✅ | **O4** Filtro por departamento en lista de empleados · **O5** horario Estándar 8am-4pm→8am-2pm | 049 |
| ✅ | **3F** Limpieza: eliminados `taller_inventario` (D-FO07) y `es_brigadista` (D-FO08) | 050 |
| ✅ | **Fix UI:** `js-search` inflaba la altura dentro de `.sig-field` (flex-column) — corregido en CSS | — |

> Bloques 1 (revisión profesor) y 2 (UX) **cerrados**; la mayoría ya estaba hecho al verificar. Único pendiente real del Bloque 1: **B13** (ver 3).

---

## 3. DECISIONES / INSUMOS PENDIENTES DEL CLIENTE 🔒

Bloquean desarrollo. Cada una incluye **qué preguntar**.

### 3.0 🔴 Proveedor SMTP para recuperación de contraseña (2026-07-12)
- **Falta:** credenciales reales de un servidor de correo saliente (host/puerto/usuario/clave) para que la recuperación de contraseña por correo (ya implementada, mig. 058) pueda enviar correos de verdad.
- **Preguntar:** ¿usan Gmail/Google Workspace (contraseña de aplicación), un correo institucional propio (gobernación/alcaldía), u otro proveedor?
- **Al desbloquear:** completar `SMTP_HOST/PORT/USER/PASS/ENCRYPTION` en `config/config.php` (no requiere tocar código ni migraciones).

### 3.1 🟡 Nómina / Liquidación (R-11 · D-RH34/D-RH14) — Bono Vacacional ✅ (v1), resto pendiente
- **Hecho (2026-07-16, mig.059):** el cliente envió el formato oficial de **Bono Vacacional** (4 hojas por tipo de personal + resumen) que la Alcaldía exige. Se implementó v1 = **"registro + reporte"** (decisión del cliente): Talento Humano captura/verifica sueldo, primas y el total final (igual que hoy en Excel); el sistema organiza esos datos y exporta el `.xlsx` multi-hoja en el formato exacto. Nuevo: historial salarial por empleado (`empleado_salarios`, sección "Datos salariales" en el expediente), módulo `/nomina` (generar período, capturar/editar celdas, cerrar período, exportar), pantalla `/config` con los parámetros editables, y `XlsxMultiSheet` (escritor OOXML multi-hoja reusable, ver `CLAUDE.md`).

- **Estado de los 3 documentos de "nómina":**

  | Documento | Estado |
  |---|---|
  | **Bono Vacacional** (formato con 4 hojas por tipo de personal) | ✅ Recibido y ya montado en el sistema |
  | **Liquidación de Prestaciones Sociales** (`LIQUIDACION MES JULIO 2026.xls`) | ✅ Recibido, ⏳ pendiente de construir en el sistema (2ª entrega) |
  | **Nómina mensual regular** (el pago normal de sueldo que se le manda cada mes a la Alcaldía) | ❌ **No confirmado** — el cliente no estaba seguro de si lo envió. El archivo de Liquidación es para **egresos/prestaciones**, no es la planilla de pago mensual corriente. Hay que pedirlo aparte si existe. |

- **Lista de lo que necesitamos de RRHH (para dejar el módulo completo):**
  1. **Datos reales (no formato, sino los números):** sueldo básico y primas (profesional, responsabilidad, antigüedad, por hijo, transporte, FOND, discapacidad, caja de ahorro) de **cada empleado activo** — hoy la pantalla de captura existe pero está vacía. Número de cuenta bancaria de nómina de cada empleado.
  2. **Un ejemplo YA CALCULADO:** un mes de Bono Vacacional con montos reales de 2-3 empleados de distinto tipo (Alto Nivel, Empleado, Obrero, Contratado) — la plantilla enviada venía vacía, no se pudo verificar la fórmula del total.
  3. **Parámetros/reglas a confirmar:** ¿los días 75/75/85/45 son correctos y con tope máximo al sumar años? Monto vigente de Cesta Ticket (¿cambia mensual o por Unidad Tributaria?). Tasa BCV y "días adicionales" para los intereses de prestaciones — ¿de dónde los sacan hoy?
  4. **Formato faltante:** confirmar si existe un formato de **nómina mensual regular** distinto al de Liquidación y, si sí, pedirlo (igual que se hizo con Bono Vacacional).

- **Preguntar al cliente (bloquea automatizar el cálculo, hoy es todo captura manual):**
  1. ¿Nos pueden enviar un **mes de Bono Vacacional YA CALCULADO** (con montos reales de al menos 2-3 empleados de distinto tipo) para calibrar la fórmula exacta de la columna "FÓRMULA NUEVA DE BONO VACACIONAL + ALÍCUOTA"? La plantilla que enviaron estaba vacía — sin esto no se debe automatizar esa columna.
  2. Los días base 75 (Alto Nivel/Empleados Fijos) / 85 (Obreros Fijos) / 45 (Contratados) — ¿son correctos? ¿Hay un **tope máximo** al sumar años de servicio (como el tope 30 de la LOTTT), o crecen sin límite?
  3. ¿Cuál es el **monto actual de cesta ticket** y cada cuánto cambia (¿mensual, según Unidad Tributaria, otro)? Hoy quedó en 0 por defecto en Configuración.
  4. ¿Nos pueden confirmar si el `LIQUIDACION MES JULIO 2026.xls` que enviaron es también el formato de la **nómina mensual normal** que se le envía a la Alcaldía, o falta ese formato aparte? (No estaban seguros al enviarlo.)
  5. Para Liquidación: la tasa BCV mensual y los "días adicionales" (79→82/120→150 sobre 360) de la hoja `INTERESES` se ven tecleados a mano cada mes — ¿de dónde los saca hoy Talento Humano (tabla oficial, boletín BCV, otro)? Se necesita saber si hay una fuente publicada consultable o si siempre es carga manual.

- **Para tener el módulo COMPLETAMENTE funcional falta:**
  - [ ] Respuestas del cliente a las 5 preguntas de arriba.
  - [ ] Cargar el **sueldo/primas real** de cada empleado activo en "Datos salariales" (hoy solo tiene datos de prueba ya eliminados) — sin esto `/nomina` genera períodos con montos en 0.
  - [ ] Definir el **monto de cesta ticket** vigente en Configuración → Nómina.
  - [ ] Con el ejemplo real calibrado, automatizar el cálculo de "TOTAL BONO VACACIONAL" (hoy es captura manual por fila).
  - [ ] **Liquidación de Prestaciones Sociales** (2da entrega): tablas `liquidaciones` (snapshot al egreso, correlativo vía `ConfigSistema::generarNumeroOficio`) + `liquidacion_intereses_mensuales` (digitaliza la hoja `INTERESES` fila por fila, reusa `empleado_salarios` + `XlsxMultiSheet`); controlador/vistas; exportación de las 3 hojas (`FORMATO`/`INTERESES`/`INTERES DE MORA`).
  - [ ] Confirmar si falta un formato de **nómina mensual normal** (pregunta 4) y, si sí, pedirlo y construir un 3er sub-módulo.
  - [ ] Probar el flujo completo con datos reales de un mes cerrado y comparar el `.xlsx` exportado contra lo que el cliente realmente envía hoy a la Alcaldía, antes de usarlo en producción.

### 3.2 ✅ B13 — Mínimo de antigüedad para constancia — **DECIDIDO (2026-06-25): SIN mínimo**
- **Decisión del cliente:** **no** se exige antigüedad mínima para emitir constancias (se descarta el "mínimo 6 meses"). El mínimo de contrato ya se aclaró en otra sesión.
- **Acción:** ninguna — el sistema ya emite constancias sin exigir antigüedad (`Constancia::crear` no valida tiempo de servicio). B13 cerrado.

### 3.3 ✅ O1 — Cargos por departamento — **DECIDIDO (2026-06-25): cargos GENERALES**
- **Decisión del cliente:** los cargos son **transversales/generales** (no por departamento), tal como ya estaba implementado. El empleado tiene `id_cargo` e `id_departamento` independientes; un mismo catálogo de cargos sirve para todos los departamentos.
- **Acción:** ninguna. Se evaluó vincular cargo↔departamento (mig. tentativa 053) y se **descartó/revirtió** por esta decisión.

### 3.4 ✅ Inventario — **LEVANTAMIENTO COMPLETO (2026-08-04)**

El cliente respondió las **59 preguntas** del cuestionario de descubrimiento
(`docs/PREGUNTAS_DESCUBRIMIENTO_Bienes_Rutas.md`, Parte 1). El análisis y el plan de
reconstrucción por fases están en **`docs/PLAN_MODULO_BIENES.md`**.

**Preguntas históricas, ya resueltas por ese levantamiento:**

| ID | Respuesta del cliente |
|----|----------|
| ✅ D-IN06 | Responsable **nominal y único**: el director del departamento o, en su defecto, el coordinador (B-26/B-27). Al egresar un trabajador el bien **no lo sigue** — queda en el departamento y se reasigna (B-28). |
| ✅ D-IN10 (H-04) | **Mantenimiento**: el bien cambia a estatus "En mantenimiento", deja de estar disponible pero **NO desaparece** (B-34). **Baja**: **sí sale** del inventario activo, conservando el registro y el oficio como aval (B-38). |
| ✅ D-IN09 | **Sí**: costo, fecha de adquisición, proveedor y factura adjunta (B-16/B-17/B-19). También origen Compra/Donación con su oficio (B-18) y garantía con vencimiento (B-20). |
| ⚠️ D-IN11 | **Reinterpretada.** No hay consumibles: no llevan papelería ni material gastable (B-07/B-43/B-44). Lo que piden es un umbral de **suficiencia de mobiliario** (sillas por empleado, mesas por departamento) — distinto de un stock mínimo. Pendiente de definir → **B-63**. |
| ⚠️ D-IN03 | **No existe clasificación hoy** (todo cae en "Inmobiliario"). El cliente pidió una propuesta; hay una en §8 del plan. Pero el código de la Alcaldía es `grupo-subgrupo-sección-…`, o sea que **ya existe un catálogo oficial** que debería ser la fuente → **B-60**. |

**Formulario BM-1 recibido (2026-08-04)** — `docs/formatos/BM-1_inventario_bienes_muebles_alcaldia.jpeg`. **Desbloquea la Fase 1.**

Aclaración clave del cliente: el BM-1 **NO lo produce IMATUR**, es el registro consolidado que la **Alcaldía le devuelve** ya codificado. El circuito es: registro interno → informe de bienes nuevos a la Alcaldía → inspección → BM-1 de vuelta con los códigos → conciliación. El sistema hace las dos primeras piezas y **recibe** la tercera.

| ID | Estado |
|----|----------|
| ✅ B-60 | Catálogo de grupos/subgrupos/secciones: **ya no bloquea**. Los valores los asigna la Alcaldía e IMATUR solo los transcribe; bastan campos validados por formato. |
| ✅ B-61 | Ejemplos reales: `2-01-108` + N° de orden de 3 dígitos con ceros a la izquierda (`084`, `131`, `171`…). |
| ✅ B-62 | "Cantidad" es la cantidad de la fila y **siempre vale 1**; no forma parte del identificador. |
| 🔴 Hallazgo | **El código oficial no clasifica.** Sillas, mesas, pizarra, aire acondicionado y router comparten `2-01-108`. El catálogo de la Alcaldía **no distingue** equipo tecnológico de mobiliario → el sistema necesita **dos ejes**: código oficial (para la Alcaldía) + categoría interna (para los reportes de la Presidencia). |
| 🟡 B-69…B-72 | Nuevas: valores en "S/P" pese a que sí registran costo · cada cuánto llega el BM-1 · si existe versión digital (permitiría carga automática de códigos) · si los saltos en el N° de orden son bajas. |
| 🟡 B-63…B-68 | Umbral de mobiliario · cómo identificar a la Coordinadora de Bienes · sede del aeropuerto · confirmar eliminación de `tipo_bien`/`cantidad` (mig. 044) · destino del bien dado de baja · responsable derivado o manual. Ver §9 del plan. |
| 🔴 Formatos | **Informe de bienes nuevos** que IMATUR envía a la Alcaldía (el más urgente ahora), acta de baja, oficio de asignación, oficio de donación. El formato de inventario de la Alcaldía **ya se recibió**. |

### 3.5 Turismo (Rutas)
| ID | Pregunta |
|----|----------|
| 🟡 D-RT02 | Tarifa Cumaná Histórica: ¿quién cobra y cuál es el flujo de pago? (columnas `tiene_tarifa`/`tarifa_monto` existen pero **nunca se escriben** → el reporte siempre dice "Gratuita"). **Única pregunta viva de Turismo.** |
| 🟡 D-RT03 | Al **Finalizar** una ruta, ¿generar informe/oficio automáticamente? |
| ✅ D-RT05 | ~~Instituciones participantes en rutas~~ — **CERRADO 2026-08-04:** eliminado (mig. 060). El indicador CMI queda descartado. |
| ✅ D-RT04 | ~~Facilitador externo: ¿lista o texto libre?~~ — **CERRADO 2026-08-04:** columna eliminada (mig. 060), nunca se usó. |

### 3.6 Formación
| ID | Pregunta |
|----|----------|
| ✅ D-FO06 | ~~¿CRUD de **oficios base** (`oficios`) + vínculo con `talleres.id_oficio`?~~ — **CERRADO 2026-08-04:** tabla y columna eliminadas (mig. 060). Si el cliente pide llevar registro de oficios **recibidos**, se construye desde cero como módulo propio. |
| 🟢 D-FO05 | ¿Parámetros internos de meta para comparar planificado vs ejecutado? |
| 🟢 D-NEW01 | ¿Activar en UI el correlativo de oficios de formación (FORM-XXX)? |

### 3.7 Transversal
| ID | Pregunta |
|----|----------|
| 🟡 D-TX03 | Migración de **históricos** (Excel/papel): definir módulos + obtener archivos fuente. |
| 🟢 D-OF03 | Libro de correspondencia unificado (oficios emitidos/recibidos). |
| ⚪ D-CMI01 | **"Reducción del tiempo de generación de reportes"** (figura en el documento): es una métrica operativa **antes/después** (manual vs. sistema), **no** un indicador que la app pueda calcular de sí misma. Se mide fuera del sistema (justificación de impacto), no se implementa como KPI. |

---

## 4. AUDITORÍA TÉCNICA ABIERTA

| # | Hallazgo | Estado | Cierra con |
|---|----------|--------|-----------|
| H-04 | Baja de bien no actualiza `condicion` | ⚠️ **Abierto** | D-IN10 (3.4) |
| H-09 | Columnas inertes | ✅ **Cerrado casi por completo** (mig. 060): eliminadas `participantes_ruta.id_institucion`, `rutas.nombre_facilitador_externo`, `talleres.id_oficio`. **Queda solo** `rutas.tiene_tarifa`/`tarifa_monto` | D-RT02 — usar o quitar del reporte |
| H-10 | Tablas sin UI | ✅ **Cerrado** (mig. 060): `oficios` e `instituciones_externas` eliminadas (vacaciones ✅, `taller_inventario` ya lo estaba) | — |

> Resueltos previamente: H-01, H-02, H-03 (visitas inmutables), H-05 (validaciones servidor), H-06 (correlativo atómico), H-07 (enums centralizados), H-08 (FKs validadas), H-11 (género M/F).

> **Recordatorio sobre H-04:** no es solo una pregunta al cliente. Hoy `ActividadInventario::save()` no toca `inventario.condicion` e `Inventario::all()` solo filtra `is_active`, así que **un bien dado de Baja sigue apareciendo como activo** en el listado, los KPIs del dashboard y los indicadores CMI-I01/I03. El inventario reporta números incorrectos mientras esto siga abierto.

---

## 5. PROGRAMACIÓN FALTANTE / BACKLOG TÉCNICO 🛠️

### 5.1 Reportes/funciones pendientes (implementables, queda lo no hecho del Bloque B)

| Módulo | Tarea | Origen |
|--------|-------|--------|
| RRHH | Réplica imprimible del **formato físico de asistencia** — 🔒 necesita el **formato real** (planilla oficial) del cliente para ser fiel | MOD-RRHH 6.2 |
| Formación | Tabla `taller_facilitadores` (múltiples facilitadores) — solo si el cliente lo pide | D-FO08-bis |
| Transversal | Importación de datos históricos desde Excel (depende de D-TX03) | D-TX03 |

### 5.2 Mejoras propuestas (futuro cercano / más adelante) ✨

Propuestas del equipo técnico, no solicitadas aún por el cliente. Priorización sugerida:

| Prioridad | Mejora | Notas de implementación |
|-----------|--------|-------------------------|
| 🟢 **a11y en formularios restantes** | Hecho login + botones ícono del header. Falta vincular `label[for]` en los formularios de los demás módulos (empleados, inventario, visitantes…). |
| 🟢 **Endurecer `Taller::actualizarPersona`** | Whitelist de columnas dentro del método (defensa, no urgente: hoy las claves son fijas). |
| 🟢 **Dividir `ReportesController`** (~3200 líneas al 2026-07-09) | Separar por área cuando convenga (mantenibilidad). |
| 🟢 **Migrar estilos inline a clases** | ~1900 `style=""` en vistas al 2026-07-09; consolidar en utilidades CSS (gradual). |
| 🟢 **Programar la tarea de respaldo en el servidor** | `cron/respaldo_bd.php` ya funciona; falta crear la tarea (`schtasks`). Operativo. |
| 🟢 **Rango de fechas fino en Indicadores** | Ya hay selector de **año**; rango libre mes-a-mes solo si el cliente lo pide (refactor amplio, bajo valor). |
| 🟢 **Ampliar la suite de pruebas** | Base creada (`tests/run.php`). Sumar casos (p. ej. `Asistencia::calcularMinutosTarde`). |

---

## 6. VERIFICACIÓN MANUAL PENDIENTE (probar en navegador)

- **B1** "botón Guardar de RRHH": no hay defecto estático; hacer un alta de empleado de punta a punta para cerrarlo.
- Export **Excel/PDF** en cualquier listado CRUD.
- Toggle **Durable/Fungible** en el modal de inventario.
- Registrar un **período de vacaciones** y verificar el conteo de días hábiles (excluye finde+feriados).
- Registrar un **traslado** y un **escalado falta→amonestación**.
- **Pendiente 2026-07-12** (probado por API/BD, falta un vistazo visual en navegador): listados/reportes de Empleados, Rutas, Visitantes, Pasantes, Inventario con las columnas/filtros nuevos; campana de notificaciones (abrir dropdown y confirmar que las alertas ya vistas no reaparecen); flujo completo de "¿Olvidaste tu contraseña?" desde el link del login.

---

## 7. REGLAS DE NEGOCIO — ESTADO POR MÓDULO (resumen)

> Detalle funcional en los `REGLAS_NEGOCIO_*.md` / `MODELO_NEGOCIO_RRHH.md`.

- **RRHH:** ✅ organigrama jerárquico, ficha técnica + wizard, expediente/recaudos, horarios/grupos A-B/OAC, asistencia/puntualidad, permisos/reposos, amonestaciones+faltas (con tipo y escalado), constancias multi-tipo, egreso/reingreso, traslados, **vacaciones (días)**, badge elegible a fijo, **Bono Vacacional v1** (datos salariales + `/nomina`). 🔒 Falta: **Liquidación de Prestaciones Sociales**.
- **Formación:** ✅ talleres/charlas/inducciones, participantes (adulto/niño, alta sin botón buscar), informe demográfico auto, evidencias, estados con auto-transición, lista de asistencia, reportes. 🔒 Falta: oficios base (D-FO06).
- **Turismo (Rutas):** ✅ rutas por ejecución, puntos+mapa Leaflet offline, participantes, oficios, estado Finalizada, demografía. 🔒 Falta: tarifa (D-RT02), informe/oficio automático al finalizar (D-RT03).
- **Inventario:** ✅ bienes, categorías, ubicaciones, movimientos, bajas, **Durable/Fungible**, reportes/kardex. 🔒 Falta: responsable del bien (D-IN06), costo/proveedor (D-IN09), baja→condición (D-IN10).
- **Recepción (Visitantes):** ✅ visitantes + visitas (bitácora inmutable), reportes. 🛠️ Backlog: visitas activas del día.
- **Sistema:** ✅ RBAC dinámico, usuarios/roles, auditoría humanizada + papelera, configuración institucional, idempotencia (token), export transversal, login endurecido (mig.051) + acepta usuario o correo, respaldos automáticos, búsqueda global, campana de alertas (ahora con "vistas" por usuario, mig.057), **carnetización** (mig.053), **recuperación de contraseña por correo** (mig.058, 🔒 falta SMTP real), egreso desactiva acceso automáticamente.

---

## 8. OBSOLETO / SIN EFECTO
- Módulos **Instituciones externas** y **Actividades de ruta**: retirados (2026-05-31).
- `taller_inventario`, `participantes_taller.es_brigadista`: eliminados (mig.050).
- `nivel_dificultad`, `ruta_inventario`: eliminados (mig.019/021).
