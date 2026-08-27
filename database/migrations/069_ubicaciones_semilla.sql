-- =====================================================================
-- 069 — Semilla de ubicaciones: sedes, oficinas y depósito
-- =====================================================================
--
-- PROBLEMA QUE RESUELVE
-- `InventarioController::store()` exige `id_ubicacion > 0` y la tabla
-- `ubicaciones` estaba VACÍA, así que era imposible registrar un bien:
-- todo el módulo construido en las migraciones 062-067 quedaba
-- inalcanzable por falta de catálogo.
--
-- QUÉ SIEMBRA
--   1. Una ubicación por cada departamento activo. El departamento es la
--      unidad de responsabilidad: el responsable del bien se DERIVA del
--      departamento de su ubicación (mig. 066), así que sin ubicación por
--      departamento no hay responsable.
--   2. El **Depósito General** (`es_deposito = TRUE`), área común de los
--      bienes sin asignar (B-23/B-25). Su responsable no sale de su propio
--      departamento sino de `bienes_depto_autoriza` (mig. 063), pero la
--      columna es NOT NULL: se le asigna *Compra de Bienes y Servicios*,
--      que es donde está la Coordinadora de Bienes.
--   3. La `sede` de cada ubicación (B-24): las dos sedes son *Sede
--      Principal* y *Aeropuerto de Cumaná*. La Oficina de Información
--      Turística (Aeropuerto) —departamento propio desde la mig. 067—
--      queda en la segunda; el resto en la primera.
--
-- El NOMBRE de cada ubicación arranca igual al del departamento porque es
-- el dato cierto que tenemos: son los espacios físicos que ocupa cada
-- unidad. El cliente los renombra a su referencia real (planta, mezzanina,
-- cubículo) desde *Inventario → Ubicaciones*, y puede agregar varias
-- ubicaciones por departamento — la relación es 1:N.
--
-- Idempotente: no duplica una ubicación que ya exista con el mismo nombre
-- en el mismo departamento, así que puede correrse dos veces sin efecto.
-- =====================================================================

BEGIN;

-- ── 1 y 3. Una ubicación por departamento activo, con su sede ─────────
INSERT INTO ubicaciones (nombre, descripcion, "departamento _d", sede, es_deposito)
SELECT d.nombre,
       'Espacio físico asignado a ' || d.nombre ||
       '. Renombrar según la referencia real del sitio.',
       d.id,
       CASE WHEN d.nombre ILIKE '%Aeropuerto%' THEN 'Aeropuerto de Cumaná'
            ELSE 'Sede Principal' END,
       FALSE
  FROM departamentos d
 WHERE d.is_active
   AND NOT EXISTS (SELECT 1 FROM ubicaciones u
                    WHERE u."departamento _d" = d.id
                      AND u.nombre = d.nombre);

-- ── 2. Depósito General ──────────────────────────────────────────────
INSERT INTO ubicaciones (nombre, descripcion, "departamento _d", sede, es_deposito)
SELECT 'Depósito General',
       'Área común donde permanecen los bienes que no están asignados a un '
       || 'departamento. Su responsable se deriva del departamento que '
       || 'autoriza los bienes (bienes_depto_autoriza), no de este.',
       (SELECT id FROM departamentos
         WHERE nombre = 'Compra de Bienes y Servicios' AND is_active
         LIMIT 1),
       'Sede Principal',
       TRUE
 WHERE EXISTS (SELECT 1 FROM departamentos
                WHERE nombre = 'Compra de Bienes y Servicios' AND is_active)
   AND NOT EXISTS (SELECT 1 FROM ubicaciones WHERE es_deposito AND is_active);

COMMIT;

-- =====================================================================
-- Verificación:
--   SELECT u.id, u.nombre, u.sede, u.es_deposito, d.nombre AS departamento
--     FROM ubicaciones u
--     LEFT JOIN departamentos d ON d.id = u."departamento _d"
--    WHERE u.is_active
--    ORDER BY u.es_deposito DESC, u.sede, u.nombre;
--   -> 24 oficinas (23 en Sede Principal + 1 en Aeropuerto de Cumaná)
--      + 1 Depósito General.
--
-- Pendiente de datos (no es programación, ver docs/BACKLOG.md):
--   · Asignar el cargo de Coordinador en *Compra de Bienes y Servicios*:
--     mientras el puesto esté vacante el sistema bloquea los movimientos
--     de bienes (por diseño, B-32).
--   · Cargar los ~142 bienes reales (B-04).
-- =====================================================================
