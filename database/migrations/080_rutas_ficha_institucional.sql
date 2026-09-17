-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 080 — Rutas: Ficha Institucional (fase T-G)
--
-- Es el **pedido #1 del cliente** (R-64: *«los reportes y los oficios»*) y el
-- único documento de Rutas cuyo formato ya está en mano:
-- `docs/formatos/rutas_ficha_institucional_IMATUR.jpeg`.
--
-- SE PEDÍAN DOS DOCUMENTOS Y SON EL MISMO. R-43 («planilla del día»), R-47
-- («informe de cierre») y R-48 resultaron ser esta misma hoja. Por eso la ficha
-- **no es una tabla nueva**: es lo que `ruta_informes` ya quería ser. Se extiende
-- esa tabla en vez de duplicarla, y así los reportes que ya suman
-- `ruta_informes.total_atendidos` siguen funcionando sin tocarse.
--
-- QUÉ LLEVA EL FORMATO (leído del papel, no supuesto):
--
--   Cabecera   RECORRIDO · FECHA · ENCARGADO · COLEGIO Ó INSTITUCIÓN · RESPONSABLE
--   Izquierda  INSTITUCIÓN | NIÑOS F | M | EDADES | TOTAL A.   ← 8 renglones
--   Derecha    ACOMPAÑANTES: DOCENTES (F/M) · REPRESENTANTES (F/M)
--              INSTITUCIONES DE APOYO: nombre (F/M)            ← ahí va Protección Civil (R-55/56)
--   Pie        TOTAL general
--
-- Del ejemplo real (Cumaná Histórica, 28-08-2026): 12 F + 10 M = 22 niños,
-- 7 + 2 = 9 docentes, Protección Civil 1 + 1 = 2 → **TOTAL 33**. La cuenta cierra,
-- así que el total es la suma de los tres bloques y no hay ninguna casilla oculta.
--
-- DE DÓNDE SALE CADA DATO. Cuatro de los cinco campos de cabecera el sistema YA
-- los tiene (recorrido, fecha, institución solicitante y encargado de la salida),
-- así que la ficha **se genera sola** al marcar la salida como Ejecutada —que es
-- lo que pide R-50— y sobre ella se capturan solo los conteos. Nada de volver a
-- escribir lo que ya está registrado.
--
-- RELACIÓN CON `participantes_ruta`: ninguna, a propósito. R-23/R-24/R-29 dicen
-- que del grupo visitante IMATUR lleva **solo el conteo**; la lista nominal
-- firmada es del personal de IMATUR, no de los niños. Por eso los renglones son
-- agregados por institución, no una fila por persona.
--
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

-- ── 1. Cabecera: `ruta_informes` pasa a ser la Ficha Institucional ───────────
ALTER TABLE ruta_informes ADD COLUMN IF NOT EXISTS responsable_nombre VARCHAR(160);
ALTER TABLE ruta_informes ADD COLUMN IF NOT EXISTS docentes_f         SMALLINT NOT NULL DEFAULT 0;
ALTER TABLE ruta_informes ADD COLUMN IF NOT EXISTS docentes_m         SMALLINT NOT NULL DEFAULT 0;
ALTER TABLE ruta_informes ADD COLUMN IF NOT EXISTS representantes_f   SMALLINT NOT NULL DEFAULT 0;
ALTER TABLE ruta_informes ADD COLUMN IF NOT EXISTS representantes_m   SMALLINT NOT NULL DEFAULT 0;
ALTER TABLE ruta_informes ADD COLUMN IF NOT EXISTS estado             VARCHAR(20) NOT NULL DEFAULT 'Borrador';
ALTER TABLE ruta_informes ADD COLUMN IF NOT EXISTS fecha_cierre       TIMESTAMP;
ALTER TABLE ruta_informes ADD COLUMN IF NOT EXISTS updated_by         INTEGER;

COMMENT ON TABLE ruta_informes IS
  'Ficha Institucional de una salida (mig. 080). Cabecera del formato oficial; los renglones por institución y de apoyo viven en ruta_ficha_grupos. Antes se llamaba «informe de visita» y solo tenía el desglose por sexo.';
COMMENT ON COLUMN ruta_informes.responsable_nombre IS
  'Casilla RESPONSABLE del formato: quien responde por el grupo visitante. El ENCARGADO (de IMATUR) NO se guarda aquí — se deriva de ruta_ejecucion_empleados.es_encargado.';
