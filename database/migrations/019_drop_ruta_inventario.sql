-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 019 — Elimina asignación de bienes a rutas
-- ─────────────────────────────────────────────────────────────────────────────
-- Decisión (2026-05-31): se retira el módulo de asignar bienes del inventario a
-- rutas turísticas. La regla de negocio sobre bienes internos que no pueden salir
-- de la institución no está definida aún; se elimina el apartado hasta profundizar.
-- El módulo de Inventario/Bienes NO se toca; solo se elimina la relación con rutas.

DROP TABLE IF EXISTS ruta_inventario CASCADE;
