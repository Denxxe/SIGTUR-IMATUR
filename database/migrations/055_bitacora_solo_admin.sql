-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 055 — Bitácora de Auditoría exclusiva del Administrador
-- La Bitácora (historial de cambios de todo el sistema, /auditoria/index) deja
-- de ser un módulo delegable por rol: AuditoriaController::guardAdmin() ahora
-- exige id_rol=1 directamente. Se elimina cualquier concesión previa a otros
-- roles para que el bloqueo tome efecto sin esperar al próximo guardado de
-- permisos en Roles y Permisos. La Papelera de Reciclaje (AuditoriaPapelera)
-- NO se toca: sigue siendo delegable por módulo operativo, como hasta ahora.
-- ─────────────────────────────────────────────────────────────────────────────

DELETE FROM permisos_rol WHERE modulo = 'AuditoriaController' AND id_rol <> 1;

-- Corrige además un bug de fondo: estos 5 módulos ya tenían fila en permisos_rol
-- (sembrada por migraciones anteriores) pero no estaban en la lista blanca de
-- RolesController::getModulos(). Como storePermisos() borra y reinserta solo lo
-- que aparece en esa lista, el próximo guardado de permisos en Roles y Permisos
-- para roles 2/3/5 habría revocado silenciosamente su acceso. Ya se agregaron a
-- getModulos() en código; aquí solo se documenta, no se requiere dato adicional.
