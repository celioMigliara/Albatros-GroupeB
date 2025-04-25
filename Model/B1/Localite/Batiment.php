<?php
require_once __DIR__ . '/../../ModeleDBB2.php';
class Batiment {
    public static function getAll() {
        $pdo = Database::getInstance()->getConnection();         
        $stmt = $pdo->query("SELECT * FROM batiment");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
