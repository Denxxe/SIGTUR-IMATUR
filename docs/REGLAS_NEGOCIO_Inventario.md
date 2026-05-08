# Módulo de Inventario — Reglas de Negocio

## Contexto institucional

El inventario de bienes de IMATUR lo gestiona la **Dirección Administrativa**. Los bienes tienen un código de Bien Nacional (BN) asignado por la Contraloría Municipal. Los bienes pueden prestarse a rutas turísticas y talleres temporalmente.

---

## RN-IN01 — Identificación de bienes

- Cada bien tiene un `codigo_bn` (Bien Nacional) como identificador principal.
- Opcionalmente tiene `serial` (número de serie del fabricante).
- El sistema no genera correlativo propio: el código BN viene dado por la Alcaldía/Contraloría.
- **Pendiente confirmar:** si hay bienes sin código BN asignado y cómo se manejan (ver pregunta 136).

---

## RN-IN02 — Condiciones válidas

Valores: `'Nuevo'`, `'Bueno'`, `'Regular'`, `'Dañado'`.  
La condición se actualiza manualmente vía `actividadinventario` o edición directa.

---

## RN-IN03 — Soft delete = "Dar de baja"

La eliminación de un bien aplica `is_active = FALSE` (no destrucción física del registro). El bien dado de baja permanece en el historial de auditoría y en movimientos previos.  
**Pendiente:** si la baja formal requiere un acto administrativo o resolución (ver pregunta 137).

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

Las ubicaciones son lugares físicos fijos dentro de IMATUR (oficinas, almacenes). Tienen FK a departamento (`"departamento _d"` — columna con espacio, siempre requiere comillas dobles en SQL). Un bien puede estar en una ubicación sin estar asignado a un empleado.

---

## Brechas identificadas (pendientes de implementación)

| ID | Descripción | Impacto |
|----|-------------|---------|
| BIN-01 | UI para `taller_inventario` (tabla existe, sin vista) | Bajo |
| BIN-02 | Confirmar proceso de baja formal (¿acto administrativo?) | Alto |
| BIN-03 | Verificación periódica de inventario (conteo físico) | Medio |
| BIN-04 | Reporte de bienes por condición/categoría/ubicación | Medio |
| BIN-05 | Control de bienes fungibles vs durables | Bajo |
