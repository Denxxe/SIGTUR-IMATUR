-- =====================================================================
-- 067 — Bienes: respuestas del cliente B-63, B-65, B-66 y B-67
-- =====================================================================
--
-- Ver docs/PLAN_MODULO_BIENES.md §12.3 (respuestas del 2026-08-05).
--
-- B-66 → Se ELIMINAN `tipo_bien` y `cantidad` (R-10).
--        Se agregaron en la mig. 044 respondiendo a D-IN05, pero el
--        levantamiento las dejó sin sentido: no llevan consumibles (B-07)
--        y cada bien se registra individualmente aunque se compre en lote
--        (B-09). El cliente confirmó su eliminación.
--
-- B-67 → El bien dado de baja sigue físicamente en IMATUR hasta que la
--        Alcaldía lo retira. Se distingue con `retirado_alcaldia`:
--        "Dado de baja · Por retirar"  vs  "Dado de baja · Retirado".
--        No es un estatus nuevo: sigue fuera del inventario activo (B-38),
--        solo se marca si ya se lo llevaron.
--
-- B-65 → La Oficina de Información Turística del Aeropuerto de Cumaná
--        pasa a ser un DEPARTAMENTO más, con su propio coordinador (que
--        por la mig. 066 será automáticamente el responsable de sus
--        bienes). Verificado antes de crearla: NO existía en
--        `departamentos`, ni en el organigrama oficial (Manual
--        Descriptivo de Cargos, abril 2024), ni en los documentos de
--        RRHH — el único rastro era `ubicaciones.sede`.
--        Se cuelga de Presidencia como las demás Oficinas de staff
--        (OAC, Consultoría Jurídica, Auditoría Interna, Relaciones
--        Inter-Institucionales). ⚠️ Ubicación jerárquica a confirmar:
--        el organigrama oficial no la contempla.
--
-- B-63 → El umbral de mobiliario se define **por número de empleados**
--        del departamento. `inventario_dotacion` guarda cuántas unidades
--        de cada categoría corresponden por empleado; el reporte compara
--        lo que hay contra lo que debería haber.
--
-- Idempotente.
-- =====================================================================

BEGIN;

-- ── B-66 / R-10: fuera las columnas sin uso ──────────────────────────
ALTER TABLE inventario DROP CONSTRAINT IF EXISTS inventario_tipo_bien_check;
ALTER TABLE inventario DROP CONSTRAINT IF EXISTS inventario_cantidad_check;
ALTER TABLE inventario DROP COLUMN IF EXISTS tipo_bien;
ALTER TABLE inventario DROP COLUMN IF EXISTS cantidad;

-- ── B-67: bien dado de baja pendiente de retiro por la Alcaldía ──────
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS retirado_alcaldia BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS fecha_retiro      DATE;

COMMENT ON COLUMN inventario.retirado_alcaldia IS
    'Solo aplica a bienes dados de baja: FALSE = sigue en IMATUR esperando que la Alcaldía lo retire ("Por retirar"); TRUE = ya se lo llevaron (B-67).';

CREATE INDEX IF NOT EXISTS idx_inventario_por_retirar
    ON inventario (retirado_alcaldia)
    WHERE estatus = 'Dado de baja' AND retirado_alcaldia = FALSE;

-- ── B-65: la sede del aeropuerto como departamento propio ────────────
INSERT INTO departamentos (nombre, descripcion, tipo_unidad, id_padre, is_active, created_at)
SELECT 'Oficina de Información Turística (Aeropuerto)',
       'Sede del Aeropuerto de Cumaná. Atiende al turista a su llegada; sus bienes se controlan aparte de la sede principal (B-24/B-65).',
       'Oficina',
       (SELECT id FROM departamentos WHERE tipo_unidad = 'Presidencia' AND is_active ORDER BY id LIMIT 1),
       TRUE, CURRENT_TIMESTAMP
 WHERE NOT EXISTS (
    SELECT 1 FROM departamentos WHERE nombre ILIKE '%Aeropuerto%' AND is_active
 );

-- ── B-63: dotación esperada por empleado, por categoría ──────────────
CREATE TABLE IF NOT EXISTS inventario_dotacion (
    id                   SERIAL PRIMARY KEY,
    id_categoria         INTEGER NOT NULL REFERENCES categorias(id) ON DELETE CASCADE,
    unidades_por_empleado NUMERIC(6,2) NOT NULL DEFAULT 1,
    observaciones        TEXT,
    is_active   BOOLEAN   DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP,
    created_by  INTEGER,
    updated_by  INTEGER
);

ALTER TABLE inventario_dotacion DROP CONSTRAINT IF EXISTS inv_dotacion_unidades_check;
ALTER TABLE inventario_dotacion ADD CONSTRAINT inv_dotacion_unidades_check
    CHECK (unidades_por_empleado > 0 AND unidades_por_empleado <= 99);

CREATE UNIQUE INDEX IF NOT EXISTS uq_inv_dotacion_categoria
    ON inventario_dotacion (id_categoria) WHERE is_active = TRUE;

COMMENT ON TABLE inventario_dotacion IS
    'B-63: cuántas unidades de cada categoría corresponden POR EMPLEADO. El reporte de suficiencia compara lo que hay en cada departamento contra lo que debería haber según su personal.';

-- Punto de partida razonable para las categorías donde el criterio
-- "por empleado" tiene sentido. El resto (herramientas, material
-- turístico, bienes culturales…) no se dota por persona, así que no se
-- siembra: sin fila, la categoría simplemente no se evalúa.
INSERT INTO inventario_dotacion (id_categoria, unidades_por_empleado, observaciones, created_at)
SELECT c.id, v.u, v.obs, CURRENT_TIMESTAMP
  FROM (VALUES
    ('Mobiliario de oficina',   2.0, 'Al menos una silla y un puesto de trabajo por empleado'),
    ('Equipos de computación',  1.0, 'Un equipo por empleado'),
    ('Equipos de comunicación', 0.5, 'Aproximadamente un punto telefónico por cada dos empleados')
  ) AS v(cat, u, obs)
  JOIN categorias c ON c.nombre = v.cat AND c.is_active = TRUE
 WHERE NOT EXISTS (
    SELECT 1 FROM inventario_dotacion d WHERE d.id_categoria = c.id AND d.is_active = TRUE
 );

COMMIT;

-- =====================================================================
-- Verificación:
--   SELECT column_name FROM information_schema.columns
--    WHERE table_name='inventario'
--      AND column_name IN ('tipo_bien','cantidad','retirado_alcaldia','fecha_retiro');
--   SELECT nombre, tipo_unidad FROM departamentos WHERE nombre ILIKE '%Aeropuerto%';
--   SELECT c.nombre, d.unidades_por_empleado
--     FROM inventario_dotacion d JOIN categorias c ON c.id = d.id_categoria;
-- =====================================================================
