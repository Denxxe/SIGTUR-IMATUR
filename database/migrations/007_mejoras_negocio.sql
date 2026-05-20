-- ============================================================
-- Migración 007: Mejoras del modelo de negocio (Fase 2)
-- ============================================================

-- 1. Inventario: agregar condición "En Reparación"
ALTER TABLE inventario DROP CONSTRAINT IF EXISTS inventario_condicion_check;
ALTER TABLE inventario ADD CONSTRAINT inventario_condicion_check
    CHECK (condicion IN ('Nuevo','Bueno','Regular','Dañado','En Reparación'));

-- 2. Inventario: permitir codigo_bn nulo mientras se espera asignación
ALTER TABLE inventario ALTER COLUMN codigo_bn DROP NOT NULL;

-- 3. Rol 5 — Recepción (entry/exit control de visitantes y asistencias)
INSERT INTO roles (id, nombre, descripcion, is_active, created_at)
    VALUES (5, 'Recepción', 'Registro de visitantes, visitas y marcaje de asistencias. Sin acceso a módulos de gestión.', TRUE, CURRENT_TIMESTAMP)
    ON CONFLICT (id) DO NOTHING;

-- 4. Configuración: renombrar correlativo global a correlativo por módulo (rutas)
UPDATE configuracion_sistema SET clave = 'correlativo_oficio_ruta'  WHERE clave = 'correlativo_oficio';
UPDATE configuracion_sistema SET clave = 'ano_correlativo_ruta'     WHERE clave = 'ano_correlativo';

-- 5. Configuración: agregar claves nuevas
INSERT INTO configuracion_sistema (clave, valor) VALUES
    ('firmante_cargo', 'Director General'),
    ('correlativo_oficio_formacion', '0'),
    ('ano_correlativo_formacion', EXTRACT(YEAR FROM CURRENT_DATE)::TEXT)
ON CONFLICT (clave) DO NOTHING;

-- 6. Rutas: agregar campos de tarifa (preparación para cobros — D-RT02)
ALTER TABLE rutas ADD COLUMN IF NOT EXISTS tiene_tarifa     BOOLEAN       DEFAULT FALSE;
ALTER TABLE rutas ADD COLUMN IF NOT EXISTS tarifa_monto     DECIMAL(10,2) DEFAULT NULL;

-- 7. Rutas: facilitador externo (guía no empleado de IMATUR — D-RT05)
ALTER TABLE rutas ADD COLUMN IF NOT EXISTS nombre_facilitador_externo VARCHAR(150) DEFAULT NULL;

-- 8. Tabla instituciones_externas (D-RT04)
CREATE TABLE IF NOT EXISTS instituciones_externas (
    id           SERIAL PRIMARY KEY,
    nombre       VARCHAR(150) NOT NULL,
    tipo         VARCHAR(50)  DEFAULT 'Educativa',
    es_educativa BOOLEAN      DEFAULT TRUE,
    municipio    VARCHAR(100),
    contacto     VARCHAR(100),
    telefono     VARCHAR(30),
    is_active    BOOLEAN      DEFAULT TRUE,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    created_by   INT,
    updated_at   TIMESTAMP,
    updated_by   INT,
    deleted_at   TIMESTAMP,
    deleted_by   INT
);

-- 9. participantes_ruta: FK opcional a institución (D-RT04)
ALTER TABLE participantes_ruta ADD COLUMN IF NOT EXISTS id_institucion INT REFERENCES instituciones_externas(id) ON DELETE SET NULL;

-- ============================================================
-- FIN MIGRACIÓN 007
-- ============================================================
