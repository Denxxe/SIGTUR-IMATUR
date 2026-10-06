# Manual de Usuario — SIGTUR-IMATUR

**Sistema Integral de Gestión Turística y Administrativa — IMATUR (Cumaná, Sucre)**
Aplicación web de uso interno. Última actualización: 2026-09-19.

> Guía práctica para el personal. Para detalle técnico ver `CLAUDE.md`; para reglas de negocio, los `REGLAS_NEGOCIO_*.md` / `MODELO_NEGOCIO_RRHH.md`. Para el modelo de datos y los diagramas: `MODELO_DATOS_ER.md`, `ESPECIFICACION_REQUERIMIENTOS.md`, `CASOS_DE_USO.md`, `DIAGRAMA_CLASES.md`, `DIAGRAMA_COMPONENTES.md`, `DIAGRAMAS_SECUENCIA.md`.

---

## 1. Acceso al sistema

1. Abre el navegador en la dirección del sistema (la indica el administrador).
2. Ingresa **usuario** (o tu **correo**) y **contraseña**, y pulsa **Iniciar Sesión**.

**Seguridad del acceso:**
- Tras **5 intentos fallidos** la cuenta se **bloquea 15 minutos** (el aviso indica los intentos restantes).
- La sesión se **cierra sola tras 30 minutos de inactividad**; vuelve a iniciar sesión.
- Las contraseñas deben tener **mínimo 8 caracteres, con al menos una letra y un número**.
- Si olvidaste tu contraseña, en la pantalla de inicio de sesión pulsa **"¿Olvidaste tu contraseña?"** e ingresa tu usuario o correo: si tu cuenta tiene un correo registrado, recibirás un enlace (válido 30 minutos) para definir una nueva. Si tu cuenta **no tiene correo registrado**, pide al **administrador** que te la restablezca desde *Sistema → Usuarios*.

---

## 2. La pantalla principal

- **Barra lateral (izquierda):** menú de módulos, agrupado por área (RRHH, Recepción, Formación, Turismo, Inventario, Análisis, Sistema). Solo muestra lo que tu rol puede usar.
- **Encabezado (arriba):**
  - 🔎 **Búsqueda global:** escribe y pulsa Enter para buscar empleados, bienes, talleres, rutas o visitantes (según tu acceso).
  - 🔔 **Campana de notificaciones:** muestra las alertas pendientes (contratos por vencer, expedientes incompletos, talleres vencidos, bienes en alerta, etc.) con un número rojo si hay asuntos por atender.
  - 🌙 **Tema claro/oscuro.**
  - 👤 **Tu nombre / Mi perfil** y **Salir**.
- **Panel Principal (Dashboard):** indicadores resumidos de tu área.

---

## 3. Roles y qué puede hacer cada uno

| Rol | Acceso principal |
|-----|------------------|
| **Administrador** | Todo el sistema, incluida la configuración, usuarios, roles, auditoría y accesos. |
| **RRHH** | Empleados, cargos, departamentos, horarios, asistencia, permisos/reposos, amonestaciones, vacaciones, **nómina**, visitantes, reportes y configuración. |
| **Turismo** | Talleres, sedes de formación, pasantes, rutas y salidas, visitantes y reportes. |
| **Inventario** | Bienes, categorías, ubicaciones, movimientos y reportes. |
| **Recepción** | Registro de visitas y asistencia. |
| **Solo Lectura** | Consulta de reportes y visitantes, sin modificar nada. |

Estos son los roles de partida; el menú de cada usuario muestra **solo** los módulos de su rol, y lo mismo vale para el panel principal, los reportes, la búsqueda y las alertas.

> Si intentas entrar a algo fuera de tu rol, el sistema te redirige con un aviso. Los cambios de permisos los hace el Administrador en *Sistema → Roles y Permisos*.

**Crear un rol nuevo.** En *Sistema → Roles y Permisos* se crea el rol (nombre y descripción) y se **marcan los módulos** que verá. No hace falta nada más: el rol funciona completo con esas casillas. En el grupo *Inventario* hay una casilla especial, **«Bienes: registrar y modificar»**: sin ella, quien tenga el módulo de Bienes solo puede **consultar** (no ve los botones de registrar, editar, codificar ni eliminar).

