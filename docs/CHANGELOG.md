# CHANGELOG — SIGTUR-IMATUR

Historial de lo construido, con el **porqué** de cada decisión. Es un archivo de registro: **no**
describe el estado actual del sistema ni lo que falta.

| Para saber… | Leer |
|---|---|
| Qué falta y qué se espera del cliente | `BACKLOG.md` |
| Cómo funciona el sistema hoy | `CLAUDE.md` |
| Qué cambió exactamente en el código | `git log` |

> **Origen (2026-09-17):** extraído de `BACKLOG.md` §2, que ocupaba el 57 % del backlog (69 KB de
> 121 KB) y obligaba a atravesar tres meses de historia cerrada para llegar a lo pendiente; y de la
> cabecera de `CLAUDE.md`, que acumulaba 7 bloques «Anterior:» (8 KB) antes de la primera línea de
> referencia técnica. Orden: **del más reciente al más antiguo**.

---

# Parte 1 — Registro por ciclo

### 2026-10-01 (b) — Los logos de los .xlsx se descuadraban según el ancho de la tabla

`XlsxLogos` anclaba el logo de IMATUR **al inicio de la última columna**, no contra el borde derecho
de la tabla. Con una última columna ancha (p. ej. «Descripción» en Configuración → Categorías de
Inventario, ~400 px) el logo caía a mitad de la hoja, encima del membrete centrado; con pocas
columnas angostas pasaba lo contrario y los dos logos tapaban el texto. Las proporciones de las
imágenes **sí** eran correctas: el problema era solo de posición.

La causa de fondo: el ancho de columna se calculaba en **dos copias** de la misma fórmula
(`ReportesExportTrait::descargarXlsx()` y `XlsxMultiSheet::construir()`) y ninguna se lo pasaba a los
logos, que solo recibían la cantidad de columnas. Ahora `XlsxLogos::anchosColumnas()` es la única
fórmula y `piezasParaHoja()` recibe esos anchos, convierte a píxeles y ancla el logo derecho a 6 px
del borde real. Si la tabla mide menos de 620 px, el sobrante se reparte entre las columnas para
que el membrete no quede bajo los logos. Cubre todos los .xlsx con membrete: listados, reportes y
las 6 hojas de Nómina. Verificado renderizando hojas de 1, 2, 3 y 8 columnas con Excel.

### 2026-10-01 — El correo de recuperación de contraseña, con plantilla institucional

Primera prueba real del SMTP (Gmail, en desarrollo): el correo llegaba, pero era texto plano con un
enlace de 100 caracteres. Ahora lleva el logo de IMATUR, botón, aviso de vigencia, el enlace de
respaldo, pie con nombre/RIF/dirección (de `configuracion_sistema`) y versión en texto plano.

- El logo va **incrustado (CID)**, no por URL: el sistema es on-premise y una dirección a
  `URL_ROOT` no se ve desde el buzón. Se usa una copia reducida (`logo_correo.png`, 38 KB) en vez del
  original de 190 KB.
- La plantilla es reutilizable: `sigtur_plantilla_correo()` en `mail_helper.php`.
- `Usuario::buscarPorIdentificador()` trae también el nombre de la persona, para el saludo.

### 2026-09-18 (e) — Rutas: el cobro, y con él las nueve fases del módulo (T-C, mig. 083)

**Cierra H-14.** Las columnas `rutas.tiene_tarifa` y `tarifa_monto` existían desde la mig. 007 y
**nunca se capturaron en ningún formulario**: el reporte informaba «Gratuita» para toda ruta,
siempre, incluso si se había cobrado. La columna se había retirado del reporte en agosto a la espera
de D-RT02. Con R-02 y R-36…R-42 respondidas, ahora se capturan de verdad y la columna vuelve.

R-40 preguntaba si el sistema debe **llevar la contabilidad** de los cobros o solo dejar constancia
de que la ruta tenía tarifa. La respuesta fue la más exigente: *«Sí debe llevar el cobro… y lo
cancelado»*. Así que es un registro de pagos, no un «pagó sí/no».

**La tarifa se pacta en dólares y se cobra en bolívares a la tasa del día** (R-36). El catálogo
guarda USD, con tres modos —Gratuita, Fija y **A convenir**, que hacía falta para Altos de Cumaná
(R-41: «depende de lo que el cliente solicite»)—, y **cada salida congela** su tarifa y su tasa. Si
mañana sube el dólar o el cliente cambia el precio, lo cobrado la semana pasada no se mueve: es el
mismo criterio de la mig. 074 con la nómina. La tasa se sugiere desde el BCV **reutilizando
`TasaBcv`**, y un fallo de red nunca bloquea — se carga a mano.

**La gratuidad no es automática por ser institución pública.** R-03 lo matiza: las instituciones no
pagan **Cumaná Histórica**, pero sí Playa Colorada, y aun exoneradas **igual deben traer el oficio**.
Por eso `exonera_instituciones` y `exonera_menores_de` son **por ruta**, no reglas generales.

**Los pagos son una tabla, no un campo**: hay abonos, varias transferencias del mismo grupo y pagos
de representantes distintos. Transferencia → comprobante adjunto; efectivo → **acta numerada**
(`ACTP-`), que solo se emite para efectivo porque la transferencia ya trae su propio respaldo.
**Anular un pago no lo borra** —es dinero—: queda con su motivo y deja de sumar. Y R-38, el pago
anticipado con fecha tope, se traduce en una salida marcada como vencida cuando pasó la fecha y
sigue habiendo saldo.

> ⚠️ **El acta de pago es una PROPUESTA nuestra.** Sobre ella el cliente dijo: *«el formato nace del
> momento, pueden darnos una idea, pero funciona como respaldo de que el servicio fue pagado»*
> (R-39). Es el único documento del módulo que diseñamos en vez de replicar. Lleva lo mínimo que
> hace de un papel un respaldo válido —quién recibió, de quién, cuánto **en cifras y en letras**,
> por qué concepto y cuándo, con las dos firmas— y se somete a su visto bueno.

**Con esto quedan construidas las nueve fases del módulo de Rutas** (mig. 078-083). Lo único
pendiente es del cliente: el formato del oficio de permiso (T-H) y su visto bueno al acta de pago.

### 2026-09-18 (d) — Rutas: el itinerario es de la salida, no del recorrido (T-F, mig. 082)

R-10: *«Puede cambiar en algún punto. Si hay varios grupos en la misma ruta al mismo tiempo, se
cambia un poco el itinerario y el orden de los puntos (para no coincidir)»*. R-18 lo dice desde el
otro lado: el orden del catálogo es el **sugerido**.

**El problema era dónde vivía el orden.** `puntos_ruta.orden` es del recorrido: cambiarlo para que
dos grupos no se crucen un martes se lo cambiaba **a todas las salidas**, pasadas y futuras. Ahora
`ruta_ejecucion_itinerario` guarda el orden **de esa salida**: sin filas manda el catálogo —el caso
normal, que no cuesta nada— y en cuanto se reordena mandan ellas. «Restablecer» las borra.

Se reordena con ↑/↓ y sin librerías (el sistema es offline): el JS intercambia los nodos y renumera,
y el servidor vuelve a exigir que estén **todas** las paradas y que no haya dos en la misma posición.

**Una parada se puede marcar «no se hizo»**, con su nota. Salió de un caso que apareció con T-H: si
la institución custodia rechaza el permiso (R-20), ese día el grupo no pasa por ahí — pero la parada
sigue existiendo en el recorrido. Queda la constancia sin tocar el catálogo.

**El personal de la salida** (R-33: 7-8 participantes por guía, «y sí: quieren que quede registrado
quiénes fueron») tenía tabla y métodos desde T-A pero **ninguna pantalla** — la cuarta vez en este
módulo. Ahora se asigna, se quita y se marca el encargado, que es **uno solo** (R-31: «siempre
encabeza un empleado de IMATUR»); es el que sale en la Ficha Institucional. La sugerencia de cuántos
guías hacen falta se muestra pero **no bloquea**: el cliente dijo que depende de los disponibles.

**El guía externo es del punto, no de la salida** (R-31): lo pone el museo o la casa natal. Se marca
en `puntos_ruta` y se registra **solo que lo hay** — R-32, si se le paga o se guardan sus datos,
sigue sin responder, y no vale inventar un registro de personas que nadie pidió. No se reintroduce
un «facilitador externo» en la ruta: esa columna se eliminó en la mig. 060 por no usarse.

Con esto quedan **ocho de las nueve fases** del módulo. Falta solo **T-C, el cobro**.

### 2026-09-18 (c) — Rutas: los permisos de acceso a las instituciones custodias (T-H, mig. 081)

Cierra el **pedido #2 del cliente** (R-64: *«los reportes y los oficios»*) y, con él, el grueso del
módulo. Salió de una pregunta que iba por otro lado: **R-20 preguntaba por el costo de entrada** a
museos y castillos, y la respuesta describió un trámite entero que el sistema no modelaba.

> IMATUR envía un oficio a cada institución custodia para poder visitarla. **Todas las rutas de la
> semana planificada van en un solo oficio**, para agilizar el trámite. Se lleva control del estado
> de cada uno —si llegó, si se dio el pase, si se rechazó— y lo notifica el Director de Relaciones
> Inter-Institucionales.

**Por qué es una entidad propia.** Un permiso cubre **varias salidas** —las de toda la semana— y una
salida puede necesitar **varios permisos**: una ruta que pasa por el Castillo y por la Basílica tiene
dos custodios distintos. Es N:M, así que no cabía como columna de `ruta_ejecuciones`. Cabecera +
renglones, igual que el Acta de Desincorporación de Bienes.

**Lo que el cliente no tiene hoy.** R-06 decía que coordinar el acceso con las fundaciones es **uno
de los tres dolores principales** del módulo, y sugería registrar el ente custodio de cada punto; el
plan decidió esperar a R-20 para saber para qué serviría. Ahora se sabe: `puntos_ruta.ente_custodio`
es lo que permite que la pantalla diga **a qué instituciones falta pedirles permiso** para las
salidas de una semana, en vez de que alguien lo recuerde de memoria.

Correlativo propio `PERM-NNN/AAAA`, que **no se recicla** al anular —el oficio ya salió— y que se
pide **después** de validar todo, para que un intento fallido no queme un número. El pase que
devuelve la institución se adjunta y se sirve por `DescargaController::permisoRuta()`, con el mismo
tratamiento de documento privado que el resto.

> ⚠️ **El imprimible es provisional.** El cliente describió el trámite pero no entregó el formato del
> oficio. Se construyó una carta institucional con el membrete estándar y el cuerpo que se desprende
> de lo que contó; cuando llegue el papel se reemplaza **solo esa vista**. Mismo criterio que con el
> Acta de Desincorporación (mig. 077).

**De paso, dos cosas rotas desde T-A.** El formulario de la parada mandaba `id_ejecucion` y
`storePunto()` leía `id_ruta`: **agregar una parada no funcionaba** (quedaba en 0). Y tanto guardar
como eliminar una parada redirigían a `/rutas/detalle/{id_ruta}`, que desde la mig. 078 espera el id
de una **salida** — llevaban a la salida equivocada o a ninguna.

### 2026-09-18 (b) — Rutas: quién pide la salida y quién la aprueba (T-E, sin migración)

Tercera vez en el módulo que el modelo estaba y la pantalla no: `ruta_ejecuciones` tenía
`origen`, `institucion_nombre`, `oficio_archivo`, `aprobada_por` y `fecha_aprobacion` desde la 078,
y `RutaEjecucion::aprobar()` también — pero nada de eso se veía ni se podía usar.

**El oficio de solicitud es ENTRANTE.** R-12 lo deja claro: lo redacta la institución que pide la
visita, no IMATUR. Así que el sistema **no lo genera** — lo recibe y lo archiva. Se sube en PDF, JPG
o PNG, se valida extensión **y MIME real** (nunca `$_FILES['type']`, que lo manda el cliente), se
guarda fuera del web root en `storage/uploads/rutas/` y se sirve por
`DescargaController::oficioRuta()` con rol 1/3, como todo documento privado del sistema. Quitarlo lo
desvincula **sin borrar el archivo**: es el respaldo de una salida autorizada.

No confundirlo con `oficios_emitidos`, que sí es saliente y lleva correlativo: ese va de IMATUR al
punto que se va a visitar.

**La aprobación** (R-13) es de la Presidencia y queda con fecha y con quién la asentó. Todo esto vive
en una tarjeta «Solicitud y aprobación» en el detalle de la salida, con las tres cosas juntas: quién
la pidió, con qué oficio y con qué visto bueno.

### 2026-09-18 — Rutas: cerrar y reprogramar una salida (T-D, sin migración)

El modelo y el controlador estaban desde T-A; **lo que no existía era la interfaz**. `cambiarEstado()`
y `reprogramar()` no los llamaba ningún botón, y el detalle de una salida mostraba el estado del
**recorrido** («Activa»), que no dice nada de si esa salida ocurrió. Es lo mismo que pasó con la
Ficha: código correcto sin puerta de entrada.

Ahora el badge es el de la salida y a su lado están **«Marcar ejecutada»** y **«No se ejecutó»**, éste
con motivo obligatorio (R-14). Cuando una salida queda sin ejecutar aparece **«Reprogramar»**, que
crea una salida **nueva** con el mismo grupo, origen y cupo, enlazada a la original (R-16). La
original **no se modifica**: es la constancia de lo que no ocurrió, y así lo dice la pantalla.

