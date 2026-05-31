# Análisis de Módulos — Formación y Turismo

**Fecha:** 2026-05-31
**Alcance:** estado funcional, reglas de negocio, validaciones, reportes/indicadores y backlog de los módulos de **Formación** (Talleres/Charlas/Inducciones, Pasantes, Sedes) y **Turismo** (Rutas Turísticas).
**Veredicto general:** ambos módulos están **funcionalmente completos** para operación. El backlog restante es técnico/menor, no bloqueante.

---

## A. MÓDULO DE FORMACIÓN

### A.1 Componentes

| Submódulo | Controlador | Tablas | Estado |
|-----------|-------------|--------|--------|
| Talleres / Charlas / Inducciones | `TalleresController` | `talleres`, `participantes_taller`, `taller_informes`, `taller_evidencias`, `taller_inventario` | ✅ Completo |
| Sedes de formación | `UbicacionesformacionController` | `ubicaciones_formacion` | ✅ Completo |
| Pasantes | `PasantesController` | `pasantes`, `pasante_documentos` | ✅ Completo |

### A.2 Funcionalidades implementadas

- **CRUD de actividades** con tipo (`Taller`/`Charla`/`Inducción`), facilitador (empleado), sede, cupo, fechas y estado.
- **Máquina de estados** `Programado → En Curso → Finalizado` (+ `Cancelado`). Transición automática `Programado→En Curso` por fecha; sin reversa desde `En Curso`. Terminales: `Finalizado`, `Cancelado`.
- **Participantes** con doble modalidad:
  - **Con cédula**: reutiliza/crea persona (`Taller::crearPersona`/`actualizarPersona`), datos demográficos completos, flag `es_brigadista`, distinción interna/externa.
  - **Libre (niños/as 5-11 sin cédula)**: `nombre_libre`/`apellido_libre`, género y fecha de nacimiento, datos de docente (`nombre_docente`/`cedula_docente`).
- **Informe demográfico** por actividad (`taller_informes`): mujeres/hombres/niñas/niños; `total_atendidas` derivado.
- **Evidencias** (`taller_evidencias`, migración 010) y **motivo de cancelación**.
- **Oficios**: actividades externas con sede no propia pueden vincular oficio recibido; las **internas no requieren oficio**.
- **Exportaciones**: CSV (13 columnas con demografía) y PDF (con KPIs), dossier integral y lista de participantes.

### A.3 Reglas de negocio clave

| ID | Regla |
|----|-------|
| RN-F06 | Actividad interna (`es_interna=TRUE`) → no requiere oficio aunque la sede sea externa; `tipo_ente = NULL`. |
| RN-F12 | No se puede **Finalizar** una actividad sin participantes inscritos (validado en alta y en edición). |
| RN-F13 | Estados terminales no admiten cambios; transición controlada por `validarTransicion()`. |
| RN-F16 | Niños/as sin cédula: edad 5-11 (validada por `fecha_nac_libre`), docente opcional. |
| — | Cupo es **estimación no bloqueante**: al exceder solo advierte (warning), no impide inscribir. |
| — | El contador de ocupación excluye participantes inactivos (soft-delete). |

### A.4 Validaciones

- **Servidor**: cédula `/^[VEJGCP]?\d{6,9}$/`, correo `FILTER_VALIDATE_EMAIL`, fecha de nacimiento pasada con edad 5-11 para libres, duración 10 min–5 h, whitelist de `tipo_actividad` y `estado`.
- **UI**: formatos de cédula/nombre/teléfono (`sigtur-validations.js`), submit deshabilitado hasta cumplir requisitos, validación de evidencias en el modal de edición.

### A.5 Indicadores asociados (ver `INDICADORES_GESTION.md`)

F-2 tipo de entidad, F-3 demografía, F-4 cobertura territorial, F-5 capacitadores, F-META meta anual, PROP-F01 ocupación, PROP-F02 finalización, PROP-F05 cancelación, + derivados (participantes/actividad, formados/capacitador).

### A.6 Backlog Formación

| Ítem | Prioridad |
|------|-----------|
| Reportes de permisos laborales y saldo de vacaciones (RRHH, fuera de Formación) | Baja |
| Informe trimestral consolidado de Formación (metas/logros) | Media |

---

## B. MÓDULO DE TURISMO (RUTAS)

### B.1 Componentes

| Submódulo | Controlador | Tablas | Estado |
|-----------|-------------|--------|--------|
| Rutas turísticas | `RutasController` | `rutas`, `puntos_ruta`, `participantes_ruta`, `ruta_informes` | ✅ Completo |
| Oficios emitidos | (en Rutas) | `oficios_emitidos` | ✅ Completo |

