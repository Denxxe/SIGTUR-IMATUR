# Preguntas para definir — SIGTUR-IMATUR

**Para:** IMATUR · **Actualizado:** 2026-08-07

El sistema está funcional y operativo. Para **completar los puntos que dependen de criterios de la
institución**, necesitamos que nos confirmen lo siguiente. Cada punto indica brevemente *para qué* se
pregunta, de modo que puedan responder sin entrar en lo técnico.

> Pueden responder directamente debajo de cada pregunta. Las marcadas con ⭐ son las más importantes
> (son las que más falta hacen para cerrar el sistema).

---

## 1. Nómina ⭐ (lo más importante)

**Gracias por la plantilla de nómina quincenal.** Con ella pudimos entender el cálculo completo
—primas, deducciones, aportes y alícuotas— así que **la mayoría de nuestras dudas quedaron resueltas**.
Solo nos quedan tres:

1. ⭐ **Días base del bono vacacional: ¿son 75 para todo el personal, o 75 / 85 / 45 según el tipo?**
   En la plantilla de nómina, el cálculo de la alícuota usa **75 días para todos** (incluidos obreros y
   contratados). Pero en el formato de Bono Vacacional entendimos 75 para Alto Nivel y Empleados Fijos,
   **85** para Obreros Fijos y **45** para Contratados. Necesitamos saber cuál de los dos criterios rige.

2. ⭐ **En el cálculo del SSO y demás deducciones, ¿cuándo se usan 4 semanas y cuándo 5?**
   En la plantilla, las hojas de Alto Nivel y Contratados calculan con **4 semanas** y las de Empleados
   Fijos y Obreros con **5**, en el mismo mes. Queremos saber si eso depende del mes, del tipo de
   personal, o si fue un descuido.

3. ⭐ **Sobre la hoja de INTERESES de la Liquidación: ¿de dónde salen los "días adicionales"?**
   *(Adjuntamos un recorte de la hoja con la columna señalada.)* Nos referimos a los valores que van
   cambiando —79, 82, 120, 150 sobre 360—. ¿Los toman de una tabla oficial, de un boletín, o los calcula
   Talento Humano cada mes? **Esta es la única pregunta que nos falta para poder construir la
   Liquidación de Prestaciones Sociales.**

**Además, dos cosas menores:** ¿de dónde sale la **cantidad de divisas** que le corresponde a cada
trabajador en el bono de responsabilidad? ¿Y ese bono aplica solo a Alto Nivel y Comisión de Servicio?

### Datos que necesitamos para poner la nómina en marcha

- Sueldo base, grado de instrucción, años de servicio en la administración pública, número de hijos y
  **número de cuenta bancaria** de cada trabajador activo.
- **Monto de cesta ticket vigente**, indicando de qué mes es (entendemos que la UNAPRE lo actualiza cada mes).
- **Tasa del dólar** del período que corresponda.
- La **tabla de escala salarial por grado** que nos mencionaron.

### ⚠️ Detectamos algunos errores en la plantilla de Excel

Al estudiar las fórmulas encontramos cuatro que conviene revisar, porque afectan montos:

1. La **prima de antigüedad de quienes tienen 23 años o más** se calcula sobre el sueldo mensual en vez
   del quincenal, así que **queda al doble** de lo que debería.
2. En la hoja de Comisión de Servicio, el **aporte patronal de FAOV está al 20 %** cuando el encabezado
   dice 2 %.
3. En esa misma hoja, la fórmula de la prima de antigüedad **está dañada** y no funciona para 19 ni 21
   años de servicio; además, las filas en blanco generan montos negativos que se cuelan en los totales.
4. En la hoja de RESUMEN, la fila de **Obreros** toma las cifras de columnas corridas: cuenta el SSO dos
   veces y deja el LRPPF por fuera.

Cuando el sistema haga el cálculo, estos errores desaparecen solos.

---

## 2. Inventario de bienes

**Levantamiento completo.** Nos respondieron las 59 preguntas y ya está construido. Solo falta que nos
faciliten **cuatro formatos en físico o digital**, que son lo único que nos impide generar esos
documentos desde el sistema:

4. ⭐ **Informe u oficio de bienes nuevos** que IMATUR le envía a la Alcaldía *(el más urgente)*.
5. **Acta administrativa de baja** y el **oficio de retiro** que se manda después.
6. **Acta u oficio de asignación** de un bien a un trabajador.
7. **Oficio de donación.**
8. ¿Existe el **BM-1 en digital** (Excel o Word)? Si la Alcaldía lo arma en computadora, podríamos cargar
   los códigos automáticamente en vez de teclearlos bien por bien.

> También necesitamos **cargar los bienes reales** (~142) y que nos confirmen que las 11 categorías
> propuestas les sirven para agrupar.

---

## 3. Turismo (Rutas)

9. ⭐ **Nos falta el cuestionario de Rutas** (lo enviamos junto con el de Bienes). La pregunta más
   importante es: cuando "Cumaná Histórica" se hace el 10 de marzo y otra vez el 20 de abril, **¿eso son
   dos rutas distintas o la misma ruta ejecutada dos veces?** De esa respuesta depende buena parte del
   diseño del módulo.

10. **Ruta con tarifa: ¿quién cobra, cómo y cuándo se paga?** Hoy el sistema tiene el campo pero nunca se
    llena, así que el reporte muestra todas las rutas como gratuitas.

11. **Al *finalizar* una ruta, ¿el sistema debe generar automáticamente un informe u oficio?**

---

## 4. Formación (Talleres / Charlas)

12. **¿Una actividad de formación puede tener más de un facilitador?** (hoy se registra uno solo).

13. **¿Quieren parámetros de meta para comparar lo planificado contra lo ejecutado por período?**

14. **¿Activamos la numeración correlativa de oficios de formación** (por ejemplo, FORM-001/2026)?

---

## 5. Recursos Humanos y otros

15. **Planilla física de asistencia: ¿nos pueden facilitar el formato impreso oficial que usan?**, para
    que el sistema genere una versión idéntica imprimible.

16. **Datos históricos: ¿desean cargar al sistema información anterior** (que hoy esté en Excel o en
    papel)? Si es así, **¿de qué módulos y nos pueden facilitar los archivos?**

17. **¿Desean un "libro de correspondencia" unificado** que liste en un solo lugar los oficios emitidos y
    recibidos?

18. **Correo institucional:** para que el sistema pueda enviar los correos de recuperación de contraseña
    necesitamos los datos de la cuenta `Sucreimatur@gmail.com` (o de la que prefieran usar).

---

### Ya confirmado (no requiere respuesta)

- **Cargos:** son generales, los mismos para todos los departamentos. ✔️
- **Constancias de trabajo:** se emiten sin exigir un tiempo mínimo de servicio. ✔️
- **Bienes:** responsable del bien, costo y proveedor, baja y mantenimiento, sede del aeropuerto,
  destino del bien dado de baja, numeración de la Alcaldía. ✔️ *(levantamiento del 2026-08-04)*
- **Nómina:** existe un formato de nómina regular aparte del de Liquidación · la cesta ticket la actualiza
  la UNAPRE cada mes · la "tasa BCV" es el tipo de cambio del dólar · la gobernación no paga caja de
  ahorro · los porcentajes de prima profesional por grado académico. ✔️
- **Rutas:** el facilitador externo y la institución participante se retiraron por no usarse. ✔️

---

*Una vez recibidas las respuestas, estimamos el trabajo de cada punto y lo incorporamos. Las respuestas
de la sección 1 (Nómina) y los formatos de la sección 2 (Bienes) son los que más ayudan a cerrar el
proyecto.*
