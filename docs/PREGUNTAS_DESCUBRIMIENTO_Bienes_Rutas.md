# Levantamiento de requerimientos — Bienes (Inventario) y Rutas Turísticas

**Fecha:** 2026-08-04 · **Para:** IMATUR — Dirección de Administración y Dirección de Planificación y Gestión Turística

---

## Cómo usar este documento

Este cuestionario está redactado **desde cero, como si el sistema no existiera todavía**. Es deliberado: si preguntamos "¿está bien como lo hicimos?", la respuesta casi siempre es "sí", y así no se detectan las diferencias entre lo que construimos y cómo trabaja realmente el instituto.

Pedimos que las respuestas describan **cómo se hace hoy** (en papel, en Excel o como sea), no cómo creen que debería hacerlo un sistema.

**Marcas de prioridad**

| Marca | Significado |
|-------|-------------|
| ⭐ | **Crítica.** Define la estructura de la base de datos. Cambiarla después cuesta mucho. |
| ▲ | **Importante.** Afecta pantallas y reportes. |
| ○ | **Complementaria.** Mejora el módulo; puede quedar para una segunda entrega. |
| 💡 | **Exploratoria.** Propuesta nuestra: algo que el sistema podría hacer y quizá no se ha considerado. |

**Sugerencia de trabajo:** dos sesiones de ~1 hora, una por módulo, con la persona que **hace** el trabajo a diario (no solo quien lo supervisa). Las respuestas de quien opera suelen revelar excepciones que el manual no recoge.

**Muy importante:** al final hay una lista de **documentos y formatos físicos** que necesitamos. Un formato real vale más que diez respuestas — con la planilla en la mano no hay que adivinar.

---
---

# PARTE 1 — BIENES / INVENTARIO

> ## ✅ RESPONDIDO POR EL CLIENTE — 2026-08-04
> Las 59 preguntas de esta parte están contestadas (respuestas en la 4.ª columna de cada tabla).
>
> El análisis y el plan de construcción derivado están en **`docs/PLAN_MODULO_BIENES.md`**.
>
> **Quedaron 9 preguntas abiertas o nuevas (B-60 a B-68)** — ver §9 de ese plan. Las dos
> que bloquean el arranque son el **catálogo oficial de grupos/subgrupos/secciones de la
> Alcaldía** y **tres ejemplos reales de código BN**.

## A. Panorama general

| # | | Pregunta |
|---|---|----------|
| B-01 | ⭐ | ¿Quién es hoy **el responsable** del inventario de IMATUR? ¿Una persona, una coordinación, o cada dirección lleva el suyo? | una cordinacion, se llama Coordinacion de compras, bienes y servicios.
| B-02 | ⭐ | ¿Cómo se lleva el inventario **hoy**? (cuaderno, Excel, formato de la Alcaldía, sistema de la Contraloría, nada) | formato de la alcaldía, ya viene un formato.
| B-03 | ⭐ | ¿A quién hay que **rendirle cuentas** del inventario y cada cuánto? (Contraloría Municipal, Alcaldía, auditoría interna) ¿En qué formato lo exigen? | se le lleva la cuenta a la alcaldia, caundo se abquiere un nuevo equipo, se le debe de hacer un oficio a la alcaldía para que ellos vengan a codificar el nuevo bien.
| B-04 | ▲ | ¿Cuántos bienes tiene IMATUR aproximadamente? (decenas, cientos, miles) Esto define si hace falta lector de código de barras o alcanza con búsqueda manual. |  regular para ser una institucion pública. aproximandamente unos 142 bienes que posee imatur actualmente. cuando se compra o se adquiere un nuevo equipo esto aumenta.
| B-05 | ▲ | ¿Qué es lo que **más problemas** les da hoy con el inventario? (bienes que no aparecen, no saber quién los tiene, el conteo anual, los reportes) | por el momento realizar el oficio al momento de recibir un nuevo inventario, y tambien hacer el cambio de gestion que se tiene que hacer una auditoria de todo los bienes turistico y verificar que esos bienes esten en condiciones y excatamente en que lugar está...

## B. Qué se considera un bien

