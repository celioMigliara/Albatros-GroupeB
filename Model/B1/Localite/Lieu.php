<?php
require_once __DIR__ . '/../../ModeleDBB2.php';

class Lieu {
    public static function getAll() {
        $pdo = Database::getInstance()->getConnection();         
        $stmt = $pdo->query("SELECT * FROM lieu");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}