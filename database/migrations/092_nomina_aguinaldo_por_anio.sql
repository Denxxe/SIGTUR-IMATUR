-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 092 — Días del aguinaldo por año
--
-- Los días del aguinaldo (bonificación de fin de año) eran un escalar sin
-- histórico, `nomina_dias_bono_fin_anio` = 150. El cliente indicó que fueron
-- **120 días hasta 2022 y 150 desde 2023**. Con un solo valor, una liquidación
-- que abarcara meses anteriores a 2023 sembraba 150 en todos y había que
-- corregir esas filas a mano.
--
-- Ahora es una escala por año: cada fila rige desde `desde_anio` hasta el año
-- anterior a la fila siguiente; la última rige en adelante. La nómina toma los
-- días del año de su fecha de corte, y la liquidación los del año de cada mes.
--
-- El valor vigente se copia del escalar (por si se había cambiado) y el
-- escalar se elimina, para que no haya dos fuentes del mismo dato.
--
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

BEGIN;

CREATE TABLE IF NOT EXISTS nomina_dias_aguinaldo (
    desde_anio  SMALLINT     PRIMARY KEY CHECK (desde_anio BETWEEN 1900 AND 2100),
    dias        NUMERIC(6,2) NOT NULL CHECK (dias > 0 AND dias <= 365),
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP,
    updated_by  INTEGER
);

COMMENT ON TABLE nomina_dias_aguinaldo IS
    'Días del aguinaldo por año (mig. 092). Cada fila rige desde desde_anio hasta el año anterior a la fila siguiente. 1900 = «desde siempre».';

-- 120 días hasta 2022 (según el cliente)…
INSERT INTO nomina_dias_aguinaldo (desde_anio, dias)
VALUES (1900, 120)
ON CONFLICT (desde_anio) DO NOTHING;

-- …y desde 2023 el valor vigente del escalar (150 si no estaba).
INSERT INTO nomina_dias_aguinaldo (desde_anio, dias)
SELECT 2023, COALESCE(
           (SELECT NULLIF(valor, '')::numeric FROM configuracion_sistema
             WHERE clave = 'nomina_dias_bono_fin_anio'), 150)
ON CONFLICT (desde_anio) DO NOTHING;

DELETE FROM configuracion_sistema WHERE clave = 'nomina_dias_bono_fin_anio';

COMMIT;

-- =====================================================================
-- Verificación:
--   SELECT * FROM nomina_dias_aguinaldo ORDER BY desde_anio;   -- 1900→120, 2023→150
--   SELECT 1 FROM configuracion_sistema WHERE clave = 'nomina_dias_bono_fin_anio';  -- 0 filas
-- =====================================================================
