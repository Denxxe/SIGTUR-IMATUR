-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 017 — Datos demográficos para participantes libres en rutas
-- ─────────────────────────────────────────────────────────────────────────────

ALTER TABLE participantes_ruta
    ADD COLUMN IF NOT EXISTS genero_libre    CHAR(1)  CHECK (genero_libre IN ('M','F','O')),
    ADD COLUMN IF NOT EXISTS fecha_nac_libre DATE;

COMMENT ON COLUMN participantes_ruta.genero_libre    IS 'Género del participante sin cédula (niño/a)';
COMMENT ON COLUMN participantes_ruta.fecha_nac_libre IS 'Fecha de nacimiento del participante sin cédula (validación 5-11 años)';
