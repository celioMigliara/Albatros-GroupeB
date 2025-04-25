<?php

/**
 * Navigation bar component pour l'admin
 */

// Inclure le modèle User si la variable n'est pas déjà définie
if (!isset($nbComptesEnAttente)) {
    require_once __DIR__ . '/../../Model/B5/User.php';
    $nbComptesEnAttente = User::countUtilisateursEnAttente();
}
?>

<!-- Checkbox pour contrôler l'affichage du menu -->
<input type="checkbox" id="menu-toggle-checkbox" class="menu-toggle-checkbox">

<!-- Logo dans le coin supérieur gauche -->
<div class="logo-container">
    <a href="<?= BASE_URL ?>/AccueilAdmin.php">
        <img src="<?= BASE_URL ?>/Assets/B5/logo.png" alt="logo institut albatros" class="site-logo">
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

<!-- Cercles décoratifs (images SVG) -->
<img src="https://www.albatros-asbl.be/wp-content/uploads/assets/little-circle-orange.svg" alt="cercle jaune" class="decorative-circle yellow-circle">
<img src="https://www.albatros-asbl.be/wp-content/uploads/assets/little-circle-green.svg" alt="cercle vert" class="decorative-circle green-circle">

<!-- Menu navigation -->
<div class="navbar-wrapper">
    <nav id="main-nav">
        <ul>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle">Demandes</a>
                <div class="dropdown-menu demandes-menu">
                <a href="<?= BASE_URL ?>/ListeDemandes"">Gérer les demandes</a>
                    <a href="#">Créer une demande d'intervention</a>
                    <a href="#">Crées une récurrence</a>
                </div>
            </li>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle">Gestion</a>
                <div class="dropdown-menu gestion-menu">
                    <a href="#">Gérer les utilisateurs</a>
                    <a href="#">Gérer les sites</a>
                    <a href="#">Gérer les bâtiments</a>
                    <a href="#">Gérer les endroits</a>


                </div>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/inscriptions" class="with-badge">
                    Valider les accès
                    <span class="badge"><?= $nbComptesEnAttente ?></span>
                </a>
            </li>
            <li><a href="#">Historique</a></li>
            <li><a href="#">Feuille de route</a></li>
        </ul>
    </nav>
</div>