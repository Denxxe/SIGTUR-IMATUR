# Lo que necesitamos para dejar el sistema al 100 % — **todo en un solo documento**

**Para:** IMATUR · **De:** equipo de desarrollo · **Actualizado:** 2026-10-11

El sistema está construido y probado: hoy se puede registrar, calcular e imprimir en todos los
módulos. Lo que falta para dejarlo al 100 % **ya casi no es programación**: son **documentos
(formatos), datos reales y algunas confirmaciones** que solo la institución puede dar. Aquí está
**todo junto, módulo por módulo**, para pedirlo de una sola vez. Cada pregunta trae entre paréntesis
un código (por ejemplo *B-73*) que nos ayuda a ubicar la respuesta; pueden responder debajo de cada
punto o por WhatsApp citando ese código. Al final hay una **lista de chequeo** con todo lo que hay
que entregarnos.

**Cómo leer las prioridades:** 🔴 sin esto una parte no puede terminarse · 🟡 importante, pero el
sistema ya funciona · 🟢 menor u opcional.

### Lo más urgente (top 4)

| | Qué | Por qué | Dónde |
|---|---|---|---|
| 1 | El **archivo actual del inventario** de la encargada, **con el departamento** de cada bien | Sin él no se pueden cargar los bienes reales | §2 · pregunta 1 |
| 2 | Los **datos de cada trabajador** (sueldo, grado, fechas, hijos, cuenta) | Sin ellos no se puede emitir una nómina real | §1 · planilla al final |
| 3 | Que la **directora de Talento Humano verifique en el sistema un mes de bono vacacional** | Es lo único que falta para que el total se calcule solo | §1 · Nómina 1 |
| 4 | Los formatos del **Acta de Desincorporación** y del **acta de asignación** | Para que esas actas salgan con su formato oficial | §2 |

---

## Resumen — en qué está cada módulo

| Módulo | Estado | Qué le falta |
|---|---|---|
| **Personal (RRHH)** | ✅ Completo | Los datos del personal real |
| **Nómina** | ✅ Calcula la quincena, el bono vacacional y **la Liquidación de Prestaciones Sociales** | Los datos de cada trabajador y las tasas del BCV |
| **Bienes (Inventario)** | 🟢 Funciona; la codificación propia **ya está lista**, esperando sus datos | El inventario actual con su departamento, 2 formatos y la última codificación de la Alcaldía |
| **Turismo (Rutas)** | ✅ Completo | La lista oficial de rutas y dos formatos menores |
| **Formación** | ✅ Completo | Solo mejoras opcionales |
| **Recepción (Visitas)** | ✅ Completo | Nada |
| **Sistema** | ✅ Funcionando | El correo saliente y los datos para instalarlo en producción |

---
---

# 1 · PERSONAL (RRHH) Y NÓMINA

### ✅ Lo que ya funciona

Ficha de cada trabajador con asistente paso a paso, expediente con sus recaudos, organigrama, cargos,
horarios, asistencia y puntualidad, permisos, vacaciones, faltas y amonestaciones, constancias, carnets,
egreso y reingreso, y traslados. La **nómina quincenal se calcula sola**: las primas salen del sueldo
básico, el grado de instrucción, los años en la administración pública y los hijos; los porcentajes se
editan desde la pantalla. El bono vacacional calcula con el mismo motor.

**Novedades de esta semana:**
- **Ya está la Liquidación de Prestaciones Sociales**, con las mismas tres hojas de su formato (FORMATO, INTERESES e INTERÉS DE MORA). Al egresar un trabajador, el sistema arma su cálculo mes a mes y paga la antigüedad por lo **mayor** entre lo depositado y los 30 días por año (art. 142 de la LOTTT). Se imprime para la firma y se exporta a Excel.
- **Todo es configurable.** Los días de garantía, los días adicionales y su tope, los 30 días por año, los días de vacaciones y los demás valores de la ley y del contrato se cambian en *Nómina → Parámetros*: si cambia la ley, el sistema sigue calculando sin tocar el programa.
- La **fecha de ingreso a la administración pública** ahora se registra para **todo** el personal, no
  solo para comisión de servicio. Si un trabajador estuvo antes en la Alcaldía, la Gobernación u otro
  ente, esos años **cuentan** para sus vacaciones y su prima de antigüedad. *(Comisión de servicio es
  otra cosa: seguir cobrando en el otro ente mientras se trabaja en IMATUR.)*
