-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 077 — Acta de Desincorporación por lote (C-5)
--
-- Procedimiento nuevo notificado por la Alcaldía el 2026-09-02
-- (PLAN_MODULO_BIENES.md §2-ter): el acta de baja pasa a ser **Acta de
-- Desincorporación**, es **por lote** y la Alcaldía la **firma y sella** — ese
-- sello ES el aval del retiro. El oficio de retiro sale del alcance.
--
-- Hasta ahora `Inventario::marcarRetirado()` confirmaba el retiro **bien por
-- bien** y la entidad «acta» no existía: no había forma de saber qué bienes
-- fueron en la misma acta ni de reimprimirla.
--
-- EL CICLO QUE MODELA ESTA TABLA
--   1. El bien se desincorpora    → estatus «Desincorporado» · «Por retirar»
--                                    (sale del inventario activo, sigue en la
--                                    sede: B-38 y B-67)
--   2. Se arma el acta con varios → los bienes quedan enganchados al acta
--   3. Se imprime y se lleva      → la Alcaldía firma y sella
--   4. Se registra el acta firmada → TODOS sus bienes pasan a «Retirado»
--      (con su escaneado adjunto)
--
-- ⚠️ El FORMATO oficial del acta todavía no llegó (§3.4 del BACKLOG). Esta
-- migración construye el **flujo y los datos**, que no dependen de él; la vista
-- imprimible es provisional y se sustituye cuando el cliente entregue el
-- formato, sin tocar nada de lo que hay aquí.
--
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS inventario_actas_desincorporacion (
    id               SERIAL PRIMARY KEY,
    numero           VARCHAR(20)  NOT NULL,          -- correlativo propio, "003/2026"
    fecha            DATE         NOT NULL DEFAULT CURRENT_DATE,
    motivo           TEXT,                            -- motivo general del lote
    observacion      TEXT,

    -- Firma y sello de la Alcaldía: mientras estén vacíos, el acta está emitida
    -- pero sus bienes siguen «Por retirar». Al llenarlos, pasan a «Retirado».
    fecha_firma      DATE,
    recibido_por     VARCHAR(200),                    -- quién firma por la Alcaldía
    archivo_url      VARCHAR(255),                    -- escaneado del acta sellada
    nombre_original  VARCHAR(255),

    is_active        BOOLEAN   NOT NULL DEFAULT TRUE,
    anulado_motivo   TEXT,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP,
    deleted_at       TIMESTAMP,
    created_by       INTEGER,
    updated_by       INTEGER,
    deleted_by       INTEGER
);

CREATE INDEX IF NOT EXISTS idx_actas_desinc_fecha ON inventario_actas_desincorporacion (fecha DESC);

-- Un bien pertenece a lo sumo a un acta. Al anularla el vínculo se limpia y el
-- bien vuelve a la bolsa de «desincorporados sin acta».
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS id_acta_desincorporacion INTEGER;

DO $$
BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'fk_inventario_acta_desinc') THEN
    ALTER TABLE inventario
      ADD CONSTRAINT fk_inventario_acta_desinc
      FOREIGN KEY (id_acta_desincorporacion)
      REFERENCES inventario_actas_desincorporacion(id) ON DELETE SET NULL;
  END IF;
END $$;

CREATE INDEX IF NOT EXISTS idx_inventario_acta_desinc ON inventario (id_acta_desincorporacion);

COMMENT ON TABLE inventario_actas_desincorporacion IS
  'Acta de Desincorporación por lote (C-5). La firma y el sello de la Alcaldía (fecha_firma + archivo_url) son el aval del retiro: al registrarlos, todos los bienes del acta pasan a retirado_alcaldia = TRUE.';

-- Correlativo propio, con el mismo mecanismo por módulo que ya usan rutas,
-- pasantes, constancias y los oficios de bienes (ConfigSistema::generarNumeroOficio).
INSERT INTO configuracion_sistema (clave, valor, descripcion) VALUES
  ('correlativo_oficio_acta', '0',
   'Último correlativo de Acta de Desincorporación emitida en el año en curso'),
  ('ano_correlativo_acta', EXTRACT(YEAR FROM CURRENT_DATE)::TEXT,
   'Año del correlativo de actas de desincorporación (se reinicia automáticamente)')
ON CONFLICT (clave) DO NOTHING;
