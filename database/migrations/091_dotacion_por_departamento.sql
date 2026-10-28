-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 091 — La dotación de bienes es POR DEPARTAMENTO (B-63)
--
-- La mig. 067 modeló la suficiencia como «unidades de cada categoría por
-- empleado». El cliente cerró el criterio al revés: «cada departamento tendrá
-- una cantidad de bienes» (el aire acondicionado es del espacio, no de la
-- persona). Así que `inventario_dotacion` pasa a guardar, por cada par
-- departamento + categoría, cuántas unidades debería tener ese departamento.
--
-- Las filas que sembró la mig. 067 (2 muebles / 1 equipo / 0,5 teléfonos por
-- empleado) eran un punto de partida supuesto, no cifras del cliente, y no se
-- pueden traducir a «por departamento» sin inventarlas: se eliminan. La tabla
-- queda vacía hasta que Bienes cargue las cifras reales desde
-- Inventario → Suficiencia.
--
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

BEGIN;

ALTER TABLE inventario_dotacion
    ADD COLUMN IF NOT EXISTS id_departamento INTEGER REFERENCES departamentos(id) ON DELETE CASCADE;
ALTER TABLE inventario_dotacion
    ADD COLUMN IF NOT EXISTS unidades INTEGER;

-- Las dotaciones por empleado no tienen departamento: son las de la mig. 067.
DELETE FROM inventario_dotacion WHERE id_departamento IS NULL;

ALTER TABLE inventario_dotacion DROP CONSTRAINT IF EXISTS inv_dotacion_unidades_check;
ALTER TABLE inventario_dotacion DROP COLUMN IF EXISTS unidades_por_empleado;
ALTER TABLE inventario_dotacion ALTER COLUMN id_departamento SET NOT NULL;
ALTER TABLE inventario_dotacion ALTER COLUMN unidades SET NOT NULL;
ALTER TABLE inventario_dotacion ADD CONSTRAINT inv_dotacion_unidades_check
    CHECK (unidades >= 1 AND unidades <= 9999);

-- Una dotación vigente por departamento y categoría.
DROP INDEX IF EXISTS uq_inv_dotacion_categoria;
CREATE UNIQUE INDEX IF NOT EXISTS uq_inv_dotacion_depto_categoria
    ON inventario_dotacion (id_departamento, id_categoria) WHERE is_active = TRUE;

COMMENT ON TABLE inventario_dotacion IS
    'B-63: cuántas unidades de cada categoría debería tener cada DEPARTAMENTO. El reporte de suficiencia compara contra los bienes ubicados en ese departamento (sin depósito ni bajas).';
COMMENT ON COLUMN inventario_dotacion.unidades IS
    'Unidades esperadas de la categoría en el departamento (mig. 091; antes unidades_por_empleado).';

COMMIT;

-- =====================================================================
-- Verificación:
--   SELECT column_name FROM information_schema.columns
--    WHERE table_name = 'inventario_dotacion' ORDER BY ordinal_position;
--   -- Debe listar id_departamento y unidades, y NO unidades_por_empleado.
-- =====================================================================
