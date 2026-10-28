# Módulo de Rutas Turísticas — Reglas de Negocio

**Última actualización:** 2026-10-28 · **Migraciones:** hasta 090
**Fuente de las reglas:** respuestas del cliente R-01…R-72 (`docs/entregables/PREGUNTAS_DESCUBRIMIENTO_Bienes_Rutas.md`, Parte 2, y `docs/BACKLOG.md` §3.5) + formatos reales en `docs/formatos/rutas_*`
**Pendientes del cliente:** `docs/BACKLOG.md` §3.5 · **Historial de construcción:** `docs/CHANGELOG.md`

## Contexto institucional

Las rutas las gestiona el **Departamento de Rutas Turísticas y Proyectos** (Dirección de
Planificación y Gestión Turística). Frecuencia: **2-3 salidas semanales** más las que se piden
puntualmente (R-04). Grupo típico **15-30 personas**, máximo histórico **120** (R-05). Los tres
dolores que nombró el cliente (R-06): **logística**, **puntualidad del turista** y **coordinación con
las instituciones custodias** de los puntos — el módulo de permisos (RN-RT10) atiende el tercero.

Programas declarados (R-02, corregidos por R-41/R-65/R-66/R-67):

| Ruta | Tarifa | Público |
|------|--------|---------|
| **Cumaná Histórica** | 5 $ por adulto · menores de 8 gratis · gratuita para instituciones públicas (R-03) | Todo público. **Tres modalidades del mismo recorrido** (R-65): la comercial, *Exploradores de Cumaná* y *«Huellas del Ayer»* (adultos mayores); no se modelan como filas aparte |
| **Exploradores de Cumaná** | Gratuita | Instituciones educativas, **4 a 16 años** (R-66; R-02 decía 4-8) |
| **Playa Las Maritas** · **Playa Colorada** | 25 $ por persona | — |
| **Río Brito** | 15 $ por persona | **12 años o más**; excluye dificultad visual; advertir condición articular (R-57) |
| **Altos de Sucre** | **A convenir** — *«depende de lo que el cliente solicite»* (R-41/R-67) | Enlace con posadas; todas las rutas trabajan con aliados locales (R-60) |

> Los folletos traen además **Playa Manare** (full day, 8:00 a. m.-5:00 p. m.) y llaman «Altos de
> Sucre» a lo que R-02 llamó «Altos de Cumaná». **El catálogo real no se carga hasta R-72** (lista
> oficial): cargarlo con la lista equivocada es peor que no cargarlo.

---

## RN-RT01 — Catálogo ≠ salida

`rutas` es el **catálogo** reutilizable (qué ofrece IMATUR: recorrido, puntos, duración, tarifa,
restricciones). `ruta_ejecuciones` es cada **salida** (una fecha, un grupo). El cliente lo dijo tres
veces: *«existe un catálogo»* (R-08), varias salidas de la misma ruta la misma mañana con guías
rotativos (R-09), y *«así sean la misma ruta, es considerada 2 salidas»* (R-07).

- Participantes, personal, itinerario, cobro, Ficha Institucional y oficios cuelgan de la **salida**
  (`id_ejecucion`), nunca del recorrido. `participantes_ruta.id_ruta` / `ruta_informes.id_ruta`
  son columnas viejas, nulas, solo para filas migradas.
- `rutas.fecha_visita`, `hora_visita`, `id_facilitador` y `cupo_maximo` siguen en la tabla pero
  están **obsoletas**: el código ya no las escribe.

## RN-RT02 — Ruta del catálogo

