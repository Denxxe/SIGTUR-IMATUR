# PLAN DE ENTREGA — SIGTUR-IMATUR

**Generado:** 2026-06-21 · **Rama:** `development_stage` · **Migraciones aplicadas:** hasta **042**

Tablero de tareas para asignar de cara a la entrega. Estados verificados **en código** el 2026-06-21
(no solo según memoria). Editá la columna **Responsable** y **Estado** a medida que avancen.

## Leyenda de estado
- ✅ **HECHO (verificado)** — confirmado en código en esta revisión.
- 🟡 **PARCIAL** — existe base, falta completar.
- ❌ **PENDIENTE** — no implementado.
- 🔁 **REPRODUCIR** — reportado por el profesor; hay que reproducir el fallo antes de tocar código.
- 🔒 **BLOQUEADO** — requiere decisión de negocio del cliente antes de codificar.

---

## BLOQUE 1 — Revisión del profesor (`docs/Notas.md`)

| # | Tarea | Estado | Evidencia / Nota | Resp. |
|---|-------|--------|------------------|-------|
| B1 | "Botón Guardar de RRHH no funciona" | 🔁 REPRODUCIR | Wizard `empleados/form.php`. No reproducible por análisis estático; probar alta de punta a punta. Si el typo de `form.php:184` rompe el render del paso, podría ser la causa. | |
| B2 | N° Expediente se autoincrementa solo, no editable | ✅ HECHO | `form.php:139-144` readonly + preview `proximo_expediente`; mig.040 `EXP-####`. | |
| B3 | Validar TODOS los campos de empleados por paso | 🟡 PARCIAL | Validación por paso existe; auditar campo por campo (no-vacíos, formato, requeridos reales). | |
| B4 | Lógica fecha ingreso/egreso; ocultar egreso según contrato | ✅ HECHO | mig.041 separa `fecha_vencimiento_contrato` de `fecha_egreso`; `wzVencToggle()` oculta para Fijos. Confirmar con el profesor que cubre su pedido. | |
| B5 | Primeras letras en mayúscula (auto) | ✅ HECHO | `sigtur-validations.js:236` capitaliza texto libre. | |
| B6 | Validación de RIF (cliente + servidor) | ✅ HECHO | `initRifInput` + `Controller::rifValido/normalizarRif`. | |
| B7 | Edad automática desde fecha de nacimiento | ✅ HECHO | `Util::edad` + `initEdadInput`/`sigturEdad`. | |
| B8 | Teléfono con todos los dígitos + prefijo | ✅ HECHO | `initTelefonoInput` (prefijo VE + 7 dígitos). | |
| B9 | Etiquetas de color por estado (Formación) | ✅ HECHO | `Taller::ESTADO_BADGES` / `Ruta::ESTADO_BADGES`. Confirmar con profesor qué módulo exacto señaló. | |
| B10 | Alerta por registro duplicado (no guardar) | ✅ HECHO | `Empleado::existeCedula()` bloquea alta; reporte `reportes/duplicados`. | |
| B11 | Alta de participantes: el recuadro debe **agregar** (no buscar); lupa aparte para buscar ya agregados; elegir **niño vs adulto** antes de pedir cédula | ❌ PENDIENTE | Rediseño UX del alta en talleres/rutas. | |
| B12 | "No busca al reingresar al mismo recuadro" | ❌ PENDIENTE | Bug del evento de búsqueda en el mismo flujo de B11. | |
| B13 | Mínimo 6 meses de antigüedad (constancia/egreso) | 🔒 BLOQUEADO | **Contradice** decisión B13 previa ("constancias sin antigüedad mínima"). Cerrar con cliente antes de implementar. | |
| B14 | Anular amonestación con motivo + describir causa de despido | ✅ HECHO | mig.042 `motivo_anulacion`; `MOTIVOS_EGRESO`. | |

> **Conclusión Bloque 1:** de los 14 ítems, **8 ya están hechos**. El trabajo real es B1 (reproducir), B3 (completar), **B11/B12 (rediseño del alta de participantes)** y resolver B13 con el cliente.

