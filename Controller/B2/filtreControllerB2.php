<?php require_once __DIR__ . '/../../Model/B2/filtreModelB2.php'; ?>

<?php

$pdo = Database::getInstance()->getConnection();

// Combiner les champs de date pour créer des dates complètes
$date_debut = null;
if (!empty($_POST['date_debut_jour']) && !empty($_POST['date_debut_mois']) && !empty($_POST['date_debut_annee'])) {
    $date_debut = sprintf('%04d-%02d-%02d', $_POST['date_debut_annee'], $_POST['date_debut_mois'], $_POST['date_debut_jour']);
}

$date_fin = null;
if (!empty($_POST['date_fin_jour']) && !empty($_POST['date_fin_mois']) && !empty($_POST['date_fin_annee'])) {
    $date_fin = sprintf('%04d-%02d-%02d', $_POST['date_fin_annee'], $_POST['date_fin_mois'], $_POST['date_fin_jour']);
}

// Initialiser les filtres
$filters = [
    'date_debut' => $date_debut,
    'date_fin' => $date_fin,
];

// Récupérer les demandes filtrées
$results = getDemandesParDates($pdo, $filters);
$nbr_demandes = count($results);
?>