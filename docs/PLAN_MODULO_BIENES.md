# Plan de reconstrucción — Módulo de Bienes (Inventario)

**Fecha:** 2026-08-04 · **Base:** respuestas del cliente en `PREGUNTAS_DESCUBRIMIENTO_Bienes_Rutas.md` (Parte 1, B-01…B-59) + **Formulario BM-1 real** entregado el mismo día (§2-bis) · **Estado BD hoy:** migraciones hasta 061

> **Estado: ✅ Fases 1, 2, 4 y 5 completas · Fase 3 parcial** (migraciones 062-067, 2026-08-05).
> **8 de las 9 preguntas abiertas quedaron respondidas e implementadas.** Pendiente: 3 documentos
> bloqueados por los formatos del cliente y la pregunta B-71 — §12.
>
> ### ⚠️ 2026-09-02 — CAMBIO DE PROCEDIMIENTO: **leer §2-ter antes que nada**
> IMATUR pasa a **asignar el código** de sus propios bienes (la Alcaldía ya no viene a codificar,
> solo recibe la relación) y el acta de baja pasa a ser **Acta de Desincorporación** por lote, sin
> oficio de retiro. La base construida sigue sirviendo, pero hay 7 consecuencias en el código
> (C-1…C-7), **B-60 se reabre** y hay 8 preguntas nuevas (B-73…B-80). **Nada de esto está
> implementado todavía.**

---

## 1. Qué revelaron las respuestas

El módulo actual fue construido como un **CRUD de bienes genérico**: registrar un bien, moverlo, darlo de baja. Las respuestas describen algo distinto: un **expediente administrativo por bien**, con un ciclo de vida gobernado por la Alcaldía y respaldado por documentos firmados.

Las cinco diferencias de fondo:

| # | Lo que asumió el sistema | Lo que realmente ocurre |
|---|---|---|
| 1 | El bien nace con su código | El bien **nace sin código**. Se registra internamente, se envía un **informe a la Alcaldía**, ellos inspeccionan, asignan el código y devuelven el **BM-1 consolidado**, del que IMATUR carga los números (B-03, B-12, B-14 · §2-bis) |
| 2 | El código es un texto libre | Es **estructurado**: `grupo-subgrupo-sección-cantidad-N° de orden`, y lo asigna el Departamento de Bienes de la Alcaldía (B-11) |
| 3 | La baja es un movimiento más | Es un **acto administrativo** firmado por la Coordinadora de Bienes y la Presidencia, más un oficio para que la Alcaldía venga a retirar el bien (B-39) |
| 4 | No hay documentos asociados | Cada bien acumula **factura, informe de la Alcaldía, oficio de donación, acta de asignación, acta de baja** (B-16 a B-19) |
| 5 | El inventario se administra solo | Todo movimiento lo **autoriza la Coordinadora de Bienes** (B-32) |

**El dolor principal declarado (B-05):** generar el oficio al recibir un bien nuevo, y la auditoría completa en cada **cambio de gestión**. El módulo debe atacar esos dos puntos, no solo listar bienes.

**Escala (B-04):** ~142 bienes. Volumen pequeño — no hace falta paginación de servidor ni lector de códigos de barras. El valor está en el control documental, no en el rendimiento.

---

## 2. Lo que se conserva

No hay que rehacer todo. Se mantienen:

- Las tablas `inventario`, `categorias`, `ubicaciones`, `actividad_inventario` como base.
- Soft delete, auditoría (`audit_logs`), papelera.
- El patrón de adjuntos ya probado en RRHH (`expediente_documentos` + `DescargaController`) — se replica tal cual.
- Los reportes existentes (inventario, kardex, bienes asignados, bajas) como punto de partida.
- El `qrcode.min.js` ya vendorizado (quedó sin usar tras el carnet) — **se reutiliza para las etiquetas**.

---

## 2-bis. El formato oficial: Formulario BM-1 (recibido 2026-08-04)

Imagen en `docs/formatos/BM-1_inventario_bienes_muebles_alcaldia.jpeg`.

> ### ⚠️ El BM-1 es un documento ENTRANTE, no algo que IMATUR produzca
>
> Es el **registro consolidado que la Alcaldía elabora y le devuelve a IMATUR**, ya con los
> códigos asignados. El sistema **no debe generarlo** para enviarlo.
>
> El circuito real tiene tres piezas y solo las dos primeras son responsabilidad del sistema:
>
> | # | Pieza | ¿La hace el sistema? |
> |---|---|---|
> | 1 | **Registro interno** de IMATUR — donde se dan de alta los bienes nuevos con todos sus datos | ✅ **Sí. Es el corazón del módulo.** |
> | 2 | **Informe / oficio** a la Alcaldía con los bienes nuevos, para que vengan a verificar | ✅ **Sí — es el dolor #1 declarado (B-05)** |
> | 3 | **BM-1 consolidado** que la Alcaldía devuelve con grupo/subgrupo/sección y N° de orden | ❌ No. **Se recibe** y de él se cargan los códigos |
>
> De ahí sale una funcionalidad que no estaba en el plan original: **conciliar el BM-1 recibido
> contra el registro interno** — cargar los códigos asignados, detectar bienes que IMATUR tiene
> y la Alcaldía no reconoce (o al revés), y archivar el documento como respaldo. Es exactamente
> lo que hace falta en la auditoría por cambio de gestión (§4.5).
>
> Reproducir la vista BM-1 como **reporte interno** sí es útil —para comparar contra lo que
> mandó la Alcaldía—, pero como herramienta de control, no como entregable oficial.

El formato, con datos verdaderos de IMATUR:

