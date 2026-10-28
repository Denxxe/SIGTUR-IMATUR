# Módulo de Bienes (Inventario) — Reglas de Negocio

**Última actualización:** 2026-10-28 · **Migraciones:** hasta 090
**Fuentes:** levantamiento con el cliente (`docs/entregables/PREGUNTAS_DESCUBRIMIENTO_Bienes_Rutas.md`,
Parte 1, B-01…B-72) y respuestas posteriores (B-73…B-84, enunciadas en `docs/BACKLOG.md` §3.4);
formatos reales en `docs/formatos/`. Historial de lo construido: `docs/CHANGELOG.md`.

---

## Contexto institucional

Los bienes de IMATUR los gestiona la **Coordinación de Compras, Bienes y Servicios** (B-01),
adscrita a la Dirección de Administración. Pertenecen patrimonialmente a la **Alcaldía del
Municipio Sucre**, que lleva el registro consolidado.

El módulo es un **expediente administrativo por bien** —ciclo de vida respaldado por documentos
firmados—, no un CRUD. Dos dolores declarados (B-05): el documento al recibir un bien nuevo y la
auditoría completa en cada **cambio de gestión**.

**Escala:** ~142 bienes (B-04). Sin paginación de servidor ni lector de códigos: el valor está en el
control documental y la trazabilidad.

**Cambio de procedimiento (cliente, 2026-09-02).** IMATUR, como ente autónomo, pasa a **asignar el
código** de sus bienes a partir de la **última revisión de la Alcaldía** (todavía no hecha), y el
acta de baja pasa a ser **Acta de Desincorporación por lote**, sin oficio de retiro. La notificación
fue verbal (B-80): la responsabilidad de codificar se asume sin respaldo escrito. Si tras esa
revisión nada se envía ya a la Alcaldía (B-78-bis, abierta), cambiarían la relación de bienes nuevos
y el aval del acta; no se toca nada hasta confirmarlo.

---

## RN-IN01 — Qué entra al inventario

| Regla | Por qué |
|---|---|
| Solo **bienes durables** (mobiliario, equipos, herramientas) | B-06 |
| **No se llevan consumibles** (papelería, limpieza, material gastable) | B-07, B-43, B-44. Por eso `tipo_bien` (Durable/Fungible) y `cantidad`, de la mig. 044, se **eliminaron** en la mig. 067 (B-66) |
| **Registro individual**: 20 sillas iguales son 20 registros, cada uno con su código | B-09. La «cantidad» del BM-1 siempre vale 1 (B-62) |
| Todo es de IMATUR: no hay comodatos ni préstamos de terceros | B-08 — la propiedad no se modela |
| `serial` es opcional y no identifica el bien (el código sí), pero se registra | B-13; matizado por el BM-1, que lo trae dentro de la descripción |

---

## RN-IN02 — El código oficial

Formato del **Formulario BM-1** de la Alcaldía (`docs/formatos/BM-1_inventario_bienes_muebles_alcaldia.jpeg`):

```
GRUPO - SUB-GRUPO - SECCIÓN - N° DE ORDEN
  2   -    01     -   108   -     084
```

- Grupo 1 dígito · sub-grupo 2 · sección 3 · N° de orden 3 dígitos con ceros a la izquierda (B-61).
  La columna *Cantidad* del BM-1 no forma parte del identificador (B-62).
- En la BD el código vive **por partes** (`codigo_grupo`, `codigo_subgrupo`, `codigo_seccion`,
  `nro_orden`); `codigo_bn` es el compuesto, que arma **solo** `Inventario::componerCodigo()`.
- El código **no se teclea en el alta**: un bien nace sin código (B-12) y lo recibe en el acto de
  codificar (RN-IN03). En edición se conserva el existente.
- **Un código no se recicla** (B-76): un desincorporado conserva su número.
- **El código no clasifica.** En el BM-1 real sillas, mesas, pizarra, aire acondicionado y router
  comparten `2-01-108`. Por eso hay **dos ejes independientes**: el código oficial (para la
  Alcaldía) y la **categoría interna** (para los reportes de la Presidencia, RN-IN04).

**Quién lo asigna** (`inventario.codificado_por` + `fecha_codificacion`, mig. 087):

