-- ============================================================
-- Migración 012 — participantes_taller: campos para niño/a libre
-- Añade datos demográficos para participantes sin cédula (RN-F16)
-- ============================================================

ALTER TABLE participantes_taller
  ADD COLUMN IF NOT EXISTS fecha_nac_libre    DATE,
  ADD COLUMN IF NOT EXISTS genero_libre       CHAR(1) CHECK (genero_libre IN ('M', 'F', 'O')),
  ADD COLUMN IF NOT EXISTS parroquia_id_libre INTEGER REFERENCES parroquia(id),
  ADD COLUMN IF NOT EXISTS direccion_libre    TEXT;

COMMENT ON COLUMN participantes_taller.fecha_nac_libre    IS 'Fecha de nacimiento del niño/a participante sin cédula';
COMMENT ON COLUMN participantes_taller.genero_libre       IS 'Género del participante libre: M, F, O';
COMMENT ON COLUMN participantes_taller.parroquia_id_libre IS 'Parroquia de residencia del participante libre';
COMMENT ON COLUMN participantes_taller.direccion_libre    IS 'Dirección de residencia del participante libre';
