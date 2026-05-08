--
-- PostgreSQL database dump
--

-- Dumped from database version 17.4
-- Dumped by pg_dump version 17.4

-- Started on 2026-04-28 13:13:41

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
-- TOC entry 5 (class 2615 OID 131537)
-- Name: public; Type: SCHEMA; Schema: -; Owner: postgres
--

-- *not* creating schema, since initdb creates it


ALTER SCHEMA public OWNER TO postgres;

--
-- TOC entry 5241 (class 0 OID 0)
-- Dependencies: 5
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON SCHEMA public IS '';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 238 (class 1259 OID 131723)
-- Name: actividad_inventario; Type: TABLE; Schema: public; Owner: postgres
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
    CONSTRAINT actividad_inventario_tipo_movimiento_check CHECK (((tipo_movimiento)::text = ANY ((ARRAY['Asignacion'::character varying, 'Devolucion'::character varying, 'Traslado'::character varying, 'Baja'::character varying, 'Mantenimiento'::character varying])::text[])))
);


ALTER TABLE public.actividad_inventario OWNER TO postgres;

--
-- TOC entry 237 (class 1259 OID 131722)
-- Name: actividad_inventario_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.actividad_inventario_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.actividad_inventario_id_seq OWNER TO postgres;

--
-- TOC entry 5243 (class 0 OID 0)
-- Dependencies: 237
-- Name: actividad_inventario_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.actividad_inventario_id_seq OWNED BY public.actividad_inventario.id;


--
-- TOC entry 250 (class 1259 OID 131840)
-- Name: actividades_ruta; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.actividades_ruta OWNER TO postgres;

--
-- TOC entry 249 (class 1259 OID 131839)
-- Name: actividades_ruta_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.actividades_ruta_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.actividades_ruta_id_seq OWNER TO postgres;

--
-- TOC entry 5244 (class 0 OID 0)
-- Dependencies: 249
-- Name: actividades_ruta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.actividades_ruta_id_seq OWNED BY public.actividades_ruta.id;


--
-- TOC entry 230 (class 1259 OID 131650)
-- Name: asistencias; Type: TABLE; Schema: public; Owner: postgres
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
    deleted_by integer
);


ALTER TABLE public.asistencias OWNER TO postgres;

--
-- TOC entry 229 (class 1259 OID 131649)
-- Name: asistencias_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.asistencias_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.asistencias_id_seq OWNER TO postgres;

--
-- TOC entry 5245 (class 0 OID 0)
-- Dependencies: 229
-- Name: asistencias_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.asistencias_id_seq OWNED BY public.asistencias.id;


--
-- TOC entry 254 (class 1259 OID 131885)
-- Name: audit_logs; Type: TABLE; Schema: public; Owner: postgres
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
    CONSTRAINT audit_logs_operacion_check CHECK (((operacion)::text = ANY ((ARRAY['INSERT'::character varying, 'UPDATE'::character varying, 'DELETE'::character varying])::text[])))
);


ALTER TABLE public.audit_logs OWNER TO postgres;

--
-- TOC entry 253 (class 1259 OID 131884)
-- Name: audit_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.audit_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.audit_logs_id_seq OWNER TO postgres;

--
-- TOC entry 5246 (class 0 OID 0)
-- Dependencies: 253
-- Name: audit_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.audit_logs_id_seq OWNED BY public.audit_logs.id;


--
-- TOC entry 224 (class 1259 OID 131581)
-- Name: cargos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cargos (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion text,
    sueldo_base numeric(12,2) DEFAULT 0,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


ALTER TABLE public.cargos OWNER TO postgres;

--
-- TOC entry 223 (class 1259 OID 131580)
-- Name: cargos_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.cargos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.cargos_id_seq OWNER TO postgres;

--
-- TOC entry 5247 (class 0 OID 0)
-- Dependencies: 223
-- Name: cargos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.cargos_id_seq OWNED BY public.cargos.id;


--
-- TOC entry 232 (class 1259 OID 131669)
-- Name: categorias; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.categorias OWNER TO postgres;

--
-- TOC entry 231 (class 1259 OID 131668)
-- Name: categorias_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.categorias_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.categorias_id_seq OWNER TO postgres;

--
-- TOC entry 5248 (class 0 OID 0)
-- Dependencies: 231
-- Name: categorias_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.categorias_id_seq OWNED BY public.categorias.id;


--
-- TOC entry 222 (class 1259 OID 131568)
-- Name: departamentos; Type: TABLE; Schema: public; Owner: postgres
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
    deleted_by integer
);


ALTER TABLE public.departamentos OWNER TO postgres;

--
-- TOC entry 221 (class 1259 OID 131567)
-- Name: departamentos_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.departamentos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.departamentos_id_seq OWNER TO postgres;

--
-- TOC entry 5249 (class 0 OID 0)
-- Dependencies: 221
-- Name: departamentos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.departamentos_id_seq OWNED BY public.departamentos.id;


--
-- TOC entry 226 (class 1259 OID 131595)
-- Name: empleados; Type: TABLE; Schema: public; Owner: postgres
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
    deleted_by integer
);


ALTER TABLE public.empleados OWNER TO postgres;

--
-- TOC entry 225 (class 1259 OID 131594)
-- Name: empleados_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.empleados_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.empleados_id_seq OWNER TO postgres;

--
-- TOC entry 5250 (class 0 OID 0)
-- Dependencies: 225
-- Name: empleados_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.empleados_id_seq OWNED BY public.empleados.id;


--
-- TOC entry 236 (class 1259 OID 131695)
-- Name: inventario; Type: TABLE; Schema: public; Owner: postgres
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
    CONSTRAINT inventario_condicion_check CHECK (((condicion)::text = ANY ((ARRAY['Nuevo'::character varying, 'Bueno'::character varying, 'Regular'::character varying, 'Dañado'::character varying, 'Inservible'::character varying])::text[])))
);


ALTER TABLE public.inventario OWNER TO postgres;

--
-- TOC entry 235 (class 1259 OID 131694)
-- Name: inventario_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.inventario_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.inventario_id_seq OWNER TO postgres;

--
-- TOC entry 5251 (class 0 OID 0)
-- Dependencies: 235
-- Name: inventario_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.inventario_id_seq OWNED BY public.inventario.id;


--
-- TOC entry 264 (class 1259 OID 139982)
-- Name: municipio; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.municipio OWNER TO postgres;

--
-- TOC entry 5252 (class 0 OID 0)
-- Dependencies: 264
-- Name: TABLE municipio; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE public.municipio IS 'Municipios disponibles del sistema';


--
-- TOC entry 263 (class 1259 OID 139981)
-- Name: municipio_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.municipio_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.municipio_id_seq OWNER TO postgres;

--
-- TOC entry 5253 (class 0 OID 0)
-- Dependencies: 263
-- Name: municipio_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.municipio_id_seq OWNED BY public.municipio.id;


--
-- TOC entry 266 (class 1259 OID 139989)
-- Name: parroquia; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.parroquia OWNER TO postgres;

--
-- TOC entry 5254 (class 0 OID 0)
-- Dependencies: 266
-- Name: TABLE parroquia; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE public.parroquia IS 'Parroquias del sistema, asociada a municipio especifico.';


--
-- TOC entry 265 (class 1259 OID 139988)
-- Name: parroquia_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.parroquia_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.parroquia_id_seq OWNER TO postgres;

