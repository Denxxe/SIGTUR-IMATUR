# BACKLOG ÚNICO — SIGTUR-IMATUR

**Última actualización:** 2026-08-27 · **Migraciones aplicadas:** hasta **073** · **Rama:** `development_stage`

Documento **único** de seguimiento: qué falta por hacer y decidir. Consolida y reemplaza a
`REGISTRO_NEGOCIO.md`, `DECISIONES_PENDIENTES.md`, `preguntas_modelo_negocio.md`,
`AUDITORIA_SENIOR_2026-05-31.md`, `Notas.md` y `PLAN_ENTREGA.md`.

- **Referencia técnica:** `CLAUDE.md` (arquitectura, BD, convenciones, migraciones).
- **Reglas de negocio por módulo (detalle):** `REGLAS_NEGOCIO_*.md`, `MODELO_NEGOCIO_RRHH.md`, `ESTRUCTURA_ORGANIZATIVA.md`.
- **Indicadores:** `INDICADORES_GESTION.md`.
- **Preguntas para el cliente (imprimible):** `PREGUNTAS_CLIENTE.md` (espejo de la sección 3).

**Leyenda:** 🔴 bloquea BD/lógica · 🟡 alto impacto · 🟢 menor · ✅ hecho · 🔒 espera decisión/insumo del cliente · 🛠️ implementable ya

---

## 1. ESTADO GLOBAL

- **RRHH:** completo salvo **Nómina**. **Bono Vacacional v1 ✅** (registro + reporte, mig.059); Vacaciones (días) ✅; egreso/reingreso ✅; traslados ✅; disciplina ✅; constancias ✅.
- **Nómina:** 🟢 **motor de cálculo construido (2026-08-27, mig. 072).** Fases N‑A/N‑B/N‑C hechas: las primas se derivan, los porcentajes viven en tablas, cesta ticket y tasa del dólar tienen vigencia mensual, hay quincena con snapshot/recálculo/cierre y export de 6 hojas. Las 3 preguntas abiertas **ya no bloquean** (N‑1 y N‑2 son parámetros). Fases **N‑A a N‑D hechas** (mig. 072 y 073): el bono vacacional también calcula sus primas, aunque su **total** sigue confirmándose a mano porque la fórmula no está en ninguna fuente (el sistema muestra su estimación al lado, con la diferencia). Falta solo **N‑E** (Liquidación, bloqueada por N‑3). Lo que realmente falta son **insumos**: sueldos base, grados, cuentas bancarias, cesta ticket y tasa de cada mes. Antes: replanteamiento (2026-08-07). Llegó la plantilla real de nómina quincenal y de sus fórmulas se extrajo **el cálculo completo** — porcentajes por grado académico, escala de antigüedad con tope 30 %, deducciones, aportes y alícuotas. Aparecieron 3 cambios de fondo: son **3 documentos** (se suma la nómina quincenal), **5 tipos de personal** (falta Comisión de Servicio) y las primas **se derivan, no se capturan**. Quedan **3 preguntas**; solo una (N-3) bloquea la Liquidación. Plan por fases en `docs/PLAN_MODULO_NOMINA.md`.
- **Formación / Recepción:** CRUD y reglas operativas completos. Quedan preguntas de impacto medio/bajo.
- **Inventario (Bienes):** 🔄 **En replanteamiento.** El levantamiento del 2026-08-04 (59 preguntas respondidas) reveló que lo construido es un CRUD genérico, mientras que el instituto necesita un **expediente administrativo por bien** con ciclo de vida gobernado por la Alcaldía (codificación, actas, oficios). Plan por fases en `docs/PLAN_MODULO_BIENES.md`.
- **Turismo (Rutas):** cuestionario de descubrimiento **pendiente de responder** (Parte 2 de `PREGUNTAS_DESCUBRIMIENTO_Bienes_Rutas.md`). Prioridad: R-07/R-08 (catálogo vs ejecución) — de esa respuesta depende si hay rediseño.
- **Cuello de botella de la entrega:** ya **no es código**, son **decisiones/insumos del cliente** (sección 3).

---

## 2. LO RESUELTO EN ESTE CICLO

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

