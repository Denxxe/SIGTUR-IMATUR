-- =============================================================================
-- Migración 003: Normalización pasantes → personas
-- Ejecutar DESPUÉS de las migraciones 001 y 002
-- psql -U postgres -d "SIGTUR-IMATUR" -f database/migrations/003_normalize_pasantes.sql
-- =============================================================================

-- 1. Agregar columna id_persona (nullable primero para poder migrar datos)
ALTER TABLE pasantes
    ADD COLUMN IF NOT EXISTS id_persona INT REFERENCES personas(id) ON DELETE RESTRICT;

-- 2. Migrar datos existentes: para cada pasante, buscar o crear persona por cédula
DO $$
DECLARE
    pas     RECORD;
    pers_id INT;
BEGIN
    FOR pas IN
        SELECT id, cedula, nombre, apellido
        FROM   pasantes
        WHERE  is_active = TRUE
          AND  id_persona IS NULL
    LOOP
        -- Intentar reusar persona existente con la misma cédula
        SELECT id INTO pers_id
        FROM   personas
        WHERE  cedula = pas.cedula
        LIMIT  1;

        -- Si no existe, crear una nueva persona mínima
        IF pers_id IS NULL THEN
            INSERT INTO personas (cedula, nombre, apellido, created_at, created_by)
            VALUES (pas.cedula, pas.nombre, pas.apellido, CURRENT_TIMESTAMP, 1)
            RETURNING id INTO pers_id;
        END IF;

        UPDATE pasantes SET id_persona = pers_id WHERE id = pas.id;
    END LOOP;
END;
$$;

-- 3. Hacer NOT NULL (falla si quedó algún pasante sin id_persona)
ALTER TABLE pasantes ALTER COLUMN id_persona SET NOT NULL;

-- 4. Eliminar columnas redundantes (ahora viven en personas)
ALTER TABLE pasantes DROP COLUMN IF EXISTS cedula;
ALTER TABLE pasantes DROP COLUMN IF EXISTS nombre;
ALTER TABLE pasantes DROP COLUMN IF EXISTS apellido;

-- Índice de rendimiento
CREATE INDEX IF NOT EXISTS idx_pasantes_persona ON pasantes (id_persona);