- Al **registrar el sueldo** solo se escribe el sueldo básico (y la prima de discapacidad, si aplica):
  las demás primas las calcula el sistema.
- Convertir una falta en amonestación ahora **pide confirmación**, y una falta solo puede convertirse
  **una vez**.

### ❓ Preguntas — Nómina

*Gracias por las respuestas del 06/10: con ellas quedó cerrado lo de las **4 semanas**. Lo que sigue
son **repreguntas cortas** sobre lo que quedó a medias.*

**1. 🔴 Verificar en el sistema un mes de bono vacacional** *(lo hace la directora de Talento Humano)*

La plantilla de nómina explica cómo se acumula el bono día a día, pero **no dice cómo se calcula el
monto final que se paga**. Para no inventar, el sistema muestra un **total estimado** al lado del total
que ustedes confirman, y la **diferencia** entre los dos. Ya no hace falta enviarnos nada:

1. Con los datos del personal cargados y la **cesta ticket del mes** en *Nómina → Parámetros*, generar
   un período de bono vacacional.
2. Escribir, para cada trabajador, el **total que ustedes calculan** como siempre.
3. Mirar la columna **Diferencia**: si da cero, el sistema calcula bien y desde entonces el total sale
   solo; si no, avisarnos **en qué trabajadores difiere y por cuánto**, y lo corregimos.

**2. 🟡 Una liquidación real ya calculada, y las tasas del BCV** *(para comprobar la Liquidación)*

Ya está claro cómo se calculan los días (gracias por confirmar que 79/82 son del bono vacacional y
120/150 del aguinaldo). Con eso y con su hoja de Excel **ya construimos la Liquidación**. Para darla por buena necesitamos:

- **Una liquidación que ustedes ya hayan calculado** de un trabajador real (la hoja llena, con sus números), para compararla con la del sistema; y
- **La tasa de interés de prestaciones sociales del BCV de cada mes** (la que ustedes escriben en la columna «INTERESES BCV» de la hoja). Se carga en *Nómina → Parámetros*. Sin ella, los intereses de ese mes quedan en 0 y el sistema lo avisa.

**3. ✅ Bono de responsabilidad y comisión de servicio** — *resuelta*

Lo cobra **solo el personal de alto nivel** (Presidencia y Direcciones), fijo o contratado, también si
está en comisión de servicio. Un trabajador en comisión que no es de alto nivel **no** lo cobra. El
sistema ya calcula así.

### ❓ Preguntas — Personal

**4. 🟢 La planilla física de asistencia** *(opcional)*

Si quieren que el sistema imprima una planilla **igual** a la que usan hoy en papel, necesitamos ver una.
Sin ella el módulo funciona igual.

### 📎 Recaudos de este módulo

- **Los datos de cada trabajador activo** — ver la **planilla de datos por trabajador** al final.
- **Cada mes, la cesta ticket y la tasa de la maqueta** de nómina: Talento Humano las escribe en
  *Nómina → Parámetros* antes de generar la quincena. *(El botón «Consultar BCV» es solo de referencia.)*
- **El resultado de la verificación del bono vacacional** (pregunta 1): si coincidió o en qué trabajadores difirió.
- **Una liquidación real ya calculada** y **las tasas de interés de prestaciones del BCV** de cada mes (pregunta 2).
- La **tabla de escala salarial por grado** que Talento Humano ofreció en una nota de voz *(si
  existe)*.

> ⚠️ **Recordatorio — errores en su plantilla de Excel de nómina.** Al estudiar sus fórmulas
> encontramos cuatro que afectan montos reales (prima de antigüedad al doble desde 23 años, FAOV
> patronal al 20 % en la hoja de Comisión, fórmula de antigüedad dañada en esa hoja, y la fila de
> Obreros del RESUMEN corrida una columna). **El sistema ya los calcula bien**; se los señalamos para
> que revisen los meses ya pagados con ese archivo.

---
---

# 2 · BIENES (INVENTARIO)

### ✅ Lo que ya funciona