El hilo se ve en los dos sentidos —desde la original, «Se reprogramó para el …»; desde la nueva,
«Reemplaza a una anterior (del …)»—, con enlace de ida y vuelta. Y una salida no ejecutada **no**
ofrece Ficha Institucional ni la genera: no hubo grupo atendido que registrar.

### 2026-09-17 (b) — Rutas: la Ficha Institucional, y los reportes que la mig. 078 dejó en cero (mig. 080)

**1. Los reportes de Turismo estaban rotos y no se notaba.** Al preparar T-G apareció que la
separación catálogo/salida había dejado media docena de consultas mirando `rutas` como si cada fila
fuera una salida. Ninguna daba error: devolvían **cero**. «Ejecuciones de Ruta» filtraba por
`estado = 'Finalizada'`, un estado que la 078 eliminó; los participantes se contaban por
`participantes_ruta.id_ruta`, que hoy es NULL en todo registro nuevo; y el **informe de una salida
no se guardaba** porque el controlador mandaba `id_ruta` donde el modelo lee `id_ejecucion`. También
volvía a contar como ausente al empleado que salía a una ruta. Todo repuntado a `ruta_ejecuciones`.

**2. La Ficha Institucional (T-G, mig. 080).** Es el pedido #1 del cliente y el único documento de
Rutas con formato en mano. Se pedían dos —«planilla del día» (R-43) e «informe de cierre» (R-47/48)—
y **resultaron la misma hoja**, así que la ficha no es una tabla nueva: es lo que `ruta_informes` ya
quería ser. Se extendió esa tabla y los renglones viven en `ruta_ficha_grupos`; los reportes que
suman `total_atendidos` siguieron funcionando sin tocarse.

Lo que cambia de fondo es **qué se captura**: antes eran cuatro cifras sueltas
(mujeres/hombres/niñas/niños) que nadie había pedido; ahora es el desglose real —un renglón por
institución con su rango de edades, los acompañantes separados en docentes y representantes, y las
**instituciones de apoyo** (Protección Civil, R-55/R-56)—. Esas cuatro columnas quedan **derivadas**
y se recalculan solas. El total cuadra contra el papel del cliente: 22 niños + 9 docentes + 2 de
Protección Civil = **33**.

Y **se genera sola** al marcar la salida como Ejecutada, que es lo que dice R-50: al volver a la
oficina (R-46) la hoja ya está, con recorrido, fecha, encargado e institución puestos desde lo que
el sistema registró. Solo faltan los conteos. Dos estados: Borrador mientras se captura, Cerrada
cuando se imprime — y reabrir no borra nada, solo permite corregir.

**3. De paso: una salida no se podía cerrar desde la interfaz.** `cambiarEstadoSalida` existía desde
T-A pero ningún botón lo llamaba, y el detalle de la salida mostraba el estado del **recorrido**
(«Activa»), que no dice nada de si esa salida ocurrió. Sin eso la ficha no podía nacer nunca. Ahora
el badge es el de la salida y a su lado están «Marcar ejecutada» y «No se ejecutó» —con motivo
obligatorio (R-14)—. Cinco enlaces de esa pantalla (informe, los tres de oficios y la asistencia
masiva) seguían pasando el id del recorrido en vez del de la salida.

### 2026-09-17 — Rutas: el catálogo se separa de las salidas, y la edad deja de estar cableada (mig. 078-079)

**1. Catálogo ≠ salida (T-A, mig. 078).** El módulo estaba construido sobre una premisa falsa: cada
fila de `rutas` era *una salida*, con su fecha y su guía. R-07/R-08 desmintieron eso — **el catálogo
es reutilizable** y la misma ruta se ejecuta muchas veces, incluso dos veces el mismo día. Nace
`ruta_ejecuciones` (con los tres estados que dio R-14: `Programado` / `Ejecutado` / `No ejecutado`,
motivo obligatorio y reprogramación enlazada, R-16) y `ruta_ejecucion_empleados`. Participantes,
asistencia, informe y oficios cuelgan ahora de `id_ejecucion`. Dos pantallas: `/rutas/index` es el
catálogo, `/rutas/salidas` la agenda.

**2. La edad salió del código (T-B, mig. 079) — H-17 cerrado.** `RutasController` exigía **5 años
mínimo y menos de 12**, con los rótulos «Niño/a 5–11» repartidos por la vista, el informe y el
export. Ese rango se fijó en la mig. 017 **sin levantamiento**: ningún dato del cliente lo
respaldaba, y chocaba de frente con lo que sí dijeron —

> R-66: *«Exploradores lleva el tope de 4 hasta 16 años»* → **hoy un niño de 4 no se podía inscribir.**
> R-57: *«Río Brito tiene restricción: de 12 años en adelante. Personas con dificultad visual, excluidos.»*

De R-57 se desprende lo importante: la restricción **no es solo la edad** y **no es global** — es un
atributo **de cada recorrido**. Por eso `rutas.edad_min` / `rutas.edad_max` (NULL = sin tope) y
`rutas.restricciones` en texto para lo que no es edad. La única regla es
`Ruta::motivoEdadNoValida()`, y se aplica en **los dos** flujos de inscripción (con cédula y sin
ella) y también en el formulario, que ya no deshabilita el botón por un rango inventado sino por el
de esa ruta. Los rótulos del informe quedaron en «Niñas»/«Niños»: el desglose es por sexo, no por un
tramo que nadie pidió.

**3. El cupo es por día, no por salida (T-I, mig. 079).** R-28: *«se ha implementado un cupo de 60
personas por día — esto es nuevo»*. Con dos salidas la misma mañana el tope se **reparte**, así que
no puede vivir en `ruta_ejecuciones`: es el escalar `rutas_cupo_diario` en Configuración (0 = sin
tope, para desactivarlo sin tocar código). `RutaEjecucion::personasEnFecha()` suma todas las salidas
de esa fecha —descontando las no ejecutadas— y el detalle de la salida muestra *«N de 60 personas
ese día»*. **Advierte, no bloquea**, igual que el cupo de Talleres: es planificación, no un límite
rígido.

> ⚠️ **Corrección del mismo día:** al recibir los formatos se interpretó que la *lista de asistencia
> nominal* (con cédula y firma) probaba que IMATUR registra participantes uno a uno. El cliente
> aclaró que **esa lista es del personal de IMATUR** que sale a la ruta — guías y ayudantes. De modo
> que R-23/R-24/R-29 eran correctas: del grupo visitante se lleva **solo el conteo**, y la lista
> nominal es el imprimible de `ruta_ejecucion_empleados`. El registro individual sobrevive para el
> **particular de pago** (R-22/R-62).

### 2026-09-17 — Bienes: los formatos que llegaron, H-16, el renombrado del estatus y el acta por lote (mig. 075-077)

Cuatro cosas del mismo módulo en un día, encadenadas.

**1. Llegaron 2 de los 4 formatos (mig. 075).** La Directora de Bienes entregó el **Oficio N° 179/2026**
(relación de bienes nuevos a la Alcaldía) y un **documento de donación**; el tercer archivo era el BM-1
que ya teníamos desde agosto. Ambos quedaron construidos: `/inventario/relaciones` con su correlativo
propio y su control de "qué ya se reportó", y el documento de donación con los campos del donante y
`Util::montoALetras()`/`fechaEnLetras()` — verificados **literales** contra el papel.

> ⚠️ **B-81:** el oficio entregado es de **junio**, anterior al cambio de procedimiento del 02/09, y
> dice lo contrario de lo levantado: pide que la **Alcaldía** codifique, y su tabla no tiene columna
> de código. Se construyó **fiel a lo entregado**, que es lo verificable; si el cliente confirma el
> formato nuevo, es agregar una columna.

**2. Los datos institucionales estaban mal.** Los dos documentos, firmados y sellados, declaran
Resolución **32** y Gaceta **87**, ambas del 05/09/2025. El sistema tenía 025/2024 y 042/2024 —de
relleno— imprimiéndose en **cinco documentos reales**: constancias, cartas de pasantes y los dos
oficios de rutas. Corregidos, junto con el cargo (**Presidenta**, no «Director General»).

**3. H-16 — el reporte de bajas medía la papelera (mig. 076).** Desde la mig. 062 una desincorporación
es `estatus = 'Dado de baja'` **conservando `is_active = TRUE`**; `is_active = FALSE` es la papelera de
registros creados por error. El reporte filtraba por la papelera: una desincorporación real **nunca**
aparecía y un registro borrado por equivocación **sí**.

> **Por qué sobrevivió a la reconstrucción del módulo:** la consulta estaba **copiada tres veces**
> (listado + Excel + PDF). Ahora hay un solo origen, `bajasQuery()`/`bajasTotales()`. Y al corregirlo
> apareció que el **mismo error estaba en el KPI `kpiBajasAnio` del Dashboard**: eran cuatro, no tres.

La fecha salía de `deleted_at` y el autor de `deleted_by`; ahora ambos salen del **movimiento de Baja**,
que es el acto real. El reporte gana la columna *Por retirar / Retirado* (B-67).

**4. C-6 — el estatus se llama «Desincorporado» (mig. 076).** Se renombró el **valor**, no solo el
rótulo: `inventario` estaba en **0 filas** y ningún `audit_logs` lo mencionaba, así que hacerlo hoy
costaba cero y después de cargar los ~142 bienes habría costado bastante; un mapa de etiquetas habría
dejado la bitácora y la base diciendo lo viejo para siempre.

> ⚠️ **La trampa:** **catorce consultas** tenían el literal `'Dado de baja'` **cableado** en el SQL
> (Dashboard ×2, indicadores ×6, reportes ×2, Centro de Alertas ×3, dotación ×1). Aplicar la
> migración sin tocarlas las habría dejado sin filtrar **en silencio**, devolviendo los bienes
> desincorporados al inventario activo, a los KPIs y a las alertas. Ahora usan `:baja` o
> `Inventario::sqlEstBaja()`. **El texto vive en un solo sitio: `Inventario::EST_BAJA`.**

**5. Acta de Desincorporación por lote (C-5, mig. 077).** Modela el ciclo real en dos actos: el bien se
desincorpora → se arma el acta con varios → se imprime y se lleva → **vuelve firmada y sellada, y ese
sello ES el aval del retiro**: al registrarla, **todos** sus bienes pasan a «Retirado» de una vez.
Antes había que confirmarlos uno por uno y la entidad «acta» no existía. Anular un acta firmada
**revierte el retiro**, porque el aval que lo respaldaba dejó de existir; el correlativo no se recicla.

> **La vista imprimible es PROVISIONAL.** El formato oficial no ha llegado (R-2). Usa el membrete
> compartido y una estructura mínima —qué bienes, por qué, quién entrega, quién recibe—; cuando llegue
> el formato se sustituye **solo** `acta_imprimible.php`.

**Verificado:** 20 comprobaciones de los formatos + 21 del acta contra la BD real, más el flujo completo
en navegador (emitir → imprimir → registrar firmada → los 3 bienes pasan a *Retirado* en el reporte), y
un bien desincorporado contra uno en papelera para probar H-16 en listado, Excel y PDF. Datos de prueba
eliminados en todos los casos.

**De paso:** se quitó de `/config` la sección *Nómina*, que desde el 14/09 solo contenía un cartel
apuntando a `/nomina/parametros`.

---

### 2026-09-13 — La tasa del dólar se consulta al BCV, como sugerencia (mig. 074)

Cargar los parámetros del mes obligaba a ir a buscar la tasa a mano. Ahora el botón **Consultar BCV**
de `/nomina/parametros` la trae y la propone; **Talento Humano la confirma o la corrige**.

**Por qué sugerencia y no automático.** No está confirmado que la tasa que IMATUR aplica sea la del BCV
del día — la plantilla del cliente trae **36,58 y 36,23 en hojas distintas del mismo período**, lo que
apunta a un criterio propio (pregunta **N-4**, ya en `PREGUNTAS_CLIENTE.md` §C4). Dar el dato por bueno
automáticamente produciría nóminas mal calculadas **en silencio y con aire de autoridad**, que es peor
que el campo manual. La consulta ocurre **solo al cargar el parámetro**: el cálculo de la quincena nunca
sale a internet, toma la tasa ya congelada en `nomina_periodos`.

**Trazabilidad (mig. 074).** `nomina_parametros_mes` gana `tasa_fuente` (BCV/Manual), `tasa_fecha_valor`
y `tasa_consultada_at`. La procedencia la decide el controlador comparando lo sugerido con lo guardado:
**si el usuario retoca el número, aunque sea un decimal, queda como Manual** y se limpian fecha valor y
momento de consulta — el respaldo del BCV no puede amparar un valor que el BCV no publicó. Verificado en
navegador: alta → `BCV` + fecha valor; edición del número → `Manual` + fecha en blanco; ambas en la
bitácora.

**El BCV no tiene API.** Se lee su portada (bloque `id="dolar"`). Se descartaron los espejos JSON tras
comprobarlos: `ve.dolarapi.com` iba **4 días atrasado** (832,49 contra 842,21 oficial, ~1,2 %) y
`pydolarve.org` no respondía. Para un ente público la fuente citable en auditoría es el BCV.

