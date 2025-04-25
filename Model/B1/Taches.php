<?php 
require_once __DIR__ . '/../ModeleDBB2.php';
class Taches {

    
    public static function getTachesFromDemande($idDemande) {
        $pdo = Database::getInstance()->getConnection(); 
    
        // Requête SQL avec date_fin_tache réintégrée
        $stmt = $pdo->prepare("
            SELECT 
                t.Id_tache, 
                t.sujet_tache AS titre_tache, 
                t.description_tache, 
                t.date_creation_tache, 
                t.date_planif_tache, 
                t.date_fin_tache,
                t.commentaire_technicien_tache, 
                COALESCE(s.nom_statut, 'Non défini') AS nom_statut
            FROM tache t
            LEFT JOIN historique h ON t.Id_tache = h.Id_tache
            LEFT JOIN statut s ON h.Id_statut = s.Id_statut
            WHERE t.Id_demande = :idDemande
            ORDER BY t.date_creation_tache ASC
        ");
    
        // Exécution de la requête avec le paramètre
        $stmt->execute(['idDemande' => $idDemande]);
    
        // Retourne les résultats sous forme de tableau associatif
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function createTask($data) {
        $pdo = Database::getInstance()->getConnection(); 
    
        // Insérer la tâche dans la table `tache`
        $query = "
            INSERT INTO tache (
                sujet_tache, 
                id_utilisateur, 
                date_creation_tache, 
                date_planif_tache, 
                date_fin_tache,
                id_demande, 
                description_tache
            )
            VALUES (
                :nom_tache, 
                :technicien, 
                :date, 
                :date_planif_tache,
                :date_fin_tache,
                :id_demande, 
                :description
            )
        ";
    
        $stmt = $pdo->prepare($query);
    
        // Si aucun technicien n'est sélectionné, insérer NULL pour id_utilisateur
        $technicien = !empty($data['technicien']) ? $data['technicien'] : null;
        
        // Utiliser la date planifiée fournie ou par défaut utiliser date + 7 jours
        $datePlanif = !empty($data['date_planif_tache']) ? $data['date_planif_tache'] : date('Y-m-d', strtotime('+7 days'));
        
        // Date de fin si fournie, sinon NULL
        $dateFin = !empty($data['date_fin_tache']) ? $data['date_fin_tache'] : null;
    
        $success = $stmt->execute([
            ':nom_tache' => $data['nom_tache'],
            ':technicien' => $technicien, // Peut être NULL
            ':date' => $data['date'],
            ':date_planif_tache' => $datePlanif,
            ':date_fin_tache' => $dateFin,
            ':id_demande' => $data['id_demande'],
            ':description' => $data['description'],
        ]);
    
        if ($success) {
            // Récupérer l'ID de la tâche insérée
            $idTache = $pdo->lastInsertId();
    
            // Insérer le statut initial dans la table `historique`
            $queryHistorique = "
                INSERT INTO historique (id_tache, id_statut, date_modif)
                VALUES (:id_tache, :id_statut, NOW())
            ";
    
            $stmtHistorique = $pdo->prepare($queryHistorique);
    
            // Utiliser un statut par défaut (par exemple, "Nouvelle" avec id_statut = 1)
            $statutInitial = !empty($data['statut']) ? $data['statut'] : 1;
    
            $stmtHistorique->execute([
                ':id_tache' => $idTache,
                ':id_statut' => $statutInitial,
            ]);
        }
    
        return $success;
    }

    
    public static function getTacheById($idTache) {
        $pdo = Database::getInstance()->getConnection(); 
        $stmt = $pdo->prepare("
            SELECT t.*, 
                   h.id_statut, 
                   d.id_lieu, 
                   l.id_batiment, 
                   b.id_site, 
                   l.nom_lieu, 
                   b.nom_batiment, 
                   si.nom_site
            FROM tache t
            LEFT JOIN historique h ON t.id_tache = h.id_tache
            LEFT JOIN demande d ON t.id_demande = d.id_demande
            LEFT JOIN lieu l ON d.id_lieu = l.id_lieu
            LEFT JOIN batiment b ON l.id_batiment = b.id_batiment
            LEFT JOIN site si ON b.id_site = si.id_site
            WHERE t.id_tache = :id_tache
        ");
        $stmt->execute([':id_tache' => $idTache]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function updateTask($data) {
        $pdo = Database::getInstance()->getConnection(); 
    
        // Mettre à jour les informations de la tâche
        $stmt = $pdo->prepare("
            UPDATE tache
            SET sujet_tache = :nom_tache,
                id_utilisateur = :technicien,
                date_planif_tache = :date_planif_tache,
                date_fin_tache = :date_fin_tache,
                commentaire_technicien_tache = :commentaire_technicien
            WHERE id_tache = :id_tache
        ");
        $taskUpdated = $stmt->execute([
            ':nom_tache' => $data['nom_tache'],
            ':technicien' => $data['technicien'],
            ':date_planif_tache' => $data['date_planif_tache'],
            ':date_fin_tache' => isset($data['date_fin_tache']) ? $data['date_fin_tache'] : null,
            ':commentaire_technicien' => $data['commentaire_technicien'],
            ':id_tache' => $data['id_tache'],
        ]);
    
        // Mettre à jour le statut dans la table `historique`
        $stmtHistorique = $pdo->prepare("
            UPDATE historique
            SET id_statut = :id_statut, date_modif = NOW()
            WHERE id_tache = :id_tache
        ");
        $statusUpdated = $stmtHistorique->execute([
            ':id_tache' => $data['id_tache'],
            ':id_statut' => $data['statut'],
        ]);
    
        return $taskUpdated && $statusUpdated;
    }

    public static function getTaskIdByUniqueData($data) {
        $db = Database::getInstance()->getConnection(); 
        $stmt = $db->prepare("
            SELECT id_tache 
            FROM tache 
            WHERE sujet_tache = :nom_tache 
              AND date_creation_tache = :date_creation_tache 
              AND id_demande = :id_demande
            LIMIT 1
        ");
        $stmt->execute([
            ':nom_tache' => $data['nom_tache'],
            ':date_creation_tache' => $data['date'], // Utilisez la colonne correcte
            ':id_demande' => $data['id_demande'],
        ]);
        return $stmt->fetchColumn(); // Retourne l'ID de la tâche
    }
    public static function isTacheTerminee($id_demande) {
        $pdo = Database::getInstance()->getConnection(); 
        $stmt = $pdo->prepare("
            SELECT COUNT(*) AS total,
                   SUM(CASE 
                         WHEN h.id_statut = 5 THEN 1 
                         ELSE 0 
                       END) AS termine
            FROM tache t
            JOIN historique h ON t.id_tache = h.id_tache
            WHERE t.id_demande = :id_demande
        ");
        $stmt->execute([':id_demande' => $id_demande]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return ($result['total'] > 0 && $result['total'] == $result['termine']);
    }
    
    public static function getTasksByTechnicien($technicienId) {
        $pdo = Database::getInstance()->getConnection(); 
        $stmt = $pdo->prepare("
            SELECT t.*, h.id_statut, s.nom_statut, t.date_creation_tache, t.sujet_tache, t.description_tache
            FROM tache t
            JOIN historique h ON t.id_tache = h.id_tache
            JOIN statut s ON h.id_statut = s.id_statut
            WHERE t.id_utilisateur = :technicienId
            ORDER BY t.date_creation_tache DESC
        ");
        $stmt->execute([':technicienId' => $technicienId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
}