-- =============================================================================
-- Migración 002: Extensiones RRHH + Corrección de Auditoría
-- Ejecutar DESPUÉS de restaurar schema.sql y migración 001
-- psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/002_rrhh_extensions.sql
-- =============================================================================

-- ─────────────────────────────────────────────────────────────────────────────
-- 1. COLUMNAS FALTANTES EN EMPLEADOS
-- ─────────────────────────────────────────────────────────────────────────────
ALTER TABLE empleados
    ADD COLUMN IF NOT EXISTS tipo_contrato  VARCHAR(30) DEFAULT 'Fijo'
        CHECK (tipo_contrato IN ('Fijo','Contratado','Suplente','Comisión de Servicio')),
    ADD COLUMN IF NOT EXISTS fecha_egreso   DATE,
    ADD COLUMN IF NOT EXISTS id_horario     INT;   -- FK se agrega después de crear la tabla

-- ─────────────────────────────────────────────────────────────────────────────
-- 2. TIPO DE ACTIVIDAD EN TALLERES
-- ─────────────────────────────────────────────────────────────────────────────
ALTER TABLE talleres
    ADD COLUMN IF NOT EXISTS tipo_actividad VARCHAR(30) DEFAULT 'Taller'
        CHECK (tipo_actividad IN ('Taller','Charla','Curso','Taller de Arte','Capacitación'));

-- ─────────────────────────────────────────────────────────────────────────────
-- 3. COMPLETAR AUDITORÍA — taller_informes
-- ─────────────────────────────────────────────────────────────────────────────
ALTER TABLE taller_informes
    ADD COLUMN IF NOT EXISTS updated_by  INTEGER,
    ADD COLUMN IF NOT EXISTS deleted_at  TIMESTAMP,
    ADD COLUMN IF NOT EXISTS deleted_by  INTEGER;

-- ─────────────────────────────────────────────────────────────────────────────
-- 4. COMPLETAR AUDITORÍA — taller_inventario
-- ─────────────────────────────────────────────────────────────────────────────
ALTER TABLE taller_inventario
    ADD COLUMN IF NOT EXISTS is_active   BOOLEAN   DEFAULT TRUE,
    ADD COLUMN IF NOT EXISTS updated_at  TIMESTAMP,
    ADD COLUMN IF NOT EXISTS updated_by  INTEGER,
    ADD COLUMN IF NOT EXISTS deleted_at  TIMESTAMP,
    ADD COLUMN IF NOT EXISTS deleted_by  INTEGER;

-- ─────────────────────────────────────────────────────────────────────────────
-- 5. COMPLETAR AUDITORÍA — participantes_taller
-- ─────────────────────────────────────────────────────────────────────────────
ALTER TABLE participantes_taller
    ADD COLUMN IF NOT EXISTS is_active   BOOLEAN   DEFAULT TRUE,
    ADD COLUMN IF NOT EXISTS updated_at  TIMESTAMP,
    ADD COLUMN IF NOT EXISTS updated_by  INTEGER,
    ADD COLUMN IF NOT EXISTS deleted_at  TIMESTAMP,
    ADD COLUMN IF NOT EXISTS deleted_by  INTEGER;

-- ─────────────────────────────────────────────────────────────────────────────
-- 6. COMPLETAR AUDITORÍA — pasantes
-- ─────────────────────────────────────────────────────────────────────────────
ALTER TABLE pasantes
    ADD COLUMN IF NOT EXISTS updated_by  INTEGER,
    ADD COLUMN IF NOT EXISTS deleted_by  INTEGER;

-- ─────────────────────────────────────────────────────────────────────────────
-- 7. COMPLETAR AUDITORÍA — pasante_documentos
-- ─────────────────────────────────────────────────────────────────────────────
ALTER TABLE pasante_documentos
    ADD COLUMN IF NOT EXISTS is_active   BOOLEAN   DEFAULT TRUE,
    ADD COLUMN IF NOT EXISTS updated_at  TIMESTAMP,
    ADD COLUMN IF NOT EXISTS updated_by  INTEGER,
    ADD COLUMN IF NOT EXISTS deleted_at  TIMESTAMP,
    ADD COLUMN IF NOT EXISTS deleted_by  INTEGER;

