<?php
require_once __DIR__ . '/../ModeleDBB2.php';

//use PDO;

class Batiment
{
    private static $db;

    private static function initDb()
    {
        if (self::$db === null) {
            self::$db = Database::getInstance()->getConnection();
        }
    }

    public static function getAllBatiments()
    {
        self::initDb();
        $query = self::$db->query("SELECT * FROM batiment");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // contrôleur pour récupérer les bâtiments actifs
    public static function getActiveBatiments()
    {
        self::initDb();
        $query = self::$db->query("SELECT b.* FROM batiment AS b INNER JOIN site AS s  ON s.id_site = b.id_site WHERE b.actif_batiment = true AND s.actif_site     = true");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // contrôleur pour récupérer un bâtiment par son ID
    public static function getbatimentById($id)
    {
        self::initDb();
        $stmt = self::$db->prepare("SELECT * FROM batiment WHERE id_batiment = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // contrôleur pour ajouter un bâtiment
    public static function addBatiment($nom, $site_id, $actif) {
        self::initDb();
        $stmt = self::$db->prepare("SELECT id_batiment FROM batiment WHERE nom_batiment = ? AND id_site = ?");
        $stmt->execute([$nom, $site_id]);
        $id = $stmt->fetchColumn();
    
        if (!$id) {
            $stmt = self::$db->prepare("INSERT INTO batiment (nom_batiment, id_site, actif_batiment) VALUES (?, ?, ?)");
            $stmt->execute([$nom, $site_id, $actif]);
            $id = self::$db->lastInsertId();
        }
    
        return $id;
    }
    
    // contrôleur pour mettre à jour un bâtiment
    public static function updateBatiment($id, $nom)
    {
        self::initDb();
        $stmt = self::$db->prepare("UPDATE batiment SET nom_batiment = ? WHERE id_batiment = ?");
        $stmt->execute([$nom, $id]);
    }

    // contrôleur pour supprimer un bâtiment (hard delete)
    public static function deleteBatiment($id)
    {
        self::initDb();
        $stmt = self::$db->prepare("DELETE FROM batiment WHERE id_batiment = ?");
        $stmt->execute([$id]);
    }

    // contrôleur pour désactiver un bâtiment (soft delete)
    public static function softDeleteBatiment($id)
    {
        self::initDb();
        $stmt = self::$db->prepare("UPDATE batiment SET actif_batiment = false WHERE id_batiment = ?");
        $stmt->execute([$id]);
    }

    // contrôleur pour récupérer les bâtiments actifs en fonction de leurs sites
    public static function getActiveBatimentBySite($id_site)
    {
        self::initDb();
        $stmt = self::$db->prepare("SELECT * FROM batiment WHERE id_site = ? AND actif_batiment = true");
        $stmt->execute([$id_site]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // contrôleur pour récupérer tous les bâtiments en fonction de leurs sites
    public static function getAllBatimentBySite($id_site)
    {
        self::initDb();
        $stmt = self::$db->prepare("SELECT * FROM batiment WHERE id_site = ?");
        $stmt->execute([$id_site]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // contrôleur pour récuperer l'id du site en fonction de celui du batiment

    public static function getSiteIdByBatiment($id_batiment)
    {
        self::initDb();
        $stmt = self::$db->prepare("SELECT id_site FROM batiment WHERE id_batiment = ?");
        $stmt->execute([$id_batiment]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['id_site'] : null;
    }

    public static function reactivateBatiment($id)
    {
        self::initDb();
        $stmt = self::$db->prepare("UPDATE batiment SET actif_batiment = true WHERE id_batiment = ?");
        $stmt->execute([$id]);
    }
    public static function getActiveBatimentsWithSite()
    {
        self::initDb();
        $stmt = self::$db->query("SELECT b.*, s.nom_site FROM batiment b JOIN site s ON b.id_site = s.id_site WHERE b.actif_batiment = true AND s.actif_site = true");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAllBatimentsWithSite()
    {
        self::initDb();
        $stmt = self::$db->query("SELECT b.*, s.nom_site FROM batiment b JOIN site s ON b.id_site = s.id_site");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
