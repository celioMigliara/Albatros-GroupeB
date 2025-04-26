document.addEventListener('DOMContentLoaded', () => {
    const checkboxes = document.querySelectorAll('.site-filter');
    const selectAll = document.querySelector('#select_all_sites');
    const tableRows = document.querySelectorAll('#table-body tr');


    const filterBtn = document.getElementById("filterBtn");
    const dropdown = document.querySelector('#dropdownFilter');

    console.log('filterBtn:', filterBtn);
    console.log('dropdown:', dropdown);

    filterBtn.addEventListener('click', () => {
        dropdown.classList.toggle('show');
    });


    window.addEventListener('click', function(event) {
        if (!dropdown.contains(event.target) && !filterBtn.contains(event.target)) {
            dropdown.classList.remove('show');
        }
    });

    const updateTableVisibility = () => {
        const checkedSites = Array.from(checkboxes)
            .filter(cb => cb !== selectAll && cb.checked)
            .map(cb => cb.value);

       
        tableRows.forEach(row => {
            const rowSite = row.getAttribute('data-site');
            row.style.display = checkedSites.includes(rowSite) ? '' : 'none';
        });

        // Si tous les sites sont cochés, on coche aussi "Tous", sinon on décoche
        const allChecked = Array.from(checkboxes)
            .filter(cb => cb !== selectAll)
            .every(cb => cb.checked);
        selectAll.checked = allChecked;
    };

    // Gestion du "Tous"
    selectAll.addEventListener('change', () => {
        const isChecked = selectAll.checked;
        checkboxes.forEach(cb => {
            cb.checked = isChecked;
        });
        updateTableVisibility();
    });

    // Gestion des autres cases à cocher
    checkboxes.forEach(cb => {
        if (cb !== selectAll) {
            cb.addEventListener('change', updateTableVisibility);
        }
    });

    updateTableVisibility(); // Mise à jour initiale
    // Gestion de la validation du formulaire
    const form = document.querySelector("form[name='frmAdd']");
    if (form) { // Vérifie que le formulaire existe avant d'appliquer la validation
        const inputs = form.querySelectorAll("input[required], select[required]");
        const errorContainer = document.createElement("div");
        errorContainer.classList.add("error-global");
        errorContainer.style.color = "red";
        errorContainer.style.fontSize = "14px";
        errorContainer.style.display = "none";
        form.prepend(errorContainer); // Ajoute le conteneur d'erreur en haut du formulaire

        inputs.forEach(input => {
            input.addEventListener("blur", function () {
                if (this.value.trim() === "") {
                    this.classList.add("error-border");
                    afficherMessageErreur(this, "Ce champ est obligatoire !");
                } else {
                    this.classList.remove("error-border");
                    enleverMessageErreur(this);
                }
            });
        });

        form.addEventListener("submit", function(event) {
            let isValid = true;
            errorContainer.style.display = "none"; // Cache le message global d'erreur
            
            inputs.forEach(input => {
                if (input.value.trim() == "") {
                    input.classList.add("error-border");
                    afficherMessageErreur(input, "Ce champ est obligatoire !");
                    isValid = false;
                }
            });

            if (!isValid) {
                errorContainer.textContent = "Veuillez remplir tous les champs obligatoires.";
                errorContainer.style.display = "block"; // Affiche le message global d'erreur
                event.preventDefault();
            }
        });

        function afficherMessageErreur(input, message) {
            let errorSpan = input.nextElementSibling;
            if (!errorSpan || !errorSpan.classList.contains("error-message")) {
                errorSpan = document.createElement("span");
                errorSpan.classList.add("error-message");
                errorSpan.style.color = "red";
                errorSpan.style.fontSize = "12px";
                errorSpan.style.display = "block";
                input.parentNode.appendChild(errorSpan);
            }
            errorSpan.textContent = message;
        }

        function enleverMessageErreur(input) {
            let errorSpan = input.nextElementSibling;
            if (errorSpan && errorSpan.classList.contains("error-message")) {
                errorSpan.remove();
            }
        }
    }
});
    