---

## 4. Módulos por área

### 4.1 Recursos Humanos (RRHH)

**Empleados.** Alta mediante un **asistente por pasos** (datos personales → formación → institucionales → carga familiar → resumen). Dos campos a tener en cuenta:
- **Ingreso a la administración pública** (paso *Institucionales*): viene con la misma fecha del ingreso a IMATUR. **Cámbiala** si el trabajador trabajó antes en otro ente público (Alcaldía, Gobernación, un ministerio…): de esta fecha salen sus **días de vacaciones** y su **prima de antigüedad**. No puede ser posterior al ingreso a IMATUR. Aplica a todo el personal, no solo a la comisión de servicio.
- En la **carga familiar** se indica también el **género** de cada familiar.

Cada trabajador tiene un **expediente** con:

- **Datos personales, académicos y laborales**, con **foto** (la misma que sale en el carnet).
- **Recaudos del expediente:** carga de documentos escaneados, con aviso de cuántos obligatorios faltan.
- **Carga familiar, cursos y experiencia laboral.**
- **Constancias y documentos generados** (ver más abajo).
- **Traslados de departamento:** cambiar de unidad es una **reasignación con historial** — se registra fecha y motivo, y los traslados anteriores quedan a la vista.
- **Datos salariales** (*Registrar sueldo*): solo se escriben el **sueldo básico** mensual y, si aplica, la **prima de discapacidad**, con su fecha de vigencia; nunca se sobrescribe el anterior, se acumula el historial. Las primas de profesionalización, antigüedad, por hijo y el transporte **las calcula Nómina sola**.
- **Datos de nómina:** cuenta bancaria, si cobra el bono de responsabilidad en divisas, sueldo de la dependencia de origen (comisión de servicio) y corrección del código de grado de instrucción.
- **Historial de egresos / reingresos.**
- Botones **Ficha Técnica** (imprimible) y **Carnet**.

**Asistencia.** Marcaje de entrada/salida.
- La **entrada** se compara con el horario del trabajador y queda marcada como **impuntual** si pasa la tolerancia configurada.
- La **salida** exige **motivo obligatorio** si se marca antes de la hora de salida de su horario (con su propia tolerancia, independiente de la de puntualidad).
- Los marcajes son **bitácora: no se eliminan.** Si hay un error, se documenta; no se borra.

**Permisos y Reposos.** Registrar, aprobar, rechazar o anular; se distingue Permiso de Reposo.

**Amonestaciones y Faltas.** El sistema cuenta las faltas (injustificada / incumplimiento) y permite **escalar una falta a amonestación** con el botón de la bandera: pide **confirmación**, muestra con cuántas amonestaciones quedará el trabajador y cada falta se escala **una sola vez** (después aparece como *Amonestada*). Con **3 amonestaciones activas** aparece la alerta **"causa de despido"** — es **solo una alerta**: el egreso siempre es una decisión y una acción manual. Anular una amonestación o una falta exige **motivo**.

**Vacaciones.** Calcula el saldo: **15 días hábiles + 1 por año de servicio, con tope de 30**, sobre la antigüedad total en la Administración Pública. Los días **excluyen fines de semana y feriados**.
- Pantalla **Feriados**: los fijos se cargan una vez; **Carnaval y Semana Santa se calculan solos** por año con el botón *Generar*. El sistema avisa si a los próximos años les faltan.
- Admite un **ajuste inicial** por empleado, para arrancar con el saldo que ya traía de antes del sistema.
- El **cobro** del período (bono vacacional) se maneja en **Nómina** (§5).

**Egreso y reingreso (desincorporación de personal).** Dar de baja a un trabajador **NO borra su registro**: pasa al **histórico de egresados** con fecha, motivo y observación, conservando expediente, tiempo de servicio y constancias. Su usuario del sistema se **desactiva automáticamente**.
- La **fecha de egreso no puede ser futura**.
- El **reingreso** lo devuelve a la nómina activa y reactiva su usuario, dejando registrado su paso por el histórico.
- Ojo: la **fecha de vencimiento del contrato** es un campo distinto del egreso — es la que alimenta la alerta de contratos por vencer.

