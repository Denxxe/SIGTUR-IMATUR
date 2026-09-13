# Manual de Usuario — SIGTUR-IMATUR

**Sistema Integral de Gestión Turística y Administrativa — IMATUR (Cumaná, Sucre)**
Aplicación web de uso interno. Última actualización: 2026-09-13.

> Guía práctica para el personal. Para detalle técnico ver `CLAUDE.md`; para reglas de negocio, los `REGLAS_NEGOCIO_*.md` / `MODELO_NEGOCIO_RRHH.md`.

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
| **Turismo** | Talleres, sedes de formación, pasantes, rutas, visitantes y reportes. |
| **Inventario** | Bienes, categorías, ubicaciones, movimientos y reportes. |
| **Recepción** | Registro de visitas y asistencia. |

> Si intentas entrar a algo fuera de tu rol, el sistema te redirige con un aviso. Los cambios de permisos los hace el Administrador en *Sistema → Roles y Permisos*.

---

## 4. Módulos por área

### 4.1 Recursos Humanos (RRHH)

**Empleados.** Alta mediante un **asistente por pasos** (datos personales → formación → institucionales → carga familiar → resumen). Cada trabajador tiene un **expediente** con:

- **Datos personales, académicos y laborales**, con **foto** (la misma que sale en el carnet).
- **Recaudos del expediente:** carga de documentos escaneados, con aviso de cuántos obligatorios faltan.
- **Carga familiar, cursos y experiencia laboral.**
- **Constancias y documentos generados** (ver más abajo).
- **Traslados de departamento:** cambiar de unidad es una **reasignación con historial** — se registra fecha y motivo, y los traslados anteriores quedan a la vista.
- **Datos salariales:** cada sueldo se registra con su **fecha efectiva**; nunca se sobrescribe el anterior, se acumula el historial. Es el insumo del cálculo de nómina.
- **Datos de nómina:** cuenta bancaria, si cobra el bono de responsabilidad en divisas, sueldo de la dependencia de origen (comisión de servicio) y corrección del código de grado de instrucción.
- **Historial de egresos / reingresos.**
- Botones **Ficha Técnica** (imprimible) y **Carnet**.

**Asistencia.** Marcaje de entrada/salida.
- La **entrada** se compara con el horario del trabajador y queda marcada como **impuntual** si pasa la tolerancia configurada.
- La **salida** exige **motivo obligatorio** si se marca antes de la hora de salida de su horario (con su propia tolerancia, independiente de la de puntualidad).
- Los marcajes son **bitácora: no se eliminan.** Si hay un error, se documenta; no se borra.

**Permisos y Reposos.** Registrar, aprobar, rechazar o anular; se distingue Permiso de Reposo.

**Amonestaciones y Faltas.** El sistema cuenta las faltas (injustificada / incumplimiento) y permite **escalar una falta a amonestación**. Con **3 amonestaciones activas** aparece la alerta **"causa de despido"** — es **solo una alerta**: el egreso siempre es una decisión y una acción manual. Anular una amonestación o una falta exige **motivo**.

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

- **Talleres / Charlas / Inducciones** con participantes (adultos con cédula o niños "libres" con representante), **lista de asistencia imprimible**, marcaje individual o masivo, informe demográfico, evidencias y estados que avanzan solos según la fecha.
- El sistema **avisa de participantes repetidos** al inscribir, y hay un reporte de *Posibles duplicados* para depurar.
- **Pasantes:** control de practicantes, tutores y documentos, con **carta de postulación** y **carta de aceptación** imprimibles, **carnet** propio (formato PASANTE) y foto.
- **Sedes de Formación:** lugares donde se dictan las actividades.

### 4.4 Turismo (Rutas)

- **Rutas** con puntos en el mapa, participantes, cupo y estados; al **Finalizar** una ruta cuenta como ejecución.
- **Oficio de la ruta:** documento imprimible con los puntos y el total de participantes.

> **Nota:** el módulo de Rutas está en **rediseño**. El levantamiento con IMATUR (2026-09) confirmó que existe un **catálogo de rutas reutilizable** distinto de cada **salida ejecutada**, y que las rutas **sí se cobran**. Lo que este manual describe es lo que el sistema hace **hoy**; ver `PLAN_MODULO_RUTAS.md`.

### 4.5 Inventario (Bienes)

**Alta y codificación.** Un bien nace **sin código**, en estatus **"En espera de codificación"**.
1. Se registra el bien con sus datos (Durable = inventariable; Fungible = consumible con cantidad).
2. Cuando llega el **BM-1** de la Alcaldía, se registra su recepción en *Formularios BM-1 recibidos* (se puede adjuntar el escaneado; es opcional, a veces llega en papel).
3. Desde ahí se **codifica** cada bien — grupo, subgrupo, sección y N° de orden, que componen el código (`2-01-108-084`) — y el bien pasa a **Activo**. Queda registrado **en qué BM-1 vino** cada código.

