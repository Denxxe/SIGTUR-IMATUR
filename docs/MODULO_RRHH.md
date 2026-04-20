# Módulo Técnico: Recursos Humanos (RRHH)

**Ruta Lógica:** `/empleados`, `/departamentos`, `/cargos`, `/asistencias`.
**Nivel de Acceso Sugerido:** RRHH, Administración.

## Objetivo del Módulo
La lógica de negocio se enfoca en gestionar todo el historial del talento humano y la estructura organizacional. Se requiere guardar por separado lo que "Es un Humano" (Persona) de "El Contrato que cumple" (Empleado), garantizando la normalización de Datos.

## Tablas en Juego
- `departamentos`: Divisiones lógicas (Dirección, RRHH, etc).
- `cargos`: Perfiles profesionales y la asigación de límite sueldo base.
- `personas`: Datos puros, indivisibles del ser humano (Cédula, Nombres, Genero, Nacimiento).
- `empleados`: Extensión laboral que une un `id_persona` con su rol institucional (`id_cargo`, `id_departamento`, `fecha_ingreso`).
- `asistencias`: Diario de movimientos y comentarios por cada empleado activo.

## Lógica de Negocio (Business Rules)
1. **Transacción Dual (Registro Atómico):**
   Un empleado *no puede existir* si no se registra su núcleo base en `personas`. El Controlador `EmpleadosController::store()` envía la estructura de datos al Modelo `Empleado`. 
   Esta clase abre un túnel de base de datos (`beginTransaction`), realiza el INSERT en la tabla Persona reteniendo su ID (`RETURNING id`), y usa este para asociar a la persona en la tabla hija `Empleados`.
   Si cualquier proceso falla, ocurre un *Rollback* y ninguna tabla queda manchada temporalmente.
2. **Bajas Laborales (Soft Delete):**
   Si se da de baja un trabajador, el modelo altera su estado a la sombra (Papelera) como inactivo. El Expediente no desaparece, simplemente oculta a nivel visual la cuenta, salvaguardando reportes antiguos.
3. **Marcaje de Asistencia:**
   `AsistenciasController` rastrea ingresos. Solo permite a los empleados actualmente activos ser tomados en cuenta en los resúmenes visualizados.
