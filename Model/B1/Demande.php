<?php
// Model/Demande.php

require_once __DIR__ . '/../ModeleDBB2.php';
class Demande {
   
    public static function getTotalDemandes() {
        $pdo = Database::getInstance()->getConnection(); 
        $query = "SELECT COUNT(*) AS total FROM demande";
        $stmt = $pdo->query($query);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public static function getById($id) {
        $pdo = Database::getInstance()->getConnection(); 
        $stmt = $pdo->prepare("
            SELECT d.*, 
                   s.nom_statut, 
                   u.nom_utilisateur, 
                   u.prenom_utilisateur, 
                   l.nom_lieu, 
                   b.nom_batiment, 
                   b.id_batiment, 
                   si.nom_site, 
                   si.id_site, 
                   COUNT(m.id_media) AS nombre_pieces_jointes
            FROM demande d
            JOIN est e ON d.id_demande = e.id_demande
            JOIN statut s ON e.id_statut = s.id_statut
            JOIN utilisateur u ON d.id_utilisateur = u.id_utilisateur
            JOIN lieu l ON d.id_lieu = l.id_lieu
            JOIN batiment b ON l.id_batiment = b.id_batiment
            JOIN site si ON b.id_site = si.id_site
            LEFT JOIN media m ON d.id_demande = m.id_demande
            WHERE d.id_demande = :id
            GROUP BY d.id_demande
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

public static function updateCommentaireAdmin($id, $commentaire) {
    $db = Database::getInstance()->getConnection(); 
    $stmt = $db->prepare("UPDATE demande SET commentaire_admin_dmd = ? WHERE id_demande = ?");
    $stmt->execute([$commentaire, $id]);
}

public static function refuserDemandes($id, $nouveauStatut) {
    $db = Database::getInstance()->getConnection(); 

    $stmt = $db->prepare("SELECT e.id_statut FROM est e WHERE e.id_demande = ?");
    $stmt->execute([$id]);
    $statutActuel = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$statutActuel || $statutActuel['id_statut'] != 1) {
        return false;
    }

    $stmt = $db->prepare("UPDATE est SET id_statut = ? WHERE id_demande = ?");
    return $stmt->execute([$nouveauStatut, $id]);
}

public static function getFilteredWithPagination($filters, $offset, $limit) {
    $pdo = Database::getInstance()->getConnection(); 

    // Début de la requête SQL
    $query = "
        SELECT d.*, 
               s.nom_statut, 
               u.nom_utilisateur, 
               u.prenom_utilisateur, 
               l.nom_lieu, 
               b.nom_batiment, 
               si.nom_site, 
               COUNT(m.id_media) AS nombre_pieces_jointes
        FROM demande d
        JOIN est e ON d.id_demande = e.id_demande
        JOIN statut s ON e.id_statut = s.id_statut
        JOIN utilisateur u ON d.id_utilisateur = u.id_utilisateur
        JOIN lieu l ON d.id_lieu = l.id_lieu
        JOIN batiment b ON l.id_batiment = b.id_batiment
        JOIN site si ON b.id_site = si.id_site
        LEFT JOIN media m ON d.id_demande = m.id_demande
        WHERE 1=1
    ";

    $params = [];

     if(empty($filters['statut']) || $filters['statut'] != 6) {
        $query .= " AND s.id_statut != 6"; // Exclure les demandes annulées 
     }  

    // Ajout du filtre par statut
    if (!empty($filters['statut'])) {
        $query .= " AND s.id_statut = :statut";
        $params[':statut'] = $filters['statut'];
    }

    // Ajout du filtre par bâtiment
    if (!empty($filters['batiment'])) {
        $query .= " AND b.id_batiment = :batiment";
        $params[':batiment'] = $filters['batiment'];
    }

    // Ajout du filtre par mots-clés
    if (!empty($filters['keywords'])) {
        $query .= " AND (
            d.sujet_dmd LIKE :keywords 
            OR d.description_dmd LIKE :keywords
            OR u.nom_utilisateur LIKE :keywords
            OR u.prenom_utilisateur LIKE :keywords
            OR l.nom_lieu LIKE :keywords
            OR b.nom_batiment LIKE :keywords
            OR si.nom_site LIKE :keywords
        )";
        $params[':keywords'] = '%' . $filters['keywords'] . '%';
    }

    // Ajout du tri
    $order = $filters['tri'] === 'asc' ? 'ASC' : 'DESC';
    $query .= " GROUP BY d.id_demande ORDER BY d.date_creation_dmd $order LIMIT :offset, :limit";

    // Préparation de la requête
    $stmt = $pdo->prepare($query);

    // Liaison des paramètres
    if (!empty($filters['statut'])) {
        $stmt->bindValue(':statut', $params[':statut'], PDO::PARAM_INT);
    }
    if (!empty($filters['batiment'])) {
        $stmt->bindValue(':batiment', $params[':batiment'], PDO::PARAM_INT);
    }
    if (!empty($filters['keywords'])) {
        $stmt->bindValue(':keywords', $params[':keywords'], PDO::PARAM_STR);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

    // Exécution de la requête
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public static function getTotalFiltered($filters) {
    $pdo = Database::getInstance()->getConnection(); 

    $query = "
        SELECT COUNT(DISTINCT d.id_demande) AS total
        FROM demande d
        JOIN est e ON d.id_demande = e.id_demande
        JOIN statut s ON e.id_statut = s.id_statut
        JOIN utilisateur u ON d.id_utilisateur = u.id_utilisateur
        JOIN lieu l ON d.id_lieu = l.id_lieu
        JOIN batiment b ON l.id_batiment = b.id_batiment
        JOIN site si ON b.id_site = si.id_site
        LEFT JOIN media m ON d.id_demande = m.id_demande
        WHERE 1=1
    ";

    // Ajout des filtres dynamiques
    if (!empty($filters['keywords'])) {
        $query .= " AND (d.sujet_dmd LIKE :keywords OR d.description_dmd LIKE :keywords)";
    }
    if (!empty($filters['statut'])) {
        $query .= " AND s.nom_statut = :statut";
    }
    if (!empty($filters['site'])) {
        $query .= " AND si.nom_site = :site";
    }
    if (!empty($filters['batiment'])) {
        $query .= " AND b.nom_batiment = :batiment";
    }
    if (!empty($filters['date_debut'])) {
        $query .= " AND d.date_creation_dmd >= :date_debut";
    }
    if (!empty($filters['date_fin'])) {
        $query .= " AND d.date_creation_dmd <= :date_fin";
    }

    $stmt = $pdo->prepare($query);

    // Liaison des paramètres
    if (!empty($filters['keywords'])) {
        $stmt->bindValue(':keywords', '%' . $filters['keywords'] . '%', PDO::PARAM_STR);
    }
    if (!empty($filters['statut'])) {
        $stmt->bindValue(':statut', $filters['statut'], PDO::PARAM_STR);
    }
    if (!empty($filters['site'])) {
        $stmt->bindValue(':site', $filters['site'], PDO::PARAM_STR);
    }
    if (!empty($filters['batiment'])) {
        $stmt->bindValue(':batiment', $filters['batiment'], PDO::PARAM_STR);
    }
    if (!empty($filters['date_debut'])) {
        $stmt->bindValue(':date_debut', $filters['date_debut'], PDO::PARAM_STR);
    }
    if (!empty($filters['date_fin'])) {
        $stmt->bindValue(':date_fin', $filters['date_fin'], PDO::PARAM_STR);
    }

    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

public static function getByUserAndBuilding($userId, $filters, $offset, $limit) {
    $pdo = Database::getInstance()->getConnection(); 
    $query = "
        SELECT d.*, 
               s.nom_statut, 
               u.nom_utilisateur, 
               u.prenom_utilisateur, 
               l.nom_lieu, 
               b.nom_batiment, 
               si.nom_site, 
               COUNT(m.id_media) AS nombre_pieces_jointes
        FROM demande d
        JOIN utilisateur u ON d.id_utilisateur = u.id_utilisateur
        JOIN lieu l ON d.id_lieu = l.id_lieu
        JOIN batiment b ON l.id_batiment = b.id_batiment
        JOIN site si ON b.id_site = si.id_site
        LEFT JOIN media m ON d.id_demande = m.id_demande
        JOIN est e ON d.id_demande = e.id_demande
        JOIN statut s ON e.id_statut = s.id_statut
        WHERE (d.id_utilisateur = :userId OR b.id_batiment IN (
            SELECT id_batiment FROM travaille WHERE id_utilisateur = :userId
        ))
    ";

    // Ajout des filtres dynamiques
    if (!empty($filters['keywords'])) {
        $query .= " AND (d.sujet_dmd LIKE :keywords OR d.description_dmd LIKE :keywords)";
    }
    if (!empty($filters['statut'])) {
        $query .= " AND s.id_statut = :statut";
    }
    if (!empty($filters['site'])) {
        $query .= " AND si.id_site = :site";
    }
    if (!empty($filters['batiment'])) {
        $query .= " AND b.id_batiment = :batiment";
    }
    if (!empty($filters['date_debut'])) {
        $query .= " AND d.date_creation_dmd >= :date_debut";
    }
    if (!empty($filters['date_fin'])) {
        $query .= " AND d.date_creation_dmd <= :date_fin";
    }

    $query .= " GROUP BY d.id_demande ORDER BY d.date_creation_dmd " . ($filters['tri'] === 'asc' ? 'ASC' : 'DESC');
    $query .= " LIMIT :offset, :limit";

    $stmt = $pdo->prepare($query);

    // Lier les paramètres
    $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
    if (!empty($filters['keywords'])) {
        $stmt->bindValue(':keywords', '%' . $filters['keywords'] . '%', PDO::PARAM_STR);
    }
    if (!empty($filters['statut'])) {
        $stmt->bindValue(':statut', $filters['statut'], PDO::PARAM_INT);
    }
    if (!empty($filters['site'])) {
        $stmt->bindValue(':site', $filters['site'], PDO::PARAM_INT);
    }
    if (!empty($filters['batiment'])) {
        $stmt->bindValue(':batiment', $filters['batiment'], PDO::PARAM_INT);
    }
    if (!empty($filters['date_debut'])) {
        $stmt->bindValue(':date_debut', $filters['date_debut'], PDO::PARAM_STR);
    }
    if (!empty($filters['date_fin'])) {
        $stmt->bindValue(':date_fin', $filters['date_fin'], PDO::PARAM_STR);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public static function getTotalByUserAndBuilding($userId, $filters) {
    $pdo = Database::getInstance()->getConnection(); 

    $query = "
        SELECT COUNT(*) AS total
        FROM demande d
        JOIN utilisateur u ON d.id_utilisateur = u.id_utilisateur
        JOIN lieu l ON d.id_lieu = l.id_lieu
        JOIN batiment b ON l.id_batiment = b.id_batiment
        JOIN est e ON d.id_demande = e.id_demande  -- Joindre la table est pour récupérer id_statut
        JOIN statut s ON e.id_statut = s.id_statut  -- Joindre la table statut pour récupérer le statut
        WHERE (d.id_utilisateur = :userId OR b.id_batiment IN (
            SELECT id_batiment FROM travaille WHERE id_utilisateur = :userId
        ))
    ";

    // Ajouter les filtres dynamiques
    if (!empty($filters['keywords'])) {
        $query .= " AND (d.sujet_dmd LIKE :keywords OR d.description_dmd LIKE :keywords)";
    }
    if (!empty($filters['statut'])) {
        $query .= " AND s.id_statut = :statut";  // Utiliser s.id_statut ici, pas d.id_statut
    }
    if (!empty($filters['site'])) {
        $query .= " AND d.id_site = :site";  // Si vous avez cette colonne dans demande
    }
    if (!empty($filters['batiment'])) {
        $query .= " AND b.id_batiment = :batiment"; // Utilisez b.id_batiment pour le filtre par bâtiment
    }
    if (!empty($filters['date_debut'])) {
        $query .= " AND d.date_creation_dmd >= :date_debut";
    }
    if (!empty($filters['date_fin'])) {
        $query .= " AND d.date_creation_dmd <= :date_fin";
    }

    $stmt = $pdo->prepare($query);

    // Lier les paramètres
    $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
    if (!empty($filters['keywords'])) {
        $stmt->bindValue(':keywords', '%' . $filters['keywords'] . '%', PDO::PARAM_STR);
    }
    if (!empty($filters['statut'])) {
        $stmt->bindValue(':statut', $filters['statut'], PDO::PARAM_INT);
    }
    if (!empty($filters['site'])) {
        $stmt->bindValue(':site', $filters['site'], PDO::PARAM_INT);
    }
    if (!empty($filters['batiment'])) {
        $stmt->bindValue(':batiment', $filters['batiment'], PDO::PARAM_INT);
    }
    if (!empty($filters['date_debut'])) {
        $stmt->bindValue(':date_debut', $filters['date_debut'], PDO::PARAM_STR);
    }
    if (!empty($filters['date_fin'])) {
        $stmt->bindValue(':date_fin', $filters['date_fin'], PDO::PARAM_STR);
    }

    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}


public static function updateDemande($id, $data) {
    $pdo = Database::getInstance()->getConnection(); 

    $stmt = $pdo->prepare("
        UPDATE demande
        SET description_dmd = :description_dmd,
            id_lieu = (SELECT id_lieu FROM lieu WHERE nom_lieu = :nom_lieu LIMIT 1)
        WHERE id_demande = :id
    ");
    $stmt->execute([
        ':description_dmd' => $data['description_dmd'],
        ':nom_lieu' => $data['nom_lieu'],
        ':id' => $id,
    ]);
}

public static function getImagesByDemandeId($idDemande) {
    $pdo = Database::getInstance()->getConnection(); 
    $stmt = $pdo->prepare("
        SELECT url_media
        FROM media 
        WHERE id_demande = :id_demande
    ");
    $stmt->execute([':id_demande' => $idDemande]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public static function updateStatut($idDemande, $nouveauStatut) {
    $pdo = Database::getInstance()->getConnection(); 
    $stmt = $pdo->prepare("UPDATE est SET id_statut = :statut WHERE id_demande = :id");
    return $stmt->execute([
        ':statut' => $nouveauStatut,
        ':id' => $idDemande,
    ]);
  
}

public static function getByIdforMail($idDemande) {
    $pdo = Database::getInstance()->getConnection(); 
    try {
        $stmt = $pdo->prepare("
            SELECT d.*, u.id_utilisateur, u.nom_utilisateur, u.prenom_utilisateur, u.mail_utilisateur
            FROM demande d
            JOIN utilisateur u ON d.id_utilisateur = u.id_utilisateur
            WHERE d.id_demande = :id
        ");
        $stmt->execute([':id' => $idDemande]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    } catch (PDOException $e) {
        error_log("Erreur SQL dans getByIdforMail: " . $e->getMessage());
        return false;
    }
}

// Nouvelle méthode pour recalculer le statut de la demande
public static function recalcDemandStatus($id_demande) {
    $pdo = Database::getInstance()->getConnection(); 

    // Récupérer les statuts des tâches associées à la demande
    $stmt = $pdo->prepare("
        SELECT h.id_statut
        FROM tache t
        JOIN historique h ON t.id_tache = h.id_tache
        WHERE t.id_demande = :id_demande
        ORDER BY h.id_statut ASC -- Priorité croissante
    ");
    $stmt->execute([':id_demande' => $id_demande]);
    $statuts = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Si aucune tâche n'est associée, ne rien faire
    if (empty($statuts)) {
        return;
    }

    // Vérifier les cas spécifiques
    $statuts = array_unique($statuts); // Éliminer les doublons

    if (in_array(5, $statuts) && count($statuts) === 1) {
        // Cas 3 : Toutes les tâches sont "Terminé"
        $newStatusId = 5; // "Terminé"
    } elseif (in_array(6, $statuts) && count($statuts) === 1) {
        // Cas 3 : Toutes les tâches sont "Annulé"
        $newStatusId = 6; // "Annulé"
    } elseif (in_array(4, $statuts)) {
        // Cas 2 : Une tâche est "En commande"
        $newStatusId = 4; // "En commande"
    } elseif (in_array(2, $statuts)) {
        // Cas 1 : Toutes les tâches sont "Nouvelle" ou "Planifiée"
        $newStatusId = 2; // "Planifiée"
    } else {
        // Par défaut, mettre à jour avec le statut ayant la plus haute priorité
        $newStatusId = min($statuts);
    }

    // Mettre à jour le statut de la demande
    $stmt = $pdo->prepare("
        UPDATE est
        SET id_statut = :newStatusId, date_modif_dmd = NOW()
        WHERE id_demande = :id_demande
    ");
    $stmt->execute([
        ':newStatusId' => $newStatusId,
        ':id_demande' => $id_demande,
    ]);
}

public static function getDemandesByUser($userId, $offset = 0, $limit = 10) {
    $pdo = Database::getInstance()->getConnection(); 
    $stmt = $pdo->prepare("
        SELECT d.*, s.nom_statut
        FROM demande d
        JOIN est e ON d.id_demande = e.id_demande
        JOIN statut s ON e.id_statut = s.id_statut
        WHERE d.id_utilisateur = :userId
        ORDER BY d.date_creation_dmd DESC
        LIMIT :offset, :limit
    ");
    $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


}

