document.addEventListener("DOMContentLoaded", function () {
    const exportButton = document.querySelector(".btnExcel");

    if (exportButton) {
        exportButton.addEventListener("click", function (event) {
            event.preventDefault();

            // Récupération des valeurs des filtres
            const formData = new FormData();
            formData.append("date_debut_jour", document.querySelector("select[name='date_debut_jour']").value || "");
            formData.append("date_debut_mois", document.querySelector("select[name='date_debut_mois']").value || "");
            formData.append("date_debut_annee", document.querySelector("select[name='date_debut_annee']").value || "");
            formData.append("date_fin_jour", document.querySelector("select[name='date_fin_jour']").value || "");
            formData.append("date_fin_mois", document.querySelector("select[name='date_fin_mois']").value || "");
            formData.append("date_fin_annee", document.querySelector("select[name='date_fin_annee']").value || "");

            // Envoi de la requête POST
            fetch(BASE_URL + "/exportDemandes", {
                method: "POST",
                body: formData
            }).then(response => response.blob())
              .then(blob => {
                  const url = window.URL.createObjectURL(blob);
                  const a = document.createElement("a");
                  a.style.display = "none";
                  a.href = url;
                  a.download = "export_demandes.csv";
                  document.body.appendChild(a);
                  a.click();
                  window.URL.revokeObjectURL(url);
              })
              .catch(error => console.error("Erreur lors de l'exportation :", error));
        });
    }
});
