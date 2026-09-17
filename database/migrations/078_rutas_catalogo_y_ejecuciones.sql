-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 078 — Rutas: separar el CATÁLOGO de las SALIDAS (fase T-A)
--
-- El módulo se construyó sobre una premisa falsa: cada fila de `rutas` era *una
-- salida concreta* (tenía fecha, hora, facilitador y cupo). El levantamiento la
-- desmiente en tres respuestas independientes:
--
--   R-08  "Existe un catálogo… los puntos de la ruta, el recorrido: todo lleva
--          un catálogo"
--   R-09  Una misma ruta puede tener DOS salidas la misma mañana, con guías
--          distintos — imposible de representar con una fila por ruta
--   R-07  "Así sean la misma ruta, es considerada 2 salidas y en el registro
--          son 2 rutas aplicadas"   (2026-09-17, el cierre definitivo)
--
-- El propio esquema lo delataba: de los cuatro estados, tres describían una
-- ruta del catálogo (Activa / Inactiva / En Mantenimiento) y uno una salida
-- (Finalizada). Cuatro valores en una columna para dos ciclos de vida.
--
--   rutas              → EL CATÁLOGO. Qué se ofrece: recorrido, duración,
--                        tarifa, restricciones. No tiene fecha.
--   ruta_ejecuciones   → LA SALIDA. Cada vez que se sale: fecha, hora, grupo,
--                        estado, quién la guio.
--
-- ESTADOS (R-14, respondida el 2026-09-17 con las palabras del cliente):
--   Programado → Ejecutado | No ejecutado
-- «Cancelada» NO es un estado: desemboca en *No ejecutado* con su motivo, y de
-- ahí sale la reprogramación — que es OTRA salida enlazada a la original, no un
-- cambio de fecha sobre la misma. Así el conteo de ejecutadas no se infla.
--
-- Datos: `rutas` tiene 2 filas y `puntos_ruta` 1. La migración es trivial; el
-- costo estaba en el código.
--
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

-- ── 1. La tabla de salidas ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ruta_ejecuciones (
    id                  SERIAL PRIMARY KEY,
    id_ruta             INTEGER NOT NULL REFERENCES rutas(id) ON DELETE RESTRICT,

    fecha               DATE NOT NULL,
    hora                TIME,
    cupo_maximo         INTEGER,

    -- R-14: tres estados, no cinco.
    estado              VARCHAR(20) NOT NULL DEFAULT 'Programado',

    -- R-15: "si se cancela, el motivo siempre tiene que saberse" → obligatorio
    -- cuando el estado es «No ejecutado» (lo valida el modelo, no un CHECK,
    -- para poder dar un mensaje claro en vez de un error de BD).
    motivo_no_ejecucion TEXT,

    -- R-16 + R-14: la salida que no se pudo ejecutar queda registrada, y la
    -- reprogramación es una salida NUEVA que apunta a ella.
    id_reprogramada_de  INTEGER REFERENCES ruta_ejecuciones(id) ON DELETE SET NULL,

    -- R-11: dos orígenes. La institución solicita por oficio (que llega en
    -- físico y lo redacta ella, R-12: el sistema lo archiva, no lo genera).
    origen              VARCHAR(20) NOT NULL DEFAULT 'Particular',
    institucion_nombre  VARCHAR(200),
    oficio_archivo      VARCHAR(255),
    oficio_original     VARCHAR(255),

    -- R-13: la decisión final la tiene la Presidenta.
    aprobada_por        INTEGER,
    fecha_aprobacion    DATE,

    -- R-45: "sí se lleva y se reporta la incidencia"
    incidencias         TEXT,
    observaciones       TEXT,

    is_active   BOOLEAN   NOT NULL DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP,
    deleted_at  TIMESTAMP,
    created_by  INTEGER,
    updated_by  INTEGER,
    deleted_by  INTEGER,

    CONSTRAINT ruta_ejec_estado_check
        CHECK (estado IN ('Programado', 'Ejecutado', 'No ejecutado')),
    CONSTRAINT ruta_ejec_origen_check
        CHECK (origen IN ('Particular', 'Institucional'))
);

CREATE INDEX IF NOT EXISTS idx_ruta_ejec_ruta   ON ruta_ejecuciones (id_ruta);
CREATE INDEX IF NOT EXISTS idx_ruta_ejec_fecha  ON ruta_ejecuciones (fecha DESC);
CREATE INDEX IF NOT EXISTS idx_ruta_ejec_estado ON ruta_ejecuciones (estado);

COMMENT ON TABLE ruta_ejecuciones IS
  'Una SALIDA: cada vez que se ejecuta una ruta del catálogo. R-07: «así sean la misma ruta, es considerada 2 salidas».';

-- ── 2. Empleados que van en cada salida (R-33) ───────────────────────────────
-- «Depende de la cantidad de niños o personas: 7-8 niños por guía; una salida
-- de 35 personas irían 3 guías». Y sí quieren registrar quiénes fueron.
-- La salida la ENCABEZA siempre un empleado de IMATUR (R-31); el guía externo
-- lo pone el punto visitado, así que no se modela aquí.
CREATE TABLE IF NOT EXISTS ruta_ejecucion_empleados (
    id            SERIAL PRIMARY KEY,
    id_ejecucion  INTEGER NOT NULL REFERENCES ruta_ejecuciones(id) ON DELETE CASCADE,
    id_empleado   INTEGER NOT NULL REFERENCES empleados(id) ON DELETE RESTRICT,
    es_encargado  BOOLEAN NOT NULL DEFAULT FALSE,
    is_active     BOOLEAN NOT NULL DEFAULT TRUE,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by    INTEGER
);

