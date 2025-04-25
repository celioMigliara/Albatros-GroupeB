<?php
require_once(__DIR__ . '/../vendor/autoload.php');
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Détection automatique de PHPUnit
if (!isset($_ENV['DB_ENV'])) {
    if (defined('PHPUNIT_RUNNING')) {
        $_ENV['DB_ENV'] = 'TEST';
    } else {
        $_ENV['DB_ENV'] = 'PROD';
    }
}

class Database {
    private static ?Database $instance = null;
    private PDO $conn;

    private function __construct() {
        $env = $_ENV['DB_ENV'] ?? 'PROD';
        $type = strtoupper($_ENV['DB_TYPE'] ?? 'MYSQL');
        $prefix = $env . '_' . $type . '_';

         
        $host     = $_ENV[$prefix . "SERVEUR"];
        $port     = $_ENV[$prefix . "PORT"];
        $dbname   = $_ENV[$prefix . "NOMDB"];
        $username = $_ENV[$prefix . "UTILISATEUR"];
        $password = $_ENV[$prefix . "MDP"];


        try {
            if ($type === 'MYSQL') {
                $this->conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
            } elseif ($type === 'MSSQL') {
                $this->conn = new PDO("sqlsrv:Server=$host,$port;Database=$dbname", $username, $password);
            } else {
                throw new RuntimeException("Type de base de données non supporté : $type");
            }

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new RuntimeException("Erreur de connexion ($type) : " . $e->getMessage());
        }
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->conn;
    }

    public static function resetInstance(): void {
        self::$instance = null;
    }
}

