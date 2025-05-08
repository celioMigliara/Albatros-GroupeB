<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des modifications</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Vos styles B4 -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB4/styleB4.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB4/style.css">
    <!-- Style de la navbar selon le rôle -->
    <?php if ($_SESSION['user_role'] == 1): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarAdmin.css">
    <?php else: ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarTechnicien.css">
    <?php endif; ?>
</head>
<body>
    <!-- Navbar -->
    <header>
        <?php if ($_SESSION['user_role'] == 1): ?>
            <?php require_once __DIR__ . '/../../B5/navbarAdmin.php'; ?>
        <?php else: ?>
            <?php require_once __DIR__ . '/../../B5/navbarTechnicien.php'; ?>
        <?php endif; ?>
    </header>

    <h1 class="title">Historique des modifications</h1>

    <div class="container">
        <?php if (empty($historique)): ?>
            <p><em>Aucune modification à afficher pour l’instant.</em></p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Ticket</th>
                        <th>Sujet</th>
                        <th>Description</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
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
