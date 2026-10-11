-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 086 — Liquidación de Prestaciones Sociales (fase N-E)
--
-- Digitaliza el formato del cliente `LIQUIDACION MES JULIO 2026.xls`, cuyas
-- tres hojas tienen fórmulas reales (los datos son de prueba):
--
--   · FORMATO          → `liquidaciones` (el documento que se firma).
--   · INTERESES        → `liquidacion_meses` (un renglón por mes de servicio:
--                        salario, salario integral, días depositados, anticipos,
--                        tasa e intereses capitalizados mes a mes).
--   · INTERES DE MORA  → `liquidaciones.detalle_mora` (meses entre el egreso y el
--                        pago × la tasa del mes).
--
-- La «tasa» de la hoja INTERESES NO es el tipo de cambio: la columna se llama
-- «INTERESES BCV» y sus valores (4,68 … 4,94 al mes = 56-59 % anual) son la tasa
-- de interés de prestaciones sociales que publica el BCV, dividida entre 12. Se
-- guarda ANUAL en `nomina_tasas_bcv`, una fila por mes, compartida por todas las
-- liquidaciones (como la cesta ticket en `nomina_parametros_mes`).
--
-- Reglas (respuesta N-3 del 2026-10-06 + fórmulas de la plantilla):
--   · Garantía art. 142 a/b LOTTT: 15 días por trimestre y, desde el 2.º año,
--     2 días adicionales por año, acumulativos hasta 30.
--   · Salario integral diario = normal + normal × días bono vacacional / 360
--     + normal × días aguinaldo / 360 (79/82 y 120/150 de la plantilla).
--   · Art. 142 c/d: se paga lo MAYOR entre la garantía depositada y 30 días por
--     año (o fracción > 6 meses) al último salario integral.
--
-- El número `LIQ-NNN/AAAA` se asigna al CERRAR (un borrador no consume número) y
-- no se recicla al anular. Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

BEGIN;

-- Tasa de interés de prestaciones sociales del BCV, por mes.
CREATE TABLE IF NOT EXISTS nomina_tasas_bcv (
    id                 SERIAL PRIMARY KEY,
    periodo            CHAR(7)      NOT NULL UNIQUE,         -- AAAA-MM
    tasa_prestaciones  NUMERIC(8,4) NOT NULL CHECK (tasa_prestaciones >= 0),  -- % ANUAL
    tasa_activa        NUMERIC(8,4)          CHECK (tasa_activa IS NULL OR tasa_activa >= 0), -- % anual, interés de mora
    observaciones      TEXT,
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by         INT,
    updated_at         TIMESTAMP,
    updated_by         INT,
    CONSTRAINT chk_tasas_bcv_periodo CHECK (periodo ~ '^\d{4}-(0[1-9]|1[0-2])$')
);
COMMENT ON TABLE  nomina_tasas_bcv IS 'Tasa de interés de prestaciones sociales publicada por el BCV, una fila por mes (mig. 086). NO es el tipo de cambio.';
COMMENT ON COLUMN nomina_tasas_bcv.tasa_prestaciones IS 'Porcentaje ANUAL. La hoja INTERESES del cliente aplica cada mes la doceava parte.';
COMMENT ON COLUMN nomina_tasas_bcv.tasa_activa IS 'Porcentaje ANUAL para el interés de mora (art. 92 LOTTT). Si falta, se usa tasa_prestaciones.';

