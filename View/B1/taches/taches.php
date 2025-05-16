<?php
require_once __DIR__ . '/../../../Model/UserConnectionUtils.php';

if (!UserConnectionUtils::isUserConnected()) {
    header('Location: ' . BASE_URL . "/connexion");
    exit;
}
// Au début du fichier, après les inclusions nécessaires
$id_demande = isset($_GET['id_demande']) ? intval($_GET['id_demande']) : 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Création d'une tâche</title>
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

<?php require_once __DIR__ . '/../../B5/navbarAdmin.php'; ?>

    <h1 class="title">Création d'une tâche</h1>
    <form id="createTaskForm" method="POST" action="<?= BASE_URL ?>/creerTacheStore/<?= htmlspecialchars($demande['id_demande']) ?>" enctype="multipart/form-data" class="form-grid">
    <!-- Ajouter ceci juste après l'ouverture du formulaire -->
    <input type="hidden" name="id_demande" value="<?= htmlspecialchars($idDemande) ?>">    
    
    
    <!-- Ligne 1 : Nom de la tâche et Statut -->
        <div class="form-row">
            <div class="form-group">
                <label for="nom_tache" class="label">Nom de la tâche :</label>
                <input type="text" id="nom_tache" name="nom_tache" class="input" required>
            </div>
            <div class="form-group">
                <label for="statut" class="label">Statut :</label>
                <select id="statut" name="statut" class="input">
                    <?php foreach ($statuts as $statut): ?>
                        <option value="<?= $statut['id_statut'] ?>"><?= htmlspecialchars($statut['nom_statut']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Ligne 2 : Technicien et Date -->
        <div class="form-row">
            <div class="form-group">
                <label for="technicien" class="label">Technicien :</label>
                <select id="technicien" name="technicien" class="input" required>
                    <option value="">Sélectionnez un technicien</option>
                    <?php foreach ($techniciens as $technicien): ?>
                        <option value="<?= $technicien['id_utilisateur'] ?>">
                            <?= htmlspecialchars($technicien['prenom_utilisateur'] . ' ' . $technicien['nom_utilisateur']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="date" class="label">Date :</label>
                <input type="date" id="date" name="date" class="input" required>
            </div>
        </div>

        <!-- Ligne 3 : Site, Bâtiment et Lieu -->
        <div class="form-row">
            <div class="form-group">
                <label for="site" class="label">Site :</label>
                <select id="site" name="site" class="input">
                    <?php foreach ($sites as $site): ?>
                        <option value="<?= $site['id_site'] ?>" <?= isset($demande['id_site']) && $site['id_site'] == $demande['id_site'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($site['nom_site']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="batiment" class="label">Bâtiment :</label>
                <select id="batiment" name="batiment" class="input">
                    <?php foreach ($batiments as $batiment): ?>
                        <option value="<?= $batiment['id_batiment'] ?>" <?= isset($demande['id_batiment']) && $batiment['id_batiment'] == $demande['id_batiment'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($batiment['nom_batiment']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="lieu" class="label">Lieu :</label>
                <select id="lieu" name="lieu" class="input">
                    <?php foreach ($lieux as $lieu): ?>
                        <option value="<?= $lieu['id_lieu'] ?>" <?= $lieu['id_lieu'] == $demande['id_lieu'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($lieu['nom_lieu']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Ligne 4 : Description -->
        <div class="form-row">
            <div class="form-group full-width">
                <label for="description" class="label">Description :</label>
                <textarea id="description" name="description" class="input"></textarea>
            </div>
        </div>

        <!-- Ligne 5 : Ajouter un média -->
        <div class="form-row">
            <div class="form-group full-width">
                <label for="image" class="label">Ajouter un média :</label>
                <input type="file" id="image" name="image" class="input" accept="image/*,video/*,audio/*,application/pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt">
                <small>Formats acceptés : images, vidéos, audio, PDF, documents Office, texte...</small>
                <small class="warning">Taille maximale: 38 Mo</small>
            </div>
        </div>

       <!-- Bouton -->
       <button type="submit" class="btn-small">Créer la tâche</button>
       <a href="<?= BASE_URL ?>/listedemande/<?= htmlspecialchars($demande['id_demande']) ?>" class="btn-return mobile-return">Retour</a>
    
    </form>
    

    <div> 
        <p id="errorMessage"></p>
    </div>
    <script>
    const BASE_URL = "<?= BASE_URL ?>";
</script>
    <script src="<?= BASE_URL ?>/Javascript/B1/verificationTaches.js"></script>
            
         </form>
</body>
</html>