| Procedencia | Cuándo | Efecto |
|---|---|---|
| `Alcaldía` | Se transcribe del BM-1 (histórico y el lote que espera la última revisión) | Además marca `verificado_alcaldia` + `fecha_verificacion` = **reconocido por la Alcaldía** |
| `IMATUR` | Codificación propia encendida | **No** toca `verificado_alcaldia`: codificado ≠ reconocido |

Quién decide la procedencia es el controlador: lo que viene de un BM-1 es siempre de la Alcaldía; lo
demás, de IMATUR si la codificación propia está encendida.

---

## RN-IN03 — Codificación

### Con la codificación propia APAGADA (estado actual, valor por defecto)

```
① ALTA INTERNA ─────────────→ estatus «En espera de codificación», sin código
② RELACIÓN A LA ALCALDÍA ───→ oficio con el lote de bienes nuevos (RN-IN10)
③ BM-1 CONSOLIDADO (entrante) → la Alcaldía devuelve el formulario con el código por bien
④ CONCILIACIÓN ─────────────→ se registra la recepción del BM-1 con su escaneado
                               (`inventario_consolidados_bm1`) y desde ahí se transcribe el
                               código de cada bien → «Activo», trazable a su BM-1
```

- **El BM-1 no lo genera el sistema**: es el registro que elabora y devuelve la Alcaldía. Su
  recepción es un **evento puntual**, sin periodicidad (B-70).
- La Alcaldía agrupa el BM-1 en bandas por departamento, con los mismos nombres de `departamentos`:
  la conciliación puede hacerse departamento por departamento.
- La conciliación se **conserva** aun con la codificación propia: la última revisión de la Alcaldía
  traerá códigos que hay que cargar, y el histórico recibido debe quedar trazable.

### Con la codificación propia ENCENDIDA (`/inventario/codificacion`, `CodificacionBienes`)

Alta → *Codificar* → elegir grupo-subgrupo-sección del catálogo → el sistema **propone** el
siguiente N° de orden (editable, normalizado a los dígitos configurados, sin repetidos) → «Activo»,
codificado por IMATUR. Ya no se espera una inspección.

| Parámetro (`configuracion_sistema`) | Regla |
|---|---|
| `bienes_codificacion_propia` | Interruptor. **0** por defecto = transcribir el BM-1 · **1** = codifica IMATUR. **No enciende** sin punto de partida ni sin una clasificación vigente; se **apaga sola** si se da de baja la última clasificación vigente |
| `bienes_nro_orden_inicio` (B-73) | Último N° de orden que deje la Alcaldía. Siguiente = **mayor entre este y el mayor registrado** + 1 |
| `bienes_nro_orden_digitos` (B-74) | Ceros a la izquierda (3 → `084`). Si un número los supera se escribe completo (`1000`) |
| `bienes_secuencia_por_clasificacion` (B-74) | **0 = una sola secuencia para todo IMATUR** (decidido 2026-10-11) · 1 = una por grupo-subgrupo-sección. El punto de partida es uno solo |

**Reglas de la secuencia:**

- Los números se comparan **como enteros** (`084` = `84`); lo no numérico (`S/C`) se ignora.
- El máximo se toma sobre **todos** los bienes —activos, desincorporados y papelera— porque un código
  no se recicla (B-76) y porque el listado de la Alcaldía viene **ordenado por departamento, no por
  código** (B-72): los saltos aparentes no son bajas. El punto de partida es el máximo del registro
  completo, nunca el último de una hoja.
- Los repetidos se rechazan con `CodificacionBienes::nroOrdenOcupado()` (`084` = `84`).

**Catálogo de clasificaciones** (`inventario_clasificaciones`, B-75 / B-60 reabierta): si IMATUR
clasifica, necesita la lista de valores válidos. Sembrado con **2-01-108** (el único conocido) y los
códigos ya en uso; se completa desde la pantalla. Con la codificación propia solo se codifica con
una clasificación vigente; el código de una clasificación con bienes no se puede cambiar (solo su
descripción) y no se borra: se da de baja o se reactiva.

---

## RN-IN04 — Estatus, condición y categoría: tres ejes distintos

Mezclar estatus y condición en una columna fue el origen del bug **H-04** (cerrado en la mig. 062).

**`condicion`** — estado *físico*: `Nuevo`, `Bueno` (por defecto), `Regular`, `Dañado`.

