# Módulo de RRHH (Talento Humano) — Reglas de Negocio

**Última actualización:** 2026-10-28

> Fuente única de las reglas de RRHH: el relevamiento con la institución (sesiones 2026-06-02/04,
> decisiones D-RH01…D-RH36, todas cerradas) y su estado en el código. Las preguntas abiertas viven en
> `docs/BACKLOG.md` §3 — **no se reproducen aquí**. Las reglas de cálculo de pago están en
> [`REGLAS_NEGOCIO_Nomina.md`](REGLAS_NEGOCIO_Nomina.md).

**Fuentes documentales del cliente** (2026-06-04): Ficha Técnica del Trabajador (formato oficial),
checklist de recaudos del expediente, hojas físicas de asistencia, `FORMATO DE REGISTRO DE DATOS.xls`
(hojas LISTADO GENERAL —38 campos— y ORGANIGRAMA) y `REPOSOS, PERMISOS Y VACACIONES.xls` (taxonomía de
ausencias). Organigrama oficial: Manual Descriptivo de Cargos (abril 2024).

> Los IDs RN-RH01…RN-RH16 conservan su número histórico (los citan el código y otros documentos);
> por eso el orden de las secciones no es correlativo. RN-RH17…RN-RH20 son reglas incorporadas del
> antiguo `MODELO_NEGOCIO_RRHH.md`.

---

## 1. Estructura organizativa y autoridad

### RN-RH17 — Organigrama (mig. 027, D-RH30)

```
Presidencia (Dirección General — María Maza)
  └── Direcciones (cada una = un departamento)
        └── Coordinaciones (+ unidades de staff)
              └── Personal adscrito
```

- `departamentos` es jerárquico (`id_padre` + `tipo_unidad` ∈ Presidencia/Junta Directiva/Dirección/
  Coordinación/Oficina/Unidad), con el organigrama oficial sembrado y gestionable en `/departamentos`.
  Direcciones: Planificación y Gestión Turística, Administración, Talento Humano.
- Coordinaciones de la fuente: Promoción Turística, Presupuesto, Registro y Selección, Contabilidad,
  Bienestar Social, Compra/Bienes y Servicios, Nómina, Calidad y Servicio, Formación Turística, entre otras.
- **Director** y **Coordinador** son cargos distintos. El liderazgo se **deriva del cargo** del empleado
  (`Director`/`Coordinador`/`Presidenta`); no hay campo «responsable».
- Los cargos son generales/transversales (O1): un mismo cargo existe en varios departamentos.

### RN-RH18 — Quién decide qué

| Cargo | Facultades en RRHH |
|-------|-------------------|
| Directora General / Presidenta (María Maza) | Aprueba permisos especiales, asigna/modifica empleados, decisión final en traslados, confirma el paso a Fijo |
| Directora de Talento Humano | Oficializa y firma permisos, registra y gestiona expedientes, emite constancias |
| Coordinador de departamento | Participa en los traslados de su área (y puede decidirlos, ver RN-RH19) |
| Jefe inmediato | Recibe la notificación previa de los permisos por diligencia |

---

## 2. Registro del empleado

### RN-RH01 — Empleado siempre vinculado a Persona

Todo empleado existe en `personas`; el alta es atómica (INSERT `personas` → INSERT `empleados`, una
transacción). **Cédula = solo dígitos, máx. 8** (mig. 037), y no se repite como empleado activo ni
egresado (`Empleado::existeCedula`; si ya egresó, se usa *Reingreso*).

### RN-RH02 — Cargo, departamento, horario y grupo

Los asigna **Talento Humano** al registrar (también la Directora General), según el cargo requerido y el
origen del empleado. Cargo y departamento son obligatorios.

**Cargos por jerarquía (mig. 035, D-RH11):** IMATUR **no distingue sueldo base por cargo** (todos cobran
el mismo base salvo casos notorios), así que se eliminó `cargos.sueldo_base`. Cada cargo tiene
`nivel_jerarquico` ∈ Presidencia · Dirección · Coordinación · Adscrito (`Cargo::NIVELES`/`ORDEN_NIVEL`).

### RN-RH12 — Asistente de alta y Ficha Técnica (mig. 026/030)

