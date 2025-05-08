<?php

class Database {
    private static ?Database $instance = null;
    private PDO $conn;

    private function __construct() {
        $host     = 'localhost'; // Remplacez par l'adresse de votre serveur
        $port     = '3306';      // Remplacez par le port de votre base de données
        $dbname   = 'albatros';  // Remplacez par le nom de votre base de données
        $username = 'root';      // Remplacez par votre nom d'utilisateur
        $password = '';          // Remplacez par votre mot de passe

        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";

        try {
            $this->conn = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->conn;
    }

    // Empêcher le clonage de l'instance
    private function __clone() {}

    // Empêcher la désérialisation de l'instance
    public function __wakeup() {
        throw new RuntimeException("Désérialisation non autorisée.");
    }
}
?>
