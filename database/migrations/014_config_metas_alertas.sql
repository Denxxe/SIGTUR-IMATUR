-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 014 — parámetros operativos en configuracion_sistema
-- ─────────────────────────────────────────────────────────────────────────────

-- 1. Metas anuales (meta_rutas_anio ya existe desde 013; confirmar sin duplicar)
INSERT INTO configuracion_sistema (clave, valor, descripcion) VALUES
  ('meta_talleres_anio',     '0',  'Meta anual de actividades formativas a ejecutar'),
  ('meta_rutas_anio',        '0',  'Meta anual de rutas turísticas a ejecutar')
ON CONFLICT (clave) DO NOTHING;

-- 2. Umbrales de alertas del dashboard
INSERT INTO configuracion_sistema (clave, valor, descripcion) VALUES
  ('dias_preaviso_contrato', '30', 'Días de anticipación para alertar sobre contratos vencientes'),
  ('dias_preaviso_pasante',  '15', 'Días de anticipación para alertar sobre pasantes próximos a culminar')
ON CONFLICT (clave) DO NOTHING;