**Encabezado** — "INVENTARIO DE BIENES MUEBLES", sello de la *Coordinación de Bienes y Materiales* de la Alcaldía:

| Campo | Valor (fijo para IMATUR) |
|---|---|
| Entidad propietaria | ALCALDÍA BOLIVARIANA DEL MUNICIPIO SUCRE |
| Unidad de trabajo o dependencia | INSTITUTO MUNICIPAL AUTÓNOMO DE TURISMO (IMATUR-SUCRE) |
| Servicio | ALCALDÍA |
| Estado / Municipio | SUCRE / SUCRE |
| Dirección o lugar | CALLE SUCRE, CASA Nº11, AL LADO DE LA FUNERARIA UNIÓN |
| Fecha | la de emisión |

**Columnas de la tabla:**

```
┌──────── CLASIFICACIÓN (CÓDIGO) ────────┬──────────┬──────────┐
│  GRUPO  │  SUB-GRUPO  │    SECCIÓN     │ CANTIDAD │ Nº ORDEN │  NOMBRE Y DESCRIPCIÓN  │ VALOR UNIT. BS │ VALOR TOTAL BS │
└─────────┴─────────────┴────────────────┴──────────┴──────────┘
```

**Fila real:** `2 │ 01 │ 108 │ 1 │ 084 │ SILLA VISITANTE EN SEMICUERO COLOR NEGRO SIN POSABRAZO │ S/P │ S/P`

**Las filas se agrupan bajo una banda con el nombre del departamento.** En la muestra: *Dirección de Planificación y Gestión Turística*, *Promoción Turística*, *Calidad y Servicio Turístico* — los tres **existen tal cual en la tabla `departamentos`** (ids 7, 16 y 17).

> Que la Alcaldía agrupe por los mismos departamentos que ya tenemos modelados es una buena
> señal: la conciliación del BM-1 contra el registro interno puede hacerse **departamento por
> departamento**, sin traducir nombres.

### Lo que este documento resuelve

| Pregunta | Resuelta |
|---|---|
| **B-61** — ejemplos reales de código | ✅ `2-01-108`, N° de orden `084`, `131`, `141`, `153`, `155`…`171`. Grupo = 1 dígito, sub-grupo = 2, sección = 3, N° de orden = **3 dígitos con ceros a la izquierda** |
| **B-62** — qué significa "cantidad" en el código | ✅ Es la cantidad de la fila y **siempre vale 1**, porque el registro es individual (coherente con B-09). No forma parte del identificador |
| **B-60** — catálogo de grupos/subgrupos/secciones | 🟡 **Parcial, pero ya no bloquea.** Conocemos la estructura y quién asigna los valores (la Alcaldía). Como IMATUR solo *transcribe* lo que le asignan, basta con campos validados por formato; el catálogo serviría para un desplegable, no es requisito |

### Tres hallazgos que cambian el plan

**1. El código oficial NO sirve para clasificar internamente.** Todos los bienes de la muestra —sillas, mesas, pizarra, archivo, biblioteca, **aire acondicionado** y **router**— comparten la misma clasificación `2-01-108`. Es decir, el catálogo de la Alcaldía **no distingue** equipo tecnológico de mobiliario, que es justo lo que el cliente pidió en B-22.

> **Conclusión de diseño:** hacen falta **dos ejes independientes** — el *código oficial* (para el formulario BM-1 y la Alcaldía) y una *categoría interna* (para los reportes de la Presidencia, §6 y §8). No son lo mismo y no deben mezclarse en una sola columna.

**2. Marca, modelo y serial van dentro de la descripción.** El formulario no tiene columnas propias: el aire acondicionado aparece como *"AIRE ACONDICIONADO MARCA HYUNDAI DE 36 MIL BTU COLOR BEIGE MODELO: PISO TECHO SERIAL: 540K51799013708016068"*. Conviene **seguir capturando `marca`/`modelo`/`serial` por separado** y que el sistema **componga** ese texto al exportar — se gana poder filtrar y buscar sin perder fidelidad al formato. Matiza B-13: el serial no es el identificador, pero sí se registra.

**3. Los valores van en "S/P" (sin precio).** Las columnas *Valor unitario* y *Valor total* aparecen en `S/P` en **todas** las filas, aunque B-17 dijo que sí registran costo y factura. El costo se lleva internamente pero no se declara en este formulario → nueva pregunta **B-69**.

---

## 2-ter. CAMBIO DE PROCEDIMIENTO (notificado el 2026-09-02): IMATUR codifica sus propios bienes

> **Origen:** el cliente informó que la Alcaldía le notificó un procedimiento nuevo. **No es una
> corrección de lo levantado en agosto: es un cambio real del circuito**, y hay que tratarlo como tal.

### Lo que cambia

| | Antes (lo implementado, mig. 062-067) | Ahora |
|---|---|---|
| **Quién asigna el código** | La Alcaldía, en una inspección física | **IMATUR**, como ente autónomo |
| **Punto de partida** | — | El **último código que la Alcaldía deje** en su última revisión y asignación — **esa revisión todavía NO se ha hecho** |
| **Rol de la Alcaldía** | Inspecciona, codifica, devuelve el BM-1 | **Recibe la relación** de bienes nuevos para mantener su registro patrimonial. Ya no viene a codificar |
| **El informe de bienes nuevos** | Solicitud de inspección: lote de bienes **sin** código | **Relación informativa**: lote **ya codificado**, con **monto** y los demás datos del formato |
| **Acta de baja** | Acta + **oficio de retiro** a la Alcaldía (2 documentos) | **Acta de Desincorporación** (1 documento). Lista todos los bienes a retirar; la Alcaldía **firma y sella** y eso es el **aval** del retiro. **El oficio de retiro sale del alcance** |
| **Acta de asignación** ("acta de encargado") | Pendiente de formato | **Sigue vigente**, hay que hacerla |
| **Oficio de donación** | Pendiente de formato | **Sigue vigente** |

