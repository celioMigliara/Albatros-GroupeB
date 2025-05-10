
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
        <title>Gestion des lieux</title>
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
    

    <h1 class="title">Gestion des lieux</h1>

    <div class="container1-B4">
    <?php if (isset($id_batiment)): ?>
        <br>
        <div class="header">
            <h2>Modifier le bâtiment</h2>
            <?php if ($batiment['actif_batiment']): 
                $message = "Êtes‑vous sûr de vouloir supprimer le bâtiment {$batiment['nom_batiment']} ?";?>
                <button type="button" class="delete" onclick="openDeletePopup(<?= htmlspecialchars(json_encode($message), ENT_QUOTES, 'UTF-8') ?>)">Supprimer le bâtiment</button>
            <?php else: 
                $message = "Êtes‑vous sûr de vouloir réactiver le bâtiment {$batiment['nom_batiment']} ?";?>
                <button type="button" class="add" onclick="openDeletePopup(<?= htmlspecialchars(json_encode($message), ENT_QUOTES, 'UTF-8') ?>)">Réactiver le bâtiment</button>
            <?php endif; ?>
        </div>

        <form method="post" style="margin: 20px;">
            <div class="form-row">
                <label for="batimentName">Nom du bâtiment :</label>
                <input type="text" id="batimentName" name="batiment_name" 
                       value="<?= htmlspecialchars($batiment['nom_batiment']) ?>" required>
                <button type="submit" name="update_batiment" class="save">Enregistrer</button>
            </div>
        </form>
    <?php endif; ?>

    <!-- Lieux -->
    <div class="header">
        <h2 style="margin-top: 30px;">Lieux</h2>
            <!-- Formulaire de filtrage des lieux -->
            <form method="get" action="" style="margin-top: 10px;">
                <input type="hidden" name="id" value="<?= $id_batiment ?>">
                <label for="filter">Afficher:</label>
                <select name="filter" id="filter" onchange="this.form.submit()">
                    <option value="all" <?= ($filter ?? '') === 'all' ? 'selected' : '' ?>>Tous les lieux</option>
                    <option value="active" <?= ($filter ?? '') === 'active' ? 'selected' : '' ?>>Lieux actifs</option>
                </select>
            </form>
            
        <?php if (isset($id_batiment)): ?>
            <button class="add" onclick="openAddPopup()">Ajouter un lieu</button>
        <?php else: ?>
            <br>
        <?php endif; ?>
    </div>

    <table class="table">
<thead class="table-header">
    <tr>
        <th>Lieu</th>
        <?php if (!isset($id_batiment)): ?>
            <th>Bâtiment</th>
            <th>Site</th>
        <?php endif; ?>

        <?php if (($filter ?? '') === 'all'): ?>
            <th>Statut</th>   <!-- nouvelle colonne -->
        <?php endif; ?>
    </tr>
</thead>
        <tbody class="tbody">
        <?php if (!empty($lieux)): ?>
            <?php foreach ($lieux as $lieu): ?>
                <tr
                    onclick="window.location.href='lieux/detail?id=<?= $lieu['id_lieu'] ?>'"
                    style="cursor:pointer;"
                >
                    <td><?= htmlspecialchars($lieu['nom_lieu']) ?></td>

                    <?php if (!isset($id_batiment)): ?>
                        <td><?= htmlspecialchars($lieu['nom_batiment']) ?></td>
                        <td><?= htmlspecialchars($lieu['nom_site']) ?></td>
                    <?php endif; ?>

                    <?php if (($filter ?? '') === 'all'): ?>
                        <td><?= $lieu['actif_lieu'] ? 'Actif' : 'Inactif' ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <?php
                // Colspan dynamique : 1 (Lieu) + évent. 2 (Bâtiment/Site) + évent. 1 (Statut)
                $colspan = 1 + (!isset($id_batiment) ? 2 : 0) + (($filter ?? '') === 'all' ? 1 : 0);
            ?>
            <tr>
                <td colspan="<?= $colspan ?>">Aucun lieu disponible.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>


    <?php if (isset($id_batiment)): ?>
        <br><a href="batiments?id=<?= $id_site ?>">← Retour aux bâtiments du site</a>
    <?php endif; ?>
</div>

<!-- Popup suppression ou réactivation -->
<div class="overlay" id="overlay" onclick="closeLieuPopup()"></div>
<?php if (isset($id_batiment)): ?>
    <?php if ($batiment['actif_batiment']): ?>
        <div class="popup-delete" id="deletePopup">
            <h3>Confirmer la suppression</h3>
            <p id="deletePopupMessage">Êtes-vous sûr de vouloir supprimer ce bâtiment ?</p>
            <form method="post">
                <div class="button-group">
                    <button type="submit" name="delete_batiment" class="delete">Confirmer</button>
                    <button type="button" onclick="closeLieuPopup()" class="stop">Annuler</button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="popup-delete" id="deletePopup">
            <h3>Confirmer la réactivation</h3>
            <p id="deletePopupMessage">Voulez-vous réactiver ce bâtiment ?</p>
            <form method="post">
                <div class="button-group">
                    <button type="submit" name="activate_batiment" class="save">Confirmer</button>
                    <button type="button" onclick="closeLieuPopup()" class="delete">Annuler</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Popup ajout lieu -->
    
    <div class="popup" id="addPopup">
        <h3>Ajouter un lieu</h3>
        <form method="post">
            <label for="lieuName">Nom du lieu :</label>
            <input type="text" id="lieuName" name="lieu_name" required>
            <br><br>
            <button type="submit" name="add_lieu" class="save">Ajouter</button>
            <button type="button" onclick="closeLieuPopup()" class="delete">Annuler</button>
        </form>
    </div>
<?php endif; ?>