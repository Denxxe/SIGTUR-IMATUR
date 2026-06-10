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

    // Sanitiza $_POST: elimina tags HTML sin corromper caracteres UTF-8 (tildes, ñ, etc.)
    protected function sanitizePost(): array {
        $raw = $_POST ?? [];
        return array_map(function($v) {
            if (!is_string($v)) return $v;
            return trim(strip_tags($v));
        }, $raw);
    }
}
