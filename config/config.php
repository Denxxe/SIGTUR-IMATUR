<?php
/**
 * SIGTUR-IMATUR - Archivo de configuración global
 */

// URL Base de la aplicación
define('URL_ROOT', 'http://localhost/SIGTUR-IMATUR');

// Nombre del Sitio
define('SITE_NAME', 'SIGTUR-IMATUR');

// Configuración de la Base de Datos (PostgreSQL)
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_USER', 'postgres');
define('DB_PASS', 'root'); // Cambiar según el entorno real
define('DB_NAME', 'sigtur_imatur');

// Carpeta de la aplicación (Raíz del proyecto)
define('APP_ROOT', dirname(dirname(__FILE__)));
