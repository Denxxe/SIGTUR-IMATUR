# Registro de Negocio — Preguntas, Decisiones y Auditoría (FUENTE ÚNICA)

**Última actualización:** 2026-06-05  
**Propósito:** **Documento único** que condensa toda la información de modelo de negocio del proyecto SIGTUR-IMATUR:
- **Parte A — Preguntas ABIERTAS** (por módulo) → lo que falta por rellenar.
- **Parte B — Decisiones TOMADAS** (por módulo) → respuestas e impacto.
- **Parte C — Auditoría técnica** (hallazgos H-xx).
- **Parte D — Backlog / mejoras de desarrollo.**

Sustituye y consolida a `preguntas_modelo_negocio.md` + `DECISIONES_PENDIENTES.md` + el resumen de `AUDITORIA_SENIOR_2026-05-31.md` (este último se conserva con el detalle de commits). El detalle de las decisiones RRHH vive en `MODELO_NEGOCIO_RRHH.md` y `REGLAS_NEGOCIO_RRHH.md`.

**Leyenda:** 🔴 bloquea BD/lógica · 🟡 alto impacto · 🟢 menor · ❓ sin respuesta · ⚠️ parcial · ✅ resuelta  
**Cómo usar:** responder en la Parte A; al resolver, mover la fila a la Parte B (decisión + impacto) y reflejar en el doc del módulo.

> **Estado global (2026-06-05):** RRHH **funcionalmente completo** (migraciones 025–034); abiertas solo **Vacaciones** y **Nómina**. Resto de módulos: pendientes de impacto medio/bajo. **~22 preguntas abiertas.**

---

# PARTE A — PREGUNTAS ABIERTAS (por módulo)

## A.1 RRHH (Talento Humano)
| ID | Prio | Estado | Pregunta | Desbloquea | Origen |
|----|------|--------|----------|------------|--------|
| D-RH04 / D-RH05 / D-NEW05 | 🔴 | ⚠️ | **Vacaciones — fórmula.** Días por años de servicio (¿LOTTT 15+1?), ¿saldo automático o manual? (Confirmado: descanso = fin de semana; acumulables sin expirar; comisión coordinada con ente origen.) | VacacionesController | MOD-RRHH 5 · AUD H-10 |
| D-RH14 | 🟡 | ❓ | **Bono vacacional:** ¿el sistema lo calcula o solo lo registra? | Campo en `vacaciones` / nómina | DECISIONES |
| D-RH34 | 🟡 | ❓ | **Nómina** para Alcaldía/Gobernación: ¿generar? ¿formato? | Módulo nómina | MOD-RRHH 8 |

## A.2 Inventario
| ID | Prio | Estado | Pregunta | Desbloquea | Origen |
|----|------|--------|----------|------------|--------|
| D-IN06 | 🔴 | ❓ | ¿"Responsable del bien" nominal? ¿Un bien asignable a >1 empleado? | FK `id_responsable` o tabla asignación | DECISIONES |
| D-IN10 | 🟡 | ❓ | ¿Baja/Mantenimiento cambia automáticamente la `condicion` del bien? | `actividad_inventario`→`inventario.condicion` | AUD H-04 |
| D-IN09 | 🟡 | ❓ | ¿Registrar costo de adquisición, fecha de compra y proveedor? | Campos en `inventario` | DECISIONES |
| D-IN03 | 🟢 | ⚠️ | Categorías aún no definidas del todo; se basan en la clasificación oficial. Confirmar lista final. | Ajuste de categorías | DECISIONES |
| D-IN05 | 🟢 | ⚠️ | Debería distinguirse fungibles vs durables; el sistema aún no lo maneja. ¿Profundizar? | `tipo_bien` + lógica | DECISIONES · REG BIN-05 |

## A.3 Formación
| ID | Prio | Estado | Pregunta | Desbloquea | Origen |
|----|------|--------|----------|------------|--------|
| D-FO05 | 🟡 | ⚠️ | No se ejecuta planificación, pero deberían existir **parámetros internos** para comparar planificado vs ejecutado (indicadores). | Parámetros de meta + comparativo | DECISIONES |
| D-FO06 | 🟡 | ❓ | ¿CRUD de **oficios base** (`oficios`) y vínculo con `talleres.id_oficio`? | OficiosController | AUD H-09/H-10 |
| D-FO07 | 🟢 | ❓ | ¿Se usa `taller_inventario` (materiales prestados)? ¿Control de devolución? | UI materiales por taller | AUD H-10 · REG BIN-01 |
| D-FO08 | 🟢 | ❓ | ¿Qué significa `es_brigadista`? Hoy no se usa. | Definir uso o eliminar | AUD H-09 |
| D-FO09 | 🟢 | ❓ | ¿Cuándo se marca asistencia (durante/después; tras "Finalizado")? | Reglas de asistencia | preguntas |
| D-NEW01 | 🟢 | ❓ | ¿Activar correlativo de oficios de formación (FORM-XXX) en UI? | Vista de talleres | preguntas |

