<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Contenu/StylesB3.css">
    <title>Mot de passe oublié</title>
</head>

<body>
    <div class="block">

        <!-- Overlay pour l'effet arriere flou-->
        <div class="overlay"></div>

        <!-- Section de gauche de la page -->
        <div class="left">

            <!-- Boutons pour la navigation entre Connexion et Inscription -->
            <div class="button">
                <a href="<?= BASE_URL ?>/connexion" class="btnConnexion">SE CONNECTER</a>
                <a href="<?= BASE_URL ?>/inscription" class="btnInscription">S'INSCRIRE</a>
            </div>

            <!-- Logo de l'application -->
            <img src="<?= BASE_URL ?>/Assets/Images/Logo/Albatros1.png" alt="Logo Albatros" class="logo_Albatros">

        </div>

        <!-- Section de droite de la page -->
        <div class="right">

            <!-- Boutons mobiles -->
            <div class="mobile-buttons">
                <a href="<?= BASE_URL ?>/inscription" class="btnInscription-mobile">S'INSCRIRE</a>
                <a href="<?= BASE_URL ?>/connexion" class="btnConnexion-mobile">SE CONNECTER</a>
            </div>

            <!-- Titre de la section de connexion -->
            <h2>MOTS DE PASSE OUBLIÉ</h2>

            <!-- Note pour indiquer que certains champs sont obligatoires -->
            <p class="required-note">Les champs marqués d'un <span class="required-asterisk">*</span> sont obligatoires</p>

            <!-- Note pour indiquer que certains champs sont obligatoires -->
            <p class="paragraphe">
                Entrez votre e-mail et cliquez sur 'Réinitialiser le mot de passe'.<br>
                Vous recevrez un lien sécurisé pour modifier votre mot de passe.
            </p>

            <!-- Formulaire de connexion -->
            <form class="formulaire" id="formulaire-reset-password" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES); ?>">

                <!-- Bouton pour revenir à la page précédente 
                 Il va falloir le styliser pour qu'il rentre
                 dans un coin et qu'il ne gene pas la navigation 
            -->
                <div class="button-back">
                    <button class="back-button-item" id="back-button">
                        <svg height="16" width="16" xmlns="http://www.w3.org/2000/svg" version="1.1"
                            viewBox="0 0 1024 1024">
                            <path
                                d="M874.690416 495.52477c0 11.2973-9.168824 20.466124-20.466124 20.466124l-604.773963 0 188.083679 188.083679c7.992021 7.992021 7.992021 20.947078 0 28.939099-4.001127 3.990894-9.240455 5.996574-14.46955 5.996574-5.239328 0-10.478655-1.995447-14.479783-5.996574l-223.00912-223.00912c-3.837398-3.837398-5.996574-9.046027-5.996574-14.46955 0-5.433756 2.159176-10.632151 5.996574-14.46955l223.019353-223.029586c7.992021-7.992021 20.957311-7.992021 28.949332 0 7.992021 8.002254 7.992021 20.957311 0 28.949332l-188.073446 188.073446 604.753497 0C865.521592 475.058646 874.690416 484.217237 874.690416 495.52477z">
                            </path>
                        </svg>
                        <span>Retour</span>
                    </button>
                </div>

                <div class="champs-normal">

                    <!-- Champ pour l'email -->
                    <div class="input-group-custom">

                        <input required type="email" id="mail_utilisateur" name="mail_utilisateur" autocomplete="off" class="input-custom" placeholder=" ">
                        <label class="user-label-custom" for="mail_utilisateur">Email<span class="required-asterisk">*</span></label>

                        <!-- Message d'erreur si le champ n'est pas rempli -->
                        <div class="error-message">Veuillez compléter ce champ obligatoire</div>

                    </div>
                </div>

                <!-- Zone contenant le bouton final -->
                <div class="zone-bouton-final">

                    <!-- Bouton pour soumettre le formulaire de connexion -->
                    <button class="cssbuttons-io-button" role="button" type="submit">

                        Envoyer le mail

                        <div class="icon">

                            <!-- Icône pour le bouton de connexion -->
                            <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M16.172 11l-5.364-5.364 1.414-1.414L20 12l-7.778 7.778-1.414-1.414L16.172 13H4v-2z" fill="currentColor"></path>
                            </svg>

                        </div>

                        <div id="popup-reset-mdp" class="popup"></div>
            </form>
        </div>
    </div>

    <script>
        // On définit la base URL depuis le PHP pour le JS
        const BASE_URL = <?= json_encode(BASE_URL) ?>;
    </script>

    <!-- Inclusion du script pour gérer les champs obligatoires -->
    <script src="<?= BASE_URL ?>/JavaScript/General.js"></script>

</body>

</html>