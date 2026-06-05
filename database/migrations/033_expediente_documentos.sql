-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 033 — Documentos del expediente (R-5)
-- Decisión: ver docs/MODELO_NEGOCIO_RRHH.md 3.3 (D-RH22).
--   • Modelo híbrido: se suben archivos digitales (PDF/imagen) por recaudo, con
--     convención de nombre Tipo_Empleado_ID, y el sistema detecta faltantes.
--   • Vive dentro del módulo Empleados (sin RBAC nuevo).
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

BEGIN;

CREATE TABLE IF NOT EXISTS expediente_documentos (
    id              SERIAL PRIMARY KEY,
    id_empleado     integer NOT NULL REFERENCES empleados(id) ON DELETE CASCADE,
    tipo_documento  character varying(50) NOT NULL,
    archivo_url     character varying(255) NOT NULL,
    nombre_original character varying(255),
    observaciones   text,
    is_active       boolean DEFAULT TRUE,
    created_at      timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at      timestamp without time zone,
    deleted_at      timestamp without time zone,
    created_by      integer,
    updated_by      integer,
    deleted_by      integer
);
CREATE INDEX IF NOT EXISTS idx_expediente_doc_empleado ON expediente_documentos(id_empleado) WHERE is_active = TRUE;

COMMIT;
