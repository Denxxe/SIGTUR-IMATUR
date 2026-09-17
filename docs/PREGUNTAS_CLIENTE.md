# Lo que falta para cerrar el sistema — **módulo por módulo**

**Para:** IMATUR · **De:** equipo de desarrollo · **Actualizado:** 2026-09-17

El sistema está construido y funcionando. Lo que falta para dejarlo al 100 % **ya casi no es
programación**: son documentos, datos y unas pocas confirmaciones que dependen de criterios de la
institución.

Este documento está organizado **por módulo**. De cada uno verán tres cosas:

- ✅ **Lo que ya funciona** — para que sepan qué pueden empezar a usar.
- 🔧 **Lo que falta para completarlo** — y de qué depende.
- ❓ **Lo que necesitamos de ustedes** — numerado, para que puedan responder debajo de cada punto.

> Pueden responder directamente debajo de cada pregunta, o por WhatsApp indicando el número.
> **No hace falta responderlo todo de una vez**: cada módulo es independiente.

---

## Resumen — en qué está cada módulo

| Módulo | Estado | Qué le falta | Quién lo destraba |
|---|---|---|---|
| **Recepción (Visitas)** | ✅ **Completo** | Nada | — |
| **Formación** | ✅ **Completo** | Solo mejoras opcionales | Ustedes, si las quieren |
| **Personal (RRHH)** | ✅ **Completo** | **Los datos del personal real** | Ustedes |
| **Nómina** | 🟢 **Calcula correctamente** | La **Liquidación de Prestaciones Sociales** + los datos de cada trabajador | 1 respuesta + datos |
| **Inventario (Bienes)** | 🟡 **Construido, falta adaptarlo** | **2 formatos**, la **codificación propia** y los 142 bienes | 5 respuestas + 2 documentos |
| **Turismo (Rutas)** | 🟢 **Destrabado (17/09)** | Reorganizar catálogo/salidas y construir: ya no espera respuestas | 1 formato menor |
| **Sistema / transversal** | ✅ Funcionando | El correo saliente | 1 dato técnico |

**Si tuvieran que responder solo cuatro cosas**, serían estas — *todas de Bienes y Nómina: **Rutas
ya no espera nada**, gracias a sus respuestas del 17/09*:

| | Qué | Dónde |
|---|---|---|
| 1 | El **archivo digital del inventario** que lleva la encargada | §2 · pregunta 6 |
| 2 | El **Acta de Desincorporación** (formato) | §2 · pregunta 1 |
| 3 | Un **mes de bono vacacional ya calculado** | §3 · pregunta 1 |
| 4 | Los **datos de nómina** de cada trabajador | §3 · pregunta 7 |

---
---

# 1 · PERSONAL (RRHH)

### ✅ Lo que ya funciona

Todo el módulo: ficha del trabajador con asistente paso a paso, expediente con sus recaudos,
organigrama, cargos, horarios y grupos de rotación, asistencia con puntualidad, permisos y reposos,
vacaciones, faltas y amonestaciones, constancias de seis tipos, carnets, egreso y reingreso con
historial, y traslados entre departamentos.

### 🔧 Lo que falta para completarlo

**Nada de programación.** El módulo está terminado. Lo único que falta son **los datos reales**: hoy
hay 3 trabajadores de prueba y 5 cargos cargados.

### ❓ Lo que necesitamos de ustedes

**1. El personal real** ⭐

Nombre, cédula, cargo, departamento, fechas de ingreso, tipo de contrato y el resto de la ficha de
cada trabajador activo.

**2. El catálogo de cargos**

El listado completo del Manual Descriptivo de Cargos, o al menos los que estén en uso hoy.

**3. La planilla física de asistencia** *(opcional)*

Si quieren que el sistema imprima una planilla igual a la que usan hoy en papel, necesitamos verla.
Sin ella el módulo funciona igual; es solo por fidelidad al formato.

---
---

# 2 · INVENTARIO (BIENES)

### ✅ Lo que ya funciona

Cada bien tiene su **expediente completo**: registro con marca, modelo y serial; datos de adquisición
(costo, proveedor, factura, garantía con su vencimiento); origen compra o donación; ubicación y
departamento; responsable **calculado solo** a partir del departamento; movimientos con autorización;
mantenimiento con salida y retorno; etiquetas con código QR; hoja de vida; conteo por cambio de
gestión con su acta; y reportes para la Presidencia.

**Y desde el 15/09** —con los documentos que nos pasaron— también genera:

- El **oficio de relación de bienes nuevos** a la Alcaldía: se eligen los bienes, el sistema pone el
  correlativo, arma la tabla y queda listo para imprimir y firmar. Los bienes ya reportados dejan de
  aparecer, así que no se manda uno dos veces, y cualquier oficio anterior se puede volver a imprimir
  tal como se envió.
