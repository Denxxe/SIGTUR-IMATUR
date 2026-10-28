# Módulo de Nómina — Reglas de Negocio

**Última actualización:** 2026-10-28 · **Migraciones:** hasta 090

> **Leer antes de tocar `NominaController`, `Nomina`, `BonoVacacional`, `Liquidacion`, `Sueldo` o
> `empleado_salarios`.** Historial de construcción en `CHANGELOG.md`; pendientes del cliente en
> `BACKLOG.md` §3.1.

## Contexto

Talento Humano produce **tres documentos**: la **nómina quincenal** (pago corriente), el **bono
vacacional** y la **liquidación de prestaciones sociales**. Los tres usan un único motor de cálculo
(`Nomina::calcular()`), de modo que la misma prima del mismo trabajador no puede salir distinta en dos
documentos.

**Fuentes:** las **fórmulas** de los formatos del cliente `INSTITUTO IMATUR JULIO 2026.xlsx` (nómina
quincenal) y `LIQUIDACION MES JULIO 2026.xls` (liquidación) — los datos de esos libros son de prueba,
solo las fórmulas son fuente de verdad — más las respuestas escritas N-1…N-4 (2026-10-06) y la del
bono de responsabilidad (2026-10-11).

---

## RN-NO01 — Todas las cifras son parámetros

Ninguna cifra de la LOTTT ni de la contratación colectiva está escrita en el código. Todas se editan
en **`/nomina/parametros`** (única pantalla; la sección de nómina se retiró de `/config` para no tener
los mismos valores en dos formularios). Si cambia la ley o el contrato, se corrige el valor y el
sistema sigue calculando. **Las cifras que aparecen en este documento son los valores por defecto**,
no reglas fijas.

| Dónde vive | Qué contiene |
|---|---|
| `nomina_grados` | % de prima de profesionalización por grado (RN-NO04) |
| `nomina_antiguedad` | % de prima de antigüedad por año; la última fila es el tope (RN-NO05) |
| `nomina_parametros_mes` | Cesta ticket y **tasa del dólar** de cada mes (RN-NO03) |
| `nomina_tasas_bcv` | **Tasa de interés de prestaciones** de cada mes + tasa activa opcional (RN-NO03) |
| `configuracion_sistema` | Escalares `nomina_*`, `bono_vac_dias_*`, `liq_*`, `vacaciones_dias_*` |

`NominaController::guardarEscalares()` solo acepta las claves declaradas por `Nomina::params()`,
`BonoVacacional::CONFIG_DIAS`, `Liquidacion::PARAMS_DEFAULT` y las de Vacaciones: el endpoint no
puede escribir otra configuración.

**Por qué en tablas y no en constantes:** el patrón H-07 (enums en constantes PHP) es para valores
de dominio del *software*; estos son cifras que el cliente renegocia.

---

## RN-NO02 — Cinco tipos de personal, derivados

Cada trabajador cae en una hoja según datos que ya existen, sin captura adicional
(`Nomina::tipoPersonal()`, que también usa el bono vacacional):

1. **Comisión de Servicio** — `institucion_origen` ≠ IMATUR. **Tiene prioridad**: un director en
   comisión va a esta hoja, no a Alto Nivel.
2. **Alto Nivel** — cargo de nivel Presidencia o Dirección.
3. **Contratados** — `tipo_contrato = 'Contratado'`.
4. **Obreros Fijos** — clasificación Obrero.
5. **Empleados Fijos** — el resto.

El export quincenal reproduce las **6 hojas** del formato (5 tipos + RESUMEN).

---

## RN-NO03 — Las dos «tasas BCV» son cosas distintas

| | Tasa del dólar | Tasa de interés de prestaciones |
|---|---|---|
| Qué es | Tipo de cambio Bs/USD | % **anual** de interés sobre prestaciones sociales |
| Dónde | `nomina_parametros_mes.tasa_dolar` | `nomina_tasas_bcv.tasa_prestaciones` (+ `tasa_activa`) |
| Para qué | Bono/prima de responsabilidad (se pacta en divisas) | Intereses y mora de la liquidación |
| De dónde sale | La **maqueta** de nómina que recibe Talento Humano cada mes, cargada a mano (N-4) | Publicación del BCV, cargada a mano por mes |

- **Tasa del dólar (N-4):** la que manda es la de la maqueta mensual, junto con la cesta ticket (lo
  único que varía con el dólar). El botón *Consultar BCV* (mig. 074) es solo referencia. El cálculo
  **nunca consulta internet**: usa la tasa congelada en el período.
