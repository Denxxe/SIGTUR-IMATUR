# PLAN DEL MÓDULO DE NÓMINA — SIGTUR-IMATUR

**Fecha:** 2026-08-07 · **Actualizado:** 2026-08-27
**Estado:** fases **N‑A, N‑B, N‑C y N‑D construidas** (mig. 072 y 073). Falta solo **N‑E**
(Liquidación, bloqueada por N‑3). Las 3 preguntas siguen abiertas: N‑1 y N‑2 **ya no bloquean**
(entraron como parámetros) y la fórmula del **total del bono vacacional** —que no está en
ninguna fuente— se resolvió dejando la estimación del sistema al lado del total confirmado,
con la diferencia visible. Ver §6.4 y §6.5.
**Fuentes:** formato real `INSTITUTO IMATUR JULIO 2026.xlsx` (plantilla de nómina quincenal, **datos de
prueba** — solo las fórmulas son fuente de verdad) + 4 audios de Talento Humano del 2026-07-23
(transcripción en `docs/formatos/transcripcion_audios_rrhh_2026-07-23.md`).

> **Leer antes de tocar `NominaController`, `BonoVacacional`, `Sueldo` o `empleado_salarios`.**

---

## 1. Qué cambia respecto de lo que creíamos

El Bono Vacacional v1 (mig. 059) se construyó como **"registro + reporte"**: Talento Humano captura a
mano cada monto y el sistema solo lo organiza y lo exporta. Se hizo así porque no teníamos las
fórmulas. **Ahora las tenemos**, extraídas de la plantilla real, y cambian tres supuestos:

| Supuesto del v1 | Realidad |
|---|---|
| Hay 2 documentos de nómina (Bono Vacacional + Liquidación) | Hay **3**: se suma la **nómina quincenal regular**, que es el pago corriente |
| 4 tipos de personal | **5**: los 4 conocidos + **Comisión de Servicio**, con hoja y cálculo propios |
| Las primas son montos que se capturan | Las primas son **derivadas** por fórmula a partir de sueldo base, grado de instrucción, años de servicio y nº de hijos |
| El pago es mensual | Es **quincenal** (`sueldo_base_mensual / 2`) |

---

## 2. Estructura del formato real

Seis hojas. Las tres primeras comparten disposición; Contratados y Comisión difieren.

| Hoja | Contenido | Particularidad |
|---|---|---|
| `ALTO NIVEL` | Presidencia y Direcciones | Incluye **bono de responsabilidad en divisas** |
| `EMPLEADOS FIJOS INSTI` | Personal fijo | — |
| `OBREROS FIJOS INST` | Obreros | — |
| `CONTRATADOS 2` | Contratados | Añade `SALARIO BASE SEMANAL`; sin prima de antigüedad en el resumen |
| `NOMINA DE INSTITUTOS ASIGNA CO` | **Comisión de servicio** | Calcula la **diferencia** entre el sueldo del cargo y el que paga la dependencia de origen; bono de responsabilidad en divisas |
| `RESUMEN` | Consolidado por tipo de personal | Suma las 5 hojas |

En el sistema los 5 grupos ya son derivables sin captura adicional: los 4 primeros con
`BonoVacacional::tipoPersonal()`, y Comisión de Servicio con `institucion_origen <> 'IMATUR'`
(mig. 025, ver `CLAUDE.md` peculiaridad 18).

---

## 3. El cálculo, tal como lo hacen hoy

Todo por quincena, sobre `base = sueldo_base_mensual / 2`.

### 3.1 Asignaciones

| Concepto | Fórmula |
|---|---|
| Prima de profesionalización | `base × %` según grado de instrucción (tabla 3.2) |
| Prima de antigüedad | `base × %` según **años en la administración pública** (tabla 3.3) |
| Bono de transporte | `12,50 / 2 = 6,25` — fijo, igual para todos |
| Prima por hijos | `nº de hijos × 6,50` |
| **Total asignaciones** | `profesionalización + antigüedad + transporte + hijos` |
| **Total sueldo normal quincenal** | `base + total asignaciones` |

