# BACKLOG ÚNICO — SIGTUR-IMATUR

**Última actualización:** 2026-10-11 · **Migraciones aplicadas:** hasta **087** · **Rama:** `development_stage`

> ✅ **2026-10-11 (b) — Bienes: motor de codificación propia construido y APAGADO (mig. 087).**
> IMATUR ya puede asignar el código de sus bienes con secuencia propia, catálogo de clasificaciones y
> la distinción *codificado por IMATUR* / *reconocido por la Alcaldía*. Se enciende en
> *Bienes → Codificación* cuando lleguen **B-73** (punto de partida) y **B-75** (catálogo); B-74 quedó
> como parámetro. Ver `PLAN_MODULO_BIENES.md` §2-sexies.
>
> ✅ **2026-10-11 — Nómina completa: construida la Liquidación de Prestaciones Sociales (fase N-E,
> mig. 086).** Digitaliza las tres hojas de `LIQUIDACION MES JULIO 2026.xls` (FORMATO, INTERESES,
> INTERÉS DE MORA) con sus fórmulas reales, paga lo mayor entre garantía y retroactivo (art. 142 d) y
> **todas las cifras de la ley y del contrato son parámetros** en *Nómina → Parámetros* (pedido del
> cliente: «que todo sea configurable»). Del cliente solo hace falta una liquidación real ya calculada
> para comparar y las **tasas del BCV** de cada mes. Ver §3.1 y `PLAN_MODULO_NOMINA.md` §7.

> ⚠️ **2026-09-02 — Bienes: la Alcaldía cambió el procedimiento de codificación.** IMATUR pasa a
> asignar el código de sus propios bienes y el acta de baja pasa a ser **Acta de Desincorporación**
> por lote (sin oficio de retiro). **Ver §3.4** y `docs/PLAN_MODULO_BIENES.md` §2-ter.
>
> ✅ **2026-09-17 — Llegaron 2 de los 4 formatos de Bienes** (Directora de Bienes, vía WhatsApp).
> **Oficio de relación** y **documento de donación** quedaron **construidos** (mig. 075). Siguen
> faltando el **Acta de Desincorporación** y el **acta de asignación**. ⚠️ El oficio recibido es el
> del procedimiento **anterior** al cambio del 2026-09-02 — hay que confirmarlo. **Ver §3.4.**

> 📝 **2026-10-06 — Respuestas del cliente a Nómina y Bienes** (anotadas por el equipo sobre
> `docs/entregables/Preguntas_y_recaudos_IMATUR_2026-10-06.odt`) **+ una planilla
> ANTIGUA de la encargada**, solo como muestra de cómo llevan su listado interno
> (`docs/formatos/planilla_inventario_encargada_MUESTRA_antigua.docx`) — **no es el inventario vigente**. Cerradas: N-2, B-76,
> B-79, B-80, B-82, las 11 categorías. **N-1, N-3 y N-4 cerradas** (N-1 aplicada en la mig. 085) (columna de alícuotas confirmada; cesta ticket y tasa salen de la maqueta mensual, a mano). Respondidas a medias (hay que repreguntar):
> B-77/B-78. El archivo vigente del inventario **sigue pendiente**. **Ver §3.1,
> §3.4 y `PLAN_MODULO_BIENES.md` §2-quinquies.**

Documento **único** de seguimiento: **qué falta por hacer y decidir**. Lo ya hecho no vive aquí.

> **Fuente única de las preguntas abiertas.** Los IDs (B-xx de Bienes, R-xx de Rutas, N-x de Nómina,
> D-xx de decisiones, H-xx de auditoría) se definen **solo en la sección 3 y la 4 de este archivo**.
> Los planes de módulo y los documentos de reglas los **referencian por ID**, nunca los reproducen:
> así una respuesta del cliente se anota en un solo sitio.

| Para saber… | Leer |
|---|---|
| Cómo funciona el sistema hoy (arquitectura, BD, convenciones) | `CLAUDE.md` |
| Qué se hizo y por qué (historial) | `CHANGELOG.md` |
| Reglas de negocio por módulo | `REGLAS_NEGOCIO_*.md`, `MODELO_NEGOCIO_RRHH.md` |
| Planes de reconstrucción en curso | `PLAN_MODULO_{BIENES,RUTAS,NOMINA}.md` |
| Indicadores y sus fórmulas | `INDICADORES_GESTION.md` |
| Requerimientos, modelo de datos y diagramas UML | `ESPECIFICACION_REQUERIMIENTOS.md` · `MODELO_DATOS_ER.md` · `CASOS_DE_USO.md` · `DIAGRAMA_CLASES.md` · `DIAGRAMA_COMPONENTES.md` · `DIAGRAMAS_SECUENCIA.md` |
| **Lo que se le pide al cliente, en lenguaje llano y por módulo** | `PREGUNTAS_CLIENTE.md` |
| Respuestas crudas del cliente (material de origen) | `PREGUNTAS_DESCUBRIMIENTO_Bienes_Rutas.md`, `formatos/` |

**Leyenda:** 🔴 bloquea BD/lógica · 🟡 alto impacto · 🟢 menor · ✅ hecho · 🔒 espera decisión/insumo del cliente · 🛠️ implementable ya

---

## 0. RUTA AL CIERRE — en qué orden trabajar

> Actualizado 2026-10-06. El detalle de cada punto está en las secciones 3 (insumos del cliente) y 5
> (programación). **Para enviar al cliente: `PREGUNTAS_CLIENTE.md`** — las mismas preguntas e IDs de
> la §3, en lenguaje llano, con la lista de chequeo de recaudos.

### 0.1 Se puede programar YA, sin esperar a nadie

| # | Tarea | Tamaño | Por qué ahora |
|---|---|---|---|
| 1 | ✅ ~~Deuda técnica de §5.2~~ **hecha (2026-10-11)**: `label[for]` completos, lista blanca en `Taller::actualizarPersona`, estilos inline 2.724 → 1.581, pruebas 145 → 179. Lo que queda inline es deliberado (ver `CLAUDE.md` → Design System) | — | — |

**Hecho y retirado de esta lista** (detalle en `CHANGELOG.md`): verificación en navegador (2026-10-03)
y **prueba de formularios guardando** (2026-10-04, B1 cerrado) · tareas programadas y feriados movibles
(08-28) · H-16 y Acta de Desincorporación a nivel de datos (09-17) · Rutas T-A…T-I (09-17/18) ·
Bienes en solo consulta (10-04) · **Indicadores por rol** (10-06): cada rol ve en Indicadores solo las áreas de sus módulos ·
**Liquidación de Prestaciones Sociales (10-11, mig. 086)** con todas las reglas como parámetros ·
**motor de codificación propia de Bienes (10-11, mig. 087)**, apagado hasta B-73/B-75.

### 0.2 Espera al cliente (sección 3)

Ordenado por lo que desbloquea. **Este es el cuello de botella real de la entrega.** Todo está en
`PREGUNTAS_CLIENTE.md`, con el mismo ID.

| Prioridad | Qué pedir | Desbloquea |
|---|---|---|
| 🔴 1 | **Inventario digital VIGENTE de la encargada** con el **departamento de cada bien** (hoy hay **23 de ~142**, transcritos de los documentos ya entregados — ver 0.3) + **B-73/B-75**, que esperan la última codificación de la Alcaldía | Carga completa de los bienes y **encender** la codificación propia (R-12, ya construida: solo hay que escribir el punto de partida y el catálogo en *Bienes → Codificación*) |
| 🔴 2 | **Prueba general de Nómina con Talento Humano** (decidido 2026-10-06: verifica **la directora, dentro del sistema**, en vez de mandarnos cálculos hechos). En la misma sesión ella: **carga los datos de nómina** de algunos trabajadores de distinto tipo · carga la **cesta ticket y la tasa** del mes (de la maqueta) · genera la **quincena** y la compara con su Excel · genera el **bono vacacional** y escribe sus totales (la columna *Diferencia* dice si el sistema acierta) · carga las **tasas de interés del BCV** y **liquida un egresado real** comparando con su cálculo. Lo que no coincida se nos devuelve con trabajador y monto. ⏳ Falta preparar la **guía paso a paso** de la prueba (pospuesta 2026-10-11) | Confirmar quincena, bono vacacional y Liquidación con datos reales, y dejar cargado al personal |
| 🔴 3 | **Formato del Acta de Desincorporación** y **formato del acta de asignación** (B-79 ✅ respondida) | R-2 (imprimible definitivo; el flujo ya funciona) y R-3 |
| 🟡 4 | **B-74** (secuencia: «se verá en el oficio») · **B-77/B-78-bis** (¿la relación a la Alcaldía desaparece tras la última codificación?) · nuevo formato de relación con código (B-81 ✅) | Detalles del procedimiento nuevo de Bienes |
| 🟡 5 | **B-83** abogado visador (B-82 ✅ confirmada) | Documento de donación |
| 🟡 6 | Bono de responsabilidad en **Comisión** (N-2 ✅) | Montos **definitivos** (no bloquean: son parámetros) |
| 🟡 7 | **R-72** lista oficial de recorridos · **R-71** cuadro de visitantes del custodio · **formato del oficio de permiso** (T-H) · **visto bueno al acta de pago** (R-39) | Cargar el catálogo de Rutas y fijar dos imprimibles provisionales |
| 🟡 8 | **Credenciales SMTP** | Recuperación de contraseña por correo (ya construida) |
| 🟢 9 | **D-FO05** metas anuales · **D-FO08-bis** varios facilitadores · **D-NEW01** oficio de formación · planilla física de asistencia · cifras de dotación **por departamento** (Suficiencia, B-63 ✅ criterio) · **D-OF03** libro de correspondencia · **D-TX03** históricos | Mejoras y reportes puntuales |

### 0.3 Carga de datos — sin esto el sistema está correcto pero vacío

