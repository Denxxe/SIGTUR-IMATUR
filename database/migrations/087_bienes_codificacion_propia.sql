-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 087 — Codificación propia de bienes (R-12 · C-1…C-4, C-7)
--
-- Procedimiento nuevo notificado el 2026-09-02: IMATUR asigna el código de sus
-- bienes, continuando la secuencia desde la última revisión de la Alcaldía.
-- El motor queda CONSTRUIDO pero APAGADO (`bienes_codificacion_propia = 0`):
-- mientras esté apagado, codificar sigue siendo transcribir el BM-1. Se
-- enciende desde Bienes → Codificación cuando el cliente entregue:
--   · B-73 — el punto de partida (último N° de orden que deje la Alcaldía);
--   · B-75 — los grupos/subgrupos/secciones válidos (catálogo).
-- B-74 (una secuencia o una por clasificación; qué pasa después de 999) queda
-- como parámetro: no hay respuesta del cliente todavía.
--
-- C-3: «codificado» y «reconocido por la Alcaldía» dejan de ser lo mismo:
--   · `codificado_por` ∈ {Alcaldía, IMATUR} + `fecha_codificacion`;
--   · `verificado_alcaldia` conserva su sentido: la Alcaldía lo reconoce (BM-1).
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

BEGIN;

ALTER TABLE inventario ADD COLUMN IF NOT EXISTS codificado_por VARCHAR(10);
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS fecha_codificacion DATE;
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_inventario_codificado_por') THEN
        ALTER TABLE inventario ADD CONSTRAINT chk_inventario_codificado_por
            CHECK (codificado_por IS NULL OR codificado_por IN ('Alcaldía', 'IMATUR'));
    END IF;
END $$;
COMMENT ON COLUMN inventario.codificado_por IS 'Quién asignó el código: la Alcaldía (BM-1) o IMATUR (codificación propia, mig. 087). Distinto de verificado_alcaldia.';

-- Todo lo codificado hasta hoy vino del BM-1.
UPDATE inventario
   SET codificado_por = 'Alcaldía', fecha_codificacion = COALESCE(fecha_verificacion, updated_at::date, created_at::date)
 WHERE codigo_bn IS NOT NULL AND codificado_por IS NULL;

-- Catálogo de clasificaciones (C-4 · B-75). La Alcaldía no lo ha entregado: se
-- siembra con los códigos ya en uso y se completa desde la pantalla.
CREATE TABLE IF NOT EXISTS inventario_clasificaciones (
    id            SERIAL PRIMARY KEY,
    grupo         VARCHAR(4)   NOT NULL,
    subgrupo      VARCHAR(4)   NOT NULL,
    seccion       VARCHAR(6)   NOT NULL,
    descripcion   VARCHAR(200),
    is_active     BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by    INT,
    updated_at    TIMESTAMP,
    updated_by    INT,
    CONSTRAINT uq_inventario_clasificacion UNIQUE (grupo, subgrupo, seccion),
    CONSTRAINT chk_inv_clasif_formato CHECK (grupo ~ '^\d{1,4}$' AND subgrupo ~ '^\d{1,4}$' AND seccion ~ '^\d{1,6}$')
);
COMMENT ON TABLE inventario_clasificaciones IS 'Grupo-subgrupo-sección válidos para codificar (C-4, B-75). Se completa cuando la Alcaldía entregue su catálogo.';

-- El único código conocido con certeza: el del BM-1 sellado (sillas, mesas, aires, router…).
INSERT INTO inventario_clasificaciones (grupo, subgrupo, seccion, descripcion)
VALUES ('2', '01', '108', 'Según el BM-1 de la Alcaldía (mobiliario y equipos de oficina). Descripción oficial pendiente (B-75)')
ON CONFLICT (grupo, subgrupo, seccion) DO NOTHING;

INSERT INTO inventario_clasificaciones (grupo, subgrupo, seccion, descripcion)
SELECT DISTINCT codigo_grupo, codigo_subgrupo, codigo_seccion, 'Tomado de los bienes ya codificados'
  FROM inventario
 WHERE codigo_grupo ~ '^\d{1,4}$' AND codigo_subgrupo ~ '^\d{1,4}$' AND codigo_seccion ~ '^\d{1,6}$'
ON CONFLICT (grupo, subgrupo, seccion) DO NOTHING;

-- Parámetros (todo configurable; el motor arranca apagado).
INSERT INTO configuracion_sistema (clave, valor, descripcion)
SELECT v.clave, v.valor, v.descripcion
  FROM (VALUES
        ('bienes_codificacion_propia', '0',
         'Bienes: 1 = IMATUR asigna el código de sus bienes (procedimiento del 2026-09-02); 0 = se transcribe el que asigna la Alcaldía en el BM-1.'),
        ('bienes_nro_orden_inicio', '',
         'Bienes: último N° de orden que deja la Alcaldía en su última revisión (B-73). La secuencia propia continúa desde el mayor entre este y el mayor ya registrado.'),
        ('bienes_nro_orden_digitos', '3',
         'Bienes: cantidad de dígitos del N° de orden (se rellena con ceros a la izquierda; si un número los supera, se escribe completo).'),
        ('bienes_secuencia_por_clasificacion', '0',
         'Bienes (B-74): 0 = una sola secuencia de N° de orden para todo IMATUR; 1 = una secuencia por cada grupo-subgrupo-sección.')
       ) AS v(clave, valor, descripcion)
 WHERE NOT EXISTS (SELECT 1 FROM configuracion_sistema c WHERE c.clave = v.clave);

COMMIT;

-- Verificación:
--   SELECT clave, valor FROM configuracion_sistema WHERE clave LIKE 'bienes_%';
--   SELECT * FROM inventario_clasificaciones;
