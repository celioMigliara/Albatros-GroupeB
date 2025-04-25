<?php
require_once __DIR__ . '/../../ModeleDBB2.php';
class Statut {
    public static function getAll() {
        $pdo = Database::getInstance()->getConnection(); 
        $stmt = $pdo->query("SELECT * FROM statut");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
