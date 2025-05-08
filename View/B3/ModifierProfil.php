<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Modifier Profil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB3/StylesB3.css">
    <?php if (($_SESSION['user']['role_id'] ?? null) == 1): ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarAdmin.css">
<?php else: ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarTechnicien.css">
<?php endif; ?>

</head>

<body>

<header>

    <?php if (($_SESSION['user']['role_id'] ?? null) == 1): ?>
    <?php require_once __DIR__ . '/../B5/navbarAdmin.php'; ?>
    <?php else: ?>
        <?php require_once __DIR__ . '/../B5/navbarTechnicien.php'; ?>
        <?php endif; ?>
    </header>  
    <!-- Overlay pour l'effet arriere flou-->
    <div class="overlay"></div>

    <div class="block">

        <!-- Section de gauche de la page (vide, sans animations) -->
        <div class="left">

            <img src="<?= BASE_URL ?>/Assets/B3/Albatros1.png" alt="Logo Albatros" class="logo_Albatros">
        </div>

        <!-- Section de droite de la page -->
        <div class="right">

            <!-- Titre de la </div>section de modification du profil -->
            <h2 class="title">Modifier son profil</h2>

            <!-- Formulaire de modification -->
            <form class="formulaire" id="formulaire-modification-profil" novalidate style="display: flex; flex-wrap: wrap; justify-content: center; gap: 20px;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES); ?>">

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

                <div class="champs-normal" style="display: flex; justify-content: center;">
                    <!-- Champ pour le prénom -->
                    <div class="input-group-custom">
                        <input type="text" id="prenom_utilisateur" name="prenom_utilisateur" class="input-custom" placeholder="">
                        <label class="user-label-custom" for="prenom_utilisateur">Prénom (facultatif)</label>
                    </div>


                    <!-- Champ pour le nom -->
                    <div class="input-group-custom">
                        <input type="text" id="nom_utilisateur" name="nom_utilisateur" class="input-custom"
                            placeholder="">
                        <label class="user-label-custom" for="nom_utilisateur">Nom (facultatif)</label>
                    </div>

                    <!-- Champ pour l'email -->
                    <div class="input-group-custom">
                        <input type="email" id="mail_utilisateur" name="mail_utilisateur" class="input-custom"
                            placeholder="">
                        <label class="user-label-custom" for="mail_utilisateur">Email (facultatif)</label>
                    </div>


                    <!-- Champ pour le mot de passe -->
                    <div class="input-group-custom">
                        <input type="password" id="mdp_utilisateur" name="mdp_utilisateur" class="input-custom"
                            minlength="8" placeholder="">
                        <label class="user-label-custom" for="mdp_utilisateur">Mot de passe (facultatif)</label>
                    </div>
                </div>

                <!-- Zone contenant le bouton final -->
                <div class="zone-bouton-final">

                    <!-- Bouton pour soumettre le formulaire de connexion -->
                    <button class="cssbuttons-io-button" role="button" type="submit">

                        Modifier Profil

                        <div class="icon">

                            <!-- Icône pour le bouton de connexion -->
                            <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M16.172 11l-5.364-5.364 1.414-1.414L20 12l-7.778 7.778-1.414-1.414L16.172 13H4v-2z" fill="currentColor"></path>
                            </svg>

                        </div>

                    </button>

                </div>
            </form>
        </div>
    </div>

    <script>
        // On définit la base URL depuis le PHP pour le JS
        const BASE_URL = <?= json_encode(BASE_URL) ?>;
    </script>
    
    <!-- Inclusion du script pour gérer le formulaire -->
    <script src="<?= BASE_URL ?>/JavaScript/B3/General.js"></script>
</body>

</html>