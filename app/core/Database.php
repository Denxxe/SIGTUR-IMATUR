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
    private $stmt;
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
        $this->stmt = $this->dbh->prepare($sql);
    }

    // Vincular valores (bind)
    public function bind($param, $value, $type = null) {
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
        $this->stmt->bindValue($param, $value, $type);
    }

    // Ejecutar la consulta preparada
    public function execute() {
        return $this->stmt->execute();
    }

    // Obtener el conjunto de resultados como array de objetos
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    // Obtener un único registro
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }

    // Obtener el número de filas
    public function rowCount() {
        return $this->stmt->rowCount();
    }

    // Obtener el último ID insertado (PostgreSQL requiere nombre de secuencia)
    public function lastInsertId($sequence = null) {
        return $this->dbh->lastInsertId($sequence);
    }

    // Transacciones
    public function beginTransaction() {
        return $this->dbh->beginTransaction();
    }

    public function endTransaction() {
        if ($this->dbh->inTransaction()) {
            return $this->dbh->commit();
        }
        return false;
    }

    public function cancelTransaction() {
        if ($this->dbh->inTransaction()) {
            return $this->dbh->rollBack();
        }
        return false;
    }
}
