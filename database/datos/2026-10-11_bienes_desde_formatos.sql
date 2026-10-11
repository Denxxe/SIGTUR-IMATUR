-- ─────────────────────────────────────────────────────────────────────────────
-- Carga de datos (NO es una migración): los 23 bienes reales que aparecen en los
-- documentos entregados por el cliente, transcritos de las imágenes de
-- docs/formatos/:
--
--   · BM-1_inventario_bienes_muebles_alcaldia.jpeg  (sello 26/04/2025) → 18 bienes
--     YA CODIFICADOS por la Alcaldía, por departamento.
--   · oficio_relacion_bienes_nuevos_alcaldia_2026-06-10.jpg (Oficio N° 179/2026)
--     → 4 bienes comprados, SIN código, con su monto.
--   · documento_donacion_bien_2026-02-18.jpg → 1 bien donado (cafetera).
--
-- ⚠️ ES UNA MUESTRA, NO EL INVENTARIO: la foto del BM-1 es una sola hoja (los N°
-- de orden llegan a 171 y solo se ven 18). La carga completa sigue esperando el
-- inventario vigente de la encargada (BACKLOG §0.2).
--
-- Lo que los documentos NO dicen y se puso por defecto, marcado en observaciones
-- con «[por confirmar]» para que la encargada lo revise:
--   · condición física → «Bueno» (el BM-1 no la trae);
--   · categoría interna → la más cercana a la descripción;
--   · origen de los bienes del BM-1 → «Compra» (no consta);
--   · ubicación de los 5 bienes del oficio y la donación → Depósito General (no consta).
-- La ONU y el router del oficio van en UN renglón porque así figuran, con un solo
-- monto; los 2 banderines se registran por separado (B-09) con la mitad cada uno.
--
-- Idempotente: no duplica si se ejecuta dos veces (marca «[carga 2026-10-11]»).
-- Ejecutar:  psql -U postgres -d "SIGTUR-IMATUR" -f database/datos/2026-10-11_bienes_desde_formatos.sql
-- ─────────────────────────────────────────────────────────────────────────────

BEGIN;

-- ── El BM-1 recibido (para que cada bien sepa de qué documento vino su código)
INSERT INTO inventario_consolidados_bm1 (fecha_recepcion, fecha_documento, referencia, observaciones)
SELECT DATE '2026-08-04', DATE '2025-04-26', 'BM-1 Coordinación de Bienes y Materiales (hoja 1)',
       '[carga 2026-10-11] Foto de una hoja del Formulario BM-1 entregada en el levantamiento del 2026-08-04 '
       || '(docs/formatos/BM-1_inventario_bienes_muebles_alcaldia.jpeg). Adjuntar el escaneado completo cuando llegue.'
WHERE NOT EXISTS (SELECT 1 FROM inventario_consolidados_bm1 WHERE referencia = 'BM-1 Coordinación de Bienes y Materiales (hoja 1)');

-- ── El Oficio N° 179/2026 (relación de bienes nuevos ya enviada a la Alcaldía)
INSERT INTO inventario_relaciones (numero, fecha, destinatario_nombre, destinatario_cargo, destinatario_ente, observacion)
SELECT '179/2026', DATE '2026-06-10', 'Lcdo. Antonio Guevara', 'Coordinador de Bienes y Materias',
       'Alcaldía del Municipio Sucre del estado Sucre',
       '[carga 2026-10-11] Oficio emitido fuera del sistema (docs/formatos/oficio_relacion_bienes_nuevos_alcaldia_2026-06-10.jpg). Recibido por la Alcaldía el 10/06/2026 17:20.'
WHERE NOT EXISTS (SELECT 1 FROM inventario_relaciones WHERE numero = '179/2026');

