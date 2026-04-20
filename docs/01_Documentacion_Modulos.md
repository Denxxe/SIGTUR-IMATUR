# Documentación Técnica: Módulos del Sistema SIGTUR-IMATUR

Este documento detalla la lógica de negocio, tablas y relaciones de cada uno de los módulos operativos construidos en el sistema.

---

## 1. Módulo de Recursos Humanos (RRHH)
**Objetivo:** Gestionar el personal interno de IMATUR, sus estructuras jerárquicas y su asistencia.

### Componentes y Controladores:
- **Departamentos (`DepartamentosController`)**: Gestiona las áreas físicas o lógicas empresariales.
- **Cargos (`CargosController`)**: Administra los roles internos y sueldos base de los empleados.
- **Empleados (`EmpleadosController`)**: Relaciona atributos personales (`personas`) con atributos empresariales (`empleados`). Maneja la asignación de cargo y expediente de forma atómica en base de datos.
- **Asistencias (`AsistenciasController`)**: Registro diario de entrada, salida y observaciones del personal interno vigente.

### Funcionalidades Clave:
- Guardado atómico con transacciones en PostgreSQL (Tabla `personas` + Tabla `empleados`).
- Visualización de gráficas de cantidad de empleados por departamento en el Dashboard principal.

---

## 2. Módulo de Inventario
**Objetivo:** Trazabilidad absoluta de los bienes nacionales físicos e insumos de la institución.

### Componentes y Controladores:
- **Categorías (`CategoriasController`)**: Agrupación lógica de bienes (Electrónica, Mobiliario, etc).
- **Ubicaciones (`UbicacionesController`)**: Oficinas físicas donde reside un bien (Ej. "Despacho Principal").
- **Inventario (`InventarioController`)**: Creación de registros de bienes usando un "Código BN" único para cada equipo, evaluando su condición y valor estimado.
- **Movimientos/Actividades (`ActividadesinventarioController`)**: Registro de asignaciones, desincorporaciones y observaciones de mantenimiento temporal. Garantiza un historial de vida por cada bien.

---

## 3. Módulo de Formación Educativa
**Objetivo:** Planificación, censo y análisis de charlas, cursos y pasantes de universidades. Es el módulo más robusto debido a la cantidad de informes que recopila.

### Componentes y Controladores:
- **Sedes (`UbicacionesformacionController`)**: Espacios donde se imparten las charlas u oficinas adscritas para talleres.
- **Talleres (`TalleresController`)**: Creación de un curso temporal con fecha inicio/fin, cupos y un empleado como "Facilitador".
- **Informes Demográficos (`Taller_informes`)**: Al terminar un taller, se captura obligatoriamente cuántos asistentes hubo y se segmentan en: Mujeres, Hombres, Niñas, Niños, asegurando estadísticas precisas (Totales calculados automáticamente).
- **Insumos por Taller (`Taller_inventario`)**: Permite que a un evento de formación se le "presten" sillas, proyectores, etc., vinculando el Módulo de Inventario con el de Formación temporalmente.
- **Pasantes (`PasantesController`)**: Gestión de jóvenes practicantes, su casa de estudio y el estado de entrega de sus requisitos (Postulación, Aceptación, Evaluación final).

---

## 4. Módulo de Turismo (Rutas y Eventos)
**Objetivo:** Promoción turística y planificación de atractivos y festividades.

### Componentes y Controladores:
- **Rutas (`RutasController`)**: Atractivos macros que pueden tener múltiples paradas. (Ej: "Ruta Histórica de Cumaná").
- **Puntos de Ruta (`Puntos_ruta`)**: Geoposiciones o paradas turísticas específicas concatenadas en orden para formar la ruta macro.
- **Actividades / Festividades (`ActividadesrutaController`)**: Eventos puntuales, calendarios y conmemoraciones que IMATUR promueve hacia la ruta. 

---

## 5. Módulo Misceláneo y de Reportes
- **Centro de Reportes (`ReportesController`)**: Exportación consolidada y consultas complejas (JOINs) preparadas para listados y cruces estadísticos.
- **Indicadores Clave (Dashboard)**: Utilización de librerías para procesar los datos de empleados, inventarios inactivos, y rutas activas hacia ApexCharts.
