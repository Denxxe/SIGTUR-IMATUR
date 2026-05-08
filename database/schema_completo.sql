-- =============================================================================
-- SIGTUR-IMATUR — Schema completo consolidado
-- Versión: schema base + migraciones 001 al 006 (estado final)
-- Generado: 2026-05-08
--
-- Uso (instalación limpia):
--   createdb -U postgres "SIGTUR-IMATUR"
--   PGPASSWORD=1234 psql -U postgres -d "SIGTUR-IMATUR" -f database/schema_completo.sql
--
-- Acceso de desarrollo:
--   Usuario app: admin   (contraseña: hash bcrypt en tabla usuarios)
--   Usuario DB:  postgres / 1234  (entorno Laragon local)
--
-- Este archivo reemplaza schema.sql + migraciones 001-006.
-- NO ejecutar las migraciones individualmente si ya se usó este archivo.
-- =============================================================================

SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

BEGIN;

-- ══════════════════════════════════════════════════════════════════════════════
-- SECCIÓN 1 — TABLAS (sin FK constraints; se agregan al final)
-- ══════════════════════════════════════════════════════════════════════════════

-- ─────────────────────────────────────────────────────
-- roles
-- ─────────────────────────────────────────────────────
CREATE TABLE roles (
    id          SERIAL PRIMARY KEY,
    nombre      VARCHAR(50)  NOT NULL UNIQUE,
    descripcion TEXT,
    is_active   BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP,
    deleted_at  TIMESTAMP,
    created_by  INTEGER,
    updated_by  INTEGER,
    deleted_by  INTEGER
);
COMMENT ON TABLE roles IS 'Niveles de permiso del sistema (Administrador, RRHH, Turismo, Inventario).';

-- ─────────────────────────────────────────────────────
-- municipio
-- Nota: created_at NOT NULL sin DEFAULT — siempre pasar valor explícito
-- ─────────────────────────────────────────────────────
CREATE TABLE municipio (
    id            SERIAL PRIMARY KEY,
    nombre        VARCHAR(55) NOT NULL,
    codigo_postal VARCHAR(4),
    is_active     BOOLEAN     NOT NULL DEFAULT TRUE,
    created_at    TIMESTAMP   NOT NULL,
    updated_at    TIMESTAMP   NOT NULL,
    deleted_at    TIMESTAMP,
    created_by    INTEGER     NOT NULL,
    updated_by    INTEGER     NOT NULL,
    deleted_by    INTEGER
);
COMMENT ON TABLE municipio IS 'Municipios disponibles del sistema.';

-- ─────────────────────────────────────────────────────
-- parroquia
-- Nota: columnas usan create_at/create_by (sin "d") — nomenclatura histórica
-- ─────────────────────────────────────────────────────
CREATE TABLE parroquia (
    id           SERIAL PRIMARY KEY,
    nombre       VARCHAR(100) NOT NULL,
    id_municipio INTEGER      NOT NULL,
    is_active    BOOLEAN      NOT NULL,
    create_by    INTEGER      NOT NULL,
    update_by    INTEGER      NOT NULL,
    delete_by    INTEGER,
    create_at    TIMESTAMP    NOT NULL,
    update_at    TIMESTAMP    NOT NULL,
    delete_at    TIMESTAMP
);
COMMENT ON TABLE parroquia IS 'Parroquias del sistema, asociadas a municipio específico.';

-- ─────────────────────────────────────────────────────
-- personas (entidad base de todas las personas físicas)
-- ─────────────────────────────────────────────────────
CREATE TABLE personas (
    id               SERIAL PRIMARY KEY,
    cedula           VARCHAR(15) UNIQUE,
    nombre           VARCHAR(100) NOT NULL,
    apellido         VARCHAR(100) NOT NULL,
    telefono         VARCHAR(15),
    correo           VARCHAR(100),
    genero           CHAR(1) CHECK (genero IN ('M', 'F', 'O')),
    fecha_nacimiento DATE,
    direccion        TEXT,
    parroquia_id     INTEGER,
    is_active        BOOLEAN   DEFAULT TRUE,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP,
    deleted_at       TIMESTAMP,
    created_by       INTEGER,
    updated_by       INTEGER,
    deleted_by       INTEGER
);
COMMENT ON TABLE personas IS 'Datos base de todas las personas físicas del sistema.';

-- ─────────────────────────────────────────────────────
-- departamentos
-- ─────────────────────────────────────────────────────
CREATE TABLE departamentos (
    id          SERIAL PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    is_active   BOOLEAN   DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP,
    deleted_at  TIMESTAMP,
    created_by  INTEGER,
    updated_by  INTEGER,
    deleted_by  INTEGER
);

-- ─────────────────────────────────────────────────────
-- cargos
-- ─────────────────────────────────────────────────────
CREATE TABLE cargos (
    id          SERIAL PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    sueldo_base NUMERIC(12,2) DEFAULT 0,
    is_active   BOOLEAN   DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP,
    deleted_at  TIMESTAMP,
    created_by  INTEGER,
    updated_by  INTEGER,
    deleted_by  INTEGER
);

-- ─────────────────────────────────────────────────────
-- horarios (migración 002 — sin UI dedicada aún)
-- ─────────────────────────────────────────────────────
CREATE TABLE horarios (
    id             SERIAL PRIMARY KEY,
    nombre         VARCHAR(100) NOT NULL,
    hora_entrada   TIME         NOT NULL,
    hora_salida    TIME         NOT NULL,
    dias_laborales VARCHAR(50)  DEFAULT 'L-V',
    descripcion    TEXT,
    is_active      BOOLEAN   DEFAULT TRUE,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP,
    deleted_at     TIMESTAMP,
    created_by     INTEGER,
    updated_by     INTEGER,
    deleted_by     INTEGER
);
COMMENT ON TABLE horarios IS 'Turnos de trabajo del personal (ej: Mañana 7-12, Administrativo 8-16).';

-- ─────────────────────────────────────────────────────
-- empleados (incluye migración 002: tipo_contrato, fecha_egreso, id_horario)
-- ─────────────────────────────────────────────────────
CREATE TABLE empleados (
    id              SERIAL PRIMARY KEY,
    id_persona      INTEGER NOT NULL UNIQUE,
    id_cargo        INTEGER NOT NULL,
    id_departamento INTEGER NOT NULL,
    id_horario      INTEGER,
    nro_expediente  VARCHAR(20) UNIQUE,
    fecha_ingreso   DATE      DEFAULT CURRENT_DATE,
    tipo_contrato   VARCHAR(30) DEFAULT 'Fijo'
                        CHECK (tipo_contrato IN ('Fijo','Contratado','Suplente','Comisión de Servicio')),
    fecha_egreso    DATE,
    is_active       BOOLEAN   DEFAULT TRUE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP,
    deleted_at      TIMESTAMP,
    created_by      INTEGER,
    updated_by      INTEGER,
    deleted_by      INTEGER
);

