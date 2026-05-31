# Auditoría de Ingeniería Senior — SIGTUR-IMATUR

**Fecha:** 2026-05-31
**Alcance:** todos los módulos (RRHH, Recepción, Formación, Turismo, Inventario, Sistema/Transversal).
**Método:** análisis cruzado BD viva (37 tablas, migraciones 001-021 aplicadas) ↔ modelos/controladores/vistas. **Cada hallazgo fue verificado contra la base de datos real**, no contra `schema.sql` (base, desactualizado). Los falsos positivos detectados se documentan al final con su refutación.

> Este documento identifica **desfases, lógica incompleta y deuda técnica**. No es un changelog: es el mapa de lo que falta para dejar el sistema "lo más completo posible". Las preguntas de negocio que se derivan están en `preguntas_modelo_negocio.md` (sección 2026-05-31).

---

## 🔴 ALTA — Defectos de correctitud (afectan operación o datos)

### H-01 · No se pueden crear nuevas Ubicaciones de inventario
- **Dónde:** `app/models/Ubicacion.php:39` (INSERT).
- **Hecho verificado:** la tabla `ubicaciones` tiene la columna **`"departamento _d"` `NOT NULL` sin valor por defecto**, pero el `INSERT` del modelo solo escribe `(nombre, descripcion, created_by)`. Toda alta nueva de ubicación **falla** con violación de `NOT NULL`.
- **Impacto:** el catálogo de ubicaciones de inventario es de facto inmutable (solo existen las sembradas). El módulo Inventario depende de él.
- **Causa raíz / decisión de negocio:** ¿las ubicaciones deben pertenecer a un departamento? La columna lo exige pero no hay UI para elegirlo (ver `D-UB01`). 
- **Opciones de arreglo:** (a) agregar selección de departamento en UI+controlador+modelo; (b) si ya no aplica, volver la columna `NULL`/con default y renombrarla a `id_departamento` (limpia también el nombre con espacio).

### H-02 · La auditoría de Parroquia se registra en español
- **Dónde:** `app/models/Parroquia.php:103` (`'ACTUALIZAR'`), `:108` (`'INSERTAR'`), `:128` (`'ELIMINAR'`).
- **Hecho verificado:** todas las demás tablas registran `INSERT/UPDATE/DELETE` (inglés); `audit_logs` no contiene hoy ninguna operación en español (nunca se ha editado una parroquia con este código).
- **Impacto:** (1) el **filtro por acción** de la nueva Bitácora no reconoce `ACTUALIZAR/...` → se muestran con badge neutro y no filtran; (2) la mejora de **registrar el estado completo en UPDATE** (`Model::audit`, 2026-05-31) solo se dispara con `'UPDATE'`, así que parroquia seguiría con log parcial.
- **Arreglo:** trivial — normalizar a `'UPDATE'/'INSERT'/'DELETE'`.

---

## 🟡 MEDIA — Lógica incompleta o inconsistencias

### H-03 · `visitas` sin columnas de auditoría completas
- **Verificado:** `visitas` tiene `is_active, created_at, created_by` pero **carece de** `updated_at/updated_by/deleted_at/deleted_by`. `Visita::delete()` solo hace `is_active=FALSE` (no rompe), pero no queda registro de *cuándo* ni *quién* eliminó, y no encaja con la papelera/convención del resto.
- **Arreglo:** migración para agregar las 4 columnas + ajustar `Visita::delete()`.

### H-04 · Dar de baja un bien por "Movimientos" no cambia su condición
- **Dónde:** `app/controllers/ActividadesinventarioController.php` (lógica comentada nunca implementada).
- **Hecho:** registrar un `actividad_inventario` tipo `Baja`/`Mantenimiento` **no actualiza** `inventario.condicion`. Un bien dado de baja puede seguir figurando "Bueno".
- **Pregunta de negocio asociada:** `D-IN10`.

### H-05 · Validaciones de servidor faltantes
- **Email** sin `filter_var(...FILTER_VALIDATE_EMAIL)` en `EmpleadosController` y `VisitantesController` (solo HTML5).
- **`fecha_fin >= fecha_inicio`** no validada en `PasantesController`.
- **Unicidad de `codigo_bn` y `serial`** (ambos `UNIQUE` en BD) no se pre-validan en `InventarioController` → el usuario recibe un error opaco de BD en vez de un mensaje claro.

