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
    <title>Modifier l'utilisateur #<?= htmlentities($utilisateur['user_id']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Vos styles B4 -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB4/styleB4.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB4/style.css">
    <!-- Style de la navbar selon le rôle -->
        <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarAdmin.css">
 
</head>
<body>
    <header>
            <?php require_once __DIR__ . '/../../B5/navbarAdmin.php'; ?>
      
    </header>

    <h1 class="title">Modifier l'utilisateur #<?= htmlentities($utilisateur['user_id']) ?></h1>

    <div class="formedit">
        <!-- Affichage d'une erreur éventuelle -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlentities($error) ?></div>
        <?php endif; ?>
        <br>
        <form method="post" action="<?= BASE_URL ?>/utilisateurs/modifier/<?= $utilisateur['user_id'] ?>">
            <label for="prenom">Prénom</label>
            <div class="form-row">
                <input type="text" maxlength="12" id="prenom" name="prenom" required
                       value="<?= htmlentities($utilisateur['prenom']) ?>">
            </div>

            <label for="nom">Nom</label>
            <div class="form-row">
                <input type="text" maxlength="12" id="nom" name="nom" required
                       value="<?= htmlentities($utilisateur['nom']) ?>">
            </div>

            <label for="email">Email</label>
            <div class="form-row">
                <input class="emailformedit" type="email" id="email" name="email" required
                       value="<?= htmlentities($utilisateur['email']) ?>">
            </div>
            
            <label for="role">Role</label>
            <div class="form-row">
                <select id="role" name="role" required>
                    <?php foreach (\Model\B4\User::getAllRoles() as $r): ?>
                        <option value="<?= $r['id_role'] ?>"
                            <?= $r['id_role'] === $utilisateur['role'] ? 'selected' : '' ?>>
                            <?= htmlentities($r['nom_role']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
    <label for="batimentDropdown">Bâtiments</label>
  <div class="form-row">
        <div class="dropdown-checkbox">
                <button type="button" id="batimentButton" class="dropdown-btn">
                    Sélectionner des bâtiments <span class="arrow">▾</span>
                </button>
                <div id="batimentDropdown" class="dropdown-content">
                    <?php foreach ($allBatiments as $bat): ?>
                    <label class="dropdown-item">
                        <input
                        type="checkbox"
                        name="batiments[]"
                        value="<?= $bat['id_batiment'] ?>"
                        <?= in_array($bat['id_batiment'], $assignedBatiments) ? 'checked' : '' ?>
                        >
                        <?= htmlspecialchars($bat['nom_batiment']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
                <br>
                <button type="submit" class="save">Enregistrer</button>
                <a href="<?= BASE_URL ?>/utilisateurs" class="delete">Annuler</a>
                </form><br>
 </div>

        
</div>
   

    <script>
  document.getElementById('batimentButton').addEventListener('click', function(e) {
    const container = this.closest('.dropdown-checkbox');
    container.classList.toggle('open');
  });

  // Fermer si on clique en dehors
  document.addEventListener('click', function(e) {
    const dropdown = document.querySelector('.dropdown-checkbox');
    if (!dropdown.contains(e.target)) {
      dropdown.classList.remove('open');
    }
  });
</script>


    <style>
        /* Conteneur */
    .dropdown-checkbox{
        width: 100%;
    margin-top: 6px;
    margin-bottom: 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    box-sizing: border-box;
    }
.emailformedit{
    width: 100%;
    padding: 8px;
    margin-top: 6px;
    margin-bottom: 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    box-sizing: border-box;
}

.formedit{
    width: 35%;
    justify-content: center;
    align-items: center;
    background-color: #f8f9fa;
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    max-width: 900px;
    margin: 0 auto 0;
    padding: 0 20px;
    position: relative;
}

.dropdown-checkbox {
  position: relative;
  display: inline-block;
  width: 100%;
}

/* Bouton */
.dropdown-btn {
  width: 100%;
  padding: 8px 12px;
  text-align: left;
  background: #fff;
  border: 1px solid #ccc;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

/* Flèche */
.dropdown-btn .arrow {
  transition: transform 0.2s;
}

/* Contenu caché */
.dropdown-content {
  display: none;
  position: absolute;
  background: #fff;
  border: 1px solid #ccc;
  width: 100%;
  max-height: 200px;
  overflow-y: auto;
  z-index: 100;
  margin-top: 2px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

/* Item */
.dropdown-item {
  display: block;
  padding: 6px 10px;
}

/* Hover */
.dropdown-item:hover {
  background: #f0f0f0;
}

/* Ouvre le dropdown */
.dropdown-checkbox.open .dropdown-content {
  display: block;
}

/* Rotate arrow */
.dropdown-checkbox.open .dropdown-btn .arrow {
  transform: rotate(180deg);
}

    </style>

    <!-- Vos scripts JS B4 -->
    <script src="<?= BASE_URL ?>/Js/scriptB4.js"></script>
</body>
</html>