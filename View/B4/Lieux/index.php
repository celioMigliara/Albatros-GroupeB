<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Gestion des lieux</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB4/styleB4.css">
        <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB4/style.css">
        <?php if ($_SESSION['user_role'] == 1): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarAdmin.css">
    <?php else: ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarTechnicien.css">
    <?php endif; ?>
</head>

<body>
    <header>
    <?php if ($_SESSION['user_role'] == 1): ?>
    <?php require_once __DIR__ . '/../../B5/navbarAdmin.php'; ?>
    <?php else: ?>
        <?php require_once __DIR__ . '/../../B5/navbarTechnicien.php'; ?>
        <?php endif; ?>
    </header>
    

    <h1 class="title">Gestion des lieux</h1>

<div class="container">
    <?php if (isset($id_batiment)): ?>
        <br>
        <div class="header">
            <h2>Modifier le bâtiment</h2>

            <?php if ($batiment['actif_batiment']): ?>
                <button type="button" class="delete" onclick="openDeletePopup()">Supprimer le bâtiment</button>
            <?php else: ?>
                <button type="button" class="add" onclick="openDeletePopup()">Réactiver le bâtiment</button>
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
            <button class="add" onclick="openPopup()">Ajouter un lieu</button>
        <?php else: ?>
            <br>
        <?php endif; ?>
    </div>

    <table style="margin: 20px;">
    <thead>
        <tr>
            <th>Lieu</th>
            <?php if (!isset($id_batiment)): ?>
                <th>Bâtiment</th>
                <th>Site</th>
            <?php endif; ?>
        </tr>
    </thead>

    <tbody>
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
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="<?= !isset($id_batiment) ? 3 : 1 ?>">
                    Aucun lieu disponible.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>


    <?php if (isset($id_batiment)): ?>
        <br><a href="batiments?id=<?= $id_site ?>">← Retour aux bâtiments du site</a>
    <?php endif; ?>
</div>

<!-- Popup suppression ou réactivation -->
<?php if (isset($id_batiment)): ?>
    <div class="overlay" id="overlay-delete" onclick="closeDeletePopup()"></div>
    <?php if ($batiment['actif_batiment']): ?>
        <div class="popup-delete" id="popup-delete">
            <h3>Confirmer la suppression</h3>
            <p>Êtes-vous sûr de vouloir supprimer ce bâtiment ?</p>
            <form method="post">
                <div class="button-group">
                    <button type="submit" name="delete_batiment" class="delete">Confirmer</button>
                    <button type="button" onclick="closeDeletePopup()" class="stop">Annuler</button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="popup-delete" id="popup-delete">
            <h3>Confirmer la réactivation</h3>
            <p>Voulez-vous réactiver ce bâtiment ?</p>
            <form method="post">
                <div class="button-group">
                    <button type="submit" name="activate_batiment" class="save">Confirmer</button>
                    <button type="button" onclick="closeDeletePopup()" class="delete">Annuler</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Popup ajout lieu -->
    <div class="overlay" id="overlay" onclick="closePopup()"></div>
    <div class="popup" id="popup">
        <h3>Ajouter un lieu</h3>
        <form method="post">
            <label for="lieuName">Nom du lieu :</label>
            <input type="text" id="lieuName" name="lieu_name" required>
            <br><br>
            <button type="submit" name="add_lieu" class="save">Ajouter</button>
            <button type="button" onclick="closePopup()" class="delete">Annuler</button>
        </form>
    </div>
<?php endif; ?>

<script>
    function openDeletePopup() {
        document.getElementById("popup-delete").style.display = "block";
        document.getElementById("overlay-delete").style.display = "block";
    }
    function closeDeletePopup() {
        document.getElementById("popup-delete").style.display = "none";
        document.getElementById("overlay-delete").style.display = "none";
    }
    function openPopup() {
        document.getElementById("popup").style.display = "block";
        document.getElementById("overlay").style.display = "block";
    }
    function closePopup() {
        document.getElementById("popup").style.display = "none";
        document.getElementById("overlay").style.display = "none";
    }
</script>