-- ─────────────────────────────────────────────────────
-- asistencias
-- ─────────────────────────────────────────────────────
CREATE TABLE asistencias (
    id           SERIAL PRIMARY KEY,
    id_empleado  INTEGER NOT NULL,
    fecha        DATE      DEFAULT CURRENT_DATE,
    hora_entrada TIME      NOT NULL,
    hora_salida  TIME,
    observacion  TEXT,
    is_active    BOOLEAN   DEFAULT TRUE,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP,
    deleted_at   TIMESTAMP,
    created_by   INTEGER,
    updated_by   INTEGER,
    deleted_by   INTEGER
);

-- ─────────────────────────────────────────────────────
-- usuarios
-- ─────────────────────────────────────────────────────
CREATE TABLE usuarios (
    id           SERIAL PRIMARY KEY,
    id_empleado  INTEGER NOT NULL UNIQUE,
    id_rol       INTEGER NOT NULL,
    username     VARCHAR(50) NOT NULL UNIQUE,
    password     TEXT        NOT NULL,
    ultimo_login TIMESTAMP,
    is_active    BOOLEAN   DEFAULT TRUE,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP,
    deleted_at   TIMESTAMP,
    created_by   INTEGER,
    updated_by   INTEGER,
    deleted_by   INTEGER
);

-- ─────────────────────────────────────────────────────
-- audit_logs
-- ─────────────────────────────────────────────────────
CREATE TABLE audit_logs (
    id             SERIAL PRIMARY KEY,
    tabla_afectada VARCHAR(100) NOT NULL,
    operacion      VARCHAR(20)  NOT NULL
                       CHECK (operacion IN ('INSERT','UPDATE','DELETE')),
    record_id      INTEGER,
    datos_previos  JSONB,
    datos_nuevos   JSONB,
    id_usuario     INTEGER,
    fecha          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_direccion   VARCHAR(45)
);

-- ─────────────────────────────────────────────────────
-- categorias
-- ─────────────────────────────────────────────────────
CREATE TABLE categorias (
    id          SERIAL PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    is_active   BOOLEAN   DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP,
    deleted_at  TIMESTAMP,
    created_by  INTEGER,
    updated_by  INTEGER,
    deleted_by  INTEGER
);

-- ─────────────────────────────────────────────────────
-- ubicaciones (bienes internos)
-- Nota: columna "departamento _d" tiene espacio — quirk histórico, mantener tal cual
-- ─────────────────────────────────────────────────────
CREATE TABLE ubicaciones (
    id                SERIAL PRIMARY KEY,
    nombre            VARCHAR(100) NOT NULL UNIQUE,
    descripcion       TEXT,
    "departamento _d" INTEGER NOT NULL,
    is_active         BOOLEAN   DEFAULT TRUE,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP,
    deleted_at        TIMESTAMP,
    created_by        INTEGER,
    updated_by        INTEGER,
    deleted_by        INTEGER
);
COMMENT ON TABLE ubicaciones IS 'Oficinas o almacenes internos donde se custodian los bienes.';

-- ─────────────────────────────────────────────────────
-- inventario
-- ─────────────────────────────────────────────────────
CREATE TABLE inventario (
    id            SERIAL PRIMARY KEY,
    id_categoria  INTEGER NOT NULL,
    id_ubicacion  INTEGER NOT NULL,
    codigo_bn     VARCHAR(50) UNIQUE,
    nombre        VARCHAR(255) NOT NULL,
    descripcion   TEXT,
    marca         VARCHAR(100),
    modelo        VARCHAR(100),
    serial        VARCHAR(100) UNIQUE,
    condicion     VARCHAR(20) DEFAULT 'Bueno'
                      CHECK (condicion IN ('Nuevo','Bueno','Regular','Dañado','Inservible')),
    observaciones TEXT,
    is_active     BOOLEAN   DEFAULT TRUE,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP,
    deleted_at    TIMESTAMP,
    created_by    INTEGER,
    updated_by    INTEGER,
    deleted_by    INTEGER
);

-- ─────────────────────────────────────────────────────
-- actividad_inventario (movimientos de bienes)
-- ─────────────────────────────────────────────────────
CREATE TABLE actividad_inventario (
    id                      SERIAL PRIMARY KEY,
    id_inventario           INTEGER NOT NULL,
    tipo_movimiento         VARCHAR(30) NOT NULL
                                CHECK (tipo_movimiento IN ('Asignacion','Devolucion','Traslado','Baja','Mantenimiento')),
    descripcion             TEXT,
    fecha                   DATE DEFAULT CURRENT_DATE,
    id_empleado_responsable INTEGER,
    is_active               BOOLEAN   DEFAULT TRUE,
    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP,
    deleted_at              TIMESTAMP,
    created_by              INTEGER,
    updated_by              INTEGER,
    deleted_by              INTEGER
);

-- ─────────────────────────────────────────────────────
-- ubicaciones_formacion (migración 004: +es_sede_propia)
-- ─────────────────────────────────────────────────────
CREATE TABLE ubicaciones_formacion (
    id             SERIAL PRIMARY KEY,
    nombre         VARCHAR(150) NOT NULL,
    tipo           VARCHAR(50),
    direccion      TEXT,
    parroquia      INTEGER NOT NULL,
    es_sede_propia BOOLEAN DEFAULT FALSE,
    is_active      BOOLEAN   DEFAULT TRUE,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP,
    deleted_at     TIMESTAMP,
    created_by     INTEGER,
    updated_by     INTEGER,
    deleted_by     INTEGER
);

-- ─────────────────────────────────────────────────────
-- oficios (migración 004 — oficios recibidos de instituciones externas)
-- ─────────────────────────────────────────────────────
CREATE TABLE oficios (
    id             SERIAL PRIMARY KEY,
    numero         VARCHAR(50),
    fecha          DATE NOT NULL,
    id_institucion INTEGER,
    asunto         VARCHAR(255),
    is_active      BOOLEAN   DEFAULT TRUE,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP,
    deleted_at     TIMESTAMP,
    created_by     INTEGER,
    updated_by     INTEGER,
    deleted_by     INTEGER
);