> **Submódulos eliminados** (2026-05-31): asignación de bienes a rutas (`ruta_inventario`, migración 019), actividades de ruta (`actividades_ruta`), e **Instituciones externas** del flujo de turismo. Las tablas quedan inertes en BD.

### B.2 Funcionalidades implementadas

- **CRUD de rutas** con tipo de ruta, departamento, facilitador (empleado o externo), fecha de visita, hora, duración (`H:MM`), tarifa opcional, prerequisito de formación.
- **Máquina de estados** `Activa / Inactiva / En Mantenimiento / Finalizada`. `Finalizada` es **terminal** (migración 020) — no admite cambios posteriores. Mantenimiento exige `motivo_mantenimiento`.
- **Puntos de ruta** (paradas) con orden único por ruta (migración 016), latitud/longitud y **mapa Leaflet offline** (pin SVG); timeline con detección robusta de órdenes duplicados.
- **Participantes** alineados con Formación (migración 017): con cédula (reutiliza personas) o libres (niños/as 5-11), institución opcional.
- **Asistencia post-visita**: individual y masiva, con UI táctil responsive.
- **Informe post-visita** manual (`ruta_informes`, migración 018) con demografía y sugerencia "Aplicar".
- **Oficios emitidos** con correlativo por módulo (`RUTA-NNN/AAAA`), historial y aviso de duplicados; vista previa en vivo.
- **Exportaciones**: CSV (15 columnas + totales) y PDF (con finalizadas y demografía consolidada por tipo).

### B.3 Reglas de negocio clave

| ID | Regla |
|----|-------|
| RN-RT (RN-F12 turismo) | Rutas con `requiere_formacion=TRUE` exigen que el participante con cédula haya asistido a ≥1 formación; libres exentos; el operador puede **forzar** la inscripción. |
| Finalizar ruta | Pasar a `Finalizada` requiere ≥1 participante inscrito; no se puede crear una ruta directamente como `Finalizada`. |
| Evento único | Cada registro de ruta es una **ejecución independiente**: dos recorridos del mismo lugar en fechas distintas = dos rutas finalizadas para reportes/indicadores. |
| Cupo | Estimación no bloqueante (warning), igual que Formación. |

### B.4 Validaciones

- **Servidor**: nombre min 3, `fecha_visita ≥ hoy`, `hora_visita` regex `HH:MM`, `duracion_estimada` `H:MM`, `motivo_mantenimiento` obligatorio en mantenimiento, orden de parada único, lat `[-90,90]`/lng `[-180,180]`, cédula venezolana, `cedula_libre` alfanumérica, correo válido.
- **UI**: submit deshabilitado hasta datos válidos, `pattern`/`min=hoy`, validación de coordenadas y orden antes de guardar punto.

### B.5 Indicadores asociados (ver `INDICADORES_GESTION.md`)

T-1 meta anual de rutas (finalizadas por `fecha_visita`), T-2 participantes por tipo de ruta, T-DEMO demografía de participantes, demografía consolidada por tipo en el reporte de rutas.

### B.6 Cambios recientes relevantes (2026-05-31)

1. **Fix crítico de inscripción**: `Ruta::buscarPersonaPorCedula()` consultaba sobre el JOIN a empleados y devolvía un id incorrecto → la inscripción fallaba en silencio. Reescrito para consultar `personas` directamente.
2. **`nivel_dificultad` eliminado** (migración 021): IMATUR no usa esa distinción; removido de modelo, controlador, vistas y reportes; en su lugar se muestra el badge de `tipo_ruta`.
3. **Estado `Finalizada`** con validación de participantes.
4. **Instituciones externas** retiradas del módulo de turismo (controlador/vista/sidebar/RBAC/indicador T-3).

### B.7 Backlog Turismo

| Ítem | Prioridad |
|------|-----------|
| Normalización de `duracion_estimada` histórica a `H:MM` | Baja |
| Flujo de cobro de rutas con tarifa (no se registran pagos actualmente) | A confirmar con negocio |
| Regla de bienes internos en rutas (eliminada hasta definir) | A confirmar con negocio |

---

## C. Conclusión

| Módulo | Cobertura funcional | Reglas | Validaciones | Reportes/Indicadores |
|--------|--------------------|--------|--------------|----------------------|
| Formación | ✅ Completa | ✅ Implementadas | ✅ Servidor + UI | ✅ 8 indicadores + derivados |
| Turismo | ✅ Completa | ✅ Implementadas | ✅ Servidor + UI | ✅ 3 indicadores + consolidado |

Ambos módulos son **operables en producción**. Los pendientes son refinamientos (cobro de rutas, bienes internos) que requieren **decisión de negocio**, no desarrollo bloqueante.