- **Tasa de prestaciones:** la hoja INTERESES del cliente la muestra como % **mensual** = anual ÷ 12
  (p. ej. 4,86 = 58,35 ÷ 12). Se carga la anual; el sistema divide.

No confundirlas: comparten el nombre «BCV» en los formatos del cliente.

---

## RN-NO04 — Nómina quincenal: asignaciones

Todo por quincena, sobre **`base = sueldo_base_mensual / 2`**. El sueldo base sale del historial
salarial vigente a la fecha de corte (`Sueldo::actual()`, RN-NO10).

| Concepto | Fórmula | Parámetro (default) |
|---|---|---|
| Prima de profesionalización | `base × % del grado` | `nomina_grados` |
| Prima de antigüedad | `base × % por años en la administración pública` | `nomina_antiguedad` |
| Bono de transporte | `transporte mensual / 2` | `nomina_bono_transporte_mensual` (12,50) |
| Prima por hijos | `nº de hijos × monto por hijo` | `nomina_monto_por_hijo` (6,50) |
| **Total asignaciones** | suma de las cuatro | |
| **Sueldo normal quincenal** | `base + total asignaciones` | |

Las primas **se derivan, no se capturan**. El nº de hijos sale de `carga_familiar`.

**Prima de profesionalización — defaults:** BACH 0 % · TSU 20 % · PROF 25 % · ESP 30 % · MAEST 35 %
· DR 40 %. El código de grado sale de `personas.codigo_grado` (corrección manual) o, si no hay, del
mapeo de `personas.nivel_academico` (`Nomina::MAPEO_GRADO`). **Un valor no reconocido no cobra 0 % en
silencio:** el empleado sale con advertencia (p. ej. «Universitario» es ambiguo y no se mapea).

---

## RN-NO05 — Prima de antigüedad

- Se calcula sobre los años en la **administración pública** (`empleados.fecha_ingreso_administracion`),
  **no** sobre los años en IMATUR. Sin esa fecha se usa la de ingreso a IMATUR, con advertencia.
- Escala por defecto (23 filas): incremento anual de 1,0 (años 1-5), 1,2 (6-10), 1,4 (11-15),
  1,6 (16-20) y 1,8 (21-22), con **tope de 30 % desde los 23 años**:

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

La fila de mayor año se marca sola como tope y aplica a todos los años superiores.

---

## RN-NO06 — Deducciones y aportes patronales

`base_semanal = sueldo normal quincenal × 12/52 × semanas`.

| Concepto | Fórmula | Default |
|---|---|---|
| SSO trabajador | `base_semanal × %` | 2 % |
| FAOV trabajador | `sueldo normal quincenal × %` | 1 % |
| LRPPF trabajador | `base_semanal × %` | 0,5 % |
| **Neto a cobrar** | `sueldo normal − deducciones` | |
| SSO patronal | `base_semanal × %` | 4 % |
| FAOV patronal | `sueldo normal quincenal × %` | 2 % |
| RPE patronal | `base_semanal × %` | 1,7 % |

**Semanas (N-2):** por defecto **4** (`nomina_semanas_default`) y se puede elegir por período al
generarlo. La plantilla del cliente mezclaba ×4 y ×5 en el mismo mes.

---

## RN-NO07 — Alícuotas y conceptos derivados

| Concepto | Fórmula | Default |
|---|---|---|
| Sueldo normal diario | `((quincenal × 2) + cesta ticket) / días del mes` | `nomina_dias_mes` 30 |
| Días del bono vacacional | `días base de la condición + años en la administración` (RN-NO09) | |
| Alícuota de bono vacacional | `diario × días del bono vacacional / días del año` | `nomina_dias_base_anio` 360 |
| Alícuota de fin de año | `diario × días del aguinaldo / días del año` | `nomina_dias_aguinaldo` por año ¹ |
| Sueldo integral diario | `diario + alícuota vacacional + alícuota fin de año` | |
| Becas | `nº de hijos × monto` | `nomina_becas_por_hijo` 12,50 |
| Bono 50 % | `base × %` | `nomina_pct_bono_50` 50 |
| **Bono de responsabilidad** | `(divisas × tasa del dólar) / 2` | RN-NO08 |

Los intermedios no se redondean; solo la salida (como Excel). Redondear en cascada desvía los totales.