-- ─────────────────────────────────────────────────────
-- talleres (estado final — migr. 002: tipo_actividad; 004: id_oficio;
--            006: es_interna, tipo_ente, 'Inducción' en CHECK)
-- ─────────────────────────────────────────────────────
CREATE TABLE talleres (
    id                     SERIAL PRIMARY KEY,
    nombre                 VARCHAR(200) NOT NULL,
    descripcion            TEXT,
    fecha_inicio           DATE         NOT NULL,
    fecha_fin              DATE,
    hora_inicio            TIME,
    hora_fin               TIME,
    id_ubicacion_formacion INTEGER,
    id_facilitador         INTEGER      NOT NULL,
    id_oficio              INTEGER,
    cupo_maximo            INTEGER DEFAULT 30,
    estado                 VARCHAR(20) DEFAULT 'Programado'
                               CHECK (estado IN ('Programado','En Curso','Finalizado','Cancelado')),
    tipo_actividad         VARCHAR(30) DEFAULT 'Taller'
                               CHECK (tipo_actividad IN ('Taller','Charla','Inducción')),
    es_interna             BOOLEAN NOT NULL DEFAULT FALSE,
    tipo_ente              VARCHAR(50)
                               CHECK (tipo_ente IS NULL OR tipo_ente IN
                                   ('Escuela','Liceo','Comunidad','Prestador de Servicio','IMATUR')),
    is_active              BOOLEAN   DEFAULT TRUE,
    created_at             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at             TIMESTAMP,
    deleted_at             TIMESTAMP,
    created_by             INTEGER,
    updated_by             INTEGER,
    deleted_by             INTEGER
);

-- ─────────────────────────────────────────────────────
-- taller_informes (migración 002: +updated_by, deleted_at, deleted_by)
-- Nota: total_atendidas es derivado (mujeres+hombres+ninas+ninos); recalcular antes de guardar
-- ─────────────────────────────────────────────────────
CREATE TABLE taller_informes (
    id                      SERIAL PRIMARY KEY,
    id_taller               INTEGER NOT NULL UNIQUE,
    unidad_estadal          VARCHAR(255) DEFAULT 'Sucre',
    lugar_exacto            VARCHAR(255),
    instituciones_presentes TEXT,
    mujeres                 INTEGER DEFAULT 0,
    hombres                 INTEGER DEFAULT 0,
    ninas                   INTEGER DEFAULT 0,
    ninos                   INTEGER DEFAULT 0,
    total_atendidas         INTEGER DEFAULT 0,
    resumen_actividad       TEXT,
    is_active               BOOLEAN   DEFAULT TRUE,
    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP,
    deleted_at              TIMESTAMP,
    created_by              INTEGER,
    updated_by              INTEGER,
    deleted_by              INTEGER
);

-- ─────────────────────────────────────────────────────
-- taller_inventario (migración 002: +is_active, updated_at/by, deleted_at/by)
-- ─────────────────────────────────────────────────────
CREATE TABLE taller_inventario (
    id            SERIAL PRIMARY KEY,
    id_taller     INTEGER NOT NULL,
    id_inventario INTEGER NOT NULL,
    cantidad      INTEGER DEFAULT 1,
    observaciones TEXT,
    is_active     BOOLEAN   DEFAULT TRUE,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP,
    deleted_at    TIMESTAMP,
    created_by    INTEGER,
    updated_by    INTEGER,
    deleted_by    INTEGER,
    UNIQUE (id_taller, id_inventario)
);

-- ─────────────────────────────────────────────────────
-- participantes_taller (estado final — migr. 002: auditoría; 004: libre;
--                        006: es_brigadista, nombre_docente, cedula_docente)
-- ─────────────────────────────────────────────────────
CREATE TABLE participantes_taller (
    id             SERIAL PRIMARY KEY,
    id_taller      INTEGER NOT NULL,
    id_persona     INTEGER,
    nombre_libre   VARCHAR(100),
    apellido_libre VARCHAR(100),
    cedula_libre   VARCHAR(20),
    asistio        BOOLEAN DEFAULT FALSE,
    observaciones  TEXT,
    es_brigadista  BOOLEAN NOT NULL DEFAULT FALSE,
    nombre_docente VARCHAR(100),
    cedula_docente VARCHAR(20),
    is_active      BOOLEAN   DEFAULT TRUE,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP,
    deleted_at     TIMESTAMP,
    created_by     INTEGER,
    updated_by     INTEGER,
    deleted_by     INTEGER,
    CONSTRAINT pt_participante_requerido
        CHECK (id_persona IS NOT NULL OR nombre_libre IS NOT NULL),
    UNIQUE (id_taller, id_persona)
);

-- ─────────────────────────────────────────────────────
-- pasantes (estado final — migr. 002: auditoría; 003: id_persona FK,
--            drop cedula/nombre/apellido; +Abandonado en CHECK)
-- ─────────────────────────────────────────────────────
CREATE TABLE pasantes (
    id                     SERIAL PRIMARY KEY,
    id_persona             INTEGER NOT NULL,
    institucion            VARCHAR(200) NOT NULL,
    carrera                VARCHAR(200),
    id_tutor_institucional INTEGER,
    fecha_inicio           DATE,
    fecha_fin              DATE,
    estado                 VARCHAR(50) DEFAULT 'Postulado'
                               CHECK (estado IN ('Postulado','Aceptado','En Curso',
                                                 'Culminado','Rechazado','Abandonado')),
    evaluacion             TEXT,
    nota                   NUMERIC(5,2),
    is_active              BOOLEAN   DEFAULT TRUE,
    created_at             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at             TIMESTAMP,
    deleted_at             TIMESTAMP,
    created_by             INTEGER,
    updated_by             INTEGER,
    deleted_by             INTEGER
);

-- ─────────────────────────────────────────────────────
-- pasante_documentos (migración 002: +is_active, updated_at/by, deleted_at/by)
-- Nota: usa fecha_registro (no created_at) como columna timestamp principal
-- ─────────────────────────────────────────────────────
CREATE TABLE pasante_documentos (
    id             SERIAL PRIMARY KEY,
    id_pasante     INTEGER NOT NULL,
    tipo_documento VARCHAR(100) NOT NULL
                       CHECK (tipo_documento IN
                           ('Carta de Postulación','Carta de Aceptación','Evaluación','Otro')),
    entregado      BOOLEAN DEFAULT FALSE,
    archivo_url    TEXT,
    observaciones  TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by     INTEGER,
    is_active      BOOLEAN   DEFAULT TRUE,
    updated_at     TIMESTAMP,
    updated_by     INTEGER,
    deleted_at     TIMESTAMP,
    deleted_by     INTEGER
);

