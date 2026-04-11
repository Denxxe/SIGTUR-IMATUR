-- =============================================================================
-- SIGTUR-IMATUR (Sistema Integral de Gestión Turística y Administrativa)
-- Schema PostgreSQL v2.0 — Rediseño completo
-- Fecha: 2026-04-11
-- =============================================================================

-- =============================================================================
-- DOMINIO 1: ADMINISTRACIÓN DEL SISTEMA
-- =============================================================================

-- 1.1 ROLES
CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,

    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    created_by INT,
    updated_by INT,
    deleted_by INT
);
COMMENT ON TABLE roles IS 'Roles de acceso al sistema (RBAC).';

-- =============================================================================
-- DOMINIO 2: RECURSOS HUMANOS
-- =============================================================================

-- 2.1 PERSONAS (Tabla base — herencia para Empleados y Participantes)
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

    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    created_by INT,
    updated_by INT,
    deleted_by INT
);
CREATE INDEX idx_personas_cedula ON personas(cedula);
COMMENT ON TABLE personas IS 'Datos base de todas las personas físicas del sistema.';

-- 2.2 DEPARTAMENTOS
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

-- 2.3 CARGOS
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

-- 2.4 EMPLEADOS (Hereda de personas 1:1)
CREATE TABLE empleados (
    id SERIAL PRIMARY KEY,
    id_persona INT NOT NULL UNIQUE,
    id_cargo INT NOT NULL,
    id_departamento INT NOT NULL,
    nro_expediente VARCHAR(20) UNIQUE,
    fecha_ingreso DATE DEFAULT CURRENT_DATE,

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

-- 2.5 USUARIOS (Credenciales vinculadas a empleados)
CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    id_empleado INT NOT NULL UNIQUE,
    id_rol INT NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password TEXT NOT NULL, -- password_hash() PHP
    ultimo_login TIMESTAMP,

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

-- 2.6 ASISTENCIAS
CREATE TABLE asistencias (
    id SERIAL PRIMARY KEY,
    id_empleado INT NOT NULL,
    fecha DATE DEFAULT CURRENT_DATE,
    hora_entrada TIME NOT NULL,
    hora_salida TIME,
    observacion TEXT,

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
CREATE INDEX idx_asistencias_empleado_fecha ON asistencias(id_empleado, fecha);

-- =============================================================================
-- DOMINIO 3: INVENTARIO INSTITUCIONAL
-- =============================================================================

-- 3.1 CATEGORÍAS DE INVENTARIO
CREATE TABLE categorias (
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

-- 3.2 UBICACIONES FÍSICAS (Sedes, oficinas, almacenes)
CREATE TABLE ubicaciones (
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

-- 3.3 INVENTARIO (Bienes institucionales)
CREATE TABLE inventario (
    id SERIAL PRIMARY KEY,
    id_categoria INT NOT NULL,
    id_ubicacion INT NOT NULL,
    codigo_bn VARCHAR(50) UNIQUE,          -- Código de Bien Nacional
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    marca VARCHAR(100),
    modelo VARCHAR(100),
    serial VARCHAR(100) UNIQUE,
    condicion VARCHAR(20) DEFAULT 'Bueno' CHECK (condicion IN ('Nuevo','Bueno','Regular','Dañado','Inservible')),
    observaciones TEXT,

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
CREATE INDEX idx_inventario_codigo_bn ON inventario(codigo_bn);

-- 3.4 ACTIVIDAD DE INVENTARIO (Movimientos: asignación, devolución, traslado)
CREATE TABLE actividad_inventario (
    id SERIAL PRIMARY KEY,
    id_inventario INT NOT NULL,
    tipo_movimiento VARCHAR(30) NOT NULL CHECK (tipo_movimiento IN ('Asignacion','Devolucion','Traslado','Baja','Mantenimiento')),
    descripcion TEXT,
    fecha DATE DEFAULT CURRENT_DATE,
    id_empleado_responsable INT,           -- Quién recibe o entrega

    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    created_by INT,
    updated_by INT,
    deleted_by INT,

    CONSTRAINT fk_act_inv_item FOREIGN KEY (id_inventario) REFERENCES inventario(id) ON DELETE RESTRICT,
    CONSTRAINT fk_act_inv_emp FOREIGN KEY (id_empleado_responsable) REFERENCES empleados(id) ON DELETE SET NULL
);
CREATE INDEX idx_act_inv_fecha ON actividad_inventario(fecha);

-- =============================================================================
-- DOMINIO 4: FORMACIÓN (TALLERES COMUNITARIOS)
-- =============================================================================

-- 4.1 UBICACIONES DE FORMACIÓN (Liceos, plazas, centros comunitarios)
CREATE TABLE ubicaciones_formacion (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    tipo VARCHAR(50),                      -- 'Liceo', 'Plaza', 'Centro Comunitario', etc.
    direccion TEXT,
    municipio VARCHAR(100),

    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    created_by INT,
    updated_by INT,
    deleted_by INT
);

-- 4.2 TALLERES
CREATE TABLE talleres (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE,
    hora_inicio TIME,
    hora_fin TIME,
    id_ubicacion_formacion INT,
    id_facilitador INT NOT NULL,           -- Empleado que dicta el taller
    cupo_maximo INT DEFAULT 30,
    estado VARCHAR(20) DEFAULT 'Programado' CHECK (estado IN ('Programado','En Curso','Finalizado','Cancelado')),

    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    created_by INT,
    updated_by INT,
    deleted_by INT,

    CONSTRAINT fk_talleres_ubicacion FOREIGN KEY (id_ubicacion_formacion) REFERENCES ubicaciones_formacion(id) ON DELETE SET NULL,
    CONSTRAINT fk_talleres_facilitador FOREIGN KEY (id_facilitador) REFERENCES empleados(id) ON DELETE RESTRICT
);
CREATE INDEX idx_talleres_fecha ON talleres(fecha_inicio);
CREATE INDEX idx_talleres_estado ON talleres(estado);

-- 4.3 PARTICIPANTES DE TALLER (N:M personas ↔ talleres)
CREATE TABLE participantes_taller (
    id SERIAL PRIMARY KEY,
    id_taller INT NOT NULL,
    id_persona INT NOT NULL,
    asistio BOOLEAN DEFAULT FALSE,
    observaciones TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,

    CONSTRAINT fk_part_taller FOREIGN KEY (id_taller) REFERENCES talleres(id) ON DELETE CASCADE,
    CONSTRAINT fk_part_persona FOREIGN KEY (id_persona) REFERENCES personas(id) ON DELETE RESTRICT,
    CONSTRAINT uq_participante_taller UNIQUE (id_taller, id_persona)
);

-- =============================================================================
-- DOMINIO 5: RUTAS TURÍSTICAS
-- =============================================================================

-- 5.1 RUTAS
CREATE TABLE rutas (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    duracion_estimada VARCHAR(50),          -- Ej: '2 horas', '1 día'
    nivel_dificultad VARCHAR(20) DEFAULT 'Fácil' CHECK (nivel_dificultad IN ('Fácil','Moderado','Difícil','Extremo')),
    estado VARCHAR(20) DEFAULT 'Activa' CHECK (estado IN ('Activa','Inactiva','En Mantenimiento')),

    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    created_by INT,
    updated_by INT,
    deleted_by INT
);
CREATE INDEX idx_rutas_estado ON rutas(estado);

-- 5.2 PUNTOS DE RUTA (Lugares dentro de la ruta, ordenados)
CREATE TABLE puntos_ruta (
    id SERIAL PRIMARY KEY,
    id_ruta INT NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    orden INT NOT NULL DEFAULT 1,          -- Secuencia dentro de la ruta
    latitud DECIMAL(10,7),                 -- Coordenada GPS (opcional)
    longitud DECIMAL(10,7),

    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    created_by INT,
    updated_by INT,
    deleted_by INT,

    CONSTRAINT fk_punto_ruta FOREIGN KEY (id_ruta) REFERENCES rutas(id) ON DELETE CASCADE
);

-- 5.3 ACTIVIDADES DE RUTA (Excursiones, eventos en la ruta)
CREATE TABLE actividades_ruta (
    id SERIAL PRIMARY KEY,
    id_ruta INT NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    fecha DATE,
    id_empleado_responsable INT,

    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    created_by INT,
    updated_by INT,
    deleted_by INT,

    CONSTRAINT fk_act_ruta FOREIGN KEY (id_ruta) REFERENCES rutas(id) ON DELETE CASCADE,
    CONSTRAINT fk_act_ruta_emp FOREIGN KEY (id_empleado_responsable) REFERENCES empleados(id) ON DELETE SET NULL
);
CREATE INDEX idx_act_ruta_fecha ON actividades_ruta(fecha);

-- 5.4 RUTA ↔ INVENTARIO (N:M — Equipos asignados a rutas)
CREATE TABLE ruta_inventario (
    id SERIAL PRIMARY KEY,
    id_ruta INT NOT NULL,
    id_inventario INT NOT NULL,
    cantidad INT DEFAULT 1,
    observaciones TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,

    CONSTRAINT fk_ri_ruta FOREIGN KEY (id_ruta) REFERENCES rutas(id) ON DELETE CASCADE,
    CONSTRAINT fk_ri_inv FOREIGN KEY (id_inventario) REFERENCES inventario(id) ON DELETE RESTRICT,
    CONSTRAINT uq_ruta_inventario UNIQUE (id_ruta, id_inventario)
);

-- =============================================================================
-- DOMINIO 6: AUDITORÍA DEL SISTEMA
-- =============================================================================

CREATE TABLE audit_logs (
    id SERIAL PRIMARY KEY,
    tabla_afectada VARCHAR(100) NOT NULL,
    operacion VARCHAR(20) NOT NULL CHECK (operacion IN ('INSERT','UPDATE','DELETE')),
    record_id INT,
    datos_previos JSONB,
    datos_nuevos JSONB,
    id_usuario INT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_direccion VARCHAR(45),

    CONSTRAINT fk_logs_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL
);
CREATE INDEX idx_logs_fecha ON audit_logs(fecha);
CREATE INDEX idx_logs_tabla ON audit_logs(tabla_afectada);

-- =============================================================================
-- REGISTROS INICIALES
-- =============================================================================

INSERT INTO roles (nombre, descripcion, created_by) VALUES
('Administrador', 'Acceso total al sistema', NULL),
('RRHH', 'Gestión de personal y asistencia', NULL),
('Turismo', 'Gestión de rutas y formación', NULL),
('Inventario', 'Gestión de bienes institucionales', NULL);

-- Nota: El primer usuario admin debe crearse manualmente vinculando persona → empleado → usuario.
-- La contraseña debe generarse con password_hash('clave', PASSWORD_BCRYPT) en PHP.
