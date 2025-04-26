<?php
require_once __DIR__ . '/../../Model/B1/Demande.php';

//Pour la petite fenêtre de bienvenuevec les demandes ou pas 
$userId = $_SESSION['user_id'];
$demandes = Demande::getDemandesByUser($userId, 0, 5);?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Institut Albatros - Espace Technicien</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarTechnicien.css"">
    <link rel=" stylesheet" href="<?= BASE_URL ?>/Css/cssB5/accueilTechnicien.css">
    <link rel=" stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Caveat+Brush&display=swap" rel="stylesheet">
</head>

<body>
    <?php require_once __DIR__ . '/navbarTechnicien.php'; ?>

    <!-- Espace visible pour forcer l'écart -->
    <div id="spacing-element" style="height:80px; background-color:transparent; width:100%;"></div>

    <div class="container">
        <!-- Zone de bienvenue avec le petit bonhomme vert kaki -->
        <section class="technicien-header">
            <div class="technicien-welcome">
                <div class="dropdown user-dropdown">
                    <label for="user-dropdown-toggle" class="dropdown-btn icon-only" tabindex="0">
                        <i class="fas fa-user"></i>
                    </label>
                    <div class="dropdown-content">
                        <a href="parametres.php"><i class="fas fa-cog"></i> Paramètres</a>
                        <a href="deconnexion.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                    </div>
                </div>

                <div class="technicien-text">
                    <h2>Bonjour, <span class="technicien-name">
                            <?= htmlspecialchars(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) ?>
                        </span> !</h2>
                    <p>Content de vous revoir.</p>
                </div>
            </div>
        </section>
    </div>

    <!-- Afficahe dans l'accueil des demandes du technicien si il en a ou pas -->
    <section class="demandes-uti">
        <h3 class="uti-demande">Vos dernières demandes</h3>
        <?php if (!empty($demandes)): ?>
            <div class="taches-liste">
                <?php foreach ($demandes as $demande): ?>
                    <div class="carte-tache">
                        <h3><?= htmlspecialchars($demande['sujet_dmd']) ?></h3>
                        <p><?= htmlspecialchars($demande['description_dmd']) ?></p>
                        <!-- Couleur dynamique en fonction du statut -->
                        <p><strong>Statut :</strong>
                            <span class="statut <?= strtolower(str_replace(' ', '-', $demande['nom_statut'])) ?>">
                                <?= htmlspecialchars($demande['nom_statut']) ?>
                            </span>
                        </p>
                        <p><strong>Crée le :</strong> <?= date('d/m/Y', strtotime($demande['date_creation_dmd'])) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="aucune-tache">Aucune demande en cours pour le moment.</p>
        <?php endif; ?>

    </section>


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
        window.onload = function() {
            adjustSpacing();

            // Ajuster aussi lors du redimensionnement de la fenêtre
            window.addEventListener('resize', adjustSpacing);

            // Améliorer le comportement du menu déroulant utilisateur sur appareils tactiles
            const userBtn = document.querySelector('.dropdown-btn.icon-only');
            const dropdownContent = document.querySelector('.dropdown-content');

            if (userBtn && dropdownContent) {
                userBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Ajouter/supprimer la classe 'visible' pour l'animation
                    dropdownContent.classList.toggle('visible');
                });

                // Fermer le menu quand on clique ailleurs
                document.addEventListener('click', function(e) {
                    if (!userBtn.contains(e.target) && !dropdownContent.contains(e.target)) {
                        dropdownContent.classList.remove('visible');
                    }
                });

                // Ajouter un gestionnaire pour les liens du menu
                const menuLinks = dropdownContent.querySelectorAll('a');
                menuLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        dropdownContent.classList.remove('visible');
                    });
                });
            }
        };
    </script>
</body>

</html>