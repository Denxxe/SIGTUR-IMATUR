-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 020 — Estado "Finalizada" para rutas turísticas
-- ─────────────────────────────────────────────────────────────────────────────
-- Decisión (2026-05-31): cada ruta es una EJECUCIÓN única. Una ruta al mismo
-- lugar en fechas distintas son registros distintos; al finalizarse, cada una
-- cuenta como una ruta finalizada independiente en reportes e indicadores.
-- "Finalizada" es un estado TERMINAL (como en talleres).

ALTER TABLE rutas DROP CONSTRAINT IF EXISTS rutas_estado_check;

ALTER TABLE rutas ADD CONSTRAINT rutas_estado_check
    CHECK (estado IN ('Activa', 'Inactiva', 'En Mantenimiento', 'Finalizada'));

COMMENT ON COLUMN rutas.estado IS
    'Activa | Inactiva | En Mantenimiento | Finalizada (terminal: visita ejecutada)';
