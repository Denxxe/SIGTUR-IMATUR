# Lo que necesitamos de IMATUR para cerrar el sistema

**Para:** IMATUR · **Actualizado:** 2026-08-27

El sistema está construido y funcionando. Lo que falta para dejarlo al 100 % **ya casi no es
programación**: son documentos, datos y tres confirmaciones que dependen de criterios de la institución.

Este documento lista **exactamente** eso, ordenado por lo que desbloquea. Pueden responder debajo de
cada punto.

---

## Resumen: qué falta y qué desbloquea

| | Cuántos | Qué desbloquea |
|---|---|---|
| **A. Formatos y respuestas que bloquean programación** | 6 | Los últimos 4 documentos que el sistema no puede generar todavía, y el módulo de Liquidación |
| **B. Datos para poner en marcha** | 7 | Que el sistema deje de estar vacío. Sin esto está correcto pero no sirve |
| **C. Confirmaciones que afinan números ya calculados** | 3 | El cálculo ya funciona; estas respuestas lo vuelven definitivo |
| **D. Decisiones opcionales** | 5 | Mejoras que solo construimos si las quieren |

**Los tres puntos más urgentes:** el **informe de bienes nuevos** (A1), el **mes de bono vacacional ya
calculado** (A5) y el **cuestionario de Rutas** (A6).

---

# A · Formatos y respuestas que bloquean programación

Sin estos no podemos construir. Todo lo demás del sistema ya está hecho.

### A1 ⭐ Informe u oficio de bienes nuevos — **el más urgente**

El documento que IMATUR le envía a la Alcaldía para que inspeccione y codifique los bienes nuevos.
Ustedes lo señalaron como su principal dolor. **Necesitamos el formato** (físico o digital) para que el
sistema lo genere; si lo inventamos, habría que rehacerlo.

### A2 Acta administrativa de baja + oficio de retiro

El acta que firman la Coordinadora de Bienes y la Presidencia cuando un bien se da de baja, y el oficio
que se le manda después a la Alcaldía para que lo retire.

> El proceso de baja **ya funciona** en el sistema: el bien sale del inventario activo y se distingue
> entre *Por retirar* y *Retirado*. Lo único que falta es el documento imprimible.

### A3 Acta u oficio de asignación de un bien a un trabajador

El documento que firma el trabajador cuando recibe un bien bajo su responsabilidad.

### A4 Oficio de donación

El sistema ya registra el origen «Donación» con su oficio adjunto; falta el formato para generarlo.

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

### A6 ⭐ Cuestionario de Rutas Turísticas

Se lo enviamos junto con el de Bienes (que sí respondieron completo, gracias). Es el **único módulo del
que todavía no tenemos el levantamiento**.

La pregunta que más pesa: cuando «Cumaná Histórica» se hace el 10 de marzo y otra vez el 20 de abril,
**¿son dos rutas distintas o la misma ruta ejecutada dos veces?** ¿Y existe un catálogo de rutas —con su
recorrido y paradas— que se reutiliza cada vez, o cada salida se arma desde cero?

De esa respuesta depende si el módulo queda como está o hay que rediseñar parte de él. **Preferimos
preguntar antes que rehacer después.**

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

Ya se puede cargar: la estructura y las ubicaciones están listas. Si tienen el inventario en Excel,
podemos cargarlo de una vez en lugar de teclearlo bien por bien.

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
  baja · numeración de la Alcaldía · umbral de mobiliario por número de empleados. ✔️
- **Nómina:** existe un formato de nómina quincenal aparte del de Liquidación · la cesta ticket la
  actualiza la UNAPRE cada mes · la «tasa BCV» es el tipo de cambio del dólar · la gobernación no paga
  caja de ahorro · los porcentajes de prima profesional por grado académico · la escala de antigüedad
  con tope de 30 %. ✔️
- **Rutas:** el facilitador externo y la institución participante se retiraron por no usarse. ✔️

---

*Con las respuestas de la sección **A** cerramos la programación pendiente. Con los datos de la sección
**B** el sistema queda operativo. La sección **C** vuelve definitivos los montos de nómina, que hoy ya
se calculan.*
