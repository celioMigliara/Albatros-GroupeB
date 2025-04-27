<?php
// Au début du fichier, après les inclusions nécessaires
$id_demande = isset($_GET['id_demande']) ? intval($_GET['id_demande']) : 0;
$isTechnicien = $_SESSION['user_role'] == 2; // Vérifier si l'utilisateur est un technicien
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title><?= $isTechnicien ? "Ajout d'informations" : "Modification d'une tâche" ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB1/styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB1/styleB1.css"> 
    <?php if ($_SESSION['user_role'] == 1): ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarAdmin.css">
<?php else: ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarTechnicien.css">
<?php endif; ?>

</head>

<body>

    <?php require_once __DIR__ . '/../../B5/navbarAdmin.php'; ?>

    <h1 class="titleB1"><?= $isTechnicien ? "Ajout d'informations à la tâche" : "Modification d'une tâche" ?></h1>

    + <form method="POST" action="<?= BASE_URL ?>/updateTask" enctype="multipart/form-data" class="form-grid" id="updateTaskForm">
    <input type="hidden" name="id_tache" value="<?= htmlspecialchars($tache['id_tache']) ?>">


        <input type="hidden" name="id_tache" value="<?= htmlspecialchars($tache['id_tache']) ?>">
        <input type="hidden" name="id_demande" value="<?= htmlspecialchars($tache['id_demande']) ?>">

        <?php if (!$isTechnicien): ?>
            <!-- Ces champs ne sont visibles que pour les administrateurs -->
            <!-- Ligne 1 : Nom de la tâche et Statut -->
            <div class="form-row">
                <div class="form-group">
                    <label for="nom_tache">Nom de la tâche :</label>
                    <input type="text" id="nom_tache" name="nom_tache" class="input" value="<?= htmlspecialchars($tache['sujet_tache']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="statut">Statut :</label>
                    <select id="statut" name="statut" class="input">
                        <?php foreach ($statuts as $statut): ?>
                            <option value="<?= $statut['id_statut'] ?>" <?= $statut['id_statut'] == $tache['id_statut'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($statut['nom_statut']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Ligne 2 : Technicien et Date -->
            <div class="form-row">
                <div class="form-group">
                    <label for="technicien">Technicien :</label>
                    <select id="technicien" name="technicien" class="input">
                        <option value="">Sélectionnez un technicien</option>
                        <?php foreach ($techniciens as $technicien): ?>
                            <option value="<?= $technicien['id_utilisateur'] ?>" <?= $technicien['id_utilisateur'] == $tache['id_utilisateur'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($technicien['prenom_utilisateur'] . ' ' . $technicien['nom_utilisateur']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date_planif_tache">Date :</label>
                    <input type="date" id="date_planif_tache" name="date_planif_tache" class="input" value="<?= htmlspecialchars($tache['date_planif_tache']) ?>" required>
                </div>
            </div>

            <!-- Lignes 3 et 4 : Site, Bâtiment, Lieu et Description -->
            <div class="form-row">
                <div class="form-group">
                    <label for="site">Site :</label>
                    <select id="site" name="site" class="input">
                        <?php foreach ($sites as $site): ?>
                            <option value="<?= $site['id_site'] ?>" <?= $site['id_site'] == $tache['id_site'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($site['nom_site']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="batiment">Bâtiment :</label>
                    <select id="batiment" name="batiment" class="input">
                        <?php foreach ($batiments as $batiment): ?>
                            <option value="<?= $batiment['id_batiment'] ?>" <?= $batiment['id_batiment'] == $tache['id_batiment'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($batiment['nom_batiment']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="lieu">Lieu :</label>
                    <select id="lieu" name="lieu" class="input">
                        <?php foreach ($lieux as $lieu): ?>
                            <option value="<?= $lieu['id_lieu'] ?>" <?= $lieu['id_lieu'] == $tache['id_lieu'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($lieu['nom_lieu']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group full-width">
                    <label for="description">Description :</label>
                    <textarea id="description" name="description" class="input"><?= htmlspecialchars($tache['description_tache']) ?></textarea>
                </div>
            </div>
        <?php else: ?>
            <!-- Pour les techniciens, ajouter seulement les informations en lecture seule -->
            <div class="task-info-readonly">
                <div class="form-row">
                    <div class="form-group full-width">
                        <h2>Informations sur la tâche</h2>
                        <p><strong>Titre :</strong> <?= htmlspecialchars($tache['sujet_tache']) ?></p>
                        <p><strong>Description :</strong> <?= htmlspecialchars($tache['description_tache'] ?: 'Aucune description') ?></p>
                        <p><strong>Date de création :</strong> <?= htmlspecialchars($tache['date_creation_tache']) ?></p>
                        <p><strong>Date planifiée :</strong> <?= htmlspecialchars($tache['date_planif_tache']) ?></p>
                        <?php

                        // Vérifier si nous avons le nom du statut
                        if (isset($tache['nom_statut'])) {
                            $nomStatut = $tache['nom_statut'];
                        } else if (isset($tache['id_statut'])) {
                            // Si nous n'avons que l'ID du statut, récupérer le nom correspondant
                            switch ($tache['id_statut']) {
                                case 1:
                                    $nomStatut = 'Nouvelle';
                                    break;
                                case 2:
                                    $nomStatut = 'Planifiée';
                                    break;
                                case 3:
                                    $nomStatut = 'Demande de prix';
                                    break;
                                case 4:
                                    $nomStatut = 'En commande';
                                    break;
                                case 5:
                                    $nomStatut = 'Terminée';
                                    break;
                                case 6:
                                    $nomStatut = 'Annulée';
                                    break;
                                default:
                                    $nomStatut = 'Indéfini';
                                    break;
                            }
                        }

                        // Déterminer la classe CSS
                        switch (strtolower($nomStatut)) {
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
                        ?>
                        <p><strong>Statut :</strong> <span class="statut-label <?= $statutClass ?>"><?= htmlspecialchars($nomStatut) ?></span></p>

                        <!-- Champs cachés pour préserver les valeurs lors de la soumission -->
                        <input type="hidden" name="nom_tache" value="<?= htmlspecialchars($tache['sujet_tache']) ?>">
                        <input type="hidden" name="technicien" value="<?= htmlspecialchars($tache['id_utilisateur']) ?>">
                        <input type="hidden" name="date_planif_tache" value="<?= htmlspecialchars($tache['date_planif_tache']) ?>">
                        <input type="hidden" name="statut" value="<?= htmlspecialchars($tache['id_statut']) ?>">
                        <input type="hidden" name="description" value="<?= htmlspecialchars($tache['description_tache']) ?>">
                        <input type="hidden" name="site" value="<?= htmlspecialchars($tache['id_site']) ?>">
                        <input type="hidden" name="batiment" value="<?= htmlspecialchars($tache['id_batiment']) ?>">
                        <input type="hidden" name="lieu" value="<?= htmlspecialchars($tache['id_lieu']) ?>">
                    </div>
                </div>
            </div>

            <!-- Commentaire technicien -->
            <div class="form-row">
                <div class="form-group full-width">
                    <label for="commentaire_technicien" class="tech-label">Commentaire technicien :</label>
                    <textarea id="commentaire_technicien" name="commentaire_technicien" class="input tech-textarea"><?= htmlspecialchars($tache['commentaire_technicien_tache'] ?? '') ?></textarea>
                    <small>Ajoutez vos observations ou actions réalisées ici</small>
                </div>
            </div>
        <?php endif; ?>

        <!-- Affichage des médias liés à la tâche (pour tous les utilisateurs) -->
        <div class="form-row">
            <div class="form-group full-width">
                <h2>Médias associés :</h2>
                <?php if (!empty($images)): ?>
                    <div class="media-gallery">
                        <?php foreach ($images as $media): ?>
                            <?php
                            $fileExtension = pathinfo($media['url_media'], PATHINFO_EXTENSION);
                            $isImage = in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
                            $isVideo = in_array(strtolower($fileExtension), ['mp4', 'webm', 'ogg', 'mov', 'avi']);
                            $isPDF = strtolower($fileExtension) === 'pdf';
                            $isAudio = in_array(strtolower($fileExtension), ['mp3', 'wav', 'ogg', 'aac']);
                            ?>
                            <a href="<?= BASE_URL ?>/Public/Uploads/<?= htmlspecialchars($media['url_media']) ?>" target="_blank" class="media-item">
                                <?php if ($isImage): ?>
                                    <img src="<?= BASE_URL ?>/Public/Uploads/<?= htmlspecialchars($media['url_media']) ?>" alt="Image associée" class="demande-image">
                                    <?php elseif ($isVideo): ?>
                                        <div class="media-preview">
                                            <span class="media-icon">🎬</span>
                                            <span class="media-name"><?= htmlspecialchars(basename($media['url_media'])) ?></span>
                                        </div>
                                    <?php elseif ($isPDF): ?>
                                        <div class="media-preview">
                                            <span class="media-icon">📄</span>
                                            <span class="media-name"><?= htmlspecialchars(basename($media['url_media'])) ?></span>
                                        </div>
                                    <?php elseif ($isAudio): ?>
                                        <div class="media-preview">
                                            <span class="media-icon">🔊</span>
                                            <span class="media-name"><?= htmlspecialchars(basename($media['url_media'])) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <div class="media-preview">
                                            <span class="media-icon">📎</span>
                                            <span class="media-name"><?= htmlspecialchars(basename($media['url_media'])) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    </a>
                                <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>Aucun média associé à cette tâche.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ajout de média (pour tous les utilisateurs) -->
        <div class="form-row">
            <div class="form-group full-width">
                <label for="media">Ajouter un média :</label>
                <input type="file" id="media" name="media" accept="image/*,video/*,audio/*,application/pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt">
                <small>Formats acceptés : images, vidéos, audio, PDF, documents Office, texte...</small>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group full-width" style="text-align: center;">
                <button type="submit" class="btn-small"><?= $isTechnicien ? "Enregistrer les informations" : "Modifier la tâche" ?></button>
                <a href="<?= $isTechnicien ? (BASE_URL . '/tasksForTechnicien') : (BASE_URL . '/listedemande/' . htmlspecialchars($tache['id_demande'])) ?>" class="btn-return">Retour</a>
                </div>
        </div>
    </form>

    <script>
    const BASE_URL = "<?= BASE_URL ?>";
</script>
    <script src="<?= BASE_URL ?>/JavaScript/B1/verificationTaches.js"></script>
</body>

</html>