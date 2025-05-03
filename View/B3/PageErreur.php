<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB3/StylesB3.css">
    <title>Erreur</title>
</head>

<body>
    <div class="block">

        <!-- Overlay pour l'effet arriere flou-->
        <div class="overlay"></div>

        <!-- Section de gauche de la page -->
        <div class="left">

            <!-- Logo de l'application -->
            <img src="<?= BASE_URL ?>/Assets/B3/Albatros1.png" alt="Logo Albatros" class="logo_Albatros">

        </div>

        <!-- Section de droite de la page -->
        <div class="right">
           
        <h1><?= $errorMsg->title ?? 'Erreur'?></h1>
        <p><?= $errorMsg->message ?? 'Une erreur est survenue. Veuillez réessayer plus tard.' ?></p>

        </div>
    </div>

</body>

</html>