| Campo / regla | Detalle |
|---|---|
| Estado (`Ruta::ESTADOS`) | `Activa` · `Inactiva` · `En Mantenimiento` — si se ofrece o no. Se puede cambiar siempre. **`En Mantenimiento` exige motivo** (`motivo_mantenimiento`). `Finalizada` desapareció en la mig. 078: describía una salida |
| Tipo (`Ruta::$TIPOS_RUTA`) | `Cumaná Histórica` · `Exploradores de Cumaná` · `Comunitaria` · `General` (default) |
| Duración estimada | Formato `H:MM`. Lo normal es **1:30**, máximo **3:00**; se lleva por punto y total (R-19) |
| Edad (`edad_min`/`edad_max`) | Opcionales, 0-120; NULL = sin tope. Mínima ≤ máxima. Ver RN-RT06 |
| Restricciones (`restricciones`) | Texto libre para lo que no es edad (R-57). Se muestra en la ficha del recorrido **y al inscribir** |
| Prerequisito de formación (`requiere_formacion`) | Ver RN-RT06 |
| Baja | Bloqueada si la ruta tiene salidas: se deja de ofrecer con `Inactiva`; borrarla se llevaría el histórico |

**Tarifa (mig. 083, R-36/R-41).** Se pacta en **USD** y se cobra en Bs a la tasa del día. Tres modos
(`rutas.tarifa_modo`): *Gratuita* · *Fija* (exige monto > 0) · *A convenir* (el monto se pacta en cada
salida). `tiene_tarifa` se mantiene **derivada** (modo ≠ Gratuita) solo para consultas viejas.

**Exoneraciones del catálogo — son por ruta, no globales** (R-03/R-42):

- `exonera_instituciones`: las instituciones públicas no pagan **este** recorrido. Las instituciones
  no pagan Cumaná Histórica pero sí Playa Colorada, y **igual deben traer el oficio**.
- `exonera_menores_de`: edad bajo la cual no se cobra (Cumaná Histórica: 8). Hoy es **informativa**:
  se captura y se muestra, pero el cálculo del esperado no la descuenta (ver RN-RT07).

## RN-RT03 — Puntos del recorrido

- Orden, nombre, descripción y coordenadas opcionales; el mapa usa **Leaflet vendorizado en local**
  (sin CDN). Cumaná Histórica tiene **7 puntos** (`rutas_itinerario_CumanaHistorica_7puntos*.jpeg`).
- El orden del catálogo es el **sugerido**: el guía lo varía (R-10/R-18) — ver RN-RT05.
- `ente_custodio` (R-06/R-20): institución que custodia el punto. Alimenta «a quién hay que pedirle
  permiso esta semana». Una plaza pública no tiene custodio y no genera trámite.
- `tiene_guia_externo` (R-31): museos y casas natales ponen su propio guía. Solo se registra **que
  lo hay**; no se reintroduce un facilitador externo en la ruta.

## RN-RT04 — Salida: solicitud, aprobación y ciclo de vida

**Estados (R-14, con las palabras del cliente; CHECK en BD):**

```
Programado ──► Ejecutado                      (terminal)
     └──────► No ejecutado (+ motivo)         (terminal)
                   └──► reprogramar: salida NUEVA enlazada (id_reprogramada_de)
```

| Regla | Detalle |
|---|---|
| Fecha | Una salida nueva no puede programarse en el pasado. Varias salidas el mismo día, de la misma ruta o de otras (R-09) |
| Origen (R-11) | `Particular` o `Institucional`. La institucional **exige** el nombre de la institución solicitante; la particular no lo guarda |
| Oficio de solicitud (R-12) | Es **entrante**: lo redacta cada institución (no hay formato único) e IMATUR lo **archiva**. PDF/JPG/PNG ≤ 5 MB, MIME real, fuera del web root, servido por `DescargaController::oficioRuta()`. Quitarlo desvincula pero no borra el archivo |
| Aprobación (R-13) | La da la **Presidencia**, a toda salida (particular o institucional). Se registra quién la asentó y la fecha; no se aprueba dos veces. Es un **hito**, no un estado |
| Cancelar | **No es un estado.** Cancele el grupo o IMATUR (clima, agua, gasolina…), el resultado es `No ejecutado` con **motivo obligatorio** (R-15) |
| Ejecutado | No se marca con fecha futura. Al marcarlo **nace la Ficha Institucional** (RN-RT08) |
| Terminales | `Ejecutado` y `No ejecutado` no vuelven atrás ni se editan: ni datos, ni cobro, ni itinerario, ni inscripciones |
| Reprogramar (R-14/R-16) | Solo desde `No ejecutado`, **una sola vez**, fecha no pasada. Crea otra salida que hereda ruta, hora, cupo, origen, institución, oficio y observaciones. **La original queda intacta** como constancia de lo que no ocurrió — así el histórico no se pierde y las ejecutadas no se inflan |
| Incidencias (R-45) | Texto libre en la salida |
| Eliminar | Soft delete; una salida `Ejecutado` no se elimina (es histórico) |