| # | | Pregunta |
|---|---|----------|
| B-06 | ⭐ | ¿Qué cosas entran en el inventario? ¿Solo equipos y mobiliario, o también material de oficina, insumos de limpieza, uniformes, herramientas? | entran solo mobiliario y herrameintas (cosas que permanezcan con el uso).
| B-07 | ⭐ | ¿Distinguen entre bienes **inventariables** (que duran años y tienen código) y **consumibles** (que se gastan: resmas, marcadores, café)? ¿Se llevan en el mismo registro o por separado? | solo los que tiene codigo y duran años... los demas se descarta o no se lleva la cuenta.
| B-08 | ▲ | ¿Hay bienes que **no son de IMATUR** pero están en sus instalaciones? (comodato, préstamo de la Alcaldía, de un ente externo) ¿Hay que diferenciarlos? | todo lo que hay es de IMATUR
| B-09 | ○ | ¿Existen bienes que se compran **en lote** y se registran juntos (ej. 20 sillas iguales)? ¿Cada silla lleva su código o el lote completo lleva uno? | se registra individual asi se compre en lote. cada uno lleva su codigo.

## C. Identificación del bien

| # | | Pregunta |
|---|---|----------|
| B-10 | ⭐ | ¿Cómo se identifica un bien de forma única? ¿Cuál es el código que manda? | Se identifica por la ciodificaion que hace la alcaldía, para llevar el registro. (numero de orden)
| B-11 | ⭐ | El **código de Bien Nacional (BN)**: ¿quién lo asigna, la Contraloría o IMATUR? ¿Qué formato tiene? *(pedir 3 ejemplos reales)* | directamente la alcaldía (departamento de bienes de la alcaldía), el formato se lleva de la siguiente manera: grupo-subgrupo-sección-cantidad-N° de orden... es la completacion del codigo. segun el fromato dado.
| B-12 | ⭐ | ¿Puede existir un bien **sin** código BN? ¿Cuánto tiempo puede pasar así? ¿Qué se hace mientras tanto? | en el momento de tener en el inventario se coloca, pero se hace el oficio para enviarlo a la alcaldía y la lacaldía viene hacer la inspeccion para veriicar el inventario y asignar el N° de orden. Por el moemnto se regitra en el sistema (todo sobre el bien, menos el N° de orden y si fue verificado por la alccaldía).
| B-13 | ▲ | Además del código BN, ¿registran el **serial del fabricante**? ¿Es obligatorio? |No solo con el codigo se lleva el control. 
| B-14 | ▲ | ¿Los bienes llevan **etiqueta física** pegada? ¿Cómo la hacen hoy? | La alcaldía pegan la etiqueta fisica al hacer la inspección (se podria generar una vez asignada). 
| B-15 | 💡 | ¿Les serviría que el sistema **imprima las etiquetas** con el código y un código QR, para inventariar después escaneando con el teléfono? | si con la etiqueta y el QR para llevar el control del inventario de ese bien.

## D. Datos que se registran

| # | | Pregunta |
|---|---|----------|
| B-16 | ⭐ | Enumere **todo** lo que anotan de un bien al registrarlo. *(Comparamos contra lo que tenemos.)* |La descripccion del inmueble, asignacion (departaemnto),y el precio de caunto está el bien, se le anexa la factura y el informe de la alcaldía... En espera de codificacion por la alcaldía una vez hecha el oficio y la revision.
| B-17 | ⭐ | ¿Registran el **costo de adquisición**, la **fecha de compra** y el **proveedor**? ¿La Contraloría se lo exige? | si se registra esto en conjunto con la factura...
| B-18 | ▲ | ¿Registran de dónde vino el bien? (compra, donación, transferencia de la Alcaldía, incautación) | si, si es donacion se aplica un oficio para hacer valido la donacion, descricion de inmueble a donar y la persona que lo dona a imatur, para llevar un control de los bienes donados (se le abjunta), al bien para llevar registro de todo esto, la codificacion de la lacladía es tal cual con la diferencia que el bien es donado y no comprado.
| B-19 | ▲ | ¿Guardan la **factura o el documento de adquisición**? ¿Les serviría adjuntarlo digitalizado al bien? | si abjuntado.
| B-20 | ▲ | ¿Registran **garantía** y su fecha de vencimiento? | se lleva el control interno de las garantias y su fecha de vencimiento.
| B-21 | 💡 | ¿Les serviría poder adjuntar **fotos del bien** (estado al recibirlo, daños)? Ayuda mucho en reclamos y en el conteo anual. | si para los expedientes de administracion y conteo anual.

## E. Clasificación y ubicación

