# Plan de reconstrucción — Módulo de Bienes (Inventario)

**Fecha:** 2026-08-04 · **Base:** respuestas del cliente en `PREGUNTAS_DESCUBRIMIENTO_Bienes_Rutas.md` (Parte 1, B-01…B-59) · **Estado BD hoy:** migraciones hasta 061

---

## 1. Qué revelaron las respuestas

El módulo actual fue construido como un **CRUD de bienes genérico**: registrar un bien, moverlo, darlo de baja. Las respuestas describen algo distinto: un **expediente administrativo por bien**, con un ciclo de vida gobernado por la Alcaldía y respaldado por documentos firmados.

Las cinco diferencias de fondo:

| # | Lo que asumió el sistema | Lo que realmente ocurre |
|---|---|---|
| 1 | El bien nace con su código | El bien **nace sin código**. Se registra, se emite un **oficio a la Alcaldía**, ellos inspeccionan, asignan el N° de orden y pegan la etiqueta física (B-03, B-12, B-14) |
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

---

## 4. Flujos a construir

### 4.1 Codificación (el dolor #1 — B-05)

```
Registrar bien  →  estatus "En espera de codificación"
      ↓
Generar OFICIO a la Alcaldía  ←── el sistema lo produce (hoy es manual)
      ↓
Inspección de la Alcaldía
      ↓
Cargar N° de orden + marcar verificado  →  estatus "Activo"
      ↓
Imprimir etiqueta con QR (B-15)
```

Se puede agrupar **varios bienes en un mismo oficio** (lo habitual al recibir un lote).

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
| **Oficio de solicitud de codificación** a la Alcaldía | B-03, B-05, B-12 |
| **Acta/oficio de asignación** de bien a responsable | B-29 |
| **Acta administrativa de baja** (firma Coordinadora + Presidencia) | B-39 |
| **Oficio a la Alcaldía** para retiro del bien desincorporado | B-39, B-40 |
| **Etiqueta con código + QR** | B-14, B-15 |
| **Acta de conteo** por cambio de gestión | B-48, B-49 |

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

**Antes de adoptar cualquier lista propia, hay que entender esto:** el código de la Alcaldía es `grupo-subgrupo-sección-…`. Es decir, **la Alcaldía ya tiene un catálogo oficial de clasificación** — y las categorías del sistema deberían ser ese catálogo, no uno inventado por nosotros. Pedirlo es la acción más importante de esta lista (ver §9).

Mientras llega, esta propuesta de trabajo cubre lo típico de un instituto de este tamaño:

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

Ninguna impide empezar por la Fase 1, pero **B-60 y B-61 conviene resolverlas antes de tocar el código de codificación**.

| # | | Pregunta |
|---|---|---|
| B-60 | ⭐ | **El catálogo oficial de la Alcaldía.** ¿Nos pueden facilitar la tabla de **grupos, subgrupos y secciones** que usa el Departamento de Bienes? Define las categorías del sistema y la estructura del código. Sin esto, la clasificación de §8 es una suposición. |
| B-61 | ⭐ | **Tres ejemplos reales de código BN.** Se pidió en B-11 y quedó sin responder: llegó el formato (`grupo-subgrupo-sección-cantidad-N° de orden`) pero no un código real escrito. Necesitamos verlos para saber cuántos dígitos lleva cada parte y con qué se separan. |
| B-62 | ⭐ | En ese formato aparece **"cantidad"**, pero B-09 dice que cada bien se registra individualmente con su propio código. ¿Qué representa esa parte — siempre 1, o la cantidad del lote en que se adquirió? |
| B-63 | ▲ | **El "umbral" de B-45/B-47.** No es stock de consumibles, sino saber si *alcanzan* los bienes (sillas por empleado, mesas por departamento). ¿Cómo lo definirían: una cantidad esperada por departamento, o una relación contra el número de empleados? |
| B-64 | ▲ | **La Coordinadora de Bienes autoriza los movimientos** (B-32). ¿Cómo la identifica el sistema — por el cargo de la persona, por su departamento, o se designa manualmente? |
| B-65 | ▲ | **Sede del aeropuerto** (B-24): sus bienes, ¿se asignan a algún departamento de IMATUR o la sede funciona como una ubicación independiente con su propio responsable? |
| B-66 | ▲ | **Contradicción con la migración 044:** se implementaron Durable/Fungible y cantidad respondiendo a D-IN05. Ahora B-07/B-09 dicen que no se llevan consumibles y que el registro es individual. ¿Confirmamos que se eliminan? |
| B-67 | ○ | B-42 quedó sin definir: mientras el bien dado de baja espera que la Alcaldía lo retire, ¿debe reflejarse en algún lado (una ubicación "por retirar")? |
| B-68 | ○ | ¿El responsable del bien se **deriva automáticamente** del director/coordinador del departamento, o se elige a mano? Cambia si hay que mantenerlo al cambiar el liderazgo. |

### Formatos que faltan por pedir
- [ ] Oficio de solicitud de codificación a la Alcaldía ← **el más urgente**
- [ ] Acta administrativa de baja
- [ ] Oficio de asignación de bien a un empleado
- [ ] Oficio de donación
- [ ] El formato de inventario de la Alcaldía mencionado en B-02
- [ ] Catálogo de grupos/subgrupos/secciones (B-60)

---

## 10. Fases

| Fase | Contenido | Depende de |
|---|---|---|
| **1. Base** | `estatus` + `condicion` separados · flujo de codificación · origen/donación · costo/proveedor/garantía · responsable · sedes y depósito · categorías reales | B-60, B-61 |
| **2. Movimientos** | Origen/destino · autorización · mantenimiento con retorno · corrección definitiva de H-04 | Fase 1 |
| **3. Documentos** | Adjuntos por bien (factura, informe, oficios) · generación de oficio de codificación, acta de asignación, acta de baja | Fase 1 · formatos reales |
| **4. Explotación** | Etiquetas con QR · reportes de §6 · alertas de §7 · hoja de vida del bien (B-36) | Fases 1-3 |
| **5. Cierre** | Conteo por cambio de gestión · RBAC de §7 | Fase 4 |

> **H-04 se corrige en la Fase 2**, con criterio ya definido por el cliente (§3.2). Mientras tanto el inventario sigue reportando como activos los bienes dados de baja — pero como la tabla está **vacía (0 filas)**, hoy no hay datos incorrectos a la vista.

---

## 11. Nota sobre la documentación existente

`docs/REGLAS_NEGOCIO_Inventario.md` quedó **obsoleto** (2026-05-22): describe `ruta_inventario` y `taller_inventario` (eliminadas en la mig. 050), y da por resueltas D-IN01/D-IN05 con criterios que estas respuestas contradicen. Debe reescribirse al cerrar la Fase 1, no antes.
