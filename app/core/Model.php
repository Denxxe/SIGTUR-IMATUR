<?php
/**
 * Clase Model: Clase base para todos los modelos
 */
class Model {
    protected $db;

    public function __construct() {
        // Inicializar la clase Database
        $this->db = new Database();
    }

    // Métodos CRUD genéricos opcionales (pero útiles)
}