| # | | Pregunta |
|---|---|----------|
| B-22 | ⭐ | ¿En qué **categorías** clasifican los bienes? *(pedir la lista completa que usan hoy)* | no lo hay, solo por descripcion y parece que todos llevan como inmoviliario, por el moemnto no se diferencia (se deberia diferenciar, como equipo tecnologico a las computadoras, inmueble como las mesas y sillas). colocalo, investiga los tipos de inventario... para tener idea.
| B-23 | ⭐ | ¿Cómo definen la **ubicación** de un bien? ¿Por oficina, por piso, por departamento? ¿Puede un bien estar "en tránsito"? | por departamento, si los que pertenece en deposito si está en transacion, de posito tambien tiene sus bienes y bien no asignado a algun departaemnto tiene que erstar en deposito.
| B-24 | ▲ | ¿Las ubicaciones son solo de la sede principal o hay otras sedes/depósitos? | solo esta sede principal y la sede del aeropuerto si se lleva el control de esos bienes (oficina de informacion turistica) se enceuntra en el aeropuerto de cumana y si se lleva registro de los bienes en esa sede...
| B-25 | ▲ | ¿La ubicación pertenece a un **departamento**, o son cosas independientes? (una sala de reuniones que usan todos) | la ubicacion pertenece al departaemnto, deposito es el area comun de los bienes sin asignar...

## F. Responsable del bien

| # | | Pregunta |
|---|---|----------|
| B-26 | ⭐ | ¿Cada bien tiene un **empleado responsable** con nombre y apellido, o la responsabilidad es del departamento? | director del departaemnto o en su defecto el coordinador de cada bien en su departamento.
| B-27 | ⭐ | ¿Un mismo bien puede tener **varios responsables** a la vez? (una impresora que usa toda una oficina) | No, solo un responsable como el anterior pregunta.
| B-28 | ⭐ | Cuando un empleado **se va de IMATUR**, ¿qué pasa con los bienes a su cargo? ¿Hay una solvencia o acta de entrega que deba firmar? | se reasigna a la nueva persona responsable del bien dependiendo del departaemnto y cargo asignado.
| B-29 | ▲ | Al entregar un bien a un empleado, ¿se firma algún **documento**? ¿Cuál? *(pedir el formato)* | si se produce un oficio el caul se tiene que firmar (empleado).
| B-30 | 💡 | ¿Le sería útil que, al procesar el egreso de un trabajador, el sistema **avise automáticamente** de los bienes que tiene asignados y no ha devuelto? | no solo se hace el oficio a la nueva persona responsable que se quedara en el departaemnto con los bienes, los bienes quedan al departamento.

## G. Movimientos

| # | | Pregunta |
|---|---|----------|
| B-31 | ⭐ | ¿Qué tipos de movimiento sufre un bien a lo largo de su vida? Enumérelos con sus nombres reales. | 1- de deposito a departaemnto. 2- de departamento a deposito. 3- de departaemnto a departamento.
| B-32 | ▲ | ¿Un movimiento requiere **autorización** de alguien antes de ejecutarse, o se registra y ya? | la tiene que autorizar por la coordinadora de bienes.
| B-33 | ▲ | Cuando un bien se manda a **reparación**: ¿se registra a dónde va, quién lo repara y cuánto costó? ¿Se lleva control de si volvió? | mantenimiento (hay servicios generales que se encarga de mantenimiento de bienes). ellos llevan un proceso para "reparar" el bien. si se tiene registro sobre este proceso, quien lo hace (el encargado por lo general es el coordinador de esta area).
| B-34 | ⭐ | Cuando un bien sale a **mantenimiento o reparación**, ¿debe dejar de aparecer como disponible? | si debe de dejar de estar disponible solo que sale el estatus de en mantenimiento debe de estar y una vez hecho el mantiniento debe de estar disponible otra vez, pero no desaparece del inventario ya que se debe de tener en ceunta (codigo y todo del bien), solo es transsion de estatus del bien.
| B-35 | ▲ | ¿Los bienes salen prestados para **rutas turísticas o talleres**? ¿Se registra la salida y el regreso? ¿Quién responde si no vuelve? |si es posible, son los bienes asignado al departamento asignado a estos bienes, y responde la coordinadora o direccion de ese deaprtamento (es posible pero no siempre pasa que se necesite.).
| B-36 | 💡 | ¿Le serviría un **historial completo por bien** ("hoja de vida"): desde que se compró, cada movimiento, cada reparación, hasta la baja? | si estaria bien un historial por cada bien, de los movimeintos, desde que se compro, hasta que se daño y los traslado, movimeinto , persona encargada del bien, fecha... etc.

## H. Bajas y desincorporación

