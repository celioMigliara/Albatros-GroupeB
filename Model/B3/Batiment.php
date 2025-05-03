<?php

require_once 'db_connect.php';

class BatimentB3
{
    private $userId;
    private $batiments;

    // Constructeur
    public function __construct($new_batiments)
    {
        $this->batiments = $new_batiments;
    }

    // Getters et Setters
    public function setUserId($new_userId)
    {
        $this->userId = $new_userId;
    }

    public static function getBatiments()
    {
        $db = Database::getInstance();
        $connexion = $db->getConnection();

        $query = "SELECT Id_batiment, nom_batiment FROM batiment WHERE actif_batiment = 1";
        $stmt = $connexion->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fonction pour récupérer les bâtiments d'un utilisateur
    public function validateBatiments()
    {
        $batiments = $this->batiments;
        if (!is_array($batiments)) {
            return false;
        }

        $allBatiments = self::getBatiments();
        $validBatimentIds = array_column($allBatiments, 'Id_batiment');

        foreach ($batiments as $batimentId) {
            if (!in_array($batimentId, $validBatimentIds)) {
                return false;
            }
        }

        return true;
    }

    // Fonction pour insérer les bâtiments d'un utilisateur
    public function insertBatimentsUtilisateur()
    {
        $pdo = Database::getInstance()->getConnection();
        $userId = $this->userId;
        $batiments = $this->batiments;

        if (!$pdo || !$userId || empty($batiments))
            return false;

        // Si $batiments est une chaîne, la convertir en tableau
        if (is_string($batiments)) {
            $batiments = explode(',', $batiments);
        }

        $requete = $pdo->prepare("INSERT INTO travaille (Id_utilisateur, Id_batiment) VALUES (?, ?)");

        // Si jamais une requete ne passe pas, on retourne faux
        $retour = true;
        foreach ($batiments as $batimentId) {
            $retour &= $requete->execute([$userId, trim($batimentId)]); // trim() pour enlever les espaces
        }

        return $retour;
    }
}
