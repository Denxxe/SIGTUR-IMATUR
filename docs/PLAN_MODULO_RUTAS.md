# Plan de reconstrucción — Módulo de Rutas Turísticas

**Fecha:** 2026-09-03 · **Ampliado:** 2026-09-17 · **Origen:** respuestas del cliente a
**R-01 … R-64** (`docs/PREGUNTAS_DESCUBRIMIENTO_Bienes_Rutas.md`, Parte 2) + **7 formatos reales**
(`docs/formatos/rutas_*`)
**Estado:** análisis cerrado · **T-A, T-B, T-I, T-G y T-D construidas (mig. 078-080)**, resto pendiente
**Migraciones vigentes:** hasta **080**

> ## ⚠️ 2026-09-17 — Llegaron R-17…R-64 y los formatos: **leer §1-bis antes que nada**
>
> El plan de septiembre se escribió con 16 de 64 respuestas. Ahora hay **57**, más los documentos
> reales. **Nada de lo decidido se cae** —la separación catálogo/ejecución se confirma—, pero
> aparecen **tres piezas que no estaban en el plan** y varias decisiones de alcance que lo
> **reducen**. Ver **§1-bis**.

---

## 0. Resumen en una página

El cliente respondió las **15 preguntas que definen la estructura de datos** (secciones A, B y C).
La conclusión es corta y cara:

> **El módulo está construido sobre una premisa falsa.** Cada fila de `rutas` es hoy *una salida
> concreta* (tiene `fecha_visita`, `hora_visita`, `id_facilitador`, `cupo_maximo`). El cliente dice
> que **existe un catálogo de rutas** (R-08) y que dos salidas de Cumaná Histórica son
> **"la misma ruta ejecutada 2 veces"** (R-07). Son **dos entidades**, no una.

La prueba de que la mezcla ya dolía está en el propio esquema: de los cuatro estados actuales,
`Activa` / `Inactiva` / `En Mantenimiento` describen **una ruta del catálogo** ("¿se ofrece o no?")
y `Finalizada` describe **una salida** ("ya se ejecutó"). Cuatro valores en una columna para dos
ciclos de vida distintos — el síntoma clásico de dos tablas metidas en una.

**Lo bueno:** `rutas` tiene **2 filas** en la base de datos. La migración de datos es trivial. El
costo está en el código (modelo, controlador, 5 vistas, reportes), no en los datos.

**¿Se puede empezar a desarrollar?** **Sí — desde el 2026-09-17, TODO.** Con R-17…R-64 respondidas
y los siete formatos en mano, **no queda ninguna fase esperando al cliente**. Ver §1-bis y §7.

---

## 1. Qué revelaron las respuestas

### 1.1 Cinco hechos nuevos que cambian el diseño

| # | Hecho | De dónde sale | Qué rompe |
|---|-------|---------------|-----------|
| 1 | **Existe un catálogo reutilizable** de rutas, con sus puntos, inicio y fin | R-07, R-08 | 🔴 La tabla `rutas` completa |
| 2 | **Varias salidas el mismo día**, de la misma ruta o de rutas distintas, **con guías rotativos** | R-09 | 🔴 `id_facilitador` único; y refuerza el punto 1 |
| 3 | **Sí se cobra**, en dólares, con tarifa por ruta y exoneraciones | R-02, R-03 | 🟠 `tiene_tarifa`/`tarifa_monto` nunca se capturan (H-14) |
| 4 | **La Presidenta aprueba** cada salida, particular o institucional | R-13 | 🟠 No existe aprobación en el modelo |
| 5 | **Se cancela con motivo** y **se reprograma conservando la misma salida** | R-15, R-16 | 🟠 No existen ni el estado ni el histórico de fecha |

### 1.2 El catálogo real (R-02) — seis programas, no cuatro

| Ruta | Tarifa | Público | Nota |
|------|--------|---------|------|
| **Cumaná Histórica** | **5 $ / adulto**; **menores de 8 años gratis** | Todo público | Gratuita para escuelas e instituciones públicas (R-03) |
| **Exploradores de Cumaná** | **Gratuita** | **Niños de 4 a 8 años** | *Mismo recorrido que Cumaná Histórica; cambia el público* |
| **Playa Las Maritas** | 25 $ / persona | — | |
| **Río Brito** | 15 $ / persona | — | |
| **Playa Colorada** | 25 $ / persona | — | |
| **Altos de Cumaná** | *"se cuadra"* | — | **Enlace con posadas** → hay aliados externos (conecta con R-60) |