| Qué | Estado hoy |
|---|---|
| Personal real | **3 empleados** de prueba |
| Datos de nómina por trabajador: sueldo básico, grado de instrucción, **ingreso a la administración pública** (desde 2026-10-04 para **todo** el personal, no solo comisión), carga familiar/hijos, cuenta y banco, tipo de personal | **1 fila** de prueba en `empleado_salarios`. **Los carga Talento Humano** en la prueba general (0.2 · 2), no se nos envían |
| Cesta ticket y tasa del dólar por mes | 1 mes cargado (2026-07, valores de la plantilla) |
| Tasa de interés de prestaciones del BCV por mes (Liquidación) | **0 meses.** Hacen falta todos los meses que abarquen las liquidaciones reales (desde el ingreso del trabajador). Se cargan en la prueba general (0.2 · 2) |
| Catálogo de cargos | ✅ **Cerrado (2026-10-06):** se usan los que ya están en el sistema; IMATUR los agrega, modifica o elimina desde *Cargos* si lo necesita. No se pide documento |
| Los bienes reales | **23 de ~142 (2026-10-11)**, transcritos de los documentos ya entregados: 18 del BM-1 (codificados), 4 del Oficio 179/2026 y la cafetera donada (`database/datos/2026-10-11_bienes_desde_formatos.sql`). Condición, categoría y ubicación de varios quedaron **[por confirmar]**. El inventario vigente completo **sigue pendiente** |
| Catálogo de rutas real | Pendiente de **R-72** |
| Coordinador de *Compra de Bienes y Servicios* | **Vacante** → los movimientos de bienes están bloqueados por diseño (B-32) |
| Datos institucionales de configuración | ✅ Corregidos en la mig. 075 (Presidenta, Resolución 32, Gaceta 87). Falta el visto bueno (**B-82**) y el abogado visador (**B-83**) |
| Configuración de producción | `URL_ROOT`, credenciales de BD (hoy de desarrollo), clave del admin, SMTP. **Quién los define** se pregunta en `PREGUNTAS_CLIENTE.md` |

### 0.4 Lo que ya NO falta

**Ciclo 2026-10-11 (mig. 087):** **codificación propia de Bienes** (R-12 · C-1…C-4, C-7) — siguiente
N° de orden sin reciclar, catálogo de clasificaciones, *codificado por IMATUR* ≠ *reconocido por la
Alcaldía*, interruptor que no enciende sin B-73/B-75.

**Ciclo 2026-10-11 (mig. 086):** **Liquidación de Prestaciones Sociales** — las 3 hojas del formato
del cliente, art. 142 d, mora, cierre con número `LIQ-`, anulación, imprimible y Excel · **toda regla
de la LOTTT y del contrato como parámetro** (garantía, días adicionales, retroactivo, vacaciones,
divisores 30/360, bono 50 %) · tasa del BCV por mes.

**Ciclo 2026-10-04 (mig. 084):** roles por **módulo** (un rol creado en *Roles y Permisos* funciona
sin tocar código; escritura de Bienes como capacidad `InventarioEscritura`) · cerrada la **escalada de
RRHH a Administrador** · nombre real del rol en el menú · **prueba de formularios guardando** (B1
cerrado) · una falta se escala **una sola vez** y **con confirmación** · folio de expediente anunciado
= asignado · **ingreso a la administración pública para todo el personal** · *Registrar sueldo* solo
pide sueldo básico y prima de discapacidad · botón **Guardar** del total del bono vacacional
(estaba desconectado) · **género** del familiar en el alta · Bienes en **solo consulta** sin el
permiso.

**Antes:** 2026-09-17/18 Rutas completas (T-A…T-I) y Bienes (actas, relación, donación) · 2026-08-27
auditoría H-12…H-15, feriados movibles, Nómina N-A…N-D. Detalle en la sección 2 y en `CHANGELOG.md`.

---

## 1. ESTADO GLOBAL

- **RRHH:** ✅ completo, **Nómina incluida** (2026-10-11). **Bono Vacacional v1 ✅** (registro + reporte, mig.059); Vacaciones (días) ✅; egreso/reingreso ✅; traslados ✅; disciplina ✅; constancias ✅.
- **Nómina:** ✅ **los tres documentos construidos** — quincena, bono vacacional y, desde el 2026-10-11, la **Liquidación de Prestaciones Sociales** (fase N-E, mig. 086; `PLAN_MODULO_NOMINA.md` §7). Todas las reglas son parámetros. Faltan solo **datos reales** (personal, sueldos, tasas del BCV) y la verificación del total del bono vacacional por la directora. *Historial:* 🟢 **motor de cálculo construido (2026-08-27, mig. 072).** Fases N‑A/N‑B/N‑C hechas: las primas se derivan, los porcentajes viven en tablas, cesta ticket y tasa del dólar tienen vigencia mensual, hay quincena con snapshot/recálculo/cierre y export de 6 hojas. Las 3 preguntas abiertas **ya no bloquean** (N‑1 y N‑2 son parámetros). Fases **N‑A a N‑D hechas** (mig. 072 y 073): el bono vacacional también calcula sus primas, aunque su **total** sigue confirmándose a mano porque la fórmula no está en ninguna fuente (el sistema muestra su estimación al lado, con la diferencia). Falta solo **N‑E** (Liquidación, bloqueada por N‑3). Lo que realmente falta son **insumos**: sueldos base, grados, cuentas bancarias, cesta ticket y tasa de cada mes. Antes: replanteamiento (2026-08-07). Llegó la plantilla real de nómina quincenal y de sus fórmulas se extrajo **el cálculo completo** — porcentajes por grado académico, escala de antigüedad con tope 30 %, deducciones, aportes y alícuotas. Aparecieron 3 cambios de fondo: son **3 documentos** (se suma la nómina quincenal), **5 tipos de personal** (falta Comisión de Servicio) y las primas **se derivan, no se capturan**. Quedan **3 preguntas**; solo una (N-3) bloquea la Liquidación. Plan por fases en `docs/PLAN_MODULO_NOMINA.md`.
- **Formación / Recepción:** CRUD y reglas operativas completos. Quedan preguntas de impacto medio/bajo.
- **Inventario (Bienes):** fases 1-4 construidas (mig. 062-069) sobre el levantamiento del 2026-08-04 (59 preguntas), que reveló que lo que hacía falta era un **expediente administrativo por bien**, no un CRUD. ⚠️ **2026-09-02: la Alcaldía cambió el procedimiento** — IMATUR pasa a **asignar el código** de sus bienes y el acta de baja pasa a ser **Acta de Desincorporación** por lote (sin oficio de retiro). Lo construido sirve igual, pero hay **7 cambios de código pendientes**, **B-60 reabierta** y 8 preguntas nuevas: ver §3.4 y `docs/PLAN_MODULO_BIENES.md` §2-ter.
- **Turismo (Rutas):** ✅ **2026-09-18 — las nueve fases del rediseño están construidas** (T-A…T-I, mig. 078-083). Solo quedan del cliente el **formato del oficio de permiso**, el **visto bueno al acta de pago** y **R-72** (lista oficial del catálogo, necesaria para cargar los recorridos reales). *Antes:* 🟢 **2026-09-17 — el módulo quedó DESBLOQUEADO.** Llegaron **R-17…R-64**, **siete formatos reales** (`docs/formatos/rutas_*`) y —dentro de `PREGUNTAS_CLIENTE.md` §4— las cinco respuestas que llevaban meses frenando el rediseño: **R-14** (los estados son **Programado / Ejecutado / No ejecutado**) y **R-37…R-40** (el cobro, y **piden contabilidad completa**, no solo constancia). **63 de 64 respondidas.** Los formatos aportan: la **Ficha Institucional** resulta ser a la vez la planilla del día y el informe de cierre; Cumaná Histórica tiene **7 puntos**, no 5; y R-20 destapa un proceso que no estaba en el plan — los **oficios de permiso a las instituciones custodias**, agrupados por semana y con estado. Aparecen **dos fases nuevas** (T-H oficios de permiso · T-I restricciones y cupo) y se **recorta** alcance: sin modo campo (R-46), sin galería (R-49), sin transporte (R-58), sin encuesta (R-59). *(El rediseño T-A, entonces pendiente, se hizo ese mismo día.)* Ver `PLAN_MODULO_RUTAS.md` §1-bis. *Antes:* 🔴 **2026-09-03 — llegó el levantamiento parcial (R-01…R-16) y confirma el rediseño.** El cliente respondió justo el bloque que define la estructura: **existe un catálogo de rutas** (R-08) y dos salidas de la misma ruta son *"la misma ruta ejecutada 2 veces"* (R-07) → `rutas` se parte en **catálogo** + **`ruta_ejecuciones`**. Además: **sí se cobra** (R-02, 5/15/25 $ — D-RT02 deja de ser incógnita), **la Presidencia aprueba** cada salida (R-13), se **cancela con motivo** y se **reprograma conservando la misma salida** (R-15/R-16), y **vuelve** la institución solicitante (R-11 desmiente D-RT05). **La mitad del rediseño se puede construir ya** (fases T-A/T-B/T-C); lo demás espera **R-14** (nombres de estado, quedó en blanco), **R-37…R-40** (cobro) y R-17…R-64. Análisis y fases en **`docs/PLAN_MODULO_RUTAS.md`**.
- **Cuello de botella de la entrega:** ya **no es código**, son **decisiones/insumos del cliente** (sección 3).

---

## 2. ÚLTIMOS CAMBIOS

> 📓 **El historial completo vive en [`CHANGELOG.md`](CHANGELOG.md)** — 30 entradas fechadas desde
> 2026-06-21, con el porqué de cada decisión. Aquí solo lo más reciente, para dar contexto a lo que
> falta. **Un backlog dice qué falta, no qué se hizo.**

| Fecha | Qué se hizo | Mig. |
|---|---|---|
| 2026-10-11 | **Bienes: motor de codificación propia** (R-12), construido y apagado hasta B-73/B-75; secuencia, dígitos y modo de secuencia configurables | 087 |
| 2026-10-11 | **Liquidación de Prestaciones Sociales** (N-E): hojas FORMATO, INTERESES e INTERÉS DE MORA del cliente, art. 142 d, tasa del BCV por mes, cierre `LIQ-`, imprimible y Excel. **Toda regla de la LOTTT y del contrato pasó a parámetro** | 086 |
| 2026-10-04 | **Roles por módulo**: un rol creado desde la pantalla funciona sin tocar código; cerrada la escalada de RRHH a Administrador; escritura de bienes como permiso propio | 084 |
| 2026-09-18 | **Rutas: fases T-D, T-E, T-H, T-F y T-C** — cierre/reprogramación, solicitud y aprobación, permisos a custodios, itinerario y personal, cobro completo. **H-14 cerrado** | 081 – 083 |
| 2026-09-17 (c) | **Rutas: T-A (catálogo ≠ salida, H-18 cerrado), T-B + T-I (edad y cupo por recorrido, H-17 cerrado) y T-G (Ficha Institucional)** | 078 – 080 |
| 2026-09-17 (b) | **H-16 cerrado**: el reporte de bajas medía la papelera, no las desincorporaciones · «Dado de baja» → **«Desincorporado»** (C-6) | 076 |
| 2026-09-17 | **Bienes: llegaron 2 de los 4 formatos** y quedaron construidos — oficio de relación a la Alcaldía y documento de donación. Corregidas la resolución y la gaceta que se imprimían en 5 documentos reales | 075 |
| 2026-09-13 | La **tasa del dólar** se consulta al BCV como *sugerencia*, nunca automática (la nómina no sale a internet: usa la tasa congelada del período) | 074 |
| 2026-08-28 | Deuda técnica: `ReportesController` partido en 8 traits (3.405 → 101 líneas) · a11y de formularios (88 → 393 `label[for]`) · utilidades CSS | — |
| 2026-08-28 | **Tareas programadas** instalables con un comando · generador de feriados movibles por año | — |
| 2026-08-27 | **Nómina: motor de cálculo** (fases N‑A…N‑D) — primas derivadas, porcentajes en tablas, quincena con cierre, export de 6 hojas, bono vacacional migrado al motor | 072 · 073 |
| 2026-08-04/05 | **Módulo de Bienes reconstruido**, fases 1-4: expediente administrativo por bien, `estatus` vs `condicion`, movimientos, mantenimiento, conteo por cambio de gestión | 062 – 069 |

