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

        // Requête SQL pour récupérer le numero de ticket, le lieu, batiment et site d'une tache
        $sql = "SELECT 
            d.num_ticket_dmd,
            l.nom_lieu, 
            b.nom_batiment,
            s.nom_site
        FROM demande d
        INNER JOIN lieu l ON d.Id_lieu = l.Id_lieu
        INNER JOIN batiment b ON l.Id_batiment = b.Id_batiment
        INNER JOIN site s ON b.Id_site = s.Id_site
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
        $sql = "SELECT s.id_statut, s.nom_statut
                FROM historique h
                INNER JOIN statut s ON h.id_statut = s.id_statut
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

    public static function updateLinearOrder($start, $end)
    {
        // Connexion à la base de données
        $pdo = Database::getInstance()->getConnection();

        // Démarrer la transaction pour garantir la cohérence des données
        $pdo->beginTransaction();

        // On vérifie que les deux ordres existent et on récupère aussi l'id de la tâche
        // On sélectionne "ordre_tache" en premier pour qu'il devienne la clé, et "id_tache" ensuite
        $stmt = $pdo->prepare("SELECT ordre_tache, id_tache FROM tache WHERE ordre_tache IN (?, ?)");
        $stmt->execute([$start, $end]);
        $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // => [ordre_tache => id_tache]

        // Vérifier que les deux ordres existent bien dans la base
        if (!isset($results[$start]) || !isset($results[$end])) {
            $pdo->rollBack();
            return false;
        }

        // Récupérer l'id de la tâche se trouvant à l'ordre $start (on profite de la syntaxe du key-pair)
        $startId = $results[$start];

        // Mise à jour des autres tâches pour faire de la place pour le déplacement
        if ($start > $end) {
            // Pour un déplacement vers le haut (ex: ordre 5 -> 2),
            // on décale les tâches entre $end et $start - 1 vers le bas (+1)
            $update = $pdo->prepare("UPDATE tache SET ordre_tache = ordre_tache + 1 WHERE ordre_tache >= ? AND ordre_tache < ?");
            $update->execute([$end, $start]);
        } else {
            // Pour un déplacement vers le bas (ex: ordre 2 -> 5),
            // on décale les tâches entre $start + 1 et $end vers le haut (-1)
            $update = $pdo->prepare("UPDATE tache SET ordre_tache = ordre_tache - 1 WHERE ordre_tache > ? AND ordre_tache <= ?");
            $update->execute([$start, $end]);
        }

        // On met à jour la tâche identifiée par $startId pour la placer à la position $end
        $stmt = $pdo->prepare("UPDATE tache SET ordre_tache = ? WHERE id_tache = ?");
        $stmt->execute([$end, $startId]);

        // Commit de la transaction si tout s'est bien passé
        $pdo->commit();

        return true;
    }

    // Fonction pour mettre à jour l'ordre d'une tâche
    public function updateOrder($order)
    {
        // Connexion à la base de données
        $pdo = Database::getInstance()->getConnection();

        // On modifie le numéro d'ordre de la tache
        $sql = "UPDATE tache
        SET ordre_tache = :order
        WHERE id_tache = :id ";

        $stmt = $pdo->prepare($sql);
        
        // Exécution de la requête
        return $stmt->execute(['order' => $order, 'id' => $this->tacheId]);
    }
}