El `CHECK` actual de `rutas.tipo_ruta` admite `Cumaná Histórica`, `Exploradores de Cumaná`,
`Comunitaria` y `General`. **Faltan cuatro programas** y **sobran dos** que el cliente no nombró.
Con el rediseño el problema desaparece por sí solo: los programas dejan de ser un `enum` y pasan a
ser **filas del catálogo**, que el usuario administra sin migraciones.

### 1.3 Puntos de Cumaná Histórica (adelanto de R-17, vino en R-06)

Castillo San Antonio de la Eminencia · Fortaleza Santa María de la Cabeza · Basílica Menor Santa
Inés · Callejones de Santa Inés y El Alacrán · Casa Natal de Antonio José de Sucre.

R-06 añade que **el acceso a esos puntos se coordina con fundaciones externas** y que esa
coordinación es uno de los tres dolores principales — junto con la logística y la puntualidad del
turista. Sugiere registrar el **ente custodio** de cada punto, pero conviene esperar a R-20.

---

## 1-bis. Lo que aportan R-17…R-64 y los formatos (2026-09-17)

### 1-bis.1 Tres piezas que NO estaban en el plan

**① Los oficios de permiso a las instituciones custodias (R-20)** 🔴

La pregunta era *"¿hay puntos con costo de entrada?"* y la respuesta describió otra cosa: **un
proceso administrativo completo** que el módulo no modela.

> IMATUR **envía un oficio a cada institución custodia** (museos, castillos, fundaciones) para poder
> visitarla. **Todas las rutas de la semana planificada van en un solo oficio**, para agilizar el
> trámite. Se lleva **control del estado de cada uno** —si llegó, si se dio el pase, si se
> rechazó— y lo notifica el **Director de Relaciones Inter-Institucionales**, que además corrobora
> que las instituciones estén disponibles. Estados: **aceptado / en espera**.

Esto es **la mitad del único pedido explícito del cliente** (R-64: *«los reportes y **los
oficios**»*). Y tiene consecuencias de modelo: el permiso **no** cuelga de una salida —cubre varias
a la vez—, así que es una entidad propia con sus renglones, igual que el Acta de Desincorporación de
Bienes. También responde **R-70** (ente custodio del punto) y parte de **R-52**.

**② La Ficha Institucional es el corazón del cierre (R-43 = R-47 = R-48)** 🔴

Se pedían dos documentos —*planilla del día* e *informe de cierre*— y resultaron **el mismo**:
`docs/formatos/rutas_ficha_institucional_IMATUR.jpeg`. Va dirigida a **una sola persona**, la
Directora de Promoción Turística, y su contenido es **el conteo demográfico**:

| Bloque | Desglose |
|---|---|
| Encabezado | Recorrido · Fecha · **Encargado** · Colegio o institución · Responsable |
| **Niños** | F / M · rango de edades · total |
| **Acompañantes** | Docentes (F/M) · Representantes (F/M) |
| **Instituciones de apoyo** | nombre · F / M · total — *aquí va Protección Civil (R-55/R-56)* |
| | **Total general** |

Y **se genera automáticamente** al cerrar (R-50). Esto define la tabla del cierre y **elimina** de
una vez las suposiciones sobre qué lleva el informe.

**③ La lista nominal es del PERSONAL, no de los participantes** 🟢

> ⚠️ **Corregido el 2026-09-17 (2).** Primero se leyó `rutas_lista_asistencia_nominal_IMATUR.jpeg`
> —nombre, cédula y firma— como una lista de participantes, y se concluyó que convivían dos formas
> de registrar gente. **El cliente aclaró que es la asistencia de los trabajadores de IMATUR que
> salen a la ruta**: el o los guías y los ayudantes.

Eso **confirma R-23/R-24/R-29 tal cual**: del grupo visitante IMATUR lleva **solo el conteo**. Y da
al formato su sitio exacto: es el **imprimible de `ruta_ejecucion_empleados`** (la tabla que creó
T-A), no de `participantes_ruta`.