## A.4 Turismo (Rutas)
| ID | Prio | Estado | Pregunta | Desbloquea | Origen |
|----|------|--------|----------|------------|--------|
| D-RT02 | 🟡 | ⚠️ | **Tarifa Cumaná Histórica:** se asume fija por persona; falta confirmar quién cobra y el flujo. Arquitectura preparada (`tiene_tarifa`/`tarifa_monto`). | Módulo de pagos | DECISIONES · AUD H-09 · REG BRT-01 |
| D-RT03 | 🟡 | ❓ | Al **Finalizar** una ruta, ¿generar informe/oficio automáticamente? | Cierre de ruta automatizado | derivada |
| D-RT04 / D-NEW03 | 🟢 | ❓ | **Facilitador externo:** ¿lista gestionada o texto libre? | Tabla de guías vs texto | AUD H-09 |
| REG BRT-05 | 🟢 | ❓ | ¿Reporte de ejecuciones de ruta (múltiples fechas)? | Reporte | REG |
| REG BRT-06 | 🟢 | ❓ | ¿Adultos acompañantes + prerequisito de formación en rutas? | Confirmar regla | REG |

## A.5 Recepción (Visitantes)
| ID | Prio | Estado | Pregunta | Desbloquea | Origen |
|----|------|--------|----------|------------|--------|
| REG BVIS-04 | 🟢 | ❓ | Indicador "visitas activas del día" en Dashboard. | Indicador Dashboard | REG |
| REG BVIS-05 | 🟢 | ❓ | Estadísticas de visitas en Reportes (datos ya existen). | Reporte de visitas | REG |

## A.6 Transversal / Sistema
| ID | Prio | Estado | Pregunta | Desbloquea | Origen |
|----|------|--------|----------|------------|--------|
| D-TX03 | 🟡 | ⚠️ | **Migración de históricos** (Excel/papel): definir módulos con datos + obtener archivos fuente. | Scripts de importación | DECISIONES |
| D-OF03 | 🟢 | ⚠️ | **Libro de correspondencia** unificado (oficios emitidos/recibidos): el sistema debería llevarlo. Infraestructura existe (`oficios`/`oficios_emitidos`). | Módulo de correspondencia | DECISIONES |
| D-TX01 | 🟢 | ⚠️ | ¿Otras operaciones requieren aprobación formal además de pasantes? (baja de bien, etc.) | Flujos de aprobación | DECISIONES |

---

# PARTE B — DECISIONES TOMADAS (por módulo, condensado)

> Detalle e impacto técnico completo en `DECISIONES_PENDIENTES.md` (archivo histórico) y, para RRHH, en `MODELO_NEGOCIO_RRHH.md` §10–11.

## B.1 RRHH — resumen
RRHH se relevó a fondo (2026-06-02→05) y se implementó completo (migraciones 025–034). **Todas las preguntas D-RH01…D-RH36 están resueltas** salvo Vacaciones (A.1) y Nómina (A.1). Decisiones clave:
- Horarios: Estándar 8–2pm; Servicios Generales grupos A/B (rotación diaria); OAC sub-grupos 7–12 / 10–2 (D-RH01/08).
- Contrato: todo nuevo Contratado; Fijo solo desde origen; **comisión de servicio = origen Alcaldía/Gobernación (derivado, no manual); IMATUR = no comisión**. Edad: comisión 18–70, IMATUR 18–65 (D-RH19/27/31).
- Permisos: TH oficializa, especiales→Dirección General; reposo/permiso separados por categoría (D-RH02/03/24/32).
- Asistencia: puntualidad con tolerancia configurable; horas solo para reporte; "En Ruta" no es falta (D-RH09/17/33).
- Amonestaciones: sistema cuenta, RRHH registra; 3 = despido (D-RH28). Expediente híbrido + ficha técnica (D-RH22). Constancias con correlativo (RN-RH14).
- **D-RH07** ✅ contratos con vencimiento variable → alerta en Dashboard (implementada).
- **D-RH10** ✅ no hay personal no-formal salvo pasantes (módulo propio).
- **D-RH11** ✅ IMATUR no distingue sueldo base por cargo → se eliminó `cargos.sueldo_base`; los cargos se clasifican por `nivel_jerarquico` (Presidencia/Dirección/Coordinación/Adscrito) según el organigrama (migración 035).
- **R-12 Egreso/desincorporación** ✅ (migración 036) Dar de baja (renuncia/despido/jubilación/fin de contrato/fallecimiento/otro) **no borra**: marca `fecha_egreso`+`motivo_egreso`, sale de nómina activa pero queda como **histórico consultable** (pestaña "Egresados") con acceso a su expediente, **tiempo de servicio** calculado y **constancias** (redacción en pasado). `is_active=FALSE` queda solo para papelera (registro creado por error). **Reingreso con historial** (`empleados_egresos`); índice único impide doble egreso abierto.
- Detalle: `MODELO_NEGOCIO_RRHH.md` §10–11 y `REGLAS_NEGOCIO_RRHH.md`.

