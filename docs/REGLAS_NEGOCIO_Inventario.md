# Módulo de Bienes (Inventario) — Reglas de Negocio

**Última actualización:** 2026-09-03 · **Migraciones:** 062–069
**⚠️ Cambio de procedimiento (2026-09-02):** IMATUR pasa a **asignar** el código de sus bienes y el acta de baja pasa a ser **Acta de Desincorporación** (sin oficio de retiro). Ver RN-IN02, RN-IN03 y RN-IN09 — **regla vigente, código aún no adaptado**.
**Fuentes:** levantamiento con el cliente (`PREGUNTAS_DESCUBRIMIENTO_Bienes_Rutas.md`, B-01…B-72) y el **Formulario BM-1** real (`docs/formatos/`).
**Plan y pendientes:** `docs/PLAN_MODULO_BIENES.md`.

> Este documento reemplaza por completo la versión de 2026-05-22, que describía
> el módulo como un CRUD de bienes y daba por buenas estructuras ya eliminadas
> (`ruta_inventario`, `taller_inventario`, Durable/Fungible).

---

## Contexto institucional

Los bienes de IMATUR los gestiona la **Coordinación de Compras, Bienes y
Servicios** (B-01), adscrita a la Dirección de Administración.

Los bienes pertenecen patrimonialmente a la **Alcaldía del Municipio Sucre**,
que lleva el registro consolidado. Hasta ahora IMATUR **no era dueño del
proceso de codificación** (la Alcaldía asignaba el código y pegaba la
etiqueta); desde la notificación del **2026-09-02**, IMATUR —como ente
autónomo— **asigna el código** y le pasa la relación a la Alcaldía, que
conserva la propiedad y el registro patrimonial. El sistema modela ese reparto
de responsabilidades, no un inventario autónomo.

**Escala:** ~142 bienes (B-04). El valor del módulo está en el control
documental y la trazabilidad, no en el volumen.

---

## RN-IN01 — Qué entra al inventario

Solo **bienes durables**: mobiliario y herramientas, cosas que permanecen con
el uso (B-06).

**No se llevan consumibles.** Papelería, insumos de limpieza y material
gastable quedan fuera del sistema: no se registran ni se controlan (B-07,
B-43, B-44). Por eso la distinción Durable/Fungible y la columna `cantidad`
—introducidas en la mig. 044— **se eliminaron** en la mig. 067.

**Registro individual.** Aunque se compren en lote (20 sillas iguales), cada
unidad es un registro con su propio código (B-09). No existe el concepto de
"cantidad" en un bien.

**Todo es de IMATUR.** No hay bienes en comodato ni préstamo de terceros
(B-08), así que no se modela la propiedad.

---

## RN-IN02 — El código oficial: de transcribirlo a asignarlo

> ### ⚠️ CAMBIO DE PROCEDIMIENTO (notificado por el cliente el 2026-09-02)
>
> **IMATUR pasa a asignar el código de sus propios bienes.** Como ente
> autónomo, después de la **última revisión y asignación de códigos de la
> Alcaldía** —que **todavía no se ha hecho**— IMATUR continúa la secuencia a
> partir del último código que la Alcaldía le deje. La Alcaldía **ya no viene a
> codificar**: solo recibe la **relación** de los bienes que entran nuevos
> (código asignado, monto y el resto de los datos del informe) para mantener su
> registro patrimonial.
>
> **Lo que NO cambia:** el formato del código, el registro individual, los dos
> ejes (código oficial + categoría interna), la propiedad patrimonial de la
> Alcaldía y todo el expediente por bien. La base del sistema sirve igual.
>
> **Estado: regla vigente, código NO adaptado todavía.** Hoy `Inventario::codificar()`
> transcribe el código contra un BM-1 recibido y `verificado_alcaldia` significa
> "la Alcaldía lo verificó". Lo que falta construir está en
> `docs/PLAN_MODULO_BIENES.md` §2-ter, y depende de dos insumos: el **punto de
> partida de la secuencia** (esa última revisión pendiente) y el **catálogo de
> grupos/subgrupos/secciones** (B-60, reabierta).

