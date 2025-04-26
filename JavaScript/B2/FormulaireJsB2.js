// Attendre que le DOM soit entièrement chargé avant d’exécuter le JS
document.addEventListener("DOMContentLoaded", function () {

    // === Sélection des éléments du DOM ===
    const form = document.getElementById("form-demande");
    const site = document.getElementById("site");
    const batiment = document.getElementById("batiment");
    const lieu = document.getElementById("lieu");
    const fileInput = document.getElementById("piece_jointe");
    const popupMessage = document.getElementById("popup-message");
    const popup = document.getElementById("popup");
    const filePreviewZone = document.getElementById("file-preview-zone");


    /************Localisation*******************/

    let locationData = {}; // ce sera rempli par un fetch au chargement

    fetch(BASE_URL + "/Controller/B2/LocationControllerB2.php") //Appelle le controller
        .then(res => res.json()) // Récupère la réponse JSON
        .then(data => { // Traite la réponse JSON
         
            locationData = data; // Remplit le tableau locationData avec les données du backend
            remplirSelect(site, locationData.sites, "SITE"); // Remplit le select site avec les données du backend
        });


    /************Piece jointe*******************/

    // Taille max autorisée pour un fichier : 1 Mo
    const TAILLE_MAX = 1 * 1024 * 1024;
    // Tableau temporaire contenant les fichiers sélectionnés
    let fichiersSelectionnes = [];


    /************Compteur caractere sujet et description*******************/
    document.getElementById("sujet").addEventListener("input", function () { //Selectione champ sujet et ajout une écouteru d'evenement input
        let text = this.value; //Récup texte actuel du champ 
        document.getElementById("caracteresCount").textContent = text.length; //Met a jour le compteur de caractere sujet
    });

    document.getElementById("description").addEventListener("input", function () {
        let text = this.value;
        document.getElementById("caracteresCountDescription").textContent = text.length;
    });


    // === Vérifie que tous les champs required sont remplis ===
    const verifierChampsObligatoires = () => {
        let valide = true;

        form.querySelectorAll("[required]").forEach(champ => {
            const parent = champ.closest(".formulaire-champs-B2");
            let erreur = parent.querySelector(".erreur-champs-B2") || document.createElement("div");
            erreur.className = "erreur-champs-B2";
            parent.appendChild(erreur);

            if (!champ.value.trim()) {
                champ.classList.add("champ-invalide");

                // Message personnalisé selon le champ
                switch (champ.id) {
                    case 'sujet':
                        erreur.textContent = "Le sujet est obligatoire";
                        break;
                    case 'site':
                        erreur.textContent = "Le choix site est obligatoire";
                        break;
                    case 'batiment':
                        erreur.textContent = "Le choix bâtiment est obligatoire";
                        break;
                    case 'lieu':
                        erreur.textContent = "Le choix lieu est obligatoire";
                        break;
                    default:
                        erreur.textContent = "Ce champ est obligatoire";
                }

                erreur.style.display = "block";
                valide = false;
            } else {
                champ.classList.remove("champ-invalide");
                erreur.style.display = "none";
            }
        });

        return valide;
    };
    // === Supprime l’erreur dès que l’utilisateur modifie un champ ===
    form.querySelectorAll("[required]").forEach(champ => {
        const parent = champ.closest(".formulaire-champs-B2");
        const erreur = parent.querySelector(".erreur-champs-B2");

        const effacerErreur = () => {
            champ.classList.remove("champ-invalide");
            if (erreur) {
                erreur.style.display = "none";
                erreur.textContent = "";
            }
        };

        // Pour les champs texte
        champ.addEventListener("input", effacerErreur);

    });

    // === Gestion du choix de fichiers (upload) ===
    fileInput.addEventListener("change", () => {
        const nouveauxFichiers = Array.from(fileInput.files);
        const erreurFichier = document.getElementById("erreur-fichier");

        //Renitialise le message d'erreur
        let erreurDetectee = false;

        // Réinitialise le message d'erreur fichier
        if (erreurFichier) {
            erreurFichier.textContent = "";
            erreurFichier.style.display = "none";
        }

        // Vérifie chaque fichier sélectionné
        nouveauxFichiers.forEach(fichier => {
            if (fichier.size > TAILLE_MAX) {
                // Fichier trop volumineux
                if (erreurFichier) {
                    erreurFichier.textContent = `Le fichier ${fichier.name} dépasse la taille maximale autorisée de ${TAILLE_MAX / 1024 / 1024} Mo.`;
                    erreurFichier.style.display = "block";
                }
            } else {
                // Vérifie qu’il n’a pas déjà été ajouté
                const estDejaAjoute = fichiersSelectionnes.some(f => f.name === fichier.name && f.size === fichier.size);
                if (!estDejaAjoute) {
                    fichiersSelectionnes.push(fichier);
                }
            }
        });

        if (!erreurDetectee) {
            mettreAJourPreview(); // Met à jour l'affichage des fichiers
        }
    });

    // === Affichage des fichiers choisis et bouton ❌ pour les retirer ===
    function mettreAJourPreview() {
        filePreviewZone.innerHTML = "";



        if (fichiersSelectionnes.length === 0) {
            filePreviewZone.textContent = "Aucun fichier sélectionné";
            return;
        }

        // Crée une ligne par fichier avec un bouton ❌
        fichiersSelectionnes.forEach((file, index) => {
            const ligne = document.createElement("div");
            ligne.className = "fichier-ligne-B2";
            ligne.innerHTML = `
    <span class="nom-fichier-B2">${file.name}</span>
    <button type="button" class="btn-remove-B2" data-index="${index}">❌</button>
`;
            filePreviewZone.appendChild(ligne);
        });

        // Supprime un fichier du tableau en cliquant sur ❌
        document.querySelectorAll(".btn-remove-B2").forEach(btn => {
            btn.addEventListener("click", () => {
                const index = parseInt(btn.getAttribute("data-index"));
                fichiersSelectionnes.splice(index, 1);
                mettreAJourPreview();
            });
        });
    }

    // === Remplissage des select ===
    function remplirSelect(select, items, placeholder) {
        select.innerHTML = `<option value="" disabled selected>${placeholder}</option>`; //Vide le select, option par defaut placeholder de l'html
        items.forEach(item => {
            select.innerHTML += `<option value="${item.id}">${item.nom}</option>`;
        });
    }

    // Quand un site est choisi → charge les bâtiments associés
    site.addEventListener("change", () => {
        //parser l id pour passer de "1" a 1 
        const idSite = parseInt(site.value); //Récupère l'id du site sélectionné
        const batimentsFiltres = locationData.batiments.filter(b => b.id_site === idSite); //Filtrage des bat dans le controller qui appartient au site selectionné 
        remplirSelect(batiment, batimentsFiltres, "BATIMENT"); //Remplissage du select batiment avec les batiments filtrés
        lieu.innerHTML = `<option value="">LIEU</option>`; //Renitialise le select lieu a lieu
    });


    batiment.addEventListener("change", () => {
        const idBat = parseInt(batiment.value);
        const lieuxFiltres = locationData.lieux.filter(l => l.id_batiment === idBat); //Filtre les lieux qui sont rataché au bat selectionné
        remplirSelect(lieu, lieuxFiltres, "LIEU"); //et remplit le select lieu avec les lieux filtrés
    });


    // === Réinitialisation complète du formulaire ===
    const resetFormulaire = () => {
        form.reset();
        fileInput.value = "";
        fichiersSelectionnes = [];
        mettreAJourPreview();
        remplirSelect(site, locationData.sites, "SITE");
        batiment.innerHTML = '<option value="">BATIMENT</option>';
        lieu.innerHTML = '<option value="">LIEU</option>';

        //Si envoie du form alors reset des compteurs
        document.getElementById("caracteresCount").textContent = "0";
        document.getElementById("caracteresCountDescription").textContent = "0";
    };

    //Si bouton annuler alors reset des compteurs
    form.addEventListener("reset", () => {
        document.getElementById("caracteresCount").textContent = "0";
        document.getElementById("caracteresCountDescription").textContent = "0";
    });

    // === Affiche une popup avec le message du backend ===
    const afficherPopup = (message, success = true) => {
        popupMessage.textContent = message;
    
        // Applique un style différent selon le succès ou l’erreur
        popupMessage.classList.remove("popup-message-erreur");
        if (!success) {
            popupMessage.classList.add("popup-message-erreur");
        }
    
        popup.style.display = "block";
        document.getElementById("popup-overlay-B2").style.display = "block";
        document.querySelector(".container-B2").classList.add("blurred-B2");
        document.body.classList.add("no-scroll");
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    // Supprimer l’erreur dès qu’un <select> est modifié
    ["site", "batiment", "lieu"].forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.addEventListener("change", () => {
                //Remonte ds html et renvoie un parametre qui correspont formulaire-champs
                const parent = select.closest(".formulaire-champs-B2");
                //Si parent pas nul, cherche .erreur-champs sinon retourne undefined 
                const erreur = parent?.querySelector(".erreur-champs-B2");
                //Une fois trouvé met les message a non pour masquer 
                select.classList.remove("champ-invalide");
                if (erreur) {
                    erreur.style.display = "none";
                    erreur.textContent = "";
                }
            });
        }
    });

    // === Envoi du formulaire avec Fetch (AJAX) ===
    form.addEventListener("submit", async (e) => {
        e.preventDefault(); // Empêche rechargement de la page


        // Vérifie si tous les champs sont correctement remplis
        if (!verifierChampsObligatoires()) return;

        // Crée un objet FormData pour contenir toutes les données du formulaire
        const formData = new FormData(form);

        // On supprime les fichiers ajoutés automatiquement par le champ HTML
        formData.delete("piece_jointe[]"); // On nettoie les fichiers envoyés automatiquement

        // Et  ajoute manuellement les fichiers validés côté JS
        fichiersSelectionnes.forEach(file => {
            formData.append("piece_jointe[]", file); // On ajoute ceux validés
        });

        try {
            // Envoie du formulaire au backend via Fetch
            // POST -> vers le contrôleur PHP qui traite les demandes
            const res = await fetch(BASE_URL + "/Controller/B2/DemandeControllerB2.php", {
                method: "POST",
                body: formData // Envoie l’objet FormData (fichiers + texte)
            });

            // Attend la réponse JSON du backend
            const data = await res.json();

            if (data.nouveau_token) {
                document.querySelector('input[name="csrf_token"]').value = data.nouveau_token;
            }
            // Affiche le message dans un popup (succès ou erreur)
            afficherPopup(data.message, data.success);

            // Si la demande a été traitée avec succès, on réinitialise tout le formulaire
            if (    data.success) resetFormulaire();

        } catch (error) {
            console.error("Erreur AJAX :", error);
            document.getElementById("message").innerHTML = `<p style="color: red;">Erreur lors de l'envoi.</p>`;
        }

    });
});

// === Ferme la popup en cliquant à l’extérieur ===
function closePopup() {
    const popup = document.getElementById("popup");
    const overlay = document.getElementById("popup-overlay-B2");
    popup.style.display = "none";
    overlay.style.display = "none";
    document.querySelector(".container-B2").classList.remove("blurred-B2");
    document.body.classList.remove("no-scroll");
}