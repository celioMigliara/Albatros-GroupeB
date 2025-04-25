<?php
require_once __DIR__ . '/../ModeleDBB2.php';
class Utilisateur {
    public static function getTechniciens() {
        $pdo = Database::getInstance()->getConnection(); 
        $stmt = $pdo->query("SELECT id_utilisateur, nom_utilisateur, prenom_utilisateur FROM utilisateur WHERE id_role = 2");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        $pdo = Database::getInstance()->getConnection(); 
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }



}