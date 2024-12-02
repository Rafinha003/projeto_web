<?php
class Database {
    private $host = 'localhost';
    private $dbname = 'sistema_web';
    private $username = 'root';
    private $password = '';

    public function getConnection(): PDO {
        try {
            $conn = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->username, $this->password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (PDOException $e) {
            die("Erro de conexão com o banco de dados: " . $e->getMessage());
        }
    }
}