**Lo que NO cambia** — y por eso el módulo construido sigue sirviendo: el formato del código, el
registro individual, los dos ejes (código oficial + categoría interna), el expediente documental por
bien, movimientos, mantenimiento, responsable derivado, conteo por cambio de gestión y la propiedad
patrimonial de la Alcaldía. **La base es la misma; cambia quién ejecuta la codificación.**

### Consecuencias en el código (por construir)

| # | Qué | Detalle |
|---|---|---|
| **C-1** | **Secuencia propia de N° de orden** | `Inventario::siguienteNroOrden()` = `MAX(nro_orden)` sobre todos los bienes vs. un **punto de partida configurable** (clave nueva en `configuracion_sistema`), y el modal de codificación lo propone. Hoy el campo es de tecleo libre y el único control es `findByNroOrden()` (anti-repetido), que **se conserva** |
| **C-2** | **La codificación deja de esperar** | Un bien nuevo puede codificarse en el acto. `EST_SIN_CODIFICAR` **no se elimina**: sigue describiendo el lote histórico que aguarda la última revisión de la Alcaldía, y la alerta `dias_alerta_sin_codificar` sigue teniendo sentido para él |
| **C-3** | **`verificado_alcaldia` cambia de significado** | Hoy `codificar()` lo pone en `TRUE` porque el código venía del BM-1. Con código propio, «verificado por la Alcaldía» y «codificado» dejan de ser lo mismo: hay que separar *codificado por IMATUR* de *reconocido por la Alcaldía* (o dejar de escribir la bandera al codificar internamente) |
| **C-4** | **Grupo/subgrupo/sección necesitan catálogo** | ⚠️ **Reabre B-60.** Se cerró con «IMATUR solo transcribe, basta validar el formato». Si ahora IMATUR **clasifica**, hace falta la lista de valores válidos: sin ella, ante un bien que no se parezca a nada ya registrado no hay con qué decidir. Mitigación: **sembrar el catálogo desde el listado digital** de la encargada (los códigos ya en uso) y permitir agregar |
| **C-5** | **Acta de Desincorporación por lote** | Tabla nueva (cabecera + renglones, mismo patrón que `inventario_consolidados_bm1`): se arma con los bienes a retirar, se imprime, y al **registrar el acta firmada y sellada** (con su escaneado adjunto) se marcan **todos** sus bienes como retirados. Hoy `marcarRetirado()` es **bien por bien** y no existe la entidad acta. La distinción *Por retirar* / *Retirado* (B-67) se conserva y encaja perfecto |
| **C-6** | **Renombrar en la UI** | El documento debe decir **«Acta de Desincorporación»** (exigencia explícita del cliente). La pestaña del listado ya dice *Desincorporados*; queda por decidir si el estatus `Dado de baja` se renombra a `Desincorporado` en toda la UI (implica migración por el CHECK) |
| **C-7** | **Etiquetas y relación con monto** | Las etiquetas QR ya no dependen de la Alcaldía (corregir el texto «Solo se listan bienes ya codificados por la Alcaldía»). La relación saliente **sí declara el monto** → matiza **B-69**, que había cerrado el costo como control interno |

> **La conciliación del BM-1 se conserva.** La última revisión sigue pendiente y hay que poder cargar
> los códigos que traiga; además el histórico ya recibido debe quedar trazable. Deja de ser el camino
> normal de codificación, no deja de existir.

### Insumos nuevos que el cliente ofreció (2026-09-02)

- **Los formatos nuevos**, cuando los tenga (los cuatro documentos siguen bloqueados hasta entonces).
- ✅ **B-71 respondida: SÍ existe versión digital** de los documentos — y además de un **inventario
  interno que la encargada lleva aparte**. **Hay que pedir ese archivo:** habilita la carga masiva de
  los ~142 bienes, el catálogo de códigos ya usados (C-4) y el punto de partida de la secuencia (C-1).
- ✅ **B-72 reinterpretada:** los **saltos en el N° de orden no son bajas**. El listado de la Alcaldía
  está **ordenado por departamento, no por código**, así que los códigos *parecen* desordenados. Al
  recibir el digital se ordena por código y se confirma. **Implicación técnica:** el punto de partida
  es el `MAX` del archivo completo, nunca el último de una hoja o de un departamento.

### Preguntas nuevas para el cliente

| # | Pregunta | Por qué importa |
|---|---|---|
| **B-73** | Mientras la Alcaldía no haga esa última revisión, **¿desde qué número arrancamos?** ¿Esperamos la revisión, o partimos del mayor N° de orden del listado interno? | Sin punto de partida no se puede codificar (C-1). Es lo primero que hace falta |
| **B-74** | La secuencia del N° de orden, **¿es una sola para todo IMATUR** o una por grupo-subgrupo-sección? ¿Sigue siendo de 3 dígitos — qué se hace al pasar de 999? | Define el algoritmo del siguiente número |
| **B-75** | Si IMATUR ahora clasifica: **¿cuál es la lista de grupos/subgrupos/secciones** que pueden usar? (o al menos los que usan hoy) | Reabre B-60 (C-4) |
| **B-76** | **¿El código de un bien desincorporado se reutiliza**, o la secuencia nunca recicla? | Evita colisiones y decide si `findByNroOrden` debe mirar también los desincorporados |
| **B-77** | La relación de bienes nuevos: **¿el monto va en Bs a la fecha de compra?** ¿Cada cuánto se envía — por lote, mensual, al recibir cada bien? | Contenido y disparador del documento |
| **B-78** | **¿La Alcaldía devuelve algo** (acuse, sello, un BM-1 nuevo) al recibir la relación? | Decide si hay que seguir modelando un documento entrante |
| **B-79** | **Acta de Desincorporación:** ¿quién firma por IMATUR? ¿Lleva correlativo? ¿Se adjunta el escaneado firmado y **eso** marca los bienes como retirados? | Define la tabla y el flujo del C-5 |
| **B-80** | **¿Tienen por escrito la notificación** de la Alcaldía con el procedimiento nuevo? | Cambia quién responde por la codificación; conviene tenerlo en el expediente y no depender de una comunicación verbal |

