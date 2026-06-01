# Preguntas Pendientes de Modelo de Negocio — SIGTUR-IMATUR

**Última actualización:** 2026-06-01  
**Propósito:** Listado LIMPIO de preguntas sin respuesta que condicionan el desarrollo. Las preguntas respondidas viven en `DECISIONES_PENDIENTES.md`.

**Leyenda:**
- ❓ **Sin respuesta** — bloquea o condiciona implementación
- ⚠️ **Respuesta parcial** — pendiente de confirmación final

---

## 🔴 CRÍTICAS — Bloquean diseño de BD o lógica central

| ID | Módulo | Pregunta | Desbloquea |
|----|--------|----------|------------|
| D-RH01 | RRHH | ¿Cuántos turnos existen? ¿Cómo se llaman (Matutino/Vespertino)? | Datos semilla `horarios` + HorariosController |
| D-RH02 | RRHH | ¿Qué tipos de permisos laborales existen? | Enum en `permisos_laborales.tipo_permiso` |
| D-RH03 | RRHH | ¿Los permisos requieren aprobación de superior o RRHH los registra directo? | Flujo `estado` en PermisosLaboralesController |
| D-RH04 | RRHH | ¿Vacaciones en días hábiles o calendario? ¿Cuántos días/año de servicio? | Lógica de cálculo VacacionesController |
| D-RH05 | RRHH | ¿Saldo vacaciones se calcula automático o RRHH lo gestiona manual? | Diseño de VacacionesController |
| D-IN06 | Inventario | ¿Existe "responsable del bien" nominal? ¿Un bien puede asignarse a >1 empleado? | FK `id_responsable` o tabla de asignación múltiple |

---

## 🟡 IMPORTANTES — Funcionalidades de alto impacto

| ID | Módulo | Pregunta | Desbloquea |
|----|--------|----------|------------|
| D-RT02 | Rutas | ⚠️ Tarifa Cumaná Histórica: ¿fija por persona, por grupo, por tipo? ¿Quién cobra? | Módulo de pagos `participantes_ruta` |
| D-RH06 | RRHH | ¿Vacaciones no disfrutadas se acumulan al año siguiente o se pierden? | Lógica de arrastre en cierre de año |
| D-RH07 | RRHH | ¿Contratos con fecha de vencimiento deben generar alerta en Dashboard? | Alerta contratos próximos a vencer |
| D-RH08 | RRHH | ¿Horario fijo para todos o turnos diferentes por departamento? | Asignación de horario a empleado |
| D-RH09 | RRHH | ¿El sistema debe calcular puntualidad/ausentismo? ¿Se usa para nómina? | Columnas calculadas en reporte asistencia |
| D-RH12 | RRHH | ¿Se manejan horas extras? ¿Deben registrarse en el sistema? | Módulo de horas extra o campo en asistencias |
| D-RH13 | RRHH | ¿Ausencias justificadas e injustificadas se gestionan por separado? ¿Quién las aprueba? | Tipo de ausencia en `asistencias` o `permisos_laborales` |
| D-RH14 | RRHH | ¿Existe "bono vacacional"? ¿El sistema debe calcularlo o solo registrarlo? | Campo en `vacaciones` o cálculo de nómina |
| D-FO05 | Formación | ¿La planificación semanal de Formación debe registrarse en el sistema? | Módulo de planificación + comparativo vs ejecutado |
| D-IN09 | Inventario | ¿El sistema debe registrar costo de adquisición, fecha de compra y proveedor? | Campos adicionales en tabla `inventario` |
| D-TX01 | Transversal | ⚠️ ¿Qué otras operaciones requieren aprobación formal de un superior? | Flujos de aprobación adicionales |
| ~~D-US03~~ | Sistema | ✅ **RESUELTA (2026-06-01):** Rol 6 renombrado a "Solo Lectura". Permisos: Dashboard, Reportes, Visitantes. Implementado en BD (INSERT permisos_rol). | — |
| ~~D-US05~~ | Sistema | ✅ **RESUELTA (2026-06-01):** Contraseña default = cédula del empleado (auto-rellena en el formulario). Módulo "Mi Perfil" (`/perfil/index`) implementado: el usuario puede cambiar username y contraseña (mín. 6 chars). Accesible para todos los roles (exento de RBAC en Router.php). | — |

---

## 🟢 MENORES — Mejoras y detalles operativos