-- ── Bienes del BM-1: codificados por la Alcaldía, Activos
WITH bm1 AS (
    SELECT id FROM inventario_consolidados_bm1
     WHERE referencia = 'BM-1 Coordinación de Bienes y Materiales (hoja 1)' LIMIT 1
), datos (nro, depto, cat, nombre, descripcion, marca, modelo, serial) AS (VALUES
    ('084', 'Dirección de Planificación y Gestión Turística', 'Mobiliario de oficina',          'Silla de visitante',   'En semicuero color negro, sin posabrazo', NULL, NULL, NULL),
    ('131', 'Dirección de Planificación y Gestión Turística', 'Mobiliario de oficina',          'Mesa larga',           'En madera color marrón', NULL, NULL, NULL),
    ('141', 'Dirección de Planificación y Gestión Turística', 'Máquinas y equipos de oficina',  'Pizarra mediana',      'Acrílica', NULL, NULL, NULL),
    ('153', 'Dirección de Planificación y Gestión Turística', 'Climatización y refrigeración',  'Aire acondicionado',   'De 36 mil BTU, color beige', 'Hyundai', 'Piso techo', '540K517990137080160608'),
    ('155', 'Dirección de Planificación y Gestión Turística', 'Mobiliario de oficina',          'Archivo',              'En fórmica color gris de 02 puertas', NULL, NULL, NULL),
    ('156', 'Dirección de Planificación y Gestión Turística', 'Mobiliario de oficina',          'Estante',              'En fórmica color gris de 02 puertas', NULL, NULL, NULL),
    ('157', 'Dirección de Planificación y Gestión Turística', 'Mobiliario de oficina',          'Biblioteca',           'En fórmica color gris con puertas de vidrio', NULL, NULL, NULL),
    ('158', 'Dirección de Planificación y Gestión Turística', 'Mobiliario de oficina',          'Telefonera horizontal','De 05 compartimientos en fórmica color gris', NULL, NULL, NULL),
    ('159', 'Dirección de Planificación y Gestión Turística', 'Material turístico y promocional','Lona de bastidores',  'Imagen alusiva al indio', NULL, NULL, NULL),
    ('160', 'Dirección de Planificación y Gestión Turística', 'Mobiliario de oficina',          'Escritorio',           'En fórmica color gris con patas de hierro', NULL, NULL, NULL),
    ('171', 'Dirección de Planificación y Gestión Turística', 'Equipos de comunicación',        'Router',               'Color blanco de 04 antenas', 'Mercusis', NULL, NULL),
    ('110', 'Promoción Turística',                            'Mobiliario de oficina',          'Silla de visitante',   'En cuero color negro, sin posabrazo', NULL, NULL, NULL),
    ('112', 'Promoción Turística',                            'Mobiliario de oficina',          'Silla',                'En semicuero color marrón, sin posabrazo', NULL, NULL, NULL),
    ('139', 'Promoción Turística',                            'Mobiliario de oficina',          'Mesa larga',           'En madera color marrón', NULL, NULL, NULL),
    ('148', 'Promoción Turística',                            'Mobiliario de oficina',          'Silla',                'En semicuero color marrón, sin posabrazos', NULL, NULL, NULL),
    ('025', 'Calidad y Servicios Turísticos',                 'Mobiliario de oficina',          'Silla plástica',       'Color blanco con posabrazos', NULL, NULL, NULL),
    ('028', 'Calidad y Servicios Turísticos',                 'Mobiliario de oficina',          'Silla plástica',       'Color blanco con posabrazos', NULL, NULL, NULL),
    ('029', 'Calidad y Servicios Turísticos',                 'Mobiliario de oficina',          'Silla plástica',       'Color blanco con posabrazos', NULL, NULL, NULL)
)
INSERT INTO inventario
    (id_categoria, id_ubicacion, nombre, descripcion, marca, modelo, serial, condicion, estatus, observaciones,
     codigo_grupo, codigo_subgrupo, codigo_seccion, nro_orden, codigo_bn,
     verificado_alcaldia, fecha_verificacion, codificado_por, fecha_codificacion, id_consolidado_bm1, origen)
SELECT c.id, u.id, d.nombre, d.descripcion, COALESCE(d.marca, ''), COALESCE(d.modelo, ''), d.serial,
       'Bueno', 'Activo',
       '[carga 2026-10-11] Transcrito del BM-1 del 26/04/2025. [por confirmar] condición física, categoría interna y origen (el BM-1 no los trae).',
       '2', '01', '108', d.nro, '2-01-108-' || d.nro,
       TRUE, DATE '2025-04-26', 'Alcaldía', DATE '2025-04-26', (SELECT id FROM bm1), 'Compra'
  FROM datos d
  JOIN categorias  c ON c.nombre = d.cat   AND c.is_active
  JOIN ubicaciones u ON u.nombre = d.depto AND u.is_active
 WHERE NOT EXISTS (SELECT 1 FROM inventario i WHERE i.nro_orden = d.nro AND i.codigo_seccion = '108');