**Constancias.** Se generan desde el expediente, en seis tipos: **trabajo, bancaria, horario, funciones, antigüedad y egreso**. Llevan **correlativo**, muestran el **estatus** del trabajador (Activo / Egresado / En permiso) y su tiempo de servicio. No exigen antigüedad mínima.

**Carnet del trabajador (carnetización).** Desde el expediente, botón **Carnet**: abre la versión imprimible del carnet institucional (formato CR80 vertical, una cara) con foto, apellidos y nombres, cédula, unidad de adscripción y los datos institucionales tomados de *Configuración*. Indica si es **FIJO** o **CONTRATADO**.
- Para que salga la foto hay que cargarla antes en el expediente (*Cargar / cambiar foto*).
- Es una página lista para **imprimir directamente** en formato CR80 vertical (54 × 85,6 mm), a una sola cara, reproduciendo el carnet físico vigente de IMATUR.

### 4.2 Recepción (Visitas)

- Registrar **entrada y salida** de visitantes (un mismo registro se cierra al marcar salida).
- En el Dashboard ves **Visitas hoy** y **Activas ahora** (personas dentro sin salida registrada).
- Igual que la asistencia, las visitas son **bitácora y no se eliminan**; se consultan con *Ver detalles*.

### 4.3 Formación

- **Talleres / Charlas / Inducciones** con participantes (adultos con cédula o niños "libres" con representante), **lista de asistencia imprimible**, marcaje individual o masivo, informe demográfico y evidencias. Una actividad *Programada* pasa **sola** a *En Curso* cuando llega su fecha y hora de inicio, siempre que tenga al menos un participante inscrito; **finalizarla o cancelarla sigue siendo manual**.
- El sistema **avisa de participantes repetidos** al inscribir, y hay un reporte de *Posibles duplicados* para depurar.
- **Pasantes:** control de practicantes, tutores y documentos, con **carta de postulación** y **carta de aceptación** imprimibles, **carnet** propio (formato PASANTE) y foto.
- **Sedes de Formación:** lugares donde se dictan las actividades.

### 4.4 Turismo (Rutas)

**Lo primero que hay que entender: el recorrido y la salida son cosas distintas.**

- El **recorrido** (o *ruta del catálogo*) es lo que IMATUR ofrece: Cumaná Histórica, Exploradores de Cumaná, Playa Colorada… Tiene sus paradas, su tarifa y sus restricciones. **No tiene fecha.**
- La **salida** es cada vez que ese recorrido se hace: tiene fecha, grupo, guías, cobro e informe. Dos salidas de Cumaná Histórica son *la misma ruta ejecutada dos veces*.

Por eso el módulo tiene **dos pantallas**: *Rutas* (el catálogo) y *Salidas* (lo que ocurre).

#### El catálogo de recorridos

Cada recorrido se registra una vez y se reutiliza. Lleva:

- **Paradas** con su orden, descripción y ubicación en el **mapa** (funciona sin internet). Cada parada indica su **institución custodia** —el museo, castillo o fundación al que hay que pedirle permiso— y si el punto **pone su propio guía**.
- **Restricciones de edad:** edad mínima y máxima, las dos opcionales. Es una propiedad **del recorrido**: Río Brito admite de 12 años en adelante y Exploradores de 4 a 16. Más un campo de texto para lo que no es edad (movilidad, visión…). Estas condiciones se muestran en la ficha del recorrido **y al inscribir**.
- **Tarifa**, en **dólares**, con tres modos: *Gratuita*, *Fija* (un monto) o *A convenir*. Se indica aparte si exonera a los menores de cierta edad y si exonera a instituciones. **La gratuidad es por ruta**: las instituciones públicas no pagan Cumaná Histórica, pero sí Playa Colorada.
- **Estado:** Activa, Inactiva o En Mantenimiento — es decir, si se ofrece o no.

#### Una salida, paso a paso

