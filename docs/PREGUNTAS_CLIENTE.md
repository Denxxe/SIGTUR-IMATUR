# Lo que necesitamos de IMATUR para cerrar el sistema

**Para:** IMATUR · **Actualizado:** 2026-09-03 (recoge el cambio de procedimiento de Bienes del 2026-09-02)

El sistema está construido y funcionando. Lo que falta para dejarlo al 100 % **ya casi no es
programación**: son documentos, datos y tres confirmaciones que dependen de criterios de la institución.

Este documento lista **exactamente** eso, ordenado por lo que desbloquea. Pueden responder debajo de
cada punto.

---

## Resumen: qué falta y qué desbloquea

| | Cuántos | Qué desbloquea |
|---|---|---|
| **A. Formatos y respuestas que bloquean programación** | 9 | Los 4 documentos que el sistema no puede generar todavía, la **nueva codificación de bienes** y el módulo de Liquidación |
| **B. Datos para poner en marcha** | 7 | Que el sistema deje de estar vacío. Sin esto está correcto pero no sirve |
| **C. Confirmaciones que afinan números ya calculados** | 3 | El cálculo ya funciona; estas respuestas lo vuelven definitivo |
| **D. Decisiones opcionales** | 5 | Mejoras que solo construimos si las quieren |

**Los cuatro puntos más urgentes:** la **relación de bienes nuevos** con el formato nuevo (A1), el
**archivo digital del inventario de la encargada** (A7), el **mes de bono vacacional ya calculado**
(A5) y, de Rutas, **los nombres de los estados y cómo funciona el cobro** (A6 — el resto de ese
cuestionario ya lo respondieron y con eso arrancamos).

---

# A · Formatos y respuestas que bloquean programación

Sin estos no podemos construir. Todo lo demás del sistema ya está hecho.

> ### Nota del 2026-09-02 — nos avisaron del cambio de procedimiento en Bienes
>
> Entendido: como IMATUR es un ente autónomo, después de la **última revisión y asignación de códigos
> de la Alcaldía** (que todavía está pendiente), **IMATUR asignará el código de sus propios bienes**
> continuando la secuencia desde el último que les dejen, y la Alcaldía solo recibirá la **relación**
> de los bienes nuevos —con su código y su monto— para mantener su registro. **La base del sistema
> sirve igual**; ajustamos quién ejecuta la codificación. Los puntos A1, A2 y A7 recogen lo que
> necesitamos para eso.

### A1 ⭐ Relación / informe de bienes nuevos — **el más urgente**

El documento que IMATUR le envía a la Alcaldía con los bienes que entran nuevos. **Con el
procedimiento nuevo ya no pide inspección**: es la relación de los bienes **ya codificados por
IMATUR**, con su código y su monto.

**Necesitamos el formato nuevo** (en digital, si lo tienen) para que el sistema lo genere; si lo
inventamos, habría que rehacerlo.

### A2 Acta de Desincorporación

El acta que firman la Coordinadora de Bienes y la Presidencia, que **lista todos los bienes que se van
a retirar** y que la Alcaldía **firma y sella** como aval de que ya fueron desincorporados.

Anotado: **se llamará «Acta de Desincorporación»** en todo el sistema, y **ya no habrá oficio de
retiro** — un solo documento en lugar de dos.

> El proceso **ya funciona a nivel de datos**: el bien sale del inventario activo y se distingue entre
> *Por retirar* y *Retirado*. Falta el documento imprimible y agrupar los bienes **por acta**.

### A3 Acta de asignación de un bien a un trabajador («acta de encargado»)

El documento que firma el trabajador cuando recibe un bien bajo su responsabilidad. Confirmado que
**sigue vigente**.

### A4 Oficio de donación

Confirmado que **sigue vigente**. El sistema ya registra el origen «Donación» con su oficio adjunto;
falta el formato para generarlo.

### A5 ⭐ Un mes de bono vacacional **ya calculado**, con números reales

Esto lo pedimos en julio y sigue pendiente. **Ahora es más importante que antes**, y vale explicar por qué:

