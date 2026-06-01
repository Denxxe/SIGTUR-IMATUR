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
    CONSTRAINT actividad_inventario_tipo_movimiento_check CHECK (((tipo_movimiento)::text = ANY ((ARRAY['Asignacion'::character varying, 'Devolucion'::character varying, 'Traslado'::character varying, 'Baja'::character varying, 'Mantenimiento'::character varying])::text[])))
);


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
    deleted_by integer
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
    CONSTRAINT audit_logs_operacion_check CHECK (((operacion)::text = ANY ((ARRAY['INSERT'::character varying, 'UPDATE'::character varying, 'DELETE'::character varying])::text[])))
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
-- Name: cargos; Type: TABLE; Schema: public; Owner: -
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
    deleted_by integer
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
    tipo_contrato character varying(30) DEFAULT 'Fijo'::character varying,
    fecha_egreso date,
    id_horario integer,
    CONSTRAINT empleados_tipo_contrato_check CHECK (((tipo_contrato)::text = ANY ((ARRAY['Fijo'::character varying, 'Contratado'::character varying, 'Suplente'::character varying, 'Comisión de Servicio'::character varying])::text[])))
);


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
-- Name: instituciones_externas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.instituciones_externas (
    id integer NOT NULL,
    nombre character varying(150) NOT NULL,
    tipo character varying(50) DEFAULT 'Educativa'::character varying,
    es_educativa boolean DEFAULT true,
    municipio character varying(100),
    contacto character varying(100),
    telefono character varying(30),
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by integer,
    updated_at timestamp without time zone,
    updated_by integer,
    deleted_at timestamp without time zone,
    deleted_by integer
);


--
-- Name: instituciones_externas_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.instituciones_externas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: instituciones_externas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.instituciones_externas_id_seq OWNED BY public.instituciones_externas.id;


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
    CONSTRAINT inventario_condicion_check CHECK (((condicion)::text = ANY ((ARRAY['Nuevo'::character varying, 'Bueno'::character varying, 'Regular'::character varying, 'Dañado'::character varying, 'En Reparación'::character varying])::text[])))
);


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
-- Name: oficios; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.oficios (
    id integer NOT NULL,
    numero character varying(50),
    fecha date NOT NULL,
    id_institucion integer,
    asunto character varying(255),
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


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
-- Name: oficios_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.oficios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: oficios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.oficios_id_seq OWNED BY public.oficios.id;


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
    id_institucion integer,
    genero_libre character(1),
    fecha_nac_libre date,
    CONSTRAINT participantes_ruta_genero_libre_check CHECK ((genero_libre = ANY (ARRAY['M'::bpchar, 'F'::bpchar]))),
    CONSTRAINT pr_participante_req CHECK (((id_persona IS NOT NULL) OR (nombre_libre IS NOT NULL)))
);


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
    es_brigadista boolean DEFAULT false NOT NULL,
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
    CONSTRAINT permisos_estado_check CHECK (((estado)::text = ANY ((ARRAY['Pendiente'::character varying, 'Aprobado'::character varying, 'Rechazado'::character varying, 'Anulado'::character varying])::text[]))),
    CONSTRAINT permisos_tipo_check CHECK (((tipo_permiso)::text = ANY ((ARRAY['Médico'::character varying, 'Personal'::character varying, 'Duelo'::character varying, 'Lactancia'::character varying, 'Estudio'::character varying, 'Otro'::character varying])::text[])))
);


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
    CONSTRAINT personas_genero_check CHECK ((genero = ANY (ARRAY['M'::bpchar, 'F'::bpchar])))
);


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
    nombre_facilitador_externo character varying(150) DEFAULT NULL::character varying,
    motivo_mantenimiento text,
    tipo_ruta character varying(50) DEFAULT 'General'::character varying,
    CONSTRAINT rutas_estado_check CHECK (((estado)::text = ANY ((ARRAY['Activa'::character varying, 'Inactiva'::character varying, 'En Mantenimiento'::character varying, 'Finalizada'::character varying])::text[]))),
    CONSTRAINT rutas_tipo_ruta_check CHECK (((tipo_ruta)::text = ANY ((ARRAY['Cumaná Histórica'::character varying, 'Exploradores de Cumaná'::character varying, 'Comunitaria'::character varying, 'General'::character varying])::text[])))
);


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
-- Name: taller_inventario; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.taller_inventario (
    id integer NOT NULL,
    id_taller integer NOT NULL,
    id_inventario integer NOT NULL,
    cantidad integer DEFAULT 1,
    observaciones text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by integer,
    is_active boolean DEFAULT true,
    updated_at timestamp without time zone,
    updated_by integer,
    deleted_at timestamp without time zone,
    deleted_by integer
);


--
-- Name: taller_inventario_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.taller_inventario_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: taller_inventario_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.taller_inventario_id_seq OWNED BY public.taller_inventario.id;


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
    id_oficio integer,
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
    "departamento _d" integer NOT NULL
);


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
    deleted_by integer
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
-- Name: asistencias id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asistencias ALTER COLUMN id SET DEFAULT nextval('public.asistencias_id_seq'::regclass);