Formato del **Formulario BM-1** (sin cambios):

```
GRUPO - SUB-GRUPO - SECCIÓN - N° DE ORDEN
  2   -    01     -   108   -     084
```

- **Hasta la última revisión de la Alcaldía:** lo asigna el **Departamento de
  Bienes de la Alcaldía** y IMATUR solo lo transcribe (B-11). Los bienes
  históricos y el lote que espera esa revisión siguen esta regla.
- **Después de esa revisión:** lo asigna **IMATUR**, continuando la secuencia
  desde el último N° de orden entregado. La numeración deja de ser opaca (B-72
  queda superada para los bienes nuevos): el sistema debe **proponer** el
  siguiente y garantizar que no se repita.
- En la BD el código vive **por partes** (`codigo_grupo`, `codigo_subgrupo`,
  `codigo_seccion`, `nro_orden`) y `codigo_bn` guarda el compuesto que arma
  `Inventario::componerCodigo()`.

> **El código NO clasifica.** En el BM-1 real, sillas, mesas, pizarra, aire
> acondicionado y router comparten todos `2-01-108`. Por eso el sistema tiene
> **dos ejes independientes**: el código oficial (para la Alcaldía) y la
> **categoría interna** (para los reportes de la Presidencia) — ver RN-IN04.

---

## RN-IN03 — Ciclo de codificación

### Ciclo nuevo (a partir de la notificación del 2026-09-02)

El circuito pierde la espera: la codificación deja de depender de una visita.

```
① REGISTRO INTERNO (el sistema)
   Alta con descripción, marca/modelo/serial, departamento, costo, factura,
   origen
                    ↓
② CODIFICACIÓN INTERNA (el sistema)   ⏳ por construir
   IMATUR asigna grupo-subgrupo-sección + el siguiente N° de orden de su
   secuencia → estatus "Activo". Ya no se espera a nadie
                    ↓
③ RELACIÓN A LA ALCALDÍA (el sistema)   ⏳ pendiente del formato nuevo
   Lote de bienes nuevos YA codificados, con su monto, para que la Alcaldía
   mantenga su registro patrimonial. Es informativa: no pide inspección
```

La **conciliación del BM-1 se conserva** (`inventario_consolidados_bm1`) por dos
razones: la última revisión de la Alcaldía todavía está pendiente y hay que
poder cargar los códigos que traiga, y el histórico ya recibido debe quedar
trazable.

### Ciclo anterior (vigente hasta esa última revisión)

Un bien **nace sin código** (B-12). Tres piezas, dos del sistema:

```
① REGISTRO INTERNO → estatus "En espera de codificación"
                    ↓
② INFORME A LA ALCALDÍA: lote de bienes nuevos para que inspeccionen
                    ↓
        Inspección física de la Alcaldía
                    ↓
③ BM-1 CONSOLIDADO (documento ENTRANTE): trae el código por bien
                    ↓
   CONCILIACIÓN: se transcriben los códigos, se marca verificado y se
   archiva el formulario → estatus "Activo"
```

**Esto es lo que hay implementado hoy** (`/inventario/consolidados`).

**El BM-1 no lo genera el sistema**: es el registro consolidado que la Alcaldía
elabora y devuelve. Se registra cada recepción (`inventario_consolidados_bm1`),
se adjunta el escaneado y desde ahí se codifican los bienes, quedando la
trazabilidad de en qué formulario vino cada código.

**Frecuencia:** es un **evento puntual**. No hay periodicidad conocida; la
Alcaldía verifica y devuelve el BM-1 cuando corresponde (B-70).

---

## RN-IN04 — Estatus vs condición: dos ejes distintos

Mezclarlos fue el origen del bug H-04. Son independientes:

**`condicion`** — estado *físico*: `Nuevo`, `Bueno`, `Regular`, `Dañado`.

**`estatus`** — situación *administrativa*:

