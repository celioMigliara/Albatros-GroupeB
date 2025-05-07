// Définition des clés pour les filtres du localStorage
const SEARCH_INPUT_KEY = "searchInput";
const START_DATE_KEY = "startDate";
const END_DATE_KEY = "endDate";
const STATUS_FILTER_KEY = "statusFilter";
const MEDIA_FILTER_KEY = "mediaFilter";

// Définition des clés pour la gestion des techniciens du localStorage
const PRESENT_KEY = "techniciens_presents";
const TECHNICIEN_KEY = "technicien_courant";
let initialTaskMap = new Map();
let idToOrderModified = new Map();
let listeTaches;

// Sauvegarde des filtres
document.getElementById("searchInput").addEventListener("input", function () {
    localStorage.setItem(SEARCH_INPUT_KEY, this.value);
});

// Sauvegarde des dates
document.getElementById("startDate").addEventListener("change", function () {
    localStorage.setItem(START_DATE_KEY, this.value);
});

// Sauvegarde de la date de fin
document.getElementById("endDate").addEventListener("change", function () {
    localStorage.setItem(END_DATE_KEY, this.value);
});

// Sauvegarde du filtre de statut
document.getElementById("statusFilter").addEventListener("change", function () {
    const selectedValues = Array.from(this.selectedOptions).map(opt => opt.value);
    localStorage.setItem(STATUS_FILTER_KEY, JSON.stringify(selectedValues));
});

// Sauvegarde du filtre de médias
document.getElementById("mediaFilter").addEventListener("change", function () {
    localStorage.setItem(MEDIA_FILTER_KEY, this.value);
});

// Restauration des filtres
document.addEventListener("DOMContentLoaded", function () {

    // Search input restoration
    const searchInput = localStorage.getItem(SEARCH_INPUT_KEY);
    if (searchInput !== null) {
        document.getElementById("searchInput").value = searchInput;
    }

    // Date restoration
    const startDate = localStorage.getItem(START_DATE_KEY);
    if (startDate !== null) {
        document.getElementById("startDate").value = startDate;
    }

    // End date restoration
    const endDate = localStorage.getItem(END_DATE_KEY);
    if (endDate !== null) {
        document.getElementById("endDate").value = endDate;
    }

    // Status filter restoration
    const savedStatus = localStorage.getItem(STATUS_FILTER_KEY);
    const statusSelect = document.getElementById("statusFilter");
    if (savedStatus) {
        const values = JSON.parse(savedStatus);
        Array.from(statusSelect.options).forEach(opt => {
            opt.selected = values.includes(opt.value);
        });
    }
    else {
        // Sélectionne par défaut l'option "0" s’il n’y a rien en localStorage
        Array.from(statusSelect.options).forEach(opt => {
            opt.selected = opt.value === "0";
        });
    }

    // Media filter restoration
    const media = localStorage.getItem(MEDIA_FILTER_KEY);
    if (media !== null) {
        document.getElementById("mediaFilter").value = media;
    }
});

// Fonction pour récupérer et mettre à jour la liste des techniciens présents
function getPresentTechnicians() {
    let techs = localStorage.getItem(PRESENT_KEY);
    return techs ? JSON.parse(techs) : [];
}

// Fonction pour mettre à jour la liste des techniciens présents
function setPresentTechnicians(techs) {
    localStorage.setItem(PRESENT_KEY, JSON.stringify(techs));
}

// Fonction pour parser le JSON de manière sécurisée
function safeJsonParse(str) {
    try {
        return JSON.parse(str);
    } catch (e) {
        return null;
    }
}

// Fonction pour setup le drag and drop
document.addEventListener("DOMContentLoaded", function () {
    setupDragAndDrop();
});

// Fonction pour restaurer le technicien courant
document.addEventListener("DOMContentLoaded", function () {
    const savedTechnicien = localStorage.getItem(TECHNICIEN_KEY);
    if (savedTechnicien) {
        let TechnicienSelect = document.getElementById("technicienSelect");
        if (TechnicienSelect) {
            TechnicienSelect.value = savedTechnicien;
        }

        loadTachesForTechnicien(savedTechnicien);
    }
});

let SelectTechnicien = document.getElementById("technicienSelect");
if (SelectTechnicien) {
    SelectTechnicien.addEventListener("change", function () {
        const selectedValue = this.value;

        localStorage.setItem(TECHNICIEN_KEY, selectedValue);
        loadTachesForTechnicien(selectedValue);
    });
}