> **Dos hallazgos técnicos que valen para el futuro:**
> 1. **El BCV publica con fecha valor adelantada**, no «la tasa de hoy»: un domingo su portada ya muestra
>    la del martes siguiente. Guardar `CURRENT_DATE` habría guardado una fecha falsa; se lee el atributo
>    `content` en ISO de la propia página.
> 2. **El servidor del BCV entrega la cadena de certificados equivocada** (manda un intermedio Sectigo
>    que no es el que firmó su certificado). Los navegadores lo disimulan buscando el que falta (AIA);
>    OpenSSL, que es lo que usa PHP, no — de ahí que todos los ejemplos de internet traigan
>    `CURLOPT_SSL_VERIFYPEER => false`. **Aquí no se apagó la verificación**: se versionó el intermedio
>    correcto en `storage/certs/bcv-sectigo-ca.pem` (vence 2036, con su LEEME), así que la verificación
>    queda activa y además anclada a la CA esperada. Apagarla habría dejado que un intermediario dictara
>    la tasa con la que se paga la nómina.

Si el BCV cambia su página o su CA, `TasaBcv::consultar()` lanza un mensaje que dice qué pasó y la tasa
se sigue cargando a mano: **nunca bloquea la nómina**.

### 2026-08-28 (2) — Deuda técnica: ReportesController partido, a11y de formularios y utilidades CSS (sin migración)

**`ReportesController`: 3.405 → 101 líneas.** Reunía **101 métodos** de siete áreas distintas en un
solo archivo. Se repartió en 8 traits bajo `app/controllers/reportes/` (`Rrhh`, `Formacion`, `Turismo`,
`Inventario`, `Recepcion`, `Sistema`, `Indicadores`, `Export`), dejando en el controlador solo el índice
y los tres helpers transversales (`requireRoles`, `qsFiltros`, `renderReporte`).

Se eligió **traits** y no clases colaboradoras justamente porque no cambia nada: se componen en la misma
clase, así que `$this`, los métodos privados y las firmas siguen siendo los mismos. **Ninguna URL, ruta
o llamada cambió.** Se incluyen con `require_once` porque el autocargador de `public/index.php` solo
mira rutas planas y no entra en subdirectorios.

La corrección no se dio por buena "a ojo", se **demostró** por tres vías:

| Comprobación | Resultado |
|---|---|
| API por reflexión, antes vs. después (nombre + visibilidad + firma con tipos y defaults) | **101 métodos, 0 diferencias** |
| Cuerpos de los 101 métodos, comparados uno a uno contra la versión en git | **0 distintos** — mismo hash `ace02895ad58` del conjunto |
| Prueba de humo en runtime: un método privado de 5 traits distintos contra la BD real | Todos ejecutan (`queryAuditoria` → 141 filas, `queryRutas` → 2) |

De paso, el parser destapó un método que un primer barrido no veía: `fmtMesLargo` es
`private static`, y el patrón inicial solo contemplaba `private function`. Por eso el conteo se
contrastó contra la reflexión antes de mover una sola línea.

**Accesibilidad de formularios: de 88 a 393 `label[for]`** (de 469 etiquetas). Se automatizó, pero
solo sobre los casos seguros, y con dos resguardos: los `id` se deduplican **contra los de
`inc/header.php` y `inc/footer.php`**, porque el requisito es que sean únicos en la *página* y toda
vista incluye el layout; y donde el control ya tenía `id` se **reutiliza** en vez de inventar otro
(144 de los 305). Verificado después: **0 `id` duplicados y 0 `for=` apuntando a un `id` inexistente**
en las 67 vistas.

**Quedan 76 sin tocar, a propósito:** 59 están dentro de un `foreach` —un `id` estático se repetiría en
cada fila, que es peor que no tenerlo— y 17 no tienen un control asociable. Esos necesitan un `id`
generado por PHP y hay que verlos caso por caso.

**Estilos inline: 2.361 → 2.199.** Aquí lo que más valía era **no** hacer el reemplazo masivo. Al
abrirlo aparecieron tres cosas que lo desaconsejan:

1. **Un `style=` inline gana a cualquier regla sin `!important`.** Cambiarlo por una clase normal no es
   neutral: `.sig-table th { text-align: left }` se impondría donde antes mandaba el inline. La
   sustitución solo es fiel si la utilidad lleva `!important`.
2. **Bootstrap 5 ya está cargado en local** (`assets/libs/bootstrap.min.css`) y sus utilidades ya son
   `!important` — `text-center`, `text-end`, `d-none`. No hacía falta CSS nuevo salvo `.u-num`
   (cifras tabulares), que Bootstrap no trae.
3. **Dos trampas que habrían roto cosas en silencio:**
   - `display:none` → `.d-none` **rompería 143 sitios** que hacen `el.style.display = '…'` por JS: el
     `!important` de la clase le gana al inline del toggle, y el elemento ya no volvería a aparecer.
     **Familia descartada.**
   - **19 vistas standalone** (constancia, ficha técnica, carnets, oficios, listas de asistencia,
     login…) **no incluyen el layout y por tanto no cargan Bootstrap**. Cambiarles un inline por una
     clase las dejaría sin estilo. **Excluidas.**

Así que se migró solo lo demostrable: los `style` cuya lista de declaraciones coincide **entera** con
`text-align:center`, `text-align:right` o `text-align:right` + `tabular-nums`, y únicamente en las 67
vistas que sí cargan Bootstrap. No hay ninguna regla `text-align` con `!important` en el proyecto, así
que no hay con quién competir y el valor calculado no cambia. Los `style` **mixtos** se dejaron
intactos. Se documentó la capa de utilidades al final de `sigtur-components.css`, explicando por qué
lleva `!important`.

> **Para quien siga con esto:** el resto de los 2.199 **no es mecánico**. Cada familia necesita su
> propio análisis de especificidad, y `color`/`padding`/`margin` sí chocan con reglas `!important` ya
> existentes (overrides del modal de Bootstrap, tema oscuro y `@media print`).

Estado tras el ciclo: **0 errores de sintaxis** en el proyecto, **81/81 pruebas** y la API de reportes
intacta.

### 2026-08-28 — Tareas programadas, generador de feriados y saneo de la documentación (sin migración)

**Las dos tareas programadas existen y ejecutan.** No había **ninguna** (`schtasks /query` vacío), y
faltaban **dos**, no una: además del respaldo —cuyo último archivo era del **26 de junio**— estaba sin
programar `cron/actualizar_estados.php`, así que **los talleres nunca pasaban solos de *Programado* a
*En Curso***: se quedaban en Programado aunque la fecha de inicio ya hubiera llegado. Se creó
`cron/instalar_tareas.ps1`, que deduce solo la ruta del proyecto y de `php.exe`, es idempotente (`/F`)
y trae `-Desinstalar`, para poder reejecutarlo tal cual en el servidor de producción. Verificado de
punta a punta: ambas tareas lanzadas desde el Programador devuelven **resultado 0** y el respaldo
produjo un `.sql` real de 253 KB.

> Corren como el usuario actual, o sea cuando ese usuario tiene sesión iniciada. En un servidor donde
> deban correr siempre, recrearlas con `/RU SYSTEM` desde una consola elevada.

**Los feriados movibles se calculan.** La mig. 071 cargó Carnaval y Semana Santa **a mano** para
2026-2028, con la advertencia de que había que agregar cada año nuevo o el conteo de vacaciones
volvería a fallar en silencio. Se acababan en 2028. Ahora `Feriado::pascua()` (algoritmo Gregoriano
anónimo, implementado a mano porque `easter_date()` vive en la extensión `calendar` **y solo llega
hasta 2037**) y `Feriado::movibles()` son **funciones puras**, y `Feriado::generarAnio()` carga lo que
falte desde la UI (`/vacaciones/feriados` → «Generar Carnaval y Semana Santa»).

La prueba de que el generador es correcto es que **reproduce exactamente las 12 fechas que la mig. 071
cargó a mano**, año por año, incluido el bisiesto. Se sumaron **14 casos** (suite 67 → **81**,
todas pasan): las 3 pascuas de referencia más 2029, los dos extremos del algoritmo (25 de abril de
2038 y 22 de marzo de 2285), el contraste con `easter_date()` en todo su rango, y una verificación de
que **en 2026-2060 cada feriado cae en su día de semana** — que es lo que atraparía un offset
equivocado. Probado también contra la BD: 2029 generó sus 4 feriados, repetirlo no duplicó nada, y
`Vacacion::diasHabiles()` bajó Semana Santa 2029 de **5 a 3** días.

Dos decisiones de diseño: regenerar **no resucita** un feriado eliminado a propósito (se omite la fecha
si ya existe una fila, activa o no), y la pantalla **avisa** cuando a los próximos 3 años les faltan
sus movibles (`Feriado::aniosSinMovibles`), porque el síntoma de este dato es que no se nota.

**Documentación saneada.** Los `REGLAS_NEGOCIO_*.md` habían quedado atrás respecto del código:

| Archivo | Qué decía de más o de menos |
|---|---|
| **Rutas** | Describía **cuatro estructuras ya eliminadas** (`instituciones_externas`, `nombre_facilitador_externo`, `ruta_inventario`, `nivel_dificultad`), daba el mapa Leaflet por pendiente estando construido, omitía el estado `Finalizada` y publicaba un formato de correlativo equivocado (`RUTA-007/2026`; el real es `007/2026`, sin prefijo) |
| **RRHH** | Cuatro secciones como «UI pendiente» o «lógica pendiente» (permisos, vacaciones, horarios, expediente) estando hechas; BRH-02/06/07 abiertas estando cerradas; y la ruta de expedientes en `public/uploads/`, **que se eliminó** (H-15) |
| **Visitantes** | BVIS-04 y BVIS-05 como pendientes: ambas hechas. **Módulo sin pendientes** |
| **Formación** | Listaba `es_brigadista` y `taller_inventario`, eliminados en la mig. 050 |
| **Inventario** | «crear las ubicaciones» como tarea pendiente: la mig. 069 sembró 25 |
| **Pasantes** | Al día; se fechó y se dejó constancia de que sus 6 brechas están cerradas |

**Dos correcciones de fondo al propio backlog:**

- **D-FO05 no era una pregunta de diseño.** El indicador *planificado vs. ejecutado* **ya está
  construido** (`meta_talleres_anio`/`meta_rutas_anio` en Configuración, leídas por
  `ReportesController`). Lo que falta es el **número real** — hoy hay 100 de relleno. Pasa de
  «decidir» a «pedir un dato» (§3.6).
- **D-NEW01 es más grande de lo que parecía.** No es cablear una llamada: las claves de correlativo
  existen desde la mig. 007 pero **nada las usa**, `oficios_emitidos` **no tiene `id_taller`**, y sobre
  todo **no se sabe qué dice el documento ni a quién se dirige**. Queda bloqueada por el cliente, en la
  misma categoría que los formatos de Bienes (§3.6).

**Un falso positivo, para que no se vuelva a levantar:** se sospechó que
`UbicacionesFormacionController.php` rompería en Linux por una discrepancia de mayúsculas con el
`ucwords()` del Router. **No es cierto:** git tiene el archivo commiteado como
`UbicacionesformacionController.php`, que es justo lo que el Router busca. La discrepancia estaba solo
en la copia de trabajo de Windows (`core.ignorecase = true`). Se alineó el disco; no hubo cambio que
commitear.

### 2026-08-27 — Bono Vacacional al motor de cálculo (mig. 073 — fase N‑D)

Las primas, el sueldo normal diario y la alícuota del bono vacacional ya las **calcula** el mismo
motor de la nómina quincenal. Al compartirlo, los dos documentos no pueden discrepar en la misma prima
del mismo trabajador. `BonoVacacional::TIPOS = Nomina::TIPOS` y `tipoPersonal()` delega, así que un
trabajador cae en la misma hoja en ambos. Los días se cuentan **a la fecha de corte** del período y no
a hoy: generar un período pasado ahora da el mismo número que dio entonces.

**El total no se pudo calcular — y no se inventó.** La fórmula del monto que se paga no está en
ninguna fuente: la plantilla documenta la *alícuota* (el devengo diario), no el total, y el mes ya
calculado que el cliente prometió el 23/07 no llegó. Se resolvió así:

- `total_calculado` = estimación del sistema bajo un supuesto **declarado**
  (`sueldo normal diario × días correspondientes`), etiquetado como estimación en la BD, la UI y el `.xlsx`.
- `total_bono_vacacional` sigue siendo la cifra oficial que confirma Talento Humano.
- La UI, el cuadro resumen y el export muestran la **diferencia** entre ambos.

Eso convierte la pregunta pendiente en un instrumento que se responde solo: **en cuanto llegue un mes
real, la diferencia dice si el supuesto acierta.** Si acierta, el total pasa a calcularse; si no, la
diferencia muestra por dónde corregir. Probado con un caso real: capturando 70.000 contra 76.467,44
calculados, el sistema muestra −6.467,44 en la fila, en el resumen y en la hoja de Excel.

Operación: `recalcular()` **preserva los totales confirmados** y el grado/escala — no pisa el trabajo
de captura; `aceptarCalculados()` toma en bloque solo los vacíos, auditado; el período **exige el mes
cargado** en `nomina_parametros_mes` porque la cesta ticket entra en el diario; y al cerrar se bloquean
las tres vías de edición (recalcular, aceptar y capturar), verificado.

**Con esto quedan hechas las fases N‑A a N‑D.** Falta solo **N‑E** (Liquidación de Prestaciones
Sociales), bloqueada por la pregunta N‑3.


### 2026-08-27 — Nómina: motor de cálculo construido (mig. 072 — fases N‑A, N‑B y N‑C)

El Bono Vacacional v1 era "registro + reporte" porque no teníamos las fórmulas. La plantilla real las
trajo, y muestran que **las primas se derivan** de cuatro entradas: sueldo base, grado de instrucción,
años en la administración pública y nº de hijos. Ya no hay que capturarlas.