--
-- TOC entry 5255 (class 0 OID 0)
-- Dependencies: 265
-- Name: parroquia_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.parroquia_id_seq OWNED BY public.parroquia.id;


--
-- TOC entry 244 (class 1259 OID 131784)
-- Name: participantes_taller; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.participantes_taller (
    id integer NOT NULL,
    id_taller integer NOT NULL,
    id_persona integer NOT NULL,
    asistio boolean DEFAULT false,
    observaciones text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by integer
);


ALTER TABLE public.participantes_taller OWNER TO postgres;

--
-- TOC entry 243 (class 1259 OID 131783)
-- Name: participantes_taller_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.participantes_taller_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.participantes_taller_id_seq OWNER TO postgres;

--
-- TOC entry 5256 (class 0 OID 0)
-- Dependencies: 243
-- Name: participantes_taller_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.participantes_taller_id_seq OWNED BY public.participantes_taller.id;


--
-- TOC entry 262 (class 1259 OID 139965)
-- Name: pasante_documentos; Type: TABLE; Schema: public; Owner: postgres
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
    CONSTRAINT pasante_documentos_tipo_documento_check CHECK (((tipo_documento)::text = ANY ((ARRAY['Carta de Postulación'::character varying, 'Carta de Aceptación'::character varying, 'Evaluación'::character varying, 'Otro'::character varying])::text[])))
);


ALTER TABLE public.pasante_documentos OWNER TO postgres;

--
-- TOC entry 261 (class 1259 OID 139964)
-- Name: pasante_documentos_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.pasante_documentos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.pasante_documentos_id_seq OWNER TO postgres;

--
-- TOC entry 5257 (class 0 OID 0)
-- Dependencies: 261
-- Name: pasante_documentos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.pasante_documentos_id_seq OWNED BY public.pasante_documentos.id;


--
-- TOC entry 260 (class 1259 OID 139945)
-- Name: pasantes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pasantes (
    id integer NOT NULL,
    cedula character varying(20) NOT NULL,
    nombre character varying(100) NOT NULL,
    apellido character varying(100) NOT NULL,
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
    CONSTRAINT pasantes_estado_check CHECK (((estado)::text = ANY ((ARRAY['Postulado'::character varying, 'Aceptado'::character varying, 'En Curso'::character varying, 'Culminado'::character varying, 'Rechazado'::character varying])::text[])))
);


ALTER TABLE public.pasantes OWNER TO postgres;

--
-- TOC entry 259 (class 1259 OID 139944)
-- Name: pasantes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.pasantes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.pasantes_id_seq OWNER TO postgres;

--
-- TOC entry 5258 (class 0 OID 0)
-- Dependencies: 259
-- Name: pasantes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.pasantes_id_seq OWNED BY public.pasantes.id;


--
-- TOC entry 220 (class 1259 OID 131553)
-- Name: personas; Type: TABLE; Schema: public; Owner: postgres
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
    CONSTRAINT personas_genero_check CHECK ((genero = ANY (ARRAY['M'::bpchar, 'F'::bpchar, 'O'::bpchar])))
);


ALTER TABLE public.personas OWNER TO postgres;

--
-- TOC entry 5259 (class 0 OID 0)
-- Dependencies: 220
-- Name: TABLE personas; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE public.personas IS 'Datos base de todas las personas físicas del sistema.';


--
-- TOC entry 219 (class 1259 OID 131552)
-- Name: personas_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.personas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.personas_id_seq OWNER TO postgres;

--
-- TOC entry 5260 (class 0 OID 0)
-- Dependencies: 219
-- Name: personas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.personas_id_seq OWNED BY public.personas.id;


--
-- TOC entry 248 (class 1259 OID 131823)
-- Name: puntos_ruta; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.puntos_ruta OWNER TO postgres;

--
-- TOC entry 247 (class 1259 OID 131822)
-- Name: puntos_ruta_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.puntos_ruta_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.puntos_ruta_id_seq OWNER TO postgres;

--
-- TOC entry 5261 (class 0 OID 0)
-- Dependencies: 247
-- Name: puntos_ruta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.puntos_ruta_id_seq OWNED BY public.puntos_ruta.id;


--
-- TOC entry 218 (class 1259 OID 131540)
-- Name: roles; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.roles OWNER TO postgres;

--
-- TOC entry 5262 (class 0 OID 0)
-- Dependencies: 218
-- Name: TABLE roles; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE public.roles IS 'Define los niveles de permisos del sistema (Ej. Administrador, RRHH, Turismo). Determina a qué pantallas puede entrar un usuario.';


--
-- TOC entry 217 (class 1259 OID 131539)
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.roles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.roles_id_seq OWNER TO postgres;

--
-- TOC entry 5263 (class 0 OID 0)
-- Dependencies: 217
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- TOC entry 252 (class 1259 OID 131862)
-- Name: ruta_inventario; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ruta_inventario (
    id integer NOT NULL,
    id_ruta integer NOT NULL,
    id_inventario integer NOT NULL,
    cantidad integer DEFAULT 1,
    observaciones text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by integer
);


ALTER TABLE public.ruta_inventario OWNER TO postgres;

--
-- TOC entry 251 (class 1259 OID 131861)
-- Name: ruta_inventario_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ruta_inventario_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ruta_inventario_id_seq OWNER TO postgres;

--
-- TOC entry 5264 (class 0 OID 0)
-- Dependencies: 251
-- Name: ruta_inventario_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ruta_inventario_id_seq OWNED BY public.ruta_inventario.id;


--
-- TOC entry 246 (class 1259 OID 131807)
-- Name: rutas; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.rutas (
    id integer NOT NULL,
    nombre character varying(200) NOT NULL,
    descripcion text,
    duracion_estimada character varying(50),
    nivel_dificultad character varying(20) DEFAULT 'Fácil'::character varying,
    estado character varying(20) DEFAULT 'Activa'::character varying,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer,
    CONSTRAINT rutas_estado_check CHECK (((estado)::text = ANY ((ARRAY['Activa'::character varying, 'Inactiva'::character varying, 'En Mantenimiento'::character varying])::text[]))),
    CONSTRAINT rutas_nivel_dificultad_check CHECK (((nivel_dificultad)::text = ANY ((ARRAY['Fácil'::character varying, 'Moderado'::character varying, 'Difícil'::character varying, 'Extremo'::character varying])::text[])))
);


ALTER TABLE public.rutas OWNER TO postgres;

--
-- TOC entry 245 (class 1259 OID 131806)
-- Name: rutas_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.rutas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.rutas_id_seq OWNER TO postgres;

--
-- TOC entry 5265 (class 0 OID 0)
-- Dependencies: 245
-- Name: rutas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.rutas_id_seq OWNED BY public.rutas.id;


--
-- TOC entry 256 (class 1259 OID 139898)
-- Name: taller_informes; Type: TABLE; Schema: public; Owner: postgres
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
    created_by integer
);


ALTER TABLE public.taller_informes OWNER TO postgres;

--
-- TOC entry 255 (class 1259 OID 139897)
-- Name: taller_informes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.taller_informes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.taller_informes_id_seq OWNER TO postgres;

--
-- TOC entry 5266 (class 0 OID 0)
-- Dependencies: 255
-- Name: taller_informes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.taller_informes_id_seq OWNED BY public.taller_informes.id;


