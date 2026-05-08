# Módulo de Rutas Turísticas — Reglas de Negocio

## Contexto institucional

Las rutas turísticas las gestiona el **Departamento de Rutas Turísticas y Proyectos** bajo la Dirección de Planificación y Gestión Turística. IMATUR opera dos tipos principales de ruta:

- **Cumaná Histórica** — abierta a todo público, con tarifa.
- **Exploradores de Cumaná** — dirigida a instituciones escolares, requiere formación previa.

---

## RN-RT01 — Estados de ruta

Valores válidos: `'Activa'`, `'Inactiva'`, `'En Mantenimiento'`.  
Las rutas son itinerarios fijos reutilizables. Para registrar una ejecución se usa `fecha_visita` y grupos de participantes distintos en cada ocasión.

---

## RN-RT02 — Niveles de dificultad

Valores válidos (con tilde, exactos): `'Fácil'`, `'Moderado'`, `'Difícil'`, `'Extremo'`.  
El CHECK constraint en BD rechaza cualquier valor que no coincida exactamente. Siempre validar contra whitelist antes de insertar/actualizar.

---

## RN-RT03 — Prerequisito de formación (RN-F12)

Las rutas con `requiere_formacion = TRUE` (ej: Exploradores de Cumaná) exigen que el participante haya asistido (`asistio = TRUE`) a al menos una actividad de formación antes de inscribirse.

- Participantes con cédula: el sistema verifica en `participantes_taller`.
- Participantes libres (niños/as sin cédula): **exentos** de esta verificación.
- Adultos acompañantes: misma lógica que participantes regulares (pendiente confirmar, ver pregunta 143).

---

## RN-RT04 — Participantes y grupos

- Los participantes pueden ser individuos o grupos escolares.
- Se admiten participantes sin cédula (niños/as) mediante el modo libre (`nombre_libre`/`apellido_libre`).
- Un participante puede inscribirse en múltiples rutas.
- **Cumaná Histórica** admite todo público (turistas, familias, grupos corporativos).
- **Exploradores de Cumaná** es para instituciones escolares.

---

## RN-RT05 — Rutas de pago

- **Cumaná Histórica** tiene tarifa.
- El sistema **no registra pagos actualmente** — pendiente definir si debe implementarse (ver pregunta 140).
- Las demás rutas son gratuitas.

---

## RN-RT06 — Puntos de ruta (paradas)

- Los puntos tienen orden, nombre, descripción y coordenadas lat/lon opcionales.
- El orden no es obligatorio (el guía puede variar el recorrido).
- Un punto de ruta puede aparecer en múltiples rutas si se registra por separado en cada una.
- No hay cálculo automático de duración total a partir de tiempos por punto.

---

## RN-RT07 — Facilitador y guía

- La ruta tiene un facilitador principal (`id_facilitador` FK a empleados).
- El facilitador no necesariamente es el guía activo en cada ejecución.
- **Pendiente:** si puede ser un guía externo certificado (ver pregunta 76).

---

## RN-RT08 — Inventario asignado

Los bienes se vinculan a una ruta mediante `ruta_inventario` (cantidad + observaciones). La desvinculación es manual. No hay devolución automática al finalizar o desactivar la ruta.

---

## RN-RT09 — Oficio emitido

Al generar un oficio de visita (`/rutas/oficio/{id}`), el sistema:
1. Asigna un número correlativo desde `configuracion_sistema.correlativo_oficio`.
2. Guarda el registro en `oficios_emitidos` (vinculado a la ruta).
3. Renderiza una página imprimible standalone (`oficio_imprimible.php`) sin layout del sistema.

El correlativo reinicia a `001` al cambiar de año.

---

## Brechas identificadas (pendientes de implementación)

| ID | Descripción | Impacto |
|----|-------------|---------|
| BRT-01 | Registro de pagos para Cumaná Histórica | Alto |
| BRT-02 | Confirmar si facilitador puede ser externo | Bajo |
| BRT-03 | Mapa visual de puntos de ruta (Google Maps / OSM offline) | Medio |
| BRT-04 | Registro de institución educativa al inscribir grupo | Medio |
| BRT-05 | Reporte de ejecuciones de ruta (una ruta, múltiples fechas) | Medio |
| BRT-06 | Asociar adultos acompañantes al prerequisito de formación | Bajo |