- El **documento de donación**: se cargan los datos del donante y el sistema redacta el texto
  completo, **escribe los montos y la fecha en letras** y lo deja listo para firmar.

### 🔧 Lo que falta para completarlo

| | Qué falta | De qué depende |
|---|---|---|
| 1 | Que el **Acta de Desincorporación** salga con **su formato oficial** | 🔴 Del formato — pregunta 1 |
| 2 | **Acta de asignación** de un bien a un trabajador | 🔴 Del formato — pregunta 2 |
| 3 | Que **IMATUR asigne sus propios códigos** | 🔴 De las respuestas de la pregunta 5 |
| 4 | **Cargar los ~142 bienes reales** | 🔴 Del archivo de la pregunta 6 |
| 5 | Que el reporte de *Suficiencia de Bienes* diga algo | 🟡 De la pregunta 8 |

> **Actualizado el 17/09: el Acta de Desincorporación ya funciona completa.** Se arma el acta con
> los bienes a retirar, se imprime, y cuando vuelve firmada y sellada por la Alcaldía se registra
> —con su escaneado— y **todos sus bienes quedan marcados como retirados de una sola vez**. Antes
> había que confirmarlos uno por uno y no existía la noción de «acta».
>
> Lo único que falta es **la hoja impresa con su formato oficial**: la que genera hoy es provisional
> (membrete institucional, tabla de bienes y los tres bloques de firma). Cuando nos pasen el formato
> real se cambia esa hoja y nada de lo registrado se toca.

### ❓ Lo que necesitamos de ustedes

**1. El Acta de Desincorporación** 🔴 *el punto más urgente de este módulo*

El acta que firman la Coordinadora de Bienes y la Presidencia, que **lista todos los bienes que se van
a retirar** y que la Alcaldía **firma y sella** como aval.

Anotado ya: se llamará **«Acta de Desincorporación»** en todo el sistema, y **ya no habrá oficio de
retiro** — un solo documento en lugar de dos.

Y tres detalles del acta:

- ¿**Quién firma** por IMATUR, además de la Coordinadora de Bienes y la Presidencia?
- ¿Lleva **número correlativo**?
- El acta ya firmada y sellada, ¿**se carga al sistema** para que quede como aval y marque los bienes
  como retirados?

**2. El acta de asignación de un bien a un trabajador** («acta de encargado») 🔴

El documento que firma el trabajador cuando recibe un bien bajo su responsabilidad. Nos confirmaron
que sigue vigente; solo nos falta el formato.

**3. ⭐ Una duda sobre el oficio de relación que nos pasaron**

El oficio que nos dieron es de **junio** y dice: *«se le solicita a la Coordinación que dirige, les
sean asignados los respectivos códigos»*. Es decir, **le pide a la Alcaldía que codifique**, y su
tabla **no trae columna de código**.

Pero en septiembre nos informaron que el procedimiento cambió: que ahora **IMATUR asigna sus propios
códigos** y la relación pasa a ser informativa, con el código y el monto de cada bien.

**Son dos cosas distintas.** Lo construimos igual al papel que nos dieron, que es lo seguro. Solo
necesitamos que nos confirmen:

- ¿El oficio se sigue usando **así como está**?
- ¿O viene un formato nuevo, con columna de código?

*Si es lo segundo, **no hay que rehacer nada**: es agregarle una columna.*

**4. ⚠️ La resolución y la gaceta de la Presidenta — estaban mal en el sistema**

Los dos documentos que nos pasaron, ambos firmados y sellados, dicen:

> Resolución **N° 32** del **05/09/2025**, publicada en Gaceta Municipal Extraordinaria **N° 87**
> del **05/09/2025**

El sistema tenía cargada la Resolución **025** del 15/03/2024 y la Gaceta **042** del 20/01/2024 —
datos de relleno que nunca se corrigieron. Y eso **se venía imprimiendo en cinco documentos reales**:
constancias de trabajo, carta de aceptación y de culminación de pasantes, y los dos oficios de rutas.

Ya lo corregimos con los datos de sus documentos, y de paso el cargo, que ahora dice **Presidenta**
(antes decía «Director General»). **Solo necesitamos que nos confirmen que 32 y 87 es lo vigente.**

**5. Cinco preguntas sobre la codificación propia** 🔴

Sin estas no podemos programar la asignación de códigos:

1. **¿Desde qué número arrancamos?** Mientras la Alcaldía no haga esa última revisión, ¿esperamos, o
   partimos del mayor N° de orden de su listado interno?