| # | | Pregunta |
|---|---|----------|
| B-37 | ⭐ | ¿Por qué motivos se da de baja un bien? (deterioro, obsolescencia, pérdida, robo, transferencia a otro ente) | robo, por deterioro, pérdida.
| B-38 | ⭐ | **Pregunta clave:** cuando un bien se da de baja, ¿debe **desaparecer** del inventario activo, o seguir apareciendo marcado como "dado de baja"? | un bien debe de desaparecer, pero queda el aval del oficio que fue desincorporado, para tener esos bienes desincorporados, no debe de salir en el inventario activo de IMATUR.
| B-39 | ⭐ | ¿La baja requiere un **acto administrativo, acta o resolución**? ¿Quién lo firma? *(pedir el formato)* | un acto administrativo, firmado por la coordinadora de bienes, (responsable de coordinacion) y presidencia, y se le hace un oficio a la alcaldía pra que vengan a retirarlo y hacer su proceso administrativo.
| B-40 | ▲ | ¿Interviene la Contraloría Municipal para autorizar una baja? ¿Cómo se le notifica? | mediante el oficio generado por la dad de baja.
| B-41 | ▲ | En caso de **robo o pérdida**, ¿hay un procedimiento distinto? (denuncia, averiguación administrativa) | se realiza la denuncia y la averiguacion administrativa en caso de estos casos. 
| B-42 | ○ | ¿Los bienes dados de baja se descartan físicamente, se rematan, o se guardan en un depósito? | se descartan fisicamente o se los viene a returar por la alcaldía... (se guardan hasta que la alcaldía venga a retirarlos). pero no está definido aun, solo los listados de los bienes dado de alta, dañados o proceso activos...

## I. Consumibles

> Solo si en B-07 respondieron que también controlan material gastable.

| # | | Pregunta |
|---|---|----------|
| B-43 | ▲ | ¿Cómo controlan hoy la papelería y los insumos? ¿Quién los entrega? | no llevan esto
| B-44 | ▲ | ¿Llevan control de **cuánto queda** de cada insumo? | no llevan insumos gastables
| B-45 | ⭐ | ¿Existe un **mínimo** por debajo del cual hay que reponer? ¿Cuáles son los ítems críticos y con qué umbral? | no se mantiene, pero el umbral podria estar para los inmuebles para saber si hay poco de los inmuebles (sillas para la cantidad de empleados, mesas para los departamentos y así...).
| B-46 | ▲ | Al entregar consumibles, ¿se registra a quién y cuánto? ¿O solo se descuenta del total? | no.
| B-47 | 💡 | ¿Le serviría que el sistema **avise cuando un insumo está por agotarse**, y un reporte de consumo mensual para planificar las compras? | cuando queda poco de los inmuebles.

## J. Conteo físico y reportes

| # | | Pregunta |
|---|---|----------|
| B-48 | ⭐ | ¿Cada cuánto hacen **inventario físico** (conteo real, bien por bien)? ¿Quién lo hace? | caundo se recibe la coordinancion (cambio de coordinador), y cambio de presidencia. lo hace el encargado de la coordinancion de bienes...
| B-49 | ▲ | Durante el conteo, ¿cómo registran las diferencias entre lo que dice el papel y lo que aparece? | se va por lo fisico, se lleva la informacion de las diferencias en un papel para marcar el estado actual del conteo.
| B-50 | 💡 | ¿Le serviría un **modo conteo** en el sistema: imprime la lista, marca lo encontrado, y al final le muestra qué faltó y qué apareció de más? | es indifente ya que al momento del conteo, ya se tiene la lista del inventario y para movimientos de bienes se tiene que notificar, así que deberia estar el conteo completo, loq ue se hace en el conteo es verificar el estatus, el lugar y la cantidad de los bienes.
| B-51 | ⭐ | ¿Qué **reportes de inventario** debe entregar y a quién? *(pedir un ejemplo de cada uno)* | los reportes de inventario deberia de entregarlo la alcaldia a IMatur, a la presidenta se le entregan los reportes de estados de los inventarios, los bienes dañados, dados de alta, los activos, los bienes por departamentos, los activos nuevos sin codigo, los de donaciones, generales, y loq ue esta en almacen etc, de forma interna para la presidenta y llevar control del inventario interno en reportes...
| B-52 | ▲ | ¿Esos reportes tienen un **formato obligatorio** de la Contraloría o la Alcaldía? | no, ya que los reportes son internos para la presidencia y llevar control de los bienes, no necesariamente algun formato explicito.
| B-53 | ○ | ¿Necesitan reportes por departamento, para que cada director vea lo que tiene a cargo? | ya esta en la parte de reportes, si, que se pueda filtrar por departaemnto y que departamento tiene bienes asu cargo y en que estado, codigo y todo lo general que se necesita.

## K. Exploratorias — Bienes

