# DECISIONES PENDIENTES — SIGTUR-IMATUR

**Última actualización:** 2026-05-22  
**Propósito:** Documento de trabajo — Q&A de modelo de negocio con respuestas e impacto técnico. Las preguntas **sin responder** están también en `preguntas_modelo_negocio.md` como resumen ejecutivo.

**Instrucción:** Escribir la respuesta en la línea `RESPUESTA:` de cada pregunta. Las marcadas 🔴 bloquean decisiones de BD o lógica central. Las 🟡 desbloquean funcionalidades de alto impacto. Las 🟢 son mejoras menores.

**Leyenda de estado:**
- ❓ Sin responder — pendiente
- ✅ Respondida — implementada o documentada
- ⚠️ Respondida parcialmente — implementación pendiente

---

## MÓDULO 1 — INVENTARIO

### 🔴 Decisiones críticas

**D-IN01** ✅ ¿El proceso de "Dar de Baja" un bien requiere generar un acto administrativo imprimible desde el sistema (como el oficio de rutas), o solo el registro interno es suficiente?
> **Desbloquea:** Diseño del flujo de baja formal + documento imprimible en `inventario/baja_acto.php`
> **RESPUESTA:** Con el registro interno es suficiente.
> **Impacto técnico implementado:** No se genera documento imprimible de baja. El flujo actual de `InventarioController::baja()` con registro en `actividad_inventario` tipo 'Baja' es suficiente. Sin cambios de código necesarios.

**D-IN02** ✅ ¿Los bienes dados de baja deben entregarse a la Contraloría Municipal con un listado oficial? ¿El sistema debe generarlo?
> **Desbloquea:** Si aplica, reporte específico de bienes dados de baja para Contraloría
> **RESPUESTA:** No es necesario generar un listado oficial, pero sería bueno tener la opción de generar uno.
> **Impacto técnico implementado:** Reporte de bajas implementado en `ReportesController` — exportable a CSV. Filtra `actividad_inventario` donde `tipo_movimiento = 'Baja'`, incluyendo código BN, descripción, categoría, fecha y motivo.

### 🟡 Funcionalidades importantes

**D-IN03** ❓ ¿Qué tipos de bienes maneja IMATUR? ¿Las categorías actuales del sistema (Electrónica, Mobiliario, etc.) reflejan la clasificación real, o hay una clasificación oficial diferente?
> **Desbloquea:** Ajuste de categorías en BD + datos de prueba correctos
> **RESPUESTA:** Las categorias aun no estan definidas del todo, pero se basan en la clasificación oficial de la institución.

**D-IN04** ✅ ¿Los estados/condiciones actuales son suficientes? El sistema tiene: `Nuevo`, `Bueno`, `Regular`, `Dañado`. ¿Falta alguno como "En Reparación" o "En Garantía"?
> **Desbloquea:** Migración de CHECK constraint en `inventario.condicion`
> **RESPUESTA:** Se agregan el estado en reparacion.
> **Impacto técnico implementado:** `'En Reparación'` añadido al CHECK constraint de `inventario.condicion` (migración 007). La whitelist en `InventarioController` actualizada a: `['Nuevo','Bueno','Regular','Dañado','En Reparación']`. Selector en UI incluye la nueva opción.

**D-IN05** ❓ ¿Existe diferenciación entre bienes fungibles (papel, tóner) y bienes durables (equipos, mobiliario)? ¿Se manejan diferente?
> **Desbloquea:** Campo `tipo_bien` en tabla `inventario` + lógica diferenciada
> **RESPUESTA:** Si deberia de haber la distinción, pero el sistema no lo maneja por el momento. Si hay que profundizar en esto, se puede hacer.

**D-IN06** ❓ ¿Existe un "responsable del bien" asignado nominalmente? ¿Un bien puede estar asignado a más de un empleado simultáneamente?
> **Desbloquea:** FK `id_responsable` en `inventario` o tabla de asignación múltiple
> **RESPUESTA:**

### 🟢 Mejoras menores

**D-IN07** ✅ ¿Se realizan inventarios físicos periódicos (anuales/semestrales)? ¿El sistema debe apoyar ese proceso con una lista de conteo/verificación?
> **Desbloquea:** Módulo de "Conteo físico" con listado exportable para marcar presencia
> **RESPUESTA:** Se realizan inventarios físicos periódicos, pero el sistema no los apoya. Deberia hacerlo con un módulo de "Conteo físico" con listado exportable para marcar presencia.
> **Impacto técnico implementado:** Reporte de conteo físico implementado — lista de bienes activos exportable a CSV, con columnas: código BN, descripción, categoría, ubicación, condición, serial. Permite imprimir y marcar físicamente.

**D-IN08** ✅ ¿Hay bienes sin código BN asignado todavía? ¿Cómo se manejan en el sistema mientras esperan el código?
> **Desbloquea:** Si el campo `codigo_bn` puede ser nullable/temporal
> **RESPUESTA:** Por lo general no hay bienes sin código BN asignado, pero si hay, se manejan en el sistema mientras esperan el código.
> **Impacto técnico implementado:** `inventario.codigo_bn` ahora es nullable (migración 007). La validación en `InventarioController` ya no exige el campo como obligatorio. En vistas se muestra "—" cuando el campo es NULL.

---

