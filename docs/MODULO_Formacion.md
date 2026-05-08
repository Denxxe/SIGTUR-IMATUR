# Módulo Técnico: Formación

**Ruta Lógica:** `/talleres`, `/ubicacionesformacion`, `/pasantes`.
**Nivel de Acceso Sugerido:** Turismo, Administración.

## Objetivo del Módulo
La división formativa recauda métricas, impulsa políticas turísticas y funciona como eje central de IMATUR. 

## Tablas en Juego
- `ubicaciones_formacion`: Sitios donde se ejecutan formacionales. FK a `parroquia`.
- `talleres`: Cabecillas de proyecto. Cuentan con Facilitador, fechas, y temas. Columna `tipo_actividad` agregada en migración 002 (valores: 'Taller','Charla','Curso','Taller de Arte','Capacitación'; DEFAULT 'Taller').
- `taller_informes`: Registros demográficos que evalúan la calidad e impacto post-fecha de culminación del taller. `total_atendidas` es derivado (mujeres+hombres+niñas+niños) — siempre recalcular antes de guardar.
- `participantes_taller` *(migración 002)*: Tabla pivote de inscripción y asistencia individual de personas a un taller.
- `taller_inventario` *(migración 002)*: Préstamo de bienes del inventario institucional a un taller.
- `pasantes`: Registro civil e institucional. La migración 003 agrega `id_persona` FK para normalizar con la tabla `personas` y eliminar los campos redundantes `cedula`, `nombre`, `apellido`.
- `pasante_documentos`: Cartas y evaluaciones con flags de entrega por pasante.

## Lógica de Negocio (Business Rules)
1. **Indicadores de Desempeño y Género:**
   Post-Evento, es el único módulo que rompe los moldes clásicos de crud para exigir un llenado "Demográfico" obligacional (`taller_informes`) con datos puros estadísticos (Niños, Niñas, Mujeres...). El formulario sube un Reporte con un `total_atendidas` automatizado.
2. **Vínculos Mixtos:**
   Vincula la tabla de recursos humanos asignando un `id_facilitador` (Que es llave para Empleados). Si un Faciliador es retirado o jubilado, los talleres en histórico conservan la integridad gracias a llaves foráneas y borrado lógico.
3. **Revisión de Casos (Pasantes):**
   Posee restricciones CHECK en postgreSQL contra la columna de "Estado del pasante", bloqueando modificaciones imprecisas o transiciones rotas. (Postulado -> En Curso -> Culminado o Rechazado).
