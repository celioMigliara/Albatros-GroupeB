<?php
require_once __DIR__ . '/../../Model/UserConnectionUtils.php';

if (!UserConnectionUtils::isAdminConnected()) {
    header('Location: ' . BASE_URL . "/connexion");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des demandes</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarAdmin.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB2/Historique_styleB2.css">
</head>
<body>
<?php require_once __DIR__ . '/../B5/navbarAdmin.php'; ?>

<h2 class="title">Liste des demandes</h2>
        <br>
    <div class="containerB2">
        
        <!-- Formulaire de filtres -->
        <form method="POST" class="filter-formB2">
            <div class="form-groupB2">
                <label for="date_debut"><b>Date de début :</b></label>
                <div class="date-select">
                    <select name="date_debut_jour" id="date_debut_jour">
                        <option value="">Jour</option>
                        <?php for ($i = 1; $i <= 31; $i++): ?>
                            <option value="<?= $i ?>" <?= (isset($_GET['date_debut_jour']) && $_GET['date_debut_jour'] == $i) ? 'selected' : '' ?>>
                                <?= $i ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <select name="date_debut_mois" id="date_debut_mois">
                        <option value="">Mois</option>
                        <?php
                        $mois = [
                            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
                        ];
                        foreach ($mois as $num => $nom): ?>
                            <option value="<?= $num ?>" <?= (isset($_GET['date_debut_mois']) && $_GET['date_debut_mois'] == $num) ? 'selected' : '' ?>>
                                <?= $nom ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select name="date_debut_annee" id="date_debut_annee">
                        <option value="">Année</option>
                        <?php for ($i = date('Y') - 10; $i <= date('Y'); $i++): ?>
                            <option value="<?= $i ?>" <?= (isset($_GET['date_debut_annee']) && $_GET['date_debut_annee'] == $i) ? 'selected' : '' ?>>
                                <?= $i ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="form-groupB2">
                <label for="date_fin"><b>Date de fin :</b></label>
                <div class="date-select">
                    <select name="date_fin_jour" id="date_fin_jour">
                        <option value="">Jour</option>
                        <?php for ($i = 1; $i <= 31; $i++): ?>
                            <option value="<?= $i ?>" <?= (isset($_GET['date_fin_jour']) && $_GET['date_fin_jour'] == $i) ? 'selected' : '' ?>>
                                <?= $i ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <select name="date_fin_mois" id="date_fin_mois">
                        <option value="">Mois</option>
                        <?php foreach ($mois as $num => $nom): ?>
                            <option value="<?= $num ?>" <?= (isset($_GET['date_fin_mois']) && $_GET['date_fin_mois'] == $num) ? 'selected' : '' ?>>
                                <?= $nom ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select name="date_fin_annee" id="date_fin_annee">
                        <option value="">Année</option>
                        <?php for ($i = date('Y') - 10; $i <= date('Y'); $i++): ?>
                            <option value="<?= $i ?>" <?= (isset($_GET['date_fin_annee']) && $_GET['date_fin_annee'] == $i) ? 'selected' : '' ?>>
                                <?= $i ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <button type="submit" class="btnFiltreB2">Filtrer</button>
        </form>

        <!-- Tableau des demandes -->
        <table class="tableB2">
            <thead>
                <tr>
                    <th>Ticket</th>
                    <th>Sujet</th>
                    <th>Date de création</th>
                    <th>Site</th>
                    <th>Bâtiment</th>
                    <th>Lieu</th>
                    <th>Demandeur</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($results)): ?>
                    <tr><td colspan="7" class="text-center">Aucune demande trouvée.</td></tr>
                <?php else: ?>
                    <?php foreach ($results as $result): ?>
                        <tr>
                            <?php foreach (['num_ticket_dmd', 'sujet_dmd', 'date_creation_dmd', 'nom_site', 'nom_batiment', 'nom_lieu', 'nom_complet', 'nom_statut'] as $champ): ?>
                                <td><?= htmlspecialchars($result[$champ]) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($nbr_demandes > 0): ?>
            <p class="nbr-demandes"><?php echo $nbr_demandes ?> demandes trouvées</p>
        <?php endif; ?>

        <!-- Bouton Export Excel -->
        <div class="btn-wrapperB2">
    <form method="POST" action="<?= BASE_URL ?>/exportDemandes">
        <button type="submit" class="btnExcelB2">Exporter</button>
    </form>
    <form method="GET" action="<?= BASE_URL ?>/AccueilAdmin">
        <button type="submit" class="btnRetourB2">Retour</button>
    </form>
</div>

        <script>
    const BASE_URL = "<?= BASE_URL ?>";
  </script>
        <script src="<?= BASE_URL ?>/Javascript/B2/exportCsv.js"></script>
    </div>
</body>
</html>

