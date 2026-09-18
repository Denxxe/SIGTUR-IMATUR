-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 083 — Rutas: tarifa, exoneración y registro de pagos (T-C)
--
-- La última fase del módulo, y la más exigente de las dos lecturas posibles:
-- R-40 preguntaba si el sistema debe **llevar la contabilidad** de los cobros o
-- solo dejar constancia de que la ruta tenía tarifa, y la respuesta fue
-- *«Sí debe llevar el cobro… y lo cancelado»*. Es un registro de pagos, no un
-- «pagó sí/no».
--
-- CIERRA H-14. Las columnas `rutas.tiene_tarifa` y `tarifa_monto` existen desde
-- la mig. 007 y **nunca se capturaron en ningún formulario**: el reporte
-- informaba «Gratuita» para toda ruta, siempre, incluso si se había cobrado. La
-- columna se retiró del reporte el 2026-08-27 a la espera de D-RT02. D-RT02 está
-- respondida (R-02/R-36…R-42), así que ahora se capturan de verdad.
--
-- ── LO QUE DIJO EL CLIENTE ───────────────────────────────────────────────────
--
-- R-02 · seis programas con su tarifa:
--     Cumaná Histórica 5 $/adulto (menores de 8 gratis) · Exploradores gratuita ·
--     Playa Las Maritas 25 $ · Río Brito 15 $ · Playa Colorada 25 $ ·
--     Altos de Cumaná «se cuadra»
-- R-36 · la tarifa se pacta **en dólares** y se cobra en bolívares **a la tasa
--     del día**  → el catálogo guarda USD; la salida **congela** la tasa
-- R-37 · cobra IMATUR, en una cuenta exclusiva → es configuración, no una tabla
-- R-38 · **pago anticipado con fecha tope**: *«se les tiene una fecha para
--     cancelar y poder planificar la salida»*
-- R-39 · transferencia → captura/voucher adjunto · efectivo → **acta de pago**
-- R-41 · fijo por ruta y persona, **salvo Altos de Sucre**, que depende de lo
--     que el cliente solicite → la tarifa admite «a convenir»
-- R-42 · las exoneraciones **las autoriza la Presidenta**
-- R-03 · las instituciones públicas no pagan **pero igual traen el oficio**, y
--     eso aplica **solo a Cumaná Histórica** → la gratuidad NO es automática por
--     ser institución: es por **ruta + tipo de solicitante**
--
-- ── DECISIONES DE MODELO ─────────────────────────────────────────────────────
--
-- 1. `tarifa_modo` en vez de un booleano. Con `tiene_tarifa` no se distinguía
--    «gratuita» de «a convenir», y R-41 exige las dos: Exploradores no cobra
--    nunca, Altos de Cumaná se cuadra cada vez. Tres modos, no dos estados.
--
-- 2. **La tarifa y la tasa se CONGELAN en la salida.** Si mañana sube el dólar o
--    el cliente cambia el precio del catálogo, lo cobrado en una salida de la
--    semana pasada no puede moverse. Es el mismo criterio que la mig. 074 con la
--    nómina: `generarPeriodo()` usa la tasa congelada y nunca sale a internet al
--    recalcular.
--
-- 3. **Los pagos son una tabla, no un campo.** R-40 pide lo cancelado: puede
--    haber abonos, varias transferencias de un mismo grupo, pagos de distintos
--    representantes. Un `monto_pagado` en la salida no lo aguanta.
--
-- 4. **Anular un pago no lo borra.** Es dinero: queda con su motivo y deja de
--    sumar. Mismo criterio que las amonestaciones (mig. 042) y los permisos
--    (mig. 081).
--
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

-- ── 1. La tarifa, en el CATÁLOGO y en dólares (R-36/R-41) ───────────────────
ALTER TABLE rutas ADD COLUMN IF NOT EXISTS tarifa_modo VARCHAR(20) NOT NULL DEFAULT 'Gratuita';
ALTER TABLE rutas ADD COLUMN IF NOT EXISTS exonera_menores_de SMALLINT;
ALTER TABLE rutas ADD COLUMN IF NOT EXISTS exonera_instituciones BOOLEAN NOT NULL DEFAULT FALSE;

