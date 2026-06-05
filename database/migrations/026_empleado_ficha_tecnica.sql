-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 026 — Ficha Técnica del Trabajador (R-2)
-- Amplía datos de empleado/persona y crea las 3 tablas hijas de la ficha técnica.
-- Decisiones: ver docs/MODELO_NEGOCIO_RRHH.md 3.1–3.4 (D-RH25/26/31/35).
--   • Campos de formación académica + extras de alto valor (RIF, estado civil,
--     discapacidad) → en personas (atributos de persona, reutilizables).
--   • Clasificación Empleado/Obrero → en empleados.
--   • Institución = Nómina (un solo campo institucion_origen, ya existe — mig.025).
--   • Tablas hijas claveadas por id_persona (diseño normalizado, como pasantes).
-- ─────────────────────────────────────────────────────────────────────────────

BEGIN;

-- 1) personas — datos personales extra + formación académica
ALTER TABLE personas
    ADD COLUMN IF NOT EXISTS rif                    character varying(20),
    ADD COLUMN IF NOT EXISTS estado_civil           character varying(20),
    ADD COLUMN IF NOT EXISTS discapacidad           boolean DEFAULT FALSE,
    ADD COLUMN IF NOT EXISTS discapacidad_detalle   character varying(150),
    ADD COLUMN IF NOT EXISTS nivel_academico        character varying(50),
    ADD COLUMN IF NOT EXISTS profesion              character varying(120),
    ADD COLUMN IF NOT EXISTS titulo                 character varying(150),
    ADD COLUMN IF NOT EXISTS fecha_graduacion       date,
    ADD COLUMN IF NOT EXISTS institucion_academica  character varying(150);

ALTER TABLE personas DROP CONSTRAINT IF EXISTS personas_estado_civil_check;
ALTER TABLE personas ADD CONSTRAINT personas_estado_civil_check
    CHECK (estado_civil IS NULL OR estado_civil IN
        ('Soltero', 'Casado', 'Concubinato', 'Divorciado', 'Viudo'));

-- 2) empleados — clasificación Empleado/Obrero
ALTER TABLE empleados
    ADD COLUMN IF NOT EXISTS clasificacion character varying(20);

ALTER TABLE empleados DROP CONSTRAINT IF EXISTS empleados_clasificacion_check;
ALTER TABLE empleados ADD CONSTRAINT empleados_clasificacion_check
    CHECK (clasificacion IS NULL OR clasificacion IN ('Empleado', 'Obrero'));

-- 3) carga_familiar
CREATE TABLE IF NOT EXISTS carga_familiar (
    id              SERIAL PRIMARY KEY,
    id_persona      integer NOT NULL REFERENCES personas(id) ON DELETE RESTRICT,
    nombre_apellido character varying(150) NOT NULL,
    cedula          character varying(15),
    fecha_nacimiento date,
    parentesco      character varying(20) NOT NULL,
    is_active       boolean DEFAULT TRUE,
    created_at      timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at      timestamp without time zone,
    deleted_at      timestamp without time zone,
    created_by      integer,
    updated_by      integer,
    deleted_by      integer,
    CONSTRAINT carga_familiar_parentesco_check
        CHECK (parentesco IN ('Padre', 'Madre', 'Cónyuge', 'Concubino', 'Hijo'))
);
CREATE INDEX IF NOT EXISTS idx_carga_familiar_persona ON carga_familiar(id_persona) WHERE is_active = TRUE;

-- 4) cursos_realizados
CREATE TABLE IF NOT EXISTS cursos_realizados (
    id                 SERIAL PRIMARY KEY,
    id_persona         integer NOT NULL REFERENCES personas(id) ON DELETE RESTRICT,
    institucion        character varying(150),
    curso              character varying(200) NOT NULL,
    fecha_inicio       date,
    fecha_culminacion  date,
    is_active          boolean DEFAULT TRUE,
    created_at         timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at         timestamp without time zone,
    deleted_at         timestamp without time zone,
    created_by         integer,
    updated_by         integer,
    deleted_by         integer
);
CREATE INDEX IF NOT EXISTS idx_cursos_realizados_persona ON cursos_realizados(id_persona) WHERE is_active = TRUE;

-- 5) experiencia_laboral
CREATE TABLE IF NOT EXISTS experiencia_laboral (
    id                 SERIAL PRIMARY KEY,
    id_persona         integer NOT NULL REFERENCES personas(id) ON DELETE RESTRICT,
    organismo          character varying(150) NOT NULL,
    cargo              character varying(150),
    fecha_inicio       date,
    fecha_culminacion  date,
    is_active          boolean DEFAULT TRUE,
    created_at         timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at         timestamp without time zone,
    deleted_at         timestamp without time zone,
    created_by         integer,
    updated_by         integer,
    deleted_by         integer
);
CREATE INDEX IF NOT EXISTS idx_experiencia_laboral_persona ON experiencia_laboral(id_persona) WHERE is_active = TRUE;

COMMIT;