De la plantilla de nómina pudimos extraer casi todo el cálculo, pero **la fórmula del total del bono
vacacional no aparece en ninguna parte**: la plantilla documenta la *alícuota* (lo que se acumula por
día), no el monto que finalmente se paga.

Para no inventar una fórmula, el sistema ahora hace esto: calcula un **total estimado** con un supuesto
declarado y lo muestra **al lado del total que ustedes confirman**, con la diferencia entre ambos. En
cuanto nos entreguen un mes real, esa diferencia nos dice si el supuesto es correcto:

- Si coincide → el total pasa a calcularse solo y dejan de teclearlo.
- Si no coincide → la diferencia nos muestra exactamente por dónde corregir.

**Con un solo mes basta.** Puede ser un mes ya pagado, con los nombres que sea.

### A6 ⭐ Rutas Turísticas — gracias por las respuestas, y quedan **cuatro cosas** por cerrar

Recibimos las respuestas a las primeras 16 preguntas y **fueron justo las que hacían falta**. Con
ellas confirmamos que **sí existe un catálogo de rutas** y que dos salidas de «Cumaná Histórica» son
la misma ruta ejecutada dos veces. Eso significa que **vamos a reorganizar el módulo** para que
funcione como trabajan ustedes: el catálogo por un lado (las seis rutas, con su recorrido y su
tarifa) y las salidas por otro (cada fecha, con su grupo, su guía y su aprobación).

**Ya estamos trabajando en eso.** No hace falta que respondan nada para que avancemos. Lo que sí
necesitamos, cuando puedan, es:

**1. Los nombres de los estados de una salida** *(quedó en blanco — es la pregunta R-14)*

Desde que se solicita hasta que termina, ¿cómo la llaman ustedes en cada momento? Por lo que nos
contaron, imaginamos algo como *Solicitada → Aprobada → Programada → Ejecutada*, más *Cancelada*.
**Pero preferimos usar sus palabras, no las nuestras**, porque son las que van a ver en pantalla.

**2. El cobro — cómo funciona en la práctica**

Nos dijeron los montos (5 $ Cumaná Histórica, 15 $ Río Brito, 25 $ Las Maritas y Playa Colorada) y
que las instituciones públicas y los menores de 8 años no pagan. Falta:

- ¿**Quién recibe el dinero**: IMATUR o la Alcaldía?
- ¿**Cómo se paga**: efectivo el día de la salida, transferencia previa, punto de venta?
- ¿**Qué comprobante** se entrega? *(si tienen uno, nos sirve muchísimo verlo)*
- ¿El sistema debe **llevar la cuenta de lo cobrado**, o basta con dejar constancia de que la salida
  tenía tarifa? **Esta es la que más cambia el trabajo.**
- ¿**Quién autoriza** una exoneración fuera de los dos casos ya conocidos?

**3. Dos formatos**

- El **oficio de solicitud** que envían los colegios *(quedaron en pasárnoslo)*.
- El **informe de una ruta ya ejecutada** — es el documento más importante del módulo.

**4. Tres dudas cortas que nos dejaron sus propias respuestas**

- **Exploradores de Cumaná**: nos dijeron que *"es lo mismo que Cumaná Histórica, solo cambia el
  público"*. ¿La anotamos como **una ruta aparte** o como **la misma ruta con dos modalidades**?
- **Las edades**: Exploradores es de 4 a 8 años. ¿Hay **tope**, o un niño de 9 puede ir igual? ¿Y
  las rutas de playa tienen edad mínima? *(hoy el sistema no deja registrar a un niño de 4 — lo
  estamos corrigiendo)*
- **Altos de Cumaná**: ¿tiene tarifa fija? ¿La cobra IMATUR o la posada? ¿Les serviría que el
  sistema lleve un **directorio de las posadas y aliados** con los que coordinan?

El resto del cuestionario (las paradas de cada ruta, los guías, los participantes, el informe) lo
podemos ir viendo con calma: no nos frena.

### A7 ⭐ Los archivos digitales — incluido el inventario que lleva la encargada