## MÓDULO 2 — RECURSOS HUMANOS

### 🔴 Decisiones críticas

**D-RH01** ❓ ¿Los horarios de trabajo están definidos formalmente? ¿Cuántos turnos existen? ¿Cómo se llaman (Matutino, Vespertino, otro)?
> **Desbloquea:** Datos semilla de `horarios` + UI de HorariosController (tablas ya existen)
> **RESPUESTA:**

**D-RH02** ❓ ¿Qué tipos de permisos laborales existen? (médico, personal, duelo, maternidad/paternidad, sindical, otro)
> **Desbloquea:** Enum/lista en `permisos_laborales.tipo_permiso` + PermisosLaboralesController
> **RESPUESTA:**

**D-RH03** ❓ ¿Los permisos laborales requieren aprobación de un superior, o RRHH los registra directamente sin flujo de aprobación?
> **Desbloquea:** Si `permisos_laborales.estado` tiene un flujo (Solicitado→Aprobado) o es directo
> **RESPUESTA:**

**D-RH04** ❓ ¿Las vacaciones se calculan por días hábiles o días calendario? ¿Cuántos días corresponden por año de servicio?
> **Desbloquea:** Lógica de cálculo en `VacacionesController` + fórmula de saldo disponible
> **RESPUESTA:**

**D-RH05** ❓ ¿El saldo de vacaciones se calcula automáticamente año a año, o RRHH lo registra manualmente y el sistema solo registra los días tomados?
> **Desbloquea:** Si la tabla `vacaciones` necesita auto-cálculo o es solo registro manual
> **RESPUESTA:**

### 🟡 Funcionalidades importantes

**D-RH06** ❓ ¿Los días de vacaciones no disfrutados se acumulan al año siguiente o se pierden?
> **Desbloquea:** Lógica de arrastre de saldo en cierre de año
> **RESPUESTA:**

**D-RH07** ❓ ¿Los empleados contratados tienen fecha de vencimiento de contrato que el sistema deba alertar cuando esté próxima a vencer?
> **Desbloquea:** Alerta en Dashboard para contratos que vencen en los próximos 30 días
> **RESPUESTA:**

**D-RH08** ❓ ¿Los horarios son fijos para todos (8am-4pm) o hay turnos diferentes por departamento?
> **Desbloquea:** Si cada empleado tiene su propio turno o hay un turno global
> **RESPUESTA:**

**D-RH09** ❓ ¿El sistema debe calcular reportes de puntualidad o ausentismo? ¿Se usa para nómina?
> **Desbloquea:** Columnas calculadas en el reporte de asistencia (tardanzas, % asistencia)
> **RESPUESTA:**

### 🟢 Mejoras menores

**D-RH10** ❓ ¿Hay personal que preste servicios sin ser empleado formal (voluntarios, servicio comunitario)? ¿Deben estar en el sistema?
> **Desbloquea:** Tipo de contrato adicional o tabla separada
> **RESPUESTA:**

**D-RH11** ❓ ¿Los cargos tienen el mismo sueldo base para todos los empleados en ese cargo, o puede variar por empleado?
> **Desbloquea:** Si `sueldo_base` debe moverse de `cargos` a `empleados`
> **RESPUESTA:**

---

## MÓDULO 3 — FORMACIÓN (TALLERES / CHARLAS / INDUCCIONES)

### 🔴 Decisiones críticas

**D-FO01** ✅ ¿El informe demográfico (`taller_informes`) debe ser obligatorio antes de poder cambiar el estado del taller a "Finalizado"?
> **Estado actual:** Implementado en Fase 1. Si la respuesta es NO, se puede revertir.
> **RESPUESTA:** Al finalizar el taller, se debe generar el informe demográfico.
> **Impacto técnico implementado:** Confirmado correcto. `TalleresController::validarTransicion()` (RN-F13) bloquea la transición a Finalizado si `taller_informes` no tiene registro para ese taller. Sin cambios necesarios.

**D-FO02** ✅ ¿Un oficio de entrada (de Zona Educativa hacia IMATUR) puede originar múltiples talleres, o siempre es uno a uno?
> **Desbloquea:** Relación en BD entre `oficios` y `talleres` (1:1 vs 1:N)
> **RESPUESTA:** Por el momento, un oficio genera una actividad. 1:1
> **Impacto técnico implementado:** Diseño actual correcto. La FK `talleres.id_oficio → oficios.id` permanece como está. Sin migración necesaria.

**D-FO03** ✅ ¿Los talleres externos que IMATUR organiza para escuelas/liceos generan también un **oficio emitido** desde IMATUR hacia las instituciones? ¿Debe generarse desde el sistema?
> **Desbloquea:** Módulo de oficio de convocatoria para talleres (similar al oficio de rutas)
> **RESPUESTA:** No se supone que ya las instituciones ya solicitaron el oficio mediante zona educativa.
> **Impacto técnico implementado:** No se implementa generación de oficio emitido para talleres externos. El módulo de oficios de rutas permanece como caso único. Sin cambios.

### 🟡 Funcionalidades importantes

