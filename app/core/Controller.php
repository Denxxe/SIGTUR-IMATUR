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
}
