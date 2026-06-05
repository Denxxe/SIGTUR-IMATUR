-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 028 — Horarios, grupos y tolerancia de puntualidad (R-6)
-- Decisiones: ver docs/MODELO_NEGOCIO_RRHH.md 1.x (D-RH33, D-RH36).
--   • Seed del catálogo `horarios` con las modalidades institucionales.
--   • `empleados.grupo_rotacion` (A/B) para la rotación de Servicios Generales.
--   • Clave de configuración `minutos_tolerancia_puntualidad` (default 15).
--   • RBAC: HorariosController para rol 2 (RRHH). Rol 1 usa '*'.
-- Idempotente (guards NOT EXISTS / ON CONFLICT).
-- ─────────────────────────────────────────────────────────────────────────────

BEGIN;

-- 1) empleados.grupo_rotacion (A/B) — solo aplica a Servicios Generales
ALTER TABLE empleados
    ADD COLUMN IF NOT EXISTS grupo_rotacion character(1);

ALTER TABLE empleados DROP CONSTRAINT IF EXISTS empleados_grupo_rotacion_check;
ALTER TABLE empleados ADD CONSTRAINT empleados_grupo_rotacion_check
    CHECK (grupo_rotacion IS NULL OR grupo_rotacion IN ('A', 'B'));

-- 2) Seed de horarios (idempotente por nombre)
INSERT INTO horarios (nombre, hora_entrada, hora_salida, dias_laborales, descripcion, created_at)
SELECT v.nombre, v.hora_entrada::time, v.hora_salida::time, v.dias, v.descr, NOW()
FROM (VALUES
    ('Estándar (8:00am–2:00pm)',            '08:00', '14:00', 'L-V', 'Horario general vigente'),
    ('OAC Matutino (7:00am–12:00pm)',       '07:00', '12:00', 'L-V', 'Recepción / OAC, sub-grupo 1'),
    ('OAC Vespertino (10:00am–2:00pm)',     '10:00', '14:00', 'L-V', 'Recepción / OAC, sub-grupo 2'),
    ('Servicios Generales (8:00am–2:00pm)', '08:00', '14:00', 'L-V (rotación A/B)', 'Días alternos según grupo A/B')
) AS v(nombre, hora_entrada, hora_salida, dias, descr)
WHERE NOT EXISTS (SELECT 1 FROM horarios h WHERE h.nombre = v.nombre);

-- 3) Clave de configuración: tolerancia de puntualidad (minutos)
INSERT INTO configuracion_sistema (clave, valor, descripcion)
SELECT 'minutos_tolerancia_puntualidad', '15',
       'Minutos de tolerancia tras la hora de entrada antes de marcar impuntualidad'
WHERE NOT EXISTS (SELECT 1 FROM configuracion_sistema WHERE clave = 'minutos_tolerancia_puntualidad');

-- 4) RBAC: HorariosController para rol 2 (RRHH)
INSERT INTO permisos_rol (id_rol, modulo)
VALUES (2, 'HorariosController')
ON CONFLICT (id_rol, modulo) DO NOTHING;

COMMIT;