-- ─────────────────────────────────────────────────────
-- rutas (estado final — migr. 005: fecha_visita, hora_visita, id_departamento,
--         id_facilitador, cupo_maximo; 006: requiere_formacion)
-- ─────────────────────────────────────────────────────
CREATE TABLE rutas (
    id                 SERIAL PRIMARY KEY,
    nombre             VARCHAR(200) NOT NULL,
    descripcion        TEXT,
    duracion_estimada  VARCHAR(50),
    nivel_dificultad   VARCHAR(20) DEFAULT 'Fácil'
                           CHECK (nivel_dificultad IN ('Fácil','Moderado','Difícil','Extremo')),
    estado             VARCHAR(20) DEFAULT 'Activa'
                           CHECK (estado IN ('Activa','Inactiva','En Mantenimiento')),
    fecha_visita       DATE,
    hora_visita        TIME,
    id_departamento    INTEGER,
    id_facilitador     INTEGER,
    cupo_maximo        INTEGER DEFAULT 20,
    requiere_formacion BOOLEAN NOT NULL DEFAULT FALSE,
    is_active          BOOLEAN   DEFAULT TRUE,
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP,
    deleted_at         TIMESTAMP,
    created_by         INTEGER,
    updated_by         INTEGER,
    deleted_by         INTEGER
);

-- ─────────────────────────────────────────────────────
-- puntos_ruta (paradas de una ruta con coordenadas opcionales)
-- ─────────────────────────────────────────────────────
CREATE TABLE puntos_ruta (
    id          SERIAL PRIMARY KEY,
    id_ruta     INTEGER NOT NULL,
    nombre      VARCHAR(200) NOT NULL,
    descripcion TEXT,
    orden       INTEGER NOT NULL DEFAULT 1,
    latitud     NUMERIC(10,7),
    longitud    NUMERIC(10,7),
    is_active   BOOLEAN   DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP,
    deleted_at  TIMESTAMP,
    created_by  INTEGER,
    updated_by  INTEGER,
    deleted_by  INTEGER
);

-- ─────────────────────────────────────────────────────
-- actividades_ruta
-- ─────────────────────────────────────────────────────
CREATE TABLE actividades_ruta (
    id                      SERIAL PRIMARY KEY,
    id_ruta                 INTEGER NOT NULL,
    nombre                  VARCHAR(200) NOT NULL,
    descripcion             TEXT,
    fecha                   DATE,
    id_empleado_responsable INTEGER,
    is_active               BOOLEAN   DEFAULT TRUE,
    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP,
    deleted_at              TIMESTAMP,
    created_by              INTEGER,
    updated_by              INTEGER,
    deleted_by              INTEGER
);

-- ─────────────────────────────────────────────────────
-- ruta_inventario (migración 002: +is_active, updated_at/by, deleted_at/by)
-- ─────────────────────────────────────────────────────
CREATE TABLE ruta_inventario (
    id            SERIAL PRIMARY KEY,
    id_ruta       INTEGER NOT NULL,
    id_inventario INTEGER NOT NULL,
    cantidad      INTEGER DEFAULT 1,
    observaciones TEXT,
    is_active     BOOLEAN   DEFAULT TRUE,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP,
    deleted_at    TIMESTAMP,
    created_by    INTEGER,
    updated_by    INTEGER,
    deleted_by    INTEGER,
    UNIQUE (id_ruta, id_inventario)
);

-- ─────────────────────────────────────────────────────
-- participantes_ruta (migración 005 — espejo de participantes_taller para rutas)
-- ─────────────────────────────────────────────────────
CREATE TABLE participantes_ruta (
    id             SERIAL PRIMARY KEY,
    id_ruta        INTEGER NOT NULL,
    id_persona     INTEGER,
    nombre_libre   VARCHAR(100),
    apellido_libre VARCHAR(100),
    cedula_libre   VARCHAR(20),
    asistio        BOOLEAN DEFAULT FALSE,
    observaciones  TEXT,
    is_active      BOOLEAN   DEFAULT TRUE,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP,
    deleted_at     TIMESTAMP,
    created_by     INTEGER,
    updated_by     INTEGER,
    deleted_by     INTEGER,
    CONSTRAINT pr_participante_req
        CHECK (id_persona IS NOT NULL OR nombre_libre IS NOT NULL)
);

-- ─────────────────────────────────────────────────────
-- visitantes (migración 001 — personas externas que visitan IMATUR)
-- ─────────────────────────────────────────────────────
CREATE TABLE visitantes (
    id               SERIAL PRIMARY KEY,
    cedula           VARCHAR(20) UNIQUE,
    nombre           VARCHAR(100) NOT NULL,
    apellido         VARCHAR(100) NOT NULL,
    procedencia      VARCHAR(100),
    telefono         VARCHAR(20),
    genero           CHAR(1) CHECK (genero IN ('M','F','O')),
    correo           VARCHAR(100),
    motivo_frecuente TEXT,
    is_active        BOOLEAN   DEFAULT TRUE,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP,
    deleted_at       TIMESTAMP,
    created_by       INTEGER,
    updated_by       INTEGER,
    deleted_by       INTEGER
);
COMMENT ON TABLE visitantes IS 'Personas externas a la institución que realizan visitas institucionales.';

-- ─────────────────────────────────────────────────────
-- visitas (migración 001 — control de entrada/salida, patrón toggle)
-- ─────────────────────────────────────────────────────
CREATE TABLE visitas (
    id            SERIAL PRIMARY KEY,
    id_visitante  INTEGER NOT NULL,
    id_empleado   INTEGER,
    motivo        VARCHAR(255),
    hora_entrada  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    hora_salida   TIMESTAMP,
    observaciones TEXT,
    is_active     BOOLEAN DEFAULT TRUE,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by    INTEGER
);
COMMENT ON TABLE visitas IS 'Control de marcaje de entrada/salida de visitantes. Patrón toggle.';

-- ─────────────────────────────────────────────────────
-- configuracion_sistema (migración 005 — clave/valor institucional)
-- ─────────────────────────────────────────────────────
CREATE TABLE configuracion_sistema (
    id          SERIAL PRIMARY KEY,
    clave       VARCHAR(100) UNIQUE NOT NULL,
    valor       TEXT         DEFAULT '',
    descripcion VARCHAR(255),
    updated_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_by  INTEGER
);

-- ─────────────────────────────────────────────────────
-- oficios_emitidos (migración 005 — oficios salientes de rutas)
-- ─────────────────────────────────────────────────────
CREATE TABLE oficios_emitidos (
    id                  SERIAL PRIMARY KEY,
    numero              VARCHAR(20)  NOT NULL,
    fecha               DATE         NOT NULL DEFAULT CURRENT_DATE,
    destinatario_nombre VARCHAR(200),
    destinatario_cargo  VARCHAR(200),
    asunto              VARCHAR(500),
    id_ruta             INTEGER,
    is_active           BOOLEAN   DEFAULT TRUE,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by          INTEGER
);

