-- Migration 011: link visitantes to personas table
-- Personal data now lives in personas; visitantes holds visitor-context data only

ALTER TABLE visitantes
    ADD COLUMN IF NOT EXISTS id_persona INT REFERENCES personas(id);

-- Migrate existing visitante records → create persona entries and link them
DO $$
DECLARE
    v   RECORD;
    pid INT;
BEGIN
    FOR v IN
        SELECT * FROM visitantes
        WHERE is_active = TRUE AND id_persona IS NULL
    LOOP
        pid := NULL;

        -- Try to find existing persona by cedula
        IF v.cedula IS NOT NULL THEN
            SELECT id INTO pid
            FROM   personas
            WHERE  cedula = v.cedula AND is_active = TRUE
            LIMIT  1;
        END IF;

        -- Create persona if none found
        IF pid IS NULL THEN
            INSERT INTO personas (cedula, nombre, apellido, telefono, genero, correo, created_by)
            VALUES (v.cedula, v.nombre, v.apellido, v.telefono, v.genero, v.correo, v.created_by)
            RETURNING id INTO pid;
        END IF;

        UPDATE visitantes SET id_persona = pid WHERE id = v.id;
    END LOOP;
END $$;

-- visitantes.nombre y apellido pasan a ser nullable (datos viven en personas)
ALTER TABLE visitantes ALTER COLUMN nombre   DROP NOT NULL;
ALTER TABLE visitantes ALTER COLUMN apellido DROP NOT NULL;
