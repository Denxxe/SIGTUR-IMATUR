-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 034 — Constancias de trabajo (R-10)
-- Decisión: ver docs/MODELO_NEGOCIO_RRHH.md 8.1 (RN-RH14).
--   • El sistema genera constancias de trabajo con correlativo (CONST-NNN/AAAA)
--     y guarda un log/historial por empleado.
--   • Vive dentro del módulo Empleados (sin RBAC nuevo).
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

BEGIN;

CREATE TABLE IF NOT EXISTS constancias (
    id            SERIAL PRIMARY KEY,
    id_empleado   integer NOT NULL REFERENCES empleados(id) ON DELETE CASCADE,
    numero        character varying(30) NOT NULL,
    tipo          character varying(50) NOT NULL DEFAULT 'Constancia de trabajo',
    fecha_emision timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    observaciones text,
    is_active     boolean DEFAULT TRUE,
    created_at    timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at    timestamp without time zone,
    deleted_at    timestamp without time zone,
    created_by    integer,
    updated_by    integer,
    deleted_by    integer
);
CREATE INDEX IF NOT EXISTS idx_constancias_empleado ON constancias(id_empleado) WHERE is_active = TRUE;

-- Claves de correlativo para el módulo 'constancia' (usadas por ConfigSistema::generarNumeroOficio)
INSERT INTO configuracion_sistema (clave, valor, descripcion)
SELECT 'correlativo_oficio_constancia', '0', 'Último correlativo de constancias de trabajo'
WHERE NOT EXISTS (SELECT 1 FROM configuracion_sistema WHERE clave = 'correlativo_oficio_constancia');

INSERT INTO configuracion_sistema (clave, valor, descripcion)
SELECT 'ano_correlativo_constancia', EXTRACT(YEAR FROM CURRENT_DATE)::text, 'Año del correlativo de constancias'
WHERE NOT EXISTS (SELECT 1 FROM configuracion_sistema WHERE clave = 'ano_correlativo_constancia');

COMMIT;
