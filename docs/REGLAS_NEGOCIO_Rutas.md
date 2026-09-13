# Módulo de Rutas Turísticas — Reglas de Negocio

**Última actualización:** 2026-09-03 · **Migraciones:** hasta 073
**Pendientes y preguntas abiertas:** `docs/BACKLOG.md` §3.5 · **Plan de reconstrucción:** `docs/PLAN_MODULO_RUTAS.md`

> ## 🔴 AVISO 2026-09-03 — el levantamiento llegó y **este documento describe un modelo que va a cambiar**
>
> El cliente respondió **R-01 … R-16** del cuestionario de descubrimiento, y las respuestas
> **desmienten la premisa sobre la que está construido el módulo**:
>
> | | Lo que dice el cliente |
> |---|---|
> | **R-07** | Dos salidas de Cumaná Histórica son *"la misma ruta ejecutada 2 veces"* |
> | **R-08** | *"Existe un catálogo"* de rutas: puntos, recorrido, inicio y fin |
> | **R-09** | **Varios grupos el mismo día**, con **guías rotativos** |
> | **R-02** | **Sí se cobra** (5 $ / 15 $ / 25 $ por persona) y hay **seis programas**, no cuatro |
> | **R-11 / R-13** | La salida **nace de una solicitud** (particular o institucional por oficio) y **la aprueba la Presidencia** |
> | **R-15 / R-16** | Se **cancela con motivo** y se **reprograma conservando la misma salida** |
>
> **Consecuencia:** `rutas` se parte en **catálogo** (`rutas`) + **salida** (`ruta_ejecuciones`).
> Mientras eso no se construya, **las reglas de abajo siguen siendo las vigentes en el código**,
> pero están marcadas 🔴 donde ya se sabe que van a cambiar. **No tomarlas como diseño objetivo.**
>
> **D-RT01 y D-RT05 quedan desmentidas.** El detalle, el modelo propuesto y las fases están en
> `docs/PLAN_MODULO_RUTAS.md`.

## Contexto institucional

Las rutas turísticas las gestiona el **Departamento de Rutas Turísticas y Proyectos** bajo la
Dirección de Planificación y Gestión Turística. **IMATUR ofrece seis programas** (R-02):

| Ruta | Tarifa | Público |
|------|--------|---------|
| **Cumaná Histórica** | 5 $ por adulto · **menores de 8 años gratis** · **gratuita** para escuelas e instituciones públicas | Todo público |
| **Exploradores de Cumaná** | Gratuita | Niños de **4 a 8 años** — mismo recorrido, otro público |
| **Playa Las Maritas** | 25 $ por persona | — |
| **Río Brito** | 15 $ por persona | — |
| **Playa Colorada** | 25 $ por persona | — |
| **Altos de Cumaná** | *"se cuadra"* — enlace con posadas | — |

Frecuencia: **2-3 salidas semanales** más las solicitadas puntualmente (R-04). Tamaño de grupo:
**15-30 personas** en promedio, **máximo histórico 120** (R-05).

Los tres dolores que el cliente nombró (R-06): **logística**, **puntualidad del turista** y
**coordinación con fundaciones externas** para conseguir acceso a los puntos.

---

## RN-RT01 — Estados de ruta — 🔴 **cambia**

**Vigente en el código:** valores válidos (`Ruta::ESTADOS`): `'Activa'`, `'Inactiva'`,
`'En Mantenimiento'`, `'Finalizada'`. `'Finalizada'` es **terminal** (`Ruta::ESTADO_TERMINAL`).

🔴 **Desmentido por R-07/R-08 (2026-09-03).** Cada registro en `rutas` representa hoy *una ejecución
independiente*; el cliente confirmó que **existe un catálogo reutilizable** y que dos salidas de la
misma ruta son *"la misma ruta ejecutada 2 veces"*. **D-RT01 queda desmentida.**

Los cuatro estados actuales mezclan dos ciclos de vida: `Activa`/`Inactiva`/`En Mantenimiento`
describen **una ruta del catálogo** (¿se ofrece?), `Finalizada` describe **una salida**. Con el
rediseño el catálogo se queda con `is_active` y la salida tiene su propio ciclo (RN-RT10).

⛔ **R-14 sin responder**: los nombres exactos de los estados los tiene que dar IMATUR. Ver
`docs/PLAN_MODULO_RUTAS.md` §4.

---

## RN-RT02 — ~~Niveles de dificultad~~ (eliminado)

La columna `nivel_dificultad` se **eliminó en la migración 021**: IMATUR no clasifica
sus rutas por dificultad. No usar; el reporte de rutas tampoco la ofrece como filtro.

