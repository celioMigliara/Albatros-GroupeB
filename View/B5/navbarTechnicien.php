<?php
/**
 * Navigation bar component pour technicien
 */
session_start(); // Assure-toi que les sessions sont bien démarrées

// Pour test temporaire (à supprimer plus tard) :
// $_SESSION['role'] = 'Technicien'; // à commenter ou enlever dans l'intégration finale

?>
<!-- Checkbox pour contrôler l'affichage du menu -->
<input type="checkbox" id="menu-toggle-checkbox" class="menu-toggle-checkbox">

<!-- Logo dans le coin supérieur gauche -->
<div class="logo-container">
    <a href="<?= BASE_URL ?>/View//B5/AccueilTechnicien.php">
        <img src="<?= BASE_URL ?>/Image/logo.png" alt="logo institut albatros" class="site-logo">
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
            <li class="dropdown">
                <a href="#" class="dropdown-toggle">Demandes</a>
                <div class="dropdown-menu demandes-menu">
                    <a href="#">Nouvelle demande</a>
                    <a href="#">Voir mes demandes</a>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Technicien'): ?>
                        <a href="#">Voir mes tâches</a>
                    <?php endif; ?>
                </div>
            </li>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Technicien'): ?>
                <li><a href="#">Feuille de route</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

<!-- Script JS pour le menu déroulant -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropdowns = document.querySelectorAll('.dropdown-toggle');

    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', function (e) {
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

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.dropdown')) {
            dropdowns.forEach(dropdown => {
                dropdown.parentElement.classList.remove('active');
            });
        }
    });
});
</script>
