-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 016 — Orden único de paradas dentro de una ruta (RT-07)
-- ─────────────────────────────────────────────────────────────────────────────

-- Eliminar duplicados antes de agregar el constraint (si existen)
-- Conserva el de menor id en caso de conflicto
DELETE FROM puntos_ruta
WHERE id NOT IN (
    SELECT MIN(id) FROM puntos_ruta
    WHERE is_active = TRUE
    GROUP BY id_ruta, orden
) AND is_active = TRUE;

-- Agregar constraint UNIQUE solo sobre registros activos (parcial index)
CREATE UNIQUE INDEX IF NOT EXISTS uq_puntos_ruta_orden
    ON puntos_ruta (id_ruta, orden)
    WHERE is_active = TRUE;

COMMENT ON INDEX uq_puntos_ruta_orden IS
    'Garantiza que no existan dos paradas con el mismo orden dentro de una ruta activa';
