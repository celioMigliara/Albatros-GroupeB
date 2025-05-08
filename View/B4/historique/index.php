
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
    <title>Historique des modifications</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Vos styles B4 -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB4/styleB4.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB4/style.css">
        <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarAdmin.css">
       
</head>
<body>
    <!-- Navbar -->
    <header>

            <?php require_once __DIR__ . '/../../B5/navbarAdmin.php'; ?>
    </header>

    <h1 class="title">Historique des modifications</h1>

    <div class="container">
        <?php if (empty($historique)): ?>
            <p><em>Aucune modification à afficher pour l’instant.</em></p>
        <?php else: ?>
          <table class="table">
            <thead class="table-header">
                    <tr>
                        <th>Date</th>
                        <th>Ticket</th>
                        <th>Sujet</th>
                        <th>Description</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                     <tbody class="tbody">

                    <?php foreach ($historique as $h): ?>
                        <tr>
                            <td><?= htmlentities($h['date_modif']) ?></td>
                            <td><?= htmlentities($h['num_ticket']) ?></td>
                            <td><?= htmlentities($h['sujet']) ?></td>
                            <td><?= htmlentities($h['description']) ?></td>
                            <td><?= htmlentities($h['statut']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Vos scripts JS B4 -->
    <script src="<?= BASE_URL ?>/Js/scriptB4.js"></script>
</body>
</html>