### H-06 · Correlativo de oficios sin protección de concurrencia
- **Dónde:** `app/models/ConfigSistema.php::generarNumeroOficio()`.
- **Hecho:** lee-incrementa-escribe sin transacción/`SELECT ... FOR UPDATE`. Dos emisiones simultáneas podrían obtener el mismo número. Riesgo bajo en uso real (1 recepción), pero es una condición de carrera latente.

### H-07 · Enums duplicados en varias capas (sin fuente única)
- Estados de taller (`Programado/En Curso/Finalizado/Cancelado`), estados/tipos de ruta y `condicion` de inventario están escritos a mano en **modelo + controlador + vista**. `Ruta::$TIPOS_RUTA` ya centraliza tipos de ruta; falta el resto.
- **Riesgo:** al cambiar un valor en BD hay que tocar 3 lugares → desincronización.
- **Recomendación:** constantes en el modelo (`Taller::ESTADOS`, `Inventario::CONDICIONES`, …) y reutilizarlas.

### H-08 · Claves foráneas `NOT VALID`
- `ubicaciones."departamento _d"` y `ubicaciones_formacion.parroquia` quedaron como FK `NOT VALID` (no se validaron filas existentes). Integridad no garantizada para datos previos.
- **Arreglo:** corregir datos huérfanos y `ALTER TABLE ... VALIDATE CONSTRAINT`.

---

## 🟢 BAJA — Inertes, huérfanos y detalles

### H-09 · Columnas inertes (existen, sin lógica que las gestione)
| Columna | Estado |
|--------|--------|
| `rutas.tiene_tarifa`, `rutas.tarifa_monto` | sin UI ni cobro (ver `D-RT02`) |
| `rutas.nombre_facilitador_externo` | sin UI para capturarla (ver `D-RT04`) |
| `talleres.id_oficio` | nunca se asigna (oficios base sin CRUD, ver `D-FO06`) |
| `participantes_taller.es_brigadista` | nunca se usa (ver `D-FO08`) |
| `participantes_ruta.id_institucion` | siempre NULL (módulo instituciones retirado) |

### H-10 · Tablas inertes o sin UI
`horarios`, `permisos_laborales`, `vacaciones` (mig. 002, 0 registros, sin CRUD), `taller_inventario` (sin UI, ver `D-FO07`), `oficios` (base, sin CRUD, ver `D-FO06`), `actividades_ruta` e `instituciones_externas` (retiradas del flujo, conservadas inertes).

### H-11 · `genero` permite 'O' pero la UI solo ofrece M/F
- **Verificado:** `personas.genero CHECK IN ('M','F','O')`; el formulario de empleados solo lista M/F. Un registro con 'O' no se puede preservar al editar.

---

## ✅ Falsos positivos detectados y DESCARTADOS (rigor de verificación)

> Documentados para evitar que se "arreglen" cosas que ya están bien.

1. **"El Router bloquea a un rol con `AuditoriaController`"** — **FALSO.** En `Router.php`, la verificación estándar `in_array($controller, $permitidos)` ya admite a quien tenga `AuditoriaController`; el bloque especial solo *agrega* acceso para quien tenga `AuditoriaPapelera`. La lógica es correcta.
2. **"`Visita.php:73` usa `deleted_at` inexistente"** — **FALSO.** `Visita::delete()` solo ejecuta `UPDATE visitas SET is_active=FALSE`. No referencia `deleted_at` (sí es cierto, aparte, que la tabla carece de esa columna → ver H-03, pero no hay crash).

---

## Prioridad sugerida de ejecución

1. **H-02** (parroquia → inglés): trivial, desbloquea bitácora correcta. 
2. **H-01 + D-UB01** (alta de ubicaciones): definir si ubicación lleva departamento y arreglar el INSERT.
3. **H-05** (validaciones de servidor): bajo esfuerzo, alto valor de integridad.
4. **H-04** (baja de bien → condición), **H-03** (auditoría visitas), **H-06** (correlativo), **H-07** (enums).
5. Resto (inertes/huérfanos): decidir por negocio qué se implementa y qué se elimina (ver preguntas).

---

## Artefactos relacionados
- `database/schema_consolidado.sql` — **fuente única de verdad** del esquema (reemplaza `schema.sql` + migraciones 001-021). Verificado: recrea 37 tablas + seeds de sistema sin errores.
- `docs/preguntas_modelo_negocio.md` — preguntas de negocio abiertas (sección 2026-05-31 con las nuevas).
- `docs/INDICADORES_GESTION.md`, `docs/ANALISIS_MODULOS_FORMACION_TURISMO.md` — análisis previos.
