# Módulo de RRHH — Reglas de Negocio

**Última actualización:** 2026-06-04

> **Fuente de negocio:** `docs/MODELO_NEGOCIO_RRHH.md` consolida el relevamiento con la institución (modalidades de horario, tipos de empleado, expediente, permisos/reposos/vacaciones, organigrama). Este documento traduce esas reglas a su estado técnico (implementado vs pendiente) y a su hoja de ruta (sección 12 del modelo). Preguntas abiertas/decisiones (fuente única): `docs/REGISTRO_NEGOCIO.md`.

## Contexto institucional

IMATUR se estructura como **Presidencia → Direcciones → Coordinaciones (+ unidades de staff) → personal adscrito**. El módulo de RRHH está a cargo de la **Dirección de Talento Humano**. Los datos de empleados sirven como base para los módulos de Formación (facilitadores), Rutas (guías), y acceso al sistema. **Director** y **Coordinador** son cargos distintos (el liderazgo se deriva del cargo del empleado). La jerarquía está **implementada** (migración 027, `departamentos.id_padre`/`tipo_unidad`) con el organigrama oficial (Manual Descriptivo de Cargos, abril 2024) sembrado.

----

## RN-RH01 — Empleado siempre vinculado a Persona

Todo empleado debe existir previamente en la tabla `personas`. La creación de un empleado es atómica: INSERT en `personas` → INSERT en `empleados` (mismo `beginTransaction`). No puede haber empleado sin persona.

---

## RN-RH02 — Cargo y Departamento obligatorios

Un empleado debe tener un cargo y un departamento asignado. Un mismo cargo puede existir en distintos departamentos.

**Cargos por jerarquía (migración 035, D-RH11):** IMATUR **no distingue sueldo base por cargo** (todos cobran el mismo base salvo casos notorios) → se eliminó `cargos.sueldo_base`. En su lugar, cada cargo tiene `nivel_jerarquico` ∈ **Presidencia · Dirección · Coordinación · Adscrito** (sucesión de responsabilidad del organigrama; `Cargo::NIVELES`/`ORDEN_NIVEL`). La vista de Cargos los ordena por nivel; al crear uno nuevo se elige su escalón.

---

## RN-RH03 — Tipos de contrato

**Estado actual (BD, migración 025 — 2026-06-04):** la estabilidad y el origen quedaron separados en 3 campos:
- `tipo_contrato` ∈ `'Fijo'`/`'Contratado'`, DEFAULT `'Contratado'` (todo nuevo es Contratado).
- `institucion_origen` ∈ `'Alcaldía'`/`'Gobernación'`/`'IMATUR'`, DEFAULT `'IMATUR'`.
- `es_comision_servicio` BOOLEAN **derivado** = (`institucion_origen ≠ 'IMATUR'`). Comisión de servicio ⟺ viene de Alcaldía/Gobernación; IMATUR ⇒ no comisión. No es un campo manual.
- **Edad:** comisión de servicio (Alcaldía/Gobernación) 18–70; personal IMATUR 18–65 (validado en cliente y `EmpleadosController`).

Enums centralizados en `Empleado::TIPOS_CONTRATO` / `Empleado::INSTITUCIONES_ORIGEN` (patrón H-07); el controller y la vista los consumen.

**Reglas de negocio que implementa (confirmadas 2026-06-04):**
- **Todo empleado nuevo entra como `'Contratado'`** (DEFAULT) (D-RH19/2.1).
- **`'Suplente'` y `'Comisión de Servicio'` deprecados** como `tipo_contrato` — no existen suplentes; comisión de servicio es designación ortogonal de origen (`es_comision_servicio`).
- Comisión de servicio **se deriva del origen** (Alcaldía/Gobernación ⇒ Sí; IMATUR ⇒ No); un empleado en comisión puede ser Fijo *o* Contratado (D-RH27/D-RH31).
- Única forma de ingresar como **Fijo**: venir ya fijo desde Alcaldía/Gobernación (con carta de asignación si aún no cumple el tiempo). Origen IMATUR llega a Fijo solo por tiempo de servicio.

Reglas vigentes de baja:
- Los empleados **Contratados** pueden tener `fecha_egreso` programada.
- Empleados dados de baja: `fecha_egreso` registrada + `is_active = FALSE` en `empleados`.
- **Baja** no elimina al empleado ni a su persona; preserva el historial.

---

## RN-RH03b — Clasificación, origen y transición a Fijo (confirmado 2026-06-04)

