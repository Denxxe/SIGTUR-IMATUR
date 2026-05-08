-- ============================================================
-- Migración 005 — Rutas completas, configuración institucional
-- ============================================================

-- 1. Extender tabla rutas con campos de planificación de visitas
ALTER TABLE rutas
  ADD COLUMN IF NOT EXISTS fecha_visita    DATE,
  ADD COLUMN IF NOT EXISTS hora_visita     TIME,
  ADD COLUMN IF NOT EXISTS id_departamento INTEGER REFERENCES departamentos(id) ON DELETE SET NULL,
  ADD COLUMN IF NOT EXISTS id_facilitador  INTEGER REFERENCES empleados(id) ON DELETE SET NULL,
  ADD COLUMN IF NOT EXISTS cupo_maximo     INTEGER DEFAULT 20;

-- 2. Participantes de ruta (espejo de participantes_taller)
CREATE TABLE IF NOT EXISTS participantes_ruta (
  id               SERIAL PRIMARY KEY,
  id_ruta          INTEGER NOT NULL REFERENCES rutas(id) ON DELETE CASCADE,
  id_persona       INTEGER          REFERENCES personas(id) ON DELETE RESTRICT,
  nombre_libre     VARCHAR(100),
  apellido_libre   VARCHAR(100),
  cedula_libre     VARCHAR(20),
  asistio          BOOLEAN  DEFAULT FALSE,
  observaciones    TEXT,
  is_active        BOOLEAN  DEFAULT TRUE,
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP,
  deleted_at       TIMESTAMP,
  created_by       INTEGER,
  updated_by       INTEGER,
  deleted_by       INTEGER,
  CONSTRAINT pr_participante_req CHECK (id_persona IS NOT NULL OR nombre_libre IS NOT NULL)
);

-- 3. Configuración institucional del sistema (clave → valor)
CREATE TABLE IF NOT EXISTS configuracion_sistema (
  id          SERIAL PRIMARY KEY,
  clave       VARCHAR(100) UNIQUE NOT NULL,
  valor       TEXT DEFAULT '',
  descripcion VARCHAR(255),
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_by  INTEGER
);

INSERT INTO configuracion_sistema (clave, valor, descripcion) VALUES
  ('director_nombre',    '',                        'Nombre del Director/Presidente de IMATUR'),
  ('director_apellido',  '',                        'Apellido del Director/Presidente'),
  ('director_cargo',     'Director',                'Cargo del firmante institucional'),
  ('resolucion_numero',  '',                        'N° de la Resolución de nombramiento'),
  ('resolucion_fecha',   '',                        'Fecha de la Resolución (texto, ej: 15 de enero de 2025)'),
  ('gaceta_numero',      '',                        'N° de la Gaceta Municipal Extraordinaria'),
  ('gaceta_fecha',       '',                        'Fecha de la Gaceta (texto, ej: 20 de enero de 2025)'),
  ('telf_institucion',   '(0293) 431-4073',         'Teléfono institucional'),
  ('correo_institucion', 'imatur.cumana@gmail.com', 'Correo electrónico institucional'),
  ('correlativo_oficio', '0',                       'Último correlativo de oficio emitido en el año en curso'),
  ('ano_correlativo',    '2026',                    'Año del correlativo activo (se reinicia automáticamente)')
ON CONFLICT (clave) DO NOTHING;

-- 4. Oficios emitidos (comunicaciones oficiales salientes)
CREATE TABLE IF NOT EXISTS oficios_emitidos (
  id                   SERIAL PRIMARY KEY,
  numero               VARCHAR(20)  NOT NULL,
  fecha                DATE         NOT NULL DEFAULT CURRENT_DATE,
  destinatario_nombre  VARCHAR(200),
  destinatario_cargo   VARCHAR(200),
  asunto               VARCHAR(500),
  id_ruta              INTEGER REFERENCES rutas(id) ON DELETE SET NULL,
  is_active            BOOLEAN DEFAULT TRUE,
  created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  created_by           INTEGER
);