| Estatus | Significado | ¿Inventario activo? |
|---|---|---|
| En espera de codificación | Registrado, sin N° de orden | Sí, con distintivo |
| Activo | Operativo y disponible | Sí |
| En mantenimiento | Fuera de servicio temporalmente | **Sí**, marcado como no disponible |
| Extraviado | Pérdida en averiguación | Sí, marcado |
| Robado | Con denuncia | Sí, marcado |
| Dado de baja | Desincorporado | **No** |

Criterio del cliente: el bien **en mantenimiento no desaparece** del inventario
—solo deja de estar disponible— porque hay que seguir teniendo en cuenta su
código (B-34); el **dado de baja sí sale** del inventario activo, conservando
su registro y el oficio como aval (B-38).

**Clasificación interna** (`categorias`): 11 categorías propias para los
reportes de la Presidencia, independientes del código de la Alcaldía (B-22).

---

## RN-IN05 — Responsable: automático, nunca manual

El responsable de un bien **no se elige**: se deduce (B-68).

```
bien → ubicación → departamento → Director (o, en su defecto, Coordinador)
```

- Un bien tiene **un solo** responsable (B-27).
- Si entra alguien nuevo en ese cargo, pasa a ser responsable de **todos** los
  bienes de su departamento, automáticamente.
- Cuando un trabajador se va, los bienes **no lo siguen**: quedan en el
  departamento y el nuevo titular del cargo los asume (B-28, B-30).
- Los bienes en **depósito** no pertenecen a ningún departamento: su custodio
  es la jefatura de la Coordinación de Bienes.

Por eso `inventario.id_responsable` **se eliminó** (mig. 066) y el dato se
deriva en cada consulta: una columna almacenada quedaría desactualizada al
cambiar un cargo, egresar un empleado o trasladar un bien. El histórico se
conserva en `actividad_inventario`, que guarda el responsable de cada
movimiento en su momento.

---

## RN-IN06 — Ubicaciones, sedes y depósito

- La ubicación de un bien se define **por departamento** (B-23, B-25).
- **Dos sedes**: la principal y la **Oficina de Información Turística del
  Aeropuerto de Cumaná**, que tiene bienes bajo control propio (B-24). Desde la
  mig. 067 esa oficina es un **departamento más**, con su propio coordinador
  —y por tanto su propio responsable (B-65). Cuelga de la **Dirección de
  Planificación y Gestión Turística** (mig. 068).
- El **depósito** es el área común de los bienes sin asignar. Todo bien que no
  esté asignado a un departamento debe estar ahí.

---

## RN-IN07 — Movimientos

Tres tipos de traslado según el cliente (B-31): depósito→departamento,
departamento→depósito y departamento→departamento. El sistema los modela con
**un solo tipo `Traslado` + origen/destino**: el caso concreto se deduce de las
ubicaciones.

| Movimiento | Efecto sobre el bien |
|---|---|
| Traslado | Cambia la ubicación (y con ella, el responsable) |
| Salida a mantenimiento | Estatus → En mantenimiento; abre el proceso |
| Retorno de mantenimiento | Estatus → Activo; cierra el proceso |
| Baja | Estatus → Dado de baja |

**Toda operación la autoriza la Coordinación de Bienes** (B-32). El sistema la
identifica por **cargo + departamento** (B-64, configurable). Si el puesto está
vacante, **los movimientos quedan bloqueados**: es preferible detener la
operación a registrar movimientos sin autorizar.

Movimiento y efecto son **transaccionales**: ocurren juntos o no ocurren.

**Reglas de bloqueo:** un bien dado de baja no admite movimientos; uno sin
codificar tampoco; no se traslada al mismo sitio; no hay doble salida a
mantenimiento ni retorno sin mantenimiento abierto.

---

## RN-IN08 — Mantenimiento

**Correctivo** (B-33): lo ejecuta **Servicios Generales** o un taller externo.
Se registra el proceso completo —encargado, falla, trabajo realizado, costo y
resultado (Reparado / Sin reparación / Irrecuperable)—, no solo la salida. Un
bien no puede tener dos mantenimientos abiertos a la vez.

