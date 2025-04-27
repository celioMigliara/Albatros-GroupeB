<?php

require_once __DIR__ . '/../../Model/B3/UserCredentials.php';

class ProfileController
{
    // Fonction pour modifier le profil de l'utilisateur
    public function updateProfile()
    {

        // Démarrage de session ABSOLUMENT EN PREMIER
        if (session_status() === PHP_SESSION_NONE) {
            // Configurer les paramètres du cookie de session
            session_set_cookie_params([
                'httponly' => true,
                'secure' => false, // à activer uniquement en HTTPS
                'samesite' => 'Strict'
            ]);
            // Démarrer la session
            session_start();
        }

        // Génération du token AVANT TOUTE CHOSE
        $csrf_token = genererCSRFToken();
        
        // Verification de la méthode de la requête
        if ($_SERVER["REQUEST_METHOD"] === "POST" && UserCredentials::isUserConnected()) {

            // Vérification du token CSRF
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => "Token CSRF invalide."
                ]);
                return false;
            }

            // Définir un code HTTP 400 (Bad Request) par défaut
            http_response_code(400);

            // On renvoie du JSON par défaut (AJAX)
            header("Content-Type: application/json");
            // Start la session pour récupérer les données de session
            if (session_status() == PHP_SESSION_NONE) {
                // Configurer les paramètres du cookie de session
                session_set_cookie_params([
                    'httponly' => true,
                    'secure' => false, // à activer uniquement en HTTPS
                    'samesite' => 'Strict'
                ]);

                // Démarrer la session
                session_start();
            }

            // L'utilisateur doit être connecté pour pouvoir changer ses données
            $userId = UserCredentials::getConnectedUserId();
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
            $params[':id'] = $userId;

            // On vérifie si les champs sont vides ou non
            if (!empty($nom)) {
                if (!UserCredentials::verifyNameFormat($nom)) {
                    // Réponse JSON avec le message d'erreur
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Une erreur est survenue, veuillez réessayer.'
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
                        'message' => 'Une erreur est survenue, veuillez réessayer.'
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
                        'message' => 'Une erreur est survenue, veuillez réessayer.'
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
                        'message' => 'Une erreur est survenue, veuillez réessayer.'
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

            $result = UserCredentials::changeProfile($champsAChanger, $params);
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
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Une erreur est survenue, veuillez réessayer.'
                ]);
            }

            return $result;
        } 
        else {

            // Si la méthode n'est pas POST, on re-affiche la page de modification du profil
            // On vérifie si l'utilisateur est connecté
            if (UserCredentials::isUserConnected()) {
                // Affiche la page si la méthode n'est pas POST (en cas de simple visite de la page)
                require 'Vue/ModifierProfil.php';
                return true;
            } else {
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => "Erreur: L'utilisateur n'est pas connecté."
                ]);
                header("Location: index.php?action=connexion");
                return false;
            }
        }
    }
}