**D-FO04** ✅ ¿El formato de asistencia que emite Talento Humano para actividades internas tiene campos específicos distintos al del sistema actual? ¿Debe generarse imprimible?
> **Desbloquea:** Vista imprimible de lista de asistencia para actividades internas
> **RESPUESTA:** el formato de asistencia es el formato mostrado en la imagen adjunta analizada.
> **Impacto técnico implementado:** Lista de asistencia imprimible implementada en `app/views/talleres/lista_asistencia.php`. Formato institucional con membrete, datos del taller, tabla de participantes con columnas Nombre, C.I., Institución/Cargo, Firma. Accesible desde la vista detalle del taller.

**D-FO05** ❓ ¿La "Planificación semanal" de Formación debe registrarse en el sistema para comparar planificado vs ejecutado en el informe trimestral?
> **Desbloquea:** Módulo de planificación semanal + comparativo en informe trimestral
> **RESPUESTA:** La no deberia ejecutarse pero si deberia de haber parametros internos para que el sistema pueda comparar lo planificado con lo ejecutado. y así poder generar los indicadores

**D-FO06** ✅ ¿Los pasantes de IMATUR pueden actuar como facilitadores en talleres y charlas? ¿Debe quedar registrado?
> **Desbloquea:** Permitir `id_facilitador` apuntar a pasantes además de empleados, o campo separado
> **RESPUESTA:** No, no pueden, solo algunos empleados como los de formacion o personas seleccionadas por el area de formacion pueden ser facilitadores.
> **Impacto técnico implementado:** Sin cambio. El campo `talleres.id_facilitador` apunta solo a `empleados`. No se necesita tabla de facilitadores externos para talleres (distinto de rutas que tiene `nombre_facilitador_externo`).

**D-FO07** ✅ ¿El informe trimestral de Formación tiene un formato estándar que el sistema deba generar imprimible para la Alcaldía?
> **Desbloquea:** Vista/reporte de informe trimestral consolidado (metas, logros, actividades por tipo)
> **RESPUESTA:** Por el momento no, solo se necesita un reporte general de las actividades de formación en formato institucional con datos del taller.
> **Impacto técnico implementado:** Informe de actividad imprimible implementado en `app/views/talleres/informe_imprimible.php`. Formato institucional con: nombre de unidad estadal, nombre de actividad, fecha/hora/lugar, instituciones presentes, desglose demográfico (mujeres/hombres/niñas/niños), total atendidos y resumen de la actividad.

### 🟢 Mejoras menores

**D-FO08** ✅ ¿Un mismo taller necesita soporte para múltiples facilitadores (actualmente solo uno principal)?
> **Estado actual:** Planificado para v3.0 con tabla `taller_facilitadores`. ¿Es prioritario?
> **RESPUESTA:** No es prioritario, pero seria bueno tenerlo en cuenta para futuras versiones. Por ahora no
> **Impacto técnico implementado:** Queda en backlog v3.0. Sin cambio en modelo actual. Documentado en sección BACKLOG.

---

## MÓDULO 4 — PASANTES

### 🔴 Decisiones críticas

**D-PS01** ✅ ¿El paso de estado "Postulado → Aceptado" debe requerir una acción especial (ej: confirmación de Dirección) o cualquier usuario con rol Turismo puede hacerlo?
> **Desbloquea:** Restricción de rol en `PasantesController::editar()` para esa transición específica
> **RESPUESTA:** Debe esperar confirmacion de la direccion de turismo.
> **Impacto técnico implementado:** Restricción implementada en `PasantesController::editar()` — si el estado cambia de 'Postulado' a 'Aceptado', se verifica `$_SESSION['user_rol'] === 1` (Administrador). Si no cumple, se lanza error 403. Solo rol 1 puede aprobar esa transición.

**D-PS02** ✅ ¿La evaluación de pasantes usa escala numérica (1-20, 1-10) o cualitativa (Excelente/Bueno/Regular)? ¿Hay evaluaciones parciales o solo nota final?
> **Desbloquea:** Tipo del campo `nota` + si se necesita tabla `pasante_evaluaciones`
> **RESPUESTA:** Escala númerica con notas cualitativas para mejor entendimiento.
> **Impacto técnico implementado:** Modelo actual correcto. Campo `pasantes.nota` (DECIMAL numérico) + campo `pasantes.evaluacion` (texto cualitativo: Excelente/Bueno/Regular/Deficiente). Ambos campos ya existen en la BD. La vista detalle muestra ambos valores.

### 🟡 Funcionalidades importantes

**D-PS03** ✅ Lista exacta de documentos requeridos al inicio de la pasantía (carta institucional, constancia de inscripción, seguro estudiantil, copia de cédula, foto, planilla, otros)
> **Desbloquea:** Checklist de documentos en UI con los tipos exactos
> **RESPUESTA:** Carta institucional, copia de cédula, planilla.
> **Impacto técnico implementado:** Tabla `pasante_documentos` ya tiene estos 3 campos booleanos. La UI muestra checklist con exactamente: Carta Institucional, Copia de Cédula, Planilla. Campos extra (seguro estudiantil, foto) no se muestran ni validan.

**D-PS04** ✅ ¿Las horas de pasantía deben calcularse automáticamente a partir de `fecha_inicio` y `fecha_fin`? ¿Cuántas horas diarias son estándar?
> **Desbloquea:** Cálculo automático de horas en la vista detalle y carta de culminación
> **RESPUESTA:** Las horas de pasantía deben calcularse automáticamente a partir de `fecha_inicio` y `fecha_fin`. No hay horas diarias estándar.
> **Impacto técnico implementado:** Cálculo de días implementado en vista detalle de pasante: `(fecha_fin - fecha_inicio)` mostrado como "X días de pasantía". La carta de culminación imprimible incluye el período completo (fecha inicio — fecha fin) y el total de días calculado.

