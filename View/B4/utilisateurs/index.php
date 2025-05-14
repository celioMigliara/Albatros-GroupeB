<?php
require_once __DIR__ . '/../../../Model/UserConnectionUtils.php';

if (!UserConnectionUtils::isAdminConnected()) {
    header('Location: ' . BASE_URL . "/connexion");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Liste des utilisateurs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Vos styles B4 -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB4/styleB4.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB4/style.css">

    <style>
  /* Modal overlay */
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
  }
  /* Modal box */
  .modal {
    background: #fff;
    padding: 1.5rem;
    border-radius: 0.5rem;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    text-align: center;
  }
  .modal h2 {
    margin-top: 0;
  }
  .modal-buttons {
    margin-top: 1rem;
    display: flex;
    justify-content: space-around;
  }
  .modal-buttons button {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 0.25rem;
    cursor: pointer;
  }
  .btn-cancel { background: #ccc; }
  .btn-confirm { background: #e74c3c; color: #fff; }

  /* Toast notification */
  .toast {
    position: fixed;
    bottom: 1rem;
    left: 50%;
    transform: translateX(-50%);
    background: #333;
    color: #fff;
    padding: 0.75rem 1.5rem;
    border-radius: 0.25rem;
    display: none;
    z-index: 1001;
  }
</style>

    <!-- Style de la navbar selon le rôle -->
    <?php if ($_SESSION['user']['role_id'] == 1): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarAdmin.css">
    <?php endif; ?>
</head>

<body>
    <!-- Navbar -->
    <header>
        <?php if ($_SESSION['user']['role_id'] == 1): ?>
            <?php require_once __DIR__ . '/../../B5/navbarAdmin.php'; ?>
        <?php endif; ?>
    </header>

    <h1 class="title">Liste des utilisateurs</h1>

    <div class="container-B4">
      
    <!-- Message d'erreur éventuel -->
    <?php if (isset($_GET['error']) && $_GET['error'] === 'email_exists'): ?>
        <div class="alert alert-danger">
            Cet email est déjà utilisé par un autre utilisateur.
        </div>
    <?php endif; ?>

 

        <!-- Formulaire de tri -->
        <br>
        <form method="get" style="margin-bottom:1em;">
            <label for="tri">Trier par :</label>
            <select name="tri" id="tri" onchange="this.form.submit()">
                <option value="nom" <?= ($tri === 'nom') ? 'selected' : '' ?>>Nom</option>
                <option value="batiment" <?= ($tri === 'batiment') ? 'selected' : '' ?>>Bâtiment</option>
            </select>
            <!-- Conserver la page actuelle -->
            <input type="hidden" name="page" value="<?= $page ?>">
        </form>

        <!-- Tableau des utilisateurs -->
        <table class="table">
            <thead class="table-header">
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Bâtiments</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="tbody">
                <?php foreach ($utilisateurs as $u): ?>
                    <tr>
                        <td><?= htmlentities($u['user_id']) ?></td>
                        <td><?= htmlentities($u['nom']) ?></td>
                        <td><?= htmlentities($u['prenom']) ?></td>
                        <td><?= htmlentities($u['email']) ?></td>
                        <td><?= htmlentities($u['role']) ?></td>
                        <td>
                            <?= htmlspecialchars(
                                $u['batiment'] ??  // pour tri par bâtiment
                                    $u['batiments'] ??  // pour tri par nom
                                    'Aucun'
                            ) ?>
                        </td>
                        <td>

                            <a href="<?= BASE_URL ?>/utilisateurs/modifier/<?= $u['user_id'] ?>"
                                class="btn btn-primary">Modifier</a>

                            <?php if ($u['actif']): ?>
                                <a href="<?= BASE_URL ?>/utilisateurs/desactiver/<?= $u['user_id'] ?>" class="btn btn-warning" data-userid="<?= $u['user_id'] ?>">Désactiver</a>
                            <?php else: ?>
                                <a href="<?= BASE_URL ?>/utilisateurs/activer/<?= $u['user_id'] ?>" class="btn btn-success" data-userid="<?= $u['user_id'] ?>">Activer</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->

        <ul class="pagination">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= BASE_URL ?>/utilisateurs?page=<?= $page - 1 ?>&tri=<?= urlencode($tri) ?>">
                        « Précédent
                    </a>
                </li>
            <?php endif; ?>

            <?php for ($p = 1; $p <= $pages; $p++): ?>
                <li class="page-item">
                    <?php if ($p === $page): ?>
                        <span class="page-link current"><?= $p ?></span>
                    <?php else: ?>
                        <a class="page-link" href="<?= BASE_URL ?>/utilisateurs?page=<?= $p ?>&tri=<?= urlencode($tri) ?>">
                            <?= $p ?>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endfor; ?>

            <?php if ($page < $pages): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= BASE_URL ?>/utilisateurs?page=<?= $page + 1 ?>&tri=<?= urlencode($tri) ?>">
                        Suivant »
                    </a>
                </li>
            <?php endif; ?>
        </ul>

        <!-- Modal de confirmation -->
<div class="modal-overlay" id="confirmModal">
  <div class="modal">
    <h2 id="modalTitle">Confirmation</h2>
    <p id="modalMessage">Êtes-vous sûr ?</p>
    <div class="modal-buttons">
      <button class="btn-cancel" id="modalCancel">Annuler</button>
      <button class="btn-confirm" id="modalConfirm">Oui, je confirme</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>


    </div>
  
    <script>
document.addEventListener('DOMContentLoaded', () => {
  const modal      = document.getElementById('confirmModal');
  const titleEl    = document.getElementById('modalTitle');
  const messageEl  = document.getElementById('modalMessage');
  const btnCancel  = document.getElementById('modalCancel');
  const btnConfirm = document.getElementById('modalConfirm');
  const toast      = document.getElementById('toast');
  let targetHref, actionTextdesc;

  // Ouvre le modal pour chaque lien Désactiver/Activer
  document.querySelectorAll('a.btn-warning, a.btn-success').forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      targetHref  = link.href;
      actionTextdesc  = link.classList.contains('btn-warning') 
                    ? 'désactiver' : 'activer';
      // Personnalise le texte
      titleEl.textContent   = `Confirmation`;
      messageEl.textContent = 
        `Êtes-vous sûr de vouloir ${actionTextdesc} l’utilisateur #${link.dataset.userid}?`;
      modal.style.display = 'flex';
    });
  });

  // Annuler
  btnCancel.addEventListener('click', () => {
    modal.style.display = 'none';
  });

  // Confirmer
  btnConfirm.addEventListener('click', () => {
    modal.style.display = 'none';
    // Lance la désactivation/activation
    fetch(targetHref).then(() => {
      // Après un petit délai, on recharge la liste
      setTimeout(() => window.location.reload(), 500);
    });
  });

  // Fonction toast
  function showToast(msg) {
    toast.textContent = msg;
    toast.style.display = 'block';
    setTimeout(() => {
      toast.style.opacity = '0';
      setTimeout(() => {
        toast.style.display = 'none';
        toast.style.opacity = '1';
      }, 500);
    }, 2000);
  }
});
</script>

</body>

</html>