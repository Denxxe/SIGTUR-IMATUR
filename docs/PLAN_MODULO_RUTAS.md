# Plan de reconstrucción — Módulo de Rutas Turísticas

**Fecha:** 2026-09-03 · **Origen:** respuestas del cliente a **R-01 … R-16**
(`docs/PREGUNTAS_DESCUBRIMIENTO_Bienes_Rutas.md`, Parte 2)
**Estado:** análisis cerrado · construcción **no iniciada**
**Migraciones vigentes al escribir:** hasta **073**

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

**¿Se puede empezar a desarrollar?** **Sí, la mitad.** Ver §7.

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

## 2. 🔴 Choque en producción detectado al leer R-02

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

## 4. Estados — el bloqueo principal ⛔

**R-14 quedó en blanco** y es una pregunta ⭐. De R-13, R-15 y R-16 se **infiere** este ciclo:

```
Solicitada ──► Aprobada ──► Programada ──► Ejecutada        (terminal)
     │             │             │
     └─────────────┴─────────────┴──► Cancelada (+ motivo)  (terminal)
                                 │
                                 └──► reprogramación: MISMA fila, cambia la fecha,
                                      se guarda la original y el motivo (R-16)
```

**No fijar el `CHECK` con estos nombres inventados.** Hay que pedirle a IMATUR **las palabras que
usan ellos** — es exactamente lo que R-14 preguntaba. Mientras tanto, la construcción puede avanzar
en todo lo que no toca la columna `estado`.

El único punto firme: **la reprogramación NO es un estado**. Es un cambio de fecha sobre la misma
ejecución, con histórico (R-16 lo dice literalmente).

---

## 5. Cobro (R-02 responde parte de D-RT02)

**Lo que ya se sabe:** se cobra, en dólares, monto por persona fijado por ruta, con dos
exoneraciones conocidas — **menores de 8 años** en Cumaná Histórica, y **escuelas/instituciones
públicas** (R-03).

**Lo que falta y bloquea:** R-37 (quién recibe el dinero: ¿IMATUR, la Alcaldía?), R-38 (efectivo /
transferencia / punto de venta), R-39 (¿qué comprobante?), R-40 (**¿el sistema lleva la
contabilidad o solo deja constancia?**) y R-42 (quién autoriza exoneraciones).

**Se puede construir ya** la parte declarativa: tarifa en el catálogo, tarifa aplicada y exoneración
en la ejecución, y **reactivar la columna Tarifa del reporte** que se retiró en H-14 — porque ahora
sí tendrá un dato verdadero detrás. **No construir** registro de pagos ni comprobantes hasta R-40.

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
| **T-A** | **Separar catálogo y ejecución.** Nueva `ruta_ejecuciones`, mudar columnas, repuntar `participantes_ruta` / `ruta_informes` / `oficios_emitidos`, migrar las 2 filas, partir modelo y controlador, rehacer las vistas | 🟢 **NO — se puede empezar hoy.** Es la fase grande y no depende de ninguna respuesta faltante |
| **T-B** | **Edad por catálogo** (`edad_min`/`edad_max`), retirar el 5–11 del código y de los rótulos del informe | 🟢 **NO — se puede hoy.** Corrige un choque real (§2) |
| **T-C** | **Tarifa declarativa**: monto por ruta, tarifa aplicada + exoneración por salida, reactivar la columna en el reporte | 🟢 **NO** para lo declarativo · 🔒 el **registro de pagos** espera R-37…R-40 |
| **T-D** | **Cancelación y reprogramación** con motivo e histórico de fecha | 🟡 **Parcial** — la lógica es clara (R-15/R-16); solo los **nombres de estado** esperan R-14 |
| **T-E** | **Solicitud y aprobación**: origen particular/institucional, institución solicitante, oficio, aprobación de Presidencia | 🟡 **Parcial** — el flujo es claro (R-11/R-13); el **imprimible** espera el formato de oficio (R-12) |
| **T-F** | **Itinerario por grupo** (reordenable, R-10) y **varios guías** por salida (R-09) | 🔒 **Sí** — R-17…R-21 (paradas, duración, costo de entrada) y R-31…R-34 (guías) |
| **T-G** | **Informe de cierre** contrastado contra el formato real | 🔒 **Sí** — R-47/R-48 + el documento físico |

