<?php
/**
 * Clase Database: Conexión Singleton/Base usando PDO para PostgreSQL
 */
class Database {
    private $host = DB_HOST;
    private $port = DB_PORT;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private $dbh; // Database Handler
    private $error;

    public function __construct() {
        // Configurar DSN (Data Source Name)
        $dsn = 'pgsql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->dbname;
        
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        );

        // Crear una instancia de PDO
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            die("Error de conexión: " . $this->error);
        }
    }

    // Preparar la consulta
    public function query($sql) {
        return $this->dbh->prepare($sql);
    }

    // Vincular valores (bind)
    public function bind($stmt, $param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $stmt->bindValue($param, $value, $type);
    }

    // Ejecutar la consulta preparada
    public function execute($stmt) {
        return $stmt->execute();
    }

    // Obtener el conjunto de resultados como array de objetos
    public function resultSet($stmt) {
        $this->execute($stmt);
        return $stmt->fetchAll();
    }

    // Obtener un único registro
    public function single($stmt) {
        $this->execute($stmt);
        return $stmt->fetch();
    }

    // Obtener el número de filas
    public function rowCount($stmt) {
        return $stmt->rowCount();
    }
}
