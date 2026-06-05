-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 030 — Uniforme y datos comunitarios (R-2b)
-- Decisión: ver docs/MODELO_NEGOCIO_RRHH.md 3.1 (D-RH25, D-RH35).
--   • Datos comunitarios (atributos de persona) → personas.
--   • Uniforme: solo se registra (D-RH35), no se controla dotación → empleados.
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

BEGIN;

ALTER TABLE personas
    ADD COLUMN IF NOT EXISTS centro_votacion  character varying(150),
    ADD COLUMN IF NOT EXISTS consejo_comunal  character varying(150),
    ADD COLUMN IF NOT EXISTS comuna           character varying(150);

ALTER TABLE empleados
    ADD COLUMN IF NOT EXISTS uniforme       boolean DEFAULT FALSE,
    ADD COLUMN IF NOT EXISTS talla_camisa   character varying(10),
    ADD COLUMN IF NOT EXISTS talla_pantalon character varying(10),
    ADD COLUMN IF NOT EXISTS talla_zapato   character varying(10);

COMMIT;
