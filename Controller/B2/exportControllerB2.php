    <?php
    require_once __DIR__ . '/../../Model/B2/filtreModelB2.php';

    $pdo = Database::getInstance()->getConnection();

    // Combiner les champs de date pour créer des dates complètes
    $date_debut = null;
    if (!empty($_GET['date_debut_jour']) && !empty($_GET['date_debut_mois']) && !empty($_GET['date_debut_annee'])) {
        $date_debut = sprintf('%04d-%02d-%02d', $_GET['date_debut_annee'], $_GET['date_debut_mois'], $_GET['date_debut_jour']);
    }

    $date_fin = null;
    if (!empty($_GET['date_fin_jour']) && !empty($_GET['date_fin_mois']) && !empty($_GET['date_fin_annee'])) {
        $date_fin = sprintf('%04d-%02d-%02d', $_GET['date_fin_annee'], $_GET['date_fin_mois'], $_GET['date_fin_jour']);
    }

    // Initialiser les filtres
    $filters = [
        'date_debut' => $date_debut,
        'date_fin' => $date_fin,
    ];

    // Récupérer les données filtrées
    $results = getDemandesParDates($pdo, $filters);

    // Générer le fichier CSV
    header('Content-Type: text/csv; charset=ISO-8859-1');
    header('Content-Disposition: attachment; filename="export_demandes.csv"');

    // Ouvrir un flux de sortie
    $output = fopen('php://output', 'w');

    // Ajouter les en-têtes des colonnes
    fputcsv($output, [
        'Numéro de ticket',
        'Sujet',
        'Date de création',
        'Bâtiment',
        'Lieu',
        'Demandeur',
        'Statut',
        'Site'
    ]);

    // Ajouter les données
    foreach ($results as $row) {
        // Convertir chaque valeur en ISO-8859-1 pour éviter les caractères spéciaux
        $row = array_map(function ($value) {
            return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $value);
        }, $row);

        fputcsv($output, $row);
    }

    // Fermer le flux
    fclose($output);
    exit();
    ?>