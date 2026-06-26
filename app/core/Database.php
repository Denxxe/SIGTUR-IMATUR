<?php
/**
 * Clase Database: Conexión Base usando PDO para PostgreSQL
 * Se ha eliminado la persistencia para evitar fugas de estado en transacciones.
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

    public function __construct($pdoInstance = null) {
        if ($pdoInstance) {
            $this->dbh = $pdoInstance;
            return;
        }

        // Configurar DSN (Data Source Name)
        $dsn = 'pgsql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->dbname;
        
        $options = array(
            // PERSISTENCIA DESACTIVADA: Evita que múltiples objetos compartan estado transaccional inadvertidamente.
            PDO::ATTR_PERSISTENT => false, 
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        );

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            // Registrar el detalle en el log; NO exponerlo (puede filtrar host/credenciales/versión).
            error_log('[SIGTUR] Error de conexión a BD: ' . $this->error);
            if (PHP_SAPI === 'cli') {
                fwrite(STDERR, 'Error de conexión con la base de datos.' . PHP_EOL);
            } else {
                http_response_code(500);
                echo 'Error de conexión con la base de datos. Contacte al administrador.';
            }
            exit(1);
        }
    }

    /**
     * Acceso al controlador PDO interno para compartirlo entre modelos.
     */
    public function getHandler() {
        return $this->dbh;
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

    // Obtener el número de filas afectadas
    public function rowCount() {
        return $this->stmt->rowCount();
    }

    // Transacciones
    public function beginTransaction() {
        return $this->dbh->beginTransaction();
    }

    /**
     * Finalizar transacción (Commit)
     * @return bool True si tuvo éxito, False de lo contrario.
     */
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

    public function inTransaction() {
        return $this->dbh->inTransaction();
    }
}
