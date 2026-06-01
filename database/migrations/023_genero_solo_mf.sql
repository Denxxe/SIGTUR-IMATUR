-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 023 — género solo M/F (elimina 'O' de todos los CHECK)
-- Decisión de negocio: administrativamente solo se registra Masculino/Femenino.
-- Verificado: 0 registros con genero='O' en BD antes de ejecutar.
-- ─────────────────────────────────────────────────────────────────────────────

-- personas.genero
ALTER TABLE personas DROP CONSTRAINT IF EXISTS personas_genero_check;
ALTER TABLE personas ADD CONSTRAINT personas_genero_check
    CHECK (genero IN ('M', 'F'));

-- visitantes.genero
ALTER TABLE visitantes DROP CONSTRAINT IF EXISTS visitantes_genero_check;
ALTER TABLE visitantes ADD CONSTRAINT visitantes_genero_check
    CHECK (genero IN ('M', 'F'));

-- participantes_taller.genero_libre
ALTER TABLE participantes_taller DROP CONSTRAINT IF EXISTS participantes_taller_genero_libre_check;
ALTER TABLE participantes_taller ADD CONSTRAINT participantes_taller_genero_libre_check
    CHECK (genero_libre IN ('M', 'F'));

-- participantes_ruta.genero_libre
ALTER TABLE participantes_ruta DROP CONSTRAINT IF EXISTS participantes_ruta_genero_libre_check;
ALTER TABLE participantes_ruta ADD CONSTRAINT participantes_ruta_genero_libre_check
    CHECK (genero_libre IN ('M', 'F'));
