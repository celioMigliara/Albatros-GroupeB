// Définition des clés pour les filtres du localStorage
const SEARCH_INPUT_KEY = "searchInput";
const START_DATE_KEY = "startDate";
const END_DATE_KEY = "endDate";
const STATUS_FILTER_KEY = "statusFilter";
const MEDIA_FILTER_KEY = "mediaFilter";
const INVALID_STATUT = 0;
const DEFAULT_TACHES_PAR_PAGE = 10;

// Mettre ce flag à true si on veut changer le comportement des médias
// Par défaut (à false), l'url du média est affichée et l'admin clique dessus pour accéder à la
// ressource qui est hébérgée sur internet.
// Avec le flag à true, l'url du média devient le nom du fichier qui est contenu
// dans /Public/Uploads/
// Et donc on ouvre le fichier qui est stocké en local
let OUVRIR_MEDIA_EN_TANT_QUE_FICHIER_LOCAL = true;

// Définition des clés pour la gestion des techniciens du localStorage
const PRESENT_KEY = "techniciens_presents";
const TECHNICIEN_KEY = "technicien_courant";
let initialTaskMap = new Map();
let idToOrderModified = new Map();
let listeTaches = null;

// Variables pour le drag and drop
let draggedRow = null;
let currentPage = 1; // Page initiale
let totalPages = 1;  // Nombre total de pages
const tasksPerPage = DEFAULT_TACHES_PAR_PAGE; // Nombre de tâches par page

document.getElementById("saveOrder").addEventListener("click", EnregistrerOrdre);

// Événements pour les boutons de pagination
document.getElementById("resetFiltersBtn").addEventListener("click", resetFilters);

// Événements pour les filtres
document.getElementById("mediaFilter").addEventListener("change", RefreshTableAndApplyFilters);
document.getElementById("statusFilter").addEventListener("change", RefreshTableAndApplyFilters);
document.getElementById("startDate").addEventListener("change", RefreshTableAndApplyFilters);
document.getElementById("endDate").addEventListener("change", RefreshTableAndApplyFilters);
document.getElementById("searchInput").addEventListener("input", RefreshTableAndApplyFilters);

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

document.getElementById("technicienSelect").addEventListener("change", function () {
    const selectedValue = this.value;

    localStorage.setItem(TECHNICIEN_KEY, selectedValue);
    loadTachesForTechnicien(selectedValue);
});