--
-- TOC entry 258 (class 1259 OID 139922)
-- Name: taller_inventario; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.taller_inventario (
    id integer NOT NULL,
    id_taller integer NOT NULL,
    id_inventario integer NOT NULL,
    cantidad integer DEFAULT 1,
    observaciones text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by integer
);


ALTER TABLE public.taller_inventario OWNER TO postgres;

--
-- TOC entry 257 (class 1259 OID 139921)
-- Name: taller_inventario_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.taller_inventario_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.taller_inventario_id_seq OWNER TO postgres;

--
-- TOC entry 5267 (class 0 OID 0)
-- Dependencies: 257
-- Name: taller_inventario_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.taller_inventario_id_seq OWNED BY public.taller_inventario.id;


--
-- TOC entry 242 (class 1259 OID 131758)
-- Name: talleres; Type: TABLE; Schema: public; Owner: postgres
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
    CONSTRAINT talleres_estado_check CHECK (((estado)::text = ANY ((ARRAY['Programado'::character varying, 'En Curso'::character varying, 'Finalizado'::character varying, 'Cancelado'::character varying])::text[])))
);


ALTER TABLE public.talleres OWNER TO postgres;

--
-- TOC entry 241 (class 1259 OID 131757)
-- Name: talleres_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.talleres_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.talleres_id_seq OWNER TO postgres;

--
-- TOC entry 5268 (class 0 OID 0)
-- Dependencies: 241
-- Name: talleres_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.talleres_id_seq OWNED BY public.talleres.id;


--
-- TOC entry 234 (class 1259 OID 131682)
-- Name: ubicaciones; Type: TABLE; Schema: public; Owner: postgres
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
    "departamento _d" integer NOT NULL
);


ALTER TABLE public.ubicaciones OWNER TO postgres;

--
-- TOC entry 5269 (class 0 OID 0)
-- Dependencies: 234
-- Name: TABLE ubicaciones; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE public.ubicaciones IS ' Estas son las oficinas o almacenes internos de la institución donde "duerme" un equipo (Ej. Almacén Principal, Despacho del Director).';


--
-- TOC entry 240 (class 1259 OID 131747)
-- Name: ubicaciones_formacion; Type: TABLE; Schema: public; Owner: postgres
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
    parroquia integer NOT NULL
);


ALTER TABLE public.ubicaciones_formacion OWNER TO postgres;

--
-- TOC entry 239 (class 1259 OID 131746)
-- Name: ubicaciones_formacion_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ubicaciones_formacion_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ubicaciones_formacion_id_seq OWNER TO postgres;

--
-- TOC entry 5270 (class 0 OID 0)
-- Dependencies: 239
-- Name: ubicaciones_formacion_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ubicaciones_formacion_id_seq OWNED BY public.ubicaciones_formacion.id;


--
-- TOC entry 233 (class 1259 OID 131681)
-- Name: ubicaciones_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ubicaciones_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ubicaciones_id_seq OWNER TO postgres;

--
-- TOC entry 5271 (class 0 OID 0)
-- Dependencies: 233
-- Name: ubicaciones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ubicaciones_id_seq OWNED BY public.ubicaciones.id;


--
-- TOC entry 228 (class 1259 OID 131624)
-- Name: usuarios; Type: TABLE; Schema: public; Owner: postgres
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
    deleted_by integer
);


ALTER TABLE public.usuarios OWNER TO postgres;

--
-- TOC entry 227 (class 1259 OID 131623)
-- Name: usuarios_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.usuarios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.usuarios_id_seq OWNER TO postgres;

--
-- TOC entry 5272 (class 0 OID 0)
-- Dependencies: 227
-- Name: usuarios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.usuarios_id_seq OWNED BY public.usuarios.id;


--
-- TOC entry 4849 (class 2604 OID 131726)
-- Name: actividad_inventario id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividad_inventario ALTER COLUMN id SET DEFAULT nextval('public.actividad_inventario_id_seq'::regclass);


--
-- TOC entry 4873 (class 2604 OID 131843)
-- Name: actividades_ruta id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividades_ruta ALTER COLUMN id SET DEFAULT nextval('public.actividades_ruta_id_seq'::regclass);


--
-- TOC entry 4835 (class 2604 OID 131653)
-- Name: asistencias id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.asistencias ALTER COLUMN id SET DEFAULT nextval('public.asistencias_id_seq'::regclass);


--
-- TOC entry 4879 (class 2604 OID 131888)
-- Name: audit_logs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.audit_logs ALTER COLUMN id SET DEFAULT nextval('public.audit_logs_id_seq'::regclass);


--
-- TOC entry 4824 (class 2604 OID 131584)
-- Name: cargos id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cargos ALTER COLUMN id SET DEFAULT nextval('public.cargos_id_seq'::regclass);


--
-- TOC entry 4839 (class 2604 OID 131672)
-- Name: categorias id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categorias ALTER COLUMN id SET DEFAULT nextval('public.categorias_id_seq'::regclass);


--
-- TOC entry 4821 (class 2604 OID 131571)
-- Name: departamentos id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departamentos ALTER COLUMN id SET DEFAULT nextval('public.departamentos_id_seq'::regclass);


--
-- TOC entry 4828 (class 2604 OID 131598)
-- Name: empleados id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empleados ALTER COLUMN id SET DEFAULT nextval('public.empleados_id_seq'::regclass);


--
-- TOC entry 4845 (class 2604 OID 131698)
-- Name: inventario id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inventario ALTER COLUMN id SET DEFAULT nextval('public.inventario_id_seq'::regclass);


--
-- TOC entry 4900 (class 2604 OID 139985)
-- Name: municipio id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.municipio ALTER COLUMN id SET DEFAULT nextval('public.municipio_id_seq'::regclass);


--
-- TOC entry 4902 (class 2604 OID 139992)
-- Name: parroquia id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parroquia ALTER COLUMN id SET DEFAULT nextval('public.parroquia_id_seq'::regclass);


--
-- TOC entry 4861 (class 2604 OID 131787)
-- Name: participantes_taller id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.participantes_taller ALTER COLUMN id SET DEFAULT nextval('public.participantes_taller_id_seq'::regclass);


--
-- TOC entry 4897 (class 2604 OID 139968)
-- Name: pasante_documentos id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pasante_documentos ALTER COLUMN id SET DEFAULT nextval('public.pasante_documentos_id_seq'::regclass);


--
-- TOC entry 4893 (class 2604 OID 139948)
-- Name: pasantes id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pasantes ALTER COLUMN id SET DEFAULT nextval('public.pasantes_id_seq'::regclass);


--
-- TOC entry 4818 (class 2604 OID 131556)
-- Name: personas id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personas ALTER COLUMN id SET DEFAULT nextval('public.personas_id_seq'::regclass);


--
-- TOC entry 4869 (class 2604 OID 131826)
-- Name: puntos_ruta id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.puntos_ruta ALTER COLUMN id SET DEFAULT nextval('public.puntos_ruta_id_seq'::regclass);


--
-- TOC entry 4815 (class 2604 OID 131543)
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- TOC entry 4876 (class 2604 OID 131865)
-- Name: ruta_inventario id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ruta_inventario ALTER COLUMN id SET DEFAULT nextval('public.ruta_inventario_id_seq'::regclass);


--
-- TOC entry 4864 (class 2604 OID 131810)
-- Name: rutas id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rutas ALTER COLUMN id SET DEFAULT nextval('public.rutas_id_seq'::regclass);


