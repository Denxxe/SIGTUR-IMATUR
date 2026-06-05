-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 029 — Puntualidad en asistencias (R-7)
-- Decisión: ver docs/MODELO_NEGOCIO_RRHH.md 6.1 (D-RH33).
--   • `asistencias.minutos_tarde`: minutos de retraso respecto a la hora de entrada
--     del horario asignado (calculado al marcar entrada). NULL = empleado sin horario.
--     La impuntualidad se determina comparando minutos_tarde con la tolerancia
--     configurable (`minutos_tolerancia_puntualidad`, default 15).
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

BEGIN;

ALTER TABLE asistencias
    ADD COLUMN IF NOT EXISTS minutos_tarde integer;

COMMIT;
