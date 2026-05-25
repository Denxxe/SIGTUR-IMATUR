# Módulo de RRHH — Reglas de Negocio

**Última actualización:** 2026-05-22

## Contexto institucional

IMATUR tiene una estructura con cuatro Direcciones bajo la Dirección General. El módulo de RRHH está a cargo de la **Dirección de Talento Humano**. Los datos de empleados sirven como base para los módulos de Formación (facilitadores), Rutas (guías), y acceso al sistema.

---

## RN-RH01 — Empleado siempre vinculado a Persona

Todo empleado debe existir previamente en la tabla `personas`. La creación de un empleado es atómica: INSERT en `personas` → INSERT en `empleados` (mismo `beginTransaction`). No puede haber empleado sin persona.

---

## RN-RH02 — Cargo y Departamento obligatorios

Un empleado debe tener un cargo y un departamento asignado. Los cargos tienen un `sueldo_base` de referencia. Un mismo cargo puede existir en distintos departamentos.

---

## RN-RH03 — Tipos de contrato

Valores válidos: `'Fijo'`, `'Contratado'`, `'Suplente'`, `'Comisión de Servicio'`. DEFAULT: `'Fijo'`.

- Los empleados **Contratados** pueden tener `fecha_egreso` programada.
- Empleados dados de baja: `fecha_egreso` registrada + `is_active = FALSE` en `empleados`.
- **Baja** no elimina al empleado ni a su persona; preserva el historial.

---

## RN-RH04 — Asistencia: patrón toggle

La asistencia sigue el mismo patrón que las visitas:
- Si existe un registro abierto del día (sin `hora_salida`) → UPDATE `hora_salida = NOW()`.
- Si no existe → INSERT nuevo con `hora_entrada = NOW()`.
- Un empleado solo puede tener una asistencia abierta por día.

El marcaje registra el usuario que realizó la acción mediante `$this->getUserId()`. ✅ Bug BRH-04 corregido en Fase 2.5.

---

## RN-RH05 — Asistencia manual

Además del marcaje automático, el sistema permite registro manual de asistencias (retroactivo). Esto cubre casos de fallas del sistema o ausencias documentadas.

---

## RN-RH06 — Permisos Laborales (tablas listas, UI pendiente)

La tabla `permisos_laborales` existe desde migración 002. Tipos de permiso a confirmar con la institución (ver D-RH02, D-NEW04). El flujo de aprobación no está implementado (ver D-RH03).

**Estado actual:** Tablas creadas, sin controlador ni vista dedicada. Pendiente Fase 3.

---

## RN-RH07 — Vacaciones (tablas listas, lógica pendiente)

La tabla `vacaciones` existe desde migración 002. La lógica de cálculo de días disponibles no está implementada.

**Estado actual:** Tabla creada, sin controlador ni vista. Ver D-RH04, D-RH05, D-RH06, D-NEW05.

---

## RN-RH08 — Horarios (tabla lista, sin UI)

La tabla `horarios` existe desde migración 002. Cada empleado tiene un `id_horario` FK. Los turnos no están configurados en el sistema.

**Estado actual:** Pendiente definir con institución (D-RH01, D-RH08). Fase 3.

---

## RN-RH09 — Empleado con usuario del sistema

Un empleado puede o no tener usuario en el sistema. La tabla `usuarios` tiene FK opcional a `empleados`. La desactivación del usuario (`is_active = FALSE`) es independiente del estado del empleado.

---

## Estado de brechas

| ID | Descripción | Estado |
|----|-------------|--------|
| BRH-01 | UI para Permisos Laborales | ❓ Pendiente Fase 3 — requiere D-RH02/D-RH03 |
| BRH-02 | UI para Vacaciones con cálculo de saldo | ❓ Pendiente Fase 3 — requiere D-RH04/D-RH05 |
| BRH-03 | UI para Horarios (asignar turnos) | ❓ Pendiente Fase 3 — requiere D-RH01 |
| BRH-04 | `marcar()` usaba `$user_id = 1` hardcodeado | ✅ Corregido en Fase 2.5 |
| BRH-05 | Alertas por contratos próximos a vencer | ✅ Implementado en Dashboard (30 días) |
| BRH-06 | Reporte de permisos por tipo/empleado/período | ❓ Pendiente — requiere BRH-01 |
| BRH-07 | Reporte de saldo de vacaciones por empleado | ❓ Pendiente — requiere BRH-02 |