-- ─────────────────────────────────────────────────────
-- permisos_laborales (migración 002 — sin UI dedicada aún)
-- ─────────────────────────────────────────────────────
CREATE TABLE permisos_laborales (
    id               SERIAL PRIMARY KEY,
    id_empleado      INTEGER NOT NULL,
    tipo_permiso     VARCHAR(50) NOT NULL
                         CHECK (tipo_permiso IN ('Médico','Personal','Duelo','Lactancia','Estudio','Otro')),
    fecha_inicio     DATE NOT NULL,
    fecha_fin        DATE NOT NULL,
    dias_solicitados INTEGER,
    motivo           TEXT,
    estado           VARCHAR(20) DEFAULT 'Pendiente'
                         CHECK (estado IN ('Pendiente','Aprobado','Rechazado','Anulado')),
    id_aprobador     INTEGER,
    fecha_aprobacion TIMESTAMP,
    observaciones    TEXT,
    is_active        BOOLEAN   DEFAULT TRUE,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP,
    deleted_at       TIMESTAMP,
    created_by       INTEGER,
    updated_by       INTEGER,
    deleted_by       INTEGER
);
COMMENT ON TABLE permisos_laborales IS 'Registro de permisos y ausencias justificadas del personal.';

-- ─────────────────────────────────────────────────────
-- vacaciones (migración 002 — sin UI dedicada aún)
-- ─────────────────────────────────────────────────────
CREATE TABLE vacaciones (
    id                    SERIAL PRIMARY KEY,
    id_empleado           INTEGER NOT NULL,
    anio                  INTEGER NOT NULL,
    dias_correspondientes INTEGER DEFAULT 15,
    dias_tomados          INTEGER DEFAULT 0,
    fecha_inicio          DATE,
    fecha_fin             DATE,
    estado                VARCHAR(20) DEFAULT 'Pendiente'
                              CHECK (estado IN ('Pendiente','Aprobado','En Curso','Completado','Rechazado')),
    observaciones         TEXT,
    is_active             BOOLEAN   DEFAULT TRUE,
    created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP,
    deleted_at            TIMESTAMP,
    created_by            INTEGER,
    updated_by            INTEGER,
    deleted_by            INTEGER,
    UNIQUE (id_empleado, anio)
);
COMMENT ON TABLE vacaciones IS 'Control anual de días de vacaciones por empleado.';

-- ══════════════════════════════════════════════════════════════════════════════
-- SECCIÓN 2 — ÍNDICES
-- ══════════════════════════════════════════════════════════════════════════════

CREATE INDEX idx_personas_cedula          ON personas          (cedula);
CREATE INDEX idx_asistencias_fecha        ON asistencias       (fecha);
CREATE INDEX idx_asistencias_emp_fecha    ON asistencias       (id_empleado, fecha);
CREATE INDEX idx_usuarios_username        ON usuarios          (username);
CREATE INDEX idx_logs_fecha               ON audit_logs        (fecha);
CREATE INDEX idx_logs_tabla               ON audit_logs        (tabla_afectada);
CREATE INDEX idx_inventario_codigo_bn     ON inventario        (codigo_bn);
CREATE INDEX idx_talleres_fecha           ON talleres          (fecha_inicio);
CREATE INDEX idx_talleres_estado          ON talleres          (estado);
CREATE INDEX idx_pasantes_persona         ON pasantes          (id_persona);
CREATE INDEX idx_rutas_estado             ON rutas             (estado);
CREATE INDEX idx_act_inv_fecha            ON actividad_inventario (fecha);
CREATE INDEX idx_act_ruta_fecha           ON actividades_ruta  (fecha);
CREATE INDEX idx_visitantes_cedula        ON visitantes        (cedula);
CREATE INDEX idx_visitas_visitante        ON visitas           (id_visitante);
CREATE INDEX idx_visitas_entrada          ON visitas           (hora_entrada);
CREATE INDEX idx_permisos_empleado        ON permisos_laborales (id_empleado);
CREATE INDEX idx_permisos_fechas          ON permisos_laborales (fecha_inicio, fecha_fin);
CREATE INDEX idx_vacaciones_empleado      ON vacaciones        (id_empleado);
CREATE INDEX idx_vacaciones_anio          ON vacaciones        (anio);

-- ══════════════════════════════════════════════════════════════════════════════
-- SECCIÓN 3 — DATOS SEMILLA (datos de desarrollo)
-- ══════════════════════════════════════════════════════════════════════════════

-- roles (4 roles fijos del sistema)
INSERT INTO roles (id, nombre, descripcion, is_active, created_at) VALUES
    (1, 'Administrador', 'Acceso total al sistema',           TRUE, '2026-04-12 14:15:24'),
    (2, 'RRHH',          'Gestión de personal y asistencia',  TRUE, '2026-04-12 14:15:24'),
    (3, 'Turismo',       'Gestión de rutas y formación',      TRUE, '2026-04-12 14:15:24'),
    (4, 'Inventario',    'Gestión de bienes institucionales', TRUE, '2026-04-12 14:15:24');

-- municipio (Sucre y Bolivar — Cumaná está en Sucre)
INSERT INTO municipio (id, nombre, codigo_postal, is_active, created_at, updated_at, created_by, updated_by) VALUES
    (2, 'Sucre',   '6101', TRUE, '2026-04-26 16:51:36', '2026-04-26 16:51:36', 2, 2),
    (3, 'Bolivar', '6107', TRUE, '2026-04-28 03:22:54', '2026-04-28 03:22:54', 2, 2);

-- parroquia (7 parroquias del municipio Sucre — columnas sin "d": create_at, create_by)
INSERT INTO parroquia (id, nombre, id_municipio, is_active, create_by, update_by, delete_by, create_at, update_at, delete_at) VALUES
    (1, 'Altagracia',        2, TRUE, 2, 2, NULL, '2026-04-26 16:52:13', '2026-04-26 16:52:13', NULL),
    (2, 'Santa Ines',        2, TRUE, 2, 2, NULL, '2026-04-26 16:53:21', '2026-04-26 16:53:21', NULL),
    (3, 'Valentin Valiente', 2, TRUE, 2, 2, NULL, '2026-04-26 16:54:36', '2026-04-26 16:54:36', NULL),
    (4, 'Ayacucho',          2, TRUE, 2, 2, NULL, '2026-04-26 16:55:00', '2026-04-26 16:55:00', NULL),
    (5, 'San Juan',          2, TRUE, 2, 2, NULL, '2026-04-26 16:55:32', '2026-04-26 16:55:32', NULL),
    (6, 'Raul Leoni',        2, TRUE, 2, 2, NULL, '2026-04-26 16:56:06', '2026-04-26 16:56:06', NULL),
    (7, 'Gran Mariscal',     2, TRUE, 2, 2, NULL, '2026-04-26 16:56:30', '2026-04-26 16:56:30', NULL);

