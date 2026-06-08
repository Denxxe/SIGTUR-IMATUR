# Modelo de Negocio — RRHH (Talento Humano)

**Creado:** 2026-06-02  
**Última actualización:** 2026-06-04  
**Estado:** En construcción — relevamiento activo con fuentes documentales  
**Complementa:** `REGLAS_NEGOCIO_RRHH.md` (reglas técnicas) · `ESTRUCTURA_ORGANIZATIVA.md` (organigrama)  
**Cierra preguntas de:** `REGISTRO_NEGOCIO.md` (fuente única de preguntas/decisiones)

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

## 10. Preguntas Abiertas / Provisionales (Sesión 2026-06-02 → 2026-06-04)

| ID | Sección | Estado | Pregunta / Decisión |
|----|---------|--------|---------------------|
| D-RH16 | Horarios | 🟢 Provisional | Por ahora NO hay calendario de feriados; cálculo A/B intercalado simple. Revisión futura. |
| D-RH17 | Asistencia | ✅ Cerrada | El sistema detecta ruta/formación externa del día → "no asistencia presencial". Apoyo opcional a otros empleados. |
| D-RH18 | Asistencia | 📋 Recibido | Formato físico recibido (imágenes); pendiente diseñar versión mejorada. | generada segun lo sacado en la imagen
| D-RH19 | Contrato | ✅ Cerrada | No hay suplentes; eliminar/deprecar `'Suplente'`. |
| D-RH20 | Contrato | ✅ Cerrada | Paso a Fijo NO automático; indicación a RRHH/Directora; el sistema solo alerta elegibilidad. |
| D-RH21 | Carga familiar | ✅ Cerrada | Sí importa: info + bono escolaridad + beneficios + justificación médica. Tabla dedicada. |
| D-RH22 | Expediente | ✅ Cerrada | Híbrido: subir archivos (nombre `Tipo_Empleado_ID`) + checklist con detección de faltantes + generar ficha técnica. |
| D-RH23 | Expediente | ✅ Cerrada | Datos laborales anteriores = bloque Experiencia Laboral de la ficha. |
| D-RH24 | Permisos | ✅ Cerrada | Tipos: médico-familiar, diligencia, duelo, maternidad/paternidad, personal, estudios (+ reposo, vacaciones, sin justificar). |
| ~~D-RH25~~ | Datos empleado | ✅ Resuelta | Campos Ficha + extras (mig.026) + tallas de uniforme y datos comunitarios (mig.030). **Asistente multi-paso implementado** (`empleados/form.php`, 5 pasos con `localStorage` + resumen final). R-2b completo. |
| ~~D-RH26~~ | Datos empleado | ✅ Resuelta | **Clasificación Empleado/Obrero** = campo separado (`empleados.clasificacion`), **implementado mig.026**. Sus implicaciones en prestaciones/uniforme se definirán con D-RH35 y vacaciones. |
| ~~D-RH27~~ | Contrato | ✅ Resuelta (afinada 2026-06-06) | `tipo_contrato` (Fijo/Contratado) + `institucion_origen` (Alcaldía/Gobernación/IMATUR). **`es_comision_servicio` se deriva del origen** (= origen ≠ IMATUR), no es campo manual. Migración 025. |
| ~~D-RH28~~ | Amonestaciones | ✅ Resuelta | El sistema **registra y cuenta amonestaciones** por empleado. Referencia: 3 faltas injustificadas ≈ 1 amonestación, pero el sistema **solo notifica las faltas**; las **amonestaciones las crea RRHH manualmente** según corresponda. **3 amonestaciones = despido** (Contratado). |
| ~~D-RH29~~ | Carga familiar | ✅ Resuelta | El sistema **solo almacena los datos** de carga familiar para reportes; **no calcula** beneficios (bono escolaridad, etc.). |
| ~~D-RH30~~ | Organigrama | ✅ Recibido (Manual Descriptivo de Cargos, abril 2024) | Jerarquía oficial: **Presidencia → 3 Direcciones** (Planificación y Gestión Turística · Administración · Talento Humano) **→ Coordinaciones**; + unidades de staff bajo Presidencia (Dirección General, OAC, Secretaría, Consultoría Jurídica, Auditoría Interna, Relaciones Inter-Institucionales). Listo para modelar jerarquía (R-1). |
| ~~D-RH31~~ | Datos empleado | ✅ Resuelta | Institución = Nómina: un solo campo `institucion_origen` (Alcaldía/Gobernación/IMATUR). De él se deriva la comisión de servicio (≠ IMATUR) y el tope de edad (65 IMATUR / 70 comisión). |
| ~~D-RH32~~ | Permisos | ✅ Resuelta (implementado mig.032) | Reposo y Permiso diferenciados por `categoria` (select) en `permisos_laborales`; taxonomía en `tipo_permiso`. Módulo `PermisosController`. |
| ~~D-RH33~~ | Asistencia | ✅ Resuelta | Horas trabajadas se calculan **por día y por semana** (según el caso, solo para reporte/indicadores). **Puntualidad:** se compara la hora de marcaje de entrada contra el horario asignado + **15 min de tolerancia** (por defecto); pasado ese margen se marca **impuntualidad**. La tolerancia es **configurable** en Configuración (p. ej. 5/15/30 min). |
| **D-RH34** | Nómina | ❓ Abierta | ¿El sistema debe generar la nómina para Alcaldía/Gobernación? ¿Formato? (alto impacto, futuro) |
| ~~D-RH35~~ | Uniforme | ✅ Resuelta | Las tallas de uniforme (camisa/pantalón/zapato) **solo se registran**; no se controla dotación/entrega. |
| ~~D-RH36~~ | Horario | ✅ Resuelta | El **horario ajustado** se modela como un horario más, creado por Administrador/RRHH y **asociado al/los empleado(s)** que corresponda (no es un caso especial separado: el catálogo de `horarios` admite horarios personalizados asignables). |

