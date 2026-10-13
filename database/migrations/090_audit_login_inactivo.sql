-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 090 — La bitácora acepta LOGIN_INACTIVO
--
-- Hallado 2026-10-13 al revisar la bitácora: AuthController registra
-- 'LOGIN_INACTIVO' cuando una cuenta suspendida (o de un empleado egresado)
-- intenta entrar con la contraseña correcta, pero el CHECK de la mig. 054 solo
-- admitía INSERT/UPDATE/DELETE/LOGIN/LOGIN_FALLIDO. El INSERT fallaba, el
-- try/catch lo tragaba y esos intentos NUNCA quedaron registrados.
--
-- (El mismo día se corrigió Municipio, que auditaba con 'INSERTAR'/'ACTUALIZAR'/
-- 'ELIMINAR' y perdía igual sus registros; ahí el arreglo es de código, no de BD.)
--
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

ALTER TABLE audit_logs DROP CONSTRAINT IF EXISTS audit_logs_operacion_check;
ALTER TABLE audit_logs ADD CONSTRAINT audit_logs_operacion_check
    CHECK (operacion IN ('INSERT', 'UPDATE', 'DELETE', 'LOGIN', 'LOGIN_FALLIDO', 'LOGIN_INACTIVO'));
