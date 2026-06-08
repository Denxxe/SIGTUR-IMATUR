-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 038 — Representante del participante sin cédula en rutas
-- Regla de negocio (diferenciación de menores sin cédula):
--   Un niño/a sin cédula se ancla en su REPRESENTANTE (adulto con cédula). Esa
--   cédula del adulto es el identificador estable que permite distinguir
--   homónimos (mismo nombre + fecha de nacimiento, distinto representante =
--   personas distintas) y reconocer a la misma persona entre actividades.
--   • Talleres ya captura este adulto en nombre_docente/cedula_docente.
--   • Rutas no lo tenía: se agregan nombre_representante/cedula_representante.
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

BEGIN;

ALTER TABLE participantes_ruta ADD COLUMN IF NOT EXISTS nombre_representante  character varying(100);
ALTER TABLE participantes_ruta ADD COLUMN IF NOT EXISTS cedula_representante  character varying(20);

COMMIT;
