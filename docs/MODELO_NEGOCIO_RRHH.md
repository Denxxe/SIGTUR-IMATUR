# Modelo de Negocio — RRHH (Talento Humano)

**Creado:** 2026-06-02  
**Última actualización:** 2026-09-17 (saneo: §10-12 colapsadas, hoja de ruta cumplida)  
**Estado:** Relevamiento **cerrado**. Secciones 1-9 vigentes como fuente de reglas de RRHH  
**Complementa:** `REGLAS_NEGOCIO_RRHH.md` (reglas técnicas). El organigrama vigente está en la **sección 7.1** de este mismo documento y sembrado en la migración 027  
**Preguntas abiertas:** `BACKLOG.md` §3 — **no se reproducen aquí**

**Leyenda de estado:**
- ✅ **Confirmado** — listo para implementar
- 🟢 **Provisional** — decisión tomada "por ahora", puede revisarse
- ⚠️ **Parcial** — falta información para cerrar
- ❓ **Sin respuesta** — bloquea implementación
- 📋 **Pendiente documento** — esperando archivo/formato físico

**Fuentes documentales recibidas (2026-06-04):**
| Fuente | Aporte |
|--------|--------|
| `FICHA TÉCNICA DEL TRABAJADOR` (imagen) | Estructura oficial del documento que el sistema debe generar |
| Checklist de recaudos (imagen) | Lista exacta de documentos del expediente + sub-recaudos de carga familiar |
| Formato de asistencia (imágenes) | Hoja semanal rudimentaria, separada por tipo de personal (Matutino, Gobernación…) |
| `FORMATO DE REGISTRO DE DATOS.xls` → hoja **LISTADO GENERAL** | 38 campos reales del registro maestro de empleados |
| `FORMATO DE REGISTRO DE DATOS.xls` → hoja **ORGANIGRAMA** | Jerarquía Dirección → Coordinación → personal adscrito |
| `REPOSOS, PERMISOS Y VACACIONES.xls` | Taxonomía real de ausencias en uso |

---

## 1. Horarios y Modalidades de Trabajo

### 1.1 Horario Estándar — ✅ Confirmado

- **Horario original:** 8:00am – 4:00pm
- **Horario actual (vigente):** 8:00am – 2:00pm — ajuste por razones de infraestructura institucional.
- Aplica a la mayoría del personal que no entra en las modalidades especiales descritas abajo.

---

### 1.2 Servicios Generales — Grupo A / Grupo B — ✅ Confirmado

El personal de **Servicios Generales** trabaja en un esquema de **días alternados**. Al registrar un empleado de Servicios Generales se le asigna uno de dos grupos: **Grupo A** o **Grupo B**.

**Mecánica de rotación:**
- La secuencia alterna **por día hábil trabajado** (no por semana ni quincena).
- El grupo que trabaja hoy descansa mañana, y así sucesivamente.
- Horario cuando asiste: 8:00am – 2:00pm.

| Día | Grupo que trabaja |
|-----|-------------------|
| Lunes | A |
| Martes | B |
| Miércoles | A |
| Jueves | B |
| Viernes | A |
| Sábado / Domingo | — libre — |
| Lunes (semana siguiente) | B *(continúa donde quedó)* |
| Martes | A |
| … | … |

**Días no laborables (feriados oficiales):** 🟢 **Provisional (D-RH16)**
- **Decisión por ahora:** el sistema **NO** manejará un calendario de días no laborables. El cálculo del turno A/B se hace **intercalado simple** (alternancia continua por día hábil de lunes a viernes), sin contemplar feriados decretados.
- La previsión de saltar feriados de la secuencia queda **anotada para revisión futura** (el usuario lo confirmará con la institución). Si en el futuro se incorpora, se necesitará un calendario de feriados.

---

### 1.3 Recepción / OAC — Sub-grupos de Horario — ✅ Confirmado

El departamento se denomina oficialmente **OAC — Oficina de Atención al Ciudadano** (en el sistema y en los permisos se rotula como **Recepción**). Su personal trabaja **todos los días hábiles**, sin alternancia, dividido en dos sub-grupos con turnos distintos:

| Sub-grupo | Horario |
|-----------|---------|
| Sub-grupo 1 | 7:00am – 12:00pm |
| Sub-grupo 2 | 10:00am – 2:00pm |

