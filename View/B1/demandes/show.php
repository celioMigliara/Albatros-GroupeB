<?php 
require_once __DIR__ . '/../../../Model/UserConnectionUtils.php';

if (!UserConnectionUtils::isUserConnected()) {
    header('Location: ' . BASE_URL . "/connexion");
    exit;
}
if (!isset($demande)) die("Données de la demande manquantes."); ?>

<?php
// Vérifier si la demande est modifiable (ni annulée, ni terminée)
$isDemandeModifiable = !in_array(strtolower($demande['nom_statut']), ['annulée', 'terminée']);
?>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB1/styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB1/styleB1.css">
    <?php if ($_SESSION['user']['role_id']  == 1): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarAdmin.css">
    <?php else: ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarTechnicien.css">
    <?php endif; ?>
</head>

<header>
    <?php if ($_SESSION['user']['role_id'] == 1): ?>
        <?php require_once __DIR__ . '/../../B5/navbarAdmin.php'; ?>
    <?php else: ?>
        <?php require_once __DIR__ . '/../../B5/navbarTechnicien.php'; ?>
    <?php endif; ?>
</header>


<?php if ($_SESSION['user']['role_id'] == 1): ?>
    <h1 class="title">Gestion de la demande </h1>
<?php else: ?>
    <h1 class="title">Gestion de ma demande </h1>
