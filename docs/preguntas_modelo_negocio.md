# Preguntas Pendientes — Fuente Única (por módulo) — SIGTUR-IMATUR

**Última actualización:** 2026-06-05  
**Propósito:** **Fuente única** de preguntas de modelo de negocio / decisiones **sin resolver**, consolidando: `preguntas_modelo_negocio` (histórico), `AUDITORIA_SENIOR_2026-05-31.md` (hallazgos H-xx abiertos) y los backlogs de las `REGLAS_NEGOCIO_*.md` (B-xx). Organizada **por módulo**.

**Cómo usar:** responder cada fila (en su columna o en `DECISIONES_PENDIENTES.md` con el detalle e impacto técnico). Al resolver, marcar ✅ aquí y registrar la decisión. Las preguntas **ya respondidas** viven en `DECISIONES_PENDIENTES.md` y en los docs de cada módulo.

**Prioridad:** 🔴 bloquea BD/lógica central · 🟡 alto impacto · 🟢 mejora/detalle  
**Estado:** ❓ sin respuesta · ⚠️ respuesta parcial (falta confirmar para implementar)

**Origen:** `MOD-RRHH` = `MODELO_NEGOCIO_RRHH.md` · `AUD H-xx` = auditoría senior · `REG B-xx` = backlog de reglas de negocio del módulo.

> **Estado global (2026-06-05):** el módulo **RRHH está funcionalmente completo** (migraciones 025–034); solo quedan abiertas **Vacaciones** y **Nómina** (decisiones de negocio). El resto de módulos tiene pendientes de impacto medio/bajo. Total abierto: ~24 preguntas.

---

## 1. RRHH (Talento Humano)

| ID | Prio | Estado | Pregunta | Desbloquea | Origen |
|----|------|--------|----------|------------|--------|
| D-RH04 / D-RH05 / D-NEW05 | 🔴 | ⚠️ | **Vacaciones — fórmula de cálculo.** ¿Cuántos días por años de servicio? ¿Aplica LOTTT (15 + 1/año)? ¿El cálculo del saldo lo hace el sistema o RRHH lo carga manual? (Confirmado: descanso = fin de semana; acumulables sin expirar; comisión de servicio coordinada con ente origen.) | `VacacionesController` (R-8 vacaciones) | MOD-RRHH 5 · AUD H-10 |
| D-RH14 | 🟡 | ❓ | **Bono vacacional:** ¿el sistema lo calcula o solo lo registra? | Campo en `vacaciones` / cálculo | MOD-RRHH |
| D-RH34 | 🟡 | ❓ | **Nómina** para enviar a Alcaldía/Gobernación: ¿el sistema debe generarla? ¿Formato/estructura? | Módulo nómina (R-11) | MOD-RRHH 8 |
| D-RH10 | 🟢 | ❓ | ¿Hay personal sin ser empleado formal (voluntarios, servicio comunitario)? | Tipo de vínculo adicional o tabla separada | preguntas |
| D-RH11 | 🟢 | ❓ | ¿El `sueldo_base` es igual para todos en el mismo cargo o varía por empleado? | Mover `sueldo_base` de `cargos` a `empleados` | preguntas |

---

## 2. Inventario

