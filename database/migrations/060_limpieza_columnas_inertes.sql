-- =====================================================================
-- 060 — Limpieza de columnas y tablas inertes (Turismo / Formación)
-- =====================================================================
--
-- Auditoría 2026-08-04: estas estructuras existían en la BD pero NINGUNA
-- parte del sistema las escribía. Eran peso muerto que además hacía que
-- los reportes mostraran datos falsos.
--
-- Cierra los hallazgos H-09 (columnas inertes) y H-10 (tablas sin UI).
--
--  1. rutas.nombre_facilitador_externo
--       Solo se LEÍA en un reporte (ReportesController::rutas), nunca se
--       capturaba en ninguna pantalla. Siempre NULL.
--
--  2. participantes_ruta.id_institucion  +  tabla instituciones_externas
--       RutasController insertaba SIEMPRE null. La tabla quedó en 0 filas
--       y sin UI desde que se retiró el módulo de instituciones externas
--       (2026-05-31). Reintroducirlo sería revertir esa decisión.
--
--  3. talleres.id_oficio  +  tabla oficios
--       Cero referencias en TalleresController, el modelo Taller y sus
--       vistas. La tabla `oficios` (oficios recibidos externos → IMATUR)
--       nunca tuvo CRUD; sus únicas filas eran basura de prueba.
--
-- NO se toca `rutas.tiene_tarifa` / `tarifa_monto`: sigue pendiente de
-- decisión del cliente (D-RT02, tarifa de Cumaná Histórica).
--
-- Tampoco se toca `oficios_emitidos` (oficios SALIENTES generados desde
-- rutas), que sí está en uso — no confundir con `oficios`.
--
-- Idempotente: se puede ejecutar varias veces sin error.
--
-- Orden: primero las columnas que referencian las tablas (sus FK caen
-- con la columna), después las tablas.
-- =====================================================================

BEGIN;

-- 1. Facilitador externo de rutas (nunca se capturó) -------------------
ALTER TABLE rutas
    DROP COLUMN IF EXISTS nombre_facilitador_externo;

-- 2. Institución del participante de ruta (siempre null) ---------------
ALTER TABLE participantes_ruta
    DROP COLUMN IF EXISTS id_institucion;

-- 3. Oficio recibido asociado al taller (sin uso) ----------------------
ALTER TABLE talleres
    DROP COLUMN IF EXISTS id_oficio;

-- 4. Tablas que quedan huérfanas tras lo anterior ----------------------
DROP TABLE IF EXISTS oficios;
DROP TABLE IF EXISTS instituciones_externas;

COMMIT;

-- =====================================================================
-- Verificación (debe devolver 0 filas):
--
--   SELECT column_name FROM information_schema.columns
--    WHERE (table_name='rutas'              AND column_name='nombre_facilitador_externo')
--       OR (table_name='participantes_ruta' AND column_name='id_institucion')
--       OR (table_name='talleres'           AND column_name='id_oficio');
--
--   SELECT table_name FROM information_schema.tables
--    WHERE table_name IN ('oficios','instituciones_externas');
-- =====================================================================
