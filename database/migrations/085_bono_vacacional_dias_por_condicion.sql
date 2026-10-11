-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 085 — Días del bono vacacional por CONDICIÓN (fijo / contratado)
--
-- Respuesta del cliente a N-1 (2026-10-06): «Personal fijo son 85, se le suma un
-- día adicional por año de servicio; alto nivel, contratado; 45 para contratados,
-- y a todos se les suma un día adicional por año de trabajo».
--
-- Los días base NO dependen del tipo de personal (las 5 hojas de la nómina) sino
-- de la estabilidad del trabajador (`empleados.tipo_contrato`):
--   · Fijo       → 85 + años  (empleados, obreros y alto nivel fijos)
--   · Contratado → 45 + años  (incluido el alto nivel contratado)
--
-- Reemplaza:
--   · las 5 claves por tipo `bono_vac_dias_{alto_nivel,empleados_fijos,
--     obreros_fijos,contratados,comision}` (mig. 059 y 072). Además los obreros
--     NO sumaban años, contra lo que dijo el cliente («a todos»);
--   · `nomina_dias_bono_vac_base` (mig. 072), que daba 75 a todos en la alícuota
--     de la quincena: la plantilla del cliente usaba 75 en todas las hojas y eso
--     es lo que la respuesta descarta. Desde ahora la quincena y el bono
--     vacacional leen los mismos días.
--
-- Las quincenas y bonos ya generados no cambian: guardan sus entradas.
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

BEGIN;

INSERT INTO configuracion_sistema (clave, valor, descripcion)
SELECT v.clave, v.valor, v.descripcion
  FROM (VALUES
        ('bono_vac_dias_fijo', '85',
         'Bono vacacional: días base del personal FIJO (empleados, obreros y alto nivel fijos). A todos se suma 1 día por año de servicio. Respuesta N-1, 2026-10-06.'),
        ('bono_vac_dias_contratado', '45',
         'Bono vacacional: días base del personal CONTRATADO (incluido el alto nivel contratado). A todos se suma 1 día por año de servicio. Respuesta N-1, 2026-10-06.')
       ) AS v(clave, valor, descripcion)
 WHERE NOT EXISTS (SELECT 1 FROM configuracion_sistema c WHERE c.clave = v.clave);

DELETE FROM configuracion_sistema
 WHERE clave IN ('bono_vac_dias_alto_nivel', 'bono_vac_dias_empleados_fijos',
                 'bono_vac_dias_obreros_fijos', 'bono_vac_dias_contratados',
                 'bono_vac_dias_comision', 'nomina_dias_bono_vac_base');

COMMIT;

-- Verificación:
--   SELECT clave, valor FROM configuracion_sistema WHERE clave LIKE 'bono_vac_dias_%';
--     -> bono_vac_dias_contratado 45 · bono_vac_dias_fijo 85