-- departamentos (HTML entities corregidas a UTF-8 real)
INSERT INTO departamentos (id, nombre, descripcion, is_active, created_at, created_by) VALUES
    (3, 'Dirección General',           'Sede Principal',                  TRUE, '2026-04-12 14:41:40', 1),
    (4, 'Departamento de informática', 'se encarga de-...',               TRUE, '2026-04-17 15:11:35', 2),
    (5, 'RRHH',                        'Departamento de Talento Humano',  TRUE, '2026-04-28 03:25:26', 2);

-- cargos (HTML entities corregidas)
INSERT INTO cargos (id, nombre, descripcion, sueldo_base, is_active, created_at, created_by) VALUES
    (2, 'Director', NULL,                                          1000.00, TRUE, '2026-04-12 14:41:40', 1),
    (3, 'CTI',      'Coordinación de tecnología de Información.', 150.00,  TRUE, '2026-04-17 14:52:17', 1);

-- personas:
--   id=2 → Super Admin (empleado/usuario de sistema)
--   id=3 → María López (creada al ejecutar migración 003 para el pasante)
INSERT INTO personas (id, cedula, nombre, apellido, telefono, correo, genero, fecha_nacimiento, direccion, is_active, created_at, created_by) VALUES
    (2, 'V-00000000', 'Super', 'Admin', '0000-0000000', NULL, NULL, NULL, 'Localhost', TRUE, '2026-04-12 14:41:40', 1),
    (3, 'V-30123456', 'María', 'López', NULL,           NULL, NULL, NULL, NULL,        TRUE, '2026-04-18 01:04:13', 1);

-- empleados (Super Admin como Director de Dirección General)
INSERT INTO empleados (id, id_persona, id_cargo, id_departamento, id_horario, nro_expediente, fecha_ingreso, tipo_contrato, fecha_egreso, is_active, created_at, created_by) VALUES
    (1, 2, 2, 3, NULL, NULL, '2026-04-12', 'Fijo', NULL, TRUE, '2026-04-12 14:41:40', 1);

-- asistencias (2 marcajes de prueba del empleado id=1)
INSERT INTO asistencias (id, id_empleado, fecha, hora_entrada, hora_salida, observacion, is_active, created_at, updated_at, created_by, updated_by) VALUES
    (1, 1, '2026-04-17', '15:17:24', '15:17:31', 'Marcaje de salida automático', TRUE, '2026-04-17 15:17:24', '2026-04-17 15:17:31', 1, 1),
    (2, 1, '2026-04-28', '03:19:06', '03:19:14', 'Marcaje de salida automático', TRUE, '2026-04-28 03:19:06', '2026-04-28 03:19:14', 1, 1);

-- usuarios (id=2 — el id=1 quedó libre por la secuencia del sistema original)
-- Contraseña: hash bcrypt — usar la contraseña del equipo de desarrollo
INSERT INTO usuarios (id, id_empleado, id_rol, username, password, is_active, created_at, created_by) VALUES
    (2, 1, 1, 'admin', '$2y$10$BwzQEV0g8B0OeoSgD4NUX.lI2h1oltT3qAWZWv48eXES8WED.IEwy', TRUE, '2026-04-12 14:41:40', 1);

-- categorias
INSERT INTO categorias (id, nombre, descripcion, is_active, created_at, updated_at, created_by, updated_by) VALUES
    (1, 'Inmobiliario', 'Bienes inmobiliarios', TRUE, '2026-04-18 04:56:22', NULL,                   2, NULL),
    (2, 'Inmuebles',    'Prueba  2',            TRUE, '2026-04-28 03:21:34', '2026-04-28 03:21:48', 2, 2);

-- audit_logs (2 entradas de ejemplo)
INSERT INTO audit_logs (id, tabla_afectada, operacion, record_id, datos_previos, datos_nuevos, id_usuario, fecha, ip_direccion) VALUES
    (8,  'talleres',      'UPDATE', 1, '{"is_active": false}', '{"is_active": true}', 2, '2026-04-19 00:09:05', '127.0.0.1'),
    (19, 'departamentos', 'INSERT', 0, NULL,                   '{"nombre": "RRHH"}',  2, '2026-04-28 03:25:27', '::1');

-- talleres (descripciones con UTF-8 correcto; con columnas nuevas de migraciones 002, 004, 006)
INSERT INTO talleres (id, nombre, descripcion, fecha_inicio, fecha_fin, hora_inicio, hora_fin,
                      id_ubicacion_formacion, id_facilitador, id_oficio, cupo_maximo,
                      estado, tipo_actividad, es_interna, tipo_ente,
                      is_active, created_at, updated_at, created_by, updated_by) VALUES
    (1, 'Charla cultura', '',
        '2026-04-16', '2026-04-17', '11:32:00', '14:32:00',
        NULL, 1, NULL, 30, 'Finalizado', 'Charla', FALSE, NULL,
        TRUE, '2026-04-17 14:33:51', NULL, 2, NULL),
    (2, 'CTI', 'Taller sobre los parques de Cumaná',
        '2026-04-18', '2026-04-18', '11:30:00', '14:30:00',
        NULL, 1, NULL, 15, 'Programado', 'Taller', FALSE, NULL,
        TRUE, '2026-04-18 02:39:56', '2026-04-28 03:24:22', 2, 2);

-- pasantes (post-migración 003: sin cedula/nombre/apellido, usa id_persona)
INSERT INTO pasantes (id, id_persona, institucion, carrera, id_tutor_institucional,
                      fecha_inicio, fecha_fin, estado, evaluacion, nota,
                      is_active, created_at) VALUES
    (1, 3, 'UPTAEB', 'Turismo', 1, '2026-04-18', '2026-07-18', 'En Curso', NULL, NULL, TRUE, '2026-04-18 01:04:13');

-- pasante_documentos (5 documentos del pasante id=1)
INSERT INTO pasante_documentos (id, id_pasante, tipo_documento, entregado, archivo_url, observaciones, is_active, fecha_registro, created_by) VALUES
    (1, 1, 'Carta de Postulación', TRUE,  NULL,                                                                      'Recibida por coordinación', TRUE, '2026-04-18 01:04:13', NULL),
    (2, 1, 'Carta de Aceptación',  TRUE,  NULL,                                                                      'Firmada por el director',   TRUE, '2026-04-18 01:04:13', NULL),
    (3, 1, 'Evaluación',           FALSE, NULL,                                                                      'Pendiente al finalizar',    TRUE, '2026-04-18 01:04:13', NULL),
    (4, 1, 'Otro',                 TRUE,  '/uploads/pasantes/1776786487_WhatsAppImage2025-07-02at4.53.32PM1.jpeg', 'Dale',                      TRUE, '2026-04-21 15:48:07', 2),
    (5, 1, 'Carta de Aceptación',  TRUE,  '/uploads/pasantes/1777346944_Anyeliscv.pdf.pdf',                       '',                          TRUE, '2026-04-28 03:29:04', 2);

