-- =====================================================================
-- 063 — Bienes, Fase 2: movimientos con origen/destino, autorización
--       y mantenimiento con retorno
-- =====================================================================
--
-- Ver docs/PLAN_MODULO_BIENES.md §3.5, §4.2 y §4.3.
--
-- El modelo anterior de `actividad_inventario` solo guardaba un tipo de
-- movimiento y un empleado. NO registraba de dónde a dónde iba el bien,
-- que es justo lo que describe el cliente en B-31:
--
--     1. de depósito a departamento
--     2. de departamento a depósito
--     3. de departamento a departamento
--
-- Además:
--   · B-32 — todo movimiento lo autoriza la Coordinadora de Bienes.
--   · B-33 — el mantenimiento lo ejecuta Servicios Generales y se lleva
--            registro del proceso, de quién lo hizo y de si el bien volvió.
--   · B-34 — durante el mantenimiento el bien deja de estar disponible
--            pero NO desaparece del inventario.
--
-- B-64 (respondida 2026-08-04): la Coordinadora de Bienes se identifica
-- por CARGO + DEPARTAMENTO. Se guardan ambos en `configuracion_sistema`
-- para no depender de nombres escritos a mano en el código.
--
-- Idempotente.
-- =====================================================================

BEGIN;

-- ── 1. Movimientos: origen, destino, autorización y retorno ──────────
ALTER TABLE actividad_inventario ADD COLUMN IF NOT EXISTS id_ubicacion_origen  INTEGER;
ALTER TABLE actividad_inventario ADD COLUMN IF NOT EXISTS id_ubicacion_destino INTEGER;
ALTER TABLE actividad_inventario ADD COLUMN IF NOT EXISTS autorizado_por       INTEGER;
ALTER TABLE actividad_inventario ADD COLUMN IF NOT EXISTS fecha_retorno        DATE;

ALTER TABLE actividad_inventario DROP CONSTRAINT IF EXISTS fk_actinv_ubic_origen;
ALTER TABLE actividad_inventario ADD CONSTRAINT fk_actinv_ubic_origen
    FOREIGN KEY (id_ubicacion_origen) REFERENCES ubicaciones(id) ON DELETE SET NULL;

ALTER TABLE actividad_inventario DROP CONSTRAINT IF EXISTS fk_actinv_ubic_destino;
ALTER TABLE actividad_inventario ADD CONSTRAINT fk_actinv_ubic_destino
    FOREIGN KEY (id_ubicacion_destino) REFERENCES ubicaciones(id) ON DELETE SET NULL;

ALTER TABLE actividad_inventario DROP CONSTRAINT IF EXISTS fk_actinv_autorizado;
ALTER TABLE actividad_inventario ADD CONSTRAINT fk_actinv_autorizado
    FOREIGN KEY (autorizado_por) REFERENCES empleados(id) ON DELETE SET NULL;

COMMENT ON COLUMN actividad_inventario.autorizado_por IS
    'Empleado que autoriza el movimiento — la Coordinadora de Bienes (B-32).';
COMMENT ON COLUMN actividad_inventario.fecha_retorno IS
    'Solo para salidas a mantenimiento: cuándo volvió el bien (B-33).';

-- ── 2. Tipos de movimiento según el lenguaje real del cliente ────────
-- Los tres traslados de B-31 se modelan con un único tipo 'Traslado'
-- + origen/destino, en vez de tres tipos distintos: así el reporte de
-- movimientos no depende de cómo se nombró el traslado.
UPDATE actividad_inventario SET tipo_movimiento = 'Traslado'
 WHERE tipo_movimiento IN ('Asignacion','Devolucion');
UPDATE actividad_inventario SET tipo_movimiento = 'Salida a mantenimiento'
 WHERE tipo_movimiento = 'Mantenimiento';

ALTER TABLE actividad_inventario DROP CONSTRAINT IF EXISTS actividad_inventario_tipo_movimiento_check;
ALTER TABLE actividad_inventario ADD CONSTRAINT actividad_inventario_tipo_movimiento_check CHECK (
    tipo_movimiento IN ('Traslado',
                        'Asignación de responsable',
                        'Salida a mantenimiento',
                        'Retorno de mantenimiento',
                        'Baja')
);