| # | | Pregunta |
|---|---|----------|
| B-54 | 💡 | ¿Necesitan calcular **depreciación** de los bienes, o eso lo lleva Contabilidad aparte? | no es necesario, que el bien dure lo que tenga que durar, hasta que se cambie el status, 
| B-55 | 💡 | ¿Hay bienes **asegurados**? ¿Haría falta llevar control de las pólizas y sus vencimientos? | no se lleva ese control, si se roba, pierde o daña algun bien, se queda en ese estatus, solo los procesos aplicados anteriormente,  como las garantias y las denuncias de robo...
| B-56 | 💡 | ¿Hay equipos con **mantenimiento preventivo programado** (aires, computadoras)? ¿Les serviría que el sistema avise cuándo toca? |si seria bueno que avisara los manteminetos preventivos de esos equipos (aires acondicionados, impresora, computadoras).
| B-57 | 💡 | ¿IMATUR tiene **vehículos**? Suelen necesitar control aparte (combustible, kilometraje, seguro, conductor asignado). ¿Entran en este módulo o se manejan distinto? | no posee vehiculos asignados a este ente... no se espera algo por el estilo.
| B-58 | 💡 | ¿Quién debería poder **ver** el inventario y quién **modificarlo**? ¿Debería un director ver solo los bienes de su dirección? | solo los encargados de este modulo y el administrador (presindeta, encargado del sistema con los permisos pertinente), solo los coordinadores podrian editar los bienes, y administracion solo los podria visualizar (se debe de hacer la esquematizacion con los roles y usuarios del sistema pertinente).
| B-59 | 💡 | Si pudiera pedir **una sola cosa** que le resuelva el mayor dolor de cabeza con los bienes, ¿cuál sería? | lo que ya veniamos hablando, los reportes y los estatus de los bienes.

---
---

# PARTE 2 — RUTAS TURÍSTICAS

> ## ⏳ PENDIENTE DE RESPONDER
> Sin anotaciones del cliente al 2026-08-04. La pregunta que más conviene resolver
> primero es **R-07/R-08** (¿una ruta es un catálogo reutilizable o cada salida es un
> registro independiente?): de su respuesta depende si el módulo necesita rediseño.

## A. Panorama general

| # | | Pregunta |
|---|---|----------|
| R-01 | ⭐ | ¿Qué es exactamente una "ruta turística" para IMATUR? Descríbala como se la explicaría a alguien que nunca la ha visto. |
| R-02 | ⭐ | ¿Cuáles son **todas** las rutas o programas que ofrecen hoy? *(nombres oficiales, sin abreviar)* |
| R-03 | ⭐ | ¿Cómo se organiza y registra una ruta **hoy**? (oficios en papel, Excel, WhatsApp, nada) |
| R-04 | ▲ | ¿Con qué frecuencia se ejecutan? (semanal, mensual, por temporada, solo cuando lo piden) |
| R-05 | ▲ | ¿Cuántas personas participan típicamente en una ruta? ¿Y cuál es el máximo que pueden atender? |
| R-06 | ▲ | ¿Qué es lo que más se les complica hoy al organizar una ruta? |

## B. La ruta como concepto

> **Esta sección es la más importante del módulo.** Define toda la estructura de datos.

| # | | Pregunta |
|---|---|----------|
| R-07 | ⭐ | Si "Cumaná Histórica" se hace el 10 de marzo y otra vez el 20 de abril, ¿eso son **dos rutas distintas** o **la misma ruta ejecutada dos veces**? |
| R-08 | ⭐ | ¿Existe un **catálogo** de rutas (el recorrido, los puntos, la duración) que se reutiliza cada vez que se programa una salida? ¿O cada salida se arma desde cero? |
| R-09 | ⭐ | ¿Una misma ruta puede tener **varios grupos el mismo día** (mañana y tarde)? |
| R-10 | ▲ | ¿Las rutas cambian de recorrido según el grupo, o el itinerario es siempre el mismo? |

## C. Programación de una salida

| # | | Pregunta |
|---|---|----------|
| R-11 | ⭐ | ¿Cómo nace una ruta? ¿Un colegio la solicita, IMATUR la programa, ambas? |
| R-12 | ▲ | Si un colegio la solicita, ¿cómo lo hace? ¿Mandan un oficio? *(pedir un ejemplo real)* |
| R-13 | ▲ | ¿Hay que **aprobar** la salida antes de ejecutarla? ¿Quién aprueba? |
| R-14 | ⭐ | ¿Qué estados atraviesa una ruta desde que se planifica hasta que termina? Nómbrelos con las palabras que usan ustedes. |
| R-15 | ▲ | ¿Puede **cancelarse**? ¿Por qué motivos? ¿Se registra el motivo? |
| R-16 | ▲ | ¿Se reprograma por lluvia u otra causa? ¿Se considera la misma salida o una nueva? |

