-- =====================================================================
-- 064 — Bienes, Fase 3 (parte 1): expediente documental y recepción
--       del BM-1 consolidado
-- =====================================================================
--
-- Ver docs/PLAN_MODULO_BIENES.md §2-bis, §3.6 y §4.1.
--
-- Esta migración cubre la parte de la Fase 3 que NO depende de recibir
-- los formatos físicos del cliente. La GENERACIÓN de documentos (informe
-- de bienes nuevos, acta de asignación, acta de baja) queda pendiente
-- hasta tener los formatos reales — ver §9 del plan.
--
-- 1. `inventario_documentos` — cada bien acumula respaldos: factura,
--    informe de la Alcaldía, oficio de donación, actas (B-16 a B-19).
--    Mismo patrón ya probado en RRHH con `expediente_documentos`: el
--    binario vive FUERA del web root y se sirve por id vía DescargaController.
--
-- 2. `inventario_consolidados_bm1` — el BM-1 es un documento ENTRANTE: la
--    Alcaldía lo elabora y se lo devuelve a IMATUR ya codificado. Cada
--    recepción se registra aquí, se adjunta el archivo y desde ella se
--    codifican los bienes en lote. `inventario.id_consolidado_bm1` deja
--    la trazabilidad de en qué BM-1 se codificó cada bien.
--
-- Idempotente.
-- =====================================================================

BEGIN;

-- ── 1. Recepciones del BM-1 consolidado ──────────────────────────────
CREATE TABLE IF NOT EXISTS inventario_consolidados_bm1 (
    id               SERIAL PRIMARY KEY,
    fecha_recepcion  DATE NOT NULL DEFAULT CURRENT_DATE,
    -- Fecha que trae impresa el formulario (puede diferir de la recepción)
    fecha_documento  DATE,
    referencia       VARCHAR(120),
    archivo_url      VARCHAR(255),
    nombre_original  VARCHAR(255),
    observaciones    TEXT,
    is_active   BOOLEAN   DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP,
    deleted_at  TIMESTAMP,
    created_by  INTEGER,
    updated_by  INTEGER,
    deleted_by  INTEGER
);

COMMENT ON TABLE inventario_consolidados_bm1 IS
    'Formularios BM-1 recibidos de la Alcaldía (documento entrante, ya codificado).';

ALTER TABLE inventario ADD COLUMN IF NOT EXISTS id_consolidado_bm1 INTEGER;
ALTER TABLE inventario DROP CONSTRAINT IF EXISTS fk_inventario_bm1;
ALTER TABLE inventario ADD CONSTRAINT fk_inventario_bm1
    FOREIGN KEY (id_consolidado_bm1) REFERENCES inventario_consolidados_bm1(id) ON DELETE SET NULL;

COMMENT ON COLUMN inventario.id_consolidado_bm1 IS
    'BM-1 en el que la Alcaldía asignó el código de este bien.';

-- ── 2. Documentos de respaldo por bien ───────────────────────────────
CREATE TABLE IF NOT EXISTS inventario_documentos (
    id              SERIAL PRIMARY KEY,
    id_inventario   INTEGER NOT NULL REFERENCES inventario(id) ON DELETE CASCADE,
    tipo_documento  VARCHAR(50) NOT NULL,
    archivo_url     VARCHAR(255) NOT NULL,
    nombre_original VARCHAR(255),
    observaciones   TEXT,
    is_active   BOOLEAN   DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP,
    deleted_at  TIMESTAMP,
    created_by  INTEGER,
    updated_by  INTEGER,
    deleted_by  INTEGER
);

CREATE INDEX IF NOT EXISTS idx_inv_doc_bien ON inventario_documentos (id_inventario);

COMMENT ON TABLE inventario_documentos IS
    'Respaldos del bien: factura, informe de la Alcaldía, oficio de donación, actas (B-16 a B-19).';

COMMIT;

-- =====================================================================
-- Verificación:
--   SELECT to_regclass('public.inventario_documentos'),
--          to_regclass('public.inventario_consolidados_bm1');
--   SELECT column_name FROM information_schema.columns
--    WHERE table_name='inventario' AND column_name='id_consolidado_bm1';
-- =====================================================================