**D-PS05** ✅ ¿El tutor siempre debe ser el jefe del departamento, o puede ser cualquier empleado activo?
> **Desbloquea:** Filtro en el select de tutores al crear/editar pasante
> **RESPUESTA:** Cualquier empleado activo. El cual asigne la direccion conforme a la necesidad del proyecto/pasantes
> **Impacto técnico implementado:** Sin cambio. El select de tutores en `pasantes/crear.php` y `pasantes/editar.php` ya lista todos los empleados activos (`is_active = TRUE`). No se filtra por cargo ni departamento.

**D-PS06** ✅ ¿Los pasantes deben marcar asistencia diaria en el sistema, o su control de asistencia es independiente (papel)?
> **Desbloquea:** Módulo de asistencia de pasantes (tabla separada o usar `asistencias`)
> **RESPUESTA:** Su control de asistencia es independiente (modulo de visitantes en dado caso).
> **Impacto técnico implementado:** No se implementa módulo de asistencia para pasantes. El control es en papel. El módulo de Visitantes ya permite registrar su entrada/salida si se requiere trazabilidad.

### 🟢 Mejoras menores

**D-PS07** ✅ ¿Hay un límite de pasantes simultáneos por departamento o por tutor?
> **Desbloquea:** Validación de cupo al inscribir nuevo pasante
> **RESPUESTA:** No hay límite.
> **Impacto técnico implementado:** Sin validación de cupo implementada. `PasantesController::crear()` permite inscribir sin restricción de cantidad.

---

## MÓDULO 5 — RUTAS TURÍSTICAS

### 🔴 Decisiones críticas

**D-RT01** ✅ Si la misma ruta se ejecuta 4 veces al mes con 4 grupos distintos, ¿se crean 4 registros de ruta separados o se reutiliza el mismo registro con 4 fechas de visita distintas?
> **Desbloquea:** Diseño del modelo de "ejecución de ruta" — impacta BD, reportes y estadísticas
> **Opciones:** (A) Un registro de ruta por ejecución; (B) Una ruta con múltiples fechas en tabla separada `ejecuciones_ruta`
> **RESPUESTA:** Un registro de ruta por ejecución, con sus datos asociados (una ruta no deberia ser exactamente igual a otra en términos de valores a menos que sea arbitrario o casualidad).
> **Impacto técnico implementado:** Diseño actual correcto. Cada registro en `rutas` representa una ejecución independiente. No se necesita tabla `ejecuciones_ruta`. Sin cambios de BD.

**D-RT02** ⚠️ La tarifa de "Cumaná Histórica": ¿es fija por persona, varía por tipo de grupo (escolar/turista/corporativo)? ¿Quién cobra — IMATUR directamente o un tercero?
> **Desbloquea:** Módulo de registro de pagos en `participantes_ruta` o tabla `pagos_ruta`
> **RESPUESTA:** Aun sin información especifica, se asume que es fija por persona. pero deja esta pregunta como pendiente pero con los posibles cambios a ejecutar
> **Impacto técnico implementado:** Arquitectura preparada — campos `tiene_tarifa BOOL` y `tarifa_monto DECIMAL(10,2)` añadidos a tabla `rutas` (migración 007). La lógica de cobro no se activa en UI hasta confirmar flujo definitivo. Pendiente respuesta final.

**D-RT03** ✅ ¿El correlativo de oficios es **único para toda la institución** (un solo contador compartido entre rutas y talleres) o cada módulo tiene su propio correlativo independiente?
> **Desbloquea:** Si `configuracion_sistema.correlativo_oficio` es global o se necesitan múltiples claves
> **RESPUESTA:** cada correlativo debe ser especifico para cada modulo, así se lleva el control de cuantos oficios van en cada modulo. tienen que tener su identificador por modulo, año y consecutivo.
> **Impacto técnico implementado:** Correlativo por módulo implementado (migración 007). Claves en `configuracion_sistema`: `correlativo_oficio_ruta`/`ano_correlativo_ruta` para rutas; `correlativo_oficio_formacion`/`ano_correlativo_formacion` para formación. `ConfigSistema::generarNumeroOficio(string $modulo)` acepta parámetro de módulo. Formato: `RUTA-007/2026` o `FORM-001/2026`.

### 🟡 Funcionalidades importantes

**D-RT04** ✅ ¿Las instituciones educativas (colegios, liceos) que traen grupos a rutas deben estar pre-registradas en el sistema como entidad, separadas de los participantes individuales?
> **Desbloquea:** Tabla `instituciones_externas` + FK en `participantes_ruta.id_institucion`
> **RESPUESTA:** si, pero deberia de tener un campo que indique si es una institución educativa o no.
> **Impacto técnico implementado:** Tabla `instituciones_externas` creada (migración 007) con campo `es_educativa BOOL`. FK `participantes_ruta.id_institucion → instituciones_externas.id` (nullable). Permite agrupar participantes por institución en reportes.