-- configuracion_sistema (11 claves institucionales — de migración 005)
INSERT INTO configuracion_sistema (clave, valor, descripcion) VALUES
    ('director_nombre',    '',                        'Nombre del Director/Presidente de IMATUR'),
    ('director_apellido',  '',                        'Apellido del Director/Presidente'),
    ('director_cargo',     'Director',                'Cargo del firmante institucional'),
    ('resolucion_numero',  '',                        'N° de la Resolución de nombramiento'),
    ('resolucion_fecha',   '',                        'Fecha de la Resolución (texto, ej: 15 de enero de 2025)'),
    ('gaceta_numero',      '',                        'N° de la Gaceta Municipal Extraordinaria'),
    ('gaceta_fecha',       '',                        'Fecha de la Gaceta (texto, ej: 20 de enero de 2025)'),
    ('telf_institucion',   '(0293) 431-4073',         'Teléfono institucional'),
    ('correo_institucion', 'imatur.cumana@gmail.com', 'Correo electrónico institucional'),
    ('correlativo_oficio', '0',                       'Último correlativo de oficio emitido en el año en curso'),
    ('ano_correlativo',    '2026',                    'Año del correlativo activo (se reinicia automáticamente)')
ON CONFLICT (clave) DO NOTHING;

-- ══════════════════════════════════════════════════════════════════════════════
-- SECCIÓN 4 — FOREIGN KEY CONSTRAINTS (al final, cuando todos los datos existen)
-- ══════════════════════════════════════════════════════════════════════════════

-- parroquia
ALTER TABLE parroquia ADD CONSTRAINT fk_parroquia_municipio   FOREIGN KEY (id_municipio) REFERENCES municipio(id);
ALTER TABLE parroquia ADD CONSTRAINT fk_parroquia_create_by   FOREIGN KEY (create_by)   REFERENCES usuarios(id) NOT VALID;
ALTER TABLE parroquia ADD CONSTRAINT fk_parroquia_update_by   FOREIGN KEY (update_by)   REFERENCES usuarios(id) NOT VALID;
ALTER TABLE parroquia ADD CONSTRAINT fk_parroquia_delete_by   FOREIGN KEY (delete_by)   REFERENCES usuarios(id) NOT VALID;

-- municipio
ALTER TABLE municipio ADD CONSTRAINT fk_municipio_created_by  FOREIGN KEY (created_by)  REFERENCES usuarios(id) NOT VALID;

-- personas
ALTER TABLE personas  ADD CONSTRAINT fk_personas_parroquia    FOREIGN KEY (parroquia_id) REFERENCES parroquia(id) NOT VALID;

-- empleados
ALTER TABLE empleados ADD CONSTRAINT fk_empleados_persona     FOREIGN KEY (id_persona)      REFERENCES personas(id)      ON DELETE RESTRICT;
ALTER TABLE empleados ADD CONSTRAINT fk_empleados_cargo       FOREIGN KEY (id_cargo)         REFERENCES cargos(id)        ON DELETE RESTRICT;
ALTER TABLE empleados ADD CONSTRAINT fk_empleados_dpto        FOREIGN KEY (id_departamento)  REFERENCES departamentos(id) ON DELETE RESTRICT;
ALTER TABLE empleados ADD CONSTRAINT fk_empleados_horario     FOREIGN KEY (id_horario)       REFERENCES horarios(id)      ON DELETE SET NULL;

-- asistencias
ALTER TABLE asistencias ADD CONSTRAINT fk_asistencias_empleado FOREIGN KEY (id_empleado)    REFERENCES empleados(id)     ON DELETE CASCADE;

-- usuarios
ALTER TABLE usuarios ADD CONSTRAINT fk_usuarios_empleado      FOREIGN KEY (id_empleado)      REFERENCES empleados(id)     ON DELETE RESTRICT;
ALTER TABLE usuarios ADD CONSTRAINT fk_usuarios_rol           FOREIGN KEY (id_rol)           REFERENCES roles(id)         ON DELETE RESTRICT;

-- audit_logs
ALTER TABLE audit_logs ADD CONSTRAINT fk_logs_usuario         FOREIGN KEY (id_usuario)       REFERENCES usuarios(id)      ON DELETE SET NULL;

-- inventario
ALTER TABLE inventario ADD CONSTRAINT fk_inv_cat              FOREIGN KEY (id_categoria)     REFERENCES categorias(id)    ON DELETE RESTRICT;
ALTER TABLE inventario ADD CONSTRAINT fk_inv_ubi              FOREIGN KEY (id_ubicacion)     REFERENCES ubicaciones(id)   ON DELETE RESTRICT;

-- ubicaciones
ALTER TABLE ubicaciones ADD CONSTRAINT fk_ubi_dpto            FOREIGN KEY ("departamento _d") REFERENCES departamentos(id) NOT VALID;

-- actividad_inventario
ALTER TABLE actividad_inventario ADD CONSTRAINT fk_act_inv_item FOREIGN KEY (id_inventario)  REFERENCES inventario(id)    ON DELETE RESTRICT;
ALTER TABLE actividad_inventario ADD CONSTRAINT fk_act_inv_emp  FOREIGN KEY (id_empleado_responsable) REFERENCES empleados(id) ON DELETE SET NULL;

-- ubicaciones_formacion
ALTER TABLE ubicaciones_formacion ADD CONSTRAINT fk_ubf_parroquia FOREIGN KEY (parroquia)    REFERENCES parroquia(id)     NOT VALID;

-- oficios
ALTER TABLE oficios ADD CONSTRAINT fk_oficios_institucion     FOREIGN KEY (id_institucion)   REFERENCES ubicaciones_formacion(id) ON DELETE RESTRICT;

-- talleres
ALTER TABLE talleres ADD CONSTRAINT fk_talleres_ubicacion     FOREIGN KEY (id_ubicacion_formacion) REFERENCES ubicaciones_formacion(id) ON DELETE SET NULL;
ALTER TABLE talleres ADD CONSTRAINT fk_talleres_facilitador   FOREIGN KEY (id_facilitador)   REFERENCES empleados(id)     ON DELETE RESTRICT;
ALTER TABLE talleres ADD CONSTRAINT fk_talleres_oficio        FOREIGN KEY (id_oficio)        REFERENCES oficios(id)       ON DELETE SET NULL;

-- taller_informes
ALTER TABLE taller_informes ADD CONSTRAINT fk_taller_inf      FOREIGN KEY (id_taller)        REFERENCES talleres(id)      ON DELETE CASCADE;

