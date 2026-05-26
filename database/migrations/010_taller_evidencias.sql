-- 010_taller_evidencias.sql
-- Workflow de estados: motivo de cancelación + evidencias de actividades finalizadas
-- Ejecutar: PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/010_taller_evidencias.sql

ALTER TABLE talleres ADD COLUMN IF NOT EXISTS motivo_cancelacion TEXT;

CREATE TABLE IF NOT EXISTS taller_evidencias (
    id               SERIAL PRIMARY KEY,
    id_taller        INT  NOT NULL REFERENCES talleres(id),
    archivo          VARCHAR(300) NOT NULL,
    nombre_original  VARCHAR(300) NOT NULL,
    tipo_archivo     VARCHAR(100),
    uploaded_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    uploaded_by      INT REFERENCES usuarios(id),
    is_active        BOOL NOT NULL DEFAULT TRUE,
    deleted_at       TIMESTAMP,
    deleted_by       INT REFERENCES usuarios(id)
);

CREATE INDEX IF NOT EXISTS idx_taller_evidencias_taller ON taller_evidencias(id_taller);