- Estos sub-grupos son **independientes** del esquema A/B de Servicios Generales.
- El empleado se asigna a uno de los dos sub-grupos al registrarse en el departamento.

---

### 1.4 Participación en Rutas o Formación Externa — ✅ Confirmado

- Cuando a un trabajador **le toca participar en una Ruta o en una actividad de Formación externa**, ese día **no asiste de forma presencial** a la sede.
- Esta ausencia **no es falta** — es actividad institucional asignada.
- **Comportamiento esperado del sistema (D-RH17):** al evaluar la asistencia del día, el sistema debe **detectar automáticamente** si el empleado tiene una ruta o una formación externa asignada ese día. Si la tiene, ese día **goza de no asistencia presencial** (estado "En Ruta / En Actividad"), y no se contabiliza como ausencia ni como presencia normal.
- A algunos empleados se les puede **pedir apoyo** para estas actividades aunque no estén asignados de origen (detalle fino pendiente).

---

### 1.5 Resumen de Modalidades de Horario

| Modalidad | Quién aplica | Días que asiste | Horario |
|-----------|-------------|-----------------|---------|
| Estándar | Personal general | Todos los hábiles | 8am – 2pm |
| Servicios Generales Grupo A | Servicios Generales (Grupo A) | Días alternos de la secuencia | 8am – 2pm |
| Servicios Generales Grupo B | Servicios Generales (Grupo B) | Días alternos de la secuencia | 8am – 2pm |
| Recepción / OAC Sub-grupo 1 | OAC | Todos los hábiles | 7am – 12pm |
| Recepción / OAC Sub-grupo 2 | OAC | Todos los hábiles | 10am – 2pm |
| Horario ajustado | Estudiantes / personas con discapacidad | Según corresponda | Especial (ver 3.3) |
| En Ruta / Actividad | Cualquier empleado asignado ese día | El día de la actividad | Fuera de sede |

---

## 2. Tipos de Empleado y Vinculación

### 2.1 Principio General — ✅ Confirmado

**Todo empleado nuevo es Contratado**, sin excepción. La **única** forma de ingresar directamente como **Fijo** es que ya venga fijo desde su origen (un empleado de Alcaldía o Gobernación que ya era fijo allí, llegando por comisión de servicio).

> **Impacto técnico:** el campo `empleados.tipo_contrato` tiene DEFAULT `'Fijo'`. Debe corregirse a `'Contratado'` (ver hoja de ruta, sección 12).

---

### 2.2 Modelo de Tipo de Contrato + Comisión de Servicio — ✅ Confirmado (con corrección de modelo)

**Regla (aclarada 2026-06-06):** "Comisión de Servicio" **NO** es un tipo de contrato ni una decisión manual: **se deriva del origen**. Un empleado **es comisión de servicio si y solo si proviene de Alcaldía o Gobernación**; si proviene de **IMATUR**, no es comisión. Es ortogonal a la estabilidad (Fijo/Contratado).

- **Tipo de contrato (estabilidad):** `Fijo` o `Contratado`.
- **Comisión de servicio = (`institucion_origen` ∈ {Alcaldía, Gobernación})**. Un empleado en comisión puede ser **Fijo o Contratado** según su estatus en el ente de origen.
- **No existen "Suplentes"** en IMATUR (D-RH19: `'Suplente'` deprecado).

> ✅ **Implementado (migración 025; regla afinada 2026-06-06):** `empleados.tipo_contrato` (Fijo/Contratado, DEFAULT Contratado) + `institucion_origen` (Alcaldía/Gobernación/IMATUR). `es_comision_servicio` **se deriva** del origen (`origen ≠ IMATUR`) en `EmpleadosController` — ya no es un checkbox manual; en el asistente se muestra como indicador de solo lectura. Ver D-RH27/D-RH31.

| Concepto | Valores | Nota |
|----------|---------|------|
| Tipo de contrato (estabilidad) | Fijo · Contratado | Todo nuevo entra Contratado |
| Origen / Institución (nómina) | Alcaldía · Gobernación · IMATUR | De dónde proviene y quién paga |
| Comisión de servicio | **Derivado** | = (origen ≠ IMATUR). Alcaldía/Gobernación ⇒ Sí; IMATUR ⇒ No |