El alta/edición es un **asistente de 5 pasos** (`empleados/form.php`: personales → formación →
institucionales → carga familiar → resumen) con borrador en `localStorage`. Los campos salen del
LISTADO GENERAL y de la Ficha Técnica; el sistema **genera** la Ficha Técnica imprimible
(`EmpleadosController::fichaTecnica`).

| Bloque | Campos |
|--------|--------|
| Personales | Nombres y apellidos, cédula, RIF (cuando aplica, validado), sexo M/F, fecha de nacimiento, estado civil, dirección, parroquia, teléfono, correo (cuando aplica), discapacidad Sí/No, foto (carnet) |
| Comunitarios (opcionales, D-RH25) | Centro de votación, consejo comunal, comuna |
| Formación | Nivel académico, profesión, nombre del título, **fecha del título** (columna `fecha_graduacion`; el rótulo era «Fecha de graduación»), institución, etapa; cursos realizados (institución · curso · inicio · culminación) |
| Laborales | Cargo, departamento, institución de origen, tipo de contrato, clasificación, horario/grupo, fecha de ingreso a IMATUR, ingreso a la administración pública, vencimiento de contrato, uniforme Sí/No + tallas (solo se registra, D-RH35). Tiempo de servicio = derivado |
| Experiencia laboral (D-RH23) | Organismo · cargo · inicio · culminación |
| Carga familiar | Ver RN-RH11 |

- Tablas hijas `carga_familiar`/`cursos_realizados`/`experiencia_laboral`, gestionadas desde el expediente.
- El **folio** del expediente es automático: `EXP-####` derivado del `id`, no editable (mig. 040).
- La **Ficha Técnica** es además un recaudo del expediente.

### RN-RH03d — Edad permitida

Mínimo **18**. Personal IMATUR hasta **65**; comisión de servicio (Alcaldía/Gobernación) hasta **70**,
límite absoluto. Se valida en el cliente y en `EmpleadosController`; la edad nunca se almacena.

### RN-RH11 — Carga familiar (mig. 026/039, D-RH21)

- **Tabla dedicada**: nombre y apellido, cédula, fecha de nacimiento, parentesco (padre, madre,
  cónyuge/concubino, hijo/a), género y si vive. El asistente pide el género del familiar.
- Usos: expediente; reportes; **justificar permisos médicos a familiar** (el familiar debe estar
  registrado); base de beneficios. Nómina cuenta los **hijos** desde aquí (prima por hijo).
- Sub-recaudos: cédula y partida de nacimiento de pareja/cónyuge, acta de matrimonio/concubinato,
  cédula y partida de padres e hijos. Lo pendiente sobre beneficios específicos (D-RH29) está en BACKLOG §3.

### RN-RH10 — Expediente y recaudos (mig. 033, D-RH22)

El expediente se organiza **por departamento** (antes, por tipo de empleado). Físicamente es una
carpeta marrón tipo oficio con ganchos; en el sistema es un **modelo híbrido**: subida de archivos +
checklist con **detección de faltantes** (`ExpedienteDocumento::RECAUDOS`/`recaudosEstado`).

| Recaudo | Obligatorio |
|---------|-------------|
| Currículum (CV) · cédula ampliada y centrada · partida de nacimiento · título Bachiller/Profesional · fondo negro del título · RIF · referencia bancaria | Sí |
| Recaudos de carga familiar · Ficha Técnica · documentación de estudiante/discapacidad · otro | No |

- Archivos PDF/JPG/PNG ≤ 5 MB (MIME real), nombre `Tipo_Empleado_{id}_{ts}`.
- **Viven fuera de la raíz web** (`storage/uploads/expedientes/`) y se sirven por `DescargaController`,
  que valida el rol. `public/uploads/` se eliminó (H-15): allí cualquier documento era legible por URL.
- Personal **estudiante o con discapacidad**: se le pide la documentación y se le ajusta el horario (RN-RH08).

---

## 3. Vinculación

### RN-RH03 — Tipo de contrato, origen y comisión de servicio (mig. 025)

| Concepto | Valores | Regla |
|----------|---------|-------|
| `tipo_contrato` (estabilidad) | Fijo · Contratado | DEFAULT **Contratado**: todo nuevo entra Contratado (D-RH19) |
| `institucion_origen` (quién paga) | Alcaldía · Gobernación · IMATUR | DEFAULT IMATUR |
| `es_comision_servicio` | **Derivado** | = origen ≠ IMATUR. No es checkbox; el asistente lo muestra de solo lectura (D-RH27/D-RH31) |
| `clasificacion` | Empleado · Obrero | Independiente del contrato (D-RH26) |