Nos confirmaron que **existe versión digital de los documentos** y de un **inventario interno que la
encargada lleva aparte**. **Ese archivo es muy valioso para nosotros** y lo pedimos formalmente:

- Nos deja **cargar los ~142 bienes de una vez** en lugar de teclearlos uno por uno.
- Nos da la **lista de códigos que ya están en uso** (la clasificación que usan de verdad).
- Nos da el **último número de la secuencia**, que es justo el punto de partida que necesitamos.

Sobre los saltos en el N° de orden: entendido que **no son bajas**, sino que el listado está ordenado
**por departamento y no por código**. Cuando tengamos el digital lo ordenamos por código y lo
confirmamos.

### A8 Cinco preguntas cortas sobre la codificación propia

Sin estas no podemos programar la asignación de códigos:

1. **¿Desde qué número arrancamos?** Mientras la Alcaldía no haga esa última revisión, ¿esperamos, o
   partimos del mayor N° de orden de su listado interno?
2. **¿La secuencia es una sola para todo IMATUR**, o una por cada grupo-subgrupo-sección? ¿Sigue siendo
   de 3 dígitos — qué hacemos al pasar de 999?
3. **¿Cuál es la lista de grupos, subgrupos y secciones** que pueden usar? Antes la Alcaldía asignaba
   esos valores y ustedes solo los copiaban; si ahora clasifican, el sistema necesita la lista (o al
   menos los que usan hoy) para no dejarlos escribir cualquier cosa.
4. **¿El código de un bien desincorporado se reutiliza**, o la numeración nunca se recicla?
5. **¿La Alcaldía les devuelve algo** al recibir la relación (acuse, sello, un inventario nuevo)? ¿Y
   cada cuánto se le envía: por lote, mensual, cada vez que entra un bien?

**Y un pedido:** si tienen **por escrito** la notificación de la Alcaldía con este procedimiento nuevo,
nos ayudaría tenerla. Cambia quién responde por la codificación, y conviene que quede en el expediente
y no solo de palabra.

### A9 Dos detalles del Acta de Desincorporación

¿**Quién firma** por IMATUR además de la Coordinadora de Bienes y la Presidencia? ¿Lleva **número
correlativo**? ¿Y el acta ya firmada y sellada **se carga al sistema** para que quede como aval y marque
los bienes como retirados?

---

# B · Datos para poner el sistema en marcha

El sistema está correcto, pero **arranca vacío**. Estos son los datos que necesita para servir de algo.

### B1 ⭐ Personal real

Nombre, cédula, cargo, departamento, fechas de ingreso, tipo de contrato y el resto de la ficha de cada
trabajador activo. Hoy hay **3 registros de prueba**.

### B2 ⭐ Datos de nómina de cada trabajador

Por cada trabajador activo:

- **Sueldo base** mensual
- **Grado de instrucción** (bachiller / TSU / licenciado o ingeniero / especialista / magíster / doctor)
- **Fecha de ingreso a la administración pública** — no la de ingreso a IMATUR; es la que determina la
  prima de antigüedad
- **Número de hijos** (o su carga familiar completa)
- **Número de cuenta bancaria** y banco donde se le paga

> Hoy el sistema tiene **una sola fila de prueba** de datos salariales.

### B3 ⭐ Cesta ticket y tasa del dólar, **por cada mes** que se vaya a pagar

Ambos cambian todos los meses. El sistema ya tiene la pantalla para cargarlos mes a mes; necesitamos
los valores. Indíquennos **de qué mes es cada monto**.

### B4 Catálogo de cargos

Hoy el sistema tiene **5 cargos**. Necesitamos el listado completo del Manual Descriptivo de Cargos, o
al menos los que estén en uso.

### B5 Los ~142 bienes reales

Ya se puede cargar: la estructura y las ubicaciones están listas. **Con el archivo digital del punto
A7** los cargamos de una vez, en lugar de teclearlos bien por bien.

### B6 Asignar el Coordinador de *Compra de Bienes y Servicios*

