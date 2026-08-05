# Plan de reconstrucción — Módulo de Bienes (Inventario)

**Fecha:** 2026-08-04 · **Base:** respuestas del cliente en `PREGUNTAS_DESCUBRIMIENTO_Bienes_Rutas.md` (Parte 1, B-01…B-59) + **Formulario BM-1 real** entregado el mismo día (§2-bis) · **Estado BD hoy:** migraciones hasta 061

> **Estado: ✅ Fases 1, 2 y 4 completas · Fase 3 parcial** (migraciones 062-065, 2026-08-04).
> **Lo único pendiente son 3 documentos bloqueados por los formatos del cliente — §12.**

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
| ~~B-60~~ | ✅ | ~~Catálogo oficial de grupos/subgrupos/secciones.~~ **Ya no bloquea.** Conocemos la estructura y que los valores los asigna la Alcaldía; IMATUR solo los transcribe. Seguiría siendo útil para un desplegable, pero no es requisito. |
| ~~B-61~~ | ✅ | ~~Ejemplos reales de código.~~ **Resuelta con el BM-1:** `2-01-108`, N° de orden de 3 dígitos con ceros a la izquierda (`084`, `131`, `171`…). |
| ~~B-62~~ | ✅ | ~~Qué significa "cantidad" en el código.~~ **Resuelta:** es la cantidad de la fila y siempre vale 1; no forma parte del identificador. |
| B-69 | ▲ | **Valores en S/P.** En el BM-1 las columnas *Valor unitario* y *Valor total* aparecen en `S/P` en todas las filas, pero B-17 dice que sí registran costo y factura. ¿El costo es solo control interno, o la Alcaldía va a empezar a exigir el monto declarado? |
| B-70 | ▲ | **Recepción del BM-1.** ¿Cada cuánto lo devuelve la Alcaldía — con cada lote verificado, una vez al año, cuando se lo piden? Define si la conciliación es un evento puntual o una rutina. |
| B-71 | ▲ | **El BM-1 llega en papel.** ¿Existe la versión digital (Excel/Word) del archivo que arma la Alcaldía? Si la hay, la carga de códigos podría ser automática en vez de teclear bien por bien. |
| B-72 | ○ | **Numeración del N° de orden.** Los números de la muestra van del 025 al 171 con saltos. ¿Los huecos son bienes ya dados de baja, o la Alcaldía numera de forma continua para toda la Alcaldía y no solo para IMATUR? |
| B-63 | ▲ | **El "umbral" de B-45/B-47.** No es stock de consumibles, sino saber si *alcanzan* los bienes (sillas por empleado, mesas por departamento). ¿Cómo lo definirían: una cantidad esperada por departamento, o una relación contra el número de empleados? |
| ~~B-64~~ | ✅ | ~~¿Cómo identifica el sistema a la Coordinadora de Bienes?~~ **Respondida (2026-08-04): por CARGO + DEPARTAMENTO.** Implementado en la mig. 063 con las claves de configuración `bienes_cargo_autoriza` y `bienes_depto_autoriza`. |
| B-65 | ▲ | **Sede del aeropuerto** (B-24): sus bienes, ¿se asignan a algún departamento de IMATUR o la sede funciona como una ubicación independiente con su propio responsable? |
| B-66 | ▲ | **Contradicción con la migración 044:** se implementaron Durable/Fungible y cantidad respondiendo a D-IN05. Ahora B-07/B-09 dicen que no se llevan consumibles y que el registro es individual. ¿Confirmamos que se eliminan? |
| B-67 | ○ | B-42 quedó sin definir: mientras el bien dado de baja espera que la Alcaldía lo retire, ¿debe reflejarse en algún lado (una ubicación "por retirar")? |
| ~~B-68~~ | ✅ | ~~¿Responsable automático o manual?~~ **Respondida (2026-08-05): AUTOMÁTICO.** Se deduce del departamento donde está el bien (Director y, en su defecto, Coordinador); si entra alguien nuevo en ese cargo, pasa a ser responsable de todos los bienes de su departamento. Implementado en la mig. 066 — se eliminó `inventario.id_responsable` y se deriva en la consulta. |

### Formatos que faltan por pedir
- [x] ~~Formato de inventario de la Alcaldía (B-02)~~ — **recibido**: Formulario BM-1, `docs/formatos/`
- [ ] **Informe / oficio de bienes nuevos** que IMATUR envía a la Alcaldía ← **el más urgente ahora**
- [ ] Acta administrativa de baja
- [ ] Oficio de asignación de bien a un empleado
- [ ] Oficio de donación
- [ ] Versión digital del BM-1, si existe (B-71)

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

