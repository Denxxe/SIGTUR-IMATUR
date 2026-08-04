-- =====================================================================
-- 061 — Datos institucionales del carnet (dirección, lema) + corrección
--       del teléfono y el correo oficiales
-- =====================================================================
--
-- Origen: el cliente entregó el carnet físico vigente (2026-08-04). Los
-- datos de contacto impresos en él son los REALES y no coincidían con
-- los que tenía el sistema.
--
--   Teléfono : (0293) 431-4073        ->  0293-4310178
--   Correo   : imatur.cumana@gmail.com ->  Sucreimatur@gmail.com
--
-- ⚠️ OJO — el correo institucional NO se usa solo en el carnet:
--    · es el REMITENTE de los correos de recuperación de contraseña
--      (`sigtur_enviar_correo()` lo lee de esta clave, mig. 058);
--    · aparece en constancias, oficios y membretes de reportes.
--    Al cambiarlo, el correo de recuperación pasará a enviarse desde
--    Sucreimatur@gmail.com. Las credenciales SMTP deben corresponder a
--    ESA cuenta (siguen pendientes, ver BACKLOG §3.0).
--
-- Claves nuevas, ambas editables en /config:
--   direccion_institucion : aparece en el carnet
--   lema_institucion      : "Historia y Porvenir"
--
-- Idempotente: los INSERT usan NOT EXISTS y los UPDATE son de valor fijo.
-- =====================================================================

BEGIN;

-- 1. Corrección de los datos de contacto -------------------------------
UPDATE configuracion_sistema
   SET valor = '0293-4310178',
       updated_at = CURRENT_TIMESTAMP
 WHERE clave = 'telf_institucion';

UPDATE configuracion_sistema
   SET valor = 'Sucreimatur@gmail.com',
       updated_at = CURRENT_TIMESTAMP
 WHERE clave = 'correo_institucion';

-- 2. Dirección institucional (nueva) -----------------------------------
INSERT INTO configuracion_sistema (clave, valor, descripcion, updated_at)
SELECT 'direccion_institucion',
       'Estado Sucre, municipio Sucre, Cumaná, Calle Sucre, Casa Nº11',
       'Dirección física del instituto. Aparece en el carnet institucional.',
       CURRENT_TIMESTAMP
 WHERE NOT EXISTS (SELECT 1 FROM configuracion_sistema WHERE clave = 'direccion_institucion');

-- 3. Lema institucional (nueva) ----------------------------------------
INSERT INTO configuracion_sistema (clave, valor, descripcion, updated_at)
SELECT 'lema_institucion',
       'Historia y Porvenir',
       'Lema del instituto. Aparece al pie del carnet institucional.',
       CURRENT_TIMESTAMP
 WHERE NOT EXISTS (SELECT 1 FROM configuracion_sistema WHERE clave = 'lema_institucion');

COMMIT;

-- =====================================================================
-- Verificación:
--   SELECT clave, valor FROM configuracion_sistema
--    WHERE clave IN ('telf_institucion','correo_institucion',
--                    'direccion_institucion','lema_institucion');
-- =====================================================================
