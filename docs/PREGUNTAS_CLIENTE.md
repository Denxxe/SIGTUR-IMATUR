# Lo que necesitamos para dejar el sistema al 100 % — **todo en un solo documento**

**Para:** IMATUR · **De:** equipo de desarrollo · **Actualizado:** 2026-10-06

El sistema está construido y probado: hoy se puede registrar, calcular e imprimir en todos los
módulos. Lo que falta para dejarlo al 100 % **ya casi no es programación**: son **documentos
(formatos), datos reales y algunas confirmaciones** que solo la institución puede dar. Aquí está
**todo junto, módulo por módulo**, para pedirlo de una sola vez. Cada pregunta trae entre paréntesis
un código (por ejemplo *B-73*) que nos ayuda a ubicar la respuesta; pueden responder debajo de cada
punto o por WhatsApp citando ese código. Al final hay una **lista de chequeo** con todo lo que hay
que entregarnos.

**Cómo leer las prioridades:** 🔴 sin esto una parte no puede terminarse · 🟡 importante, pero el
sistema ya funciona · 🟢 menor u opcional.

### Lo más urgente (top 5)

| | Qué | Por qué | Dónde |
|---|---|---|---|
| 1 | El **archivo digital del inventario** que lleva la encargada, y desde qué número arranca la codificación propia | Sin él no se pueden cargar los ~142 bienes ni asignar códigos | §2 · B-73 |
| 2 | Los **datos de cada trabajador** (sueldo, grado, fechas, hijos, cuenta) | Sin ellos no se puede emitir una nómina real | §1 · planilla al final |
| 3 | **Un mes de bono vacacional ya calculado** | Es lo único que falta para que el total se calcule solo | §1 · Nómina 1 |
| 4 | De dónde salen los **«días adicionales»** de la hoja de intereses | Es lo único que falta para construir la Liquidación de Prestaciones Sociales | §1 · N-3 |
| 5 | Los formatos del **Acta de Desincorporación** y del **acta de asignación** | Para que esas actas salgan con su formato oficial | §2 |

---

## Resumen — en qué está cada módulo

| Módulo | Estado | Qué le falta |
|---|---|---|
| **Personal (RRHH)** | ✅ Completo | Los datos del personal real |
| **Nómina** | 🟢 Calcula la quincena y el bono vacacional | La Liquidación de Prestaciones Sociales (1 respuesta) y los datos de cada trabajador |
| **Bienes (Inventario)** | 🟡 Funciona; falta la codificación propia | El archivo de la encargada, 2 formatos y las reglas de la codificación |
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
- La **fecha de ingreso a la administración pública** ahora se registra para **todo** el personal, no
  solo para comisión de servicio. Si un trabajador estuvo antes en la Alcaldía, la Gobernación u otro
  ente, esos años **cuentan** para sus vacaciones y su prima de antigüedad. *(Comisión de servicio es
  otra cosa: seguir cobrando en el otro ente mientras se trabaja en IMATUR.)*
- Al **registrar el sueldo** solo se escribe el sueldo básico (y la prima de discapacidad, si aplica):
  las demás primas las calcula el sistema.
- Convertir una falta en amonestación ahora **pide confirmación**, y una falta solo puede convertirse
  **una vez**.

### ❓ Preguntas — Nómina

**1. 🔴 Un mes de bono vacacional YA CALCULADO, con números reales**

La plantilla de nómina explica cómo se acumula el bono día a día, pero **no dice cómo se calcula el
monto final que se paga**. Para no inventar, el sistema muestra un **total estimado** al lado del total
que ustedes confirman. Con un solo mes real (uno ya pagado sirve) sabremos si la estimación es correcta:
si coincide, el total se calcula solo; si no, la diferencia nos dice qué corregir.

**2. 🔴 Hoja de INTERESES de la Liquidación: ¿de dónde salen los «días adicionales»? (N-3)**

*(Adjuntamos un recorte de la hoja con la columna señalada.)* Son los valores que van cambiando:
**79, 82, 120, 150 sobre 360**. ¿Salen de una tabla oficial, de un boletín, o los calcula Talento
Humano cada mes? **Es lo único que nos falta para construir la Liquidación de Prestaciones Sociales.**

**3. 🟡 Días base del bono vacacional: ¿75 para todos, o 75 / 85 / 45 según el tipo de personal? (N-1)**

La plantilla de nómina usa **75 días para todos**, incluidos obreros y contratados. Pero del formato del
bono entendimos **75** para Alto Nivel y Empleados Fijos, **85** para Obreros Fijos y **45** para
Contratados. Los dos criterios se contradicen: ¿cuál rige?

**4. 🟡 En el SSO y demás deducciones, ¿cuándo se usan 4 semanas y cuándo 5? (N-2)**