1. **Registrarla.** Se elige el recorrido, la fecha y la hora, y se indica el **origen**: *Particular* (la pide un turista) o *Institucional* (la pide una escuela o un ente público). Si es institucional, se anota **quién la pide** y se **adjunta el oficio que envió la institución** — ese oficio lo redacta ella, IMATUR solo lo archiva como respaldo.
2. **Aprobación de la Presidencia.** Toda salida la aprueba la Presidencia, tanto la particular como la institucional. El sistema registra **quién asentó el visto bueno y cuándo**, y no deja aprobar dos veces.
3. **Personal.** Se agregan los trabajadores de IMATUR que van, y **uno se marca como encargado**. El sistema sugiere cuántos guías harían falta según el tamaño del grupo, pero **no bloquea**: depende de quién esté disponible.
4. **Itinerario.** Por defecto la salida sigue el orden del catálogo. Si ese día hay dos grupos y conviene que no se crucen, se **cambia el orden solo para esa salida** — el recorrido y las demás salidas no se tocan. También se puede marcar una parada como **"no se hizo"** con su nota (típicamente porque el custodio negó el permiso). *Restablecer* vuelve al orden del catálogo.
5. **Permisos de acceso.** Ver más abajo.
6. **Cobro.** Ver más abajo.
7. **Participantes.** Se inscriben con cédula o en **modo libre** (niños sin cédula), en cuyo caso se registra a su **representante**. El sistema avisa si la edad no encaja con el recorrido y **advierte** —sin bloquear— si se supera el **cupo del día** (60 personas por defecto, configurable; es un tope **diario**, no por salida). La asistencia se marca individual o masivamente.
8. **Cierre.** La salida se cierra con **Marcar ejecutada** o **No se ejecutó**. En el segundo caso el **motivo es obligatorio** (falta de combustible, clima, el grupo canceló). Los dos estados son **finales: no se vuelve atrás**. Una salida no ejecutada no admite inscripciones ni informe.
9. **Reprogramar.** Solo desde *No ejecutado* y una sola vez. **No edita la salida: crea otra**, que hereda grupo, origen, cupo y oficio, y queda enlazada con la original. **La original se conserva intacta**: es la constancia de lo que no ocurrió.
10. **Incidencias.** Hay un campo para registrar lo que pasó en la salida.

#### Permisos a las instituciones custodias

Para entrar a un museo, castillo o fundación hace falta un **oficio de permiso**.

- La pantalla **Permisos** muestra, por semana, **a qué custodios falta pedirles permiso**, deduciéndolo de las paradas de las salidas programadas. Si una parada no tiene custodio declarado, no aparece — hay que completarlo en el catálogo.
- **Un oficio cubre toda la semana** hacia esa institución y puede amparar varias salidas; y una salida con dos custodios distintos necesita dos oficios.
- Estados: **En espera → Aceptado / Rechazado**. El rechazo exige motivo. Se puede adjuntar el **pase recibido** escaneado.
- **Anular un permiso no lo borra y no recicla su número**: el oficio ya salió de la institución.

> El imprimible del oficio de permiso es **provisional**: se ajustará cuando llegue el formato oficial de IMATUR.

#### Cobro y pagos

- Primero se **fijan las condiciones** de la salida: tarifa en dólares, **tasa de cambio** y **fecha tope de pago** (el pago es anticipado, para poder planificar). La tasa se **sugiere desde el BCV**, pero si no hay internet se carga a mano — nunca bloquea.
- Tarifa y tasa quedan **congeladas en la salida**: si después cambia el precio del recorrido o sube el dólar, **lo ya cobrado no se mueve**.
- Se registran **abonos**: varias transferencias, de pagadores distintos, hasta completar. El sistema lleva el **estado de cuenta** (esperado, abonado, saldo) y señala las salidas **vencidas con saldo**.
- **Transferencia y punto de venta** → se adjunta el comprobante. **Efectivo** → el sistema genera un **acta de pago numerada** imprimible.
- **Anular un pago no lo borra** — es dinero: queda con su motivo y deja de sumar.
- **Exonerar** una salida exige motivo y registra quién lo asentó. Las exoneraciones las autoriza la Presidencia; el sistema no decide por su cuenta.

#### Cierre documental

