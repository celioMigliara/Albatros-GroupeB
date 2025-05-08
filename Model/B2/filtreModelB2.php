<?php

require_once __DIR__ . '/../ModeleDBB2.php';

$pdo = Database::getInstance()->getConnection();

function getDemandesParDates($pdo, $filters) {
    $sql = "SELECT
                demande.num_ticket_dmd,
                demande.sujet_dmd,
                demande.date_creation_dmd,
                batiment.nom_batiment,
                lieu.nom_lieu,
                CONCAT(utilisateur.prenom_utilisateur, ' ', utilisateur.nom_utilisateur) AS nom_complet,
                statut.nom_statut,
                site.nom_site
            FROM demande
            JOIN lieu ON demande.id_lieu = lieu.id_lieu
            JOIN batiment ON lieu.id_batiment = batiment.id_batiment
            JOIN site ON batiment.id_site = site.id_site
            JOIN utilisateur ON demande.id_utilisateur = utilisateur.id_utilisateur
            LEFT JOIN est ON demande.id_demande = est.id_demande
            LEFT JOIN statut ON est.id_statut = statut.id_statut
            WHERE 1";

    // Ajouter les conditions pour les dates
    if (!empty($filters['date_debut'])) {
        $sql .= " AND demande.date_creation_dmd >= :date_debut";
    }
    if (!empty($filters['date_fin'])) {
        $sql .= " AND demande.date_creation_dmd <= :date_fin";
    }

    $stmt = $pdo->prepare($sql);

    // Lier les paramètres des dates
    if (!empty($filters['date_debut'])) {
        $stmt->bindParam(':date_debut', $filters['date_debut'], PDO::PARAM_STR);
    }
    if (!empty($filters['date_fin'])) {
        $stmt->bindParam(':date_fin', $filters['date_fin'], PDO::PARAM_STR);
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>