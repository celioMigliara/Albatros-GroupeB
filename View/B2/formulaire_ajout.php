<?php
require_once(__DIR__ . '/../../Secure/B2/session.php'); // Pour que la session soit démarrée et que le token soit généré
$token = generateCsrfToken();

require_once __DIR__ . '/../../Model/UserConnectionUtils.php';

if (!UserConnectionUtils::isAdminConnected()) {
    header('Location: ' . BASE_URL . "/connexion");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB2/style_maintenance.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarAdmin.css">

    <title>Ajout</title>

</head>

<body>

    <?php require_once __DIR__ . '/../B5/navbarAdmin.php'; ?>

    <h1 class="titre">Formulaire d'ajout de maintenance</h1>
    <div class="separateur-double-ligne-B2"></div>
    <div class="frm-add-recurr">
    <form class="frmAdd-recurr" name="frmAdd" action="<?= BASE_URL ?>/maintenance/ajouter" method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="demo-form-row-titre">
                <label for="titre">Titre de la demande <span class="error">*</span></label><br>
                <input type="text" name="titre" id="titre" class="demo-form-field-titre" required />
            </div>

            <div class="demo-form-row">
                <label for="frequence">Fréquence <span class="error">*</span></label><br>
                <input type="number" name="frequence" id="frequence" class="demo-form-freq" />
                <select id="choix_periode" name="periode" class="demo-form-field" required>
                    <option value="">Sélectionnez une période</option>
                    <option value="jour">Jours</option>
                    <option value="mois">Mois</option>
                    <option value="année">Année</option>
                </select>
            </div>

            <div class="demo-form-row">
                <label for="date_anniversaire">Date d'anniversaire <span class="error">*</span></label><br>
                <input type="date" name="date_anniversaire" id="anniversaire" class="demo-form-field" required>
            </div>

            <div class="demo-form-row">
                <label for="delai">Délai de rappel</label><br>
                <input type="number" name="delai" id="delai" class="demo-form-freq" />
                <select id="choix_delai" name="periode_delai" class="demo-form-field">
                    <option value="">Sélectionnez une unité</option>
                    <option value="jour">Jours</option>
                    <option value="mois">Mois</option>
                </select>
            </div>

            <div class="demo-form-row">
                <label for="choixSite">Site <span class="error">*</span></label><br>
                <select id="choixSite" name="site" class="demo-form-field" required>
                    <option value="">Sélectionnez un site</option>
                </select>
            </div>

            <div class="demo-form-row">
                <label for="choixBatiment">Bâtiment <span class="error">*</span></label><br>
                <select id="choixBatiment" name="batiment" class="demo-form-field" required>
                    <option value="">Sélectionnez un bâtiment</option>
                </select>
            </div>

            <div class="demo-form-row">
                <label for="choixLieu">Lieu <span class="error">*</span></label><br>
                <select id="choixLieu" name="lieu" class="demo-form-field" required>
                    <option value="">Sélectionnez un lieu</option>
                </select>
            </div>

            <div class="demo-form-row-descr">
                <label for="desc_maint">Description de l'intervention</label><br>
                <input type="text" name="desc_maint" id="desc_maint" class="demo-form-des" placeholder="Description de l'intervention" />
            </div>

            <div class="demo-form-row">
                <a href="<?= BASE_URL ?>/recurrence" class="retour_liste_recurr">Retour à la liste</a>
            </div>

            <div class="demo-form-row">
                <input name="add_mainte" type="submit" value="Confirmer" class="add2_recurr">
            </div>

        </form>

    <script>
        const BASE_URL = "<?= BASE_URL ?>";
    </script>

    <script src="<?= BASE_URL ?>/JavaScript/B2/script.js"></script>

</body>

</html>
