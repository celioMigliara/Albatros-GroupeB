<?php

require_once 'db_connect.php';

class Technicien
{
    private $techId = 0;

    // Constructeur de la classe
    public function __construct($new_techId)
    {
        $this->techId = $new_techId;
    }

    // fonction pour verifier si le technicien existe
    public function exists()
    {
        $techniciens = self::getTechniciens(); // Récupérer tous les techniciens valides et actifs
    
        foreach ($techniciens as $technicien) {
            if (intval($technicien['Id_utilisateur']) === intval($this->techId)) {
                return true;
            }
        }
        
        return false;
    }
    
// fonction pour recuperer tous les techniciens valides et actifs
    public static function getTechniciens()
    {
        // Connexion à la base de données
        $pdo = Database::getInstance()->getConnection();

        // Requête SQL pour récupérer les techniciens (role = 2) qui sont valides et actifs
        $sql = "SELECT Id_utilisateur, nom_utilisateur, prenom_utilisateur, mail_utilisateur
            FROM utilisateur 
            WHERE Id_role = 2 AND valide_utilisateur = 1 AND actif_utilisateur = 1";

        // Préparer la requête
        $stmt = $pdo->prepare($sql);

        // Exécution de la requête
        $stmt->execute();

        // Récupérer et retourner les résultats sous forme de tableau associatif
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // fonction pour recuperer le nom et prenom du technicien
    public function getTechnicienName()
    {
        // Connexion à la base de données
        $pdo = Database::getInstance()->getConnection();

        // SQL pour récupérer le nom et le prénom du technicien
        $sql = "SELECT nom_utilisateur, prenom_utilisateur
                FROM utilisateur 
                WHERE Id_utilisateur = :userId AND Id_Role = 2 AND valide_utilisateur = 1 AND actif_utilisateur = 1";

        // Préparation de la requête
        $stmt = $pdo->prepare($sql);

        // Lier l'ID de l'utilisateur à la requête SQL
        $stmt->bindParam(':userId', $this->techId, PDO::PARAM_INT);

        // Exécution de la requête
        $stmt->execute();

        // Récupérer les résultats sous forme de tableau associatif
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getTachesForTechnicien($page_debut = 0, $page_fin = 10)
    {
        // Connexion à la base de données
        $pdo = Database::getInstance()->getConnection();
    
        // Requête SQL pour récupérer les tâches liées à un utilisateur (technicien) avec le nombre de médias associés
        $sql = "SELECT 
            t.Id_tache, 
            t.sujet_tache, 
            t.description_tache, 
            t.date_creation_tache, 
            t.date_planif_tache, 
            t.date_fin_tache, 
            t.commentaire_technicien_tache, 
            t.ordre_tache, 
            t.Id_demande 
            FROM tache t 
            WHERE t.Id_utilisateur = :userId 
            ORDER BY t.ordre_tache ASC LIMIT :page_fin OFFSET :page_debut";
    
        // Préparation de la requête
        $stmt = $pdo->prepare($sql);
    
        // Lier l'ID de l'utilisateur et les paramètres de pagination
        $stmt->bindParam(':userId', $this->techId, PDO::PARAM_INT);
        $stmt->bindParam(':page_fin', $page_fin, PDO::PARAM_INT);
        $stmt->bindParam(':page_debut', $page_debut, PDO::PARAM_INT);

        // Exécution de la requête
        $stmt->execute();
    
        // Récupérer les résultats sous forme de tableau associatif
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalTaches($onlyEnCours): int 
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = "SELECT COUNT(*) as total_count
                FROM tache t
                WHERE t.id_utilisateur = :userId ";

        // Si on ne veut que les tâches en cours (statut < 5)
        if ($onlyEnCours) {
            $sql .= " AND (
                        SELECT h.Id_statut
                        FROM historique h
                        WHERE h.id_tache = t.id_tache
                        ORDER BY h.date_modif DESC
                        LIMIT 1
                    ) < 5";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':userId', $this->techId, PDO::PARAM_INT);

        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }


    // fonction pour recuperer les taches en cours du technicien
    public function getTaches($onlyEnCours, $tasksOffset = 0, $tasksPerPage = 0)
    {
        // Connexion à la base de données
        $pdo = Database::getInstance()->getConnection();

        // On s'assure qu'on a bien au moins 0 dans la page début
        $tasksOffset = max($tasksOffset, 0);

        // On récupère les infos de la tache avec le bon userId et le dernier statut en date
        // qui est inférieur à 5. Cela permet de récupérer uniquement les taches "en cours"
        $sql = "SELECT  t.Id_tache, 
                        t.sujet_tache, 
                        t.description_tache, 
                        t.date_creation_tache,
                        t.date_planif_tache, 
                        t.date_fin_tache, 
                        t.commentaire_technicien_tache,
                        t.ordre_tache, 
                        t.Id_demande
                FROM tache t
                WHERE t.Id_utilisateur = :userId ";

        if ($onlyEnCours)
        {
            $sql .= " AND 
                    (
                        SELECT h.Id_statut
                        FROM historique h
                        WHERE h.Id_tache = t.Id_tache
                        ORDER BY h.date_modif DESC
                        LIMIT 1
                    ) < 5 ";
        }

        $sql .= " ORDER BY t.ordre_tache ASC ";
        
        // Ajouter les filtres de pagination, si on en a plus de 0, cela veut dire qu'on veut un nombre précis
        if ($tasksPerPage > 0)
        {
            $sql .= " LIMIT :tasks_per_page OFFSET :tasks_offset ";
        }

        // Préparation de la requête
        $stmt = $pdo->prepare($sql);

        // Lier l'ID de l'utilisateur à la requête SQL
        $stmt->bindParam(':userId', $this->techId, PDO::PARAM_INT);

        // Bind les paramètres si on les a ajoutés
        if ($tasksPerPage > 0)
        {
            $stmt->bindParam(':tasks_per_page', $tasksPerPage, PDO::PARAM_INT);
            $stmt->bindParam(':tasks_offset', $tasksOffset, PDO::PARAM_INT);
        }

        // Exécution de la requête
        $stmt->execute();

        // Récupérer les résultats sous forme de tableau associatif
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}


