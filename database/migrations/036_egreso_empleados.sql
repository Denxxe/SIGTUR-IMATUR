-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 036 — Egreso / desincorporación de empleados (R-12)
-- Regla de negocio:
--   • Al "dar de baja" a un trabajador (renuncia, despido, jubilación, fin de
--     contrato, fallecimiento u otro) NO se borra el registro: se marca como
--     EGRESADO (fecha_egreso + motivo_egreso). El expediente sigue siendo un
--     registro histórico válido (is_active = TRUE), consultable y con acceso a
--     todos sus datos para generar constancias (tiempo de servicio, bancos…).
--   • is_active = FALSE queda reservado para registros creados por error
--     (papelera), NO para egresos reales.
--   • Reingreso permitido conservando el historial: cada egreso queda en
--     empleados_egresos; al reingresar se cierra esa fila (fecha_reingreso) y se
--     limpia el egreso vigente en empleados.
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

BEGIN;

-- Egreso vigente sobre el propio empleado (fecha_egreso ya existía en mig. 029)
ALTER TABLE empleados ADD COLUMN IF NOT EXISTS motivo_egreso      character varying(40);
ALTER TABLE empleados ADD COLUMN IF NOT EXISTS observacion_egreso text;

-- Historial de egresos / reingresos (un empleado puede egresar y reingresar varias veces)
CREATE TABLE IF NOT EXISTS empleados_egresos (
    id                     SERIAL PRIMARY KEY,
    id_empleado            integer NOT NULL REFERENCES empleados(id) ON DELETE CASCADE,
    fecha_egreso           date NOT NULL,
    motivo_egreso          character varying(40) NOT NULL,
    observacion            text,
    fecha_reingreso        date,
    reingreso_observacion  text,
    created_at             timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by             integer,
    reingreso_at           timestamp without time zone,
    reingreso_by           integer
);
CREATE INDEX IF NOT EXISTS idx_emp_egresos_empleado ON empleados_egresos(id_empleado);
-- Un empleado sólo puede tener un egreso "abierto" (sin reingreso) a la vez
CREATE UNIQUE INDEX IF NOT EXISTS uq_emp_egreso_abierto
    ON empleados_egresos(id_empleado) WHERE fecha_reingreso IS NULL;

COMMIT;