### 3.2 Prima de profesionalización — % por grado

| Código | Grado | % |
|---|---|---|
| `BACH` | Bachiller | 0 % |
| `TSU` | Técnico Superior Universitario | 20 % |
| `PROF` | Profesional / Licenciado | 25 % |
| `ESP` | Especialista | 30 % |
| `MAEST` | Magíster | 35 % |
| `DR` | Doctor | 40 % |

### 3.3 Prima de antigüedad — % por años en la administración pública

Escala con incrementos crecientes y **tope de 30 %**:

| Años | % | Años | % | Años | % |
|---|---|---|---|---|---|
| 1 | 1,0 | 9 | 9,8 | 17 | 21,2 |
| 2 | 2,0 | 10 | 11,0 | 18 | 22,8 |
| 3 | 3,0 | 11 | 12,4 | 19 | 24,4 |
| 4 | 4,0 | 12 | 13,8 | 20 | 26,0 |
| 5 | 5,0 | 13 | 15,2 | 21 | 27,8 |
| 6 | 6,2 | 14 | 16,6 | 22 | 29,6 |
| 7 | 7,4 | 15 | 18,0 | **≥23** | **30,0** |
| 8 | 8,6 | 16 | 19,6 | | |

> El incremento anual sube por tramos: 1,0 (años 1‑5) · 1,2 (6‑10) · 1,4 (11‑15) · 1,6 (16‑20) · 1,8 (21‑22), y se congela en 30 %.
>
> **Ojo:** se calcula sobre los años en la **administración pública** (`empleados.fecha_ingreso_administracion`, mig. 045), **no** sobre los años en IMATUR. La plantilla trae ambas columnas y usa la primera.

### 3.4 Deducciones y aportes

| Concepto | Fórmula |
|---|---|
| SSO 2 % (trabajador) | `(total × 12/52) × 0,02 × semanas` |
| FAOV 1 % (trabajador) | `total × 0,01` |
| LRPPF 0,5 % (trabajador) | `(total × 12/52) × 0,005 × semanas` |
| **Neto a cobrar** | `total − deducciones` |
| SSO patronal 4 % | `(total × 12/52) × 0,04 × semanas` |
| FAOV patronal 2 % | `total × 0,02` |
| RPE patronal 1,7 % | `(total × 12/52) × 0,017 × semanas` |

`semanas` vale **4 o 5** según la hoja — inconsistencia sin explicar, ver §5 punto 5.

### 3.5 Alícuotas y conceptos derivados

| Concepto | Fórmula |
|---|---|
| Sueldo normal diario | `((quincenal × 2) + cesta_ticket) / 30` |
| Alícuota de bono vacacional | `diario × (75 + años_admin) / 360` |
| Alícuota de bono de fin de año | `diario × 150 / 360` |
| Sueldo integral diario | `diario + alícuota vacacional + alícuota fin de año` |
| Días hábiles | `75 + años_admin` |
| Becas | `nº de hijos × 12,50` |
| Bono 50 % | `base × 0,5` |
| **Bono de responsabilidad** | **`(cantidad_divisas × tasa_dólar) / 2`** |

> **La "tasa BCV" es el tipo de cambio del dólar**, no un parámetro de contratación colectiva. El bono de
> responsabilidad se pacta **en divisas** y se paga en bolívares al cambio del mes. Esto explica por qué
> Talento Humano necesita la tasa cada mes y por qué aparece también en la hoja de intereses de la
> Liquidación. (En el audio, Talento Humano no lo tenía claro; la plantilla lo despeja.)

**Confirma la fórmula del Bono Vacacional v1:** el `(75 + años)/360` de la alícuota coincide con
`BonoVacacional::diasCorrespondientes()` = días base + años de servicio. Ese supuesto era correcto.

---

## 4. Implicación de diseño: `empleado_salarios` guarda lo que no debe

Hoy la tabla almacena **el monto de cada prima**, capturado a mano. Las fórmulas muestran que todas son
**derivadas**. Lo que hay que persistir son las **entradas**:

