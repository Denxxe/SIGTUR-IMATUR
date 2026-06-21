-- 041_fecha_vencimiento_contrato.sql
-- B4 — Separa el VENCIMIENTO del contrato (fecha futura, solo Contratados) de la
-- FECHA DE EGRESO real (salida del trabajador, gestionada por el módulo R-12).
--
-- Motivo: `fecha_egreso` se usaba con dos significados en conflicto:
--   · Empleado::all() filtra `fecha_egreso IS NULL` (= activo)  → un vencimiento
--     futuro escrito ahí hacía DESAPARECER al empleado de la nómina activa.
--   · DashboardController la trataba como vencimiento futuro para la alerta
--     "contrato por vencer".
-- Esta migración crea una columna dedicada y migra cualquier valor futuro.
-- Idempotente.

BEGIN;

ALTER TABLE empleados ADD COLUMN IF NOT EXISTS fecha_vencimiento_contrato DATE;

-- Mueve a la nueva columna cualquier "egreso" que en realidad sea un vencimiento
-- futuro (dato mal ubicado por el modelo anterior) y libera fecha_egreso.
UPDATE empleados
   SET fecha_vencimiento_contrato = fecha_egreso,
       fecha_egreso = NULL
 WHERE fecha_egreso IS NOT NULL
   AND fecha_egreso > CURRENT_DATE;

-- Los empleados Fijos no tienen vencimiento por tiempo.
UPDATE empleados
   SET fecha_vencimiento_contrato = NULL
 WHERE tipo_contrato = 'Fijo';

COMMIT;
