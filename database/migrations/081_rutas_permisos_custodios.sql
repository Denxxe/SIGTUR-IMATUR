-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 081 — Rutas: oficios de permiso a las instituciones custodias (T-H)
--
-- Es la otra mitad del **pedido #2 del cliente** (R-64: *«los reportes y los
-- oficios»*), y un proceso que el módulo no modelaba en absoluto. Salió de una
-- pregunta que iba por otro lado: R-20 preguntaba por el **costo de entrada** a
-- museos y castillos, y la respuesta describió un trámite entero:
--
--   > IMATUR **envía un oficio a cada institución custodia** (museos, castillos,
--   > fundaciones) para poder visitarla. **Todas las rutas de la semana
--   > planificada van en un solo oficio**, para agilizar el trámite. Se lleva
--   > control del estado de cada uno —si llegó, si se dio el pase, si se
--   > rechazó— y lo notifica el **Director de Relaciones Inter-Institucionales**,
--   > que además corrobora que las instituciones estén disponibles.
--   > Estados: **aceptado / en espera**.
--
-- POR QUÉ ES UNA ENTIDAD PROPIA Y NO UNA COLUMNA DE `ruta_ejecuciones`:
-- **un permiso cubre VARIAS salidas** —las de toda la semana— y una salida puede
-- necesitar **varios permisos** (una ruta pasa por el Castillo y por la Basílica,
-- que son custodios distintos). Es una relación N:M, igual que el Acta de
-- Desincorporación de Bienes: cabecera + renglones.
--
-- R-52 lo confirma desde otro ángulo: de una ruta se emiten **tres** documentos,
-- y uno son «los permisos de los lugares a visitar». Los otros dos ya existen
-- (Ficha Institucional, mig. 080; oficio saliente al punto, `oficios_emitidos`).
--
-- ⚠️ EL IMPRIMIBLE ES PROVISIONAL: el cliente no ha entregado el formato del
-- oficio de permiso. El flujo, los estados y lo que se registra **sí** son los
-- que él describió, así que cuando llegue el formato se sustituye únicamente
-- `permiso_imprimible.php`; ni la tabla ni el flujo se tocan. Mismo criterio que
-- con el Acta de Desincorporación (mig. 077).
--
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

-- ── 1. Quién custodia cada punto (R-06) ─────────────────────────────────────
-- R-06 dice que coordinar el acceso con las fundaciones externas es **uno de los
-- tres dolores principales** del módulo, y sugería registrar el ente custodio.
-- Se esperó a R-20 —como decía el plan— y ahora tiene para qué servir: es lo que
-- permite que el sistema diga a qué instituciones hay que pedirles permiso para
-- las salidas de una semana, en vez de que alguien lo recuerde de memoria.
ALTER TABLE puntos_ruta ADD COLUMN IF NOT EXISTS ente_custodio VARCHAR(160);

COMMENT ON COLUMN puntos_ruta.ente_custodio IS
  'Institución que custodia el punto y a la que hay que pedir permiso de acceso (R-06/R-20): Fundación Castillo San Antonio, Basílica Santa Inés… NULL = espacio público sin custodio.';

-- ── 2. El oficio de permiso ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ruta_permisos (
    id                  SERIAL PRIMARY KEY,
    numero              VARCHAR(20)  NOT NULL,
    fecha               DATE         NOT NULL DEFAULT CURRENT_DATE,

    -- A quién se le pide
    institucion         VARCHAR(160) NOT NULL,
    destinatario_nombre VARCHAR(160),
    destinatario_cargo  VARCHAR(120),

    -- El período que cubre: la semana planificada (R-20)
    semana_desde        DATE         NOT NULL,
    semana_hasta        DATE         NOT NULL,

    -- El trámite
    estado              VARCHAR(20)  NOT NULL DEFAULT 'En espera',
    fecha_respuesta     DATE,
    observaciones       TEXT,
    motivo_anulacion    TEXT,

    -- Quien lo tramita: el Director de Relaciones Inter-Institucionales (R-20)
    id_responsable      INTEGER REFERENCES empleados(id),

    -- El pase devuelto por la institución, escaneado
    respuesta_archivo   VARCHAR(255),
    respuesta_original  VARCHAR(255),

    is_active   BOOLEAN   NOT NULL DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP,
    deleted_at  TIMESTAMP,
    created_by  INTEGER,
    updated_by  INTEGER,
    deleted_by  INTEGER,

    CONSTRAINT ruta_permisos_estado_check
        CHECK (estado IN ('En espera', 'Aceptado', 'Rechazado', 'Anulado')),
    CONSTRAINT ruta_permisos_semana_check
        CHECK (semana_hasta >= semana_desde)
);

COMMENT ON TABLE ruta_permisos IS
  'Oficio de permiso de acceso a una institución custodia (R-20). Uno cubre TODAS las salidas de la semana hacia esa institución — por eso los renglones viven en ruta_permiso_salidas.';
COMMENT ON COLUMN ruta_permisos.estado IS
  'En espera (se envió, no hay respuesta) · Aceptado (dieron el pase) · Rechazado · Anulado. R-20 nombra los dos primeros; «Rechazado» sale de «si se rechazó» en la misma respuesta.';
COMMENT ON COLUMN ruta_permisos.id_responsable IS
  'El Director de Relaciones Inter-Institucionales, que tramita y notifica (R-20). Se elige de empleados, no se escribe a mano.';

-- El correlativo no se recicla: un número anulado se pierde, como en Bienes.
CREATE UNIQUE INDEX IF NOT EXISTS uq_ruta_permisos_numero ON ruta_permisos (numero);
CREATE INDEX IF NOT EXISTS idx_ruta_permisos_estado ON ruta_permisos (estado) WHERE is_active = TRUE;
CREATE INDEX IF NOT EXISTS idx_ruta_permisos_semana ON ruta_permisos (semana_desde, semana_hasta);

-- ── 3. Qué salidas cubre cada permiso ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS ruta_permiso_salidas (
    id           SERIAL PRIMARY KEY,
    id_permiso   INTEGER NOT NULL REFERENCES ruta_permisos(id) ON DELETE CASCADE,
    id_ejecucion INTEGER NOT NULL REFERENCES ruta_ejecuciones(id),
    is_active    BOOLEAN NOT NULL DEFAULT TRUE,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by   INTEGER,
    CONSTRAINT uq_permiso_salida UNIQUE (id_permiso, id_ejecucion)
);

COMMENT ON TABLE ruta_permiso_salidas IS
  'Salidas cubiertas por un permiso. N:M a propósito: un permiso cubre varias salidas (toda la semana) y una salida puede necesitar varios permisos (una ruta pasa por dos custodios distintos).';

CREATE INDEX IF NOT EXISTS idx_permiso_salidas_ejecucion ON ruta_permiso_salidas (id_ejecucion);

-- ── 4. Correlativo propio ───────────────────────────────────────────────────
-- Separado del de rutas: son documentos distintos y el cliente los cuenta
-- aparte. Mismo mecanismo que `acta`, `bienes`, `constancia`…
INSERT INTO configuracion_sistema (clave, valor, descripcion) VALUES
  ('correlativo_oficio_permiso', '0',
   'Último N° de oficio de permiso a institución custodia emitido (R-20). Se reinicia cada año.'),
  ('ano_correlativo_permiso', EXTRACT(YEAR FROM CURRENT_DATE)::TEXT,
   'Año del correlativo de los oficios de permiso.')
ON CONFLICT (clave) DO NOTHING;