## RN-RT05 — Itinerario y personal de la salida

**Itinerario (mig. 082, R-10/R-18).** Sin filas en `ruta_ejecucion_itinerario` la salida sigue el
orden del catálogo (el caso normal); con filas, mandan ellas, **solo para esa salida**. Si dos grupos
coinciden se altera el de uno para que no se crucen, sin tocar el recorrido ni otras salidas.

- Se guarda en bloque: debe incluir **todas** las paradas del recorrido, sin órdenes repetidos.
- Una parada puede marcarse **omitida** con nota — típicamente porque el custodio rechazó el permiso
  (R-20). Sigue en el recorrido; cambia ese día.
- «Restablecer» vuelve al orden del catálogo. No editable en estado terminal.

**Personal (`ruta_ejecucion_empleados`, R-31/R-33/R-34):**

- La salida la encabeza **siempre un empleado de IMATUR**; **uno solo es el encargado** (marcar otro
  desmarca al anterior). Un empleado no se asigna dos veces a la misma salida.
- Número variable según el grupo: el sistema **sugiere** 1 guía por cada **8** personas
  (`RutaEjecucion::PERSONAS_POR_GUIA`, R-33: «7-8 por guía»). **No bloquea**: depende de los disponibles.
- La certificación **no es requisito** para guiar (R-34): no se bloquea ni se alerta por vencimiento.
- La lista nominal firmada (`rutas_lista_asistencia_nominal_IMATUR.jpeg`) es la asistencia de **este
  personal**, no de los participantes.

## RN-RT06 — Participantes e inscripción

**Del grupo visitante IMATUR lleva el conteo, no los nombres** (R-23/R-24/R-29): eso va en la Ficha
Institucional. `participantes_ruta` queda para el registro individual — sobre todo el **particular
de pago**, del que se toma la localidad (R-22/R-62).

| Regla | Detalle |
|---|---|
| Solo salidas `Programado` | Una salida terminal no admite inscripciones |
| Con cédula | Solo dígitos, 6-8. Se busca o crea la persona; no se inscribe dos veces en la misma salida |
| Sin cédula (modo libre) | Nombre + fecha de nacimiento (no futura) obligatorios; ID escolar opcional (3-20 alfanuméricos). **Representante (nombre + cédula 6-8) obligatorio.** Anti-duplicado por nombre+fecha de nacimiento o ID escolar en la misma salida |
| Edad | Solo `Ruta::motivoEdadNoValida()`, con el rango **del recorrido**, en los dos flujos y en el formulario. Sin rango, no restringe. **No hay rango literal en el código** (el 5-11 cableado era H-17) |
| Prerequisito de formación (RN-F12) | Si `requiere_formacion`, la persona con cédula debe haber asistido a alguna actividad de formación. Se puede **forzar** («Inscribir sin formación», caso excepcional). Libres exentos |
| Cupo de la salida | `cupo_maximo` de la salida: **advierte, no bloquea** |
| Cupo diario (R-28) | **60 personas por DÍA**, sumando todas las salidas de la fecha (excepto las `No ejecutado`). `rutas_cupo_diario` en Configuración, 0 = sin tope. **Advierte, no bloquea**: es planificación y el propio cliente dijo que «es nuevo» |