**Qué se construyó**

| | |
|---|---|
| **Motor** | `Nomina::calcular()` es una **función pura** — todas las entradas explícitas, sin tocar la BD — así que se puede probar contra los valores ya calculados de la plantilla. **45 casos** en `tests/run.php` (suite: 18 → 67, todos pasan). Los intermedios no se redondean y solo se redondea la salida, como Excel. |
| **Porcentajes como datos** | `nomina_grados` (6 filas, BACH 0 % … DR 40 %) y `nomina_antiguedad` (23 filas, incrementos por tramo, tope 30 % desde el año 23). Fuera del patrón H‑07 a propósito: H‑07 centraliza valores de dominio del software, y estos son cifras de contratación colectiva. |
| **Parámetros con vigencia** | `nomina_parametros_mes`: cesta ticket y tasa del dólar **por mes**. Eran escalares sin histórico, así que un mes pasado no se podía reconstruir. Una quincena **no se puede generar** si su mes no está cargado — mejor bloquear que producir un número plausible. |
| **Entradas nuevas en la ficha** | `empleados.cuenta_nomina`/`banco_nomina`/`divisas_bono_responsabilidad`/`sueldo_dependencia_origen` y `personas.codigo_grado`, con su tarjeta "Datos de nómina" en el expediente. |
| **Quinto tipo de personal** | *Comisión de Servicio*, derivado de `institucion_origen <> 'IMATUR'` sin captura nueva. **Tiene prioridad sobre el nivel jerárquico**: un director en comisión va a su hoja, porque ahí se calcula la diferencia contra la dependencia de origen. |
| **Quincena** | `nomina_periodos` congela cesta ticket, tasa y semanas; `nomina_detalle` guarda las **entradas** además de los resultados, para auditar de dónde sale cada número. Recálculo en Borrador, inmutable al cerrar. Export de **6 hojas** con `XlsxMultiSheet`. |

**Ninguna cifra queda en silencio.** Si el grado de instrucción no se reconoce, el empleado se
**reporta** en vez de cobrar 0 % — es el defecto #7 de la plantilla del cliente. Cada fila lleva sus
`advertencias` y la vista las agrupa antes de dejar cerrar. Probado contra los 3 empleados reales de la
base: los 3 salieron con advertencias correctas, uno de ellos porque su `nivel_academico` es
«Universitario», que es ambiguo y no se mapea a ninguno de los 6 grados.

**El defecto #1 del cliente quedó fijado en una prueba.** Su hoja aplica el 30 % de antigüedad al
sueldo mensual y paga **112,80**; sobre el quincenal corresponden **56,40**. El test lo afirma con ese
número, así que cualquier cambio futuro que lo rompa se detecta.

**Las preguntas abiertas ya no bloquean.** N‑1 (días base del bono vacacional: 75 en toda la plantilla
vs. 85/45 en nuestra configuración) es una clave de configuración; N‑2 (semanas ×4/×5) se elige por
período en el propio formulario, con la contradicción explicada ahí mismo. El cálculo funciona; el
número no es definitivo hasta que el cliente confirme.

De paso: se extrajo `XlsxMultiSheet::construir()` de `descargar()` para poder verificar el `.xlsx` sin
enviarlo (comprobado: ZIP válido de 6 hojas con los datos dentro), y se **definió el CSS de
`.sig-alert`**, que se usaba en 7 vistas del módulo de Bienes sin existir en ninguna hoja de estilos —
esos avisos, incluido el que bloquea los movimientos de bienes, se renderizaban como texto plano.


### 2026-08-27 — Feriados movibles de Carnaval y Semana Santa (mig. 071)

`Vacacion::diasHabiles()` excluye fines de semana **y feriados**, y el modelo `Feriado` ya distinguía
bien los fijos (`recurrente = TRUE`, año centinela 2000, comparados por mes-día) de los movibles
(`recurrente = FALSE`, fecha puntual). El problema era de **datos**: la tabla solo tenía los 12 fijos,
sin un solo Carnaval ni Semana Santa. El sistema contaba esos 4 días como hábiles y **le descontaba a
cada trabajador vacaciones que no le corresponden** — sin error visible, solo días mal restados.

Cargados 2026, 2027 y 2028 (12 filas). Las fechas dependen de la Pascua, así que se calcularon con el
algoritmo Gregoriano anónimo y se **verificaron por dos vías**: contra `easter_date()` de PHP (las 3
pascuas coinciden) y comprobando que cada día derivado cae en su día de semana (Miércoles de Ceniza en
miércoles, Lunes de Carnaval en lunes…). Se incluye 2026 aunque ya pasó, porque los períodos se
registran de forma retroactiva.

Efecto comprobado con `Vacacion::diasHabiles()`:

| Rango | Antes | Ahora |
|---|---|---|
| Semana de Carnaval 2026 (lun-vie) | 5 | **3** |
| Semana Santa 2026 (lun-vie) | 5 | **3** |
| Semanas de control sin feriados | 5 / 10 | 5 / 10 (sin cambio) |

> **⚠️ Mantenimiento anual — resuelto el 2026-08-28.** Estos feriados no se repiten en la misma fecha,
> así que había que cargar los del año siguiente a mano o el conteo volvía a fallar en silencio. Ya
> **se calculan**: `Feriado::generarAnio()` y el botón «Generar Carnaval y Semana Santa» de
> `/vacaciones/feriados`. La pantalla además avisa si a los próximos 3 años les faltan. Ver la
> entrada del 2026-08-28.

### 2026-08-27 — Los tres defectos restantes de la auditoría (cierra H-13, H-14 y H-15)

| # | Qué se hizo |
|---|---|
| **H-15** | **Las evidencias de talleres salen del web root.** Eran el último archivo de usuario en `public/uploads/`: legibles por URL sin control de rol, y con el enlace roto bajo el vhost donde `public/` es la raíz. Ahora van a `storage/uploads/talleres/` servidas por `DescargaController::taller()` (roles 1,3). El bloque de subida estaba **duplicado** en `store()` y `cambiarEstado()` y ninguna copia validaba MIME real ni tamaño: se unificó en `TalleresController::procesarEvidencias()` con extensión + MIME real + ≤5 MB, igual que expedientes y bienes. **`public/uploads/` se eliminó por completo** (quedaban dos carpetas vacías de la migración de junio) y se limpió su bloque del `.gitignore`. La tabla `taller_evidencias` estaba en 0 filas, así que no hubo archivos que mover. |
| **H-14** | **Se retiró la columna Tarifa del reporte de rutas** (vista + export a Excel, con su fila de totales recolumnada de 15 a 14 columnas; el PDF nunca la traía). Informaba «Gratuita» para toda ruta, siempre, porque `tiene_tarifa`/`tarifa_monto` no se capturan en ningún formulario. Las columnas **se conservan** esperando D-RT02. |
| **H-13** | **`DROP TABLE actividades_ruta`** (mig. 070). Verificado antes de soltarla: 0 filas, 0 referencias en `app/` y **0 registros en `audit_logs`** — por eso, a diferencia de `id_oficio`/`instituciones_externas`, no hizo falta conservar su etiqueta en `auditoria/index.php`. Se retiró también su `setval` de `009_fix_sequences.sql`, que habría hecho fallar esa migración en cualquier instalación ya actualizada. **56 → 55 tablas.** |

De paso se corrigieron referencias muertas en `CLAUDE.md`: el módulo «ActividadesRuta» y la tabla
`ruta_inventario` (eliminada en la mig. 019) seguían listados como vigentes, y el reporte de rutas
figuraba con «filtros estado/dificultad» cuando `nivel_dificultad` se eliminó en la mig. 021.

### 2026-08-27 — El menú lateral pasa a leer el RBAC real (cierra H-12, sin migración)

El sidebar tenía los permisos cableados por número de rol en 8 bloques de `views/inc/header.php`,
mientras el Router los resolvía desde `permisos_rol`. Ahora hay **una sola definición**:
`RolesController::getNavegacion()` (token de permiso → url, etiqueta, ícono, grupo) y
`getNavegacionVisible()`, que filtra con `roleHasModulo()`. Agregar un módulo al menú = agregar una
fila. Los 8 `in_array($rol, [...])` desaparecieron.

Fallaba en los dos sentidos, y los dos quedaron corregidos:

| Caso | Antes | Ahora |
|---|---|---|
| Rol 2 (RRHH) con `PasantesController` y `UsuariosController` | Tenía el permiso, **no veía el enlace** | Los ve |
| Rol 6 (Solo Lectura) con `VisitantesController` | Tenía el permiso, no veía el enlace | Lo ve |
| Rol 5 (Recepción) **sin** `ReportesController` | Veía «Reportes» → *Acceso Denegado* | Ya no aparece |

Lo **no delegable** queda declarado en la misma tabla con `soloAdmin`: Bitácora (exclusiva del
Administrador por `AuditoriaController::guardAdmin`, mig. 055), Municipios y Parroquias (catálogos
geográficos, fuera de `getModulos()`). `VisitasController` se excluye a propósito del menú: es acceso
directo desde Visitantes. Verificado simulando los 6 roles contra `permisos_rol`.

> **Efecto colateral a tener en cuenta:** ahora el menú refleja *exactamente* lo que dice
> *Roles y Permisos*. Si RRHH no debe administrar usuarios, la corrección es quitarle
> `UsuariosController` en esa pantalla — ya no hay un segundo criterio escondido en la vista.

### 2026-08-27 — Semilla de ubicaciones: el módulo de Bienes era inalcanzable (mig. 069)

`InventarioController::store()` exige `id_ubicacion > 0` y la tabla `ubicaciones` estaba **vacía**:
era literalmente imposible registrar un bien, así que las migraciones 062-067 (cuatro fases de
trabajo) no se podían usar. La mig. 069 siembra **una ubicación por departamento activo** —el
departamento es la unidad de responsabilidad, y el responsable del bien se deriva de él (mig. 066)—
más el **Depósito General** (`es_deposito`), y asigna la `sede` de cada una: la Oficina del
Aeropuerto en *Aeropuerto de Cumaná*, el resto en *Sede Principal*. Total: 24 oficinas + 1 depósito.
Idempotente; verificada aplicándola tres veces.

Al sembrarla salió a la luz un hueco de la Fase 1: **`ubicaciones.sede` y `es_deposito` se leían en
todo el módulo** (`Inventario::LATERAL_RESPONSABLE`, `DotacionInventario`, el reporte de suficiencia,
los filtros de depósito) **pero no se escribían en ninguna parte** — no estaban en `Ubicacion::save()`,
ni en el controlador, ni en el modal. Una semilla que la UI no puede mantener no sirve, así que se
completaron: enum `Ubicacion::SEDES` (patrón H-07), columna Sede y badge *Depósito* en el listado,
selector de sede y casilla de depósito en el modal.

Los nombres de las ubicaciones arrancan iguales a los del departamento porque es el dato cierto; el
cliente los renombra a su referencia real (planta, mezzanina, cubículo) y puede crear varias por
departamento. **Sigue pendiente de datos, no de código:** cargar los ~142 bienes reales y asignar el
Coordinador de *Compra de Bienes y Servicios* (mientras el puesto esté vacante el sistema bloquea los
movimientos, por diseño B-32 — hoy el responsable derivado sale como vacante).

### 2026-08-04 — Instalación desde cero reparada: `schema_consolidado.sql` autosuficiente (sin migración)

| # | Mejora | Detalle |
|---|--------|---------|
| ✅ 🔴 Despliegue | **El consolidado quedaba 36 migraciones atrás** | `database/schema_consolidado.sql` cubría hasta la **023**, el README mandaba aplicar "024 a 052" y `CLAUDE.md` decía "024 a 039" — pero existen hasta la **059**. Cualquier instalación nueva hecha siguiendo la documentación quedaba **sin las migraciones 053–059**: foto de carnet, auditoría de login, alertas vistas, tolerancia de salida temprana, recuperación de contraseña y **todo el módulo de Nómina**. Fallo silencioso: la BD se creaba sin error y el sistema reventaba al usar esos módulos. |
| ✅ | **Regenerado desde la BD viva, autosuficiente (001–060)** | `pg_dump --no-owner --no-privileges` + `--exclude-table-data` sobre las 42 tablas operativas. Instalar = importar **un solo archivo**, sin migraciones encima. `database/migrations/` queda como historial y para actualizar instalaciones antiguas. |
| ✅ | **Catálogos institucionales sembrados** | `roles`, `permisos_rol`, `configuracion_sistema`, `departamentos` (organigrama oficial, 23), `cargos`, `horarios`, `feriados`, `municipio`, `parroquia`. Vacías las operativas (personal, inventario, talleres, rutas, visitantes, pasantes, asistencias, constancias, nómina, bitácora) y **correlativos de oficios reiniciados a 0** (antes el dump los habría dejado en constancia=17, ruta=3). |
| ✅ | **Usuario administrador de arranque** | Hueco anterior no detectado: el consolidado **no incluía ningún usuario**, y como `usuarios.id_empleado` es `NOT NULL`, una instalación nueva **no tenía forma de iniciar sesión**. Ahora un bloque `DO $bootstrap$` crea persona + empleado técnico + `admin`/`Sigtur2026` (idempotente). ⚠️ Contraseña pública en el repo — cambiar al primer ingreso. |
| ✅ | **Verificado, no asumido** | Cargado en una base vacía (`ON_ERROR_STOP=1`): **49 tablas, 0 errores**, hash bcrypt validado con `password_verify`, secuencias sin colisión. Dos fallos reales encontrados y corregidos en el proceso: (1) las columnas de auditoría `*_by` de los seeds referenciaban `usuarios.id` inexistentes; las **NOT NULL** (`municipio.created_by/updated_by`, `parroquia.create_by/update_by`) obligan a que el admin exista **antes**, así que el bloque de arranque va **entre** los datos de `departamentos` y los de `municipio`; (2) el FK circular de `departamentos.id_padre` impedía usar `--data-only` (hay que usar dump completo, que pone las constraints después de los datos). |
| ✅ Docs | **README.md + `docs/CLAUDE.md` corregidos** | Se eliminó el paso "aplicar migraciones 024–0xx" de ambos, se documentó el login de arranque y se dejó una nota de **cómo regenerar el consolidado** sin repetir los dos fallos de arriba. |

