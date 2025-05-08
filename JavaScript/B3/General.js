// On initialise l'objet XMLHttpRequest
var xhr = null;
InitXHR();

function InitXHR() {
  xhr = new XMLHttpRequest();
  xhr.onreadystatechange = HandleReadyStateChange;
  xhr.onabort = function () {
    console.warn("Requête annulée !");
  };
  xhr.ontimeout = function () {
    console.warn("Requête expirée !");
  };
  xhr.onerror = function () {
    console.error("Erreur réseau ou requête bloquée.");
  };
}

// Fonction pour revenir à la page précédente
var BackButton = document.getElementById("back-button");
if (BackButton) {
  BackButton.addEventListener("click", function () {
    window.history.back(); // Permet de revenir à la page précédente
  });
}

var InscriptionForm = document.getElementById("formulaire-inscription");
if (InscriptionForm) {
  InscriptionForm.addEventListener("submit", function (event) {
    // Empêche la soumission normale du formulaire
    event.preventDefault();

    // Récupération des données du formulaire
    let newNom = document.getElementById("nom_utilisateur").value;
    let newPrenom = document.getElementById("prenom_utilisateur").value;
    let newMail = document.getElementById("mail_utilisateur").value;
    let newPassword = document.getElementById("mdp_utilisateur").value;
    let confirmMail = document.getElementById("confirmer_mail").value;
    let confirmPassword = document.getElementById("confirmer_mots_de_passe").value;

    let newRole = document.querySelector(
      'input[name="role_utilisateur"]:checked'
    ).value;
       // Récupération du token CSRF
       const csrfToken = document.querySelector('input[name="csrf_token"]').value;

    // Récupération des bâtiments
    // Nouvelle gestion des bâtiments
    let batiments = [];
    if (newRole === "3") {
      // Uniquement si employé
      batiments = Array.from(
        document.querySelectorAll(
          '#batiment-modal input[name="batiments_utilisateur[]"]:checked'
        )
      ).map((checkbox) => checkbox.value);
    }

    // On ajoute les paramètres de requête
    var RequestParameter =
      "nom_utilisateur=" +
      encodeURIComponent(newNom) +
      "&prenom_utilisateur=" +
      encodeURIComponent(newPrenom) +
      "&mail_utilisateur=" +
      encodeURIComponent(newMail) +
      "&mdp_utilisateur=" +
      encodeURIComponent(newPassword) +
      "&role_utilisateur=" +
      encodeURIComponent(newRole) +
      "&csrf_token=" +
      encodeURIComponent(csrfToken) +
      "&confirmer_mail=" +
      encodeURIComponent(confirmMail) +
      "&confirmer_mots_de_passe=" +
      encodeURIComponent(confirmPassword);
      
    // Ajout des bâtiments comme paramètres multiples
    if (newRole === "3") {
      batiments.forEach((id) => {
        RequestParameter +=
          "&batiments_utilisateur[]=" + encodeURIComponent(id);
      });
    }
    // Et on l'envoie, si aucune requete n'est déjà en cours
    SendRequestPOST(RequestParameter, BASE_URL + "/inscription", true);
  });
}

// Gestion de l'affichage des bâtiments selon le rôle
document.querySelectorAll('input[name="role_utilisateur"]').forEach((radio) => {
  radio.addEventListener("change", (e) => {
    const batimentSection = document.getElementById("batiment-section");
    if (e.target.value === "2") {
      // Technicien
      batimentSection.style.display = "none";
    } else {
      // Employé
      batimentSection.style.display = "block";
    }
  });
});

// Initialisation au chargement
window.addEventListener("DOMContentLoaded", () => {
  const checkedRoleInput = document.querySelector('input[name="role_utilisateur"]:checked');
  const batimentSection = document.getElementById("batiment-section");

  // On ne fait rien si les éléments n'existent pas
  if (!checkedRoleInput || !batimentSection) {
    return;
  }

  const role = checkedRoleInput.value;
  batimentSection.style.display = role === "2" ? "none" : "block";
});

var ConnexionForm = document.getElementById("formulaire-connexion");
if (ConnexionForm) {
  ConnexionForm.addEventListener("submit", function (event) {
    // Empêche la soumission normale du formulaire
    event.preventDefault();

    // Récupération des données du formulaire
    var newMail = document.getElementById("mail_utilisateur").value;
    var newPassword = document.getElementById("mdp_utilisateur").value;
    const csrfToken = document.querySelector('input[name="csrf_token"]').value;
    // On ajoute les paramètres de requete
    var RequestParameter =
      "mail_utilisateur=" +
      encodeURIComponent(newMail) +
      "&mdp_utilisateur=" +
      encodeURIComponent(newPassword);
      // Ajout du token CSRF aux paramètres de requête
      RequestParameter += "&csrf_token=" + encodeURIComponent(csrfToken);
    // Et on l'envoie, si aucune requete n'est déjà en cours
    SendRequestPOST(RequestParameter, BASE_URL + "/connexion", true);
  });
}