## D. Recorrido y puntos

| # | | Pregunta |
|---|---|----------|
| R-17 | ▲ | ¿Cuáles son los **puntos o paradas** de cada ruta? *(pedir el itinerario de al menos una)* |
| R-18 | ▲ | ¿El orden de las paradas es fijo o el guía lo adapta según el día? |
| R-19 | ○ | ¿Registran **cuánto dura** cada parada, o solo la duración total? |
| R-20 | ○ | ¿Hay puntos con **costo de entrada** (museos, castillos) o restricciones de horario? |
| R-21 | 💡 | ¿Les sería útil ver las paradas en un **mapa** dentro del sistema, y poder imprimir el itinerario con el mapa para entregárselo al grupo? |

## E. Participantes

| # | | Pregunta |
|---|---|----------|
| R-22 | ⭐ | ¿Quiénes participan? ¿Personas individuales, grupos escolares completos, ambos? |
| R-23 | ⭐ | Cuando viene un **colegio**, ¿registran a cada niño uno por uno, o basta con el colegio, el docente y la cantidad? |
| R-24 | ⭐ | Los niños **no tienen cédula**. ¿Cómo los identifican hoy? ¿Piden datos del representante? |
| R-25 | ▲ | ¿Registran datos demográficos (edad, sexo) de los participantes? ¿Para qué reporte los necesitan? |
| R-26 | ▲ | ¿Hace falta registrar la **institución** de la que viene el grupo (colegio, liceo, consejo comunal)? ¿Se lleva un directorio de esas instituciones? |
| R-27 | ▲ | ¿Se pide **autorización del representante** para menores? ¿En papel? *(pedir el formato)* |
| R-28 | ▲ | ¿Hay **cupo máximo**? ¿Qué pasa si se llena — lista de espera? |
| R-29 | ○ | ¿Se registra si el participante **asistió realmente**, o solo que se inscribió? |
| R-30 | 💡 | ¿Necesitan saber si una persona **ya hizo** esa ruta antes, para no repetirla o para dar prioridad a quien no ha ido? |

## F. Guías y personal

| # | | Pregunta |
|---|---|----------|
| R-31 | ⭐ | ¿Quién conduce la ruta? ¿Un empleado de IMATUR, un guía externo, ambos? |
| R-32 | ▲ | Si es externo: ¿se le paga? ¿Se lleva registro de sus datos, o es ocasional? |
| R-33 | ▲ | ¿Cuánto personal de IMATUR acompaña una salida? ¿Se registra quiénes fueron? |
| R-34 | ○ | ¿Los guías necesitan **certificación** vigente? ¿Habría que controlar su vencimiento? |
| R-35 | 💡 | El personal que sale a una ruta no está en la oficina. ¿Debería el sistema **justificar automáticamente** su asistencia ese día? *(hoy ya lo hace — confirmar que es lo correcto)* |

## G. Tarifas y pagos

> **Zona de mayor incertidumbre.** Hay campos de tarifa en la base de datos que hoy no se usan.

| # | | Pregunta |
|---|---|----------|
| R-36 | ⭐ | ¿Alguna ruta **se cobra**? ¿Cuál y cuánto? |
| R-37 | ⭐ | Si se cobra: ¿**quién** recibe el dinero? ¿IMATUR, la Alcaldía, un tercero? |
| R-38 | ⭐ | ¿Cómo se paga? (efectivo el mismo día, transferencia previa, punto de venta) |
| R-39 | ⭐ | ¿Se emite algún **comprobante**? ¿Factura, recibo, planilla de depósito? |
| R-40 | ⭐ | ¿Debe el sistema **llevar la contabilidad** de esos cobros, o solo dejar constancia de que la ruta tenía tarifa? |
| R-41 | ▲ | ¿El monto es fijo o varía? (por persona, por grupo, por temporada, descuento a estudiantes) |
| R-42 | ▲ | ¿Hay exoneraciones? ¿Quién las autoriza? |

## H. Día de la ejecución

| # | | Pregunta |
|---|---|----------|
| R-43 | ▲ | ¿Qué se registra **el día** de la ruta? ¿Hay una planilla que se llena en campo? *(pedir el formato)* |
| R-44 | ▲ | ¿Se pasa lista? ¿Antes de salir, durante, al terminar? |
| R-45 | ○ | ¿Se registra alguna incidencia (alguien se enfermó, se perdió, la ruta se acortó)? |
| R-46 | 💡 | La persona en campo, ¿tendría **teléfono con internet**? Esto define si el registro se hace en el sitio o al volver a la oficina. |

