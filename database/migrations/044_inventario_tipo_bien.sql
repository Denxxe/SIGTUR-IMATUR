-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 044 — Tipo de bien: Durable vs Fungible (U5 / D-IN05)
-- Durable  = bien nacional inventariable (requiere Código BN, identificable).
-- Fungible = material consumible (sin Código BN/serial; se controla por cantidad).
-- Permite validaciones específicas por tipo en el registro de inventario.
-- ─────────────────────────────────────────────────────────────────────────────

ALTER TABLE inventario ADD COLUMN IF NOT EXISTS tipo_bien VARCHAR(20) NOT NULL DEFAULT 'Durable';
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS cantidad  INTEGER     NOT NULL DEFAULT 1;

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'inventario_tipo_bien_chk') THEN
        ALTER TABLE inventario ADD CONSTRAINT inventario_tipo_bien_chk CHECK (tipo_bien IN ('Durable', 'Fungible'));
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'inventario_cantidad_chk') THEN
        ALTER TABLE inventario ADD CONSTRAINT inventario_cantidad_chk CHECK (cantidad >= 1);
    END IF;
END $$;

-- Normaliza vacíos a NULL: evita colisión con los índices UNIQUE de codigo_bn/serial
-- cuando hay varios consumibles sin código (PostgreSQL trata cada NULL como distinto).
UPDATE inventario SET codigo_bn = NULL WHERE codigo_bn = '';
UPDATE inventario SET serial    = NULL WHERE serial    = '';