CREATE TABLE IF NOT EXISTS liquidaciones (
    id                      SERIAL PRIMARY KEY,
    numero                  VARCHAR(20) UNIQUE,              -- LIQ-NNN/AAAA, al cerrar
    id_empleado             INT  NOT NULL REFERENCES empleados(id),
    id_egreso               INT  REFERENCES empleados_egresos(id),
    -- Snapshot del trabajador (el documento no debe cambiar si cambia la ficha)
    fecha_ingreso           DATE NOT NULL,                   -- inicio del período liquidado
    fecha_egreso            DATE NOT NULL,
    motivo_egreso           VARCHAR(40),
    fecha_pago              DATE,                            -- para el interés de mora
    tipo_personal           VARCHAR(40),
    condicion               VARCHAR(20),                     -- Empleado Fijo / Obrero Fijo / Contratado
    cargo                   VARCHAR(150),
    departamento            VARCHAR(150),
    anios                   INT  NOT NULL DEFAULT 0,
    meses                   INT  NOT NULL DEFAULT 0,
    dias                    INT  NOT NULL DEFAULT 0,
    -- Bloque «conceptos / monto mensual / monto diario» del FORMATO (último salario)
    sueldo_base             NUMERIC(14,2) NOT NULL DEFAULT 0,
    prima_profesional       NUMERIC(14,2) NOT NULL DEFAULT 0,
    prima_responsabilidad   NUMERIC(14,2) NOT NULL DEFAULT 0,
    prima_antiguedad        NUMERIC(14,2) NOT NULL DEFAULT 0,
    prima_hijos             NUMERIC(14,2) NOT NULL DEFAULT 0,
    bono_transporte         NUMERIC(14,2) NOT NULL DEFAULT 0,
    prima_fond              NUMERIC(14,2) NOT NULL DEFAULT 0,
    prima_discapacidad      NUMERIC(14,2) NOT NULL DEFAULT 0,
    caja_ahorro             NUMERIC(14,2) NOT NULL DEFAULT 0,
    subtotal_mensual        NUMERIC(14,2) NOT NULL DEFAULT 0,
    salario_normal_diario   NUMERIC(14,2) NOT NULL DEFAULT 0,
    dias_alic_vac           NUMERIC(6,2)  NOT NULL DEFAULT 0,
    alic_vac_diaria         NUMERIC(14,2) NOT NULL DEFAULT 0,
    dias_alic_fin           NUMERIC(6,2)  NOT NULL DEFAULT 0,
    alic_fin_diaria         NUMERIC(14,2) NOT NULL DEFAULT 0,
    salario_integral_diario NUMERIC(14,2) NOT NULL DEFAULT 0,
    -- Antigüedad: art. 142 c (retroactivo) vs. a+b (garantía); se paga la mayor
    dias_retroactivo        NUMERIC(8,2)  NOT NULL DEFAULT 0,
    monto_retroactivo       NUMERIC(14,2) NOT NULL DEFAULT 0,
    dias_garantia           NUMERIC(8,2)  NOT NULL DEFAULT 0,
    monto_garantia          NUMERIC(14,2) NOT NULL DEFAULT 0,
    antiguedad_aplicada     VARCHAR(12),                     -- 'garantia' | 'retroactivo'
    monto_antiguedad        NUMERIC(14,2) NOT NULL DEFAULT 0,
    -- Fracciones (días calculados; *_manual = corrección de Talento Humano)
    dias_vacaciones         NUMERIC(8,2)  NOT NULL DEFAULT 0,
    dias_vacaciones_manual  NUMERIC(8,2),
    monto_vacaciones        NUMERIC(14,2) NOT NULL DEFAULT 0,
    dias_bono_vac           NUMERIC(8,2)  NOT NULL DEFAULT 0,
    dias_bono_vac_manual    NUMERIC(8,2),
    monto_bono_vac          NUMERIC(14,2) NOT NULL DEFAULT 0,
    dias_fin_anio           NUMERIC(8,2)  NOT NULL DEFAULT 0,
    dias_fin_anio_manual    NUMERIC(8,2),
    monto_fin_anio          NUMERIC(14,2) NOT NULL DEFAULT 0,
    intereses               NUMERIC(14,2) NOT NULL DEFAULT 0,
    anticipos               NUMERIC(14,2) NOT NULL DEFAULT 0,
    subtotal                NUMERIC(14,2) NOT NULL DEFAULT 0,
    interes_mora            NUMERIC(14,2) NOT NULL DEFAULT 0,
    detalle_mora            JSONB,
    total                   NUMERIC(14,2) NOT NULL DEFAULT 0,
    -- Ciclo de vida
    estado                  VARCHAR(10) NOT NULL DEFAULT 'Borrador'
                            CHECK (estado IN ('Borrador', 'Cerrada', 'Anulada')),
    advertencias            TEXT,
    observaciones           TEXT,
    motivo_anulacion        TEXT,
    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by              INT,
    updated_at              TIMESTAMP,
    updated_by              INT,
    cerrada_at              TIMESTAMP,
    cerrada_by              INT,
    anulada_at              TIMESTAMP,
    anulada_by              INT
);
COMMENT ON TABLE liquidaciones IS 'Liquidación de Prestaciones Sociales (mig. 086): hoja FORMATO del cliente. Snapshot; no cambia si cambia la ficha.';

-- Una sola liquidación viva por egreso (las anuladas no cuentan).
CREATE UNIQUE INDEX IF NOT EXISTS uq_liquidacion_egreso_viva
    ON liquidaciones (id_empleado, fecha_egreso) WHERE estado <> 'Anulada';
CREATE INDEX IF NOT EXISTS idx_liquidaciones_empleado ON liquidaciones (id_empleado);