--
-- TOC entry 4881 (class 2604 OID 139901)
-- Name: taller_informes id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.taller_informes ALTER COLUMN id SET DEFAULT nextval('public.taller_informes_id_seq'::regclass);


--
-- TOC entry 4890 (class 2604 OID 139925)
-- Name: taller_inventario id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.taller_inventario ALTER COLUMN id SET DEFAULT nextval('public.taller_inventario_id_seq'::regclass);


--
-- TOC entry 4856 (class 2604 OID 131761)
-- Name: talleres id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.talleres ALTER COLUMN id SET DEFAULT nextval('public.talleres_id_seq'::regclass);


--
-- TOC entry 4842 (class 2604 OID 131685)
-- Name: ubicaciones id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ubicaciones ALTER COLUMN id SET DEFAULT nextval('public.ubicaciones_id_seq'::regclass);


--
-- TOC entry 4853 (class 2604 OID 131750)
-- Name: ubicaciones_formacion id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ubicaciones_formacion ALTER COLUMN id SET DEFAULT nextval('public.ubicaciones_formacion_id_seq'::regclass);


--
-- TOC entry 4832 (class 2604 OID 131627)
-- Name: usuarios id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios ALTER COLUMN id SET DEFAULT nextval('public.usuarios_id_seq'::regclass);


--
-- TOC entry 5207 (class 0 OID 131723)
-- Dependencies: 238
-- Data for Name: actividad_inventario; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.actividad_inventario (id, id_inventario, tipo_movimiento, descripcion, fecha, id_empleado_responsable, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by) FROM stdin;
\.


--
-- TOC entry 5219 (class 0 OID 131840)
-- Dependencies: 250
-- Data for Name: actividades_ruta; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.actividades_ruta (id, id_ruta, nombre, descripcion, fecha, id_empleado_responsable, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by) FROM stdin;
\.


--
-- TOC entry 5199 (class 0 OID 131650)
-- Dependencies: 230
-- Data for Name: asistencias; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.asistencias (id, id_empleado, fecha, hora_entrada, hora_salida, observacion, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by) FROM stdin;
1	1	2026-04-17	15:17:24	15:17:31	Marcaje de salida automático	t	2026-04-17 15:17:24.260671	2026-04-17 15:17:31.216112	\N	1	1	\N
2	1	2026-04-28	03:19:06	03:19:14	Marcaje de salida automático	t	2026-04-28 03:19:06.059983	2026-04-28 03:19:14.187483	\N	1	1	\N
\.


--
-- TOC entry 5223 (class 0 OID 131885)
-- Dependencies: 254
-- Data for Name: audit_logs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.audit_logs (id, tabla_afectada, operacion, record_id, datos_previos, datos_nuevos, id_usuario, fecha, ip_direccion) FROM stdin;
8	talleres	UPDATE	1	{"is_active": false}	{"is_active": true}	2	2026-04-19 00:09:05.950151	127.0.0.1
19	departamentos	INSERT	0	\N	{"nombre": "RRHH"}	2	2026-04-28 03:25:27.028582	::1
\.


--
-- TOC entry 5193 (class 0 OID 131581)
-- Dependencies: 224
-- Data for Name: cargos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cargos (id, nombre, descripcion, sueldo_base, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by) FROM stdin;
2	Director	\N	1000.00	t	2026-04-12 14:41:40.888475	\N	\N	1	\N	\N
3	CTI	Coordinaci&oacute;n de tecnolog&iacute;a de Informaci&oacute;n.	150.00	t	2026-04-17 14:52:17.178796	\N	\N	1	\N	\N
\.


--
-- TOC entry 5201 (class 0 OID 131669)
-- Dependencies: 232
-- Data for Name: categorias; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.categorias (id, nombre, descripcion, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by) FROM stdin;
1	Inmobiliario	Bienes inmobiliarios	t	2026-04-18 04:56:22.455698	\N	\N	2	\N	\N
2	Inmuebles	Prueba  2	t	2026-04-28 03:21:34.415084	2026-04-28 03:21:48.698294	\N	2	2	\N
\.


--
-- TOC entry 5191 (class 0 OID 131568)
-- Dependencies: 222
-- Data for Name: departamentos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.departamentos (id, nombre, descripcion, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by) FROM stdin;
3	Dirección General	Sede Principal	t	2026-04-12 14:41:40.888475	\N	\N	1	\N	\N
4	Departamento de inform&aacute;tica	se encarga de-...	t	2026-04-17 15:11:35.123254	\N	\N	2	\N	\N
5	RRHH	Departamento de Talento Humano	t	2026-04-28 03:25:26.955571	\N	\N	2	\N	\N
\.


--
-- TOC entry 5195 (class 0 OID 131595)
-- Dependencies: 226
-- Data for Name: empleados; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.empleados (id, id_persona, id_cargo, id_departamento, nro_expediente, fecha_ingreso, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by) FROM stdin;
1	2	2	3	\N	2026-04-12	t	2026-04-12 14:41:40.888475	\N	\N	1	\N	\N
\.


--
-- TOC entry 5205 (class 0 OID 131695)
-- Dependencies: 236
-- Data for Name: inventario; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.inventario (id, id_categoria, id_ubicacion, codigo_bn, nombre, descripcion, marca, modelo, serial, condicion, observaciones, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by) FROM stdin;
\.


--
-- TOC entry 5233 (class 0 OID 139982)
-- Dependencies: 264
-- Data for Name: municipio; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.municipio (id, nombre, codigo_postal, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by) FROM stdin;
2	Sucre	6101	t	2026-04-26 16:51:36.702899	2026-04-26 16:51:36.702899	\N	2	2	\N
3	Bolivar	6107	t	2026-04-28 03:22:54.098579	2026-04-28 03:22:54.098579	\N	2	2	\N
\.


--
-- TOC entry 5235 (class 0 OID 139989)
-- Dependencies: 266
-- Data for Name: parroquia; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.parroquia (id, nombre, id_municipio, is_active, create_by, update_by, delete_by, create_at, update_at, delete_at) FROM stdin;
1	Altagracia	2	t	2	2	\N	2026-04-26 16:52:13.285237	2026-04-26 16:52:13.285237	\N
2	Santa Ines	2	t	2	2	\N	2026-04-26 16:53:21.016217	2026-04-26 16:53:21.016217	\N
3	Valentin Valiente	2	t	2	2	\N	2026-04-26 16:54:36.814184	2026-04-26 16:54:36.814184	\N
4	Ayacucho	2	t	2	2	\N	2026-04-26 16:55:00.340542	2026-04-26 16:55:00.340542	\N
5	San Juan	2	t	2	2	\N	2026-04-26 16:55:32.137902	2026-04-26 16:55:32.137902	\N
6	Raul Leoni	2	t	2	2	\N	2026-04-26 16:56:06.355341	2026-04-26 16:56:06.355341	\N
7	Gran Mariscal	2	t	2	2	\N	2026-04-26 16:56:30.227526	2026-04-26 16:56:30.227526	\N
\.


--
-- TOC entry 5213 (class 0 OID 131784)
-- Dependencies: 244
-- Data for Name: participantes_taller; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.participantes_taller (id, id_taller, id_persona, asistio, observaciones, created_at, created_by) FROM stdin;
\.


