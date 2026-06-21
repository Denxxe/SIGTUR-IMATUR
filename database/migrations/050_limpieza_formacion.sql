-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 050 — Limpieza de elementos sin uso en Formación (3F)
-- Decisión del cliente (2026-06-21): NO se usa `taller_inventario` (D-FO07) ni
-- la bandera `participantes_taller.es_brigadista` (D-FO08). Se eliminan.
-- Resuelve parte de la auditoría H-09 (columnas inertes) y H-10 (tablas sin UI).
-- ─────────────────────────────────────────────────────────────────────────────

ALTER TABLE participantes_taller DROP COLUMN IF EXISTS es_brigadista;
DROP TABLE IF EXISTS taller_inventario;
