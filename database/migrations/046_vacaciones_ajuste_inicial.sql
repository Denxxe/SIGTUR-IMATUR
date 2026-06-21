-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 046 — Ajuste de saldo inicial de vacaciones (3A)
-- Días que el empleado YA disfrutó antes de poner el módulo en marcha.
-- Permite que el saldo calculado sea exacto desde el arranque:
--   saldo = derecho acumulado − ajuste inicial − períodos registrados.
-- ─────────────────────────────────────────────────────────────────────────────

ALTER TABLE empleados ADD COLUMN IF NOT EXISTS vacaciones_ajuste_dias INTEGER NOT NULL DEFAULT 0;