--
-- TOC entry 5231 (class 0 OID 139965)
-- Dependencies: 262
-- Data for Name: pasante_documentos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.pasante_documentos (id, id_pasante, tipo_documento, entregado, archivo_url, observaciones, fecha_registro, created_by) FROM stdin;
1	1	Carta de Postulación	t	\N	Recibida por coordinación	2026-04-18 01:04:13.419835	\N
2	1	Carta de Aceptación	t	\N	Firmada por el director	2026-04-18 01:04:13.419835	\N
3	1	Evaluación	f	\N	Pendiente al finalizar	2026-04-18 01:04:13.419835	\N
4	1	Otro	t	/uploads/pasantes/1776786487_WhatsAppImage2025-07-02at4.53.32PM1.jpeg	Dale 	2026-04-21 15:48:07.712145	2
5	1	Carta de Aceptación	t	/uploads/pasantes/1777346944_Anyeliscv.pdf.pdf		2026-04-28 03:29:04.408776	2
\.


--
-- TOC entry 5229 (class 0 OID 139945)
-- Dependencies: 260
-- Data for Name: pasantes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.pasantes (id, cedula, nombre, apellido, institucion, carrera, id_tutor_institucional, fecha_inicio, fecha_fin, estado, evaluacion, nota, is_active, created_at, updated_at, deleted_at, created_by) FROM stdin;
1	V-30123456	María	López	UPTAEB	Turismo	1	2026-04-18	2026-07-18	En Curso	\N	\N	t	2026-04-18 01:04:13.419835	\N	\N	\N
\.


--
-- TOC entry 5189 (class 0 OID 131553)
-- Dependencies: 220
-- Data for Name: personas; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.personas (id, cedula, nombre, apellido, telefono, correo, genero, fecha_nacimiento, direccion, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by, parroquia_id) FROM stdin;
2	V-00000000	Super	Admin	0000-0000000	\N	\N	\N	Localhost	t	2026-04-12 14:41:40.888475	\N	\N	1	\N	\N	\N
\.


--
-- TOC entry 5217 (class 0 OID 131823)
-- Dependencies: 248
-- Data for Name: puntos_ruta; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.puntos_ruta (id, id_ruta, nombre, descripcion, orden, latitud, longitud, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by) FROM stdin;
\.


--
-- TOC entry 5187 (class 0 OID 131540)
-- Dependencies: 218
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.roles (id, nombre, descripcion, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by) FROM stdin;
1	Administrador	Acceso total al sistema	t	2026-04-12 14:15:24.492607	\N	\N	\N	\N	\N
2	RRHH	Gestión de personal y asistencia	t	2026-04-12 14:15:24.492607	\N	\N	\N	\N	\N
3	Turismo	Gestión de rutas y formación	t	2026-04-12 14:15:24.492607	\N	\N	\N	\N	\N
4	Inventario	Gestión de bienes institucionales	t	2026-04-12 14:15:24.492607	\N	\N	\N	\N	\N
\.


--
-- TOC entry 5221 (class 0 OID 131862)
-- Dependencies: 252
-- Data for Name: ruta_inventario; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.ruta_inventario (id, id_ruta, id_inventario, cantidad, observaciones, created_at, created_by) FROM stdin;
\.


--
-- TOC entry 5215 (class 0 OID 131807)
-- Dependencies: 246
-- Data for Name: rutas; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.rutas (id, nombre, descripcion, duracion_estimada, nivel_dificultad, estado, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by) FROM stdin;
\.


--
-- TOC entry 5225 (class 0 OID 139898)
-- Dependencies: 256
-- Data for Name: taller_informes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.taller_informes (id, id_taller, unidad_estadal, lugar_exacto, instituciones_presentes, mujeres, hombres, ninas, ninos, total_atendidas, resumen_actividad, is_active, created_at, updated_at, created_by) FROM stdin;
\.


--
-- TOC entry 5227 (class 0 OID 139922)
-- Dependencies: 258
-- Data for Name: taller_inventario; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.taller_inventario (id, id_taller, id_inventario, cantidad, observaciones, created_at, created_by) FROM stdin;
\.


--
-- TOC entry 5211 (class 0 OID 131758)
-- Dependencies: 242
-- Data for Name: talleres; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.talleres (id, nombre, descripcion, fecha_inicio, fecha_fin, hora_inicio, hora_fin, id_ubicacion_formacion, id_facilitador, cupo_maximo, estado, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by) FROM stdin;
1	Charla cultura		2026-04-16	2026-04-17	11:32:00	14:32:00	\N	1	30	Finalizado	t	2026-04-17 14:33:51.465932	\N	\N	2	\N	\N
2	CTI	Taller sobre los parques de Cuaman&aacute;	2026-04-18	2026-04-18	11:30:00	14:30:00	\N	1	15	Programado	t	2026-04-18 02:39:56.325374	2026-04-28 03:24:22.084605	\N	2	2	\N
\.


--
-- TOC entry 5203 (class 0 OID 131682)
-- Dependencies: 234
-- Data for Name: ubicaciones; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.ubicaciones (id, nombre, descripcion, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by, "departamento _d") FROM stdin;
\.


--
-- TOC entry 5209 (class 0 OID 131747)
-- Dependencies: 240
-- Data for Name: ubicaciones_formacion; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.ubicaciones_formacion (id, nombre, tipo, direccion, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by, parroquia) FROM stdin;
\.


--
-- TOC entry 5197 (class 0 OID 131624)
-- Dependencies: 228
-- Data for Name: usuarios; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.usuarios (id, id_empleado, id_rol, username, password, ultimo_login, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by) FROM stdin;
2	1	1	admin	$2y$10$BwzQEV0g8B0OeoSgD4NUX.lI2h1oltT3qAWZWv48eXES8WED.IEwy	\N	t	2026-04-12 14:41:40.888475	\N	\N	1	\N	\N
\.


--
-- TOC entry 5273 (class 0 OID 0)
-- Dependencies: 237
-- Name: actividad_inventario_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.actividad_inventario_id_seq', 1, false);


--
-- TOC entry 5274 (class 0 OID 0)
-- Dependencies: 249
-- Name: actividades_ruta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.actividades_ruta_id_seq', 1, false);


--
-- TOC entry 5275 (class 0 OID 0)
-- Dependencies: 229
-- Name: asistencias_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.asistencias_id_seq', 2, true);


--
-- TOC entry 5276 (class 0 OID 0)
-- Dependencies: 253
-- Name: audit_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.audit_logs_id_seq', 19, true);


--
-- TOC entry 5277 (class 0 OID 0)
-- Dependencies: 223
-- Name: cargos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.cargos_id_seq', 3, true);


--
-- TOC entry 5278 (class 0 OID 0)
-- Dependencies: 231
-- Name: categorias_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.categorias_id_seq', 2, true);


--
-- TOC entry 5279 (class 0 OID 0)
-- Dependencies: 221
-- Name: departamentos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.departamentos_id_seq', 5, true);


--
-- TOC entry 5280 (class 0 OID 0)
-- Dependencies: 225
-- Name: empleados_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.empleados_id_seq', 1, true);


--
-- TOC entry 5281 (class 0 OID 0)
-- Dependencies: 235
-- Name: inventario_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.inventario_id_seq', 1, false);


--
-- TOC entry 5282 (class 0 OID 0)
-- Dependencies: 263
-- Name: municipio_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.municipio_id_seq', 3, true);


--
-- TOC entry 5283 (class 0 OID 0)
-- Dependencies: 265
-- Name: parroquia_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.parroquia_id_seq', 7, true);


