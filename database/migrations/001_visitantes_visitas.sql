-- =============================================================================
-- Migración 001: Módulos de Visitantes y Visitas
-- Ejecutar contra la BD SIGTUR-IMATUR DESPUÉS de restaurar schema.sql
-- psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/001_visitantes_visitas.sql
-- =============================================================================

-- Tabla de visitantes (personas externas a la institución)
CREATE TABLE IF NOT EXISTS visitantes (
    id               SERIAL PRIMARY KEY,
    cedula           VARCHAR(20) UNIQUE,
    nombre           VARCHAR(100) NOT NULL,
    apellido         VARCHAR(100) NOT NULL,
    procedencia      VARCHAR(100),
    telefono         VARCHAR(20),
    genero           CHAR(1) CHECK (genero IN ('M', 'F', 'O')),
    correo           VARCHAR(100),
    motivo_frecuente TEXT,

    is_active        BOOLEAN   DEFAULT TRUE,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP,
    deleted_at       TIMESTAMP,
    created_by       INT,
    updated_by       INT,
    deleted_by       INT
);
COMMENT ON TABLE visitantes IS 'Personas externas a la institución que realizan visitas.';

-- Tabla de visitas (control de entrada/salida)
CREATE TABLE IF NOT EXISTS visitas (
    id            SERIAL PRIMARY KEY,
    id_visitante  INT NOT NULL REFERENCES visitantes(id) ON DELETE RESTRICT,
    id_empleado   INT REFERENCES empleados(id) ON DELETE SET NULL,
    motivo        VARCHAR(255),
    hora_entrada  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    hora_salida   TIMESTAMP,
    observaciones TEXT,

    is_active     BOOLEAN DEFAULT TRUE,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by    INT
);
COMMENT ON TABLE visitas IS 'Control de marcaje de entrada y salida de visitantes externos.';

-- Índices de rendimiento
CREATE INDEX IF NOT EXISTS idx_visitas_visitante ON visitas (id_visitante);
CREATE INDEX IF NOT EXISTS idx_visitas_entrada   ON visitas (hora_entrada);
CREATE INDEX IF NOT EXISTS idx_visitantes_cedula ON visitantes (cedula);
