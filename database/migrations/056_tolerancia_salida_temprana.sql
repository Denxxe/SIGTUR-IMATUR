-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 056 — Tolerancia de salida anticipada (asistencia)
-- Al marcar salida antes de la hora_salida del horario asignado al empleado,
-- se exige un motivo (ver AsistenciasController::marcar/estadoMarcaje). Esta
-- clave define cuántos minutos antes de esa hora se toleran sin pedir motivo,
-- de forma independiente a `minutos_tolerancia_puntualidad` (que es para la
-- impuntualidad de ENTRADA).
-- Idempotente (guard NOT EXISTS).
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO configuracion_sistema (clave, valor, descripcion)
SELECT 'minutos_tolerancia_salida_temprana', '10',
       'Minutos de tolerancia antes de la hora de salida del horario, antes de exigir motivo de salida anticipada'
WHERE NOT EXISTS (SELECT 1 FROM configuracion_sistema WHERE clave = 'minutos_tolerancia_salida_temprana');