2. **¿La secuencia es una sola para todo IMATUR**, o una por cada grupo-subgrupo-sección? ¿Sigue
   siendo de 3 dígitos — qué hacemos al pasar de 999?
3. **¿Cuál es la lista de grupos, subgrupos y secciones** que pueden usar? Antes la Alcaldía asignaba
   esos valores y ustedes solo los copiaban; si ahora clasifican, el sistema necesita la lista (o al
   menos los que usan hoy) para no dejarlos escribir cualquier cosa.
4. **¿El código de un bien desincorporado se reutiliza**, o la numeración nunca se recicla?
5. **¿La Alcaldía les devuelve algo** al recibir la relación (acuse, sello, un inventario nuevo)? ¿Y
   cada cuánto se le envía: por lote, mensual, cada vez que entra un bien?

**Y un pedido:** si tienen **por escrito** la notificación de la Alcaldía con este procedimiento
nuevo, nos ayudaría tenerla. Cambia quién responde por la codificación, y conviene que quede en el
expediente y no solo de palabra.

**6. ⭐ El archivo digital del inventario que lleva la encargada** 🔴

Nos confirmaron que existe. **Ese archivo es el que más nos destraba:**

- Nos deja **cargar los ~142 bienes de una vez** en lugar de teclearlos uno por uno.
- Nos da la **lista de códigos que ya están en uso** — que es la clasificación que usan de verdad, y
  responde la pregunta 5.3.
- Nos da el **último número de la secuencia**, que es justo el punto de partida de la pregunta 5.1.

*Sobre los saltos en el N° de orden: entendido que **no son bajas**, sino que el listado está ordenado
por departamento y no por código. Cuando tengamos el digital lo ordenamos por código y lo
confirmamos.*

**7. ¿Quién es el abogado que visa el documento de donación?** 🟢

Nombre y número de IPSA. *(El bloque ya está hecho; si no lo llenan, simplemente no se imprime.)*

**8. ¿Cuántos bienes le tocan a cada trabajador?**

El sistema tiene el reporte **Suficiencia de Bienes**, que compara lo que hay en cada departamento
contra lo que debería haber según su personal. Lo que no tenemos son **sus números**: hoy están
puestas tres dotaciones de ejemplo, inventadas por nosotros.

Necesitamos, aunque sea aproximado: **¿cuántas sillas por empleado? ¿cuántas mesas o escritorios?
¿computadoras?** Y si hay categorías que **no** se reparten por persona (un aire acondicionado es del
espacio, no de cada trabajador), díganos cuáles, para no evaluarlas.

> Sin esto el reporte funciona, pero compara contra un número inventado, así que su resultado no
> significa nada todavía.

**9. Una acción interna, no un dato que enviarnos**

Falta **asignar el Coordinador de _Compra de Bienes y Servicios_** en el sistema. Mientras ese cargo
esté vacante, el sistema **bloquea todos los movimientos de bienes**, porque por diseño todo
movimiento lo autoriza esa coordinación. Es intencional, pero hay que llenar el puesto para operar.

**10. Revisar las categorías**

Propusimos 11 categorías internas para agrupar los bienes en los reportes — distintas del código de
la Alcaldía, que no distingue un router de una silla. Convendría que las revisen y nos digan si
encajan con cómo quieren ver sus bienes.

---
---

# 3 · NÓMINA

### ✅ Lo que ya funciona

El sistema **calcula la nómina quincenal completa**. Las primas ya no se teclean: se **deducen** del
sueldo base, el grado de instrucción, los años en la administración pública y el número de hijos.
Están los cinco tipos de personal, la exportación en las seis hojas del formato oficial, el cierre del
período y el recálculo.

Los **porcentajes son editables desde el sistema** (profesionalización, antigüedad, deducciones y
aportes): si cambia el contrato colectivo, se ajusta sin tocar programación.

El **Bono Vacacional** también calcula sus primas con el mismo motor.

**Novedad:** la **tasa del dólar** ya no hay que buscarla. La pantalla trae un botón *Consultar BCV*
que la trae sola y la propone; ustedes la confirman o la corrigen. El sistema **no la da por buena
solo**, y queda registrado si el número salió del BCV o se escribió a mano.

### 🔧 Lo que falta para completarlo

| | Qué falta | De qué depende |
|---|---|---|
| 1 | La **Liquidación de Prestaciones Sociales** | 🔴 De la pregunta 2 — es lo único que la bloquea |
| 2 | Que el **total del bono vacacional** se calcule solo | 🔴 De la pregunta 1 |
| 3 | Que los montos sean **definitivos** | 🟡 De las preguntas 3, 4 y 5 |
| 4 | Poder emitir una nómina de verdad | 🔴 De los datos de las preguntas 7 y 8 |