**Edad permitida (aclarada 2026-06-06):** comisión de servicio (Alcaldía/Gobernación) **18–70**; personal IMATUR **18–65**. Mínimo 18 siempre.

**Diferencias clave Contratado vs Fijo:**
- El **Fijo** goza del derecho a **retornar a su institución de origen** (Alcaldía/Gobernación).
- El **Contratado** que acumula **3 amonestaciones** es causa de **despido** (ver 2.5).
- Para registrar como **Fijo** a un empleado de **comisión de servicio** que aún **no cumple el tiempo estipulado**, debe presentar la **carta de asignación** de su institución de origen.
- Los empleados de **origen IMATUR** llegan a Fijo **solo por tiempo de servicio** — **no** presentan carta de asignación.

---

### 2.3 Transición de Contratado a Fijo — ✅ Confirmado

La transición se alcanza por **años de servicio acumulados**:

| Origen | Años de servicio (referencia) |
|--------|-------------------------------|
| Alcaldía | 5 a 6 años |
| Gobernación | 3 a 6 años |
| IMATUR (sin vínculo previo) | 5 a 6 años |

- Los años de servicio previos en la institución de origen **se suman** a los años en IMATUR.
- **La transición NO es automática (D-RH20):** el tiempo puede variar. Se hace una **indicación a RRHH y a la Directora** para que el empleado pase a Fijo. El sistema debe **alertar/sugerir** la elegibilidad, pero la promoción la confirma RRHH/Dirección manualmente.

---

### 2.4 Cómo Ingresa una Persona como Empleado — ✅ Confirmado

| Origen | Documentos mínimos al ingresar | Contrato inicial |
|--------|-------------------------------|------------------|
| Persona particular (sin vínculo con ente público) | Solo CV | Contratado |
| Proveniente de Alcaldía/Gobernación (comisión de servicio) | CV + documentos de dependencia | Contratado, o **Fijo si trae carta de asignación** |

---

### 2.5 Amonestaciones y Faltas Injustificadas — ✅ Confirmado (refina D-RH13)

- Una **amonestación** es, por lo general, la **acumulación de faltas injustificadas** — habitualmente una amonestación equivale a cierto número de faltas injustificadas, **pero esto puede variar** según el caso.
- **3 amonestaciones** son causa de despido para un empleado **Contratado**.
- El sistema debe **contabilizar faltas injustificadas** y **registrar amonestaciones** por empleado (ver D-RH28 para la regla exacta falta→amonestación).

---

### 2.6 Asignación de Departamento, Cargo, Horario y Grupo — ✅ Confirmado

- La asignación la realiza **Talento Humano** al registrar al empleado.
- La **Directora General (María Maza)** también tiene facultad de asignar/modificar.
- La asignación depende del **cargo/oficio requerido** y del origen del empleado (comisión de servicio vs directo).

---

## 3. Registro de Nuevos Empleados

### 3.1 Datos del Empleado — ✅ Confirmado (campos según fuentes)

Los campos a registrar surgen del **`FORMATO DE REGISTRO DE DATOS.xls` (hoja LISTADO GENERAL)** y de la **Ficha Técnica del Trabajador**. La mayoría son obligatorios; algunos opcionales (cuando aplica).

**Bloque — Datos Personales:**
| Campo | Obligatorio | Nota |
|-------|-------------|------|
| Nombres y apellidos | Sí | |
| Cédula | Sí | |
| RIF | Cuando aplica | Formato `V-XXXXXXXXX` |
| Sexo / Género | Sí | M / F (ya normalizado, migración 023) |
| Fecha de nacimiento | Sí | Restricciones de edad (3.1.1) |
| Estado civil | Sí | Soltero/a, Casado/a, Concubino/a |
| Dirección de habitación | Sí | |
| Parroquia | Sí | |
| N° teléfono | Sí | |
| Correo electrónico | Cuando aplica | |
| Discapacidad | Sí (Sí/No) | Si aplica → recaudo + horario ajustado |
| Centro de votación | Opcional | Dato comunitario (ver D-RH25) |
| Consejo comunal | Opcional | Dato comunitario |
| Comuna | Opcional | Dato comunitario |