--
-- Name: audit_logs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs ALTER COLUMN id SET DEFAULT nextval('public.audit_logs_id_seq'::regclass);


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
-- Name: departamentos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.departamentos ALTER COLUMN id SET DEFAULT nextval('public.departamentos_id_seq'::regclass);


--
-- Name: empleados id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.empleados ALTER COLUMN id SET DEFAULT nextval('public.empleados_id_seq'::regclass);


--
-- Name: horarios id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.horarios ALTER COLUMN id SET DEFAULT nextval('public.horarios_id_seq'::regclass);


--
-- Name: instituciones_externas id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.instituciones_externas ALTER COLUMN id SET DEFAULT nextval('public.instituciones_externas_id_seq'::regclass);


--
-- Name: inventario id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario ALTER COLUMN id SET DEFAULT nextval('public.inventario_id_seq'::regclass);


--
-- Name: municipio id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.municipio ALTER COLUMN id SET DEFAULT nextval('public.municipio_id_seq'::regclass);


--
-- Name: oficios id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.oficios ALTER COLUMN id SET DEFAULT nextval('public.oficios_id_seq'::regclass);


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
-- Name: taller_inventario id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.taller_inventario ALTER COLUMN id SET DEFAULT nextval('public.taller_inventario_id_seq'::regclass);


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
-- Name: horarios horarios_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.horarios
    ADD CONSTRAINT horarios_pkey PRIMARY KEY (id);


--
-- Name: instituciones_externas instituciones_externas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.instituciones_externas
    ADD CONSTRAINT instituciones_externas_pkey PRIMARY KEY (id);


--
-- Name: inventario inventario_codigo_bn_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario
    ADD CONSTRAINT inventario_codigo_bn_key UNIQUE (codigo_bn);


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
-- Name: oficios oficios_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.oficios
    ADD CONSTRAINT oficios_pkey PRIMARY KEY (id);


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
-- Name: taller_inventario taller_inventario_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.taller_inventario
    ADD CONSTRAINT taller_inventario_pkey PRIMARY KEY (id);


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
-- Name: taller_inventario uq_taller_inventario; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.taller_inventario
    ADD CONSTRAINT uq_taller_inventario UNIQUE (id_taller, id_inventario);


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
-- Name: vacaciones vacaciones_id_empleado_anio_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vacaciones
    ADD CONSTRAINT vacaciones_id_empleado_anio_key UNIQUE (id_empleado, anio);


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
-- Name: idx_act_ruta_fecha; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_act_ruta_fecha ON public.actividades_ruta USING btree (fecha);


--
-- Name: idx_asistencias_empleado_fecha; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_asistencias_empleado_fecha ON public.asistencias USING btree (id_empleado, fecha);


--
-- Name: idx_asistencias_fecha; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_asistencias_fecha ON public.asistencias USING btree (fecha);


--
-- Name: idx_inventario_codigo_bn; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_inventario_codigo_bn ON public.inventario USING btree (codigo_bn);


--
-- Name: idx_logs_fecha; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_logs_fecha ON public.audit_logs USING btree (fecha);


--
-- Name: idx_logs_tabla; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_logs_tabla ON public.audit_logs USING btree (tabla_afectada);


--
-- Name: idx_pasantes_persona; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_pasantes_persona ON public.pasantes USING btree (id_persona);


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
-- Name: uq_puntos_ruta_orden; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uq_puntos_ruta_orden ON public.puntos_ruta USING btree (id_ruta, orden) WHERE (is_active = true);


--
-- Name: uq_ruta_informes_ruta; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uq_ruta_informes_ruta ON public.ruta_informes USING btree (id_ruta);


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
-- Name: taller_inventario fk_taller_inv_item; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.taller_inventario
    ADD CONSTRAINT fk_taller_inv_item FOREIGN KEY (id_inventario) REFERENCES public.inventario(id) ON DELETE RESTRICT;


--
-- Name: taller_inventario fk_taller_inv_taller; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.taller_inventario
    ADD CONSTRAINT fk_taller_inv_taller FOREIGN KEY (id_taller) REFERENCES public.talleres(id) ON DELETE CASCADE;


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
-- Name: oficios oficios_id_institucion_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.oficios
    ADD CONSTRAINT oficios_id_institucion_fkey FOREIGN KEY (id_institucion) REFERENCES public.ubicaciones_formacion(id) ON DELETE RESTRICT;


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
-- Name: participantes_ruta participantes_ruta_id_institucion_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.participantes_ruta
    ADD CONSTRAINT participantes_ruta_id_institucion_fkey FOREIGN KEY (id_institucion) REFERENCES public.instituciones_externas(id) ON DELETE SET NULL;


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
-- Name: talleres talleres_id_oficio_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.talleres
    ADD CONSTRAINT talleres_id_oficio_fkey FOREIGN KEY (id_oficio) REFERENCES public.oficios(id) ON DELETE SET NULL;


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