**Lo que sí queda del registro individual:** R-22 dice que participan *"ambos"* y R-62 que del
**particular de pago** se toma la localidad. Así que `participantes_ruta` no sobra — cambia de
público: **deja de ser para el grupo escolar y pasa a ser para el particular que paga** (y enlaza
con T-C, el cobro).

### 1-bis.2 Decisiones que REDUCEN el alcance

Cinco respuestas cierran puertas, y eso vale tanto como las que abren:

| | Respuesta | Qué deja de construirse |
|---|---|---|
| **R-46** | *"Se hace al volver a la oficina"* | **Sin modo campo, sin app móvil, sin offline.** Era el riesgo más caro del módulo |
| **R-49 / R-63** | Las fotos son para prensa, no para el informe | **Sin galería ni evidencias** por salida |
| **R-27** | La institución educativa responde por los permisos | **Sin formato de autorización** de representante |
| **R-58** | El transporte va en el presupuesto, no se registra | **Sin unidad ni conductor** |
| **R-59** | El testimonio lo toma prensa, sin encuesta formal | **Sin indicador de satisfacción** |
| **R-61** | Solo la temporada escolar mueve la demanda | **Sin temporadas ni tarifas estacionales** |
| **R-12** | El oficio de solicitud lo redacta cada institución | El sistema **recibe y archiva**; no lo genera |
| **R-30** | No interesa si alguien repitió ruta | **Sin histórico por persona** |
| **R-34** | Guían estén certificados o no | **Sin control de vencimiento** de certificaciones |

### 1-bis.3 Correcciones al catálogo del §1.2

Los folletos entregados **no coinciden** con lo que dijo R-02:

| | R-02 (2026-09-03) | Los folletos (2026-09-17) |
|---|---|---|
| Nombre | «Altos de Cumaná» | **«Altos de Sucre»** — *«pueblo de montañas, peldaño a las nubes, balcón hacia el mar»* |
| Rutas | seis programas | aparece **Playa Manare**, que no estaba en la lista. Es **FULL DAY**: salida 8:00 a. m., retorno 5:00 p. m. |
| Cumaná Histórica | 5 puntos *(inferidos de R-06)* | **7 puntos** numerados, con inicio y retorno |

🟡 **R-72 (nueva):** ¿cuántas rutas tiene el catálogo realmente, y cuál es el nombre oficial de cada
una? **Cargar el catálogo con la lista equivocada es peor que no cargarlo.**

**R-65 queda respondida por el tríptico:** el folleto de *Exploradores de Cumaná* contiene **el
itinerario de Cumaná Histórica**. Confirma R-02: es **el mismo recorrido con otro público**, o sea
**una ruta con dos modalidades**, no dos filas del catálogo.

### 1-bis.4 Datos concretos que ya se pueden usar

| Dato | Valor | De |
|---|---|---|
| Duración típica / máxima | **1 h 30 min** / **3 h** | R-19 |
| Se registra duración **por punto** y total | sí | R-19 |
| Orden de los puntos | **sugerido**, el guía lo varía | R-18 / R-10 |
| Cupo | **60 personas por DÍA** *(no por salida — es nuevo)* | R-28 |
| Restricción real | **Río Brito: 12 años o más; excluye dificultad visual; advertir condición articular** | R-57 |
| Tarifa | **en USD**, cobrada en Bs **a la tasa del día** | R-36 |
| Guía | **siempre** un empleado de IMATUR; el guía externo lo pone **el punto** | R-31 |
| Personal por salida | **variable según el tamaño del grupo** | R-33 |
| Motivo de cancelación | **obligatorio** | R-15 |
| Meta anual | **100 rutas** — el valor de relleno queda confirmado | R-51 |
| Mapa de puntos | **sí lo quieren** | R-21 |

---

## 2. ✅ ~~Choque en producción detectado al leer R-02~~ — **CORREGIDO (mig. 079)**

> ✅ **Cerrado el 2026-09-17 (fase T-B).** Lo que sigue es el análisis original, que se conserva
> porque explica **por qué** el rango no podía quedarse en el código. Hoy vive en
> `rutas.edad_min`/`edad_max`/`restricciones` y la única regla es `Ruta::motivoEdadNoValida()`.

