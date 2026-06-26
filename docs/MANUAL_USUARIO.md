# Manual de Usuario — SIGTUR-IMATUR

**Sistema Integral de Gestión Turística y Administrativa — IMATUR (Cumaná, Sucre)**
Aplicación web de uso interno. Última actualización: 2026-06-25.

> Guía práctica para el personal. Para detalle técnico ver `CLAUDE.md`; para reglas de negocio, los `REGLAS_NEGOCIO_*.md` / `MODELO_NEGOCIO_RRHH.md`.

---

## 1. Acceso al sistema

1. Abre el navegador en la dirección del sistema (la indica el administrador).
2. Ingresa **usuario** y **contraseña** y pulsa **Iniciar Sesión**.

**Seguridad del acceso:**
- Tras **5 intentos fallidos** la cuenta se **bloquea 15 minutos** (el aviso indica los intentos restantes).
- La sesión se **cierra sola tras 30 minutos de inactividad**; vuelve a iniciar sesión.
- Las contraseñas deben tener **mínimo 8 caracteres, con al menos una letra y un número**.
- Si olvidaste tu contraseña, **el administrador** (rol Administrador) puede restablecerla desde *Sistema → Usuarios*.

---

## 2. La pantalla principal

- **Barra lateral (izquierda):** menú de módulos. Solo muestra lo que tu rol puede usar.
- **Encabezado (arriba):**
  - 🔎 **Búsqueda global:** escribe y pulsa Enter para buscar empleados, bienes, talleres, rutas o visitantes (según tu acceso).
  - 🔔 **Campana de notificaciones:** muestra las alertas pendientes (contratos por vencer, expedientes incompletos, talleres vencidos, bienes en alerta, etc.) con un número rojo si hay asuntos por atender.
  - 🌙 **Tema claro/oscuro.**
  - 👤 **Tu nombre / Mi perfil** y **Salir**.
- **Panel Principal (Dashboard):** indicadores resumidos de tu área.

---

## 3. Roles y qué puede hacer cada uno

| Rol | Acceso principal |
|-----|------------------|
| **Administrador** | Todo el sistema, incluida la configuración, usuarios, roles, auditoría y accesos. |
| **RRHH** | Empleados, cargos, departamentos, horarios, asistencia, permisos/reposos, amonestaciones, vacaciones, visitantes, reportes y configuración. |
| **Turismo** | Talleres, sedes de formación, pasantes, rutas, visitantes y reportes. |
| **Inventario** | Bienes, categorías, ubicaciones, movimientos y reportes. |
| **Recepción** | Registro de visitas y asistencia. |

> Si intentas entrar a algo fuera de tu rol, el sistema te redirige con un aviso. Los cambios de permisos los hace el Administrador en *Sistema → Roles y Permisos*.

---

## 4. Módulos por área

### Recursos Humanos (RRHH)
- **Empleados:** alta mediante un **asistente por pasos** (datos personales → formación → institucionales → carga familiar → resumen). Cada empleado tiene un **expediente** con recaudos, constancias, traslados, vacaciones e historial.
- **Asistencia:** marcaje de entrada/salida; calcula puntualidad y horas (solo informativo).
- **Permisos y Reposos:** registrar, aprobar/rechazar; se distingue Permiso de Reposo.
- **Amonestaciones y Faltas:** el sistema cuenta; **3 amonestaciones activas** marcan "causa de despido" (es solo una alerta, el egreso es manual).
- **Vacaciones:** calcula el saldo (15 días hábiles + 1 por año, tope 30).
- **Egreso/Reingreso:** dar de baja NO borra al trabajador; queda como histórico para constancias y tiempo de servicio.
- **Constancias:** de trabajo, bancaria, horario, funciones, antigüedad o egreso, con correlativo.

### Recepción (Visitas)
- Registrar **entrada y salida** de visitantes (un mismo registro se cierra al marcar salida).
- En el Dashboard ves **Visitas hoy** y **Activas ahora** (personas dentro sin salida registrada).

### Formación
- **Talleres / Charlas / Inducciones** con participantes (adultos con cédula o niños "libres" con representante), informe demográfico, evidencias y estados que avanzan solos según fecha.

### Turismo (Rutas)
- **Rutas** con puntos en el mapa, participantes y estados; al **Finalizar** una ruta cuenta como ejecución.

### Inventario
- **Bienes** (Durable = inventariable con código BN; Fungible = consumible con cantidad), **categorías**, **ubicaciones**, **movimientos** (asignación, traslado, baja, mantenimiento) y **bajas**.

### Sistema (Administrador)
- **Configuración** institucional (RIF, metas anuales, umbrales de alerta, etc.).
- **Usuarios**, **Roles y Permisos**, **Auditoría** (bitácora de cambios), **Accesos** (inicios de sesión) y **Papelera**.

---

## 5. Reportes e indicadores

Entra a **Análisis → Reportes**. Verás solo las tarjetas de tu rol. Cada reporte permite **filtrar** y **exportar a Excel/PDF**.

- **RRHH:** directorio, asistencia, permisos, amonestaciones, egresos, comisión de servicio, constancias, expedientes incompletos, carga familiar, **saldo de vacaciones**, visitantes y **estadísticas de visitas**.
- **Formación/Turismo:** talleres, **informe trimestral**, cobertura comunitaria, rutas, participación, **ejecuciones de ruta**, pasantes.
- **Inventario:** inventario, kardex, bienes asignados, bajas.
- **Seguridad (Admin):** auditoría del sistema y **accesos** (quién entró, cuándo y desde qué IP).
- **Indicadores de Gestión:** panel con KPIs por área; arriba puedes elegir el **año** a consultar.

> En cualquier reporte o listado, los botones **Excel** y **PDF** exportan lo que estás viendo (respetando los filtros), con el membrete institucional.

---

## 6. Notificaciones (campana)

La 🔔 del encabezado reúne los pendientes de tu área. Haz clic para ver la lista y entra a cada uno para atenderlo. Los roles RRHH/Administrador tienen además el enlace al **Centro de Alertas** completo.

---

## 7. Búsqueda global

Escribe en el 🔎 del encabezado (mínimo 2 letras) y pulsa Enter. Muestra coincidencias agrupadas (empleados, inventario, talleres, rutas, visitantes) **según tu acceso**. Haz clic en un resultado para abrirlo.

---

## 8. Mi perfil

Entra desde tu nombre (arriba a la derecha) → **Mi Perfil**:
- Cambiar **nombre de usuario**.
- Cambiar **contraseña** (pide la actual; la nueva debe cumplir la política de seguridad).

---

## 9. Preguntas frecuentes

- **No veo un módulo en el menú.** No está habilitado para tu rol; solicítalo al Administrador.
- **Mi cuenta se bloqueó.** Espera 15 minutos o pide al Administrador que la restablezca.
- **La sesión se cerró sola.** Es por inactividad (30 min); vuelve a iniciar sesión.
- **Guardé dos veces por error.** El sistema evita registros duplicados por doble envío; si dudas, revisa el listado antes de repetir.
- **¿Se pierde algo al eliminar?** No: casi todo es borrado lógico y se puede recuperar desde *Sistema → Papelera* (según permisos).
