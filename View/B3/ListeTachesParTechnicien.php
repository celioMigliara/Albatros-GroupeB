<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Gestion des tâches - Page 1</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB3/FeuilleRoute.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Css/cssB5/navbarAdmin.css">
</head>

<body data-page="ListeTaches">
    <?php require_once __DIR__ . '/../B5/navbarAdmin.php'; ?>

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
                    <option value="0">Sélectionnez un technicien</option>
                    <?php
                    foreach ($techniciens as $tech): ?>
                        <option value="<?= htmlspecialchars($tech['Id_utilisateur']) ?>">
                            <?= htmlspecialchars($tech['nom_utilisateur']) ?>
                            <?= htmlspecialchars($tech['prenom_utilisateur']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="buttons">
                    <button type="button" id="imprimerFeuilleRoute" class="action-btn">🖨️ Imprimer la feuille de route</button>
                    <button type="button" id="ajouterPrintList">Ajouter ce technicien à la liste d'impression</button>
                    <button type="button" id="listeImpression">Gérer la liste d'impression</button>
                </div>

            </div>

        </form>

        <!-- Zone des filtres de recherche -->
        <div class="filters">
            <div class="filter-group">
                <div>
                    <label for="searchInput">Recherche par mot-clé :</label>
                    <br>
                    <input type="text" id="searchInput" placeholder="Rechercher...">
                </div>

                <div>
                    <label for="startDate">Date de début :</label>
                    <br>
                    <input type="date" id="startDate" placeholder="Date début">
                </div>

                <div>
                    <label for="endDate">Date de fin :</label>
                    <br>
                    <input type="date" id="endDate" placeholder="Date fin">
                </div>

                <div>
                    <label for="statusFilter">Filtrer les taches en cours<br>(Ctrl + clic pour sélectionner plusieurs statuts) :</label>
                    <br>
                    <select name="statusFilter" id="statusFilter" multiple size="1">
                        <option value="0">Tous statuts</option>
                        <?php foreach ($statutsEnCours as $statut): ?>
                            <option value="<?= htmlspecialchars($statut['id_statut']) ?>">
                                <?= htmlspecialchars($statut['nom_statut']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="mediaFilter">Filtrer par média :</label>
                    <br>
                    <select id="mediaFilter">
                        <option value="0">Pas de filtre média</option>
                        <option value="1">Uniquement sans média</option>
                        <option value="2">Uniquement avec média</option>
                    </select>
                </div>

                <div>
                    <button id="resetFiltersBtn">
                        🔄 Réinitialiser les filtres
                    </button>
                </div>
            </div>
        </div>

        <div class="buttons">
            <button id="openModifOrdrePopup" class="action-btn">Changer l'ordre manuellement</button>
            <button id="saveOrder">Enregistrer l'ordre des taches</button>
        </div>

        <!-- Tableau des tâches -->
        <div class="table-container">
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
        </div>
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

    <div id="popup">
        <p id="popup-message"></p>
        <button onclick="closeSimplePopup()">Fermer</button>
    </div>

    <!-- Popup pour la modification manuelle de l'ordre -->
    <div id="modifOrdrePopup" class="popup">
        <div class="popup-content">
            <button class="fermer-popup">&times;</button>
            <h3>Modifier l'ordre des tâches</h3>

            <div class="popup-form">
                <div class="input-group-custom">
                    <label for="sourceTacheOrdre">Quelle tache dont il faut changer l'ordre ?</label>
                    <input id="sourceTacheOrdre" type="number" class="input-custom" placeholder="1">
                </div>

                <div class="input-group-custom">
                    <label for="targetTacheOrdre">Quel ordre voulez-vous lui assigner ?</label>
                    <input id="targetTacheOrdre" type="number" class="input-custom" placeholder="2">
                </div>

                <div class="popup-buttons">
                    <button id="modifOrdreTache" class="btn">Modifier l'ordre</button>
                    <button id="confirmerModifOrdre" class="btn">OK</button>
                </div>
            </div>
        </div>
    </div>

    <div id="popup">
        <p id="popup-message"></p>
        <button onclick="closeSimplePopup()">Fermer</button>
    </div>

    <script>
        // On définit la base URL depuis le PHP pour le JS
        const BASE_URL = <?= json_encode(BASE_URL) ?>;
    </script>

    <script src="<?= BASE_URL ?>/JavaScript/B3/FeuilleDeRoute.js"></script>

</body>

</html>