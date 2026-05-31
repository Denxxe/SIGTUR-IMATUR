-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 021 — Elimina nivel_dificultad de rutas
-- ─────────────────────────────────────────────────────────────────────────────
-- Decisión (2026-05-31): IMATUR no maneja la distinción de dificultad en rutas.
-- Se retira el campo del módulo. La columna y su CHECK se eliminan de la BD.

ALTER TABLE rutas DROP CONSTRAINT IF EXISTS rutas_nivel_dificultad_check;
ALTER TABLE rutas DROP COLUMN IF EXISTS nivel_dificultad;