**`estatus`** — situación *administrativa* (`Inventario::ESTATUS`):

| Estatus | Significado | ¿Inventario activo? |
|---|---|---|
| En espera de codificación | Registrado, sin N° de orden (estatus inicial) | Sí, con distintivo |
| Activo | Operativo y disponible | Sí |
| En mantenimiento | Fuera de servicio temporalmente | **Sí**, no disponible |
| Extraviado | Pérdida en averiguación | Sí, marcado |
| Robado | Con denuncia | Sí, marcado |
| Desincorporado | Dado de baja (antes `Dado de baja`, renombrado en la mig. 076 por exigencia del cliente) | **No** |

- Criterio del cliente: el bien **en mantenimiento no desaparece** —hay que seguir viendo su código—
  (B-34); el **desincorporado sí sale** del inventario activo, conservando su registro como aval (B-38).
- **Desincorporar ≠ borrar.** El desincorporado sigue con `is_active = TRUE`; `is_active = FALSE` es
  la **papelera** (registros creados por error). Confundirlos fue el bug H-16. El valor del estatus
  nunca se escribe literal en SQL: `Inventario::EST_BAJA`.
- **A mano** solo se cambia entre Activo / En mantenimiento / Extraviado / Robado. La codificación y
  la desincorporación tienen su propio flujo.

**`categoria`** — clasificación interna para la Presidencia (B-22), independiente del código. Las
**11 sembradas** (confirmadas por el cliente):

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

## RN-IN05 — Responsable: derivado, nunca manual

El responsable **no se elige ni se almacena**: se deduce en cada consulta (B-68).

```
bien → ubicación → departamento → Director (o, en su defecto, Coordinador) activo
```

- Un bien tiene **un solo** responsable (B-27).
- Si entra alguien nuevo en ese cargo, pasa a responder por **todos** los bienes del departamento.
- Cuando un trabajador egresa, los bienes **no lo siguen**: quedan en el departamento (B-28, B-30).
- Bienes en **depósito**: su custodio es la jefatura del departamento que autoriza los movimientos
  (Coordinación de Bienes, RN-IN07).

`inventario.id_responsable` **se eliminó** (mig. 066): una columna almacenada quedaría desactualizada
al cambiar un cargo, egresar un empleado o trasladar un bien. El movimiento «Asignación de
responsable» ya no se registra (se conserva para mostrar los históricos). El responsable de cada
movimiento en su momento queda en `actividad_inventario`.

---

## RN-IN06 — Ubicaciones, sedes y depósito

- La ubicación de un bien se define **por departamento** (B-23, B-25); un departamento puede tener
  varias ubicaciones. Sin ubicación no se puede registrar un bien.
- **Dos sedes** (`Ubicacion::SEDES`): *Sede Principal* y *Aeropuerto de Cumaná* (Oficina de
  Información Turística, con bienes bajo control propio, B-24). Esa oficina es un **departamento
  más**, con su propio coordinador y por tanto su propio responsable (B-65), y cuelga de la
  Dirección de Planificación y Gestión Turística (mig. 068).
- El **depósito** (`es_deposito`) es el área común de los bienes sin asignar: todo bien no asignado
  a un departamento debe estar ahí.
- La mig. 069 sembró 25 ubicaciones (una por departamento activo + Depósito General). Los nombres
  arrancan iguales al departamento; el cliente los renombra a su referencia real (planta, cubículo).

---

## RN-IN07 — Movimientos

| Movimiento | Efecto sobre el bien |
|---|---|
| Traslado | Cambia la ubicación (y con ella, el responsable) |
| Salida a mantenimiento | Estatus → En mantenimiento; abre el proceso (RN-IN08) |
| Retorno de mantenimiento | Estatus → Activo; cierra el proceso |
| Baja | Estatus → Desincorporado (RN-IN09) |

- Los tres traslados del cliente (depósito→departamento, departamento→depósito,
  departamento→departamento, B-31) son **un solo tipo `Traslado`** con origen y destino; el caso se
  deduce de las ubicaciones. El origen es donde está el bien ahora.
- **Toda operación la autoriza la Coordinación de Bienes** (B-32). El sistema la identifica por
  **cargo + departamento** (`bienes_cargo_autoriza` / `bienes_depto_autoriza`, B-64). Si el puesto
  está **vacante, los movimientos quedan bloqueados**: es preferible detener la operación a registrar
  movimientos sin autorizar.