**Bloque — Formación / Datos Académicos:**
| Campo | Nota |
|-------|------|
| Nivel académico / Grado académico | Bachiller, Profesional, Técnico Medio… |
| Profesión | Ej. "Lic. en Administración" |
| Nombre del título | |
| Fecha de graduación | |
| Institución | |
| Etapa (Primaria / Media / Diversificada / Técnico Medio) | Checkbox en ficha |
| Cursos realizados | Tabla: Institución · Curso · Inicio · Culminación |

**Bloque — Carga Familiar:** ver sección 3.2.

**Bloque — Datos Laborales (actuales):**
| Campo | Nota |
|-------|------|
| Cargo | |
| Área / Departamento | |
| Institución / Nómina | Alcaldía / Gobernación / IMATUR |
| Tipo de personal | Fijo / Contratado |
| Clasificación | **Empleado / Obrero** (ver D-RH26) |
| Fecha de ingreso | Base para tiempo de servicio |
| Tiempo de servicio | Derivado (calculado) |
| Estatus | Activo / Inactivo |
| Uniforme (Sí/No) + tallas camisa/pantalón/zapato | Ver D-RH35 |

**Bloque — Experiencia Laboral (trabajos anteriores) (D-RH23):**
| Campo | Nota |
|-------|------|
| Organismo | Empleador anterior |
| Cargo | |
| Inicio | |
| Culminación | |

> Estos datos se visualizan en la plantilla de **Ficha Técnica**, de la cual el sistema debe poder **generar el documento** con la información registrada (ver 3.3 y sección 8).

#### 3.1.1 Restricciones de Edad — ✅ Confirmado
- Mínimo: **18 años**.
- Máximo general: **65 años**.
- **Excepción:** se permite registrar mayor de 65 años **únicamente** si viene por comisión de servicio (Alcaldía/Gobernación), y el **límite absoluto sigue siendo 70 años** en cualquier caso.

---

### 3.2 Carga Familiar — ✅ Confirmado (uso) / ⚠️ Parcial (beneficios)

Se registran los familiares del empleado. Estructura según la Ficha Técnica:

| Campo |
|-------|
| Nombre y apellido |
| Cédula |
| Fecha de nacimiento |
| Parentesco (padre, madre, cónyuge/concubino, hijo/a) |

**Para qué se usa (D-RH21 — confirmado que SÍ importa):**
- Información familiar del empleado para el expediente.
- Base para un eventual **bono de escolaridad** y otros beneficios (si llegan a aplicar).
- Tener los datos a mano para **reportes** rápidos.
- **Justificación de faltas por servicio médico a familiar** (ver 4.2): el familiar debe estar en la carga familiar.

> Requiere **tabla dedicada** (no campo de texto). Las preguntas finas sobre beneficios específicos se registran como **D-RH29** (próximo feedback).

**Sub-recaudos de carga familiar (del checklist de expediente):**
- Copia de cédula y partida de nacimiento de la pareja/cónyuge/concubino.
- Copia de acta de matrimonio / concubinato.
- Copia de cédula y partida de nacimiento del padre y/o madre.
- Copia de cédula y partida de nacimiento de los hijos.

---

### 3.3 Expediente y Documentos — ✅ Confirmado

El expediente se organiza **por departamento** (antes se organizaba por tipo de empleado). El sistema asignará un **código interno** al trabajador (hoy la institución no lo tiene).

**Recaudos del expediente (checklist oficial):**
| Documento |
|-----------|
| Currículum (CV) |
| Copia de cédula ampliada y centrada |
| Copia de la partida de nacimiento |
| Copia del título de Bachiller / Profesional |
| Copia del fondo negro del título |
| RIF |
| Referencia bancaria |
| Recaudos de carga familiar (ver 3.2) |
| **Ficha Técnica del Trabajador** (generada por el sistema) |
| Documentación de estudiante / discapacidad (cuando aplica) |

> Nota física: la institución arma el expediente en **carpeta marrón tipo oficio con ganchos**.

