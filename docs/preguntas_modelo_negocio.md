# Preguntas de Modelo de Negocio — SIGTUR-IMATUR

**Institución:** IMATUR-SUCRE — Instituto Municipal Autónomo de Turismo, Municipio Sucre  
**Fecha de elaboración:** 2026-05-08 | **Última actualización:** 2026-05-08  
**Propósito:** Aclarar el modelo operativo real de cada módulo para identificar brechas de diseño antes de continuar el desarrollo.

**Leyenda:**
- ✅ **Respondida** — implementada o documentada
- ⚠️ **Respondida parcialmente** — respuesta conocida, implementación pendiente
- ❓ **Pendiente** — sin respuesta aún, bloquea desarrollo

---

## MÓDULO 1 — INVENTARIO DE BIENES

### Clasificación y Codificación

1. ❓ ¿Qué tipos de bienes maneja IMATUR? (mobiliario, equipos audiovisuales, equipos de cómputo, materiales de oficina, vehículos, instrumentos, otros)
2. ❓ ¿Existe actualmente un código de Bien Nacional (BN) asignado por la Alcaldía o la Contraloría? ¿Cómo se forma ese código? (prefijo por categoría, número correlativo, año, etc.)
3. ❓ ¿Quién asigna el código BN — IMATUR o la Contraloría Municipal? ¿Hay bienes sin código asignado todavía?
4. ❓ ¿Se maneja un código interno propio de IMATUR además del BN?
5. ❓ ¿Las categorías actuales del sistema reflejan las categorías reales de la institución, o hay una clasificación oficial diferente?

### Condición y Ciclo de Vida

6. ❓ ¿Cuáles son los estados posibles de un bien? El sistema tiene: Nuevo, Bueno, Regular, Dañado. ¿Falta alguno? (ej: En Reparación, En Garantía)
7. ❓ ¿Cómo se registra oficialmente la "baja" de un bien? ¿Requiere un acto administrativo, resolución firmada por el Director, o acta de la Contraloría?
8. ❓ ¿Los bienes dados de baja deben seguir siendo visibles en el historial o simplemente desaparecen del inventario activo?
9. ❓ ¿Se realizan inventarios físicos periódicos (anuales, semestrales)? ¿El sistema debe apoyar ese proceso de verificación con una lista de conteo?

### Movimientos y Traslados

10. ❓ ¿Existe algún tipo de movimiento no contemplado? El sistema tiene: Asignación, Devolución, Traslado, Baja, Mantenimiento.
11. ❓ ¿Un bien puede estar asignado a más de un empleado o departamento al mismo tiempo?
12. ❓ ¿Los traslados entre departamentos requieren algún documento físico o firma del responsable?
13. ❓ ¿Existe la figura de "responsable del bien" asignado nominalmente en el inventario?
14. ⚠️ ¿Los bienes prestados a rutas turísticas deben devolverse automáticamente al finalizar la ruta, o se gestiona la devolución manualmente?  
    **Respuesta parcial:** La devolución es manual actualmente. No hay devolución automática implementada.
15. ❓ ¿Se compran bienes nuevos regularmente? ¿El sistema debe registrar el costo de adquisición, fecha de compra o proveedor?

### Ubicaciones

16. ❓ ¿Las ubicaciones son solo instalaciones fijas de IMATUR o incluyen ubicaciones temporales (eventos, rutas fuera de sede)?
17. ❓ ¿Un bien puede estar en una ubicación sin estar asignado a ningún empleado?

---

## MÓDULO 2 — RECURSOS HUMANOS (EMPLEADOS)

### Estructura Organizativa

18. ✅ ¿Cuántos departamentos tiene IMATUR actualmente? ¿Hay subdepartamentos?  
    **Respuesta:** 4 Direcciones: Planificación y Gestión Turística, Administrativa, Talento Humano, y Consultoría Jurídica. Cada una con subdepartamentos. Ver `docs/ESTRUCTURA_ORGANIZATIVA.md`.
19. ❓ ¿Los cargos son únicos por departamento o un mismo cargo puede existir en varios departamentos?
20. ✅ ¿Existe un organigrama oficial?  
    **Respuesta:** Sí. Ver `docs/ESTRUCTURA_ORGANIZATIVA.md`. La BD usa estructura plana hasta que se requieran reportes jerárquicos.