---

## 11. Preguntas Cerradas — Correlación con `preguntas_modelo_negocio.md`

| ID original | Estado | Respuesta resumida |
|-------------|--------|--------------------|
| D-RH01 | ✅ Cerrada | Modalidades: Estándar 8–2; ServGen A/B rotación diaria; OAC sub-grupos 7–12 / 10–2 |
| D-RH02 | ✅ Cerrada | Taxonomía de permisos confirmada (ver 4.2) |
| D-RH03 | ✅ Cerrada | TH aprueba ordinarios; Directora General los especiales; firma TH o Directora |
| D-RH04 | ⚠️ Avanzada | Descanso = fin de semana; vacaciones acumulables. Falta fórmula por años de servicio |
| D-RH05 | ⚠️ Avanzada | Hay acumulación automática; falta confirmar si el cálculo es del sistema o manual |
| D-RH06 | ✅ Cerrada | Vacaciones no disfrutadas se acumulan, nunca se pierden |
| D-RH08 | ✅ Cerrada | Horarios distintos por tipo/departamento |
| D-RH09 | ⚠️ Actualizada | Sí se calculan horas, pero solo para reporte/indicadores (no para pago) |
| D-RH12 | ✅ Cerrada | No se manejan horas extras |
| D-RH13 | ✅ Cerrada | Justificadas/injustificadas por separado; injustificadas → amonestaciones |
| D-RH16–D-RH24 | Ver sección 10 | — |
| D-NEW04 | ✅ Cerrada | Tipos = estándar venezolanos confirmados (ver 4.2) |
| D-NEW05 | ⚠️ Abierta | Fórmula LOTTT de vacaciones pendiente de confirmar para IMATUR |

---

## 12. Hoja de Ruta de Implementación (subtareas por sección)

Cambios al sistema derivados de este modelo, agrupados por sección. El orden sugiere dependencias (fundaciones primero). Cada subtarea indica si está **bloqueada** por preguntas abiertas.