> **⚠️ Mantenimiento anual.** Estos feriados no se repiten en la misma fecha: hay que cargar los del
> año siguiente antes de que llegue, desde `/vacaciones/feriados` **sin** marcar «se repite cada año»,
> o extendiendo la mig. 071. Si nadie lo hace, el conteo vuelve a fallar en silencio. Vale la pena
> evaluar un generador por año (la fecha es calculable), pero no se construyó en este ciclo.

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

> **Queda 1 sola pregunta abierta del módulo: B-71** — ¿existe el BM-1 en digital? El cliente lo pedirá junto con los formatos pendientes.

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

## 3. DECISIONES / INSUMOS PENDIENTES DEL CLIENTE 🔒

Bloquean desarrollo. Cada una incluye **qué preguntar**.

### 3.0 🔴 Proveedor SMTP para recuperación de contraseña (2026-07-12)
- **Falta:** credenciales reales de un servidor de correo saliente (host/puerto/usuario/clave) para que la recuperación de contraseña por correo (ya implementada, mig. 058) pueda enviar correos de verdad.
- **Preguntar:** ¿usan Gmail/Google Workspace (contraseña de aplicación), un correo institucional propio (gobernación/alcaldía), u otro proveedor?
- **Al desbloquear:** completar `SMTP_HOST/PORT/USER/PASS/ENCRYPTION` en `config/config.php` (no requiere tocar código ni migraciones).

### 3.1 🟡 Nómina / Liquidación (R-11 · D-RH34/D-RH14) — **avance grande el 2026-08-07**

> **Análisis completo y modelo de cálculo extraído: `docs/PLAN_MODULO_NOMINA.md`. Leerlo antes de tocar el módulo.**

- **Hecho (2026-07-16, mig.059):** Bono Vacacional v1 = **"registro + reporte"**: Talento Humano captura/verifica sueldo, primas y el total final; el sistema organiza y exporta el `.xlsx` multi-hoja en el formato exacto. Incluye `empleado_salarios`, módulo `/nomina`, parámetros en `/config` y `XlsxMultiSheet`.

- **Nuevo (2026-08-07):** el cliente entregó **la plantilla real de nómina quincenal** (`INSTITUTO IMATUR JULIO 2026.xlsx`, con datos de prueba pero **fórmulas reales**) y 4 audios de Talento Humano (transcritos en `docs/formatos/transcripcion_audios_rrhh_2026-07-23.md`). De ahí se extrajo **el cálculo completo**: porcentajes de prima de profesionalización por grado, escala de antigüedad por años (con tope 30 %), transporte, hijos, deducciones, aportes patronales y alícuotas. **Ya no hay que adivinar la fórmula.**

- **Tres cambios de fondo respecto de lo que creíamos** (detalle en el plan §1):
  1. Los documentos de nómina son **3, no 2**: se suma la **nómina quincenal regular** — esto **responde la antigua pregunta 4**.
  2. Los tipos de personal son **5, no 4**: falta **Comisión de Servicio**, con hoja y cálculo propios.
  3. Las primas **no se capturan, se derivan**. `empleado_salarios` guarda los resultados cuando debería guardar las entradas (ver plan §4).

- **Estado de los 3 documentos:**

  | Documento | Estado |
  |---|---|
  | **Bono Vacacional** | ✅ Recibido y montado (v1, captura manual del total) |
  | **Nómina quincenal regular** | ✅ **Recibida 2026-08-07** y descifrada. ⏳ Pendiente de construir |
  | **Liquidación de Prestaciones Sociales** | ✅ Recibida. ⏳ Bloqueada por **1 sola pregunta** (N-3) |

- **Preguntas abiertas — quedan 3** (antes eran 5):

  | # | Pregunta | Bloquea |
  |---|---|---|
  | **N-1** | **Días base del bono vacacional: ¿75 para todos o 75/75/85/45 por tipo?** La plantilla de nómina usa **75 en todas las hojas**, incluidas obreros y contratados; nuestra config tiene 85 y 45. Se contradicen | `bono_vac_dias_*` y la alícuota |
  | **N-2** | **Criterio de las semanas (×4 / ×5)** en SSO/LRPPF/aportes: ¿depende del mes, del tipo de personal, o es un error de la plantilla? | Toda la línea de deducciones |
  | **N-3** | **"Días adicionales"** de la hoja `INTERESES` (79→82 / 120→150 sobre 360). En el audio **no entendió la pregunta** → reformular **con recorte de pantalla** | **Único insumo que falta para la Liquidación** |

  Menores: de dónde sale la **cantidad de divisas** de cada trabajador y si el bono de responsabilidad aplica solo a Alto Nivel y Comisión.