- **Ficha Institucional.** Al marcar la salida como ejecutada, el sistema **la crea solo**, en borrador y precargada con lo que ya sabe. Turismo la completa al volver a la oficina: un **renglón por institución** con su conteo por sexo y rango de edades, los **docentes** y **representantes** acompañantes, y las **instituciones de apoyo** (Protección Civil, etc.). **Los totales los calcula el sistema** a partir de los renglones — no se escriben a mano. Hay **una sola ficha por salida** y se puede **cerrar** (y reabrir). Se imprime en el formato oficial.
- **Oficio de visita.** Documento imprimible que IMATUR emite hacia el punto a visitar, con correlativo propio. No confundirlo con el oficio **entrante** del punto 1.

### 4.5 Inventario (Bienes)

**Alta y codificación.** Un bien nace **sin código**, en estatus **"En espera de codificación"**.
1. Se registra el bien con sus datos (cada bien es un registro individual: dos sillas iguales son dos registros).
2. Cuando llega el **BM-1** de la Alcaldía, se registra su recepción en *Formularios BM-1 recibidos* (se puede adjuntar el escaneado; es opcional, a veces llega en papel).
3. Desde ahí se **codifica** cada bien — grupo, subgrupo, sección y N° de orden, que componen el código (`2-01-108-084`) — y el bien pasa a **Activo**. Queda registrado **en qué BM-1 vino** cada código.

El listado tiene pestaña **"Sin codificar"** con su contador. El N° de orden no se puede repetir.

**Dos ejes distintos, no los mezcles:**
- **Estatus** (situación administrativa): En espera de codificación · Activo · En mantenimiento · Extraviado · Robado · **Desincorporado**.
- **Condición** (estado físico): Nuevo · Bueno · Regular · Dañado.
- Un bien **en mantenimiento NO desaparece** del inventario; un bien **desincorporado sí sale** del inventario activo, pero **su registro se conserva** (pestaña *Desincorporados*).

**Movimientos.** Asignación de responsable, traslado, salida y retorno de mantenimiento, y baja. El sistema impide lo que no tiene sentido: mover un bien dado de baja, trasladarlo al mismo sitio, sacarlo dos veces a mantenimiento o retornarlo sin mantenimiento abierto. Un bien sin codificar solo admite asignación de responsable.

**Herramientas de control:**
- **Conteos de Inventario:** verificación física (por ejemplo, por cambio de gestión), bien por bien, con **acta imprimible** al cerrar.
- **Mantenimiento Preventivo:** calendario por equipo, con aviso cuando se acerca el vencimiento.
- **Etiquetas de Bienes:** hoja imprimible con el código oficial y un **QR** que abre la hoja de vida del bien. Solo lista bienes ya codificados. Funciona sin internet.
- **Suficiencia de Bienes:** compara la dotación esperada por departamento contra lo que realmente hay.
- Cada bien tiene **hoja de vida**: documentos, foto e historial completo de movimientos.

**Documentos que se emiten desde el módulo:**
- **Oficio de relación de bienes nuevos:** se arma con los bienes que aún no tienen código, se numera y se envía a la Alcaldía. Anularlo exige motivo y **no recicla el número**.
- **Documento de donación:** cuando un bien entra por donación se capturan los datos del donante (cédula, estado civil, domicilio), la procedencia y el valor, y se emite el documento imprimible.
- **Acta de Desincorporación (por lote):** se arma con los bienes ya desincorporados, se imprime y se lleva a la Alcaldía (*emitida*). Cuando vuelve sellada se registra con su escaneado (*firmada*) y **todos sus bienes pasan a "Retirado" de una vez**. Si se anula un acta firmada, el **retiro se revierte**: el aval que lo respaldaba dejó de existir.
  > El imprimible del acta es **provisional** hasta que llegue el formato oficial. La información registrada no cambia.

> **Solo consulta:** si tu rol tiene el módulo de Bienes pero no el permiso *Bienes: registrar y modificar*, verás el distintivo **«Solo consulta»** y no aparecerán los botones de registrar, editar, codificar, mover ni eliminar.