### ❓ Lo que necesitamos de ustedes

**1. ⭐ Un mes de bono vacacional YA CALCULADO, con números reales** 🔴

Esto lo pedimos en julio y sigue pendiente. **Ahora es más importante que antes**, y vale explicar por
qué:

De la plantilla de nómina pudimos extraer casi todo el cálculo, pero **la fórmula del total del bono
vacacional no aparece en ninguna parte**: la plantilla documenta la *alícuota* (lo que se acumula por
día), no el monto que finalmente se paga.

Para no inventar una fórmula, el sistema hace esto: calcula un **total estimado** con un supuesto
declarado y lo muestra **al lado del total que ustedes confirman**, con la diferencia entre ambos. En
cuanto nos entreguen un mes real, esa diferencia nos dice si el supuesto es correcto:

- Si coincide → el total pasa a calcularse solo y dejan de teclearlo.
- Si no coincide → la diferencia nos muestra exactamente por dónde corregir.

**Con un solo mes basta.** Puede ser un mes ya pagado, con los nombres que sea.

**2. ⭐ Hoja de INTERESES de la Liquidación: ¿de dónde salen los «días adicionales»?** 🔴

*(Adjuntamos un recorte de la hoja con la columna señalada — la vez anterior la pregunta no quedó
clara, y es culpa nuestra por no haberla ilustrado.)*

Nos referimos a los valores que van cambiando: **79, 82, 120, 150 sobre 360**. ¿Los toman de una tabla
oficial, de un boletín, o los calcula Talento Humano cada mes?

**Esta es la única pregunta que nos falta para construir la Liquidación de Prestaciones Sociales.**

**3. ⭐ Días base del bono vacacional: ¿75 para todos, o 75 / 85 / 45 según el tipo?**

En la plantilla de nómina, la alícuota se calcula con **75 días para todo el personal** —incluidos
obreros y contratados—. Pero del formato de Bono Vacacional entendimos 75 para Alto Nivel y Empleados
Fijos, **85** para Obreros Fijos y **45** para Contratados.

**Los dos criterios se contradicen.** ¿Cuál rige?

**4. ⭐ En el SSO y las demás deducciones, ¿cuándo son 4 semanas y cuándo 5?**

En la plantilla, las hojas de Alto Nivel y Contratados calculan con **4 semanas** y las de Empleados
Fijos y Obreros con **5**, en el mismo mes. ¿Depende del mes, del tipo de personal, o fue un descuido?

**5. ⭐ La tasa del dólar: ¿cuál tasa usan, exactamente?**

Antes de confiar en el botón del BCV necesitamos saber qué tasa aplican de verdad:

1. ¿Es la **tasa oficial del BCV**, o una que les indica la Alcaldía o la Gobernación?
2. Si es la del BCV, **¿de qué día?** ¿La del día que se paga, la del último día del mes, la del día
   en que se arma la nómina?
3. **¿Es una sola por mes?** Se lo preguntamos porque en la plantilla que nos enviaron aparecen **dos
   tasas distintas en el mismo período: 36,58 en una hoja y 36,23 en otra.** Si eso es correcto y cada
   nómina lleva su propia tasa, tenemos que cambiar cómo lo guarda el sistema (hoy guarda **una por
   mes**). Si fue un descuido de la plantilla, nos quedamos como estamos.

> **Un detalle que conviene saber:** el BCV no publica «la tasa de hoy». Publica una tasa con su
> **fecha valor**, que es el día en que rige y que suele ser el **próximo día hábil**. Por eso el
> sistema les muestra siempre esa fecha junto al número, para que confirmen que corresponde al mes que
> están cargando. Un domingo, por ejemplo, la página del BCV ya muestra la tasa del martes siguiente.

*Mientras no respondan, el botón funciona igual y la tasa se puede cargar a mano: esto no bloquea
nada.*

**6. Dos cosas menores del bono de responsabilidad**

- ¿De dónde sale la **cantidad de divisas** que le corresponde a cada trabajador?
- ¿Ese bono aplica **solo** a Alto Nivel y Comisión de Servicio?

**7. ⭐ Datos de nómina de cada trabajador** 🔴

Por cada trabajador activo:

- **Sueldo base** mensual
- **Grado de instrucción** (bachiller / TSU / licenciado o ingeniero / especialista / magíster / doctor)
- **Fecha de ingreso a la administración pública** — no la de ingreso a IMATUR; es la que determina la
  prima de antigüedad
- **Número de hijos** (o su carga familiar completa)
- **Número de cuenta bancaria** y banco donde se le paga