---

## 3. Cambios al modelo de datos

### 3.1 `inventario` — columnas nuevas

| Columna | Tipo | Por qué (pregunta) |
|---|---|---|
| `estatus` | varchar CHECK | Estado administrativo, **distinto de `condicion`** (ver 3.2). B-34, B-38 |
| `nro_orden` | varchar | N° de orden que asigna la Alcaldía. NULL hasta la inspección. B-11, B-12 |
| `codigo_grupo` / `codigo_subgrupo` / `codigo_seccion` | varchar | Partes del código oficial. B-11 |
| `verificado_alcaldia` | bool | Si la Alcaldía ya hizo la inspección. B-12 |
| `fecha_verificacion` | date | Cuándo. B-12 |
| `origen` | varchar CHECK (`Compra`/`Donación`) | B-18 |
| `donante` | varchar | Persona/ente que dona. B-18 |
| `costo_adquisicion` | numeric(14,2) | B-16, B-17 |
| `fecha_adquisicion` | date | B-17 |
| `proveedor` | varchar | B-17 |
| `tiene_garantia` | bool | B-20 |
| `garantia_vence` | date | B-20 — alimenta alerta |
| `id_responsable` | int FK empleados | Un solo responsable. B-26, B-27 |
| `foto_url` | varchar | B-21 — mismo patrón que `personas.foto_url` |

### 3.2 `estatus` vs `condicion` — separación necesaria

Hoy se mezclan en una sola columna, y por eso existe el bug H-04. Son dos ejes independientes:

- **`condicion`** = estado *físico*: `Nuevo`, `Bueno`, `Regular`, `Deteriorado`.
- **`estatus`** = estado *administrativo*:

| Estatus | Significado | ¿Aparece en inventario activo? |
|---|---|---|
| `En espera de codificación` | Registrado, sin N° de orden de la Alcaldía | Sí, con distintivo |
| `Activo` | Operativo y disponible | Sí |
| `En mantenimiento` | Fuera de servicio temporalmente (B-34) | **Sí**, pero marcado como no disponible |
| `Extraviado` | Pérdida en averiguación (B-41) | Sí, marcado |
| `Robado` | Con denuncia (B-41) | Sí, marcado |
| `Dado de baja` | Desincorporado (B-38) | **No** — sale del inventario activo |

> **Esto cierra H-04 con criterio del cliente:** el bien en mantenimiento **no desaparece** (B-34: *"no desaparece del inventario, solo es transición de estatus"*), pero el dado de baja **sí sale del activo** conservando su registro y el oficio como aval (B-38).

### 3.3 Columnas que sobran

| Columna | Por qué | Acción |
|---|---|---|
| `tipo_bien` (Durable/Fungible) | B-07: **no llevan consumibles**. Todo lo inventariado es durable | Eliminar o fijar en Durable |
| `cantidad` | B-09: **registro individual** aunque se compre en lote; cada uno con su código | Eliminar (siempre 1) |
| `serial` | B-13: *"no, solo con el código se lleva el control"* | Opcional, dejar de mostrar |

> Ojo: `tipo_bien`/`cantidad` se agregaron en la migración 044 respondiendo a D-IN05. Las respuestas de ahora **contradicen** esa decisión. Conviene confirmarlo antes de eliminarlas (ver §8).

### 3.4 `ubicaciones` — sedes y depósito

| Columna | Por qué |
|---|---|
| `sede` | B-24: hay **dos sedes** — Sede Principal y la **Oficina de Información Turística del Aeropuerto de Cumaná**, cuyos bienes también se controlan |
| `es_deposito` | B-23, B-25: el depósito es el área común de los bienes sin asignar. Todo bien no asignado **debe** estar en depósito |

### 3.5 `actividad_inventario` — el modelo actual no sirve

Hoy solo guarda `tipo_movimiento` + `id_empleado_responsable`. **No registra de dónde a dónde**, que es justamente lo que describe B-31.

| Columna nueva | Por qué |
|---|---|
| `id_ubicacion_origen` / `id_ubicacion_destino` | B-31: depósito→departamento, departamento→depósito, departamento→departamento |
| `autorizado_por` | B-32: todo movimiento lo autoriza la Coordinadora de Bienes |
| `fecha_retorno` | Para salidas de mantenimiento (B-33) |

Y el enum de `tipo_movimiento` debe reconciliarse: hoy es `Asignacion/Devolucion/Traslado/Baja/Mantenimiento`; el cliente habla de **traslados entre ubicaciones** + mantenimiento + baja + asignación de responsable.

### 3.6 Tablas nuevas

