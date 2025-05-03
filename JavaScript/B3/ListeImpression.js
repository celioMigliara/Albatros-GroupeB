
// On setup la print list
document.addEventListener("DOMContentLoaded", function () 
{
    displayPrintList();
});

const PRESENT_KEY = "techniciens_presents";
const TECHNICIEN_KEY = "technicien_courant";

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
        '<tr><td colspan="2">Aucun technicien présent pour l\'impression.</td></tr>';
        return;
    }
    
    // Affiche le tableau
    techs.forEach((tech) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
        <td>${tech.name}</td>
        <td>
        <button class="action-btn" onclick="window.open('${BASE_URL}/feuillederoute/imprimer?tech_id=${tech.id}', '_blank')">Voir la feuille de route</button>
        <button class="action-btn" onclick="voirTaches('${tech.id}')">Voir les taches</button>
        <button class="action-btn" onclick="removeTechnician('${tech.id}')">Supprimer</button>
        </td>
        `;
        tbody.appendChild(tr);
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