**Esto es una acción interna de IMATUR, no un dato para enviarnos.** Mientras ese cargo esté vacante, el
sistema **bloquea todos los movimientos de bienes**, porque por diseño todo movimiento lo autoriza esa
coordinación. Es intencional, pero hay que llenar el puesto en el sistema para operar.

### B7 Correo institucional para envíos automáticos

Para que el sistema pueda enviar los correos de recuperación de contraseña necesitamos los datos de
acceso de `Sucreimatur@gmail.com` (o de la cuenta que prefieran): servidor, puerto, usuario y clave. Si
usan Gmail, hace falta una «contraseña de aplicación».

> Sin esto, la recuperación de contraseña por correo no funciona. El respaldo actual es que el
> administrador la restablezca a mano.

---

# C · Confirmaciones que afinan números ya calculados

El cálculo de nómina **ya funciona**: estos tres puntos están puestos como parámetros ajustables. Lo que
falta es confirmar cuál es el correcto para que los montos sean definitivos.

### C1 ⭐ Días base del bono vacacional: ¿75 para todos, o 75 / 85 / 45 según el tipo?

En la plantilla de nómina, la alícuota se calcula con **75 días para todo el personal** —incluidos
obreros y contratados—. Pero del formato de Bono Vacacional entendimos 75 para Alto Nivel y Empleados
Fijos, **85** para Obreros Fijos y **45** para Contratados.

**Los dos criterios se contradicen.** ¿Cuál rige?

### C2 ⭐ En el SSO y las demás deducciones, ¿cuándo son 4 semanas y cuándo 5?

En la plantilla, las hojas de Alto Nivel y Contratados calculan con **4 semanas** y las de Empleados
Fijos y Obreros con **5**, en el mismo mes. ¿Depende del mes, del tipo de personal, o fue un descuido?

### C3 ⭐ Hoja de INTERESES de la Liquidación: ¿de dónde salen los «días adicionales»?

*(Adjuntamos un recorte de la hoja con la columna señalada — la vez anterior la pregunta no quedó clara,
y es culpa nuestra por no haberla ilustrado.)*

Nos referimos a los valores que van cambiando: **79, 82, 120, 150 sobre 360**. ¿Los toman de una tabla
oficial, de un boletín, o los calcula Talento Humano cada mes?

**Esta es la única pregunta que nos falta para construir la Liquidación de Prestaciones Sociales.**

### Y dos cosas menores de nómina

- ¿De dónde sale la **cantidad de divisas** que le corresponde a cada trabajador en el bono de
  responsabilidad?
- ¿Ese bono aplica **solo** a Alto Nivel y Comisión de Servicio?

---

# D · Decisiones opcionales

Solo las construimos si las quieren. Ninguna bloquea nada.

### D1 Ruta con tarifa: ¿quién cobra y cómo se paga?

El sistema tenía un campo de tarifa que nunca se llenaba, así que el reporte mostraba **todas las rutas
como gratuitas** — un dato falso. **Ya lo retiramos del reporte.** Si alguna ruta se cobra, díganos
quién recibe el dinero, cómo se paga y si el sistema debe llevar la contabilidad o solo dejar constancia;
lo volvemos a activar. Si no se cobra nada, eliminamos los campos.

### D2 Al finalizar una ruta, ¿generar el informe automáticamente?

El informe de ruta ya existe y se arma con la demografía de los participantes. La pregunta es solo si
debe dispararse solo al marcar la ruta como *Finalizada*, o prefieren generarlo a mano.

### D3 ¿Una actividad de formación puede tener más de un facilitador?

Hoy se registra uno solo.

### D4 ¿Metas de formación y rutas para comparar planificado contra ejecutado?

Hoy hay un valor de relleno (100 talleres y 100 rutas al año). Si nos dan las metas reales, el indicador
empieza a decir algo.

### D5 Otras dos, menores

- ¿Activamos la **numeración correlativa de oficios de formación** (por ejemplo, `FORM-001/2026`)?
- ¿Quieren un **libro de correspondencia** unificado, que liste en un solo lugar los oficios emitidos y
  recibidos de todos los módulos?