| Tabla | Para qué |
|---|---|
| `inventario_documentos` | Factura, informe de la Alcaldía, oficio de donación, acta de asignación, acta de baja. Mismo patrón que `expediente_documentos` (B-16, B-18, B-19) |
| `inventario_mantenimientos` | Proceso de reparación: quién lo hizo (Servicios Generales), fechas, resultado, costo (B-33) |
| `inventario_bajas` | Snapshot de la desincorporación: motivo, acta, firmantes, oficio a la Alcaldía, fecha de retiro (B-37 a B-42) |
| `inventario_mantenimiento_plan` | Mantenimiento **preventivo** programado: aires, impresoras, computadoras (B-56) |
| `inventario_consolidados_bm1` | Cada **BM-1 recibido** de la Alcaldía: fecha, archivo adjunto y resultado de la conciliación. Da trazabilidad de cuándo se codificó cada lote (§2-bis) |

---

## 4. Flujos a construir

### 4.1 Codificación (el dolor #1 — B-05)

Tres actores: el **registro interno** (el sistema), el **informe saliente** y el **BM-1 entrante**.

```
① REGISTRO INTERNO  (sistema)
   Alta del bien: descripción, marca/modelo/serial, departamento,
   costo, factura, origen (compra/donación)
        →  estatus "En espera de codificación"   ·   sin código, sin N° de orden
                    ↓
② INFORME / OFICIO A LA ALCALDÍA  (lo genera el sistema — hoy es manual)
   Lote de bienes nuevos pendientes de verificación
                    ↓
        Inspección física de la Alcaldía
                    ↓
③ BM-1 CONSOLIDADO  (lo devuelve la Alcaldía)
   Trae grupo-subgrupo-sección + N° de orden por bien
                    ↓
   CONCILIACIÓN en el sistema:
     · cargar el código y el N° de orden en cada bien
     · marcar verificado_alcaldia + fecha
     · archivar el BM-1 como documento de respaldo
     · señalar diferencias (bienes sin reconocer en uno u otro lado)
        →  estatus "Activo"
                    ↓
④ ETIQUETA con código + QR  (B-14, B-15)
   La Alcaldía pega la suya en la inspección; el sistema puede
   generar la propia una vez asignado el código
```

El informe del paso ② agrupa **varios bienes** (lo habitual al recibir un lote). El paso ③ es la pieza que no estaba contemplada y que además resuelve la auditoría de cambio de gestión (§4.5).

### 4.2 Asignación de responsable
Bien en depósito → se asigna a un departamento → responsable = director o, en su defecto, coordinador (B-26). Se genera un **oficio que firma el empleado** (B-29). Al salir un trabajador, el bien **no lo sigue**: queda en el departamento y se reasigna al nuevo responsable (B-28, B-30).

### 4.3 Mantenimiento
Solicitud → autorización de la Coordinadora de Bienes → estatus `En mantenimiento` (no disponible, pero visible) → Servicios Generales ejecuta y registra → retorno → estatus `Activo` (B-33, B-34).

### 4.4 Baja / desincorporación
Motivo (robo, deterioro, pérdida) → **acta administrativa** firmada por Coordinadora de Bienes + Presidencia → **oficio a la Alcaldía** para retiro → estatus `Dado de baja` → sale del inventario activo, queda en el listado de desincorporados con su aval (B-37 a B-42). En robo/pérdida, además: denuncia y averiguación administrativa (B-41).

### 4.5 Conteo por cambio de gestión (el dolor #2 — B-05, B-48)
No es periódico: se dispara al **cambiar de coordinador o de presidencia**. Lo que se verifica es **estatus, lugar y cantidad** (B-50). Debe producir un acta comparando el registro contra lo hallado físicamente.

---

## 5. Documentos a generar

Todos siguen el patrón ya probado en constancias/oficios (HTML imprimible + membrete institucional + correlativo vía `ConfigSistema::generarNumeroOficio`).

| Documento | Origen |
|---|---|
| **Informe / oficio de bienes nuevos** para verificación de la Alcaldía ← *el más urgente* | B-03, B-05, B-12 |
| **Acta/oficio de asignación** de bien a responsable | B-29 |
| **Acta administrativa de baja** (firma Coordinadora + Presidencia) | B-39 |
| **Oficio a la Alcaldía** para retiro del bien desincorporado | B-39, B-40 |
| **Etiqueta con código + QR** | B-14, B-15 |
| **Acta de conteo** por cambio de gestión | B-48, B-49 |
| **Vista tipo BM-1** como reporte **interno** de control (no es entregable a la Alcaldía) | §2-bis |

---

## 6. Reportes (B-51, B-53)

Todos internos para la Presidencia, **sin formato obligatorio** (B-52), filtrables por departamento (B-53):

- Inventario general · Bienes activos · Bienes por departamento
- Bienes dañados · Bienes dados de baja (desincorporados)
- **Bienes nuevos sin código** (en espera de codificación)
- Bienes por donación · Bienes en depósito/almacén
- Estado general del inventario (resumen por estatus)

## 7. Alertas y permisos

**Alertas** (al Centro de Alertas existente): garantías por vencer (B-20), mantenimiento preventivo próximo (B-56), bienes esperando codificación hace mucho (B-12).

**RBAC** (B-58) — el rol 4 "Inventario" actual no refleja lo pedido:

| Quién | Puede |
|---|---|
| Coordinación de Compras, Bienes y Servicios | Crear, editar, mover, dar de baja |
| Presidencia | Ver todo + reportes |
| Administración | **Solo ver** |
| Administrador del sistema | Todo |

---

## 8. Categorías propuestas (B-22)

El cliente pidió expresamente una propuesta: hoy no hay clasificación real (todo cae en "Inmobiliario").

**El BM-1 recibido zanjó esta duda** (§2-bis): en la muestra real, sillas, mesas, pizarra, archivo, aire acondicionado y router comparten **todos** la clasificación `2-01-108`. El catálogo de la Alcaldía **no distingue** equipo tecnológico de mobiliario, que es justamente lo que se pide en B-22.

