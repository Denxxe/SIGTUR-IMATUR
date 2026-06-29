-- =====================================================================
-- 053_foto_persona.sql
-- Carnetización: foto de la persona (empleados y pasantes comparten
-- la tabla personas vía id_persona, así una sola foto sirve para ambos).
-- Guarda solo el NOMBRE del archivo; el binario vive en
-- storage/uploads/fotos/ y se sirve por DescargaController::foto().
-- Idempotente.
-- =====================================================================
BEGIN;

ALTER TABLE personas ADD COLUMN IF NOT EXISTS foto_url VARCHAR(255);

COMMENT ON COLUMN personas.foto_url IS 'Nombre del archivo de foto (carnetización). Ruta real en storage/uploads/fotos/.';

COMMIT;