let listeImpression = document.getElementById("listeImpression");
if (listeImpression) {
    listeImpression.addEventListener("click", function () {
        window.location.href = BASE_URL + "/feuillederoute/liste/impression";
    });
}

// Fonction pour ajouter un technicien à la liste des présents
let ajouterPrintList = document.getElementById("ajouterPrintList");
if (ajouterPrintList) {
    ajouterPrintList.addEventListener("click", function () {
        const select = document.getElementById("technicienSelect");
        const selectedValue = select.value;
        const selectedText = select.options[select.selectedIndex].text;

        if (!selectedValue) {
            // On peut remplacer par une popup
            alert("Veuillez sélectionner un technicien.");
            console.log("Veuillez sélectionner un technicien.");
            return;
        }

        let presentTechs = getPresentTechnicians();
        if (presentTechs.some((t) => t.id === selectedValue)) {
            // On peut remplacer par une popup
            alert("Ce technicien est déjà dans la liste.");
            console.log("Ce technicien est déjà dans la liste.");
            return;
        }

        presentTechs.push({
            id: selectedValue,
            name: selectedText,
        });

        setPresentTechnicians(presentTechs);
        // On peut remplacer par une popup
        alert("Technicien ajouté à la liste des présents.");
        console.log("Technicien ajouté à la liste des présents.");
    });
}

// Fonction pour supprimer un technicien de la liste des présents
let saveOrder = document.getElementById("saveOrder");
if (saveOrder) {
    saveOrder.addEventListener("click", function () {
        if (idToOrderModified.size === 0) {
            console.log("Aucune modification à sauvegarder.");
            return;
        }

        let queryString = "";
        let index = 0;
        for (let [id, order] of idToOrderModified) {
            if (initialTaskMap.get(id) == order) {
                console.log(
                    "On skip l'element " + id + " avec le numero d'ordre " + order
                );
                continue;
            }

            queryString += `&changes[${index}][id]=${encodeURIComponent(
                id
            )}&changes[${index}][order]=${encodeURIComponent(order)}`;
            index++;
        }

        if (index == 0) {
            console.log("Aucune modification à sauvegarder.");
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open("POST", BASE_URL + "/feuillederoute/ordre/update", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                idToOrderModified.clear();
                let JsonReponse = safeJsonParse(xhr.responseText);
                if (!JsonReponse) {
                    console.log(xhr.responseText);
                    return;
                }

                // Remplacer par une popup
                alert(JsonReponse.message);
                console.log(JsonReponse.message);
            }
        };

        // Rajouter le token csrf manuellement
        const csrfToken = document.querySelector('input[name="csrf_token"]').value;
        queryString += "&csrf_token=" + encodeURIComponent(csrfToken);

        if (queryString.charAt(0) === "&") {
            queryString = queryString.slice(1);
        }

        xhr.send(queryString);
    });
}

// Helper fonction
function escapeHTML(text) {
    const div = document.createElement("div");
    div.textContent = text ?? "";
    return div.innerHTML;
}

// Helper fonction
function formatDate(dateStr) {
    if (!dateStr) return "";
    const date = new Date(dateStr);
    return date.toLocaleDateString("fr-FR", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    });
}

// Variables pour le drag and drop
let draggedRow = null;
let draggedTaskData = null; // Pour stocker la tâche à déplacer entre pages
let pendingDrop = false;    // Pour savoir si on attend un drop sur la nouvelle page
let currentPage = 1; // Page initiale
let totalPages = 1;  // Nombre total de pages
const tasksPerPage = 6; // Nombre de tâches par page

// Fonction pour afficher ou masquer les zones de drop
function showDropZones(show = true) {
    document.getElementById("dropPrevPage").classList.toggle("active", show && currentPage > 1);
    document.getElementById("dropNextPage").classList.toggle("active", show && currentPage < totalPages);
}

// Fonction pour supprimer les styles de survol
function clearHoverStyles() {
    const tableBody = document.querySelector("#tasksTable tbody");
    if (tableBody) {
        tableBody.querySelectorAll("tr").forEach(tr => {
            tr.style.borderTop = "";
            tr.style.cursor = "";
        });
    }
}

