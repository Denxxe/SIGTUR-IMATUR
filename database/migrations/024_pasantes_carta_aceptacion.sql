-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 024 — Carta de aceptación de pasantes
-- ─────────────────────────────────────────────────────────────────────────────
-- oficio_aceptacion : número de oficio asignado al grupo (ej. PAST-001/2026)
-- tutor_externo     : nombre y cargo del responsable en la institución de origen
--                     a quien se dirige la carta (ej. "Profa. Rosa Rincón, Resp. Proyecto")
-- ─────────────────────────────────────────────────────────────────────────────

ALTER TABLE pasantes
    ADD COLUMN IF NOT EXISTS oficio_aceptacion VARCHAR(25),
    ADD COLUMN IF NOT EXISTS tutor_externo     VARCHAR(200);

-- Correlativo independiente para cartas de aceptación de pasantes
INSERT INTO configuracion_sistema (clave, valor, descripcion) VALUES
    ('correlativo_oficio_pasante', '0',
     'Correlativo de cartas de aceptación de pasantes (PAST-NNN/AAAA)'),
    ('ano_correlativo_pasante', '0',
     'Año del correlativo de cartas de aceptación de pasantes')
ON CONFLICT (clave) DO NOTHING;
