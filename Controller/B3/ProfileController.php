<?php

require_once 'Model/B3/UserCredentials.php';
require_once 'Model/B3/UserProfile.php';
require_once 'Model/B3/Security.php';
require_once 'Model/UserConnectionUtils.php';

class ProfileController
{
    // Fonction pour modifier le profil de l'utilisateur
    public function updateProfile()
    {
        // Instanciation de l'object Security pour les sessions protégées
        $securityObj = new Security();
        
        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            // Vérifier que le user est connecté
            if (!UserConnectionUtils::isUserConnected())
            {
                // Code 401 (utilisateur non authentifié)
                http_response_code(401);

                // On setup le message d'erreur pour la vue
                $errorMsg = new MessageErreur("Chargement de la page impossible", "Veuillez vous connecter pour changer votre profil.");
                require 'View/B3/PageErreur.php';
                return false;
            }

            // On renvoie du JSON par défaut (AJAX)
            header("Content-Type: application/json");

            // Vérification du token CSRF
            if (!$securityObj->checkCSRFToken($_POST['csrf_token'] ?? '')) {
                http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => "Token CSRF invalide."
                ]);
                return false;
            }

            // Définir un code HTTP 400 (Bad Request) par défaut
            http_response_code(400);

            // L'utilisateur doit être connecté pour pouvoir changer ses données
            $userId = UserConnectionUtils::getConnectedUserId();
            if ($userId == null) {
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => "Erreur: L'utilisateur n'est pas connecté."
                ]);

                return false;
            }

            // On récupère les variables qu'on va vérifier
            $nom = $_POST['nom_utilisateur'] ?? null; // nom de l'utilisateur
            $prenom = $_POST['prenom_utilisateur'] ?? null; // prénom de l'utilisateur
            $email = $_POST['mail_utilisateur'] ?? null; // email de l'utilisateur
            $mot_de_passe = $_POST['mdp_utilisateur'] ?? null; // mot de passe
            $champsAChanger = [];

            // On vérifie si les champs sont vides ou non
            if (!empty($nom)) {
                if (!UserCredentials::verifyNameFormat($nom)) {
                    // Réponse JSON avec le message d'erreur
                    echo json_encode([
                        'status' => 'error',
                        'message' => "Le format du nom est invalide."
                    ]);
                    return false;
                }
                $champsAChanger[] = "nom_utilisateur = :nom_utilisateur";
                $params[':nom_utilisateur'] = $nom;
            }
 
            // On vérifie si les champs sont vides ou non
            if (!empty($prenom)) {
                if (!UserCredentials::verifyNameFormat($prenom)) {
                    // Réponse JSON avec le message d'erreur
                    echo json_encode([
                        'status' => 'error',
                        'message' => "Le format du prénom est invalide"
                    ]);
                    return false;
                }
                $champsAChanger[] = "prenom_utilisateur = :prenom_utilisateur";
                $params[':prenom_utilisateur'] = $prenom;
            }


            // On vérifie si les champs sont vides ou non
            if (!empty($email)) {
                if (!UserCredentials::verifyEmailFormat($email)) {
                    // Réponse JSON avec le message d'erreur
                    echo json_encode([
                        'status' => 'error',
                        'message' => "Le format de l'email est invalide"
                    ]);
                    return false;
                }

                if (UserCredentials::isEmailAlreadyTaken($email))
                {
                    // Réponse JSON avec le message d'erreur
                    echo json_encode([
                        'status' => 'error',
                        'message' => "Erreur : L'email est déjà utilisée"
                    ]);
                    return false;
                }
                
                $champsAChanger[] = "mail_utilisateur = :mail_utilisateur";
                $params[':mail_utilisateur'] = $email;
            }

            // On vérifie si les champs sont vides ou non
            if (!empty($mot_de_passe)) {
                if (!UserCredentials::verifyStrongPassword($mot_de_passe)) {
                    // Réponse JSON avec le message d'erreur
                    echo json_encode([
                        'status' => 'error',
                        'message' => "Erreur : Le nouveau mot de passe n'est pas valide. Il faut au moins 8 caractères avec une minuscule, une majuscule et un chiffre"
                    ]);
                    return false;
                }
                $champsAChanger[] = "mdp_utilisateur = :mdp_utilisateur";

                // On oublie pas de hash le mot de passe
                $params[':mdp_utilisateur'] = UserCredentials::hashPassword($mot_de_passe);
            }

            // Aucun paramètre n'a été donné. Il n'y a donc rien à modifier
            if (empty($champsAChanger)) {
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'warning',
                    'message' => "Aucun champ à modifier n'est fourni"
                ]);
                return false;
            }

            $userProfile = new UserProfile($userId);
            $result = $userProfile->changeProfile($champsAChanger, $params);
            if ($result) {
                // Définir un code HTTP 200 (Succès)
                http_response_code(200);

                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Votre profil a été changé avec succès.'
                ]);
            } 
            else {

                // Code d'erreur avec une erreur serveur
                http_response_code(500);

                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Une erreur est survenue, veuillez réessayer.'
                ]);
            }

            return $result;
        } 
        else {
            if (UserConnectionUtils::isUserConnected()) {

                // Génération du token CSRF pour le formulaire
                $csrf_token = $securityObj->genererCSRFToken();
                
                // Affiche la page si la méthode n'est pas POST (en cas de simple visite de la page)
                require 'View/B3/ModifierProfil.php';
                return true;

            } else {
                
                // On setup le message d'erreur pour la vue
                $errorMsg = new MessageErreur("Chargement de la page impossible", "Veuillez vous connecter pour changer votre profil.");
                require 'View/B3/PageErreur.php';
                return false;
            }
        }
    }
}