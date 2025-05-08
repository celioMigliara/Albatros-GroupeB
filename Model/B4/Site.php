<?php

require_once __DIR__ . '/../ModeleDBB2.php';

//use PDO;

class Site
{
    private static $db;

    private static function initDb()
    {
        if (self::$db === null) {
            self::$db = Database::getInstance()->getConnection();
        }
    }
    // contrôleur pour récupérer tous les sites
    public static function getAllSites()
    {
        self::initDb();
        $query = self::$db->query("SELECT * FROM site");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // contrôleur pour récupérer les sites actifs
    public static function getActiveSites()
    {
        self::initDb();
        $query = self::$db->query("SELECT * FROM site WHERE actif_site = true");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // contrôleur pour récupérer un site par son ID
    public static function getSiteById($id)
    {
        self::initDb();
        $stmt = self::$db->prepare("SELECT * FROM site WHERE id_site = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // contrôleur pour ajouter un site
    public static function addSite($nom, $actif) {
        self::initDb();
        $stmt = self::$db->prepare("SELECT id_site FROM site WHERE nom_site = ?");
        $stmt->execute([$nom]);
        $id = $stmt->fetchColumn();
    
        if (!$id) {
            $stmt = self::$db->prepare("INSERT INTO site (nom_site, actif_site) VALUES (?,?)");
            $stmt->execute([$nom,$actif]);
            $id = self::$db->lastInsertId();
        }
    
        return $id;
    }

    // contrôleur pour mettre à jour un site
    public static function updateSite($id, $nom)
    {
        self::initDb();
        $stmt = self::$db->prepare("UPDATE site SET nom_site = ? WHERE id_site = ?");
        $stmt->execute([$nom, $id]);
    }

    // contrôleur pour supprimer un site (hard delete)
    public static function deleteSite($id)
    {
        self::initDb();
        $stmt = self::$db->prepare("DELETE FROM site WHERE id_site = ?");
        $stmt->execute([$id]);
    }

    // contrôleur pour désactiver un site (soft delete)
    public static function softDeleteSite($id)
    {
        self::initDb();
        $stmt = self::$db->prepare("UPDATE site SET actif_site = false WHERE id_site = ?");
        $stmt->execute([$id]);
    }

    public static function reacticateSite($id)
    {
        self::initDb();
        $stmt = self::$db->prepare("UPDATE site SET actif_site = true WHERE id_site = ?");
        $stmt->execute([$id]);
    }
}