## B.2 Inventario
| ID | Decisión |
|----|----------|
| D-IN01 | Baja: **solo registro interno**, sin acto imprimible. |
| D-IN02 | Reporte de bajas (CSV) implementado en ReportesController. |
| D-IN04 | +`'En Reparación'` en CHECK de `inventario.condicion` (mig.007). |
| D-IN07 | Reporte de **conteo físico** (CSV) implementado. |
| D-IN08 | `codigo_bn` **nullable** (mig.007); muestra "—" si NULL. |

## B.3 Formación
| ID | Decisión |
|----|----------|
| D-FO01 | Informe demográfico obligatorio al Finalizar (RN-F13). |
| D-FO02 | Oficio→taller **1:1**. |
| D-FO03 | Talleres externos **no** generan oficio emitido (lo solicita la institución vía Zona Educativa). |
| D-FO04 | Lista de asistencia imprimible (`talleres/lista_asistencia.php`). |
| D-FO06* | Facilitadores = solo empleados (no pasantes). *(distinto del D-FO06 de oficios en Parte A)* |
| D-FO07* | Informe de actividad imprimible (`informe_imprimible.php`). |
| D-FO08* | Múltiples facilitadores → backlog v3.0. |

## B.4 Pasantes — todas resueltas
D-PS01 (aprobar Postulado→Aceptado solo rol 1) · D-PS02 (nota numérica + cualitativa) · D-PS03 (docs: carta, cédula, planilla) · D-PS04 (días calculados; carta culminación) · D-PS05 (tutor = cualquier empleado activo) · D-PS06 (asistencia en papel/visitantes) · D-PS07 (sin límite de cupo). + Carta de aceptación con correlativo PAST-NNN/AAAA (D-TX01, mig.024).

## B.5 Turismo (Rutas)
| ID | Decisión |
|----|----------|
| D-RT01 | Un registro de `rutas` por ejecución (no tabla de ejecuciones). |
| D-RT03 | Correlativo de oficios **por módulo** (RUTA-/FORM-…). |
| D-RT04 | `instituciones_externas` con `es_educativa` (mig.007). **NOTA: módulo retirado del flujo (2026-05-31)** — columna `participantes_ruta.id_institucion` queda inerte. |
| D-RT05 | Facilitador externo por `nombre_facilitador_externo`. |
| D-RT06 | Mapa Leaflet offline **implementado** (assets locales + tiles OSM). |

## B.6 Visitantes
D-VIS01 (motivo = lista predefinida) · D-VIS02 ("Institución/Procedencia") · D-VIS03 (solo venezolanos, sin tipo_documento) · D-VIS04 (reporte por período/institución/motivo/empleado). Visitas = bitácora inmutable (D-RE03).

## B.7 Oficios / Documentos
| ID | Decisión |
|----|----------|
| D-OF01 | Firmante configurable (`firmante_cargo` / director_* en ConfigSistema). |
| D-OF02 | Una sola firma por documento. |

## B.8 Reportes / Indicadores
D-RE01/RE02 (informe trimestral consolidado → backlog) · D-RE03 (5 KPIs del Dashboard) · D-RE04 (turistas por procedencia).

