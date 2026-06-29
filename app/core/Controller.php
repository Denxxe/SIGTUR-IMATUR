<?php
/**
 * Clase Controller: Clase base de controladores
 * Carga los modelos y las vistas
 */
class Controller {
    // Método para cargar el modelo
    public function model($model) {
        // Requerir el archivo del modelo
        require_once('../app/models/' . $model . '.php');
        // Instanciar el modelo
        return new $model();
    }

    // Método para cargar la vista
    public function view($view, $data = []) {
        // Comprobar si el archivo de la vista existe
        if (file_exists('../app/views/' . $view . '.php')) {
            require_once('../app/views/' . $view . '.php');
        } else {
            // Error en la vista (puedes redirigir a una página 404)
            die('La vista ' . $view . ' no existe');
        }
    }

    // Helper para obtener User ID de la sesión actual
    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    // Valida un correo electrónico: debe ser un email válido (filter_var) y NO
    // contener símbolos especiales fuera del set seguro (rechaza espacios y
    // caracteres raros). Mismo criterio que la validación front (sigtur-validations.js).
    protected function emailValido(string $email): bool {
        if ($email === '') return false;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
        return (bool) preg_match('/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/', $email);
    }

    // Valida un teléfono venezolano: exactamente 11 dígitos que inician en 0
    // (0XXX + 7 = formato que produce el campo del front). Mismo criterio que
    // sigtur-validations.js (prefijo móvil + 7 dígitos).
    protected function telefonoValido(string $tel): bool {
        return (bool) preg_match('/^0\d{10}$/', preg_replace('/\D/', '', $tel));
    }

    // Valida un RIF venezolano: una letra de tipo (V/E/J/P/G/C) + 8 dígitos +
    // 1 dígito verificador (9 dígitos en total). Acepta guiones/espacios. Mismo
    // criterio que la validación front (sigtur-validations.js).
    protected function rifValido(string $rif): bool {
        $r = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $rif));
        return (bool) preg_match('/^[VEJPGC]\d{9}$/', $r);
    }

    // Normaliza un RIF al formato canónico "X-XXXXXXXX-X". Si no es válido,
    // devuelve el valor original sin tocar.
    protected function normalizarRif(string $rif): string {
        $r = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $rif));
        if (preg_match('/^([VEJPGC])(\d{8})(\d)$/', $r, $m)) {
            return $m[1] . '-' . $m[2] . '-' . $m[3];
        }
        return $rif;
    }

    /**
     * Carnetización — guarda la foto subida ($_FILES['foto']) de una persona.
     * Valida imagen (jpg/jpeg/png, ≤5 MB, MIME real), la mueve a
     * storage/uploads/fotos/ y actualiza personas.foto_url. Lanza Exception
     * con mensaje claro si algo falla. Mismo criterio que subirDocumento().
     */
    protected function guardarFotoPersona(int $idPersona): void {
        if ($idPersona <= 0) throw new Exception('Persona no válida.');
        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Debe adjuntar una imagen válida (JPG o PNG).');
        }
        $permitidas = ['jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $permitidas, true)) {
            throw new Exception('Formato no permitido. Use JPG o PNG.');
        }
        if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
            throw new Exception('La imagen supera el límite de 5 MB.');
        }
        $mimeReal = function_exists('mime_content_type') ? @mime_content_type($_FILES['foto']['tmp_name']) : null;
        if ($mimeReal !== null && !in_array($mimeReal, ['image/jpeg', 'image/png'], true)) {
            throw new Exception('El contenido del archivo no corresponde a una imagen JPG/PNG válida.');
        }

        $fileName  = 'Foto_Persona_' . $idPersona . '_' . time() . '.' . $ext;
        $uploadDir = dirname(dirname(__DIR__)) . '/storage/uploads/fotos/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }
        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $fileName)) {
            throw new Exception('No se pudo guardar la imagen en el servidor.');
        }
        Persona::actualizarFoto($idPersona, $fileName, $this->getUserId());
    }

    // Sanitiza $_POST: elimina tags HTML sin corromper caracteres UTF-8 (tildes, ñ, etc.)
    protected function sanitizePost(): array {
        $raw = $_POST ?? [];
        return array_map(function($v) {
            if (!is_string($v)) return $v;
            return trim(strip_tags($v));
        }, $raw);
    }
}