### 2026-08-05 — Bienes: 4 respuestas más del cliente implementadas (mig. 067)

| # | Respuesta | Qué se hizo |
|---|-----------|-------------|
| ✅ B-66 | *"Sí, se elimina"* | **R-10 cerrado.** Fuera `inventario.tipo_bien` y `cantidad`, más las constantes del modelo y las consultas CMI-I01/I03 que las usaban. IMATUR no lleva consumibles y el registro es individual. |
| ✅ B-67 | *"Con una etiqueta Por retirar"* | El bien dado de baja sale del inventario activo pero sigue físicamente en IMATUR hasta que la Alcaldía lo retire. Se distingue **"Dado de baja · Por retirar"** de **"· Retirado"**, con acción y fecha para confirmar el retiro. |
| ✅ B-65 | *"Como otro departamento, con su propio coordinador"* | **Verificado primero, como se pidió:** la sede del aeropuerto **no existía en ningún lado** — ni en `departamentos`, ni en el organigrama oficial (Manual Descriptivo de Cargos, abril 2024), ni en los documentos de RRHH; el único rastro era `ubicaciones.sede`. Se creó como **Oficina**, y el cliente confirmó que cuelga de la **Dirección de Planificación y Gestión Turística** (mig. 068), junto a Promoción Turística y las demás coordinaciones del área. Por la mig. 066, su coordinador es automáticamente el responsable de sus bienes. |
| ✅ B-63 | *"Por los números de empleados en los departamentos"* | Nueva tabla `inventario_dotacion` (unidades por empleado y categoría) y reporte **`/inventario/suficiencia`**: compara lo que hay en cada departamento contra lo que debería haber según su personal. Excluye el depósito (lo que está ahí no está en uso) y los bienes de baja/extraviados/robados. Sembradas 3 dotaciones de partida; las categorías que no se reparten por persona no se evalúan. |
| ✅ B-69 / B-70 / B-72 | Costo = control interno · BM-1 = evento puntual · N° de orden = jurisdicción de la Alcaldía | **Sin cambios de código**: las tres confirman el diseño actual. |
| ✅ Docs | **`REGLAS_NEGOCIO_Inventario.md` reescrito** | La versión de 2026-05-22 describía un CRUD y daba por vigentes `ruta_inventario`, `taller_inventario` y Durable/Fungible. Ahora documenta las 13 reglas reales (RN-IN01…RN-IN13) y **lo que el sistema no hace por decisión**. |
| ✅ | **Verificado** | 22 pruebas nuevas sobre la BD (departamento del aeropuerto y su responsable derivado, ciclo Por retirar → Retirado con sus rechazos, análisis de suficiencia con déficit real y exclusión del depósito) + regresión completa: 16 + 26 + 9 pruebas de las fases anteriores, suite 18/18. |

> ~~**Queda 1 sola pregunta abierta del módulo: B-71**~~ — **superado el 2026-09-02** (§3.4): B-71 y
> B-72 quedaron respondidas, pero el **cambio de procedimiento** abrió 8 preguntas nuevas
> (B-73…B-80) y **reabrió B-60**.

### 2026-08-05 — Bienes: responsable automático (mig. 066) — responde B-68 y B-72

| # | Cambio | Detalle |
|---|--------|---------|
| ✅ B-68 | **El responsable ya no se elige: se deduce** | Decisión del cliente: el responsable es la jefatura del departamento donde está el bien, y si entra alguien nuevo en ese cargo pasa a serlo de todos los bienes de su departamento. Se **eliminó** `inventario.id_responsable` y se deriva en la consulta: bien → ubicación → departamento → **Director** y, en su defecto, **Coordinador**. |
| ✅ | **Por qué derivar y no recalcular** | Una columna almacenada habría que reescribirla al cambiar un cargo, al egresar un empleado o al trasladar un bien — y basta olvidar uno de esos casos para que el inventario muestre como responsable a alguien que ya no lo es. Derivándolo, **no puede quedar desactualizado**. El histórico se conserva en `actividad_inventario`, que guarda el responsable de cada movimiento en su momento. |
| ✅ | **Bienes en depósito** | No pertenecen a ningún departamento (B-25), así que su custodio es la jefatura de la Coordinación de Bienes — la misma que autoriza los movimientos. |
| ✅ | **Se retira la asignación manual** | El movimiento "Asignación de responsable" sale de los tipos seleccionables: para cambiar de responsable se traslada el bien o cambia la jefatura. El campo del formulario se sustituyó por un indicador explicativo. |
| ⚠️ Hallazgo | **Dos consultas habrían reventado** | El reporte de inventario y el indicador **CMI-I03** usaban `i.id_responsable`, columna que esta migración elimina. Recalculados sobre la derivación; CMI-I03 ahora mide cuántos bienes están en un departamento **con jefatura asignada**, que es información útil (señala departamentos acéfalos). |
| ✅ B-72 | **N° de orden: solo se transcribe** | Respuesta del cliente: la numeración la lleva la Alcaldía con criterio propio y garantiza que no se repita; IMATUR la desconoce y solo la copia. **No hay cambio de código**: el sistema ya se limita a transcribirla. La validación de N° de orden duplicado se mantiene como red contra errores de tecleo dentro de IMATUR. |
| ✅ | **Verificado** | 9 pruebas sobre la BD con empleados reales: sin jefatura → sin responsable; entra coordinador → lo toma; entra director → tiene prioridad; **el director egresa → vuelve al coordinador solo**; bien en depósito → Coordinación de Bienes; traslado → cambia con la ubicación. Más regresión de las fases 3 y 4 (16 y 26 pruebas). |

### 2026-08-04 — Bienes, Fase 4: los 6 requisitos que no dependían de formatos (mig. 065)

| # | Entregable | Detalle |
|---|-----------|---------|
| ✅ R-4 | **Etiquetas con código + QR** | Hoja imprimible 62×30 mm con membrete, código oficial y QR que abre la hoja de vida del bien — para inventariar escaneando (B-15). Reutiliza el `qrcode.min.js` que ya estaba vendorizado y quedó sin uso tras el carnet, así que funciona **sin internet**. Solo lista bienes ya codificados: sin N° de orden no hay qué pegar. |
| ✅ R-5 | **Reportes para la Presidencia** | En vez de seis reportes casi idénticos, se añadieron filtros de **estatus, origen, departamento y "solo depósito"** al reporte de inventario: con ellos un mismo reporte cubre las listas de B-51 (activos, dañados, sin código, donaciones, por departamento, en almacén). Sin formato obligatorio (B-52). |
| ✅ R-6 | **Alertas** | Tres nuevas en el Centro de Alertas: bienes esperando código hace demasiado (B-12), garantías por vencer (B-20) y mantenimiento preventivo próximo (B-56). Umbrales editables en Configuración. |
| ✅ R-7 | **Mantenimiento preventivo programado** | `inventario_mantenimiento_plan` con frecuencia y próxima fecha. Al **retornar** de un mantenimiento el calendario avanza solo, así no se queda atrás. Un solo plan activo por bien. |
| ✅ R-8 | **Conteo por cambio de gestión** — el **dolor #2** | Al abrirlo se **congela** lo que el sistema cree tener de cada bien; luego se registra lo hallado y se comparan (B-50: estatus, lugar, condición). Un solo conteo abierto a la vez; no se puede cerrar con bienes sin verificar. **Acta imprimible** con resumen y detalle de diferencias. **No corrige los bienes automáticamente**: las diferencias se resuelven con movimientos normales, que es lo que deja rastro auditable. |
| ✅ R-9 | **Lectura/escritura por rol** (B-58) | La Coordinación de Bienes (rol 4) y el Administrador **editan**; cualquier otro rol con acceso al módulo queda en **solo lectura**. El RBAC del sistema es por controlador, no por acción, así que la distinción se resolvió acotada en los dos controladores del módulo en vez de tocar el mecanismo compartido (que afectaría a todos los módulos). 15 acciones de escritura protegidas. |
| ⏸ R-10 | **NO se hizo** | Eliminar `tipo_bien`/`cantidad` espera la confirmación del cliente (**B-66**). |
| ✅ | **Verificado** | 26 pruebas sobre la BD: plan preventivo (creación, idempotencia, rango, avance del calendario tras el retorno), conteo completo (congelado, doble apertura bloqueada, cierre con pendientes bloqueado, detección de diferencias, cierre, no-modificación de bienes), guardia de escritura para los 4 roles y las 3 alertas nuevas. |

### 2026-08-04 — Bienes, Fase 3 (parte 1): expediente documental y recepción del BM-1 (mig. 064)

Se construyó **todo lo que no depende de recibir formatos físicos**. La generación de documentos queda bloqueada hasta tenerlos.