---

## RN-RT03 — Prerequisito de formación (RN-F12)

Las rutas con `requiere_formacion = TRUE` (ej: Exploradores de Cumaná) exigen que el participante haya asistido (`asistio = TRUE`) a al menos una actividad de formación antes de inscribirse.

- Participantes con cédula: el sistema verifica en `participantes_taller`.
- Participantes libres (niños/as sin cédula): **exentos** de esta verificación.

---

## RN-RT04 — Participantes y grupos

- Los participantes pueden ser individuos o grupos escolares.
- Se admiten participantes sin cédula (niños/as) mediante el modo libre (`nombre_libre`/`apellido_libre`).
- Un participante puede inscribirse en múltiples rutas.
- 🔴 La institución educativa **no se registra hoy**: `participantes_ruta.id_institucion` y la tabla
  `instituciones_externas` se eliminaron en la **migración 060**. **R-11 (2026-09-03) desmiente
  D-RT05**: las instituciones públicas **solicitan la ruta por oficio**, y de que el solicitante sea
  una institución pública **depende la gratuidad** (R-03). Hay que **reconstruirlo** — esta vez con
  el flujo que lo usa, no solo la columna.
- 🔴 **Rango de edad del modo libre**: el código exige **5-11 años**
  (`RutasController.php:229`, más los rótulos de la vista y del informe). **Exploradores de Cumaná
  es para niños de 4 a 8** (R-02) → **hoy un niño de 4 años no se puede inscribir**. El rango debe
  salir del código y pasar al catálogo (`edad_min`/`edad_max`). Ver plan §2.

---

## RN-RT05 — Rutas de pago — 🟠 **sí se cobra (confirmado R-02)**

- **R-02 (2026-09-03) confirma el cobro**, en dólares y por persona: Cumaná Histórica **5 $**,
  Río Brito **15 $**, Playa Las Maritas y Playa Colorada **25 $**, Exploradores **gratuita**,
  Altos de Cumaná *"se cuadra"*.
- **Dos exoneraciones conocidas:** **menores de 8 años** en Cumaná Histórica (R-02) y
  **escuelas e instituciones públicas** (R-03).
- **Estado del código:** `tiene_tarifa BOOL` y `tarifa_monto DECIMAL(10,2)` existen (migración 007)
  pero **nunca se capturan**. El reporte dejó de mostrar la columna Tarifa el 2026-08-27 (**H-14**)
  porque informaba «Gratuita» siempre. → **Las columnas ya no se eliminan: se capturan**, y la
  columna del reporte vuelve cuando tenga un dato verdadero detrás.
- 🔒 **Lo que sigue bloqueado (D-RT02 parcial):** **quién recibe el dinero** (R-37), **cómo se paga**
  (R-38), **qué comprobante se emite** (R-39), **si el sistema lleva la contabilidad o solo deja
  constancia** (R-40) y **quién autoriza exoneraciones** (R-42). Hasta responderlas **no se
  construye registro de pagos**.

---

## RN-RT06 — Puntos de ruta (paradas)

- Los puntos tienen orden, nombre, descripción y coordenadas lat/lon opcionales.
- El orden no es obligatorio (el guía puede variar el recorrido según el día).
- La tabla `puntos_ruta` tiene `lat` y `lon`, y el **mapa está construido**: `rutas/detalle.php`
  renderiza los puntos con **Leaflet vendorizado en local** (`assets/js/leaflet.min.js` +
  `assets/css/leaflet.min.css`), sin CDN.

---

## RN-RT07 — Facilitador y guía — 🔴 **se queda corto**

- **Vigente:** la ruta tiene **un** facilitador principal (`id_facilitador` FK a empleados).
  `rutas.nombre_facilitador_externo` se eliminó en la migración 060 (D-RT04).
- 🔴 **R-09 habla de "guías rotativos"** y de varios grupos simultáneos → una salida puede llevar
  **más de un guía**. El `id_facilitador` único no lo representa. Vuelve, pero como **tabla de guías
  por ejecución**, no como columna de texto.
- ⏳ **R-31…R-34 sin responder**: si hay guías externos, si se les paga y si necesitan certificación.

---

## RN-RT08 — ~~Inventario asignado~~ (eliminado)

La tabla `ruta_inventario` se **eliminó en la migración 019**. No se asignan bienes a rutas.

---

## RN-RT09 — Oficio emitido

