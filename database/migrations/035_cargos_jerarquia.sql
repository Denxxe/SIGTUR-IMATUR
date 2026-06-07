-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 035 — Jerarquía de cargos (reemplaza sueldo_base)
-- Decisión (2026-06-07, D-RH11): IMATUR no distingue sueldo base por cargo
--   (todos cobran el mismo base salvo casos notorios) → se elimina `sueldo_base`.
--   En su lugar, los cargos se clasifican por NIVEL de responsabilidad según el
--   organigrama: Presidencia → Dirección → Coordinación → Adscrito.
-- ─────────────────────────────────────────────────────────────────────────────

BEGIN;

ALTER TABLE cargos
    ADD COLUMN IF NOT EXISTS nivel_jerarquico character varying(20) DEFAULT 'Adscrito';

ALTER TABLE cargos DROP CONSTRAINT IF EXISTS cargos_nivel_jerarquico_check;
ALTER TABLE cargos ADD CONSTRAINT cargos_nivel_jerarquico_check
    CHECK (nivel_jerarquico IS NULL OR nivel_jerarquico IN
        ('Presidencia', 'Dirección', 'Coordinación', 'Adscrito'));

-- Sembrar el nivel de los cargos existentes
UPDATE cargos SET nivel_jerarquico = 'Adscrito'    WHERE nivel_jerarquico IS NULL;
UPDATE cargos SET nivel_jerarquico = 'Presidencia' WHERE nombre ILIKE 'president%';
UPDATE cargos SET nivel_jerarquico = 'Dirección'   WHERE nombre ILIKE 'director%';
UPDATE cargos SET nivel_jerarquico = 'Coordinación' WHERE nombre ILIKE 'coordinador%';

-- IMATUR no registra sueldo base por cargo
ALTER TABLE cargos DROP COLUMN IF EXISTS sueldo_base;

COMMIT;