| Entrada necesaria | Estado en el sistema |
|---|---|
| Sueldo base mensual del cargo | ✅ `empleado_salarios.sueldo_basico` |
| Código de grado de instrucción (BACH/TSU/PROF/ESP/MAEST/DR) | ⚠️ Existe `personas.nivel_academico`, hay que **mapearlo** a estos 6 códigos |
| Años en la administración pública | ✅ `empleados.fecha_ingreso_administracion` (mig. 045) |
| Nº de hijos | ✅ Derivable de `carga_familiar` |
| Nº de cuenta bancaria de nómina | ❌ **No existe** — agregar |
| Cantidad de divisas del bono de responsabilidad | ❌ **No existe** — agregar (solo Alto Nivel y Comisión) |
| Sueldo que paga la dependencia de origen (comisión) | ❌ **No existe** — agregar |
| Tasa del dólar y cesta ticket del mes | ⚠️ Config sin histórico; **ambos cambian cada mes** → necesitan tabla con vigencia |

Las columnas de primas de `empleado_salarios` (`prima_profesional`, `prima_antiguedad`,
`prima_por_hijo`, `bono_transporte`) pasarían de **capturadas** a **calculadas**. Conviene conservarlas
como *snapshot* del período cerrado (igual que `bono_vacacional_detalle`), pero dejando de pedirlas en
el formulario. `caja_ahorro` puede quedar en cero por regla: **la gobernación no la paga** (audio 3).

---

## 5. Defectos encontrados en la plantilla del cliente

Están en las **fórmulas**, así que se arrastran a cualquier mes que se arme con este archivo. Verificados
contra los valores ya calculados por Excel, no deducidos a ojo.

| # | Defecto | Efecto |
|---|---|---|
| 1 | Tramo **≥23 años** de la prima de antigüedad usa el sueldo **mensual** en vez del quincenal (`O*0.3` en vez de `P*0.3`) | **Paga el doble** la prima a quien tenga 23+ años (comprobado: 112,80 donde corresponde 56,40) |
| 2 | FAOV patronal de la hoja de Comisión: `=(O6*0.2)` bajo un encabezado que dice **2 %** | Aporte patronal **10× sobreestimado** |
| 3 | Fórmula de antigüedad de la hoja de Comisión corrupta: contiene `IF(F6=C621,…)` (referencia a celda en vez del número 19) y `ij6f(F6=21,…)` | Los tramos de **19 y 21 años no funcionan**; las filas sin llenar producen primas **negativas** que entran en los `SUM` y dejan los totales del RESUMEN en negativo |
| 4 | Fila de **Obreros** del RESUMEN desplazada una columna: `J9` y `K9` apuntan ambas a `X10` | Cuenta el SSO **dos veces**, mete el FAOV en la casilla del LRPPF y **omite el LRPPF** |
| 5 | Semanas inconsistentes: Alto Nivel y Contratados usan **×4**, Empleados Fijos y Obreros **×5**, en el mismo mes | Deducciones y aportes no comparables entre hojas |
| 6 | Celdas con el resultado **pegado como número** en vez de fórmula (toda la fila de Obreros) | Si cambia el sueldo base, esa fila **no se recalcula** |
| 7 | El código de grado usado es `BCH`, pero las fórmulas comparan contra `"BACH"` | Hoy sin efecto (bachiller es 0 % de todos modos), pero cualquier variante mal tecleada de otro código cae al `else` y da **0 % en silencio** |

Automatizar el cálculo elimina los siete de raíz. Vale la pena mencionárselos al cliente: son la mejor
justificación del módulo.

---

## 6. Qué falta para construir

### 6.1 Confirmaciones pendientes (3)

