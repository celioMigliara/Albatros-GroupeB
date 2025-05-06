<?php
// Inclusion du modèle User pour accéder à la base de données
require_once __DIR__ . '/../../Model/B5/User.php';

// Nombre d'utilisateurs à afficher par page
$utilisateursParPage = 10;

// Numéro de la page actuelle (1 par défaut)
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;

// Calcul de l'offset pour la requête SQL
$offset = ($page - 1) * $utilisateursParPage;

// Récupération des utilisateurs en attente (avec pagination)
$utilisateursEnAttente = User::getUtilisateursEnAttente($utilisateursParPage, $offset);

// Récupération du nombre total d'utilisateurs en attente pour la pagination
$totalUtilisateurs = User::countUtilisateursEnAttente();

// Calcul du nombre total de pages
$totalPages = ceil($totalUtilisateurs / $utilisateursParPage);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Liste des inscriptions</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarAdmin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/listeInscriptions.css"> <!-- Feuille de style spécifique -->
</head>
<body>

<?php require_once __DIR__ . '/navbarAdmin.php'; ?>

<!-- Espace visible pour forcer l'écart -->
<div id="spacing-element" style="height:150px; background-color:transparent; width:100%;"></div>

<div class="container">
    <div class="content">
        <h1>Inscriptions en attente</h1>

        <?php foreach ($utilisateursEnAttente as $utilisateur): ?>
        <!-- Le lien englobe toute la carte utilisateur -->
        <a href="<?= BASE_URL ?>/utilisateur/<?= $utilisateur['id_utilisateur'] ?>" class="card-user-link">
        <div class="card-user">
                <div class="avatar"></div>
                <div class="user-info">
                    <p><?= htmlspecialchars($utilisateur['prenom_utilisateur'] . ' ' . $utilisateur['nom_utilisateur']) ?></p>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
       
        <!-- Message si aucun utilisateur -->
        <?php if (empty($utilisateursEnAttente)): ?>
            <p class="no-inscription">Aucune inscription en attente.</p>
        <?php endif; ?>

        <!-- Pagination -->
        <div class="pagination-container">
            <!-- Lien précédent -->
            <div class="prev">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>">&laquo; Précédent</a>
                <?php endif; ?>
            </div>

            <!-- Numéros de page -->
            <div class="page-numbers">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>

            <!-- Lien suivant -->
            <div class="next">
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>">Suivant &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Script pour ajuster la position de la liste -->
<script>
window.onload = function() {
    // Ajuste l'espacement selon la taille de l'écran
    const spacingElement = document.getElementById('spacing-element');
    if (spacingElement) {
        if (window.innerWidth <= 480) {
            spacingElement.style.height = '140px';
        } else if (window.innerWidth <= 768) {
            spacingElement.style.height = '120px';
        } else {
            spacingElement.style.height = '120px';
        }
    }
};
</script>
<script>
window.onload = function() {
    const spacingElement = document.getElementById('spacing-element');
    if (spacingElement) {
        if (window.innerWidth <= 480) {
            spacingElement.style.height = '140px';
        } else if (window.innerWidth <= 768) {
            spacingElement.style.height = '120px';
        } else {
            spacingElement.style.height = '120px';
        }
    }

    <?php if (isset($_GET['status']) && $_GET['status'] === 'validation') : ?>
        alert("✅ L'inscription a bien été validée !");
    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'refus') : ?>
        alert("❌ L'inscription a été refusée avec succès.");
    <?php endif; ?>
};
</script>


</body>
</html>