---

## 3. DECISIONES / INSUMOS PENDIENTES DEL CLIENTE 🔒

Bloquean desarrollo. **Esta sección es la fuente única**: cada ID (B-xx, R-xx, N-x, D-xx) se enuncia
aquí y los demás documentos lo referencian.

> 🖨️ **Para enviarle al cliente:** `PREGUNTAS_CLIENTE.md` — lo mismo, en lenguaje llano y
> **organizado por módulo**, con qué funciona ya y qué le falta a cada uno. Al responder el cliente,
> actualizar **los dos** (son las únicas dos copias, a propósito: distinta audiencia).

### 3.0 🔴 Proveedor SMTP para recuperación de contraseña (2026-07-12)
- **Falta:** credenciales reales de un servidor de correo saliente (host/puerto/usuario/clave) para que la recuperación de contraseña por correo (ya implementada, mig. 058) pueda enviar correos de verdad.
- **Preguntar:** ¿usan Gmail/Google Workspace (contraseña de aplicación), un correo institucional propio (gobernación/alcaldía), u otro proveedor?
- **Al desbloquear:** completar `SMTP_HOST/PORT/USER/PASS/ENCRYPTION` en `config/config.php` (no requiere tocar código ni migraciones).

### 3.1 🟡 Nómina / Liquidación (R-11 · D-RH34/D-RH14) — **construido completo: fases N-A a N-E (2026-10-11)**

> **Estado:** el módulo **calcula los tres documentos**. Motor puro con pruebas, porcentajes en tablas,
> parámetros mensuales con vigencia, 5 tipos de personal, nómina quincenal con export de 6 hojas, bono
> vacacional migrado al motor y **Liquidación de Prestaciones Sociales (N-E, mig. 086, 2026-10-11)**.
> Lo que falta del cliente: **datos** (sueldos, tasas del BCV por mes), una **liquidación real ya
> calculada** para comparar, y la verificación del total del bono vacacional.
>
> **El total del bono vacacional lo verifica la directora de Talento Humano (decidido 2026-10-06).**
> La fórmula del *total* no está en ninguna fuente, así que el sistema calcula una estimación bajo
> supuesto declarado y muestra la diferencia contra el total confirmado (mig. 073). Ya **no se le
> pide al cliente un mes calculado**: la directora genera un período en `/nomina`, captura el total
> que ella calcula para cada trabajador y la columna *Diferencia* dice si el supuesto acierta. Si
> coincide, el total se acepta con `aceptarCalculados()`; si no, nos pasa las diferencias y se
> corrige la fórmula. **Requisito previo:** sueldos, grados, fechas e hijos cargados, y la cesta
> ticket del mes en `/nomina/parametros` — sin eso la estimación no es comparable.

> **Análisis completo y modelo de cálculo extraído: `docs/PLAN_MODULO_NOMINA.md`. Leerlo antes de tocar el módulo.**

> ### 📝 2026-10-06 — Respuestas a N-1…N-4 (notas del equipo sobre el entregable del 06/10)
>
> Son **notas de lo que dijo el cliente**, no un formato: aplica la regla de abajo — ningún número
> entra al código si choca con un formato. Texto original en
> `docs/entregables/Preguntas_y_recaudos_IMATUR_2026-10-06.odt`.
>
> | # | Lo que respondieron | Lectura | Estado |
> |---|---|---|---|
> | **N-2** | «La base de cálculo son **4 semanas**» | El ×5 de algunas hojas era descuido. Coincide con el default (`nomina_semanas_default = 4`) | ✅ **Cerrada.** Sin cambio de código |
> | **N-3** | «Los días dictados en la ley: 15 por trimestre = 60 al año; **2 días adicionales por año** a partir del segundo (60, 62, 64…), acumulativos **hasta 30**» | Es la garantía de prestaciones del **art. 142 LOTTT**. Pero **no explica los números de la columna** (79, 82, 120, 150 / 360): con esa regla saldrían 60, 62, 64. Lo más probable es que esa columna sean **los días de las alícuotas del salario integral** — 79 = 75+4 y 82 = 75+7 (bono vacacional), 120→150 (bono de fin de año, que ya usamos en 150) | ✅ **Cerrada.** El cliente confirmó (N-3-bis = **sí**): en esa columna se colocaron los días del **bono vacacional** y del **aguinaldo**. La N-E ya se puede construir |
> | **N-1** | «Personal **fijo 85**, contratados **45**, y a todos **+1 día por año**»; de Alto Nivel quedó la frase «alto nivel contratado» sin cifra | Los días base dependen de la **condición** del trabajador, no del tipo de personal: «alto nivel contratado» se refiere a **los de alto nivel que sean contratados**: cobran los 45 de los contratados; el alto nivel fijo, los 85 de los fijos. Se descarta el «75 para todos» de la plantilla quincenal | ✅ **Cerrada y aplicada (mig. 085):** fijo **85**, contratado **45** (el alto nivel y la comisión según su contrato), **+1 día por año a todos**. La alícuota de la quincena usa los mismos días |
> | **N-4** | Llega una **maqueta** al inicio de cada mes; el sueldo no varía con el dólar, **solo la cesta ticket** («anclada al BCV»). «**Cada nómina lleva la suya** según la cesta realizada» | Contesta «¿una por mes?» con un **no**, pero no dice si «cada nómina» es cada **quincena** o cada **hoja** (tipo de personal) — la plantilla trae 36,58 y 36,23 en hojas distintas del mismo período | ✅ **Cerrada (aclarado 2026-10-06).** La maqueta llega **una vez al mes** y trae la cesta ticket y la tasa: se cargan **a mano** en `/nomina/parametros`, uno por mes, que es lo que el sistema ya guarda. El botón *Consultar BCV* es solo referencia. Los 36,58/36,23 de la plantilla eran datos de prueba. Si alguna maqueta trajera valores distintos por tipo de personal, se reabre |
> | Bono resp. | Las divisas **las dicta la contratación colectiva de la Alcaldía**; aplica **solo a Alto Nivel, fijo o contratado** — no a fijos ni contratados | El código ya lo hace para Alto Nivel (el nivel del cargo manda sobre el contrato). La diferencia es **Comisión de Servicio**: la plantilla le paga el bono y la respuesta no la menciona | 🟡 Repreguntar si Comisión **sin** cargo de alto nivel cobra. No se toca `TIPOS_CON_RESPONSABILIDAD` mientras tanto |

- **Hecho (2026-07-16, mig.059):** Bono Vacacional v1 = **"registro + reporte"**: Talento Humano captura/verifica sueldo, primas y el total final; el sistema organiza y exporta el `.xlsx` multi-hoja en el formato exacto. Incluye `empleado_salarios`, módulo `/nomina`, parámetros en `/config` y `XlsxMultiSheet`.

- **Nuevo (2026-08-07):** el cliente entregó **la plantilla real de nómina quincenal** (`INSTITUTO IMATUR JULIO 2026.xlsx`, con datos de prueba pero **fórmulas reales**) y 4 audios de Talento Humano (transcritos en `docs/formatos/transcripcion_audios_rrhh_2026-07-23.md`). De ahí se extrajo **el cálculo completo**: porcentajes de prima de profesionalización por grado, escala de antigüedad por años (con tope 30 %), transporte, hijos, deducciones, aportes patronales y alícuotas. **Ya no hay que adivinar la fórmula.**

- **Tres cambios de fondo respecto de lo que creíamos** (detalle en el plan §1):
  1. Los documentos de nómina son **3, no 2**: se suma la **nómina quincenal regular** — esto **responde la antigua pregunta 4**.
  2. Los tipos de personal son **5, no 4**: falta **Comisión de Servicio**, con hoja y cálculo propios.
  3. Las primas **no se capturan, se derivan**. `empleado_salarios` guarda los resultados cuando debería guardar las entradas (ver plan §4).

- **Estado de los 3 documentos:**

  | Documento | Estado |
  |---|---|
  | **Bono Vacacional** | ✅ Recibido; calcula con el motor (mig. 073). Solo el **total** se confirma a mano hasta que la directora de Talento Humano lo verifique en el sistema |
  | **Nómina quincenal regular** | ✅ **Recibida 2026-08-07** y **construida** (mig. 072: cálculo, quincena con cierre y export de 6 hojas) |
  | **Liquidación de Prestaciones Sociales** | ✅ Recibida (`LIQUIDACION MES JULIO 2026.xls`) y ✅ **construida** (mig. 086, 2026-10-11). Falta compararla contra una liquidación real ya calculada |

- **Preguntas abiertas al 2026-10-06** (las originales N-1…N-4 se respondieron el 06/10; ver el bloque de arriba):

  | # | Pregunta | Bloquea |
  |---|---|---|
  | **Bono resp.** | ¿Un trabajador en **comisión de servicio** que **no** ocupa cargo de alto nivel cobra el bono? (la plantilla lo paga en la hoja de Comisión) | `Nomina::TIPOS_CON_RESPONSABILIDAD` |

  ✅ **N-2 cerrada:** la base son **4 semanas**. ✅ **N-3 cerrada:** 79/82 = días del bono vacacional, 120/150 = días del aguinaldo (confirmado 2026-10-06). ✅ **N-4 cerrada:** cesta ticket y tasa salen de la **maqueta mensual** y se cargan a mano; el BCV es referencia. ✅ **N-1 cerrada (mig. 085):** fijo **85**, contratado **45** (el alto nivel y la comisión según su contrato), **+1 día por año a todos**.

- **Ya resueltas** (no volver a preguntar): ✅ **N-2: 4 semanas** (2026-10-06) · ✅ las divisas del bono de responsabilidad las fija la **contratación colectiva de la Alcaldía** · ✅ existe formato de nómina regular aparte (era la pregunta 4) · ✅ cesta ticket cambia **mensualmente**, lo publica la **UNAPRE** · ✅ la **"tasa BCV" es el tipo de cambio del dólar** — el bono de responsabilidad se pacta en divisas y se paga al cambio · ✅ la **caja de ahorro no la paga la gobernación** (queda en 0 por regla) · ✅ los % de prima profesional por grado académico.

