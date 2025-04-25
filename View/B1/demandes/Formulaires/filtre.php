<!-- filepath: c:\xampp\htdocs\projet\ProjetAlbatrosB1\View\demandes\Formulaires\filtre.php -->
<head>
    <meta charset="UTF-8">
    <title>Filtres</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB1/styleB1.css">
</head>

<div class="filtre-container">
    <!-- modification pour que le controller des demandes soit executé avec les filtres -->
<form method="POST" action="index.php?action=index" class="filtre-form">
        <h2>Filtre(s) :</h2>
        <!-- Mot(s) Clé(s) -->
        <div class="form-group">
            <label for="keywords">Mot(s) Clé(s) :</label>
            <input type="text" name="keywords" id="keywords" placeholder="titre, nom..."
                   value="<?= isset($_POST['keywords']) ? htmlspecialchars($_POST['keywords']) : '' ?>" />
        </div>

        <!-- Statut -->
        <div class="form-group">
            <label for="statut">Statut :</label>
            <select name="statut" id="statut">
                <option value="">Sélectionnez un statut</option>
                <?php if (!empty($statuts)): ?>
                    <?php foreach ($statuts as $statut): ?>
                        <option value="<?= $statut['id_statut'] ?>"
                            <?= (isset($_POST['statut']) && $_POST['statut'] == $statut['id_statut']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($statut['nom_statut']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <!-- Site -->
        <div class="form-group">
            <label for="site">Site :</label>
            <select name="site" id="site">
                <option value="">Sélectionnez un site</option>
                <?php if (!empty($sites)): ?>
                    <?php foreach ($sites as $site): ?>
                        <option value="<?= $site['id_site'] ?>"
                            <?= (isset($_POST['site']) && $_POST['site'] == $site['id_site']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($site['nom_site']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <!-- Bâtiment -->
        <div class="form-group">
            <label for="batiment">Bâtiment :</label>
            <select name="batiment" id="batiment">
                <option value="">Sélectionnez un bâtiment</option>
                <?php if (!empty($batiments)): ?>
                    <?php foreach ($batiments as $batiment): ?>
                        <option value="<?= $batiment['id_batiment'] ?>"
                            <?= (isset($_POST['batiment']) && $_POST['batiment'] == $batiment['id_batiment']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($batiment['nom_batiment']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <!-- Tri -->
        <div class="form-group">
            <label for="tri">Trier :</label>
            <select name="tri" id="tri">
                <option value="asc" <?= (isset($_POST['tri']) && $_POST['tri'] === 'asc') ? 'selected' : '' ?>>Plus récents</option>
                <option value="desc" <?= (isset($_POST['tri']) && $_POST['tri'] === 'desc') ? 'selected' : '' ?>>Plus anciens</option>  
            </select>
        </div>

        <!-- Période -->
        <div class="form-group">
            <label for="date_debut">Période :</label>
            <input type="date" name="date_debut" id="date_debut"
                   value="<?= isset($_POST['date_debut']) ? htmlspecialchars($_POST['date_debut']) : '' ?>" />
            <input type="date" name="date_fin" id="date_fin"
                   value="<?= isset($_POST['date_fin']) ? htmlspecialchars($_POST['date_fin']) : '' ?>" />
        </div>

        <!-- Boutons -->
        <div class="button-group">
            <button type="submit">Appliquer</button>
            <a href="index.php?action=index" class="btn-reset">Effacer</a> <!-- Bouton pour réinitialiser le filtre, passe aussi l'index pour ce soit exécuté-->  
        </div>
    </form>
</div>