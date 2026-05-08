# Estructura Organizativa — IMATUR

## Organigrama declarado

```
DIRECCIÓN GENERAL
│
├── DIRECCIÓN DE PLANIFICACIÓN Y GESTIÓN TURÍSTICA
│   ├── Departamento de Rutas Turísticas y Proyectos
│   └── Departamento de Formación y Capacitación Turística
│       ├── Formación interna (personal IMATUR)
│       └── Formación externa (Zona Educativa, instituciones, comunidades)
│
├── DIRECCIÓN ADMINISTRATIVA
│   ├── Departamento de Administración y Finanzas
│   └── Departamento de Tecnología e Informática
│
├── DIRECCIÓN DE TALENTO HUMANO
│   ├── Departamento de Registro y Nómina
│   └── Departamento de Bienestar Laboral
│
└── CONSULTORÍA JURÍDICA
```

---

## Estado actual en la base de datos

La tabla `departamentos` usa una estructura **plana** (sin `parent_id`). Los registros actuales
representan áreas funcionales sin reflejar la jerarquía de Direcciones.

### Mapeo funcional actual

| Rol SIGTUR | Módulos asignados | Equivale en organigrama |
|------------|-------------------|------------------------|
| 1 — Admin | Todo el sistema | Dirección General / TI |
| 2 — RRHH | Empleados, Cargos, Departamentos, Asistencias, Config | Dir. Talento Humano |
| 3 — Turismo | Rutas, Talleres, Visitantes, Visitas | Dir. Planificación Turística |
| 4 — Inventario | Inventario | Dir. Administrativa |

---

## Relación Formación ↔ Talento Humano

- **Departamento de Formación** planifica y ejecuta las actividades.
- **Talento Humano** emite el formato de asistencia oficial para actividades internas.
- **Talento Humano** revisa los informes de formación y los remite a la Dirección para firma y sello.
- Esta separación de roles actualmente no está modelada en el sistema (ambos usan el rol 2 — RRHH).

---

## Análisis: ¿Agregar `parent_id` a `departamentos`?

### Opción A — Estructura plana actual (estado actual)
**Pros:** Simplicidad, sin JOIN recursivos, sin migración de datos.  
**Cons:** No refleja la jerarquía real; los reportes por Dirección no son posibles.

### Opción B — Añadir `parent_id` (propuesta futura)
```sql
ALTER TABLE departamentos ADD COLUMN parent_id INTEGER REFERENCES departamentos(id);
```
**Pros:** Refleja la estructura real; permite reportes jerárquicos.  
**Cons:** Requiere migración de datos existentes; queries con WITH RECURSIVE o JOINs extra.

### Decisión actual
**Se mantiene la estructura plana** mientras el sistema está en fase de desarrollo.  
La jerarquía se puede agregar en una migración posterior (v3.x) cuando los reportes
por Dirección sean requeridos funcionalmente. No hay bloqueantes actuales.

---

## Notas de implementación

- El campo `departamentos.nombre` usa el nombre completo de la unidad
  (ej: "Departamento de Formación y Capacitación Turística").
- Las actividades de formación y los talleres se asocian a la institución donde se realiza
  mediante `ubicaciones_formacion`, no directamente a un departamento.
- El campo `talleres.es_interna` distingue si la actividad es para el personal propio (IMATUR)
  o para entidades externas, independientemente de la tabla `departamentos`.
