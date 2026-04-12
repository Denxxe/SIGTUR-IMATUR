<?php
/**
 * Script temporal para sembrar el usuario maestro
 * Se debe eliminar después de ejecutarse
 */
require_once '../config/config.php';
require_once '../app/core/Database.php';

try {
    $db = new Database();

    // 1. Crear un Rol de Administrador por defecto si no existe
    $db->query("SELECT id FROM roles WHERE nombre = 'Administrador'");
    $rol = $db->single();
    if (!$rol) {
        $db->query("INSERT INTO roles (nombre, descripcion, created_by) VALUES ('Administrador', 'Rol maestro superior', 1) RETURNING id");
        $rol_id = $db->single()->id;
        echo "Rol Administrador creado.<br>";
    } else {
        $rol_id = $rol->id;
    }

    // 2. Verificar si el usuario admin ya existe
    $db->query("SELECT id FROM usuarios WHERE username = 'admin'");
    if (!$db->single()) {
        $hashed_password = password_hash('admin123', PASSWORD_BCRYPT);
        $db->query("INSERT INTO usuarios (id_rol, username, password, created_by) VALUES (:id_rol, 'admin', :password, 1)");
        $db->bind(':id_rol', $rol_id);
        $db->bind(':password', $hashed_password);
        $db->execute();
        
        echo "<h1>Usuario 'admin' creado exitosamente</h1>";
        echo "<p>Contraseña: <strong>admin123</strong></p>";
        echo "<a href='index.php'>Ir al Login</a>";
    } else {
        echo "<h1>El usuario 'admin' ya existe en la base de datos.</h1>";
        echo "<a href='index.php'>Ir al Login</a>";
    }

} catch(Exception $e) {
    echo "Error sembrando base de datos: " . $e->getMessage();
}