document.addEventListener('DOMContentLoaded', () => {
    const siteSelect = document.getElementById('choixSite');
    const batimentSelect = document.getElementById('choixBatiment');
    const lieuSelect = document.getElementById('choixLieu');

    document.getElementById("anniversaire").setAttribute("min", new Date().toISOString().split("T")[0]);

    // Charger les sites au démarrage
    fetch(BASE_URL + '/Controller/B2/ajax.php?get_sites=1')
        .then(response => response.json())
        .then(data => {
            
             // Supprimer toutes les options sauf la première
            siteSelect.innerHTML = '<option value="">Sélectionnez un site</option>';
            data.forEach(site => {
                const option = document.createElement('option');
                option.value = site.id_site;
                option.textContent = site.nom_site;
                siteSelect.appendChild(option);
            });

            // Restaurer la sélection si une valeur est déjà présente
            const selectedSite = siteSelect.getAttribute('data-selected');
            if (selectedSite) {
                siteSelect.value = selectedSite;
                siteSelect.dispatchEvent(new Event('change')); // Pour recharger les bâtiments
            }
        });

    // Charger les bâtiments quand un site est sélectionné
    siteSelect.addEventListener('change', () => {
        const siteId = siteSelect.value;

        // Vider les anciens choix
        batimentSelect.innerHTML = '<option value="">Sélectionnez un bâtiment</option>';
        lieuSelect.innerHTML = '<option value="">Sélectionnez un lieu</option>';

        if (siteId) {
            fetch(BASE_URL + `/Controller/B2/ajax.php?site_id=${siteId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(batiment => {
                        const option = document.createElement('option');
                        option.value = batiment.id_batiment;
                        option.textContent = batiment.nom_batiment;
                        batimentSelect.appendChild(option);
                    });
                    
                    const selectedBatiment = batimentSelect.getAttribute('data-selected');
                    if (selectedBatiment) {
                        batimentSelect.value = selectedBatiment;
                        batimentSelect.dispatchEvent(new Event('change')); // Pour charger les lieux
                    }
                });
        }
    });

    // Charger les lieux quand un bâtiment est sélectionné
    batimentSelect.addEventListener('change', () => {
        const batimentId = batimentSelect.value;

        lieuSelect.innerHTML = '<option value="">Sélectionnez un lieu</option>';

        if (batimentId) {
            fetch(BASE_URL +`/Controller/B2/ajax.php?batiment_id=${batimentId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(lieu => {
                        const option = document.createElement('option');
                        option.value = lieu.id_lieu;
                        option.textContent = lieu.nom_lieu;
                        lieuSelect.appendChild(option);
                    });

                    const selectedLieu = lieuSelect.getAttribute('data-selected');
                    if (selectedLieu) {
                        lieuSelect.value = selectedLieu;
                    }
                });
        }
    });
});
// Fonction pour afficher le pop-up
function showPopup(message, isError = false) {
    const popup = isError ? document.getElementById("error-popup") : document.getElementById("popup");
    const popupMessage = isError ? document.getElementById("error-message") : document.getElementById("popup-message");
    const okBtn = isError ? document.getElementById("error-ok-btn") : document.getElementById("popup-ok-btn");
    popupMessage.textContent = message;  // Ajoute le message au pop-up
    popup.style.display = 'block'; // Affiche le pop-up
    
    // Gérer le bouton OK
    okBtn.addEventListener('click', function () {
        if (isError) {
            window.location.href = window.location.href; // Reste sur la même page en cas d'erreur
        } else {
            window.location.href = "recurrence";
        }
        popup.style.display = 'none'; // Ferme le pop-up
    });

    // Gérer le bouton de fermeture
    const closeBtn = document.querySelector(isError ? '#error-popup-close' : '#popup-close');
    closeBtn.addEventListener('click', function () {
        popup.style.display = 'none'; // Ferme le pop-up si on clique sur la croix
    });
}
document.querySelectorAll('.supp-btn_recurr').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const popupDel = document.getElementById("popupDel");
        const message = document.getElementById("popup-message-sup");
        const okBtn = document.getElementById("popup-ok-btn-sup");
        const NoBtn = document.getElementById("popup-no-btn-sup");
        const closeBtn = document.getElementById("popup-close-sup");

        message.textContent = "Confirmer la suppression de cette récurrence ?";
        popupDel.style.display = 'block';

        const onOkClick = () => {
            fetch(BASE_URL +'/Controller/B2/ajax-suppression.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' 

                },
                body: `id=${encodeURIComponent(id)}&csrf_token=${encodeURIComponent(csrfToken)}`
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    window.location.href = "recurrence";
                } else {
                    popupDel.style.display = 'none';
                }
            })
            .catch(err => {
                alert("Erreur réseau !");
                console.error(err);
            });

            okBtn.removeEventListener('click', onOkClick);
        };

        okBtn.addEventListener('click', onOkClick);
       

        closeBtn.addEventListener('click', () => {
            popupDel.style.display = 'none';
            okBtn.removeEventListener('click', onOkClick);
        });
        
        NoBtn.addEventListener('click', () => {
            popupDel.style.display = 'none';
            okBtn.removeEventListener('click', onOkClick);
        });
    });
});