| # | Subtarea | Alcance principal | Bloqueado por |
|---|----------|-------------------|---------------|
| R-1 | **Organigrama / Cargos / Departamentos** ✅ **HECHO (mig.027)** | `departamentos` jerárquico (id_padre+tipo_unidad); organigrama oficial sembrado; cargos +Presidenta/Coordinador; liderazgo derivado del cargo | ✅ Resuelto (D-RH30) |
| R-2 | **Campos de empleado / Ficha Técnica** ✅ **HECHO (mig.026)** | `personas` +RIF/estado civil/discapacidad/formación académica; `empleados` +clasificación; tablas hijas carga_familiar/cursos/experiencia con UI en expediente; generador de Ficha Técnica imprimible | ✅ Resuelto |
| R-2b | **Asistente multi-paso + uniforme/comunitarios** ✅ **HECHO (mig.030)** | Wizard de página completa (`empleados/form.php`) crear/editar con localStorage + resumen; `personas` +centro_votacion/consejo_comunal/comuna; `empleados` +uniforme/tallas | ✅ Resuelto (D-RH25/D-RH35) |
| R-3 | **Tipo de contrato (fix base)** ✅ **HECHO (mig.025)** | DEFAULT → 'Contratado'; 'Suplente'/'Comisión de Servicio' deprecados; +`institucion_origen` +`es_comision_servicio` | ✅ Resuelto (D-RH27) |
| R-4 | **Carga Familiar** ✅ **HECHO (mig.026)** | Tabla + UI en expediente; solo almacena datos (D-RH29 ✅). Sub-recaudos (documentos) = parte de R-5 | ✅ Resuelto |
| R-5 | **Expediente / Documentos** ✅ **HECHO (mig.033)** | Tabla `expediente_documentos`; subida PDF/imagen con convención `Tipo_Empleado_ID`; checklist de recaudos con detección de faltantes obligatorios; descarga/eliminar en el expediente | ✅ Resuelto |
| R-6 | **Horarios y Grupos** ✅ **HECHO (mig.028)** | Catálogo `horarios` con CRUD + seed de modalidades; `empleados.grupo_rotacion` (A/B); horarios personalizados asignables (D-RH36 ✅); config de tolerancia preparada | ✅ Resuelto |
| R-7 | **Asistencia** ✅ **HECHO (mig.029)** | Puntualidad (`minutos_tarde` vs horario + tolerancia configurable), resumen del día (presentes/impuntuales/ausentes/en actividad), horas trabajadas (derivadas), detección En Ruta/Formación externa, vista renovada | ✅ Resuelto |
| R-8 | **Permisos / Reposos** ✅ **HECHO (mig.032)** / Vacaciones ⏳ | Permisos y reposos implementados (`PermisosController`): categoría Reposo/Permiso (D-RH32 ✅), taxonomía, duración, estatus En curso/Concluido derivado, flujo aprobar/rechazar/anular. **Vacaciones pendiente** (fórmula D-RH04/05, D-NEW05) | ⚠️ Permisos/reposos ✅; vacaciones bloqueado |
| R-9 | **Amonestaciones** ✅ **HECHO (mig.031)** | Tablas `faltas` y `amonestaciones` (registro manual RRHH); roster con conteos + semáforo; detalle por empleado; alerta "causa de despido" a las 3 amonestaciones | ✅ Resuelto (D-RH28) |
| R-10 | **Documentos generados / Constancias** ✅ **HECHO (mig.034)** | Constancia de trabajo imprimible con correlativo `CONST-NNN/AAAA` + historial por empleado en el expediente | ✅ Resuelto |
| R-11 | **Nómina (futuro)** | Generación de nómina para Alcaldía/Gobernación | D-RH34 |
| R-12 | **Egreso / desincorporación** ✅ **HECHO (mig.036)** | Baja por renuncia/despido/jubilación/fin de contrato/fallecimiento/otro: marca `fecha_egreso`+`motivo_egreso` (no borra), histórico consultable (pestaña Egresados), tiempo de servicio en expediente/constancia, reingreso con historial (`empleados_egresos`) | ✅ Resuelto |

> **Estado (2026-06-04):** Hechos R-2, R-3, R-4. Desbloqueados R-1, R-5, R-6, R-7, R-9 y la parte de permisos/reposos de R-8. Bloqueados: vacaciones (R-8: D-RH04/05/NEW05) y nómina (R-11: D-RH34).
>
> **Recomendación de continuación:** **R-6 (Horarios y Grupos)** → **R-7 (Asistencia)** es el siguiente bloque de mayor valor (núcleo operativo: puntualidad/ausentismo), ya totalmente especificado (D-RH33/D-RH36). Conviene agregar los campos de empleado restantes (grupo A/B, uniforme, comunitarios) **antes** de rehacer el formulario como asistente multi-paso (R-2b), para wizardizar una sola vez con el set de campos completo. Luego R-9 (amonestaciones, se nutre de asistencia) y R-8 permisos/reposos. R-1 (organigrama) puede hacerse en paralelo cuando se quiera reestructurar `departamentos`.

---

*Documento vivo — actualizar a medida que se reciba más información de la institución.*