- **Ya resueltas** (no volver a preguntar): ✅ existe formato de nómina regular aparte (era la pregunta 4) · ✅ cesta ticket cambia **mensualmente**, lo publica la **UNAPRE** · ✅ la **"tasa BCV" es el tipo de cambio del dólar** — el bono de responsabilidad se pacta en divisas y se paga al cambio · ✅ la **caja de ahorro no la paga la gobernación** (queda en 0 por regla) · ✅ los % de prima profesional por grado académico.

- **⚠️ 7 defectos detectados en la plantilla del cliente** (plan §5), verificados contra los valores calculados: el tramo ≥23 años paga **el doble** la prima de antigüedad, el FAOV patronal de la hoja de Comisión está al **20 % en vez de 2 %**, la fórmula de antigüedad de esa hoja está **corrupta** (`C621`, `ij6f`), y la fila de Obreros del RESUMEN está **desplazada una columna**. Están en las fórmulas, así que sobreviven a cualquier mes real. **Avisárselo al cliente** — es la mejor justificación del módulo.

- **Insumos operativos que siguen faltando:**
  - [ ] Sueldo base, grado de instrucción, años en la administración pública, nº de hijos y **cuenta bancaria** de cada empleado activo (hoy `empleado_salarios` tiene 1 fila de prueba).
  - [ ] Cesta ticket vigente **con su mes** (julio: 22.907; al 23/07 el cliente dijo 28.388 — cambia mensual).
  - [ ] Tasa del dólar del período.
  - [ ] La **tabla de escala salarial por grado** que Talento Humano ofreció en el último audio (tramo confuso, confirmar).

- **Construcción pendiente** (fases N-A…N-E en el plan §6.3): motor de cálculo → entradas que faltan → nómina quincenal con export de 6 hojas → migrar Bono Vacacional a cálculo → Liquidación.

> **Regla:** ningún número entra al código desde un audio. De 7 afirmaciones numéricas de las notas de voz, **3 resultaron equivocadas** al contrastarlas con la plantilla.

### 3.2 ✅ B13 — Mínimo de antigüedad para constancia — **DECIDIDO (2026-06-25): SIN mínimo**
- **Decisión del cliente:** **no** se exige antigüedad mínima para emitir constancias (se descarta el "mínimo 6 meses"). El mínimo de contrato ya se aclaró en otra sesión.
- **Acción:** ninguna — el sistema ya emite constancias sin exigir antigüedad (`Constancia::crear` no valida tiempo de servicio). B13 cerrado.

### 3.3 ✅ O1 — Cargos por departamento — **DECIDIDO (2026-06-25): cargos GENERALES**
- **Decisión del cliente:** los cargos son **transversales/generales** (no por departamento), tal como ya estaba implementado. El empleado tiene `id_cargo` e `id_departamento` independientes; un mismo catálogo de cargos sirve para todos los departamentos.
- **Acción:** ninguna. Se evaluó vincular cargo↔departamento (mig. tentativa 053) y se **descartó/revirtió** por esta decisión.

### 3.4 ✅ Inventario — **LEVANTAMIENTO COMPLETO (2026-08-04)**

El cliente respondió las **59 preguntas** del cuestionario de descubrimiento
(`docs/PREGUNTAS_DESCUBRIMIENTO_Bienes_Rutas.md`, Parte 1). El análisis y el plan de
reconstrucción por fases están en **`docs/PLAN_MODULO_BIENES.md`**.

**Preguntas históricas, ya resueltas por ese levantamiento:**