21. ❓ ¿Hay empleados que pertenecen a más de un departamento simultáneamente?

### Tipos de Empleados y Contratos

22. ⚠️ Los tipos de contrato actuales son: Fijo, Contratado, Suplente, Comisión de Servicio. ¿Es completa esta lista? ¿Hay personal obrero, directivo, de libre nombramiento?  
    **Respuesta parcial:** Lista confirmada como suficiente por ahora. Pendiente verificar personal de libre nombramiento.
23. ❓ ¿Los empleados contratados tienen fecha de vencimiento de contrato? ¿El sistema debe alertar cuando un contrato está por vencer?
24. ❓ ¿Hay personal que preste servicios sin ser empleado formal (voluntarios, servicio comunitario)?
25. ❓ ¿El sueldo base del cargo es el mismo para todos los empleados en ese cargo, o puede variar por empleado?

### Asistencia

26. ⚠️ ¿Cómo se registra actualmente la asistencia?  
    **Respuesta parcial:** El sistema tiene marcaje manual (entrada/salida). El título del módulo es "Control de Asistencia Biométrico (Manual)". No hay biométrico real.
27. ❓ ¿El horario laboral es fijo para todos (ej: 8am–4pm) o hay turnos diferentes por departamento?
28. ❓ ¿Se manejan horas extras? ¿Deben registrarse en el sistema?
29. ❓ ¿Las llegadas tarde generan algún descuento o sanción administrativa que el sistema deba calcular?
30. ❓ ¿Se registran ausencias justificadas e injustificadas por separado? ¿Quién las aprueba?
31. ❓ ¿El sistema debe calcular reportes de puntualidad o ausentismo para la nómina?

### Permisos y Vacaciones

32. ❓ ¿Qué tipos de permisos laborales existen? (médico, personal, duelo, maternidad/paternidad, sindical, otro)
33. ❓ ¿Los permisos requieren aprobación de un superior antes de aplicarse? ¿Quién aprueba?
34. ❓ ¿Las vacaciones se calculan por días hábiles o días calendario?
35. ❓ ¿Cuántos días de vacaciones corresponden por año de servicio según la normativa venezolana vigente para entes municipales?
36. ❓ ¿Los días de vacaciones no disfrutados se acumulan o se pierden al cierre del año?
37. ❓ ¿Existe "bono vacacional"? ¿El sistema debe calcularlo o solo registrarlo?

---

## MÓDULO 3 — FORMACIÓN (TALLERES, CHARLAS, INDUCCIONES)

### Tipos de Actividad

38. ✅ ¿La lista de tipos de actividad es completa?  
    **Respuesta:** Sí. Los tipos son: Taller, Charla / Conversatorio, Inducción. Implementado en migración 006.
39. ✅ ¿Hay diferencia operativa entre un "Taller" y una "Charla"?  
    **Respuesta:** No, son el mismo concepto con diferente nombre. No hay diferencia de duración, certificado ni proceso.
40. ✅ ¿Las actividades pueden ser fuera de las instalaciones de IMATUR?  
    **Respuesta:** Sí. Actividades externas dependen de Zona Educativa para selección de instituciones.
41. ✅ ¿Los participantes son siempre externos o también pueden ser empleados de IMATUR?  
    **Respuesta:** También pueden ser trabajadores de IMATUR. Las actividades internas (`es_interna = TRUE`) son para el personal propio.
42. ✅ ¿Los niños participantes deben registrarse con datos del representante?  
    **Respuesta:** Se registran datos del niño/a y del docente acompañante (nombre + cédula del docente). Implementado en migración 006.
43. ✅ ¿Existe límite de edad mínima?  
    **Respuesta:** Sí, a partir de los 9 años.
44. ✅ ¿Un mismo participante puede inscribirse en múltiples talleres?  
    **Respuesta:** Sí, especialmente los brigadistas. El sistema no bloquea la inscripción múltiple.
45. ✅ ¿Se emite certificado de participación?  
    **Respuesta:** No.
46. ✅ ¿El informe demográfico es un requisito de reporte ante la Alcaldía?  
    **Respuesta:** Sí, ante la Alcaldía y la Dirección de IMATUR.

### Sedes y Facilitadores

