-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 045 — Base del módulo de Vacaciones (R-8, 3A)
-- Reglas confirmadas (2026-06-21):
--   · 15 días HÁBILES base + 1 día por año de servicio, TOPE 30.
--   · Antigüedad = total (incluye años en Alcaldía/Gobernación para comisionados).
--   · Días = hábiles, excluyendo fines de semana Y feriados.
--   · Vacaciones no disfrutadas se ACUMULAN (no expiran).
-- El cobro/liquidación NO entra aquí (pendiente de formatos de nómina — 3B).
-- ─────────────────────────────────────────────────────────────────────────────

-- 1) Antigüedad total: fecha de ingreso a la administración pública (comisionados).
--    Si es NULL, se usa empleados.fecha_ingreso (ingreso a IMATUR).
ALTER TABLE empleados ADD COLUMN IF NOT EXISTS fecha_ingreso_administracion DATE;

-- 2) Calendario de feriados (para descontar días no hábiles).
--    recurrente = TRUE → se repite cada año en el mismo mes/día (fijos).
--    recurrente = FALSE → fecha puntual de ese año (movibles: Carnaval, Semana Santa).
CREATE TABLE IF NOT EXISTS feriados (
    id          SERIAL PRIMARY KEY,
    fecha       DATE NOT NULL,
    nombre      VARCHAR(120) NOT NULL,
    recurrente  BOOLEAN NOT NULL DEFAULT TRUE,
    is_active   BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP,
    deleted_at  TIMESTAMP,
    created_by  INTEGER,
    updated_by  INTEGER,
    deleted_by  INTEGER
);
CREATE INDEX IF NOT EXISTS idx_feriados_mesdia ON feriados (EXTRACT(MONTH FROM fecha), EXTRACT(DAY FROM fecha));

-- Seed de feriados nacionales fijos + locales de Cumaná (idempotente por nombre).
INSERT INTO feriados (fecha, nombre, recurrente)
SELECT v.fecha::date, v.nombre, TRUE
FROM (VALUES
    ('2000-01-01', 'Año Nuevo'),
    ('2000-01-21', 'Santa Inés (Cumaná)'),
    ('2000-04-19', 'Declaración de Independencia'),
    ('2000-05-01', 'Día del Trabajador'),
    ('2000-05-03', 'Cruz de Mayo'),
    ('2000-06-24', 'Batalla de Carabobo'),
    ('2000-07-05', 'Día de la Independencia'),
    ('2000-07-24', 'Natalicio del Libertador'),
    ('2000-10-12', 'Día de la Resistencia Indígena'),
    ('2000-12-24', 'Nochebuena'),
    ('2000-12-25', 'Navidad'),
    ('2000-12-31', 'Fin de Año')
) AS v(fecha, nombre)
WHERE NOT EXISTS (SELECT 1 FROM feriados f WHERE f.nombre = v.nombre AND f.recurrente = TRUE);

-- 3) `vacaciones` pasa a ser un REGISTRO DE PERÍODOS (varios por año):
--    se elimina la restricción única (id_empleado, anio) que solo permitía 1/año.
--    El derecho anual y el saldo acumulado se calculan dinámicamente en el modelo.
ALTER TABLE vacaciones DROP CONSTRAINT IF EXISTS vacaciones_id_empleado_anio_key;

-- 4) RBAC: el módulo Vacaciones lo gestiona RRHH (rol 2); Admin (rol 1) usa '*'.
INSERT INTO permisos_rol (id_rol, modulo)
SELECT 2, 'VacacionesController'
WHERE NOT EXISTS (SELECT 1 FROM permisos_rol WHERE id_rol = 2 AND modulo = 'VacacionesController');