¹ Según el cliente, el aguinaldo fue de **120 días hasta 2022 y 150 desde 2023**. Desde la mig. 092 es
una escala por año (`nomina_dias_aguinaldo`, editable en *Nómina → Parámetros*): cada fila rige desde su
año hasta el anterior a la fila siguiente. La quincena toma los días del año de su fecha de corte y la
liquidación los del año de **cada mes**, así que un mes de 2022 sale con 120 sin corregirlo a mano
(`Nomina::diasAguinaldo()`). Reemplazó al escalar `nomina_dias_bono_fin_anio`.

---

## RN-NO08 — Bono de responsabilidad y comisión de servicio

- **Bono de responsabilidad:** se pacta **en divisas** (`empleados.divisas_bono_responsabilidad`, según
  la contratación colectiva de la Alcaldía) y se paga en bolívares a la tasa del dólar del mes.
  **Solo lo cobra el Alto Nivel** (fijo o contratado), **también si está en comisión de servicio**;
  un comisionado que no es de alto nivel no lo cobra (`Nomina::cobraResponsabilidad()`, decidido
  2026-10-11). La plantilla del cliente lo pagaba a toda la hoja de Comisión; manda la respuesta de
  Talento Humano. Si alguien que no es de alto nivel tiene divisas cargadas, se advierte y no se paga.
- **Comisión de servicio:** la hoja paga la **diferencia** entre el sueldo normal del cargo y el que
  paga la dependencia de origen (`empleados.sueldo_dependencia_origen`), nunca negativa:
  `max(0, sueldo normal − sueldo de origen)`. Sin sueldo de origen, advertencia.
- **Caja de ahorro:** 0 por regla — la gobernación no la paga.

---

## RN-NO09 — Bono vacacional

**Días (N-1, mig. 085):** van por **condición** del trabajador (`tipo_contrato`), no por tipo de
personal: **fijo 85, contratado 45** por defecto (`bono_vac_dias_fijo`, `bono_vac_dias_contratado`),
**+1 por año** en la administración pública **a todos**. Alto nivel y comisión siguen la misma regla
según su contrato. Es un beneficio de contratación colectiva superior a la LOTTT (art. 192). La
alícuota de la quincena y el bono vacacional leen los mismos días (`BonoVacacional::diasBase()`).
Los años se cuentan a la **fecha de corte** del período: regenerar un mes pasado da el mismo número.

**Total: estimación al lado del confirmado.** Las primas, el diario y la alícuota salen del motor;
el **total** no, porque su fórmula no está en ninguna fuente (la plantilla documenta la alícuota, no
el monto que se paga). En lugar de inventarla:

- `total_calculado` = `sueldo normal diario × días correspondientes` — **estimación bajo un supuesto
  declarado**, rotulada como tal en la BD, la pantalla y el `.xlsx`.
- `total_bono_vacacional` = la **cifra oficial**, la que confirma Talento Humano.
- Pantalla, resumen y export muestran la **diferencia** entre ambos.

La verificación la hace la directora de Talento Humano dentro del sistema: genera un período, captura
sus totales y lee la diferencia. Si el supuesto acierta, `aceptarCalculados()` pasa a ser el flujo
normal; si no, la diferencia indica por dónde corregir. Mientras tanto, el sistema no afirma un número
que no puede sostener.

**Operación:**
- Generar un período **exige el mes cargado** en `nomina_parametros_mes` (la cesta entra en el diario).
- `recalcular()` (solo en Borrador) **preserva los totales confirmados** y el grado/escala.
- `aceptarCalculados()` copia la estimación solo a las filas sin total confirmado; queda auditado
  igual que la captura fila por fila.
- Lo único que se captura a mano: el total confirmado y el grado/escala del cargo (no es el grado de
  instrucción y no se deriva de nada).
- **Cerrado** bloquea captura, aceptación en bloque y recálculo.

Distinto del módulo Vacaciones (mig. 045), que cuenta **días** de disfrute; éste calcula el **monto**.

---

## RN-NO10 — Período quincenal: congelado y reconstruible

- `empleado_salarios` es historial **append-only**: nunca UPDATE, siempre INSERT con `fecha_efectiva`;
  el vigente en una fecha es la última fila con `fecha_efectiva <= fecha`. Lo que se persiste son las
  **entradas** (sueldo base); las primas que guarda son snapshot, no captura.
- Generar un período **exige** cesta ticket y tasa del dólar del mes: mejor bloquear que producir un
  número plausible.