> Hoy el sistema tiene **una sola fila de prueba**.

**8. ⭐ Cesta ticket y tasa del dólar, por cada mes que se vaya a pagar** 🔴

Ambos cambian todos los meses. El sistema ya tiene la pantalla para cargarlos mes a mes; necesitamos
los valores, **indicando de qué mes es cada monto**. La **cesta ticket** sigue siendo captura manual:
la publica la UNAPRE y no hay de dónde leerla automáticamente.

---

### ⚠️ Encontramos errores en su plantilla de Excel de nómina

Al estudiar las fórmulas para construir el cálculo encontramos **cuatro que afectan montos reales**.
Están en las fórmulas, así que se repiten en cualquier mes que se arme con ese archivo:

1. La **prima de antigüedad de quienes tienen 23 años o más** se calcula sobre el sueldo **mensual** en
   vez del quincenal, así que **queda al doble**. Ejemplo comprobado: la hoja paga 112,80 donde
   corresponden 56,40.
2. En la hoja de Comisión de Servicio, el **aporte patronal de FAOV está al 20 %** cuando el encabezado
   dice 2 % — diez veces más de lo debido.
3. En esa misma hoja, la **fórmula de la prima de antigüedad está dañada**: no funciona para 19 ni 21
   años de servicio, y las filas en blanco generan montos negativos que se cuelan en los totales.
4. En la hoja de RESUMEN, la fila de **Obreros** toma las cifras de columnas corridas: cuenta el SSO
   dos veces y deja el LRPPF por fuera.

**El sistema ya calcula estos cuatro casos correctamente**, así que al usarlo desaparecen. Se los
señalamos para que puedan revisar los meses ya pagados con ese archivo.

---
---

# 4 · TURISMO (RUTAS)

### ✅ Lo que ya funciona

El recorrido de cada ruta con sus paradas y su **mapa** (que funciona sin internet), la inscripción de
participantes —incluidos niños sin cédula, con su representante—, los oficios con numeración
correlativa, el informe con la demografía del grupo y los reportes.

### 🔧 Lo que falta para completarlo

**Gracias por las respuestas del 03/09: fueron justo las que hacían falta.** Con ellas confirmamos que
**sí existe un catálogo de rutas** y que dos salidas de «Cumaná Histórica» son la misma ruta ejecutada
dos veces.

Eso significa que **vamos a reorganizar el módulo** para que funcione como trabajan ustedes: el
catálogo por un lado (las seis rutas, con su recorrido y su tarifa) y las salidas por otro (cada
fecha, con su grupo, su guía y su aprobación).

**Ya estamos trabajando en eso y no hace falta que respondan nada para que avancemos.**

> ## ✅ **Con sus respuestas del 17/09, este módulo quedó completamente destrabado**
>
> Contestaron **todo lo que faltaba**, incluidos los dos puntos que llevaban meses frenándonos: los
> **nombres de los estados** y **cómo funciona el cobro**. Y los formatos que nos pasaron
> —la **Ficha Institucional**, el **itinerario de Cumaná Histórica** y la **lista de asistencia**—
> resolvieron de golpe lo que era el documento más importante del módulo.
>
> **Ya no necesitamos nada más para construirlo.** Solo dos cosas menores, al final de esta sección.

| | Qué falta | De qué depende |
|---|---|---|
| 1 | Separar **catálogo** de **salidas** | ✅ De nadie |
| 2 | Que un niño de 4 años se pueda inscribir, y las **restricciones de Río Brito** | ✅ De nadie |
| 3 | La **tarifa** de cada ruta, en dólares y a la tasa del día | ✅ De nadie |
| 4 | **No ejecutado** y reprogramación | ✅ De nadie — *ya nos dieron los estados* |
| 5 | **Registrar los cobros** y lo cancelado | ✅ De nadie — *ya sabemos cómo funciona* |
| 6 | Registrar la **solicitud** y su aprobación | ✅ De nadie — *el oficio lo traen ustedes, el sistema lo archiva* |
| 7 | La **Ficha Institucional** generada sola al cerrar | ✅ De nadie — *ya tenemos el formato* |
| 8 | **Varios guías** por salida, con el criterio de 7-8 niños por guía | ✅ De nadie |
| 9 | Los **oficios de permiso** a museos y castillos, agrupados por semana | 🟡 Del formato *(ver al final)* |

### ❓ Lo que necesitamos de ustedes

**1. ⭐ Los nombres de los estados de una salida** — ✅ **RESPONDIDA el 17/09**

