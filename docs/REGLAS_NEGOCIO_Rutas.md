# Módulo de Rutas Turísticas — Reglas de Negocio

**Última actualización:** 2026-05-22

## Contexto institucional

Las rutas turísticas las gestiona el **Departamento de Rutas Turísticas y Proyectos** bajo la Dirección de Planificación y Gestión Turística. IMATUR opera dos tipos principales de ruta:

- **Cumaná Histórica** — abierta a todo público, con tarifa.
- **Exploradores de Cumaná** — dirigida a instituciones escolares, requiere formación previa.

---

## RN-RT01 — Estados de ruta

Valores válidos: `'Activa'`, `'Inactiva'`, `'En Mantenimiento'`.  
Cada registro en `rutas` representa **una ejecución independiente** — no se reutiliza el mismo registro con múltiples fechas (D-RT01 respondida).

---

## RN-RT02 — Niveles de dificultad

Valores válidos con tilde exacta: `'Fácil'`, `'Moderado'`, `'Difícil'`, `'Extremo'`.  
El CHECK constraint en BD rechaza cualquier valor que no coincida exactamente. Siempre validar contra whitelist antes de insertar/actualizar.

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
- Las instituciones educativas se pre-registran en `instituciones_externas` (migración 007).
- `participantes_ruta.id_institucion FK instituciones_externas` (nullable) — para agrupar por institución.

---

## RN-RT05 — Rutas de pago

- **Cumaná Histórica** tiene tarifa. Campos: `tiene_tarifa BOOL`, `tarifa_monto DECIMAL(10,2)` (migración 007).
- El sistema **no registra pagos actualmente** — flujo de cobro pendiente de confirmar (D-RT02 ⚠️).
- Las demás rutas son gratuitas.

---

## RN-RT06 — Puntos de ruta (paradas)

- Los puntos tienen orden, nombre, descripción y coordenadas lat/lon opcionales.
- El orden no es obligatorio (el guía puede variar el recorrido según el día).
- La tabla `puntos_ruta` ya tiene columnas `lat` y `lon` para futura integración con mapa offline (Leaflet.js + OSM — backlog Fase 4).

---

## RN-RT07 — Facilitador y guía

- La ruta tiene un facilitador principal (`id_facilitador` FK a empleados).
- El facilitador puede ser un guía externo certificado, no necesariamente empleado de IMATUR.
- Campo `nombre_facilitador_externo VARCHAR(200)` en `rutas` (migración 007) — se usa cuando `id_facilitador` es NULL.
- En vistas se muestra "(Externo)" junto al nombre cuando aplica.

---

## RN-RT08 — Inventario asignado

Los bienes se vinculan a una ruta mediante `ruta_inventario` (cantidad + observaciones). La desvinculación es manual. No hay devolución automática al finalizar o desactivar la ruta.

---

## RN-RT09 — Oficio emitido

Al generar un oficio de visita (`/rutas/oficio/{id}`), el sistema:
1. Asigna un número correlativo desde `configuracion_sistema.correlativo_oficio_ruta`.
2. Guarda el registro en `oficios_emitidos` (vinculado a la ruta).
3. Renderiza una página imprimible standalone (`oficio_imprimible.php`) sin layout del sistema.

Formato del correlativo: `RUTA-007/2026`. Se reinicia a `001` al cambiar de año.

---

## Estado de brechas

| ID | Descripción | Estado |
|----|-------------|--------|
| BRT-01 | Registro de pagos para Cumaná Histórica | ⚠️ Pendiente — arquitectura preparada (D-RT02), flujo por confirmar |
| BRT-02 | Facilitador puede ser guía externo | ✅ Resuelto — `nombre_facilitador_externo` migración 007 |
| BRT-03 | Mapa visual de puntos de ruta | ❓ Pendiente Fase 4 — Leaflet.js + OSM offline |
| BRT-04 | Registro de institución educativa al inscribir grupo | ✅ Resuelto — `instituciones_externas` migración 007 |
| BRT-05 | Reporte de ejecuciones de ruta (múltiples fechas) | ❓ Pendiente — bajo impacto |
| BRT-06 | Adultos acompañantes y prerequisito de formación | ❓ Pendiente confirmar — bajo impacto |
