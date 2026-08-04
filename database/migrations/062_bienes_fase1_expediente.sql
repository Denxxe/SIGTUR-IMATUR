-- =====================================================================
-- 062 — Bienes, Fase 1: de CRUD genérico a expediente administrativo
-- =====================================================================
--
-- Base: levantamiento con el cliente (B-01…B-59) + Formulario BM-1 real.
-- Ver docs/PLAN_MODULO_BIENES.md §3 y §10.
--
-- Cambios estructurales de esta fase:
--
--   1. Se separa ESTATUS (administrativo) de CONDICION (físico). Era el
--      origen del bug H-04: un bien dado de baja seguía contando como
--      activo porque ambos ejes vivían en la misma columna.
--
--   2. El código oficial de la Alcaldía deja de ser texto libre y pasa a
--      sus cuatro partes reales: grupo-subgrupo-sección + N° de orden
--      (BM-1: `2-01-108`, orden `084`). `codigo_bn` se conserva como el
--      código compuesto que arma el modelo, para no romper los reportes.
--
--   3. Dos ejes de clasificación independientes: el código oficial (para
--      la Alcaldía) y la CATEGORÍA interna (para los reportes de la
--      Presidencia). El BM-1 demostró que el código NO clasifica: sillas,
--      mesas, aire acondicionado y router comparten todos `2-01-108`.
--
--   4. Datos de adquisición (origen, costo, proveedor, garantía) y
--      responsable nominal único.
--
--   5. Ubicaciones con sede (hay dos: principal y aeropuerto) y marca de
--      depósito (área común de los bienes sin asignar).
--
-- NO se tocan `tipo_bien` ni `cantidad` (mig. 044) pese a haber quedado
-- sin sentido — B-07 dice que no llevan consumibles y B-09 que el
-- registro es individual. Se retiran de la interfaz en esta fase y se
-- eliminarán de la BD cuando el cliente confirme (pregunta B-66).
--
-- Idempotente.
-- =====================================================================

BEGIN;

-- ── 1. Estatus administrativo ────────────────────────────────────────
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS estatus VARCHAR(30);

UPDATE inventario SET estatus = 'Activo' WHERE estatus IS NULL;

ALTER TABLE inventario ALTER COLUMN estatus SET DEFAULT 'En espera de codificación';
ALTER TABLE inventario ALTER COLUMN estatus SET NOT NULL;

ALTER TABLE inventario DROP CONSTRAINT IF EXISTS inventario_estatus_check;
ALTER TABLE inventario ADD CONSTRAINT inventario_estatus_check CHECK (
    estatus IN ('En espera de codificación','Activo','En mantenimiento',
                'Extraviado','Robado','Dado de baja')
);

-- ── 2. Condición = solo estado físico ────────────────────────────────
-- 'En Reparación' sale: ahora es el estatus 'En mantenimiento'.
UPDATE inventario SET condicion = 'Dañado' WHERE condicion = 'En Reparación';

ALTER TABLE inventario DROP CONSTRAINT IF EXISTS inventario_condicion_check;
ALTER TABLE inventario ADD CONSTRAINT inventario_condicion_check CHECK (
    condicion IS NULL OR condicion IN ('Nuevo','Bueno','Regular','Dañado')
);

-- ── 3. Código oficial de la Alcaldía, por partes ─────────────────────
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS codigo_grupo        VARCHAR(4);
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS codigo_subgrupo     VARCHAR(4);
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS codigo_seccion      VARCHAR(6);
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS nro_orden           VARCHAR(10);
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS verificado_alcaldia BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS fecha_verificacion  DATE;

COMMENT ON COLUMN inventario.nro_orden IS
    'N° de orden que asigna la Alcaldía (3 dígitos con ceros a la izquierda). NULL hasta la inspección.';
COMMENT ON COLUMN inventario.codigo_bn IS
    'Código oficial compuesto (grupo-subgrupo-sección-N° de orden). Lo arma Inventario::componerCodigo().';

-- ── 4. Adquisición y procedencia ─────────────────────────────────────
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS origen            VARCHAR(20);
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS donante           VARCHAR(200);
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS costo_adquisicion NUMERIC(14,2);
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS fecha_adquisicion DATE;
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS proveedor         VARCHAR(200);
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS tiene_garantia    BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS garantia_vence    DATE;

UPDATE inventario SET origen = 'Compra' WHERE origen IS NULL;
ALTER TABLE inventario ALTER COLUMN origen SET DEFAULT 'Compra';