| # | Pregunta | Por qué bloquea |
|---|---|---|
| N-1 | **Días base del bono vacacional: ¿75 para todos, o 75/75/85/45 por tipo?** La plantilla de nómina usa **75 en todas las hojas** (incluidas obreros y contratados); nuestra configuración tiene 85 y 45 | Se contradicen. Define `bono_vac_dias_*` y la alícuota |
| N-2 | **Criterio de las semanas (×4 / ×5)**: ¿depende del mes, del tipo de personal, o es un error? | Cambia toda la línea de deducciones y aportes |
| N-3 | **"Días adicionales" de la hoja INTERESES** de la Liquidación (79→82 / 120→150 sobre 360) | Único insumo que falta para diseñar la Liquidación. En el audio no entendió la pregunta: **reformular con un recorte de pantalla** |

Menores: de dónde sale la **cantidad de divisas** de cada trabajador, y si el **bono de responsabilidad**
aplica solo a Alto Nivel y Comisión.

### 6.2 Insumos operativos

- [ ] Sueldos base, grado, años de administración pública, nº de hijos y **cuenta bancaria** de cada empleado activo (hoy `empleado_salarios` tiene 1 fila de prueba).
- [ ] Monto de cesta ticket vigente **con su mes** (la plantilla de julio trae 22.907; el audio del 23/07 dice 28.388 — cambia todos los meses, lo publica la UNAPRE).
- [ ] Tasa del dólar del período (la plantilla de prueba trae 36,58 y 36,23 en hojas distintas).

### 6.3 Fases propuestas

| Fase | Alcance | Estado |
|---|---|---|
| **N‑A** | Motor de cálculo: tablas de % (profesionalización, antigüedad) como configuración, no como código; `Nomina::calcular()`; pruebas contra los valores de la plantilla | ✅ **HECHO** (mig. 072) |
| **N‑B** | Entradas que faltan: cuenta bancaria, divisas, sueldo de dependencia de origen; mapeo `nivel_academico` → código de grado; histórico mensual de cesta ticket y tasa | ✅ **HECHO** (mig. 072) |
| **N‑C** | Nómina quincenal: períodos, snapshot por empleado, cierre y **exportación de las 6 hojas** con `XlsxMultiSheet` | ✅ **HECHO** (mig. 072) |
| **N‑D** | Migrar Bono Vacacional v1 de captura manual a cálculo, reusando el motor de N‑A | ✅ **HECHO** (mig. 073) — con una salvedad: **el total sigue de captura** porque su fórmula no está en ninguna fuente. Ver §6.5 |
| **N‑E** | Liquidación de Prestaciones Sociales | 🔒 Bloqueada por **N‑3** |

### 6.4 Qué quedó construido (2026-08-27, mig. 072)