Al generar un oficio de visita (`/rutas/oficio/{id}`), el sistema:
1. Asigna un número correlativo desde `configuracion_sistema.correlativo_oficio_ruta`.
2. Guarda el registro en `oficios_emitidos` (vinculado a la ruta).
3. Renderiza una página imprimible standalone (`oficio_imprimible.php`) sin layout del sistema.

Formato del correlativo: `007/2026` — **sin prefijo**. Lo genera
`ConfigSistema::generarNumeroOficio('ruta')`, que incrementa de forma atómica
(`UPDATE … RETURNING` dentro de transacción, hallazgo H-06) y **reinicia a `001` al cambiar de año**.

🔒 **D-RT03** pendiente: si al pasar la ruta a *Finalizada* debe generarse el informe/oficio
automáticamente. Hoy se dispara a mano.

⚠️ **Falta el oficio de entrada.** R-11/R-12: las instituciones públicas **solicitan** la ruta con
un oficio que **queda archivado en IMATUR** (se recibe en físico o digital). El sistema solo emite
oficios de salida; **no registra el de entrada**. El cliente quedó en enviar el formato.

---

## RN-RT10 — Solicitud, aprobación y ciclo de vida de una salida — 🆕 **por construir**

Reglas que salen de R-11 a R-16 y que **el sistema hoy no implementa**:

1. **Una salida nace de una solicitud** (R-11), con dos orígenes: **particular** (el turista la pide)
   o **institucional** (oficio que queda archivado en IMATUR).
2. **Toda salida requiere aprobación de la Presidencia** — actualmente **María Maza** —
   **tanto la particular como la institucional** (R-13). Hay que registrar **quién aprobó y cuándo**.
3. **Cancelación con motivo obligatorio** (R-15). Motivos reales citados: **falta de gasolina**,
   **clima**, **el grupo cancela**.
4. **La reprogramación NO es un estado**: es un **cambio de fecha sobre la misma salida** (R-16,
   literal: *"se reprograma y se considera la misma, solo que con el cambio"*). Se conserva la fecha
   original y el motivo.
5. **Varias salidas el mismo día** (R-09), de la misma ruta o de rutas distintas, con guías rotativos.
6. **El itinerario puede variar por grupo** (R-10): si hay dos grupos en la misma ruta a la vez,
   **se cambia el orden de los puntos** para no coincidir. → el recorrido del catálogo es una
   **plantilla**; la salida guarda **su** itinerario.

⛔ **R-14 sin responder** — los nombres de los estados. Hasta tenerlos, no se fija el `CHECK`.

---

## Estado de brechas

| ID | Descripción | Estado |
|----|-------------|--------|
| BRT-01 | Registro de pagos | 🟠 **Desbloqueado a medias (R-02).** Se confirma que **sí se cobra** y se conocen los montos → la **tarifa declarativa se puede construir ya**. El **registro de pagos** sigue 🔒 por R-37…R-40 |
| BRT-02 | Guías: hoy uno solo por ruta | 🔴 **Reabierto por R-09** (*"guías rotativos"*). Vuelve como **tabla de guías por ejecución**, no como columna de texto |
| BRT-03 | Mapa visual de puntos de ruta | ✅ Resuelto — Leaflet + OSM vendorizados en local, en `rutas/detalle.php` |
| BRT-04 | Registro de institución solicitante | 🔴 **Reabierto por R-11.** `instituciones_externas` se eliminó en la mig. 060, pero las instituciones **solicitan por oficio** y de eso depende la gratuidad. **D-RT05 desmentida** |
| BRT-05 | Catálogo de rutas vs. salidas | 🔴 **Abierto por R-07/R-08.** Lo que se daba por resuelto (cada ruta *es* una ejecución) es justamente lo que el cliente desmiente. **Es el rediseño**: `docs/PLAN_MODULO_RUTAS.md` §3 |
| BRT-06 | Adultos acompañantes y prerequisito de formación | 🔒 Pendiente — R-22/R-23 sin responder |
| BRT-07 | **Rango de edad 5–11 cableado en el código** | 🔴 **Nuevo (R-02).** Exploradores es para **4 a 8 años**: hoy un niño de 4 **no se puede inscribir**. El rango pasa al catálogo |
| BRT-08 | **Oficio de solicitud (entrada) no se registra** | 🔴 **Nuevo (R-11/R-12).** El sistema solo emite oficios de salida |
| BRT-09 | **Aprobación de la Presidencia** | 🔴 **Nuevo (R-13).** No existe en el modelo |
| BRT-10 | **Cancelación con motivo y reprogramación** | 🔴 **Nuevo (R-15/R-16).** No existen ni el estado ni el histórico de fecha |
