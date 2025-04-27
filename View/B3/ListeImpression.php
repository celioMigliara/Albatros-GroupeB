<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

  <title>Liste d'impression des techniciens présents</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB3/FeuilleRoute.css">
</head>

<body data-page="ListeImpression">
  <div class="block_taches">
    <div class="overlay"></div>
    <h1>Liste d'impression</h1>
    <div>    <button id="openTechPopup" class="back-btn">Ajouter des techniciens</button>
    <button onclick="window.location.href='index.php?action=voirTachesParTechnicien'" class="back-btn">Visualiser les taches de techniciens</button>
  </div>

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
  </div>
  <script src="./JavaScript/ListeImpression.js"></script>

</body>

</html>
