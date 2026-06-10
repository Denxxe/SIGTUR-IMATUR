-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 039 — Carga familiar: sexo y estado vital
-- Para el reporte detallado de carga familiar con filtros (esposa fallecida,
-- padres vivos, niños por edad/sexo, etc.) se agregan:
--   • genero  (M/F)  — sexo del familiar
--   • vive    (bool) — TRUE = vivo, FALSE = fallecido (default vivo)
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

BEGIN;

ALTER TABLE carga_familiar ADD COLUMN IF NOT EXISTS genero character(1);
ALTER TABLE carga_familiar ADD COLUMN IF NOT EXISTS vive boolean DEFAULT TRUE;

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'carga_familiar_genero_check') THEN
        ALTER TABLE carga_familiar
            ADD CONSTRAINT carga_familiar_genero_check
            CHECK (genero IS NULL OR genero = ANY (ARRAY['M'::bpchar, 'F'::bpchar]));
    END IF;
END $$;

COMMIT;