Por eso el sistema necesita **dos ejes independientes**: el **código oficial** (transcrito del BM-1, para la Alcaldía) y una **categoría interna** (para los reportes de la Presidencia). Esta es la propuesta para el eje interno:

| Categoría | Ejemplos |
|---|---|
| Mobiliario de oficina | Escritorios, sillas, mesas, archivadores, estantes |
| Equipos de computación | CPU, laptops, monitores, impresoras, escáneres, UPS |
| Equipos de comunicación | Teléfonos, radios, centrales telefónicas |
| Equipos audiovisuales | Videobeam, cámaras, televisores, sonido, micrófonos |
| Climatización y refrigeración | Aires acondicionados, ventiladores, neveras |
| Electrodomésticos y enseres | Cafeteras, microondas, dispensadores de agua |
| Máquinas y equipos de oficina | Fotocopiadoras, trituradoras, encuadernadoras |
| Herramientas y equipos de mantenimiento | Herramientas de Servicios Generales |
| Equipos de seguridad | Extintores, cámaras de vigilancia, alarmas |
| Material turístico y promocional durable | Stands, pendones, kioscos, señalética |
| Bienes culturales y bibliográficos | Libros, obras, piezas de exhibición |

---

## 9. Preguntas abiertas y nuevas

> **Actualización 2026-08-04:** con el BM-1 recibido, **B-61 y B-62 quedaron resueltas** y
> **B-60 dejó de bloquear** (ver §2-bis). **La Fase 1 ya se puede arrancar.** Lo que sigue abierto
> afecta detalles, no la estructura.

| # | | Pregunta |
|---|---|---|
| **B-60** | 🔴 | **REABIERTA (2026-09-02).** Catálogo oficial de grupos/subgrupos/secciones. Se había cerrado porque «IMATUR solo transcribe»; con el procedimiento nuevo **IMATUR clasifica**, así que el catálogo pasa de comodidad a **requisito** — ver §2-ter C-4 y B-75. |
| ~~B-61~~ | ✅ | ~~Ejemplos reales de código.~~ **Resuelta con el BM-1:** `2-01-108`, N° de orden de 3 dígitos con ceros a la izquierda (`084`, `131`, `171`…). |
| ~~B-62~~ | ✅ | ~~Qué significa "cantidad" en el código.~~ **Resuelta:** es la cantidad de la fila y siempre vale 1; no forma parte del identificador. |
| ~~B-69~~ | ✅ | ~~¿El costo es control interno o la Alcaldía lo exigirá?~~ **Respondida (2026-08-05): es control INTERNO.** Para la Alcaldía es irrelevante, por eso su registro lleva `S/P` aunque IMATUR tenga el informe y la factura. Sin cambios: el sistema ya registra el costo internamente y no lo declara. |
| ~~B-70~~ | ✅ | ~~¿Cada cuánto llega el BM-1?~~ **Respondida (2026-08-05): es un evento puntual**, sin periodicidad conocida. Sin cambios: el sistema registra cada recepción cuando ocurre, no asume calendario. |
| ~~B-71~~ | ✅ | ~~¿Existe el BM-1 en digital?~~ **Respondida (2026-09-02): SÍ**, existe versión digital de **todos** los documentos, y además de un **inventario interno que la encargada lleva aparte**. **Pedir ese archivo:** habilita carga masiva de los ~142 bienes, el catálogo de códigos ya usados y el punto de partida de la secuencia. |
| ~~B-72~~ | ✅ | ~~¿Qué significan los saltos en el N° de orden?~~ **Reinterpretada (2026-09-02): NO son bajas.** El listado está ordenado **por departamento, no por código**, y por eso los códigos parecen desordenados. Se confirmará al ordenar el archivo digital por código. **Implicación:** el punto de partida es el `MAX` de **todo** el archivo, no el último de una hoja o departamento. |
| ~~B-63~~ | ✅ | ~~¿Cómo se define el umbral de mobiliario?~~ **Respondida (2026-08-05): por el número de empleados del departamento.** Implementado en la mig. 067: `inventario_dotacion` define unidades por empleado y por categoría, y el reporte de **Suficiencia** compara lo que hay contra lo que debería haber. |
| ~~B-64~~ | ✅ | ~~¿Cómo identifica el sistema a la Coordinadora de Bienes?~~ **Respondida (2026-08-04): por CARGO + DEPARTAMENTO.** Implementado en la mig. 063 con las claves de configuración `bienes_cargo_autoriza` y `bienes_depto_autoriza`. |
| ~~B-65~~ | ✅ | ~~¿La sede del aeropuerto es un departamento o una ubicación aparte?~~ **Respondida (2026-08-05): es un DEPARTAMENTO más, con su propio coordinador y por tanto su propio responsable.** Verificado antes de crearla: no existía en `departamentos`, ni en el organigrama oficial (Manual Descriptivo de Cargos, abril 2024), ni en los documentos de RRHH — el único rastro era `ubicaciones.sede`. Creada en la mig. 067 y ubicada bajo la **Dirección de Planificación y Gestión Turística** (mig. 068, confirmado por el cliente). |
| ~~B-66~~ | ✅ | ~~¿Se eliminan `tipo_bien` y `cantidad`?~~ **Respondida (2026-08-05): SÍ.** Eliminadas en la mig. 067 junto con sus constantes y las consultas que las usaban (CMI-I01/I03). |
| ~~B-67~~ | ✅ | ~~¿Cómo se refleja el bien dado de baja que espera retiro?~~ **Respondida (2026-08-05): con una etiqueta "Por retirar".** Implementado en la mig. 067 (`retirado_alcaldia` + `fecha_retiro`): el bien sale del inventario activo pero se distingue entre *Por retirar* y *Retirado*, con acción para confirmar cuándo la Alcaldía se lo llevó. |
| ~~B-68~~ | ✅ | ~~¿Responsable automático o manual?~~ **Respondida (2026-08-05): AUTOMÁTICO.** Se deduce del departamento donde está el bien (Director y, en su defecto, Coordinador); si entra alguien nuevo en ese cargo, pasa a ser responsable de todos los bienes de su departamento. Implementado en la mig. 066 — se eliminó `inventario.id_responsable` y se deriva en la consulta. |

