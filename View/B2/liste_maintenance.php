<?php
require_once(__DIR__ . '/../../Secure/B2/session.php'); // Pour que la session soit démarrée et que le token soit généré
$token = generateCsrfToken();

require_once __DIR__ . '/../../Model/UserConnectionUtils.php';

if (!UserConnectionUtils::isAdminConnected()) {
    header('Location: ' . BASE_URL . "/connexion");
    exit;
}

if (isset($_SESSION['popup_message'])) {
    $message = $_SESSION['popup_message']; //Stock  le message de la session
    $success = $_SESSION['popup_success'];

    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        showPopup(" . json_encode($message) . ", " . ($success ? 'false' : 'true') . ");
    });
    </script>";

    unset($_SESSION['popup_message'], $_SESSION['popup_success']);
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB2/style_maintenance.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarAdmin.css">

    <title>Liste de maintenance</title>
</head>
<?php require_once __DIR__ . '/../B5/navbarAdmin.php'; ?>

<script>
    const BASE_URL = "<?= BASE_URL ?>";
</script>
<script src="<?= BASE_URL ?>/Javascript/B2/script.js" defer></script>

<body>
    <div id="popup" class="popup" style="display: none;">
        <div class="modal-content">
            <img src="<?= BASE_URL ?>/Assets/B2/Albatros.jpg" alt="Logo popup" class="popup-logo-B2"
                data-effect="mfp-move-horizontal">
            <p id="popup-message"></p>
            <button id="popup-ok-btn" class="">OK</button>
        </div>
    </div>

    <!-- Pop-up d’erreur -->
    <div id="error-popup" class="popup error" style="display: none;">
        <div class="modal-content">
            <img src="<?= BASE_URL ?>/Assets/B2/Albatros.jpg" alt="Logo popup" class="popup-logo-B2"
                data-effect="mfp-move-horizontal">
            <p id="error-message"></p>
            <button id="error-ok-btn" class="">OK</button>
        </div>
    </div>
    <h1 class="titre">Liste des maintenances</h1>
    <div class="separateur-double-ligne-B2"></div>

    <form method="post" action="index.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
        <div class="container-recurr">
            <table class="tbl-qa-recurr">
                <thead>
                    <tr class="table-row">
                        <th class="table-header" width="20%">Maintenance à planifier</th>
                        <th class="table-header" width="20%">Date d'anniversaire</th>

                        <!-- Filtre Site -->
                        <th class="table-header" width="20%">Site
                            <div class="dropdown" id="dropdownFilter">
                                <button type="button" class="filter-btn" id="filterBtn">Filtrer</button>
                                <div class="dropdown-content" id="siteFilterMenu">
                                    <label>
                                        Tous
                                        <input type="checkbox" id="select_all_sites" class="site-filter" value="Tous"
                                            checked>
                                    </label>
                                    <?php
                                    $uniqueSites = [];
                                    foreach ($result as $row) {
                                        if (!isset($uniqueSites[$row['id_site']])) {
                                            $uniqueSites[$row['id_site']] = $row['nom_site'];
                                        }
                                    }
                                    foreach ($uniqueSites as $id => $nom):
                                        ?>
                                        <label>
                                            <?= htmlspecialchars($nom) ?>
                                            <input type="checkbox" class="site-filter" value="<?= htmlspecialchars($id) ?>"
                                                checked>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </th>
                        <th class="table-header" width="20%">Bâtiment
                        </th>
                        <th class="table-header" width="20%"></th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <?php
                    if (!empty($result)) {
                        foreach ($result as $row): ?>
                            <tr class="table-row" data-site="<?= htmlspecialchars($row['id_site']) ?>">
                                <td><?= htmlspecialchars($row["sujet_reccurrence"]) ?></td>
                                <td><?= date('d/m/Y', strtotime($row["date_anniv_recurrence"])) ?></td>
                                <td><?= htmlspecialchars($row["nom_site"]) ?></td>
                                <td><?= htmlspecialchars($row["nom_batiment"]) ?></td>
                                <td>
                                    <a class="modif_recurr"
                                        href="<?= BASE_URL ?>/maintenance/modifier/<?= $row['id_recurrence'] ?>">Modifier</a>
                                </td>
                            </tr>
                            <?php
                        endforeach;
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="container">
            <a href="<?= BASE_URL ?>/maintenance/ajouter" class="add_recurr">Ajouter</a>
        </div>
    </form>
</body>

</html>