- **Clasificación** `Empleado` / `Obrero`: campo distinto del tipo de contrato (D-RH26).
- **Transición Contratado → Fijo** por tiempo de servicio (Alcaldía 5–6 años, Gobernación 3–6, IMATUR 5–6). Los años previos en el ente de origen **se suman**. **No es automática**: el sistema solo **alerta la elegibilidad**; RRHH/Directora la confirman (D-RH20).
- **Restricción de edad** al registrar: 18–65 años; excepción >65 solo por comisión de servicio; límite absoluto 70.

---

## RN-RH03c — Amonestaciones (✅ implementado mig.031 — R-9)

- El sistema **cuenta** `faltas` injustificadas y `amonestaciones` por empleado y las muestra en un roster con semáforo; **RRHH las registra manualmente** (el sistema solo notifica — D-RH28).
- **3 amonestaciones activas** (`Amonestacion::LIMITE_DESPIDO`) = **causa de despido** (aplica a Contratado) → alerta en el detalle/roster.
- Módulo: `AmonestacionesController` (roster `index`, `empleado($id)` detalle, registrar/eliminar falta y amonestación). Las faltas injustificadas son distintas de los permisos/ausencias justificadas (R-8).

---

## RN-RH04 — Asistencia: patrón toggle

La asistencia sigue el mismo patrón que las visitas:
- Si existe un registro abierto del día (sin `hora_salida`) → UPDATE `hora_salida = NOW()`.
- Si no existe → INSERT nuevo con `hora_entrada = NOW()`.
- Un empleado solo puede tener una asistencia abierta por día.

El marcaje registra el usuario que realizó la acción mediante `$this->getUserId()`. ✅ Bug BRH-04 corregido en Fase 2.5.

---

## RN-RH05 — Asistencia manual

Además del marcaje automático, el sistema permite registro manual de asistencias (retroactivo). Esto cubre casos de fallas del sistema o ausencias documentadas.

---

## RN-RH06 — Permisos, Reposos y Ausencias (taxonomía confirmada, UI pendiente)

La tabla `permisos_laborales` existe desde migración 002. **Taxonomía confirmada (2026-06-04, ver `MODELO_NEGOCIO_RRHH.md` 4.2):** reposo médico, permiso médico a familiar, diligencia, duelo, maternidad/paternidad (post-parto), personal, estudios; + vacaciones y falta sin justificar.

- **Atributos por registro:** empleado, fecha inicio, tipo, tiempo (horas/días/meses), hasta (fecha fin), estatus (En curso/Concluido), observación.
- **Aprobación (D-RH03):** Talento Humano oficializa todos; los **especiales** los aprueba la Directora General. Firma: Directora de TH o Directora General.
- **Justificadas e injustificadas se gestionan por separado** (RN-RH13); las injustificadas alimentan amonestaciones (RN-RH03c).
- **Log con timestamp de solicitud** y correlativo (RN-RH14).

**✅ Implementado (migración 032 — R-8):** `PermisosController` + modelo `PermisoLaboral` + `permisos/index.php`. Reposo y Permiso se distinguen por `categoria` (D-RH32); `tipo_permiso` con la taxonomía; `duracion` texto libre; **En curso/Concluido derivado** de `fecha_fin`; flujo Pendiente→Aprobado/Rechazado/Anulado (D-RH03). **Vacaciones NO incluido** (fórmula pendiente).

---

## RN-RH07 — Vacaciones (reglas parciales, lógica pendiente)

La tabla `vacaciones` existe desde migración 002.

**Confirmado (2026-06-04):**
- Fines de semana = descanso normal (excepto eventos: Carnaval, Semana Santa, Día de Santa Inés, Cruz de Mayo).
- Vacaciones **no disfrutadas se acumulan** — nunca se pierden; sumatoria automática al período siguiente (D-RH06).
- Comisión de servicio: vacaciones coordinadas con Alcaldía/Gobernación.

**Pendiente:** fórmula de días por años de servicio y cálculo automático vs manual (D-RH04, D-RH05, D-NEW05). UI sin implementar (R-8).

---

## RN-RH08 — Horarios y Modalidades (definidas, UI pendiente)

La tabla `horarios` existe desde migración 002. Cada empleado tiene `id_horario` FK.

**Modalidades confirmadas (2026-06-04, ver `MODELO_NEGOCIO_RRHH.md` 1.5):**
- **Estándar:** 8am–2pm (antes 8am–4pm).
- **Servicios Generales Grupo A / B:** rotación alterna por día hábil (8am–2pm). Cálculo intercalado simple, sin calendario de feriados por ahora (D-RH16, provisional).
- **OAC / Recepción:** sub-grupo 1 (7am–12pm) y sub-grupo 2 (10am–2pm), todos los días hábiles.
- **Horario ajustado:** estudiantes/personas con discapacidad (D-RH36).
- **En Ruta / Actividad:** el día asignado el empleado no asiste presencial (RN-RH15).

