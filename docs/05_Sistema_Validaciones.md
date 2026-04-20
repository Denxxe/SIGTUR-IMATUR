# Sistema de Validaciones Unificadas
*(Data Sanitization & UI Validation Protocol)*

SIGTUR-IMATUR emplea un esquema de validación en dos capas ("Two-Tier Validation Guard"): una capa de cliente basada en eventos de tippeo de JavaScript y una capa de servidor hermética construida sobre PDO y Filtros PHP.

---

## 1. Capa de Cliente (El Escudo `sigtur-validations.js`)
Ubicado en `public/assets/js/sigtur-validations.js` y conectado a todos los módulos a través de `footer.php`, este script asegura que problemas tipográficos y de formato ni siquiera abandonen el navegador del usuario.

### Reglas de Auto-formateo
El validador atrapa la estructura del formulario en el DOM y aplica los siguientes comportamientos, guiándose por el atributo `name` o `id` en tiempo real (evento de JS `input`):

1. **Cédulas Venezolanas (`name="cedula"`)**
   - **Regla:** Forzar el estándar `(V/E)-1234567`.
   - **Mecanismo:** Si el usuario pulsa un número directamente, el validador estampa una `V-` automáticamente. Bloquea la inserción de letras y corrige guiones repetidos al vuelo.
2. **Nombres Propios (`name="nombre"`, `name="apellido"`)**
   - **Regla:** Sin números, sin símbolos especiales, correctamente capitalizado.
   - **Mecanismo:** Usa `replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')` para rebotar teclas numéricas, y luego capitaliza automáticamente las letras luego de espacios, asistiendo a secretarias o usuarios a escribir un nombre presentable.
3. **Restricción Temporal de Fechas (`type="date"` + "nacimiento")**
   - Inyecta `max="HOY"` directamente al HTML del navegador. Es imposible que el selector dibuje o acepte personas nacidas el día de mañana.
4. **Validación Visual de Bootstrap (`.was-validated`)**
   - El script captura el evento `submit()`. Si hay campos vacíos obligatorios, intercepta el clic impidiendo la petición, y pinta las cajas de rojo usando el ecosistema base de Bootstrap 5.

---

## 2. Capa Servidor (PHP Backend)
La segunda capa defiende al sistema si la capa de cliente lograra ser evadida o alterada. No interrumpe la navegación del usuario sino que actúa silenciosamente en el Controlador.

### Saneamiento de Strings Global (Super Global Filter)
Al inicio de cada bloque ` store()` en los controladores, SIGTUR inyecta:
`$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);`
- Esto convierte entradas maliciosas tipo `<script>alert('XSS')</script>` a entidades escapadas `&lt;script&gt;`, neutralizando el *Cross-Site Scripting*.

### Casting Forzado a Nulos y Enteros
- Cualquier ingreso de un ID es protegido en RAM truncándolo mediante casting: `$id = (int)$_POST['id']`. De este modo es imposible introducir `id= 5 OR 1=1`.
- Los datos de fechas o números opcionales que llegan del usuario vacío `""` son convertibles mediante validaciones ternarias: `$this->fecha_nacimiento = !empty($data['fecha_nac']) ? $data['fecha_nac'] : null;`. Esto previene el clásico bug de PostgreSQL `invalid input syntax for type date: ""`.