- `nomina_periodos` congela cesta, tasa y semanas; `nomina_detalle` guarda **entradas y resultados**:
  un período cerrado se audita número por número.
- **Borrador:** se puede recalcular (incorpora correcciones de ficha y re-congela los parámetros del
  mes sin perder el período). **Cerrado:** inmutable; el recálculo se rechaza.
- **Nada de silencios:** cada fila guarda sus `advertencias` (sin sueldo, sueldo 0, grado no reconocido
  o no vigente, sin fecha de ingreso a la administración, sin cuenta bancaria, comisión sin sueldo de
  origen, divisas sin derecho) y la quincena las muestra agrupadas antes de cerrar.

---

## RN-NO11 — Liquidación de prestaciones sociales (N-E, mig. 086)

Solo se liquida a personal **egresado**. **Período liquidado:** desde la última fecha de reingreso
anterior al egreso (un reingreso abre una relación nueva) o, si no hubo, desde el ingreso. El formato
del cliente tiene tres hojas y cada una tiene su equivalente:

| Hoja del cliente | En el sistema |
|---|---|
| `INTERESES` | `liquidacion_meses` + `Liquidacion::calcularMeses()` — un renglón por mes de servicio |
| `FORMATO` | `liquidaciones` + `Liquidacion::calcularResumen()` — el documento que se firma |
| `INTERES DE MORA` | `liquidaciones.detalle_mora` + `Liquidacion::calcularMora()` |

### Hoja INTERESES (por mes)

| Concepto | Fórmula |
|---|---|
| Total mensual | salario base + prima profesional + prima antigüedad + transporte + hijos |
| Salario diario | total / días del mes |
| Alícuota bono vacacional | diario × días del bono vacacional / 360 |
| Alícuota fin de año | diario × días del aguinaldo / 360 |
| Salario integral | diario + ambas alícuotas |
| Depósito | integral × días depositados |
| Saldo antes de interés | saldo anterior + depósito − anticipo |
| Interés | saldo antes de interés × (tasa anual de prestaciones / 12) % |
| Saldo | saldo antes de interés + interés — **se capitaliza** cada mes |

El salario integral de INTERESES **no lleva cesta ticket ni prima de responsabilidad**; el del
FORMATO sí lleva la de responsabilidad (divisas × tasa del dólar del mes del egreso, solo alto nivel).
Se respetan los dos.

### Días depositados (art. 142 a-b, N-3)

- **Garantía:** 15 días por trimestre (`liq_dias_garantia_trimestre`). Modo `calendario` (default,
  como la hoja): se depositan en marzo, junio, septiembre y diciembre; modo `ingreso`: cada 3 meses
  desde el ingreso (`liq_trimestre_modo`). Trimestre incompleto = 5 días por mes trabajado. En el mes
  del egreso se deposita lo que falte del trimestre en curso.
- **Adicionales:** 2 días por año desde el segundo año, acumulativos, con tope de 30
  (`liq_dias_adicionales_anio` · `_desde_anio` · `_tope` = 2 · 2 · 30): el 2.º aniversario deposita 2,
  el 3.º 4, etc. Se sigue la regla **escrita** del cliente: los 6/8/10 de la hoja de prueba no cuadran
  con ninguna fecha de ingreso coherente.
- **Días de las alícuotas (N-3):** la columna 79/82/120/150 de INTERESES son los días de las
  alícuotas: 79/82 del bono vacacional (días base + años) y 120/150 del aguinaldo.

### Hoja FORMATO

Último salario: `diario = subtotal mensual / 30` · alícuotas como arriba · **salario integral diario =
TRUNC(diario + alícuotas; 2)**.

| Concepto | Días | Salario |
|---|---|---|
| Antigüedad (art. 142 d) | la **mayor** entre garantía (Σ depósitos) y retroactivo (30 por año, +1 año si la fracción supera 6 meses — `liq_dias_retroactivo_anio`, `liq_meses_fraccion_retroactivo`) | integral (retroactivo) |
| Fracción de vacaciones (art. 190) | vencidas no disfrutadas (módulo Vacaciones) + (15 + años, tope 30) × meses del año de servicio en curso / 12 | normal diario |
| Fracción bono vacacional (art. 192) | días del bono vacacional × meses del año de servicio en curso / 12 | normal diario |
| Fracción fin de año | días del aguinaldo × meses del año civil / 12 | normal diario |
| Intereses (art. 143) | — | Σ intereses |
| Descuento de anticipos (art. 144) | — | − Σ anticipos |

