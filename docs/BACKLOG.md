# BACKLOG ÚNICO — SIGTUR-IMATUR

**Última actualización:** 2026-06-25 · **Migraciones aplicadas:** hasta **050** · **Rama:** `development_stage`

Documento **único** de seguimiento: qué falta por hacer y decidir. Consolida y reemplaza a
`REGISTRO_NEGOCIO.md`, `DECISIONES_PENDIENTES.md`, `preguntas_modelo_negocio.md`,
`AUDITORIA_SENIOR_2026-05-31.md`, `Notas.md` y `PLAN_ENTREGA.md`.

- **Referencia técnica:** `CLAUDE.md` (arquitectura, BD, convenciones, migraciones).
- **Reglas de negocio por módulo (detalle):** `REGLAS_NEGOCIO_*.md`, `MODELO_NEGOCIO_RRHH.md`, `ESTRUCTURA_ORGANIZATIVA.md`.
- **Indicadores:** `INDICADORES_GESTION.md`.

**Leyenda:** 🔴 bloquea BD/lógica · 🟡 alto impacto · 🟢 menor · ✅ hecho · 🔒 espera decisión/insumo del cliente · 🛠️ implementable ya

---

## 1. ESTADO GLOBAL

- **RRHH:** completo salvo **Nómina/Liquidación** (espera formatos). Vacaciones (días) ✅; egreso/reingreso ✅; traslados ✅; disciplina ✅; constancias ✅.
- **Formación / Turismo / Inventario / Recepción:** CRUD y reglas operativas completos. Quedan preguntas de impacto medio/bajo.
- **Cuello de botella de la entrega:** ya **no es código**, son **decisiones/insumos del cliente** (sección 3).

---

## 2. LO RESUELTO EN ESTE CICLO

### 2026-06-25 — Bloque CMI de indicadores (sin migración)

Alineación del panel `reportes/indicadores` con el *Cuadro de Mando Integral* del documento del proyecto. 8 indicadores nuevos (prefijo `CMI-*` en `INDICADORES_GESTION.md`), solo lectura sobre datos existentes:

| # | Indicador | Fórmula |
|---|-----------|---------|
| ✅ CMI-RH01 | Cumplimiento de jornada | horas reales / programadas (mes, días con marcaje completo + horario) |
| ✅ CMI-RH02 | Precisión de asistencia | registros con salida / total (mes) |
| ✅ CMI-RH03 | Documentación del personal | empleados con recaudos obligatorios completos / total |
| ✅ CMI-I01 | Precisión del registro (inventario) | durables con código BN (+fungibles) / total |
| ✅ CMI-I02 | Movimientos de bienes | conteo por `tipo_movimiento` (año) |
| ✅ CMI-I03 | Asignación de responsables | durables con último movimiento = Asignación / total durables |
| ✅ CMI-F01 | Cobertura por parroquia | parroquias con actividad / total (año) |
| ✅ CMI-T01 | Frecuencia de rutas | rutas finalizadas por mes (6 meses) |

> Archivos: `ReportesController::indicadores()` + `views/reportes/indicadores.php`. Pendientes del documento que **no** se implementaron (ver 3.4 y 3.8): stock mínimo, instituciones participantes en rutas, tiempo de generación de reportes.

### 2026-06-21 (mig. 043–050)

| # | Entregable | Migración |
|---|-----------|-----------|
| ✅ | **Export Excel/PDF transversal** en todo listado `data-tabla-buscable` (`sigturExportarTabla`, opt-out `data-no-export`) | — |
| ✅ | **RIF institucional centralizado** en `ConfigSistema::rif()` + `window.SIGTUR_RIF` (oficial G-20008498-7) | 043 |
| ✅ | **Inventario Durable/Fungible** (`tipo_bien`+`cantidad`, validación por tipo) — cierra D-IN05 | 044 |
| ✅ | **Vacaciones (días)**: 15 hábiles +1/año tope 30, antigüedad total, feriados, saldo acumulado + ajuste inicial (`/vacaciones`) — cierra D-RH04/05 (parte de días) | 045/046 |
| ✅ | **3C** badge "Elegible a fijo" (señal visual, no promueve) | — |
| ✅ | **3D** Traslado de departamento = reasignación con historial (`empleado_traslados`) | 047 |
| ✅ | **3E** Faltas con `tipo` (injustificada/incumplimiento) + escalado falta→amonestación (`id_falta_origen`) | 048 |
| ✅ | **U4** Alertas de vencimiento: talleres vencidos (Dashboard + Centro de Alertas role-aware con contratos/pasantes) | — |
| ✅ | **O4** Filtro por departamento en lista de empleados · **O5** horario Estándar 8am-4pm→8am-2pm | 049 |
| ✅ | **3F** Limpieza: eliminados `taller_inventario` (D-FO07) y `es_brigadista` (D-FO08) | 050 |
| ✅ | **Fix UI:** `js-search` inflaba la altura dentro de `.sig-field` (flex-column) — corregido en CSS | — |

