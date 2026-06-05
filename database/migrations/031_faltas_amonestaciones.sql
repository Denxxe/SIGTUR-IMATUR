-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 031 — Faltas injustificadas y amonestaciones (R-9)
-- Decisión: ver docs/MODELO_NEGOCIO_RRHH.md 2.5 (D-RH28).
--   • `faltas`: faltas injustificadas registradas por RRHH (el sistema las cuenta
--     y notifica). Distintas de los permisos/ausencias justificadas (R-8).
--   • `amonestaciones`: registradas manualmente por RRHH. 3 amonestaciones activas
--     = causa de despido (empleado Contratado).
--   • RBAC: AmonestacionesController para rol 2 (RRHH). Rol 1 usa '*'.
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

BEGIN;

CREATE TABLE IF NOT EXISTS faltas (
    id           SERIAL PRIMARY KEY,
    id_empleado  integer NOT NULL REFERENCES empleados(id) ON DELETE CASCADE,
    fecha        date NOT NULL DEFAULT CURRENT_DATE,
    motivo       text,
    is_active    boolean DEFAULT TRUE,
    created_at   timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at   timestamp without time zone,
    deleted_at   timestamp without time zone,
    created_by   integer,
    updated_by   integer,
    deleted_by   integer
);
CREATE INDEX IF NOT EXISTS idx_faltas_empleado ON faltas(id_empleado) WHERE is_active = TRUE;

CREATE TABLE IF NOT EXISTS amonestaciones (
    id           SERIAL PRIMARY KEY,
    id_empleado  integer NOT NULL REFERENCES empleados(id) ON DELETE CASCADE,
    fecha        date NOT NULL DEFAULT CURRENT_DATE,
    motivo       text NOT NULL,
    is_active    boolean DEFAULT TRUE,
    created_at   timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at   timestamp without time zone,
    deleted_at   timestamp without time zone,
    created_by   integer,
    updated_by   integer,
    deleted_by   integer
);
CREATE INDEX IF NOT EXISTS idx_amonestaciones_empleado ON amonestaciones(id_empleado) WHERE is_active = TRUE;

INSERT INTO permisos_rol (id_rol, modulo)
VALUES (2, 'AmonestacionesController')
ON CONFLICT (id_rol, modulo) DO NOTHING;

COMMIT;
