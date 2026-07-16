-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 059 — Base salarial + Bono Vacacional (R-11, 1ra entrega)
-- El cliente envió el formato oficial que la Alcaldía exige para el Bono
-- Vacacional (4 hojas por tipo de personal + resumen). El sistema hoy NO
-- guarda sueldo ni primas por empleado (mig. 035 lo eliminó de cargos con la
-- decisión "IMATUR no distingue sueldo por cargo"), así que se introduce como
-- historial por empleado (mismo patrón append-only que empleado_traslados).
-- v1 = "registro + reporte": Talento Humano captura/valida los montos (igual
-- que hoy en Excel) y el sistema los organiza y exporta en el formato exacto;
-- no se automatiza el cálculo legal completo (queda para una 2da entrega,
-- junto con Liquidación de Prestaciones Sociales, cuando el cliente confirme
-- un mes ya calculado con números reales).
-- Los días base por tipo de personal (75/75/85/45) son un beneficio de
-- contrato colectivo (superior al mínimo LOTTT Art.192: 15+1/año) — van
-- configurables en configuracion_sistema, no fijos en código.
-- Idempotente (CREATE TABLE IF NOT EXISTS / ON CONFLICT DO NOTHING).
-- ─────────────────────────────────────────────────────────────────────────────

-- 1) Historial salarial por empleado (append-only, igual que empleado_traslados).
--    El valor "actual" es siempre la fila con fecha_efectiva más reciente.
CREATE TABLE IF NOT EXISTS empleado_salarios (
    id                      SERIAL PRIMARY KEY,
    id_empleado             INTEGER NOT NULL REFERENCES empleados(id) ON DELETE CASCADE,
    fecha_efectiva          DATE NOT NULL DEFAULT CURRENT_DATE,
    sueldo_basico           NUMERIC(12,2) NOT NULL DEFAULT 0,
    prima_profesional       NUMERIC(12,2) NOT NULL DEFAULT 0,
    prima_responsabilidad   NUMERIC(12,2) NOT NULL DEFAULT 0,
    prima_antiguedad        NUMERIC(12,2) NOT NULL DEFAULT 0,
    prima_por_hijo          NUMERIC(12,2) NOT NULL DEFAULT 0,
    bono_transporte         NUMERIC(12,2) NOT NULL DEFAULT 0,
    prima_fond              NUMERIC(12,2) NOT NULL DEFAULT 0,
    prima_discapacidad      NUMERIC(12,2) NOT NULL DEFAULT 0,
    caja_ahorro             NUMERIC(12,2) NOT NULL DEFAULT 0,
    motivo                  VARCHAR(255),
    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by              INTEGER
);
CREATE INDEX IF NOT EXISTS idx_empleado_salarios_empleado ON empleado_salarios (id_empleado);

-- 2) Cabecera de una corrida mensual de Bono Vacacional.
CREATE TABLE IF NOT EXISTS bono_vacacional_periodos (
    id           SERIAL PRIMARY KEY,
    periodo      VARCHAR(20) NOT NULL UNIQUE,
    fecha_corte  DATE NOT NULL,
    estado       VARCHAR(20) NOT NULL DEFAULT 'Borrador'
                 CHECK (estado IN ('Borrador', 'Cerrado')),
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by   INTEGER,
    cerrado_at   TIMESTAMP,
    cerrado_by   INTEGER
);

-- 3) Detalle por empleado en ese período — snapshot (se congela al generar).
CREATE TABLE IF NOT EXISTS bono_vacacional_detalle (
    id                    SERIAL PRIMARY KEY,
    id_periodo            INTEGER NOT NULL REFERENCES bono_vacacional_periodos(id) ON DELETE CASCADE,
    id_empleado            INTEGER NOT NULL REFERENCES empleados(id) ON DELETE RESTRICT,
    tipo_personal          VARCHAR(20) NOT NULL
                           CHECK (tipo_personal IN ('Alto Nivel', 'Empleados Fijos', 'Obreros Fijos', 'Contratados')),
    dias_vacaciones        INTEGER NOT NULL DEFAULT 0,
    grado_escala           VARCHAR(30),
    sueldo_basico          NUMERIC(12,2) NOT NULL DEFAULT 0,
    prima_profesional      NUMERIC(12,2) NOT NULL DEFAULT 0,
    prima_antiguedad       NUMERIC(12,2) NOT NULL DEFAULT 0,
    n_hijos                INTEGER NOT NULL DEFAULT 0,
    monto_hijo             NUMERIC(12,2) NOT NULL DEFAULT 0,
    prima_por_hijo         NUMERIC(12,2) NOT NULL DEFAULT 0,
    bono_transporte        NUMERIC(12,2) NOT NULL DEFAULT 0,
    prima_discapacidad     NUMERIC(12,2) NOT NULL DEFAULT 0,
    caja_ahorro            NUMERIC(12,2) NOT NULL DEFAULT 0,
    sueldo_integral        NUMERIC(12,2) NOT NULL DEFAULT 0,
    cuenta_bancaria        VARCHAR(30),
    monto_cesta_ticket     NUMERIC(12,2) NOT NULL DEFAULT 0,
    alicuotas              NUMERIC(12,2) NOT NULL DEFAULT 0,
    total_bono_vacacional  NUMERIC(12,2),
    created_at             TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_bono_vac_detalle_periodo ON bono_vacacional_detalle (id_periodo);
CREATE INDEX IF NOT EXISTS idx_bono_vac_detalle_empleado ON bono_vacacional_detalle (id_empleado);

-- 4) Parámetros configurables (Configuración del Sistema) — días base por tipo
--    de personal (contrato colectivo, NO la LOTTT general) + monto cesta ticket.
INSERT INTO configuracion_sistema (clave, valor, descripcion) VALUES
  ('bono_vac_dias_alto_nivel',      '75', 'Bono Vacacional: días base (+ años de servicio) para Alto Nivel y Dirección'),
  ('bono_vac_dias_empleados_fijos', '75', 'Bono Vacacional: días base (+ años de servicio) para Empleados Fijos'),
  ('bono_vac_dias_obreros_fijos',   '85', 'Bono Vacacional: días fijos (no suma años) para Obreros Fijos'),
  ('bono_vac_dias_contratados',     '45', 'Bono Vacacional: días base (+ años de servicio) para Contratados'),
  ('monto_cesta_ticket',            '0',  'Monto mensual de cesta ticket usado en el cálculo del Bono Vacacional')
ON CONFLICT (clave) DO NOTHING;

-- 5) RBAC: Nómina la gestiona RRHH (rol 2); Admin (rol 1) usa '*'.
INSERT INTO permisos_rol (id_rol, modulo)
SELECT 2, 'NominaController'
WHERE NOT EXISTS (SELECT 1 FROM permisos_rol WHERE id_rol = 2 AND modulo = 'NominaController');
