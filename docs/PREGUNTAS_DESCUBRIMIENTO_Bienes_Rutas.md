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
> ~~**Quedaron 9 preguntas abiertas o nuevas (B-60 a B-68)**~~ — B-63…B-72 se cerraron entre el
> 2026-08-05 y el 2026-09-02 (mig. 062-069). Estado al día en §9 y §12 del plan.

> ## ⚠️ ACTUALIZACIÓN 2026-09-02 — cambió el procedimiento: **varias respuestas de abajo quedaron superadas**
>
> La Alcaldía notificó a IMATUR un procedimiento nuevo. **Lo que sigue vigente es el formato del
> código y todo el expediente por bien; cambia quién codifica.**
>
> | | Ahora |
> |---|---|
> | **Codificación** | La asigna **IMATUR**, como ente autónomo, continuando la secuencia desde el último N° de orden que la Alcaldía deje en su **última revisión — que todavía no se ha hecho**. La Alcaldía **ya no viene a codificar** |
> | **Oficio de bienes nuevos** | Pasa a ser una **relación informativa** de bienes **ya codificados**, con su **monto**, para que la Alcaldía mantenga su registro patrimonial |
> | **Baja** | El documento es el **Acta de Desincorporación**, **por lote**; la Alcaldía la **firma y sella** y eso es el aval del retiro. **No habrá oficio de retiro** |
> | **Acta de encargado** y **oficio de donación** | **Siguen vigentes** |
>
> **Respuestas de este cuestionario que quedan superadas (no citarlas como vigentes):**
> **B-03**, **B-10**, **B-11**, **B-12** y **B-14** (quién codifica y cuándo) · **B-39** y **B-40**
> (el oficio de retiro deja de existir; el aval es el acta sellada) · **B-72** (los saltos en el N° de
> orden **no son bajas**: el listado va **por departamento, no por código**).
>
> **Respuestas nuevas de la misma conversación:** **B-71 = SÍ** existe versión digital de todos los
> documentos **y de un inventario interno que la encargada lleva aparte** (pedirlo: desbloquea la carga
> de los ~142 bienes, el catálogo de códigos en uso y el punto de partida de la secuencia) · **B-69
> matizada**: el monto **sí** se declara en la relación.
>
> **Preguntas nuevas B-73…B-80** (punto de partida · alcance y longitud de la secuencia · catálogo de
> clasificación —**reabre B-60**— · reutilización de códigos · contenido y frecuencia de la relación ·
> acuse de la Alcaldía · firmas y correlativo del acta · notificación por escrito): enunciadas en
> **`docs/PLAN_MODULO_BIENES.md` §2-ter**, y en lenguaje de cliente en `PREGUNTAS_CLIENTE.md` A7-A9.

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

> ⚠️ **B-10, B-11, B-12 y B-14 quedaron superadas el 2026-09-02:** el código lo asigna ahora **IMATUR**
> (secuencia propia desde el último que deje la Alcaldía), un bien nuevo **ya no espera** inspección
> para tener su N° de orden, y la etiqueta puede imprimirse en el acto. El **formato del código no
> cambia**. Ver el bloque de actualización al inicio de la Parte 1.

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

> ⚠️ **Actualizado 2026-09-02:** el documento se llama **Acta de Desincorporación** (así debe
> mostrarse en el sistema), es **por lote** —lista todos los bienes que se van a retirar— y la
> Alcaldía la **firma y sella** como aval. **El oficio de retiro de B-39/B-40 deja de existir.**
> Lo demás de esta sección sigue vigente: motivos, firmas de Coordinadora + Presidencia, denuncia en
> robo/pérdida, y que el bien sale del inventario activo conservando su registro (B-38).

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

> ## ✅ RESPONDIDO — 2026-09-17
> **63 de las 64 preguntas están respondidas.** El 2026-09-03 llegaron R-01…R-16; el **2026-09-17**
> llegó **todo lo demás**, repartido en **dos sitios**: las secciones D a L de este cuestionario y
> **las respuestas escritas dentro de `PREGUNTAS_CLIENTE.md` §4**, que es donde aparecieron **R-14 y
> todo el bloque de cobro**. Más **siete formatos reales** en `docs/formatos/rutas_*`.
>
> **Solo queda sin responder R-32** (si se le paga al guía externo), y **ya no importa**: R-31 aclara
> que ese guía lo pone el punto visitado, no IMATUR.
>
> ### 🔓 El módulo queda DESBLOQUEADO
> Las cinco preguntas que llevaban meses frenando el rediseño —**R-14** (estados) y **R-37…R-40**
> (cobro)— están contestadas. **Ya no hay nada que esperar para construir.**
>
> El análisis y el plan de reconstrucción están en **`docs/PLAN_MODULO_RUTAS.md`**.
>
> ### Lo que cambia con esta segunda tanda
>
> | | Hallazgo | Consecuencia |
> |---|---|---|
> | 📄 | **Llegó la Ficha Institucional** (R-43/R-47/R-48) | La planilla del día y el informe de cierre **son el mismo documento**. Se conoce su estructura exacta |
> | 📄 | **Llegó el itinerario** de Cumaná Histórica (R-17) | **7 puntos** numerados con reseña, no los 5 que se infirieron de R-06 |
> | 🔴 | **R-20 descubre un proceso entero**: los **oficios de permiso a las instituciones custodias**, agrupados por semana, con su estado (llegó / pase / rechazado) | **No estaba en el plan.** Es el segundo de los dos pedidos explícitos del cliente (R-64: *«los reportes y los oficios»*) |
> | 🟢 | **R-46: el registro se hace al volver a la oficina** | **Cierra el alcance:** no hace falta modo campo, app móvil ni funcionamiento sin conexión |
> | 🟢 | **R-57 da la restricción concreta** (Río Brito 12+, sin dificultad visual) | **Es la respuesta que faltaba para H-17**: la restricción es del catálogo, no un rango global |
> | 🟡 | **R-23/R-24 vs. los formatos** | Dicen *«solo registro general»*, pero existe una **lista nominal firmada**. Conviven: nominal para adultos con cédula, conteo para escolares |
> | 🟢 | **El catálogo real no coincide con R-02** | Los folletos traen **Playa Manare** (que no estaba) y **«Altos de Sucre»**, no «Altos de Cumaná» |
> | ✅ | **R-51 confirma la meta de 100 rutas** | Cierra la parte de Rutas de D-FO05 |
> | ✅ | **R-54 confirma el libro de correspondencia** | **D-OF03** pasa de «mejora opcional» a **requisito** |
> | ✅ | **R-12: el oficio de solicitud lo redacta cada institución** | **Ya no hay que pedir ese formato**: el sistema lo **recibe y archiva**, no lo genera. Campos en común que sí se piden: **cantidad de niños, ruta o atractivo específico, cantidad de representantes/maestros** |
> | 🔴 | **R-65 destapa una TERCERA modalidad** | *Cumaná Histórica* es **la comercial**; *Exploradores de Cumaná* es **solo para instituciones educativas**; y existe **«Cumaná Histórica – Huellas del Ayer»**, **solo para adultos mayores**. Un recorrido, **tres modalidades por público** |
> | 🔴 | **R-47/48 añaden actas que no conocíamos** | Al cerrar se levantan **actas de ejecutado y de no ejecutado**, y **se archivan en la OAC** |
> | 🟢 | **R-33 da un ratio calculable** | **7-8 niños por guía** → el sistema puede **sugerir** cuántos guías hacen falta según el grupo |
> | 🟢 | **R-68: hoy es papel + Excel** | Planificación en papel; estadística acumulada en Excel **semestral**; cortes **mensuales** para el **informe de gestión trimestral** |
>
> **De la primera tanda:** R-07/R-08/R-09 confirman que **sí existe un catálogo reutilizable** y que
> una salida es una **ejecución** de ese catálogo → el modelo actual (una fila de `rutas` por salida)
> **necesita rediseño**, no ajustes. R-02 confirma que **sí se cobra**. R-11 **reabre** el registro
> de la institución solicitante, eliminado en la mig. 060.