- `'Suplente'` y `'Comisión de Servicio'` ya no son tipos de contrato: no hay suplentes en IMATUR, y la
  comisión es ortogonal a la estabilidad (un comisionado puede ser Fijo o Contratado).
- **Comisión ≠ servicio previo:** comisión es **seguir en la nómina** de Alcaldía/Gobernación mientras
  trabaja en IMATUR. Quien trabajó en otro ente, se desvinculó y hoy cobra por IMATUR es personal IMATUR
  con servicio previo: no es comisión, pero esos años **cuentan** para la antigüedad.
- **Ingreso a la administración pública** (`fecha_ingreso_administracion`): se registra para **todo** el
  personal; se precarga con la fecha de ingreso y se cambia si hubo servicio previo; no puede ser
  posterior al ingreso a IMATUR. Es la base de la antigüedad (vacaciones y prima de antigüedad).

**Cómo ingresa una persona:**

| Origen | Documentos mínimos | Contrato inicial |
|--------|-------------------|------------------|
| Particular (sin vínculo público) | CV | Contratado |
| Alcaldía/Gobernación (comisión) | CV + documentos de la dependencia | Contratado, o **Fijo** si ya era fijo en su origen y trae **carta de asignación** cuando aún no cumple el tiempo |

- El **Fijo** conserva el derecho a **retornar a su institución de origen**.
- El origen IMATUR llega a Fijo **solo por tiempo de servicio**, sin carta de asignación.

### RN-RH03b — Transición Contratado → Fijo (D-RH20)

Por años de servicio acumulados; los años previos en el ente de origen **se suman**. Referencia del
cliente: Alcaldía 5-6 años, Gobernación 3-6, IMATUR 5-6. **No es automática**: el sistema muestra el
badge «Elegible a fijo» (`Empleado::elegibleParaFijo`, umbral `UMBRAL_FIJO` = el mínimo de cada rango:
Gobernación 3, Alcaldía e IMATUR 5) y la promoción la confirman RRHH y la Directora a mano.

---

## 4. Horarios y asistencia

### RN-RH08 — Modalidades de horario (mig. 028/049)

| Modalidad | Quién | Días | Horario |
|-----------|-------|------|---------|
| Estándar | Personal general | Todos los hábiles | **8:00 – 14:00** (antes 8–16; se redujo por infraestructura) |
| Servicios Generales A / B | Servicios Generales | Días alternos | 8:00 – 14:00 |
| OAC Matutino (sub-grupo 1) | OAC — Oficina de Atención al Ciudadano («Recepción») | Todos los hábiles | 7:00 – 12:00 |
| OAC Vespertino (sub-grupo 2) | OAC | Todos los hábiles | 10:00 – 14:00 |
| Horario ajustado (D-RH36) | Estudiantes / personas con discapacidad | Según el caso | Un horario más del catálogo |
| En Ruta / Actividad | Quien tenga ruta o formación externa ese día | El día de la actividad | Fuera de sede (RN-RH15) |

- `horarios` es un catálogo con CRUD (`/horarios`); los sub-grupos OAC son independientes del A/B.
- **Grupo A/B:** la secuencia alterna **por día hábil trabajado**, no por semana (L-A, M-B, X-A, J-B,
  V-A, y el lunes siguiente continúa B). Intercalado simple, **sin saltar feriados** (D-RH16, provisional).
  `empleados.grupo_rotacion` guarda el grupo; el turno del día no se calcula en el sistema.
- **Fines de semana = descanso** para todos, salvo eventos turísticos en que se puede convocar personal:
  Carnaval, Semana Santa, Día de Santa Inés, Cruz de Mayo.

### RN-RH04 · RN-RH05 · RN-RH13 — Asistencia: registro, puntualidad y horas (mig. 029/056)

- **Patrón toggle** (igual que visitas): sin registro abierto del día → INSERT con `hora_entrada`; con uno
  abierto → UPDATE `hora_salida`. Una asistencia abierta por día; se guarda el usuario que marcó.