**El sistema hoy no permite inscribir a un niño de 4 años, y Exploradores de Cumaná es para niños
de 4 a 8.**

`RutasController.php:229` rechaza toda edad menor a 5 (*"El participante debe tener al menos 5
años"*), la vista `rutas/detalle.php` etiqueta el modo libre como *"Niño/a 5–11 (sin cédula)"* y
deshabilita el botón fuera de ese rango (línea 700), y el export del informe rotula las columnas
demográficas como **"Niñas (5-11)" / "Niños (5-11)"**.

De dónde salió el 5–11: se fijó en la migración 017 sin levantamiento. **El rango real es 4–8 para
Exploradores**, y Cumaná Histórica es "todo público" con gratuidad a los menores de 8 — o sea que
tampoco tiene tope inferior.

**Corrección propuesta:** el rango de edad deja de ser una constante del código y pasa a ser
**`edad_min` / `edad_max` del catálogo** (nulo = sin restricción). La regla dura que se conserva es
la de identificación, no la de edad: *quien tiene cédula se registra con cédula*. Es una de las
piezas que **se puede construir ya** (§7).

---

## 3. Modelo de datos propuesto

### 3.1 La separación

```
rutas                      ← CATÁLOGO (qué ofrece IMATUR).  Hoy: 6 filas.
  └── puntos_ruta          ← recorrido plantilla
        │
ruta_ejecuciones           ← SALIDA (una fecha, un grupo). Hoy: lo que la tabla `rutas` guarda.
  ├── ruta_ejecucion_puntos  ← itinerario efectivo de ese grupo (R-10: se reordena)
  ├── ruta_ejecucion_guias   ← varios guías por salida (R-09: "guías rotativos")
  ├── participantes_ruta     ← repunta de id_ruta → id_ejecucion
  ├── ruta_informes          ← repunta de id_ruta → id_ejecucion
  └── oficios_emitidos       ← repunta a la ejecución
```

### 3.2 `rutas` — qué queda y qué se va

| Columna | Destino |
|---------|---------|
| `nombre`, `descripcion`, `duracion_estimada`, `requiere_formacion` | **Se quedan** (son del catálogo) |
| `tiene_tarifa`, `tarifa_monto` | **Se quedan y por fin se capturan** (R-02). Añadir `moneda` (USD) |
| `fecha_visita`, `hora_visita`, `id_facilitador`, `cupo_maximo`, `id_departamento` | **Se mudan a `ruta_ejecuciones`** |
| `estado` (`Activa`/`Inactiva`/`En Mantenimiento`/`Finalizada`) | **Se parte en dos**: el catálogo se queda con `is_active` (¿se ofrece?); el ciclo de vida se va a la ejecución |
| `motivo_mantenimiento` | **Se elimina** — "En Mantenimiento" no describe ni una ruta ni una salida |
| `tipo_ruta` + su `CHECK` | **Se elimina** — el programa *es* la fila del catálogo |
| **Nuevas** | `edad_min`, `edad_max` (§2) · `publico_objetivo` · `cupo_sugerido` · `gratis_menores_de` |

### 3.3 `ruta_ejecuciones` — la tabla nueva

| Columna | Origen |
|---------|--------|
| `id_ruta` FK → catálogo | R-07/R-08 |
| `fecha`, `hora_inicio`, `hora_fin` | R-09 (varias el mismo día) |
| `estado` | R-14 ⛔ **sin responder** — ver §4 |
| `origen` (`Particular` \| `Institucional`) | R-11 |
| `id_institucion`, `n_oficio_solicitud`, `fecha_oficio_solicitud` | R-11, R-12 |
| `aprobado_por`, `fecha_aprobacion` | R-13 (la Presidencia) |
| `cupo_maximo`, `id_departamento` | mudadas desde `rutas` |
| `motivo_cancelacion` | R-15 |
| `fecha_programada_original`, `motivo_reprogramacion` | R-16 (misma salida, otra fecha) |
| `tarifa_aplicada`, `es_exonerada`, `motivo_exoneracion` | R-02, R-03 |

### 3.4 Lo que hay que reconstruir porque se eliminó

| Estructura | Se eliminó en | Por qué vuelve |
|---|---|---|
| **Institución solicitante** (`instituciones_externas`) | mig. 060 (D-RT05) | R-11: solicitan **por oficio**, y **la gratuidad depende** de que sean institución pública |
| **Varios guías** (`nombre_facilitador_externo`) | mig. 060 (D-RT04) | R-09: *"guías rotativos"* — no vuelve la columna de texto, vuelve como **tabla de guías por ejecución** |
| **Actividades por punto** (`actividades_ruta`) | mig. 070 | R-01: *"trivias, fotos, planes vacacionales"*. 🟡 **No reconstruir todavía** — falta saber si se *registran* o solo se hacen (R-17…R-21) |

> **Lección, ya van dos.** D-IN01 (bienes) y ahora **D-RT01 + D-RT05** (rutas): tres decisiones
> tomadas temprano **sin levantamiento** que el levantamiento desmintió. Las estructuras eliminadas
> por "no se usaban nunca" no estaban de más — estaban **sin construir el flujo que las usaba**.

---

## 4. Estados — ✅ **RESPONDIDO el 2026-09-17**

**R-14 ya no está en blanco.** El cliente los nombró, y son **tres**, no los cinco que habíamos
inferido:

> *"**Programado, Ejecutado, o No ejecutado** (muchas veces llegan los oficios, se planifica la
> ruta), pero a veces llega el momento donde los solicitantes cancelan (IMATUR puede cancelar por
> razones ajenas: agua, clima, terremotos…). En estos casos se haría una **reprogramación** de la
> salida que no se pudo ejecutar."*

