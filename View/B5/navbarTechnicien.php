<?php

/**
 * Navigation bar component pour technicien
 */
//session_start(); // Assure-toi que les sessions sont bien démarrées

// Pour test temporaire (à supprimer plus tard) :
// $_SESSION['role'] = 'Technicien'; // à commenter ou enlever dans l'intégration finale

?>
<!-- Checkbox pour contrôler l'affichage du menu -->
<input type="checkbox" id="menu-toggle-checkbox" class="menu-toggle-checkbox">
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="https://fonts.googleapis.com/css2?family=Caveat+Brush&display=swap" rel="stylesheet">

    <title>Document</title>
</head>
<body>
<div class="logo-container">
  <a href="<?= BASE_URL ?>/AccueilTechnicien">
    <div class="logo-wrapper">
      <img src="https://www.albatros-asbl.be/wp-content/uploads/assets/little-circle-green.svg" class="cercle-vert" alt="cercle vert">
      <img src="https://www.albatros-asbl.be/wp-content/uploads/assets/little-circle-white.svg" class="cercle-blanc" alt="cercle blanc">
      <img src="https://www.albatros-asbl.be/wp-content/uploads/2022/09/Logo.svg" class="logo" alt="Logo Albatros">
    </div>
  </a>
</div>

<!-- HAMBURGER MENU - Toujours visible en mobile -->
<label for="menu-toggle-checkbox" class="menu-toggle">
    <div class="hamburger-icon">
        <div class="hamburger-line"></div>
        <div class="hamburger-line"></div>
        <div class="hamburger-line"></div>
    </div>
</label>

<!-- Cercles décoratifs -->
<img src="https://www.albatros-asbl.be/wp-content/uploads/assets/little-circle-orange.svg" alt="cercle jaune" class="decorative-circle yellow-circle">
<img src="https://www.albatros-asbl.be/wp-content/uploads/assets/little-circle-green.svg" alt="cercle vert" class="decorative-circle green-circle">

<!-- Menu navigation -->
<div class="navbar-wrapper">
    <nav id="main-nav">
        <ul>
            <li>
                <a href="<?= BASE_URL ?>/AccueilTechnicien">Accueil</a>
            </li>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle">Demandes</a>
                <div class="dropdown-menu demandes-menu">
                    <a href="<?= BASE_URL ?>/demande">Nouvelle demande</a>
                    <a href="<?= BASE_URL ?>/ListeDemandes">Voir mes demandes</a>

                    <?php if (isset($_SESSION['user']['role_id']) && $_SESSION['user']['role_id']  == 2): ?>
                        <a href="<?= BASE_URL ?>/tasksForTechnicien">Voir mes tâches</a>
                    <?php endif; ?>
                </div>
            </li>
            <li class="dropdown">
            <a href="#" class="dropdown-toggle">Profil</a>
            <div class="dropdown-menu demandes-menu">
                <a href="<?= BASE_URL ?>/profil">Modifier mon profil</a>
                <a href="<?= BASE_URL ?>/deconnexion">Déconnexion</a>
            </div>
            </li>

        </ul>
    </nav>
</div>

</body>
</html>
<!-- Logo dans le coin supérieur gauche -->


<!-- Script JS pour le menu déroulant -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropdowns = document.querySelectorAll('.dropdown-toggle');

        dropdowns.forEach(dropdown => {
            dropdown.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Fermer tous les autres
                dropdowns.forEach(other => {
                    if (other !== dropdown) {
                        other.parentElement.classList.remove('active');
                    }
                });

                // Toggle l'affichage de celui cliqué
                this.parentElement.classList.toggle('active');
            });
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                dropdowns.forEach(dropdown => {
                    dropdown.parentElement.classList.remove('active');
                });
            }
        });
    });
</script>