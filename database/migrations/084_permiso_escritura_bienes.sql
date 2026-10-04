-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 084 — Permiso «Bienes: registrar y modificar» (InventarioEscritura)
--
-- Hasta ahora, quién podía registrar o modificar bienes estaba cableado en el
-- código (`InventarioController::ROLES_ESCRITURA = [1, 4]`): cualquier otro rol
-- con acceso al módulo entraba en solo lectura, y un rol creado desde Roles y
-- Permisos no tenía forma de recibir la escritura sin tocar código.
--
-- Pasa a ser una casilla más de Roles y Permisos, igual que la Papelera
-- (`AuditoriaPapelera`): un token que no es un controlador, sino una capacidad
-- dentro de uno. Se le asigna al rol Inventario (4), que es quien la tenía; el
-- Administrador la tiene por su comodín '*'. Nada cambia para los roles de hoy.
--
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO permisos_rol (id_rol, modulo)
SELECT 4, 'InventarioEscritura'
WHERE EXISTS (SELECT 1 FROM roles WHERE id = 4)
ON CONFLICT (id_rol, modulo) DO NOTHING;