| # | Entregable | Detalle |
|---|-----------|---------|
| ✅ | **Documentos de respaldo por bien** | `inventario_documentos` con catálogo cerrado de tipos (factura, informe de la Alcaldía, oficio de donación, acta de asignación, acta de baja, denuncia, garantía, otro). Binario **fuera del web root** (`storage/uploads/bienes/`), servido por id con control de rol vía `DescargaController` — mismo patrón ya probado en RRHH. Valida extensión **y MIME real**, máx. 5 MB. Cierra B-19. |
| ✅ | **Foto del bien** | B-21. Subida y visualización en la hoja de vida, con la misma protección. |
| ✅ | **Recepción del BM-1** | `inventario_consolidados_bm1` + pantalla `/inventario/consolidados`. Registra cada formulario que devuelve la Alcaldía, permite adjuntar el escaneado (opcional: a veces llega en papel) y **codificar los bienes desde ahí**. `inventario.id_consolidado_bm1` deja la trazabilidad de en qué formulario vino el código de cada bien — justo lo que hace falta en la auditoría por cambio de gestión. |
| ✅ | **Hoja de vida del bien** (B-36) | `/inventario/detalle/{id}`: ficha completa, foto, código oficial con su BM-1 de procedencia, documentos, mantenimientos y movimientos en una sola pantalla. Era un pedido explícito del cliente. |
| ⏳ | **Generación de documentos: pendiente** | Informe de bienes nuevos (dolor #1), acta de baja y acta de asignación. **Bloqueados por los formatos reales** — si los inventamos, habría que rehacerlos. |
| ✅ | **Verificado** | 16 pruebas sobre la BD: recepción, codificación trazable, conteo de bienes por BM-1, adjuntos con catálogo cerrado, borrado lógico y hoja de vida completa. |

> **Qué falta exactamente para cerrar el módulo (requisitos + preguntas salientes): `docs/PLAN_MODULO_BIENES.md` §12.**
> Resumen: 3 documentos bloqueados por formatos · 7 requisitos implementables ya (etiquetas QR, reportes de Presidencia, alertas, mantenimiento preventivo, conteo por cambio de gestión, RBAC del módulo, limpieza de `tipo_bien`) · 9 preguntas abiertas (B-63, B-65…B-72).

### 2026-08-04 — Bienes, Fase 2: movimientos, autorización y mantenimiento (mig. 063)

| # | Entregable | Detalle |
|---|-----------|---------|
| ✅ | **Movimientos con origen y destino** | `actividad_inventario` no registraba **de dónde a dónde** iba el bien, que es justo lo que describe B-31. Ahora sí. Los tres traslados del cliente (depósito→departamento, departamento→depósito, departamento→departamento) se modelan con **un solo** tipo `Traslado` + origen/destino: el caso concreto se deduce de las ubicaciones y los reportes no dependen de cómo se nombró el traslado. |
| ✅ | **Autorización por cargo + departamento** (B-32, B-64) | La Coordinadora de Bienes **no se elige en el formulario**: la resuelve el sistema con `ActividadInventario::autorizador()` a partir de `bienes_cargo_autoriza` + `bienes_depto_autoriza` (config, no nombres fijos en el código). Si el puesto está vacante, el módulo **bloquea el registro** y lo explica, en vez de dejar pasar movimientos sin autorizar. |
| ✅ | **Mantenimiento como proceso, no como apunte** (B-33) | Nueva tabla `inventario_mantenimientos`: encargado de Servicios Generales *o* taller externo, falla reportada, trabajo realizado, costo y resultado (Reparado / Sin reparación / Irrecuperable). Índice único parcial que impide dos mantenimientos abiertos del mismo bien. Panel de "mantenimientos en curso" en el listado. |
| ✅ | **Todo transaccional** | Un movimiento **cambia el estado del bien**, así que registro y efecto ocurren juntos o no ocurren: traslado→ubicación, asignación→responsable, salida→estatus En mantenimiento, retorno→Activo. Si el retorno es *Irrecuperable*, el bien vuelve a Activo con condición Dañado, a la espera del acto de baja (Fase 3). |
| ✅ | **Reglas de negocio validadas** | No se mueve un bien dado de baja (B-38); no se traslada al mismo sitio; no hay doble salida a mantenimiento ni retorno sin mantenimiento abierto; un bien sin codificar solo admite asignación de responsable. |
| ⚠️ Hallazgo | **CMI-I03 estaba a punto de quedar en 0** | El indicador "asignación de responsables" derivaba del último movimiento con tipo `'Asignacion'`, valor que la mig. 063 renombró. Se recalculó **directo sobre `inventario.id_responsable`** (columna de la Fase 1): más exacto y ya no depende del nombre del movimiento. |
| ✅ | **Verificado con 18 pruebas sobre la BD** | Ciclo completo: autorización obligatoria, traslado con origen/destino, asignación, salida y retorno de mantenimiento, rechazos esperados, y **atomicidad** (una FK inválida revierte el movimiento sin dejar registro huérfano). |

### 2026-08-04 — Bienes, Fase 1 construida (mig. 062) — **cierra H-04**

Primera fase del plan (`docs/PLAN_MODULO_BIENES.md` §10). El módulo deja de ser un CRUD de bienes.

| # | Entregable | Detalle |
|---|-----------|---------|
| ✅ 🔴 **H-04 CERRADO** | **`estatus` separado de `condicion`** | Era el origen del bug: ambos ejes vivían en la misma columna. Ahora `estatus` = situación administrativa (En espera de codificación · Activo · En mantenimiento · Extraviado · Robado · Dado de baja) y `condicion` = estado físico (Nuevo/Bueno/Regular/Dañado). Con el criterio del cliente: **en mantenimiento el bien NO desaparece** (B-34) y **dado de baja SÍ sale** del inventario activo conservando su registro (B-38). |
| ✅ | **Flujo de codificación contra el BM-1** | El bien nace **sin código**, en estatus "En espera de codificación". `Inventario::codificar()` transcribe grupo/subgrupo/sección + N° de orden cuando la Alcaldía devuelve el BM-1, y lo pasa a Activo. `componerCodigo()` arma `2-01-108-084`; valida partes completas y N° de orden único. Pestaña "Sin codificar" con contador en el listado. |
| ✅ | **Dos ejes de clasificación** | Código oficial (Alcaldía) **y** categoría interna (reportes de Presidencia). Se sembraron **11 categorías** y se retiraron las 2 de prueba ("Inmobiliario", "Inmuebles"). El BM-1 demostró que el código no clasifica: sillas, mesas, aire acondicionado y router comparten `2-01-108`. |
| ✅ | **Adquisición y responsable** | `origen` (Compra/Donación, con donante obligatorio si es donación), `costo_adquisicion`, `fecha_adquisicion`, `proveedor`, `tiene_garantia`+`garantia_vence`, `id_responsable` (FK empleados, **único** — B-26/27) y `foto_url`. Cierra D-IN06 y D-IN09. |
| ✅ | **Sedes y depósito** | `ubicaciones` +`sede` (Sede Principal y Oficina del Aeropuerto — B-24) +`es_deposito` (área común de los bienes sin asignar — B-23/25). |
| ✅ | **Reportes y alertas alineados** | Se corrigieron **8 consultas** en `DashboardController`, `ReportesController` y `CentroAlertas` que seguían filtrando por la condición `'En Reparación'` (ya inexistente) y que **contaban los dados de baja como activos**. El reporte de inventario suma columnas Estatus y Responsable. |
| ✅ | **Verificado con pruebas reales** | 19 comprobaciones sobre la BD ejercitando el ciclo completo: alta sin código → pendiente → codificación → duplicado rechazado → código incompleto rechazado → mantenimiento (sigue visible) → baja (desaparece del activo, se conserva). Se detectaron y corrigieron 5 warnings de PHP (`?:` sobre claves inexistentes) que habrían llenado el log en producción. Consolidado regenerado y reinstalado en BD vacía. |

> **Pendiente de la Fase 1:** `tipo_bien`/`cantidad` (mig. 044) quedaron sin uso pero **no se eliminaron** — esperan la confirmación del cliente (**B-66**). Siguen con DEFAULT, así que nada se rompe.

### 2026-08-04 — Levantamiento del módulo de Bienes + cuestionario de descubrimiento (sin migración)

| # | Entregable | Detalle |
|---|-----------|---------|
| ✅ Docs | **`docs/PREGUNTAS_DESCUBRIMIENTO_Bienes_Rutas.md`** — 123 preguntas (59 Bienes + 64 Rutas) | Redactadas **desde cero, como si el sistema no existiera**, para que el cliente describa su realidad sin quedar anclado a lo ya construido. Cuatro niveles de prioridad (⭐ define BD · ▲ afecta pantallas · ○ complementaria · 💡 propuesta nuestra), lista de 15 formatos físicos a pedir, y un anexo interno de contraste contra lo implementado. |
| ✅ Cliente | **Parte 1 (Bienes) respondida completa** | Las 59 respuestas quedaron en el propio documento. |
| ✅ Análisis | **`docs/PLAN_MODULO_BIENES.md`** — plan de reconstrucción por fases | Lo construido es un **CRUD genérico**; lo que el instituto necesita es un **expediente administrativo por bien**. Cinco diferencias de fondo: el bien nace **sin** código (lo asigna la Alcaldía tras una inspección solicitada por oficio), el código es **estructurado** (`grupo-subgrupo-sección-cantidad-N° de orden`), la baja es un **acto administrativo** firmado por Coordinadora de Bienes + Presidencia, cada bien acumula **documentos** (factura, informe, oficios), y **todo movimiento lo autoriza** la Coordinadora de Bienes. |
| ✅ Diseño | **`estatus` (administrativo) separado de `condicion` (físico)** | Origen del bug H-04: hoy se mezclan. Nuevos estatus: En espera de codificación · Activo · En mantenimiento · Extraviado · Robado · Dado de baja. **Criterio del cliente ya definido:** en mantenimiento **no desaparece** (B-34); dado de baja **sí sale** del inventario activo (B-38). H-04 se corrige en la Fase 2. |
| ⚠️ Hallazgo | **La migración 044 quedó contradicha** | `tipo_bien` (Durable/Fungible) y `cantidad` se implementaron respondiendo a D-IN05. Ahora B-07 dice que **no llevan consumibles** y B-09 que el registro es **individual** aunque se compre en lote. Ambas columnas sobran → confirmar con **B-66** antes de eliminarlas. |
| ⚠️ Hallazgo | **D-IN11 (stock mínimo) estaba mal planteada** | No es stock de papelería: no llevan consumibles. Lo que piden es un umbral de **suficiencia de mobiliario** (sillas por empleado, mesas por departamento). Replanteada como **B-63**. |
| ⚠️ Hallazgo | **Dos sedes, no una** | Además de la Sede Principal, la **Oficina de Información Turística del Aeropuerto de Cumaná** tiene bienes que también se controlan (B-24). `ubicaciones` no contempla sedes. |
| ✅ Docs | **9 preguntas nuevas (B-60…B-68)** | Las dos bloqueantes: el **catálogo oficial de grupos/subgrupos/secciones** de la Alcaldía y **3 ejemplos reales de código BN**. Más el **oficio de codificación**, que es el formato más urgente (automatizarlo ataca el dolor #1 declarado por el cliente). |
| ⏳ Pendiente | **Parte 2 (Rutas) sin responder** | Prioridad: **R-07/R-08** — si el cliente espera un catálogo de rutas reutilizable en vez de una fila por ejecución, el módulo necesita **rediseño**, no ajustes. |

### 2026-08-04 — Carnet institucional rediseñado según el modelo físico (mig. 061)

El cliente entregó el **carnet físico vigente**. Se rehízo `app/views/inc/carnet_card.php` para reproducirlo.

| # | Cambio | Detalle |
|---|--------|---------|
| ✅ 🔴 Datos | **Teléfono y correo del sistema estaban equivocados** | El carnet real trae `0293-4310178` y `Sucreimatur@gmail.com`; el sistema tenía `(0293) 431-4073` e `imatur.cumana@gmail.com`. **No eran variantes de formato, eran datos distintos.** Corregidos en `configuracion_sistema` (mig. 061). ⚠️ **El correo institucional es el remitente de la recuperación de contraseña** y aparece en constancias/oficios — las credenciales SMTP que falten (BACKLOG §3.0) deben ser de **esa** cuenta. |
| ✅ | **Dirección y lema ahora configurables** | Claves nuevas `direccion_institucion` y `lema_institucion` ("Historia y Porvenir"), editables en `/config` → Contacto Institucional. No quedaron fijas en el código. |
| ✅ | **Diseño alineado al carnet real** | Logo de la Alcaldía arriba-izquierda; "IMATUR" grande con perfilado blanco y RIF debajo; **unidad de adscripción en vertical** sobre el margen izquierdo (tamaño de fuente automático según largo); foto **circular con aro dorado**; apellidos y nombres en líneas separadas alineados a la derecha; cédula con separadores de miles; contacto con iconos circulares al pie; lema sobre la franja inferior. |
| ✅ | **Tipo de credencial conservado** (decisión del cliente) | El modelo físico no los trae, pero se mantienen: insignia **TRABAJADOR/PASANTE** + **FIJO/CONTRATADO**, integradas al bloque de identidad en vez de centradas como antes. |
| ✅ | **Pasantes: institución en vertical** | Donde el trabajador lleva su departamento, el pasante lleva su **institución educativa** (decisión del cliente). Antes mostraba Carrera + Institución como líneas de datos. |
| ⏳ | **Falta el arte del fondo** | El degradado, la marca de agua y la foto de Cumaná al pie **todavía no los tenemos**. Se aproximan con CSS. Está preparado para incorporarlo sin tocar código: basta dejar el archivo en `public/assets/images/carnet_fondo.png` y la vista lo detecta (`is_file`) y sustituye el degradado. |
| ✅ | **Verificado** | Renderizado real contra la BD (empleado y pasante), no solo `php -l`. Se corrigió un fallo detectado al probar: la cédula se formateaba con `number_format((int)…)`, que **descartaba los ceros a la izquierda** (`00123456` → `123.456`); ahora se agrupa sobre la cadena. Probado con 7 casos incluidos cédula vacía y ya formateada. |

### 2026-08-04 — Limpieza de columnas y tablas inertes (mig. 060) — cierra H-09 y H-10

Auditoría: estas estructuras existían en la BD pero **ninguna parte del sistema las escribía**. Eran peso muerto y, en un caso, hacían que un reporte mostrara datos falsos. Decisión del cliente: eliminarlas.

| # | Eliminado | Por qué |
|---|-----------|---------|
| ✅ | `rutas.nombre_facilitador_externo` | Solo se **leía** en el reporte de Rutas (`ReportesController::rutas`), nunca se capturaba en ninguna pantalla → siempre NULL. Cierra **D-RT04**. |
| ✅ | `participantes_ruta.id_institucion` + tabla `instituciones_externas` | `RutasController` insertaba **siempre `null`**; la tabla quedó en 0 filas y sin UI desde que se retiró el módulo de instituciones externas (2026-05-31). Cierra **D-RT05** (el indicador CMI de "instituciones participantes" queda descartado). |
| ✅ | `talleres.id_oficio` + tabla `oficios` | Cero referencias en `TalleresController`, modelo `Taller` y vistas. `oficios` (oficios **recibidos**, externos → IMATUR) nunca tuvo CRUD; sus 2 únicas filas eran basura de prueba (asuntos `"klkkl"`, `"kjhgfd"`). Cierra **D-FO06**. |
| ⏸️ | `rutas.tiene_tarifa` / `tarifa_monto` | **NO se eliminó**: sigue pendiente de decisión del cliente (D-RT02). *Actualización 2026-08-27:* ya **no se lee en ninguna parte** — se retiró del reporte porque informaba "Gratuita" siempre (H-14). Las columnas quedan inertes de verdad, esperando D-RT02. |

- **No confundir:** `oficios_emitidos` (oficios **salientes** generados desde rutas) sí está en uso y no se tocó.
- **Código ajustado:** `Ruta::inscribir()` pierde el parámetro `$id_institucion` (firma nueva: `(id_ruta, id_persona, user_id, observaciones)`), `Ruta::inscribirLibre()` y `RutasController` dejan de enviarlo, y el `COALESCE` del facilitador en `ReportesController` se simplifica.
- **Se conservaron a propósito** las etiquetas `'id_oficio'` (`auditoria/index.php`) e `'instituciones_externas'` (`dashboard/index.php`): son diccionarios de visualización de la **bitácora histórica**, no referencias vivas. Hay 18 registros de `audit_logs` cuyo JSON las menciona; sin la etiqueta se mostrarían con el nombre crudo de la columna. Ambas quedaron comentadas explicando esto.
- **Verificado:** migración aplicada (51 → 49 tablas), `php -l` en los 5 archivos tocados, los dos flujos de inscripción a ruta (con cédula y libre) probados con `INSERT` real + `ROLLBACK`, la consulta del reporte de Rutas ejecutada contra la BD migrada, y suite `php tests/run.php` 18/18 ✓. Consolidado **regenerado** y reinstalado desde cero en una BD vacía (49 tablas, 0 errores).
- **Limpieza extra:** se eliminaron `database/schema.sql` y `database/schema_completo.sql` (obsoletos: cubrían hasta la 011 y el base original; generaban dudas sobre cuál importar). Recuperables desde el historial de git.

### 2026-07-13 — UX: botón "Siguiente" del asistente de empleados sin feedback de error (sin migración)

| # | Mejora | Detalle |
|---|--------|---------|
| ✅ Fix UX | **Wizard de empleados**: "Siguiente" quedaba `disabled` sin explicar qué campo fallaba | `wzUpdateNav()` (`empleados/form.php`) ya no deshabilita `#wzNext`; se deja siempre clickeable para que `wzValidateStep()` pueda ejecutar `reportValidity()` sobre el primer campo inválido al hacer clic (globo nativo del navegador señalando el campo exacto). Antes, al estar `disabled`, el `onclick` nunca se disparaba y el usuario no tenía ninguna pista. |
| ✅ Fix UX | **RIF**: sin feedback visible mientras se escribía un valor mal formado | `initRifInput()` (`sigtur-validations.js`, se auto-adjunta a cualquier input con token `rif` en name/id) ahora inserta un `<small class="sig-rif-msg">` bajo el campo que muestra en rojo "RIF no válido. Formato: J-12345678-9." en vivo mientras se escribe, igual patrón que "Cédula disponible". Aplica automáticamente a los dos campos RIF del sistema (empleados y RIF institucional en `/config`). |

### 2026-07-11/12 — Bitácora inmutable, notificaciones, auditoría de reportes, recuperación de contraseña (mig. 054–058)

| # | Mejora | Detalle |
|---|--------|---------|
| ✅ Bitácora inmutable | **Asistencias y visitas ya NO son eliminables** | Se quitó el botón "Eliminar" (y el endpoint completo, no solo la UI) de `asistencias/index.php` y `visitantes/index.php`; reemplazado por "Ver detalles" (modal). Son bitácora/auditoría, no un CRUD editable. |
| ✅ Asistencia | **Motivo obligatorio si el empleado marca salida antes de su horario** (mig. 056) | Tolerancia configurable (`minutos_tolerancia_salida_temprana`, default 10 min), independiente de la tolerancia de puntualidad de entrada. Editable en `/config`. |
| ✅ Notificaciones | **Campana "tipo Facebook"**: alertas ya vistas no reaparecen (mig. 057) | `alertas_vistas` (fingerprint por usuario+clave de alerta). Reaparecen SOLO si cambia el conjunto de registros que las componen (ej. sube el número de contratos por vencer), nunca por simple paso del tiempo. |
| ✅ Empleados | **Listado principal**: badge de tipo de contrato (Fijo/Contratado/Suplente/Comisión), columna Contacto, filtro por Cargo, badge Grupo A/B (rotación) | `empleados/index.php` |
| ✅ Reportes/listados | **Auditoría completa (~18 hallazgos) cerrada**: Directorio de Personal (tel/correo/vencimiento), Amonestaciones (cédula/cargo/última fecha), Egresos (departamento/tiempo servicio), Constancias (cargo/depto/filtro tipo), Rutas (departamento/tarifa/guía externo/filtros), Visitantes (hora salida/atendido por), Pasantes (contacto/nota, + fechas en el listado), Bajas de Inventario (motivo), Inventario (filtros server-side) + bloque transversal (buscador/paginación en 6 reportes que no lo tenían + botón exportar en listados de tarjetas de Talleres/Rutas) | Ver detalle en `ReportesController.php` |
| ✅ Seguridad | **Recuperación de contraseña por correo** (autoservicio, mig. 058) | Token de un solo uso (30 min, hash sha256), PHPMailer vendoreado sin Composer (`app/libs/PHPMailer`). Remitente = correo institucional (`configuracion_sistema.correo_institucion`). **Pendiente:** credenciales SMTP reales (proveedor sin definir aún) — hoy el envío falla de forma controlada. |
| ✅ Seguridad | **Login acepta usuario o correo** | Resuelve "olvidé mi usuario" sin flujo aparte — si recuerda su correo, no necesita el username. |
| ✅ Seguridad | **Egreso desactiva automáticamente el acceso del empleado; reingreso lo reactiva** | Antes el usuario de acceso quedaba huérfano y activo indefinidamente tras un despido/renuncia — brecha confirmada y cerrada. `Empleado::procesarEgreso()`/`reingresar()`. |
| ✅ Fix | **Cédula sin normalizar en Visitantes/Pasantes/Búsqueda global** (mismo bug que rompió Talleres/Rutas días atrás) | `Visitante::buscarPorCedula/crear/store`, `Pasante::findPersonaByCedula/createPersona/updatePersona`, `BuscarController` ahora normalizan a solo-dígitos antes de buscar/guardar (mig. 037). Verificado con auditoría completa del sistema: patrón de JS que causó el bug original (script abortado por `getElementById` sin guarda) confirmado como caso aislado, no sistémico; RBAC/`permisos_rol` sin discrepancias. |
| ✅ Fix | **`CargaFamiliar`**: cédula normalizada + anti-duplicado **por empleado** (no global) | La misma cédula de familiar SÍ puede repetirse legítimamente entre empleados distintos (hermanos que declaran al mismo padre, cónyuges que ambos trabajan en la institución). Solo se bloquea el doble registro accidental del mismo familiar para el mismo empleado. |
| ✅ Migraciones | **054/055 aplicadas** (estaban pendientes desde hacía semanas) | 054: `audit_logs.operacion` acepta `LOGIN`/`LOGIN_FALLIDO` (antes fallaba en silencio, `/reportes/accesos` siempre vacío). 055: bitácora general exclusiva de Admin (0 filas afectadas, ya sin concesiones previas). |

### 2026-06-28/29 — Carnetización + UX (mig. 053)

| # | Mejora | Detalle |
|---|--------|---------|
| ✅ Carnetización | **Carnets CR80 imprimibles** (empleados y pasantes) | Formato credencial 54×85.6mm una cara, colores institucionales, `window.print()`. Foto por persona (`personas.foto_url`, mig.053) en `storage/uploads/fotos/`, servida por `DescargaController::foto`; subida con `Controller::guardarFotoPersona()` (MIME real). Partial compartido `inc/carnet_card.php`. Sin RIF/vigencia/QR por decisión del cliente (QR vendorizado queda disponible). |
| ✅ Dashboard | **Tarjeta "Pasantes (Visitas)"** (Recepción) + **KPI "Ausencias del mes"** (RRHH, tabla `faltas`, distinto de Impuntualidad) | `DashboardController` |
| ✅ UX | **Breadcrumb dinámico** en el header (Inicio / Grupo / Sección / Página) | `$___bcMap` en `header.php` |
| ✅ Docs | **`docs/PREGUNTAS_CLIENTE.md`** — lista consolidada de preguntas para el cliente (espejo de §3) | — |

### 2026-06-25 — Análisis profundo: Lote 5 (integridad) + Lote 6 (UX/a11y/README)

| # | Mejora | Detalle |
|---|--------|---------|
| ✅ Seguridad | **Anti-IDOR en borrados** | `eliminarFamiliar/Curso/Experiencia` validan pertenencia a la persona del empleado; `eliminarDocumento` valida `id_empleado`. |
| ✅ Verificación | **Transacciones** | Revisado: ya están aplicadas donde se requieren (`Empleado::save/egreso/reingreso/traslado`, Pasantes, Roles, ConfigSistema). Los demás guardados son de una sola sentencia (atómicos); `guardarCargaFamiliarInicial` es best-effort por diseño. **Sin cambios necesarios.** |
| ✅ UX | **Header móvil** | El buscador inline se oculta en <576px (queda campana/tema/perfil). |
| ✅ a11y | **Labels/aria** | `login` con `label[for]`+`autocomplete`; `aria-label` en campana y botón de tema. |
| ✅ Docs | **README.md** | Instalación, config (`config.example.php`), migraciones, crons (`schtasks`), restauración de respaldos, pruebas, estructura. |

### 2026-06-25 — Análisis profundo: Lote 2 (proteger uploads) + Lote 4 (cache de alertas)

| # | Mejora | Detalle |
|---|--------|---------|
| ✅ Confidencialidad | **Documentos privados fuera del web root** | Recaudos y docs de pasantes movidos a `storage/uploads/` (no accesibles por URL). Nuevo `DescargaController` sirve por **id de registro** con verificación de rol + `is_active` + `basename()` (sin path traversal). Vistas enlazan a `/descarga/...`. Archivos existentes migrados; valores antiguos siguen resolviéndose. |
| ✅ Seguridad | **Validación MIME en subida** | `EmpleadosController`/`PasantesController` validan extensión **y** `mime_content_type`. |
| ✅ Rendimiento | **Cache de alertas en sesión** | `CentroAlertas::resumenCacheado` (TTL 120s) usado por la campana del header; se invalida al abrir `reportes/alertas`. Evita recomputar roster/faltantes/config en cada página. |

> Residual: dos documentos de pasante quedaron en el **historial de git** (commiteados antes del `.gitignore`); se quitaron del tracking ahora. Si se requiere borrarlos del historial, hace falta reescritura (BFG/`git filter-repo`) — repo interno, prioridad baja.

### 2026-06-25 — Análisis profundo: Lote 1 (seguridad rápida) + Lote 3 (índices, mig. 052)

| # | Mejora | Detalle |
|---|--------|---------|
| ✅ Seguridad | **Errores de producción** | `public/index.php` con `display_errors` según `APP_DEBUG`, `log_errors` y `set_exception_handler` (página 500 limpia). `Database` ya no filtra el detalle del error de conexión. |
| ✅ Seguridad | **Cookie de sesión endurecida** | `httponly` + `samesite=Lax` (+ `secure` con HTTPS) antes de `session_start()`. |
| ✅ Seguridad | **Secretos fuera del repo** | `config/config.php` deja de versionarse (`.gitignore`); plantilla `config/config.example.php`. **Acción operativa:** cambiar la contraseña real de PostgreSQL. |
| ✅ Rendimiento | **Índices (mig. 052)** | 5 índices nuevos en tablas que crecen (participantes_ruta, actividad_inventario, personas/parroquia, audit_logs); verificado que no duplican los existentes. |

> Correcciones del análisis: el "SQL injection crítico en `Taller::actualizarPersona`" era **falso positivo** (claves de columna fijas en el controlador, no input). El "upload de PHP" está mitigado por whitelist de extensión (el riesgo real es de *fuga*, ver §5.2).

### 2026-06-25 — Respaldos automáticos de BD (sin migración)

| # | Mejora | Detalle |
|---|--------|---------|
| ✅ Continuidad | **Respaldo automático de la base de datos** (`cron/respaldo_bd.php`) | `pg_dump` (SQL plano) a `storage/backups/` con nombre fechado + **rotación** (conserva `BACKUP_RETENTION`=14). Carpeta fuera de `public/` y con `.gitignore`. `PG_DUMP_PATH`/`BACKUP_RETENTION` en config. Programable en el Programador de tareas de Windows; restaurar con `psql -f`. Probado: genera dump válido (92 CREATE/COPY). |

### 2026-06-25 — Calidad: pruebas, normalización de fin de línea y manual (sin migración)

| # | Mejora | Detalle |
|---|--------|---------|
| ✅ Pruebas | **Suite mínima sin dependencias** (`tests/run.php`, `php tests/run.php`) | 18 checks de lógica pura sin BD: política de contraseñas, vacaciones (derecho/antigüedad/acumulado), `Util::edad`, `Empleado::tiempoServicio`. |
| ✅ Repo | **`.gitattributes`** | Normaliza fin de línea a LF y marca binarios — elimina el ruido "LF will be replaced by CRLF". |
| ✅ Docs | **Manual de usuario por rol** (`docs/MANUAL_USUARIO.md`) | Guía práctica: acceso/seguridad, interfaz, roles, módulos, reportes, campana, búsqueda, perfil y FAQ. |

### 2026-06-25 — UX/seguridad: campana, búsqueda global, accesos, filtro de año (sin migración)

| # | Mejora | Detalle |
|---|--------|---------|
| ✅ Vista de accesos | **Reporte de accesos al sistema** (`reportes/accesos`, rol 1) | Inicios de sesión e intentos fallidos desde `audit_logs` (AuthController ahora registra `LOGIN`/`LOGIN_FALLIDO`); filtros usuario/tipo/fecha + export. |
| ✅ Centro de notificaciones | **Campana en el header** | `CentroAlertas::resumen($rol)` (fuente única, reusada por `reportes/alertas`); dropdown role-aware con badge de conteo accionable. |
| ✅ Filtro de período | **Selector de año en Indicadores** | `?anio=` gobierna los indicadores anuales del panel; métricas "del mes" y tendencias siguen relativas a hoy. |
| ✅ Búsqueda global | **Buscador en el header** (`BuscarController`) | Empleados / inventario / talleres / rutas / visitantes, **gated por rol**; acceso permitido a todo usuario autenticado en el Router. |

### 2026-06-25 — Bloque CMI de indicadores (sin migración)

Alineación del panel `reportes/indicadores` con el *Cuadro de Mando Integral* del documento del proyecto. 8 indicadores nuevos (prefijo `CMI-*` en `INDICADORES_GESTION.md`), solo lectura sobre datos existentes:

| # | Indicador | Fórmula |
|---|-----------|---------|
| ✅ CMI-RH01 | Cumplimiento de jornada | horas reales / programadas (mes, días con marcaje completo + horario) |
| ✅ CMI-RH02 | Precisión de asistencia | registros con salida / total (mes) |
| ✅ CMI-RH03 | Documentación del personal | empleados con recaudos obligatorios completos / total |
| ✅ CMI-I01 | Precisión del registro (inventario) | durables con código BN (+fungibles) / total |
| ✅ CMI-I02 | Movimientos de bienes | conteo por `tipo_movimiento` (año) |
| ✅ CMI-I03 | Asignación de responsables | durables con último movimiento = Asignación / total durables |
| ✅ CMI-F01 | Cobertura por parroquia | parroquias con actividad / total (año) |
| ✅ CMI-T01 | Frecuencia de rutas | rutas finalizadas por mes (6 meses) |

> Archivos: `ReportesController::indicadores()` + `views/reportes/indicadores.php`. Pendientes del documento que **no** se implementaron (ver 3.4 y 3.5): stock mínimo, instituciones participantes en rutas, tiempo de generación de reportes.

### 2026-06-25 — Endurecimiento de login + optimización N+1 (mig. 051)

| # | Mejora | Detalle |
|---|--------|---------|
| ✅ Seguridad | **Endurecer el login** | Bloqueo tras 5 intentos fallidos por 15 min (`usuarios.failed_attempts`/`locked_until`), política de contraseñas (mín. 8 + letra y número), mensaje genérico anti-enumeración, `session_regenerate_id`, expiración de sesión por inactividad (`SESSION_TIMEOUT`=30 min en el Router). |
| ✅ Rendimiento | **Optimizar N+1 de documentación** | `ExpedienteDocumento::faltantesObligatorios()` + `entregadosPorEmpleado()` (consultas agregadas) reemplazan el bucle `recaudosEstado()` por empleado en `indicadores()`, `alertas()` y `expedientesIncompletos()`. |

> Migración **051** (`usuarios_seguridad_login`): `+failed_attempts/locked_until/last_login`. Idempotente.

### 2026-06-25 — Bloque B (reportes implementables, sin migración)

| # | Reporte | Ruta · Roles |
|---|---------|--------------|
| ✅ BRH-07 | **Saldo de vacaciones** por empleado (años servicio, derecho, acumulado, ajuste, disfrutado, saldo) | `reportes/vacacionesSaldo` · 1,2 |
| ✅ D-RE01/02 | **Informe trimestral de Formación** (actividades/finalizadas/canceladas/inscritos/atendidos + género por trimestre, filtro por año) | `reportes/formacionTrimestral` · 1,3 |
| ✅ BRT-05 | **Ejecuciones de ruta** (rutas Finalizadas por fecha, participantes y atendidos; filtros año/tipo) | `reportes/ejecucionesRuta` · 1,3 |
| ✅ BVIS-05 | **Estadísticas de visitas** (afluencia por mes, visitantes únicos, situación del día) | `reportes/estadisticasVisitas` · 1,2 |
| ✅ BVIS-04 | **Visitas activas del día** en el Dashboard (`kpiVisitasActivas` = entradas de hoy sin salida) | Dashboard · 1,2,5 |

> Quedan del Bloque B: **formato físico imprimible de asistencia** (necesita el formato real del cliente, ver 5) y **`taller_facilitadores`** / **importación de históricos** (condicionados a decisión).

### 2026-06-21 (mig. 043–050)

| # | Entregable | Migración |
|---|-----------|-----------|
| ✅ | **Export Excel/PDF transversal** en todo listado `data-tabla-buscable` (`sigturExportarTabla`, opt-out `data-no-export`) | — |
| ✅ | **RIF institucional centralizado** en `ConfigSistema::rif()` + `window.SIGTUR_RIF` (oficial G-20008498-7) | 043 |
| ✅ | **Inventario Durable/Fungible** (`tipo_bien`+`cantidad`, validación por tipo) — cierra D-IN05 | 044 |
| ✅ | **Vacaciones (días)**: 15 hábiles +1/año tope 30, antigüedad total, feriados, saldo acumulado + ajuste inicial (`/vacaciones`) — cierra D-RH04/05 (parte de días) | 045/046 |
| ✅ | **3C** badge "Elegible a fijo" (señal visual, no promueve) | — |
| ✅ | **3D** Traslado de departamento = reasignación con historial (`empleado_traslados`) | 047 |
| ✅ | **3E** Faltas con `tipo` (injustificada/incumplimiento) + escalado falta→amonestación (`id_falta_origen`) | 048 |
| ✅ | **U4** Alertas de vencimiento: talleres vencidos (Dashboard + Centro de Alertas role-aware con contratos/pasantes) | — |
| ✅ | **O4** Filtro por departamento en lista de empleados · **O5** horario Estándar 8am-4pm→8am-2pm | 049 |
| ✅ | **3F** Limpieza: eliminados `taller_inventario` (D-FO07) y `es_brigadista` (D-FO08) | 050 |
| ✅ | **Fix UI:** `js-search` inflaba la altura dentro de `.sig-field` (flex-column) — corregido en CSS | — |

> Bloques 1 (revisión profesor) y 2 (UX) **cerrados**; la mayoría ya estaba hecho al verificar. Único pendiente real del Bloque 1: **B13** (ver 3).

---


---

# Parte 2 — Notas de cabecera de `CLAUDE.md` (archivo)

Resúmenes técnicos que encabezaban `CLAUDE.md`. Se conservan porque varios traen detalle de
implementación que no quedó en la Parte 1 (en especial la migración 059, Bono Vacacional v1).
Solapan parcialmente con las entradas de arriba.

**Anterior:** 2026-09-17 — **BIENES: llegaron 2 de los 4 formatos y quedaron construidos (mig. 075).** El **oficio de relación de bienes nuevos** a la Alcaldía (`/inventario/relaciones`, modelo `RelacionBienes` + tabla `inventario_relaciones` + `inventario.id_relacion`) y el **documento de donación** (hoja de vida del bien → «Documento de donación», con los campos nuevos del donante y `Util::montoALetras()`/`fechaEnLetras()`). ⚠️ **El oficio entregado es el del procedimiento ANTERIOR al cambio del 2026-09-02**: pide que la Alcaldía codifique y su tabla no tiene columna de código. Se construyó **fiel a lo entregado**; si el cliente confirma el formato nuevo (**B-81**), basta agregar la columna en `relacion_imprimible.php` — el modelo ya guarda el código. Siguen faltando el **Acta de Desincorporación** y el **acta de asignación**. La mig. 075 además **corrige la resolución y la gaceta** que el sistema imprimía en 5 documentos reales (eran de relleno; las reales son Resolución 32 y Gaceta 87, ambas del 05/09/2025) y el cargo, ahora **Presidenta**. Leer `docs/PLAN_MODULO_BIENES.md` **§2-quater**.

**Anterior:** 2026-09-03 — ⚠️ **BIENES: la Alcaldía cambió el procedimiento de codificación (notificado el 2026-09-02).** IMATUR pasa a **asignar el código de sus propios bienes**, continuando la secuencia desde el último que la Alcaldía deje en su última revisión (que **aún no se ha hecho**); la Alcaldía ya no viene a codificar, solo recibe la **relación** de bienes nuevos con código y monto. El acta de baja pasa a ser **Acta de Desincorporación**, **por lote**, firmada y sellada por la Alcaldía como aval — **el oficio de retiro se elimina del alcance**. **Nada de esto está implementado:** hoy `Inventario::codificar()` transcribe el código de un BM-1 recibido y pone `verificado_alcaldia = TRUE`, y `marcarRetirado()` confirma el retiro bien por bien. Antes de tocar Inventario **leer `docs/PLAN_MODULO_BIENES.md` §2-ter** (consecuencias C-1…C-7, **B-60 reabierta** porque ahora IMATUR clasifica y hace falta el catálogo de grupos/subgrupos/secciones, y 8 preguntas nuevas B-73…B-80). Reglas actualizadas en `REGLAS_NEGOCIO_Inventario.md` RN-IN02/RN-IN03/RN-IN09.

**Anterior:** 2026-08-27 (d) — **Bono Vacacional migrado al motor de cálculo (mig. 073, fase N-D).** Las primas, el diario y la alícuota se calculan con el mismo motor de la quincenal; los 5 tipos de personal salen de una sola definición. **El total sigue confirmándose a mano a propósito**: su fórmula no está en ninguna fuente del cliente, así que el sistema guarda su estimación (`total_calculado`) al lado del confirmado y muestra la diferencia, en vez de afirmar un número que no puede sostener. Falta solo **N-E** (Liquidación, bloqueada por N-3).

**Anterior:** 2026-08-27 (c) — **Nómina: motor de cálculo construido (mig. 072, fases N-A y N-B del plan).** Las primas ya NO se capturan: se derivan de sueldo base, grado de instrucción, años en la administración pública y nº de hijos. Modelo `Nomina` con `calcular()` **pura** (sin BD), 45 casos de prueba contra los valores de la plantilla real — incluido el que destapa el defecto #1 del cliente (prima de antigüedad del tramo ≥23 años: 56,40 correctos vs. 112,80 que paga su hoja). Porcentajes en tablas (`nomina_grados`, `nomina_antiguedad`), cesta ticket y tasa del dólar **por mes con vigencia**, quinto tipo de personal (Comisión de Servicio), nómina quincenal con períodos/snapshot/recálculo/cierre y **export de 6 hojas**. Un valor de grado no reconocido **se reporta**, no se paga como 0 % en silencio (defecto #7 del cliente). **Leer `docs/PLAN_MODULO_NOMINA.md` antes de tocar el módulo.**

**Anterior:** 2026-08-27 (b) — **Cierre de los 4 defectos abiertos + feriados movibles (mig. 070-071).** (1) Las **evidencias de talleres** eran el último archivo de usuario en `public/uploads/`: legibles por URL sin control de rol y con el enlace roto bajo el vhost donde `public/` es la raíz. Ahora van a `storage/uploads/talleres/` servidas por `DescargaController::taller()`, con el bloque de subida unificado en `TalleresController::procesarEvidencias()` (antes duplicado y sin validar MIME real ni tamaño). **`public/uploads/` ya no existe — no reintroducirlo.** (2) **H-14**: se retiró la columna Tarifa del reporte de rutas (informaba «Gratuita» siempre). (3) **H-13**: `DROP TABLE actividades_ruta` (mig. 070), 56 → 55 tablas. (4) **Feriados movibles** de Carnaval y Semana Santa 2026-2028 (mig. 071): faltaban por completo y el conteo de vacaciones descontaba 4 días hábiles de más al año.

**Anterior:** 2026-08-27 — **El menú lateral pasa a leer el RBAC real (cierra H-12) + semilla de ubicaciones (mig. 069).** El sidebar tenía los permisos cableados por número de rol en 8 bloques de `header.php` mientras el Router los resolvía desde `permisos_rol`; ahora se genera con `RolesController::getNavegacion()`/`getNavegacionVisible()` filtrando por `roleHasModulo()` — **ver la sección «Sidebar» del RBAC antes de tocar el menú**. La mig. 069 siembra `ubicaciones` (una por departamento + Depósito General, con su sede): sin esas filas era **imposible registrar un bien**, así que las fases 1-4 del módulo (mig. 062-067) estaban inalcanzables. De paso se completó un hueco de la Fase 1: `ubicaciones.sede`/`es_deposito` se leían en todo el módulo pero no se escribían en ninguna parte.

**Anterior:** 2026-08-04 — **Módulo de Bienes: Fases 1, 2 y 4 completas, Fase 3 parcial** (mig. 062-065): `estatus` separado de `condicion` (cierra H-04), código oficial por partes con flujo de codificación contra el BM-1, datos de adquisición, responsable único, sedes y 11 categorías internas. **Fase 2**: movimientos con origen/destino y autorización por cargo+departamento, mantenimiento con salida/retorno registrado (`inventario_mantenimientos`), todo transaccional. **Fase 3**: expediente documental por bien, recepción del BM-1 y hoja de vida; falta la generación de documentos (bloqueada por los formatos del cliente). **Fase 4**: etiquetas con QR, reportes filtrables para la Presidencia, alertas de garantía/preventivo/sin codificar, mantenimiento preventivo programado, conteo por cambio de gestión con acta, y **lectura/escritura por rol** (`InventarioController::puedeEscribir()`, B-58). **Lo único que falta son 3 documentos bloqueados por los formatos del cliente: `docs/PLAN_MODULO_BIENES.md` §12.** Antes: **Módulo de Bienes en replanteamiento**: el levantamiento con el cliente (59 preguntas respondidas) mostró que lo construido es un CRUD genérico y lo que hace falta es un expediente administrativo por bien; plan por fases en `docs/PLAN_MODULO_BIENES.md`. Antes en la misma fecha: carnet rediseñado según el modelo físico (mig. 061), limpieza de columnas inertes (mig. 060) y **`database/schema_consolidado.sql` regenerado y ahora es autosuficiente (001–068)**: instalar desde cero = importar ese único archivo, sin migraciones encima. Incluye catálogos institucionales sembrados y un administrador de arranque (`admin`/`Sigtur2026`, cambiar al primer ingreso). Verificado cargándolo en una BD vacía: 49 tablas, 0 errores. Antes, el consolidado cubría solo hasta la 023 y el README mandaba aplicar 024–052, así que **toda instalación nueva quedaba sin las migraciones 053–059** (carnet, auditoría de login, alertas vistas, recuperación de contraseña, nómina).

**Anterior:** 2026-07-16 (migración 059; **Nómina — Bono Vacacional v1** "registro + reporte": historial salarial por empleado (`empleado_salarios`), módulo `/nomina` (períodos + captura/edición + cierre), escritor OOXML multi-hoja reusable `XlsxMultiSheet` para exportar en el formato exacto que exige la Alcaldía; días base por tipo de personal configurables. Liquidación de Prestaciones Sociales queda para una 2da entrega — ver `docs/BACKLOG.md` §3.1 — suite `php tests/run.php` 18/18 ✓)  
