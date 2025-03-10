<?php
require_once __DIR__ . '/../init.php';

class Database {
    private static $instance = null;    
    private $connection;
    private $host;
    private $username;
    private $password;
    private $database;

    private function __construct() {
        $config = config();
        $this->host = $config['DB_HOST'];
        $this->username = $config['DB_USERNAME'];
        $this->password = $config['DB_PASSWORD'];
        $this->database = $config['DB_NAME'];

        // Debug output
        error_log("Database connection attempt with:");
        error_log("Host: " . $this->host);
        error_log("Username: " . $this->username);
        error_log("Database: " . $this->database);

        if (empty($this->host) || empty($this->username) || empty($this->database)) {
            throw new Exception("Database configuration is incomplete. Please check your .env file.");
        }

        $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);

        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function prepare($sql) {
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->connection->error);
        }
        return $stmt;
    }

    public function query($sql, $params = [], $types = '') {
        if (empty($params)) {
            return $this->connection->query($sql);
        }

        $stmt = $this->prepare($sql);
        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt->get_result();
    }

    public function escape($value) {
        trigger_error('escape() is deprecated. Use prepared statements instead.', E_USER_DEPRECATED);
        return $this->connection->real_escape_string($value);
    }

    public function getError() {
        return $this->connection->error;
    }

    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    public function beginTransaction() {
        return $this->connection->begin_transaction();
    }

    public function commit() {
        return $this->connection->commit();
    }

    public function rollback() {
        return $this->connection->rollback();
    }
}