```
Programado ──► Ejecutado        (terminal)
     │
     └───────► No ejecutado (+ motivo)
                     │
                     └──► reprogramación: NUEVA salida programada,
                          enlazada a la que no se pudo ejecutar
```

**Tres correcciones sobre lo que habíamos inferido:**

| Lo que asumimos | Lo que dijeron |
|---|---|
| *Solicitada* y *Aprobada* como estados | **No existen como estados.** La solicitud y la aprobación de la Presidenta (R-13) son **hitos previos** a que la salida se programe — atributos, no estados del ciclo |
| *Cancelada* como estado terminal | **Cancelar no es un estado**: desemboca en **`No ejecutado`** con su motivo. Da igual quién cancele (el solicitante o IMATUR por clima, agua, etc.): el resultado es el mismo |
| La reprogramación cambia la fecha de la misma fila | R-16 decía *"se considera la misma salida"*, pero R-14 lo precisa: la que no se ejecutó **queda registrada como `No ejecutado`** y la reprogramación es **otra salida enlazada a ella**. Así el histórico no se pierde y el conteo de ejecutadas no se infla |

**El `CHECK` queda:** `('Programado', 'Ejecutado', 'No ejecutado')`. El motivo es **obligatorio**
cuando el estado es *No ejecutado* (R-15: *"si se cancela, el motivo siempre tiene que saberse"*).

> ⚠️ **Y hay un producto que no conocíamos:** al cerrar se levantan **actas de ejecutado y de no
> ejecutado**, y **se archivan en la OAC** (R-47). Son dos documentos, no uno.

---

## 5. Cobro — ✅ **RESPONDIDO POR COMPLETO el 2026-09-17**

**D-RT02 queda cerrada.** El bloque entero (R-36…R-42) está contestado, y la respuesta es la más
exigente de las dos posibles: **el sistema lleva la contabilidad**, no solo deja constancia.

| | Respuesta | Qué implica en el modelo |
|---|---|---|
| **R-36** | Tarifa **pactada en USD**, cobrada en Bs **a la tasa del día** | El catálogo guarda **USD**; la salida **congela la tasa aplicada**. El sistema ya consulta el BCV (mig. 074, Nómina): **se reutiliza `TasaBcv`** |
| **R-37** | Cobra **IMATUR**, en una **cuenta exclusiva** para rutas | Un dato de configuración, no una tabla |
| **R-38** | **Pago ANTICIPADO con fecha tope** — *"se les tiene una fecha para cancelar y poder planificar"* | La salida lleva **fecha límite de pago**, y **el pago condiciona la planificación**. Es una regla de negocio, no un adorno |
| **R-39** | Transferencia → **captura/voucher adjunto**. Efectivo → **acta de pago** que levanta IMATUR | Adjunto por pago (patrón ya probado) + **un documento imprimible nuevo** |
| **R-40** | **Sí lleva el cobro y lo cancelado** | **Tabla de pagos**, con monto, fecha, forma, comprobante y estado. No es declarativo |
| **R-41** | Fijo por ruta y persona, **salvo Altos de Sucre** (*"depende de lo que el cliente solicite"*) | La tarifa del catálogo debe admitir **«a convenir»** |
| **R-42** | Exoneraciones **las autoriza la Presidenta** | Campo de autorización en la exoneración |