Desde que se solicita hasta que termina, ¿cómo la llaman ustedes en cada momento? Por lo que nos
contaron, imaginamos algo como *Solicitada → Aprobada → Programada → Ejecutada*, más *Cancelada*.
Programado, Ejecutado, o no ejetucado (muchas veces llegan los oficios, se planifican la ruta), pero aveces llegan el momento donde los solicitantes cancela, (Imatur puede cancelar por razones ajenas Agua, clima, terremotos etc).
En estos casos se haria una reprogramacion de la salida que no se pudo ejecutar.

**Pero preferimos usar sus palabras, no las nuestras**, porque son las que van a ver en pantalla — y
una vez fijadas, cambiarlas después cuesta.

**2. ⭐ El cobro — cómo funciona en la práctica** — ✅ **RESPONDIDA el 17/09**

Nos dijeron los montos (5 $ Cumaná Histórica, 15 $ Río Brito, 25 $ Las Maritas y Playa Colorada) y que
las instituciones públicas y los menores de 8 años no pagan. Falta:
En el centro historico las instituciones publicas no hacen algun dividendo, pero si se necesitan oficios... Unicamente para cumana Historica, instituciones que quieren alguna ruta para estudiantes necesitan que traigan un oficio previo para la planificacion de la ruta.

- ¿**Quién recibe el dinero**: IMATUR o la Alcaldía? se dispone una cuenta persona exclusiva para el cobro de las rutas en IMATUR.
- ¿**Cómo se paga**: efectivo el día de la salida, transferencia previa, punto de venta? Normalmente caundo son las salidas no gratuitas, se le tiene una fecha para cancelar y poder planificar la salida, pero pueden cancelar dentro de la fecha tope...
- ¿**Qué comprobante** se entrega? *(si tienen uno, nos sirve muchísimo verlo)* si ellos tiene que pasar el capture o bauche de pago, en efectivo se levanta un acta de pago (el formato nace del momento, puedes darnos una idea, pero funciona para un respaldo de que el servicio fue pagado).
- ¿El sistema debe **llevar la cuenta de lo cobrado**, o basta con dejar constancia de que la salida
  tenía tarifa? **Esta es la que más cambia el trabajo.** si debe de llevar el cobro.. y lo cancelado (cuando no es aviso0)
- ¿**Quién autoriza** una exoneración fuera de los dos casos ya conocidos? en caso de que haya un exoneracion lo autoriza es la presidenta (Maria maza en este caso).

**3. Dos formatos** — ✅ **RESUELTOS el 17/09** (uno llegó, el otro ya no aplica)

- El **oficio de solicitud** que envían los colegios *(quedaron en pasárnoslo)*. formato de las instituciones, no siempre es el mismo, pero por lo general tienen cosas en comun... Sera por varias instituciones. por lo general se le solicita la cantidad de niños, ruta o atractivo especifico, cantidad de representantes (maestros).
- El **informe de una ruta ya ejecutada** — es el documento más importante del módulo. planilla de asistencia del personal y la planilla estadistica de la instituciones... al Finalizar, esa planilla lleva elconteo de los participantes, instituciones que estuvo involucrada (institucion que poidio el servicio y Defensa civil o algun ente gubernamental que necesitaba estar). en actas de no ejecutado y ejecutado si se hacen y se dejan con la OAC...

**4. Cinco dudas cortas** — ✅ **RESPONDIDAS el 17/09**

- **Exploradores de Cumaná**: nos dijeron que *"es lo mismo que Cumaná Histórica, solo cambia el
  público"*. ¿La anotamos como **una ruta aparte** o como **la misma ruta con dos modalidades**? Cumana historica es la oficial y comercializada... Ecploradores de Cumaná está dirijida unicamente a instituciones educativas, y tambien Cumaná historica -Huellas del ayer. Que esta dirigido unicamente para adultos mayores...
- **Las edades**: Exploradores es de 4 a 8 años. ¿Hay **tope**, o un niño de 9 puede ir igual? ¿Y las
  rutas de playa tienen edad mínima? exploradores llevan el tope de 4 hasta  16 años por ser para instituciones educativas.
- **Altos de Sucre**: ¿tiene tarifa fija?No es una tarifa Fija depende que lo del cleinte solicite ¿La cobra IMATUR o la posada? pasa por imatur ¿Les serviría que el sistema
  lleve un **directorio de las posadas y aliados** con los que coordinan? no
- **¿Cómo llevan hoy el registro de una ruta?** *(esta quedó en espera)* ¿En Excel, en papel, o no se
  lleva? Lo preguntamos porque de ahí depende **cuánto historial hay que cargar** al arrancar: si hay
  un Excel con las salidas de este año, lo subimos de una vez. llean el registros por planificaccion por papel, luego para las estadisticas se acumulan en un excel para llevar la estadisitica completa de todas las rutas acumulativo medio año (6 meses), pero si se lleva el conteo completo. las Estadisticas se sacan mensaules para el informe de gestion (que es trimestral).