- **⚠️ 7 defectos detectados en la plantilla del cliente** (plan §5), verificados contra los valores calculados: el tramo ≥23 años paga **el doble** la prima de antigüedad, el FAOV patronal de la hoja de Comisión está al **20 % en vez de 2 %**, la fórmula de antigüedad de esa hoja está **corrupta** (`C621`, `ij6f`), y la fila de Obreros del RESUMEN está **desplazada una columna**. Están en las fórmulas, así que sobreviven a cualquier mes real. **Avisárselo al cliente** — es la mejor justificación del módulo.

- **Insumos operativos que siguen faltando:**
  - [ ] Sueldo base, grado de instrucción, **fecha de ingreso a la administración pública** (para todo el personal desde 2026-10-04), carga familiar/hijos y **cuenta bancaria** de cada empleado activo (hoy `empleado_salarios` tiene 1 fila de prueba).
  - [ ] Cesta ticket vigente **con su mes** (julio: 22.907; al 23/07 el cliente dijo 28.388 — cambia mensual).
  - [ ] Cesta ticket y tasa del dólar de cada mes: **las trae la maqueta de nómina** que reciben al inicio del mes y se **cargan a mano** en `/nomina/parametros` (N-4, cerrada 2026-10-06). El botón *Consultar BCV* (mig. 074) queda solo como referencia.
  - [ ] La **tabla de escala salarial por grado** que Talento Humano ofreció en el último audio (tramo confuso, confirmar).

- **Construcción pendiente:** ninguna. ✅ **N-E hecha el 2026-10-11 (mig. 086)** con las fórmulas reales de las tres hojas del formato. Al construirla se aclaró que la «tasa» de la hoja INTERESES es la **tasa de interés de prestaciones del BCV** (% mensual = anual ÷ 12), **no** el tipo de cambio. Se toman dos decisiones que la plantilla no trae: pagar lo mayor entre garantía y retroactivo (art. 142 d) y usar la regla escrita de los días adicionales (2 por año desde el segundo, hasta 30) en vez de los 6/8/10 de la hoja de prueba. Detalle en `PLAN_MODULO_NOMINA.md` §7.
- **Insumos nuevos para la Liquidación:** [ ] **tasa de interés de prestaciones sociales del BCV** de cada mes que abarquen las liquidaciones (se cargan en *Nómina → Parámetros*) · [ ] una **liquidación real ya calculada** para compararla.

> **Regla:** ningún número entra al código desde un audio. De 7 afirmaciones numéricas de las notas de voz, **3 resultaron equivocadas** al contrastarlas con la plantilla.

### 3.2 ✅ B13 — Mínimo de antigüedad para constancia — **DECIDIDO (2026-06-25): SIN mínimo**
- **Decisión del cliente:** **no** se exige antigüedad mínima para emitir constancias (se descarta el "mínimo 6 meses"). El mínimo de contrato ya se aclaró en otra sesión.
- **Acción:** ninguna — el sistema ya emite constancias sin exigir antigüedad (`Constancia::crear` no valida tiempo de servicio). B13 cerrado.

### 3.3 ✅ O1 — Cargos por departamento — **DECIDIDO (2026-06-25): cargos GENERALES**
- **Decisión del cliente:** los cargos son **transversales/generales** (no por departamento), tal como ya estaba implementado. El empleado tiene `id_cargo` e `id_departamento` independientes; un mismo catálogo de cargos sirve para todos los departamentos.
- **Acción:** ninguna. Se evaluó vincular cargo↔departamento (mig. tentativa 053) y se **descartó/revirtió** por esta decisión.

### 3.4 ⚠️ Inventario — levantamiento completo (2026-08-04), **pero el procedimiento cambió (2026-09-02)**

> ### 📝 2026-10-06 — Respuestas a B-73…B-83 (+ una planilla antigua como muestra)
>
> Notas del equipo sobre `docs/entregables/Preguntas_y_recaudos_IMATUR_2026-10-06.odt`. Además
> entregaron una **planilla antigua** de la encargada **solo para ver cómo llevan su listado
> interno** (`docs/formatos/planilla_inventario_encargada_MUESTRA_antigua.docx`). **No es el inventario
> vigente**: no se carga ni se concilia. Lo que enseña del formato, en `PLAN_MODULO_BIENES.md`
> §2-quinquies.
>
> | ID | Lo que respondieron | Estado |
> |----|---------------------|--------|
> | **B-73** | Siguen esperando la **última codificación de la Alcaldía**; el sistema debe arrancar de ahí | 🔒 Sigue abierta (no depende de ellos). El motor ya está construido (mig. 087): el dato se escribe en *Bienes → Codificación* |
> | **B-75** | Igual: esperan la última codificación para tomar los códigos de ahí | 🔒 Sigue abierta. Hoy todo lo conocido es `2-01-108` |
> | **B-74** | «Se verá en el oficio pasado» | 🔒 Sin respuesta útil todavía. Ya no bloquea: dígitos y «una o varias secuencias» son parámetros (mig. 087) |
> | **B-76** | **No se recicla**: el código es del bien aunque esté dañado | ✅ **Cerrada.** El código ya lo cumple: `findByNroOrden()` cuenta los desincorporados (siguen `is_active = TRUE`) |
> | **B-79** | Firman **solo dos** (Coordinadora de Bienes + Presidencia). **Sí** lleva correlativo de IMATUR. Sobre el acta sellada por la Alcaldía: «no lo han hecho; IMATUR sí debe tener el suyo **por cada bien**» | ✅ Firmas y correlativo **cerrados** (ya es así). ⚠️ «por cada bien» → **B-79-bis** |
> | **B-77** | La relación se hace **cada vez que llega un bien nuevo** | ✅ Disparador: por bien, no por lote mensual. El monto en Bs quedó sin contestar |
> | **B-78** | La Alcaldía devuelve **el inventario actualizado**; pero **desde la nueva codificación ya no se envía a la Alcaldía**: queda en IMATUR, que se encarga de desincorporar, registrar bienes nuevos y codificarlos | ⚠️ **Cambia el alcance → B-78-bis** |
> | **B-81** | Viene un **formato nuevo con columna de código** | ✅ **Cerrada.** Esperar el formato (no solo agregar la columna: el cuerpo del oficio actual pide codificar) |
> | **B-80** | **No existe por escrito**: fue de palabra | ✅ **Cerrada.** Queda como riesgo: la responsabilidad de codificar se asume sin respaldo documental |
> | **B-82** | «Sí» | ✅ **Cerrada.** Resolución 32 / Gaceta 87 confirmadas |
> | **B-83** | «No lo sé» | 🟢 Sigue abierta; el bloque no se imprime mientras no se configure |
> | **B-63** | El aire es **del espacio**; «**cada departamento** tendrá una cantidad de bienes» | ✅ Criterio cerrado: la dotación es **por departamento**, no por empleado. **Hay que rehacer `inventario_dotacion`** (hoy `unidades_por_empleado`). Faltan las cifras |
> | 11 categorías | «Sí» | ✅ **Cerrada.** Aprobadas |
>
> ### 📋 Abiertas al 2026-10-06 (después de las respuestas) — espejo llano en `PREGUNTAS_CLIENTE.md` §2
>
> | ID | Pregunta | Bloquea |
> |----|----------|---------|
> | 🔴 Insumo | **Inventario digital VIGENTE de la encargada**, con el **departamento** de cada bien (la planilla antigua no lo trae; el BM-1 sí, por bandas) | Carga de los bienes reales, responsable derivado, conteo y suficiencia |
> | 🔴 **B-73 / B-75** | Siguen esperando la última codificación de la Alcaldía | R-12 |
> | 🔴 Formato | **Acta de Desincorporación** | R-2 — el imprimible es provisional |
> | 🔴 Formato | **Acta de asignación** («acta de encargado») | R-3 |
> | 🟡 **B-78-bis** 🆕 | Si tras la última codificación **nada se envía a la Alcaldía**: ¿el Acta de Desincorporación **sigue necesitando el sello de la Alcaldía** como aval, o la firman solo IMATUR? ¿Y la relación de bienes nuevos deja de emitirse? | El flujo de baja (hoy: registrar el acta sellada) y R-1 |
> | 🟡 **B-79-bis** 🆕 | «IMATUR debe tener el suyo por cada bien»: ¿un **acta por cada bien desincorporado**, o un acta por lote que lista varios bienes (lo construido)? | C-5 — el acta por lote |
> | 🟡 **B-74** | Una secuencia o varias; qué pasa después de 999 | Algoritmo del siguiente número |
> | 🟡 Formato | **Relación de bienes nuevos con código** (B-81) | Sustituir el oficio de junio |
> | 🟢 **B-77 (monto)** | ¿El monto va en Bs a la fecha de compra? | Contenido de la relación |
> | 🟢 **B-83** | Abogado visador | Documento de donación |
> | 🟢 B-63 (cifras) | Cuántos bienes por **departamento** y categoría | Suficiencia de Bienes con datos reales |
> | ⚙️ Acción interna | Asignar el Coordinador de *Compra de Bienes y Servicios* | Movimientos de bienes (bloqueados por diseño, B-32) |

> ### ✅ 2026-09-17 — Llegaron 2 de los 4 formatos, y quedaron construidos (mig. 075)
>
> La Directora de Bienes entregó tres documentos. Archivados en `docs/formatos/`.
>
> | Recibido | Qué es | Estado |
> |---|---|---|
> | **Oficio N° 179/2026** (10/06/2026) `oficio_relacion_bienes_nuevos_alcaldia_2026-06-10.jpg` | Relación de bienes nuevos al Coordinador de Bienes de la Alcaldía. Tabla `CANTIDAD │ DESCRIPCIÓN DEL BIEN │ MONTO EN Bs` | ✅ **Construido** — `/inventario/relaciones` |
> | **Documento de donación** (18/02/2026) `documento_donacion_bien_2026-02-18.jpg` | Declaración del donante + aceptación de la Presidenta, con visado de abogada, firmas y huellas | ✅ **Construido** — hoja de vida del bien → «Documento de donación» |
> | **Formulario BM-1** | **Repetido.** Es el mismo formato recibido el 2026-08-04, esta copia en blanco | ➖ No aporta |
>
> **⚠️ B-81 — el oficio contradice el cambio del 2026-09-02.** Es de **junio**, anterior a la
> notificación, y dice lo contrario de lo levantado: *«se le solicita a la Coordinación que dirige,
> les sean asignados los respectivos códigos»*. Su tabla **no tiene columna de código**, que era
> justo el dato que el formato nuevo debía traer. La Directora además lo describió como *«el que se
> le pasa a la Alcaldía para que venga hacer la codificación»* — sigue narrando el procedimiento
> viejo. **Se construyó fiel a lo entregado**; si el cliente confirma el formato nuevo, el cambio es
> agregar la columna en la vista imprimible (el modelo ya guarda el código de cada bien).
>
> **B-82 — resolución y gaceta.** Los dos documentos, firmados y sellados, declaran
> **Resolución N° 32 del 05/09/2025** y **Gaceta Municipal Extraordinaria N° 87 del 05/09/2025**.
> El sistema tenía 025 (15/03/2024) y 042 (20/01/2024) — datos de relleno que se imprimían en
> **cinco documentos reales**: constancias de trabajo, carta de aceptación y de culminación de
> pasantes, y los dos oficios de rutas. Corregido en la mig. 075; falta el visto bueno.
>
> **Siguen faltando:** **Acta de Desincorporación** (por lote, C-5) y **acta de asignación**
> («acta de encargado»). Sin sus formatos no se construyen: es la misma razón por la que estos dos
> esperaron desde agosto.
>
> **Nota de modelado:** en el oficio original los banderines figuran con cantidad 2 en una sola
> fila. El sistema registra **cada bien individualmente** (B-09/B-62: la cantidad del código siempre
> vale 1), así que dos banderines salen como **dos renglones de 1**. Es intencional — es lo que
> permite que cada uno tenga su propio N° de orden y su propia hoja de vida.

