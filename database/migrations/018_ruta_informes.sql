-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 018 — Informe post-visita de rutas turísticas
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS ruta_informes (
    id               SERIAL PRIMARY KEY,
    id_ruta          INTEGER NOT NULL REFERENCES rutas(id) ON DELETE CASCADE,
    lugar_exacto     VARCHAR(300),
    mujeres          INTEGER NOT NULL DEFAULT 0,
    hombres          INTEGER NOT NULL DEFAULT 0,
    ninas            INTEGER NOT NULL DEFAULT 0,
    ninos            INTEGER NOT NULL DEFAULT 0,
    total_atendidos  INTEGER NOT NULL DEFAULT 0,
    observaciones    TEXT,
    resumen_visita   TEXT,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by       INTEGER REFERENCES usuarios(id)
);

CREATE UNIQUE INDEX IF NOT EXISTS uq_ruta_informes_ruta ON ruta_informes (id_ruta);

COMMENT ON TABLE  ruta_informes             IS 'Informe demográfico post-visita de una ruta turística';
COMMENT ON COLUMN ruta_informes.ninas       IS 'Participantes libres femeninas (5-11 años)';
COMMENT ON COLUMN ruta_informes.ninos       IS 'Participantes libres masculinos (5-11 años)';
COMMENT ON COLUMN ruta_informes.total_atendidos IS 'Suma calculada: mujeres + hombres + ninas + ninos';

-- Normalizar duracion_estimada histórica a formato H:MM donde sea posible
UPDATE rutas
SET duracion_estimada =
    CASE
        -- Ya está en formato correcto
        WHEN duracion_estimada ~ '^\d{1,2}:\d{2}$' THEN duracion_estimada
        -- "3 horas", "3 hora", "3h"
        WHEN duracion_estimada ~ '^\d{1,2}\s*(hora|horas|h)$' THEN
            REGEXP_REPLACE(duracion_estimada, '^(\d{1,2})\s*.*$', '\1') || ':00'
        -- "1.5 horas" → "1:30"
        WHEN duracion_estimada = '1.5 horas' THEN '1:30'
        WHEN duracion_estimada = '2.5 horas' THEN '2:30'
        WHEN duracion_estimada = '3.5 horas' THEN '3:30'
        -- Dejar los demás sin tocar
        ELSE duracion_estimada
    END
WHERE duracion_estimada IS NOT NULL AND duracion_estimada <> '';