document.getElementById("listeImpression").addEventListener("click", function () {
    window.location.href = BASE_URL + "/feuillederoute/liste/impression";
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
        // Sélectionne par défaut l'option "0" s'il n'y a rien en localStorage
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

document.addEventListener("DOMContentLoaded", function () {
    const imprimerBtn = document.getElementById("imprimerFeuilleRoute");

    if (imprimerBtn) {
        imprimerBtn.addEventListener("click", function (e) {
            e.preventDefault(); // Évite l'envoi du formulaire

            const select = document.getElementById("technicienSelect");
            const techId = select.value;

            if (!techId) {
                CreateSimplePopup(
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

// Fonction pour restaurer le technicien courant
document.addEventListener("DOMContentLoaded", function () {
    setupDragAndDrop();
    // Récupère l'ID du technicien sauvegardé dans le localStorage
    const savedTechnicien = localStorage.getItem(TECHNICIEN_KEY);

    // Vérifie que la valeur existe et n'est pas "0" (valeur par défaut du select)
    if (savedTechnicien && savedTechnicien != "0") {
        const technicienSelect = document.getElementById("technicienSelect");
        const options = technicienSelect.options;
        let found = false;

        // Parcourt toutes les options du select pour vérifier si la valeur sauvegardée existe toujours
        for (let i = 0; i < options.length; i++) {
            if (options[i].value === savedTechnicien) {
                found = true;
                break;
            }
        }

        if (found) {
            // Si la valeur est valide, on définit le select sur cette valeur
            technicienSelect.value = savedTechnicien;

            // Et on charge les tâches associées à ce technicien
            loadTachesForTechnicien(savedTechnicien);
        } else {
            // Si la valeur n'existe plus (ex : technicien supprimé), on la supprime du localStorage
            localStorage.removeItem(TECHNICIEN_KEY);
        }
    }
});

// Fonction pour ajouter un technicien à la liste des présents
let ajouterPrintList = document.getElementById("ajouterPrintList");
if (ajouterPrintList) {
    ajouterPrintList.addEventListener("click", function () {
        const select = document.getElementById("technicienSelect");
        const selectedValue = select.value;
        const selectedText = select.options[select.selectedIndex].text;

        if (!selectedValue || selectedValue == 0) {

            CreateSimplePopup("Veuillez sélectionner un technicien.");
            return;
        }

        let presentTechs = getPresentTechnicians();
        if (presentTechs.some((t) => t.id === selectedValue)) {

            CreateSimplePopup("Ce technicien est déjà dans la liste.");
            return;
        }

        presentTechs.push({
            id: selectedValue,
            name: selectedText,
        });

        setPresentTechnicians(presentTechs);

        CreateSimplePopup("Technicien ajouté à la liste des présents.");
    });
}

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

function EnregistrerOrdre(displayPopup = true) {
    if (idToOrderModified.size === 0) {
        if (displayPopup) {
            CreateSimplePopup("Aucune modification à sauvegarder.")
        }
        return;
    }

    let queryString = "";
    let index = 0;
    for (let [id, order] of idToOrderModified) {
        if (initialTaskMap.get(id) == order) {
            console.log(
                "On skip l'element " + id + " avec le numero d'ordre " + order
                + " car aucun changement n'est détécté."
            );
            continue;
        }

        queryString += `&changes[${index}][id]=${encodeURIComponent(
            id
        )}&changes[${index}][order]=${encodeURIComponent(order)}`;
        index++;
    }

    if (index == 0) {
        if (displayPopup) {
            CreateSimplePopup("Aucune modification à sauvegarder.")
        }
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open("POST", BASE_URL + "/feuillederoute/ordre/update", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            xhrChangeOrderCallback(xhr, displayPopup);
        }
    };

    // Rajouter le token csrf manuellement
    const csrfToken = document.querySelector('input[name="csrf_token"]').value;
    queryString += "&csrf_token=" + encodeURIComponent(csrfToken);

    if (queryString.charAt(0) === "&") {
        queryString = queryString.slice(1);
    }

    xhr.send(queryString);
}

function xhrChangeOrderCallback(xhr, displayPopup) {
    let JsonReponse = safeJsonParse(xhr.responseText);
    if (!JsonReponse) {
        console.log(xhr.responseText);
        return;
    }
    else {
        if (displayPopup) {
            CreateSimplePopup(JsonReponse.message)
        }

        console.log(JsonReponse.message);
    }

    const selectedTech = localStorage.getItem(TECHNICIEN_KEY);
    loadTachesForTechnicien(selectedTech);
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
        let mediaUrl = media.url_media;
        if (OUVRIR_MEDIA_EN_TANT_QUE_FICHIER_LOCAL === true) {
            mediaUrl = BASE_URL + "/Public/Uploads/" + mediaUrl;
        }

        li.innerHTML = `<strong>${escapeHTML(media.nom_media)}:</strong> <a href="${escapeHTML(mediaUrl)}" target="_blank">${escapeHTML(media.url_media)}</a>`;
        ul.appendChild(li);
    });

    document.getElementById('mediaPopup').style.display = 'block';
}

// Fonction pour fermer la popup des médias
function closeMediaPopup() {
    document.getElementById('mediaPopup').style.display = 'none';
}

// Fonction pour créer et afficher le popup
function CreateSimplePopup(message) {
    document.getElementById("popup-message").innerText = message;
    document.getElementById("popup").style.display = "block";
}

// Fonction pour fermer le popup
function closeSimplePopup() {
    document.getElementById("popup").style.display = "none";
}

var xhrLoadTasks = null;

// Fonction pour charger les tâches pour un technicien donné
function loadTachesForTechnicien(Technicien) {
    const start = (currentPage - 1) * tasksPerPage;
    const url = BASE_URL + `/tasks/ongoing?technicien_id=${encodeURIComponent(Technicien)}&start=${start}&limit=${tasksPerPage}`;

    // On cancel la requete en cours pour laisser place à la nouvelle requete, pour éviter les desync au niveaux de taches <-> numero de pages
    if (xhrLoadTasks && xhrLoadTasks.readyState > 0 && xhrLoadTasks.readyState < 4) {
        xhrLoadTasks.abort();
    }

    xhrLoadTasks = new XMLHttpRequest();
    xhrLoadTasks.open("GET", url, true);

    xhrLoadTasks.onreadystatechange = function () {
        if (xhrLoadTasks.readyState === 4 && xhrLoadTasks.status === 200) {
            const response = safeJsonParse(xhrLoadTasks.responseText);
            if (!response) {
                console.error("Les taches reçues du serveur sont invalides.");
                return;
            }

            // Affectation des taches à la variable globale listeTaches
            const tasks = response.tasks;
            listeTaches = tasks;

            // On calcule le nombre de pages totales qu'on peut avoir (sans en avoir request les données)
            totalPages = Math.max(1, Math.ceil(response.totalTasks / tasksPerPage));

            // On refresh la table (qu'on reconstruit de 0) et on applique les filtres éxistants
            RefreshTableAndApplyFilters();

            // On update la pagination
            updatePaginationControls();
        }
    };

    xhrLoadTasks.send(null);
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

    // Update de la current page
    currentPage = Math.max(1, Math.min(currentPage, totalPages));

    // Update de la pagination
    updatePaginationControls();

    // On load les nouvelles taches
    loadTachesForTechnicien(localStorage.getItem(TECHNICIEN_KEY));  // Recharger les tâches pour le technicien
}

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
        const idStatut = String(tache.statut.id_statut ?? INVALID_STATUT);

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

// Initialiser le tableau des tâches
function initTableauTechnicien(taches) {

    const tbody = document.querySelector("#tasksTable tbody");
    tbody.innerHTML = "";

    initialTaskMap.clear();
    taches.forEach((tache) => {
        initialTaskMap.set(tache.Id_tache, tache.ordre_tache);
    });

    if (taches.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8">Aucune tâche trouvée</td></tr>`;
    }
    else {
        // On récupère l'index supposé en fonction de la page. On oublie pas le +1 car ca commence à 1 et non à 0
        let index = 1 + ((currentPage - 1) * tasksPerPage);
        taches.forEach(tache => {
            // Vérifier si cette tâche a des médias associés
            const hasMedias = Array.isArray(tache.medias) && tache.medias.length > 0;
            const mediaHtml = hasMedias
                ? `<button class="btn-media" data-media='${JSON.stringify(tache.medias)}'>Voir</button>`
                : `<button class="btn-media no-media" disabled>Pas de média</button>`;

            const idStatut = tache.statut ? tache.statut.id_statut : INVALID_STATUT;
            const nomStatut = tache.statut ? tache.statut.nom_statut : "Non défini";

            if (index != tache.ordre_tache) {
                idToOrderModified.set(tache.Id_tache, index);
                console.log("Desync de l'ordre de la tache ["
                    + tache.Id_tache + "] qui est à " + tache.ordre_tache + " à la place de " + index);
            }

            const row = `
                <tr class="draggable" data-task-id="${escapeHTML(tache.Id_tache)}" data-task-statut="${idStatut}">
                    <td>${escapeHTML(index)}</td>
                    <td>${formatDate(tache.date_creation_tache)}</td>
                    <td>${escapeHTML(tache.num_ticket_dmd ?? 'N/A')}</td>
                    <td>${escapeHTML(tache.nom_batiment ?? 'Non spécifié')}</td>
                    <td>${escapeHTML(tache.nom_lieu ?? 'Non spécifié')}</td>
                    <td>${escapeHTML(tache.description_tache)}</td>
                    <td>${mediaHtml}</td>
                    <td>${escapeHTML(nomStatut)}</td>
                </tr>`;
            tbody.insertAdjacentHTML("beforeend", row);
            index++;
        });

        // Si on a trouvé des incohérences, alors on enregistre le nouvel ordre des taches
        if (idToOrderModified.size > 0) {
            EnregistrerOrdre(false);
        }
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

function modifierOrdreTaches(ordreTacheSource, ordreTacheTarget) {
    const rows = Array.from(document.querySelectorAll('#tasksTable tbody tr'));
    if (!rows) {
        console.warn("Les rows du tableau de taches sont invalides");
        return;
    }

    let start = rows[ordreTacheSource];
    let end = rows[ordreTacheTarget];
    if (!start) {
        CreateSimplePopup("Veuillez séléctionner un ordre de tache pour le début valide.");
        return;
    }
    if (!end) {
        CreateSimplePopup("Veuillez séléctionner un ordre de tache pour la fin valide.");
        return;
    }

    // Calcul de la nouvelle position
    const insertBefore = ordreTacheSource > ordreTacheTarget;
    if (insertBefore) {
        end.parentNode.insertBefore(start, end);
    } else {
        end.parentNode.insertBefore(start, end.nextSibling);
    }

    // Mise à jour de l'ordre affiché et de la map
    const updatedRows = Array.from(document.querySelectorAll('#tasksTable tbody tr'));
    const taskOffset = (currentPage - 1) * tasksPerPage;

    updatedRows.forEach((row, index) => {
        const taskId = row.dataset.taskId;
        const newOrder = taskOffset + index + 1; // +1 pour commencer à 1
        idToOrderModified.set(Number(taskId), newOrder);
    });
}

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
            draggedRow.style.opacity = "0.5";
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

        if (draggedRow && target && draggedRow !== target) {
            const rows = Array.from(document.querySelectorAll('#tasksTable tbody tr'));
            let start = rows.indexOf(draggedRow);
            let end = rows.indexOf(target);

            modifierOrdreTaches(start, end);
        }
    });

    // DRAG END
    tableBody.addEventListener("dragend", function () {
        if (draggedRow) {
            draggedRow.style.opacity = "";
            draggedRow = null;
        }
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

// Gestion de la popup de modification d'ordre
document.addEventListener("DOMContentLoaded", function () {
    const openPopupBtn = document.getElementById("openModifOrdrePopup");
    const closePopupBtn = document.querySelector("#modifOrdrePopup .fermer-popup");
    const modifOrdreBtn = document.getElementById("modifOrdreTache");
    const confirmerBtn = document.getElementById("confirmerModifOrdre");
    const popup = document.getElementById("modifOrdrePopup");

    if (openPopupBtn) {
        openPopupBtn.addEventListener("click", function () {
            popup.style.display = "flex";
        });
    }

    if (closePopupBtn) {
        closePopupBtn.addEventListener("click", function () {
            popup.style.display = "none";
        });
    }

    if (confirmerBtn) {
        confirmerBtn.addEventListener("click", function () {
            popup.style.display = "none";
        });
    }

    if (modifOrdreBtn) {
        modifOrdreBtn.addEventListener("click", function () {
            const start = document.getElementById("sourceTacheOrdre").value;
            const end = document.getElementById("targetTacheOrdre").value;
            if (!start || !end) {
                CreateSimplePopup("Veuillez renseigner les champs de saisie pour l'ordre des taches.");
                return;
            }

            if (start <= 0) {
                CreateSimplePopup("Veuillez renseigner un nombre supérieur à 0 pour l'ordre de la tache source (ex: 1)");
                return
            }

            if (end <= 0) {
                CreateSimplePopup("Veuillez renseigner un nombre supérieur à 0 pour l'ordre de la tache destination (ex: 1)");
                return;
            }

            const currentTechId = localStorage.getItem(TECHNICIEN_KEY);
            const csrfToken = document.querySelector('input[name="csrf_token"]').value;
            const params = `techId=${encodeURIComponent(currentTechId)}&start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}&csrf_token=${encodeURIComponent(csrfToken)}`;

            const xhr = new XMLHttpRequest();
            xhr.open("POST", BASE_URL + "/feuillederoute/ordre/update/lineaire", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    xhrChangeOrderCallback(xhr, true);
                }
            };

            xhr.send(params);
        });
    }
});