> ### 🔴 2026-09-02 — La Alcaldía notificó un procedimiento nuevo: **IMATUR codifica sus propios bienes**
>
> Detalle completo, consecuencias en el código (C-1…C-7) y las 8 preguntas nuevas (B-73…B-80) en
> **`docs/PLAN_MODULO_BIENES.md` §2-ter**. Resumen:
>
> | Qué cambia | |
> |---|---|
> | **Codificación** | IMATUR asigna el código, **continuando la secuencia** desde el último que la Alcaldía deje en su **última revisión — que todavía no se ha hecho**. La Alcaldía ya no viene a codificar |
> | **Informe de bienes nuevos** | Deja de pedir inspección: pasa a ser una **relación** de bienes **ya codificados**, con su **monto**, para que la Alcaldía mantenga su registro patrimonial |
> | **Acta de baja** | Se llama **Acta de Desincorporación** (así debe mostrarse), es **por lote** y la Alcaldía la **firma y sella** = aval del retiro. **El oficio de retiro sale del alcance** |
> | **Acta de asignación** ("acta de encargado") y **oficio de donación** | **Siguen vigentes**, hay que construirlos |
> | **Formatos** | El cliente los enviará **cuando tenga los nuevos** (dos de los cuatro cambiaron) |
>
> **Lo construido sigue sirviendo** (registro, expediente, movimientos, mantenimiento, responsable
> derivado, conteo): cambia **quién ejecuta** la codificación. Pero **nada de esto está
> implementado** y hay dos bloqueos nuevos que no son formatos:
>
> - **B-73 — punto de partida de la secuencia** (sin él no se puede codificar).
> - **B-75 — catálogo de grupos/subgrupos/secciones**: **reabre B-60**, que se había cerrado con el
>   argumento «IMATUR solo transcribe». Si ahora clasifica, necesita la lista de valores válidos.
>
> **Respuestas útiles de la misma conversación:**
> - ✅ **B-71: sí hay versión digital** de todos los documentos **y de un inventario interno que la
>   encargada lleva aparte** → **pedir ese archivo**: desbloquea la carga de los ~142 bienes, el
>   catálogo de códigos en uso y el punto de partida.
> - ✅ **B-72: los saltos en el N° de orden NO son bajas** — el listado va **por departamento, no por
>   código**. Se confirmará al ordenar el digital por código. El punto de partida es el `MAX` de
>   **todo** el archivo, no el último de una hoja.
> - ⚠️ **B-69 matizada:** el costo era control interno, pero el **monto sí se declara** en la nueva
>   relación. El dato ya se captura.

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
| 🟡 Formatos | **Actualizado 2026-09-17.** ✅ **Relación de bienes nuevos** y ✅ **oficio de donación**: recibidos y **construidos** (mig. 075). 🔴 Faltan **Acta de Desincorporación** (por lote) y **acta de asignación**. ~~Oficio de retiro~~ **eliminado del alcance**. El BM-1 se recibió en agosto. ⚠️ Ver **B-81**: el oficio entregado es el del procedimiento anterior. |

### 3.5 Turismo (Rutas)

> ✅ **2026-09-18 — construido por completo (T-A…T-I).** De esta sección solo siguen vivos: el **formato del oficio de permiso**, el **visto bueno al acta de pago** (R-39) y **R-71/R-72**. El resto queda como registro de las respuestas.

> **Actualizado 2026-09-17 — llegaron R-17…R-64 y SIETE formatos reales** (`docs/formatos/rutas_*`).
> **57 de 64 preguntas respondidas.** Análisis, modelo y fases: **`docs/PLAN_MODULO_RUTAS.md`**
> (leer **§1-bis**). Respuestas en `PREGUNTAS_DESCUBRIMIENTO_Bienes_Rutas.md` Parte 2.
>
> **Lo que destraba:** de siete fases bloqueadas quedan **una y media**. **T-F** (itinerario y varios
> empleados por salida) y **T-G** (la Ficha Institucional, que es el informe de cierre) quedaron
> **desbloqueadas**, y aparecen **dos fases nuevas**: **T-H** (oficios de permiso a las
> instituciones custodias, R-20) y **T-I** (restricciones por ruta y cupo diario, R-57/R-28).
>
> **Lo que recorta:** R-46 confirma que el registro se hace **en la oficina** → sin modo campo, app
> móvil ni offline. R-49/R-63 → sin galería de fotos. R-27 → sin autorización de representante.
> R-58 → sin transporte. R-59 → sin encuesta. R-61 → sin temporadas.

> ## 🔓 2026-09-17 — **El módulo quedó DESBLOQUEADO**
> Las cinco preguntas que llevaban meses frenando el rediseño están contestadas. **Ya no hay nada
> que esperar del cliente para construir.** Ojo: **R-14 y el bloque de cobro se respondieron dentro
> de `PREGUNTAS_CLIENTE.md` §4**, no en el cuestionario.

**Respuestas que destraban (antes bloqueantes):**

| ID | Respuesta |
|----|----------|
| ✅ R-14 ⭐ | **Los estados son TRES, no cinco:** `Programado` → `Ejecutado` \| **`No ejecutado`**. **Cancelar no es un estado**: lleva a *No ejecutado* con su motivo, y de ahí sale la **reprogramación**. Textual: *"Programado, Ejecutado, o No ejecutado… a veces los solicitantes cancelan (IMATUR puede cancelar por razones ajenas: agua, clima, terremotos). En estos casos se haría una reprogramación"* |
| ✅ R-37 ⭐ | **Cobra IMATUR**, en una **cuenta exclusiva** para rutas |
| ✅ R-38 ⭐ | **Pago ANTICIPADO con fecha tope**: *"se les tiene una fecha para cancelar y poder planificar la salida"* → la salida lleva **fecha límite de pago**, y el pago condiciona la planificación |
| ✅ R-39 ⭐ | Transferencia → **captura/voucher adjunto**. Efectivo → **acta de pago** que levanta IMATUR. ⚠️ *"El formato nace del momento, **pueden darnos una idea**"* → **el cliente nos pide proponer ese formato** |
| ✅ R-40 ⭐ | **SÍ lleva la contabilidad:** *"sí debe llevar el cobro… y lo cancelado"*. No es declarativo |
| ✅ R-42 | Las **exoneraciones las autoriza la Presidenta**. Y un matiz de R-03: las instituciones públicas **no pagan pero igual traen el oficio**, y eso aplica **solo a Cumaná Histórica** |

**Lo único que queda pendiente:**

| ID | Pregunta |
|----|----------|
| 🟡 Formato | **Oficio de permiso a las instituciones custodias** (R-20) — el proceso apareció el 2026-09-17 y es **la mitad del pedido explícito del cliente** (R-64: *«los reportes y los oficios»*). El flujo se puede construir sin él; el imprimible no |
| 🟢 Formato | **Acta de pago en efectivo** (R-39) — **el cliente pidió que la propongamos nosotros**, así que no bloquea: se diseña y se somete a su visto bueno |
| ⚪ R-32 | ¿Se le paga al guía externo? Sin responder, pero **ya no importa**: R-31 aclara que lo pone el punto visitado |

**Nuevas — surgen de la segunda tanda de respuestas:**

| ID | Pregunta |
|----|----------|
| 🟡 R-71 🆕 | El **cuadro de visitantes del punto** (`rutas_cuadro_visitantes_FundacionCastilloSanAntonio.jpeg`) lo exige **el custodio**, no IMATUR, y **sus casillas no coinciden** con las de la Ficha Institucional (*Niño/Niña/Adolescente/Mujer/Hombre/Adulto mayor* + *Local/Nacional/Extranjero*, frente a *Niños F-M/Docentes/Representantes/Apoyo*). ¿El sistema debe **imprimirlo también**, o lo llenan a mano allá? Si debe imprimirlo, hay que capturar el desglose en **dos cortes distintos** |
| 🟡 R-72 🆕 | **¿Cuántas rutas tiene el catálogo y cuál es el nombre oficial de cada una?** Los folletos traen **Playa Manare** (que no estaba en R-02) y **«Altos de Sucre»**, no «Altos de Cumaná». **Cargar el catálogo con la lista equivocada es peor que no cargarlo** |
| ~~R-67~~ ✅ | ~~Altos de Sucre~~ — **RESPONDIDA:** *"no es una tarifa fija, depende de lo que el cliente solicite"*, y **la cobra IMATUR**. **Directorio de posadas: NO lo quieren** |
| ~~R-68~~ ✅ | ~~¿Cómo se registra hoy?~~ — **RESPONDIDA:** planificación **en papel**; la estadística se acumula en un **Excel semestral**; cortes **mensuales** que alimentan el **informe de gestión trimestral**. *(Define el histórico a cargar y confirma que el reporte debe poder cortarse por mes y por trimestre.)* |
| ~~R-65~~ ✅ | ~~¿Exploradores es ruta aparte?~~ — **RESPONDIDA, y con una sorpresa: son TRES modalidades del mismo recorrido.** *Cumaná Histórica* = la comercial · *Exploradores de Cumaná* = **solo instituciones educativas** · **«Cumaná Histórica – Huellas del Ayer» = solo adultos mayores**. Una ruta en el catálogo, **tres modalidades por público** |
| ~~R-66~~ ✅ | ~~Edades~~ — **RESPONDIDA dos veces:** Exploradores va de **4 a 16 años** (*"por ser para instituciones educativas"*), **no 4-8** como decía R-02; y R-57 da el caso de **Río Brito: 12+**, sin dificultad visual, advertir condición articular |
| ~~R-69~~ ✅ | ~~¿Cuántos guías por salida?~~ — **RESPONDIDA con un ratio usable: 7-8 niños por guía** (*"una salida de 35 personas irían 3 guías"*), ajustable según disponibilidad. **Y sí quieren registrar quiénes fueron.** El guía externo lo pone **el punto**, no IMATUR (R-31) |
| ~~R-70~~ ✅ | ~~¿Ente custodio del punto?~~ — **RESPONDIDA por R-20**, y con mucho más: todo el proceso de permisos (fase T-H) |
| ~~R-32~~ ⚪ | ~~¿Se le paga al guía externo?~~ — sin responder, pero **pierde sentido**: R-31 aclara que lo pone el punto visitado |