CREATE INDEX IF NOT EXISTS idx_actinv_tipo ON actividad_inventario (tipo_movimiento);

-- ── 3. Mantenimientos: el proceso, no solo el movimiento (B-33) ──────
CREATE TABLE IF NOT EXISTS inventario_mantenimientos (
    id                  SERIAL PRIMARY KEY,
    id_inventario       INTEGER NOT NULL REFERENCES inventario(id) ON DELETE CASCADE,
    -- Movimiento de salida que originó este mantenimiento
    id_actividad_salida INTEGER REFERENCES actividad_inventario(id) ON DELETE SET NULL,
    fecha_salida        DATE NOT NULL DEFAULT CURRENT_DATE,
    fecha_retorno       DATE,
    -- Quién lo repara: normalmente Servicios Generales (empleado interno),
    -- pero puede ser un taller externo (texto libre).
    id_empleado_encargado INTEGER REFERENCES empleados(id) ON DELETE SET NULL,
    proveedor_externo   VARCHAR(200),
    descripcion_falla   TEXT,
    trabajo_realizado   TEXT,
    costo               NUMERIC(14,2),
    resultado           VARCHAR(30),
    -- Auditoría estándar del sistema
    is_active   BOOLEAN   DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP,
    deleted_at  TIMESTAMP,
    created_by  INTEGER,
    updated_by  INTEGER,
    deleted_by  INTEGER
);

ALTER TABLE inventario_mantenimientos DROP CONSTRAINT IF EXISTS inv_mant_resultado_check;
ALTER TABLE inventario_mantenimientos ADD CONSTRAINT inv_mant_resultado_check CHECK (
    resultado IS NULL OR resultado IN ('Reparado','Sin reparación','Irrecuperable')
);

CREATE INDEX IF NOT EXISTS idx_inv_mant_bien ON inventario_mantenimientos (id_inventario);
-- Un bien no puede tener dos mantenimientos abiertos a la vez.
CREATE UNIQUE INDEX IF NOT EXISTS uq_inv_mant_abierto
    ON inventario_mantenimientos (id_inventario)
    WHERE fecha_retorno IS NULL AND is_active = TRUE;

-- ── 4. Quién autoriza: cargo + departamento (B-64) ───────────────────
INSERT INTO configuracion_sistema (clave, valor, descripcion, updated_at)
SELECT 'bienes_depto_autoriza',
       COALESCE((SELECT id::text FROM departamentos
                  WHERE is_active AND nombre ILIKE '%bienes%' ORDER BY id LIMIT 1), ''),
       'Departamento cuya jefatura autoriza los movimientos de bienes (Coordinación de Compras, Bienes y Servicios).',
       CURRENT_TIMESTAMP
 WHERE NOT EXISTS (SELECT 1 FROM configuracion_sistema WHERE clave = 'bienes_depto_autoriza');

INSERT INTO configuracion_sistema (clave, valor, descripcion, updated_at)
SELECT 'bienes_cargo_autoriza',
       COALESCE((SELECT id::text FROM cargos
                  WHERE is_active AND nombre ILIKE 'coordinador%' ORDER BY id LIMIT 1), ''),
       'Cargo que autoriza los movimientos de bienes dentro de ese departamento.',
       CURRENT_TIMESTAMP
 WHERE NOT EXISTS (SELECT 1 FROM configuracion_sistema WHERE clave = 'bienes_cargo_autoriza');

COMMIT;

-- =====================================================================
-- Verificación:
--   SELECT column_name FROM information_schema.columns
--    WHERE table_name='actividad_inventario'
--      AND column_name IN ('id_ubicacion_origen','id_ubicacion_destino',
--                          'autorizado_por','fecha_retorno');
--   SELECT to_regclass('public.inventario_mantenimientos');
--   SELECT clave, valor FROM configuracion_sistema
--    WHERE clave LIKE 'bienes_%_autoriza';
-- =====================================================================
