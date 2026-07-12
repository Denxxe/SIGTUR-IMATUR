-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 057 — Notificaciones "vistas" por usuario (campana tipo Facebook)
-- Las alertas de CentroAlertas son agregados calculados al vuelo (no hay fila
-- por alerta), así que "marcar como vista" no es un simple is_read: se guarda,
-- por usuario y por clave de alerta, un fingerprint (hash) del conjunto de IDs
-- que la componían en el momento de verla. Mientras ese conjunto no cambie, la
-- alerta no se le vuelve a mostrar a ese usuario; si cambia (ej. un nuevo
-- contrato entra en la ventana de aviso), reaparece con el conjunto nuevo.
-- Idempotente (CREATE TABLE IF NOT EXISTS).
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS alertas_vistas (
    id            SERIAL PRIMARY KEY,
    id_usuario    INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    clave_alerta  VARCHAR(60) NOT NULL,
    fingerprint   VARCHAR(64) NOT NULL,
    visto_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (id_usuario, clave_alerta)
);

CREATE INDEX IF NOT EXISTS idx_alertas_vistas_usuario ON alertas_vistas (id_usuario);