| ID | Respuesta del cliente |
|----|----------|
| ✅ D-IN06 | Responsable **nominal y único**: el director del departamento o, en su defecto, el coordinador (B-26/B-27). Al egresar un trabajador el bien **no lo sigue** — queda en el departamento y se reasigna (B-28). |
| ✅ D-IN10 (H-04) | **Mantenimiento**: el bien cambia a estatus "En mantenimiento", deja de estar disponible pero **NO desaparece** (B-34). **Baja**: **sí sale** del inventario activo, conservando el registro y el oficio como aval (B-38). |
| ✅ D-IN09 | **Sí**: costo, fecha de adquisición, proveedor y factura adjunta (B-16/B-17/B-19). También origen Compra/Donación con su oficio (B-18) y garantía con vencimiento (B-20). |
| ⚠️ D-IN11 | **Reinterpretada.** No hay consumibles: no llevan papelería ni material gastable (B-07/B-43/B-44). Lo que piden es un umbral de **suficiencia de mobiliario** (sillas por empleado, mesas por departamento) — distinto de un stock mínimo. Pendiente de definir → **B-63**. |
| ⚠️ D-IN03 | **No existe clasificación hoy** (todo cae en "Inmobiliario"). El cliente pidió una propuesta; hay una en §8 del plan. Pero el código de la Alcaldía es `grupo-subgrupo-sección-…`, o sea que **ya existe un catálogo oficial** que debería ser la fuente → **B-60**. |

**Formulario BM-1 recibido (2026-08-04)** — `docs/formatos/BM-1_inventario_bienes_muebles_alcaldia.jpeg`. **Desbloquea la Fase 1.**

Aclaración clave del cliente: el BM-1 **NO lo produce IMATUR**, es el registro consolidado que la **Alcaldía le devuelve** ya codificado. El circuito es: registro interno → informe de bienes nuevos a la Alcaldía → inspección → BM-1 de vuelta con los códigos → conciliación. El sistema hace las dos primeras piezas y **recibe** la tercera.

| ID | Estado |
|----|----------|
| ✅ B-60 | Catálogo de grupos/subgrupos/secciones: **ya no bloquea**. Los valores los asigna la Alcaldía e IMATUR solo los transcribe; bastan campos validados por formato. |
| ✅ B-61 | Ejemplos reales: `2-01-108` + N° de orden de 3 dígitos con ceros a la izquierda (`084`, `131`, `171`…). |
| ✅ B-62 | "Cantidad" es la cantidad de la fila y **siempre vale 1**; no forma parte del identificador. |
| 🔴 Hallazgo | **El código oficial no clasifica.** Sillas, mesas, pizarra, aire acondicionado y router comparten `2-01-108`. El catálogo de la Alcaldía **no distingue** equipo tecnológico de mobiliario → el sistema necesita **dos ejes**: código oficial (para la Alcaldía) + categoría interna (para los reportes de la Presidencia). |
| 🟡 B-69…B-72 | Nuevas: valores en "S/P" pese a que sí registran costo · cada cuánto llega el BM-1 · si existe versión digital (permitiría carga automática de códigos) · si los saltos en el N° de orden son bajas. |
| 🟡 B-63…B-68 | Umbral de mobiliario · cómo identificar a la Coordinadora de Bienes · sede del aeropuerto · confirmar eliminación de `tipo_bien`/`cantidad` (mig. 044) · destino del bien dado de baja · responsable derivado o manual. Ver §9 del plan. |
| 🔴 Formatos | **Informe de bienes nuevos** que IMATUR envía a la Alcaldía (el más urgente ahora), acta de baja, oficio de asignación, oficio de donación. El formato de inventario de la Alcaldía **ya se recibió**. |

### 3.5 Turismo (Rutas)
| ID | Pregunta |
|----|----------|
| 🟡 D-RT02 | Tarifa Cumaná Histórica: ¿quién cobra y cuál es el flujo de pago? Las columnas `tiene_tarifa`/`tarifa_monto` existen pero nunca se capturaron; desde el 2026-08-27 **tampoco se reportan** (H-14), así que el sistema ya no afirma nada falso. La respuesta define si se construye la captura o se eliminan las columnas. **Única pregunta viva de Turismo.** |
| 🟡 D-RT03 | Al **Finalizar** una ruta, ¿generar informe/oficio automáticamente? |
| ✅ D-RT05 | ~~Instituciones participantes en rutas~~ — **CERRADO 2026-08-04:** eliminado (mig. 060). El indicador CMI queda descartado. |
| ✅ D-RT04 | ~~Facilitador externo: ¿lista o texto libre?~~ — **CERRADO 2026-08-04:** columna eliminada (mig. 060), nunca se usó. |

