# Historial de Desarrollo y Corrección de Bugs
*(Bitácora de todos los componentes generados e hitos superados)*

A lo largo del proyecto se implementaron las siguientes características fundamentales para garantizar un sistema estable:

## Correcciones e Ingeniería Aplicada al Backend
1. **Refactorización de persistencia (Falsos Positivos de DB)**
   - *Problema Original:* El sistema devolvía "Éxito" pero el registro seguía en Papelera.
   - *Solución Definitiva:* Anulación del sistema persistente en `Database.php`. Al inyectar el guardado de auditoria en la misma sub-instancia de `Database` se evitan deadlocks (bloqueos ciegos) en PostgreSQL.

2. **Saneamiento a Valores Nulos (Bug "Fechas de Nacimiento")**
   - *Problema Original:* A la hora de añadir un empleado dejando la fecha vacía, PostgreSQL generaba fallo de sintaxis en `DATE("")`.
   - *Solución Definitiva:* Los modelos convierten las fechas vacías a `null` explícito de PHP antes de enviarlo a PDO.

3. **Restricciones "CHECK" corregidas**
   - Refactorizada la tabla `audit_logs` eliminando el restrictivo comprobador estricto (`operacion_check`) para soportar la constante de movimiento `RESTORE` y `UPDATE`.

## Interfaces e Ingeniería Aplicada al Frontend
1. **Integración Global de Validaciones (`sigtur-validations.js`)**
   - Se construyó desde cero un script autónomo que evalúa atributos, tipeo dinámico, capitaliza nombres, limpia la Cédula venezolana (`V-/E-`) e inmoviliza fechas del pasado buscando el atributo tipo `date`.

2. **Reacondicionamiento Visual Avanzado (Toasts & Sidebar)**
   - Modificación profunda en `inc/footer.php` e `inc/header.php` para instalar Toasts dinámicos que detectan éxito, fallo en base de datos.
   - Sidebar que es responsivo a dispositivos portátiles adaptando un degradado moderno acorde a las leyes turísticas de diseño (`glassmorphism` moderado).
   - Separación inteligente de acordeones con validación de "links activos".

3. **Diagnóstico a Bajo Nivel (Script en `public/inspect.php`)**
   - Se generó un marco de pruebas `tester` usando código RAW para saltarse las restricciones y analizar el estado interno de las bases de Postgres cuando estas fallaban. Todo script diagnóstico fue eliminado al completar las pruebas para evitar vulneraciones de seguridad.

## Migraciones Incrementales Registradas

1. **001 — Visitantes y Visitas**
   - Tablas `visitantes` (personas externas) y `visitas` (marcaje entrada/salida con patrón toggle: si hay visita abierta → cierra; si no → abre).

2. **002 — Extensiones RRHH y Auditoría**
   - Nuevas tablas: `horarios`, `permisos_laborales`, `vacaciones`.
   - Columnas en `empleados`: `tipo_contrato`, `fecha_egreso`, `id_horario`.
   - Columna `tipo_actividad` en `talleres`.
   - Auditoría completa en: `taller_informes`, `taller_inventario`, `participantes_taller`, `pasantes`, `pasante_documentos`, `ruta_inventario`.

3. **003 — Normalización Pasantes → Personas**
   - Agrega `id_persona INT FK` a `pasantes`.
   - Migra datos: para cada pasante activo busca o crea la `persona` equivalente por cédula.
   - Elimina columnas redundantes (`cedula`, `nombre`, `apellido`) de `pasantes`.
   - Resuelve la inconsistencia de normalización donde `pasantes` era independiente de `personas`.
   - **Debe ejecutarse después de 001 y 002.**

---
*Notas Finales*: Toda esta infraestructura deja la pista lista para un despliegue sin interferencias en plataformas on-premise local para el IMATUR.