// Fonction pour afficher la popup des médias
function showMediaPopup(mediaList) {
    const ul = document.getElementById('mediaList');
    ul.innerHTML = '';

    mediaList.forEach(media => {
        const li = document.createElement('li');
        li.innerHTML = `<strong>${escapeHTML(media.nom_media)}:</strong> <a href="${escapeHTML(media.url_media)}" target="_blank">${escapeHTML(media.url_media)}</a>`;
        ul.appendChild(li);
    });

    document.getElementById('mediaPopup').style.display = 'block';
}

// Fonction pour fermer la popup des médias
function closeMediaPopup() {
    document.getElementById('mediaPopup').style.display = 'none';
}

// Fonction pour charger les tâches pour un technicien donné
function loadTachesForTechnicien(Technicien) {
    const start = (currentPage - 1) * tasksPerPage;
    const url = BASE_URL + `/tasks?technicien_id=${encodeURIComponent(Technicien)}&start=${start}&limit=${tasksPerPage}`;

    const xhr = new XMLHttpRequest();
    xhr.open("GET", url, true);

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = safeJsonParse(xhr.responseText);
            if (!response) {
                console.error("Les taches reçues du serveur sont invalides.");
                return;
            }

            // Affectation des taches à la variable globale listeTaches
            const tasks = response.tasks;
            listeTaches = tasks;

            console.log(response.totalTasks);
            totalPages = Math.ceil(response.totalTasks / tasksPerPage);
            taskStartIndex = (currentPage - 1) * tasksPerPage + 1;

            // On refresh la table (qu'on reconstruit de 0) et on applique les filtres éxistants
            RefreshTableAndApplyFilters();

            updatePaginationControls();
        }
    };

    xhr.send(null);
}

// Fonction pour mettre à jour les contrôles de pagination
function updatePaginationControls() {
    document.getElementById("pageNumber").textContent = `Page ${currentPage} / ${totalPages}`;
    document.getElementById("prevPage").disabled = currentPage === 1;
    document.getElementById("nextPage").disabled = currentPage >= totalPages;
}

// Fonction pour changer de page
function changePage(direction) {
    if (direction === 'first') {
        currentPage = 1;
    } else if (direction === 'last') {
        currentPage = totalPages;
    } else {
        currentPage += direction;
    }
    currentPage = Math.max(1, Math.min(currentPage, totalPages));
    updatePaginationControls();
    loadTachesForTechnicien(localStorage.getItem('technicien_courant'));  // Recharger les tâches pour le technicien
}

// Événements pour les boutons de pagination
document.getElementById("resetFiltersBtn").addEventListener("click", resetFilters);

// Fonction pour réinitialiser les filtres
function resetFilters() {
    // Champ de recherche
    document.getElementById("searchInput").value = "";

    // Dates
    document.getElementById("startDate").value = "";
    document.getElementById("endDate").value = "";

    // Pour le select statusFilter, on sélectionne seulement l'option "0"
    const statusSelect = document.getElementById("statusFilter");
    Array.from(statusSelect.options).forEach(option => {
        option.selected = option.value === "0";
    });

    // Ici c'est plus simple car une seule valeur pour le filtre média
    document.getElementById("mediaFilter").value = "0";

    // Clear localStorage pour les filtres
    localStorage.removeItem(SEARCH_INPUT_KEY);
    localStorage.removeItem(START_DATE_KEY);
    localStorage.removeItem(END_DATE_KEY);
    localStorage.removeItem(STATUS_FILTER_KEY);
    localStorage.removeItem(MEDIA_FILTER_KEY);

    // Si aucun filtre n'est présent, alors on peut directement reconstruire
    // le tableau sans passer par la fonction qui évalue les filtres
    initTableauTechnicien(listeTaches);
}

// Événements pour les filtres
document.getElementById("mediaFilter").addEventListener("change", RefreshTableAndApplyFilters);
document.getElementById("statusFilter").addEventListener("change", RefreshTableAndApplyFilters);
document.getElementById("startDate").addEventListener("change", RefreshTableAndApplyFilters);
document.getElementById("endDate").addEventListener("change", RefreshTableAndApplyFilters);
document.getElementById("searchInput").addEventListener("input", RefreshTableAndApplyFilters);