## B.9 Usuarios / Seguridad
| ID | Decisión |
|----|----------|
| D-US01 | Director usa rol 1 (Administrador). |
| D-US02 | Rol 5 "Recepción" (Dashboard/Visitantes/Visitas/Asistencias). |
| D-US03 | Rol 6 "Solo Lectura" (Dashboard/Reportes/Visitantes) implementado. |
| D-US05 | Contraseña por defecto = cédula; módulo "Mi Perfil"; mín. 6 chars. |
| D-US06 | Política: mín. 6 chars, sin caducidad/complejidad. |

## B.10 Configuración / Transversal
D-CF01 (RIF G-20008498-7) · D-CF02 (dirección) · D-CF03 (un teléfono) · D-CF04 (Admin actualiza resolución/gaceta) · D-TX02 (alertas en Dashboard) · D-TX04 (~34 empleados / ~60 bienes → paginación 20 ok) · D-TX04-quirk (no renombrar columnas `parroquia`).

---

# PARTE C — AUDITORÍA TÉCNICA (hallazgos)

Detalle y commits en `AUDITORIA_SENIOR_2026-05-31.md`.

| # | Hallazgo | Estado | Cierra con |
|---|----------|--------|-----------|
| H-01 | Ubicaciones sin departamento | ✅ Resuelto | — |
| H-02 | Parroquia audita en español | ✅ Resuelto | — |
| H-03 | `visitas` sin auditoría completa | ✅ Cerrado (decisión: inmutable) | — |
| H-04 | Baja de bien no actualiza `condicion` | ⚠️ Abierto | **D-IN10** |
| H-05 | Validaciones de servidor faltantes | ✅ Resuelto | — |
| H-06 | Correlativo de oficios sin transacción | ✅ Resuelto | — |
| H-07 | Enums duplicados | ✅ Resuelto | — |
| H-08 | FKs NOT VALID (7) | ✅ Resuelto | — |
| H-09 | Columnas inertes | ⚠️ Abierto | **D-RT02, D-RT04, D-FO06, D-FO08** + `id_institucion` (módulo retirado) |
| H-10 | Tablas sin UI | ⚠️ Parcial | `horarios`/`permisos_laborales` ✅ (R-6/R-8); faltan **`vacaciones`** (D-RH04/05), **`taller_inventario`** (D-FO07), **`oficios`** (D-FO06) |
| H-11 | `genero` permitía 'O' | ✅ Resuelto | — |

---

# PARTE D — BACKLOG / MEJORAS DE DESARROLLO

No requieren decisión de negocio; implementables cuando se prioricen.

| Módulo | Mejora | Origen |
|--------|--------|--------|
| RRHH | ✅ **Hecho:** Reporte de **permisos/reposos** (`reportes/permisos` + CSV) por categoría/estado/período | BRH-06 |
| RRHH | ✅ **Hecho:** Reporte de **asistencia** con puntualidad + horas; **indicadores** RRHH (clasificación, permisos vigentes, amonestaciones, impuntualidad del mes) | R-7/R-8/R-9 |
| RRHH | Reporte de **saldo de vacaciones** (depende de D-RH04/05) | BRH-07 |
| RRHH | Alerta "empleados en permiso/reposo hoy" en Dashboard | CLAUDE.md |
| RRHH | Réplica imprimible del **formato físico de asistencia** | MOD-RRHH 6.2 |
| RRHH | Unificar **RIF institucional** (ficha/constancia G-20008498-7 vs carta_aceptacion G-20009499-7) vía ConfigSistema | CLAUDE.md |
| Formación | Tabla `taller_facilitadores` (múltiples facilitadores) | REG · D-FO08 |
| Formación | Informe **trimestral consolidado** (metas/logros) | D-RE01/RE02 |
| Turismo | Reporte de ejecuciones de ruta (múltiples fechas) | REG BRT-05 |
| Recepción | "Visitas activas del día" + estadísticas en Reportes | REG BVIS-04/05 |
| Transversal | Libro de correspondencia unificado | D-OF03 |
| Transversal | Importación de datos históricos desde Excel | D-TX03 |

---

## Obsoletas / sin efecto
- **D-NEW02** (CRUD `instituciones_externas`): módulo **retirado** (2026-05-31).
- **REG BRT-03** (mapa de ruta): ya implementado (Leaflet local).

## Referencias
- `MODELO_NEGOCIO_RRHH.md` — modelo + Q&A RRHH (D-RH01–D-RH36) + hoja de ruta R-1…R-11.
- `REGLAS_NEGOCIO_*.md` — reglas por módulo (con backlog B-xx).
- `AUDITORIA_SENIOR_2026-05-31.md` — auditoría con commits.
- `DECISIONES_PENDIENTES.md` — Q&A histórico detallado (archivo).
