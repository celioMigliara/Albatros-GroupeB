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

    public static function getAllStatusEnCours()
    {
        // Connexion à la base de données
        $pdo = Database::getInstance()->getConnection();

        // Requête SQL pour récupérer tous les techniciens
        $sql = "SELECT id_statut, nom_statut
            FROM statut WHERE id_statut < 5";

        // Préparer la requête
        $stmt = $pdo->prepare($sql);

        // Exécution de la requête
        $stmt->execute();

        // Récupérer et retourner les résultats sous forme de tableau associatif
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAllStatutsTermines()
    {
        // Connexion à la base de données
        $pdo = Database::getInstance()->getConnection();

        // Requête SQL pour récupérer tous les techniciens
        $sql = "SELECT id_statut, nom_statut
            FROM statut WHERE id_statut >= 5";

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

    // Fonction qui met à jour l'ordre des taches d'un technicien à partir du start au end.
    public static function updateLinearOrder($start, $end, $techId)
    {
        $pdo = Database::getInstance()->getConnection();

        // On commence une transaction car on enchaine les requetes SQL qui pourraient ne pas passer
        // On s'assure de la cohérence des données avec le concept de transaction
        $pdo->beginTransaction();

        // 1. Récupérer la tâche de départ avec statut < 5
        // C'est pour s'assurer qu'on modifie bien la bonne tache start
        $stmt = $pdo->prepare("
            SELECT id_tache
            FROM tache
            WHERE ordre_tache = :start
            AND id_utilisateur = :techId
            AND (
                SELECT h.id_statut
                FROM historique h
                WHERE h.id_tache = tache.id_tache
                ORDER BY h.date_modif DESC
                LIMIT 1
            ) < 5
        ");
        $stmt->execute(['start' => $start, 'techId' => $techId]);
        $startTask = $stmt->fetch();

        // On rollback si on a rien trouvé
        if (!$startTask) {
            $pdo->rollBack();
            return false;
        }

        $startId = $startTask['id_tache'];

        // 2. Mise à jour des autres tâches dans l'intervalle, statut < 5 uniquement
        if ($start > $end) {
            $update = $pdo->prepare("
                UPDATE tache
                SET ordre_tache = ordre_tache + 1
                WHERE ordre_tache >= :end AND ordre_tache < :start
                AND id_utilisateur = :techId
                AND (
                    SELECT h.Id_statut
                    FROM historique h
                    WHERE h.Id_tache = tache.id_tache
                    ORDER BY h.date_modif DESC
                    LIMIT 1
                ) < 5
            ");
            $update->execute(['end' => $end, 'start' => $start, 'techId' => $techId]);
        } else {
            $update = $pdo->prepare("
                UPDATE tache
                SET ordre_tache = ordre_tache - 1
                WHERE ordre_tache > :start AND ordre_tache <= :end
                AND id_utilisateur = :techId
                AND (
                    SELECT h.Id_statut
                    FROM historique h
                    WHERE h.Id_tache = tache.id_tache
                    ORDER BY h.date_modif DESC
                    LIMIT 1
                ) < 5
            ");
            $update->execute(['start' => $start, 'end' => $end, 'techId' => $techId]);
        }

        // 3. Mise à jour de la tâche de départ
        $stmt = $pdo->prepare("
            UPDATE tache
            SET ordre_tache = :end
            WHERE id_tache = :startId
            AND id_utilisateur = :techId
        ");
        $stmt->execute(['end' => $end, 'startId' => $startId, 'techId' => $techId]);

        // On commit la transaction une fois que tout s'est bien passé
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