COMMENT ON COLUMN ruta_informes.estado IS
  'Borrador = se sigue capturando · Cerrada = se imprimió y entregó; ya no se edita.';
COMMENT ON COLUMN ruta_informes.mujeres IS
  'DERIVADA (mig. 080): docentes_f + representantes_f + suma de los renglones de apoyo (F). No se captura.';
COMMENT ON COLUMN ruta_informes.hombres IS
  'DERIVADA (mig. 080): docentes_m + representantes_m + suma de los renglones de apoyo (M). No se captura.';
COMMENT ON COLUMN ruta_informes.ninas IS
  'DERIVADA (mig. 080): suma del bloque NIÑOS (F) de los renglones por institución.';
COMMENT ON COLUMN ruta_informes.ninos IS
  'DERIVADA (mig. 080): suma del bloque NIÑOS (M) de los renglones por institución.';

DO $$
BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'ruta_informes_estado_check') THEN
    ALTER TABLE ruta_informes ADD CONSTRAINT ruta_informes_estado_check
      CHECK (estado IN ('Borrador', 'Cerrada'));
  END IF;
END $$;

-- Una ficha por salida. Sin esto, «guardar» dos veces crearía dos fichas de la
-- misma salida y los reportes sumarían el doble de atendidos.
CREATE UNIQUE INDEX IF NOT EXISTS uq_ruta_informes_ejecucion
    ON ruta_informes (id_ejecucion) WHERE id_ejecucion IS NOT NULL;

-- ── 2. Renglones de la ficha ────────────────────────────────────────────────
-- Un solo tabla para los dos bloques del formato, distinguidos por `tipo`:
-- comparten columnas (nombre, F, M) y solo el bloque de instituciones usa el
-- rango de edades. Dos tablas habrían duplicado el CRUD por una columna.
CREATE TABLE IF NOT EXISTS ruta_ficha_grupos (
    id           SERIAL PRIMARY KEY,
    id_informe   INTEGER     NOT NULL REFERENCES ruta_informes(id) ON DELETE CASCADE,
    tipo         VARCHAR(20) NOT NULL,
    nombre       VARCHAR(160) NOT NULL,
    femenino     SMALLINT    NOT NULL DEFAULT 0,
    masculino    SMALLINT    NOT NULL DEFAULT 0,
    edad_min     SMALLINT,
    edad_max     SMALLINT,
    orden        SMALLINT    NOT NULL DEFAULT 0,
    is_active    BOOLEAN     NOT NULL DEFAULT TRUE,
    created_at   TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    created_by   INTEGER,
    CONSTRAINT ruta_ficha_grupos_tipo_check   CHECK (tipo IN ('Institucion', 'Apoyo')),
    CONSTRAINT ruta_ficha_grupos_conteo_check CHECK (femenino >= 0 AND masculino >= 0),
    CONSTRAINT ruta_ficha_grupos_edades_check CHECK (
        (edad_min IS NULL OR (edad_min >= 0 AND edad_min <= 120)) AND
        (edad_max IS NULL OR (edad_max >= 0 AND edad_max <= 120)) AND
        (edad_min IS NULL OR edad_max IS NULL OR edad_min <= edad_max)
    )
);

COMMENT ON TABLE ruta_ficha_grupos IS
  'Renglones de la Ficha Institucional. tipo=Institucion → bloque NIÑOS (con rango de edades); tipo=Apoyo → INSTITUCIONES DE APOYO (adultos: Protección Civil, PNB…, R-55/R-56).';
COMMENT ON COLUMN ruta_ficha_grupos.edad_min IS
  'Casilla EDADES del formato («7 a 10 años»). Solo aplica a tipo=Institucion; en los de apoyo va NULL porque son adultos.';

CREATE INDEX IF NOT EXISTS idx_ruta_ficha_grupos_informe ON ruta_ficha_grupos (id_informe);

-- ── 3. Las fichas que ya existían quedan cerradas ────────────────────────────
-- Se capturaron con el formulario viejo (solo mujeres/hombres/niñas/niños) y no
-- tienen renglones. Marcarlas Borrador invitaría a editarlas sin su desglose.
UPDATE ruta_informes SET estado = 'Cerrada'
 WHERE estado = 'Borrador' AND created_at < NOW() - INTERVAL '1 minute';