// Fonction pour rafraîchir le tableau et appliquer les filtres
function RefreshTableAndApplyFilters() {
    const mediaFilterValue = document.getElementById("mediaFilter").value;

    // Récupère un tableau de valeurs sélectionnées (castées en chaînes)
    const statutSelect = document.getElementById("statusFilter");
    const selectedStatusValues = Array.from(statutSelect.selectedOptions).map(opt => opt.value);

    const filteredTaches = listeTaches.filter(tache => {
        const hasMedias = Array.isArray(tache.medias) && tache.medias.length > 0;

        // Filtrage par médias
        if (mediaFilterValue === "2" && !hasMedias) return false;
        if (mediaFilterValue === "1" && hasMedias) return false;

        // Filtrage par statut
        const idStatut = String(tache.statut.Id_statut ?? 0);

        // Si "0" est dans les options sélectionnées, on ignore le filtre
        if (!selectedStatusValues.includes("0")) {
            if (!selectedStatusValues.includes(idStatut)) {
                return false;
            }
        }

        const startDateValue = document.getElementById("startDate").value;
        const endDateValue = document.getElementById("endDate").value;

        const start = startDateValue ? normalizeDateToUTC(startDateValue) : null;

        // Filtrage par dates
        const tacheDate = normalizeDateToUTC(parseDate(tache.date_creation_tache ?? '')); // supposée en format ISO
        if (startDateValue && tacheDate < start) {
            return false;
        }
        if (endDateValue) {
            endDate = new Date(endDateValue);
            endDate.setDate(endDate.getDate() + 1);
            endDate.setHours(0, 0, 0, 0); // On évite le décalage horaire
            const end = normalizeDateToUTC(endDate);

            if (tacheDate >= end) return false;
        }

        const searchQuery = document.getElementById("searchInput").value.toLowerCase();

        if (searchQuery) {
            const ticket = (tache.num_ticket_dmd ?? '').toLowerCase();
            const batiment = (tache.nom_batiment ?? '').toLowerCase();
            const lieu = (tache.nom_lieu ?? '').toLowerCase();
            const description = (tache.description_tache ?? '').toLowerCase();

            // Vérifier si le mot-clé de la recherche est dans l'un des champs
            const match = ticket.includes(searchQuery) ||
                batiment.includes(searchQuery) ||
                lieu.includes(searchQuery) ||
                description.includes(searchQuery);

            // Si un match est trouvé, la tâche sera incluse
            return match;
        }

        return true;
    });

    initTableauTechnicien(filteredTaches);
}

// Fonction pour normaliser une date en UTC, utile pour la comparaison de dates inclusives
function normalizeDateToUTC(date) {
    const d = new Date(date);
    return Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()); // Création de la date en UTC (00:00)
}

// Avoir le format année/mois/jour plutot que jour/mois/année
function parseDate(dateStr) {
    const [day, month, year] = dateStr.split("/");
    return new Date(`${year}-${month}-${day}`);
}

// Initialiser le tableau des tâches
function initTableauTechnicien(taches) {

    const tbody = document.querySelector("#tasksTable tbody");
    tbody.innerHTML = "";

    initialTaskMap.clear();
    taches.forEach((tache, index) => {
        initialTaskMap.set(tache.Id_tache, taskStartIndex + index);
    });

    if (taches.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8">Aucune tâche trouvée</td></tr>`;
    }
    else {
        taches.forEach(tache => {
            // Vérifier si cette tâche a des médias associés
            const hasMedias = Array.isArray(tache.medias) && tache.medias.length > 0;
            const mediaHtml = hasMedias
                ? `<button class="btn-media" data-media='${JSON.stringify(tache.medias)}'>Voir</button>`
                : `<button class="btn-media no-media" disabled>Pas de média</button>`;

            const nomStatut = tache.statut ? tache.statut.nom_statut : "Non défini";
            const row = `
                <tr class="draggable" data-task-id="${escapeHTML(tache.Id_tache)}">
                    <td>${escapeHTML(tache.ordre_tache)}</td>
                    <td>${formatDate(tache.date_creation_tache)}</td>
                    <td>${escapeHTML(tache.num_ticket_dmd ?? 'N/A')}</td>
                    <td>${escapeHTML(tache.nom_batiment ?? 'Non spécifié')}</td>
                    <td>${escapeHTML(tache.nom_lieu ?? 'Non spécifié')}</td>
                    <td>${escapeHTML(tache.description_tache)}</td>
                    <td>${mediaHtml}</td>
                    <td>${escapeHTML(nomStatut)}</td>
                </tr>`;
            tbody.insertAdjacentHTML("beforeend", row);
        });
    }

    tbody.querySelectorAll('.btn-media').forEach(button => {
        if (!button.disabled) {
            button.addEventListener('click', function () {
                const mediaData = JSON.parse(this.dataset.media);
                showMediaPopup(mediaData);
            });
        }
    });

    initDragAndDrop();
}

