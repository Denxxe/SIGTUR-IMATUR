--
-- =====================================================================
-- SIGTUR-IMATUR — ESQUEMA CONSOLIDADO (INSTALACIÓN DESDE CERO)
-- =====================================================================
--
-- Generado: 2026-08-04  ·  PostgreSQL 17
-- Cubre: esquema base + TODAS las migraciones 001–067.
--
-- ESTE ARCHIVO ES AUTOSUFICIENTE. Después de importarlo NO hay que
-- aplicar ninguna migración de database/migrations/ — ya están todas
-- incluidas. Las migraciones sueltas se conservan solo como historial
-- y para actualizar instalaciones antiguas.
--
-- ---------------------------------------------------------------------
-- QUÉ INCLUYE
-- ---------------------------------------------------------------------
--   · Las 56 tablas, índices, constraints, secuencias y CHECKs.
--   · Catálogos institucionales con datos (listos para operar):
--       - roles (5) y permisos_rol  ....... RBAC dinámico
--       - configuracion_sistema ........... datos del instituto, RIF,
--                                           tolerancias, metas, nómina
--       - departamentos (24) .............. organigrama + sede aeropuerto
--       - cargos (5) ...................... niveles jerárquicos
--       - horarios (5) .................... modalidades de jornada
--       - feriados (12) ................... nacionales + Cumaná
--       - categorias (11) ................. clasificación interna de bienes
--       - municipio (2) / parroquia (7) ... geografía de Sucre
--   · Un usuario administrador de arranque (ver el final del archivo).
--
-- ---------------------------------------------------------------------
-- QUÉ **NO** INCLUYE (se carga operando el sistema)
-- ---------------------------------------------------------------------
--   Personal, usuarios reales, inventario, bienes, ubicaciones físicas,
--   categorías, talleres, rutas, visitantes, pasantes, asistencias,
--   constancias, nómina y bitácora. Todas esas tablas quedan vacías.
--
--   Los correlativos de oficios (constancias, rutas, formación,
--   pasantes) quedan reiniciados en 0.
--
-- ---------------------------------------------------------------------
-- CÓMO INSTALAR
-- ---------------------------------------------------------------------
--   createdb -U postgres "SIGTUR-IMATUR"
--   psql -U postgres -d "SIGTUR-IMATUR" -f database/schema_consolidado.sql
--
--   En Windows, psql suele estar en:
--   "C:\Program Files\PostgreSQL\17\bin\psql.exe"
--
-- =====================================================================
--
--
-- PostgreSQL database dump
--

-- Dumped from database version 17.4
-- Dumped by pg_dump version 17.4

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: public; Type: SCHEMA; Schema: -; Owner: -
--

-- *not* creating schema, since initdb creates it


--
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON SCHEMA public IS '';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: actividad_inventario; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.actividad_inventario (
    id integer NOT NULL,
    id_inventario integer NOT NULL,
    tipo_movimiento character varying(30) NOT NULL,
    descripcion text,
    fecha date DEFAULT CURRENT_DATE,
    id_empleado_responsable integer,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer,
    id_ubicacion_origen integer,
    id_ubicacion_destino integer,
    autorizado_por integer,
    fecha_retorno date,
    CONSTRAINT actividad_inventario_tipo_movimiento_check CHECK (((tipo_movimiento)::text = ANY ((ARRAY['Traslado'::character varying, 'Asignación de responsable'::character varying, 'Salida a mantenimiento'::character varying, 'Retorno de mantenimiento'::character varying, 'Baja'::character varying])::text[])))
);


--
-- Name: COLUMN actividad_inventario.id_empleado_responsable; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.actividad_inventario.id_empleado_responsable IS 'Responsable en el momento del movimiento (histórico). Desde la mig. 066 el responsable actual del bien se DERIVA de su departamento, no se almacena.';


--
-- Name: COLUMN actividad_inventario.autorizado_por; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.actividad_inventario.autorizado_por IS 'Empleado que autoriza el movimiento — la Coordinadora de Bienes (B-32).';


--
-- Name: COLUMN actividad_inventario.fecha_retorno; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.actividad_inventario.fecha_retorno IS 'Solo para salidas a mantenimiento: cuándo volvió el bien (B-33).';


--
-- Name: actividad_inventario_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.actividad_inventario_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: actividad_inventario_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.actividad_inventario_id_seq OWNED BY public.actividad_inventario.id;