## RN-RT07 — Cobro (mig. 083)

El sistema **lleva la cuenta**, no solo constancia (R-40): abonos, varias transferencias del mismo
grupo, pagos de representantes distintos. Cobra **IMATUR**, en una cuenta exclusiva para rutas (R-37).

| Regla | Detalle |
|---|---|
| Condiciones congeladas | Cada salida guarda **su** tarifa USD, tasa y fecha de la tasa. Cambiar el precio del catálogo o que suba el dólar **no mueve** lo ya cobrado (mismo criterio que la nómina, mig. 074). Solo se corrigen mientras la salida esté `Programado` |
| Sugerencia | Gratuita → 0. Institucional en ruta con `exonera_instituciones` → 0 (recordando el oficio). A convenir → sin monto, se pacta. Fija → monto del catálogo |
| Tasa | Se sugiere desde el BCV reutilizando `TasaBcv`; un fallo de red **nunca bloquea**, se carga a mano. Tarifa > 0 exige tasa |
| Pago anticipado (R-38) | La fecha tope no puede ser posterior a la salida. **Vencido** = pasó el tope, no está exonerada y falta por cobrar |
| Esperado | Tarifa × participantes registrados × tasa. Sin tarifa, a convenir o exonerada no hay saldo: manda lo cobrado |
| Formas (R-39) | *Transferencia* (exige referencia; captura/voucher adjunto, servido por `DescargaController::comprobantePago()`) · *Efectivo* (**acta de pago numerada** `ACTP-NNN/AAAA`, solo para efectivo) · *Punto de venta*. Monto > 0, fecha no futura |
| Exoneración (R-42) | La autoriza la **Presidenta**; el sistema no decide: exige motivo y registra quién y cuándo. Una salida exonerada no admite pagos |
| Anular un pago | **No lo borra** (es dinero): queda con motivo y deja de sumar. El número de acta no se recicla |

## RN-RT08 — Ficha Institucional (cierre de la salida, mig. 080)

R-43, R-47 y R-48 pedían «planilla del día» e «informe de cierre»: son **el mismo documento**
(`rutas_ficha_institucional_IMATUR.jpeg`), dirigido a la Directora de Promoción Turística.

- **Nace sola al marcar la salida `Ejecutado`** (R-50), en Borrador, con recorrido, fecha, encargado
  e institución ya puestos. Se completa **en la oficina** al volver (R-46). Una ficha por salida.
- Una salida `No ejecutado` **no tiene ficha**: no hubo grupo.
- Contenido: renglones por **institución** (niñas/niños, rango de edades), **acompañantes** —docentes
  y representantes por sexo— e **instituciones de apoyo** (Protección Civil, R-55/R-56).
- **TOTAL = niños + acompañantes + apoyo**. Verificado contra el ejemplo real: 22 + 9 + 2 = 33.
  `mujeres`/`hombres` (todos los adultos), `ninas`/`ninos` y `total_atendidos` son **derivados**
  (`RutaFicha::recalcular()`): no se escriben a mano.
- Estados *Borrador* / *Cerrada*. No se cierra sin personas atendidas. **Reabrir** devuelve a edición
  para corregir un conteo; no borra nada y queda en la bitácora.
- Las actas de ejecutado/no ejecutado que se archivan en la OAC (R-47) se levantan fuera del sistema.

## RN-RT09 — Oficio saliente al punto

El oficio de visita de IMATUR hacia el espacio a visitar (`/rutas/oficio/{idSalida}`):

1. Exige destinatario; asigna correlativo con `ConfigSistema::generarNumeroOficio('ruta')` — formato
   `007/2026`, **sin prefijo**, atómico y reinicia a `001` cada año.
2. Lo guarda en `oficios_emitidos` ligado a la salida.
3. Renderiza `oficio_imprimible.php` (membrete institucional, standalone). Se emite a mano.

No confundir con el oficio de **solicitud** (entrante, RN-RT04) ni con el de **permiso** (RN-RT10).

