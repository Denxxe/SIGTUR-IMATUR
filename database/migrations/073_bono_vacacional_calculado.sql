-- =====================================================================
-- 073 — Bono Vacacional: de captura manual a cálculo (fase N-D)
-- =====================================================================
--
-- Ver `docs/PLAN_MODULO_NOMINA.md` §4 y §6.4.
--
-- QUÉ RESUELVE
-- La mig. 059 montó el Bono Vacacional como "registro + reporte": Talento
-- Humano teclea cada prima y también el total. La mig. 072 trajo el motor de
-- cálculo, y con él todas las primas dejan de ser capturables:
--
--   prima de profesionalización = base quincenal × % del grado
--   prima de antigüedad         = base quincenal × % de años de administración
--   bono de transporte          = mensual / 2
--   prima por hijos             = nº de hijos × monto
--   sueldo normal diario        = ((quincenal × 2) + cesta ticket) / 30
--   alícuota de bono vacacional = diario × días / 360
--
-- ⚠️ LO QUE **NO** SE PUEDE CALCULAR TODAVÍA: EL TOTAL
--
-- La fórmula del **total del bono vacacional** no está en ninguna fuente. La
-- plantilla de nómina quincenal documenta la ALÍCUOTA (el devengo diario) pero
-- no el monto que se paga; el cliente prometió "un mes de bono vacacional ya
-- calculado con números reales" (audio del 23/07) y no lo entregó.
--
-- Por eso NO se sustituye el total capturado por uno inventado. Se agrega
-- `total_calculado` con la estimación del sistema bajo un supuesto EXPLÍCITO
-- (`sueldo_normal_diario × días correspondientes`) **al lado** del total que
-- confirma Talento Humano, y la UI muestra la diferencia entre ambos.
--
-- Eso convierte la pregunta pendiente en un instrumento que se responde solo:
-- en cuanto llegue un mes real, la diferencia dice si el supuesto acierta. Si
-- acierta, el total pasa a calcularse; si no, la diferencia muestra por dónde
-- corregir. Mientras tanto el sistema no afirma un número que no puede
-- sostener.
--
-- QUÉ MÁS CAMBIA
--   · Quinto tipo de personal (Comisión de Servicio) también aquí; se ensancha
--     `tipo_personal` a 25 para que quepa con holgura.
--   · El período congela cesta ticket y tasa del dólar, igual que
--     `nomina_periodos`: sin eso el diario de un período pasado no se puede
--     reconstruir.
--   · Se guardan las ENTRADAS del cálculo (grado, %, años) además de los
--     resultados, para poder auditar de dónde sale cada número.
--   · `advertencias` por empleado: lo que el cálculo no pudo resolver se avisa,
--     no se paga como 0 en silencio.
--
-- Idempotente.
-- =====================================================================

BEGIN;

-- ── Parámetros congelados del período ─────────────────────────────────
ALTER TABLE bono_vacacional_periodos ADD COLUMN IF NOT EXISTS monto_cesta_ticket NUMERIC(14,2) NOT NULL DEFAULT 0;
ALTER TABLE bono_vacacional_periodos ADD COLUMN IF NOT EXISTS tasa_dolar         NUMERIC(14,4) NOT NULL DEFAULT 0;

COMMENT ON COLUMN bono_vacacional_periodos.monto_cesta_ticket IS
  'Cesta ticket del mes, congelada al generar (entra en el sueldo normal diario). Viene de nomina_parametros_mes.';

-- ── Entradas del cálculo (auditoría del "por qué salió este número") ──
ALTER TABLE bono_vacacional_detalle ADD COLUMN IF NOT EXISTS sueldo_base_quincenal  NUMERIC(14,2) NOT NULL DEFAULT 0;
ALTER TABLE bono_vacacional_detalle ADD COLUMN IF NOT EXISTS codigo_grado           VARCHAR(10);
ALTER TABLE bono_vacacional_detalle ADD COLUMN IF NOT EXISTS pct_profesionalizacion NUMERIC(6,3)  NOT NULL DEFAULT 0;
ALTER TABLE bono_vacacional_detalle ADD COLUMN IF NOT EXISTS anios_administracion   SMALLINT      NOT NULL DEFAULT 0;
ALTER TABLE bono_vacacional_detalle ADD COLUMN IF NOT EXISTS pct_antiguedad         NUMERIC(6,3)  NOT NULL DEFAULT 0;

-- ── Resultados nuevos ─────────────────────────────────────────────────
ALTER TABLE bono_vacacional_detalle ADD COLUMN IF NOT EXISTS sueldo_normal_diario NUMERIC(14,2) NOT NULL DEFAULT 0;
ALTER TABLE bono_vacacional_detalle ADD COLUMN IF NOT EXISTS total_calculado      NUMERIC(14,2);
ALTER TABLE bono_vacacional_detalle ADD COLUMN IF NOT EXISTS advertencias         TEXT;

COMMENT ON COLUMN bono_vacacional_detalle.total_calculado IS
  'ESTIMACIÓN del sistema bajo el supuesto sueldo_normal_diario x días correspondientes. NO es la fórmula del cliente: esa no está documentada en ninguna fuente. Se muestra junto a total_bono_vacacional (el confirmado por Talento Humano) para que la diferencia calibre el supuesto cuando llegue un mes real.';
COMMENT ON COLUMN bono_vacacional_detalle.total_bono_vacacional IS
  'Total que Talento Humano confirma o corrige. Sigue siendo la cifra oficial mientras la fórmula del total no esté confirmada por el cliente.';
COMMENT ON COLUMN bono_vacacional_detalle.advertencias IS
  'Lo que el cálculo no pudo resolver para este empleado. Se muestra en la UI antes de permitir el cierre.';

-- ── Quinto tipo de personal ───────────────────────────────────────────
-- 'Comisión de Servicio' mide exactamente 20 caracteres y la columna era
-- VARCHAR(20): entraba justo. Se ensancha a 25 para no depender de eso.
ALTER TABLE bono_vacacional_detalle ALTER COLUMN tipo_personal TYPE VARCHAR(25);

ALTER TABLE bono_vacacional_detalle DROP CONSTRAINT IF EXISTS bono_vacacional_detalle_tipo_personal_check;
ALTER TABLE bono_vacacional_detalle ADD CONSTRAINT bono_vacacional_detalle_tipo_personal_check
    CHECK (tipo_personal IN ('Alto Nivel', 'Empleados Fijos', 'Obreros Fijos', 'Contratados', 'Comisión de Servicio'));

COMMIT;

-- =====================================================================
-- Verificación:
--   SELECT column_name FROM information_schema.columns
--    WHERE table_name = 'bono_vacacional_detalle'
--      AND column_name IN ('total_calculado','advertencias','codigo_grado',
--                          'sueldo_normal_diario','sueldo_base_quincenal');
--   -> 5 filas.
--
--   Los períodos ya generados conservan sus valores capturados; para pasarlos
--   al cálculo hay que **recalcularlos** desde la UI (solo en Borrador).
--
-- Pendiente del cliente (única pieza que falta para cerrar el bono vacacional):
--   un mes YA CALCULADO con números reales, para contrastar `total_calculado`
--   contra `total_bono_vacacional` y confirmar o corregir el supuesto.
-- =====================================================================