-- ── Bienes del Oficio N° 179/2026: comprados, sin código (esperan la codificación)
WITH rel AS (SELECT id FROM inventario_relaciones WHERE numero = '179/2026' LIMIT 1),
datos (orden, cat, nombre, descripcion, marca, modelo, serial, costo) AS (VALUES
    (1, 'Equipos de comunicación', 'G/EPON ONU y router Wi-Fi AX3000',
        'ONU color blanco y naranja, serial LP20241017800; router dual band gigabit modelo HG5013-4G, serial LP20241207703 (un solo renglón y monto en el oficio)',
        'V.SOL', 'V2801S-V', 'LP20241017800', 34790.20),
    (2, 'Equipos de computación', 'Computador de escritorio',
        'Mini PC Intel i3-8109U, monitor de 22", teclado y mouse inalámbricos, color negro',
        'Admiral', 'ADM249Q01', NULL, 177108.80),
    (3, 'Material turístico y promocional', 'Banderín', '1 de 2 del oficio (monto total 82.596,64 dividido entre 2)', NULL, NULL, NULL, 41298.32),
    (4, 'Material turístico y promocional', 'Banderín', '2 de 2 del oficio (monto total 82.596,64 dividido entre 2)', NULL, NULL, NULL, 41298.32)
)
INSERT INTO inventario
    (id_categoria, id_ubicacion, nombre, descripcion, marca, modelo, serial, condicion, estatus, observaciones,
     origen, costo_adquisicion, id_relacion)
SELECT c.id, u.id, d.nombre, d.descripcion, COALESCE(d.marca, ''), COALESCE(d.modelo, ''), d.serial,
       'Bueno', 'En espera de codificación',
       '[carga 2026-10-11] Oficio N° 179/2026 (renglón ' || d.orden || '). [por confirmar] condición, categoría y ubicación (el oficio no dice en qué departamento está).',
       'Compra', d.costo, (SELECT id FROM rel)
  FROM datos d
  JOIN categorias  c ON c.nombre = d.cat AND c.is_active
  JOIN ubicaciones u ON u.nombre = 'Depósito General' AND u.is_active
 WHERE NOT EXISTS (SELECT 1 FROM inventario i
                    WHERE i.observaciones LIKE '[carga 2026-10-11] Oficio N° 179/2026 (renglón ' || d.orden || ')%');

-- ── La cafetera donada (documento de donación del 18/02/2026)
INSERT INTO inventario
    (id_categoria, id_ubicacion, nombre, descripcion, marca, modelo, serial, condicion, estatus, observaciones,
     origen, donante, donante_cedula, donante_estado_civil, donante_domicilio, donacion_procedencia,
     costo_adquisicion, donacion_valor_usd, donacion_fecha, fecha_adquisicion)
SELECT c.id, u.id, 'Cafetera eléctrica', 'Color negro, capacidad de hasta 12 tazas o 1,5 litros', 'Salcar', 'SAL-CFM12T-BK/W', NULL,
       'Bueno', 'En espera de codificación',
       '[carga 2026-10-11] Documento de donación del 18/02/2026. [por confirmar] condición y ubicación.',
       'Donación', 'Juan Carlos Marval Rodríguez', 'V-16316848', 'divorciado', 'Cumaná, estado Sucre',
       'La obtuvo como premio de un bingo en el que participó, motivo por el cual no posee factura de compra.',
       19025.28, 48, DATE '2026-02-18', DATE '2026-02-18'
  FROM categorias c, ubicaciones u
 WHERE c.nombre = 'Electrodomésticos y enseres' AND c.is_active
   AND u.nombre = 'Depósito General' AND u.is_active
   AND NOT EXISTS (SELECT 1 FROM inventario i WHERE i.observaciones LIKE '[carga 2026-10-11] Documento de donación%');

COMMIT;

-- Verificación:
--   SELECT estatus, COUNT(*) FROM inventario WHERE observaciones LIKE '[carga 2026-10-11]%' GROUP BY estatus;
--     → Activo 18 · En espera de codificación 5