**Matiz importante de R-03/R-42:** las instituciones públicas **no pagan, pero igual deben traer el
oficio previo**, y **eso aplica únicamente a Cumaná Histórica**. La gratuidad no es automática por
ser institución: es por **ruta + tipo de solicitante**.

> ⚠️ **Nos pidieron proponer un formato.** Sobre el acta de pago en efectivo el cliente dijo:
> *"el formato nace del momento, **pueden darnos una idea**, pero funciona como respaldo de que el
> servicio fue pagado"*. Es el único documento del módulo que **diseñamos nosotros** y sometemos a
> su visto bueno — no hay que esperar nada.

**Ya se puede construir todo T-C**, incluido el registro de pagos, y **reactivar la columna Tarifa
del reporte** retirada en H-14 — ahora sí con dato verdadero detrás.

---

## 6. Alcance del cambio en código

| Archivo | Impacto |
|---|---|
| `app/models/Ruta.php` (406 líneas) | 🔴 Alto — se parte en `Ruta` (catálogo) + `RutaEjecucion` |
| `app/controllers/RutasController.php` (702 líneas) | 🔴 Alto — el CRUD pasa a ser dos flujos |
| `app/views/rutas/index.php`, `detalle.php` | 🔴 Alto — listado de catálogo + listado de salidas |
| `app/views/rutas/informe.php`, `oficio.php`, `oficio_imprimible.php` | 🟠 Medio — repuntan a la ejecución |
| `ReportesController` (trait de Rutas) | 🟠 Medio — recolumnado; vuelve la Tarifa (H-14) |
| Dashboard / CMI / alertas | 🟡 Bajo — las consultas cuentan salidas, no catálogo |
| **Datos** | 🟢 **Trivial — 2 filas en `rutas`** |

---

## 7. Fases y qué se puede hacer YA

