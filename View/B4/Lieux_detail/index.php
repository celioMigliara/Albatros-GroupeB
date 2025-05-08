<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des lieux</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB4/styleB4.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB4/style.css">
    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarAdmin.css">
    <?php else: ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarTechnicien.css">
    <?php endif; ?>
    <script src="<?= BASE_URL ?>/JavaScript/B4/popup.js"></script>
</head>

<body>
<header>
    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1): ?>
        <?php require_once __DIR__ . '/../../B5/navbarAdmin.php'; ?>
    <?php else: ?>
        <?php require_once __DIR__ . '/../../B5/navbarTechnicien.php'; ?>
    <?php endif; ?>
</header>

<h1 class="title">Détail du lieu</h1>

<div class="container">
    <?php if (isset($lieu)): ?>
        <br>
        <div class="header">
        <h2>Modification du Lieu</h2>

        <!-- Bouton supprimer ou réactiver -->
        <?php if ($lieu['actif_lieu']): ?>
            <button type="button" onclick="openPopup()" class="delete">Supprimer le lieu</button>
        <?php else: ?>
            <button type="button" onclick="openPopup()" class="add">Réactiver le lieu</button>
        <?php endif; ?>
        
        </div>

        <!-- Formulaire pour modifier le lieu -->
        <form method="post" style="margin-top: 20px;">
            <div style="margin-bottom: 20px;">
                <label for="lieuName">Nom du lieu :</label>
                <input type="text" id="lieuName" name="nom_lieu"
                       value="<?= htmlspecialchars($lieu['nom_lieu']) ?>" required>
            </div>

            <div class="button-group">
                <button type="submit" name="update_lieu" class="save">Enregistrer</button>
            </div>
        </form>

        <br>
        <a href="../lieux?id=<?= $id_batiment ?>">← Retour à la liste des lieux</a>

        <!-- Popup dynamique : suppression ou réactivation -->
        <div class="overlay" id="overlay" onclick="closeDetailPopup()"></div>
        <div class="popup" id="popup">
            <?php if ($lieu['actif_lieu']): ?>
                <h3>Confirmer la suppression</h3>
                <p>Êtes-vous sûr de vouloir supprimer ce lieu ?</p>
                <form method="post">
                    <div class="button-group">
                        <button type="submit" name="delete_lieu" class="delete">Confirmer</button>
                        <button type="button" onclick="closeDetailPopup()" class="stop">Annuler</button>
                    </div>
                </form>
            <?php else: ?>
                <h3>Confirmer la réactivation</h3>
                <p>Voulez-vous réactiver ce lieu ?</p>
                <form method="post">
                    <div class="button-group">
                        <button type="submit" name="activate_lieu" class="save">Confirmer</button>
                        <button type="button" onclick="closeDetailPopup()" class="delete">Annuler</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <p>Lieu non trouvé.</p>
    <?php endif; ?>
</div>
</body>
</html>
