-- =====================================================================
-- 066 — Bienes: el responsable pasa a ser AUTOMÁTICO (B-68)
-- =====================================================================
--
-- Decisión del cliente (2026-08-05): el responsable de un bien NO se
-- elige a mano. Se deduce de dónde está el bien:
--
--     bien → ubicación → departamento → quien ocupa la jefatura
--
-- Prioridad: el **Director** del departamento y, en su defecto, el
-- **Coordinador** (B-26). Si entra alguien nuevo en ese cargo, pasa a ser
-- responsable de todos los bienes de su departamento automáticamente.
--
-- POR QUÉ SE ELIMINA LA COLUMNA en vez de recalcularla:
-- `inventario.id_responsable` guardaba el valor. Con una columna
-- almacenada habría que reescribirla cada vez que cambia un cargo, un
-- empleado egresa o un bien se traslada — y basta olvidar uno de esos
-- casos para que el inventario muestre como responsable a alguien que ya
-- no lo es. Derivándolo en la consulta el dato **no puede quedar
-- desactualizado**. El histórico no se pierde: `actividad_inventario`
-- registra el responsable de cada movimiento en su momento.
--
-- Caso especial: los bienes en **depósito** no pertenecen a ningún
-- departamento (B-25). Su custodio es la jefatura de la Coordinación de
-- Bienes, la misma que autoriza los movimientos (`bienes_depto_autoriza`).
--
-- Idempotente. La tabla `inventario` está vacía, así que no hay pérdida
-- de datos.
-- =====================================================================

BEGIN;

ALTER TABLE inventario DROP CONSTRAINT IF EXISTS fk_inventario_responsable;
DROP INDEX IF EXISTS idx_inventario_responsable;
ALTER TABLE inventario DROP COLUMN IF EXISTS id_responsable;

-- El movimiento "Asignación de responsable" deja de tener sentido: ya no
-- se asigna a nadie, se deduce. Se conserva en el CHECK para no romper
-- registros históricos, pero se retira de la lista seleccionable en la UI
-- (`ActividadInventario::TIPOS_MANUALES`).
COMMENT ON COLUMN actividad_inventario.id_empleado_responsable IS
    'Responsable en el momento del movimiento (histórico). Desde la mig. 066 el responsable actual del bien se DERIVA de su departamento, no se almacena.';

COMMIT;

-- =====================================================================
-- Verificación (no debe existir la columna):
--   SELECT column_name FROM information_schema.columns
--    WHERE table_name='inventario' AND column_name='id_responsable';
--
-- Derivación esperada (la que usa Inventario::SELECT_BASE):
--   bien → ubicaciones."departamento _d" → empleado activo de ese
--   departamento con nivel_jerarquico 'Dirección' (o 'Coordinación'),
--   salvo que la ubicación sea depósito → Coordinación de Bienes.
-- =====================================================================
