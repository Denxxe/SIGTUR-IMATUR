-- ============================================================
-- Migración 006: Mejoras Módulo Formación
-- Fecha: 2026-05-08
-- ============================================================

BEGIN;

-- ── 1. talleres: tipo interno/externo y tipo de entidad ──────────────────────

ALTER TABLE talleres
    ADD COLUMN IF NOT EXISTS es_interna  BOOLEAN NOT NULL DEFAULT FALSE,
    ADD COLUMN IF NOT EXISTS tipo_ente   VARCHAR(50);

ALTER TABLE talleres
    ADD CONSTRAINT talleres_tipo_ente_check
    CHECK (tipo_ente IS NULL OR tipo_ente IN (
        'Escuela','Liceo','Comunidad','Prestador de Servicio','IMATUR'
    ));

-- Ampliar tipo_actividad para incluir 'Inducción'
ALTER TABLE talleres
    DROP CONSTRAINT IF EXISTS talleres_tipo_actividad_check;

ALTER TABLE talleres
    ADD CONSTRAINT talleres_tipo_actividad_check
    CHECK (tipo_actividad IN ('Taller','Charla','Inducción'));

-- ── 2. participantes_taller: brigadista y docente acompañante ────────────────

ALTER TABLE participantes_taller
    ADD COLUMN IF NOT EXISTS es_brigadista  BOOLEAN NOT NULL DEFAULT FALSE,
    ADD COLUMN IF NOT EXISTS nombre_docente VARCHAR(100),
    ADD COLUMN IF NOT EXISTS cedula_docente VARCHAR(20);

-- ── 3. rutas: prerequisito de formación ─────────────────────────────────────

ALTER TABLE rutas
    ADD COLUMN IF NOT EXISTS requiere_formacion BOOLEAN NOT NULL DEFAULT FALSE;

COMMIT;
