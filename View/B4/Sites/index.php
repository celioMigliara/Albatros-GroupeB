
<?php
require_once __DIR__ . '/../../../Model/UserConnectionUtils.php';

if (!UserConnectionUtils::isAdminConnected()) {
    header('Location: ' . BASE_URL . "/connexion");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Gestion des sites</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB4/style.css">
        <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB4/styleB4.css">
     
        <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarAdmin.css">
   
        <script src="<?= BASE_URL ?>/JavaScript/B4/popup.js"></script>
</head>

<body>
    <header>
   
    <?php require_once __DIR__ . '/../../B5/navbarAdmin.php'; ?>
    
    </header>

    <h1 class="title">Gestion des sites </h1>

    <div class="container1-B4">
        <div class="header">
            <h2>Gérer les sites</h2>
            <!-- Formulaire de filtrage des sites -->
            <form method="get" action="">
                <label for="filter">Afficher:</label>
                <select name="filter" id="filter" onchange="this.form.submit()">
                    <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>Tous les sites</option>
                    <option value="active" <?= $filter === 'active' ? 'selected' : '' ?>>Sites actifs</option>
                </select>
            </form>
            <button class="add" onclick="openPopup()">Ajouter</button>
        </div>
        <!-- Affichage des sites -->
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sites as $site): ?>
                    <tr onclick="window.location.href='batiments?id=<?= $site['id_site'] ?>'" style="cursor: pointer;">
                        <td><?= htmlspecialchars($site['nom_site']) ?></td>
                    </tr>
                    
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="center"></div>
            <button id="openImport" class="center" onclick="openImportPopup()">Importer via un fichier Excel</button>
        </div>
    </div>


    <!-- Bouton pour importer des sites/ -->

    <!-- Popup -->
    <div class="popup "id="importPopup">
        <h3>Importer via un fichier Excel</h3>
        <form action="sites/import" method="post" enctype="multipart/form-data" onsubmit="return validateUpload()">
            <input type="file" name="excel_file" id="excel_file" accept=".xlsx" required><br><br>
            <input type="submit" value="Importer" class="save">
            <button type="button" onclick="closePopup()" class="delete">Annuler</button>
        </form>
    </div>

    <div class="overlay" id="overlay" onclick="closePopup()"></div>
    <!-- Popup pour ajouter un site -->
    <div class="popup" id="popup">
        <h3>Ajouter un site</h3>
        <form method="post">
            <label for="site_name">Nom du site :</label>
            <input type="text" id="site_name" name="site_name" required>
            <br><br>
            <button class="save" type="submit" name="add_site">Ajouter</button>
            <button type="button" onclick="closePopup()" class="delete">Annuler</button>
        </form>
    </div>
</body>