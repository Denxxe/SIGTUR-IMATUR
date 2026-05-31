-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 015 — Motivo de mantenimiento en rutas (RT-02 máquina de estados)
-- ─────────────────────────────────────────────────────────────────────────────

ALTER TABLE rutas
    ADD COLUMN IF NOT EXISTS motivo_mantenimiento TEXT;

COMMENT ON COLUMN rutas.motivo_mantenimiento IS
    'Motivo obligatorio cuando estado = ''En Mantenimiento''';