COMMENT ON COLUMN rutas.tarifa_modo IS
  'Gratuita (Exploradores) · Fija (tarifa_monto en USD por persona) · A convenir (Altos de Cumaná, R-41: «depende de lo que el cliente solicite»).';
COMMENT ON COLUMN rutas.tarifa_monto IS
  'Tarifa por persona **en USD** (R-36). Se cobra en bolívares a la tasa del día, que se congela en cada salida. Solo aplica con tarifa_modo = Fija.';
COMMENT ON COLUMN rutas.exonera_menores_de IS
  'Edad por debajo de la cual no se cobra (R-02: Cumaná Histórica, menores de 8). NULL = no hay exoneración por edad.';
COMMENT ON COLUMN rutas.exonera_instituciones IS
  'Las instituciones públicas no pagan ESTA ruta (R-03/R-42). No es automático para todas: aplica solo donde el cliente lo dijo — Cumaná Histórica.';

DO $$
BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'rutas_tarifa_modo_check') THEN
    ALTER TABLE rutas ADD CONSTRAINT rutas_tarifa_modo_check
      CHECK (tarifa_modo IN ('Gratuita', 'Fija', 'A convenir'));
  END IF;
  -- Una tarifa fija sin monto no es una tarifa.
  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'rutas_tarifa_monto_check') THEN
    ALTER TABLE rutas ADD CONSTRAINT rutas_tarifa_monto_check
      CHECK (tarifa_modo <> 'Fija' OR (tarifa_monto IS NOT NULL AND tarifa_monto > 0));
  END IF;
END $$;

-- Las filas viejas: `tiene_tarifa` nunca se capturó, así que todas están en
-- FALSE. Quedan «Gratuita», que es lo que el sistema venía informando.
UPDATE rutas SET tarifa_modo = 'Fija'
 WHERE tiene_tarifa = TRUE AND tarifa_monto IS NOT NULL AND tarifa_monto > 0
   AND tarifa_modo = 'Gratuita';

-- ── 2. Lo que se congela en la SALIDA (R-36/R-38/R-42) ──────────────────────
ALTER TABLE ruta_ejecuciones ADD COLUMN IF NOT EXISTS tarifa_usd         NUMERIC(10,2);
ALTER TABLE ruta_ejecuciones ADD COLUMN IF NOT EXISTS tasa_cambio        NUMERIC(14,4);
ALTER TABLE ruta_ejecuciones ADD COLUMN IF NOT EXISTS tasa_fecha         DATE;
ALTER TABLE ruta_ejecuciones ADD COLUMN IF NOT EXISTS fecha_tope_pago    DATE;
ALTER TABLE ruta_ejecuciones ADD COLUMN IF NOT EXISTS es_exonerada       BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE ruta_ejecuciones ADD COLUMN IF NOT EXISTS motivo_exoneracion TEXT;
ALTER TABLE ruta_ejecuciones ADD COLUMN IF NOT EXISTS exonerada_por      INTEGER;
ALTER TABLE ruta_ejecuciones ADD COLUMN IF NOT EXISTS fecha_exoneracion  DATE;

COMMENT ON COLUMN ruta_ejecuciones.tarifa_usd IS
  'Tarifa por persona **congelada** al programar la salida. No se relee del catálogo: si mañana cambia el precio, lo cobrado no se mueve.';
COMMENT ON COLUMN ruta_ejecuciones.tasa_cambio IS
  'Bs por USD **congelada** para esta salida (R-36, «a la tasa del día»). Se sugiere desde el BCV (TasaBcv, mig. 074) pero se guarda aquí: al recalcular NUNCA se sale a internet.';
COMMENT ON COLUMN ruta_ejecuciones.fecha_tope_pago IS
  'R-38: pago ANTICIPADO. «Se les tiene una fecha para cancelar y poder planificar la salida».';
COMMENT ON COLUMN ruta_ejecuciones.exonerada_por IS
  'Usuario que registró la exoneración. R-42: quien la autoriza es la Presidenta; el sistema deja constancia de quién lo asentó y cuándo.';

