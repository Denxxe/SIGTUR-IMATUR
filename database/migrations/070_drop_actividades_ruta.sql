-- =====================================================================
-- 070 — Elimina la tabla huérfana `actividades_ruta` (cierra H-13)
-- =====================================================================
--
-- El módulo "Actividades de ruta" se retiró el 2026-05-31 junto con
-- "Instituciones externas". La tabla quedó atrás: **cero referencias** en
-- `app/` (ni controlador, ni modelo, ni vista, ni consulta de reporte),
-- **0 filas** y **0 registros** en `audit_logs` que la mencionen —
-- verificado antes de soltarla. Mismo caso que las cinco estructuras
-- eliminadas en la mig. 060.
--
-- Por eso no hace falta preservar etiqueta en `auditoria/index.php`: a
-- diferencia de `id_oficio`/`instituciones_externas` (que sí aparecen en
-- registros históricos y se conservaron a propósito para humanizarlos),
-- esta tabla no dejó rastro.
--
-- Se elimina también su `setval` de `009_fix_sequences.sql`: la migración
-- 009 resincroniza secuencias y fallaría al referenciar una tabla que ya
-- no existe si se corriera sobre una instalación actualizada.
--
-- Idempotente. `CASCADE` cubre sus dos FKs (`fk_act_ruta` a rutas,
-- `fk_act_ruta_emp` a empleados), el índice `idx_act_ruta_fecha`, la clave
-- primaria y la secuencia (que es OWNED BY la columna).
-- =====================================================================

BEGIN;

DROP TABLE IF EXISTS actividades_ruta CASCADE;

COMMIT;

-- =====================================================================
-- Verificación:
--   SELECT tablename FROM pg_tables
--    WHERE schemaname='public' AND tablename='actividades_ruta';
--   -> 0 filas.
--
--   SELECT COUNT(*) FROM pg_class WHERE relname='actividades_ruta_id_seq';
--   -> 0 (la secuencia cae con la tabla por ser OWNED BY su columna).
--
-- Tras esta migración quedan 55 tablas.
-- =====================================================================
