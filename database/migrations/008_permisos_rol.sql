-- ============================================================
-- MIGRACIÓN 008: Permisos dinámicos por rol
-- Convierte el RBAC de hardcoded (Router.php) a tabla en BD.
-- ============================================================

-- 1. Tabla principal
CREATE TABLE IF NOT EXISTS permisos_rol (
    id         SERIAL PRIMARY KEY,
    id_rol     INTEGER NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    modulo     VARCHAR(60) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INTEGER,
    UNIQUE (id_rol, modulo)
);

CREATE INDEX IF NOT EXISTS idx_permisos_rol_rol ON permisos_rol(id_rol);

-- 2. Seed con los permisos actuales
-- Rol 1 — Administrador: marcador especial '*' (acceso total)
INSERT INTO permisos_rol (id_rol, modulo) VALUES (1, '*')
    ON CONFLICT DO NOTHING;

-- Rol 2 — RRHH
INSERT INTO permisos_rol (id_rol, modulo) VALUES
    (2, 'DashboardController'),
    (2, 'EmpleadosController'),
    (2, 'CargosController'),
    (2, 'DepartamentosController'),
    (2, 'AsistenciasController'),
    (2, 'VisitantesController'),
    (2, 'VisitasController'),
    (2, 'ReportesController'),
    (2, 'ConfigController')
ON CONFLICT DO NOTHING;

-- Rol 3 — Turismo
INSERT INTO permisos_rol (id_rol, modulo) VALUES
    (3, 'DashboardController'),
    (3, 'RutasController'),
    (3, 'ActividadesrutaController'),
    (3, 'TalleresController'),
    (3, 'UbicacionesformacionController'),
    (3, 'PasantesController'),
    (3, 'VisitantesController'),
    (3, 'VisitasController'),
    (3, 'ReportesController')
ON CONFLICT DO NOTHING;

-- Rol 4 — Inventario
INSERT INTO permisos_rol (id_rol, modulo) VALUES
    (4, 'DashboardController'),
    (4, 'InventarioController'),
    (4, 'CategoriasController'),
    (4, 'UbicacionesController'),
    (4, 'ActividadesinventarioController'),
    (4, 'ReportesController')
ON CONFLICT DO NOTHING;

-- Rol 5 — Recepción
INSERT INTO permisos_rol (id_rol, modulo) VALUES
    (5, 'DashboardController'),
    (5, 'VisitantesController'),
    (5, 'VisitasController'),
    (5, 'AsistenciasController')
ON CONFLICT DO NOTHING;

-- ============================================================
-- FIN MIGRACIÓN 008
-- ============================================================
