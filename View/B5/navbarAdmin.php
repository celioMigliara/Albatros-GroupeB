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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://fonts.googleapis.com/css2?family=Caveat+Brush&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarAdmin.css">

</head>

<body>
    <!-- Logo dans le coin supérieur gauche -->

 
        <div class="logo-container">
            <a href="<?= BASE_URL ?>/AccueilAdmin">
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
                   <a href=" <?= BASE_URL ?>/demande">Nouvelle(s) Demande(s)</a>
                            <a href="<?= BASE_URL ?>/recurrence">Gérer récurrence</a>
                            <a href="<?= BASE_URL ?>/LisetDemandeExporter">Liste demandes</a>
                        </div>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle">Gestion</a>
                        <div class="dropdown-menu gestion-menu">
                            <a href="<?= BASE_URL ?>/utilisateurs">Gérer les utilisateurs</a>
                            <a href="<?= BASE_URL ?>/sites">Gérer les sites</a>
                            <a href="<?= BASE_URL ?>/batiments">Gérer les bâtiments</a>
                            <a href="<?= BASE_URL ?>/lieux">Gérer les lieux</a>


                        </div>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/inscriptions" class="with-badge">
                            Valider les accès
                            <span class="badge"><?= $nbComptesEnAttente ?></span>
                        </a>
                    </li>
                    <li><a href="<?= BASE_URL ?>/historique">Historique</a></li>
                    <li><a href="<?= BASE_URL ?>/feuillederoute">Feuille de route</a></li>
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