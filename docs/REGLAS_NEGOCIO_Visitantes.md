# Módulo de Visitantes y Control de Visitas — Reglas de Negocio

**Última actualización:** 2026-05-22

## Contexto institucional

El control de visitantes registra a personas **externas que ingresan físicamente a las instalaciones de IMATUR** (no a turistas de rutas, que se registran en el módulo de Rutas). Su propósito es control de acceso institucional y estadística de visitas recibidas.

---

## RN-VIS01 — Visitante vs Participante de Ruta

- **Visitante** = persona externa que viene a IMATUR (reunión, trámite, entrega de documentos, etc.).
- **Participante de Ruta** = persona que se une a un recorrido turístico guiado.
- Son entidades separadas con tablas distintas (`visitantes` / `participantes_ruta`).
- Una misma persona física puede estar registrada en ambos módulos.

---

## RN-VIS02 — Registro de visitante (perfil)

El visitante tiene: cédula, nombre, apellido, procedencia/institución, teléfono, género, correo, motivo frecuente.  

El campo `procedencia` representa la **institución que representa** el visitante, no su ciudad de origen (D-VIS02 respondida). El label en UI es "Institución / Procedencia".

---

## RN-VIS03 — Control de visita: patrón toggle

Cada entrada/salida se registra en la tabla `visitas`:
- Si el visitante tiene una visita activa (sin `hora_salida`) → se registra la salida (UPDATE `hora_salida = NOW()`).
- Si no hay visita activa → se registra la entrada (INSERT).
- Un visitante puede tener múltiples visitas el mismo día (entra y sale varias veces).

---

## RN-VIS04 — Empleado visitado

Cada visita puede vincularse al empleado que atendió al visitante (`id_empleado FK`). Este campo es **opcional** en el formulario actual.

---

## RN-VIS05 — Motivo de visita

El campo `motivo` en la tabla `visitas` es una lista predefinida (D-VIS01 respondida):
- `'Reunión de trabajo'`
- `'Trámite administrativo'`
- `'Entrega de documentos'`
- `'Visita institucional'`
- `'Pasantías'`
- `'Otro'`

El formulario usa `<select>` con CHECK constraint en BD. Whitelist actualizada en `VisitasController`.

---

## RN-VIS06 — Solo visitantes venezolanos

IMATUR no recibe visitantes extranjeros frecuentes. El campo `cedula` asume documentos venezolanos. No existe campo `tipo_documento` (D-VIS03 respondida).

---

## RN-VIS07 — Reportes de visitas

Reporte implementado con filtros por: período (fechas), institución/procedencia, motivo de visita, empleado atendido (D-VIS04 respondida).

---

## Estado de brechas

| ID | Descripción | Estado |
|----|-------------|--------|
| BVIS-01 | Reporte de visitantes por período/procedencia/motivo | ✅ Resuelto — filtros completos implementados |
| BVIS-02 | Campo `tipo_documento` para extranjeros | ✅ Resuelto — D-VIS03: no aplica, solo venezolanos |
| BVIS-03 | Categorías de motivo de visita | ✅ Resuelto — lista de 6 categorías predefinidas |
| BVIS-04 | Indicador "visitas activas del día" en Dashboard | ❓ Pendiente — bajo impacto |
| BVIS-05 | Estadísticas de visitas en módulo de Reportes | ❓ Pendiente — datos existen, falta integrarlo |