| Fase | Contenido | ¿Bloqueada? |
|------|-----------|-------------|
| ✅ ~~T-A~~ | ~~Separar catálogo y ejecución~~ | **HECHA (2026-09-17, mig. 078).** `ruta_ejecuciones` + `ruta_ejecucion_empleados`, modelo `RutaEjecucion`, dos pantallas (`/rutas/index` catálogo · `/rutas/salidas`). Incluye los tres estados de R-14 y la reprogramación enlazada de R-16 (parte de T-D) |
| ✅ ~~T-B~~ | ~~Edad por catálogo, retirar el 5–11 del código y de los rótulos~~ | **HECHA (2026-09-17, mig. 079).** `rutas.edad_min/edad_max/restricciones` + `Ruta::motivoEdadNoValida()`. **Cierra H-17.** El rango vive en el recorrido, no en el código: se valida en servidor y en el formulario, y los rótulos «(5–11)» del informe y del export son ahora solo «Niñas»/«Niños» |
| **T-C** | **Cobro completo**: tarifa USD en el catálogo, tasa congelada por salida, **registro de pagos**, fecha tope, exoneración autorizada por la Presidencia, comprobante adjunto y **acta de pago en efectivo** | 🟢 **DESBLOQUEADA (2026-09-17).** R-36…R-42 respondidas — y piden **contabilidad**, no solo constancia. Ver §5 |
| ✅ ~~T-D~~ | ~~No ejecutado y reprogramación, con motivo obligatorio y enlace a la salida original~~ | **HECHA (2026-09-18).** Sin migración: las columnas ya estaban desde la 078; **lo que faltaba era la interfaz** — el modelo y el controlador existían pero ningún botón los llamaba. Ahora la salida muestra **su** estado (antes mostraba el del recorrido), se cierra con «Marcar ejecutada» / «No se ejecutó» —motivo obligatorio, R-14— y se reprograma con un modal. El hilo se ve en los dos sentidos y la original nunca se toca (R-16) |
| **T-E** | **Solicitud y aprobación**: origen particular/institucional, institución solicitante, oficio **entrante** archivado, aprobación de Presidencia | 🟢 **DESBLOQUEADA.** R-12 aclara que el oficio **lo redacta la institución** → se **recibe y adjunta**, no se genera. No hay formato que esperar |
| **T-F** | **Itinerario por grupo** (reordenable, R-10) y **varios empleados** por salida (R-33) | 🟢 **DESBLOQUEADA (2026-09-17).** R-17…R-21 y R-31…R-34 respondidas. El guía externo **no** se modela como facilitador: lo pone el punto (R-31) |
| ✅ ~~T-G~~ | ~~Ficha Institucional — el cierre con el conteo demográfico~~ | **HECHA (2026-09-17, mig. 080).** Vive sobre `ruta_informes` (la ficha **es** el informe: R-43 = R-47 = R-48) + `ruta_ficha_grupos` para los renglones. **Nace sola al marcar la salida como Ejecutada** (R-50), con recorrido, fecha, encargado e institución ya puestos. Imprimible fiel al formato, con sus renglones en blanco. Verificado contra el ejemplo real del cliente: 22 + 9 + 2 = **33** |
| **T-H** 🆕 | **Oficios de permiso a instituciones custodias** (R-20): uno por semana cubriendo varias salidas, con estado *en espera / aceptado / rechazado* y el Director de Relaciones Inter-Institucionales como responsable | 🟡 **El flujo sí, el imprimible no** — falta el formato del oficio |
| ✅ ~~T-I~~ | ~~Restricciones por ruta (R-57) y cupo diario de 60 (R-28)~~ | **HECHA (2026-09-17, mig. 079).** `rutas.restricciones` se muestra en la ficha y **al inscribir**; el cupo es `rutas_cupo_diario` en Configuración (0 = sin tope) y se cuenta **por fecha sumando todas las salidas**, sin bloquear |

**Recomendación (revisada el 2026-09-17):** el orden no cambia — **T-A sigue primero**, porque es el
prerrequisito de todo y **cada línea escrita sobre el modelo viejo hay que rehacerla**. Lo que sí
cambió es el panorama: **ya NO queda ninguna fase bloqueada por el cliente.** De las siete
originales, las siete están desbloqueadas, más las dos nuevas.

**Orden sugerido:**

| # | Fase | Por qué ahí |
|---|---|---|
| 1 | **T-A** | Prerrequisito de todo. Con 2 filas en `rutas`, la migración de datos es trivial |
| ~~2~~ | ✅ ~~T-B + T-I~~ | **HECHAS (mig. 079).** H-17 cerrado |
| ~~3~~ | ✅ ~~T-G~~ | **HECHA (mig. 080).** Y de paso apareció el control para cerrar una salida, que T-A había dejado sin interfaz |
| ~~4~~ | ✅ ~~T-D~~ | **HECHA.** Salió casi entera con T-G: sin poder cerrar una salida, la ficha no nacía |
| 5 | **T-E + T-H** | Los **oficios** — pedido #2 del cliente. T-H puede construirse sin su imprimible |
| 6 | **T-C** | El cobro completo. El más grande de los que quedan, y el único con un documento que **diseñamos nosotros** |
| 7 | **T-F** | Itinerario por grupo y varios empleados por salida |

> ✅ **El riesgo que señalaba este plan («arrancar antes de R-14») desapareció:** R-14 está
> respondida y el `CHECK` queda fijado con las palabras del cliente, no con las nuestras.

> ### ℹ️ Sobre `participantes_ruta` (corregido el 2026-09-17 (2))
> **Del grupo visitante IMATUR lleva solo el conteo** (R-23/R-24/R-29): nada de inscribir niño por
> niño. La lista nominal firmada es del **personal de IMATUR**, no de los participantes — ver
> §1-bis.1 ③.
>
> Aun así **la tabla no sobra**: R-22 y R-62 dejan sitio al **particular de pago**, del que sí se
> toma algún dato. Cambia de público, no desaparece. El conteo del grupo es lo que construye **T-G**.

---

## 8. Preguntas al cliente — **qué fase bloquea cada una**