47. ⚠️ ¿Los facilitadores son siempre empleados de IMATUR o pueden ser ponentes externos?  
    **Respuesta parcial:** Para actividades internas pueden ser personas externas al Departamento de Formación. La tabla actualmente solo soporta un facilitador empleado de IMATUR (`id_facilitador FK empleados`).
48. ✅ ¿Un mismo taller puede tener más de un facilitador?  
    **Respuesta:** Sí. Actualmente el sistema soporta un facilitador principal. Múltiples facilitadores es una mejora futura (v3.0).

### Oficios de Talleres

49. ✅ ¿Los oficios de talleres son solicitudes que recibe IMATUR desde Zona Educativa?  
    **Respuesta:** Sí. Son oficios **entrantes** (de otras instituciones hacia IMATUR). Se registran en la tabla `oficios`.
50. ✅ ¿Quién firma los oficios de talleres?  
    **Respuesta:** El mismo Director que firma los oficios de rutas.
51. ❓ ¿Un oficio de entrada puede originar múltiples talleres o siempre es uno a uno?

### Informe Trimestral de Formación

52. ❓ ¿El informe de gestión trimestral de Formación tiene un formato estándar que el sistema deba generar imprimible?
53. ❓ ¿La "Planificación semanal" de Formación debe registrarse en el sistema o es un documento separado (Word/papel)?
54. ❓ ¿El informe demográfico (`taller_informes`) debe ser **obligatorio** antes de poder marcar un taller como "Finalizado"?

---

## MÓDULO 4 — PASANTES

### Proceso de Pasantía

55. ✅ ¿Cuál es el flujo completo de una pasantía?  
    **Respuesta:** Postulado → Aceptado/Rechazado → En Curso → Culminado/Abandonado. El paso a Aceptado requiere carta firmada y sellada por Dirección.
56. ✅ ¿El estado "Postulado" significa que ya entregó documentos?  
    **Respuesta:** Sí, ya preguntó y entregó carta de postulación a IMATUR.
57. ✅ ¿Quién aprueba el paso de Postulado a Aceptado?  
    **Respuesta:** La Dirección General. Al firmar y sellar la carta, se remite la carta de aceptación al pasante.

### Documentos

58. ❓ ¿Qué documentos debe entregar obligatoriamente un pasante al inicio? (carta de la institución, constancia de estudios, seguro, planilla, foto, cédula, otro)
59. ⚠️ ¿Los documentos se guardan físicamente o digitalmente?  
    **Respuesta parcial:** Se archivan físicamente. El sistema registra solo si el documento fue entregado (flag booleano). No hay carga de archivos digitales.
60. ❓ ¿Al culminar la pasantía, el sistema debe generar la carta de culminación en formato imprimible (como el oficio de rutas)?
61. ❓ ¿Las horas de pasantía deben calcularse automáticamente a partir de `fecha_inicio` y `fecha_fin`?

### Evaluación

62. ❓ ¿Existe una rúbrica de evaluación estándar? ¿La "nota" en el sistema es la nota final o hay evaluaciones parciales?
63. ❓ ¿Hay pasantías de investigación distintas a las de servicio comunitario?
64. ❓ ¿Los pasantes reciben alguna contraprestación económica (beca, viáticos)?

### Tutor

65. ❓ ¿El tutor de pasantía siempre es el jefe del departamento o puede ser cualquier empleado?
66. ❓ ¿Hay un límite de pasantes simultáneos por tutor o por departamento?

---

## MÓDULO 5 — RUTAS TURÍSTICAS

### Definición y Recurrencia

67. ⚠️ ¿Una "ruta turística" es un itinerario fijo que se repite con diferentes grupos?  
    **Respuesta parcial:** Sí, las rutas son itinerarios reutilizables. Se usa `fecha_visita` para registrar cada ejecución. El diseño actual no diferencia entre el itinerario base y una ejecución concreta.
68. ❓ Si la misma ruta se ejecuta varias veces al mes con grupos distintos, ¿cómo debe registrarse? ¿Un registro de ruta por ejecución o una sola ruta con múltiples grupos/fechas?
69. ❓ ¿Cuántas rutas turísticas activas opera IMATUR actualmente?

### Participantes y Grupos

70. ✅ ¿Cuáles son los tipos de participantes de rutas?  
    **Respuesta:** Cumaná Histórica admite todo público. Exploradores de Cumaná es para instituciones escolares. También hay grupos familiares y corporativos.