- ¿Desean **cargar datos históricos** que hoy estén en Excel o papel? Si es así, ¿de qué módulos, y nos
  pueden facilitar los archivos?

---

# ⚠️ Encontramos errores en su plantilla de Excel de nómina

Al estudiar las fórmulas para construir el cálculo encontramos **cuatro que afectan montos reales**.
Están en las fórmulas, así que se repiten en cualquier mes que se arme con ese archivo:

1. La **prima de antigüedad de quienes tienen 23 años o más** se calcula sobre el sueldo **mensual** en
   vez del quincenal, así que **queda al doble**. Ejemplo comprobado: la hoja paga 112,80 donde
   corresponden 56,40.
2. En la hoja de Comisión de Servicio, el **aporte patronal de FAOV está al 20 %** cuando el encabezado
   dice 2 % — diez veces más de lo debido.
3. En esa misma hoja, la **fórmula de la prima de antigüedad está dañada**: no funciona para 19 ni 21
   años de servicio, y las filas en blanco generan montos negativos que se cuelan en los totales.
4. En la hoja de RESUMEN, la fila de **Obreros** toma las cifras de columnas corridas: cuenta el SSO dos
   veces y deja el LRPPF por fuera.

**El sistema ya calcula estos cuatro casos correctamente**, así que al usarlo desaparecen. Se los
señalamos para que puedan revisar los meses ya pagados con ese archivo.

---

## Ya confirmado — no requiere respuesta

- **Cargos:** son generales, los mismos para todos los departamentos. ✔️
- **Constancias de trabajo:** se emiten sin exigir tiempo mínimo de servicio. ✔️
- **Bienes** *(levantamiento del 2026-08-04/05, 59 preguntas)*: responsable del bien automático por
  departamento · costo y proveedor como control interno · baja y mantenimiento · la Oficina del
  Aeropuerto es un departamento propio bajo Planificación y Gestión Turística · destino del bien dado de
  baja · umbral de mobiliario por número de empleados. ✔️
- **Bienes, cambio del 2026-09-02:** IMATUR asignará el código de sus bienes continuando la secuencia ·
  la Alcaldía solo recibe la relación · el acta pasa a llamarse **Acta de Desincorporación**, por lote y
  sellada por la Alcaldía como aval · **no habrá oficio de retiro** · el acta de encargado y el oficio de
  donación siguen vigentes · los saltos en el N° de orden son por el orden del listado, no bajas ·
  existe versión digital de los documentos y del inventario interno. ✔️ *(Lo que aún necesitamos de esto
  está en A1, A2, A7, A8 y A9.)*
- **Nómina:** existe un formato de nómina quincenal aparte del de Liquidación · la cesta ticket la
  actualiza la UNAPRE cada mes · la «tasa BCV» es el tipo de cambio del dólar · la gobernación no paga
  caja de ahorro · los porcentajes de prima profesional por grado académico · la escala de antigüedad
  con tope de 30 %. ✔️
- **Rutas (respuestas del 2026-09-03):** existe un **catálogo** de rutas con su recorrido y sus puntos ·
  dos salidas de la misma ruta son **la misma ruta ejecutada dos veces** · puede haber **varios grupos
  el mismo día** con guías rotativos · **sí se cobra** (5 $ / 15 $ / 25 $ por persona, gratis para
  instituciones públicas y menores de 8 años) · la salida nace de una **solicitud** (particular o
  institucional por oficio) y **la aprueba la Presidencia** · se **cancela con motivo** (gasolina, clima,
  el grupo cancela) y se **reprograma conservando la misma salida** · los cinco puntos de Cumaná
  Histórica. ✔️ *(Con esto ya empezamos a reorganizar el módulo. Lo que falta está en A6.)*
  **Nota:** el facilitador externo y la institución participante se habían retirado por no usarse; con
  estas respuestas **vuelven** — las instituciones sí importan y sí hay más de un guía por salida.

---

*Con las respuestas de la sección **A** cerramos la programación pendiente. Con los datos de la sección
**B** el sistema queda operativo. La sección **C** vuelve definitivos los montos de nómina, que hoy ya
se calculan.*
