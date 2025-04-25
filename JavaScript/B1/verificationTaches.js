// verificationTaches.js
document.addEventListener("DOMContentLoaded", function() {
    // Récupération du formulaire grâce à son id "createTaskForm"
    const form = document.getElementById("createTaskForm");

    form.addEventListener("submit", function(event) {
        let erreurs = [];

        // Vérification du champ "Nom de la tâche"
        const nomTache = document.getElementById("nom_tache");
        if (!nomTache.value.trim()) {
            erreurs.push("Le nom de la tâche est obligatoire.");
        }

        // Vérification du champ "Technicien"
        const technicien = document.getElementById("technicien");
        if (!technicien.value.trim()) {
            erreurs.push("Veuillez sélectionner un technicien.");
        }

        // Vérification du champ "Date"
        const dateField = document.getElementById("date");
        if (!dateField.value.trim()) {
            erreurs.push("La date est obligatoire.");
        }

        // Vérification du champ "Description"
        const description = document.getElementById("description");
        if (!description.value.trim()) {
            erreurs.push("La description est obligatoire.");
        } else if (description.value.length > 512) {
            erreurs.push("La description ne doit pas dépasser 512 caractères.");
        }
        

        // Si des erreurs sont détectées, empêcher l'envoi du formulaire et afficher les messages d'erreur
        if (erreurs.length > 0) {
            event.preventDefault();
            alert(erreurs.join("\n"));
        }
    });
});
