-- =============================================================================
-- SIGTUR-IMATUR (Sistema Integral de Gestión Turística y Administrativa)
-- Script de Creación de Base de Datos - PostgreSQL
-- =============================================================================


CREATE DATABASE "SIGTUR-IMATUR"
    WITH
    OWNER = postgres
    TEMPLATE = template0
    ENCODING = 'UTF8'
    LC_COLLATE = 'Spanish_Spain.1252'
    LC_CTYPE = 'Spanish_Spain.1252'
    LOCALE_PROVIDER = 'libc'
    TABLESPACE = pg_default
    CONNECTION LIMIT = -1
    IS_TEMPLATE = False;

COMMENT ON DATABASE "SIGTUR-IMATUR"
    IS 'BD para el proyecto hecho en IMATUR';


-- -----------------------------------------------------------------------------
-- 1. TABLA: roles
-- Descripción: Define los niveles de acceso al sistema.
-- -----------------------------------------------------------------------------

CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    
    -- Campos de Auditoría y Control
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    created_by INT,
    updated_by INT,
    deleted_by INT
);

COMMENT ON TABLE roles IS 'Roles de usuario para control de permisos (RBAC).';

-- -----------------------------------------------------------------------------
-- 2. TABLA: personas
-- Descripción: Tabla base para herencia de datos personales.
-- -----------------------------------------------------------------------------
CREATE TABLE personas (
    id SERIAL PRIMARY KEY,
    cedula VARCHAR(15) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    correo VARCHAR(100),
    genero CHAR(1) CHECK (genero IN ('M', 'F', 'O')),
    fecha_nacimiento DATE,
    direccion TEXT,
    
    -- Campos de Auditoría
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    created_by INT,
    updated_by INT,
    deleted_by INT
);

CREATE INDEX idx_personas_cedula ON personas(cedula);
COMMENT ON TABLE personas IS 'Datos base de todas las personas físicas en el sistema.';

-- -----------------------------------------------------------------------------
-- 3. TABLA: departamentos y cargos
-- -----------------------------------------------------------------------------
CREATE TABLE departamentos (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    created_by INT,
    updated_by INT,
    deleted_by INT
);

CREATE TABLE cargos (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    sueldo_base DECIMAL(12,2) DEFAULT 0,
    
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    created_by INT,
    updated_by INT,
    deleted_by INT
);

-- -----------------------------------------------------------------------------
-- 4. TABLA: empleados
-- Descripción: Extensión de la tabla personas para personal IMATUR.
-- -----------------------------------------------------------------------------
CREATE TABLE empleados (
    id SERIAL PRIMARY KEY,
    id_persona INT NOT NULL UNIQUE,
    id_cargo INT NOT NULL,
    id_departamento INT NOT NULL,
    nro_expediente VARCHAR(20) UNIQUE,
    fecha_ingreso DATE DEFAULT CURRENT_DATE,
    
    -- Auditoría
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    created_by INT,
    updated_by INT,
    deleted_by INT,

    CONSTRAINT fk_empleados_persona FOREIGN KEY (id_persona) REFERENCES personas(id) ON DELETE RESTRICT,
    CONSTRAINT fk_empleados_cargo FOREIGN KEY (id_cargo) REFERENCES cargos(id) ON DELETE RESTRICT,
    CONSTRAINT fk_empleados_dpto FOREIGN KEY (id_departamento) REFERENCES departamentos(id) ON DELETE RESTRICT
);

-- -----------------------------------------------------------------------------
-- 5. TABLA: usuarios
-- Descripción: Credenciales de acceso vinculadas a empleados.
-- -----------------------------------------------------------------------------
CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    id_empleado INT NOT NULL UNIQUE,
    id_rol INT NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password TEXT NOT NULL, -- Almacenar siempre con password_hash() de PHP
    ultimo_login TIMESTAMP,
    
    -- Auditoría
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    created_by INT,
    updated_by INT,
    deleted_by INT,

    CONSTRAINT fk_usuarios_empleado FOREIGN KEY (id_empleado) REFERENCES empleados(id) ON DELETE RESTRICT,
    CONSTRAINT fk_usuarios_rol FOREIGN KEY (id_rol) REFERENCES roles(id) ON DELETE RESTRICT
);

CREATE INDEX idx_usuarios_username ON usuarios(username);

-- -----------------------------------------------------------------------------
-- 6. TABLA: visitantes
-- Descripción: Extensión de personas para quienes visitan la institución.
-- -----------------------------------------------------------------------------
CREATE TABLE visitantes (
    id SERIAL PRIMARY KEY,
    id_persona INT NOT NULL UNIQUE,
    procedencia VARCHAR(255), -- Institución o ciudad
    motivo_frecuente TEXT,
    
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    created_by INT,
    updated_by INT,
    deleted_by INT,

    CONSTRAINT fk_visitantes_persona FOREIGN KEY (id_persona) REFERENCES personas(id) ON DELETE RESTRICT
);

-- -----------------------------------------------------------------------------
-- 7. TABLA: asistencias
-- -----------------------------------------------------------------------------
CREATE TABLE asistencias (
    id SERIAL PRIMARY KEY,
    id_empleado INT NOT NULL,
    fecha DATE DEFAULT CURRENT_DATE,
    hora_entrada TIME NOT NULL,
    hora_salida TIME,
    observacion TEXT,
    
    -- Auditoría
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    created_by INT,
    updated_by INT,
    deleted_by INT,

    CONSTRAINT fk_asistencias_empleado FOREIGN KEY (id_empleado) REFERENCES empleados(id) ON DELETE CASCADE
);