---

## BLOQUE 2 — UX / validaciones / limpieza (media)

| # | Tarea | Estado | Área | Resp. |
|---|-------|--------|------|-------|
| U1 | Uniformar/limpiar los CRUD (orden, estética) | ❌ PENDIENTE | Transversal | |
| U2 | Nivel académico → grado; resolver `profesion` vs `titulo` | ✅ HECHO (verificado 2026-06-21) | RRHH | — |
| U3 | Filtro "por tiempo / próximos" en listados | ❌ PENDIENTE | Formación/Turismo | |
| U4 | Notificar fechas vencidas (taller/contrato/pasante) — centro de alertas | ✅ HECHO (2026-06-21) | Transversal | — |
| U5 | Inventario: tipo de bien Durable vs Fungible + validación por tipo | ✅ HECHO (mig.044, 2026-06-21) | Inventario | — |
| U6 | Export Excel/PDF en listados CRUD | ✅ HECHO (transversal, 2026-06-21) | Transversal | — |
| U7 | Unificar RIF institucional vía `ConfigSistema` | ✅ HECHO (mig.043, 2026-06-21) | Transversal | — |
| U8 | ~~Typo HTML `form.php:184`~~ | ❎ FALSO POSITIVO (el archivo ya estaba correcto) | RRHH | — |

> **Avance Bloque 2 (2026-06-21):** U2 ya estaba resuelto (nivel académico usa grados; no existe campo "Título", solo "Profesión"). U4/U5/U6/U7 implementados y commiteados. U8 era artefacto de visualización (no había typo). **Quedan U1 y U3.**
>
> **U4 (vencimientos):** se agregó alerta de **talleres/actividades vencidas** (`Taller::contarVencidos()`: Programado con fecha pasada o En Curso con `fecha_fin` pasada) al **Dashboard** (roles 1,3) y al **Centro de Alertas**. El Centro de Alertas quedó **role-aware**: RRHH/admin ven contratos por vencer + permisos + disciplina + expedientes; Formación/admin ven talleres vencidos + pasantes por culminar. Contratos y pasantes ahora también se consolidan ahí (antes solo en el Dashboard).
>
> **Decisiones tomadas:** U2 → solo "Profesión". U5 → Durable vs Fungible. U7 → RIF oficial **G-20008498-7** (editable en /config).

---

## BLOQUE 3 — Reglas de negocio PENDIENTES (bloquean desarrollo) 🔒

### 3A. Vacaciones (R-8, D-RH04/05) — `Notas.md` aportó la fórmula base
- Base: **15 días + 1 día por año de servicio**.
- Fin de semana = día laboral (órgano turístico) → descanso normal; **excepciones por eventos** (Carnaval, Semana Santa, Santa Inés, Cruz de Mayo).
- Vacaciones no disfrutadas **se acumulan** sin expirar; se calculan en liquidación.
- Comisión de servicio: coordinadas con ente de origen.
- **Falta cerrar:** ¿saldo automático o manual?, períodos solapados, calendario de feriados/eventos.
- **Tarea al desbloquear:** `VacacionesController` + UI sobre tabla `vacaciones` (existe, sin UI).

### 3B. Nómina / Liquidación (R-11, D-RH34) — estructura aportada
- Nómina la recibe de la Alcaldía; RRHH calcula; Alcaldía analiza y paga.
- **Salario integral = salario base + cuota de vacaciones + cuota de utilidades.**
- **Liquidación = salario integral + vacaciones no disfrutadas + conceptos pendientes** (fracción utilidades, tiempo de servicio).
- **Falta:** ¿el sistema calcula o solo registra?, formato exacto, tablas de sueldos (LOTTT/función pública).
- **Tarea al desbloquear:** definir alcance (v1 probablemente registro + reporte, no cálculo completo).

### 3C. Traspaso Contratado → Fijo
- Por tiempo de servicio: Alcaldía 5-6, Gobernación 3-6, IMATUR 5-6 años; años previos se suman; decisión final de la Presidenta; Fijo requiere **carta de asignación**.
- **Tarea:** alerta "elegible para fijo" + validación de carta.

