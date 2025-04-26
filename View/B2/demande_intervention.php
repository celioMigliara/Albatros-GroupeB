<?php
require_once(__DIR__ . "/../../Secure/B2/session_secureB2.php");

?>

<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Demande d'intervention</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB2/StyleB2.css">
  <?php if ($_SESSION['user_role'] == 1): ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarAdmin.css">
<?php else: ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarTechnicien.css">
<?php endif; ?>
</head>

<body class="page-demande-B2">
<header>

    <?php if ($_SESSION['user_role'] == 1): ?>
    <?php require_once __DIR__ . '/../B5/navbarAdmin.php'; ?>
    <?php else: ?>
        <?php require_once __DIR__ . '/../B5/navbarTechnicien.php'; ?>
        <?php endif; ?>
    </header>  
    <noscript>
    <div class="noscript-message-B2">
      JavaScript est désactivé, veuillez l'activer pour une meilleure expérience.
    </div>
  </noscript>

  <div class="container-B2">
  
    <header>
      <h1 class="title-B2">Demande d'intervention</h1>
      <div class="separateur-double-ligne-B2"></div>
    </header>

    <div class="formulaire-container-B2">

      <form id="form-demande" method="POST" enctype="multipart/form-data" novalidate>
        <!-- Sujet -->
        <fieldset>
          <legend>Sujet<span class="obligatoire-B2">*</span></legend>

          <div class="formulaire-champs-B2">

            <textarea name="sujet" id="sujet" maxlength="50" placeholder="changer ampoule"
              class="input-B2" required><?= htmlspecialchars($_POST['sujet'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            <small class="caracteres-count-B2"><span id="caracteresCount">0</span>/ 50 caractères</small>
            <div class="erreur-champs-B2"></div>
          </div>
        </fieldset>



        <!-- Site, Bâtiment, Lieu -->
        <fieldset class="form-row-B2">
          <legend>Localisation<span class="obligatoire-B2">*</span></legend>


          <div class="formulaire-champs-B2">
            <label class="label-B2" for="site">Site <span class="obligatoire-B2">*</span></label>
            <select name="site" id="site" required class="input-B2">
              <option value="">SITE</option>
            </select>

          </div>

          <div class="formulaire-champs-B2">
            <label class="label-B2" for="batiment">Bâtiment <span class="obligatoire-B2">*</span></label>
            <select name="batiment" id="batiment" required class="input-B2">
              <option value="">BATIMENT</option>
            </select>

          </div>

          <div class="formulaire-champs-B2">
            <label class="label-B2" for="lieu">Lieu <span class="obligatoire-B2" 2>*</span></label>
            <select name="lieu" id="lieu" required class="input-B2">
              <option value="">LIEU</option>
            </select>
          </div>

        </fieldset>


        <!-- Description -->
        <fieldset class="formulaire-champs-B2">
          <legend>Description</legend>
          <textarea name="description" id="description" maxlength="512" placeholder="Description de l'intervention"
            class="input-B2"><?= htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
          <small><span id="caracteresCountDescription">0</span>/ 512 caractères</small>
        </fieldset>


        <fieldset class="formulaire-champs-B2 fieldset-petite-section-B2">
          <legend>Pièce jointe</legend>

          <input type="file" name="piece_jointe[]" id="piece_jointe" multiple hidden accept=".jpg, .jpeg, .png, .pdf, .mp4">

          <button type="button" class="btn-upload-B2" onclick="document.getElementById('piece_jointe').click();">
            📂 Sélectionner un ou plusieurs fichiers
          </button>

          <!-- Zone dynamique où les fichiers sélectionnés seront listés -->
          <div id="file-preview-zone" class="NomFichier-B2">Aucun fichier sélectionné</div>

          <div id="erreur-fichier" class="erreur-champs-B2"></div>
        </fieldset>



        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-buttons-B2">
          <button type="reset" class="btn-B2 btn-Annuler-B2">Annuler</button>
          <button type="submit" class="btn-B2 btn-Envoyer-B2">Envoyer</button>
        </div>


        <div id="message"></div>
      </form>
    </div>
  </div>

  <div id="popup-overlay-B2" class="popup-overlay"></div>

  <div id="popup" class="popup-B2">
    <div class="popup-content-B2">
      <img src="<?= BASE_URL ?>/Assets/B2/Albatros.jpg" alt="Logo popup" class="popup-logo-B2" data-effect="mfp-move-horizontal">
      <p id="popup-message"></p>
      <button onclick="closePopup()">FERMER</button>
    </div>
  </div>

  <script>
    const BASE_URL = "<?= BASE_URL ?>";
  </script>
  <!-- Description
  <script src="../script/script.js"></script>-->
  <script src="<?= BASE_URL ?>/Javascript/B2/FormulaireJsB2.js"></script>

</body>

</html>