<?php

require_once 'db_connect.php';
require_once 'User.php';

class UserProfile extends UserB3
{
    public function __construct($newUserId)
    {
        $this->setUserId($newUserId);
    }
    
    public function changeProfile($champsAChanger, $params)
    {
        // Quitter si on a ni paramètres ni champs à changer
        if (empty($champsAChanger) || empty($params)) {
            return false;
        }

        // Connexion à la base de données
        $pdo = Database::getInstance()->getConnection();

        // Préparation du SQL avec le format UPDATE dynamique et safe 
        $sql = "UPDATE utilisateur SET " . implode(', ', $champsAChanger) . " WHERE Id_utilisateur = :id";
        $stmt = $pdo->prepare($sql);

        // On lie le paramètre manquant qui est l'user ID
        $params[':id'] = $this->getUserId();

        // On retourne si l'execution est bien passé ou pas
        return $stmt->execute($params);
    }
    
    public function getUserData(): array 
    {
        // Connexion à la base de données
        $pdo = Database::getInstance()->getConnection();
        
        // Requête SQL pour récupérer les informations de l'utilisateur
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ? AND valide_utilisateur = 1 AND actif_utilisateur = 1");
        
        // Exécution de la requête SQL
        $stmt->execute([$this->userId]);

        // Retour du résultat
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
