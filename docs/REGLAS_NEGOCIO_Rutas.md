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
- ✅ **Rango de edad: es del recorrido, no del sistema** (mig. 079, 2026-09-17). `rutas.edad_min` y
  `rutas.edad_max` — NULL = sin tope — más `rutas.restricciones` en texto para lo que no es edad
  (R-57: *«dificultad visual, excluidos»*; *«condición en articulaciones: sí debería saberlo»*). La
  única regla es `Ruta::motivoEdadNoValida()`, aplicada en los **dos** flujos de inscripción y en el
  formulario. El 5–11 cableado —que dejaba fuera a los niños de 4 de Exploradores— **ya no existe**.
  Las condiciones se muestran en la ficha del recorrido y **al inscribir**.
- ✅ **Cupo: 60 personas por día** (R-28), no por salida — `rutas_cupo_diario` en Configuración,
  0 = sin tope. **Advierte, no bloquea**, igual que en Talleres: es planificación.
- ✅ **El cierre de una salida es la Ficha Institucional** (mig. 080). R-43, R-47 y R-48 pedían
  «planilla del día» e «informe de cierre», y resultaron el **mismo** documento. Se genera sola al
  marcar la salida como Ejecutada (R-50) y lleva **conteos, no nombres**: del grupo visitante IMATUR
  no registra persona por persona (R-23/R-24/R-29). Lleva un renglón por institución (con su rango
  de edades), los acompañantes —docentes y representantes— y las **instituciones de apoyo**
  (Protección Civil, R-55/R-56). El TOTAL es la suma de los tres bloques.
- ✅ **Una salida se cierra con «Marcar ejecutada» o «No se ejecutó»** (R-14), y en el segundo caso
  el motivo es obligatorio. Los estados terminales no vuelven atrás.
- ✅ **Reprogramar (R-16) no edita la salida: crea otra.** Solo desde «No ejecutado», una sola vez, y
  con fecha no pasada. La nueva hereda grupo, origen, cupo y oficio, y queda enlazada por
  `id_reprogramada_de`. **La original se conserva intacta**: es la constancia de lo que no ocurrió.
- ✅ **Una salida no ejecutada no tiene Ficha Institucional** ni admite inscripciones: no hubo grupo.
- ✅ **El oficio de solicitud es ENTRANTE** (R-12): lo redacta la institución y IMATUR lo **archiva**
  como respaldo de que la salida se pidió formalmente. No confundir con el oficio **saliente** de
  IMATUR hacia el punto a visitar, que sí lleva correlativo (`oficios_emitidos`).
- ✅ **Una salida de origen institucional debe decir quién la pide** (R-11); una particular, no.
- ✅ **La aprueba la Presidencia** (R-13). El sistema registra el visto bueno con fecha y con quién
  lo asentó; no se aprueba dos veces.

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

> Registro de brechas **de este módulo**. El plan de reconstrucción y sus fases T-A…T-G están en
> `PLAN_MODULO_RUTAS.md`; las **preguntas al cliente** (R-xx), en `BACKLOG.md` §3.5 — aquí solo se
> referencian por ID.

| ID | Descripción | Estado |
|----|-------------|--------|
| BRT-01 | Registro de pagos | 🟠 **Desbloqueado a medias (R-02).** Se confirma que **sí se cobra** y se conocen los montos → la **tarifa declarativa se puede construir ya**. El **registro de pagos** sigue 🔒 por R-37…R-40 |
| BRT-02 | Guías: hoy uno solo por ruta | 🔴 **Reabierto por R-09**, **precisado por R-31/R-33 (2026-09-17)**: la salida la **encabeza siempre un empleado de IMATUR** y lo acompaña un **número variable** según el tamaño del grupo → **tabla de empleados por ejecución**. El **guía externo lo pone el punto visitado**, no IMATUR: no va como facilitador de la salida |
| BRT-03 | Mapa visual de puntos de ruta | ✅ Resuelto — Leaflet + OSM vendorizados en local, en `rutas/detalle.php` |
| BRT-04 | Registro de institución solicitante | 🔴 **Reabierto por R-11.** `instituciones_externas` se eliminó en la mig. 060, pero las instituciones **solicitan por oficio** y de eso depende la gratuidad. **D-RT05 desmentida** |
| BRT-05 | Catálogo de rutas vs. salidas | 🔴 **Abierto por R-07/R-08.** Lo que se daba por resuelto (cada ruta *es* una ejecución) es justamente lo que el cliente desmiente. **Es el rediseño**: `docs/PLAN_MODULO_RUTAS.md` §3 |
| BRT-06 | Adultos acompañantes y prerequisito de formación | 🟢 **Resuelto por R-22…R-25 + los formatos (2026-09-17).** Los acompañantes **sí se cuentan**, y con desglose propio: la *Ficha Institucional* separa **Docentes** y **Representantes** por sexo, aparte de los niños. No hay prerequisito de formación |
| ~~BRT-07~~ | ~~Rango de edad 5–11 cableado en el código~~ | ✅ **RESUELTO (2026-09-17, mig. 079).** La restricción es ahora un atributo del recorrido —edad **y** condiciones— y se advierte al inscribir. Río Brito 12+, Exploradores 4–16 |
| BRT-08 | **Oficio de solicitud (entrada) no se registra** | 🔴 **Precisado por R-12 (2026-09-17):** llega **en físico** y **lo redacta cada institución** — no hay formato único. El sistema debe **recibir y archivar el escaneado**, no generarlo |
| BRT-09 | **Aprobación de la Presidencia** | 🔴 **Nuevo (R-13).** No existe en el modelo |
| BRT-10 | **Cancelación con motivo y reprogramación** | 🔴 **Nuevo (R-15/R-16).** No existen ni el estado ni el histórico de fecha. **R-15 (2026-09-17) lo endurece: el motivo es OBLIGATORIO** — *"si se cancela, el motivo siempre tiene que saberse"* |
| BRT-11 🆕 | **Ficha Institucional: el conteo demográfico del cierre** | 🔴 **Nuevo (R-43/R-47/R-48/R-50).** La planilla del día y el informe de cierre **son el mismo documento**, va a la Directora de Promoción Turística y **se genera automáticamente**. Formato real en `docs/formatos/rutas_ficha_institucional_IMATUR.jpeg`. Hoy no existe nada de esto |
| BRT-12 🆕 | **Oficios de permiso a las instituciones custodias** | 🔴 **Nuevo (R-20).** IMATUR pide permiso por oficio a cada museo/castillo/fundación, **agrupando la semana en uno solo**, y controla su estado (*llegó · pase · rechazado*) a través del **Director de Relaciones Inter-Institucionales**. Es la mitad del pedido explícito de R-64. No existe en el modelo |
| BRT-13 🆕 | **Cupo diario de 60 personas** | 🟡 **Nuevo (R-28).** No hay cupo por salida, pero sí un tope **por DÍA** — con dos salidas el mismo día, se reparte. Es una regla reciente del cliente |
| BRT-14 🆕 | **Incidencias de la salida** | 🟡 **Nuevo (R-45).** *"Sí se lleva y se reporta la incidencia"*. No hay dónde registrarla |
| BRT-15 🆕 | **Duración por punto y total** | 🟢 **Nuevo (R-19).** Se calcula **punto a punto** y en total. Referencias reales: **1 h 30 min** lo normal, **3 h** el máximo |