El listado tiene pestaña **"Sin codificar"** con su contador. El N° de orden no se puede repetir.

**Dos ejes distintos, no los mezcles:**
- **Estatus** (situación administrativa): En espera de codificación · Activo · En mantenimiento · Extraviado · Robado · Dado de baja.
- **Condición** (estado físico): Nuevo · Bueno · Regular · Dañado.
- Un bien **en mantenimiento NO desaparece** del inventario; un bien **dado de baja sí sale** del inventario activo, pero **su registro se conserva** (pestaña *Desincorporados*).

**Movimientos.** Asignación de responsable, traslado, salida y retorno de mantenimiento, y baja. El sistema impide lo que no tiene sentido: mover un bien dado de baja, trasladarlo al mismo sitio, sacarlo dos veces a mantenimiento o retornarlo sin mantenimiento abierto. Un bien sin codificar solo admite asignación de responsable.

**Herramientas de control:**
- **Conteos de Inventario:** verificación física (por ejemplo, por cambio de gestión), bien por bien, con **acta imprimible** al cerrar.
- **Mantenimiento Preventivo:** calendario por equipo, con aviso cuando se acerca el vencimiento.
- **Etiquetas de Bienes:** hoja imprimible con el código oficial y un **QR** que abre la hoja de vida del bien. Solo lista bienes ya codificados. Funciona sin internet.
- **Suficiencia de Bienes:** compara la dotación esperada por departamento contra lo que realmente hay.
- Cada bien tiene **hoja de vida**: documentos, foto e historial completo de movimientos.

> **Cambio en curso (2026-09):** la Alcaldía notificó que **IMATUR pasará a codificar sus propios bienes**, continuando la secuencia desde su última revisión. Mientras eso no se active en el sistema, el circuito sigue siendo el descrito arriba.

### 4.6 Sistema (Administrador)

- **Configuración institucional:** director y cargo firmante, RIF, resolución y gaceta, teléfono, correo, dirección y lema (salen en constancias, carnets y membretes), **metas anuales** de talleres y rutas, **días de preaviso** de contratos y pasantías, **tolerancias** de puntualidad y de salida anticipada, correlativos de oficios, **días de bono vacacional por tipo de personal** y **monto de la cesta ticket**.
- **Usuarios**, **Roles y Permisos**, **Municipios y Parroquias**.
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
- El **total** de cada trabajador se puede **capturar o corregir a mano**, y el botón **"Aceptar calculados"** da por buenos de una vez todos los totales que el sistema estimó y que aún nadie confirmó. Al **recalcular**, los totales ya confirmados **se conservan**.

> **Pendiente:** la **Liquidación de Prestaciones Sociales** aún no está en el sistema; queda para una entrega posterior.

---

## 6. Reportes e indicadores

Entra a **Análisis → Reportes**. Verás solo las tarjetas de tu rol. Cada reporte permite **filtrar** y **exportar a Excel/PDF**.

- **Alertas:** Centro de Alertas (pendientes por atender).
- **RRHH:** directorio de personal, asistencia, permisos y reposos, amonestaciones y faltas, **egresos y rotación**, comisión de servicio, constancias emitidas, expedientes incompletos, carga familiar, **saldo de vacaciones**, visitantes y estadísticas de visitas.
- **Formación/Turismo:** talleres, **informe trimestral**, cobertura comunitaria, rutas, participación en rutas, **ejecuciones de ruta**, pasantes y posibles duplicados.
- **Inventario:** inventario, kardex de movimientos, bienes asignados, bienes dados de baja, **conteos**, **mantenimiento preventivo**, **etiquetas** y **suficiencia de bienes**.
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
- **Borré un bien y no aparece en "Bienes Dados de Baja".** Son cosas distintas: **dar de baja** un bien es un movimiento (sale del inventario activo y queda en la pestaña *Desincorporados*); **eliminar** lo manda a la Papelera, que es para registros creados por error.
- **Cerré una quincena y estaba mala.** Una quincena cerrada no se puede editar: hay que generar una nueva. Por eso conviene revisar las advertencias y recalcular **antes** de cerrar.
- **El carnet sale sin foto.** Carga la foto en el expediente del trabajador (*Cargar / cambiar foto*) y vuelve a abrir el carnet.
- **No me deja generar la nómina del mes.** Faltan los **parámetros del mes** (cesta ticket y tasa del dólar) en *Nómina → Parámetros*.
- **Un trabajador egresado desapareció de las listas.** No se borró: está en el histórico de egresados y sigue disponible para constancias y tiempo de servicio. Si vuelve, se registra un **reingreso**.