## A. Panorama general

| # | | Pregunta | Respuesta del cliente (2026-09-03) |
|---|---|----------|------------------------------------|
| R-01 | ⭐ | ¿Qué es exactamente una "ruta turística" para IMATUR? Descríbala como se la explicaría a alguien que nunca la ha visto. | Es un paseo, pero con la diferencia de que **los empleados tienen que exponer** ciertos lugares del centro histórico o del sitio: **narran** al turista la historia cultural de cada elemento —curiosidades, notas, datos varios— de forma dinámica, para que el turista sienta una **inmersión**. Hay lugares donde además se hacen **actividades recreativas**: trivias, fotos, planes vacacionales, etc. |
| R-02 | ⭐ | ¿Cuáles son **todas** las rutas o programas que ofrecen hoy? *(nombres oficiales, sin abreviar)* | **Seis programas, con tarifa:**<br>• **Cumaná Histórica** — 5 $ por adulto; **niños menores de 8 años, gratis**. Para todo público.<br>• **Exploradores de Cumaná** — **gratuita**. Para niños de **4 a 8 años**. *Es el mismo recorrido que Cumaná Histórica; solo cambia el público objetivo.*<br>• **Playa Las Maritas** — 25 $ por persona.<br>• **Río Brito** — 15 $ por persona.<br>• **Playa Colorada** — 25 $ por persona.<br>• **Altos de Cumaná** — "se cuadra"; **enlace con personas en posadas**, etc. |
| R-03 | ⭐ | ¿Cómo se organiza y registra una ruta **hoy**? (oficios en papel, Excel, WhatsApp, nada) | Se realiza un **oficio con días de antelación** para poder ejecutar la ruta. Eso aplica **solo a escuelas e instituciones públicas**; en ese caso **Cumaná Histórica es gratuita**. ⚠️ *Cómo se registra hoy (papel/Excel/nada) quedó **sin responder**: "aún no se sabe, queda en espera de respuesta".* |
| R-04 | ▲ | ¿Con qué frecuencia se ejecutan? (semanal, mensual, por temporada, solo cuando lo piden) | Por lo general **2-3 salidas semanales**, dependiendo de la salida; y además **cuando lo piden** particularmente. |
| R-05 | ▲ | ¿Cuántas personas participan típicamente en una ruta? ¿Y cuál es el máximo que pueden atender? | **Promedio: entre 15 y 30 personas.** El **máximo atendido** en una ruta ha sido de **120 personas**. |
| R-06 | ▲ | ¿Qué es lo que más se les complica hoy al organizar una ruta? | Lo más complicado: **la logística**, **la puntualidad del turista** y **la coordinación con las fundaciones externas** para conseguir acceso al lugar — depende de dónde se vaya a realizar la ruta.<br><br>**Puntos habituales de Cumaná Histórica** *(dato aportado en la misma respuesta, adelanta R-17)*: Castillo San Antonio de la Eminencia · Fortaleza Santa María de la Cabeza · Basílica Menor Santa Inés · Callejones de Santa Inés y El Alacrán · Casa Natal de Antonio José de Sucre. |

## B. La ruta como concepto

> **Esta sección es la más importante del módulo.** Define toda la estructura de datos.

| # | | Pregunta | Respuesta del cliente (2026-09-03) |
|---|---|----------|------------------------------------|
| R-07 | ⭐ | Si "Cumaná Histórica" se hace el 10 de marzo y otra vez el 20 de abril, ¿eso son **dos rutas distintas** o **la misma ruta ejecutada dos veces**? | **2026-09-03:** *"En los registros saldría la misma ruta ejecutada 2 veces, pero en diferente tiempo."*<br>**2026-09-17:** *"son 2 rutas diferentes…"*<br>⚠️ **Las dos respuestas parecen opuestas, pero describen lo mismo desde distinto ángulo** — ver la nota bajo esta tabla. |
| R-08 | ⭐ | ¿Existe un **catálogo** de rutas (el recorrido, los puntos, la duración) que se reutiliza cada vez que se programa una salida? ¿O cada salida se arma desde cero? | **"Existe un catálogo"** de todos estos puntos: cuándo comienza y cuándo termina, los puntos de la ruta, el recorrido — **todo lleva un catálogo.** **2026-09-17:** *"sí tienen un catálogo, tengo fotos de esto"* 📎 *formato pendiente de enviar.* |
| R-09 | ⭐ | ¿Una misma ruta puede tener **varios grupos el mismo día** (mañana y tarde)? | **Sí.** Puede tener **diferentes grupos con guías rotativos**, según la planificación. Incluso **en una misma mañana** se han hecho **dos salidas de la misma ruta** (dos Cumaná Histórica) y también **dos rutas distintas a la vez** (Cumaná Histórica y Río Brito). Depende de la planificación. |
| R-10 | ▲ | ¿Las rutas cambian de recorrido según el grupo, o el itinerario es siempre el mismo? | **Puede cambiar en algún punto.** Si hay varios grupos en la misma ruta al mismo tiempo, **se cambia un poco el itinerario y el orden de los puntos** (para no coincidir). |

