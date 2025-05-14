// On setup la print list
document.addEventListener("DOMContentLoaded", function () {
    displayPrintList();
});

const PRESENT_KEY = "techniciens_presents";
const TECHNICIEN_KEY = "technicien_courant";

document.addEventListener("DOMContentLoaded", function () {
    // Listener délégué sur le tbody pour gérer les checkbox avancées
    const tbody = document.querySelector("#printTable tbody");
    tbody.addEventListener("change", function (event) {
        const checkbox = event.target;
        if (checkbox.matches('input[type="checkbox"][id^="checkbox-taches-"]')) {
            const techId = checkbox.id.replace('checkbox-taches-', '');
            const advancedSettingsDiv = document.getElementById(`advanced-settings-${techId}`);
            if (advancedSettingsDiv) {
                advancedSettingsDiv.style.display = checkbox.checked ? 'block' : 'none';
            }
        }
    });
});

function getPresentTechnicians() {
    let techs = localStorage.getItem(PRESENT_KEY);
    return techs ? JSON.parse(techs) : [];
}

function setPresentTechnicians(techs) {
    localStorage.setItem(PRESENT_KEY, JSON.stringify(techs));
}

// Helper function
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

function voirTaches(id) {
    localStorage.setItem("technicien_courant", id);
    window.location.href = BASE_URL + "/feuillederoute/liste/taches";
}

// Le JS pour liste d'impression :
// Affiche la liste d'impression
function displayPrintList() {
    const techs = getPresentTechnicians();
    const tbody = document.querySelector("#printTable tbody");
    tbody.innerHTML = "";

    if (techs.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="3">Aucun technicien présent pour l\'impression.</td></tr>';
        return;
    }

    // Affiche le tableau
    techs.forEach((tech) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
        <td>${tech.name}</td>
        <td style="display: flex; align-items: center; gap: 10px;">
            <button class="action-btn imprimer-btn" data-tech-id="${tech.id}">Imprimer la feuille de route</button>
            <button class="action-btn" onclick="voirTaches('${tech.id}')">Voir les taches</button>
            <button class="action-btn" onclick="removeTechnician('${tech.id}')">Retirer de la liste d'impression</button>
            
            <label for="checkbox-taches-${tech.id}">Paramètres avancés</label>
            <input id="checkbox-taches-${tech.id}" type="checkbox">

            <div class="advanced-settings" id="advanced-settings-${tech.id}" style="display: none;">
                <label for="nombre-taches-${tech.id}" style="font-size: 0.95em; color:rgb(33, 31, 27); margin-right: 2px;">
                Saisissez le nombre de tâches à imprimer (Entrez 0 pour imprimer toutes les tâches) :
                </label>
                <input type="number" min="0" value="1" id="nombre-taches-${tech.id}" class="nombre-taches" data-tech-id="${tech.id}" style="width: 70px;" placeholder="1">
                <div style="height: 15px;"></div>
                <label for="debut-taches-${tech.id}" style="font-size: 0.95em; color:rgb(33, 31, 27); margin-right: 2px;">
                Indiquez à partir de quelle tâche commencer l'impression :
                </label>
                <input type="number" min="1" value="1" id="debut-taches-${tech.id}" class="nombre-taches" data-tech-id="${tech.id}" style="width: 70px;" placeholder="1">
            </div>
        </td>

        `;
        tbody.appendChild(tr);

        // Ajouter l'événement click pour le bouton d'impression
        tr.querySelector('.imprimer-btn').addEventListener('click', function () {
            const techId = this.dataset.techId;
            const checkbox = tr.querySelector(`#checkbox-taches-${techId}`);
            let url = `${BASE_URL}/feuillederoute/imprimer?tech_id=${techId}`;

            if (checkbox && checkbox.checked) {
                const nombreTaches = tr.querySelector(`#nombre-taches-${techId}`)?.value || 1;
                const debutTaches = tr.querySelector(`#debut-taches-${techId}`)?.value || 1;

                url += `&nombreTask=${encodeURIComponent(nombreTaches)}&debutTask=${encodeURIComponent(debutTaches)}`;
            }

            window.open(url, '_blank');
        });

    });
}

// Affiche la popup
let TechPopup = document.getElementById("openTechPopup");
if (TechPopup) {
    TechPopup.addEventListener("click", function () {
        fetchTechnicians();
        document.getElementById("techPopup").style.display = "block";
    });
}

// Ferme la popup
TechPopupClose = document.getElementById("closeTechPopup");
if (TechPopupClose) {
    TechPopupClose.addEventListener("click", function () {
        document.getElementById("techPopup").style.display = "none";
    });
}

// Fonction pour récupérer les techniciens
function fetchTechnicians() {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", BASE_URL + "/feuillederoute/liste/techniciens", true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const data = JSON.parse(xhr.responseText);
            if (data && data.status && data.status === "success") {
                const techniciens = data.technicians;
                populateTechniciansTable(techniciens);
            }
        }
    };

    xhr.send();
}

// Fonction pour remplir le tableau des techniciens
function populateTechniciansTable(techniciens) {
    const tbody = document.querySelector("#techListTable tbody");
    tbody.innerHTML = "";

    const presentTechs = getPresentTechnicians();

    techniciens.forEach((tech) => {
        const id = tech.Id_utilisateur;
        const name = `${tech.prenom_utilisateur} ${tech.nom_utilisateur}`;
        const isPresent = presentTechs.some((t) => t.id === id.toString()); // Assure une comparaison en string

        const tr = document.createElement("tr");
        tr.innerHTML = `
    <td>${escapeHTML(name)}</td>
    <td>
      <button class="addBtn" ${isPresent ? "disabled" : ""
            } data-id="${id}" data-name="${name}">
        ${isPresent ? "Déjà ajouté" : "Ajouter"}
      </button>
      ${isPresent
                ? `<button class="removeBtn" data-id="${id}">Retirer</button>`
                : ""
            }
    </td>
  `;
        tbody.appendChild(tr);
    });

    addPopupEventListeners();
    displayPrintList();
}

// Fonction pour ajouter les événements aux boutons de la popup
function addPopupEventListeners() {
    document.querySelectorAll(".addBtn").forEach((btn) => {
        btn.addEventListener("click", function () {
            const id = this.dataset.id;
            const name = this.dataset.name;
            let presentTechs = getPresentTechnicians();

            if (presentTechs.some((t) => t.id === id)) return;

            presentTechs.push({
                id,
                name,
            });
            setPresentTechnicians(presentTechs);
            fetchTechnicians(); // Refresh
        });
    });

    // Ajoute les événements pour le bouton de suppression
    document.querySelectorAll(".removeBtn").forEach((btn) => {
        btn.addEventListener("click", function () {
            const id = this.dataset.id;
            removeTechnician(id);
            fetchTechnicians(); // Refresh
        });
    });
}

// Fonction pour supprimer un technicien de la liste
function removeTechnician(id) {
    let techs = getPresentTechnicians();
    techs = techs.filter((t) => t.id !== id);
    setPresentTechnicians(techs);
    displayPrintList();
}