| ID | Prio | Estado | Pregunta | Desbloquea | Origen |
|----|------|--------|----------|------------|--------|
| D-IN06 | 🔴 | ❓ | ¿Existe "responsable del bien" nominal? ¿Un bien puede asignarse a >1 empleado? | FK `id_responsable` o tabla de asignación múltiple | preguntas |
| D-IN10 | 🟡 | ❓ | ¿Registrar un movimiento **Baja**/**Mantenimiento** debe cambiar automáticamente la `condicion` del bien (p. ej. a "Dañado")? Hoy no lo hace. | Sincronizar `actividad_inventario` → `inventario.condicion` | AUD H-04 |
| D-IN09 | 🟡 | ❓ | ¿Registrar costo de adquisición, fecha de compra y proveedor? | Campos adicionales en `inventario` | preguntas |
| D-IN03 | 🟢 | ❓ | ¿Las categorías actuales reflejan la clasificación oficial de IMATUR? | Ajuste de categorías | preguntas |
| D-IN05 | 🟢 | ❓ | ¿Bienes fungibles (papel, tóner) y durables (equipos) se manejan distinto? | Campo `tipo_bien` + lógica | preguntas · REG BIN-05 |

---

## 3. Formación (Talleres / Charlas / Inducciones)

| ID | Prio | Estado | Pregunta | Desbloquea | Origen |
|----|------|--------|----------|------------|--------|
| D-FO05 | 🟡 | ❓ | ¿La planificación semanal de Formación debe registrarse en el sistema? | Módulo de planificación + comparativo vs ejecutado | preguntas |
| D-FO06 | 🟡 | ❓ | ¿Se gestionan los **oficios base** (tabla `oficios`) con CRUD y se vinculan al taller (`talleres.id_oficio`)? ¿Para qué (solicitud de sede, autorización)? | OficiosController + flujo oficio→taller | AUD H-09/H-10 |
| D-FO07 | 🟢 | ❓ | ¿La tabla `taller_inventario` (materiales prestados) se va a usar? ¿Obligatorio? ¿Control de devolución? | UI de materiales por taller | AUD H-10 · REG BIN-01 |
| D-FO08 | 🟢 | ❓ | ¿Qué significa `es_brigadista` en un participante? ¿Implica rol/beneficio? Hoy no se usa. | Definir uso o eliminar campo | AUD H-09 |
| D-FO09 | 🟢 | ❓ | ¿Cuándo se marca la **asistencia** (durante o después del taller)? ¿Se permite tras "Finalizado"? | Reglas de asistencia + máquina de estados | preguntas |
| D-NEW01 | 🟢 | ❓ | ¿El correlativo de oficios de formación se activa en UI o solo queda la infraestructura? | Activar correlativo FORM-XXX en vista de talleres | preguntas |

---

## 4. Turismo (Rutas)

| ID | Prio | Estado | Pregunta | Desbloquea | Origen |
|----|------|--------|----------|------------|--------|
| D-RT02 | 🟡 | ⚠️ | **Tarifa Cumaná Histórica:** ¿fija por persona, por grupo, por tipo? ¿Quién cobra? ¿Cobro integrado al sistema? (Arquitectura `tiene_tarifa`/`tarifa_monto` preparada.) | Módulo de pagos `participantes_ruta` | preguntas · AUD H-09 · REG BRT-01 |
| D-RT03 | 🟡 | ❓ | Al pasar una ruta a **Finalizada**, ¿debe generarse informe y/o oficio **automáticamente**? Hoy es manual. | Cierre de ruta automatizado | AUD-derivada |
| D-RT04 / D-NEW03 | 🟢 | ❓ | **Facilitador externo** (`nombre_facilitador_externo`): ¿lista gestionada de guías o texto libre cada vez? | Tabla de guías externos vs texto | AUD H-09 · preguntas |
| REG BRT-05 | 🟢 | ❓ | ¿Reporte de ejecuciones de ruta (múltiples fechas de una misma ruta)? | Reporte de ejecuciones | REG BRT-05 |
| REG BRT-06 | 🟢 | ❓ | ¿Adultos acompañantes y su prerequisito de formación en rutas? | Confirmar regla (bajo impacto) | REG BRT-06 |

---

## 5. Recepción (Visitantes)

| ID | Prio | Estado | Pregunta | Desbloquea | Origen |
|----|------|--------|----------|------------|--------|
| REG BVIS-04 | 🟢 | ❓ | Indicador **"visitas activas del día"** en el Dashboard. | Tarjeta/indicador en Dashboard | REG BVIS-04 |
| REG BVIS-05 | 🟢 | ❓ | **Estadísticas de visitas** en el módulo de Reportes (los datos ya existen). | Reporte de visitas | REG BVIS-05 |

---

## 6. Transversal

| ID | Prio | Estado | Pregunta | Desbloquea | Origen |
|----|------|--------|----------|------------|--------|
| D-TX03 | 🟡 | ⚠️ | **Migración de datos históricos** (Excel/papel). Confirmado que existen; falta definir **qué módulos** los tienen y obtener los **archivos fuente** para diseñar los scripts de importación CSV por módulo. | Scripts de importación por módulo | preguntas |

---

## 7. Deuda técnica abierta (Auditoría Senior) — trazabilidad

Los hallazgos abiertos de `AUDITORIA_SENIOR_2026-05-31.md` se cierran resolviendo las preguntas de arriba:

| Hallazgo | Descripción | Pregunta(s) que lo cierran |
|----------|-------------|----------------------------|
| H-04 | Baja de bien no actualiza su `condicion` | D-IN10 |
| H-09 | Columnas inertes (`rutas.tiene_tarifa`/`tarifa_monto`/`nombre_facilitador_externo`, `talleres.id_oficio`, `participantes_taller.es_brigadista`, `participantes_ruta.id_institucion`) | D-RT02, D-RT04, D-FO06, D-FO08 (y `id_institucion`: columna inerte de módulo retirado — decidir si se elimina) |
| H-10 | Tablas sin UI | **`horarios`/`permisos_laborales` ✅ ya tienen UI (R-6/R-8)**; pendientes: `vacaciones` (D-RH04/05), `taller_inventario` (D-FO07), `oficios` base (D-FO06). `actividades_ruta`/`instituciones_externas` retiradas del flujo. |

---

## 8. Obsoletas / sin efecto

| ID | Motivo |
|----|--------|
| D-NEW02 | CRUD de `instituciones_externas`: **módulo retirado** del sistema (2026-05-31). Sin efecto salvo que se reincorpore. |
| REG BRT-03 | Mapa visual de puntos de ruta: **ya implementado** (Leaflet local + tiles OSM cacheados). |
| D-RH07 | Alerta de contratos por vencer en Dashboard: **ya implementado** (BRH-05). |

---

## 9. Mejoras de desarrollo (backlog técnico — no bloquean, no requieren decisión de negocio)

Recopiladas de los backlogs de reglas y de `CLAUDE.md`. Son implementables cuando se priorice.

| Módulo | Mejora | Origen |
|--------|--------|--------|
| RRHH | Reporte de **permisos/reposos** por tipo/empleado/período (ya hay datos tras R-8) | CLAUDE.md · BRH-06 |
| RRHH | Reporte de **saldo de vacaciones** por empleado (depende de la fórmula D-RH04/05) | CLAUDE.md · BRH-07 |
| RRHH | Alerta "empleados en permiso/reposo hoy" en Dashboard | CLAUDE.md |
| RRHH | Réplica imprimible del **formato físico de asistencia** (hoja semanal) | MOD-RRHH 6.2 |
| RRHH | Unificar **RIF institucional** (ficha/constancia G-20008498-7 vs carta_aceptacion G-20009499-7) vía ConfigSistema | CLAUDE.md 18b/18j |
| Formación | Tabla `taller_facilitadores` (múltiples facilitadores por actividad) | REG Formación |
| Formación | Informe **agregado trimestral** de Formación (metas/logros/actividades) | REG Formación · CLAUDE.md |
| Turismo | Reporte de ejecuciones de ruta (múltiples fechas) | REG BRT-05 |
| Recepción | Indicador "visitas activas del día" + estadísticas de visitas en Reportes | REG BVIS-04/05 |

---

## Notas de uso

- Este documento es la **fuente única** de preguntas SIN responder, **organizada por módulo**.
- Al responder una pregunta: registrar la decisión e impacto técnico en `DECISIONES_PENDIENTES.md` y reflejarla en el doc del módulo (`MODELO_NEGOCIO_RRHH.md`, `REGLAS_NEGOCIO_*.md`); luego marcarla ✅/eliminarla de aquí.
- El histórico de preguntas **ya resueltas** del módulo RRHH (D-RH01–D-RH36) está en `MODELO_NEGOCIO_RRHH.md` (secciones 10–11) y en `REGLAS_NEGOCIO_RRHH.md`.
- Referencias: `AUDITORIA_SENIOR_2026-05-31.md` (deuda técnica) · `INDICADORES_GESTION.md` · `ANALISIS_MODULOS_FORMACION_TURISMO.md`.
