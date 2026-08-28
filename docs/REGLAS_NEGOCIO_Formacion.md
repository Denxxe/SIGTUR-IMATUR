# Módulo de Formación — Reglas de Negocio

**Última actualización:** 2026-08-28 · **Migraciones:** hasta 073
**Pendientes y preguntas abiertas:** `docs/BACKLOG.md` §3.6.

> **Revisión del 2026-08-28.** Se corrigió la tabla de entidades, que seguía listando
> `participantes_taller.es_brigadista` y `taller_inventario`, **eliminados en la migración 050**
> por no usarse. El módulo está completo salvo una decisión opcional del cliente (D-NEW01).

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

- El sistema soporta **un facilitador principal** por actividad (`id_facilitador`).
- Para actividades internas, el facilitador puede ser una persona externa al Departamento de Formación.
- 🔒 **Solo si el cliente lo pide** (D-FO08-bis / pregunta **D3**): varios facilitadores por actividad,
  mediante una tabla `taller_facilitadores`. No se construye a priori.

---

## RN-F10 — Informes trimestrales

- El resumen de actividades se genera **trimestralmente** (normalmente).
- El informe incluye: metas, logros, planificación semanal (internas y externas).
- `taller_informes` cubre el resumen por actividad individual, con su vista imprimible
  (`talleres/informe_imprimible.php`) y export CSV.
- **Metas ✅:** `meta_talleres_anio` y `meta_rutas_anio` (Configuración) alimentan el indicador
  *planificado vs. ejecutado* de `/reportes/indicadores`. Hoy valen **100 cada una, de relleno**:
  🔒 falta que el cliente dé las metas reales (pregunta **D4**) para que el indicador diga algo.
- **Pendiente menor:** informe agregado trimestral (hoy se arma sumando los individuales).

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
| `participantes_taller` | `nombre_docente VARCHAR(100)`, `cedula_docente VARCHAR(20)` |
| `rutas` | `requiere_formacion BOOLEAN` |

**Eliminados (migración 050)** — no volver a referenciarlos: `participantes_taller.es_brigadista`
(D-FO08) y la tabla `taller_inventario` (D-FO07). Ninguno llegó a usarse; `Taller::inscribir()` ya
no recibe `$esBrigadista`. `talleres.id_oficio` y la tabla `oficios` se eliminaron en la **mig. 060**
(D-FO06): si el cliente pide llevar registro de oficios recibidos, se construye como módulo propio.

---

## Documentos y correlativos

Las **evidencias** de un taller se guardan en `storage/uploads/talleres/`, **fuera de la raíz web**,
y se sirven por `DescargaController::taller()` con control de rol (1 y 3). La subida valida extensión,
**MIME real** y tamaño ≤5 MB en `TalleresController::procesarEvidencias()` (hallazgo **H-15**).

🔒 **D-NEW01 / pregunta D5 — sin resolver.** Las claves `correlativo_oficio_formacion` y
`ano_correlativo_formacion` existen en `configuracion_sistema` desde la **mig. 007**, pero
**nada las usa**: no hay oficio de formación en el sistema. Antes de construirlo hace falta saber
del cliente **qué dice ese documento y a quién se dirige** — igual que con los formatos de Bienes,
inventarlo garantiza rehacerlo. `oficios_emitidos` tampoco tiene `id_taller`, así que el día que se
confirme requiere migración además de UI.
