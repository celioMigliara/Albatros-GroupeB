// Fichier JS qui gère les fonctionnalités inhérentes de la 
// page d'inscription

document.addEventListener("DOMContentLoaded", function () 
{
    // Ajouter un callback sur le search input
    const searchInput = document.getElementById("searchInputBatiments");
    if (searchInput)
    {
        searchInput.addEventListener("input", 
            ApplySearchFilters);
    }
});

function ApplySearchFilters()
{
    // On récupère le search input et on va l'utiliser
    const searchQuery = document.getElementById("searchInputBatiments")
                        .value
                        .toLowerCase();
    
    // Récupère toutes les cases à cocher et leurs labels
    const checkboxes = document.querySelectorAll('.checkbox-wrapper-checkbox input[type="checkbox"]');
    const labels = document.querySelectorAll('.checkbox-wrapper-checkbox label');

    // Parcours de chaque case à cocher pour afficher ou masquer en fonction de la recherche
    checkboxes.forEach((checkbox, index) => {
        const label = labels[index];
        const batimentName = label.querySelector('.label-text').textContent.toLowerCase();

        if (batimentName.includes(searchQuery)) {
            checkbox.style.display = ''; // Affiche la case si elle correspond
            label.style.display = ''; // Affiche le label correspondant
        } else {
            checkbox.style.display = 'none'; // Masque la case si elle ne correspond pas
            label.style.display = 'none'; // Masque le label si il ne correspond pas
        }
    });
}