--
-- TOC entry 5284 (class 0 OID 0)
-- Dependencies: 243
-- Name: participantes_taller_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.participantes_taller_id_seq', 1, true);


--
-- TOC entry 5285 (class 0 OID 0)
-- Dependencies: 261
-- Name: pasante_documentos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.pasante_documentos_id_seq', 5, true);


--
-- TOC entry 5286 (class 0 OID 0)
-- Dependencies: 259
-- Name: pasantes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.pasantes_id_seq', 1, true);


--
-- TOC entry 5287 (class 0 OID 0)
-- Dependencies: 219
-- Name: personas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.personas_id_seq', 3, true);


--
-- TOC entry 5288 (class 0 OID 0)
-- Dependencies: 247
-- Name: puntos_ruta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.puntos_ruta_id_seq', 1, false);


--
-- TOC entry 5289 (class 0 OID 0)
-- Dependencies: 217
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.roles_id_seq', 4, true);


--
-- TOC entry 5290 (class 0 OID 0)
-- Dependencies: 251
-- Name: ruta_inventario_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.ruta_inventario_id_seq', 1, false);


--
-- TOC entry 5291 (class 0 OID 0)
-- Dependencies: 245
-- Name: rutas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.rutas_id_seq', 1, false);


--
-- TOC entry 5292 (class 0 OID 0)
-- Dependencies: 255
-- Name: taller_informes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.taller_informes_id_seq', 1, false);


--
-- TOC entry 5293 (class 0 OID 0)
-- Dependencies: 257
-- Name: taller_inventario_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.taller_inventario_id_seq', 1, false);


--
-- TOC entry 5294 (class 0 OID 0)
-- Dependencies: 241
-- Name: talleres_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.talleres_id_seq', 2, true);


--
-- TOC entry 5295 (class 0 OID 0)
-- Dependencies: 239
-- Name: ubicaciones_formacion_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.ubicaciones_formacion_id_seq', 1, false);


--
-- TOC entry 5296 (class 0 OID 0)
-- Dependencies: 233
-- Name: ubicaciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.ubicaciones_id_seq', 1, false);


--
-- TOC entry 5297 (class 0 OID 0)
-- Dependencies: 227
-- Name: usuarios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.usuarios_id_seq', 2, true);


--
-- TOC entry 4962 (class 2606 OID 131734)
-- Name: actividad_inventario actividad_inventario_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividad_inventario
    ADD CONSTRAINT actividad_inventario_pkey PRIMARY KEY (id);


--
-- TOC entry 4980 (class 2606 OID 131849)
-- Name: actividades_ruta actividades_ruta_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividades_ruta
    ADD CONSTRAINT actividades_ruta_pkey PRIMARY KEY (id);


--
-- TOC entry 4943 (class 2606 OID 131660)
-- Name: asistencias asistencias_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.asistencias
    ADD CONSTRAINT asistencias_pkey PRIMARY KEY (id);


--
-- TOC entry 4987 (class 2606 OID 131894)
-- Name: audit_logs audit_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_pkey PRIMARY KEY (id);


--
-- TOC entry 4926 (class 2606 OID 131593)
-- Name: cargos cargos_nombre_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cargos
    ADD CONSTRAINT cargos_nombre_key UNIQUE (nombre);


--
-- TOC entry 4928 (class 2606 OID 131591)
-- Name: cargos cargos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cargos
    ADD CONSTRAINT cargos_pkey PRIMARY KEY (id);


--
-- TOC entry 4947 (class 2606 OID 131680)
-- Name: categorias categorias_nombre_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categorias
    ADD CONSTRAINT categorias_nombre_key UNIQUE (nombre);


--
-- TOC entry 4949 (class 2606 OID 131678)
-- Name: categorias categorias_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categorias
    ADD CONSTRAINT categorias_pkey PRIMARY KEY (id);


--
-- TOC entry 4922 (class 2606 OID 131579)
-- Name: departamentos departamentos_nombre_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departamentos
    ADD CONSTRAINT departamentos_nombre_key UNIQUE (nombre);


--
-- TOC entry 4924 (class 2606 OID 131577)
-- Name: departamentos departamentos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departamentos
    ADD CONSTRAINT departamentos_pkey PRIMARY KEY (id);


--
-- TOC entry 4930 (class 2606 OID 131605)
-- Name: empleados empleados_id_persona_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT empleados_id_persona_key UNIQUE (id_persona);


--
-- TOC entry 4932 (class 2606 OID 131607)
-- Name: empleados empleados_nro_expediente_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT empleados_nro_expediente_key UNIQUE (nro_expediente);


--
-- TOC entry 4934 (class 2606 OID 131603)
-- Name: empleados empleados_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT empleados_pkey PRIMARY KEY (id);


--
-- TOC entry 4956 (class 2606 OID 131708)
-- Name: inventario inventario_codigo_bn_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inventario
    ADD CONSTRAINT inventario_codigo_bn_key UNIQUE (codigo_bn);


--
-- TOC entry 4958 (class 2606 OID 131706)
-- Name: inventario inventario_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inventario
    ADD CONSTRAINT inventario_pkey PRIMARY KEY (id);


--
-- TOC entry 4960 (class 2606 OID 131710)
-- Name: inventario inventario_serial_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inventario
    ADD CONSTRAINT inventario_serial_key UNIQUE (serial);


--
-- TOC entry 5005 (class 2606 OID 139987)
-- Name: municipio municipio_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.municipio
    ADD CONSTRAINT municipio_pkey PRIMARY KEY (id);


--
-- TOC entry 5007 (class 2606 OID 139994)
-- Name: parroquia parroquia_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parroquia
    ADD CONSTRAINT parroquia_pkey PRIMARY KEY (id);


--
-- TOC entry 4971 (class 2606 OID 131793)
-- Name: participantes_taller participantes_taller_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.participantes_taller
    ADD CONSTRAINT participantes_taller_pkey PRIMARY KEY (id);


--
-- TOC entry 5003 (class 2606 OID 139975)
-- Name: pasante_documentos pasante_documentos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pasante_documentos
    ADD CONSTRAINT pasante_documentos_pkey PRIMARY KEY (id);


--
-- TOC entry 4999 (class 2606 OID 139958)
-- Name: pasantes pasantes_cedula_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pasantes
    ADD CONSTRAINT pasantes_cedula_key UNIQUE (cedula);


--
-- TOC entry 5001 (class 2606 OID 139956)
-- Name: pasantes pasantes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pasantes
    ADD CONSTRAINT pasantes_pkey PRIMARY KEY (id);


--
-- TOC entry 4918 (class 2606 OID 131565)
-- Name: personas personas_cedula_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personas
    ADD CONSTRAINT personas_cedula_key UNIQUE (cedula);


--
-- TOC entry 4920 (class 2606 OID 131563)
-- Name: personas personas_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personas
    ADD CONSTRAINT personas_pkey PRIMARY KEY (id);


--
-- TOC entry 4978 (class 2606 OID 131833)
-- Name: puntos_ruta puntos_ruta_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.puntos_ruta
    ADD CONSTRAINT puntos_ruta_pkey PRIMARY KEY (id);


--
-- TOC entry 4913 (class 2606 OID 131551)
-- Name: roles roles_nombre_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_nombre_key UNIQUE (nombre);


--
-- TOC entry 4915 (class 2606 OID 131549)
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- TOC entry 4983 (class 2606 OID 131871)
-- Name: ruta_inventario ruta_inventario_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ruta_inventario
    ADD CONSTRAINT ruta_inventario_pkey PRIMARY KEY (id);