> ### ⚠️ R-07 — las dos respuestas no se contradicen: describen dos capas
>
> El 2026-09-03 dijeron *"la misma ruta ejecutada 2 veces"* y el 2026-09-17 *"son 2 rutas
> diferentes"*. Puestas junto a R-08 y R-09, **encajan**:
>
> | Capa | Qué dice el cliente |
> |---|---|
> | **El recorrido** | **Es uno solo y está catalogado** (R-08: *"todo lleva un catálogo"*; y el folleto de Cumaná Histórica trae los 7 puntos impresos). Los 10 de marzo y 20 de abril recorren **lo mismo**. |
> | **La salida** | **Cada una es un registro aparte**, con su fecha, su grupo, su guía y **su propia ficha institucional** (R-09: dos salidas de la misma ruta en una mañana). De ahí *"son 2 rutas diferentes"*: para quien lleva los papeles, son **dos expedientes**. |
>
> **Un catálogo reutilizable + una salida por evento satisface las dos respuestas**, y es lo único
> que permite representar R-09 (dos grupos simultáneos del mismo recorrido). El modelo actual —una
> fila de `rutas` por salida, con los puntos duplicados cada vez— **no** puede.
>
> 🟡 Conviene confirmarlo con una frase, pero **no bloquea**: ninguna lectura razonable de R-08/R-09
> permite el modelo actual.

## C. Programación de una salida

| # | | Pregunta | Respuesta del cliente (2026-09-03) |
|---|---|----------|------------------------------------|
| R-11 | ⭐ | ¿Cómo nace una ruta? ¿Un colegio la solicita, IMATUR la programa, ambas? | **Dos orígenes:** el **turista particular** la solicita, y las **instituciones públicas** la solicitan **mediante un oficio que queda archivado en IMATUR**. **2026-09-17:** *"el oficio es institucional; no es uno fijo, sino que las diferentes van a traer el oficio"* → **no hay un formato único: cada institución trae el suyo.** El sistema **recibe y archiva**, no genera. ⚠️ *Reabre el registro de la institución solicitante (eliminado en la mig. 060, D-RT05).* |
| R-12 | ▲ | Si un colegio la solicita, ¿cómo lo hace? ¿Mandan un oficio? *(pedir un ejemplo real)* | **2026-09-17: en físico.** ✅ *Ya no hace falta pedir «el formato del oficio de solicitud»: lo redacta cada institución, no IMATUR. Lo que el sistema necesita es poder **adjuntar el escaneado** y registrar de quién viene.* |
| R-13 | ▲ | ¿Hay que **aprobar** la salida antes de ejecutarla? ¿Quién aprueba? | **Sí. La decisión final la tiene la Presidenta** (actualmente **María Maza**), **tanto para solicitudes particulares como institucionales**. |
| R-14 | ⭐ | ¿Qué estados atraviesa una ruta desde que se planifica hasta que termina? Nómbrelos con las palabras que usan ustedes. | ✅ **RESPONDIDA (2026-09-17, en `PREGUNTAS_CLIENTE.md` §4):** *"**Programado, Ejecutado, o No ejecutado** (muchas veces llegan los oficios, se planifica la ruta), pero a veces llega el momento donde los solicitantes cancelan (IMATUR puede cancelar por razones ajenas: agua, clima, terremotos…). En estos casos se haría una **reprogramación** de la salida que no se pudo ejecutar."*<br>→ **Tres estados, no cinco:** `Programado` → `Ejecutado` \| `No ejecutado`. **Cancelar no es un estado: lleva a *No ejecutado*** con su motivo, y de ahí sale la **reprogramación**. |
| R-15 | ▲ | ¿Puede **cancelarse**? ¿Por qué motivos? ¿Se registra el motivo? | **Sí se puede.** Motivos: **falta de gasolina**, **clima/tiempo**, o **que el grupo cancele**. **2026-09-17:** *"si se cancela, el motivo siempre tiene que saberse"* → **el motivo es OBLIGATORIO**, no opcional. |
| R-16 | ▲ | ¿Se reprograma por lluvia u otra causa? ¿Se considera la misma salida o una nueva? | **Se reprograma y se considera la misma salida**, solo con cambio de fecha. Confirmado el 2026-09-17. |

## D. Recorrido y puntos

| # | | Pregunta | Respuesta del cliente (2026-09-17) |
|---|---|----------|------------------------------------|
| R-17 | ▲ | ¿Cuáles son los **puntos o paradas** de cada ruta? *(pedir el itinerario de al menos una)* | ✅ **Entregado en el folleto** `docs/formatos/rutas_itinerario_CumanaHistorica_7puntos.jpeg`. **Cumaná Histórica tiene 7 puntos numerados, con inicio y retorno:** 1 Castillo San Antonio de la Eminencia *(inicio)* · 2 Casa Museo Antonio José de Sucre · 3 Basílica Menor Santa Inés · 4 Fortaleza Santa María de la Cabeza · 5 Callejón Santa Inés · 6 Callejón El Alacrán · 7 Callejón El Ahorcado *(retorno)*. **Cada punto lleva su reseña** («¿Sabías qué…?»), que es lo que el guía narra (R-01). |
| R-18 | ▲ | ¿El orden de las paradas es fijo o el guía lo adapta según el día? | **Se puede variar según convenga.** El orden del catálogo es el **sugerido**, no una imposición — coherente con R-10 (se altera para que dos grupos no coincidan). |
| R-19 | ○ | ¿Registran **cuánto dura** cada parada, o solo la duración total? | **Ambas.** Se lleva el cálculo **punto a punto** y además un **tiempo estimado total**. **Una ruta completa ha durado 3 horas como máximo; lo normal es 1 h 30 min.** |
| R-20 | ○ | ¿Hay puntos con **costo de entrada** (museos, castillos) o restricciones de horario? | ⚠️ **La respuesta descubre un proceso entero que no estaba en el plan.** No habla de costo sino de **permisos de acceso**: IMATUR **envía un oficio a cada institución custodia** (museos, castillos, fundaciones) para poder visitarla. **Estrategia:** todas las rutas de la semana planificada van **en un solo oficio**, para agilizar el trámite. Se lleva **control del estado de cada oficio** — si llegó, si se dio el pase, si se rechazó — y quien lo notifica es el **Director de Relaciones Inter-Institucionales**, que además corrobora que las instituciones estén disponibles. Estados: **aceptado / en espera**. |
| R-21 | 💡 | ¿Les sería útil ver las paradas en un **mapa** dentro del sistema, y poder imprimir el itinerario con el mapa para entregárselo al grupo? | **Sí.** |

