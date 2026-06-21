-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 047 — Traslado de personal entre departamentos (3D / O3)
-- Decisión: REASIGNACIÓN CON HISTORIAL (sin flujo de aprobación en el sistema).
-- Cada traslado cambia el departamento (y opcionalmente el cargo) del empleado
-- y queda registrado en el historial.
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS empleado_traslados (
    id                      SERIAL PRIMARY KEY,
    id_empleado             INTEGER NOT NULL REFERENCES empleados(id) ON DELETE CASCADE,
    id_departamento_origen  INTEGER,
    id_departamento_destino INTEGER NOT NULL,
    id_cargo_origen         INTEGER,
    id_cargo_destino        INTEGER,
    fecha                   DATE NOT NULL DEFAULT CURRENT_DATE,
    motivo                  VARCHAR(255),
    observacion             TEXT,
    is_active               BOOLEAN DEFAULT TRUE,
    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by              INTEGER
);
CREATE INDEX IF NOT EXISTS idx_traslados_empleado ON empleado_traslados (id_empleado);
