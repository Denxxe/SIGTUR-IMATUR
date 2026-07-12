-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 058 — Recuperación de contraseña por correo (autoservicio)
-- El reset manual por Administrador (Sistema → Usuarios) se mantiene como
-- respaldo para cuentas sin correo registrado. Esta tabla soporta el flujo de
-- autoservicio: token de un solo uso enviado por correo, expira en 30 min.
-- Se guarda solo el HASH (sha256) del token — el token real solo viaja por
-- correo, nunca queda en claro en la BD.
-- Idempotente (CREATE TABLE IF NOT EXISTS).
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS password_resets (
    id           SERIAL PRIMARY KEY,
    id_usuario   INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    token_hash   VARCHAR(64) NOT NULL,
    expires_at   TIMESTAMP NOT NULL,
    used_at      TIMESTAMP,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    requested_ip VARCHAR(45)
);

CREATE INDEX IF NOT EXISTS idx_password_resets_usuario ON password_resets (id_usuario);
CREATE INDEX IF NOT EXISTS idx_password_resets_token    ON password_resets (token_hash);