71. ✅ ¿Los participantes de Cumaná Histórica pagan tarifa?  
    **Respuesta:** Sí, Cumaná Histórica es paga. El sistema no registra pagos actualmente.
72. ❓ ¿Las escuelas o instituciones que traen grupos deben estar pre-registradas en el sistema como "institución" aparte de los participantes individuales?
73. ❓ ¿El pago de Cumaná Histórica se registra por persona, por grupo, o por institución? ¿Quién gestiona el cobro?

### Puntos de Ruta

74. ❓ ¿Los "puntos de ruta" tienen un orden obligatorio o el guía puede alterar el recorrido según el día?
75. ❓ ¿Los puntos tienen un tiempo estimado de visita? ¿Se suma para calcular duración total automáticamente?
76. ❓ ¿Los puntos de ruta tienen coordenadas GPS registradas? ¿El sistema debería mostrar mapa del recorrido?

### Facilitadores y Guías

77. ❓ ¿El "facilitador" de una ruta puede ser un guía externo certificado, no necesariamente empleado de IMATUR?
78. ❓ ¿Un empleado puede ser facilitador de múltiples rutas activas al mismo tiempo?

### Oficios de Ruta

79. ✅ ¿Los oficios emitidos llevan correlativo automático?  
    **Respuesta:** Sí. Formato `001/2026`, se reinicia cada año. Implementado en `configuracion_sistema` y `oficios_emitidos`.
80. ❓ ¿El correlativo de oficios es **único para toda la institución** (compartido entre rutas y talleres) o hay correlativo separado por módulo?
81. ❓ ¿Los talleres externos también deben generar **oficios emitidos** desde IMATUR hacia las instituciones, o solo oficios recibidos?

---

## MÓDULO 6 — VISITANTES Y CONTROL DE VISITAS

### Registro de Visitantes

82. ✅ ¿Quiénes son los "visitantes" en este módulo?  
    **Respuesta:** Personas externas que vienen físicamente a las instalaciones de IMATUR (trámites, reuniones). Los turistas de rutas se registran en el módulo de Rutas.
83. ❓ ¿Los visitantes son siempre venezolanos con cédula, o se reciben visitantes extranjeros con pasaporte? ¿Con qué frecuencia?
84. ❓ ¿"Procedencia" significa ciudad/estado de origen o institución que representa el visitante?

### Control de Entrada/Salida

85. ✅ ¿Quién registra la entrada al sistema?  
    **Respuesta:** El funcionario que atiende (cualquier usuario con acceso al módulo de Visitas).
86. ❓ ¿El "motivo de visita" debe ser texto libre o una lista de categorías predefinidas? (reunión, trámite, entrega de documentos, otro)
87. ✅ ¿Un visitante puede entrar y salir múltiples veces en el mismo día?  
    **Respuesta:** Sí. Cada entrada/salida es un registro separado en `visitas`. El patrón toggle maneja esto.
88. ✅ ¿El registro de visitas registra con qué empleado se reunió el visitante?  
    **Respuesta:** Sí, campo `id_empleado` opcional en `visitas`. Ya implementado.

### Reportes

89. ❓ ¿El módulo de visitantes debe generar algún reporte específico (diario, mensual)? ¿Para qué ente o propósito?

---

## MÓDULO 7 — OFICIOS Y DOCUMENTOS INSTITUCIONALES

### Modelo de Oficios

90. ❓ ¿Existe **un único correlativo** de oficios para toda la institución o hay un correlativo por departamento o módulo?
91. ❓ ¿Los oficios de IMATUR tienen un formato único o varía según el departamento que los emite?
92. ❓ ¿El sistema actual genera oficios **solo para rutas turísticas**, o también debe generarlos para Formación (talleres) y RRHH (sanciones, llamadas de atención)?
93. ❓ ¿Los oficios deben archivarse digitalmente (PDF en servidor) o se imprimen y archivan físicamente solamente?
94. ❓ ¿Existe un "libro de correspondencia" donde se registran todos los oficios enviados y recibidos?
95. ❓ ¿Los oficios **recibidos** de otras instituciones deben registrarse en el sistema? ¿Con qué datos mínimos?

### Firmas y Autorizaciones

96. ❓ ¿Siempre firma el Director? ¿Hay situaciones donde firma un subdirector, coordinador u otro funcionario habilitado?
97. ❓ ¿Hay documentos que requieren dos firmas (ej: Director + Consultoría Jurídica)?
98. ❓ ¿Se usa firma electrónica o siempre es firma manuscrita en papel impreso?