CREATE TABLE IF NOT EXISTS liquidacion_meses (
    id                  SERIAL PRIMARY KEY,
    id_liquidacion      INT  NOT NULL REFERENCES liquidaciones(id) ON DELETE CASCADE,
    mes                 DATE NOT NULL,                       -- primer día del mes
    -- Entradas (editables mientras la liquidación está en Borrador)
    salario_base        NUMERIC(14,2) NOT NULL DEFAULT 0,
    prima_profesional   NUMERIC(14,2) NOT NULL DEFAULT 0,
    prima_antiguedad    NUMERIC(14,2) NOT NULL DEFAULT 0,
    bono_transporte     NUMERIC(14,2) NOT NULL DEFAULT 0,
    bono_hijos          NUMERIC(14,2) NOT NULL DEFAULT 0,
    dias_alic_vac       NUMERIC(6,2)  NOT NULL DEFAULT 0,
    dias_alic_fin       NUMERIC(6,2)  NOT NULL DEFAULT 0,
    dias_deposito       NUMERIC(6,2)  NOT NULL DEFAULT 0,
    anticipo            NUMERIC(14,2) NOT NULL DEFAULT 0,
    -- Derivadas (las recalcula Liquidacion::calcularMeses)
    total_mensual       NUMERIC(14,2) NOT NULL DEFAULT 0,
    salario_diario      NUMERIC(14,4) NOT NULL DEFAULT 0,
    alic_vac            NUMERIC(14,4) NOT NULL DEFAULT 0,
    alic_fin            NUMERIC(14,4) NOT NULL DEFAULT 0,
    salario_integral    NUMERIC(14,4) NOT NULL DEFAULT 0,
    monto_deposito      NUMERIC(14,2) NOT NULL DEFAULT 0,
    saldo_antes_interes NUMERIC(14,2) NOT NULL DEFAULT 0,
    tasa_mensual        NUMERIC(8,4),                        -- NULL = falta la tasa del BCV
    interes             NUMERIC(14,2) NOT NULL DEFAULT 0,
    saldo               NUMERIC(14,2) NOT NULL DEFAULT 0,
    editado             BOOLEAN NOT NULL DEFAULT FALSE,      -- Talento Humano corrigió el renglón
    CONSTRAINT uq_liquidacion_mes UNIQUE (id_liquidacion, mes)
);
COMMENT ON TABLE liquidacion_meses IS 'Hoja INTERESES del cliente: un renglón por mes de servicio (mig. 086).';

-- Correlativo LIQ-NNN/AAAA (ConfigSistema::generarNumeroOficio('liquidacion'))
INSERT INTO configuracion_sistema (clave, valor, descripcion)
SELECT v.clave, v.valor, v.descripcion
  FROM (VALUES
        ('correlativo_oficio_liquidacion', '0', 'Correlativo de liquidaciones de prestaciones sociales (LIQ-NNN/AAAA). Se asigna al cerrar.'),
        ('ano_correlativo_liquidacion', to_char(CURRENT_DATE, 'YYYY'), 'Año del correlativo de liquidaciones; se reinicia al cambiar de año.'),
        ('liq_trimestre_modo', 'calendario', 'Liquidación: «calendario» deposita los 15 días en marzo, junio, septiembre y diciembre (como la hoja INTERESES del cliente); «ingreso», cada 3 meses contados desde la fecha de ingreso.'),
        -- Cifras de la ley y del contrato: NADA de esto está en el código.
        ('liq_dias_garantia_trimestre',     '15', 'Liquidación: días de garantía por trimestre (art. 142 a LOTTT).'),
        ('liq_dias_adicionales_anio',       '2',  'Liquidación: días adicionales por año de servicio (art. 142 b LOTTT).'),
        ('liq_dias_adicionales_desde_anio', '2',  'Liquidación: año de servicio desde el que se depositan los días adicionales.'),
        ('liq_dias_adicionales_tope',       '30', 'Liquidación: tope acumulado de días adicionales por año.'),
        ('liq_dias_retroactivo_anio',       '30', 'Liquidación: días por año de servicio del cálculo retroactivo (art. 142 c LOTTT).'),
        ('liq_meses_fraccion_retroactivo',  '6',  'Liquidación: la fracción de año que SUPERE estos meses cuenta como año completo (art. 142 c).'),
        ('nomina_dias_mes',                 '30', 'Nómina y liquidación: divisor del salario diario (mensual / 30).'),
        ('nomina_pct_bono_50',              '50', 'Nómina: porcentaje del «Bono 50 %» sobre el sueldo base quincenal.'),
        ('vacaciones_dias_base',            '15', 'Vacaciones: días hábiles del primer año (art. 190 LOTTT); se suma 1 por año.'),
        ('vacaciones_dias_tope',            '30', 'Vacaciones: tope de días hábiles por año (art. 190 LOTTT).')
       ) AS v(clave, valor, descripcion)
 WHERE NOT EXISTS (SELECT 1 FROM configuracion_sistema c WHERE c.clave = v.clave);

COMMIT;

-- Verificación:
--   \d liquidaciones
--   SELECT clave, valor FROM configuracion_sistema WHERE clave LIKE '%liquidacion%' OR clave = 'liq_trimestre_modo';
