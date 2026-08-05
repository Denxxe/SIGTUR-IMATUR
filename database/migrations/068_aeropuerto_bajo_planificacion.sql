-- =====================================================================
-- 068 — La Oficina del Aeropuerto pasa bajo Planificación y Gestión
--       Turística
-- =====================================================================
--
-- La mig. 067 creó la Oficina de Información Turística (Aeropuerto) como
-- unidad de staff bajo **Presidencia**, por analogía con las otras
-- Oficinas (OAC, Consultoría Jurídica, Auditoría Interna, Relaciones
-- Inter-Institucionales). Se marcó como "por confirmar" porque el
-- organigrama oficial (Manual Descriptivo de Cargos, abril 2024) no la
-- contempla.
--
-- Confirmado por el cliente (2026-08-05): cuelga de la **Dirección de
-- Planificación y Gestión Turística**, que es donde encaja por función
-- —atención al turista— junto a Promoción Turística, Calidad y Servicios
-- Turísticos, Proyectos e Inversión Turística, Formación y Comunicación.
--
-- Solo cambia la jerarquía. No afecta a los bienes ni a su responsable:
-- éste se deriva del departamento del bien (mig. 066), no de su posición
-- en el organigrama.
--
-- Idempotente.
-- =====================================================================

BEGIN;

UPDATE departamentos
   SET id_padre = (SELECT id FROM departamentos
                    WHERE nombre = 'Dirección de Planificación y Gestión Turística'
                      AND is_active LIMIT 1),
       updated_at = CURRENT_TIMESTAMP
 WHERE nombre ILIKE '%Aeropuerto%'
   AND is_active
   AND id_padre IS DISTINCT FROM (SELECT id FROM departamentos
                                   WHERE nombre = 'Dirección de Planificación y Gestión Turística'
                                     AND is_active LIMIT 1);

COMMIT;

-- =====================================================================
-- Verificación:
--   SELECT d.nombre, d.tipo_unidad, p.nombre AS padre
--     FROM departamentos d LEFT JOIN departamentos p ON p.id = d.id_padre
--    WHERE d.nombre ILIKE '%Aeropuerto%';
--   -> padre esperado: Dirección de Planificación y Gestión Turística
-- =====================================================================