**✅ Implementado (migración 028 — R-6):** catálogo `horarios` con CRUD (`HorariosController`/`Horario`/`horarios/index.php`) + seed de modalidades (Estándar, OAC Matutino/Vespertino, Servicios Generales). `empleados.grupo_rotacion` (A/B) para la rotación de Servicios Generales. Horario ajustado = horario más del catálogo asignable (D-RH36). Config `minutos_tolerancia_puntualidad` (default 15) lista para R-7.

---

## RN-RH10 — Expediente y Documentos (confirmado 2026-06-04, pendiente R-5)

- El expediente se organiza **por departamento**; el sistema asigna un **código interno** al trabajador.
- Recaudos: CV, cédula ampliada, partida de nacimiento, título Bachiller/Profesional, fondo negro del título, RIF, referencia bancaria, recaudos de carga familiar, Ficha Técnica, documentación de estudiante/discapacidad si aplica.
- **Modelo híbrido (D-RH22) — ✅ implementado mig.033:** subida de archivos (PDF/JPG/PNG ≤5MB) con convención `Tipo_Empleado_{id}` en `public/uploads/expedientes/` + checklist con **detección de recaudos faltantes** (`ExpedienteDocumento::recaudosEstado`). Sección "Recaudos del Expediente" en `empleados/detalle.php`. Catálogo en `ExpedienteDocumento::RECAUDOS`.

---

## RN-RH11 — Carga Familiar (confirmado 2026-06-04, pendiente R-4)

- **Tabla dedicada** (no campo de texto). Campos: nombre y apellido, cédula, fecha de nacimiento, parentesco (padre/madre/cónyuge-concubino/hijo).
- Usos: expediente, base de bono escolaridad/beneficios (D-RH29), reportes, justificación de faltas médicas de familiar.

---

## RN-RH12 — Ficha Técnica del Trabajador (✅ implementado mig.026/030 — R-2/R-2b)

- **Registro/edición = asistente multi-paso** (`empleados/form.php`, `EmpleadosController::nuevo()`/`editar($id)`): 5 pasos (personales → formación → institucionales → carga familiar → resumen) con `localStorage` y verificación final. Campos completos incluyen uniforme/tallas y datos comunitarios (centro votación/consejo comunal/comuna).
- El sistema **genera** el documento "Ficha Técnica del Trabajador" en vista imprimible (`EmpleadosController::fichaTecnica($id)` → `empleados/ficha_tecnica.php`).
- Bloques: Datos Personales · Formación (con Cursos Realizados) · Carga Familiar · Datos Laborales (con Experiencia Laboral = trabajos anteriores).
- Datos extra en `personas` (RIF, estado civil, discapacidad, nivel académico, profesión, título, fecha graduación, institución académica) y `empleados.clasificacion` (Empleado/Obrero).
- Tablas hijas `carga_familiar`/`cursos_realizados`/`experiencia_laboral` (modelos `CargaFamiliar`/`CursoRealizado`/`ExperienciaLaboral`), gestionadas desde el **expediente** (`EmpleadosController::detalle($id)`).
- Tallas de uniforme y datos comunitarios ya capturados (mig.030, R-2b). RIF institucional en la ficha = G-20008498-7 (discrepa del de `carta_aceptacion.php`; unificar vía ConfigSistema).

---

## RN-RH13 — Asistencia: puntualidad, ausentismo y horas (✅ implementado mig.029 — R-7)

- Patrón toggle vigente (RN-RH04).
- **Puntualidad:** al marcar entrada se calcula `asistencias.minutos_tarde` = retraso vs hora del horario asignado. Impuntual si supera `minutos_tolerancia_puntualidad` (config, default 15, editable en `/config`). Sin horario → NULL.
- **Horas trabajadas** derivadas (hora_salida − hora_entrada) **solo para reporte/indicadores** — no para pago (D-RH09/D-RH12).
- **Ausentismo:** el index de asistencia muestra resumen del día (activos/presentes/impuntuales/en actividad/ausentes) y lista a los ausentes.
- **Detección En Ruta/Formación externa (RN-RH15):** `Asistencia::empleadosEnActividad($fecha)` excluye del ausentismo a quien está en ruta (`rutas.fecha_visita`) o formación externa (`talleres.es_interna=FALSE`).
- **No** hay horas extras.
- Formato físico recibido (hoja semanal por tipo de personal); la vista del sistema ya moderniza ese registro. Pendiente opcional: réplica imprimible del formato.