--
-- TOC entry 4976 (class 2606 OID 131820)
-- Name: rutas rutas_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rutas
    ADD CONSTRAINT rutas_pkey PRIMARY KEY (id);


--
-- TOC entry 4991 (class 2606 OID 139915)
-- Name: taller_informes taller_informes_id_taller_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.taller_informes
    ADD CONSTRAINT taller_informes_id_taller_key UNIQUE (id_taller);


--
-- TOC entry 4993 (class 2606 OID 139913)
-- Name: taller_informes taller_informes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.taller_informes
    ADD CONSTRAINT taller_informes_pkey PRIMARY KEY (id);


--
-- TOC entry 4995 (class 2606 OID 139931)
-- Name: taller_inventario taller_inventario_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.taller_inventario
    ADD CONSTRAINT taller_inventario_pkey PRIMARY KEY (id);


--
-- TOC entry 4969 (class 2606 OID 131770)
-- Name: talleres talleres_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.talleres
    ADD CONSTRAINT talleres_pkey PRIMARY KEY (id);


--
-- TOC entry 4965 (class 2606 OID 131756)
-- Name: ubicaciones_formacion ubicaciones_formacion_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ubicaciones_formacion
    ADD CONSTRAINT ubicaciones_formacion_pkey PRIMARY KEY (id);


--
-- TOC entry 4951 (class 2606 OID 131693)
-- Name: ubicaciones ubicaciones_nombre_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ubicaciones
    ADD CONSTRAINT ubicaciones_nombre_key UNIQUE (nombre);


--
-- TOC entry 4953 (class 2606 OID 131691)
-- Name: ubicaciones ubicaciones_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ubicaciones
    ADD CONSTRAINT ubicaciones_pkey PRIMARY KEY (id);


--
-- TOC entry 4973 (class 2606 OID 131795)
-- Name: participantes_taller uq_participante_taller; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.participantes_taller
    ADD CONSTRAINT uq_participante_taller UNIQUE (id_taller, id_persona);


--
-- TOC entry 4985 (class 2606 OID 131873)
-- Name: ruta_inventario uq_ruta_inventario; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ruta_inventario
    ADD CONSTRAINT uq_ruta_inventario UNIQUE (id_ruta, id_inventario);


--
-- TOC entry 4997 (class 2606 OID 139933)
-- Name: taller_inventario uq_taller_inventario; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.taller_inventario
    ADD CONSTRAINT uq_taller_inventario UNIQUE (id_taller, id_inventario);


--
-- TOC entry 4937 (class 2606 OID 131635)
-- Name: usuarios usuarios_id_empleado_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_id_empleado_key UNIQUE (id_empleado);


--
-- TOC entry 4939 (class 2606 OID 131633)
-- Name: usuarios usuarios_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);


--
-- TOC entry 4941 (class 2606 OID 131637)
-- Name: usuarios usuarios_username_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_username_key UNIQUE (username);


--
-- TOC entry 4963 (class 1259 OID 131745)
-- Name: idx_act_inv_fecha; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_act_inv_fecha ON public.actividad_inventario USING btree (fecha);


--
-- TOC entry 4981 (class 1259 OID 131860)
-- Name: idx_act_ruta_fecha; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_act_ruta_fecha ON public.actividades_ruta USING btree (fecha);


--
-- TOC entry 4944 (class 1259 OID 131667)
-- Name: idx_asistencias_empleado_fecha; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_asistencias_empleado_fecha ON public.asistencias USING btree (id_empleado, fecha);


--
-- TOC entry 4945 (class 1259 OID 131666)
-- Name: idx_asistencias_fecha; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_asistencias_fecha ON public.asistencias USING btree (fecha);


--
-- TOC entry 4954 (class 1259 OID 131721)
-- Name: idx_inventario_codigo_bn; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_inventario_codigo_bn ON public.inventario USING btree (codigo_bn);


--
-- TOC entry 4988 (class 1259 OID 131900)
-- Name: idx_logs_fecha; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_logs_fecha ON public.audit_logs USING btree (fecha);


--
-- TOC entry 4989 (class 1259 OID 131901)
-- Name: idx_logs_tabla; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_logs_tabla ON public.audit_logs USING btree (tabla_afectada);


--
-- TOC entry 4916 (class 1259 OID 131566)
-- Name: idx_personas_cedula; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_personas_cedula ON public.personas USING btree (cedula);


--
-- TOC entry 4974 (class 1259 OID 131821)
-- Name: idx_rutas_estado; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_rutas_estado ON public.rutas USING btree (estado);


--
-- TOC entry 4966 (class 1259 OID 131782)
-- Name: idx_talleres_estado; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_talleres_estado ON public.talleres USING btree (estado);


--
-- TOC entry 4967 (class 1259 OID 131781)
-- Name: idx_talleres_fecha; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_talleres_fecha ON public.talleres USING btree (fecha_inicio);


--
-- TOC entry 4935 (class 1259 OID 131648)
-- Name: idx_usuarios_username; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_usuarios_username ON public.usuarios USING btree (username);


--
-- TOC entry 5018 (class 2606 OID 131740)
-- Name: actividad_inventario fk_act_inv_emp; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividad_inventario
    ADD CONSTRAINT fk_act_inv_emp FOREIGN KEY (id_empleado_responsable) REFERENCES public.empleados(id) ON DELETE SET NULL;


--
-- TOC entry 5019 (class 2606 OID 131735)
-- Name: actividad_inventario fk_act_inv_item; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividad_inventario
    ADD CONSTRAINT fk_act_inv_item FOREIGN KEY (id_inventario) REFERENCES public.inventario(id) ON DELETE RESTRICT;


--
-- TOC entry 5026 (class 2606 OID 131850)
-- Name: actividades_ruta fk_act_ruta; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividades_ruta
    ADD CONSTRAINT fk_act_ruta FOREIGN KEY (id_ruta) REFERENCES public.rutas(id) ON DELETE CASCADE;


--
-- TOC entry 5027 (class 2606 OID 131855)
-- Name: actividades_ruta fk_act_ruta_emp; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividades_ruta
    ADD CONSTRAINT fk_act_ruta_emp FOREIGN KEY (id_empleado_responsable) REFERENCES public.empleados(id) ON DELETE SET NULL;


--
-- TOC entry 5014 (class 2606 OID 131661)
-- Name: asistencias fk_asistencias_empleado; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.asistencias
    ADD CONSTRAINT fk_asistencias_empleado FOREIGN KEY (id_empleado) REFERENCES public.empleados(id) ON DELETE CASCADE;


--
-- TOC entry 5009 (class 2606 OID 131613)
-- Name: empleados fk_empleados_cargo; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT fk_empleados_cargo FOREIGN KEY (id_cargo) REFERENCES public.cargos(id) ON DELETE RESTRICT;


--
-- TOC entry 5010 (class 2606 OID 131618)
-- Name: empleados fk_empleados_dpto; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT fk_empleados_dpto FOREIGN KEY (id_departamento) REFERENCES public.departamentos(id) ON DELETE RESTRICT;


--
-- TOC entry 5011 (class 2606 OID 131608)
-- Name: empleados fk_empleados_persona; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT fk_empleados_persona FOREIGN KEY (id_persona) REFERENCES public.personas(id) ON DELETE RESTRICT;