**D-RT05** ✅ ¿El facilitador/guía de una ruta puede ser un guía externo certificado (no empleado de IMATUR)?
> **Desbloquea:** Si `id_facilitador` puede apuntar a personas externas además de empleados
> **RESPUESTA:** Si.
> **Impacto técnico implementado:** Campo `nombre_facilitador_externo VARCHAR(200)` añadido a tabla `rutas` (migración 007). Lógica en `RutasController`: si `id_facilitador` es NULL y `nombre_facilitador_externo` tiene valor, se usa el nombre externo. En vistas se muestra "(Externo)" junto al nombre.

### 🟢 Mejoras menores

**D-RT06** ✅ ¿Los puntos de ruta tienen coordenadas GPS registradas? ¿Sería útil visualizar un mapa del recorrido en pantalla (funcionando sin internet con Leaflet + OSM)?
> **Desbloquea:** Integración de Leaflet.js offline en `rutas/detalle.php`
> **RESPUESTA:** Los puntos (paradas) de la ruta no tienen coordenadas GPS registradas, pero sería útil visualizar un mapa del recorrido en pantalla (funcionando sin internet con Leaflet + OSM).
> **Impacto técnico implementado:** Pendiente descargar Leaflet.js + tiles OSM para funcionamiento offline. La tabla `puntos_ruta` ya tiene columnas `lat` y `lon`. Documentado como backlog — ver BACKLOG sección Fase 3.

---

## MÓDULO 6 — VISITANTES

### 🟡 Funcionalidades importantes

**D-VIS01** ✅ ¿El motivo de visita debe ser texto libre o una lista de categorías predefinidas?
> **Opciones sugeridas:** Reunión de trabajo / Trámite administrativo / Entrega de documentos / Visita institucional / Otro
> **Desbloquea:** Cambio de campo libre a `<select>` en formulario de visitas + CHECK en BD
> **RESPUESTA:** Debe ser una lista de categorías predefinidas: Reunión de trabajo / Trámite administrativo / Entrega de documentos / Visita institucional / Pasantías / Otro.
> **Impacto técnico implementado:** Campo `motivo_visita` cambiado de texto libre a `<select>` en `visitas/crear.php` y `visitas/editar.php`. CHECK constraint actualizado en BD con los 6 valores. Whitelist en `VisitasController` actualizada.

**D-VIS02** ✅ ¿"Procedencia" del visitante significa ciudad/estado de origen o institución que representa?
> **Desbloquea:** Etiqueta del campo en UI + tipo de dato (lista de estados vs texto libre)
> **RESPUESTA:** Significa la institución que representa.
> **Impacto técnico implementado:** Label cambiado de "Procedencia" a "Institución / Procedencia" en formularios y vistas de visitantes. El campo sigue siendo texto libre (nombre de institución). Sin cambio de BD.

**D-VIS03** ✅ ¿Hay visitantes extranjeros frecuentes (embajadores, delegaciones) que lleguen con pasaporte? ¿Con qué frecuencia?
> **Desbloquea:** Campo `tipo_documento` (CI / Pasaporte / RIF) en `visitantes`
> **RESPUESTA:** No, solo venezolanos.
> **Impacto técnico implementado:** Sin cambio. El campo `cedula` en `visitantes` permanece como identificador único. No se añade campo `tipo_documento`.

**D-VIS04** ✅ ¿El módulo de visitantes debe generar un reporte mensual para informes institucionales? ¿Qué datos incluiría?
> **Estado actual:** Reporte básico implementado en Fase 1 (visitantes por período/motivo/empleado). ¿Necesita algo adicional?
> **RESPUESTA:** El reporte por tiempo (periodo), institución, motivo de visita y empleado.
> **Impacto técnico implementado:** Reporte implementado en Fase 1 con todos los filtros requeridos. Filtro por procedencia/institución ya activo. Datos correctos. Sin cambios adicionales necesarios.

---

## MÓDULO 7 — OFICIOS Y DOCUMENTOS

### 🔴 Decisiones críticas

**D-OF01** ✅ ¿Siempre firma el Director General, o hay situaciones donde firma un subdirector, coordinador u otro funcionario habilitado?
> **Desbloquea:** Si la firma en los documentos imprimibles es fija o configurable por módulo
> **RESPUESTA:** Siempre firma el Director General o coordinación de dirección. Debe estar habilitado para ello.
> **Impacto técnico implementado:** Campo `firmante_cargo` añadido a `configuracion_sistema`. Permite configurar el cargo del firmante (ej: "Director General", "Coordinador de Dirección"). Todos los documentos imprimibles leen `firmante_cargo` desde `ConfigSistema::get('firmante_cargo')`. El nombre del firmante sigue siendo `director_nombre` + `director_apellido`.

**D-OF02** ✅ ¿Hay documentos que requieren dos firmas (ej: Director + Consultoría Jurídica)?
> **Desbloquea:** Segunda sección de firma en plantillas de documentos imprimibles
> **RESPUESTA:** No.
> **Impacto técnico implementado:** Sin cambio. Todas las plantillas imprimibles tienen una sola sección de firma.