- Movimiento y efecto son **transaccionales**: ocurren juntos o no ocurren.
- **Bloqueos:** un bien desincorporado no admite movimientos; uno sin codificar tampoco; no se
  traslada al mismo sitio; no hay doble salida a mantenimiento ni retorno sin mantenimiento abierto;
  la fecha no puede ser futura.

---

## RN-IN08 — Mantenimiento

**Correctivo** (B-33): lo ejecuta **Servicios Generales** o un taller externo. Se registra el
proceso completo —encargado o proveedor, falla, trabajo realizado, costo y resultado (`Reparado` ·
`Sin reparación` · `Irrecuperable`)—, no solo la salida. Un bien no puede tener dos mantenimientos
abiertos. Si el resultado es **Irrecuperable**, el bien vuelve a Activo con condición `Dañado`, a la
espera de su desincorporación.

**Preventivo** (B-56): calendario por bien (frecuencia trimestral, semestral o anual + próxima fecha)
para aires, impresoras y computadoras. Al retornar de un mantenimiento la próxima fecha **avanza
sola**; si eso falla, no revierte el retorno.

---

## RN-IN09 — Desincorporación

**Motivos** (B-37): robo, deterioro, pérdida. En **robo o pérdida** se suma la **denuncia** y la
averiguación administrativa (B-41).

**Es un acto administrativo** (B-39): el **Acta de Desincorporación** —así debe llamarse en el
sistema, no «acta de baja»— la firman **solo dos**: la Coordinadora de Bienes y la Presidencia, y
lleva **correlativo de IMATUR** (B-79). **No hay oficio de retiro**: el acta lista todos los bienes
a retirar y la Alcaldía la **firma y sella**; esa acta sellada es el **aval** del retiro. Es **por
lote** (B-79-bis).

**Ciclo** (`inventario_actas_desincorporacion`, mig. 077):

```
1. El bien se desincorpora      → «Desincorporado · Por retirar»: sale del inventario activo
                                   pero sigue en la sede (B-38, B-67)
2. Se emite el acta con un lote → número del correlativo `acta`; los bienes quedan enganchados
3. Se imprime y la Alcaldía firma y sella
4. Se registra el acta firmada  → fecha de firma, quién recibe por la Alcaldía y escaneado:
   (con su escaneado)              TODOS sus bienes pasan a «Retirado» con esa fecha
```

- Solo entran al acta bienes desincorporados, sin retirar y sin otra acta. Se valida **antes** de
  pedir número, para no quemar correlativos.
- La fecha de firma no puede ser futura ni anterior a la del acta.
- **Anular** exige motivo: los bienes vuelven a quedar sin acta y, si ya estaba firmada, se les
  **revierte el retiro** (el aval dejó de existir). El número **no se recicla**.
- Se conserva la confirmación de retiro **bien por bien** (`marcarRetirado`) para casos sueltos.

**Después de la desincorporación** (B-67), el listado de desincorporados distingue:

- **Desincorporado · Por retirar** — todavía en las instalaciones.
- **Desincorporado · Retirado** — la Alcaldía ya se lo llevó (con su fecha).

> ⚠️ **Hueco en el código (2026-10-28):** el paso 1 existe en el modelo
> (`ActividadInventario::registrarMovimiento()` con tipo `Baja`), pero **ninguna pantalla lo
> ofrece**: `Baja` no está en `TIPOS_MANUALES` y la edición del bien no permite elegir
> `Desincorporado`. Hoy no hay forma de desincorporar un bien desde la UI.

---

## RN-IN10 — Documentos

### Expediente por bien (B-16 a B-19)

Adjuntos (`InventarioDocumento::TIPOS`): factura o documento de adquisición · informe de la Alcaldía
· oficio de donación · acta de asignación · acta de baja/desincorporación · denuncia · garantía ·
otro. Más la **foto** del bien (B-21). Los archivos viven **fuera del web root**
(`storage/uploads/bienes/`) y se sirven por id con control de rol (`DescargaController`).

**Datos de adquisición:**