**Art. 142 d:** la plantilla solo trae el literal c (retroactivo). La ley manda pagar lo mayor; el
sistema calcula los dos, paga el mayor y **dice cuál aplicó**.

### Mora

Interés simple sobre el subtotal, desde el mes siguiente al egreso hasta el mes del pago, a
`TRUNC(tasa anual / 12; 2)`: la tasa activa si está cargada; si no, la de prestaciones. Sin fecha de
pago no hay mora.

### Lo que el sistema no sabe, lo dice

Meses anteriores al primer sueldo registrado quedan en 0 con advertencia; un mes sin tasa de
prestaciones deja su interés en 0 con advertencia (no se inventa una tasa); las vacaciones vencidas
se toman del módulo Vacaciones y se piden verificar. Talento Humano puede corregir cualquier renglón
(queda marcado `editado`) o los días de cada fracción, y la cadena se recalcula entera.
*Reconstruir* vuelve a sembrar desde la ficha y descarta las correcciones.

### Ciclo de vida

Borrador → **Cerrada** (recibe `LIQ-NNN/AAAA` al cerrar, no al crear; inmutable) · cualquiera →
**Anulada** (con motivo; el número no se recicla y el egreso queda libre para liquidarse de nuevo).
**Una sola liquidación viva por egreso.** Cambiar un parámetro no altera una liquidación cerrada (guarda
sus resultados); una en borrador lo toma al recalcularla. Salidas: pantalla, imprimible del FORMATO
(firmas del trabajador y Talento Humano) y `.xlsx` de 3 hojas en el orden del formato del cliente.

---

## RN-NO12 — Principios de diseño

1. **Nada se programa desde un audio.** Cada número entra al código solo si está en un formato del
   cliente o confirmado por escrito: un `85` oído como `45` termina en un pago real.
2. **Cuando el formato contradice la respuesta escrita, manda la respuesta** (bono de responsabilidad,
   días adicionales). Cuando contradice la ley, manda la ley (art. 142 d).
3. **Ante una fórmula desconocida, estimación declarada y diferencia visible** — nunca un número
   inventado presentado como bueno (RN-NO09).
4. **Funciones de cálculo puras.** `Nomina::calcular()` y las de `Liquidacion` reciben todas las
   entradas (y `$params`) sin tocar la BD, y están probadas contra los valores ya calculados por Excel
   en `tests/run.php`.

---

## Defectos de la plantilla del cliente (por qué se automatizó)

Están en las **fórmulas**, así que se arrastran a cualquier mes armado con ese archivo. Verificados
contra los valores calculados por Excel. El sistema no reproduce ninguno.

| # | Defecto | Efecto |
|---|---|---|
| 1 | Antigüedad ≥23 años usa el sueldo **mensual** en vez del quincenal | Paga el doble (112,80 donde corresponde 56,40; hay prueba que fija 56,40) |
| 2 | FAOV patronal de Comisión: `×0.2` bajo un encabezado de 2 % | Aporte 10× sobreestimado |
| 3 | Fórmula de antigüedad de Comisión corrupta (`C621`, `ij6f`) | Tramos de 19 y 21 años no funcionan; filas vacías dan primas negativas que contaminan el RESUMEN |
| 4 | Fila de Obreros del RESUMEN desplazada una columna | SSO contado dos veces, FAOV en la casilla del LRPPF, LRPPF omitido |
| 5 | Semanas ×4 en unas hojas y ×5 en otras, el mismo mes | Deducciones no comparables (resuelto por N-2) |
| 6 | Resultados pegados como número en vez de fórmula (Obreros) | No se recalculan si cambia el sueldo |
| 7 | Se teclea `BCH` y se compara contra `"BACH"` | Cualquier código mal escrito da 0 % en silencio (por eso el sistema advierte, RN-NO04) |

---

## Pendientes del cliente (no bloquean)

Detalle en `BACKLOG.md` §3.1:
- Datos reales por empleado: sueldo base, grado, fecha de ingreso a la administración pública, hijos y
  cuenta bancaria.
- Cesta ticket y tasa del dólar de cada mes (maqueta mensual).
- Tasas de interés de prestaciones del BCV de los meses que abarquen las liquidaciones reales.
- Validación del total del bono vacacional (RN-NO09) y una liquidación real para comparar número a
  número.
