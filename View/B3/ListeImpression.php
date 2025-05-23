<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

  <title>Liste d'impression des techniciens présents</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB3/FeuilleRoute.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarAdmin.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB3/ListeImpression.css">

</head>

<body data-page="ListeImpression">
  <?php require_once __DIR__ . '/../B5/navbarAdmin.php'; ?>


  <h1>Liste d'impression</h1>
 <div class="separateur-double-ligne-B2"></div>
  <div class="block_taches">
    
    <div class="overlay"></div>

    <div>
      <button id="openTechPopup" class="back-btn">Ajouter des techniciens</button>
      <button class="back-btn" onclick="window.location.href='<?= BASE_URL ?>/feuillederoute/liste/taches'">
        Visualiser les tâches des techniciens
      </button>
    </div>

    <div class="table-containerr">
      <table id="printTable">
        <thead>
          <tr>
            <th>Technicien</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- Le contenu sera généré par JS -->
        </tbody>
      </table>
    </div>
  </div>


  <!-- Popup -->
  <div id="techPopup" style="display:none;">
    <h2>Liste des techniciens</h2>
    <table id="techListTable">
      <thead>
        <tr>
          <td>Nom</td>
          <td>Action</td>
        </tr>
      </thead>
      <tbody>

      </tbody>
    </table>
    <button id="closeTechPopup">Fermer</button>
  </div>

  <script>
    // On définit la base URL depuis le PHP pour le JS
    const BASE_URL = <?= json_encode(BASE_URL) ?>;
  </script>

  <script src="<?= BASE_URL ?>/JavaScript/B3/ListeImpression.js"></script>

</body>

</html>