**D-OF03** ✅ ¿Existe un "libro de correspondencia" donde se registran todos los oficios enviados y recibidos? ¿El sistema debe llevarlo?
> **Desbloquea:** Módulo de libro de correspondencia con todos los oficios emitidos/recibidos del sistema
> **RESPUESTA:** No. Pero el sistema debería de llevar el registro, así que hay que implementarlo.
> **Impacto técnico implementado:** Pendiente como módulo futuro. La infraestructura existe: tabla `oficios` (recibidos) y `oficios_emitidos` (enviados). El módulo de libro de correspondencia unificado queda en backlog Fase 3. Ver BACKLOG.

---

## MÓDULO 8 — REPORTES E INDICADORES

### 🟡 Funcionalidades importantes

**D-RE01** ✅ ¿IMATUR debe entregar informes periódicos a la Alcaldía, Ministerio de Turismo u otro ente? ¿Con qué frecuencia y qué indicadores son obligatorios?
> **Desbloquea:** Diseño del Informe de Gestión consolidado (PDF imprimible por período)
> **RESPUESTA:** Sí, informes trimestrales a la Alcaldía y Ministerio de Turismo.
> **Impacto técnico implementado:** Documentado para Fase 3. El informe consolidado trimestral requiere datos de todos los módulos. Ver BACKLOG.

**D-RE02** ✅ ¿El informe trimestral consolidado incluye todas las secciones (RRHH, Formación, Rutas, Inventario) o solo algunas?
> **Desbloquea:** Alcance y estructura del informe trimestral
> **RESPUESTA:** Sí, incluye todas las secciones.
> **Impacto técnico implementado:** Alcance documentado para Fase 3. El informe consolidado abarca: RRHH (empleados activos, permisos, asistencia), Formación (talleres/charlas/inducciones), Rutas (ejecuciones, participantes), Inventario (condición general).

**D-RE03** ✅ ¿Cuáles son los 5 indicadores más importantes que el Director revisa regularmente?
> **Desbloquea:** Reorganización del Dashboard para priorizar esos KPIs
> **RESPUESTA:** Trabajadores activos, Formación completada, Rutas programadas, Inventario disponible, Logs del sistema.
> **Impacto técnico implementado:** Dashboard actualizado con 5 KPIs en tarjetas prominentes: (1) Trabajadores Activos, (2) Talleres Finalizados (mes actual), (3) Rutas Ejecutadas (mes actual), (4) Bienes en Inventario Activo, (5) Actividad Reciente (últimas entradas en audit_logs).

**D-RE04** ✅ ¿Se debe reportar el número de turistas atendidos por mes/año desglosado por municipio de procedencia?
> **Desbloquea:** Filtro por procedencia en reporte de rutas + cruce con municipios
> **RESPUESTA:** Sí, se debe reportar el número de turistas atendidos por mes/año desglosado por municipio de procedencia.
> **Impacto técnico implementado:** Filtro por procedencia/institución ya implementado en reporte de visitantes. El reporte de rutas incluye columna de departamento/procedencia del grupo.

---

## MÓDULO 9 — USUARIOS Y SEGURIDAD

### 🔴 Decisiones críticas

**D-US01** ✅ ¿El Director necesita acceso al sistema? ¿Con qué tipo de permisos?
> **Opciones:** (A) Usa rol Administrador; (B) Rol nuevo "Dirección" — solo lectura + reportes; (C) No usa el sistema
> **Desbloquea:** Si se crea rol 5 con permisos de solo lectura ampliados
> **RESPUESTA:** Si necesita acceso, usaría el rol Administrador.
> **Impacto técnico implementado:** Sin cambio. El Director usa rol 1 (Administrador). No se crea rol adicional para Dirección.

**D-US02** ✅ ¿Existe personal de recepción que solo deba registrar visitantes y visitas, entrada de trabajadores, sin acceso a otros módulos?
> **Desbloquea:** Rol 5 "Recepción" con acceso solo a `Visitantes` y `Visitas`
> **RESPUESTA:** Sí, existe personal de recepción que solo debe registrar visitantes y visitas, entrada de trabajadores, sin acceso a otros módulos.
> **Impacto técnico implementado:** Rol 5 "Recepción" creado (migración 007 + semilla en tabla `roles`). Permisos en `Router.php`: Dashboard, Visitantes, Visitas, Asistencias. Sidebar en `header.php` muestra solo esas secciones para rol 5.

**D-US03** ⚠️ ¿Hay funcionarios que necesiten acceso de solo lectura a múltiples módulos sin poder editar?
> **Desbloquea:** Rol de "Solo lectura" o permisos granulares por módulo
> **RESPUESTA:** Sí, hay funcionarios que necesitan acceso de solo lectura a múltiples módulos sin poder editar.
> **Impacto técnico implementado:** Pendiente. Implementar rol 6 "Solo Lectura" es complejo — requiere deshabilitar todos los botones de crear/editar/eliminar en las vistas, o duplicar vistas con versiones de solo lectura. Queda en backlog. Ver BACKLOG.

### 🟢 Mejoras menores

**D-US04** ✅ ¿El sistema se usará solo dentro de la red local de IMATUR o habrá acceso remoto (desde casa, tablet, teléfono)?
> **Desbloquea:** Configuración de HTTPS, responsive design prioritario, posible VPN
> **RESPUESTA:** Por lo pronto sera dentro de la red local, pero se estima que se podrá acceder desde cualquier dispositivo con conexión a internet.
> **Impacto técnico implementado:** Sin cambio por ahora. Despliegue local con Laragon. El responsive design de Bootstrap 5.3 ya garantiza compatibilidad móvil cuando se abra acceso externo.

