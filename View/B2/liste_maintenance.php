<?php
require_once(__DIR__ . '/../../Secure/B2/session.php'); // Pour que la session soit démarrée et que le token soit généré
$token = generateCsrfToken();

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB2/style_maintenance.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400&display=swap" rel="stylesheet">
    <?php if ($_SESSION['user_role'] == 1): ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarAdmin.css">
<?php else: ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarTechnicien.css">
<?php endif; ?>
    <title>Liste de maintenance</title>
</head>
<?php if ($_SESSION['user_role'] == 1): ?>
    <?php require_once __DIR__ . '/../B5/navbarAdmin.php'; ?>
<?php endif; ?>
<script>
    const BASE_URL = "<?= BASE_URL ?>";
</script>
<script src="<?= BASE_URL ?>/Javascript/B2/script.js" defer></script>

<body>

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
                                        <input type="checkbox" id="select_all_sites" class="site-filter" value="Tous" checked>
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
                                            <input type="checkbox"
                                                class="site-filter"
                                                value="<?= htmlspecialchars($id) ?>"
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
                                <td><?= htmlspecialchars($row["date_anniv_recurrence"]) ?></td>
                                <td><?= htmlspecialchars($row["nom_site"]) ?></td>
                                <td><?= htmlspecialchars($row["nom_batiment"]) ?></td>
                                <td>
                                    <a class="modif_recurr" href="index.php?action=maintenance_modifier&id=<?= $row['id_recurrence'] ?>">Modifier</a>
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
            <a href="index.php?action=maintenance_ajouter" class="add_recurr">Ajouter</a>
        </div>
    </form>
</body>

</html>