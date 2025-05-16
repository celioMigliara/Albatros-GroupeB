function openAddPopup() {
    document.getElementById("addPopup").style.display = "block";
    document.getElementById("overlay").style.display = "block";
}

function openDeletePopup(message){
    document.getElementById("deletePopupMessage").innerText = message;
    document.getElementById("deletePopup").style.display = "block";
    document.getElementById("overlay").style.display = "block";
}

function openImportPopup() {
    document.getElementById("importPopup").style.display = "block";
    document.getElementById("overlay").style.display = "block";
}
function closeLieuPopup() {
    document.getElementById("addPopup").style.display = "none";
    document.getElementById("deletePopup").style.display = "none";
    document.getElementById("overlay").style.display = "none";
}
function closeSitePopup() {
    document.getElementById("importPopup").style.display = "none";
    document.getElementById("addPopup").style.display = "none";
    document.getElementById("overlay").style.display = "none";
}
function closeBatimentPopup() {
    document.getElementById("addPopup").style.display = "none";
    document.getElementById("deletePopup").style.display = "none";
    document.getElementById("overlay").style.display = "none";
}
function closeLieuDetailPopup() {
    document.getElementById("deletePopup").style.display = "none";
    document.getElementById("overlay").style.display = "none";
}


function validateUpload() {
    const fileInput = document.getElementById('excel_file');
    const importBtn = document.getElementById('openImport');

    const file = fileInput.files[0];
    if (!file || !file.name.endsWith('.xlsx')) {
        alert('Veuillez sélectionner un fichier .xlsx valide.');
        return false;
    }

    // Fermer la popup immédiatement
    closePopup();

    // Désactiver le bouton pour éviter les clics répétés
    importBtn.disabled = true;
    importBtn.value = "Import en cours...";

    return true; // on laisse le formulaire se soumettre
}