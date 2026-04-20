# Módulo Técnico: Formación

**Ruta Lógica:** `/talleres`, `/ubicacionesformacion`, `/pasantes`.
**Nivel de Acceso Sugerido:** Turismo, Administración.

## Objetivo del Módulo
La división formativa recauda métricas, impulsa políticas turísticas y funciona como eje central de IMATUR. 

## Tablas en Juego
- `ubicaciones_formacion`: Sitios donde se ejecutan formacionales.
- `talleres`: Cabecillas de proyecto. Cuentan con Facilitador, fechas, y temas.
- `taller_informes`: Registros demográficos que evalúan la calidad e impacto post-fecha de culminación del taller.
- `pasantes`: Registro civil e institucional, y `pasante_documentos` para certificar estatus de entrada o finalización de periodo.

## Lógica de Negocio (Business Rules)
1. **Indicadores de Desempeño y Género:**
   Post-Evento, es el único módulo que rompe los moldes clásicos de crud para exigir un llenado "Demográfico" obligacional (`taller_informes`) con datos puros estadísticos (Niños, Niñas, Mujeres...). El formulario sube un Reporte con un `total_atendidas` automatizado.
2. **Vínculos Mixtos:**
   Vincula la tabla de recursos humanos asignando un `id_facilitador` (Que es llave para Empleados). Si un Faciliador es retirado o jubilado, los talleres en histórico conservan la integridad gracias a llaves foráneas y borrado lógico.
3. **Revisión de Casos (Pasantes):**
   Posee restricciones CHECK en postgreSQL contra la columna de "Estado del pasante", bloqueando modificaciones imprecisas o transiciones rotas. (Postulado -> En Curso -> Culminado o Rechazado).