En la plantilla, unas hojas usan 4 y otras 5 **en el mismo mes**. ¿Depende del mes, del tipo de personal,
o fue un descuido?

**5. 🟡 ¿Qué tasa del dólar aplican, exactamente? (N-4)**

- ¿La **oficial del BCV**, o una que indica la Alcaldía o la Gobernación?
- Si es la del BCV, **¿de qué día?** (la del pago, la del último día del mes, la del día en que se arma
  la nómina)
- **¿Es una sola por mes?** En la plantilla aparecen **36,58 y 36,23 en hojas distintas del mismo
  período**. Si cada nómina lleva la suya, tenemos que ajustar el sistema, que hoy guarda una por mes.

*Mientras tanto el botón «Consultar BCV» funciona y la tasa también se puede escribir a mano.*

**6. 🟢 Dos detalles del bono de responsabilidad**

- ¿De dónde sale la **cantidad de divisas** que le corresponde a cada trabajador?
- ¿Ese bono aplica **solo** a Alto Nivel y a Comisión de Servicio?

### ❓ Preguntas — Personal

**7. 🟢 La planilla física de asistencia** *(opcional)*

Si quieren que el sistema imprima una planilla **igual** a la que usan hoy en papel, necesitamos ver una.
Sin ella el módulo funciona igual.

### 📎 Recaudos de este módulo

- **Los datos de cada trabajador activo** — ver la **planilla de datos por trabajador** al final.
- **El catálogo de cargos** (Manual Descriptivo de Cargos, o al menos los cargos en uso). Hoy hay 5.
- **Cesta ticket y tasa del dólar** de cada mes que se vaya a pagar, indicando de qué mes es cada monto.
- **Un mes de bono vacacional ya calculado** (pregunta 1).
- **El recorte de la hoja de INTERESES** respondido (pregunta 2).
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

**1. 🔴 ¿Desde qué número arranca la codificación propia? (B-73)**

Ahora IMATUR asigna sus propios códigos, continuando la secuencia. ¿Esperamos la última revisión de la
Alcaldía, o partimos del **mayor N° de orden** de su listado interno? *Sin este dato no se puede
asignar ningún código.*

**2. 🔴 ¿Cuál es la lista de grupos, subgrupos y secciones que pueden usar? (B-75)**

Antes la Alcaldía ponía esos valores y ustedes los copiaban. Si ahora IMATUR clasifica, el sistema
necesita la lista (o al menos los que usan hoy) para no permitir valores inventados. *El archivo de la
encargada probablemente la trae.*

**3. 🔴 Tres detalles del Acta de Desincorporación (B-79)**

- ¿**Quién más firma** por IMATUR, además de la Coordinadora de Bienes y la Presidencia?
- ¿Lleva **número correlativo**? *(hoy el sistema le pone uno propio)*
- *Ya está resuelto:* el acta sellada por la Alcaldía se carga escaneada y eso marca los bienes como
  retirados.

**4. 🟡 Reglas de la secuencia (B-74 y B-76)**

- ¿Es **una sola secuencia** para todo IMATUR, o una por cada grupo-subgrupo-sección? ¿Sigue siendo de
  3 dígitos — qué se hace al pasar de 999?
- El código de un bien desincorporado, ¿**se reutiliza** o nunca se recicla?

**5. 🟡 La relación de bienes nuevos (B-77, B-78 y B-81)**

- ¿El **monto** va en bolívares a la fecha de compra? ¿**Cada cuánto** se envía (por lote, mensual, al
  recibir cada bien)?
- ¿La Alcaldía **les devuelve algo** al recibirla (acuse, sello, un inventario nuevo)?
- El oficio que nos dieron es de **junio**: le pide a la Alcaldía que codifique y no tiene columna de
  código. ¿Se sigue usando **así**, o viene un formato nuevo con el código? *(Si es lo segundo, es solo
  agregar una columna.)*

**6. 🟡 ¿Tienen por escrito la notificación del procedimiento nuevo? (B-80)**

Cambia quién responde por la codificación; conviene tenerla en el expediente y no solo de palabra.

**7. 🟡 Confirmen la resolución y la gaceta de la Presidenta (B-82)**

Con sus documentos firmados corregimos lo que el sistema imprimía: **Resolución N° 32** del 05/09/2025 y
**Gaceta Municipal Extraordinaria N° 87** del 05/09/2025, cargo **Presidenta**. Aparece en constancias,
cartas de pasantes y oficios de rutas. **Solo necesitamos su visto bueno.**

**8. 🟢 ¿Quién es el abogado que visa el documento de donación? (B-83)**

Nombre y número de IPSA. Si no lo llenan, ese bloque simplemente no se imprime.

**9. 🟢 ¿Cuántos bienes le corresponden a cada trabajador?**

