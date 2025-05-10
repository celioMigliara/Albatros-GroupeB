
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
        <title>Gestion des bâtiments</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB4/styleB4.css">
        <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB4/style.css">
        <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarAdmin.css">
        <script src="<?= BASE_URL ?>/JavaScript/B4/popup.js"></script>
   
</head>

<body>
    <header>
    <?php require_once __DIR__ . '/../../B5/navbarAdmin.php'; ?>
    
      
    </header>
    

    <h1 class="title">Gestion des bâtiments</h1>


<div class="container1-B4">
    
    <?php if (isset($site)): ?>
        <div class="header">
            <h2>Modifier le site</h2>
            <?php if ($site['actif_site']): 
                $message = "Êtes‑vous sûr de vouloir supprimer le site {$site['nom_site']} ?";?>
                <button type="button" class="delete" onclick="openDeletePopup(<?= htmlspecialchars(json_encode($message), ENT_QUOTES, 'UTF-8') ?>)">Supprimer le site</button>
            <?php else: 
                $message = "Êtes‑vous sûr de vouloir réactiver le site {$site['nom_site']} ?";?>
                <button type="button" class="add" onclick="openDeletePopup(<?= htmlspecialchars(json_encode($message), ENT_QUOTES, 'UTF-8') ?>)">Activer le site</button>
            <?php endif; ?>
        </div>

        <form method="post" style="margin-top: 20px;">
            <div class="form-row">
                <label for="siteName">Nom du site :</label>
                <input type="text" id="siteName" name="site_name" 
                    value="<?= htmlspecialchars($site['nom_site']) ?>" required>
                <button type="submit" name="update_site" class="save">Enregistrer</button>
            </div>
        </form>
    <?php endif; ?>

    <!-- Bâtiments -->
    <div class="header">
        <h2>Bâtiments</h2>

            <?php if ((isset($site['actif_site']) && $site['actif_site'])|| !isset($site['actif_site'])): ?>
                <form method="get" action="" style="margin-top: 10px;">
                    <input type="hidden" name="id" value="<?= $id_site ?>">
                    <label for="filter">Afficher:</label>
                    <select name="filter" id="filter" onchange="this.form.submit()">
                        <option value="all" <?= ($filter ?? '') === 'all' ? 'selected' : '' ?>>Tous les bâtiments</option>
                        <option value="active" <?= ($filter ?? '') === 'active' ? 'selected' : '' ?>>Bâtiments actifs</option>
                    </select>
                </form>
            <?php endif; ?>

        
        <?php if(isset($id_site)): ?>
            <button class="add" onclick="openAddPopup()">Ajouter un bâtiment</button>
        <?php else: ?>
            <br>           
        <?php endif; ?>
    </div>
    <table class="table">
        <thead class="table-header">
            <tr>
                <th>Bâtiment</th>
                <?php if ($id_site === null): ?>
                    <th>Site</th>
                <?php endif; ?>

                <!-- ► nouvelle colonne « Statut » quand $filter == false -->
                <?php if ($filter === 'all'): ?>
                    <th>Statut</th>
                <?php endif; ?>
            </tr>
        </thead>

        <tbody class="tbody">
        <?php if (!empty($batiments)): ?>
            <?php foreach ($batiments as $batiment): ?>
                <tr
                    onclick="window.location.href='lieux?id=<?= $batiment['id_batiment'] ?>'"
                    style="cursor:pointer;"
                >
                    <td><?= htmlspecialchars($batiment['nom_batiment']) ?></td>

                    <?php if ($id_site === null): ?>
                        <td><?= htmlspecialchars($batiment['nom_site']) ?></td>
                    <?php endif; ?>

                    <!-- ► valeur de la nouvelle colonne -->
                    <?php if ($filter === 'all'): ?>
                        <td><?= $batiment['actif_batiment'] ? 'Actif' : 'Inactif' ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <?php
                /* colspan dynamique : 1 (Bâtiment) + évent. 1 (Site) + évent. 1 (Statut) */
                $colspan = 1 + ($id_site === null ? 1 : 0) + ($filter === false ? 1 : 0);
            ?>
            <tr>
                <td colspan="<?= $colspan ?>">Aucun bâtiment disponible.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>


    <?php if (isset($id_site)): ?>
        <br><a href="sites">← Retour à la liste des sites</a>
    <?php endif; ?>
</div>
<div class="overlay" id="overlay" onclick="closeBatimentPopup()"></div>
<!-- Popup suppression -->
<?php if (isset($id_site)): ?>
    <?php if ($site['actif_site']): ?>
        <div class="popup-delete" id="deletePopup">
            <h3>Confirmer la suppression</h3>
            <p id="deletePopupMessage">Êtes-vous sûr de vouloir supprimer ce site ?</p>
            <form method="post">
                <div class="button-group">
                    <button type="submit" name="delete_site" class="delete">Confirmer</button>
                    <button type="button" onclick="closeBatimentPopup()" class="stop">Annuler</button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="popup-delete" id="deletePopup">
            <h3>Confirmer l'activation</h3>
            <p id="deletePopupMessage">Êtes-vous sûr de vouloir réactiver ce site ?</p>
            <form method="post">
                <div class="button-group">
                    <button type="submit" name="activate_site" class="save">Confirmer</button>
                    <button type="button" onclick="closeBatimentPopup()" class="delete">Annuler</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Popup ajout bâtiment -->
    <div class="popup" id="addPopup">
        <h3>Ajouter un bâtiment</h3>
        <form method="post">
            <input type="hidden" name="id_site" value="<?= $id_site ?>">
            <label for="batimentName">Nom du bâtiment :</label>
            <input type="text" id="batimentName" name="batiment_name" required>
            <br><br>
            <button type="submit" name="add_batiment" class="save">Ajouter</button>
            <button type="button" onclick="closeBatimentPopup()" class="delete">Annuler</button>
        </form>
    </div>
<?php endif; ?>