**Recomendación:** arrancar por **T-A + T-B + T-C(declarativa)**. Son la mitad del módulo, no
dependen de ninguna respuesta pendiente, y **T-A es el prerrequisito de todo lo demás** — cuanto más
código se escriba sobre el modelo viejo, más caro sale.

**Riesgo de arrancar antes de R-14:** bajo y acotado. Si los nombres de estado llegan distintos,
cambia un `CHECK`, una constante y unas etiquetas. **No** cambia la estructura.

---

## 8. Preguntas al cliente

### 8.1 Bloqueantes — impiden cerrar el rediseño

| # | Pregunta | Bloquea |
|---|---|---|
| **R-14** ⭐ | **¿Qué estados atraviesa una salida, con las palabras que ustedes usan?** *(Quedó en blanco. De sus otras respuestas se infiere Solicitada → Aprobada → Programada → Ejecutada, más Cancelada; hace falta confirmarlo.)* | T-D, y el `CHECK` de `ruta_ejecuciones.estado` |
| **R-40** ⭐ | ¿El sistema debe **llevar la contabilidad** de los cobros, o solo dejar constancia de que la salida tenía tarifa? | T-C (registro de pagos) |
| **R-37/R-38/R-39** ⭐ | ¿Quién recibe el dinero, cómo se paga y qué comprobante se emite? | T-C |

### 8.2 Nuevas — surgen de las propias respuestas

| # | Pregunta | Por qué |
|---|---|---|
| **R-65** ⭐ | **¿Exploradores de Cumaná es una ruta aparte o el mismo recorrido de Cumaná Histórica con otro público?** R-02 dice *"es lo mismo, solo que se diferencian por el público objetivo"*. → ¿Dos filas del catálogo, o **una ruta con dos modalidades**? | Define si el catálogo tiene 6 filas o 5 con variantes |
| **R-66** ▲ | **Las edades**: Exploradores es 4-8 y en Cumaná Histórica los menores de 8 no pagan. ¿Hay **tope de edad** en Exploradores, o un niño de 9 entra igual? ¿Y edad mínima para las rutas de playa? | §2 — el rango sale del código y pasa al catálogo |
| **R-67** ▲ | **Altos de Cumaná** dice *"se cuadra… enlace con personas en posadas"*. ¿Tiene tarifa? ¿La cobra IMATUR o la posada? ¿Hay que llevar un **directorio de posadas aliadas**? | Tarifa de esa ruta + adelanta R-60 |
| **R-68** ▲ | **¿Cómo se registra hoy una ruta?** R-03 quedó *"en espera de respuesta"* — no sabemos si hoy usan Excel, papel o nada | Define cuántos datos históricos hay que cargar |
| **R-69** ▲ | R-09 menciona **"guías rotativos"**: ¿cuántos guías van por salida y **se registra quiénes fueron**? | T-F, y el `id_facilitador` único de hoy |
| **R-70** ○ | R-06 dice que se **coordina con fundaciones externas** el acceso a los puntos. ¿Hay que registrar el **ente custodio** de cada punto y el estado de esa gestión? | T-F, junto con R-20 |

### 8.3 Formatos a pedir

Los ⭐ son los que más aceleran: **oficio de solicitud** (R-12, el cliente quedó en enviarlo) e
**informe de una ruta ejecutada** (R-47/R-48). Lista completa en el cuestionario, Parte 3.

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
- **Demografía del informe** — la estructura sirve; hay que contrastarla contra el formato real
  (T-G) y corregir los rótulos "5-11".