## E. Participantes

| # | | Pregunta | Respuesta del cliente (2026-09-17) |
|---|---|----------|------------------------------------|
| R-22 | ⭐ | ¿Quiénes participan? ¿Personas individuales, grupos escolares completos, ambos? | **Ambos.** |
| R-23 | ⭐ | Cuando viene un **colegio**, ¿registran a cada niño uno por uno, o basta con el colegio, el docente y la cantidad? | **La institución trae su propio listado nominal de los niños; IMATUR solo hace el registro general.** |
| R-24 | ⭐ | Los niños **no tienen cédula**. ¿Cómo los identifican hoy? ¿Piden datos del representante? | **No se identifican uno a uno.** IMATUR lleva **solo el conteo general**. |
| R-25 | ▲ | ¿Registran datos demográficos (edad, sexo) de los participantes? ¿Para qué reporte los necesitan? | **Sí, pero agregados**, no por persona: *"de 9 a 10 años son 4, 8 años 3, 10 niños, 13 niñas"*. ✅ **El formato exacto llegó** — ver la *Ficha Institucional* bajo esta tabla. |
| R-26 | ▲ | ¿Hace falta registrar la **institución** de la que viene el grupo (colegio, liceo, consejo comunal)? ¿Se lleva un directorio de esas instituciones? | **Sí, se lleva registro.** ⚠️ *Confirma la reapertura de D-RT05: la institución solicitante se eliminó en la mig. 060 y hay que reconstruirla.* |
| R-27 | ▲ | ¿Se pide **autorización del representante** para menores? ¿En papel? *(pedir el formato)* | **La institución educativa es la responsable de eso.** Para IMATUR **se da por sentado** que los permisos de los padres ya existen. ✅ *No hay formato que construir: queda fuera del alcance.* |
| R-28 | ▲ | ¿Hay **cupo máximo**? ¿Qué pasa si se llena — lista de espera? | **No había cupo máximo**, pero *"se ha implementado un cupo de **60 personas por día** — esto es nuevo"*. ⚠️ **Es por DÍA, no por salida**: con dos salidas el mismo día, el tope se reparte. |
| R-29 | ○ | ¿Se registra si el participante **asistió realmente**, o solo que se inscribió? | **No hay lista detallada que cotejar**: lo que se registra el día de la ruta es **el conteo demográfico**. |
| R-30 | 💡 | ¿Necesitan saber si una persona **ya hizo** esa ruta antes, para no repetirla o para dar prioridad? | **No, no es necesario y no se toma en cuenta.** ✅ *El anti-duplicado de participantes entre salidas no aplica aquí.* |

