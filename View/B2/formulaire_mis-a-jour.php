<?php
require_once(__DIR__ .'/../../Secure/B2/session.php'); // Pour que la session soit démarrée et que le token soit généré
$token = generateCsrfToken();

require_once __DIR__ . '/../../Model/UserConnectionUtils.php';

if (!UserConnectionUtils::isAdminConnected()) {
    header('Location: ' . BASE_URL . "/connexion");
    exit;
    
    if (isset($_SESSION['popup_message'])) { 
    $message = $_SESSION['popup_message'];
    $success = $_SESSION['popup_success'];

    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        showPopup(" . json_encode($message) . ", " . ($success ? 'false' : 'true') . ");
    });
    </script>";

    unset($_SESSION['popup_message'], $_SESSION['popup_success']);
}
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

    <title>modifierMaintenance</title>
    <meta name="csrf-token" content="<?= htmlspecialchars($token) ?>">
</head>
<body>
    <?php require_once __DIR__ . '/../B5/navbarAdmin.php'; ?>

    <h1 class="titre">Formulaire de modification d'une maintenance</h1>
    <div class="separateur-double-ligne-B2"></div>
    <div class="frm-add-recurr">
    <form class="frmAdd-recurr" name="frmAdd" action="<?= BASE_URL ?>/maintenance/modifier/<?php echo $maintenance['id_recurrence']; ?>" method="POST">
    <input type="hidden" name="id_recurrence" value="<?php echo $maintenance['id_recurrence']; ?>">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="demo-form-row-titre">
                <label for="titre">Titre de la demande <span class="error">*</span></label><br>
                <input type="text" name="titre" id="titre" class="demo-form-field-titre" 
                value="<?php echo isset($maintenance['sujet_reccurrence']) ? htmlspecialchars($maintenance['sujet_reccurrence']) : ''; ?>" required />
            </div>

            <div class="demo-form-row">
                <label for="frequence">Fréquence <span class="error">*</span></label><br>
                <input type="number" name="frequence" id="frequence" class="demo-form-freq" 
                value="<?php echo isset($maintenance['valeur_freq_recurrence']) ? $maintenance['valeur_freq_recurrence'] : ''; ?>" />
                <select id="choix_periode" name="periode" class="demo-form-field" required>
                    <option value="">Sélectionnez une période</option>
                    <option value="jour" <?php echo (isset($maintenance['nom_unite_frequence']) && $maintenance['nom_unite_frequence'] == "jour") ? 'selected' : ''; ?>>Jours</option>
                    <option value="mois" <?php echo (isset($maintenance['nom_unite_frequence']) && $maintenance['nom_unite_frequence'] == "mois") ? 'selected' : ''; ?>>Mois</option>
                    <option value="année" <?php echo (isset($maintenance['nom_unite_frequence']) && $maintenance['nom_unite_frequence'] == "année") ? 'selected' : ''; ?>>Année</option>
                </select>
            </div>

            <div class="demo-form-row">
                <label for="date_anniversaire">Date d'anniversaire <span class="error">*</span></label><br>
                <input type="date" name="date_anniversaire" id="anniversaire" class="demo-form-field"
                value="<?php echo isset($maintenance['date_anniv_recurrence']) ? $maintenance['date_anniv_recurrence'] : ''; ?>" required />
            </div>

            <div class="demo-form-row">
                <label for="delai">Délai de rappel</label><br>
                <input type="number" name="delai" id="delai" class="demo-form-freq"
                value="<?php echo isset($maintenance['valeur_rappel_recurrence']) ? $maintenance['valeur_rappel_recurrence'] : ''; ?>"/>
                <select id="choix_delai" name="periode_delai" class="demo-form-field">
                    <option value="">Sélectionnez une unité</option>
                    <option value="jour" <?php echo (isset($maintenance['nom_unite_rappel']) && $maintenance['nom_unite_rappel'] == 'jour') ? 'selected' : ''; ?>>Jours</option>
                    <option value="mois" <?php echo (isset($maintenance['nom_unite_rappel']) && $maintenance['nom_unite_rappel'] == 'mois') ? 'selected' : ''; ?>>Mois</option>
                </select>
            </div>

            <div class="demo-form-row">
                <label for="choixSite">Site <span class="error">*</span></label><br>
                <select id="choixSite" name="site" class="demo-form-field" required data-selected="<?= $maintenance['id_site'] ?>">
                <option value="">Sélectionnez un site</option>
                    <?php
                        $sites = $pdo->query("SELECT id_site, nom_site FROM site")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($sites as $site) {
                            $selected = ($site['id_site'] == $maintenance['id_site']) ? 'selected' : '';
                            echo "<option value='{$site['id_site']}' $selected>{$site['nom_site']}</option>";
                        }
                    ?>
                </select>
            </div>

            <div class="demo-form-row">
                <label for="choixBatiment">Bâtiment <span class="error">*</span></label><br>
                <select id="choixBatiment" name="batiment" class="demo-form-field" required data-selected="<?= $maintenance['id_batiment'] ?>">
                    <option value="">Sélectionnez un bâtiment</option>
                    <?php  $batiments = $pdo->query("SELECT id_batiment, nom_batiment FROM batiment WHERE id_site = '$maintenance[id_site]'")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($batiments as $batiment):?>
                        <option value="<?= $batiment['id_batiment'] ?>" <?= $batiment['id_batiment'] == $maintenance['id_batiment'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($batiment['nom_batiment']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        
            <div class="demo-form-row">
                <label for="choixLieu">Lieu <span class="error">*</span></label><br>
                <select id="choixLieu" name="lieu" class="demo-form-field" required data-selected="<?= $maintenance['id_lieu'] ?>">
                <option value="">Sélectionnez un lieu</option>
                    <?php
                        $lieux = $pdo->query("SELECT id_lieu, nom_lieu FROM lieu WHERE id_batiment = '$maintenance[id_batiment]'")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($lieux as $lieu) {
                            $selected = ($lieu['id_lieu'] == $maintenance['id_lieu']) ? 'selected' : '';
                            echo "<option value='{$lieu['id_lieu']}' $selected>{$lieu['nom_lieu']}</option>";
                        }
                    ?>
                </select>
            </div>

            <div class="demo-form-row-descr">
                <label for="desc_maint">Description de l'intervention</label><br>
                <input type="text" name="desc_maint" id="desc_maint" class="demo-form-des" placeholder="Description de l'intervention"
                value="<?php echo isset($maintenance['desc_recurrence']) ? htmlspecialchars($maintenance['desc_recurrence']) : ''; ?>"/>
            </div>

            <div class="demo-form-row">
                <a href="<?= BASE_URL ?>/recurrence" class="retour_liste_recurr">Retour à la liste</a>
            </div>
           
            <div class="demo-form-row">
                <button type="button" class="supp-btn_recurr" data-id="<?php echo $maintenance['id_recurrence']; ?>">Supprimer</button>
            </div>

            <div class="demo-form-row">
                <input name="add_mainte" type="submit" value="Modifier" class="add2_recurr">
            </div>

          
        </form>

        <!-- Pop-up de succès -->
        <div id="popup" class="popup">
            <span id="popup-close" class="popup-close">&times;</span>
            <p id="popup-message"></p>
            <button class="popup-ok-btn" id="popup-ok-btn">OK</button>
        </div>

        <!-- Pop-up d’erreur -->
        <div id="error-popup" class="popup error">
            <span id="error-popup-close" class="popup-close">&times;</span>
            <p id="error-message"></p>
            <button class="popup-ok-btn" id="error-ok-btn">OK</button>
        </div>

        <!-- Pop-up de confirmation de suppression -->
        <div id="popupDel" class="popupDel">
                <span id="popup-close-sup" class="popup-close">&times;</span>
                <p id="popup-message-sup"></p>
                <button class="popup-ok-btn-sup" id="popup-ok-btn-sup">Supprimer la maintenance</button>
                <button class="popup-no-btn-sup" id="popup-no-btn-sup">Revenir en arrière</button>
            </div>  
        </div>
    
        <script>
    const BASE_URL = "<?= BASE_URL ?>";
    </script>

    <script src="<?= BASE_URL ?>/Javascript/B2/script.js"></script>

</body>
</html>