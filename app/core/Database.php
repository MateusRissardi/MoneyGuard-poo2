<?php

class Database
{
    private $host = 'localhost';
    private $db_name = 'money_guard';
    private $username = 'postgres';
    private $password = '123456';
    private $port = '5432';

    private $conn;
    private static $instance = null;

    private function __construct()
    {
        $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name}";

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Erro de Conexão: ' . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->conn;
    }

    private function __clone()
    {
    }
}

?>