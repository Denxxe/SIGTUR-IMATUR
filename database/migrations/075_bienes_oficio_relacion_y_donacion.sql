-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 075 — Bienes: oficio de relación a la Alcaldía + documento de donación
--
-- Origen: los dos formatos que la Directora de Bienes entregó el 2026-09-15
-- (archivados en docs/formatos/):
--   · oficio_relacion_bienes_nuevos_alcaldia_2026-06-10.jpg  (Oficio N° 179/2026)
--   · documento_donacion_bien_2026-02-18.jpg
--
-- Cierra dos de los cuatro documentos que tenía pendientes el módulo. Quedan
-- fuera el Acta de Desincorporación y el acta de asignación: sus formatos no
-- llegaron y construirlos a ciegas obligaría a rehacerlos (misma razón por la
-- que estos dos esperaron desde agosto).
--
-- Idempotente (IF NOT EXISTS / ON CONFLICT).
-- ─────────────────────────────────────────────────────────────────────────────

-- ── 1. Datos que exige el documento de donación ──────────────────────────────
-- El documento identifica al donante como parte de un acto jurídico: nombre,
-- cédula, estado civil y domicilio. Hoy `inventario.donante` es un solo texto
-- con el nombre, que alcanzaba para un listado pero no para redactar el acto.
--
-- `donacion_procedencia` es el párrafo que explica de dónde salió el bien y
-- por qué no hay factura ("me pertenece por haberla obtenido como premio de un
-- Bingo"): sin él el documento no se sostiene, porque la donación se acepta
-- justamente a falta de factura.
--
-- El valor va en dos monedas porque el formato lo declara así: el monto en Bs
-- (en letras y en números) y su equivalente en dólares.
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS donante_cedula       VARCHAR(20);
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS donante_estado_civil VARCHAR(30);
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS donante_domicilio    VARCHAR(255);
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS donacion_procedencia TEXT;
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS donacion_valor_usd   NUMERIC(12,2);
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS donacion_fecha       DATE;

COMMENT ON COLUMN inventario.donacion_procedencia IS
  'Cómo obtuvo el donante el bien y por qué no posee factura (va literal en el documento de donación).';
COMMENT ON COLUMN inventario.donacion_valor_usd IS
  'Equivalente en USD del valor estimado; el monto en Bs vive en costo_adquisicion.';

-- ── 2. Oficio de relación de bienes nuevos (cabecera + renglones) ────────────
-- El oficio se le pasa a la Coordinación de Bienes de la Alcaldía con el lote
-- de bienes nuevos. Se modela como cabecera + vínculo desde cada bien (mismo
-- patrón que inventario_consolidados_bm1) por dos razones:
--   · saber qué bienes ya se reportaron, para no mandarlos dos veces;
--   · poder reimprimir el oficio tal como se envió, que es lo que pide una
--     auditoría por cambio de gestión.
CREATE TABLE IF NOT EXISTS inventario_relaciones (
  id                   SERIAL PRIMARY KEY,
  numero               VARCHAR(20)  NOT NULL,          -- "179/2026"
  fecha                DATE         NOT NULL DEFAULT CURRENT_DATE,
  destinatario_nombre  VARCHAR(200) NOT NULL,
  destinatario_cargo   VARCHAR(200),
  destinatario_ente    VARCHAR(200),
  observacion          TEXT,
  is_active            BOOLEAN      NOT NULL DEFAULT TRUE,
  anulado_motivo       TEXT,
  created_at           TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  created_by           INTEGER,
  deleted_at           TIMESTAMP,
  deleted_by           INTEGER
);

CREATE INDEX IF NOT EXISTS idx_inv_relaciones_fecha ON inventario_relaciones (fecha DESC);

-- Un bien pertenece a lo sumo a un oficio de relación. Al anular el oficio el
-- vínculo se limpia y el bien vuelve a la bolsa de "sin reportar".
ALTER TABLE inventario ADD COLUMN IF NOT EXISTS id_relacion INTEGER;

DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM pg_constraint WHERE conname = 'fk_inventario_relacion'
  ) THEN
    ALTER TABLE inventario
      ADD CONSTRAINT fk_inventario_relacion
      FOREIGN KEY (id_relacion) REFERENCES inventario_relaciones(id) ON DELETE SET NULL;
  END IF;
END $$;

CREATE INDEX IF NOT EXISTS idx_inventario_relacion ON inventario (id_relacion);

-- ── 3. Configuración: correlativo propio y destinatario habitual ─────────────
-- El correlativo sigue el mismo mecanismo por módulo que ya usan rutas,
-- pasantes y constancias (ConfigSistema::generarNumeroOficio).
INSERT INTO configuracion_sistema (clave, valor, descripcion) VALUES
  ('correlativo_oficio_bienes', '0',
   'Último correlativo de oficio de relación de bienes emitido en el año en curso'),
  ('ano_correlativo_bienes', EXTRACT(YEAR FROM CURRENT_DATE)::TEXT,
   'Año del correlativo de oficios de bienes (se reinicia automáticamente)'),
  ('bienes_destinatario_nombre', 'Lcdo. Antonio Guevara',
   'Destinatario habitual del oficio de relación de bienes nuevos'),
  ('bienes_destinatario_cargo', 'Coordinador de Bienes y Materias',
   'Cargo del destinatario del oficio de relación de bienes'),
  ('bienes_destinatario_ente', 'Alcaldía del Municipio Sucre del estado Sucre',
   'Ente al que pertenece el destinatario del oficio de relación de bienes'),
  ('abogado_visador_nombre', '',
   'Abogado que visa el documento de donación (opcional)'),
  ('abogado_visador_ipsa', '',
   'N° de IPSA del abogado visador (opcional)'),
  ('director_cedula', 'V-15.933.871',
   'Cédula del firmante institucional (la exige el documento de donación)'),
  ('director_nombre_completo', 'MARÍA DE LOS ANGELES MAZA MÁRQUEZ',
   'Nombre completo del firmante tal como aparece en los actos jurídicos')
ON CONFLICT (clave) DO NOTHING;

-- ── 4. Corrección: la resolución y la gaceta que imprimía el sistema ─────────
-- Los dos documentos entregados por la Directora de Bienes, ambos firmados y
-- sellados, declaran la designación vigente de la Presidenta:
--     Resolución N° 32 del 05/09/2025 · Gaceta Municipal Extraordinaria N° 87
--     del 05/09/2025
-- El sistema tenía Resolución 025 (15/03/2024) y Gaceta 042 (20/01/2024), datos
-- de relleno que hoy se imprimen en CINCO documentos reales: constancias de
-- trabajo, carta de aceptación y de culminación de pasantes, y los dos oficios
-- de rutas. Se corrigen aquí; son editables desde /config si el cliente indica
-- otra cosa.
UPDATE configuracion_sistema SET valor = '32'         WHERE clave = 'resolucion_numero';
UPDATE configuracion_sistema SET valor = '05/09/2025' WHERE clave = 'resolucion_fecha';
UPDATE configuracion_sistema SET valor = '87'         WHERE clave = 'gaceta_numero';
UPDATE configuracion_sistema SET valor = '05/09/2025' WHERE clave = 'gaceta_fecha';

-- El cargo real es PRESIDENTA, no "Director"/"Director General": así firma en
-- los dos oficios entregados.
UPDATE configuracion_sistema SET valor = 'Presidenta' WHERE clave = 'director_cargo';
UPDATE configuracion_sistema SET valor = 'Presidenta' WHERE clave = 'firmante_cargo';
