# Auditoría de Ingeniería Senior — SIGTUR-IMATUR

**Fecha auditoría:** 2026-05-31
**Última actualización:** 2026-05-31 (todos los hallazgos cerrados)
**Alcance:** todos los módulos (RRHH, Recepción, Formación, Turismo, Inventario, Sistema/Transversal).
**Método:** análisis cruzado BD viva (37 tablas, migraciones 001-021) ↔ modelos/controladores/vistas. Cada hallazgo fue verificado contra la base de datos real. Los falsos positivos detectados se documentan al final.

> **Estado final:** todos los hallazgos H-01 a H-11 están **cerrados**. Los pendientes abiertos son decisiones de negocio, documentados en `preguntas_modelo_negocio.md` (H-09/H-10/H-03).

---

## 🔴 ALTA

### H-01 · Ubicaciones sin departamento — ✅ RESUELTO
- **Problema:** `Ubicacion::save()` no escribía `"departamento _d"` (NOT NULL sin default) → toda alta nueva fallaba.
- **Solución:** `Ubicacion` mapea la columna como `id_departamento`, `all()`/`find()` la exponen con JOIN al nombre del departamento, el controlador valida que sea obligatoria, la vista tiene el select. Verificado con INSERT real.
- **Decisión D-UB01:** sí, la ubicación pertenece a un departamento (obligatorio).
- **Commits:** `9b960fe`

### H-02 · Auditoría de Parroquia en español — ✅ RESUELTO
- **Problema:** `Parroquia::save()/delete()` registraban `ACTUALIZAR/INSERTAR/ELIMINAR` → la Bitácora no los filtraba ni el re-fetch de UPDATE los capturaba.
- **Solución:** normalizado a `UPDATE/INSERT/DELETE`.
- **Commits:** `9b960fe`

---

## 🟡 MEDIA

### H-03 · `visitas` sin columnas de auditoría completas — ✅ CERRADO (decisión de negocio)
- **Decisión (2026-05-31):** las visitas son **registros inmutables** (no se editan ni eliminan). No se requieren `updated_at/deleted_at`. El reporte de visitantes se mejoró para mostrar datos completos del visitante (cédula, teléfono, correo, género) y se eliminó "Empleado visitado" (no se captura al registrar).
- **Commits:** `363b709`, `c655fde`

### H-04 · Baja de bien no actualiza su condición — ⚠️ PENDIENTE DECISIÓN
- **Hecho:** registrar un movimiento tipo `Baja`/`Mantenimiento` no actualiza `inventario.condicion`.
- **Pendiente:** ver pregunta `D-IN10` — ¿debe sincronizarse automáticamente?

### H-05 · Validaciones de servidor faltantes — ✅ RESUELTO
- Email: `filter_var(FILTER_VALIDATE_EMAIL)` en `EmpleadosController` y `VisitantesController`.
- `fecha_fin >= fecha_inicio` en `PasantesController` (crear y editar).
- Unicidad de `codigo_bn` y `serial`: `Inventario::findByCodigoBn()`/`findBySerial()` pre-validan con mensajes precisos.
- **Commits:** `4832d82`

### H-06 · Correlativo de oficios sin protección de concurrencia — ✅ RESUELTO
- `ConfigSistema::generarNumeroOficio()` reescrito con transacción + `SELECT FOR UPDATE` + `UPDATE ... RETURNING` atómico.
- **Commits:** `8311a81`

### H-07 · Enums duplicados en varias capas — ✅ RESUELTO
- Constantes centralizadas en los modelos:
  - `Taller::ESTADOS`, `ESTADOS_TERMINALES`, `TIPOS_ACTIVIDAD`, `ESTADO_BADGES`, `TRANSICIONES`
  - `Ruta::ESTADOS`, `ESTADO_TERMINAL`, `ESTADO_BADGES`
  - `Inventario::CONDICIONES`, `CONDICION_DEFAULT`, `CONDICION_BADGES`
- Controllers usan las constantes para whitelists. Vistas PHP usan `Model::ESTADO_BADGES`. JS recibe los mapas vía `json_encode()`.
- **Commits:** `d83c493`