**Estado de las decisiones previas:**

| ID | Estado |
|----|--------|
| 🟠 D-RT02 | **Respondida a medias por R-02: SÍ SE COBRA** (5 $ Cumaná Histórica · 15 $ Río Brito · 25 $ Las Maritas y Playa Colorada · Exploradores gratuita), con exoneración a **menores de 8 años** e **instituciones públicas**. **R-36 añade el cómo: la tarifa se pacta en USD y se cobra en bolívares a la tasa del día** → el catálogo guarda **USD** y la salida congela la **tasa aplicada** (el sistema ya consulta el BCV desde la mig. 074). Las columnas `tiene_tarifa`/`tarifa_monto` **no se eliminan: se capturan**, y la columna del reporte (retirada en H-14) vuelve con dato verdadero. **Falta el flujo** (R-37…R-40) |
| ✅ D-RT03 | ~~Al **Finalizar** una ruta, ¿generar informe/oficio automáticamente?~~ — **RESPONDIDA (R-50): SÍ, automáticamente.** Y R-47/R-48 dicen cuál: la **Ficha Institucional**, dirigida a la Directora de Promoción Turística. Formato en `docs/formatos/rutas_ficha_institucional_IMATUR.jpeg` |
| 🔴 D-RT01 | ~~Cada registro es una ejecución independiente~~ — **DESMENTIDA por R-07/R-08.** Es la base sobre la que se construyó el módulo. **Provoca el rediseño** |
| 🔴 D-RT05 | ~~Instituciones participantes eliminadas (mig. 060)~~ — **DESMENTIDA por R-11:** solicitan por oficio y de eso depende la gratuidad. **Hay que reconstruirlo** |
| ✅ D-RT04 | ~~Facilitador externo: ¿lista o texto libre?~~ — la columna se eliminó (mig. 060), y **R-31/R-33 cierran el fondo**: la salida la **encabeza siempre un empleado de IMATUR**, acompañado por **un número variable de empleados** según el tamaño del grupo → **tabla de empleados por salida**. El **guía externo lo pone el punto visitado** (museo, casa natal), así que **no se modela como facilitador de la salida** sino, si acaso, como dato del punto |
| ✅ D-OF03 | ~~Libro de correspondencia unificado~~ — **CONFIRMADO como requisito por R-53/R-54:** los oficios de ruta llevan **numeración correlativa** y **sí se lleva un libro**. Deja de ser «mejora opcional» |

### 3.6 Formación
| ID | Pregunta |
|----|----------|
| ✅ D-FO06 | ~~¿CRUD de **oficios base** (`oficios`) + vínculo con `talleres.id_oficio`?~~ — **CERRADO 2026-08-04:** tabla y columna eliminadas (mig. 060). Si el cliente pide llevar registro de oficios **recibidos**, se construye desde cero como módulo propio. |
| ⚠️ D-FO05 | **Reclasificada (2026-08-28): ya está construido, falta el dato.** `meta_talleres_anio` y `meta_rutas_anio` existen en Configuración y alimentan el indicador *planificado vs. ejecutado* (`ReportesController` ~L2291). Hoy valen **100 cada una, de relleno**. No es una decisión de diseño: hay que **pedir las metas reales** (pregunta D4) |
| 🔒 D-NEW01 | **¿Activar el correlativo de oficios de formación (`FORM-XXX`)?** Precisado el 2026-08-28: **no es "cablear una llamada".** Las claves `correlativo_oficio_formacion`/`ano_correlativo_formacion` existen desde la mig. 007 pero **nada las usa**, y `oficios_emitidos` **no tiene `id_taller`**. Falta lo esencial: **qué dice ese documento y a quién se dirige**. Construirlo a ciegas repite el error que se evitó con los formatos de Bienes. Requiere migración + flujo tipo `/rutas/oficio` (~550 líneas de referencia) |

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
| H-04 | Baja de bien no actualiza `condicion` | ✅ **Cerrado** (mig. 062): `estatus` (administrativo) quedó separado de `condicion` (físico); un bien dado de baja **sale** del inventario activo y ya no contamina KPIs ni CMI-I01/I03 | — |
| H-09 | Columnas inertes | ✅ **Cerrado** (mig. 060 + H-14): eliminadas `participantes_ruta.id_institucion`, `rutas.nombre_facilitador_externo`, `talleres.id_oficio`. `rutas.tiene_tarifa`/`tarifa_monto` se conservan pero ya **no se leen** (se quitaron del reporte el 2026-08-27), así que dejaron de producir un dato falso | D-RT02 decide si se capturan o se eliminan |
| H-10 | Tablas sin UI | ✅ **Cerrado** (mig. 060): `oficios` e `instituciones_externas` eliminadas (vacaciones ✅, `taller_inventario` ya lo estaba) | — |

> Resueltos previamente: H-01, H-02, H-03 (visitas inmutables), H-05 (validaciones servidor), H-06 (correlativo atómico), H-07 (enums centralizados), H-08 (FKs validadas), H-11 (género M/F).

**Hallazgos nuevos (auditoría 2026-08-07):**

| # | Hallazgo | Estado |
|---|----------|--------|
| H-12 | **El sidebar contradice al RBAC dinámico.** El Router resuelve permisos desde `permisos_rol` (editable en *Roles y Permisos*), pero `views/inc/header.php` los tenía cableados por número de rol (`in_array($rol,[1,2,3,5])`, 8 casos) | ✅ **Cerrado (2026-08-27, sin migración).** El sidebar se genera con `RolesController::getNavegacion()` + `getNavegacionVisible()`, que resuelven la visibilidad con `roleHasModulo()` — el mismo mapa del Router. Fallaba en **los dos sentidos** y ambos quedaron corregidos: el rol 2 tenía `PasantesController`/`UsuariosController` y no veía los enlaces, el rol 6 tenía `VisitantesController` y tampoco; al revés, «Reportes» se mostraba a todos y el rol 5 (que no lo tiene) aterrizaba en *Acceso Denegado*. Lo no delegable (Bitácora, Municipios, Parroquias) queda marcado con `soloAdmin` en la misma definición. Verificado simulando los 6 roles |
| H-13 | **Tabla huérfana `actividades_ruta`**: cero referencias en `app/` desde que el módulo se retiró (2026-05-31) | ✅ **Cerrado (mig. 070).** `DROP TABLE ... CASCADE`. Verificado antes de soltarla: 0 filas, 0 referencias en `app/`, **0 registros en `audit_logs`** — por eso no hizo falta conservar etiqueta en `auditoria/index.php`. Se retiró también su `setval` de `009_fix_sequences.sql`, que habría hecho fallar esa migración en instalaciones ya actualizadas. 56 → 55 tablas |
| H-14 | **`rutas.tiene_tarifa`/`tarifa_monto` nunca se escriben** pero sí se leían: el reporte decía **«Gratuita» para toda ruta, siempre** — dato falso, no solo columna inerte | ✅ **Cerrado (2026-08-27, sin migración).** Se retiró la columna Tarifa del reporte de rutas (vista + export a Excel, con su fila de totales recolumnada). El PDF nunca la traía. **Las columnas se conservan** a la espera de D-RT02: si el cliente confirma que se cobra, se implementa la captura y se reactiva; si descarta el cobro, se eliminan |
| **H-16** | ✅ **CERRADO (2026-09-17, mig. 076).** Las tres consultas se unificaron en `bajasQuery()`/`bajasTotales()` —estaban **copiadas**, que es justo por lo que el error sobrevivió a la reconstrucción del módulo—, ahora filtran por `estatus = EST_BAJA` con `is_active = TRUE`, fechan por el movimiento de `Baja` y acreditan a quien lo registró. Se añadió la columna *Por retirar / Retirado* (B-67). **Aparecieron dos cosas más al corregirlo:** (1) el **mismo error estaba en el KPI `kpiBajasAnio` del Dashboard**, también corregido; (2) **catorce consultas tenían el literal `'Dado de baja'` cableado**, que con el renombrado de C-6 habrían dejado de filtrar en silencio — ahora usan la constante. Verificado con un bien desincorporado y otro en papelera: el reporte muestra el primero y no el segundo, en listado, Excel y PDF. *Enunciado original:* 🔴 **ABIERTO (2026-09-03) — el reporte «Bienes Dados de Baja» mide la papelera, no las desincorporaciones.** Desde la mig. 062 una baja es `estatus = 'Dado de baja'` **conservando `is_active = TRUE`** (el bien sale del inventario activo pero su registro se preserva, B-38), mientras `is_active = FALSE` es la **papelera** de registros creados por error. Las tres consultas de `ReportesInventarioTrait::bajasInventario()` (listado + total histórico + bajas del año) filtran por `is_active = FALSE AND deleted_at IS NOT NULL`: **una desincorporación real nunca aparece**, y un registro borrado por equivocación **sí** aparece contado como baja. Es el mismo error de fondo que H-04, sobrevivió a la reconstrucción del módulo porque el reporte no se revisó al separar `estatus` de `condicion` | ⏳ **Por corregir.** El dato correcto ya existe: `Inventario::desincorporados()` (filtra `EST_BAJA`), que es lo que usa la pestaña *Desincorporados* del listado. Hay que apuntar el reporte y sus **dos exportaciones** ahí, y fechar por el movimiento de baja (`actividad_inventario`) en vez de por `deleted_at`. Sin migración. Documentado en `INDICADORES_GESTION.md` §4.7. **Gana relevancia con el cambio del 2026-09-02:** la desincorporación pasa a ser el flujo con documento propio (Acta de Desincorporación) |
| ~~**H-17**~~ | ✅ **CERRADO (2026-09-17, mig. 079 — fase T-B).** El rango salió del código y vive en el recorrido: `rutas.edad_min` / `rutas.edad_max` (NULL = sin tope) más `rutas.restricciones` para lo que no es edad (R-57: *«dificultad visual, excluidos»*). `Ruta::motivoEdadNoValida()` es la única regla, y se aplica en los **dos** flujos de inscripción — con cédula y sin ella — y en el formulario, que ya no deshabilita el botón por un 5–11 inventado sino por el rango real de esa ruta. Los rótulos «(5–11)» del informe y del export son ahora «Niñas»/«Niños». Un niño de 4 **sí** se inscribe donde el recorrido lo admite. Historial: 🔴 **ABIERTO (2026-09-03) — el rango de edad 5–11 está cableado y deja fuera a los niños de Exploradores de Cumaná.** `RutasController.php:229` rechaza toda edad menor a 5 (*"El participante debe tener al menos 5 años"*), la vista `rutas/detalle.php` rotula el modo libre como *"Niño/a 5–11 (sin cédula)"* y deshabilita el botón fuera de ese rango (línea 700), y el export del informe titula sus columnas *"Niñas (5-11)"/"Niños (5-11)"*. **R-02 dice que Exploradores de Cumaná es para niños de 4 a 8 años** → hoy **un niño de 4 no se puede inscribir**. El 5–11 se fijó en la migración 017 **sin levantamiento**; ningún dato del cliente lo respaldaba | ⏳ **Por corregir (fase T-B).** El rango sale del código y pasa al catálogo (`edad_min`/`edad_max`, nulo = sin restricción). La regla que se conserva es la de **identificación** (quien tiene cédula se registra con cédula), no la de edad. Requiere migración por las columnas nuevas. Ver `docs/PLAN_MODULO_RUTAS.md` §2 |
| ~~**H-18**~~ | ✅ **CERRADO (2026-09-17, mig. 078 — fase T-A).** `rutas` quedó como **catálogo** y cada salida vive en `ruta_ejecuciones` (fecha, hora, cupo, estado) con su personal en `ruta_ejecucion_empleados`; participantes, informes, oficios, reportes e indicadores apuntan a la salida. Los estados del catálogo (`Activa`/`Inactiva`/`En Mantenimiento`) y los de la salida (`Programado`/`Ejecutado`/`No ejecutado`, R-14) ya no comparten columna. Historial: 🔴 **ABIERTO (2026-09-03) — el modelo de Rutas contradice cómo trabaja IMATUR.** Cada fila de `rutas` es *una salida* (tiene `fecha_visita`, `hora_visita`, `id_facilitador`, `cupo_maximo`), pero R-07/R-08 confirman que **existe un catálogo reutilizable** y que dos salidas de Cumaná Histórica son *"la misma ruta ejecutada 2 veces"*. El propio esquema lo delataba: de los 4 estados, tres describen **una ruta del catálogo** (`Activa`/`Inactiva`/`En Mantenimiento`) y uno describe **una salida** (`Finalizada`) — dos ciclos de vida en una columna. Consecuencia práctica: repetir una ruta 20 veces son 20 filas con sus puntos duplicados, y R-09 (varios grupos el mismo día con guías rotativos) no se puede representar | ⏳ **Por corregir (fase T-A).** Separar `rutas` (catálogo) de `ruta_ejecuciones` (salida). **Los datos no son problema: `rutas` tiene 2 filas.** El costo está en el código (modelo 406 líneas, controlador 702, 5 vistas, trait de reportes). **D-RT01 queda desmentida.** Plan completo en `docs/PLAN_MODULO_RUTAS.md` |
| H-15 | **Las evidencias de talleres eran el último archivo de usuario en `public/uploads/`**: legibles por URL sin control de rol, y el enlace `URL_ROOT.'/public/uploads/...'` **se rompía bajo el vhost** `SIGTUR-IMATUR.test` (donde `public/` ya es la raíz), así que solo se veían en una de las dos URLs documentadas. El bloque de subida además estaba **duplicado** en `store()` y `cambiarEstado()`, y ninguna copia validaba MIME real ni tamaño (confiaban en `$_FILES['type']`, que lo manda el cliente) | ✅ **Cerrado (2026-08-27, sin migración).** Van a `storage/uploads/talleres/` servidas por `DescargaController::taller()` (roles 1,3); subida unificada en `TalleresController::procesarEvidencias()` con extensión + MIME real + ≤5 MB, igual que expedientes/bienes. **`public/uploads/` se eliminó por completo** (quedaban dos carpetas vacías de la migración de junio) y se limpió su bloque del `.gitignore` |

