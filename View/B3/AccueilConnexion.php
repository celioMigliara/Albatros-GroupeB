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

</body>

</html>