//Ce fichier "ModifierProfil.js" est dépendant de "General.js"
// Il faut donc veiller à toujours inclure "General.js" avant
// pour continuer à utiliser ses fonctionnalités.
// Les callbacks quand le DOM est chargé
document.addEventListener("DOMContentLoaded", cacheDefaultFields);
document.addEventListener("DOMContentLoaded", registerInputEventsToValidate);
document.addEventListener("DOMContentLoaded", handleResetInput);

document.getElementById("formulaire-modification-profil").
addEventListener("submit", handleProfileForm);

let defaultNomValue = null;
let defaultPrenomValue = null;
let defaultMailValue = null;

function handleResetInput()
{
    // Ajoute les boutons de reset
    document.querySelectorAll(".reset-input-btn").forEach(btn => {
        btn.addEventListener("click", function () {
            const targetId = this.dataset.target;
            const input = document.getElementById(targetId);

            switch (targetId) {
                case "nom_utilisateur":
                    input.value = defaultNomValue;
                    break;
                case "prenom_utilisateur":
                    input.value = defaultPrenomValue;
                    break;
                case "mail_utilisateur":
                case "confirm_mail_utilisateur":
                    input.value = defaultMailValue;
                    break;
                case "mdp_utilisateur":
                case "confirm_mdp_utilisateur":
                    input.value = "";
                    break;
                default:
                    input.value = "";
            }

            // Pour déclencher les validations
            input.dispatchEvent(new Event("input")); 
        });
    });
}

function cacheDefaultFields() {
    // On affecte les valeurs par défaut
    defaultNomValue = document.getElementById("nom_utilisateur").value;
    defaultPrenomValue = document.getElementById("prenom_utilisateur").value;

    const mailUser = document.getElementById("mail_utilisateur");
    defaultMailValue = mailUser.value;

    const passwordUser = document.getElementById("mdp_utilisateur");

    // On ajoute les listeners
    mailUser.addEventListener("input", compareMailConfirm);
    passwordUser.addEventListener("input", comparerPasswordConfirm);
}

function compareMailConfirm() {
    let confirmGroup = document.getElementById("confirm-mail-group");
    if (this.value === defaultMailValue) {
        confirmGroup.style.display = "none"; // Cacher
    } else {
        confirmGroup.style.display = "block"; // Afficher
    }
}

function comparerPasswordConfirm() {
    let confirmGroup = document.getElementById("confirm-password-group");
    if (this.value.trim() === "") {
        confirmGroup.style.display = "none";
    } else {
        confirmGroup.style.display = "block";
    }
}

function handleProfileForm(event) {
    event.preventDefault();

    // Récupération des données du formulaire
    let newNom = document.getElementById("nom_utilisateur").value;
    let newPrenom = document.getElementById("prenom_utilisateur").value;
    let newMail = document.getElementById("mail_utilisateur").value;
    let newMailConfirm = document.getElementById("confirm_mail_utilisateur").value;
    let newPassword = document.getElementById("mdp_utilisateur").value;
    let newPasswordConfirm = document.getElementById("confirm_mdp_utilisateur").value;
    const csrfToken = document.querySelector('input[name="csrf_token"]').value;

    // Création d'une instance de URLSearchParams pour gérer les paramètres de requête
    const params = new URLSearchParams();

    // On vérifie si chaque champ a été modifié et ajoute les paramètres si nécessaire

    // Vérification du nom
    if (newNom !== defaultNomValue) {
        if (!isNomValid(newNom)) {
            CreateSimplePopup("Le nom n'est pas valide.", false);
            return;
        }
        params.append("nom_utilisateur", newNom);
    }

    // Vérification du prénom
    if (newPrenom !== defaultPrenomValue) {
        if (!isPrenomValid(newPrenom)) {
            CreateSimplePopup("Le prénom n'est pas valide.", false);
            return;
        }
        params.append("prenom_utilisateur", newPrenom);
    }

    // Vérification de l'email
    if (newMail !== defaultMailValue) {
        if (!isValidEmail(newMail)) {
            CreateSimplePopup("Le format de l'email est incorrect. Veuillez suivre cette norme : example@mail.com", false);
            return;
        }
        if (newMail !== newMailConfirm) {
            CreateSimplePopup("Les emails ne correspondent pas.", false);
            return;
        }
        params.append("mail_utilisateur", newMail);
    }

    // Vérification du mot de passe
    if (newPassword) {
        if (!isStrongPassword(newPassword)) {
            CreateSimplePopup("Le mot de passe n'est pas assez fort. Veuillez inclure au moins 1 minuscule, 1 majuscule, 1 chiffre avec au moins 8 caractères.", false);
            return;
        }
        if (newPassword !== newPasswordConfirm) {
            CreateSimplePopup("Les mots de passe ne correspondent pas.", false);
            return;
        }
        params.append("mdp_utilisateur", newPassword);
    }

    // Ajout du token CSRF aux paramètres de requête
    params.append("csrf_token", csrfToken);

    // Envoi de la requête
    SendRequestPOST(params.toString(), BASE_URL + "/profil/modifier", true);
}

function registerInputEventsToValidate() {
    const nom = document.getElementById("nom_utilisateur");
    const prenom = document.getElementById("prenom_utilisateur");
    const email = document.getElementById("mail_utilisateur");
    const confirmEmail = document.getElementById("confirm_mail_utilisateur");
    const password = document.getElementById("mdp_utilisateur");
    const confirmPassword = document.getElementById("confirm_mdp_utilisateur");

    // Listener sur tous les champs
    [
        nom, prenom, email, confirmEmail, password, confirmPassword
    ]
        .forEach(input => {
            input.addEventListener("input", validateForm);
        });
}

function validateForm() 
{
    // On récupère les données du formulaire
    const nomValue = document.getElementById("nom_utilisateur").value;
    const prenomValue = document.getElementById("prenom_utilisateur").value;
    const newEmailValue = document.getElementById("mail_utilisateur").value;
    const confirmEmail = document.getElementById("confirm_mail_utilisateur").value;
    const passwordValue = document.getElementById("mdp_utilisateur").value;
    const confirmPasswordValue = document.getElementById("confirm_mdp_utilisateur").value;
    const submitBtn = document.getElementById("modifier-profil-btn");

    // On évalue nos conditions
    const emailChanged = newEmailValue !== defaultMailValue;
    const emailMatch = newEmailValue === confirmEmail;
    const passwordNotEmpty = passwordValue.length > 0;
    const passwordMatch = passwordValue === confirmPasswordValue;
    const nomChanged = nomValue !== defaultNomValue;
    const prenomChanged = prenomValue !== defaultPrenomValue;

    // On décide si le bouton devrait être actif ou non
    // Les conditions sont tells que :
    // Si l'email a changé et est valide et la confirmation-email match
    // ou si le password n'est plus vide, est valide et la confirmation-password match
    // ou si le nom/prénom ont changés, alors on peut envoyer la requete
    const emailValid = !emailChanged || (isValidEmail(newEmailValue) && emailMatch);
    const passwordValid = !passwordNotEmpty || (isStrongPassword(passwordValue) && passwordMatch);
    const nomValid = !nomChanged || isNomValid(nomValue);
    const prenomValid = !prenomChanged || isPrenomValid(prenomValue);

    const shouldEnable = emailValid && passwordValid && nomValid && prenomValid &&
    (emailChanged || passwordNotEmpty || nomChanged || prenomChanged);

    // On active/désactive le bouton suivant nos conditions
    submitBtn.disabled = !shouldEnable;
}
