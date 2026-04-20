# Documentación: Arquitectura Global y Auditoría

SIGTUR-IMATUR v2.0 ha sido construido usando un patrón MVC (Modelo-Vista-Controlador) escrito en PHP Nativo impulsado por una fuerte interacción dinámica del lado del cliente.

## 1. Patrón Arquitectónico Base
- **App Core (`app/core`)**: 
  - `Controller.php`: Clase que se encarga de renderizar la Vista (UI) o inyectar los modelos del lado del servidor.
  - `Router.php`: Enrutador dinámico que lee URLs limpias y bloquea el acceso si la sesión no ha arrancado (Seguridad básica por Módulos).
  - `Database.php`: Clase Singleton/Wrapper conectada a PostgreSQL mediante PDO, manejando preparación (Sanitización) de consultas, Binding y Commit/Rollback.

## 2. Sistema de Restauración y Auditoría
Uno de los puntos clave programados es la **Papelera Lógica Global**.

### Mecanismo "Soft-Delete" (Borrado Lógico)
Ningún registro se elimina mediante la sentencia `DELETE` SQL a nivel de usuario administrador. En su lugar, todos los modelos tienen una columna `is_active` (`BOOLEAN`). 
Al invocar `::delete()`, el registro se actualiza con `is_active = FALSE`. 
- Automáticamente el dato desaparece del controlador normal.
- El dato viaja y aparece en la vista de la **Papelera de Reciclaje**.

### Auditoría Completa (`AuditLog.php`)
Las acciones (`INSERT`, `UPDATE`) quedan grabadas con la IP, el usuario que hizo el cambio y la fecha en la tabla especial `audit_logs`.
- Para lograr esto, se eliminó la "Persistencia" (`PDO::ATTR_PERSISTENT = false`) de las conexiones de la base de datos para asegurar que al mezclar un UPDATE de un empleado con un log de auditoría en la misma transacción temporal, el proceso NO quede colapsado y devuelva falsos positivos. Todo se guarda como un paquete "Atómico" perfecto.

## 3. UI/UX: Interacción Inteligente

### 3.1. Validador Global de Formularios (`sigtur-validations.js`)
El "Escudo Defensivo" en el FrontEnd que se inyectó en `footer.php`. Se encarga de:
- Escribir `V-` automático en campos cuyo `name` es `cedula`.
- No permitir colocar números en campos de nombres.
- Obligar el uso de reglas de Bootstrap 5 (`.was-validated` y `.needs-validation`) evitando que un formulario envíe datos vacíos perdiendo tiempo de carga contra el servidor.

### 3.2. Sistema de Notificaciones Asíncronas (Toasts)
Se reemplazaron antiguos cuadros de alertas bloqueantes (`alert()`) y `flash()` planos por **Toasts Asíncronos** (pequeños cuadros elegantes en la esquina superior derecha).
- Son inyectados por PHP mandando un comando simple usando el *Session Helper* de `flash('nombre', 'Éxito', 'success')`.

## 4. Persistencia Compleja: PostgreSQL
- **Uso estricto de RETURNING id**: Debido a la arquitectura de PostgreSQL, al momento de querer conectar jerarquías (Como *Persona -> Empleado -> Cargo*), la manera documentada por la que rige este sistema para obtener el último registro y seguir vinculando hijos en código, es a través de `RETURNING id` en lugar del anticuado e inestable `lastInsertId()` de MySql.
