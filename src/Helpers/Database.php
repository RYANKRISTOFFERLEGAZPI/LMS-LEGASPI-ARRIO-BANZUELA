<?php
declare(strict_types=1);
namespace App\Helpers;
use PDO;
use PDOException;
use RuntimeException;

class Database {
    private static ?Database $instance = null;
    
    private $connection;
    private $config;
    
    private function __construct(){
        $this->loadConfig();
        $this->connect();
    }

    private function loadConfig(){
        $this->config = [
            'host' => getenv('DB_HOST') ?: 'localhost',
            'port' => getenv('DB_PORT') ?: '3306',
            'name' => getenv('DB_NAME') ?: 'oop',
            'user' => getenv('DB_USER') ?: 'root',
            'password' => getenv('DB_PASSWORD') ?: '',
            'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
            'driver' => getenv('DB_DRIVER') ?: 'mysql'
        ];
        
        if (!$this->config['name'] || !$this->config['user']) {
            throw new PDOException("Database name and user are required in .env file");
        }
    }

    private function connect(){
        try {
            $dsn = sprintf(
                "%s:host=%s;port=%s;dbname=%s;charset=%s",
                $this->config['driver'],
                $this->config['host'],
                $this->config['port'],
                $this->config['name'],
                $this->config['charset']
            );
            
            $this->connection = new PDO(
                $dsn,
                $this->config['user'],
                $this->config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            
        } catch (PDOException $e) {
            throw new PDOException("Database connection failed: " . $e->getMessage());
        }
    }

    private function __clone() {}
    
    public function __wakeup() {
        throw new RuntimeException("Cannot unserialize singleton");
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->connection;
    }

    public function query(string $sql): array {
        if ($this->connection === null) {
            return [];
        }

        $stmt = $this->connection->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>