--
-- Name: actividades_ruta; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.actividades_ruta (
    id integer NOT NULL,
    id_ruta integer NOT NULL,
    nombre character varying(200) NOT NULL,
    descripcion text,
    fecha date,
    id_empleado_responsable integer,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: actividades_ruta_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.actividades_ruta_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: actividades_ruta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.actividades_ruta_id_seq OWNED BY public.actividades_ruta.id;


--
-- Name: alertas_vistas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.alertas_vistas (
    id integer NOT NULL,
    id_usuario integer NOT NULL,
    clave_alerta character varying(60) NOT NULL,
    fingerprint character varying(64) NOT NULL,
    visto_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: alertas_vistas_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.alertas_vistas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: alertas_vistas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.alertas_vistas_id_seq OWNED BY public.alertas_vistas.id;


--
-- Name: amonestaciones; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.amonestaciones (
    id integer NOT NULL,
    id_empleado integer NOT NULL,
    fecha date DEFAULT CURRENT_DATE NOT NULL,
    motivo text NOT NULL,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer,
    motivo_anulacion text,
    id_falta_origen integer
);


--
-- Name: amonestaciones_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.amonestaciones_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: amonestaciones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.amonestaciones_id_seq OWNED BY public.amonestaciones.id;


--
-- Name: asistencias; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.asistencias (
    id integer NOT NULL,
    id_empleado integer NOT NULL,
    fecha date DEFAULT CURRENT_DATE,
    hora_entrada time without time zone NOT NULL,
    hora_salida time without time zone,
    observacion text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer,
    minutos_tarde integer
);


--
-- Name: asistencias_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.asistencias_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: asistencias_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.asistencias_id_seq OWNED BY public.asistencias.id;


--
-- Name: audit_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.audit_logs (
    id integer NOT NULL,
    tabla_afectada character varying(100) NOT NULL,
    operacion character varying(20) NOT NULL,
    record_id integer,
    datos_previos jsonb,
    datos_nuevos jsonb,
    id_usuario integer,
    fecha timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    ip_direccion character varying(45),
    CONSTRAINT audit_logs_operacion_check CHECK (((operacion)::text = ANY ((ARRAY['INSERT'::character varying, 'UPDATE'::character varying, 'DELETE'::character varying, 'LOGIN'::character varying, 'LOGIN_FALLIDO'::character varying])::text[])))
);


--
-- Name: audit_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.audit_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: audit_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.audit_logs_id_seq OWNED BY public.audit_logs.id;


--
-- Name: bono_vacacional_detalle; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.bono_vacacional_detalle (
    id integer NOT NULL,
    id_periodo integer NOT NULL,
    id_empleado integer NOT NULL,
    tipo_personal character varying(20) NOT NULL,
    dias_vacaciones integer DEFAULT 0 NOT NULL,
    grado_escala character varying(30),
    sueldo_basico numeric(12,2) DEFAULT 0 NOT NULL,
    prima_profesional numeric(12,2) DEFAULT 0 NOT NULL,
    prima_antiguedad numeric(12,2) DEFAULT 0 NOT NULL,
    n_hijos integer DEFAULT 0 NOT NULL,
    monto_hijo numeric(12,2) DEFAULT 0 NOT NULL,
    prima_por_hijo numeric(12,2) DEFAULT 0 NOT NULL,
    bono_transporte numeric(12,2) DEFAULT 0 NOT NULL,
    prima_discapacidad numeric(12,2) DEFAULT 0 NOT NULL,
    caja_ahorro numeric(12,2) DEFAULT 0 NOT NULL,
    sueldo_integral numeric(12,2) DEFAULT 0 NOT NULL,
    cuenta_bancaria character varying(30),
    monto_cesta_ticket numeric(12,2) DEFAULT 0 NOT NULL,
    alicuotas numeric(12,2) DEFAULT 0 NOT NULL,
    total_bono_vacacional numeric(12,2),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT bono_vacacional_detalle_tipo_personal_check CHECK (((tipo_personal)::text = ANY ((ARRAY['Alto Nivel'::character varying, 'Empleados Fijos'::character varying, 'Obreros Fijos'::character varying, 'Contratados'::character varying])::text[])))
);


--
-- Name: bono_vacacional_detalle_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.bono_vacacional_detalle_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: bono_vacacional_detalle_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.bono_vacacional_detalle_id_seq OWNED BY public.bono_vacacional_detalle.id;


--
-- Name: bono_vacacional_periodos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.bono_vacacional_periodos (
    id integer NOT NULL,
    periodo character varying(20) NOT NULL,
    fecha_corte date NOT NULL,
    estado character varying(20) DEFAULT 'Borrador'::character varying NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by integer,
    cerrado_at timestamp without time zone,
    cerrado_by integer,
    CONSTRAINT bono_vacacional_periodos_estado_check CHECK (((estado)::text = ANY ((ARRAY['Borrador'::character varying, 'Cerrado'::character varying])::text[])))
);


--
-- Name: bono_vacacional_periodos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.bono_vacacional_periodos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: bono_vacacional_periodos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.bono_vacacional_periodos_id_seq OWNED BY public.bono_vacacional_periodos.id;


--
-- Name: carga_familiar; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.carga_familiar (
    id integer NOT NULL,
    id_persona integer NOT NULL,
    nombre_apellido character varying(150) NOT NULL,
    cedula character varying(15),
    fecha_nacimiento date,
    parentesco character varying(20) NOT NULL,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer,
    genero character(1),
    vive boolean DEFAULT true,
    CONSTRAINT carga_familiar_genero_check CHECK (((genero IS NULL) OR (genero = ANY (ARRAY['M'::bpchar, 'F'::bpchar])))),
    CONSTRAINT carga_familiar_parentesco_check CHECK (((parentesco)::text = ANY ((ARRAY['Padre'::character varying, 'Madre'::character varying, 'Cónyuge'::character varying, 'Concubino'::character varying, 'Hijo'::character varying])::text[])))
);


--
-- Name: carga_familiar_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.carga_familiar_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: carga_familiar_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.carga_familiar_id_seq OWNED BY public.carga_familiar.id;


--
-- Name: cargos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cargos (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer,
    nivel_jerarquico character varying(20) DEFAULT 'Adscrito'::character varying,
    CONSTRAINT cargos_nivel_jerarquico_check CHECK (((nivel_jerarquico IS NULL) OR ((nivel_jerarquico)::text = ANY ((ARRAY['Presidencia'::character varying, 'Dirección'::character varying, 'Coordinación'::character varying, 'Adscrito'::character varying])::text[]))))
);


--
-- Name: cargos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cargos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cargos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cargos_id_seq OWNED BY public.cargos.id;


--
-- Name: categorias; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.categorias (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: categorias_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.categorias_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: categorias_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.categorias_id_seq OWNED BY public.categorias.id;


--
-- Name: configuracion_sistema; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.configuracion_sistema (
    id integer NOT NULL,
    clave character varying(100) NOT NULL,
    valor text DEFAULT ''::text,
    descripcion character varying(255),
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_by integer
);


--
-- Name: configuracion_sistema_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.configuracion_sistema_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: configuracion_sistema_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.configuracion_sistema_id_seq OWNED BY public.configuracion_sistema.id;


--
-- Name: constancias; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.constancias (
    id integer NOT NULL,
    id_empleado integer NOT NULL,
    numero character varying(30) NOT NULL,
    tipo character varying(50) DEFAULT 'Constancia de trabajo'::character varying NOT NULL,
    fecha_emision timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    observaciones text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: constancias_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.constancias_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: constancias_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.constancias_id_seq OWNED BY public.constancias.id;


--
-- Name: cursos_realizados; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cursos_realizados (
    id integer NOT NULL,
    id_persona integer NOT NULL,
    institucion character varying(150),
    curso character varying(200) NOT NULL,
    fecha_inicio date,
    fecha_culminacion date,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: cursos_realizados_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cursos_realizados_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cursos_realizados_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cursos_realizados_id_seq OWNED BY public.cursos_realizados.id;


--
-- Name: departamentos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.departamentos (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer,
    id_padre integer,
    tipo_unidad character varying(30),
    CONSTRAINT departamentos_tipo_unidad_check CHECK (((tipo_unidad IS NULL) OR ((tipo_unidad)::text = ANY ((ARRAY['Presidencia'::character varying, 'Junta Directiva'::character varying, 'Dirección'::character varying, 'Coordinación'::character varying, 'Oficina'::character varying, 'Unidad'::character varying])::text[]))))
);


--
-- Name: departamentos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.departamentos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: departamentos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.departamentos_id_seq OWNED BY public.departamentos.id;


--
-- Name: empleado_salarios; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.empleado_salarios (
    id integer NOT NULL,
    id_empleado integer NOT NULL,
    fecha_efectiva date DEFAULT CURRENT_DATE NOT NULL,
    sueldo_basico numeric(12,2) DEFAULT 0 NOT NULL,
    prima_profesional numeric(12,2) DEFAULT 0 NOT NULL,
    prima_responsabilidad numeric(12,2) DEFAULT 0 NOT NULL,
    prima_antiguedad numeric(12,2) DEFAULT 0 NOT NULL,
    prima_por_hijo numeric(12,2) DEFAULT 0 NOT NULL,
    bono_transporte numeric(12,2) DEFAULT 0 NOT NULL,
    prima_fond numeric(12,2) DEFAULT 0 NOT NULL,
    prima_discapacidad numeric(12,2) DEFAULT 0 NOT NULL,
    caja_ahorro numeric(12,2) DEFAULT 0 NOT NULL,
    motivo character varying(255),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by integer
);


--
-- Name: empleado_salarios_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.empleado_salarios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: empleado_salarios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.empleado_salarios_id_seq OWNED BY public.empleado_salarios.id;


--
-- Name: empleado_traslados; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.empleado_traslados (
    id integer NOT NULL,
    id_empleado integer NOT NULL,
    id_departamento_origen integer,
    id_departamento_destino integer NOT NULL,
    id_cargo_origen integer,
    id_cargo_destino integer,
    fecha date DEFAULT CURRENT_DATE NOT NULL,
    motivo character varying(255),
    observacion text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by integer
);


--
-- Name: empleado_traslados_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.empleado_traslados_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: empleado_traslados_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.empleado_traslados_id_seq OWNED BY public.empleado_traslados.id;


--
-- Name: empleados; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.empleados (
    id integer NOT NULL,
    id_persona integer NOT NULL,
    id_cargo integer NOT NULL,
    id_departamento integer NOT NULL,
    nro_expediente character varying(20),
    fecha_ingreso date DEFAULT CURRENT_DATE,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer,
    tipo_contrato character varying(30) DEFAULT 'Contratado'::character varying,
    fecha_egreso date,
    id_horario integer,
    institucion_origen character varying(20) DEFAULT 'IMATUR'::character varying,
    es_comision_servicio boolean DEFAULT false,
    clasificacion character varying(20),
    grupo_rotacion character(1),
    uniforme boolean DEFAULT false,
    talla_camisa character varying(10),
    talla_pantalon character varying(10),
    talla_zapato character varying(10),
    motivo_egreso character varying(40),
    observacion_egreso text,
    fecha_vencimiento_contrato date,
    fecha_ingreso_administracion date,
    vacaciones_ajuste_dias integer DEFAULT 0 NOT NULL,
    CONSTRAINT empleados_clasificacion_check CHECK (((clasificacion IS NULL) OR ((clasificacion)::text = ANY ((ARRAY['Empleado'::character varying, 'Obrero'::character varying])::text[])))),
    CONSTRAINT empleados_grupo_rotacion_check CHECK (((grupo_rotacion IS NULL) OR (grupo_rotacion = ANY (ARRAY['A'::bpchar, 'B'::bpchar])))),
    CONSTRAINT empleados_institucion_origen_check CHECK (((institucion_origen)::text = ANY ((ARRAY['Alcaldía'::character varying, 'Gobernación'::character varying, 'IMATUR'::character varying])::text[]))),
    CONSTRAINT empleados_tipo_contrato_check CHECK (((tipo_contrato)::text = ANY ((ARRAY['Fijo'::character varying, 'Contratado'::character varying])::text[])))
);


--
-- Name: empleados_egresos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.empleados_egresos (
    id integer NOT NULL,
    id_empleado integer NOT NULL,
    fecha_egreso date NOT NULL,
    motivo_egreso character varying(40) NOT NULL,
    observacion text,
    fecha_reingreso date,
    reingreso_observacion text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by integer,
    reingreso_at timestamp without time zone,
    reingreso_by integer
);


--
-- Name: empleados_egresos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.empleados_egresos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: empleados_egresos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.empleados_egresos_id_seq OWNED BY public.empleados_egresos.id;


--
-- Name: empleados_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.empleados_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: empleados_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.empleados_id_seq OWNED BY public.empleados.id;


--
-- Name: expediente_documentos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.expediente_documentos (
    id integer NOT NULL,
    id_empleado integer NOT NULL,
    tipo_documento character varying(50) NOT NULL,
    archivo_url character varying(255) NOT NULL,
    nombre_original character varying(255),
    observaciones text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: expediente_documentos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.expediente_documentos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: expediente_documentos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.expediente_documentos_id_seq OWNED BY public.expediente_documentos.id;


--
-- Name: experiencia_laboral; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.experiencia_laboral (
    id integer NOT NULL,
    id_persona integer NOT NULL,
    organismo character varying(150) NOT NULL,
    cargo character varying(150),
    fecha_inicio date,
    fecha_culminacion date,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: experiencia_laboral_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.experiencia_laboral_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: experiencia_laboral_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.experiencia_laboral_id_seq OWNED BY public.experiencia_laboral.id;


--
-- Name: faltas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.faltas (
    id integer NOT NULL,
    id_empleado integer NOT NULL,
    fecha date DEFAULT CURRENT_DATE NOT NULL,
    motivo text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer,
    motivo_anulacion text,
    tipo character varying(40) DEFAULT 'Inasistencia injustificada'::character varying NOT NULL
);


--
-- Name: faltas_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.faltas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: faltas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.faltas_id_seq OWNED BY public.faltas.id;


--
-- Name: feriados; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.feriados (
    id integer NOT NULL,
    fecha date NOT NULL,
    nombre character varying(120) NOT NULL,
    recurrente boolean DEFAULT true NOT NULL,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: feriados_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.feriados_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: feriados_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.feriados_id_seq OWNED BY public.feriados.id;


--
-- Name: horarios; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.horarios (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    hora_entrada time without time zone NOT NULL,
    hora_salida time without time zone NOT NULL,
    dias_laborales character varying(50) DEFAULT 'L-V'::character varying,
    descripcion text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: TABLE horarios; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.horarios IS 'Turnos de trabajo del personal (Ej: Mañana 7-12, Administrativo 8-16).';


--
-- Name: horarios_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.horarios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: horarios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.horarios_id_seq OWNED BY public.horarios.id;


--
-- Name: inventario; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.inventario (
    id integer NOT NULL,
    id_categoria integer NOT NULL,
    id_ubicacion integer NOT NULL,
    codigo_bn character varying(50),
    nombre character varying(255) NOT NULL,
    descripcion text,
    marca character varying(100),
    modelo character varying(100),
    serial character varying(100),
    condicion character varying(20) DEFAULT 'Bueno'::character varying,
    observaciones text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer,
    estatus character varying(30) DEFAULT 'En espera de codificación'::character varying NOT NULL,
    codigo_grupo character varying(4),
    codigo_subgrupo character varying(4),
    codigo_seccion character varying(6),
    nro_orden character varying(10),
    verificado_alcaldia boolean DEFAULT false NOT NULL,
    fecha_verificacion date,
    origen character varying(20) DEFAULT 'Compra'::character varying,
    donante character varying(200),
    costo_adquisicion numeric(14,2),
    fecha_adquisicion date,
    proveedor character varying(200),
    tiene_garantia boolean DEFAULT false NOT NULL,
    garantia_vence date,
    foto_url character varying(255),
    id_consolidado_bm1 integer,
    retirado_alcaldia boolean DEFAULT false NOT NULL,
    fecha_retiro date,
    CONSTRAINT inventario_condicion_check CHECK (((condicion IS NULL) OR ((condicion)::text = ANY ((ARRAY['Nuevo'::character varying, 'Bueno'::character varying, 'Regular'::character varying, 'Dañado'::character varying])::text[])))),
    CONSTRAINT inventario_estatus_check CHECK (((estatus)::text = ANY ((ARRAY['En espera de codificación'::character varying, 'Activo'::character varying, 'En mantenimiento'::character varying, 'Extraviado'::character varying, 'Robado'::character varying, 'Dado de baja'::character varying])::text[]))),
    CONSTRAINT inventario_origen_check CHECK (((origen IS NULL) OR ((origen)::text = ANY ((ARRAY['Compra'::character varying, 'Donación'::character varying])::text[]))))
);


--
-- Name: COLUMN inventario.codigo_bn; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.inventario.codigo_bn IS 'Código oficial compuesto (grupo-subgrupo-sección-N° de orden). Lo arma Inventario::componerCodigo().';


--
-- Name: COLUMN inventario.nro_orden; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.inventario.nro_orden IS 'N° de orden que asigna la Alcaldía (3 dígitos con ceros a la izquierda). NULL hasta la inspección.';


--
-- Name: COLUMN inventario.id_consolidado_bm1; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.inventario.id_consolidado_bm1 IS 'BM-1 en el que la Alcaldía asignó el código de este bien.';


--
-- Name: COLUMN inventario.retirado_alcaldia; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.inventario.retirado_alcaldia IS 'Solo aplica a bienes dados de baja: FALSE = sigue en IMATUR esperando que la Alcaldía lo retire ("Por retirar"); TRUE = ya se lo llevaron (B-67).';


--
-- Name: inventario_consolidados_bm1; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.inventario_consolidados_bm1 (
    id integer NOT NULL,
    fecha_recepcion date DEFAULT CURRENT_DATE NOT NULL,
    fecha_documento date,
    referencia character varying(120),
    archivo_url character varying(255),
    nombre_original character varying(255),
    observaciones text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: TABLE inventario_consolidados_bm1; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.inventario_consolidados_bm1 IS 'Formularios BM-1 recibidos de la Alcaldía (documento entrante, ya codificado).';


--
-- Name: inventario_consolidados_bm1_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.inventario_consolidados_bm1_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: inventario_consolidados_bm1_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.inventario_consolidados_bm1_id_seq OWNED BY public.inventario_consolidados_bm1.id;


--
-- Name: inventario_conteo_detalle; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.inventario_conteo_detalle (
    id integer NOT NULL,
    id_conteo integer NOT NULL,
    id_inventario integer NOT NULL,
    esperado_ubicacion integer,
    esperado_estatus character varying(30),
    esperado_condicion character varying(20),
    hallado boolean,
    hallado_ubicacion integer,
    hallado_condicion character varying(20),
    observaciones text,
    verificado_at timestamp without time zone,
    verificado_by integer
);


--
-- Name: inventario_conteo_detalle_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.inventario_conteo_detalle_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: inventario_conteo_detalle_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.inventario_conteo_detalle_id_seq OWNED BY public.inventario_conteo_detalle.id;


--
-- Name: inventario_conteos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.inventario_conteos (
    id integer NOT NULL,
    motivo character varying(40) NOT NULL,
    fecha_inicio date DEFAULT CURRENT_DATE NOT NULL,
    fecha_cierre date,
    estado character varying(20) DEFAULT 'Abierto'::character varying NOT NULL,
    id_responsable integer,
    observaciones text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer,
    CONSTRAINT inv_conteo_estado_check CHECK (((estado)::text = ANY ((ARRAY['Abierto'::character varying, 'Cerrado'::character varying])::text[]))),
    CONSTRAINT inv_conteo_motivo_check CHECK (((motivo)::text = ANY ((ARRAY['Cambio de coordinación'::character varying, 'Cambio de presidencia'::character varying, 'Auditoría'::character varying, 'Otro'::character varying])::text[])))
);


--
-- Name: inventario_conteos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.inventario_conteos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: inventario_conteos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.inventario_conteos_id_seq OWNED BY public.inventario_conteos.id;


--
-- Name: inventario_documentos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.inventario_documentos (
    id integer NOT NULL,
    id_inventario integer NOT NULL,
    tipo_documento character varying(50) NOT NULL,
    archivo_url character varying(255) NOT NULL,
    nombre_original character varying(255),
    observaciones text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: TABLE inventario_documentos; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.inventario_documentos IS 'Respaldos del bien: factura, informe de la Alcaldía, oficio de donación, actas (B-16 a B-19).';


--
-- Name: inventario_documentos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.inventario_documentos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: inventario_documentos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.inventario_documentos_id_seq OWNED BY public.inventario_documentos.id;


--
-- Name: inventario_dotacion; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.inventario_dotacion (
    id integer NOT NULL,
    id_categoria integer NOT NULL,
    unidades_por_empleado numeric(6,2) DEFAULT 1 NOT NULL,
    observaciones text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    CONSTRAINT inv_dotacion_unidades_check CHECK (((unidades_por_empleado > (0)::numeric) AND (unidades_por_empleado <= (99)::numeric)))
);


--
-- Name: TABLE inventario_dotacion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.inventario_dotacion IS 'B-63: cuántas unidades de cada categoría corresponden POR EMPLEADO. El reporte de suficiencia compara lo que hay en cada departamento contra lo que debería haber según su personal.';


--
-- Name: inventario_dotacion_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.inventario_dotacion_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: inventario_dotacion_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.inventario_dotacion_id_seq OWNED BY public.inventario_dotacion.id;


--
-- Name: inventario_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.inventario_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: inventario_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.inventario_id_seq OWNED BY public.inventario.id;


--
-- Name: inventario_mantenimiento_plan; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.inventario_mantenimiento_plan (
    id integer NOT NULL,
    id_inventario integer NOT NULL,
    frecuencia_meses integer DEFAULT 6 NOT NULL,
    ultima_fecha date,
    proxima_fecha date NOT NULL,
    descripcion text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer,
    CONSTRAINT inv_plan_frec_check CHECK (((frecuencia_meses >= 1) AND (frecuencia_meses <= 60)))
);


--
-- Name: inventario_mantenimiento_plan_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.inventario_mantenimiento_plan_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: inventario_mantenimiento_plan_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.inventario_mantenimiento_plan_id_seq OWNED BY public.inventario_mantenimiento_plan.id;


--
-- Name: inventario_mantenimientos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.inventario_mantenimientos (
    id integer NOT NULL,
    id_inventario integer NOT NULL,
    id_actividad_salida integer,
    fecha_salida date DEFAULT CURRENT_DATE NOT NULL,
    fecha_retorno date,
    id_empleado_encargado integer,
    proveedor_externo character varying(200),
    descripcion_falla text,
    trabajo_realizado text,
    costo numeric(14,2),
    resultado character varying(30),
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer,
    CONSTRAINT inv_mant_resultado_check CHECK (((resultado IS NULL) OR ((resultado)::text = ANY ((ARRAY['Reparado'::character varying, 'Sin reparación'::character varying, 'Irrecuperable'::character varying])::text[]))))
);


--
-- Name: inventario_mantenimientos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.inventario_mantenimientos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: inventario_mantenimientos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.inventario_mantenimientos_id_seq OWNED BY public.inventario_mantenimientos.id;


--
-- Name: municipio; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.municipio (
    id integer NOT NULL,
    nombre character varying(55) NOT NULL,
    codigo_postal character varying(4),
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp without time zone NOT NULL,
    updated_at timestamp without time zone NOT NULL,
    deleted_at timestamp without time zone,
    created_by integer NOT NULL,
    updated_by integer NOT NULL,
    deleted_by integer
);


--
-- Name: TABLE municipio; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.municipio IS 'Municipios disponibles del sistema';


--
-- Name: municipio_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.municipio_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: municipio_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.municipio_id_seq OWNED BY public.municipio.id;


--
-- Name: oficios_emitidos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.oficios_emitidos (
    id integer NOT NULL,
    numero character varying(20) NOT NULL,
    fecha date DEFAULT CURRENT_DATE NOT NULL,
    destinatario_nombre character varying(200),
    destinatario_cargo character varying(200),
    asunto character varying(500),
    id_ruta integer,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by integer
);


--
-- Name: oficios_emitidos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.oficios_emitidos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: oficios_emitidos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.oficios_emitidos_id_seq OWNED BY public.oficios_emitidos.id;


--
-- Name: parroquia; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.parroquia (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    id_municipio integer NOT NULL,
    is_active boolean NOT NULL,
    create_by integer NOT NULL,
    update_by integer NOT NULL,
    delete_by integer,
    create_at timestamp without time zone NOT NULL,
    update_at timestamp without time zone NOT NULL,
    delete_at timestamp without time zone
);


--
-- Name: TABLE parroquia; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.parroquia IS 'Parroquias del sistema, asociada a municipio especifico.';


--
-- Name: parroquia_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.parroquia_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: parroquia_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.parroquia_id_seq OWNED BY public.parroquia.id;


--
-- Name: participantes_ruta; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.participantes_ruta (
    id integer NOT NULL,
    id_ruta integer NOT NULL,
    id_persona integer,
    nombre_libre character varying(100),
    apellido_libre character varying(100),
    cedula_libre character varying(20),
    asistio boolean DEFAULT false,
    observaciones text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer,
    genero_libre character(1),
    fecha_nac_libre date,
    nombre_representante character varying(100),
    cedula_representante character varying(20),
    CONSTRAINT participantes_ruta_genero_libre_check CHECK ((genero_libre = ANY (ARRAY['M'::bpchar, 'F'::bpchar]))),
    CONSTRAINT pr_participante_req CHECK (((id_persona IS NOT NULL) OR (nombre_libre IS NOT NULL)))
);


--
-- Name: COLUMN participantes_ruta.genero_libre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.participantes_ruta.genero_libre IS 'Género del participante sin cédula (niño/a)';


--
-- Name: COLUMN participantes_ruta.fecha_nac_libre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.participantes_ruta.fecha_nac_libre IS 'Fecha de nacimiento del participante sin cédula (validación 5-11 años)';


--
-- Name: participantes_ruta_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.participantes_ruta_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: participantes_ruta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.participantes_ruta_id_seq OWNED BY public.participantes_ruta.id;


--
-- Name: participantes_taller; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.participantes_taller (
    id integer NOT NULL,
    id_taller integer NOT NULL,
    id_persona integer,
    asistio boolean DEFAULT false,
    observaciones text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by integer,
    is_active boolean DEFAULT true,
    updated_at timestamp without time zone,
    updated_by integer,
    deleted_at timestamp without time zone,
    deleted_by integer,
    nombre_libre character varying(100),
    apellido_libre character varying(100),
    cedula_libre character varying(20),
    nombre_docente character varying(100),
    cedula_docente character varying(20),
    fecha_nac_libre date,
    genero_libre character(1),
    parroquia_id_libre integer,
    direccion_libre text,
    CONSTRAINT participantes_taller_genero_libre_check CHECK ((genero_libre = ANY (ARRAY['M'::bpchar, 'F'::bpchar]))),
    CONSTRAINT pt_participante_requerido CHECK (((id_persona IS NOT NULL) OR (nombre_libre IS NOT NULL)))
);


--
-- Name: participantes_taller_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.participantes_taller_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: participantes_taller_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.participantes_taller_id_seq OWNED BY public.participantes_taller.id;


--
-- Name: pasante_documentos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pasante_documentos (
    id integer NOT NULL,
    id_pasante integer NOT NULL,
    tipo_documento character varying(100) NOT NULL,
    entregado boolean DEFAULT false,
    archivo_url text,
    observaciones text,
    fecha_registro timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by integer,
    is_active boolean DEFAULT true,
    updated_at timestamp without time zone,
    updated_by integer,
    deleted_at timestamp without time zone,
    deleted_by integer,
    CONSTRAINT pasante_documentos_tipo_documento_check CHECK (((tipo_documento)::text = ANY ((ARRAY['Carta de Postulación'::character varying, 'Carta de Aceptación'::character varying, 'Evaluación'::character varying, 'Otro'::character varying])::text[])))
);


--
-- Name: pasante_documentos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.pasante_documentos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pasante_documentos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.pasante_documentos_id_seq OWNED BY public.pasante_documentos.id;


--
-- Name: pasantes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pasantes (
    id integer NOT NULL,
    institucion character varying(200) NOT NULL,
    carrera character varying(200),
    id_tutor_institucional integer,
    fecha_inicio date,
    fecha_fin date,
    estado character varying(50) DEFAULT 'Postulado'::character varying,
    evaluacion text,
    nota numeric(5,2),
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer,
    id_persona integer NOT NULL,
    oficio_aceptacion character varying(25),
    tutor_externo character varying(200),
    CONSTRAINT pasantes_estado_check CHECK (((estado)::text = ANY ((ARRAY['Postulado'::character varying, 'Aceptado'::character varying, 'En Curso'::character varying, 'Culminado'::character varying, 'Rechazado'::character varying])::text[])))
);


--
-- Name: pasantes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.pasantes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pasantes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.pasantes_id_seq OWNED BY public.pasantes.id;


--
-- Name: password_resets; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_resets (
    id integer NOT NULL,
    id_usuario integer NOT NULL,
    token_hash character varying(64) NOT NULL,
    expires_at timestamp without time zone NOT NULL,
    used_at timestamp without time zone,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    requested_ip character varying(45)
);


--
-- Name: password_resets_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.password_resets_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: password_resets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.password_resets_id_seq OWNED BY public.password_resets.id;


--
-- Name: permisos_laborales; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.permisos_laborales (
    id integer NOT NULL,
    id_empleado integer NOT NULL,
    tipo_permiso character varying(50) NOT NULL,
    fecha_inicio date NOT NULL,
    fecha_fin date NOT NULL,
    dias_solicitados integer,
    motivo text,
    estado character varying(20) DEFAULT 'Pendiente'::character varying,
    id_aprobador integer,
    fecha_aprobacion timestamp without time zone,
    observaciones text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer,
    categoria character varying(20),
    duracion character varying(40),
    CONSTRAINT permisos_categoria_check CHECK (((categoria IS NULL) OR ((categoria)::text = ANY ((ARRAY['Reposo'::character varying, 'Permiso'::character varying, 'Vacaciones'::character varying])::text[])))),
    CONSTRAINT permisos_estado_check CHECK (((estado)::text = ANY ((ARRAY['Pendiente'::character varying, 'Aprobado'::character varying, 'Rechazado'::character varying, 'Anulado'::character varying])::text[]))),
    CONSTRAINT permisos_tipo_check CHECK (((tipo_permiso)::text = ANY ((ARRAY['Reposo médico'::character varying, 'Médico familiar'::character varying, 'Diligencia'::character varying, 'Duelo'::character varying, 'Maternidad/Paternidad'::character varying, 'Personal'::character varying, 'Estudios'::character varying, 'Otro'::character varying])::text[])))
);


--
-- Name: TABLE permisos_laborales; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.permisos_laborales IS 'Registro de permisos y ausencias justificadas del personal.';


--
-- Name: permisos_laborales_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.permisos_laborales_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: permisos_laborales_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.permisos_laborales_id_seq OWNED BY public.permisos_laborales.id;


--
-- Name: permisos_rol; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.permisos_rol (
    id integer NOT NULL,
    id_rol integer NOT NULL,
    modulo character varying(60) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by integer
);


--
-- Name: permisos_rol_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.permisos_rol_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: permisos_rol_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.permisos_rol_id_seq OWNED BY public.permisos_rol.id;


--
-- Name: personas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.personas (
    id integer NOT NULL,
    cedula character varying(15),
    nombre character varying(100) NOT NULL,
    apellido character varying(100) NOT NULL,
    telefono character varying(15),
    correo character varying(100),
    genero character(1),
    fecha_nacimiento date,
    direccion text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer,
    parroquia_id integer,
    rif character varying(20),
    estado_civil character varying(20),
    discapacidad boolean DEFAULT false,
    discapacidad_detalle character varying(150),
    nivel_academico character varying(50),
    profesion character varying(120),
    titulo character varying(150),
    fecha_graduacion date,
    institucion_academica character varying(150),
    centro_votacion character varying(150),
    consejo_comunal character varying(150),
    comuna character varying(150),
    foto_url character varying(255),
    CONSTRAINT personas_estado_civil_check CHECK (((estado_civil IS NULL) OR ((estado_civil)::text = ANY ((ARRAY['Soltero'::character varying, 'Casado'::character varying, 'Concubinato'::character varying, 'Divorciado'::character varying, 'Viudo'::character varying])::text[])))),
    CONSTRAINT personas_genero_check CHECK ((genero = ANY (ARRAY['M'::bpchar, 'F'::bpchar])))
);


--
-- Name: TABLE personas; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.personas IS 'Datos base de todas las personas físicas del sistema.';


--
-- Name: COLUMN personas.foto_url; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.personas.foto_url IS 'Nombre del archivo de foto (carnetización). Ruta real en storage/uploads/fotos/.';


--
-- Name: personas_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.personas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: personas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.personas_id_seq OWNED BY public.personas.id;


--
-- Name: puntos_ruta; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.puntos_ruta (
    id integer NOT NULL,
    id_ruta integer NOT NULL,
    nombre character varying(200) NOT NULL,
    descripcion text,
    orden integer DEFAULT 1 NOT NULL,
    latitud numeric(10,7),
    longitud numeric(10,7),
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: puntos_ruta_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.puntos_ruta_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: puntos_ruta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.puntos_ruta_id_seq OWNED BY public.puntos_ruta.id;


--
-- Name: roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.roles (
    id integer NOT NULL,
    nombre character varying(50) NOT NULL,
    descripcion text,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: TABLE roles; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.roles IS 'Define los niveles de permisos del sistema (Ej. Administrador, RRHH, Turismo). Determina a qué pantallas puede entrar un usuario.';


--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.roles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- Name: ruta_informes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.ruta_informes (
    id integer NOT NULL,
    id_ruta integer NOT NULL,
    lugar_exacto character varying(300),
    mujeres integer DEFAULT 0 NOT NULL,
    hombres integer DEFAULT 0 NOT NULL,
    ninas integer DEFAULT 0 NOT NULL,
    ninos integer DEFAULT 0 NOT NULL,
    total_atendidos integer DEFAULT 0 NOT NULL,
    observaciones text,
    resumen_visita text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by integer
);


--
-- Name: TABLE ruta_informes; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.ruta_informes IS 'Informe demográfico post-visita de una ruta turística';


--
-- Name: COLUMN ruta_informes.ninas; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.ruta_informes.ninas IS 'Participantes libres femeninas (5-11 años)';


--
-- Name: COLUMN ruta_informes.ninos; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.ruta_informes.ninos IS 'Participantes libres masculinos (5-11 años)';


--
-- Name: COLUMN ruta_informes.total_atendidos; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.ruta_informes.total_atendidos IS 'Suma calculada: mujeres + hombres + ninas + ninos';


--
-- Name: ruta_informes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.ruta_informes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: ruta_informes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.ruta_informes_id_seq OWNED BY public.ruta_informes.id;


--
-- Name: rutas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.rutas (
    id integer NOT NULL,
    nombre character varying(200) NOT NULL,
    descripcion text,
    duracion_estimada character varying(50),
    estado character varying(20) DEFAULT 'Activa'::character varying,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer,
    fecha_visita date,
    hora_visita time without time zone,
    id_departamento integer,
    id_facilitador integer,
    cupo_maximo integer DEFAULT 20,
    requiere_formacion boolean DEFAULT false NOT NULL,
    tiene_tarifa boolean DEFAULT false,
    tarifa_monto numeric(10,2) DEFAULT NULL::numeric,
    motivo_mantenimiento text,
    tipo_ruta character varying(50) DEFAULT 'General'::character varying,
    CONSTRAINT rutas_estado_check CHECK (((estado)::text = ANY ((ARRAY['Activa'::character varying, 'Inactiva'::character varying, 'En Mantenimiento'::character varying, 'Finalizada'::character varying])::text[]))),
    CONSTRAINT rutas_tipo_ruta_check CHECK (((tipo_ruta)::text = ANY ((ARRAY['Cumaná Histórica'::character varying, 'Exploradores de Cumaná'::character varying, 'Comunitaria'::character varying, 'General'::character varying])::text[])))
);


--
-- Name: COLUMN rutas.estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rutas.estado IS 'Activa | Inactiva | En Mantenimiento | Finalizada (terminal: visita ejecutada)';


--
-- Name: COLUMN rutas.motivo_mantenimiento; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rutas.motivo_mantenimiento IS 'Motivo obligatorio cuando estado = ''En Mantenimiento''';


--
-- Name: COLUMN rutas.tipo_ruta; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rutas.tipo_ruta IS 'Programa o clasificación administrativa de la ruta';


--
-- Name: rutas_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.rutas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: rutas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.rutas_id_seq OWNED BY public.rutas.id;


--
-- Name: taller_evidencias; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.taller_evidencias (
    id integer NOT NULL,
    id_taller integer NOT NULL,
    archivo character varying(300) NOT NULL,
    nombre_original character varying(300) NOT NULL,
    tipo_archivo character varying(100),
    uploaded_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    uploaded_by integer,
    is_active boolean DEFAULT true NOT NULL,
    deleted_at timestamp without time zone,
    deleted_by integer
);


--
-- Name: taller_evidencias_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.taller_evidencias_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: taller_evidencias_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.taller_evidencias_id_seq OWNED BY public.taller_evidencias.id;


--
-- Name: taller_informes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.taller_informes (
    id integer NOT NULL,
    id_taller integer NOT NULL,
    unidad_estadal character varying(255) DEFAULT 'Sucre'::character varying,
    lugar_exacto character varying(255),
    instituciones_presentes text,
    mujeres integer DEFAULT 0,
    hombres integer DEFAULT 0,
    ninas integer DEFAULT 0,
    ninos integer DEFAULT 0,
    total_atendidas integer DEFAULT 0,
    resumen_actividad text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_at timestamp without time zone,
    deleted_by integer
);


--
-- Name: taller_informes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.taller_informes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: taller_informes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.taller_informes_id_seq OWNED BY public.taller_informes.id;


--
-- Name: talleres; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.talleres (
    id integer NOT NULL,
    nombre character varying(200) NOT NULL,
    descripcion text,
    fecha_inicio date NOT NULL,
    fecha_fin date,
    hora_inicio time without time zone,
    hora_fin time without time zone,
    id_ubicacion_formacion integer,
    id_facilitador integer NOT NULL,
    cupo_maximo integer DEFAULT 30,
    estado character varying(20) DEFAULT 'Programado'::character varying,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer,
    tipo_actividad character varying(30) DEFAULT 'Taller'::character varying,
    es_interna boolean DEFAULT false NOT NULL,
    tipo_ente character varying(50),
    motivo_cancelacion text,
    CONSTRAINT talleres_estado_check CHECK (((estado)::text = ANY ((ARRAY['Programado'::character varying, 'En Curso'::character varying, 'Finalizado'::character varying, 'Cancelado'::character varying])::text[]))),
    CONSTRAINT talleres_tipo_actividad_check CHECK (((tipo_actividad)::text = ANY ((ARRAY['Taller'::character varying, 'Charla'::character varying, 'Inducción'::character varying])::text[]))),
    CONSTRAINT talleres_tipo_ente_check CHECK (((tipo_ente IS NULL) OR ((tipo_ente)::text = ANY ((ARRAY['Escuela'::character varying, 'Liceo'::character varying, 'Comunidad'::character varying, 'Prestador de Servicio'::character varying, 'IMATUR'::character varying])::text[]))))
);


--
-- Name: talleres_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.talleres_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: talleres_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.talleres_id_seq OWNED BY public.talleres.id;


--
-- Name: ubicaciones; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.ubicaciones (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer,
    "departamento _d" integer NOT NULL,
    sede character varying(80) DEFAULT 'Sede Principal'::character varying,
    es_deposito boolean DEFAULT false NOT NULL
);


--
-- Name: TABLE ubicaciones; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.ubicaciones IS ' Estas son las oficinas o almacenes internos de la institución donde "duerme" un equipo (Ej. Almacén Principal, Despacho del Director).';


--
-- Name: ubicaciones_formacion; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.ubicaciones_formacion (
    id integer NOT NULL,
    nombre character varying(150) NOT NULL,
    tipo character varying(50),
    direccion text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer,
    parroquia integer NOT NULL,
    es_sede_propia boolean DEFAULT false
);


--
-- Name: ubicaciones_formacion_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.ubicaciones_formacion_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: ubicaciones_formacion_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.ubicaciones_formacion_id_seq OWNED BY public.ubicaciones_formacion.id;


--
-- Name: ubicaciones_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.ubicaciones_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: ubicaciones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.ubicaciones_id_seq OWNED BY public.ubicaciones.id;


--
-- Name: usuarios; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.usuarios (
    id integer NOT NULL,
    id_empleado integer NOT NULL,
    id_rol integer NOT NULL,
    username character varying(50) NOT NULL,
    password text NOT NULL,
    ultimo_login timestamp without time zone,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer,
    failed_attempts integer DEFAULT 0 NOT NULL,
    locked_until timestamp without time zone,
    last_login timestamp without time zone
);


--
-- Name: usuarios_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.usuarios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: usuarios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.usuarios_id_seq OWNED BY public.usuarios.id;


--
-- Name: vacaciones; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.vacaciones (
    id integer NOT NULL,
    id_empleado integer NOT NULL,
    anio integer NOT NULL,
    dias_correspondientes integer DEFAULT 15,
    dias_tomados integer DEFAULT 0,
    fecha_inicio date,
    fecha_fin date,
    estado character varying(20) DEFAULT 'Pendiente'::character varying,
    observaciones text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer,
    CONSTRAINT vacaciones_estado_check CHECK (((estado)::text = ANY ((ARRAY['Pendiente'::character varying, 'Aprobado'::character varying, 'En Curso'::character varying, 'Completado'::character varying, 'Rechazado'::character varying])::text[])))
);


--
-- Name: TABLE vacaciones; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.vacaciones IS 'Control anual de días de vacaciones por empleado.';


--
-- Name: vacaciones_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.vacaciones_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: vacaciones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.vacaciones_id_seq OWNED BY public.vacaciones.id;


--
-- Name: visitantes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.visitantes (
    id integer NOT NULL,
    cedula character varying(20),
    nombre character varying(100),
    apellido character varying(100),
    procedencia character varying(100),
    telefono character varying(20),
    genero character(1),
    correo character varying(100),
    motivo_frecuente text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer,
    id_persona integer,
    CONSTRAINT visitantes_genero_check CHECK ((genero = ANY (ARRAY['M'::bpchar, 'F'::bpchar])))
);


--
-- Name: TABLE visitantes; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.visitantes IS 'Personas externas a la institución que realizan visitas.';


--
-- Name: visitantes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.visitantes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: visitantes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.visitantes_id_seq OWNED BY public.visitantes.id;


--
-- Name: visitas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.visitas (
    id integer NOT NULL,
    id_visitante integer NOT NULL,
    id_empleado integer,
    motivo character varying(255),
    hora_entrada timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    hora_salida timestamp without time zone,
    observaciones text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by integer
);


--
-- Name: TABLE visitas; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.visitas IS 'Control de marcaje de entrada y salida de visitantes externos.';


--
-- Name: visitas_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.visitas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: visitas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.visitas_id_seq OWNED BY public.visitas.id;


--
-- Name: actividad_inventario id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actividad_inventario ALTER COLUMN id SET DEFAULT nextval('public.actividad_inventario_id_seq'::regclass);


--
-- Name: actividades_ruta id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actividades_ruta ALTER COLUMN id SET DEFAULT nextval('public.actividades_ruta_id_seq'::regclass);


--
-- Name: alertas_vistas id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.alertas_vistas ALTER COLUMN id SET DEFAULT nextval('public.alertas_vistas_id_seq'::regclass);


--
-- Name: amonestaciones id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.amonestaciones ALTER COLUMN id SET DEFAULT nextval('public.amonestaciones_id_seq'::regclass);


--
-- Name: asistencias id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asistencias ALTER COLUMN id SET DEFAULT nextval('public.asistencias_id_seq'::regclass);


--
-- Name: audit_logs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs ALTER COLUMN id SET DEFAULT nextval('public.audit_logs_id_seq'::regclass);


--
-- Name: bono_vacacional_detalle id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bono_vacacional_detalle ALTER COLUMN id SET DEFAULT nextval('public.bono_vacacional_detalle_id_seq'::regclass);


--
-- Name: bono_vacacional_periodos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bono_vacacional_periodos ALTER COLUMN id SET DEFAULT nextval('public.bono_vacacional_periodos_id_seq'::regclass);


--
-- Name: carga_familiar id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.carga_familiar ALTER COLUMN id SET DEFAULT nextval('public.carga_familiar_id_seq'::regclass);


--
-- Name: cargos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cargos ALTER COLUMN id SET DEFAULT nextval('public.cargos_id_seq'::regclass);


--
-- Name: categorias id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categorias ALTER COLUMN id SET DEFAULT nextval('public.categorias_id_seq'::regclass);


--
-- Name: configuracion_sistema id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.configuracion_sistema ALTER COLUMN id SET DEFAULT nextval('public.configuracion_sistema_id_seq'::regclass);


--
-- Name: constancias id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.constancias ALTER COLUMN id SET DEFAULT nextval('public.constancias_id_seq'::regclass);


--
-- Name: cursos_realizados id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cursos_realizados ALTER COLUMN id SET DEFAULT nextval('public.cursos_realizados_id_seq'::regclass);


--
-- Name: departamentos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.departamentos ALTER COLUMN id SET DEFAULT nextval('public.departamentos_id_seq'::regclass);


--
-- Name: empleado_salarios id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.empleado_salarios ALTER COLUMN id SET DEFAULT nextval('public.empleado_salarios_id_seq'::regclass);


--
-- Name: empleado_traslados id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.empleado_traslados ALTER COLUMN id SET DEFAULT nextval('public.empleado_traslados_id_seq'::regclass);


--
-- Name: empleados id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.empleados ALTER COLUMN id SET DEFAULT nextval('public.empleados_id_seq'::regclass);


--
-- Name: empleados_egresos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.empleados_egresos ALTER COLUMN id SET DEFAULT nextval('public.empleados_egresos_id_seq'::regclass);


--
-- Name: expediente_documentos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.expediente_documentos ALTER COLUMN id SET DEFAULT nextval('public.expediente_documentos_id_seq'::regclass);


--
-- Name: experiencia_laboral id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.experiencia_laboral ALTER COLUMN id SET DEFAULT nextval('public.experiencia_laboral_id_seq'::regclass);


--
-- Name: faltas id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.faltas ALTER COLUMN id SET DEFAULT nextval('public.faltas_id_seq'::regclass);


--
-- Name: feriados id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.feriados ALTER COLUMN id SET DEFAULT nextval('public.feriados_id_seq'::regclass);


--
-- Name: horarios id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.horarios ALTER COLUMN id SET DEFAULT nextval('public.horarios_id_seq'::regclass);


--
-- Name: inventario id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario ALTER COLUMN id SET DEFAULT nextval('public.inventario_id_seq'::regclass);


--
-- Name: inventario_consolidados_bm1 id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario_consolidados_bm1 ALTER COLUMN id SET DEFAULT nextval('public.inventario_consolidados_bm1_id_seq'::regclass);


--
-- Name: inventario_conteo_detalle id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario_conteo_detalle ALTER COLUMN id SET DEFAULT nextval('public.inventario_conteo_detalle_id_seq'::regclass);


--
-- Name: inventario_conteos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario_conteos ALTER COLUMN id SET DEFAULT nextval('public.inventario_conteos_id_seq'::regclass);


--
-- Name: inventario_documentos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario_documentos ALTER COLUMN id SET DEFAULT nextval('public.inventario_documentos_id_seq'::regclass);


--
-- Name: inventario_dotacion id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario_dotacion ALTER COLUMN id SET DEFAULT nextval('public.inventario_dotacion_id_seq'::regclass);


--
-- Name: inventario_mantenimiento_plan id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario_mantenimiento_plan ALTER COLUMN id SET DEFAULT nextval('public.inventario_mantenimiento_plan_id_seq'::regclass);


--
-- Name: inventario_mantenimientos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario_mantenimientos ALTER COLUMN id SET DEFAULT nextval('public.inventario_mantenimientos_id_seq'::regclass);


--
-- Name: municipio id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.municipio ALTER COLUMN id SET DEFAULT nextval('public.municipio_id_seq'::regclass);


--
-- Name: oficios_emitidos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.oficios_emitidos ALTER COLUMN id SET DEFAULT nextval('public.oficios_emitidos_id_seq'::regclass);


--
-- Name: parroquia id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.parroquia ALTER COLUMN id SET DEFAULT nextval('public.parroquia_id_seq'::regclass);


--
-- Name: participantes_ruta id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.participantes_ruta ALTER COLUMN id SET DEFAULT nextval('public.participantes_ruta_id_seq'::regclass);


--
-- Name: participantes_taller id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.participantes_taller ALTER COLUMN id SET DEFAULT nextval('public.participantes_taller_id_seq'::regclass);


--
-- Name: pasante_documentos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pasante_documentos ALTER COLUMN id SET DEFAULT nextval('public.pasante_documentos_id_seq'::regclass);


--
-- Name: pasantes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pasantes ALTER COLUMN id SET DEFAULT nextval('public.pasantes_id_seq'::regclass);


--
-- Name: password_resets id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_resets ALTER COLUMN id SET DEFAULT nextval('public.password_resets_id_seq'::regclass);


--
-- Name: permisos_laborales id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permisos_laborales ALTER COLUMN id SET DEFAULT nextval('public.permisos_laborales_id_seq'::regclass);


--
-- Name: permisos_rol id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permisos_rol ALTER COLUMN id SET DEFAULT nextval('public.permisos_rol_id_seq'::regclass);


--
-- Name: personas id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personas ALTER COLUMN id SET DEFAULT nextval('public.personas_id_seq'::regclass);


--
-- Name: puntos_ruta id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.puntos_ruta ALTER COLUMN id SET DEFAULT nextval('public.puntos_ruta_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Name: ruta_informes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ruta_informes ALTER COLUMN id SET DEFAULT nextval('public.ruta_informes_id_seq'::regclass);


--
-- Name: rutas id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rutas ALTER COLUMN id SET DEFAULT nextval('public.rutas_id_seq'::regclass);


--
-- Name: taller_evidencias id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.taller_evidencias ALTER COLUMN id SET DEFAULT nextval('public.taller_evidencias_id_seq'::regclass);


--
-- Name: taller_informes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.taller_informes ALTER COLUMN id SET DEFAULT nextval('public.taller_informes_id_seq'::regclass);


--
-- Name: talleres id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.talleres ALTER COLUMN id SET DEFAULT nextval('public.talleres_id_seq'::regclass);


--
-- Name: ubicaciones id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ubicaciones ALTER COLUMN id SET DEFAULT nextval('public.ubicaciones_id_seq'::regclass);


--
-- Name: ubicaciones_formacion id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ubicaciones_formacion ALTER COLUMN id SET DEFAULT nextval('public.ubicaciones_formacion_id_seq'::regclass);


--
-- Name: usuarios id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios ALTER COLUMN id SET DEFAULT nextval('public.usuarios_id_seq'::regclass);


--
-- Name: vacaciones id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vacaciones ALTER COLUMN id SET DEFAULT nextval('public.vacaciones_id_seq'::regclass);


--
-- Name: visitantes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.visitantes ALTER COLUMN id SET DEFAULT nextval('public.visitantes_id_seq'::regclass);


--
-- Name: visitas id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.visitas ALTER COLUMN id SET DEFAULT nextval('public.visitas_id_seq'::regclass);


--
-- Data for Name: cargos; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.cargos (id, nombre, descripcion, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by, nivel_jerarquico) FROM stdin;
3	CTI	Coordinación de tecnología de Información.	t	2026-04-17 14:52:17.178796	\N	\N	\N	\N	\N	Adscrito
5	Presidenta	Máxima autoridad del instituto	t	2026-06-05 03:44:31.144823	\N	\N	\N	\N	\N	Presidencia
6	Coordinador	Responsable de una coordinación	t	2026-06-05 03:44:31.144823	\N	\N	\N	\N	\N	Coordinación
2	Director	Responsable de una Dirección	t	2026-04-12 14:41:40.888475	2026-06-08 22:31:12.277056	\N	\N	\N	\N	Dirección
4	Guia Turistico	Trabajador encargado de rutas turisticas	t	2026-06-01 15:56:06.1521	2026-06-08 22:35:39.56058	\N	\N	\N	\N	Adscrito
\.


--
-- Data for Name: categorias; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.categorias (id, nombre, descripcion, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by) FROM stdin;
1	Inmobiliario	Bienes inmobiliarios	f	2026-04-18 04:56:22.455698	\N	2026-08-04 18:11:11.195526	\N	\N	\N
2	Inmuebles	Prueba  2	f	2026-04-28 03:21:34.415084	2026-04-28 03:21:48.698294	2026-08-04 18:11:11.195526	\N	\N	\N
3	Climatización y refrigeración	Aires acondicionados, ventiladores, neveras	t	2026-08-04 18:11:11.195526	\N	\N	\N	\N	\N
4	Equipos de comunicación	Teléfonos, radios, centrales telefónicas, routers	t	2026-08-04 18:11:11.195526	\N	\N	\N	\N	\N
5	Equipos de seguridad	Extintores, cámaras de vigilancia, alarmas	t	2026-08-04 18:11:11.195526	\N	\N	\N	\N	\N
6	Electrodomésticos y enseres	Cafeteras, microondas, dispensadores de agua	t	2026-08-04 18:11:11.195526	\N	\N	\N	\N	\N
7	Herramientas y mantenimiento	Herramientas de Servicios Generales	t	2026-08-04 18:11:11.195526	\N	\N	\N	\N	\N
8	Equipos de computación	CPU, laptops, monitores, impresoras, escáneres, UPS	t	2026-08-04 18:11:11.195526	\N	\N	\N	\N	\N
9	Material turístico y promocional	Stands, pendones, kioscos, señalética, lonas	t	2026-08-04 18:11:11.195526	\N	\N	\N	\N	\N
10	Máquinas y equipos de oficina	Fotocopiadoras, trituradoras, encuadernadoras	t	2026-08-04 18:11:11.195526	\N	\N	\N	\N	\N
11	Bienes culturales y bibliográficos	Libros, obras, piezas de exhibición	t	2026-08-04 18:11:11.195526	\N	\N	\N	\N	\N
12	Equipos audiovisuales	Videobeam, cámaras, televisores, sonido, micrófonos	t	2026-08-04 18:11:11.195526	\N	\N	\N	\N	\N
13	Mobiliario de oficina	Escritorios, sillas, mesas, archivadores, estantes	t	2026-08-04 18:11:11.195526	\N	\N	\N	\N	\N
\.


--
-- Data for Name: configuracion_sistema; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.configuracion_sistema (id, clave, valor, descripcion, updated_at, updated_by) FROM stdin;
26	rif_institucional	G-20008498-7	RIF del instituto, usado en documentos oficiales y reportes	2026-06-28 17:01:55.392757	\N
27	minutos_tolerancia_salida_temprana	10	Minutos de tolerancia antes de la hora de salida del horario, antes de exigir motivo de salida anticipada	2026-07-11 23:51:17.899562	\N
28	bono_vac_dias_alto_nivel	75	Bono Vacacional: días base (+ años de servicio) para Alto Nivel y Dirección	2026-07-16 12:43:18.149605	\N
29	bono_vac_dias_empleados_fijos	75	Bono Vacacional: días base (+ años de servicio) para Empleados Fijos	2026-07-16 12:43:18.149605	\N
30	bono_vac_dias_obreros_fijos	85	Bono Vacacional: días fijos (no suma años) para Obreros Fijos	2026-07-16 12:43:18.149605	\N
31	bono_vac_dias_contratados	45	Bono Vacacional: días base (+ años de servicio) para Contratados	2026-07-16 12:43:18.149605	\N
32	monto_cesta_ticket	0	Monto mensual de cesta ticket usado en el cálculo del Bono Vacacional	2026-07-16 12:43:18.149605	\N
11	ano_correlativo_ruta	0	Año del correlativo activo (se reinicia automáticamente)	2026-05-08 16:57:20.940974	\N
12	firmante_cargo	Director General	\N	2026-05-20 10:35:25.894766	\N
13	correlativo_oficio_formacion	0	\N	2026-05-20 10:35:25.894766	\N
14	ano_correlativo_formacion	0	\N	2026-05-20 10:35:25.894766	\N
24	correlativo_oficio_constancia	0	Último correlativo de constancias de trabajo	2026-06-05 17:21:13.71886	\N
8	telf_institucion	0293-4310178	Teléfono institucional	2026-08-04 14:42:39.592851	\N
9	correo_institucion	Sucreimatur@gmail.com	Correo electrónico institucional	2026-08-04 14:42:39.592851	\N
38	direccion_institucion	Estado Sucre, municipio Sucre, Cumaná, Calle Sucre, Casa Nº11	Dirección física del instituto. Aparece en el carnet institucional.	2026-08-04 14:42:39.592851	\N
21	correlativo_oficio_pasante	0	Correlativo de cartas de aceptación de pasantes (PAST-NNN/AAAA)	2026-06-01 13:27:58.416309	\N
22	ano_correlativo_pasante	0	Año del correlativo de cartas de aceptación de pasantes	2026-06-01 13:27:58.416309	\N
10	correlativo_oficio_ruta	0	Último correlativo de oficio emitido en el año en curso	2026-05-08 16:57:20.880114	\N
1	director_nombre	Maria	Nombre del Director/Presidente de IMATUR	2026-06-28 17:01:54.746647	\N
2	director_apellido	Maza	Apellido del Director/Presidente	2026-06-28 17:01:54.800802	\N
3	director_cargo	Director	Cargo del firmante institucional	2026-06-28 17:01:54.849354	\N
4	resolucion_numero	025	N° de la Resolución de nombramiento	2026-06-28 17:01:54.891653	\N
5	resolucion_fecha	15 De Marzo De 2024	Fecha de la Resolución (texto, ej: 15 de enero de 2025)	2026-06-28 17:01:54.939612	\N
25	ano_correlativo_constancia	0	Año del correlativo de constancias	2026-06-05 17:21:13.71886	\N
6	gaceta_numero	042	N° de la Gaceta Municipal Extraordinaria	2026-06-28 17:01:54.985504	\N
39	lema_institucion	Historia y Porvenir	Lema del instituto. Aparece al pie del carnet institucional.	2026-08-04 14:42:39.592851	\N
40	bienes_depto_autoriza	23	Departamento cuya jefatura autoriza los movimientos de bienes (Coordinación de Compras, Bienes y Servicios).	2026-08-04 19:28:35.763964	\N
41	bienes_cargo_autoriza	6	Cargo que autoriza los movimientos de bienes dentro de ese departamento.	2026-08-04 19:28:35.763964	\N
42	dias_aviso_garantia	30	Días de antelación para avisar que la garantía de un bien está por vencer.	2026-08-04 20:55:13.565943	\N
7	gaceta_fecha	20 De Enero Del 2024	Fecha de la Gaceta (texto, ej: 20 de enero de 2025)	2026-06-28 17:01:55.030345	\N
15	meta_talleres_anio	100	Meta anual de actividades formativas a ejecutar	2026-06-28 17:01:55.162101	\N
16	meta_rutas_anio	100	Meta anual de rutas turísticas a ejecutar	2026-06-28 17:01:55.201958	\N
17	dias_preaviso_contrato	15	Días de anticipación para alertar sobre contratos vencientes	2026-06-28 17:01:55.257451	\N
18	dias_preaviso_pasante	10	Días de anticipación para alertar sobre pasantes próximos a culminar	2026-06-28 17:01:55.304731	\N
23	minutos_tolerancia_puntualidad	5	Minutos de tolerancia tras la hora de entrada antes de marcar impuntualidad	2026-06-28 17:01:55.354204	\N
43	dias_aviso_mantenimiento	15	Días de antelación para avisar que toca el mantenimiento preventivo de un bien.	2026-08-04 20:55:13.565943	\N
44	dias_alerta_sin_codificar	30	Días que puede llevar un bien esperando el código de la Alcaldía antes de avisar.	2026-08-04 20:55:13.565943	\N
\.


--
-- Data for Name: departamentos; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.departamentos (id, nombre, descripcion, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by, id_padre, tipo_unidad) FROM stdin;
6	Presidencia	\N	t	2026-06-05 03:44:31.144823	\N	\N	\N	\N	\N	\N	Presidencia
3	Dirección General	Sede Principal	t	2026-04-12 14:41:40.888475	\N	\N	\N	\N	\N	6	Dirección
13	Dirección de Secretaría	\N	t	2026-06-05 03:44:31.144823	\N	\N	\N	\N	\N	6	Dirección
11	Relaciones Inter-Institucionales	\N	t	2026-06-05 03:44:31.144823	\N	\N	\N	\N	\N	6	Oficina
12	Oficina de Atención al Ciudadano (OAC)	\N	t	2026-06-05 03:44:31.144823	\N	\N	\N	\N	\N	6	Oficina
14	Consultoría Jurídica	\N	t	2026-06-05 03:44:31.144823	\N	\N	\N	\N	\N	6	Oficina
15	Auditoría Interna	\N	t	2026-06-05 03:44:31.144823	\N	\N	\N	\N	\N	6	Oficina
5	Dirección de Talento Humano	Departamento de Talento Humano	t	2026-04-28 03:25:26.955571	\N	\N	\N	\N	\N	6	Dirección
7	Dirección de Planificación y Gestión Turística	\N	t	2026-06-05 03:44:31.144823	\N	\N	\N	\N	\N	6	Dirección
8	Dirección de Administración	\N	t	2026-06-05 03:44:31.144823	\N	\N	\N	\N	\N	6	Dirección
16	Promoción Turística	\N	t	2026-06-05 03:44:31.144823	\N	\N	\N	\N	\N	7	Coordinación
17	Calidad y Servicios Turísticos	\N	t	2026-06-05 03:44:31.144823	\N	\N	\N	\N	\N	7	Coordinación
18	Proyectos e Inversión Turística	\N	t	2026-06-05 03:44:31.144823	\N	\N	\N	\N	\N	7	Coordinación
19	Formación	\N	t	2026-06-05 03:44:31.144823	\N	\N	\N	\N	\N	7	Coordinación
20	Comunicación	\N	t	2026-06-05 03:44:31.144823	\N	\N	\N	\N	\N	7	Coordinación
21	Presupuesto	\N	t	2026-06-05 03:44:31.144823	\N	\N	\N	\N	\N	8	Coordinación
22	Contabilidad	\N	t	2026-06-05 03:44:31.144823	\N	\N	\N	\N	\N	8	Coordinación
23	Compra de Bienes y Servicios	\N	t	2026-06-05 03:44:31.144823	\N	\N	\N	\N	\N	8	Coordinación
24	Servicios Generales	\N	t	2026-06-05 03:44:31.144823	\N	\N	\N	\N	\N	8	Coordinación
25	Registro y Selección	\N	t	2026-06-05 03:44:31.144823	\N	\N	\N	\N	\N	5	Coordinación
26	Bienestar Social	\N	t	2026-06-05 03:44:31.144823	\N	\N	\N	\N	\N	5	Coordinación
27	Nómina	\N	t	2026-06-05 03:44:31.144823	\N	\N	\N	\N	\N	5	Coordinación
4	Departamento de informática	Departamento de Telecomunicaciones e Informática.	t	2026-04-17 15:11:35.123254	2026-06-05 04:14:16.072951	\N	\N	\N	\N	3	Unidad
28	Oficina de Información Turística (Aeropuerto)	Sede del Aeropuerto de Cumaná. Atiende al turista a su llegada; sus bienes se controlan aparte de la sede principal (B-24/B-65).	t	2026-08-05 15:30:24.276213	\N	\N	\N	\N	\N	6	Oficina
\.


--
-- Data for Name: feriados; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.feriados (id, fecha, nombre, recurrente, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by) FROM stdin;
1	2000-05-03	Cruz de Mayo	t	t	2026-06-21 14:22:02.581279	\N	\N	\N	\N	\N
2	2000-01-21	Santa Inés (Cumaná)	t	t	2026-06-21 14:22:02.581279	\N	\N	\N	\N	\N
3	2000-05-01	Día del Trabajador	t	t	2026-06-21 14:22:02.581279	\N	\N	\N	\N	\N
4	2000-04-19	Declaración de Independencia	t	t	2026-06-21 14:22:02.581279	\N	\N	\N	\N	\N
5	2000-07-05	Día de la Independencia	t	t	2026-06-21 14:22:02.581279	\N	\N	\N	\N	\N
6	2000-07-24	Natalicio del Libertador	t	t	2026-06-21 14:22:02.581279	\N	\N	\N	\N	\N
7	2000-12-25	Navidad	t	t	2026-06-21 14:22:02.581279	\N	\N	\N	\N	\N
8	2000-01-01	Año Nuevo	t	t	2026-06-21 14:22:02.581279	\N	\N	\N	\N	\N
9	2000-06-24	Batalla de Carabobo	t	t	2026-06-21 14:22:02.581279	\N	\N	\N	\N	\N
10	2000-12-24	Nochebuena	t	t	2026-06-21 14:22:02.581279	\N	\N	\N	\N	\N
11	2000-12-31	Fin de Año	t	t	2026-06-21 14:22:02.581279	\N	\N	\N	\N	\N
12	2000-10-12	Día de la Resistencia Indígena	t	t	2026-06-21 14:22:02.581279	\N	\N	\N	\N	\N
\.


--
-- Data for Name: horarios; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.horarios (id, nombre, hora_entrada, hora_salida, dias_laborales, descripcion, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by) FROM stdin;
2	Servicios Generales (8:00am–2:00pm)	08:00:00	14:00:00	L-V (rotación A/B)	Días alternos según grupo A/B	t	2026-06-05 03:54:51.487854	\N	\N	\N	\N	\N
3	OAC Vespertino (10:00am–2:00pm)	10:00:00	14:00:00	L-V	Recepción / OAC, sub-grupo 2	t	2026-06-05 03:54:51.487854	\N	\N	\N	\N	\N
4	OAC Matutino (7:00am–12:00pm)	07:00:00	12:00:00	L-V	Recepción / OAC, sub-grupo 1	t	2026-06-05 03:54:51.487854	\N	\N	\N	\N	\N
5	Estándar	08:00:00	14:00:00	L-V	Horario general vigente	t	2026-06-05 03:54:51.487854	2026-06-21 16:57:53.74893	\N	\N	\N	\N
6	Pasantes	10:30:00	12:00:00	L-V	.....	t	2026-07-09 14:23:24.556772	\N	\N	\N	\N	\N
\.


--
-- Data for Name: inventario_dotacion; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.inventario_dotacion (id, id_categoria, unidades_por_empleado, observaciones, is_active, created_at, updated_at, created_by, updated_by) FROM stdin;
1	4	0.50	Aproximadamente un punto telefónico por cada dos empleados	t	2026-08-05 15:30:24.276213	\N	\N	\N
2	8	1.00	Un equipo por empleado	t	2026-08-05 15:30:24.276213	\N	\N	\N
3	13	2.00	Al menos una silla y un puesto de trabajo por empleado	t	2026-08-05 15:30:24.276213	\N	\N	\N
\.


--
--
-- =====================================================================
-- USUARIO ADMINISTRADOR DE ARRANQUE
-- =====================================================================
--
-- Sin esto no hay forma de entrar al sistema recién instalado:
-- `usuarios.id_empleado` es NOT NULL, así que todo usuario necesita
-- una persona y un empleado detrás.
--
--     Usuario:    admin
--     Contraseña: Sigtur2026
--
--   ###############################################################
--   #  CAMBIAR LA CONTRASEÑA EN EL PRIMER INGRESO                 #
--   #  (Perfil -> Cambiar contraseña).                            #
--   #  Esta clave es pública: está versionada en el repositorio.  #
--   ###############################################################
--
-- El empleado creado aquí es un registro técnico de arranque, no una
-- persona real. Una vez cargado el personal verdadero y creado un
-- administrador nominal, conviene desactivarlo.
--
-- Va en este punto del archivo a propósito: necesita que `cargos` y
-- `departamentos` ya estén sembrados, y debe existir antes de
-- `municipio`/`parroquia`, cuyas columnas de auditoría son NOT NULL y
-- apuntan a este usuario (id 1).
--

DO $bootstrap$
DECLARE
    v_id_cargo  integer;
    v_id_depto  integer;
BEGIN
    IF EXISTS (SELECT 1 FROM public.usuarios WHERE id = 1 OR username = 'admin') THEN
        RAISE NOTICE 'El administrador de arranque ya existe; no se recrea.';
        RETURN;
    END IF;

    -- Se ancla al catálogo sembrado sin depender de IDs fijos.
    SELECT id INTO v_id_depto FROM public.departamentos
        WHERE is_active AND tipo_unidad = 'Presidencia' ORDER BY id LIMIT 1;
    IF v_id_depto IS NULL THEN
        SELECT id INTO v_id_depto FROM public.departamentos
            WHERE is_active ORDER BY id LIMIT 1;
    END IF;

    SELECT id INTO v_id_cargo FROM public.cargos
        WHERE is_active AND nivel_jerarquico = 'Presidencia' ORDER BY id LIMIT 1;
    IF v_id_cargo IS NULL THEN
        SELECT id INTO v_id_cargo FROM public.cargos
            WHERE is_active ORDER BY id LIMIT 1;
    END IF;

    IF v_id_cargo IS NULL OR v_id_depto IS NULL THEN
        RAISE EXCEPTION 'Faltan cargos o departamentos sembrados; '
                        'no se puede crear el administrador de arranque.';
    END IF;

    INSERT INTO public.personas (id, nombre, apellido, is_active, created_at)
    VALUES (1, 'Administrador', 'del Sistema', TRUE, CURRENT_TIMESTAMP);

    INSERT INTO public.empleados
        (id, id_persona, id_cargo, id_departamento, nro_expediente,
         tipo_contrato, institucion_origen, fecha_ingreso, is_active, created_at)
    VALUES
        (1, 1, v_id_cargo, v_id_depto, 'EXP-0001',
         'Fijo', 'IMATUR', CURRENT_DATE, TRUE, CURRENT_TIMESTAMP);

    -- bcrypt de 'Sigtur2026'
    INSERT INTO public.usuarios
        (id, id_empleado, id_rol, username, password, is_active, created_at)
    VALUES
        (1, 1, 1, 'admin',
         '$2y$10$vrNAvVFuZQ0OXyh/nmW9We6DuiGRZwosiiY.IUpj/WX.LcE3QkZym',
         TRUE, CURRENT_TIMESTAMP);

    RAISE NOTICE 'Administrador de arranque creado -> admin / Sigtur2026 '
                 '(CAMBIAR LA CONTRASEÑA).';
END
$bootstrap$;



-- Data for Name: municipio; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.municipio (id, nombre, codigo_postal, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by) FROM stdin;
2	Sucre	6101	t	2026-04-26 16:51:36.702899	2026-04-26 16:51:36.702899	\N	1	1	\N
3	Bolivar	6107	t	2026-04-28 03:22:54.098579	2026-04-28 03:22:54.098579	\N	1	1	\N
\.


--
-- Data for Name: parroquia; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.parroquia (id, nombre, id_municipio, is_active, create_by, update_by, delete_by, create_at, update_at, delete_at) FROM stdin;
1	Altagracia	2	t	1	1	\N	2026-04-26 16:52:13.285237	2026-04-26 16:52:13.285237	\N
2	Santa Ines	2	t	1	1	\N	2026-04-26 16:53:21.016217	2026-04-26 16:53:21.016217	\N
3	Valentin Valiente	2	t	1	1	\N	2026-04-26 16:54:36.814184	2026-04-26 16:54:36.814184	\N
4	Ayacucho	2	t	1	1	\N	2026-04-26 16:55:00.340542	2026-04-26 16:55:00.340542	\N
5	San Juan	2	t	1	1	\N	2026-04-26 16:55:32.137902	2026-04-26 16:55:32.137902	\N
6	Raul Leoni	2	t	1	1	\N	2026-04-26 16:56:06.355341	2026-04-26 16:56:06.355341	\N
7	Gran Mariscal	2	t	1	1	\N	2026-04-26 16:56:30.227526	2026-04-26 16:56:30.227526	\N
\.


--
-- Data for Name: permisos_rol; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.permisos_rol (id, id_rol, modulo, created_at, created_by) FROM stdin;
1	1	*	2026-05-26 02:22:50.069328	\N
11	3	DashboardController	2026-05-26 02:22:50.078989	\N
12	3	RutasController	2026-05-26 02:22:50.078989	\N
14	3	TalleresController	2026-05-26 02:22:50.078989	\N
15	3	UbicacionesformacionController	2026-05-26 02:22:50.078989	\N
16	3	PasantesController	2026-05-26 02:22:50.078989	\N
17	3	VisitantesController	2026-05-26 02:22:50.078989	\N
19	3	ReportesController	2026-05-26 02:22:50.078989	\N
20	4	DashboardController	2026-05-26 02:22:50.079374	\N
21	4	InventarioController	2026-05-26 02:22:50.079374	\N
22	4	CategoriasController	2026-05-26 02:22:50.079374	\N
23	4	UbicacionesController	2026-05-26 02:22:50.079374	\N
24	4	ActividadesinventarioController	2026-05-26 02:22:50.079374	\N
25	4	ReportesController	2026-05-26 02:22:50.079374	\N
30	6	ReportesController	2026-05-26 02:33:46.506516	\N
31	6	DashboardController	2026-05-26 02:33:46.506516	\N
37	5	AsistenciasController	2026-05-26 16:29:21.945685	\N
38	5	VisitantesController	2026-05-26 16:29:21.945685	\N
40	5	DashboardController	2026-05-26 16:29:21.945685	\N
41	2	ReportesController	2026-05-26 20:54:16.748618	\N
42	2	ConfigController	2026-05-26 20:54:16.748618	\N
43	2	EmpleadosController	2026-05-26 20:54:16.748618	\N
44	2	CargosController	2026-05-26 20:54:16.748618	\N
45	2	DepartamentosController	2026-05-26 20:54:16.748618	\N
46	2	AsistenciasController	2026-05-26 20:54:16.748618	\N
47	2	VisitantesController	2026-05-26 20:54:16.748618	\N
49	2	PasantesController	2026-05-26 20:54:16.748618	\N
50	2	UsuariosController	2026-05-26 20:54:16.748618	\N
51	2	DashboardController	2026-05-26 20:54:16.748618	\N
54	6	VisitantesController	2026-06-01 04:49:57.27028	\N
55	2	HorariosController	2026-06-05 03:54:51.487854	\N
56	2	AmonestacionesController	2026-06-05 09:19:44.234118	\N
57	2	PermisosController	2026-06-05 12:07:06.536181	\N
58	2	VacacionesController	2026-06-21 14:22:02.626529	\N
59	2	NominaController	2026-07-16 12:43:18.158763	\N
\.


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.roles (id, nombre, descripcion, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by) FROM stdin;
2	RRHH	Gestión de personal y asistencia	t	2026-04-12 14:15:24.492607	\N	\N	\N	\N	\N
3	Turismo	Gestión de rutas y formación	t	2026-04-12 14:15:24.492607	\N	\N	\N	\N	\N
5	Recepción	Registro de visitantes, visitas y marcaje de asistencias. Sin acceso a módulos de gestión.	t	2026-05-20 10:35:25.876512	\N	\N	\N	\N	\N
4	Inventario	Gestión de bienes institucionales	t	2026-04-12 14:15:24.492607	2026-05-26 02:31:55.886056	\N	\N	\N	\N
1	Administrador	Acceso total al sistema	t	2026-04-12 14:15:24.492607	2026-05-26 15:14:08.526225	\N	\N	\N	\N
6	Solo Lectura	Prueba para verificar el insert del rol y otrogar permisos	t	2026-05-26 02:33:32.316047	2026-05-26 20:53:54.119892	\N	\N	\N	\N
\.


--
-- Name: actividad_inventario_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.actividad_inventario_id_seq', 16, true);


--
-- Name: actividades_ruta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.actividades_ruta_id_seq', 1, true);


--
-- Name: alertas_vistas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.alertas_vistas_id_seq', 9, true);


--
-- Name: amonestaciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.amonestaciones_id_seq', 5, true);


--
-- Name: asistencias_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.asistencias_id_seq', 6, true);


--
-- Name: audit_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.audit_logs_id_seq', 241, true);


--
-- Name: bono_vacacional_detalle_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.bono_vacacional_detalle_id_seq', 9, true);


--
-- Name: bono_vacacional_periodos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.bono_vacacional_periodos_id_seq', 3, true);


--
-- Name: carga_familiar_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.carga_familiar_id_seq', 8, true);


--
-- Name: cargos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.cargos_id_seq', 7, true);


--
-- Name: categorias_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.categorias_id_seq', 13, true);


--
-- Name: configuracion_sistema_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.configuracion_sistema_id_seq', 44, true);


--
-- Name: constancias_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.constancias_id_seq', 18, true);


--
-- Name: cursos_realizados_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.cursos_realizados_id_seq', 1, false);


--
-- Name: departamentos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.departamentos_id_seq', 28, true);


--
-- Name: empleado_salarios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.empleado_salarios_id_seq', 7, true);


--
-- Name: empleado_traslados_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.empleado_traslados_id_seq', 1, false);


--
-- Name: empleados_egresos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.empleados_egresos_id_seq', 5, true);


--
-- Name: empleados_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.empleados_id_seq', 12, true);


--
-- Name: expediente_documentos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.expediente_documentos_id_seq', 4, true);


--
-- Name: experiencia_laboral_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.experiencia_laboral_id_seq', 1, false);


--
-- Name: faltas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.faltas_id_seq', 3, true);


--
-- Name: feriados_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.feriados_id_seq', 12, true);


--
-- Name: horarios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.horarios_id_seq', 6, true);


--
-- Name: inventario_consolidados_bm1_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.inventario_consolidados_bm1_id_seq', 3, true);


--
-- Name: inventario_conteo_detalle_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.inventario_conteo_detalle_id_seq', 4, true);


--
-- Name: inventario_conteos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.inventario_conteos_id_seq', 4, true);


--
-- Name: inventario_documentos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.inventario_documentos_id_seq', 6, true);


--
-- Name: inventario_dotacion_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.inventario_dotacion_id_seq', 3, true);


--
-- Name: inventario_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.inventario_id_seq', 17, true);


--
-- Name: inventario_mantenimiento_plan_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.inventario_mantenimiento_plan_id_seq', 4, true);


--
-- Name: inventario_mantenimientos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.inventario_mantenimientos_id_seq', 5, true);


--
-- Name: municipio_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.municipio_id_seq', 3, true);


--
-- Name: oficios_emitidos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.oficios_emitidos_id_seq', 3, true);


--
-- Name: parroquia_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.parroquia_id_seq', 7, true);


--
-- Name: participantes_ruta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.participantes_ruta_id_seq', 4, true);


--
-- Name: participantes_taller_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.participantes_taller_id_seq', 2, true);


--
-- Name: pasante_documentos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.pasante_documentos_id_seq', 7, true);


--
-- Name: pasantes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.pasantes_id_seq', 2, true);


--
-- Name: password_resets_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.password_resets_id_seq', 3, true);


--
-- Name: permisos_laborales_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.permisos_laborales_id_seq', 4, true);


--
-- Name: permisos_rol_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.permisos_rol_id_seq', 59, true);


--
-- Name: personas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.personas_id_seq', 16, true);


--
-- Name: puntos_ruta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.puntos_ruta_id_seq', 2, true);


--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.roles_id_seq', 6, true);


--
-- Name: ruta_informes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.ruta_informes_id_seq', 1, false);


--
-- Name: rutas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.rutas_id_seq', 2, true);


--
-- Name: taller_evidencias_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.taller_evidencias_id_seq', 1, false);


--
-- Name: taller_informes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.taller_informes_id_seq', 2, true);


--
-- Name: talleres_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.talleres_id_seq', 9, true);


--
-- Name: ubicaciones_formacion_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.ubicaciones_formacion_id_seq', 2, true);


--
-- Name: ubicaciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.ubicaciones_id_seq', 26, true);


--
-- Name: usuarios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.usuarios_id_seq', 3, true);


--
-- Name: vacaciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.vacaciones_id_seq', 1, true);


--
-- Name: visitantes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.visitantes_id_seq', 4, true);


--
-- Name: visitas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.visitas_id_seq', 3, true);


--
-- Name: actividad_inventario actividad_inventario_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actividad_inventario
    ADD CONSTRAINT actividad_inventario_pkey PRIMARY KEY (id);


--
-- Name: actividades_ruta actividades_ruta_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actividades_ruta
    ADD CONSTRAINT actividades_ruta_pkey PRIMARY KEY (id);


--
-- Name: alertas_vistas alertas_vistas_id_usuario_clave_alerta_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.alertas_vistas
    ADD CONSTRAINT alertas_vistas_id_usuario_clave_alerta_key UNIQUE (id_usuario, clave_alerta);


--
-- Name: alertas_vistas alertas_vistas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.alertas_vistas
    ADD CONSTRAINT alertas_vistas_pkey PRIMARY KEY (id);


--
-- Name: amonestaciones amonestaciones_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.amonestaciones
    ADD CONSTRAINT amonestaciones_pkey PRIMARY KEY (id);


--
-- Name: asistencias asistencias_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asistencias
    ADD CONSTRAINT asistencias_pkey PRIMARY KEY (id);


--
-- Name: audit_logs audit_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_pkey PRIMARY KEY (id);


--
-- Name: bono_vacacional_detalle bono_vacacional_detalle_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bono_vacacional_detalle
    ADD CONSTRAINT bono_vacacional_detalle_pkey PRIMARY KEY (id);


--
-- Name: bono_vacacional_periodos bono_vacacional_periodos_periodo_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bono_vacacional_periodos
    ADD CONSTRAINT bono_vacacional_periodos_periodo_key UNIQUE (periodo);


--
-- Name: bono_vacacional_periodos bono_vacacional_periodos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bono_vacacional_periodos
    ADD CONSTRAINT bono_vacacional_periodos_pkey PRIMARY KEY (id);


--
-- Name: carga_familiar carga_familiar_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.carga_familiar
    ADD CONSTRAINT carga_familiar_pkey PRIMARY KEY (id);


--
-- Name: cargos cargos_nombre_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cargos
    ADD CONSTRAINT cargos_nombre_key UNIQUE (nombre);


--
-- Name: cargos cargos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cargos
    ADD CONSTRAINT cargos_pkey PRIMARY KEY (id);


--
-- Name: categorias categorias_nombre_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categorias
    ADD CONSTRAINT categorias_nombre_key UNIQUE (nombre);


--
-- Name: categorias categorias_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categorias
    ADD CONSTRAINT categorias_pkey PRIMARY KEY (id);


--
-- Name: configuracion_sistema configuracion_sistema_clave_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.configuracion_sistema
    ADD CONSTRAINT configuracion_sistema_clave_key UNIQUE (clave);


--
-- Name: configuracion_sistema configuracion_sistema_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.configuracion_sistema
    ADD CONSTRAINT configuracion_sistema_pkey PRIMARY KEY (id);


--
-- Name: constancias constancias_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.constancias
    ADD CONSTRAINT constancias_pkey PRIMARY KEY (id);


--
-- Name: cursos_realizados cursos_realizados_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cursos_realizados
    ADD CONSTRAINT cursos_realizados_pkey PRIMARY KEY (id);


--
-- Name: departamentos departamentos_nombre_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.departamentos
    ADD CONSTRAINT departamentos_nombre_key UNIQUE (nombre);


--
-- Name: departamentos departamentos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.departamentos
    ADD CONSTRAINT departamentos_pkey PRIMARY KEY (id);


--
-- Name: empleado_salarios empleado_salarios_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.empleado_salarios
    ADD CONSTRAINT empleado_salarios_pkey PRIMARY KEY (id);


--
-- Name: empleado_traslados empleado_traslados_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.empleado_traslados
    ADD CONSTRAINT empleado_traslados_pkey PRIMARY KEY (id);


--
-- Name: empleados_egresos empleados_egresos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.empleados_egresos
    ADD CONSTRAINT empleados_egresos_pkey PRIMARY KEY (id);


--
-- Name: empleados empleados_id_persona_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT empleados_id_persona_key UNIQUE (id_persona);


--
-- Name: empleados empleados_nro_expediente_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT empleados_nro_expediente_key UNIQUE (nro_expediente);


--
-- Name: empleados empleados_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT empleados_pkey PRIMARY KEY (id);


--
-- Name: expediente_documentos expediente_documentos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.expediente_documentos
    ADD CONSTRAINT expediente_documentos_pkey PRIMARY KEY (id);


--
-- Name: experiencia_laboral experiencia_laboral_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.experiencia_laboral
    ADD CONSTRAINT experiencia_laboral_pkey PRIMARY KEY (id);


--
-- Name: faltas faltas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.faltas
    ADD CONSTRAINT faltas_pkey PRIMARY KEY (id);


--
-- Name: feriados feriados_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.feriados
    ADD CONSTRAINT feriados_pkey PRIMARY KEY (id);


--
-- Name: horarios horarios_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.horarios
    ADD CONSTRAINT horarios_pkey PRIMARY KEY (id);


--
-- Name: inventario inventario_codigo_bn_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario
    ADD CONSTRAINT inventario_codigo_bn_key UNIQUE (codigo_bn);


--
-- Name: inventario_consolidados_bm1 inventario_consolidados_bm1_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario_consolidados_bm1
    ADD CONSTRAINT inventario_consolidados_bm1_pkey PRIMARY KEY (id);


--
-- Name: inventario_conteo_detalle inventario_conteo_detalle_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario_conteo_detalle
    ADD CONSTRAINT inventario_conteo_detalle_pkey PRIMARY KEY (id);


--
-- Name: inventario_conteos inventario_conteos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario_conteos
    ADD CONSTRAINT inventario_conteos_pkey PRIMARY KEY (id);


--
-- Name: inventario_documentos inventario_documentos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario_documentos
    ADD CONSTRAINT inventario_documentos_pkey PRIMARY KEY (id);


--
-- Name: inventario_dotacion inventario_dotacion_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario_dotacion
    ADD CONSTRAINT inventario_dotacion_pkey PRIMARY KEY (id);


--
-- Name: inventario_mantenimiento_plan inventario_mantenimiento_plan_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario_mantenimiento_plan
    ADD CONSTRAINT inventario_mantenimiento_plan_pkey PRIMARY KEY (id);


--
-- Name: inventario_mantenimientos inventario_mantenimientos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario_mantenimientos
    ADD CONSTRAINT inventario_mantenimientos_pkey PRIMARY KEY (id);


--
-- Name: inventario inventario_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario
    ADD CONSTRAINT inventario_pkey PRIMARY KEY (id);


--
-- Name: inventario inventario_serial_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario
    ADD CONSTRAINT inventario_serial_key UNIQUE (serial);


--
-- Name: municipio municipio_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.municipio
    ADD CONSTRAINT municipio_pkey PRIMARY KEY (id);


--
-- Name: oficios_emitidos oficios_emitidos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.oficios_emitidos
    ADD CONSTRAINT oficios_emitidos_pkey PRIMARY KEY (id);


--
-- Name: parroquia parroquia_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.parroquia
    ADD CONSTRAINT parroquia_pkey PRIMARY KEY (id);


--
-- Name: participantes_ruta participantes_ruta_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.participantes_ruta
    ADD CONSTRAINT participantes_ruta_pkey PRIMARY KEY (id);


--
-- Name: participantes_taller participantes_taller_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.participantes_taller
    ADD CONSTRAINT participantes_taller_pkey PRIMARY KEY (id);


--
-- Name: pasante_documentos pasante_documentos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pasante_documentos
    ADD CONSTRAINT pasante_documentos_pkey PRIMARY KEY (id);


--
-- Name: pasantes pasantes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pasantes
    ADD CONSTRAINT pasantes_pkey PRIMARY KEY (id);


--
-- Name: password_resets password_resets_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_resets
    ADD CONSTRAINT password_resets_pkey PRIMARY KEY (id);


--
-- Name: permisos_laborales permisos_laborales_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permisos_laborales
    ADD CONSTRAINT permisos_laborales_pkey PRIMARY KEY (id);


--
-- Name: permisos_rol permisos_rol_id_rol_modulo_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permisos_rol
    ADD CONSTRAINT permisos_rol_id_rol_modulo_key UNIQUE (id_rol, modulo);


--
-- Name: permisos_rol permisos_rol_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permisos_rol
    ADD CONSTRAINT permisos_rol_pkey PRIMARY KEY (id);


--
-- Name: personas personas_cedula_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personas
    ADD CONSTRAINT personas_cedula_key UNIQUE (cedula);


--
-- Name: personas personas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personas
    ADD CONSTRAINT personas_pkey PRIMARY KEY (id);


--
-- Name: puntos_ruta puntos_ruta_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.puntos_ruta
    ADD CONSTRAINT puntos_ruta_pkey PRIMARY KEY (id);


--
-- Name: roles roles_nombre_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_nombre_key UNIQUE (nombre);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: ruta_informes ruta_informes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ruta_informes
    ADD CONSTRAINT ruta_informes_pkey PRIMARY KEY (id);


--
-- Name: rutas rutas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rutas
    ADD CONSTRAINT rutas_pkey PRIMARY KEY (id);


--
-- Name: taller_evidencias taller_evidencias_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.taller_evidencias
    ADD CONSTRAINT taller_evidencias_pkey PRIMARY KEY (id);


--
-- Name: taller_informes taller_informes_id_taller_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.taller_informes
    ADD CONSTRAINT taller_informes_id_taller_key UNIQUE (id_taller);


--
-- Name: taller_informes taller_informes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.taller_informes
    ADD CONSTRAINT taller_informes_pkey PRIMARY KEY (id);


--
-- Name: talleres talleres_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.talleres
    ADD CONSTRAINT talleres_pkey PRIMARY KEY (id);


--
-- Name: ubicaciones_formacion ubicaciones_formacion_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ubicaciones_formacion
    ADD CONSTRAINT ubicaciones_formacion_pkey PRIMARY KEY (id);


--
-- Name: ubicaciones ubicaciones_nombre_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ubicaciones
    ADD CONSTRAINT ubicaciones_nombre_key UNIQUE (nombre);


--
-- Name: ubicaciones ubicaciones_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ubicaciones
    ADD CONSTRAINT ubicaciones_pkey PRIMARY KEY (id);


--
-- Name: participantes_taller uq_participante_taller; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.participantes_taller
    ADD CONSTRAINT uq_participante_taller UNIQUE (id_taller, id_persona);


--
-- Name: usuarios usuarios_id_empleado_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_id_empleado_key UNIQUE (id_empleado);


--
-- Name: usuarios usuarios_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);


--
-- Name: usuarios usuarios_username_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_username_key UNIQUE (username);


--
-- Name: vacaciones vacaciones_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vacaciones
    ADD CONSTRAINT vacaciones_pkey PRIMARY KEY (id);


--
-- Name: visitantes visitantes_cedula_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.visitantes
    ADD CONSTRAINT visitantes_cedula_key UNIQUE (cedula);


--
-- Name: visitantes visitantes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.visitantes
    ADD CONSTRAINT visitantes_pkey PRIMARY KEY (id);


--
-- Name: visitas visitas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.visitas
    ADD CONSTRAINT visitas_pkey PRIMARY KEY (id);


--
-- Name: idx_act_inv_fecha; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_act_inv_fecha ON public.actividad_inventario USING btree (fecha);


--
-- Name: idx_act_inv_item_fecha; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_act_inv_item_fecha ON public.actividad_inventario USING btree (id_inventario, fecha DESC) WHERE (is_active = true);


--
-- Name: idx_act_ruta_fecha; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_act_ruta_fecha ON public.actividades_ruta USING btree (fecha);


--
-- Name: idx_actinv_tipo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_actinv_tipo ON public.actividad_inventario USING btree (tipo_movimiento);


--
-- Name: idx_alertas_vistas_usuario; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_alertas_vistas_usuario ON public.alertas_vistas USING btree (id_usuario);


--
-- Name: idx_amonestaciones_empleado; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_amonestaciones_empleado ON public.amonestaciones USING btree (id_empleado) WHERE (is_active = true);


--
-- Name: idx_asistencias_empleado_fecha; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_asistencias_empleado_fecha ON public.asistencias USING btree (id_empleado, fecha);


--
-- Name: idx_asistencias_fecha; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_asistencias_fecha ON public.asistencias USING btree (fecha);


--
-- Name: idx_bono_vac_detalle_empleado; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_bono_vac_detalle_empleado ON public.bono_vacacional_detalle USING btree (id_empleado);


--
-- Name: idx_bono_vac_detalle_periodo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_bono_vac_detalle_periodo ON public.bono_vacacional_detalle USING btree (id_periodo);


--
-- Name: idx_carga_familiar_persona; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_carga_familiar_persona ON public.carga_familiar USING btree (id_persona) WHERE (is_active = true);


--
-- Name: idx_constancias_empleado; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_constancias_empleado ON public.constancias USING btree (id_empleado) WHERE (is_active = true);


--
-- Name: idx_cursos_realizados_persona; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_cursos_realizados_persona ON public.cursos_realizados USING btree (id_persona) WHERE (is_active = true);


--
-- Name: idx_emp_egresos_empleado; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_emp_egresos_empleado ON public.empleados_egresos USING btree (id_empleado);


--
-- Name: idx_empleado_salarios_empleado; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_empleado_salarios_empleado ON public.empleado_salarios USING btree (id_empleado);


--
-- Name: idx_expediente_doc_empleado; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_expediente_doc_empleado ON public.expediente_documentos USING btree (id_empleado) WHERE (is_active = true);


--
-- Name: idx_experiencia_laboral_persona; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_experiencia_laboral_persona ON public.experiencia_laboral USING btree (id_persona) WHERE (is_active = true);


--
-- Name: idx_faltas_empleado; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_faltas_empleado ON public.faltas USING btree (id_empleado) WHERE (is_active = true);


--
-- Name: idx_feriados_mesdia; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_feriados_mesdia ON public.feriados USING btree (EXTRACT(month FROM fecha), EXTRACT(day FROM fecha));


--
-- Name: idx_inv_conteo_det; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_inv_conteo_det ON public.inventario_conteo_detalle USING btree (id_conteo);


--
-- Name: idx_inv_doc_bien; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_inv_doc_bien ON public.inventario_documentos USING btree (id_inventario);


--
-- Name: idx_inv_mant_bien; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_inv_mant_bien ON public.inventario_mantenimientos USING btree (id_inventario);


--
-- Name: idx_inv_plan_proxima; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_inv_plan_proxima ON public.inventario_mantenimiento_plan USING btree (proxima_fecha);


--
-- Name: idx_inventario_codigo_bn; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_inventario_codigo_bn ON public.inventario USING btree (codigo_bn);


--
-- Name: idx_inventario_estatus; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_inventario_estatus ON public.inventario USING btree (estatus);


--
-- Name: idx_inventario_garantia; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_inventario_garantia ON public.inventario USING btree (garantia_vence) WHERE (garantia_vence IS NOT NULL);


--
-- Name: idx_inventario_por_retirar; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_inventario_por_retirar ON public.inventario USING btree (retirado_alcaldia) WHERE (((estatus)::text = 'Dado de baja'::text) AND (retirado_alcaldia = false));


--
-- Name: idx_logs_fecha; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_logs_fecha ON public.audit_logs USING btree (fecha);


--
-- Name: idx_logs_tabla; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_logs_tabla ON public.audit_logs USING btree (tabla_afectada);


--
-- Name: idx_logs_tabla_operacion; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_logs_tabla_operacion ON public.audit_logs USING btree (tabla_afectada, operacion);


--
-- Name: idx_parroquia_municipio; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_parroquia_municipio ON public.parroquia USING btree (id_municipio);


--
-- Name: idx_part_ruta_ruta; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_part_ruta_ruta ON public.participantes_ruta USING btree (id_ruta) WHERE (is_active = true);


--
-- Name: idx_pasantes_persona; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_pasantes_persona ON public.pasantes USING btree (id_persona);


--
-- Name: idx_password_resets_token; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_password_resets_token ON public.password_resets USING btree (token_hash);


--
-- Name: idx_password_resets_usuario; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_password_resets_usuario ON public.password_resets USING btree (id_usuario);


--
-- Name: idx_permisos_empleado; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_permisos_empleado ON public.permisos_laborales USING btree (id_empleado);


--
-- Name: idx_permisos_fechas; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_permisos_fechas ON public.permisos_laborales USING btree (fecha_inicio, fecha_fin);


--
-- Name: idx_permisos_rol_rol; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_permisos_rol_rol ON public.permisos_rol USING btree (id_rol);


--
-- Name: idx_personas_cedula; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_personas_cedula ON public.personas USING btree (cedula);


--
-- Name: idx_personas_parroquia; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_personas_parroquia ON public.personas USING btree (parroquia_id) WHERE (is_active = true);


--
-- Name: idx_rutas_estado; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_rutas_estado ON public.rutas USING btree (estado);


--
-- Name: idx_taller_evidencias_taller; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_taller_evidencias_taller ON public.taller_evidencias USING btree (id_taller);


--
-- Name: idx_talleres_estado; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_talleres_estado ON public.talleres USING btree (estado);


--
-- Name: idx_talleres_fecha; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_talleres_fecha ON public.talleres USING btree (fecha_inicio);


--
-- Name: idx_traslados_empleado; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_traslados_empleado ON public.empleado_traslados USING btree (id_empleado);


--
-- Name: idx_usuarios_username; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuarios_username ON public.usuarios USING btree (username);


--
-- Name: idx_vacaciones_anio; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_vacaciones_anio ON public.vacaciones USING btree (anio);


--
-- Name: idx_vacaciones_empleado; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_vacaciones_empleado ON public.vacaciones USING btree (id_empleado);


--
-- Name: idx_visitantes_cedula; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_visitantes_cedula ON public.visitantes USING btree (cedula);


--
-- Name: idx_visitas_entrada; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_visitas_entrada ON public.visitas USING btree (hora_entrada);


--
-- Name: idx_visitas_visitante; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_visitas_visitante ON public.visitas USING btree (id_visitante);


--
-- Name: uq_emp_egreso_abierto; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uq_emp_egreso_abierto ON public.empleados_egresos USING btree (id_empleado) WHERE (fecha_reingreso IS NULL);


--
-- Name: uq_inv_conteo_abierto; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uq_inv_conteo_abierto ON public.inventario_conteos USING btree (estado) WHERE (((estado)::text = 'Abierto'::text) AND (is_active = true));


--
-- Name: uq_inv_conteo_bien; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uq_inv_conteo_bien ON public.inventario_conteo_detalle USING btree (id_conteo, id_inventario);


--
-- Name: uq_inv_dotacion_categoria; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uq_inv_dotacion_categoria ON public.inventario_dotacion USING btree (id_categoria) WHERE (is_active = true);


--
-- Name: uq_inv_mant_abierto; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uq_inv_mant_abierto ON public.inventario_mantenimientos USING btree (id_inventario) WHERE ((fecha_retorno IS NULL) AND (is_active = true));


--
-- Name: uq_inv_plan_bien; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uq_inv_plan_bien ON public.inventario_mantenimiento_plan USING btree (id_inventario) WHERE (is_active = true);


--
-- Name: uq_puntos_ruta_orden; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uq_puntos_ruta_orden ON public.puntos_ruta USING btree (id_ruta, orden) WHERE (is_active = true);


--
-- Name: INDEX uq_puntos_ruta_orden; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON INDEX public.uq_puntos_ruta_orden IS 'Garantiza que no existan dos paradas con el mismo orden dentro de una ruta activa';


--
-- Name: uq_ruta_informes_ruta; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uq_ruta_informes_ruta ON public.ruta_informes USING btree (id_ruta);


--
-- Name: alertas_vistas alertas_vistas_id_usuario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.alertas_vistas
    ADD CONSTRAINT alertas_vistas_id_usuario_fkey FOREIGN KEY (id_usuario) REFERENCES public.usuarios(id) ON DELETE CASCADE;


--
-- Name: amonestaciones amonestaciones_id_empleado_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.amonestaciones
    ADD CONSTRAINT amonestaciones_id_empleado_fkey FOREIGN KEY (id_empleado) REFERENCES public.empleados(id) ON DELETE CASCADE;


--
-- Name: bono_vacacional_detalle bono_vacacional_detalle_id_empleado_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bono_vacacional_detalle
    ADD CONSTRAINT bono_vacacional_detalle_id_empleado_fkey FOREIGN KEY (id_empleado) REFERENCES public.empleados(id) ON DELETE RESTRICT;


--
-- Name: bono_vacacional_detalle bono_vacacional_detalle_id_periodo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bono_vacacional_detalle
    ADD CONSTRAINT bono_vacacional_detalle_id_periodo_fkey FOREIGN KEY (id_periodo) REFERENCES public.bono_vacacional_periodos(id) ON DELETE CASCADE;


--
-- Name: carga_familiar carga_familiar_id_persona_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.carga_familiar
    ADD CONSTRAINT carga_familiar_id_persona_fkey FOREIGN KEY (id_persona) REFERENCES public.personas(id) ON DELETE RESTRICT;


--
-- Name: constancias constancias_id_empleado_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.constancias
    ADD CONSTRAINT constancias_id_empleado_fkey FOREIGN KEY (id_empleado) REFERENCES public.empleados(id) ON DELETE CASCADE;


--
-- Name: cursos_realizados cursos_realizados_id_persona_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cursos_realizados
    ADD CONSTRAINT cursos_realizados_id_persona_fkey FOREIGN KEY (id_persona) REFERENCES public.personas(id) ON DELETE RESTRICT;


--
-- Name: departamentos departamentos_id_padre_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.departamentos
    ADD CONSTRAINT departamentos_id_padre_fkey FOREIGN KEY (id_padre) REFERENCES public.departamentos(id) ON DELETE SET NULL;


--
-- Name: empleado_salarios empleado_salarios_id_empleado_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.empleado_salarios
    ADD CONSTRAINT empleado_salarios_id_empleado_fkey FOREIGN KEY (id_empleado) REFERENCES public.empleados(id) ON DELETE CASCADE;


--
-- Name: empleado_traslados empleado_traslados_id_empleado_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.empleado_traslados
    ADD CONSTRAINT empleado_traslados_id_empleado_fkey FOREIGN KEY (id_empleado) REFERENCES public.empleados(id) ON DELETE CASCADE;


--
-- Name: empleados_egresos empleados_egresos_id_empleado_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.empleados_egresos
    ADD CONSTRAINT empleados_egresos_id_empleado_fkey FOREIGN KEY (id_empleado) REFERENCES public.empleados(id) ON DELETE CASCADE;


--
-- Name: expediente_documentos expediente_documentos_id_empleado_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.expediente_documentos
    ADD CONSTRAINT expediente_documentos_id_empleado_fkey FOREIGN KEY (id_empleado) REFERENCES public.empleados(id) ON DELETE CASCADE;


--
-- Name: experiencia_laboral experiencia_laboral_id_persona_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.experiencia_laboral
    ADD CONSTRAINT experiencia_laboral_id_persona_fkey FOREIGN KEY (id_persona) REFERENCES public.personas(id) ON DELETE RESTRICT;


--
-- Name: faltas faltas_id_empleado_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.faltas
    ADD CONSTRAINT faltas_id_empleado_fkey FOREIGN KEY (id_empleado) REFERENCES public.empleados(id) ON DELETE CASCADE;


--
-- Name: actividad_inventario fk_act_inv_emp; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actividad_inventario
    ADD CONSTRAINT fk_act_inv_emp FOREIGN KEY (id_empleado_responsable) REFERENCES public.empleados(id) ON DELETE SET NULL;


--
-- Name: actividad_inventario fk_act_inv_item; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actividad_inventario
    ADD CONSTRAINT fk_act_inv_item FOREIGN KEY (id_inventario) REFERENCES public.inventario(id) ON DELETE RESTRICT;


--
-- Name: actividades_ruta fk_act_ruta; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actividades_ruta
    ADD CONSTRAINT fk_act_ruta FOREIGN KEY (id_ruta) REFERENCES public.rutas(id) ON DELETE CASCADE;


--
-- Name: actividades_ruta fk_act_ruta_emp; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actividades_ruta
    ADD CONSTRAINT fk_act_ruta_emp FOREIGN KEY (id_empleado_responsable) REFERENCES public.empleados(id) ON DELETE SET NULL;


--
-- Name: actividad_inventario fk_actinv_autorizado; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actividad_inventario
    ADD CONSTRAINT fk_actinv_autorizado FOREIGN KEY (autorizado_por) REFERENCES public.empleados(id) ON DELETE SET NULL;


--
-- Name: actividad_inventario fk_actinv_ubic_destino; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actividad_inventario
    ADD CONSTRAINT fk_actinv_ubic_destino FOREIGN KEY (id_ubicacion_destino) REFERENCES public.ubicaciones(id) ON DELETE SET NULL;


--
-- Name: actividad_inventario fk_actinv_ubic_origen; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actividad_inventario
    ADD CONSTRAINT fk_actinv_ubic_origen FOREIGN KEY (id_ubicacion_origen) REFERENCES public.ubicaciones(id) ON DELETE SET NULL;


--
-- Name: asistencias fk_asistencias_empleado; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asistencias
    ADD CONSTRAINT fk_asistencias_empleado FOREIGN KEY (id_empleado) REFERENCES public.empleados(id) ON DELETE CASCADE;


--
-- Name: empleados fk_empleados_cargo; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT fk_empleados_cargo FOREIGN KEY (id_cargo) REFERENCES public.cargos(id) ON DELETE RESTRICT;


--
-- Name: empleados fk_empleados_dpto; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT fk_empleados_dpto FOREIGN KEY (id_departamento) REFERENCES public.departamentos(id) ON DELETE RESTRICT;


--
-- Name: empleados fk_empleados_horario; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT fk_empleados_horario FOREIGN KEY (id_horario) REFERENCES public.horarios(id) ON DELETE SET NULL;


--
-- Name: empleados fk_empleados_persona; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT fk_empleados_persona FOREIGN KEY (id_persona) REFERENCES public.personas(id) ON DELETE RESTRICT;


--
-- Name: inventario fk_inv_cat; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario
    ADD CONSTRAINT fk_inv_cat FOREIGN KEY (id_categoria) REFERENCES public.categorias(id) ON DELETE RESTRICT;


--
-- Name: inventario fk_inv_ubi; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario
    ADD CONSTRAINT fk_inv_ubi FOREIGN KEY (id_ubicacion) REFERENCES public.ubicaciones(id) ON DELETE RESTRICT;


--
-- Name: inventario fk_inventario_bm1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario
    ADD CONSTRAINT fk_inventario_bm1 FOREIGN KEY (id_consolidado_bm1) REFERENCES public.inventario_consolidados_bm1(id) ON DELETE SET NULL;


--
-- Name: audit_logs fk_logs_usuario; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT fk_logs_usuario FOREIGN KEY (id_usuario) REFERENCES public.usuarios(id) ON DELETE SET NULL;


--
-- Name: participantes_taller fk_part_persona; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.participantes_taller
    ADD CONSTRAINT fk_part_persona FOREIGN KEY (id_persona) REFERENCES public.personas(id) ON DELETE RESTRICT;


--
-- Name: participantes_taller fk_part_taller; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.participantes_taller
    ADD CONSTRAINT fk_part_taller FOREIGN KEY (id_taller) REFERENCES public.talleres(id) ON DELETE CASCADE;


--
-- Name: pasante_documentos fk_pasante_doc; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pasante_documentos
    ADD CONSTRAINT fk_pasante_doc FOREIGN KEY (id_pasante) REFERENCES public.pasantes(id) ON DELETE CASCADE;


--
-- Name: pasantes fk_pasante_tutor; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pasantes
    ADD CONSTRAINT fk_pasante_tutor FOREIGN KEY (id_tutor_institucional) REFERENCES public.empleados(id) ON DELETE SET NULL;


--
-- Name: puntos_ruta fk_punto_ruta; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.puntos_ruta
    ADD CONSTRAINT fk_punto_ruta FOREIGN KEY (id_ruta) REFERENCES public.rutas(id) ON DELETE CASCADE;


--
-- Name: taller_informes fk_taller_inf; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.taller_informes
    ADD CONSTRAINT fk_taller_inf FOREIGN KEY (id_taller) REFERENCES public.talleres(id) ON DELETE CASCADE;


--
-- Name: talleres fk_talleres_facilitador; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.talleres
    ADD CONSTRAINT fk_talleres_facilitador FOREIGN KEY (id_facilitador) REFERENCES public.empleados(id) ON DELETE RESTRICT;


--
-- Name: talleres fk_talleres_ubicacion; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.talleres
    ADD CONSTRAINT fk_talleres_ubicacion FOREIGN KEY (id_ubicacion_formacion) REFERENCES public.ubicaciones_formacion(id) ON DELETE SET NULL;


--
-- Name: usuarios fk_usuarios_empleado; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT fk_usuarios_empleado FOREIGN KEY (id_empleado) REFERENCES public.empleados(id) ON DELETE RESTRICT;


--
-- Name: usuarios fk_usuarios_rol; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT fk_usuarios_rol FOREIGN KEY (id_rol) REFERENCES public.roles(id) ON DELETE RESTRICT;


--
-- Name: inventario_conteo_detalle inventario_conteo_detalle_id_conteo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario_conteo_detalle
    ADD CONSTRAINT inventario_conteo_detalle_id_conteo_fkey FOREIGN KEY (id_conteo) REFERENCES public.inventario_conteos(id) ON DELETE CASCADE;


--
-- Name: inventario_conteo_detalle inventario_conteo_detalle_id_inventario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario_conteo_detalle
    ADD CONSTRAINT inventario_conteo_detalle_id_inventario_fkey FOREIGN KEY (id_inventario) REFERENCES public.inventario(id) ON DELETE CASCADE;


--
-- Name: inventario_conteos inventario_conteos_id_responsable_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario_conteos
    ADD CONSTRAINT inventario_conteos_id_responsable_fkey FOREIGN KEY (id_responsable) REFERENCES public.empleados(id) ON DELETE SET NULL;


--
-- Name: inventario_documentos inventario_documentos_id_inventario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario_documentos
    ADD CONSTRAINT inventario_documentos_id_inventario_fkey FOREIGN KEY (id_inventario) REFERENCES public.inventario(id) ON DELETE CASCADE;


--
-- Name: inventario_dotacion inventario_dotacion_id_categoria_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario_dotacion
    ADD CONSTRAINT inventario_dotacion_id_categoria_fkey FOREIGN KEY (id_categoria) REFERENCES public.categorias(id) ON DELETE CASCADE;


--
-- Name: inventario_mantenimiento_plan inventario_mantenimiento_plan_id_inventario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario_mantenimiento_plan
    ADD CONSTRAINT inventario_mantenimiento_plan_id_inventario_fkey FOREIGN KEY (id_inventario) REFERENCES public.inventario(id) ON DELETE CASCADE;


--
-- Name: inventario_mantenimientos inventario_mantenimientos_id_actividad_salida_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario_mantenimientos
    ADD CONSTRAINT inventario_mantenimientos_id_actividad_salida_fkey FOREIGN KEY (id_actividad_salida) REFERENCES public.actividad_inventario(id) ON DELETE SET NULL;


--
-- Name: inventario_mantenimientos inventario_mantenimientos_id_empleado_encargado_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario_mantenimientos
    ADD CONSTRAINT inventario_mantenimientos_id_empleado_encargado_fkey FOREIGN KEY (id_empleado_encargado) REFERENCES public.empleados(id) ON DELETE SET NULL;


--
-- Name: inventario_mantenimientos inventario_mantenimientos_id_inventario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario_mantenimientos
    ADD CONSTRAINT inventario_mantenimientos_id_inventario_fkey FOREIGN KEY (id_inventario) REFERENCES public.inventario(id) ON DELETE CASCADE;


--
-- Name: municipio municipio_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.municipio
    ADD CONSTRAINT municipio_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.usuarios(id);


--
-- Name: oficios_emitidos oficios_emitidos_id_ruta_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.oficios_emitidos
    ADD CONSTRAINT oficios_emitidos_id_ruta_fkey FOREIGN KEY (id_ruta) REFERENCES public.rutas(id) ON DELETE SET NULL;


--
-- Name: parroquia parroquia_create_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.parroquia
    ADD CONSTRAINT parroquia_create_by_fkey FOREIGN KEY (create_by) REFERENCES public.usuarios(id);


--
-- Name: parroquia parroquia_delete_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.parroquia
    ADD CONSTRAINT parroquia_delete_by_fkey FOREIGN KEY (delete_by) REFERENCES public.usuarios(id);


--
-- Name: parroquia parroquia_id_municipio _fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.parroquia
    ADD CONSTRAINT "parroquia_id_municipio _fkey" FOREIGN KEY (id_municipio) REFERENCES public.municipio(id);


--
-- Name: parroquia parroquia_update_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.parroquia
    ADD CONSTRAINT parroquia_update_by_fkey FOREIGN KEY (update_by) REFERENCES public.usuarios(id);


--
-- Name: participantes_ruta participantes_ruta_id_persona_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.participantes_ruta
    ADD CONSTRAINT participantes_ruta_id_persona_fkey FOREIGN KEY (id_persona) REFERENCES public.personas(id) ON DELETE RESTRICT;


--
-- Name: participantes_ruta participantes_ruta_id_ruta_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.participantes_ruta
    ADD CONSTRAINT participantes_ruta_id_ruta_fkey FOREIGN KEY (id_ruta) REFERENCES public.rutas(id) ON DELETE CASCADE;


--
-- Name: participantes_taller participantes_taller_parroquia_id_libre_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.participantes_taller
    ADD CONSTRAINT participantes_taller_parroquia_id_libre_fkey FOREIGN KEY (parroquia_id_libre) REFERENCES public.parroquia(id);


--
-- Name: pasantes pasantes_id_persona_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pasantes
    ADD CONSTRAINT pasantes_id_persona_fkey FOREIGN KEY (id_persona) REFERENCES public.personas(id) ON DELETE RESTRICT;


--
-- Name: password_resets password_resets_id_usuario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_resets
    ADD CONSTRAINT password_resets_id_usuario_fkey FOREIGN KEY (id_usuario) REFERENCES public.usuarios(id) ON DELETE CASCADE;


--
-- Name: permisos_laborales permisos_laborales_id_aprobador_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permisos_laborales
    ADD CONSTRAINT permisos_laborales_id_aprobador_fkey FOREIGN KEY (id_aprobador) REFERENCES public.empleados(id) ON DELETE SET NULL;


--
-- Name: permisos_laborales permisos_laborales_id_empleado_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permisos_laborales
    ADD CONSTRAINT permisos_laborales_id_empleado_fkey FOREIGN KEY (id_empleado) REFERENCES public.empleados(id) ON DELETE RESTRICT;


--
-- Name: permisos_rol permisos_rol_id_rol_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permisos_rol
    ADD CONSTRAINT permisos_rol_id_rol_fkey FOREIGN KEY (id_rol) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: personas personas_parroquia_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personas
    ADD CONSTRAINT personas_parroquia_id_fkey FOREIGN KEY (parroquia_id) REFERENCES public.parroquia(id);


--
-- Name: ruta_informes ruta_informes_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ruta_informes
    ADD CONSTRAINT ruta_informes_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.usuarios(id);


--
-- Name: ruta_informes ruta_informes_id_ruta_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ruta_informes
    ADD CONSTRAINT ruta_informes_id_ruta_fkey FOREIGN KEY (id_ruta) REFERENCES public.rutas(id) ON DELETE CASCADE;


--
-- Name: rutas rutas_id_departamento_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rutas
    ADD CONSTRAINT rutas_id_departamento_fkey FOREIGN KEY (id_departamento) REFERENCES public.departamentos(id) ON DELETE SET NULL;


--
-- Name: rutas rutas_id_facilitador_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rutas
    ADD CONSTRAINT rutas_id_facilitador_fkey FOREIGN KEY (id_facilitador) REFERENCES public.empleados(id) ON DELETE SET NULL;


--
-- Name: taller_evidencias taller_evidencias_deleted_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.taller_evidencias
    ADD CONSTRAINT taller_evidencias_deleted_by_fkey FOREIGN KEY (deleted_by) REFERENCES public.usuarios(id);


--
-- Name: taller_evidencias taller_evidencias_id_taller_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.taller_evidencias
    ADD CONSTRAINT taller_evidencias_id_taller_fkey FOREIGN KEY (id_taller) REFERENCES public.talleres(id);


--
-- Name: taller_evidencias taller_evidencias_uploaded_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.taller_evidencias
    ADD CONSTRAINT taller_evidencias_uploaded_by_fkey FOREIGN KEY (uploaded_by) REFERENCES public.usuarios(id);


--
-- Name: ubicaciones ubicaciones_departamento _d_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ubicaciones
    ADD CONSTRAINT "ubicaciones_departamento _d_fkey" FOREIGN KEY ("departamento _d") REFERENCES public.departamentos(id);


--
-- Name: ubicaciones_formacion ubicaciones_formacion_parroquia_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ubicaciones_formacion
    ADD CONSTRAINT ubicaciones_formacion_parroquia_fkey FOREIGN KEY (parroquia) REFERENCES public.parroquia(id);


--
-- Name: vacaciones vacaciones_id_empleado_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vacaciones
    ADD CONSTRAINT vacaciones_id_empleado_fkey FOREIGN KEY (id_empleado) REFERENCES public.empleados(id) ON DELETE RESTRICT;


--
-- Name: visitantes visitantes_id_persona_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.visitantes
    ADD CONSTRAINT visitantes_id_persona_fkey FOREIGN KEY (id_persona) REFERENCES public.personas(id);


--
-- Name: visitas visitas_id_empleado_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.visitas
    ADD CONSTRAINT visitas_id_empleado_fkey FOREIGN KEY (id_empleado) REFERENCES public.empleados(id) ON DELETE SET NULL;


--
-- Name: visitas visitas_id_visitante_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.visitas
    ADD CONSTRAINT visitas_id_visitante_fkey FOREIGN KEY (id_visitante) REFERENCES public.visitantes(id) ON DELETE RESTRICT;


--
-- PostgreSQL database dump complete
--


--
-- Fin del esquema consolidado SIGTUR-IMATUR (migraciones 001-067).
--