### 3D. Traspaso de personal entre departamentos (O3, nuevo)
- Reunión de directores/coordinadores; aprobación final Directora general o coordinador del depto emisor.
- **Falta:** ¿flujo con aprobación o reasignación con historial?

### 3E. Faltas justificadas vs injustificadas
- Justificadas: servicio médico a familiar directo (con informe) o diligencia notificada al jefe. Injustificadas (3 = despido) ya existen.
- **Falta:** ¿se modela el flujo de justificación con soporte documental?

### 3F. Otras reglas abiertas (de `REGISTRO_NEGOCIO.md`)
| ID | Tema | Módulo |
|----|------|--------|
| D-IN10 / H-04 | ¿Baja/Mantenimiento cambia la `condicion` del bien? | Inventario |
| D-IN06 | ¿Responsable del bien? ¿un bien a >1 empleado? | Inventario |
| D-IN09 | ¿Costo de adquisición / fecha compra / proveedor? | Inventario |
| D-IN05 | ¿Distinguir fungibles vs durables? | Inventario |
| D-RT02 | Tarifa Cumaná Histórica: ¿quién cobra y flujo de pago? | Turismo |
| D-FO07 | ¿Se usa `taller_inventario`? | Formación |
| D-FO08 | ¿Qué es `es_brigadista`? (usar o eliminar) | Formación |

---

## BLOQUE 4 — Estructura organizativa / cargos

| # | Tarea | Estado | Resp. |
|---|-------|--------|-------|
| O1 | Cargos por departamento: definir qué cargos existen en cada uno y sus diferencias (Director ≠ Coordinador) | 🔒 BLOQUEADO (info cliente) | |
| O2 | Reforzar organigrama (Presidencia→Dirección→Coordinación→Oficina→Adscritos) | ✅ HECHO (mig.027) — confirmar visual | |
| O3 | Traspaso entre departamentos con aprobación (ver 3D) | 🔒 BLOQUEADO | |
| O4 | Expediente organizado por departamento (antes por tipo) — confirmar vista | 🔁 VERIFICAR | |
| O5 | Actualizar horario institucional 8am-4pm → **8am-2pm**; OAC subgrupos 7-12 y 10-2 | 🔁 VERIFICAR seed `horarios` | |

---

## BLOQUE 5 — Deuda técnica / housekeeping

**Urgente (riesgo de pérdida):**
- [ ] **Commitear** migraciones 040/041/042, `docs/Notas.md`, `public/uploads/expedientes/` y ~35 archivos modificados sin commit.
- [ ] **Sincronizar docs:** `CLAUDE.md` y `REGISTRO_NEGOCIO.md` hablan de mig.021/039; actualizar a **042**.

**Auditoría abierta (no bloquea negocio):**
- H-04 → depende de D-IN10.
- H-09 columnas inertes: `id_institucion`, `tiene_tarifa`, `nombre_facilitador_externo`, `id_oficio` — usar o limpiar.
- H-10 tablas sin UI: `vacaciones` (bloqueada), `taller_inventario`, `oficios`.

**Backlog implementable sin decisión:**
- Réplica imprimible del formato físico de asistencia.
- Informe trimestral consolidado de Formación.
- Reporte de ejecuciones de ruta (múltiples fechas).
- "Visitas activas del día" en Dashboard + estadísticas en Reportes.

---

## PLAN DE SPRINTS SUGERIDO

1. **Sprint 1 — Quick wins (ya casi listo):** B1 (reproducir), B3, **B11/B12**, U8 (typos). Cerrar B13 con cliente. Commitear todo (Bloque 5 urgente).
2. **Sprint 2 — Reglas:** reunión con cliente para cerrar 3A-3F + O1/O3; documentar en `REGISTRO_NEGOCIO.md`.
3. **Sprint 3 — Desarrollo grande:** Vacaciones → Nómina/Liquidación → Traslados (ya con reglas cerradas).
4. **Transversal:** Bloque 2 (UX) y Bloque 5 (deuda/backlog) en paralelo.
