-- =====================================================================
-- 074 — Nómina: trazabilidad del origen de la tasa del dólar
-- =====================================================================
--
-- QUÉ RESUELVE
-- La tasa del dólar del mes (`nomina_parametros_mes.tasa_dolar`) se teclea a
-- mano. Se agrega la posibilidad de SUGERIRLA desde la página del BCV, pero
-- sin que el sistema la dé por buena solo: Talento Humano sigue confirmando
-- el valor. Para que esa sugerencia sea auditable hace falta guardar de dónde
-- salió el número.
--
-- POR QUÉ NO SE CONSULTA AL CALCULAR
-- La consulta ocurre UNA VEZ, al cargar el parámetro del mes. El cálculo de
-- la quincena nunca sale a internet: toma la tasa ya guardada y la congela en
-- `nomina_periodos`. Si el recálculo fuera a buscar "la tasa de hoy", una
-- quincena en borrador cambiaría sola de un día para otro.
--
-- POR QUÉ `tasa_fecha_valor` ES UNA COLUMNA APARTE
-- El BCV no publica "la tasa de hoy": publica una tasa con su **fecha valor**,
-- que es el día hábil en que rige y que suele ser POSTERIOR al día de la
-- consulta (un domingo la página ya muestra la del martes siguiente). Guardar
-- CURRENT_DATE sería guardar una fecha falsa.
--
-- ⚠️ PREGUNTA ABIERTA (N-4, ver docs/PREGUNTAS_CLIENTE.md)
-- No está confirmado que la tasa que IMATUR aplica a la nómina sea la del BCV
-- del día. La plantilla real del cliente trae 36,58 y 36,23 en hojas distintas
-- del mismo período, lo que sugiere un criterio propio. Por eso esto es una
-- SUGERENCIA y el campo manual no desaparece.
--
-- Idempotente.
-- =====================================================================

-- Origen del valor: 'BCV' si vino de la consulta, 'Manual' si se tecleó.
ALTER TABLE public.nomina_parametros_mes
    ADD COLUMN IF NOT EXISTS tasa_fuente VARCHAR(20);

-- Fecha valor publicada por el BCV (el día en que esa tasa rige).
ALTER TABLE public.nomina_parametros_mes
    ADD COLUMN IF NOT EXISTS tasa_fecha_valor DATE;

-- Momento en que se hizo la consulta (distinto de la fecha valor).
ALTER TABLE public.nomina_parametros_mes
    ADD COLUMN IF NOT EXISTS tasa_consultada_at TIMESTAMP;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'nomina_parametros_mes_tasa_fuente_chk'
    ) THEN
        ALTER TABLE public.nomina_parametros_mes
            ADD CONSTRAINT nomina_parametros_mes_tasa_fuente_chk
            CHECK (tasa_fuente IS NULL OR tasa_fuente IN ('BCV', 'Manual'));
    END IF;
END $$;

-- Los meses ya cargados se tecleron a mano: queda constancia.
UPDATE public.nomina_parametros_mes
   SET tasa_fuente = 'Manual'
 WHERE tasa_fuente IS NULL;

COMMENT ON COLUMN public.nomina_parametros_mes.tasa_fuente IS
    'De dónde salió la tasa: BCV (sugerida por la consulta a bcv.org.ve y confirmada por el usuario) o Manual.';
COMMENT ON COLUMN public.nomina_parametros_mes.tasa_fecha_valor IS
    'Fecha valor publicada por el BCV: el día hábil en que rige esa tasa. No es la fecha de la consulta.';
COMMENT ON COLUMN public.nomina_parametros_mes.tasa_consultada_at IS
    'Momento en que se consultó el BCV. Solo se llena cuando tasa_fuente = BCV.';