---

## 5. PROGRAMACIÓN FALTANTE / BACKLOG TÉCNICO 🛠️

### 5.1 Reportes/funciones pendientes (implementables, queda lo no hecho del Bloque B)

| Módulo | Tarea | Origen |
|--------|-------|--------|
| ~~Bienes~~ | ✅ ~~Corregir H-16~~ — **hecho (2026-09-17, mig. 076)**, y eran cuatro consultas: el KPI del Dashboard tenía el mismo error | §4 H-16 |
| ~~Bienes~~ | ✅ ~~Acta de Desincorporación a nivel de datos (R-13/C-5)~~ — **hecho (2026-09-17, mig. 077)**. Emisión por lote, retiro en bloque al registrarla firmada, y anulación que revierte el retiro. El imprimible es provisional hasta que llegue el formato | §3.4 · plan §2-ter |
| ~~Bienes~~ | ✅ ~~Renombrar «Dado de baja» → «Desincorporación»~~ — **hecho (2026-09-17, mig. 076)**. Se renombró el **valor**, no solo el rótulo: `inventario` estaba en 0 filas, así que era el momento más barato. Obligó a desclavar el literal de 14 consultas | §3.4 · plan §2-ter |
| ~~Bienes~~ | ✅ ~~Codificación interna con secuencia propia (R-12/C-1…C-4, C-7)~~ — **construida (2026-10-11, mig. 087), apagada**. Para encenderla: **B-73** y **B-75** | §3.4 · plan §2-sexies |
| ~~**Rutas**~~ | ✅ ~~T-A — separar catálogo de ejecución (H-18)~~ **HECHA (mig. 078)** | §3.5 · plan §7 |
| ~~**Rutas**~~ | ✅ ~~T-B — edad por catálogo (H-17)~~ **HECHA (mig. 079).** Junto con **T-I** (restricciones por ruta y cupo diario de 60, configurable en `rutas_cupo_diario`) | §3.5 · plan §2 |
| ~~**Rutas**~~ | ✅ ~~T-C — tarifa y cobro~~ **HECHA (mig. 083)**: tarifa en USD por recorrido, tasa congelada por salida, pagos con abonos y comprobante, exoneración, acta de pago. 🔒 El **acta de pago** es propuesta nuestra (R-39): falta el visto bueno del cliente | §3.5 · plan §5 |
| ~~**Rutas**~~ | ✅ ~~T-D — cierre y reprogramación~~ **HECHA**: cierre con motivo obligatorio y reprogramación como salida nueva enlazada | §3.5 · plan §4 |
| ~~**Rutas**~~ | ✅ ~~T-E — solicitud y aprobación~~ **HECHA**: oficio de la institución archivado con descarga controlada, aprobación de la Presidencia con fecha y autor | §3.5 · plan §3.4 |
| ~~**Rutas**~~ | ✅ ~~T-F — itinerario por salida y personal asignado~~ **HECHA (mig. 082)** | §3.5 |
| ~~**Rutas**~~ | ✅ ~~T-G — informe de cierre~~ **HECHA (mig. 080)**: la Ficha Institucional nace al cerrar la salida y se imprime en el formato oficial. *(También T-H, oficios de permiso, mig. 081 — el imprimible es provisional hasta que llegue el formato)* | §3.5 |
| RRHH | Réplica imprimible del **formato físico de asistencia** — 🔒 necesita el **formato real** (planilla oficial) del cliente para ser fiel | MOD-RRHH 6.2 |
| Formación | Tabla `taller_facilitadores` (múltiples facilitadores) — solo si el cliente lo pide | D-FO08-bis |
| Transversal | Importación de datos históricos desde Excel (depende de D-TX03) | D-TX03 |

### 5.2 Mejoras propuestas (futuro cercano / más adelante) ✨

Propuestas del equipo técnico, no solicitadas aún por el cliente. Priorización sugerida:

| Prioridad | Mejora | Notas de implementación |
|-----------|--------|-------------------------|
| ✅ ~~**a11y en formularios restantes**~~ | **Hecho (2026-10-11).** De 559 `label`: 545 con `for`, 6 envuelven su control y 8 rótulos de grupo con `aria-labelledby`. En bucles el `id` lo genera `sigtur_uid()`. Se encontró y corrigió que pulsar el texto «Bienes a desincorporar/relacionar» marcaba todos (el `label` contenía el botón «Todos»). |
| ✅ ~~**Endurecer `Taller::actualizarPersona`**~~ | **Hecho (2026-10-11):** `Taller::COLUMNAS_PERSONA_EDITABLES`; cualquier otra clave lanza `InvalidArgumentException` antes de llegar al SQL (la cédula no se cambia por esta vía). Con prueba. |
| ✅ ~~**Dividir `ReportesController`**~~ | **Hecho (2026-08-28): 3.405 → 101 líneas.** Repartido en 8 traits bajo `app/controllers/reportes/`. API y cuerpos **byte-idénticos** (verificado por reflexión y por hash). Ver `CHANGELOG.md`. |
| ✅ ~~**Migrar estilos inline a clases**~~ | **Hecho lo migrable (2026-10-11): 2.724 → 1.581 `style=""`.** 3.348 declaraciones repetidas pasaron a 175 utilidades `u-*` (`!important`, equivalentes a un inline); verificado etiqueta por etiqueta sin diferencias. Lo que queda inline es deliberado: `display` (JS), standalone/impresión, PHP dentro, elementos que toca el JS, casos sueltos. Ver `CLAUDE.md` → Design System. |
| ✅ ~~**Programar la tarea de respaldo en el servidor**~~ | **Hecho (2026-08-28)** con `cron/instalar_tareas.ps1`, que crea las **dos** tareas y es idempotente. Reejecutar en el servidor de producción. |
| 🟢 **Rango de fechas fino en Indicadores** | Ya hay selector de **año**; rango libre mes-a-mes solo si el cliente lo pide (refactor amplio, bajo valor). |
| ✅ ~~**Ampliar la suite de pruebas**~~ | **179 pruebas al 2026-10-11** (81 en agosto). Nuevas: liquidación contra Excel, codificación de bienes, montos y fechas en letras (destapó «veintiun mil» sin tilde en documentos jurídicos, corregido), validadores de correo/teléfono/RIF, código y descripción oficial de bienes, elegible a fijo, lista blanca de `Taller` y `sigtur_uid`. `Asistencia::calcularMinutosTarde` sigue sin prueba: lee el horario de la BD. |

---

## 6. VERIFICACIÓN MANUAL PENDIENTE (probar en navegador)