**Comportamiento del sistema (D-RH22 — confirmado, modelo híbrido):**
1. **Subida de archivos digitales** (PDF/imagen) de cada recaudo.
2. **Convención de nombre** ligando el archivo al empleado y al tipo de documento, p. ej. `Partida_Empleado_01` (ID del empleado + tipo de documento).
3. **Checklist con detección de faltantes:** el sistema debe **detectar y avisar** qué recaudos del expediente faltan por entregar.
4. **Generación de la Ficha Técnica:** el sistema genera el documento de Ficha Técnica con los datos registrados, y esa ficha forma parte del expediente. De la ficha se extraen los datos de carga familiar y experiencia laboral.

**Personal estudiante o con discapacidad — ✅ Confirmado:**
- Se le solicita la **documentación correspondiente** en el expediente.
- Se le **ajusta el horario** según corresponda (ver D-RH36 para el modelo del horario ajustado).

---

### 3.4 Datos Laborales Anteriores — ✅ Confirmado

Los datos del empleo anterior se capturan en el bloque **Experiencia Laboral** de la Ficha Técnica (Organismo · Cargo · Inicio · Culminación). Ver tabla en 3.1.

---

## 4. Permisos, Reposos y Ausencias

### 4.1 Autoridad de Aprobación — ✅ Confirmado

| Tipo de permiso | Quién aprueba / firma |
|-----------------|-----------------------|
| Permisos laborales ordinarios | Directora de Talento Humano |
| Permisos especiales | Directora General (María Maza) |
| Firma formal de cualquier permiso | Directora de Talento Humano **o** Directora General |

- Talento Humano **siempre oficializa** el permiso, incluso cuando la aprobación viene de la Dirección General.

---

### 4.2 Tipos de Ausencia / Permiso — ✅ Confirmado (taxonomía real)

Taxonomía consolidada de `REPOSOS, PERMISOS Y VACACIONES.xls` + respuestas del usuario:

| Tipo | Descripción | Documentación / Nota |
|------|-------------|----------------------|
| **Reposo médico** | Incapacidad médica del propio empleado | Reposo médico (días/horas: 48HRS, 72HRS, 7/10/21 días…) + diagnóstico |
| **Permiso médico a familiar** | Atención a familiar cercano (padre, madre, hijo/a, cónyuge) | Informe médico / constancia + familiar en carga familiar |
| **Permiso por diligencia** | Diligencia importante | Notificación previa al jefe inmediato |
| **Permiso por duelo** | Fallecimiento de familiar | — |
| **Permiso por maternidad/paternidad** (post-parto) | "PERMISO POST" en la fuente (p. ej. 6 meses) | — |
| **Permiso personal** | Permiso personal del empleado | — |
| **Permiso por estudios** | "EN CLASES" en la fuente | — |
| **Vacaciones** | Ver sección 5 | Días según período |
| **Falta sin justificar** | "SIN JUST." en la fuente | Genera amonestación (ver 2.5) |

**Atributos de cada registro (según la fuente):**
- Empleado · Fecha de inicio · Tipo · Tiempo (duración: horas/días/meses) · Hasta (fecha fin) · Estatus (**En curso / Concluido**) · Observación (diagnóstico, "debe reincorporarse el…", etc.).

> ⚠️ **Modelo (D-RH32):** Reposo médico y Permiso conviven en la misma fuente con un campo `TIPO`. A confirmar si se modelan como tipos dentro de `permisos_laborales` o como entidades separadas. Como entidades separadas, se debe poder difererenciar por select caudno es un permiso de reposo medico o un permiso.

---

### 4.3 Justificadas vs Injustificadas — ✅ Confirmado (cierra D-RH13)

- Se gestionan **por separado**.
- Ambas las aprueba/registra **Talento Humano**.
- Las faltas injustificadas alimentan las **amonestaciones** (3 amonestaciones = despido para Contratados, ver 2.5).
- El sistema debe **contabilizar faltas injustificadas** por empleado.

---

## 5. Vacaciones

### 5.1 Días de Descanso — ✅ Confirmado (cierra D-RH04 parcial)

- Aunque IMATUR es organismo turístico, los **fines de semana son días de descanso** para los trabajadores (aplica a todos los horarios, incluidos grupos A/B).
- **Excepción:** eventos turísticos/culturales de relevancia → se puede pedir a ciertos trabajadores que asistan:

| Evento |
|--------|
| Carnaval |
| Semana Santa |
| Día de Santa Inés |
| Cruz de Mayo |