### 3.6 Formación
| ID | Pregunta |
|----|----------|
| ✅ D-FO06 | ~~¿CRUD de **oficios base** (`oficios`) + vínculo con `talleres.id_oficio`?~~ — **CERRADO 2026-08-04:** tabla y columna eliminadas (mig. 060). Si el cliente pide llevar registro de oficios **recibidos**, se construye desde cero como módulo propio. |
| 🟢 D-FO05 | ¿Parámetros internos de meta para comparar planificado vs ejecutado? |
| 🟢 D-NEW01 | ¿Activar en UI el correlativo de oficios de formación (FORM-XXX)? |

### 3.7 Transversal
| ID | Pregunta |
|----|----------|
| 🟡 D-TX03 | Migración de **históricos** (Excel/papel): definir módulos + obtener archivos fuente. |
| 🟢 D-OF03 | Libro de correspondencia unificado (oficios emitidos/recibidos). |
| ⚪ D-CMI01 | **"Reducción del tiempo de generación de reportes"** (figura en el documento): es una métrica operativa **antes/después** (manual vs. sistema), **no** un indicador que la app pueda calcular de sí misma. Se mide fuera del sistema (justificación de impacto), no se implementa como KPI. |

---

## 4. AUDITORÍA TÉCNICA ABIERTA

| # | Hallazgo | Estado | Cierra con |
|---|----------|--------|-----------|
| H-04 | Baja de bien no actualiza `condicion` | ✅ **Cerrado** (mig. 062): `estatus` (administrativo) quedó separado de `condicion` (físico); un bien dado de baja **sale** del inventario activo y ya no contamina KPIs ni CMI-I01/I03 | — |
| H-09 | Columnas inertes | ✅ **Cerrado** (mig. 060 + H-14): eliminadas `participantes_ruta.id_institucion`, `rutas.nombre_facilitador_externo`, `talleres.id_oficio`. `rutas.tiene_tarifa`/`tarifa_monto` se conservan pero ya **no se leen** (se quitaron del reporte el 2026-08-27), así que dejaron de producir un dato falso | D-RT02 decide si se capturan o se eliminan |
| H-10 | Tablas sin UI | ✅ **Cerrado** (mig. 060): `oficios` e `instituciones_externas` eliminadas (vacaciones ✅, `taller_inventario` ya lo estaba) | — |

> Resueltos previamente: H-01, H-02, H-03 (visitas inmutables), H-05 (validaciones servidor), H-06 (correlativo atómico), H-07 (enums centralizados), H-08 (FKs validadas), H-11 (género M/F).

**Hallazgos nuevos (auditoría 2026-08-07):**

