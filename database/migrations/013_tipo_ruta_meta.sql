-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 013 — tipo_ruta en rutas + metas anuales + RBAC InstitucionesExternas
-- ─────────────────────────────────────────────────────────────────────────────

-- 1. Clasificación administrativa de rutas turísticas
ALTER TABLE rutas
    ADD COLUMN IF NOT EXISTS tipo_ruta VARCHAR(50) DEFAULT 'General'
        CHECK (tipo_ruta IN ('Cumaná Histórica', 'Exploradores de Cumaná', 'Comunitaria', 'General'));

COMMENT ON COLUMN rutas.tipo_ruta IS 'Programa o clasificación administrativa de la ruta';

-- 2. Auto-clasificar rutas existentes por sus campos actuales
--    Primero las de tarifa → Cumaná Histórica
UPDATE rutas SET tipo_ruta = 'Cumaná Histórica'
WHERE tiene_tarifa = TRUE AND tipo_ruta = 'General';

--    Luego las escolares (requieren formación) → Exploradores de Cumaná
UPDATE rutas SET tipo_ruta = 'Exploradores de Cumaná'
WHERE requiere_formacion = TRUE AND tipo_ruta = 'General';

-- 3. Metas anuales en configuracion_sistema
INSERT INTO configuracion_sistema (clave, valor)
VALUES
    ('meta_rutas_anio',    '0'),
    ('meta_talleres_anio', '0')
ON CONFLICT (clave) DO NOTHING;

-- 4. RBAC — Módulo Instituciones Externas (roles 1=Admin, 3=Turismo)
INSERT INTO permisos_rol (id_rol, modulo) VALUES
    (1, 'InstitucionesexternasController'),
    (3, 'InstitucionesexternasController')
ON CONFLICT (id_rol, modulo) DO NOTHING;
