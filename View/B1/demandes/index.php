<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../Model/UserConnectionUtils.php';

if (!UserConnectionUtils::isUserConnected()) {
    header('Location: ' . BASE_URL . "/connexion");
    exit;
}

$pageActuelle = isset($pageActuelle) ? (int)$pageActuelle : 1;
$totalPages = isset($totalPages) ? (int)$totalPages : 1;
$demandes = $demandes ?? [];
$filters = $filters ?? [];

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des demandes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB1/styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB1/styleB1.css">
    <?php if ($_SESSION['user']['role_id'] == 1): ?>
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
    
    
    <?php if ($_SESSION['user']['role_id']== 1): ?>
        <h1 class="title">Liste des demandes </h1>
    <?php else: ?>
        <h1 class="title">Liste de mes demandes </h1>
    <?php endif; ?>

    



    <!-- Conteneur principal -->
    <div class="main-container">

        <!-- Les cartes des demandes -->
        <div class="demandes-container">
            <?php if (!empty($demandes)): ?>
                <?php foreach ($demandes as $demande): ?>
                    <div class="demande-card">
                        <div class="demande-header">
                            <h3 class="demande-title">Titre : <?= htmlspecialchars($demande['sujet_dmd']) ?></h3>
                            <p class="demande-number">Numéro : <?= htmlspecialchars($demande['num_ticket_dmd']) ?></p>
                        </div>
                        <div class="demande-body">
                            <p><strong>Site :</strong> <?= htmlspecialchars($demande['nom_site']) ?></p>
                            <p><strong>Bâtiment :</strong> <?= htmlspecialchars($demande['nom_batiment']) ?></p>
                            <p><strong>Demandeur :</strong> <?= htmlspecialchars($demande['prenom_utilisateur']) ?> <?= htmlspecialchars($demande['nom_utilisateur']) ?></p>
                            <p><strong>Demandé le :</strong> <?= htmlspecialchars($demande['date_creation_dmd']) ?></p>
                            <p><strong>Pièce(s) jointe(s) :</strong> <?= htmlspecialchars($demande['nombre_pieces_jointes']) ?></p>
                            <?php
                            // Définir une classe CSS en fonction du nom du statut
                            $statutClass = '';
                            if (isset($demande['nom_statut'])) {
                                switch (strtolower($demande['nom_statut'])) {
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
                            <p><strong>Statut :</strong> <span class="statut-label <?= $statutClass ?>"><?= htmlspecialchars($demande['nom_statut']) ?></span></p>
                        </div>
                        <?php if ($_SESSION['user']['id'] == $demande['id_utilisateur']): ?>
                        <div class="demande-actions">
                                <a href="<?= BASE_URL ?>/listedemande/<?= htmlspecialchars($demande['id_demande']) ?>" class="action-button">Voir</a>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucune demande trouvée.</p>
            <?php endif; ?>
        </div>
        
        <!-- Formulaire de filtres -->
        <?php include_once 'Formulaires/filtre.php'; ?>

    </div>

    <div class="technicien-button-container">
    <?php if (isset($_SESSION['user']['role_id']) && $_SESSION['user']['role_id'] == 2): ?>
        <!-- Lien vers la liste de tâches du technicien -->
                <a href="<?= BASE_URL ?>/tasksForTechnicien" class="btn-green">Voir mes tâches</a>
            <?php endif; ?>
    </div>


    <!-- Pagination -->
    <div class="pagination">
        <?php if ($pageActuelle > 1): ?>
            <form method="POST" action="<?= BASE_URL ?>/ListeDemandes">
                <input type="hidden" name="page" value="<?= $pageActuelle - 1 ?>">
                <?php foreach ($_GET as $key => $value): ?>
                    <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
                <?php endforeach; ?>
                <button type="submit" class="pagination-link">Précédent</button>
            </form>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <form method="POST" action="<?= BASE_URL ?>/ListeDemandes">
                <input type="hidden" name="page" value="<?= $i ?>">
                <?php foreach ($_GET as $key => $value): ?>
                    <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
                <?php endforeach; ?>
                <button type="submit" class="pagination-link <?= $i === $pageActuelle ? 'active' : '' ?>">
                    <?= $i ?>
                </button>
            </form>
        <?php endfor; ?>

        <?php if ($pageActuelle < $totalPages): ?>
            <form method="POST" action="<?= BASE_URL ?>/ListeDemandes">
                <input type="hidden" name="page" value="<?= $pageActuelle + 1 ?>">
                <?php foreach ($_GET as $key => $value): ?>
                    <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
                <?php endforeach; ?>
                <button type="submit" class="pagination-link">Suivant</button>
            </form>
        <?php endif; ?>
    </div>
    <?php if (!empty($_SESSION['popup_annulation'])): ?>
    <div id="popupModal" class="modal">
        <div class="modal-content">
                  <img src="<?= BASE_URL ?>/Assets/B2/Albatros.jpg" alt="Logo popup" class="popup-logo-B2" data-effect="mfp-move-horizontal">
            <span class="close-btn" onclick="closePopupModal()">&times;</span>
            <p>Votre demande a bien été annulée.</p>
               <button onclick="closePopup()">FERMER</button>
        </div>
    </div>
    <?php unset($_SESSION['popup_annulation']); ?>
<?php endif; ?>
</body>
</html>
<script>

    window.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById("popupModal");

    });
    function closePopup() {
     const modal = document.getElementById("popupModal");
        if (modal) modal.style.display = "none";
   
}
</script>