---

### 5.2 Acumulación de Días — ✅ Confirmado (cierra D-RH06)

- Las vacaciones **no se pierden** si no se disfrutan en el período.
- Los días no disfrutados **se acumulan** y se suman al total disponible.
- Pueden tomarse **en cualquier momento** mientras estén activas.
- Si al calcular no se escogen nuevamente, se hace **sumatoria automática** para el siguiente período.

---

### 5.3 Vacaciones por Comisión de Servicio — ⚠️ Parcial

- Los empleados en comisión de servicio (Alcaldía/Gobernación) tienen las vacaciones **coordinadas entre IMATUR y la institución de origen**.
- Se toman en cuenta los períodos de comisión de servicio para el cálculo.

> ⚠️ **Pendiente:** fórmula exacta de días por años de servicio y la coordinación con Alcaldía/Gobernación. Ver D-RH04, D-RH05, D-NEW05.

---

## 6. Asistencia

### 6.1 Qué se Registra — ✅ Confirmado (actualiza D-RH09, D-RH12)

- **Sí se calculan horas trabajadas**, pero **solo para reporte e indicadores** — **no** influyen en cálculo de pago/nómina. (Antes se había dicho que no se calculaban; esto queda actualizado.)
- Se controla **puntualidad** (llegó tarde) y **ausentismo** (no vino).
- **No se manejan horas extras** en el sistema.
- La asistencia sigue el **patrón toggle** existente (entrada / salida).
- El día de Ruta/Formación externa se marca diferenciado (ver 1.4).

> ⚠️ Ver D-RH33: definir cómo se computan las horas (entrada/salida real vs horario asignado) y la tolerancia de puntualidad.
El calculo se hace en horas trabajadas por semana o día sea cual sea el caso, se hace un calculo correspondiente, la puntualidad se basa en la hora en que el trabajador entro en jornada (marco entrada en asistencia) segun su horario y 15 minutos luego de su horario, ya luego de ese tiempo se puede marcar como inpuntualidad (aunque esto podria ser configurable en la pantalla del sistema de configuracion para adelantar a 30 minutos o a menos como 5 minutos).

---

### 6.2 Formato de Asistencia Físico — 📋 Pendiente de mejora

- El formato actual es una **hoja semanal rudimentaria**: `N° · Nombre y Apellido · Cédula · [Lunes…Viernes, con columna HORA por día]`, donde cada quien firma y anota la hora.
- Las hojas están **separadas por tipo de personal** (p. ej. "Personal Matutino", "Personal de Gobernación").
- **Objetivo:** digitalizar y **mejorar** ese formato en el sistema (ya se tienen las imágenes de referencia). El diseño de la vista de asistencia debe partir de esa estructura pero modernizarla.
![alt text](<WhatsApp Image 2026-06-04 at 4.24.46 PM (1).jpeg>)
---

## 7. Estructura Organizativa, Cargos y Traspasos

### 7.1 Organigrama y Cargos — ✅ Confirmado (requiere guía formal)

La estructura surge del **organigrama** (hoja ORGANIGRAMA del Excel). Cada **Dirección** está adscrita bajo la Presidencia, y cada Dirección equivale a un **departamento**. Dentro de cada Dirección hay **Coordinaciones**, y bajo cada Coordinación hay **personal adscrito**.

**Jerarquía de cargos:**
```
Presidenta (Dirección General — María Maza)
  └── Direcciones (cada una = un departamento)
        └── Coordinaciones
              └── Trabajadores adscritos a una coordinación
```

- **Director** y **Coordinador** son **cargos distintos** con responsabilidades diferentes dentro de la estructura.
- Coordinaciones identificadas en la fuente: Promoción Turística, Presupuesto, Registro y Selección, Contabilidad, Bienestar Social, Compra/Bienes y Servicios, Nómina, Calidad y Servicio, Formación Turística, entre otras.

> ✅ **Implementado (migración 027, 2026-06-04):** `departamentos` es jerárquico (`id_padre` + `tipo_unidad`), con el organigrama oficial sembrado y gestionable desde `/departamentos/index`. El liderazgo Director/Coordinador se deriva del cargo del empleado (cargos `Director`/`Coordinador`/`Presidenta`). Fuente: Manual Descriptivo de Cargos (abril 2024).

