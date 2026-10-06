# Diagrama de Componentes y Despliegue

**SIGTUR-IMATUR** · **Última actualización:** 2026-09-19

> **Para qué sirve.** Insumo para dibujar el **diagrama de componentes** (UML) y el **diagrama de
> despliegue**. Muestra cómo está dividido el sistema en piezas desplegables, qué interfaces expone
> y consume cada una, y en qué nodos físicos se ejecutan.

---

## Índice

1. [Vista de componentes de alto nivel](#1-vista-de-componentes-de-alto-nivel)
2. [Componentes internos de la aplicación](#2-componentes-internos-de-la-aplicación)
3. [Interfaces entre componentes](#3-interfaces-entre-componentes)
4. [Componentes por módulo funcional](#4-componentes-por-módulo-funcional)
5. [Componentes de terceros (vendorizados)](#5-componentes-de-terceros-vendorizados)
6. [Diagrama de despliegue](#6-diagrama-de-despliegue)
7. [Estructura física de directorios](#7-estructura-física-de-directorios)
8. [Dependencias externas y degradación](#8-dependencias-externas-y-degradación)

---

## 1. Vista de componentes de alto nivel

```mermaid
flowchart TB
    subgraph CLI["Cliente"]
        NAV["Navegador web<br/>Chrome / Edge / Firefox"]
    end

    subgraph SRV["Servidor de aplicacion (Apache + PHP 8)"]
        FC["&lt;&lt;component&gt;&gt;<br/>Front Controller<br/>public/index.php"]
        CORE["&lt;&lt;component&gt;&gt;<br/>Nucleo MVC<br/>Router · Controller · Model · Database"]
        SEC["&lt;&lt;component&gt;&gt;<br/>Seguridad<br/>RBAC · sesion · token · auditoria"]
        MOD["&lt;&lt;component&gt;&gt;<br/>Modulos de negocio<br/>RRHH · Nomina · Formacion<br/>Turismo · Bienes · Recepcion"]
        REP["&lt;&lt;component&gt;&gt;<br/>Reportes e Indicadores"]
        DOC["&lt;&lt;component&gt;&gt;<br/>Generacion documental<br/>imprimibles · XLSX · PDF · QR"]
        FIL["&lt;&lt;component&gt;&gt;<br/>Gestor de archivos<br/>DescargaController"]
        MAIL["&lt;&lt;component&gt;&gt;<br/>Correo<br/>mail_helper + PHPMailer"]
    end

    subgraph BAT["Procesos por lotes (CLI)"]
        CRON1["&lt;&lt;component&gt;&gt;<br/>actualizar_estados.php"]
        CRON2["&lt;&lt;component&gt;&gt;<br/>respaldo_bd.php"]
    end

    subgraph DAT["Almacenamiento"]
        PG[("PostgreSQL 17<br/>SIGTUR-IMATUR")]
        ST["storage/uploads<br/>storage/backups<br/>(fuera de la raiz web)"]
    end

    subgraph EXT["Servicios externos (opcionales)"]
        SMTP["Servidor SMTP"]
        BCV["Sitio del BCV"]
    end

    NAV -- HTTP/HTTPS --> FC
    FC --> CORE
    CORE --> SEC
    CORE --> MOD
    MOD --> REP
    MOD --> DOC
    MOD --> FIL
    SEC --> MAIL
    CORE -- PDO --> PG
    FIL --> ST
    CRON1 -- PDO --> PG
    CRON2 -- pg_dump --> PG
    CRON2 --> ST
    MAIL -. SMTP .-> SMTP
    MOD -. HTTPS, opcional .-> BCV
```

**Lectura:** el sistema es un **monolito modular**. No hay microservicios ni API pública: la única
frontera de red es el navegador contra Apache, más dos salidas **opcionales** (SMTP y BCV) que
degradan sin interrumpir el servicio.

---

## 2. Componentes internos de la aplicación

```mermaid
flowchart LR
    subgraph PUB["public/ (unica raiz web)"]
        IDX["index.php<br/>Front Controller"]
        AST["assets/<br/>css · js · libs · images"]
    end

    subgraph APP["app/ (NO accesible por web)"]
        subgraph CORE["core/"]
            RT["Router.php"]
            DBC["Database.php"]
            CT["Controller.php"]
            MD["Model.php"]
            UT["Util.php"]
            TB["TasaBcv.php"]
            XL["XlsxMultiSheet.php · XlsxLogos.php"]
        end
        CTRL["controllers/<br/>33 controladores + 8 traits"]
        MODL["models/<br/>50 modelos"]
        VIEW["views/<br/>vistas por modulo + inc/"]
        HLP["helpers/<br/>session_helper · mail_helper"]
        LIB["libs/PHPMailer"]
    end

    subgraph CFG["config/"]
        CF["config.php (no versionado)"]
        CE["config.example.php"]
    end

    subgraph DBF["database/"]
        SC["schema_consolidado.sql"]
        MG["migrations/ 001-084"]
    end

    subgraph STO["storage/ (NO accesible por web)"]
        UP["uploads/<br/>expedientes · fotos · pasantes<br/>talleres · bienes · rutas"]
        BK["backups/"]
    end

    subgraph CRN["cron/"]
        C1["actualizar_estados.php"]
        C2["respaldo_bd.php"]
        C3["instalar_tareas.ps1"]
    end

    subgraph TST["tests/"]
        TR["run.php"]
    end

    IDX --> CF
    IDX --> HLP
    IDX --> RT
    RT --> CTRL
    CTRL --> MODL
    CTRL --> VIEW
    MODL --> DBC
    CTRL --> UT
    CTRL --> XL
    MODL --> TB
    HLP --> LIB
    VIEW --> AST
    CTRL --> UP
    C2 --> BK
```

### 2.1 Responsabilidad de cada componente

| Componente | Responsabilidad | Interfaz que expone |
|---|---|---|
| **Front Controller** (`public/index.php`) | Cargar configuración, endurecer la cookie de sesión, registrar el autoload, capturar excepciones no controladas y arrancar el `Router` | HTTP |
| **Router** | Parsear `/controlador/metodo/parametro`, aplicar los 5 middlewares y despachar | Interna: `call_user_func_array` |
| **Controller (base)** | Cargar vistas y modelos, sanitizar POST, validar correo/teléfono/RIF, guardar fotos | `model()`, `view()`, `getUserId()` |
| **Model (base)** | Acceso a `Database` y **auditoría automática** con diff completo | `audit()`, `auditStatic()` |
| **Database** | Único punto de acceso a PostgreSQL: sentencias preparadas y transacciones | `query`, `bind`, `execute`, `resultSet`, `single`, `beginTransaction` |
| **Seguridad** | RBAC dinámico, expiración de sesión, revalidación de cuenta, token anti doble-envío, bitácora | `RolesController::getMapaRbac()`, `sigtur_token_*`, `AuditLog::log()` |
| **Módulos de negocio** | Cada uno: un controlador, N modelos y sus vistas | URLs `/modulo/accion` |
| **Reportes** | ~40 consultas agregadas con filtros, KPIs e indicadores | URLs `/reportes/*` |
| **Generación documental** | Vistas imprimibles sin layout, Excel multi-hoja, PDF, etiquetas QR | URLs + descarga de archivo |
| **Gestor de archivos** | Servir archivos de `storage/uploads/` **validando el rol** por tipo de recurso | `DescargaController::{expediente,foto,pasante,taller,bien,bm1,acta,oficioRuta,permisoRuta,comprobantePago,fotoBien}` |
| **Correo** | Envío SMTP con PHPMailer vendorizado (sin Composer) | `sigtur_enviar_correo()` |
| **Procesos por lotes** | Auto-transición de estados de talleres y respaldo diario | CLI |

> ⚠️ **`public/uploads/` no existe y no debe reintroducirse.** Todo archivo subido va a
> `storage/uploads/` y se sirve por `DescargaController`, que valida el rol. Dentro de `public/`
> sería legible por URL sin control de acceso alguno.

---

## 3. Interfaces entre componentes

```mermaid
flowchart LR
    NAV(["Navegador"])
    FC["Front Controller"]
    RTR["Router"]
    RBAC["RBAC"]
    CTL["Controlador de modulo"]
    MOD["Modelo"]
    DB["Database"]
    PG[("PostgreSQL")]
    AUD["AuditLog"]
    FS["storage/uploads"]

    NAV -->|"IHttp: GET/POST /ctrl/metodo/param"| FC
    FC -->|"IDespacho"| RTR
    RTR -->|"IPermisos: getMapaRbac()"| RBAC
    RTR -->|"IAccion: metodo publico"| CTL
    CTL -->|"IDominio: find/save/delete/reglas"| MOD
    MOD -->|"IPersistencia: query/bind/execute"| DB
    DB -->|"PDO pgsql"| PG
    MOD -->|"IAuditoria: log()"| AUD
    AUD --> DB
    CTL -->|"IArchivo: mover/validar MIME"| FS
    CTL -->|"IVista: render sin logica"| NAV
```

| Interfaz | Provee | Consume | Contrato |
|---|---|---|---|
| **IHttp** | Front Controller | Navegador | URL amigable `/controlador/metodo/parametro`; sesión por cookie endurecida |
| **IPermisos** | `RolesController` | `Router`, `header.php` | `getMapaRbac()` devuelve `[idRol => '*' \| [controladores]]`. **Fuente única**: el menú y el control de acceso usan el mismo mapa |
| **IAccion** | Controladores | `Router` | Solo métodos **públicos, no estáticos y no mágicos** son despachables |
| **IDominio** | Modelos | Controladores | Devuelven datos o lanzan `Exception` con mensaje apto para el usuario. **Nunca imprimen ni redirigen** |
| **IPersistencia** | `Database` | Modelos | Sentencias preparadas obligatorias; transacciones explícitas |
| **IAuditoria** | `AuditLog` | `Model` | Toda escritura registra estado previo y posterior. **Nunca registra contraseñas** |
| **IArchivo** | `DescargaController` | Navegador | Valida rol por tipo de recurso antes de entregar el archivo |
| **IVista** | `views/` | Controladores | Recibe datos ya resueltos; **no consulta la base de datos** |

---

## 4. Componentes por módulo funcional

```mermaid
flowchart TB
    subgraph SIS["Sistema y Seguridad"]
        S1["Auth"]
        S2["Usuarios"]
        S3["Roles y Permisos"]
        S4["Auditoria y Papelera"]
        S5["Configuracion"]
        S6["Perfil"]
        S7["Busqueda global"]
        S8["Descargas"]
        S9["Exportacion transversal"]
    end
    subgraph RH["Recursos Humanos"]
        R1["Empleados y expediente"]
        R2["Organigrama y cargos"]
        R3["Horarios"]
        R4["Asistencia"]
        R5["Permisos y reposos"]
        R6["Vacaciones y feriados"]
        R7["Disciplina"]
        R8["Constancias y carnet"]
    end
    subgraph NM["Nomina"]
        N1["Parametros del mes"]
        N2["Datos salariales"]
        N3["Nomina quincenal"]
        N4["Bono vacacional"]
    end
    subgraph FR["Formacion"]
        F1["Talleres y participantes"]
        F2["Informes y evidencias"]
        F3["Pasantes"]
        F4["Sedes de formacion"]
    end
    subgraph TR["Turismo"]
        T1["Catalogo de recorridos"]
        T2["Salidas"]
        T3["Itinerario y personal"]
        T4["Permisos a custodios"]
        T5["Cobro y pagos"]
        T6["Ficha institucional"]
    end
    subgraph BN["Bienes"]
        B1["Inventario y codificacion"]
        B2["Movimientos"]
        B3["Mantenimiento"]
        B4["Conteos"]
        B5["Relaciones y actas"]
        B6["Etiquetas y suficiencia"]
    end
    subgraph RC["Recepcion"]
        C1["Visitantes"]
        C2["Visitas"]
    end
    subgraph AN["Analisis"]
        A1["Dashboard"]
        A2["Reportes"]
        A3["Indicadores"]
        A4["Centro de alertas"]
    end

    RH --> NM
    RH --> TR
    RH --> FR
    RH --> BN
    RH --> RC
    SIS --> RH
    SIS --> NM
    SIS --> FR
    SIS --> TR
    SIS --> BN
    SIS --> RC
    RH --> AN
    NM --> AN
    FR --> AN
    TR --> AN
    BN --> AN
    RC --> AN
```

> **La dependencia crítica es RRHH.** Todos los módulos operativos referencian `empleados`
> (facilitador, tutor, guía, encargado, responsable, aprobador, empleado visitado). Sin personal
> cargado, el resto del sistema no puede operar.

---

## 5. Componentes de terceros (vendorizados)

**Restricción de arquitectura: despliegue sin internet ⇒ ninguna dependencia por CDN.**
Todo vive en el repositorio, sin Composer ni npm.

| Componente | Versión / archivo | Uso | Ubicación |
|---|---|---|---|
| **Bootstrap** | 5.3 — `bootstrap.min.css`, `bootstrap.bundle.min.js` | Maquetación y componentes de interfaz | `public/assets/libs/` |
| **Bootstrap Icons** | `bootstrap-icons.min.css` + `.woff`, `.woff2`, `.svg` | Iconografía **100 % local** | `public/assets/libs/` |
| **ApexCharts** | `apexcharts.min.js` | Gráficos de indicadores y dashboard | `public/assets/libs/` |
| **Leaflet + OSM** | `leaflet.min.js`, `leaflet.min.css` | Mapa de las paradas de las rutas | `public/assets/` |
| **QRCode.js** | `qrcode.min.js` | Etiquetas QR de bienes, **generadas en el navegador sin internet** | `public/assets/libs/` |
| **PHPMailer** | `app/libs/PHPMailer/` | Envío SMTP del correo de recuperación | `app/libs/` |

**Componentes propios que sustituyen a librerías externas:**

| Componente | Sustituye a | Motivo |
|---|---|---|
| `XlsxMultiSheet` / `XlsxLogos` | PhpSpreadsheet | Generar XLSX multi-hoja con membrete sin Composer |
| `sigtur-validations.js` | Librería de validación | Validación de cédula, teléfono, correo y RIF con **el mismo criterio** que el servidor |
| `sigturExportarTabla` | DataTables Buttons | Exportación Excel/PDF transversal de cualquier listado `data-tabla-buscable` |
| `sigtur_token_emitir/consumir` | Middleware CSRF de framework | Idempotencia de formularios |

---

## 6. Diagrama de despliegue

```mermaid
flowchart TB
    subgraph LAN["Red local de IMATUR (sin salida a internet requerida)"]
        subgraph N1["&lt;&lt;device&gt;&gt; Estacion de trabajo"]
            NAV["&lt;&lt;browser&gt;&gt;<br/>Chrome / Edge / Firefox"]
            IMP["&lt;&lt;device&gt;&gt;<br/>Impresora<br/>(carnet CR80 · etiquetas)"]
        end

        subgraph N2["&lt;&lt;device&gt;&gt; Servidor IMATUR (Windows / Linux)"]
            subgraph WS["&lt;&lt;executionEnvironment&gt;&gt; Apache + PHP 8.0+"]
                APP["&lt;&lt;artifact&gt;&gt;<br/>SIGTUR-IMATUR<br/>DocumentRoot = public/"]
                EXT["Extensiones: pdo_pgsql · zip · fileinfo"]
            end
            subgraph DBE["&lt;&lt;executionEnvironment&gt;&gt; PostgreSQL 17"]
                DBI["&lt;&lt;database&gt;&gt;<br/>SIGTUR-IMATUR<br/>69 tablas"]
            end
            subgraph SCH["&lt;&lt;executionEnvironment&gt;&gt; Programador de tareas"]
                TK1["SIGTUR-Estados<br/>cada 10 min"]
                TK2["SIGTUR-Respaldo<br/>diario 23:00"]
            end
            FSY["&lt;&lt;artifact&gt;&gt;<br/>storage/<br/>uploads · backups"]
        end
    end

    subgraph WAN["Internet (opcional)"]
        SMTP["&lt;&lt;device&gt;&gt;<br/>Servidor SMTP"]
        BCV["&lt;&lt;device&gt;&gt;<br/>Sitio del BCV"]
    end

    NAV -- "HTTP/HTTPS :80/:443" --> WS
    NAV -- "imprime" --> IMP
    WS -- "PDO/TCP :5432" --> DBE
    WS -- "lectura/escritura" --> FSY
    TK1 -- "PHP CLI" --> DBE
    TK2 -- "pg_dump" --> DBE
    TK2 --> FSY
    WS -. "SMTP, opcional" .-> SMTP
    WS -. "HTTPS, opcional" .-> BCV
```

### 6.1 Especificación de nodos

| Nodo | Requisitos | Notas |
|---|---|---|
| **Estación de trabajo** | Navegador actualizado; JavaScript habilitado | Impresora con soporte **CR80 vertical (54 × 85,6 mm)** para carnets |
| **Servidor de aplicación** | PHP **8.0+** con `pdo_pgsql`, `zip`, `fileinfo`; Apache con `DocumentRoot` apuntando a **`public/`** | En desarrollo se usa Laragon (Windows) |
| **Servidor de base de datos** | **PostgreSQL 16/17**, puerto 5432 | Puede convivir en el mismo equipo |
| **Programador de tareas** | `schtasks` (Windows) o `cron` (Linux); PHP CLI y `pg_dump` accesibles | Se instala con `cron/instalar_tareas.ps1` (idempotente) |
| **Almacenamiento** | `storage/uploads/` y `storage/backups/`, **fuera de la raíz web**, con permisos de escritura del usuario del servicio web | No versionados |

### 6.2 Configuración por entorno

Todo lo específico del entorno vive en **`config/config.php`**, que **no se versiona**:

| Constante | Qué define |
|---|---|
| `URL_ROOT` | URL base del sitio, sin barra final |
| `DB_HOST` · `DB_PORT` · `DB_NAME` · `DB_USER` · `DB_PASS` | Conexión a PostgreSQL |
| `APP_DEBUG` | **`false` en producción** — oculta errores al usuario y los registra en el log |
| `SESSION_TIMEOUT` | Expiración por inactividad (1800 s = 30 min) |
| `PG_DUMP_PATH` · `BACKUP_RETENTION` | Respaldo automático y rotación |
| `SMTP_*` | Servidor de correo saliente |

### 6.3 Procedimiento de despliegue

```bash
# 1. Crear la base
createdb -U postgres "SIGTUR-IMATUR"

# 2. Importar el esquema consolidado — UN SOLO ARCHIVO, es toda la base
psql -U postgres -d "SIGTUR-IMATUR" -f database/schema_consolidado.sql

# 3. Configurar el entorno
cp config/config.example.php config/config.php     # y editarlo

# 4. Apuntar el DocumentRoot del servidor web a public/

# 5. Instalar las dos tareas programadas
powershell -ExecutionPolicy Bypass -File cron\instalar_tareas.ps1

# 6. Ingresar y CAMBIAR la contraseña del administrador de arranque
```

> **No hay paso de migraciones.** `schema_consolidado.sql` es autosuficiente: incluye el esquema
> base, todas las migraciones, los catálogos institucionales sembrados y un usuario administrador
> de arranque. `database/migrations/` queda como historial y para **actualizar** instalaciones
> antiguas.

---

## 7. Estructura física de directorios

```
SIGTUR-IMATUR/
├── public/                  ← ÚNICA raíz web
│   ├── index.php            Front Controller
│   └── assets/
│       ├── css/             sigtur-tokens · sigtur-components · login · leaflet
│       ├── js/              sigtur-validations · leaflet
│       ├── libs/            bootstrap · bootstrap-icons · apexcharts · qrcode
│       └── images/
├── app/                     ← NO accesible por web
│   ├── core/                Router · Database · Controller · Model · Util · TasaBcv · Xlsx*
│   ├── controllers/         33 controladores
│   │   └── reportes/        8 traits
│   ├── models/              50 modelos
│   ├── views/               vistas por módulo + inc/header.php · inc/footer.php
│   ├── helpers/             session_helper · mail_helper
│   └── libs/PHPMailer/
├── config/
│   ├── config.example.php   plantilla versionada
│   └── config.php           ← NO versionado (credenciales)
├── database/
│   ├── schema_consolidado.sql
│   └── migrations/          001 … 083
├── cron/
│   ├── actualizar_estados.php
│   ├── respaldo_bd.php
│   └── instalar_tareas.ps1
├── storage/                 ← NO accesible por web, NO versionado
│   ├── uploads/             expedientes · fotos · pasantes · talleres · bienes · rutas
│   └── backups/
├── tests/run.php
└── docs/                    documentación técnica y de negocio
```

### 7.1 Reglas de seguridad de la estructura

1. El servidor web debe servir **solo `public/`**. Si `app/`, `config/`, `storage/` o `database/`
   quedan accesibles por URL, se exponen credenciales, documentos personales y el esquema completo.
2. **`config/config.php` no se versiona** (`.gitignore`): contiene credenciales.
3. **`storage/uploads/` nunca dentro de `public/`.** Se sirve por `DescargaController`, que valida
   el rol antes de entregar cada archivo.
4. Las subidas validan **extensión, tamaño (≤ 5 MB) y tipo MIME real**.

---

## 8. Dependencias externas y degradación

| Dependencia | Criticidad | Si falla |
|---|---|---|
| **PostgreSQL** | 🔴 **Crítica** | El sistema no opera. El error se registra en el log y al usuario se le muestra un mensaje genérico (nunca el detalle de conexión) |
| **Tarea `SIGTUR-Estados`** | 🟡 Alta | Los talleres se quedan en *Programado* aunque su fecha ya pasó |
| **Tarea `SIGTUR-Respaldo`** | 🟡 Alta | No hay copias automáticas de la base |
| **Servidor SMTP** | 🟢 Baja | Solo falla la recuperación de contraseña por correo; el Administrador puede restablecerla desde *Sistema → Usuarios* |
| **Sitio del BCV** | 🟢 Muy baja | La tasa del dólar **se carga a mano**. El cálculo de nómina **nunca** sale a internet: usa la tasa congelada del período |
| **Internet** | 🟢 No requerida | Todas las librerías, el mapa y la generación de QR funcionan **en local** |

### 8.1 Puntos únicos de fallo y su mitigación

| Punto | Mitigación |
|---|---|
| Base de datos | Respaldo diario con rotación; restauración con un solo comando `psql` |
| `config/config.php` | Plantilla versionada (`config.example.php`) para reconstruirlo |
| Archivos subidos | Incluir `storage/uploads/` en el respaldo del servidor (**no** lo cubre `pg_dump`) |
| Correlativos de oficio | Incremento atómico dentro de transacción: dos usuarios simultáneos no obtienen el mismo número |
| Sesión del usuario | Revalidación del estado de la cuenta en **cada petición**: suspender a alguien tiene efecto inmediato |

> ⚠️ **El respaldo de `pg_dump` NO incluye los archivos subidos.** Un plan de recuperación completo
> debe copiar también `storage/uploads/`.
