-- =====================================================================
-- 072 — Nómina: motor de cálculo (fases N-A y N-B del plan)
-- =====================================================================
--
-- Ver `docs/PLAN_MODULO_NOMINA.md` — leerlo antes de tocar esto.
--
-- QUÉ CAMBIA DE FONDO
-- El Bono Vacacional v1 (mig. 059) se construyó como "registro + reporte":
-- Talento Humano teclea cada monto porque no teníamos las fórmulas. La
-- plantilla real (`INSTITUTO IMATUR JULIO 2026.xlsx`) las trajo, y muestran
-- que las primas **se derivan** de 4 entradas: sueldo base, grado de
-- instrucción, años en la administración pública y nº de hijos.
--
-- Esta migración monta lo que el cálculo necesita y NO existía.
--
-- 1. TABLAS DE PORCENTAJES COMO DATOS, NO COMO CÓDIGO
--    `nomina_grados` (6 filas) y `nomina_antiguedad` (23 filas). Van en
--    tablas y no en constantes PHP a propósito: son parámetros de
--    contratación colectiva que el cliente puede renegociar, y el patrón
--    H-07 (enums en constantes) aplica a valores de dominio del software,
--    no a cifras que cambian por decreto.
--
--    ⚠️ El tramo de antigüedad ≥23 años se representa con `anios = 23` y se
--    interpreta como TOPE (30 %). La plantilla del cliente tiene aquí su
--    defecto #1: usa el sueldo mensual en vez del quincenal y **paga el
--    doble** (112,80 donde corresponde 56,40). El motor lo calcula bien.
--
-- 2. PARÁMETROS MENSUALES CON VIGENCIA (`nomina_parametros_mes`)
--    Cesta ticket y tasa del dólar **cambian todos los meses** (la primera
--    la publica la UNAPRE; la segunda es el tipo de cambio, y el bono de
--    responsabilidad se pacta en divisas y se paga al cambio). Hoy viven
--    en `configuracion_sistema` como escalares sin histórico, así que
--    recalcular un mes pasado daría un número distinto. Con esta tabla el
--    período cerrado se puede reconstruir.
--
-- 3. ENTRADAS QUE FALTABAN EN LA FICHA (`empleados`)
--    · `cuenta_nomina` / `banco_nomina` — la nómina se paga por
--      transferencia; el número de cuenta no existía en ninguna parte.
--    · `divisas_bono_responsabilidad` — cantidad en divisas del bono de
--      responsabilidad (Alto Nivel y Comisión de Servicio).
--    · `sueldo_dependencia_origen` — lo que ya le paga su dependencia al
--      personal en comisión de servicio; su nómina es la DIFERENCIA.
--
-- 4. QUINTO TIPO DE PERSONAL
--    Son 5 hojas, no 4: falta **Comisión de Servicio**, derivable sin
--    captura nueva (`institucion_origen <> 'IMATUR'`, mig. 025). Se amplía
--    el CHECK de `bono_vacacional_detalle.tipo_personal`.
--
-- 5. NÓMINA QUINCENAL (`nomina_periodos` / `nomina_detalle`)
--    El pago corriente es **quincenal** (`sueldo_base_mensual / 2`). Cada
--    período congela sus parámetros (tasa, cesta ticket, semanas) y un
--    snapshot por empleado, igual que `bono_vacacional_detalle`.
--
--    `semanas` (4 o 5) se guarda POR PERÍODO porque es la pregunta N-2 sin
--    responder: la plantilla usa ×4 en Alto Nivel y Contratados y ×5 en
--    Empleados y Obreros **en el mismo mes**. Hasta que el cliente aclare
--    el criterio, es un parámetro visible y no una constante escondida.
--
-- Idempotente.
-- =====================================================================

BEGIN;

-- ── 1. Prima de profesionalización: % por grado de instrucción ────────
CREATE TABLE IF NOT EXISTS nomina_grados (
    id           SERIAL PRIMARY KEY,
    codigo       VARCHAR(10)  NOT NULL UNIQUE,
    nombre       VARCHAR(80)  NOT NULL,
    porcentaje   NUMERIC(6,3) NOT NULL DEFAULT 0,
    orden        SMALLINT     NOT NULL DEFAULT 0,
    is_active    BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP,
    updated_by   INTEGER
);
COMMENT ON TABLE nomina_grados IS
  'Prima de profesionalización: % sobre el sueldo base quincenal según grado de instrucción. Extraído de las fórmulas de la plantilla real del cliente.';