Si el resultado es **Irrecuperable**, el bien vuelve a Activo con condición
Dañado, a la espera del acto de baja.

**Preventivo** (B-56): calendario por bien (frecuencia + próxima fecha) para
aires, impresoras y computadoras. Al retornar de un mantenimiento, la próxima
fecha **avanza sola**.

---

## RN-IN09 — Baja y desincorporación

**Motivos** (B-37): robo, deterioro, pérdida.

**Es un acto administrativo**, no un movimiento más (B-39): requiere el
**Acta de Desincorporación** firmada por la **Coordinadora de Bienes y la
Presidencia**.

> ### ⚠️ CAMBIO (cliente, 2026-09-02)
>
> 1. El documento se llama **Acta de Desincorporación** y **así debe aparecer
>    en el sistema** — no "acta de baja".
> 2. **Ya no hay oficio de retiro.** El acta lista **todos los bienes que se
>    van a retirar**; la Alcaldía la **firma y sella**, y ese acta sellada es el
>    **aval** de que los bienes fueron desincorporados y retirados. Un solo
>    documento en lugar de dos.
> 3. Por tanto el acta es **por lote**, no por bien, y al registrarla firmada se
>    marcan retirados todos los bienes que contiene.
>
> **Estado: regla vigente, código NO adaptado.** Hoy `Inventario::marcarRetirado()`
> confirma el retiro **bien por bien** y no existe la entidad "acta". Ver
> `docs/PLAN_MODULO_BIENES.md` §2-ter.

En caso de **robo o pérdida** se suma la **denuncia** y la averiguación
administrativa (B-41).

**Después de la baja** (B-67): el bien sale del inventario activo pero sigue
físicamente en IMATUR hasta que la Alcaldía lo retire. Se distingue con una
marca:

- **Dado de baja · Por retirar** — desincorporado, todavía en las instalaciones
- **Dado de baja · Retirado** — la Alcaldía ya se lo llevó (con su fecha)

---

## RN-IN10 — Documentos de respaldo

Cada bien acumula su expediente (B-16 a B-19): factura o documento de
adquisición, informe de la Alcaldía, oficio de donación, acta de asignación,
acta de baja, denuncia y garantía.

Los archivos viven **fuera del web root** (`storage/uploads/bienes/`) y se
sirven por id de registro con control de rol, igual que los recaudos de RRHH.

**Origen del bien** (B-18): `Compra` o `Donación`. La donación exige registrar
**quién dona** y se acredita con su oficio.

**Costo:** se registra junto con la factura. Era **control interno** (B-69: por
eso el BM-1 trae `S/P` en las columnas de valor), pero con el procedimiento
nuevo el **monto sí se declara** en la relación de bienes nuevos que IMATUR le
envía a la Alcaldía (cliente, 2026-09-02). El dato ya se captura; lo que falta
es el documento que lo publique.

**Garantía** (B-20): se lleva control interno con su fecha de vencimiento y
aviso anticipado.

---

## RN-IN11 — Conteo por cambio de gestión

No es un inventario periódico: se dispara al **cambiar de coordinador o de
presidencia** (B-48). Lo hace el encargado de la Coordinación de Bienes.

Al abrirlo, el sistema **congela** lo que cree tener de cada bien (ubicación,
estatus, condición); luego se registra lo hallado físicamente y se comparan
(B-50). Solo puede haber un conteo abierto y no se cierra con bienes sin
verificar.

**El conteo no corrige los bienes.** Produce el acta con las diferencias; la
corrección se hace con movimientos normales, que es lo que deja rastro de quién
y cuándo la hizo.

---

## RN-IN12 — Suficiencia de bienes

No es stock mínimo de consumibles (no los llevan). Es saber si **alcanzan** los
bienes, medido **por el número de empleados** de cada departamento (B-63).

`inventario_dotacion` define cuántas unidades de cada categoría corresponden
por empleado; el reporte compara lo que hay contra lo que debería haber. Solo
se evalúan las categorías con dotación definida: las que no se reparten por
persona (herramientas, material turístico, bienes culturales) quedan fuera a
propósito. Los bienes en depósito no cuentan, porque no están en uso.