| Dato | Regla |
|---|---|
| **Origen** (B-18) | `Compra` o `Donación`. La donación exige registrar **quién dona** y se acredita con su documento |
| **Costo**, fecha, proveedor (B-17) | Se registran con la factura. En el BM-1 las columnas de valor van en `S/P` (B-69: control interno), pero la relación de bienes nuevos **sí declara el monto**: en Bs, el de la compra (B-77) |
| **Garantía** (B-20) | Fecha de vencimiento con aviso anticipado |
| **Marca, modelo, serial** | Se capturan por separado (filtrar y buscar) y el sistema los **compone** dentro de la descripción al imprimir (`Inventario::descripcionOficial()`), como hacen los formatos reales |

### Documentos que genera el sistema

Todos HTML imprimible con el membrete institucional y correlativo vía
`ConfigSistema::generarNumeroOficio`. Un número anulado no se recicla.

| Documento | Regla | Estado del formato |
|---|---|---|
| **Relación de bienes nuevos** a la Alcaldía (`/inventario/relaciones`, `inventario_relaciones`, correlativo `bienes`) | Se hace **cada vez que entra un bien nuevo** (B-77). Ningún bien va en dos oficios; `inventario.id_relacion` indica qué ya se reportó y permite reimprimir el oficio tal como se envió. Anular libera los bienes | Construido fiel al **Oficio N° 179/2026** (`docs/formatos/oficio_relacion_bienes_nuevos_alcaldia_2026-06-10.jpg`), que es del procedimiento **anterior**: pide a la Alcaldía codificar y no trae columna de código. Viene un formato nuevo con código (B-81) |
| **Documento de donación** (hoja de vida del bien) | Declaración del donante (nombre, cédula, estado civil, domicilio), procedencia y por qué no hay factura, valor en Bs **en letras y números** + equivalente en USD, aceptación de la Presidenta, fecha en letras (`Util::montoALetras()`/`fechaEnLetras()`). El bloque del abogado visador se imprime solo si está configurado (B-83) | Construido sobre `docs/formatos/documento_donacion_bien_2026-02-18.jpg` |
| **Acta de Desincorporación** (por lote, RN-IN09) | Flujo y datos completos | Imprimible **provisional**: falta el formato |
| **Acta de conteo** (RN-IN11) | Diferencias entre lo registrado y lo hallado | — |
| **Etiquetas** con código + **QR** a la hoja de vida (B-14, B-15) | Solo bienes ya codificados: sin N° de orden no hay qué imprimir. Sirve para inventariar escaneando | — |

La resolución y gaceta de la Presidenta que imprimen estos y otros documentos (Resolución N° 32 y
Gaceta Municipal Extraordinaria N° 87, ambas del 05/09/2025) se tomaron de los documentos sellados y
el cliente las confirmó (B-82).

**No existen aún** (falta el formato): **acta de asignación** de bien a responsable, que firma el
empleado («acta de encargado», B-29) y **acta de incorporación**, una por cada bien que entra (B-84;
quizá el mismo documento que la de asignación).

**Hoja de vida** (B-36): ficha, foto, código con su BM-1 de procedencia, documentos, mantenimientos y
movimientos en una sola pantalla.

---

## RN-IN11 — Conteo por cambio de gestión

No es periódico: se dispara al **cambiar de coordinación o de presidencia** (B-48); también admite
«Auditoría» y «Otro». Lo hace el encargado de la Coordinación de Bienes.

Al abrirlo, el sistema **congela** lo que cree tener de cada bien no desincorporado (ubicación,
estatus, condición); luego se registra lo hallado físicamente y se comparan (B-50). Solo puede haber
**un conteo abierto** y no se cierra con bienes sin verificar.

**El conteo no corrige los bienes.** Produce el acta con las diferencias; la corrección se hace con
movimientos normales, que es lo que deja rastro de quién y cuándo la hizo.

---

## RN-IN12 — Suficiencia de bienes

No es stock mínimo de consumibles (no los llevan): es saber si **alcanzan** los bienes (B-63).

**Hoy** `inventario_dotacion` define unidades de cada categoría **por empleado**, y el reporte
compara, por departamento, lo que hay contra lo que debería haber. Solo se evalúan las categorías con
dotación definida; los bienes en depósito, desincorporados, extraviados o robados no cuentan.

