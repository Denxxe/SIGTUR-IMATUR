# Módulo de Visitantes y Control de Visitas — Reglas de Negocio

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

El visitante tiene: cédula, nombre, apellido, procedencia, teléfono, género, correo, motivo frecuente.  
El campo `motivo_frecuente` es un **perfil del visitante** (dato estático), no el motivo de cada visita concreta.

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

El campo `motivo` en la tabla `visitas` es texto libre (por visita específica, no por perfil).  
**Pendiente confirmar:** si debe ser lista categorizada o libre (ver pregunta 151).

---

## RN-VIS06 — Visitantes extranjeros

El campo `cedula` actualmente asume documentos venezolanos. No hay campo `tipo_documento` (CI / Pasaporte / RIF).  
**Pendiente:** confirmar si se reciben visitantes extranjeros frecuentemente (ver pregunta 83).

---

## RN-VIS07 — Reportes de visitas

No hay reporte dedicado de visitas implementado. Los datos existen en BD. Los reportes actuales en `ReportesController` no incluyen el módulo de visitas.  
**Pendiente:** reporte de visitantes por mes/procedencia/motivo.

---

## Brechas identificadas (pendientes de implementación)

| ID | Descripción | Impacto |
|----|-------------|---------|
| BVIS-01 | Reporte de visitantes por período/procedencia/motivo | Medio |
| BVIS-02 | Campo `tipo_documento` para visitantes extranjeros | Bajo |
| BVIS-03 | Categorías de motivo de visita (lista vs texto libre) | Bajo |
| BVIS-04 | Indicador "visitas activas del día" en Dashboard | Bajo |
| BVIS-05 | Conexión estadística entre visitas y módulo de Reportes | Medio |
