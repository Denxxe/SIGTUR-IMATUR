-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 049 — Horario Estándar institucional 8:00am–2:00pm (O5)
-- La institución cambió el horario habitual de 8am–4pm a 8am–2pm por razones de
-- infraestructura. Se ajusta el horario "Estándar" (los demás ya estaban a 2pm).
-- Idempotente: solo actúa si aún está en 16:00.
-- ─────────────────────────────────────────────────────────────────────────────

UPDATE horarios
SET hora_salida = '14:00:00', updated_at = CURRENT_TIMESTAMP
WHERE nombre = 'Estándar' AND hora_salida = '16:00:00';