### Formatos que faltan por pedir
- [x] ~~Formato de inventario de la Alcaldía (B-02)~~ — **recibido**: Formulario BM-1, `docs/formatos/`
> **Actualizado 2026-09-02 (§2-ter):** el cliente enviará los formatos **nuevos** cuando los tenga —
> el procedimiento cambió, así que los formatos viejos ya no sirven de referencia para dos de ellos.

- [ ] **Relación / informe de bienes nuevos** que IMATUR envía a la Alcaldía ← **el más urgente**.
      Ahora lleva el **código que IMATUR asigna** y el **monto**
- [ ] **Acta de Desincorporación** (por lote; la Alcaldía firma y sella = aval). ~~Oficio de retiro~~
      **fuera del alcance**
- [ ] **Acta de asignación** de bien a un empleado ("acta de encargado") — sigue vigente
- [ ] Oficio de donación — sigue vigente
- [x] ~~Versión digital del BM-1, si existe (B-71)~~ — **respondido: SÍ existe** versión digital de
      todos los documentos, **y de un inventario interno que la encargada lleva aparte**. **Pedir ese
      archivo** (carga masiva de los ~142 bienes + catálogo de códigos + punto de partida)

---

## 10. Fases

| Fase | Contenido | Depende de |
|---|---|---|
| ~~**1. Base**~~ | ✅ **HECHA** (mig. 062): `estatus` + `condicion` separados (**cierra H-04**) · código oficial por partes + flujo de codificación contra el BM-1 · categoría interna (11 sembradas) · origen/donación · costo/proveedor/garantía · responsable único · sedes y depósito | — |
| ~~**2. Movimientos**~~ | ✅ **HECHA** (mig. 063): origen/destino · autorización por cargo+departamento · mantenimiento con salida/retorno y proceso completo · todo transaccional | — |
| **3. Documentos** | ✅ **HECHO** (mig. 064): adjuntos por bien · foto del bien · recepción del BM-1 con archivo y codificación trazable · hoja de vida del bien (B-36).<br>⏳ **Falta**: generación del informe de bienes nuevos, acta de asignación y acta de baja | 🔒 formatos reales |
| ~~**4. Explotación**~~ | ✅ **HECHA** (mig. 065): etiquetas con QR · reportes filtrables · alertas · hoja de vida (ya en Fase 3) | — |
| ~~**5. Cierre**~~ | ✅ **HECHA** (mig. 065): conteo por cambio de gestión con acta · lectura/escritura por rol | — |

> **H-04 quedó cerrado en la Fase 1** (no hizo falta esperar a la Fase 2): al introducir `estatus` se corrigieron además las 8 consultas de Dashboard, Reportes y Centro de Alertas que contaban los dados de baja como activos y que filtraban por la condición `'En Reparación'`, ya inexistente.

---

## 11. Nota sobre la documentación existente

✅ **`docs/REGLAS_NEGOCIO_Inventario.md` reescrito por completo (2026-08-05).** La versión anterior (2026-05-22) describía el módulo como un CRUD y daba por vigentes estructuras ya eliminadas (`ruta_inventario`, `taller_inventario`, Durable/Fungible). Ahora documenta las 13 reglas reales (RN-IN01…RN-IN13) derivadas del levantamiento y del BM-1, más lo que el sistema **no** hace por decisión.

---

## 12. Qué falta para terminar el módulo

Estado al 2026-08-04, tras las migraciones 062-064.

### 12.1 Construido y funcionando

| Área | Estado |
|---|---|
| Estatus administrativo vs condición física (cierra **H-04**) | ✅ |
| Código oficial por partes + flujo de codificación | ✅ |
| Categoría interna (11 sembradas) como eje aparte del código | ✅ |
| Adquisición: origen, donante, costo, fecha, proveedor, garantía | ✅ |
| Responsable nominal único · sedes · depósito | ✅ |
| Movimientos con origen/destino y autorización por cargo+departamento | ✅ |
| Mantenimiento con salida, retorno, costo y resultado | ✅ |
| Documentos de respaldo por bien + foto | ✅ |
| Recepción del BM-1 con archivo y codificación trazable | ✅ |
| Hoja de vida del bien (B-36) | ✅ |

### 12.2 Requisitos pendientes de construir

**Bloqueados por falta de formatos del cliente** 🔒

