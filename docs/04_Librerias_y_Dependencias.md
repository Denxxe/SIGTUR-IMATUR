# Librerías y Dependencias del Sistema

Este documento describe todas las tecnologías, librerías de terceros y dependencias empleadas en el desarrollo de SIGTUR-IMATUR. Todo el ecosistema ha sido diseñado para funcionar de manera local (on-premise) o en la nube sin depender de conexión a internet para librerías críticas de estilos, garantizando alta disponibilidad.

## 1. Tecnologías Base (Core)
- **PHP 8+**: Lenguaje de código del lado del servidor. El sistema utiliza sus capacidades orientadas a objetos, el manejo nativo de sesiones (`session_start()`) y `password_hash()` con BCRYPT para máxima seguridad en credenciales.
- **PostgreSQL 12+**: Motor de base de datos relacional de objetos. Se eligió por su estabilidad superior frente a MySQL en transacciones de llaves foráneas (`CASCADE`/`RESTRICT`) y su soporte para columnas especializadas (como `JSONB` si fuese necesario) e integridad referencial cruda.

## 2. Dependencias FrontEnd (UI/UX)
El sistema carga estas librerías desde la carpeta `/public/assets/libs/` para asegurar que el sistema no se caiga si falla el internet institucional.

### 2.1 Bootstrap 5.3 (Local)
*Ruta:* `/assets/libs/bootstrap.min.css` y `bootstrap.bundle.min.js`.
- Se usa como framework estructural de FrontEnd para la disposición de columnas (Grid System), botones, modales (ventanas emergentes) e inputs de formulario.
- **Integración de Validaciones:** Se emplean las clases `.needs-validation` y `.was-validated` nativas de Bootstrap para dar retroalimentación visual al usuario en The DOM cuando comete un error en el formulario (ej. bordes rojos).

### 2.2 Bootstrap Icons (Local)
*Ruta:* `/assets/libs/bootstrap-icons.min.css` (fuentes: `bootstrap-icons.woff2`, `bootstrap-icons.woff`, `bootstrap-icons.svg`)
- Provee la iconografía moderna y limpia para el Sidebar, los botones de acción y los elementos del Dashboard. Integrado 100% localmente; las rutas de fuente en el CSS apuntan a `./` para compatibilidad con el servidor local sin internet.

### 2.3 ApexCharts.js (Local)
*Ruta:* `/assets/libs/apexcharts.min.js`
- Librería de visualización de datos.
- **Función en SIGTUR**: Transforma los conteos de consultas SQL en gráficos interactivos (`Pie`, `Bar`, `Line`) que se muestran en `DashboardController` y `ReportesController`. Se prefirió sobre Chart.js debido a su interactividad por defecto y su fácil manejo de datos directamente renderizados desde arreglos JSON de PHP.

## 3. Dependencias BackEnd
Al ser un sistema Vanilla/Custom MVC, *NO* utiliza Composer ni librerías pesadas (Laravel/Symfony). Las tecnologías requeridas son extensiones nativas:
- **PDO_PGSQL**: Extensión nativa de PHP requerida actuvamente para conectarse a PostgreSQL.
- **PDO (PHP Data Objects)**: Wrapper robusto para interactuar con la DB usando *Prepared Statements*, blindando el sistema completamente contra ataques de inyección SQL (SQLi).