---

## MÓDULO 8 — REPORTES Y ESTADÍSTICAS

### Obligaciones de Reporte

99. ❓ ¿IMATUR debe entregar informes periódicos a la Alcaldía, Ministerio de Turismo u otro ente? ¿Con qué frecuencia?
100. ❓ ¿Qué indicadores son **obligatorios** en esos informes? (personas capacitadas, rutas ejecutadas, visitantes, bienes inventariados, etc.)
101. ❓ ¿Los reportes actuales del sistema (rutas, pasantes, asistencia) cubren esas obligaciones o hacen falta reportes específicos?
102. ❓ ¿Se requiere un "informe de gestión" mensual o trimestral consolidado con todos los módulos?

### Estadísticas de Turismo

103. ❓ ¿Se debe reportar el número de turistas atendidos por mes/año desglosado por municipio de procedencia?
104. ❓ ¿Existe algún indicador de "impacto turístico" que la institución deba calcular (ej: número de personas sensibilizadas)?
105. ❓ ¿Los datos del módulo de Visitantes (control de entrada) se cuentan como "turistas" en las estadísticas o son visitantes administrativos?

### Informe Trimestral de Formación

106. ❓ ¿El informe trimestral de Formación tiene **secciones fijas** que el sistema deba generar? (metas, logros, actividades realizadas, número de personas atendidas por tipo de entidad)
107. ❓ ¿Las "metas" del informe trimestral se registran previamente en el sistema (planificación) o se calculan a partir de lo ejecutado?

---

## MÓDULO 9 — USUARIOS Y SEGURIDAD

### Roles y Accesos

108. ⚠️ Los roles actuales son: Administrador, RRHH, Turismo, Inventario. ¿Refleja esto los usuarios reales del sistema?  
     **Respuesta parcial:** Aprobado como estructura inicial. Pendiente confirmar si el Director necesita rol propio o usa Administrador.
109. ❓ ¿El Director necesita acceso al sistema? ¿Con qué tipo de permisos? (¿solo lectura de reportes, o acceso total como Administrador?)
110. ❓ ¿Existe una recepcionista o secretaria que deba registrar visitantes pero sin acceso a otros módulos? ¿Necesitaría un rol 5 "Recepción"?
111. ❓ ¿Hay funcionarios que necesitan acceso de **solo lectura** a múltiples módulos sin poder editar?
112. ❓ ¿Los facilitadores externos al sistema necesitan algún tipo de acceso?

### Seguridad

113. ❓ ¿Cuántos usuarios simultáneos usarán el sistema aproximadamente?
114. ❓ ¿El sistema se usará solo dentro de la red local de IMATUR o habrá acceso remoto (desde casa, tablet, teléfono)?
115. ❓ ¿Existe una política de contraseñas institucional? (longitud mínima, cambio periódico)

---

## MÓDULO 10 — CONFIGURACIÓN Y DATOS INSTITUCIONALES

### Datos Formales

116. ✅ Nombre oficial de la institución  
     **Respuesta:** "Instituto Municipal Autónomo de Turismo (IMATUR-SUCRE)". Ya configurado en `configuracion_sistema`.
117. ❓ ¿La Resolución de nombramiento del Director cambia cada período de gobierno? ¿Quién la actualiza en el sistema?
118. ❓ ¿El RIF G-20008498-7 es el RIF institucional vigente?
119. ❓ ¿La dirección física "Calle Sucre N° 11, San Francisco, Parroquia Santa Inés" es la dirección actual?
120. ❓ ¿Hay más de un número telefónico institucional? ¿Se muestran todos en los oficios?

---

## PREGUNTAS TRANSVERSALES

### Flujos de Aprobación

121. ❓ ¿Qué operaciones requieren aprobación de un superior antes de ejecutarse? (dar de baja un bien, registrar un permiso, inscribir un pasante, cambiar estado de taller)
122. ❓ ¿El sistema debe implementar flujos de aprobación (solicitar → aprobar) o todas las operaciones son inmediatas y se controlan por roles?

### Notificaciones

123. ❓ ¿El sistema debe mostrar alertas internas? (contratos por vencer, cupos llenos, pasantes próximos a culminar, talleres programados para hoy)
124. ❓ ¿Las notificaciones serían solo dentro del sistema o también por correo electrónico?

