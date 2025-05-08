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
    <style>

    </style>

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
                                <a href="<?= BASE_URL ?>/utilisateurs/desactiver/<?= $u['user_id'] ?>"
                                    class="btn btn-warning">Désactiver</a>
                            <?php else: ?>
                                <a href="<?= BASE_URL ?>/utilisateurs/activer/<?= $u['user_id'] ?>"
                                    class="btn btn-success">Activer</a>
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


    </div>
    <script src="<?= BASE_URL ?>/Js/scriptB4.js"></script>
</body>

</html>