INSERT INTO nomina_grados (codigo, nombre, porcentaje, orden)
SELECT v.codigo, v.nombre, v.pct, v.orden
  FROM (VALUES
        ('BACH',  'Bachiller',                        0.0, 1),
        ('TSU',   'Técnico Superior Universitario',  20.0, 2),
        ('PROF',  'Profesional / Licenciado',        25.0, 3),
        ('ESP',   'Especialista',                    30.0, 4),
        ('MAEST', 'Magíster',                        35.0, 5),
        ('DR',    'Doctor',                          40.0, 6)
       ) AS v(codigo, nombre, pct, orden)
 WHERE NOT EXISTS (SELECT 1 FROM nomina_grados g WHERE g.codigo = v.codigo);

-- ── 2. Prima de antigüedad: % por años en la administración pública ───
-- Incrementos por tramo: 1,0 (años 1-5) · 1,2 (6-10) · 1,4 (11-15) ·
-- 1,6 (16-20) · 1,8 (21-22), y se congela en 30 % desde el año 23.
CREATE TABLE IF NOT EXISTS nomina_antiguedad (
    anios        SMALLINT     PRIMARY KEY,
    porcentaje   NUMERIC(6,3) NOT NULL DEFAULT 0,
    es_tope      BOOLEAN      NOT NULL DEFAULT FALSE,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP,
    updated_by   INTEGER
);
COMMENT ON TABLE nomina_antiguedad IS
  'Prima de antigüedad: % sobre el sueldo base quincenal según años en la administración pública (empleados.fecha_ingreso_administracion, NO los años en IMATUR). La fila con es_tope=TRUE aplica a ese año y a todos los superiores.';

INSERT INTO nomina_antiguedad (anios, porcentaje, es_tope)
SELECT v.anios, v.pct, v.tope
  FROM (VALUES
        (1,1.0,FALSE),  (2,2.0,FALSE),  (3,3.0,FALSE),  (4,4.0,FALSE),
        (5,5.0,FALSE),  (6,6.2,FALSE),  (7,7.4,FALSE),  (8,8.6,FALSE),
        (9,9.8,FALSE),  (10,11.0,FALSE),(11,12.4,FALSE),(12,13.8,FALSE),
        (13,15.2,FALSE),(14,16.6,FALSE),(15,18.0,FALSE),(16,19.6,FALSE),
        (17,21.2,FALSE),(18,22.8,FALSE),(19,24.4,FALSE),(20,26.0,FALSE),
        (21,27.8,FALSE),(22,29.6,FALSE),(23,30.0,TRUE)
       ) AS v(anios, pct, tope)
 WHERE NOT EXISTS (SELECT 1 FROM nomina_antiguedad a WHERE a.anios = v.anios);

-- ── 3. Parámetros que cambian cada mes ────────────────────────────────
CREATE TABLE IF NOT EXISTS nomina_parametros_mes (
    id                  SERIAL PRIMARY KEY,
    periodo             CHAR(7)       NOT NULL UNIQUE,   -- AAAA-MM
    monto_cesta_ticket  NUMERIC(14,2) NOT NULL DEFAULT 0,
    tasa_dolar          NUMERIC(14,4) NOT NULL DEFAULT 0,
    observaciones       VARCHAR(255),
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by          INTEGER,
    updated_at          TIMESTAMP,
    updated_by          INTEGER,
    CONSTRAINT nomina_parametros_mes_periodo_chk CHECK (periodo ~ '^[0-9]{4}-[0-9]{2}$')
);
COMMENT ON TABLE nomina_parametros_mes IS
  'Cesta ticket (la publica la UNAPRE) y tasa del dólar por mes. Con histórico, para que un período cerrado se pueda reconstruir con los valores que tenía.';

