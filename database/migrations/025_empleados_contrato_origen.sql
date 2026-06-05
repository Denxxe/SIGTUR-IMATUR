-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 025 — Tipo de contrato + origen/nómina + comisión de servicio (R-3)
-- Decisiones de negocio (ver docs/MODELO_NEGOCIO_RRHH.md 2.1–2.2, D-RH19/D-RH27):
--   • Todo empleado nuevo es 'Contratado' → DEFAULT pasa de 'Fijo' a 'Contratado'.
--   • No existen 'Suplente' en IMATUR → se depreca y se migra a 'Contratado'.
--   • 'Comisión de Servicio' NO es un tipo de contrato excluyente: es una
--     designación ortogonal. Se separa en 3 campos:
--        tipo_contrato        → estabilidad: 'Fijo' | 'Contratado'
--        institucion_origen   → origen/nómina: 'Alcaldía' | 'Gobernación' | 'IMATUR'
--        es_comision_servicio → bool (solo aplica a Alcaldía/Gobernación)
-- Verificado: BD viva tiene 1 empleado 'Fijo' (sin Suplente ni Comisión).
-- ─────────────────────────────────────────────────────────────────────────────

BEGIN;

-- 1) Nuevas columnas (defaults seguros para filas existentes)
ALTER TABLE empleados
    ADD COLUMN IF NOT EXISTS institucion_origen character varying(20) DEFAULT 'IMATUR',
    ADD COLUMN IF NOT EXISTS es_comision_servicio boolean DEFAULT FALSE;

-- 2) Migración de datos previos al nuevo modelo
--    'Comisión de Servicio' → marca el flag (estabilidad por defecto 'Contratado';
--    institucion_origen queda 'IMATUR' por desconocerse el ente — revisar manualmente).
UPDATE empleados
   SET es_comision_servicio = TRUE,
       tipo_contrato = 'Contratado'
 WHERE tipo_contrato = 'Comisión de Servicio';

--    'Suplente' → 'Contratado' (no existen suplentes).
UPDATE empleados
   SET tipo_contrato = 'Contratado'
 WHERE tipo_contrato = 'Suplente';

-- 3) Nuevo CHECK de estabilidad (solo Fijo/Contratado) + DEFAULT Contratado
ALTER TABLE empleados DROP CONSTRAINT IF EXISTS empleados_tipo_contrato_check;
ALTER TABLE empleados
    ALTER COLUMN tipo_contrato SET DEFAULT 'Contratado';
ALTER TABLE empleados ADD CONSTRAINT empleados_tipo_contrato_check
    CHECK (tipo_contrato IN ('Fijo', 'Contratado'));

-- 4) CHECK de institución de origen
ALTER TABLE empleados DROP CONSTRAINT IF EXISTS empleados_institucion_origen_check;
ALTER TABLE empleados ADD CONSTRAINT empleados_institucion_origen_check
    CHECK (institucion_origen IN ('Alcaldía', 'Gobernación', 'IMATUR'));

COMMIT;