<?php endif; ?>
<div class="gestion-demande">
    <div class="demande-card">
        <div class="demande-header">
            <p><strong>Numéro :</strong> <?= htmlspecialchars($demande['num_ticket_dmd']) ?> : <?= htmlspecialchars($demande['sujet_dmd']) ?></p>
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
            <span class="statut-label <?= $statutClass ?>"><?= htmlspecialchars($demande['nom_statut']) ?></span>
        </div>



        <!-- Affichage des champs en fonction du rôle -->
        <form method="POST" action="<?= BASE_URL ?>/updateDemande/<?= htmlspecialchars($demande['id_demande']) ?>" enctype="multipart/form-data" class="form-grid">
            <input type="hidden" name="id" value="<?= htmlspecialchars($demande['id_demande']) ?>">

            <p><strong>Demandeur :</strong> <?= htmlspecialchars($demande['prenom_utilisateur']) ?> <?= htmlspecialchars($demande['nom_utilisateur']) ?></p>
            <p><strong>Demandé le :</strong> <?= htmlspecialchars(date('d M Y', strtotime($demande['date_creation_dmd']))) ?></p>

            <p><strong>Site :</strong>
                <?php if ($_SESSION['user']['role_id']  == 1 || !$isDemandeModifiable): ?>
                    <?= htmlspecialchars($demande['nom_site']) ?>
                <?php else: ?>
                    <select name="nom_site" <?= !$isDemandeModifiable ? 'disabled' : '' ?>>
                        <?php foreach ($sites as $site): ?>
                            <option value="<?= htmlspecialchars($site['nom_site']) ?>" <?= $site['nom_site'] == $demande['nom_site'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($site['nom_site']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </p>

            <p><strong>Bâtiment :</strong>
                <?php if ($_SESSION['user']['role_id']  == 1 || !$isDemandeModifiable): ?>
                    <?= htmlspecialchars($demande['nom_batiment']) ?>
                <?php else: ?>
                    <select name="nom_batiment" <?= !$isDemandeModifiable ? 'disabled' : '' ?>>
                        <?php foreach ($batiments as $batiment): ?>
                            <option value="<?= htmlspecialchars($batiment['nom_batiment']) ?>" <?= $batiment['nom_batiment'] == $demande['nom_batiment'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($batiment['nom_batiment']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </p>

            <p><strong>Lieu :</strong>
                <?php if ($_SESSION['user']['role_id']  == 1 || !$isDemandeModifiable): ?>
                    <?= htmlspecialchars($demande['nom_lieu']) ?>
                <?php else: ?>
                    <select name="nom_lieu" <?= !$isDemandeModifiable ? 'disabled' : '' ?>>
                        <?php foreach ($lieux as $lieu): ?>
                            <option value="<?= htmlspecialchars($lieu['nom_lieu']) ?>" <?= $lieu['nom_lieu'] == $demande['nom_lieu'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($lieu['nom_lieu']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </p>

            <p><strong>Description :</strong>
                <?php if ($_SESSION['user']['role_id']  == 1 || !$isDemandeModifiable): ?>
                    <?= htmlspecialchars($demande['description_dmd']) ?>
                <?php else: ?>
                    <textarea name="description_dmd" <?= !$isDemandeModifiable ? 'disabled' : '' ?>><?= htmlspecialchars($demande['description_dmd']) ?></textarea>
                <?php endif; ?>
            </p>

            <!-- Section d'upload de fichier -->
            <?php if ($isDemandeModifiable): ?>
                <div class="form-row">
                <div class="form-group full-width">
                <form method="POST" action="<?= BASE_URL ?>/updateDemande" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= htmlspecialchars($demande['id_demande']) ?>">
                            <label for="media">Ajouter un média :</label>
                            <input type="file" id="media" name="media" accept="image/*,video/*,audio/*,application/pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt"> 
                            <small>Formats acceptés : images, vidéos, audio, PDF, documents Office, texte...</small>
                            <button type="submit" class="btn-confirm">Confirmer l'ajout</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>


        </form>

        <!-- Bouton d'annulation pour les utilisateurs -->
        <?php if ($_SESSION['user']['role_id']  == 3 && strtolower($demande['nom_statut']) === 'nouvelle'): ?>
            <div class="user-actions">
                <form method="POST" action="index.php?action=annulerDemande&id=<?= htmlspecialchars($demande['id_demande']) ?>" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette demande ?');">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($demande['id_demande']) ?>">
                    <button type="submit" class="btn-danger">Annuler ma demande</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Affichage des images -->
        <?php if (!empty($images)): ?>
            <div class="images-container">
                <h2>Images associées :</h2>
                <div class="images-gallery">
                    <?php foreach ($images as $image): ?>
                        <a href="<?= BASE_URL ?>/Public/Uploads/<?= htmlspecialchars($image['url_media']) ?>" target="_blank">
                            <img src="<?= BASE_URL ?>/Public/Uploads/<?= htmlspecialchars($image['url_media']) ?>" alt="Image associée" class="demande-image">
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <p>Aucune image associée à cette demande.</p>
        <?php endif; ?>

        <!-- Affichage du commentaire admin s'il existe -->
        <?php if (!empty($demande['commentaire_admin_dmd'])): ?>
            <div class="admin-comment-display">
                <p><strong>Commentaire Admin :</strong></p>
                <p><?= nl2br(htmlspecialchars($demande['commentaire_admin_dmd'])) ?></p>
            </div>
        <?php endif; ?>

        <!-- Section pour le commentaire admin -->
        <?php if ($_SESSION['user']['role_id'] == 1): ?>
            <div class="admin-comment">
            <form method="POST" action="<?= BASE_URL ?>/updateCommentaire">  
                              <input type="hidden" name="id" value="<?= htmlspecialchars($demande['id_demande']) ?>">
                    <label for="commentaire_admin">Commentaire Admin :</label>
                    <textarea id="commentaire_admin" name="commentaire_admin" rows="4" cols="50"><?= htmlspecialchars($demande['commentaire_admin_dmd']) ?></textarea>
                    <button type="submit" class="btn btn-success">Enregistrer</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if (!$isDemandeModifiable && $_SESSION['user']['role_id'] == 3): ?>
            <div class="info-message">
                <p>Cette demande est <?= strtolower($demande['nom_statut']) ?> et ne peut plus être modifiée.</p>
            </div>
        <?php endif; ?>


        <!-- Bouton de soumission pour les utilisateurs -->
        <?php if ($_SESSION['user']['role_id'] == 3 || $_SESSION['user']['role_id'] == 2 && $isDemandeModifiable): ?>
            <button type="submit" class="btn btn-primary btn-modif">Modifier</button>
        <?php endif; ?>


        <div class="form-row">
            <div class="form-group full-width" style="text-align: center;">
                <a href="<?= BASE_URL ?>/ListeDemandes" class="btn-return">Retour</a>
            </div>
        </div>


        </di>
    </div>

    <?php if ($_SESSION['user']['role_id'] == 1): ?>
        <div class="buttons">
            <?php if ($isDemandeModifiable): ?>
                <form method="POST" action="<?= BASE_URL ?>/refuserDemande">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($demande['id_demande']) ?>">
                    <button type="submit" class="btn btn-danger">Refuser demande</button>
                </form>

                <div class="demande-actions">
                    <a href="<?= BASE_URL ?>/creerTache/<?= htmlspecialchars($demande['id_demande']) ?>" class="btn btn-primary">Ajouter une tâche</a>
                </div>
            <?php else: ?>
                <div class="info-message">
                    <p>Cette demande est <?= strtolower($demande['nom_statut']) ?> et ne peut plus être modifiée.</p>
                </div>
            <?php endif; ?>
        </div>

        <h1 class="title">Liste des tâches associées</h1>
        <div class="taches-container">
            <?php if (!empty($taches)): ?>
                <?php foreach ($taches as $tache): ?>
                    <?php
                    // Définir une classe CSS en fonction du nom du statut de la tâche
                    $statutClass = '';
                    if (isset($tache['nom_statut'])) {
                        switch (strtolower($tache['nom_statut'])) {
                            case 'nouvelle':
                                $statutClass = 'statut-nouvelle';
                                break;
                            case 'planifiée':
                                $statutClass = 'statut-planifiee';
                                break;
                            case 'en cours':
                                $statutClass = 'statut-en-cours';
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
                    <div class="tache-card">
                        <div class="tache-header">
                            <h3 class="tache-title">Titre : <?= htmlspecialchars($tache['titre_tache']) ?></h3>
                            <p class="tache-number">Date de création : <?= htmlspecialchars($tache['date_creation_tache']) ?></p>
                        </div>
                        <div class="tache-body">
                            <p><strong>Date planifiée :</strong> <?= htmlspecialchars($tache['date_planif_tache']) ?></p>
                            <p><strong>Date de fin :</strong> <?= htmlspecialchars($tache['date_fin_tache']) ?></p>
                            <p><strong>Statut :</strong> <span class="statut-label <?= $statutClass ?>"><?= htmlspecialchars($tache['nom_statut']) ?></span></p>
                        </div>
                        <div class="tache-actions">
                            <div class="tache-actions">
                                <a href="<?= BASE_URL ?>/taches/modifier/<?= htmlspecialchars($tache['Id_tache']) ?>" class="btn btn-primary">Modifier</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucune tâche trouvée pour cette demande.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>