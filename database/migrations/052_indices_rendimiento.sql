-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 052 — Índices de rendimiento (FKs / filtros en tablas que crecen)
-- Idempotente (IF NOT EXISTS). Solo se agregan los que NO existían ya:
--   · participantes_taller(id_taller) ya cubierto por UNIQUE (id_taller,id_persona)
--   · expediente_documentos(id_empleado), amonestaciones/faltas/carga_familiar...
--     ya tenían índice — no se duplican aquí.
-- Estos apuntan a tablas que crecen con el uso (inscripciones, movimientos,
-- bitácora) y a JOINs frecuentes de cobertura territorial.
-- ─────────────────────────────────────────────────────────────────────────────

-- Participantes por ruta: subconsultas COUNT(*) por ruta + demografía.
CREATE INDEX IF NOT EXISTS idx_part_ruta_ruta
    ON participantes_ruta(id_ruta) WHERE is_active = TRUE;

-- Movimientos de inventario: kardex e "asignación de responsables" (DISTINCT ON
-- por bien, ordenado por fecha) se benefician de (id_inventario, fecha desc).
CREATE INDEX IF NOT EXISTS idx_act_inv_item_fecha
    ON actividad_inventario(id_inventario, fecha DESC) WHERE is_active = TRUE;

-- Cobertura territorial / directorios: JOIN personas → parroquia → municipio.
CREATE INDEX IF NOT EXISTS idx_personas_parroquia
    ON personas(parroquia_id) WHERE is_active = TRUE;

CREATE INDEX IF NOT EXISTS idx_parroquia_municipio
    ON parroquia(id_municipio);

-- Reporte de accesos / auditoría filtrada por tabla + operación (audit_logs crece).
CREATE INDEX IF NOT EXISTS idx_logs_tabla_operacion
    ON audit_logs(tabla_afectada, operacion);
