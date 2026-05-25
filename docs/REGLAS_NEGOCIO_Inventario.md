# Módulo de Inventario — Reglas de Negocio

**Última actualización:** 2026-05-22

## Contexto institucional

El inventario de bienes de IMATUR lo gestiona la **Dirección Administrativa**. Los bienes tienen un código de Bien Nacional (BN) asignado por la Contraloría Municipal. Los bienes pueden prestarse a rutas turísticas y talleres temporalmente.

---

## RN-IN01 — Identificación de bienes

- Cada bien tiene un `codigo_bn` (Bien Nacional) como identificador principal — **nullable** desde migración 007.
- Los bienes sin código BN se muestran con "—" en vistas hasta que se asigne el código oficial.
- El sistema no genera correlativo propio: el código BN viene dado por la Alcaldía/Contraloría.
- Opcionalmente tiene `serial` (número de serie del fabricante).

---

## RN-IN02 — Condiciones válidas

Valores válidos: `'Nuevo'`, `'Bueno'`, `'Regular'`, `'Dañado'`, `'En Reparación'`.

La condición `'En Reparación'` fue añadida en migración 007. La whitelist en `InventarioController` debe incluir los 5 valores.

La condición se actualiza manualmente vía `actividadinventario` o edición directa.

---

## RN-IN03 — Soft delete = "Dar de baja"

La eliminación de un bien aplica `is_active = FALSE` (no destrucción física del registro). El bien dado de baja permanece en el historial de auditoría y en movimientos previos.

El registro interno es suficiente — no se genera acto administrativo imprimible (D-IN01 respondida).

---

## RN-IN04 — Movimientos de inventario

Tipos de movimiento válidos: `'Asignacion'`, `'Devolucion'`, `'Traslado'`, `'Baja'`, `'Mantenimiento'`.

Cada movimiento registra: bien, empleado responsable, fecha, descripción.  
No existe actualmente restricción en el sistema que impida mover un bien en estado Dañado.

---

## RN-IN05 — Bienes prestados a rutas

Los bienes pueden asignarse a rutas mediante `ruta_inventario` con cantidad y observaciones. Al remover la asignación (`RutaInventario::remover()`), el bien queda disponible nuevamente. No existe devolución automática al finalizar una ruta — se gestiona manualmente.

---

## RN-IN06 — Bienes prestados a talleres

Existe la tabla `taller_inventario` para préstamo de bienes a talleres. No hay controlador/vista dedicada para esta funcionalidad — acceso solo desde BD.

---

## RN-IN07 — Ubicaciones

Las ubicaciones son lugares físicos fijos dentro de IMATUR (oficinas, almacenes). Tienen FK a departamento mediante columna `"departamento _d"` — **atención: la columna tiene un espacio en el nombre, siempre requiere comillas dobles en SQL**.

Un bien puede estar en una ubicación sin estar asignado a un empleado.

---

## Estado de brechas

| ID | Descripción | Estado |
|----|-------------|--------|
| BIN-01 | UI para `taller_inventario` | ❓ Pendiente — bajo impacto |
| BIN-02 | Baja formal: ¿requiere acto administrativo? | ✅ Resuelto — D-IN01: solo registro interno |
| BIN-03 | Verificación periódica (conteo físico) | ✅ Resuelto — reporte de conteo exportable a CSV |
| BIN-04 | Reporte de bienes por condición/categoría/ubicación | ✅ Resuelto — D-IN02: reporte de bajas implementado |
| BIN-05 | Control de bienes fungibles vs durables | ❓ Pendiente — D-IN05: definir si aplica |
| BIN-06 | `codigo_bn` nullable para bienes sin código | ✅ Resuelto — migración 007, campo nullable |