> Bloques 1 (revisión profesor) y 2 (UX) **cerrados**; la mayoría ya estaba hecho al verificar. Único pendiente real del Bloque 1: **B13** (ver 3).

---

## 3. DECISIONES / INSUMOS PENDIENTES DEL CLIENTE 🔒

Bloquean desarrollo. Cada una incluye **qué preguntar**.

### 3.1 🔴 Nómina / Liquidación (R-11 · D-RH34/D-RH14)
- **Falta:** los **formatos de las cuentas** que la Alcaldía usa para la nómina, y las tablas de sueldos (LOTTT / función pública).
- **Preguntar:** ¿el sistema **calcula** la nómina/liquidación o solo **registra** y genera el reporte de envío? · ¿formato exacto del archivo a la Alcaldía? · estructura de salario integral (base + cuota vacaciones + cuota utilidades) y conceptos de liquidación · ¿el bono vacacional lo calcula el sistema?
- **Al desbloquear:** módulo Nómina + liquidación (probable v1 = registro + reporte, no cálculo completo).

### 3.2 🟡 B13 — Mínimo de antigüedad para constancia
- El profesor pidió **mínimo 6 meses de servicio** para emitir constancia; el sistema hoy **no exige antigüedad** (decisión previa). **Contradicción.**
- **Preguntar:** ¿se exige mínimo 6 meses para alguna constancia (¿cuáles?) o se mantiene sin mínimo?

### 3.3 🟡 O1 — Cargos por departamento
- **Preguntar:** ¿qué cargos existen en cada departamento y sus diferencias? ¿Se cataloga la relación cargo↔departamento o el cargo es transversal (como hoy)?

### 3.4 Inventario
| ID | Pregunta |
|----|----------|
| 🔴 D-IN06 | ¿"Responsable del bien" nominal? ¿Un bien asignable a >1 empleado? (FK `id_responsable` o tabla de asignación) |
| 🟡 D-IN10 (H-04) | ¿La Baja/Mantenimiento cambia automáticamente la `condicion` del bien? |
| 🟡 D-IN09 | ¿Registrar costo de adquisición, fecha de compra y proveedor? |
| 🟡 D-IN11 (CMI) | **Stock mínimo de papelería** (indicador del documento, no implementado). Requiere columna `inventario.stock_minimo` (fungibles) + alerta + indicador. **Preguntar:** ¿qué ítems se controlan y con qué umbral por ítem? |
| 🟢 D-IN03 | Confirmar lista final de categorías. |

### 3.5 Turismo (Rutas)
| ID | Pregunta |
|----|----------|
| 🟡 D-RT02 | Tarifa Cumaná Histórica: ¿quién cobra y cuál es el flujo de pago? (arquitectura `tiene_tarifa`/`tarifa_monto` lista) |
| 🟡 D-RT03 | Al **Finalizar** una ruta, ¿generar informe/oficio automáticamente? |
| 🟡 D-RT05 (CMI) | **Instituciones participantes en rutas** (indicador del documento). Se **retiró** a propósito (`id_institucion` fuera del flujo, ver H-09). Reintroducirlo revierte una decisión previa. **Preguntar:** ¿se registra de nuevo la institución del grupo que recorre la ruta? |
| 🟢 D-RT04 | Facilitador externo: ¿lista gestionada o texto libre? |

### 3.6 Formación
| ID | Pregunta |
|----|----------|
| 🟡 D-FO06 | ¿CRUD de **oficios base** (`oficios`) + vínculo con `talleres.id_oficio`? (tabla sin UI) |
| 🟢 D-FO05 | ¿Parámetros internos de meta para comparar planificado vs ejecutado? |
| 🟢 D-NEW01 | ¿Activar en UI el correlativo de oficios de formación (FORM-XXX)? |

### 3.7 Transversal
| ID | Pregunta |
|----|----------|
| 🟡 D-TX03 | Migración de **históricos** (Excel/papel): definir módulos + obtener archivos fuente. |
| 🟢 D-OF03 | Libro de correspondencia unificado (oficios emitidos/recibidos). |
| ⚪ D-CMI01 | **"Reducción del tiempo de generación de reportes"** (figura en el documento): es una métrica operativa **antes/después** (manual vs. sistema), **no** un indicador que la app pueda calcular de sí misma. Se mide fuera del sistema (justificación de impacto), no se implementa como KPI. |

