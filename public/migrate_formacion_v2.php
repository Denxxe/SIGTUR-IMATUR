<?php
require_once '../config/config.php';
require_once '../app/core/Database.php';

try {
    $db = new Database();
    $db->beginTransaction();

    echo "iniciando migración v2...<br>";

    // 1. TALLER INFORMES
    $db->query("
        CREATE TABLE IF NOT EXISTS taller_informes (
            id SERIAL PRIMARY KEY,
            id_taller INT NOT NULL UNIQUE,
            unidad_estadal VARCHAR(255) DEFAULT 'Sucre',
            lugar_exacto VARCHAR(255),
            instituciones_presentes TEXT,
            mujeres INT DEFAULT 0,
            hombres INT DEFAULT 0,
            ninas INT DEFAULT 0,
            ninos INT DEFAULT 0,
            total_atendidas INT DEFAULT 0,
            resumen_actividad TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP,
            created_by INT,
            CONSTRAINT fk_taller_inf FOREIGN KEY (id_taller) REFERENCES talleres(id) ON DELETE CASCADE
        );
    ");
    $db->execute();
    echo "✅ Tabla taller_informes creada<br>";

    // 2. TALLER INVENTARIO
    $db->query("
        CREATE TABLE IF NOT EXISTS taller_inventario (
            id SERIAL PRIMARY KEY,
            id_taller INT NOT NULL,
            id_inventario INT NOT NULL,
            cantidad INT DEFAULT 1,
            observaciones TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_by INT,
            CONSTRAINT fk_taller_inv_taller FOREIGN KEY (id_taller) REFERENCES talleres(id) ON DELETE CASCADE,
            CONSTRAINT fk_taller_inv_item FOREIGN KEY (id_inventario) REFERENCES inventario(id) ON DELETE RESTRICT,
            CONSTRAINT uq_taller_inventario UNIQUE (id_taller, id_inventario)
        );
    ");
    $db->execute();
    echo "✅ Tabla taller_inventario creada<br>";

    // 3. PASANTES
    $db->query("
        CREATE TABLE IF NOT EXISTS pasantes (
            id SERIAL PRIMARY KEY,
            cedula VARCHAR(20) NOT NULL UNIQUE,
            nombre VARCHAR(100) NOT NULL,
            apellido VARCHAR(100) NOT NULL,
            institucion VARCHAR(200) NOT NULL,
            carrera VARCHAR(200),
            id_tutor_institucional INT,
            fecha_inicio DATE,
            fecha_fin DATE,
            estado VARCHAR(50) DEFAULT 'Postulado' CHECK (estado IN ('Postulado', 'Aceptado', 'En Curso', 'Culminado', 'Rechazado')),
            evaluacion TEXT,
            nota DECIMAL(5,2),
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP,
            deleted_at TIMESTAMP,
            created_by INT,
            CONSTRAINT fk_pasante_tutor FOREIGN KEY (id_tutor_institucional) REFERENCES empleados(id) ON DELETE SET NULL
        );
    ");
    $db->execute();
    echo "✅ Tabla pasantes creada<br>";

    // 4. PASANTE DOCUMENTOS
    $db->query("
        CREATE TABLE IF NOT EXISTS pasante_documentos (
            id SERIAL PRIMARY KEY,
            id_pasante INT NOT NULL,
            tipo_documento VARCHAR(100) NOT NULL CHECK (tipo_documento IN ('Carta de Postulación', 'Carta de Aceptación', 'Evaluación', 'Otro')),
            entregado BOOLEAN DEFAULT FALSE,
            archivo_url TEXT,
            observaciones TEXT,
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_by INT,
            CONSTRAINT fk_pasante_doc FOREIGN KEY (id_pasante) REFERENCES pasantes(id) ON DELETE CASCADE
        );
    ");
    $db->execute();
    echo "✅ Tabla pasante_documentos creada<br>";

    // DUMMY DATA SEEDING
    echo "<br>Inyectando datos de prueba...<br>";

    // Crear un pasante dummy
    $db->query("SELECT id FROM empleados WHERE is_active = TRUE LIMIT 1");
    $tutor = $db->single();
    $id_tutor = $tutor ? $tutor->id : null;

    $db->query("SELECT id FROM pasantes WHERE cedula = 'V-30123456'");
    if (!$db->single()) {
        $db->query("
            INSERT INTO pasantes (cedula, nombre, apellido, institucion, carrera, id_tutor_institucional, fecha_inicio, fecha_fin, estado)
            VALUES ('V-30123456', 'María', 'López', 'UPTAEB', 'Turismo', :id_tutor, CURRENT_DATE, CURRENT_DATE + INTERVAL '3 months', 'En Curso')
            RETURNING id;
        ");
        $db->bind(':id_tutor', $id_tutor);
        $pasante = $db->single();

        if ($pasante) {
            $db->query("
                INSERT INTO pasante_documentos (id_pasante, tipo_documento, entregado, observaciones) VALUES
                (:id, 'Carta de Postulación', TRUE, 'Recibida por coordinación'),
                (:id, 'Carta de Aceptación', TRUE, 'Firmada por el director'),
                (:id, 'Evaluación', FALSE, 'Pendiente al finalizar')
            ");
            $db->bind(':id', $pasante->id);
            $db->execute();
        }
    }

    $db->endTransaction();
    echo "🎉 Migración completada exitosamente!";

} catch (Exception $e) {
    if (isset($db)) $db->cancelTransaction();
    echo "❌ Error: " . $e->getMessage();
}
