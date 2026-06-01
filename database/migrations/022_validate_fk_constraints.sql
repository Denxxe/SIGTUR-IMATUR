-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 022 — validar FKs que quedaron NOT VALID
-- ─────────────────────────────────────────────────────────────────────────────
-- Antes de ejecutar se verificó que no existen filas huérfanas en ninguna de
-- las 7 FKs afectadas. VALIDATE CONSTRAINT solo adquiere un lock de lectura
-- (ACCESS SHARE) por lo que no bloquea lecturas ni escrituras en producción.
-- ─────────────────────────────────────────────────────────────────────────────

ALTER TABLE municipio             VALIDATE CONSTRAINT municipio_created_by_fkey;
ALTER TABLE parroquia             VALIDATE CONSTRAINT parroquia_create_by_fkey;
ALTER TABLE parroquia             VALIDATE CONSTRAINT parroquia_update_by_fkey;
ALTER TABLE parroquia             VALIDATE CONSTRAINT parroquia_delete_by_fkey;
ALTER TABLE personas              VALIDATE CONSTRAINT personas_parroquia_id_fkey;
ALTER TABLE ubicaciones           VALIDATE CONSTRAINT "ubicaciones_departamento _d_fkey";
ALTER TABLE ubicaciones_formacion VALIDATE CONSTRAINT ubicaciones_formacion_parroquia_fkey;
