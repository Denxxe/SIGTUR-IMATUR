# Módulo de Bienes (Inventario) — Reglas de Negocio

**Última actualización:** 2026-08-05 · **Migraciones:** 062–068
**Fuentes:** levantamiento con el cliente (`PREGUNTAS_DESCUBRIMIENTO_Bienes_Rutas.md`, B-01…B-72) y el **Formulario BM-1** real (`docs/formatos/`).
**Plan y pendientes:** `docs/PLAN_MODULO_BIENES.md`.

> Este documento reemplaza por completo la versión de 2026-05-22, que describía
> el módulo como un CRUD de bienes y daba por buenas estructuras ya eliminadas
> (`ruta_inventario`, `taller_inventario`, Durable/Fungible).

---

## Contexto institucional

Los bienes de IMATUR los gestiona la **Coordinación de Compras, Bienes y
Servicios** (B-01), adscrita a la Dirección de Administración.

IMATUR **no es dueño del proceso de codificación**: los bienes pertenecen
patrimonialmente a la **Alcaldía del Municipio Sucre**, que es quien asigna el
código oficial, pega la etiqueta física y lleva el registro consolidado. El
sistema modela ese reparto de responsabilidades, no un inventario autónomo.

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

## RN-IN02 — El código oficial lo asigna la Alcaldía

Formato del **Formulario BM-1**:

```
GRUPO - SUB-GRUPO - SECCIÓN - N° DE ORDEN
  2   -    01     -   108   -     084
```

- Lo asigna el **Departamento de Bienes de la Alcaldía**, nunca IMATUR (B-11).
- El **N° de orden** es un registro que lleva la Alcaldía con criterio propio;
  garantiza que no se repita en ningún bien. IMATUR **desconoce la lógica** de
  esa numeración y solo la transcribe (B-72). El sistema **no genera ni
  predice** números: únicamente valida que no se repita dentro de IMATUR, como
  red contra errores de tecleo.
- En la BD el código vive **por partes** (`codigo_grupo`, `codigo_subgrupo`,
  `codigo_seccion`, `nro_orden`) y `codigo_bn` guarda el compuesto que arma
  `Inventario::componerCodigo()`.

> **El código NO clasifica.** En el BM-1 real, sillas, mesas, pizarra, aire
> acondicionado y router comparten todos `2-01-108`. Por eso el sistema tiene
> **dos ejes independientes**: el código oficial (para la Alcaldía) y la
> **categoría interna** (para los reportes de la Presidencia) — ver RN-IN04.

---

## RN-IN03 — Ciclo de codificación

Un bien **nace sin código** (B-12). El circuito tiene tres piezas y solo dos
son responsabilidad del sistema:

```
① REGISTRO INTERNO (el sistema)
   Alta con descripción, marca/modelo/serial, departamento, costo, factura,
   origen → estatus "En espera de codificación"
                    ↓
② INFORME A LA ALCALDÍA (el sistema)   ⏳ pendiente del formato real
   Lote de bienes nuevos para que vengan a inspeccionar
                    ↓
        Inspección física de la Alcaldía
                    ↓
③ BM-1 CONSOLIDADO (lo devuelve la Alcaldía)  ← documento ENTRANTE
   Trae grupo-subgrupo-sección + N° de orden por bien
                    ↓
   CONCILIACIÓN: se transcriben los códigos, se marca verificado y se
   archiva el formulario → estatus "Activo"
```

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

**Es un acto administrativo**, no un movimiento más (B-39): requiere acta
firmada por la **Coordinadora de Bienes y la Presidencia**, más un **oficio a
la Alcaldía** para que venga a retirar el bien. La Contraloría se entera por
ese mismo oficio (B-40).

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

**Costo:** se registra junto con la factura, pero es **control interno**. Para
la Alcaldía es irrelevante — por eso el BM-1 trae `S/P` en las columnas de
valor aun cuando IMATUR tenga el informe y la factura (B-69).

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
| Predecir el N° de orden | Es jurisdicción de la Alcaldía (B-72) |

---

## Pendientes

**Bloqueados por formatos del cliente:** informe de bienes nuevos a la Alcaldía
(el dolor #1), acta administrativa de baja + oficio de retiro, y acta de
asignación de bien a responsable.

**Preguntas abiertas:** B-71 (¿existe el BM-1 en digital? permitiría carga
automática de códigos).

**Operativo antes de producción:** cargar los ~142 bienes reales, crear las
ubicaciones y asignar el cargo de Coordinador en Compras, Bienes y Servicios
—mientras esté vacante, los movimientos están bloqueados por diseño.

Detalle completo en `docs/PLAN_MODULO_BIENES.md` §12.