**D-US05** ⚠️ ¿Existe una política de contraseñas institucional? (longitud mínima, cambio periódico obligatorio)
> **Desbloquea:** Validación de contraseña en `UsuariosController` + flag `password_debe_cambiar`
> **RESPUESTA:** No existe una política de contraseñas institucional. Al registrarse el usuario se le pedirá que cambie la contraseña por defecto. Su contraseña por defecto sería la cédula.
> **Impacto técnico implementado:** Pendiente. Al crear usuario desde `UsuariosController`, la contraseña por defecto debe ser el número de cédula del empleado asociado (o un valor ingresado manualmente si no hay empleado). Flag `password_debe_cambiar BOOL` pendiente de agregar a `usuarios`. Ver BACKLOG.

---

## MÓDULO 10 — CONFIGURACIÓN INSTITUCIONAL

### 🟢 Verificaciones

**D-CF01** ✅ ¿El RIF G-20008498-7 es el RIF institucional vigente?
> **RESPUESTA:** Sí, el RIF G-20008498-7 es el RIF institucional vigente.
> **Impacto técnico implementado:** Dato en `configuracion_sistema` clave `rif_institucion`. Sin cambio.

**D-CF02** ✅ ¿La dirección "Calle Sucre N° 11, San Francisco, Parroquia Santa Inés" es la dirección actual?
> **RESPUESTA:** Sí, la dirección "Calle Sucre N° 11, San Francisco, Parroquia Santa Inés" es la dirección actual.
> **Impacto técnico implementado:** Dato en `configuracion_sistema` clave `direccion_institucion`. Sin cambio.

**D-CF03** ✅ ¿Hay más de un número telefónico institucional? ¿Se muestran todos en los oficios?
> **RESPUESTA:** No, solo se muestra un número telefónico institucional.
> **Impacto técnico implementado:** Un solo campo `telf_institucion` en `configuracion_sistema`. Sin cambio.

**D-CF04** ✅ ¿La Resolución de nombramiento del Director cambia cada período de gobierno? ¿Quién la actualiza en el sistema?
> **Desbloquea:** Proceso/manual de actualización de `configuracion_sistema` al cambiar de Director
> **RESPUESTA:** Sí, la Resolución de nombramiento del Director cambia cada período de gobierno. Y quien la actualiza en el sistema es el Administrador.
> **Impacto técnico implementado:** El Administrador (rol 1) accede a `ConfigController` para actualizar `director_nombre`, `director_apellido`, `resolucion_numero`, `resolucion_fecha`, `gaceta_numero`, `gaceta_fecha`. Sin cambio en lógica.

---

## PREGUNTAS TRANSVERSALES

### 🔴 Críticas

**D-TX01** ⚠️ ¿Qué operaciones requieren aprobación formal de un superior antes de ejecutarse? (dar de baja un bien, registrar permiso, cambiar estado de pasante, cambiar estado de taller)
> **Desbloquea:** Flujos de aprobación en los módulos correspondientes
> **RESPUESTA:** No se requiere aprobación formal de un superior antes de ejecutarse. Pero hay acciones que sí requieren aprobación formal (como la aceptación de un pasante, entre otras).
> **Impacto técnico implementado:** Aprobación de pasantes (Postulado→Aceptado) implementada — solo rol 1. Otras transiciones que podrían requerir aprobación formal: dar de baja un bien (pendiente confirmar), aprobar permiso laboral (pendiente D-RH03). Pregunta permanece abierta para otros módulos.

**D-TX02** ✅ ¿El sistema debe mostrar alertas internas? (contratos por vencer, cupos llenos, pasantes próximos a culminar, talleres programados para hoy)
> **Desbloquea:** Módulo de notificaciones/alertas en el Dashboard o sidebar
> **RESPUESTA:** Sí, el sistema debe mostrar alertas internas.
> **Impacto técnico implementado:** Alertas implementadas en el Dashboard: (1) Contratos próximos a vencer (empleados contratados con fecha_egreso en los próximos 30 días), (2) Pasantes próximos a culminar (fecha_fin en los próximos 15 días), (3) Talleres en curso hoy. Las alertas se muestran como banners `alert-warning` en la parte superior del Dashboard.

### 🟢 Menores

**D-TX03** ⚠️ ¿Hay datos históricos (empleados, bienes, talleres anteriores) que deban migrarse al sistema? ¿En qué formato están (Excel, Word, papel)?
> **Desbloquea:** Script de importación masiva de datos históricos
> **RESPUESTA:** Sí, en excel y papel.
> **Impacto técnico implementado:** Pendiente. Script de importación desde Excel requiere definir la estructura exacta de los archivos fuente. Ver BACKLOG Fase 4.

**D-TX04** ✅ ¿Cuántos empleados tiene IMATUR actualmente? ¿Cuántos bienes tiene el inventario aproximadamente?
> **Desbloquea:** Estimación del volumen de datos para ajustar paginación y performance
> **RESPUESTA:** aproximadamente 34 empleados y 60 bienes.
> **Impacto técnico implementado:** Volumen bajo — paginación de 20 registros por página es suficiente. No se necesitan índices adicionales ni optimizaciones de performance. Sin cambio.