-- ─────────────────────────────────────────────────────────────────────────────
-- 8. COMPLETAR AUDITORÍA — ruta_inventario
-- ─────────────────────────────────────────────────────────────────────────────
ALTER TABLE ruta_inventario
    ADD COLUMN IF NOT EXISTS is_active   BOOLEAN   DEFAULT TRUE,
    ADD COLUMN IF NOT EXISTS updated_at  TIMESTAMP,
    ADD COLUMN IF NOT EXISTS updated_by  INTEGER,
    ADD COLUMN IF NOT EXISTS deleted_at  TIMESTAMP,
    ADD COLUMN IF NOT EXISTS deleted_by  INTEGER;

-- ─────────────────────────────────────────────────────────────────────────────
-- 9. TABLA HORARIOS (turnos de trabajo)
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS horarios (
    id              SERIAL PRIMARY KEY,
    nombre          VARCHAR(100) NOT NULL,
    hora_entrada    TIME NOT NULL,
    hora_salida     TIME NOT NULL,
    dias_laborales  VARCHAR(50)  DEFAULT 'L-V',   -- 'L-V', 'L-S', 'Rotativo'
    descripcion     TEXT,
    is_active       BOOLEAN      DEFAULT TRUE,
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP,
    deleted_at      TIMESTAMP,
    created_by      INT,
    updated_by      INT,
    deleted_by      INT
);
COMMENT ON TABLE horarios IS 'Turnos de trabajo del personal (Ej: Mañana 7-12, Administrativo 8-16).';

-- FK de empleados → horarios (ahora que existe la tabla)
ALTER TABLE empleados
    ADD CONSTRAINT fk_empleados_horario
    FOREIGN KEY (id_horario) REFERENCES horarios(id) ON DELETE SET NULL;

-- ─────────────────────────────────────────────────────────────────────────────
-- 10. TABLA PERMISOS LABORALES
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS permisos_laborales (
    id                  SERIAL PRIMARY KEY,
    id_empleado         INT NOT NULL REFERENCES empleados(id) ON DELETE RESTRICT,
    tipo_permiso        VARCHAR(50) NOT NULL,
    fecha_inicio        DATE NOT NULL,
    fecha_fin           DATE NOT NULL,
    dias_solicitados    INT,
    motivo              TEXT,
    estado              VARCHAR(20)  DEFAULT 'Pendiente',
    id_aprobador        INT REFERENCES empleados(id) ON DELETE SET NULL,
    fecha_aprobacion    TIMESTAMP,
    observaciones       TEXT,
    is_active           BOOLEAN      DEFAULT TRUE,
    created_at          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP,
    deleted_at          TIMESTAMP,
    created_by          INT,
    updated_by          INT,
    deleted_by          INT,
    CONSTRAINT permisos_tipo_check   CHECK (tipo_permiso IN ('Médico','Personal','Duelo','Lactancia','Estudio','Otro')),
    CONSTRAINT permisos_estado_check CHECK (estado IN ('Pendiente','Aprobado','Rechazado','Anulado'))
);
COMMENT ON TABLE permisos_laborales IS 'Registro de permisos y ausencias justificadas del personal.';

CREATE INDEX IF NOT EXISTS idx_permisos_empleado ON permisos_laborales (id_empleado);
CREATE INDEX IF NOT EXISTS idx_permisos_fechas   ON permisos_laborales (fecha_inicio, fecha_fin);

-- ─────────────────────────────────────────────────────────────────────────────
-- 11. TABLA VACACIONES
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS vacaciones (
    id                      SERIAL PRIMARY KEY,
    id_empleado             INT NOT NULL REFERENCES empleados(id) ON DELETE RESTRICT,
    anio                    INT NOT NULL,
    dias_correspondientes   INT  DEFAULT 15,
    dias_tomados            INT  DEFAULT 0,
    fecha_inicio            DATE,
    fecha_fin               DATE,
    estado                  VARCHAR(20) DEFAULT 'Pendiente',
    observaciones           TEXT,
    is_active               BOOLEAN     DEFAULT TRUE,
    created_at              TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP,
    deleted_at              TIMESTAMP,
    created_by              INT,
    updated_by              INT,
    deleted_by              INT,
    CONSTRAINT vacaciones_estado_check CHECK (estado IN ('Pendiente','Aprobado','En Curso','Completado','Rechazado')),
    UNIQUE (id_empleado, anio)
);
COMMENT ON TABLE vacaciones IS 'Control anual de días de vacaciones por empleado.';

CREATE INDEX IF NOT EXISTS idx_vacaciones_empleado ON vacaciones (id_empleado);
CREATE INDEX IF NOT EXISTS idx_vacaciones_anio      ON vacaciones (anio);
