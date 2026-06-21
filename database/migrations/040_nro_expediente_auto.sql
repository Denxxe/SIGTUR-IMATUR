-- 040_nro_expediente_auto.sql
-- B2 — Folio de expediente automático y permanente, derivado del id del empleado.
-- El sistema asigna 'EXP-####' al registrar; el campo deja de ser editable en la UI.
-- Idempotente: solo rellena los expedientes sin número (NULL); respeta los ya asignados.
-- La columna empleados.nro_expediente ya es VARCHAR(20) UNIQUE nullable (cabe 'EXP-' + 6 dígitos).

BEGIN;

-- Rellena los expedientes sin número y normaliza los folios que no siguen el
-- formato 'EXP-####' (datos previos sin un sistema de folios establecido).
-- Deriva del id (único), por lo que no hay riesgo de colisión con el UNIQUE.
UPDATE empleados
   SET nro_expediente = 'EXP-' || LPAD(id::text, 4, '0')
 WHERE nro_expediente IS NULL
    OR TRIM(nro_expediente) = ''
    OR nro_expediente !~ '^EXP-[0-9]+$';

COMMIT;
