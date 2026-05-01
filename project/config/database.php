<?php
class Database {
    private $host = "localhost";
    private $db_name = "practicaldb";
    private $username = "root";
    private $password = "";
    public $pdo;
    
    public function __construct() {
        try {
            $this->pdo = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
}

// Create global database instance
$database = new Database();
$pdo = $database->pdo;
?>