| # | Hallazgo | Estado |
|---|----------|--------|
| H-12 | **El sidebar contradice al RBAC dinámico.** El Router resuelve permisos desde `permisos_rol` (editable en *Roles y Permisos*), pero `views/inc/header.php` los tenía cableados por número de rol (`in_array($rol,[1,2,3,5])`, 8 casos) | ✅ **Cerrado (2026-08-27, sin migración).** El sidebar se genera con `RolesController::getNavegacion()` + `getNavegacionVisible()`, que resuelven la visibilidad con `roleHasModulo()` — el mismo mapa del Router. Fallaba en **los dos sentidos** y ambos quedaron corregidos: el rol 2 tenía `PasantesController`/`UsuariosController` y no veía los enlaces, el rol 6 tenía `VisitantesController` y tampoco; al revés, «Reportes» se mostraba a todos y el rol 5 (que no lo tiene) aterrizaba en *Acceso Denegado*. Lo no delegable (Bitácora, Municipios, Parroquias) queda marcado con `soloAdmin` en la misma definición. Verificado simulando los 6 roles |
| H-13 | **Tabla huérfana `actividades_ruta`**: cero referencias en `app/` desde que el módulo se retiró (2026-05-31) | ✅ **Cerrado (mig. 070).** `DROP TABLE ... CASCADE`. Verificado antes de soltarla: 0 filas, 0 referencias en `app/`, **0 registros en `audit_logs`** — por eso no hizo falta conservar etiqueta en `auditoria/index.php`. Se retiró también su `setval` de `009_fix_sequences.sql`, que habría hecho fallar esa migración en instalaciones ya actualizadas. 56 → 55 tablas |
| H-14 | **`rutas.tiene_tarifa`/`tarifa_monto` nunca se escriben** pero sí se leían: el reporte decía **«Gratuita» para toda ruta, siempre** — dato falso, no solo columna inerte | ✅ **Cerrado (2026-08-27, sin migración).** Se retiró la columna Tarifa del reporte de rutas (vista + export a Excel, con su fila de totales recolumnada). El PDF nunca la traía. **Las columnas se conservan** a la espera de D-RT02: si el cliente confirma que se cobra, se implementa la captura y se reactiva; si descarta el cobro, se eliminan |
| H-15 | **Las evidencias de talleres eran el último archivo de usuario en `public/uploads/`**: legibles por URL sin control de rol, y el enlace `URL_ROOT.'/public/uploads/...'` **se rompía bajo el vhost** `SIGTUR-IMATUR.test` (donde `public/` ya es la raíz), así que solo se veían en una de las dos URLs documentadas. El bloque de subida además estaba **duplicado** en `store()` y `cambiarEstado()`, y ninguna copia validaba MIME real ni tamaño (confiaban en `$_FILES['type']`, que lo manda el cliente) | ✅ **Cerrado (2026-08-27, sin migración).** Van a `storage/uploads/talleres/` servidas por `DescargaController::taller()` (roles 1,3); subida unificada en `TalleresController::procesarEvidencias()` con extensión + MIME real + ≤5 MB, igual que expedientes/bienes. **`public/uploads/` se eliminó por completo** (quedaban dos carpetas vacías de la migración de junio) y se limpió su bloque del `.gitignore` |

---

## 5. PROGRAMACIÓN FALTANTE / BACKLOG TÉCNICO 🛠️

### 5.1 Reportes/funciones pendientes (implementables, queda lo no hecho del Bloque B)

| Módulo | Tarea | Origen |
|--------|-------|--------|
| RRHH | Réplica imprimible del **formato físico de asistencia** — 🔒 necesita el **formato real** (planilla oficial) del cliente para ser fiel | MOD-RRHH 6.2 |
| Formación | Tabla `taller_facilitadores` (múltiples facilitadores) — solo si el cliente lo pide | D-FO08-bis |
| Transversal | Importación de datos históricos desde Excel (depende de D-TX03) | D-TX03 |

### 5.2 Mejoras propuestas (futuro cercano / más adelante) ✨

Propuestas del equipo técnico, no solicitadas aún por el cliente. Priorización sugerida:

| Prioridad | Mejora | Notas de implementación |
|-----------|--------|-------------------------|
| 🟢 **a11y en formularios restantes** | Hecho login + botones ícono del header. Falta vincular `label[for]` en los formularios de los demás módulos (empleados, inventario, visitantes…). |
| 🟢 **Endurecer `Taller::actualizarPersona`** | Whitelist de columnas dentro del método (defensa, no urgente: hoy las claves son fijas). |
| 🟢 **Dividir `ReportesController`** (~3200 líneas al 2026-07-09) | Separar por área cuando convenga (mantenibilidad). |
| 🟢 **Migrar estilos inline a clases** | ~1900 `style=""` en vistas al 2026-07-09; consolidar en utilidades CSS (gradual). |
| 🟢 **Programar la tarea de respaldo en el servidor** | `cron/respaldo_bd.php` ya funciona; falta crear la tarea (`schtasks`). Operativo. |
| 🟢 **Rango de fechas fino en Indicadores** | Ya hay selector de **año**; rango libre mes-a-mes solo si el cliente lo pide (refactor amplio, bajo valor). |
| 🟢 **Ampliar la suite de pruebas** | Base creada (`tests/run.php`). Sumar casos (p. ej. `Asistencia::calcularMinutosTarde`). |

---

## 6. VERIFICACIÓN MANUAL PENDIENTE (probar en navegador)

