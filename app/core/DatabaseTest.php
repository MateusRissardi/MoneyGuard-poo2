<?php

class DatabaseTest
{
    private $host = 'localhost';
    private $db_name = 'money_guard_test';
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
            die('Erro de Conexão com o Banco de TESTE: ' . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new DatabaseTest();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function cleanDB()
    {
        $tables = [
            '"ExpenseSplit"',
            '"Settlement"',
            '"Expense"',
            '"GroupMember"',
            '"Group"',
            '"User"'
        ];
        $this->conn->exec("BEGIN");
        try {
            foreach ($tables as $table) {
                $this->conn->exec("TRUNCATE $table RESTART IDENTITY CASCADE");
            }
            $this->conn->exec("COMMIT");
        } catch (PDOException $e) {
            $this->conn->exec("ROLLBACK");
            throw $e;
        }
    }

    private function __clone()
    {
    }
}
?>