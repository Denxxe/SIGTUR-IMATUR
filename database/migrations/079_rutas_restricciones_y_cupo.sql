-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 079 — Rutas: restricciones por recorrido y cupo diario (fases T-B y T-I)
--
-- CIERRA H-17, que era un bloqueo de uso real, no deuda técnica:
-- `RutasController` exigía **5 años como mínimo** y **menos de 12**, cableado en
-- el código, con los rótulos «Niño/a 5–11» repartidos por la vista y el informe.
-- Ese rango se fijó en la mig. 017 **sin levantamiento**: ningún dato del
-- cliente lo respaldaba. Y choca con lo que sí dijeron:
--
--   R-02  Exploradores de Cumaná es para **niños de 4 a 8 años**
--         → hoy un niño de 4 NO SE PUEDE INSCRIBIR
--   R-66  *"Exploradores lleva el tope de 4 hasta 16 años, por ser para
--         instituciones educativas"* → ni siquiera es 4-8: es **4 a 16**
--   R-57  *"Río Brito tiene restricción: de 12 años en adelante. Personas con
--         dificultad visual, excluidos. Con alguna condición en articulaciones:
--         sí debería saberlo [el sistema]"*
--
-- De R-57 se desprende que la restricción **no es solo la edad** y **no es
-- global**: es un atributo DE CADA RECORRIDO. Río Brito admite de 12 en
-- adelante y excluye por condición física; Exploradores va de 4 a 16. Un rango
-- único en el código no puede representar eso.
--
-- CUPO (T-I, R-28): *"no se maneja un cupo máximo para las rutas… pero se ha
-- implementado un cupo de **60 personas por día** — esto es nuevo"*.
-- Ojo: es por **DÍA**, no por salida. Con dos salidas la misma mañana (R-09) el
-- tope se reparte entre ambas, así que no puede vivir en `ruta_ejecuciones`.
--
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

-- ── 1. Restricciones del recorrido ───────────────────────────────────────────
-- NULL = sin restricción. Deliberadamente: la mayoría de las rutas no tiene
-- tope, y un valor por defecto volvería a inventar una regla que nadie pidió.
ALTER TABLE rutas ADD COLUMN IF NOT EXISTS edad_min       SMALLINT;
ALTER TABLE rutas ADD COLUMN IF NOT EXISTS edad_max       SMALLINT;
ALTER TABLE rutas ADD COLUMN IF NOT EXISTS restricciones  TEXT;

COMMENT ON COLUMN rutas.edad_min IS
  'Edad mínima para participar. NULL = sin mínimo. R-57: Río Brito = 12.';
COMMENT ON COLUMN rutas.edad_max IS
  'Edad máxima. NULL = sin tope. R-66: Exploradores de Cumaná = 16.';
COMMENT ON COLUMN rutas.restricciones IS
  'Condiciones que el sistema debe ADVERTIR al inscribir (no bloquear): "excluye a personas con dificultad visual", "advertir por condición articular"… R-57.';

DO $$
BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'rutas_edades_check') THEN
    ALTER TABLE rutas ADD CONSTRAINT rutas_edades_check
      CHECK (
        (edad_min IS NULL OR (edad_min >= 0  AND edad_min <= 120)) AND
        (edad_max IS NULL OR (edad_max >= 0  AND edad_max <= 120)) AND
        (edad_min IS NULL OR edad_max IS NULL OR edad_min <= edad_max)
      );
  END IF;
END $$;

-- ── 2. Datos que el cliente YA dio ───────────────────────────────────────────
-- Se aplican por tipo de ruta porque el catálogo real todavía no está cargado
-- (pregunta abierta R-72: qué rutas hay y cómo se llaman exactamente).
UPDATE rutas SET edad_min = 4, edad_max = 16
 WHERE tipo_ruta = 'Exploradores de Cumaná' AND edad_min IS NULL AND edad_max IS NULL;

-- ── 3. Cupo diario (T-I, R-28) ───────────────────────────────────────────────
-- Escalar de configuración, no columna: el tope es de la institución y del día,
-- no de un recorrido concreto. 0 = sin tope, para poder desactivarlo sin código.
INSERT INTO configuracion_sistema (clave, valor, descripcion) VALUES
  ('rutas_cupo_diario', '60',
   'Tope de personas atendidas por DÍA sumando todas las salidas (R-28). 0 = sin tope.')
ON CONFLICT (clave) DO NOTHING;