| ID | Módulo | Pregunta | Desbloquea |
|----|--------|----------|------------|
| D-IN03 | Inventario | ¿Las categorías actuales reflejan la clasificación oficial de IMATUR? | Ajuste categorías en BD |
| D-IN05 | Inventario | ¿Bienes fungibles (papel, tóner) y durables (equipos) se manejan diferente? | Campo `tipo_bien` + lógica diferenciada |
| D-RH10 | RRHH | ¿Hay personal sin ser empleado formal (voluntarios, servicio comunitario)? | Tipo contrato adicional o tabla separada |
| D-RH11 | RRHH | ¿El `sueldo_base` es igual para todos en el mismo cargo o puede variar por empleado? | Mover `sueldo_base` de `cargos` a `empleados` |
| D-TX03 | Transversal | ⚠️ **CONFIRMADO (2026-06-01):** Sí hay antecedentes históricos en Excel y papel a migrar. El formato exacto depende del módulo (visitas, bienes, etc.) — se define módulo a módulo cuando se aborde cada importación. Pendiente: definir qué módulos tienen datos históricos y obtener los archivos fuente para diseñar los scripts de importación. | Scripts de importación CSV por módulo |
| D-NEW01 | Formación | ¿El correlativo de oficios de formación se activa en UI o solo infraestructura preparada? | Activar correlativo FORM-XXX en vista de talleres |
| D-NEW02 | Turismo | ¿`instituciones_externas` necesita un CRUD dedicado o solo se usa como lookup al registrar participantes? | InstitucionesExternasController + vista |
| D-NEW03 | Turismo | ¿El facilitador externo (`nombre_facilitador_externo`) debe ser lista gestionada o texto libre cada vez? | Tabla de guías externos registrados |
| D-NEW04 | RRHH | ¿Los tipos de permisos son los estándar venezolanos (Médico/Personal/Duelo/Maternidad/Sindical/Estudio) u otros? | Enum final en `permisos_laborales.tipo_permiso` |
| D-NEW05 | RRHH | ¿Los días de vacaciones según LOTTT (15 días + 1 día/año adicional) aplican a IMATUR como ente municipal? | Fórmula en VacacionesController |

---

## 🔎 NUEVAS — Auditoría de ingeniería 2026-05-31

Derivadas de `docs/AUDITORIA_SENIOR_2026-05-31.md`. Cada una desbloquea cerrar un hallazgo (H-xx).

| ID | Módulo | Pregunta | Desbloquea |
|----|--------|----------|------------|
| ~~D-UB01~~ | Inventario | ✅ **RESPONDIDA (2026-05-31):** sí, la ubicación pertenece a un departamento (obligatorio). Implementado: select en UI + `id_departamento` en modelo/controlador. H-01 cerrado. | — |
| D-IN10 | Inventario | ❓ ¿Registrar un movimiento **Baja**/**Mantenimiento** debe cambiar automáticamente la `condicion` del bien (p. ej. a "Dañado")? Hoy no lo hace (H-04). | Sincronizar `actividad_inventario` → `inventario.condicion` |
| ~~D-IN11~~ | Inventario | ✅ **RESUELTA (2026-05-31, H-05):** validación de unicidad implementada en `InventarioController` con mensajes precisos (`Inventario::findByCodigoBn/findBySerial`). | — |
| D-FO06 | Formación | ❓ ¿Se gestionan los **oficios base** (tabla `oficios`) con CRUD y se vinculan al taller (`talleres.id_oficio`)? ¿Para qué (solicitud de sede, autorización)? | OficiosController + flujo oficio→taller (H-09/H-10) |
| D-FO07 | Formación | ❓ ¿La tabla `taller_inventario` (materiales prestados al taller) se va a usar? ¿Es obligatorio? ¿Se controla devolución? | UI de materiales por taller |
| D-FO08 | Formación | ❓ ¿Qué significa `es_brigadista` en un participante? ¿Implica rol/beneficio? Hoy el campo no se usa. | Definir uso o eliminar campo |
| D-FO09 | Formación | ❓ ¿Cuándo se marca la **asistencia** (durante o después del taller)? ¿Se permite marcar tras "Finalizado"? | Reglas de asistencia + máquina de estados |
| D-RT03 | Turismo | ❓ Al pasar una ruta a **Finalizada**, ¿debe generarse informe y/o oficio **automáticamente**? Hoy todo es manual. | Cierre de ruta automatizado |
| ~~D-RE03~~ | Recepción | ✅ **RESUELTA (2026-06-01):** Visitas = bitácora inmutable sin hora de salida. El toggle de registro solo considera visitas abiertas del **día actual** (fix en `Visita::registrar()`): visitas de días anteriores no bloquean nuevas entradas. Empleado que recibe: NO obligatorio. | — |
| ~~D-RE04~~ | Recepción | ✅ **RESUELTA (2026-06-01):** Motivos de visita quedan fijos en el código por ahora. | — |
| ~~D-US06~~ | Sistema | ✅ **RESUELTA (2026-06-01):** Mínimo 6 caracteres, sin caducidad, sin complejidad obligatoria. Implementado en servidor (UsuariosController + PerfilController) y en UI (minlength + validación JS). | — |
| ~~D-TX04~~ | Transversal | ✅ **RESUELTA (2026-06-01):** NO se renombran las columnas `create_at/update_at/delete_at` de `parroquia`. Bajo impacto, no justifica migración de esquema. El quirk queda documentado en CLAUDE.md. | — |
| ~~D-RH15~~ | RRHH | ✅ **RESUELTA (2026-05-31, H-11):** decisión — solo M/F. Migración 023 actualiza CHECK en 4 tablas. Opción "Otro" eliminada de todos los formularios. | — |

---

## Notas de uso

- Este documento es la fuente única de verdad para preguntas SIN responder.
- Cuando una pregunta se responda: moverla a `DECISIONES_PENDIENTES.md` con su respuesta e impacto técnico, luego eliminarla de aquí.
- El estado ⚠️ significa que hay una respuesta aproximada pero falta confirmación para implementar.