## I. Cierre e informe

| # | | Pregunta |
|---|---|----------|
| R-47 | ⭐ | Al terminar una ruta, ¿hay que entregar un **informe**? ¿A quién? *(pedir un ejemplo real — es el documento más importante del módulo)* |
| R-48 | ⭐ | ¿Qué debe contener ese informe? |
| R-49 | ▲ | ¿Lleva **fotos** como evidencia? ¿Cuántas? |
| R-50 | ▲ | ¿Debería el sistema **generarlo automáticamente** al cerrar la ruta, o prefieren llenarlo a mano? |
| R-51 | ▲ | ¿Se lleva la cuenta de cuántas personas se atendieron al mes/año? ¿Existe una **meta** que cumplir? |

## J. Oficios y documentos

| # | | Pregunta |
|---|---|----------|
| R-52 | ▲ | ¿Qué documentos se emiten alrededor de una ruta? (invitación, agradecimiento, permiso, convocatoria) |
| R-53 | ▲ | ¿Llevan **numeración correlativa**? ¿Cómo se reinicia cada año? |
| R-54 | ○ | ¿Se lleva un libro de correspondencia de esos oficios? |

## K. Seguridad y contingencia

| # | | Pregunta |
|---|---|----------|
| R-55 | 💡 | ¿Existe un **protocolo de seguridad**? ¿Se registran contactos de emergencia de los participantes? |
| R-56 | 💡 | ¿Se coordina con Protección Civil, bomberos o policía turística? ¿Habría que dejar constancia? |
| R-57 | 💡 | ¿Hay rutas con **restricciones** (edad mínima, condición física, no aptas para personas con discapacidad)? ¿Debería el sistema advertirlo al inscribir? |
| R-58 | 💡 | ¿Se contrata **transporte**? ¿Habría que registrar la unidad y el conductor? |

## L. Exploratorias — Rutas

| # | | Pregunta |
|---|---|----------|
| R-59 | 💡 | ¿Recogen la **opinión de los participantes** al final? Una encuesta breve daría un indicador de satisfacción, que suele pesar en la rendición de cuentas. |
| R-60 | 💡 | ¿Trabajan con **aliados** (posadas, restaurantes, artesanos, transportistas)? ¿Haría falta un directorio? |
| R-61 | 💡 | ¿Hay **temporadas** marcadas (vacaciones escolares, Semana Santa, Carnaval)? ¿Ayudaría un calendario anual de rutas planificadas? |
| R-62 | 💡 | ¿Necesitan mostrar **de qué parroquias** vienen los participantes, para demostrar cobertura territorial? |
| R-63 | 💡 | ¿Se reutilizan las **fotos** de las rutas para redes sociales o memoria institucional? ¿Debería el sistema guardarlas organizadas por ruta? |
| R-64 | 💡 | Si pudiera pedir **una sola cosa** para el módulo de rutas, ¿cuál sería? |

---
---

# PARTE 3 — Documentos y formatos a solicitar

Esto es lo que más acelera el trabajo. **Un formato real evita semanas de suposiciones.**

### Bienes
- [ ] Formato de inventario que se entrega a la Contraloría o la Alcaldía
- [ ] Acta o formato de **entrega de bien a un empleado**
- [ ] Acta o formato de **baja/desincorporación**
- [ ] Lista de **categorías** de bienes que usan
- [ ] Lista de **ubicaciones** (oficinas, depósitos)
- [ ] 3 ejemplos reales de **código BN** (para entender el formato)
- [ ] Último inventario físico realizado (aunque esté en papel)
- [ ] Si controlan consumibles: lista de ítems y sus mínimos

### Rutas
- [ ] **Informe de una ruta ya ejecutada** ← *el más importante*
- [ ] Oficio de solicitud de un colegio
- [ ] Oficio de respuesta/invitación que emite IMATUR
- [ ] Planilla de registro de participantes usada en campo
- [ ] Autorización del representante para menores
- [ ] Itinerario detallado de al menos una ruta
- [ ] Reporte mensual o anual de rutas que entregan a la Presidencia

---
---

# PARTE 4 — Uso interno (no imprimir para el cliente)

## Puntos donde lo construido podría no coincidir

Estas son las respuestas que **más impacto tendrían** si difieren de lo que asumimos.

### Bienes