**Criterio cerrado con el cliente (B-63):** la dotación es **por departamento** («cada departamento
tendrá una cantidad de bienes»; el aire es del espacio), no por empleado. La tabla y el reporte deben
rehacerse a *unidades por departamento y categoría* cuando lleguen las cifras.

---

## RN-IN13 — Reportes, alertas y permisos

**Reportes** (B-51, B-53): internos para la Presidencia, **sin formato obligatorio** (B-52). El
listado y el reporte de inventario cubren con filtros de estatus, origen, departamento y depósito:
activos, dañados, sin código, donaciones, por departamento y en almacén; además kardex, bienes
asignados y desincorporados (`Inventario::desincorporados()`, nunca la papelera).

**Alertas** (Centro de Alertas, días configurables):

| Alerta | Parámetro (por defecto) |
|---|---|
| Bienes sin codificar hace tiempo (B-12) | `dias_alerta_sin_codificar` (30) |
| Garantías por vencer (B-20) | `dias_aviso_garantia` (30) |
| Mantenimiento preventivo próximo (B-56) | `dias_aviso_mantenimiento` (15) |

**Permisos** (B-58):

| Quién | Puede |
|---|---|
| Rol con la casilla **«Bienes: registrar y modificar»** (`InventarioEscritura`; hoy el rol Inventario) · Administrador | Crear, editar, codificar, mover, desincorporar, emitir actas y relaciones, adjuntar documentos |
| Cualquier otro rol con acceso al módulo | **Solo consulta** |

La escritura es una **capacidad asignable** en *Roles y Permisos* (mig. 084), no una lista de roles
(`InventarioController::puedeEscribir()`). Sin ella las pantallas no dibujan los botones de escritura
y el servidor además rechaza la acción.

---

## Carga del inventario real

El archivo vigente de la encargada no ha llegado. La planilla entregada el 2026-10-06
(`docs/formatos/planilla_inventario_encargada_MUESTRA_antigua.docx`) es **antigua y solo muestra el
formato**: no se carga ni se concilia. Lo que enseña para el importador:

| Rasgo de la planilla | Tratamiento |
|---|---|
| Mismas columnas del BM-1 sin valores en Bs; *Observaciones* = condición física (`REGULAR`, `DAÑADAS`, `NUEVO`) | Mapear a `condicion`, incluidos plurales y femeninos |
| Bienes sin código como `S/C`, a veces con cantidad > 1 | Entran «En espera de codificación», **un registro por unidad** |
| Marca, modelo y serial rotulados dentro de la descripción | Extraer solo lo rotulado; el resto queda en la descripción |
| «DONADO» en la descripción | `origen = Donación` |
| **No trae departamento** | El archivo vigente debe traerlo: sin él no hay responsable derivado ni conteo por departamento |
| N° de orden repetidos | **Reportarlos**, no rechazar el archivo entero |

---

## Lo que NO hace el sistema (por decisión)

| | Por qué |
|---|---|
| Depreciación | No la necesitan; el bien dura hasta cambiar de estatus (B-54) |
| Pólizas de seguro | No se lleva ese control (B-55) |
| Vehículos | IMATUR no tiene (B-57) |
| Consumibles | No se controlan (B-07) |
| Generar el BM-1 | Es un documento entrante de la Alcaldía; una vista tipo BM-1 solo serviría como control interno |
| Oficio de retiro tras la desincorporación | Sale del alcance: el acta sellada es el aval (2026-09-02) |
| Asignar responsables a mano | Se derivan (RN-IN05) |

---

## Pendiente del cliente

Detalle y estado en `docs/BACKLOG.md` §3.4.

- **Inventario digital vigente** de la encargada, con el departamento de cada bien: carga de los ~142
  bienes.
- **B-73** (punto de partida) y **B-75** (catálogo de clasificaciones): encender la codificación
  propia. Ambos esperan la última codificación de la Alcaldía.
- **Formatos:** Acta de Desincorporación, acta de asignación, acta de incorporación (B-84) y relación
  de bienes nuevos con columna de código (B-81).
- **B-78-bis** (¿la Alcaldía sale del circuito?), **B-83** (abogado visador), cifras de dotación por
  departamento (B-63).
- **Antes de producción:** asignar el cargo de Coordinador en Compras, Bienes y Servicios; mientras
  esté vacante los movimientos están bloqueados por diseño (B-32).
