-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 051 — Endurecimiento del login (bloqueo por intentos fallidos)
-- Agrega control de intentos fallidos y bloqueo temporal de la cuenta, más la
-- marca de último acceso. Idempotente (IF NOT EXISTS).
--   · failed_attempts: intentos fallidos consecutivos desde el último login exitoso.
--   · locked_until:    si > NOW(), la cuenta está bloqueada hasta esa hora.
--   · last_login:      timestamp del último inicio de sesión exitoso.
-- ─────────────────────────────────────────────────────────────────────────────

ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS failed_attempts INT NOT NULL DEFAULT 0;
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS locked_until    TIMESTAMP NULL;
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS last_login      TIMESTAMP NULL;
