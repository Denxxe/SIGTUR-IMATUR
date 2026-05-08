# Módulo de Formación — Reglas de Negocio

## Contexto institucional

IMATUR imparte actividades de formación denominadas genéricamente **Inducciones, Charlas y Talleres** (usadas como sinónimos según el contexto). Las actividades se dividen en dos grandes categorías: **internas** y **externas**.

---

## RN-F01 — Tipos de actividad

Los tipos de actividad válidos son:
- **Taller** — sesión práctica extendida
- **Charla / Conversatorio** — sesión informativa o de sensibilización
- **Inducción** — bienvenida/integración de personal

---

## RN-F02 — Actividades Internas

- Son impartidas **por la institución (IMATUR) para su propio personal**.
- Al momento de realizarse, **toda la institución debe estar presente**.
- El formato de asistencia es emitido por **Talento Humano**.
- No generan oficio de solicitud externo (se gestionan internamente).
- Campo en BD: `talleres.es_interna = TRUE`.

---

## RN-F03 — Actividades Externas

- Son impartidas **a entidades externas** (escuelas, liceos, comunas, prestadores de servicio).
- Organizadas a través de **Zona Educativa** u otras instituciones.
- Zona Educativa selecciona los colegios/instituciones y la cantidad de participantes.
- Generan un **oficio recibido** (número, fecha, asunto) como soporte documental.
- Campo en BD: `talleres.es_interna = FALSE` + `talleres.tipo_ente` con el tipo de entidad.

---

## RN-F04 — Tipo de entidad destinataria (`tipo_ente`)

Para actividades externas, el campo `tipo_ente` indica a quién va dirigida la formación:
- `'Escuela'` — educación primaria
- `'Liceo'` — educación secundaria
- `'Comunidad'` — comunas o consejos comunales
- `'Prestador de Servicio'` — hoteles, restaurantes, kioscos y negocios turísticos
- `'IMATUR'` — cuando se capacita a personal de la propia institución (actividad interna)

Cuando `es_interna = TRUE`, el campo `tipo_ente` se deja `NULL` (IMATUR es implícito).

---

## RN-F05 — Oficio para actividades externas

Toda actividad externa **nueva** debe registrar los datos del oficio recibido:
- Número de oficio (opcional si no se tiene al momento)
- **Fecha del oficio** (obligatoria)
- Asunto / motivo de la solicitud

No aplica a actividades internas ni a la edición de registros existentes.

---

## RN-F06 — Participantes con cédula vs. sin cédula

- Los participantes adultos deben estar **registrados en el sistema** (tabla `personas`).
- Los niños y niñas **pueden inscribirse sin cédula** mediante el modo "participante libre":
  - Nombre y apellido obligatorios
  - N° de ID escolar opcional
- Edad mínima de participación: **9 años** (criterio de la institución).

---

## RN-F07 — Brigadistas

- Un mismo participante **puede inscribirse en múltiples talleres**.
- Los brigadistas son integrantes frecuentes provenientes de las instituciones externas.
- Al inscribir un participante con cédula, se puede marcar la bandera `es_brigadista = TRUE`.
- El sistema **no bloquea** la inscripción múltiple; queda registrada para estadísticas.

---

## RN-F08 — Docente acompañante (niños/as)

- Cuando se inscribe un niño/a (participante libre) en una actividad externa con escuelas/liceos,
  se debe registrar el **nombre y cédula del docente** que acompaña al grupo.
- Campos: `nombre_docente`, `cedula_docente` en `participantes_taller`.
- Estos datos son parte del formato de asistencia para actividades externas.

---

## RN-F09 — Múltiples facilitadores

- Actualmente el sistema soporta **un facilitador principal** por actividad (`id_facilitador`).
- Para actividades internas, el facilitador puede ser una persona externa al Departamento de Formación.
- **Pendiente (v3.0):** soporte para múltiples facilitadores mediante tabla `taller_facilitadores`.

---

## RN-F10 — Informes trimestrales

- El resumen de actividades se genera **trimestralmente** (normalmente).
- El informe incluye: metas, logros, planificación semanal (internas y externas).
- Ya existe `taller_informes` para el resumen por actividad individual.
- **Pendiente:** informe agregado trimestral.

---

## RN-F11 — Pasantes

- El módulo recibe cartas de pasantes, genera evaluaciones y asigna tutores.
- Se realiza seguimiento de actividades, estructura de proyecto e informe final.
- El proceso pasa por Formación → revisión → Talento Humano → Dirección (firma y sello).

---

## RN-F12 — Prerequisito de formación para rutas (Exploradores de Cumaná)

- La ruta **"Exploradores de Cumaná"** y otras marcadas con `rutas.requiere_formacion = TRUE`
  requieren que el participante haya **asistido** a al menos una actividad de formación previamente.
- El sistema valida esto al inscribir: busca un registro en `participantes_taller`
  con `asistio = TRUE` para ese `id_persona`.
- Participantes libres (niños/as sin cédula) están **exentos** de este prerequisito.

---

## RN-F13 — Máquina de estados (ya implementado)

```
Programado → En Curso → Finalizado
           ↘          ↘
             Cancelado   Cancelado
```
- Un taller Finalizado no puede cambiar de estado.
- Un taller Cancelado no puede cambiar de estado.
- Solo se puede Finalizar si tiene al menos un participante (RN-F12 previo).

---

## Entidades DB relacionadas

| Tabla | Campos nuevos (migración 006) |
|-------|-------------------------------|
| `talleres` | `es_interna BOOLEAN`, `tipo_ente VARCHAR(50)` |
| `participantes_taller` | `es_brigadista BOOLEAN`, `nombre_docente VARCHAR(100)`, `cedula_docente VARCHAR(20)` |
| `rutas` | `requiere_formacion BOOLEAN` |