---

### 7.2 Traspaso de Personal entre Departamentos — ✅ Confirmado

1. Se convoca una **reunión de directores y coordinadores** del área involucrada.
2. La **decisión final** la toma la **Directora General (María Maza)** o la **coordinadora del departamento de origen**.
3. Talento Humano ejecuta el cambio en el sistema.

---

## 8. Documentos Generados por RRHH

### 8.1 Documentos y Log — ✅ Confirmado

El sistema debe **generar y/o registrar** los siguientes documentos, guardando **timestamp de solicitud** y comportándose como un **log/historial** por empleado (con correlativo, similar al de oficios de otros módulos):

| Documento | Nota |
|-----------|------|
| Constancias de trabajo | Remitidas por RRHH; log con fecha/hora |
| Permisos laborales | Registro con timestamp (ver `REPOSOS, PERMISOS Y VACACIONES.xls` como referencia) |
| **Ficha Técnica del Trabajador** | Generada con los datos del empleado |
| Formato de asistencia | Generado/exportable desde el sistema |
| Reportes del sistema | Indicadores RRHH, asistencia, permisos, vacaciones |
| **Nómina para enviar a Alcaldía/Gobernación** | Cuando se obtenga la definición (ver D-RH34) |

---

## 9. Estructura de Autoridad en Talento Humano

| Cargo | Nombre | Facultades en RRHH |
|-------|--------|-------------------|
| Directora General / Presidenta | María Maza | Aprueba permisos especiales, asigna empleados, decisión final en traspasos, confirma paso a Fijo |
| Directora de Talento Humano | — | Oficializa permisos, firma permisos laborales, registra y gestiona expedientes, emite constancias |
| Coordinador de departamento | — | Participa en decisiones de traspaso de su área |
| Jefe inmediato | — | Recibe notificación de permisos por diligencia |

---

## 10. Hoja de ruta R-1…R-12 — **cerrada**

> Las secciones 1 a 9 de arriba son el **relevamiento con la institución** y siguen vigentes: son la
> fuente de las reglas de horarios, tipos de empleado, expediente, permisos, vacaciones y organigrama.
> Lo que sigue era el seguimiento de su implementación, y ya se completó.

**Todo R-1…R-12 está construido**, salvo la última pieza de R-11:

| # | Subtarea | Estado |
|---|---|---|
| R-1 | Organigrama / cargos / departamentos jerárquicos | ✅ mig. 027 · 035 |
| R-2 · R-2b | Campos de empleado · Ficha Técnica · asistente multi-paso | ✅ mig. 026 · 030 |
| R-3 | Tipo de contrato + origen + comisión de servicio derivada | ✅ mig. 025 |
| R-4 | Carga familiar | ✅ mig. 026 |
| R-5 | Expediente y recaudos | ✅ mig. 033 |
| R-6 | Horarios y grupos A/B | ✅ mig. 028 |
| R-7 | Asistencia, puntualidad y ausentismo | ✅ mig. 029 |
| R-8 | Permisos y reposos · **vacaciones (días)** | ✅ mig. 032 · 045/046 |
| R-9 | Faltas y amonestaciones, con escalado | ✅ mig. 031 · 048 |
| R-10 | Constancias multi-tipo con correlativo | ✅ mig. 034 |
| R-11 | **Nómina** | ⚠️ **Parcial.** Motor de cálculo, quincena y bono vacacional ✅ (mig. 072/073). 🔒 Falta la **Liquidación de Prestaciones Sociales** |
| R-12 | Egreso / desincorporación y reingreso con histórico | ✅ mig. 036 |

**Decisiones RRHH:** las 36 preguntas D-RH01…D-RH36 de las sesiones del 2026-06-02/04 quedaron
**todas cerradas** y sus respuestas están incorporadas en las secciones 1-9 de este documento y en
`REGLAS_NEGOCIO_RRHH.md`. Las únicas que siguen abiertas se rastrean en **`BACKLOG.md` §3.1**
(N-1, N-2, N-3 de Nómina) — no se duplican aquí.

---

*Documento de relevamiento. Las secciones 1-9 se actualizan si la institución aporta información
nueva; el seguimiento de lo pendiente vive en `BACKLOG.md`.*