Cada bien tiene su **hoja de vida** completa: datos de compra o donación, garantía, ubicación,
responsable (sale solo del departamento), movimientos con autorización, mantenimiento, documentos,
etiquetas con código QR y conteo por cambio de gestión con su acta. Se emiten el **oficio de relación
de bienes nuevos**, el **documento de donación** (con montos y fechas en letras) y el **Acta de
Desincorporación por lote**: al registrar el acta sellada, todos sus bienes quedan como retirados de
una vez. Quien solo debe consultar ve el módulo **sin botones de modificar**.

### ❓ Preguntas

*Gracias por las respuestas del 06/10 y por la planilla de muestra, que nos sirvió para ver cómo
llevan su listado. Quedaron cerrados: los códigos **no se reciclan**, el acta la firman **solo** la
Coordinadora y la Presidencia con correlativo de IMATUR, la Resolución 32 / Gaceta 87, las 11
categorías, y que la dotación se cuenta **por departamento**.*

**1. 🔴 El inventario actual, con el departamento de cada bien**

La planilla que nos mostraron es antigua y **no dice en qué departamento está cada bien**. Cuando
nos pasen el archivo vigente, por favor que traiga esa columna (o la sede/oficina): sin ella el
sistema no puede saber quién responde por cada bien. Pueden usar el mismo formato que ya llevan.

**2. 🔴 Punto de partida y grupos (B-73 y B-75)**: *seguimos esperando con ustedes*

*Novedad: el sistema **ya está preparado** para que IMATUR codifique. Solo falta escribir estos dos
datos en *Inventario → Codificación* y encenderlo.*

Entendido: esperan la **última codificación de la Alcaldía**. Cuando llegue, la necesitamos **completa**
(no solo una hoja): el BM-1 que tenemos ya llega al N° **171**.

**3. 🟡 Si la Alcaldía sale del circuito, ¿qué pasa con las actas? (B-78-bis)**

Nos dijeron que, después de la última codificación, **ya nada se envía a la Alcaldía** e IMATUR se
encarga de desincorporar, registrar y codificar. Entonces:

- El **Acta de Desincorporación**, ¿sigue llevando el **sello de la Alcaldía** como aval, o la firman
  solo ustedes?
- La **relación de bienes nuevos**, ¿deja de hacerse, o se sigue haciendo para el archivo de IMATUR?

**4. ✅ «Un acta por cada bien» (B-79-bis)** — *resuelta*

El **Acta de Desincorporación agrupa varios bienes** (así está hecha). Lo de «uno por cada bien» es el
**acta de incorporación**: se hace una por cada bien que entra.

**4-bis. 🟡 El acta de incorporación de bien (B-84)**

Hoy el sistema **no la tiene**: cuando entra un bien se registra su ficha, se adjunta la factura y se
emite la relación de bienes nuevos (que agrupa varios bienes), pero no un acta por bien. ¿Nos pueden
pasar una (foto o escaneado)? Y para no duplicar trabajo: ¿es la **misma** que el acta de asignación
(«acta de encargado») o es **otro** documento? ¿Es la que **reemplaza a la relación** cuando ya no se
envíe nada a la Alcaldía?

**5. ✅ Reglas de la secuencia (B-74)** — *resuelta*

**Una sola numeración** para todo IMATUR. Si algún día se pasa del 999, el sistema permite subir a 4
dígitos desde la pantalla de Codificación.

**6. ✅ El monto de la relación (B-77)** — *resuelta*

En bolívares, el de la compra. El sistema ya lo imprime así.

**7. 🟢 ¿Quién es el abogado que visa el documento de donación? (B-83)**

Nombre y número de IPSA. Si no lo llenan, ese bloque simplemente no se imprime.

**8. 🟢 ¿Cuántos bienes le corresponden a cada departamento?**

Ya sabemos que se cuenta **por departamento** (el aire es del espacio). Aunque sea aproximado: por
departamento, ¿cuántas sillas, escritorios, computadoras…?

### 📎 Recaudos de este módulo

- 🔴 **El archivo actual del inventario** de la encargada, **con el departamento** de cada bien (pregunta 1). *La planilla del 06/10 nos sirvió de muestra del formato, gracias.*
- 🔴 **Formato del Acta de Desincorporación** (la que firma la Alcaldía como aval). Hoy sale una versión
  provisional.
- 🔴 **Formato del acta de asignación** («acta de encargado»), la que firma el trabajador al recibir un
  bien.