| # | Qué | Por qué está bloqueado |
|---|---|---|
| R-1 | **Relación / informe de bienes nuevos** para enviar a la Alcaldía | Es el **dolor #1 declarado** (B-05). Sin el formato real, cualquier cosa que generemos habría que rehacerla. **Cambió de naturaleza (§2-ter):** ahora lleva el código que asigna IMATUR y el monto, y es informativa — no pide inspección. |
| R-2 | **Acta de Desincorporación** (firma Coordinadora + Presidencia; la Alcaldía firma y sella como aval) | B-39. **Reducida y renombrada (§2-ter):** un solo documento **por lote**, ~~sin oficio de retiro~~. El nombre «Acta de Desincorporación» es exigencia del cliente. |
| R-3 | **Acta de asignación** de bien a responsable, que firma el empleado ("acta de encargado") | B-29. Sigue vigente. Ídem: hace falta el formato. |
| R-11 | **Oficio de donación** | Sigue vigente. Ídem. |
| R-12 | **Codificación interna con secuencia propia** (C-1…C-4, C-7) | 🔒 No es formato: espera el **punto de partida** (B-73) y el **catálogo de grupos/subgrupos/secciones** (B-75, reabre B-60). El resto es programable en cuanto lleguen esos dos datos. |
| R-13 | **Acta de Desincorporación por lote a nivel de datos** (C-5, C-6) | La tabla y el flujo (registrar el acta sellada → marcar retirados todos sus bienes) **se pueden construir sin el formato**; solo el imprimible depende de él. |

> Mientras tanto **el flujo de baja funciona a nivel de datos** (el bien pasa a *Dado de baja* y sale del inventario activo); lo que falta es el documento imprimible y el agrupamiento por acta.

**Implementables ya** — ✅ **TODOS HECHOS** (mig. 065 y 067)

| # | Qué | Origen |
|---|---|---|
| ✅ R-4 | ~~Etiquetas con código + QR~~ **hecho** | B-14, B-15. El `qrcode.min.js` ya está vendorizado. |
| ✅ R-5 | ~~Reportes de la Presidencia~~ **hecho** (vía filtros de estatus/origen/departamento/depósito) | B-51, B-53. Sin formato obligatorio (B-52). |
| ✅ R-6 | ~~Alertas~~ **hecho** (+ mantenimiento preventivo) | B-20, B-12. Se enganchan al Centro de Alertas existente. |
| ✅ R-7 | ~~Mantenimiento preventivo programado~~ **hecho** | B-56. Requiere la tabla `inventario_mantenimiento_plan`. |
| ✅ R-8 | ~~Conteo por cambio de gestión~~ **hecho**, con acta imprimible | B-05, B-48, B-50. Es el **dolor #2**. |
| ✅ R-9 | ~~RBAC del módulo~~ **hecho** (`InventarioController::puedeEscribir()`) | B-58. Hoy sigue el rol 4 genérico. |
| ✅ R-10 | ~~Eliminar `tipo_bien` y `cantidad`~~ **hecho** (mig. 067) | B-66 confirmada por el cliente. | 

### 12.3 Preguntas salientes al cliente

**Bloquean R-1, R-2, R-3 y R-11 — pedir los formatos** (el cliente los enviará *cuando tenga los
nuevos*, 2026-09-02; **pedirlos en digital**, que sí existen):

- [ ] Relación/informe de bienes nuevos, **con código y monto** ← **el más urgente**
- [ ] **Acta de Desincorporación** (por lote)
- [x] ~~Oficio de retiro tras la baja~~ — **ELIMINADO del alcance** (§2-ter): el acta sellada es el aval
- [ ] Acta de asignación de un bien a un empleado ("acta de encargado")
- [ ] Oficio de donación
- [ ] **El inventario interno que la encargada lleva aparte** (archivo digital) — no es un formato,
      es el insumo que desbloquea la carga de los ~142 bienes, el catálogo de códigos y el punto de
      partida de la secuencia

**Bloquean R-12 (codificación interna):** B-73 (punto de partida) y B-75 (catálogo de
grupos/subgrupos/secciones). Las 8 preguntas nuevas están en **§2-ter**.

**Preguntas abiertas** (ninguna bloquea lo implementable):

| # | | Pregunta |
|---|---|---|
| ~~B-63~~ | ✅ | **Respondida:** por número de empleados. Implementado (mig. 067). |
| ~~B-65~~ | ✅ | **Respondida:** es un departamento propio con su coordinador. Creado (mig. 067). |
| ~~B-66~~ | ✅ | **Respondida:** sí se eliminan. Hecho (mig. 067). |
| ~~B-67~~ | ✅ | **Respondida:** etiqueta "Por retirar". Hecho (mig. 067). |
| ~~B-68~~ | ✅ | **Respondida:** automático, derivado del departamento (mig. 066). |
| ~~B-69~~ | ⚠️ | **Matizada (2026-09-02):** era control interno, pero el **monto sí se declara** en la relación de bienes nuevos del procedimiento nuevo. El dato ya se captura. |
| ~~B-70~~ | ✅ | **Respondida:** evento puntual, sin periodicidad. |
| ~~B-71~~ | ✅ | **Respondida (2026-09-02): SÍ hay digital** de todos los documentos y del **inventario interno de la encargada**. Pedir los archivos. |
| ~~B-72~~ | ✅ | **Reinterpretada (2026-09-02): los saltos NO son bajas** — el listado va por departamento, no por código. Se confirma al ordenar el digital por código. |
| **B-73…B-80** | 🔒 | **Nuevas (2026-09-02)** — punto de partida de la secuencia · alcance y longitud de la numeración · catálogo de clasificación · reutilización de códigos · contenido y frecuencia de la relación · si la Alcaldía devuelve acuse · firmas/correlativo del acta de desincorporación · notificación por escrito del procedimiento. **Enunciadas en §2-ter.** |

### 12.4 Antes de usarlo en producción

- [ ] **Cargar los ~142 bienes reales** (B-04). Hoy la tabla está vacía; sin datos no se puede validar nada de esto contra la realidad.
- [ ] Asignar el cargo de **Coordinador** en el departamento *Compra de Bienes y Servicios*: mientras el puesto esté vacante, el sistema **bloquea** todos los movimientos (por diseño, B-32).
- [ ] Revisar que las **11 categorías** propuestas encajen con cómo quieren agrupar sus bienes.