---

## RN-RH14 — Documentos generados y log (✅ constancias implementadas mig.034 — R-10)

**Constancias (R-10):** `Constancia::crear()` genera correlativo `CONST-NNN/AAAA` (`ConfigSistema::generarNumeroOficio('constancia')`), vista imprimible (`empleados/constancia.php`, firmante desde ConfigSistema) y **historial por empleado** (`fecha_emision`) en el expediente. `EmpleadosController::generarConstancia/constancia/eliminarConstancia`.

- El sistema genera/registra: constancias de trabajo, permisos laborales, fichas técnicas, formato de asistencia, reportes, y a futuro nómina para Alcaldía/Gobernación (D-RH34).
- Cada emisión guarda **timestamp de solicitud** y se comporta como **log/historial** por empleado, con **correlativo** (similar al de oficios — `ConfigSistema::generarNumeroOficio()`).

---

## RN-RH15 — Integración Asistencia ↔ Rutas/Formación (confirmado 2026-06-04, pendiente R-7)

- Al evaluar la asistencia del día, el sistema **detecta** si el empleado tiene una ruta o actividad de formación externa asignada.
- Si la tiene, ese día se registra como **"En Ruta / En Actividad"** y no cuenta como ausencia ni presencia normal.
- A algunos empleados se les puede pedir **apoyo** para estas actividades (detalle fino pendiente).

---

## RN-RH09 — Empleado con usuario del sistema

Un empleado puede o no tener usuario en el sistema. La tabla `usuarios` tiene FK opcional a `empleados`. La desactivación del usuario (`is_active = FALSE`) es independiente del estado del empleado.

---

## Estado de brechas

Mapa hacia la hoja de ruta de `MODELO_NEGOCIO_RRHH.md` sección 12 (R-1…R-11).

| ID | Descripción | Estado | Roadmap |
|----|-------------|--------|---------|
| BRH-01 | UI para Permisos/Reposos Laborales | ✅ Hecho (migración 032) | R-8 |
| BRH-02 | UI para Vacaciones con cálculo de saldo | ⚠️ Reglas parciales — falta fórmula (D-RH04/05/NEW05) | R-8 |
| BRH-03 | UI para Horarios (modalidades + grupos A/B + sub-grupos OAC) | ✅ Hecho (migración 028) | R-6 |
| BRH-04 | `marcar()` usaba `$user_id = 1` hardcodeado | ✅ Corregido en Fase 2.5 | — |
| BRH-05 | Alertas por contratos próximos a vencer | ✅ Implementado en Dashboard | — |
| BRH-06 | Reporte de permisos por tipo/empleado/período | ❓ Pendiente — requiere BRH-01 | R-8 |
| BRH-07 | Reporte de saldo de vacaciones por empleado | ❓ Pendiente — requiere BRH-02 | R-8 |
| BRH-08 | Fix DEFAULT `tipo_contrato` → 'Contratado'; deprecar 'Suplente' | ✅ Hecho (migración 025) | R-3 |
| BRH-09 | Separar estabilidad / origen-nómina / comisión de servicio | ✅ Hecho (migración 025) | R-3 |
| BRH-10 | Ampliar campos de empleado + generador de Ficha Técnica + asistente multi-paso | ✅ Hecho (migraciones 026 y 030) | R-2 / R-2b |
| BRH-11 | Tabla de Carga Familiar + sub-recaudos | ⚠️ Tabla `carga_familiar` ✅ (mig.026); sub-recaudos/beneficios pendientes (D-RH29) | R-4 |
| BRH-12 | Expediente: subida de archivos + checklist + detección faltantes | ✅ Hecho (migración 033) | R-5 |
| BRH-13 | Jerarquía organizativa (Dirección→Coordinación) + Director/Coordinador | ✅ Hecho (migración 027) | R-1 |
| BRH-14 | Asistencia: puntualidad/ausentismo/horas-reporte + En Ruta | ✅ Hecho (migración 029) | R-7 |
| BRH-15 | Amonestaciones + conteo faltas injustificadas + alerta despido | ✅ Hecho (migración 031) | R-9 |
| BRH-16 | Documentos generados (constancias) con correlativo + log | ✅ Hecho (migración 034) | R-10 |
| BRH-17 | Generación de nómina para Alcaldía/Gobernación | ❓ Futuro — requiere D-RH34 | R-11 |
