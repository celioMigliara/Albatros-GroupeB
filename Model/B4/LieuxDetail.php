<?php
require_once __DIR__ . '/../ModeleDBB2.php';

//use PDO;

class LieuDetail
{
    private static $db;

    private static function initDb()
    {
        if (self::$db === null) {
            self::$db = DBManager::getInstance()->getConnection();
        }
    }

    // Récupérer un lieu par son ID
    public static function getLieuById($id_lieu)
    {
        self::initDb();
        $stmt = self::$db->prepare("SELECT * FROM lieu WHERE id_lieu = ?");
        $stmt->execute([$id_lieu]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Mettre à jour un lieu par son ID
    public static function updateLieu($id_lieu, $nom_lieu)
    {
        self::initDb();
        $stmt = self::$db->prepare("UPDATE lieu SET nom_lieu = ? WHERE id_lieu = ?");
        $stmt->execute([$nom_lieu, $id_lieu]);
    }

    // Supprimer (soft delete) un lieu
    public static function softDeleteLieu($id_lieu)
    {
        self::initDb();
        $stmt = self::$db->prepare("UPDATE lieu SET actif_lieu = false WHERE id_lieu = ?");
        $stmt->execute([$id_lieu]);
    }

    // Supprimer définitivement (hard delete) un lieu
    public static function deleteLieu($id_lieu)
    {
        self::initDb();
        $stmt = self::$db->prepare("DELETE FROM lieu WHERE id_lieu = ?");
        $stmt->execute([$id_lieu]);
    }


    public static function reactivateLieu($id)
    {
    self::initDb();
    $stmt = self::$db->prepare("UPDATE lieu SET actif_lieu = true WHERE id_lieu = ?");
    $stmt->execute([$id]);
    }

}
