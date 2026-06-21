-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 043 — RIF institucional centralizado en configuracion_sistema (U7)
-- Fuente única de verdad para el RIF usado en documentos y reportes.
-- Resuelve la discrepancia G-20008498-7 (ficha/constancia/reportes) vs
-- G-20009499-7 (carta de aceptación de pasantes): el oficial es G-20008498-7.
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO configuracion_sistema (clave, valor, descripcion) VALUES
  ('rif_institucional', 'G-20008498-7', 'RIF del instituto, usado en documentos oficiales y reportes')
ON CONFLICT (clave) DO NOTHING;