// Ajout d'un listener pour reset le mot de passe avec le mail
var FormResetMdp = document.getElementById("formulaire-reset-password");
if (FormResetMdp) {
  FormResetMdp.addEventListener("submit", function (event) {
    event.preventDefault();

    // On récupère l'input du mail
    var UserEmail = document.getElementById("mail_utilisateur");
    const csrfToken = document.querySelector('input[name="csrf_token"]').value;

    if (UserEmail) {
      // On setup la requete
      var RequestParameter =
        "mail_utilisateur=" + encodeURIComponent(UserEmail.value.trim());

              // Ajout du token CSRF aux paramètres de requête
      RequestParameter += "&csrf_token=" + encodeURIComponent(csrfToken);

      // Et on l'envoie, si aucune requête n'est déjà en cours
      SendRequestPOST(RequestParameter, BASE_URL + "/motdepasse/reset", true);
    }
  });
}

// Ajout d'un listener pour changer le mot de passe
var ChangerMdpForm = document.getElementById("formulaire-change-password");
if (ChangerMdpForm) {
  ChangerMdpForm.addEventListener("submit", function (event) {
    event.preventDefault();
    // Récupération des données du formulaire
    var newPassword = document.getElementById("new_password").value;

    // On récupère le token CSRF
    const csrfToken = document.querySelector('input[name="csrf_token"]').value;

    // Récupération du token dans l'URL
    var token = GetTokenFromUrl();

    // Envoi des données avec le token
    var RequestParameter =
      "new_password=" +
      encodeURIComponent(newPassword) +
      "&token=" +
      encodeURIComponent(token);

    // Ajout du token CSRF aux paramètres de requête
    RequestParameter += "&csrf_token=" + encodeURIComponent(csrfToken);

    // Et on l'envoie, si aucune requete n'est déjà en cours
    SendRequestPOST(RequestParameter, BASE_URL + "/motdepasse/changer", true);
  });
}

// Ajout d'un listener pour changer le mot de passe
var ModifierProfilForm = document.getElementById(
  "formulaire-modification-profil"
);
if (ModifierProfilForm) {
  ModifierProfilForm.addEventListener("submit", function (event) {
    event.preventDefault();

    // Récupération des données du formulaire
    let newNom = document.getElementById("nom_utilisateur").value;
    let newPrenom = document.getElementById("prenom_utilisateur").value;
    let newMail = document.getElementById("mail_utilisateur").value;
    let newPassword = document.getElementById("mdp_utilisateur").value;
    const csrfToken = document.querySelector('input[name="csrf_token"]').value;

    // On ajoute les paramètres de requete
    let RequestParameter = "";

    // Ajouter les paramètres uniquement si la valeur est définie (non vide)
    if (newNom) {
      RequestParameter += "nom_utilisateur=" + encodeURIComponent(newNom);
    }

    if (newPrenom) {
      RequestParameter +=
        "&prenom_utilisateur=" + encodeURIComponent(newPrenom);
    }

    if (newMail) {
      RequestParameter += "&mail_utilisateur=" + encodeURIComponent(newMail);
    }

    if (newPassword) {
      RequestParameter += "&mdp_utilisateur=" + encodeURIComponent(newPassword);
    }
    // Ajout du token CSRF aux paramètres de requête
    RequestParameter += "&csrf_token=" + encodeURIComponent(csrfToken);
    

    if (RequestParameter.charAt(0) === "&") {
      RequestParameter = RequestParameter.slice(1);
    }
    // Et on l'envoie, si aucune requete n'est déjà en cours
    SendRequestPOST(RequestParameter, BASE_URL + "/profil/modifier", true);
  });
}

