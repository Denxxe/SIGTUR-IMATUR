-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 027 — Jerarquía organizativa de departamentos (R-1)
-- Decisiones: ver docs/MODELO_NEGOCIO_RRHH.md 7.1 (D-RH30).
--   • `departamentos` se vuelve jerárquico: +id_padre (auto-FK) + tipo_unidad.
--   • Se siembra el organigrama oficial (Manual Descriptivo de Cargos, abril 2024):
--     Presidencia → 3 Direcciones → Coordinaciones + unidades de staff.
--   • Las filas de prueba se reparentan/renombran (RRHH → Dirección de Talento Humano).
--   • Liderazgo se deriva del cargo → se agregan 'Coordinador' y 'Presidenta' a cargos.
-- Idempotente: ADD COLUMN IF NOT EXISTS, INSERT ... ON CONFLICT (nombre) DO NOTHING,
--   UPDATE por nombre.
-- ─────────────────────────────────────────────────────────────────────────────

BEGIN;

-- 1) Nuevas columnas
ALTER TABLE departamentos
    ADD COLUMN IF NOT EXISTS id_padre integer,
    ADD COLUMN IF NOT EXISTS tipo_unidad character varying(30);

ALTER TABLE departamentos DROP CONSTRAINT IF EXISTS departamentos_id_padre_fkey;
ALTER TABLE departamentos ADD CONSTRAINT departamentos_id_padre_fkey
    FOREIGN KEY (id_padre) REFERENCES departamentos(id) ON DELETE SET NULL;

ALTER TABLE departamentos DROP CONSTRAINT IF EXISTS departamentos_tipo_unidad_check;
ALTER TABLE departamentos ADD CONSTRAINT departamentos_tipo_unidad_check
    CHECK (tipo_unidad IS NULL OR tipo_unidad IN
        ('Presidencia', 'Junta Directiva', 'Dirección', 'Coordinación', 'Oficina', 'Unidad'));

-- 2) Reparentar/renombrar filas de prueba existentes
UPDATE departamentos SET nombre = 'Dirección de Talento Humano' WHERE nombre = 'RRHH';

-- 3) Sembrar unidades oficiales (id_padre/tipo se fijan en el paso 4)
INSERT INTO departamentos (nombre, created_at) VALUES
    ('Presidencia', NOW()),
    ('Dirección de Planificación y Gestión Turística', NOW()),
    ('Dirección de Administración', NOW()),
    ('Dirección de Talento Humano', NOW()),
    ('Dirección General', NOW()),
    ('Relaciones Inter-Institucionales', NOW()),
    ('Oficina de Atención al Ciudadano (OAC)', NOW()),
    ('Dirección de Secretaría', NOW()),
    ('Consultoría Jurídica', NOW()),
    ('Auditoría Interna', NOW()),
    ('Promoción Turística', NOW()),
    ('Calidad y Servicios Turísticos', NOW()),
    ('Proyectos e Inversión Turística', NOW()),
    ('Formación', NOW()),
    ('Comunicación', NOW()),
    ('Presupuesto', NOW()),
    ('Contabilidad', NOW()),
    ('Compra de Bienes y Servicios', NOW()),
    ('Servicios Generales', NOW()),
    ('Registro y Selección', NOW()),
    ('Bienestar Social', NOW()),
    ('Nómina', NOW())
ON CONFLICT (nombre) DO NOTHING;

-- 4) Fijar tipo_unidad e id_padre por nombre

-- 4.1 Raíz
UPDATE departamentos SET tipo_unidad = 'Presidencia', id_padre = NULL
WHERE nombre = 'Presidencia';

-- 4.2 Unidades de staff/línea directamente bajo Presidencia
UPDATE departamentos SET tipo_unidad = 'Dirección',
    id_padre = (SELECT id FROM departamentos WHERE nombre = 'Presidencia')
WHERE nombre IN ('Dirección General', 'Dirección de Secretaría');

UPDATE departamentos SET tipo_unidad = 'Oficina',
    id_padre = (SELECT id FROM departamentos WHERE nombre = 'Presidencia')
WHERE nombre IN ('Relaciones Inter-Institucionales', 'Oficina de Atención al Ciudadano (OAC)',
                 'Consultoría Jurídica', 'Auditoría Interna');

-- 4.3 Direcciones operativas bajo Presidencia
UPDATE departamentos SET tipo_unidad = 'Dirección',
    id_padre = (SELECT id FROM departamentos WHERE nombre = 'Presidencia')
WHERE nombre IN ('Dirección de Planificación y Gestión Turística',
                 'Dirección de Administración', 'Dirección de Talento Humano');

-- 4.4 Coordinaciones de Planificación y Gestión Turística
UPDATE departamentos SET tipo_unidad = 'Coordinación',
    id_padre = (SELECT id FROM departamentos WHERE nombre = 'Dirección de Planificación y Gestión Turística')
WHERE nombre IN ('Promoción Turística', 'Calidad y Servicios Turísticos',
                 'Proyectos e Inversión Turística', 'Formación', 'Comunicación');

-- 4.5 Coordinaciones de Administración
UPDATE departamentos SET tipo_unidad = 'Coordinación',
    id_padre = (SELECT id FROM departamentos WHERE nombre = 'Dirección de Administración')
WHERE nombre IN ('Presupuesto', 'Contabilidad', 'Compra de Bienes y Servicios', 'Servicios Generales');

-- 4.6 Coordinaciones de Talento Humano
UPDATE departamentos SET tipo_unidad = 'Coordinación',
    id_padre = (SELECT id FROM departamentos WHERE nombre = 'Dirección de Talento Humano')
WHERE nombre IN ('Registro y Selección', 'Bienestar Social', 'Nómina');

-- 4.7 Filas heredadas sin lugar en el organigrama → Unidad bajo Presidencia
UPDATE departamentos SET tipo_unidad = 'Unidad',
    id_padre = (SELECT id FROM departamentos WHERE nombre = 'Presidencia')
WHERE tipo_unidad IS NULL AND is_active = TRUE;

-- 5) Cargos de liderazgo (el liderazgo se deriva del cargo del empleado)
INSERT INTO cargos (nombre, descripcion, created_at) VALUES
    ('Presidenta', 'Máxima autoridad del instituto', NOW()),
    ('Coordinador', 'Responsable de una coordinación', NOW())
ON CONFLICT (nombre) DO NOTHING;

COMMIT;
