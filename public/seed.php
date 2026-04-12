<?php
/**
 * Script temporal para sembrar el usuario maestro
 * Se debe eliminar después de ejecutarse
 */
require_once '../config/config.php';
require_once '../app/core/Database.php';

try {
    $db = new Database();
    $db->beginTransaction();

    echo "<h3>Iniciando Siembra de BD SIGTUR-IMATUR...</h3>";

    // 1. Crear un Rol de Administrador
    $db->query("SELECT id FROM roles WHERE nombre = 'Administrador'");
    $rol = $db->single();
    if (!$rol) {
        $db->query("INSERT INTO roles (nombre, descripcion, created_by) VALUES ('Administrador', 'Rol maestro', 1) RETURNING id");
        $rol_id = $db->single()->id;
        echo "✅ Rol Administrador creado.<br>";
    } else {
        $rol_id = $rol->id;
    }

    // 2. Crear Departamento Base
    $db->query("SELECT id FROM departamentos WHERE nombre = 'Dirección General'");
    $depto = $db->single();
    if (!$depto) {
        $db->query("INSERT INTO departamentos (nombre, descripcion, created_by) VALUES ('Dirección General', 'Sede Principal', 1) RETURNING id");
        $depto_id = $db->single()->id;
        echo "✅ Departamento Base creado.<br>";
    } else {
        $depto_id = $depto->id;
    }

    // 3. Crear Cargo Base
    $db->query("SELECT id FROM cargos WHERE nombre = 'Director'");
    $cargo = $db->single();
    if (!$cargo) {
        $db->query("INSERT INTO cargos (nombre, sueldo_base, created_by) VALUES ('Director', 1000.00, 1) RETURNING id");
        $cargo_id = $db->single()->id;
        echo "✅ Cargo Director creado.<br>";
    } else {
        $cargo_id = $cargo->id;
    }

    // 4. Crear Persona para el Admin
    $db->query("SELECT id FROM personas WHERE cedula = 'V-00000000'");
    $persona = $db->single();
    if (!$persona) {
        // En PostgreSQL 12+, currval/lastval o RETURNING se usan
        $db->query("INSERT INTO personas (cedula, nombre, apellido, telefono, direccion, created_by) 
                    VALUES ('V-00000000', 'Super', 'Admin', '0000-0000000', 'Localhost', 1) RETURNING id");
        $persona_id = $db->single()->id;
        echo "✅ Datos Personales del Admin creados.<br>";
    } else {
        $persona_id = $persona->id;
    }

    // 5. Crear Empleado para el Admin
    $db->query("SELECT id FROM empleados WHERE id_persona = :id_persona");
    $db->bind(':id_persona', $persona_id);
    $empleado = $db->single();
    if (!$empleado) {
        $db->query("INSERT INTO empleados (id_persona, id_cargo, id_departamento, created_by) 
                    VALUES (:id_persona, :id_cargo, :id_depto, 1) RETURNING id");
        $db->bind(':id_persona', $persona_id);
        $db->bind(':id_cargo', $cargo_id);
        $db->bind(':id_depto', $depto_id);
        $empleado_id = $db->single()->id;
        echo "✅ Ficha de Empleado del Admin creada.<br>";
    } else {
        $empleado_id = $empleado->id;
    }

    // 6. Finalmente, Crear Usuario
    $db->query("SELECT id FROM usuarios WHERE username = 'admin'");
    if (!$db->single()) {
        $hashed_password = password_hash('admin123', PASSWORD_BCRYPT);
        $db->query("INSERT INTO usuarios (id_empleado, id_rol, username, password, created_by) 
                    VALUES (:id_empleado, :id_rol, 'admin', :password, 1)");
        $db->bind(':id_empleado', $empleado_id);
        $db->bind(':id_rol', $rol_id);
        $db->bind(':password', $hashed_password);
        $db->execute();
        
        $db->endTransaction();
        echo "<hr><h1>🎉 Usuario 'admin' sembrado exitosamente</h1>";
        echo "<ul><li><strong>Base de datos poblada en cadena.</strong></li>";
        echo "<li><strong>Estilos CSS movidos a /public para correcta visualización.</strong></li></ul>";
        echo "<h4>Tus Credenciales:</h4>";
        echo "<p>Usuario: <strong>admin</strong></p>";
        echo "<p>Contraseña: <strong>admin123</strong></p>";
        echo "<a href='index.php'>Ir al Login Seguro</a>";
    } else {
        $db->endTransaction();
        echo "<hr><h1>⚠️ El usuario 'admin' ya había sido creado.</h1>";
        echo "<a href='index.php'>Ir al Login</a>";
    }

} catch(Exception $e) {
    if (isset($db)) $db->cancelTransaction();
    echo "<h1 style='color:red;'>Error Crítico: " . $e->getMessage() . "</h1>";
    echo "<pre>";
    print_r($e);
    echo "</pre>";
}