- 🟡 **El formato nuevo de la relación de bienes**, con la columna de código, cuando lo tengan.
- ⚙️ **Una acción interna:** asignar en el sistema al **Coordinador de _Compra de Bienes y Servicios_**.
  Mientras el cargo esté vacante, el sistema **no permite movimientos de bienes** (todo movimiento lo
  autoriza esa coordinación).

---
---

# 3 · TURISMO (RUTAS)

### ✅ Lo que ya funciona

Todo el módulo, construido con sus respuestas de septiembre: **catálogo** de recorridos y **salidas**
por separado; estados *Programado → Ejecutado / No ejecutado* con reprogramación; solicitud con su
oficio y aprobación de la Presidencia; edades y restricciones por recorrido y cupo diario; varios
guías por salida; **cobro** en dólares a la tasa del día, con abonos, comprobantes, exoneraciones y
acta de pago; **oficios de permiso** a las instituciones custodias; y la **Ficha Institucional** que
se arma sola al cerrar la salida.

### ❓ Preguntas

**1. 🟡 ¿Cuántas rutas tiene el catálogo y cuál es el nombre oficial de cada una? (R-72)**

Los folletos traen **Playa Manare** (que no estaba en la lista anterior) y dicen **«Altos de Sucre»**,
no «Altos de Cumaná». Antes de cargar el catálogo real necesitamos la **lista oficial**: cargarla con
nombres equivocados es peor que no cargarla.

**2. 🟡 El cuadro de visitantes que pide cada punto (R-71)**

El Castillo de San Antonio pide un cuadro con *Niño / Niña / Adolescente / Mujer / Hombre / Adulto mayor*
y *Local / Nacional / Extranjero*, distinto de la Ficha Institucional. ¿Quieren que **el sistema lo
imprima** también, o lo llenan a mano allá?

**3. ✅ Acta de pago en efectivo (R-39)** — *aprobada*

La propuesta quedó aprobada tal como está: quién pagó, por cuál salida, cuánto (en cifras y en
letras), fecha y quién recibió, con número correlativo.

### 📎 Recaudos de este módulo

- 🟡 **Un oficio de permiso** de los que mandan a los museos y castillos (para que el impreso salga igual
  al suyo; hoy es provisional).
- 🟡 **La lista oficial de rutas** (pregunta 1), con sus paradas si es posible.
- 🟢 *(Opcional)* el **Excel semestral** de rutas, si quieren cargar el historial de este año.

---
---

# 4 · FORMACIÓN

### ✅ Lo que ya funciona

Talleres, charlas e inducciones con su ciclo completo, participantes adultos y niños, informe
demográfico automático, evidencias, listas de asistencia, estados que cambian solos por fecha y
reportes, incluido el informe trimestral.

### ❓ Preguntas *(todas opcionales)*

**1. 🟢 ¿Cuáles son sus metas anuales de talleres y de rutas? (D-FO05)**

El sistema compara lo planificado contra lo ejecutado, pero hoy usa un valor de relleno (100 y 100).

**2. 🟢 ¿Una actividad puede tener más de un facilitador? (D-FO08-bis)**

Hoy se registra uno. Si suelen ser varios, lo cambiamos.

**3. ✅ Oficio de formación numerado (D-NEW01)** — *hecho*

Ya está en el sistema: en el detalle de cada actividad, botón **«Oficio»**. Lleva número `FORM-001/2026`,
el membrete, la firma de la Presidencia y un texto propuesto con los datos de la actividad que se puede
corregir antes de emitir. Si tienen un oficio de formación de los que usan hoy, mándenlo y ajustamos el
texto para que salga igual.

### 📎 Recaudos de este módulo

- 🟢 Las **metas anuales** (pregunta 1).
- 🟢 *(Opcional)* Un **oficio de formación** de los que usan hoy, para que el texto propuesto salga igual.

---
---

# 5 · RECEPCIÓN (VISITAS)

### ✅ Lo que ya funciona

Registro de visitantes y control de entrada y salida, con historial. Las visitas son una **bitácora**:
no se borran, solo se consultan.

**Nada pendiente.** Este módulo no necesita nada de ustedes.

---
---

# 6 · SISTEMA

### ✅ Lo que ya funciona

Usuarios y **roles configurables desde pantalla**: un rol nuevo se crea marcando los módulos que verá,
sin programación (y para Bienes existe la casilla aparte *Bienes: registrar y modificar*). Bitácora de
cambios, papelera, búsqueda global, campana de alertas, exportación a Excel y PDF, respaldos
automáticos y recuperación de contraseña por correo.

