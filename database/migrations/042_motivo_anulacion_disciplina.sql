-- 042_motivo_anulacion_disciplina.sql
-- B14 — Al quitar (anular) una falta o amonestación, RRHH registra el motivo del
-- "por qué" se anula. Queda como parte del histórico (la fila se marca is_active=FALSE).
-- Idempotente.

BEGIN;

ALTER TABLE amonestaciones ADD COLUMN IF NOT EXISTS motivo_anulacion TEXT;
ALTER TABLE faltas         ADD COLUMN IF NOT EXISTS motivo_anulacion TEXT;

COMMIT;