-- ============================================================================
-- SEEDS DE SISTEMA (roles, permisos_rol, configuracion_sistema)
-- ============================================================================
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
-- Data for Name: configuracion_sistema; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.configuracion_sistema (id, clave, valor, descripcion, updated_at, updated_by) FROM stdin;
1	director_nombre	Maria	Nombre del Director/Presidente de IMATUR	2026-05-30 21:34:12.333445	2
2	director_apellido	Maza	Apellido del Director/Presidente	2026-05-30 21:34:12.372191	2
3	director_cargo	Director	Cargo del firmante institucional	2026-05-30 21:34:12.413043	2
4	resolucion_numero	025	N° de la Resolución de nombramiento	2026-05-30 21:34:12.453349	2
5	resolucion_fecha	15 de marzo de 2024	Fecha de la Resolución (texto, ej: 15 de enero de 2025)	2026-05-30 21:34:12.493806	2
10	correlativo_oficio_ruta	1	Último correlativo de oficio emitido en el año en curso	2026-05-08 16:57:20.880114	2
11	ano_correlativo_ruta	2026	Año del correlativo activo (se reinicia automáticamente)	2026-05-08 16:57:20.940974	2
12	firmante_cargo	Director General	\N	2026-05-20 10:35:25.894766	\N
13	correlativo_oficio_formacion	0	\N	2026-05-20 10:35:25.894766	\N
14	ano_correlativo_formacion	2026	\N	2026-05-20 10:35:25.894766	\N
6	gaceta_numero	042	N° de la Gaceta Municipal Extraordinaria	2026-05-30 21:34:12.531695	2
7	gaceta_fecha	20 de Enero del 2024	Fecha de la Gaceta (texto, ej: 20 de enero de 2025)	2026-05-30 21:34:12.568367	2
8	telf_institucion	(0293) 431-4073	Teléfono institucional	2026-05-30 21:34:12.604648	2
9	correo_institucion	imatur.cumana@gmail.com	Correo electrónico institucional	2026-05-30 21:34:12.646117	2
15	meta_talleres_anio	100	Meta anual de actividades formativas a ejecutar	2026-05-30 21:34:12.687781	2
16	meta_rutas_anio	100	Meta anual de rutas turísticas a ejecutar	2026-05-30 21:34:12.732515	2
17	dias_preaviso_contrato	30	Días de anticipación para alertar sobre contratos vencientes	2026-05-30 21:34:12.803157	2
18	dias_preaviso_pasante	15	Días de anticipación para alertar sobre pasantes próximos a culminar	2026-05-30 21:34:12.844755	2
\.


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.roles (id, nombre, descripcion, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by) FROM stdin;
2	RRHH	Gestión de personal y asistencia	t	2026-04-12 14:15:24.492607	\N	\N	\N	\N	\N
3	Turismo	Gestión de rutas y formación	t	2026-04-12 14:15:24.492607	\N	\N	\N	\N	\N
5	Recepción	Registro de visitantes, visitas y marcaje de asistencias. Sin acceso a módulos de gestión.	t	2026-05-20 10:35:25.876512	\N	\N	\N	\N	\N
4	Inventario	Gestión de bienes institucionales	t	2026-04-12 14:15:24.492607	2026-05-26 02:31:55.886056	\N	\N	2	\N
1	Administrador	Acceso total al sistema	t	2026-04-12 14:15:24.492607	2026-05-26 15:14:08.526225	\N	\N	2	\N
6	Reportes Del Sistema	Prueba para verificar el insert del rol y otrogar permisos	t	2026-05-26 02:33:32.316047	2026-05-26 20:53:54.119892	\N	2	2	\N
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
30	6	ReportesController	2026-05-26 02:33:46.506516	2
31	6	DashboardController	2026-05-26 02:33:46.506516	2
37	5	AsistenciasController	2026-05-26 16:29:21.945685	2
38	5	VisitantesController	2026-05-26 16:29:21.945685	2
40	5	DashboardController	2026-05-26 16:29:21.945685	2
41	2	ReportesController	2026-05-26 20:54:16.748618	2
42	2	ConfigController	2026-05-26 20:54:16.748618	2
43	2	EmpleadosController	2026-05-26 20:54:16.748618	2
44	2	CargosController	2026-05-26 20:54:16.748618	2
45	2	DepartamentosController	2026-05-26 20:54:16.748618	2
46	2	AsistenciasController	2026-05-26 20:54:16.748618	2
47	2	VisitantesController	2026-05-26 20:54:16.748618	2
49	2	PasantesController	2026-05-26 20:54:16.748618	2
50	2	UsuariosController	2026-05-26 20:54:16.748618	2
51	2	DashboardController	2026-05-26 20:54:16.748618	2
\.


--
-- Name: configuracion_sistema_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.configuracion_sistema_id_seq', 20, true);


--
-- Name: permisos_rol_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.permisos_rol_id_seq', 53, true);


--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.roles_id_seq', 6, true);


--
-- PostgreSQL database dump complete
--

