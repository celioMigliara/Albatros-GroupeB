<?php
// Configurer les paramètres du cookie de session
session_set_cookie_params([
    'httponly' => true,
    'secure' => false, // à activer uniquement en HTTPS
    'samesite' => 'Strict'
]);

// Démarrer la session
session_start();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
</head>

<body>

    <?php
    if (isset($_SESSION['user']['id'])) {
        echo "<h2>Bienvenue sur la page d'accueil. <br>
        Vous êtes actuellement connectés en tant que ID : " .
            $_SESSION['user']['id'] . ". <br>Avec le nom suivant : " .
            $_SESSION['user']['prenom'] ?? "(Prenom invalide)" .
            "</h2>";
    } else {
        echo "Vous n'etes pas connectés";
    }
    ?>

    <h1>Sélectionnez la page que vous souhaitez</h1>

    <ul>
        <p>Fonctionnalités en développement :</p>
        <li><a href="<?= BASE_URL ?>/deconnexion">Se déconnecter</a></li>
        <br>

        <p>Fonctionnalités en production :</p>
        <li><a href="<?= BASE_URL ?>/inscription">Inscription</a></li>
        <li><a href="<?= BASE_URL ?>/connexion">Connexion</a></li>

        <br>

        <li><a href="<?= BASE_URL ?>/motdepasse/reset">Mot de passe oublié</a></li>
        <li><a href="<?= BASE_URL ?>/motdepasse/changer">Changer de mot de passe</a></li>

        <br>

        <li><a href="<?= BASE_URL ?>/profil/modifier">Modifier le profil</a></li>

        <br>
        <li><a href="<?= BASE_URL ?>/feuillederoute/liste/techniciens">Liste des techniciens</a></li>
        <li><a href="<?= BASE_URL ?>/feuillederoute/liste/taches">Liste des tâches par technicien</a></li>
        <li><a href="<?= BASE_URL ?>/feuillederoute/liste/impression">Liste d'impression</a></li>
    </ul>

</body>

</html>