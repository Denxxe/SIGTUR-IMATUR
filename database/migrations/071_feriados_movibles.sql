-- =====================================================================
-- 071 — Feriados movibles: Carnaval y Semana Santa (2026-2028)
-- =====================================================================
--
-- PROBLEMA QUE RESUELVE
-- `Vacacion` cuenta días hábiles excluyendo fines de semana **y feriados**
-- (tabla `feriados`), y el modelo `Feriado` ya distingue correctamente
-- entre fijos (`recurrente = TRUE`, se repiten cada año en el mismo mes/día,
-- guardados con el año centinela 2000) y **movibles** (`recurrente = FALSE`,
-- fecha puntual de ese año).
--
-- Pero la tabla solo tenía los 12 fijos: **ningún Carnaval ni Semana Santa**.
-- Consecuencia real: el sistema contaba esos 4 días como hábiles y le
-- descontaba al trabajador vacaciones que no le corresponden.
--
-- CÓMO SE OBTUVIERON ESTAS FECHAS
-- Dependen del Domingo de Resurrección, así que se calcularon con el
-- algoritmo Gregoriano anónimo (Meeus/Jones/Butcher) y se **verificaron
-- por dos vías**: contra `easter_date()` de PHP (coinciden las 3 pascuas)
-- y comprobando que cada día derivado cae en su día de semana correcto
-- (Miércoles de Ceniza en miércoles, Lunes de Carnaval en lunes, etc.).
--
--   Pascua:  2026-04-05  ·  2027-03-28  ·  2028-04-16
--   Lunes de Carnaval  = Pascua − 48    Jueves Santo  = Pascua − 3
--   Martes de Carnaval = Pascua − 47    Viernes Santo = Pascua − 2
--
-- Se carga 2026 aunque ya pasó: los períodos de vacaciones se registran de
-- forma retroactiva y el conteo debe dar lo mismo hoy que en marzo.
--
-- ⚠️ MANTENIMIENTO ANUAL
-- Estos feriados **no se repiten** en la misma fecha, así que hay que
-- agregar los del año siguiente antes de que llegue. Se hace desde la UI
-- (`/vacaciones/feriados`, dejando SIN marcar "se repite cada año"), o
-- extendiendo esta migración. Si nadie los carga, el conteo de días vuelve
-- a fallar en silencio — no hay error visible, solo días mal descontados.
--
-- Idempotente: no inserta un feriado que ya exista para esa fecha.
-- =====================================================================

BEGIN;

INSERT INTO feriados (fecha, nombre, recurrente)
SELECT v.fecha::date, v.nombre, FALSE
  FROM (VALUES
        -- 2026
        ('2026-02-16', 'Lunes de Carnaval'),
        ('2026-02-17', 'Martes de Carnaval'),
        ('2026-04-02', 'Jueves Santo'),
        ('2026-04-03', 'Viernes Santo'),
        -- 2027
        ('2027-02-08', 'Lunes de Carnaval'),
        ('2027-02-09', 'Martes de Carnaval'),
        ('2027-03-25', 'Jueves Santo'),
        ('2027-03-26', 'Viernes Santo'),
        -- 2028
        ('2028-02-28', 'Lunes de Carnaval'),
        ('2028-02-29', 'Martes de Carnaval'),
        ('2028-04-13', 'Jueves Santo'),
        ('2028-04-14', 'Viernes Santo')
       ) AS v(fecha, nombre)
 WHERE NOT EXISTS (SELECT 1 FROM feriados f
                    WHERE f.fecha = v.fecha::date
                      AND f.is_active);

COMMIT;

-- =====================================================================
-- Verificación:
--   SELECT EXTRACT(YEAR FROM fecha) AS anio, COUNT(*)
--     FROM feriados WHERE NOT recurrente AND is_active
--    GROUP BY 1 ORDER BY 1;
--   -> 2026: 4 · 2027: 4 · 2028: 4
--
--   Los 12 fijos siguen con el año centinela 2000 y recurrente = TRUE;
--   `Feriado::conjuntos()` los compara por mes-día, no por año.
-- =====================================================================
