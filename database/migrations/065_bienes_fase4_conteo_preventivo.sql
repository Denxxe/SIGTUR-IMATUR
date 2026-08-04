-- =====================================================================
-- 065 — Bienes, Fase 4: conteo por cambio de gestión y mantenimiento
--       preventivo programado
-- =====================================================================
--
-- Ver docs/PLAN_MODULO_BIENES.md §12.2 (R-7 y R-8).
--
-- 1. CONTEO POR CAMBIO DE GESTIÓN (R-8) — el **dolor #2** declarado en B-05.
--    No es un inventario periódico: se dispara al cambiar de coordinador o
--    de presidencia (B-48). Lo que se verifica de cada bien es **estatus,
--    lugar y condición** (B-50); las diferencias se anotan contra lo que
--    dice el registro (B-49).
--
--    Se modela como una "foto" congelada: al abrir el conteo se copia el
--    estado que el sistema cree tener de cada bien, y luego se registra lo
--    hallado físicamente. Así la comparación queda auditable aunque el bien
--    se mueva después.
--
-- 2. MANTENIMIENTO PREVENTIVO (R-7) — B-56: avisar cuándo toca el
--    mantenimiento de aires, impresoras y computadoras.
--
-- Idempotente.
-- =====================================================================

BEGIN;

-- ── 1. Conteos físicos ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS inventario_conteos (
    id             SERIAL PRIMARY KEY,
    motivo         VARCHAR(40) NOT NULL,
    fecha_inicio   DATE NOT NULL DEFAULT CURRENT_DATE,
    fecha_cierre   DATE,
    estado         VARCHAR(20) NOT NULL DEFAULT 'Abierto',
    -- Quién entrega y quién recibe (el cambio de gestión tiene dos partes)
    id_responsable INTEGER REFERENCES empleados(id) ON DELETE SET NULL,
    observaciones  TEXT,
    is_active   BOOLEAN   DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP,
    deleted_at  TIMESTAMP,
    created_by  INTEGER,
    updated_by  INTEGER,
    deleted_by  INTEGER
);

ALTER TABLE inventario_conteos DROP CONSTRAINT IF EXISTS inv_conteo_motivo_check;
ALTER TABLE inventario_conteos ADD CONSTRAINT inv_conteo_motivo_check CHECK (
    motivo IN ('Cambio de coordinación','Cambio de presidencia','Auditoría','Otro')
);

ALTER TABLE inventario_conteos DROP CONSTRAINT IF EXISTS inv_conteo_estado_check;
ALTER TABLE inventario_conteos ADD CONSTRAINT inv_conteo_estado_check CHECK (
    estado IN ('Abierto','Cerrado')
);

-- Solo puede haber un conteo abierto a la vez.
CREATE UNIQUE INDEX IF NOT EXISTS uq_inv_conteo_abierto
    ON inventario_conteos ((estado)) WHERE estado = 'Abierto' AND is_active = TRUE;

-- ── 2. Detalle del conteo: lo esperado vs lo hallado ─────────────────
CREATE TABLE IF NOT EXISTS inventario_conteo_detalle (
    id             SERIAL PRIMARY KEY,
    id_conteo      INTEGER NOT NULL REFERENCES inventario_conteos(id) ON DELETE CASCADE,
    id_inventario  INTEGER NOT NULL REFERENCES inventario(id) ON DELETE CASCADE,
    -- Lo que el sistema creía (congelado al abrir el conteo)
    esperado_ubicacion INTEGER,
    esperado_estatus   VARCHAR(30),
    esperado_condicion VARCHAR(20),
    -- Lo que se halló físicamente
    hallado            BOOLEAN,
    hallado_ubicacion  INTEGER,
    hallado_condicion  VARCHAR(20),
    observaciones      TEXT,
    verificado_at      TIMESTAMP,
    verificado_by      INTEGER
);

CREATE INDEX IF NOT EXISTS idx_inv_conteo_det ON inventario_conteo_detalle (id_conteo);
-- Un bien no puede aparecer dos veces en el mismo conteo.
CREATE UNIQUE INDEX IF NOT EXISTS uq_inv_conteo_bien
    ON inventario_conteo_detalle (id_conteo, id_inventario);

-- ── 3. Mantenimiento preventivo programado (B-56) ────────────────────
CREATE TABLE IF NOT EXISTS inventario_mantenimiento_plan (
    id              SERIAL PRIMARY KEY,
    id_inventario   INTEGER NOT NULL REFERENCES inventario(id) ON DELETE CASCADE,
    frecuencia_meses INTEGER NOT NULL DEFAULT 6,
    ultima_fecha    DATE,
    proxima_fecha   DATE NOT NULL,
    descripcion     TEXT,
    is_active   BOOLEAN   DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP,
    deleted_at  TIMESTAMP,
    created_by  INTEGER,
    updated_by  INTEGER,
    deleted_by  INTEGER
);

ALTER TABLE inventario_mantenimiento_plan DROP CONSTRAINT IF EXISTS inv_plan_frec_check;
ALTER TABLE inventario_mantenimiento_plan ADD CONSTRAINT inv_plan_frec_check CHECK (
    frecuencia_meses BETWEEN 1 AND 60
);

CREATE INDEX IF NOT EXISTS idx_inv_plan_proxima ON inventario_mantenimiento_plan (proxima_fecha);
-- Un solo plan activo por bien.
CREATE UNIQUE INDEX IF NOT EXISTS uq_inv_plan_bien
    ON inventario_mantenimiento_plan (id_inventario) WHERE is_active = TRUE;

-- ── 4. Umbrales de aviso, editables en /config ───────────────────────
INSERT INTO configuracion_sistema (clave, valor, descripcion, updated_at)
SELECT 'dias_aviso_garantia', '30',
       'Días de antelación para avisar que la garantía de un bien está por vencer.',
       CURRENT_TIMESTAMP
 WHERE NOT EXISTS (SELECT 1 FROM configuracion_sistema WHERE clave = 'dias_aviso_garantia');

INSERT INTO configuracion_sistema (clave, valor, descripcion, updated_at)
SELECT 'dias_aviso_mantenimiento', '15',
       'Días de antelación para avisar que toca el mantenimiento preventivo de un bien.',
       CURRENT_TIMESTAMP
 WHERE NOT EXISTS (SELECT 1 FROM configuracion_sistema WHERE clave = 'dias_aviso_mantenimiento');

INSERT INTO configuracion_sistema (clave, valor, descripcion, updated_at)
SELECT 'dias_alerta_sin_codificar', '30',
       'Días que puede llevar un bien esperando el código de la Alcaldía antes de avisar.',
       CURRENT_TIMESTAMP
 WHERE NOT EXISTS (SELECT 1 FROM configuracion_sistema WHERE clave = 'dias_alerta_sin_codificar');

COMMIT;

-- =====================================================================
-- Verificación:
--   SELECT to_regclass('public.inventario_conteos'),
--          to_regclass('public.inventario_conteo_detalle'),
--          to_regclass('public.inventario_mantenimiento_plan');
--   SELECT clave, valor FROM configuracion_sistema WHERE clave LIKE 'dias_a%';
-- =====================================================================
