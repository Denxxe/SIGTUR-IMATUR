-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 032 — Permisos y reposos (R-8, sin vacaciones)
-- Decisión: ver docs/MODELO_NEGOCIO_RRHH.md 4.x (D-RH32, D-RH24).
--   • `categoria` distingue Reposo vs Permiso (vía select); Vacaciones queda para
--     después (fórmula pendiente — D-RH04/05/NEW05).
--   • `tipo_permiso` adopta la taxonomía confirmada.
--   • `duracion` (texto libre: "72 horas", "10 días", "6 meses") captura el "TIEMPO"
--     de la fuente; "En curso/Concluido" se DERIVA de fecha_fin vs hoy (no se almacena).
--   • RBAC: PermisosController para rol 2 (RRHH). Rol 1 usa '*'.
-- Tabla con 0 filas → sin migración de datos.
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

BEGIN;

ALTER TABLE permisos_laborales
    ADD COLUMN IF NOT EXISTS categoria character varying(20),
    ADD COLUMN IF NOT EXISTS duracion  character varying(40);

ALTER TABLE permisos_laborales DROP CONSTRAINT IF EXISTS permisos_categoria_check;
ALTER TABLE permisos_laborales ADD CONSTRAINT permisos_categoria_check
    CHECK (categoria IS NULL OR categoria IN ('Reposo', 'Permiso', 'Vacaciones'));

-- Nueva taxonomía de tipo_permiso (tabla vacía → reemplazo directo del CHECK)
ALTER TABLE permisos_laborales DROP CONSTRAINT IF EXISTS permisos_tipo_check;
ALTER TABLE permisos_laborales ADD CONSTRAINT permisos_tipo_check
    CHECK (tipo_permiso IN (
        'Reposo médico', 'Médico familiar', 'Diligencia', 'Duelo',
        'Maternidad/Paternidad', 'Personal', 'Estudios', 'Otro'
    ));

INSERT INTO permisos_rol (id_rol, modulo)
VALUES (2, 'PermisosController')
ON CONFLICT (id_rol, modulo) DO NOTHING;

COMMIT;