- Se permite **registro manual retroactivo** (fallas del sistema, ausencias documentadas). Las
  asistencias son bitácora: **no se eliminan**.
- **Puntualidad:** `minutos_tarde` = entrada real − hora del horario asignado; impuntual si supera
  `minutos_tolerancia_puntualidad` (editable en `/config`). Sin horario → NULL.
- **Salida temprana:** si marca salida antes de su hora, más allá de `minutos_tolerancia_salida_temprana`
  (default 10, independiente de la anterior), el **motivo es obligatorio**.
- **Horas trabajadas** = salida − entrada, **solo para reportes e indicadores**: no influyen en el pago
  (D-RH09/D-RH12). **No hay horas extras.**
- **Ausentismo:** el índice resume el día (activos/presentes/impuntuales/en actividad/ausentes).
- Formato físico de origen: hoja semanal por tipo de personal (N° · nombre · cédula · firma y hora por
  día). La vista del sistema lo moderniza.

### RN-RH15 — En Ruta / Formación externa (D-RH17)

El día que un empleado participa en una **salida de ruta** (`ruta_ejecuciones.fecha`) o en una
**formación externa** (`talleres.es_interna = FALSE`, dentro de su rango de fechas) no asiste a la sede,
y **no es falta**: `Asistencia::empleadosEnActividad()` lo detecta y lo excluye del ausentismo
(«En actividad»). A otros empleados se les puede pedir apoyo para esas actividades.

---

## 5. Ausencias y disciplina

### RN-RH06 — Permisos y reposos (mig. 032, D-RH32)

| Categoría | Tipos (`PermisoLaboral::TIPOS`) | Documentación |
|-----------|--------------------------------|---------------|
| Reposo | Reposo médico | Reposo (48 h, 72 h, 7/10/21 días…) + diagnóstico |
| Permiso | Médico familiar | Informe/constancia; familiar en la carga familiar |
| | Diligencia | Notificación previa al jefe inmediato |
| | Duelo · Maternidad/Paternidad (post-parto, p. ej. 6 meses) · Personal · Estudios («en clases») · Otro | — |

- Reposo y Permiso se distinguen por `categoria` (select en cascada categoría → tipo).
- Atributos: empleado, desde, hasta, duración (texto libre, p. ej. «72 horas»; se propone en días al
  elegir el rango), observación. **En curso/Concluido se deriva** de `fecha_fin`.
- Flujo: Pendiente → Aprobado / Rechazado / Anulado.
- **Aprobación (D-RH03):** ordinarios, la Directora de Talento Humano; **especiales**, la Directora
  General. Firma cualquiera de las dos, y Talento Humano **siempre oficializa**.
- Vacaciones van en su propio módulo (RN-RH07).

### RN-RH03c — Faltas y amonestaciones (mig. 031/042/048, D-RH13/D-RH28)

- Justificadas (permisos) e injustificadas se gestionan **por separado**; ambas las registra Talento Humano.
- `faltas.tipo` ∈ Inasistencia injustificada · Incumplimiento de reglas.
- Una amonestación suele ser la acumulación de cierto número de faltas, **pero varía según el caso**: el
  sistema cuenta y notifica, RRHH decide.
- **Escalado falta → amonestación:** desde el detalle del empleado, con confirmación (el modal dice con
  cuántas quedará, p. ej. «3/3 — causa de despido») y **una sola vez por falta** mientras su amonestación
  siga activa (`Amonestacion::activaDesdeFalta`). El empleado es siempre el de la falta.
- **Anular** (no borrar) exige motivo (`motivo_anulacion`); una falta cuya amonestación se anula puede
  volver a escalarse.
- **3 amonestaciones activas** (`Amonestacion::LIMITE_DESPIDO`) = **causa de despido** para Contratados:
  el sistema **solo alerta** (roster con semáforo, columna Disciplina, botón *Procesar despido*). El
  egreso es siempre manual.

---

## 6. Vacaciones

### RN-RH07 — Días de vacaciones (mig. 045/046/071)

- **Fórmula:** 15 días hábiles + 1 por año de servicio, **tope 30** (`Vacacion::regla()`; base y tope son
  parámetros `vacaciones_dias_base/tope` en `/nomina/parametros`).