-- ── 4. Entradas que faltaban en la ficha del empleado ─────────────────
ALTER TABLE empleados ADD COLUMN IF NOT EXISTS cuenta_nomina                VARCHAR(30);
ALTER TABLE empleados ADD COLUMN IF NOT EXISTS banco_nomina                 VARCHAR(80);
ALTER TABLE empleados ADD COLUMN IF NOT EXISTS divisas_bono_responsabilidad NUMERIC(12,2) NOT NULL DEFAULT 0;
ALTER TABLE empleados ADD COLUMN IF NOT EXISTS sueldo_dependencia_origen    NUMERIC(12,2) NOT NULL DEFAULT 0;

COMMENT ON COLUMN empleados.cuenta_nomina IS 'Cuenta bancaria donde se abona la nómina.';
COMMENT ON COLUMN empleados.divisas_bono_responsabilidad IS 'Cantidad EN DIVISAS del bono de responsabilidad; se paga en bolívares al cambio del mes (Alto Nivel y Comisión de Servicio).';
COMMENT ON COLUMN empleados.sueldo_dependencia_origen IS 'Sueldo que ya le paga su dependencia de origen al personal en comisión de servicio; su nómina en IMATUR es la diferencia.';

-- ── 5. Grado de instrucción del empleado ──────────────────────────────
-- `personas.nivel_academico` es varchar libre ('Ingeniero', 'Universitario'…)
-- y el cálculo necesita uno de los 6 códigos. Se deriva por mapeo
-- (`Nomina::codigoGrado()`), pero esta columna permite CORREGIR a mano el
-- caso que el mapeo no acierte. Si está NULL se usa el mapeo; si el mapeo
-- tampoco reconoce el valor, el empleado se REPORTA como pendiente en vez
-- de pagarle 0 % en silencio (defecto #7 de la plantilla del cliente).
ALTER TABLE personas ADD COLUMN IF NOT EXISTS codigo_grado VARCHAR(10);
COMMENT ON COLUMN personas.codigo_grado IS 'Código de grado de instrucción para la prima de profesionalización (nomina_grados.codigo). NULL = derivar de nivel_academico por mapeo.';

-- ── 6. Quinto tipo de personal: Comisión de Servicio ──────────────────
ALTER TABLE bono_vacacional_detalle DROP CONSTRAINT IF EXISTS bono_vacacional_detalle_tipo_personal_check;
ALTER TABLE bono_vacacional_detalle ADD CONSTRAINT bono_vacacional_detalle_tipo_personal_check
    CHECK (tipo_personal IN ('Alto Nivel', 'Empleados Fijos', 'Obreros Fijos', 'Contratados', 'Comisión de Servicio'));

-- ── 7. Nómina quincenal: períodos y snapshot ──────────────────────────
CREATE TABLE IF NOT EXISTS nomina_periodos (
    id                  SERIAL PRIMARY KEY,
    periodo             CHAR(7)   NOT NULL,              -- AAAA-MM
    quincena            SMALLINT  NOT NULL,              -- 1 = 1-15, 2 = 16-fin
    fecha_corte         DATE      NOT NULL,
    -- Parámetros congelados al generar (reconstrucción exacta del período)
    monto_cesta_ticket  NUMERIC(14,2) NOT NULL DEFAULT 0,
    tasa_dolar          NUMERIC(14,4) NOT NULL DEFAULT 0,
    semanas             SMALLINT      NOT NULL DEFAULT 4,
    estado              VARCHAR(15)   NOT NULL DEFAULT 'Borrador',
    observaciones        VARCHAR(255),
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by          INTEGER,
    cerrado_at          TIMESTAMP,
    cerrado_by          INTEGER,
    CONSTRAINT nomina_periodos_periodo_chk  CHECK (periodo ~ '^[0-9]{4}-[0-9]{2}$'),
    CONSTRAINT nomina_periodos_quincena_chk CHECK (quincena IN (1, 2)),
    CONSTRAINT nomina_periodos_estado_chk   CHECK (estado IN ('Borrador', 'Cerrado')),
    CONSTRAINT nomina_periodos_semanas_chk  CHECK (semanas BETWEEN 1 AND 6),
    CONSTRAINT nomina_periodos_unico        UNIQUE (periodo, quincena)
);
COMMENT ON TABLE nomina_periodos IS
  'Corrida de nómina quincenal. Congela los parámetros del mes para que un período cerrado se pueda reconstruir tal como se pagó.';

CREATE TABLE IF NOT EXISTS nomina_detalle (
    id                     SERIAL PRIMARY KEY,
    id_periodo             INTEGER NOT NULL REFERENCES nomina_periodos(id) ON DELETE CASCADE,
    id_empleado            INTEGER NOT NULL REFERENCES empleados(id),
    tipo_personal          VARCHAR(25) NOT NULL,
    -- Entradas del cálculo (snapshot: qué se usó, no solo qué salió)
    sueldo_base_mensual    NUMERIC(14,2) NOT NULL DEFAULT 0,
    sueldo_base_quincenal  NUMERIC(14,2) NOT NULL DEFAULT 0,
    codigo_grado           VARCHAR(10),
    pct_profesionalizacion NUMERIC(6,3)  NOT NULL DEFAULT 0,
    anios_administracion   SMALLINT      NOT NULL DEFAULT 0,
    pct_antiguedad         NUMERIC(6,3)  NOT NULL DEFAULT 0,
    n_hijos                SMALLINT      NOT NULL DEFAULT 0,
    -- Asignaciones
    prima_profesionalizacion NUMERIC(14,2) NOT NULL DEFAULT 0,
    prima_antiguedad         NUMERIC(14,2) NOT NULL DEFAULT 0,
    bono_transporte          NUMERIC(14,2) NOT NULL DEFAULT 0,
    prima_por_hijos          NUMERIC(14,2) NOT NULL DEFAULT 0,
    total_asignaciones       NUMERIC(14,2) NOT NULL DEFAULT 0,
    total_sueldo_normal      NUMERIC(14,2) NOT NULL DEFAULT 0,
    -- Deducciones del trabajador
    sso_trabajador         NUMERIC(14,2) NOT NULL DEFAULT 0,
    faov_trabajador        NUMERIC(14,2) NOT NULL DEFAULT 0,
    lrppf_trabajador       NUMERIC(14,2) NOT NULL DEFAULT 0,
    total_deducciones      NUMERIC(14,2) NOT NULL DEFAULT 0,
    neto_a_cobrar          NUMERIC(14,2) NOT NULL DEFAULT 0,
    -- Aportes patronales
    sso_patronal           NUMERIC(14,2) NOT NULL DEFAULT 0,
    faov_patronal          NUMERIC(14,2) NOT NULL DEFAULT 0,
    rpe_patronal           NUMERIC(14,2) NOT NULL DEFAULT 0,
    total_aportes          NUMERIC(14,2) NOT NULL DEFAULT 0,
    -- Conceptos derivados
    sueldo_normal_diario   NUMERIC(14,2) NOT NULL DEFAULT 0,
    alicuota_bono_vac      NUMERIC(14,2) NOT NULL DEFAULT 0,
    alicuota_bono_fin_anio NUMERIC(14,2) NOT NULL DEFAULT 0,
    sueldo_integral_diario NUMERIC(14,2) NOT NULL DEFAULT 0,
    dias_habiles_bono_vac  SMALLINT      NOT NULL DEFAULT 0,
    becas                  NUMERIC(14,2) NOT NULL DEFAULT 0,
    bono_50                NUMERIC(14,2) NOT NULL DEFAULT 0,
    bono_responsabilidad   NUMERIC(14,2) NOT NULL DEFAULT 0,
    -- Solo comisión de servicio
    sueldo_dependencia_origen NUMERIC(14,2) NOT NULL DEFAULT 0,
    diferencia_comision       NUMERIC(14,2) NOT NULL DEFAULT 0,
    -- Datos de pago congelados
    cuenta_nomina          VARCHAR(30),
    banco_nomina           VARCHAR(80),
    -- Trazabilidad de lo que el cálculo no pudo resolver
    advertencias           TEXT,
    created_at             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT nomina_detalle_tipo_chk CHECK (tipo_personal IN
        ('Alto Nivel', 'Empleados Fijos', 'Obreros Fijos', 'Contratados', 'Comisión de Servicio')),
    CONSTRAINT nomina_detalle_unico UNIQUE (id_periodo, id_empleado)
);
COMMENT ON TABLE nomina_detalle IS
  'Snapshot por empleado de una quincena: guarda las ENTRADAS del cálculo (sueldo base, % aplicados, años, hijos) además de los resultados, para poder auditar por qué salió cada número.';
COMMENT ON COLUMN nomina_detalle.advertencias IS
  'Lo que el cálculo no pudo resolver para este empleado (sin sueldo registrado, grado de instrucción no reconocido, sin cuenta bancaria…). Se muestra en la UI: el sistema avisa en vez de pagar 0 en silencio.';

CREATE INDEX IF NOT EXISTS idx_nomina_detalle_periodo  ON nomina_detalle (id_periodo);
CREATE INDEX IF NOT EXISTS idx_nomina_detalle_empleado ON nomina_detalle (id_empleado);

-- ── 8. Parámetros escalares del cálculo ───────────────────────────────
-- Ninguno se hardcodea: todos son negociables por contratación colectiva.
INSERT INTO configuracion_sistema (clave, valor, descripcion)
SELECT v.clave, v.valor, v.descripcion
  FROM (VALUES
        ('nomina_bono_transporte_mensual', '12.50',
         'Bono de transporte MENSUAL; en la quincena se paga la mitad. Igual para todos.'),
        ('nomina_monto_por_hijo', '6.50',
         'Prima por hijo, monto quincenal por cada hijo.'),
        ('nomina_becas_por_hijo', '12.50',
         'Monto de becas por hijo.'),
        ('nomina_semanas_default', '4',
         'Semanas usadas en SSO/LRPPF y aportes patronales. La plantilla del cliente usa 4 en unas hojas y 5 en otras el mismo mes (pregunta N-2 sin responder): se fija por período.'),
        ('nomina_pct_sso_trabajador', '2',
         'SSO retenido al trabajador (%). Base: total x 12/52 x semanas.'),
        ('nomina_pct_faov_trabajador', '1',
         'FAOV retenido al trabajador (%). Base: total del sueldo normal quincenal.'),
        ('nomina_pct_lrppf_trabajador', '0.5',
         'LRPPF (paro forzoso) retenido al trabajador (%). Base: total x 12/52 x semanas.'),
        ('nomina_pct_sso_patronal', '4',
         'Aporte patronal SSO (%). Base: total x 12/52 x semanas.'),
        ('nomina_pct_faov_patronal', '2',
         'Aporte patronal FAOV (%). Base: total del sueldo normal quincenal. OJO: la hoja de Comisión de la plantilla del cliente tiene 20% por error (defecto #2).'),
        ('nomina_pct_rpe_patronal', '1.7',
         'Aporte patronal RPE (%). Base: total x 12/52 x semanas.'),
        ('nomina_dias_bono_fin_anio', '150',
         'Días de bono de fin de año para la alícuota (sobre 360).'),
        ('nomina_dias_base_anio', '360',
         'Días base del año para las alícuotas.'),
        ('nomina_dias_bono_vac_base', '75',
         'Días base del bono vacacional para la alícuota. La plantilla de nómina usa 75 en TODAS las hojas, incluidas obreros y contratados, mientras bono_vac_dias_* tiene 85 y 45: es la pregunta N-1, sin responder.'),
        ('bono_vac_dias_comision', '75',
         'Días base del bono vacacional del personal en Comisión de Servicio (quinto tipo, mig. 072). Completa las 4 claves bono_vac_dias_* de la mig. 059.')
       ) AS v(clave, valor, descripcion)
 WHERE NOT EXISTS (SELECT 1 FROM configuracion_sistema c WHERE c.clave = v.clave);

COMMIT;

-- =====================================================================
-- Verificación:
--   SELECT codigo, porcentaje FROM nomina_grados ORDER BY orden;
--     -> 6 filas: BACH 0 … DR 40
--   SELECT COUNT(*), MAX(porcentaje) FROM nomina_antiguedad;
--     -> 23 filas, tope 30
--   SELECT clave FROM configuracion_sistema WHERE clave LIKE 'nomina_%';
--     -> 13 claves
--
-- Pendiente de INSUMOS del cliente (no de programación):
--   · Cesta ticket y tasa del dólar de cada mes -> nomina_parametros_mes.
--   · Sueldo base, grado, años de administración pública, nº de hijos y
--     cuenta bancaria de cada empleado activo.
--   · Respuestas N-1 (días base 75 vs 85/45) y N-2 (semanas 4 o 5): hoy
--     entran como parámetros, así que no bloquean el cálculo, pero el
--     número final no es definitivo hasta confirmarlas.
-- =====================================================================
