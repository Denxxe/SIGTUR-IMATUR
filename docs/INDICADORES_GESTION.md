# Indicadores de Gestión — SIGTUR-IMATUR

**Última actualización:** 2026-05-31
**Fuentes de verdad en código:**
- `app/controllers/ReportesController.php::indicadores()` — página *Indicadores de Gestión* (RF30)
- `app/controllers/DashboardController.php::index()` — Panel Principal (role-aware)
- `app/controllers/ReportesController.php::stats*()` — KPIs de cabecera de cada reporte

> Todos los indicadores se calculan **en vivo** sobre la base de datos en cada carga (no hay tablas de agregación ni caché). Todas las consultas filtran `is_active = TRUE` salvo las que miden bajas/eliminaciones. El "año actual" se obtiene de `date('Y')` del servidor.

---

## Índice

1. [Parámetros configurables (metas y umbrales)](#1-parámetros-configurables)
2. [Indicadores del Panel Principal (Dashboard)](#2-indicadores-del-panel-principal-dashboard)
3. [Indicadores de Gestión (página RF30)](#3-página-de-indicadores-de-gestión-rf30)
4. [KPIs de cabecera por reporte](#4-kpis-de-cabecera-por-reporte)
5. [Glosario de fuentes de datos](#5-glosario-de-fuentes-de-datos)

---

## 1. Parámetros configurables

Almacenados en `configuracion_sistema (clave, valor)` — editables desde **Sistema → Configuración** (migración 014). Alimentan metas y alertas.

| Clave | Valor por defecto | Para qué sirve |
|-------|-------------------|----------------|
| `meta_talleres_anio` | `0` | Meta anual de actividades formativas a ejecutar (finalizar). Denominador de la barra de progreso de Formación. |
| `meta_rutas_anio` | `0` | Meta anual de rutas turísticas a ejecutar (finalizar). Denominador de la barra de progreso de Turismo. |
| `dias_preaviso_contrato` | `30` | Ventana (días) para alertar sobre contratos próximos a vencer en el dashboard. |
| `dias_preaviso_pasante` | `15` | Ventana (días) para alertar sobre pasantes próximos a culminar en el dashboard. |

> Con meta = `0` la barra de progreso no se evalúa (evita división por cero); se muestra solo el conteo ejecutado.

---

## 2. Indicadores del Panel Principal (Dashboard)

El dashboard es **role-aware**: cada bloque solo se calcula y muestra según el rol de la sesión. Origen: `DashboardController::index()`.

### 2.1 Bloque PERSONAL — roles 1 (Admin), 2 (RRHH)

| Indicador | Para qué sirve | Fórmula / Cómo se calcula | Fuente |
|-----------|----------------|----------------------------|--------|
| **Empleados activos** | Tamaño actual de la plantilla. | `COUNT(*)` de empleados activos. | `empleados` |
| **Asistencias del mes** | Volumen de marcajes del mes en curso. | `COUNT(*)` de asistencias donde `date_trunc('month', fecha) = mes actual`. | `asistencias` |
| **Δ Asistencias vs mes anterior** | Tendencia del cumplimiento de marcaje. | `((mes_actual − mes_anterior) / mes_anterior) × 100`. Se oculta si el mes anterior es 0 o la variación es < 0.5 %. Flecha ↑ verde / ↓ roja. | `asistencias` |
| **Contratos por vencer** | Anticipar renovaciones/egresos de personal contratado. | `COUNT(*)` de empleados `tipo_contrato='Contratado'` con `fecha_egreso BETWEEN hoy AND hoy + dias_preaviso_contrato`. | `empleados` + `configuracion_sistema` |
| **Asistencia por mes (4 meses)** | Mini-tendencia de marcaje. | `COUNT(*)` agrupado por `YYYY-MM`, últimos 4 meses (meses sin datos se rellenan con 0). | `asistencias` |
| **Empleados por departamento (top 8)** | Distribución de la plantilla. | `COUNT(empleados)` por departamento, orden descendente, límite 8. | `departamentos` ⟕ `empleados` |

### 2.2 Bloque VISITAS — roles 1, 2, 5 (Recepción)

| Indicador | Para qué sirve | Fórmula / Cómo se calcula | Fuente |
|-----------|----------------|----------------------------|--------|
| **Visitas hoy** | Carga de recepción del día. | `COUNT(*)` de visitas con `DATE(hora_entrada) = hoy`. | `visitas` |
| **Δ Visitas hoy vs ayer** | Variación diaria de afluencia. | `((hoy − ayer) / ayer) × 100`, mismas reglas de ocultamiento que los demás deltas. | `visitas` |
| **Visitantes únicos (semana)** | Personas distintas atendidas en la semana en curso. | `COUNT(DISTINCT id_visitante)` desde el inicio de la semana. | `visitas` |
| **Δ Visitantes semana vs semana anterior** | Tendencia semanal de afluencia. | Delta % entre la semana actual y la previa (ventana de 7 días). | `visitas` |
| **Visitantes únicos (mes)** | Alcance mensual de recepción. | `COUNT(DISTINCT id_visitante)` del mes en curso. | `visitas` |
| **Visitas por día (14 días)** | Mini-tendencia de afluencia. | `COUNT(*)` por día, últimos 14 días (días vacíos rellenados con 0). | `visitas` |

### 2.3 Bloque FORMACIÓN Y TURISMO — roles 1, 3 (Turismo)

| Indicador | Para qué sirve | Fórmula / Cómo se calcula | Fuente |
|-----------|----------------|----------------------------|--------|
| **Actividades activas** | Carga formativa vigente. | `COUNT(*)` de talleres en estado `En Curso` o `Programado`. | `talleres` |
| **Personas formadas (año)** | Alcance formativo acumulado del año. | `COUNT(*)` de inscripciones en talleres cuyo `fecha_inicio` cae en el año actual. | `participantes_taller` ⋈ `talleres` |
| **Δ Formados vs año anterior** | Crecimiento interanual del alcance formativo. | Delta % de formados del año actual contra el año previo. | `participantes_taller` ⋈ `talleres` |
| **Rutas activas** | Oferta turística disponible. | `COUNT(*)` de rutas en estado `Activa`. | `rutas` |
| **Pasantes en curso** | Pasantías vigentes. | `COUNT(*)` de pasantes en estado `En Curso`. | `pasantes` |
| **Tasa de ocupación de actividades** | Qué tan llenas están las actividades respecto al cupo. | `SUM(inscritos) / SUM(cupo_maximo) × 100` sobre talleres del año, no cancelados y con `cupo_maximo > 0`. | `talleres` ⟕ `participantes_taller` |
| **Tasa de finalización** | Eficacia de ejecución formativa. | `finalizados / total_actividades_año × 100`. | `talleres` |
| **Tasa de cancelación** | Pérdida/deserción de actividades. | `cancelados / total_actividades_año × 100`. | `talleres` |
| **Actividades por mes (6 meses)** | Mini-tendencia de programación. | `COUNT(*)` por `YYYY-MM`, últimos 6 meses (rellenados con 0). | `talleres` |

### 2.4 Bloque INVENTARIO — roles 1, 4 (Inventario)

| Indicador | Para qué sirve | Fórmula / Cómo se calcula | Fuente |
|-----------|----------------|----------------------------|--------|
| **Bienes activos** | Patrimonio operativo total. | `COUNT(*)` de bienes activos. | `inventario` |
| **Bienes en alerta** | Patrimonio que requiere atención. | `COUNT(*)` con `condicion IN ('Dañado','En Reparación')`. | `inventario` |
| **Bajas del año** | Desincorporaciones del año. | `COUNT(*)` de bienes con `is_active = FALSE`, `deleted_at` en el año actual. | `inventario` |
| **Tasa de deterioro** | % del patrimonio en mal estado. | `bienes_en_alerta / total_bienes × 100`. | `inventario` |
| **Bienes por condición** | Distribución del estado del patrimonio. | `COUNT(*)` agrupado por `condicion`. | `inventario` |

### 2.5 Feed de actividad reciente — solo Admin (rol 1)

| Indicador | Para qué sirve | Fórmula / Cómo se calcula | Fuente |
|-----------|----------------|----------------------------|--------|
| **Últimas 15 operaciones** | Trazabilidad rápida de cambios en el sistema. | Últimos 15 registros del log de auditoría (tabla afectada, operación, usuario, fecha). | `audit_logs` ⟕ `usuarios` |

### 2.6 Alertas del dashboard — según rol

| Alerta | Condición que la dispara | Roles |
|--------|--------------------------|-------|
| Contratos por vencer | `kpiContratosVencen > 0` (ver 2.1) | 1, 2 |
| Pasantes próximos a culminar | Pasantes `En Curso` con `fecha_fin` dentro de `dias_preaviso_pasante` | 1, 3 |
| Actividades de formación vigentes | `actividades_activas > 0` | 1, 3 |
| Bienes en estado de alerta | `bienes_alerta > 0` | 1, 4 |

---

## 3. Página de Indicadores de Gestión (RF30)

Ruta: `reportes/indicadores`. Acceso: todos los roles. Origen: `ReportesController::indicadores()`. Esta vista es **transversal** (no filtra por rol — muestra todos los bloques).

### 3.1 KPIs de resumen (tarjetas superiores)

| KPI | Para qué sirve | Fórmula | Fuente |
|-----|----------------|---------|--------|
| **Empleados** | Plantilla activa total. | `COUNT(*)` empleados activos. | `empleados` |
| **Visitas hoy** | Afluencia del día. | `COUNT(*)` visitas con `DATE(hora_entrada)=hoy`. | `visitas` |
| **Actividades activas** | Formación en curso/programada. | `COUNT(*)` talleres `En Curso`+`Programado`. | `talleres` |
| **Formados (año)** | Alcance formativo del año. | `COUNT(*)` inscripciones en talleres del año. | `participantes_taller` ⋈ `talleres` |
| **Rutas activas** | Oferta turística disponible. | `COUNT(*)` rutas `Activa`. | `rutas` |
| **Pasantes en curso** | Pasantías vigentes. | `COUNT(*)` pasantes `En Curso`. | `pasantes` |
| **Bienes activos** | Patrimonio operativo. | `COUNT(*)` inventario activo. | `inventario` |
| **Bienes en alerta** | Patrimonio en `Dañado`/`En Reparación`. | `COUNT(*)` con esas condiciones. | `inventario` |

### 3.2 Sección Personal

| Indicador | Para qué sirve | Fórmula | Fuente |
|-----------|----------------|---------|--------|
| **Empleados por departamento** | Distribución organizativa de la plantilla. | `COUNT(empleados)` por departamento (incluye departamentos con 0). | `departamentos` ⟕ `empleados` |
| **Asistencias por mes (4 meses)** | Tendencia de marcaje. | `COUNT(*)` agrupado por `YYYY-MM`, últimos 4 meses. | `asistencias` |
| **PROP-P01 — Distribución por tipo de contrato** | Composición contractual de la plantilla (estabilidad laboral). | `COUNT(*)` empleados agrupado por `tipo_contrato` (NULL/'' → "Sin especificar"). | `empleados` |

### 3.3 Sección Formación

| ID | Indicador | Para qué sirve | Fórmula | Fuente |
|----|-----------|----------------|---------|--------|
| — | **Talleres por mes (6 meses)** | Ritmo de programación formativa. | `COUNT(*)` por `YYYY-MM`, últimos 6 meses. | `talleres` |
| — | **Talleres por tipo** | Mezcla de Taller/Charla/Inducción. | `COUNT(*)` agrupado por `tipo_actividad`. | `talleres` |
| — | **Participantes internos vs externos** | Cuánta formación es para personal IMATUR vs comunidad. | `SUM` condicional sobre `talleres.es_interna` de las inscripciones. | `participantes_taller` ⋈ `talleres` |
| **F-3** | **Demografía de formación (año)** | Desagregación por género/edad de las personas atendidas (reporte de género). | `SUM` de `mujeres`, `hombres`, `ninas`, `ninos`, `total_atendidas` de los informes de talleres del año. | `taller_informes` ⋈ `talleres` |
| **F-4** | **Cobertura territorial** | En cuántos municipios se ejecuta formación (alcance geográfico). | `COUNT(DISTINCT municipio)` de las sedes de talleres del año, sobre el total de municipios. Incluye lista de municipios cubiertos. | `talleres` ⋈ `ubicaciones_formacion` ⋈ `parroquia` ⋈ `municipio` |
| **F-2** | **Tipo de entidad atendida** | Qué clase de público se forma (Personal IMATUR / tipo de ente externo). | Talleres y participantes agrupados por `CASE`: interna → "Personal IMATUR"; si no, `tipo_ente`; si vacío → "Sin especificar". | `talleres` ⟕ `participantes_taller` |
| **F-5** | **Capacitadores activos (top 10)** | Productividad de facilitadores (actividades y personas formadas). | Por facilitador: `COUNT(talleres)` y `SUM(inscritos)` del año, orden desc, límite 10. | `talleres` ⋈ `empleados` ⋈ `personas` ⟕ `participantes_taller` |
| **F-META** | **Meta anual de formación** | Avance contra la meta institucional de actividades ejecutadas. | `talleres_finalizados_año / meta_talleres_anio`. Numerador = `COUNT(*)` talleres `Finalizado` del año. | `talleres` + `configuracion_sistema` |
| **PROP-F01** | **Tasa de ocupación de actividades (año)** | Eficiencia en el uso del cupo ofertado. | `SUM(inscritos) / SUM(cupo_maximo) × 100` sobre talleres del año, no cancelados, con `cupo_maximo > 0`. | `talleres` ⟕ `participantes_taller` |
| **PROP-F02** | **Tasa de finalización (año)** | Eficacia de ejecución. | `finalizados / total_actividades_año × 100`. | `talleres` |
| **PROP-F05** | **Tasa de cancelación (año)** | Indicador de deserción/pérdida de actividades. | `cancelados / total_actividades_año × 100`. | `talleres` |

### 3.4 Sección Turismo

| ID | Indicador | Para qué sirve | Fórmula | Fuente |
|----|-----------|----------------|---------|--------|
| **T-2** | **Participantes por tipo de ruta** | Demanda relativa de cada tipo de ruta. | Por `tipo_ruta` (NULL → "General"): `COUNT(DISTINCT rutas)` y `COUNT(participantes)`. | `rutas` ⟕ `participantes_ruta` |
| **T-1** | **Meta anual de rutas** | Avance contra la meta de rutas ejecutadas. | `rutas_finalizadas_año / meta_rutas_anio`. Numerador = `COUNT(*)` rutas `Finalizada` con `EXTRACT(YEAR FROM COALESCE(fecha_visita, created_at)) = año`. | `rutas` + `configuracion_sistema` |
| **T-DEMO** | **Demografía de participantes en rutas (año)** | Desagregación por género/edad de quienes recorren rutas ejecutadas en el año. | Conteos condicionales: con cédula → `personas.genero` (M/F); libres (niños/as) → `genero_libre`. Solo rutas con `COALESCE(fecha_visita, created_at)` del año. | `participantes_ruta` ⟕ `personas` (+ `EXISTS rutas`) |

> **T-3 (Instituciones participantes en rutas) — RETIRADO.** El campo `id_institucion` se eliminó del flujo de inscripción de rutas; el indicador quedó sin fuente de datos y se removió del controlador y de la vista (2026-05-31).
> **Nivel de dificultad — ELIMINADO** (migración 021). IMATUR no usa esa distinción; ya no aparece en ningún indicador, filtro ni reporte de rutas.

### 3.5 Sección Recepción (Visitas)

| Indicador | Para qué sirve | Fórmula | Fuente |
|-----------|----------------|---------|--------|
| **Visitas por día (14 días)** | Tendencia de afluencia reciente. | `COUNT(*)` por día, últimos 14 días. | `visitas` |
| **Visitas por motivo (top 6)** | Razones principales de visita. | `COUNT(*)` agrupado por `motivo` (NULL/'' → "Sin especificar"), top 6. | `visitas` |

### 3.6 Sección Inventario

| ID | Indicador | Para qué sirve | Fórmula | Fuente |
|----|-----------|----------------|---------|--------|
| — | **Inventario por categoría** | Composición del patrimonio por tipo de bien. | `COUNT(bienes)` por categoría (incluye categorías con 0). | `categorias` ⟕ `inventario` |
| — | **Inventario por condición** | Estado general del patrimonio. | `COUNT(*)` agrupado por `condicion`. | `inventario` |
| **PROP-I01** | **Tasa de depreciación operativa** | % del patrimonio deteriorado (necesidad de mantenimiento/reposición). | `deteriorados / total × 100`, donde deteriorados = `Dañado` + `En Reparación`. | `inventario` |

---

## 4. KPIs de cabecera por reporte

Cada reporte muestra tarjetas-resumen calculadas por su función `stats*()`. Respetan los **mismos filtros** activos del reporte (fechas, estado, etc.), salvo donde se indique.

### 4.1 Reporte de Asistencia — `statsAsistencia()` · roles 1, 2

| KPI | Fórmula | Fuente |
|-----|---------|--------|
| Total registros | `COUNT(*)` de asistencias en el rango y filtros. | `asistencias` ⋈ `empleados` ⋈ `personas` |
| Empleados con registro | `COUNT(DISTINCT id_empleado)`. | idem |
| Días con registros | `COUNT(DISTINCT fecha)`. | idem |

### 4.2 Reporte de Talleres — `statsTalleres()` · roles 1, 3

| KPI | Fórmula | Fuente |
|-----|---------|--------|
| Total actividades | `COUNT(*)` de talleres (con filtros). | `talleres` |
| Finalizados | `COUNT(estado='Finalizado')`. | `talleres` |
| En curso | `COUNT(estado='En Curso')`. | `talleres` |
| Programados | `COUNT(estado='Programado')`. | `talleres` |
| Cancelados | `COUNT(estado='Cancelado')`. | `talleres` |
| Total inscritos | `SUM` de participantes activos por taller. | `participantes_taller` |

### 4.3 Reporte de Rutas — `statsRutas()` + `statsRutasPorTipo()` · roles 1, 3

| KPI | Fórmula | Fuente |
|-----|---------|--------|
| Total rutas | `COUNT(*)` rutas activas. | `rutas` |
| Activas | `COUNT(estado='Activa')`. | `rutas` |
| Inactivas | `COUNT(estado='Inactiva')`. | `rutas` |
| En mantenimiento | `COUNT(estado='En Mantenimiento')`. | `rutas` |
| Finalizadas | `COUNT(estado='Finalizada')`. | `rutas` |
| **Demografía consolidada por tipo de ruta** | Por `tipo_ruta`: rutas, finalizadas, y `SUM` de mujeres/hombres/niñas/niños/total atendidos de los informes. | `rutas` ⟕ `ruta_informes` |

### 4.4 Reporte de Pasantes · roles 1, 3

| KPI | Fórmula | Fuente |
|-----|---------|--------|
| Total | `COUNT(*)` pasantes (con filtros). | `pasantes` |
| En curso / Culminados / Postulados / Aceptados / Rechazados | `COUNT(estado=…)` por cada estado. | `pasantes` |

### 4.5 Reporte de Visitantes — `statsVisitantes()` · roles 1, 2

| KPI | Fórmula | Fuente |
|-----|---------|--------|
| Total visitas | `COUNT(*)` en el rango de fechas. | `visitas` |
| Visitantes únicos | `COUNT(DISTINCT id_visitante)`. | `visitas` |

### 4.6 Reporte de Inventario — `statsInventario()` · roles 1, 4

| KPI | Fórmula | Fuente |
|-----|---------|--------|
| Total bienes | `COUNT(*)` inventario activo. | `inventario` |
| Nuevos / Buenos / Regulares / Dañados / En Reparación | `COUNT(condicion=…)` por cada condición. | `inventario` |

### 4.7 Reporte de Bienes Dados de Baja · roles 1, 4

| KPI | Fórmula | Fuente |
|-----|---------|--------|
| Total histórico de bajas | `COUNT(*)` con `is_active=FALSE AND deleted_at IS NOT NULL`. | `inventario` |
| Bajas del año | Igual, con `EXTRACT(YEAR FROM deleted_at) = año actual`. | `inventario` |

---

## 5. Glosario de fuentes de datos

| Tabla | Rol en los indicadores |
|-------|------------------------|
| `empleados` | Plantilla, contratos, deltas de personal. |
| `asistencias` | Marcaje diario; tendencias mensuales. |
| `departamentos` | Agrupación organizativa. |
| `visitas` / `visitantes` | Afluencia, recepción, visitantes únicos. |
| `talleres` | Actividades formativas; estados, tipos, cupos, metas. |
| `participantes_taller` | Inscripciones (formados); internos/externos; ocupación. |
| `taller_informes` | Demografía consolidada de formación (género/edad). |
| `ubicaciones_formacion` / `parroquia` / `municipio` | Cobertura territorial de formación. |
| `rutas` | Oferta turística; estados; metas; tipos de ruta. |
| `participantes_ruta` | Inscripciones a rutas; demografía (con cédula y libre). |
| `ruta_informes` | Demografía consolidada de rutas ejecutadas. |
| `pasantes` | Pasantías por estado. |
| `inventario` | Patrimonio; condición; deterioro; bajas. |
| `categorias` | Composición del patrimonio. |
| `audit_logs` | Feed de actividad reciente. |
| `configuracion_sistema` | Metas anuales y umbrales de alerta. |

---

## Notas de cálculo (importantes para interpretar los números)

1. **"Año actual" = año del servidor** (`date('Y')`). No es año fiscal ni móvil.
2. **Deltas del dashboard** se ocultan cuando el período base es 0 (no hay con qué comparar) o la variación absoluta es < 0.5 % (ruido).
3. **Rutas "ejecutadas"** = estado `Finalizada`, contadas por `fecha_visita` (o `created_at` si no hay fecha de visita). Cada ejecución es un evento único: dos recorridos del mismo lugar en fechas distintas cuentan como dos rutas finalizadas.
4. **Talleres "ejecutados"** = estado `Finalizado`, contados por `fecha_inicio`.
5. **Ocupación** ignora actividades sin cupo definido (`cupo_maximo > 0`) y las canceladas, para no distorsionar el porcentaje. El cupo es **estimación no bloqueante** (se puede exceder al inscribir; solo advierte).
6. **Demografía de formación** proviene del **informe** del taller (`taller_informes`), que es un dato consolidado capturado manualmente — no se deriva de la lista de inscritos.
7. **Demografía de rutas** sí se deriva de los participantes: género de `personas` para inscritos con cédula y `genero_libre` para niños/as sin cédula.
8. Todos los conteos de personas/actividades excluyen registros con soft-delete (`is_active = FALSE`), excepto los indicadores de bajas de inventario, que precisamente miden lo eliminado.
