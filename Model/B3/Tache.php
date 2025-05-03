<?php

require_once 'db_connect.php';

class Tache
{
    private $tacheId = 0;
    private $demandeId = 0;

    // Constructeur de la classe Tache
    public function __construct($new_tacheId)
    {
        $this->tacheId = $new_tacheId;
    }

    // Getter pour l'ID de la tâche et setter pour l'ID de la tâche
    public function setDemandeId($new_demandeId)
    {
        $this->demandeId = $new_demandeId;
    }

    //fonction pour récupérer les informations d'une tâche les statuts et les médias
    public static function getAllStatuts()
    {
        // Connexion à la base de données
        $pdo = Database::getInstance()->getConnection();

        // Requête SQL pour récupérer tous les techniciens
        $sql = "SELECT Id_statut, nom_statut
            FROM statut";

        // Préparer la requête
        $stmt = $pdo->prepare($sql);

        // Exécution de la requête
        $stmt->execute();

        // Récupérer et retourner les résultats sous forme de tableau associatif
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fonction pour récupérer les informations d'une tâche par son ID
    public function getTasksDataByDemandeId()
    {
        // Connexion à la base de données
        $pdo = Database::getInstance()->getConnection();

        // Requête SQL pour récupérer le numero de ticket
        $sql = "SELECT 
                    d.num_ticket_dmd,
                    l.nom_lieu, 
                    b.nom_batiment
                FROM demande d
                INNER JOIN lieu l ON d.Id_lieu = l.Id_lieu
                INNER JOIN batiment b ON l.Id_batiment = b.Id_batiment
                WHERE d.Id_demande = :demandeId";

        // Préparation de la requête
        $stmt = $pdo->prepare($sql);

        // Lier l'ID de l'utilisateur à la requête SQL
        $stmt->bindParam(':demandeId', $this->demandeId, PDO::PARAM_INT);

        // Exécution de la requête
        $stmt->execute();

        // Récupérer le résultat sous forme de tableau associatif
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Fonction pour récupérer les media d'une tâche par son ID
    public function getMediasByTacheId()
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = "SELECT nom_media, url_media 
                FROM media 
                WHERE Id_tache = :tacheId";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':tacheId', $this->tacheId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fonction pour récupérer le statut d'une tâche par son ID
    public function getTaskStatutByTacheId()
    {
        // Connexion à la base de données
        $pdo = Database::getInstance()->getConnection();

        // Requête SQL pour récupérer le numero de ticket
        $sql = "SELECT s.Id_statut, s.nom_statut
                FROM historique h
                INNER JOIN statut s ON h.Id_statut = s.Id_statut
                WHERE h.Id_tache = :taskId
                ORDER BY h.date_modif DESC
                LIMIT 1";

        // Préparation de la requête
        $stmt = $pdo->prepare($sql);

        // Lier l'ID de la tache à la requête SQL
        $stmt->bindParam(':taskId', $this->tacheId, PDO::PARAM_INT);

        // Exécution de la requête
        $stmt->execute();

        // Récupérer le résultat sous forme de tableau associatif
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Fonction pour mettre à jour l'ordre d'une tâche
    public function updateOrder($order)
    {
        // Connexion à la base de données
        $pdo = Database::getInstance()->getConnection();

        // Préparation de la requête
        $stmt = $pdo->prepare("UPDATE tache SET ordre_tache = :order WHERE Id_tache = :id");
        
        // Exécution de la requête
        return $stmt->execute(['order' => $order, 'id' => $this->tacheId]);
    }
}
