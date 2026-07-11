-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 054 — Permitir LOGIN/LOGIN_FALLIDO en audit_logs
-- Bug: el CHECK de audit_logs.operacion solo aceptaba INSERT/UPDATE/DELETE.
-- Desde la migración 051, AuthController intenta registrar 'LOGIN' (acceso
-- exitoso) y 'LOGIN_FALLIDO' (contraseña incorrecta), pero el INSERT era
-- rechazado por el CHECK y el error se descartaba en silencio: la bitácora
-- de accesos (/reportes/accesos) siempre aparecía vacía. Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

ALTER TABLE audit_logs DROP CONSTRAINT IF EXISTS audit_logs_operacion_check;
ALTER TABLE audit_logs ADD CONSTRAINT audit_logs_operacion_check
    CHECK (operacion IN ('INSERT', 'UPDATE', 'DELETE', 'LOGIN', 'LOGIN_FALLIDO'));