El reporte *Suficiencia de Bienes* compara lo que hay contra lo que debería haber, pero hoy usa
**números de ejemplo**. Aunque sea aproximado: ¿cuántas **sillas**, **escritorios** y **computadoras**
por empleado? ¿Qué cosas **no** se reparten por persona (un aire acondicionado es del espacio)?

**10. 🟢 Revisen las 11 categorías internas** que propusimos para agrupar los bienes en los reportes, y
díganos si encajan con cómo quieren verlos.

### 📎 Recaudos de este módulo

- 🔴 **El archivo digital del inventario que lleva la encargada.** Nos permite cargar los ~142 bienes de
  una vez, trae los códigos en uso (pregunta 2) y el último número (pregunta 1).
- 🔴 **Formato del Acta de Desincorporación** (la que firma la Alcaldía como aval). Hoy sale una versión
  provisional.
- 🔴 **Formato del acta de asignación** («acta de encargado»), la que firma el trabajador al recibir un
  bien.
- 🟡 La **notificación escrita** del procedimiento nuevo (pregunta 6), si existe.
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

**3. 🟢 Visto bueno al acta de pago en efectivo (R-39)**

Nos pidieron proponerla y ya está en el sistema: quién pagó, por cuál salida, cuánto, fecha y quién
recibió, con número correlativo. **Revísenla y díganos si la aprueban** o qué cambiar.

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

**3. 🟢 ¿Quieren numerar los oficios de formación? (D-NEW01)**

Podemos hacerlo (`FORM-001/2026`), pero antes necesitamos **ver ese oficio**: qué dice y a quién va.

### 📎 Recaudos de este módulo

- 🟢 Las **metas anuales** (pregunta 1).
- 🟢 Un **oficio de formación** de ejemplo, si quieren la pregunta 3.

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
- [ ] Catálogo de cargos
- [ ] Cesta ticket y tasa del dólar de cada mes a pagar
- [ ] Un mes de bono vacacional ya calculado
- [ ] Respuesta a la hoja de INTERESES (recorte adjunto) — N-3
- [ ] Respuestas N-1, N-2, N-4 y detalles del bono de responsabilidad
- [ ] Tabla de escala salarial por grado *(si existe)*
- [ ] Planilla física de asistencia *(opcional)*

**Bienes**
- [ ] Archivo digital del inventario de la encargada
- [ ] Formato del Acta de Desincorporación
- [ ] Formato del acta de asignación («acta de encargado»)
- [ ] Respuestas B-73, B-75, B-79 (las tres 🔴) y B-74, B-76, B-77, B-78, B-81
- [ ] Notificación escrita del procedimiento nuevo (B-80), si existe
- [ ] Visto bueno a Resolución 32 / Gaceta 87 (B-82)
- [ ] Nombre e IPSA del abogado visador (B-83)
- [ ] Dotación de bienes por empleado y revisión de las 11 categorías
- [ ] Asignar al Coordinador de Compra de Bienes y Servicios *(acción interna)*

**Rutas**
- [ ] Lista oficial de rutas con sus nombres (R-72)
- [ ] Un oficio de permiso a museos/castillos
- [ ] Respuesta sobre el cuadro de visitantes del custodio (R-71)
- [ ] Visto bueno al acta de pago (R-39)
- [ ] Excel semestral de rutas *(opcional)*

**Formación**
- [ ] Metas anuales de talleres y rutas *(opcional)*
- [ ] Respuestas D-FO08-bis y D-NEW01, con un oficio de ejemplo *(opcional)*

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
  tiempo mínimo de servicio.
- **Nómina:** existe una nómina quincenal aparte de la Liquidación · la cesta ticket la publica la UNAPRE
  cada mes · la «tasa BCV» es el tipo de cambio del dólar · la gobernación no paga caja de ahorro · los
  porcentajes por grado académico y la escala de antigüedad con tope de 30 %.
- **Bienes:** el levantamiento de agosto (59 preguntas) · el procedimiento nuevo de septiembre (IMATUR
  codifica, Acta de Desincorporación por lote, sin oficio de retiro) · existe la versión digital del
  inventario · los saltos en el N° de orden no son bajas · ya recibimos el oficio de relación, el
  documento de donación y el BM-1.
- **Rutas:** las 64 preguntas de septiembre, salvo las dos de arriba: catálogo y salidas, los tres
  estados, el cobro (cuenta de IMATUR, pago anticipado con fecha tope, voucher o acta, exoneraciones de
  la Presidenta), las tres modalidades de Cumaná Histórica, Exploradores de 4 a 16 años, 7-8 niños por
  guía, Altos de Sucre a convenir y sin directorio de posadas, el registro en papel más el Excel
  semestral, y los siete formatos recibidos.

---

*Con los **documentos y respuestas** cerramos la programación pendiente. Con los **datos** (personal,
nómina, los ~142 bienes y las rutas) el sistema deja de estar vacío y queda listo para operar.*