---

## RN-IN13 — Reportes y permisos

**Reportes** (B-51): todos internos para la Presidencia, **sin formato
obligatorio** (B-52). El reporte de inventario cubre las listas pedidas
mediante filtros de estatus, origen, departamento y depósito: activos, dañados,
sin código, donaciones, por departamento y en almacén.

**Permisos** (B-58):

| Quién | Puede |
|---|---|
| Coordinación de Bienes · Administrador | Crear, editar, mover, dar de baja |
| Cualquier otro rol con acceso al módulo | **Solo lectura** |

El RBAC del sistema es por controlador, no por acción, así que la distinción
lectura/escritura se resuelve dentro del módulo
(`InventarioController::puedeEscribir()`).

**Etiquetas** (B-14, B-15): la Alcaldía pega la suya en la inspección; el
sistema genera una propia con el código y un **QR** que abre la hoja de vida
del bien, para inventariar escaneando.

**Hoja de vida** (B-36): ficha, foto, código con su BM-1 de procedencia,
documentos, mantenimientos y movimientos en una sola pantalla.

---

## Lo que NO hace el sistema (por decisión)

| | Por qué |
|---|---|
| Depreciación | No la necesitan; el bien dura lo que dure hasta cambiar de estatus (B-54) |
| Pólizas de seguro | No se lleva ese control (B-55) |
| Vehículos | IMATUR no tiene (B-57) |
| Consumibles | No se controlan (B-07) |
| Generar el BM-1 | Es un documento entrante: lo hace la Alcaldía |
| Asignar responsables a mano | Se derivan (RN-IN05) |
| ~~Predecir el N° de orden~~ | **Ya no aplica (2026-09-02):** con el procedimiento nuevo el sistema **sí** propone el siguiente de la secuencia (RN-IN02) |

---

## Pendientes

**Bloqueados por formatos del cliente** — el cliente los enviará *cuando tenga
los formatos nuevos* (2026-09-02), porque el procedimiento cambió:

- **Informe/relación de bienes nuevos** a la Alcaldía (el dolor #1) — ahora
  lleva el **código que IMATUR asigna** y el **monto**.
- **Acta de Desincorporación** (por lote, la Alcaldía firma y sella = aval).
  **El oficio de retiro se elimina del alcance.**
- **Acta de asignación** de bien a responsable ("acta de encargado") — sigue
  vigente.
- **Oficio de donación** — sigue vigente.

**Por construir tras el cambio de procedimiento** (ver `PLAN_MODULO_BIENES.md`
§2-ter): codificación interna con secuencia propia y acta de desincorporación
por lote.

**Preguntas abiertas:** ✅ B-71 respondida (2026-09-02): **sí existe versión
digital** de los documentos, e incluso de un **inventario interno que la
encargada lleva aparte** — hay que pedir esos archivos (habilitan carga masiva
de los ~142 bienes y el punto de partida de la secuencia). Nuevas: **B-73…B-80**
en `PLAN_MODULO_BIENES.md` §2-ter.

**Operativo antes de producción:** cargar los ~142 bienes reales y asignar el cargo
de Coordinador en Compras, Bienes y Servicios —mientras esté vacante, los movimientos
están bloqueados por diseño (B-32).

> Las **ubicaciones ya no faltan**: la mig. 069 sembró 25 (una por departamento activo,
> más el Depósito General), con su `sede` asignada —la Oficina del Aeropuerto en
> *Aeropuerto de Cumaná*, el resto en *Sede Principal*. Antes la tabla estaba vacía y
> `InventarioController::store()` exige `id_ubicacion > 0`, así que **era imposible
> registrar un bien**: las cuatro fases construidas no se podían usar. Los nombres
> arrancan iguales a los del departamento; el cliente los renombra a su referencia real
> (planta, mezzanina, cubículo) y puede crear varias por departamento.

Detalle completo en `docs/PLAN_MODULO_BIENES.md` §12.
