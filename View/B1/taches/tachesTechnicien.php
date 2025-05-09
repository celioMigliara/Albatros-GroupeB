<?php
require_once __DIR__ . '/../../../Model/UserConnectionUtils.php';

if (!UserConnectionUtils::isUserConnected()) {
    header('Location: ' . BASE_URL . "/connexion");
    exit;
}
var_dump($_SESSION['user']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tâches à réaliser</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB1/styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB1/styleB1.css">
     <?php if ($_SESSION['user']['role_id']  == 1): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarAdmin.css">
    <?php else: ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarTechnicien.css">
    <?php endif; ?>

</head>
<body>
<header>
   <?php if ($_SESSION['user']['role_id'] == 1): ?>
    <?php require_once __DIR__ . '/../../B5/navbarAdmin.php'; ?>
    <?php else: ?>
    <?php require_once __DIR__ . '/../../B5/navbarTechnicien.php'; ?>
        <?php endif; ?>
    </header>
    <div class="tasks-page-container">
        <h1 class="title">Tâches à réaliser</h1>
        
        <div class="tasks-grid">
            <?php if (!empty($tasks)): ?>
                <?php foreach ($tasks as $task): ?>
                    <div class="task-card">
                        <h3 class="task-title"><?= htmlspecialchars($task['sujet_tache']) ?></h3>
                        
                        <div class="task-details">
                            <p class="task-date">Date de création : <?= htmlspecialchars($task['date_creation_tache']) ?></p>
                            <p class="task-description">Description : <?= htmlspecialchars($task['description_tache'] ?: 'Aucune description') ?></p>
                            
                            <?php
                            // Définir une classe CSS en fonction du nom du statut
                            $statutClass = '';
                            if (isset($task['nom_statut'])) {
                                switch (strtolower($task['nom_statut'])) {
                                    case 'nouvelle':
                                        $statutClass = 'statut-nouvelle';
                                        break;
                                    case 'planifiée':
                                        $statutClass = 'statut-planifiee';
                                        break;
                                    case 'demande de prix':
                                        $statutClass = 'statut-demande-prix';
                                        break;
                                    case 'en commande':
                                        $statutClass = 'statut-en-commande'; 
                                        break;
                                    case 'terminée':
                                        $statutClass = 'statut-terminee';
                                        break;
                                    case 'annulée':
                                        $statutClass = 'statut-annulee';
                                        break;
                                    default:
                                        $statutClass = 'statut-default';
                                        break;
                                }
                            }
                            ?>
                            <p class="task-status"><strong>Statut :</strong> <span class="statut-label <?= $statutClass ?>"><?= htmlspecialchars($task['nom_statut']) ?></span></p>
                            </div>
                        
                        <div class="task-actions">
                        <div class="task-actions">
                        <a href="<?= BASE_URL ?>/taches/modifier/<?= htmlspecialchars($task['id_tache']) ?>" class="modifier-btn">Ajouter informations</a>
                        </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-tasks-message">Vous n'avez pas de tâches en cours.</p>
            <?php endif; ?>
        </div>
        
        <div class="return-container">
            <a href="<?= BASE_URL ?>/ListeDemandes" class="btn-return">Retour</a>
        </div>
    </div>
</body>
</html>