**Motor.** `Nomina::calcular()` es una **función pura**: recibe todas las entradas explícitas y
devuelve los 30 conceptos de la quincena sin tocar la BD. Por eso se puede probar contra los valores
ya calculados de la plantilla — hay **45 casos** en `tests/run.php`, incluido el que fija la prima de
antigüedad del tramo ≥23 años en **56,40** (el defecto #1 del cliente paga 112,80). Los intermedios
se calculan sin redondear y solo se redondea la salida, como hace Excel; redondear en cascada
desviaría los totales.

**Los porcentajes son datos, no código.** `nomina_grados` (6 filas) y `nomina_antiguedad` (23 filas,
con la fila 23 marcada como tope). Se sale del patrón H-07 a propósito: H-07 centraliza los valores de
dominio del *software*, y estos son cifras de contratación colectiva que el cliente renegocia.

**Nada de silencios.** Si el grado de instrucción no se reconoce, el empleado se **reporta** en vez de
cobrar 0 % — es exactamente el defecto #7 de la plantilla. Cada fila de `nomina_detalle` guarda sus
`advertencias` (sin sueldo registrado, grado no reconocido, sin cuenta bancaria, comisión sin sueldo de
origen) y la quincena las muestra agrupadas antes de dejar cerrar. Probado contra los 3 empleados
reales de la base: los 3 salieron con advertencias correctas, incluido uno cuyo `nivel_academico` es
«Universitario», que es ambiguo y no se mapea.

**Reconstruible.** `nomina_periodos` congela cesta ticket, tasa del dólar y semanas; `nomina_detalle`
guarda las **entradas** del cálculo además de los resultados. Un período cerrado se puede auditar
número por número. En Borrador se puede **recalcular** (incorpora correcciones de ficha sin perder el
período); cerrado queda inmutable — verificado que el recálculo se rechaza.

**Las dos preguntas abiertas no bloquean.** N‑1 (días base del bono vacacional) y N‑2 (semanas ×4/×5)
entran como **parámetros**: N‑1 en `configuracion_sistema`, N‑2 se elige por período en el propio
formulario, con la contradicción de la plantilla explicada ahí mismo. El cálculo funciona; lo que no
es definitivo es el número, hasta que el cliente confirme.

**Export.** 6 hojas (5 tipos de personal + RESUMEN) con `XlsxMultiSheet`. La hoja de Comisión de
Servicio agrega las dos columnas propias (sueldo de la dependencia y diferencia a pagar). Verificado
que el `.xlsx` generado es un ZIP válido de 6 hojas con los datos calculados dentro. De paso se
extrajo `XlsxMultiSheet::construir()` de `descargar()`, para poder verificar el archivo sin enviarlo.

> **Regla que no se negocia:** nada de esto se programa contra lo dicho en un audio. Cada número entra al
> código sólo si está en un formato del cliente o confirmado por escrito. Un `85` oído como `45` termina
> en un pago real.

### 6.5 Bono Vacacional migrado al motor (2026-08-27, mig. 073) — y el hueco que queda

Las primas, el sueldo normal diario y la alícuota ya pasan por `Nomina::calcular()`. Al compartir
motor con la quincenal, los dos documentos **no pueden discrepar** en la misma prima del mismo
trabajador. `BonoVacacional::TIPOS = Nomina::TIPOS` y `tipoPersonal()` delega, así que un trabajador
cae en la misma hoja en ambos documentos. Los días se cuentan **a la fecha de corte** del período, no
a hoy: generar un período pasado da el mismo número que dio entonces (antes no).

**El total no se pudo calcular, y no se inventó.** La plantilla del cliente documenta la *alícuota*
—el devengo diario— pero no el monto que se paga; el «mes de bono vacacional ya calculado con números
reales» que Talento Humano prometió en el audio del 23/07 no llegó. Ante eso había tres salidas:

1. dejar el total en captura pura, como estaba (el sistema no aporta nada);
2. deducir una fórmula y presentarla como buena (el error más caro posible en un módulo de pago);
3. calcular una estimación **bajo un supuesto declarado** y mostrarla junto al total confirmado.

Se tomó la tercera. `total_calculado` = `sueldo_normal_diario × días correspondientes`, etiquetado como
estimación en la BD (comentario de columna), en la UI y en el `.xlsx`. `total_bono_vacacional` sigue
siendo la cifra oficial. La UI, el cuadro resumen y el export muestran la **diferencia** entre ambos.

Eso convierte la pregunta pendiente en un instrumento que se responde solo: **en cuanto el cliente
entregue un mes real, la diferencia dice si el supuesto acierta.** Si acierta, el total pasa a
calcularse y `aceptarCalculados()` se vuelve el flujo normal; si no, la diferencia muestra por dónde
corregir. Mientras tanto el sistema no afirma un número que no puede sostener.

Detalles de operación: `recalcular()` **preserva los totales confirmados** y el grado/escala (no pisa
el trabajo de captura); `aceptarCalculados()` toma en bloque solo los que están vacíos, auditado igual
que la captura fila por fila; el período **exige el mes cargado** en `nomina_parametros_mes`, porque la
cesta ticket entra en el diario; y al cerrar se bloquean las tres vías de edición (verificado).

**Lo que queda de la fórmula N‑1 aquí:** los días del bono salen de `bono_vac_dias_*` (contrato
colectivo: 75/75/85/45/75) y la alícuota se recalcula con esos mismos días, no con
`nomina_dias_bono_vac_base`. Las dos fuentes se contradicen — es exactamente N‑1 — y por eso conviven
como parámetros hasta que el cliente aclare cuál manda.