- **B1** "botón Guardar de RRHH": no hay defecto estático; hacer un alta de empleado de punta a punta para cerrarlo.
- Export **Excel/PDF** en cualquier listado CRUD.
- Toggle **Durable/Fungible** en el modal de inventario.
- Registrar un **período de vacaciones** y verificar el conteo de días hábiles (excluye finde+feriados).
- Registrar un **traslado** y un **escalado falta→amonestación**.
- **Pendiente 2026-07-12** (probado por API/BD, falta un vistazo visual en navegador): listados/reportes de Empleados, Rutas, Visitantes, Pasantes, Inventario con las columnas/filtros nuevos; campana de notificaciones (abrir dropdown y confirmar que las alertas ya vistas no reaparecen); flujo completo de "¿Olvidaste tu contraseña?" desde el link del login.

---

## 7. REGLAS DE NEGOCIO — ESTADO POR MÓDULO (resumen)

> Detalle funcional en los `REGLAS_NEGOCIO_*.md` / `MODELO_NEGOCIO_RRHH.md`.

- **RRHH:** ✅ organigrama jerárquico, ficha técnica + wizard, expediente/recaudos, horarios/grupos A-B/OAC, asistencia/puntualidad, permisos/reposos, amonestaciones+faltas (con tipo y escalado), constancias multi-tipo, egreso/reingreso, traslados, **vacaciones (días)**, badge elegible a fijo, **Bono Vacacional v1** (datos salariales + `/nomina`). **nómina quincenal calculada (mig. 072)**: motor puro con 45 pruebas, porcentajes en tablas, parámetros mensuales con vigencia, 5 tipos de personal, advertencias por empleado, recálculo y cierre, export de 6 hojas. 🔒 Falta: migrar el **Bono Vacacional** al motor (N‑D) y la **Liquidación de Prestaciones Sociales** (N‑E, bloqueada por N‑3) — ver §3.1 y `docs/PLAN_MODULO_NOMINA.md`.
- **Formación:** ✅ talleres/charlas/inducciones, participantes (adulto/niño, alta sin botón buscar), informe demográfico auto, evidencias, estados con auto-transición, lista de asistencia, reportes. 🔒 Falta: oficios base (D-FO06).
- **Turismo (Rutas):** ✅ rutas por ejecución, puntos+mapa Leaflet offline, participantes, oficios, estado Finalizada, demografía, informe con demografía. 🔒 Falta: tarifa (D-RT02 — ya no se reporta un dato falso, ver H-14), disparar el informe automáticamente al Finalizar (D-RT03), y **responder el cuestionario de descubrimiento** (R-07/R-08 pueden forzar rediseño).
- **Inventario:** ✅ expediente administrativo por bien (fases 1-4, mig. 062-067): estatus vs condición, codificación contra el BM-1, adquisición/garantía, responsable **derivado** del departamento, movimientos con origen/destino y autorización, mantenimiento correctivo y preventivo, documentos y hoja de vida, etiquetas QR, conteo por cambio de gestión, análisis de suficiencia y RBAC del módulo. 🔒 Falta: **3 documentos imprimibles** bloqueados por los formatos del cliente (informe de bienes nuevos, acta de baja, acta de asignación) y **cargar los ~142 bienes reales** — ver `docs/PLAN_MODULO_BIENES.md` §12. *(D-IN06, D-IN09 y D-IN10 quedaron cerradas por el levantamiento del 2026-08-04.)*
- **Recepción (Visitantes):** ✅ visitantes + visitas (bitácora inmutable), reportes. 🛠️ Backlog: visitas activas del día.
- **Sistema:** ✅ RBAC dinámico, usuarios/roles, auditoría humanizada + papelera, configuración institucional, idempotencia (token), export transversal, login endurecido (mig.051) + acepta usuario o correo, respaldos automáticos, búsqueda global, campana de alertas (ahora con "vistas" por usuario, mig.057), **carnetización** (mig.053), **recuperación de contraseña por correo** (mig.058, 🔒 falta SMTP real), egreso desactiva acceso automáticamente.

---

## 8. OBSOLETO / SIN EFECTO
- Módulos **Instituciones externas** y **Actividades de ruta**: retirados (2026-05-31).
- `taller_inventario`, `participantes_taller.es_brigadista`: eliminados (mig.050).
- `nivel_dificultad`, `ruta_inventario`: eliminados (mig.019/021).
