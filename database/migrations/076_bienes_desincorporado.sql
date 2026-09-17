-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 076 — Bienes: «Dado de baja» pasa a llamarse «Desincorporado» (C-6)
--
-- Exigencia del cliente tras el cambio de procedimiento del 2026-09-02: el acto
-- y su documento se llaman **desincorporación**, no «baja». El documento ya se
-- llama «Acta de Desincorporación»; el estatus del bien tenía que acompañarlo.
--
-- POR QUÉ SE RENOMBRA EL VALOR Y NO SOLO EL RÓTULO
-- Se evaluó dejar el valor 'Dado de baja' en la base y mostrar otra etiqueta en
-- pantalla. Se descartó: la bitácora (`audit_logs`) y los reportes seguirían
-- diciendo lo viejo, y quedaría una divergencia permanente entre lo que ve el
-- usuario y lo que guarda el sistema. Renombrar el valor cuesta CERO ahora
-- —`inventario` está en 0 filas y ningún `audit_logs` menciona el estatus— y
-- mucho más después de cargar los ~142 bienes reales.
--
-- El UPDATE va igual, por si esta migración se aplica sobre una base que ya
-- tenga datos.
--
-- ⚠️ Esta migración va acompañada de un cambio de código imprescindible: catorce
-- consultas tenían el texto 'Dado de baja' CABLEADO en el SQL (Dashboard,
-- indicadores, reportes, Centro de Alertas, dotación). Si se aplica el SQL sin
-- ese cambio, esas consultas dejan de filtrar y los bienes desincorporados
-- vuelven a contarse como inventario activo — en silencio. Ahora todas usan
-- `Inventario::EST_BAJA` como parámetro.
--
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

-- 1. El CHECK primero en su versión permisiva, para poder mover las filas.
ALTER TABLE inventario DROP CONSTRAINT IF EXISTS inventario_estatus_check;

-- 2. Filas existentes (0 hoy; la sentencia protege instalaciones con datos).
UPDATE inventario SET estatus = 'Desincorporado' WHERE estatus = 'Dado de baja';

-- 3. CHECK con el catálogo nuevo. Debe coincidir exactamente con
--    Inventario::ESTATUS — si divergen, un alta válida en PHP falla en la BD.
ALTER TABLE inventario ADD CONSTRAINT inventario_estatus_check
    CHECK (estatus IN (
        'En espera de codificación',
        'Activo',
        'En mantenimiento',
        'Extraviado',
        'Robado',
        'Desincorporado'
    ));

-- 4. La bitácora es un registro histórico y NO se reescribe: si un movimiento
--    se guardó en su momento como 'Dado de baja', eso fue lo que pasó y así
--    debe quedar. Hoy son 0 filas de todos modos.
COMMENT ON COLUMN inventario.estatus IS
  'Estatus administrativo del bien. «Desincorporado» (antes «Dado de baja», mig. 076) lo saca del inventario activo conservando el registro (B-38); no confundir con is_active = FALSE, que es la papelera de registros creados por error.';
