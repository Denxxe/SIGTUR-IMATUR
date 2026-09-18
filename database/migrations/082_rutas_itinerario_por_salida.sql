-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 082 — Rutas: itinerario por salida y guía externo del punto (T-F)
--
-- R-10: *«Puede cambiar en algún punto. Si hay varios grupos en la misma ruta al
-- mismo tiempo, **se cambia un poco el itinerario y el orden de los puntos**
-- (para no coincidir).»*
--
-- R-18 lo confirma desde el otro lado: *«Se puede variar según convenga»* — el
-- orden del catálogo es el **sugerido**, no una imposición.
--
-- HOY EL ORDEN VIVE EN `puntos_ruta.orden`, QUE ES DEL RECORRIDO. Cambiarlo para
-- que dos grupos no coincidan un martes se lo cambiaría **a todas las salidas**,
-- pasadas y futuras. Por eso el orden propio de una salida va en su propia tabla.
--
-- CÓMO FUNCIONA: si una salida NO tiene filas aquí, usa el orden del catálogo
-- —que es el caso normal—. En cuanto se reordena, se guardan **todos** sus puntos
-- y esa tabla manda para esa salida. «Restablecer» borra las filas y vuelve al
-- catálogo. Así el caso común no cuesta nada y la excepción queda acotada.
--
-- `omitido` sale de un caso real que apareció con T-H: si la institución custodia
-- **rechaza el permiso** (R-20), esa parada no se hace ese día — pero la parada
-- sigue existiendo en el recorrido. Marcarla omitida deja constancia de por qué
-- el grupo no pasó por ahí, sin tocar el catálogo.
--
-- ─────────────────────────────────────────────────────────────────────────────
-- R-31: *«Siempre encabeza un empleado de IMATUR. En algunos puntos que tienen el
-- suyo (museo, casa natal…) se suma un guía externo.»*
--
-- El guía externo es **del punto**, no de la salida: quien lo pone es el museo.
-- Por eso se marca en `puntos_ruta` y NO se reintroduce un facilitador externo
-- en la ruta (la columna `rutas.nombre_facilitador_externo` se eliminó en la
-- mig. 060 justamente por no usarse). Se guarda **solo que lo hay**, no quién es:
-- R-32 —si se le paga o se registran sus datos— sigue sin responder, y no vale
-- inventar un registro de personas que nadie pidió.
--
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

-- ── 1. El itinerario propio de una salida ───────────────────────────────────
CREATE TABLE IF NOT EXISTS ruta_ejecucion_itinerario (
    id           SERIAL PRIMARY KEY,
    id_ejecucion INTEGER  NOT NULL REFERENCES ruta_ejecuciones(id) ON DELETE CASCADE,
    id_punto     INTEGER  NOT NULL REFERENCES puntos_ruta(id)      ON DELETE CASCADE,
    orden        SMALLINT NOT NULL,
    omitido      BOOLEAN  NOT NULL DEFAULT FALSE,
    nota         VARCHAR(255),
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by   INTEGER,
    CONSTRAINT uq_itinerario_punto  UNIQUE (id_ejecucion, id_punto),
    CONSTRAINT ck_itinerario_orden  CHECK (orden >= 1)
);

COMMENT ON TABLE ruta_ejecucion_itinerario IS
  'Orden de las paradas PARA UNA SALIDA concreta (R-10/R-18). Sin filas = se usa el orden del catálogo, que es el caso normal. Con filas, éstas mandan para esa salida.';
COMMENT ON COLUMN ruta_ejecucion_itinerario.omitido IS
  'La parada no se hizo ese día — típicamente porque la institución custodia rechazó el permiso (R-20). Deja constancia sin tocar el recorrido del catálogo.';
COMMENT ON COLUMN ruta_ejecucion_itinerario.nota IS
  'Por qué se alteró o se omitió esa parada en esta salida.';

CREATE INDEX IF NOT EXISTS idx_itinerario_ejecucion ON ruta_ejecucion_itinerario (id_ejecucion, orden);

-- ── 2. El punto que pone su propio guía (R-31) ──────────────────────────────
ALTER TABLE puntos_ruta ADD COLUMN IF NOT EXISTS tiene_guia_externo BOOLEAN NOT NULL DEFAULT FALSE;

COMMENT ON COLUMN puntos_ruta.tiene_guia_externo IS
  'El punto aporta su propio guía, que se suma al de IMATUR (R-31). Solo se registra QUE lo hay: R-32 —si se le paga o se guardan sus datos— sigue sin responder.';