> **Cambio en curso (2026-09):** la Alcaldía notificó que **IMATUR pasará a codificar sus propios bienes**, continuando la secuencia desde su última revisión. Mientras eso no se active en el sistema, el circuito sigue siendo el descrito arriba.

### 4.6 Sistema (Administrador)

- **Configuración institucional:** director y cargo firmante, RIF, resolución y gaceta, teléfono, correo, dirección y lema (salen en constancias, carnets y membretes), **metas anuales** de talleres y rutas, **días de preaviso** de contratos y pasantías, **tolerancias** de puntualidad y de salida anticipada, correlativos de oficios y el cupo diario de rutas. *(Los parámetros de Nómina —cesta ticket, tasa del dólar, porcentajes, días del bono vacacional— están en **Nómina → Parámetros**, §5.1.)*
- **Usuarios**, **Roles y Permisos** (ver §3), **Municipios y Parroquias**. Un rol distinto del Administrador que tenga *Usuarios* (por ejemplo RRHH) puede crear cuentas para el personal, pero no puede asignar el rol Administrador, tocar una cuenta de Administrador ni cambiar su propio rol.
- **Auditoría** (bitácora de cambios: quién, qué y cuándo), **Accesos** (inicios de sesión) y **Papelera de Reciclaje**.

---

## 5. Nómina

Módulo del rol **RRHH** (y Administrador). Tiene tres pantallas.

### 5.1 Parámetros del mes

Antes de generar cualquier nómina hay que cargar el mes:
- **Cesta ticket** del mes (la publica la UNAPRE) — entra en el sueldo diario y en las alícuotas.
- **Tasa del dólar** del mes — con ella se paga el bono de responsabilidad, pactado en divisas.

Ambas **cambian todos los meses**. Si el mes no está cargado, el sistema no deja generar el período.

### 5.2 Nómina quincenal

1. **Generar la quincena:** se elige el mes y la quincena (1 o 2). El sistema toma una **foto del momento** de todo el personal activo y calcula sueldo base, primas de profesionalización y antigüedad, bono de transporte, prima por hijos, deducciones (SSO, FAOV, LRPPF), aportes patronales, alícuotas y neto a cobrar.
2. **Revisar las advertencias.** El sistema señala las filas con datos incompletos o dudosos (por ejemplo, un sueldo que falta o un grado mal registrado). **Revísalas antes de cerrar.**
3. **Corregir y recalcular.** Se arregla el dato en la ficha del trabajador y se pulsa **Recalcular**: la quincena se rehace con los datos actuales, conservando su número.
4. **Exportar a Excel:** una hoja **por cada tipo de personal** (Alto Nivel, Empleados Fijos, Obreros Fijos, Contratados, Comisión de Servicio) más una hoja **RESUMEN**. La hoja de Comisión de Servicio agrega lo que paga la dependencia de origen y la diferencia.
5. **Cerrar la quincena.** Una vez cerrada **ya no se puede recalcular ni editar**. Ciérrala solo cuando el resultado esté conforme.

### 5.3 Bono Vacacional

Mismo flujo (generar → revisar → exportar → cerrar), con dos particularidades:
- Los **días que corresponden** salen del **tipo de personal**, según lo configurado en *Configuración* (contrato colectivo).
- El **total** de cada trabajador se puede **capturar o corregir a mano**: se escribe en la columna *Total confirmado* y se pulsa **Guardar** en esa fila (o Enter). El botón **"Aceptar calculados"** da por buenos de una vez todos los totales que el sistema estimó y que aún nadie confirmó. Al **recalcular**, los totales ya confirmados **se conservan**.

> **Pendiente:** la **Liquidación de Prestaciones Sociales** aún no está en el sistema; queda para una entrega posterior.

---

## 6. Reportes e indicadores

Entra a **Análisis → Reportes**. Verás solo las tarjetas de tu rol. Cada reporte permite **filtrar** y **exportar a Excel/PDF**.