ALTER TABLE inventario DROP CONSTRAINT IF EXISTS inventario_origen_check;
ALTER TABLE inventario ADD CONSTRAINT inventario_origen_check CHECK (
    origen IS NULL OR origen IN ('Compra','Donación')
);

-- ── 5. Responsable nominal (uno solo, B-26/B-27) ─────────────────────
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS id_responsable INTEGER;

ALTER TABLE inventario DROP CONSTRAINT IF EXISTS fk_inventario_responsable;
ALTER TABLE inventario ADD CONSTRAINT fk_inventario_responsable
    FOREIGN KEY (id_responsable) REFERENCES empleados(id) ON DELETE SET NULL;

-- ── 6. Foto del bien (B-21) ──────────────────────────────────────────
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS foto_url VARCHAR(255);

-- ── 7. Ubicaciones: sede y depósito ──────────────────────────────────
ALTER TABLE ubicaciones ADD COLUMN IF NOT EXISTS sede         VARCHAR(80);
ALTER TABLE ubicaciones ADD COLUMN IF NOT EXISTS es_deposito  BOOLEAN NOT NULL DEFAULT FALSE;

UPDATE ubicaciones SET sede = 'Sede Principal' WHERE sede IS NULL OR sede = '';
ALTER TABLE ubicaciones ALTER COLUMN sede SET DEFAULT 'Sede Principal';

-- ── 8. Índices de apoyo ──────────────────────────────────────────────
CREATE INDEX IF NOT EXISTS idx_inventario_estatus        ON inventario (estatus);
CREATE INDEX IF NOT EXISTS idx_inventario_responsable    ON inventario (id_responsable);
CREATE INDEX IF NOT EXISTS idx_inventario_garantia       ON inventario (garantia_vence)
    WHERE garantia_vence IS NOT NULL;

-- ── 9. Catálogo de categorías internas (B-22) ────────────────────────
-- Hoy solo existen 2 filas de prueba ("Inmobiliario", "Inmuebles") y la
-- tabla inventario está vacía, así que se pueden retirar sin riesgo.
-- Este eje es el INTERNO (reportes de la Presidencia); el código de la
-- Alcaldía va aparte y no clasifica.
UPDATE categorias
   SET is_active = FALSE, deleted_at = CURRENT_TIMESTAMP
 WHERE is_active = TRUE
   AND nombre IN ('Inmobiliario','Inmuebles')
   AND NOT EXISTS (SELECT 1 FROM inventario i WHERE i.id_categoria = categorias.id);

INSERT INTO categorias (nombre, descripcion, is_active, created_at)
SELECT v.nombre, v.descripcion, TRUE, CURRENT_TIMESTAMP
  FROM (VALUES
    ('Mobiliario de oficina',            'Escritorios, sillas, mesas, archivadores, estantes'),
    ('Equipos de computación',           'CPU, laptops, monitores, impresoras, escáneres, UPS'),
    ('Equipos de comunicación',          'Teléfonos, radios, centrales telefónicas, routers'),
    ('Equipos audiovisuales',            'Videobeam, cámaras, televisores, sonido, micrófonos'),
    ('Climatización y refrigeración',    'Aires acondicionados, ventiladores, neveras'),
    ('Electrodomésticos y enseres',      'Cafeteras, microondas, dispensadores de agua'),
    ('Máquinas y equipos de oficina',    'Fotocopiadoras, trituradoras, encuadernadoras'),
    ('Herramientas y mantenimiento',     'Herramientas de Servicios Generales'),
    ('Equipos de seguridad',             'Extintores, cámaras de vigilancia, alarmas'),
    ('Material turístico y promocional', 'Stands, pendones, kioscos, señalética, lonas'),
    ('Bienes culturales y bibliográficos','Libros, obras, piezas de exhibición')
  ) AS v(nombre, descripcion)
 WHERE NOT EXISTS (
    SELECT 1 FROM categorias c WHERE c.nombre = v.nombre AND c.is_active = TRUE
 );

COMMIT;

-- =====================================================================
-- Verificación:
--   SELECT column_name FROM information_schema.columns
--    WHERE table_name='inventario' AND column_name IN
--      ('estatus','codigo_grupo','nro_orden','origen','id_responsable');
--   SELECT nombre FROM categorias WHERE is_active ORDER BY nombre;
--   SELECT conname, pg_get_constraintdef(oid) FROM pg_constraint
--    WHERE conrelid='inventario'::regclass AND contype='c';
-- =====================================================================
