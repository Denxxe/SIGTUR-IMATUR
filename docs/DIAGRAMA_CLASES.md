# Diagrama de Clases

**SIGTUR-IMATUR** · MVC en PHP 8 sin framework · **Última actualización:** 2026-09-19
**Inventario:** 8 clases de núcleo · 33 controladores (+8 traits de reportes) · 50 modelos

> **Para qué sirve.** Insumo para dibujar el **diagrama de clases** del sistema. Presenta primero
> la **arquitectura de capas** (las clases base que todo lo demás hereda), luego los diagramas por
> módulo con atributos y operaciones relevantes, y por último las tablas de referencia con **todas**
> las clases y sus responsabilidades.
>
> **Criterio de recorte:** se documentan los métodos **públicos que representan comportamiento de
> negocio**. Los CRUD homogéneos (`all`, `find`, `save`, `delete`) se declaran una sola vez en la
> plantilla de `Model` y no se repiten en cada clase.

---

## Índice

1. [Arquitectura de capas](#1-arquitectura-de-capas)
2. [Clases del núcleo](#2-clases-del-núcleo)
3. [Patrón de controlador y modelo](#3-patrón-de-controlador-y-modelo)
4. [Diagrama de clases — Seguridad y Sistema](#4-diagrama-de-clases--seguridad-y-sistema)
5. [Diagrama de clases — RRHH](#5-diagrama-de-clases--rrhh)
6. [Diagrama de clases — Nómina](#6-diagrama-de-clases--nómina)
7. [Diagrama de clases — Formación](#7-diagrama-de-clases--formación)
8. [Diagrama de clases — Turismo](#8-diagrama-de-clases--turismo)
9. [Diagrama de clases — Bienes](#9-diagrama-de-clases--bienes)
10. [Diagrama de clases — Recepción y Reportes](#10-diagrama-de-clases--recepción-y-reportes)
11. [Catálogo completo de clases](#11-catálogo-completo-de-clases)
12. [Patrones y convenciones de diseño](#12-patrones-y-convenciones-de-diseño)

---

## 1. Arquitectura de capas

```mermaid
flowchart TB
    subgraph L1["Capa de presentacion"]
        V["Vistas PHP<br/>app/views/**<br/>inc/header.php · inc/footer.php"]
        JS["sigtur-validations.js<br/>Bootstrap · ApexCharts · Leaflet · QRCode"]
    end
    subgraph L2["Capa de control"]
        R["Router"]
        C["Controller (base abstracta)"]
        CC["33 controladores concretos"]
        T["8 traits de reportes"]
    end
    subgraph L3["Capa de dominio"]
        M["Model (base abstracta)"]
        MM["50 modelos de dominio"]
        U["Util · TasaBcv · XlsxMultiSheet · XlsxLogos"]
    end
    subgraph L4["Capa de persistencia"]
        DB["Database (envoltorio PDO)"]
        PG[("PostgreSQL 17")]
    end
    subgraph L5["Transversal"]
        H["session_helper · mail_helper"]
        AL["AuditLog"]
    end

    JS --- V
    V --> CC
    R --> CC
    CC -->|hereda de| C
    CC --> MM
    T -.usado por.-> CC
    MM -->|hereda de| M
    M --> DB
    MM --> U
    DB --> PG
    CC --> H
    M --> AL
    AL --> DB
```

**Reglas de la arquitectura:**
- La **vista nunca toca la base de datos**: recibe datos ya resueltos por el controlador.
- El **modelo nunca imprime**: devuelve datos u lanza excepciones.
- El **controlador no construye SQL**: delega en el modelo.
- `Database` es el **único** punto que habla con PDO.

---

## 2. Clases del núcleo

```mermaid
classDiagram
    class Router {
        #string currentController
        #string currentMethod
        #array params
        +__construct()
        -esDespachable(ctrl, metodo) bool
        +getUrl() array
    }
    note for Router "Front controller.\nResuelve /controlador/metodo/parametro.\nAplica 4 middlewares en orden:\n1) autenticacion\n2) expiracion de sesion\n3) estado de cuenta + RBAC\n4) token anti doble-envio"

    class Database {
        -string host
        -string port
        -string dbname
        -PDO dbh
        -PDOStatement stmt
        +__construct(pdoInstance)
        +getHandler() PDO
        +compartida()$ Database
        +query(sql)
        +bind(param, value, type)
        +execute() bool
        +resultSet() array
        +single() object
        +rowCount() int
        +beginTransaction()
        +endTransaction()
        +cancelTransaction()
        +inTransaction() bool
    }

    class Controller {
        #model(nombre) Model
        #view(ruta, data)
        #getUserId() int
        #getEmpleadoId() int
        #emailValido(email) bool
        #telefonoValido(tel) bool
        #rifValido(rif) bool
        #normalizarRif(rif) string
        #guardarFotoPersona(idPersona)
        #sanitizePost() array
    }
    note for Controller "Todos los helpers son protected A PROPOSITO:\nel Router despacha cualquier metodo PUBLICO\ncomo si fuera una URL."

    class Model {
        #Database db
        +__construct()
        #audit(tabla, op, id, previos, nuevos, user)
        #auditStatic(tabla, op, id, previos, nuevos, user)$
        -fetchFullRow(pdo, tabla, id)$ array
        -toArray(valor)$ array
    }

    class Util {
        +edad(fechaNac)$ int
        +edadTexto(fechaNac)$ string
        +numeroALetras(n)$ string
        +montoALetras(monto, moneda)$ string
        +fechaEnLetras(fecha)$ string
    }

    class TasaBcv {
        +consultar()$ array
        -descargar()$ string
        -extraerTasa(html)$ float
        -extraerFechaValor(html)$ string
    }
    note for TasaBcv "Solo SUGIERE la tasa.\nUn fallo de red nunca bloquea:\nla tasa se carga a mano."

    class XlsxMultiSheet {
        +nuevaHoja(nombre)
        +membrete(titulo, subtitulo)
        +filaFusionada(texto, estilo)
        +filaCeldas(celdas, estilo)
        +filaVacia()
        +cerrarHoja()
        +construir() string
        +descargar(nombreArchivo)
    }

    class XlsxLogos {
        -logos()$ array
        +piezasParaHoja(hoja)$ array
    }

    Router ..> Controller : despacha
    Controller ..> Model : instancia
    Model --> Database : usa
    XlsxMultiSheet ..> XlsxLogos : usa
```

### 2.1 Middlewares del `Router` (orden de ejecución)

| # | Middleware | Efecto si falla |
|---|---|---|
| 1 | **Autenticación** — ¿hay `user_id` en sesión? | Redirige a `AuthController::login` |
| 2 | **Expiración por inactividad** (30 min) | Destruye la sesión y redirige con aviso |
| 3 | **Estado de cuenta** — relee `usuarios` por clave primaria en cada petición | Si la cuenta quedó inactiva, cierra sesión en el acto. Si cambió de rol, aplica el nuevo sin esperar a un nuevo inicio de sesión |
| 4 | **RBAC** — `RolesController::getMapaRbac()` | Redirige a `DashboardController::accesoDenegado` |
| 5 | **Token anti doble-envío** en POST | Ignora la operación y avisa *"Solicitud duplicada o expirada"* |

> **Exentos del token (a propósito):** `AuthController` (la vista de login no lleva footer),
> `ExportarController` (no escribe nada) y los métodos `marcarAsistencia`,
> `marcarAsistenciaMasiva` y `marcarAlertasVistas` (idempotentes por diseño).
>
> **Acceso siempre permitido a todo usuario autenticado:** `PerfilController`, `BuscarController`,
> `DescargaController` y `ExportarController` — cada uno aplica su propia restricción por recurso.

---

## 3. Patrón de controlador y modelo

Todo módulo del sistema instancia la misma estructura. En el diagrama de clases general basta con
dibujarla **una vez** como plantilla y luego listar las clases concretas.

```mermaid
classDiagram
    class Controller {
        <<abstract>>
    }
    class XController {
        +index()
        +store()
        +detalle(id)
        +delete(id)
    }
    class Model {
        <<abstract>>
    }
    class XModel {
        +all(filtros)$ array
        +find(id)$ object
        +save(datos, userId)$ int
        +delete(id, userId)$ bool
        +ESTADOS$ array
    }
    Controller <|-- XController
    Model <|-- XModel
    XController ..> XModel : usa
    XController ..> Vista : renderiza
```

**Convenciones que se repiten en todos los modelos:**

| Elemento | Convención |
|---|---|
| `all($filtros)` | Listado con `WHERE is_active = TRUE` y los JOIN necesarios |
| `paginate($pagina, $filtros)` | Variante paginada para listados extensos |
| `find($id)` | Un registro por clave primaria |
| `save($datos, $userId)` | INSERT o UPDATE según venga `id`; audita |
| `delete($id, $userId)` | **Borrado lógico** (`is_active = FALSE`) + auditoría |
| `const ESTADOS` / `const TIPOS` | Enumerados de negocio **centralizados en el modelo**, jamás cableados en vistas o SQL |
| `const ESTADO_BADGES` | Mapa estado → clase CSS, para que la vista no decida colores |

---

## 4. Diagrama de clases — Seguridad y Sistema

```mermaid
classDiagram
    class AuthController {
        +login()
        +createUserSession(user)
        +logout()
        +olvidoPassword()
        +enviarRecuperacion()
        +resetPassword(token)
        +procesarReset()
    }
    class RolesController {
        -cacheRbac$
        +getMapaRbac()$ array
        +roleHasModulo(modulo)$ bool
        +getModulos()$ array
        +getNavegacion()$ array
        +getNavegacionVisible()$ array
        +storePermisos()
    }
    class UsuariosController {
        +index()
        +store()
        +delete(id)
    }
    class AuditoriaController {
        +index()
        +papelera()
        +restaurar(tabla, id)
        -guardAdmin()
    }
    class ConfigController {
        +index()
        +store()
    }
    class PerfilController {
        +cambiarUsername()
        +cambiarPassword()
        +ping()
    }
    class DescargaController {
        +expediente(id)
        +foto(id)
        +pasante(id)
        +taller(id)
        +bien(id)
        +bm1(id)
        +acta(id)
        +oficioRuta(id)
        +permisoRuta(id)
        +comprobantePago(id)
        +fotoBien(id)
    }
    class BuscarController {
        +index()
    }
    class ExportarController {
        +tabla()
    }

    class Usuario {
        +BLOQUEO_MINUTOS$
        +findByUsernameOrEmail(valor)$
        +findParaLogin(valor)$
        +estadoSesion(id)$ object
        +empleadoDeUsuario(id)$ int
        +activoPorEmpleado(idEmp)$ bool
        +contarAdminsActivos()$ int
        +esUltimoAdminActivo(id)$ bool
        +bloqueoRestante(user)$ int
        +registrarLoginFallido(id)$ array
        +registrarLoginExitoso(id)$
        +actualizarPassword(id, hash)$
        +passwordPolicyError(pwd)$ string
    }
    class Rol
    class AuditLog {
        +log(tabla, op, id, previos, nuevos, user)$
        +paginate(filtros)$ array
        +byTabla(tabla)$ array
        +getDeleted(tabla)$ array
        +modulosDistintos()$ array
    }
    class ConfigSistema {
        +get(clave)$ string
        +set(clave, valor)$
        +getAll()$ array
        +rif()$ string
        +generarNumeroOficio(tipo)$ string
        +urlLogoImatur()$ string
        +urlLogoAlcaldia()$ string
    }
    class PasswordReset {
        +generar(idUsuario, ip)$ string
        +validar(token)$ object
        +marcarUsado(id)$
    }
    class CentroAlertas {
        +resumenCacheado()$ array
        +resumenPersonal()$ array
        +totalAccionable()$ int
        +marcarVisiblesVistas(idUsuario)$
        +invalidarCache()$
    }

    AuthController ..> Usuario
    AuthController ..> PasswordReset
    AuthController ..> AuditLog
    RolesController ..> Rol
    UsuariosController ..> Usuario
    AuditoriaController ..> AuditLog
    ConfigController ..> ConfigSistema
    PerfilController ..> Usuario
```

> **`ConfigSistema::generarNumeroOficio()` es el punto único de correlativos.** Incrementa de forma
> **atómica** (`UPDATE … RETURNING` dentro de una transacción) y **reinicia a `001` al cambiar de
> año**. Lo usan constancias, pasantes, oficios de ruta, permisos, relaciones y actas.

---

## 5. Diagrama de clases — RRHH

```mermaid
classDiagram
    class EmpleadosController {
        +index()
        +nuevo()
        +editar(id)
        +detalle(id)
        +store()
        +verificarCedula()
        +fichaTecnica(id)
        +carnet(id)
        +subirFoto()
        +trasladar()
        +egresar()
        +reingresar()
        +guardarFamiliar()
        +guardarCurso()
        +guardarExperiencia()
        +subirDocumento()
        +generarConstancia()
        +constancia(id)
        +guardarSueldo()
        +guardarDatosNomina()
    }
    class Persona {
        +save(datos, userId)$ int
        +actualizarFoto(id, archivo, userId)$
    }
    class Empleado {
        +TIPOS_CONTRATO$
        +INSTITUCIONES_ORIGEN$
        +UMBRAL_FIJO$
        +all(filtros)$
        +egresados()$
        +facilitadoresTalleres()$
        +existeCedula(cedula)$ bool
        +aniosServicio(emp)$ int
        +mesesServicio(emp)$ int
        +tiempoServicio(emp)$ string
        +elegibleParaFijo(emp)$ bool
        +formatoFolio(id)$ string
        +procesarEgreso(id, datos, userId)$
        +reingresar(id, datos, userId)$
        +historialEgresos(id)$ array
        +trasladar(id, datos, userId)$
        +historialTraslados(id)$ array
        +guardarDatosNomina(id, datos, userId)$
    }
    class Departamento {
        +arbol()$ array
    }
    class Cargo {
        +NIVELES$
        +ORDEN_NIVEL$
    }
    class Horario
    class CargaFamiliar {
        +porPersona(idPersona)$ array
        +existeCedulaEnPersona(ced, idPersona)$ bool
    }
    class CursoRealizado
    class ExperienciaLaboral
    class ExpedienteDocumento {
        +RECAUDOS$
        +recaudosEstado(idEmp)$ array
        +clavesObligatorias()$ array
        +faltantesObligatorios(idEmp)$ array
    }
    class Constancia {
        +TIPOS$
        +labelTipo(tipo)$ string
        +crear(idEmp, tipo, obs, userId)$ int
        +porEmpleado(idEmp)$ array
    }

    class AsistenciasController {
        +index()
        +marcar()
        +estadoMarcaje()
    }
    class Asistencia {
        +toleranciaPuntualidad()$ int
        +toleranciaSalidaTemprana()$ int
        +calcularMinutosTarde(idEmp, hora)$ int
        +empleadosEnActividad(fecha)$ array
        +presentesDia(fecha)$ array
        +findOpen(idEmp, fecha)$ object
    }
    class PermisosController {
        +index()
        +store()
        +aprobar(id)
        +rechazar(id)
        +anular(id)
    }
    class PermisoLaboral {
        +CATEGORIAS$
        +TIPOS$
        +ESTADOS$
        +vigenteHoy(idEmp)$ object
        +cambiarEstado(id, estado, userId)$
    }
    class VacacionesController {
        +index()
        +empleado(id)
        +registrar()
        +cambiarEstado()
        +guardarAjuste()
        +feriados()
        +generarFeriados()
    }
    class Vacacion {
        +fechaBaseServicio(emp)$ date
        +aniosServicio(emp)$ int
        +diasPorAnios(anios)$ int
        +derechoAnioActual(emp)$ int
        +derechoAcumulado(emp)$ int
        +totalDisfrutado(idEmp)$ int
        +saldo(emp)$ int
        +diasHabiles(desde, hasta)$ int
    }
    class Feriado {
        +pascua(anio)$ date
        +movibles(anio)$ array
        +generarAnio(anio)$ int
        +aniosSinMovibles()$ array
        +lookup(anio)$ array
    }
    class AmonestacionesController {
        +index()
        +empleado(id)
        +registrarFalta()
        +registrarAmonestacion()
        +amonestarDesdeFalta()
    }
    class Falta {
        +TIPOS$
        +porEmpleado(idEmp)$ array
    }
    class Amonestacion {
        +LIMITE_DESPIDO$
        +porEmpleado(idEmp)$ array
        +roster()$ array
    }

    EmpleadosController ..> Empleado
    EmpleadosController ..> Persona
    EmpleadosController ..> CargaFamiliar
    EmpleadosController ..> CursoRealizado
    EmpleadosController ..> ExperienciaLaboral
    EmpleadosController ..> ExpedienteDocumento
    EmpleadosController ..> Constancia
    Empleado ..> Persona
    Empleado ..> Cargo
    Empleado ..> Departamento
    Empleado ..> Horario
    AsistenciasController ..> Asistencia
    PermisosController ..> PermisoLaboral
    VacacionesController ..> Vacacion
    VacacionesController ..> Feriado
    Vacacion ..> Feriado
    AmonestacionesController ..> Falta
    AmonestacionesController ..> Amonestacion
    Amonestacion ..> Falta : escalado
```

---

## 6. Diagrama de clases — Nómina

```mermaid
classDiagram
    class NominaController {
        +index()
        +parametros()
        +guardarGrado()
        +guardarAntiguedad()
        +guardarParametros()
        +consultarTasa()
        +quincenal()
        +nuevaQuincena()
        +verQuincena(id)
        +recalcularQuincena(id)
        +cerrarQuincena(id)
        +exportarQuincena(id)
        +nuevoPeriodo()
        +verPeriodo(id)
        +aceptarCalculados(id)
        +guardarDetalle()
        +cerrarPeriodo(id)
        +exportarPeriodo(id)
    }

    class Nomina {
        +TIPOS_PERSONAL$
        +params()$ array
        +grados()$ array
        +pctGrado(codigo)$ float
        +pctAntiguedad(anios)$ float
        +escalaAntiguedad()$ array
        +parametrosMes(periodo)$ object
        +guardarParametrosMes(datos)$
        +tipoPersonal(emp)$ string
        +codigoGrado(emp)$ string
        +aniosAdministracion(emp)$ int
        +entradasEmpleado(emp, params)$ array
        +calcular(entradas)$ array
        +calcularQuincena(entradas)$ array
        +empleadosParaNomina()$ array
        +generarPeriodo(periodo, quincena, userId)$ int
        +detallePorPeriodo(idPeriodo)$ array
        +advertencias(idPeriodo)$ array
        +resumen(idPeriodo)$ array
        +recalcular(idPeriodo, userId)$
        +cerrar(idPeriodo, userId)$
    }
    note for Nomina "calcular() es una FUNCION PURA:\nno toca la base de datos.\nCubierta por tests/run.php"

    class Sueldo {
        +actual(idEmp)$ object
        +historial(idEmp)$ array
        +guardar(idEmp, datos, userId)$
        +totalComponentes(fila)$ float
        +integralMensual(fila)$ float
        +integralDiario(fila)$ float
        +contarHijos(idPersona)$ int
    }

    class BonoVacacional {
        +tipoPersonal(emp)$ string
        +diasBase(tipoPersonal)$ int
        +diasCorrespondientes(emp)$ int
        +generarPeriodo(periodo, corte, userId)$ int
        +recalcular(idPeriodo, userId)$
        +detallePorPeriodo(idPeriodo)$ array
        +actualizarDetalle(idDetalle, total, userId)$
        +aceptarCalculados(idPeriodo, userId)$ int
        +advertencias(idPeriodo)$ array
        +cerrar(idPeriodo, userId)$
    }

    class TasaBcv {
        +consultar()$ array
    }

    NominaController ..> Nomina
    NominaController ..> Sueldo
    NominaController ..> BonoVacacional
    NominaController ..> TasaBcv : solo sugerencia
    Nomina ..> Sueldo
    Nomina ..> Empleado
    BonoVacacional ..> Nomina : reutiliza el motor
    NominaController ..> XlsxMultiSheet : export de 6 hojas
```

> **La tasa del dólar nunca se consulta al calcular.** `generarPeriodo()` y `recalcular()` usan la
> tasa **congelada** en el período. `TasaBcv` solo interviene cuando el usuario pide la sugerencia
> en la pantalla de parámetros.

---

## 7. Diagrama de clases — Formación

```mermaid
classDiagram
    class TalleresController {
        +index()
        +store()
        +detalle(id)
        +buscarPersona()
        +inscribir()
        +desinscribir()
        +verificarDuplicado()
        +actualizarParticipante()
        +marcarAsistencia()
        +marcarAsistenciaMasiva()
        +cambiarEstado()
        +informe(id)
        +informeImprimible(id)
        +listaAsistencia(id)
        +exportarInformeCsv(id)
        +historialPersona()
    }
    class Taller {
        +ESTADOS$
        +TIPOS_ACTIVIDAD$
        +TIPOS_ENTE$
        +paginate(pagina, filtros)$
        +findDuplicate(datos)$ object
        +getParticipantes(idTaller)$ array
        +estaInscrito(idTaller, idPersona)$ bool
        +estaInscritoLibre(idTaller, datos)$ bool
        +inscribir(idTaller, idPersona, userId)$
        +inscribirLibre(idTaller, datos, userId)$
        +desinscribir(idPart, userId)$
        +marcarAsistencia(idPart, userId)$
        +marcarAsistenciaMasiva(idTaller, ids, userId)$
        +personaRecibioFormacion(idPersona)$ bool
        +buscarPersonaPorCedula(cedula)$ object
        +crearPersona(datos, userId)$ int
        +autoGenerarInforme(idTaller)$
        +saveInforme(idTaller, datos, userId)$
        +saveEvidencias(idTaller, archivos, userId)$
        +autoTransicionarProgramados()$ int
        +contarVencidos()$ int
        +cambiarEstado(id, estado, motivo, userId)$
    }
    class PasantesController {
        +index()
        +detalle(id)
        +crear()
        +editar(id)
        +aprobar(id)
        +carta(id)
        +cartaAceptacion(id)
        +carnet(id)
        +subirDocumento()
        +subirFoto()
    }
    class Pasante {
        +ESTADOS$
        +getPasantesConTutor()$ array
        +findPersonaByCedula(cedula)$ object
        +createPersona(datos)$ int
        +create(datos)$ int
        +update(id, datos)$
        +getDocumentos(idPasante)$ array
        +saveDocumento(idPasante, datos)$
    }
    class UbicacionFormacion

    TalleresController ..> Taller
    TalleresController ..> UbicacionFormacion
    TalleresController ..> Persona
    Taller ..> Empleado : facilitador
    PasantesController ..> Pasante
    Pasante ..> Persona
    Pasante ..> Empleado : tutor
    PasantesController ..> ConfigSistema : correlativo
```

---

## 8. Diagrama de clases — Turismo

> Es el módulo con más clases porque separa **catálogo** de **salida**, y cuelga de la salida
> cinco procesos distintos: personal, itinerario, participantes, cobro y cierre.

```mermaid
classDiagram
    class RutasController {
        +index()
        +store()
        +ruta(id)
        +storePunto()
        +deletePunto(id)
        +salidas()
        +storeSalida()
        +detalle(id)
        +cambiarEstadoSalida()
        +reprogramar()
        +aprobarSalida()
        +subirOficioSolicitud()
        +agregarEmpleadoSalida()
        +quitarEmpleadoSalida()
        +guardarItinerario()
        +restablecerItinerario()
        +buscarPersona()
        +inscribir()
        +desinscribir()
        +marcarAsistencia()
        +marcarAsistenciaMasiva()
        +fijarCobro()
        +exonerarSalida()
        +registrarPago()
        +anularPago()
        +subirComprobante()
        +actaPago(id)
        +tasaBcv()
        +permisos()
        +emitirPermiso()
        +permiso(id)
        +responderPermiso()
        +anularPermiso()
        +permisoImprimible(id)
        +guardarIncidencias()
        +informe(id)
        +ficha(id)
        +oficio(id)
    }

    class Ruta {
        +ESTADOS$
        +ESTADO_BADGES$
        +activas()$ array
        +getPuntos(idRuta)$ array
        +motivoEdadNoValida(ruta, fechaNac)$ string
        +textoTarifa(ruta)$ string
        +textoEdades(ruta)$ string
        +buscarPersonaPorCedula(cedula)$ object
    }
    note for Ruta "motivoEdadNoValida() es la UNICA regla de edad.\nEl rango vive en el recorrido, no en el codigo."

    class PuntoRuta {
        +allByRuta(idRuta)$ array
    }

    class RutaEjecucion {
        +EST_PROGRAMADO$
        +EST_EJECUTADO$
        +EST_NO_EJECUTADO$
        +ESTADOS_TERMINALES$
        +ORIGENES$
        +PERSONAS_POR_GUIA$
        +porRuta(idRuta)$ array
        +paginate(pagina, filtros)$ array
        +resumenPorEstado()$ array
        +cupoDiario()$ int
        +cupoRestanteDelDia(fecha)$ int
        +personasEnFecha(fecha)$ int
        +crear(datos, userId)$ int
        +aprobar(id, userId)$
        +cambiarEstado(id, estado, motivo, userId)$ bool
        +reprogramar(id, nuevaFecha, userId)$ int
        +reprogramadaComo(id)$ object
        +guardarOficioSolicitud(id, archivo, userId)$
        +empleados(idEjec)$ array
        +agregarEmpleado(idEjec, idEmp, esEncargado, userId)$
        +guiasSugeridos(personas)$ int
        +itinerario(idEjec)$ array
        +itinerarioPersonalizado(idEjec)$ bool
        +guardarItinerario(idEjec, orden, userId)$
        +restablecerItinerario(idEjec, userId)$
        +participantes(idEjec)$ array
        +inscribir(idEjec, idPersona, userId)$
        +inscribirLibre(idEjec, datos, userId)$
        +marcarAsistencia(idPart, userId)$
        +crearOficioEmitido(idEjec, datos, userId)$ string
        +guardarIncidencias(idEjec, texto, userId)$
    }
    note for RutaEjecucion "cambiarEstado() hacia Ejecutado\nDISPARA RutaFicha::generarDesdeEjecucion().\nSi la ficha falla, el cambio de estado NO se revierte."

    class PagoRuta {
        +FORMAS$
        +TARIFA_MODOS$
        +porEjecucion(idEjec)$ array
        +estadoDeCuenta(idEjec)$ array
        +fijarCondiciones(idEjec, datos, userId)$
        +sugerenciaTarifa(idEjec)$ array
        +exonerar(idEjec, motivo, userId)$
        +quitarExoneracion(idEjec, userId)$
        +registrar(idEjec, datos, userId)$ int
        +guardarComprobante(idPago, archivo, userId)$
        +anular(idPago, motivo, userId)$
    }

    class PermisoRuta {
        +EST_ESPERA$
        +EST_ACEPTADO$
        +EST_RECHAZADO$
        +EST_ANULADO$
        +salidasDeLaSemana(desde, hasta)$ array
        +custodiosDeLaSemana(desde, hasta)$ array
        +porEjecucion(idEjec)$ array
        +resumenPorEstado()$ array
        +responsableSugerido()$ object
        +emitir(datos, idsEjecucion, userId)$ int
        +responder(id, estado, datos, userId)$
        +guardarRespuestaArchivo(id, archivo, userId)$
        +anular(id, motivo, userId)$
    }

    class RutaFicha {
        +porEjecucion(idEjec)$ object
        +grupos(idInforme)$ array
        +encargado(idEjec)$ object
        +totales(idInforme)$ array
        +generarDesdeEjecucion(idEjec, userId)$ int
        +sugerenciaDesdeParticipantes(idEjec)$ array
        +guardar(idInforme, datos, grupos, userId)$
        +recalcular(idInforme)$
        +cerrar(idInforme, userId)$
        +reabrir(idInforme, userId)$
    }
    note for RutaFicha "mujeres/hombres/ninas/ninos/total\nson DERIVADOS: los recalcula\nrecalcular() en cada guardado."

    RutasController ..> Ruta
    RutasController ..> PuntoRuta
    RutasController ..> RutaEjecucion
    RutasController ..> PagoRuta
    RutasController ..> PermisoRuta
    RutasController ..> RutaFicha
    Ruta "1" --> "0..*" PuntoRuta
    Ruta "1" --> "0..*" RutaEjecucion
    RutaEjecucion "1" --> "0..1" RutaFicha
    RutaEjecucion "1" --> "0..*" PagoRuta
    RutaEjecucion "0..*" -- "0..*" PermisoRuta
    RutaEjecucion ..> Empleado : personal asignado
    RutaEjecucion ..> Persona : participantes
    PagoRuta ..> TasaBcv : sugerencia
    RutaEjecucion ..> ConfigSistema : correlativo del oficio
```

---

## 9. Diagrama de clases — Bienes

```mermaid
classDiagram
    class InventarioController {
        +index()
        +store()
        +detalle(id)
        +codificar()
        +subirDocumento()
        +subirFoto()
        +consolidados()
        +registrarBM1()
        +relaciones()
        +emitirRelacion()
        +relacion(id)
        +anularRelacion()
        +guardarDonacion()
        +donacion(id)
        +actas()
        +emitirActa()
        +acta(id)
        +registrarActaFirmada()
        +anularActa()
        +marcarRetirado()
        +conteos()
        +abrirConteo()
        +verConteo(id)
        +verificarConteo()
        +cerrarConteo(id)
        +actaConteo(id)
        +planMantenimiento()
        +guardarPlan()
        +etiquetas()
        +suficiencia()
        +guardarDotacion()
    }
    class ActividadesinventarioController {
        +index()
        +store()
    }

    class Inventario {
        +ESTATUS$
        +CONDICIONES$
        +componerCodigo(partes)$ string
        +descripcionOficial(bien)$ string
        +sqlEstBaja()$ string
        +fueraDeInventario(bien)$ bool
        +disponible(bien)$ bool
        +desincorporados()$ array
        +pendientesCodificacion()$ array
        +findByNroOrden(nro)$ object
        +findByCodigoBn(cod)$ object
        +findBySerial(serial)$ object
        +codificar(id, partes, idBm1, userId)$
        +guardarDonacion(id, datos, userId)$
        +resumenPorEstatus()$ array
        +marcarRetirado(id, userId)$
        +porRetirar()$ array
    }
    note for Inventario "El responsable NO se almacena:\nse deriva del departamento de la ubicacion.\nPara listar desincorporados usar\ndesincorporados(), NUNCA is_active = FALSE."

    class ActividadInventario {
        +TIPOS$
        +byItem(idBien)$ array
        +registrarMovimiento(datos, userId)$ int
        +autorizador()$ object
    }
    class Mantenimiento {
        +abiertoDe(idBien)$ object
        +porBien(idBien)$ array
        +enCurso()$ array
        +abrir(datos, userId)$ int
        +cerrar(id, datos, userId)$
    }
    class PlanMantenimiento {
        +porBien(idBien)$ object
        +proximos(dias)$ array
        +guardar(datos, userId)$
        +marcarRealizado(id, fecha, userId)$
    }
    class ConteoInventario {
        +abierto()$ object
        +abrir(datos, userId)$ int
        +detalle(idConteo)$ array
        +verificar(idDetalle, datos, userId)$
        +cerrar(idConteo, userId)$
        +resumen(idConteo)$ array
    }
    class ConsolidadoBM1 {
        +bienes(idBm1)$ array
        +crear(datos, userId)$ int
    }
    class RelacionBienes {
        +candidatos()$ array
        +items(idRelacion)$ array
        +emitir(datos, idsBienes, userId)$ int
        +anular(id, motivo, userId)$
    }
    class ActaDesincorporacion {
        +candidatos()$ array
        +items(idActa)$ array
        +estaFirmada(acta)$ bool
        +emitir(datos, idsBienes, userId)$ int
        +registrarFirmada(id, datos, userId)$
        +anular(id, motivo, userId)$
    }
    class DotacionInventario {
        +categoriasSinDotacion()$ array
        +analisis()$ array
        +guardar(datos, userId)$
    }
    class InventarioDocumento {
        +TIPOS$
        +porBien(idBien)$ array
    }
    class Categoria
    class Ubicacion {
        +SEDES$
    }

    InventarioController ..> Inventario
    InventarioController ..> ConsolidadoBM1
    InventarioController ..> RelacionBienes
    InventarioController ..> ActaDesincorporacion
    InventarioController ..> ConteoInventario
    InventarioController ..> PlanMantenimiento
    InventarioController ..> DotacionInventario
    InventarioController ..> InventarioDocumento
    ActividadesinventarioController ..> ActividadInventario
    ActividadesinventarioController ..> Mantenimiento
    Inventario ..> Categoria
    Inventario ..> Ubicacion
    Inventario ..> ConsolidadoBM1
    Inventario ..> RelacionBienes
    Inventario ..> ActaDesincorporacion
    Mantenimiento ..> ActividadInventario
    Ubicacion ..> Departamento
```

---

## 10. Diagrama de clases — Recepción y Reportes

```mermaid
classDiagram
    class VisitantesController {
        +index()
        +buscarVisitante()
        +registrar()
    }
    class VisitasController {
        +index()
    }
    class Visitante {
        +buscarPorCedula(cedula)$ object
        +crear(datos, userId)$ int
        +store(datos, userId)$
    }
    class Visita {
        +getRecientesToday()$ array
        +paginate(pagina, filtros)$ array
        +registrar(idVisitante, datos, userId)$
    }

    class ReportesController {
        +index()
        #requireModulo(modulos)
    }
    class ReportesRrhhTrait {
        <<trait>>
        +directorio()
        +asistencia()
        +permisos()
        +amonestaciones()
        +egresos()
        +constancias()
        +expedientesIncompletos()
        +cargaFamiliar()
        +vacacionesSaldo()
        +comisionServicio()
    }
    class ReportesFormacionTrait {
        <<trait>>
        +talleres()
        +coberturaFormacion()
        +formacionTrimestral()
        +dossier()
        +pasantes()
    }
    class ReportesTurismoTrait {
        <<trait>>
        +rutas()
        +ejecucionesRuta()
        +participacionRutas()
    }
    class ReportesInventarioTrait {
        <<trait>>
        +inventario()
        +kardex()
        +bienesAsignados()
        +bajasInventario()
    }
    class ReportesRecepcionTrait {
        <<trait>>
        +visitantes()
        +estadisticasVisitas()
    }
    class ReportesSistemaTrait {
        <<trait>>
        +alertas()
        +auditoria()
        +accesos()
        +duplicados()
    }
    class ReportesIndicadoresTrait {
        <<trait>>
        +indicadores()
    }
    class ReportesExportTrait {
        <<trait>>
    }
    class DashboardController {
        +index()
        +accesoDenegado()
        +marcarAlertasVistas()
    }

    VisitantesController ..> Visitante
    VisitantesController ..> Visita
    VisitasController ..> Visita
    Visitante ..> Persona
    Visita ..> Empleado

    ReportesController ..|> ReportesRrhhTrait
    ReportesController ..|> ReportesFormacionTrait
    ReportesController ..|> ReportesTurismoTrait
    ReportesController ..|> ReportesInventarioTrait
    ReportesController ..|> ReportesRecepcionTrait
    ReportesController ..|> ReportesSistemaTrait
    ReportesController ..|> ReportesIndicadoresTrait
    ReportesController ..|> ReportesExportTrait
    DashboardController ..> CentroAlertas
```

> **`ReportesController` usa composición por *traits*, no herencia.** Se partió en 8 traits
> (de 3.405 a 101 líneas) porque una sola clase con ~40 reportes era inmanejable. En el diagrama
> UML se representa como **8 interfaces/mixins realizados por la clase**.

---

## 11. Catálogo completo de clases

### 11.1 Núcleo (8)

| Clase | Responsabilidad |
|---|---|
| `Router` | Front controller: parseo de URL, middlewares de autenticación/RBAC/idempotencia, despacho |
| `Database` | Envoltorio PDO sobre PostgreSQL: sentencias preparadas, transacciones, conexión compartida por petición |
| `Controller` | Clase base: carga de modelos y vistas, sanitización de POST, validaciones (correo, teléfono, RIF), carga de fotos |
| `Model` | Clase base: acceso a `Database` y auditoría automática con diff completo |
| `Util` | Utilidades puras: edad, número y monto a letras, fecha en letras |
| `TasaBcv` | Consulta la tasa del BCV como **sugerencia**; degrada sin bloquear |
| `XlsxMultiSheet` | Generador de Excel multi-hoja sin dependencias externas |
| `XlsxLogos` | Inserta el membrete institucional en las hojas de Excel |

### 11.2 Controladores (33 + 8 traits)

| Grupo | Controladores |
|---|---|
| **Sistema** | `Auth`, `Usuarios`, `Roles`, `Auditoria`, `Config`, `Perfil`, `Descarga`, `Buscar`, `Exportar`, `Dashboard` |
| **RRHH** | `Empleados`, `Cargos`, `Departamentos`, `Horarios`, `Asistencias`, `Permisos`, `Vacaciones`, `Amonestaciones` |
| **Nómina** | `Nomina` |
| **Formación** | `Talleres`, `Ubicacionesformacion`, `Pasantes` |
| **Turismo** | `Rutas` |
| **Bienes** | `Inventario`, `Categorias`, `Ubicaciones`, `Actividadesinventario` |
| **Recepción** | `Visitantes`, `Visitas` |
| **Geografía** | `Municipio`, `Parroquia` |
| **Reportes** | `Reportes` + traits `Rrhh`, `Formacion`, `Turismo`, `Inventario`, `Recepcion`, `Sistema`, `Indicadores`, `Export` |

### 11.3 Modelos (50)

| Módulo | Modelos |
|---|---|
| **Sistema** (7) | `Usuario`, `Rol`, `AuditLog`, `ConfigSistema`, `PasswordReset`, `CentroAlertas`, `Persona` |
| **RRHH** (14) | `Empleado`, `Departamento`, `Cargo`, `Horario`, `Asistencia`, `PermisoLaboral`, `Vacacion`, `Feriado`, `Falta`, `Amonestacion`, `Constancia`, `ExpedienteDocumento`, `CargaFamiliar`, `CursoRealizado` |
| **RRHH (cont.)** (1) | `ExperienciaLaboral` |
| **Nómina** (3) | `Nomina`, `Sueldo`, `BonoVacacional` |
| **Formación** (3) | `Taller`, `UbicacionFormacion`, `Pasante` |
| **Turismo** (6) | `Ruta`, `PuntoRuta`, `RutaEjecucion`, `RutaFicha`, `PermisoRuta`, `PagoRuta` |
| **Bienes** (13) | `Inventario`, `ActividadInventario`, `Categoria`, `Ubicacion`, `InventarioDocumento`, `ConsolidadoBM1`, `RelacionBienes`, `ActaDesincorporacion`, `Mantenimiento`, `PlanMantenimiento`, `ConteoInventario`, `DotacionInventario`, `ActividadInventario` |
| **Recepción** (2) | `Visitante`, `Visita` |
| **Geografía** (2) | `Municipio`, `Parroquia` |

---

## 12. Patrones y convenciones de diseño

| Patrón | Dónde | Por qué |
|---|---|---|
| **Front Controller** | `public/index.php` + `Router` | Un único punto de entrada donde aplicar autenticación, RBAC e idempotencia |
| **MVC** | `controllers` / `models` / `views` | Separación de responsabilidades |
| **Template Method** | `Controller`, `Model` | Comportamiento común (vista, modelo, auditoría) heredado por todas las clases concretas |
| **Active Record (parcial)** | Modelos con `find`/`save`/`delete` estáticos | Simplicidad sin ORM |
| **Registry / Singleton por petición** | `Database::compartida()` | Abrir una conexión cuesta ~45 ms; la consulta, < 1 ms |
| **Traits (mixins)** | `ReportesController` | Partir una clase de 3.400 líneas sin inventar una jerarquía artificial |
| **Strategy implícito** | `ConfigSistema::generarNumeroOficio($tipo)` | Un solo algoritmo de correlativo parametrizado por tipo de documento |
| **Función pura + snapshot** | `Nomina::calcular()` y `nomina_periodos` | El cálculo es determinista y reproducible porque las entradas quedan congeladas |
| **Observer implícito** | `Model::audit()` | Toda escritura genera su registro en la bitácora sin que el llamador lo pida |
| **Soft delete** | `is_active` en todos los modelos | Ninguna operación destruye información |
| **Token de un solo uso** | `sigtur_token_emitir/consumir` | Idempotencia de formularios sin librerías |

### 12.1 Reglas de codificación que el diagrama debe reflejar

1. **Los métodos públicos de un controlador son URLs.** Por eso los helpers de `Controller` son
   `protected`: si fueran públicos quedarían expuestos como endpoints.
2. **Los enumerados de negocio viven en constantes del modelo**, nunca cableados en vistas o SQL.
   Ejemplos: `Ruta::ESTADOS`, `RutaEjecucion::ESTADOS_TERMINALES`, `Amonestacion::LIMITE_DESPIDO`,
   `Inventario::ESTATUS`, `PagoRuta::FORMAS`, `Constancia::TIPOS`, `Cargo::NIVELES`.
3. **Los parámetros configurables viven en `configuracion_sistema`**, no en constantes: tolerancias,
   metas, cupo diario, días de preaviso, días de bono por tipo de personal.
4. **Los modelos no imprimen ni redirigen**: devuelven datos o lanzan `Exception` con un mensaje
   apto para el usuario, que el controlador convierte en mensaje flash.
5. **La auditoría es automática**: en un UPDATE, `Model::audit()` relee la fila completa en la misma
   conexión para que el diff refleje todos los campos, no solo los que el modelo armó a mano.
   **Nunca registra la columna `password`.**
