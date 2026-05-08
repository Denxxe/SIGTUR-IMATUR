-- =============================================================================
-- Migración 004: Reglas de Negocio — Módulo Formación
-- Ejecutar DESPUÉS de las migraciones 001, 002 y 003
-- psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/004_formacion_reglas_negocio.sql
-- =============================================================================

-- ─────────────────────────────────────────────────────────────────────────────
-- RN-F01: tipo_actividad solo permite 'Taller' y 'Charla'
-- ─────────────────────────────────────────────────────────────────────────────
ALTER TABLE talleres DROP CONSTRAINT IF EXISTS talleres_tipo_actividad_check;
UPDATE talleres SET tipo_actividad = 'Taller'
    WHERE tipo_actividad IS NOT NULL
      AND tipo_actividad NOT IN ('Taller', 'Charla');
ALTER TABLE talleres ADD CONSTRAINT talleres_tipo_actividad_check
    CHECK (tipo_actividad IN ('Taller', 'Charla'));

-- ─────────────────────────────────────────────────────────────────────────────
-- RN-F02: Marcar sede propia (IMATUR) en ubicaciones_formacion
-- Después de ejecutar, marcar el registro de IMATUR manualmente:
--   UPDATE ubicaciones_formacion SET es_sede_propia = TRUE WHERE nombre ILIKE '%IMATUR%';
-- ─────────────────────────────────────────────────────────────────────────────
ALTER TABLE ubicaciones_formacion
    ADD COLUMN IF NOT EXISTS es_sede_propia BOOLEAN DEFAULT FALSE;

-- ─────────────────────────────────────────────────────────────────────────────
-- RN-F05/F06: Tabla de oficios para actividades externas
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS oficios (
    id             SERIAL PRIMARY KEY,
    numero         VARCHAR(50),
    fecha          DATE NOT NULL,
    id_institucion INT REFERENCES ubicaciones_formacion(id) ON DELETE RESTRICT,
    asunto         VARCHAR(255),
    is_active      BOOLEAN   DEFAULT TRUE,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP,
    deleted_at     TIMESTAMP,
    created_by     INT,
    updated_by     INT,
    deleted_by     INT
);

ALTER TABLE talleres
    ADD COLUMN IF NOT EXISTS id_oficio INT REFERENCES oficios(id) ON DELETE SET NULL;

-- ─────────────────────────────────────────────────────────────────────────────
-- RN-F16: Participantes sin cédula (niños/as sin documento de identidad)
-- ─────────────────────────────────────────────────────────────────────────────
ALTER TABLE participantes_taller ALTER COLUMN id_persona DROP NOT NULL;
ALTER TABLE participantes_taller
    ADD COLUMN IF NOT EXISTS nombre_libre   VARCHAR(100),
    ADD COLUMN IF NOT EXISTS apellido_libre VARCHAR(100),
    ADD COLUMN IF NOT EXISTS cedula_libre   VARCHAR(20);

-- Garantiza que siempre haya al menos un identificador
ALTER TABLE participantes_taller
    ADD CONSTRAINT pt_participante_requerido
    CHECK (id_persona IS NOT NULL OR nombre_libre IS NOT NULL);