--
-- TOC entry 5016 (class 2606 OID 131711)
-- Name: inventario fk_inv_cat; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inventario
    ADD CONSTRAINT fk_inv_cat FOREIGN KEY (id_categoria) REFERENCES public.categorias(id) ON DELETE RESTRICT;


--
-- TOC entry 5017 (class 2606 OID 131716)
-- Name: inventario fk_inv_ubi; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inventario
    ADD CONSTRAINT fk_inv_ubi FOREIGN KEY (id_ubicacion) REFERENCES public.ubicaciones(id) ON DELETE RESTRICT;


--
-- TOC entry 5030 (class 2606 OID 131895)
-- Name: audit_logs fk_logs_usuario; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT fk_logs_usuario FOREIGN KEY (id_usuario) REFERENCES public.usuarios(id) ON DELETE SET NULL;


--
-- TOC entry 5023 (class 2606 OID 131801)
-- Name: participantes_taller fk_part_persona; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.participantes_taller
    ADD CONSTRAINT fk_part_persona FOREIGN KEY (id_persona) REFERENCES public.personas(id) ON DELETE RESTRICT;


--
-- TOC entry 5024 (class 2606 OID 131796)
-- Name: participantes_taller fk_part_taller; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.participantes_taller
    ADD CONSTRAINT fk_part_taller FOREIGN KEY (id_taller) REFERENCES public.talleres(id) ON DELETE CASCADE;


--
-- TOC entry 5035 (class 2606 OID 139976)
-- Name: pasante_documentos fk_pasante_doc; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pasante_documentos
    ADD CONSTRAINT fk_pasante_doc FOREIGN KEY (id_pasante) REFERENCES public.pasantes(id) ON DELETE CASCADE;


--
-- TOC entry 5034 (class 2606 OID 139959)
-- Name: pasantes fk_pasante_tutor; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pasantes
    ADD CONSTRAINT fk_pasante_tutor FOREIGN KEY (id_tutor_institucional) REFERENCES public.empleados(id) ON DELETE SET NULL;


--
-- TOC entry 5025 (class 2606 OID 131834)
-- Name: puntos_ruta fk_punto_ruta; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.puntos_ruta
    ADD CONSTRAINT fk_punto_ruta FOREIGN KEY (id_ruta) REFERENCES public.rutas(id) ON DELETE CASCADE;


--
-- TOC entry 5028 (class 2606 OID 131879)
-- Name: ruta_inventario fk_ri_inv; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ruta_inventario
    ADD CONSTRAINT fk_ri_inv FOREIGN KEY (id_inventario) REFERENCES public.inventario(id) ON DELETE RESTRICT;


--
-- TOC entry 5029 (class 2606 OID 131874)
-- Name: ruta_inventario fk_ri_ruta; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ruta_inventario
    ADD CONSTRAINT fk_ri_ruta FOREIGN KEY (id_ruta) REFERENCES public.rutas(id) ON DELETE CASCADE;


--
-- TOC entry 5031 (class 2606 OID 139916)
-- Name: taller_informes fk_taller_inf; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.taller_informes
    ADD CONSTRAINT fk_taller_inf FOREIGN KEY (id_taller) REFERENCES public.talleres(id) ON DELETE CASCADE;


--
-- TOC entry 5032 (class 2606 OID 139939)
-- Name: taller_inventario fk_taller_inv_item; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.taller_inventario
    ADD CONSTRAINT fk_taller_inv_item FOREIGN KEY (id_inventario) REFERENCES public.inventario(id) ON DELETE RESTRICT;


--
-- TOC entry 5033 (class 2606 OID 139934)
-- Name: taller_inventario fk_taller_inv_taller; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.taller_inventario
    ADD CONSTRAINT fk_taller_inv_taller FOREIGN KEY (id_taller) REFERENCES public.talleres(id) ON DELETE CASCADE;


--
-- TOC entry 5021 (class 2606 OID 131776)
-- Name: talleres fk_talleres_facilitador; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.talleres
    ADD CONSTRAINT fk_talleres_facilitador FOREIGN KEY (id_facilitador) REFERENCES public.empleados(id) ON DELETE RESTRICT;


--
-- TOC entry 5022 (class 2606 OID 131771)
-- Name: talleres fk_talleres_ubicacion; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.talleres
    ADD CONSTRAINT fk_talleres_ubicacion FOREIGN KEY (id_ubicacion_formacion) REFERENCES public.ubicaciones_formacion(id) ON DELETE SET NULL;


--
-- TOC entry 5012 (class 2606 OID 131638)
-- Name: usuarios fk_usuarios_empleado; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT fk_usuarios_empleado FOREIGN KEY (id_empleado) REFERENCES public.empleados(id) ON DELETE RESTRICT;


--
-- TOC entry 5013 (class 2606 OID 131643)
-- Name: usuarios fk_usuarios_rol; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT fk_usuarios_rol FOREIGN KEY (id_rol) REFERENCES public.roles(id) ON DELETE RESTRICT;


--
-- TOC entry 5036 (class 2606 OID 140003)
-- Name: municipio municipio_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.municipio
    ADD CONSTRAINT municipio_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.usuarios(id) NOT VALID;


--
-- TOC entry 5037 (class 2606 OID 140008)
-- Name: parroquia parroquia_create_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parroquia
    ADD CONSTRAINT parroquia_create_by_fkey FOREIGN KEY (create_by) REFERENCES public.usuarios(id) NOT VALID;


--
-- TOC entry 5038 (class 2606 OID 140018)
-- Name: parroquia parroquia_delete_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parroquia
    ADD CONSTRAINT parroquia_delete_by_fkey FOREIGN KEY (delete_by) REFERENCES public.usuarios(id) NOT VALID;


--
-- TOC entry 5039 (class 2606 OID 139995)
-- Name: parroquia parroquia_id_municipio _fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parroquia
    ADD CONSTRAINT "parroquia_id_municipio _fkey" FOREIGN KEY (id_municipio) REFERENCES public.municipio(id);


--
-- TOC entry 5040 (class 2606 OID 140013)
-- Name: parroquia parroquia_update_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parroquia
    ADD CONSTRAINT parroquia_update_by_fkey FOREIGN KEY (update_by) REFERENCES public.usuarios(id) NOT VALID;


--
-- TOC entry 5008 (class 2606 OID 140046)
-- Name: personas personas_parroquia_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personas
    ADD CONSTRAINT personas_parroquia_id_fkey FOREIGN KEY (parroquia_id) REFERENCES public.parroquia(id) NOT VALID;


--
-- TOC entry 5015 (class 2606 OID 140028)
-- Name: ubicaciones ubicaciones_departamento _d_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ubicaciones
    ADD CONSTRAINT "ubicaciones_departamento _d_fkey" FOREIGN KEY ("departamento _d") REFERENCES public.departamentos(id) NOT VALID;


--
-- TOC entry 5020 (class 2606 OID 140023)
-- Name: ubicaciones_formacion ubicaciones_formacion_parroquia_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ubicaciones_formacion
    ADD CONSTRAINT ubicaciones_formacion_parroquia_fkey FOREIGN KEY (parroquia) REFERENCES public.parroquia(id) NOT VALID;


--
-- TOC entry 5242 (class 0 OID 0)
-- Dependencies: 5
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE USAGE ON SCHEMA public FROM PUBLIC;


-- Completed on 2026-04-28 13:13:41

--
-- PostgreSQL database dump complete
--

