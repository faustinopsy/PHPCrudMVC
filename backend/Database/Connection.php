<?php
namespace App\Database;
use Exception;
use PDO;
use PDOException;

 class Connection {
    private $host;
    private $db_name;
    private $username;
    private $password;
    protected $conn;
    private $db_type;
public function __construct() {
    $configFilePath = __DIR__ . '/config.php';
    $config = require $configFilePath;
    $this->db_type = DB_TYPE;
    
    if ($this->db_type == 'mysql') {
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASSWORD;
    }
    
    $this->connect();
}
public function connect() {
  try {
    switch ($this->db_type) {
        case "mysql":
          $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name;
            break;
        case "pgsql":
            $dsn = "pgsql:host=" . $this->host . ";dbname=" . $this->db_name;
            break;
        case "sqlite":
            $dsn = "sqlite:". __DIR__ . "/Database.db";
            $filepath = __DIR__ . "/Database.db";
            if (!file_exists($filepath)) {
                die("Arquivo não encontrado: $filepath");
            }
            break;
        case "mssql":
           $dsn = "sqlsrv:Server=" . $this->host . ";Database=" . $this->db_name;
           break;
        default:
            throw new Exception("Database type not supported.");
      }
      if ($this->db_type == "sqlite") {
        $this->conn = new PDO($dsn);
    }  
    $this->conn = new PDO($dsn, $this->username, $this->password);
    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if ($this->conn === null) {
        throw new Exception('Failed to establish a database connection.');
    }
    } catch (PDOException $exception) {
        echo "Connection error: " . $exception->getMessage();
    } catch (Exception $exception) {
        echo $exception->getMessage();
    }
    
}

 }