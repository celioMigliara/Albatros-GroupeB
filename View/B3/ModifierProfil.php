<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Modifier Profil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB3/StylesB3.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB3/ModifierProfil.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB2/style_maintenance.css">
    <?php if (($_SESSION['user']['role_id'] ?? null) == 1): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarAdmin.css">
    <?php else: ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarTechnicien.css">
    <?php endif; ?>
</head>

<body style="background: none !important;">

    <header>

        <?php if (($_SESSION['user']['role_id'] ?? null) == 1): ?>
            <?php require_once __DIR__ . '/../B5/navbarAdmin.php'; ?>
        <?php else: ?>
            <?php require_once __DIR__ . '/../B5/navbarTechnicien.php'; ?>
        <?php endif; ?>
    </header>

    <h1 class="title">Modifier Profil</h1>
    <div class="separateur-double-ligne-B2"></div>

    <div class="block" style="max-height: 65%; max-width: 65%;">

        <div class="left">
            <section class="profile-summary" style="display: flex; align-items: center; gap: 16px; margin: 30px auto; width: fit-content;">
                <img src="<?= BASE_URL ?>/Assets/B3/LogoUserVert.png" alt="Icone utilisateur" style="width: 65px; height: 65px;">
                <div>
                    <p class ="TextNameFirstName"><?php echo htmlspecialchars(strtoupper($userNom) . ' ' . ucfirst(strtolower($userPrenom))); ?></p>
                    <p class ="TextEmail"><?php echo htmlspecialchars($userEmail); ?></p>
                </div>
            </section>
        </div>

        <div class="right-panel">
            <!-- Titre de la </div>section de modification du profil -->
            <h2 class="title">Modifier son profil</h2>

            <!-- Formulaire de modification -->
            <form class="formulaire" id="formulaire-modification-profil" novalidate style="display: flex; flex-wrap: wrap; justify-content: center; gap: 20px;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES); ?>">

                <div class="champs-normal" style="display: flex; justify-content: center;">
                    <!-- Champ pour le prénom -->
                    <div class="input-group-custom">
                        <input type="text" id="prenom_utilisateur" name="prenom_utilisateur" class="input-custom"
                            value="<?php echo htmlspecialchars($userPrenom); ?>">
                        <label class="user-label-custom" for="prenom_utilisateur">Prénom (facultatif)</label>
                        <button type="button" class="reset-input-btn" data-target="prenom_utilisateur">✖</button>
                    </div>


                    <!-- Champ pour le nom -->
                    <div class="input-group-custom">
                        <input type="text" id="nom_utilisateur" name="nom_utilisateur" class="input-custom"
                            value="<?php echo htmlspecialchars($userNom); ?>">
                        <label class="user-label-custom" for="nom_utilisateur">Nom (facultatif)</label>
                        <button type="button" class="reset-input-btn" data-target="nom_utilisateur">✖</button>
                    </div>

                    <!-- Email principal -->
                    <div class="input-group-custom">
                        <input type="email" id="mail_utilisateur" name="mail_utilisateur" class="input-custom"
                            value="<?php echo htmlspecialchars($userEmail); ?>">
                        <label class="user-label-custom" for="mail_utilisateur">Email (facultatif)</label>
                        <button type="button" class="reset-input-btn" data-target="mail_utilisateur">✖</button>
                    </div>

                    <!-- Confirmation email -->
                    <div class="input-group-custom" id="confirm-mail-group" style="display: none;">
                        <input type="email" id="confirm_mail_utilisateur" name="confirm_mail_utilisateur" class="input-custom" placeholder="">
                        <label class="user-label-custom" for="confirm_mail_utilisateur">Confirmer Email</label>
                    </div>

                    <!-- Nouveau mot de passe -->
                    <div class="input-group-custom">
                        <input type="password" id="mdp_utilisateur" name="mdp_utilisateur" class="input-custom" minlength="8" placeholder="">
                        <label class="user-label-custom" for="mdp_utilisateur">Nouveau mot de passe (facultatif)</label>
                        <button type="button" class="reset-input-btn" data-target="mdp_utilisateur">✖</button>
                    </div>

                    <!-- Confirmation mot de passe -->
                    <div class="input-group-custom" id="confirm-password-group" style="display: none;">
                        <input type="password" id="confirm_mdp_utilisateur" name="confirm_mdp_utilisateur" class="input-custom" minlength="8" placeholder="">
                        <label class="user-label-custom" for="confirm_mdp_utilisateur">Confirmer mot de passe</label>
                    </div>
                </div>

                <!-- Zone contenant le bouton final -->
                <div class="zone-bouton-final">

                    <button id="modifier-profil-btn" role="button" type="submit" disabled>
                        Modifier Profil
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
    <script src="<?= BASE_URL ?>/JavaScript/B3/ModifierProfil.js"></script>
</body>

</html>