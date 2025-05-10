
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

<h1 class="title">Détail du lieu</h1>

<div class="container1-B4">
    <?php if (isset($lieu)): ?>
        <br>
        <div class="header">
        <h2>Modification du Lieu</h2>

        <!-- Bouton supprimer ou réactiver -->
        <?php if ($everythingActive): 
            $message = "Êtes‑vous sûr de vouloir supprimer le lieu {$lieu['nom_lieu']} ?";?>
            <button type="button" onclick="openDeletePopup(<?= htmlspecialchars(json_encode($message), ENT_QUOTES, 'UTF-8') ?>)" class="delete">Supprimer le lieu</button>
        <?php else: 
            if ($batimentAndSiteActive){
                $message = "Êtes-vous sûr de vouloir réactiver le lieu {$lieu['nom_lieu']} ?";
            } else {
                $message = "En reactivant ce lieu, vous réactiverez également le bâtiment et le site associés. Êtes-vous sûr de vouloir continuer ?";
            }?>
            <button type="button" onclick="openDeletePopup(<?= htmlspecialchars(json_encode($message), ENT_QUOTES, 'UTF-8') ?>)" class="add">Réactiver le lieu</button>
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
        <div class="overlay" id="overlay" onclick="closeLieuDetailPopup()"></div>
        <div class="popup" id="deletePopup">
            <?php if ($everythingActive): ?>
                <h3>Confirmer la suppression</h3>
                <p id="deletePopupMessage">Êtes-vous sûr de vouloir supprimer ce lieu ?</p>
                <form method="post">
                    <div class="button-group">
                        <button type="submit" name="delete_lieu" class="delete">Confirmer</button>
                        <button type="button" onclick="closeLieuDetailPopup()" class="stop">Annuler</button>
                    </div>
                </form>
            <?php else: ?>
                <h3>Confirmer la réactivation</h3>
                <p id="deletePopupMessage">Voulez-vous réactiver ce lieu ?</p>
                <form method="post">
                    <div class="button-group">
                        <button type="submit" name="activate_lieu" class="save">Confirmer</button>
                        <button type="button" onclick="closeLieuDetailPopup()" class="delete">Annuler</button>
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