// Fonction pour gérer le survol de la zone de drop
function handleDropZoneOver(e) {
    e.preventDefault();
    e.currentTarget.classList.add("over");
}

// Fonction pour gérer la sortie de la zone de drop
function handleDropZoneLeave(e) {
    e.currentTarget.classList.remove("over");
}

// Fonction pour gérer le drop sur la zone de drop
function handleDropZoneDrop(e, direction) {
    e.preventDefault();
    e.currentTarget.classList.remove("over");
    if (!draggedTaskData) return;

    // Change la page
    if (direction === "prev" && currentPage > 1) {
        currentPage--;
    } else if (direction === "next" && currentPage < totalPages) {
        currentPage++;
    } else {
        return;
    }

    // Recharge la page et mémorise la tâche à déplacer
    window.taskToDrop = draggedTaskData;
    pendingDrop = true;
    loadTachesForTechnicien(localStorage.getItem('technicien_courant'));
}

// Attache les événements pour le changement de page
document.getElementById("dropPrevPage").addEventListener("dragover", handleDropZoneOver);
document.getElementById("dropPrevPage").addEventListener("dragleave", handleDropZoneLeave);
document.getElementById("dropPrevPage").addEventListener("drop", e => handleDropZoneDrop(e, "prev"));

document.getElementById("dropNextPage").addEventListener("dragover", handleDropZoneOver);
document.getElementById("dropNextPage").addEventListener("dragleave", handleDropZoneLeave);
document.getElementById("dropNextPage").addEventListener("drop", e => handleDropZoneDrop(e, "next"));

// Fonction pour configurer le drag and drop
function setupDragAndDrop() {
    const tableBody = document.querySelector("#tasksTable tbody");
    if (!tableBody) {
        console.warn("Table body not found");
        return;
    }

    // Écouter les événements de drag & drop
    tableBody.addEventListener("dragstart", function (e) {
        if (e.target && e.target.nodeName === "TR") {
            draggedRow = e.target;
            draggedTaskData = {
                id: e.target.dataset.taskId
            };
            // Sauvegarder l'état du drag dans le localStorage
            localStorage.setItem("draggedTask", JSON.stringify(draggedTaskData));
            showDropZones(true);
            draggedRow.style.opacity = "0.5";
            console.log(`Drag Start: Tâche ID ${draggedRow.dataset.taskId}`);
        }
    });

    // DRAG OVER
    tableBody.addEventListener("dragover", function (e) {
        e.preventDefault();
        const target = e.target.closest("tr");
        if (target && target !== draggedRow) {
            target.style.borderTop = "2px solid #f9bb30";
            target.style.cursor = "move";
        }
    });

    // DRAG LEAVE
    tableBody.addEventListener("dragleave", function (e) {
        const target = e.target.closest("tr");
        if (target) {
            target.style.borderTop = "";
        }
    });

    // DROP
    tableBody.addEventListener("drop", function (e) {
        e.preventDefault();
        clearHoverStyles();

        const target = e.target.closest("tr");

        // Si nous attendons un drop de la tâche depuis une autre page
        if (pendingDrop && window.taskToDrop && target) {
            const taskIdToMove = Number(window.taskToDrop.id);
            const targetTaskId = Number(target.dataset.taskId);

            // Récupérer l'ordre des tâches sur la page actuelle
            const rows = Array.from(document.querySelectorAll('#tasksTable tbody tr'));
            let ids = rows.map(row => Number(row.dataset.taskId));

            // Retirer la tâche à déplacer si elle est déjà dans la liste
            ids = ids.filter(id => id !== taskIdToMove);

            // Trouver la position de la tâche cible
            const targetIndex = ids.indexOf(targetTaskId);

            // Insérer la tâche déplacée avant ou après la tâche cible
            ids.splice(targetIndex, 0, taskIdToMove);

            // Mettre à jour l'ordre des tâches
            const taskOffset = (currentPage - 1) * tasksPerPage;
            ids.forEach((id, i) => {
                const newOrder = taskOffset + i + 1;
                idToOrderModified.set(id, newOrder);
            });

            // Recharger l'affichage des tâches
            loadTachesForTechnicien(localStorage.getItem('technicien_courant'));

            // Réinitialiser l'état du drag
            window.taskToDrop = null;
            pendingDrop = false;

            return;
        }

        if (draggedRow && target && draggedRow !== target) {
            const rows = Array.from(document.querySelectorAll('#tasksTable tbody tr'));
            let start = rows.indexOf(draggedRow);
            let end = rows.indexOf(target);

            // Calcul de la nouvelle position
            const insertBefore = start > end;
            if (insertBefore) {
                target.parentNode.insertBefore(draggedRow, target);
            } else {
                target.parentNode.insertBefore(draggedRow, target.nextSibling);
            }

            // Mise à jour de l'ordre affiché et de la map
            const updatedRows = Array.from(tableBody.querySelectorAll("tr"));
            const taskOffset = (typeof currentPage !== "undefined" && typeof tasksPerPage !== "undefined")
                ? (currentPage - 1) * tasksPerPage
                : 0;

            updatedRows.forEach((row, i) => {
                const taskId = row.dataset.taskId;
                const newOrder = taskOffset + i + 1; // +1 pour commencer à 1
                idToOrderModified.set(Number(taskId), newOrder);
            });

            draggedRow.style.opacity = "";

            // Reset
            draggedRow = null;
            lastTarget = null;
        }
    });

    // DRAG END
    tableBody.addEventListener("dragend", function () {
        if (draggedRow) {
            draggedRow.style.opacity = "";
        }
        showDropZones(false);
        draggedRow = null;
        draggedTaskData = null;
    });
}