### Histórico y Auditoría

125. ⚠️ ¿Con qué frecuencia se consulta el historial de auditoría?  
     **Respuesta parcial:** El módulo de Auditoría está implementado. Solo el Administrador tiene acceso al historial completo y la papelera.
126. ❓ ¿Cuántos años de historial debe conservar el sistema? ¿Existe política de retención de datos?

### Datos Existentes

127. ❓ ¿Hay datos históricos (empleados, bienes, talleres anteriores) que deban migrarse al sistema? ¿En qué formato están? (Excel, Word, papel)
128. ❓ ¿Cuántos empleados tiene IMATUR actualmente?
129. ❓ ¿Cuántos bienes tiene el inventario aproximadamente?
130. ❓ ¿Cuántos talleres/actividades se realizan en promedio por mes?
131. ❓ ¿Cuántas rutas turísticas activas opera IMATUR?

---

## PREGUNTAS NUEVAS — Identificadas durante el desarrollo (Fase 2)

Estas preguntas surgieron al implementar los módulos y descubrir brechas no anticipadas.

### RRHH — Brechas de Horarios, Permisos y Vacaciones

132. ❓ ¿Los horarios de trabajo están ya definidos formalmente? ¿Cuántos turnos existen? ¿Los nombres de los turnos son: Matutino, Vespertino, o tienen otro nombre institucional?
133. ❓ ¿El módulo de Permisos Laborales debe tener flujo de aprobación (empleado solicita, jefe aprueba) o el registro es directo por el responsable de RRHH?
134. ❓ ¿Las vacaciones se solicitan al sistema formalmente, o RRHH las registra directamente después de coordinarse internamente?
135. ❓ ¿El saldo de vacaciones (días disponibles) debe calcularse automáticamente año a año, o RRHH lo gestiona manualmente y el sistema solo registra lo tomado?

### Inventario — Proceso formal de baja

136. ❓ ¿El proceso de "Dar de Baja" un bien requiere generar un acto administrativo imprimible desde el sistema (como el oficio de rutas) o solo el registro en el sistema es suficiente?
137. ❓ ¿Los bienes dados de baja se entregan a la Contraloría Municipal para su verificación? ¿El sistema debe generar algún listado oficial para ese proceso?
138. ❓ ¿Existe diferenciación entre bienes fungibles (consumibles: papel, tóner) y bienes durables (equipos, mobiliario)? ¿Se manejan diferente en el inventario?

### Rutas — Recurrencia y pagos

139. ❓ La ruta "Cumaná Histórica" tiene tarifa. ¿La tarifa es fija por persona o varía según el tipo de grupo (escolar, turista individual, corporativo)? ¿Quién cobra — IMATUR o un tercero?
140. ❓ ¿Si una ruta se ejecuta 4 veces al mes con 4 grupos distintos, se crean 4 registros de ruta separados o se reutiliza el mismo con 4 fechas de visita distintas? Esta decisión impacta el diseño del módulo.
141. ❓ ¿Las instituciones educativas (colegios, liceos) que traen grupos a rutas deben estar registradas en el sistema como entidad, separadas de los participantes individuales?

### Formación — Informes y documentos

142. ❓ ¿El formato de asistencia que emite Talento Humano para actividades internas tiene campos específicos distintos al del sistema actual? ¿El sistema debe generarlo imprimible?
143. ❓ ¿La "Planificación semanal" de actividades de Formación debe registrarse en el sistema para poder comparar lo planificado vs lo ejecutado en el informe trimestral?
144. ❓ ¿Los pasantes de IMATUR pueden actuar como facilitadores en talleres y charlas? ¿Esto debe quedar registrado en el sistema?
145. ❓ ¿El informe demográfico (mujeres, hombres, niñas, niños) debe completarse obligatoriamente antes de poder cambiar el estado del taller a "Finalizado"?

### Pasantes — Documentación y evaluación

