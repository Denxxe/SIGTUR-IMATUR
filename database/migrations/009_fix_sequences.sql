-- ============================================================
-- MIGRACIÓN 009: Sincronización de secuencias SERIAL
-- Las secuencias quedaron desfasadas al insertar filas con IDs
-- explícitos en migraciones anteriores. GREATEST garantiza que
-- nunca retrocede si la secuencia ya está por delante del MAX(id).
-- ============================================================

SELECT setval('actividad_inventario_id_seq',   GREATEST((SELECT COALESCE(MAX(id), 1) FROM actividad_inventario),   (SELECT last_value FROM actividad_inventario_id_seq)));
-- `actividades_ruta_id_seq` retirado: la tabla se eliminó en la mig. 070 (H-13).
-- Dejarlo haría fallar esta migración en cualquier instalación ya actualizada.
SELECT setval('asistencias_id_seq',             GREATEST((SELECT COALESCE(MAX(id), 1) FROM asistencias),             (SELECT last_value FROM asistencias_id_seq)));
SELECT setval('audit_logs_id_seq',              GREATEST((SELECT COALESCE(MAX(id), 1) FROM audit_logs),              (SELECT last_value FROM audit_logs_id_seq)));
SELECT setval('cargos_id_seq',                  GREATEST((SELECT COALESCE(MAX(id), 1) FROM cargos),                  (SELECT last_value FROM cargos_id_seq)));
SELECT setval('categorias_id_seq',              GREATEST((SELECT COALESCE(MAX(id), 1) FROM categorias),              (SELECT last_value FROM categorias_id_seq)));
SELECT setval('configuracion_sistema_id_seq',   GREATEST((SELECT COALESCE(MAX(id), 1) FROM configuracion_sistema),   (SELECT last_value FROM configuracion_sistema_id_seq)));
SELECT setval('departamentos_id_seq',           GREATEST((SELECT COALESCE(MAX(id), 1) FROM departamentos),           (SELECT last_value FROM departamentos_id_seq)));
SELECT setval('empleados_id_seq',               GREATEST((SELECT COALESCE(MAX(id), 1) FROM empleados),               (SELECT last_value FROM empleados_id_seq)));
SELECT setval('horarios_id_seq',                GREATEST((SELECT COALESCE(MAX(id), 1) FROM horarios),                (SELECT last_value FROM horarios_id_seq)));
SELECT setval('instituciones_externas_id_seq',  GREATEST((SELECT COALESCE(MAX(id), 1) FROM instituciones_externas),  (SELECT last_value FROM instituciones_externas_id_seq)));
SELECT setval('inventario_id_seq',              GREATEST((SELECT COALESCE(MAX(id), 1) FROM inventario),              (SELECT last_value FROM inventario_id_seq)));
SELECT setval('municipio_id_seq',               GREATEST((SELECT COALESCE(MAX(id), 1) FROM municipio),               (SELECT last_value FROM municipio_id_seq)));
SELECT setval('oficios_id_seq',                 GREATEST((SELECT COALESCE(MAX(id), 1) FROM oficios),                 (SELECT last_value FROM oficios_id_seq)));
SELECT setval('oficios_emitidos_id_seq',        GREATEST((SELECT COALESCE(MAX(id), 1) FROM oficios_emitidos),        (SELECT last_value FROM oficios_emitidos_id_seq)));
SELECT setval('parroquia_id_seq',               GREATEST((SELECT COALESCE(MAX(id), 1) FROM parroquia),               (SELECT last_value FROM parroquia_id_seq)));
SELECT setval('participantes_ruta_id_seq',      GREATEST((SELECT COALESCE(MAX(id), 1) FROM participantes_ruta),      (SELECT last_value FROM participantes_ruta_id_seq)));
SELECT setval('participantes_taller_id_seq',    GREATEST((SELECT COALESCE(MAX(id), 1) FROM participantes_taller),    (SELECT last_value FROM participantes_taller_id_seq)));
SELECT setval('pasante_documentos_id_seq',      GREATEST((SELECT COALESCE(MAX(id), 1) FROM pasante_documentos),      (SELECT last_value FROM pasante_documentos_id_seq)));
SELECT setval('pasantes_id_seq',                GREATEST((SELECT COALESCE(MAX(id), 1) FROM pasantes),                (SELECT last_value FROM pasantes_id_seq)));
SELECT setval('permisos_laborales_id_seq',      GREATEST((SELECT COALESCE(MAX(id), 1) FROM permisos_laborales),      (SELECT last_value FROM permisos_laborales_id_seq)));
SELECT setval('permisos_rol_id_seq',            GREATEST((SELECT COALESCE(MAX(id), 1) FROM permisos_rol),            (SELECT last_value FROM permisos_rol_id_seq)));
SELECT setval('personas_id_seq',                GREATEST((SELECT COALESCE(MAX(id), 1) FROM personas),                (SELECT last_value FROM personas_id_seq)));
SELECT setval('puntos_ruta_id_seq',             GREATEST((SELECT COALESCE(MAX(id), 1) FROM puntos_ruta),             (SELECT last_value FROM puntos_ruta_id_seq)));
SELECT setval('roles_id_seq',                   GREATEST((SELECT COALESCE(MAX(id), 1) FROM roles),                   (SELECT last_value FROM roles_id_seq)));
SELECT setval('ruta_inventario_id_seq',         GREATEST((SELECT COALESCE(MAX(id), 1) FROM ruta_inventario),         (SELECT last_value FROM ruta_inventario_id_seq)));
SELECT setval('rutas_id_seq',                   GREATEST((SELECT COALESCE(MAX(id), 1) FROM rutas),                   (SELECT last_value FROM rutas_id_seq)));
SELECT setval('taller_informes_id_seq',         GREATEST((SELECT COALESCE(MAX(id), 1) FROM taller_informes),         (SELECT last_value FROM taller_informes_id_seq)));
SELECT setval('taller_inventario_id_seq',       GREATEST((SELECT COALESCE(MAX(id), 1) FROM taller_inventario),       (SELECT last_value FROM taller_inventario_id_seq)));
SELECT setval('talleres_id_seq',                GREATEST((SELECT COALESCE(MAX(id), 1) FROM talleres),                (SELECT last_value FROM talleres_id_seq)));
SELECT setval('ubicaciones_id_seq',             GREATEST((SELECT COALESCE(MAX(id), 1) FROM ubicaciones),             (SELECT last_value FROM ubicaciones_id_seq)));
SELECT setval('ubicaciones_formacion_id_seq',   GREATEST((SELECT COALESCE(MAX(id), 1) FROM ubicaciones_formacion),   (SELECT last_value FROM ubicaciones_formacion_id_seq)));
SELECT setval('usuarios_id_seq',                GREATEST((SELECT COALESCE(MAX(id), 1) FROM usuarios),                (SELECT last_value FROM usuarios_id_seq)));
SELECT setval('vacaciones_id_seq',              GREATEST((SELECT COALESCE(MAX(id), 1) FROM vacaciones),              (SELECT last_value FROM vacaciones_id_seq)));
SELECT setval('visitantes_id_seq',              GREATEST((SELECT COALESCE(MAX(id), 1) FROM visitantes),              (SELECT last_value FROM visitantes_id_seq)));
SELECT setval('visitas_id_seq',                 GREATEST((SELECT COALESCE(MAX(id), 1) FROM visitas),                 (SELECT last_value FROM visitas_id_seq)));

-- ============================================================
-- FIN MIGRACIÓN 009
-- ============================================================
