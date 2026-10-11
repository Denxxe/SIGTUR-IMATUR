-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 088 — Oficio de formación numerado (D-NEW01)
--
-- Decidido 2026-10-11: SÍ quieren un oficio numerado para las actividades de
-- formación (talleres, charlas, inducciones), con su propio correlativo
-- `FORM-NNN/AAAA`. Las claves del correlativo existen desde la mig. 007
-- (`correlativo_oficio_formacion` / `ano_correlativo_formacion`) pero nada las
-- usaba, y `oficios_emitidos` solo sabía colgar de una salida de ruta.
--
-- No llegó un formato del cliente: el oficio usa el mismo membrete, firma y pie
-- del oficio de rutas (el que el cliente dio por bueno) y su CUERPO es editable
-- antes de emitir. Por eso se guarda el texto tal como salió (`cuerpo`): un
-- oficio emitido se reimprime idéntico aunque cambie el taller o la plantilla.
--
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

BEGIN;

ALTER TABLE oficios_emitidos ADD COLUMN IF NOT EXISTS id_taller INT;
ALTER TABLE oficios_emitidos ADD COLUMN IF NOT EXISTS cuerpo TEXT;
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'fk_oficio_taller') THEN
        ALTER TABLE oficios_emitidos ADD CONSTRAINT fk_oficio_taller
            FOREIGN KEY (id_taller) REFERENCES talleres(id) ON DELETE SET NULL;
    END IF;
END $$;
CREATE INDEX IF NOT EXISTS idx_oficios_emitidos_taller ON oficios_emitidos (id_taller);
COMMENT ON COLUMN oficios_emitidos.id_taller IS 'Oficio de formación (mig. 088): la actividad a la que corresponde.';
COMMENT ON COLUMN oficios_emitidos.cuerpo IS 'Texto del oficio tal como se emitió (párrafos separados por línea en blanco). Hoy solo lo usan los oficios de formación.';

-- Las claves del correlativo vienen de la mig. 007; por si una instalación no las tiene.
INSERT INTO configuracion_sistema (clave, valor, descripcion)
SELECT v.clave, v.valor, v.descripcion
  FROM (VALUES
        ('correlativo_oficio_formacion', '0', 'Correlativo de oficios de formación (FORM-NNN/AAAA).'),
        ('ano_correlativo_formacion', to_char(CURRENT_DATE, 'YYYY'), 'Año del correlativo de oficios de formación.')
       ) AS v(clave, valor, descripcion)
 WHERE NOT EXISTS (SELECT 1 FROM configuracion_sistema c WHERE c.clave = v.clave);

COMMIT;
