<?php
require_once __DIR__ . '/../ModeleDBB2.php';

//use PDO;

class Lieu
{
    private static $db;

    // Initialiser la connexion à la base de données si ce n’est pas déjà fait
    private static function initDb()
    {
        if (self::$db === null) {
            self::$db = Database::getInstance()->getConnection();
        }
    }

    // Récupérer tous les lieux
    public static function getAllLieu()
    {
        self::initDb();
        $query = self::$db->query("SELECT * FROM lieu");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer uniquement les lieux actifs
    public static function getActiveLieu()
    {
        self::initDb();
        $query = self::$db->query("SELECT * FROM lieu WHERE actif_lieu = true");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer un lieu par son ID
    public static function getLieuById($id)
    {
        self::initDb();
        $stmt = self::$db->prepare("
        SELECT l.*, b.actif_batiment, s.id_site, s.actif_site 
        FROM lieu AS l
        JOIN batiment AS b ON b.id_batiment = l.id_batiment
        JOIN site AS s ON s.id_site = b.id_site
        WHERE l.id_lieu = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Ajouter un nouveau lieu
    public static function addLieu($nom, $actif, $id_batiment)
    {
        self::initDb();
        $actif = (int) $actif;
        $stmt = self::$db->prepare("INSERT INTO lieu (nom_lieu, actif_lieu, id_batiment) VALUES (?, ?, ?)");
        return $stmt->execute([$nom, $actif, $id_batiment]);
    }

    // Mettre à jour le nom d’un lieu existant
    public static function updateLieu($id, $nom)
    {
        self::initDb();
        $stmt = self::$db->prepare("UPDATE lieu SET nom_lieu = ? WHERE id_lieu = ?");
        $stmt->execute([$nom, $id]);
    }

    // Supprimer définitivement un lieu (hard delete)
    public static function deleteLieu($id)
    {
        self::initDb();
        $stmt = self::$db->prepare("DELETE FROM lieu WHERE id_lieu = ?");
        $stmt->execute([$id]);
    }

    // Désactiver un lieu (soft delete)
    public static function softDeleteLieu($id)
    {
        self::initDb();
        $stmt = self::$db->prepare("UPDATE lieu SET actif_lieu = false WHERE id_lieu = ?");
        $stmt->execute([$id]);
    }

    // Récupérer les lieux actifs d’un bâtiment donné
    public static function getActiveLieuByBatiment($id_batiment)
    {
        self::initDb();
        $stmt = self::$db->prepare("SELECT * FROM lieu WHERE id_batiment = ? AND actif_lieu = true");
        $stmt->execute([$id_batiment]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer tous les lieux (actifs ou non) d’un bâtiment donné
    public static function getAllLieuByBatiment($id_batiment)
    {
        self::initDb();
        $stmt = self::$db->prepare("        SELECT  l.*,
                b.nom_batiment                                                     AS nom_batiment,
                s.nom_site                                                         AS nom_site,
                CASE
                    WHEN b.actif_batiment = 0          -- ou = FALSE
                    OR s.actif_site     = 0          -- ou = FALSE
                    THEN 0                             -- renvoyé comme FALSE
                    ELSE s.actif_site                  -- valeur réelle sinon
                END AS actif_lieu

        FROM    lieu      AS l
        JOIN    batiment  AS b ON b.id_batiment = l.id_batiment
        JOIN    site      AS s ON s.id_site     = b.id_site
		WHERE l.id_batiment = ?;");
        $stmt->execute([$id_batiment]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer l’ID du bâtiment auquel appartient un lieu
    public static function getBatimentIdByLieu($id_lieu)
    {
        self::initDb();
        $stmt = self::$db->prepare("SELECT id_batiment FROM lieu WHERE id_lieu = ?");
        $stmt->execute([$id_lieu]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['id_batiment'] : null;
    }

    public static function reactivateLieu($id)
    {
    self::initDb();
    $stmt = self::$db->prepare("UPDATE lieu SET actif_lieu = true WHERE id_lieu = ?");
    $stmt->execute([$id]);
    }

public static function getAllLieuxWithBatiment(): array
{
    self::initDb();

    $sql = "
        SELECT  l.*,
                b.nom_batiment                                                     AS nom_batiment,
                s.nom_site                                                         AS nom_site,
                CASE
                    WHEN b.actif_batiment = 0          -- ou = FALSE
                    OR s.actif_site     = 0          -- ou = FALSE
                    THEN 0                             -- renvoyé comme FALSE
                    ELSE s.actif_site                  -- valeur réelle sinon
                END AS actif_lieu

        FROM    lieu      AS l
        JOIN    batiment  AS b ON b.id_batiment = l.id_batiment
        JOIN    site      AS s ON s.id_site     = b.id_site;
    ";

    return self::$db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

public static function getActiveLieuxWithBatiment(): array
{
    self::initDb();

    $sql = "
        SELECT  l.*,
                b.nom_batiment                         AS nom_batiment,
                s.nom_site                             AS nom_site
        FROM    lieu l
        JOIN    batiment b ON l.id_batiment = b.id_batiment
        JOIN    site     s ON b.id_site     = s.id_site
        WHERE   l.actif_lieu      = TRUE
        AND     b.actif_batiment = TRUE
        AND     s.actif_site     = TRUE
    ";

    return self::$db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

    

}