function SendRequestPOST(SendParameter, OpenPath, StopIfSending = true) {
  // On peut autoriser seulement 1 requete à la fois grace à StopIfSending
  // Si le readyState est différent de 0, cela voudrait dire qu'on est en pleine requete
  if (StopIfSending && xhr.readyState != 0) {
    // Si la requete n'est pas complétée (à 4), on envoie pas de nouvelles requetes
    if (xhr.readyState != 4) {
      return;
    }

    // Sinon on réinitiliase l'objet XHR pour la suite de la requête
    InitXHR();
  }

  // Envoi de nos données avec une requete POST
  xhr.open("POST", OpenPath, true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.send(SendParameter);
}

function safeJsonParse(str) {
  try {
    return JSON.parse(str);
  } catch (e) {
    return null;
  }
}

function HandleReadyStateChange() {
  // Toutes les données sont réceptionnées
  if (xhr.readyState == 4) {
    var Json = safeJsonParse(xhr.responseText);

    // Normalement on ne renvoie que du JSON mais peut être que le PHP
    // a rajouté une erreur sous forme de texte.
    if (!Json) {
      console.log("Json mal parsé. Voici le contenu brut :");
      console.log(xhr.responseText);
      return;
    }

    if (Json.redirect) {
      console.log(xhr.responseText);

      // Se rediriger vers la page
      window.location.href = Json.redirect;
    } else if (Json.message) {
      CreateSimplePopup(Json.message, Json.status === "success");
    } else {
      console.log(xhr.responseText);
    }
    
    if (Json.debug) {
      console.log(Json.debug);
    }
  }
}

function CreateSimplePopup(message, IsSuccess) {
  // Vérifie si l'appareil est une tablette ou un mobile
  const isMobile = window.matchMedia("(max-width: 430px)").matches;
  const isTablet = window.matchMedia("(min-width: 767px) and (max-width: 1025px)").matches;

  // Créer dynamiquement un élément div pour la popup
  const popup = document.createElement("div");
  popup.style.display = "flex";
  popup.style.flexDirection = "column"; // Disposition en colonne
  popup.style.position = "absolute"; // Positionnement absolu
  popup.style.left = "50%";
  popup.style.transform = "translateX(-50%) translateY(40px)"; // Position de départ (décalée vers le bas)
  popup.style.bottom = "30px"; // Position en bas de la page

  if (!IsSuccess) {
    popup.style.background = "#d32f2f"; // Fond rouge pour la popup
  } else {
    popup.style.background = "#4CAF50"; // Fond vert pour la popup
  }

  popup.style.color = "#fff"; // Couleur du texte en blanc
  popup.style.padding = "20px 30px"; // Augmenter le padding autour du contenu
  popup.style.borderRadius = "8px"; // Coins arrondis
  popup.style.boxShadow = "0 4px 6px rgba(0, 0, 0, 0.1)"; // Ombre pour un effet flottant
  popup.style.zIndex = "10000"; // S'assurer que la popup est au-dessus des autres éléments
  popup.style.width = "65%"; // Largeur automatique pour la popup
  popup.style.height = "auto"; // Hauteur automatique en fonction du contenu
  popup.style.alignItems = "center"; // Centrer le contenu
  popup.style.fontSize = "25px"; // Augmenter la taille du texte
  popup.style.lineHeight = "1.5"; // Espacement des lignes pour améliorer la lisibilité
  popup.style.opacity = "0";
  popup.style.transition = "opacity 0.5s ease, transform 0.5s ease";
  popup.style.marginBottom = "10px auto"; // Centrer la popup horizontalement

  // Si c'est mobile, applique le style "popup-mobile" en JS
  if (isMobile) {
    // Tu peux ajuster ces styles selon tes besoins
    popup.style.display = "flex";
    popup.style.flexDirection = "column";
    popup.style.alignItems = "center";
    popup.style.justifyContent = "center";
    popup.style.width = "90%"; // Plus large sur mobile
    popup.style.maxWidth = "95vw"; // Limite la largeur à l'écran
    popup.style.left = "50%";
    popup.style.bottom = "20px";
    popup.style.transform = "translateX(-50%) translateY(40px)";
    popup.style.fontSize = "18px"; // Texte un peu plus petit sur mobile
    popup.style.padding = "15px 10px"; // Padding adapté au mobile
    popup.style.boxSizing = "border-box";
  }

  if (isTablet) {
    // Tu peux ajuster ces styles selon tes besoins
    popup.style.display = "flex";
    popup.style.flexDirection = "column";
    popup.style.alignItems = "center";
    popup.style.justifyContent = "center";
    popup.style.width = "90%"; // Plus large sur mobile
    popup.style.maxWidth = "95vw"; // Limite la largeur à l'écran
    popup.style.left = "50%";
    popup.style.bottom = "20px";
    popup.style.transform = "translateX(-50%) translateY(40px)";
    popup.style.fontSize = "18px"; // Texte un peu plus petit sur mobile
    popup.style.padding = "15px 10px"; // Padding adapté au mobile
    popup.style.boxSizing = "border-box";
  }

  
  // Ajouter l'icône d'attention
  const icon = document.createElementNS("http://www.w3.org/2000/svg", "svg");
  icon.setAttribute("xmlns", "http://www.w3.org/2000/svg");
  icon.setAttribute("width", "24");
  icon.setAttribute("height", "24");
  icon.setAttribute("viewBox", "0 0 24 24");
  icon.innerHTML =
    '<path fill="#fff" d="m13 13h-2v-6h2zm0 4h-2v-2h2zm-1-15c-1.3132 0-2.61358.25866-3.82683.7612-1.21326.50255-2.31565 1.23915-3.24424 2.16773-1.87536 1.87537-2.92893 4.41891-2.92893 7.07107 0 2.6522 1.05357 5.1957 2.92893 7.0711.92859.9286 2.03098 1.6651 3.24424 2.1677 1.21325.5025 2.51363.7612 3.82683.7612 2.6522 0 5.1957-1.0536 7.0711-2.9289 1.8753-1.8754 2.9289-4.4189 2.9289-7.0711 0-1.3132-.2587-2.61358-.7612-3.82683-.5026-1.21326-1.2391-2.31565-2.1677-3.24424-.9286-.92858-2.031-1.66518-3.2443-2.16773-1.2132-.50254-2.5136-.7612-3.8268-.7612z"></path>';

  popup.appendChild(icon);
  
  // Ajouter le message à la popup
  const messageElement = document.createElement("strong");
  messageElement.classList.add("message");
  messageElement.innerText = message;
  messageElement.style.textAlign = "center"; // <-- Ajoute cette ligne
  messageElement.style.display = "block"; // <-- Ajoute cette ligne pour que text-align fonctionne bien
  popup.appendChild(messageElement);

  

  // Créer un bouton de fermeture pour la popup
  const closeButton = document.createElement("button");
  closeButton.innerText = "Fermer";
  closeButton.style.marginTop = "15px";
  closeButton.style.padding = "8px 15px"; // Augmenter le padding du bouton
  closeButton.style.border = "none";
  if (!IsSuccess) {
    closeButton.style.background = "#d32f2f"; // Couleur du bouton (rouge)
  }
  else {
    closeButton.style.background = "#4CAF50"; // Couleur du bouton (vert)
  }
  closeButton.style.color = "#fff";
  closeButton.style.cursor = "pointer";
  closeButton.style.borderRadius = "5px";
  closeButton.style.fontSize = "16px"; // Taille du texte du bouton

  // Ajouter le bouton de fermeture à la popup
  popup.appendChild(closeButton);

  // Lorsque le bouton de fermeture est cliqué, on enlève la popup
  closeButton.onclick = function () {
    popup.remove();
  };

  // Ajouter la popup au container (ici 'left' comme spécifié dans ton code HTML)
  // Sélectionner le bon container
  const container = document.querySelector(isMobile ? ".right" : ".left");
  container.appendChild(popup);

  // Animation d’apparition
  setTimeout(() => {
    popup.style.opacity = "1";
    popup.style.transform = "translateX(-50%) translateY(0)";
  }, 100);

  // Optionnellement, on peut ajouter une fermeture automatique après un certain temps
  setTimeout(() => {
    popup.remove();
  }, 5000); // La popup se ferme automatiquement après 5 secondes
}

// Fonction pour récupérer un paramètre de l'URL
function GetTokenFromUrl() {
  const urlParams = new URLSearchParams(window.location.search);
  return urlParams.get("token"); // Récupère la valeur du token
}

function togglePassword() {
  const passwordInput = document.getElementById("password");
  const eyeIcon = document.querySelector(".eye");

  // activer et desactiver la password visibility
  if (passwordInput.type === "password") {
    passwordInput.type = "text";
    eyeIcon.classList.add("open"); // Ajouter la classe pour ouvrir l'œil
  } else {
    passwordInput.type = "password";
    eyeIcon.classList.remove("open"); // Retirer la classe pour fermer l'œil
  }
}

// Gestion des popups
let currentPopup = null;

function openPopup(popupId, callback = null) {
  currentPopup = document.getElementById(popupId);
  if (currentPopup) {
    currentPopup.style.display = "flex";
    if (callback && typeof callback === "function") callback();
  }
}

function closePopup() {
  if (currentPopup) {
    currentPopup.style.display = "none";
    currentPopup = null;
  }
}

// Specifique au batiments uniquement c'est le Compteur de bâtiments sélectionner
let selectedBatiments = 0;

// Remplacer la fonction updateCounter existante par :
function updateCounter() {
  const checkboxes = document.querySelectorAll('#batiment-modal input[type="checkbox"]:checked');
  selectedBatiments = checkboxes.length;
  document.getElementById("selected-count").textContent = selectedBatiments;
  
  // Ajouter une validation visuelle
  const batimentSection = document.getElementById("batiment-section");
  batimentSection.classList.toggle('invalid', selectedBatiments === 0 && document.querySelector('input[name="role_utilisateur"]:checked').value === "3");
}

// Fermeture au clic externe
window.onclick = function (event) {
  if (event.target.classList.contains("popup")) {
    closePopup();
  }
};

function validatePasswords(event) {
  const password = document.getElementById("mdp_utilisateur").value;
  const confirmPassword = document.getElementById(
    "confirmer_mots_de_passe"
  ).value;
  if (password !== confirmPassword) {
    event.preventDefault();
    alert("Les mots de passe ne correspondent pas.");
  }
}