-- taller_inventario
ALTER TABLE taller_inventario ADD CONSTRAINT fk_taller_inv_taller FOREIGN KEY (id_taller)   REFERENCES talleres(id)      ON DELETE CASCADE;
ALTER TABLE taller_inventario ADD CONSTRAINT fk_taller_inv_item   FOREIGN KEY (id_inventario) REFERENCES inventario(id)   ON DELETE RESTRICT;

-- participantes_taller
ALTER TABLE participantes_taller ADD CONSTRAINT fk_part_taller  FOREIGN KEY (id_taller)     REFERENCES talleres(id)      ON DELETE CASCADE;
ALTER TABLE participantes_taller ADD CONSTRAINT fk_part_persona  FOREIGN KEY (id_persona)   REFERENCES personas(id)      ON DELETE RESTRICT;

-- pasantes
ALTER TABLE pasantes ADD CONSTRAINT fk_pasante_persona        FOREIGN KEY (id_persona)       REFERENCES personas(id)      ON DELETE RESTRICT;
ALTER TABLE pasantes ADD CONSTRAINT fk_pasante_tutor          FOREIGN KEY (id_tutor_institucional) REFERENCES empleados(id) ON DELETE SET NULL;

-- pasante_documentos
ALTER TABLE pasante_documentos ADD CONSTRAINT fk_pasante_doc  FOREIGN KEY (id_pasante)       REFERENCES pasantes(id)      ON DELETE CASCADE;

-- rutas
ALTER TABLE rutas ADD CONSTRAINT fk_rutas_dpto                FOREIGN KEY (id_departamento)  REFERENCES departamentos(id) ON DELETE SET NULL;
ALTER TABLE rutas ADD CONSTRAINT fk_rutas_facilitador         FOREIGN KEY (id_facilitador)   REFERENCES empleados(id)     ON DELETE SET NULL;

-- puntos_ruta
ALTER TABLE puntos_ruta ADD CONSTRAINT fk_punto_ruta          FOREIGN KEY (id_ruta)          REFERENCES rutas(id)         ON DELETE CASCADE;

-- actividades_ruta
ALTER TABLE actividades_ruta ADD CONSTRAINT fk_act_ruta       FOREIGN KEY (id_ruta)          REFERENCES rutas(id)         ON DELETE CASCADE;
ALTER TABLE actividades_ruta ADD CONSTRAINT fk_act_ruta_emp   FOREIGN KEY (id_empleado_responsable) REFERENCES empleados(id) ON DELETE SET NULL;

-- ruta_inventario
ALTER TABLE ruta_inventario ADD CONSTRAINT fk_ri_ruta         FOREIGN KEY (id_ruta)          REFERENCES rutas(id)         ON DELETE CASCADE;
ALTER TABLE ruta_inventario ADD CONSTRAINT fk_ri_inv          FOREIGN KEY (id_inventario)    REFERENCES inventario(id)    ON DELETE RESTRICT;

-- participantes_ruta
ALTER TABLE participantes_ruta ADD CONSTRAINT fk_pr_ruta      FOREIGN KEY (id_ruta)          REFERENCES rutas(id)         ON DELETE CASCADE;
ALTER TABLE participantes_ruta ADD CONSTRAINT fk_pr_persona   FOREIGN KEY (id_persona)       REFERENCES personas(id)      ON DELETE RESTRICT;

-- visitas
ALTER TABLE visitas ADD CONSTRAINT fk_visitas_visitante       FOREIGN KEY (id_visitante)     REFERENCES visitantes(id)    ON DELETE RESTRICT;
ALTER TABLE visitas ADD CONSTRAINT fk_visitas_empleado        FOREIGN KEY (id_empleado)      REFERENCES empleados(id)     ON DELETE SET NULL;

-- oficios_emitidos
ALTER TABLE oficios_emitidos ADD CONSTRAINT fk_oficio_ruta    FOREIGN KEY (id_ruta)          REFERENCES rutas(id)         ON DELETE SET NULL;

-- permisos_laborales
ALTER TABLE permisos_laborales ADD CONSTRAINT fk_permiso_emp  FOREIGN KEY (id_empleado)      REFERENCES empleados(id)     ON DELETE RESTRICT;
ALTER TABLE permisos_laborales ADD CONSTRAINT fk_permiso_apro FOREIGN KEY (id_aprobador)     REFERENCES empleados(id)     ON DELETE SET NULL;

-- vacaciones
ALTER TABLE vacaciones ADD CONSTRAINT fk_vacacion_emp         FOREIGN KEY (id_empleado)      REFERENCES empleados(id)     ON DELETE RESTRICT;

-- ══════════════════════════════════════════════════════════════════════════════
-- SECCIÓN 5 — AJUSTE DE SECUENCIAS (reflejan el estado real de los datos)
-- ══════════════════════════════════════════════════════════════════════════════

SELECT setval('roles_id_seq',                  4,  true);
SELECT setval('municipio_id_seq',              3,  true);
SELECT setval('parroquia_id_seq',              7,  true);
SELECT setval('personas_id_seq',               3,  true);
SELECT setval('departamentos_id_seq',          5,  true);
SELECT setval('cargos_id_seq',                 3,  true);
SELECT setval('horarios_id_seq',               1,  false);
SELECT setval('empleados_id_seq',              1,  true);
SELECT setval('asistencias_id_seq',            2,  true);
SELECT setval('usuarios_id_seq',               2,  true);
SELECT setval('audit_logs_id_seq',             19, true);
SELECT setval('categorias_id_seq',             2,  true);
SELECT setval('ubicaciones_id_seq',            1,  false);
SELECT setval('inventario_id_seq',             1,  false);
SELECT setval('actividad_inventario_id_seq',   1,  false);
SELECT setval('ubicaciones_formacion_id_seq',  1,  false);
SELECT setval('oficios_id_seq',                1,  false);
SELECT setval('talleres_id_seq',               2,  true);
SELECT setval('taller_informes_id_seq',        1,  false);
SELECT setval('taller_inventario_id_seq',      1,  false);
SELECT setval('participantes_taller_id_seq',   1,  false);
SELECT setval('pasantes_id_seq',               1,  true);
SELECT setval('pasante_documentos_id_seq',     5,  true);
SELECT setval('rutas_id_seq',                  1,  false);
SELECT setval('puntos_ruta_id_seq',            1,  false);
SELECT setval('actividades_ruta_id_seq',       1,  false);
SELECT setval('ruta_inventario_id_seq',        1,  false);
SELECT setval('participantes_ruta_id_seq',     1,  false);
SELECT setval('visitantes_id_seq',             1,  false);
SELECT setval('visitas_id_seq',                1,  false);
SELECT setval('configuracion_sistema_id_seq',  11, true);
SELECT setval('oficios_emitidos_id_seq',       1,  false);
SELECT setval('permisos_laborales_id_seq',     1,  false);
SELECT setval('vacaciones_id_seq',             1,  false);

COMMIT;
