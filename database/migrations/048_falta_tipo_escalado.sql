-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 048 — Faltas: tipo + escalado a amonestación (3E)
-- Aclaración de negocio: las FALTAS (inasistencia injustificada o incumplimiento
-- de reglas) se acumulan y PUEDEN generar AMONESTACIONES; 3 amonestaciones = causa
-- de despido. Ambas se anulan con el mismo flujo (motivo de anulación, B14).
-- ─────────────────────────────────────────────────────────────────────────────

ALTER TABLE faltas ADD COLUMN IF NOT EXISTS tipo VARCHAR(40) NOT NULL DEFAULT 'Inasistencia injustificada';

-- Vínculo opcional: amonestación generada a partir de una falta.
ALTER TABLE amonestaciones ADD COLUMN IF NOT EXISTS id_falta_origen INTEGER;
