<?php
require_once __DIR__ . '/../../Model/B5/User.php';
require_once __DIR__ . '/../../Model/UserConnectionUtils.php';

if (!UserConnectionUtils::isAdminConnected()) {
    header('Location: ' . BASE_URL . "/connexion");
    exit;
}
// Inclusion du modèle User pour accéder à la fonction de comptage
// Récupération du nombre de comptes utilisateurs en attente de validation
$nbComptesEnAttente = User::countUtilisateursEnAttente();


?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Institut Albatros</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarAdmin.css"">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/accueilAdmin.css"">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <?php require_once __DIR__ . '/navbarAdmin.php'; ?>

    <!-- Espace visible pour forcer l'écart -->
    <div id="spacing-element" style="height:80px; background-color:transparent; width:100%;"></div>

    <div class="container">
        <!-- Zone de bienvenue avec le petit bonhomme vert kaki -->
        <section class="admin-header">
            <div class="admin-welcome">
                <div class="dropdown user-dropdown">
                    <button class="dropdown-btn icon-only" tabindex="0">
                        <i class="fas fa-user"></i>
                    </button>
                  
                </div>

            <div class="admin-text">
                    <h2>Bonjour, <span class="admin-name">
                    <?= htmlspecialchars(($_SESSION['user']['prenom'] ?? '') ) ?>
                    </span> !</h2>
                    <p>Content de vous revoir.</p>
                </div>
            </div>
        </section>

        <main class="dashboard">
            <!-- NOTIFICATIONS -->
            <section class="notifications-section">
                <h2><i class="fas fa-bell"></i> Notification(s) récente(s)</h2>

                <div class="notification-card alert">
                    <div class="notification-header">
                        <h3><i class="fas fa-user-clock"></i> <?= $nbComptesEnAttente ?> inscriptions en attente</h3>
                        <span class="notification-badge"><?= $nbComptesEnAttente ?></span>
                    </div>
                    <p>Comptes utilisateurs à valider. Dernière demande il y a quelques minutes.</p>

                    <div class="action-container">
                        <a href="<?= BASE_URL ?>/inscriptions" class="btn-validate">
                            <i class="fas fa-eye"></i> valider les accès
                        </a>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Script pour ajuster l'espacement et gérer le menu utilisateur -->
    <script>
        function adjustSpacing() {
            // Ajuste l'espacement selon la taille de l'écran
            const spacingElement = document.getElementById('spacing-element');
            if (spacingElement) {
                if (window.innerWidth <= 480) {
                    spacingElement.style.height = '100px';
                } else if (window.innerWidth <= 768) {
                    spacingElement.style.height = '80px';
                } else {
                    spacingElement.style.height = '60px';
                }
            }
        }

        // Exécuter au chargement de la page
        window.onload = function () {
            adjustSpacing();

            // Ajuster aussi lors du redimensionnement de la fenêtre
            window.addEventListener('resize', adjustSpacing);

            // Améliorer le comportement du menu déroulant utilisateur sur appareils tactiles
            const userBtn = document.querySelector('.dropdown-btn.icon-only');
            const dropdownContent = document.querySelector('.dropdown-content');

            if (userBtn && dropdownContent) {
                userBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Ajouter/supprimer la classe 'visible' pour l'animation
                    dropdownContent.classList.toggle('visible');
                });

                // Fermer le menu quand on clique ailleurs
                document.addEventListener('click', function (e) {
                    if (!userBtn.contains(e.target) && !dropdownContent.contains(e.target)) {
                        dropdownContent.classList.remove('visible');
                    }
                });

                // Ajouter un gestionnaire pour les liens du menu
                const menuLinks = dropdownContent.querySelectorAll('a');
                menuLinks.forEach(link => {
                    link.addEventListener('click', function () {
                        dropdownContent.classList.remove('visible');
                    });
                });
            }
        };
    </script>
</body>

</html>