`docs/REGLAS_NEGOCIO_Inventario.md` quedó **obsoleto** (2026-05-22): describe `ruta_inventario` y `taller_inventario` (eliminadas en la mig. 050), y da por resueltas D-IN01/D-IN05 con criterios que estas respuestas contradicen. Debe reescribirse al cerrar la Fase 1, no antes.

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
| R-1 | **Informe / oficio de bienes nuevos** para enviar a la Alcaldía | Es el **dolor #1 declarado** (B-05). Sin el formato real, cualquier cosa que generemos habría que rehacerla. |
| R-2 | **Acta administrativa de baja** (firma Coordinadora + Presidencia) + oficio de retiro a la Alcaldía | B-39. Ídem: hace falta el formato. |
| R-3 | **Acta / oficio de asignación** de bien a responsable, que firma el empleado | B-29. Ídem. |

> Mientras tanto **el flujo de baja funciona a nivel de datos** (el bien pasa a *Dado de baja* y sale del inventario activo); lo que falta es el documento imprimible.

**Implementables ya** — ✅ **TODOS HECHOS** (mig. 065), salvo R-10

| # | Qué | Origen |
|---|---|---|
| ✅ R-4 | ~~Etiquetas con código + QR~~ **hecho** | B-14, B-15. El `qrcode.min.js` ya está vendorizado. |
| ✅ R-5 | ~~Reportes de la Presidencia~~ **hecho** (vía filtros de estatus/origen/departamento/depósito) | B-51, B-53. Sin formato obligatorio (B-52). |
| ✅ R-6 | ~~Alertas~~ **hecho** (+ mantenimiento preventivo) | B-20, B-12. Se enganchan al Centro de Alertas existente. |
| ✅ R-7 | ~~Mantenimiento preventivo programado~~ **hecho** | B-56. Requiere la tabla `inventario_mantenimiento_plan`. |
| ✅ R-8 | ~~Conteo por cambio de gestión~~ **hecho**, con acta imprimible | B-05, B-48, B-50. Es el **dolor #2**. |
| ✅ R-9 | ~~RBAC del módulo~~ **hecho** (`InventarioController::puedeEscribir()`) | B-58. Hoy sigue el rol 4 genérico. |
| ⏸ R-10 | Eliminar `tipo_bien` y `cantidad` de la BD — **lo único pendiente aquí** | Depende solo de confirmar **B-66**. |

### 12.3 Preguntas salientes al cliente

**Bloquean R-1, R-2 y R-3 — pedir los formatos físicos:**

- [ ] Informe/oficio de bienes nuevos que IMATUR envía a la Alcaldía ← **el más urgente**
- [ ] Acta administrativa de baja
- [ ] Oficio de retiro que se manda a la Alcaldía tras la baja
- [ ] Acta/oficio de asignación de un bien a un empleado
- [ ] Oficio de donación

**Preguntas abiertas** (ninguna bloquea lo implementable):

| # | | Pregunta |
|---|---|---|
| B-63 | ▲ | **Umbral de mobiliario.** No es stock de consumibles: es saber si *alcanzan* los bienes (sillas por empleado, mesas por departamento). ¿Cómo se define — cantidad esperada por departamento, o relación contra el número de empleados? |
| B-65 | ▲ | **Sede del aeropuerto.** Sus bienes, ¿se asignan a algún departamento de IMATUR o la sede funciona como ubicación independiente con su propio responsable? |
| B-66 | ▲ | **Confirmar eliminación** de `tipo_bien` (Durable/Fungible) y `cantidad`. B-07 dice que no llevan consumibles y B-09 que el registro es individual, así que sobran. Hoy están sin uso pero siguen en la BD. |
| B-67 | ○ | Mientras el bien dado de baja espera que la Alcaldía lo retire, ¿debe reflejarse en algún lado (una ubicación "por retirar")? |
| ~~B-68~~ | ✅ | **Respondida:** automático, derivado del departamento (mig. 066). |
| B-69 | ▲ | **Valores en "S/P".** El BM-1 trae *Valor unitario* y *Valor total* en `S/P` en todas las filas, pero B-17 dice que sí registran costo y factura. ¿Es solo control interno o la Alcaldía va a exigir el monto? |
| B-70 | ▲ | ¿Cada cuánto devuelve la Alcaldía el BM-1 — con cada lote verificado, una vez al año, cuando se lo piden? |
| B-71 | ▲ | ¿Existe el BM-1 en **digital** (Excel/Word)? Si la Alcaldía lo arma en computadora, la carga de códigos podría ser automática en vez de teclear bien por bien. |
| ~~B-72~~ | ✅ | ~~¿Qué significan los saltos en el N° de orden?~~ **Respondida (2026-08-05):** la numeración es un registro que lleva **la Alcaldía** con criterio propio, garantizando que no se repita en ningún bien. IMATUR la desconoce y solo la transcribe. Sin cambios de código: el sistema ya se limita a copiarla. |

### 12.4 Antes de usarlo en producción

- [ ] **Cargar los ~142 bienes reales** (B-04). Hoy la tabla está vacía; sin datos no se puede validar nada de esto contra la realidad.
- [ ] Asignar el cargo de **Coordinador** en el departamento *Compra de Bienes y Servicios*: mientras el puesto esté vacante, el sistema **bloquea** todos los movimientos (por diseño, B-32).
- [ ] Revisar que las **11 categorías** propuestas encajen con cómo quieren agrupar sus bienes.