## RN-RT10 — Oficios de permiso a instituciones custodias (mig. 081, R-20)

Para entrar a un museo, castillo o fundación, IMATUR envía un oficio de permiso. **Uno cubre todas
las salidas de la semana** hacia esa institución, para agilizar el trámite. Lo tramita el **Director
de Relaciones Inter-Institucionales**, que también corrobora la disponibilidad (es el responsable
sugerido).

- **N:M con las salidas** (`ruta_permiso_salidas`): un permiso cubre varias, y una salida con dos
  custodios necesita dos. Todas las salidas deben caer dentro de la semana indicada.
- Correlativo propio `PERM-NNN/AAAA`, pedido después de validar (un intento fallido no quema número).
- Estados: *En espera* → *Aceptado* | *Rechazado* (el rechazo **exige motivo**; la respuesta no puede
  ser anterior a la emisión y solo se responde una vez). El pase devuelto se adjunta
  (`DescargaController::permisoRuta()`).
- **Anular** conserva el registro con motivo y **no recicla el número**: el oficio ya salió.
- El sistema lista los custodios de las salidas de la semana (desde `puntos_ruta.ente_custodio`).

---

## Parámetros

| Parámetro | Dónde | Valor |
|---|---|---|
| Cupo diario | `configuracion_sistema.rutas_cupo_diario` (`/config`) | 60 (0 = sin tope) |
| Personas por guía (sugerencia) | `RutaEjecucion::PERSONAS_POR_GUIA` | 8 |
| Edad y restricciones | `rutas.edad_min` / `edad_max` / `restricciones` | Por recorrido |
| Tarifa y exoneraciones | `rutas.tarifa_modo` / `tarifa_monto` / `exonera_menores_de` / `exonera_instituciones` | Por recorrido, USD |
| Correlativos | `correlativo_oficio_ruta` · `_permiso` · `_actapago` | Reinician por año, no se reciclan |
| Meta anual | `configuracion_sistema.meta_rutas_anio` (Indicadores) | 100 rutas (R-51) |

## Documentos

| Documento | Formato | Estado |
|---|---|---|
| Ficha Institucional | `docs/formatos/rutas_ficha_institucional_IMATUR.jpeg` | Imprimible fiel al formato |
| Oficio de visita al punto | Referencia del cliente | Imprimible |
| Oficio de permiso a custodios | — | **Provisional** (`permiso_imprimible.php`): falta el formato oficial |
| Acta de pago en efectivo | Propuesta nuestra (R-39: «pueden darnos una idea») | Aprobada por IMATUR el 2026-10-11 |
| Oficio de solicitud | Lo redacta cada institución | Se archiva, no se genera |
| Cuadro de visitantes del custodio | `rutas_cuadro_visitantes_FundacionCastilloSanAntonio.jpeg` | No se genera; sus casillas no coinciden con la Ficha (R-71 abierta) |

## Fuera de alcance (decisiones del cliente)

| Respuesta | Qué no se construye |
|---|---|
| R-46 — el registro se hace al volver a la oficina | Modo campo, app móvil, offline |
| R-49 / R-63 — las fotos son para prensa | Galería o evidencias por salida |
| R-27 — la institución educativa responde por los permisos | Autorización de representante |
| R-58 — el transporte va en el presupuesto | Unidad y conductor |
| R-59 — el testimonio lo toma prensa | Encuesta de satisfacción |
| R-61 — solo la temporada escolar mueve la demanda | Temporadas o tarifas estacionales |
| R-30 — no interesa si alguien repitió | Histórico de rutas por persona |
| R-67 — no lo quieren | Directorio de posadas/aliados |
| Mig. 019 / 021 / 060 / 070 | Inventario asignado a rutas, nivel de dificultad, facilitador externo de texto, actividades por punto (eliminados; no revivirlos) |

**Pendiente del cliente:** formato del oficio de permiso (T-H), R-71 y R-72 — ver `docs/BACKLOG.md` §3.5.