> 📋 **El enunciado de cada pregunta vive en `BACKLOG.md` §3.5**, y en lenguaje llano para el cliente
> en `PREGUNTAS_CLIENTE.md` → *Turismo (Rutas)*. Aquí solo el mapeo a fases, que es lo propio de este
> plan.

> **Actualizado el 2026-09-17: no queda ninguna pregunta bloqueante.** Las cinco que frenaban el
> rediseño (R-14 y el bloque de cobro) se respondieron **dentro de `PREGUNTAS_CLIENTE.md` §4**.

| # | | Bloquea | Estado |
|---|---|---|---|
| ~~R-14~~ | ✅ | ~~T-D y el `CHECK` de estados~~ | **RESPONDIDA:** `Programado` / `Ejecutado` / `No ejecutado`. Ver §4 |
| ~~R-37/38/39/40~~ | ✅ | ~~T-C, registro de pagos~~ | **RESPONDIDAS, y piden contabilidad completa.** Ver §5 |
| **R-32** | 🟢 | Nada — pierde sentido con R-31: el guía externo lo pone el punto | ⛔ Sin responder, **ya no importa** |
| **R-71** 🆕 | 🟡 | ¿El sistema imprime también el **cuadro del punto** (Fundación Castillo), cuyas casillas **no coinciden** con las de la Ficha? | 🆕 Nueva |
| **R-72** 🆕 | 🟡 | **Cuántas rutas tiene el catálogo y su nombre oficial.** Los folletos traen *Playa Manare* y *Altos de **Sucre*** — R-02 decía otra cosa | 🆕 Nueva |
| **Formato** | 🟡 | **T-H** — el **oficio de permiso** a las instituciones custodias (R-20) | 🆕 Nuevo |
| ~~R-65~~ | ✅ | ~~Forma del catálogo~~ | **Respondida por el tríptico:** Exploradores = el mismo recorrido, **una ruta con dos modalidades** |
| ~~R-66~~ | ✅ | ~~Edades~~ | **Respondida por R-57**, con un caso concreto (Río Brito 12+) |
| ~~R-67~~ | 🟡 | ~~Tarifa de Altos de Sucre + directorio de posadas~~ | **Parcial:** R-60 confirma que **todas** las rutas tienen aliados prestadores de servicio. Falta solo la tarifa |
| ~~R-68~~ | 🟡 | ~~Cómo se registra hoy~~ | **Parcial:** sigue sin decirse si usan Excel o papel, pero R-43/R-46 dejan claro que **el registro es en oficina y sobre la Ficha** |
| ~~R-69~~ | ✅ | ~~Varios guías por salida~~ | **Respondida por R-33:** el número depende del tamaño del grupo → tabla, no columna |
| ~~R-70~~ | ✅ | ~~Ente custodio del punto~~ | **Respondida por R-20**, y con mucho más: todo el proceso de permisos (T-H) |
| ~~R-12~~ | ✅ | ~~Formato del oficio de solicitud~~ | **Ya no aplica:** lo redacta cada institución (R-12) |
| ~~R-47/48~~ | ✅ | ~~Informe de ruta ejecutada~~ | **Formato recibido:** la Ficha Institucional |

**Nada de esto bloqueaba T-G**, que son el grueso del módulo. Ver §7.

---

## 9. Qué NO cambia

Para que el alcance no se infle más de lo que ya se infló:

- **Puntos y mapa Leaflet** — el recorrido con coordenadas y el mapa offline funcionan; pasan a
  colgar del catálogo sin más cambio que la FK.
- **Modo libre de participantes** (sin cédula, con representante) — el mecanismo es correcto; solo
  se corrige el rango de edad (§2).
- **Correlativo de oficios** — `ConfigSistema::generarNumeroOficio('ruta')` es atómico y reinicia
  por año (H-06). Se conserva tal cual; solo cambia a qué apunta.
- **Prerequisito de formación** (RN-RT03) — se conserva; pasa a ser atributo del catálogo, que es
  donde siempre debió estar.
- **Demografía del informe** — la estructura sirve; ✅ los rótulos "5-11" ya se corrigieron
  (mig. 079: son «Niñas»/«Niños», el desglose es por sexo). Falta contrastarla contra el formato
  real (T-G).