> ### ✅ R-23/R-24/R-29 son correctas: IMATUR **no** registra participantes uno a uno
>
> ⚠️ **Corregido el 2026-09-17 (2).** Al recibir los formatos se interpretó que la *lista de
> asistencia nominal* era de los **participantes**, y que por tanto convivían dos registros. **El
> cliente lo aclaró: esa lista es la asistencia del PERSONAL DE IMATUR** que sale a la ruta — el o
> los guías y los ayudantes. No tiene nada que ver con el grupo visitante.
>
> | Documento | De quién | Qué registra |
> |---|---|---|
> | **Lista de asistencia** `rutas_lista_asistencia_nominal_IMATUR.jpeg` | **Trabajadores de IMATUR** que van a la salida | N° · Nombre y apellido · **Cédula** · **Firma** |
> | **Ficha Institucional** `rutas_ficha_institucional_IMATUR.jpeg` | **El grupo visitante** | **Conteo agregado** — ver abajo |
>
> **Consecuencia de diseño:** la lista nominal es el imprimible de la tabla de **empleados por
> salida** (`ruta_ejecucion_empleados`, mig. 078), no de participantes. Y el registro del grupo es
> **solo el conteo**: nada de inscribir niño por niño.
>
> 🟡 **Matiz que sigue en pie (R-22/R-62):** participan *"ambos"*, individuales y grupos, y de los
> **particulares de pago** sí se toma algún dato (*"si se pregunta, se lleva el registro de qué
> localidad son"*). Así que el registro individual **no desaparece**: queda para el particular que
> paga, no para el grupo escolar.
>
> ### 📄 Ficha Institucional — el formato del conteo (R-25, R-43, R-48)
>
> Encabezado: **RECORRIDO · FECHA · ENCARGADO · COLEGIO O INSTITUCIÓN · RESPONSABLE**.
>
> | Bloque | Desglose | Ejemplo real (28-08-2026) |
> |---|---|---|
> | **Niños** | F / M · **rango de edades** · total | 12 F · 10 M · «1 a 10 años» · **22** |
> | **Acompañantes — Docentes** | F / M · total | 7 F · 2 M · **9** |
> | **Acompañantes — Representantes** | F / M · total | *(vacío)* |
> | **Instituciones de apoyo** | nombre · F / M · total | Protección Civil · 1 F · 1 M · **2** |
> | | | **TOTAL 33** |
>
> El bloque *Instituciones de apoyo* confirma R-55/R-56: **Protección Civil va en la ficha**, no es
> un apunte suelto.
>
> ### 📄 Cuadro de visitantes del punto (no es de IMATUR)
>
> `rutas_cuadro_visitantes_FundacionCastilloSanAntonio.jpeg` lleva membrete de la **Fundación
> Castillo San Antonio de la Eminencia**: lo exige **el custodio del punto**, no IMATUR. Sus
> casillas son otras —**Niño · Niña · Adolescente · Mujer · Hombre · Adulto mayor** y
> **Procedencia: Local / Nacional / Extranjero**, más *Motivo: Escolar / Evento / Turismo*— y su
> total (31) **no cuadra** con el de la Ficha Institucional (33) porque no cuenta a Protección Civil.
>
> 🟡 **Pregunta nueva (R-71):** ¿el sistema debe **también** imprimir este cuadro para entregarlo en
> cada punto, o lo llenan a mano allá? Las categorías no coinciden con las de la Ficha, así que
> requeriría capturar el desglose en los dos cortes.

## F. Guías y personal

| # | | Pregunta | Respuesta del cliente (2026-09-17) |
|---|---|----------|------------------------------------|
| R-31 | ⭐ | ¿Quién conduce la ruta? ¿Un empleado de IMATUR, un guía externo, ambos? | **Siempre encabeza un empleado de IMATUR.** En algunos puntos que tienen el suyo (museo, casa natal…) **se suma un guía externo**, pero la salida **siempre va encabezada por un trabajador de IMATUR**. ✅ *El guía externo es del **punto**, no de la salida: se modela en el punto del catálogo, no como facilitador.* |
| R-32 | ▲ | Si es externo: ¿se le paga? ¿Se lleva registro de sus datos, o es ocasional? | ⛔ **Sin responder.** *Pierde urgencia con R-31: si el guía externo lo pone el punto, IMATUR probablemente no le paga ni lo registra.* |
| R-33 | ▲ | ¿Cuánto personal de IMATUR acompaña una salida? ¿Se registra quiénes fueron? | ✅ **Hay una regla concreta: 7-8 niños por guía.** *"Una salida de 35 personas irían 3 guías y acompañantes"*, aunque *"depende de la cantidad de guías disponibles y se pueden hacer estrategias"*. **Y sí: quieren que quede registrado quiénes fueron.** ⚠️ *Confirma que hacen falta **varios empleados por salida**, no el `id_facilitador` único de hoy — y que el sistema puede **sugerir** cuántos guías se necesitan.* |
| R-34 | ○ | ¿Los guías necesitan **certificación** vigente? ¿Habría que controlar su vencimiento? | **Hay 3 guías certificados**; el resto **se está formando**. Normalmente son los del **departamento de Promoción Turística**, con experiencia, **estén certificados o no**. ✅ *La certificación **no** es requisito para guiar → no hace falta bloquear ni alertar por vencimiento; a lo sumo, marcarla como dato.* |
| R-35 | 💡 | El personal que sale a una ruta no está en la oficina. ¿Debería el sistema **justificar automáticamente** su asistencia ese día? *(hoy ya lo hace — confirmar que es lo correcto)* | **Sí.** ✅ *Confirma el comportamiento actual; no hay nada que cambiar.* |

## G. Tarifas y pagos

> **Zona de mayor incertidumbre.** Hay campos de tarifa en la base de datos que hoy no se usan.

| # | | Pregunta | Respuesta del cliente (2026-09-17) |
|---|---|----------|------------------------------------|
| R-36 | ⭐ | ¿Alguna ruta **se cobra**? ¿Cuál y cuánto? | **Sí** (montos en R-02), *"al precio acordado anteriormente **en dólar del día**"*. ⚠️ **La tarifa se pacta en USD y se cobra en bolívares a la tasa del día** → el catálogo guarda el monto en **USD**, y la salida congela la **tasa aplicada**. El sistema ya sabe consultar la tasa del BCV (mig. 074, Nómina): se reutiliza. |
| R-37 | ⭐ | Si se cobra: ¿**quién** recibe el dinero? ¿IMATUR, la Alcaldía, un tercero? | ✅ **IMATUR.** *"Se dispone una cuenta personal exclusiva para el cobro de las rutas en IMATUR."* |
| R-38 | ⭐ | ¿Cómo se paga? (efectivo el mismo día, transferencia previa, punto de venta) | ✅ **Pago ANTICIPADO con fecha tope.** *"Cuando son las salidas no gratuitas, se les tiene una fecha para cancelar y poder planificar la salida, pero pueden cancelar dentro de la fecha tope."* → la salida lleva una **fecha límite de pago**, y el pago **condiciona la planificación**. |
| R-39 | ⭐ | ¿Se emite algún **comprobante**? ¿Factura, recibo, planilla de depósito? | ✅ **Dos vías:** transferencia → el cliente **manda la captura o el voucher** (se adjunta); efectivo → **IMATUR levanta un acta de pago**. ⚠️ *"El formato nace del momento, **pueden darnos una idea**, pero funciona como respaldo de que el servicio fue pagado"* → **el cliente nos pide proponer el formato del acta de pago.** |
| R-40 | ⭐ | ¿Debe el sistema **llevar la contabilidad** de esos cobros, o solo dejar constancia de que la ruta tenía tarifa? | ✅ **SÍ, lleva la cuenta.** *"Sí debe llevar el cobro… y lo cancelado."* → no es declarativo: **hay registro de pagos**. |
| R-41 | ▲ | ¿El monto es fijo o varía? | **Fijo por ruta y por persona, en USD** (R-02/R-36), **salvo Altos de Sucre**, que *"no es una tarifa fija: depende de lo que el cliente solicite"* (R-67). Sin variación por temporada (R-61). |
| R-42 | ▲ | ¿Hay exoneraciones? ¿Quién las autoriza? | ✅ **La Presidenta** (María Maza). Casos ya fijos: **menores de 8 años** en Cumaná Histórica e **instituciones públicas**; ⚠️ *matiz de R-03:* las instituciones públicas **no pagan pero igual deben traer el oficio previo**, y eso aplica **únicamente a Cumaná Histórica**. |

## H. Día de la ejecución

| # | | Pregunta | Respuesta del cliente (2026-09-17) |
|---|---|----------|------------------------------------|
| R-43 | ▲ | ¿Qué se registra **el día** de la ruta? ¿Hay una planilla que se llena en campo? *(pedir el formato)* | **Sí: la planilla de estadística** de niños/personas. ✅ **Formato entregado** → es la **Ficha Institucional** (`rutas_ficha_institucional_IMATUR.jpeg`), detallada en la sección E. |
| R-44 | ▲ | ¿Se pasa lista? ¿Antes de salir, durante, al terminar? | **No se pasa lista** (no hay listado nominal que cotejar con escolares). ⚠️ *Matizado por el formato de lista nominal firmada que sí existe para adultos — ver la nota de la sección E.* |
| R-45 | ○ | ¿Se registra alguna incidencia (alguien se enfermó, se perdió, la ruta se acortó)? | **Sí, se lleva y se reporta la incidencia.** ✅ *Campo nuevo en la salida.* |
| R-46 | 💡 | La persona en campo, ¿tendría **teléfono con internet**? Esto define si el registro se hace en el sitio o al volver a la oficina. | **Se hace al volver a la oficina.** ✅ **Decisión de alcance: NO hace falta modo campo, ni app móvil, ni funcionamiento sin conexión.** |

## I. Cierre e informe

| # | | Pregunta | Respuesta del cliente (2026-09-17) |
|---|---|----------|------------------------------------|
| R-47 | ⭐ | Al terminar una ruta, ¿hay que entregar un **informe**? ¿A quién? *(pedir un ejemplo real — es el documento más importante del módulo)* | **Sí: a la Directora de Promoción Turística** (actualmente **María Acosta**). Es **un solo destinatario**, interno. |
| R-48 | ⭐ | ¿Qué debe contener ese informe? | **Los datos demográficos de la ruta.** ✅ **El ejemplo llegó**: es la **Ficha Institucional** (sección E). **El informe de cierre y la planilla del día son el MISMO documento.** |
| R-49 | ▲ | ¿Lleva **fotos** como evidencia? ¿Cuántas? | **No.** Las fotos de las rutas son para el **informe general de la institución** y para prensa, no para este documento. ✅ *No hace falta adjuntar evidencias fotográficas a la salida.* |
| R-50 | ▲ | ¿Debería el sistema **generarlo automáticamente** al cerrar la ruta, o prefieren llenarlo a mano? | **Sí, automáticamente.** |
| R-51 | ▲ | ¿Se lleva la cuenta de cuántas personas se atendieron al mes/año? ¿Existe una **meta** que cumplir? | **Sí se lleva la cuenta. No hay meta oficial**; dejan la de **100 rutas** ya cargada en Configuración. ✅ *Cierra la parte de Rutas de **D-FO05**: el valor de relleno queda **confirmado como bueno**.* |

## J. Oficios y documentos

| # | | Pregunta | Respuesta del cliente (2026-09-17) |
|---|---|----------|------------------------------------|
| R-52 | ▲ | ¿Qué documentos se emiten alrededor de una ruta? (invitación, agradecimiento, permiso, convocatoria) | **Tres:** los **permisos** y las **actas de los lugares a visitar** (ver R-20) y el **informe demográfico** (la Ficha Institucional). *No hay invitaciones ni agradecimientos que generar.* |
| R-53 | ▲ | ¿Llevan **numeración correlativa**? ¿Cómo se reinicia cada año? | **Sí, numeración correlativa y número de oficio.** ✅ *El mecanismo por módulo ya existe (`ConfigSistema::generarNumeroOficio`).* |
| R-54 | ○ | ¿Se lleva un libro de correspondencia de esos oficios? | **Sí se lleva.** ✅ *Responde **D-OF03**, que estaba como «mejora opcional»: pasa a **requisito confirmado**.* |

## K. Seguridad y contingencia

| # | | Pregunta | Respuesta del cliente (2026-09-17) |
|---|---|----------|------------------------------------|
| R-55 | 💡 | ¿Existe un **protocolo de seguridad**? ¿Se registran contactos de emergencia de los participantes? | **Siempre se cuenta con Protección Civil.** Y se pide a las instituciones que **notifiquen a IMATUR si traen niños con alguna discapacidad o condición de cuidado**, para ir prevenidos. ✅ *No se piden contactos de emergencia individuales; lo que se registra es la **condición especial del grupo**.* |
| R-56 | 💡 | ¿Se coordina con Protección Civil, bomberos o policía turística? ¿Habría que dejar constancia? | **Sí.** ✅ *Y la constancia ya tiene sitio: el bloque **Instituciones de apoyo** de la Ficha Institucional.* |
| R-57 | 💡 | ¿Hay rutas con **restricciones**? ¿Debería el sistema advertirlo al inscribir? | **Sí, y dieron el caso concreto:** **Río Brito es de 12 años en adelante**, **excluye a personas con dificultad visual** y hay que advertir a quien tenga **alguna condición en las articulaciones**. **"Sí debería saberlo"** el sistema. ⚠️ *Esto convierte a la restricción en un **atributo del catálogo**, no un rango global — y es la respuesta que faltaba para **H-17**.* |
| R-58 | 💡 | ¿Se contrata **transporte**? ¿Habría que registrar la unidad y el conductor? | **No se registra**, pero **sí va dentro del presupuesto** de la planificación donde aplica. ✅ *Fuera del alcance del módulo.* |

## L. Exploratorias — Rutas

| # | | Pregunta | Respuesta del cliente (2026-09-17) |
|---|---|----------|------------------------------------|
| R-59 | 💡 | ¿Recogen la **opinión de los participantes** al final? Una encuesta breve daría un indicador de satisfacción. | **No hay encuesta formal.** El **equipo de prensa** toma un **testimonio**, y el guía le pregunta al encargado del grupo — *"en ocasiones se responde y otras no"*. ✅ *No hay indicador de satisfacción que construir: el dato no es sistemático.* |
| R-60 | 💡 | ¿Trabajan con **aliados** (posadas, restaurantes, artesanos, transportistas)? ¿Haría falta un directorio? | **Sí: todas las rutas trabajan con aliados prestadores de servicios de la zona.** 🟡 *Un directorio sería útil (adelanta R-67), pero no lo pidieron explícitamente.* |
| R-61 | 💡 | ¿Hay **temporadas** marcadas? ¿Ayudaría un calendario anual de rutas planificadas? | **Solo la temporada escolar**, que aumenta las solicitudes de las instituciones. **La temporada alta turística se mantiene normal.** ✅ *No hace falta modelar temporadas ni tarifas estacionales.* |
| R-62 | 💡 | ¿Necesitan mostrar **de qué parroquias** vienen los participantes, para demostrar cobertura territorial? | **Sí, se lleva el registro de la localidad:** por la **institución** cuando el grupo viene de una, y **preguntándole a la persona** cuando es un particular de pago. ✅ *Encaja con el campo *Procedencia* del cuadro del punto (Local / Nacional / Extranjero).* |
| R-63 | 💡 | ¿Se reutilizan las **fotos** para redes sociales o memoria institucional? ¿Debería el sistema guardarlas por ruta? | **No es necesario guardarlas en el sistema**, aunque sí las usan para prensa y redes. ✅ *Coherente con R-49. Sin galería que construir.* |
| R-64 | 💡 | Si pudiera pedir **una sola cosa** para el módulo de rutas, ¿cuál sería? | ⭐ **"En los reportes emitidos sería en lo que más énfasis quisieran que se resolviera… y en los oficios."** → **Las dos prioridades del módulo, dichas por el cliente: los REPORTES y los OFICIOS.** |

---
---

# PARTE 3 — Documentos y formatos a solicitar

Esto es lo que más acelera el trabajo. **Un formato real evita semanas de suposiciones.**

### Bienes

> **Estado al 2026-09-02.** El cliente enviará los formatos **nuevos** cuando los tenga: dos de ellos
> cambiaron con el procedimiento. **Pedirlos en digital** — confirmó que existen.

- [x] ~~Formato de inventario que se entrega a la Alcaldía~~ — **recibido:** Formulario BM-1 (`docs/formatos/`)
- [ ] **Relación/informe de bienes nuevos**, con el **código que asigna IMATUR** y el **monto** ← *el más urgente*
- [ ] **Acta de Desincorporación** (por lote, sellada por la Alcaldía). ~~Oficio de retiro~~: fuera del alcance
- [ ] Acta o formato de **entrega de bien a un empleado** ("acta de encargado")
- [ ] **Oficio de donación**
- [ ] ⭐ **El inventario interno que la encargada lleva aparte, en digital** — desbloquea la carga de
      los ~142 bienes, la lista de códigos en uso y el punto de partida de la secuencia
- [ ] **Lista de grupos / subgrupos / secciones** que pueden usar al codificar (B-75, reabre B-60)
- [ ] Si la tienen: la **notificación por escrito** de la Alcaldía con el procedimiento nuevo (B-80)
- [x] ~~Lista de **categorías** de bienes~~ — 11 categorías internas sembradas (mig. 062), a validar con el cliente
- [x] ~~Lista de **ubicaciones**~~ — sembradas en la mig. 069 (una por departamento + Depósito General)
- [x] ~~3 ejemplos reales de **código BN**~~ — obtenidos del BM-1 (`2-01-108` + N° de orden de 3 dígitos)
- [x] ~~Si controlan consumibles: lista de ítems y sus mínimos~~ — **no controlan consumibles** (B-07)

### Rutas

> **Estado al 2026-09-17: llegaron 7 formatos.** Lo que queda pendiente ya **no es documental**, sino
> **R-14** (los nombres de los estados) y el bloque de **cobro** (R-37…R-40).

**Recibidos el 2026-09-17** — en `docs/formatos/`:

- [x] ⭐ ~~Informe de una ruta ya ejecutada~~ → **`rutas_ficha_institucional_IMATUR.jpeg`**. Resulta
      ser **el mismo documento** que la planilla del día (R-43 = R-47/R-48)
- [x] ~~Itinerario detallado~~ → **`rutas_itinerario_CumanaHistorica_7puntos.jpeg`** (7 puntos con
      reseña). Faltan los de las otras rutas, pero el **formato** ya se conoce
- [x] ~~Planilla de registro de participantes usada en campo~~ → **`rutas_lista_asistencia_nominal_IMATUR.jpeg`**
      (N° · nombre · cédula · firma)
- [x] ~~Formato del oficio de solicitud~~ — **ya no aplica:** R-12 aclara que **lo redacta cada
      institución**, no IMATUR. El sistema lo **recibe y archiva**
- [x] ~~Autorización del representante para menores~~ — **fuera del alcance** (R-27: la responsable
      es la institución educativa)
- [x] **Catálogo comercial** → `rutas_catalogo_flyers_*` y `rutas_triptico_ExploradoresDeCumana_*`
- [x] **Cuadro de visitantes del punto** → `rutas_cuadro_visitantes_FundacionCastilloSanAntonio.jpeg`
      *(no es de IMATUR: lo exige el custodio del punto)*

**Todavía pendientes:**

- [ ] 🔴 **Formato del oficio de permiso** que IMATUR envía a las instituciones custodias (R-20) —
      *el proceso apareció recién el 2026-09-17 y es la mitad del pedido de R-64*
- [ ] **Itinerarios** de las otras rutas (Río Brito, Playa Las Maritas, Playa Colorada, Altos de
      Sucre, Playa Manare)
- [ ] **Comprobante de cobro** — solo si R-39/R-40 confirman que el sistema lleva la contabilidad
- [ ] Reporte mensual o anual de rutas que entregan a la Presidencia *(si existe uno distinto de la
      Ficha Institucional)*

---
---

# PARTE 4 — Uso interno (no imprimir para el cliente)

> 📋 Este documento es **material de origen**: el cuestionario tal como se envió y las respuestas
> tal como llegaron. El análisis vive en los `PLAN_MODULO_*.md` y lo pendiente en `BACKLOG.md` §3 —
> si algo de aquí contradice a esos, **mandan ellos**.

## Puntos donde lo construido podría no coincidir

Estas son las respuestas que **más impacto tendrían** si difieren de lo que asumimos.

### Bienes — ✅ superado

La tabla de riesgos de Bienes se retiró el 2026-09-17: **todas sus filas quedaron resueltas** por el
levantamiento del 2026-08-04 y la reconstrucción del módulo (mig. 062-069) — H-04 cerrado, responsable
derivado del departamento, costo/proveedor/garantía capturados, 11 categorías, ubicaciones sembradas,
`estatus` separado de `condicion`. Conservarla como «riesgo» inducía a error.

- **De dónde venía cada decisión:** `CHANGELOG.md` (entradas de 2026-08-04/05).
- **Estado actual y lo que falta:** `PLAN_MODULO_BIENES.md` §12 y `BACKLOG.md` §3.4.
- **El riesgo vivo es otro**, del 2026-09-02 (IMATUR codifica sus propios bienes · Acta de
  Desincorporación por lote): consecuencias C-1…C-7 en `PLAN_MODULO_BIENES.md` §2-ter.

### Rutas

> ### 🔴 ACTUALIZADO 2026-09-03 — las respuestas llegaron y **confirmaron el peor escenario**
> Esta tabla ya no es una lista de riesgos hipotéticos: **cuatro de sus filas se materializaron.**
> El plan de reconstrucción está en **`docs/PLAN_MODULO_RUTAS.md`**.

| Pregunta | Lo que el sistema asume hoy | Veredicto con la respuesta en la mano |
|---|---|---|
| **R-07/R-08/R-09** (ruta vs ejecución) | 🔴 Cada fila de `rutas` es **una ejecución independiente**; no hay catálogo reutilizable. Si repiten Cumaná Histórica 20 veces, hay 20 filas con sus puntos duplicados | 🔴 **CONFIRMADO EL RIESGO.** El cliente dice que **existe un catálogo** y que dos salidas de Cumaná Histórica son *"la misma ruta ejecutada 2 veces"*. Además hay **varios grupos el mismo día con guías rotativos**. → **Rediseño**: separar `rutas` (catálogo) de `ruta_ejecuciones` (salidas). **D-RT01 queda desmentida** |
| **R-36 a R-42** (tarifas) | Columnas `tiene_tarifa`/`tarifa_monto` existen pero **nunca se escriben**; desde el 2026-08-27 tampoco se leen (H-14) | 🟠 **SÍ SE COBRA** (R-02): 5 $ Cumaná Histórica · 15 $ Río Brito · 25 $ Las Maritas y Playa Colorada · Exploradores gratuita · gratis para instituciones públicas y menores de 8 años. → Las columnas **no se eliminan**: se capturan. **Falta el flujo** (quién recibe, comprobante, contabilidad): R-37…R-40 sin responder |
| **R-11/R-26** (institución del grupo) | Eliminado en la migración 060 (nunca se usó) | 🟠 **HAY QUE RECONSTRUIRLO.** R-11: *"las instituciones públicas lo solicitan mediante un oficio que se queda en IMATUR"*, y la gratuidad depende de que el solicitante sea institución pública. **D-RT05 queda desmentida** |
| **R-14** (estados) | `Activa`, `Inactiva`, `En Mantenimiento`, `Finalizada` | 🔴 **Inservible para el flujo real.** R-13 exige **aprobación de la Presidencia**, R-15 **cancelación con motivo**, R-16 **reprogramación conservando la misma salida**. Ninguno existe. ⛔ **R-14 quedó sin responder** — hacen falta los nombres exactos antes de fijar el CHECK |
| **R-02** (rutas ofrecidas) | CHECK fijo: `Cumaná Histórica`, `Exploradores de Cumaná`, `Comunitaria`, `General` | 🟠 **Faltan 4 programas**: Playa Las Maritas, Río Brito, Playa Colorada, Altos de Cumaná. Y `Comunitaria`/`General` no los nombró el cliente. → Con el rediseño el CHECK **desaparece**: los programas pasan a ser **filas del catálogo**, no un enum |
| **R-01/R-10** (actividades por punto) | `actividades_ruta` se **eliminó en la mig. 070** por no usarse | 🟡 **Puede tener que volver.** R-01: *"hay lugares donde se hacen actividades recreativas: trivias, fotos, planes vacacionales"*. Confirmar en R-17…R-21 si eso se **registra** o solo se hace |
| **R-05** (volumen) | `cupo_maximo` default 20; paginación de servidor ya implementada | 🟢 **Sin riesgo.** 15-30 típico, 120 máximo. El default 20 conviene subirlo o dejarlo por ruta del catálogo |
| **R-31/R-32** (guía externo) | Eliminado en la migración 060 | ⏳ **Sin responder.** R-09 menciona *"guías rotativos"* — al menos hay **varios guías por ruta**, no uno solo. El `id_facilitador` único de hoy se queda corto |
| **R-23** (grupo escolar) | Se registra participante por participante | ⏳ **Sin responder.** Con 120 personas en una salida, el flujo actual sería muy pesado |
| **R-47/R-50** (informe) | Existe `ruta_informes` con demografía y resumen | ⏳ **Sin responder.** Falta el formato real |
| **R-49/R-63** (fotos) | No hay evidencias fotográficas en rutas (Talleres sí las tiene) | ⏳ **Sin responder**, pero R-01 menciona fotos como actividad |

## Preguntas ya cerradas — no volver a abrir

- ~~**D-RT01:** cada registro es una ejecución independiente~~ — 🔴 **DESMENTIDA por R-07/R-08
  (2026-09-03):** el cliente confirmó que **existe un catálogo reutilizable** y que dos salidas de la
  misma ruta son *"la misma ruta ejecutada 2 veces"*. Se decidió temprano, sin levantamiento, y era la
  base del módulo. **Segunda decisión temprana que el levantamiento tumba** (la primera fue D-IN01).
  Plan de reconstrucción: `docs/PLAN_MODULO_RUTAS.md`
- ~~**D-RT05:** instituciones participantes — eliminado (mig. 060)~~ — 🟠 **DESMENTIDA por R-11:** la
  institución solicitante **sí importa** (solicita por oficio, y de ella depende la gratuidad)
- ~~**D-IN01:** la baja solo requiere registro interno, sin acto administrativo imprimible~~ —
  **DESMENTIDA por B-39:** la baja **sí** es un acto administrativo con acta firmada. Desde el
  2026-09-02 ese documento es el **Acta de Desincorporación**, por lote y sellada por la Alcaldía.
  *(Buen ejemplo de por qué se revalidan las decisiones tempranas.)*
- ~~**D-IN05:** Durable/Fungible — implementado en la migración 044~~ — **revertido:** `tipo_bien` y
  `cantidad` se eliminaron en la mig. 067 (B-66); IMATUR no lleva consumibles y el registro es individual
- Módulos retirados: ~~instituciones externas~~ (**reabierto**, ver arriba), actividades de ruta
  *(a revisar con R-17…R-21: R-01 menciona trivias y actividades recreativas)*, inventario de ruta,
  inventario de taller, nivel de dificultad

## Nota sobre la documentación de estos módulos

~~`docs/REGLAS_NEGOCIO_Inventario.md` y `docs/REGLAS_NEGOCIO_Rutas.md` están **desactualizados**~~ —
✅ **Hecho.** `REGLAS_NEGOCIO_Inventario.md` se reescribió por completo el 2026-08-05 con las
respuestas en la mano (RN-IN01…RN-IN13) y se actualizó el **2026-09-03** con el cambio de
procedimiento; `REGLAS_NEGOCIO_Rutas.md` se saneó el 2026-08-28. Ninguno de los dos describe ya
estructuras eliminadas.
