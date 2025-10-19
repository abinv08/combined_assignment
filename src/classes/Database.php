<?php

class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "combined_db";
    private $conn;

    public function connect() {
        try {
            $dsn = "mysql:host=$this->host;dbname=$this->database;charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            return $this->conn;
        } catch(PDOException $e) {
            error_log("Connection failed: " . $e->getMessage());
            return null;
        }
    }
}