146. ❓ Lista exacta de documentos requeridos al inicio de la pasantía: ¿carta de la institución educativa, constancia de inscripción, seguro estudiantil, planilla de datos, copia de cédula, foto carnet, otros?
147. ❓ ¿La "carta de culminación" tiene un formato institucional fijo? Si es así, ¿el sistema debe generarla imprimible (como el oficio de rutas)?
148. ❓ ¿La evaluación de pasantes usa una escala numérica (1-20, 1-10, 0-5) o es cualitativa (Excelente, Bueno, Regular)? ¿Hay evaluaciones parciales o solo una nota final?
149. ❓ ¿Los pasantes deben marcar asistencia diaria en el sistema, o su control de asistencia es independiente?

### Oficios — Correlativo único vs por módulo

150. ❓ ¿El correlativo de oficios es único para toda la institución (un solo contador compartido entre rutas, talleres y cualquier otro oficio) o cada módulo tiene su propio correlativo independiente?
151. ❓ ¿Los talleres externos que IMATUR organiza para escuelas/liceos generan un **oficio de convocatoria** emitido por IMATUR? ¿Ese oficio debe generarse desde el sistema?

### Visitantes — Categorías y reportes

152. ❓ ¿El motivo de visita debe ser texto libre o una lista de categorías? (Reunión de trabajo, Trámite administrativo, Entrega de documentos, Visita institucional, Otro)
153. ❓ ¿El módulo de Visitantes debe generar un reporte mensual de visitas recibidas para informes institucionales? ¿Qué datos incluiría?
154. ❓ ¿Hay visitantes extranjeros frecuentes (embajadores, delegaciones)? ¿Se necesita un campo para tipo de documento (CI / Pasaporte)?

### Reportes — Formato y periodicidad

155. ❓ ¿El "informe de gestión trimestral" que se entrega a la Alcaldía tiene un formato estándar imprimible? ¿Incluye todas las secciones: RRHH, Formación, Rutas, Inventario?
156. ❓ ¿El Dashboard actual (KPIs con gráficas) es suficiente para la dirección, o se necesita un "Informe Ejecutivo" descargable en PDF con los indicadores principales?
157. ❓ ¿Cuáles son los 5 indicadores más importantes que el Director revisa regularmente? (para priorizar el Dashboard)

### Sistema — Roles adicionales

158. ❓ ¿El Director necesita un acceso especial al sistema? Opciones: (a) usa rol Administrador, (b) se crea un rol "Dirección" con acceso de solo lectura a todo + reportes, (c) no usa el sistema directamente.
159. ❓ ¿Existe personal de recepción que solo deba registrar visitas y visitantes, sin acceso a otros módulos? Si es así, se requiere un nuevo **rol 5 — Recepción**.
160. ❓ ¿Hay alguna funcionalidad del sistema que deba ser accesible al público en general (sin login), como consulta de rutas disponibles o formulario de inscripción para talleres?

---

## PRIORIDADES PARA ACLARACIÓN

### 🔴 Urgente — Bloquea diseño de BD o lógica central

| # | Pregunta | Módulo | Impacto |
|---|----------|--------|---------|
| 90 | Correlativo de oficios: único para toda la institución o por módulo | Oficios | BD |
| 140 | Rutas: ¿un registro por ejecución o reutilizar con fechas? | Rutas | Diseño |
| 150 | Correlativo de oficios: ¿único o por módulo? (duplicado crítico) | Oficios | BD |
| 132 | Turnos/horarios definidos formalmente | RRHH | BD |
| 158 | Rol para el Director | Sistema | RBAC |
| 159 | ¿Existe personal de recepción que necesite rol propio? | Sistema | RBAC |

### 🟡 Importante — Funcionalidades pendientes de alto impacto

| # | Pregunta | Módulo |
|---|----------|--------|
| 147 | ¿El sistema genera carta de culminación imprimible? | Pasantes |
| 155 | Formato del informe trimestral para la Alcaldía | Reportes |
| 142 | Formato imprimible del formato de asistencia de TH | Formación |
| 136 | Baja de bienes: ¿requiere acto administrativo imprimible? | Inventario |
| 139 | Pago de Cumaná Histórica: ¿quién cobra, cómo? | Rutas |
| 145 | ¿El informe demográfico es obligatorio para Finalizar taller? | Formación |

### 🟢 Verificación — Confirmar lo ya implementado

| # | Pregunta | Módulo |
|---|----------|--------|
| 118 | RIF institucional vigente | Config |
| 119 | Dirección física actual | Config |
| 120 | Teléfonos institucionales | Config |
| 128-131 | Volumen de datos (empleados, bienes, talleres, rutas) | Migración |