### ❓ Preguntas

**1. 🟡 Correo para envíos automáticos (3.0)**

Para que lleguen los correos de recuperación de contraseña necesitamos **servidor, puerto, usuario y
clave** de la cuenta que prefieran (por ejemplo `Sucreimatur@gmail.com`; si es Gmail, una «contraseña de
aplicación»). Sin esto, el administrador restablece las claves a mano.

**2. 🟢 ¿Quieren un libro de correspondencia unificado? (D-OF03)**

Un solo listado con los oficios emitidos y recibidos de todos los módulos. Los de rutas ya se numeran y
llevan su libro.

**3. 🟢 ¿Desean cargar datos históricos? (D-TX03)**

Si tienen información de años anteriores en Excel o papel: ¿de qué módulos, y nos pasan los archivos?

---
---

# Lista de chequeo de recaudos — todo lo que hay que entregarnos

**Personal y Nómina**
- [ ] Datos de cada trabajador activo (planilla de abajo)
- [ ] Cada mes: cargar en el sistema la cesta ticket y la tasa de la maqueta
- [ ] Verificación de un mes de bono vacacional en el sistema (directora de Talento Humano)
- [ ] Una liquidación real ya calculada (para comparar)
- [ ] Tasas de interés de prestaciones sociales del BCV, mes a mes
- [x] ~~Respuesta sobre el bono de responsabilidad en comisión~~ (resuelta 2026-10-11)
- [ ] Tabla de escala salarial por grado *(si existe)*
- [ ] Planilla física de asistencia *(opcional)*

**Bienes**
- [ ] Archivo **actual** del inventario de la encargada, con el departamento de cada bien
- [ ] Formato del Acta de Desincorporación
- [ ] Formato del acta de asignación («acta de encargado»)
- [ ] Formato del acta de incorporación de bien (B-84) — y si es la misma que la de asignación
- [ ] Última codificación de la Alcaldía, completa (B-73, B-75)
- [ ] Respuestas B-78-bis, B-79-bis y B-74 (B-77 resuelta)
- [ ] Formato nuevo de la relación de bienes, con código
- [ ] Nombre e IPSA del abogado visador (B-83)
- [ ] Dotación de bienes por departamento
- [ ] Asignar al Coordinador de Compra de Bienes y Servicios *(acción interna)*

**Rutas**
- [ ] Lista oficial de rutas con sus nombres (R-72)
- [ ] Un oficio de permiso a museos/castillos
- [ ] Respuesta sobre el cuadro de visitantes del custodio (R-71)
- [x] ~~Visto bueno al acta de pago (R-39)~~ (aprobada 2026-10-11)
- [ ] Excel semestral de rutas *(opcional)*

**Formación**
- [ ] Metas anuales de talleres y rutas *(opcional)*
- [ ] Respuesta D-FO08-bis · *(opcional)* un oficio de formación de ejemplo (D-NEW01 ya está hecho)

**Sistema**
- [ ] Datos del correo saliente (servidor, puerto, usuario, clave)
- [ ] Respuestas D-OF03 y D-TX03 *(opcionales)*, con los archivos históricos si aplica
- [ ] Datos de configuración para producción (sección siguiente)

---

## Planilla de datos por trabajador

Por cada trabajador activo (un Excel con una fila por persona es perfecto):

| Dato | Detalle |
|---|---|
| Cédula, nombres y apellidos | |
| Cargo y departamento | |
| Tipo de personal | Alto Nivel · Empleado Fijo · Obrero Fijo · Contratado · Comisión de Servicio |
| Institución de origen | IMATUR · Alcaldía · Gobernación *(Alcaldía o Gobernación solo si **sigue** en esa nómina: comisión de servicio)* |
| Fecha de ingreso a IMATUR | |
| **Fecha de ingreso a la administración pública** | La **primera** vez que entró a trabajar en un ente público (Alcaldía, Gobernación, ministerio…). Si IMATUR es su primer empleo público, es la misma de ingreso a IMATUR. **Define sus vacaciones y su prima de antigüedad** |
| Fecha de vencimiento del contrato | Solo contratados |
| Sueldo básico mensual | Y prima de discapacidad, si cobra |
| Grado de instrucción | Bachiller · TSU · Licenciado o Ingeniero · Especialista · Magíster · Doctor |
| Carga familiar | Nombre, cédula (si tiene), fecha de nacimiento, género y parentesco de cada familiar — de aquí sale la prima por hijo |
| Banco y número de cuenta de nómina | |
| Bono de responsabilidad en divisas | Solo si aplica |

