<?php
require_once __DIR__ . '/../../ModeleDBB2.php';

class Site {
    public static function getAll() {
        $pdo = Database::getInstance()->getConnection(); 
        $stmt = $pdo->query("SELECT * FROM site");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