| Pregunta | Lo que el sistema asume hoy | Riesgo si la respuesta difiere |
|---|---|---|
| **B-38** (baja desaparece o no) | 🔴 **Bug conocido (H-04):** registrar un movimiento de "Baja" **no** cambia la condición del bien ni lo saca del listado activo. Sigue contándose en KPIs e indicadores CMI-I01/I03 | **El inventario reporta números incorrectos hoy.** Se arregla en cualquier escenario; la respuesta solo define si se marca "Dado de baja" o se excluye |
| **B-26/B-27** (responsable) | No existe responsable en la ficha del bien; solo se registra un empleado en cada *movimiento* | Si el responsable debe ser nominal y permanente → columna nueva o tabla de asignación (**cambio de esquema**) |
| **B-17** (costo, fecha, proveedor) | No se registran | Si la Contraloría los exige → 3 columnas nuevas y afecta todos los reportes |
| **B-45** (stock mínimo) | No existe. Es un indicador del documento del proyecto que quedó sin implementar | Requiere columna + alerta + indicador |
| **B-22** (categorías) | Solo 2 en la BD, y parecen de prueba: "Inmobiliario", "Inmuebles" | Muy probablemente hay que rehacer el catálogo completo |
| **B-06/B-07** (qué entra) | Durable/Fungible ya implementado | Si además distinguen bienes en comodato → campo de propiedad |
| **B-34** (mantenimiento) | El bien sigue apareciendo disponible | Mismo problema de fondo que H-04 |
| **B-04** (volumen) | Hoy la tabla `inventario` tiene **0 filas** | Si son miles de bienes, la paginación cliente no basta — habría que pasar a paginación de servidor |

### Rutas

| Pregunta | Lo que el sistema asume hoy | Riesgo si la respuesta difiere |
|---|---|---|
| **R-07/R-08** (ruta vs ejecución) | 🔴 **La decisión más cara.** Cada fila de `rutas` es **una ejecución independiente**; no hay catálogo reutilizable. Si repiten Cumaná Histórica 20 veces, hay 20 filas con sus puntos duplicados | Si esperan un catálogo + salidas programadas → **rediseño del módulo** (separar `rutas` de `ejecuciones_ruta`, migrar puntos). Es la pregunta que más conviene resolver primero |
| **R-36 a R-40** (tarifas) | Columnas `tiene_tarifa`/`tarifa_monto` existen pero **nunca se escriben** desde ninguna pantalla → el reporte dice **"Gratuita" siempre** | Si sí cobran → hay que construir la captura y decidir si se registran pagos. Si no cobran → quitar la columna del reporte. **Hoy el reporte muestra información falsa** |
| **R-26** (institución del grupo) | Eliminado en la migración 060 (nunca se usó) | Si lo necesitan → se reconstruye desde cero. Conviene confirmarlo antes de que el cliente lo pida |
| **R-31/R-32** (guía externo) | Eliminado en la migración 060 | Igual que arriba |
| **R-14** (estados) | `Activa`, `Inactiva`, `En Mantenimiento`, `Finalizada` | "En Mantenimiento" es un estado extraño para una salida programada; sugiere que el modelo se pensó como catálogo. Se conecta con R-07 |
| **R-23** (grupo escolar) | Se registra participante por participante | Si con el docente y la cantidad basta, el flujo actual es innecesariamente pesado para el usuario |
| **R-47/R-50** (informe) | Existe `ruta_informes` con demografía y resumen | Contrastar contra el informe real; probablemente falten campos |
| **R-02** (rutas ofrecidas) | Catálogo fijo en un CHECK: `Cumaná Histórica`, `Exploradores de Cumaná`, `Comunitaria`, `General` | Si hay más programas → migración para ampliar el CHECK |
| **R-49/R-63** (fotos) | No hay evidencias fotográficas en rutas (Talleres sí las tiene) | Asimetría entre módulos que quizá no es intencional |

## Preguntas ya cerradas — no volver a abrir

- **D-RT01:** cada registro es una ejecución independiente *(pero conviene revalidarlo con R-07: se decidió temprano y es la base del módulo)*
- **D-IN01:** la baja solo requiere registro interno, sin acto administrativo imprimible *(revalidar con B-39)*
- **D-IN05:** Durable/Fungible — implementado en la migración 044
- Módulos retirados: instituciones externas, actividades de ruta, inventario de ruta, inventario de taller, nivel de dificultad

## Nota sobre la documentación de estos módulos

`docs/REGLAS_NEGOCIO_Inventario.md` y `docs/REGLAS_NEGOCIO_Rutas.md` están **desactualizados** (última revisión 2026-05-22). Describen como vigentes estructuras ya eliminadas: `ruta_inventario`, `taller_inventario`, `nivel_dificultad`, `instituciones_externas` y `nombre_facilitador_externo`. Conviene reescribirlos **después** de esta ronda de preguntas, con las respuestas en la mano.