---

## RESUMEN DE BLOQUEOS POR PRIORIDAD

### 🔴 Pendiente respuesta — bloquean diseño de BD o lógica central

| ID | Pregunta resumida | Módulo |
|----|-------------------|--------|
| D-IN06 | ¿Existe responsable del bien asignado nominalmente? | Inventario |
| D-RH01 | Horarios: ¿cuántos turnos existen y cómo se llaman? | RRHH |
| D-RH02 | Tipos de permisos laborales válidos | RRHH |
| D-RH03 | ¿Los permisos requieren aprobación o son directos? | RRHH |
| D-RH04 | Vacaciones: ¿días hábiles o calendario? ¿cuántos días/año? | RRHH |
| D-RH05 | ¿Saldo de vacaciones se calcula automático o manual? | RRHH |

### 🟡 Pendiente respuesta — funcionalidades de alto impacto esperando

| ID | Pregunta resumida | Módulo |
|----|-------------------|--------|
| D-RT02 | Tarifa Cumaná Histórica: ¿quién cobra, flujo exacto? | Rutas |
| D-RH06 | ¿Vacaciones no disfrutadas se acumulan o se pierden? | RRHH |
| D-RH07 | ¿Alertas de contratos por vencer? | RRHH |
| D-RH08 | ¿Horarios fijos o por departamento? | RRHH |
| D-RH09 | ¿Reportes de puntualidad/ausentismo para nómina? | RRHH |
| D-TX01 | ¿Otras aprobaciones formales pendientes de confirmar? | Transversal |

---

## BACKLOG — Funcionalidades para Fases Siguientes

### Fase 3 — Módulos RRHH faltantes (requiere responder D-RH01 a D-RH11)
- HorariosController (turnos + asignación a empleados)
- PermisosLaboralesController (CRUD + reporte)
- VacacionesController (saldo + días tomados)

### Fase 3 — Funcionalidades pendientes de información
- Libro de correspondencia unificado (D-OF03)
- Informe de gestión trimestral consolidado (D-RE01, D-RE02)
- Mapa Leaflet.js offline para puntos de ruta (D-RT06)
- Cobro tarifa Cumaná Histórica (D-RT02 — pendiente definir flujo)
- Rol 6 "Solo Lectura" (D-US03 — pendiente confirmar alcance)

### Fase 4 — Mejoras de calidad
- Importación de datos históricos desde Excel (D-TX03)
- Múltiples facilitadores por taller (D-FO08 — v3.0)
- Contraseña por defecto = cédula al crear usuario (D-US05)

### Nuevas preguntas generadas durante implementación

- D-NEW01 ❓ ¿El correlativo de oficios de formación (talleres externos) se necesita actualmente, o solo se prepara la infraestructura?
  > **Desbloquea:** Activar correlativo FORM-XXX en UI de talleres
  > **RESPUESTA:**
- D-NEW02 ❓ ¿La tabla `instituciones_externas` debe tener un CRUD dedicado en el sistema, o solo se usa como lookup al registrar participantes?
  > **Desbloquea:** Controlador `InstitucionesExternasController` + vista CRUD
  > **RESPUESTA:**
- D-NEW03 ❓ ¿El facilitador externo (`nombre_facilitador_externo`) debe ir en una lista gestionada por el sistema, o es texto libre cada vez?
  > **Desbloquea:** Tabla de guías/facilitadores externos registrados
  > **RESPUESTA:**
- D-NEW04 ❓ ¿Los tipos de permisos laborales son los estándar venezolanos (Médico, Personal, Duelo, Maternidad/Paternidad, Sindical, Estudio) u otros?
  > **Requiere:** Enum final en `permisos_laborales.tipo_permiso` para PermisosLaboralesController
  > **RESPUESTA:**
- D-NEW05 ❓ ¿Los días de vacaciones según LOTTT venezolana (15 días + 1 día adicional por año) aplican a IMATUR como ente municipal?
  > **Requiere:** Fórmula de cálculo de saldo en VacacionesController
  > **RESPUESTA:**

### Preguntas RRHH adicionales (identificadas en revisión 2026-05-22)

- D-RH12 ❓ ¿Se manejan horas extras? ¿Deben registrarse en el sistema?
  > **Desbloquea:** Campo `horas_extra` en `asistencias` o tabla separada
  > **RESPUESTA:**
- D-RH13 ❓ ¿Las ausencias justificadas e injustificadas se gestionan por separado? ¿Quién las aprueba?
  > **Desbloquea:** Tipo de ausencia en `asistencias` o diferenciación en `permisos_laborales`
  > **RESPUESTA:**
- D-RH14 ❓ ¿Existe "bono vacacional"? ¿El sistema debe calcularlo o solo registrarlo?
  > **Desbloquea:** Campo en `vacaciones` o módulo de cálculo salarial
  > **RESPUESTA:**

### Preguntas Inventario adicionales (identificadas en revisión 2026-05-22)

- D-IN09 ❓ ¿El sistema debe registrar el costo de adquisición, fecha de compra y proveedor de los bienes?
  > **Desbloquea:** Campos `costo_adquisicion`, `fecha_compra`, `proveedor` en tabla `inventario`
  > **RESPUESTA:**