- **Los guías**: nos hablaron de *"guías rotativos"*. ¿**Cuántos guías van en una salida** — uno o
  varios? depende la cantidad de personas: un aproximacion de 7 a 8 niños por guia (una salida de 35 personas hirian 3 guias y acompañantes), pero depende de la cantidad de guias disponibles y se pueden hacer estrategias para solventar esto ¿Y quieren que quede registrado **quiénes fueron** en cada una? si *(hoy el sistema solo admite
  uno por ruta; si normalmente son varios, hay que cambiarlo)* si son varios.

*El resto del cuestionario (las paradas de cada ruta, los participantes, el informe) lo podemos ir
viendo con calma: no nos frena.*

**5. Dos mejoras opcionales** — ✅ **RESPONDIDAS el 17/09**

- Al marcar una ruta como *Finalizada*, ¿quieren que el **informe se genere solo**, o prefieren
  generarlo a mano? si.
- Nos dijeron que **coordinar con fundaciones y entes externos** el acceso a los puntos es uno de los
  tres dolores del módulo. Si les sirve, el sistema puede guardar, por cada parada, **quién la
  custodia** y en qué va la gestión del permiso, para no reconstruirlo de memoria en cada salida. dependiendo de la salida, peros i se llea una coordinacion de los puntos a visitar en la guia.

  otra pregunta: al momento de hacer un ragistro de diferente entidad (institucion o persona) al solicitar una ruta (asi sean la misma ruta es considerada 2 salidas y en el registro son 2 rutas aplicadas).

---

### ✅ Recibido — gracias. Esto es lo que entendimos

Su última frase —*"así sean la misma ruta, es considerada 2 salidas y en el registro son 2 rutas
aplicadas"*— **confirma exactamente** cómo vamos a organizarlo: **el recorrido se guarda una sola
vez** (con sus paradas y su reseña, como el folleto) y **cada salida es un registro aparte**, con su
grupo, su guía y su ficha. Es justo lo que nos hacía falta oír.

Anotamos también:

- **Tres modalidades del mismo recorrido**, no tres rutas: *Cumaná Histórica* (la comercial),
  *Exploradores de Cumaná* (solo instituciones educativas, **4 a 16 años**) y
  ***Cumaná Histórica – Huellas del Ayer*** (solo adultos mayores) — esta última no la conocíamos.
- **Los estados son tres:** Programado → Ejecutado, o **No ejecutado** con su motivo, y de ahí sale
  la reprogramación.
- **El cobro:** en dólares a la tasa del día, **por adelantado con fecha tope**, a una cuenta de
  IMATUR; voucher para transferencia y **acta de pago** para efectivo; exoneraciones las autoriza
  la Presidenta. **El sistema llevará la cuenta de lo cobrado y lo cancelado.**
- **7-8 niños por guía** como criterio para saber cuántos guías necesita una salida.
- El registro hoy es **papel + un Excel semestral**, con cortes mensuales para el informe de gestión
  trimestral.

### ❓ Solo nos quedan dos cosas de Rutas

**A. El oficio de permiso a los museos y castillos** 🟡

Nos contaron algo que no sabíamos: que IMATUR **manda un oficio a cada institución** (Castillo, Casa
Natal, fundaciones) para poder visitarla, que **meten toda la semana en un solo oficio** para
agilizar, y que llevan el control de si llegó, si dieron el pase o si lo rechazaron.

Eso vamos a construirlo. **Solo necesitamos ver uno** para que el impreso salga igual al suyo.

**B. El acta de pago en efectivo — ustedes nos pidieron proponerla** 🟢

Dijeron que *"el formato nace del momento"* y que les diéramos una idea. La vamos a proponer
nosotros con los datos mínimos que da respaldo —quién pagó, por cuál salida, cuánto, en qué fecha y
quién recibió— y **se la pasamos para su visto bueno** antes de dejarla fija. **No nos frena.**

---
---

# 5 · FORMACIÓN

### ✅ Lo que ya funciona

Talleres, charlas e inducciones con su ciclo completo; participantes adultos y niños; el informe
demográfico que se arma solo; las evidencias fotográficas; las listas de asistencia; los cambios de
estado automáticos por fecha; y los reportes, incluido el informe trimestral.

### 🔧 Lo que falta para completarlo

**Nada bloqueante.** El módulo está terminado. Lo que sigue son mejoras que solo construimos si las
quieren.

### ❓ Lo que necesitamos de ustedes *(todo opcional)*