CREATE INDEX idx_asistencias_fecha ON asistencias(fecha);

-- -----------------------------------------------------------------------------
-- 8. TABLA: visitas
-- -----------------------------------------------------------------------------
CREATE TABLE visitas (
    id SERIAL PRIMARY KEY,
    id_visitante INT NOT NULL,
    id_empleado_host INT NOT NULL, -- Empleado que recibe la visita
    fecha DATE DEFAULT CURRENT_DATE,
    hora_entrada TIME NOT NULL,
    hora_salida TIME,
    motivo_detalle TEXT,
    
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    created_by INT,
    updated_by INT,
    deleted_by INT,

    CONSTRAINT fk_visitas_visitante FOREIGN KEY (id_visitante) REFERENCES visitantes(id) ON DELETE RESTRICT,
    CONSTRAINT fk_visitas_host FOREIGN KEY (id_empleado_host) REFERENCES empleados(id) ON DELETE RESTRICT
);

-- -----------------------------------------------------------------------------
-- 9. TABLA: actividades turísticas
-- -----------------------------------------------------------------------------
CREATE TABLE actividades (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    fecha_inicio TIMESTAMP,
    fecha_fin TIMESTAMP,
    lugar VARCHAR(255),
    estado VARCHAR(20) DEFAULT 'Programada', -- Programada, En curso, Finalizada, Cancelada
    
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    created_by INT,
    updated_by INT,
    deleted_by INT
);

CREATE TABLE actividad_participante (
    id_actividad INT NOT NULL,
    id_persona INT NOT NULL,
    rol_en_actividad VARCHAR(100), -- Participante, Guía, Logística
    
    PRIMARY KEY (id_actividad, id_persona),
    CONSTRAINT fk_act_part_act FOREIGN KEY (id_actividad) REFERENCES actividades(id) ON DELETE CASCADE,
    CONSTRAINT fk_act_part_per FOREIGN KEY (id_persona) REFERENCES personas(id) ON DELETE RESTRICT
);

-- -----------------------------------------------------------------------------
-- 10. TABLA: inventario (Categorias, Ubicaciones e Items)
-- -----------------------------------------------------------------------------
CREATE TABLE categorias (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ubicaciones (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE inventario (
    id SERIAL PRIMARY KEY,
    id_categoria INT NOT NULL,
    id_ubicacion INT NOT NULL,
    nombre_item VARCHAR(255) NOT NULL,
    descripcion TEXT,
    serial VARCHAR(100) UNIQUE,
    stock_actual INT DEFAULT 0,
    estado_fisico VARCHAR(50) DEFAULT 'Excelente', -- Excelente, Bueno, Regular, Malo, Inservible
    
    -- Auditoría
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    created_by INT,
    updated_by INT,
    deleted_by INT,

    CONSTRAINT fk_inv_cat FOREIGN KEY (id_categoria) REFERENCES categorias(id) ON DELETE RESTRICT,
    CONSTRAINT fk_inv_ubi FOREIGN KEY (id_ubicacion) REFERENCES ubicaciones(id) ON DELETE RESTRICT
);

CREATE TABLE actividad_inventario (
    id_actividad INT NOT NULL,
    id_inventario INT NOT NULL,
    cantidad_asignada INT DEFAULT 1,
    
    PRIMARY KEY (id_actividad, id_inventario),
    CONSTRAINT fk_act_inv_act FOREIGN KEY (id_actividad) REFERENCES actividades(id) ON DELETE CASCADE,
    CONSTRAINT fk_act_inv_inv FOREIGN KEY (id_inventario) REFERENCES inventario(id) ON DELETE RESTRICT
);

-- -----------------------------------------------------------------------------
-- 11. TABLA: audit_logs (Trazabilidad)
-- -----------------------------------------------------------------------------
CREATE TABLE audit_logs (
    id SERIAL PRIMARY KEY,
    tabla_afectada VARCHAR(100) NOT NULL,
    operacion VARCHAR(20) NOT NULL, -- INSERT, UPDATE, DELETE
    record_id INT,
    datos_previos JSONB,
    datos_nuevos JSONB,
    id_usuario INT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_direccion VARCHAR(45),

    CONSTRAINT fk_logs_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE INDEX idx_logs_fecha ON audit_logs(fecha);

-- -----------------------------------------------------------------------------
-- 12. REGISTROS INICIALES BÁSICOS
-- -----------------------------------------------------------------------------
INSERT INTO roles (nombre, descripcion, created_by) VALUES 
('Administrador', 'Control total del sistema', 1),
('RRHH', 'Gestión de personal y asistencia', 1),
('Turismo', 'Gestión de actividades y visitantes', 1),
('Servicios Generales', 'Gestión de inventario institucional', 1);

-- Nota: El primer usuario administrador debe crearse vinculando una persona y un empleado primero.
-- El password 'admin123' hasheado con bcrypt seria algo como: $2y$10$8L7/5v0nI4L3.p7R0W7uO.qXRz09fB8Xv0dY.m1pG.nQ.v1a2b3c4