---

## 4. AUDITORÍA TÉCNICA ABIERTA

| # | Hallazgo | Estado | Cierra con |
|---|----------|--------|-----------|
| H-04 | Baja de bien no actualiza `condicion` | ⚠️ Abierto | D-IN10 (3.4) |
| H-09 | Columnas inertes restantes: `participantes_ruta.id_institucion`, `rutas.tiene_tarifa`/`nombre_facilitador_externo`, `talleres.id_oficio` | ⚠️ Parcial (ya se limpió `es_brigadista`) | D-RT02/04, D-FO06 — usar o limpiar |
| H-10 | Tablas sin UI: solo queda **`oficios`** (vacaciones ✅, `taller_inventario` eliminada) | ⚠️ Parcial | D-FO06 |

> Resueltos previamente: H-01, H-02, H-03 (visitas inmutables), H-05 (validaciones servidor), H-06 (correlativo atómico), H-07 (enums centralizados), H-08 (FKs validadas), H-11 (género M/F).

---

## 5. PROGRAMACIÓN FALTANTE / BACKLOG TÉCNICO 🛠️

Implementable sin decisión de negocio (priorizar cuando convenga):

| Módulo | Tarea | Origen |
|--------|-------|--------|
| RRHH | Reporte de **saldo de vacaciones** por empleado (el módulo ya calcula el saldo) | BRH-07 |
| RRHH | Réplica imprimible del **formato físico de asistencia** | MOD-RRHH 6.2 |
| Formación | Informe **trimestral consolidado** (metas/logros) | D-RE01/02 |
| Formación | Tabla `taller_facilitadores` (múltiples facilitadores) — solo si el cliente lo pide | D-FO08-bis |
| Turismo | Reporte de **ejecuciones de ruta** (múltiples fechas) | BRT-05 |
| Recepción | "**Visitas activas del día**" en Dashboard + estadísticas de visitas en Reportes | BVIS-04/05 |
| Transversal | Importación de datos históricos desde Excel (depende de D-TX03) | D-TX03 |

---

## 6. VERIFICACIÓN MANUAL PENDIENTE (probar en navegador)

- **B1** "botón Guardar de RRHH": no hay defecto estático; hacer un alta de empleado de punta a punta para cerrarlo.
- Export **Excel/PDF** en cualquier listado CRUD.
- Toggle **Durable/Fungible** en el modal de inventario.
- Registrar un **período de vacaciones** y verificar el conteo de días hábiles (excluye finde+feriados).
- Registrar un **traslado** y un **escalado falta→amonestación**.

---

## 7. REGLAS DE NEGOCIO — ESTADO POR MÓDULO (resumen)

> Detalle funcional en los `REGLAS_NEGOCIO_*.md` / `MODELO_NEGOCIO_RRHH.md`.

- **RRHH:** ✅ organigrama jerárquico, ficha técnica + wizard, expediente/recaudos, horarios/grupos A-B/OAC, asistencia/puntualidad, permisos/reposos, amonestaciones+faltas (con tipo y escalado), constancias multi-tipo, egreso/reingreso, traslados, **vacaciones (días)**, badge elegible a fijo. 🔒 Falta: **Nómina/Liquidación**.
- **Formación:** ✅ talleres/charlas/inducciones, participantes (adulto/niño, alta sin botón buscar), informe demográfico auto, evidencias, estados con auto-transición, lista de asistencia, reportes. 🔒 Falta: oficios base (D-FO06).
- **Turismo (Rutas):** ✅ rutas por ejecución, puntos+mapa Leaflet offline, participantes, oficios, estado Finalizada, demografía. 🔒 Falta: tarifa (D-RT02), informe/oficio automático al finalizar (D-RT03).
- **Inventario:** ✅ bienes, categorías, ubicaciones, movimientos, bajas, **Durable/Fungible**, reportes/kardex. 🔒 Falta: responsable del bien (D-IN06), costo/proveedor (D-IN09), baja→condición (D-IN10).
- **Recepción (Visitantes):** ✅ visitantes + visitas (bitácora inmutable), reportes. 🛠️ Backlog: visitas activas del día.
- **Sistema:** ✅ RBAC dinámico, usuarios/roles, auditoría humanizada + papelera, configuración institucional, idempotencia (token), export transversal.

---

## 8. OBSOLETO / SIN EFECTO
- Módulos **Instituciones externas** y **Actividades de ruta**: retirados (2026-05-31).
- `taller_inventario`, `participantes_taller.es_brigadista`: eliminados (mig.050).
- `nivel_dificultad`, `ruta_inventario`: eliminados (mig.019/021).