DO $$
BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'ruta_ejec_exoneracion_check') THEN
    -- Exonerar sin decir por qué deja un cobro perdonado sin explicación.
    ALTER TABLE ruta_ejecuciones ADD CONSTRAINT ruta_ejec_exoneracion_check
      CHECK (es_exonerada = FALSE OR motivo_exoneracion IS NOT NULL);
  END IF;
END $$;

-- ── 3. Los pagos (R-39/R-40) ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ruta_pagos (
    id              SERIAL PRIMARY KEY,
    id_ejecucion    INTEGER      NOT NULL REFERENCES ruta_ejecuciones(id),
    fecha           DATE         NOT NULL DEFAULT CURRENT_DATE,
    forma           VARCHAR(20)  NOT NULL,

    -- Se guardan las dos caras y la tasa con que se convirtió: el pacto es en
    -- USD (R-36) pero el dinero entra en bolívares.
    monto_bs        NUMERIC(14,2) NOT NULL,
    monto_usd       NUMERIC(10,2),
    tasa_aplicada   NUMERIC(14,4),

    personas        SMALLINT,
    pagador_nombre  VARCHAR(160),
    pagador_cedula  VARCHAR(20),
    referencia      VARCHAR(60),

    -- Transferencia → captura/voucher. Efectivo → acta que levanta IMATUR.
    comprobante_archivo  VARCHAR(255),
    comprobante_original VARCHAR(255),
    acta_numero          VARCHAR(20),

    observaciones    TEXT,
    anulado          BOOLEAN NOT NULL DEFAULT FALSE,
    motivo_anulacion TEXT,

    is_active   BOOLEAN   NOT NULL DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP,
    created_by  INTEGER,
    updated_by  INTEGER,

    CONSTRAINT ruta_pagos_forma_check  CHECK (forma IN ('Transferencia', 'Efectivo', 'Punto de venta')),
    CONSTRAINT ruta_pagos_monto_check  CHECK (monto_bs > 0),
    CONSTRAINT ruta_pagos_anula_check  CHECK (anulado = FALSE OR motivo_anulacion IS NOT NULL)
);

COMMENT ON TABLE ruta_pagos IS
  'Pagos recibidos por una salida (R-40: «sí debe llevar el cobro y lo cancelado»). Es una tabla y no un campo porque hay abonos, varias transferencias del mismo grupo y pagos de distintos representantes.';
COMMENT ON COLUMN ruta_pagos.acta_numero IS
  'Correlativo del acta de pago en efectivo (R-39). El formato lo propusimos nosotros: el cliente dijo «pueden darnos una idea».';
COMMENT ON COLUMN ruta_pagos.anulado IS
  'Anular NO borra: es dinero. La fila queda con su motivo y deja de sumar al total cobrado.';

CREATE INDEX IF NOT EXISTS idx_ruta_pagos_ejecucion ON ruta_pagos (id_ejecucion) WHERE is_active = TRUE;
CREATE UNIQUE INDEX IF NOT EXISTS uq_ruta_pagos_acta ON ruta_pagos (acta_numero) WHERE acta_numero IS NOT NULL;

-- ── 4. Configuración del cobro (R-37) ───────────────────────────────────────
INSERT INTO configuracion_sistema (clave, valor, descripcion) VALUES
  ('rutas_cuenta_cobro', '',
   'Cuenta exclusiva de IMATUR para el cobro de rutas (R-37). Se imprime en el acta de pago.'),
  ('rutas_dias_tope_pago', '3',
   'Días antes de la salida como fecha tope de pago por defecto (R-38). 0 = no se sugiere ninguna.'),
  ('correlativo_oficio_actapago', '0',
   'Último N° de acta de pago en efectivo emitida (R-39). Se reinicia cada año.'),
  ('ano_correlativo_actapago', EXTRACT(YEAR FROM CURRENT_DATE)::TEXT,
   'Año del correlativo de las actas de pago.')
ON CONFLICT (clave) DO NOTHING;