CREATE UNIQUE INDEX IF NOT EXISTS ux_ruta_ejec_emp
    ON ruta_ejecucion_empleados (id_ejecucion, id_empleado) WHERE is_active;

-- ── 3. Migrar las filas existentes ───────────────────────────────────────────
-- Cada `rutas` de hoy es una salida: se le crea su ejecución conservando fecha,
-- hora, cupo y facilitador. El catálogo se queda con el resto.
DO $$
DECLARE r RECORD; nueva_id INTEGER;
BEGIN
  IF NOT EXISTS (SELECT 1 FROM ruta_ejecuciones) THEN
    FOR r IN SELECT * FROM rutas WHERE fecha_visita IS NOT NULL LOOP
      INSERT INTO ruta_ejecuciones (id_ruta, fecha, hora, cupo_maximo, estado, created_at, created_by)
      VALUES (r.id, r.fecha_visita, r.hora_visita, r.cupo_maximo,
              CASE WHEN r.estado = 'Finalizada' THEN 'Ejecutado' ELSE 'Programado' END,
              COALESCE(r.created_at, CURRENT_TIMESTAMP), r.created_by)
      RETURNING id INTO nueva_id;

      IF r.id_facilitador IS NOT NULL THEN
        INSERT INTO ruta_ejecucion_empleados (id_ejecucion, id_empleado, es_encargado, created_by)
        VALUES (nueva_id, r.id_facilitador, TRUE, r.created_by);
      END IF;
    END LOOP;
  END IF;
END $$;

-- ── 4. Repuntar lo que colgaba de la ruta y ahora cuelga de la salida ────────
ALTER TABLE participantes_ruta ADD COLUMN IF NOT EXISTS id_ejecucion INTEGER;
ALTER TABLE ruta_informes      ADD COLUMN IF NOT EXISTS id_ejecucion INTEGER;
ALTER TABLE oficios_emitidos   ADD COLUMN IF NOT EXISTS id_ejecucion INTEGER;

UPDATE participantes_ruta p SET id_ejecucion = e.id
  FROM ruta_ejecuciones e WHERE e.id_ruta = p.id_ruta AND p.id_ejecucion IS NULL;
UPDATE ruta_informes i SET id_ejecucion = e.id
  FROM ruta_ejecuciones e WHERE e.id_ruta = i.id_ruta AND i.id_ejecucion IS NULL;
UPDATE oficios_emitidos o SET id_ejecucion = e.id
  FROM ruta_ejecuciones e WHERE e.id_ruta = o.id_ruta AND o.id_ejecucion IS NULL;

DO $$
BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'fk_part_ruta_ejecucion') THEN
    ALTER TABLE participantes_ruta ADD CONSTRAINT fk_part_ruta_ejecucion
      FOREIGN KEY (id_ejecucion) REFERENCES ruta_ejecuciones(id) ON DELETE CASCADE;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'fk_informe_ejecucion') THEN
    ALTER TABLE ruta_informes ADD CONSTRAINT fk_informe_ejecucion
      FOREIGN KEY (id_ejecucion) REFERENCES ruta_ejecuciones(id) ON DELETE CASCADE;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'fk_oficio_ejecucion') THEN
    ALTER TABLE oficios_emitidos ADD CONSTRAINT fk_oficio_ejecucion
      FOREIGN KEY (id_ejecucion) REFERENCES ruta_ejecuciones(id) ON DELETE SET NULL;
  END IF;
END $$;

CREATE INDEX IF NOT EXISTS idx_part_ejecucion    ON participantes_ruta (id_ejecucion);
CREATE INDEX IF NOT EXISTS idx_informe_ejecucion ON ruta_informes (id_ejecucion);

-- `id_ruta` se CONSERVA en las tres tablas a propósito: son columnas viejas con
-- datos y quitarlas obligaría a tocar los reportes en la misma migración. Se
-- eliminan cuando el código no las lea (limpieza aparte, como la mig. 060).
--
-- Pero deja de ser OBLIGATORIA: el código ya no la escribe, y con el NOT NULL
-- puesto una inscripción nueva revienta en la BD. Conservar la columna es una
-- cosa; seguir exigiéndola, otra.
ALTER TABLE participantes_ruta ALTER COLUMN id_ruta DROP NOT NULL;
ALTER TABLE ruta_informes      ALTER COLUMN id_ruta DROP NOT NULL;

-- Un informe por SALIDA (antes era uno por ruta, que con varias salidas de la
-- misma ruta habría mezclado los conteos de todas).
CREATE UNIQUE INDEX IF NOT EXISTS ux_ruta_informe_ejecucion
    ON ruta_informes (id_ejecucion) WHERE id_ejecucion IS NOT NULL;

-- ── 5. El catálogo se queda sin lo que era de la salida ──────────────────────
-- Las columnas NO se borran todavía, por la misma razón que arriba: primero el
-- código deja de leerlas. Lo que sí cambia es el CHECK de estado: «Finalizada»
-- describía una salida y ya no tiene sentido en el catálogo.
UPDATE rutas SET estado = 'Activa' WHERE estado = 'Finalizada';

ALTER TABLE rutas DROP CONSTRAINT IF EXISTS rutas_estado_check;
ALTER TABLE rutas ADD CONSTRAINT rutas_estado_check
    CHECK (estado IN ('Activa', 'Inactiva', 'En Mantenimiento'));

COMMENT ON TABLE rutas IS
  'El CATÁLOGO de rutas: qué se ofrece (recorrido, duración, tarifa, restricciones). NO tiene fecha: cada salida vive en ruta_ejecuciones (mig. 078).';
COMMENT ON COLUMN rutas.fecha_visita IS
  'OBSOLETA desde la mig. 078 — la fecha vive en ruta_ejecuciones.fecha. Se conserva hasta la limpieza.';
