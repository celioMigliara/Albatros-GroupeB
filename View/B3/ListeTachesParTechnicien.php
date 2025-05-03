<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Gestion des tâches - Page 1</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB3/FeuilleRoute.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

</head>

<body data-page="ListeTaches">

    <div class="overlay"></div>

    <div class="block_taches">
        <div style="width: 100%;">
            <h1>Feuille de route du technicien</h1>
        </div>

        <form>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? '', ENT_QUOTES); ?>">

            <!-- Conteneur du choix du technicien -->
            <div class="container_technicien">
                <select name="technicien_id" id="technicienSelect">
                    <option value="">Sélectionnez un technicien</option>
                    <?php
                    foreach ($techniciens as $tech): ?>
                        <option value="<?= htmlspecialchars($tech['Id_utilisateur']) ?>">
                            <?= htmlspecialchars($tech['nom_utilisateur']) ?>
                            <?= htmlspecialchars($tech['prenom_utilisateur']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="buttons">
                    <button type="button" id="ajouterPrintList">Ajouter ce technicien à la liste d'impression</button>
                    <button type="button" id="imprimerFeuilleRoute" class="action-btn">🖨️ Imprimer la feuille de route</button>
                </div>

            </div>

        </form>

        <!-- Zone des filtres de recherche -->
        <div class="filters">
            <div class="filter-group">
                <input type="text" id="searchInput" placeholder="Rechercher...">

                <!-- Ajouter les champs pour la recherche par date -->
                <div>
                    <input type="date" id="startDate" placeholder="Date début">
                </div>
                <div>
                    <input type="date" id="endDate" placeholder="Date fin">
                </div>

                <select name="statusFilter" id="statusFilter" multiple size=6>
                    <option value="0">Tous statuts</option>
                    <?php
                    foreach ($statuts as $statut): ?>
                        <option value="<?= htmlspecialchars($statut['Id_statut']) ?>">
                            <?= htmlspecialchars($statut['nom_statut']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select id="mediaFilter">
                    <option value="0">Pas de filtre média</option>
                    <option value="1">Uniquement sans média</option>
                    <option value="2">Uniquement avec média</option>
                </select>

                <button id="resetFiltersBtn">
                    🔄 Réinitialiser les filtres
                </button>
            </div>
        </div>

        <div class="buttons">
            <button id="saveOrder">Enregistrer l'ordre des taches</button>
            <button id="listeImpression">Gérer la liste d'impression</button>
        </div>

        <div id="dropPrevPage" class="drop-zone">← Déposer ici pour page précédente</div>
        <!-- Tableau des tâches -->
        <table id="tasksTable">
            <thead>
                <tr>
                    <th>N° Tâche</th>
                    <th>Date</th>
                    <th>Ticket</th>
                    <th>Bâtiment</th>
                    <th>Lieu</th>
                    <th>Description</th>
                    <th>Média</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <!-- Les lignes de tâches vont ici -->
            </tbody>
        </table>
        <div id="dropNextPage" class="drop-zone">Déposer ici pour page suivante →</div>
    </div>



    <!-- Pagination -->
    <div class="pagination_container" style="width: 100%">
        <div id="pagination">
            <button id="firstPage" onclick="changePage('first')">First</button>
            <button id="prevPage" onclick="changePage(-1)" disabled>Précédent</button>
            <span id="pageNumber">Page 1</span>
            <button id="nextPage" onclick="changePage(1)">Suivant</button>
            <button id="lastPage" onclick="changePage('last')">Last</button>
        </div>

    </div>

    <div id="mediaPopup" style="display:none; position:fixed; top:20%; left:30%; width:40%; background:white; border:1px solid #ccc; box-shadow:0 0 10px rgba(0,0,0,0.3); padding:20px; z-index:1000;">
        <h3>Médias de la tâche</h3>
        <ul id="mediaList" style="max-height:300px; overflow:auto;"></ul>
        <button onclick="closeMediaPopup()">Fermer</button>
    </div>

    <script>
        // On définit la base URL depuis le PHP pour le JS
        const BASE_URL = <?= json_encode(BASE_URL) ?>;
    </script>

    <script src="<?= BASE_URL ?>/JavaScript/B3/FeuilleDeRoute.js"></script>

</body>

</html>