**1. ¿Cuáles son sus metas anuales de formación y de rutas?**

El sistema compara lo planificado contra lo ejecutado, pero hoy tiene un valor de relleno (100
talleres y 100 rutas al año). Con las metas reales, ese indicador empieza a decir algo.

**2. ¿Una actividad puede tener más de un facilitador?**

Hoy se registra uno solo. Si normalmente son varios, lo cambiamos.

**3. ¿Quieren numerar los oficios de formación?**

Podemos numerarlos correlativamente (`FORM-001/2026`), pero antes necesitamos saber **qué dice ese
oficio y a quién va dirigido**. Sin eso estaríamos inventando un documento, que es justo el error que
evitamos con los formatos de Bienes.

---
---

# 6 · RECEPCIÓN (VISITAS)

### ✅ Lo que ya funciona

Registro de visitantes y control de entrada y salida, con el historial completo. Las visitas son una
**bitácora**: no se pueden borrar, solo consultar.

### 🔧 Lo que falta

**Nada.** Este módulo está completo y no depende de ninguna respuesta suya.

---
---

# 7 · SISTEMA Y TRANSVERSAL

### ✅ Lo que ya funciona

Usuarios, roles y permisos configurables desde pantalla; bitácora de cambios y papelera; búsqueda
global; campana de alertas; carnets; exportación a Excel y PDF desde cualquier listado; respaldos
automáticos de la base; y recuperación de contraseña por correo.

### ❓ Lo que necesitamos de ustedes

**1. Correo institucional para envíos automáticos** 🟡

Para que el sistema pueda enviar los correos de recuperación de contraseña necesitamos los datos de
acceso de `Sucreimatur@gmail.com` (o de la cuenta que prefieran): **servidor, puerto, usuario y
clave**. Si usan Gmail, hace falta una «contraseña de aplicación».

> Sin esto, la recuperación por correo no funciona. El respaldo actual es que el administrador
> restablezca la clave a mano.

**2. ¿Quieren un libro de correspondencia unificado?** *(opcional)*

Que liste en un solo lugar los oficios emitidos y recibidos de todos los módulos.

**3. ¿Desean cargar datos históricos?** *(opcional)*

Si tienen información en Excel o papel de años anteriores, ¿de qué módulos, y nos pueden facilitar los
archivos?

---
---

# Ya confirmado — no hace falta responder

Estas quedaron cerradas y ya están incorporadas al sistema:

- **Cargos:** son generales, los mismos para todos los departamentos. ✔️
- **Constancias de trabajo:** se emiten sin exigir tiempo mínimo de servicio. ✔️
- **Bienes** *(levantamiento del 2026-08-04/05, 59 preguntas)*: responsable del bien automático por
  departamento · costo y proveedor como control interno · baja y mantenimiento · la Oficina del
  Aeropuerto es un departamento propio bajo Planificación y Gestión Turística · destino del bien dado
  de baja · umbral de mobiliario por número de empleados. ✔️
- **Bienes, cambio del 2026-09-02:** IMATUR asignará el código de sus bienes continuando la secuencia ·
  la Alcaldía solo recibe la relación · el acta pasa a llamarse **Acta de Desincorporación**, por lote
  y sellada por la Alcaldía como aval · **no habrá oficio de retiro** · el acta de encargado y el
  oficio de donación siguen vigentes · los saltos en el N° de orden son por el orden del listado, no
  bajas · existe versión digital de los documentos y del inventario interno. ✔️
- **Nómina:** existe un formato de nómina quincenal aparte del de Liquidación · la cesta ticket la
  actualiza la UNAPRE cada mes · la «tasa BCV» es el tipo de cambio del dólar · la gobernación no paga
  caja de ahorro · los porcentajes de prima profesional por grado académico · la escala de antigüedad
  con tope de 30 %. ✔️
- **Rutas (respuestas del 2026-09-03):** existe un **catálogo** de rutas con su recorrido y sus puntos ·
  dos salidas de la misma ruta son **la misma ruta ejecutada dos veces** · puede haber **varios grupos
  el mismo día** con guías rotativos · **sí se cobra** (5 $ / 15 $ / 25 $ por persona, gratis para
  instituciones públicas y menores de 8 años) · la salida nace de una **solicitud** (particular o
  institucional por oficio) y **la aprueba la Presidencia** · se **cancela con motivo** (gasolina,
  clima, el grupo cancela) y se **reprograma conservando la misma salida** · los cinco puntos de Cumaná
  Histórica. ✔️

---

*Con los **documentos y respuestas** de cada módulo cerramos la programación pendiente. Con los
**datos** (personal, nómina, los 142 bienes) el sistema deja de estar vacío y queda operativo.*