- **Alertas:** Centro de Alertas (pendientes por atender).
- **RRHH:** directorio de personal, asistencia, permisos y reposos, amonestaciones y faltas, **egresos y rotación**, comisión de servicio, constancias emitidas, expedientes incompletos, carga familiar y **saldo de vacaciones**.
- **Recepción:** reporte de visitantes y estadísticas de visitas.
- **Formación/Turismo:** talleres, **informe trimestral**, cobertura comunitaria, rutas, participación en rutas, **ejecuciones de ruta**, pasantes y posibles duplicados.
- **Inventario:** inventario, kardex de movimientos, bienes asignados, **bienes desincorporados** (con la columna *Por retirar / Retirado*), **conteos**, **mantenimiento preventivo**, **etiquetas** y **suficiencia de bienes**.
- **Seguridad (Admin):** auditoría del sistema y **accesos** (quién entró, cuándo y desde qué IP).
- **Indicadores de Gestión:** panel con KPIs por área; arriba puedes elegir el **año** a consultar.

> En cualquier reporte o listado, los botones **Excel** y **PDF** exportan lo que estás viendo (respetando los filtros), con el membrete institucional.

---

## 7. Notificaciones (campana)

La 🔔 del encabezado reúne los pendientes de tu área. Haz clic para ver la lista y entra a cada uno para atenderlo. Los roles RRHH/Administrador tienen además el enlace al **Centro de Alertas** completo.

Una alerta que ya revisaste **deja de aparecer**; vuelve a mostrarse solo si cambian los casos que la originan.

---

## 8. Búsqueda global

Escribe en el 🔎 del encabezado (mínimo 2 letras) y pulsa Enter. Muestra coincidencias agrupadas (empleados, inventario, talleres, rutas, visitantes) **según tu acceso**. Haz clic en un resultado para abrirlo.

---

## 9. Mi perfil

Entra desde tu nombre (arriba a la derecha) → **Mi Perfil**:
- Cambiar **nombre de usuario**.
- Cambiar **contraseña** (pide la actual; la nueva debe cumplir la política de seguridad).

---

## 10. Preguntas frecuentes

- **No veo un módulo en el menú.** No está habilitado para tu rol; solicítalo al Administrador.
- **Mi cuenta se bloqueó.** Espera 15 minutos o pide al Administrador que la restablezca.
- **La sesión se cerró sola.** Es por inactividad (30 min); vuelve a iniciar sesión.
- **Guardé dos veces por error.** El sistema evita registros duplicados por doble envío; si dudas, revisa el listado antes de repetir.
- **¿Se pierde algo al eliminar?** No: casi todo es borrado lógico y se puede recuperar desde *Sistema → Papelera* (según permisos).
- **Borré un bien y no aparece en el reporte de bienes desincorporados.** Son cosas distintas: **desincorporar** un bien es un movimiento (sale del inventario activo y queda en la pestaña *Desincorporados*); **eliminar** lo manda a la Papelera, que es para registros creados por error.
- **No puedo marcar una salida de ruta como ejecutada.** Revisa la fecha: no se puede dar por ejecutada una salida que todavía no ha ocurrido. Y si ya está en *Ejecutado* o *No ejecutado*, esos estados son finales.
- **Me equivoqué al cerrar una salida.** Los estados de cierre no se revierten a propósito: son constancia. Si la salida no se hizo y se repuso otro día, el camino correcto es **Reprogramar**, que crea una salida nueva enlazada.
- **El cobro de una salida quedó con la tarifa vieja.** Es intencional: la tarifa y la tasa se **congelan** en cada salida para que cambiar el precio del recorrido o la tasa del día no altere lo ya cobrado.
- **Registré mal un pago.** No se borra: se **anula con motivo** y deja de sumar al total abonado.
- **Cerré una quincena y estaba mala.** Una quincena cerrada no se puede editar: hay que generar una nueva. Por eso conviene revisar las advertencias y recalcular **antes** de cerrar.
- **El carnet sale sin foto.** Carga la foto en el expediente del trabajador (*Cargar / cambiar foto*) y vuelve a abrir el carnet.
- **No me deja generar la nómina del mes.** Faltan los **parámetros del mes** (cesta ticket y tasa del dólar) en *Nómina → Parámetros*.
- **Un trabajador egresado desapareció de las listas.** No se borró: está en el histórico de egresados y sigue disponible para constancias y tiempo de servicio. Si vuelve, se registra un **reingreso**.
