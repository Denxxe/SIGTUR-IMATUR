# Módulo de Rutas Turísticas — Reglas de Negocio

**Última actualización:** 2026-08-28 · **Migraciones:** hasta 073
**Pendientes y preguntas abiertas:** `docs/BACKLOG.md` §3.5.

> **Revisión del 2026-08-28.** Este documento describía cuatro estructuras que ya
> **no existen** (`instituciones_externas`, `rutas.nombre_facilitador_externo`,
> `ruta_inventario`, `nivel_dificultad`) y daba por pendiente el mapa Leaflet, que
> está construido. Corregido contra el código.
>
> ⚠️ **Es el único módulo sin levantamiento de requerimientos.** Las 64 preguntas de
> `PREGUNTAS_DESCUBRIMIENTO_Bienes_Rutas.md` (Parte 2) siguen sin responder, y R-07/R-08
> (catálogo de rutas vs. ejecución) pueden forzar un rediseño de lo que aquí se describe.

## Contexto institucional

Las rutas turísticas las gestiona el **Departamento de Rutas Turísticas y Proyectos** bajo la Dirección de Planificación y Gestión Turística. IMATUR opera dos tipos principales de ruta:

- **Cumaná Histórica** — abierta a todo público; el cobro **no está confirmado** (ver RN-RT05).
- **Exploradores de Cumaná** — dirigida a instituciones escolares, requiere formación previa.

---

## RN-RT01 — Estados de ruta

Valores válidos (`Ruta::ESTADOS`): `'Activa'`, `'Inactiva'`, `'En Mantenimiento'`, `'Finalizada'`.
`'Finalizada'` es **terminal** (`Ruta::ESTADO_TERMINAL`): una ruta finalizada ya no cambia de estado.
Cada registro en `rutas` representa **una ejecución independiente** — no se reutiliza el mismo registro con múltiples fechas (D-RT01 respondida).

---

## RN-RT02 — ~~Niveles de dificultad~~ (eliminado)

La columna `nivel_dificultad` se **eliminó en la migración 021**: IMATUR no clasifica
sus rutas por dificultad. No usar; el reporte de rutas tampoco la ofrece como filtro.

---

## RN-RT03 — Prerequisito de formación (RN-F12)

Las rutas con `requiere_formacion = TRUE` (ej: Exploradores de Cumaná) exigen que el participante haya asistido (`asistio = TRUE`) a al menos una actividad de formación antes de inscribirse.

- Participantes con cédula: el sistema verifica en `participantes_taller`.
- Participantes libres (niños/as sin cédula): **exentos** de esta verificación.

---

## RN-RT04 — Participantes y grupos

- Los participantes pueden ser individuos o grupos escolares.
- Se admiten participantes sin cédula (niños/as) mediante el modo libre (`nombre_libre`/`apellido_libre`).
- Un participante puede inscribirse en múltiples rutas.
- La institución educativa **no se registra**: `participantes_ruta.id_institucion` y la tabla
  `instituciones_externas` se eliminaron en la **migración 060** por no usarse nunca (D-RT05 cerrada).
  El indicador CMI que dependía de ese dato quedó descartado.

---

## RN-RT05 — Rutas de pago

- Las columnas `tiene_tarifa BOOL` y `tarifa_monto DECIMAL(10,2)` existen (migración 007) pero
  **nunca se capturan en ningún formulario**: están siempre vacías.
- Por eso el reporte de rutas **dejó de mostrar la columna Tarifa** el 2026-08-27 (hallazgo **H-14**):
  informaba «Gratuita» para toda ruta, siempre — un dato falso, no solo una columna inerte.
- El sistema **no registra pagos**. 🔒 **D-RT02** decide el destino de las columnas: si el cliente
  confirma que se cobra, se construye la captura y se reactiva el reporte; si lo descarta, se eliminan.

---

## RN-RT06 — Puntos de ruta (paradas)

- Los puntos tienen orden, nombre, descripción y coordenadas lat/lon opcionales.
- El orden no es obligatorio (el guía puede variar el recorrido según el día).
- La tabla `puntos_ruta` tiene `lat` y `lon`, y el **mapa está construido**: `rutas/detalle.php`
  renderiza los puntos con **Leaflet vendorizado en local** (`assets/js/leaflet.min.js` +
  `assets/css/leaflet.min.css`), sin CDN.

---

## RN-RT07 — Facilitador y guía

- La ruta tiene un facilitador principal (`id_facilitador` FK a empleados).
- `rutas.nombre_facilitador_externo` se **eliminó en la migración 060** (D-RT04 cerrada): nunca se
  usó. Hoy el facilitador es siempre un empleado; si el cliente necesita registrar guías externos,
  se construye desde cero.

---

## RN-RT08 — ~~Inventario asignado~~ (eliminado)

La tabla `ruta_inventario` se **eliminó en la migración 019**. No se asignan bienes a rutas.

---

## RN-RT09 — Oficio emitido

Al generar un oficio de visita (`/rutas/oficio/{id}`), el sistema:
1. Asigna un número correlativo desde `configuracion_sistema.correlativo_oficio_ruta`.
2. Guarda el registro en `oficios_emitidos` (vinculado a la ruta).
3. Renderiza una página imprimible standalone (`oficio_imprimible.php`) sin layout del sistema.

Formato del correlativo: `007/2026` — **sin prefijo**. Lo genera
`ConfigSistema::generarNumeroOficio('ruta')`, que incrementa de forma atómica
(`UPDATE … RETURNING` dentro de transacción, hallazgo H-06) y **reinicia a `001` al cambiar de año**.

🔒 **D-RT03** pendiente: si al pasar la ruta a *Finalizada* debe generarse el informe/oficio
automáticamente. Hoy se dispara a mano.

---

## Estado de brechas

| ID | Descripción | Estado |
|----|-------------|--------|
| BRT-01 | Registro de pagos para Cumaná Histórica | 🔒 **Bloqueado por D-RT02.** Las columnas existen pero no se capturan; desde el 2026-08-27 tampoco se reportan (H-14) |
| BRT-02 | Facilitador puede ser guía externo | ❌ **Revertido** — `nombre_facilitador_externo` eliminado (mig. 060) por no usarse |
| BRT-03 | Mapa visual de puntos de ruta | ✅ Resuelto — Leaflet + OSM vendorizados en local, en `rutas/detalle.php` |
| BRT-04 | Registro de institución educativa al inscribir grupo | ❌ **Revertido** — `instituciones_externas` eliminada (mig. 060) por no usarse (D-RT05) |
| BRT-05 | Reporte de ejecuciones de ruta (múltiples fechas) | ✅ Resuelto — cada ruta **es** una ejecución (RN-RT01); el reporte de rutas las lista con filtros de período y estado |
| BRT-06 | Adultos acompañantes y prerequisito de formación | 🔒 Pendiente de confirmar — bajo impacto. Se cubriría con el cuestionario (Parte 2) |