> 🟡 **2026-10-11 — Codificación propia (mig. 087): falta verla en el navegador.** Probada con 10 casos
> automáticos y 13 comprobaciones de punta a punta (sobre un respaldo, restaurado), y renderizando
> listado, hoja de vida y `/inventario/codificacion` con el motor encendido y apagado. Falta el vistazo
> en navegador del modal *Codificar* encendido (selector de clasificación, número propuesto).
>
> 🟡 **2026-10-11 — Liquidación (mig. 086): falta verla en el navegador.** Probada con 47 casos
> automáticos contra los valores de Excel, de punta a punta contra la base (sobre un respaldo,
> restaurado después) y renderizando las 8 pantallas sin errores de PHP. Lo que falta es **un vistazo
> en navegador**, porque la extensión de Chrome no estaba disponible: `/nomina/liquidaciones`, el
> detalle (modal *Corregir mes*, *Ajustes*, *Anular*), el imprimible (sin la URL de Chrome en la
> hoja), la tarjeta de tasas del BCV y la sección de liquidación del modal de parámetros en
> `/nomina/parametros`, y el botón *Liquidar prestaciones* en el expediente de un egresado.

> ✅ **2026-10-04 — prueba de formularios guardando datos (navegador + HTTP), sobre un respaldo de la
> base que luego se restauró.** Todo guarda: **B1 cerrado** (alta de 5 pasos con familiar → expediente),
> traslado (cambia el departamento y queda en historial), falta → amonestación, vacaciones (09/10–13/10
> = **2 días hábiles**: descuenta fin de semana y el feriado del 12/10), modal de Ubicaciones (sede +
> depósito, la edición recarga los valores), Nómina (cargar mes, generar quincena, avisos, recalcular,
> datos de nómina, sueldo, total confirmado del bono vacacional) y evidencia de taller (rechaza un
> `.png` con PHP dentro; la imagen real se descarga idéntica solo con sesión).
>
> **Corregido:** (1) **una misma falta se podía escalar varias veces** — tres clics sobre una sola
> inasistencia = causa de despido; además el controlador tomaba el empleado del formulario y no de la
> falta. Ahora una falta se escala una vez (vuelve a poder si se anula su amonestación) y la bandera
> pasa a «Amonestada». (2) **El asistente de alta anunciaba un folio que no se asignaba** (`EXP-0005`
> anunciado, `EXP-0020` real): usaba `MAX(id)+1` en vez de la secuencia.
>
> ✅ **Resuelto el mismo día:** el ingreso a la administración pública se habilitó para **todo** el
> personal (precargado con la fecha de ingreso, editable; ver `REGLAS_NEGOCIO_RRHH.md`). Y los cuatro
> menores: «Registrar sueldo» pide solo lo que Nómina no calcula (sueldo básico y prima de
> discapacidad); el botón de guardar del bono vacacional **existía pero estaba desconectado** (un
> `<form>` envolviendo celdas de tabla); el asistente pide el género del familiar; el escalado pide
> confirmación y avisa con cuántas amonestaciones queda el empleado.

> ✅ **2026-10-03 — recorrido de lectura hecho:** todas las pantallas de abajo **abren sin error**
> (incluidas las 9 fases de Rutas) y el menú de los 6 roles coincide con *Roles y Permisos*. Lo que
> sigue pendiente de esta lista es lo que exige **guardar** algo: subir una evidencia, registrar
> vacaciones/traslado/escalado, el modal de Ubicaciones, el alta de empleado de punta a punta (B1).

**Pendiente 2026-08-27 — lo construido en este ciclo, probado por BD y por pruebas
automatizadas pero NO abierto en el navegador. Es el riesgo más alto que queda:**

- Las 4 pantallas de Nómina: `/nomina/parametros`, `/nomina/quincenal`, el detalle de una
  quincena y el detalle de un período de bono vacacional (calculado vs confirmado).
- La tarjeta **«Datos de nómina»** del expediente del empleado y su modal.
- **Ubicaciones** con la columna Sede, el badge Depósito y el modal con sede + casilla de depósito.
- El **menú lateral entrando con cada uno de los 6 roles**, comparándolo con lo que dice
  *Roles y Permisos*. Es la prueba que destapó H-12 y la que lo cierra.
- Las alertas `.sig-alert` de las 7 vistas del módulo de Bienes, que hasta ahora se veían como
  texto plano y ya tienen estilo.
- Subir una **evidencia de taller** y abrirla: ahora se sirve por `/descarga/taller/{id}`.

- **B1** "botón Guardar de RRHH": no hay defecto estático; hacer un alta de empleado de punta a punta para cerrarlo.
- Export **Excel/PDF** en cualquier listado CRUD.
- Toggle **Durable/Fungible** en el modal de inventario.
- Registrar un **período de vacaciones** y verificar el conteo de días hábiles (excluye finde+feriados).
- Registrar un **traslado** y un **escalado falta→amonestación**.
- **Pendiente 2026-07-12** (probado por API/BD, falta un vistazo visual en navegador): listados/reportes de Empleados, Rutas, Visitantes, Pasantes, Inventario con las columnas/filtros nuevos; campana de notificaciones (abrir dropdown y confirmar que las alertas ya vistas no reaparecen); flujo completo de "¿Olvidaste tu contraseña?" desde el link del login.

---

## 7. REGLAS DE NEGOCIO — ESTADO POR MÓDULO (resumen)

> Detalle funcional en los `REGLAS_NEGOCIO_*.md` / `MODELO_NEGOCIO_RRHH.md`.

- **RRHH:** ✅ organigrama jerárquico, ficha técnica + wizard, expediente/recaudos, horarios/grupos A-B/OAC, asistencia/puntualidad, permisos/reposos, amonestaciones+faltas (con tipo y escalado), constancias multi-tipo, egreso/reingreso, traslados, **vacaciones (días)**, badge elegible a fijo, **Bono Vacacional v1** (datos salariales + `/nomina`). **nómina quincenal calculada (mig. 072)**: motor puro con 45 pruebas, porcentajes en tablas, parámetros mensuales con vigencia, 5 tipos de personal, advertencias por empleado, recálculo y cierre, export de 6 hojas. ✅ Bono Vacacional en el motor (N‑D) y ✅ **Liquidación de Prestaciones Sociales** (N‑E, mig. 086): garantía mes a mes con intereses del BCV, art. 142 d, fracciones, mora, imprimible y Excel de 3 hojas; todas las reglas como parámetros — ver §3.1 y `docs/PLAN_MODULO_NOMINA.md` §7.
- **Formación:** ✅ talleres/charlas/inducciones, participantes (adulto/niño, alta sin botón buscar), informe demográfico auto, evidencias, estados con auto-transición, lista de asistencia, reportes. 🔒 Falta: oficios base (D-FO06).
- **Turismo (Rutas):** ✅ puntos + mapa Leaflet offline, participantes (con modo libre y representante), oficios con correlativo, demografía e informe. ✅ **Catálogo y salidas ya están separados** (T-A, mig. 078): `ruta_ejecuciones` + `ruta_ejecucion_empleados`, dos pantallas, los tres estados de R-14 y la reprogramación enlazada. ✅ **Restricciones por recorrido y cupo diario** (T-B + T-I, mig. 079) — **H-17 cerrado**: la edad ya no está cableada, y un niño de 4 se inscribe donde el recorrido lo admite. ✅ **Ficha Institucional** (T-G, mig. 080): nace sola al cerrar la salida, se captura por institución y se imprime en el formato oficial. ✅ **Estados y reprogramación** (T-D): la salida se cierra desde su pantalla, con motivo obligatorio, y se reprograma creando una salida nueva enlazada. ✅ **Solicitud y aprobación** (T-E): el oficio que envía la institución se archiva y se descarga con control de rol; la aprobación de la Presidencia queda con fecha y autor. ✅ **Permisos de acceso** (T-H, mig. 081): oficio por semana a cada institución custodia, con su estado y el pase recibido; el sistema dice **a quién falta pedirle** a partir del custodio de cada parada. ✅ **Itinerario por salida y personal** (T-F, mig. 082). ✅ **Cobro completo** (T-C, mig. 083) — **H-14 cerrado**: tarifa en USD por recorrido, tasa congelada por salida (sugerida desde el BCV), registro de pagos con abonos y comprobante, exoneración con motivo, y acta de pago numerada para el efectivo. 🎉 **Las nueve fases del módulo están construidas.** ⏳ Del cliente: el **formato del oficio de permiso** (T-H, el imprimible es provisional) y su **visto bueno al acta de pago** que propusimos (T-C, R-39). Fases en `docs/PLAN_MODULO_RUTAS.md` §7.
- **Inventario:** ✅ expediente administrativo por bien (fases 1-4, mig. 062-067): **codificación propia construida y apagada (mig. 087, R-12)** · estatus vs condición, codificación contra el BM-1, adquisición/garantía, responsable **derivado** del departamento, movimientos con origen/destino y autorización, mantenimiento correctivo y preventivo, documentos y hoja de vida, etiquetas QR, conteo por cambio de gestión, análisis de suficiencia y RBAC del módulo. 🔒 Falta: **2 documentos imprimibles** bloqueados por sus formatos (**Acta de Desincorporación** y acta de asignación; la relación de bienes nuevos y el oficio de donación ✅ **construidos**, mig. 075) y **cargar los bienes reales** — sigue faltando el inventario vigente de la encargada (lo del 2026-10-06 fue una planilla antigua de muestra, ver `docs/PLAN_MODULO_BIENES.md` §2-quinquies). ⚠️ **Y adaptar el módulo al procedimiento nuevo del 2026-09-02** (IMATUR codifica: C-1…C-7 en §2-ter; bloqueado por B-73 y B-75). *(D-IN06, D-IN09 y D-IN10 quedaron cerradas por el levantamiento del 2026-08-04.)*
- **Recepción (Visitantes):** ✅ visitantes + visitas (bitácora inmutable), reportes. 🛠️ Backlog: visitas activas del día.
- **Sistema:** ✅ RBAC dinámico, usuarios/roles, auditoría humanizada + papelera, configuración institucional, idempotencia (token), export transversal, login endurecido (mig.051) + acepta usuario o correo, respaldos automáticos, búsqueda global, campana de alertas (ahora con "vistas" por usuario, mig.057), **carnetización** (mig.053), **recuperación de contraseña por correo** (mig.058, 🔒 falta SMTP real), egreso desactiva acceso automáticamente.

---

## 8. OBSOLETO / SIN EFECTO
- Módulos **Instituciones externas** y **Actividades de ruta**: retirados (2026-05-31).
- `taller_inventario`, `participantes_taller.es_brigadista`: eliminados (mig.050).
- `nivel_dificultad`, `ruta_inventario`: eliminados (mig.019/021).