function initDragAndDrop() {
    const tableBody = document.querySelector("#tasksTable tbody");
    if (!tableBody) {
        console.warn("Table body not found");
        return;
    }

    // Rendre chaque ligne draggable
    tableBody.querySelectorAll("tr").forEach((tr) => {
        tr.setAttribute("draggable", "true");
    });
}

// Supprimer la fonction autoChangePageOnHover existante et remplacer par :

let hoverTimeout;

function setupPageChangeHover() {
    const dropPrevPage = document.getElementById("dropPrevPage");
    const dropNextPage = document.getElementById("dropNextPage");

    const handleHoverStart = (direction) => {
        // Démarrer le timeout seulement si pas déjà en cours
        if (!hoverTimeout) {
            hoverTimeout = setTimeout(() => {
                if (direction === 'prev' && currentPage > 1) {
                    currentPage--;
                    loadTachesForTechnicien(localStorage.getItem('technicien_courant'));
                } else if (direction === 'next' && currentPage < totalPages) {
                    currentPage++;
                    loadTachesForTechnicien(localStorage.getItem('technicien_courant'));
                }
                hoverTimeout = null;
            }, 800);
        }
    };

    const handleHoverEnd = () => {
        // Annuler le timeout si la souris quitte la zone
        if (hoverTimeout) {
            clearTimeout(hoverTimeout);
            hoverTimeout = null;
        }
    };

    // Gestionnaire pour la zone précédente
    dropPrevPage.addEventListener('dragover', (e) => {
        e.preventDefault();
        handleHoverStart('prev');
    });

    dropPrevPage.addEventListener('dragleave', handleHoverEnd);
    dropPrevPage.addEventListener('drop', handleHoverEnd);

    // Gestionnaire pour la zone suivante
    dropNextPage.addEventListener('dragover', (e) => {
        e.preventDefault();
        handleHoverStart('next');
    });

    dropNextPage.addEventListener('dragleave', handleHoverEnd);
    dropNextPage.addEventListener('drop', handleHoverEnd);
}

// Initialiser une seule fois au chargement
document.addEventListener("DOMContentLoaded", setupPageChangeHover);

document.addEventListener("DOMContentLoaded", function () {
    const imprimerBtn = document.getElementById("imprimerFeuilleRoute");

    if (imprimerBtn) {
        imprimerBtn.addEventListener("click", function (e) {
            e.preventDefault(); // Évite l'envoi du formulaire

            const select = document.getElementById("technicienSelect");
            const techId = select.value;

            if (!techId) {
                alert(
                    "Veuillez sélectionner un technicien avant d'imprimer sa feuille de route."
                );
                return;
            }

            // Ouvre la feuille de route du technicien sélectionné
            window.open(
                `${BASE_URL}/feuillederoute/imprimer/${encodeURIComponent(techId)}`,
                "_blank"
            );
            

        });
    }
});