## Planilla de datos por bien

Si el archivo de la encargada no trae alguno de estos datos, no importa: cargamos lo que tenga.

| Dato | Detalle |
|---|---|
| Código actual | Grupo-subgrupo-sección y N° de orden, si ya lo tiene |
| Nombre y descripción | Marca, modelo y serial cuando aplique |
| Departamento y ubicación | Sede principal o Aeropuerto |
| Condición | Nuevo · Bueno · Regular · Dañado |
| Origen | Compra o donación; fecha y monto si se conocen |

---

## Datos de configuración para producción

Para instalar el sistema en el equipo definitivo necesitamos saber **quién define** cada uno de estos
datos (no los inventamos nosotros):

| Dato | Para qué |
|---|---|
| **Dirección (URL) con la que se entrará** | Por ejemplo, la IP del equipo en la red de IMATUR o un dominio |
| **Equipo donde quedará instalado** | Una PC de la oficina (con Docker) o un servidor |
| **Clave de la base de datos** | Reemplaza la de desarrollo |
| **Clave del usuario administrador** | La define una persona de IMATUR al recibir el sistema |
| **Datos del correo saliente** | Ver §6, pregunta 1 |
| **Quién recibe los respaldos** y dónde se guardan | El sistema hace respaldos automáticos diarios |

---

## Ya respondido — gracias, no hace falta volver a contestarlo

- **Personal:** los cargos son generales para todos los departamentos · las constancias no exigen
  tiempo mínimo de servicio · *(06/10)* el catálogo de cargos es el que ya está en el sistema; lo
  ajustan ustedes desde *Cargos* (agregar, modificar o eliminar).
- **Nómina:** existe una nómina quincenal aparte de la Liquidación · la cesta ticket la publica la UNAPRE
  cada mes · la «tasa BCV» de la nómina es el tipo de cambio del dólar (la de la hoja INTERESES de la Liquidación es otra: la tasa de interés de prestaciones) · la gobernación no paga caja de ahorro · los
  porcentajes por grado académico y la escala de antigüedad con tope de 30 % · *(06/10)* la base son
  **4 semanas** · los días adicionales de prestaciones siguen la ley (15 por trimestre + 2 por año) · en la hoja
  INTERESES, 79/82 son días del bono vacacional y 120/150 del aguinaldo ·
  días del bono vacacional: fijos 85 y contratados 45 (el alto nivel según su contrato), + 1 por año a todos · la cesta ticket y la tasa salen de la maqueta que reciben cada mes y se cargan a mano · las divisas del bono de
  responsabilidad las fija la contratación colectiva de la Alcaldía y lo cobra **solo el alto nivel**, aunque esté en comisión.
- **Bienes:** el levantamiento de agosto (59 preguntas) · el procedimiento nuevo de septiembre (IMATUR
  codifica, Acta de Desincorporación por lote, sin oficio de retiro) · existe la versión digital del
  inventario · los saltos en el N° de orden no son bajas · ya recibimos el oficio de relación, el
  documento de donación y el BM-1 · *(06/10)* los códigos no se reciclan · el acta la firman solo la
  Coordinadora y la Presidencia, con correlativo de IMATUR · Resolución 32 / Gaceta 87 confirmadas · la
  notificación del procedimiento fue de palabra · la relación se hace por cada bien nuevo · las 11
  categorías · la dotación es por departamento.
- **Rutas:** las 64 preguntas de septiembre, salvo las dos de arriba: catálogo y salidas, los tres
  estados, el cobro (cuenta de IMATUR, pago anticipado con fecha tope, voucher o acta, exoneraciones de
  la Presidenta), las tres modalidades de Cumaná Histórica, Exploradores de 4 a 16 años, 7-8 niños por
  guía, Altos de Sucre a convenir y sin directorio de posadas, el registro en papel más el Excel
  semestral, y los siete formatos recibidos.

---

*Con los **documentos y respuestas** cerramos la programación pendiente. Con los **datos** (personal,
nómina, los bienes y las rutas) el sistema deja de estar vacío y queda listo para operar.*
