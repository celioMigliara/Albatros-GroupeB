<?php
namespace Model\B4;

require_once __DIR__ . '/../ModeleDBB2.php';  // votre Database
use PDO;

class Historique
{
    private PDO $db;

    public function __construct()
    {
        $this->db = \Database::getInstance()->getConnection();
    }

    /**
     * Récupère l’historique des modifications :
     * date, ticket, sujet de la tâche, description, statut
     */
    public function getAll(): array
    {
        $sql = "
            SELECT 
                h.date_modif          AS date_modif,
                d.num_ticket_dmd      AS num_ticket,
                t.sujet_tache         AS sujet,
                t.description_tache   AS description,
                s.nom_statut          AS statut
            FROM historique h
            JOIN tache t    ON h.id_tache   = t.id_tache
            JOIN statut s   ON h.id_statut  = s.id_statut
            JOIN demande d  ON t.id_demande = d.id_demande
            ORDER BY h.date_modif DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