- **Antigüedad** desde el ingreso a la administración pública (RN-RH03), para todo el personal.
- **Días hábiles** excluyen fines de semana y **feriados** (tabla `feriados`). Los fijos se repiten por
  mes-día; Carnaval y Semana Santa son **movibles** y se generan por año en `/vacaciones/feriados`. ⚠️ Si
  un año queda sin ellos, el conteo falla en silencio; la pantalla avisa cuando faltan.
- **No se pierden** (D-RH06): lo no disfrutado se acumula al período siguiente y se toma en cualquier
  momento. **Ajuste inicial** para arrancar con el saldo histórico real.
- Comisión de servicio: las vacaciones se coordinan con el ente de origen (coordinación operativa, fuera
  del sistema).
- El **monto** (bono vacacional) es de Nómina: ver `REGLAS_NEGOCIO_Nomina.md`.

---

## 7. Movimientos del personal

### RN-RH19 — Traslado de departamento (mig. 047)

Se convoca una reunión de directores y coordinadores del área; decide la **Directora General** o la
**coordinadora del departamento de origen**; Talento Humano lo ejecuta. En el sistema es una
**reasignación con historial** (`Empleado::trasladar()`, tabla `empleado_traslados`), no una edición.

### RN-RH20 — Egreso y reingreso (mig. 036/041, R-12)

- Motivos: renuncia, despido, jubilación, fin de contrato, fallecimiento, otro (`Empleado::MOTIVOS_EGRESO`).
- El egreso **no borra**: registra `fecha_egreso` + motivo y el empleado queda `is_active = TRUE` como
  histórico (constancias, tiempo de servicio). `is_active = FALSE` es solo la papelera (registros por error).
- **Reingreso** cierra la fila de `empleados_egresos` y conserva el historial; no puede haber dos egresos
  abiertos. Egreso y reingreso desactivan/reactivan la cuenta de usuario del empleado.
- El **vencimiento de contrato** es un campo aparte (`fecha_vencimiento_contrato`) que alimenta la alerta
  de contratos por vencer; no es un egreso.

---

## 8. Documentos y acceso

### RN-RH14 — Documentos generados (mig. 034, B13)

Cada emisión guarda fecha/hora y queda en el **historial del empleado**, con correlativo (mismo mecanismo
que los oficios, `ConfigSistema::generarNumeroOficio`).

| Documento | Regla |
|-----------|-------|
| Constancias | `CONST-NNN/AAAA`. Tipos: trabajo, bancaria (sin monto), horario, funciones, antigüedad, egreso. Egreso solo si egresó; bancaria/horario solo si activo. **Sin antigüedad mínima.** Muestra estatus (Activo/Egresado/En permiso/En reposo) y tiempo de servicio |
| Ficha Técnica del Trabajador | Generada con los datos registrados (RN-RH12) |
| Carnet | CR80, empleados y pasantes (foto por persona) |
| Permisos, asistencia, reportes | Registro con timestamp; reportes de RRHH exportables |
| Nómina para Alcaldía/Gobernación | Ver `REGLAS_NEGOCIO_Nomina.md` |

El RIF institucional de todo documento sale de `ConfigSistema::rif()` (G-20008498-7), vía el membrete único.

### RN-RH09 — Empleado con usuario del sistema

- Un empleado puede tener o no usuario (`usuarios.id_empleado`).
- Lo que ve cada usuario lo decide su **rol**, que se arma en *Sistema → Roles y Permisos* marcando
  módulos; un rol nuevo funciona completo sin programación. Lo exclusivo del Administrador (bitácora,
  accesos, aprobar pasantes, cuentas de Administrador) no se delega.
- Talento Humano puede crear cuentas, pero **no** asignar el rol Administrador, tocar ni suspender una
  cuenta de Administrador, ni cambiarse su propio rol.

### RN-RH16 — Sueldo

En *Registrar sueldo* solo se capturan el **sueldo básico** mensual y la **prima de discapacidad**; cada
registro es nuevo (vigente desde una fecha) y conserva el historial (`empleado_salarios`). Las primas de
profesionalización, antigüedad, por hijo y el transporte **no se teclean**: Nómina las deriva del grado de
instrucción, la antigüedad, la carga familiar y los parámetros. Detalle en `REGLAS_NEGOCIO_Nomina.md`.
