-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 037 — Normalizar cédulas existentes (solo dígitos)
-- A partir de ahora la cédula se guarda SOLO con números (sin V-/E- ni puntos).
-- Esta migración limpia los registros previos en las tablas con cédula real
-- (personas y visitantes; ambas tienen UNIQUE en cedula).
--   • Quita todo carácter no numérico.
--   • Guarda anti-colisión: si el valor normalizado ya existe en otra fila, se
--     OMITE esa fila (se deja con su formato actual para revisión manual), para
--     no violar la restricción UNIQUE. Esos casos son posibles duplicados reales.
-- Idempotente: tras correrla, solo quedan con caracteres no numéricos las filas
-- que colisionarían (a depurar a mano).
-- NOTA: `cedula_libre` (ID escolar de participantes sin cédula) NO se toca:
--       puede ser alfanumérico por diseño.
-- ─────────────────────────────────────────────────────────────────────────────

BEGIN;

UPDATE personas p
SET cedula = regexp_replace(p.cedula, '\D', '', 'g'),
    updated_at = CURRENT_TIMESTAMP
WHERE p.cedula ~ '[^0-9]'
  AND regexp_replace(p.cedula, '\D', '', 'g') <> ''
  AND NOT EXISTS (
        SELECT 1 FROM personas p2
        WHERE p2.id <> p.id
          AND p2.cedula = regexp_replace(p.cedula, '\D', '', 'g')
  );

UPDATE visitantes v
SET cedula = regexp_replace(v.cedula, '\D', '', 'g'),
    updated_at = CURRENT_TIMESTAMP
WHERE v.cedula IS NOT NULL
  AND v.cedula ~ '[^0-9]'
  AND regexp_replace(v.cedula, '\D', '', 'g') <> ''
  AND NOT EXISTS (
        SELECT 1 FROM visitantes v2
        WHERE v2.id <> v.id
          AND v2.cedula = regexp_replace(v.cedula, '\D', '', 'g')
  );

-- Cédula del docente en participantes de taller (cédula real de un adulto)
UPDATE participantes_taller
SET cedula_docente = regexp_replace(cedula_docente, '\D', '', 'g')
WHERE cedula_docente ~ '[^0-9]'
  AND regexp_replace(cedula_docente, '\D', '', 'g') <> '';

COMMIT;
