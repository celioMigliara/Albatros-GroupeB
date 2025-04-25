<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demandes en attente</title>
    <!-- 
        Vue: NbInscriptions.php
        Objectif: Afficher le nombre d'inscriptions en attente de validation.
        Cette page est appelée par le contrôleur AdminInscriptionController,
        qui fournit la variable $nombre contenant le résultat du COUNT().
    -->
</head>
<body>
    <!-- Titre principal de la page -->
    <h2>Nombre d'inscriptions en attente :</h2>
    
    <!-- Affichage du nombre d'utilisateurs en attente récupéré depuis le modèle -->
    <p><?= isset($nombre) ? $nombre : 'Erreur : variable non définie.' ?> utilisateur(s)</p>

    
    <!-- 
        Note: Vous pouvez enrichir cette vue en ajoutant une interface plus détaillée
        (par exemple, un tableau listant les inscriptions avec des boutons "Valider" et "Refuser")
        pour faciliter la gestion des inscriptions par l'administrateur.
    -->
</body>
</html>