### H-08 · Claves foráneas NOT VALID — ✅ RESUELTO
- 7 FKs validadas con migración 022 (0 huérfanos en todas). BD ahora enforcea integridad referencial completa.
  - `municipio.created_by`, `parroquia.create_by/update_by/delete_by`, `personas.parroquia_id`, `ubicaciones."departamento _d"`, `ubicaciones_formacion.parroquia`
- **Commits:** `e6fabb0` (migración 022)

---

## 🟢 BAJA

### H-09 · Columnas inertes — ⚠️ PENDIENTE DECISIÓN
| Columna | Pregunta abierta |
|---------|-----------------|
| `rutas.tiene_tarifa`, `rutas.tarifa_monto` | `D-RT02` — ¿cobro integrado? |
| `rutas.nombre_facilitador_externo` | `D-RT04` — ¿texto libre o lista? |
| `talleres.id_oficio` | `D-FO06` — ¿CRUD de oficios base? |
| `participantes_taller.es_brigadista` | `D-FO08` — ¿qué significa? |
| `participantes_ruta.id_institucion` | módulo instituciones retirado; columna inerte |

### H-10 · Tablas sin UI — ⚠️ PENDIENTE DECISIÓN
`horarios`, `permisos_laborales`, `vacaciones` (mig. 002, sin CRUD — ver D-RH01..05), `taller_inventario` (`D-FO07`), `oficios` base (`D-FO06`), `actividades_ruta` e `instituciones_externas` (retiradas del flujo).

### H-11 · `genero` permitía 'O' — ✅ RESUELTO
- 0 registros con 'O' en BD. Migración 023 actualiza CHECK a `IN ('M','F')` en las 4 tablas (`personas`, `visitantes`, `participantes_taller`, `participantes_ruta`). Opción "Otro" eliminada de todos los formularios (talleres/detalle, rutas/detalle, visitantes/index, talleres/informe_imprimible).
- **Commits:** `1605e4a` (migración 023)

---

## ✅ Falsos positivos descartados

1. **"Router bloquea a `AuditoriaController`"** — FALSO. La lógica del Router es correcta; el bloque especial solo *agrega* acceso para `AuditoriaPapelera`, no lo bloquea.
2. **"`Visita.php` usa `deleted_at` inexistente"** — FALSO. Solo ejecuta `is_active=FALSE`; no referencia `deleted_at`.

---

## Resumen ejecutivo de cierre

| # | Hallazgo | Estado | Commit |
|---|----------|--------|--------|
| H-01 | Alta de ubicaciones rota | ✅ Resuelto | `9b960fe` |
| H-02 | Parroquia audita en español | ✅ Resuelto | `9b960fe` |
| H-03 | visitas sin auditoría completa | ✅ Cerrado (decisión: inmutable) | `363b709` |
| H-04 | Baja bien → condición | ⚠️ Pendiente `D-IN10` | — |
| H-05 | Validaciones servidor faltantes | ✅ Resuelto | `4832d82` |
| H-06 | Correlativo oficios sin transacción | ✅ Resuelto | `8311a81` |
| H-07 | Enums duplicados | ✅ Resuelto | `d83c493` |
| H-08 | FKs NOT VALID (7) | ✅ Resuelto | `e6fabb0` |
| H-09 | Columnas inertes | ⚠️ Pendiente decisión negocio | — |
| H-10 | Tablas sin UI | ⚠️ Pendiente decisión negocio | — |
| H-11 | Género permitía 'O' | ✅ Resuelto | `1605e4a` |

**Migraciones aplicadas:** 022 (VALIDATE FK), 023 (género M/F)
**Esquema actualizado:** `database/schema_consolidado.sql` pendiente de regenerar con mig. 022-023.

---

## Artefactos relacionados
- `database/schema_consolidado.sql` — esquema base + 001-021 + seeds (pendiente actualizar a 023)
- `docs/preguntas_modelo_negocio.md` — preguntas de negocio abiertas
- `docs/INDICADORES_GESTION.md`, `docs/ANALISIS_MODULOS_FORMACION_TURISMO.md` — análisis de módulos
